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
require_once('SRA_WS.php');
require_once('SRA_WSParam.php');
// }}}

// {{{ Constants
/**
 * identifier for the method service type
 * @type string
 */
define('SRA_WS_GLOBAL_TYPE_METHOD', 'method');

/**
 * identifier for the rb service type
 * @type string
 */
define('SRA_WS_GLOBAL_TYPE_RB', 'rb');

/**
 * identifier for the sql service type
 * @type string
 */
define('SRA_WS_GLOBAL_TYPE_SQL', 'sql');
// }}}

// {{{ SRA_WSGlobal
/**
 * used to define a global (non entity specific) web service
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSGlobal {
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
   * used to distinguish this class from SRA_WS when references to both are 
   * mixed
   * @type boolean
   */
  var $_global = TRUE;
  
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
   * the identifier for this service
   * @type string
   */
  var $_identifier;
  
  /**
   * whether or not to introspect the class for api documentation if this is a 
   * method type service. this documentation will be used then in the auto 
   * generation of api documentation. for more information, see the 
   * documentation provided in SRA_Util::parsePhpSource regarding how to 
   * document source files
   * @type boolean
   */
  var $_introspectApi;
  
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
	 * the params associated with this web service definition
	 * @type SRA_WSParam[]
	 */
	var $_params = array();
  
  /**
   * whether or not to enable this service via rest
   * @type boolean
   */
  var $_rest;
  
  /**
   * whether or not to enable this service via soap
   * @type boolean
   */
  var $_soap;
  
  /**
   * the service type, either method, rb or sql
   * @type string
   */
  var $_type;
  
  
  
	// {{{ SRA_WSGlobal
	/**
	 * parses the service configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @access  public
	 */
	function SRA_WSGlobal($conf) {
    $this->_id = $conf['attributes']['key'];
    $this->_api = isset($conf['attributes']['api']) ? $conf['attributes']['api'] : NULL;
    $this->_authenticate = SRA_Util::convertBoolean(isset($conf['attributes']['authenticate']) ? $conf['attributes']['authenticate'] : NULL, TRUE);
    $this->_format = isset($conf['attributes']['format']) ? $conf['attributes']['format'] : SRA_WS_FORMAT_JSON;
    $this->_formatFixed = SRA_Util::convertBoolean(isset($conf['attributes']['format-fixed']) ? $conf['attributes']['format-fixed'] : NULL);
    $this->_hidden = SRA_Util::convertBoolean(isset($conf['attributes']['hidden']) ? $conf['attributes']['hidden'] : NULL);
    $this->_hiddenMethod = isset($conf['attributes']['hidden-method']) ? $conf['attributes']['hidden-method'] : NULL;
    $this->_identifier = isset($conf['attributes']['identifier']) ? $conf['attributes']['identifier'] : NULL;
    $this->_introspectApi = SRA_Util::convertBoolean(isset($conf['attributes']['introspect-api']) ? $conf['attributes']['introspect-api'] : NULL, TRUE);
    $this->_ipAuthenticator = isset($conf['attributes']['ip-authenticator']) ? $conf['attributes']['ip-authenticator'] : NULL;
    $this->_limit = isset($conf['attributes']['limit']) ? $conf['attributes']['limit'] : NULL;
    $this->_limitFixed = SRA_Util::convertBoolean(isset($conf['attributes']['limit-fixed']) ? $conf['attributes']['limit-fixed'] : NULL);
    $this->_metaFormat = isset($conf['attributes']['meta-format']) ? $conf['attributes']['meta-format'] : ($this->_format == SRA_WS_FORMAT_RAW ? SRA_WS_META_FORMAT_NONE : $this->_format);
    $this->_metaFormatFixed = SRA_Util::convertBoolean(isset($conf['attributes']['meta-format-fixed']) ? $conf['attributes']['meta-format-fixed'] : NULL);
    $this->_rest = SRA_Util::convertBoolean(isset($conf['attributes']['rest']) ? $conf['attributes']['rest'] : NULL, TRUE);
    $this->_soap = SRA_Util::convertBoolean(isset($conf['attributes']['soap']) ? $conf['attributes']['soap'] : NULL, TRUE);
	
    if (isset($conf['ws-param'])) {
			$keys = array_keys($conf['ws-param']);
			foreach ($keys as $key) {
				if (!SRA_WSParam::isValid($this->_params[$key] = new SRA_WSParam($conf['ws-param'][$key]))) {
					$msg = "SRA_WSGlobal: Failed - sql param ${key} produced error for global service " . $this->_id;
					$this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
			}
    }
    $this->_type = isset($conf['attributes']['type']) ? $conf['attributes']['type'] : SRA_WS_GLOBAL_TYPE_METHOD;
    
    // validate
    if ($this->_api) {
      $resources =& SRA_Controller::getAppResources();
      if (!$resources->containsKey($this->_api)) {
        $msg = 'SRA_WSGlobal: Failed - invalid api resource key ' . $this->_api . ' for entity web service ' . $this->_id;
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
    }
    if (($this->_format != SRA_WS_FORMAT_JSON && $this->_format != SRA_WS_FORMAT_XML && $this->_format != SRA_WS_FORMAT_RAW) || ($this->_format == SRA_WS_FORMAT_RAW && $this->_type != SRA_WS_GLOBAL_TYPE_METHOD)) {
      $msg = 'SRA_WSGlobal: Failed - invalid format ' . $this->_format . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (isset($this->_hiddenMethod) && !SRA_Util::validateStaticMethodPath($this->_hiddenMethod)) {
      $msg = 'SRA_WSGlobal: Failed - Invalid hidden-method "' . $this->_hiddenMethod . '" for service ' . $this->_id;
      $this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!isset($this->_identifier) || ($this->_type == SRA_WS_GLOBAL_TYPE_METHOD && !SRA_Util::validateStaticMethodPath($this->_identifier))) {
      $msg = 'SRA_WSGlobal: Failed - Identifier missing or invalid for global service ' . $this->_id;
      $this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_ipAuthenticator && !SRA_Util::validateStaticMethodPath($this->_ipAuthenticator)) {
      $msg = 'SRA_WSGlobal: Failed - invalid ip authenticator ' . $this->_ipAuthenticator . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (isset($this->_limit) && (!is_numeric($this->_limit) || $this->_limit < 1)) {
      $msg = 'SRA_WSGlobal: Failed - Invalid limit "' . $this->_limit . '" for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_metaFormat != SRA_WS_META_FORMAT_JSON && $this->_metaFormat != SRA_WS_META_FORMAT_XML && $this->_metaFormat != SRA_WS_META_FORMAT_NONE) {
      $msg = 'SRA_WSGlobal: Failed - invalid meta-format ' . $this->_metaFormat . ' for entity web service ' . $this->_id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_type != SRA_WS_GLOBAL_TYPE_METHOD && $this->_type != SRA_WS_GLOBAL_TYPE_RB && $this->_type != SRA_WS_GLOBAL_TYPE_SQL) {
      $msg = "SRA_WSGlobal: Failed - Invalid type $this->_type for global service " . $this->_id;
      $this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
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
	 * Static method that returns true if the object parameter is a SRA_WSGlobal object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsglobal');
	}
	// }}}
  
}
// }}}
?>
