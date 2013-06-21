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
    echo "Usage: sra-installer.php [id] [locale]\n";
    echo "  where [id] blank (sierra/etc/installer.xml will be used as the configuration)\n";
    echo "  OR the path to an installer XML configuration file\n";
    echo "  OR the ID of an application ([app]/etc/installer.xml will be used as the configuration)\n";
    echo "  and [locale] is an optional custom locale code ([lang] OR [lang]-[country])\n\n";
    exit;
}

$installer = new SRA_Installer();
$installer->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL, isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : NULL);

?>
