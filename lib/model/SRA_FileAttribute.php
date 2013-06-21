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

// }}}

// {{{ Constants
/**
 * the default decimal precision for the getSize* methods
 * @type int
 */
define('SRA_FILE_ATTRUBTE_DECIMAL_PRECISION', 2);

/**
 * identifies the "db" "file-handling" method
 * @type string
 */
define('SRA_FILE_ATTRIBUTE_TYPE_DB', 'db');

/**
 * identifies the "dir" "file-handling" method
 * @type string
 */
define('SRA_FILE_ATTRIBUTE_TYPE_DIR', 'dir');

/**
 * prefix for encrypted ids
 * @type string
 */
define('SRA_FILE_ATTRIBUTE_EID_PREFIX', 'sfa://');

/**
 * prefix to identify files that have been reset (will be automatically deleted 
 * when getInstanceFromEncryptedId is invoked (applies only when _fileHandling 
 * is SRA_FILE_ATTRIBUTE_TYPE_DIR)
 * @type string
 */
define('SRA_FILE_ATTRIBUTE_EID_REMOVE_PREFIX', 'REMOVE');

/**
 * the name of the ImageMagick "convert" cli utility used by the "resizeImage" 
 * and "toThumbnail" methods. in order to use those methods, ImageMagick must 
 * be installed and this utility must be present in the $PATH
 * @type string
 */
define('SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT', 'convert');
// }}}

// {{{ SRA_FileAttribute
/**
 * Used to represent an entity model "is-file" attribute. for more information, 
 * see the "entity-model_1_0.dtd". 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_FileAttribute {
  // {{{ Attributes
  // public attributes
	/**
	 * the app key that this SRA_FileAttribute belongs to
	 * @type string
	 * @access private
	 */
	var $_app;
  
	/**
	 * the name of the attribute that this SRA_FileAttribute belongs to
	 * @type string
	 * @access private
	 */
	var $_attribute;
	
	/**
	 * the attribute index for this SRA_FileAttribute (only applicable if the 
	 * attribute is an array)
	 * @type string
	 * @access private
	 */
	var $_attributeIndex;
	
	/**
	 * byte array representation of the file (only appies to files that are stored 
	 * in the database). cannot be set through instantiation.
	 * @type byte[]
	 * @access private
	 */
	var $_bytes;
  
	/**
	 * an optional custom uri for this file (use setCustomUri to set)
	 * @type string
	 * @access private
	 */
	var $_customUri;
	
	/**
	 * the name of the entity that this SRA_FileAttribute belongs to
	 * @type string
	 * @access private
	 */
	var $_entity;
	
	/**
	 * the id of the entity that this SRA_FileAttribute belongs to
	 * @type string
	 * @access private
	 */
	var $_entityId;
	
	/**
	 * if the SRA_FILE_ATTRIBUTE_TYPE_DIR file handling method is used, 
	 * specifies the directory where files should be stored
	 * for more information, see the documentation provided in entity-model_1_0.dtd
	 * @type string
	 * @access private
	 */
	var $_fileDir;
	
	/**
	 * if the SRA_FILE_ATTRIBUTE_TYPE_DIR file handling method is used, 
	 * specifies the uri where files should be referenced from the web server
	 * for more information, see the documentation provided in entity-model_1_0.dtd
	 * @type string
	 * @access private
	 */
	var $_fileDirUri;
	
	/**
	 * determines how this file attribute will be stored. will correspond with 
	 * one of the SRA_FILE_ATTRIBUTE_TYPE_* constants for more information, see 
	 * the documentation provided in entity-model_1_0.dtd
	 * @type string
	 * @access private
	 */
	var $_fileHandling;
	
	/**
	 * whether or not the _fileScriptUri should be rendered as an Apache RewriteRule
	 * for more information, see the documentation provided in entity-model_1_0.dtd
	 * @type string
	 * @access private
	 */
	var $_fileScriptRewrite;
	
	/**
	 * if the SRA_FILE_ATTRIBUTE_TYPE_DB file handling method is used, 
	 * specifies the Uri to the file-renderer.php script
	 * for more information, see the documentation provided in entity-model_1_0.dtd
	 * @type string
	 * @access private
	 */
	var $_fileScriptUri;
	
	/**
	 * the actual name of the file if the SRA_FILE_ATTRIBUTE_TYPE_DIR 
	 * file handling method is used, or a browser friendly name if the 
	 * SRA_FILE_ATTRIBUTE_TYPE_DB file handling method is used.
	 * @type string
	 * @access private
	 */
	var $_fileName;
  
	/**
	 * set this attribute to force this file attribute to use this file path to 
   * retrieve the file bytes (may be remote)
	 * @type string
	 * @access private
	 */
	var $_hardPath;
	
	/**
	 * whether or not existing database SRA_FileAttribute properties have been 
	 * initialized
	 * @type boolean
	 * @access private
	 */
	var $_initialized = FALSE;
	
	/**
	 * the original name of the file that was uploaded (if file was added from an 
	 * uploaded form)
	 * @type string
	 * @access private
	 */
	var $_name;
	
	/**
	 * whether or not the SRA_FileAttribute is new (new SRA_FileAttributes have not been 
	 * committed to the database or file system)
	 * @type boolean
	 * @access private
	 */
	var $_new = FALSE;
	
	/**
	 * the size, in bytes, of the file
	 * @type string
	 * @access private
	 */
	var $_size;
	
	/**
	 * the sql that can be performed on the $_db to return the attributes 
	 * associated with this SRA_FileAttribute. this query will be executed the 
	 * first time that the $_bytes, $_fileName, $_name, or $_size attributes 
	 * are requested through their corresponding getter methods
	 * @type string
	 * @access private
	 */
	var $_sql;
	
	/**
	 * the mime type of the file if the browser provided this information in the 
	 * original file upload
	 * @type string
	 * @access private
	 */
	var $_type;
  
	// private attributes	
	// }}}
  
	// {{{ Operations
	// constructor(s)
	// {{{ SRA_FileAttribute
	/**
	 * Instantiates a new instance of this object with the initial given 
	 * attributes
	 * @access  public
	 */
	function SRA_FileAttribute($fileName = NULL, $name = NULL, $size = NULL, $type = NULL, $new = NULL) {
		$this->setFileName($fileName);
		$this->setName($name);
		$this->setSize($size);
		$this->setType($type);
		$this->_setNew($new);
    $this->_app = SRA_Controller::getCurrentAppId();
	}
	// }}}
	
	
	// public operations
	// {{{ copy
	/**
	 * copies this file attribute
	 * @access  public
	 * @return SRA_FileAttribute
	 */
	function & copy() {
		return SRA_Util::copyObject($this);
	}
	// }}}
  
	// {{{ delete
	/**
	 * deletes the file represented by this SRA_FileAttribute instance IF the 
	 * _fileHandling is SRA_FILE_ATTRIBUTE_TYPE_DIR and the _fileName exists
	 * @access  public
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	function delete() {
		if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DIR && file_exists($this->_fileName)) {
			unlink($this->_fileName);
			return TRUE;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ equals
	/**
	 * Returns TRUE if this SRA_FileAttribute references the same file as the 
	 * $fileAttr parameter
	 * @param SRA_FileAttribute $fileAttr
	 * @access  public
	 * @return string
	 */
	function equals(& $fileAttr) {
		if (SRA_FileAttribute::isValid($fileAttr) && $this->getEncryptedId() == $fileAttr->getEncryptedId()) {
			return TRUE;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getApp
	/**
	 * Returns the _app attribute
	 * @access  public
	 * @return string
	 */
	function getApp() {
		return $this->_app;
	}
	// }}}
  
	// {{{ getAttribute
	/**
	 * Returns the _attribute attribute
	 * @access  public
	 * @return string
	 */
	function getAttribute() {
		return $this->_attribute;
	}
	// }}}
	
	// {{{ getAttributeIndex
	/**
	 * Returns the _attributeIndex attributeIndex
	 * @access  public
	 * @return string
	 */
	function getAttributeIndex() {
		return $this->_attributeIndex;
	}
	// }}}
	
	// {{{ getBytes
	/**
	 * Returns the _bytes attribute (returned by reference)
	 * @access  public
	 * @return byte[]
	 */
	function & getBytes() {
		$this->_init();
    if ($this->_hardPath && !isset($this->_bytes)) {
      $this->_bytes = file_get_contents($this->_hardPath);
    }
		return $this->_bytes;
	}
	// }}}
	
	// {{{ getData
	/**
	 * Returns an appropriately serialized version of this SRA_FileAttribute which 
	 * can be stored in the database column for the attribute that it pertains to. 
	 * Returns an SRA_Error object, if unsuccessful
	 * @param string $fileHandling see the $_fileHandling api comments (REQUIRED 
	 * if not already set)
	 * @param string $fileDir see the $_fileDir api comments (REQUIRED for 
	 * SRA_FILE_ATTRIBUTE_TYPE_DIR type SRA_FileAttribute instances if not already 
	 * set)
	 * @access  public
	 */
	function & getData($fileHandling = FALSE, $fileDir = FALSE) {
		if ($fileHandling) {
			$this->_setFileHandling($fileHandling);
		}
		if ($fileDir) {
			$this->_setFileDir($fileDir);
		}
		// new SRA_FileAttribute
		if ($this->isNew()) {
			if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
				if (file_exists($this->getFileName()) && is_readable($this->getFileName()) && !$this->_bytes) {
					$fp = fopen($this->getFileName(), 'rb');
					$this->_bytes = fread($fp, $this->getSize());
          $this->setSize(strlen($this->_bytes));
					fclose($fp);
				}
				else if (!$this->_bytes) {
					$msg = 'SRA_FileAttribute::getData: Failed - No _bytes exist for this file';
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
			// copy file to correct storage directory if necessary
			else if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DIR) {
				if (file_exists($this->getFileName()) && is_readable($this->getFileName()) && is_writable($this->_fileDir)) {
					$newFile = SRA_File::createRandomFile($this->_fileDir, SRA_Util::getFileNameWOExtension($this->getName(), TRUE), '.' . SRA_Util::getFileExtension($this->getName(), TRUE));
					SRA_File::move($this->getFileName(), $newFile);
					$this->_fileName = $newFile;
				}
				else {
					$msg = 'SRA_FileAttribute::getData: Failed - Either file does not exist or is not readable: ' . $this->getFileName() . ' or directory is not writable: ' . $this->_fileDir ;
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
			$this->_setNew(FALSE);
		}
		// existing SRA_FileAttribute
		else {
			$this->_init();
		}
		return serialize($this);
	}
	// }}}
	
	// {{{ getAppDbData
	/**
	 * Returns the data for this SRA_FileAttribute properly converted for insertion 
	 * into the current app database
	 * @access  public
	 * @return string
	 */
	function & getAppDbData(& $db) {
		if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
			return $db->convertBlob($this->getData());
		}
		else if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DIR) {
			return $db->convertText($this->getData());
		}
    
		return FALSE;
	}
	// }}}
	
	// {{{ getAppDbDataType
	/**
	 * Returns the correct database data type for this SRA_FileAttribute. This is 
	 * SRA_DATA_TYPE_BLOB if the _fileHandling is SRA_FILE_ATTRIBUTE_TYPE_DB 
	 * and SRA_DATA_TYPE_STRING if the _fileHandling is 
	 * SRA_FILE_ATTRIBUTE_TYPE_DIR
	 * @access  public
	 * @return string
	 */
	function getAppDbDataType() {
		if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
			return SRA_DATA_TYPE_BLOB;
		}
		else if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DIR) {
			return SRA_DATA_TYPE_STRING;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getEncryptedId
	/**
	 * Returns a unique encrypted identifier for this file attribute if the 
	 * SRA_FILE_ATTRIBUTE_TYPE_DB file handling method is used
   * @param boolean $encode whether or not to url encode the ecrypted id
	 * @access  public
	 * @return string
	 */
	function getEncryptedId($encode=TRUE) {
		$str = base64_encode(SRA_FILE_ATTRIBUTE_EID_PREFIX . $this->_app . '>' . $this->_entity . '>' . $this->_entityId . '>' . $this->_attribute . '>' . $this->_attributeIndex . '>' . $this->_fileDir . '>' . $this->_fileDirUri . '>' . $this->_fileHandling . '>' . $this->_fileName . '>' . $this->_size . '>' . $this->_type);
    return $encode ? urlencode($str) : $str;
	}
	// }}}
	
	// {{{ getEntity
	/**
	 * Returns the _entity entity
	 * @access  public
	 * @return string
	 */
	function getEntity() {
		return $this->_entity;
	}
	// }}}
	
	// {{{ getEntityId
	/**
	 * Returns the _entityId entityId
	 * @access  public
	 * @return string
	 */
	function getEntityId() {
		return $this->_entityId;
	}
	// }}}
	
	// {{{ getFileExtension
	/**
	 * Returns the file extension for this file
	 * @access  public
	 * @return string
	 */
	function getFileExtension() {
		if (strstr($this->getFileName(), '.')) {
			return SRA_Util::getFileExtension($this->getFileName());
		}
		else if (strstr($this->getName(), '.')){
			return SRA_Util::getFileExtension($this->getName());
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getFileDir
	/**
	 * Returns the file extension for this file
	 * @access  public
	 * @return string
	 */
	function getFileDir() {
		return $this->_fileDir;
	}
	// }}}
	
	// {{{ getFileDirUri
	/**
	 * Returns the file extension for this file
	 * @access  public
	 * @return string
	 */
	function getFileDirUri() {
		return $this->_fileDirUri;
	}
	// }}}
	
	// {{{ getFileHandling
	/**
	 * Returns the file extension for this file
	 * @access  public
	 * @return string
	 */
	function getFileHandling() {
		return $this->_fileHandling;
	}
	// }}}
	
	// {{{ getFileScriptUri
	/**
	 * Returns the file extension for this file
	 * @access  public
	 * @return string
	 */
	function getFileScriptUri() {
		return $this->_fileScriptUri;
	}
	// }}}
	
	// {{{ getFileName
	/**
	 * Returns the _fileName attribute
	 * @access  public
	 * @return string
	 */
	function getFileName() {
		$this->_init();
		return $this->_fileName;
	}
	// }}}
  
	// {{{ getHardPath
	/**
	 * Returns the _hardPath attribute
	 * @access  public
	 * @return string
	 */
	function getHardPath() {
		return $this->_hardPath;
	}
	// }}}
  
	// {{{ getIcon
	/**
	 * returns the full path to the icon to use for this file based on it's mime 
   * type if found in $baseDir. this algorithm uses the following search order:
   *  1) check for [file extension].(png|jpg|gif)
   *  2) explode mimeType on '/', lowercase both pieces and remove leading 'x-'
   *  3) check for mimeType[1].(png|jpg|gif)
   *  4) check for mimeType[0].(png|jpg|gif)
   *  5) check for source_[file extension].(png|jpg|gif)
   *  6) remove trailing 's' from extension if applicable and check for source_[file extension].(png|jpg|gif)
   *  6) return NULL
   * @param string $baseDir the base directory containing the icon images. this 
   * directory may be either absolute or app relative
	 * @access  public
	 * @return string
	 */
	function getIcon($baseDir) {
    // convert app relative directory
    if (!file_exists($baseDir) && file_exists(SRA_Controller::getAppDir() . '/' . $baseDir)) { $baseDir = SRA_Controller::getAppDir() . '/' . $baseDir; }
    
    $ext = strtolower(SRA_Util::getFileExtension($this->getName()));
    $mimePieces = explode('/', $this->getType());
    if (count($mimePieces) == 2) {
      $mimePieces[0] = strtolower($mimePieces[0]);
      $mimePieces[1] = str_replace('x-', '', strtolower($mimePieces[1]));
    }
    else {
      $mimePieces = NULL;
    }
    $baseDir = SRA_Util::endsWith($baseDir, '/') ? $baseDir : $baseDir . '/';
    $file = $ext ? (file_exists($baseDir . $ext . '.png') ? $baseDir . $ext . '.png' : (file_exists($baseDir . $ext . '.jpg') ? $baseDir . $ext . '.jpg' : (file_exists($baseDir . $ext . '.gif') ? $baseDir . $ext . '.gif' : NULL))) : NULL;
    $file = !$file && $mimePieces ? (file_exists($baseDir . $mimePieces[1] . '.png') ? $baseDir . $mimePieces[1] . '.png' : (file_exists($baseDir . $mimePieces[1] . '.jpg') ? $baseDir . $mimePieces[1] . '.jpg' : (file_exists($baseDir . $mimePieces[1] . '.gif') ? $baseDir . $mimePieces[1] . '.gif' : NULL))) : $file;
    $file = !$file && $mimePieces ? (file_exists($baseDir . $mimePieces[0] . '.png') ? $baseDir . $mimePieces[0] . '.png' : (file_exists($baseDir . $mimePieces[0] . '.jpg') ? $baseDir . $mimePieces[0] . '.jpg' : (file_exists($baseDir . $mimePieces[0] . '.gif') ? $baseDir . $mimePieces[0] . '.gif' : NULL))) : $file;
    $file = !$file && $ext ? (file_exists($baseDir . 'source_' . $ext . '.png') ? $baseDir . 'source_' . $ext . '.png' : (file_exists($baseDir . 'source_' . $ext . '.jpg') ? $baseDir . 'source_' . $ext . '.jpg' : (file_exists($baseDir . 'source_' . $ext . '.gif') ? $baseDir . 'source_' . $ext . '.gif' : NULL))) : $file;
    $ext = !$file && SRA_Util::endsWith($ext, 's') ? substr($ext, 0, -1) : $ext;
    $file = !$file && $ext ? (file_exists($baseDir . 'source_' . $ext . '.png') ? $baseDir . 'source_' . $ext . '.png' : (file_exists($baseDir . 'source_' . $ext . '.jpg') ? $baseDir . 'source_' . $ext . '.jpg' : (file_exists($baseDir . 'source_' . $ext . '.gif') ? $baseDir . 'source_' . $ext . '.gif' : NULL))) : $file;
    $file = !$file ? (file_exists($baseDir . 'unknown.png') ? $baseDir . 'unknown.png' : (file_exists($baseDir . 'unknown.jpg') ? $baseDir . 'unknown.jpg' : (file_exists($baseDir . 'unknown.gif') ? $baseDir . 'unknown.gif' : NULL))) : $file;
    return $file;
	}
	// }}}
	
	// {{{ getName
	/**
	 * Returns the _name attribute
	 * @access  public
	 * @return string
	 */
	function getName() {
		$this->_init();
		return $this->_name;
	}
	// }}}
	
	// {{{ getSize
	/**
	 * Returns the _size attribute
	 * @access public
	 * @return int
	 */
	function getSize() {
		if (method_exists($this, '_init')) { $this->_init(); }
		return $this->_size;
	}
	// }}}
	
	// {{{ getSizeKb
	/**
	 * Returns the size of the file in KB
	 * @param int $decimalPrecision the decimal precision for the return value
   * @param float $size this method can be invoked statically by specifying this 
   * parameter which is the size in bytes
   * @access public
	 * @return float
	 */
	function getSizeKb($decimalPrecision = SRA_FILE_ATTRUBTE_DECIMAL_PRECISION, $size=NULL) {
    $size = isset($size) ? $size : (method_exists($this, 'getSize') ? $this->getSize() : NULL);
		return number_format($size/1024, $decimalPrecision);
	}
	// }}}
  
	// {{{ getSizeLabel
	/**
	 * returns the size label to use for this file attribute (or for $size if size
   * is specified). this label will be in gigabytes if > 1 gigabytes, megabytes 
   * if > 1 megabytes, kilobytes if > 1 kilobytes or bytes otherwise
	 * @param int $decimalPrecision the decimal precision for the return value
   * @param float $size this method can be invoked statically by specifying this 
   * parameter which is the size in bytes
	 * @access public
	 * @return string
	 */
	function getSizeLabel($decimalPrecision = SRA_FILE_ATTRUBTE_DECIMAL_PRECISION, $size=NULL) {
		$resources =& SRA_Controller::getSysResources();
    $size = isset($size) ? $size : (method_exists($this, 'getSize') ? $this->getSize() : NULL);
    $label = $size . ' ' . $resources->getString('text.size.bytes');
    if (($gb = SRA_FileAttribute::getSizeGb($decimalPrecision, $size)) > 1) {
      $label = $gb . ' ' . $resources->getString('text.size.gigabytes');
    }
    else if (($mb = SRA_FileAttribute::getSizeMb($decimalPrecision, $size)) > 1) {
      $label = $mb . ' ' . $resources->getString('text.size.megabytes');
    }
    else if (($kb = SRA_FileAttribute::getSizeKb($decimalPrecision, $size)) > 1) {
      $label = $kb . ' ' . $resources->getString('text.size.kilobytes');
    }
    return $label;
	}
	// }}}
	
	// {{{ getSizeMb
	/**
	 * Returns the size of the file in MB
	 * @param int $decimalPrecision the decimal precision for the return value
   * @param float $size this method can be invoked statically by specifying this 
   * parameter which is the size in bytes
	 * @access public
	 * @return float
	 */
	function getSizeMb($decimalPrecision = SRA_FILE_ATTRUBTE_DECIMAL_PRECISION, $size=NULL) {
    $size = isset($size) ? $size : (method_exists($this, 'getSize') ? $this->getSize() : NULL);
		return number_format($size/1048576, $decimalPrecision);
	}
	// }}}
  
	// {{{ getSizeGb
	/**
	 * Returns the size of the file in GB
	 * @param int $decimalPrecision the decimal precision for the return value
   * @param float $size this method can be invoked statically by specifying this 
   * parameter which is the size in bytes
	 * @access public
	 * @return float
	 */
	function getSizeGb($decimalPrecision = SRA_FILE_ATTRUBTE_DECIMAL_PRECISION, $size=NULL) {
    $size = isset($size) ? $size : (method_exists($this, 'getSize') ? $this->getSize() : NULL);
		return number_format($size/1073741824, $decimalPrecision);
	}
	// }}}
	
	// {{{ getSql
	/**
	 * Returns the _sql attribute
	 * @access  public
	 * @return string
	 */
	function getSql() {
		return $this->_sql;
	}
	// }}}
	
	// {{{ getType
	/**
	 * Returns the _type attribute
	 * @access  public
	 * @return string
	 */
	function getType() {
		$this->_init();
		return $this->_type;
	}
	// }}}
	
	// {{{ getUri
	/**
	 * Returns the _uri attribute
	 * @access  public
	 * @return string
	 */
	function getUri() {
    if ($this->_customUri) {
      return $this->_customUri;
    }
		else if (!$this->isNew()) {
			// uri is to file-renderer.php wo/ RewriteRule script
			if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB && !$this->isFileScriptRewrite()) {
				return $this->_fileScriptUri . '/?eid=' . $this->getEncryptedId() . '/' . $this->getName();
			}
			// uri is to file-renderer.php w/ RewriteRule script
			if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
				return $this->_fileScriptUri . '/' . $this->getEncryptedId() . '/' . $this->getName();
			}
			// uri is to actual file
			else {
				return $this->_fileDirUri . '/' . basename($this->getFileName());
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ isFileScriptRewrite
	/**
	 * Returns the _fileScriptRewrite attribute
	 * @access  public
	 * @return boolean
	 */
	function isFileScriptRewrite() {
		return $this->_fileScriptRewrite;
	}
	// }}}
  
	// {{{ isImage
	/**
	 * Returns true if this file has an image mime type
	 * @access  public
	 * @return boolean
	 */
	function isImage() {
		return SRA_Util::beginsWith($this->getType(), 'image');
	}
	// }}}
	
	// {{{ isNew
	/**
	 * Returns the _new attribute
	 * @access  public
	 * @return boolean
	 */
	function isNew() {
		return $this->_new;
	}
	// }}}
  
	// {{{ isText
	/**
	 * Returns true if this file has a text mime type
	 * @access  public
	 * @return boolean
	 */
	function isText() {
		return SRA_Util::beginsWith($this->getType(), 'text');
	}
	// }}}
  
	// {{{ resizeImage
	/**
	 * if this file attribute represents an image, this method may be used to 
   * resize that image. in order to use this functionality, ImageMagick must be 
   * installed and executable (see SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT 
   * constant). at least 1, $height or $width must be specified. the return 
   * value will be the path to a temp file stored the resized image
	 * @param int $height the height of the thumbnail to generate
   * @param int $width the width of the thumbnail to generate
   * @param boolean $preserveAspect whether or not to preserve the aspect ratio 
   * when the resized image is created. if false, and both height and width are 
   * specified, the resized image will be modified in such a way to create the 
   * aspect ratio prescribed by those dimensions
	 * @access  public
	 * @return string
	 */
	function resizeImage($height, $width, $preserveAspect=TRUE) {
    if (($height || $width) && $this->isImage() && ($imageMagick = SRA_File::findInPath(SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT))) {
      $inputFile = SRA_File::createRandomFile(NULL, '', '.' . SRA_Util::getFileExtension($this->getName()));
      $outputFile = SRA_File::createRandomFile(NULL, '', '.' . SRA_Util::getFileExtension($this->getName()));
      $this->writeToFile($inputFile);
      
      $cmd = $imageMagick . ' -resize ' . ($width ? $width : '1600') . 'x' . ($height ? $height : '1200') . (!$preserveAspect ? '\!' : '') . ' ' . $inputFile . ' ' . $outputFile;
      system($cmd);
      
      SRA_File::unlink($inputFile);
      return $outputFile;
    }
	}
	// }}}
	
	// {{{ setBytes
	/**
	 * Sets the _bytes attribute
	 * @param string $bytes the bytes attribute to set (passed by reference)
	 * @access  public
	 * @return void
	 */
	function setBytes(& $bytes) {
		$this->_bytes =& $bytes;
	}
	// }}}
  
	// {{{ setCustomUri
	/**
	 * this method can be used to set a custom uri for this file
	 * @param string $uri the uri to set
	 * @access public
	 * @return void
	 */
	function setCustomUri($uri) {
		$this->_customUri = $uri;
	}
	// }}}
	
	// {{{ setFileName
	/**
	 * Sets the _fileName attribute
	 * @param string $fileName the fileName attribute to set
	 * @access  public
	 * @return void
	 */
	function setFileName($fileName) {
		$this->_fileName = $fileName;
	}
	// }}}
  
	// {{{ setHardPath
	/**
	 * sets a custom hard path for this file attribute. when set, this file will 
   * be used when the bytes are retrieved
	 * @param string $hardPath the path to set - may be remote
	 * @access public
	 * @return void
	 */
	function setHardPath($hardPath) {
		$this->_hardPath = $hardPath;
	}
	// }}}
	
	// {{{ setName
	/**
	 * Sets the _name attribute
	 * @param string $name the name attribute to set
	 * @access  public
	 * @return void
	 */
	function setName($name) {
		$this->_name = $name;
	}
	// }}}
	
	// {{{ setSize
	/**
	 * Sets the _size attribute
	 * @param int $size the size attribute to set
	 * @access  public
	 * @return void
	 */
	function setSize($size) {
		$this->_size = $size;
	}
	// }}}
	
	// {{{ setType
	/**
	 * Sets the _type attribute
	 * @param string $type the type attribute to set
	 * @access  public
	 * @return void
	 */
	function setType($type) {
		$this->_type = $type;
	}
	// }}}
  
	// {{{ toText
	/**
	 * this method is used to convert this file attribute to an indexable text 
   * representation when it is possible to do so. if a file uses a "text/*" 
   * mime-type (and that mime-type does not have a custom converter defined) the 
   * contents of that file will be returned directly. other mime-types require 
   * custom text converters. these can be defined in sierra-config.xml using the 
   * "param" element where the type is "file-converter", the id is a space 
   * separated list of mime-types supported by that converter (as defined in 
   * /etc/mime.types), and the value is the cli command for invoking that 
   * converter containing 2 tokens. the first is "${input}" which is the name of 
   * the file being input (STDIN is not supported) to the converter (a temp file 
   * created when this method is invoked), and the second is "${output}" which 
   * is the name of a temporary output file that the text will be written to 
   * (STDOUT is not supported, use > if the converter application does not 
   * support direct file output). When this method is invoked and a valid 
   * converter is found, the file will first be written to a temp file, an 
   * output file will be created, the converter will be invoked, and the output 
   * of that converter will be returned (both temp files are also deleted). 
   * these are a few examples of text converters defined in sierra-config.xml:
   *  <!-- PDF conversion using Xpdf (http://www.foolabs.com/xpdf/) -->
   *  <param id="application/pdf" type="file-converter" value="/usr/local/bin/pdftotext ${input} ${output}" />
	 * @access  public
	 * @return string
	 */
	function & toText() {
    if ($this->_type) {
      $converters = SRA_Controller::getSysParams(NULL, 'file-converter');
      foreach($converters as $mimeTypes => $converter) {
        $mimeTypes = explode(' ', $mimeTypes);
        if (in_array($this->_type, $mimeTypes)) {
          $input = SRA_File::createRandomFile();
          $this->writeToFile($input);
          $output = SRA_File::createRandomFile();
          $cmd = str_replace('${output}', $output, str_replace('${input}', $input, $converter));
          system($cmd);
          $text =& SRA_File::toString($output);
          SRA_File::unlink($input);
          SRA_File::unlink($output);
          return $text;
        }
      }
      // text mime type
      if (SRA_Util::beginsWith($this->_type, 'text/', FALSE)) {
        return $this->getBytes();
      }
    }
    $es = '';
    return $es;
	}
	// }}}
  
	// {{{ toThumbnail
	/**
	 * used to create a thumbnail image representation of this file where 
   * supported. in order to use this method, the ImageMagick utility 
   * SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT must be found in the path. 
   * Additionally, the file mime-type must be supported for conversion. the 
   * supported types include the image formats listed at 
   * http://www.imagemagick.org/script/formats.php as well as any other formats 
   * that can be converted to a supported format by a custom converter. These 
   * converters can be defined in sierra-config.xml using the "param" element 
   * where the type is "thumbnail-converter", the id is a space separated list 
   * of mime-types supported by that converter (as defined in /etc/mime.types), 
   * and the value is the cli command for invoking that converter containing 2 
   * tokens. the first is "${input}" which is the name of the original file  
   * (STDIN is not supported) for the converter (a temp file created when this 
   * method is invoked), and the second is "${output}" which is the name of a 
   * temporary output file that the new file will be written to (STDOUT is not 
   * supported, use > if the converter application does not support direct file 
   * output). if both height and width are specific, the aspect ratio of the 
   * source file may be changed as the resulting thumbnail will be generated 
   * using those exact dimensions. if either $height or $width or specified, 
   * then that will be the exact size for that dimension while the opposite 
   * dimension will be proportional according to the aspect ratio. at least 1, 
   * $height or $width MUST be provided. if multiple thumbnails are generated 
   * (i.e. if the file is a pdf will multiple pages), then the return value 
   * will be an array of file paths. otherwise, when successful a single file 
   * path will be returned. if a thumbnail could not be created, NULL will be 
   * returned
   * 
	 * @param int $height the height of the thumbnail to generate
   * @param int $width the width of the thumbnail to generate
   * @param string $format the format of the thumbnail to create. the default 
   * format is 'png'. alternate formats include 'gif', 'jpg' and any other 
   * output format (identified by it's file extension) supported by ImageMagick
   * @param boolean $preserveAspect whether or not to preserve the aspect ratio 
   * when the thumbnail is created. if false, and both height and width are 
   * specified, the thumbnail image will be modified in such a way to create the 
   * aspect ratio prescribed by those dimensions
   * @param array $includeTypes an array of regular expressions defining the 
   * mime-types for which thumbnails are allowed. if this file mime-type is not 
   * in that array, no thumbnail will be created
   * @param array $skipTypes an array of regular expressions defining the 
   * mime-types for which thumbnails are NOT allowed. if this file mime-type is  
   * in that array, no thumbnail will be created
	 * @access  public
	 * @return mixed
	 */
	function toThumbnail($height, $width, $format='png', $preserveAspect=TRUE, $includeTypes=NULL, $skipTypes=NULL) {
    $include = TRUE;
    if ($includeTypes) {
      $include = FALSE;
      foreach($includeTypes as $match) {
        if (ereg($match, $this->_type)) {
          $include = TRUE;
          break;
        }
      }
    }
    if ($skipTypes) {
      foreach($skipTypes as $match) {
        if (ereg($match, $this->_type)) {
          $include = FALSE;
          break;
        }
      }
    }
    if ($include && ($height || $width) && $this->_type && ($imageMagick = SRA_File::findInPath(SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT))) {
      $converters = SRA_Controller::getSysParams(NULL, 'thumbnail-converter');
      foreach($converters as $mimeTypes => $converter) {
        $mimeTypes = explode(' ', $mimeTypes);
        if (in_array($this->_type, $mimeTypes)) {
          $input = SRA_File::createRandomFile();
          $this->writeToFile($input);
          $inputFile = SRA_File::createRandomFile();
          $cmd = str_replace('${output}', $inputFile, str_replace('${input}', $input, $converter));
          exec($cmd);
          SRA_File::unlink($input);
          break;
        }
      }
      if (!file_exists($inputFile)) { 
        $inputFile = SRA_File::createRandomFile();
        $this->writeToFile($inputFile);
      }
      $file = SRA_File::createRandomFile(NULL, NULL, '.' . $format);
      $cmd = $imageMagick  . ' -thumbnail ' . ($width ? $width : '1600') . 'x' . ($height ? $height : '1200') . (!$preserveAspect ? '\!' : '') . ' ' . $inputFile . '[0] ' . $file;
      exec($cmd);
      SRA_File::unlink($inputFile);
      $multipageFile = substr($file, 0, strlen($file) - strlen($format) - 1) . '-{$n}.' . $format;
      if (!filesize($file)) { $file = NULL; }
      return $file;
    }
    $nl = NULL;
    return $nl;
	}
	// }}}
  
	// {{{ toXmlArray
	/**
	 * returns this file attribute in the form of an associative array containing 
   * 2 nested values: 'attributes': an array containing 3 sub-keys: 'name', 
   * 'size' and 'type', and $fileKey containing the base64 encoding of the bytes 
   * representing this file. this value will be preceded by "<![CDATA[" and 
   * postceded by "]]>"
	 * @access  public
	 * @return array
	 */
	function toXmlArray() {
		$arr = array('attributes' => array('name' => $this->getName(), 'size' => $this->getSize(), 'type' => $this->getType(), 'uri' => $this->getUri()));
    return $arr;
	}
	// }}}
  
  
	// {{{ writeToFile
	/**
	 * this method writes the bytes of this file to the new file name specified
	 * @param string $file the full path to the file to write to
	 * @access  public
	 * @return void
	 */
	function writeToFile($file) {
    if (!$bytes =& $this->getBytes()) {
			$msg = "SRA_FileAttribute::writeToFile: Failed - file contains no data";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
		if (!($fp = fopen($file, 'w'))) {
			$msg = "SRA_FileAttribute::writeToFile: Failed - id ${file} is not writeable";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    fwrite($fp, $bytes);
    fclose($fp);
	}
	// }}}
	
	
	// private operations
	
	// {{{ _init
	/**
	 * Initializes the basic SRA_FileAttribute properties $_bytes (if applicable), 
	 * $_fileName, $_name, $_size and $_type based on the database query ($_sql) 
	 * specified when this SRA_FileAttribute was initialized. Returns TRUE if 
	 * successful, FALSE otherwise
	 * @access  private
	 * @return boolean
	 */
	function _init() {
		if (!$this->_initialized && !$this->isNew() && $this->_app && $this->_sql && !$this->_hardPath) {
			$currentApp = SRA_Controller::getCurrentAppId();
			SRA_Controller::init($this->_app);
			if (SRA_Database::isValid($db =& SRA_Controller::getAppDb())) {
				if (!SRA_Error::isError($results =& $db->fetch($this->_sql, array(SRA_DATA_TYPE_BLOB)))) {
					if ($row =& $results->next()) {
						$results = $this->_initData($row[0]);
					}
				}
			}
			SRA_Controller::init($currentApp);
		}
		if ($results) {
			return $results;
		}
		else {
			return FALSE;
		}
	}
	// }}}
	
	// {{{ _initData
	/**
	 * Initializes the basic SRA_FileAttribute properties $_bytes (if applicable), 
	 * $_fileName, $_name, $_size and $_type from the given $data extracted from 
	 * the corresponding database column for the file. returns TRUE if successful 
	 * FALSE otherwise
	 * @param byte[] $data the data containing those properties
	 * @access  private
	 * @return boolean
	 */
	function _initData(& $data) {
		if (SRA_FileAttribute::isValid($fileAttribute =& SRA_FileAttribute::getInstanceFromData($data))) {
			if ($fileAttribute->getBytes()) {
				$this->setBytes($fileAttribute->getBytes());
			}
			$this->setFileName($fileAttribute->getFileName());
			$this->setName($fileAttribute->getName());
			$this->setSize($fileAttribute->getSize());
			$this->setType($fileAttribute->getType());
			$this->_setInitialized(TRUE);
			$this->_setNew(FALSE);
			return TRUE;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ _setAttribute
	/**
	 * Sets the _attribute attribute
	 * @param string $attribute see the $_attribute api comments
	 * @access  private
	 */
	function _setAttribute($attribute) {
		$this->_attribute = $attribute;
	}
	// }}}
	
	// {{{ _setAttributeIndex
	/**
	 * Sets the _attributeIndex attributeIndex
	 * @param string $attributeIndex see the $_attributeIndex api comments
	 * @access  private
	 */
	function _setAttributeIndex($attributeIndex) {
		$this->_attributeIndex = $attributeIndex;
	}
	// }}}
	
	// {{{ _setEntity
	/**
	 * Sets the _entity attribute
	 * @param string $entity see the $_entity api comments
	 * @access  private
	 */
	function _setEntity($entity) {
		$this->_entity = $entity;
	}
	// }}}
	
	// {{{ _setEntityId
	/**
	 * Sets the _entityId attribute
	 * @param string $entityId see the $_entityId api comments
	 * @access  private
	 */
	function _setEntityId($entityId) {
		$this->_entityId = $entityId;
	}
	// }}}
	
	// {{{ _setFileDir
	/**
	 * Sets the _fileDir attribute
	 * @param string $fileDir the fileDir attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setFileDir($fileDir) {
		if (is_dir($fileDir) && is_writable($fileDir)) {
			$this->_fileDir = $fileDir;
		}
	}
	// }}}
	
	// {{{ _setFileDirUri
	/**
	 * Sets the _fileDirUri attribute
	 * @param string $fileDirUri the fileDirUri attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setFileDirUri($fileDirUri) {
		$this->_fileDirUri = $fileDirUri;
	}
	// }}}
	
	// {{{ _setFileHandling
	/**
	 * Sets the _fileHandling attribute
	 * @param string $fileHandling see the $_fileHandling api comments
	 * @access  private
	 */
	function _setFileHandling($fileHandling) {
		if (SRA_FileAttribute::fileHandlingMethodIsValid($fileHandling)) {
			$this->_fileHandling = $fileHandling;
			return TRUE;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ _setFileScriptRewrite
	/**
	 * Sets the _fileScriptRewrite attribute
	 * @param string $fileScriptRewrite the fileScriptRewrite attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setFileScriptRewrite($fileScriptRewrite) {
		$this->_fileScriptRewrite = $fileScriptRewrite;
	}
	// }}}
	
	// {{{ _setFileScriptUri
	/**
	 * Sets the _fileScriptUri attribute
	 * @param string $fileScriptUri the fileScriptUri attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setFileScriptUri($fileScriptUri) {
		$this->_fileScriptUri = $fileScriptUri;
	}
	// }}}
	
	// {{{ _setInitialized
	/**
	 * Sets the _initialized attribute
	 * @param string $initialized the initialized attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setInitialized($initialized) {
		$this->_initialized = $initialized;
	}
	// }}}
	
	// {{{ _setNew
	/**
	 * Sets the _new attribute
	 * @param string $new the new attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setNew($new) {
		$this->_new = $new;
	}
	// }}}
	
	// {{{ _setApp
	/**
	 * Sets the _app attribute
	 * @param string $app the app attribute to _set
	 * @access  private
	 * @return void
	 */
	function _setApp($app) {
		$this->_app = $app;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ getInstanceFromDb
	/**
	 * Creates a new instance of SRA_FileAttribute given the following database 
	 * instance criteria
	 * @param byte[] $data can be either an sql query (beginning with SELECT) that 
	 * can be used to retrieve the actual properties of this SRA_FileAttribute, or the 
	 * actual data from the database column itself for the SRA_FileAttribute
	 * @param string $attribute see the $_attribute api comments
	 * @param string $attributeIndex see the $_attributeIndex api comments
	 * @param string $entity see the $_entity api comments
	 * @param string $entityId see the $_entityId api comments
	 * @param string $fileDir see the $_fileDir api comments
	 * @param string $fileDirUri see the $_fileDirUri api comments
	 * @param string $fileHandling see the $_fileHandling api comments
	 * @param string $fileScriptRewrite see the $_fileScriptRewrite api comments
	 * @param string $fileScriptUri see the $_fileScriptUri api comments
	 * @access  public
	 * @return SRA_FileAttribute or SRA_Error
	 */
	function & getInstanceFromDb(& $data, $attribute, $attributeIndex, $entity, $entityId, $fileDir, $fileDirUri, $fileHandling, $fileScriptRewrite, $fileScriptUri) {
		// data is a query
		if (strpos(trim(strtolower($data)), 'select') === 0) {
			$fileAttribute = new SRA_FileAttribute();
			$fileAttribute->_sql = $data;
		}
		// data is actual data from the database
		else if (!SRA_FileAttribute::isValid($fileAttribute =& SRA_FileAttribute::getInstanceFromData($data))) {
			return FALSE;
		}
		$fileAttribute->_setAttribute($attribute);
		$fileAttribute->_setAttributeIndex($attributeIndex);
		$fileAttribute->_setEntity($entity);
		$fileAttribute->_setEntityId($entityId);
		$fileAttribute->_setFileDir($fileDir);
		$fileAttribute->_setFileDirUri($fileDirUri);
		$fileAttribute->_setFileHandling($fileHandling);
		$fileAttribute->_setFileScriptRewrite($fileScriptRewrite);
		$fileAttribute->_setFileScriptUri($fileScriptUri);
		$fileAttribute->_setNew(FALSE);
		$fileAttribute->_setApp(SRA_Controller::getCurrentAppId());
		return $fileAttribute;
	}
	// }}}
	
	// {{{ getInstanceFromData
	/**
	 * Initializes and returns a SRA_FileAttribute object from serialized data. 
	 * returns an FALSE if the serialized data is not a SRA_FileAttribute 
	 * instance
	 * @param byte[] $data the serialized SRA_FileAttribute instance
	 * @access  public
	 * @return SRA_FileAttribute
	 */
	function & getInstanceFromData(& $data) {
		if (SRA_FileAttribute::isValid($fileAttribute = unserialize($data))) {
			return $fileAttribute;
		}
		else {
			return FALSE;
		}
	}
	// }}}
	
	// {{{ getInstanceFromEncryptedId
	/**
	 * Creates a new instance of SRA_FileAttribute based on an encrypted id generated 
	 * by the getEncryptedId method. Returns FALSE if the file was removed
	 * @param string $id the encrypted id to evaluate
	 * @param string $fileDir the directory where this file SHOULD exist. this 
	 * parameter should be specified if SRA_FILE_ATTRIBUTE_TYPE_DIR file handling 
	 * is used to avoid potential security risks. ONLY files in that directory 
	 * will be instantiated or removed
	 * @access  public
	 * @return SRA_FileAttribute, FALSE or SRA_Error
	 */
	function getInstanceFromEncryptedId($id, $fileDir = FALSE) {
		$removeFile = FALSE;
    $id = str_replace(' ', '+', $id);
		if (strstr($id, SRA_FILE_ATTRIBUTE_EID_REMOVE_PREFIX)) {
			$id = str_replace(SRA_FILE_ATTRIBUTE_EID_REMOVE_PREFIX, '', $id);
			$removeFile = TRUE;
		}
		$data = base64_decode($id);
		if (!strstr($data, SRA_FILE_ATTRIBUTE_EID_PREFIX)) {
			$msg = "SRA_FileAttribute::getInstanceFromEncryptedId: Failed - id ${id} is not appropriately prefixed: " . SRA_FILE_ATTRIBUTE_EID_PREFIX;
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$data = explode('>', str_replace(SRA_FILE_ATTRIBUTE_EID_PREFIX, '', $data));
		if (count($data) != 11) {
			$msg = "SRA_FileAttribute::getInstanceFromEncryptedId: Failed - id ${id} does not have appropriate # of elements (11): " . count($data);
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		if ($data[7] == SRA_FILE_ATTRIBUTE_TYPE_DB) {
			$currentApp = SRA_Controller::getCurrentAppId();
			SRA_Controller::init($data[0]);
			if (SRA_Error::isError($dao =& SRA_DaoFactory::getDao($data[1]))) {
				SRA_Controller::init($currentApp);
				return $dao;
			}
			if (SRA_Error::isError($entity =& $dao->findByPk($data[2]))) {
				SRA_Controller::init($currentApp);
				return $entity;
			}
      if (is_array($entity)) {
        $entity =& $entity[0];
      }
      
			$attrs = explode('#', $data[3]);
			$attr =& $entity->getAttribute($attrs[0]);
			if ($attrs[1]) {
				$attr =& $attr->getAttribute($attrs[1]); 
			}
			if (is_array($attr) && $data[4]) {
				$attr =& $attr[$data[4]];
			}
			
			if (!SRA_FileAttribute::isValid($attr)) {
				SRA_Controller::init($currentApp);
				$msg = 'SRA_FileAttribute::getInstanceFromEncryptedId: Failed - Data did not provide a valid SRA_FileAttribute: app ' . $data[0] . ', entity ' . $data[1] . ', entityId ' . $data[2] . ', attribute ' . $data[3] . ', attributeIndex ' . $data[4];
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			SRA_Controller::init($currentApp);
			return $attr;
		}
		else if ($data[7] == SRA_FILE_ATTRIBUTE_TYPE_DIR) {
			// check directory
			if ($data[8] && !file_exists($data[8])) {
				$msg = "SRA_FileAttribute::getInstanceFromEncryptedId: Failed - SRA_File: $data[8] does not exist";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			else if ($fileDir && dirname($data[8]) != $fileDir) {
				$msg = "SRA_FileAttribute::getInstanceFromEncryptedId: Failed - SRA_File: $data[8] is not in the specified file directory: $fileDir";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			$attr = new SRA_FileAttribute();
			$attr->_setFileDir($data[5]);
			$attr->_setFileDirUri($data[6]);
			$attr->_setFileHandling($data[7]);
			$attr->setFileName($data[8]);
			$attr->setSize($data[9]);
			$attr->setType($data[10]);
			if ($removeFile) {
				$attr->delete();
				return FALSE;
			}
			return $attr;
		}
		else {
			$msg = "SRA_FileAttribute::getInstanceFromEncryptedId: Failed - id ${id} specifies an invalid file handling method: " . $data[7];
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
	}
	// }}}
	
	// {{{ getInstanceFromUpload
	/**
	 * Creates a new instance of SRA_FileAttribute for a field from the current 
	 * $_FILES PHP global. Returns an FALSE if the $name is not present in that 
	 * variable
	 * @param string $name the name of the uploaded field or the actual $_FILES 
	 * array for that field
	 * @param string $attribute see the $_attribute api comments
	 * @param string $attributeIndex see the $_attributeIndex api comments
	 * @param string $entity see the $_entity api comments
	 * @param string $entityId see the $_entityId api comments
	 * @param string $fileDir see the $_fileDir api comments
	 * @param string $fileDirUri see the $_fileDirUri api comments
	 * @param string $fileHandling see the $_fileHandling api comments
	 * @param string $fileScriptRewrite see the $_fileScriptRewrite api comments
	 * @param string $fileScriptUri see the $_fileScriptUri api comments
   * @param string $fleProcessor see the entity-model dtd documentation for the attribute entity, file-processor attribute
	 * @access  public
	 * @return SRA_FileAttribute or SRA_Error
	 */
	function getInstanceFromUpload($data, $attribute = FALSE, $attributeIndex = FALSE, $entity = FALSE, $entityId = FALSE, $fileDir = FALSE, $fileDirUri = FALSE, $fileHandling = FALSE, $fileScriptRewrite = FALSE, $fileScriptUri = FALSE, $fileProcessor = FALSE) {
		if (is_scalar($data) && is_array($_FILES[$data])) {
			$data = $_FILES[$data];
		}
		if (is_array($data['tmp_name'])) {
			$fileName = $data['tmp_name'][0];
			$err =  $data['error'][0];
		}
		else {
			$fileName = $data['tmp_name'];
			$err =  $data['error'];
		}
		$fileAttribute = FALSE;
		if (is_array($data) && file_exists($fileName) && !$err) {
      if ($fileProcessor) {
        $tmp = exec(str_replace('{$file}', is_array($data['tmp_name']) ? $data['tmp_name'][0] : $data['tmp_name'], $fileProcessor));
      }
			if (is_array($data['tmp_name'])) {
				$fileAttribute = new SRA_FileAttribute($data['tmp_name'][0], $data['name'][0], $data['size'][0], $data['type'][0]);
			}
			else {
				$fileAttribute = new SRA_FileAttribute($data['tmp_name'], $data['name'], $data['size'], $data['type']);
			}
		}
		if ($fileAttribute) {
			$fileAttribute->_setAttribute($attribute);
			$fileAttribute->_setAttributeIndex($attributeIndex);
			$fileAttribute->_setEntity($entity);
			$fileAttribute->_setEntityId($entityId);
			$fileAttribute->_setFileDir($fileDir);
			$fileAttribute->_setFileDirUri($fileDirUri);
			$fileAttribute->_setFileHandling($fileHandling);
			$fileAttribute->_setFileScriptRewrite($fileScriptRewrite);
			$fileAttribute->_setFileScriptUri($fileScriptUri);
			$fileAttribute->_setNew(TRUE);
			$fileAttribute->_setApp(SRA_Controller::getCurrentAppId());
		}
		return $fileAttribute;
	}
	// }}}
	
	// {{{ fileHandlingMethodIsValid
	/**
	 * Static method that returns TRUE if a given file handling method is valid 
	 * (if it corresponds with one of the SRA_FILE_ATTRIBUTE_TYPE_* constants)
	 *
	 * @param  string $method the method to validate
	 * @access	public
	 * @return	boolean
	 */
	function fileHandlingMethodIsValid($method) {
		return ($method == SRA_FILE_ATTRIBUTE_TYPE_DB || $method == SRA_FILE_ATTRIBUTE_TYPE_DIR);
	}
	// }}}
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_FileAttribute object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_fileattribute');
	}
	// }}}
	
  
	// private operations

  
}
// }}}
?>
