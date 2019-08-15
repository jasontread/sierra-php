<?php
/*
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | SIERRA : PHP Application Framework  http://code.google.com/p/sierra-php |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | Copyright 2017 Jason Read                                               |
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

/**
 * the regular expression to use when conventionally checking for API 
 * controller source files
 */
define('SRA_API_ROUTER_CONTROLLER_SOURCE_FILE_REGEX', '/controller.*\.php/i');

/**
 * the regular expression to use for matching version directories
 */
define('SRA_API_ROUTER_CONTROLLER_VERSION_DIR_REGEX', '/^[vV]([0-9\.]+)([a-zA-Z]?[a-zA-Z0-9]*)$/');

/**
 * whether or not to enable caching of method responses (override and turn off for debugging)
 */
if (!defined('SRA_API_ROUTER_CONTROLLER_USE_CACHE')) define('SRA_API_ROUTER_CONTROLLER_USE_CACHE', TRUE);

/**
 * default value for @api-key-name
 */
define('SRA_API_ROUTER_DEFAULT_API_KEY_NAME', 'api-key');

/**
 * API router cache key prefix
 */
define('SRA_API_ROUTER_CACHE_PREFIX', 'sra_api_cache_');

/**
 * Cache TTL for API initialization details
 */
define('SRA_API_ROUTER_INIT_CACHE_TTL', 86400);


/**
 * This class is used to quickly create, deploy and document APIs.
 * Full usage documentation is provided in 
 * https://github.com/cloudharmony/sierra-php/wiki/Creating-and-Deploying-an-API
 */
class SRA_ApiRouter {
  
  /**
   * set to true if the Access-Control-Allow-Methods has been sent
   */
  var $_accessControlAllowMethodsSent;

	/**
	 * the name of the controller class
	 * @type string
	 */
	var $_class;
	
	/**
	 * reference to an instance of the controller
	 * @type object
	 */
	var $_controller;
	
	/**
	 * array containing paths to the source files of the controller and member 
	 * controller attributes
	 * @type array
	 */
	var $_controllerIncludes;
	
	/**
	 * catch all fail status code
	 */
	var $_failStatus = 500;
	
	/**
	 * whether or not the controller has been initialized using the 
	 * 'initController' method
	 * @type boolean
	 */
	var $_initialized = NULL;
	
	/**
	 * the API methods provided by the controller
	 * @type array
	 */
	var $_methods = array();
	
	/**
	 * the controller settings
	 * @type array
	 */
	var $_settings;
	
	/**
	 * absolute path to the PHP file containing the deployed API controller
	 * @type string
	 */
	var $_source;
	
	/**
	 * Private constructor - use the static singleton method 'create' to obtain
	 * a instance of this class
	 * @param string $controller absolute path to the PHP file containing the API 
	 * controller to deploy
	 */
	private function SRA_ApiRouter($controller=NULL) {
		$this->_source = $controller;
    require_once('util/SRA_Cache.php');
	}
	
	/**
	 * adds a cache key
	 * @param string $ckey the cache key to add
	 * @param int $ttl the TTL for this cache key
	 * @return void
	 */
	private function addCacheKey($ckey, $ttl) {
	  $key = sprintf('%s%s_keys', SRA_API_ROUTER_CACHE_PREFIX, SRA_Controller::getCurrentAppId());
	  $ckeys =& SRA_Cache::getCache($key);
	  if (!is_array($ckeys)) $ckeys = array();
	  $ckeys[$ckey] = time() + $ttl;
	  $maxTtl = 0;
	  //  purge any expired keys
	  foreach(array_keys($ckeys) as $i) {
	    $diff = $ckeys[$i] - time();
	    if ($diff <= 0) unset($ckeys[$i]);
	    else if ($diff > $maxTtl) $maxTtl = $diff;
	  }
	  if ($ckeys) SRA_Cache::setCache($key, $ckeys, $maxTtl);
	  else if (SRA_Cache::cacheIsset($key)) SRA_Cache::deleteCache($key);
	}
	
	/**
	 * adds a cache key
	 * @param string $ckey the cache key to add
	 * @param int $ttl the TTL for this cache key
	 * @return void
	 */
	private function purgeCache() {
	  $key = sprintf('%s%s_keys', SRA_API_ROUTER_CACHE_PREFIX, SRA_Controller::getCurrentAppId());
	  if (is_array($ckeys =& SRA_Cache::getCache($key))) {
  	  foreach(array_keys($ckeys) as $ckey) {
  	    if (SRA_Cache::cacheIsset($ckey)) SRA_Cache::deleteCache($ckey);
      } 
	  }
    if (SRA_Cache::cacheIsset($key)) SRA_Cache::deleteCache($key);
	}
	
	/**
	 * checks if an entities has a date or time attribute in sub-entities
	 * @param array $entities has containing all of the entities
	 * @param string $entity the entity to check
	 * @return boolean
	 */
	private function checkForSubDateOrTime(&$entities, $entity) {
		static $_checkStack;
		if (!isset($_checkStack)) $_checkStack = array();
		$hasDateOrTime = FALSE;
		if (!isset($_checkStack[$entity])) {
			$_checkStack[$entity] = TRUE;
			foreach(array_keys($entities[$entity]['attributes']) as $attr) {
				if ($entities[$entity]['attributes'][$attr]['entity'] && ($entities[$entities[$entity]['attributes'][$attr]['type']]['hasDate'] || $this->checkForSubDateOrTime($entities, $entities[$entity]['attributes'][$attr]['type']))) {
					$hasDateOrTime = TRUE;
					break;
				}
			}
			unset($_checkStack[$entity]);
		}
		return $hasDateOrTime;
	}
	
	/**
	 * cleans a json object string and returns the cleaned value - this method 
	 * removes unwanted attributes from the json string by applying entity-include
	 * and entity-exclude and removing null values
	 * @param mixed $json the json to clean
	 * @param boolean $beautify whether or not to beautify the resulting json 
	 * string
	 * @param int $round rounding precision
	 * @param boolean $retObj used for recursive calls - causes return value to 
	 * be an object instead of json
	 * @return string
	 */
	private function &cleanJson(&$json, $beautify=FALSE, $round=2, $retObj=FALSE) {
	  if (is_string($json)) $json = str_replace('{ "type"', '{ "_type"', $json);
		$cleaned =& $json;
		if (is_object($json) || preg_match('/^\s*[\{\[].*[\}\]]\s*$/', $cleaned)) {
			if (is_object($json)) $obj =& $json;
			else $obj = json_decode($json);
			
      if ($json && !$obj) {
        // try to strip out odd characters
        $json = preg_replace('/[^(\x20-\x7F)]*/','', $json);
        if (($obj = json_decode($json)) === NULL) {
  				$msg = 'SRA_ApiRouter::cleanJson - Error: unable to decode json ' . $json;
  				SRA_Error::logError($msg, __FILE__, __LINE__); 
        }
      }
      
      if (is_array($obj)) {
        foreach(array_keys($obj) as $i) $obj[$i] =& $this->cleanJson($obj[$i], $beautify, $round, TRUE);
      }
			else if ($type = isset($obj->_type) ? $obj->_type : NULL) {
				$include = isset($this->_settings['entity-include'][$type]) ? $this->_settings['entity-include'][$type] : NULL;
				$exclude = isset($this->_settings['entity-exclude'][$type]) ? $this->_settings['entity-exclude'][$type] : NULL;
				unset($obj->_type);
				foreach(array_keys(get_object_vars($obj)) as $name) {
					if ($obj->${name} === NULL || ($include && !in_array($name, $include)) || ($exclude && in_array($name, $exclude))) unset($obj->${name});
					else {
						$attr =& $obj->${name};
						
						// array that has been converted to an object
						if (is_object($attr) && ($vars = array_keys(get_object_vars($attr)))) {
						  $isSubObject = TRUE;
						  foreach($vars as $v) {
						    $sattr =& $attr->${v};
						    if (!is_object($sattr) || !($sattr->_type)) {
						      $isSubObject = FALSE;
						      break;
						    }
						  }
						  if ($isSubObject) {
						    $nattr = array();
  						  foreach($vars as $v) array_push($nattr, $attr->${v});
  						  $obj->${name} = $nattr;
                $attr =& $nattr;
						  }
						}
						
						$keys = is_array($attr) ? array_keys($attr) : NULL;
						if (is_array($attr) && count($keys) && is_object($attr[$keys[0]]) && isset($attr[$keys[0]]->_type)) {
							foreach($keys as $i) $this->cleanJson($attr[$i], FALSE, $round, TRUE);
						}
						else if (is_object($attr) && isset($attr->_type)) $this->cleanJson($attr, FALSE, $round, TRUE);
					}
				}
			}
			
			if (!$retObj) {
				$cleaned = json_encode($obj);
				// beautify json
				if ($beautify) $cleaned = SRA_ApiRouter::beautify($cleaned, $round);
			}
			else $cleaned =& $obj;
		}
		return $cleaned;
	}
	
	/**
	 * returns an array containing the entities that should be represented in API
	 * documentation - the return value is indexed by the entity type and the 
	 * value is a hash containing the following keys:
	 *   attributes     array of attributes associated with this entity (indexed 
	 *                  by attribute name)
	 *     array        is this attribute an array
	 *     default      default value for this attribute
	 *     description  attribute description
	 *     entity       is this attribute an entity?
	 *     example      example value for this attribute
	 *     name         name of this attribute
	 *     options      array of options for this attribute
	 *     required     is this attribute required?
	 *     type         attribute data type - one of the following: 
	 *                  string; float; int; bool; date; timestamp; [entity]
	 *     type_label   the type that should be used in documentation (for entity 
	 *                  type attributes only)
	 *   description    entity description
	 *   example        example entity value
	 *   hasDate        does this entity (or a sub-entity) have a date attribute?
	 *   name           the entity name
	 *   type           the entity type
	 *   type_label     the type that should be used in documentation to 
	 *                  reference this entity
	 * @param string $entity optional name of a specific entity to return
	 * @return array
	 */
	public function getEntities($entity=NULL) {
		$entities = array();
		$types = array();
		foreach(array_keys($this->_methods) as $method) {
			if ($this->_methods[$method]['return']['entity']) $this->getEntityConfig($this->_methods[$method]['return']['type'], $entities);
		}
		// check for dates in sub-entities
		foreach(array_keys($entities) as $key) {
			$entities[$key]['hasDate'] = $this->checkForSubDateOrTime($entities, $key);
		}
		
		return $entity ? $entities[$entity] : $entities;
	}
	
	/**
	 * loads the config for $entity and returns an associative array defining
	 * those configurations - where the key in the array is the entity name
	 * (used to load both $entity and any sub-entities)
	 * @param string $entity the name of the entity to return the config for
	 * @param array $config the current load config (for recursive calls)
	 * @return void
	 */
	private function getEntityConfig($entity, &$config) {
		if (!isset($config[$entity])) {
			$dao =& SRA_DaoFactory::getDao($entity, FALSE, FALSE, FALSE, SRA_ERROR_OPERATIONAL);
			$efile = SRA_Controller::getAppLibDir() . '/' . basename(SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR) . "/${entity}.php";
			$obj = NULL;
			
			if (method_exists($dao, 'newInstance')) $obj =& $dao->newInstance();
			else if (file_exists($efile)) {
				require_once($efile);
				if (class_exists($entity)) $obj = new ${entity}();
			}
			
			if (is_object($obj) && method_exists($obj, 'getAttributeNames')) {
				$config[$entity] = array('attributes' => array(), 
				                         'description' => $obj->getHelpContent(), 
				                         'example' => NULL,
				                         'hasDate' => FALSE,
				                         'name' => $obj->getEntityLabel(), 
				                         'type' => $entity,
				                         'type_label' => isset($this->_settings['entity-ref'][$entity]) ? $this->_settings['entity-ref'][$entity] : $entity);
				$include = isset($this->_settings['entity-include'][$entity]) ? $this->_settings['entity-include'][$entity] : NULL;
				$exclude = isset($this->_settings['entity-exclude'][$entity]) ? $this->_settings['entity-exclude'][$entity] : NULL;
				if (isset($this->_settings['entity-example'][$entity])) {
					$example = $this->_settings['entity-example'][$entity];
					if (file_exists($file = SRA_Controller::getAppConfDir() . "/${example}") || 
					    file_exists($file = SRA_Controller::getAppDir() . "/${example}")) $exampole = file_get_contents($file);
					else if (method_exists($dao, $example)) $example = $dao->${example}($include, $exclude);
					else if (method_exists($obj, $example)) $example = $obj->${example}($include, $exclude);
					
					// example is an object
					if (is_object($example)) {
						global $_utilDateFormat;
						global $_utilTimeFormat;
						if (isset($this->_settings['format-date'])) $_utilDateFormat = $this->_settings['format-date'];
						if (isset($this->_settings['format-time'])) $_utilTimeFormat = $this->_settings['format-time'];
						
						if (method_exists('toJson', $example)) $example = $example->toJson($include, $exclude);
						else $example = SRA_Util::toJson($example, $include, $exclude);
					}
					
					$config[$entity]['example'] =& $this->cleanJson($example, TRUE, $this->_settings['beautify-round']);
				}
				foreach($obj->getAttributeNames() as $name) {
					if (($include && !in_array($name, $include)) || ($exclude && in_array($name, $exclude))) continue;
					
					$config[$entity]['attributes'][$name] = array('array' => $obj->getAttributeCardinality($name) ? TRUE : FALSE, 
					                                              'default' => (($def = $obj->getDefaultAttributeValue($name)) != 'NULL' ? $def : NULL),
					                                              'description' => $obj->getHelpContent($name),
					                                              'entity' => $isEntity = $obj->attributeIsEntity($name),
					                                              'example' => preg_match("/example:\s*'(.*)'/msU", $obj->getHelpContent($name), $m) ? $m[1] : NULL,
					                                              'name' => $obj->getEntityLabel($name),
					                                              'options' => $obj->getOptionsMap($name),
					                                              'required' => $obj->isAttributeRequired($name, TRUE),
					                                              'type' => $type = $obj->getAttributeType($name),
					                                              'type_label' => $isEntity && isset($this->_settings['entity-ref'][$type]) ? $this->_settings['entity-ref'][$type] : $type);
          if (in_array($config[$entity]['attributes'][$name]['type'], array('date', 'time'))) $config[$entity]['hasDate'] = TRUE;
				}
				ksort($config[$entity]['attributes']);
				foreach($obj->getAttributeNames() as $name) {
				  if (($include && !in_array($name, $include)) || ($exclude && in_array($name, $exclude))) continue;
					if ($obj->attributeIsEntity($name) && !isset($config[$subEntity = $obj->getAttributeType($name)])) $this->getEntityConfig($subEntity, $config);
				}
			}
		}
		ksort($config);
	}
  
  /**
   * returns the active protocol - http or https
   * @return string
   */
  private function getProto() {
    global $_api_proto;
  
    if (!$_api_proto) {
      $headers = function_exists('getallheaders') ? getallheaders() : array();
      $_api_proto = (isset($headers['CloudFront-Forwarded-Proto']) || isset($_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO']) ? strtolower(isset($_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO']) ? $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] : $headers['CloudFront-Forwarded-Proto']) : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http'));
    }
  
    return $_api_proto;
  }
  
  /**
   * Returns the resource bundle to use for API related strings. Generic API 
   * strings are defined in sierra-php/etc/l10n/api-router.properties. These 
   * strings may be overriden by an application in 
   * [app]/etc/l10n/app.properties
   * @return SRA_ResourceBundle
   */
  public function &getResources() {
    if (!isset($this->_resources)) {
      $this->_resources =& SRA_ResourceBundle::getBundle(dirname(dirname(dirname(__FILE__))) . '/etc/l10n/api-router');
      $appResources =& SRA_Controller::getAppResources();
      $data =& $this->_resources->getData();
      $appData =& $appResources->getData();
      foreach(array_keys($data) as $key) {
        if (isset($appData[$key])) $data[$key] = $appData[$key];
      }
      $this->_resources->_data =& $data;
    }
    return $this->_resources;
  }
  
  /**
   * returns the server URI (e.g. https://oakley.bamp.io) - if request is proxied 
   * from CloudFront, correct hostname is determined
   * @param boolean $includePath include the URL path
   * @param boolean $includeQueryString if $includePath is TRUE, whether or not 
   * to include query strings in the url
   * @param boolean $includeProto whether or not to include the protocol 
   * (default is TRUE)
   * @return string
   */
  private function getServerUri($includePath=FALSE, $includeQueryString=TRUE, $includeProto=TRUE) {
    global $_api_server_uri;
  
    if (!$_api_server_uri) {
      $headers = function_exists('getallheaders') ? getallheaders() : array();
      $_api_server_uri = isset($headers['Host']) ? $headers['Host'] : SRA_Controller::getServerName();
      if ($_api_server_uri && !preg_match('/^http:/', $_api_server_uri)) $_api_server_uri = $this->getProto() . '://' . $_api_server_uri;
    }
  
    $uri = $_api_server_uri . ($includePath && isset($_SERVER['REQUEST_URI']) ? ($includeQueryString ? $_SERVER['REQUEST_URI'] : strtok($_SERVER['REQUEST_URI'], '?')) : '');
    if (!$includeProto) $uri = str_replace('http://', '', str_replace('https://', '', $uri));
    return $uri;
  }
	
	/**
	 * extracts global or method specific settings from an API returned from 
	 * SRA_Util::parsePhpSource. The return value is a hash containing those
	 * settings. Returns NULL if any of the settings are invalid (error will 
	 * be logged and output to stdout if $debug=TRUE). The return value is a set
	 * of key/value pairs with default values populated. Values are primitive 
	 * types except in the case of the following:
	 *   api-key-validation => optional name of a global function that may be 
	 *                         used to validate API keys prior to method 
	 *                         invocation. The function should have this 
	 *                         signature:
	 *                         function_name($apiKey, $api, $method, $params): boolean
	 *                         If the function returns TRUE, the call will be 
	 *                         assumed to be valid. If it returns FALSE, it will 
	 *                         be assumed to be invalid resulting in a 401 
	 *                         Unauthorized response. Only methods flagged with 
	 *                         @api-key-validate will be validated
	 *   api-key-name       => the name of the request header, query or form 
	 *                         variable where the API key should be accessed (in 
	 *                         that order) in order to invoke the 
	 *                         @api-key-validation function. If no value is 
	 *                         found, and an attempt to invoke an 
	 *                         @api-key-validate method is made, a 400 Bad 
	 *                         Request response will result. The default value 
	 *                         for this configuration is api_key
	 *   api-key-validate   => TRUE for methods that require API key validation
	 *   cache-scope-params => an array of cookie or request params (when 
	 *                         cache-scope is 'cookie' or 'request')
   *   contact-email      =>  optional contact email address for the API
   *   contact-name       =>  optional contact name address for the API
   *   contact-url        =>  optional contact URL address for the API
	 *   description        => description for the API or API method
	 *   doc-hidden         => TRUE if a method should be excluded in API docs
	 *   doc-mashape        => NULL if documentation should be disabled
	 *   doc-tags           => array of tags for the API documentation endpoints 
	 *                         (or empty array)
	 *   doc-tag-primary    => the primary API documentation endoint tag (the 
	 *                         first tag specified)
	 *   doc-swagger        => NULL if documentation should be disabled
   *   doc-swagger2       => NULL if documentation should be disabled
   *   docs               => URI for API documentation (e.g. /docs). If set,
   *                         the 'docs-template' will be displayed when invoked
   *   docs-function      => Optional global function to invoke prior to 
   *                         rendering 'docs-template'. If set, the function 
   *                         should return some value the result of which will 
   *                         be set to the template variable $docsFunc. If the 
   *                         function does not return a value, a 403 response 
   *                         will be generated.
   *   docs-function-conditional => Optional If the API includes a 
   *                         @docs-function annotation, and that function 
   *                         returns an object, this annotation can be set to 
   *                         an attribute or function of that object the value 
   *                         of which must evaluation to TRUE in order for this 
   *                         API method to be included in the documentation
   *   docs-template      => Template to display when the (optional) 'docs' URI 
   *                         if invoked. Default is 'api-docs.tpl'. The 
   *                         template environment will contain a '$router' 
   *                         variable referencing the `SRA_ApiRouter` instance 
   *                         and an 'api_resources' variable referencing an 
   *                         'SRA_ResourceBundle' with the strings in 
   *                         '/etc/l10n/api-router.properties'
	 *   entity-example     => array of example settings for entities (indexed by 
	 *                         entity name)
	 *   entity-exclude:    => array of attributes that should be excluded from 
	 *                         entity rendering
	 *   entity-include:    => array of attributes that should be included in
	 *                         entity rendering
	 *   entity-ref         => array of entity references - how they should be 
	 *                         named in API documentation
	 *   error-codes        => a hash containing method response value/status 
	 *                         pairs - where the status value is a hash with 2 
	 *                         keys: code and description
   *   external-docs-description => optional description for the 
   *                         'external-docs-url'
   *   external-docs-url  => optional external documentation URL
	 *   headers-add        => key/value pairs defining headers that should be
	 *                         appended to the response
	 *   headers-remove     => array of header names that should be removed 
	 *                         from the response
	 *   http-methods       => an array containing all of the http methods (GET, 
	 *                         POST, PUT or DELETE) that an API method should be 
	 *                         routed for
	 *   http-method-notes  => an optional array of notes for each of the 
	 *                         http-methods (has the same array indeces)
	 *   http-method-summaries => an optional array of summaries for each of the 
	 *                         http-methods (has the same array indeces)
	 *   method             => the name of the controller method
	 *   params             => an ordered hash of API method parameters indexed 
	 *                         by the parameter name. The value in this hash 
	 *                         contains the following keys:
	 *                           array:       is this parameter an array?
	 *                           default:     default value for this parameter
	 *                           description: description for the parameter
	 *                           example:     example value for this parameter
	 *                           no-validate: TRUE if validation of values for 
	 *                                        this parameter should be skipped
	 *                           options:     options list for this parameter
	 *                           placeholder: is this parameter in the route as
	 *                                        a placeholder?
	 *                           regex:       regular expression for validation
	 *                           required:    is the parameter required?
	 *                           type:        the data type for this parameter
	 *                                        string; float; int; bool; date; 
	 *                                        timestamp
	 *   return             => description of a method return - a hash with the 
	 *                         following keys
	 *                           array:       is the return value an array?
	 *                           csv-method:  optional name of an entity method
	 *                                        that will produce a CSV blob
	 *                           entity:      TRUE if type is an entity
	 *                           example:     example return value
	 *                           type:        return data type  - one of:
	 *                                        string; float; int; bool; date; 
	 *                                        timestamp; void; [entity]
	 *                           type-label   if the return type is entity, this
	 *                                        value will correspond with how the
	 *                                        entity type should be referenced in
	 *                                        generated API documentation
	 *   route              => a hash describing the route - uses the following
	 *                         keys:
	 *                           fixed:        the route without placeholders 
	 *                                         (e.g. /users/add)
	 *                           parts:        the total # of parts possible
	 *                                         including placeholders
	 *                           placeholders: ordered array containing the names 
	 *                                         of parameters that may be imbedded 
	 *                                         into the route
	 *   set-cookie         => the optional name of a cookie the return value of 
	 *                         an API method should be set to
	 *   status-codes       => a hash containing condition/status pairs
	 *                         where the condition keys are one of the following:
	 *                         error; exception; invalid; not-found; null; ok
	 *                         and the status value is a hash with 2 keys: 
	 *                         code and description
	 *   status-doc-skip    => array of status code identifiers for which status
	 *                         documentation should not be generated
	 *   status-codes-num   => same as status-codes, but indexed by status code 
	 *                         and the value is the description
	 *   tags               => array of tags for the API/method (or empty array)
	 *   tag-primary        => the primary tag (the first tag specified)
   *   terms-of-service   => optional URL to a terms of service page for the 
   *                         API
	 * @param array $api the api hash to return the settings from
	 * @param string $method the name of the method this is being invoked for 
	 * (NULL for class settings)
	 * @param boolean $debug whether or not to enable debug output
	 * @return array
	 */
	private function getSettings(&$api, $method=NULL, $debug=FALSE) {
		$settings = NULL;
		if (is_array($api)) {
			$settings = array();
			
			// cache scope
			if (isset($api['cache-scope'])) {
				$pieces = explode(' ', is_array($api['cache-scope']) ? $api['cache-scope'][0] : $api['cache-scope']);
				if (in_array($scope = trim(strtolower($pieces[0])), array('cookie', 'none', 'params', 'request'))) {
					$settings['cache-scope'] = $scope;
					if ((!isset($pieces[1]) || !trim($pieces[1])) && ($scope == 'cookie' || $scope == 'request')) {
						$msg = 'SRA_ApiRouter::getSettings - Error: cache-scope cookie or request requires the name(s) of those parameters to be specified.' . ($method ? " Method: ${method}" : '');
						SRA_Error::logError($msg, __FILE__, __LINE__);
						if ($debug) print("${msg}\n");
						return NULL;
					}
					else $settings['cache-scope-params'] = explode(',', $pieces[1]);
				}
				else {
					$msg = "SRA_ApiRouter::getSettings - Error: scope ${scope} is not a valid cache-scope." . ($method ? " Method: ${method}" : '');
					SRA_Error::logError($msg, __FILE__, __LINE__);
					if ($debug) print("${msg}\n");
					return NULL;
				}
			}
			else if ($method) {
				$settings['cache-scope'] = $this->_settings['cache-scope'];
				if (isset($this->_settings['cache-scope-params'])) $settings['cache-scope-params'] = $this->_settings['cache-scope-params'];
			}
			else $settings['cache-scope'] = 'params';
			
			// description
			$settings['description'] = isset($api['comment']) ? SRA_ApiRouter::formatDescription($api['comment']) : NULL;
      
      // maintenance-file
			$settings['maintenance-file'] = isset($api['maintenance-file']) && trim($api['maintenance-file']) ? $api['maintenance-file'] : ($method ? $this->_settings['maintenance-file'] : NULL);
			
			// headers
			$settings['headers-add'] = array();
			$settings['headers-remove'] = array();
			if (isset($api['header'])) {
				foreach(is_array($api['header']) ? $api['header'] : array($api['header']) as $header) {
					$pieces = explode(' ', $header);
					if (substr($pieces[0], 0, 1) == '-') $settings['headers-remove'][] = substr($pieces[0], 1);
					else if (count($pieces) >= 2) $settings['headers-add'][trim($pieces[0])] = trim(str_replace($pieces[0], '', $header));
				}
			}
			if ($method) {
				foreach($this->_settings['headers-add'] as $key => $val) {
					if (!isset($settings['headers-add'][$key]) && !in_array($key, $settings['headers-remove'])) $settings['headers-add'][$key] = $val;
				}
				foreach($this->_settings['headers-remove'] as $key) {
					if (!in_array($key, $settings['headers-remove']) && !isset($settings['headers-add'][$key])) $settings['headers-remove'][] = $key;
				}
			}
			else {
				$settings['api'] = isset($api['api']) ? (is_array($api['api']) ? $api['api'][0] : $api['api']) : SRA_Controller::getAppName();
				$settings['api-version'] = isset($api['api-version']) ? (is_array($api['api-version']) ? $api['api-version'][0] : $api['api-version']) : (isset($_SERVER['SCRIPT_FILENAME']) && preg_match(SRA_API_ROUTER_CONTROLLER_VERSION_DIR_REGEX, $d = basename(dirname($_SERVER['SCRIPT_FILENAME'])), $m) ? substr($d, 1) : NULL);
        $settings['api_version'] = $settings['api-version'];
        
				// dynamic sierra-api and sierra-api-version headers
				if (!in_array('sierra-api', $settings['headers-remove'])) $settings['headers-add']['sierra-api'] = $settings['api'];
				if (!in_array('sierra-api-version', $settings['headers-remove']) && isset($settings['api-version'])) $settings['headers-add']['sierra-api-version'] = $settings['api-version'];
			}
			if (($idx = array_search('sierra-api', $settings['headers-remove'])) !== FALSE) unset($settings['headers-remove'][$idx]);
			if (($idx = array_search('sierra-api-version', $settings['headers-remove'])) !== FALSE) unset($settings['headers-remove'][$idx]);
			
			// set-cookie
			if ($method && isset($api['set-cookie'])) {
			  $settings['set-cookie'] = $api['set-cookie'];
			}
			
			// status codes
			$customStatuses = array();
			$resources =& $this->getResources();
			$settings['status-codes'] = array();
			if (isset($api['status'])) {
				foreach(is_array($api['status']) ? $api['status'] : array($api['status']) as $status) {
					if (preg_match('/^([a-zA-Z\-]+)\s+([0-9]{3})/msU', $status, $m) && in_array($condition = trim(strtolower($m[1])), array('error', 'exception', 'invalid', 'not-found', 'null', 'unauthorized', 'ok')) && SRA_ApiRouter::getMessages($m[2])) {
						$desc = trim(str_replace($m[0], '', $status));
						$settings['status-codes'][$condition] = array('code' => $m[2], 'description' => trim($desc) ? SRA_ApiRouter::formatDescription($desc) : ($resources->containsKey($m = 'api.status.' . $m[2]) ? $resources->getString($m) : SRA_ApiRouter::getMessages($m[2])));
						$customStatuses[$m[2]] = $condition;
					}
					else {
						$msg = "SRA_ApiRouter::getSettings - Error: @status ${status} is not valid (check documentation for valid conditions and codes)." . ($method ? " Method: ${method}" : '');
						SRA_Error::logError($msg, __FILE__, __LINE__);
						if ($debug) print("${msg}\n");
						return NULL;
					}
				}
			}
			if (!isset($settings['status-codes']['error']) && !isset($customStatuses[500])) $settings['status-codes']['error'] = $method ? $this->_settings['status-codes']['error'] : array('code' => 500, 'description' => $resources->getString('api.status.500'));
			if (!isset($settings['status-codes']['exception']) && !isset($customStatuses[500])) $settings['status-codes']['exception'] = $method ? $this->_settings['status-codes']['exception'] : array('code' => 500, 'description' => $resources->getString('api.status.500'));
			if (!isset($settings['status-codes']['invalid']) && !isset($customStatuses[400])) $settings['status-codes']['invalid'] = $method ? $this->_settings['status-codes']['invalid'] : array('code' => 400, 'description' => $resources->getString('api.status.400'));
			if (!isset($settings['status-codes']['not-found']) && !isset($customStatuses[404])) $settings['status-codes']['not-found'] = $method ? $this->_settings['status-codes']['not-found'] : array('code' => 404, 'description' => $resources->getString('api.status.404'));
			if (!isset($settings['status-codes']['null']) && !isset($customStatuses[503])) $settings['status-codes']['null'] = $method ? $this->_settings['status-codes']['null'] : array('code' => 503, 'description' => $resources->getString('api.status.503'));
			if (!isset($settings['status-codes']['unauthorized']) && !isset($customStatuses[401])) $settings['status-codes']['unauthorized'] = $method ? $this->_settings['status-codes']['unauthorized'] : array('code' => 401, 'description' => $resources->getString('api.status.401', array('apiKeyName' => isset($api['api-key-name']) ? $api['api-key-name'] : SRA_API_ROUTER_DEFAULT_API_KEY_NAME)));
			if (!isset($settings['status-codes']['ok']) && !isset($customStatuses[200])) $settings['status-codes']['ok'] = $method ? $this->_settings['status-codes']['ok'] : array('code' => 200, 'description' => $resources->getString('api.status.200'));
			$settings['status_codes'] = $settings['status-codes'];
			
			// status-doc-skip
			$settings['status-doc-skip'] = array();
			if (isset($api['status-doc-skip'])) {
				foreach(is_array($api['status-doc-skip']) ? $api['status-doc-skip'] : array($api['status-doc-skip']) as $status) {
					if (in_array($condition = trim(strtolower($status)), array('error', 'exception', 'invalid', 'not-found', 'null', 'unauthorized', 'ok'))) {
						if (!in_array($code = $settings['status-codes'][$condition]['code'], $settings['status-doc-skip'])) $settings['status-doc-skip'][$code] = $condition;
					}
					else if (is_numeric($status) && $status >=100 && $status <= 999) {
						if (!in_array($status, $settings['status-doc-skip'])) $settings['status-doc-skip'][$status] = $status;
					}
					else {
						$msg = "SRA_ApiRouter::getSettings - Error: @status-doc-skip ${status} is not valid (check documentation for valid conditions)." . ($method ? " Method: ${method}" : '');
						SRA_Error::logError($msg, __FILE__, __LINE__);
						if ($debug) print("${msg}\n");
						return NULL;
					} 
				}
			}
			else if ($method) {
			  $settings['status-doc-skip'] = array();
			  foreach($this->_settings['status-doc-skip'] as $code => $condition) {
			    if (!isset($customStatuses[$code]) && !in_array($condition, $customStatuses)) $settings['status-doc-skip'][$code] = $condition;
			  }
		  }
			
			// tags
			$settings['tags'] = array();
			if (isset($api['tag'])) foreach(is_array($api['tag']) ? $api['tag'] : array($api['tag']) as $tag) $settings['tags'][] = $tag;
			if (count($settings['tags'])) {
				$settings['tag-primary'] = $settings['tags'][0];
				$settings['tag_primary'] = $settings['tag-primary'];
			}

			// swagger auth reference
			if (isset($api['swagger-auth'])) $settings['swagger_auth'] = $api['swagger-auth'];
			
			// method specific settings
			if ($method) {
			  if (isset($this->_settings['api-key-validation'])) {
			    $settings['api-key-validate'] = isset($api['api-key-validate']) || (isset($this->_settings['api-key-validate']) && !isset($api['api-key-validate-skip']));
			    $settings['api_key_validate'] = $settings['api-key-validate'];
		    }
		    if (!isset($settings['api-key-validate']) || !$settings['api-key-validate']) unset($settings['status-codes']['unauthorized']);
		    
				$settings['method'] = $method;
				$settings['async'] = isset($api['async']) ? TRUE : $this->_settings['async'];
				$settings['skip-callback-param'] = isset($api['skip-callback-param']) ? TRUE : $this->_settings['skip-callback-param'];
				$settings['skip_callback_param'] = $settings['skip-callback-param'];
				$settings['skip-date-params'] = isset($api['skip-date-params']) ? TRUE : $this->_settings['skip-date-params'];
				$settings['skip_date_params'] = $settings['skip-date-params'];
				$settings['beautify'] = isset($api['beautify']) ? TRUE : $this->_settings['beautify'];
				$settings['beautify-round'] = isset($api['beautify-round']) && is_numeric($api['beautify-round']) && $api['beautify-round'] >= 0 ? $api['beautify-round'] : $this->_settings['beautify-round'];
				$settings['bool-false'] = isset($api['bool-false']) ? (is_array($api['bool-false']) ? $api['bool-false'][0] : $api['bool-false']) : $this->_settings['bool-false'];
				$settings['bool-true'] = isset($api['bool-true']) ? (is_array($api['bool-true']) ? $api['bool-true'][0] : $api['bool-true']) : $this->_settings['bool-true'];
				$settings['cache-ttl'] = isset($api['cache-ttl']) && is_numeric($api['cache-ttl']) && $api['cache-ttl'] >= 0 ? $api['cache-ttl']*1 : $this->_settings['cache-ttl'];
				$settings['content-type-other'] = isset($api['content-type-other']) ? (is_array($api['content-type-other']) ? $api['content-type-other'][0] : $api['content-type-other']) : 'text/plain';
				$settings['doc-hidden'] = isset($api['doc-hidden']) ? TRUE : FALSE;
				$settings['doc_hidden'] = $settings['doc-hidden'];
        $settings['docs-function-conditional'] = isset($api['docs-function-conditional']) ? trim(is_array($api['docs-function-conditional']) ? $api['docs-function-conditional'][0] : $api['docs-function-conditional']) : NULL;
				$settings['error-codes'] = array();
				if (isset($api['error'])) {
					foreach(is_array($api['error']) ? $api['error'] : array($api['error']) as $error) {
						if (preg_match('/^([\S]+)\s+([0-9]{3})/msU', $error, $m)) {
							$desc = trim(str_replace($m[0], '', $error));
							$settings['error-codes'][$ret = trim($m[1])] = array('code' => $code = $m[2], 'description' => $desc = trim($desc) ? SRA_ApiRouter::formatDescription($desc) : NULL);
							$found = FALSE;
							foreach(array_keys($settings['status-codes']) as $s) {
								if ($settings['status-codes'][$s]['code'] == $code) {
									$settings['status-codes'][$s]['description'] = $desc;
									$found = TRUE;
								}
							}
							if (!$found) $settings['status-codes'][$code] = array('code' => $code, 'description' => $desc);
							if (isset($settings['status-doc-skip'][$code])) unset($settings['status-doc-skip'][$code]);
						}
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @error ${error} is not valid (check documentation for valid error annotations)." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				$settings['status-codes-num'] = array();
				foreach($settings['status-codes'] as $status) $settings['status-codes-num'][$status['code']] = $status['description'];
				ksort($settings['status-codes-num']);
				$settings['status_codes_num'] = $settings['status-codes-num'];
				$settings['status_doc_skip'] = $settings['status-doc-skip'];
				
				$settings['format-date'] = isset($api['format-date']) ? (is_array($api['format-date']) ? $api['format-date'][0] : $api['format-date']) : $this->_settings['format-date'];
				$settings['format_date'] = $settings['format-date'];
				$settings['format-time'] = isset($api['format-time']) ? (is_array($api['format-time']) ? $api['format-time'][0] : $api['format-time']) : $this->_settings['format-time'];
				$settings['format_time'] = $settings['format-time'];
				$settings['name'] = isset($api['name']) ? (is_array($api['name']) ? $api['name'][0] : $api['name']) : $method;
				$settings['http-methods'] = array();
				$settings['http-method-nicknames'] = array();
				$settings['http-method-notes'] = array();
				$settings['http-method-summaries'] = array();
				if (isset($api['http-method'])) {
					foreach(is_array($api['http-method']) ? $api['http-method'] : array($api['http-method']) as $http) {
					  $note = NULL;
					  $summary = NULL;
					  if (preg_match('/^([A-Za-z]+)\s+(.*)$/', $http, $m)) {
					    $http = $m[1];
					    if (preg_match('/^(.*)\/(.*)\/(.*)$/', $m[2], $m1) || preg_match('/^(.*)\/(.*)$/', $m[2], $m1)) {
					      $nickname = str_replace(' ', '_', trim($m1[1]));
					      $summary = substr(trim($m1[2]), 0, 120);
					      if (isset($m1[3])) $note = trim($m1[3]);
					    }
					    else $nickname = str_replace(' ', '_', trim($m[2]));
					  }
					  else $nickname = str_replace(' ', '_', sprintf('%s%s', preg_match('/' . $http . '/i', $settings['name']) ? '' : $http . ' ', $settings['name']));
					  
						if (in_array($http = trim(strtoupper($http)), array('GET', 'POST', 'PUT', 'DELETE')) && !in_array($http, $settings['http-methods'])) {
						  $settings['http-methods'][] = $http;
						  $settings['http-method-nicknames'][] = $nickname;
						  $settings['http-method-notes'][] = $note;
						  $settings['http-method-summaries'][] = $summary;
					  }
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @http-method ${http} is not valid (check documentation for valid http methods)." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				else {
				  $settings['http-methods'][] = 'GET';
				  $settings['http-method-nicknames'][] = str_replace(' ', '_', sprintf('%s%s', preg_match('/get/i', $settings['name']) ? '' : 'GET ', $settings['name']));
				  $settings['http-method-notes'][] = NULL;
				  $settings['http-method-summaries'][] = NULL;
			  }
				$settings['http_methods'] = $settings['http-methods'];
				$settings['http_method_nicknames'] = $settings['http-method-nicknames'];
				$settings['http_method_notes'] = $settings['http-method-notes'];
				$settings['http_method_summaries'] = $settings['http-method-summaries'];
				$settings['max-execution-time'] = isset($api['max-execution-time']) && is_numeric($api['max-execution-time']) && $api['max-execution-time'] > 0 ? $api['max-execution-time'] : $this->_settings['max-execution-time'];
				$settings['memory-limit'] = isset($api['memory-limit']) && preg_match('/^[0-9]+[bkmg]$/', $api['memory-limit']) ? $api['memory-limit'] : $this->_settings['memory-limit'];
				$settings['params'] = array();
				if (isset($api['params'])) {
					foreach($api['params'] as $name => $param) {
						$settings['params'][$name] = array('default' => isset($param['value']) && $param['value'] != 'NULL' && $param['value'] != 'null' ? SRA_Util::stripQuotes(SRA_Util::stripQuotes($param['value']), "'", "'") : NULL,
						                                   'description' => isset($param['comment']) ? SRA_ApiRouter::formatDescription($param['comment']) : NULL,
						                                   'example' => NULL,
						                                   'no-validate' => FALSE,
						                                   'options' => NULL,
						                                   'placeholder' => FALSE,
						                                   'regex' => NULL,
						                                   'required' => !isset($param['value']),
						                                   'type' => isset($param['type']) ? trim(strtolower($param['type'])) : 'string');
						// boolean parameters
						if ($settings['params'][$name]['type'] == 'boolean' && isset($settings['params'][$name]['default']) && !is_bool($settings['params'][$name]['default'])) {
						  $settings['params'][$name]['default'] = strtolower($settings['params'][$name]['default']) == 'true';
					  }
						// array parameters
						if (preg_match('/\[\]$/', $settings['params'][$name]['type'])) {
						  $settings['params'][$name]['array'] = TRUE;
						  $settings['params'][$name]['type'] = substr($settings['params'][$name]['type'], 0, -2);
						}
						else $settings['params'][$name]['array'] = FALSE;
						
						if ($settings['params'][$name]['type'] == 'integer') $settings['params'][$name]['type'] = 'int';
						else if ($settings['params'][$name]['type'] == 'boolean') $settings['params'][$name]['type'] = 'bool';
						if (!in_array($settings['params'][$name]['type'], array('string', 'float', 'int', 'bool', 'date', 'timestamp', 'var'))) {
							$msg = "SRA_ApiRouter::getSettings - Error: @param " . $settings['params'][$name]['type'] . " ${name} is not a valid type (check documentation for valid datatypes)." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				if (isset($api['param-example'])) {
					foreach(is_array($api['param-example']) ? $api['param-example'] : array($api['param-example']) as $p) {
						if (preg_match('/^\s*\$?([A-Za-z0-9_]+)\s+(.*)/', $p, $m) && isset($settings['params'][$name = trim($m[1])])) {
							$settings['params'][$name]['example'] = trim($m[2]);
						}
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @param-example ${p} is not valid - unable to parse or associate with a parameter. Name: " . $name . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				if (isset($api['param-options'])) {
					foreach(is_array($api['param-options']) ? $api['param-options'] : array($api['param-options']) as $p) {
						if (preg_match('/^\s*\$?([A-Za-z0-9_]+)\s+(.*)/', $p, $m) && isset($settings['params'][$name = trim($m[1])])) {
							$options = trim($m[2]);
							if ((file_exists($file = SRA_Controller::getAppConfDir() . "/l10n/${options}") || 
							    file_exists($file = SRA_Controller::getAppConfDir() . "/l10n/${options}.properties") || 
							    file_exists($file = SRA_Controller::getAppDir() . "/l10n/${options}") || 
							    file_exists($file = SRA_Controller::getAppDir() . "/${options}") || 
							    file_exists($file = SRA_Controller::getAppConfDir() . "/l10n/${options}.properties") || 
							    file_exists($file = SRA_Controller::getSysConfDir() . "/l10n/${options}.properties") || 
							    file_exists($file = "${options}") || file_exists($file = "${options}.properties")) && 
							    ($bundle =& SRA_ResourceBundle::getBundle($file))) {
								$options = array_keys($bundle->getData());
							}
							else if (preg_match('/;$/', trim($options))) eval($options);
							if (!is_array($options)) $options = explode(',', $options);
							$settings['params'][$name]['options'] = $options;
						}
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @param-options ${p} is not valid - unable to parse or associate with a parameter." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				if (isset($api['param-regex'])) {
					foreach(is_array($api['param-regex']) ? $api['param-regex'] : array($api['param-regex']) as $p) {
						if (preg_match('/^\s*\$?([A-Za-z0-9_]+)\s+(.*)/', $p, $m) && isset($settings['params'][$name = trim($m[1])]) && 
						    preg_match('/^\/.*\/$/', trim($m[2]))) {
							$settings['params'][$name]['regex'] = trim($m[2]);
						}
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @param-regex ${p} is not valid - unable to parse or associate with a parameter." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				if (isset($api['param-no-validate'])) {
					foreach(is_array($api['param-no-validate']) ? $api['param-no-validate'] : array($api['param-no-validate']) as $p) {
						if (preg_match('/^\s*\$?([A-Za-z0-9_]+)/', $p, $m) && isset($settings['params'][$name = trim($m[1])])) $settings['params'][$name]['no-validate'] = TRUE;
						else {
							$msg = "SRA_ApiRouter::getSettings - Error: @param-no-validate ${p} is not valid - unable to parse or associate with a parameter." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				$settings['return'] = array('array' => FALSE, 'entity' => FALSE, 'example' => NULL, 'exclude' => NULL, 'include' => NULL, 'type' => 'void');
				if (isset($api['return'])) {
					if (preg_match('/\[\]$/', $api['return'])) {
						$settings['return']['array'] = TRUE;
						$api['return'] = str_replace('[]', '', $api['return']);
					}
					$settings['return']['type'] = trim($api['return']);
					if ($settings['return']['type'] == 'integer') $settings['return']['type'] = 'int';
					else if ($settings['return']['type'] == 'boolean') $settings['return']['type'] = 'bool';
					if (in_array(strtolower($settings['return']['type']), array('string', 'float', 'int', 'bool', 'date', 'timestamp', 'void'))) {
						$settings['return']['type'] = strtolower($settings['return']['type']);
						if ($settings['return']['array']) {
							$msg = "SRA_ApiRouter::getSettings - Error: @return " . $settings['return']['type'] . " invalid - primitive return types cannot be arrays." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
					else {
						$settings['return']['entity'] = TRUE;
						$settings['return']['type-label'] = isset($this->_settings['entity-ref'][$settings['return']['type']]) ? $this->_settings['entity-ref'][$settings['return']['type']] : $settings['return']['type'];
						$settings['return']['type_label'] = $settings['return']['type-label'];
						if (isset($api['return-csv-method'])) {
							$settings['return']['csv-method'] = $api['return-csv-method'];
							$settings['return']['csv_method'] = $api['return-csv-method'];
						}
						
						// validate presence of return type entity
						$entity = $settings['return']['type'];
						$dao =& SRA_DaoFactory::getDao($entity, FALSE, FALSE, FALSE, SRA_ERROR_OPERATIONAL);
						$efile = SRA_Controller::getAppLibDir() . '/' . basename(SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR) . "/${entity}.php";
						$obj = NULL;

						if (method_exists($dao, 'newInstance')) $obj =& $dao->newInstance();
						else if (file_exists($efile)) {
							require_once($efile);
							if (class_exists($entity)) $obj = new ${entity}();
						}
						if (!class_exists($entity) || !is_object($obj) || !count($attributes = $obj->getAttributeNames())) {
							$msg = "SRA_ApiRouter::getSettings - Error: @return " . $settings['return']['type'] . " is not a valid entity type." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
						else if (isset($settings['return']['csv-method']) && !method_exists($obj, $settings['return']['csv-method'])) {
							$msg = "SRA_ApiRouter::getSettings - Error: @return-csv-method " . $settings['return']['csv-method'] . " is not a valid entity method." . ($method ? " Method: ${method}" : '');
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							return NULL;
						}
					}
				}
				if (isset($api['return-example'])) {
					$example = $api['return-example'];
					if (file_exists($file = SRA_Controller::getAppConfDir() . "/${example}") || 
					    file_exists($file = SRA_Controller::getAppDir() . "/${example}") || 
					    file_exists($file = "${options}")) $exampole = file_get_contents($file);
					
					// beautify json
					if (preg_match('/^\s*[\{\[].*[\}\]]$/msU', trim($example))) $example = SRA_ApiRouter::beautify($example);
					$settings['return']['example'] = $example;
				}
				
				// route
				$route = isset($api['route']) ? $api['route'] : $method;
				$pieces = explode('/', $route);
				$fixed = '/' . $pieces[0];
				$placeholders = array();
				for($i=1; $i<count($pieces); $i++) {
					if (preg_match('/^\\$?{\$?([A-Za-z0-9_]+)\}$/', $pieces[$i], $m)) $placeholders[] = $m[1];
					else if (!count($placeholders)) $fixed .= '/' . $pieces[$i];
				}
				
				$settings['route'] = array('fixed' => $fixed, 'parts' => count($pieces) + 1, 'placeholders' => $placeholders, 'full' => $fixed . ($placeholders ? '/' . implode('/', $placeholders) : ''));
				foreach($placeholders as $name) $settings['params'][$name]['placeholder'] = TRUE;
			}
			
			// class specific settings
			else {
  			// api-key-validation
  			if (isset($api['api-key-validation'])) {
  			  if (function_exists($api['api-key-validation'])) {
  			    $settings['api-key-validation'] = $api['api-key-validation'];
  			    $settings['api-key-name'] = isset($api['api-key-name']) ? $api['api-key-name'] : SRA_API_ROUTER_DEFAULT_API_KEY_NAME;
  			    $settings['api-key-validate'] = isset($api['api-key-validate']);
  			    $settings['api_key_validation'] = $settings['api-key-validation'];
  			    $settings['api_key_name'] = $settings['api-key-name'];
  			    $settings['api_key_validate'] = $settings['api-key-validate'];
  			  }
  			  else {
  					$msg = 'SRA_ApiRouter::getSettings - Error: api-key-validation function ' . $api['api-key-validation'] . ' does not exist';
  					SRA_Error::logError($msg, __FILE__, __LINE__);
  					if ($debug) print("${msg}\n");
  					return NULL;
  			  }
  			}
  			else unset($settings['status-codes']['unauthorized']);
  			
				$settings['async'] = isset($api['async']) ? TRUE : FALSE;
				$settings['skip-callback-param'] = isset($api['skip-callback-param']) ? TRUE : FALSE;
				$settings['skip_callback_param'] = $settings['skip-callback-param'];
				$settings['skip-date-params'] = isset($api['skip-date-params']) ? TRUE : FALSE;
				$settings['skip_date_params'] = $settings['skip-date-params'];
				$settings['beautify'] = isset($api['beautify']) ? TRUE : FALSE;
				$settings['beautify-round'] = isset($api['beautify-round']) && is_numeric($api['beautify-round']) && $api['beautify-round'] >= 0 ? $api['beautify-round'] : 2;
				$settings['bool-false'] = isset($api['bool-false']) ? (is_array($api['bool-false']) ? $api['bool-false'][0] : $api['bool-false']) : 'false';
				$settings['bool-true'] = isset($api['bool-true']) ? (is_array($api['bool-true']) ? $api['bool-true'][0] : $api['bool-true']) : 'true';
				$settings['cache-ttl'] = isset($api['cache-ttl']) && is_numeric($api['cache-ttl']) && $api['cache-ttl'] >= 0 ? $api['cache-ttl']*1 : NULL;
				$settings['cache-ttl-doc'] = isset($api['cache-ttl-doc']) && is_numeric($api['cache-ttl-doc']) && $api['cache-ttl-doc'] >= 0 ? $api['cache-ttl-doc']*1 : 60*60;
        $settings['contact-email'] = isset($api['contact-email']) ? $api['contact-email'] : NULL;
        $settings['contact_email'] = $settings['contact-email'];
        $settings['contact-name'] = isset($api['contact-name']) ? $api['contact-name'] : NULL;
        $settings['contact_name'] = $settings['contact-name'];
        $settings['contact-url'] = isset($api['contact-url']) ? $api['contact-url'] : NULL;
        $settings['contact_url'] = $settings['contact-url'];
				$settings['doc-swagger'] = isset($api['doc-swagger']) ? trim(is_array($api['doc-swagger']) ? $api['doc-swagger'][0] : $api['doc-swagger']) : '/swagger.json';
        if (!$settings['doc-swagger']) $settings['doc-swagger'] = NULL;
				$settings['doc_swagger'] = $settings['doc-swagger'];
				$settings['doc-swagger2'] = isset($api['doc-swagger2']) ? trim(is_array($api['doc-swagger2']) ? $api['doc-swagger2'][0] : $api['doc-swagger2']) : '/swagger2.json';
        if (!$settings['doc-swagger2']) $settings['doc-swagger2'] = NULL;
				$settings['doc_swagger2'] = $settings['doc-swagger2'];
				$settings['doc-mashape'] = isset($api['doc-mashape']) ? trim(is_array($api['doc-mashape']) ? $api['doc-mashape'][0] : $api['doc-mashape']) : '/mashape.xml';
        if (!$settings['doc-mashape']) $settings['doc-mashape'] = NULL;
				$settings['doc_mashape'] = $settings['doc-mashape'];
				$settings['docs'] = isset($api['docs']) ? trim(is_array($api['docs']) ? $api['docs'][0] : $api['docs']) : NULL;
        if (!$settings['docs']) $settings['docs'] = NULL;
				$settings['docs-function'] = isset($api['docs-function']) ? trim(is_array($api['docs-function']) ? $api['docs-function'][0] : $api['docs-function']) : NULL;
				$settings['docs-template'] = isset($api['docs-template']) ? trim(is_array($api['docs-template']) ? $api['docs-template'][0] : $api['docs-template']) : 'api-docs.tpl';
        $settings['external-docs-description'] = isset($api['external-docs-description']) ? $api['external-docs-description'] : NULL;
        $settings['external_docs_description'] = $settings['external-docs-description'];
        $settings['external-docs-url'] = isset($api['external-docs-url']) ? $api['external-docs-url'] : NULL;
        $settings['external_docs_url'] = $settings['external-docs-url'];
				$settings['format-date'] = isset($api['format-date']) ? (is_array($api['format-date']) ? $api['format-date'][0] : $api['format-date']) : SRA_Controller::getAppDateOnlyFormat();
				$settings['format_date'] = $settings['format-date'];
				$settings['format-time'] = isset($api['format-time']) ? (is_array($api['format-time']) ? $api['format-time'][0] : $api['format-time']) : SRA_Controller::getAppDateFormat();
				$settings['format_time'] = $settings['format-time'];
        $settings['license-name'] = isset($api['license-name']) ? $api['license-name'] : NULL;
        $settings['license_name'] = $settings['license-name'];
        $settings['license-url'] = isset($api['license-url']) ? $api['license-url'] : NULL;
        $settings['license_url'] = $settings['license-url'];
				$settings['max-execution-time'] = isset($api['max-execution-time']) && is_numeric($api['max-execution-time']) && $api['max-execution-time'] > 0 ? $api['max-execution-time'] : NULL;
				$settings['memory-limit'] = isset($api['memory-limit']) && preg_match('/^[0-9]+[bkmg]$/', $api['memory-limit']) ? $api['memory-limit'] : NULL;
				$settings['name'] = isset($api['name']) ? (is_array($api['name']) ? $api['name'][0] : $api['name']) : $this->_class;
				$settings['singleton'] = isset($api['singleton']) ? (is_array($api['singleton']) ? $api['singleton'][0] : $api['singleton']) : NULL;
        $settings['terms-of-service'] = isset($api['terms-of-service']) ? $api['terms-of-service'] : NULL;
        $settings['terms_of_service'] = $settings['terms-of-service'];
				$settings['uri-prefix'] = isset($api['uri-prefix']) ? $api['uri-prefix'] : NULL;
				// explicit URL
				if (isset($api['url']) && ((is_array($api['url']) && $api['url'][0]) || $api['url'])) {
				  $settings['url'] = is_array($api['url']) ? $api['url'][0] : $api['url'];
				}
				else {
				  $appId = SRA_Controller::getCurrentAppId();
          $url = $this->getServerUri(TRUE);
				  $settings['url'] =  SRA_ApiRouter::formatUrl($this->getServerUri(FALSE, TRUE) . ($settings['uri-prefix'] ? '/' . $settings['uri-prefix'] : '') . '/api');
			  }
				if (!preg_match('/^http/i', $settings['url'])) $settings['url'] = SRA_ApiRouter::formatUrl($this->getServerUri() . '/' . $settings['url']);
				if (preg_match('/:\/[a-z]/', $settings['url'])) $settings['url'] = str_replace(':/', '://', $settings['url']);
				preg_match('/^(https?:\/\/[^\/]+)(.*)$/', $settings['url'], $m);
				$settings['url_base'] = $m[1];
				$settings['url_base_actual'] = $this->getServerUri();
        $settings['url_hostname'] = $this->getServerUri(FALSE, FALSE, FALSE);
        $settings['url_proto'] = $this->getProto();
				$settings['url_resource'] = $m[2];
				// entity examples
				$settings['entity-example'] = array();
				if (isset($api['entity-example'])) {
					foreach(is_array($api['entity-example']) ? $api['entity-example'] : array($api['entity-example']) as $example) {
						if (preg_match('/^([\S]+)\s+(.*)$/msU', trim($example), $m)) $settings['entity-example'][$m[1]] = $m[2];
					}
				}
				// entity ref
				$settings['entity-ref'] = array();
				if (isset($api['entity-ref'])) {
					foreach(is_array($api['entity-ref']) ? $api['entity-ref'] : array($api['entity-ref']) as $ref) {
						if (preg_match('/^([\S]+)\s+([\S]+)$/', trim($ref), $m)) $settings['entity-ref'][$m[1]] = $m[2];
					}
				}
				// entity exclude
				$settings['entity-exclude'] = array();
				if (isset($api['entity-exclude'])) {
					foreach(is_array($api['entity-exclude']) ? $api['entity-exclude'] : array($api['entity-exclude']) as $exclude) {
						if (preg_match('/^([\S]+)\s+([\S]+)$/', trim($exclude), $m)) {
							$entity = trim($m[1]);
							$attr = trim($m[2]);
							if (!isset($settings['entity-exclude'][$entity])) $settings['entity-exclude'][$entity] = array();
							$settings['entity-exclude'][$entity][] = $attr;
						}
					}
				}
				// entity include
				$settings['return']['include'] = array();
				if (isset($api['entity-include'])) {
					foreach(is_array($api['entity-include']) ? $api['entity-include'] : array($api['entity-include']) as $include) {
						if (preg_match('/^([\S]+)\s+([\S]+)$/', trim($include), $m)) {
							$entity = trim($m[1]);
							$attr = trim($m[2]);
							if (!isset($settings['entity-include'][$entity])) $settings['entity-include'][$entity] = array();
							$settings['entity-include'][$entity][] = $attr;
						}
					}
				}
				// documentation endpoint tags
				$settings['doc-tag'] = array();
				if (isset($api['doc-tag'])) foreach(is_array($api['doc-tag']) ? $api['doc-tag'] : array($api['doc-tag']) as $tag) $settings['doc-tags'][] = $tag;
				if (count($settings['doc-tags'])) {
					$settings['doc-tag-primary'] = $settings['doc-tags'][0];
					$settings['doc_tag_primary'] = $settings['doc-tag-primary'];
				}
				$settings['doc_tag'] = $settings['doc-tag'];
				// oauth2 settings
				if (isset($api['oauth-authorization-url']) && isset($api['oauth-token-url']) && isset($api['oauth-scope'])) {
					$settings['oauth-authorization-url'] = str_replace('SRA_HOSTNAME', SRA_HOSTNAME, $api['oauth-authorization-url']);
					$settings['oauth-token-url'] = str_replace('SRA_HOSTNAME', SRA_HOSTNAME, $api['oauth-token-url']);
					$settings['oauth-scope'] = array();
					foreach(is_array($api['oauth-scope']) ? $api['oauth-scope'] : array($api['oauth-scope']) as $scope) {
						if (preg_match('/^([\S]+)\s+(.*)$/', trim($scope), $m) || preg_match('/^([\S]+)$/', trim($scope), $m)) $settings['oauth-scope'][trim($m[1])] = isset($m[2]) ? trim($m[2]) : NULL;
					}
					$settings['oauth_authorization_url'] = $settings['oauth-authorization-url'];
					$settings['oauth_token_url'] = $settings['oauth-token-url'];
					$settings['oauth_scope'] = $settings['oauth-scope'];
				}
			}
      
      // Populate method parameter settings that match with attributes in the 
      // method response when that response is an entity
      if ($method && 
          isset($settings['params']) && 
          is_array($settings['params']) && 
          isset($settings['return']) && 
          isset($settings['return']['entity']) && 
          isset($settings['return']['type']) && 
          $settings['return']['entity']) {
        $entity = $settings['return']['type'];
        $dao =& SRA_DaoFactory::getDao($entity, FALSE, FALSE, FALSE, SRA_ERROR_OPERATIONAL);
				$efile = SRA_Controller::getAppLibDir() . '/' . basename(SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR) . "/${entity}.php";
				$obj = NULL;

				if (method_exists($dao, 'newInstance')) $obj =& $dao->newInstance();
				else if (file_exists($efile)) {
					require_once($efile);
					if (class_exists($entity)) $obj = new ${entity}();
				}
        if (is_object($obj) && count($attrs = $obj->getAttributeNames())) {
          foreach($settings['params'] as $attr => $props) {
            if (in_array($attr, $attrs)) {
              if ((!isset($props['array']) || !$props['array']) && $obj->getAttributeCardinality($attr)) $props['array'] = TRUE;
              if ((!isset($props['description']) || !trim($props['description'])) && ($v = $obj->getHelpContent($attr))) $props['description'] = $v;
              if ((!isset($props['options']) || !$props['options']) && ($v = $obj->getOptionsMap($attr))) $props['options'] = array_keys($v);
              if ((!isset($props['type']) || !$props['type'] || $props['type'] == 'var') && in_array($v = $obj->getAttributeType($attr), array('blob', 'boolean', 'date', 'float', 'int', 'string', 'time'))) $props['type'] = $v == 'boolean' ? 'bool' : ($v == 'time' ? 'timestamp' : ($v == 'blob' ? 'string' : $v));
              $settings['params'][$attr] = $props;
            }
          }
        }
      }
      
      // convert var types to string
      if ($method && isset($settings['params']) && is_array($settings['params'])) {
        foreach(array_keys($settings['params']) as $attr) {
          if (isset($settings['params'][$attr]['type']) && $settings['params'][$attr]['type'] == 'var') $settings['params'][$attr]['type'] = 'string';
        }
      }
			
			if ($settings) ksort($settings);
			if ($debug && $settings) {
				print("Returning settings:\n");
				print_r($settings);
			}
		}
		return $settings;
	}
	
	/**
	 * Initializes the API controller (if specified) - returns TRUE if the 
	 * controller was initializes and is valid, FALSE otherwise
	 * @param boolean $debug whether or not to enable debug output
   * @param boolean $noCache don't allow for caching of API initialization 
   * details
	 * @return boolean
	 */
	private function initController($debug=FALSE, $noCache=FALSE) {
    $ckey = md5(sprintf('%s%s', SRA_API_ROUTER_CACHE_PREFIX, $this->_source));
    // check for cached API initialization details
    if ($this->_initialized === NULL && 
       file_exists($this->_source) && 
       !$noCache && 
       SRA_API_ROUTER_CONTROLLER_USE_CACHE && 
       is_array($cached =& SRA_Cache::getCache($ckey)) && 
       isset($cached['methods']) && 
       is_array($cached['methods']) && 
       count($cached['methods']) && 
       isset($cached['filemtime']) && 
       is_numeric($cached['filemtime']) && 
       $cached['filemtime'] == filemtime($this->_source)) {
      require_once($this->_source);
      $this->_initialized = FALSE;
      $this->_class = $cached['class'];
      $this->_settings = $cached['settings'];
      eval($cached['controller']);
			if (!is_object($this->_controller)) {
				$msg = sprintf('SRA_ApiRouter::initController - Error: unable to instantiate controller %s using %s', $this->_class, $cached['controller']);
				SRA_Error::logError($msg, __FILE__, __LINE__);
				if ($debug) print("${msg}\n");					
			}
			else {
        if ($debug) print("SRA_ApiRouter::initController - Debug: API controller ${clas} instantiated successfully using" . (isset($this->_settings['singleton']) ? ' singleton ' . $this->_settings['singleton'] : ' new operator') . "\n");
        $this->_initialized = TRUE;
        $this->_controllerIncludes = $cached['controllerIncludes'];
        $this->_methods = $cached['methods'];
      }
    }
		else if ($this->_initialized === NULL && file_exists($this->_source) && 
		    is_array($api =& SRA_Util::parsePhpSource($this->_source, FALSE))) {
			$this->_initialized = FALSE;
			if ($debug) print("SRA_ApiRouter::initController - Initializing API routering using controller " . $this->_source . "\n");
			if (isset($api['classes']) && 
			    is_array($api['classes']) && 
			    count($api['classes'])) {
				$keys = array_keys($api['classes']);
				$this->_class = $keys[0];
				$clas = $this->_class;
				if ($debug) print("SRA_ApiRouter::initController - Debug: API controller class is ${clas}\n");
			}
			else {
				$msg = 'SRA_ApiRouter::initController - Error: unable to determine controller class';
				SRA_Error::logError($msg, __FILE__, __LINE__);
				if ($debug) print("${msg}\n");
			}
			// attempt to instantiate the controller
			if ($this->_class && is_array($this->_settings = $this->getSettings($api['classes'][$this->_class], FALSE, $debug))) {
				// instantiate the controller
				try {
					if ($debug) print("SRA_ApiRouter::initController - Debug: Attempting to instantiate ${clas} from source " . $this->_source . "\n");
					require_once($this->_source);
					if (class_exists($this->_class) && (!isset($this->_settings['singleton']) || method_exists($this->_class, $this->_settings['singleton']))) {
            $controllerInit = '$this->_controller =' . (isset($this->_settings['singleton']) ? "& ${clas}::" . $this->_settings['singleton'] : " new ${clas}") . '($this);';
						eval($controllerInit);
						if (!is_object($this->_controller)) {
							$msg = 'SRA_ApiRouter::initController - Error: unable to instantiate controller ' . $this->_class;
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");					
						}
						else if ($debug) print("SRA_ApiRouter::initController - Debug: API controller ${clas} instantiated successfully using" . (isset($this->_settings['singleton']) ? ' singleton ' . $this->_settings['singleton'] : ' new operator') . "\n");
					}
					else {
						$msg = 'SRA_ApiRouter::initController - Error: ' . (class_exists($this->_class) && isset($this->_settings['singleton']) ? 'singleton method ' . $this->_settings['singleton'] . ' is not valid' : "class ${clas} does not exist") . "\n";
						SRA_Error::logError($msg, __FILE__, __LINE__);
						if ($debug) print("${msg}\n");
					}
				}
				catch (Exception $e) {
					$msg = 'SRA_ApiRouter::initController - Error: unable to instantiate controller ' . $this->_class . ' due to exception: ' . $e->getMessage();
					SRA_Error::logError($msg, __FILE__, __LINE__);
					if ($debug) print("${msg}\n");
				}
			}
			else if ($this->_class) {
				$msg = "SRA_ApiRouter::initController - Error: settings for controller ${clas} are not valid";
				SRA_Error::logError($msg, __FILE__, __LINE__);
				if ($debug) print("${msg}\n");
			}
			if (is_object($this->_controller)) {
				// determine what source files the controller is using
				$reflection = new ReflectionClass($this->_controller);
				$this->_controllerIncludes = array($reflection->getFileName());
				foreach(get_object_vars($this->_controller) as $attr => $val) {
					if (is_object($val)) {
						$reflection = new ReflectionClass($val);
						if (!in_array($reflection->getFileName(), $this->_controllerIncludes)) $this->_controllerIncludes[] = $reflection->getFileName();
					}
				}
				foreach($api['classes'][$this->_class]['methods'] as $name => $method) {
					// only include public instance methods without the @skip-api annotation
					if (!$method['static'] && $method['access'] != 'private' && $method['access'] != 'protected' && !isset($method['skip-api'])) {
						if (is_array($settings = $this->getSettings($method, $name, $debug))) {
							$this->_methods[$name] = $settings;
							if ($debug) print("SRA_ApiRouter::initController - Debug: successfully initialized API method ${clas}::${name}\n");
						}
						else {
							$msg = "SRA_ApiRouter::initController - Error: settings for method ${clas}::${name} are not valid";
							SRA_Error::logError($msg, __FILE__, __LINE__);
							if ($debug) print("${msg}\n");
							break;
						}
					}
				}
				if (count($this->_methods)) {
					$this->_initialized = TRUE;
          $cache = array();
          $cache['filemtime'] = filemtime($this->_source);
          $cache['class'] = $this->_class;
          $cache['settings'] = $this->_settings;
          $cache['controller'] = $controllerInit;
          $cache['controllerIncludes'] = $this->_controllerIncludes;
          $cache['methods'] = $this->_methods;
          SRA_Cache::setCache($ckey, $cache, SRA_API_ROUTER_INIT_CACHE_TTL);
					if ($debug) print('SRA_ApiRouter::initController - Debug: successfully initialized controller with ' . count($this->_methods) . " methods\n");
				}
				else {
					$msg = 'SRA_ApiRouter::initController - Error: controller does not contain any API methods';
					SRA_Error::logError($msg, __FILE__, __LINE__);
					if ($debug) print("${msg}\n");
				}
			}
		}
		else {
			$msg = "SRA_ApiRouter::initController - Error: unable to parse controller source ${controller}";
			SRA_Error::logError($msg, __FILE__, __LINE__);
			if ($debug) print("${msg}\n");
		}
		return $this->_initialized;
	}
	
	/**
	 * invokes a method and returns the method response
	 * @param string $uri the URI used to invoke this method
	 * @param string $method the name of the method to invoke
	 * @param string $condition the resulting condition - passed by reference
	 * @param boolean $csv pass by reference variable that is set to TRUE if 
	 * the request specified the csv argument or uses a .csv suffix
	 * @return mixed
	 */
	public function &method($uri, $method, &$condition, &$csv=FALSE) {
		// invalid method - should never get here
		if (!isset($this->_methods[$method])) {
			$condition = 'not-found';
			return NULL;
		}
		
		// convert all headers to lower case
		if ($headers = function_exists('getallheaders') ? getallheaders() : NULL) {
		  foreach($headers as $key => $val) {
  		  unset($headers[$key]);
  		  $headers[strtolower($key)] = $val;
		  }
		}
		
		$condition = 'ok';
		$method =& $this->_methods[$method];
		
		// max-execution-time and memory-limit
		if (isset($method['max-execution-time'])) ini_set('max_execution_time', $method['max-execution-time']);
		if (isset($method['memory-limit'])) ini_set('memory_limit', $method['memory-limit']);
		
		// extract parameters from placeholds and http headers (GET and POST)
		$requestParams =& SRA_ApiRouter::getRequestParams();
		$args = array();
    
		// CSV output format (either csv parameter or .csv URI suffix)
		$csv = isset($requestParams['csv']);
		if (!$csv && preg_match('/\.csv$/i', $_SERVER['REQUEST_URI'])) {
			$csv = TRUE;
			$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - 4);
			foreach(array_keys($_GET) as $key) {
				if (preg_match('/\.csv$/i', $_GET[$key])) {
					$_GET[$key] = substr($_GET[$key], 0, strlen($_GET[$key]) - 4);
					break;
				}
			}
			if (preg_match('/\.csv$/i', $_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = substr($_SERVER['QUERY_STRING'], 0, strlen($_SERVER['QUERY_STRING']) - 4);
		}
		else if (!$csv && isset($headers['accept']) && strtolower($headers['accept']) == 'text/csv') $csv = TRUE;

    // construct method arguments
		foreach($method['params'] as $name => $param) $args[$name] = isset($requestParams[$name]) ? $requestParams[$name] : NULL;
		
		// get params from placeholders
		if ($method['route']['placeholders']) {
			$ruri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
			if ($placeholders = trim(substr($ruri, strrpos($ruri, $method['route']['fixed']) + strlen($method['route']['fixed']) + 1))) {
				if ($placeholders = trim($placeholders) ? explode('/', trim($placeholders)) : NULL) {
					foreach($method['route']['placeholders'] as $i => $name) {
						if (isset($placeholders[$i]) && $placeholders[$i]) $args[$name] = urldecode($placeholders[$i]);
					}
				}
			}
		}
		
		foreach($method['params'] as $name => $param) {
		  // set default values and convert dates/times/booleans
		  if (!isset($args[$name]) && isset($param['default']) && (gettype($param['default']) == 'boolean' || ($param['default'] != 'NULL' && $param['default'] != 'null'))) {
		    eval('$args[$name] = ' . ($param['default'] === TRUE ? 'TRUE' : ($param['default'] === FALSE ? 'FALSE' : (is_numeric($param['default']) || substr($param['default'], 0, 1) == '"' || substr($param['default'], 0, 1) == "'" ? $param['default'] : '"' . $param['default'] . '"'))) . ';');
	    }
		  
		  // create array type parameters
		  if (isset($args[$name]) && isset($param['array']) && $param['array'] && !is_array($args[$name])) {
        if (isset($args[$name]) && trim($args[$name])) {
  		    $vals = explode(',', $args[$name]);
  		    $args[$name] = array();
  		    foreach($vals as $val) $args[$name][] = trim($val);
        }
        else $args[$name] = NULL;
		  }
	  }
		
		// validate parameters and convert to appropriate datatype
		foreach($method['params'] as $name => $param) {
		  $isArray = isset($param['array']) && $param['array'];
		  
			if (!$param['no-validate']) {
				$invalid = NULL;
				if (isset($args[$name])) {
				  foreach(is_array($args[$name]) ? $args[$name] : array($args[$name]) as $i => $val) {
    				if ($param['options'] && !in_array($val, $param['options'])) {
              $invalid = 'options';
              $method['headers-add'][sprintf('sierra-api-valid-%s-options', $name)] = str_replace(' ', '_', implode('-', $param['options']));
            }
    				else if ($param['regex'] && !preg_match($param['regex'], $val)) $invalid = 'regex';
				  }
				}
				else if ($param['required']) $invalid = 'required';   
			}
			if (!$invalid) {
			  foreach(is_array($args[$name]) ? $args[$name] : array($args[$name]) as $i => $val) {
  				switch($param['type']) {
  					case 'float':
  					case 'int':
  						if ($val && !is_numeric($val)) $invalid = 'not-numeric-' . gettype($val);
  						else if (is_array($args[$name])) $args[$name][$i] *= 1;
  						else if (isset($args[$name])) $args[$name] *= 1;
  						break;
  					case 'bool':
  						$b = SRA_Util::convertBoolean($val);
  						if ($val !== NULL && $b === NULL) $invalid = 'not-boolean-' . gettype($val);
  						else if (is_array($args[$name])) $args[$name][$i] = $b;
  						else if (isset($args[$name])) $args[$name] = $b;
  						break;
  					case 'date':
  					case 'timestamp':
  						$d = strtotime($val);
  						if ($d === FALSE) $invalid = 'invalid-' . $param['type'];
  						else {
  						  if (is_array($args[$name])) {
    							$args[$name][$i] = new SRA_GregorianDate($args[$name]);
    							if ($param['type'] == 'date') $args[$name][$i]->setDateOnly(TRUE);
  						  }
  						  else {
    							$args[$name] = new SRA_GregorianDate($args[$name]);
    							if ($param['type'] == 'date') $args[$name]->setDateOnly(TRUE); 
  						  }
  						}
  						break;
  				}
			  }
			}
			if ($invalid) {
				$condition = 'invalid';
				if (!in_array($header = 'sierra-api-invalid', $method['headers-remove'])) $method['headers-add'][$header] = "${name}-${invalid}";
				break;
			}
		}
    
		// API key validation
		if ($condition == 'ok' && isset($method['api-key-validate']) && $method['api-key-validate']) {
		  $func = isset($this->_settings['api-key-validation']) ? $this->_settings['api-key-validation'] : NULL;
		  if ($func && function_exists($func)) {
        $keyName = strtolower(isset($this->_settings['api-key-name']) ? $this->_settings['api-key-name'] : SRA_API_ROUTER_DEFAULT_API_KEY_NAME);
        $apiKey = NULL;
        $found = FALSE;
        foreach(explode(',', $keyName) as $k) {
          $k = trim($k);
          if (isset($headers[$k])) $apiKey = $headers[$k];
          else if (isset($headers[$k1 = str_replace('-', '_', $k)])) $apiKey = $headers[$k1];
          else if ($requestParams[$k]) $apiKey = $requestParams[$k];
          else if ($_COOKIE[$k]) $apiKey = $_COOKIE[$k];
          else if (isset($headers['Authorization']) || isset($headers['authorization'])) {
            $apiKey = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
            $pieces = explode(' ', $apiKey);
            $apiKey = trim($pieces[count($pieces) - 1]);
          }
          if ($apiKey && strtolower($apiKey) != 'undefined') {
            $found = TRUE;
            if ($func($apiKey, $this->_settings['api'], $method['method'], $args)) {
              $condition = 'ok';
              break;
            }
            else $condition = 'unauthorized';
          }
        }
        if (!$found) {
          if (isset($this->_settings['api-key-redirect'])) {
            header('HTTP/1.1 302 Found');
            header(sprintf('Location: %s', $this->_settings['api-key-redirect']));
            exit;
          }
          else $condition = 'invalid';
        }
		  }
		  else {
				$msg = sprintf('SRA_ApiRouter::method - Error: api-key-validation function %s does not exist', $func);
				SRA_Error::logError($msg, __FILE__, __LINE__);
		    $condition = 'error';
		  }
		}
    
		// attempt to invoke the method
		if ($condition == 'ok') {
			$ckey = NULL;
			if (is_numeric($method['cache-ttl']) && $method['cache-ttl'] > 0 && $method['cache-scope'] != 'none') {
				$ckey = SRA_API_ROUTER_CACHE_PREFIX . SRA_Controller::getAppName() . '_' . $this->_settings['api'] . '_' . $method['method'];
				foreach($args as $arg) $ckey .= '_' . (is_array($arg) ? implode('_', $arg) : $arg);
				if ($method['cache-scope-params'] && ($method['cache-scope'] == 'cookie' || $method['cache-scope'] == 'request')) {
					foreach($method['cache-scope-params'] as $p) {
						if ($method['cache-scope'] == 'cookie' && isset($_COOKIE[$p])) $ckey .= "_${p}_" . $_COOKIE[$p];
						else if ($method['cache-scope'] == 'request') $ckey .= "_${p}_" . (isset($requestParams[$p]) ? $requestParams[$p] : NULL);
					}
				}
			}
			// base64 encode cache key
			if ($ckey) $ckey = md5($ckey);
      
      // include entity class file if applicable
      if (isset($method['return']['entity']) && $method['return']['entity'] && !class_exists($method['return']['type'])) {
        $entity = $method['return']['type'];
        $dao =& SRA_DaoFactory::getDao($entity, FALSE, FALSE, FALSE, SRA_ERROR_OPERATIONAL);
        $efile = SRA_Controller::getAppLibDir() . '/' . basename(SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR) . "/${entity}.php";
        if (!class_exists($entity) && file_exists($efile)) {
          require_once($efile);
        } 
      }
			
			// response is in cache
			if (SRA_API_ROUTER_CONTROLLER_USE_CACHE && strtolower($_SERVER['REQUEST_METHOD']) == 'get' && $ckey && SRA_Cache::cacheIsset($ckey)) {
				$response =& SRA_Cache::getCache($ckey);
				if (!in_array($header = 'sierra-api-cached', $method['headers-remove'])) $method['headers-add'][$header] = 'true';
			}
			// response is not in cache (or caching is disabled)
			else {
				if (SRA_API_ROUTER_CONTROLLER_USE_CACHE && strtolower($_SERVER['REQUEST_METHOD']) == 'get' && $ckey && !in_array($header = 'sierra-api-cached', $method['headers-remove'])) $method['headers-add'][$header] = 'false';
				
				// async
				if ($method['async']) {
  				// check for duplicate pending process
  				$pprocess = NULL;
  				$pid = NULL;
  				$bpid = NULL;
  				$tfile = NULL;
  				$ofile = NULL;
  				$efile = NULL;
  				$pfile = SRA_Controller::getAppTmpDir() . '/.api_' . $ckey;
  				if (file_exists($pfile)) {
  					if (count($pprocess = explode("\n", trim(file_get_contents($pfile)))) != 5 ||  
  					    !is_numeric($pid = $pprocess[0]) || 
  					    !is_numeric($bpid = $pprocess[1]) || 
  					    !file_exists($tfile = $pprocess[2]) || 
  					    !file_exists($ofile = $pprocess[3]) || 
  							!file_exists($efile = $pprocess[4]) || 
  							!SRA_Util::isProcessActive($pid)) {
  						$pprocess = NULL;
  						$pid = NULL;
  						$bpid = NULL;
  						$tfile = NULL;
  						$ofile = NULL;
  						$efile = NULL;
  					}
  				}

  				$response = NULL;
  				if (!$pprocess) {
  					$tpl =& SRA_Controller::getAppTemplate();
  					$tpl->assign('includes', $this->_controllerIncludes);
  					$tpl->assign('method', $method['method']);
  					$iniVars = array();
  					if (isset($this->_settings['max-execution-time'])) $iniVars['max_execution_time'] = $this->_settings['max-execution-time'];
  					if (isset($this->_settings['memory-limit'])) $iniVars['memory_limit'] = $this->_settings['memory-limit'];
  					$tpl->assign('iniVars', $iniVars);
  					$params = '';
  					foreach(array_keys($args) as $i => $arg) $params .= ($i ? ', ' : '') . '$router->_methodArgs["' . $arg . '"]';
  					$tpl->assign('params', $params);
  					$mlabel = get_class($this->_controller) . '::' . $method['method'] . "(${params})";
  					if ($tpl->displayToFile(dirname(dirname(__FILE__)) . '/bin/tpl/invoke-api-method.tpl', $efile = SRA_File::createRandomFile())) {
  						chmod($efile, 0755);
  						if ($fp = fopen($tfile = SRA_File::createRandomFile(), 'w')) {
  							$this->_methodArgs = $args;
  							fwrite($fp, serialize($this));
  							fclose($fp);
  							$ofile = SRA_File::createRandomFile();
  							$exec = "${efile} ${tfile} ${ofile}";
  							if (is_numeric($pid = SRA_Util::fork($exec))) {
                  // sra_error::logerror("EXEC: ${efile} ${tfile} ${ofile}");
                  // exit;
  								$pfp = fopen($pfile, 'w');
  								fwrite($pfp, "${pid}\n");
  								fwrite($pfp, getmypid() . "\n");
  								fwrite($pfp, "${tfile}\n");
  								fwrite($pfp, "${ofile}\n");
  								fwrite($pfp, "${efile}");
  								fclose($pfp);

  								// delete temporary files 3 seconds after script completion
  								if ((!$pprocess || (is_numeric($bpid) && !SRA_Util::isProcessActive($bpid))) && ($fp = fopen($dfile = SRA_File::createRandomFile(), 'w'))) {
  									fwrite($fp, "#!/bin/bash\n");
  									fwrite($fp, 'while [[ ${?} == 0 ]]' . "\n");
  									fwrite($fp, "do\n");
  									fwrite($fp, "  sleep 1s\n");
  									fwrite($fp, "  ps -p ${pid} >/dev/null\n");
  									fwrite($fp, "done\n");
  									fwrite($fp, "sleep 3s\n");
                    fwrite($fp, "rm -f ${tfile}\n");
                    fwrite($fp, "rm -f ${ofile}\n");
                    fwrite($fp, "rm -f ${efile}\n");
                    fwrite($fp, "rm -f ${pfile}\n");
                    fwrite($fp, "rm -f ${dfile}\n");
  									fclose($fp);
  									chmod($dfile, 0755);
  									SRA_Util::fork($dfile);
  								}
  							}
  							else {
  								$msg = "SRA_ApiRouter::method - Error: unable to fork asynchronous method ${mlabel} using ${exec}";
  								SRA_Error::logError($msg, __FILE__, __LINE__);
  								$condition = 'error';
  							}
  						}
  						else {
  							$msg = "SRA_ApiRouter::method - Error: unable to generate serialized input file for asynchronous execution script for method ${mlabel}";
  							SRA_Error::logError($msg, __FILE__, __LINE__);
  							$condition = 'error';
  						}
  					}
  					else {
  						$msg = "SRA_ApiRouter::method - Error: unable to generate asynchronous execution script for method ${mlabel}";
  						SRA_Error::logError($msg, __FILE__, __LINE__);
  						$condition = 'error';
  					}
  				}

  				if (is_numeric($pid)) {
  					while(TRUE) {
  						if (!SRA_Util::isProcessActive($pid)) {
  							$output = trim(file_get_contents($ofile));
  							if ($output == 'NULL') $response = NULL;
  							else if ($output == 'exception') $condition = 'exception';
  							else if (($response = unserialize($output)) === FALSE) {
  								$error = file_exists($ofile) ? file_get_contents($ofile) : NULL;
  								$msg = "SRA_ApiRouter::method - Error: exception when invoking method " . get_class($this->_controller) . '::' . $method['method'] . "(${params}); exec: ${exec}; error: " . trim($error);
  								SRA_Error::logError($msg, __FILE__, __LINE__);
  								$condition = 'error';
  							}
  							else if ($response) {
  							  // response caching
  								if ($ckey) {
  								  // cache GET methods
    							  if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
    								  SRA_Cache::setCache($ckey, $response, $method['cache-ttl']);
    								  $this->addCacheKey($ckey, $method['cache-ttl']); 
    							  }
    							  // purge cache for POST/PUT/DELETE
    							  else $this->purgeCache();
								  }
  								// set cookie
  							  if (isset($method['set-cookie'])) setcookie($method['set-cookie'], $response, 0, '/');
  							}
  							break;
  						}
  						if (!$response) usleep(100000);
  					}
  				}
  				else if ($condition != 'error' && $condition != 'exception') {
  					$msg = "SRA_ApiRouter::method - Error: unable to invoke method ${mlabel}";
  					SRA_Error::logError($msg, __FILE__, __LINE__);
  					$condition = 'error';
  				}
				}
				// non-async
				else {
          $code = '$response = $this->_controller->' . $method['method'] . '(';
          foreach(array_keys($args) as $i => $arg) $code .= ($i ? ', ' : '') . '$args["' . $arg . '"]';
          $code .= ');';
          try {
            eval($code);
            // cache response
            if ($ckey && $response) {              
						  // cache GET methods
						  if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
                SRA_Cache::setCache($ckey, $response, $method['cache-ttl']);
                $this->addCacheKey($ckey, $method['cache-ttl']);
						  }
						  // purge cache for POST/PUT/DELETE
						  else $this->purgeCache();
            }
          }
          catch (Exception $e) {
            $msg = sprintf('SRA_ApiRouter::method - Error: uncaught exception when invoking %s: %s', $code, $e->getMessage());
            SRA_Error::logError($msg, __FILE__, __LINE__);
            $condition = 'exception';
          } 
				}
			}
		}
		return $response;
	}
	
	/**
	 * initiates an http header redirect to the latest version of an API based
	 * on the parameters specified. Redirection will be to the same URI including 
	 * the same request parameters. The redirect http status code will be $status. 
	 * Returns TRUE on success, FALSE on failure (e.g. unable to determine 
	 * redirection directory). This method should be invoked by a script that has
	 * been invoked by http and exists in the parent directory to the versioned
	 * directories - see GitHub documentation for more details regarding how to 
	 * name such directories
	 * @param mixed $suffix supported pre-release directory suffixes if 
	 * applicable (a single allowed suffix or an array suffixes)
	 * @param array $exclude request parameters that be excluded from the 
	 * redirection
	 * @param int $status the http status code to use for redirection
	 * @return boolean
	 */
	public function redirect($suffix=NULL, $exclude=NULL, $status=302) {
		$redirected = FALSE;
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			$fstatus = $status;
			$uri = NULL;
			$versions = array();
			foreach(SRA_File::getFileList(dirname($_SERVER['SCRIPT_FILENAME']), '*', FALSE, 2) as $dir) {
				if (preg_match(SRA_API_ROUTER_CONTROLLER_VERSION_DIR_REGEX, basename($dir), $m)) {
					$value = 0;
					$place = 1000000;
					foreach(explode('.', $m[1]) as $i => $v) {
						$value += $v * $place;
						$place /= 10;
					}
					$versions[$value] = array('id' => $m[1], 'uri' => basename($dir), 'value' => $value);
					if ($l = trim(strtolower($m[2]))) $versions[$value]['suffix'] = $l;
				}
			}
			krsort($versions);
			$suffixes = is_array($suffix) ? $suffix : ($suffix ? array($suffix) : array());
			foreach($suffixes as $i => $l) $suffixes[$i] = trim(strtolower($l));
			foreach($versions as $version) {
				if (!isset($version['suffix']) || in_array($version['suffix'], $suffixes)) {
					$uri = $version['uri'];
					break;
				}
			}
			// request failed
			if (!$uri) $fstatus = $this->_failStatus;

			if ($fstatus) {
				SRA_ApiRouter::status($fstatus);
				// use redirect header
				if ($fstatus >= 300 && $fstatus < 400 && $uri) {
					// append request variables to the URI
					$url = $this->getServerUri() . '/' . str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) . '/' . $uri;
					foreach($_GET as $k => $v) if (!is_array($exclude) || !in_array($k, $exclude)) $url .= (strpos($url, '?') ? '&' : '?') . $k . '=' . urlencode($v);
					$url = SRA_ApiRouter::formatUrl($url);
					header("Location: $url");
				}
			}
			return $redirected = $fstatus == $status;
		}
		return $redirected;
	}
	
	/**
	 * exits from the current request with the appropriate response code and 
	 * message as indicated by $condition (default is 'ok'). Returns TRUE on 
	 * success, FALSE otherwise
	 * @param string $condition the exit condition - one of the following values: 
	 * error; exception; invalid; not-found; null; ok
	 * @param string $method if the response is for a method - this parameter
	 * may be specified defining the name of that method (one of the keys in 
	 * $this->_methods)
	 * @param string $contentType an optional content-type
	 * @param mixed $response an optional response value
	 * @param boolean $doc whether or not this response is for auto-generated 
	 * API documentation
	 * @param boolean $csv whether or not an entity response should be rendered 
	 * in CSV format if supported
	 * @return boolean
	 */
	private function response($condition='ok', $method=NULL, $contentType=NULL, &$response=NULL, $doc=FALSE, $csv=FALSE) {
		$responded = FALSE;
		
		if (!$method || isset($this->_methods[$method])) {
			if ($method) $config =& $this->_methods[$method];
			else $config =& $this->_settings;
			$scalar = !is_object($response) && !is_array($response);
			$code = NULL;
      $requestParams =& SRA_ApiRouter::getRequestParams();
			
			// look for alternate response code (method return dependent)
			if ($method && $scalar && isset($config['error-codes']) && count($config['error-codes'])) {
				foreach($config['error-codes'] as $val => $status) {
					try {
						$ecode = '$matched = ' . $val . ' === $response;';
						eval($ecode);
						if ($matched) {
							$code = $status['code'];
							break;
						}
					}
					catch (Exception $e) {
						$msg = "SRA_ApiRouter::response - Error: unable to evaluate value ${val}";
						SRA_Error::logError($msg, __FILE__, __LINE__);
					}
				}
			}
			// null condition code
			if ($condition == 'ok' && $method && $this->_methods[$method]['return']['type'] && $this->_methods[$method]['return']['type'] != 'void' && $response === NULL) $condition = 'null';
			
			// invalid parameter
			if (is_array($config) && isset($config['params']) && $response && preg_match('/^invalid\-(.*)$/', $response, $m) && ($pieces = explode('-', $m[1]))) {
				$condition = 'invalid';
				$config['headers-add']['sierra-api-invalid'] = $m[1];
				$response = NULL;
			}
			
			// status code
			if (!$code && isset($config['status-codes'][$condition]['code'])) $code = $config['status-codes'][$condition]['code'];
			
			if (is_array($config) && $code && SRA_ApiRouter::status($code)) {
				$responded = TRUE;
				
				// additional headers
				$headers = $config['headers-add'];
				if (!in_array($header = 'sierra-api-condition', $config['headers-remove'])) $headers[$header] = $condition;
				if (!in_array($header = 'sierra-api-runtime', $config['headers-remove'])) $headers[$header] = SRA_Controller::getRunTime();
				if ($method && isset($this->_methods[$method]['route']['fixed']) && !in_array($header = 'sierra-api-route', $config['headers-remove'])) $headers[$header] = $this->_methods[$method]['route']['fixed'];
				if ($method && isset($_SERVER['REQUEST_METHOD']) && !in_array($header = 'sierra-api-method', $config['headers-remove'])) $headers[$header] = $_SERVER['REQUEST_METHOD'];
				
				ksort($headers);
				
				// headers
				if (count($config['headers-remove'])) foreach($config['headers-remove'] as $header) {
					header_remove($header);
					if(isset($headers[$header])) unset($headers[$header]);
				}
				if (!is_array($headerList = headers_list())) $headerList = array();
				foreach(array_keys($headerList) as $i) $headerList[$i] = strtolower($headerList[$i]);
				foreach($headers as $header => $val) {
          // check if $val is in $_SERVER super global
          $pieces = explode('|', $val);
          if (isset($_SERVER[$pieces[0]]) && is_string($_SERVER[$pieces[0]])) $val = $_SERVER[$pieces[0]];
          else if (strtoupper($pieces[0]) == $pieces[0] && count($pieces) == 2) $val = $pieces[1];
				  if ((!$this->_accessControlAllowMethodsSent || strtolower($header) != 'access-control-allow-methods') && !in_array($header, $headerList)) header("${header}: ${val}");
			  }
				
				// beautify
				$useJsDates = isset($requestParams['use-date-object']) ? SRA_Util::convertBoolean($requestParams['use-date-object']) : FALSE;
				$beautify = FALSE;
				// determine whether or not to beautify the json
				if (!$useJsDates && $config['beautify']) {
      		// convert all headers to lower case
      		if ($rheaders = function_exists('getallheaders') ? getallheaders() : NULL) {
      		  foreach($rheaders as $key => $val) {
      		    unset($rheaders[$key]);
      		    $rheaders[strtolower($key)] = $val;
    		    }
      		}
      		if (isset($rheaders['accept'])) $beautify = strtolower($rheaders['accept']) == 'text/json';
      		else $beautify = isset($requestParams['beautify']) && SRA_Util::convertBoolean($requestParams['beautify']);
				}
				
				// content-type
				if ($contentType = $contentType ? $contentType : ($method ? ($scalar ? $config['content-type-other'] : 
					 ($csv && $method && isset($response) && $this->_methods[$method]['return']['csv-method'] ? 'text/csv' : ($beautify ? 'text/json' : 'application/json'))) : NULL)) {
				  header("Content-type: ${contentType}");
				}
				
				// Cache-Control
				if ($condition == 'ok' && is_numeric($cacheTtl = $config['cache-ttl' . ($doc ? '-doc' : '')])) $this->setCacheHeaders($cacheTtl);
        else if ($code >= 400) $this->setCacheHeaders(0);
				
				if ($method && isset($response) && !$scalar) {
					global $_utilDateFormat;
					global $_utilTimeFormat;
					if ($format = isset($requestParams['format-date']) ? $requestParams['format-date'] : NULL) {
						$_utilDateFormat = $format;
						$_utilTimeFormat = $format;
					}
					else {
						if (isset($this->_settings['format-date'])) $_utilDateFormat = $this->_settings['format-date'];
						if (isset($this->_settings['format-time'])) $_utilTimeFormat = $this->_settings['format-time'];
					}
					$entity = $this->_methods[$method]['return']['type'];
					
					if ($csv && $this->_methods[$method]['return']['csv-method']) {
						// determine filename for csv file
						$filename = str_replace('.csv', '', str_replace('_', '-', str_replace('.', '-', str_replace('/', '-', str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])))));
						if (substr($filename, 0, 1) == '-') $filename = substr($filename, 1);
						if (!$filename) $filename = $method;
						header("Content-Disposition: attachment; filename=${filename}.csv");
						
						$m = $this->_methods[$method]['return']['csv-method'];
						if (is_array($response)) {
							$started = FALSE;
							foreach(array_keys($response) as $i) {
								print($response[$i]->${m}(!$started));
								$started = TRUE;
							}
						}
						else print($response->${m}(TRUE));
					}
					else {
						$include = isset($this->_settings['entity-include'][$entity]) ? $this->_settings['entity-include'][$entity] : NULL;
						$exclude = isset($this->_settings['entity-exclude'][$entity]) ? $this->_settings['entity-exclude'][$entity] : NULL;
						// nested include/excludes
						if (method_exists($response, 'getAttributeTypesUsed')) {
						  foreach(array('include', 'exclude') as $t) {
  						  foreach(array_keys($this->_settings['entity-' . $t]) as $type) {
  						    if ($response->getAttributeTypesUsed($type)) {
  						      foreach($response->getAttributeNames() as $attr) {
  						        if ($response->getAttributeType($attr) == $type) {
  						          foreach($this->_settings['entity-' . $t][$type] as $add) {
  						            if ($t == 'include') {
  						              $a = $attr . '_' . $add;
    						            $include[] = $a; 
  						            }
  						            else {
  						              $a = $attr . '_' . $add;
    						            $exclude[] = $a;
  						            }
  					            }
  						        }
  						      }
  						    }
  						  } 
						  }
						}
            
						$json = method_exists($response, 'toJson') ? $response->toJson($include, $exclude, NULL, $useJsDates) : SRA_Util::toJson($response, $include, $exclude, $useJsDates);
						$json =& $this->cleanJson($json, $beautify, $this->_methods[$method]['beautify-round']);
						$callback = isset($requestParams['callback']) ? $requestParams['callback'] : (isset($requestParams['jsonp']) ? $requestParams['jsonp'] : NULL);
						print(($callback ? $callback . '(' : '') . $json . ($callback ? ');' : ''));
					}
				}
				else if ($method && $response !== NULL) {
				  if (is_bool($response)) print($response ? 'true' : 'false');
				  else print($response);
			  }
			}
		}
		
		return $responded;
	}
  
	/**
	 * attempts to route the current http request to the appropriate API method 
	 * or documentation endpoint - the not-found response code will be rendered 
	 * otherwise
   * @param string $uri optional explicit URI to route for (overrides 
   * REQUEST_URI)
	 * @return boolean
	 */
	public function route($uri=NULL) {
		$routed = FALSE;
		if ($this->_settings && $this->_methods) {
			$uri = $uri ? $uri : $_SERVER['REQUEST_URI'];
			
			// remove script and directory name prefixes
			if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			else if (preg_match('/^(.*)\.php$/', $_SERVER['SCRIPT_NAME'], $m) && strpos($uri, $m[1]) === 0) $uri = substr($uri, strlen($m[1]));
			
			if (substr($uri, 0, 1) != '/') $uri = '/' . $uri;
			// check if URI is for API documentation
			foreach(array('docs', 'doc-swagger', 'doc-swagger2', 'doc-mashape') as $doc) {
				$docUri = isset($this->_settings[$doc]) ? $this->_settings[$doc] : NULL;
        if (!trim($docUri)) continue;
        $docUri = trim($docUri);
				if (substr($docUri, 0, 1) != '/') $docUri = '/' . $docUri;
				if (strpos($uri, $docUri) === 0) {
					// max-execution-time and memory-limit
					if (isset($this->_settings['max-execution-time'])) ini_set('max_execution_time', $this->_settings['max-execution-time']);
					if (isset($this->_settings['memory-limit'])) ini_set('memory_limit', $this->_settings['memory-limit']);
					
					if (!in_array($header = 'sierra-api-doc', $this->_settings['headers-remove'])) $this->_settings['headers-add'][$header] = str_replace('doc-', '', $doc);
					// check for cache
					$cached = FALSE;
					$ckey = NULL;
					$response = NULL;
					if ($doc != 'docs' && SRA_API_ROUTER_CONTROLLER_USE_CACHE && strtolower($_SERVER['REQUEST_METHOD']) == 'get' && is_numeric($this->_settings['cache-ttl-doc']) && $this->_settings['cache-ttl-doc'] > 0) {
						$ckey = md5(SRA_API_ROUTER_CACHE_PREFIX . SRA_Controller::getAppName() . '_' . $this->_settings['api'] . '_' . $doc);
						$cacheTime = SRA_Cache::cacheIsset($ckey, TRUE);
						if ($cacheTime && $cacheTime > filemtime($this->_source) && $cacheTime > filemtime(__FILE__)) {
							$response =& SRA_Cache::getCache($ckey);
							$cached = TRUE;
						}
					}
					if (!$response) {
						$tpl =& SRA_Controller::getAppTemplate();
            if ($this->_settings['docs-function'] && function_exists($func = $this->_settings['docs-function'])) {
              if ($docsFunc = ${func}()) {
                $tpl->assign('docsFunc', $docsFunc);
                if (is_object($docsFunc)) {
                  // check for conditional methods
                  foreach(array_keys($this->_methods) as $key) {
                    if (isset($this->_methods[$key]['docs-function-conditional'])) {
                      $check = $this->_methods[$key]['docs-function-conditional'];
                      if (!(method_exists($docsFunc, $check) ? $docsFunc->${check}() : $docsFunc->${check})) unset($this->_methods[$key]);
                    }
                  }
                }
              }
              else {
      					$this->response('invalid', NULL, NULL, $nl=NULL, TRUE);
      					$routed = TRUE;
      					break;
              }
            }
						$tpl->assignByRef('router', $this);
						$tpl->assignByRef('api_resources', $this->getResources());
						$response =& $tpl->fetch($doc == 'docs' ? $this->_settings['docs-template'] : dirname(dirname(dirname(__FILE__))) . "/www/tpl/api/${doc}.tpl");
						if ($ckey) {
						  SRA_Cache::setCache($ckey, $response, $this->_settings['cache-ttl-doc']);
						  $this->addCacheKey($ckey, $this->_settings['cache-ttl-doc']);
					  }
					}
					if (!in_array($header = 'sierra-api-cached', $this->_settings['headers-remove'])) $this->_settings['headers-add'][$header] = $cached ? 'true' : 'false';
					$this->response('ok', NULL, $doc == 'docs' ? NULL : (preg_match('/swagger/', $doc) ? 'application/json' : 'text/xml'), $nl = NULL, TRUE);
					print($response);
					$routed = TRUE;
					break;
				}
			}
			
			if (!$routed) {
				$condition = 'not-found';
				$method = NULL;
				$response = NULL;
				$csv = FALSE;
				$allowMethods = array();
        $invokeMethod = NULL;
				foreach(array_keys($this->_methods) as $m) {
					if (strpos($uri, $f = $this->_methods[$m]['route']['fixed']) === 0 && count(explode('/', $uri)) <= $this->_methods[$m]['route']['parts'] && 
					    ($uri == $f || in_array(substr(str_replace($f, '', $uri), 0, 1), array('/', '?'))) && 
					    ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' || in_array($_SERVER['REQUEST_METHOD'], $this->_methods[$m]['http-methods']))) {
            if (!$invokeMethod || count(explode('/', $invokeMethod)) < $this->_methods[$m]['route']['parts']) $invokeMethod = $m;
          }
				}
        if ($invokeMethod) {
					$routed = TRUE;
					$method = $invokeMethod;
          foreach(array_keys($this->_methods) as $m) {
            if ($this->_methods[$m]['full'] == $this->_methods[$invokeMethod]['full']) {
              foreach($this->_methods[$m]['http-methods'] as $httpMethod) $allowMethods[$httpMethod] = TRUE;
            }
          }
			    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			      $condition = 'ok';
			      $response = '';
		      }
          else if (isset($this->_methods[$invokeMethod]['maintenance-file']) && 
                   $this->_methods[$invokeMethod]['maintenance-file'] != 'none' && 
                   file_exists($this->_methods[$invokeMethod]['maintenance-file'])) {
            $resources =& $this->getResources();
            $this->_methods[$invokeMethod]['status-codes']['null'] = array('code' => 503, 'description' => $resources->getString('api.status.503'));
            $this->_methods[$invokeMethod]['headers-add']['sierra-api-maintenance'] = 'true';
            $condition = 'null';
            $response = '';
          }
			    else {
            $response =& $this->method($uri, $invokeMethod, $condition, $csv);
          }
        }
		    if ($allowMethods) {
		      $this->_accessControlAllowMethodsSent = TRUE;
		      header('Access-Control-Allow-Methods: ' . implode(', ', array_keys($allowMethods)));
	      }
				$this->response($condition, $method, $condition != 'ok' ? 'text/plain' : NULL, $response, FALSE, $csv);
			}
		}
		return $routed;
	}
  
  
  /**
   * sets the Cache-Control response header to $ttl if it has not already been set
   * @param int $ttl the TTL (time-to-live) in seconds
   * @return boolean
   */
  function setCacheHeaders($ttl) {
    $isSet = FALSE;
    $cacheSent = FALSE;
    foreach(headers_list() as $cheader) {
      if (preg_match('/cache\-control/i', $cheader)) {
        $cacheSent = TRUE;
        break;
      }
    }
    if (!$cacheSent && is_numeric($ttl) && $ttl >= 0) {
      if ($ttl === 0) {
        header('Cache-Control: no-cache, must-revalidate');
    		header('Pragma: no-cache');
    		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
      }
      else {
        header('Cache-Control: ' . $ttl);
    		header(sprintf('Expires: %s', gmdate('D, j M Y H:i:s', time() + $ttl) . ' GMT'));
      }
      $isSet = TRUE;
    }
    return $isSet;
  }
	
	
	// static methods
	/**
	 * beautifies a JSON string (adds proper indentation)
	 * @param string $json JSON to beautify
	 * @return string
	 */
	private static function beautify($json, $round) {
		$beautified = NULL;
		SRA_File::write($tfile = SRA_File::createRandomFile(), $json);
		$beautified = shell_exec("cat ${tfile} | " . 'python -c "import sys, json; json.encoder.FLOAT_REPR = lambda f: (\'%.' . $round . 'f\' % f); print json.dumps(json.load(sys.stdin), sort_keys=True, indent=2)"');
		unlink($tfile);
		return $beautified ? $beautified : $json;
	}
		
	/**
	 * Creates a new SRA_ApiRouter instance that may be used to route API requests
	 * to a $controller - or redirect to the most current API version (see 
	 * corresponding methods below). If $controller is discovered and invalid, 
	 * this method returns NULL and triggers the http response code $fail
	 * @param string $controller the path (relative or absolute) to a PHP source
	 * file containing the API controller code and annotation defined settings.
	 * If this parameter is not specified, the router will look for a source file
	 * with 'Controller' in the name in the directory containing the script 
	 * responding to the current http request. If not specified or discovered, 
	 * the route method will be inoperable (will generate an http response code
	 * $fail if triggered)
	 * @param int $fail the http status code to trigger if $controller is invalid
	 * set to NULL to skip triggering of the status code
	 * @param boolean $debug whether or not to enable debug output
	 * @return SRA_ApiRouter
	 */
	public static function &create($controller=NULL, $fail=500, $debug=FALSE) {
		static $_routers;
		if (!is_array($_routers)) $_routers = array();
		$ckey = $_SERVER['SCRIPT_FILENAME'];
		
		if (!isset($_routers[$ckey])) {
			// look for controller in parent directory of SCRIPT_FILENAME
			if (!$controller && isset($_SERVER['SCRIPT_FILENAME']) && count($files = SRA_File::getFileList(dirname($_SERVER['SCRIPT_FILENAME'])))) {
				foreach($files as $file) if (preg_match(SRA_API_ROUTER_CONTROLLER_SOURCE_FILE_REGEX, $file)) $controller = $file;
			}
			if ($controller && (file_exists($controller) || file_exists($controller = str_replace('//', '/', SRA_Controller::getAppLibDir() . '/' . $controller)) || 
			                    file_exists($controller = str_replace('//', '/', SRA_Controller::getAppDir() . '/' . $controller)) || 
			                    (isset($_SERVER['SCRIPT_FILENAME']) && file_exists($controller = str_replace('//', '/', dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $controller))))) {
			  $_routers[$ckey] = new SRA_ApiRouter($controller);
				// initialize the controller - this method returns FALSE if the 
				// controller is invalid
				if (!$_routers[$ckey]->initController($debug)) {
					unset($_routers[$ckey]);
					if ($fail) SRA_ApiRouter::status($fail);
				}
			}
			else if ($controller && $fail) SRA_ApiRouter::status($fail);
			else if (!$controller) $_routers[$ckey] = new SRA_ApiRouter();
			
			// set generic fail status code
			if (isset($_routers[$ckey])) $_routers[$ckey]->_failStatus = $fail;
		}
		
		return isset($_routers[$ckey]) ? $_routers[$ckey] : ($nl = NULL);
	}
	
	/**
	 * formats a comment string - replaces newlines with spaces
	 * @param string $comment the comment to format
	 * @return string
	 */
	private static function formatDescription($comment) {
		return trim(str_replace('  ', ' ', str_replace('   ', ' ', str_replace('    ', ' ', str_replace("\n", ' ', $comment)))));
	}
	
	/**
	 * formats a comment string - replaces newlines with spaces
	 * @param string $comment the comment to format
	 * @return string
	 */
	private static function formatUrl($url) {
		while(strpos($url, '//')) $url = str_replace('//', '/', $url);
		return str_replace('http:/', 'http://', $url);
	}
	
	/**
	 * returns a hash representing the http status messages (indexed by http 
	 * status code) - alternatively, if $status is specified, the return value
	 * will be the associated message for that status code
	 * @param int $status a specific status code to return a message for - if
	 * specified, return value will be a string
	 * @return mixed
	 */
	private static function getMessages($status=NULL) {
		static $_messages;
		if (!isset($_messages) && ($bundle =& SRA_ResourceBundle::getBundle(dirname(dirname(dirname(__FILE__))) . '/etc/l10n/http-status-codes'))) $_messages = $bundle->getData();
		return $status ? (isset($_messages[$status]) ? $_messages[$status] : NULL) : $_messages;
	}
	
	/**
	 * returns the request parameters based on the current request method (GET, 
	 * POST, PUT, DELETE)
	 * return array
	 */
	private static function &getRequestParams() {
	  static $_requestParams;
	  if (!isset($_requestParams)) {
  		$_requestParams = array();
      $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
      if ($requestMethod == 'get') $_requestParams = $_GET;
      else {
        $raw = file_get_contents('php://input');
        if (is_array($json = json_decode($raw, TRUE))) $_requestParams = $json;
        else if ($raw) parse_str($raw, $_requestParams);
        else $_requestParams = array();
      }
	  }
    return $_requestParams;
	}
	
	/**
	 * completes an http request using the status code $status - returns TRUE on
	 * success, FALSE otherwise (i.e. $status is not valid)
	 * @param int $status the status code to use
	 * @return boolean
	 */
	private static function status($status=200) {
		$resuilt = FALSE;
		if ($msg = SRA_ApiRouter::getMessages($status)) {
			header($_SERVER['SERVER_PROTOCOL']." ${status} ${msg}");
			header("Status: ${status}");
			$result = TRUE;	
		}
		return $result;
	}
	
}
?>
