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

// {{{ Constants
/**
 * the default country
 * @type String
 */
define('SRA_COUNTRY_DEFAULT', 'US');

/**
 * the name of the properties file containing the ISO 3166 country code/names
 * @type String
 */
define('SRA_COUNTRY_PROPERTIES', 'iso3166');

/**
 * the prefix for the country specific provinces properties files
 * @type String
 */
define('SRA_COUNTRY_PROVINCES_PREFIX', 'provinces-');
// }}}

// {{{ Includes
// }}}

// {{{ SRA_Country
/**
 * used to represent data and behavior associated with a specific country. when 
 * imported, a default instance of this class will be added to the application 
 * template using the variable name 'SRA_Country'
 * @author Jason Read <jason@idir.org>
 * @package sierra.util.l10n
 */
class SRA_Country {
  // {{{ Properties
  // private
  
  // public
  /**
   * the ISO 3166 country code that this object pertains to
   * @type String
   */
  var $code;
  
  // }}} 

  // {{{ SRA_Country
  /**
   * constructor used to instantiate a new SRA_Country instance for the $country 
   * specified. use the static SRA_Country::isValid method to determine if the 
   * instantiation is successful
   * @param String $country the ISO 3166 country code or country name. if not 
   * specified, SRA_COUNTRY_DEFAULT will be used
   * @access public
   */
  function SRA_Country($country=NULL) {
    if (!isset($country)) { $country = SRA_COUNTRY_DEFAULT; }
    
    $evalStr = trim(strtoupper($country));
    $countries =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROPERTIES);
    $data =& $countries->getData();
    $keys = array_keys($data);
    foreach($keys as $key) {
      if ($evalStr == strtoupper($key) || $evalStr == trim(strtoupper($data[$key]))) {
        $this->code = $key; 
      }
    }
  }
  // }}}
  
  // {{{ getCountryName
  /**
   * returns the country name for this country or for $code if invoked 
   * statically. returns NULL if this country object or $code is not valid
   * @param string $code ISO 3166 country code to return the country name for
   * @access public or public static
   * @return string
   */
  function getCountryName($code=NULL) {
    if (!$code) $code = $this->code;
    $key = trim(strtoupper($code));
    $countries =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROPERTIES);
    return $countries->containsKey(trim(strtoupper($code))) ? $countries->getString(trim(strtoupper($code))) : NULL;
  }
  // }}}
  
  // {{{ getProvinceCode
  /**
   * attempts to lookup the province code for this country (or $country when 
   * invoked statically) and the $region specified. if no match is found, NULL 
   * will be returned
   * @param string $region the region string to lookup the code for
   * @param string $code the ISO 3166 country code (required when invoked 
   * statically)
   * @access public or public static
   * @return string
   */
  function getProvinceCode($region, $code=NULL) {
    if (!$code) $code = $this->code;
    $code = strtolower($code);
    $evalStr = trim(strtoupper($region));
    if ($region && $code && file_exists(SRA_DIR . '/etc/l10n/' . SRA_COUNTRY_PROVINCES_PREFIX . strtolower($code) . '.properties')) {
      $regions =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROVINCES_PREFIX . strtolower($code));
      $data =& $regions->getData();
      $keys = array_keys($data);
      foreach($keys as $key) {
        if ($evalStr == strtoupper($key) || $evalStr == trim(strtoupper($data[$key]))) { return $key; }
      }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getProvinceName
  /**
   * attempts to lookup the province name for this country (or $country when 
   * invoked statically) and the $regionCode specified. if no match is found, 
   * NULL will be returned
   * @param string $regionCode the region code
   * @param string $code the ISO 3166 country code (required when invoked 
   * statically)
   * @access public or public static
   * @return string
   */
  function getProvinceName($regionCode, $code=NULL) {
    if (!$code) $code = $this->code;
    $code = strtolower($code);
    if (file_exists($f = SRA_CONF_DIR . '/l10n/' . SRA_COUNTRY_PROVINCES_PREFIX . strtolower($code) . '.properties')) {
      $regions =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROVINCES_PREFIX . strtolower($code));
      if ($regions->containsKey(trim(strtoupper($regionCode)))) { return $regions->getString(trim(strtoupper($regionCode))); }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getProvinces
  /**
   * returns an associative array of province code/name pair for this country 
   * (or $country when invoked statically)
   * @param string $code the ISO 3166 country code (required when invoked 
   * statically)
   * @access public
   * @return array
   */
  function getProvinces($code=NULL) {
    if (!$code) $code = $this->code;
    $code = strtolower($code);
    if (file_exists(SRA_CONF_DIR . '/l10n/' . SRA_COUNTRY_PROVINCES_PREFIX . $code . '.properties')) {
      $regions =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROVINCES_PREFIX . $code);
      return $regions->getData();
    }
  }
  // }}}
  
  // {{{ getTimeZones
  /**
   * returns the timezones associated with this country
   * @return SRA_TimeZone[]
   */
  function &getTimeZones() {
    $timeZones = array();
    foreach(SRA_TimeZone::getAllTimeZones($this->code) as $id) {
      $timeZones[] =& SRA_TimeZone::getTimeZone($id);
    }
    return $timeZones;
  }
  // }}}
  
  // private methods
  
  
  // static methods
  
  // {{{ getCode
  /**
   * attempts to lookup the ISO 3166 country code for the $country specified. if 
   * no match is found, NULL will be returned
   * @param string $country the country to lookup the code for. if this is 
   * already a valid code, it will be returned
   * @access public
   * @return string
   */
  function getCode($country) {
    if ($country && SRA_Country::isValid($country = new SRA_Country($country))) {
      return $country->code;
    }
    else {
      return NULL;
    }
  }
  // }}}
  
  // {{{ isValid
  /**
   * Static method that returns true if the object parameter is an 
   * SRA_Country object
   * @param Object $object the object to validate
   * @access public
   * @return  boolean
   */
  function isValid($object) {
    return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_country' && isset($object->code));
  }
  // }}}

}
// }}}
?>
