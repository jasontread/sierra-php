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
require_once('model/SRA_Generator.php');
require_once('model/SRA_AttributeGenerator.php');
require_once('model/SRA_ValidateGenerator.php');
require_once('model/SRA_FileAttribute.php');
require_once('model/SRA_EntitySchema.php');
require_once('model/SRA_EntityView.php');
require_once('model/SRA_EntityViewProcessor.php');
require_once('model/SRA_AopAspect.php');
require_once('model/SRA_AopIntroduction.php');
require_once('model/SRA_WS.php');
// }}}

// {{{ Constants

/**
 * the class suffix to use for DAOs
 */
define('SRA_ENTITY_GENERATOR_DAO_SUFFIX', 'DAO');

/**
 * the class suffix to use for VOs
 */
define('SRA_ENTITY_GENERATOR_VO_SUFFIX', 'VO');

/**
 * the max # of recursive hits that are allows to the getSchema method. if this 
 * # is exceeded, an SRA_Error will be logged and the process will be killed using 
 * the exit function
 */
define('SRA_ENTITY_GENERATOR_MAX_SCHEMA_RECURSION', 20);

// }}}

// {{{ SRA_EntityGenerator
/**
 * Used to generate an entity model entity
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_EntityGenerator extends SRA_Generator {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	var $_name;
  var $_aopAspects = array();
  var $_aopIntroductions = array();
	var $_abstract;
	var $_apiResource;
  var $_attributeGenerators = array();
	var $_columnPostfix = '';
	var $_columnPrefix = '';
	var $_constraint;
  var $_customDao;
	var $_dao;
	var $_daoFile;
	var $_daoExtends;
	var $_daoExtendsFile;
	var $_daoSuffix;
	var $_db;
	var $_dbRefIntegrity;
	var $_ddlCamelCase;
	var $_ddlUpperCase;
  var $_defaultViews;
  var $_dtdAttributes;
	var $_dtdCamelCase;
  var $_dtdSubElements;
	var $_entityGenerators;
	var $_entityModelXml;
	var $_fileDir;
	var $_fileDirUri;
	var $_fileHandling;
  var $_fileIconDir;
	var $_fileScriptRewrite;
	var $_fileScriptUri;
	var $_generatePath;
	var $_globalViews;
  var $_ignoreBadImport;
  var $_matchUserId;
	var $_msgs;
	var $_mysqlTableType;
	var $_nestedQueriesOk;
	var $_nullDbEmptyStr;
  var $_onCreateImport;
	var $_onDeleteUpdate;
	var $_orderBy;
	var $_parentEntity;
	var $_primaryKey;
	var $_propagateUpdate;
  var $_renderAppend;
  var $_renderExclude;
  var $_renderInclude;
	var $_resource;
  var $_resourceBundles = FALSE;
	var $_resources;
	var $_resourceHelp;
	var $_schema;
	var $_schemaLoading = FALSE;
	var $_schemaRecursionCount = 0;
	var $_skipPersistence;
  var $_skipWsdl;
	var $_sysErrResource;
	var $_table;
	var $_types;
  var $_unitTest;
  var $_unitTestClass;
	var $_viewProcessors;
	var $_views;
	var $_voExtends;
	var $_voExtendsFile;
	var $_voSuffix;
	var $_validateGenerators;
  var $_webServices = array();
  var $_wsDb;
  var $_wsDbName;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntityGenerator
	/**
	 * Creates a new SRA_EntityGenerator with the configuration specified
	 * @param boolean $ddlCamelCase whether or not to generate camel case ddl
	 * @param boolean $dtdCamelCase whether or not to generate camel case dtds
	 * @param string $generatePath the directory where generated class should be 
	 * created
	 * @param array $types an array of all of the entity types defined in the 
	 * entity model xml configuration
	 * @param string $entityModelXml path to the entity model xml configuration
	 * @param string $sysErrResource the system error resource key
	 * @param array $conf associative array of entity configuration values
	 * @param ViewProcessor[] $viewProcessors global view processors that may be 
	 * used by this entity
	 * @param SRA_EntityView[] $globalViews global views that this entity or its attributes' views may extend from
   * @access  private
	 */
	function SRA_EntityGenerator($dbKey, $dbRefIntegrity, $ddlCamelCase, $ddlUpperCase, $dtdCamelCase, $msgs, $nestedQueriesOk, $generatePath, $types, $entityModelXml, $resources, $sysErrResource, & $conf, & $viewProcessors, & $globalViews, $defaultViews, $daoSuffix, $voSuffix, $wsDb) {
		
		$this->_name = $conf['attributes']['key'];
		$this->_abstract = isset($conf['attributes']['abstract']) && $conf['attributes']['abstract'] == '1';
		$this->_entityModelXml = $entityModelXml;
		$this->_viewProcessors =& $viewProcessors;
		$this->_globalViews =& $globalViews;
    $this->_defaultViews = $defaultViews;
    // default views
    if (isset($conf['default-view']) && SRA_Error::isError($entityDefaultViews = SRA_Generator::getDefaultViews($conf['default-view'], $globalViews))) {
      return $entityDefaultViews;
    }
    if ($entityDefaultViews) { $this->_defaultViews = array_merge($entityDefaultViews, $this->_defaultViews ? $this->_defaultViews : array()); }
		
		if (isset($conf['attributes']['resources']) && $conf['attributes']['resources']) {
			$this->_resources = $conf['attributes']['resources'];
		}
		else {
			$this->_resources = $resources;
		}
		if ($this->_resources && !is_array($this->_resources)) {
			$this->_resources = explode(' ', $this->_resources);
		}
		if (isset($conf['attributes']['ws-db']) && $conf['attributes']['ws-db']) {
			$this->_wsDb = $conf['attributes']['ws-db'];
		}
		else {
			$this->_wsDb = $wsDb;
		}
    $this->_wsDbName = isset($conf['attributes']['ws-db-name']) ? $conf['attributes']['ws-db-name'] : NULL;
		
		$this->_ddlCamelCase = $ddlCamelCase;
		$this->_ddlUpperCase = $ddlUpperCase;
		$this->_dtdCamelCase = $dtdCamelCase;
		$this->_fileHandling = $conf['attributes']['file-handling'];
    $this->_matchUserId = $conf['attributes']['match-user-id'];
    $this->_mysqlTableType = isset($conf['attributes']['mysql-table-type']) ? $conf['attributes']['mysql-table-type'] : NULL;
		$this->_nullDbEmptyStr = isset($conf['attributes']['null-db-empty-str']) && $conf['attributes']['null-db-empty-str'] == '1';
    $this->_skipPersistence = isset($conf['attributes']['skip-persistence']) && $conf['attributes']['skip-persistence'] == '1';
		
		// validate configuration
		
		// missing mandatory attributes
		if (!count($conf['attribute']) || !$conf['attributes']['key']) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Missing 'attribute' 'key' for entity " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		$this->_resource = $this->getResource($conf, $this->_resources, array('entity', 'text'));
		$this->_resourceHelp = $this->getHelpResource($conf, $this->_resources, array('entity'));

		// validate api-resource
		if ($conf['attributes']['api-resource'] && !SRA_Generator::validateResource($conf['attributes']['api-resource'], $this->_resources)) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - api-resource '" . $conf['attributes']['api-resource'] . "' is not valid for entity " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		$this->_sysErrResource = $sysErrResource;
		
		// invalid dao
		if (isset($conf['attributes']['dao']) && trim($conf['attributes']['dao']) && !($daoFile = SRA_File::getRelativePath('lib', $conf['attributes']['dao'] . '.' . SRA_SYS_PHP_EXTENSION))) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed -  dao '" . $conf['attributes']['dao'] . "' class file does not exist";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$conf['attributes']['dao'] = isset($daoFile) && $daoFile ? basename($conf['attributes']['dao'], '.' . SRA_SYS_PHP_EXTENSION) : NULL;
		
		// invalid daoExtends
		if (isset($conf['attributes']['dao-extends']) && !($daoExtendsFile = SRA_File::getRelativePath('lib', $conf['attributes']['dao-extends'] . '.' . SRA_SYS_PHP_EXTENSION))) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed -  dao-extends '" . $conf['attributes']['dao-extends'] . "' class file does not exist";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$conf['attributes']['dao-extends'] = isset($daoExtendsFile) && $daoExtendsFile ? basename($conf['attributes']['dao-extends'], '.' . SRA_SYS_PHP_EXTENSION) : NULL;
    
		// invalid unit test
    if ($conf['attributes']['unit-test'] && !SRA_Util::endsWith($conf['attributes']['unit-test'], '.' . SRA_SYS_PHP_EXTENSION)) { $conf['attributes']['unit-test'] .= '.' . SRA_SYS_PHP_EXTENSION; }
		if (isset($conf['attributes']['unit-test']) && !($this->_unitTest = SRA_File::getRelativePath('lib', $conf['attributes']['unit-test']))) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed -  unit-test '" . $conf['attributes']['unit-test'] . "' class file does not exist";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
		}
    else if ($this->_unitTest) {
      $this->_unitTestClass = str_replace('.' . SRA_SYS_PHP_EXTENSION, '', basename($this->_unitTest));
    }
		
		// invalid voExtends
		if (isset($conf['attributes']['vo-extends']) && !($voExtendsFile = SRA_File::getRelativePath('lib', $conf['attributes']['vo-extends'] . '.' . SRA_SYS_PHP_EXTENSION))) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed -  vo-extends '" . $conf['attributes']['vo-extends'] . "' class file does not exist";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$conf['attributes']['vo-extends'] = isset($voExtendsFile) && $voExtendsFile ? basename($conf['attributes']['vo-extends'], '.' . SRA_SYS_PHP_EXTENSION) : NULL;
		
		// validate msg
		$this->_msgs = array();
		if (isset($conf['msg'])) {
			$keys = array_keys($conf['msg']);
			foreach ($keys as $key) {
				$this->_msgs[$key] = $conf['msg'][$key]['attributes']['resource'];
			}
		}
		
		// add enclosing entity msgs
		$this->_msgs = array_merge($msgs, $this->_msgs);
		
		// views
		if (SRA_Error::isError($views =& SRA_Generator::getViews($conf['view'], $viewProcessors))) {
			return $views;
		}
		if (is_array($views) && count($views)) {
			SRA_EntityView::mergeExtends($views, $views);
			SRA_EntityView::mergeExtends($views, $globalViews);
			SRA_EntityView::setDefaultView($views);
			$this->_views =& $views;
		}
		
		// both table and dao
    $this->_customDao = isset($conf['attributes']['dao']) && $conf['attributes']['dao'] ? TRUE : FALSE;
		$this->_table = !$this->_skipPersistence && !$this->usesWsDb() && !$this->_customDao ? $this->getDdlName(isset($conf['attributes']['table']) ? $conf['attributes']['table'] : NULL, $this->_name, $this->_ddlUpperCase) : NULL;
		
		// set suffixes
		$this->_daoSuffix = $daoSuffix;
		$this->_voSuffix = $voSuffix;
		
		$this->_columnPrefix = isset($conf['attributes']['column-prefix']) ? $conf['attributes']['column-prefix'] : '';
		$this->_columnPostfix = isset($conf['attributes']['column-postfix']) ? $conf['attributes']['column-postfix'] : '';
		
		// validate primary key
		if (!$this->_abstract && ((!isset($conf['attributes']['skip-persistence']) || $conf['attributes']['skip-persistence'] == '0') && (!isset($conf['attributes']['primary-key']) || !isset($conf['attribute'][$conf['attributes']['primary-key']])))) {
			$msg = 'SRA_EntityGenerator::SRA_EntityGenerator: Failed - Valid primary key was not specified for entity ' . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
    
    // file icon dir
    if (isset($conf['attributes']['file-icon-dir']) && !is_dir($conf['attributes']['file-icon-dir']) && !is_dir(SRA_Controller::getAppDir() . '/' . $conf['attributes']['file-icon-dir'])) {
      $msg = 'SRA_EntityGenerator::SRA_EntityGenerator: Failed - file-icon-dir ' . $conf['attributes']['file-icon-dir'] . " is not valid for $key";
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    else if (isset($conf['attributes']['file-icon-dir'])) {
      $this->_fileIconDir = $conf['attributes']['file-icon-dir'];
    }
		
		// check/initialize SRA_AttributeGenerators
		$this->_primaryKey = isset($conf['attributes']['primary-key']) ? $conf['attributes']['primary-key'] : NULL;
		if (isset($conf['validate'])) {
			$vkeys = array_keys($conf['validate']);
		}
		$primaryTableHasColumn = FALSE;
		$hasFileAttrs = FALSE;
		$keys = array_keys($conf['attribute']);
		foreach ($keys as $key) {
      if ($this->_fileIconDir && !isset($conf['attribute'][$key]['attributes']['file-icon-dir'])) { $conf['attribute'][$key]['attributes']['file-icon-dir'] = $this->_fileIconDir; }
      if (isset($conf['attributes']['is-global']) && !isset($conf['attribute'][$key]['attributes']['is-global'])) { $conf['attribute'][$key]['attributes']['is-global'] = $conf['attributes']['is-global']; }
			$this->_attributeGenerators[$key] = new SRA_AttributeGenerator($conf['attribute'][$key], $ddlCamelCase, $dtdCamelCase, $this, $types);
			if (isset($this->_attributeGenerators[$key]->err)) {
				$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Attribute ${key} produced error for entity " . $conf['attributes']['key'];
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
				return;
			}
			if ($this->_attributeGenerators[$key]->_isFile) {
				$hasFileAttrs = TRUE;
			}
			if (!$this->_skipPersistence && !$this->usesWsDb() && !$this->_customDao && $this->_attributeGenerators[$key]->_column && (!$this->_attributeGenerators[$key]->_table || $this->_attributeGenerators[$key]->_table == $this->_table) && !$this->_attributeGenerators[$key]->_sequence) {
				$primaryTableHasColumn = TRUE;
			}
		}
		if (!$this->_skipPersistence && !$this->usesWsDb() && !$this->_customDao && !$primaryTableHasColumn) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Entity " . $conf['attributes']['key'] . ' has no columns in it\'s primary table';
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		
		// validate file handling
		if ($hasFileAttrs) {
			$this->_fileScriptUri = $conf['attributes']['file-script-uri'];
			$this->_fileScriptRewrite = $conf['attributes']['file-script-rewrite'] == '1';
			$this->_fileDir = $conf['attributes']['file-dir'];
			if (!is_dir(dirname($this->_fileDir))) {
				if (substr($this->_fileDir, 0, 1) != '/') {
					$this->_fileDir = '/' . $this->_fileDir;
				}
				if (is_dir(SRA_Controller::getAppDir() . $this->_fileDir)) {
					$this->_fileDir = SRA_Controller::getAppDir() . $this->_fileDir;
				}
				else {
					$this->_fileDir = SRA_DIR . $this->_fileDir;
				}
			}
			$this->_fileDirUri = $conf['attributes']['file-dir-uri'];
			// no valid file handling method specified
			if (!$this->_fileHandling || ($this->_fileHandling != SRA_FILE_ATTRIBUTE_TYPE_DB && $this->_fileHandling != SRA_FILE_ATTRIBUTE_TYPE_DIR)) {
				$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid file handling: $this->_fileHandling (required) for entity " . $conf['attributes']['key'];
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
				return;
			}
			// validate db file handling
			else if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DB) {
				// script uri not specified
				if (!$this->_fileScriptUri) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid file script uri (required) for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				$keys = array_keys($this->_attributeGenerators);
				foreach ($keys as $key) {
					// invalid column type (must be blob for db files)
					if ($this->_attributeGenerators[$key]->_isFile && $this->_attributeGenerators[$key]->_type != SRA_DATA_TYPE_BLOB) {
						$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid column type (blob required) for entity " . $conf['attributes']['key'] . " and attribute ${key}";
						$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
						return;
					}
				}
			}
			// validate dir file handling
			else if ($this->_fileHandling == SRA_FILE_ATTRIBUTE_TYPE_DIR) {
				// dir not specified
				if (!$this->_fileDir) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid file dir (required) for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				else if (!is_writable($this->_fileDir)) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid file dir (not writable) for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				// dir uri not specified
				else if (!$this->_fileDirUri) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid file dir uri (required) for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
				$keys = array_keys($this->_attributeGenerators);
				foreach ($keys as $key) {
					// invalid column type (must be text for dir files)
					if ($this->_attributeGenerators[$key]->_isFile && $this->_attributeGenerators[$key]->_type != SRA_DATA_TYPE_STRING) {
						$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Invalid column type (text required) for entity " . $conf['attributes']['key'] . " and attribute ${key}";
						$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
						return;
					}
				}
			}
		}
    
		// check/initialize SRA_WSs
		if (isset($conf['ws'])) {
			$keys = array_keys($conf['ws']);
			foreach ($keys as $key) {
				$this->_webServices[$key] = new SRA_WS($conf['ws'][$key], $this->_name);
				if (!SRA_WS::isValid($this->_webServices[$key])) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - web service ${key} produced error for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
			}
		}
		
		// check/initialize SRA_ValidateGenerators
		$this->_validateGenerators = array();
		if (isset($conf['validate'])) {
			$keys = array_keys($conf['validate']);
			foreach ($keys as $key) {
				$this->_validateGenerators[$key] = new SRA_ValidateGenerator($conf['validate'][$key]);
				if (!SRA_ValidateGenerator::isValid($this->_validateGenerators[$key])) {
					$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Validation constraint ${key} produced error for entity " . $conf['attributes']['key'];
					$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
					return;
				}
			}
		}
		
		// check for conflicing types
		if (SRA_EntityModeler::isValidType($conf['attributes']['key'])) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - an entity cannot have the same name as a database type. entity: " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
    
		// merge aop aspects
    if (isset($conf['aop'][0]['advice'])) {
			if (!isset($conf['aop']['advice'])) $conf['aop']['advice'] = array();
      foreach (array_keys($conf['aop'][0]['advice']) as $key) $conf['aop']['advice'][$key] = $conf['aop'][0]['advice'][$key];
    }
    if (isset($conf['aop'][0]['aspect'])) {
			if (!isset($conf['aop']['aspect'])) $conf['aop']['aspect'] = array();
      foreach (array_keys($conf['aop'][0]['aspect']) as $key) $conf['aop']['aspect'][$key] = $conf['aop'][0]['aspect'][$key];
    }
    if (isset($conf['aop'][0]['introduction'])) {
			if (!isset($conf['aop']['introduction'])) $conf['aop']['introduction'] = array();
      foreach (array_keys($conf['aop'][0]['introduction']) as $key) $conf['aop']['introduction'][$key] = $conf['aop'][0]['introduction'][$key];
    }
		if (isset($conf['aop'][0])) unset($conf['aop'][0]);

    // add aop aspects
    if (isset($conf['aop']['aspect'])) {
      $keys = array_keys($conf['aop']['aspect']);
      foreach ($keys as $key) {
        $this->_aopAspects[$key] = new SRA_AopAspect($key, $conf['aop']);
        if (isset($this->_aopAspects[$key]->err)) {
          $msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Unable to instantiate aspect $key";
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
      }
    }

    // add aop introductions
    if (isset($conf['aop']['introduction'])) {
      $keys = array_keys($conf['aop']['introduction']);
      foreach ($keys as $key) {
        $this->_aopIntroductions[$key] = new SRA_AopIntroduction($key, $conf['aop']['introduction'][$key]);
        if (isset($this->_aopIntroductions[$key]->err)) {
          $msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Unable to instantiate introduction $key";
          $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
          return;
        }
      }
    }
		
		$this->_apiResource = $conf['attributes']['api-resource'];
		$this->_constraint = isset($conf['attributes']['constraint']) ? $conf['attributes']['constraint'] : NULL;
		$this->_db = $dbKey;
		$this->_dbRefIntegrity = $dbRefIntegrity;
		$this->_nestedQueriesOk = $nestedQueriesOk;
		$this->_generatePath = $generatePath;
    $this->_ignoreBadImport = isset($conf['attributes']['ignore-bad-import']) && $conf['attributes']['ignore-bad-import'] == '1';
    $this->_onCreateImport = isset($conf['attributes']['on-create-import']) ? $conf['attributes']['on-create-import'] : NULL;
		$this->_onDeleteUpdate = isset($conf['attributes']['on-delete-update']) ? $conf['attributes']['on-delete-update'] : NULL;
		$this->_orderBy = isset($conf['attributes']['order-by']) ? $conf['attributes']['order-by'] : NULL;
		$this->_propagateUpdate = isset($conf['attributes']['propagate-update']) && $conf['attributes']['propagate-update'] == '1';
    $this->_renderAppend = isset($conf['attributes']['render-append']) ? explode(' ', $conf['attributes']['render-append']) : array();
    $this->_renderExclude = isset($conf['attributes']['render-exclude']) ? explode(' ', $conf['attributes']['render-exclude']) : array();
    $this->_renderInclude = isset($conf['attributes']['render-include']) ? explode(' ', $conf['attributes']['render-include']) : array();
		$this->_resource = $this->_resource ? $this->_resource : $this->_name;
    $this->_skipWsdl = isset($conf['attributes']['skip-wsdl']) && $conf['attributes']['skip-wsdl'] == '1' ? TRUE : FALSE;
		$this->_types = $types;
		
		$this->_dao = isset($conf['attributes']['dao']) && trim($conf['attributes']['dao']) ? $conf['attributes']['dao'] : NULL;
		$this->_daoFile = isset($daoFile) ? $daoFile : NULL;
		$this->_daoExtends = isset($conf['attributes']['dao-extends']) ? $conf['attributes']['dao-extends'] : NULL;
		$this->_daoExtendsFile = isset($daoExtendsFile) ? $daoExtendsFile : NULL;
		$this->_voExtends = isset($conf['attributes']['vo-extends']) ? $conf['attributes']['vo-extends'] : NULL;
		$this->_voExtendsFile = isset($voExtendsFile) ? $voExtendsFile : NULL;
		
		// check for conflicting VO or DAO class names
		if ($this->_name . $this->_daoSuffix == $this->_daoExtends) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Generated DAO cannot have the same name as the extended DAO. entity: " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		if ($this->_name . $this->_voSuffix == $this->_voExtends) {
			$msg = "SRA_EntityGenerator::SRA_EntityGenerator: Failed - Generated DAO cannot have the same name as the extended VO. entity: " . $conf['attributes']['key'];
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
    
    // check for attributes not allowed with ws-db
    if ($this->usesWsDb()) {
      $err = NULL;
      if ($this->_columnPostfix) $err = 'column-postfix';
      else if ($this->_columnPrefix) $err = 'column-prefix';
      else if ($this->_constraint) $err = 'constraint';
      else if ($this->_nullDbEmptyStr) $err = 'null-db-empty-str';
      else if ($this->_onCreateImport) $err = 'on-create-import';
      else if ($this->_onDeleteUpdate) $err = 'on-delete-update';
      else if ($this->_orderBy) $err = 'order-by';
      else if ($this->_propagateUpdate) $err = 'propagate-update';
      else if (isset($conf['attributes']['table'])) $err = 'table';
      if ($err) {
        $msg = 'SRA_EntityGenerator::SRA_EntityGenerator: Failed - the entity attribute ' . $err . ' cannot be used in conjunction with ws-db for entity ' . $this->_name;
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
    }
    
	}
	// }}}
	
  
  // public operations
	
	// {{{ attributeGeneratorToColumn
	/**
	 * Used to convert an SRA_AttributeGenerator to a SRA_SchemaColumn object
	 * @param SRA_AttributeGenerator $generator the SRA_AttributeGenerator to convert
	 * @param SRA_SchemaTable $table the table that this column will belong to
	 * @param SRA_SchemaTable $baseTable the base table for the schema
   * @access  public
	 * @return SRA_SchemaColumn
	 */
	function &attributeGeneratorToColumn(& $generator, & $table, & $baseTable) {
		$column = new SRA_SchemaColumn(array($generator->_name), $generator->_column);
		$column->addReadConstraint($generator->_name, $generator->_constraint);
		$column->setDefault($generator->_default);
		$column->setLazyLoad($generator->_lazyLoad);
		$column->setLazyLoadExclusive($generator->_lazyLoadExclusive);
		$column->setCardinality($generator->_cardinality);
		$column->setCardinalityLower($generator->_cardinalityLower);
		$column->setCardinalityUpper($generator->_cardinalityUpper);
		$column->setColumnType($generator->_columnType);
		$column->setReadOnly($generator->_readOnly);
		$column->setRetrieveFunction($generator->_retrieveFunction);
		$column->setSequence($generator->_sequence);
		$column->setSetFunction($generator->_setFunction);
		$column->setSetOnly($generator->_setOnly);
		$column->setType($generator->_type);
		$column->setValidators($generator->_validators);
		$column->setVars($generator->_vars);
		
		// set foreign table criteria
		if ($table->_name != $baseTable->_name) {
			if ($generator->_orderBy) {
				$table->setOrderConstraint($generator->_orderBy);
			}
			if ($generator->_constraint) {
				$table->setSelectConstraint($generator->_constraint);
			}
		}
		return $column;
	}
	// }}}
  
  
	// {{{ attrIsPersistent
	/**
	 * returns TRUE if $attr is persistent
   * @param string $attr the attribute
   * @access public
	 * @return boolean
	 */
  function attrIsPersistent($attr) {
    return isset($this->_attributeGenerators[$attr]) && !$this->_entityGenerators[$this->_attributeGenerators[$attr]->_type]->_skipPersistence ? TRUE : FALSE;
  }
	// }}}
	
	
	// {{{ checkAttrs
	/**
	 * validates that all attr generators are valid
   * @access  public
	 * @return String
	 */
	function checkAttrs() {
		// check attribute generators
		$keys = array_keys($this->_attributeGenerators);
		foreach ($keys as $key) {
			if (!SRA_AttributeGenerator::isValid($this->_attributeGenerators[$key])) {
				unset($this->_attributeGenerators[$key]);
			}
		}
	}
	// }}}
  
  
	// {{{ excludeAttrFromRender
	/**
	 * Returns true if the $attr specified should be excluded from a render method 
   * invocation (json or xml)
   * @param string $attr the name of the attribute to check
   * @access  public
	 * @return String
	 */
  function excludeAttrFromRender($attr) {
    return in_array($attr, $this->_renderExclude) || ($this->_renderInclude && !in_array($attr, $this->_renderInclude));
  }
  // }}}
  
  
	// {{{ getResources
	/**
	 * returns the resources used by this entity
   * @access  public
	 * @return ResourceBundle
	 */
	function &getResources() { 
    if (!$this->_resourceBundles) {
      $this->_resourceBundles =& SRA_Controller::getAppResources();
      if ($this->_resources) {
        foreach ($this->_resources as $res) {
          $this->_resourceBundles =& SRA_ResourceBundle::merge($this->_resourceBundles, SRA_ResourceBundle::getBundle($res));
        }
      }
    }
    return $this->_resourceBundles;
	}
	// }}}
  
	
	// {{{ generate
	/**
	 * Used to generate this entity
	 * @param SRA_EntityGenerator[] $entityGenerators an array of all of the 
	 * SRA_EntityGenerator objects in the entity model
   * @access  public
	 * @return String
	 */
	function &generate() { 
    $tpl =& SRA_Controller::getAppTemplate();
    
		// set resources
		$tpl->assignByRef(SRA_APP_RB_NAME, $this->getResources());
		
		if (!$this->_skipPersistence && !$this->usesWsDb() && !$this->_customDao && ($schema =& $this->getSchema())) {
			$tpl->assignByRef('schema', $schema);
			$tpl->assignByRef('primaryTable', $schema->getPrimaryTable());
		}
		else if (!$this->_skipPersistence && !$this->usesWsDb() && !$this->_customDao) {
			$msg = "SRA_EntityGenerator::generate: Failed - Unable to retrieve schema for entity $this->_name";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		
		/* GENERATE VO */
		$this->checkAttrs();
		if (SRA_Error::isError($err =& $this->_generateVO())) {
			return $err;
		}
		
		/* GENERATE DAO */
		$this->checkAttrs();
		if (SRA_Error::isError($err =& $this->_generateDAO())) {
			return $err;
		}
		
	}
	// }}}
  
  
	// {{{ getAopAspects
	/**
	 * Returns all of the SRA_AopAspects for the specified class, method and 
   * when
   * @param string $classType the class to return the aspects for (one of the SRA_AOP_CLASS_* constants)
   * @param string $method the name of the method to return the aspects for (w/o parameters or parens)
   * @param string $when the aspect when flag. one of the SRA_AOP_ASPECT_WHEN_* constants
   * @access  public
	 * @return String
	 */
	function getAopAspects($classType, $method, $when) {
		$aspects = array();
    $keys = array_keys($this->_aopAspects);
    foreach ($keys as $key) {
      if ($this->_aopAspects[$key]->appliesTo($classType, $method, $when)) {
        $aspects[] = $this->_aopAspects[$key];
      }
    }
    return $aspects;
	}
	// }}}
  
  
	// {{{ getAopIntroductions
	/**
	 * Returns all of the SRA_AopIntroductions of the specified type
   * @param string $type a type limiter. if not specified, all introductions 
   * of any type will be returned. must correspond with one of the 
   * SRA_AOP_INTRODUCTION_TYPE_* constants
   * @param string $classType a class limiter. if not specified, all introductions 
   * for any class will be returned. must correspond with one of the 
   * SRA_AOP_CLASS_* constants
   * @access  public
	 * @return String
	 */
	function getAopIntroductions($type=FALSE, $classType=FALSE) {
		$introductions = array();
    $keys = array_keys($this->_aopIntroductions);
    foreach ($keys as $key) {
      if ($this->_aopIntroductions[$key]->appliesTo($type, $classType)) {
        $introductions[] = $this->_aopIntroductions[$key];
      }
    }
    return $introductions;
	}
	// }}}
  
  
	// {{{ getAttributes
	/**
	 * returns the attributes associated with this entity
   * @param string $attr the name of a specific attribute to return the 
   * generator for. if specified, the return value will be a single instance of 
   * SRA_AttributeGenerators
   * @access  public
	 * @return SRA_AttributeGenerators[]
	 */
	function &getAttributes($attr=NULL) {
    $keys = array_keys($this->_attributeGenerators);
    foreach($keys as $key) {
      if (!SRA_AttributeGenerator::isValid($this->_attributeGenerators[$key])) {
        unset($this->_attributeGenerators[$key]);
      }
    }
		return $attr ? $this->_attributeGenerators[$attr] : $this->_attributeGenerators;
	}
	// }}}
  
  
	// {{{ getTypesUsed
	/**
	 * returns all of the types used by this entity and its' corresponding 
   * attributes
   * @param boolean $complexOnly when true only complex (entity) types will be 
   * returned
   * @param array $stack used internally to avoid infinite recursion
   * @param boolean $recursive whether or not to invoke recursively
   * @param boolean $excludeNonIncludeAttrs whether or not to skip entity type 
   * attributes where the skip-include flag is true
   * @access  public
	 * @return string[]
	 */
	function getTypesUsed($complexOnly=FALSE, $stack=NULL, $recursive=TRUE, $excludeNonIncludeAttrs=FALSE) {
    $types = array();
    foreach(array_keys($this->_attributeGenerators) as $key) {
      if ($this->_attributeGenerators[$key] && (!$complexOnly || $this->_attributeGenerators[$key]->isEntity())) {
        
        if ($excludeNonIncludeAttrs && $this->_attributeGenerators[$key]->isEntity() && $this->_attributeGenerators[$key]->_skipInclude) { continue; }
        
        $types[$this->_attributeGenerators[$key]->_type] = TRUE;
        if ($recursive && $this->_attributeGenerators[$key]->isEntity() && (!$stack || !in_array($this->_attributeGenerators[$key]->_type, $stack))) {
          if (!$stack) { $stack = array(); }
          $stack[] = $this->_name;
          foreach($this->_entityGenerators[$this->_attributeGenerators[$key]->_type]->getTypesUsed($complexOnly, $stack) as $type) {
            $types[$type] = TRUE;
          }
        }
      }
    }
		return array_keys($types);
	}
	// }}}	
  
  
	// {{{ getAttrType
	/**
	 * returns the data (or entity) type for the attribute specified
	 * 
   * @param string $attr the attribute to return the type for
   * @access  public
	 * @return string
	 */
  function getAttrType($attr) {
    return isset($this->_attributeGenerators[$attr]) ? $this->_attributeGenerators[$attr]->_type : NULL;
  }
	// }}}
	
	
	// {{{ getDaoClassName
	/**
	 * Returns the DAO class name
   * @access  public
	 * @return String
	 */
	function getDaoClassName() {
		return $this->_name . $this->_daoSuffix;
	}
	// }}}
	
	
	// {{{ getDaoPath
	/**
	 * Returns the path to the DAO for this entity
   * @access  public
	 * @return String
	 */
	function getDaoPath() {
		return $this->_generatePath . '/' . $this->_name . $this->_daoSuffix . '.' . SRA_SYS_PHP_EXTENSION;
	}
	// }}}
  
  
	// {{{ getDtdSubElementName
	/**
	 * Returns the DTD name value for an entity sub-element
   * @param string $subName the sub-element name
   * @access public
	 * @return string
	 */
	function getDtdSubElementName($subName) {
		$name  = $this->_name . strtoupper(substr($subName, 0, 1)) . substr($subName, 1);
		if ($this->_dtdCamelCase) {
			return $name;
		}
		else {
			return SRA_Util::camelCaseToDashes($name);
		}
	}
	// }}}
  
  
	// {{{ getEntityGenerator
	/**
	 * Returns the SRA_EntityGenerator for the $entity specified
   * @param $entity the name of the entity to return the generator for
   * @access  public
	 * @return SRA_EntityGenerator
	 */
	function &getEntityGenerator($entity) {
		return $this->_entityGenerators[$entity];
	}
	// }}}
	
	
	// {{{ getTableAttributes
	/**
	 * Returns an array of all of the attribute generators that utilize the table 
	 * specified
   * @access  public
	 * @return SRA_AttributeGenerator[]
	 */
	function &getTableAttributes($table) {
		$generators = array();
		$keys = array_keys($this->_attributeGenerators);
		foreach($keys as $key) {
			if ($this->_attributeGenerators[$key]->_table == $table) {
				$generators[$key] =& $this->_attributeGenerators[$key];
			}
		}
		return $generators;
	}
	// }}}
	
	
	// {{{ getVoClassName
	/**
	 * Returns the VO class name for this entity
   * @access  public
	 * @return String
	 */
	function getVoClassName() {
		return $this->_name . $this->_voSuffix;
	}
	// }}}
	
	
	// {{{ getVoPath
	/**
	 * Returns the path to the VO for this entity
   * @access  public
	 * @return String
	 */
	function getVoPath() {
		return $this->_generatePath . '/' . $this->_name . $this->_voSuffix . '.' . SRA_SYS_PHP_EXTENSION;
	}
	// }}}
	
	// {{{ getIncludes
	/**
	 * Returns all of the includes for the entity
   * @access  public
	 * @return String[]
	 */
	function getIncludes() {
    $includes = array();
    $aopIntroductions =& $this->getAopIntroductions(SRA_AOP_INTRODUCTION_TYPE_INCLUDE, SRA_AOP_CLASS_VO);
    $keys = array_keys($aopIntroductions);
    foreach($keys as $key) {
      $includes[] = $aopIntroductions[$key]->getValue();
    }
    foreach($this->getTypesUsed(TRUE, NULL, FALSE, TRUE) as $type) {
      $includes[] = $type . $this->_voSuffix . '.' . SRA_SYS_PHP_EXTENSION;
    }
		return $includes;
	}
	// }}}
	
	// {{{ getPrimaryKeyAttribute
	/**
	 * Returns the primary key attribute for this entity
   * @access  public
	 * @return SRA_AttributeGenerator
	 */
	function &getPrimaryKeyAttribute() {
		return $this->_attributeGenerators[$this->_primaryKey];
	}
	// }}}
	
	
	// {{{ getSchema
	/**
	 * Returns an array of the names of all the attributes that should be tracked
	 * 
   * @access  public
	 * @return SRA_EntitySchema  or -1 if loading or FALSE if non-persistent
	 */
	function &getSchema() {
		// schema is currently loading, return -1 to indicate this
		if ($this->_schemaLoading) {
			$this->_schemaRecursionCount++;
			if ($this->_schemaRecursionCount == SRA_ENTITY_GENERATOR_MAX_SCHEMA_RECURSION) {
				$msg = "SRA_EntityGenerator::getSchema: Failed - Recursion limit SRA_ENTITY_GENERATOR_MAX_SCHEMA_RECURSION hit for entity $this->_name";
				SRA_Error::logError($msg, __FILE__, __LINE__);
				exit;
			}
			return -1;
		}
		// schema is already loaded, return
		else if (isset($this->_schema)) {
			return $this->_schema;
		}
		// non-persistent entity, no schema to return
		else if ($this->_skipPersistence || $this->_customDao || $this->_abstract) {
			return FALSE;
		}
		
		// load schema
		$this->_schemaLoading = TRUE;
		$this->_schema = new SRA_EntitySchema ($this->_name);
		$this->_schema->setDbRefIntegrity($this->_dbRefIntegrity);
		$this->_schema->setDdlCamelCase($this->_ddlCamelCase);
		$this->_schema->setDdlUpperCase($this->_ddlUpperCase);
		$this->_schema->setDtdCamelCase($this->_dtdCamelCase);
		$tables = array();
		$tables[$this->_table] = new SRA_SchemaTable($this->_table);
		$tables[$this->_table]->setDeleteConstraint($this->_onDeleteUpdate);
		$tables[$this->_table]->setOrderConstraint($this->_orderBy);
		$pk =& $this->getPrimaryKeyAttribute();
		$tables[$this->_table]->setPrimaryKey($pk->_column);
		$tables[$this->_table]->setPrimary(TRUE);
		$tables[$this->_table]->setSelectConstraint($this->_constraint);
		if ($this->_mysqlTableType) $tables[$this->_table]->setMysqlTableType($this->_mysqlTableType);
		$keys = array_keys($this->_attributeGenerators);
		while (count($keys)) {
			$temp = array_keys($keys);
			for($i=0; $i<count($temp); $i++) {
				$key = $keys[$temp[$i]];
				if ($this->_attributeGenerators[$key]->_skipPersistence || $this->_attributeGenerators[$key]->_customDao || !$key) {
					unset($keys[$temp[$i]]);
					continue;
				}
        // SRA_Error::logError("EVAL ATTR " . $this->_attributeGenerators[$key]->_name . " FOR ENTITY " . $this->_name, __FILE__, __LINE__);
				$atable = $this->_attributeGenerators[$key]->_table ? $this->_attributeGenerators[$key]->_table : $this->_table;
				if (!isset($tables[$atable])) {
					$tables[$atable] = new SRA_SchemaTable($atable);
					$pkCol = $pk->_column;
					if ($this->_attributeGenerators[$key]->_tablePkColumn) {
						$pkCol = $this->_attributeGenerators[$key]->_tablePkColumn;
					}
					
					if ($this->_attributeGenerators[$key]->_table && $this->_attributeGenerators[$key]->_table != $this->_table) {
						$column =& $this->attributeGeneratorToColumn($pk, $tables[$atable], $tables[$this->_table]);
						$column->setName($pkCol);
						$column->setReferences($this->_table . '(' . $pk->_column . ')');
						$column->setSequence(FALSE);
						if (!$this->_attributeGenerators[$key]->isEntity() && (!$this->_attributeGenerators[$key]->_onDeleteCascadeSet || ($this->_attributeGenerators[$key]->_onDeleteCascadeSet && $this->_attributeGenerators[$key]->_onDeleteCascade))) {
							$column->setOnDeleteCascade(TRUE);
						}
						$tables[$atable]->addColumn($column);
					}
          else {
            $tables[$atable]->setPrimaryKey($pkCol);
          }
				}
        // add attribute index
        if ($this->_attributeGenerators[$key]->_indexed && $this->_attributeGenerators[$key]->_column && (!$this->_attributeGenerators[$key]->_table || $this->_attributeGenerators[$key]->_table == $tables[$atable]->getName())) {
          $tables[$atable]->addIndex(new SRA_SchemaIndex($this->_attributeGenerators[$key]->_column, $tables[$atable]->getName() . '_' . $this->_attributeGenerators[$key]->_column . '_idx', $tables[$atable]->getName()));
        }
				// base scalar attribute
				if (!$this->_attributeGenerators[$key]->isEntity()) {
					$column =& $this->attributeGeneratorToColumn($this->_attributeGenerators[$key], $tables[$atable], $tables[$this->_table]);
          if (isset($tables[$atable]->_columns[$column->getName()])) { $tables[$atable]->_columns[$column->getName()]->setBaseAttribute($column->getBaseAttribute()); }
					$tables[$atable]->addColumn($column);
				}
				// entity attribute
				else {
					if (!count($mappings =& $this->_attributeGenerators[$key]->getMappedAttributes(TRUE))) {
						// handle attribute nesting
						if (count($this->_attributeGenerators[$key]->_attributeGenerators)) {
							$akeys = array_keys($this->_attributeGenerators[$key]->_attributeGenerators);
							foreach ($akeys as $akey) {
								$column =& $this->attributeGeneratorToColumn($this->_attributeGenerators[$key]->_attributeGenerators[$akey], $tables[$atable], $tables[$this->_table]);
								$sattributes = $column->getAttributes();
								$sakeys = array_keys($sattributes);
								foreach ($sakeys as $sakey) {
									$column->addAttribute($this->_attributeGenerators[$key]->_name . '_' . $sattributes[$sakey]);
								}
								$tables[$atable]->addColumn($column);
							}
						}
						
						// validate type and type
						if ($this->_attributeGenerators[$key]->_type && !isset($this->_entityGenerators[$this->_attributeGenerators[$key]->_type])) {
							$this->_schemaLoading = FALSE;
							$msg = "SRA_EntityGenerator::getSchema: Failed - Invalid _type for attribute ${key}: Type - " . $this->_attributeGenerators[$key]->_type;
							return SRA_Error::logError($msg, __FILE__, __LINE__);
						}
						
						// set primary key(s)
						
						// add schema for entity type attribute
						$typeKey = $this->_attributeGenerators[$key]->_type;
						if (isset($this->_entityGenerators[$typeKey])) {
							$typePkAttr =& $this->_entityGenerators[$typeKey]->getPrimaryKeyAttribute();
							$column =& $this->attributeGeneratorToColumn($this->_attributeGenerators[$key], $tables[$atable], $tables[$this->_table]);
							$column->updateAttributeName($this->_attributeGenerators[$key]->_name, $this->_attributeGenerators[$key]->_name . '_' . $typePkAttr->_name);
							$column->setType($typePkAttr->_type);
              $column->setValidators($typePkAttr->_validators);
              $column->setVars($typePkAttr->_vars);
							$column->setOnDeleteCascade($this->_attributeGenerators[$key]->_onDeleteCascade);
							$overwrite = FALSE;
							
							// foreign entity primary key in primary table
							if ((!$this->_attributeGenerators[$key]->_table || $this->_attributeGenerators[$key]->_table == $this->_table) && !$this->_attributeGenerators[$key]->_tablePkColumn && !$this->_attributeGenerators[$key]->_cardinality) {
								$column->setReferences($this->_entityGenerators[$typeKey]->_table . '(' . $typePkAttr->_column . ')');
							}
							// entity primary key in foreign entity primary table
							else if ((!$this->_attributeGenerators[$key]->_table || $this->_attributeGenerators[$key]->_table == $this->_entityGenerators[$typeKey]->_table) && ($this->_attributeGenerators[$key]->_tablePkColumn || $this->_attributeGenerators[$key]->_cardinality)) {
								$atable = $this->_entityGenerators[$typeKey]->_table;
								if (!isset($tables[$atable])) {
									$tables[$atable] = new SRA_SchemaTable($atable);
								}
								$column->updateAttributeName($this->_attributeGenerators[$key]->_name . '_' . $typePkAttr->_name, $this->_attributeGenerators[$key]->_name);
								$column->setBaseAttribute($pk->_name);
                $column->setName($this->_attributeGenerators[$key]->_tablePkColumn ? $this->_attributeGenerators[$key]->_tablePkColumn : $pk->_column);
                $column->setType($pk->_type);
                $column->setValidators($pk->_validators);
                $column->setVars($pk->_vars);
								$column->setReferences($this->_table . '(' . $pk->_column . ')');
                $overwrite = TRUE;
							}
							// foreign entity primary key in external table
							else if ($this->_attributeGenerators[$key]->_table && $this->_attributeGenerators[$key]->_table != $this->_table && $this->_attributeGenerators[$key]->_table != $this->_entityGenerators[$typeKey]->_table) {
								$column->setReferences($this->_entityGenerators[$typeKey]->_table . '(' . $typePkAttr->_column . ')');
								if ($this->_attributeGenerators[$key]->_ddlNameGenerated) {
									$column->setName($typePkAttr->_column);
								}
                $tables[$atable]->setPrimaryKey($this->_attributeGenerators[$key]->_tablePkColumn ? $this->_attributeGenerators[$key]->_tablePkColumn : $pk->_column);
                $tables[$atable]->addPrimaryKey($column->getName());
								$column->setOnDeleteCascade(TRUE);
								$overwrite = TRUE;
							}
							
              $oldColumn =& $tables[$atable]->_columns[$column->getName()];
							$tables[$atable]->addColumn($column, $overwrite);
              // restore base attribute name
              if ($oldColumn && $overwrite && $this->_attributeGenerators[$key]->_cardinality) { $tables[$atable]->_columns[$column->getName()]->setBaseAttribute($oldColumn->getBaseAttribute()); }
              if ($oldColumn && !$overwrite && !$this->_attributeGenerators[$key]->_cardinality) { $tables[$atable]->_columns[$column->getName()]->setBaseAttribute($column->getBaseAttribute()); }
						}
					}
					else {
						$mkeys = array_keys($mappings);
						foreach ($mkeys as $mkey) {
							$column =& $this->attributeGeneratorToColumn($mappings[$mkey], $tables[$atable], $tables[$this->_table]);
							$tables[$atable]->addColumn($column);
						}
					}
				}
				unset($keys[$temp[$i]]);
			}
		}
		$this->_schema->addTable($tables);
		$this->_schemaLoading = FALSE;
		//echo "SCHEMA FOR " . $this->_name . "\n";
		//print_r($this->_schema);
		return $this->_schema;
	}
	// }}}
  
  
	// {{{ getDtdAttributes
	/**
	 * Returns the attributes that should be attributes within this entity's DTD
   * @param boolean $includeSetOnly whether or not to include setOnly attributes
   * @access  public
	 * @return AttributeGenerator[]
	 */
	function &getDtdAttributes($includeSetOnly=FALSE) {
    if (!isset($this->_dtdAttributes) || $this->_lastDtdAttributesSetOnly != $includeSetOnly) {
      $this->_lastDtdAttributesSetOnly = $includeSetOnly;
      $this->_dtdAttributes = array();
      $subElements =& $this->getDtdElements($includeSetOnly);
      $keys = array_keys($this->_attributeGenerators);
      foreach ($keys as $key) {
        if (!$this->excludeAttrFromRender($key) && SRA_AttributeGenerator::isValid($this->_attributeGenerators[$key]) && !isset($subElements[$key]) && !$this->_attributeGenerators[$key]->_circularRef && ($includeSetOnly || !$this->_attributeGenerators[$key]->_setOnly)) {
          $this->_dtdAttributes[$key] =& $this->_attributeGenerators[$key];
        }
      }
    }
    return $this->_dtdAttributes;
	}
	// }}}
  
  
	// {{{ getDtdElements
	/**
	 * Returns the attributes that should be sub-elements within this entity's DTD
   * @param boolean $includeSetOnly whether or not to include setOnly attributes
   * @access  public
	 * @return AttributeGenerator[]
	 */
	function &getDtdElements($includeSetOnly=FALSE) {
    if (!isset($this->_dtdSubElements) || $this->_lastDtdElementsSetOnly != $includeSetOnly) {
      $this->_lastDtdElementsSetOnly = $includeSetOnly;
      $keys = array_keys($this->_attributeGenerators);
      foreach ($keys as $key) {
        if (!$this->excludeAttrFromRender($key) && SRA_AttributeGenerator::isValid($this->_attributeGenerators[$key]) && !$this->_attributeGenerators[$key]->_circularRef && ($includeSetOnly || !$this->_attributeGenerators[$key]->_setOnly) && !$this->_attributeGenerators[$key]->_xmlUseAttribute) {
          $this->_dtdSubElements[$key] =& $this->_attributeGenerators[$key];
        }
      }
    }
    return $this->_dtdSubElements;
	}
	// }}}
  
  
	// {{{ hasCardinality
	/**
	 * returns TRUE if the $attr specified is valid for this entity and has 
   * cardinality
	 * 
   * @param string $attr the attribute to check for
   * @access  public
	 * @return boolean
	 */
  function hasCardinality($attr) {
    return isset($this->_attributeGenerators[$attr]) && $this->_attributeGenerators[$attr]->_cardinality;
  }
	// }}}
  
  
	// {{{ hasAttribute
	/**
	 * returns TRUE if this entity contains the attribute $attr
	 * 
   * @param string $attr the attribute to check for
   * @access  public
	 * @return boolean
	 */
  function hasAttribute($attr) {
    return isset($this->_attributeGenerators[$attr]);
  }
	// }}}
  
  
	// {{{ isPrimaryTableAttr
	/**
	 * returns TRUE if $attr is a non-cardinality attribute stored in the primary 
   * entity table
   * @access  public
	 * @return boolean
	 */
	function isPrimaryTableAttr($attr) {
    $keys = array_keys($this->_attributeGenerators);
    foreach ($keys as $key) {
      if ($this->_attributeGenerators[$key]->_name == $attr && (!$this->_attributeGenerators[$key]->_table || $this->_attributeGenerators[$key]->_table == $this->_table) && (!$this->_attributeGenerators[$key]->_cardinality || !$this->_attributeGenerators[$key]->isEntity())) {
        return TRUE;
      }
    }
    return FALSE;
	}
	// }}}
  
  
	// {{{ isFile
	/**
	 * returns TRUE if the $attr specified is valid for this entity and is a file
	 * 
   * @param string $attr the attribute to check for
   * @access  public
	 * @return boolean
	 */
  function isFile($attr) {
    return isset($this->_attributeGenerators[$attr]) && $this->_attributeGenerators[$attr]->_isFile;
  }
	// }}}
  
  
	// {{{ usesFiles
	/**
	 * returns TRUE if this entity or any of its' sub-entities use file type 
   * attributes
   * @param array $stack used internally to avoid infinite recursion
   * @access  public
	 * @return boolean
	 */
	function usesFiles($stack=NULL) {
    foreach(array_keys($this->_attributeGenerators) as $key) {
      if ($this->_attributeGenerators[$key]->_isFile) {
        return TRUE;
      }
      else if ($this->_attributeGenerators[$key] && $this->_attributeGenerators[$key]->isEntity() && (!$stack || !in_array($this->_attributeGenerators[$key]->_type, $stack))) {
        if (!$stack) { $stack = array(); }
        $stack[] = $this->_name;
        
        if ($this->_entityGenerators[$this->_attributeGenerators[$key]->_type]->usesFiles($stack)) {
          return TRUE;
        }
      }
    }
		return FALSE;
	}
	// }}}
  
	// {{{ usesWsDb
	/**
	 * returns TRUE if this entity uses a web service db for persistence
   * @access  public
	 * @return boolean
	 */
  function usesWsDb() {
    return $this->_wsDb && $this->_wsDbName ? TRUE : FALSE;
  }
	// }}}
  
  
  // private operations
	// {{{ _generateDAO
	/**
	 * Used to generate the DAO for this entity
	 * @param file $fp the file pointer to write to, if not specified, a new fp 
	 * will be opened
   * @access  public
	 * @return void
	 */
	function &_generateDAO($fp = FALSE) {
		if (!$this->_skipPersistence && !$this->_customDao) {
			if (!$fp) {
				$closeFp = TRUE;
				$fileName = $this->getDaoPath();
				// attempt to create new file
				SRA_Util::printDebug("SRA_EntityGenerator::_generateDAO - Attempting to create DAO file ${fileName}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
				if ((file_exists($fileName) && !unlink($fileName)) || ($fp = fopen($fileName, 'w')) === FALSE) {
					$msg = "SRA_EntityGenerator::_generateDAO: Failed - Pointer to file ${fileName} could not be opened";
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
			$tpl =& SRA_Controller::getAppTemplate();
			$tpl->assignByRef('entity', $this);
			$tpl->assignByRef('className', $this->getDaoClassName());
			$tpl->assignByRef('voClassName', $this->getVoClassName());
			$includes = array($this->_name . $this->_voSuffix . '.' . SRA_SYS_PHP_EXTENSION);
			$tpl->assignByRef('includes', $includes);
			$tpl->assignByRef('primaryKey', $this->getPrimaryKeyAttribute());
			fwrite($fp, $tpl->fetch(dirname(realpath(__FILE__)) . ($this->usesWsDb() ? '/dao-ws.tpl' : '/dao.tpl')));
			if ($closeFp) {
				fclose($fp);
        chmod($fileName, 0666);
			}
			$daoClass = $this->_name . $this->_daoSuffix;
			$daoFile = $fileName;
		}
		else {
			$daoClass = $this->_dao;
			$daoFile = $this->_daoFile;
			SRA_Util::printDebug("SRA_EntityGenerator::_generateDAO - Skipping DAO for " . $this->_name . " because fixed DAO has been specified or entity is not persistent", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
		}
		if (!$this->_skipPersistence || $this->_dao) {
			SRA_DaoFactory::registerDAO($this->_name, $daoClass, $daoFile);
		}
	}
	// }}}
	
	// {{{ _generateVO
	/**
	 * Used to generate the VO for this entity
	 * @param file $fp the file pointer to write to, if not specified, a new fp 
	 * will be opened
   * @access  public
	 * @return String
	 */
	function &_generateVO($fp = FALSE) {
		if (!$fp) {
			$closeFp = TRUE;
			$fileName = $this->getVoPath();
			// attempt to create new file
			SRA_Util::printDebug("SRA_EntityGenerator::_generateVO - Attempting to create VO file ${fileName}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
			if ((file_exists($fileName) && !unlink($fileName)) || ($fp = fopen($fileName, 'w')) === FALSE) {
				$msg = "SRA_EntityGenerator::_generateVO: Failed - Pointer to file ${fileName} could not be opened";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
		}
		$tpl =& SRA_Controller::getAppTemplate();
		$tpl->assignByRef('entity', $this);
		$tpl->assignByRef('className', $this->getVoClassName());
		$tpl->assignByRef('primaryKey', $this->getPrimaryKeyAttribute());
		fwrite($fp, $tpl->fetch(dirname(realpath(__FILE__)) . '/vo.tpl'));
		if ($closeFp) {
			fclose($fp);
      chmod($fileName, 0666);
		}
	}
	// }}}
	
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_Generator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_entitygenerator');
	}
	// }}}
  
}
// }}}
?>
