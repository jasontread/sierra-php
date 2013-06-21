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
require_once('SRA_WSRequest.php');
require_once('SRA_WS.php');
require_once('SRA_WSGlobal.php');
// }}}

// {{{ Constants
/**
 * the identifier for the api doc type
 * @type string
 */
define('SRA_WS_GATEWAY_DOC_TYPE_API', 'api');

/**
 * the identifier for the entity doc type
 * @type string
 */
define('SRA_WS_GATEWAY_DOC_TYPE_ENTITY', 'entity');

/**
 * the identifier for the wsdl doc type
 * @type string
 */
define('SRA_WS_GATEWAY_DOC_TYPE_WSDL', 'wsdl');

/**
 * the identifier for the api css uri in the mapping file
 * @type string
 */
define('SRA_WS_GATEWAY_KEY_API_CSS_URI', '.api-css-uri');

/**
 * the identifier for the gateway rewrite flag in the mapping file
 * @type string
 */
define('SRA_WS_GATEWAY_KEY_REWRITE', '.gateway-rewrite');

/**
 * the identifier for the skip app id flag in the mapping file
 * @type string
 */
define('SRA_WS_GATEWAY_KEY_SKIP_APP_ID', '.gateway-skip-app-id');

/**
 * the identifier for the gateway uri in the mapping file
 * @type string
 */
define('SRA_WS_GATEWAY_KEY_URI', '.gateway-uri');

/**
 * the timeout threshold in minutes for an async request. when this threshold is 
 * reached and an invocation is made to retrieve the results of that request the 
 * status code SRA_WS_REQUEST_STATUS_TIMEOUT will be returned
 * @type int
 */
define('SRA_WS_GATEWAY_ASYNC_REQUEST_TIMEOUT', 5);

/**
 * the prefix to use for silent buffer session variables and file names
 * @type string
 */
define('SRA_WS_GATEWAY_BUFFER_PREFIX', '.wsBuffer');

/**
 * the number of minutes to keep an output buffer file
 * @type int
 */
define('SRA_WS_GATEWAY_BUFFER_FILE_REMOVE', 1);

/**
 * the prefix of the name of the temp file containing the service mappings. this 
 * file name will be followed by the app identifier
 * @type string
 */
define('SRA_WS_GATEWAY_MAPPING_FILE', '.ws-');

/**
 * the name of the no-op web service. this is a generic service that can be used 
 * to validate login credentials and start sessions
 * @type string
 */
define('SRA_WS_GATEWAY_NOOP', 'wsnoop');

/**
 * identifier for soap version 1.1
 * @type string
 */
define('SRA_WS_VERSION_1_1', '11');

/**
 * identifier for soap version 1.2
 * @type string
 */
define('SRA_WS_VERSION_1_2', '12');
// }}}

// {{{ SRA_WSGateway
/**
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSGateway {
  /**
   * if the request is for api or wsdl documentation, this attribute will 
   * represent which
   * @type string
   */
  var $_doc;
  
  /**
   * TRUE if the request is soap
   * @type boolean
   */
  var $_isSoap = FALSE;
  
  /**
   * represents the request this gateway instance has been instantiated for
   * @type SRA_WSRequest
   */
  var $_request;
  
  /**
   * the service instance for this request
   * @type SRA_WS
   */
  var $_service;
  
  /**
   * the session variable for this request
   * @type string
   */
  var $_sessionVar;
  
	// {{{ SRA_WSGateway
	/**
	 * instantiates a new SRA_WSGateway instance to hande a web service request
   * @param hash $params the request parameters. these are documented in 
   * sra-ws-gateway.php
   * @access public
	 */
	function SRA_WSGateway(&$params) {
    require_once('SRA_WSRequest.php');
    $this->_bindDynamicParams($params);
    
    // php 5.2.2 bug workaround ($GLOBALS['HTTP_RAW_POST_DATA'] is not set)
    if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) { $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input'); }
    
    // soap request
    if (isset($GLOBALS['HTTP_RAW_POST_DATA']) && preg_match('/xmlns:(.*)="(.*)envelope/', $GLOBALS['HTTP_RAW_POST_DATA'], $m)) {
      //sra_error::logerror($GLOBALS['HTTP_RAW_POST_DATA']);
      $soapKey = $m[1];
      $this->_isSoap = TRUE;
      $parser =& SRA_XmlParser::getXmlParser($GLOBALS['HTTP_RAW_POST_DATA'], TRUE);
      $data =& $parser->getData();
      if (isset($data[$soapKey . ':envelope'][$soapKey . ':body']) && 
          ($keys = array_keys($data[$soapKey . ':envelope'][$soapKey . ':body'])) && 
          ($keys1 = array_keys($data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]])) && 
          ($keys2 = array_keys($data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]][$keys1[1]])) && 
          isset($data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]][$keys1[1]][$keys2[0]]['request']) && 
          ($keys3 = array_keys($data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]][$keys1[1]][$keys2[0]]['request'])) && 
          isset($data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]][$keys1[1]][$keys2[0]]['request'][$keys3[0]])) {
        $params['ws'] = preg_match('/.*:(.*)/', $keys1[1], $m) ? $m[1] : $keys1[1];
        $xmlConf = $data[$soapKey . ':envelope'][$soapKey . ':body'][$keys[0]][$keys1[1]][$keys2[0]]['request'][$keys3[0]];
        $params['ws-format'] = isset($xmlConf['attributes']['format']) ? $xmlConf['attributes']['format'] : SRA_WS_META_FORMAT_XML;
        $xmlConf = SRA_WSGateway::_convertSoapXml($xmlConf);
      }
    }
    else {
      $xmlConf = NULL;
    }
    
    if (isset($params['ws-request-xml'])) {
      if (!SRA_XmlParser::isValid($xmlParser =& SRA_XmlParser::getXmlParser($params['ws-request-xml'], TRUE))) {
        $msg = 'SRA_WSGateway: Failed - ws-request-xml count not be parsed: ' . $params['ws-request-xml'];
        $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
        return;
      }
      $xmlConf =& $xmlParser->getData();
    }
    $this->_request = new SRA_WSRequest($xmlConf, $this->_isSoap, isset($params['ws']) ? $params['ws'] : NULL);
    if (isset($params['ws-action'])) $this->_request->_action = $params['ws-action'];
    if (isset($params['ws-app'])) $this->_request->_app = $params['ws-app'];
    if (isset($params['ws-format'])) {
      $this->_request->_format = $params['ws-format'];
      $this->_request->_metaFormat = $this->_request->_format == SRA_WS_FORMAT_RAW ? SRA_WS_META_FORMAT_NONE : $this->_request->_format;
    }
    
    if (isset($params['ws-asynchronous'])) $this->_request->_asynchronous = SRA_Util::convertBoolean($params['ws-asynchronous']);
    $constraintGroups = array();
    foreach(array_keys($params) as $key) {
      if (preg_match('/ws-constraint(.*)-.*/', $key, $m) && count($m) == 2) {
        $constraintGroups[$m[1]] = array();
        foreach(array_keys($params) as $key) {
          if (preg_match('/ws-constraint' . $m[1] . '-attr(.*)/', $key, $m1) && count($m1) == 2) {
            $constraintGroups[$m[1]][] = $m1[1];
          }
        }
        if (!count($constraintGroups[$m[1]])) unset($constraintGroups[$m[1]]);
      }
    }
    foreach(array_keys($constraintGroups) as $gkey) {
      $gkeyPre = 'ws-constraint' . $gkey . '-';
      $gconf = array('attributes' => array(), 'ws-constraint' => array());
      if (isset($params[$gkeyPre . 'connective'])) $gconf['attributes']['connective'] = $params[$gkeyPre . 'connective'];
      foreach($constraintGroups[$gkey] as $ckey) {
        $cconf = array('attributes' => array());
        $cconf['attributes']['attr'] = $params[$gkeyPre . 'attr' . $ckey];
        if (isset($params[$gkeyPre . 'type' . $ckey])) $cconf['attributes']['attr-type'] = $params[$gkeyPre . 'type' . $ckey];
        if (isset($params[$gkeyPre . 'operator' . $ckey])) $cconf['attributes']['operator'] = $params[$gkeyPre . 'operator' . $ckey];
        if (isset($params[$gkeyPre . 'value' . $ckey])) $cconf['attributes']['value'] = $params[$gkeyPre . 'value' . $ckey];
        if (isset($params[$gkeyPre . 'value-type' . $ckey])) $cconf['attributes']['value-type'] = $params[$gkeyPre . 'value-type' . $ckey];
        $gconf['ws-constraint'][] = $cconf;
      }
      $this->_request->_constraintGroups[] = new SRA_WSConstraintGroup($gconf);
    }
    
    if (isset($params['ws-exclude'])) $this->_request->_excludeAttrs = explode(strpos($params['ws-exclude'], ',') ? ',' : ' ', $params['ws-exclude']);
    if (isset($params['ws-id'])) $this->_request->_primaryKey = $params['ws-id'];
    if (isset($params['ws-include'])) $this->_request->_includeAttrs = explode(strpos($params['ws-include'], ',') ? ',' : ' ', $params['ws-include']);
    if (isset($params['ws-callback'])) $this->_request->_callback = $params['ws-callback'];
    if (isset($params['ws-js-dates'])) $this->_request->_jsDates = SRA_Util::convertBoolean($params['ws-js-dates']);
    if (isset($params['ws-date-format'])) $this->_request->_dateFormat = $params['ws-date-format'];
    if (isset($params['ws-time-format'])) $this->_request->_timeFormat = $params['ws-time-format'];
    if (isset($params['ws-date-format']) || isset($params['ws-time-format'])) $this->_request->_jsDates = FALSE;
    if (isset($params['ws-limit'])) $this->_request->_limit = $params['ws-limit'];
    if (isset($params['ws-meta-format'])) $this->_request->_metaFormat = $params['ws-meta-format'];
    if (isset($params['ws-offset'])) $this->_request->_offset = $params['ws-offset'];
    $rparams = array();
    foreach(array_keys($params) as $key) {
      if (preg_match('/ws-param(.*)/', $key, $m) && count($m) == 2 && !preg_match('/ws-param(.*)\-/', $key)) {
        $rparams[$m[1]] = TRUE;
      }
    }
    foreach(array_keys($rparams) as $pkey) {
      $pconf = array('attributes' => array('name' => $params['ws-param' . $pkey]));
      if (isset($params['ws-param' . $pkey . '-value'])) $pconf['attributes']['value'] = $params['ws-param' . $pkey . '-value'];
      if (isset($params['ws-param' . $pkey . '-value-type'])) $pconf['attributes']['value-type'] = $params['ws-param' . $pkey . '-value-type'];
      if (SRA_WSParam::isValid($param = new SRA_WSParam($pconf))) {
        if (isset($this->_request->_params[$param->_name])) {
          if (!is_array($this->_request->_params[$param->_name]->_value)) $this->_request->_params[$param->_name]->_value = array($this->_request->_params[$param->_name]->_value);
          $this->_request->_params[$param->_name]->_value[] = $param->_value;
        }
        else {
          $this->_request->_params[$param->_name] = $param;
        }
      }
    }
    if (isset($params['ws-password'])) $this->_request->_password = $params['ws-password'];
    if (isset($params['ws-query'])) $this->_request->_query = SRA_Util::convertBoolean($params['ws-query']);
    if (isset($params['ws-request-id'])) $this->_request->_requestId = $params['ws-request-id'];
    if (isset($params['ws-request-id1'])) $this->_request->_requestId1 = $params['ws-request-id1'];
    if (isset($params['ws-session-id'])) $this->_request->_sessionId = $params['ws-session-id'];
    if (isset($params['ws-use-sessions'])) $this->_request->_useSessions = SRA_Util::convertBoolean($params['ws-use-sessions']);
    if (isset($params['ws-user'])) $this->_request->_user = $params['ws-user'];
    if (isset($params['ws-validator'])) $this->_request->_validator = $params['ws-validator'];
    if ($this->_isSoap) $this->_request->_soapVersion = isset($params['ws-soap-version']) ? $params['ws-soap-version'] : SRA_WS_VERSION_1_2;
    if (isset($params['ws-wf-id'])) $this->_request->_workflowId = $params['ws-wf-id'];
    if (isset($params['ws-doc'])) $this->_doc = $params['ws-doc'];
    
    if (!isset($params['ws-request-xml'])) {
      foreach(array_keys($params) as $key) {
        if ($key == 'ws' || SRA_Util::beginsWith($key, 'ws-')) {
          unset($params[$key]);
        }
      }
      foreach(array_keys($params) as $key) {
        $pconf = array('attributes' => array('name' => $key, 'value' => $params[$key]));
        if (isset($params[$key . '-type'])) $pconf['attributes']['value-type'] = $params[$key . '-type'];
        if (SRA_WSParam::isValid($param = new SRA_WSParam($pconf))) {
          if (isset($this->_request->_params[$key])) {
            if (!is_array($this->_request->_params[$key]->_value)) $this->_request->_params[$key]->_value = array($this->_request->_params[$key]->_value);
            $this->_request->_params[$key]->_value[] = $param->_value;
          }
          else {
            $this->_request->_params[$key] = $param;
          }
        }
      }
    }
    
    $this->_sessionVar = SRA_WS_GATEWAY_BUFFER_PREFIX . $this->_request->_requestId . '_' . $this->_request->_requestId1;
    
    if ($this->_doc && $this->_doc != SRA_WS_GATEWAY_DOC_TYPE_API && $this->_doc != SRA_WS_GATEWAY_DOC_TYPE_ENTITY && $this->_doc != SRA_WS_GATEWAY_DOC_TYPE_WSDL) {
      $msg = 'SRA_WSGateway: Failed - ws-doc is not valid: ' . $this->_doc;
      $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
      return;
    }
    else if ($this->_request->__id != SRA_WS_GATEWAY_NOOP && $this->_request->_service && ((($this->_isSoap || $this->_doc == SRA_WS_GATEWAY_DOC_TYPE_WSDL) && !$this->_request->_service->_soap) || (!$this->_soap && !$this->_request->_service->_rest))) {
      $this->_request->_status = SRA_WS_REQUEST_STATUS_INVALID_PROTO;
    }
    
    if ($this->_isSoap) { $this->_request->_metaFormat = SRA_WS_META_FORMAT_XML; }
	}
	// }}}
  
	// {{{ printResponse
	/**
	 * renders the response from the web service request
   * @access public
   * @return void
	 */
	function printResponse() {
    if ($this->_request->_asynchronous) { ob_start(); }
    if ((!$this->_doc && $this->_request->_metaFormat == SRA_WS_META_FORMAT_XML) || $this->_doc == SRA_WS_GATEWAY_DOC_TYPE_WSDL) { 
      // determine correct mime type (soap 1.2 standard requires use of application/soap+xml mime type)
      $contentType = $this->_isSoap && $this->_request->_soapVersion == SRA_WS_VERSION_1_2 ? 'application/soap+xml' : 'text/xml';
      header('Content-Type: ' . $contentType);  
    }

    if ($this->_request->_query) {
      // delete old output buffer files
      $files = SRA_File::getFileList(SRA_Controller::getSysTmpDir(), '/^' . SRA_WS_GATEWAY_BUFFER_PREFIX . '.*$/');
      foreach($files as $key => $file) {
        if (time() > (filemtime($file) + (SRA_WS_GATEWAY_BUFFER_FILE_REMOVE * 60))) {
          SRA_File::unlink($file);
          unset($files[$key]);
        }
      }
      
      session_start();
      $outputBufferFile = SRA_Controller::getSysTmpDir() . '/' . $this->_sessionVar . '_' . session_id();
      if (!isset($_SESSION[$this->_sessionVar])) {
        $_SESSION[$this->_sessionVar] = time();
      }
      if (file_exists($outputBufferFile) || file_exists($outputBufferFile . '.cpl')) {
        echo SRA_File::toString($outputBufferFile . (file_exists($outputBufferFile) ? '' : '.cpl'));
        if (file_exists($outputBufferFile)) { SRA_File::move($outputBufferFile, $outputBufferFile . '.cpl'); }
        unset($_SESSION[$this->_sessionVar]);
        $skipPrint = TRUE;
      }
      else {
        if (time() > ($_SESSION[$this->_sessionVar] + (SRA_WS_GATEWAY_ASYNC_REQUEST_TIMEOUT * 60))) {
          $this->_request->_status = SRA_WS_REQUEST_STATUS_TIMEOUT;
          unset($_SESSION[$this->_sessionVar]);
          $msg = 'sra-ajax-gateway: Timeout occurred while waiting for request ' . $this->_request->_requestId . '/' . $this->_request->_requestId1;
          SRA_Error::logError($msg, __FILE__, __LINE__);
        }
        else {
          $this->_request->_status = SRA_WS_REQUEST_STATUS_RESULTS_NOT_AVAILABLE;
        }
      }
    }
    if (!$skipPrint && !$this->_doc) {
      $this->_request->printResponse();
    }
    else if ($this->_doc) {
      if ($this->_doc != SRA_WS_GATEWAY_DOC_TYPE_WSDL) {
        global $_wsGateway;
        $_wsGateway = NULL;
      }
      if (!SRA_Controller::init($this->_request->_app, $this->_doc == SRA_WS_GATEWAY_DOC_TYPE_WSDL, FALSE, TRUE, TRUE)) {
        $resources =& SRA_ResourceBundle::getBundle('web-services-api');
        echo $resources->getString('error.invalidApp');
        return FALSE;
      }
      if ($this->_doc != SRA_WS_GATEWAY_DOC_TYPE_WSDL) {
        $_wsGateway =& $this;
      }
      $tpl =& SRA_Controller::getSysTemplate();
      $tpl->assign('rewrite', SRA_WSGateway::isGatewayRewrite());
      $tpl->assign('skipAppId', SRA_WSGateway::isGatewaySkipAppId());
      
      // show wsdl
      if ($this->_doc == SRA_WS_GATEWAY_DOC_TYPE_WSDL) {
        if (!$this->_request->_id) {
          $services =& SRA_WSGateway::getServices();
        }
        else {
          $services = array();
          if ($this->_request->getService()) {
            $services[$this->_request->_id] =& $this->_request->getService();
          }
        }
        if (!$services) {
          $resources =& SRA_ResourceBundle::getBundle('web-services-api');
          echo $resources->getString('error.invalidService');
          return FALSE;
        }
        $tpl->assignByRef('services', $services);
        foreach(array_keys($services) as $key) {
          if ($services[$key]->_global) {
            $tpl->assign('hasGlobalService', TRUE);
            if ($services[$key]->_type == SRA_WS_GLOBAL_TYPE_RB) {
              $tpl->assign('hasRbService', TRUE);
            }
            else if ($services[$key]->_type == SRA_WS_GLOBAL_TYPE_SQL) {
              $tpl->assign('hasSqlService', TRUE);
            }
            else {
              $tpl->assign('hasMethodService', TRUE);
            }
          }
          else {
            $tpl->assign('hasEntityService', TRUE);
            if ($services[$key]->_create) { $tpl->assign('hasCreateService', TRUE); }
            if ($services[$key]->_delete) { $tpl->assign('hasDeleteService', TRUE); }
            if ($services[$key]->_retrieve) { $tpl->assign('hasRetrieveService', TRUE); }
            if ($services[$key]->_update) { $tpl->assign('hasUpdateService', TRUE); }
          }
          if (!$services[$key]->_formatFixed) {
            $tpl->assign('unfixedFormat', TRUE);
            if ($service[$key]->_global && $service[$key]->_type == SRA_WS_GLOBAL_TYPE_METHOD) {
              $tpl->assign('formatRaw', TRUE);
            }
            $tpl->assign('formatJson', TRUE);
            $tpl->assign('formatXml', TRUE);
          }
          if (!$services[$key]->_rest) {
            $tpl->assign('restRestricted', TRUE);
          }
          if (!$services[$key]->_soap) {
            $tpl->assign('soapRestricted', TRUE);
          }
        }
        
        $entities = array();
        if (!$this->_request->_id || (($service =& $this->_request->getService()) && !$service->_global)) {
          if ($this->_request->_id) {
            SRA_DaoFactory::getDao($service->_entity);
            $className = SRA_EntityModeler::getAppEntityClass($service->_entity);
            $types = array($service->_entity);
            eval('$types = array_merge($types, ' . $className . '::getAttributeTypesUsed(TRUE));');
            eval("\$hasNonMandatoryValidator = ${className}::hasNonMandatoryValidator();");
            $tpl->assign('hasNonMandatoryValidator', $hasNonMandatoryValidator);
          }
          else {
            $tpl->assign('hasNonMandatoryValidator', TRUE);
          }
          foreach(($this->_request->_id ? $types : SRA_EntityModeler::getAppEntityIds()) as $name) {
            require_once(SRA_EntityModeler::getAppEntityPath($name));
            $className = SRA_EntityModeler::getAppEntityClass($name);
            eval('$skipWsdl = ' . $className . '::skipWsdl();');
            
            if (!$skipWsdl) {
              if (!$usesFiles) { eval('$usesFiles = ' . $className . '::attributesUseFiles();'); }
              eval('$wsdl = ' . $className . '::getWsdl(NULL, NULL, "      ");');
              $entities[$className] = $wsdl;
            }
          }
        }
        $tpl->assign('usesFiles', $usesFiles);
        $tpl->assign('serviceSpecific', $this->_request->_id ? TRUE : FALSE);
        $tpl->assignByRef('entities', $entities);
        $tpl->display('model/sra-wsdl.tpl');
      }
      // show api documentation
      else {
        $tpl->assign('apiUri', SRA_WSGateway::getApiUri());
        $tpl->assign('authenticated', isset($_SERVER['PHP_AUTH_USER']));
        $tpl->assign('entityUri', SRA_WSGateway::getEntityUri());
        $tpl->assign('wsUri', SRA_Controller::getServerUri() . SRA_WSGateway::getGatewayUri());
        $tpl->assign('wsdlUri', SRA_WSGateway::getWsdlUri());
        $tpl->assignByRef('appResources', SRA_Controller::getAppResources());
        $tpl->assignByRef('apiResources', SRA_ResourceBundle::getBundle('web-services-api'));
        if ($wsCssUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_API_CSS_URI)) { $tpl->assign('wsCssUri', $wsCssUri); }
        
        if ($this->_doc == SRA_WS_GATEWAY_DOC_TYPE_ENTITY) {
          if (!file_exists($entityPath = SRA_EntityModeler::getAppEntityPath($this->_request->_id))) {
            $msg = 'SRA_WSGateway: Failed - entity is not valid: ' . $this->_request->_id;
            $this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
            return FALSE;
          }
          else {
            include_once($entityPath);
            $tmp = $this->_request->_id;
            $tpl->assignByRef('entity', new $tmp());
            $tpl->display('model/sra-ws-api-entity.tpl');
          }
        }
        else {
          if ($this->_request->_id && !SRA_WSGateway::getService($this->_request->_id)) {
            $resources =& SRA_ResourceBundle::getBundle('web-services-api');
            echo $resources->getString('error.invalidService');
            return FALSE;
          }
          
          // service specific api
          if ($this->_request->_id) {
            $tpl->assignByRef('service', $service =& SRA_WSGateway::getService($this->_request->_id));
            // assign api
            if ($service->_global && $service->_type == SRA_WS_GLOBAL_TYPE_METHOD && $service->_introspectApi && ($api =& SRA_Util::getStaticMethodPathApi($service->_identifier))) {
              $tpl->assignByRef('api', $api);
              $tpl->assign('usesLimit', isset($api['params']) && isset($api['params']['limit']));
              $tpl->assign('usesOffset', isset($api['params']) && isset($api['params']['offset']));
            }
            else if ($service->_global && $service->_type != SRA_WS_GLOBAL_TYPE_METHOD) {
              $tpl->assign('usesLimit', TRUE);
              $tpl->assign('usesOffset', TRUE);
            }
            // assign reference to dao and entity for entity specific services
            else if (!$service->_global && !SRA_Error::isError($dao =& SRA_DaoFactory::getDao($service->_entity))) {
              $tpl->assignByRef('dao', $dao);
              $tpl->assignByRef('entity', $dao->newInstance());
            }
            $tpl->display('model/sra-ws-api-service.tpl');
          }
          // services overview api
          else {
            $services =& SRA_WSGateway::getServices();
            $containsSoapService = FALSE;
            foreach(array_keys($services) as $key) {
              if ($services[$key]->_soap) {
                $containsSoapService = TRUE;
                break;
              }
            }
            $tpl->assign('containsSoapService', $containsSoapService);
            $tpl->assignByRef('services', $services);
            $tpl->display('model/sra-ws-api-overview.tpl');
          }
        }
      }
    }
    
    if ($this->_request->_asynchronous) {
      $_SESSION[$this->_sessionVar] = time();
      $outputBufferFile = SRA_Controller::getSysTmpDir() . '/' . $this->_sessionVar . '_' . session_id();
      $val = ob_get_contents();
      ob_end_clean();
      SRA_File::write($outputBufferFile . '.tmp', $val);
      SRA_File::move($outputBufferFile . '.tmp', $outputBufferFile);
    }
    
    if ($this->_request->_useSessions) { session_write_close(); }
	}
	// }}}
  
	// {{{ process
	/**
	 * processes the web service request. returns TRUE on success, FALSE if a 
   * system error occurs
   * @access public
   * @return boolean
	 */
	function process() {
    if (!$this->_doc && !$this->_request->_query) {
      return $this->_request->process();
    }
    else {
      return TRUE;
    }
	}
	// }}}
  
  
	// {{{ _bindDynamicParams
	/**
	 * binds any dynamic parameters in $params as documented in sra-ws-gateway.php 
   * and removes them from $params if they exist. invalid dynamic parameters 
   * will be logged and removed
   * @param hash $params the request parameters
   * @access private
   * @return void
	 */
	function _bindDynamicParams(&$params) {
    // dynamically binded parameters
    if (isset($params['ws1']) || isset($params['ws2']) || isset($params['ws3']) || isset($params['ws4']) || isset($params['ws5'])) {
      if (isset($params['ws1']) && !isset($params['ws-app']) && SRA_Controller::appKeyIsValid($params['ws1'])) {
        $params['ws-app'] = $params['ws1'];
        if (!$params['ws2']) $params['ws2'] = 'api';
      }
      else if (isset($params['ws1']) && !isset($params['ws-request-xml']) && strpos($params['ws1'], 'ws-request') !== FALSE) {
        $params['ws-request-xml'] = $params['ws1'];
      }
      else if (isset($params['ws1']) && $params['ws1']) {
        $msg = 'SRA_WSGateway::_bindDynamicParams: Warning - dynamic variable ws1 "' . $params['ws1'] . '" is not valid';
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
      }
      if (isset($params['ws2']) && ($params['ws2'] == SRA_WS_VERSION_1_1 || $params['ws2'] == SRA_WS_VERSION_1_2)) {
        $params['ws-soap-version'] = $params['ws2'];
      }
      else if (isset($params['ws2']) && !isset($params['ws-doc']) && ($params['ws2'] == 'api' || $params['ws2'] == 'entity' || $params['ws2'] == 'wsdl')) {
        $params['ws-doc'] = $params['ws2'];
      }
      else if (isset($params['ws2']) && !isset($params['ws'])) {
        $params['ws'] = $params['ws2'];
      }
      else if (isset($params['ws2']) && $params['ws2']) {
        $msg = 'SRA_WSGateway::_bindDynamicParams: Warning - dynamic variable ws2 "' . $params['ws2'] . '" is not valid';
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
      }
      if (isset($params['ws3']) && ($params['ws3'] == SRA_WS_VERSION_1_1 || $params['ws3'] == SRA_WS_VERSION_1_2)) {
        $params['ws-soap-version'] = $params['ws3'];
      }
      else if (isset($params['ws3']) && !isset($params['ws-doc']) && ($params['ws3'] == 'api' || $params['ws3'] == 'wsdl' || $params['ws3'] == 'entity')) {
        $params['ws-doc'] = $params['ws3'];
      }
      else if (isset($params['ws3']) && !isset($params['ws-format']) && ($params['ws3'] == 'json' || $params['ws3'] == 'xml' || $params['ws3'] == 'raw')) {
        $params['ws-format'] = $params['ws3'];
      }
      else if (isset($params['ws3']) && !isset($params['ws-action']) && ($params['ws3'] == 'create' || $params['ws3'] == 'delete' || $params['ws3'] == 'retrieve' || $params['ws3'] == 'update')) {
        $params['ws-action'] = $params['ws3'];
      }
      else if (isset($params['ws3']) && $params['ws3']) {
        $msg = 'SRA_WSGateway::_bindDynamicParams: Warning - dynamic variable ws3 "' . $params['ws3'] . '" is not valid';
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
      }
      if (isset($params['ws4']) && ($params['ws4'] == SRA_WS_VERSION_1_1 || $params['ws4'] == SRA_WS_VERSION_1_2)) {
        $params['ws-soap-version'] = $params['ws4'];
      }
      else if (isset($params['ws4']) && !isset($params['ws-format']) && ($params['ws4'] == 'json' || $params['ws4'] == 'xml' || $params['ws4'] == 'raw')) {
        $params['ws-format'] = $params['ws4'];
      }
      else if (isset($params['ws4']) && !isset($params['ws-id'])) {
        $params['ws-id'] = $params['ws4'];
      }
      else if (isset($params['ws4']) && $params['ws4']) {
        $msg = 'SRA_WSGateway::_bindDynamicParams: Warning - dynamic variable ws4 "' . $params['ws4'] . '" is not valid';
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
      }
      if (isset($params['ws5']) && ($params['ws5'] == SRA_WS_VERSION_1_1 || $params['ws5'] == SRA_WS_VERSION_1_2)) {
        $params['ws-soap-version'] = $params['ws5'];
      }
      else if (isset($params['ws5']) && !isset($params['ws-format']) && ($params['ws5'] == 'json' || $params['ws5'] == 'xml' || $params['ws5'] == 'raw')) {
        $params['ws-format'] = $params['ws5'];
      }
      else if (isset($params['ws5']) && $params['ws5']) {
        $msg = 'SRA_WSGateway::_bindDynamicParams: Warning - dynamic variable ws5 "' . $params['ws5'] . '" is not valid';
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
      }
      unset($params['ws1']);
      unset($params['ws2']);
      unset($params['ws3']);
      unset($params['ws4']);
      unset($params['ws5']);
    }
	}
	// }}}
  
	
	// Static methods
  
  
	// {{{ cacheServices
	/**
	 * caches the serialized versions of the $services in a resources file indexed 
   * by service name. returns TRUE on success
	 * @param SRA_WS[] $services the services to cache
   * @param string $gatewayUri the uri to sra-ws-gateway.php
   * @param boolean $gatewayRewrite whether or not a rewrite rule is in place 
   * for $gatewayUri
   * @param boolean $gatewaySkipAppId whether or not to skip adding the app id 
   * when ws uris are generated (i.e. the app id is automatically added using 
   * a rewrite rule)
   * @param string $apiCssUri the uri to the css for web service api 
   * documentation
	 * @access public
	 * @return boolean
	 */
	function cacheServices(&$services, $gatewayUri, $gatewayRewrite=FALSE, $gatewaySkipAppId=FALSE, $apiCssUri=NULL) {
    static $_cached = array();
    // only cache web services once per instance per application
    if (!isset($_cached[$file = SRA_WSGateway::getAppMappingFile()])) {
      if ((file_exists($file) && !unlink($file)) || ($fp = fopen($file, 'w')) === FALSE) {
        $msg = "SRA_WSGateway::cacheServices: Failed - Unable to write to mappings file " . $file;
        SRA_Error::logError($msg, __FILE__, __LINE__);
        return FALSE;
      }
      foreach(array_keys($services) as $key) {
        fwrite($fp, $services[$key]->_id . '=' . serialize($services[$key]) . "\n");
      }
      fwrite($fp, SRA_WS_GATEWAY_KEY_URI . '=' . $gatewayUri . "\n");
      fwrite($fp, SRA_WS_GATEWAY_KEY_REWRITE . '=' . $gatewayRewrite . "\n");
      fwrite($fp, SRA_WS_GATEWAY_KEY_SKIP_APP_ID . '=' . $gatewaySkipAppId . "\n");
      fwrite($fp, SRA_WS_GATEWAY_KEY_API_CSS_URI . '=' . $apiCssUri);
      fclose($fp);
      chmod($file, 0666);
      $_cached[$file] = TRUE;
      return TRUE;
    }
    else {
      return FALSE;
    }
	}
	// }}}
  
	// {{{ getApiCssUri
	/**
	 * returns the gateway api css uri (if applicable) for the active application
	 * @access public
	 * @return string
	 */
	function getApiCssUri() {
    return SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_API_CSS_URI);
	}
	// }}}
  
	// {{{ getApiUri
	/**
	 * returns the api uri
	 * @access public
	 * @return string
	 */
	function getApiUri() {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/api' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws-doc=api');
	}
	// }}}
  
	// {{{ getAppMappingFile
	/**
	 * returns the name of the current active application web services mapping 
   * file
	 *
	 * @access public
	 * @return string
	 */
	function getAppMappingFile() {
		return SRA_Controller::getAppTmpDir() . '/' . SRA_WS_GATEWAY_MAPPING_FILE . SRA_Controller::getCurrentAppId();
	}
	// }}}
  
	// {{{ getEntityUri
	/**
	 * returns the entity uri prefix
	 * @access public
	 * @return string
	 */
	function getEntityUri() {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/[entity]/entity' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws-doc=entity&ws-id=[entity]');
	}
	// }}}
  
	// {{{ getGatewayUri
	/**
	 * returns the gateway uri for the active application
	 * @access public
	 * @return string
	 */
	function getGatewayUri() {
    return SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
	}
	// }}}
  
	// {{{ getService
	/**
	 * retrieves a reference to the service specified based on the current active 
   * platform or NULL if $id is not valid
	 * @param  string $id the identifier of the service to return
	 * @access public
	 * @return mixed
	 */
	function &getService($id) {
    static $services;
    $file = SRA_WSGateway::getAppMappingFile();
    if (!isset($services[$file])) {
      $services[$file] = array();
      $tmp = file($file);
      $keys = array_keys($tmp);
      foreach($keys as $key) {
        $pieces = explode('=', $tmp[$key], 2);
        $services[$file][$pieces[0]] = $pieces[0] != SRA_WS_GATEWAY_KEY_URI && $pieces[0] != SRA_WS_GATEWAY_KEY_REWRITE && $pieces[0] != SRA_WS_GATEWAY_KEY_SKIP_APP_ID && $pieces[0] != SRA_WS_GATEWAY_KEY_API_CSS_URI ? unserialize(str_replace("\n", "", $pieces[1])) : str_replace("\n", "", $pieces[1]);
      }
    }
    foreach(array_keys($services[$file]) as $service) {
      if (strtolower($id) == strtolower($service)) {
        return $services[$file][$service];
      }
    }
    return $nl = NULL;
	}
	// }}}
  
	// {{{ getServices
	/**
	 * returns a reference to an array of SRA_WS and/or SRA_WSGlobal instances 
   * representing the services defined for the current application
	 * @access public
	 * @return mixed
	 */
	function &getServices() {
    static $services;
    $file = SRA_WSGateway::getAppMappingFile();
    if (!isset($services[$file])) {
      $services[$file] = array();
      $tmp = file($file);
      $keys = array_keys($tmp);
      foreach($keys as $key) {
        $pieces = explode('=', $tmp[$key], 2);
        if ($pieces[0] == SRA_WS_GATEWAY_KEY_URI || $pieces[0] == SRA_WS_GATEWAY_KEY_REWRITE || $pieces[0] == SRA_WS_GATEWAY_KEY_SKIP_APP_ID || $pieces[0] == SRA_WS_GATEWAY_KEY_API_CSS_URI) { continue; }
        $services[$file][$pieces[0]] = $pieces[0] != SRA_WS_GATEWAY_KEY_URI ? unserialize(str_replace("\n", "", $pieces[1])) : str_replace("\n", "", $pieces[1]);
      }
    }
    return isset($services[$file]) ? $services[$file] :  ($nl = NULL);
	}
	// }}}
  
	// {{{ getServiceExecJsonUri
	/**
	 * returns the api uri to execute $service with no parameters and return the 
   * results in json format
   * @param mixed $service the SRA_WS or SRA_WSGlobal to return the api uri for
	 * @access public
	 * @return string
	 */
	function getServiceExecJsonUri(& $service) {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/' . $service->_id . '/json' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws=' . $service->_id . '&ws-format=json');
	}
	// }}}
  
	// {{{ getServiceExecXmlUri
	/**
	 * returns the api uri to execute $service with no parameters and return the 
   * results in xml format
   * @param mixed $service the SRA_WS or SRA_WSGlobal to return the api uri for
	 * @access public
	 * @return string
	 */
	function getServiceExecXmlUri(& $service) {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/' . $service->_id . '/xml' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws=' . $service->_id . '&ws-format=xml');
	}
	// }}}
  
	// {{{ getServiceApiUri
	/**
	 * returns the api uri for $service
   * @param mixed $service the SRA_WS or SRA_WSGlobal to return the api uri for
	 * @access public
	 * @return string
	 */
	function getServiceApiUri(& $service) {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/' . $service->_id . '/api' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws=' . $service->_id . '&ws-doc=api');
	}
	// }}}
  
	// {{{ getServiceWsdlUri
	/**
	 * returns the wsdl uri for $service
   * @param mixed $service the SRA_WS or SRA_WSGlobal to return the api uri for
	 * @access public
	 * @return string
	 */
	function getServiceWsdlUri(& $service) {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/' . $service->_id . '/wsdl' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws=' . $service->_id . '&ws-doc=wsdl');
	}
	// }}}
  
	// {{{ getWsdlUri
	/**
	 * returns the wsdl uri
	 * @access public
	 * @return string
	 */
	function getWsdlUri() {
		$wsUri = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_URI);
    $wsUriRewrite = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE);
    $wsSkipAppId = SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID);
    return $wsUri . ($wsUriRewrite ? (!$wsSkipAppId ? '/' . SRA_Controller::getCurrentAppId() : '') . '/wsdl' : (!$wsSkipAppId ? '?ws-app=' . SRA_Controller::getCurrentAppId() . '&' : '?') . 'ws-doc=wsdl');
	}
	// }}}
  
	// {{{ isGatewayRewrite
	/**
	 * returns TRUE if rewrite rule is in place for the gateway uri
	 * @access public
	 * @return boolean
	 */
	function isGatewayRewrite() {
    return SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_REWRITE) ? TRUE : FALSE;
	}
	// }}}
  
	// {{{ isGatewaySkipAppId
	/**
	 * returns TRUE if the app id should not be used in web service uris
	 * @access public
	 * @return boolean
	 */
	function isGatewaySkipAppId() {
    return SRA_WSGateway::getService(SRA_WS_GATEWAY_KEY_SKIP_APP_ID) ? TRUE : FALSE;
	}
	// }}}
	
	// {{{ isValid
	/**
	 * Sreturns true if the $object is a SRA_WSGateway instance
	 * @param object $object the object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid(&$object) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsgateway');
	}
	// }}}
  
	// {{{ _convertSoapXml
	/**
	 * converts a soap xml request so it is compliant with ws-request.dtd
	 * @access private
	 * @return array
	 */
	function _convertSoapXml($xml) {
    $converted = array();
    foreach(array_keys($xml) as $key) {
      $newKey = NULL;
      switch(strtolower($key)) {
        case 'attrtype':
          $newKey = 'attr-type';
          break;
        case 'valuetype':
          $newKey = 'value-type';
          break;
        case 'constraint':
          $newKey = 'ws-constraint';
          break;
        case 'param':
          $newKey = 'ws-param';
          break;
        case 'excludeattrs':
          $newKey = 'exclude-attrs';
          break;
        case 'includeattrs':
          $newKey = 'include-attrs';
          break;
        case 'callback':
          $newKey = 'ws-callback';
          break;
        case 'jsDates':
          $newKey = 'ws-js-dates';
          break;
        case 'dateFormat':
          $newKey = 'ws-date-format';
          break;
        case 'timeFormat':
          $newKey = 'ws-time-format';
          break;
        case 'requestid':
          $newKey = 'request-id';
          break;
        case 'requestid1':
          $newKey = 'request-id1';
          break;
        case 'constraintgroup':
          $newKey = 'ws-constraint-group';
          break;
        case 'create':
          $newKey = 'ws-create';
          break;
        case 'delete':
          $newKey = 'ws-delete';
          break;
        case 'retrieve':
          $newKey = 'ws-retrieve';
          break;
        case 'sessionid':
          $newKey = 'session-id';
          break;
        case 'update':
          $newKey = 'ws-update';
          break;
        case 'usesessions':
          $newKey = 'use-sessions';
          break;
        case 'workflowid':
          $newKey = 'workflow-id';
          break;
      }
      
      if (is_array($xml[$key])) {
        $converted[$newKey ? $newKey : $key] = SRA_WSGateway::_convertSoapXml($xml[$key]);
      }
      else {
        $converted[$newKey ? $newKey : $key] = $xml[$key];
      }
    }
    return $converted;
	}
	// }}}
  
}
// }}}
?>
