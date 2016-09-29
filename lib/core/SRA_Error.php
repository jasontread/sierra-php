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

// {{{ Constants
/**
 * A constant that defines an OPERATIONAL status, meaning there are no problems.
 * @type   int
 * @access public
 */
define('SRA_ERROR_OPERATIONAL', E_USER_NOTICE);

/**
 * A constant that defines PROBLEM status. This status occurs when there is some
 * form of problem, but that the system can continue responding to page requests.
 * @type   int
 * @access public
 */
define('SRA_ERROR_PROBLEM',     E_USER_WARNING);

/**
 * This constant defines a critical problem that will not allow the system to continue
 * operating. When in this status the SRA_Template engine will display an error message to
 * any user that attempts to access any page.
 * @type   int
 * @access public
 */
define('SRA_ERROR_SHUTDOWN',    E_USER_ERROR);

/**
 * Constant that defines any system status.
 * @type   int
 * @access public
 */
define('SRA_ERROR_ALL',         E_USER_NOTICE | E_USER_WARNING | E_USER_ERROR);

/**
 * the default error-mask
 * @type int
 * @access public
 */
define('SRA_DEFAULT_ERROR_MASK', E_USER_WARNING | E_USER_ERROR);

// }}}

// {{{ Includes
include_once(SRA_LIB_DIR . '/util/SRA_LogEntry.php');
// }}}

// {{{ error_handler()
/**
 * This function can be used as the error handler for php files in a
 * production environment. It contains the logic for deciding whether or not
 * to proceed should a very severe error be thrown.
 *
 * This is a function because set_error_handler() doesn't like static methods.
 * As of PHP 4.1.2.
 *
 * PHP error_reporting() bit values:
 * 1    E_ERROR
 * 2    E_WARNING
 * 4    E_PARSE
 * 8    E_NOTICE
 * 16   E_CORE_ERROR
 * 32   E_CORE_WARNING
 * 64   E_COMPILE_ERROR
 * 128  E_COMPILE_WARNING
 * 256  E_USER_ERROR
 * 512  E_USER_WARNING
 * 1024 E_USER_NOTICE
 *
 * @param int $errNo PHP SRA_Error number.
 * @param	string $errStr description of error.
 * @param	string $errFile file where error occured.
 * @param	int $errLine line where error occured.
 * @param	string errContext context in which the error occurred. Pointer to
 * a symbol table
 * @access public
 * @return void
 * @author Charlie Killian, charlie@tizac.com
 */
function error_handler($errNo, $errStr, $errFile, $errLine, $errContext=FALSE) {
	$nl = "\n";
	$s_start = '';
	$s_end = '';

	// Get the error message and do any extra work depending on errNo.
	switch ($errNo) {
			case SRA_ERROR_OPERATIONAL:
					$err_msg = $s_start."SRA_ERROR_OPERATIONAL:$s_end $errStr";
					$err_no = SRA_ERROR_OPERATIONAL;
					break;

			case SRA_ERROR_PROBLEM:
					$err_msg = $s_start."SRA_ERROR_PROBLEM:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case SRA_ERROR_SHUTDOWN:
					$err_msg = $s_start."SRA_ERROR_SHUTDOWN:$s_end $errStr";
					$err_no = SRA_ERROR_SHUTDOWN;
					break;

			case SRA_ERROR_ALL:
					$err_msg = $s_start."SRA_ERROR_ALL:$s_end $errStr";
					$err_no = SRA_ERROR_ALL;
					break;

			case E_ERROR:
					$err_msg = $s_start."E_ERROR:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_WARNING:
					$err_msg = $s_start."E_WARNING:$s_end $errStr";
					$err_no = SRA_ERROR_OPERATIONAL;
					break;

			case E_NOTICE:
					$err_msg = $s_start."E_NOTICE:$s_end $errStr";
					$err_no = SRA_ERROR_OPERATIONAL;
					break;

			case E_PARSE:
					$err_msg = $s_start."E_PARSE:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_USER_ERROR:
					$err_msg = $s_start."E_USER_ERROR:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_USER_WARNING:
					$err_msg = $s_start."E_USER_WARNING:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_USER_NOTICE:
					$err_msg = $s_start."E_USER_NOTICE:$s_end $errStr";
					$err_no = SRA_ERROR_OPERATIONAL;
					break;

			case E_CORE_ERROR:
					$err_msg = $s_start."E_CORE_ERROR:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_CORE_WARNING:
					$err_msg = $s_start."E_CORE_WARNING:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_COMPILE_ERROR:
					$err_msg = $s_start."E_COMPILE_ERROR:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
					break;

			case E_COMPILE_WARNING:
					 $err_msg = $s_start."E_COMPILE_WARNING:$s_end $errStr";
					 $err_no = SRA_ERROR_PROBLEM;
					 break;

			default:
          if (phpversion() >= 5) {
            // PHP 5 recoverable error - treat as problem level error
            if ($errNo == 4096) {
              $err_msg = $s_start."E_RECOVERABLE_ERROR:$s_end $errStr";
              $err_no = SRA_ERROR_PROBLEM;
            }
            else return; 
          }
					$err_msg = $s_start."Unknown Type:$s_end $errStr";
					$err_no = SRA_ERROR_PROBLEM;
	} // end switch
	
	// Don't display psql duplicate key messages
	if (!strpos($err_msg, 'duplicate key into unique index processor_process_lock_index')) {
		SRA_Error::writeLog($err_msg, $errFile, $errLine, $err_no);
	}
}
// }}}

// {{{ SRA_Error

/**
 * SRA_Error description here.
 * @author	Jason Read <jason@idir.org>
 * @package sierra.core
 */
class SRA_Error {

    // {{{ Properties
    /**
     * This is an instance variable used to store the error message string associated with the SRA_Error object.
     * @type   string
     * @access private
     */
    var $_errorMessage;

    /**
     * SRA_File which created the error call.

     * @type   string
     * @access private
     */
    var $_file;

    /**
     * Line number of the file making the call (where the error occured).
     * @type   int
     * @access private
     */
    var $_lineNumber;

    /**
     * The severity of the error. Should be one of the 3 valid SRA_Error constants
     * (OPERATIONAL==Informational, PROBLEM==Problem, but not critical, and SHUTDOWN==Very critical,
     * could affect future system performance or availability).
     * @type   int
     * @access private
     */
    var $_severity;
    // }}}

    // {{{ SRA_Error()
    /**
     * Constructor. Assign values to the properties.
     *
     * @param	string $errorMessage the error message to log
     * @param	string $file path to the file which created this error call
     * @param	int $lineNumber line number in the file making the call.
     * @param	int $severity one of the defined constants above. this is an 
		 * optional parameter. the default value is SRA_ERROR_PROBLEM
     *
     * @access	public
     */
    function SRA_Error($errorMessage, $file, $lineNumber, $severity)
    {
        $this->_errorMessage	= $errorMessage;
        $this->_file			= $file;
        $this->_lineNumber		= $lineNumber;
        $this->_severity		= $severity;
    }
    // }}}

    // {{{ getErrorMessage()
    /**
     * An instance method used to access the errorMessage variable for the SRA_Error object instantiation.
     *
     * @access	public
     * @return	string
     */
    function getErrorMessage()
    {
        if (isset($this->_errorMessage))
            return $this->_errorMessage;
    }
    // }}}

    // {{{ getFile()
    /**
     * Returns the file name where this error occurred.
     *
     * @access	public
     * @return	string
     */
    function getFile()
    {
        if (isset($this->_file))
            return $this->_file;
    }
    // }}}

    // {{{ getLineNumber()
    /**
     * Returns the line # in the file where the error occurred.
     *
     * @access	public
     * @return	int
     */
    function getLineNumber()
    {
        if (isset($this->_lineNumber))
            return $this->_lineNumber;
    }
    // }}}

    // {{{ getSeverity()
    /**
     * Returns the severity of the SRA_Error. This value will correspond with one of the
     * SRA_Error object constants.
     *
     * @access	public
     * @return	int
     */
    function getSeverity()
    {
        if (isset($this->_severity))
            return $this->_severity;
    }
    // }}}

    // {{{ isError()
    /**
     * isError checks to see if the passed object is type SRA_Error.
     *
     * @param	object $obj object to check.
     *
     * @access	public
     * @return	boolean
     */
    function isError($obj)
    {
        return is_object($obj) && strtolower(get_class($obj)) == 'sra_error' || is_subclass_of($obj, 'sra_error');
    }
    // }}}
	
    // {{{ isValidLevel()
    /**
     * Returns true if the errorLevel parameter is a valid error level (one of the 
	 * SRA_ERROR_* constants).
     *
     * @param	int errorLevel the error level to check. This value should correspond 
	 * to one of the SRA_ERROR_* constants.
     *
     * @access	public
     * @return	boolean
     */
    function isValidLevel($errorLevel)
    {
        return SRA_Util::validateBit($errorLevel, SRA_ERROR_ALL);
    }
    // }}}

    // {{{ logError()
    /**
     * A static method used by other system objects to log errors. This method
     * requires that the invoking method specify an error message, what file the
     * error originates from, the line number for that file, and the severity of
     * the error. The method will then store this error to the global error log
     * file specified by the system etc file. It also returns an error object
     * that can be passed "up" in order to allow the system to handle the error
     * approriately.
     *
     * @param	string $errorMessage the error message to log
     * @param	string $file path to the file which created this error call
     * @param	int $lineNumber line number in the file making the call.
     * @param	int $severity one of the defined constants above. this is an 
		 * optional parameter. the default value is SRA_ERROR_PROBLEM
		 * @param boolean $debug the debug boolean flag. if not specified, the SRA_Controller::isAppInDebug() method will be 
		 * 					called to determine if debug output should be produced
     *
     * @access	public
     * @return	SRA_Error
     */
    function &logError($errorMessage, $file, $lineNumber, $severity=SRA_ERROR_PROBLEM, $debug = SRA_UTIL_USE_SYS_CONFIG_DEBUG) {
      if (is_array($errorMessage) || is_object($errorMessage)) {
        ob_start();
        print_r($errorMessage);
        $errorMessage = ob_get_clean();
      }
			error_handler($severity, $errorMessage, $file, $lineNumber);
			
			// Output debug info
      if ($debug) {
        SRA_Util::printDebug(SRA_Error::statusTostring($severity) . ": " . $errorMessage, $debug, $file, $lineNumber);
      }
		
			// Return an error object.
			return new SRA_Error($errorMessage, $file, $lineNumber, $severity);
    }
    // }}}

    // {{{ statusTostring()
    /**
     * This static method converts a status integer into the corresponding text.
     * Used for readablility in the error log.
     *
     * @param	int $status the SRA_Error status to convert. This value will correspond
     * 			with one of the SRA_ERROR_* constants.
     * @access	public
     * @return	string
     */
    function statusTostring($status)
    {
        switch ($status)
        {
            case SRA_ERROR_OPERATIONAL:
                return "OPERATIONAL";
            case SRA_ERROR_PROBLEM:
                return "PROBLEM";
            case SRA_ERROR_SHUTDOWN:
                return "SHUTDOWN";
            case SRA_ERROR_ALL:
                return "ANY";
        }
    }
    // }}}

    // {{{ writeLog()
    /**
     * Static method which creates a SRA_LogEntry object for a given message.
     *
     * @param	string $errorMessage the error message to log
     * @param	string $file path to the file which created this error call
     * @param	int $lineNumber line number in the file making the call.
     * @param	int $severity one of the defined constants above. this is an 
		 * optional parameter. the default value is SRA_ERROR_PROBLEM
     * @access public
     * @return boolean
     */
    static function writeLog($errorMessage, $file, $lineNumber, $severity) {
			
			if (!($severity & SRA_Controller::getAppErrorMask())) {
				// Mask doesn't want us to log this message.
				return FALSE;
			}

			// Write log message here.
			$log_entry = new SRA_LogEntry();
			$log_entry->setFile($file);
			$log_entry->setLineNumber($lineNumber);
			$log_entry->setMessage($errorMessage);

			// Get the error.log file.
			return $log_entry->writeToFile(SRA_Controller::getAppErrorLogFile());
    }
    // }}}
}
// }}}

?>
