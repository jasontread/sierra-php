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
require_once('SRA_WSConstraint.php');
require_once('SRA_QueryBuilderConstraintGroup.php');
// }}}

// {{{ Constants
/**
 * constant identifying a conjunctive constraint group
 * @type string
 */
define('SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE', 'and');

/**
 * constant identifying a disjunctive constraint group
 * @type string
 */
define('SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE', 'or');
// }}}

// {{{ 
/**
 * used to specify 1 or more ws-constraint sub-elements that will be evaluated 
 * as a whole based on the "_connective" specified for the group
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSConstraintGroup {
  
  /**
	 * whether the connective between constraints of this group should be 
   * conjunctively or disjunctively joined
	 * @type string
	 */
	var $_connective;
  
  /**
	 * nested _constraints
	 * @type SRA_WSConstraint[]
	 */
	var $_constraints;
  
  /**
	 * the SRA_QueryBuilderConstraintGroup joinMethod
	 * @type int
	 */
	var $_joinMethod;
  

	// {{{ 
	/**
	 * parses the group configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged). a constraint group must have at least 1 nested constraint 
   * or 1 nested constraint group
   * @param array $conf the configuration to parse
   * @access  public
	 */
	function SRA_WSConstraintGroup($conf) {
    $id = $conf['attributes']['key'];
    $this->_connective = isset($conf['attributes']['connective']) ? $conf['attributes']['connective'] : SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE;
    $this->_joinMethod = $this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE ? SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_AND : SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_OR;
    
    foreach (array_keys($conf['ws-constraint']) as $key) {
      if (!SRA_WSConstraint::isValid($this->_constraints[] = new SRA_WSConstraint($conf['ws-constraint'][$key], $this->_connective))) {
        $msg = "SRA_WSConstraintGroup: Failed - nested constraint ${key} produced error for constraint group " . $id;
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
    }
    
		if (isset($this->_connective) && $this->_connective != SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE && $this->_connective != SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE) {
      $msg = 'SRA_WSConstraintGroup: Failed - invalid _connective for web service constraint group ' . $id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    if (!count($this->_constraints)) {
      $msg = 'SRA_WSConstraintGroup: Failed - a constraint group must have at least 1 nested constraint for web service constraint group ' . $id;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
	}
	// }}}
	
  
  // public operations
  
	// {{{ applySortingConstraints
	/**
	 * applies any sorting constraints in this group
   * @param object[] $entities the entities to apply the sorting constraints to
	 * @access public
	 * @return object[]
	 */
  function &applySortingConstraints(&$entities) {
    foreach(array_keys($this->_constraints) as $key) {
      if ($this->_constraints[$key]->isSortingConstraint()) {
        $entities =& SRA_Util::sortObjects($entities, $this->_constraints[$key]->_attr, $this->_constraints[$key]->_operator == SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC);
      }
    }
    return $entities;
  }
  // }}}
  
	// {{{ evaluateAllConstraints
	/**
	 * returns TRUE if $entity is valid against the _constraints contained within 
   * this group. if it is not valid for an equality constraint, that constraint 
   * value will be automatically set into $entity
	 *
   * @param object $entity the entity to validate
   * @param boolean $forceAll whether or not to force evaluation of even query 
   * constraints
	 * @access public
	 * @return boolean
	 */
  function evaluateAllConstraints(& $entity, $forceAll=FALSE) {
    $keys = array_keys($this->_constraints);
    $found = FALSE;
    foreach($keys as $key) {
      $found = TRUE;
      if ($this->_constraints[$key]->evaluateEntity($entity, $this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE, $forceAll)) {
        if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE) {
          return TRUE;
        }
      }
      else {
        if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE) {
          return FALSE;
        }
      }
    }
    if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE) {
      foreach($keys as $key) {
        if ($this->_constraints[$key]->evaluateEntity($entity)) {
          return TRUE;
        }
      }
    }
    return $this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE && $found ? FALSE : TRUE;
  }
  // }}}
  
	// {{{ evaluateNonQueryConstraints
	/**
	 * evaluates any non-query _constraints. returns TRUE if the evaluation of 
   * the groups _constraints is also TRUE
	 * @access public
	 * @return boolean
	 */
  function evaluateNonQueryConstraints() {
    $keys = array_keys($this->_constraints);
    $found = FALSE;
    foreach($keys as $key) {
      if ($this->_constraints[$key]->isNonQueryConstraint() && $this->_constraints[$key]->attrType != SRA_WS_VALUE_TYPE_GET_ATTR) {
        $found = TRUE;
        if ($this->_constraints[$key]->evaluate()) {
          if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE) {
            return TRUE;
          }
        }
        else {
          if ($this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE) {
            return FALSE;
          }
        }
      }
    }
    return $this->_connective == SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE && $found ? FALSE : TRUE;
  }
  // }}}
  
	// {{{ toQueryConstraintGroup
	/**
	 * converts this web service constraint group into an equivalent query 
   * constraint group
	 * @access public
	 * @return SRA_QueryBuilderConstraintGroup
	 */
  function &toQueryConstraintGroup() {
    $_constraints = array();
    $keys = array_keys($this->_constraints);
    foreach($keys as $key) {
      if (!$this->_constraints[$key]->isNonQueryConstraint()) {
        $c_constraints =& $this->_constraints[$key]->toQueryConstraints();
        $keys = array_keys($c_constraints);
        foreach($keys as $key) {
          $_constraints[] =& $c_constraints[$key];
        }
      }
    }
    return new SRA_QueryBuilderConstraintGroup($_constraints, $this->_joinMethod);
  }
  // }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a  object.
	 *
	 * @param  Object $object The object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsconstraintgroup');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
