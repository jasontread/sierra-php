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

// }}}

// {{{ SRA_WSParam
/**
 * used to define a global web service param
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSParam {
  // public attributes
	
  /**
	 * the unique name of the parameter
	 * @type string
	 */
	var $_name;
  
  /**
   * whether or not this parameter can be overriden by the request
   * @type boolean
   */
  var $_allowOverride;
  
  /**
	 * the value of the parameter
	 * @type string
	 */
	var $_value;
  
  /**
	 * the value type
	 * @type string
	 */
	var $_valueType;
  
  
	// {{{ SRA_WSParam
	/**
	 * parses the constraint configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @access public
	 */
	function SRA_WSParam($conf) {
		$this->_name = isset($conf['attributes']['name']) ? $conf['attributes']['name'] : $conf['attributes']['key'];
    $this->_allowOverride = isset($conf['attributes']['allow-override']) && $conf['attributes']['allow-override'] == '1' ? TRUE : FALSE;
    $this->_valueType = isset($conf['attributes']['value-type']) ? $conf['attributes']['value-type'] : NULL;
    $this->_value = $this->_valueType == SRA_WS_VALUE_TYPE_FILE && $_FILES[$this->_name] ? $this->_name : (isset($conf['attributes']['value']) ? $conf['attributes']['value'] : $conf['xml_value']);
    
    if ($this->_valueType && !SRA_WS::validateValueType($this->_valueType)) {
      $msg = "SRA_WSParam: Failed - invalid valueType $this->_valueType for sql param " . $this->_name;
      $this->_err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
	}
	// }}}
	
	
	// static methods
	
	// {{{ isValid
	/**
	 * static method that returns true if the object parameter is a SRA_WSParam object
	 * @param object $object The object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsparam');
	}
	// }}}
  
}
// }}}
?>
