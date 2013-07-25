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
require_once('model/SRA_DaoFactory.php');
require_once('model/SRA_EntityGenerator.php');
// }}}

// {{{ Constants
/**
 * the delimiter for scalar attributes with cardinality stored in the same table 
 * as the entity
 */
define('SRA_AGGREGATE_CARDINALITY_DELIM', '|#|');

/**
 * Constant that identifies a blob data type (large binary object)
 */
define('SRA_DATA_TYPE_BLOB', 'blob');

/**
 * Constant that identifies a boolean data type
 */
define('SRA_DATA_TYPE_BOOLEAN', 'boolean');

/**
 * Constant that identifies a date data type. This data type uses the 
 * SRA_GregorianDate object to encapsulate its value (time is ignored)
 */
define('SRA_DATA_TYPE_DATE', 'date');

/**
 * Constant that identifies the a floating point data type
 */
define('SRA_DATA_TYPE_FLOAT', 'float');

/**
 * Constant that identifies an integer data type
 */
define('SRA_DATA_TYPE_INT', 'int');

/**
 * Constant that identifies a text data type
 */
define('SRA_DATA_TYPE_STRING', 'string');

/**
 * Constant that identifies a timestamp data type. This data type uses the 
 * SRA_GregorianDate object to encapsulate its value (time is used)
 */
define('SRA_DATA_TYPE_TIME', 'time');

/**
 * Defines the default app relative location for generated DAOs and VOs
 */
define('SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR', SRA_DEFAULT_LIB_DIR . '/model');

/**
 * the dtd element name to assign to the file element
 */
define('SRA_ENTITY_MODELER_FILE_DTD_NAME', 'SraFile');

/**
 * the base entity dtd file
 */
define('SRA_ENTITY_MODELER_BASE_DTD_TPL', SRA_LIB_DIR . '/model/base-entity-dtd.tpl');

/**
 * template to describe an entity using dtd
 */
define('SRA_ENTITY_MODELER_ENTITY_DTD_TPL', SRA_LIB_DIR . '/model/entity-dtd.tpl');

/**
 * the key in the apps resource bundle containing the string identifying 
 * that an attribute may have one or more of the options specified for it each 
 * separated by a space
 */
define('SRA_ENTITY_MODELER_MULTIPLE_OPTIONS_API_RESOURCE', 'entity-model.multiple_options_separated_by_a_space');

/**
 * the default system error resource
 */
define('SRA_ENTITY_MODELER_DEFAULT_SYS_ERR_RESOURCE', 'error.sys');

/**
 * the memory limit to apply when generating a model
 */
define('SRA_ENTITY_MODELER_MEMORY_LIMIT', '128M');

/**
 * the template variable to assign the entity model path to
 */
define('SRA_ENTITY_MODELER_PATH_TPL_VAR', 'entityModelPath');

/**
 * the dtd element name to assign to the view resources element
 */
define('SRA_ENTITY_MODELER_VIEW_RESOURCES_DTD_NAME', 'ViewResources');

/**
 * the parameter type used to specify resource bundles for auto-inclusion into 
 * a view
 */
define('SRA_ENTITY_MODELER_VIEW_RESOURCES_PARAM_TYPE', 'view-resources');

/**
 * the dtd element name to assign to the view resources string element
 */
define('SRA_ENTITY_MODELER_VIEW_RESOURCES_STRING_DTD_NAME', 'ViewResourcesString');

/**
 * a semaphore id used to guarantee that only one process rebuilds the entity 
 * model at a time.
 */
define('SRA_ENTITY_MODELER_BUILD_LOCK', 12345);
// }}}

// {{{ SRA_EntityModeler
/**
 * Facade used to manage and initiate initialization of entity models
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_EntityModeler {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntityModeler
	/**
	 * Constructor - does nothing
   * @access  private
	 */
	function SRA_EntityModeler() {}
	// }}}
	
  
  // public operations
	
	// {{{ init
	/**
	 * Initializes the entity model for a given app
	 * @param string $appKey the identifier of the app to initialize
	 * @param mixed $entityModel the identifier or the entity model within the 
	 * "app-config" "use-entity-model" "key" attribute or the full path to the 
   * entity model xml file. alternatively, this parameter may be an array 
   * already pulled from xml defining the model
   * @param boolean $forceBuild whether or not to force the build to occur 
   * regardless of whether or not the xml cache file is update to date
   * @param boolean $skipClassGeneration whether or not to skip class generation 
   * (if TRUE only database synchronization will occur)
   * @access  public
	 * @return String
	 */
	function init($appKey, &$entityModel, $forceBuild=FALSE, $skipClassGeneration=FALSE, $resetDaos=FALSE) {
    if (!is_array($entityModel)) {
      $path = file_exists($entityModel) && SRA_Util::beginsWith($entityModel, '/') ? $entityModel : SRA_Controller::getAppConfAttr(array(SRA_ENTITY_MODELER_CONFIG_KEY, $entityModel, 'attributes', 'path'));
      if (!($conf = SRA_EntityModeler::findEntityModelXml($path))) {
        $msg = "SRA_EntityModeler::init: Failed - Unable to locate entity model ${entityModel} for app ${appKey}";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
		
      SRA_Util::printDebug("SRA_EntityModeler::init - Initializing app ${appKey}. Looking for entity model in " . $conf, SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
    }
    else {
      $data =& $entityModel['entity-model'];
    }
    
    if (function_exists('sem_get')) {
      $lock = sem_get(SRA_ENTITY_MODELER_BUILD_LOCK, 1);
      sem_acquire($lock);
		}
    
    // check if model is already being build
    if (!is_array($entityModel)) {
      $lockFile = SRA_Controller::getAppTmpDir() . '/.' . str_replace('/', '_', $conf) . '.lock';
      $curPid = file_exists($lockFile) ? SRA_File::toString($lockFile)*1 : NULL;
      if (file_exists($lockFile) && (!$curPid || SRA_Util::isProcessActive($curPid))) {
        $tpl =& SRA_Controller::getAppTemplate();
        $tpl->assign('lockFile', $lockFile);
        $tpl->assign('entityModel', str_replace('.xml', '', basename($conf)));
        $tpl->display(defined('SRA_CONSOLE') && SRA_CONSOLE ? 'sra-model-wait-console.tpl' : 'sra-model-wait.tpl');
        exit;
      }
    }
    
		if (is_array($entityModel) || !SRA_XmlParser::isCached($conf, TRUE) || $forceBuild) {
      if (!is_array($entityModel)) {
        $fp = fopen($lockFile, 'w');
        fwrite($fp, getmypid());
        fclose($fp);
        chmod($lockFile, 0666);
      }
      ini_set('memory_limit', SRA_ENTITY_MODELER_MEMORY_LIMIT);
      
      if ($resetDaos) {
        SRA_DaoFactory::resetDAOs();
      }
      
      if (!is_array($entityModel)) SRA_Controller::registerShutdownMethod($tmp = 'SRA_File', 'unlink', array($lockFile));
			$tpl =& SRA_Controller::getAppTemplate();
			$tpl->assignByRef(SRA_ENTITY_MODELER_PATH_TPL_VAR, $conf);
			
			SRA_Util::printDebug("SRA_EntityModeler::init - ${appKey} is NOT initialized", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
			if (!$data && SRA_Error::isError($data =& SRA_EntityModeler::getEntityModelData($conf))) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - Entity model '${conf}' could not be parsed.";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			// abstract
			if (isset($data['attributes']['abstract']) && $data['attributes']['abstract'] == '1') {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - Entity model '${conf}' is abstract. Abstract entity models can only be used with the 'import' element in an enclosing non-abstract 'entity-model'";
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			
			// get paths
			$generatePath = SRA_File::getRelativePath(SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR);
			if (array_key_exists('attributes', $data) && array_key_exists('generate-path', $data['attributes'])) {
				$generatePath = SRA_File::getRelativePath($data['attributes']['generate-path']);
			}

      // web services gateway uri
      $wsApiCssUri = isset($data['attributes']['ws-api-css-uri']) ? $data['attributes']['ws-api-css-uri'] : NULL;
      $wsGatewayRewrite = isset($data['attributes']['ws-gateway-rewrite']) && $data['attributes']['ws-gateway-rewrite'] == '1';
      $wsGatewaySkipAppId = isset($data['attributes']['ws-gateway-skip-app']) && $data['attributes']['ws-gateway-skip-app'] == '1';
      $wsGatewayUri = isset($data['attributes']['ws-gateway-uri']) ? $data['attributes']['ws-gateway-uri'] : NULL;
      $wsDb = isset($data['attributes']['ws-db']) ? $data['attributes']['ws-db'] : NULL;
      if ($wsDb && (!function_exists('curl_init') || !function_exists('json_decode'))) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = 'SRA_EntityModeler::init: Failed - curl and json extensions must be installed in order to utilize ws-db';
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      
			// global file handling attributes
			$fileDir = FALSE;
			$fileDirUri = FALSE;
			$fileHandling = SRA_FILE_ATTRIBUTE_TYPE_DB;
      $fileIconDir = NULL;
			$fileScriptUri = FALSE;
			$fileScriptRewrite = FALSE;
			if (array_key_exists('attributes', $data) && array_key_exists('file-dir', $data['attributes'])) {
				$fileDir = $data['attributes']['file-dir'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('file-dir-uri', $data['attributes'])) {
				$fileDirUri = $data['attributes']['file-dir-uri'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('file-handling', $data['attributes'])) {
				$fileHandling = $data['attributes']['file-handling'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('file-icon-dir', $data['attributes'])) {
				$fileIconDir = $data['attributes']['file-icon-dir'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('file-script-uri', $data['attributes'])) {
				$fileScriptUri = $data['attributes']['file-script-uri'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('file-script-rewrite', $data['attributes'])) {
				$fileScriptRewrite = $data['attributes']['file-script-rewrite'];
			}
      if (array_key_exists('attributes', $data) && array_key_exists('header-tpl', $data['attributes'])) {
				$tpl->assign('headerTemplate', $data['attributes']['header-tpl']);
			}
			$resources = FALSE;
			if (array_key_exists('attributes', $data) && array_key_exists('resources', $data['attributes'])) {
				$resources = $data['attributes']['resources'];
			}
			$sysErrResource = SRA_ENTITY_MODELER_DEFAULT_SYS_ERR_RESOURCE;
			if (array_key_exists('attributes', $data) && array_key_exists('sys-err-resource', $data['attributes'])) {
				$sysErrResource = $data['attributes']['sys-err-resource'];
			}
			// validate directories
			if (!is_dir($generatePath)) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - generatePath ${generatePath} is not valid";
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			if (!is_writable($generatePath)) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - generatePath ${generatePath} is not writable";
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
        
      // global web services
      $webServices = array();
      if (array_key_exists('ws-global', $data)) {
        require_once('model/SRA_WSGlobal.php');
        
        $keys = array_keys($data['ws-global']);
        foreach ($keys as $key) {
          $webServices[] = new SRA_WSGlobal($data['ws-global'][$key]);
          if (!SRA_WSGlobal::isValid($webServices[count($webServices) - 1])) {
            if (function_exists('sem_get')) sem_release($lock);
            $msg = "SRA_EntityModeler::init: Failed - global web service ${key} produced error";
            SRA_XmlParser::deleteCache($conf, TRUE);
            return SRA_Error::logError($msg, __FILE__, __LINE__);
          }
        }
      }
			
			// view processors
			$viewProcessors = array();
			if (array_key_exists('view-processor', $data)) {
				$keys = array_keys($data['view-processor']);
				foreach ($keys as $key) {
					$vpAttrs = $data['view-processor'][$key]['attributes'];
					$viewProcessors[$key] = new SRA_EntityViewProcessor($key, $vpAttrs['path'], $vpAttrs['args'], $vpAttrs['input-view'], $vpAttrs['output-file-path'], $vpAttrs['post-process-cmd'], $vpAttrs['pre-process-cmd']);
				}
			}
			
			// create entity generators
			SRA_Util::printDebug("SRA_EntityModeler::init - entity model loaded with: generate-path=${generatePath}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
			$keys = array_keys($data['entity']);
			if (!count($keys)) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - XML file '${conf}' does not contain any entity elements";
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
						
			$ddlCamelCase = FALSE;
			$ddlUpperCase = FALSE;
			$dtdCamelCase = FALSE;
			if (array_key_exists('attributes', $data) && array_key_exists('ddl-camel-case', $data['attributes'])) {
				$ddlCamelCase = $data['attributes']['ddl-camel-case'] == '1';
			}
			if (array_key_exists('attributes', $data) && array_key_exists('ddl-upper-case', $data['attributes'])) {
				$ddlUpperCase = $data['attributes']['ddl-upper-case'] == '1';
			}
			if (array_key_exists('attributes', $data) && array_key_exists('dtd-camel-case', $data['attributes'])) {
				$dtdCamelCase = $data['attributes']['dtd-camel-case'] == '1';
			}
			
			$ddlPath = FALSE;
			$dtdPath = FALSE;
      $dtdUri = FALSE;
			if (array_key_exists('attributes', $data) && array_key_exists('ddl-path', $data['attributes'])) {
				$ddlPath = $data['attributes']['ddl-path'];
			}
			if (array_key_exists('attributes', $data) && array_key_exists('dtd-path', $data['attributes'])) {
				$dtdPath = $data['attributes']['dtd-path'];
			}
      if (array_key_exists('attributes', $data) && array_key_exists('dtd-uri', $data['attributes'])) {
				$dtdUri = $data['attributes']['dtd-uri'];
			}
			if ($ddlPath) {
				if (!is_dir(dirname($ddlPath))) {
					if (substr($ddlPath, 0, 1) != '/') {
						$ddlPath = '/' . $ddlPath;
					}
					$ddlPath = SRA_Controller::getAppDir() . $ddlPath;
				}
				if ((file_exists($ddlPath) && !unlink($ddlPath)) || ($fp = fopen($ddlPath, 'w')) === FALSE) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - XML file '${conf}' contains an invalid ddl-path: $ddlPath";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
				fclose($fp);
        chmod($ddlPath, 0666);
			}
			if ($dtdPath) {
				if (!is_dir(dirname($dtdPath))) {
					if (substr($dtdPath, 0, 1) != '/') {
						$dtdPath = '/' . $dtdPath;
					}
					$dtdPath = SRA_Controller::getAppDir() . $dtdPath;
				}
				if ((file_exists($dtdPath) && !unlink($dtdPath)) || ($fp = fopen($dtdPath, 'w')) === FALSE) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - XML file '${conf}' contains an invalid dtd-path: $dtdPath";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
        fclose($fp);
        chmod($dtdPath, 0666);
			}
			
			$msgs = array();
			if (isset($data['msg'])) {
				$keys = array_keys($data['msg']);
				foreach ($keys as $key) {
					$msgs[$key] = $data['msg'][$key]['attributes']['resource'];
				}
			}
			
			$nestedQueriesOk = TRUE;
			if (isset($data['attributes']['nested-queries-ok'])) {
				$nestedQueriesOk = $data['attributes']['nested-queries-ok'] == '1';
			}
			
			// views
			if (SRA_Error::isError($globalViews =& SRA_Generator::getViews($data['global-views'][0]['view'], $viewProcessors))) {
        if (function_exists('sem_get')) sem_release($lock);
				$msg = "SRA_EntityModeler::init: Failed - Cannot instantiate global views for entity-model '${conf}'";
				SRA_XmlParser::deleteCache($conf, TRUE);
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			if (is_array($globalViews) && count($globalViews)) {
				SRA_EntityView::mergeExtends($globalViews, $globalViews);
			}
      // default views
      $defaultViews = NULL;
      if (isset($data['global-views'][0]['default-view']) && SRA_Error::isError($defaultViews = SRA_Generator::getDefaultViews($data['global-views'][0]['default-view'], $globalViews))) {
        if (function_exists('sem_get')) sem_release($lock);
        SRA_XmlParser::deleteCache($conf, TRUE);
        return $defaultViews;
      }
			
      $db = FALSE;
      if (SRA_Controller::hasAppDb()) {
        $db =& SRA_Controller::getAppDb();
      }
			
			// db referential integrity
			if ($db && (strtolower(get_class($db)) == 'sra_databasemysql' || strtolower(get_class($db)) == 'sra_databasesqlite')) {
				$dbRefIntegrity = isset($data['attributes']['ref-integrity']) && $data['attributes']['ref-integrity'] == 'db';
			}
			else if ($db) {
				$dbRefIntegrity = !isset($data['attributes']['ref-integrity']) || $data['attributes']['ref-integrity'] == 'db';
			}
			
			// db
			$dbKey = FALSE;
			if ($db && isset($data['attributes']['db'])) {
				$dbKey = $data['attributes']['db'];
				if (SRA_Error::isError($db =& SRA_Controller::getAppDb($dbKey))) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - Invalid db identifier '${dbKey}'";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
			
			static $entityGenerators = array();
			$keys = array_keys($data['entity']);
			// merge up attribute type resources
			foreach ($keys as $key) {
				$akeys = array_keys($data['entity'][$key]['attribute']);
				foreach ($akeys as $akey) {
					if (isset($data['entity'][$key]['attribute'][$akey]['attributes']['type']) && isset($data['entity'][$data['entity'][$key]['attribute'][$akey]['attributes']['type']]['attributes']['resources'])) {
						$baseResources = isset($data['entity'][$key]['attributes']['resources']) ? $data['entity'][$key]['attributes']['resources'] : $resources;
						$baseResources .= ' ' . $data['entity'][$data['entity'][$key]['attribute'][$akey]['attributes']['type']]['attributes']['resources'];
						$baseResources = trim($baseResources);
						$data['entity'][$key]['attributes']['resources'] = $baseResources;
					}
				}
			}
			
			$daoSuffix = isset($data['attributes']['dao-suffix']) ? $data['attributes']['dao-suffix'] : SRA_ENTITY_GENERATOR_DAO_SUFFIX;
			$voSuffix = isset($data['attributes']['vo-suffix']) ? $data['attributes']['vo-suffix'] : SRA_ENTITY_GENERATOR_VO_SUFFIX;
      
			// instantiate entity generators
			foreach ($keys as $key) {
        // entity generate is already cached
        if (isset($entityGenerators[$key]) && SRA_EntityGenerator::isValid($entityGenerators[$key])) continue;
        
				SRA_Util::printDebug("SRA_EntityModeler::init - Initializing entity ${key}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
				// extends
				if (isset($data['entity'][$key]['attributes']['extends']) && $data['entity'][$key]['attributes']['extends'] && !array_key_exists($data['entity'][$key]['attributes']['extends'], $data['entity'])) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - entity ${key} cannot extend non-existent entity " . $data['entity'][$key]['attributes']['extends'];
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
				else if (isset($data['entity'][$key]['attributes']['extends']) && $data['entity'][$key]['attributes']['extends']) {
          // abstract and skipPersistence attributes should not carry over into extending entity
          $entityAbstract = isset($data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['abstract']) && $data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['abstract'] == '1' ? '1' : '0';
          $entitySkipPersistence = isset($data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['skip-persistence']) && $data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['skip-persistence'] == '1' ? '1' : '0';
          unset($data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['abstract']);
          unset($data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['skip-persistence']);
          
          // web services are not included in extends
          if (isset($data['entity'][$data['entity'][$key]['attributes']['extends']]['ws'])) {
            $entityWebServices = $data['entity'][$data['entity'][$key]['attributes']['extends']]['ws'];
            unset($data['entity'][$data['entity'][$key]['attributes']['extends']]['ws']);
          }
          
					$am =& SRA_ArrayManager::merge($data['entity'][$key], $data['entity'][$data['entity'][$key]['attributes']['extends']]);
					
          // reset extended attributes
          $data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['abstract'] = $entityAbstract;
          $data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['skip-persistence'] = $entitySkipPersistence;
          // reset web services
          if ($entityWebServices) $data['entity'][$data['entity'][$key]['attributes']['extends']]['ws'] = $entityWebServices;
          
          $data['entity'][$key] =& $am->getData();
					$baseResources = isset($data['entity'][$key]['attributes']['resources']) ? $data['entity'][$key]['attributes']['resources'] : $resources;
					$baseResources = isset($data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['resources']) ? $baseResources  . ' ' . $data['entity'][$data['entity'][$key]['attributes']['extends']]['attributes']['resources'] : $baseResources;
					$baseResources = trim($baseResources);
					if ($baseResources) {
						$data['entity'][$key]['attributes']['resources'] = $baseResources;
					}
					SRA_Util::printDebug("SRA_EntityModeler::init - Merged entity ${key} with extended entity " . $data['entity'][$key]['attributes']['extends'], SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
				}

				if (!array_key_exists('file-dir', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-dir'] = $fileDir;
				}
				if (!array_key_exists('file-dir-uri', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-dir-uri'] = $fileDirUri;
				}
				if (!array_key_exists('file-handling', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-handling'] = $fileHandling;
				}
        if (!array_key_exists('file-icon-dir', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-icon-dir'] = $fileIconDir;
				}
				if (!array_key_exists('file-script-uri', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-script-uri'] = $fileScriptUri;
				}
				if (!array_key_exists('file-script-rewrite', $data['entity'][$key]['attributes'])) {
					$data['entity'][$key]['attributes']['file-script-rewrite'] = $fileScriptRewrite;
				}
				// add auto primary key attribute if applicable
				if (isset($data['attributes']['auto-pk']) && $data['attributes']['auto-pk'] && 
				    !isset($data['entity'][$key]['attributes']['primary-key']) && 
				    (!isset($data['entity'][$key]['attributes']['abstract']) || $data['entity'][$key]['attributes']['abstract'] != '1') && 
						(!isset($data['entity'][$key]['attributes']['skip-persistence']) || $data['entity'][$key]['attributes']['skip-persistence'] != '1')) {
					$pkName = str_replace('{$name}', strtolower(substr($data['entity'][$key]['attributes']['key'], 0, 1)) . substr($data['entity'][$key]['attributes']['key'], 1), $data['attributes']['auto-pk']);
					$data['entity'][$key]['attributes']['primary-key'] = $pkName;
					$data['entity'][$key]['attribute'][$pkName] = array('attributes' => array('key' => $pkName, 'sequence' => '1'));
				}
        
				if (!SRA_EntityGenerator::isValid($entityGenerators[$key] = new SRA_EntityGenerator($dbKey, isset($dbRefIntegrity) ? $dbRefIntegrity : FALSE, $ddlCamelCase, $ddlUpperCase, $dtdCamelCase, $msgs, $nestedQueriesOk, $generatePath, $keys, $conf, $resources, $sysErrResource, $data['entity'][$key], $viewProcessors, $globalViews, $defaultViews, $daoSuffix, $voSuffix, $wsDb))) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - Entity ${key} could not be initialzed";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
        $ajkeys = array_keys($entityGenerators[$key]->_webServices);
        foreach($ajkeys as $ajkey) {
          $webServices[] =& $entityGenerators[$key]->_webServices[$ajkey];
        }
			}
      
      // save web services
      if (isset($wsGatewayUri) && $webServices) {
        require_once('model/SRA_WSGateway.php');
        if (SRA_Error::isError(SRA_WSGateway::cacheServices($webServices, $wsGatewayUri, $wsGatewayRewrite, $wsGatewaySkipAppId, $wsApiCssUri))) {
          if (function_exists('sem_get')) sem_release($lock);
          $msg = "SRA_EntityModeler::init: Failed - Unable to cache web services";
          SRA_XmlParser::deleteCache($conf, TRUE);
          return SRA_Error::logError($msg, __FILE__, __LINE__);
        }
      }
      
      // set xml related template variables
      $pieces = explode('.', basename($dtdPath));
      $pieces = explode('_', $pieces[0]);
      $docType = $pieces[0];
      $tpl->assign('docType', $docType);
      $tpl->assign('dtdPath', $dtdPath);
      $tpl->assign('dtdUri', $dtdUri);
      $tpl->assign('fileElementName', $dtdCamelCase ? SRA_ENTITY_MODELER_FILE_DTD_NAME : SRA_Util::camelCaseToDashes(SRA_ENTITY_MODELER_FILE_DTD_NAME));
      $tpl->assign('viewResourcesElementName', $dtdCamelCase ? SRA_ENTITY_MODELER_VIEW_RESOURCES_DTD_NAME : SRA_Util::camelCaseToDashes(SRA_ENTITY_MODELER_VIEW_RESOURCES_DTD_NAME));
      $tpl->assign('viewResourcesStringElementName', $dtdCamelCase ? SRA_ENTITY_MODELER_VIEW_RESOURCES_STRING_DTD_NAME : SRA_Util::camelCaseToDashes(SRA_ENTITY_MODELER_VIEW_RESOURCES_STRING_DTD_NAME));
			
			// generate entities
      if (!$skipClassGeneration) {
        $keys = array_keys($entityGenerators);
        foreach ($keys as $key) {
          if ($entityGenerators[$key]->_abstract) { continue; }
          if (SRA_Error::isError($entityGenerators[$key]->setEntityGenerators($entityGenerators))) {
            if (function_exists('sem_get')) sem_release($lock);
            $msg = "SRA_EntityModeler::init: Failed - SRA_EntityGenerators could not be set for entity ${key}";
            SRA_XmlParser::deleteCache($conf, TRUE);
            return SRA_Error::logError($msg, __FILE__, __LINE__);
          }
        }
        foreach ($keys as $key) {
          if ($entityGenerators[$key]->_abstract) { continue; }
          $msg = "SRA_EntityModeler::init - Generating entity ${key}";
          SRA_Util::printDebug($msg, SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
          if (SRA_Error::isError($entityGenerators[$key]->generate())) {
            if (function_exists('sem_get')) sem_release($lock);
            $msg = "SRA_EntityModeler::init: Failed - Entity ${key} could not be generated";
            SRA_XmlParser::deleteCache($conf, TRUE);
            return SRA_Error::logError($msg, __FILE__, __LINE__);
          }
        }
      }
			
			// instantiate indexes
			$indexes = array();
      if (!isset($data['index'])) {
        $data['index'] = array();
      }
			$ikeys = array_keys($data['index']);
			foreach ($ikeys as $ikey) {
				if (!isset($data['index'][$ikey]['attributes']['columns']) || !isset($data['index'][$ikey]['attributes']['table'])) {
          if (function_exists('sem_get')) sem_release($lock);
					$msg = "SRA_EntityModeler::init: Failed - Index definition ${ikey} does not contain a columns or table attribute";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
				$index = new SRA_SchemaIndex($data['index'][$ikey]['attributes']['columns'], $ikey, $data['index'][$ikey]['attributes']['table']);
				if (isset($data['index'][$ikey]['attributes']['modifier'])) {
					$index->setModifier($data['index'][$ikey]['attributes']['modifier']);
				}
				if (isset($data['index'][$ikey]['attributes']['postfix'])) {
					$index->setModifier($data['index'][$ikey]['attributes']['postfix']);
				}
				if (!isset($indexes[$index->getTable()])) {
					$indexes[$index->getTable()] = array();
				}
				$indexes[$index->getTable()][] = $index;
			}
			
			// generate database schema
			$tables = array();
			foreach ($keys as $key) {
				if ($entityGenerators[$key]->_abstract || $entityGenerators[$key]->usesWsDb()) { continue; }
				$schema =& $entityGenerators[$key]->getSchema();
				if (SRA_EntitySchema ::isValid($schema)) {
					$etables =& $schema->getTables();
					$tkeys = array_keys($etables);
					foreach ($tkeys as $tkey) {
						if (!isset($tables[$etables[$tkey]->getName()])) {
							$tables[$etables[$tkey]->getName()] =& $etables[$tkey];
							if (isset($indexes[$etables[$tkey]->getName()])) {
								$tables[$etables[$tkey]->getName()]->addIndex($indexes[$etables[$tkey]->getName()]);
							}
						}
						else {
							$tables[$etables[$tkey]->getName()]->addColumn($etables[$tkey]->getColumns(), $etables[$tkey]->isPrimary());
							$tables[$etables[$tkey]->getName()]->addIndex($etables[$tkey]->getIndexes());
							if ($etables[$tkey]->isPrimary() && $etables[$tkey]->getPrimaryKey()) {
								$tables[$etables[$tkey]->getName()]->setPrimaryKey($etables[$tkey]->getPrimaryKey());
							}
							if (!$etables[$tkey]->isPrimary() && $etables[$tkey]->getPrimaryKey()) {
								$tables[$etables[$tkey]->getName()]->addPrimaryKey($etables[$tkey]->getPrimaryKey());
							}
						}
					}
				}
			}
			
			// synchronize database schema
			if (isset($data['attributes']['mysql-table-type'])) {
				$mysqlTableType = $data['attributes']['mysql-table-type'];
			}
			else if (isset($dbRefIntegrity) && $dbRefIntegrity) {
				$mysqlTableType = 'InnoDB';
			}
			else {
				$mysqlTableType = 'MyISAM';
			}
      if (count($tables) && !$db) {
        if (function_exists('sem_get')) sem_release($lock);
        $msg = 'SRA_EntityModeler::init: Failed - Persistence cannot be enabled if no database has been defined';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
			else if (count($tables) && $data['attributes']['abstract'] != '1' && isset($data['attributes']['sync-schema']) && $data['attributes']['sync-schema'] == '1') {
				$tkeys = array_keys($tables);
        // determine synchronization ordering where tables that reference others are synchronized after those tables that they reference
        $references = array();
				foreach ($tkeys as $tkey) {
					$columns =& $tables[$tkey]->getColumns();
          $ckeys = array_keys($columns);
          foreach($ckeys as $ckey) {
            if (is_object($columns[$ckey])) {
              if ($columns[$ckey]->getReferences()) {
                if (!isset($references[$tkey])) {
                  $references[$tkey] = array();
                }
                $references[$tkey][] = $columns[$ckey]->getReferences();
              }
            }
            else {
              unset($columns[$ckey]);
            }
          }
				}
        $tableOrder = array();
        $tableKeys = array_keys($tables);
        // arrange table creation in correct order when db referential integrity is used
        if ($dbRefIntegrity) {
          $count = 0;
          while(count($tableKeys)) {
            foreach(array_keys($tableKeys) as $tableKey) {
              $tk = $tableKeys[$tableKey];
              if (isset($references[$tk])) {
                foreach(array_keys($references[$tk]) as $ref) {
                  $refTable = explode('(', $references[$tk][$ref]);
                  $refTable = $refTable[0];
                  if (in_array($refTable, $tableOrder)  || $refTable == $tk) {
                    unset($references[$tk][$ref]);
                  }
                }
                if (!count($references[$tk])) {
                  unset($references[$tk]);
                }
              }
              if (!isset($references[$tk]) && !in_array($tk, $tableOrder)) {
                $tableOrder[] = $tk;
                unset($tableKeys[$tableKey]);
              }
            }
            if ($count == 100) {
              if (function_exists('sem_get')) sem_release($lock);
              $msg = 'SRA_EntityModeler::init: Failed - Could not resolve relational depedencies to create table synchronization order. Un-resolved dependencies include: ' . implode(', ', $tableKeys);
              SRA_XmlParser::deleteCache($conf, TRUE);
              return SRA_Error::logError($msg, __FILE__, __LINE__);
            }
            $count++;
          }
        }
        // order doesn't matter because db does not enforce referential integrity
        else {
          $tableOrder = $tableKeys;
        }
        
        $imports = array();
        $keys = array_keys($entityGenerators);
				foreach ($tableOrder as $tkey) {
					if (!($ret = SRA_EntityModeler::_synchronizeTable($db, $tables[$tkey], $dbRefIntegrity, $mysqlTableType))) {
            if (function_exists('sem_get')) sem_release($lock);
						$msg = 'SRA_EntityModeler::init: Failed - Could not synchronize table: ' . $tables[$tkey]->getName();
						SRA_XmlParser::deleteCache($conf, TRUE);
						return SRA_Error::logError($msg, __FILE__, __LINE__);
					}
          // table was created, check for on-create-import
          else if ($ret === 1) {
            foreach ($keys as $key) {
              if ($entityGenerators[$key]->_table == $tables[$tkey]->getName() && $entityGenerators[$key]->_onCreateImport) {
                $files = explode(' ', $entityGenerators[$key]->_onCreateImport);
                foreach ($files as $import) {
                  if ($file = SRA_File::getRelativePath('etc', $import)) {
                    $imports[] = $file;
                  }
                  else if (!$entityGenerators[$key]->_ignoreBadImport) {
                    $msg = "SRA_EntityModeler::init: Error - Invalid import ${import}";
                    SRA_Error::logError($msg, __FILE__, __LINE__);
                  }
                }
              }
            }
          }
				}
        // process imports
        foreach($imports as $import) {
          $pieces = file($import);
          foreach ($pieces as $query) {
            $query = str_replace("\n", '', $query);
            if (trim($query) && substr($query, 0, 2) != '/*' && SRA_Error::isError($db->execute($query))) {
              $msg = "SRA_EntityModeler::init: Error - Invalid query ${query} in import ${import}";
              SRA_Error::logError($msg, __FILE__, __LINE__);
            }
          }
        }
			}
			
			// write ddl to file
			if ($db && count($tables) && $ddlPath) {
        require_once('sql/SRA_DatabaseMySql.php');
        
				$tpl->assign('isMysql', SRA_DatabaseMySql::isValid($db));
				$tpl->assign('mysqlTableType', $mysqlTableType);
				$tpl->assign('dbRefIntegrity', $dbRefIntegrity);
				$tpl->assignByRef('tables', $tables);
				$tpl->assignByRef('db', $db);
				$fp = fopen($ddlPath, 'w');
				$buffer =& $tpl->fetch(dirname(realpath(__FILE__)) . '/ddl-header.tpl');
				$keys = array_keys($tables);
				foreach ($keys as $key) {
					$tpl->assignByRef('table', $tables[$key]);
					$buffer .= $tpl->fetch(dirname(realpath(__FILE__)) . '/table.tpl');
					$buffer = str_replace(':#:', ';', $buffer);
				}
				fwrite($fp, $buffer);
				fclose($fp);
			}
			
			// write dtd to file
			if ($dtdPath) {
        $fp = fopen($dtdPath, 'w');
        $keys = array_keys($entityGenerators);
        $entityElements = array();
        foreach ($keys as $key) {
          if (!SRA_EntityGenerator::isValid($entityGenerators[$key]) || $entityGenerators[$key]->_abstract) { continue; }
          $entityElements[] = $entityGenerators[$key]->getDtdName();
        }
        $tpl->assignByRef('entityElements', $entityElements);
        fwrite($fp, $tpl->fetch(SRA_ENTITY_MODELER_BASE_DTD_TPL));
        foreach ($keys as $key) {
          if (!SRA_EntityGenerator::isValid($entityGenerators[$key]) || $entityGenerators[$key]->_abstract) { continue; }
          $tpl->assignByRef('entity', $entityGenerators[$key]);
          $tpl->assignByRef(SRA_APP_RB_NAME, $entityGenerators[$key]->getResources());
          fwrite($fp, $tpl->fetch(SRA_ENTITY_MODELER_ENTITY_DTD_TPL));
        }
        fclose($fp);
			}
			
      if (!is_array($entityModel)) SRA_File::unlink($lockFile);
		}
		else {
			SRA_Util::printDebug("SRA_EntityModeler::init - ${appKey} is already initialized", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
		}
		if (function_exists('sem_get')) sem_release($lock);
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
	
	// {{{ findEntityModelXml
	/**
	 * Returns the path to an entity model xml file based on the $conf specified. 
	 * the search order is defined in the SRA_File::getRelativePath() api where 
   * $dir is "conf". returns NULL if $conf is not valid
	 * @param string $conf the fixed or relative identifier for the entity model
   * @access public
	 * @return mixed
	 */
	function findEntityModelXml($conf) {
		if (!preg_match('/\.xml$/i', $conf)) $conf .= '.xml';
		if (!file_exists($xml = $conf)) $xml = SRA_Controller::getAppConfDir() . '/' . $conf;
		if (!file_exists($xml)) $xml = SRA_Controller::getAppDir() . '/' . $conf;
		if (!file_exists($xml)) $xml = SRA_CONF_DIR . '/' . $conf;
		if (!file_exists($xml)) $xml = SRA_DIR . '/' . $conf;
    return file_exists($xml) ? $xml : NULL;
	}
	// }}}
	
	// {{{ getEntityModelData
	/**
	 * Returns the data associated with an entity model based on the $conf 
	 * specified. the search order is defined in the SRA_File::getRelativePath() api
	 * where $dir is "conf"
	 * 
	 * @param string $conf the fixed or relative identifier for the entity model
   * @param boolean $preserveAbstract whether or not to preserve the abstract flag 
   * in the $data
   * @param boolean $ignoreBad whether or not to ignore invalid $conf params. by 
   * default if a $conf is not valid (i.e. invalid path) and error will be logged 
   * and returned
   * @access  public
	 * @return array or SRA_Error
	 */
	function & getEntityModelData($conf, $preserveAbstract = TRUE, $ignoreBad=FALSE) {
		$tmp = $conf;
		if (!$conf = SRA_EntityModeler::findEntityModelXml($conf)) {
			$msg = "SRA_EntityModeler::getEntityModelData: Failed - Unable to locate entity model ${tmp}";
			return $ignoreBad ? NULL : SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		if (SRA_Error::isError($xmlParser =& SRA_XmlParser::getXmlParser($conf, TRUE))) {
			$msg = "SRA_EntityModeler::getEntityModelData: Failed - XML file '${conf}' could not be parsed.";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$data =& $xmlParser->getData('entity-model');
		
		// specify abstract entities
		if (isset($data['attributes']['abstract']) && $data['attributes']['abstract'] == '1') {
			$keys = array_keys($data['entity']);
			foreach ($keys as $key) {
				$data['entity'][$key]['attributes']['abstract'] = '1';
			}
		}
		
		// abstract
		if (!$preserveAbstract) {
			unset($data['attributes']['abstract']);
		}
		
		// import
		if (isset($data['attributes']['import'])) {
			$imports = array();
			$keys = explode(' ', $data['attributes']['import']);
			foreach ($keys as $key) {
				if (SRA_Error::isError($imports[$key] =& SRA_EntityModeler::getEntityModelData($key, FALSE, isset($data['attributes']['ignore-bad-import']) && $data['attributes']['ignore-bad-import'] == '1'))) {
					$msg = "SRA_EntityModeler::getEntityModelData: Failed - Unable to parse import entity model ${key}";
					SRA_XmlParser::deleteCache($conf, TRUE);
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
        if ($imports[$key]) {
          $am =& SRA_ArrayManager::merge($data, $imports[$key]);
          if (isset($imports[$key]['attributes']['resources']) && $data['attributes']['resources'] != $imports[$key]['attributes']['resources']) {
            $data['attributes']['resources'] .= ' ' . $imports[$key]['attributes']['resources'];
          }
        }
			}
			$data =& $am->getData();
		}
		
		return $data;
	}
	// }}}
  
  
	// {{{ getAppEntityClasses
	/**
	 * returns a hash of all of the entity class names specified in $entityModelId 
   * for the current active app. the hash will be indexed by the name of the 
   * generated PHP source file containing that class. returns NULL for no 
   * entities. the returned hash will have the same order as 'getAppEntityIds' 
   * (i.e. index 0 in 'getAppEntityIds' is the same as index 0 in 
   * 'getAppEntityClasses' - although the latter is a hash)
   * @param boolean $classKey when TRUE, the key in the returned associative 
   * array will be  the classname and the value will be the path
	 * @param mixed $entityModel the id or ids (an array) of the entity model. if 
   * not specified, the return value will contain all of the entities for all of
   * the entity models defined for the current application
	 * @access public static
	 * @return hash
	 */
	function getAppEntityClasses($classKey=FALSE, $entityModel=NULL) {
    $entityModels = $entityModel ? (is_array($entityModel) ? $entityModel : array($entityModel)) : SRA_EntityModeler::getAppEntityModels();
    $entityClasses = array();
    foreach($entityModels as $entityModel) {
      if (!$conf = SRA_EntityModeler::findEntityModelXml(SRA_Controller::getAppConfAttr(array(SRA_ENTITY_MODELER_CONFIG_KEY, $entityModel, 'attributes', 'path')))) {
        $msg = "SRA_EntityModeler::getAppEntityModelEntityIds: Failed - Unable to locate entity model ${entityModel} for app ${appKey}";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      if (SRA_Error::isError($data =& SRA_EntityModeler::getEntityModelData($conf))) {
        $msg = "SRA_EntityModeler::getAppEntityModelEntityIds: Failed - Entity model '${conf}' could not be parsed.";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      
      $voSuffix = isset($data['attributes']['vo-suffix']) ? $data['attributes']['vo-suffix'] : SRA_ENTITY_GENERATOR_VO_SUFFIX;
			$generatePath = SRA_File::getRelativePath(array_key_exists('attributes', $data) && array_key_exists('generate-path', $data['attributes']) ? $data['attributes']['generate-path'] : SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR);
      
      $keys = array_keys($data['entity']);
      foreach ($keys as $key) {
        $name = $key . $voSuffix;
        $path = $generatePath . '/' . $name . '.' . SRA_SYS_PHP_EXTENSION;
        $classKey ? $entityClasses[$name] = $path : $entityClasses[$path] = $name;
      }
    }
		return $entityClasses ? $entityClasses : NULL;
	}
	// }}}
  
  
	// {{{ getAppEntityClass
	/**
	 * returns the name of the class for $entityId
	 * @param string $entityId the id (key="SomeEntity") of the entity to return 
   * the classname for
	 * @access public static
	 * @return string
	 */
	function getAppEntityClass($entityId) {
    if (in_array($entityId, $entityIds = SRA_EntityModeler::getAppEntityIds())) {
      $entityClasses = SRA_EntityModeler::getAppEntityClasses();
      $keys = array_keys($entityClasses);
      return $entityClasses[$keys[array_search($entityId, $entityIds)]];
    }
    else if ($entityId) {
      return str_replace('.' . SRA_SYS_PHP_EXTENSION, '', basename($entityId));
    }
	}
	// }}}
	
	
	// {{{ getAppEntityIds
	/**
	 * returns an array of all of the entity ids specified in $entityModelId for 
   * the current active app. returns NULL for no entities
	 * @param mixed $entityModel the id or ids (an array) of the entity model. if 
   * not specified, the return value will contain all of the entity ids for all 
   * of the entity models defined for the current application
	 * @access public static
	 * @return string[]
	 */
	function getAppEntityIds($entityModel=NULL) {
    $entityModels = $entityModel ? (is_array($entityModel) ? $entityModel : array($entityModel)) : SRA_EntityModeler::getAppEntityModels();
    $entityIds = array();
    foreach($entityModels as $entityModel) {
			$path = SRA_Controller::getAppConfAttr(array(SRA_ENTITY_MODELER_CONFIG_KEY, $entityModel, 'attributes', 'path'));
      if (!($conf = SRA_EntityModeler::findEntityModelXml($path))) {
        $msg = "SRA_EntityModeler::getAppEntityModelEntityIds: Failed - Unable to locate entity model ${entityModel} for app ${appKey}";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      if (SRA_Error::isError($data =& SRA_EntityModeler::getEntityModelData($conf))) {
        $msg = "SRA_EntityModeler::getAppEntityModelEntityIds: Failed - Entity model '${conf}' could not be parsed.";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      $keys = array_keys($data['entity']);
      foreach ($keys as $key) {
        $entityIds[] = $key;
      }
    }
		return $entityIds ? $entityIds : NULL;
	}
	// }}}
  
  
	// {{{ getAppEntityModels
	/**
	 * returns an array representing the ids of all of the entity models defined 
   * for the current application. returns NULL if no entity models are defined 
	 * @access public static
	 * @return string[]
	 */
	function getAppEntityModels() {
		$entityModelIds = $entityModelIds;
		if (SRA_Controller::getAppConfAttr(SRA_ENTITY_MODELER_CONFIG_KEY)) {
			$entityModelIds = array_keys(SRA_Controller::getAppConfAttr(SRA_ENTITY_MODELER_CONFIG_KEY));
		}
		return $entityModelIds;
	}
	// }}}
  
  
	// {{{ getAppEntityPath
	/**
	 * returns the path to the file containing the class for $entityId
	 * @param string $entityId the id (key="SomeEntity") of the entity to return 
   * the path for
	 * @access public static
	 * @return string
	 */
	function getAppEntityPath($entityId) {
    if (in_array($entityId, $entityIds = SRA_EntityModeler::getAppEntityIds())) {
      $paths = array_keys(SRA_EntityModeler::getAppEntityClasses());
      return $paths[array_search($entityId, $entityIds)];
    }
    else {
      $file = str_replace('.' . SRA_SYS_PHP_EXTENSION, '', basename($entityId));
      if ($files = SRA_File::getFileList(SRA_Controller::getAppLibDir(), $file . '.' . SRA_SYS_PHP_EXTENSION, TRUE)) {
        return $files[0];
      }
    }
	}
	// }}}
  
  
	// {{{ getAppEntityUnitTests
	/**
	 * returns a hash of all of the entity unit tests specified in $entityModelId 
   * where the key is the name of the entity and the value is the path to the 
   * unit test
	 * @param mixed $entityModel the id or ids (an array) of the entity model. if 
   * not specified, the return value will contain all of the entities for all of
   * the entity models defined for the current application
	 * @access public static
	 * @return hash
	 */
	function getAppEntityUnitTests($entityModel=NULL) {
    $entityModels = $entityModel ? (is_array($entityModel) ? $entityModel : array($entityModel)) : SRA_EntityModeler::getAppEntityModels();
    $unitTests = array();
    foreach($entityModels as $entityModel) {
      if (!$conf = SRA_EntityModeler::findEntityModelXml(SRA_Controller::getAppConfAttr(array(SRA_ENTITY_MODELER_CONFIG_KEY, $entityModel, 'attributes', 'path')))) {
        $msg = "SRA_EntityModeler::getAppEntityUnitTests: Failed - Unable to locate entity model ${entityModel} for app ${appKey}";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      if (SRA_Error::isError($data =& SRA_EntityModeler::getEntityModelData($conf))) {
        $msg = "SRA_EntityModeler::getAppEntityUnitTests: Failed - Entity model '${conf}' could not be parsed.";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      
      $voSuffix = isset($data['attributes']['vo-suffix']) ? $data['attributes']['vo-suffix'] : SRA_ENTITY_GENERATOR_VO_SUFFIX;
      
      foreach (array_keys($data['entity']) as $key) {
        if (isset($data['entity'][$key]['attributes']['unit-test'])) {
          $unitTest = $data['entity'][$key]['attributes']['unit-test'];
          if (!SRA_Util::endsWith($unitTest, '.' . SRA_SYS_PHP_EXTENSION)) $unitTest .= '.' . SRA_SYS_PHP_EXTENSION;
          $unitTests[$key . $voSuffix] = file_exists($unitTest) ? $unitTest : SRA_Controller::getAppLibDir() . '/' . $unitTest;
        }
      }
    }
		return $unitTests ? $unitTests : NULL;
	}
	// }}}
  
  
	// {{{ isAppEntityClassValid
	/**
	 * returns TRUE if $entityClass is a valid class for an entity for the current 
   * application
	 * @param string $entityClass the classname of the entity to validate
	 * @access public static
	 * @return boolean
	 */
	function isAppEntityClassValid($entityClass) {
    return in_array($entityClass, SRA_EntityModeler::getAppEntityClasses());
	}
	// }}}
  
  
	// {{{ isAppEntityIdValid
	/**
	 * returns TRUE if $entityId is a valid id of an entity for the current 
   * application
	 * @param string $entityId the id (key="SomeEntity") of the entity to validate
	 * @access public static
	 * @return boolean
	 */
	function isAppEntityIdValid($entityId) {
    return in_array($entityId, SRA_EntityModeler::getAppEntityIds());
	}
	// }}}
	
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_EntityModeler object.
	 * @param object $object the object to validate
	 * @access public  static
	 * @return boolean
	 */
	function isValid(&$object) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_entitymodeler');
	}
	// }}}
  
  // {{{ isValidType
  /**
   * Static method that returns TRUE if the object parameter references a valid 
   * data type
   * @param string $type the type to validate
   * @access public
   * @return boolean
   */
  function isValidType($type) {
    return ($type == SRA_DATA_TYPE_BLOB || 
            $type == SRA_DATA_TYPE_BOOLEAN || $type == SRA_DATA_TYPE_DATE || 
            $type == SRA_DATA_TYPE_TIME || $type == SRA_DATA_TYPE_FLOAT || 
            $type == SRA_DATA_TYPE_INT || $type == SRA_DATA_TYPE_STRING);
  }
  // }}}
	
  
  // private operations
	
	// {{{ _synchronizeTable
	/**
	 * used to synchronize a single table against in the database. if the table 
	 * specified does not contain any records (SELECT count(*) returns 0), it will 
	 * be dropped and re-created. if it does not exist, it will be created. if it 
	 * exists and contains records, each column will be checked and created if it 
	 * does not already exist. returns: FALSE on failure, TRUE on success and 1 if 
   * the table did not exist and was created as a result
	 *
	 * @param SRA_Database $db the database connection to use
	 * @param SRA_SchemaTable $table the table to synchronize
	 * @param boolean $dbRefIntegrity whether or not referential integrity should 
	 * be enforced at the database layer
	 * @param string $mysqlTableType the table type for mysql databases only
	 * @access	public
	 * @return	boolean
	 */
	function _synchronizeTable(& $db, & $table, $dbRefIntegrity, $mysqlTableType=FALSE) {
    require_once('sql/SRA_DatabaseMySql.php');
    
		$tpl =& SRA_Controller::getAppTemplate();
		$tpl->assign('isMysql', SRA_DatabaseMySql::isValid($db));
		$tpl->assign('mysqlTableType', $mysqlTableType);
		$tpl->assign('dbRefIntegrity', $dbRefIntegrity);
		$tpl->assignByRef('db', $db);
		
		$query = 'SELECT count(*) FROM ' . $table->getName();
		$results =& $db->fetch($query, array(SRA_DATA_TYPE_INT), FALSE, FALSE, SRA_ERROR_OPERATIONAL);
		$row = FALSE;
		if (SRA_ResultSet::isValid($results)) {
			$row =& $results->next();
		}
		
    $created = FALSE;
		$err = FALSE;
		// table does not exist or has no records, re-create
		if (SRA_Error::isError($results) || $row[0] == 0) {
      if (SRA_Error::isError($results)) {
        $created = TRUE;
      }
			$query = 'DROP TABLE ' . $table->getName() . ($dbRefIntegrity ? ' CASCADE CONSTRAINTS' : '');
			if (!SRA_Error::isError($results) && SRA_Error::isError($r =& $db->execute($query))) {
			  $msg = "SRA_EntityModeler::_synchronizeTable: Failed - Unable to drop existing table " . $table->getName() . " using query $query";
        SRA_Error::logError($msg, __FILE__, __LINE__);
				$err = TRUE;
			}
			else {
				$tpl->assignByRef('table', $table);
				$queries = explode(';', $tpl->fetch(dirname(realpath(__FILE__)) . '/table.tpl'));
				foreach ($queries as $query) {
				  // add semi-colons
				  $query = trim(str_replace(':#:', ';', $query));
					if (!$query) continue;
					
					// drop existing triggers/sequences
					if (preg_match('/^CREATE TRIGGER ([\S]+)/i', $query, $m1) || preg_match('/^CREATE SEQUENCE ([\S]+)/i', $query, $m2)) {
					  $db->execute($q = 'DROP ' . ($m1 ? 'TRIGGER ' : 'SEQUENCE ') . ($m1 ? $m1[1] : $m2[1]), FALSE, SRA_ERROR_OPERATIONAL);
					  // add semi-colon back to trigger
					  if ($m1) $query .= ';';
					}
					
					// execute query
					$err = SRA_Error::isError($db->execute($query)) ? TRUE : NULL;
					if ($err) break;
					
				}
			}
		}
		// table exists
		else {
			// check if each column exists
			$columns =& $table->getColumns();
			$ckeys = array_keys($columns);
			$isOracle = strtolower(get_class($db)) == 'sra_databaseoracle';
			foreach ($ckeys as $ckey) {
				$query = 'SELECT ' . $columns[$ckey]->getName() . ' FROM ' . $table->getName();
				if (SRA_Error::isError($r =& $db->fetch($query, array(SRA_DATA_TYPE_INT), 1, FALSE, SRA_ERROR_OPERATIONAL))) {
					// column does not exist, add to table
					$query = 'ALTER TABLE ' . $table->getName() . ' ADD ' . ($isOracle ? '(' : 'COLUMN ') . $columns[$ckey]->getName() . ' ' . $db->getColumnDefinition($table, $columns[$ckey]) . ($isOracle ? ')' : '');
					$err = SRA_Error::isError($db->execute($query)) ? TRUE : NULL;
					if ($err) {
						break;
					}
				}
			}
		}
		return $err ? FALSE : ($created ? 1 : TRUE);
	}
	// }}}
  
}
// }}}
?>
