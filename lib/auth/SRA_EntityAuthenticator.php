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

// {{{ SRA_EntityAuthenticator
/**
 * Authenticates a user using an entity object. This SRA_Authenticator 
 * utilizes the following parameters:
 *  'entity'    => the name of the entity to use - REQUIRED
 *	'user-attr' => the name of the username attribute (if username is not the 
 *                 entity primary key
 *  'pswd-attr' => the name of the password attribute. default is 'password'
 *  'pswd-md5'  => (1|0) whether or not pswd-attr is md5 encrypted (default is 0)
 *  'finder'    => the name of the DAO finder method (find by user name). the 
 *                 default is findByPk
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_EntityAuthenticator extends SRA_Authenticator {
  // {{{ Attributes
  // public attributes
	
  
  // private attributes
	
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntityAuthenticator
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_EntityAuthenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
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
		
	  $entity = $this->_params && $this->_params->getParam('entity') ? $this->_params->getParam('entity') : NULL;
	  $userAttr = $this->_params && $this->_params->getParam('user-attr') ? $this->_params->getParam('user-attr') : NULL;
	  $pswdAttr = $this->_params && $this->_params->getParam('pswd-attr') ? $this->_params->getParam('pswd-attr') : 'password';
	  $pswdMD5 = $this->_params && $this->_params->getParam('pswd-md5') == '1' ? TRUE : FALSE;
	  $finder = $this->_params && $this->_params->getParam('finder') ? $this->_params->getParam('finder') : 'findByPk';
	  $pswd = $pswdMD5 ? md5($pswd) : $pswd;
	  
		// validate entity type
		if (!$entity || SRA_Error::isError($dao =& SRA_DaoFactory::getDao($entity))) {
			$msg = "SRA_EntityAuthenticator::_authenticate: Failed - invalid entity-type: " . $entity;
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		// validate finder method
	  if (!method_exists($dao, $finder)) {
			$msg = "SRA_EntityAuthenticator::_authenticate: Failed - invalid finder method: " . $finder;
			return SRA_Error::logError($msg, __FILE__, __LINE__);
	  }
	  // lookup entity
	  if (is_object($obj =& $dao->${finder}($user)) && method_exists($obj, 'getAttribute')) {
	    $this->status = $obj->getAttribute($pswdAttr) == $pswd ? SRA_AUTHENTICATOR_STATUS_SUCCESS : SRA_AUTHENTICATOR_STATUS_INVALID_PSWD;
	  }
	  else {
	    $this->status = SRA_AUTHENTICATOR_STATUS_INVALID_USER;
	  }
	  
	}
	// }}}

  
}
// }}}
?>
