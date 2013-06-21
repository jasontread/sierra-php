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
require_once('util/SRA_Params.php');
// }}}

// {{{ Constants

/**
 * the attribute key under which the username will be stored in the 'attrs' attribute
 * @type string
 */
define('SRA_AUTHENTICATOR_ATTR_USER', 'user');

/**
 * status code signifying that the user was authenticated successfully
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_SUCCESS', 1);

/**
 * status code signifying that the user authentication failed (but a more 
 * specific reason is not known). this is the default status code.
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_FAILED', 2);

/**
 * status code signifying that the user authentication failed because the password 
 * specified is not valid (the user is valid)
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_INVALID_PSWD', 4);

/**
 * status code signifying that the user authentication failed because the username 
 * specified is not valid
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_INVALID_USER', 8);

/**
 * status code signifying that the user authentication was successful, but the 
 * user attribute validation failed
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_INVALID_ATTRS', 16);

/**
 * status code signifying that the user authentication failed due to a system error
 * @type int
 */
define('SRA_AUTHENTICATOR_STATUS_ERROR', 32);

/**
 * the default timeout value where access tracking is performed based on IP
 * @type int
 */
define('SRA_AUTHENTICATOR_DEFAULT_TIMEOUT', 15);

/**
 * file prefix for storing authenticator cache files and login counts in a file
 * @type string
 */
define('SRA_AUTHENTICATOR_FILE_PREFIX', '.sierra_auth_');

/**
 * the name of a cookie to set when a user logs out
 * @type string
 */
define('SRA_AUTHENTICATOR_LOGOUT_COOKIE', 'sraLogout');

/**
 * file prefix for storing logout records
 * @type string
 */
define('SRA_AUTHENTICATOR_LOGOUT_FILE_PREFIX', '.sierra_auth_logout_');

/**
 * the attribute key under which the username will be stored in the 'attrs' attribute
 * @type string
 */
define('SRA_AUTHENTICATOR_LOGOUT_FWD_VAR', 'sraLogoutFwd');

/**
 * SRA_Authenticator debug flag
 * @type  boolean
 */
define('SRA_AUTHENTICATOR_DEBUG', FALSE);

/**
 * SRA_Authenticator debug file
 * @type  string
 */
define('SRA_AUTHENTICATOR_DEBUG_FILE', SRA_DIR . '/tmp/.sierra_auth_debug');

// }}}

// {{{ SRA_Authenticator
/**
 * Abstract class defining the API that should be adhered to by any SIERRA 
 * SRA_Authenticator.
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_Authenticator {
  // {{{ Attributes
  // public attributes
	/**
   * associative array of user attributes. will always contain 
	 * the 'SRA_AUTHENTICATOR_ATTR_USER' hash key and may additionally specify 
   * other attributes extracted by the SRA_Authenticator
	 * @type array
	 */
	var $attrs = array();
	
	/**
   * if the status is SRA_AUTHENTICATOR_STATUS_INVALID_ATTRS, this will be the 
	 * name of the attribute that was not matched
	 * @type string
	 */
	var $unmatchedAttr;
	
	/**
   * an optional container for SRA_Authenticator specific error codes/messages
	 * @type string
	 */
	var $errMsg;

	/**
   * the authentication status. will correspond with one of the SRA_AUTHENTICATOR_STATUS_* 
	 * constants
	 * @type int
	 */
	var $status = SRA_AUTHENTICATOR_STATUS_FAILED;
	
  
  // private attributes
	/**
   * unique identifier for this authenticator instance
	 * @type string
	 */
	var $_id;
	
	/**
   * the log file where successful login records should be recorded. 
	 * if not specified, they will not be recorded. the path should be 
	 * relative to {sierra-dir}/log
	 * @type string
	 */
	var $_accessLog;
	
	/**
   * keeps track of the # of times the user has attempted to login with this 
	 * authenticator
	 * @type string
	 */
	var $_attemptCount = 0;
	
	/**
   * the template that should be displayed if the user clicks cancel when the 
	 * login box is displayed. if not specified, a blank screen will be displayed
	 * @type string
	 */
	var $_cancelTpl;
  
  /**
   * whether or not the user has already been prompted for their username and 
   * password from the console
   * @type boolean
   */
  var $_consolePrompted = FALSE;
  
	/**
   * allows the SRA_Authenticator instance to be morphed to a specific entity
	 * @type string
	 */
  var $_entityType;
	
	/**
   * whether or not the user exceeded the max allowed login attempts
	 * @type boolean
	 */
	var $_exceededMaxLoginAttemptCount = FALSE;
	
	/**
   * the log file where failed login attempts should be recorded. if 
	 * not specified, they will be recorded in the default app log 
	 * file
	 * @type string
	 */
	var $_failureLog;
	
	/**
   * a global PHP variable under which this SRA_Authenticator instance 
	 * should be stored when invoked. If an SRA_Authenticator already 
	 * exists in this variable, then it will be replaced with this
	 * instance.
	 * @type string
	 */
	var $_globalVar;
  
	/**
   * template containing an html login form which should be displayed instead of 
   * the default http authentication prompt (the login dialog displayed by your 
   * browser). when used, this form should post back to itself (action="") 
   * utilizing method="post" and contain form fields for both user (name="user") 
   * and password (name="password"). if a login is unsuccessful, this template 
   * will be re-dispalyed with the template variable "authStatus" set to one of 
   * the SRA_AUTHENTICATOR_STATUS_* constant values. 
   *
   *   Note: if multiple authenticators have been used, the template variable 
   *         "authStatus" will contain the status code from the last attempted 
   *         authenticator and "authStatus_[authenticator id]", template 
   *         variables will also be used in order to distinguish between the 
   *         different statuses. 
   *
   *   Note: if this template is being re-displayed due to a login error, the 
   *         previous post fields will be accessible using the template variable 
   *         'form' which will be a reference to $_POST
   *
   *   Note: if multiple authenticators are specified for a given resource, 
   *         'login-tpl' will be displayed from the last authenticator only
   *
   *   Note: standard http authentication will still be used for web services
   *
   *   Note: when login-tpl is used, the user and password will be stored in a 
   *         PHP session variable and thereby subject to session expiration and 
   *         repeat logins based on your PHP session ttl. when this occurs, the 
   *         template variable 'timeout' will be set to true
   * 
   *   Note: when this option is used, the $_SERVER['PHP_AUTH_USER'] and 
   *         $_SERVER['PHP_AUTH_PW'] super global values will automatically be 
   *         set
   *
   *   Note: if a session timeout occurs during a POST, you can support that 
   *         without losing data by adding something like the following to your 
   *         login-tpl:
   *          {foreach from=$form key=name item=val}
   *          {if $name neq 'user' && $name neq 'password'}
   *          <textarea name="{$name}" style="display:none">{$val}</textarea>
   *          {/if}
   *          {/foreach}
   *
	 * @type string
	 */
	var $_loginTpl;
	
	/**
   * the uri that the user should be forwarded to when a logout occurs
	 * @type string
	 */
	var $_logoutFwdUri;
	
	/**
   * $_GET variable that will result in the user being logged out of this 
	 * authenticator. a manual logout can also be performed via the 
	 * SRA_Authenticator.logout instance method. the value of this parameter should 
	 * be something besides empty string or 0 in order for a logout to be 
	 * triggered automatically
	 * @type string
	 */
	var $_logoutGetVar;
	
	/**
   * $_POST variable that will result in the user being logged out of this 
	 * authenticator. a manual logout can also be performed via the 
	 * SRA_Authenticator.logout instance method. the value of this parameter should 
	 * be something besides empty string or 0 in order for a logout to be 
	 * triggered automatically
	 * @type string
	 */
	var $_logoutPostVar;
	
	/**
   * the template that should be displayed if a logout has been performed. if 
	 * both logout-fwd-uri and logout-tpl are specified, the template will take 
	 * priority. if neither are specified, the user will be forwarded to the same 
	 * page they are on, resulting in an authentication dialog being displayed
	 * @type string
	 */
	var $_logoutTpl;
	
	/**
   * key/value pairs that must also be matched for a successful user 
	 * authentication to take place where the "key" is the identifier or the 
	 * attribute (as specified in the authenticator params of type "attrs") and 
	 * the value is a regular expression to match against that value.
	 * @type array
	 */
	var $_matchAllAttrs;
	
	/**
   * same as _matchAllAttrs, but only 1 needs to match
	 * @type array
	 */
	var $_matchOneAttrs;
	
	/**
   * template to display if an attribute match fails. attribute matches are 
	 * defined in the "restrict-access" elements. if not specified, the login 
	 * prompt wil be re-displayed until the user enters credentials with proper 
	 * access or the max-attempts is exceeded
	 * @type string
	 */
	var $_matchFailTpl;
	
	/**
   * the maximum # of failed attempts before the max-attempt-tpl 
	 * template is displayed. login attempts are tracked using sessions
	 * or IP address (based on the max-attempt-track attribute). if this
	 * attribute is 0 (default) then unlimited attempts are allowed
	 * @type int
	 */
	var $_maxAttempts;
	
	/**
   * the template to display if the user exceeds the "max-attempts" value. 
	 * REQUIRED if max-attempts is specified and > 0
	 * @type String
	 */
	var $_maxAttemptTpl;
	
	/**
   * whether or not to track login attempts based on IP. the default behavior is 
	 * to track based on a session cookie
	 * @type boolean
	 */
	var $_maxAttemptIp;
	
	/**
   * # of minutes to wait before allowing a user to re-attempt to login after 
	 * they have exceeded "max-attempts". Only applies to IP based login attempt 
	 * tracking "max-attempt-ip"
	 * @type int
	 */
	var $_maxAttemptTimeout;
	
	/**
   * the password to authenticate
	 * @type string
	 */
	var $_pswd;
	
	/**
   * the template to display if a system error occurs when this authenticator 
	 * is invoked
	 * @type String
	 */
	var $_sysErrTpl;
	
	/**
   * the template variable under which this SRA_Authenticator instance should be 
	 * stored when invoked. If an SRA_Authenticator already exists under this 
	 * variable, then it will be replaced with this instance. 
	 * @type String
	 */
	var $_tplVar;
	
	/**
   * the username to authenticate
	 * @type string
	 */
	var $_user;
	
	/**
   * whether or not sessions should be used to store valid logins. if sessions 
	 * are not used, then the user will be re-authenticated with each http request 
	 * (they will not be required to re-submit login information, but the 
	 * authenticator will be hit each time that they make a request) based on the 
	 * PHP_AUTH_USER and PHP_AUTH_PW global variables. the default value for this 
	 * attribute is TRUE
	 * @type boolean
	 */
	var $_useSessions;
	
	/**
   * the params specified for this authenticator
	 * @type SRA_Params
	 */
	var $_params = FALSE;
	
	/**
   * authentication resource string
	 * @type string
	 */
	var $_resource;
  
  /**
   * if an $entityType has been specified and successfully instantiated, this 
   * instance variable will be a reference to that entity for the user
   * @type object
   */
  var $_userEntity;
	
	/**
   * whether or not this SRA_Authenticator has already been written to cache
	 * @type boolean
	 */
	var $_written = FALSE;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Authenticator
	/**
	 * Constructor API. Should be invoked by all sub-classes on first line via 
	 * parent::SRA_Authenticator($user, $pass, $params). There should be no other 
	 * logic with sub-class constructors as this constructor will also invoke the 
	 * _authenticate method which is the location where all validation and attr 
	 * extraction logic should be placed. For an example of how to properly create 
	 * a new SRA_Authenticator, review the code for one of the standard SRA_Authenticator 
	 * implementations including SRA_DbAuthenticator and SRA_LdapAuthenticator
   * @access public
	 */
	function SRA_Authenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
                         $logoutFwdUri, $logoutGetVar, $logoutPostVar, $logoutTpl, $matchAllAttrs, $matchOneAttrs, 
												 $matchFailTpl, $maxAttempts, $maxAttemptTpl, $maxAttemptIp, 
												 $maxAttemptTimeout, & $params, $resource, $sysErrTpl, 
												 $tplVar, $useSessions) {
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("SRA_Authenticator::SRA_Authenticator: Instantiate authenticator - id: $id, " . 
										 "accessLog: $accessLog, cancelTpl: $cancelTpl, entityType: $entityType, $entityUserCol: $entityUserCol, failureLog: $failureLog, " . 
										 "globalVar: $globalVar, loginTpl: $loginTpl, logoutFwdUri: $logoutFwdUri, logoutGetVar: $logoutGetVar, " . 
										 "logoutPostVar: $logoutPostVar, logoutTpl: $logoutTpl, maxAttempts: $maxAttempts, " . 
										 "maxAttemptTpl: $maxAttemptTpl, maxAttemptTimeout=$maxAttemptTimeout, " . 
										 "resource: $resource, sysErrTpl: $sysErrTpl, tplVar: $tplVar, useSessions: $useSessions, PHP_AUTH_USER: " . 
                     $_SERVER['PHP_AUTH_USER'] . ' PHP_AUTH_PW: ' . $_SERVER['PHP_AUTH_PW'], 
										 SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		if (!$id) {
			$msg = "SRA_Authenticator::SRA_Authenticator: Failed - id not specified";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		$this->_id = $id;
		
		if ($accessLog) {
			$this->_accessLog = SRA_Controller::getAppLogDir() . '/' . $accessLog;
		}
		
		$this->_cancelTpl = $cancelTpl;
    $this->_entityType = $entityType;
    $this->_entityUserCol = $entityUserCol;
		if ($failureLog && $failureLog != '0') {
			$this->_failureLog = SRA_Controller::getAppLogDir() . '/' . $failureLog;
		}
		else if ($failureLog != '0') {
			$this->_failureLog = SRA_Controller::getAppErrorLogFile();
		}
		$this->_globalVar = $globalVar;
		$this->_matchAllAttrs = $matchAllAttrs;
		$this->_matchOneAttrs = $matchOneAttrs;
		$this->_matchFailTpl = $matchFailTpl;
    $this->_loginTpl = $loginTpl;
		$this->_logoutFwdUri = $logoutFwdUri;
		$this->_logoutGetVar = $logoutGetVar;
		$this->_logoutPostVar = $logoutPostVar;
		$this->_logoutTpl = $logoutTpl;
		$this->_maxAttempts = $maxAttempts;
		if ($maxAttempts > 0 && !$maxAttemptTpl) {
			$msg = "SRA_Authenticator::SRA_Authenticator: Failed - maxAttempts ${maxAttempts} specified, but no maxAttemptTpl";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		$this->_maxAttemptTpl = $maxAttemptTpl;
		$this->_maxAttemptIp = $maxAttemptIp;
		$this->_maxAttemptTimeout = SRA_AUTHENTICATOR_DEFAULT_TIMEOUT;
		if (is_numeric($maxAttemptTimeout) && $maxAttemptTimeout > 0) {
			$this->_maxAttemptTimeout = $maxAttemptTimeout;
		}
		if (!SRA_Params::isValid($params)) {
			$msg = "SRA_Authenticator::SRA_Authenticator: Failed - No SRA_Params parameter specified";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		$this->_params =& $params;
		$this->_pswd = $_SERVER['PHP_AUTH_PW'];
		$this->_user = $_SERVER['PHP_AUTH_USER'];
		if (!$sysErrTpl) {
			$msg = "SRA_Authenticator::SRA_Authenticator: Failed - sysErrTpl not specified";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		if (!$resource) {
			$msg = "SRA_Authenticator::SRA_Authenticator: Failed - resource not specified";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__);
			return;
		}
		$this->_resource = $resource;
		$this->_sysErrTpl = $sysErrTpl;
		$this->_tplVar = $tplVar;
		$this->_useSessions = $useSessions;
		
		$this->attrs[SRA_AUTHENTICATOR_ATTR_USER] = $this->_user;
		global $_wsGateway;
    
		// prompt for credentials if not already authenticated
		if (!$this->checkForLogout() && ((!isset($_SERVER['PHP_AUTH_USER'])) || (!isset($_SERVER['PHP_AUTH_PW'])) || (!SRA_Controller::runningFromCli() && $this->_logoutFileExists()))) {
      if (!$_wsGateway && !isset($_COOKIE[SRA_AUTHENTICATOR_LOGOUT_COOKIE]) && $this->_logoutFileExists()) {
        if (SRA_AUTHENTICATOR_DEBUG) 
          SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Logout cookie does not exist for - PHP_AUTH_USER: " . $_SERVER['PHP_AUTH_USER'] . ', ' . 
                           "PHP_AUTH_PW: " . $_SERVER['PHP_AUTH_PW'] . '. Logout file will be deleted', SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, 
                           FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE);
        unlink($this->_getLogoutFile());
      }
      else if (!$_wsGateway) {
        if (SRA_AUTHENTICATOR_DEBUG) 
          SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Prompting user - PHP_AUTH_USER: " . $_SERVER['PHP_AUTH_USER'] . ', ' . 
                           "PHP_AUTH_PW: " . $_SERVER['PHP_AUTH_PW'] . ', _logoutFileExists: ' . 
                           $this->_logoutFileExists(), SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, 
                           FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE);
        $this->_displayLoginDialog();
      }
		}
    
    $userVariable =& $this->_getUserVariable();
		
		// check for existing authentication instance in session

		$foundInSession = FALSE;
		if (!SRA_Controller::runningFromCli() && $this->_useSessions) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Use sessions, checking for valid session instance", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			$cache =& $this->_getFromCache();
			
			if ($cache) {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Cached session instance found (session id: " . session_id() . ")", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				
				// set match attrs if applicable
				if (count($this->_matchAllAttrs) || count($this->_matchOneAttrs)) {
					if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Setting and checking match attrs", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
					$cache->_matchAllAttrs = $this->_matchAllAttrs;
					$cache->_matchOneAttrs = $this->_matchOneAttrs;
					$cache->_matchAttributes(TRUE);
				}
				//update _attemptCount
				$this->_attemptCount = $cache->_attemptCount;

				// cache is valid
				if ($cache->status == SRA_AUTHENTICATOR_STATUS_SUCCESS) {
					if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Cached session is in success state", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
					$cache->_pswd = $this->_pswd;
					SRA_Util::mergeObject($this, $cache, TRUE);
          if (!isset($_SERVER['PHP_AUTH_USER'])) { $_SERVER['PHP_AUTH_USER'] = $this->_user; }
          $userVariable =& $this->_getUserVariable();
					$foundInSession = TRUE;
				}
				// user exceeded _maxAttempts in cache
				else if ($this->_maxAttempts > 0 && $cache->_exceededMaxLoginAttemptCount && $cache->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
					if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Max attempts exceeded and cached session is not in successful state", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
					$tpl =& SRA_Controller::getAppTemplate();
					$tpl->display($this->_maxAttemptTpl);
					exit;
				}
			}
			else {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Cached session instance not found", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			}
		}
		else if ($this->status != SRA_AUTHENTICATOR_STATUS_SUCCESS && $this->_maxAttempts > 0 && $this->_getLoginAttemptCount() > $this->_maxAttempts) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Sessions not used and max attempt count exceeded", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			$tpl =& SRA_Controller::getAppTemplate();
			$tpl->display($this->_maxAttemptTpl);
			exit;
		}
		
		if (!$foundInSession) {
      include_once('util/SRA_Cache.php');
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: SRA_Authenticator not found in session, authenticating", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
      $cacheTtl = NULL;
      $cacheKey = NULL;
      if ($this->_params && is_numeric($cacheTtl = $this->_params->getParam('cache-auth')) && $cacheTtl > 0 && $this->_user && $this->_pswd && ($temp = SRA_Cache::getCache($cacheKey = '_sraAuthCache_' . $this->_id . '_' . SRA_Controller::getCurrentAppId() . '_' . $this->_user . '_' . base64_encode($this->_pswd)))) {
        if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: got authentication status from cache", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
        $this->status = $temp;
      }
			else if (SRA_Error::isError($this->_authenticate($this->_user, $this->_pswd))) {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: SRA_Error: _authenticate returned error", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$this->status = SRA_AUTHENTICATOR_STATUS_ERROR;
			}
      else if ($cacheTtl && $cacheKey) {
        if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: caching authentication status " . $this->status, SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
        SRA_Cache::setCache($cacheKey, $this->status, $cacheTtl*1);
      }
			$this->_matchAttributes();
			$this->_completeAuth();
			
			if ($this->status == SRA_AUTHENTICATOR_STATUS_ERROR && $this->_sysErrTpl) {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: SRA_Error: System error occurred. displaying system error template", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$tpl =& SRA_Controller::getAppTemplate();
				$tpl->display($this->_sysErrTpl);
				exit;
			}
		}
		
		if ($this->_attemptCount || $this->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
			$this->_updateLoginAttempts();
		}
    
    if ($this->status == SRA_AUTHENTICATOR_STATUS_SUCCESS && ($this->_globalVar || $this->_tplVar)) {
      if (!$userVariable) { $userVariable =& $this->attrs; }
      if ($this->_globalVar) {
        SRA_Util::setGlobal($this->_globalVar, $userVariable);
      }
      if ($this->_tplVar) {
        $tpl =& SRA_Controller::getAppTemplate();
        $tpl->assignByRef($this->_tplVar, $userVariable);
      }
    }
		
		// store authentication instance in the session (if not already stored when _updateLoginAttempts was called above)
		if (!SRA_Controller::runningFromCli() && $this->_useSessions && !$foundInSession && !$this->_written) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Writing session to cache file", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			$this->_writeToCache();
		}
		else {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Write to cache is not necessary: _useSessions $this->_useSessions, foundInSession $foundInSession, written $this->_written", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		}

		if ($this->_exceededMaxLoginAttemptCount) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: SRA_Error: exceed max login attempt count", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			exit;
		}
		
		// check for logout
		if ($this->status == SRA_AUTHENTICATOR_STATUS_SUCCESS && $this->checkForLogout()) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: Logout occurred", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			$this->logout();
		}
		
    global $_sraAuthSkipConstructorLogin;
		if ($this->status == SRA_AUTHENTICATOR_STATUS_SUCCESS) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: SUCCESS", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		}
		else if (!$_sraAuthSkipConstructorLogin) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::SRA_Authenticator: FAILED - $this->status", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
      if (!$_wsGateway) { $this->_displayLoginDialog(); }
		}
	}
	// }}}
	
  
  // public operations
	
	
	// {{{ checkForLogout
	/**
	 * returns TRUE if one of the logout $_GET or $_POST values exists
   * @access public
	 * @return boolean
	 */
	function checkForLogout() {
		return (($this->_logoutGetVar && $_GET[$this->_logoutGetVar]) || ($this->_logoutPostVar && $_POST[$this->_logoutPostVar]));
	}
	// }}}
	
	// {{{ logout
	/**
	 * removed the user's login state to this authenticator
   * @access public
	 * @return void
	 */
	function logout() {
    if (!SRA_Controller::runningFromCli()) {
      if ($this->_id && $this->_user && !$this->_logoutFileExists() && !$this->_loginTpl) {
        SRA_File::touch($this->_getLogoutFile());
        setcookie(SRA_AUTHENTICATOR_LOGOUT_COOKIE);
      }
      $this->_killSession();
      if (isset($_GET[SRA_AUTHENTICATOR_LOGOUT_FWD_VAR])) {
        header('Location: ' . $_GET[SRA_AUTHENTICATOR_LOGOUT_FWD_VAR]);
      }
      else if ($this->_logoutTpl) {
        $tpl =& SRA_Controller::getAppTemplate();
        $tpl->display($this->_logoutTpl);
      }
      else if ($this->_logoutFwdUri) {
        header('Location: ' . $this->_logoutFwdUri);
      }
      else {
        header('Location: ' . $_SERVER['SCRIPT_URL']);
      }
      exit;
    }
	}
	// }}}
	
	// {{{ authenticate()
	/**
	 * Static method used to initiate authentication. called each time a app 
	 * is initialized (using SRA_Controller::init())
	 *
	 * @param array $sysConf the system configuration
	 * @param array $appConf the app configuration
	 * @access public static
	 * @return boolean
	 */
	function authenticate(& $sysConf, & $appConf) {
    global $_wsGateway;
    if (isset($appConf['restrict-access']) && isset($appConf['restrict-access']['attributes'])) {
      $appConf['restrict-access'] = array($appConf['restrict-access']);
    }
    if (isset($sysConf['restrict-access']) && isset($sysConf['restrict-access']['attributes'])) {
      $sysConf['restrict-access'] = array($sysConf['restrict-access']);
    }
		// restrictions
		if (isset($appConf['restrict-access']) && is_array($appConf['restrict-access']) && is_array($sysConf['restrict-access'])) {
      $restrictions = array();
      $keys = array_keys($appConf['restrict-access']);
      foreach($keys as $key) {
        $restrictions[] = $appConf['restrict-access'][$key];
      }
      $keys = array_keys($sysConf['restrict-access']);
      foreach($keys as $key) {
        $restrictions[] = $sysConf['restrict-access'][$key];
      }
		}
		else if (isset($appConf['restrict-access']) && is_array($appConf['restrict-access'])) {
			$restrictions = $appConf['restrict-access'];
		}
		else if (isset($sysConf['restrict-access']) && is_array($sysConf['restrict-access'])) {
			$restrictions = $sysConf['restrict-access'];
		}
		// authenticators
		if (isset($appConf['authenticator']) && is_array($appConf['authenticator']) && is_array($sysConf['authenticator'])) {
			$authenticators = array_merge($appConf['authenticator'], $sysConf['authenticator']);
		}
		else if (isset($appConf['authenticator']) && is_array($appConf['authenticator'])) {
			$authenticators = $appConf['authenticator'];
		}
		else if (isset($sysConf['authenticator']) && is_array($sysConf['authenticator'])) {
			$authenticators = $sysConf['authenticator'];
		}
		
		if (isset($restrictions) && isset($authenticators)) {
			$keys = array_keys($restrictions);
			$doAuths = array();
			$authInstances = array();
			$authMatchAllParams = array();
			$authMatchOneParams = array();
			foreach ($keys as $key) {
				foreach(explode('|', $restrictions[$key]['attributes']['match']) as $match) {
          $match = $match == '*' ? '.*' : $match;
          if (!SRA_Util::beginsWith($match, '/')) $match = '/' . $match;
          if (!SRA_Util::endsWith($match, '/')) $match .= '/i';
          
          if ((trim(strtolower($match)) == strtolower($_SERVER['SCRIPT_FILENAME']) || 
              strstr(strtolower($_SERVER['SCRIPT_FILENAME']), strtolower($match)) || 
              preg_match($match, $_SERVER['SCRIPT_FILENAME']) || 
              preg_match($match, $_SERVER['REQUEST_URI']))) {
            $authIds = explode(' ', $restrictions[$key]['attributes']['authenticators']);
            foreach ($authIds as $authId) {
              if (!isset($authMatchAllParams[$authId])) {
                $authMatchAllParams[$authId] = array();
              }
              if (!isset($authMatchOneParams[$authId])) {
                $authMatchOneParams[$authId] = array();
              }
              if (!isset($authenticators[$authId])) {
                $msg = "SRA_Authenticator::authenticate: Failed - SRA_Authenticator id ${authId} is not valid";
                return SRA_Error::logError($msg, __FILE__, __LINE__);
                return FALSE;
              }
              else {
                if (isset($restrictions[$key]['param'])) {
                  $matchAttrs = new SRA_Params($restrictions[$key]['param']);
                  $matchAttrs = $matchAttrs->getParams(array($id, SRA_PARAMS_NO_TYPE));
                  if ($restrictions[$key]['attributes']['validate-one-attr'] == '1') {
                    SRA_Util::mergeArray($authMatchOneParams[$authId], $matchAttrs);
                  }
                  else {
                    SRA_Util::mergeArray($authMatchAllParams[$authId], $matchAttrs);
                  }
                }
                $doAuths[$authId] = $restrictions[$key];
              }
            }
          }
        }
			}
			$authenticated = $doAuths && count($doAuths) ? FALSE : TRUE;
      global $_sraAuthSkipConstructorLogin;
			foreach (array_keys($doAuths) as $authId) {
        $_sraAuthSkipConstructorLogin = TRUE;
        if (!SRA_Authenticator::_evalRestrictAccessCond($doAuths[$authId]['attributes'])) {
          unset($authInstances[$authId]);
          continue; 
        }
				if (SRA_Authenticator::isValid($authInstances[$authId] =& SRA_Authenticator::_getAuthenticator($authenticators[$authId], $authMatchAllParams[$authId], $authMatchOneParams[$authId]))) {
					if (!SRA_Controller::runningFromCli() && $restrictions[$key]['attributes']['auth-all'] == '1' && $authInstances[$authId]->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
            $authInstances[$authId]->_displayLoginDialog();
					}
					else if ($authInstances[$authId]->status == SRA_AUTHENTICATOR_STATUS_SUCCESS) {
						$authenticated = TRUE;
            if ($restrictions[$key]['attributes']['auth-all'] != '1') break;
					}
				}
				else {
					$msg = "SRA_Authenticator::authenticate: Failed - SRA_Authenticator id ${authId} could not be instantiated";
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
      unset($_sraAuthSkipConstructorLogin);
			if (!$authenticated) {
				foreach ($authIds as $authId) {
					if (isset($authInstances[$authId]) && $authInstances[$authId]->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
						if (!$_wsGateway) { 
              if ($authInstances[$authId]->_failureLog) SRA_LogEntry::log('FAILED: ' . $authInstances[$authId]->status . ' - ' . $authInstances[$authId]->_completeAuthLogMessage(), $authInstances[$authId]->_failureLog, TRUE, $authInstances[$authId]->_id);
              $authInstances[$authId]->_displayLoginDialog();
            }
					}
				}
			}
		}
    else if (SRA_Controller::runningFromCli() || $_wsGateway) {
      $authenticated = TRUE;
    }
		return SRA_Controller::runningFromCli() || $_wsGateway ? $authenticated : TRUE;
	}
	// }}}
  
	// {{{ promptConsole
	/**
	 * this method prompts the user for a username and password at the command 
   * line and sets those values in the $_SERVER['PHP_AUTH_PW'] and 
   * $_SERVER['PHP_AUTH_USER'] globals. it returns TRUE on success, FALSE 
   * otherwise. this method can be overriden by implementing authenticators
	 * @access public
	 * @return boolean
	 */
	function promptConsole() {
    $user = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    if (!$user || !$password) {
      $rb =& SRA_Controller::getAppResources();
      $_readline = function_exists('readline');
      
      if (!$_readline) { $stdin = fopen('php://stdin', 'r'); }
      echo $rb->getString($this->_resource) . "\n\n";
      while(TRUE) {
        if (!$user) {
          if ($_readline) {
            $user = readline($rb->getString('console.auth.user') . ' ');
          }
          else {
            echo $rb->getString('console.auth.user') . ' ';
            $user = trim(fgets($stdin,1000));
          }
          if (!$user) { continue; }
        }
        if (!$password) {
          if ($_readline) {
            $password = readline($rb->getString('console.auth.password') . ' ');
          }
          else {
            echo $rb->getString('console.auth.password') . ' ';
            $password = trim(fgets($stdin,1000));
          }
          if (!$password) { continue; }
        }
        break;
      }
      if (!$_readline) { fclose($stdin); }
    }
    $_SERVER['PHP_AUTH_USER'] = $user;
    $_SERVER['PHP_AUTH_PW'] = $password;
    return TRUE;
	}
	// }}}
	
	
	// {{{ getUserId()
	/**
	 * Static method that returns the user id for the current active user if the 
	 * user is in a secured area
	 *
	 * @access	public static
	 * @return	string
	 */
	function getUserId() {
		return $_SERVER['PHP_AUTH_USER'];
	}
	// }}}
	
	
	// {{{ matchAttribute
	/**
	 * returns TRUE if a given attribute is present and equal to the value 
	 * specified (if a value is specified - otherwise returns TRUE if present). 
	 * if the $attr is an array and $val is specified, then each value in the 
	 * $attr will be checked for a corresponding match
	 * @param string $attr the name of the attribute
	 * @param string $val an optional value to match. may be a regular expression
	 * or a fixed value
   * @access public
	 * @return boolean
	 */
	function matchAttribute($attr, $val = FALSE) {
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::matchAttribute: attr: $attr, val: $val", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug($this->attrs, SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
    $entityAttr = FALSE;
		if (isset($this->attrs[$attr]) || (isset($this->_userEntity) && is_object($this->_userEntity) && ($entityAttr = $this->_userEntity->getAttribute($attr)))) {
			if ($val) {
				$attr = $entityAttr ? $entityAttr : $this->attrs[$attr];
				if (!is_array($attr)) {
					$attr = array($attr);
				}
				$keys = array_keys($attr);
				foreach ($keys as $key) {
					if ((trim(strtolower($val)) == trim(strtolower($attr[$key])) || 
							strstr(trim(strtolower($attr[$key])), trim(strtolower($val))) || 
							ereg("^" . strtolower($val) . "+$", strtolower($attr[$key])))) {
						return TRUE;
					}
				}
			}
			else {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
	
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_Authenticator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_authenticator' || is_subclass_of($object, 'SRA_Authenticator')));
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * Returns a string representation of this object
   * @access public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
  
  // private operations
	// {{{ _authenticate
	/**
	 * MUST be implemented by sub-classes. Should authenticate the user and set 
	 * the value of the status attribute according to one of the SRA_AUTHENTICATOR_STATUS_* 
	 * constants
   * @param string $user the username to authenticate
   * @param string $pswd the password to authenticate
   * @access private
	 * @return void or SRA_Error object
	 */
	function _authenticate($user, $pswd) {
		$msg = "SRA_Authenticator::_authenticate: Failed - MUST be implemented by the sub-class";
		return SRA_Error::logError($msg, __FILE__, __LINE__);
	}
	// }}}
	
	// {{{ _completeAuth
	/**
	 * If an authentication does not result in an SRA_Error, this method is invoked to 
	 * complete the login process based on the status code set by the corresponding 
	 * sub-class _authenticate invocation
   * @access private
	 * @return void
	 */
	function _completeAuth() {
		$tpl =& SRA_Controller::getAppTemplate();
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_completeAuth: Logging - " . $this->_completeAuthLogMessage(), SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		// status specific behavior
		switch ($this->status) {
			case SRA_AUTHENTICATOR_STATUS_SUCCESS: 
				if ($this->_accessLog) {
					SRA_LogEntry::log("SUCCESS: " . $this->_completeAuthLogMessage(), $this->_accessLog, TRUE, $this->_id);
				}
				break;
			case SRA_AUTHENTICATOR_STATUS_FAILED: 
			case SRA_AUTHENTICATOR_STATUS_INVALID_PSWD: 
			case SRA_AUTHENTICATOR_STATUS_INVALID_USER: 
			case SRA_AUTHENTICATOR_STATUS_INVALID_ATTRS:
        global $_sraAuthSkipConstructorLogin;
				if (!$_sraAuthSkipConstructorLogin && $this->_failureLog) {
					SRA_LogEntry::log('FAILED: ' . $this->status . ' - ' . $this->_completeAuthLogMessage(), $this->_failureLog, TRUE, $this->_id);
				}
				break;
			case SRA_AUTHENTICATOR_STATUS_ERROR: 

				break;
		}
    if ($this->_loginTpl) {
      $tpl =& SRA_Controller::getAppTemplate();
      $tpl->assign('authStatus', $this->status);
      $tpl->assign('authStatus_' . $this->_id, $this->status);
      if ($this->status == SRA_AUTHENTICATOR_STATUS_SUCCESS && $_POST && isset($_POST['user']) && isset($_POST['password'])) {
        setcookie('sra-auth-completed', '1');
      }
    }
	}
	// }}}
  
  
	// {{{ _completeAuthLogMessage
	/**
	 * returns the log message used by _completeAuth
   * @access private
	 * @return void
	 */
	function _completeAuthLogMessage() {
		return 'RESOURCE: ' . $_SERVER['SCRIPT_FILENAME'] . ' USER: ' . $this->_user . 
							' IP: ' . $_SERVER['REMOTE_ADDR'] . ' ATTEMPT: ' . $this->_getLoginAttemptCount() . 
							' SERVER: ' . $_SERVER['SERVER_NAME'] . ' AGENT: ' . $_SERVER['HTTP_USER_AGENT'] . 
              ' CACHE FILE: ' . $this->_getCacheFileName();
	}
	// }}}
	
	
	// {{{ _displayLoginDialog
	/**
	 * prompts the user for login credentials
   * @access private
	 * @return SRA_Authenticator
	 */
	function _displayLoginDialog() {
    global $_wsGateway;
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_displayLoginDialog: Display login prompt", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		$rb =& SRA_Controller::getAppResources();
		if (SRA_Controller::runningFromCli()) {
      if (!$this->_consolePrompted) {
        if (!$this->promptConsole()) {
          exit;
        }
        $this->_consolePrompted = TRUE;
        $this->_user = $_SERVER['PHP_AUTH_USER'];
        $this->_pswd = $_SERVER['PHP_AUTH_PW'];
        $this->attrs[SRA_AUTHENTICATOR_ATTR_USER] = $this->_user;
      }
    }
    else if ($this->_loginTpl && !$_wsGateway) {
      $tpl =& SRA_Controller::getAppTemplate();
      $tpl->assign('timeout', isset($_COOKIE['sra-auth-completed']) && $_COOKIE['sra-auth-completed'] == '1');
      $tpl->assignByRef('form', $_POST);
      $tpl->display($this->_loginTpl);
      exit;
    }
    else {
      header('WWW-Authenticate: Basic realm="' . $rb->getString($this->_resource) . '"'); 
      header('HTTP/1.0 401 Unauthorized');
      if ($this->_cancelTpl) {
        $tpl =& SRA_Controller::getAppTemplate();
        $tpl->display($this->_cancelTpl);
      }
      if ($this->_logoutFileExists()) {
        unlink($this->_getLogoutFile());
      }
      exit;
    }
	}
	// }}}
  
	// {{{ _evalRestrictAccessCond
	/**
	 * evaluates the conditional parameters for a "restrict-access" element as 
   * defined in app-config. returns TRUE if no conditional parameters are 
   * specified, or if they are specified and evaluate to TRUE
   * @param hash $config hash containing the "restrict-access" "cond-" 
   * parameters (see app-config dtd for more info)
   * @access private
	 * @return boolean
	 */
  function _evalRestrictAccessCond(&$config) {
    $result = TRUE;
    if (isset($config['cond-var'])) {
      $config['cond-op'] = isset($config['cond-op']) ? $config['cond-op']*1 : 1;
      $config['cond-var-type'] = isset($config['cond-var-type']) && ($config['cond-var-type'] == 'form' || $config['cond-var-type'] == 'global' || $config['cond-var-type'] == 'session') ? $config['cond-var-type'] : 'global';
      if ($config['cond-var-type'] == 'form') {
        $var = isset($_POST[$config['cond-var']]) ? $_POST[$config['cond-var']] : (isset($_GET[$config['cond-var']]) ? $_GET[$config['cond-var']] : NULL);
      }
      else if ($config['cond-var-type'] == 'global') {
        $var =& SRA_Util::getGlobal($config['cond-var']);
      }
      else {
        session_start();
        $var = $_SESSION[$config['cond-var']];
      }
      if (is_object($var) && isset($config['cond-var-attr'])) {
        $attr = $config['cond-var-attr'];
        $varAttr = isset($var->${attr}) ? $var->${attr} : (method_exists($var, $attr) ? $var->${attr}() : (method_exists($var, 'getAttribute') ? $var->getAttribute($attr) : NULL));
      }
      else {
        $varAttr = $var;
      }
      $val = isset($config['cond-val']) ? $config['cond-val'] : TRUE;
      
      // greater than/equal
      if ($config['cond-op'] & 2 && $config['cond-op'] & 1) { $result = $varAttr >= $val; }
      
      // greater than
      else if ($config['cond-op'] & 2) { $result = $varAttr > $val; }
      
      // less than/equal
      else if ($config['cond-op'] & 4 && $config['cond-op'] & 1) { $result = $varAttr <= $val; }
      
      // less than
      else if ($config['cond-op'] & 4) { $result = $varAttr < $val; }
      
      // identical
      else if ($config['cond-op'] & 1 && $config['cond-op'] & 512) { $result = $varAttr === $val; }
      
      // equal
      else if ($config['cond-op'] & 1) { $result = $varAttr == $val; }
      
      // starts with
      else if ($config['cond-op'] & 8) { $result = SRA_Util::beginsWith($varAttr, $val); }
      
      // ends with
      else if ($config['cond-op'] & 16) { $result = SRA_Util::endsWith($varAttr, $val); }
      
      // substring
      else if ($config['cond-op'] & 32) { $result = strpos($varAttr, $val) !== FALSE; }
      
      // negate
      if ($config['cond-op'] & 256) { $result = !$result; }
      
    }
    return $result;
  }
  // }}}
	
	
	// {{{ _getAuthenticator
	/**
	 * instantiate and returns an SRA_Authenticator based on the configuration and 
	 * restrictions specified
   * @access private
	 * @return SRA_Authenticator
	 */
	function & _getAuthenticator(& $conf, $matchAllAttrs, $matchOneAttrs) {
		$id = $conf['attributes']['key'];
		
		// hard-coded path
		if ($conf['attributes']['path']) {
			$search = $conf['attributes']['path'];
		}
		else {
			$search = $conf['attributes']['type'] . '.' . SRA_SYS_PHP_EXTENSION;
		}
		$search = SRA_Util::removeLeadingSlash($search);
		if (file_exists(SRA_Controller::getAppLibDir() . '/' . $search)) {
			$path = SRA_Controller::getAppLibDir() . '/' . $search;
		}
		else if (file_exists(SRA_LIB_DIR . '/auth/' . $search)) {
			$path = SRA_LIB_DIR . '/auth/' . $search;
		}
		else if (file_exists(SRA_LIB_DIR . '/' . $search)) {
			$path = SRA_LIB_DIR . '/' . $search;
		}
		else if (file_exists($search)) {
			$path = $search;
		}
		else if (file_exists('/' . $search)) {
			$path = '/' . $search;
		}
		if (!$path) {
			$msg = "SRA_Authenticator::_getAuthenticator: Failed - Cannot locate authenticator ${id}";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$className = $conf['attributes']['type'];
		include_once($path);
		if (!class_exists($className)) {
			$msg = "SRA_Authenticator::_getAuthenticator: Failed - Class type ${className} is not valid";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$maxAttempts = 0;
		if (isset($conf['attributes']['max-attempts'])) {
			$maxAttempts = $conf['attributes']['max-attempts'];
		}
		$maxAttemptIp = FALSE;
		if (isset($conf['attributes']['max-attempt-ip'])) {
			$maxAttemptIp = $conf['attributes']['max-attempt-ip'] == '1';
		}
		$maxAttemptTimeout = SRA_AUTHENTICATOR_DEFAULT_TIMEOUT;
		if (isset($conf['attributes']['max-attempt-timeout'])) {
			$maxAttemptTimeout = $conf['attributes']['max-attempt-timeout'];
		}
		$useSessions = TRUE;
		if (isset($conf['attributes']['use-sessions'])) {
			$useSessions = $conf['attributes']['use-sessions'] == '1';
		}
    
    if (isset($conf['attributes']['login-tpl'])) {
      session_start();
      // check for login-tpl form post
      if (count($_POST) && $_POST['user'] && $_POST['password']) {
        $_SERVER['PHP_AUTH_USER'] = $_POST['user'];
        $_SERVER['PHP_AUTH_PW'] = $_POST['password'];
        $_SESSION['sra-auth-user'] = $_POST['user'];
        $_SESSION['sra-auth-pw'] = $_POST['password'];
      }
      // check for login credentials in session
      else if (isset($_SESSION['sra-auth-user']) && isset($_SESSION['sra-auth-pw'])) {
        $_SERVER['PHP_AUTH_USER'] = $_SESSION['sra-auth-user'];
        $_SERVER['PHP_AUTH_PW'] = $_SESSION['sra-auth-pw'];
      }
    }
    
    // don't use sessions for wget or shell connections
    if ($useSessions) $useSessions = !isset($_SERVER['HTTP_USER_AGENT']) || preg_match('/wget/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/curl/i', $_SERVER['HTTP_USER_AGENT']) ? FALSE : TRUE;
    
		$obj = new ${className}($id, $conf['attributes']['access-log'], $conf['attributes']['cancel-tpl'], 
                            $conf['attributes']['entity-type'], $conf['attributes']['entity-user-col'], 
														$conf['attributes']['failure-log'], $conf['attributes']['global-var'], 
														$conf['attributes']['login-tpl'], $conf['attributes']['logout-fwd-uri'], 
														$conf['attributes']['logout-get-var'], $conf['attributes']['logout-post-var'], 
														$conf['attributes']['logout-tpl'], $matchAllAttrs, $matchOneAttrs, 
														$conf['attributes']['match-fail-tpl'], 
												 		$maxAttempts, $conf['attributes']['max-attempt-tpl'], 
														$maxAttemptIp, $maxAttemptTimeout, new SRA_Params($conf['param']), 
														$conf['attributes']['resource'], $conf['attributes']['sys-err-tpl'], 
														$conf['attributes']['tpl-var'], $useSessions);
														
		return $obj;
	}
	// }}}
	
	
	// {{{ _getCacheFileName
	/**
	 * returns the file name to use for caching operations for this instance of SRA_Authenticator
   * @access private
	 * @return string
	 */
	function _getCacheFileName() {
		if (!session_id()) { session_start(); }
		$cacheFile = $this->_getCacheFilePrefix() . session_id();
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getCacheFileName: Cache file is $cacheFile", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		return session_id() ? $cacheFile : FALSE;
	}
	// }}}
	
	
	// {{{ _getCacheFilePrefix
	/**
	 * returns the file name prefix for session cache files
   * @access private
	 * @return string
	 */
	function _getCacheFilePrefix() {
		return SRA_Controller::getAppTmpDir() . '/' . SRA_AUTHENTICATOR_FILE_PREFIX . $this->_id;
	}
	// }}}
	
	
	// {{{ _getFromCache
	/**
	 * gets an SRA_Authenticator from cache if it exists. this function only works with 
	 * sessions enabled
   * @access private
	 * @return SRA_Authenticator|FALSE
	 */
	function & _getFromCache() {
		static $cached = array();
		
		if ($this->_writeToCache(TRUE)) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getFromCache: Instance retrieved from write cache", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			return $this->_writeToCache(TRUE);
		}
		else {
			$fileName = $this->_getCacheFileName();
			if ($fileName && !isset($cached[$fileName])) {
				$cached[$fileName] = file_exists($fileName) ? unserialize(SRA_File::toString($fileName)) : FALSE;
        if (!$this->_user && SRA_Authenticator::isValid($cached[$fileName])) { $this->_user = $cached[$fileName]->_user; }
				if (SRA_AUTHENTICATOR_DEBUG && file_exists($fileName)) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getFromCache: Instance retrieved from file $fileName", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			}
			return isset($cached[$fileName]) && SRA_Authenticator::isValid($cached[$fileName]) ? $cached[$fileName] : FALSE;
		}
	}
	// }}}
	
	
	// {{{ _getLoginAttemptCount
	/**
	 * returns the # of times that this user has attempted to login based on the 
	 * tracking method specified (IP or sessions)
   * @access private
	 * @return int
	 */
	function _getLoginAttemptCount() {
    if (!SRA_Controller::runningFromCli()) {
      // tracking based on IP
      if ($this->_maxAttemptIp) {
        $file = SRA_Controller::getAppTmpDir() . '/' . SRA_AUTHENTICATOR_FILE_PREFIX . $this->_id . $_SERVER['REMOTE_ADDR'];
        // delete old files
        if (file_exists($file) && (filectime($file) < (time() - ($this->_maxAttemptTimeout * 60)))) {
          unlink($file);
        }
        
        $ret = 1;
        if (file_exists($file)) {
          $fp = fopen($file, 'r');
          $data = fread($fp, 4096);
          fclose($fp);
          $data = explode('\n', $data);
          if (is_numeric($data[0])) {
            $ret = $data[0];
          }
        }
        if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getLoginAttemptCount: Returning login attempt count $ret for IP " . $_SERVER['REMOTE_ADDR'] . " from file $file", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
        return $ret;
      }
      // tracking based on session
      else {
        if ($this->_attemptCount) {
          return $this->_attemptCount;
        }
        else if ($auth =& $this->_getFromCache()) {
          $ret = $auth->_attemptCount;
        }
        else {
          $ret = 1;
        }
        if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getLoginAttemptCount: Returning login attempt count $ret for Session " . session_id(), SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
        return $ret;
      }
    }
    else {
      return 1;
    }
	}
	// }}}
	
	
	// {{{ _getLogoutFile
	/**
	 * returns the path to the logout file. this file will be created each time 
	 * the user logs out and deleted (if it exists) each time the user logs in 
	 * successfully
   * @access private
	 * @return string
	 */
	function _getLogoutFile() {
		$logoutFile = SRA_Controller::getAppTmpDir() . '/' . SRA_AUTHENTICATOR_LOGOUT_FILE_PREFIX . $this->_user;
		if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_getLogoutFile: Returning logout file $logoutFile", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
		return $logoutFile;
	}
	// }}}
	
  
	// {{{ _getUserVariable
	/**
	 * returns the user variable for this authenticator (if applicable)
   * @access private
	 * @return mixed
	 */
  function &_getUserVariable() {
    if (isset($_SERVER['PHP_AUTH_USER']) && $this->_entityType) {
      if (!SRA_Error::isError($dao =& SRA_DaoFactory::getDao($this->_entityType))) {
        if ($this->_entityUserCol && SRA_Database::isValid($db =& SRA_Controller::getAppDb()) && !SRA_Error::isError($tmp =& $dao->findBySqlConstraints(array($this->_entityUserCol => $db->convertText($_SERVER['PHP_AUTH_USER'])))) && count($tmp) == 1) {
          $userVariable =& $tmp[0];
          $this->_userEntity =& $tmp[0];
        }
        else if (!$this->_entityUserCol && !SRA_Error::isError($tmp =& $dao->findByPk($_SERVER['PHP_AUTH_USER'])) && is_object($tmp)) {
          $userVariable =& $tmp;
          $this->_userEntity =& $tmp;
        }
      }
    }
    return $userVariable;
  }
  // }}}
  
	
	// {{{ _killSession
	/**
	 * kills any data in the current session if the SRA_Authenticator uses sessions
   * @access private
	 * @return string
	 */
	function _killSession() {
		if ($this->_useSessions) {
			$this->_removeFromCache();
			session_start();
			$_SESSION = array();
			if (isset($_COOKIE[session_name()])) {
			  setcookie(session_name(), '', time()-42000, '/');
        if ($this->_loginTpl) {
          setcookie('sra-auth-completed', '', time()-42000, '/');
        }
			}
			session_destroy();
		}
	}
	// }}}
	
	
	// {{{ _logoutFileExists
	/**
	 * returns TRUE if a logout file exists for this authenticator and user
   * @access private
	 * @return boolean
	 */
	function _logoutFileExists() {
		return $this->_user && file_exists($this->_getLogoutFile());
	}
	// }}}
	
	
	// {{{ _matchAttributes
	/**
	 * matches SRA_Authenticator instance attributes if applicable and sets the 
	 * corresponding status code. alternatively, if a "match-fail-tpl" has been 
	 * defined, it will be displayed immediately and the process exited
   * @access private
	 * @return void
	 */
	function _matchAttributes($ignoreMatchFail = FALSE) {
		// first, convert scalar attributes to arrays where necessary
		if ($attrs = $this->_params->getParams('aattrs')) {
			$keys = array_keys($attrs);
			foreach ($keys as $key) {
				$akey = $key;
				if ($attrs[$key]) {
					$akey = $attrs[$key];
				}
				if (isset($this->attrs[$akey]) && !is_array($this->attrs[$akey])) {
					$this->attrs[$akey] = explode(' ', $this->attrs[$akey]);
				}
			}
		}
		if ($this->status == SRA_AUTHENTICATOR_STATUS_SUCCESS && (is_array($this->_matchAllAttrs) || is_array($this->_matchOneAttrs))) {
			$attrs = $this->attrs[$akey];
			if (!is_array($attrs)) {
				$attrs = array($attrs);
			}
			$keys = array_keys($this->_matchOneAttrs);
      if (isset($this->_userEntity) && is_object($this->_userEntity) && method_exists($this->_userEntity, 'getAttribute')) {
        // get additional _userEntity attributes
        foreach ($keys as $key) {
          if (!isset($attrs[$key])) {
            $attrs[$key] = $this->_userEntity->getAttribute($key);
          }
        }
      }
      $akeys = array_keys($attrs);
			$validatedOneAttr = FALSE;
			$validatedAllAttr = TRUE;
			$unmatchedAttr = FALSE;
			foreach ($keys as $key) {
				$match = FALSE;
				foreach ($akeys as $akey) {
					if ((trim(strtolower($this->_matchOneAttrs[$key])) == trim(strtolower($attrs[$akey])) || 
							strstr(trim(strtolower($attrs[$akey])), trim(strtolower($this->_matchOneAttrs[$key]))) || 
							ereg("^" . strtolower($this->_matchOneAttrs[$key]) . "+$", strtolower($attrs[$akey])))) {
						$match = TRUE;
						if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match One: Found match $key/" . $this->_matchOneAttrs[$key], SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
						break;
					}
					else {
						if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match One: NO match $key/" . $this->_matchOneAttrs[$key], SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
						$this->unmatchedAttr = $key;
					}
				}
				if ($match) {
					$validatedOneAttr = TRUE;
					break;
				}
			}
			$validatedOneAttr = count($this->_matchOneAttrs) ? $validatedOneAttr : TRUE;
      
			$keys = array_keys($this->_matchAllAttrs);
      if (isset($this->_userEntity) && is_object($this->_userEntity) && method_exists($this->_userEntity, 'getAttribute')) {
        // get additional _userEntity attributes
        foreach ($keys as $key) {
          if (!isset($attrs[$key])) {
            $attrs[$key] = $this->_userEntity->getAttribute($key);
          }
        }
      }
      $akeys = array_keys($attrs);
			foreach ($keys as $key) {
				$match = FALSE;
				foreach ($akeys as $akey) {
					if ((trim(strtolower($this->_matchAllAttrs[$key])) == trim(strtolower($attrs[$akey])) || 
							strstr(trim(strtolower($attrs[$akey])), trim(strtolower($this->_matchAllAttrs[$key]))) || 
							ereg("^" . strtolower($this->_matchAllAttrs[$key]) . "+$", strtolower($attrs[$akey])))) {
						$match = TRUE;
						if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match All: Found match $key/" . $this->_matchAllAttrs[$key], SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
						break;
					}
					else {
						if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match All: NO match $key/" . $this->_matchAllAttrs[$key], SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
						$this->unmatchedAttr = $key;
					}
				}
				if (!$match) {
					$validatedAllAttr = FALSE;
					break;
				}
			}
			$validatedAllAttr = count($this->_matchAllAttrs) ? $validatedAllAttr : TRUE;
			
			if ($validatedAllAttr && $validatedOneAttr) {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match succeeded", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$this->status = SRA_AUTHENTICATOR_STATUS_SUCCESS;
				$this->unmatchedAttr = FALSE;
			}
			else {
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Match failed", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$this->status = SRA_AUTHENTICATOR_STATUS_INVALID_ATTRS;
				// display match failed template
				if ($this->_matchFailTpl && !$ignoreMatchFail) {
					if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_matchAttributes: Displaying match-fail-tpl $this->_matchFailTpl", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
					$tpl =& SRA_Controller::getAppTemplate();
					$tpl->display($this->_matchFailTpl);
					exit;
				}
			}
		}
	}
	// }}}
	
	
	// {{{ _removeFromCache
	/**
	 * removes an authenticator cache file if it exists
   * @access private
	 * @return void
	 */
	function _removeFromCache() {
		if ($fileName = $this->_getCacheFileName()) {
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_removeFromCache: Removing $fileName from cache", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			unlink($fileName);
		}
	}
	// }}}
	
	
	// {{{ _updateLoginAttempts
	/**
	 * updates the failed login attempts count if applicable. sets _exceededMaxLoginAttemptCount 
	 * to true if applicable
   * @access private
	 * @return void
	 */
	function _updateLoginAttempts() {
		if ($this->status != SRA_AUTHENTICATOR_STATUS_ERROR && $this->_maxAttempts > 0) {
			// tracking based on IP
			if ($this->_maxAttemptIp) {
				if ($this->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
					$loginCount = $this->_getLoginAttemptCount() + 1;
				}
				else {
					$loginCount = 0;
				}
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_updateLoginAttempts: Setting login attempt count $loginCount for IP: " . $_SERVER['REMOTE_ADDR'], SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$file = SRA_Controller::getAppTmpDir() . '/' . SRA_AUTHENTICATOR_FILE_PREFIX . $this->_id . $_SERVER['REMOTE_ADDR'];
				$fp = fopen($file, 'w');
				fwrite($fp, $loginCount);
				fclose($fp);
			}
			// tracking based on session
			else {
				if (!session_id()) {
					session_start();
				}
				if ($this->status != SRA_AUTHENTICATOR_STATUS_SUCCESS) {
					$loginCount = $this->_getLoginAttemptCount() + 1;
				}
				else {
					$loginCount = 0;
				}
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_updateLoginAttempts: Status: $this->status Setting login attempt count $loginCount for session " . session_id(), SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				$this->_attemptCount = $loginCount;
				$this->_writeToCache();
			}
			
			// max login attempts exceeded
			if ($this->status != SRA_AUTHENTICATOR_STATUS_SUCCESS && $loginCount > $this->_maxAttempts) {
				$this->_exceededMaxLoginAttemptCount = TRUE;
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_updateLoginAttempts: $loginCount exceeded max allowed", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
				if ($this->_maxAttemptTpl) {
					$tpl =& SRA_Controller::getAppTemplate();
					if ($this->_tplVar) {
						$tpl->assignByRef($this->_tplVar, $this);
					}
					$tpl->display($this->_maxAttemptTpl);
					exit;
				}
			}
			else {
				$this->_exceededMaxLoginAttemptCount = FALSE;
				if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_updateLoginAttempts: $loginCount is OK", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			}
		}
	}
	// }}}
	
	
	// {{{ _writeToCache
	/**
	 * writes the current SRA_Authenticator instance to cache based on the user's 
	 * session_id
	 * @param boolean $lookup whether or not to attempt to lookup the authenticator 
	 * in cache and return it if it exists (it will only exist if previously 
	 * written)
   * @access private
	 * @return void
	 */
	function & _writeToCache($lookup = FALSE) {
		static $written = array();
		if ($fileName = $this->_getCacheFileName()) {
			// lookup
			if ($lookup && isset($written[$fileName])) {
				return $written[$fileName];
			}
			else if ($lookup) {
				return FALSE;
			}
			
			// don't store password in cache file
			$pswd = $this->_pswd;
			$this->_pswd = FALSE;
			$attrs = FALSE;
			SRA_File::write($fileName, serialize($this));
			$this->_pswd = $pswd;
			$written[$fileName] =& $this;
			$this->_written = TRUE;
			
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_writeToCache: Wrote to cache $fileName", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			
			// remove old cache files
			$prefix = $this->_getCacheFilePrefix();
			$files = SRA_File::getFileList(dirname($prefix), '/^' . basename($prefix) . '.*$/');
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_writeToCache: Evaluating cache files: ", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug($files, SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
			foreach ($files as $file) {
				if (!file_exists($sfile = session_save_path() . '/sess_' . str_replace(basename($prefix), '', basename($file)))) {
					if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("$this->_id: SRA_Authenticator::_writeToCache: Removing old cache file: $file", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
					unlink($file);
				}
			}
		}
	}
	// }}}
  
}
// }}}
?>
