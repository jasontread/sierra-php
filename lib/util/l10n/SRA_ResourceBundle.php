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
 * SRA_ResourceBundle object debug flag
 * @type   boolean
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_DEBUG', FALSE);

/**
 * Whether or not non-translated resource bundle properties files should be 
 * automatically transferred and cached when requested. Be very careful with this 
 * option as this may seriously slow down your system if you have large 
 * non-translated properties files. If this is the case, you should convert your 
 * files using the {SRA_DIR}/bin/l10n_file_translator.php utility. 
 * (see SRA_ResourceBundle api for more info)
 * @type   boolean
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_AUTO_TRANSLATE', FALSE);

/**
 * the default relative directory for properties files
 * @type   String
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR', '/etc/l10n');

/**
 * The default name for a app resource bundle file
 * @type   String
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_DEFAULT_BUNDLE_NAME', 'app');

/**
 * The default name for a system resource bundle file
 * @type   String
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_DEFAULT_SYS_BUNDLE_NAME', file_exists(SRA_DIR . SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR . '/sierra.properties') ? 'sierra' : 'sierra-default');

/**
 * the file extension for properties files
 * @type   String
 * @access public
 */
define('SRA_RESOURCE_BUNDLE_FILE_EXT', 'properties');
// }}}

// {{{ Includes
// }}}

// {{{ SRA_ResourceBundle
/**
 * The SRA_ResourceBundle class (including api comments) was borrowed directly
 * from Java:
 * 
 * Resource bundles contain locale-specific data. When your program
 * needs a locale-specific String, your program can load it from the
 * resource bundle that is appropriate for the current user's locale. In
 * this way, you can write program code that is largely independent of the
 * user's locale isolating most, if not all, of the locale-specific
 * information in resource bundles. 
 * 
 * This allows you to write programs that can:
 * 
 * be easily localized, or translated, into different languages
 * handle multiple locales at once
 * be easily modified later to support even more locales 
 * 
 * 
 * Resource bundles will be stored in properties files. Property files
 * consist of language/string pairs such as the following:
 * mystring=Hello world.
 * 
 * In the preceeding example, the string "Hello world" was assigned to
 * the key "mystring". This string could be referenced in the future
 * through the SRA_ResourceBundle::getString method. 
 * 
 * The resource bundle files are stored using the naming convention
 * "{Bundle Name}_{2 letter ISO language code}.properties". For example:
 * MyProperties_es.properties. Alternatively, the default file will not
 * contain a language code. For example: MyProperties.properties. 
 * 
 * When a new SRA_ResourceBundle object is requested through the
 * SRA_ResourceBundle::getBundle the method will first get the language code
 * from the locale parameter and then attempt to lookup the resource file
 * for that specific language code. If that resource file does not exists,
 * the default resource file will be used.
 * 
 * Resource bundle keys are not case sensitive.
 * 
 * Resource bundle keys and values may also include imbedded php code. Any 
 * text imbedded between php:: and ::php will be sent through the php parser 
 * and the return value added to that key or value.
 * 
 * Once initialized, SRA_ResourceBundle data is cached. It will only be refreshed 
 * if/when the original properties file is modified.
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.util.l10n
 */
class SRA_ResourceBundle {
    // {{{ Properties
    /**
     * The name of the bundle without the language or .properties extensions. 
     * @type   String
     * @access private
     */
    var $_bundleName;
    /**
     * An associative array of strings assigned to the instantiated
     * SRA_ResourceBundle object. The key in this array will be equal to the
     * resource identifier and the value, the locale specific string
     * assigned to that identifier.
     * @type   String[]
     * @access private
     */
    var $_data = array();
    /**
     * The SRA_Locale object to which the SRA_ResourceBundle pertains.
     * @type   SRA_Locale
     * @access private
     */
    var $_locale;
    // }}} 

    // {{{ SRA_ResourceBundle()
    /**
     * SRA_ResourceBundle object constructor. This method is private and should
     * never be called directly. Instead, new SRA_ResourceBundle objects
     * should be requested through the SRA_ResourceBundle::getBundle static
     * method. This method instantiates a new SRA_ResourceBundle object based
     * on the properties file specified.
     *
     * @param   file : String - The name of the file to instantiate the
     * resource bundle object for. An SRA_Error will be generated if the file
     * does not exists or is invalid.
     * @param   locale : SRA_Locale - The locale to which the SRA_ResourceBundle
     * pertains.
     * @access  private
     * @return  
     * @author  Jason Read <jason@idir.org>
     */
    function SRA_ResourceBundle($file, & $locale)
    {
		// Validate parameters
        if (!file_exists($file))
		{
			$msg = "SRA_ResourceBundle::SRA_ResourceBundle: Failed - file specified '$file' is not valid.";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
			return;
		}
		if (!SRA_Locale::isValid($locale))
		{
			$msg = "SRA_ResourceBundle::SRA_ResourceBundle: Failed - locale specified '" . gettype($locale) . "' is not valid.";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
			return;
		}
		
		// Process properties file
		
		// Auto-translation enabled
		if (SRA_RESOURCE_BUNDLE_AUTO_TRANSLATE)
		{
			$this->_data =& SRA_File::propertiesFileToArray($file, 0, $locale->getLanguage());
		}
		// No auto-translation
		else
		{
			$this->_data =& SRA_File::propertiesFileToArray($file, 0);
		}
		if (SRA_Error::isError($this->_data))
		{
			$msg = "SRA_ResourceBundle::SRA_ResourceBundle: Failed - file specified '$file' could not be parsed.";
			$this->err = SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
			return;
		}
		
		$this->_locale =& $locale;
		$this->_bundleName = str_replace(SRA_RESOURCE_BUNDLE_FILE_EXT, "", basename($file));
		if (substr($this->_bundleName, strlen($this->_bundleName) - 3, 1) == "_")
		{
			$this->_bundleName = substr($this->_bundleName, 0, strlen($this->_bundleName) - 3);
		}
		
    }
    // }}}
    
    // {{{ findLocaleFile()
    /**
     * Static method used to find a locale specific file
		 * 
		 * The search order is as follows:
		 * 	bundle + '_' + locale1.language + '_' . locale1.country . '[file extension]'
		 * 	bundle + '_' + locale1.language . '[file extension]'
		 * 	bundle + '_' + locale2.language + '_' . locale2.country . '[file extension]'
		 * 	bundle + '_' + locale2.language . '[file extension]'
		 * 	bundle + '_' + localeN.language + '_' . localeN.country . '[file extension]'
		 * 	bundle + '_' + localeN.language . '[file extension]'
		 *  bundle . '[file extension]'
     *
     * @param   baseFile : String - The name of the file minus extension. this method 
		 * will use SRA_File::getRelativePath($dir = FALSE, $file = $bundle, $prefix = SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR)
		 * to locate the first match of the properties file based on the search 
		 * algorithm described above
     * 
     * @param extension : String - the file extension
		 * 
     * @param   locales : (SRA_Locale[] | SRA_Locale) - The SRA_Locale object order to use 
		 * for the resource bundle. If this parameter is not specified, the app 
		 * SRA_Locale will be used if a app is initialized. If a app is not
     * initialized and this parameter is not specified, the system SRA_Locale
     * will be used.
		 *
		 * @param boolean $sysSpecific whether or not the bundle should be system 
		 * specific. if TRUE, the bundle search method will be changed to SRA_File::getSysRelativePath
     * @access  public
     * @return  an array where the full path name to the bundle is indexed as 
     * 'bundle' and the selected locale is indexed as 'locale', or FALSE if the  
     * bundle does not exist
     * @author  Jason Read <jason@idir.org>
     */
    function findLocaleFile($baseFile, $extension=SRA_RESOURCE_BUNDLE_FILE_EXT, $locales=FALSE, $sysSpecific = FALSE) {
			if ($locales && !is_array($locales)) {
				$locales = array($locales);
			}
			
			// Validate parameters
			if (!$baseFile) {
				$msg = "SRA_ResourceBundle::findLocaleFile: Failed - baseFile parameter was not specified";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
			}
			
			// Determine SRA_Locale
			if (!$locales) {
				$locales =& SRA_Controller::getUserLocales();
			}
			$keys = array_keys($locales);
			$extensionPreference = array();
      $defaultLanguage = SRA_Controller::getAppDefaultLanguage();
      $defaultCountry = SRA_Controller::getAppDefaultCountry();
			foreach ($keys as $key) {
				if (!SRA_Locale::isValid($locales[$key])) {
					$msg = "SRA_ResourceBundle::getBundle: Failed - locale is not valid: '" . gettype($locales[$key]) . "'";
          SRA_Error::logError($locales[$key], __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
					return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
				}
				else {
					$extensionPreference[$baseFile . '_' . strtolower($locales[$key]->getLanguage()) . '_' . strtolower($locales[$key]->getCountry())] =& $locales[$key];
					$extensionPreference[$baseFile . '_' . strtolower($locales[$key]->getLanguage())] =& $locales[$key];
          if (!isset($extensionPreference[$baseFile]) && $locales[$key]->getLanguage() == $defaultLanguage && $locales[$key]->getCountry() == $defaultCountry) {
            $extensionPreference[$baseFile] =& $locales[$key];
          }
          else if (!isset($extensionPreference[$baseFile]) && $locales[$key]->getLanguage() == $defaultLanguage) {
            $extensionPreference[$baseFile] =& $locales[$key];
          }
				}
			}
			
      if (!isset($extensionPreference[$baseFile])) {
        $extensionPreference[$baseFile] =& SRA_Controller::getAppLocale();
      }
			$bundleFile = FALSE;
			$keys = array_keys($extensionPreference);
			foreach ($keys as $key) {
				// Determine properties file to use
				if ((!$sysSpecific && ($bundleFile = SRA_File::getRelativePath(FALSE, $key . '.' . $extension, SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR))) || 
						($sysSpecific && ($bundleFile = SRA_File::getSysRelativePath(FALSE, $key . '.' . $extension, SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR)))) {
					$locale =& $extensionPreference[$key];
					break;
				}
			}
			
			return $bundleFile ? array('bundle' => $bundleFile, 'locale' => $locale) : FALSE;
		
    }
    // }}}

  // {{{ getBundle
  /**
   * Static method used to retrieve an SRA_ResourceBundle object. This is the
   * only method that should be called in order to instantiate and use a
   * SRA_ResourceBundle object.
   * 
   * The search order is as follows:
   * 	bundle + '_' + locale1.language + '_' . locale1.country
   * 	bundle + '_' + locale1.language
   * 	bundle + '_' + locale2.language + '_' . locale2.country
   * 	bundle + '_' + locale2.language
   * 	bundle + '_' + localeN.language + '_' . localeN.country
   * 	bundle + '_' + localeN.language
   *  bundle
   * @param string $bundle The name of the resource bundle. this method will use 
   * SRA_File::getRelativePath($dir = FALSE, $file = $bundle, 
   * $prefix = SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR) to locate the first 
   * match of the properties file based on the search algorithm described above
   * @param mixed $locales the SRA_Locale object order to use for the resource 
   * bundle. If this parameter is not specified, the app SRA_Locale will be used 
   * if a app is initialized. If a app is not initialized and this parameter is 
   * not specified, the system SRA_Locale will be used
   * @param boolean $sysSpecific whether or not the bundle should be system 
   * specific. if TRUE, the bundle search method will be changed to 
   * SRA_File::getSysRelativePath
   * @access  public
   * @return  SRA_ResourceBundle
   */
  function &getBundle($bundle, $locales=FALSE, $sysSpecific = FALSE) {
    static $_sraCachedResourceBundles = array();
    
    $localeKey = '';
    if ($locales) {
      if (is_array($locales)) {
        $keys = array_keys($locales);
        foreach($keys as $key) {
          $localeKey .= $locales[$key]->getId();
        }
      }
      else {
        $localeKey = $locales->getId();
      }
    }
    $bundleKey = SRA_Controller::getCurrentAppId() . '_' . $bundle . '_' . $localeKey . '_' . $sysSpecific;
    
    if (!isset($_sraCachedResourceBundles[$bundleKey])) {
      if ((file_exists($bundle) && !is_dir($bundle)) || (($relFile = SRA_File::getRelativePath(NULL, $bundle)) && !is_dir($relFile))) {
        if (!SRA_ResourceBundle::isValid($_sraCachedResourceBundles[$bundleKey] = new SRA_ResourceBundle(file_exists($bundle) && !is_dir($bundle) ? $bundle : $relFile, SRA_Controller::getUserLocale()))) {
          $msg = 'SRA_ResourceBundle::getBundle: Failed - SRA_ResourceBundle could not be instantiated using bundle file "' . $bundle . '"';
          $_sraCachedResourceBundles[$bundleKey] =& SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
        }
      }
      else {
        $bundleData = SRA_ResourceBundle::findLocaleFile($bundle, SRA_RESOURCE_BUNDLE_FILE_EXT, $locales, $sysSpecific);
        
        // bundle not found
        if (!$bundleData) {
          $msg = "SRA_ResourceBundle::getBundle: Failed - Resource bundle specified: ${baseDir}/${bundle} does not exist";
          $_sraCachedResourceBundles[$bundleKey] =& SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
        }
        
        if (!SRA_ResourceBundle::isValid($_sraCachedResourceBundles[$bundleKey] = new SRA_ResourceBundle($bundleData['bundle'], $bundleData['locale']))) {
          $msg = 'SRA_ResourceBundle::getBundle: Failed - SRA_ResourceBundle could not be instantiated using bundle file "' . $bundleData['bundle'] . '"';
          $_sraCachedResourceBundles[$bundleKey] =& SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_RESOURCE_BUNDLE_DEBUG);
        }
      }
    }
    return $_sraCachedResourceBundles[$bundleKey];
  }
  // }}}
	
    // {{{ getBundleName()
    /**
     * Returns the value of the _bundleName attribute.
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getBundleName()
    {
        if (isset($this->_bundleName))
        {
            return $this->_bundleName;
        }
    }
    // }}}
	
    // {{{ getData()
    /**
     * Returns a reference to the _data attribute.
     *
     * @access  public
     * @return  String[]
     * @author  Jason Read <jason@idir.org>
     */
    function &getData()
    {
        if (isset($this->_data))
        {
            return $this->_data;
        }
    }
    // }}}

    // {{{ getKeys()
    /**
     * Returns the array keys of the _data attribute (all of the resouce
     * identifiers relevent to the SRA_ResourceBundle object).
     *
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getKeys()
    {
        return array_keys($this->_data);
    }
    // }}}
		
    // {{{ containsKey()
    /**
     * Returns TRUE if the key specified exists in this resource bundle
     *
		 * @param  string $key the key to validate
     * @access  public
     * @return  boolean
     */
    function containsKey($key) {
        return isset($this->_data[$key]);
    }
    // }}}

    // {{{ getString()
    /**
     * Returns the resource within the SRA_ResourceBundle _data attribute
     * associated with the key specified. Returns $key if the
     * key specified is not valid. Resource bundle keys are not case sensitive. 
     * If the constant SRA_CONVERT_OUTPUT_TO_HTML is defined and TRUE, the 
     * return value from invoking this method will be escaped for special 
     * html characters including the following:
     *  '&' (ampersand) becomes '&amp;'
     *  '"' (double quote) becomes '&quot;'
     *  ''' (single quote) becomes '&#039;' 
     *  '<' (less than) becomes '&lt;'
     *  '>' (greater than) becomes '&gt;'
     * @param   key : String - The key to return te resource for. If this
     * key does not exist in the _data attribute, an SRA_Error will be
     * generated.
		 * @param array $params associative array of key/value substitutions to use. 
		 * these values can be imbedded into a resource string using the following 
		 * format: some text {$key} other text
		 * where 'key' is the key in the associative array
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getString($key, $params=NULL) {
			$baseKey = $key;
      if (array_key_exists($key, $this->_data)) {
				if (!$params) {
					$str = $this->_data[$key];
				}
				else {
					$data = $this->_data[$key];
					foreach ($params as $key => $val) {
						$data = str_replace('{$' . $key . '}',$val,$data);
					}
					$str = $data;
				}
			}
			else {
				$str = $baseKey;
			}
      return defined('SRA_CONVERT_OUTPUT_TO_HTML') && SRA_CONVERT_OUTPUT_TO_HTML ? htmlspecialchars($str) : $str;
    }
    // }}}
		
    // {{{ merge()
    /**
     * Returns a SRA_ResourceBundle representing the merged values from both $rb1 
		 * and $rb2 where values in $rb1 take precedence over those in $rb2
     *
     * @access  public
     * @return  SRA_ResourceBundle
     * @author  Jason Read <jason@idir.org>
     */
    function &merge(& $rb1, & $rb2)
    {
				$rb = $rb1;
				if (SRA_ResourceBundle::isValid($rb2)) {
					$rb->_data = array_merge($rb2->_data, $rb1->_data);
				}
				return $rb;
    }
    // }}}
    
    // {{{ toJson
    /**
     * Returns the code necessary to instantiate thie resource bundle as an 
     * associative javascript array
     *
     * @access  public
     * @return  string
     */
    function toJson() {
      $code = '{ ';
      $keys = array_keys($this->_data);
      $started = FALSE;
      foreach($keys as $key) {
        $code .= $started ? ', ' : '';
        $code .= '"' . str_replace('"', '\"', $key) . '" : "' . str_replace("\n", '\\n', str_replace('"', '\"', $this->getString($key))) . '"';
        $started = TRUE;
      }
      $code .= ' }';
      return $code;
    }
    // }}}

    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter is a
     * SRA_ResourceBundle object.
     *
     * @param   object : Object - The object to validate.
     * @access  public
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function isValid($object)
    {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_resourcebundle');
    }
    // }}}

}
// }}}

?>
