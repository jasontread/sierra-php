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
require_once('workflow/SRA_WorkflowConstraintGroup.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_WorkflowDecision
/**
 * defines a single alternate outbound path for an step. It constains 1 or 
 * more constraints used to determine whether or not the decision should be 
 * invoked. if none of an step's decisions are triggered, then the step 
 * "next" path will be invoked. a decision will have at least 1 constraint
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowDecision extends SRA_WorkflowConstraintGroup {
  // {{{ Attributes
  // public attributes
  
  /**
	 * the proceeding/output step once the enclosing step has been completed 
   * IF this decision is triggered
	 * @type string
	 */
	var $next;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WorkflowDecision
	/**
	 * parses the workflow configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_Workflow $workflow the workflow this decision pertains to
   * @param SRA_WorkflowStep $step the step this decision pertains to
   * @access  public
	 */
	function SRA_WorkflowDecision($conf, & $workflow, & $step) {
    $this->SRA_WorkflowConstraintGroup($conf, $workflow, $step);
    if (!SRA_WorkflowDecision::isValid($this)) {
      return;
    }
    
    if (!isset($conf['attributes']['next']) || !isset($workflow->steps[$conf['attributes']['next']])) {
      $err = $conf['attributes']['next'] . ' step was not specified or is not valid';
    }
    
    $this->next = $conf['attributes']['next'];
    
    if ($err) { $this->err = SRA_Error::logError('SRA_WorkflowDecision::SRA_WorkflowDecision: Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WorkflowDecision object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflowdecision');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
