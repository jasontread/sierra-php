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
// Only 1 constraint type should be specified in any type constraint

// EQUALITY CONSTRAINTS
/**
 * bit defining an equality match for a value constraint
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL', 1);

/**
 * bit defining a greater than match for a value constraint. both GREATER and 
 * LESS bits cannot be set
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_GREATER', 2);

/**
 * bit defining a less than match for a value constraint
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_LESS', 4);


// STRING CONSTRAINTS
/**
 * bit defining a starts with than match for a value constraint. string matching 
 * constraints cannot also have the equality matching (><=) bits set
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH', 8);

/**
 * bit defining a ends with than match for a value constraint. string matching 
 * constraints cannot also have the equality matching (><=) bits set
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH', 16);

/**
 * bit defining an in string match for a value constraint. string matching 
 * constraints cannot also have the equality matching (><=) bits set. has the 
 * safe effect as setting both SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH 
 * and SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH bits
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_IN_STR', 32);


// SORTING CONSTRAINTS
/**
 * bit defining a sort ascending constraint. sorting constraints cannot have any 
 * value constraint bits
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_ASC', 64);

/**
 * bit defining a sort descending constraint. sorting constraints cannot have any 
 * value constraint bits
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC', 128);


// NEGATE BIT
/**
 * bit used to negate any of the above mentioned constraints
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_NOT', 256);

// VALIDATION MASK
/**
 * used to validate constraint types
 */ 
define('SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ALL', 511);

/**
 * NULL string constant
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_NULL', 'NULL');

// }}}

// {{{ SRA_QueryBuilderConstraint
/**
 * 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_QueryBuilderConstraint {
  // {{{ Attributes
  // public attributes
  /**
	 * the name of the attribute this constraint applies to
	 * @type string
	 */
	var $attribute;
	
  /**
	 * the constraint type. will be a bitmask containing one or more the of the 
	 * SRA_QUERY_BUILDER_CONSTRAINT_TYPE_* constraint bits set
	 * @type int
	 */
	var $type;
	
  /**
	 * the constraint value. NULL values can be specified using the string "NULL"
	 * (case sensitive)
	 * @type mixed
	 */
	var $value;
	
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_QueryBuilderConstraint
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_QueryBuilderConstraint($attribute, $type, $value) {
		$this->attribute = $attribute;
		$this->type = $type;
		if (!is_array($value)) {
			$value = array($value);
		}
		$keys = array_keys($value);
		foreach ($keys as $key) {
      if (is_object($value[$key]) && method_exists($value[$key], "getPrimaryKey")) {
        $value[$key] = $value[$key]->getPrimaryKey();
      }
			if ($value[$key] != SRA_QUERY_BUILDER_CONSTRAINT_NULL) {
				if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_IN_STR) {
					$value[$key] = "%$value[$key]%";
				}
				else {
					if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH) {
						$value[$key] = "$value[$key]%";
					}
					if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH) {
						$value[$key] = "%$value[$key]";
					}
				}
			}
		}
		$this->value = $value;
	}
	// }}}
	
  
  // public operations
	// {{{ isSortingConstraint
	/**
	 * returns TRUE if this is a sorting constraint
   * @access  public
	 * @return boolean
	 */
	function isSortingConstraint() {
		return $this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_ASC || $this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC;
	}
	// }}}
	
	// {{{ getKeys
	/**
	 * returns an array of to all of the value ids for this constraint
   * @access  public
	 * @return array
	 */
	function getKeys() {
		return array_keys($this->value);
	}
	// }}}
	
	// {{{ getSqlOperator
	/**
	 * returns the SQL operator to use for this constraint. i.e. '<', '<=', 'like'
	 * @param int $id the id of the value to return the constraint for (if this constraint contains multiple values)
   * @access  public
	 * @return string
	 */
	function getSqlOperator($id = 0) {
		$op = '';
		if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_NOT) {
			$op = ' NOT';
		}
		if ($this->value[$id] == SRA_QUERY_BUILDER_CONSTRAINT_NULL) {
			$op .= ' IS';
		}
		else if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_GREATER) {
			$op .= ' >';
		}
		else if ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_LESS) {
			$op .= ' <';
		}
		if ($this->value[$id] != SRA_QUERY_BUILDER_CONSTRAINT_NULL && $this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL) {
			$op .= ' =';
		}
		// string operators
		if (($op == '' || $op == ' NOT') && ($this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_STARTS_WITH || 
		    $this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ENDS_WITH || 
				$this->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_IN_STR)) {
			$op .= ' LIKE';
		}
		$op = str_replace('NOT =' , '<>', $op);
		$op = str_replace('NOT IS' , 'IS NOT', $op);
		$op = str_replace('> =' , '>=', $op);
		$op = str_replace('< =' , '<=', $op);
		$op .= ' ';
		return $op;
	}
	// }}}
	
	// {{{ getSqlValue
	/**
	 * returns the sql encoded value for this constraint using the $dao provided
	 * @param DAO $dao the DataAccessObject to use to convert the value
	 * @param int $id the id of the value to return the constraint for (if this constraint contains multiple values)
   * @access  public
	 * @return string
	 */
	function getSqlValue(& $dao, $id = 0) {
		if ($this->value[$id] == SRA_QUERY_BUILDER_CONSTRAINT_NULL) {
			return SRA_QUERY_BUILDER_CONSTRAINT_NULL;
		}
		return $dao->convertAttributeValue($this->attribute, $this->value[$id]);
	}
	// }}}
	
	
	// Static methods
  
	// {{{ validateConstraint
	/**
	 * validates a constraint value (using the bitmask values defined above for 
   * constraint types)
	 *
	 * @param  string $constraint the constraint to validate
	 * @access	public
	 * @return	boolean
	 */
	function validateConstraint($constraint) {
		return SRA_Util::validateBit($constraint, SRA_QUERY_BUILDER_CONSTRAINT_TYPE_ALL);
	}
	// }}}
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_QueryBuilderConstraint object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_querybuilderconstraint');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
