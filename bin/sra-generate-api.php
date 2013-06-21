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

ini_set('max_execution_time', '4800');
$_resources =& SRA_Controller::getSysResources();

// construct directories
$_exclude = array();
$_include = array();
$_generate = $_SERVER['argv'][count($_SERVER['argv']) - 1];
$_recursive = TRUE;
for($i=1; $i<count($_SERVER['argv'])-1; $i++) {
  if (trim($_SERVER['argv'][$i]) == '-e') {
    $_excludeNext = TRUE;
  }
  else if (trim($_SERVER['argv'][$i]) == '-c') {
    $_cssNext = TRUE;
  }
  else if (trim($_SERVER['argv'][$i]) == '-r') {
    $_recursiveNext = TRUE;
  }
  else if (trim($_SERVER['argv'][$i]) == '-t') {
    $_titleNext = TRUE;
  }
  else if ($_excludeNext) {
    $_excludeNext = FALSE;
    $_exclude[] = $_SERVER['argv'][$i];
  }
  else if ($_cssNext) {
    $_cssNext = FALSE;
    $_cssUri = $_SERVER['argv'][$i];
  }
  else if ($_recursiveNext) {
    $_recursiveNext = FALSE;
    $_recursive = $_SERVER['argv'][$i] ? TRUE : FALSE;
  }
  else if ($_titleNext) {
    $_titleNext = FALSE;
    $_title = $_SERVER['argv'][$i];
  }
  else {
    $_include[] = $_SERVER['argv'][$i];
  }
}

// setup defaults
if (!$_exclude && !$_include) {
  $_exclude = array(SRA_LIB_DIR . '/ext');
  $_include = array(SRA_LIB_DIR, SRA_DIR . '/etc__');
}
if (!$_title) {
  $_title = $_resources->getString('api.title') . ' v' . SRA_Controller::getSysVersion();
}

// check the command line parameters
if ($_SERVER['argc'] == 1 || ((!$_include || !$_title || !$_generate) || (isset($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?'))))) {
  echo $_resources->getString('generateapi.usage') . "\n";
  exit;
}

// validate generate dir
if (!is_dir($_generate) || !is_writable($_generate) || ($_gfiles = SRA_File::getFileList($_generate))) {
  echo $_resources->getString('generateapi.error.generate' . ($_gfiles ? '.notEmpty' : ''), array('dir' => $_generate)) . "\n";
  exit;
}

// missing include directory
if (!$_include) {
  echo $_resources->getString('generateapi.error.include.missing') . "\n";
  exit;
}

// validate include dirs
foreach($_include as $_dir) {
  $_dir = SRA_Util::endsWith($_dir, '__') ? substr($_dir, 0, -2) : $_dir;
  if (!is_dir($_dir) || !is_readable($_dir)) {
    echo $_resources->getString('generateapi.error.include', array('dir' => $_dir)) . "\n";
    exit;
  }
}

include_once('util/SRA_ApiGenerator.php');
$api = new SRA_ApiGenerator($_title, $_include, $_exclude, $_cssUri, $_recursive);
$api->generate($_generate);

?>
