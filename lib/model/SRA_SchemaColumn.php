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

// {{{ SRA_SchemaColumn
/**
 * represents a single column in a schema table
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_SchemaColumn {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the attributes that utilize this column
	 * @type array
	 */
	var $_attributes = array();
	
  /**
	 * the base attribute name for this column. this is the first value in the 
	 * $attributes parameter passed to the constructor
	 * @type string
	 */
	var $_baseAttribute;
	
	/**
	 * whether or not this column utilizes cardinality
	 * @type boolean
	 */
	var $_cardinality;
	
	/**
	 * the upper bound cardinality for this column
	 * @type mixed
	 */
	var $_cardinalityUpper;
	
  /**
	 * the lower bound cardinality for this column
	 * @type mixed
	 */
	var $_cardinalityLower;
	
  /**
	 * a user defined column data type for this table column
	 * @type string
	 */
	var $_columnType;
	
  /**
	 * the default value to assign to new values in this column
	 * @type mixed
	 */
	var $_default;
	
  /**
	 * whether or not this column value should be lazy loaded
	 * @type boolean
	 */
	var $_lazyLoad;
	
  /**
	 * whether or not this column value should be lazy loaded exclusively, meaning 
	 * no other column can be simultaneously loaded
	 * @type boolean
	 */
	var $_lazyLoadExclusive;
	
  /**
	 * the name of the column
	 * @type string
	 */
	var $_name;
	
  /**
	 * if this column references another table/primary key value, then this 
	 * attribute defines what should happen when the record it references is 
	 * deleted. If FALSE, the value of this column will be set to NULL, otherwise 
	 * the entire row that this column belongs to will be deleted
	 * @type boolean
	 */
	var $_onDeleteCascade;
	
  /**
	 * an associative array of all of the read constraints for this column indexed 
	 * by attribute name. this value will correspond with the "constraint" 
	 * specified for that attribute in the entity model xml
	 * @type array
	 */
	var $_readConstraints = array();
	
  /**
	 * whether or not this column is read-only (cannot be written to using INSERT/
	 * UPDATE queries)
	 * @type boolean
	 */
	var $_readOnly;
	
  /**
	 * the references constraint for this column
	 * @type string
	 */
	var $_references;
	
  /**
	 * the name of an optional retrieve function (will be applied to SELECT queries)
	 * @type string
	 */
	var $_retrieveFunction;
	
  /**
	 * whether or not this column is a database generated sequence
	 * @type boolean
	 */
	var $_sequence;
	
  /**
	 * the name of an optional set function (will be applied to INSERT/UPDATE queries)
	 * @type string
	 */
	var $_setFunction;
	
  /**
	 * whether or not this column is set-only (cannot be read using SELECT 
	 * queries)
	 * @type boolean
	 */
	var $_setOnly;
	
  /**
	 * the column data type. this will correspond with one of the 
	 * SRA_DATA_TYPE_* constants
	 * @type string
	 */
	var $_type;
	
  /**
	 * an array of validators associated with this column
	 * @type string[]
	 */
	var $_validators;
	
  /**
	 * an associative array of validator variables
	 * @type array
	 */
	var $_vars;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_SchemaColumn
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_SchemaColumn($attributes = FALSE, $name = FALSE) {
		$this->setAttributes($attributes);
		$this->setBaseAttribute($attributes[0]);
		$this->setName($name);
	}
	// }}}
	
  
  // public operations
	// {{{ addAttribute
	/**
	 * adds a attribute to the array representing those that this column pertains to
	 * @param string $attribute the attribute to add
   * @access  public
	 * @return void
	 */
	function addAttribute($attribute) {
		if (is_array($attribute)) {
			$keys = array_keys($attribute);
			foreach ($keys as $key) {
				$this->addAttribute($attribute[$key]);
			}
		}
		else {
			if (!in_array($attribute, $this->_attributes)) {
				$this->_attributes[] = $attribute;
			}
		}
	}
	// }}}
	
	// {{{ addReadConstraint
	/**
	 * adds a readConstraint
	 * @param string $attribute the name of the attribute that this read 
	 * constraint is for
	 * @param string $readConstraint the readConstraint to add
   * @access  public
	 * @return void
	 */
	function addReadConstraint($attribute, $readConstraint) {
		$this->_readConstraints[$attribute] = $readConstraint;
	}
	// }}}
	
	// {{{ getAttribute
	/**
	 * returns the attributes corresponding to this column
   * @access  public
	 * @return array
	 */
	function getAttributes() {
		return $this->_attributes;
	}
	// }}}
	
	// {{{ getBaseAttribute
	/**
	 * returns the baseAttribute value for this column
   * @access  public
	 * @return string
	 */
	function getBaseAttribute() {
		return $this->_baseAttribute;
	}
	// }}}
	
	// {{{ getCardinalityLower
	/**
	 * returns the cardinalityLower of this column
   * @access  public
	 * @return string
	 */
	function getCardinalityLower() {
		return $this->_cardinalityLower;
	}
	// }}}
	
	// {{{ getCardinalityUpper
	/**
	 * returns the cardinalityUpper of this column
   * @access  public
	 * @return string
	 */
	function getCardinalityUpper() {
		return $this->_cardinalityUpper;
	}
	// }}}
	
	// {{{ getColumnType
	/**
	 * returns the columnType value for this column
   * @access  public
	 * @return string
	 */
	function getColumnType() {
		return $this->_columnType;
	}
	// }}}
	
	// {{{ getDefault
	/**
	 * returns the default value for this column
   * @access  public
	 * @return string
	 */
	function getDefault() {
		return $this->_default;
	}
	// }}}
	
	// {{{ getName
	/**
	 * returns the name of this column
   * @access  public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}
	// }}}
	
	// {{{ getReadConstraints
	/**
	 * returns the readConstraints for this column
	 * @param string $attribute the attribute to return the read constraint for. 
	 * if not specified, the entire read constraints array will be returned
	 * @param boolean $exploded if TRUE, the results will be exploded into an 
	 * associative array of column/value pairs corresponding to the read 
	 * constraint 
   * @access  public
	 * @return string
	 */
	function getReadConstraints($attribute=FALSE, $exploded=FALSE) {
		$results = $attribute ? $this->_readConstraints[$attribute] : $this->_readConstraints;
		if ($results && $exploded) {
			$tmp = array();
			$results = explode(',', $results);
			foreach ($results as $result) {
				$pieces = explode('=', $result);
				$col = trim($pieces[0]);
				$val = trim($pieces[1]);
				$val = SRA_Util::stripQuotes($val, "'", "'");
				$val = SRA_Util::stripQuotes($val);
				$tmp[$col] = $val;
			}
			$results = $tmp;
		}
		return $results;
	}
	// }}}
	
	// {{{ getReferences
	/**
	 * returns the name of the table whose primary key this column references
   * @access  public
	 * @return string
	 */
	function getReferences() {
		return $this->_references;
	}
	// }}}
	
	// {{{ getRetrieveFunction
	/**
	 * returns the retrieveFunction used by this column
   * @access  public
	 * @return string
	 */
	function getRetrieveFunction() {
		return $this->_retrieveFunction;
	}
	// }}}
	
	// {{{ getSetFunction
	/**
	 * returns the setFunction used by this column
   * @access  public
	 * @return string
	 */
	function getSetFunction() {
		return $this->_setFunction;
	}
	// }}}
	
	// {{{ getType
	/**
	 * returns data type for this column
   * @access  public
	 * @return string
	 */
	function getType() {
		return $this->_type;
	}
	// }}}
	
	// {{{ getValidators
	/**
	 * returns the column validators
   * @access  public
	 * @return string[]
	 */
	function getValidators() {
		return $this->_validators;
	}
	// }}}
	
	// {{{ getVars
	/**
	 * returns the vars used by the validators for this column
	 * @param string $id an optional specific identifier or a var value to return
   * @access  public
	 * @return array
	 */
	function getVars($id=FALSE) {
		return $id && isset($this->_vars[$id]) ? $this->_vars[$id] : $this->_vars;
	}
	// }}}
	
	// {{{ hasAttribute
	/**
	 * returns TRUE if the specified attribute uses this column
	 * @param string $attr the attribute to check for
   * @access  public
	 * @return boolean
	 */
	function hasAttribute($attr) {
		return in_array($attr, $this->_attributes);
	}
	// }}}
	
	// {{{ hasValidator
	/**
	 * returns TRUE if the specified validator has been set for this column
	 * @param string $validator the constraint to check for
   * @access  public
	 * @return boolean
	 */
	function hasValidator($validator) {
		return is_array($this->_validators) && in_array($validator, $this->_validators);
	}
	// }}}
	
	// {{{ isCardinality
	/**
	 * returns TRUE if this column utilizes cardinality
   * @access  public
	 * @return boolean
	 */
	function isCardinality() {
		return $this->_cardinality;
	}
	// }}}
	
	// {{{ isLazyLoad
	/**
	 * returns TRUE if this table should be lazy loaded
   * @access  public
	 * @return boolean
	 */
	function isLazyLoad() {
		return $this->_lazyLoad;
	}
	// }}}
	
	// {{{ isLazyLoadExclusive
	/**
	 * returns TRUE if this table should be lazy loaded exclusively
   * @access  public
	 * @return boolean
	 */
	function isLazyLoadExclusive() {
		return $this->_lazyLoadExclusive;
	}
	// }}}
	
	// {{{ isOnDeleteCascade
	/**
	 * returns TRUE if deletes should be cascaded to this column
   * @access  public
	 * @return boolean
	 */
	function isOnDeleteCascade() {
		return $this->_onDeleteCascade;
	}
	// }}}
	
	// {{{ isReadOnly
	/**
	 * returns the $_readOnly flag for this column
   * @access  public
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}
	// }}}
	
	// {{{ isSequence
	/**
	 * returns the $_sequence flag for this column
   * @access  public
	 * @return boolean
	 */
	function isSequence() {
		return $this->_sequence;
	}
	// }}}
	
	// {{{ isSetOnly
	/**
	 * returns the $_setOnly flag for this column
   * @access  public
	 * @return boolean
	 */
	function isSetOnly() {
		return $this->_setOnly;
	}
	// }}}
	
	// {{{ isWritable
	/**
	 * returns TRUE if this column should be included in update and insert queries
	 * a column is writable is it is NOT a sequence, and it is not read only 
   * (unless insert is TRUE)
   * @param boolean $insert whether or not this is being called for the insert 
   * method (read-only attributes are writable for the initial insert)
   * @access  public
	 * @return boolean
	 */
	function isWritable($insert=FALSE) {
		return !$this->isSequence() && ($insert || !$this->isReadOnly());
	}
	// }}}
	
	// {{{ setAttribute
	/**
	 * sets the attributes that this column corresponds to
	 * @param array $attributes the attributes to set
   * @access  public
	 * @return void
	 */
	function setAttributes($attributes) {
		$this->_attributes = is_array($attributes) ? $attributes : array($attributes);
	}
	// }}}
	
	// {{{ setBaseAttribute
	/**
	 * sets the column baseAttribute value
	 * @param string $baseAttribute the baseAttribute value to set
   * @access  public
	 * @return void
	 */
	function setBaseAttribute($baseAttribute) {
		$this->_baseAttribute = $baseAttribute;
	}
	// }}}
	
	// {{{ setCardinality
	/**
	 * sets the column cardinality flag
	 * @param string $cardinality the cardinality to set
   * @access  public
	 * @return void
	 */
	function setCardinality($cardinality) {
		$this->_cardinality = $cardinality;
	}
	// }}}
	
	// {{{ setCardinalityLower
	/**
	 * sets the column cardinalityLower constraint
	 * @param int $cardinalityLower the cardinalityLower to set
   * @access  public
	 * @return void
	 */
	function setCardinalityLower($cardinalityLower) {
		$this->_cardinalityLower = $cardinalityLower;
	}
	// }}}
	
	// {{{ setCardinalityUpper
	/**
	 * sets the column cardinalityUpper constraint
	 * @param int $cardinalityUpper the cardinalityUpper to set
   * @access  public
	 * @return void
	 */
	function setCardinalityUpper($cardinalityUpper) {
		$this->_cardinalityUpper = $cardinalityUpper;
	}
	// }}}
	
	// {{{ setColumnType
	/**
	 * sets the column columnType value
	 * @param string $columnType the columnType value to set
   * @access  public
	 * @return void
	 */
	function setColumnType($columnType) {
		$this->_columnType = $columnType;
	}
	// }}}
	
	// {{{ setDefault
	/**
	 * sets the column default value
	 * @param string $default the default value to set
   * @access  public
	 * @return void
	 */
	function setDefault($default) {
		$this->_default = $default;
	}
	// }}}
	
	// {{{ setLazyLoad
	/**
	 * sets the table lazyLoad flag
	 * @param string $lazyLoad the flag to set
   * @access  public
	 * @return void
	 */
	function setLazyLoad($lazyLoad) {
		$this->_lazyLoad = $lazyLoad;
	}
	// }}}
	
	// {{{ setLazyLoadExclusive
	/**
	 * sets the table lazyLoadExclusive flag
	 * @param string $lazyLoadExclusive the flag to set
   * @access  public
	 * @return void
	 */
	function setLazyLoadExclusive($lazyLoadExclusive) {
		$this->_lazyLoadExclusive = $lazyLoadExclusive;
	}
	// }}}
	
	// {{{ setName
	/**
	 * sets the column name
	 * @param string $name the name to set
   * @access  public
	 * @return void
	 */
	function setName($name) {
		$this->_name = $name;
	}
	// }}}
	
	// {{{ setOnDeleteCascade
	/**
	 * sets the column onDeleteCascade
	 * @param string $onDeleteCascade the onDeleteCascade to set
   * @access  public
	 * @return void
	 */
	function setOnDeleteCascade($onDeleteCascade) {
		$this->_onDeleteCascade = $onDeleteCascade;
	}
	// }}}
	
	// {{{ setReadConstraints
	/**
	 * sets the read constraints for this column
	 * @param array $readConstraints the read constraints to set
   * @access  public
	 * @return void
	 */
	function setReadConstraints($readConstraints) {
		$this->_readConstraints = $readConstraints;
	}
	// }}}
	
	// {{{ setReadOnly
	/**
	 * sets the column read only flag
	 * @param string $readOnly the read only flag to set
   * @access  public
	 * @return void
	 */
	function setReadOnly($readOnly) {
		$this->_readOnly = $readOnly;
	}
	// }}}
	
	// {{{ setReferences
	/**
	 * sets the name of the table whose primary key this column references
	 * @param string $references the name of the table
   * @access  public
	 * @return void
	 */
	function setReferences($references) {
		$this->_references = $references;
	}
	// }}}
	
	// {{{ setRetrieveFunction
	/**
	 * sets the column retrieveFunction
	 * @param string $retrieveFunction the retrieveFunction to set
   * @access  public
	 * @return void
	 */
	function setRetrieveFunction($retrieveFunction) {
		$this->_retrieveFunction = $retrieveFunction;
	}
	// }}}
	
	// {{{ setSequence
	/**
	 * designates this column as a sequence
	 * @param string $sequence the flag to set
   * @access  public
	 * @return void
	 */
	function setSequence($sequence) {
		$this->_sequence = $sequence;
	}
	// }}}
	
	// {{{ setSetFunction
	/**
	 * sets the column setFunction
	 * @param string $setFunction the setFunction to set
   * @access  public
	 * @return void
	 */
	function setSetFunction($setFunction) {
		$this->_setFunction = $setFunction;
	}
	// }}}
	
	// {{{ setSetOnly
	/**
	 * sets the column set only flag
	 * @param string $setOnly the set only flag to set
   * @access  public
	 * @return void
	 */
	function setSetOnly($setOnly) {
		$this->_setOnly = $setOnly;
	}
	// }}}
	
	// {{{ setType
	/**
	 * sets the column data type
	 * @param string $type the type to set
   * @access  public
	 * @return void
	 */
	function setType($type) {
		if (SRA_EntityModeler::isValidType($type)) {
			$this->_type = $type;
		}
	}
	// }}}
	
	// {{{ setValidators
	/**
	 * sets the column validators
	 * @param string[] $validators the validators to set
   * @access  public
	 * @return void
	 */
	function setValidators($validators) {
		if (is_array($validators)) {
			$this->_validators = $validators;
		}
	}
	// }}}
	
	// {{{ setVars
	/**
	 * sets the vars used by the validators for this column
	 * @param array $vars the vars to set
   * @access  public
	 * @return void
	 */
	function setVars($vars) {
		if (is_array($vars)) {
			$this->_vars = $vars;
		}
	}
	// }}}
	
	// {{{ updateAttributeName
	/**
	 * changes an attribute name
	 * @param string $attribute the current name of the attribute to change
	 * @param string $newName the new name for the attribute
   * @access  public
	 * @return boolean
	 */
	function updateAttributeName($attribute, $newName) {
		$keys = array_keys($this->_attributes);
		foreach($keys as $key) {
			if ($this->_attributes[$key] == $attribute) {
				$this->_attributes[$key] = $newName;
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_SchemaColumn object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_schemacolumn');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
