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
include_once('util/SRA_Pop3Login.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_Pop3Authenticator
/**
 * Authenticates a user using a POP3 email server. This SRA_Authenticator 
 * utilizes the following parameters:
 *	'host'=> the POP3 server dns name or IP address - (i.e. 
 *    <param id="server" value="mail.mydomain.com" />) - REQUIRED
 *	'port'=> (1|0) - tcp/ip port to connect on (default is 110 or 995 for tls)
 *	'tls'=> (1|0) - whether or not to use a tls secure connection to the server
 *    (default is 0)
 *	'apop'=> (1|0) - whether or not to use APOP authentication (must be 
 *     supported by the mail server - default is 0)
 *  'append' => optional text to append to the user (if it is not already there)
 *     this may be useful for mail servers that require the entire email address
 *     as the user name thereby allowing the user to just enter the email name
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_Pop3Authenticator extends SRA_Authenticator {
  // {{{ Attributes
  // public attributes
	
  
  // private attributes
	
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Pop3Authenticator
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_Pop3Authenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
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
		if (!$this->_params || !$this->_params->getParam('host')) {
			$msg = "SRA_Pop3Authenticator::_authenticate: Failed - Missing mandatory parameters 'host'";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		// determine server variables
		$host = $this->_params->getParam('host');
    $port = $this->_params->getParam('port');
    $tls = $this->_params->getParam('tls');
    $apop = $this->_params->getParam('apop');
    if (($append = $this->_params->getParam('append')) && !SRA_Util::endsWith($user, $append, FALSE)) {
      $user .= $append;
    }
    
    if (SRA_Pop3Login::isValid($popLogin = new SRA_Pop3Login($host, $user, $pswd, $port, $tls, $apop, SRA_ERROR_OPERATIONAL))) {
			$popLogin->logout();
      $this->status = SRA_AUTHENTICATOR_STATUS_SUCCESS;
    }
    else if (!preg_match('/login error/', $popLogin->err->getErrorMessage())) {
      return $popLogin->err;
    }
	}
	// }}}

  
}
// }}}
?>
