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
require_once('SRA_QueryBuilderConstraint.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_WSConstraint
/**
 * used to add implicit security or filter related constraint to an web service
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSConstraint {
  // {{{ Attributes
  // public attributes
	
  /**
	 * the constraint attribute identifier
	 * @type string
	 */
	var $_attr;
  
  /**
	 * defines where the attribute to be constrained exists
	 * @type string
	 */
	var $_attrType;
  
  /**
	 * whether or not this attribute should be automatically set for write request 
   * actions (create or update)
	 * @type boolean
	 */
  var $_autoSet;
  
  /**
   * the connective to apply if the value for this constraint is an array
   * @type string
   */
  var $_connective;
  
  /**
	 * a bitmask containing one or more of the following operator values
	 * @type int
	 */
	var $_operator;
  
  /**
	 * the constraint value or value identifier if value-type is specified
	 * @type string
	 */
	var $_value;
  
  /**
	 * if the value for this constraint is not provided explicitely in the value 
   * attribute above (the default behavior), then this attribute defines where 
   * the value exists
	 * @type string
	 */
	var $_valueType;
  
  // private attributes
  
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WSConstraint
	/**
	 * parses the constraint configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param string $connective the connective to apply if the value for this 
   * constraint is an array
   * @access  public
	 */
	function SRA_WSConstraint($conf, $connective=SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE) {
		$this->_attr = $conf['attributes']['attr'];
    $this->_attrType = isset($conf['attributes']['attr-type']) ? $conf['attributes']['attr-type'] : SRA_WS_VALUE_TYPE_ATTR;
    $this->_autoSet = isset($conf['attributes']['auto-set']) && $conf['attributes']['auto-set'] == '1' ? TRUE : FALSE;
    $this->_connective = $connective;
    $this->_operator = isset($conf['attributes']['operator']) ? $conf['attributes']['operator'] : SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL;
    $this->_value = isset($conf['attributes']['value']) ? $conf['attributes']['value'] : NULL;
    $this->_valueType = isset($conf['attributes']['value-type']) ? $conf['attributes']['value-type'] : NULL;
    
    if (!SRA_WS::validateAttrType($this->_attrType)) {
      $msg = "SRA_WSConstraint: Failed - invalid attrType $this->_attrType for web service constraint " . $this->_attr;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!SRA_QueryBuilderConstraint::validateConstraint($this->_operator)) {
      $msg = 'SRA_WSConstraint: Failed - invalid operator ' . $this->_operator . ' for web service constraint ' . $this->_attr;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if ($this->_valueType && !SRA_WS::validateValueType($this->_valueType)) {
      $msg = "SRA_WSConstraint: Failed - invalid valueType $this->_valueType for web service constraint " . $this->_attr;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
	}
	// }}}
	
  
  // public operations
  
	// {{{ evaluate
	/**
	 * returns TRUE if this constraint is a non-query constraint and the 
   * evaluation of it returns TRUE, returns FALSE otherwise
   * @param boolean $forceAll whether or not to force evaluation of even query 
   * constraints
	 * @access	public
	 * @return	boolean
	 */
  function evaluate($forceAll=FALSE) {
    if ($this->isSortingConstraint()) { return TRUE; }
    
    return $forceAll || $this->isNonQueryConstraint() ? $this->_evaluateAttr($this->getAttr()) : FALSE;
  }
  // }}}
  
	// {{{ evaluateEntity
	/**
	 * returns TRUE if $entity is valid against the constraints contained within 
   * this group. if it is not valid for an equality constraint, that constraint 
   * value will be automatically set into $entity
   * @param object $entity the entity to validate
   * @param boolean $set whether or not to set the entity attribute if the 
   * constraint operator includes SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL. if 
   * this occurs, thie method will return TRUE
   * @param boolean $forceAll whether or not to force evaluation of even query 
   * constraints
	 * @access	public
	 * @return	boolean
	 */
  function evaluateEntity(&$entity, $set=TRUE, $forceAll=FALSE) {
    if ($this->isSortingConstraint()) { return TRUE; }
    
    if (!$this->isNonQueryConstraint() || $this->_attrType == SRA_WS_VALUE_TYPE_GET_ATTR) {
      if (!$this->_evaluateAttr($entity->getAttribute($this->_attr))) {
        // if constraint includes equal operator, try to set that value
        if ($this->_autoSet && $set && ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL)) {
          $entity->setAttribute($this->_attr, $this->getValue());
          return TRUE;
        }
        return FALSE;
      }
      else {
        return TRUE;
      }
    }
    else {
      return $this->evaluate($forceAll);
    }
  }
  // }}}
  
	// {{{ getAttr
	/**
	 * returns the actual value to use for $attr for non-query constraints
	 *
	 * @access	public
	 * @return	mixed
	 */
  function getAttr() {
    return SRA_WS::findValue($this->_attr, $this->_attrType);
  }
  // }}}
  
	// {{{ getValue
	/**
	 * returns the actual value to use for $value
	 *
	 * @access	public
	 * @return	mixed
	 */
  function getValue() {
    return $this->_valueType ? SRA_WS::findValue($this->_value, $this->_valueType) : $this->_value;
  }
  // }}}
  
	// {{{ isNonQueryConstraint
	/**
	 * returns TRUE if this constraint is a non-query constraint
	 *
	 * @access	public
	 * @return	boolean
	 */
  function isNonQueryConstraint() {
    return $this->_attrType != SRA_WS_VALUE_TYPE_ATTR;
  }
  // }}}
  
	// {{{ toQueryConstraint
	/**
	 * converts this web service constraint into an equivalent query group
	 * @access	public
	 * @return	SRA_QueryBuilderConstraint[]
	 */
  function &toQueryConstraints() {
    $constraints = array();
    $value = $this->getValue();
    if (!is_array($value)) { $value = array($value); }
    $keys = array_keys($value);
    foreach($keys as $key) {
      $constraints[] = new SRA_QueryBuilderConstraint($this->_attr, $this->_operator, $value[$key]);
    }
    return $constraints;
  }
  // }}}
  
	// {{{ isSortingConstraint
	/**
	 * returns TRUE if this constraint is a sorting constraint
	 * @access	public
	 * @return	boolean
	 */
  function isSortingConstraint() {
    return $this->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_ASC || $this->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC;
  }
  // }}}
  
	// {{{ isStringConstraint
	/**
	 * returns TRUE if this constraint is a string constraint
	 * @access	public
	 * @return	boolean
	 */
  function isStringConstraint() {
    return $this->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH || 
           $this->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH || 
           $this->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_IN_STR;
  }
  // }}}
  
  
  // Private methods
  
	// {{{ _evaluateAttr
	/**
	 * evaluates this constraint value against the $attr value specified
	 *
   * @param mixed $attr the attribute value to evaluate
	 * @access	public
	 * @return	SRA_QueryBuilderConstraint
	 */
  function _evaluateAttr($attr) {
    if ($this->isSortingConstraint()) { return TRUE; }
    
    if (SRA_GregorianDate::isValid($attr)) {
      $attr = $attr->toIntDateTime();
      $isDate = TRUE;
    }
    else if (is_array($attr)) {
      $attr = join(',', $attr);
      $isArray = TRUE;
    }
    
    $eval = '$results = ';
    if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_NOT) {
      $eval .= '!';
    }
    $eval .= '(';
    $value = $this->getValue();
    if (!is_array($value)) { $value = array($value); }
    $keys = array_keys($value);
    foreach($keys as $key) {
      if ($isDate) {
        if (is_scalar($value[$key])) { $value[$key] = new SRA_GregorianDate($value[$key]); }
        $value[$key] = $value[$key]->toIntDateTime();
      }
      else if ($isArray && is_array($value[$key])) {
        $value[$key] = join(',', $value[$key]);
      }
      
      $sattr = isset($attr) && (!is_numeric($attr) || $this->isStringConstraint()) ? '"' . str_replace('"', '\"', $attr) . '"' : (isset($attr) ? $attr : 'NULL');
      $svalue = isset($value[$key]) && (!is_numeric($value[$key]) || $this->isStringConstraint()) ? '"' . str_replace('"', '\"', $value[$key]) . '"' : (isset($value[$key]) ? $value[$key] : 'NULL');
      // string constraint
      if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH) {
        $eval .= 'SRA_Util::beginsWith(' . $sattr . ', ' . $svalue . ')';
      }
      else if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH) {
        $eval .= 'SRA_Util::endsWith(' . $sattr . ', ' . $svalue . ')';
      }
      else if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_IN_STR) {
        $eval .= 'strstr(' . $sattr . ', ' . $svalue . ')';
      }
      else {
        $eval .= $sattr;
        $eval .= $this->_getPhpOperator();
        $eval .= $svalue;
      }
      $eval .= ');';
      eval($eval);
      $results = (bool) $results;
      if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE && !$results) { return FALSE; }
      if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE && $results) { return TRUE; }
    }
    return $this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE ? TRUE : FALSE;
  }
  // }}}
  
	// {{{ _getPhpOperator
	/**
	 * returns the PHP operator to use for this constraint. i.e. '<', '<=', etc.
   * @access  public
	 * @return string
	 */
	function _getPhpOperator() {
		$op = '';
		if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_GREATER) {
			$op .= '>';
		}
		else if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_LESS) {
			$op .= '<';
		}
		if ($this->_operator & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL) {
			$op .= $op == '' ? '==' : '=';
		}
		return $op;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WSConstraint object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsconstraint');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
