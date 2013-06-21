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
// EQUALITY CONSTRAINTS
/**
 * operator bit used for attr and value must be equal
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_EQ', 1);

/**
 * operator bit used for attr is greater than value
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_GT', 2);

/**
 * operator bit used for attr is less than value
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_LT', 4);

// STRING CONSTRAINTS (only 1 constraint allowed if used)
/**
 * operator bit used for attr starts with value
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_STARTS_WITH', 8);

/**
 * operator bit used for attr ends with value
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_ENDS_WITH', 16);

/**
 * operator bit used for attr is a sub-string of value (full text search)
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_INSTR', 32);

// BOOLEAN CONSTRAINTS
/**
 * operator bit used for attr evaluates to boolean TRUE (not 0, NULL, FALSE, 
 * empty array, etc.)
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_TRUE', 64);

/**
 * operator bit used for attr evaluates to boolean FALSE (0, NULL, FALSE, empty 
 * array, etc.)
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_FALSE', 128);

// MODIFIER BITS
/**
 * operator bit used for negating the results of other operators applied
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_NEGATE', 256);

/**
 * operator bit used for attr and value must be identical (===). use in 
 * conjunction with SRA_WORKFLOW_CONSTRAINT_OP_EQ, 
 * SRA_WORKFLOW_CONSTRAINT_OP_TRUE or SRA_WORKFLOW_CONSTRAINT_OP_FALSE
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_IDENTICAL', 512);

/**
 * bitmask containing all of the SRA_WORKFLOW_CONSTRAINT_OP_* bits set (used for
 * validation)
 * @type int
 */
define('SRA_WORKFLOW_CONSTRAINT_OP_ALL', 1023);


/**
 * attr/value type identifier for the name of an attribute/sub-attribute in the 
 * entity that is referenced in an task
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR', 'attr');

/**
 * attr/value type identifier for the name of a form value (GET or POST)
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_TYPE_FORM', 'form');

/**
 * attr/value type identifier for the name of a global variable
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_TYPE_GLOBAL', 'global');

/**
 * attr/value type identifier for the name of a workflow parameter
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_TYPE_PARAM', 'param');

/**
 * attr/value type identifier for the name of a session variable
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_TYPE_SESSION', 'session');
// }}}

// {{{ SRA_WorkflowConstraint
/**
 * defines a single constraint that will be evaluated within the context of a 
 * decision or constraint group. each constraint returns boolean TRUE or FALSE 
 * the value of which will also be used to determine how the enclosing 
 * decision/constraint-group evaluates
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowConstraint {
  // {{{ Attributes
  // public attributes
  
  /**
	 * the constraint attribute identifier (see attrType for more info). nested 
   * attributes are supported. for example, if the attr being evaluated is an 
   * entity instance constaining an address object with a zipCode attribute, the 
   * identifier for that value would be "address_zipCode"
	 * @type string
	 */
	var $attr;
  
  /**
	 * the attribute to evaluate. this may be any of the following:
   *  attr:    attr is the name of an attribute/sub-attribute in the entity that 
   *           is referenced in the enclosing "step" (or identified by the 
   *           "entity-id" attribute)
   *  form:    attr is the name of a form value (GET or POST)
   *  global:  attr is the name of a global variable
   *  param:   attr is the name of a workflow parameter
   *  session: attr is the name of a session variable
   * if not specified, "attr" will be evaluated as an explicit value
	 * @type string
	 */
	var $attrType;
  
  /**
   * by default, the constraint will be applies against the enclosing step 
   * entity. if it should be evaluated against another entity within the 
   * workflow, this attribute should specify the identifier of that entity
   * @type string
   */
  var $entityId;
  
  /**
   * the comparison operator to apply between the "attr" and "value". this is a 
   * bitmask value containing 1 or more of the following bits set:
   *               
   *   EQUALITY CONSTRAINTS
   *   1:   attr and value must be equal
   *   2:   attr is greater than value
   *   4:   attr is less than value
   *   
   *   STRING CONSTRAINTS (only 1 constraint allowed if used)
   *   8:   attr starts with value
   *   16:  attr ends with value
   *   32:  value is a sub-string of attr (full text search)
   *   
   *   BOOLEAN CONSTRAINTS
   *   64:  attr evaluates to boolean TRUE (not 0, NULL, FALSE, empty array, 
   *        etc.)
   *   128: attr evaluates to boolean FALSE (0, NULL, FALSE, empty array, etc.)
   *   
   *   MODIFIER BITS
   *   256: negate the results of any of the above operator constraints
   *   512: attr and value must be identical (===, <==, >==, etc.)
   *
   * these values are defined using the SRA_WORKFLOW_CONSTRAINT_* constants
   * @type int
   */
  var $operator;
  
  /**
   * the explicit constraint value or value identifier (see valueType for more 
   * info). as with "attr", nested attributes are supported. MUST be specified 
   * unless the "operator" uses one of the boolean constraints
   * @type string
   */
  var $value;
  
  /**
   * the value to evaluate. see "attrType" for more info. if not specified, 
   * "value" will be evaluated as an explicit value
   * @type string
   */
  var $valueType;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WorkflowConstraint
	/**
	 * parses the workflow configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_Workflow $workflow the workflow this constraint pertains to
   * @param mixed $owner the step or task that this constraint pertains to
   * @access  public
	 */
	function SRA_WorkflowConstraint($conf, & $workflow, & $owner) {
    if (!isset($conf['attributes']['attr'])) {
      $err = 'attr is not specified';
    }
    $this->attr = $conf['attributes']['attr'];
    if (!$err && isset($conf['attributes']['attr-type']) && $conf['attributes']['attr-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR && $conf['attributes']['attr-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_FORM && $conf['attributes']['attr-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_GLOBAL && $conf['attributes']['attr-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_PARAM && $conf['attributes']['attr-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_SESSION) {
      $err = 'attr-type "' . $conf['attributes']['attr-type'] . '" is not valid';
    }
    $this->attrType = isset($conf['attributes']['attr-type']) ? $conf['attributes']['attr-type'] : SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR;
    $this->entityId = isset($conf['attributes']['entity-id']) ? $conf['attributes']['entity-id'] : $owner->entityId;
    $this->operator = isset($conf['attributes']['operator']) ? $conf['attributes']['operator'] & SRA_WORKFLOW_CONSTRAINT_OP_ALL : SRA_WORKFLOW_CONSTRAINT_OP_EQ;
    if (!$err && !$this->operator) {
      $err = 'operator ' . $conf['attributes']['operator'] . ' is not valid';
    }
    $this->value = isset($conf['attributes']['value']) ? $conf['attributes']['value'] : NULL;
    
    if (!$err && isset($conf['attributes']['value-type']) && $conf['attributes']['value-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR && $conf['attributes']['value-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_FORM && $conf['attributes']['value-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_GLOBAL && $conf['attributes']['value-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_PARAM && $conf['attributes']['value-type'] != SRA_WORKFLOW_CONSTRAINT_TYPE_SESSION) {
      $err = 'value-type "' . $conf['attributes']['value-type'] . '" is not valid';
    }
    $this->valueType = isset($conf['attributes']['value-type']) ? $conf['attributes']['value-type'] : NULL;
    if (!$err && !$this->entityId && ($this->attrType == SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR || $this->valueType == SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR)) {
      $err = 'attr or value type are "attr" but no entityId specified for this constraint';
    }
    if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_TRUE) { 
      $this->value = TRUE;
      $this->valueType = NULL;
      $this->operator = $this->operator | SRA_WORKFLOW_CONSTRAINT_OP_EQ;
    }
    if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_FALSE) { 
      $this->value = FALSE;
      $this->valueType = NULL;
      $this->operator = $this->operator | SRA_WORKFLOW_CONSTRAINT_OP_EQ;
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_WorkflowConstraintGroup::SRA_WorkflowConstraintGroup: ' . $this->attr . ' Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
  
	// {{{ evaluate
	/**
	 * returns the results (TRUE or FALSE) of evaluating this constraint
	 *
   * @param SraWorkflowVO $wf the workflow instance for this constraint
   * @param array $params the params hash associated with this constraint group
	 * @access	public
	 * @return	boolean
	 */
  function evaluate(& $wf, & $params) {
    if (SraWorkflowVO::isValid($wf)) { $entity =& $wf->getEntityObj($this->entityId); }
    $attr = SRA_WorkflowConstraint:: getValue($this->attr, $this->attrType, $entity, $params);
    $value = $this->value ? ($this->valueType ? SRA_WorkflowConstraint:: getValue($this->value, $this->valueType, $entity, $params) : $this->value) : NULL;
    
    $result = FALSE;
    
    // greater than/equal
    if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_GT && $this->operator & SRA_WORKFLOW_CONSTRAINT_OP_EQ) { $result = $attr >= $value; }
    
    // greater than
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_GT) { $result = $attr > $value; }
    
    // less than/equal
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_LT && $this->operator & SRA_WORKFLOW_CONSTRAINT_OP_EQ) { $result = $attr <= $value; }
    
    // less than
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_LT) { $result = $attr < $value; }
    
    // identical
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_EQ && $this->operator & SRA_WORKFLOW_CONSTRAINT_OP_IDENTICAL) { $result = $attr === $value; }
    
    // equal
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_EQ) { $result = $attr == $value; }
    
    // starts with
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_STARTS_WITH) { $result = SRA_Util::beginsWith($attr, $value); }
    
    // ends with
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_ENDS_WITH) { $result = SRA_Util::endsWith($attr, $value); }
    
    // substring
    else if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_INSTR) { $result = strpos($attr, $value) !== FALSE; }
    
    // negate
    if ($this->operator & SRA_WORKFLOW_CONSTRAINT_OP_NEGATE) { $result = !$result; }
    return $result;
  }
  // }}}
	
	
	// Static methods
  
  // {{{ getValue
  /**
   * returns the value specified
   * @param string $id the id of the value to return
   * @param string $type the type of value (see $attrType)
   * @param object $entity the entity to retrieve the value from (for "attr" 
   * type values only)
   * @param array $params the params hash to retrieve the value from (for 
   * "param" type values only)
   * @return mixed
   */
  function & getValue($id, $type, & $entity, & $params) {
    $val = NULL;
    switch ($type) {
      case SRA_WORKFLOW_CONSTRAINT_TYPE_ATTR:
        $val = is_object($entity) && method_exists($entity, 'getAttribute') ? $entity->getAttribute($id) : NULL;
        break;
      case SRA_WORKFLOW_CONSTRAINT_TYPE_FORM:
        $val = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : NULL);
        break;
      case SRA_WORKFLOW_CONSTRAINT_TYPE_GLOBAL:
        $val = SRA_Util::getGlobal($id);
        break;
      case SRA_WORKFLOW_CONSTRAINT_TYPE_PARAM:
        $val = is_array($params) && isset($params[$id]) ? $params[$id] : NULL;
        break;
      case SRA_WORKFLOW_CONSTRAINT_TYPE_SESSION:
        session_start();
        $val = $_SESSION[$id];
        break;
    }
    return $val;
  }
  // }}}
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WorkflowConstraint object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflowconstraint');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
