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
require_once('workflow/SRA_WorkflowDecision.php');
require_once('workflow/SRA_WorkflowTask.php');
// }}}

// {{{ Constants
/**
 * identifies that the step due date should be relative to the current date
 * @type int
 */
define('SRA_WORKFLOW_STEP_DUE_DATE_REL_CUR', 0);

/**
 * identifies that the step due date should be relative to the wf start date
 * @type int
 */
define('SRA_WORKFLOW_STEP_DUE_DATE_REL_WF_START', 1);

/**
 * identifies that the step due date should be relative to the wf due date
 * @type int
 */
define('SRA_WORKFLOW_STEP_DUE_DATE_REL_WF_DUE_DATE', 2);
// }}}

// {{{ SRA_WorkflowStep
/**
 * defines a phase of work within the workflow. if a step is not a "finish" 
 * step, then it will also provide 1 or more outbound step references defining 
 * what should occur after this step is complete. workflow steps may be used to 
 * accomplish tasks (1 or more nested "tasks") or to implement conditional 
 * logic (using nested decisions) or both
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowStep {
  // {{{ Attributes
  // public attributes
  
  /**
	 * the unique identifier for this step
	 * @type string
	 */
	var $id;
  
  /**
	 * the identifier of another workflow to connect to when this step is 
   * completed. NOTE: the connect-to workflow will be initialized using the same 
   * params as this step
	 * @type string
	 */
	var $connectTo;
  
  /**
	 * any decisions associated with this step. each decision defines an alternate 
   * output path for the step
	 * @type SRA_WorkflowDecision[]
	 */
	var $decisions = array();
  
  /**
   * an explicit date or relative due-date expression. explicit dates should be 
   * entered in the format "YYYY-MM-DD HH:MM:SS" (time is optional). relative 
   * expressions utilize the same format except that any of the date values 
   * (YYYY, MM, DD, HH, MM, or SS) may be replaced with a relative modifier in 
   * the format "+n" where n is the increase from the current timestamp. For 
   * example, to specify the 1st of the following month, the dueDate would be: 
   * "+0-+1-01" - where +0 signifies the current year, and +1 signifies the 
   * following month. if the current month was december (12), the following 
   * month will be January and the year will be incremented automatically. 
   * Another example: to specify exactly one week from the current time, 
   * dueDate would be "+0-+0-+7" - where the first +0 signifies the current 
   * year, the second +0 signifies the current month, and +7 signifies 7 days 
   * from the current date. month and year rollovers resulting in the 1 week 
   * jump will be automatically applied (for example, if the action was created 
   * on 12/28). negative increments can be applied by enclosing the increment 
   * value "n" in parenthesis. for example, to specify 1 month minus 1 week from 
   * the current date, dueDate would be: "+0-+1-+(7)". the increment value "n" 
   * may also be an action or workflow parameter using the format "${param name}
   * @type string
   */
  var $dueDate;
  
  /**
   * if the step dueDate is relative, this attribute determines what it is 
   * relative to. the following options are available:
   *  0: the date/time when the task was invoked (default)
   *  1: the workflow start date
   *  2: the workflow due date (should not be greater)
   * the SRA_WORKFLOW_TASK_DUE_DATE_REL_* constants are defined for these 
   * options
   * @type int
   */
  var $dueDateRel;
  
  /**
   * when entityId is specified, and no entity has yet been instantiated and 
   * assigned to it, this attribute MUST be specified designating the entity 
   * type. this will be the name of the entity within one of the application's 
   * entity models. un-committed entities (when entity-pk is not specified) are 
   * stored in a workflow table and should not exceed 10mbps in size
   * @type string
   */
  var $entity;
  
  /**
   * if this step will add a new or use an existing entity within the workflow, 
   * this attribute will be the unique identifier for that entity. if this step 
   * is the first reference to that entityId, it will be automatically added to 
   * the workflow
   * @type string
   */
  var $entityId;
  
  /**
   * either the explicit primary key value or the name of a step/workflow 
   * parameter containing that value. this attribute should be specified when 
   * this step will add an entity instance to the workflow and that entity 
   * should be referenced from the database. if "entity" is specified, and this 
   * attribute is not, a new instance of that "entity" will be instantiated and 
   * added to the workflow. parameter-based primary key values should be entered 
   * in the format "${param name}"
   * @type string
   */
  var $entityPk;
  
  /**
   * whether or not this is a finish step. finish steps are those that cause the 
   * workflow to complete and terminate. finish steps should not specify a 
   * "next" attribute or any nested "decision" elements
   * @type boolean
   */
  var $finish;
  
  /**
   * the name of this step as determined by the config "resource"
   * @type string
   */
  var $name;
  
  /**
   * the default proceeding/output step once this one has been completed. the 
   * workflow will transition to that step unless a match is found in any of the 
   * nested "decision" elements for this step
   * @type string
   */
  var $next;
  
  /**
	 * should notifications be sent for this step and its corresponding tasks? 
   * this value overrides the enclosing workflow 'notify' flag
	 * @type boolean
	 */
	var $notify;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifyBcc;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifyCc;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifyFrom;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifySubject;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifyTpl;
  
  /**
	 * overrides the default workflow value for tasks in this step
	 * @type string
	 */
	var $notifyTplHtml;
  
  /**
   * used to define implementation/front-end specified values. they are not used 
   * within the lib/workflow system itself. they may be accessed within the 
   * SRA_Workflow/SRA_WorkflowStep::params associative array attribute
   * @type array
   */
  var $params = array();
  
  /**
   * the label resource for this task
   * @type string
   */
  var $resource;
  
  /**
   * if this step is the responsibility of a user other than the owner of the 
   * workflow instance, this attribute may specify a "role" or group of users 
   * that it should be assigned to. all users within that role will have equal 
   * permission to complete and add new tasks to this step. this attribute may 
   * also reference a step/workflow parameter using the format ${param name}
   * @type string
   */
  var $role;
  
  /**
   * the tasks assigned to this workflow step
   * @type SRA_WorkflowTask[]
   */
  var $tasks = array();
  
  /**
   * if this task is the responsibility of a user other than the owner of the 
   * workflow instance, this attribute may specify the identifier of that user. 
   * this user have permission to complete and add new tasks to this step. this 
   * attribute may also reference a workflow parameter using the format 
   * ${param name}
   * @type mixed
   */
  var $user;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WorkflowStep
	/**
	 * parses the step configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_Workflow $workflow the workflow this step pertains to
   * @access  public
	 */
	function SRA_WorkflowStep($conf, & $workflow) {
    if (!isset($conf['attributes']['key'])) {
      $err = 'id is not specified';
    }
    $this->id = $conf['attributes']['key'];
    
    $this->connectTo = isset($conf['attributes']['connect-to']) ? $conf['attributes']['connect-to'] : NULL;
    if ($this->connectTo && !SRA_Workflow::isValid(SRA_WorkflowManager::getWorkflowSetup($this->connectTo))) {
      $err =  'connect-to "' . $this->connectTo . '" is not valid';
    }
    $this->dueDate = isset($conf['attributes']['due-date']) ? $conf['attributes']['due-date'] : NULL;
    $this->dueDateRel = isset($conf['attributes']['due-date-rel']) ? $conf['attributes']['due-date-rel'] : SRA_WORKFLOW_STEP_DUE_DATE_REL_CUR;
    
    if (isset($conf['attributes']['entity']) && SRA_Error::isError(SRA_DaoFactory::getDao($conf['attributes']['entity']))) {
      $err = 'entity ' . $conf['attributes']['entity'] . ' is not valid';
    }
    $this->entity = isset($conf['attributes']['entity']) ? $conf['attributes']['entity'] : NULL;
    
    if (!$err && $this->entity && !isset($conf['attributes']['entity-id'])) {
      $err = 'no entity id specified for entity ' . $this->entity;
    }
    $this->entityId = isset($conf['attributes']['entity-id']) ? $conf['attributes']['entity-id'] : NULL;
    $this->entityPk = isset($conf['attributes']['entity-pk']) ? $conf['attributes']['entity-pk'] : NULL;
    
    $this->finish = isset($conf['attributes']['finish']) && $conf['attributes']['finish'] == '1' ? TRUE : FALSE;
    if (!$err && $this->finish && (count($this->decisions) || isset($conf['attributes']['next']))) {
      $err = 'finish steps cannot have a next reference or any nested decisions';
    }
    
    $this->next = isset($conf['attributes']['next']) ? $conf['attributes']['next'] : NULL;
    if (!$err && !$this->next && !$this->finish) {
      $err = 'non-finish steps must specify a "next" step reference';
    }
    else if (!$err && $this->next && !isset($workflow->steps[$this->next])) {
      $err = $this->next . ' is not a valid step reference for this workflow';
    }
    
    $this->notify = isset($conf['attributes']['notify']) ? ($conf['attributes']['notify'] == '1' ? TRUE : FALSE) : $workflow->notify;
    $this->notifyBcc = isset($conf['attributes']['notify-bcc']) ? $conf['attributes']['notify-bcc'] : NULL;
    $this->notifyCc = isset($conf['attributes']['notify-cc']) ? $conf['attributes']['notify-cc'] : NULL;
    $this->notifyFrom = isset($conf['attributes']['notify-from']) ? $conf['attributes']['notify-from'] : $workflow->notifyFrom;
    $this->notifySubject = isset($conf['attributes']['notify-subject']) ? $conf['attributes']['notify-subject'] : $workflow->notifySubject;
    if (!$err && $this->notifySubject && !($this->notifySubject = $workflow->resources->getString($this->notifySubject))) {
      $err = 'notify subject "' . $this->notifySubject . '" is not valid';
    }
    $tpl =& SRA_Controller::getAppTemplate();
    $this->notifyTpl = isset($conf['attributes']['notify-tpl']) ? $conf['attributes']['notify-tpl'] : NULL;
    if (!$err && $this->notifyTpl && !$tpl->validateTemplate($this->notifyTpl)) {
      $err = 'notify template "' . $this->notifyTpl . '" is not valid';
    }
    $this->notifyTplHtml = isset($conf['attributes']['notify-tpl-html']) ? $conf['attributes']['notify-tpl-html'] : NULL;
    if (!$err && $this->notifyTplHtml && !$tpl->validateTemplate($this->notifyTplHtml)) {
      $err = 'notify html template "' . $this->notifyTplHtml . '" is not valid';
    }
    
    if (!$err && (!isset($conf['attributes']['resource']) || !($this->name = $workflow->resources->getString($conf['attributes']['resource'])))) {
      $err = 'resource "' . $conf['attributes']['resource'] . '" is not valid';
    }
    else {
      $this->resource = $conf['attributes']['resource'];
    }
    
    if (!$err && count($conf['param'])) {
      $keys = array_keys($conf['param']);
      foreach ($keys as $key) {
        $this->params[$key] = $conf['param'][$key]['attributes']['value'];
      }
    }
    
    $this->role = isset($conf['attributes']['role']) ? $conf['attributes']['role'] : NULL;
    if ($this->role && !$workflow->roleEntity) {
      $err = 'role cannot be specified when there is no role entity assigned in the workflow';
    }
    $this->user = isset($conf['attributes']['user']) ? $conf['attributes']['user'] : NULL;
    
    if (!$err && count($conf['decision'])) {
      $keys = array_keys($conf['decision']);
      foreach ($keys as $key) {
        if (!SRA_WorkflowDecision::isValid($this->decisions[] = new SRA_WorkflowDecision($conf['decision'][$key], $workflow, $this))) {
          $err = "decision ${key} produced error";
          break;
        }
      }
    }
    
    if (!$err && isset($conf['task']) && count($conf['task'])) {
      $keys = array_keys($conf['task']);
      foreach ($keys as $key) {
        if (!SRA_WorkflowTask::isValid($this->tasks[] = new SRA_WorkflowTask($conf['task'][$key], $workflow, $this))) {
          $err = "task ${key} produced error";
          break;
        }
      }
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_WorkflowStep::SRA_WorkflowStep: ' . $this->id . ' Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WorkflowStep object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflowstep');
	}
	// }}}
	
  
  // private operations
  
}
// }}}
?>
