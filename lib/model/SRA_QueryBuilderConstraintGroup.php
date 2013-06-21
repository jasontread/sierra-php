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
/**
 * join multiple constraints using AND
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_AND', 1);

/**
 * join multiple constraints using OR. this is the default joint method
 */
define('SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_OR', 2);
// }}}

// {{{ SRA_QueryBuilderConstraintGroup
/**
 * 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_QueryBuilderConstraintGroup {
  // {{{ Attributes
  // public attributes
  /**
	 * all of the constraints in this group
	 * @type SRA_QueryBuilderConstraint[]
	 */
	var $constraints;
	
  /**
	 * the method for joining multiple constraints. will correspond with one of 
	 * the SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_* constants
	 * @type int
	 */
	var $joinMethod;
	
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_QueryBuilderConstraintGroup
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_QueryBuilderConstraintGroup($constraints, $joinMethod = SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_OR) {
		$this->constraints =& $constraints;
		$this->joinMethod = $joinMethod;
	}
	// }}}
	
  
  // public operations
	
	// {{{ hasNonSortingConstraint()
	/**
	 * returns TRUE if this group contains any non-sorting constraints
	 *
	 * @access	public
	 * @return	boolean
	 */
	function hasNonSortingConstraint() {
		$keys = array_keys($this->constraints);
		foreach ($keys as $key) {
			if (!$this->constraints[$key]->isSortingConstraint()) {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}

	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_QueryBuilderConstraintGroup object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_querybuilderconstraintgroup');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
