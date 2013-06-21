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
require_once('workflow/SRA_WorkflowStep.php');
// }}}

// {{{ Constants
/**
 * value for "userKeyType" identifying that the user identifier may be found 
 * within the get headers ($_GET)
 * @type string
 */
define('SRA_WORKFLOW_USER_KEY_TYPE_GET', 'get');

/**
 * value for "userKeyType" identifying that the active user/user identifier may 
 * be found within the global scope ($_GLOBALS)
 * @type string
 */
define('SRA_WORKFLOW_USER_KEY_TYPE_GLOBAL', 'global');

/**
 * value for "userKeyType" identifying that the user identifier may be found 
 * within the post headers ($_POST)
 * @type string
 */
define('SRA_WORKFLOW_USER_KEY_TYPE_POST', 'post');

/**
 * value for "userKeyType" identifying that the active user/user identifier may 
 * be found within the session scope ($_SESSION)
 * @type string
 */
define('SRA_WORKFLOW_USER_KEY_TYPE_SESSION', 'session');
// }}}

// {{{ SRA_Workflow
/**
 * contains all of the steps associated with the workflow as well as a start 
 * step designator. at least 1 step or task must be defined for a workflow to be 
 * valid
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_Workflow {
  // {{{ Attributes
  // public attributes
  
  /**
   * whether or not to cascade deletions to any entities belonging to this 
   * workflow. this does not pertain to non-committed  entities. the default 
   * behavior is to not cascade delete
   * @type boolean
   */
  var $cascadeDelete;
  
  /**
   * an explicit date or relative date expression. explicit dates should be 
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
   * jump will be automatically applied (for example, if the workflow is created 
   * on 12/28). negative increments can be applied by enclosing the increment 
   * value "n" in parenthesis. for example, to specify 1 month minus 1 week from 
   * the current date, dueDate would be: "+0-+1-+(7)". the increment value "n" 
   * may also be an workflow parameter using the format "${param name}
   * @type string
   */
  var $dueDate;
  
  /**
   * the name of this workflow as determined by the workflow "resource"
   * @type string
   */
  var $name;
  
  /**
	 * whether or not email notifications will be used by this workflow. if 
   * specified, both "notify-subject" and "notify-tpl" must also be specified
	 * @type boolean
	 */
	var $notify;
  
  /**
	 * optional comma separated list of blind carbon copy recipients for all 
   * workflow notifications. these will be used for the 1st notification sent 
   * only. only applicable when at least 1 notification is being sent
	 * @type string
	 */
	var $notifyBcc;
  
  /**
	 * optional comma separated list of carbon copy recipients for all workflow 
   * notifications. these will be used for the 1st notification sent only. only 
   * applicable when at least 1 notification is being sent
	 * @type string
	 */
	var $notifyCc;
  
  /**
	 * the notification return email address. if not specified, the web server 
   * process user name will be used (i.e. apache@myserver.com). both a name and 
   * email address may be specified using the format: "[email] <[name]>"
	 * @type string
	 */
	var $notifyFrom;
  
  /**
	 * the localized email notification subject for this workflow
	 * @type string
	 */
	var $notifySubject;
  
  /**
	 * the text email notification template. this template will have access to the 
   * following dynamic workflow variables:
   *  $wfResources: the merged SRA_ResourceBundle object
   *  $wfWorkflow: the SraWorkflow instance
   *  $wfStep: the SraWorkflowStep instance (when applicable)
   *  $wfTask: the SraWorkflowTask instance
   *  $wfSetup: the SRA_Workflow (configuration) instance
   *  $wfStepSetup: the SRA_WorkflowStep (configuration) instance  (when 
   *  applicable)
   *  $wfTaskSetup: the SRA_WorkflowTask (configuration) instance
   *  $wf_{entity-id}: entities currently associated with the workflow
   *  $wfUser: the "user-entity" reference for the notification
   * must be specified if "notify" is true
	 * @type string
	 */
	var $notifyTpl;
  
  /**
	 * same as notifyTpl but specifies an html formatted email message template. 
   * either notifyTpl or notifyTplHtml MUST be specified when notifications 
   * are active for a workflow. if both are specified, the emails sent will be 
   * multipart mime formatted
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
   * a merged SRA_ResourceBundle representing all of the resources files 
   * specified for this workflow
   * @type SRA_ResourceBundle
   */
  var $resources;
  
  /**
   * same as userAttr for workflow roles
   * @type string
   */
  var $roleAttr;
  
  /**
   * same as userAttrDispl for workflow roles
   * @type string
   */
  var $roleAttrDispl;
  
  /**
   * the name of the attribute within roleEntity that specifies the users that 
   * are members of that role. this attribute may be either an array of user 
   * identifiers (based on userAttr) or an array of userEntity references. this 
   * attribute is required when "roleEntity" is specified
   * @type string
   */
  var $roleAttrMembers;
  
  /**
   * same as userEntity for workflow roles. if not specified, roles CANNOT be 
   * used within steps/tasks for this workflow
   * @type string
   */
  var $roleEntity;
  
  /**
   * the id of the starting workflow step. this attribute is required when steps 
   * are defined for the workflow
   * @type string
   */
  var $start;
  
  /**
	 * the steps associated with this workflow indexed by the step id
	 * @type SRA_WorkflowStep[]
	 */
	var $steps = array();
  
  /**
   * the tasks assigned to this workflow index by task id. these are global 
   * tasks that do not pertain to any specific step
   * @type SRA_WorkflowTask[]
   */
  var $tasks = array();
  
  /**
   * the name of the attribute within the user object that should be used within 
   * the workflow as the user identifier. this attribute will also be used to 
   * lookup the user object when userKey returns a scalar value (using the dao 
   * findBySqlConstraints or findByPk methods). if this attribute is not 
   * specified, it will be assumed that the userKey is the userEntity primary 
   * key attribute
   * @type string
   */
  var $userAttr;
  
  /**
   * if an attribute other than userAttr should be used as the display value, 
   * this attribute specifies the name of that attribute within userEntity. for 
   * example, if userAttr is the primary key (a numeric value), and you would 
   * like the user attribute "name" to be displayed instead of the numeric 
   * primary key, this attribute value would be "name"
   * @type string
   */
  var $userAttrDispl;
  
  /**
   * the attribute within userEntity containing the user's email address. the 
   * email address is used for notifications. this attribute is required if 
   * "notify" is true
   * @type string
   */
  var $userAttrEmail;
  
  /**
   * the name of the entity within the application this workflow is a part of, 
   * that represents users. this attribute is required
   * @type string
   */
  var $userEntity;
  
  /**
   * when an instance of this workflow is created, the current active user is 
   * automatically assigned ownership of it. all interractive tasks that are 
   * then created for the workflow and do not specify alternative assignment 
   * (using the "user" and/or "role" attribute) will be automatically assigned 
   * to this workflow owner. this value specifies the name of the value within 
   * the "user-key-type" context that a reference to the active user object (an 
   * instance of userEntity). this attribute is required
   * @type string
   */
  var $userKey;
  
  /**
   * where the active user object is stored. this may be any of the following:
   *  session: within the session scope ($_SESSION)
   *  global:  within the global scope ($_GLOBALS)
   *  get:     within the get headers ($_GET)
   *  post:    within the post headers ($_POST)
   * if the value is scalar it will be assumed to be the user identifier as 
   * specified through userAttr. this attribute is required. the constants 
   * SRA_WORKFLOW_USER_KEY_TYPE_* represent each of the 4 possible options for 
   * this attribute
   * @type string
   */
  var $userKeyType;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Workflow
	/**
	 * parses the workflow configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @access  public
	 */
	function SRA_Workflow($conf) {
    $tpl =& SRA_Controller::getAppTemplate();
        
    if ((!isset($conf['step']) || !count($conf['step'])) && (!isset($conf['task']) || !count($conf['task']))) {
      $err = 'No steps or tasks defined';
    }
    else if (!isset($conf['attributes']['resources'])) {
      $err = 'No resource bundles defined';
    }
    else if (isset($conf['step']) && count($conf['step']) && (!isset($conf['attributes']['start']) || !isset($conf['step'][$conf['attributes']['start']]))) {
      $err = 'Start step is not defined or is not valid';
    }
    
    $bundles = explode(' ', $conf['attributes']['resources']);
    foreach($bundles as $bundle) {
      $rb =& SRA_ResourceBundle::getBundle($bundle);
      if (!SRA_ResourceBundle::isValid($rb)) {
        $err = "SRA_Workflow::SRA_Workflow: Failed - resource bundle ${bundle} is not valid";
        break;
      }
      if ($this->resources) {
        $this->resources =& SRA_ResourceBundle::merge($this->resources, $rb);
      }
      else {
        $this->resources =& $rb;
      }
    }
    
    if (!$err && (!isset($conf['attributes']['resource']) || !($this->name = $this->resources->getString($conf['attributes']['resource'])))) {
      $err = 'resource "' . $conf['attributes']['resource'] . '" is not valid';
    }
    
    $this->cascadeDelete = isset($conf['attributes']['delete-cascade']) && $conf['attributes']['delete-cascade'] == '1' ? TRUE : FALSE;
    $this->dueDate = isset($conf['attributes']['due-date']) ? $conf['attributes']['due-date'] : NULL;
    $this->notify = isset($conf['attributes']['notify']) && $conf['attributes']['notify'] == '1' ? TRUE : FALSE;
    if (!$err && $this->notify && (!isset($conf['attributes']['notify-subject']) || !$this->resources->getString($conf['attributes']['notify-subject']) || (!isset($conf['attributes']['notify-tpl']) && !isset($conf['attributes']['notify-tpl-html'])) || (isset($conf['attributes']['notify-tpl']) && !$tpl->validateTemplate($conf['attributes']['notify-tpl'])) || (isset($conf['attributes']['notify-tpl-html']) && !$tpl->validateTemplate($conf['attributes']['notify-tpl-html'])))) {
      $err = 'Notify set to true but subject or template not specified or not valid';
    }
    
    $this->notifyBcc = isset($conf['attributes']['notify-bcc']) ? $conf['attributes']['notify-bcc'] : NULL;
    $this->notifyCc = isset($conf['attributes']['notify-cc']) ? $conf['attributes']['notify-cc'] : NULL;
    $this->notifyFrom = isset($conf['attributes']['notify-from']) ? $conf['attributes']['notify-from'] : NULL;
    $this->notifySubject = isset($conf['attributes']['notify-subject']) ? $conf['attributes']['notify-subject'] : NULL;
    $this->notifyTpl = isset($conf['attributes']['notify-tpl']) ? $conf['attributes']['notify-tpl'] : NULL;
    $this->notifyTplHtml = isset($conf['attributes']['notify-tpl-html']) ? $conf['attributes']['notify-tpl-html'] : NULL;
    if (!$err && !$this->notify && ($this->notifySubject || $this->notifyTpl || $this->notifyTplHtml)) {
      $err = 'notify subject or template specified when notify is enabled for the workflow';
    }
    $this->start = $conf['attributes']['start'];
    
    if (!$err && count($conf['param'])) {
      $keys = array_keys($conf['param']);
      foreach ($keys as $key) {
        $this->params[$key] = $conf['param'][$key]['attributes']['value'];
      }
    }
    
    $this->roleAttr = isset($conf['attributes']['role-attr']) ? $conf['attributes']['role-attr'] : NULL;
    $this->roleAttrDispl = isset($conf['attributes']['role-attr-displ']) ? $conf['attributes']['role-attr-displ'] : NULL;
    $this->roleAttrMembers = isset($conf['attributes']['role-attr-members']) ? $conf['attributes']['role-attr-members'] : NULL;
    $this->roleEntity = isset($conf['attributes']['role-entity']) ? $conf['attributes']['role-entity'] : NULL;
    if (!$err && $this->roleEntity && SRA_Error::isError($roleDao =& SRA_DaoFactory::getDao($this->roleEntity))) {
      $err = 'role entity specified "' . $this->roleEntity . '" is not valid';
    }
    else if ($roleDao) {
      if (!$this->roleAttrMembers) { $err = 'role attr members must be specified for role entity ' . $this->roleEntity; }
      if (!$err && $this->roleAttr && !$roleDao->getColumnName($this->roleAttr)) { $err = 'role attr ' . $this->roleAttr . ' is not valid for entity ' . $this->roleEntity; }
      if (!$err && $this->roleAttrDispl && !$roleDao->getColumnName($this->roleAttrDispl)) { $err = 'role attr displ ' . $this->roleAttrDispl . ' is not valid for entity ' . $this->roleEntity; }
      if (!$err && $this->roleAttrMembers && !$roleDao->getColumnName($this->roleAttrMembers)) { $err = 'role attr members ' . $this->roleAttrMembers . ' is not valid for entity ' . $this->roleEntity; }
    }
    
    $this->userAttr = isset($conf['attributes']['user-attr']) ? $conf['attributes']['user-attr'] : NULL;
    $this->userAttrDispl = isset($conf['attributes']['user-attr-displ']) ? $conf['attributes']['user-attr-displ'] : NULL;
    $this->userAttrEmail = isset($conf['attributes']['user-attr-email']) ? $conf['attributes']['user-attr-email'] : NULL;
    $this->userEntity = isset($conf['attributes']['user-entity']) ? $conf['attributes']['user-entity'] : NULL;
    $this->userKey = isset($conf['attributes']['user-key']) ? $conf['attributes']['user-key'] : NULL;
    $this->userKeyType = isset($conf['attributes']['user-key-type']) ? $conf['attributes']['user-key-type'] : NULL;
    if (!$err && (!$this->userEntity || !$this->userKey || !$this->userKeyType)) {
      $err = 'user entity, user key, and user key type must ALL be specified for a workflow';
    }
    else if (!$err && $this->notify && !$this->userAttrEmail) {
      $err = 'user attr email MUST be specified when notifications are enabled for a workflow';
    }
    else if ($this->userKeyType != SRA_WORKFLOW_USER_KEY_TYPE_GET && $this->userKeyType != SRA_WORKFLOW_USER_KEY_TYPE_GLOBAL && $this->userKeyType != SRA_WORKFLOW_USER_KEY_TYPE_SESSION && $this->userKeyType != SRA_WORKFLOW_USER_KEY_TYPE_POST) {
      $err = 'user key type: ' . $this->userKeyType . ' is not valid';
    }
    else if (!$err && SRA_Error::isError($userDao =& SRA_DaoFactory::getDao($this->userEntity))) {
      $err = 'user entity specified "' . $this->userEntity . '" is not valid';
    }
    else if ($userDao) {
      if (!$err && $this->userAttr && !$userDao->getColumnName($this->userAttr)) { $err = 'user attr ' . $this->userAttr . ' is not valid for entity ' . $this->userEntity; }
      if (!$err && $this->userAttrDispl && !$userDao->getColumnName($this->userAttrDispl)) { $err = 'user attr ' . $this->userAttrDispl . ' is not valid for entity ' . $this->userEntity; }
      if (!$err && $this->userAttrEmail && !$userDao->getColumnName($this->userAttrEmail)) { $err = 'user attr ' . $this->userAttrEmail . ' is not valid for entity ' . $this->userEntity; }
    }
    
    if (!$err && isset($conf['step']) && count($conf['step'])) {
      $keys = array_keys($conf['step']);
      // set up step keys for validation purposes
      foreach ($keys as $key) {
        $this->steps[$key] = TRUE;
      }
      foreach ($keys as $key) {
        if (!SRA_WorkflowStep::isValid($this->steps[$key] = new SRA_WorkflowStep($conf['step'][$key], $this))) {
          $err = "step ${key} produced error";
          break;
        }
      }
    }
    
    if (!$err && isset($conf['task']) && count($conf['task'])) {
      $nl = NULL;
      $keys = array_keys($conf['task']);
      foreach ($keys as $key) {
        if (!SRA_WorkflowTask::isValid($this->tasks[$key] = new SRA_WorkflowTask($conf['task'][$key], $this, $nl))) {
          $err = "task ${key} produced error";
          break;
        }
      }
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_Workflow::SRA_Workflow: Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_Workflow object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_workflow');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
