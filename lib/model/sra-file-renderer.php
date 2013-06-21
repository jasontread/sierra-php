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
require_once(dirname(dirname(realpath(__FILE__))) . '/core/SRA_Controller.php');
require_once(SRA_LIB_DIR . '/model/SRA_FileAttribute.php');
// }}}

// {{{ file-renderer
/**
 * PHP Script used to render a SRA_FileAttribute that uses the file handling type 
 * SRA_FILE_ATTRIBUTE_TYPE_DB (contents of file are stored in the database)
 * For more information, review the SRA_FileAttribute.php class api documentation.
 * Un-authorized access will result in a text/plain mime type file containing 
 * the specified error message
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */

// get the encrypted id
if ($_GET['eid']) {
	$eid = $_GET['eid'];
}
if ($_POST['eid']) {
	$eid = $_POST['eid'];
}

// render file
if ($eid && SRA_FileAttribute::isValid($fileAttribute =& SRA_FileAttribute::getInstanceFromEncryptedId($eid))) {
	header("Content-type: " . $fileAttribute->getType());
	if ($fileAttribute->getSize() > 1024) { header("Content-length: " . $fileAttribute->getSize()); }
	echo $fileAttribute->getBytes();
}

// }}}
?>
