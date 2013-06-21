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
 * the name of the properties file containing the ISO 3166 country code/names
 * @type String
 */
define('SRA_ADDRESS_FORMAT_COUNTRY_PROPERTIES', 'iso3166');

/**
 * the name of the properties file containing the address field labels
 * @type String
 */
define('SRA_ADDRESS_FORMAT_FIELD_PROPERTIES', 'address-fields');

/**
 * the name of the properties file containing the address field mappings
 * @type String
 */
define('SRA_ADDRESS_FORMAT_FIELD_MAPPING_PROPERTIES', 'address-field-mappings');

/**
 * the max columns for any address format
 * @type int
 */
define('SRA_ADDRESS_FORMAT_MAX_COLS', 3);

/**
 * the max rows for any address format
 * @type int
 */
define('SRA_ADDRESS_FORMAT_MAX_ROWS', 5);

/**
 * the location of the SRA_AddressFormat xml definition file
 * @type String
 */
define('SRA_ADDRESS_FORMAT_XML_DATA_FILE', file_exists(SRA_CONF_DIR . '/l10n/address-formats.xml') ? SRA_CONF_DIR . '/l10n/address-formats.xml' : SRA_CONF_DIR . '/l10n/address-formats-default.xml');
// }}}

// {{{ Includes
require_once('util/l10n/SRA_Country.php');
// }}}

// {{{ SRA_AddressFormat
/**
 * used to represent an country specific address format as defined in 
 * 'sierra/etc/l10n/address-formats.xml'. a single address format consists of 
 * the attributes that make up the address, the labels to use for those 
 * attributes, and the layout to use to display the address. for more 
 * information, review the address api comments below. when imported, a default 
 * instance of this class will be added to the application template using the 
 * variable name 'SRA_AddressFormat'
 * @author Jason Read <jason@idir.org>
 * @package sierra.util.l10n
 */
class SRA_AddressFormat {
  // {{{ Properties
  // private
  
  // public
  /**
   * the ISO 3166 country code that this address format pertains to
   * @type String
   */
  var $country;
  
  /**
   * the full country name for this address format as defined by the $country 
   * ISO 3166 country code
   * @type String
   */
  var $countryName;
  
  /**
   * an array of hashes defining the fields that constitute this address 
   * format. each element in this array will be a hash with the following 
   * keys. alternative, the field definitions can be retrieved using the 
   * 'getFieldsAsGrid' method. the keys in this array will be the field ids
   *   id:       the field identifier
   *   attr:     the name of the attribute to use to store this field
   *   col:      the column position within "row" for this field
   *   label:    the field label
   *   options:  a hash of key/value pairs representing the options for this 
   *             field
   *   regex:    a regular expression validation constraint for this field
   *   required: whether or not this field is required
   *   row:      the row position for this field
   * @type array
   */
  var $fields = array();
  
  /**
   * the locale used to initialize this object
   * @type SRA_Locale
   */
  var $locale;
  // }}} 

  // {{{ SRA_AddressFormat
  /**
   * constructor used to instantiate a new address format instance. DO NOT 
   * invoke this method directly. instead, use the static singleton method 
   * "getInstance" below
   * @param String $country the ISO 3166 country code that this address format 
   * pertains to
   * @param object $conf the configuration to use to initialize this address 
   * format as defined in 'sierra/etc/l10n/address-formats.xml'
   * @param SRA_Locale $locale the locale to use in initializing this object
   * @access public
   */
  function SRA_AddressFormat($country, &$conf, &$locale) {
    $this->country = $country;
    $countriesBundle =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_COUNTRY_PROPERTIES, $locale);
    $this->countryName = $countriesBundle->getString($country);
    
    $fields =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_FIELD_PROPERTIES, $locale);
    $fieldMappings =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_FIELD_MAPPING_PROPERTIES, $locale);
    $keys = array_keys($conf['field']);
    foreach($keys as $key) {
      $attrs =& $conf['field'][$key]['attributes'];
      $this->fields[$key] = array('id' => $key, 'attr' => $fieldMappings->getString($key), 
                                  'col' => $attrs['col'] ? $attrs['col'] * 1 : 1, 'label' => $fields->getString($key), 
                                  'required' => isset($attrs['required']) && $attrs['required'] == '1', 
                                  'row' => $attrs['row'] ? $attrs['row'] * 1 : 1,
                                  'showKey' => isset($attrs['show-key']) && $attrs['show-key'] == '1');
      // dynamically add country options
      if ($key == 'country') { 
        $attrs['bundle'] = SRA_ADDRESS_FORMAT_COUNTRY_PROPERTIES;
      }
      if (isset($attrs['bundle']) && SRA_ResourceBundle::isValid($optionsBundle =& SRA_ResourceBundle::getBundle(str_replace('{$country}', strtolower($country), $attrs['bundle']), $locale))) {
        $this->fields[$key]['options'] =& $optionsBundle->getData();
      }
      if (isset($attrs['regex'])) {
        $this->fields[$key]['regex'] = $attrs['regex'];
      }
    }
    $this->locale =& $locale;
  }
  // }}}
  
  // {{{ getFieldsAsGrid
  /**
   * returns the $fields attribute as a grid, or in other words, as a 
   * two-dimensional array where the first level array is the rows and the 
   * second level array is the columns. each element in the columns array will 
   * correspond to one of the $field attributes and will be positioned 
   * correctly according to the 'col' and 'row' definition for that field. the 
   * grid uses 1-based indexing, meaning the first row will resize at $grid[1]
   * @param boolean $skipCountry whether or not to skip the country field
   * @access public
   * @return  array
   */
  function &getFieldsAsGrid($skipCountry=FALSE) {
    $keys = array_keys($this->fields);
    $grid = array();
    for($i=1; $i<=SRA_ADDRESS_FORMAT_MAX_ROWS; $i++) {
      for($n=1; $n<=SRA_ADDRESS_FORMAT_MAX_COLS; $n++) {
        foreach($keys as $key) {
          if ($this->fields[$key]['row'] == $i && $this->fields[$key]['col'] == $n) {
            if (!isset($grid[$i])) { $grid[$i] = array(); }
            $grid[$i][$n] =& $this->fields[$key];
          }
        }
      }
    }
    if ($skipCountry) {
      $rkeys = array_keys($grid);
      foreach($rkeys as $rkey) {
        $ckeys = array_keys($grid[$rkey]);
        foreach($ckeys as $ckey) {
          if ($grid[$rkey][$ckey]['attr'] == 'country') { 
            unset($grid[$rkey][$ckey]);
            break;
          }
        }
      }
    }
    return $grid;
  }
  // }}}
  
  // {{{ getField
  /**
   * returns the field specified by $id
   * @param string $id the field id OR name
   * @access public
   * @return string
   */
  function &getField($id) {
    $field = NULL;
    $keys = array_keys($this->fields);
    foreach($keys as $key) {
      if ($this->fields[$key]['attr'] == $id) {
        $field = &$this->fields[$key];
        break;
      }
    }
    if (!$field) {
      foreach($keys as $key) {
        if ($key == $id) {
          $field = &$this->fields[$key];
          break;
        }
      }
    }
    return $field;
  }
  // }}}
  
  // {{{ getLabel
  /**
   * returns the label to use for the attribute or field id specified
   * @param string $id the id or name of the field to return the label for
   * @access public
   * @return string
   */
  function getLabel($id) {
    return $field =& $this->getField($id) ? $field['label'] : NULL;
  }
  // }}}
  
  // {{{ validateFieldValues
  /**
   * validates field values an returns an array of errors indexed by field id 
   * (if any validation errors  occurred), TRUE if all fields all are valid OR 
   * NULL if the $values parameter is not an array or entity. the value of each 
   * element in the return errors array will be either 'required' or 'regex' 
   * signifying why the field value is not valid (required == field is required 
   * but not specified in $values, and regex == field value failed regex 
   * validation)
   * @param mixed $values the values to validate indexed by field id 
   * (country|locality|postalCode|region|street) OR an entity instance 
   * containing those field values retrievable using the 
   * 'SRA_AddressFormat::getFieldValuesFromEntity' method
   * @access public
   * @return  mixed
   */
  function validateFieldValues(& $values) {
    $fieldValues = method_exists($values, 'isAttribute') ? SRA_AddressFormat::getFieldValuesFromEntity($values) : $values;
    if (is_array($fieldValues)) {
      $errors = array();
      $keys = array_keys($fieldValues);
      foreach($keys as $key) {
        if ($field =& $this->getField($key)) {
          if ($field['required'] && !isset($fieldValues[$key])) {
            $errors[$field['id']] = 'required';
          }
          else if (isset($fieldValues[$key]) && $field['regex'] && !preg_match($field['regex'], $fieldValues[$key])) {
            $errors[$field['id']] = 'regex';
          }
        }
      }
      return count($errors) ? $errors : TRUE;
    }
    else {
      return NULL;
    }
  }
  // }}}
  
  
  // private methods
  
  
  // static methods
  
  // {{{ getCountries
  /**
   * returns a hash of ISO 3166 identifiers/country names corresponding with the 
   * countries that are defined in SRA_ADDRESS_FORMAT_XML_DATA_FILE
   * @access public
   * @return  hash
   */
  function &getCountries() {
    static $_countries;
    
    if (!isset($_countries)) {
      $_countries = array();
      $resources =& SRA_ResourceBundle::getBundle(SRA_COUNTRY_PROPERTIES);
      if (!SRA_Error::isError($parser =& SRA_AddressFormat::_getXmlParser())) {
        $data =& $parser->getData(array('address-format'));
        $keys = array_keys($data);
        foreach($keys as $key) {
          $codes = explode(' ', $key);
          foreach($codes as $code) {
            $code = strtoupper($code);
            if (strlen($code) == 2 && $resources->getString($code) != $code) {
              $_countries[$code] = $resources->getString($code);
            }
          }
        }
        asort($_countries);
      }
      else {
        $_countries = $parser;
      }
    }
    return $_countries;
  }
  // }}}
    
	// {{{ getFieldOptions
	/**
	 * can be mapped as a global web service or invoked directly. will return the 
   * matching field options (a hash) based on the $params specified
   * @param array $params contains the following values:
   *   country: the iso 3166 code for the country
   *   field:   the id of the field
   *   locale:  the id of the locale for the address format
   *   value:   the current value of the tips field
   * @param int $limit the request limit
   * @param int $offset the request offset
   * @access public
   * @return array
	 */
	function &getFieldOptions($params, $limit=NULL, $offset=NULL) {
    if (isset($params['field']) && isset($params['country']) && SRA_AddressFormat::isValid($addressFormat = $params['locale'] ? SRA_AddressFormat::getInstanceWithLocale($params['country'], SRA_Locale::getLocale($params['locale'])) : SRA_AddressFormat::getInstance($params['country'])) && ($field =& $addressFormat->getField($params['field']))) {
      $options = $field['options'] ? $field['options'] : array();
      $keys = array_keys($options);
      if ($params['value']) {
        $lvalue = strtolower($params['value']);
        foreach($keys as $key) {
          if (strpos(strtolower($options[$key]), $lvalue) === FALSE && strtolower($key) != $lvalue) {
            unset($options[$key]);
          }
        }
      }
      if (isset($limit) || isset($offset)) { 
        $num = count($options);
        $options =& SRA_Util::applyLimitOffset($options, $limit, $offset, TRUE); 
        $options[SRA_WS_RESULT_COUNT_KEY] = $num;
      }
      return $options;
    }
    else {
      SRA_Error::logError($params, __FILE__, __LINE__);
      $msg = 'SRA_AddressFormat::getFieldOptions: Failed - $params above are not valid (country and field are required)';
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
  }
	// }}}
  
	// {{{ getFieldValuesFromEntity
	/**
	 * returns the address field values from a sierra generated entity indexed by 
   * field id: (country|locality|postalCode|region|street)
   * @param object $entity the generated entity to retrieve the fields from. 
   * this entity must contain attributes whose names correspond with the field 
   * mappings defined in 'sierra/etc/l10n/address-field-mappings.properties'
   * @param string $view an optional view to check for in retrieving those 
   * values. if specified, and the $view is valid for a field attribute, the 
   * output of that view will be returned instead of the raw value
   * @param boolean $htmlLineBreaks whether or not to add html line breaks to 
   * the 'street' attribute when it is not rendered in a view
   * @access public
   * @return array
	 */
	function &getFieldValuesFromEntity(&$entity, $view=NULL, $htmlLineBreaks=TRUE) {
    $values = array();
    if (method_exists($entity, 'isAttribute')) {
      $fieldMappings =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_FIELD_MAPPING_PROPERTIES);
      $fields = $fieldMappings->getKeys();
      foreach($fields as $field) {
        if ($entity->isAttribute($field)) {
          if ($view && $entity->hasView($view, $field)) {
            ob_start();
            $entity->renderAttribute($field, $view);
            $val = ob_get_contents();
            ob_end_clean();
          }
          else {
            $val = $entity->getAttribute($field);
            if ($htmlLineBreaks && $fieldMappings->getString($field) == 'street') { $val = str_replace("\n", '<br />', $val); }
          }
          $values[$fieldMappings->getString($field)] = $val;
        }
      }
    }
    $nl = NULL;
    return count($values) ? $values : $nl;
  }
	// }}}
  
  // {{{ getInstance
  /**
   * singleton method used to retrieve an instance of SRA_AddressFormat based 
   * on a country code. this method instantiates the address format using the 
   * locale provided through SRA_Controller::getUserLocale if available, 
   * SRA_Controller::getAppLocale otherwise
   * @param String $country the ISO 3166 country code. if not specified, 
   * SRA_COUNTRY_DEFAULT will be used
   * @access public
   * @return  SRA_AddressFormat
   */
  function getInstance($country=NULL) {
    $locale = SRA_Controller::getUserLocale() ? SRA_Controller::getUserLocale() : SRA_Controller::getAppLocale();
    return SRA_AddressFormat::getInstanceWithLocale($country, $locale);
  }
  // }}}
  
  // {{{ getInstanceWithLocale
  /**
   * singleton method used to retrieve an instance of SRA_AddressFormat based 
   * on a country code AND SRA_Locale
   * @param String $country the ISO 3166 country code. if not specified, 
   * SRA_COUNTRY_DEFAULT will be used
   * @param SRA_Locale $locale an explicit locale to use in instantiating the 
   * address format object
   * @access public
   * @return  SRA_AddressFormat
   */
  function getInstanceWithLocale($country=NULL, & $locale) {
    
    if (!SRA_Locale::isValid($locale)) {
      $msg = 'SRA_AddressFormat::getInstanceWithLocale: Failed - $locale parameter is not valid';
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    
    $countriesBundle =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_COUNTRY_PROPERTIES, $locale);
    $country = $country ? $country : $locale->getCountry();
    if (!$countriesBundle->getString($country)) { $country = SRA_COUNTRY_DEFAULT; }
    
    if (!SRA_Error::isError($parser =& SRA_AddressFormat::_getXmlParser())) {
      static $_addressFormatInstances = array();
      
      $id = $country . '-' . $locale->getId();
      if (!isset($_addressFormatInstances[$id])) {
        // initialize singleton static reference
        $country = strtolower($country);
        $data =& $parser->getData(array('address-format'));
        $keys = array_keys($data);
        foreach($keys as $key) {
          if (strpos($key, $country) !== FALSE) {
            $_addressFormatInstances[$id] = new SRA_AddressFormat(strtoupper($country), $data[$key], $locale);
            break;
          }
        }
      }
      return $_addressFormatInstances[$id];
    }
    else {
      return $parser;
    }
  }
  // }}}
  
  // {{{ isFieldLabel
  /**
   * returns true if $value is a label for $attr
   * @param string $attr the address attribute (country|locality|postalCode|region|street)
   * @param string $value the value for that attribute
   * @access public
   * @return boolean
   */
  function isFieldLabel($attr, $value) {
    $value = strtolower(trim($value));
    $fields =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_FIELD_PROPERTIES);
    $fieldMappings =& SRA_ResourceBundle::getBundle(SRA_ADDRESS_FORMAT_FIELD_MAPPING_PROPERTIES);
    $mappings =& $fieldMappings->getData();
    foreach($mappings as $key => $id) {
      if ($attr == $id && ($value == $attr || $value == $id || $value == strtolower(trim($fields->getString($key))))) { return TRUE; }
    }
    return FALSE;
  }
  // }}}
  
  // {{{ isValid
  /**
   * Static method that returns true if the object parameter is an 
   * SRA_AddressFormat object
   * @param Object $object the object to validate
   * @access public
   * @return boolean
   */
  function isValid($object) {
    return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_addressformat');
  }
  // }}}
  
  // {{{ _getXmlParser
  /**
   * returns a reference to the xml parser for SRA_ADDRESS_FORMAT_XML_DATA_FILE
   * @access private
   * @return  SRA_XmlParser
   */
  function &_getXmlParser() {
    static $_addressFormatData;
    
    // initialize the xml parser
    if (!isset($_addressFormatData)) {
      if (!SRA_XmlParser::isValid($_addressFormatData =& SRA_XmlParser::getXmlParser(SRA_ADDRESS_FORMAT_XML_DATA_FILE))) {
        $msg = 'SRA_AddressFormat::_getXmlParser: Failed - XML definition file \'' . SRA_ADDRESS_FORMAT_XML_DATA_FILE . '\' could not be parsed';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
    }
    
    return $_addressFormatData;
  }
  // }}}

}
// }}}

// assign a reference to this object to the template
if ($tpl =& SRA_Controller::getAppTemplate()) {
  $tpl->assignByRef('SRA_AddressFormat', SRA_AddressFormat::getInstance());
}
?>
