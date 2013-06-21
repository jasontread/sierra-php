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

// {{{ SRA_DbAuthenticator
/**
 * Authenticates a user in an DB server. This SRA_Authenticator utilizes the 
 * following parameters:
 *  'db'      =>the database server identifier (used to access the 
 *              database via SRA_Controller::getAppDb(id). If not 
 *              specified the default app DB will be used
 *              (i.e. <param id="db" value="authDbId" />)
 *  'table'   =>the name of the database table where the user 
 *              records are stored (i.e. <param id="table" value="USER" />) - REQUIRED
 *  'user-col'=>the name of the column containing the user id 
 *              (i.e. <param id="user-col" value="USER_NAME" />) - REQUIRED
 *  'pswd-col'=>the name of the column containing the password
 *              (i.e. <param id="pswd-col" value="PASSSWORD" />). if not 
 *              provided, only the user will be validated
 *  'pswd-cond-col'=> if the password should only be conditionally verified, 
 *              this parameter may be used to specify the column containing the 
 *              value used to determine when the password should NOT be 
 *              evaluated 
 *  'pswd-cond-val'=> if the password should only be conditionally verified, 
 *              this parameter may be used to specify the value that 
 *              'pswd-cond-col' should be equal to. if the password should NOT 
 *              be evaluated. if 'pswd-cond-col' is specified, and this 
 *              parameter is not, the value that will be used in the evaluation 
 *              is the TRUE value used by the database. set this value to the 
 *              string "TRUE", "FALSE" or "NULL" for those corresponding PHP 
 *              constants to be used
 *  'pswd-fun'=>an optional database password encryption function 
 *              that should be applied to match passwords. when 
 *              specified, the validation query will be altered to 
 *              utilize that function... WHERE PASSWORD=password('pass')
 *              (i.e. <param id='pswd-fun" value="password" />)
 *  'attrs' => 0..* column/value pairs (of "param" sub-element type 
 *             'attrs') that should be retrieved for the user from 
 *             the user table. 
 *             
 *             the attributes specified will be accessible in the 
 *             user's lib/core/SRA_Authenticator (see api)
 *             instance provided as the "tpl-var" or "global-var" 
 *             attribute specified, where the key will be the 
 *             "value" of the "param" if specified, the "key" 
 *             otherwise. these keys/value pairs can also be used 
 *             to further restrict access in the "restrict-access" 
 *             "match-property" element. (i.e. 
 *             <param id="DEPARTMENT" type="attrs" value="dept" />
 *             <param id="SERIAL_NUMBER" type="attrs" value="serialNum" />)
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.auth
 */
class SRA_DbAuthenticator extends SRA_Authenticator {
  // {{{ Attributes
  // public attributes
  
  
  // private attributes
  
  
  // }}}
  
  // {{{ Operations
  // constructor(s)
  // {{{ SRA_DbAuthenticator
  /**
   * Constructor
   * @access  public
   */
  function SRA_DbAuthenticator($id, $accessLog, $cancelTpl, $entityType, $entityUserCol, $failureLog, $globalVar, $loginTpl, 
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
  
  
  // private operations
  // {{{ _authenticate
  /**
   * Authenticates user against DB server based on the user/password and 
   * parameters specified
   * @param string $user the username to authenticate
   * @param string $pswd the password to authenticate
   * @return void or ERROR
   */
  function _authenticate($user, $pswd) {
    static $authQueries = array();
    
    // validate mandatory parameters
    if (!$this->_params || !$this->_params->getParam('table') || !$this->_params->getParam('user-col')) {
      $msg = "SRA_DbAuthenticator::_authenticate: Failed - Missing mandatory parameters 'table' or 'user-col'";
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    if (!SRA_Database::isValid($db =& SRA_Controller::getAppDb($this->_params->getParam('db')))) {
      $msg = "SRA_DbAuthenticator::_authenticate: Failed - Unable to retrieve database instance for " . $this->_params->getParam('db');
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    $table = $this->_params->getParam('table');
    $userCol = $this->_params->getParam('user-col');
    $pswdCol = $this->_params->getParam('pswd-col');
    $pswdCondCol = $this->_params->getParam('pswd-cond-col');
    $pswdCondVal = $this->_params->getParam('pswd-cond-val', TRUE);
    $pswdCondVal = $pswdCondVal == 'TRUE' ? TRUE : ($pswdCondVal == 'FALSE' ? FALSE : ($pswdCondVal == 'NULL' ? NULL : $pswdCondVal));
    $pswdFun = $this->_params->getParam('pswd-fun');
    $attrs = $this->_params->getParams(array('attrs', 'aattrs'));
    $dbAttrs = array(SRA_DATA_TYPE_STRING);
    
    $query = "SELECT ${userCol}";
    if (is_array($attrs)) {
      $keys = array_keys($attrs);
      foreach ($keys as $key) {
        $query .= ", ${key}";
        $dbAttrs[] = SRA_DATA_TYPE_STRING;
      }
    }
    $query .= " FROM ${table} WHERE ${userCol} = " . $db->convertText($user);
    if ($pswdCol && $pswd) {
      if ($pswdFun) {
        $query .= " AND (${pswdCol} = ${pswdFun}(" . $db->convertText($pswd) . ')';
      }
      else {
        $query .= " AND (${pswdCol} = " . $db->convertText($pswd);
      }
      if ($pswdCondCol) {
        $query .= " OR ${pswdCondCol} " . ($pswdCondVal === NULL ? ' IS NULL' : (' = ' . ($pswdCondVal === TRUE || $pswdCondVal === FALSE ? $db->convertBoolean($pswdCondVal) : $db->convertText($pswdCondVal))));
      }
      $query .= ')';
    }
    
    if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("SRA_DbAuthenticator::_authenticate: Executing authenticate query '${query}'", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
    
    if (!isset($authQueries[$query])) {
      if (!SRA_Error::isError($results =& $db->fetch($query, $dbAttrs))) {
        if ($results->count()) {
          if ($keys) {
            $row =& $results->next();
            $count = 1;
            foreach ($keys as $key) {
              $akey = $key;
              if ($attrs[$key]) {
                $akey = $attrs[$key];
              }
              $this->attrs[$akey] = $row[$count++];
            }
          }
          if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("SRA_DbAuthenticator::_authenticate: Query and authentication was successful", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
          $this->status = SRA_AUTHENTICATOR_STATUS_SUCCESS;
        }
        else {
          // determine if invalid user or password
          $query = "SELECT ${userCol} FROM ${table} WHERE ${userCol} = " . $db->convertText($user);
          $results = $db->fetch($query, array(SRA_DATA_TYPE_STRING));
          if (!$results->count()) {
            $this->status = SRA_AUTHENTICATOR_STATUS_INVALID_USER;
          }
          else {
            $this->status = SRA_AUTHENTICATOR_STATUS_INVALID_PSWD;
          }
          if (SRA_AUTHENTICATOR_DEBUG) { SRA_Util::printDebug("SRA_DbAuthenticator::_authenticate: Query and authentication was NOT successful", SRA_AUTHENTICATOR_DEBUG, __FILE__, __LINE__, FALSE, FALSE, TRUE, SRA_AUTHENTICATOR_DEBUG_FILE); }
        }
      }
      else {
        $msg = "SRA_DbAuthenticator::_authenticate: Failed - Unable to execute query: ${query}";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      $authQueries[$query] =& $this;
    }
    else {
      $this->attrs =& $authQueries[$query]->attrs;
      $this->status = $authQueries[$query]->status;
    }
  }
  // }}}

  
}
// }}}
?>
