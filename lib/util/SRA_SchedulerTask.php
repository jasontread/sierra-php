<?php
// {{{ Header
/*
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | SIERRA : PHP Application Framework  http://code.google.com/p/sierra-php |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | Copyright 2005 Jason Read                                               |
 |                                                                         |
 | Licensed under the Apache License, Version 2.0 (the "License");         |
 | you may not use this file except in compliance with the License.        |
 | You may obtain a copy of the License at                                 |
 |                                                                         |
 |     http://www.apache.org/licenses/LICENSE-2.0                          |
 |                                                                         |
 | Unless required by applicable law or agreed to in writing, software     |
 | distributed under the License is distributed on an "AS IS" BASIS,       |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.|
 | See the License for the specific language governing permissions and     |
 | limitations under the License.                                          |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 */
// }}}

// {{{ Imports

// }}}

// {{{ Constants
/**
 * the default schedule for a task
 * @type string
 */
define('SRA_SCHEDULER_TASK_DEFAULT_SCHEDULE', '0 0 * * *');

/**
 * the path to the fork script
 * @type string
 */
define('SRA_SCHEDULER_TASK_FORK_SCRIPT', SRA_DIR . '/bin/sra-task-fork.php');

/**
 * the max # of minutes to allow a task to be locked
 * @type int
 */
define('SRA_SCHEDULER_TASK_LOCK_MAX_MINS', 1440);
// }}}

// {{{ SRA_SchedulerTask
/**
 * used to define tasks that should be performed on a scheduled basis. the task 
 * can be either an executable file, or a php function or method (instance or 
 * static). tasks can be scheduled in either the sierra-config or individual 
 * app-config files. the schedule provided for each task is identical to the 
 * schedule specified for cron jobs (see 'schedule' attribute documentation 
 * provided below). dates and times are evaluated according to the current 
 * app (or system time-zone) as specified in sierra-config or app-config
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_SchedulerTask {
  // {{{ Attributes
  // private attributes
  /**
	 * any file attachments included with the message
	 * @type string
	 */
	var $_className;
  
  /**
	 * if method and class-name are specified, whether or not that method should 
   * be invoked or as an instance method (a new instance of class-name will be 
   * created first in the case of the latter)
	 * @type boolean
	 */
	var $_instance;
  
  /**
   * the path to a lock file created when the _lock method is invoked
   * @type string
   */
  var $_lockFile;
  
  /**
	 * if the stdout output from performing that task should be logged, this 
   * should be the name of the log file (within sierra/log) that output should 
   * be stored to (will be stored along with a timestamp)
	 * @type string
	 */
	var $_log;
  
  /**
	 * the name of the method in $className (or a function name if $className is 
   * not specified) that should be invoked for this task
	 * @type string
	 */
	var $_method;
  
  /**
	 * nested params can be used for php method-based tasks and will be passed to 
   * that method in the form of an associative array (param 'type' will be 
   * ignored)
	 * @type array
	 */
	var $_params;
  
  /**
	 * the path to the php source file. this may be absolute or relative to the 
   * sierra base or lib directories
	 * @type string
	 */
	var $_path;
  
  /**
	 * the cron-formatted schedule for this task. for more information, see the 
   * api documentation for SRA_GregorianDate::cron
	 * @type string
	 */
	var $_schedule;
	
  // }}}
	
  
  // public methods
	// {{{ SRA_SchedulerTask
	/**
	 * instantiates a new scheduled task based on the conf provided
   * @param array $conf the scheduler task configuration as explained in the 
   * sierra-config and app-config dtds
	 * @access	public
	 */
	function SRA_SchedulerTask($conf) {
		$this->_className = isset($conf['attributes']['class-name']) ? $conf['attributes']['class-name'] : NULL;
    $this->_instance = isset($conf['attributes']['instance']) && $conf['attributes']['instance'] == '1' ? TRUE : FALSE;
    $this->_log = isset($conf['attributes']['log']) ? $conf['attributes']['log'] : NULL;
    $this->_method = isset($conf['attributes']['method']) ? $conf['attributes']['method'] : NULL;
    $pathPieces = explode(' ', $conf['attributes']['path']);
    $pathSuffix = '';
    if ($pathPieces && count($pathPieces) > 1) {
      for($i=1; $i<count($pathPieces); $i++) {
        $pathSuffix .= $i==1 ? '' : ' ';
        $pathSuffix .= $pathPieces[$i];
      }
    }
    $this->_path = $pathPieces && file_exists($pathPieces[0]) ? $conf['attributes']['path'] : NULL;
    $this->_path = !$this->_path && $pathPieces && SRA_File::getRelativePath('lib', $pathPieces[0]) ? SRA_File::getRelativePath('lib', $pathPieces[0]) . $pathSuffix : $this->_path;
    $this->_path = (!$this->_path || SRA_Util::endsWith($this->_path, 'lib')) && $pathPieces[0] && SRA_File::getRelativePath(NULL, $pathPieces[0]) ? SRA_File::getRelativePath(NULL, $pathPieces[0]) . $pathSuffix : $this->_path;
    
    $this->_schedule = isset($conf['attributes']['schedule']) ? $conf['attributes']['schedule'] : SRA_SCHEDULER_TASK_DEFAULT_SCHEDULE;
    if (SRA_GregorianDate::cron($this->_schedule) === NULL) { 
      $msg = 'SRA_SchedulerTask: Failed - schedule "' . $this->_schedule . '" is not valid';
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($conf['param']) {
      $params = new SRA_Params($conf['param']);
      $this->_params = $params->getParams();
    }
    // validate data
    if (!$this->_path) {
      $msg = 'SRA_SchedulerTask: Failed - path "' . $this->_path . '" is missing or not valid';
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!$this->_className && !$this->_method && !is_executable($this->_path) && !is_executable($pathPieces[0])) {
      $msg = 'SRA_SchedulerTask: Failed - path ' . $this->_path . ' is not executable';
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_method) {
      require_once($this->_path);
      if ($this->_className) {
        if (!class_exists($this->_className)) {
          $msg = 'SRA_SchedulerTask: Failed - class ' . $this->_className . ' does not exist in ' . $this->_path;
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
        $methods = get_class_methods($this->_className);
        if (!$methods || (!in_array(strtolower($this->_method), $methods) && !in_array($this->_method, $methods))) {
          $msg = 'SRA_SchedulerTask: Failed - method ' . $this->_method . ' does not exist in the class ' . $this->_className;
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
      }
      else {
        if (!function_exists($this->_method)) {
          $msg = 'SRA_SchedulerTask: Failed - function ' . $this->_method . ' does not exist in ' . $this->_path;
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
      }
    }
	}
	// }}}
  
	// {{{ evaluate
	/**
	 * evaluates this task's schedule and performs the task as the schedule 
   * determines. returns TRUE if the task is performed, FALSE otherwise
	 * @access	public
	 * @return	boolean
	 */
	function evaluate() {
    if (SRA_GregorianDate::cron($this->_schedule) && $this->_lock()) {
      $paramsFile = 0;
      if ($this->_params) {
        $paramsFile = SRA_File::createRandomFile();
        SRA_File::write($paramsFile, serialize($this->_params));
      }
      $cmd = SRA_SCHEDULER_TASK_FORK_SCRIPT . ' "' . $this->_path . '" ' . ($this->_className ? $this->_className : '0') . ' ' . ($this->_method ? $this->_method : '0') . ' ' . 
             ($this->_instance ? '1' : '0') . ' ' . (SRA_Controller::getCurrentAppId() ? SRA_Controller::getCurrentAppId() : '0') . ' ' . 
             $this->_lockFile . ' ' . ($this->_log ? SRA_Controller::getAppLogDir() . '/' . $this->_log : '0') . ' ' .  ($paramsFile ? $paramsFile : '0') . ' ' . 
             (defined('SRA_TASK_SCHEDULER_DEBUG') ? '1' : '0') . " > /dev/null 2>&1 & echo \$!";
      SRA_File::write($this->_lockFile, exec($cmd));
      return TRUE;
    }
    else {
      return FALSE;
    }
	}
	// }}}
  
	// {{{ getId
	/**
	 * returns the unique identifier for this task 
	 * @access	public
	 * @return	string
	 */
  function getId() {
    return (SRA_Controller::getCurrentAppId() ? SRA_Controller::getCurrentAppId() : '') . str_replace(' ', '_', str_replace('.', '_', str_replace('/', '_', str_replace('\\', '_', $this->_path)))) . '_' . $this->_className . '_' . $this->_method . $this->_instance;
  }
  // }}}
  
  
  // private
	// {{{ _lock
	/**
	 * locks this task for execution using a temp file. 
	 * @access	public
	 * @return	boolean
	 */
	function _lock() {
    // attempt to lock
    if (!$this->_lockFile) {
      $this->_lockFile = SRA_Controller::getSysTmpDir() . '/.task_' . $this->getId();
      // delete invalid lock files
      if (file_exists($this->_lockFile) && (filemtime($this->_lockFile) <= (time() - (SRA_SCHEDULER_TASK_LOCK_MAX_MINS*60)) || !is_numeric(SRA_File::toString($this->_lockFile)) || !SRA_Util::getProcessName(SRA_File::toString($this->_lockFile)))) {
        SRA_File::unlink($this->_lockFile);
      }
      // create lock file
      if (!file_exists($this->_lockFile) && !SRA_Error::isError(SRA_File::touch($this->_lockFile))) {
        return TRUE;
      }
    }
    // already locked
    else {
      return TRUE;
    }
    return FALSE;
	}
	// }}}
  
  
  // Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_SchedulerTask object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid(& $object) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_schedulertask');
	}
	// }}}

}
// }}}
?>
