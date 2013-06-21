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

// }}}

// {{{ Constants
/**
 * identifies that the service request is to create a new entity
 * @type string
 */
define('SRA_WS_REQUEST_CREATE', 'create');

/**
 * identifies that the service request is to delete an existing entity
 * @type string
 */
define('SRA_WS_REQUEST_DELETE', 'delete');

/**
 * the identifier that can be used in "include-attrs" to identify the primary 
 * key attribute
 * @type string
 */
define('SRA_WS_REQUEST_PK', '_pk_');

/**
 * identifies that the service request is to retrieve existing entities
 * @type string
 */
define('SRA_WS_REQUEST_RETRIEVE', 'retrieve');

/**
 * identifies that a request could not be performed because the app specified 
 * is not valid
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_APP', 'invalid-app');

/**
 * identifies that a request could not be performed because attributes were set 
 * that are not allowed to be set by the service or if attributes set do not 
 * exist or if attributes set are read only
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_ATTRS', 'invalid-attrs');

/**
 * identifies that an update/delete request that failed because the primary key 
 * specified was not valid
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_PK', 'invalid-pk');

/**
 * identifies that a request could not be performed because the output format 
 * requested is not valid for this service
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_FORMAT', 'invalid-format');

/**
 * identifies that a request could not be performed because the input specified 
 * for an update or create action did not pass validation. if this occurs, the 
 * "response" will be populated with the error messages resulting from the 
 * failed validation
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_INPUT', 'validation-error');

/**
 * identifies that a request could not be performed because the limit requested 
 * is not allowed for this service
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_LIMIT', 'invalid-limit');

/**
 * identifies that a request could not be performed because the output 
 * meta-format requested is not valid for this service
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_META_FORMAT', 'invalid-meta-format');

/**
 * identifies that a request could not be performed because the protocol used 
 * to invoke it is not allowed (rest or soap)
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_PROTO', 'invalid-protocol');

/**
 * identifies that a request could not be performed because the request 
 * specified was not valid. the server logs will provide more detail on the 
 * reason for this error
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_REQUEST', 'invalid-request');

/**
 * identifies that a request could not be performed because a request was made 
 * for an invalid service
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_INVALID_SERVICE', 'invalid-service');

/**
 * identifies that a request could not be performed because the client IP 
 * address is not allowed
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_IP_NOT_ALLOWED', 'ip-not-allowed');

/**
 * identifies that a request could not be performed because of another 
 * unspecified error. basically, anything else that can go wrong will fall under 
 * this category. the server logs will provide more detail on the reason for 
 * this error
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_FAILED', 'failed');

/**
 * identifies that a request could not be performed because the action requested 
 * (create, delete, retrieve or update) is not allowed via the web service 
 * specified
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_NOT_ALLOWED', 'not-allowed');

/**
 * identifies that a request could not be performed because http authentication 
 * failed
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_AUTH_FAILED', 'authentication-failed');

/**
 * identifies that a request to retrieve buffered results from a prior async 
 * invocation could not be performed because the buffer result is not available 
 * yet. if this response code is returned, you may wish to retry your request 
 * again after a short interval
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_RESULTS_NOT_AVAILABLE', 'not-available');

/**
 * identifies that a request to retrieve buffered results from a prior async 
 * invocation could not be performed because the 
 * SRA_WS_GATEWAY_REQUEST_BUFFER_TIMEOUT theshold was reached
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_TIMEOUT', 'timeout');

/**
 * identifies that a request was performed successfully
 * @type string
 */
define('SRA_WS_REQUEST_STATUS_SUCCESS', 'success');

/**
 * identifies that the service request is to update an existing entity
 * @type string
 */
define('SRA_WS_REQUEST_UPDATE', 'update');

/**
 * identifies the key within the results produces by a service of type 
 * SRA_WS_GLOBAL_TYPE_METHOD specifying the total count (count without 
 * applying limit or offset)
 * @type string
 */
define('SRA_WS_RESULT_COUNT_KEY', '_resultCount');
// }}}

// {{{ SRA_WSRequest
/**
 * This class is used to define an web service invocation
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_WSRequest {
  /**
	 * the web service name
	 * @type string
	 */
	var $_id;
  
  /**
	 * identifies the action to take place for this service request. will 
   * correspond with one of the SRA_WS_REQUEST_* constants
	 * @type string
	 */
	var $_action;
  
  /**
	 * the application for this request
	 * @type string
	 */
	var $_app;
  
  /**
	 * specifies whether or not the output of this request should be buffered to a 
   * session variable instead of output to the client
	 * @type boolean
	 */
  var $_asynchronous;
  
  /**
	 * defines the names and values of the attributes to set if the action is 
   * SRA_WS_REQUEST_CREATE or SRA_WS_REQUEST_UPDATE. these will be subject to 
   * validation according to the constraints specified for the service. equality 
   * constraints will automatically be set and override any specified in the 
   * request. if any constraint groups fail to validate an attribute attempting 
   * to be set in the new entity, the gateway will return the status code 
   * SRA_WS_REQUEST_STATUS_NOT_ALLOWED
	 * @type array
	 */
	var $_attrs = NULL;
	
  /**
	 * name of a jsonp callback function (if specified using ws-callback)
	 * @type string
	 */
	var $_callback;
  
  /**
	 * optional constraint groups that should be applied to this request
	 * @type SRA_WSConstraintGroup[]
	 */
	var $_constraintGroups = array();
  
  /**
	 * a reference to the dao for non-global services
	 * @type DAO
	 */
	var $_dao;
  
  /**
	 * the formatting string to use in the response for date values. if not 
   * specified, the application date-only-format will be used
	 * @type string
	 */
	var $_dateFormat;
  
  /**
	 * for non-global requests when the request pertains to multiple entities
	 * @type object[]
	 */
	var $_entities;
  
  /**
	 * the names of attributes that should be excluded for this request
	 * @type array
	 */
	var $_excludeAttrs;
  
  /**
   * the desired response format for this request. either "json", "xml" or "raw"
   * @type string
   */
  var $_format;
  
  /**
   * TRUE if this request is for a global server (_server will be an 
   * SRA_WSGlobal instance)
   * @type boolean
   */
  var $_global = FALSE;
  
  /**
	 * the names of attributes that should be included for this request
	 * @type array
	 */
	var $_includeAttrs;
  
  /**
   * whether or not this is a soap request
   * @type boolean
   */
  var $_isSoap;
  
  /**
   * if true and the "format" is "json", date values will be encoded using the 
   * javascript Date constructor
   * @type boolean
   */
  var $_jsDates;
  
  /**
	 * the limit for this web service request
	 * @type int
	 */
	var $_limit;
  
  /**
	 * the desired response metadata format. either "json", "xml" or "none"
	 * @type string
	 */
	var $_metaFormat;
  
  /**
   * the request result set offset
   * @type int
   */
  var $_offset;
  
  /**
	 * the params associated with this web service request
	 * @type SRA_WSParam[]
	 */
	var $_params = array();
  
  /**
   * password for authentication. user and password can also be specified in the 
   * http headers. however, if user or password are specified in the get 
   * parameters or ws-request-xml, they will override the http header values
   * @type string
   */
  var $_password;
  
  /**
   * used to store the results of invoking the process method
   * @type boolean
   */
  var $_processed;
  
  /**
	 * primary key of the entity to retrieve, delete or update (only applies to 
   * those actions)
	 * @type mixed
	 */
	var $_primaryKey;
  
  /**
	 * used in conjunction with a previous "asynchronous" request in order to 
   * query for the results of that previous request
	 * @type boolean
	 */
  var $_query;
  
  /**
   * the query constraint groups corresponding with this service request
   * @type SRA_QueryBuilderConstraintGroup[]
   */
  var $_queryConstraintGroups = array();
  
  /**
	 * an optional identifier for the service request
	 * @type string
	 */
  var $_requestId;
  
  /**
	 * a second optional identifier for the service request
	 * @type string
	 */
  var $_requestId1;
  
	/**
	 * the results of the service invocation
	 * @type mixed
	 */
	var $_results;
	
	/**
	 * the total result count (without considering limit and offsets)
	 * @type int
	 */
	var $_resultCount;
  
  /**
   * the service that this request belongs to
   * @type mixed
   */
  var $_service;
  
  /**
	 * the soap version... one of the SRA_WS_SOAP_VERSION_* constant values
	 * @type string
	 */
  var $_soapVersion;
  
  /**
   * used to set an error status code if an error exists in the request
   * @type string
   */
  var $_status;
  
  /**
   * the request session id (if $_useSessions is true)
   * @type string
   */
  var $_sessionId;
  
  /**
	 * the formatting string to use in the response for time values. if not 
   * specified, the application date-format will be used
	 * @type string
	 */
	var $_timeFormat;
  
  /**
   * sessions can be used to improve response times and eliminate the need to 
   * send user and password information with each service request invocation. 
   * when this attribute is true, a session-id will be returned in the response. 
   * this session-id may them be used in future requests eliminating the need to 
   * provide user and password authentication information for those requests 
   * (assuming that authentication was successful in the original request, and 
   * the application authenticators allow for session-based authentication)
   * NOTE: if your web service client supports cookies, this option will not 
   * improve performance
   * @type boolean
   */
  var $_useSessions;
  
  /**
   * user for authentication. user and password can also be specified in the 
   * http headers. however, if user or password are specified in the get 
   * parameters or ws-request-xml, they will override the http header values
   * @type string
   */
  var $_user;
  
  /**
	 * an optional additional validators to invoke (applies only to entity 
   * create/update requests)
	 * @type array
	 */
  var $_validators;
  
  /**
	 * if the action is SRA_WS_REQUEST_RETRIEVE and the entity is stored 
   * within a workflow instance (see lib/workflow for more info), this attribute 
   * may be used to specify the workflow identifier
	 * @type int
	 */
	var $_workflowId;
  
  
	// {{{ SRA_WSRequest
	/**
	 * parses the service configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param mixed $conf the xml configuration to parse
   * @param boolean $isSoap whether or not this is a soap request
   * @param string $id the id of the service (optional)
   * 1.2)
   * @access  public
	 */
	function SRA_WSRequest($conf, $isSoap=FALSE, $id=NULL) {
    if (isset($conf['ws-request'])) {
      $keys = array_keys($conf['ws-request']);
      $conf = $conf['ws-request'][$keys[0]];
    }
    //sra_error::logerror($conf);
    $this->_isSoap = $isSoap;
    
    $this->_id = $id ? $id : $conf['attributes']['key'];
    $this->_action = isset($conf['ws-create']) ? SRA_WS_REQUEST_CREATE : (isset($conf['ws-delete']) ? SRA_WS_REQUEST_DELETE : (isset($conf['ws-update']) ? SRA_WS_REQUEST_UPDATE : SRA_WS_REQUEST_RETRIEVE));
    
    $this->_app = isset($conf['attributes']['app']) ? $conf['attributes']['app'] : NULL;
    $this->_asynchronous = SRA_Util::convertBoolean(isset($conf['attributes']['asynchronous']) ? $conf['attributes']['asynchronous'] : NULL);
    if (isset($conf['ws-constraint-group'])) {
      foreach (array_keys($conf['ws-constraint-group']) as $gkey) {
        if (!SRA_WSConstraintGroup::isValid($this->_constraintGroups[] = new SRA_WSConstraintGroup($conf['ws-constraint-group'][$gkey]))) {
          $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
          break;
        }
        else {
          $this->_queryConstraintGroups[] =& $this->_constraintGroups[count($this->_constraintGroups) - 1]->toQueryConstraintGroup();
        }
      }
    }
    $this->_dateFormat = isset($conf['attributes']['date-format']) ? $conf['attributes']['date-format'] : NULL;
    $this->_excludeAttrs = isset($conf['attributes']['exclude-attrs']) ? explode(strpos($conf['attributes']['exclude-attrs'], ',') ? ',' : ' ', $conf['attributes']['exclude-attrs']) : array();
    $this->_format = isset($conf['attributes']['format']) ? $conf['attributes']['format'] : ($this->_id == SRA_WS_GATEWAY_NOOP ? ($this->_isSoap ? SRA_WS_FORMAT_XML : SRA_WS_FORMAT_JSON) : NULL);
    if ($this->_id == SRA_WS_GATEWAY_NOOP) { $this->_global = TRUE; }
    $this->_includeAttrs = isset($conf['attributes']['include-attrs']) ? explode(strpos($conf['attributes']['include-attrs'], ',') ? ',' : ' ', $conf['attributes']['include-attrs']) : array();
    if ($this->_includeAttrs) $this->_includeAttrs[] = SRA_WS_REQUEST_PK;
    $this->_limit = isset($conf['attributes']['limit']) ? $conf['attributes']['limit'] : NULL;
    $this->_metaFormat = isset($conf['attributes']['meta-format']) ? $conf['attributes']['meta-format'] : NULL;
    $this->_metaFormat = isset($conf['attributes']['meta-format']) ? $conf['attributes']['meta-format'] : ($this->_format ? ($this->_format == SRA_WS_FORMAT_RAW ? SRA_WS_META_FORMAT_NONE : $this->_format) : NULL);
    $this->_offset = isset($conf['attributes']['offset']) ? $conf['attributes']['offset'] : NULL;
    $this->_query = SRA_Util::convertBoolean(isset($conf['attributes']['query']) ? $conf['attributes']['query'] : NULL);
    if (isset($conf['ws-param'])) {
			foreach (array_keys($conf['ws-param']) as $pkey) {
				if (!SRA_WSParam::isValid($param = new SRA_WSParam($conf['ws-param'][$pkey]))) {
          $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
          break;
        }
        else {
          $this->_params[$param->_name] = $param;
        }
			}
    }
    $key = $this->_action == SRA_WS_REQUEST_CREATE ? 'ws-create' : ($this->_action == SRA_WS_REQUEST_DELETE ? 'ws-delete' : ($this->_action == SRA_WS_REQUEST_UPDATE ? 'ws-update' : 'ws-retrieve'));
    $this->_password = isset($conf['attributes']['password']) ? $conf['attributes']['password'] : NULL;
    $this->_primaryKey = $this->_action != SRA_WS_REQUEST_CREATE && count($tmp = array_keys($conf[$key])) && isset($tmp[0]) ? (is_numeric($tmp[0]) ? $tmp[0]*1 : $tmp[0]) : NULL;
    $this->_requestId = isset($conf['attributes']['request-id']) ? $conf['attributes']['request-id'] : NULL;
    $this->_requestId1 = isset($conf['attributes']['request-id1']) ? $conf['attributes']['request-id1'] : NULL;
    $this->_sessionId = isset($conf['attributes']['session-id']) ? $conf['attributes']['session-id'] : NULL;
    $this->_timeFormat = isset($conf['attributes']['time-format']) ? $conf['attributes']['time-format'] : NULL;
    $this->_callback = isset($conf['attributes']['callback']) ? $conf['attributes']['callback'] : NULL;
    $this->_jsDates = !$this->_dateFormat && !$this->_timeFormat ? SRA_Util::convertBoolean(isset($conf['attributes']['js-dates']) ? $conf['attributes']['js-dates'] : NULL, FALSE) : FALSE;
    $this->_useSessions = SRA_Util::convertBoolean(isset($conf['attributes']['use-sessions']) ? $conf['attributes']['use-sessions'] : NULL);
    $this->_user = isset($conf['attributes']['user']) ? $conf['attributes']['user'] : NULL;
    $this->_validators = isset($conf['attributes']['validators']) ? explode(strpos($conf['attributes']['validators'], ',') ? ',' : ' ', $conf['attributes']['validators']) : array();
    $this->_workflowId = $this->_action == SRA_WS_REQUEST_RETRIEVE && isset($this->_primaryKey) && isset($conf[$key][$this->_primaryKey]['attributes']['workflow-id']) ? $conf[$key][$this->_primaryKey]['attributes']['workflow-id']*1 : NULL;
    if (($this->_action == SRA_WS_REQUEST_CREATE || $this->_action == SRA_WS_REQUEST_UPDATE) && count($tmp = array_keys($conf[$key]))) {
      if (isset($conf[$key][$tmp[0]]['ws-param'])) {
        foreach (array_keys($conf[$key][$tmp[0]]['ws-param']) as $pkey) {
          if (!SRA_WSParam::isValid($param = new SRA_WSParam($conf[$key][$tmp[0]]['ws-param'][$pkey]))) {
            $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
            break;
          }
          else {
            if (isset($this->_params[$param->_name])) {
              if (!is_array($this->_params[$param->_name]->_value)) $this->_params[$param->_name]->_value = array($this->_params[$param->_name]->_value);
              $this->_params[$param->_name]->_value[] = $param->_value;
            }
            else {
              $this->_params[$param->_name] = $param;
            }
          }
        }
      }
    }
	}
	// }}}
  
  
	// {{{ getService
	/**
	 * prints the response of this request using the format and meta-format 
   * specified. returns TRUE on success, FALSE otherwise. this method should be 
   * invoked after 'process'
	 * @access public
	 * @return void
	 */
  function &getService() {
    if (!$this->_service && $this->_id != SRA_WS_GATEWAY_NOOP && $this->_id && ($this->_service =& SRA_WSGateway::getService($this->_id))) {
      $this->_global = SRA_WSGlobal::isValid($this->_service);
    }
    return $this->_service;
  }
  // }}}
  
  
	// {{{ process
	/**
	 * processes this request. returns TRUE on success, FALSE otherwise. if 
   * unsuccessful, a corresponding SRA_WS_REQUEST_STATUS_* code will be set to 
   * $this->_status
	 * @access public
	 * @return boolean
	 */
	function process() {
		if (!$this->validate()) { return TRUE; }
    if (isset($this->_processed)) { return $this->_processed; }
    
    $this->_processed = TRUE;
    
    if ($this->_id != SRA_WS_GATEWAY_NOOP && $this->_global) {
      switch ($this->_service->_type) {
        case SRA_WS_GLOBAL_TYPE_METHOD:
          $_params = $tmp = array($this->_attrs);
          if (isset($this->_limit)) $_params[] = $this->_limit;
          if (isset($this->_offset)) $_params[] = $this->_offset;
          if (SRA_Error::isError($results = SRA_Util::invokeStaticMethodPath($this->_service->_identifier, $_params))) {
            $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
            $this->_methodErrorMsg = $results->getErrorMessage();
          }
          
          // set result count
          if (is_array($results) && isset($results[SRA_WS_RESULT_COUNT_KEY])) {
            $this->_resultCount = $results[SRA_WS_RESULT_COUNT_KEY];
            unset($results[SRA_WS_RESULT_COUNT_KEY]);
          }
          else if (is_array($results)) {
            $this->_resultCount = count($results);
          }
          $this->_results =& $results;
          break;
        case SRA_WS_GLOBAL_TYPE_SQL:
          $sql = $this->_service->_identifier;
          $keys = array_keys($this->_attrs);
          foreach($keys as $key) {
            $sql = str_replace('#' . $key . '#', $this->_attrs[$key], $sql);
          }
          $db =& SRA_Controller::getAppDb();
          if (SRA_Util::beginsWith(strtolower(trim($sql)), 'select')) {
            $this->_results =& $db->fetch($sql, NULL, $this->_limit, $this->_offset);
            $this->_isSelectQuery = TRUE;
          }
          else {
            $this->_results =& $db->execute($sql);
          }
          if (!SRA_Error::isError($this->_results)) {
            $this->_resultCount = SRA_ResultSet::isValid($this->_results) ? $this->_results->getTotalCount() : $this->_results->getNumRowsAffected();
            if ($this->_isSelectQuery) {
              $results = array();
              while($row =& $this->_results->next(TRUE)) {
                $results[] = $row;
              }
              $this->_results =& $results;
            }
          }
          else {
            $msg = "SRA_WSGlobal::process: Failed - sql code '${sql}' failed to execute on db server for service " . $this->_id;
            SRA_Error::logError($msg, __FILE__, __LINE__);
            $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
            $this->_processed = FALSE;
          }
          break;
        case SRA_WS_GLOBAL_TYPE_RB:
          $this->_results = array();
          if (SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle($this->_service->_identifier))) {
            if (isset($this->_attrs['keys'])) {
              $keys = explode(' ', $this->_attrs['keys']);
              foreach($keys as $key) {
                $this->_results[$key] = $rb->getString($key);
              }
            }
            else {
              foreach($rb->getKeys() as $key) {
                $add = TRUE;
                $str = $rb->getString($key);
                if (isset($this->_attrs['search'])) {
                  $add = strpos(strtolower($str), strtolower($this->_attrs['search']->_value)) !== FALSE;
                }
                if ($add && isset($this->_attrs['beginsWith'])) {
                  $add = SRA_Util::beginsWith($str, $this->_attrs['beginsWith']->_value, FALSE);
                }
                if ($add && isset($this->_attrs['endsWith'])) {
                  $add = SRA_Util::endsWith($str, $this->_attrs['endsWith']->_value, FALSE);
                }
                if ($add) {
                  $this->_results[$key] = $str;
                  if ($this->_limit && count($this->_results) == $this->_limit) {
                    break;
                  }
                }
              }
            }
            $this->_resultCount = count($this->_results);
          }
          else {
            $msg = 'SRA_WSGlobal::process: Failed - resource bundle "' . $this->_service->_identifier . '" is not valid for service ' . $this->_id;
            SRA_Error::logError($msg, __FILE__, __LINE__);
            $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
            $this->_processed = FALSE;
          }
          break;
      }
    }
    else if ($this->_id != SRA_WS_GATEWAY_NOOP) {
      // retrieve
      if ($this->_action == SRA_WS_REQUEST_RETRIEVE) {
        if (!$this->_entities) {
          // sql dao
          if (method_exists($this->_dao, 'getTableName')) {
            require_once('model/SRA_QueryBuilder.php');
            // construct query constraint groups if not already done
            if ($this->_constraintGroups && !$this->_queryConstraintGroups) {
            	foreach(array_keys($this->_constraintGroups) as $i) {
            		$this->_queryConstraintGroups[] =& $this->_constraintGroups[$i]->toQueryConstraintGroup();
            		unset($this->_constraintGroups[$i]);
            	}
            }
            $queryBuilder = new SRA_QueryBuilder($this->_service->_entity, $this->_queryConstraintGroups, $this->_limit, $this->_offset);
            $this->_entities =& $queryBuilder->getEntities();
          }
          // non-sql dao
          else {
            $this->_entities = array();
            $tmp =& $this->_dao->findAll();
            foreach(array_keys($tmp) as $tkey) {
              $this->_entities[] =& $tmp[$tkey];
            }
            if (!$this->_entities) { $this->_entities = NULL; }
          }
          
          if (SRA_Error::isError($this->_entities)) {
            $msg = 'SRA_WS::process: Failed - unable to get entities from query builder for service ' . $this->_id;
            SRA_Error::logError($msg, __FILE__, __LINE__);
            $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
            $this->_processed = FALSE;
          }
          $baseCount = count($this->_entities);
          if ($this->_constraintGroups) {
            $updateEntities = FALSE;
            
            // apply non-sql sorting constraints
            if (!method_exists($this->_dao, 'getTableName')) {
              foreach(array_keys($this->_constraintGroups) as $ckey) {
                $this->_entities =& $this->_constraintGroups[$ckey]->applySortingConstraints($this->_entities);
                $updateEntities = TRUE;
              }
            }
            
            foreach(array_keys($this->_entities) as $key) {
              foreach(array_keys($this->_constraintGroups) as $ckey) {
                if (!$this->_constraintGroups[$ckey]->evaluateAllConstraints($this->_entities[$key], !method_exists($this->_dao, 'getTableName'))) {
                  unset($this->_entities[$key]);
                  $updateEntities = TRUE;
                }
              }
            }
            
            if ($updateEntities) {
              $tmp = array();
              foreach(array_keys($this->_entities) as $key) {
                $tmp[] =& $this->_entities[$key];
              }
              $this->_entities =& $tmp;
            }
          }
          if (!method_exists($this->_dao, 'getTableName')) {
            $this->_resultCount = count($this->_entities);
            if (isset($this->_limit) || isset($this->_offset)) {
              $this->_entities =& SRA_Util::applyLimitOffset($this->_entities, $this->_limit, $this->_offset);
            }
          }
          else {
            $this->_resultCount = method_exists($this->_dao, 'getTableName') ? $queryBuilder->getResultCount() : $this->_resultCount;
            $this->_resultCount = $this->_resultCount - ($baseCount - count($this->_entities));
          }
        }
        else {
          $this->_resultsCount = count($this->_entities);
        }
      }
      // create, update or delete
      else if ($this->_entities[0]) {
        switch($this->_action) {
          case SRA_WS_REQUEST_CREATE:
            $results = $this->_dao->insert($this->_entities[0], $this->_service->_subEntities);
            break;
          case SRA_WS_REQUEST_DELETE:
            $results = $this->_dao->delete($this->_entities[0]);
            break;
          case SRA_WS_REQUEST_UPDATE:
            $results = $this->_entities[0]->isDirty() ? $this->_dao->update($this->_entities[0], $this->_service->_subEntities) : TRUE;
            break;
        }
        if (SRA_Error::isError($results) || !$results) {
          $msg = 'SRA_WS::process: Failed - unable to perform requested action ' . $this->_action . ' for entity ' . $this->_service->_entity . '/' . $this->_primaryKey . ' and service ' . $this->_id;
          SRA_Error::logError($msg, __FILE__, __LINE__);
          $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
          $this->_processed = FALSE;
        }
        else {
          $this->_resultCount = $this->_action != SRA_WS_REQUEST_DELETE ? 1 : 0;
        }
      }
      else {
        
      }
    }
    else {
      $this->_global = TRUE;
      if ($this->_metaFormat != SRA_WS_META_FORMAT_JSON && $this->_metaFormat != SRA_WS_META_FORMAT_XML && $this->_metaFormat != SRA_WS_META_FORMAT_NONE) {
        $this->_status = SRA_WS_REQUEST_STATUS_INVALID_META_FORMAT;
        $this->_metaFormat = SRA_WS_META_FORMAT_XML;
      }
      if ($this->_format != SRA_WS_FORMAT_JSON && $this->_format != SRA_WS_FORMAT_RAW && $this->_format != SRA_WS_FORMAT_XML) {
        $this->_status = SRA_WS_REQUEST_STATUS_INVALID_FORMAT;
        $this->_format = SRA_WS_FORMAT_XML;
      }
    }
    
    return $this->_processed;
	}
	// }}}
  
  
	// {{{ printResponse
	/**
	 * prints the response of this request using the format and meta-format 
   * specified. returns TRUE on success, FALSE otherwise. this method should be 
   * invoked after 'process'
	 * @access public
	 * @return void
	 */
  function printResponse() {
    global $_utilAttrToXmlSoap;
    
    $_utilAttrToXmlSoap = $this->_isSoap;
    if (!$this->_format) $this->_format = $this->_isSoap ? SRA_WS_FORMAT_XML : SRA_WS_FORMAT_JSON;
    if (!$this->_metaFormat) $this->_metaFormat = $this->_format == SRA_WS_FORMAT_RAW ? SRA_WS_META_FORMAT_NONE : $this->_format;
    
    $tpl =& SRA_Controller::getSysTemplate();
    if ($this->_id != SRA_WS_GATEWAY_NOOP && $this->_status == SRA_WS_REQUEST_STATUS_SUCCESS) {
      global $_utilDateFormat;
      global $_utilTimeFormat;
      $_utilDateFormat = $this->_dateFormat ? $this->_dateFormat : $this->_timeFormat;
      $_utilTimeFormat = $this->_timeFormat;
      if ($this->_entities && ($this->_service->_view || ($this->_format == SRA_WS_FORMAT_JSON && $this->_service->_viewJson) || ($this->_format == SRA_WS_FORMAT_XML && $this->_service->_viewXml))) {
        $tpl->assignByRef('resources', SRA_Controller::getAppResources());
        foreach(array_keys($this->_entities) as $key) {
          ob_start();
          $this->_entities[$key]->render(($this->_format == SRA_WS_FORMAT_JSON && $this->_service->_viewJson ? $this->_service->_viewJson : ($this->_format == SRA_WS_FORMAT_XML && $this->_service->_viewXml ? $this->_service->_viewXml : $this->_service->_view)), $this->_includeAttrs, $this->_excludeAttrs);
          $this->_entities[$key] = ob_get_contents();
          ob_end_clean();
        }
      }
      
      if ($this->_global) {
        $tpl->assignByRef('response', $this->_results);
      }
      else if (!$this->_global && isset($this->_entities[0]) && (isset($this->_primaryKey) || $this->_action == SRA_WS_REQUEST_CREATE) && $this->_action != SRA_WS_REQUEST_DELETE) {
        $this->_resultCount = 1;
        $tpl->assignByRef('response', $this->_entities[0]); 
      }
      else if (!$this->_global && $this->_entities && $this->_action != SRA_WS_REQUEST_DELETE) {
        $tpl->assignByRef('response', $this->_entities);
      }
      // add primary key to include attributes
      if ($this->_includeAttrs && $this->_results && ((is_object($this->_results) && method_exists($this->_results, 'getPrimaryKeyAttribute') && ($pkName = $this->_results->getPrimaryKeyAttribute())) || (is_array($this->_results) && ($keys = array_keys($this->_results)) && method_exists($this->_results[$keys[0]], 'getPrimaryKeyAttribute') && ($pkName = $this->_results[$keys[0]]->getPrimaryKeyAttribute())))) {
        if (in_array(SRA_WS_REQUEST_PK, $this->_includeAttrs)) {
          foreach(array_keys($this->_includeAttrs) as $ikey) {
            if ($this->_includeAttrs[$ikey] == SRA_WS_REQUEST_PK) { $this->_includeAttrs[$ikey] = $pkName; }
          }
        }
        if (!in_array($pkName , $this->_includeAttrs)) { $this->_includeAttrs[] = $pkName; }
      }
    }
    $tpl->assignByRef('request', $this);
    $tpl->assign('isJquery', isset($_SERVER['HTTP_X_REQUESTED_WITH]']) && $_SERVER['HTTP_X_REQUESTED_WITH]'] == 'XMLHttpRequest');
    if ($this->_id != SRA_WS_GATEWAY_NOOP) { $tpl->assignByRef('service', $this->_service); }
    $output = $tpl->fetch($this->_metaFormat == SRA_WS_META_FORMAT_JSON ? 'model/sra-ws-response-json.tpl' : ($this->_metaFormat == SRA_WS_META_FORMAT_XML ? 'model/sra-ws-response-xml.tpl' : 'model/sra-ws-response-raw.tpl'));
    
    if ($this->_isSoap) {
      $tpl->assignByRef('soapBody', $output);
      $tpl->display('model/sra-soap-response.tpl');
    }
    else {
      header('Content-type: ' . ($this->_metaFormat == SRA_WS_META_FORMAT_JSON ? 'application/javascript' : ($this->_metaFormat == SRA_WS_META_FORMAT_XML ? 'text/xml' : 'text/plain')));
      echo $output;
    }
  }
  // }}}
  
  
	// {{{ validate
	/**
	 * returns TRUE if this request is valid, FALSE otherwise. if it is not valid, 
   * a corresponding SRA_WS_REQUEST_STATUS_* code will be set to $this->_status
	 * @access public
	 * @return boolean
	 */
	function validate() {
    if ($this->_status) { return $this->_status == SRA_WS_REQUEST_STATUS_SUCCESS; }
    
    // sessions
    if (!$this->_useSessions && ($this->_sessionId || $this->_asynchronous)) { $this->_useSessions = TRUE; }
    if ($this->_useSessions) {
      if ($this->_sessionId) { $_COOKIE[session_name()] = $this->_sessionId; }
      session_start();
      $this->_sessionId = session_id();
      if (!$this->_user && $_SESSION['ws-user']) { 
        $this->_user = $_SESSION['ws-user']; 
      }
      else if ($this->_user) {
        $_SESSION['ws-user'] = $this->_user;
      }
      if (!$this->_password && $_SESSION['ws-password']) { 
        $this->_password = $_SESSION['ws-password']; 
      }
      else if ($this->_password) {
        $_SESSION['ws-password'] = $this->_password;
      }
    }
    
    // constraint groups
    if ($this->_constraintGroups) {
      foreach (array_keys($this->_constraintGroups) as $gid) {
        if (!$this->_constraintGroups[$gid]->evaluateNonQueryConstraints()) {
          $msg = 'SRA_WSRequest: Failed - non query constraint group returned FALSE for group ' . $gid . ' and service ' . $this->_id;
          SRA_Error::logError($msg, __FILE__, __LINE__);
          $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
          break;
        }
      }
    }
    // invalid app
    if (!$this->_status && (!$this->_app || !SRA_Controller::appKeyIsValid($this->_app) || !SRA_Controller::init($this->_app, TRUE, FALSE, TRUE, TRUE))) {
      $this->_status = SRA_WS_REQUEST_STATUS_INVALID_APP;
    }
    
    if ($this->_password) { $_SERVER['PHP_AUTH_PW'] = $this->_password; }
    if ($this->_user) { $_SERVER['PHP_AUTH_USER'] = $this->_user; }
    
    // validate service
    if ($this->_id != SRA_WS_GATEWAY_NOOP && $this->_id && ($this->_service =& SRA_WSGateway::getService($this->_id)) && (SRA_WS::isValid($this->_service) || SRA_WSGlobal::isValid($this->_service))) {
      if (SRA_WS::isValid($this->_service)) {
        // merge constraint groups
        $keys = array_keys($this->_service->_constraintGroups);
        foreach($keys as $key) {
          $this->_constraintGroups[] =& $this->_service->_constraintGroups[$key];
        }
        
        // excludeAttrs
        if ($this->_service->_excludeAttrs) $this->_excludeAttrs = array_merge($this->_excludeAttrs, $this->_service->_excludeAttrs);
        
        // includeAttrs
        if (count($this->_service->_includeAttrs)) {
          $include = array();
          foreach($this->_service->_includeAttrs as $attr) {
            if (!$this->_includeAttrs || in_array($attr, $this->_includeAttrs)) {
              $include[] = $attr;
            }
          }
          if ($this->_includeAttrs && array_diff($this->_includeAttrs, $include) && !$this->_service->_view) $this->_status = SRA_WS_REQUEST_STATUS_INVALID_ATTRS;
          $this->_includeAttrs = $include;
        }
        
        // validators
        $this->_validators = array_merge($this->_validators, $this->_service->_validators);
        
      }
      else if (SRA_WSGlobal::isValid($this->_service)) {
        // merge params
        $keys = array_keys($this->_service->_params);
        foreach($keys as $key) {
          if (!isset($this->_params[$key]) || !$this->_service->_params[$key]->_allowOverride) {
            $this->_params[$key] =& $this->_service->_params[$key];
          }
        }
        $this->_global = TRUE;
      }
      
      // format
      $format = $this->_format && (!$this->_service->_formatFixed || $this->_format == $this->_service->_format) ? $this->_format : ($this->_soap ? SRA_WS_FORMAT_XML : $this->_service->_format);
      if (($this->_format && $format != $this->_format) || ($format != SRA_WS_FORMAT_JSON && $format != SRA_WS_FORMAT_RAW && $format != SRA_WS_FORMAT_XML) || ($this->_global && $this->_service->_type != SRA_WS_GLOBAL_TYPE_METHOD && $format == SRA_WS_FORMAT_RAW)) $this->_status = SRA_WS_REQUEST_STATUS_INVALID_FORMAT;
      $this->_format = $format;
      
      // limit 
      $limit = $this->_limit && (!$this->_service->_limitFixed || $this->_limit <= $this->_service->_limit) ? $this->_limit : $this->_service->_limit;
      if ($this->_limit && $limit != $this->_limit) $this->_status = SRA_WS_REQUEST_STATUS_INVALID_LIMIT;
      $this->_limit = $limit;
      
      // meta format
      $metaFormat = $this->_metaFormat && (!$this->_service->_metaFormatFixed || $this->_metaFormat == $this->_service->_metaFormat) ? $this->_metaFormat : $this->_service->_metaFormat;
      if (($this->_metaFormat && $metaFormat != $this->_metaFormat) || ($metaFormat != SRA_WS_META_FORMAT_JSON && $metaFormat != SRA_WS_META_FORMAT_NONE && $metaFormat != SRA_WS_META_FORMAT_XML)) $this->_status = SRA_WS_REQUEST_STATUS_INVALID_META_FORMAT;
      $this->_metaFormat = $metaFormat;
      
      if ($this->_service->_authenticate) {
        require_once('auth/SRA_Authenticator.php');
        if (!SRA_Authenticator::authenticate(SRA_Controller::getSysConf(), SRA_Controller::getAppConf())) {
          if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->_status = SRA_WS_REQUEST_STATUS_AUTH_FAILED;
          }
          else {
            global $_wsGateway;
            $oldGateway = $_wsGateway;
            $_wsGateway = NULL;
            SRA_Authenticator::authenticate(SRA_Controller::getSysConf(), SRA_Controller::getAppConf());
            $_wsGateway =& $oldGateway;
          }
        }
      }
    }
    else if ($this->_id != SRA_WS_GATEWAY_NOOP) {
      $this->_format = $this->_format ? $this->_format : ($this->_soap ? SRA_WS_FORMAT_XML : SRA_WS_FORMAT_JSON);
      $this->_metaFormat = $this->_metaFormat ? $this->_metaFormat : $this->_format;
      $this->_service = NULL;
      $this->_status = SRA_WS_REQUEST_STATUS_INVALID_SERVICE;
    }
    else if ($this->_id == SRA_WS_GATEWAY_NOOP) {
      require_once('auth/SRA_Authenticator.php');
      if (!SRA_Authenticator::authenticate(SRA_Controller::getSysConf(), SRA_Controller::getAppConf())) {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
          $this->_status = SRA_WS_REQUEST_STATUS_AUTH_FAILED;
        }
        else {
          global $_wsGateway;
          $oldGateway = $_wsGateway;
          $_wsGateway = NULL;
          SRA_Authenticator::authenticate(SRA_Controller::getSysConf(), SRA_Controller::getAppConf());
          $_wsGateway =& $oldGateway;
        }
      }
    }
    // translate params to attributes
    if ($this->_params) {
      foreach(array_keys($this->_params) as $pkey) {
        if (isset($this->_attrs[$this->_params[$pkey]->_name])) {
          if (!is_array($this->_attrs[$this->_params[$pkey]->_name])) { $this->_attrs[$this->_params[$pkey]->_name] = array($this->_attrs[$this->_params[$pkey]->_name]); }
          $this->_attrs[$this->_params[$pkey]->_name][] = SRA_WS::findValue($this->_params[$pkey]->_value, $this->_params[$pkey]->_valueType);
        }
        else {
          $this->_attrs[$this->_params[$pkey]->_name] = SRA_WS::findValue($this->_params[$pkey]->_value, $this->_params[$pkey]->_valueType);
        }
      }
    }
    // missing primary key
    if (!$this->_status && !$this->_global && ($this->_action == SRA_WS_REQUEST_UPDATE || $this->_action == SRA_WS_REQUEST_DELETE) && !isset($this->_primaryKey)) {
      $msg = 'SRA_WSRequest: Failed - primary key was not specified for an update or delete service invocation for service ' . $this->_id;
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
    }
    // validate dao/entity
    if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && !$this->_global && SRA_Error::isError($this->_dao =& SRA_DaoFactory::getDao($this->_service->_entity))) {
      $msg = 'SRA_WSRequest: Failed - unable to obtain a reference to the ' . $this->_service->_entity . ' dao for service ' . $this->_id;
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
    }
    // lookup entity instance and validate access (if applicable)
    if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && !$this->_global && isset($this->_primaryKey) && $this->_queryConstraintGroups && method_exists($this->_dao, 'getTableName')) {
      require_once('model/SRA_QueryBuilderConstraintGroup.php');
      $this->_queryConstraintGroups[] = new SRA_QueryBuilderConstraintGroup(array(new SRA_QueryBuilderConstraint($this->_dao->getPkName(), SRA_QUERY_BUILDER_CONSTRAINT_TYPE_EQUAL, $this->_primaryKey)));
      $queryBuilder = new SRA_QueryBuilder($this->_service->_entity, $this->_queryConstraintGroups, 1, 0);
      if (SRA_Error::isError($this->_entities =& $queryBuilder->getEntities())) {
        $msg = 'SRA_WSRequest: Failed - unable to get entities from query builder for service ' . $this->_id;
        SRA_Error::logError($msg, __FILE__, __LINE__);
        $this->_status = SRA_WS_REQUEST_STATUS_FAILED;
      }
      if ($queryBuilder->getResultCount() != 1) {
        $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
      }
    }
    else if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && !$this->_global && isset($this->_primaryKey) && !$this->_workflowId && !$this->_entities && (SRA_Error::isError($this->_entities[0] =& $this->_dao->findByPk($this->_primaryKey)) || !$this->_entities[0])) {
      $msg = 'SRA_WSRequest: Failed - unable to obtain a reference to entity instance ' . $this->_primaryKey . ' for entity ' . $this->_service->_entity;
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->_status = SRA_WS_REQUEST_STATUS_INVALID_PK;
    }
    else if ($this->_action == SRA_WS_REQUEST_RETRIEVE && isset($this->_primaryKey) && $this->_workflowId) {
      require_once('workflow/SRA_WorkflowManager.php');
      if (SRA_Error::isError($wf =& SRA_WorkflowManager::resumeWorkflow($this->_workflowId)) || !$wf || SRA_Error::isError($this->_entities[0] =& $wf->getEntityObj($this->_primaryKey)) || !$this->_entities[0]) {
        $msg = 'SRA_WSRequest: Failed - Invalid workflow id ' . $this->_workflowId . ' or entity id ' . $this->_primaryKey . ' for web service ' . $this->_id;
        SRA_Error::logError($msg, __FILE__, __LINE__);
        $this->_status = SRA_WS_REQUEST_STATUS_INVALID_REQUEST;
      }
    }
    else if (!$this->_status && $this->_action == SRA_WS_REQUEST_CREATE) {
      $this->_entities[0] = $this->_dao->newInstance();
    }
    // validate action
    if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && !$this->_global && (($this->_action == SRA_WS_REQUEST_CREATE && !$this->_service->_create) || ($this->_action == SRA_WS_REQUEST_DELETE && !$this->_service->_delete) || ($this->_action == SRA_WS_REQUEST_RETRIEVE && !$this->_service->_retrieve) || ($this->_action == SRA_WS_REQUEST_UPDATE && !$this->_service->_update))) {
      $msg = 'SRA_WSRequest: Failed - invalid action ' . $this->_action . ' request for service ' . $this->_id;
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->_status = SRA_WS_REQUEST_STATUS_NOT_ALLOWED;
    }
    // substitute in includeAttrs
    if ($this->_dao && $this->_includeAttrs && method_exists($this->_dao, 'getPkName')) {
      if (!in_array(SRA_WS_REQUEST_PK, $this->_includeAttrs) && !in_array(SRA_WS_REQUEST_PK, $this->_dao->getPkName())) $this->_includeAttrs[] = SRA_WS_REQUEST_PK;
      foreach(array_keys($this->_includeAttrs) as $key) {
        if ($this->_includeAttrs[$key] == SRA_WS_REQUEST_PK) {
          $this->_includeAttrs[$key] = $this->_dao->getPkName();
        }
      }
    }
    // validate attrs
    if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && $this->_attrs) {
      foreach(array_keys($this->_attrs) as $key) {
        $pieces = explode('_', $key);
        $attr = $pieces[0];
        if (!$this->_service->_view && (($this->_entities[0] && ($this->_entities[0]->isAttributeReadOnly($attr) || !$this->_entities[0]->isAttribute($attr))) || (count($this->_service->_includeAttrs) && !in_array($attr, $this->_service->_includeAttrs)) || (count($this->_service->_excludeAttrs) && in_array($attr, $this->_service->_excludeAttrs)))) {
          $this->_status = SRA_WS_REQUEST_STATUS_INVALID_ATTRS;
        }
      }
    }
    // set attributes and invoke validators
    if (!$this->_status && $this->_entities[0] && ($this->_action == SRA_WS_REQUEST_CREATE || $this->_action == SRA_WS_REQUEST_UPDATE || $this->_action == SRA_WS_REQUEST_DELETE)) {
      if ($this->_attrs) { $this->_entities[0]->setAttributes($this->_attrs); }
      if ($this->_validators) { $this->_entities[0]->validate($this->_validators); }
      $this->_action == SRA_WS_REQUEST_DELETE ? $this->_entities[0]->validateDelete() : $this->_entities[0]->validate();
      if ($this->_entities[0]->validateErrors) { $this->_status = SRA_WS_REQUEST_STATUS_INVALID_INPUT; }
      $tpl =& SRA_Controller::getSysTemplate();
      $tpl->assignByRef('entity', $this->_entities[0]);
    }
    // validate IP address (if applicable)
    if ($this->_id != SRA_WS_GATEWAY_NOOP && !$this->_status && $this->_service->_ipAuthenticator && !SRA_Util::invokeStaticMethodPath($this->_service->_ipAuthenticator, $params = array($_SERVER['REMOTE_ADDR']))) {
      $this->_status = SRA_WS_REQUEST_STATUS_IP_NOT_ALLOWED;
    }
    
    if (!$this->_status) { 
      $this->_status = SRA_WS_REQUEST_STATUS_SUCCESS;
      return TRUE;
    }
    else {
      return FALSE;
    }
	}
	// }}}
  
  
	// Static methods
	
	// {{{ isValid
	/**
	 * returns TRUE if $object is a SRA_WSRequest object
	 * @param mixed $object The object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_wsrequest');
	}
	// }}}
	
}
// }}}
?>
