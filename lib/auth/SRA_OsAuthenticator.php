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

// }}}

// {{{ SRA_OsAuthenticator
/**
 * Authenticates a user using expect (http://expect.nist.gov) and the linux "su" 
 * command. in order to use this authentication method, expect MUST be installed
 * (yum install expect expectk). Alternatively, if the PHP script requiring 
 * authentication is running from the command line, the user invoking that 
 * script will be assumed and no authentication will be required. when using 
 * sierra/bin/sra-console.php, an alternate user may be specified using the 
 * PASSWD_FILE or USERNAME/PASSWORD arguments
 * This SRA_Authenticator utilizes the following parameters:
 *   exclude  => (optional) space separated list of users that should not be 
 *               authenticated
 *   groups   => (optional) space separated list of group names one of which a 
 *               user must belong to in order to be authenticated
 *   profile  => (optional) user directory relative, or absolute path to a 
 *               properties file containing user attributes (key/value pairs) 
 *               each of these values will be set in the 
 *               SRA_Authenticator::attrs instance variable. this path may 
 *               contain the key ${user} which will be substituted with the 
 *               user name
 *   transpose =>(optional) identifies user names that should be transposed. 
 *               the format for this parameter is: [authenticator user]=[os user]
 *               multiple transpose usernames may be specified each separated 
 *               by a space. direct authentication for transposed users is 
 *               automatically disallowed
 * in addition to the authenticator 'user' key that is automatically set by 
 * SRA_Authenticator and the keys set through the 'profile' parameter above, 
 * this authenticator will also automatically set the following keys: 
 *   uid:      : the user's os uid
 *   gid:      : the user's primary group gid
 *   groups    : array of groups that the user is a member of
 *   home      : absolute path to the user's home directory
 *   transposed: if the username was transposed, this value will be the 
 *               transposed username ('user' will be the non-transposed user)
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_OsAuthenticator extends SRA_Authenticator {
  // {{{ Attributes
  // public attributes
  
  
  // private attributes
  
  
  // }}}
  
  // {{{ Operations
  // constructor(s)
  // {{{ SRA_OsAuthenticator
  /**
   * Constructor
   * @access  public
   */
  function SRA_OsAuthenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
                         $logoutFwdUri, $logoutGetVar, $logoutPostVar, $logoutTpl, $matchAllAttrs, $matchOneAttrs, 
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
  // }}}
  
  
  // public operations
  
  // {{{ authenticatedFromCli
  /**
   * returns TRUE if the PHP script is running from the command line, this 
   * authenticator was invoked, and the command line user was used for the 
   * authentication instead of prompting the user for login credentials
   * @access public static
   * @return boolean
   */
  function authenticatedFromCli() {
    global $_sraOsAuthenticatorFromCli;
    return $_sraOsAuthenticatorFromCli ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ promptConsole
  /**
   * when running from the console, no login credentials are required
   * @access public
   * @return boolean
   */
  function promptConsole() {
    if (!$_SERVER['PHP_AUTH_USER'] && !$_SERVER['PHP_AUTH_PW']) {
      $_SERVER['PHP_AUTH_USER'] = SRA_Util::getCurrentUser();
      return TRUE;
    }
    else {
      return parent::promptConsole();
    }
  }
  // }}}
  
  
  // private operations
  // {{{ _authenticate
  /**
   * Authenticates user against operating system based on the user/password and 
   * parameters specified
   * @param string $user the username to authenticate
   * @param string $pswd the password to authenticate
   * @param array $params used for invoking this method statically
   * @return void or ERROR
   */
  function _authenticate($user, $pswd, $params=NULL) {
    if (!SRA_Controller::runningFromCli() && (!trim($user) || !trim($pswd))) {
      return;
    }
    $isStatic = $params ? TRUE : FALSE;
    $params = $isStatic ? $params : $this->_params;
    // construct transpose hash
    $transpose = array();
    if (SRA_Params::isValid($params) && $params->getParam('transpose')) {
      foreach(explode(' ', $params->getParam('transpose')) as $pair) {
        $tmp = explode('=', $pair);
        $transpose[$tmp[0]] = $tmp[1];
      }
    }
    
    // don't allow login from exclude and transpose users
    if ((in_array($user, $transpose) && !SRA_Controller::runningFromCli()) || (SRA_Params::isValid($params) && $params->getParam('exclude') && !in_array($user, explode(' ', $params->getParam('exclude'))))) {
      $this->status = SRA_AUTHENTICATOR_STATUS_INVALID_USER;
      return;
    }
    
    // transpose user
    if (isset($transpose[$user])) {
      $this->attrs['transposed'] = $transpose[$user];
      $user = $transpose[$user];
    }
    
    $tmpFile = uniqid();
    if (!$this->_entityType) {
      $cmds = array('cd ~;pwd', 'groups', "cd ~;touch $tmpFile", 'cd ~;ls -l -n', "cd ~;rm -f $tmpFile");
      $callbacks = array('groups' => 'explode(" ", ${results})');
      if (SRA_Params::isValid($params) && $params->getParam('profile')) {
        $profile = SRA_Util::substituteParams($params->getParam('profile'), array('user' => $user));
        $profile = 'cat ' . (SRA_Util::beginsWith($profile, '/') || SRA_Util::beginsWith($profile, '~') ? $profile : '~/' . $profile);
        $cmds[] = $profile;
        $callbacks[$profile] = 'SRA_Util::propertiesStringToHash';
      }
    }
    $login = SRA_Util::suCmds($user, $pswd, $this->_entityType ? NULL : $cmds, $this->_entityType ? NULL : $callbacks, SRA_Controller::runningFromCli());
    
    if ($login == 0) {
      $msg = 'SRA_OsAuthenticator::_authenticate: Failed - expect is not installed';
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    
    $status = $login === -1 ? SRA_AUTHENTICATOR_STATUS_INVALID_USER : ($login === -2 ? SRA_AUTHENTICATOR_STATUS_INVALID_PSWD : SRA_AUTHENTICATOR_STATUS_SUCCESS);
    
    if ($status == SRA_AUTHENTICATOR_STATUS_SUCCESS) {
      // user not member of required group
      if (!$this->_entityType && SRA_Params::isValid($params) && $params->getParam('groups') && !array_intersect(explode(' ', $params->getParam('groups')), $login['groups'])) {
        $status = SRA_AUTHENTICATOR_STATUS_INVALID_USER;
      }
      else if (!$this->_entityType) {
        $gid = NULL;
        $uid = NULL;
        foreach(explode("\n", $login['cd ~;ls -l -n']) as $tmp) {
          if (strpos($tmp, $tmpFile)) {
            $temp = explode(' ', $tmp);
            $started = FALSE;
            foreach($temp as $piece) {
              if ($started && !$uid) { $uid = $piece*1; }
              else if ($started && $uid) { 
                $gid = $piece*1;
                break;
              }
              else if (is_numeric($piece)) { $started = TRUE; }
            }
          }
        }
        
        $attrs = array('groups' => $login['groups'], 'home' => $login['cd ~;pwd'], 'uid' => $uid, 'gid' => $gid);
        if ($profile && is_array($login[$profile])) { $attrs = array_merge($login[$profile], $attrs); }
      }
    }
    
    if (SRA_Controller::runningFromCli() && $status == SRA_AUTHENTICATOR_STATUS_SUCCESS && $user == SRA_Util::getCurrentUser()) {
      global $_sraOsAuthenticatorFromCli;
      $_sraOsAuthenticatorFromCli = TRUE;
    }
    if ($isStatic) {
      return array('status' => $status, 'attrs' => $attrs);
    }
    else {
      $this->status = $status;
      if (is_array($attrs)) $this->attrs = array_merge($attrs, $this->attrs);
    }
  }
  // }}}

  
}
// }}}
?>
