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
 * This constant specifies whether or not the system should run in debug mode.
 * This mode enables any debug messages to be sent to the active console.
 * @type   boolean
 */
define('SRA_CONTROLLER_DEBUG', FALSE);

/**
 * The base directory of the system library (does not include trailing /).
 * This constant is used by many of the class files to import dependent class files.
 * @type string
 */
define('SRA_LIB_DIR', dirname(dirname(realpath(__FILE__))));

/**
 * The base directory of the system.
 * @type string
 */
define('SRA_DIR', dirname(SRA_LIB_DIR));

/**
 * the default name of the applications directory
 * @type string
 */
define('SRA_APP_DIR_NAME', 'app');

/**
 * the name of the sierra and app bin directory
 * @type string
 */
define('SRA_BIN_DIR_NAME', 'bin');

/**
 * The base name of the sierra and app configuration directory
 * @type string
 */
define('SRA_CONF_DIR_NAME', 'etc');

/**
 * The base name of the sierra and app configuration directory
 * @type string
 */
define('SRA_CONF_DIR', SRA_DIR . '/' . SRA_CONF_DIR_NAME);

/**
 * Defines the app-conf attribute name used to specify whether or not to 
 * use the SRA_EntityModeler
 */
define('SRA_ENTITY_MODELER_CONFIG_KEY', 'use-entity-model');

/**
 * the file name prefix used to store license acceptance files (see 
 * app-config.dtd "license" attribute documentation for more information)
 * @type string
 */
define('SRA_LICENSE_PREFIX', '.sra-license-');

/**
 * the default license acceptance field name
 * @type string
 */
define('SRA_LICENSE_VAR', 'licenseAccepted');

/**
 * the time that this file was initially included into the current running 
 * PHP process
 * @type float
 */
define('SRA_START_TIME', microtime(TRUE));

/**
 * The base name of the sierra and app temp directory
 * @type string
 */
define('SRA_TMP_DIR_NAME', 'tmp');

/**
 * The base name of the sierra and app configuration directory
 * @type string
 */
define('SRA_TMP_DIR', SRA_DIR . '/' . SRA_TMP_DIR_NAME);

/**
 * The default name of the SRA_Template context variable to assign the app 
 * SRA_ResourceBundle reference to
 * @type string
 */
define('SRA_APP_RB_NAME', 'resources');

/**
 * The SRA_DIR relative path to the system config file
 * @type string
 */
define('SRA_SYS_CONFIG', SRA_CONF_DIR_NAME . '/sierra-config.xml');

/**
 * The app base directory relative path to the app config file
 * @type string
 */
define('SRA_APP_CONFIG', SRA_CONF_DIR_NAME . '/app-config.xml');

/**
 * The default PHP extension to use
 * @type string
 */
define('SRA_SYS_PHP_EXTENSION', 'php');

/**
 * The default XML extension to use
 * @type string
 */
define('SRA_SYS_XML_EXTENSION', 'xml');

/**
 * the maximum integer value
 * @type int
 */
define('SRA_MAX_INT_VAL', 2147483647);

/**
 * the default SRA_GregorianDate::toString display format as specified in the 
 * sierra-config dtd for date-format
 * @type string
 */
define('SRA_DEFAULT_DATE_FORMAT', 'Y-m-d H:i:s');

/**
 * the default SRA_GregorianDate::toString display format as specified in the 
 * sierra-config dtd for date-only-format
 * @type string
 */
define('SRA_DEFAULT_DATE_ONLY_FORMAT', 'Y-m-d');

/**
 * the default error-log-file
 * @type string
 */
define('SRA_DEFAULT_ERROR_LOG_FILE', 'sra-err.log');

/**
 * the default log-dir
 * @type string
 */
define('SRA_DEFAULT_LOG_DIR', 'log');

/**
 * the default app lib-dir
 * @type string
 */
define('SRA_DEFAULT_LIB_DIR', 'lib');

/**
 * the default application html directory
 * @type string
 */
define('SRA_DEFAULT_HTML_DIR', 'www/html');

/**
 * the default templates directory
 * @type string
 */
define('SRA_DEFAULT_TEMPLATES_DIR', 'www/tpl');

/**
 * used to separate directories in the include_path environment variable
 * @type string
 */
define('SRA_PATH_SEPARATOR', ':');

/**
 * name of the ps cli program
 * @type string
 */
define('SRA_PS_PATH', 'ps');

/**
 * the name of the version information file (stored in the framework and app 
 * "etc" directory). this file contains the following values:
 *   svn-ci-password=[overrides svn-password for checkin operations]
 *   svn-ci-path=[overrides svn-path for checkin operations]
 *   svn-ci-url=[overrides svn-url for checkin operations]
 *   svn-ci-user=[overrides svn-user for checkin operations]
 *   svn-password=[the subversion repository password]
 *   svn-path=[the name of the framework/app subversion repository]
 *   svn-url=[the url to the subversion repository containing the framework/app]
 *   svn-user=[the subversion repository username]
 *   version=[current version identifier]
 *   versions-info=[path or uri to the versions info file]
 *   versions-readme=[path or uri to the versions readme file]
 * the versions-info file contains a list of all of the available versions in 
 * the format (where the topmost entry is considered the latest version):
 *   [version id]=[path or uri to source archive]
 * the versions-readme file contains a list of the readme notes for each version 
 * in the format:
 *   [version id]=[path or uri to change info notes]
 * use the SRA_Controller::getAppSvnInfo, SRA_Controller::getSysSvnInfo, 
 * SRA_Controller::getAppVersion, SRA_Controller::getSysVersion, 
 * SRA_Controller::getAppVersionRb, SRA_Controller::getSysVersionRb, 
 * SRA_Controller::getAppVersions, SRA_Controller::getSysVersions, 
 * SRA_Controller::getAppLatestVersion, and SRA_Controller::getSysLatestVersion 
 * methods to retrieve and use the data provided in these files
 * @type string
 */
define('SRA_VERSION_FILE', '.version');
// }}}

// Disable magic quotes
ini_set('magic_quotes_gpc', 0);
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);

// Startup tasks
if (!class_exists('SRA_Error')) {
  if (file_exists('/bin/hostname') && is_executable('/bin/hostname')) {
    /**
     * the hostname for the server
     * @type string
     */
    define('SRA_HOSTNAME', trim(shell_exec('/bin/hostname')));
  }
  
	ini_set('html_errors', TRUE);
	ini_set('display_errors', TRUE);
	error_reporting(E_ALL ^ E_NOTICE);
	
  // include framework lib directory in include path
  if (!strstr(ini_get('include_path'), SRA_LIB_DIR)) {
    ini_set('include_path', ini_get('include_path') . SRA_PATH_SEPARATOR . SRA_LIB_DIR);
  }
  require_once(SRA_LIB_DIR . '/core/SRA_Error.php');
  set_error_handler('error_handler');
}

/**
 * registers the PHP shutdown function SRA_Controller::registerShutdownMethod
 * @return void
 */
function app__shutdownApp__() {
	SRA_Controller::registerShutdownMethod($temp = NULL, NULL, NULL, true);  
}


// {{{ Includes
require_once(SRA_LIB_DIR . '/util/SRA_Util.php');
require_once(SRA_LIB_DIR . '/util/SRA_File.php');
require_once(SRA_LIB_DIR . '/util/SRA_XmlParser.php');
require_once(SRA_LIB_DIR . '/util/l10n/SRA_Locale.php');
require_once(SRA_LIB_DIR . '/util/l10n/SRA_ResourceBundle.php');
require_once(SRA_LIB_DIR . '/util/SRA_ArrayManager.php');
require_once(SRA_LIB_DIR . '/util/SRA_GregorianDate.php');
require_once(SRA_LIB_DIR . '/model/SRA_DaoFactory.php');
require_once(SRA_LIB_DIR . '/model/SRA_FileAttribute.php');
// }}}

// include framework www/tpl directory in include path
if (!strstr(ini_get('include_path'), SRA_Controller::getSysTplDir())) {
  ini_set('include_path', ini_get('include_path') . SRA_PATH_SEPARATOR . SRA_Controller::getSysTplDir());
}
// remove magic quotes if necessary
$sysConf =& SRA_Controller::getSysConf();
$magicQuotes = strtolower(ini_get('magic_quotes_gpc'));
if ($magicQuotes == 'on' || $magicQuotes == '1') {
	$keys = array_keys($_GET);
	foreach ($keys as $key) {
		$_GET[$key] = str_replace('\"', '"', $_GET[$key]);
		$_GET[$key] = str_replace("\\'", "'", $_GET[$key]);
	}
	$keys = array_keys($_POST);
	foreach ($keys as $key) {
		$_POST[$key] = str_replace('\"', '"', $_POST[$key]);
		$_POST[$key] = str_replace("\\'", "'", $_POST[$key]);
	}
}


// {{{ SRA_Controller
/**
 * This class provides static methods that may be used to initialize 
 * applications, retrieve references to application and system objects such as 
 * SRA_Template and SRA_Database instances, configuration information, and more
 * @author Jason Read <jason@idir.org>
 * @package sierra.core
 */

class SRA_Controller {
    
  // {{{ getAllAppConfs
  /**
   * Returns a reference to an array of all app configuration arrays, indexed by 
   * app key. If this is the first time that this method has been called then it 
   * will have to load the configurations and assign them to static variables. 
   * If not, it will simply return a reference to the existing static variables
   * @access public
   * @return array
   */
  function &getAllAppConfs() {
    static $_sraCachedAllAppConfs;
    
    if (!isset($_sraCachedAllAppConfs)) {
      $_sraCachedAllAppConfs = array();
      if (!SRA_Error::isError($sysConf =& SRA_Controller::getSysConf()) && isset($sysConf['app'])) {
        foreach (array_keys($sysConf['app']) as $app) {
          if (SRA_Error::isError($_sraCachedAllAppConfs[$app] =& SRA_Controller::getAppConf($app))) {
            unset($_sraCachedAllAppConfs[$app]);
            SRA_Error::logError('SRA_Controller::getAllAppConfs: Failed - Could not get app config for ' . $app, __FILE__, __LINE__);
          }
        }
      }
    }
    return $_sraCachedAllAppConfs;
  }
  // }}}

  // {{{ getAppDb
  /**
   * Returns a reference to the currently active app database connection if 
   * it exists, otherwise, a sys db connection will be returned (if it exists)
   * @param string $id the identifier of the database to return. this parameter 
   * is optional. if not specified, the first database defined in the app 
   * configuration will be returned
   * @access public
   * @return 	SRA_Database
   */
  function &getAppDb($id = FALSE) {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDbs = array();
    
    $dbKey = $_sraCurrentAppKey . '_' . $id;
    if (!isset($_sraCachedAppDbs[$dbKey])) {
      if (!$_sraCurrentAppKey || SRA_Error::isError($conf =& SRA_Controller::getAppConf()) || !isset($conf['db']) || !isset($conf['key']) || !is_array($conf['db']) || ($id && !isset($conf['db'][$id]))) {
        $_sraCachedAppDbs[$dbKey] =& SRA_Controller::getSysDb($id);
      }
      else {
        $keys = array_keys($conf['db']);
        if (!SRA_Error::isError($_sraCachedAppDbs[$dbKey] =& SRA_Database::getDatabase($conf['db'][$id ? $id : $keys[0]]))) {
          SRA_Controller::registerShutdownMethod($_sraCachedAppDbs[$dbKey], 'close');
        }
      }
    }
    return $_sraCachedAppDbs[$dbKey];
  }
  // }}}
  
  // {{{ getSysDb
  /**
   * This is the only method that can be referenced before init. It returns a 
   * reference to the system database as is configured in the system etc file
   * @param string $id the identifier of the database to return. this parameter 
   * is optional. if not specified, the first database defined in the 
   * configuration will be returned
   * @access 	public
   * @return 	SRA_Database
   */
  function &getSysDb($id = FALSE) {
    $id = $id ? $id : 0;
    static $_sraCachedSysDbs = array();

    if (!isset($_sraCachedSysDbs[$id])) {
      if (!SRA_Error::isError($conf =& SRA_Controller::getSysConf())) {
        if (isset($conf['db']) && (!$id || $conf['db'][$id])) {
          $keys = array_keys($conf['db']);
          if (!SRA_Error::isError($_sraCachedSysDbs[$id] =& SRA_Database::getDatabase($conf['db'][$id ? $id : $keys[0]]))) {
            SRA_Controller::registerShutdownMethod($_sraCachedSysDbs[$id], 'close');
          }
        }
        else {
          $_sraCachedSysDbs[$id] =& SRA_Error::logError('SRA_Controller::getSysDb: Failed - Could not instantiate system db ' . $id, __FILE__, __LINE__);
        }
      }
      else {
        $_sraCachedSysDbs[$id] =& $conf;
      }
    }
    return $_sraCachedSysDbs[$id];
  }
  // }}}

  // {{{ getAppConf
  /**
   * Returns a reference to the active app's configuration array
   * @param string $appKey optional parameter that specifies which app config 
   * array to return. If not specified, the currently initialized app config 
   * will be returned.
   * @access public
   * @return array
   */
  function &getAppConf($appKey = NULL) {
    global $_sraCurrentAppKey;
    static $_sraCachedAppConfs = array();
    
    $appKey = $appKey ? $appKey : $_sraCurrentAppKey;
    if ($appKey && !isset($_sraCachedAppConfs[$appKey])) {
      if (SRA_Error::isError($sysConf =& SRA_Controller::getSysConf())) {
        $_sraCachedAppConfs[$appKey] =& $sysConf;
      }
      else {
        if (!isset($sysConf['app']) || !isset($sysConf['app'][$appKey])) {
          $_sraCachedAppConfs[$appKey] =& SRA_Error::logError('SRA_Controller::getAppConf: Failed - App ' . $appKey . ' is not defined in the system configuration', __FILE__, __LINE__);
        }
        else if (SRA_Error::isError($xml =& SRA_XmlParser::getXmlParser($confFile = $sysConf['app'][$appKey]['dir'] . '/' . SRA_APP_CONFIG, TRUE))) {
          $_sraCachedAppConfs[$appKey] =& SRA_Error::logError('SRA_Controller::getAppConf: Failed - Could not load app config file "' . $confFile . '" for app "' . $appKey . '"', __FILE__, __LINE__);
        }
        else {
          $data =& $xml->getData();
          $_sraCachedAppConfs[$appKey] = array();
          $keys = array_keys($data['app-config']['attributes']);
          foreach ($keys as $key) {
            $_sraCachedAppConfs[$appKey][$key] = $data['app-config']['attributes'][$key];
          }
          $keys = array_keys($data['app-config']);
          foreach ($keys as $key) {
            if ($key != 'attributes') {
              $_sraCachedAppConfs[$appKey][$key] = $data['app-config'][$key];
            }
          }
          if (!isset($_sraCachedAppConfs[$appKey]['date-format'])) {
            $_sraCachedAppConfs[$appKey]['date-format'] = $sysConf['date-format'];
          }
          if (isset($_sraCachedAppConfs[$appKey]['db'])) {
            $keys = array_keys($_sraCachedAppConfs[$appKey]['db']);
            foreach ($keys as $key) {
              $tmp = array();
              $tmp[$key] = $_sraCachedAppConfs[$appKey]['db'][$key]['attributes'];
              if (array_key_exists('table-cache', $_sraCachedAppConfs[$appKey]['db'][$key])) {
                $tmp[$key]['table-cache'] = $_sraCachedAppConfs[$appKey]['db'][$key]['table-cache'];
              }
              $_sraCachedAppConfs[$appKey]['db'][$key] = $tmp[$key];
            }
          }
      
          // Populate app key and dir values
          $_sraCachedAppConfs[$appKey]['key'] = $appKey;
          $_sraCachedAppConfs[$appKey]['dir'] = $sysConf['app'][$appKey]['dir'];
          
          // Populate any missing db data
          if (isset($_sraCachedAppConfs[$appKey]['db'])) {
            require_once(SRA_LIB_DIR . '/sql/SRA_Database.php');
            foreach ($_sraCachedAppConfs[$appKey]['db'] as $server => $value) {
              if (array_key_exists('db', $sysConf) && array_key_exists($server, $sysConf['db'])) {
                $_sraCachedAppConfs[$appKey]['db'][$server] = & SRA_Util::arrayMerge($sysConf['db'][$server], $_sraCachedAppConfs[$appKey]['db'][$server], FALSE);
              }
              else {
                $_sraCachedAppConfs[$appKey]['db'][$server] =& SRA_Controller::_populateDefaultDbConf($_sraCachedAppConfs[$appKey]['db'][$server]);
              }
            }
          }
          
          // set ini variables
          if (isset($_sraCachedAppConfs[$appKey]['ini-set'])) {
            $keys = array_keys($_sraCachedAppConfs[$appKey]['ini-set']);
            foreach($keys as $key) {
              ini_set($key, $_sraCachedAppConfs[$appKey]['ini-set'][$key]['attributes']['value']);
            }
          }
        }
      }
    }
    return $_sraCachedAppConfs[$appKey];
  }
  // }}}
    
  // {{{ getSysConf
  /**
   * Returns a reference to the system configuration array. If this is the first 
   * time that this method has been called then it will have to load the 
   * configuration and assign it to a static variable. If not, it will simply 
   * return a reference to the existing static variable
   * @param boolean $resetCache resets the system config cache
   * @access public
   * @return 	array
   */
  function &getSysConf($resetCache=FALSE) {
    static $_sraCachedSysConf;

    if (!isset($_sraCachedSysConf) || $resetCache) {
      // Load xml file
      $confFile = SRA_DIR . '/' . SRA_SYS_CONFIG;
      if (file_exists($confFile) && SRA_Error::isError($xml =& SRA_XmlParser::getXmlParser($confFile, TRUE))) {
        $_sraCachedSysConf =& SRA_Error::logError("SRA_Controller::getSysConf: Failed - Could not load system config file '$confFile'", __FILE__, __LINE__, SRA_ERROR_SHUTDOWN);
      }
      else {
        $data = file_exists($confFile) ? $xml->getData() : array('sierra-config' => array('attributes' => array()));
        $_sraCachedSysConf = array();
        $keys = array_keys($data['sierra-config']['attributes']);
        foreach ($keys as $key) {
          $_sraCachedSysConf[$key] = $data['sierra-config']['attributes'][$key];
        }
        $keys = array_keys($data['sierra-config']);
        foreach ($keys as $key) {
          if ($key != 'attributes') {
            $_sraCachedSysConf[$key] = $data['sierra-config'][$key];
          }
        }
        $keys = array_keys($_sraCachedSysConf['app']);
        foreach ($keys as $key) {
          if (!array_key_exists('dir', $_sraCachedSysConf['app'][$key])) {
            $_sraCachedSysConf['app'][$key]['dir'] = SRA_DIR . '/' . SRA_APP_DIR_NAME . '/' . $key;
          }
        }
        if (!isset($_sraCachedSysConf['date-format'])) {
          $_sraCachedSysConf['date-format'] = SRA_DEFAULT_DATE_FORMAT;
        }
        if (array_key_exists('db', $_sraCachedSysConf)) {
          require_once(SRA_LIB_DIR . '/sql/SRA_Database.php');
          $keys = array_keys($_sraCachedSysConf['db']);
          foreach ($keys as $key) {
            $_sraCachedSysConf['db'][$key] =& SRA_Controller::_populateDefaultDbConf($_sraCachedSysConf['db'][$key]);
          }
        }
        // enable output compression
        if (isset($_sraCachedSysConf['compress-output']) && $_sraCachedSysConf['compress-output'] == '1') {
          SRA_Controller::_compressOutput();
        }
        // set ini variables
        if (isset($_sraCachedSysConf['ini-set'])) {
          $keys = array_keys($_sraCachedSysConf['ini-set']);
          foreach($keys as $key) {
            ini_set($key, $_sraCachedSysConf['ini-set'][$key]['attributes']['value']);
          }
        }
        // set new time zone
        if (isset($_sraCachedSysConf['time-zone']) && SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone($_sraCachedSysConf['time-zone']))) {
          SRA_TimeZone::setTzEnvVar($tz);
        }
      }
    }
    return $_sraCachedSysConf;
  }
  // }}}
		
  // {{{ getAppDir
  /**
   * Returns the path to the app base directory
   * @access public
   * @return  string
   */
  function getAppDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDirs[$_sraCurrentAppKey] = $conf && isset($conf['dir']) ? $conf['dir'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDirs[$_sraCurrentAppKey]) ? $_sraCachedAppDirs[$_sraCurrentAppKey] : SRA_Controller::getSysDir();
  }
  // }}}
    
  // {{{ getSysDir
  /**
   * Returns the path to the sierra framework base directory
   * @access public
   * @return  string
   */
  function getSysDir() {
    return SRA_DIR;
  }
  // }}}
    
  // {{{ getAppConfDir
  /**
   * Returns the path to the app configuration directory
   * @access public
   * @return  string
   */
  function getAppConfDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppConfDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppConfDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppConfDirs[$_sraCurrentAppKey] = $conf && isset($conf['dir']) ? $conf['dir'] . '/' . SRA_CONF_DIR_NAME : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppConfDirs[$_sraCurrentAppKey]) ? $_sraCachedAppConfDirs[$_sraCurrentAppKey] : SRA_Controller::getSysConfDir();
  }
  // }}}
    
  // {{{ getSysConfDir
  /**
   * Returns the path to the sierra configuration directory
   * @access public
   * @return  string
   */
  function getSysConfDir() {
    return SRA_CONF_DIR;
  }
  // }}}
		
  // {{{ getAppLibDir
  /**
   * Returns the path to the app base lib directory
   * @access public
   * @return  string
   */
  function getAppLibDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppLibDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppLibDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppLibDirs[$_sraCurrentAppKey] = isset($conf['lib-dir']) && ($dir = SRA_File::getRelativePath($conf['lib-dir'])) ? $dir : $conf['dir'] . '/' . SRA_DEFAULT_LIB_DIR;
    }
    return $_sraCurrentAppKey ? $_sraCachedAppLibDirs[$_sraCurrentAppKey] : NULL;
  }
  // }}}
  
  // {{{ getSysLibDir
  /**
   * Returns the path to the system lib directory
   * @access public
   * @return  string
   */
  function getSysLibDir() {
    return SRA_LIB_DIR;
  }
  // }}}
    
  // {{{ getAppName
  /**
   * Returns the name of the current initialized application. this is the app
   * identifier (key), or the string that maps to that identifer in the 
   * application resources (app.properties)
   * @access public
   * @return  string
   */
  function getAppName() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppNames = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppNames[$_sraCurrentAppKey])) {
      $resources =& SRA_Controller::getAppResources();
      $_sraCachedAppNames[$_sraCurrentAppKey] = $resources->getString($_sraCurrentAppKey);
    }
    return $_sraCurrentAppKey ? $_sraCachedAppNames[$_sraCurrentAppKey] : NULL;
  }
  // }}}
    
  // {{{ getAppParams
  /**
   * returns parameters specified in the app-config for the current running 
   * application. if $id is specified, the return value will the value for 
   * that param. if $type is specified, only params of that type will be 
   * returned. if neither $id nor $type are specified, then the full 
   * SRA_Params object will be returned
   * @param string $id (optional) the id of the param to return
   * @param string $type (optional) the type of the param(s) to return
   * @access public
   * @return mixed
   */
  function getAppParams($id=NULL, $type=NULL) {
    require_once('util/SRA_Params.php');
    
    global $_sraCurrentAppKey;
    static $_sraCachedAppParams = array();
    if (SRA_Controller::isAppInitialized()) {
      if (!isset($_sraCachedAppParams[$_sraCurrentAppKey])) {
        $conf =& SRA_Controller::getAppConf();
        $_sraCachedAppParams[$_sraCurrentAppKey] = new SRA_Params(isset($conf['param']) ? $conf['param'] : NULL);
      }
      if ($type) {
        $params =& $_sraCachedAppParams[$_sraCurrentAppKey]->getParams($type);
        return $id ? $params[$id] : $params;
      }
      else {
        return $id ? $_sraCachedAppParams[$_sraCurrentAppKey]->getParam($id) : $_sraCachedAppParams[$_sraCurrentAppKey];
      }
    }
    return SRA_Controller::getSysParams($id, $type);
  }
  // }}}
  
  // {{{ getSysParams
  /**
   * returns parameters system wide parameters as specified in 
   * sierra-config.xml. if $id is specified, the return value will the value 
   * for that param. if $type is specified, only params of that type will be 
   * returned. if neither $id nor $type are specified, then the full 
   * SRA_Params object will be returned
   * @param string $id (optional) the id of the param to return
   * @param string $type (optional) the type of the param(s) to return
   * @access public
   * @return  string
   */
  function getSysParams($id=NULL, $type=NULL) {
    require_once('util/SRA_Params.php');
    
    static $_sraCachedSysParams = NULL;
    if (!isset($_sraCachedSysParams)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysParams = new SRA_Params(isset($conf['param']) ? $conf['param'] : NULL);
    }
    if ($type) {
      $params =& $_sraCachedSysParams->getParams($type);
      return $id ? $params[$id] : $params;
    }
    else {
      return $id ? $_sraCachedSysParams->getParam($id) : $_sraCachedSysParams;
    }
  }
  // }}}
    
  // {{{ getAppShortName
  /**
   * Returns the short name of the current initialized application. this is 
   * the resource bundle value '[app id].short', or SRA_Controller::getAppName
   * if that key value does not exist
   * @access public
   * @return  string
   */
  function getAppShortName() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppShortNames = array();
    if ($_sraCurrentAppKey && !isset($_sraCachedAppShortNames[$_sraCurrentAppKey])) {
      $resources =& SRA_Controller::getAppResources();
      $_sraCachedAppShortNames[$_sraCurrentAppKey] = $resources->containsKey($_sraCurrentAppKey . '.short') ? $resources->getString($_sraCurrentAppKey . '.short') : SRA_Controller::getAppName();
    }
    return $_sraCurrentAppKey ? $_sraCachedAppShortNames[$_sraCurrentAppKey] : NULL;
  }
  // }}}
  
  // {{{ getAppHtmlDir
  /**
   * Returns the path to the current application's html directory
   * @access public
   * @return  string
   */
  function getAppHtmlDir() {
    return SRA_Controller::getAppDir() . '/' . SRA_DEFAULT_HTML_DIR;
  }
  // }}}
		
  // {{{ getAppTplDir
  /**
   * Returns the templates directory for the current active app if 
   * initialized, the framework otherwise
   * @access public
   * @return  string
   */
  function getAppTplDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppTplDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppTplDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppTplDirs[$_sraCurrentAppKey] = isset($conf['tpl-dir']) && ($dir = SRA_File::getRelativePath($conf['tpl-dir'])) ? $dir : (SRA_File::getRelativePath(SRA_DEFAULT_TEMPLATES_DIR) ? SRA_File::getRelativePath(SRA_DEFAULT_TEMPLATES_DIR) : NULL);
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppTplDirs[$_sraCurrentAppKey]) ? $_sraCachedAppTplDirs[$_sraCurrentAppKey] : SRA_Controller::getSysTplDir();
  }
  // }}}
		
  // {{{ getSysTplDir
  /**
   * Returns the templates directory for the system
   * @access public
   * @return  string
   */
  function getSysTplDir() {
    static $_sraCachedSysTplDir;
    
    if (!isset($_sraCachedSysTplDir)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysTplDir = isset($conf['tpl-dir']) && ($dir = SRA_File::getSysRelativePath($conf['tpl-dir'])) ? $dir : SRA_File::getSysRelativePath(SRA_DEFAULT_TEMPLATES_DIR);
    }
    return $_sraCachedSysTplDir;
  }
  // }}}
		
  // {{{ getAppErrorLogFile
  /**
   * Returns the path to the error log file that should be used currently. 
   * this will be the system error-log-file unless a app is initialized 
   * and a app specific log file has been specified
   * @access public
   * @return  string
   */
  function getAppErrorLogFile() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppLogFiles = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppLogFiles[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppLogFiles[$_sraCurrentAppKey] = isset($conf['error-log-file']) ? SRA_Controller::getAppLogDir() . '/' . $conf['error-log-file'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppLogFiles[$_sraCurrentAppKey]) ? $_sraCachedAppLogFiles[$_sraCurrentAppKey] : SRA_Controller::getSysErrorLogFile();
  }
  // }}}
		
  // {{{ getSysErrorLogFile
  /**
   * Returns the path to the framework wide error log file
   * @access public
   * @return  string
   */
  function getSysErrorLogFile() {
    static $_sraCachedSysLogFile;
    
    if (!isset($_sraCachedSysLogFile)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysLogFile = isset($conf['error-log-file']) ? SRA_Controller::getSysLogDir() . '/' . $conf['error-log-file'] : SRA_Controller::getSysLogDir() . '/' . SRA_DEFAULT_ERROR_LOG_FILE;
    }
    return $_sraCachedSysLogFile;
  }
  // }}}
  
  // {{{ isAppInDebug
  /**
   * returns TRUE if 'debug' is set in app-config, 
   * SRA_Controller::isSysInDebug otherwise
   * @access public
   * @return  boolean
   */
  function isAppInDebug() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppInDebug = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppInDebug[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppInDebug[$_sraCurrentAppKey] = isset($conf['debug']) ? $conf['debug'] == '1' : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppInDebug[$_sraCurrentAppKey]) ? $_sraCachedAppInDebug[$_sraCurrentAppKey] : SRA_Controller::isSysInDebug();
    
  }
  // }}}
  
  // {{{ isSysInDebug
  /**
   * returns TRUE if 'debug' is set in sierra-config, SRA_CONTROLLER_DEBUG 
   * otherwise
   * @access public
   * @return  boolean
   */
  function isSysInDebug() {
    static $_sraCachedSysInDebug;
    
    if (!isset($_sraCachedSysInDebug)) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedSysInDebug = $conf && isset($conf['debug']) ? $conf['debug'] == '1' : SRA_CONTROLLER_DEBUG;
    }
    return $_sraCachedSysInDebug;
  }
  // }}}

  // {{{ getCurrentAppId
  /**
   * Returns the app key (string identifier) of the currently initialized app
   * @access public
   * @return  String
   */
  function getCurrentAppId() {
    global $_sraCurrentAppKey;
    return $_sraCurrentAppKey;
  }
  // }}}
	
  // {{{ getAppLocale
  /**
   * Returns the default SRA_Locale associated with the active app if one is 
   * active, otherwise the sys locale
   * @access 	public
   * @return 	SRA_Locale
   */
  function &getAppLocale() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppLocales = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppLocales[$_sraCurrentAppKey])) {
      $_sraCachedAppLocales[$_sraCurrentAppKey] =& SRA_Locale::getLocale(SRA_Controller::getAppDefaultCountry(), SRA_Controller::getAppDefaultLanguage());
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppLocales[$_sraCurrentAppKey]) ? $_sraCachedAppLocales[$_sraCurrentAppKey] : SRA_Controller::getSysLocale();
  }
  // }}}
	
  // {{{ getSysLocale
  /**
   * Returns the default SRA_Locale for the system
   *
   * @access 	public
   * @return 	SRA_Locale
   */
  function &getSysLocale() {
    static $_sraCachedSysLocale;
    
    if (!isset($_sraCachedSysLocale)) {
      $_sraCachedSysLocale =& SRA_Locale::getLocale(SRA_Controller::getSysDefaultCountry(), SRA_Controller::getSysDefaultLanguage());
    }
    return $_sraCachedSysLocale;
  }
  // }}}

  // {{{ getAppTemplate
  /**
   * Returns a reference to an application specific SRA_Template object. If 
   * this is the first time that this method has been called
   * then it will have to instantiate the SRA_Template object and assign it
   * to a static variable. If not, it will simply return a reference to
   * the existing static variable.
   * 
   * This method will automatically add the following template reference 
   * variables:
   *   Controller: a reference to an SRA_Controller instance
   *   Date: a reference to a SRA_GregorianDate instance
   *   Error: a reference to a SRA_Error instance
   *   File: a reference to a SRA_File instance
   *   Locale: a reference to the user's preferred SRA_Locale
   *   Util: a reference to a SRA_Util instance
   *   Template: a reference to the SRA_Template that is returned
   * @access public
   * @return 	SRA_Template
   */
  function &getAppTemplate() {
    require_once(SRA_LIB_DIR . '/core/SRA_Template.php');
    
    global $_sraCurrentAppKey;
    static $_sraCachedAppTemplates = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppTemplates[$_sraCurrentAppKey])) {
      if (SRA_Template::isValid($_sraCachedAppTemplates[$_sraCurrentAppKey] =& SRA_Template::getTemplate($_sraCurrentAppKey))) {
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef(SRA_APP_RB_NAME, SRA_Controller::getAppResources());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Controller', new SRA_Controller());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Date', new SRA_GregorianDate());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Error', new SRA_Error(NULL, NULL, NULL, NULL));
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('File', new SRA_File());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Locale', SRA_Controller::getUserLocale());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Util', new SRA_Util());
        $_sraCachedAppTemplates[$_sraCurrentAppKey]->assignByRef('Template', $_sraCachedAppTemplates[$_sraCurrentAppKey]);
      }
    }
    return $_sraCurrentAppKey ? $_sraCachedAppTemplates[$_sraCurrentAppKey] : SRA_Controller::getSysTemplate();
  }
  // }}}
  
  // {{{ getSysTemplate
  /**
   * Returns a reference to an system SRA_Template object. If this is the first 
   * time that this method has been called then it will have to instantiate the 
   * SRA_Template object and assign it to a static variable. If not, it will 
   * simply return a reference to the existing static variable.
   * 
   * This method will automatically add the following template reference 
   * variables:
   *   Controller: a reference to an SRA_Controller instance
   *   Date: a reference to a SRA_GregorianDate instance
   *   Error: a reference to a SRA_Error instance
   *   File: a reference to a SRA_File instance
   *   Locale: a reference to the user's preferred SRA_Locale
   *   Util: a reference to a SRA_Util instance
   *   Template: a reference to the SRA_Template that is returned
   * @access public
   * @return 	SRA_Template
   */
  function &getSysTemplate() {
    require_once(SRA_LIB_DIR . '/core/SRA_Template.php');
    
    static $_sraCachedSysTemplate;
    
    if (!isset($_sraCachedSysTemplate)) {
      if (SRA_Template::isValid($_sraCachedSysTemplate =& SRA_Template::getTemplate())) {
        $_sraCachedSysTemplate->assignByRef(SRA_APP_RB_NAME, SRA_Controller::getSysResources());
        $_sraCachedSysTemplate->assignByRef('Controller', new SRA_Controller());
        $_sraCachedSysTemplate->assignByRef('Date', new SRA_GregorianDate());
        $_sraCachedSysTemplate->assignByRef('Error', new SRA_Error());
        $_sraCachedSysTemplate->assignByRef('File', new SRA_File());
        $_sraCachedSysTemplate->assignByRef('Locale', SRA_Controller::getUserLocale());
        $_sraCachedSysTemplate->assignByRef('Util', new SRA_Util());
        $_sraCachedSysTemplate->assignByRef('Template', $_sraCachedSysTemplate);
      }
    }
    return $_sraCachedSysTemplate;
  }
  // }}}
		
  // {{{ getAppTimeZone
  /**
   * Returns the SRA_TimeZone for the current initialized app, if not app 
   * is initialized, or a default timezone is not specified for the app, 
   * SRA_Controller::getSysTimeZone will be returned
   * @param boolean $id whether or not to return the id of the timezone and not 
   * the actual SRA_TimeZone instance
   * @access public
   * @return  SRA_TimeZone
   */
  function &getAppTimeZone($id=FALSE) {
    global $_sraCurrentAppKey;
    static $_sraCachedAppTimeZones = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppTimeZones[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      if (isset($conf['time-zone'])) {
        $_sraCachedAppTimeZones[$_sraCurrentAppKey] =& SRA_TimeZone::getTimeZone($conf['time-zone']);
      }
      else {
        $_sraCachedAppTimeZones[$_sraCurrentAppKey] = NULL;
      }
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppTimeZones[$_sraCurrentAppKey]) ? (!$id ? $_sraCachedAppTimeZones[$_sraCurrentAppKey] : $_sraCachedAppTimeZones[$_sraCurrentAppKey]->getId()) : SRA_Controller::getSysTimeZone($id);
  }
  // }}}
		
  // {{{ getSysTimeZone
  /**
   * Returns the temp directory for the system
   * @param boolean $id whether or not to return the id of the timezone and not 
   * the actual SRA_TimeZone instance
   * @access public
   * @return  SRA_TimeZone
   */
  function &getSysTimeZone($id=FALSE) {
    static $_sraCachedSysTimeZone;
    if (!isset($_sraCachedSysTimeZone)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysTimeZone =& SRA_TimeZone::getTimeZone(isset($conf['time-zone']) ? $conf['time-zone'] : NULL);
    }
    return !$id ? $_sraCachedSysTimeZone : $_sraCachedSysTimeZone->getId();
  }
  // }}}
		
  // {{{ getAppLogDir
  /**
   * Returns the log directory for the current active app if initialized, 
   * the framework otherwise
   * @access public
   * @return  string
   */
  function getAppLogDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppLogDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppLogDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppLogDirs[$_sraCurrentAppKey] = isset($conf['log-dir']) && ($dir = SRA_File::getRelativePath($conf['log-dir'])) && is_writable($dir) ? $dir : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppLogDirs[$_sraCurrentAppKey]) ? $_sraCachedAppLogDirs[$_sraCurrentAppKey] : SRA_Controller::getSysLogDir();
  }
  // }}}
		
  // {{{ getSysLogDir
  /**
   * Returns the log directory for the system
   * @access public
   * @return  string
   */
  function getSysLogDir() {
    static $_sraCachedSysLogDir;
    
    if (!isset($_sraCachedSysLogDir)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysLogDir = isset($conf['log-dir']) && ($dir = SRA_File::getSysRelativePath($conf['log-dir'])) && is_writable($dir) ? $dir : SRA_File::getSysRelativePath(SRA_DEFAULT_LOG_DIR);
    }
    return $_sraCachedSysLogDir;
  }
  // }}}
		
  // {{{ getAppTmpDir
  /**
   * Returns the temp directory for the current active app if initialized, the 
   * framework otherwise
   * @access public
   * @return  string
   */
  function getAppTmpDir() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppTmpDirs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppTmpDirs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppTmpDirs[$_sraCurrentAppKey] = isset($conf['tmp-dir']) && ($dir = SRA_File::getRelativePath($conf['tmp-dir'])) && is_writable($dir) ? $dir : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppTmpDirs[$_sraCurrentAppKey]) ? $_sraCachedAppTmpDirs[$_sraCurrentAppKey] : SRA_Controller::getSysTmpDir();
  }
  // }}}
		
  // {{{ getSysTmpDir
  /**
   * Returns the temp directory for the system
   * @access public
   * @return  string
   */
  function getSysTmpDir() {
    return SRA_TMP_DIR;
  }
  // }}}
		
  // {{{ getAppConfAttr
  /**
   * Returns a configuration attribute value for the currently initialized app
   * @param string or array $attr the attribute value to return
   * @access public
   * @return 	string
   */
  function getAppConfAttr($attr) {
    global $_sraCurrentAppKey;
    static $_sraCachedAppConfAttrs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppConfAttrs[$_sraCurrentAppKey])) {
      $_sraCachedAppConfAttrs[$_sraCurrentAppKey] = new SRA_ArrayManager(SRA_Controller::getAppConf());
    }
    return $_sraCurrentAppKey ? $_sraCachedAppConfAttrs[$_sraCurrentAppKey]->getData(!is_array($attr) ? array($attr) : $attr) : NULL;
  }
  // }}}
		
  // {{{ getSysConfAttr
  /**
   * Returns a configuration attribute value for the system
   * @param string or array $attr the attribute value to return
   * @access public
   * @return 	string
   */
  function getSysConfAttr($attr) {
    static $_sraCachedSysConfAttrs;
    
    if (!isset($_sraCachedSysConfAttrs)) {
      $_sraCachedSysConfAttrs = new SRA_ArrayManager(SRA_Controller::getSysConf());
    }
    return $_sraCachedSysConfAttrs ? $_sraCachedSysConfAttrs->getData(!is_array($attr) ? array($attr) : $attr) : NULL;
  }
  // }}}
		
  // {{{ getAppDefaultCountry
  /**
   * Returns the default SRA_Locale country to use
   * @access public
   * @return 	string
   */
  function getAppDefaultCountry() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDefaultCountries = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDefaultCountries[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDefaultCountries[$_sraCurrentAppKey] = isset($conf['default-country']) ? $conf['default-country'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDefaultCountries[$_sraCurrentAppKey]) ? $_sraCachedAppDefaultCountries[$_sraCurrentAppKey] : SRA_Controller::getSysDefaultCountry();
  }
  // }}}
		
  // {{{ getSysDefaultCountry
  /**
   * Returns the default SRA_Locale country to use for the entire framework
   * @access public
   * @return 	string
   */
  function getSysDefaultCountry() {
    static $_sraCachedSysDefaultCountry;
    
    if (!isset($_sraCachedSysDefaultCountry)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysDefaultCountry = isset($conf['default-country']) ? $conf['default-country'] : SRA_LOCALE_DEFAULT_COUNTRY;
    }
    return $_sraCachedSysDefaultCountry;
  }
  // }}}
    
  // {{{ getAppDefaultCurrency
  /**
   * Returns the default currency to use
   * @access public
   * @return 	string
   */
  function getAppDefaultCurrency() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDefaultCurrencies = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDefaultCurrencies[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDefaultCurrencies[$_sraCurrentAppKey] = isset($conf['default-currency']) ? $conf['default-currency'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDefaultCurrencies[$_sraCurrentAppKey]) ? $_sraCachedAppDefaultCurrencies[$_sraCurrentAppKey] : SRA_Controller::getSysDefaultCurrency();
  }
  // }}}
		
  // {{{ getSysDefaultCurrency
  /**
   * Returns the default currency to use for the entire framework
   * @access public
   * @return 	string
   */
  function getSysDefaultCurrency() {
    static $_sraCachedSysDefaultCurrency;
    
    if (!isset($_sraCachedSysDefaultCurrency)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysDefaultCurrency = isset($conf['default-currency']) ? $conf['default-currency'] : SRA_CURRENCY_DEFAULT;
    }
    return $_sraCachedSysDefaultCurrency;
  }
  // }}}
    
  // {{{ getAppDefaultLanguage
  /**
   * Returns the default SRA_Locale language to use
   * @access public
   * @return 	string
   */
  function getAppDefaultLanguage() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDefaultLanguages = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDefaultLanguages[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDefaultLanguages[$_sraCurrentAppKey] = isset($conf['default-language']) ? $conf['default-language'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDefaultLanguages[$_sraCurrentAppKey]) ? $_sraCachedAppDefaultLanguages[$_sraCurrentAppKey] : SRA_Controller::getSysDefaultLanguage();
  }
  // }}}
		
  // {{{ getSysDefaultLanguage
  /**
   * Returns the default SRA_Locale language to use for the entire framework
   * @access public
   * @return 	string
   */
  function getSysDefaultLanguage() {
    static $_sraCachedSysDefaultLanguage;
    
    if (!isset($_sraCachedSysDefaultLanguage)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysDefaultLanguage = isset($conf['default-language']) ? $conf['default-language'] : SRA_LOCALE_DEFAULT_LANGUAGE;
    }
    return $_sraCachedSysDefaultLanguage;
  }
  // }}}
		
  // {{{ getAppErrorMask
  /**
   * Returns the app error-mask (if a app is initialized, otherwise 
   * returns the framework error-mask)
   * @access public
   * @return 	string
   */
  function getAppErrorMask() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppErrorMasks = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppErrorMasks[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppErrorMasks[$_sraCurrentAppKey] = isset($conf['error-mask']) ? $conf['error-mask'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppErrorMasks[$_sraCurrentAppKey]) ? $_sraCachedAppErrorMasks[$_sraCurrentAppKey] : SRA_Controller::getSysErrorMask();
  }
  // }}}
		
  // {{{ getSysErrorMask
  /**
   * Returns the framework error-mask if specified, SRA_DEFAULT_ERROR_MASK 
   * otherwise
   * @access public
   * @return 	string
   */
  function getSysErrorMask() {
    static $_sraCachedSysErrorMask;
    
    if (!isset($_sraCachedSysErrorMask)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysErrorMask = isset($conf['error-mask']) ? $conf['error-mask'] : SRA_DEFAULT_ERROR_MASK;
    }
    return $_sraCachedSysErrorMask;
  }
  // }}}
		
  // {{{ getAppResources
  /**
   * Returns a reference to the SRA_ResourceBundle for the active app according 
   * to the preferences specified in the app config
   * @access public
   * @return 	SRA_ResourceBundle
   */
  function &getAppResources() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppResources = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppResources[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      if (!isset($conf['resources-file']) || (isset($conf['resources-file']) && $conf['resources-file'] != '0')) {
        $bundle = isset($conf['resources-file']) ? $bundle = $conf['resources-file'] : SRA_RESOURCE_BUNDLE_DEFAULT_BUNDLE_NAME;
        $_sraCachedAppResources[$_sraCurrentAppKey] =& SRA_ResourceBundle::merge(SRA_Controller::_getResources($bundle, SRA_Controller::getUserLocales()), SRA_Controller::getSysResources());
      }
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppResources[$_sraCurrentAppKey]) ? $_sraCachedAppResources[$_sraCurrentAppKey] : SRA_Controller::getSysResources();
  }
  // }}}
		
  // {{{ getSysResources
  /**
   * Returns a reference to the global SRA_ResourceBundle according to the 
   * preferences specified in sierra config
   * @access public
   * @return 	SRA_ResourceBundle
   */
  function &getSysResources() {
    static $_sraCachedSysResources;
    
    if (!isset($_sraCachedSysResources)) {
      $conf =& SRA_Controller::getSysConf();
      if (!isset($conf['resources-file']) || (isset($conf['resources-file']) && $conf['resources-file'] != '0')) {
        $bundle = isset($conf['resources-file']) ? $conf['resources-file'] : SRA_RESOURCE_BUNDLE_DEFAULT_SYS_BUNDLE_NAME;
        $_sraCachedSysResources =& SRA_Controller::_getResources($bundle, SRA_Controller::getUserLocales(), TRUE);
      }
    }
    return $_sraCachedSysResources;
  }
  // }}}
    
  // {{{ _getResources
  /**
   * Returns a reference to the global SRA_ResourceBundle according to the 
   * preferences specified in sierra config
   * @access public
   * @return 	SRA_ResourceBundle
   */
  function &_getResources($bundles, $locales=FALSE, $sysSpecific=FALSE) {
    $bundles = explode(' ', $bundles);
    $resources =& SRA_ResourceBundle::getBundle($bundles[0], $locales, $sysSpecific);
    for($i=1; $i<count($bundles); $i++) {
      if (SRA_ResourceBundle::findLocaleFile($bundles[$i])) {
        $resources =& SRA_ResourceBundle::merge(SRA_ResourceBundle::getBundle($bundles[$i]), $resources);
      }
    }
    return $resources;
  }
  // }}}
		
  // {{{ getAppDateFormat
  /**
   * returns the "date-format" specified in the app-config for the current 
   * active app. if no "date-format" was specified in app-config, 
   * SRA_Controller::getSysDateFormat will be returned
   * @access public
   * @return 	String
   */
  function getAppDateFormat() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDateFormats = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDateFormats[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDateFormats[$_sraCurrentAppKey] = isset($conf['date-format']) ? $conf['date-format'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDateFormats[$_sraCurrentAppKey]) ? $_sraCachedAppDateFormats[$_sraCurrentAppKey] : SRA_Controller::getSysDateFormat();
  }
  // }}}
    
  // {{{ getAppDateOnlyFormat
  /**
   * returns the "date-only-format" specified in the app-config for the current 
   * active app. if no "date-only-format" was specified in app-config, 
   * SRA_Controller::getSysDateOnlyFormat will be returned
   * @access public
   * @return 	String
   */
  function getAppDateOnlyFormat() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppDateOnlyFormats = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppDateOnlyFormats[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppDateOnlyFormats[$_sraCurrentAppKey] = isset($conf['date-only-format']) ? $conf['date-only-format'] : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppDateOnlyFormats[$_sraCurrentAppKey]) ? $_sraCachedAppDateOnlyFormats[$_sraCurrentAppKey] : SRA_Controller::getSysDateOnlyFormat();
  }
  // }}}
    
  // {{{ getSysDateFormat
  /**
   * returns the "date-format" specified in the sierra-config. if no 
   * "date-format" has been specified in sierra-config the constant 
   * SRA_DEFAULT_DATE_FORMAT will be returned
   * @access public
   * @return 	String
   */
  function getSysDateFormat() {
    static $_sraCachedSysDateFormat;
    
    if (!isset($_sraCachedSysDateFormat)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysDateFormat = isset($conf['date-format']) ? $conf['date-format'] : SRA_DEFAULT_DATE_FORMAT;
    }
    return $_sraCachedSysDateFormat;
  }
  // }}}
    
  // {{{ getSysDateOnlyFormat
  /**
   * returns the "date-only-format" specified in the sierra-config. if no 
   * "date-only-format" has been specified in sierra-config the constant 
   * SRA_DEFAULT_DATE_ONLY_FORMAT will be returned
   * @access public
   * @return 	String
   */
  function getSysDateOnlyFormat() {
    static $_sraCachedSysDateOnlyFormat;
    
    if (!isset($_sraCachedSysDateOnlyFormat)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysDateOnlyFormat = isset($conf['date-only-format']) ? $conf['date-only-format'] : SRA_DEFAULT_DATE_ONLY_FORMAT;
    }
    return $_sraCachedSysDateOnlyFormat;
  }
  // }}}
    
  // {{{ getAppEntityModel
  /**
   * returns an associative array with the keys: 'name', 'path' for the 
   * entity model specified by $id
   * @param string $id the entity model to return
   * @access public
   * @return 	array
   */
  function &getAppEntityModel($id) {
    $models =& SRA_Controller::getAppEntityModels();
    return isset($models[$id]) ? $models[$id] : NULL;
  }
  // }}}
    
  // {{{ getAppEntityModels
  /**
   * returns the "use-entity-model" configuration for the current app. this will 
   * be an associative array indexed by entity-model key and the value is an 
   * associative array with the keys: 'name', 'path', 'resources', where 
   * resources is the path to the resource bundle for this workflow
   * @access public
   * @return 	array
   */
  function &getAppEntityModels() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppEntityModels = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppEntityModels[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppEntityModels[$_sraCurrentAppKey] = array();
      if (isset($conf['use-entity-model'])) {
        $keys = array_keys($conf['use-entity-model']);
        foreach($keys as $key) {
          $_sraCachedAppEntityModels[$_sraCurrentAppKey][$key] = array('path' => $conf['use-entity-model'][$key]['attributes']['path']);
          if (isset($conf['use-entity-model'][$key]['attributes']['resource'])) {
            if (isset($conf['use-entity-model'][$key]['attributes']['resources'])) {
              $resources =& SRA_ResourceBundle::getBundle($conf['use-entity-model'][$key]['attributes']['resources']);
              $_sraCachedAppEntityModels[$_sraCurrentAppKey][$key]['resources'] = $conf['use-entity-model'][$key]['attributes']['resources'];
            }
            else {
              $resources =& SRA_Controller::getAppResources();
            }
            $_sraCachedAppEntityModels[$_sraCurrentAppKey][$key]['name'] = SRA_ResourceBundle::isValid($resources) ? $resources->getString($conf['use-entity-model'][$key]['attributes']['resource']) : $conf['use-entity-model'][$key]['attributes']['resource'];
          }
        }
      }
    }
    $nl = NULL;
    return $_sraCurrentAppKey && isset($_sraCachedAppEntityModels[$_sraCurrentAppKey]) ? $_sraCachedAppEntityModels[$_sraCurrentAppKey] : $nl;
  }
  // }}}
  
  // {{{ getAppLatestVersion
  /**
   * returns the identifier of the latest version for the current application. 
   * returns NULL if no version info is available
   * @access public
   * @return string
   */
  function getAppLatestVersion() {
    if ($versions = SRA_Controller::getAppVersions()) {
      $keys = array_keys($versions);
      return $keys[0];
    }
  }
  // }}}
  
  // {{{ getAppSvnInfo
  /**
   * returns the subversion repository information for the current application. 
   * this value will be a hash with the following keys:
   *   password: the subversion repository password (optional)
   *   path:     the name of the framework/app subversion repository
   *   url:      the url to the subversion repository containing the framework/app
   *   user:     the subversion repository username (optional)
   * if this information has not been specified in SRA_VERSION_FILE, NULL will 
   * be returned
   * @param boolean $checkin whether or not to return the checkin subversion 
   * repository information (default is FALSE). for more information, see the 
   * API documentation for the SRA_VERSION_FILE constant
   * @access public
   * @return hash
   */
  function getAppSvnInfo($checkin=FALSE) {
    return $rb =& SRA_Controller::getAppVersionRb() ? SRA_Controller::_getSvnInfo($rb, $checkin) : NULL;
  }
  // }}}
  
  // {{{ getAppVersion
  /**
   * returns the version identifier of the current application. returns NULL 
   * if no version info is available
   * @access public
   * @return string
   */
  function getAppVersion() {
    if ($rb =& SRA_Controller::getAppVersionRb()) { return $rb->getString('version'); }
  }
  // }}}
  
  // {{{ getAppVersions
  /**
   * returns an array of versions available for the application. the return 
   * value is a hash indexed by version id where the value is a hash with the 
   * following keys:
   *   version: the version id
   *   readme:  the path or uri to the readme file for this version (optional)
   *   src:     the path or uri to the version source archive
   * @access public
   * @return hash
   */
  function getAppVersions() {
    if (($rb =& SRA_Controller::getAppVersionRb()) && $rb->getString('versions-info') && ($versions = SRA_File::propertiesFileToArray($rb->getString('versions-info'), 0, '', TRUE))) {
      if ($rb->getString('versions-readme')) { $versionsReadme = SRA_File::propertiesFileToArray($rb->getString('versions-readme'), 0, '', TRUE); }
      
      $keys = array_keys($versions);
      foreach($keys as $key) {
        $versions[$key] = array('version' => $key, 'src' => $versions[$key]);
        if ($versionsReadme && isset($versionsReadme[$key])) { $versions[$key]['readme'] = $versionsReadme[$key]; }
      }
      return $versions;
    }
  }
  // }}}
  
  // {{{ getAppVersionRb
  /**
   * returns a reference to the SRA_ResourceBundle instance representing the 
   * application version file (SRA_VERSION_FILE). returns NULL if no version 
   * file exists
   * @access public
   * @return SRA_ResourceBundle
   */
  function getAppVersionRb() {
    if (SRA_Controller::isAppInitialized() && file_exists($versionFile = SRA_Controller::getAppConfDir() . '/' . SRA_VERSION_FILE) && SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle($versionFile))) { return $rb; }
  }
  // }}}
  
  // {{{ getSysLatestVersion
  /**
   * returns the identifier of the latest version for the framework
   * @access public
   * @return string
   */
  function getSysLatestVersion() {
    if ($versions = SRA_Controller::getSysVersions()) {
      $keys = array_keys($versions);
      return $keys[0];
    }
  }
  // }}}
  
  // {{{ getSysSvnInfo
  /**
   * returns the subversion repository information for the framework. this value
   * will be a hash with the following keys:
   *   password: the subversion repository password (optional)
   *   path:     the name of the framework/app subversion repository
   *   url:      the url to the subversion repository containing the framework/app
   *   user:     the subversion repository username (optional)
   * @param boolean $checkin whether or not to return the checkin subversion 
   * repository information (default is FALSE). for more information, see the 
   * API documentation for the SRA_VERSION_FILE constant
   * @access public
   * @return hash
   */
  function getSysSvnInfo($checkin=FALSE) {
    return $rb =& SRA_Controller::getSysVersionRb() ? SRA_Controller::_getSvnInfo($rb, $checkin) : NULL;
  }
  // }}}
  
  // {{{ getSysVersion
  /**
   * returns the version identifier of the framework. returns NULL if no version 
   * info is available
   * @access public
   * @return string
   */
  function getSysVersion() {
    if ($rb =& SRA_Controller::getSysVersionRb()) { return $rb->getString('version'); }
  }
  // }}}
  
  // {{{ getSysVersions
  /**
   * returns an array of versions available for the framework. the return 
   * value is a hash indexed by version id where the value is a hash with the 
   * following keys:
   *   version: the version id
   *   readme:  the path or uri to the readme file for this version (optional)
   *   src:     the path or uri to the version source archive
   * @access public
   * @return hash
   */
  function getSysVersions() {
    if (($rb =& SRA_Controller::getSysVersionRb()) && $rb->getString('versions-info') && ($versions = SRA_File::propertiesFileToArray($rb->getString('versions-info'), 0, '', TRUE))) {
      if ($rb->getString('versions-readme')) { $versionsReadme = SRA_File::propertiesFileToArray($rb->getString('versions-readme'), 0, '', TRUE); }
      
      $keys = array_keys($versions);
      foreach($keys as $key) {
        $versions[$key] = array('version' => $key, 'src' => $versions[$key]);
        if ($versionsReadme && isset($versionsReadme[$key])) { $versions[$key]['readme'] = $versionsReadme[$key]; }
      }
      return $versions;
    }
  }
  // }}}
  
  // {{{ getSysVersionRb
  /**
   * returns a reference to the SRA_ResourceBundle instance representing the 
   * framework version file (SRA_VERSION_FILE)
   * @access public
   * @return SRA_ResourceBundle
   */
  function getSysVersionRb() {
    if (file_exists($versionFile = SRA_Controller::getSysConfDir() . '/' . SRA_VERSION_FILE) && SRA_ResourceBundle::isValid($rb =& SRA_ResourceBundle::getBundle($versionFile))) { return $rb; }
  }
  // }}}
    
  // {{{ getAppWorkflow
  /**
   * returns an associative array with the keys: 'name', 'path' for the workflow 
   * specified by $id
   * @param string $id the workflow to return
   * @param boolean $reload whether or not to force reload the workflows
   * @access public
   * @return 	array
   */
  function &getAppWorkflow($id, $reload=FALSE) {
    $workflows =& SRA_Controller::getAppWorkflows($reload);
    return isset($workflows[$id]) ? $workflows[$id] : NULL;
  }
  // }}}
    
  // {{{ getAppWorkflows
  /**
   * returns the "use-workflow" configuration for the current app. this will 
   * be an associative array indexed by entity-model key and the value is an 
   * associative array with the keys: 'name', 'path', 'resources', where 
   * resources is the path to the resource bundle for this workflow
   * @param boolean $reload whether or not to force reload the workflows
   * @access public
   * @return 	array
   */
  function &getAppWorkflows($reload=FALSE) {
    global $_sraCurrentAppKey;
    static $_sraCachedAppWorkflows = array();
    
    if ($_sraCurrentAppKey && ($reload || !isset($_sraCachedAppWorkflows[$_sraCurrentAppKey]))) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppWorkflows[$_sraCurrentAppKey] = array();
      if (isset($conf['use-workflow'])) {
        $keys = array_keys($conf['use-workflow']);
        foreach($keys as $key) {
          $_sraCachedAppWorkflows[$_sraCurrentAppKey][$key] = array('path' => $conf['use-workflow'][$key]['attributes']['path']);
          if (isset($conf['use-workflow'][$key]['attributes']['resource'])) {
            if (isset($conf['use-workflow'][$key]['attributes']['resources'])) {
              $resources =& SRA_ResourceBundle::getBundle($conf['use-workflow'][$key]['attributes']['resources']);
              $_sraCachedAppWorkflows[$_sraCurrentAppKey][$key]['resources'] = $conf['use-workflow'][$key]['attributes']['resources'];
            }
            else {
              $resources =& SRA_Controller::getAppResources();
            }
            $_sraCachedAppWorkflows[$_sraCurrentAppKey][$key]['name'] = SRA_ResourceBundle::isValid($resources) ? $resources->getString($conf['use-workflow'][$key]['attributes']['resource']) : $conf['use-workflow'][$key]['attributes']['resource']; 
          }
        }
      }
    }
    $nl = NULL;
    return $_sraCurrentAppKey && isset($_sraCachedAppWorkflows[$_sraCurrentAppKey]) ? $_sraCachedAppWorkflows[$_sraCurrentAppKey] : $nl;
  }
  // }}}
		
  // {{{ getUserLocale
  /**
   * Returns the preferred user locale as determined from the 
   * $_SERVER['HTTP_ACCEPT_LANGUAGE'] global variable and the users' 
   * predefined browser language settings. if the locales cannot be determined
   * then the default app SRA_Locale will be returned. Only the first (the 
   * highest priority) SRA_Locale specified in the user's browser settings will 
   * be returned
   * @access 	public
   * @return 	SRA_Locale
   */
  function &getUserLocale() {
    $locales =& SRA_Controller::getUserLocales();
    return $locales[0];
  }
  // }}}
  
  // {{{ getUserLocales
  /**
   * Returns the preferred user locales as determined from the 
   * $_SERVER['HTTP_ACCEPT_LANGUAGE'] global variable and the users' 
   * predefined browser language settings. if the locales cannot be determined
   * then the default app SRA_Locale will be returned
   * @access 	public
   * @return 	SRA_Locale[]
   */
  function &getUserLocales() {
    global $_sraCurrentAppKey;
    static $_sraCachedUserLocales;
    
    if (!isset($_sraCachedUserLocales)) {
      if ($_SERVER['HTTP_ACCEPT_LANGUAGE']) {
        $locs = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($locs as $loc) {
          $loc = explode(',', $loc);
          $setLocales = array();
          foreach ($loc as $l) {
            if (preg_match('/[A-Za-z]{2}-[A-Za-z]{2}/', $l) || preg_match('/[A-Za-z]{2}/', $l)) {
              if (!SRA_Error::isError(SRA_Locale::getLocale($l))) {
                $locale =& SRA_Locale::getLocale($l);
                if (!isset($setLocales[$locale->getId()])) {
                  $_sraCachedUserLocales[] = $locale;
                  $setLocales[$locale->getId()] = TRUE;
                }
              }
            }
          }
        }
        // eliminate duplicates
        $_sraCachedUserLocales =& SRA_Locale::removeDuplicateLocales($_sraCachedUserLocales);
      }
      
      if (!count($_sraCachedUserLocales)) {
        $_sraCachedUserLocales = array(SRA_Controller::getAppLocale());
      }
    }
    return $_sraCachedUserLocales;
  }
  // }}}
  
  // {{{ init
  /**
   * This method should be called before any other app specific methods. It 
   * requires a app identifier key that specifies which app config information 
   * identified in the system etc file to use. If a valid key is not provided or 
   * if any other errors occur this method will return an SRA_Error object.
   *
   * Whenever the init method is called, any existing static _db or _template 
   * references will be destroyed. This will allow the app object to be called 
   * multiple times within one process for different apps. returns TRUE if the 
   * application was initialized, FALSE if was already initialized, and an error 
   * object if $appKey is not valid or another error occurs
   * @param string $appKey the name of the application to initialize OR the php 
   * source file invoking the initialization (must exist within the application 
   * directory)
   * @param $ignoreAuth boolean whether or not to ignore any authentication 
   * constraints
   * @param $skipEntityModels boolean whether or not to skip entity model 
   * initialization
   * @param boolean $skipLicensing whether or not to skip the licensing step if 
   * applicable
   * @param boolean $skipBrowserCompatibility whether or not to skip the browser 
   * compatibility check
   * @access public
   * @return  boolean
   */
  function init($appKey, $ignoreAuth=FALSE, $skipEntityModels=FALSE, $skipLicensing=FALSE, $skipBrowserCompatibility=FALSE) {
    global $_sraCurrentAppKey;
    global $_sraInitializedApps;
    if (!$_sraInitializedApps) { $_sraInitializedApps = array(); }
    
    if (file_exists($appKey) && SRA_Util::beginsWith($appKey, SRA_DIR . '/' . SRA_APP_DIR_NAME . '/')) {
      $tmp = explode('/', str_replace(SRA_DIR . '/' . SRA_APP_DIR_NAME . '/', '', $appKey));
      $appKey = $tmp[0];
    }
    if ($appKey != $_sraCurrentAppKey) {
      $_sraCurrentAppKey = $appKey;
      
      // add app lib directory
      $basePath = ini_get('include_path');
      if (!strstr($basePath, SRA_Controller::getAppLibDir())) {
        if (!strstr(ini_get('include_path'), SRA_LIB_DIR)) {
          $basePath .= ($basePath ? SRA_PATH_SEPARATOR : '') . SRA_Controller::getSysLibDir() . SRA_PATH_SEPARATOR . SRA_Controller::getSysTplDir();
        }
        ini_set('include_path', $basePath . SRA_PATH_SEPARATOR . SRA_Controller::getAppLibDir() . SRA_PATH_SEPARATOR . SRA_Controller::getAppTplDir());
      }
      
      if (!$_sraInitializedApps[$appKey]) {
        
        // Initialize the system configuration
        if (SRA_Error::isError($conf =& SRA_Controller::getSysConf())) {
          return SRA_Error::logError('SRA_Controller::init: Failed - could not load system configuration', __FILE__, __LINE__);
        }
  
        // Initialize the app configuration.
        if (SRA_Error::isError($appConf =& SRA_Controller::getAppConf($appKey))) {
          return SRA_Error::logError('SRA_Controller::init: Failed - could not load app configuration', __FILE__, __LINE__);
        }
        
        // before includes
        if (isset($appConf['include-before'])) {
          foreach (explode(' ', $appConf['include-before']) as $include) {
            if ($include) { include_once($include); }
          }
        }
        
        // initialize entity models
        if (!$skipEntityModels && is_array($econf = SRA_Controller::getAppConfAttr(SRA_ENTITY_MODELER_CONFIG_KEY))) {
          require_once(SRA_LIB_DIR . '/model/SRA_EntityModeler.php');
          foreach (array_keys($econf) as $key) {
            SRA_EntityModeler::init($appKey, $key);
          }
        }
        
        // authenticate
        if (!$ignoreAuth && (isset($conf['restrict-access']) || isset($appConf['restrict-access']))) {
          require_once(SRA_LIB_DIR . '/auth/SRA_Authenticator.php');
          if (!SRA_Authenticator::authenticate($conf, $appConf)) {
            if (SRA_Controller::runningFromCli()) {
              $rb =& SRA_Controller::getSysResources();
              echo "\n" . $rb->getString('console.auth.error') . "\n\n";
            }
            exit;
          }
        }
        
        // after includes
        if (isset($appConf['include'])) {
          foreach (explode(' ', $appConf['include']) as $include) {
            if ($include) { include_once($include); }
          }
        }
        
        // browser compatibility
        if (!$skipBrowserCompatibility && !SRA_Controller::runningFromCli() && $appConf['browsers-supported']) {
          $tpl =& SRA_Controller::getAppTemplate();
          $pieces = explode('?', $appConf['invalid-browser-tpl']);
          $template = $pieces[0];
          if (!$appConf['invalid-browser-tpl'] || !$tpl->validate($template)) {
            $msg = 'SRA_Controller::init: Failed - Invalid browser template ' . $appConf['invalid-browser-tpl'] . ' is not valid for app ' . $appKey;
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
          }
          $matched = FALSE;
          foreach(explode(':', $appConf['browsers-supported']) as $regex) {
            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
              $matched = TRUE;
              break;
            }
          }
          if (!$matched) {
            $tpl->display($appConf['invalid-browser-tpl']);
            exit;
          }
        }
        
        // licensing
        if (!$skipLicensing && $appConf['license-tpl']) {
          $tpl =& SRA_Controller::getAppTemplate();
          $dataDir = $appConf['license-data-dir'] ? (is_dir($appConf['license-data-dir']) ? $appConf['license-data-dir'] : SRA_Controller::getAppDir() . '/' . $appConf['license-data-dir']) : SRA_Controller::getAppTmpDir();
          if (!$tpl->validate($appConf['license-tpl'])) {
            $msg = 'SRA_Controller::init: Failed - License template ' . $appConf['license-tpl'] . ' is not valid for app ' . $appKey;
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
          }
          else if (!$appConf['license-tpl-cli'] || !$tpl->validate($appConf['license-tpl-cli'])) {
            $msg = 'SRA_Controller::init: Failed - License template ' . $appConf['license-tpl-cli'] . ' is not valid for app ' . $appKey;
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
          }
          else if ($appConf['license-data-dir'] && (!is_dir($dataDir) || !is_writable($dataDir))) {
            $msg = 'SRA_Controller::init: Failed - License data directory ' . $dataDir . ' does not exist or is not writable for app ' . $appKey;
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
          }
          $licenseFile = $dataDir . '/' . SRA_LICENSE_PREFIX . $appKey . ($appConf['license-per-user'] && $_SERVER['PHP_AUTH_USER'] ? '-' . $_SERVER['PHP_AUTH_USER'] : '');
          $licenseVar = $appConf['license-accept-var'] ? $appConf['license-accept-var'] : SRA_LICENSE_VAR;
          if (!file_exists($licenseFile)) {
            if ((!$appConf['license-tpl-get'] && $_POST && $_POST[$licenseVar]) || ($appConf['license-tpl-get'] && $_GET && $_GET[$licenseVar])) {
              SRA_File::touch($licenseFile);
            }
            else {
              if (SRA_Controller::runningFromCli()) {
                $tpl->display($appConf['license-tpl-cli']);
                $rb =& SRA_Controller::getSysResources();
                $response = strtolower(SRA_Util::cliPrompt($rb->getString('license.accept'), TRUE, array('y', 'n')));
                if ($response == 'y') {
                  SRA_File::touch($licenseFile);
                }
                else {
                  echo "\n" . $rb->getString('license.notAccepted') . "\n\n";
                  exit;
                }
              }
              else {
                $tpl->display($appConf['license-tpl']);
                exit;
              }
            }
          }
        }
      }
      // set new time zone
      SRA_TimeZone::setTzEnvVar(SRA_Controller::getAppTimeZone());
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
	
  // {{{ isAppInitialized
  /**
   * returns TRUE if $app has been initialized, FALSE otherwise 
   * @param string app the app to check. if not specified, returns TRUE if ANY 
   * app has been initialized
   * @access public
   * @return  boolean
   */
  function isAppInitialized($app=NULL) {
    global $_sraInitializedApps;
    return isset($_sraInitializedApps) && (!$app || $_sraInitializedApps[$app]);
  }
  // }}}
	
  // {{{ appKeyIsValid
  /**
   * This method returns true if the app key specified is a valid app key
   * @param	appKey : String - The app key to validate. 
   * @access public
   * @return  boolean
   */
  function appKeyIsValid($appKey) {
    static $_sraCachedAppKeysValid = array();
    
    if (!isset($_sraCachedAppKeysValid[$appKey])) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedAppKeysValid[$appKey] = isset($conf['app'][$appKey]);
    }
    return $_sraCachedAppKeysValid[$appKey];
  }
  // }}}
		
  // {{{ getRunTime
  /**
   * returns the runtime of the current php process in seconds
   * @param int $decimalPrecision the decimal precision for the microseconds 
   * portion of the return value
   * @access public
   * @return float
   */
  function getRunTime($decimalPrecision=2) {
    return number_format(microtime(TRUE) - SRA_START_TIME, $decimalPrecision);
  }
  // }}}
    
  // {{{ getRequestUri
  /**
   * Returns the full request uri
   * @param boolean $includeProtocol whether or not to include the protocol 
   * prefix as in (http:// or https://)
   * @param boolean $excludeServer whether or not to excluse the server (return 
   * value will be a relative uri)
   * @access public
   * @return  string
   */
  function getRequestUri($includeProtocol=TRUE, $excludeServer=FALSE) {
    return ($excludeServer ? '' : SRA_Controller::getServerUri($includeProtocol)) . $_SERVER['REQUEST_URI'];
  }
  // }}}
    
  // {{{ getServerName
  /**
   * Returns the server name
   * @access public
   * @return  string
   */
  function getServerName() {
    // look for HTTP_X_FORWARDED_HOST header (from apigee)
    return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME'];
  }
  // }}}
    
  // {{{ getServerUri
  /**
   * Returns the base server uri
   * @param boolean $includeProtocol whether or not to include the protocol 
   * prefix as in (http:// or https://)
   * @access public
   * @return  string
   */
  function getServerUri($includeProtocol=TRUE) {
    return ($includeProtocol ? (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') : '') . SRA_Controller::getServerName();
  }
  // }}}
    
  // {{{ hasAppDb
  /**
   * returns TRUE if an application database has been defined
   * @access 	public
   * @return 	boolean
   */
  function hasAppDb() {
    global $_sraCurrentAppKey;
    static $_sraCachedAppHasDbs = array();
    
    if ($_sraCurrentAppKey && !isset($_sraCachedAppHasDbs[$_sraCurrentAppKey])) {
      $conf =& SRA_Controller::getAppConf();
      $_sraCachedAppHasDbs[$_sraCurrentAppKey] = isset($conf['db']) && count($conf['db']) ? TRUE : NULL;
    }
    return $_sraCurrentAppKey && isset($_sraCachedAppHasDbs[$_sraCurrentAppKey]) ? $_sraCachedAppHasDbs[$_sraCurrentAppKey] : SRA_Controller::hasSysDb();
  }
  // }}}
  
  // {{{ hasSysDb
  /**
   * returns TRUE if a system database has been defined
   * @access 	public
   * @return 	boolean
   */
  function hasSysDb() {
    global $_sraCachedSysHasDb;
    
    if (!isset($_sraCachedSysHasDb)) {
      $conf =& SRA_Controller::getSysConf();
      $_sraCachedSysHasDb = isset($conf['db']) && count($conf['db']) ? TRUE : FALSE;
    }
    return $_sraCachedSysHasDb;
  }
  // }}}
  
  // {{{ isSecure
  /**
   * Returns TRUE if the current connection is secure
   * @access public
   * @return  boolean
   */
  function isSecure() {
    return isset($_SERVER['HTTPS']) ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ runningFromCli
  /**
   * returns TRUE when the framework is being from the the command line (not 
   * running from an http request)
   * @access public
   * @return  boolean
   */
  function runningFromCli() {
    return isset($_SERVER['HTTP_HOST']) ? FALSE : TRUE;
  }
  // }}}
  
  // {{{ runningFromConsole
  /**
   * returns TRUE when the framework is being from the the console 
   * (bin/sra-console.php) 
   * @access public
   * @return  boolean
   */
  function runningFromConsole() {
    return defined('SRA_CONSOLE') && SRA_CONSOLE;
  }
  // }}}
  
  // {{{ _compressOutput
  /**
   * starts output buffering and compression where supported. this will 
   * result in the entire output being buffered until completion, and then 
   * output in a compressed (gzip) format to the browser
   * @access 	public
   * @return 	void
   */
  function _compressOutput() {
    global $_sraCompressionInProgress;
    if (!$_sraCompressionInProgress && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('gzencode')) {
      $_sraCompressionInProgress = TRUE;
      header('Content-Encoding: gzip');
      ob_start('gzencode');
    }
  }
  // }}}
  
  // {{{ _getSvnInfo
  /**
   * returns the subversion repository information for the $rb specified. this 
   * value will be a hash with the following keys:
   *   password: the subversion repository password (optional)
   *   path:     the name of the framework/app subversion repository
   *   url:      the url to the subversion repository containing the framework/app
   *   user:     the subversion repository username (optional)
   * @param boolean $checkin whether or not to return the checkin subversion 
   * repository information
   * @access public
   * @return hash
   */
  function _getSvnInfo(&$rb, $checkin) {
    $svnInfo = array();
    if ($rb->containsKey('svn-path') && $rb->containsKey('svn-url')) { 
      $svnInfo['path'] = $checkin && $rb->containsKey('svn-ci-path') ? $rb->getString('svn-ci-path') : $rb->getString('svn-path');
      $svnInfo['url'] = $checkin && $rb->containsKey('svn-ci-url') ? $rb->getString('svn-ci-url') : $rb->getString('svn-url');
      if ($rb->containsKey('svn-user') || ($checkin && $rb->containsKey('svn-ci-user'))) { $svnInfo['user'] = $checkin && $rb->containsKey('svn-ci-user') ? $rb->getString('svn-ci-user') : $rb->getString('svn-user'); }
      if ($rb->containsKey('svn-password') || ($checkin && $rb->containsKey('svn-ci-password'))) { $svnInfo['password'] = $checkin && $rb->containsKey('svn-ci-password') ? $rb->getString('svn-ci-password') : $rb->getString('svn-password'); }
    }
    return count($svnInfo) ? $svnInfo : NULL;
  }
  // }}}
		
  // {{{ _populateDefaultDbConf()
  /**
   * Used to populate default etc values in a db etc element of a app 
   * or system configuration
   * @param	array $conf the default etc to populate 
   * @access private
   */
  function &_populateDefaultDbConf($conf)
  {
    if (isset($conf['attributes'])) {
      $conf = array_merge($conf, $conf['attributes']);
    }
    unset($conf['attributes']);
    if (!array_key_exists('type', $conf)) {
      $conf['type'] = SRA_DB_TYPE_MYSQL;
    }
    if (!array_key_exists('host', $conf)) {
      $conf['host'] = 'localhost';
    }
    if (!array_key_exists('port', $conf)) {
      $conf['port'] = SRA_Database::getDefaultPort($conf['type']);
    }
    if (!array_key_exists('error-level', $conf)) {
      $conf['error-level'] = SRA_ERROR_PROBLEM;
    }
    return $conf;
  }
  // }}}
	
 // {{{ registerShutdownMethod
  /**
   * This method is used to register an object method that should be called when the end 
   * of the current server process ends. This may be useful in order to perform any object 
   * specific cleanup or shutdown operations such as closing a connection to a database or 
   * an open file pointer. An SRA_Error will be returned if the method specified is not part 
   * of the object. 
   * @param	class : Object - The class object or class name to which the shutdown method 
   * call should be made. If this is a string, the method call will be made static 
   * (i.e. SRA_Database::close), otherwise it will be called through the instantiated object 
   * (i.e. $database->close). This parameter must be passed by reference as a variable. 
   * Constants cannot be used (i.e. "Test" would not work, instead, use  $temp = "Test").
   * This is required in order to maintain reference to objects.
   * @param	method : String - The class or object method that should be called.
   * @param	params : Object[] - An optional array of method import parameters. Each element 
   * within the array will be passed as a separate import parameter where the first element 
   * in the array will be the first parameter. The array may contain scalar or non-scalar 
   * parameters. If this parameter is not specified, no import parameters will be passed to 
   * the method. 
   * @param	shutdown : boolean - Private attribute used to specify that the shutdown 
   * methods should be called. This  parameter should NEVER be set except by the internal 
   * app shutdown function
   * @access 	public
   * @return 	void
   */
  function registerShutdownMethod(& $obj, $methodName, $params = NULL, $shutdown = FALSE) {
    /**
     * A static array consisting of all of the objects and corresponding object methods 
     * that have been registered to be called when the application process ends through 
     * the static SRA_Controller::registerShutdownMethod method (see api for this function 
     * for more info). 
     */
    static $_shutdownMethods = array();
    
    if (!$shutdown && !$_shutdownMethods) {
      // Register process shutdown function (cannot be a class function)
      register_shutdown_function('app__shutdownApp__');
    }
    
    $conf =& SRA_Controller::getSysConf();
    
    // make sure that the task scheduler is running
    if (!defined('SRA_TASK_SCHEDULER_DEBUG') && $shutdown) {
      global $_taskSchedulerInvoked;
      $scheduler = SRA_DIR . '/' . SRA_BIN_DIR_NAME . '/sra-task-scheduler.php';
      if (isset($conf['disable-scheduler']) && $conf['disable-scheduler'] == '0' && !$_taskSchedulerInvoked && !($pid = SRA_Util::getProcessId($scheduler))) {
        $_taskSchedulerInvoked = TRUE;
        exec($scheduler . ' > /dev/null 2>&1 &');
      }
    }
  
    // Call shutdown methods
    if ($shutdown)
    {
      // Debug message
      if (SRA_CONTROLLER_DEBUG)
      {
        $msg = 'SRA_Controller::registerShutdownMethod: Shutting down application.';
        SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
      }
      
      $keys = array_reverse(array_keys($_shutdownMethods));
      foreach ($keys as $key)
      {
        // Create parameters variable
        $params = '';
        
        // Convert to array if necessary
        if ($_shutdownMethods[$key][2] && !is_array($_shutdownMethods[$key][2]))
        {
          $temp =& $_shutdownMethods[$key][2];
          $_shutdownMethods[$key][2] = array(& $temp);
        }
        
        if (is_array($_shutdownMethods[$key][2]))
        {
          $pkeys = array_keys($_shutdownMethods[$key][2]);
          foreach ($pkeys as $pkey)
          {
            if ($params != '')
            {
              $params .= ', ';
            }
            $params .= "\$_shutdownMethods[$key][2][$pkey]";
          }
        }
        
        // Instance method
        if (is_object($_shutdownMethods[$key][0]))
        {
          // Debug message
          if (SRA_CONTROLLER_DEBUG)
          {
            $msg = 'SRA_Controller::registerShutdownMethod: Calling instance method: \'' . 
                $_shutdownMethods[$key][1] . '\' for object of type: \'' . 
                get_class($_shutdownMethods[$key][0]) . '\'';
            SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
          }
          
          eval("\$_shutdownMethods[\$key][0]->{\$_shutdownMethods[\$key][1]}($params);");
        }
        // Static method
        else
        {
          // Debug message
          if (SRA_CONTROLLER_DEBUG)
          {
            $msg = 'SRA_Controller::registerShutdownMethod: Calling static method: \'' . 
                $_shutdownMethods[$key][1] . '\' for class: \'' . $_shutdownMethods[$key][0] . '\'';
            SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
          }
          
          eval($_shutdownMethods[$key][0] . '::' . $_shutdownMethods[$key][1] . "($params);");
        }
      }
      return;
    }
    
    // Validate parameters
    
    if (is_object($obj))
    {
      // Debug message
      if (SRA_CONTROLLER_DEBUG)
      {
        $msg = 'SRA_Controller::registerShutdownMethod: Registering instance method: \'' . 
            $methodName . '\' for object of type: \'' . 
            get_class($obj) . '\'';
        SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
      }
      
      // Check if method exists
      if (!method_exists($obj, $methodName) && !method_exists($obj, strtolower($methodName)))
      {
        $msg = 'SRA_Controller::registerShutdownMethod: Failed - Invalid method name \'' . $methodName . 
             '\' for object of type: \'' . get_class($obj) . '\'';
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
      }
    }
    else
    {
      // Debug message
      if (SRA_CONTROLLER_DEBUG)
      {
        $msg = 'SRA_Controller::registerShutdownMethod: Registering static method: \'' . 
            $methodName . '\' for class: \'' . $obj . '\'';
        SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
      }
      
      // Check if class and method exists
      if ((!class_exists($obj) && !class_exists(strtolower($obj))) || (!in_array($methodName, get_class_methods($obj)) && !in_array($methodName, get_class_methods(strtolower($obj))))) {
        $msg = 'SRA_Controller::registerShutdownMethod: Failed - Invalid method or class name. Method: \'' . $methodName . 
             '\' Class: \'' . $obj . '\'';
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
      }
    }
    
    // Debug message
    if (SRA_CONTROLLER_DEBUG)
    {
      $msg = "SRA_Controller::registerShutdownMethod: Method '$methodName' registered successfully";
      SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
    }
    
    // Register shutdown method
    $_shutdownMethods[] = array(& $obj, $methodName, $params);
  }
  // }}}

}
// }}}
?>
