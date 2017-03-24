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

// {{{ Constants
								 
/**
 * The default file extension for templates
 * @type   String
 * @access public
 */
define('SRA_TEMPLATE_DEFAULT_EXT', '.tpl');

/**
 * Identifies that a template should be instantiated for the system www admin files. This 
 * constant is used to specify a template directory (see _templateDir api) of 
 * SRA_DIR . '/www/admin/tpl' in the SRA_Template::SRA_Template method through the type parameter.
 * @type   int
 * @access public
 */
define('SRA_TEMPLATE_TYPE_SYSTEM_ADMIN', 4);

/**
 * Identifies that a template should be instantiated for the system www files. This constant is 
 * used to specify a template directory (see _templateDir api) of 
 * SRA_DIR . '/www/tpl' in the SRA_Template::SRA_Template method through the type parameter.
 * @type   int
 * @access public
 */
define('SRA_TEMPLATE_TYPE_SYSTEM_WWW', 8);
								
/**
 * string identifying that an attribute value should be substituted in the 
 * renderOpen method
 * @type string
 * @access public
 */
define('SRA_TEMPLATE_RENDER_OPEN_SUB_VAL', '[$VAL]');
// }}}

// {{{ Includes
require_once(SRA_LIB_DIR . sprintf('/ext/smarty/%s/Smarty.class.php', preg_match('/^7/', phpversion()) ? 'v3' : 'v2'));
// }}}

// {{{ SRA_Template
/**
 * The SRA_Template class is used to control presentation and display logic.
 * Through it php files will have the ability to specify the values for
 * tags embedded into the template files. The SRA_Template class serve as an
 * abstraction layer to the template system.
 *
 * @author Jason Read <jason@idir.org>
 * @package sierra.core
 */
class SRA_Template {
  // {{{ Properties
  
  /**
   * whether or not output buffering has been started using the 'startBuffering' 
   * method and is still currently in progress
   * @type   boolean
   * @access private
   */
  var $_buffering;
  
  /**
   * The directory where the compiled template files exist or should be
   * created.
   * @type   String
   * @access private
   */
  var $_compileDir;
  
  /**
   * The directory where the template files exist.
   * @type   String
   * @access private
   */
  var $_templateDir;
  
  /**
   * The smarty template object that the SRA_Template instance will be a
   * wrapper for.
   * @type   Object
   * @access private
   */
  var $_tpl;
  // }}}

  // {{{ SRA_Template
  /**
   * The template constructor. should never be called from outside of this 
   * class. instead, use the SRA_Template::getTemplate singleton method
   * @access 	private
   * @return 	void
   */
  function SRA_Template() {}
  // }}}

  // {{{ append
  /**
   * Method used to append values to tags embedded into the template
   * files
   * @param string $tag the name of the template identifier to append to or an 
   * array of key => value pairs (in which case value parameter is unecessary)
   * @param string $value the value to append if tag is a key name (i.e. not an 
   * array)
   * @access public
   * @return void
   */
  function append($tag, $value = '') {
    if ($tag == '') { return SRA_Error::logError('SRA_Template::assign - tag parameter not specified.', __FILE__, __LINE__); }
    is_array($tag) ? $this->_tpl->append($tag) : $this->_tpl->append($tag, $value);
  }
  // }}}
    
  // {{{ arrayPush
  /**
   * used to push a value onto an array from within a smarty template
   * @param array $arr the array to push to
   * @param string $val the value to push
   * @param string $idx an optional array index to use (for associative 
   * arrays only)
   * @access public
   * @return void
   */
  function arrayPush(&$arr, $val, $idx) {
    $idx ? $arr[$idx] = $val : $arr[] = $val;
  }
  // }}}

  // {{{ assign
  /**
   * Method used to assign values to tags embedded into the template
   * files
   * @param string $tag the name of the template identifier to assign the 
   * value to, or an array of key => value pairs (in which case value 
   * parameter is unecessary).
   * @param string $value the value to assign if tag is a key name (i.e. not 
   * an array).
   * @access public
   * @return void
   */
  function assign($tag, $value='') {
    if (!$tag) {  
      return SRA_Error::logError('SRA_Template::assign - tag parameter not specified.', __FILE__, __LINE__);
    }
    else {
      if (isset($this->_tpl->_tpl_vars[$tag])) { unset($this->_tpl->_tpl_vars[$tag]); }
      $this->_tpl->_tpl_vars[$tag] = $value;
    }
  }
  // }}}
  
  // {{{ assignByRef
  /**
   * Method used to assign values to tags embedded into the template
   * files where the value will be a reference
   * @param string $tag The name of the template identifier to assign the 
   * value to
   * @param mixed $value a reference to the data to store
   * @access public
   * @return void
   */
  function assignByRef($tag, & $value) {
    if ($tag == '') { return SRA_Error::logError('SRA_Template::assign - tag parameter not specified.', __FILE__, __LINE__); }
    if (isset($this->_tpl->_tpl_vars[$tag])) { unset($this->_tpl->_tpl_vars[$tag]); }
    $this->_tpl->_tpl_vars[$tag] = $value;
  }
  // }}}
  
  // {{{ ceil
  /**
   * helper math function for use within templates
   * @param float $val the fraction to return the ceil for
   * @access public static
   * @return void
   */
  function ceil($val) {
    return ceil($val);
  }
  // }}}
  
  // {{{ compile
  /**
   * used to compile a template. may be beneficial in order to increase 
   * application performance by pre-compiling the templates. returns TRUE if the 
   * template was compiled
   * @param string $template the template to compile
   * @param boolean $overwrite whether or not to overwrite the existing compiled 
   * file if it exists
   * @access public
   * @return boolean
   */
  function compile($template, $overwrite=TRUE) {
    if (file_exists($template) || file_exists($template = SRA_Template::_getFilePath($fileName))) {
      $compiled = $this->_tpl->_get_compile_path($template);
      if ($overwrite && file_exists($compiled)) {
        unlink($compiled);
      }
      if (!file_exists($compiled)) {
        $this->_tpl->_compile_resource($template, $compiled);
        return TRUE;
      }
    }
    return FALSE;
  }
  // }}}
  
  // {{{ compileAll
  /**
   * this method compiles all smarty templates for this template instance
   * @param boolean $overwrite whether or not to overwrite the existing compiled 
   * file if it exists
   * @access public
   * @return void
   */
  function compileAll($overwrite=TRUE) {
    foreach(SRA_File::getFileList($this->_templateDir, '/\.tpl$/', TRUE) as $file) {
      $this->compile($file, $overwrite);
    }
  }
  // }}}
  
  // {{{ removeCache
  /**
   * removes the compile cache for $template. returns TRUE if the cache existed 
   * and was deleted
   * @param string $template the template to remove the compile cache for
   * @access public
   * @return boolean
   */
  function removeCache($template) {
    if (file_exists($template) || file_exists($template = SRA_Template::_getFilePath($fileName))) {
      if (file_exists($compiled = $this->_tpl->_get_compile_path($template))) {
        unlink($compiled);
        return TRUE;
      }
    }
    return FALSE;
  }
  // }}}
  
  // {{{ removeAllCache
  /**
   * this method removes all of the compile cache for this template instance
   * @access public
   * @return void
   */
  function removeAllCache() {
    foreach(SRA_File::getFileList($this->_templateDir, '/\.tpl$/', TRUE) as $file) {
      $this->removeCache($file);
    }
  }
  // }}}

  // {{{ define
  /**
   * helper function used to define a constant. the constant will be defined 
   * ONLY if it is not already defined
   * @param string $const the name of the constant to define
   * @param mixed $val the constant value
   * @access public static
   * @return void
   */
  function define($const, $val) {
    if (!defined($const)) { define($const, $val); }
  }
  // }}}
  
  // {{{ defined
  /**
   * returns TRUE if $const is defined
   * @param string $const the name of the constant
   * @access public static
   * @return boolean
   */
  function defined($const) {
    return defined($const);
  }
  // }}}
  
  // {{{ display
  /**
   * This method displays the template file given as its argument with the 
   * custom tags embedded as specified prior to calling this method. 
   * @param string $fileName relative or fixed app path (with or without 
   * default template extension) of the template to display
   * @access public
   * @return void
   */
  function display($fileName) {
    $fileName = SRA_Template::_getFilePath($fileName);
    $this->_setTemplateDir(dirname($fileName));
    $this->_tpl->display($fileName);
  }
  // }}}
  
  // {{{ displayToEmail
  /**
   * used to render the content of a template and send the results via email
   * this is basically just a wrapper to the SRA_Util::sendEmail and 
   * SRA_Template::fetch methods
   * @param mixed $to the recipient email address. if $to is an array, 1 
   * message will be sent to each recipient. multiple messages can be sent to 
   * in the same message instance by separating each recipient with a comma
   * (i.e. "john@john.com, Jason <jason@jason.com>")
   * @param string $subject the email subject
   * @param string $messageTpl the template containing the text version of the 
   * email message. either $messageTpl or $messageHtmlTpl MUST be specified. 
   * if both are specified, the message will be sent with both content types. 
   * otherwise, only the content type specified will be sent
   * @param string $messageHtmlTpl the template containing the html version of 
   * the email message
   * @param string $from the sender email address. if not specified, the 
   * default from address will be used ([process user]@[server domain])
   * @param string $fromName the sender name (optional). can also be specified 
   * in $from using the syntax "[email] <[name]>"
   * @param mixed $toName the recipient name (optional). if $to is an array, 
   * and $toName is specified, it much also be an array with the same # of 
   * values. can also be specified in $from using the syntax 
   * "[email] <[name]>"
   * @param string $cc optional comma separated list of carbon copy recipients
   * @param string $bcc optional comma separated list of blind carbon copy 
   * recipients
   * @param mixed $attachments a single absolute path or array of absolute 
   * paths to files that should be attached to this email. the names assigned 
   * to these attachments will be the names of the files themselves
   * @access 	public static
   * @return 	void
   */
  function displayToEmail($to, $subject, $messageTpl, $messageHtmlTpl=NULL, $from=NULL, $fromName=NULL, $toName=NULL, $cc=NULL, $bcc=NULL, &$attachments) {
    SRA_Util::sendEmail($to, $subject, $messageTpl ? $this->fetch($messageTpl) : NULL, $messageHtmlTpl ? $this->fetch($messageHtmlTpl) : NULL, $from, $fromName, $toName, $cc, $bcc, $attachments);
  }
  // }}}
  
  // {{{ displayToFile
  /**
   * displays the template $tpl and writes the output to a file. returns TRUE on 
   * success, FALSE otherwise
   * @param string $tpl relative or fixed app path (with or without default 
   * template extension) of the template to display
   * @param string $file absolute path to the file that $tpl should be rendered 
   * to
   * @access public
   * @return boolean
   */
  function displayToFile($tpl, $file) {
    if ($fp = fopen($file, 'w')) {
      fwrite($fp, $this->fetch($tpl));
      fclose($fp);
      return TRUE;
    }
    return FALSE;
  }
  // }}}
  
  // {{{ escapeHtmlChars
  /**
   * this method convert special characters to HTML entities
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeHtmlChars($str) {
    return htmlspecialchars($str);
  }
  // }}}
  
  // {{{ escapeDoubleQuotes
  /**
   * this method escapes double quotes within a string
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeDoubleQuotes($str) {
    return str_replace('"', '\"', $str);
  }
  // }}}
  
  // {{{ escapeHtmlDoubleQuotes
  /**
   * this method escapes double quotes within a string. these are replaced 
   * with the html equivalent &quot;
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeHtmlDoubleQuotes($str) {
    return str_replace('"', '&quot;', $str);
  }
  // }}}
  
  // {{{ escapeHtmlQuotes
  /**
   * this method escapes both double and single quotes within a string. these 
   * are replaced with the html equivalents &quot; and &#39;
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeHtmlQuotes($str) {
    return SRA_Template::escapeHtmlDoubleQuotes(SRA_Template::escapeHtmlSingleQuotes($str));
  }
  // }}}
  
  // {{{ escapeSingleQuotes
  /**
   * this method escapes double quotes within a string
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeSingleQuotes($str) {
    return str_replace("'", "\'", $str);
  }
  // }}}
  
  // {{{ escapeHtmlSingleQuotes
  /**
   * this method escapes single quotes within a string. these are replaced 
   * with the html equivalent &#39;
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeHtmlSingleQuotes($str) {
    return str_replace("'", '&#39;', $str);
  }
  // }}}
  
  // {{{ escapeQuotes
  /**
   * this method escapes both single and double quotes within a string
   * @param string $str the string to escape
   * @access public
   * @return array
   */
  function escapeQuotes($str) {
    return str_replace("'", "\'", str_replace('"', '\"', $str));
  }
  // }}}
  
  // {{{ explode
  /**
   * pass-thru to the PHP explode function
   * @param string $delim the delimiter to explode on 
   * @param string $str the string to explode
   * @access public
   * @return array
   */
  function explode($delim, $str) {
    return explode($delim, $str);
  }
  // }}}

  // {{{ fetch
  /**
   * Same as display(), except that this returns its output as a string
   * instead of displaying it.
   * @param string $fileName relative or fixed app path to the template
   * @access public
   * @return string
   */
  function &fetch($fileName) {
    $fileName = SRA_Template::_getFilePath($fileName);
    $this->_setTemplateDir(dirname($fileName));
    return $this->_tpl->fetch($fileName);
  }
  // }}}
  
  // {{{ floor
  /**
   * helper math function for use within templates
   * @param float $val the fraction to return the floor for
   * @access public static
   * @return void
   */
  function floor($val) {
    return floor($val);
  }
  // }}}
  
  // {{{ getWSGatewayUri
  /**
   * returns the gateway uri for the active application
   * @access public
   * @return string
   */
  function getWSGatewayUri() {
    require_once(SRA_LIB_DIR . '/model/SRA_WSGateway.php');
    return SRA_WSGateway::getGatewayUri();
  }
  // }}}
  
  // {{{ getAppTemplateVar
  /**
   * returns the template variable value associated with the specified $key
   * @param string $key the identifier for the template variable to return
   * @access public static
   * @return array
   */
  function getAppTemplateVar($key) {
    $tpl =& SRA_Controller::getAppTemplate();
    return SRA_Util::getNestedAttr($tpl->_tpl->_tpl_vars, $key);
  }
  // }}}
  
  // {{{ getArrayLength
  /**
   * returns the # of items in $arr
   * @param array $arr the array to check
   * @access public static
   * @return int
   */
  function getArrayLength(& $arr) {
    return count($arr);
  }
  // }}}
  
  // {{{ getClassInstance
  /**
   * returns a new instance of $className
   * @param string $className the name of the class to instantiate
   * @param mixed $params optional constructor parameters
   * @access public
   * @return object
   */
  function &getClassInstance($className, $params=NULL) {
    return new ${className}($params);
  }
  // }}}
  
  // {{{ getFormValue
  /**
   * the value of a $_GET or $_POST variable
   * @param string $name the name of the variable to return. first $_POST is 
   * searched, followed by $_GET
   * @access public
   * @return string
   */
  function getFormValue($name) {
    return isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : NULL);
  }
  // }}}
  
  // {{{ getSessionVar
  /**
  * returns the value of a session variable
  * @param string $name the name of the session variable to get
  * @access public
  * @return string
  */
  function getSessionVar($name) {
    session_start();
    return $_SESSION[$name];
  }
  // }}}
  
  // {{{ getType
  /**
   * pass-thru to the PHP gettype function
   * @param mixed $val the value to return the type for
   * @access public
   * @return string
   */
  function getType($val) {
    return gettype($val);
  }
  // }}}
  
  // {{{ implode
  /**
   * pass-thru to the PHP implode function
   * @param string $glue the join string
   * @param array $pieces the array
   * @access public
   * @return string
   */
  function implode($glue, $pieces) {
    return is_array($pieces) && $pieces ? implode($glue, $pieces) : '';
  }
  // }}}
  
  // {{{ lineBreaksToBr
  /**
   * this replaces line breaks in $str to html <br /> tags
   * @param string $str the string to replace the line breaks in
   * @param string $element the element to use for the tags. default is 'br'
   * @access public
   * @return string
   */
  function lineBreaksToBr($str, $element='br') {
    return str_replace("\n", '<' . $element . ' />', $str);
  }
  // }}}
   
  // {{{ validateTemplate
  /**
   * validates a template file
   * @param string $fileName relative or fixed app path (with or without default 
   * template extension)
   * @access public
   * @return boolean
   */
  function &validateTemplate($fileName) {
    $fileName = SRA_Template::_getFilePath($fileName);
    return file_exists($fileName);
  }
  // }}}
  
  // {{{ getVar
  /**
   * returns a template variable value
   * @param string $tag The name of the template variable to return
   * @access public
   * @return mixed
   */
  function getVar($tag) {
    return isset($this->_tpl->_tpl_vars[$tag]) ? $this->_tpl->_tpl_vars[$tag] : FALSE;
  }
  // }}}
  
  // {{{ getVarByRef
  /**
   * returns a reference to a template variable
   * @param string $tag The name of the template variable to return
   * @access public
   * @return void
   */
  function &getVarByRef($tag) {
    return isset($this->_tpl->_tpl_vars[$tag]) ? $this->_tpl->_tpl_vars[$tag] : FALSE;
  }
  // }}}
  
  // {{{ getUniqueSeq
  /**
   * returns a numeric sequence that is unique for the entire current PHP 
   * process. each time this method is called, the sequence will be incremented
   * @access public static
   * @return int
   */
  function getUniqueSeq() {
    static $seq = 1;
    return $seq++;
  }
  // }}}
  
  // {{{ initTemplate
  /**
   * initializes a template allowing for future references to #renderOpen. 
   * this will reset any cycles for that template that were previously 
   * utilized
   * @param string $tplName the name of the template to initialize
   * @param string $check INTERNAL USE ONLY
   * @access public static
   * @return void
   */
  function initTemplate($tplName, $check = FALSE) {
    static $initialized = array();
    if ($check) {
      if (isset($initialized[$tplName])) {
        unset($initialized[$tplName]);
        return TRUE;
      }
      return FALSE;
    }
    $initialized[$tplName] = TRUE;
  }
  // }}}
  
  // {{{ isArray
  /**
   * returns TRUE if $arr is an array
   * @param mixed $arr the array to check
   * @access public static
   * @return boolean
   */
  function isArray(& $arr) {
    return is_array($arr);
  }
  // }}}
  
  // {{{ isObject
  /**
   * returns TRUE if $obj is an object
   * @param mixed $obj the object to check
   * @access public static
   * @return boolean
   */
  function isObject(& $obj) {
    return is_object($obj);
  }
  // }}}
  
  // {{{ numberFormat
  /**
   * formats a number. identical to the PHP number_format function
   * @param float $num the number to format
   * @param int $decimals the decimal precision
   * @param char $decPoint the decimal separator character
   * @param char $thousandsSep the thousands separator character
   * @access public static
   * @return string
   */
  function numberFormat($num, $decimals, $decPoint='.', $thousandsSep=',') {
    return number_format($num, $decimals, $decPoint, $thousandsSep);
  }
  // }}}
  
  // {{{ renderOpen
  /**
   * renders an html/xml open element using the $params specified to 
   * determine what attributes of that element to render
   * @param string $tplName the name of the template
   * @param string $tag the html/xml tag to render
   * @param SRA_Params $params the SRA_Params for the template
   * @param string $tagType optional tag type identifier. used to distinguish 
   * between different cycles for the same $tag
   * @param boolean $close whether or not to close the tag
   * @param string $subVal if the value for the $tag specified is equal to 
   * SRA_TEMPLATE_RENDER_OPEN_SUB_VAL, then the value for that attribute 
   * will be substituted with $subVal
   * @param boolean $refresh whether or not to refresh the $tagAttrs
   * @access public static
   * @return void
   */
  function renderOpen($tplName, $tag, & $params, $tagType='', $close=TRUE, $subVal=FALSE, $refresh=FALSE) {
    static $cycles = array();
    static $cyclePtrs = array();
    static $tagAttrs = array();
    
    if (SRA_Template::initTemplate($tplName, TRUE)) {
      $keys = array_keys($tagAttrs);
      foreach ($keys as $key) {
        if (SRA_Util::beginsWith($key, $tplName)) {
          unset($tagAttrs[$key]);
        }
      }
    }
    if (!isset($cycles[$tplName])) {
      $cycles[$tplName] = array();
      $tmp =& $params->getTypeSubset('cycles');
      $tmp = $tmp->getParams();
      foreach($tmp as $id => $val) {
        $cycles[$tplName][$id] = explode(',', $val);
      }
    }
    if (!isset($cyclePtrs[$tplName])) {
      $cyclePtrs[$tplName] = array();
      $attrs = array_keys($cycles[$tplName]);
      foreach ($attrs as $attr) {
        $cyclePtrs[$tplName][$attr] = 0;
      }
    }
    if (!isset($tagAttrs[$tplName . $tag]) || !isset($cycles[$tplName]) || !count($cycles[$tplName]) || $refresh) {
      $tagAttrs[$tplName . $tag] =& $params->getTypeSubset($tag . '-attrs');
      $tagAttrs[$tplName . $tag] = $tagAttrs[$tplName . $tag]->getParams();
    }
    if ($tagType && !isset($tagAttrs[$tplName . $tag . $tagType]) || $refresh) {
      $tagAttrs[$tplName . $tag . $tagType] =& $params->getTypeSubset($tag . '-' . $tagType . '-attrs');
      $tagAttrs[$tplName . $tag . $tagType] = $tagAttrs[$tplName . $tag . $tagType]->getParams();
    }
    if (!is_array($cycles[$tplName])) {
      $cycles[$tplName] = array();
    }
    if (!is_array($cyclePtrs[$tplName])) {
      $cyclePtrs[$tplName] = array();
    }
    if (!is_array($tagAttrs[$tplName . $tag])) {
      $tagAttrs[$tplName . $tag] = array();
    }
    if ($tagType && (!is_array($tagAttrs[$tplName . $tag . $tagType]) || !count($tagAttrs[$tplName . $tag . $tagType]))) {
      $tagAttrs[$tplName . $tag . $tagType] = $tagAttrs[$tplName . $tag];
    }
    
    $resources =& SRA_Controller::getAppResources();
    echo "<${tag}";
    foreach($tagAttrs[$tplName . $tag . $tagType] as $attr => $val) {
      if (isset($cyclePtrs[$tplName][$val])) {
        $newVal = trim($cycles[$tplName][$val][$cyclePtrs[$tplName][$val]]);
        $cyclePtrs[$tplName][$val] = ($cyclePtrs[$tplName][$val] == count($cycles[$tplName][$val]) - 1) ? 0 : $cyclePtrs[$tplName][$val] + 1;
        $val = $newVal;
      }
      if (strstr($val, SRA_TEMPLATE_RENDER_OPEN_SUB_VAL)) {
        $val = str_replace(SRA_TEMPLATE_RENDER_OPEN_SUB_VAL, $subVal, $val);
      }
      if ($val) {
        echo ' ' . $attr . '="' . htmlspecialchars($resources->getString($val), ENT_COMPAT) . '"';
      }
    }
    if ($close) {
      echo '>';
    }
  }
  // }}}
  
  // {{{ includeOnce
  /**
   * includes a php source file using the 'include_once' directive
   * @param string $path the absolute or relative path of the file to include
   * @access public static
   * @return void
   */
  function includeOnce($path) {
    include_once($path);
  }
  // }}}
  
  // {{{ serialize
  /**
   * used to serialize an object from within a template
   * @param object $obj the object to serialize
   * @access public static
   * @return void
   */
  function serialize(& $obj) {
    return serialize($obj);
  }
  // }}}
  
  // {{{ startBuffering
  /**
   * starts output buffering if it is not already started
   * @access public static
   * @return boolean
   */
  function startBuffering() {
    if (!$this->_buffering) {
      $this->_buffering = TRUE;
      ob_start();
      return TRUE;
    }
  }
  // }}}
  
  // {{{ stopBuffering
  /**
   * stops output buffering and returns the current contents of the output buffer
   * @param boolean $stripNewlines whether or not to strip newline characters 
   * from the buffer output
   * @access public static
   * @return string
   */
  function stopBuffering($stripNewlines=FALSE) {
    if ($this->_buffering) {
      $this->_buffering = FALSE;
      $buffer = ob_get_contents();
      if ($stripNewlines) { $buffer = str_replace("\n", '', $buffer); }
      ob_end_clean();
      return $buffer;
    }
  }
  // }}}
  
  // {{{ strlen
  /**
   * pass-thru to the PHP strlen function
   * @param string $str the string to return the length of
   * @access public
   * @return int
   */
  function strlen($str) {
    return strlen($str);
  }
  // }}}
  
  // {{{ strReplace
  /**
   * pass-thru to the PHP str_replace function
   * @param string $str the string to replace 
   * @param string $replace the string to replace it with
   * @param string $subject the string
   * @access public
   * @return array
   */
  function strReplace($str, $replace, $subject) {
    return str_replace($str, $replace, $subject);
  }
  // }}}
  
  // {{{ substr
  /**
   * pass-thru to the PHP substr function
   * @param string $str the string to 
   * @param int $start the starting location
   * @param int $length the length (optional)
   * @access public
   * @return array
   */
  function substr($str, $start, $length=NULL) {
    return $length ? substr($str, $start, $length) : substr($str, $start);
  }
  // }}}
  
  // {{{ trim
  /**
   * pass-thru to the PHP trim function
   * @param string $str the string to trim
   * @access public
   * @return string
   */
  function trim($str) {
    return trim($str);
  }
  // }}}
  
  // {{{ validate
  /**
   * returns TRUE if the $fileName specified is a valid template
   * @param string $fileName the template to check
   * @access private
   * @return boolean
   */
  function validate($fileName) {
    return file_exists($this->_getFilePath($fileName));
  }
  // }}}
  
  
  // {{{ _setParams
  /**
   * Used to set dynamic parameters specified in the template filename in 
   * standard HTTP GET format:
   * 	e.g. mytemplate.tpl?param1=val1&param2=val2& ... & paramN=valN
   * the return value will be the filename minus any parameters specified
   * @param string $fileName the template identifier + params
   * @access public
   * @return the template identifier - params
   */
  function _setParams($fileName) {
    $pieces = explode('?', $fileName);
    $fileName = $pieces[0];
    if (isset($pieces[1])) {
      $params = explode('&', $pieces[1]);
      foreach ($params as $param) {
        $param = explode('=', $param);
        if (isset($param[1])) {
          $this->assign($param[0], $param[1]);
        }
      }
    }
    return $fileName;
  }
  // }}}

  // {{{ isValid
  /**
   * Static method used to validate an SRA_Template object.
   * @param object $object the object to validate.
   * @access public
   * @return boolean
   */
  function isValid(& $object)
  {
      return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_template');
  }
  // }}}
  
  // {{{ getSmarty
  /**
   * Returns a reference to the Smarty SRA_Template object.
   * @access public
   * @return void
   */
  function &getSmarty()
  {
      return $this->_tpl;
  }
  // }}}
  
  // {{{ registerObject
  /**
   * Registers an object in the template context
   * @access public
   * @return void
   */
  function registerObject($name, & $obj) {
    $this->_tpl->register_object($name, $obj);
  }
  // }}}
  
  // {{{ getTemplateDir
  /**
   * returns the directory where the current template exists
   * @access private
   * @return void
   */
  function getTemplateDir() {
    return $this->_templateDir;
  }
  // }}}
  
  // {{{ _getFilePath
  /**
   * returns the absolute path to the file specified
   * @access private
   * @return void
   */
  function _getFilePath($fileName) {
    $fileName = $this->_setParams($fileName);
    $baseName = $fileName;
    if (!file_exists($fileName)) {
      $fileName = SRA_File::getRelativePath(NULL, $baseName, SRA_Controller::getAppTplDir());
    }
    if (!file_exists($fileName)) {
      $fileName = SRA_File::getRelativePath(NULL, $baseName, SRA_Controller::getSysTplDir());
    }
    if (!file_exists($fileName)) {
      $fileName = SRA_File::getRelativePath(NULL, $baseName, SRA_Controller::getAppDir());
    }
    if (!file_exists($fileName)) {
      $fileName = SRA_File::getRelativePath(NULL, $baseName, SRA_Controller::getSysDir());
    }
    if (!file_exists($fileName)) {
      $fileName = SRA_File::getRelativePath(NULL, $baseName);
    }
    return $fileName;
  }
  // }}}
  
  // {{{ _setTemplateDir
  /**
   * sets the template dir
   * @access private
   * @return void
   */
  function _setTemplateDir($dir) {
    $this->_templateDir = $dir;
    $this->_tpl->template_dir = $dir;
  }
  // }}}
    
    
  // {{{ getTemplate
  /**
   * static singleton method for retrieving a reference to a template
   * @param string $appId the identifier of the app to return the template for
   * if not specified, the system template will be returned
   * @access public
   * @return SRA_Template
   */
  function &getTemplate($appId) {
    static $cachedTemplates = array();
    $appId = $appId ? $appId : '_sys_';
    if (!isset($cachedTemplates[$appId])) {
      $cachedTemplates[$appId] = new SRA_Template();
      if (!$cachedTemplates[$appId]->_tpl = new Smarty()) {
        return SRA_Error::logError('SRA_Template::getTemplate: Failed - Could not create new Smarty template object', __FILE__, __LINE__, SRA_ERROR_SHUTDOWN);
      }
      $cachedTemplates[$appId]->_compileDir = $appId ? SRA_Controller::getAppTmpDir() : SRA_Controller::getSysTmpDir();
      $cachedTemplates[$appId]->_templateDir = $appId ? SRA_Controller::getAppTplDir() : SRA_Controller::getSysTplDir();
      if (!is_dir($cachedTemplates[$appId]->_templateDir)) {
        return SRA_Error::logError("SRA_Template::getTemplate: Failed - SRA_Template directory '" . $cachedTemplates[$appId]->_templateDir . "' does not exist or is not a directory", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
      }
      if (!is_dir($cachedTemplates[$appId]->_compileDir)) {
        return SRA_Error::logError("SRA_Template::getTemplate: Compiled template directory '" . $cachedTemplates[$appId]->_compileDir . "' does not exist.", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
      }
      if (!is_writeable($cachedTemplates[$appId]->_compileDir)) {
        return SRA_Error::logError("SRA_Template::SRA_Template(): Compiled template directory '" . $cachedTemplates[$appId]->_compileDir . "' is not writable.", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
      }
      
      // Let Smarty know where the directories are located.
      $cachedTemplates[$appId]->_setTemplateDir($cachedTemplates[$appId]->_templateDir);
      $cachedTemplates[$appId]->_tpl->compile_dir = $cachedTemplates[$appId]->_compileDir;
    }
    return $cachedTemplates[$appId];
  }
  // }}}

}
// }}}

?>
