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
 * SRA_Currency object debug flag
 * @type   boolean
 * @access public
 */
define('SRA_CURRENCY_DEBUG', FALSE);
/**
 * Constant representing the Canadian currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_CANADA', "cad");
/**
 * Constant representing the Euro currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_EURO', "xeu");
/**
 * Constant representing the French currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_FRANCE', "xeu");
/**
 * Constant representing the German currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_GERMANY', "xeu");
/**
 * Constant representing the Spanish currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_SPAIN', "esp");
/**
 * Constant representing the English/UK currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_UK', "gbp");
/**
 * Constant representing the US currency code.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_US', "usd");
/**
 * The system currency. All persistent values will be stored using this
 * currency. A system may only utilize 1 sys currency.
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_DEFAULT', SRA_CURRENCY_US);
/**
 * The location of the SRA_Currency xml definition file. 
 * @type   String
 * @access public
 */
define('SRA_CURRENCY_XML_DATA_FILE', file_exists(SRA_CONF_DIR . '/l10n/currencies.xml') ? SRA_CONF_DIR . '/l10n/currencies.xml' : SRA_CONF_DIR . '/l10n/currencies-default.xml');
// }}}

// {{{ Includes
// }}}

// {{{ SRA_Currency
/**
 * Class used to manage currencies and currency conversion. This is part of
 * the localization classes such as SRA_Locale and SRA_ResourceBundle. The
 * currency data will be stored in the file SRA_CURRENCY_XML_DATA_FILE. 
 * This file contains a root tag "currencies" and 1..* child 'currency' 
 * tags. Each 'currency' tag contains the following elements:
 * 
 * > attribute key = "code" : Used to convert to an associative
 *   array based on currency code
 * > code (String) : The currency code. This will be the unique
 *   identifier for the currency
 * > decimal_precision (int) : The decimal precision to use for when conversions 
 *   result in floating point values.
 * > conversion (float) : The conversion factor for
 *   converting this currency to the currency represented by the
 *   SRA_CURRENCY_DEFAULT constant.
 * > symbol (String) : The symbol for the currency
 * > symbol_front (boolean : true) : Whether or not the symbol should
 *   appear at the front or the rear of formatted currency values
 * 
 * 
 * Additionally, currency names are stored in resource bundles as
 * "{SRA_DIR} /etc/l10n/iso4217.properties".
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.util.l10n
 */
class SRA_Currency {
    // {{{ Properties
    /**
     * The unique 3 digit currency identifier.
     * @type   String
     * @access private
     */
    var $_code;
    /**
     * The decimal precision to use for when conversions result in floating 
	 * point values.
     * @type   int
     * @access private
     */
    var $_decimalPrecision = 2;
    /**
     * The currency symbol
     * @type   String
     * @access private
     */
    var $_symbol;
    /**
     * Whether or not the symbol should appear at the front or the rear of
     * formatted currency values
     * @type   boolean
     * @access private
     */
    var $_symbolFront= true;
    /**
     * The numeric factor used to convert this currency to the sys currency
     * (as defined by the SRA_CURRENCY_DEFAULT constant).
     * @type   float
     * @access private
     */
    var $_sysConversionFactor;
    // }}} 

    // {{{ SRA_Currency()
    /**
     * The SRA_Currency constructor. This method is private as all SRA_Currency
     * objects should be accessed via the getCurrency method.
     *
     * @param   code : String - The unique 3 digit currency code. If
     * this value is not defined, an SRA_Error will be returned.
     * @access  private
     * @return  
     * @author  Jason Read <jason@idir.org>
     */
    function SRA_Currency($code)
    {
			// Validate parameter
			if (!is_scalar($code)) {
				$msg = "SRA_Currency::SRA_Currency: Failed - Invalid code parameter '" .gettype($code) . "'";
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				return;
			}
			
			// Get SRA_XmlParser for SRA_Currency data
			if (!SRA_XmlParser::isValid($currencyData =& SRA_Currency::_getCurrencyData()))
			{
				$msg = "SRA_Currency::SRA_Currency: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Currency data";
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				return;
			}
			
			// Get SRA_Locale data from xml parser
			if (SRA_Error::isError($currencyConf =& $currencyData->getData(array('currency', $code, 'attributes')))) {
        $code = SRA_Controller::getAppDefaultCurrency();
				$currencyConf =& $currencyData->getData(array('currency', $code, 'attributes'));
			}
			
			// Load instance variabled
			if (!array_key_exists('symbol', $currencyConf) || !array_key_exists('conversion', $currencyConf))
			{
				$msg = "SRA_Currency::SRA_Currency: Failed - Code '$code' parameters are not fully defined in XML definition file '" . 
						SRA_CURRENCY_XML_DATA_FILE . "'";
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				return;
			}
	
			// SRA_Currency instantiated successfully
			$this->_code = $code;
			$this->_symbol = $currencyConf['symbol'];
			$this->_sysConversionFactor = $currencyConf['conversion'];
			if (array_key_exists('symbol-front', $currencyConf))
			{
				$this->_symbolFront = $currencyConf['symbol-front'];
			}
			if (array_key_exists('precision', $currencyConf))
			{
				$this->_decimalPrecision = $currencyConf['precision'];
			}
		
    }
    // }}}
	
    // {{{ convert()
    /**
     * Method used to convert a currency value from one currency to another. 
     *
	 * @param	value : float - The value to convert. 
	 * @param	currency : SRA_Currency - The SRA_Currency to which the value should be 
	 * 			converted. 
     * @access  public
     * @return  float
     * @author  Jason Read <jason@idir.org>
     */
    function convert($value, & $currency)
    {
		// Validate parameters
        if (!is_numeric($value) || $value < 0)
		{
			$msg = "SRA_Currency::convert: Failed - Invalid value parameter '$value'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		if (!SRA_Currency::isValid($currency))
		{
			$msg = "SRA_Currency::convert: Failed - Invalid currency parameter '" . gettype($currency) . "'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		if (!is_numeric($sysValue = $this->convertCurrencyValue($value)))
		{
			$msg = "SRA_Currency::convert: Failed - Value parameter '$value' could not be converted to a sys value";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		return $currency->convertSysCurrencyValue($sysValue);
		
    }
    // }}}
	
    // {{{ convertCurrencyValue()
    /**
     * This method converts a value from the currency represented by the
     * SRA_Currency object in which the method is called, to the system
     * currency (as defined by the SRA_CURRENCY_DEFAULT constant).
     *
     * @param   value : float - The value to convert.
     * @access  public
     * @return  float
     * @author  Jason Read <jason@idir.org>
     */
    function convertCurrencyValue($value)
    {
		// Validate value parameter
        if (!is_numeric($value) || $value < 0)
		{
			$msg = "SRA_Currency::convertCurrencyValue: Failed - Invalid value parameter '$value'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Validate _sysConversionFactor attribute
		if (!isset($this->_sysConversionFactor) || !is_numeric($this->_sysConversionFactor) || 
			$this->_sysConversionFactor < 0)
		{
			$msg = "SRA_Currency::convertCurrencyValue: Failed - Invalid _sysConversionFactor attribute '" . 
					$this->_sysConversionFactor . "'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Retrieve Sys SRA_Currency reference
		if (!SRA_Currency::isValid($sysCurrency =& SRA_Currency::getCurrency()))
		{
			$msg = "SRA_Currency::convertCurrencyValue: Failed - Could not obtain sys currency reference";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		$precision = $sysCurrency->getDecimalPrecision();
		
		// Perform conversion
		return round($value * $this->_sysConversionFactor, $precision);
		
    }
    // }}}
	
    // {{{ convertSysCurrencyValue()
    /**
     * This method converts a value from the currency represented by the
     * sys currency (as defined by the SRA_CURRENCY_DEFAULT constant), to the 
	 * currency represented by the SRA_Currency object in which the method is 
	 * called.
     *
     * @param   value : float - The sys currency value to convert.
     * @access  public
     * @return  float
     * @author  Jason Read <jason@idir.org>
     */
    function convertSysCurrencyValue($value)
    {
		// Validate value parameter
        if (!is_numeric($value) || $value < 0)
		{
			$msg = "SRA_Currency::convertSysCurrencyValue: Failed - Invalid value parameter '$value'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Validate _sysConversionFactor attribute
		if (!isset($this->_sysConversionFactor) || !is_numeric($this->_sysConversionFactor) || 
			$this->_sysConversionFactor < 0)
		{
			$msg = "SRA_Currency::convertSysCurrencyValue: Failed - Invalid _sysConversionFactor attribute '" . 
					$this->_sysConversionFactor . "'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		$precision = $this->_decimalPrecision;
		
		// Perform conversion
		return round($value * (1/$this->_sysConversionFactor), $precision);
		
    }
    // }}}

    // {{{ getCode()
    /**
     * Returns the value of the _code attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getCode()
    {
        if (isset($this->_code))
        {
            return($this->_code);
        }
    }
    // }}}
	
    // {{{ getCurrencies()
    /**
     * Static method used to return an array of all of the Currencies 
	 * defined within the system currency etc xml file. 
     *
     * @access  public
     * @return  SRA_Currency[]
     * @author  Jason Read <jason@idir.org>
     */
    function & getCurrencies()
    {
		/**
		 * Static array maintained in the getCurrencies method as a cache 
		 * variable. 
		 * @type   SRA_Currency[]
		 * @access private
		 */
		static $_cachedCurrencies;
		
		if (!isset($_cachedCurrencies))
		{
			// Get SRA_XmlParser for SRA_Currency data
			if (!SRA_XmlParser::isValid($currencyData =& SRA_Currency::_getCurrencyData()))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Currency data";
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				return;
			}
			
			// Get currencies array
			if (SRA_Error::isError($currencies =& $localeData->getData(array('currency'))))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain currency list from SRA_XmlParser";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
			
			$currencyCodes = array_keys($currencies);
			if (!is_array($currencyCodes))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain currency list: '" . gettype($currencyCodes) . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
			
			$_cachedCurrencies = array();
			foreach  ($currencyCodes as $currencyCode)
			{
				if (SRA_Error::isError($_cachedCurrencies[] = SRA_Currency::getCurrency($currencyCode)))
				{
					$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain SRA_Currency object for currency '" . $currencyCode;
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				}
			}
		}
		
		return $_cachedCurrencies;
    }
    // }}}

    // {{{ getCurrency()
    /**
     * Static method used to access a SRA_Currency object. This method
     * maintains a cache array of previously instantiated SRA_Currency objects
     * so that each currency type is only instantiated once. This method
     * should always be called in place of the private SRA_Currency
     * constructor.
     *
     * @param   code : String - The unique 3 digit currency code. If
     * this value is not defined, an SRA_Error will be returned. The default
     * currency is the system currency (SRA_CURRENCY_DEFAULT).
     * @access  public
     * @return  SRA_Currency
     */
    function getCurrency($code=FALSE) {
      /**
       * Static array of cached currency object maintained in the
       * SRA_Currency::getCurrency static method. This will be an associative
       * array where the key in the array is equal to the 3 digit currency
       * code.
       */
      static $_cachedCurrency = array();
      
      $code = !$code ? SRA_Controller::getAppDefaultCurrency() : $code;
      
      // Validate parameter
      if (!is_scalar($code)) {
        $msg = "SRA_Currency::getCurrency: Failed - Invalid currency parameter '" .gettype($code) . "'";
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
      }
      
      // Check for cached version of SRA_Currency object
      if (!array_key_exists($code, $_cachedCurrency)) {
        if (!SRA_Currency::isValid($_cachedCurrency[$code] = new SRA_Currency($code))) {
          $msg = "SRA_Currency::getCurrency: Failed - SRA_Currency object could not be instantiated for code '$code'";
          return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
        }
      }
      
      return $_cachedCurrency[$code];
		
    }
    // }}}
	
    // {{{ getCurrencyList()
    /**
     * Static method that returns an associative array of currencies and their 
	 * corresponding display values based on the SRA_Locale parameter specified. 
     *
	 * @param	locale : SRA_Locale - The SRA_Locale object to use when determining 
	 * 			the display name to use for each currency. If this parameter 
	 * 			is not specified, the default app or system local will 
	 *			be used.
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getCurrencyList($locale = false)
    {
		/**
		 * Static array maintained in the getCurrencyList method as a cache 
		 * variable.  
		 * @type   String[]
		 * @access private
		 */
		static $_cachedCurrencyList = array();
		
		// Get SRA_Locale
        if (!SRA_Locale::isValid($locale =& SRA_Locale::getDefaultLocale($locale)))
		{
			$msg = "SRA_Currency::getCurrencyList: Failed - Could not obtain SRA_Locale object";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		if (!array_key_exists($locale->getLanguage(), $_cachedCurrencyList))
		{
			// Get SRA_XmlParser for SRA_Currency data
			if (!SRA_XmlParser::isValid($currencyData =& SRA_Currency::_getCurrencyData()))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain reference to SRA_XmlParser object for SRA_Currency data";
				$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				return;
			}
			
			// Get currencies array
			if (SRA_Error::isError($currencies =& $currencyData->getData(array('currency'))))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain currency list from SRA_XmlParser";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
			
			$currencyCodes = array_keys($currencies);
			if (!is_array($currencyCodes))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain currency list: '" . gettype($currencyCodes) . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
			sort($currencyCodes);
			
			// Get currency name resource bundle
			if (!SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle('iso4217')))
			{
				$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain resource bundle for currency names";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
			
			$currencyList = array();
			foreach  ($currencyCodes as $currencyCode)
			{
				if (SRA_Error::isError($currencyList[$currencyCode] = $rb->getString($currencyCode)))
				{
					$msg = "SRA_Currency::getCurrencies: Failed - Could not obtain currency name from iso4217 file for currency '" . 
							$currencyCode . "' and language '" . $locale->getLanguage() . "'";
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
				}
			}
			
			$_cachedCurrencyList[$locale->getLanguage()] = $currencyList;
		}
		
		return $_cachedCurrencyList[$locale->getLanguage()];
    }
    // }}}
	
    // {{{ getDecimalPrecision()
    /**
     * Returns the value of the _decimalPrecision attribute.
     *
     * @access  public
     * @return  int
     * @author  Jason Read <jason@idir.org>
     */
    function getDecimalPrecision()
    {
        if (isset($this->_decimalPrecision))
        {
            return($this->_decimalPrecision);
        }
    }
    // }}}

    // {{{ getDisplayName()
    /**
     * Returns the display name for the currency as defined in the
     * "{SRA_DIR}/etc/l10n/iso4217.properties" file.
     *
     * @param   locale : SRA_Locale - The locale for which the display name
     * should be returned. If this parameter is not specified, the default
     * properties file will be used.
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getDisplayName($locale=false)
    {
		// Get SRA_Locale
        if (!SRA_Locale::isValid($locale =& SRA_Locale::getDefaultLocale($locale)))
		{
			$msg = "SRA_Currency::getDisplayName: Failed - Could not obtain SRA_Locale object";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Validate _code attribute
		if (!isset($this->_code))
		{
			$msg = "SRA_Currency::getDisplayName: Failed - _code attribute is not set";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
				
		// Get currency name resource bundle
		if (!SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle('iso4217')))
		{
			$msg = "SRA_Currency::getDisplayName: Failed - Could not obtain resource bundle for currency names";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
			
		// Get currency name
		if (SRA_Error::isError($displayName = $rb->getString($this->_code)))
		{
			$msg = "SRA_Currency::getDisplayName: Failed - Could not obtain country name from iso4217 file for currency code '" . 
					$this->_code . "'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		return $displayName;
    }
    // }}}

    // {{{ getFormattedValue()
    /**
     * Returns a formatted currency string based on the value and SRA_Locale
     * provided. This value includes the currency symbol.
     *
     * @param   value : float - The currency value.
     * @param   locale : SRA_Locale - The locale whose numeric formatting rules
     * should be used to create the display value. If this parameter is not 
	 * specified, the default properties file will be used.
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getFormattedValue($value, $locale = false)
    {
		// Get SRA_Locale
        if (!SRA_Locale::isValid($locale =& SRA_Locale::getDefaultLocale($locale)))
		{
			$msg = "SRA_Currency::getFormattedValue: Failed - Could not obtain SRA_Locale object";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Validate value parameter
		$value = round($value, $this->_decimalPrecision);
		if (SRA_Error::isError($formattedValue = $locale->formatNumber($value)))
		{
			$msg = "SRA_Currency::getFormattedValue: Failed - Could not format value parameter '$value'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
		}
		
		// Return formatted value
		if ($this->_symbolFront)
		{
			return $this->_symbol . $formattedValue;
		}
		else
		{
			return $formattedValue . $this->_symbol;
		}
		
    }
    // }}}

    // {{{ getSymbol()
    /**
     * Returns the value of the _symbol attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getSymbol()
    {
        if (isset($this->_symbol))
        {
            return($this->_symbol);
        }
    }
    // }}}

    // {{{ getSysConversionFactor()
    /**
     * Returns the value of the _sysConversionFactor attribute.
     *
     * @access  public
     * @return  float
     * @author  Jason Read <jason@idir.org>
     */
    function getSysConversionFactor()
    {
        if (isset($this->_sysConversionFactor))
        {
            return($this->_sysConversionFactor);
        }
    }
    // }}}

    // {{{ isSymbolFront()
    /**
     * Returns the value of the _symbolFront attribute.
     *
     * @access  public
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function isSymbolFront()
    {
        if (isset($this->_symbolFront))
        {
            return($this->_symbolFront);
        }
    }
    // }}}
	
    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter is a SRA_Currency
     * object.
     *
     * @param   object : Object - The object to validate.
     * @access  public
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function isValid($object)
    {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_currency');
    }
    // }}}
    
  // {{{ validateCode
  /**
   * validates a currency code
   * @param string $code the currency code to validate
   * @access public
   * @return boolean
   */
  function validateCode($code) {
    $parser =& SRA_XmlParser::getXmlParser(SRA_CURRENCY_XML_DATA_FILE);
    return SRA_Error::isError($parser->getData(array('currency', $code))) ? FALSE : TRUE;
  }
  // }}}
	
    // {{{ _getCurrencyData()
    /**
     * Static method that returns a reference to the _currencyData attribute
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function & _getCurrencyData()
    {
		/**
		 * Static SRA_XmlParser object maintained in the getCurrencyData method and 
		 * used to retrieve the currency data. 
		 * @type   SRA_XmlParser
		 * @access private
		 */
		static $_currencyData;
		
		if (!isset($_currencyData))
		{
			if (!SRA_XmlParser::isValid($_currencyData =& SRA_XmlParser::getXmlParser(SRA_CURRENCY_XML_DATA_FILE)))
			{
				$msg = "SRA_Currency::_getCurrencyData: Failed - XML definition file '" . SRA_CURRENCY_XML_DATA_FILE . "' could not be parsed";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CURRENCY_DEBUG);
			}
		}
		
		return $_currencyData;
		
    }
    // }}}

}
// }}}

?>
