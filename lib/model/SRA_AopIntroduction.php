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
 * defines an attribute type introduction. the value for this introduction type 
 * will be the name of the attribute and an optional default value
 * @type string
 */
define('SRA_AOP_INTRODUCTION_TYPE_ATTRIBUTE', 'attribute');

/**
 * defines a constant type introduction. the value for this introduction type 
 * will be the name of the constant and an equals sign followed by the value
 * (strings should be enclosed in quotes)
 * @type string
 */
define('SRA_AOP_INTRODUCTION_TYPE_CONSTANT', 'constant');

/**
 * defines an include type introduction. the value for this introduction type 
 * will be the relative or full path to the php source file to include
 * @type string
 */
define('SRA_AOP_INTRODUCTION_TYPE_INCLUDE', 'include');

/**
 * defines an method type introduction. the value for this introduction type 
 * will be the method signature including the method name and parameters
 * @type string
 */
define('SRA_AOP_INTRODUCTION_TYPE_METHOD', 'method');
// }}}

// {{{ SRA_AopIntroduction
/**
 * Defines an additional include, attribute or method that should be attached to 
 * a class. for method introductions, the code for that method is also defined
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_AopIntroduction {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
   * the unique identifier for this introduction
   * @type string
   */
  var $_id;
  
  /**
	 * the entity class that this introduction applies to. this is either the dao 
   * SRA_AOP_CLASS_DAO or vo SRA_AOP_CLASS_VO
	 * @type string
	 */
	var $_class;
	
  /**
	 * if this introduction is for a method, this attribute contains the method 
   * body (the code to be executed in that method)
	 * @type string
	 */
	var $_code;
	
  /**
	 * the type of introduction this is. an introduction can be either an 
   * additional php include, class attribute or class method. this value will 
   * equal one of the SRA_AOP_INTRODUCTION_TYPE_* constants
	 * @type string
	 */
	var $_type;
	
  /**
	 * the value of the introduction. this attribute assumes  different meanings 
   * based on the type. for attributes, this is the name of the attribute 
   * (no $ needed) + an optional default value (i.e. name or name="Jason"). for 
   * includes, this is the path to the source file to include (relative or 
   * fixed). for methods, this is the method signature (name and parameters - 
   * i.e. myNewMethod($param1)) - $ are required for parameters. to return by 
   * reference, simply prefix the method name with $. for constants, the value 
   * will be the name of the constant, followed by an equals sign and the value 
   * for that constant (strings should be enclosed in quotes)
	 * @type string
	 */
	var $_value;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AopIntroduction
	/**
	 * Constructor
   * @param string $id the introduction identifier within $conf
   * @param array $conf the associative array from the xml file defining this 
   * introduction
   * @access  public
	 */
	function SRA_AopIntroduction($id, & $conf) {
    $this->_id = $id;
    $this->_class = $conf['attributes']['class'];
    $this->_code = isset($conf['xml_value']) ? $conf['xml_value'] : NULL;
    $this->_type = $conf['attributes']['type'];
    $this->_value = $this->_type == SRA_AOP_INTRODUCTION_TYPE_CONSTANT ? explode('=', $conf['attributes']['value']) : $conf['attributes']['value'];
    
    // validate
    if (!$this->_class || !$this->_value || ($this->_class != SRA_AOP_CLASS_DAO && $this->_class != SRA_AOP_CLASS_VO && $this->_class != SRA_AOP_CLASS_TEST) || 
       ($this->_type != SRA_AOP_INTRODUCTION_TYPE_ATTRIBUTE && $this->_type != SRA_AOP_INTRODUCTION_TYPE_CONSTANT && $this->_type != SRA_AOP_INTRODUCTION_TYPE_INCLUDE && $this->_type != SRA_AOP_INTRODUCTION_TYPE_METHOD) || 
       ($this->_type == SRA_AOP_INTRODUCTION_TYPE_METHOD && !$this->_code) || ($this->_type == SRA_AOP_INTRODUCTION_TYPE_CONSTANT && count($this->_value) != 2)) {
      $msg = "SRA_AopIntroduction::SRA_AopIntroduction: Failed - Missing mandatory values: class: $this->_class, code: $this->_code, type: $this->_type, value: $this->_value";
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->err = $msg;
    }
	}
	// }}}
	
  
  // public operations
	// {{{ appliesTo
	/**
	 * returns TRUE if this aspect applies to the given criteria
   * @access  public
	 */
	function appliesTo($type, $classType) {
    return (!$type || $this->_type == $type) && (!$classType || $this->_class == $classType);
	}
	// }}}
  
	// {{{ getClass
	/**
	 * getter for _class
   * @access  public
	 */
	function getClass() {
    return $this->_class;
	}
	// }}}
  
	// {{{ getCode
	/**
	 * getter for _code
   * @access  public
	 */
	function getCode() {
    return $this->_code;
	}
	// }}}
  
	// {{{ getType
	/**
	 * getter for _type
   * @access  public
	 */
	function getType() {
    return $this->_type;
	}
	// }}}
  
	// {{{ getValue
	/**
	 * getter for _value
   * @param int $idx the value array index to return for constant type introductions
   * @access  public
	 */
	function getValue($idx=FALSE) {
    return ($idx == 0 || $idx == 1) && is_array($this->_value) ? $this->_value[$idx] : $this->_value;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_AopIntroduction object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_introduction');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
