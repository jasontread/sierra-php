#!/usr/bin/php -q
<?php
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

 a simple sierra php console. accepts 1 optional parameter which is the name of 
 the app to initialize when the console starts up
*/

// {{{ constants
/**
 * constant identifying that console mode is currently active
 * @type boolean
 */
define('SRA_CONSOLE', TRUE);

/**
 * the maximum time that a console will remain open in seconds (i.e. 86400 
 * equals 1 day)
 * @type int
 */
define('SRA_CONSOLE_MAX_EXECUTION_TIME', 86400);

/**
 * the name of the console history file
 * @type string
 */
define('SRA_CONSOLE_HISTORY_FILE', '.sra-console_history');

/**
 * the max # of history entries to keep in the history file
 * @type int
 */
define('SRA_CONSOLE_HISTORY_KEEP', 500);

/**
 * the string used to specify a semicolon within a command
 * @type string
 */
define('SRA_CONSOLE_SEMICOL_STR', '\;');
// }}}

// {{{ includes
require_once(dirname(dirname(__FILE__)) . '/lib/core/SRA_Controller.php');
// }}}

// Check the command line parameters.
if (isset($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?'))) {
  $_resources =& SRA_Controller::getSysResources();
  echo $_resources->getString('console.usage');
  exit;
}
// load password file
if (isset($_SERVER['argv'][2]) && is_file($_SERVER['argv'][2]) && is_readable($_SERVER['argv'][2]) && ($_auth = SRA_File::propertiesFileToArray($_SERVER['argv'][2]))) {
  if (isset($_auth['username']) && $_auth['username']) { $_SERVER['PHP_AUTH_USER'] = $_auth['username']; }
  if (isset($_auth['password']) && $_auth['password']) { $_SERVER['PHP_AUTH_PW'] = $_auth['password']; }
}
// load password from cli args
else if (isset($_SERVER['argv'][2])) {
  if (isset($_SERVER['argv'][2])) { $_SERVER['PHP_AUTH_USER'] = $_SERVER['argv'][2]; }
  if (isset($_SERVER['argv'][3])) { $_SERVER['PHP_AUTH_PW'] = $_SERVER['argv'][3]; }
}
// initialize the application
if (isset($_SERVER['argv'][1]) && SRA_Error::isError(SRA_Controller::init($_SERVER['argv'][1], FALSE))) {
  $_resources =& SRA_Controller::getSysResources();
  echo $_resources->getString('console.error.app', array('app' => $_SERVER['argv'][1]));
	exit;
}

$_readline = function_exists('readline');
$_pathDirs = explode(':', getenv('PATH'));
if (!$_readline) { $_stdin = fopen('php://stdin', 'r'); }
ini_set('max_execution_time', SRA_CONSOLE_MAX_EXECUTION_TIME);
$_resources =& SRA_Controller::getSysResources();

$_historyFile = (getenv('HOME') ? getenv('HOME') : SRA_Controller::getSysTmpDir()) . '/' . SRA_CONSOLE_HISTORY_FILE;
if (!$_readline) $_history = SRA_File::fileToArray($_historyFile);
if ($_readline) {
  readline_read_history($_historyFile);
  readline_completion_function('_autoComplete');
}
if (!$_readline) $_historyPtr = 0;
$_started = FALSE;

while(TRUE) {
  $_prompt = (SRA_Controller::getCurrentAppId() ? SRA_Controller::getCurrentAppId() : 'sierra') . '> ';
  if (!$_readline) { echo $_prompt; }
  $_consoleClear = FALSE;
  $_cmd .= $_started ? ($_readline ? readline($_prompt) : fgets($_stdin,1000)) : 'clear';
  if (!trim($_cmd)) { $_cmd = ''; continue; }
  
  if (strstr($_cmd, SRA_CONSOLE_SEMICOL_STR)) { $_cmd = str_replace(SRA_CONSOLE_SEMICOL_STR, '#:#', $_cmd); }
  // comments
  if (SRA_Util::beginsWith($_cmd, '//')) { $_cmd = ''; continue; }
  
  // change to history command
  $_skipHistory = !$_started;
  if (SRA_Util::beginsWith(trim($_cmd), '!') && trim($_cmd) != '!') {
    if ($_readline) $_history = function_exists('readline_list_history') ? readline_list_history() : explode("\n", trim(str_replace('\040', ' ', str_replace('_HiStOrY_V2_', '', file_get_contents($_historyFile)))));
    $_ids = array(substr($_cmd, 1));
    if (strstr($_ids[0], ',') || strstr($_ids[0], '-')) {
      $_pieces = explode(',', $_ids[0]);
      $_ids = array();
      foreach($_pieces as $_piece) {
        if (strstr($_piece, '-')) {
          $_range = explode('-', $_piece);
          for($_x=$_range[0]*1; $_x<=$_range[1]*1; $_x++) {
            $_ids[] = $_x;
          }
        }
        else {
          $_ids[] = $_piece;
        }
      }
    }
    $_keys = array_keys($_ids);
    foreach($_keys as $_key) {
      $_ids[$_key] = $_ids[$_key]-1;
      if (!isset($_history[$_ids[$_key]])) {
        echo 'history event ' . $_ids[$_key] . " is not valid\n";
        $_cmd = '';
        continue;
      }
    }
    $_cmd = '';
    foreach($_ids as $_id) {
      $_tmp = $_history[$_id] . (count($_history) == 1 || SRA_Util::endsWith(trim($_history[$_id]), ';') ? '' : '; ');
      $_cmd .= $_tmp;
      $_history[] = $_tmp;
    }
    $_cmd .= "\n";
    echo $_cmd;
  }
  
  // display history
  if (strtolower(trim($_cmd)) == 'history' || strtolower(trim($_cmd)) == 'history;' || strtolower(trim($_cmd)) == '!') {
    if ($_readline) $_history = function_exists('readline_list_history') ? readline_list_history() : explode("\n", trim(str_replace('\040', ' ', str_replace('_HiStOrY_V2_', '', file_get_contents($_historyFile)))));
    for($_x=0; $_x<count($_history); $_x++) {
      printf(' %' . strlen(count($_history) . '') . "d  %s\n", $_x+1, $_history[$_x]);
    }
    if ($_readline) {
      readline_add_history('history');
      $_cmd = '';
    }
    else {
      $_cmd = "history\n";
    }
  }
  
  // clear history
  if (strtolower(trim($_cmd)) == 'history clear' || strtolower(trim($_cmd)) == 'history clear;' || strtolower(trim($_cmd)) == '! clear') {
    $_readline ? readline_clear_history() : ($_history = array());
    $_cmd = '';
    continue;
  }
  
  if (!$_skipHistory && ($_readline || !count($_history) || (count($_history) && $_history[count($_history) - 1] != substr($_cmd, 0, -1)))) { 
    if (trim($_cmd) && trim($_cmd) != $_lastCmd) { $_readline ? readline_add_history(trim($_cmd)) : ($_history[] = substr($_cmd, 0, -1)); }
  }
  if (!$_readline && trim($_cmd) == 'history') {
    $_cmd = '';
    continue; 
  }
  
  if (SRA_Util::beginsWith(trim($_cmd), 'exit', FALSE) || SRA_Util::beginsWith(trim($_cmd), 'quit', FALSE)) {
    if ($_readline) {
      readline_write_history($_historyFile);
    }
    else {
      $_buffer = '';
      for($_x=count($_history) > SRA_CONSOLE_HISTORY_KEEP ? count($_history) - SRA_CONSOLE_HISTORY_KEEP : 0; $_x<count($_history); $_x++) {
        $_buffer .= ($_buffer == '' ? '' : "\n") . $_history[$_x];
      }
      SRA_File::write($_historyFile, $_buffer);
    }
    break; 
  }
  
  // check for executable programs (wrap using the php 'exec' function)
  if (trim($_cmd) && substr(trim($_cmd), -1, 1) != ';') {
    $_pieces = explode(' ', $_cmd);
    foreach($_pathDirs as $_dir) {
      if (is_executable($_dir . '/' . trim($_pieces[0]))) {
        $_cmd = $_dir . '/' . $_cmd;
        break;
      }
    }
    $_pieces = explode(' ', $_cmd);
    if (is_executable(trim($_pieces[0]))) {
      $_consoleClear = basename(trim($_pieces[0])) == 'clear';
      $_cmd = 'exec("' . str_replace('"', '\"', trim($_cmd)) . '");'; 
    }
  }
  
  // execute php code
  if (substr(trim($_cmd), -1, 1) == ';') {
    // check for sierra class short circuiting
    $_pieces = explode(' ', $_cmd);
    $_cmd = '';
    foreach($_pieces as $_piece) {
      $_cmd .= $_piece == $_pieces[0] ? '' : ' ';
      // check if SRA_ prefix left out of static method invocation
      if (strpos($_piece, '::') && ($_tmp = explode('::', $_piece)) && !SRA_Util::beginsWith(strtolower(trim($_tmp[0])), 'sra_') && class_exists('SRA_' . trim($_tmp[0]))) { $_piece = 'SRA_' . $_piece; }
      // check if this is a short-circuit SRA_Controller method
      if (strpos($_piece, '(') && ($_tmp = explode('(', $_piece)) && in_array(trim(strtolower($_tmp[0])), get_class_methods('SRA_Controller'))) { $_piece = 'SRA_Controller::' . $_piece; }
      $_cmd .= $_piece;
    }
    
    if (!SRA_Util::beginsWith(strtolower(trim($_pieces[0])), 'sra_') && class_exists('SRA_' . trim($_pieces[0]))) { $_cmd = 'SRA_' . $_cmd; }
    ob_start();
    $_cmd = str_replace('echo', 'print', $_cmd);
    $_consoleEval = array();
    foreach(explode(';', $_cmd) as $_command) {
      if (trim($_command)) { $_consoleEval[] = eval('return ' . str_replace('#:#', ';', $_command) . ';'); }
    }
    $_console = ob_get_contents();
    ob_end_clean();
    if ($_console) { echo $_console . "\n"; }
    if (!SRA_Util::beginsWith($_cmd, 'print')) {
      foreach($_consoleEval as $_consoleOut) {
        $_consoleOut = SRA_GregorianDate::isValid($_consoleOut) ? $_consoleOut->toString() : ($_consoleOut===TRUE ? 'TRUE' : ($_consoleOut===FALSE ? 'FALSE' : ($_consoleOut===NULL ? 'NULL' : $_consoleOut)));
        echo (is_object($_consoleOut) ? (SRA_Error::isError($_consoleOut) ? 'SRA_Error: ' . $_consoleOut->getErrorMessage() : get_class($_consoleOut) . ' object') : $_consoleOut) . ($_consoleClear ? '' : "\n");
      }
    }
    $_lastCmd = trim($_cmd);
    $_cmd = '';
  }
  
  if (!$_started) {
    $_started = TRUE;
    echo $_resources->getString('console.start', $_attrs = array('time' => SRA_Controller::getRunTime())) . "\n";
    if (!$_readline) { echo "\n" . $_resources->getString('console.start.noReadline') . "\n\n"; }
  }
}
if (!$_readline) fclose($_stdin); 
echo $_resources->getString('console.end', $_attrs = array('time' => SRA_Controller::getRunTime())) . "\n";

function _autoComplete($str, $pos, $mp) {
  $tmp = readline_info();
  $tmp = explode(' ', $tmp['line_buffer']);
  $cmd = $tmp[count($tmp) - 1];
  $ret = array();
  // object methods and properties
  if (strpos($cmd, '->') && is_object($obj =& $GLOBALS[substr($cmd, 1, strpos($cmd, '->') - 1)])) {
    $start = substr($cmd, strpos($cmd, '->') + 2);
    // methods
    foreach(get_class_methods(get_class($obj)) as $method) {
      if (substr($method, 0, 1) != '_' && strtolower(get_class($obj)) != strtolower($method) && (!$start || SRA_Util::beginsWith($method, $start, FALSE))) {
        $ret[] = $start . substr($method, strlen($start)) . '(';
      }
    }
    // attributes
    foreach(array_keys(get_class_vars(get_class($obj))) as $var) {
      if (substr($var, 0, 1) != '_' && (!$start || SRA_Util::beginsWith($var, $start, FALSE))) {
        $ret[] = $start . substr($var, strlen($start));
      }
    }
  }
  else if ($str) {
    // declared classes
    foreach(get_declared_classes() as $class) {
      if (SRA_Util::beginsWith($class, $str, FALSE)) {
        $ret[] = $str . substr($class, strlen($str));
        $ret[] = $str . substr($class, strlen($str)) . '::';
      }
    }
    // class methods
    if (class_exists($class = substr($str, 0, strpos($str, '::') ? strpos($str, '::') : strlen($str)))) {
      $start = strpos($str, '::') ? substr($str, strpos($str, '::') + 2) : NULL;
      // methods
      foreach(get_class_methods($class) as $method) {
        if (substr($method, 0, 1) != '_' && (!$start || SRA_Util::beginsWith($method, $start, FALSE))) {
          $ret[] = $str . (strpos($str, '::') ? '' : '::') . substr($method, strlen($start)) . '(';
        }
      }
      // attributes
      foreach(array_keys(get_class_vars($class)) as $var) {
        if (substr($var, 0, 1) != '_' && (!$start || SRA_Util::beginsWith($var, $start, FALSE))) {
          $ret[] = $str . (strpos($str, '::') ? '' : '::') . substr($var, strlen($start));
        }
      }
    }
    // globals
    foreach(array_keys($GLOBALS) as $var) {
      if (substr($cmd, 0, 1) == '$' && SRA_Util::beginsWith($var, $str)) {
        $ret[] = $str . substr($var, strlen($str)) . (is_object($GLOBALS[$var]) ? '->' : '');
        if (is_object($GLOBALS[$var])) { $ret[] = $str . substr($var, strlen($str)) . '->__'; }
      }
    }
    // functions
    if (!$ret) {
      foreach(get_defined_functions() as $functions) {
        if (!is_array($functions)) { $functions = array($functions); }
        foreach($functions as $function) {
          if (SRA_Util::beginsWith($function, $str, FALSE)) {
            $ret[] = $str . substr($function, strlen($str)) . '(';
          }
        }
      }
    }
  }
  return count($ret) ? $ret : NULL;
}
?>
