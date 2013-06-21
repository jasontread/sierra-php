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
include_once('SRA_Authenticator.php');
// }}}

// {{{ Constants
/**
 * error code identifying that a user is not valid
 * @type int
 */
define('SRA_LDAP_AUTHENTICATOR_INVALID_USER', 32);

/**
 * error code identifying that a password is not valid
 * @type int
 */
define('SRA_LDAP_AUTHENTICATOR_INVALID_PSWD', 49);

/**
 * default non-secure ldap port
 * @type int
 */
define('SRA_LDAP_AUTHENTICATOR_DEFAULT_PORT', 389);

/**
 * default non-secure ldap port
 * @type int
 */
define('SRA_LDAP_AUTHENTICATOR_DEFAULT_SECURE_PORT', 636);

/**
 * base scope identifier
 * @type string
 */
define('SRA_LDAP_AUTHENTICATOR_SCOPE_BASE', 'base');

/**
 * one-level scope identifier
 * @type string
 */
define('SRA_LDAP_AUTHENTICATOR_SCOPE_ONE', 'one');

/**
 * sub-tree scope identifier
 * @type string
 */
define('SRA_LDAP_AUTHENTICATOR_SCOPE_SUB', 'sub');

/**
 * default ldap scope
 * @type string
 */
define('SRA_LDAP_AUTHENTICATOR_DEFAULT_SCOPE', SRA_LDAP_AUTHENTICATOR_SCOPE_SUB);

/**
 * user id ldap code
 * @type string
 */
define('SRA_LDAP_AUTHENTICATOR_UID', 'uid');
// }}}

// {{{ SRA_LdapAuthenticator
/**
 * Authenticates a user in an LDAP server. This SRA_Authenticator utilizes the 
 * following parameters:
 *	'server'=> the LDAP server dns name or IP address - (i.e. 
 *						 <param id="server" value="localhost" />) - REQUIRED
 *	'search'=> the LDAP server search base (i.e. 
 *						 <param id="search" value="ou=people,o=company" />) - REQUIRED
 *	'port' 	=> the LDAP server port (default is 389 for non-secure 
 *						 [ldap://] and 636 for secure [ldaps://]) (i.e. 
 *						 <param id="port" value="389" />)
 *	'secure'=> whether or not the ldap server is secure (ldaps://)
 *						 default value is 0 (FALSE) (i.e. 
 *						 <param id="secure" value="1" />)
 * 	'scope' => (base|one|sub), the LDAP search scope. determines 
 *						 which PHP method is used to retrieve user 
 *						 attributes. base=ldap_read, one=ldap_list, 
 *						 sub=ldap_search (default) (i.e. 
 *						 <param id="scope" value="sub" />)
 *	'attrs' => 0..* key/value pairs (of "param" sub-element type 
 *						 'attrs') that should be retrieved for the user from 
 *						 the LDAP server. the default attr key/values (if not 
 *						 specified otherwise) are the following (for 
 *						 additional information on standard LDAP attributes, 
 *						 see http://www.faqs.org/rfcs/rfc2256.html): 
 *							 
 *							cn/cn : common name - typically the person's full name
 *							sn/sn : surname - typically person's last name
 *							givenName/givenName : typically the person's first name
 *							mail/mail : typically the person's email address
 *							postalAddress/postalAddress : person's postal address
 *								telephoneNumber/telephoneNumber : person's telephone number
 *						 
 *						 the attributes provided by the LDAP server will be 
 *						 accessible in the user's lib/core/SRA_Authenticator (see api)
 *						 instance provided as the "tpl-var" or "global-var" 
 *						 attribute specified, where the key will be the 
 *						 "value" of the "param" if specified, the "key" 
 *						 otherwise. these keys/value pairs can also be used 
 *						 to further restrict access in the "restrict-access" 
 *						 "match-property" element. (i.e. 
 *						 <param id="cn" type="attrs" value="fullName" />
 *						 <param id="sn" type="attrs" value="lastName" />)
 *	'options'=>0..* key/value pairs (of "param" sub-element type 'options') that 
 *						 should be set for the ldap server using 
 *						 "ldap_set_option($conn, key, value)"
 * 
 * If an LDAP error occurs that is not pertaining to an invalid user or password
 * the error code will be stored to the errMsg attribute
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_LdapAuthenticator extends SRA_Authenticator {
  // {{{ Attributes
  // public attributes
	
  
  // private attributes
	
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_LdapAuthenticator
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_LdapAuthenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
                         $logoutFwdUri, $logoutGetVar, $logoutPostVar, $logoutTpl, $matchAllAttrs,$matchOneAttrs, 
												 $matchFailTpl, $maxAttempts, $maxAttemptTpl, $maxAttemptIp, 
												 $maxAttemptTimeout, & $params, $resource, $sysErrTpl, 
												 $tplVar, $useSessions) {
		parent::SRA_Authenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
                         $logoutFwdUri, $logoutGetVar, $logoutPostVar, $logoutTpl, $matchAllAttrs,$matchOneAttrs, 
												 $matchFailTpl, $maxAttempts, $maxAttemptTpl, $maxAttemptIp, 
												 $maxAttemptTimeout, $params, $resource, $sysErrTpl, 
												 $tplVar, $useSessions);
	}
	// }}}
	
  
  // public operations
	
  
  // private operations
	// {{{ _authenticate
	/**
	 * Authenticates user against LDAP server based on the user/password and 
	 * parameters specified
   * @param string $user the username to authenticate
   * @param string $pswd the password to authenticate
	 * @return void or ERROR
	 */
	function _authenticate($user, $pswd) {
		// validate mandatory parameters
		if (!$this->_params || !$this->_params->getParam('server') || !$this->_params->getParam('search')) {
			$msg = "SRA_LdapAuthenticator::_authenticate: Failed - Missing mandatory parameters 'server' or 'search'";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		// determine server variables
		$server = $this->_params->getParam('server');
		$search = $this->_params->getParam('search');
		$port = $this->_params->getParam('port');
		if (!$port && !$secure) {
			$port = SRA_LDAP_AUTHENTICATOR_DEFAULT_PORT;
		}
		else if (!$port && $secure) {
			$port = SRA_LDAP_AUTHENTICATOR_DEFAULT_SECURE_PORT;
		}
		$secure = $this->_params->getParam('secure') == '1';
		$scope = $this->_params->getParam('scope');
		if (!$scope) {
			$scope = SRA_LDAP_AUTHENTICATOR_DEFAULT_SCOPE;
		}
		$attrs = $this->_params->getParams(array('attrs', 'aattrs'));
		if (!$attrs) {
			$attrs = array('cn' => 'cn', 'sn' => 'sn', 'givenName' => 'givenName', 'mail' => 'mail', 'postalAddress' => 'postalAddress');
		}
		$options = $this->_params->getParams('options');
		
		if ($scope != SRA_LDAP_AUTHENTICATOR_SCOPE_BASE && $scope != SRA_LDAP_AUTHENTICATOR_SCOPE_ONE && $scope != SRA_LDAP_AUTHENTICATOR_SCOPE_SUB) {
			$msg = "SRA_LdapAuthenticator::_authenticate: Failed - Invalid scope ${scope}";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		
		// username and password must be provided
		if (!$user || !$pswd) {
			return;
		}
		
		if ($ds=ldap_connect($server, $port)) {
			if ($options) {
				$okeys = array_keys($options);
				foreach ($okeys as $okey) {
					ldap_set_option($ds, $okey, $options[$okey]);
				}
			}
			$keys = array_keys($attrs);
			$dn = SRA_LDAP_AUTHENTICATOR_UID . '=' . $user . ',' . $search;
			if ($ldapResult = @ldap_bind($ds, $dn, $pswd)) {
				if ($scope == SRA_LDAP_AUTHENTICATOR_SCOPE_BASE) {
					$sr = ldap_read($ds, $search, SRA_LDAP_AUTHENTICATOR_UID . '=' . $user, $keys);
				}
				else if ($scope == SRA_LDAP_AUTHENTICATOR_SCOPE_ONE) {
					$sr = ldap_list($ds, $search, SRA_LDAP_AUTHENTICATOR_UID . '=' . $user, $keys);
				}
				else if ($scope == SRA_LDAP_AUTHENTICATOR_SCOPE_SUB) {
					$sr = ldap_search($ds, $search, SRA_LDAP_AUTHENTICATOR_UID . '=' . $user, $keys);
				}
				if (!$err && !$sr) {
					$err = "LDAP Server error: " . ldap_error($ds);
				}
				else if (!$err) {
					$info = ldap_get_entries($ds, $sr);
					foreach ($keys as $key) {
						$akey = $key;
						if ($attrs[$key]) {
							$akey = $attrs[$key];
						}
						if (isset($info[0][$key][0])) {
							$this->attrs[$akey] = $info[0][$key][0];
						}
						else if (isset($info[0][strtolower($key)][0])) {
							$this->attrs[$akey] = $info[0][strtolower($key)][0];
						}
						else {
							$this->attrs[$akey] = FALSE;
						}
					}
					$this->status = SRA_AUTHENTICATOR_STATUS_SUCCESS;
				}
			}
			else {
				if (ldap_errno($ds) == SRA_LDAP_AUTHENTICATOR_INVALID_USER) {
					$this->status = SRA_AUTHENTICATOR_STATUS_INVALID_USER;
				}
				else if (ldap_errno($ds) == SRA_LDAP_AUTHENTICATOR_INVALID_PSWD) {
					$this->status = SRA_AUTHENTICATOR_STATUS_INVALID_PSWD;
				}
				else {
					$this->errMsg = ldap_error($ds);
				}
			}
			
			ldap_close($ds);
			if ($err) {
				$msg = "SRA_LdapAuthenticator::_authenticate: Failed - ${err}";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
		}
		else {
			$msg = "SRA_LdapAuthenticator::_authenticate: Failed - Unable to connect to server ${server} and port ${port}";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
	}
	// }}}

  
}
// }}}
?>
