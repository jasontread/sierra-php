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
require_once('model/SRA_AttributeValidator.php');
require_once('workflow/SRA_WorkflowDecision.php');
// }}}

// {{{ Constants
/**
 * identifies that the task due date should be relative to the current date
 * @type int
 */
define('SRA_WORKFLOW_TASK_DUE_DATE_REL_CUR', 0);

/**
 * identifies that the task due date should be relative to the wf start date
 * @type int
 */
define('SRA_WORKFLOW_TASK_DUE_DATE_REL_WF_START', 1);

/**
 * identifies that the task due date should be relative to the wf due date
 * @type int
 */
define('SRA_WORKFLOW_TASK_DUE_DATE_REL_WF_DUE_DATE', 2);

/**
 * identifies that the task due date should be relative to the step due date
 * @type int
 */
define('SRA_WORKFLOW_TASK_DUE_DATE_REL_STEP_DUE_DATE', 3);
// }}}

// {{{ SRA_WorkflowTask
/**
 * defines a unit of work within a workflow. tasks are created within the 
 * context of a workflow "step". tasks can be either interactive or 
 * auto-completed. auto-completed tasks do not require any user intervention in 
 * order to complete while interactive tasks do. interactive tasks are those 
 * tasks containing a "view" and those flagged as "interactive". the unit of 
 * work performed by a task is either the "eval*" PHP code or the "view" or 
 * the user "check off" when it is designated "interactive". therefore, at least 
 * one of those values must exist for each task
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowTask {
  // {{{ Attributes
  // public attributes
  
  /**
	 * if an entity pertains to this task, setting this flag to true will result 
   * in that entity being inserted/updated upon successful completion of the 
   * task (if interactive, otherwise performed when the task is initialized)
	 * @type boolean
	 */
	var $commit;
  
  /**
	 * the identifier of another workflow to connect to when this task is 
   * completed. NOTE: the connect-to workflow will be initialized using the same 
   * params as this task
	 * @type string
	 */
	var $connectTo;
  
  /**
   * constraint group for this task. used to determine whether or not this task 
   * is added to its step at the time that it is created
   * @type SRA_WorkflowConstraintGroup
   */
  var $constraintGroup;
  
  /**
   * the description of this task as determined by the config "resource-descr"
   * @type string
   */
  var $desciption;
  
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
   * if the task dueDate is relative, this attribute determines what it is 
   * relative to. the following options are available:
   *  0: the date/time when the task was invoked (default)
   *  1: the workflow start date
   *  2: the workflow due date (should not be greater)
   *  3: the task step due date (should not be greater)
   * the SRA_WORKFLOW_TASK_DUE_DATE_REL_* constants are defined for these 
   * options
   * @type int
   */
  var $dueDateRel;
  
  /**
   * see SRA_WorkflowStep::entity - when the task pertains to a step, this will 
   * override the step entity. when the task pertains to the workflow, this 
   * attribute is required if the task involves an entity
   * @type string
   */
  var $entity;
  
  /**
   * see SRA_WorkflowStep::entityId
   * @type string
   */
  var $entityId;
  
  /**
   * see SRA_WorkflowStep::entityPk
   * @type string
   */
  var $entityPk;
  
  /**
   * a PHP code block that should be invoked for a particular task. this code 
   * segment will have access to any current workflow entities using the 
   * entityId as the variable name as well as to the SraWorkflow, 
   * SraWorkflowStep and SraWorkflowTask instances using the variable names 
   * $workflow, $step (when applicable) and $task. This code block MUST return 
   * TRUE, or the workflow will enter an error state. this code is invoked AFTER 
   * the task is completed successfully (if a "view" is defined for the task, 
   * this would be when that view is completed/confirmed)
   * @type string
   */
  var $evalAfter;
  
  /**
   * Like "evalAfter", "evalBefore" also specifies a PHP code block that should 
   * be invoked for a particular task. however, this code is invoked at the same 
   * time that the task is created. the difference between "evalBefore" and 
   * "evalAfter" is only applicable to interractive tasks (tasks with associated 
   * views)
   * @type string
   */
  var $evalBefore;
  
  /**
   * is this task interactive? use this to create a "check-off" task... a task 
   * that a user must simply check of having completed. tasks with a "view" are 
   * interactive but in a different way because the view must be displayed and 
   * entity optionally validated (when "validate" is specified) in order for 
   * them to be considered completed. only the owner of this task, or the owner 
   * of the enclosing step, or the owner of the enclosing workflow may 
   * "check off" a task. this attribute will be automatically set to TRUE if a 
   * "view" is assigned to this task
   * @type boolean
   */
  var $interactive;
  
  /**
   * the name of this task as determined by the config "resource"
   * @type string
   */
  var $name;
  
  /**
	 * should notifications be sent for this task? this value overrides the 
   * enclosing workflow or step 'notify' flag
	 * @type boolean
	 */
	var $notify;
  
  /**
   * whether or not all participants in this workflow (past and present) should 
   * be notified (using the "notify-tpl") when this task is created. the default 
   * behavior is to only notify the new owner(s) of the task
   * @type boolean
   */
  var $notifyAll;
  
  /**
   * whether or not notifications should be sent when this task is created 
   * regardless of whether or not it is interactive. the default value for this 
   * attribute is FALSE, meaning notifications will only be sent for the task if 
   * it is interactive
   * @type boolean
   */
  var $notifyAlways;
  
  /**
   * if "notifyAttachView" is specified, this attribute must also be provided 
   * specifying what to name the attached file. this value may contain 
   * workflow/step parameters in the format ${param name}. it may also contain 
   * entity attributes in the format {$attribute name} (after being parsed for 
   * parameter names, it will be passed through the VO parseString method of 
   * the enclosing step entity)
   * @type string
   */
  var $notifyAttachName;
  
  /**
   * an entity view that should be attached to the email notification sent when 
   * this task is invoked. this view may be either text, pdf or some other 
   * output format. it will be output directly to a file, renamed to 
   * "notifyAttachName" and attached to the email notification(s)
   * @type string
   */
  var $notifyAttachView;
  
  /**
	 * overrides the default step/workflow value for this attribute
	 * @type string
	 */
	var $notifyBcc;
  
  /**
	 * overrides the default step/workflow value for this attribute
	 * @type string
	 */
	var $notifyCc;
  
  /**
	 * overrides the default step/workflow value for this attribute
	 * @type string
	 */
	var $notifyFrom;
  
  /**
	 * additional email addresses that should be notified when this task is 
   * created. these will be notified regardless of whether or not other 
   * notifications are being sent. the $wfUser template variable will NOT be 
   * present for these recipients. if the task is not interactive, 
   * "notifyAlways" MUST be true in order for notifications to be sent to these 
   * recipients
	 * @type array
	 */
	var $notifyRecipients;
  
  /**
	 * whether or not ONLY the "notifyRecipients" should be sent notifications for 
   * this task
	 * @type boolean
	 */
	var $notifyRecipientsOnly;

  /**
   * overrides the default step/workflow value for this attribute
   * @type string
   */
  var $notifySubject;
  
  /**
   * overrides the default step/workflow value for this attribute
   * @type string
   */
  var $notifyTpl;
  
  /**
   * overrides the default step/workflow value for this attribute
   * @type string
   */
  var $notifyTplHtml;
  
  /**
   * the label resource for this task
   * @type string
   */
  var $resource;
  
  /**
   * the resources used to provide a description for this task
   * @type string
   */
  var $resourceDesc;
  
  /**
   * if this task is the responsibility of a user other than the owner of the 
   * enclosing step, this attribute may specify a "role" or group of users that 
   * it should be assigned to. all users within that role will be notified, be 
   * assigned responsibility for and have equal access to complete the task. 
   * this attribute may also reference a workflow parameter using the format 
   * ${param name}
   * @type string
   */
  var $role;
  
  /**
   * if this task is the responsibility of a user other than the owner of the 
   * enclosing step, this attribute may specify the identifier of that user. 
   * this user will be notified and given responsibility to complete this task 
   * when it is created. this attribute may also reference a workflow parameter 
   * using the format ${param name}
   * @type mixed
   */
  var $user;
  
  /**
   * if this task includes a "view" requiring user input, this attribute may be 
   * specified identifying a validation constraint that should be applied to the 
   * entity after the "view" has been processed. if this validation passes 
   * successfully, the task will be considered completed, otherwise, the task 
   * will loop back on itself with the validation errors assigned to it. if this 
   * attribute is set to "1", then the entity validation will be invoked 
   * (without a specific validation identifier)
   * @type string
   */
  var $validate;
  
  /**
   * a space separated list of names of attributes that should be excluded from 
   * the entity validation. if a validation error is returned for one of these 
   * attributes, validation failure will not be triggered
   * @type array
   */
  var $validateIgnore;
  
  /**
   * if the enclosing step has an entity assigned (based on the "entityId" 
   * attribute) this attribute may be specified identifying the entity view that 
   * this task should display to the user. if this view requires input from the 
   * user, a "validate" identifier may also be specified determining the 
   * validation on that input that should occur prior to the task being 
   * completed. tasks with a "view" assigned are always interactive. multiple 
   * views can be specified each separated by a space (each view will be 
   * rendered an concatenated starting with the first)
   * @type string
   */
  var $view;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_WorkflowTask
	/**
	 * parses the workflow configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_Workflow $workflow the workflow this task pertains to
   * @param SRA_WorkflowStep $step the step that this task pertains to (if 
   * applicable)
   * @access  public
	 */
	function SRA_WorkflowTask($conf, & $workflow, & $step) {
    if ($step) {
      $owner =& $step;
    }
    else {
      $owner =& $workflow;
    }
    
    $this->commit = isset($conf['attributes']['commit']) && $conf['attributes']['commit'] == '1';
    $this->connectTo = isset($conf['attributes']['connect-to']) ? $conf['attributes']['connect-to'] : NULL;
    if ($this->connectTo && !SRA_Workflow::isValid(SRA_WorkflowManager::getWorkflowSetup($this->connectTo))) {
      $err =  'connect-to "' . $this->connectTo . '" is not valid';
    }
    if (!$err && isset($conf['attributes']['resource-descr']) && !($this->description = $workflow->resources->getString($conf['attributes']['resource-descr']))) {
      $err = 'resource-descr "' . $conf['attributes']['resource-descr'] . '" is not valid';
    }
    else if (isset($conf['attributes']['resource-descr'])) {
      $this->resourceDescr = $conf['attributes']['resource-descr'];
    }
    $this->dueDate = isset($conf['attributes']['due-date']) ? $conf['attributes']['due-date'] : NULL;
    $this->dueDateRel = isset($conf['attributes']['due-date-rel']) ? $conf['attributes']['due-date-rel'] : SRA_WORKFLOW_TASK_DUE_DATE_REL_CUR;
    $this->entity = isset($conf['attributes']['entity']) ? $conf['attributes']['entity'] : ($step ? $step->entity : NULL);
    $this->entityId = isset($conf['attributes']['entity-id']) ? $conf['attributes']['entity-id'] : ($step ? $step->entityId : NULL);
    $this->entityPk = isset($conf['attributes']['entity-pk']) ? $conf['attributes']['entity-pk'] : ($step ? $step->entityPk : NULL);
    if (!$err && $this->entity && !$this->entityId) {
      $err = 'no entity id specified for entity ' . $this->entity;
    }
    $this->evalAfter = isset($conf['eval-after'][0]) ? $conf['eval-after'][0]['xml_value'] : (isset($conf['eval-after'][1]) ? $conf['eval-after'][1]['xml_value'] : NULL);
    $this->evalBefore = isset($conf['eval-before'][0]) ? $conf['eval-before'][0]['xml_value'] : (isset($conf['eval-before'][1]) ? $conf['eval-before'][1]['xml_value'] : (isset($conf['eval-before'][2]) ? $conf['eval-before'][2]['xml_value'] : NULL));
    $this->interactive = isset($conf['attributes']['view']) || (isset($conf['attributes']['interactive']) && $conf['attributes']['interactive'] == '1') ? TRUE : FALSE;
    if (!$err && (!isset($conf['attributes']['resource']) || !($this->name = $workflow->resources->getString($conf['attributes']['resource'])))) {
      $err = 'resource "' . $conf['attributes']['resource'] . '" is not valid';
    }
    else {
      $this->resource = $conf['attributes']['resource'];
    }
    $this->notify = isset($conf['attributes']['notify']) ? ($conf['attributes']['notify'] == '1' ? TRUE : FALSE) : $owner->notify;
    $this->notifyAll = isset($conf['attributes']['notify-all']) && $conf['attributes']['notify-all'] == '1';
    $this->notifyAlways = isset($conf['attributes']['notify-always']) && $conf['attributes']['notify-always'] == '1';
    $this->notifyAttachName = isset($conf['attributes']['notify-att-name']) ? $conf['attributes']['notify-att-name'] : NULL;
    $this->notifyAttachView = isset($conf['attributes']['notify-att-view']) ? $conf['attributes']['notify-att-view'] : NULL;
    if ($this->notifyAttachView && !$this->entityId) {
      $err = 'notify attach view ' . $this->notifyAttachView . ' cannot be specified without a step entity id';
    }
    $this->notifyBcc = isset($conf['attributes']['notify-bcc']) ? $conf['attributes']['notify-bcc'] : NULL;
    $this->notifyCc = isset($conf['attributes']['notify-cc']) ? $conf['attributes']['notify-cc'] : NULL;
    $this->notifyFrom = isset($conf['attributes']['notify-from']) ? $conf['attributes']['notify-from'] : $owner->notifyFrom;
    $this->notifyRecipients = isset($conf['attributes']['notify-recipients']) ? explode(' ', $conf['attributes']['notify-recipients']) : NULL;
    $this->notifyRecipientsOnly = isset($conf['attributes']['notify-recipients-only']) && $conf['attributes']['notify-recipients-only'] == '1';
    if (!$err && $this->notifyRecipients) {
      foreach($this->notifyRecipients as $email) {
        if (!SRA_AttributeValidator::email($email)) {
          $err = 'the notify-recipient "' . $email . '" is not valid';
          break;
        }
      }
    }
    $this->notifySubject = isset($conf['attributes']['notify-subject']) ? $conf['attributes']['notify-subject'] : $owner->notifySubject;
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
    
    $this->role = isset($conf['attributes']['role']) ? $conf['attributes']['role'] : (!isset($conf['attributes']['user']) && $step ? $step->role : NULL);
    if ($this->role && !$workflow->roleEntity) {
      $err = 'role cannot be specified when there is no role entity assigned in the workflow';
    }
    $this->user = isset($conf['attributes']['user']) ? $conf['attributes']['user'] : (!isset($conf['attributes']['role']) && $step ? $step->user : NULL);
    $this->validate = isset($conf['attributes']['validate']) ? explode(' ', $conf['attributes']['validate']) : NULL;
    $this->validateIgnore = isset($conf['attributes']['validate-ignore']) ? explode(' ', $conf['attributes']['validate-ignore']) : NULL;
    $this->view = isset($conf['attributes']['view']) ? $conf['attributes']['view'] : NULL;
    
    if ($this->view && !$this->entityId) {
      $err = 'view ' . $this->view . ' cannot be specified without an entityId';
    }
    
    if (!$err && isset($conf['constraint-group'][0]) && !SRA_WorkflowConstraintGroup::isValid($this->constraintGroup = new SRA_WorkflowConstraintGroup($conf['constraint-group'][0], $workflow, $this))) {
      $err = "constraint group produced error";
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_WorkflowTask::SRA_WorkflowTask: ' . $this->id . ' Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_WorkflowTask object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflowtask');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
