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
 * SRA_Locale object debug flag
 * @type   boolean
 * @access public
 */
define('SRA_LOCALE_DEBUG', FALSE);
/**
 * Constant which represents the English speaking portion of Canada. This
 * symbol is broken up into 2 values separated by the "&" symbol. The
 * first value is the country code and the second value is the language
 * code (as defined by the language constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_CANADA', "CA&en");
/**
 * Constant which represents the French speaking portion of Canada. This
 * symbol is broken up into 2 values separated by the "&" symbol. The
 * first value is the country code and the second value is the language
 * code (as defined by the language constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_CANADA_FRENCH', "CA&fr");
/**
 * Constant which represents the English language.
 * @type   String
 * @access public
 */
define('SRA_LOCALE_ENGLISH', "en");
/**
 * Constant which represents France. This symbol is broken up into 2 values
 * separated by the "&" symbol. The first value is the country code and
 * the second value is the language code (as defined by the language
 * constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_FRANCE', "FR&fr");
/**
 * Constant which represents the French language.
 * @type   String
 * @access public
 */
define('SRA_LOCALE_FRENCH', "fr");
/**
 * Constant which represents the German language.
 * @type   String
 * @access public
 */
define('SRA_LOCALE_GERMAN', "de");
/**
 * Constant which represents Germany. This symbol is broken up into 2
 * values separated by the "&" symbol. The first value is the country code
 * and the second value is the language code (as defined by the language
 * constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_GERMANY', "de-de");
/**
 * Contant which represents Spain. This symbol is broken up into 2 values
 * separated by the "&" symbol. The first value is the country code and
 * the second value is the language code (as defined by the language
 * constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_SPAIN', "es-es");
/**
 * Constant which represents the Spanish language.
 * @type   String
 * @access public
 */
define('SRA_LOCALE_SPANISH', "es");
/**
 * Constant which represents the United Kingdom. This symbol is broken up
 * into 2 values separated by the "&" symbol. The first value is the
 * country code and the second value is the language code (as defined by
 * the language constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_UK', "gb-en");
/**
 * String which represents the United States. This symbol is broken up into
 * 2 values separated by the "&" symbol. The first value is the country
 * code and the second value is the language code (as defined by the
 * language constants... i.e. SRA_LOCALE_ENGLIGH).
 * @type   String
 * @access public
 */
define('SRA_LOCALE_US', "us-en");
/**
 * The location of the SRA_Locale xml definition file. 
 * @type   String
 * @access public
 */
define('SRA_LOCALE_XML_DATA_FILE', file_exists(SRA_CONF_DIR . '/l10n/locales.xml') ? SRA_CONF_DIR . '/l10n/locales.xml' : SRA_CONF_DIR . '/l10n/locales-default.xml');

/**
 * the default country to use if one is not specified in the getLocale method or 
 * if the country specified is not configured in the locales xml file
 * @type   String
 * @access public
 */
define('SRA_LOCALE_DEFAULT_COUNTRY', 'us');

/**
 * the default country to use if one is not specified in the getLocale method or 
 * if the country specified is not configured in the locales xml file
 * @type   String
 * @access public
 */
define('SRA_LOCALE_DEFAULT_LANGUAGE', 'en');

/**
 * the default country to use if one is not specified in the getLocale method
 * @type   String
 * @access public
 */
define('SRA_LOCALE_DEFAULT_LOCALE', SRA_LOCALE_US);
// }}}

// {{{ Includes
include_once('util/l10n/SRA_Currency.php');
// }}}

// {{{ SRA_Locale
/**
 * The SRA_Locale class (including api comments) was borrowed directly from
 * Java:
 * 
 * SRA_Locales represent a specific country and culture. Classes which can
 * be
 * passed a SRA_Locale object tailor their information for a given locale. For
 * instance, currency number formatting is handled differently for the USA
 * and France.
 * 
 * SRA_Locales are made up of a language code, and a country code. Language
 * codes are represented by
 * <a href="http://www.ics.uci.edu/pub/ietf/http/related/iso639.txt">
 * ISO 639:1988</a> w/ additions from ISO 639/RA Newsletter No. 1/1989
 * and a decision of the Advisory Committee of ISO/TC39 on August 8, 1997.
 * 
 * Country codes are represented by
 * <a href="http://www.chemie.fu-berlin.de/diverse/doc/ISO_3166.html">
 * ISO 3166</a>. 
 * 
 * The default locale is determined based on the values of the system
 * etc or the app conf. 
 * 
 * SRA_Locales are stored in the SRA_LOCALE_XML_DATA_FILE file. This file
 * consists of 1..* locales in the format described in the corresponding dtd.
 * 
 * Country and language display name attributes are retrieved from
 * property files located in the {SRA_DIR}/etc/l10n directory.
 * Country display names are stored as "iso3166.properties" and languages
 * as "iso639.properties".
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.util.l10n
 */
class SRA_Locale {
    // {{{ Properties
    /**
     * The country code for the instantiated SRA_Locale object.
     * @type   String
     * @access private
     */
    var $_country;
    /**
     * The currency used by this locale (for more info see the SRA_Currency 
	 * class api). 
     * @type   SRA_Currency
     * @access private
     */
    var $_currency;
    /**
     * The date format string (i.e. Y-m-d > 2000-01-01)
     * @type   String
     * @access private
     */
    var $_dateFormat= "Y-m-d";
    /**
     * The date time format string (i.e. Y-m-d h:i:s > 2000-01-01
     * 12:00:00).
     * @type   String
     * @access private
     */
    var $_dateTimeFormat= "Y-m-d h:i:s";
    /**
     * The separator to use between the whole and decimal portion of a
     * number. The default is a period
     * @type   char
     * @access private
     */
    var $_decimalSeparator= ".";
    /**
     * The separator to use between whole number groups (i.e. 100,000). The
     * default is a comma.
     * @type   char
     * @access private
     */
    var $_intGroupSeparator= ",";
    /**
     * The three-letter ISO identifier for the country.
     * @type   String
     * @access private
     */
    var $_iso3Country;
    /**
     * The three-letter ISO identifier for the language.
     * @type   String
     * @access private
     */
    var $_iso3Language;
    /**
     * The language code for the instantiated SRA_Locale object.
     * @type   String
     * @access private
     */
    var $_language;
    /**
     * The time format string (i.e. h:i:s > 12:00:00)
     * @type   String
     * @access private
     */
    var $_timeFormat= "h:i:s";
    // }}} 

    // {{{ SRA_Locale()
    /**
     * do not instantiate directly. instead use the singleton getLocale method
     * to object an instance of this class
     *
     * @access  private
     */
    function SRA_Locale() {}
    // }}}

    // {{{ equals()
    /**
     * Compares two locales. To be equal, object must be a SRA_Locale with the
     * same language and country.
     *
     * @param   object : Object - The locale to compare with.
     * @access  public
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function equals($object)
    {
        return (SRA_Locale::isValid($object) && $object->getLanguage() == $this->_language && 
				$object->getCountry() == $this->_country);
    }
    // }}}

    // {{{ formatNumber()
    /**
     * Returns a formatted string representation of the number specified.
     *
     * @param   number : float - The number to convert.
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function formatNumber($number)
    {
		if (!isset($this->_intGroupSeparator) || !isset($this->_decimalSeparator))
		{
			$msg = "SRA_Locale::formatNumber: Failed - _intGroupSeparator or _decimalSeparator attributes are not valid";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
		}
		if (!is_numeric($number))
		{
			$msg = "SRA_Locale::formatNumber: Failed - Invalid number parameter '$number'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
		}
		
		$values = explode(".", $number);
		
		// Format whole value
		$value = $values[0];
		if (strlen($value) > 3)
		{
			$groupSeps = floor((strlen($values[0]) - 1)/3);
			for ($i=0; $i<$groupSeps; $i++)
			{
				$value = substr($value, 0, strlen($value) - ($i * 3) - 3 - $i) . $this->_intGroupSeparator . 
							 substr($value, strlen($value) - ($i * 3) - 3 - $i, 3 + ($i * 3) + $i);
			}
			
		}
		
		if (count($values) == 2)
		{
			$value = $value . $this->_decimalSeparator . $values[1];
		}
        
		return $value;
		
    }
    // }}}
	
    // {{{ getCountryList()
    /**
     * Static method that returns an associative array of coutries and 
	 * their corresponding display values based on the SRA_Locale parameter 
	 * specified. 
     *
	 * @param	locale : SRA_Locale - The SRA_Locale object to use when determining 
	 * 			the display name to use for each country. If this parameter 
	 * 			is not specified, the default app or system local will 
	 *			be used.
     * @access  public static
     * @return  String[]
     * @author  Jason Read <jason@idir.org>
     */
    function getCountryList($locale = false)
    {
		/**
		 * Static array maintained in the getCountryList method as a cache 
		 * variable.  
		 * @type   String[]
		 * @access private
		 */
		static $_cachedCountryList = array();
		
		// Get SRA_Locale
        if (!SRA_Locale::isValid($locale =& SRA_Locale::getDefaultLocale($locale)))
		{
			$msg = "SRA_Locale::getCountryList: Failed - Could not obtain SRA_Locale object";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
		}
		
		// Check for cached version of country list
		if (!array_key_exists($locale->getLanguage(), $_cachedCountryList))
		{
			// Get SRA_XmlParser for SRA_Locale data
			if (!SRA_XmlParser::isValid($localeData =& SRA_Locale::_getLocaleData()))
			{
				$msg = "SRA_Locale::getCountryList: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Locale data";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			// Get country array
			if (SRA_Error::isError($countries =& $localeData->getData(array("countries", "country"))))
			{
				$msg = "SRA_Locale::getCountryList: Failed - Could not obtain country list from SRA_XmlParser";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			$countryCodes = array_keys($countries);
			if (!is_array($countryCodes))
			{
				$msg = "SRA_Locale::getCountryList: Failed - Could not obtain country list: '" . gettype($countryCodes) . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			sort($countryCodes);
			
			// Get country name resource bundle
			if (!SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle('iso3166')))
			{
				$msg = "SRA_Locale::getCountryList: Failed - Could not obtain resource bundle for country names";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			$countryList = array();
			foreach  ($countryCodes as $countryCode)
			{
				if (SRA_Error::isError($countryList[$countryCode] = $rb->getString($countryCode)))
				{
					$msg = "SRA_Locale::getCountryList: Failed - Could not obtain country name from iso3166 file for country '" . 
							$countryCode . "' and language '" . $locale->getLanguage() . "'";
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
				}
			}
			
			$_cachedCountryList[$locale->getLanguage()] = $countryList;
		}
		
		return $_cachedCountryList[$locale->getLanguage()];
		
    }
    // }}}
	
    // {{{ getCurrency()
    /**
     * Returns a reference to the _currency attribute
     *
     * @access  public
     * @return  SRA_Currency
     * @author  Jason Read <jason@idir.org>
     */
    function & getCurrency()
    {
        if (isset($this->_currency))
        {
            return($this->_currency);
        }
    }
    // }}}

    // {{{ getCountry()
    /**
     * Returns the value of the _country attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getCountry() {
			return($this->_country);
    }
    // }}}
		
    // {{{ getCountryName()
    /**
     * Returns the locale specific country name, or NULL if the locale only 
     * represents a language
     *
		 * @param SRA_Locale $locale - an optional locale to use when getting the country 
		 * name resource bundle. if not specified, this SRA_Locale will be used
     * @access  public
     * @return  String
     */
    function getCountryName($locale = FALSE) {
      if ($this->_country) {
        if (!$locale) {
          $locale =& $this;
        }
        $rb =& SRA_ResourceBundle::getBundle('iso3166');
        return $rb->getString($this->_country);
      }
      return NULL;
    }
    // }}}

    // {{{ getAppDateFormat()
    /**
     * Returns the value of the _dateFormat attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getAppDateFormat()
    {
        if (isset($this->_dateFormat))
        {
            return($this->_dateFormat);
        }
    }
    // }}}

    // {{{ getDateTimeFormat()
    /**
     * Returns the value of the _dateTimeFormat attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getDateTimeFormat()
    {
        if (isset($this->_dateTimeFormat))
        {
            return($this->_dateTimeFormat);
        }
    }
    // }}}

    // {{{ getDecimalSeparator()
    /**
     * Returns the value of the _decimalSeparator attribute.
     *
     * @access  public
     * @return  char
     * @author  Jason Read <jason@idir.org>
     */
    function getDecimalSeparator()
    {
        if (isset($this->_decimalSeparator))
        {
            return($this->_decimalSeparator);
        }
    }
    // }}}
	
    // {{{ getDefaultLocale()
    /**
     * Static method that returns a default SRA_Locale object. The 
	 * precedence for the return SRA_Locale object is as follows:
	 * 
	 * 1) If locale parameter is valid SRA_Locale object, it is returned
	 * 2) If app is initialized, app SRA_Locale is returned
	 * 3) Otherwise, system SRA_Locale is returned
     *
	 * @param	locale : SRA_Locale - An optional SRA_Locale parameter. If 
	 * 			this parameter is a valid SRA_Locale object, it will be 
	 * 			returned.
     * @access  public static
     * @return  SRA_Locale
     * @author  Jason Read <jason@idir.org>
     */
    function & getDefaultLocale($locale = false)
    {
			if (SRA_Locale::isValid($locale))
			{
				return $locale;
			}
			else if (SRA_Controller::isAppInitialized())
			{
				return SRA_Controller::getAppLocale();
			}
			else
			{
				return SRA_Controller::getSysLocale();
			}
    }
    // }}}
		
    // {{{ getId()
    /**
     * Returns the unique identifier for this locale. this value can be used to 
		 * re-instantiate this locale using the SRA_Locale::getLocale($code) method 
		 * where the $code is the id
     *
     * @access  public
     * @return  String
     */
    function getId() {
			return $this->_language . '-' . $this->_country;
    }
    // }}}

    // {{{ getISO3Country()
    /**
     * Returns the value of the _iso3Country attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getISO3Country()
    {
        if (isset($this->_iSO3Country))
        {
            return($this->_iSO3Country);
        }
    }
    // }}}

    // {{{ getISO3Language()
    /**
     * Returns the value of the _iso3Language attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getISO3Language()
    {
        if (isset($this->_iSO3Language))
        {
            return($this->_iSO3Language);
        }
    }
    // }}}

    // {{{ getIntGroupSeparator()
    /**
     * Returns the value of the _intGroupSeparator attribute.
     *
     * @access  public
     * @return  char
     * @author  Jason Read <jason@idir.org>
     */
    function getIntGroupSeparator()
    {
        if (isset($this->_intGroupSeparator))
        {
            return($this->_intGroupSeparator);
        }
    }
    // }}}
    
    // {{{ getLabel
    /**
     * Returns the label for this locale in the format: [Language Name][/[Country Name]]
     *
     * @access  public
     * @return  String
     */
    function getLabel() {
      $str = $this->getLanguageName();
      $country = $this->getCountryName();
      $str .= $country ? '/' . $country : '';
      return $str;
    }
    // }}}

    // {{{ getLanguage()
    /**
     * Returns the value of the _language attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getLanguage()
    {
        if (isset($this->_language))
        {
            return($this->_language);
        }
    }
    // }}}
		
    // {{{ getLanguageName()
    /**
     * Returns the locale specific language name
     *
		 * @param SRA_Locale $locale - an optional locale to use when getting the country 
		 * name resource bundle. if not specified, this SRA_Locale will be used
     * @access  public
     * @return  String
     */
    function getLanguageName($locale = FALSE) {
			if (!$locale) {
				$locale =& $this;
			}
			$rb =& SRA_ResourceBundle::getBundle('iso639');
			return $rb->getString($this->_language);
    }
    // }}}
	
    // {{{ getLanguageList()
    /**
     * Static method that returns an associative array of languages and 
	 * their corresponding display values based on the SRA_Locale parameter 
	 * specified. 
     *
	 * @param	locale : SRA_Locale - The SRA_Locale object to use when determining 
	 * 			the display name to use for each language. If this parameter 
	 * 			is not specified, the default app or system local will 
	 *			be used.
     * @access  public static
     * @return  String[]
     * @author  Jason Read <jason@idir.org>
     */
    function getLanguageList($locale = false)
    {
		/**
		 * Static array maintained in the getLanguageList method as a cache 
		 * variable.  
		 * @type   String[]
		 * @access private
		 */
		static $_cachedLanguageList = array();
		
		// Get SRA_Locale
        if (!SRA_Locale::isValid($locale =& SRA_Locale::getDefaultLocale($locale)))
		{
			$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain SRA_Locale object";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
		}
		
		// Check for cached version of country list
		if (!array_key_exists($locale->getLanguage(), $_cachedLanguageList))
		{
			// Get SRA_XmlParser for SRA_Locale data
			if (!SRA_XmlParser::isValid($localeData =& SRA_Locale::_getLocaleData()))
			{
				$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Locale data";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			// Get language array
			if (SRA_Error::isError($languages =& $localeData->getData(array("languages", "language"))))
			{
				$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain language list from SRA_XmlParser";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			$languageCodes = array_keys($languages);
			if (!is_array($languageCodes))
			{
				$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain language list: '" . gettype($languageCodes) . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			sort($languageCodes);
			
			// Get country name resource bundle
			if (!SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle('iso639')))
			{
				$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain resource bundle for language names";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
			}
			
			$languageList = array();
			foreach  ($languageCodes as $languageCode)
			{
				if (SRA_Error::isError($languageList[$languageCode] = $rb->getString($languageCode)))
				{
					$msg = "SRA_Locale::getLanguageList: Failed - Could not obtain language name from iso639 file for language code '" . 
							$languageCode . "' and language '" . $locale->getLanguage() . "'";
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
				}
			}
			
			$_cachedLanguageList[$locale->getLanguage()] = $languageList;
		}
		
		return $_cachedLanguageList[$locale->getLanguage()];
		
    }
    // }}}

    // {{{ getLocale()
    /**
     * Static method used to retrieve a reference to a locale object based
     * on the parameters provided. This method should be used in place of
     * the constructor.
     *
     * @param   code : String - The country and language code combined
     * (separated by an _ symbol). This parameter may corresond with one
     * of the SRA_LOCALE_* country constants. If the parameter contains no
     * & symbols, this parameter will be considered the country code only
     * and the additional langauge parameter will also need to be
     * provided.
     * @param   language : String - The langauge code for this local. This
     * parameter only needs to be provided if the code parameter contains
     * only the country identifier.
     * @access  public static
     * @return  SRA_Locale
     * @author  Jason Read <jason@idir.org>
     */
    function getLocale($code, $language=FALSE, $defaultCountry=FALSE)
    {
      
      /**
       * Static associative array of already instantiated SRA_Locale objects
       * maintained in the SRA_Locale::getLocale method where the key will be
       * equal to both the country and language codes separated by the &
       * symbol
       */
      static $_cachedLocales = array();
      
      // Validate parameters
      if (strstr($code, "&") || strstr($code, "-") || strstr($code, "_"))
      {
        if (strstr($code, "&")) {
          $temp = explode("&", $code);
          $code = $temp[0];
          $language = $temp[1];
        }
        else if (strstr($code, "-")) {
          $temp = explode("-", $code);
          $code = $temp[1];
          $language = $temp[0];
        }
        else if (strstr($code, "_")) {
          $temp = explode("_", $code);
          $code = $temp[1];
          $language = $temp[0];
        }
      }
      else if (!$language) {
        $language = $code;
        unset($code);
      }
      if (!isset($code) || !$code) {
        if ($defaultCountry) {
          $code = $defaultCountry;
        }
        else {
          $code = SRA_Controller::getAppDefaultCountry();
        }
      }
      $code = strtoupper($code);
      
      if (!$code || !$language)
      {
        $msg = "SRA_Locale::getLocale: Failed - Invalid country '$code' or language '$language' definitions";
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
      }
      
      // Check for cached version
      if (!array_key_exists($code . "_" . $language, $_cachedLocales))
      {
        if (!SRA_Locale::isValid($_cachedLocales[$code . "_" . $language] = SRA_Locale::_getLocale($code, $language)))
        {
          $msg = "SRA_Locale::getLocale: Failed - Could not instantiate SRA_Locale using country '$code' and language '$language'";
          return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL, SRA_LOCALE_DEBUG);
        }
      }
      
      return $_cachedLocales[$code . "_" . $language];
      
    }
    // }}}
	
    // {{{ getTimeFormat()
    /**
     * Returns the value of the _timeFormat attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getTimeFormat()
    {
        if (isset($this->_timeFormat))
        {
            return($this->_timeFormat);
        }
    }
    // }}}

    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter is a SRA_Locale
     * object.
     *
     * @param   object : Object - The object to validate.
     * @access  public
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function isValid($object)
    {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_locale');
    }
    // }}}
		
    // {{{ removeDuplicateLocales()
    /**
     * Static method that removes any duplicate SRA_Locales from an array
     *
     * @param   SRA_Locale[] $locales the array of SRA_Locales to remove duplicates from
     * @access  public static
     * @return  SRA_Locale[]
     * @author  Jason Read <jason@idir.org>
     */
    function & removeDuplicateLocales(& $locales) {
			$lkeys = array();
			$keys = array_keys($locales);
			foreach ($keys as $key) {
				if (in_array($locales[$key]->getId(), $lkeys)) {
					unset($locales[$key]);
				}
				else {
					$lkeys[$key] = $locales[$key]->getId();
				}
			}
			$tmp = array();
			$keys = array_keys($locales);
			foreach ($keys as $key) {
				$tmp[] =& $locales[$key];
			}
			return $tmp;
    }
    // }}}

    // {{{ unformatNumber()
    /**
     * Unformats a number based on the locale.
     *
     * @param   number : String - The number to unformat.
     * @access  public
     * @return  float
     * @author  Jason Read <jason@idir.org>
     */
    function unformatNumber($number)
    {
        $number = str_replace($this->_intGroupSeparator, "", $number);
		$number = str_replace($this->_decimalSeparator, ".", $number);
		if (strstr($number, "."))
		{
			return (float) $number;
		}
		else
		{
			return (int) $number;
		}
    }
    // }}}
    
  // {{{ validateCountryCode
  /**
   * validates a country code
   * @param string $code the country code to validate
   * @access public
   * @return boolean
   */
  function validateCountryCode($code) {
    $parser =& SRA_XmlParser::getXmlParser(SRA_LOCALE_XML_DATA_FILE);
    return SRA_Error::isError($parser->getData(array('countries', '0', 'country', $code))) ? FALSE : TRUE;
  }
  // }}}
  
  // {{{ validateLanguageCode
  /**
   * validates a language code
   * @param string $code the language code to validate
   * @access public
   * @return boolean
   */
  function validateLanguageCode($code) {
    $parser =& SRA_XmlParser::getXmlParser(SRA_LOCALE_XML_DATA_FILE);
    return SRA_Error::isError($parser->getData(array('languages', '1', 'language', $code))) ? FALSE : TRUE;
  }
  // }}}
  
	
    // {{{ _getLocaleData()
    /**
     * Returns a reference to the _localeData attribute
     *
     * @access  public
     * @return  SRA_XmlParser
     */
    function & _getLocaleData()
    {
			/**
			 * Static variable maintained in the getLocaleData method and containing
			 * all of the SRA_Locale data defined in the locale definition xml file
			 * (see class api for more info).
			 */
			static $_localeData;
			
			// Get SRA_XmlParser for SRA_Locale data
			if (!isset($_localeData)) {
				if (!SRA_XmlParser::isValid($_localeData =& SRA_XmlParser::getXmlParser(SRA_LOCALE_XML_DATA_FILE))) {
					$msg = "SRA_Locale::_getLocaleData: Failed - XML definition file '" . SRA_LOCALE_XML_DATA_FILE . "' could not be parsed";
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
				}
			}
			
			return $_localeData;
    }
    // }}}
    
    
    
    // {{{ _getLocale()
    /**
     * instantiates a new SRA_Locale
     *
     * @param string $country country code.
     * @param string $language language code.
     * @access  private
     * @return  SRA_Locale
     */
    function & _getLocale($country, $language) {
      $country = strtolower($country);
      $language = strtolower($language);
      
      // Get SRA_XmlParser for SRA_Locale data
      if (!SRA_XmlParser::isValid($localeData =& SRA_Locale::_getLocaleData())) {
        $msg = "SRA_Locale::_getLocale: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Locale data";
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
      }
      
      // Get SRA_Locale data from xml parser
      if (SRA_Error::isError($countryConf =& $localeData->getData(array('countries', '0', 'country', $country, 'attributes'))) || 
          SRA_Error::isError($languageConf =& $localeData->getData(array('languages', '1', 'language', $language, 'attributes')))) {
        $msg = "SRA_Locale::_getLocale: Failed - Country '$country' or language '$language' parameters are not defined in XML definition file '" . 
            SRA_LOCALE_XML_DATA_FILE . "'";
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL, SRA_LOCALE_DEBUG);
      }
      
      // set to default country/language
      if (SRA_Error::isError($countryConf) || !array_key_exists('key', $countryConf) || !array_key_exists('code3', $countryConf)) {
        $country = SRA_Controller::getAppDefaultCountry();
        $countryConf = $localeData->getData(array('countries', '0', 'country', $country, 'attributes'));
      }
      if (SRA_Error::isError($languageConf) || !array_key_exists('key', $languageConf) || !array_key_exists('code3', $languageConf)) {
        $language = SRA_Controller::getAppDefaultLanguage();
        $languageConf =& $localeData->getData(array('languages', '1', 'language', $language, 'attributes'));
      }
      
      $countryConf['code'] = $countryConf['key'];
      $languageConf['code'] = $languageConf['key'];
      
      $locale = new SRA_Locale();
      $locale->_country = $country;
      $locale->_iso3Country = $countryConf['code3'];
      $currencyCode = SRA_CURRENCY_DEFAULT;
      if (array_key_exists('currency', $countryConf)) {
        $currencyCode = $countryConf['currency'];
      }
      if (!SRA_Currency::isValid($locale->_currency =& SRA_Currency::getCurrency($currencyCode))) {
        $msg = "SRA_Locale::getLocale: Failed - SRA_Currency code '$currencyCode' is not valid";
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_LOCALE_DEBUG);
      }
      if (array_key_exists('decimal-separator', $countryConf)) {
        $locale->_decimalSeparator = $countryConf['decimal-separator'];
      }
      if (array_key_exists('int-group-separator', $countryConf)) {
        $locale->_intGroupSeparator = $countryConf['int-group-separator'];
      }
      if (array_key_exists('date-time-format', $countryConf)) {
        $locale->_dateTimeFormat = $countryConf['date-time-format'];
      }
      if (array_key_exists('date-time-format', $countryConf)) {
        $locale->_dateTimeFormat = $countryConf['date-time-format'];
      }
      if (array_key_exists('date-format', $countryConf)) {
        $locale->_dateFormat = $countryConf['date-format'];
      }
      if (array_key_exists('time-format', $countryConf)) {
        $locale->_timeFormat = $countryConf['time-format'];
      }
      $locale->_language = $language;
      $locale->_iso3Language = $languageConf['code3'];
      
      return $locale;
    }
    // }}}

}
// }}}

?>
