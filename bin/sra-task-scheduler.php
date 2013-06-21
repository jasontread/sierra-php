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
// wait until 00 second
if (!$_SERVER['argv'][1]) {
  if (date('s') != '00') {
    sleep(60 - (int) date('s'));
  }
  // wait for 1 second then fork next task schedule process
  sleep(1);
  exec(__FILE__ . ' > /dev/null 2>&1 &');
}
else {
  define('SRA_TASK_SCHEDULER_DEBUG', FALSE);
}

require_once(dirname(dirname(__FILE__)) . '/lib/core/SRA_Controller.php');
require_once('util/SRA_SchedulerTask.php');

// system scheduled tasks
$sysConf =& SRA_Controller::getSysConf();
if (isset($sysConf['scheduled-task'])) {
  if (isset($sysConf['scheduled-task']['attributes']['path'])) { $sysConf['scheduled-task'] = array($sysConf['scheduled-task']); }
  $keys = array_keys($sysConf['scheduled-task']);
  foreach($keys as $key) {
    $task = new SRA_SchedulerTask($sysConf['scheduled-task'][$key]);
    $results = $task->evaluate();
  }
}

// app scheduled tasks
$skeys = array_keys($sysConf['app']);
foreach($skeys as $skey) {
  SRA_Controller::init($skey, TRUE, TRUE, TRUE);
  $appConf =& SRA_Controller::getAppConf();
  if (isset($appConf['scheduled-task'])) {
    if (isset($appConf['scheduled-task']['attributes']['path'])) { $appConf['scheduled-task'] = array($appConf['scheduled-task']); }
    $keys = array_keys($appConf['scheduled-task']);
    foreach($keys as $key) {
      $task = new SRA_SchedulerTask($appConf['scheduled-task'][$key]);
      $results = $task->evaluate();
    }
  }
}
?>
