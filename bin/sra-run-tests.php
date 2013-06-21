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
*/

// {{{ includes
require_once(dirname(dirname(__FILE__)) . '/lib/core/SRA_Controller.php');
require_once('test/SRA_TestCase.php');
// }}}

$resources =& SRA_Controller::getSysResources();

// Check the command line parameters.
if (!$_SERVER['argv'][1] || (isset($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?')))) {
  echo $resources->getString('run-tests.usage');
  exit;
}
$quite = FALSE;
$startIdx = 1;
if ($_SERVER['argv'][1] == '-q' || $_SERVER['argv'][1] == '--quite') {
  $quite = TRUE;
  $startIdx = 2;
}

$app = SRA_Controller::appKeyIsValid($_SERVER['argv'][$startIdx]) ? $_SERVER['argv'][$startIdx] : NULL;
if ($app) {
  unset($_SERVER['argv'][$startIdx]);
  SRA_Controller::init($app); 
}

$skipErrorEntity = FALSE;
$tests = array();
$template = NULL;
if ($app) {
  $entityIds = SRA_EntityModeler::getAppEntityIds();
}
$results = array();

// all application entity model unit tests
if ($app && $_SERVER['argc'] == ($startIdx + 1)) {
  $tests = SRA_EntityModeler::getAppEntityUnitTests();
  $skipErrorEntity = TRUE;
  $entityIds = array();
}
// only the unit tests specified in the cli arguments
else {
  for($i=$startIdx; $i<$_SERVER['argc']; $i++) {
    if (!trim($_SERVER['argv'][$i])) { continue; }
    
    if ($i == ($_SERVER['argc'] - 1) && SRA_Util::endsWith($_SERVER['argv'][$i], '.' . SRA_TEMPLATE_DEFAULT_EXT)) {
      $template = $_SERVER['argv'][$i];
    }
    else {
      if (in_array($_SERVER['argv'][$i], $entityIds)) {
        $tests[SRA_EntityModeler::getAppEntityClass($_SERVER['argv'][$i])] = SRA_EntityModeler::getAppEntityPath($_SERVER['argv'][$i]);
      }
      else {
        if (!SRA_Util::endsWith($_SERVER['argv'][$i], '.' . SRA_SYS_PHP_EXTENSION)) { $_SERVER['argv'][$i] .= '.' . SRA_SYS_PHP_EXTENSION; }
        if ($test = SRA_File::getRelativePath('lib', $_SERVER['argv'][$i])) {
          $tests[] = $test;
        }
        else {
          $results[] = $resources->getString('run-tests.error.file', array('file' => $_SERVER['argv'][$i]));
        }
      }
    }
  }
}

if ($template && (!SRA_Template::isValid($tpl =& SRA_Controller::getAppTemplate()) || !$tpl->validate($template))) {
  echo $resources->getString('run-tests.error.template', array('template' => $template));
}
else if (!$tests) {
  echo $resources->getString('run-tests.error.noTests');
}

// run tests
foreach($tests as $key => $test) {
  include_once($test);
  
  // entity test
  if (!is_numeric($key) && in_array($key, $entityIds)) {
    eval('$testObj =& ' . $key . '::getUnitTestInstance();');
    $isEntity = TRUE;
  }
  // SRA_TestCase class file
  else {
    $testClass = str_replace('.' . SRA_SYS_PHP_EXTENSION, '', basename($test));
    $testObj = new ${testClass}();
    $isEntity = FALSE;
  }
  
  if (SRA_TestCase::isValid($testObj)) {
    if (!$testObj->runTests($template)) {
      $results[] = $resources->getString('run-tests.error.runTests', array('test' => $isEntity ? $key : $test));
    }
    $results[] = $resources->getString($testObj->status ? 'run-tests.status.success' : 'run-tests.status.failed', array('test' => $isEntity ? $key : str_replace(SRA_Controller::getAppDir(), '', $test), 'failed' => $testObj->failed, 'passed' => $testObj->passed, 'total' => $testObj->total));
  }
  else {
    $results[] = $resources->getString($isEntity && $skipErrorEntity ? 'run-tests.skipEntity' : 'run-tests.error.test', array('key' => $key, 'test' => $isEntity ? $key : $test));
  }
  $results[] = "\n" . $resources->getString('run-tests.runtime', array('runtime' => SRA_Controller::getRunTime()));
}

// output results
if (!$quite) {
  echo "\n";
  foreach($results as $str) {
    echo $str . "\n";
  }
  echo "\n";
}

?>
