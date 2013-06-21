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
require_once('workflow/SRA_WorkflowConstraint.php');
// }}}

// {{{ Constants
/**
 * constant identifying a conjunctive constraint group
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE', 'and');

/**
 * constant identifying a disjunctive constraint group
 * @type string
 */
define('SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE', 'or');
// }}}

// {{{ SRA_WorkflowConstraintGroup
/**
 * used to specify 1 or more constraint or constraint groups that will be 
 * evaluated as a whole based on the "connective" specified for the group 
 * (either conjunction or disjunction). the evaluation includes short-circuiting 
 * for conjunction join types (and)
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowConstraintGroup {
  // {{{ Attributes
  // public attributes
  
  /**
	 * the constraints/constraint groups that are a part of this constraint group
	 * @type mixed
	 */
	var $constraints = array();
  
  /**
	 * whether the connective between constraints of this decision should be 
   * conjunctively or disjunctively joined
	 * @type string
	 */
	var $connective;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WorkflowConstraintGroup
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
	function SRA_WorkflowConstraintGroup($conf, & $workflow, & $owner) {
    
    if (isset($conf['attributes']['connective']) && $conf['attributes']['connective'] != SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE && $conf['attributes']['connective'] != SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE) {
      $err = 'connective "' . $conf['attributes']['connective']. '" is not valid';
    }
    $this->connective = isset($conf['attributes']['connective']) ? $conf['attributes']['connective'] : SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE;
    
    if (!$err && count($conf['constraint'])) {
      $keys = array_keys($conf['constraint']);
      foreach ($keys as $key) {
        if (!SRA_WorkflowConstraint::isValid($this->constraints[] = new SRA_WorkflowConstraint($conf['constraint'][$key], $workflow, $owner))) {
          $err = "constraint ${key} produced error";
          break;
        }
      }
    }
    
    if (!$err && count($conf['constraint-group'])) {
      $keys = array_keys($conf['constraint-group']);
      foreach ($keys as $key) {
        if (!SRA_WorkflowConstraintGroup::isValid($this->constraints[] = new SRA_WorkflowConstraintGroup($conf['constraint-group'][$key], $workflow, $owner))) {
          $err = "constraint-group ${key} produced error";
          break;
        }
      }
    }
    
    if (!$err && !count($this->constraints)) {
      $err = 'no constraints defined';
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_WorkflowConstraintGroup::SRA_WorkflowConstraintGroup: Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
  
	// {{{ evaluate
	/**
	 * returns the results (TRUE or FALSE) of evaluating this constraint group
	 *
   * @param SraWorkflowVO $wf the workflow instance for this constraint group
   * @param array $params the params hash associated with this constraint group
	 * @access	public
	 * @return	boolean
	 */
  function evaluate(& $wf, & $params) {
    $keys = array_keys($this->constraints);
    foreach($keys as $key) {
      $ceval = $this->constraints[$key]->evaluate($wf, $params);
      switch ($this->connective) {
        case SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE:
          if ($ceval) { return TRUE; }
          break;
        default: 
          if (!$ceval) { return FALSE; }
      }
    }
    return $this->connective == SRA_WORKFLOW_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE ? FALSE : TRUE;
  }
  // }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WorkflowConstraintGroup object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflowconstraintgroup');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
