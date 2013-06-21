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
require_once('ext/pop3/pop3.php');
require_once('util/SRA_EmailMessage.php');
// }}}

// {{{ Constants
/**
 * the default pop3 login tcp/ip port
 */
define('SRA_POP3_LOGIN_DEFAULT_PORT', 110);

/**
 * the default pop3 login tcp/ip port for tls connections
 */
define('SRA_POP3_LOGIN_DEFAULT_PORT_TLS', 995);
// }}}

// {{{ SRA_Pop3Login
/**
 * This class is used to manage interaction with a pop3 email account. This 
 * class and the SRA_EmailMessage class are basically wrapper classes for the 
 * mime_parser and pop3 classes distributed open source by Manuel Lemos and 
 * located in sierra/lib/ext/pop3. Use this class to log into a pop3 account, 
 * query, retrieve and delete messsages.
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_Pop3Login {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * whether or not the connection is currently open
	 * @type boolean
	 */
	var $_connected = FALSE;
  
  /**
	 * the Manuel Lemos pop3 instance for this login
	 * @type string
	 */
	var $_pop3;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Pop3Login
	/**
	 * the constructor initializes the pop3 login. after instantiating a new 
   * instance, you should verify that the login was successful using the 
   * static SRA_Pop3Login::isValid($object) method. If the login was not 
   * successful that method will return FALSE. You do not need to worry about 
   * logging out as that will be done automatically once the current php 
   * instance terminates. However, if you would like to explicitely log out, you 
   * may do so by invoking the "logout" method
   * @param string $host the pop3 host name or ip address (required)
   * @param string $user the pop3 account user name (required)
   * @param string $pswd the pop3 account password (required)
   * @param int $port tcp/ip port to connect on (default is 110/995 - tls)
   * @param boolean $tls whether or not to use a tls secure connection to the 
   * server
   * @param boolean $apop whether or not to use APOP authentication (must be 
   * supported by the mail server)
   * @param int $errorLevel the error level to use (determines logging)
   * @access  public
	 */
	function SRA_Pop3Login($host, $user, $pswd, $port=NULL, $tls=FALSE, $apop=FALSE, $errorLevel=SRA_ERROR_PROBLEM) {
    $port = $port ? $port : ($tls ? SRA_POP3_LOGIN_DEFAULT_PORT_TLS : SRA_POP3_LOGIN_DEFAULT_PORT);
    $this->_pop3 = new pop3_class();
    $this->_pop3->hostname = $host;
    $this->_pop3->port = $port;
    $this->_pop3->tls = $tls;
    if (strlen($this->_pop3->Open())) {
      $this->err =& SRA_Error::logError('SRA_Pop3Login - POP3 connection error: ' . $this->_pop3->error, __FILE__, __LINE__, $errorLevel);
    }
    else {
      if (strlen($this->_pop3->Login($user, $pswd, $apop ? 1 : 0))) {
        $this->err =& SRA_Error::logError('SRA_Pop3Login - POP3 login error: ' . $this->_pop3->error, __FILE__, __LINE__, $errorLevel);
      }
      else {
        $this->_pop3->ListMessages('');
        if (strlen($this->_pop3->error)) {
          $this->err =& SRA_Error::logError('SRA_Pop3Login - POP3 query error: ' . $this->_pop3->error, __FILE__, __LINE__, $errorLevel);
        }
      }
      $this->_connected = TRUE;
      SRA_Controller::registerShutdownMethod($this, 'logout');
    }
	}
	// }}}
  
	// {{{ deleteMessage
	/**
	 * deletes a message from the pop3 server. returns TRUE on success, FALSE 
   * otherwise
   * @param string $id the message identifier as retrieved from getMessageList
	 * @access	public
	 * @return	boolean
	 */
	function deleteMessage($id) {
		if (SRA_Pop3Login::isValid($this) && $this->_connected) {
      if (strlen($this->_pop3->DeleteMessage($id))) {
        SRA_Error::logError('SRA_Pop3Login::deleteMessage - POP3 delete message ' . $id . ' error: ' . $this->_pop3->error, __FILE__, __LINE__);
      }
      else {
        return TRUE;
      }
    }
    return FALSE;
	}
	// }}}
  
	// {{{ getAllMessages
	/**
	 * returns all of the messages on the server as an array of SRA_EmailMessage 
   * instances. if this object is not currently in a valid connected state to 
   * query the server, or if an error occurs, it will return NULL (errors will 
   * be logged). if there are not messages on the server, it will return an 
   * empty array. the returned array will be indexed by message id
   * @param boolean $delete whether or not to delete the messages after 
   * retrieving them. default is FALSE
	 * @access	public
	 * @return	SRA_EmailMessage[]
	 */
	function getAllMessages($delete) {
		if (SRA_Pop3Login::isValid($this) && $this->_connected) {
      $list = $this->getMessageList();
      if (isset($list)) {
        $messages = array();
        foreach($list as $id => $size) {
          if (SRA_EmailMessage::isValid($message =& $this->getMessage($id, $delete))) {
            $messages[$id] =& $message;
          }
        }
        return $messages;
      }
    }
    return NULL;
	}
	// }}}
  
	// {{{ getMessage
	/**
	 * retrieves and returns a specific message from the pop3 server. if this 
   * object is not currently in a valid connected state to query the server, or 
   * if an error occurs, it will return NULL (errors will be logged). otherwise, 
   * it returns the SRA_EmailMessage object representation of the message
   * @param string $id the message identifier as retrieved from getMessageList
   * @param boolean $delete whether or not to delete the message after 
   * retrieving it. default is FALSE
	 * @access	public
	 * @return	SRA_EmailMessage
	 */
	function & getMessage($id, $delete=FALSE) {
		if (SRA_Pop3Login::isValid($this) && $this->_connected) {
      if (strlen($this->_pop3->OpenMessage($id, -1))) {
        SRA_Error::logError('SRA_Pop3Login::getMessage - POP3 open message ' . $id . ' error: ' . $this->_pop3->error, __FILE__, __LINE__);
      }
      else {
        $msg = array();
        while(!$eof) {
          $this->_pop3->GetMessage(1024, $msg[], $eof);
        }
        $msg =& implode('', $msg);
        if (SRA_EmailMessage::isValid($message =& SRA_EmailMessage::convert($msg))) {
          if ($delete) { $this->deleteMessage($id); }
          return $message;
        }
      }
    }
    $nl = NULL;
    return $nl;
	}
	// }}}
  
	// {{{ getMessageList
	/**
	 * returns the current messages on the pop3 server as a hash where the key is 
   * the message id and the value is the message size in bytes. returns an empty 
   * array if there are no messages on the server or NULL if this object is not 
   * currently in a valid connected state to query the server
	 * @access	public
	 * @return	array
	 */
	function getMessageList() {
		if (SRA_Pop3Login::isValid($this) && $this->_connected) {
      return $this->_pop3->ListMessages('');
    }
    else {
      return NULL;
    }
	}
	// }}}
  
	// {{{ logout
	/**
	 * closes an open connection with the pop3 server. returns TRUE on success, 
   * FALSE otherwise
	 * @access	public
	 * @return	boolean
	 */
	function logout() {
		if (SRA_Pop3Login::isValid($this) && $this->_connected) {
      $this->_connected = FALSE;
      return strlen($this->_pop3->Close()) == 0;
    }
    else {
      return FALSE;
    }
	}
	// }}}
	
  
  // public operations
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_Pop3Login object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_pop3login');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
