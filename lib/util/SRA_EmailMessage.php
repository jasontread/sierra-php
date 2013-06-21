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
require_once('ext/pop3/mime_parser.php');
// }}}

// {{{ SRA_EmailMessage
/**
 * used to represent the data associated with an email message. this class 
 * consists only of public attributes representing this data
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_EmailMessage {
  // {{{ Attributes
  // public attributes
  /**
	 * any file attachments included with the message
	 * @type SRA_FileAttribute[]
	 */
	var $attachments = array();
  
  /**
	 * the cc email address
	 * @type string
	 */
	var $cc;
  
  /**
	 * the cc name (if provided in the message headers)
	 * @type string
	 */
	var $ccName;
  
  /**
	 * the from email address
	 * @type string
	 */
	var $from;
  
  /**
	 * the from name (if provided in the message headers)
	 * @type string
	 */
	var $fromName;
  
  /**
	 * the plain text message body. if only html formatted content was included 
   * in the message, this attribute will be the tag stripped version of that 
   * content
	 * @type string
	 */
	var $message;
  
  /**
	 * the html formatted message content. if only text formatted content was 
   * included in the message, this attribute will be that content with newlines 
   * replaced with <br /> tags
	 * @type string
	 */
	var $messageHtml;
  
  /**
	 * the message id
	 * @type string
	 */
	var $messageId;
  
  /**
	 * is this message low priority. if neither $priorityLow nor $priorityHigh are 
   * TRUE, the message is normal priority
	 * @type boolean
	 */
	var $priorityLow = FALSE;
  
  /**
	 * is this message high priority. if neither $priorityLow nor $priorityHigh 
   * are TRUE, the message is normal priority
	 * @type boolean
	 */
	var $priorityHigh = FALSE;
  
  /**
	 * the date this message was received by the destination server
	 * @type SRA_GregorianDate
	 */
	var $received;
  
  /**
	 * the email address return path. this will be an email address that may or 
   * may not be the same as $from
	 * @type string
	 */
	var $returnPath;
  
  /**
	 * the server path that this message took from origin to destination. each 
   * entry in this attribute will be the IP address of the server in that step 
   * of the path starting with the origin server and ending with the receiving 
   * server
	 * @type array
	 */
	var $serverPath = array();
  
  /**
	 * the message subject
	 * @type string
	 */
	var $subject;
  
  /**
	 * the to email address
	 * @type string
	 */
	var $to;
  
  /**
	 * the to name (if provided in the message headers)
	 * @type string
	 */
	var $toName;
	
  // }}}
	
  
  // public methods
	// {{{ SRA_EmailMessage
	/**
	 * object constructor
	 * @access	public
	 */
	function SRA_EmailMessage() {
		
	}
	// }}}
  
  
	// {{{ truncate
	/**
	 * use this method to truncate this message
   * @param string $terminator the truncate identifier. all message contents 
   * on the same and proceeding lines from the first occurence of this value in 
   * the text message will be removed from the message. NOTE: this only applies 
   * to the text and NOT the html formatted message. this value may also be a 
   * regular expression starting and ending with '/' (forward slash)
	 * @access	public
   * @return void
	 */
	function truncate($terminator) {
    // check for regular expression
    if (SRA_Util::beginsWith($terminator, '/') && SRA_Util::endsWith($terminator, '/')) {
      preg_match($terminator, $this->message, $matches);
      if (count($matches)) {
        $this->truncate($matches[0]);
      }
    }
    else {
      $pieces = explode($terminator, $this->message);
      if (count($pieces) > 1) {
        $this->message = $pieces[0];
        for($i=count($this->message) - 1; $i>0; $i--) {
          if ($this->message{$i} == "\n") {
            $this->message = substr($this->message, 0, $i-1);
            break;
          }
        }
      }
    }
	}
	// }}}
  
  
  // Static methods
  
	// {{{ convert
	/**
	 * creates a new SRA_EmailMessage instance from raw message source (including 
   * headers). the return will always be an SRA_EmailMessage object. if any 
   * error occur, the SRA_EmailMessage::isValid method will return FALSE for 
   * that object
	 * @param string $raw either the raw message as a string, or the path to a 
   * file containing the message
	 * @access	public
	 * @return	SRA_EmailMessage
	 */
	function & convert(& $raw) {
		if (file_exists($raw)) {
      $raw =& trim(SRA_File::toString($raw));
    }
    $message = new SRA_EmailMessage();
    if ($raw) {
      $params = array();
      $params['Data'] =& $raw;
      $mime = new mime_parser_class();
      $mime->Decode($params, $decoded);
      if ($decoded && isset($decoded[0]) && isset($decoded[0]['Headers'])) {
        // cc
        if ($decoded[0]['Headers']['cc:']) {
          $email = SRA_Util::parseEmailString($decoded[0]['Headers']['cc:']);
          $message->cc = $email['email'];
          if ($email['name']) { $message->ccName = $email['name']; }
        }
        // from
        $email = SRA_Util::parseEmailString($decoded[0]['Headers']['from:'] ? $decoded[0]['Headers']['from:'] : $decoded[0]['Headers']['return-path:']);
        $message->from = $email['email'];
        if ($email['name']) { $message->fromName = $email['name']; }
        
        // mesasge id
        $message->messageId = $decoded[0]['Headers']['message-id:'];
        
        // priority
        $message->priorityLow = $decoded[0]['Headers']['x-priority:'] && $decoded[0]['Headers']['x-priority:'] == 5;
        $message->priorityHigh = $decoded[0]['Headers']['x-priority:'] && $decoded[0]['Headers']['x-priority:'] == 1;
        
        // the server path and date that the message was received (from the 
        // 'received:' header)
        $serverPath = array();
        $keys = array_keys($decoded[0]['Headers']['received:']);
        foreach($keys as $key) {
          // set received date
          if ($key == $keys[0]) {
            $pieces = explode(';', $decoded[0]['Headers']['received:'][$key]);
            $received = new SRA_GregorianDate($pieces[count($pieces) - 1], SRA_TimeZone::getTimeZone(SRA_TIME_ZONE_GMT));
            $received->setAppTimeZone();
            $message->received = $received;
          }
          preg_match(SRA_UTIL_IP_REGEX, $decoded[0]['Headers']['received:'][$key], $matches);
          if (count($matches)) { $serverPath[] = $matches[0]; }
        }
        $serverPath = array_reverse($serverPath);
        $message->serverPath = $serverPath;
        
        // return path
        $email = SRA_Util::parseEmailString($decoded[0]['Headers']['return-path:']);
        $message->returnPath = $email['email'];
        
        // subject
        $message->subject = trim($decoded[0]['Headers']['subject:']);
        
        // to
        if ($decoded[0]['Headers']['to:']) {
          $email = SRA_Util::parseEmailString($decoded[0]['Headers']['to:']);
          $message->to = $email['email'];
          if ($email['name']) { $message->toName = $email['name']; }
        }
        
        $parts =& SRA_EmailMessage::_getEmailParts($decoded[0]);
        if (isset($parts['attachments'])) { $message->attachments = $parts['attachments']; }
        $message->message = isset($parts['text']) ? $parts['text'] : (isset($parts['html']) ? strip_tags(preg_match('/\<[\s]*body.*\>([\S\s]+)<\/[\s]*body.*\>/i', $parts['html'], $m) ? $m[1] : $parts['html']) : NULL);
        $message->messageHtml = isset($parts['html']) ? $parts['html'] : str_replace("\n", "<br />\n", $message->message);
      }
      else {
        $message->err =& SRA_Error::logError('SRA_EmailMessage::convert - Unable to decode message', __FILE__, __LINE__);
      }
    }
    else {
      $message->err =& SRA_Error::logError('SRA_EmailMessage::convert - $raw parameter was not provided', __FILE__, __LINE__);
    }
    return $message;
	}
	// }}}
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a SRA_EmailMessage object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid(& $object) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_emailmessage');
	}
	// }}}
  
  
  // private
	// {{{ _getEmailParts
	/**
	 * this method extracts all of the parts from a decoded message starting at 
   * $decoded and continuing recursively through all of the parts and sub-parts 
   * it contains. the return value will be a hash with the following keys:
   *  attachments: SRA_FileAttribute[] - any attachments included in the message
   *  text: string - the text part of the message (if available)
   *  html: string - the html part of the message (if available)
	 * @param  array $decoded the decoded message to get the parts from. should 
   * contain a 'Headers' index (with a 'content-type:' sub-index) and a 'Body' 
   * or 'Parts' index (or both)
	 * @access	public
	 * @return	array
	 */
	function & _getEmailParts(& $decoded) {
		$parts = array();
    if ($decoded['Headers'] && $decoded['Headers']['content-type:']) {
      $pieces = explode(';', $decoded['Headers']['content-type:']);
      $contentType = strtolower(trim($pieces[0]));
      // multipart
      if ($contentType && strstr($contentType, 'multipart') && ($decoded['Parts'] && count($decoded['Parts']))) {
        $keys = array_keys($decoded['Parts']);
        foreach($keys as $key) {
          if ($subParts =& SRA_EmailMessage::_getEmailParts($decoded['Parts'][$key])) {
            if ($subParts['attachments']) {
              if (!$parts['attachments']) { $parts['attachments'] = array(); }
              $akeys = array_keys($subParts['attachments']);
              foreach($akeys as $akey) {
                $parts['attachments'][] =& $subParts['attachments'][$akey];
              }
            }
            if ($subParts['text']) { $parts['text'] =& $subParts['text']; }
            if ($subParts['html']) { $parts['html'] =& $subParts['html']; }
          }
        }
      }
      // non-multipart
      else if ($contentType && !strstr($contentType, 'multipart') && $decoded['Body'] && (strstr($decoded['Headers']['content-type:'], 'name=') || strstr($contentType, 'text/'))) {
        // file
        if (strstr($decoded['Headers']['content-type:'], 'name=')) {
          $pieces = explode('name=', $decoded['Headers']['content-type:']);
          $pieces = explode(';', $pieces[1]);
          $fileName = SRA_Util::stripQuotes($pieces[0]);
          $fileName = SRA_Util::stripQuotes($fileName, "'", "'");
          $file = new SRA_FileAttribute($fileName, $fileName, $decoded['BodyLength'], $contentType);
          $file->setBytes($decoded['Body']);
          $parts['attachments'] = array();
          $parts['attachments'][] =& $file;
        }
        // text or html
        else {
          $parts[$contentType == 'text/plain' ? 'text' : 'html'] =& $decoded['Body'];
        }
      }
    }
    return $parts;
	}
	// }}}

}
// }}}
?>
