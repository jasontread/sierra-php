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
 
 this script is used to allow forking of a task process. it uses the following 
 cli parameters (in the order specified):
 
 path:       path to the source or executable file
 class name: the name of the class within the source file or 0 for none
 method:     the name of the method to invoke or 0 for none
 instance:   if class name and method are specified, should method be invoked as 
             an instance or static (value should be 0 or 1)
 app:        the app key or 0 for none
 lock file:  the lock file to erase (within tmp) once execution is complete
 log file:   the output log file or 0 for none
 params file:the file containing serialized params for the invocation (optional)
             will be deleted
 debug:      whether or not script should be run in debug mode
*/


$path = $_SERVER['argv'][1];
$pathParts = explode(' ', $path);
$className = $_SERVER['argv'][2];
$method = $_SERVER['argv'][3];
$instance = $_SERVER['argv'][4];
$app = $_SERVER['argv'][5];
$lockFile = $_SERVER['argv'][6];
$logFile = $_SERVER['argv'][7];
$paramsFile = $_SERVER['argv'][8];
if ($_SERVER['argv'][9]) { define('SRA_TASK_SCHEDULER_DEBUG', TRUE); }

require_once(dirname(dirname(__FILE__)) . '/lib/core/SRA_Controller.php');

ob_start();
if ($app) { SRA_Controller::init($app, TRUE, TRUE, TRUE); }

if ($logFile) { $start = new SRA_GregorianDate(); }
if ((!$app || $app == SRA_Controller::getCurrentAppId()) && $pathParts && file_exists($pathParts[0])) {
  if ($className || $method) {
    require_once($path);
  }
  if (!$method && is_executable($path)) {
    exec($path, $output);
    if ($output) {
      foreach($output as $line) {
        print($line . "\n");
      }
    }
  }
  else {
    if ($paramsFile && file_exists($paramsFile)) {
      $params = unserialize(SRA_File::toString($paramsFile));
    }
    if ((!$className && function_exists($method)) || ($className && ($methods = get_class_methods($className)) && (in_array(strtolower($method), $methods) || in_array($method, $methods)))) {
      if ($className && $instance) {
        $obj = new ${className}();
        $obj->${method}($params);
      }
      else {
        eval(($className ? $className . '::' : '') . $method . '($params);');
      }
    }
  }
  if ($logFile) {
    $end = new SRA_GregorianDate();
    SRA_LogEntry::log(ob_get_contents() . ' (started: ' . $start->toString() . ')', $logFile);
  }
}
ob_end_clean();
if ($paramsFile && file_exists($paramsFile)) { SRA_File::unlink($paramsFile); }
if ($lockFile && file_exists($lockFile)) { SRA_File::unlink($lockFile); }
?>
