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

// {{{ SRA_ValidateGenerator
/**
 * Used to generate an entity model validation constraint
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.core
 */
class SRA_ValidateGenerator extends SRA_Generator {
  // {{{ Attributes
  // public attributes
  
  // private attributes
  var $_equal;
  var $_mandatory;
	var $_name;
  var $_notEqual;
	var $_requireAll;
	var $_resource;
	var $_validationSet = array();
  var $_usePhpFalse;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_ValidateGenerator
	/**
	 * Creates a new SRA_ValidateGenerator with the configuration specified
	 * @param array $conf associative array of validation configuration values
   * @access  private
	 */
	function SRA_ValidateGenerator(& $conf) {
		// validate required key, resource
		if (!isset($conf['attributes']['key']) || !isset($conf['attributes']['resource']) || !isset($conf['attributes']['attrs'])) {
			$msg = "SRA_ValidateGenerator::SRA_ValidateGenerator: Failed - Missing 'key' or 'resource' configuration values for validator " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		// invalid resource
		$rb =& SRA_Controller::getAppResources();
		if (SRA_Error::isError($rb->getString($conf['attributes']['resource']))) {
			$msg = "SRA_ValidateGenerator::SRA_ValidateGenerator: Failed - Resource '" . $conf['attributes']['resource'] . "' is not valid for validator " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
    $this->_equal = isset($conf['attributes']['equal']) && $conf['attributes']['equal'] == '1';
    $this->_evalPhpFalse = isset($conf['attributes']['eval-php-false']) && $conf['attributes']['eval-php-false'] == '1';
    $this->_mandatory = isset($conf['attributes']['mandatory']) && $conf['attributes']['mandatory'] == '1';
		$this->_name = $conf['attributes']['key'];
		$this->_notEqual = isset($conf['attributes']['not-equal']) && $conf['attributes']['not-equal'] == '1';
    $this->_requireAll = !isset($conf['attributes']['require-all']) || (isset($conf['attributes']['require-all']) && $conf['attributes']['require-all'] == '1');
		$this->_resource = $conf['attributes']['resource'];
    
		$attrs = explode(' ', $conf['attributes']['attrs']);
    $keys = array_keys($attrs);
		foreach($keys as $key) {
      $this->_validationSet[$key] = array();
      $this->_validationSet[$key]['attrs'] = array($attrs[$key]);
      $this->_validationSet[$key]['attr'] = $attrs[$key];
      $this->_validationSet[$key]['join'] = '||';
      if (strstr($attrs[$key], '=')) {
        if (SRA_Util::beginsWith($attrs[$key], '!')) {
          $this->_validationSet[$key]['not'] = TRUE;
          $attrs[$key] = substr($attrs[$key], 1);
        }
        $pieces = explode('=', $attrs[$key]);
        $this->_validationSet[$key]['if'] = $pieces[0];
        $this->_validationSet[$key]['attrs'] = strstr($pieces[1], '^') ? explode('^', $pieces[1]) : (strstr($pieces[1], '|') ? explode('|', $pieces[1]) : array($pieces[1]));
        if (strstr($pieces[1], '|')) { $this->_validationSet[$key]['join'] = '&&'; }
      }
    }
	}
	// }}}
	
  
  // public operations  
	
  
  // private operations
  
  
  // static operations
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_ValidateGenerator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_validategenerator');
	}
	// }}}

  
}
// }}}
?>
