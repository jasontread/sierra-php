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
require_once('SRA_LookupProcessor.php');
require_once('SRA_ListLookupProcessorAction.php');
require_once('SRA_ListLookupProcessorData.php');
require_once('SRA_QueryBuilder.php');
// }}}

// {{{ Constants
/**
 * the default form name
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_FORM_NAME', 'LLP');

/**
 * identifies a GET form
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_FORM_GET', 'get');

/**
 * identifies a POST form
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_FORM_POST', 'post');

/**
 * the default form type
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_FORM_TYPE', SRA_LIST_LOOKUP_PROCESSOR_FORM_POST);

/**
 * the default limit if none is specified
 * @type int
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_LIMIT', 10);

/**
 * the default offset if none is specified
 * @type int
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_OFFSET', 0);

/**
 * the default page field name if none is specified
 * @type int
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_PAGE', 'pg');

/**
 * the default sort method if none is specified
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_SORT', 'asc');

/**
 * the default name of the template variable to store the LLP results to
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_TPL_VAR', 'LLP_DATA');

/**
 * identifies dynamic attribute parameters
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DYN_STR', 'dyn');

/**
 * primary key value identifying that a new entity should be rendered
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_NEW_PK', 'new');

/**
 * regular expression used to parse field names for dyn attributes
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DYN_REGEX', '/^(.*)\${attr(|.*)?}(.*)/i');

/**
 * form prefix used to identify a dynamic ascending sorting constraint. this 
 * value will be followed by the attribute name in a multi-sort environment
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX', 'llp_dynamic_sort_asc');

/**
 * form prefix used to identify a dynamic descending sorting constraint. this 
 * value will be followed by the attribute name in a multi-sort environment
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX', 'llp_dynamic_sort_desc');

/**
 * form prefix used to identify the sort order field. this value will be followed 
 * by the attribute name
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_SORT_ORDER_FIELD_PREFIX', 'llp_dynamic_sort_order');

/**
 * prefix of the form field that identifies the name of the field containing the primary keys of the 
 * selected entities for a global action
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_FIELD_NAME_PREFIX', 'llp_global_action_select_');

/**
 * name of the select all form field checkbox
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_ALL_FIELD_NAME', 'llp_global_action_select_all');

/**
 * name of the hidden field to identify the pk id of the entity selected for an action
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME', 'llp_select_action_id');

/**
 * name of the hidden field to identify the action that was invoked
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME', 'llp_select_action');

/**
 * name of the hidden field to identify the action invoked was global
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME', 'llp_select_action_global');

/**
 * field name prefix identifying entity fields that should be updated
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX', 'llp_u');

/**
 * field name prefix identifying fields that should be removed from the $form when submitted
 * @type string
 */
define('SRA_LIST_LOOKUP_PROCESSOR_IGNORE_PREFIX', 'tmp_');
// }}}

// {{{ SRA_ListLookupProcessor
/**
 * A powerful lookup processor that can be utilized to display lists of entities 
 * in any format including the ability to segment the view into multiple pages. 
 * The following lookup "params" are applicable to this SRA_LookupProcessor (see 
 * constants section for default values for these params if applicable):
 * 
 *                        ------ PARAMS ------
 * Name            Type                   Value          Description
 * {csv attrs}     llp-constraint-{mask}  {value*}       used to define attribute value constraints. the {mask} is a bit mask 
 *                                                       defining 1 or more of the SRA_QUERY_BUILDER_CONSTRAINT_TYPE_* bits 
 *                                                       that should apply to the constraint. to determine a bit value for 
 *                                                       multiple constraint types, simply | (bitwise or) the constraints 
 *                                                       which will result in the sum of the constraints. This constraint 
 *                                                       is for DEFAULT values, which can be overriden by the dynamic 
 *                                                       constraints (llp-dconstraint-{mask}). Review the SRA_QueryBuilderConstraint 
 *                                                       API documentation for more details on the different  types of 
 *                                                       constraints
 * limit           llp-constraint         {value}        default limit (max # of results)
 * offset          llp-constraint         {value}        default offset 
 * {csv attrs*}    llp-fconstraint-{mask} {value}        FIXED constraints. cannot be overriden by the default or dynamic. 
 *                                                       should be used to enforce data security in order to avoid allowing 
 *                                                       invalid access to view and/or manipulate entities. for example, 
 *                                                       adding a user contraint as described below: ${user}
 * limit           llp-fconstraint        {value}        fixed limit (max # of results) - overrides both default and dynamic limits
 * {csv attrs|dynn}llp-dconstraint-{mask} {field name*}  DYNAMIC constraints. these will override identical DEFAULT constraints 
 * limit           llp-dconstraint        {field name}   name of the form field specifying the limit 
 *                                                       (max # of results) - overrides default limit
 * page            llp-dconstraint        {field name}   name of the form field specifying the current page # - used 
 *                                                       to determine offset
 * name            llp-form               {form name}    the name that should be assigned to the form enclosing the result data
 * type            llp-form               (get|post)     form type for dynamic variables and actions. default is get
 * actionOrder     llp-form-action        {csv actions}  defines the order of the actions (select + any {actions}s defined) as 
 *                                                       they should be rendered if "renderActions" is invoked. this should 
 *                                                       be a comma separated value like "view1,view2,delete,select". if not 
 *                                                       specified, the order will be the order in which the views are defined, 
 *                                                       followed by the global action selector if applicable
 * globalActionOrder llp-form-action      {csv actions}  if the order to display the global actions should be different (or 
 *                                                       include only a subset of the possible actions), that order/subset 
 *                                                       may be defined using this parameter
 * globalActions   llp-form-action        (1|0)          whether or global actions should be available to users. global actions 
 *                                                       are rendered using a checkbox next to each set of action links that the 
 *                                                       user may select in order to invoke an action for more than one selected 
 *                                                       entity
 * {action}        llp-form-action        {field name}   unique identifier for an action that can be invoked from the list 
 *                                                       view. actions can include such activities as deleting/updating/viewing 
 *                                                       of entities. what occurs when this action is invoked is determined by 
 *                                                       the {action}Type value which is a bitmask containing any of the  
 *                                                       SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_* constant values defined in 
 *                                                       SRA_ListLookupProcessorAction.php
 * {action}Type    llp-form-action        {type bitmask} one or more of the SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_* constant values 
 *                                                       represented as a bitmask identifying what should happen when this action 
 *                                                       is invoked
 * {action}Apost   llp-form-action        {html}         html code to render after the close </a> tag
 * {action}Apre    llp-form-action        {html}         html code to render before the <a... /> tag
 * {action}Class   llp-form-action        {html class}   the html class to use in the <a.../ > tag generated for the view link
 * {action}Link    llp-form-action        {resource}     the resource to use as the text for the view link
 * {action}Btn     llp-form-action        (1|0)          whether or not to render the action link as a button. the default behavior 
 *                                                       is to render it as a link
 * {action}Img     llp-form-action        {img src}      the img src to use for the action link (only 1, {action}Img or 
 *                                                       {action}Link should be specified)
 * {action}ImgClass llp-form-action       {html class}   css class to assign to the {action}Img
 * {action}Alt     llp-form-action        {resource}     the resource to use action the alt/title attribute for {action}Img
 * {action}GlobalAlt llp-form-action      {resource}     {action}Alt for global links: optional - {action}Alt will be used if not specified
 * {action}MsgCmpl llp-form-action        {resource}     the resource to display when an action has been invoked successfully. if the {action} includes a view, this message will only be displayed once the ACTION has been processed successfully. for example, an insert completed, an update performed
 * {action}MsgCnfm llp-form-action        {resource}     the resource to display when the action is initiated (clicked) - single quotes, if used, must be prefixed by backslash
 * {action}GlobalMsgCmpl llp-form-action  {resource}     {action}MsgCmpl for global links: optional - {action}MsgCmpl will be used if not specified
 * {action}GlobalMsgCnfm llp-form-action  {resource}     {action}MsgCnfm for global links: optional - {action}MsgCnfm will be used if not specified
 * {action}Post    llp-form-action        {html}         html code to render before the close </a> tag
 * {action}Pre     llp-form-action        {html}         html code to render after the <a... /> tag
 * {action}View    llp-form-action        {view id}      the id of the view that should be displayed if the {action}Type bitmask 
 *                                                       includes any of the SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW* constants
 * {action}ViewFwd llp                    {view id}      the id of the view that should be displayed if an action has been invoked successfully
 * {action}NonGlobal llp-form-action      (1|0)          default: 0: whether or not this action cannot be applied globally (NOTE: it will still show up in the global actions links unless explicitely specified otherwise)
 * rowCycle        llp                    {cycle id}     optional cycle identifier for the classes below. if not specified, "rowClass" will be the identifier
 * rowClass        llp                    {html class}   comma separated values specifying the classes (in that order) that should be applied to rows in the resulting data table
 * rowClassTag     llp                    {html tr attr} an optional alternate "tr" attribute tag that should be used in place of "class" for "rowClass"
 * colCycle        llp                    {cycle id}     optional cycle identifier for the classes below. if not specified, "colClass" will be the identifier
 * colClass        llp                    {html class}   comma separated values specifying the classes (in that order) that should be applied to cols in the resulting data table
 * colClassTag     llp                    {html tr attr} an optional alternate "td" attribute tag that should be used in place of "class" for "colClass"
 * [attr]ColCycle  llp                    {cycle id}     the following 3 parameters are for attribute specific column formatting: an optional cycle identifier for the classes below. if not specified, "[attr]ColClass" will be the identifier
 * [attr]ColClass  llp                    {html class}   comma separated values specifying the classes (in that order) that should be applied to [attr] cols in the resulting data table
 * [attr]ColClassTag llp                  {html tr attr} an optional alternate "td" attribute tag that should be used in place of "class" for "colClass"
 * actionsPos      llp                    ([N]|L|R)      the position for the actions, either the leftmost (L) or rightmost (R), 
 *                                                       or a numbered column (column #s begin at 0) column
 * actionEncl      llp                    {html elem}    an html element to enclose the actions in
 * actionsColCycle llp                    {cycle id}     if actionsColClass is not specified, "colClass" will be used for the actions column: optional cycle identifier for the actions cell below. if not specified, "actionsColClass" will be the identifier
 * actionsColClass llp                    {html class}   comma separated values specifying the classes (in that order) that should be applied to action cols in the resulting data table
 * actionsColClassTag llp                 {html tr attr} an optional alternate "td" attribute tag that should be used in place of "class" for "actionsColClass"
 * resultsResource llp                    {string}       results resource string. the following strings will be replaced using SRA_ResourceBundle::getString(): 
 *                                                        {$start}      : start position of the results
 *                                                        {$end}        : end position of the results
 *                                                        {$count}      : total # of results
 *                                                        {$page}       : current page number
 *                                                        {$pageCount}  : total # of pages
 *                                                       this string can be displayed in any of the following templates:
 *                                                        sra-list-table-header.tpl
 *                                                        sra-list-table-footer.tpl
 *                                                        sra-list-table-cmds.tpl
 *                                                       see the corresponding documentation in these template for details
 * sortLastSelPrec llp                     (1|0)         whether or not the last sort attribute should be given highest or lowest 
 *                                                       precedence in the sort order sequence. the default value for this parameter 
 *                                                       is FALSE, meaning the last attribute that the user selects to sort on will 
 *                                                       be the last sort column in the sql query that is generated. changing this 
 *                                                       parameter to TRUE (1), will result in that column being the first sort 
 *                                                       column in the sql query
 * 
 * 
 * a SRA_ListLookupProcessorData instance representing the results data will be 
 * set in the template context under the variable name SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_TPL_VAR 
 * at the completion of the lookup method invocation
 * 
 * Comments:
 * JOINS:       multiple separate constraints are joined using AND. OR joins should 
 *              be specified in a single constraint with comma separated values
 * Dyn & Def:   dynamic and default constraints work together as follows: if a matching 
 *              constraint type and attribute list is found between the two, the 
 *              default values applied for those dynamic constraints are those 
 *              specified for the default constraints. if there is no matching 
 *              dynamic constraint for a given default, the default constraint will 
 *              behave identical to a fixed constraint. 
 * fixed:       Fixed constraints always take precedence over dynamic and default constraints
 * *csv attrs:  comma separated list of attribute names (no spaces should be used between values)
 * *value:      use commas to deliminate multiple possible values (OR join will be applied)
 *              user id can be specified using the tag: "${user}" which will be 
 *              dynamically substituted for the actual user id as returned by 
 *              SRA_Authenticator::getUserId()
 * *field name: if the name specified is "dyn[n]" (where n is a sequential value used so that 
 *              no two "dyn" values are the same), the attributes will be dynamically 
 *              determined based on the field name value specified, where the tag 
 *              "${attr}" located in that value will be the name of the attribute 
 *              and multiple attribute names can be specified using repeating 
 *              attribute tags specified with "${attr|{delim text}}", where 
 *              {delim text} should be the text used to separate each attribute 
 *              name. For example: "attrs_${attr|-}" would signify that attributes 
 *              should be determined for the constraint where the field name starts 
 *              with "attrs_" followed by a dash separated list of attribute names:
 *              e.g. the value of <input name="attrs_fname-mname-lname" /> would 
 *              be applied as a constraint against the 3 attributes named "fname" 
 *              "mname" and "lname". If the field is an array (auto-cast to an 
 *              array if the end of the field name includes "[]" - e.g. name="fname[]"), 
 *              or if multiple attributes are specified, the join method used 
 *              with the multiple values/attributes will be OR. Multiple separate 
 *              constraints are joined using AND
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_ListLookupProcessor extends SRA_LookupProcessor {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	/**
	 * the current column index
	 * @type int
	 */
	var $_colIndex = 0;
	
	/**
	 * used to cycle through classes
	 * @type array
	 */
	var $_classCycles = array();
	
	/**
	 * used to store the data associated with the lookup
	 * @type SRA_ListLookupProcessorData
	 */
	var $_llpData;
	
	/**
	 * used to store unique prefixes for new entities
	 * @type int
	 */
	var $_newPrefixes = 1;
  // }}}
  
  // {{{ Operations
  // SRA_ListLookupProcessor
	/**
	 * invoked parent constructor
	 * @param util/SRA_Params $params see SRA_LookupProcessor api 
	 * @param string $entityName see SRA_LookupProcessor api 
   * @access  public
	 */
	function SRA_ListLookupProcessor(& $params, $entityName) {
		parent::SRA_LookupProcessor($params, $entityName);
	}
	// }}}
	
  
  // public operations  
	
	
	
	// {{{ lookup
	/**
	 * Returns all of the entities of type {$entityName} applicable to the current 
	 * list view as configured and determined by the values specified in $params 
   * @access  public static
	 * @return {$entityName}[]
	 */
	function lookup() {
		$formParams =& $this->_params->getTypeSubset('llp-form', TRUE);
		$llpParams =& $this->_params->getTypeSubset('llp', TRUE);
		$actionParams =& $this->_params->getTypeSubset('llp-form-action', TRUE);
		$constraints =& $this->_params->getTypeSubset('llp-constraint', TRUE);
		$dconstraints =& $this->_params->getTypeSubset('llp-dconstraint', TRUE);
		$fconstraints =& $this->_params->getTypeSubset('llp-fconstraint', TRUE);
		$formType = $formParams->getParam('type', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_FORM_TYPE);
		$form =& $_GET;
		if ($formType == SRA_LIST_LOOKUP_PROCESSOR_FORM_POST) {
			$form =& $_POST;
		}
		
		// actions
		$actions = array();
		$aparams = $actionParams->getParams();
		foreach ($aparams as $id => $val) {
			if (isset($aparams[$id . 'Type'])) {
				$actions[$id] = new SRA_ListLookupProcessorAction($id, $aparams["${id}Apost "], 
													     $aparams["${id}Apre "], $aparams["${id}Class "], 
															 $aparams["${id}Link"], $aparams["${id}Btn "] == '1', 
															 $aparams["${id}Img"], $aparams["${id}ImgClass "], 
															 $aparams["${id}Alt"], $aparams["${id}GlobalAlt"], 
															 $aparams["${id}MsgCmpl"], $aparams["${id}MsgCnfm"], 
															 $aparams["${id}GlobalMsgCmpl"], $aparams["${id}GlobalMsgCnfm"], 
															 $aparams["${id}Post"], $aparams["${id}Pre"], 
															 $aparams["${id}Type"], $aparams["${id}View"],
															 $aparams["${id}ViewFwd"], $aparams["${id}NonGlobal"] == '1');
			}
		}
		
		if ($formParams->getParam('globalActions', '0') == '1') {
			$actions['select'] = 1;
		}
		
		if ($formParams->getParam('actionOrder')) {
			$actionOrder = explode(',', $formParams->getParam('actionOrder'));
			$orderedActions = array();
			foreach($actionOrder as $key) {
				$key = trim($key);
				if (isset($actions[$key])) {
					$orderedActions[] =& $actions[$key];
				}
				else {
					$msg = "SRA_LookupProcessor::lookup: SRA_Error - Unknown action in actionOrder ${key}";
					SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
		}
		else {
			$orderedActions =& $actions;
		}
		
		if ($formParams->getParam('globalActionOrder')) {
			$globalActionOrder = explode(',', $formParams->getParam('globalActionOrder'));
			$globalOrderedActions = array();
			foreach($globalActionOrder as $key) {
				$key = trim($key);
				if (isset($actions[$key])) {
					$globalOrderedActions[] =& $actions[$key];
				}
				else {
					$msg = "SRA_LookupProcessor::lookup: SRA_Error - Unknown action in globalActionOrder ${key}";
					SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
		}
		else {
			$globalOrderedActions =& $orderedActions;
		}
		
		// determine limit
		$lfield = $dconstraints->getParam('limit');
		$limit = $fconstraints->getParam('limit');
		if (!$limit && ($lfield && isset($form[$lfield]))) {
			$limit = $form[$lfield];
		}
		if (!$limit) {
			if ($this->_params->getParam('limitMin')) {
				$limit = $this->_params->getParam('limitMin');
			}
			else {
				$limit = $constraints->getParam('limit', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_LIMIT);
			}
		}
		
		// determine offset
		$offset = $constraints->getParam('offset', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_OFFSET);
		$pfield = $dconstraints->getParam('page', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_PAGE);
		if (isset($form[$pfield]) && is_numeric($form[$pfield])) {
			$page = (int) $form[$pfield];
			$offset = (($page - 1) * $limit);
		}
		$constraints =& $this->_params->getTypeSubset('llp-constraint-', TRUE);
		$dconstraints =& $this->_params->getTypeSubset('llp-dconstraint-', TRUE);
		$fconstraints =& $this->_params->getTypeSubset('llp-fconstraint-', TRUE);
		
		// determine lookup constraints
		$lookupConstraints = array();
		
		// fixed lookup constraints
		$keys = array_keys($fconstraints->_params);
		foreach ($keys as $key) {
			$attrs = explode(',', $key);
			sort($attrs);
			$lookupConstraints[implode(',', $attrs)] = $fconstraints->_params[$key];
			$lookupConstraints[implode(',', $attrs)]['val'] = explode(',', $fconstraints->getParam($key));
		}
		
		// default lookup constraints
		$keys = array_keys($constraints->_params);
		foreach ($keys as $key) {
			$attrs = explode(',', $key);
			sort($attrs);
			$lkey = implode(',', $attrs);
			if (!isset($lookupConstraints[$lkey])) {
				$lookupConstraints[$lkey] = $constraints->_params[$key];
				$lookupConstraints[$lkey]['val'] = explode(',', $constraints->getParam($key));
				$lookupConstraints[$lkey]['default'] = TRUE;
			}
		}
    
		// dynamic lookup constraints
		$keys = $dconstraints->getKeys();
		$fkeys = array_keys($form);
		$fieldVals = array();
		foreach ($keys as $key) {
			$param = $dconstraints->getParam($key);
      
			$attrs = explode(',', $param);
			$vals = FALSE;
			if (SRA_Util::beginsWith($key, 'dyn')) {
				$vals = array();
				preg_match(SRA_LIST_LOOKUP_PROCESSOR_DYN_REGEX, $dconstraints->getParam($key), $matches);
				foreach ($fkeys as $fkey) {
					if (strpos($fkey, SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX) === FALSE && 
             ((!$matches[1] || SRA_Util::beginsWith($fkey, $matches[1])) && 
						 (!$matches[3] || SRA_Util::endsWith($fkey, $matches[3])))) {
						$attrName = $fkey;
						if ($matches[1]) {
						 $attrName = substr($attrName, strlen($matches[1]));
						}
						if ($matches[3]) {
							$attrName = substr($attrName, 0, strlen($attrName) - strlen($matches[1]) + 1);
						}	
						if ($matches[2]) {
							$attrs = explode(substr($matches[2], 1), $attrName);
						}
						else {
							$attrs = array($attrName);
						}
						sort($attrs);
						$lkey = implode(',', $attrs);
						if ((!isset($lookupConstraints[$lkey]) || $lookupConstraints[$lkey]['default']) && isset($form[$fkey]) && strlen($form[$fkey])) {
							$lookupConstraints[$lkey] = $dconstraints->_params[$key];
							$lookupConstraints[$lkey]['val'] = $form[$fkey];
						}
						$fieldVals[$fkey] = $lookupConstraints[$lkey]['val'];
					}
				}
			}
			else {
				sort($attrs);
				$lkey = implode(',', $attrs);
				if ((!isset($lookupConstraints[$lkey]) || $lookupConstraints[$lkey]['default']) && $form[$dconstraints->getParam[$key]]) {
					$lookupConstraints[$lkey] = $dconstraints->_params[$key];
					$lookupConstraints[$lkey]['val'] = $form[$dconstraints->getParam[$key]];
				}
				$fieldVals[$dconstraints->getParam[$key]] = $lookupConstraints[$lkey]['val'];
			}
		}
		$queryConstraintGroups = array();
		
		// dynamic sorting constraints
		$keys = array_keys($form);
		$dynAscAttrs = array();
		$dynDescAttrs = array();
		$sortOrder = array();
		foreach ($keys as $key) {
			if ($form[$key] && (SRA_Util::beginsWith($key, SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX) || 
													SRA_Util::beginsWith($key, SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX) || 
													SRA_Util::beginsWith($key, SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_SORT_ORDER_FIELD_PREFIX))) {
				if (SRA_Util::beginsWith($key, SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX)) {
					$dynAscAttrs[] = $form[$key];
				}
				else if (SRA_Util::beginsWith($key, SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX)) {
					$dynDescAttrs[] = $form[$key];
				}
				else {
					if (!in_array($form[$key], $sortOrder)) {
						$sortOrder[str_replace(SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_SORT_ORDER_FIELD_PREFIX, '', $key)] = $form[$key];
					}
				}
			}
		}
		$dynamicSortAttrs = array();
		if (count($dynAscAttrs) || count($dynDescAttrs)) {
			$queryConstraints = array();
			$idx = 0;
			$skeys  = array_keys($sortOrder);
			foreach ($dynAscAttrs as $attr) {
				$idx++;
				if (count($sortOrder)) {
					foreach ($skeys as $skey) {
						if ($sortOrder[$skey] == $attr) {
							$idx = (int) $skey;
						}
					}
				}
				$dynamicSortAttrs[$attr] = SRA_QUERY_BUILDER_SORT_ASC;
				$queryConstraints[$idx] = new SRA_QueryBuilderConstraint($attr, SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_ASC, FALSE);
			}
			foreach ($dynDescAttrs as $attr) {
				$idx++;
				if (count($sortOrder)) {
					foreach ($skeys as $skey) {
						if ($sortOrder[$skey] == $attr) {
							$idx = (int) $skey;
						}
					}
				}
				$dynamicSortAttrs[$attr] = SRA_QUERY_BUILDER_SORT_DESC;
				$queryConstraints[$idx] = new SRA_QueryBuilderConstraint($attr, SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC, FALSE);
			}
			if ($llpParams->getParam('sortLastSelPrec', '0') == '1') {
				ksort($queryConstraints);
			}
			else {
				krsort($queryConstraints);
			}
			$queryConstraintGroups[] = new SRA_QueryBuilderConstraintGroup($queryConstraints);
		}
		
		// create constraint groups
		$keys = array_keys($lookupConstraints);
		foreach ($keys as $key) {
			$queryConstraints = array();
			$attrs = explode(',', $key);
			$pieces = explode('-', $lookupConstraints[$key]['type']);
			$type = (int) $pieces[2];
			foreach ($attrs as $attr) {
				$queryConstraints[] = new SRA_QueryBuilderConstraint($attr, $type, $lookupConstraints[$key]['val']);
			}
			$queryConstraintGroups[] = new SRA_QueryBuilderConstraintGroup($queryConstraints);
		}
		
		// determine if action has occurred
		$actionComplete = FALSE;
		if ($form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME] && isset($actions[$form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME]])) {
			$aidx = $form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME];
			$vpks = array();
			if ($form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME]) {
				$vpks[] = $form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME];
			}
			if ($form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME] == '1' && !$actions[$aidx]->nonGlobal) {
				$vpks = array();
				foreach ($fkeys as $fkey) {
					if ($form[$fkey] && SRA_Util::beginsWith($fkey, SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_FIELD_NAME_PREFIX)) {
						$vpks[] = $form[$fkey];
					}
				}
				$gkeys = array_keys($globalOrderedActions);
				$gActionFound = FALSE;
				foreach ($gkeys as $gkey) {
					if ($globalOrderedActions[$gkey]->id == $aidx) {
						$gActionFound = TRUE;
					}
				}
				$aidx = $gActionFound ? $aidx : FALSE;
			}
			if ($aidx && ($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW) && !count($vpks)) {
				$vpks[] = SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . SRA_LIST_LOOKUP_PROCESSOR_NEW_PK;
			}
			if ($aidx !== FALSE && (count($vpks) || $actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW)) {
				$baseEntities = array();
				$dao =& SRA_DaoFactory::getDao($this->_entityName);
				$queryConstraints = array();
				$pkFound = FALSE;
				foreach ($vpks as $pk) {
					if (!SRA_Util::beginsWith($pk, SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . SRA_LIST_LOOKUP_PROCESSOR_NEW_PK)) {
						$queryConstraints[] = new SRA_QueryBuilderConstraint($dao->getPkName(), SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL, $pk);
						$pkFound = TRUE;
					}
					else if ($orderedActions[$key]->type != SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_DELETE) {
						$baseEntities[$pk] =& $dao->newInstance();
					}
				}
				$queryConstraintGroups[] = new SRA_QueryBuilderConstraintGroup($queryConstraints);
				$builder = new SRA_QueryBuilder($this->_entityName, $queryConstraintGroups, $limit, $offset);
				
				if ($builder->getResultCount() || count($baseEntities)) {
					if ($actions[$aidx]->type & (SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW)) {
						// remove unnecessary fields
						$myForm = $form;
						foreach ($fkeys as $fkey) {
							if (SRA_Util::beginsWith($fkey, SRA_LIST_LOOKUP_PROCESSOR_IGNORE_PREFIX) || SRA_Util::beginsWith($fkey, SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX)) {
								unset($myForm[$fkey]);
							}
						}
						$tpl =& SRA_Controller::getAppTemplate();
						$tpl->assign('formName', $formParams->getParam('name', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_FORM_NAME));
						$tpl->assign('formType', $formType);
						$tpl->assignByRef('form', $myForm);
						$tpl->display('model/llp/sra-list-duplicate-form.tpl');
					}
					$entities = array();
					if ($builder->getResultCount() && $pkFound) {
						$entities =& $builder->getEntities();
					}
					$bkeys = array_keys($baseEntities);
					foreach ($bkeys as $bkey) {
						$entities[$bkey] =& $baseEntities[$bkey];
					}
					$ekeys = array_keys($entities);
					$displayErrMsg = FALSE;
					foreach ($ekeys as $ekey) {
						if ($entities[$ekey]->getPrimaryKey()) {
							$fieldPrefix = SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . $entities[$ekey]->getPrimaryKey();
						}
						else {
							$fieldPrefix = SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . SRA_LIST_LOOKUP_PROCESSOR_NEW_PK . $this->_newPrefixes++;
						}
						
						//  update
						if ($actions[$aidx]->type & (SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_UPDATE + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW)) {
							foreach ($fkeys as $fkey) {
								if (SRA_Util::beginsWith($fkey, $fieldPrefix)) {
									if ($entities[$ekey]->getPrimaryKey() && !($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW)) {
										$form[$fieldPrefix . $dao->getPkName()] = $entities[$ekey]->getPrimaryKey();
									}
									$entities[$ekey] =& $entities[$ekey]->newInstanceFromForm($formType, NULL, $fieldPrefix);
									if (!$entities[$ekey]->validate()) {
										$displayErrMsg = $entities[$ekey]->validateErrors;
									}
									else {
										if (!SRA_Error::isError($dao->update($entities[$ekey]))) {
											$entities[$ekey] =& $dao->findByPk($entities[$ekey]->getPrimaryKey(), TRUE);
											$actionComplete = TRUE;
										}
										else {
											$displayErrMsg = $entities[$ekey]->getSysErrorString();
										}
									}
									break;
								}
							}
						}
						// delete
						if ($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_DELETE) {
							if ($entities[$ekey]->getPrimaryKey()) {
								$dao->delete($entities[$ekey]);
								unset($entities[$ekey]);
								$actionComplete = TRUE;
							}
							continue;
						}
						// view
						if ($actions[$aidx]->type & (SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW)) {
							SRA_EntityView::getGlobalFieldNamePrefix($fieldPrefix);
							$entities[$ekey]->render($actionComplete && $actions[$aidx]->viewFwd ? $actions[$aidx]->viewFwd : $actions[$aidx]->view);
							SRA_EntityView::getGlobalFieldNamePrefix('');
						}
					}
					// display confirmation message
					$displayMsg = FALSE;
					if (!$displayErrMsg && ($cmplMsg = $actions[$aidx]->getMsgComplete($form[SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME] == '1'))) {
						if (!($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_EDIT) || ($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_EDIT && $actionComplete)) {
							$rb =& SRA_Controller::getAppResources();
							$displayMsg = $rb->getString($cmplMsg);
						}
					}
					$this->_displayMsg($displayMsg);
					$this->_displayMsg($displayErrMsg);
					if ($actions[$aidx]->type & SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW) {
						echo '</form>';
						return;
					}
					unset($queryConstraintGroups[count($queryConstraintGroups) - 1]);
				}
				else {
					$msg = 'SRA_LookupProcessor::lookup: SRA_Error - action ' . $orderedActions[$key]->fieldName . " failed because no results produced for ${entityName} or a security violation has occurred for user: " . (class_exists('SRA_Authenticator') ? SRA_Authenticator::getUserId() : 'unknown');
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
		}
		
		// instantiate query builder and perform query
		$builder = new SRA_QueryBuilder($this->_entityName, $queryConstraintGroups, $limit, $offset);
		$tpl =& SRA_Controller::getAppTemplate();
		$currentPage = ($offset/$limit) + 1;
		if (!$offset) {
			$currentPage = 1;
		}
		$this->_llpData = new SRA_ListLookupProcessorData($this->_entityName, $orderedActions, $globalOrderedActions, $currentPage, $fieldVals, $limit, $builder->getResultCount(), $formParams->getParam('name', SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_FORM_NAME), $formType, $lfield, $pfield, $dynamicSortAttrs, $sortOrder, $form[SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_FIELD_NAME], $llpParams->getParam('actionsPos'), $llpParams->getParam('resultsResource'), isset($actions['select']), $this->_params);
		$tpl->assign(SRA_LIST_LOOKUP_PROCESSOR_DEFAULT_TPL_VAR, $this->_llpData);
		return $builder->getEntities();
	}
	// }}}
	
	
	// {{{ renderHeader
	/**
	 * Renders html that should be output before any entities
   * @access  public
	 * @return void
	 */
	function renderHeader() {
		$tpl =& SRA_Controller::getAppTemplate();
		$tpl->display('model/llp/sra-list-table-header.tpl');
	}
	// }}}
	
	// {{{ renderFooter
	/**
	 * Renders html that should be output after all entities
   * @access  public
	 * @return void
	 */
	function renderFooter() {
		$tpl =& SRA_Controller::getAppTemplate();
		$tpl->display('model/llp/sra-list-table-footer.tpl');
	}
	// }}}
	
	// {{{ renderEntityPostfix
	/**
	 * Renders html that should be output after each entity returned by this processor
	 * @param Object $entity a reference to the entity being rendered
   * @access  public
	 * @return String
	 */
	function renderEntityPostfix(& $entity) {
		$actionsPos = $this->_params->getParam('actionsPos');
		if ($actionsPos !== FALSE && $actionsPos !== NULL && $actionsPos != 'L' && ((int) $actionsPos) === $this->_colIndex || $actionsPos === 'R') {
			$this->_renderActions();
		}
		print('</tr>');
	}
	// }}}
	
	// {{{ renderEntityPrefix
	/**
	 * Renders html that should be output before each entity returned by this processor
	 * @param Object $entity a reference to the entity being rendered
   * @access  public
	 * @return String
	 */
	function renderEntityPrefix(& $entity) {
		if ($entity->getPrimaryKey()) {
			$fieldPrefix = SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . $entity->getPrimaryKey();
		}
		else {
			$fieldPrefix = SRA_LIST_LOOKUP_PROCESSOR_UPDATE_FIELD_PREFIX . SRA_LIST_LOOKUP_PROCESSOR_NEW_PK . $this->_newPrefixes++;
		}
		SRA_EntityView::getGlobalFieldNamePrefix($fieldPrefix);
		$params =& $this->_params->getTypeSubset('llp');
		$key = $params->getParam('rowCycle') ? $params->getParam('rowCycle') : $params->getParam('rowClass');
		$classTag = $this->_getClassTag($key, 'row');
		
		print("<tr{$classTag}>");
		$actionsPos = $params->getParam('actionsPos');
		if ($actionsPos === 'L') {
			$this->_renderActions();
		}
	}
	// }}}
	
	// {{{ renderAttributePostfix
	/**
	 * Renders html that should be output after each entity attribute
	 * @param Object $entity a reference to the entity being rendered
	 * @param mixed $attr a reference to the attribute being rendered
	 * @param string $attrName the name of the attribute being rendered
   * @access  public
	 * @return String
	 */
	function renderAttributePostfix(& $entity, & $attr, $attrName) {
		SRA_EntityView::getGlobalFieldNamePrefix('');
		print('</td>');
	}
	// }}}
	
	// {{{ renderAttributePrefix
	/**
	 * Renders html that should be output before each entity attribute
	 * @param Object $entity a reference to the entity being rendered
	 * @param mixed $attr a reference to the attribute being rendered
	 * @param string $attrName the name of the attribute being rendered
   * @access  public
	 * @return String
	 */
	function renderAttributePrefix(& $entity, & $attr, $attrName) {
		$params =& $this->_params->getTypeSubset('llp');
		$key = $params->getParam("${attrName}ColCycle") ? $params->getParam("${attrName}ColCycle") : $params->getParam("${attrName}ColClass");
		if (!$key) {
			$key = $params->getParam('colCycle') ? $params->getParam('colCycle') : $params->getParam('colClass');
			$prefix = 'col';
		}
		else {
			$prefix = "${attrName}Col";
		}
		$classTag = $this->_getClassTag($key, $prefix, $attrName);
		
		$actionsPos = $params->getParam('actionsPos');
		if ($actionsPos !== FALSE && $actionsPos !== NULL && $actionsPos != 'L' && $actionsPos != 'R' && ((int) $actionsPos) === $this->_colIndex) {
			$this->_renderActions();
		}
		print("<td{$classTag}>");
		$this->_colIndex++;
	}
	// }}}
	
	
	// {{{ _displayMsg
	/**
	 * used to display a message in a javascript popup
	 * @param string $msg the message to display. if FALSE, no message will be displayed
   * @access private
	 * @return void
	 */
	function _displayMsg($msg) {
		if ($msg) {
			$msg = is_array($msg) ? implode('\n', $msg) : $msg;
			$msg = str_replace('"', '\"', $msg);
			print("\n<script type=\"text/javascript\">\n<!--\nalert(\"" . $msg . "\");\n//-->\n</script>\n");
		}
	}
	// }}}
	
	// {{{ _getClassTag
	/**
	 * returns the class tag (or empty string if not applicable)
	 * @param string $key class key
	 * @param string $prefix key prefix
	 * @param string $attr optional attr name
   * @access private
	 * @return string
	 */
	function _getClassTag($key, $prefix, $attr = '') {
		$params =& $this->_params->getTypeSubset('llp');
		$classTag = '';
		if ($key) {
			if (!isset($this->_classCycles[$key])) {
				$this->_classCycles[$key] = array('ptr' => 0, 'classes' => explode(',', $params->getParam("${prefix}Class")));
			}
			$this->_classCycles[$key]['ptr'] = $this->_classCycles[$key]['ptr'] == count($this->_classCycles[$key]['classes']) ? 0 : $this->_classCycles[$key]['ptr'];
			$tag = $params->getParam("${prefix}ClassTag") ? $params->getParam("${prefix}ClassTag") : 'class';
			if ($this->_classCycles[$key]['classes'][$this->_classCycles[$key]['ptr']]) {
				$classTag = " ${tag}=\"" . $this->_classCycles[$key]['classes'][$this->_classCycles[$key]['ptr']] . "\"";
				$classTag = str_replace('{$attr}', $attr, $classTag);
				if ($classTag == " ${tag}=\"\"") {
					$classTag = '';
				}
			}
			$this->_classCycles[$key]['ptr']++;
		}
		return $classTag;
	}
	// }}}
	
	// {{{ _renderActions
	/**
	 * Renders html for an entity actions
   * @access  public
	 * @return void
	 */
	function _renderActions() {
		$params =& $this->_params->getTypeSubset('llp');
		$key = $params->getParam("actionsColCycle") ? $params->getParam("actionsColCycle") : $params->getParam("actionsColClass");
		if (!$key) {
			$key = $params->getParam('colCycle') ? $params->getParam('colCycle') : $params->getParam('colClass');
			$prefix = 'col';
		}
		else {
			$prefix = "actionsCol";
		}
		$classTag = $this->_getClassTag($key, $prefix);
		print("<td{$classTag}>");
		if ($params->getParam('actionEncl')) {
			print('<' . $params->getParam('actionEncl') . '>');
		}
		$tpl =& SRA_Controller::getAppTemplate();
		$actions =& $this->_llpData->getActions();
		$keys = array_keys($actions);
		foreach ($keys as $key) {
			$tpl->assign('action', $actions[$key]);
			$tpl->display('model/llp/sra-list-action.tpl');
		}
		if ($params->getParam('actionEncl')) {
			print('</' . $params->getParam('actionEncl') . '>');
		}
		print('</td>');
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * Returns a string representation of this object
   * @access  public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_ListLookupProcessor object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_listlookupprocessor');
	}
	// }}}
	
  
  // private operations
  
}
// }}}
?>
