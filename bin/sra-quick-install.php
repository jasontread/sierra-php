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
require_once(dirname(dirname(__FILE__)) . '/lib/util/installer/SRA_Installer.php');
// }}}

// Check the command line parameters.
if (isset($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?'))) {
    echo "Usage: sra-quick-install.php [APP_ID]? [UNINSTALL] [SKIP_MODEL]";
    echo "\n  APP_ID:     id of an application to also quick install. this application will";
    echo "\n              be automatically added to sierra-config.xml and if it contains a ";
    echo "\n              script [app]/bin/quick-install.php, that script will also be run";
    echo "\n  UNINSTALL:  set this argument to 1 if APP_ID should be uninstalled instead";
    echo "\n  SKIP_MODEL: set this argument to 1 if the app entity model should not be initialized\n\n";
    exit;
}

SRA_Installer::quickInstall(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL, isset($_SERVER['argv'][2]) && $_SERVER['argv'][2], isset($_SERVER['argv'][3]) && $_SERVER['argv'][3]);

?>
