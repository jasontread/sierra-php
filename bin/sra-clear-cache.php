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
// }}}

$resources =& SRA_Controller::getSysResources();

// Check the command line parameters.
if (isset($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?'))) {
  echo $resources->getString('clear-cache.usage') . "\n\n";
  exit;
}

foreach(SRA_File::getFileList(SRA_Controller::getSysTmpDir()) as $file) {
  SRA_File::unlink($file);
  echo $resources->getString('clear-cache.file', array('file' => $file)) . "\n";
}

?>
