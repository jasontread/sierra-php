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
 * Constant used to define the regular expression to use for validating a domain
* name 
 */
 define('SRA_ATTRIBUTE_VALIDATOR_DOMAIN_REGEX', '^(www.|[a-zA-Z0-9\-].)*[a-zA-Z0-9\-\.]+\.([0-9]{1}|[0-9]{2}|[0-9]{3}|[a-zA-Z]{2}|[a-zA-Z]{3}).*$');
 
/**
 * Constant used to define the regular expression to use for validating email 
 * addresses
 */
define('SRA_ATTRIBUTE_VALIDATOR_EMAIL_REGEX', "/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i");

/**
 * Constant used to define the regular expression to use for validating an IPv4 
 * address
 */
define('SRA_ATTRIBUTE_VALIDATOR_IPV4_REGEX', '^(([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\\.){3}([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])(\/(1[0-1][0-9]|12[0-8]|[2-9][0-9]|1[6-9]))?$');

/**
 * Constant used to define the regular expression to use for validating an IPv6 
 * address
 */
define('SRA_ATTRIBUTE_VALIDATOR_IPV6_REGEX', '^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))(\/(1[0-1][0-9]|12[0-8]|[2-9][0-9]|1[6-9]))?$');

/**
 * Constant used to define the regular expression to use for validating mac 
 * addresses
 */
define('SRA_ATTRIBUTE_VALIDATOR_MAC_REGEX', '^[a-zA-Z0-9][a-zA-Z0-9]:[a-zA-Z0-9][a-zA-Z0-9]:[a-zA-Z0-9][a-zA-Z0-9]:[a-zA-Z0-9][a-zA-Z0-9]:[a-zA-Z0-9][a-zA-Z0-9]:[a-zA-Z0-9][a-zA-Z0-9]$');

/**
 * Constant used to define the regular expression to use for validating phone 
 * numbers
 */
 define('SRA_ATTRIBUTE_VALIDATOR_PHONE_REGEX', '(([0-9]{3})|(([0-9]{3}).*.([0-9]{3}))).*.([0-9]{4}).*$');

/**
 * Constant used to define the regular expression to use for validating a uri 
 */
 define('SRA_ATTRIBUTE_VALIDATOR_URI_REGEX', '/^(?#Protocol)(?:(?:ht|f)tp(?:s?)\:\/\/|~\/|\/)?(?#Username:Password)(?:\w+:\w+@)?(?#Subdomains)(?:(?:[-\w]+\.)+(?#TopLevel Domains)(?:com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum|travel|[a-z]{2}))(?#Port)(?::[\d]{1,5})?(?#Directories)(?:(?:(?:\/(?:[-\w~!$+|.,=;]|%[a-f\d]{2})+)+|\/)+|\?|#)?(?#Query)(?:(?:\?(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=\/\.]|%[a-f\d]{2})*)(?:&(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)*)*(?#Anchor)(?:#(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)?$/');

/**
 * Identifies the "msg" "key" defining a global message for type validations 
 * failures (boolean|date|integer|numeric)
 */
 define('SRA_ATTRIBUTE_TYPE_MSG_KEY', 'type');
// }}}

// {{{ SRA_AttributeValidator
/**
 * Contains static method used for validating entity attributes based on 
 * validation constrains specified in the 'depends' attribute of the 
 * 'attribute' definition. The value there will correspond directly with a 
 * static method in this class
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.core
 */
class SRA_AttributeValidator {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AttributeValidator
	/**
	 * Constructor - does nothing
   * @access  private
	 */
	function SRA_AttributeValidator() {}
	// }}}
	
  
  // public operations
	// {{{ boolean
	/**
	 * The 'boolean' validation constraint uses SRA_Util::isBoolean to determine if 
	 * $value is a valid representation of a boolean value. this validator is 
	 * automatically used if the data type is SRA_DATA_TYPE_BOOLEAN. 
	 * the following are considered valid representations:
	 *	 boolean constants TRUE/FALSE
	 *   integers 1/0
	 *	 strings '1'/'0'
	 *   strings 'true'/'false' (not case sensitive)
	 *   strings 't'/'f'
	 *   strings 'yes'/'no'
	 *   strings 'y'/'n'
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function boolean($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::boolean($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('boolean', $params)) {
        return FALSE;
      }
      return SRA_Util::isBoolean($value);
    }
	}
	// }}}
  
  
	// {{{ creditCard
	/**
	 * The 'creditCard' validation constraint returns TRUE if the credit card is 
   * in a valid format. $params may, but does not have to contain a parameter 
   * named "ccTypes" which is a space separated list of the credit card types 
   * to be validated. if this is not provided, all of the types below will be 
   * assumed to be valid:
   *    amex: American Express
   *    discover: Discover
   *    mc: Mastercard
   *    visa: Visa
   * number validation is performed using mod 10 arithmetic
   * setting the var: 'allowScrambled' to 1, will allow scrambled credit card 
   * #s to pass validation. a scrambled cc number is preceded by 12 *'s
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function creditCard($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::creditCard($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('creditCard', $params)) {
        return FALSE;
      }
      
      if ($params['allowScrambled'] && substr($value, 0, 12) == '************') {
        return TRUE;
      }
      $cardtype = FALSE;
      $len = strlen($value);
      if     ( $len == 15 && substr($value, 0, 1) == '3' )                 { $cardtype = "amex"; }
      elseif ( $len == 16 && substr($value, 0, 4) == '6011' )              { $cardtype = "discover"; }
      elseif ( $len == 16 && substr($value, 0, 1) == '5'  )                { $cardtype = "mc"; }
      elseif ( ($len == 16 || $len == 13) && substr($value, 0, 1) == '4' ) { $cardtype = "visa"; }
      
      $double = array( 0,2,4,6,8,1,3,5,7,9 ); 
      $ccnum = strrev( $value ); 
      for( $i=0; $i<strlen( $ccnum ); $i++ ) 
          $values[] = ( $i&1 ) ? $double[$ccnum[$i]] : $ccnum[$i]; 
      $valid = ( array_sum( $values ) % 10 ) ? 0 : 1;
      
      $ccTypes = isset($params['ccTypes']) ? explode(' ', $params['ccTypes']) : FALSE;
      
      return $valid && (!$ccTypes || in_array($cardtype, $ccTypes));
    }
	}
	// }}}
	
	
	// {{{ date
	/**
	 * The 'date' validation constraint specifies that an attribute value must
	 * be a properly formatted date according to the GNU date syntax. For more 
	 * information on acceptable formats see 
	 * http://www.gnu.org/software/tar/manual/html_chapter/tar_7.html. this 
	 * validator is automatically used if the data type is SRA_DATA_TYPE_DATE
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function date($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::date($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('date', $params)) {
        return FALSE;
      }
      return SRA_GregorianDate::isValid($value) || strtotime($value) != -1;
    }
	}
	// }}}
  
  
	// {{{ domain
	/**
	 * The 'domain' validation constraint specifies that an attribute value must
	 * be a properly formatted domain name address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function domain($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::domain($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('domain', $params)) {
        return FALSE;
      }
      $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_DOMAIN_REGEX);
      return SRA_AttributeValidator::mask($value, $eparams);
    }
	}
	// }}}
  
  
	// {{{ email
	/**
	 * The 'email' validation constraint specifies that an attribute value must
	 * be a properly formatted email address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function email($value, $params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::email($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('email', $params)) {
        return FALSE;
      }
      $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_EMAIL_REGEX);
      return SRA_AttributeValidator::mask($value, $eparams);
    }
	}
	// }}}
	
	
	// {{{ fileExtension
	/**
	 * The 'fileExtension' validation constraint specifies that an file attribute 
	 * must utilize a specific file extension (as specified in by the original 
	 * name of the user file). This method requires the params key 
	 * 'fileExtensions' to provide a space separated list of file extensions that 
	 * are allowed. file extension validation is not case-sensitive.
	 * @param SRA_FileAttribute $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function fileExtension(& $value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::fileExtension($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('fileExtension', $params) && !SRA_FileAttribute::isValid($value)) {
        return FALSE;
      }
      $supportedExtensions = explode(' ', $params['fileExtensions']);
      $keys = array_keys($supportedExtensions);
      foreach ($keys as $key) {
        if (strlen(trim($supportedExtensions[$key]))) {
          $supportedExtensions[$key] = trim(strtolower($supportedExtensions[$key]));
        }
        else {
          unset($supportedExtensions[$key]);
        }
      }
      return in_array(strtolower($value->getFileExtension()), $supportedExtensions);
    }
	}
	// }}}
	
	
	// {{{ integer
	/**
	 * The 'integer' validation constraint verifies that the value is numeric and 
	 * not floating point. this validator is automatically used if the data type 
	 * is SRA_DATA_TYPE_INT
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function integer($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::integer($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('integer', $params)) {
        return FALSE;
      }
      $valid = is_numeric($value) && strpos($value, '.') === FALSE;
      return $valid;
    }
	}
	// }}}
  
  
	// {{{ ip
	/**
	 * The 'ip' validation constraint verifies that the value is a valid formatted 
   * IP address. this function  validates both IPv4 and IPv6 addresses (to 
   * validate a specific IP version, use the ipv4 or  ipv6 functions)
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function ip($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::ip($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('ip', $params)) {
        return FALSE;
      }
      return SRA_AttributeValidator::ipv4($value, $params) || SRA_AttributeValidator::ipv6($value, $params);
    }
	}
	// }}}
  
  
	// {{{ ipv4
	/**
	 * The 'ipv4' validation constraint verifies that the value is a valid 
   * formatted IPv4 address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function ipv4($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::ipv4($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('ip', $params)) {
        return FALSE;
      }
      /*
      $valid = trim($value) ? FALSE : TRUE;
      $pieces = explode('.', $value);
      if (count($pieces) == 4 && !$params['IPv6']) {
        $valid = TRUE;
        foreach($pieces as $val) {
          if (!is_numeric($val) || $val < 0 || $val > 255) { $valid = FALSE; }
        }
      }
      return $valid;
      */
      return SRA_AttributeValidator::mask($value, $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_IPV4_REGEX));
    }
	}
	// }}}
  
  
	// {{{ ipv6
	/**
	 * The 'ipv6' validation constraint verifies that the value is a valid 
   * formatted IPv6 address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function ipv6($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::ipv6($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('ip', $params)) {
        return FALSE;
      }
      /*
      $valid = trim($value) ? FALSE : TRUE;
      $pieces = explode(':', $value);
      if (count($pieces) == 8 && !$params['IPv4']) {
        $valid = TRUE;
        foreach($pieces as $val) {
          $val = SRA_Util::trimLeadingZeros($val);
          if (trim($val)) { $val = '0'; }
          if (strlen($val) == 0 || strlen($val) > 4 || base_convert(base_convert($val, 16, 10), 10, 16) != $val) { echo $val . "\n"; $valid = FALSE; }
        }
      }
      return $valid;
      */
      return SRA_AttributeValidator::mask($value, $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_IPV6_REGEX));
    }
	}
	// }}}
  
  
	// {{{ mac
	/**
	 * The 'mac' validation constraint specifies that an attribute value must
	 * be a properly formatted mac address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function mac($value, $params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::mac($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('mac', $params)) {
        return FALSE;
      }
      $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_MAC_REGEX);
      return SRA_AttributeValidator::mask($value, $eparams);
    }
	}
	// }}}
	
	
	// {{{ mask
	/**
	 * The 'mask' validation constraint specifies that an attribute value must 
	 * match a regular expression. This method requires the params key 'mask' to 
	 * define the regular expression that should be matched
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function mask($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::mask($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('mask', $params)) {
        return FALSE;
      }
      return preg_match($params['mask'], $value) || preg_match($params['mask'], $value) || preg_match('/' . $params['mask'] . '/', $value) ? TRUE : FALSE;
    }
	}
	// }}}
	
	
	// {{{ max
	/**
	 * The 'max' validation constraint specifies that an attribute value must 
	 * be less than or equal to a specific numeric value. This method requires 
	 * the params key 'max' to define the maximum value constraint
	 * @param numeric $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function max($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::max($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('max', $params)) {
        return FALSE;
      }
      return is_numeric($value) && $value <= $params['max'];
    }
	}
	// }}}
	
	
	// {{{ maxFileSize
	/**
	 * The 'maxFileSize' validation constraint specifies that an file attribute 
	 * can be at most a specific size in bytes. This method requires the 
	 * params key 'maxFileSize' to define the maximum size in bytes for the file.
	 * @param SRA_FileAttribute $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function maxFileSize(& $value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::maxFileSize($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('maxFileSize', $params) && !SRA_FileAttribute::isValid($value)) {
        return FALSE;
      }
      return $value->getSize() <= $params['maxFileSize'];
    }
	}
	// }}}
	
	
	// {{{ maxLength
	/**
	 * The 'maxLength' validation constraint specifies that an attribute value can
	 * only be a certain length. This method requires the params key 'maxLength' 
	 * to define the maximum allowable length
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function maxLength($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::maxLength($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('maxLength', $params)) {
        return FALSE;
      }
      return strlen($value) <= $params['maxLength'];
    }
	}
	// }}}
	
	
	// {{{ mimeType
	/**
	 * The 'mimeType' validation constraint specifies that an file attribute must 
	 * be a specific mime-type (as specified by the browser when the file was 
	 * uploaded). This method requires the params key 'mimeTypes' to provide a 
	 * space separated list of mime-types that are allowed. mime-type validation 
	 * is not case-sensitive. 'mimeTypes' may also contain regular expressions.
	 * @param SRA_FileAttribute $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function mimeType(& $value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::mimeType($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('mimeType', $params) && !SRA_FileAttribute::isValid($value)) {
        return FALSE;
      }
      $supportedMimeTypes = explode(' ', $params['mimeTypes']);
      $keys = array_keys($supportedMimeTypes);
      foreach ($keys as $key) {
        if (strlen(trim($supportedMimeTypes[$key]))) {
          $supportedMimeTypes[$key] = trim(strtolower($supportedMimeTypes[$key]));
        }
        else {
          unset($supportedMimeTypes[$key]);
        }
      }
      foreach ($keys as $key) {
        if (ereg($supportedMimeTypes[$key], strtolower($value->getType()))) {	
                            return TRUE;
        }
      }
      return FALSE;
    }
	}
	// }}}
	
	
	// {{{ min
	/**
	 * The 'min' validation constraint specifies that an attribute value must 
	 * be greater than or equal to a specific numeric value. This method requires 
	 * the params key 'min' to define the minimum value constraint
	 * @param numeric $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function min($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::min($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('min', $params)) {
        return FALSE;
      }
      return is_numeric($value) && $value >= $params['min'];
    }
	}
	// }}}
	
	
	// {{{ minFileSize
	/**
	 * The 'minFileSize' validation constraint specifies that an file attribute 
	 * must be a at least a specific size in bytes. This method requires the 
	 * params key 'minFileSize' to define the minimum size in bytes for the file.
	 * @param SRA_FileAttribute $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function minFileSize(& $value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::minFileSize($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('minFileSize', $params) && !SRA_FileAttribute::isValid($value)) {
        return FALSE;
      }
      return $value->getSize() >= $params['minFileSize'];
    }
	}
	// }}}
	
	
	// {{{ minLength
	/**
	 * The 'minLength' validation constraint specifies that an attribute value 
	 * must be a certain length. This method requires the params key 'minLength' 
	 * to define the minimum allowable length
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function minLength($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::minLength($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('minLength', $params)) {
        return FALSE;
      }
      return strlen($value) >= $params['minLength'];
    }
	}
	// }}}
  
  
	// {{{ numeric
	/**
	 * The 'numeric' validation constraint verifies that the value is numeric.  
	 * validator is automatically used if the data type is SRA_DATA_TYPE_FLOAT
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function numeric($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::numeric($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('numeric', $params)) {
        return FALSE;
      }
      return is_numeric($value);
    }
	}
	// }}}
	
	
	// {{{ option
	/**
	 * The 'option' validation constraint specifies that an attribute value must 
	 * be equal to one value in a set of values specified in one of the following 
	 * ways:
	 * 
	 * 'options' var:  must be a space separate list of valid options using the 
	 * following format:
	 * 		{option1} {option2}... 
	 * 		OR 
	 *		{option1 label}={option1} {option2 label}={option2}...
	 * 
	 * Neither option labels or values should contain spaces
   *
   * 'code' var: code snippet that should set an $options variable containing 
   * the valid options
	 * 
	 * 'resources' var: app or system relative or fixed path to a properties 
	 * file (minus the file extension) containing the options
	 *
	 * 'sql' var: a database query that will return 1 or 2 columns. if 1, that 
	 * column will be used for both key/value, if 2, the first will be used as 
	 * the key, the second as the display value
	 *
	 * 'displ' var: if the attribute is of type entity, and option or options 
	 * validation constraint is applied, then the user MUST select an existing 
	 * instance of that entity (i.e. where primary key value exists) and this var 
	 * will define the "display" value in the options map returned by 
	 * VO::getOptionsMap(). this value will be passed to VO::parseString($displ) 
	 * if it contains a $ symbol, and VO::getAttribute($displ) otherwise. For 
	 * these type of options, the following additional optional vars may be 
	 * specified:
	 *   'constraint': sql constraint to apply to the query used to determine 
	 *                 which entities should be options. if not specified, all 
	 *                 entities will be returned in the options map subject to the 
	 *                 remaining variable options described below
	 *	 'order-by':   an sql order by statement. use if different sorting is 
	 *                 desired than what is specified in the entity definition
	 *   'limit':      max # of entities to be included in the options map
	 *   'offset':     sql return pointer offset
	 * 
	 * Attributes that use this validation method can also use the 
	 * VO::getOptionsMap(string) method which will return an associative array of 
	 * value/label pairs of options for the attribute
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function option($value, &$params) {
		if (!SRA_AttributeValidator::isValidValidator('option', $params)) {
			return FALSE;
		}
		if (is_array($value)) {
			return FALSE;
		}
		return SRA_AttributeValidator::options($value, $params);
	}
	// }}}
	
	
	// {{{ options
	/**
	 * The 'option' validation constraint specifies that an attribute value must 
	 * be equal to zero or more value in a set of values specified in one of the 
	 * following ways:
	 * 
	 * 'options' var:  must be a space separate list of valid options using the 
	 * following format:
	 * 		{option1} {option2}... 
	 * 		OR 
	 *		{option1 label*}={option1*} {option2 label}={option2}...
	 * 
	 * Neither option labels or values should contain spaces
   *
   * 'code' var: code snippet that should set an $options variable containing 
   * the valid options
	 * 
	 * 'resources' var: app or system relative or fixed path to a properties 
	 * file (minus the file extension) containing the options. will be accessed 
	 * using SRA_ResourceBundle::getBundle
	 *
	 * 'sql' var: a database query that will return 1 or 2 columns. if 1, that 
	 * column will be used for both key/value, if 2, the first will be used as 
	 * the key, the second as the display value
	 *
	 * 'displ' var: if the attribute is of type entity, and option or options 
	 * validation constraint is applied, then the user MUST select an existing 
	 * instance of that entity (i.e. where primary key value exists) and this var 
	 * will define the "display" value in the options map returned by 
	 * VO::getOptionsMap(). this value will be passed to VO::parseString($displ) 
	 * if it contains a $ symbol, and VO::getAttribute($displ) otherwise. For 
	 * these type of options, the following additional optional vars may be 
	 * specified:
	 *   'constraint': sql constraint to apply to the query used to determine 
	 *                 which entities should be options. if not specified, all 
	 *                 entities will be returned in the options map subject to the 
	 *                 remaining variable options described below
	 *	 'order-by':   an sql order by statement. use if different sorting is 
	 *                 desired than what is specified in the entity definition
	 *   'limit':      max # of entities to be included in the options map
	 *   'offset':     sql return pointer offset
	 * 
	 * Attributes that use this validation method can also use the 
	 * VO::getOptionsMap() method which will return an associative array of 
	 * value/label pairs of options for the attribute
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function options($value, &$params) {
		if (!SRA_AttributeValidator::isValidValidator('options', $params)) {
			return FALSE;
		}
		if (isset($value)) {
			if (!is_array($value)) {
				$value = array($value);
			}
			$matched = FALSE;
			
			$keys = array_keys($value);
			foreach ($keys as $key) {
			
				// entity
				if (is_object($value[$key]) && method_exists($value[$key], 'getPrimaryKey')) {
					if ($value[$key]->getPrimaryKey()) {
						return TRUE;
					}
				}
				
				// 'options' var
				else if (isset($params['options'])) {
					$options = explode(' ', $params['options']);
					$vkeys = array_keys($value);
					$keys = array_keys($options);
					foreach ($vkeys as $vkey) {
						foreach ($keys as $key) {
							if (strstr($options[$key], '=')) {
								$tmp = explode('=', $options[$key]);
								$options[$key] = trim($tmp[1]);
							}
							if ($options[$key] == $value[$vkey]) {
								$matched = TRUE;
								break;
							}
						}
					}
				}
        
				// 'code' var
				else if (isset($params['code'])) {
          eval($params['code']);
					return isset($options) && is_array($options) && isset($options[$value[$key]]);
				}
				
				// 'resources' var
				else if (isset($params['resources'])) {
					if (SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle($params['resources'])) && 
							$rb->containsKey($value[$key])) {
						$matched = TRUE;
					}
				}
				
				// 'sql' var
				else if (isset($params['sql'])) {
					$matched = in_array($value[$key], SRA_AttributeValidator::getSqlOptionsMap($params['sql']));
				}
				
				if (!$matched) {
					return FALSE;
				}
			
			}
			
		}
		return TRUE;
	}
	// }}}
	
	
	// {{{ phone
	/**
	 * The 'phone' validation constraint specifies that an attribute value must
	 * be a properly formatted phone number according to the pattern specified in 
	 * the constant SRA_ATTRIBUTE_VALIDATOR_PHONE_REGEX
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function phone($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::phone($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('phone', $params)) {
        return FALSE;
      }
      $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_PHONE_REGEX);
      return SRA_AttributeValidator::mask($value, $eparams);
    }
	}
	// }}}
	
	
	// {{{ range
	/**
	 * The 'range' validation constraint specifies that an attribute value must 
	 * be between an upper and lower bound. This method requires the params keys 
	 * 'min' and 'max' to define the bound. this validation constraint accepts 
	 * only numeric types
	 * @param numeric $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function range($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::range($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('range', $params)) {
        return FALSE;
      }
      return is_numeric($value) && $value >= $params['min'] && $value <= $params['max'];
    }
	}
	// }}}
	
	
	// {{{ required
	/**
	 * The 'required' validation constraint specifies that an attribute value must 
	 * be provided.
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function required($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::required($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('required', $params)) {
        return FALSE;
      }
      return isset($value);
    }
	}
	// }}}
  
  
	// {{{ timezone
	/**
	 * The 'timezone' validation constraint specifies that an attribute value must 
	 * be a valid timezone (see SRA_TimeZone::getAllTimeZones)
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function timezone($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::timezone($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      return SRA_AttributeValidator::isValidValidator('timezone', $params) && $value && SRA_TimeZone::getTimeZone($value) ? TRUE : FALSE;
    }
	}
	// }}}
  
  
	// {{{ unique
	/**
	 * The 'unique' validation constraint specifies that an attribute value must
	 * be unique within the database table in which it is stored
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function unique($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::unique($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('unique', $params)) {
        return FALSE;
      }
      $dao =&$params['object']->getDao();
      return ($params['object']->recordExists && !$params['object']->isDirty($params['attribute'])) || count($dao->findBySqlConstraints(array($dao->getColumnName($params['attribute']) => $params['object']->getAttrDbValue($params['attribute'], $value)))) == 0;
    }
	}
	// }}}
	
	
	// {{{ uri
	/**
	 * The 'uri' validation constraint specifies that an attribute value must
	 * be a properly formatted uri address
	 * @param string $value the attribute value to validate
	 * @param array $param the input parameters specified for the attribute. these 
	 * include validation constraint specific values
   * @access  public
	 * @return boolean TRUE if the attribute value does validate, FALSE otherwise
	 */
	function uri($value, &$params) {
    if (is_array($value)) {
      foreach($value as $val) {
        if (!SRA_AttributeValidator::uri($val, $params)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    else {
      if (!SRA_AttributeValidator::isValidValidator('uri', $params)) {
        return FALSE;
      }
      $eparams = array('mask' => SRA_ATTRIBUTE_VALIDATOR_URI_REGEX);
      return SRA_AttributeValidator::mask($value, $eparams);
    }
	}
	// }}}
	
	
	// {{{ getSqlOptionsMap
	/**
	 * Used to obtain an options map for a given sql query
	 * @param string $sql the sql query that will provide the options
   * @access  public static
	 * @return array
	 */
	function & getSqlOptionsMap($sql) {
		static $cachedOptions = array();
		if (!isset($cachedOptions[$sql])) {
			$db =& SRA_Controller::getAppDb();
			$cachedOptions[$sql] = array();
			if (SRA_ResultSet::isValid($results =& $db->fetch($sql, array(SRA_DATA_TYPE_STRING, SRA_DATA_TYPE_STRING)))) {
				while ($row =& $results->next()) {
					if (isset($row[1])) {
						$cachedOptions[$sql][$row[0]] = $row[1];
					}
					else {
						$cachedOptions[$sql][$row[0]] = $row[0];
					}
				}
			}
		}
		return $cachedOptions[$sql];
	}
	// }}}
	
	
	// {{{ isValidValidator
	/**
	 * Used to test whether or not a validator and corresponding params are valid
	 * @param string $validator the name of the validate method
	 * @param array $param the input parameters for that validate method
   * @param boolean $ignoreObject whether or not to validate existing of the 
   * 'object' and 'attribute' parameters
   * @access  public
	 * @return boolean TRUE if the validator is valid, FALSE otherwise
	 */
	function isValidValidator($validator, $params, $ignoreObject=FALSE) {
		// boolean
		if ($validator == 'boolean') {
			return TRUE;
		}
    
		// date
		if ($validator == 'creditCard') {
			return TRUE;
		}
		
		// date
		if ($validator == 'date') {
			return TRUE;
		}
		
		// domain
		if ($validator == 'domain') {
			return TRUE;
		}
		
		// email
		else if ($validator == 'email') {
			return TRUE;
		}
		
		// integer
		if ($validator == 'integer') {
			return TRUE;
		}
    
		// ip
		if ($validator == 'ip') {
			return TRUE;
		}
    
		// mac
		else if ($validator == 'mac') {
			return TRUE;
		}
		
		// numeric
		if ($validator == 'numeric') {
			return TRUE;
		}
		
		// fileExtension
		else if ($validator == 'fileExtension' &&$params['fileExtensions']) {
			return TRUE;
		}
		
		// mask
		else if ($validator == 'mask' && $params['mask']) {
			return TRUE;
		}
		
		// max
		else if ($validator == 'max' && isset($params['max'])) {
			return TRUE;
		}
		
		// maxFileSize
		else if ($validator == 'maxFileSize' && isset($params['maxFileSize']) && is_numeric($params['maxFileSize'])) {
			return TRUE;
		}
		
		// maxLength
		else if ($validator == 'maxLength' && isset($params['maxLength'])) {
			return TRUE;
		}
		
		// mimeType
		else if ($validator == 'mimeType' &&$params['mimeTypes']) {
			return TRUE;
		}
		
		// min
		else if ($validator == 'min' && isset($params['min'])) {
			return TRUE;
		}
		
		// minFileSize
		else if ($validator == 'minFileSize' && isset($params['minFileSize']) && is_numeric($params['minFileSize'])) {
			return TRUE;
		}
		
		// minLength
		else if ($validator == 'minLength' && isset($params['minLength'])) {
			return TRUE;
		}
		
		// option(s)
		else if (($validator == 'option' || $validator == 'options') && (isset($params['options']) || isset($params['resources']) || isset($params['sql']) || isset($params['displ']) || isset($params['code']))) {
			return TRUE;
		}
		
		// phone
		else if ($validator == 'phone') {
			return TRUE;
		}
		
		// range
		else if ($validator == 'range' && isset($params['min']) && isset($params['max'])) {
			return TRUE;
		}
		
		// required
		else if ($validator == 'required') {
			return TRUE;
		}
    
		// timezone
		if ($validator == 'timezone') {
			return TRUE;
		}
    
		// unique
		if ($validator == 'unique' && ($ignoreObject || (isset($params['object']) && isset($params['attribute'])))) {
			return TRUE;
		}
		
		// uri
		if ($validator == 'uri') {
			return TRUE;
		}
		
		return FALSE;
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * Returns a string representation of this object
   * @access  public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_AttributeValidator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_attributevalidator');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
