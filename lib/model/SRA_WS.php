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
require_once('SRA_WSConstraintGroup.php');
require_once('SRA_WSParam.php');
// }}}

// {{{ Constants
/**
 * the json format identifier
 * @type string
 */
define('SRA_WS_FORMAT_JSON', 'json');

/**
 * the raw format identifier
 * @type string
 */
define('SRA_WS_FORMAT_RAW', 'raw');

/**
 * the xml format identifier
 * @type string
 */
define('SRA_WS_FORMAT_XML', 'xml');

/**
 * identifier for the sql service type
 * @type string
 */
define('SRA_WS_GLOBAL_TYPE_SQL', 'sql');

/**
 * the json meta format identifier
 * @type string
 */
define('SRA_WS_META_FORMAT_JSON', 'json');

/**
 * the none meta format identifier
 * @type string
 */
define('SRA_WS_META_FORMAT_NONE', 'none');

/**
 * the xml meta format identifier
 * @type string
 */
define('SRA_WS_META_FORMAT_XML', 'xml');

/**
 * specifies that an attr is the name of an attribute in the entity
 * @type string
 */
define('SRA_WS_VALUE_TYPE_ATTR', 'attr');

/**
 * specifies that an attr/value is the name of a php constant
 * @type string
 */
define('SRA_WS_VALUE_TYPE_CONSTANT', 'constant');

/**
 * specifies that an attr/value is the name of a value in the get headers
 * @type string
 */
define('SRA_WS_VALUE_TYPE_GET', 'get');

/**
 * specifies that an attr/value is a 'getAttribute' value for the entity
 * @type string
 */
define('SRA_WS_VALUE_TYPE_GET_ATTR', 'getAttr');

/**
 * specifies that an attr/value is the name of a file uploaded and located in 
 * the global $_FILES array. this can only be used for value types not attrs
 * @type string
 */
define('SRA_WS_VALUE_TYPE_FILE', 'file');

/**
 * specifies that an attr/value is the name of a php global variable
 * @type string
 */
define('SRA_WS_VALUE_TYPE_GLOBAL', 'global');

/**
 * specifies that an attr/value is the name of a value in the post headers
 * @type string
 */
define('SRA_WS_VALUE_TYPE_POST', 'post');

/**
 * specifies that an attr/value is the name of a session variable
 * @type string
 */
define('SRA_WS_VALUE_TYPE_SESSION', 'session');
// }}}

// {{{ SRA_WS
/**
 * define a single entity specific web service. for more information, review 
 * the "ws" element documentation provided in entity-model.dtd
 * @author Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WS {
  /**
	 * the web service name
	 * @type string
	 */
	var $_id;
  
  /**
   * the resource key to use for api documentation for this service
   * @type string
   */
  var $_api;
  
  /**
   * whether or not to authenticate clients invoking this service (an 
   * authenticator must be configured for the the sra-ws-gateway.php script)
   * @type boolean
   */
  var $_authenticate;
  
  /**
	 * optional constraint groups that should be applied to this web service
	 * @type SRA_WSConstraintGroup[]
	 */
	var $_constraintGroups = array();
  
  /**
	 * can a user use this service to CREATE a new instance of $_entity?
	 * @type boolean
	 */
	var $_create;
  
  /**
	 * can the user use this service to DELETE an existing instance of $_entity? 
	 * @type boolean
	 */
	var $_delete;
  
  /**
	 * the name of the entity this web service has been defined for
	 * @type string
	 */
	var $_entity;
  
  /**
	 * the names of attributes that should be excluded from the response
	 * @type array
	 */
	var $_excludeAttrs;
  
  /**
   * the default response format to use for this service. either "json", "xml" 
   * or "raw"
   * @type string
   */
  var $_format;
  
  /**
   * whether or not $_format is fixed
   * @type boolean
   */
  var $_formatFixed;
  
  /**
   * hidden services are services that can be utilized but are not included in 
   * the auto-generated api documentation/wsdl
   * @type boolean
   */
  var $_hidden;
  
  /**
   * an optional path to a static method that should be invoked to determine 
   * whether or not this service is hidden IF 'hidden' is FALSE (specified in 
   * the same format as the ws-global 'identifier' when type is 'method'). this 
   * method should return a boolean value, TRUE to hide, FALSE to display
   * @type string
   */
  var $_hiddenMethod;
  
  /**
	 * the names of attributes that should be included in the response
	 * @type array
	 */
	var $_includeAttrs;
  
  /**
   * an optional fixed or app relative path to a PHP source file containing an 
   * ip address authentication method to be invoked
   * @type string
   */
  var $_ipAuthenticator;
  
  /**
	 * the default sql limit for this web service output (the maximum # of 
   * instances that meet the service call constraints to return)
	 * @type int
	 */
	var $_limit;
  
  /**
	 * whether or not "limit" is fixed, meaning the client cannot increase it 
   * using the "ws-limit" request parameter
	 * @type boolean
	 */
	var $_limitFixed;
  
  /**
	 * the default response metadata format. either "json", "xml" or "none"
	 * @type string
	 */
	var $_metaFormat;
  
  /**
	 * whether or not $_metaFormat is fixed
	 * @type boolean
	 */
	var $_metaFormatFixed;
  
  /**
   * whether or not to enable this service via rest
   * @type boolean
   */
  var $_rest;
  
  /**
   * can a user use this service to retrieve instances of $_entity?
   * @type boolean
   */
  var $_retrieve;
  
  /**
   * whether or not to enable this service via soap
   * @type boolean
   */
  var $_soap;
  
  /**
   * whether or not to set the 'insertSubEntities'/'updateSubEntities' flags to 
   * TRUE (for CREATE and UPDATE invocations only)
   * @type boolean
   */
  var $_subEntities;
  
  /**
	 * can a user use this service to UPDATE a new instance of $_entity?
	 * @type boolean
	 */
	var $_update;
  
  /**
	 * additional validation methods that should be invoked when for create and 
   * update requests
	 * @type array
	 */
  var $_validators;
  
  /**
	 * an optional entity view to use as the service response output
	 * @type string
	 */
	var $_view;
  
  /**
	 * an optional entity view to use as the service response output when the 
   * $_format requested is json
	 * @type string
	 */
	var $_viewJson;
  
  /**
	 * an optional entity view to use as the service response output when the 
   * $_format requested is xml
	 * @type string
	 */
	var $_viewXml;
  
  
	// {{{ SRA_WS
	/**
	 * parses the service configuration and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the xml configuration array for this service
   * @param string $entity the name of the entity this web service has been 
   * defined for
   * @access public
	 */
	function SRA_WS($conf, $entity) {
    $this->_id = $conf['attributes']['key'];
    $this->_api = isset($conf['attributes']['api']) ? $conf['attributes']['api'] : NULL;
    $this->_authenticate = SRA_Util::convertBoolean(isset($conf['attributes']['authenticate']) ? $conf['attributes']['authenticate'] : NULL, TRUE);
    if (isset($conf['ws-constraint-group'])) {
			foreach (array_keys($conf['ws-constraint-group']) as $key) {
				if (!SRA_WSConstraintGroup::isValid($this->_constraintGroups[] = new SRA_WSConstraintGroup($conf['ws-constraint-group'][$key]))) {
					$msg = 'SRA_WS: Failed - constraint group ${key} produced error for service ' . $this->_id;
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
			}
    }
    $this->_create = SRA_Util::convertBoolean(isset($conf['attributes']['create']) ? $conf['attributes']['create'] : NULL);
    $this->_delete = SRA_Util::convertBoolean(isset($conf['attributes']['delete']) ? $conf['attributes']['delete'] : NULL);
    $this->_entity = $entity;
    $this->_excludeAttrs = isset($conf['attributes']['exclude-attrs']) ? explode(strpos($conf['attributes']['exclude-attrs'], ',') ? ',' : ' ', $conf['attributes']['exclude-attrs']) : NULL;
    $this->_format = isset($conf['attributes']['format']) ? $conf['attributes']['format'] : SRA_WS_FORMAT_JSON;
    $this->_formatFixed = SRA_Util::convertBoolean(isset($conf['attributes']['format-fixed']) ? $conf['attributes']['format-fixed'] : NULL);
    $this->_hidden = SRA_Util::convertBoolean(isset($conf['attributes']['hidden']) ? $conf['attributes']['hidden'] : NULL);
    $this->_hiddenMethod = isset($conf['attributes']['hidden-method']) ? $conf['attributes']['hidden-method'] : NULL;
    $this->_includeAttrs = isset($conf['attributes']['include-attrs']) ? explode(strpos($conf['attributes']['include-attrs'], ',') ? ',' : ' ', $conf['attributes']['include-attrs']) : NULL;
    $this->_ipAuthenticator = isset($conf['attributes']['ip-authenticator']) ? $conf['attributes']['ip-authenticator'] : NULL;
    $this->_limit = isset($conf['attributes']['limit']) ? $conf['attributes']['limit'] : NULL;
    $this->_limitFixed = SRA_Util::convertBoolean(isset($conf['attributes']['limit-fixed']) ? $conf['attributes']['limit-fixed'] : NULL);
    $this->_metaFormat = isset($conf['attributes']['meta-format']) ? $conf['attributes']['meta-format'] : ($this->_format == SRA_WS_FORMAT_RAW ? SRA_WS_META_FORMAT_NONE : $this->_format);
    $this->_metaFormatFixed = SRA_Util::convertBoolean(isset($conf['attributes']['meta-format-fixed']) ? $conf['attributes']['meta-format-fixed'] : NULL);
    $this->_rest = SRA_Util::convertBoolean(isset($conf['attributes']['rest']) ? $conf['attributes']['rest'] : NULL, TRUE);
    $this->_retrieve = SRA_Util::convertBoolean(isset($conf['attributes']['retrieve']) ? $conf['attributes']['retrieve'] : NULL, TRUE);
    $this->_soap = SRA_Util::convertBoolean(isset($conf['attributes']['soap']) ? $conf['attributes']['soap'] : NULL, TRUE);
    $this->_subEntities = SRA_Util::convertBoolean(isset($conf['attributes']['sub-entities']) ? $conf['attributes']['sub-entities'] : NULL, TRUE);
    $this->_update = SRA_Util::convertBoolean(isset($conf['attributes']['update']) ? $conf['attributes']['update'] : NULL);
    $this->_validators = isset($conf['attributes']['validators']) ? explode(strpos($conf['attributes']['validators'], ',') ? ',' : ' ', $conf['attributes']['validators']) : array();
    $this->_view = isset($conf['attributes']['view']) ? $conf['attributes']['view'] : NULL;
    $this->_viewJson = isset($conf['attributes']['view-json']) ? $conf['attributes']['view-json'] : NULL;
    $this->_viewXml = isset($conf['attributes']['view-xml']) ? $conf['attributes']['view-xml'] : NULL;
    
    // validate
    if ($this->_api) {
      $resources =& SRA_Controller::getAppResources();
      if (!$resources->containsKey($this->_api)) {
        $msg = 'SRA_WS: Failed - invalid api resource key ' . $this->_api . ' for entity web service ' . $this->_id;
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
    }
    if (!$this->_entity) {
      $msg = 'SRA_WS: Failed - web service specified, but no entity for ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_format != SRA_WS_FORMAT_JSON && $this->_format != SRA_WS_FORMAT_RAW && $this->_format != SRA_WS_FORMAT_XML) {
      $msg = 'SRA_WS: Failed - invalid format ' . $this->_format . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_hiddenMethod && !SRA_Util::validateStaticMethodPath($this->_hiddenMethod)) {
      $msg = 'SRA_WSGlobal: Failed - Invalid hidden-method "' . $this->_hiddenMethod . '" for service ' . $this->_id;
      $this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_ipAuthenticator && !SRA_Util::validateStaticMethodPath($this->_ipAuthenticator)) {
      $msg = 'SRA_WS: Failed - invalid ip authenticator ' . $this->_ipAuthenticator . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (isset($this->_limit) && (!is_numeric($this->_limit) || $this->_limit < 1)) {
      $msg = 'SRA_WS: Failed - Invalid limit "' . $this->_limit . '" for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_metaFormat != SRA_WS_META_FORMAT_JSON && $this->_metaFormat != SRA_WS_META_FORMAT_XML && $this->_metaFormat != SRA_WS_META_FORMAT_NONE) {
      $msg = 'SRA_WS: Failed - invalid meta-format ' . $this->_metaFormat . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!$this->_create && !$this->_delete && !$this->_retrieve && !$this->_update) {
      $msg = 'SRA_WS: Failed - at least 1 CRUD functionality flag must be enabled for web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
	}
	// }}}
  
	// {{{ getApiUri
	/**
	 * returns the uri for the api documentation for this service
	 * @access public
	 * @return string
	 */
	function getApiUri() {
    return SRA_WSGateway::getServiceApiUri($this);
	}
	// }}}
  
	// {{{ getExecJsonUri
	/**
	 * returns the uri to execute this service with no parameters and display the 
   * results in json format
	 * @access public
	 * @return string
	 */
	function getExecJsonUri() {
    return SRA_WSGateway::getServiceExecJsonUri($this);
	}
	// }}}
  
	// {{{ getExecXmlUri
	/**
	 * returns the uri to execute this service with no parameters and display the 
   * results in xml format
	 * @access public
	 * @return string
	 */
	function getExecXmlUri() {
    return SRA_WSGateway::getServiceExecXmlUri($this);
	}
	// }}}
  
	// {{{ getWsdlUri
	/**
	 * returns the uri for the wsdl documentation for this service
	 * @access public
	 * @return string
	 */
	function getWsdlUri() {
    return SRA_WSGateway::getServiceWsdlUri($this);
	}
	// }}}
  
	// {{{ isPublic
	/**
	 * returns TRUE if this is a public service. public services are those with 
   * either soap or rest enabled and not hidden
	 * @access public
	 * @return boolean
	 */
	function isPublic() {
    return !$this->_hidden && (!$this->_hiddenMethod || !SRA_Util::invokeStaticMethodPath($this->_hiddenMethod)) && ($this->_rest || $this->_soap);
	}
	// }}}
  
  
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WS object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_ws');
	}
	// }}}
  
	// {{{ findValue
	/**
	 * returns the a value based on the identifier ($id) and type ($type) 
   * specified, where $type is one of the following: SRA_WS_VALUE_TYPE_CONSTANT, 
   * SRA_WS_VALUE_TYPE_GET_ATTR, SRA_WS_VALUE_TYPE_GET, 
   * SRA_WS_VALUE_TYPE_GLOBAL, SRA_WS_VALUE_TYPE_FILE, SRA_WS_VALUE_TYPE_POST, 
   * SRA_WS_VALUE_TYPE_SESSION
	 * @param string $id the value identifier
   * @param string $type the value type
	 * @access public
	 * @return mixed
	 */
  function findValue($id, $type) {
    if (!$type) {
      $val =& $id;
    }
    else {
      $pieces = explode('_', $id);
      $id = $pieces[0];
      $attr = NULL;
      for($i=1; $i<count($pieces); $i++) {
        $attr .= $attr !== NULL ? '_' : '';
        $attr .= $pieces[$i];
      }
      $val = NULL;
      switch ($type) {
        case SRA_WS_VALUE_TYPE_CONSTANT: 
          eval('$val=' . $id . ';');
          break;
        case SRA_WS_VALUE_TYPE_GET: 
          $val = isset($_GET[$id]) ? $_GET[$id] : NULL; 
          break;
        case SRA_WS_VALUE_TYPE_GLOBAL: 
          eval('global $' . $id . '; $val=$' . $id . ' ? $' . $id . ' : NULL;');
          break;
        case SRA_WS_VALUE_TYPE_FILE: 
          $val = isset($_FILES[$id]) ? $_FILES[$id] : NULL; 
          break;
        case SRA_WS_VALUE_TYPE_POST: 
          $val = isset($_POST[$id]) ? $_POST[$id] : NULL; 
          break;
        case SRA_WS_VALUE_TYPE_SESSION: 
          session_start();
          $val = isset($_SESSION[$id]) ? $_SESSION[$id] : NULL; 
          break;
      }
      if ($attr && is_object($val) && method_exists($val, 'getAttribute')) {
        $val = $val->getAttribute($attr);
      }
    }
    if (strpos($val, 'array(') === 0 || $val === 'NULL' || $val === 'TRUE' || $val === 'FALSE' || strpos($val, 'new SRA_') === 0) {
      eval('$val = ' . $val . ';');
    }
    return $val;
  }
  // }}}
  
	// {{{ validateAttrType
	/**
	 * validates an attribute type
	 * @param int $attrType the attribute type to validate
	 * @access public
	 * @return boolean
	 */
	function validateAttrType($attrType) {
		return in_array($attrType, array(SRA_WS_VALUE_TYPE_ATTR, SRA_WS_VALUE_TYPE_CONSTANT, SRA_WS_VALUE_TYPE_GET, SRA_WS_VALUE_TYPE_GET_ATTR, SRA_WS_VALUE_TYPE_GLOBAL, SRA_WS_VALUE_TYPE_POST, SRA_WS_VALUE_TYPE_SESSION));
	}
	// }}}
  
	// {{{ validateValueType
	/**
	 * validates an value type
	 * @param int $valueType the value type to validate
	 * @access public
	 * @return boolean
	 */
	function validateValueType($valueType) {
		return in_array($valueType, array(SRA_WS_VALUE_TYPE_CONSTANT, SRA_WS_VALUE_TYPE_FILE, SRA_WS_VALUE_TYPE_GET, SRA_WS_VALUE_TYPE_GLOBAL, SRA_WS_VALUE_TYPE_POST, SRA_WS_VALUE_TYPE_SESSION));
	}
	// }}}
  
}
// }}}
?>
