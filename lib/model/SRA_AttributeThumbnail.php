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
 * the default thumbnail image format
 * @type string
 */
define('SRA_ATTRIBUTE_THUMBNAIL_DEFAULT_FORMAT', 'png');

/**
 * the thumbnail image formats that are supported
 * @type string
 */
define('SRA_ATTRIBUTE_THUMBNAIL_FORMATS', 'gif jpg png');
// }}}

// {{{ SRA_AttributeThumbnail
/**
 * used in conjunction with file type attributes (where is-file is true) when 
 * the model should automatically attempt to generate thumbnail (or thumbnails) 
 * for that attribute and store those also as file type attributes using another 
 * member attribute in the same entity. for more information on this 
 * functionality, see the api for SRA_FileAttribute::toThumbnail. this attribute 
 * may be used only with file attributes without cardinality. the value stored 
 * in the referenced attribute will be a new SRA_FileAttribute instance 
 * representing the thumbnail. at least 1 height or width (or both) MUST be 
 * specified for all thumbnails. if both height and width are specified, and 
 * preserveAspect is not true, the aspect ratio of the image may be affected 
 * because that exact height/width will be used for the generated thumbnail. if 
 * only one, height or width is specified, or if both are specified and 
 * preserveAspect is true, those values will be the maximum height or width of 
 * the generated thumbnail while the other dimension will be proportional to the 
 * dimension that is used according to the image aspect ratio. thumbnails will 
 * be generated to png format by default. gif and jpeg are also supported 
 * formats and may be specified using the "format" attribute. if multiple 
 * thumbnails are created as a result of the thumbnail generation process (i.e. 
 * when file is pdf with multiple pages), the first page thumbnail will be used 
 * UNLESS the thumbnail attribute referenced has cardinality in which case ALL 
 * of the thumbnails will be set in the same order of the pages. thumbnails 
 * cannot be generated for file attributes with cardinality
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_AttributeThumbnail {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the name of the entity attribute (in the same entity as the enclosing 
   * attribute) that the thumbnail(s) should be saved to
	 * @type string
	 */
	var $attribute;
  
  /**
   * if the referenced attribute is an entity (versus a file), this value should 
   * specified the name of the entity member attribute that should be used to 
   * store the thumbnail image
   * @type string
   */
  var $attr;
  
  /**
   * if the referenced attribute is an entity, and the file that the thumbnails 
   * are generated for may include multiple page pdf or postscript documents, 
   * this value may be used to store the page number for each of the generated 
   * thumbnails. in order to use this feature, however, the cardinality 
   * relationship must be 0..* for the referenced attribute, otherwise, a 
   * thumbnail will only be generated for the first page in the document
   * @type string
   */
  var $attrPageNum;
  
  /**
	 * the image format for the generated thumbnail(s)
	 * @type string
	 */
	var $format;
  
  /**
	 * the maximum height of the generated thumbnail(s)
	 * @type int
	 */
	var $height;
  
  /**
	 * space separated list of a regular expressions that should be used to match 
   * the mime-types that should be included in the thumbnail generation. 
   * thumbnail(s) will not be generated for any files that do not match these 
   * mime types
	 * @type string
	 */
	var $includeTypes;
  
  
  /**
	 * whether or not to preserve the aspect ratio of the original source file. if 
   * true, the thumbnail dimensions will not exceed height or width. if false, 
   * the thumbnail dimensions will be exactly height and width. when true, both 
   * height and width should be specified
	 * @type boolean
	 */
	var $preserveAspect;
  
  
  /**
	 * space separated list of a regular expressions that should be used to skip 
   * the mime-types that should be not be included in the thumbnail generation. 
   * thumbnail(s) will only be generated for files that do not match these mime 
   * types
	 * @type string
	 */
	var $skipTypes;
  
  
  /**
	 * the maximum width of the generated thumbnail(s)
	 * @type int
	 */
	var $width;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AttributeThumbnail
	/**
	 * Constructor - sets up thumbnail configuration. morphs to an error object 
   * when the configuration is not valid
   * @param array $conf the configuration to set up for this thumbnail
   * @access  public
	 */
	function SRA_AttributeThumbnail($conf) {
    $this->attribute = $conf['attributes']['key'];
    $this->attr = isset($conf['attributes']['attr']) ? $conf['attributes']['attr'] : NULL;
    $this->attrPageNum = isset($conf['attributes']['attr-page-num']) ? $conf['attributes']['attr-page-num'] : NULL;
    $this->format = isset($conf['attributes']['format']) ? $conf['attributes']['format'] : SRA_ATTRIBUTE_THUMBNAIL_DEFAULT_FORMAT;
    $this->height = isset($conf['attributes']['height']) ? $conf['attributes']['height'] : NULL;
    $this->includeTypes = isset($conf['attributes']['include-types']) ? $conf['attributes']['include-types'] : NULL;
    $this->preserveAspect = isset($conf['attributes']['preserve-aspect']) && $conf['attributes']['preserve-aspect'] == '0' ? FALSE : TRUE;
    $this->skipTypes = isset($conf['attributes']['skip-types']) ? $conf['attributes']['skip-types'] : NULL;
    $this->width = isset($conf['attributes']['width']) ? $conf['attributes']['width'] : NULL;
    if (!$this->attribute) {
      $msg = 'SRA_AttributeThumbnail::SRA_AttributeThumbnail: Failed - thumbnail attribute must be specified';
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!in_array($this->format, explode(' ', SRA_ATTRIBUTE_THUMBNAIL_FORMATS))) {
      $msg = 'SRA_AttributeThumbnail::SRA_AttributeThumbnail: Failed - format specified ' . $this->format . ' is not valid for thumbnail ' . $this->attribute;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!$this->height && !$this->width) {
      $msg = 'SRA_AttributeThumbnail::SRA_AttributeThumbnail: Failed - height OR width must be specified for thumbnail ' . $this->attribute;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!$this->preserveAspect && (!$this->height || !$this->width)) {
      $msg = 'SRA_AttributeThumbnail::SRA_AttributeThumbnail: Failed - both height AND width must be specified when preserve-aspect is false for thumbnail ' . $this->attribute;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
	}
	// }}}
	
  
  // public operations
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_AttributeThumbnail object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_attributethumbnail');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
