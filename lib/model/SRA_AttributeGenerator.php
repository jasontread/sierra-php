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
require_once('model/SRA_AttrMapping.php');
require_once('model/SRA_AttributeThumbnail.php');
// }}}

// {{{ Constants
/**
 * The 'required' validation constraint key
 * @type string
 */
define('SRA_ATTRIBUTE_GENERATOR_REQUIRED', 'required');

/**
 * The prefix to use for attribute values within the dtd (this value follows 
 * the entity name)
 * @type string
 */
define('SRA_ATTRIBUTE_DTD_ATTR_PREFIX', 'Attr');
// }}}

// {{{ SRA_AttributeGenerator
/**
 * Used to generate an entity model attribute
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_AttributeGenerator extends SRA_Generator {
  // {{{ Attributes
  // public attributes
  
  // private attributes
  var $_aggregate;
  var $_aggregateCardinality = FALSE;
  var $_aggregatePrefix;
	var $_name;
	var $_apiResource;
	var $_attrMapping;
	var $_attrMappings;
	var $_cardinality;
	var $_cardinalityErr;
	var $_cardinalityLower;
	var $_cardinalityUnique;
	var $_cardinalityUpper;
	var $_column;
	var $_columnType;
	var $_constraint;
  var $_dateFormat;
	var $_default;
  var $_hasDefault;
	var $_ddlCamelCase;
	var $_ddlUpperCase;
	var $_dtdCamelCase;
  var $_dtdName;
	var $_entityGenerator;
	var $_entityGenerators;
  var $_fileIconAttr;
  var $_fileIconDir;
  var $_fileIndexAttr;
  var $_fileUriAttr;
  var $_fileUriKbAttr;
  var $_fileUriMbAttr;
  var $_fileProcessor;
  var $_indexed;
  var $_jsonView;
	var $_lazyLoad = FALSE;
	var $_lazyLoadExclusive = FALSE;
	var $_nestedPks;
	var $_daoSuffix = FALSE;
	var $_globalName;
	var $_isFile;
	var $_mapped;
  var $_mappedExclude;
  var $_mappedInclude;
	var $_mappedPostfix;
	var $_mappedPrefix;
	var $_mappedResourcePostfix;
	var $_mappedResourcePrefix;
  var $_matchUserId;
	var $_nullDbEmptyStr;
	var $_voSuffix = FALSE;
	var $_onDeleteCascade;
  var $_onDeleteCascadeSet = FALSE;
  var $_onRemoveDelete;
	var $_orderBy;
	var $_primaryKey = FALSE;
	var $_readOnly;
  var $_recursiveLink;
  var $_renderDisplOption;
  var $_renderExclude;
  var $_renderInclude;
	var $_required = FALSE;
	var $_resource;
	var $_resourceHelp;
	var $_retrieveFunction;
	var $_sequence = FALSE;
  var $_setConvert;
	var $_setFunction;
	var $_setOnly;
	var $_singleOption = FALSE;
  var $_skipDefaultViews;
  var $_skipInclude;
	var $_skipPersistence;
  var $_skipWsdl;
	var $_table;
	var $_tablePkColumn = FALSE;
  var $_thumbnails = array();
	var $_type;
	var $_typeErrResource;
	var $_useReference;
	var $_msgs;
  var $_unionLink;
	var $_vars;
	var $_views;
	var $_validators;
  var $_xmlUseAttribute;
  var $_xmlView;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AttributeGenerator
	/**
	 * Creates a new SRA_AttributeGenerator with the configuration specified
	 * @param array $conf associative array of attribute configuration values
   * @access  private
	 */
	function SRA_AttributeGenerator(& $conf, $ddlCamelCase, $dtdCamelCase, & $entityGenerator, $types) {
		
		// determine and validate resource
		$prefixes = array('attribute', $entityGenerator->_name, $entityGenerator->_resource, 'text');
		$finalResource = isset($conf['attributes']['type']) && !SRA_EntityModeler::isValidType($conf['attributes']['type']) ? $conf['attributes']['type'] : NULL;
		$finalResourceHelp = $finalResource ? $finalResource . '-help' : NULL;
		$this->_resource = $this->getResource($conf, $entityGenerator->_resources, $prefixes, '', $finalResource);
		$this->_resourceHelp = $this->getHelpResource($conf, $entityGenerator->_resources, $prefixes, '', $finalResourceHelp);
		
		// validate required key
		if (!isset($conf['attributes']['key'])) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - Missing 'key' value for attribute " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// set type
		if (isset($conf['attributes']['is-file']) && $conf['attributes']['is-file'] == '1' && !isset($conf['attributes']['type'])) {
			if ($entityGenerator->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
				$conf['attributes']['type'] = SRA_DATA_TYPE_BLOB;
				$this->_type = SRA_DATA_TYPE_BLOB;
        if ($conf['attributes']['table'] && $conf['attributes']['table'] != $entityGenerator->_table && !isset($conf['attributes']['lazy-load'])) {
          $conf['attributes']['lazy-load'] = '1';
        }
        else if ((!$conf['attributes']['table'] || $conf['attributes']['table'] == $entityGenerator->_table) && !isset($conf['attributes']['lazy-load-exclusive'])) {
          $conf['attributes']['lazy-load-exclusive'] = '1';
        }
			}
			else {
				$conf['attributes']['max-length'] = 255;
			}
		}
    
    $this->_matchUserId = isset($conf['attributes']['match-user-id']) ? $conf['attributes']['match-user-id'] : $entityGenerator->_matchUserId;
		
		$this->_sequence = isset($conf['attributes']['sequence']) && $conf['attributes']['sequence'] == '1';
    if (isset($conf['attributes']['type']) && strtolower($conf['attributes']['type']) == 'text') { $conf['attributes']['type'] = SRA_DATA_TYPE_STRING; }
		if (isset($conf['attributes']['type'])) {
			$this->_type = $conf['attributes']['type'];
		}
		else {
			$this->_type = SRA_DATA_TYPE_STRING;
		}
		if ($this->_sequence) {
			$this->_type = SRA_DATA_TYPE_INT;
		}
		
		// attribute mappings
		$this->_mappedPostfix = isset($conf['attributes']['mapped-postfix']) ? $conf['attributes']['mapped-postfix'] : '';
		$this->_mappedResourcePostfix = isset($conf['attributes']['mapped-resource-postfix']) ? $conf['attributes']['mapped-resource-postfix'] : '';
		$this->_mappedPrefix = isset($conf['attributes']['mapped-prefix']) ? $conf['attributes']['mapped-prefix'] : '';
		$this->_mappedResourcePrefix = isset($conf['attributes']['mapped-resource-prefix']) ? $conf['attributes']['mapped-resource-prefix'] : '';
		$this->_attrMappings = array();
		if (isset($conf['attr-mapping']) && $conf['attr-mapping']) {
			$keys = array_keys($conf['attr-mapping']);
			foreach ($keys as $key) {
				$col = $this->getDdlName($conf['attr-mapping'][$key]['attributes']['column'], $key, $entityGenerator->_ddlUpperCase, $this->_mappedPrefix, $this->_mappedPostfix);
				$this->_attrMappings[$key] = new SRA_AttrMapping($key, $col, $conf['attr-mapping'][$key]['attributes']['resource'], $conf['attr-mapping'][$key]['attributes']['resource-help'], $this->_mappedResourcePostfix, $this->_mappedResourcePrefix);
				if (SRA_Error::isError($this->_attrMappings[$key])) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - attr-mapping ${key} produced error for attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
			}
		}
		if (!count($this->_attrMappings)) {
			$this->_attrMappings = FALSE;
		}
		
		// validate column(s)
		if (isset($conf['attributes']['table']) && $conf['attributes']['table']) {
			$tableName = $conf['attributes']['table'];
		}
		else {
			$tableName = $entityGenerator->_table;
		}
		
		// default lazy-load to True for entities and table related attributes
		if (!$this->_sequence && !isset($conf['attributes']['lazy-load']) && (isset($conf['attributes']['table']) || !SRA_EntityModeler::isValidType($this->_type)) && !$this->_attrMappings && (!isset($conf['attributes']['mapped']) || $conf['attributes']['mapped'] != '1')) {
			$conf['attributes']['lazy-load'] = '1';
		}
		
		// validate lazy load
		if (!isset($conf['attributes']['table']) && !isset($this->_type) && $conf['attributes']['lazy-load']) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - Attribute can only be 'lazy-loaded' if external table or entity type is specified for attribute " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate cardinality
		if (isset($conf['attributes']['cardinality']) && $conf['attributes']['cardinality']) {
			$pieces = explode('..', $conf['attributes']['cardinality']);
			if ($pieces[1] === '*') {
				$pieces[1] = SRA_MAX_INT_VAL;
			}
			$pieces[0] = (int) $pieces[0];
			$pieces[1] = (int) $pieces[1];
			if (!count($pieces) == 2 || !is_int($pieces[0]) || !is_int($pieces[1]) || $pieces[0] < 0 || $pieces[1] < 0 || $pieces[0] > $pieces[1]) {
				$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - Invalid cardinality specified '" . $conf['attributes']['cardinality'] . "' for attribute " . $conf['attributes']['key'];
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
				return;
			}
			else {
				// must specify both add-error-resource and remove-error-resource if upper bound > 1
				if (!isset($conf['attributes']['cardinality-err-resource'])) {
					$this->_cardinalityErr = $entityGenerator->_sysErrResource;
				}
				else {
					if (!SRA_Generator::validateResource($conf['attributes']['cardinality-err-resource'], $entityGenerator->_resources)) {
						$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - cardinality-err-resource '" . $conf['attributes']['cardinality-err-resource'] . "' is not valid for attribute " . $conf['attributes']['key'];
						$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
						return;
					}
					$this->_cardinalityErr = $conf['attributes']['cardinality-err-resource'];
				}
				$this->_cardinalityLower = $pieces[0];
				$this->_cardinalityUpper = $pieces[1];
				if ($this->_cardinalityUpper > 1) {
					$this->_cardinality = TRUE;
				}
        // scalar attribute stored in same table as entity
        if (SRA_EntityModeler::isValidType($this->_type) && ($tableName == $entityGenerator->_table)) {
          $this->_type = SRA_DATA_TYPE_STRING;
          if (!isset($conf['attributes']['max-length'])) { $conf['attributes']['max-length'] = 255; }
          $this->_aggregateCardinality = TRUE;
        }
			}
		}
		
		// validate constraint
		if (!$tableName && isset($conf['attributes']['constraint']) && $conf['attributes']['constraint'] && !$this->_cardinality) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - Constraint specified but no table for attribute " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate order-by
		if (isset($conf['attributes']['order-by']) && $conf['attributes']['order-by'] && !$conf['attributes']['table'] && !$this->_cardinality) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - order-by attributes must be stored in a separate table " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate api-resource
		if (isset($conf['attributes']['api-resource']) && !SRA_Generator::validateResource($conf['attributes']['api-resource'], $entityGenerator->_resources)) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - api-resource '" . $conf['attributes']['api-resource'] . "' is not valid for attribute " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate type
		if (!SRA_EntityModeler::isValidType($this->_type) && !in_array($this->_type, $types)) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - Type '" . $this->_type . "' is not a valid database or entity type. attribute: " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate msg
		$this->_msgs = array();
		if (isset($conf['msg'])) {
			$keys = array_keys($conf['msg']);
			foreach ($keys as $key) {
				if (!$conf['msg'][$key]['attributes']['resource']) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - msg '${key}' does not have required 'resource' attribute for attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				else if (!SRA_Generator::validateResource($conf['msg'][$key]['attributes']['resource'], $entityGenerator->_resources)) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - resource '" . $conf['msg'][$key]['attributes']['resource'] . "' for msg '${key}' is not valid for attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				$this->_msgs[$key] = $conf['msg'][$key]['attributes']['resource'];
			}
		}
		
		// add enclosing entity msgs
		$this->_msgs = array_merge($entityGenerator->_msgs, $this->_msgs);
		
		// validate var
		$this->_vars = array();
		if (isset($conf['var']) && $conf['var']) {
			$keys = array_keys($conf['var']);
			foreach ($keys as $key) {
				if (!isset($conf['var'][$key]['attributes']['value'])) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - var '${key}' does not have required 'value' attribute for attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				$this->_vars[$key] = $conf['var'][$key]['attributes']['value'];
			}
		}
    
		
		// dynamic depends
		if (isset($conf['attributes']['max-length'])) {
			$conf['attributes']['depends'] = isset($conf['attributes']['depends']) ? $conf['attributes']['depends'] . ' maxLength' : 'maxLength';
			$this->_vars['maxLength'] = $conf['attributes']['max-length'];
		}
		if (isset($conf['attributes']['min-length'])) {
			$conf['attributes']['depends'] = isset($conf['attributes']['depends']) ? $conf['attributes']['depends'] . ' minLength' : 'minLength';
			$this->_vars['minLength'] = $conf['attributes']['min-length'];
		}
		
		// views
    $this->_skipDefaultViews = isset($conf['attributes']['skip-default-views']) && $conf['attributes']['skip-default-views'] == '1';
    if (!$this->_skipDefaultViews) { $this->_mergeDefaultViews($conf, $entityGenerator->_defaultViews, $this->_type, $conf['attributes']['key'], $entityGenerator->_name, $conf['attributes']['cardinality'], $conf['attributes']['depends']); }
		if (SRA_Error::isError($views =& SRA_Generator::getViews($conf['view'], $entityGenerator->_viewProcessors, TRUE))) {
			return $views;
		}
    
		if (is_array($views) && count($views)) {
			SRA_EntityView::mergeExtends($views, $entityGenerator->_views);
			SRA_EntityView::mergeExtends($views, $entityGenerator->_globalViews);
			SRA_EntityView::setDefaultView($views);
			$this->_views =& $views;
		}
		
		//validators
		$this->_validators = array();
		if ($conf['attributes']['depends']) {
			$validators = explode(' ', $conf['attributes']['depends']);
			foreach ($validators as $validator) {
				$validator = trim($validator);
				if ($validator == 'required') {
					$this->_required = TRUE;
				}
				if ($validator == 'option') {
					$this->_singleOption = TRUE;
				}
				if (!SRA_AttributeValidator::isValidValidator($validator, $this->_vars, TRUE)) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - validator '${validator}' is not valid for attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				else if (!isset($this->_msgs[$validator])) {
					$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - No error message specified for validator '${validator}' in attribute " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				$this->_validators[] = $validator;
			}
		}
		
		// add data type validators (automatic depending on type)
		if (!in_array('boolean', $this->_validators) && $this->_type == SRA_DATA_TYPE_BOOLEAN) {
			$this->_validators[] = 'boolean';
			if (!isset($this->_msgs['boolean']) && isset($this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY])) {
				$this->_msgs['boolean'] = $this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY];
			}
		}
		else if (!in_array('date', $this->_validators) && ($this->_type == SRA_DATA_TYPE_DATE || $this->_type == SRA_DATA_TYPE_TYPE)) {
			$this->_validators[] = 'date';
			if (!isset($this->_msgs['date']) && isset($this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY])) {
				$this->_msgs['date'] = $this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY];
			}
		}
		else if (!in_array('integer', $this->_validators) && $this->_type == SRA_DATA_TYPE_INT) {
			$this->_validators[] = 'integer';
			if (!isset($this->_msgs['integer']) && isset($this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY])) {
				$this->_msgs['integer'] = $this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY];
			}
		}
		else if (!in_array('numeric', $this->_validators) && $this->_type == SRA_DATA_TYPE_FLOAT) {
			$this->_validators[] = 'numeric';
			if (!isset($this->_msgs['numeric']) && isset($this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY])) {
				$this->_msgs['numeric'] = $this->_msgs[SRA_ATTRIBUTE_TYPE_MSG_KEY];
			}
		}
		
		//validate type-err-resource
		if (isset($this->_type) && !isset($conf['attributes']['type-err-resource'])) {
			$conf['attributes']['type-err-resource'] = $entityGenerator->_sysErrResource;
		}
		if (!SRA_EntityModeler::isValidType($this->_type) && (!$conf['attributes']['type-err-resource'] || !SRA_Generator::validateResource($conf['attributes']['type-err-resource'], $entityGenerator->_resources))) {
			$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - type-err-resource is required but not present or invalid for attribute " . $conf['attributes']['key'] . ' Resource: ' . $conf['attributes']['type-err-resource'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
    
    $this->_isFile = isset($conf['attributes']['is-file']) && $conf['attributes']['is-file'] == '1';
    
    // file icon dir
    if ($this->_isFile && isset($conf['attributes']['file-icon-dir']) && !file_exists($conf['attributes']['file-icon-dir']) && !file_exists(SRA_Controller::getAppDir() . '/' . $conf['attributes']['file-icon-dir'])) {
      $msg = 'SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - file-icon-dir ' . $conf['attributes']['file-icon-dir'] . " is not valid for $key";
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    else if ($this->_isFile && isset($conf['attributes']['file-icon-dir'])) {
      $this->_fileIconDir = $conf['attributes']['file-icon-dir'];
    }
		
		// set etc values
		$this->_baseName = $conf['attributes']['key'];
    $this->_name = $conf['attributes']['key'];
    $this->_aggregate = isset($conf['attributes']['aggregate']) && $conf['attributes']['aggregate'] == '1';
    $this->_aggregatePrefix = isset($conf['attributes']['aggregate-prefix']) ? $conf['attributes']['aggregate-prefix'] : NULL;
		$this->_apiResource = $conf['attributes']['api-resource'];
    $this->_cardinalityUnique = !$this->_cardinality || $this->isEntity() || (isset($conf['attributes']['cardinality-unique']) && $conf['attributes']['cardinality-unique'] == '0') ? FALSE : TRUE;
    $this->_circularRef = isset($conf['attributes']['circular-ref']) && $conf['attributes']['circular-ref'] == '1';
    $this->_skipPersistence = isset($conf['attributes']['skip-persistence']) && $conf['attributes']['skip-persistence'] == '1';
		$this->_column = !$this->_skipPersistence ? (isset($conf['attributes']['column']) ? $conf['attributes']['column'] : $this->getDdlName(NULL, $this->_name, $entityGenerator->_ddlUpperCase, $entityGenerator->_columnPrefix, $entityGenerator->_columnPostfix, $this->_cardinality)) : NULL;
		$this->_columnType = isset($conf['attributes']['column-type']) ? $conf['attributes']['column-type'] : NULL;
		$this->_constraint = isset($conf['attributes']['constraint']) ? $conf['attributes']['constraint'] : NULL;
    $this->_dateFormat = isset($conf['attributes']['date-format']) ? $conf['attributes']['date-format'] : NULL;
		$this->_ddlCamelCase = $ddlCamelCase;
		$this->_ddlUpperCase = isset($ddlUpperCase) ? $ddlUpperCase : NULL;
		$this->_dtdCamelCase = $dtdCamelCase;
    $this->_dtdName = isset($conf['attributes']['dtd-name']) ? $conf['attributes']['dtd-name'] : NULL;
		$this->_default = isset($conf['attributes']['default']) ? $conf['attributes']['default'] : NULL;
    $this->_hasDefault = isset($conf['attributes']['default']);
    $this->_fileIconAttr = $this->_fileIconDir && $this->_isFile && isset($conf['attributes']['file-icon-attr']) ? $conf['attributes']['file-icon-attr'] : NULL;
    $this->_fileIndexAttr = $this->_isFile && isset($conf['attributes']['file-index-attr']) ? $conf['attributes']['file-index-attr'] : NULL;
    $this->_fileSizeAttr = $this->_isFile && isset($conf['attributes']['file-size-attr']) ? $conf['attributes']['file-size-attr'] : NULL;
    $this->_fileUriAttr = $this->_isFile && isset($conf['attributes']['file-uri-attr']) ? $conf['attributes']['file-uri-attr'] : NULL;
    $this->_fileUriKbAttr = $this->_isFile && isset($conf['attributes']['file-uri-kb-attr']) ? $conf['attributes']['file-uri-kb-attr'] : NULL;
    $this->_fileUriMbAttr = $this->_isFile && isset($conf['attributes']['file-uri-mb-attr']) ? $conf['attributes']['file-uri-mb-attr'] : NULL;
    $this->_fileProcessor = $this->_isFile && isset($conf['attributes']['file-processor']) ? $conf['attributes']['file-processor'] : NULL;
		$this->_entityGenerator =& $entityGenerator;
		$this->_indexed = isset($conf['attributes']['indexed']) && $conf['attributes']['indexed'] == '1';
    $this->_jsonView = isset($conf['attributes']['json-view']) ? $conf['attributes']['json-view'] : NULL;
    $this->_lazyLoad = isset($conf['attributes']['lazy-load']) && $conf['attributes']['lazy-load'] == '1';
		$this->_lazyLoadExclusive = isset($conf['attributes']['lazy-load-exclusive']) && $conf['attributes']['lazy-load-exclusive'] == '1';
		$this->_daoSuffix = $entityGenerator->_daoSuffix;
		$this->_globalName = isset($conf['attributes']['global-name']) ? $conf['attributes']['global-name'] : (isset($conf['attributes']['is-global']) && $conf['attributes']['is-global'] == '1' ? $this->_name : NULL);
		$this->_mapped = isset($conf['attributes']['mapped']) && $conf['attributes']['mapped'] == '1';
    $this->_mappedExclude = isset($conf['attributes']['mapped-exclude']) ? explode(' ', $conf['attributes']['mapped-exclude']) : NULL;
    $this->_mappedInclude = isset($conf['attributes']['mapped-include']) ? explode(' ', $conf['attributes']['mapped-include']) : NULL;
		$this->_nullDbEmptyStr = (isset($conf['attributes']['null-db-empty-str']) && $conf['attributes']['null-db-empty-str'] == '1') || $entityGenerator->_nullDbEmptyStr;
		$this->_voSuffix = $entityGenerator->_voSuffix;
		$this->_orderBy = isset($conf['attributes']['order-by']) ? $conf['attributes']['order-by'] : NULL;
		$this->_primaryKey = $entityGenerator->_primaryKey == $this->_name;
		$this->_readOnly = isset($conf['attributes']['read-only']) && $conf['attributes']['read-only'] == '1';
    $this->_recursiveLink = isset($conf['attributes']['recursive-link']) ? $conf['attributes']['recursive-link'] : NULL;
    $this->_renderDisplOption = isset($conf['attributes']['render-displ-option']) && $conf['attributes']['render-displ-option'] == '1' ? TRUE : FALSE;
    $this->_renderExclude = isset($conf['attributes']['render-exclude']) ? explode(' ', $conf['attributes']['render-exclude']) : array();
    $this->_renderInclude = isset($conf['attributes']['render-include']) ? explode(' ', $conf['attributes']['render-include']) : array();
		$this->_resource = $this->_resource ? $this->_resource : $this->_name;
		$this->_retrieveFunction = isset($conf['attributes']['retrieve-function']) ? $conf['attributes']['retrieve-function'] : NULL;
		$this->_sequence = isset($conf['attributes']['sequence']) && $conf['attributes']['sequence'] == '1';
		$this->_setConvert = isset($conf['attributes']['set-convert']) ? explode(' ', $conf['attributes']['set-convert']) : NULL;
    $this->_setFunction = isset($conf['attributes']['set-function']) ? $conf['attributes']['set-function'] : NULL;
		$this->_setOnly = isset($conf['attributes']['set-only']) && $conf['attributes']['set-only'] == '1';
    $this->_skipWsdl = isset($conf['attributes']['skip-wsdl']) && $conf['attributes']['skip-wsdl'] == '1' ? TRUE : FALSE;
    $this->_skipInclude = isset($conf['attributes']['skip-include']) && $conf['attributes']['skip-include'] == '1' ? TRUE : FALSE;
    $this->_syncAttributes = isset($conf['attributes']['sync-attributes']) ? explode(' ', $conf['attributes']['sync-attributes']) : NULL;
		$this->_table = isset($conf['attributes']['table']) ? $conf['attributes']['table'] : NULL;
		if (isset($conf['attributes']['table-pk-column'])) {
			$this->_tablePkColumn = $conf['attributes']['table-pk-column'];
		}
    if (isset($conf['thumbnail'])) {
      $keys = array_keys($conf['thumbnail']);
      foreach($keys as $key) {
        if (!SRA_AttributeThumbnail::isValid($this->_thumbnails[$key] = new SRA_AttributeThumbnail($conf['thumbnail'][$key]))) {
          $msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - thumbnail attribute $key is not valid for attribute " . $conf['attributes']['key'];
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
      }
    }
		$this->_type = $this->_type;
		if ($this->_sequence) {
			$this->_type = SRA_DATA_TYPE_INT;
		}
		$this->_typeErrResource = isset($conf['attributes']['type-err-resource']) ? $conf['attributes']['type-err-resource'] : NULL;
    $this->_unionLink = isset($conf['attributes']['union-link']) ? $conf['attributes']['union-link'] : NULL;
		$this->_useReference = TRUE;
		if (SRA_EntityModeler::isValidType($this->_type) && !$this->_isFile) {
			if ($this->_attrMappings) {
				$msg = "SRA_AttributeGenerator::SRA_AttributeGenerator: Failed - attr-mappings cannot be specified for non entity type attributes for attribute " . $conf['attributes']['key'];
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
				return;
			}
			$this->_useReference = FALSE;
		}
		// required bounds
		if (is_array($this->_validators) && in_array(SRA_ATTRIBUTE_GENERATOR_REQUIRED, $this->_validators) && !$this->_cardinalityLower) {
			$this->_cardinalityLower = 1;
		}
		$this->_onDeleteCascade = isset($conf['attributes']['on-delete-cascade']) && $conf['attributes']['on-delete-cascade'] == '1';
    if (isset($conf['attributes']['on-delete-cascade'])) {
      $this->_onDeleteCascadeSet = TRUE;
    }
    $this->_onRemoveDelete = isset($conf['attributes']['on-remove-delete']) && $conf['attributes']['on-remove-delete'] == '1';
    $this->_xmlUseAttribute = !$this->_cardinality && !$this->isEntity() && isset($conf['attributes']['xml-use-attribute']) && $conf['attributes']['xml-use-attribute'] == '1';
    $this->_xmlView = isset($conf['attributes']['xml-view']) ? $conf['attributes']['xml-view'] : NULL;
    
    // check for attributes not allowed with ws-db
    if ($entityGenerator->usesWsDb()) {
      if ($this->_lazyLoadExclusive) $this->_lazyLoad = TRUE;
      
      $err = NULL;
      if ($this->_aggregate) $err = 'aggregate';
      else if ($this->_aggregatePrefix) $err = 'aggregate-prefix';
      else if (isset($conf['attributes']['column'])) $err = 'column';
      else if (isset($conf['attributes']['column-type'])) $err = 'column-type';
      else if ($this->_constraint) $err = 'constraint';
      else if (isset($conf['attributes']['indexed'])) $err = 'indexed';
      else if ($this->_mapped) $err = 'mapped';
      else if ($this->_mappedExclude) $err = 'mapped-exclude';
      else if ($this->_mappedInclude) $err = 'mapped-include';
      else if ($this->_mappedPrefix) $err = 'mapped-prefix';
      else if ($this->_mappedPostfix) $err = 'mapped-postfix';
      else if ($this->_mappedResourcePrefix) $err = 'mapped-resource-prefix';
      else if ($this->_mappedResourcePostfix) $err = 'mapped-resource-postfix';
      else if ($this->_nullDbEmptyStr) $err = 'null-db-empty-str';
      else if ($this->_onDeleteCascade) $err = 'on-delete-cascade';
      else if ($this->_onRemoveDelete) $err = 'on-remove-delete';
      else if ($this->_orderBy) $err = 'order-by';
      else if ($this->_recursiveLink) $err = 'recursive-link';
      else if ($this->_retrieveFunction) $err = 'retrieve-function';
      else if ($this->_sequence) $err = 'sequence';
      else if ($this->_setFunction) $err = 'set-function';
      else if ($this->_table) $err = 'table';
      else if ($this->_tablePkColumn) $err = 'table-pk-column';
      else if ($this->_unionLink) $err = 'union-link';
      if ($err) {
        $msg = 'SRA_AttributeGenerator: Failed - the attribute ' . $err . ' cannot be used in conjunction with ws-db for entity ' . $entityGenerator->_name . ' and attribute ' . $this->_name;
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
    }
    
	}
	// }}}
	
  
  // public operations
  
	// {{{ getAggregateAttributes
	/**
	 * Returns all of the SRA_AttrMappings as SRA_AttributeGenerators for this attribute. 
	 * @param boolean $addNamePrefix whether or not to prefix each mapping with this attribute's name followed by an underscore
   * @access  public
	 * @return SRA_AttributeGenerator[]
	 */
	function & getAggregateAttributes() {
    if (!isset($this->_aggregateAttributes)) {
      $this->_aggregateAttributes = array();
      if ($this->isEntity() && $this->_aggregate) {
        if (!($this->_aggregateAttributes =& $this->getMappedAttributes())) {
          $this->_aggregateAttributes =& $this->_entityGenerators[$this->_type]->_attributeGenerators;
        }
        if ($this->_aggregatePrefix) {
          $keys = array_keys($this->_aggregateAttributes);
          foreach($keys as $key) {
            $this->_aggregateAttributes[$key] = SRA_Util::copyObject($this->_aggregateAttributes[$key]);
            if (!SRA_Util::beginsWith($this->_aggregateAttributes[$key]->_name, $this->_aggregatePrefix)) {
              $this->_aggregateAttributes[$key]->_name = $this->_aggregatePrefix . strtoupper(substr($this->_aggregateAttributes[$key]->_name, 0, 1)) . substr($this->_aggregateAttributes[$key]->_name, 1);
            }
          }
        }
      }
    }
    return count($this->_aggregateAttributes) ? $this->_aggregateAttributes : NULL;
  }
  // }}}
  
	// {{{ getConvertMethodName
	/**
	 * Returns the name of this attribute formatted for a method (first letter is 
	 * uppercase)
   * @access  public
	 * @return String
	 */
	function getConvertMethodName() {
		return 'convert' . strtoupper(substr($this->_type, 0, 1)) . substr($this->_type, 1, strlen($this->_type) - 1);
	}
	// }}}
  
	// {{{ getEntityPkColumn
	/**
	 * if this attribute is an entity, returns the name of the primary key column 
   * for that entity
   * @access  public
	 * @return String
	 */
	function getEntityPkColumn() {
    return $this->isEntity() && isset($this->_entityGenerators[$this->_type]) && ($pk =& $this->_entityGenerators[$this->_type]->getPrimaryKeyAttribute()) ? $pk->_column : 'unknown';
	}
	// }}}
  
	// {{{ getEntityPkName
	/**
	 * if this attribute is an entity, returns the name of the primary key 
   * attribute for that entity
   * @access  public
	 * @return String
	 */
	function getEntityPkName() {
    return $this->isEntity() && isset($this->_entityGenerators[$this->_type]) && ($pk =& $this->_entityGenerators[$this->_type]->getPrimaryKeyAttribute()) ? $pk->_name : 'unknown';
	}
	// }}}
  
	// {{{ getEntityTable
	/**
	 * if this attribute is an entity, returns the name of the table for that entity
   * @access  public
	 * @return String
	 */
	function getEntityTable() {
    return $this->isEntity() && isset($this->_entityGenerators[$this->_type]) ? $this->_entityGenerators[$this->_type]->_table : 'unknown';
	}
	// }}}
  
	// {{{ getNestedElementDtdName
	/**
	 * Returns the DTD name value for this attribute if it is used as a sub-element
   * of the entity it belongs in
   * @access  public
	 * @return String
	 */
	function getNestedElementDtdName() {
    $baseName = $this->_entityGenerator->_name . SRA_ATTRIBUTE_DTD_ATTR_PREFIX . strtoupper(substr($this->_name, 0, 1)) . substr($this->_name, 1);
    return $this->_dtdName ? $this->_dtdName : ($this->_dtdCamelCase ? $baseName : SRA_Util::camelCaseToDashes($baseName));
	}
	// }}}
  
	// {{{ getNestedElementDtdModfier
	/**
	 * Returns the modifier for the nested dtd name
   * @access  public
	 * @return String
	 */
	function getNestedElementDtdModfier() {
    return !$this->_isFile ? $this->getDtdModifier() : '?';
	}
	// }}}
  
	// {{{ getSubElementDtdName
	/**
	 * Returns the DTD name value for this attribute if it is used as a sub-element
   * of the entity it belongs in
   * @access  public
	 * @return String
	 */
	function getSubElementDtdName() {
    if ($this->_isFile) {
      return $this->_dtdCamelCase ? SRA_ENTITY_MODELER_FILE_DTD_NAME : SRA_Util::camelCaseToDashes(SRA_ENTITY_MODELER_FILE_DTD_NAME);
    }
    else if ($this->isEntity()) {
      return $this->getTypeDtdName();
    }
	}
	// }}}
  
	// {{{ getDtdModifier
	/**
	 * Returns the modifier for the dtd sub-element dtd name
   * @access  public
	 * @return String
	 */
	function getDtdModifier() {
    return $this->_cardinality ? '*' : '?';
	}
	// }}}
	
	// {{{ getMappedAttributes
	/**
	 * Returns all of the SRA_AttrMappings as SRA_AttributeGenerators for this attribute. 
	 * @param boolean $addNamePrefix whether or not to prefix each mapping with this attribute's name followed by an underscore
   * @access  public
	 * @return SRA_AttributeGenerator[]
	 */
	function & getMappedAttributes($addNamePrefix = FALSE) {
    $mappedAttrs = array();
  
    // fully mapped
    if ($this->_mapped) {
      $keys = array_keys($this->_entityGenerators[$this->_type]->_attributeGenerators);
      foreach ($keys as $key) {
        // skip attribute
        if (($this->_mappedExclude && in_array($key, $this->_mappedExclude)) || ($this->_mappedInclude && !in_array($key, $this->_mappedInclude))) { continue; }
        
        $generator =& $this->_entityGenerators[$this->_type]->_attributeGenerators[$key];
        
        // skip primary key attributes
        if ($this->_entityGenerators[$this->_type]->_primaryKey && $generator->_name == $this->_entityGenerators[$this->_type]->_primaryKey) { continue; }
        
        $col = $this->getDdlName($generator->_column, $generator->_name, $this->_entityGenerator->_ddlUpperCase, $this->_mappedPrefix, $this->_mappedPostfix);
        $mapping = new SRA_AttrMapping($key, $col, $generator->_resource, $generator->_resourceHelp, $this->_mappedResourcePostfix, $this->_mappedResourcePrefix);
        if (SRA_Error::isError($mappedAttrs[$key] =& $mapping->toAttributeGenerator($this, $this->_entityGenerators))) {
          $msg = 'SRA_AttributeGenerator::getMappedAttributes: Unable to create AttributeMapping for mapped attribute ' . $this->_name . "::${key}";
          return SRA_Error::logError($msg, __FILE__, __LINE__);
        }
      }
    }
    // partially mapped
    else if ($this->_attrMappings) {
      $keys = array_keys($this->_attrMappings);
      foreach ($keys as $key) {
        if (SRA_Error::isError($mappedAttrs[$key] =& $this->_attrMappings[$key]->toAttributeGenerator($this, $this->_entityGenerators))) {
          $msg = "SRA_AttributeGenerator::getMappedAttributes: Unable to create AttributeMapping for mapped column ${key}";
          return SRA_Error::logError($msg, __FILE__, __LINE__);
        }
      }
    }
    $keys = array_keys($mappedAttrs);
    foreach ($keys as $key) {
      if ($this->_table) {
        $mappedAttrs[$key]->_table = $this->_table;
      }
    }
      
    $keys = array_keys($mappedAttrs);
    foreach ($keys as $key) {
      while(SRA_Util::beginsWith($mappedAttrs[$key]->_name, $this->_name . '_')) {
        $mappedAttrs[$key]->_name = substr($mappedAttrs[$key]->_name, strlen($this->_name) + 1);
      }
      if ($addNamePrefix && !SRA_Util::beginsWith($mappedAttrs[$key]->_name, $this->_name . '_')) {
        $mappedAttrs[$key]->_name = $this->_name . '_' . $mappedAttrs[$key]->_name;
      }
    }
		return count($mappedAttrs) ? $mappedAttrs : NULL;
	}
	// }}}
  
	// {{{ getMethodName
	/**
	 * Returns the name of this attribute formatted for a method (first letter is 
	 * uppercase)
	 * 
	 * @param string $attr optional - the name of the attribute to return the 
	 * method name for
   * @access  public
	 * @return String
	 */
	function getMethodName($attr = FALSE, $base = FALSE) {
		if (!$attr) {
			$attr = $base ? $this->_baseName : $this->_name;
		}
		return strtoupper(substr($attr, 0, 1)) . substr($attr, 1, strlen($attr) - 1);
	}
	// }}}
	
	// {{{ getNestedDAOName
	/**
	 * Returns the name of the DAO for nested attributes
   * @access  public
	 * @return String
	 */
	function getNestedDAOName() {
		return $this->_type . $this->_daoSuffix;
	}
	// }}}
	
	// {{{ getNestedVOName
	/**
	 * Returns the name of the VO for nested attributes
   * @access  public
	 * @return String
	 */
	function getNestedVOName() {
		return $this->_type . $this->_voSuffix;
	}
	// }}}
	
	// {{{ getOptionsMap
	/**
	 * Returns the options map (value/label pairs) for this attribute. if this 
	 * attribute is an entity, it will return TRUE if the an options map 
	 * can/should be used for that type (only TRUE if 'displ' var specified)
	 * 
   * @access  public
	 * @return array()
	 */
	function &getOptionsMap() {
		static $map = array();
    $mkey = $this->_entityGenerator->_name . '-' . $this->_name;
		if (!isset($map[$mkey]) && (isset($this->_vars['options']) || isset($this->_vars['resources']) || isset($this->_vars['sql']) || isset($this->_vars['displ']))) {
			$map[$mkey] = array();
			
			// 'options' var
			if (isset($this->_vars['options'])) {
				$options = explode(' ', $this->_vars['options']);
				$keys = array_keys($options);
				foreach ($keys as $key) {
					if (strstr($options[$key], '=')) {
						$tmp = explode('=', $options[$key]);
						$map[$mkey][trim($tmp[0])] = trim($tmp[1]);
					}
					else {
						$map[$mkey][$options[$key]] = $options[$key];
					}
				}
			}
			
			// 'resources' var
			else if (isset($this->_vars['resources'])) {
				if (SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle($this->_vars['resources']))) {
					$map[$mkey] =& $rb->getData();
				}
			}
			
			// 'sql' var
			else if (isset($this->_vars['sql'])) {
				$map[$mkey] =& SRA_AttributeValidator::getSqlOptionsMap($this->_vars['sql']);
			}
			
			// 'displ' var
			else if (isset($this->_vars['displ'])) {
				return TRUE;
			}
			
		}
		if (isset($map[$mkey])) {
			return $map[$mkey];
		}
		else {
			return FALSE;
		}
	}
	// }}}
	
	// {{{ getOptionsValMethod
	/**
	 * Returns the options map value method that should be used for entity type 
	 * attributes with (option|options) validation constraints and a 'displ' var 
	 * defined
	 * 
   * @access  public
	 * @return string
	 */
	function getOptionsValMethod() {
		return strstr($this->_vars['displ'], '$') ? 'parseString' : 'getAttribute';
	}
	// }}}
	
	// {{{ getRangeArray
	/**
	 * Returns the array of values possible for an attribute with a range 
	 * constraint
   * @access  public
	 * @return String[]
	 */
	function getRangeArray() {
		$values = array();
		if (isset($this->_vars['min']) && isset($this->_vars['max'])) {
			for ($i = $this->_vars['min']; $i<= $this->_vars['max']; $i++) {
				$values[] = $i;
			}
		}
		return $values;
	}
	// }}}
	
	// {{{ getDtdSubElementName
	/**
	 * Returns the DTD name value for an attribute declared as a sub-element
   * @access  public
	 * @return String
	 */
	function getDtdSubElementName() {
    return $this->_entityGenerator->getDtdSubElementName($this->_name);
	}
	// }}}
	
	// {{{ getVar
	/**
	 * Returns the value of the var specified or FALSE if it doesn't exist
	 * @param string $id the id of the var to return
   * @access  public
	 * @return string
	 */
	function getVar($id) {
		if (isset($this->_vars[$id])) {
			return $this->_vars[$id];
		}
		return FALSE;
	}
	// }}}
  
	// {{{ getWsdlType
	/**
	 * returns the wsdl type for this attribute
   * @access public
	 * @return string
	 */
	function getWsdlType() {
    switch($this->_type) {
      case SRA_DATA_TYPE_BLOB:
        $type = $this->_isFile ? 'tns:SRA_File' : 'xsd:base64Binary';
        break;
      case SRA_DATA_TYPE_BOOLEAN:
        $type = 'xsd:boolean';
        break;
      case SRA_DATA_TYPE_DATE:
        $type = 'xsd:date';
        break;
      case SRA_DATA_TYPE_FLOAT:
        $type = 'xsd:double';
        break;
      case SRA_DATA_TYPE_INT:
        $type = 'xsd:integer';
        break;
      case SRA_DATA_TYPE_STRING:
        $type = 'xsd:string';
        break;
      case SRA_DATA_TYPE_TIME:
        $type = 'xsd:dateTime';
        break;
      default:
        $type = 'tns:' . $this->_type;
        break;
    }
    return $type;
	}
	// }}}
	
	// {{{ hasOptions
	/**
	 * Returns TRUE if this attribute has options
   * @access  public
	 * @return boolean
	 */
	function hasOptions() {
		return isset($this->_vars['options']) || isset($this->_vars['resources']) || isset($this->_vars['sql']) || isset($this->_vars['displ']) || isset($this->_vars['code']);
	}
	// }}}
  
	// {{{ hasCodeOptions
	/**
	 * Returns TRUE if this attribute has code based options
   * @access  public
	 * @return boolean
	 */
	function hasCodeOptions() {
		return isset($this->_vars['code']);
	}
	// }}}
	
	// {{{ hasRbOptions
	/**
	 * Returns TRUE if this attribute has SRA_ResourceBundle based options
   * @access  public
	 * @return boolean
	 */
	function hasRbOptions() {
		return isset($this->_vars['resources']);
	}
	// }}}
	
	// {{{ hasSqlOptions
	/**
	 * Returns TRUE if this attribute has SQL based options
   * @access  public
	 * @return boolean
	 */
	function hasSqlOptions() {
		return isset($this->_vars['sql']);
	}
	// }}}
	
	// {{{ hasRange
	/**
	 * Returns TRUE if this attribute has a range
   * @access  public
	 * @return boolean
	 */
	function hasRange() {
		$keys = array_keys($this->_validators);
		foreach ($keys as $key) {
			if ($this->_validators[$key] == 'range') {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ isBaseAttribute
	/**
	 * Returns TRUE if this attribute is a base attribute that can be SET
   * @access  public
	 * @return boolean
	 */
	function isBaseAttribute() {
		return ((!$this->_table || $this->_table == $this->_entityGenerator->_table) && !$this->_sequence && !$this->_readOnly && $this->_column);
	}
	// }}}
	
	// {{{ isEntity
	/**
	 * Returns TRUE if this attribute is actually a reference to another Entity
	 * entity
   * @access  public
	 * @return boolean
	 */
	function isEntity() {
		return !SRA_EntityModeler::isValidType($this->_type);
	}
	// }}}
  
	// {{{ isEntitySkipPersistence
	/**
	 * returns TRUE if this is an entity attribute and the entity is corresponds 
   * to is non-persistent
   * @access  public
	 * @return boolean
	 */
	function isEntitySkipPersistence() {
    return $this->isEntity() && isset($this->_entityGenerators[$this->_type]) && $this->_entityGenerators[$this->_type]->_skipPersistence;
	}
	// }}}
  
	// {{{ isEntitySkipPersistenceNoPk
	/**
	 * returns TRUE if this is an entity attribute and the entity is corresponds 
   * to is non-persistent and does not have a primary key attribute
   * @access  public
	 * @return boolean
	 */
	function isEntitySkipPersistenceNoPk() {
    return $this->isEntitySkipPersistence() && !$this->_entityGenerators[$this->_type]->_primaryKey;
	}
	// }}}
  
	// {{{ isRequired
	/**
	 * Returns TRUE if this attribute is required (based on whether or not it 
   * has the 'required' validator)
   * @access  public
	 * @return boolean
	 */
	function isRequired() {
		$keys = array_keys($this->_validators);
		foreach ($keys as $key) {
			if ($this->_validators[$key] == 'required') {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
  
	// {{{ _mergeDefaultViews
	/**
	 * creates views based on the global-views/default-view configuration for the 
   * model
   * @access  private
	 * @return void
	 */
  function _mergeDefaultViews(& $conf, & $defaultViews, $type, $attrName, $entityName, $cardinality, $depends) {
    if ($defaultViews) {
      $depends = $depends ? explode(' ', $depends) : NULL;
      $keys = array_keys($defaultViews);
      foreach($keys as $key) {
        if ((!in_array('*', $defaultViews[$key]['types']) && !in_array($type, $defaultViews[$key]['types'])) || 
            ($defaultViews[$key]['includeAttrs'] && !in_array($attrName, $defaultViews[$key]['includeAttrs'])) || 
            ($defaultViews[$key]['excludeAttrs'] && in_array($attrName, $defaultViews[$key]['excludeAttrs'])) || 
            ($defaultViews[$key]['includeEntities'] && !in_array($entityName, $defaultViews[$key]['includeEntities'])) || 
            ($defaultViews[$key]['excludeEntities'] && in_array($entityName, $defaultViews[$key]['excludeEntities'])) || 
            (isset($defaultViews[$key]['cardinality']) && $defaultViews[$key]['cardinality'] != $cardinality) || 
            (!isset($defaultViews[$key]['cardinality']) && $cardinality) || 
            ($defaultViews[$key]['includeDepends'] && (!$depends || count(array_intersect($depends, $defaultViews[$key]['includeDepends'])) != count($defaultViews[$key]['includeDepends']))) || 
            ($defaultViews[$key]['excludeDepends'] && $depends && count(array_intersect($depends, $defaultViews[$key]['excludeDepends'])) > 0)) { continue; }
        if (!$conf['view']) { $conf['view'] = array(); }
        if (!isset($conf['view'][$defaultViews[$key]['id']])) {
          $conf['view'][$defaultViews[$key]['id']] = array('attributes' => array('key' => $defaultViews[$key]['id'], 'extends' => $defaultViews[$key]['view']));
        }
      }
    }
  }
  // }}}
	
  
  // static methods
  
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_Generator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid(& $object) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_attributegenerator');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
