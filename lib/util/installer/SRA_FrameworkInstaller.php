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
require_once('sql/SRA_Database.php');
require_once('model/SRA_EntityModeler.php');
// }}}

// {{{ Constants
/**
 * the name of the apache configuration file in the application's etc directory
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APACHE_CONF', 'httpd.conf');

/**
 * the name of the default apache conf.d directory
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APACHE_CONF_D', '/etc/httpd/conf.d');

/**
 * the name of the file used to store app install exec commands
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APP_INSTALL', '.install');

/**
 * the name of the file used to store app release exec commands
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APP_RELEASE', '.release');

/**
 * the name of the file used to store app uninstall exec commands
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL', '.uninstall');

/**
 * the prefix for commands in .uninstall that were added automatically by the 
 * installer. these commands are removed when the the application is 
 * unconfigured
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL_AUTO', '==');

/**
 * the name of the installer xml file stored in the framework or app etc 
 * directory
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_CONFIG', 'installer.xml');

/**
 * the header to include when rendering the framework configuration
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_APP_HEADER', "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<!DOCTYPE app-config PUBLIC \"-//SIERRA//DTD APP CONFIG//EN\"\n  \"http://sierra-php.googlecode.com/svn/trunk/etc/app-config.dtd\">\n");

/**
 * the name of the file where a release files should be listed
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST', '.version-files');

/**
 * the default name for a framework release
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_NAME', 'sierra');

/**
 * the name of the directory to use to create a framework release
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME', '/tmp/.sra-release');
// }}}

// {{{ SRA_FrameworkInstaller
/**
 * sierra framework installer handler. this class handles all of the 
 * installation actions defined in sierra/etc/installer.xml
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util.installer
 */
class SRA_FrameworkInstaller {
  // public attributes
  /**
   * a reference to the installer instance (automatically set)
   * @type SRA_Installer
   */
  var $installer;
  
  // private attributes
  /**
   * the identifier of the application currently selected
   * @type string
   */
  var $_app;
  
  /**
   * a reference to an SRA_XmlParser instances for applications
   * @type SRA_XmlParser[]
   */
  var $_appConfigs = array();
  
  /**
   * the identifier of the database currently selected (in the existing database 
   * menu)
   * @type string
   */
  var $_db;
  
  /**
   * a reference to an SRA_XmlParser for the framework configuration 
   * (sierra-config.xml)
   * @type SRA_XmlParser
   */
  var $_sierraConfig;
  
  
  // {{{ SRA_FrameworkInstaller
  /**
   * constructor for the sierra framework installer
   * @access public
   */
  function SRA_FrameworkInstaller() {
    if (file_exists($confFile = SRA_DIR . '/' . SRA_SYS_CONFIG)) {
      $this->_sierraConfig =& SRA_XmlParser::getXmlParser($confFile, TRUE);
    }
  }
  // }}}
  
  // {{{ appHasSvnRepository
  /**
   * returns TRUE if the current application has an svn repository
   * @access public
   * @return boolean
   */
  function appHasSvnRepository() {
    return $this->isSvnInstalled() && $this->isAppConfigured() && SRA_Controller::getAppSvnInfo();
  }
  // }}}
  
  // {{{ builderGetApps
  /**
   * "builder" method that returns the current application identifiers
   * @access public
   * @return array
   */
  function builderGetApps() {
    $options = array();
    $config = $this->_sierraConfig->getData(array('sierra-config', 'app'));
    $keys = array_keys($config);
    foreach($keys as $key) {
      $options[] = array('key' => $key);
    }
    return $options;
  }
  // }}}
  
  // {{{ builderGetDbOptions
  /**
   * "builder" method that returns the current database configuration options
   * @access public
   * @return array
   */
  function builderGetDbOptions() {
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    $options = array();
    $data = $config->getData(array($rootElement, 'db'));
    $keys = array_keys($data);
    foreach($keys as $key) {
      $options[] = array('key' => $key);
    }
    return $options;
  }
  // }}}
  
  // {{{ builderGetAppVersions
  /**
   * "builder" method that returns the app version options
   * @access public
   * @return array
   */
  function builderGetAppVersions() {
    $options = array();
    if ($versions =& SRA_Controller::getAppVersions()) {
      $keys = array_keys($versions);
      foreach($keys as $key) {
        if ($key != SRA_Controller::getAppLatestVersion()) {
          $options[] = array('key' => $key, 'resource' => $this->installer->resources->getString('installer.update.specificVersion', array('version' => $key)) . ($key == SRA_Controller::getAppVersion() ? ' ' . $this->installer->resources->getString('installer.update.current') : ''));
        }
      }
    }
    return $options;
  }
  // }}}
  
  // {{{ builderGetVersions
  /**
   * "builder" method that returns the framework version options
   * @access public
   * @return array
   */
  function builderGetVersions() {
    $options = array();
    if ($versions =& SRA_Controller::getSysVersions()) {
      $keys = array_keys($versions);
      foreach($keys as $key) {
        if ($key != SRA_Controller::getSysLatestVersion()) {
          $options[] = array('key' => $key, 'resource' => $this->installer->resources->getString('installer.update.specificVersion', array('version' => $key)) . ($key == SRA_Controller::getSysVersion() ? ' ' . $this->installer->resources->getString('installer.update.current') : ''));
        }
      }
    }
    return $options;
  }
  // }}}
  
  // {{{ configure
  /**
   * "handler" method for the "installer.root.configure" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configure($menuItem, $data) {
    $this->resetApp();
    
    // create configuration files
    $defaultConfigs = SRA_File::getFileList(SRA_Controller::getSysConfDir(), '/^.*-default.*$/', TRUE);
    foreach($defaultConfigs as $defaultConfig) {
      $config = str_replace('-default', '', $defaultConfig);
      if (!file_exists($config)) {
        echo $this->installer->resources->getString('installer.root.configure.file', array('file' => $config)) . "\n";
        SRA_File::copy($defaultConfig, $config); 
      }
    }
    // update php path
    if ($phpBin = SRA_File::findInPath('php')) {
      foreach(SRA_File::getFileList(SRA_DIR . '/' . SRA_BIN_DIR_NAME, '/^.*\.php$/') as $script) {
        $lines = file($script);
        for($i=0; $i<count($lines); $i++) {
          if (SRA_Util::beginsWith($lines[$i], '<?php')) { break; }
          unset($lines[$i]);
        }
        $lines = '#!' . $phpBin . " -q\n" . implode('', $lines);
        SRA_File::write($script, $lines);
        if ($verbose) { echo $this->installer->resources->getString('installer.root.configure.updateFile', array('file' => $script)) . "\n"; }
      }
    }
    if (!$this->_sierraConfig) {
      echo $this->installer->resources->getString('installer.root.configure.file', array('file' => SRA_DIR . '/' . SRA_SYS_CONFIG)) . "\n";
      $this->_sierraConfig =& SRA_XmlParser::getXmlParser(SRA_DIR . '/' . SRA_SYS_CONFIG, TRUE, TRUE, 'sierra-config');
    }
    $logDir = isset($data['log-dir']) ? $data['log-dir'] : SRA_DEFAULT_LOG_DIR;
    if (substr($logDir, 0, 1) != '/') { $logDir = SRA_INSTALLER_FRAMEWORK_DIR . '/' . $logDir; }
    if (!is_dir($logDir)) {
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $logDir)) . "\n";
      mkdir($logDir) ? chmod($logDir, 0777) : $this->installer->terminate($this->installer->resources->getString('installer.root.configure.error.logDir', array('logDir' => $logDir)));
    }
    $appDir = SRA_DIR . '/' . SRA_APP_DIR_NAME;
    if (!is_dir($appDir)) {
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $appDir)) . "\n";
      mkdir($appDir); 
    }
    
    $baseData = $this->_sierraConfig->getData();
    if (isset($baseData['sierra-config']['attributes'])) { unset($baseData['sierra-config']['attributes']); }
    $baseData = isset($baseData['sierra-config']) ? $baseData['sierra-config'] : array();
    $baseData['attributes'] = $data;
    $data = array('sierra-config' => $baseData);
    $this->_sierraConfig->setData($data);
    $this->_sierraConfig->write(NULL, SRA_FRAMEWORK_INSTALLER_HEADER);
    
    return $this->installer->resources->getString('installer.root.configure.success');
  }
  // }}}
  
  // {{{ configureApp
  /**
   * "handler" method for the "installer.configureApp.configure" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureApp($menuItem, $data) {
    $result = 'installer.configureApp.error';
    $cmdsOutput = '';
    if ($appConf =& $this->_getAppConf()) {
      $this->_populateAppDefaults($data);
      
      $config =& $appConf->getData();
      foreach($data as $key => $val) {
        if ($val != $this->defaultsConfigureAppNew(NULL, $key, $data)) {
          if (!is_array($config['app-config']['attributes'])) $config['app-config']['attributes'] = array();
          $config['app-config']['attributes'][$key] = $val;
        }
      }
      $appConf->setData($config);
      $appConf->write(NULL, SRA_FRAMEWORK_INSTALLER_APP_HEADER);
      $this->_runAppInstallerCmds(SRA_FRAMEWORK_INSTALLER_APP_INSTALL);
      SRA_Controller::init($this->_app, TRUE, TRUE, TRUE);
      $result = 'installer.configureApp.success';
    }
    return $this->installer->resources->getString($result, array('app' => $this->_app, 'cmdsOutput' => $cmdsOutput));
  }
  // }}}
  
  // {{{ configureAppApache
  /**
   * "handler" method for the "installer.configureApp.apache" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureAppApache($menuItem, $data) {
    $err = FALSE;
    $msg = '';
    $htmlDir = SRA_Controller::getAppDir() . '/' . SRA_DEFAULT_HTML_DIR;
    $conf = SRA_Controller::getAppConfDir() . '/' . SRA_FRAMEWORK_INSTALLER_APACHE_CONF;
    $fp = fopen($conf, 'w');
    if ($data['vhost']) {
      fwrite($fp, '<Directory "' . $htmlDir . '">' . "\n");
      fwrite($fp, "  Options Indexes MultiViews\n  AllowOverride None\n  Order allow,deny  \n  Allow from all\n</Directory>\n\n");
      fwrite($fp, '<VirtualHost ' . ($data['ip'] ? $data['ip'] : '*') . ":80>\n");
      $vhost = '  DocumentRoot ' . $htmlDir . "\n";
      $vhost .= '  ServerName ' . $data['hostname'] . "\n";
      if ($data['alias']) {
        foreach(explode(',', $data['alias']) as $alias) {
          $vhost .= '  ServerAlias ' . trim($alias) . "\n";
        }
      }
      if ($data['accesslog']) {
        $data['accesslog'] = SRA_Util::beginsWith($data['accesslog'], '/') ? $data['accesslog'] : SRA_Controller::getAppLogDir() . '/' . $data['accesslog'];
        $vhost .= '  CustomLog "' . $data['accesslog'] . '" common' . "\n"; 
      }
      if ($data['errorlog']) {
        $data['errorlog'] = SRA_Util::beginsWith($data['errorlog'], '/') ? $data['errorlog'] : SRA_Controller::getAppLogDir() . '/' . $data['errorlog'];
        $vhost .= '  ErrorLog "' . $data['errorlog'] . "\"\n"; 
      }
    }
    else {
      $vhost .= ($data['vhost'] ? '  ' : '') . 'Alias ' . (SRA_Util::beginsWith($data['alias-dir'], '/') ? '' : '/') . $data['alias-dir'] . ' "' . SRA_Controller::getAppHtmlDir() . '/"' . "\n";
    }
    if ($data['alias-ws']) { 
      $vhost .= ($data['vhost'] ? '  ' : '') . 'Alias /sra-ws-gateway.php "' . SRA_Controller::getSysLibDir() . "/model/sra-ws-gateway.php\"\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php?ws1=$1&ws2=$2&ws3=$3&ws4=$4&ws5=$5 [P]' . "\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php?ws1=$1&ws2=$2&ws3=$3&ws4=$4 [P]' . "\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php?ws1=$1&ws2=$2&ws3=$3 [P]' . "\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php?ws1=$1&ws2=$2 [P]' . "\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php?ws1=$1 [P]' . "\n";
      $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-ws'], '/') ? '' : '/') . $data['alias-ws'] . '/(.*)/(.*)/(.*)/(.*)/(.*) /sra-ws-gateway.php [P]' . "\n";
    }
    if ($data['alias-files']) {
      if ($data['alias-files-rewrite']) {
        $vhost .= ($data['vhost'] ? '  ' : '') . 'Alias /sra-file-renderer.php "' . SRA_Controller::getSysLibDir() . "/model/sra-file-renderer.php\"\n";
        $vhost .= ($data['vhost'] ? '  ' : '') . "RewriteEngine On\n";
        $vhost .= ($data['vhost'] ? '  ' : '') . 'RewriteRule ^' . (SRA_Util::beginsWith($data['alias-files'], '/') ? '' : '/') . $data['alias-files'] . '/(.*)/(.*) /sra-file-renderer.php?eid=$1 [P]' . "\n";
      }
      else {
        $vhost .= ($data['vhost'] ? '  ' : '') . 'Alias ' . (SRA_Util::beginsWith($data['alias-files'], '/') ? '' : '/') . $data['alias-files'] . ' "' . SRA_Controller::getSysLibDir() . "/model/sra-file-renderer.php\"\n";
      }
    }
    if ($vhost) { fwrite($fp, $vhost); }
    if ($data['vhost']) {
      fwrite($fp, "</VirtualHost>\n\n");
      if ($data['ssl']) {
        fwrite($fp, '<VirtualHost ' . ($data['ip'] ? $data['ip'] : '*') . ":443>\n");
        fwrite($fp, "$vhost");
        fwrite($fp, "  SSLEngine on\n");
        $data['ssl-cert'] = is_file($data['ssl-cert']) ? $data['ssl-cert'] : SRA_Controller::getAppConfDir() . '/' . $data['ssl-cert'];
        $data['ssl-key'] = is_file($data['ssl-key']) ? $data['ssl-key'] : SRA_Controller::getAppConfDir() . '/' . $data['ssl-key'];
        fwrite($fp, '  SSLCertificateFile ' . $data['ssl-cert'] . "\n");
        fwrite($fp, '  SSLCertificateKeyFile ' . $data['ssl-key'] . "\n");
        if ($data['ssl-chain']) {
          $data['ssl-chain'] = is_file($data['ssl-chain']) ? $data['ssl-chain'] : SRA_Controller::getAppConfDir() . '/' . $data['ssl-chain'];
          fwrite($fp, '  SSLCertificateChainFile ' . $data['ssl-chain'] . "\n"); 
        }
        fwrite($fp, '  SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0' . "\n");
        fwrite($fp, "</VirtualHost>\n\n");
      }
      if ($data['mod-deflate']) {
        fwrite($fp, "<Location />\n");
        fwrite($fp, "  SetOutputFilter DEFLATE\n");
        fwrite($fp, "  BrowserMatch ^Mozilla/4 gzip-only-text/html\n");
        fwrite($fp, "  BrowserMatch ^Mozilla/4\\.0[678] no-gzip\n");
        fwrite($fp, "  BrowserMatch \\bMSI[E] !no-gzip !gzip-only-text/html\n");
        fwrite($fp, "  SetEnvIfNoCase Request_URI \\\n");
        fwrite($fp, "  \\.(?:gif|jpe?g|png)\$ no-gzip dont-vary\n");
        fwrite($fp, "  Header append Vary User-Agent env=!dont-vary\n");
        fwrite($fp, "</Location>\n");
      }
      fclose($fp);
    }
    $msg .= $this->installer->resources->getString('installer.root.configure.file', array('file' => $conf)) . "\n";
    if ($data['add-to-conf-d']) {
      $confd = str_replace('//', '/', $data['conf-d'] . '/sierra-' . $this->_app . '.conf');
      if (!file_exists($confd)) {
        SRA_File::symlink($conf, $confd);
        $msg .= $this->installer->resources->getString('installer.configureApp.apache.linked', array('src' => $conf, 'dest' => $confd)) . "\n";
        $this->_addAppUninstallCmd("rm -f $confd");
      }
    }
    return $msg . (!$err ? $this->installer->resources->getString('installer.configureApp.apache.success') : '');
  }
  // }}}
  
  // {{{ configureAppInstaller
  /**
   * "handler" method for the "installer.configureApp.installer" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureAppInstaller($menuItem, $data) {
    return $this->installer->resources->getString('installer.configureApp.installer.msg', array('app' => $this->_app));
  }
  // }}}
  
  // {{{ createAppRelease
  /**
   * "handler" method for the "installer.updateApp.createRelease" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function createAppRelease($menuItem, $data) {
    if ($releaseFile = $data['path']) {
      if (is_dir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME)) { SRA_File::rmdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME, TRUE); }
      SRA_File::mkdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME);
      SRA_File::copy(dirname($this->_getAppDir()) . '/' . basename($this->_getAppDir()), SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/', TRUE);
      $tmpAppDir = SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/' . basename($this->_getAppDir());
      if ($this->isAppConfigured()) { $output = $this->_unconfigureApp($tmpAppDir, FALSE); }
      $output .= $this->_runAppInstallerCmds(SRA_FRAMEWORK_INSTALLER_APP_RELEASE, $tmpAppDir);
      // remove subversion directories
      if ($data['pruneSvn']) {
        foreach(SRA_File::getFileList($tmpAppDir, '.svn', TRUE, 2) as $svnDir) {
          $output .= $this->installer->resources->getString('installer.root.unconfigure.dir', array('dir' => str_replace(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/', '', $svnDir))) . "\n";
          SRA_File::rmdir($svnDir, TRUE); 
        }
      }
      $fileList = $tmpAppDir . '/' . SRA_CONF_DIR_NAME . '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST;
      $files = SRA_File::getFileList($tmpAppDir, '*', TRUE, 3);
      $fp = fopen($fileList, 'w');
      foreach($files as $file) {
        if (is_file($file) || (is_dir($file) && basename($file) != '..' && $file != '/.')) {
          if (SRA_Util::endsWith($file, '/.')) { $file = substr($file, 0, strlen($file) - 2); }
          fwrite($fp, str_replace($tmpAppDir . '/', '', $file) . "\n"); 
        }
      }
      fclose($fp);
      $releaseFile = SRA_File::compress($tmpAppDir, $data['archiveType'], $releaseFile, SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME);
      SRA_File::rmdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME, TRUE);
      return $this->installer->resources->getString('installer.update.createRelease.' . ($releaseFile ? 'success' : 'fail'), array('output' => $output ? trim($output) . "\n" : '', 'release' => $releaseFile));
    }
  }
  // }}}
  
  // {{{ configureAppView
  /**
   * "handler" method for the "installer.configureApp.view" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureAppView($menuItem, $data) {
    if ($appConf =& $this->_getAppConf()) {
      $config = $appConf->getData(array('app-config', 'attributes'));
      foreach($config as $attr => $val) {
        if ($attr == 'key' || $attr == '_akey' || is_array($val)) { continue; }
        $desc .= "\n $attr=$val";
      }
      return $desc ? $desc : $this->installer->resources->getString('installer.configureApp.view.none');
    }
  }
  // }}}
  
  // {{{ configureDbAdd
  /**
   * "handler" method for the "installer.configureDb.add" and
   * "installer.configureDbView.edit" menu items
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureDbAdd($menuItem, $data) {
    if ($data['key'] == $data['name']) unset($data['name']);
    if ($data['port'] == SRA_Database::getDefaultPort(isset($data['type']) ? $data['type'] : SRA_DB_TYPE_MYSQL)) unset($data['port']);
    
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    
    $xmlData =& $config->getData();
    if (!isset($xmlData[$rootElement]['db'])) $xmlData[$rootElement]['db'] = array();
    $xmlData[$rootElement]['db'][$data['key']] = array('attributes' => $data);
    $config->setData($xmlData);
    $config->write(NULL, $this->_app ? SRA_FRAMEWORK_INSTALLER_APP_HEADER : SRA_FRAMEWORK_INSTALLER_HEADER);
    $this->_updateCurrentConfig($config);
    return $this->installer->resources->getString($this->_testDbConnection($data) ? 'installer.root.configureDb.success' : 'installer.root.configureDb.warn');
  }
  // }}}
  
  // {{{ configureDbRemove
  /**
   * "handler" method for the "installer.configureDb.remove" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureDbRemove($menuItem, $data) {
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    
    $data =& $config->getData();
    unset($data[$rootElement]['db'][$this->_db]);
    if (!$data[$rootElement]['db']) unset($data[$rootElement]['db']);
    $config->setData($data);
    $config->write(NULL, $this->_app ? SRA_FRAMEWORK_INSTALLER_APP_HEADER : SRA_FRAMEWORK_INSTALLER_HEADER);
    $this->_db = NULL;
    $this->_updateCurrentConfig($config);
    return $this->installer->resources->getString('installer.root.configureDb.success');
  }
  // }}}
  
  // {{{ configureDbView
  /**
   * "handler" method for the "installer.configureDbView.view" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function configureDbView($menuItem, $data) {
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    
    $desc = $this->installer->resources->getString('installer.configureDbView.view.header', array('db' => $this->_db));
    $data = $config->getData(array($rootElement, 'db', $this->_db, 'attributes'));
    foreach($data as $attr => $val) {
      $desc .= "\n $attr=$val";
    }
    return $desc;
  }
  // }}}
  
  // {{{ createRelease
  /**
   * "handler" method for the "installer.update.createRelease" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function createRelease($menuItem, $data) {
    if ($releaseFile = $data['path']) {
      if (is_dir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME)) { SRA_File::rmdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME, TRUE); }
      SRA_File::mkdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME);
      SRA_File::copy(SRA_DIR, SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/', TRUE);
      $tmpFwDir = SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/' . basename(SRA_DIR);
      if ($this->isConfigured()) { $this->_unconfigure($tmpFwDir, FALSE, TRUE, TRUE); }
      // remove subversion directories
      foreach(SRA_File::getFileList($tmpFwDir, '.svn', TRUE, 2) as $svnDir) { SRA_File::rmdir($svnDir, TRUE); }
      $fp = fopen($tmpFwDir . '/' . SRA_CONF_DIR_NAME . '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST, 'w');
      $files = SRA_File::getFileList($tmpFwDir, '*', TRUE, 3);
      foreach($files as $file) {
        if (is_file($file) || (is_dir($file) && basename($file) != '..' && $file != '/.')) {
          if (SRA_Util::endsWith($file, '/.')) { $file = substr($file, 0, strlen($file) - 2); }
          fwrite($fp, str_replace($tmpFwDir, '', $file) . "\n"); 
        }
      }
      fclose($fp);
      $releaseFile = SRA_File::compress($tmpFwDir, $data['archiveType'], $releaseFile, SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME);
      SRA_File::rmdir(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME, TRUE);
      return $this->installer->resources->getString('installer.update.createRelease.' . ($releaseFile ? 'success' : 'fail'), array('output' => '', 'release' => $releaseFile));
    }
  }
  // }}}
  
  // {{{ defaultsAppCheckin
  /**
   * "defaults-handler" method for the "installer.updateApp.checkin" menu 
   * item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsAppCheckin($menuItem, $id, $data) {
    $defaults = SRA_Controller::getAppSvnInfo(TRUE);
    return isset($defaults[$id]) ? $defaults[$id] : NULL;
  }
  // }}}
  
  // {{{ defaultsAppCreateRelease
  /**
   * "defaults-handler" method for the "installer.updateApp.createRelease" menu 
   * item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsAppCreateRelease($menuItem, $id, $data) {
    if ($id == 'path') {
      return exec('pwd') . '/' . $this->_app . ($data['version'] ? '-' . str_replace('.', '_', $data['version']) : '') . '.' . $data['archiveType'];
    }
  }
  // }}}
  
  // {{{ defaultsCheckin
  /**
   * "defaults-handler" method for the "installer.root.checkin" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsCheckin($menuItem, $id, $data) {
    $defaults = SRA_Controller::getSysSvnInfo(TRUE);
    return isset($defaults[$id]) ? $defaults[$id] : NULL;
  }
  // }}}
  
  // {{{ defaultsConfigure
  /**
   * "defaults-handler" method for the "installer.root.configure" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsConfigure($menuItem, $id, $data) {
    $default = NULL;
    if ($this->_sierraConfig) {
      $data =& $this->_sierraConfig->getData(array('sierra-config', 'attributes'));
      if (isset($data[$id])) { $default = $data[$id]; }
    }
    return $default;
  }
  // }}}
  
  // {{{ defaultsConfigureApp
  /**
   * "defaults-handler" method for the "installer.configureApp.configure" menu 
   * item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsConfigureApp($menuItem, $id, $data) {
    $default = NULL;
    if ($conf =& $this->_getAppConf()) {
      $data =& $conf->getData(array('app-config', 'attributes'));
      if (isset($data[$id])) { $default = $data[$id]; }
    }
    return $default ? $default : $this->defaultsConfigureAppNew($menuItem, $id, $data);
  }
  // }}}
  
  // {{{ defaultsConfigureAppApache
  /**
   * "defaults-handler" method for the "installer.configureApp.apache" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsConfigureAppApache($menuItem, $id, $data) {
    $default = NULL;
    switch($id) {
      case 'alias-dir':
        $default = $data['vhost'] ? SRA_INSTALLER_PROMPT_SKIP : $this->_app;
        break;
      case 'alias-files-rewrite':
        $default = $data['alias-files'] ? NULL : SRA_INSTALLER_PROMPT_SKIP;
        break;
      case 'conf-d':
        $default = !$data['add-to-conf-d'] ? SRA_INSTALLER_PROMPT_SKIP : (is_dir(SRA_FRAMEWORK_INSTALLER_APACHE_CONF_D) ? SRA_FRAMEWORK_INSTALLER_APACHE_CONF_D : NULL);
        break;
      case 'hostname':
      case 'alias':
      case 'ip':
      case 'accesslog':
      case 'errorlog':
      case 'ssl':
      case 'mod-deflate':
        $default = !$data['vhost'] ? SRA_INSTALLER_PROMPT_SKIP : NULL;
        break;
      case 'ssl-cert':
      case 'ssl-key':
      case 'ssl-chain':
        $default = !$data['ssl'] ? SRA_INSTALLER_PROMPT_SKIP : NULL;
        break;
    }
    
    return $default;
  }
  // }}}
  
  // {{{ defaultsConfigureAppNew
  /**
   * "defaults-handler" method for the "installer.configureAppNew.empty" and 
   * "installer.configureAppNew.src" menu items
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsConfigureAppNew($menuItem, $id, $data) {
    $default = NULL;
    switch($id) {
      case 'app-dir':
        $default = SRA_DIR . '/' . SRA_APP_DIR_NAME . '/' . $data['app'];
        break;
      case 'date-format':
        $default = SRA_Controller::getSysDateFormat();
        break;
      case 'date-only-format':
        $default = SRA_Controller::getSysDateOnlyFormat();
        break;
      case 'default-country':
        $default = SRA_Controller::getSysDefaultCountry();
        break;
      case 'default-currency':
        $default = SRA_Controller::getSysDefaultCurrency();
        break;
      case 'default-language':
        $default = SRA_Controller::getSysDefaultLanguage();
        break;
      case 'error-log-file':
        $default = basename(SRA_Controller::getSysErrorLogFile());
        break;
      case 'error-mask':
        $default = SRA_Controller::getSysErrorMask();
        break;
      case 'resources-file':
        $default = SRA_RESOURCE_BUNDLE_DEFAULT_BUNDLE_NAME;
        break;
    }
    return $default;
  }
  // }}}
  
  // {{{ defaultsConfigureDb
  /**
   * "defaults-handler" method for the "installer.configureDb.add" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsConfigureDb($menuItem, $id, $data) {
    $default = NULL;
    if ($id == 'port') {
      $default = SRA_Database::getDefaultPort(isset($data['type']) ? $data['type'] : SRA_DB_TYPE_MYSQL);
    }
    else if ($id == 'name') {
      $default = $data['key'];
    }
    return $default;
  }
  // }}}
  
  // {{{ defaultsCreateRelease
  /**
   * "defaults-handler" method for the "installer.update.createRelease" menu 
   * item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsCreateRelease($menuItem, $id, $data) {
    if ($id == 'path') {
      return exec('pwd') . '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_NAME . ($data['version'] ? '-' . str_replace('.', '_', $data['version']) : '') . '.' . $data['archiveType'];
    }
  }
  // }}}
  
  // {{{ defaultsDbEdit
  /**
   * "defaults-handler" method for the "installer.configureDbView.edit" menu 
   * item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsDbEdit($menuItem, $id, $data) {
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    
    $default = !SRA_Error::isError($val = $config->getData(array($rootElement, 'db', $this->_db, 'attributes', $id))) ? $val : NULL;
    return $default ? $default : $this->defaultsConfigureDb($menuItem, $id, $data);
  }
  // }}}
  
  // {{{ defaultsUpdateAppSvn
  /**
   * "defaults-handler" method for the "installer.updateApp.svn" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsUpdateAppSvn($menuItem, $id, $data) {
    $svnInfo = SRA_Controller::getAppSvnInfo();
    return isset($svnInfo[$id]) ? $svnInfo[$id] : NULL;
  }
  // }}}
  
  // {{{ defaultsUpdateSvn
  /**
   * "defaults-handler" method for the "installer.update.svn" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return string
   */
  function defaultsUpdateSvn($menuItem, $id, $data) {
    $svnInfo = SRA_Controller::getSysSvnInfo();
    return isset($svnInfo[$id]) ? $svnInfo[$id] : NULL;
  }
  // }}}
  
  // {{{ hasAppConfiguration
  /**
   * returns TRUE if the framework has 1 or more application configurations
   * @access public
   * @return boolean
   */
  function hasAppConfiguration() {
    return $this->_sierraConfig && is_array($conf = $this->_sierraConfig->getData(array('sierra-config', 'app'))) && count($conf);
  }
  // }}}
  
  // {{{ hasDbConfiguration
  /**
   * returns TRUE if the framework has 1 or more database configurations
   * @access public
   * @return boolean
   */
  function hasDbConfiguration() {
    $config =& $this->_getCurrentConfig();
    $rootElement = $this->_app ? 'app-config' : 'sierra-config';
    
    return $config && is_array($dbs = $config->getData(array($rootElement, 'db'))) && count($dbs);
  }
  // }}}
  
  // {{{ installNewApp
  /**
   * "handler" method for the "installer.configureAppNew.empty" and 
   * "installer.configureAppNew.src" menu items
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function installNewApp($menuItem, $data) {
    $app = $data['app'];
    $appDir = $data['app-dir'];
    $createdDir = FALSE;
    $dir = SRA_Util::endsWith($appDir, '/') ? $appDir : $appDir . '/';
    if (!is_dir($dir)) {
      $createdDir = TRUE;
      SRA_File::mkdir($dir, 0755); 
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir)) . "\n";
    }
    if ($data['app-path']) {
      $tmpDir = SRA_Controller::getSysTmpDir() . '/.update';
      if (is_dir($tmpDir)) { SRA_File::rmdir($tmpDir, TRUE); }
      SRA_File::mkdir($tmpDir);
      $this->_populateAppDefaults($data);
      SRA_File::copy($data['app-path'], $tmpDir);
      $archive = trim($tmpDir . '/' . basename($data['app-path']));
      if (!file_exists($archive)) {
        if ($createdDir) { SRA_File::rmdir($dir); }
        SRA_File::rmdir($tmpDir, TRUE);
        return $this->installer->resources->getString('installer.configureAppNew.src.error', array('archive' => $data['app-path']));
      }
      else {
        SRA_File::uncompress($archive, NULL, TRUE);
        SRA_File::unlink($archive);
        if ($confDir = SRA_File::getFileList($tmpDir, SRA_CONF_DIR_NAME, TRUE, 2)) {
          foreach($confDir as $cdir) {
            $archivePath = $archivePath && strlen($archivePath) <= $cdir ? $archivePath : dirname($cdir);
          }
          $files = SRA_File::getFileList($archivePath, '*', FALSE, 3);
          foreach($files as $file) {
            exec(SRA_File::findInPath('mv') . ' ' . $file . ' ' . $dir);
          }
        }
      }
      SRA_File::rmdir($tmpDir, TRUE);
    }
    
    if (!is_dir($dir . SRA_BIN_DIR_NAME)) { 
      SRA_File::mkdir($dir . SRA_BIN_DIR_NAME, 0755, TRUE); 
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_BIN_DIR_NAME)) . "\n";
    }
    if (!is_dir($dir . SRA_CONF_DIR_NAME)) { 
      SRA_File::mkdir($dir . SRA_CONF_DIR_NAME, 0755, TRUE); 
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_CONF_DIR_NAME)) . "\n";
    }
    if (!is_dir($rbDir = $dir . SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR)) {
      $rbDir = str_replace('//', '/', $rbDir);
      SRA_File::mkdir($rbDir, 0755, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $rbDir)) . "\n";
    }
    if (!is_dir($dir . SRA_CONF_DIR_NAME)) { 
      SRA_File::mkdir($dir . SRA_CONF_DIR_NAME, 0755, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_CONF_DIR_NAME)) . "\n";
    }
    if (!is_dir($dir . SRA_DEFAULT_LIB_DIR)) { 
      SRA_File::mkdir($dir . SRA_DEFAULT_LIB_DIR, 0755, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_DEFAULT_LIB_DIR)) . "\n";
    }
    if (!is_dir($dir . SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR)) { 
      SRA_File::mkdir($dir . SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR, 0777, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_ENTITY_MODELER_DEFAULT_GENERATE_DIR)) . "\n";
    }
    if (!is_dir($dir . SRA_DEFAULT_HTML_DIR)) { 
      SRA_File::mkdir($dir . SRA_DEFAULT_HTML_DIR, 0755, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_DEFAULT_HTML_DIR)) . "\n";
    }
    if (!is_dir($dir . SRA_DEFAULT_TEMPLATES_DIR)) { 
      SRA_File::mkdir($dir . SRA_DEFAULT_TEMPLATES_DIR, 0755, TRUE);
      echo $this->installer->resources->getString('installer.root.configure.dir', array('dir' => $dir . SRA_DEFAULT_TEMPLATES_DIR)) . "\n";
    }
    $resources = $dir . SRA_RESOURCE_BUNDLE_DEFAULT_RELATIVE_DIR . '/' . ($data['resources-file'] ? $data['resources-file'] : $this->defaultsConfigureAppNew(NULL, 'resources-file', $data)) . '.properties';
    if (!file_exists($resources)) { 
      $fp = fopen($resources, 'w');
      fwrite($fp, $this->installer->resources->getString('installer.configureAppNew.commentResourcesApp') . "\n");
      fwrite($fp, $app . '=' . $app . "\n");
      fwrite($fp, $this->installer->resources->getString('installer.configureAppNew.commentResourcesAppShort') . "\n");
      fwrite($fp, $app . '.short=' . $app);
      if ($data['blank-templates']) { fwrite($fp, "\ntext.coming-soon=" . $this->installer->resources->getString('installer.configureAppNew.blank-template-str')); }
      echo $this->installer->resources->getString('installer.root.configure.file', array('file' => $resources)) . "\n";
    }
    if ($data['blank-templates']) {
      $script = $dir . SRA_DEFAULT_HTML_DIR . '/index.php';
      $template = $dir . SRA_DEFAULT_TEMPLATES_DIR . '/home.tpl';
      $fp = fopen($script, 'w');
      fwrite($fp, "<?php\n");
      fwrite($fp, "require_once('" . SRA_LIB_DIR . "/core/SRA_Controller.php');\n");
      fwrite($fp, "SRA_Controller::init('" . $app . "');\n");
      fwrite($fp, "\$tpl =& SRA_Controller::getAppTemplate();\n");
      fwrite($fp, "\$tpl->display('home.tpl');\n");
      fwrite($fp, "?>\n");
      fclose($fp);
      echo $this->installer->resources->getString('installer.root.configure.file', array('file' => $script)) . "\n";
      $fp = fopen($template, 'w');
      fwrite($fp, "{\$resources->getString('text.coming-soon')} {\$Controller->getAppName()}\n");
      fclose($fp);
      echo $this->installer->resources->getString('installer.root.configure.file', array('file' => $template)) . "\n";
    }
    $config =& $this->_sierraConfig->getData();
    if (!isset($config['sierra-config']['app']) || !isset($config['sierra-config']['app'][$app])) {
      if (!isset($config['sierra-config']['app'])) $config['sierra-config']['app'] = array();
      $config['sierra-config']['app'][$app] = array('attributes' => array('key' => $app));
      if ($appDir != SRA_DIR . '/' . SRA_APP_DIR_NAME . '/' . $app) $config['sierra-config']['app'][$app]['attributes']['dir'] = $appDir;
      $this->_sierraConfig->setData($config);
      $this->_sierraConfig->write(NULL, SRA_FRAMEWORK_INSTALLER_HEADER);
    }
    $appConf =& $this->_getAppConf($app);
    $config =& $appConf->getData();
    foreach($data as $key => $val) {
      if ($key != 'app' && $key != 'app-dir' && $key != 'app-path' && $key != 'blank-templates' && !isset($config['app-config']['attributes'][$key]) && $val != $this->defaultsConfigureAppNew(NULL, $key, $data)) {
        if (!is_array($config['app-config']['attributes'])) $config['app-config']['attributes'] = array();
        $config['app-config']['attributes'][$key] = $val;
      }
    }
    $appConf->setData($config);
    $appConf->write(NULL, SRA_FRAMEWORK_INSTALLER_APP_HEADER);
    $this->_runAppInstallerCmds(SRA_FRAMEWORK_INSTALLER_APP_INSTALL, $app);
    return $this->installer->resources->getString('installer.configureAppNew.success', array('app' => $app, 'dir' => $appDir));
  }
  // }}}
  
  // {{{ isConfigured
  /**
   * returns TRUE if the framework has been configured (when sierra-config.xml 
   * framework configuration file exists)
   * @access public
   * @return boolean
   */
  function isConfigured() {
    return $this->_sierraConfig ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ isAppConfigured
  /**
   * returns TRUE if the selected application has been configured (when 
   * app-config.xml application configuration file exists)
   * @access public
   * @return boolean
   */
  function isAppConfigured() {
    return file_exists($this->_getAppDir() . SRA_APP_CONFIG);
  }
  // }}}
  
  // {{{ isAppHasInstaller
  /**
   * returns TRUE if the selected application has a custom installer 
   * (installer.xml in the etc directory)
   * @access public
   * @return boolean
   */
  function isAppHasInstaller() {
    return $this->isAppConfigured() && file_exists(SRA_Controller::getAppConfDir() . '/' . SRA_FRAMEWORK_INSTALLER_CONFIG);
  }
  // }}}
  
  // {{{ isSvnInstalled
  /**
   * returns TRUE if svn (subversion) is installed
   * @access public
   * @return boolean
   */
  function isSvnInstalled() {
    return SRA_File::findInPath('svn') ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ latestAppVersionAvailable
  /**
   * returns TRUE if the application version list is available
   * @access public
   * @return boolean
   */
  function latestAppVersionAvailable() {
    return SRA_Controller::getAppLatestVersion() ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ latestVersionAvailable
  /**
   * returns TRUE if the framework version list is available
   * @access public
   * @return boolean
   */
  function latestVersionAvailable() {
    return SRA_Controller::getSysLatestVersion() ? TRUE : FALSE;
  }
  // }}}
  
  // {{{ resetApp
  /**
   * resets the application configuration
   * @access public
   * @return void
   */
  function resetApp() {
    $this->_app = NULL;
  }
  // }}}
  
  // {{{ resourceKeysAppLatestVersion
  /**
   * returns the resource keys for the update to latest version application menu 
   * item
   * @access public
   * @return hash
   */
  function resourceKeysAppLatestVersion() {
    return array('extra' => SRA_Controller::getAppLatestVersion() == SRA_Controller::getAppVersion() ? $this->installer->resources->getString('installer.update.current') : '', 'version' => SRA_Controller::getAppLatestVersion());
  }
  // }}}
  
  // {{{ resourceKeysConfigureApp
  /**
   * returns the resource keys for the view existing application menu
   * @access public
   * @return hash
   */
  function resourceKeysConfigureApp() {
    return array('app' => $this->_app);
  }
  // }}}
  
  // {{{ resourceKeysConfigureDbView
  /**
   * returns the resource keys for the view existing database menu
   * @access public
   * @return hash
   */
  function resourceKeysConfigureDbView() {
    return array('db' => $this->_db);
  }
  // }}}
  
  // {{{ resourceKeysLatestVersion
  /**
   * returns the resource keys for the update to latest version menu item
   * @access public
   * @return hash
   */
  function resourceKeysLatestVersion() {
    return array('extra' => SRA_Controller::getSysLatestVersion() == SRA_Controller::getSysVersion() ? $this->installer->resources->getString('installer.update.current') : '', 'version' => SRA_Controller::getSysLatestVersion());
  }
  // }}}
  
  // {{{ setApp
  /**
   * sets the current active application identifer
   * @param string $app the identifier of the application to set
   * @access public
   * @return string
   */
  function setApp($app) {
    $this->_app = $app;
    if (file_exists($config = $this->_getAppDir() . SRA_APP_CONFIG)) { SRA_Controller::init($this->_app, TRUE, TRUE, TRUE); }
  }
  // }}}
  
  // {{{ setDb
  /**
   * sets the current active database identifer (for the existing database menu)
   * @param string $db the identifier of the db to set
   * @access public
   * @return string
   */
  function setDb($db) {
    $this->_db = $db;
  }
  // }}}
  
  // {{{ unconfigure
  /**
   * "handler" method for the "installer.root.unconfigure" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function unconfigure($menuItem, $data) {
    $this->resetApp();
    $this->_unconfigure(SRA_DIR, TRUE);
    return $this->installer->resources->getString('installer.root.unconfigure.success');
  }
  // }}}
  
  // {{{ unconfigureApp
  /**
   * "handler" method for the "installer.configureApp.unconfigure" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function unconfigureApp($menuItem, $data) {
    $cmdsOutput = $this->_unconfigureApp($this->_getAppDir());
    return $this->installer->resources->getString('installer.configureApp.unconfigure.success', array('app' => $this->_app, 'cmdsOutput' => $cmdsOutput));
  }
  // }}}
  
  // {{{ uninstallApp
  /**
   * "handler" method for the "installer.configureApp.uninstall" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function uninstallApp($menuItem, $data) {
    $results = 'installer.configureApp.uninstall.error';
    $config =& $this->_sierraConfig->getData();
    if ($dir = $this->_getAppDir()) {
      unset($config['sierra-config']['app'][$this->_app]);
      if (!$config['sierra-config']['app']) unset($config['sierra-config']['app']);
      $this->_sierraConfig->setData($config);
      $this->_sierraConfig->write(NULL, SRA_FRAMEWORK_INSTALLER_HEADER);
      if (is_dir($dir)) {
        $this->_runAppInstallerCmds(SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL, $dir);
        SRA_File::rmdir($dir, TRUE); 
      }
      $results = 'installer.configureApp.uninstall.success';
    }
    return $this->installer->resources->getString($results, array('app' => $this->_app, 'dir' => $dir));
  }
  // }}}
  
  // {{{ updateAppLatestVersion
  /**
   * "handler" method for the "installer.updateApp.latestVersion" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateAppLatestVersion($menuItem, $data) {
    return $this->updateAppSpecificVersion(SRA_Controller::getAppLatestVersion());
  }
  // }}}
  
  // {{{ updateAppManual
  /**
   * "handler" method for the "installer.updateApp.manual" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateAppManual($menuItem, $data) {
    return $this->updateAppSpecificVersion($menuItem, $data, $data['installer.updateApp.manual.path']);
  }
  // }}}
  
  // {{{ updateAppSpecificVersion
  /**
   * "handler" method for the "installer.updateApp.specificVersion" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @param string $src a manual source path
   * @access public
   * @return string
   */
  function updateAppSpecificVersion($menuItem, $data, $src=NULL) {
    $versions =& SRA_Controller::getAppVersions();
    $src = $src ? $src : $versions[$menuItem]['src'];
    if ($src) {
      $dir = SRA_Controller::getSysTmpDir() . '/.update';
      if ($src != $dir) {
        $archive = $dir . '/' . basename($src);
        if (is_dir($dir)) { SRA_File::rmdir($dir, TRUE); }
        SRA_File::mkdir($dir);
        SRA_File::copy($src, $archive);
      }
      if ($src == $dir || file_exists($archive)) {
        ob_start();
        if ($src != $dir) {
          SRA_File::uncompress($archive);
          SRA_File::unlink($archive);
        }
        if ($confDir = SRA_File::getFileList($dir, SRA_CONF_DIR_NAME, TRUE, 2)) {
          $archivePath = dirname($confDir[0]);
          $files = SRA_File::getFileList($archivePath, '*', TRUE, 3);
          $srcFileList = $this->_getAppDir() . SRA_CONF_DIR_NAME. '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST;
          $newSrcFileList = $archivePath . '/' . SRA_CONF_DIR_NAME . '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST;
          foreach($files as $file) {
            if (is_file($file) && $file != $newSrcFileList) {
              $path = $this->_getAppDir() . str_replace($archivePath . '/', '', $file);
              if (!is_file($path) || (($diff = array_diff(file($path), file($file))) && (count($diff) > 1 || !isset($diff[0])))) {
                echo $this->installer->resources->getString('installer.update.file.' . (is_file($path) ? 'mod' : 'add'), array('file' => str_replace($archivePath . '/', '', $file))) . "\n";
                SRA_File::copy($file, $path);
              }
            }
          }
          if (file_exists($newSrcFileList) && file_exists($srcFileList)) {
            $newSrcFiles = file($newSrcFileList);
            $srcFiles = file($srcFileList);
            foreach($srcFiles as $srcFile) {
              if (trim($srcFile) && !in_array($srcFile, $newSrcFiles)) {
                $file = trim($this->_getAppDir() . $srcFile);
                is_file($file) ? SRA_File::unlink($file) : SRA_File::rmdir($file, TRUE);
                echo $this->installer->resources->getString('installer.update.file.del', array('file' => $srcFile));
              }
            }
          }
          if (file_exists($newSrcFileList)) {
            echo $this->installer->resources->getString('installer.update.file.' . (is_file($srcFileList) ? 'mod' : 'add'), array('file' => str_replace($archivePath . '/', '', $newSrcFileList))) . "\n";
            SRA_File::copy($newSrcFileList, $srcFileList); 
          }
          $output = ob_get_contents() . "\n";
          if ($output) { $output = trim($output) . "\n"; }
          ob_end_clean();
          $ret = $this->installer->resources->getString('installer.update.success' . (is_numeric($menuItem) ? '' : 'Manual'), array('output' => $output, 'version' => is_numeric($menuItem) ? $menuItem : $src));
        }
      }
      SRA_File::rmdir($dir, TRUE);
      return $ret;
    }
    $this->installer->sysError();
  }
  // }}}
  
  // {{{ updateAppSvn
  /**
   * "handler" method for the "installer.updateApp.svn" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateAppSvn($menuItem, $data) {
    $svn = SRA_File::findInPath('svn');
    $dir = SRA_Controller::getSysTmpDir() . '/.update';
    if (is_dir($dir)) { SRA_File::rmdir($dir, TRUE); }
    passthru("$svn checkout " . $data['url'] . " $dir/" . $this->_app . ($data['username'] ? ' --username ' . $data['username'] : '') . ($data['password'] ? ' --password ' . $data['password'] : ''));
    return str_replace($dir, $this->installer->resources->getString('installer.update.svn.sourceArchive'), $this->updateAppSpecificVersion($menuItem, $data, $dir));
  }
  // }}}
  
  // {{{ updateLatestVersion
  /**
   * "handler" method for the "installer.update.latestVersion" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateLatestVersion($menuItem, $data) {
    return $this->updateSpecificVersion(SRA_Controller::getSysLatestVersion());
  }
  // }}}
  
  // {{{ updateManual
  /**
   * "handler" method for the "installer.update.manual" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateManual($menuItem, $data) {
    return $this->updateSpecificVersion($menuItem, $data, $data['installer.update.manual.path']);
  }
  // }}}
  
  // {{{ updateSpecificVersion
  /**
   * "handler" method for the "installer.update.specificVersion" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @param string $src a manual source path
   * @access public
   * @return string
   */
  function updateSpecificVersion($menuItem, $data, $src=NULL) {
    $versions =& SRA_Controller::getSysVersions();
    $src = $src ? $src : $versions[$menuItem]['src'];
    if ($src) {
      $dir = SRA_Controller::getSysTmpDir() . '/.update';
      if ($src != $dir) {
        $archive = $dir . '/' . basename($src);
        if (is_dir($dir)) { SRA_File::rmdir($dir, TRUE); }
        SRA_File::mkdir($dir);
        SRA_File::copy($src, $archive);
      }
      if ($src == $dir || file_exists($archive)) {
        if ($src != $dir) {
          SRA_File::uncompress($archive);
          SRA_File::unlink($archive);
        }
        $archivePath = $dir . '/sierra';
        $binDir = SRA_DIR . '/' . SRA_BIN_DIR_NAME;
        $files = SRA_File::getFileList($archivePath, '*', TRUE);
        $srcFileList = SRA_CONF_DIR. '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST;
        $newSrcFileList = $archivePath . '/' . SRA_CONF_DIR_NAME . '/' . SRA_FRAMEWORK_INSTALLER_RELEASE_FILE_LIST;
        foreach($files as $file) {
          if (is_file($file) && $file != $newSrcFileList && !strpos($file, '/.svn/')) {
            $path = SRA_DIR . str_replace($archivePath, '', $file);
            if (!is_file($path) || (($diff = array_diff(file($path), file($file))) && (dirname($path) != $binDir || count($diff) > 1 || !isset($diff[0])))) {
              if (dirname($path) == $binDir && $this->isConfigured()) {
                $oldFile = file($path);
                $newFile = $oldFile[0] . implode('', file($file));
                SRA_File::write($file, $newFile);
              }
              echo $this->installer->resources->getString('installer.update.file.' . (is_file($path) ? 'mod' : 'add'), array('file' => str_replace($archivePath, '', $file))) . "\n";
              SRA_File::copy($file, $path);
            }
          }
        }
        if (file_exists($newSrcFileList) && file_exists($srcFileList)) {
          $newSrcFiles = file($newSrcFileList);
          $srcFiles = file($srcFileList);
          foreach($srcFiles as $srcFile) {
            if (trim($srcFile) && !in_array($srcFile, $newSrcFiles)) {
              $file = trim(SRA_DIR . $srcFile);
              is_file($file) ? SRA_File::unlink($file) : SRA_File::rmdir($file, TRUE);
              echo $this->installer->resources->getString('installer.update.file.del', array('file' => $srcFile));
            }
          }
        }
        if (file_exists($newSrcFileList)) {
          echo $this->installer->resources->getString('installer.update.file.' . (is_file($srcFileList) ? 'mod' : 'add'), array('file' => str_replace($archivePath, '', $newSrcFileList))) . "\n";
          SRA_File::copy($newSrcFileList, $srcFileList); 
        }
        SRA_File::rmdir($dir, TRUE);
        return $this->installer->resources->getString('installer.update.success' . (is_numeric($menuItem) ? '' : 'Manual'), array('output' => '', 'version' => is_numeric($menuItem) ? $menuItem : $src));
      }
    }
    return $this->installer->sysError();
  }
  // }}}
  
  // {{{ updateSvn
  /**
   * "handler" method for the "installer.update.repository" menu item
   * @param string $menuItem the menu-item identifier
   * @param hash $data values provided by the user
   * @access public
   * @return string
   */
  function updateSvn($menuItem, $data) {
    $svn = SRA_File::findInPath('svn');
    $dir = SRA_Controller::getSysTmpDir() . '/.update';
    if (is_dir($dir)) { SRA_File::rmdir($dir, TRUE); }
    passthru("$svn checkout " . $data['url'] . " $dir/sierra" . ($data['username'] ? ' --username ' . $data['username'] : '') . ($data['password'] ? ' --password ' . $data['password'] : ''));
    return str_replace($dir, $this->installer->resources->getString('installer.update.svn.sourceArchive'), $this->updateSpecificVersion($menuItem, $data, $dir));
  }
  // }}}
  
  // {{{ validatorConfigure
  /**
   * "validator" method for the "installer.root.configure" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param string $val the value provided by the user
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return boolean
   */
  function validatorConfigure($menuItem, $id, $val, $data) {
    $ret = TRUE;
    switch($id) {
      case 'app':
        $data =& $this->_sierraConfig->getData();
        if (!SRA_Util::isAlphaNumeric($val) || isset($data['sierra-config']['app'][$val])) $ret = FALSE;
        break;
      case 'app-dir':
        if ((!is_dir($val) && !is_dir(dirname($val))) || (is_dir($val) && !is_writable($val)) || (!is_dir($val) && is_dir(dirname($val)) && !is_writable(dirname($val)))) $ret = FALSE;
        break;
      case 'default-country':
        if ($val) {
          require_once('util/l10n/SRA_Locale.php');
          if (!SRA_Locale::validateCountryCode($val)) $ret = FALSE;
        }
        break;
      case 'default-currency':
        if ($val) {
          require_once('util/l10n/SRA_Currency.php');
          if (!SRA_Currency::validateCode($val)) $ret = FALSE;
        }
        break;
      case 'default-language':
        if ($val) {
          require_once('util/l10n/SRA_Locale.php');
          if (!SRA_Locale::validateLanguageCode($val)) $ret = FALSE;
        }
        break;
      case 'error-mask':
        if ($val && !is_numeric($val)) $ret = FALSE;
        break;
      case 'log-dir':
        if ($val) {
          if (!is_dir($val)) $val = SRA_INSTALLER_FRAMEWORK_DIR . '/' . $val;
          if ((is_dir($val) && !is_writable($val)) || (!is_dir($val) && !is_writable(dirname($val)))) $ret = FALSE;
        }
        break;
    }
    return $ret;
  }
  // }}}
  
  // {{{ validatorConfigureAppApache
  /**
   * "validator" method for the "installer.configureApp.apache" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param string $val the value provided by the user
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return boolean
   */
  function validatorConfigureAppApache($menuItem, $id, $val, $data) {
    $ret = TRUE;
    switch($id) {
      case 'conf-d':
        $ret = is_dir($val) && is_writable($val) ? TRUE : FALSE;
        break;
      case 'ip':
        if ($val) { $ret = SRA_Util::validateIp('*', $val); }
        break;
      case 'ssl-cert':
      case 'ssl-key':
      case 'ssl-chain':
        $ret = !$val || is_file($val) || is_file(SRA_Controller::getAppConfDir() . '/' . $val);
        break;
    }
    return $ret;
  }
  // }}}
  
  // {{{ validatorConfigureDb
  /**
   * "validator" method for the "installer.configureDb.add" and 
   * "installer.configureDbView.edit" menu items
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param string $val the value provided by the user
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return boolean
   */
  function validatorConfigureDb($menuItem, $id, $val, $data) {
    $ret = TRUE;
    switch($id) {
      case 'type':
        if (((!$val || $val == SRA_DB_TYPE_MYSQL) && !function_exists('mysql_connect') && !!function_exists('mysql_pconnect')) || ($val == SRA_DB_TYPE_POSTGRESQL && !function_exists('pg_connect')) || ($val == SRA_DB_TYPE_MSSQL && !function_exists('mssql_connect'))) $ret = FALSE;
        break;
    }
    return $ret;
  }
  // }}}
  
  // {{{ validatorCreateRelease
  /**
   * "validator" method for the "installer.update.createRelease" menu item
   * @param string $menuItem the menu-item identifier
   * @param string $id the prompt identifier
   * @param string $val the value provided by the user
   * @param hash $data hash (indexed by prompt key) of data that has already 
   * been collected
   * @access public
   * @return boolean
   */
  function validatorCreateRelease($menuItem, $id, $val, $data) {
    if ($id == 'path' && (!is_dir(dirname($val)) || !is_writable(dirname($val)))) {
      return FALSE;
    }
    return TRUE;
  }
  // }}}
  
  
  // {{{ _addAppUninstallCmd
  /**
   * adds an app uninstall exec command. these commands are stored in 
   * SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL located in the app etc directory
   * @param string $cmd 
   * @access private
   * @return void
   */
  function _addAppUninstallCmd($cmd) {
    $conf = SRA_Controller::getAppConfDir() . '/' . SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL;
    $fp = fopen($conf, 'a');
    fwrite($fp, "\n" . SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL_AUTO . $cmd);
    fclose($fp);
  }
  // }}}
  
  // {{{ _getAppConf
  /**
   * returns an SRA_XmlParser for the $app application configuration
   * @param string $app the identifier of the application to return the 
   * configuration for. if not specified, the directory to the current 
   * application will be returned
   * @access private
   * @return SRA_XmlParser
   */
  function &_getAppConf($app=NULL) {
    $app = $app ? $app : $this->_app;
    if (!isset($this->_appConfigs[$app]) && is_dir($dir = $this->_getAppDir($app))) {
      if (!file_exists($appConf = $dir . SRA_APP_CONFIG)) {
        $fp = fopen($appConf, 'w');
        fwrite($fp, SRA_FRAMEWORK_INSTALLER_APP_HEADER . "\n<app-config></app-config\n");
        fclose($fp);
      }
      $this->_appConfigs[$app] =& SRA_XmlParser::getXmlParser($appConf, TRUE);
    }
    return $this->_appConfigs[$app];
  }
  // }}}
  
  // {{{ _getAppDir
  /**
   * returns the directory (with trailing /) for $app. returns NULL if $app is 
   * not valid
   * @param string $app the identifier of the application to return the 
   * directory of. if not specified, the directory to the current application 
   * will be returned
   * @access private
   * @return string
   */
  function _getAppDir($app=NULL) {
    $app = $app ? $app : $this->_app;
    $config =& $this->_sierraConfig->getData();
    $dir = isset($config['sierra-config']['app'][$app]) ? (isset($config['sierra-config']['app'][$app]['dir']) ? $config['sierra-config']['app'][$app]['dir'] : SRA_DIR . '/' . SRA_APP_DIR_NAME . '/' . $app) : NULL;
    if ($dir && !SRA_Util::endsWith($dir, '/')) $dir .= '/';
    return $dir;
  }
  // }}}
  
  // {{{ _getCurrentConfig
  /**
   * returns the SRA_XmlParser for the framework or application (if an 
   * application is currently configured) configuration
   * @access private
   * @return SRA_XmlParser
   */
  function &_getCurrentConfig() {
    return $this->_app ? $this->_getAppConf() : $this->_sierraConfig;
  }
  // }}}
  
  // {{{ _populateAppDefaults
  /**
   * populates default values in $data that may have been removed by the 
   * installer
   * @param hash $data the application config values
   * @access private
   * @return void
   */
  function _populateAppDefaults(&$data) {
    if (!isset($data['date-only'])) $data['date-format'] = SRA_DEFAULT_DATE_FORMAT;
    if (!isset($data['date-only-format'])) $data['date-only-format'] = SRA_DEFAULT_DATE_ONLY_FORMAT;
    if (!isset($data['default-country'])) $data['default-country'] = SRA_LOCALE_DEFAULT_COUNTRY;
    if (!isset($data['default-currency'])) $data['default-currency'] = SRA_CURRENCY_DEFAULT;
    if (!isset($data['default-language'])) $data['default-language'] = SRA_LOCALE_DEFAULT_LANGUAGE;
  }
  // }}}
  
  // {{{ _runAppInstallerCmds
  /**
   * runs the installer commands for $app (release, install, or uninstall). 
   * returns the output from running these commands
   * @param string $file the SRA_FRAMEWORK_INSTALLER_APP_* file containing the 
   * commands to execute. auto uninstall commands will also be removed from 
   * SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL
   * @param string $app the identifier of the application to run the installer 
   * commands for. if not specified, the directory to the current application 
   * will be used. optionally this parameter may be an absolute path to the 
   * application directory
   * @access private
   * @return string
   */
  function _runAppInstallerCmds($file, $app=NULL) {
    $output = '';
    $appDir = is_dir($app) ? $app : $this->_getAppDir($app);
    $cmdsFile = $appDir . '/' . SRA_CONF_DIR_NAME . '/' . $file;
    if (file_exists($cmdsFile)) {
      ob_start();
      $lines = file($cmdsFile);
      foreach ($lines as $cmd) {
        $cmd = trim($cmd);
        if ($file == SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL && SRA_Util::beginsWith($cmd, SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL_AUTO)) { $cmd = substr($cmd, strlen(SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL_AUTO)); }
        $cmd = str_replace('$appDir', $appDir, $cmd);
        if ($cmd) { passthru('cd ' . $appDir . '; ' . $cmd); }
      }
      $output = ob_get_contents() . "\n";
      if ($output) { $output .= "\n"; }
      ob_end_clean();
      if ($file == SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL) {
        $fp = fopen($cmdsFile, 'w');
        foreach($lines as $line) {
          if (trim($line) && !SRA_Util::beginsWith($line, SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL_AUTO)) {
            fwrite($fp, $line);
          }
        }
        fclose($fp);
      }
    }
    return $output;
  }
  // }}}
  
  // {{{ _testDbConnection
  /**
   * tests a database connection and returns TRUE on success, FALSE otherwise
   * @param hash $config the database configuration hash
   * @access private
   * @return boolean
   */
  function _testDbConnection($config) {
    $ret = TRUE;
    if (!isset($config['host'])) $config['host'] = SRA_DB_DEFAULT_HOST;
    if (!isset($config['name'])) $config['name'] = $config['key'];
    $config['port'] = SRA_Database::getDefaultPort(isset($config['type']) ? $config['type'] : SRA_DB_TYPE_MYSQL);
    switch($config['type']) {
      case SRA_DB_TYPE_POSTGRESQL:
        ($conn = pg_connect("host={$config['host']} dbname={$config['name']} user={$config['user']} password={$config['password']} port={$config['port']}")) ? pg_close($conn) : $ret = FALSE;
        break;
      case SRA_DB_TYPE_MSSQL:
        ($conn = mssql_connect($config['host'] . ':' . $config['port'], $config['user'], $config['password'])) ? (!mssql_select_db($config['name'], $conn) ? $ret = FALSE : $ret = TRUE) : $ret = FALSE;
        if ($conn) mssql_close($conn);
        break;
      default:
        ($conn = (!function_exists('mysql_pconnect') ? mysqli_connect($config['host'], $config['user'], $config['password'], NULL, $config['port']) : mysql_connect($config['host'] . ':' . $config['port'], $config['user'], $config['password']))) ? (!(!function_exists('mysql_pconnect') ? mysqli_select_db($conn, $config['name']) : mysql_select_db($config['name'], $conn)) ? $ret = FALSE : $ret = TRUE) : $ret = FALSE;
        if ($conn) {
          !function_exists('mysql_pconnect') ? mysqli_close($conn) : mysql_close($conn);
        }
    }
    return $ret;
  }
  // }}}
  
  // {{{ _unconfigure
  /**
   * unconfigures a framework directory by deleting any custom configuration 
   * files, the tmp and log directories, any subversion directories (optional), 
   * and the app directory (optional)
   * @param string $path the base path to the framework directory (i.e. 
   * '/var/www/sierra')
   * @param boolean $verbose whether or not to output results to stdout
   * @param boolean $removeSvnDirs whether or not to remove remove subversion 
   * directories
   * @param boolean $forceRemoveAppDir whether or not to remove the app 
   * directory regardless of whether or not it is empty. WARNING: this is a 
   * recursive delete... any applications in this directory will also be deleted
   * @access private
   * @return void
   */
  function _unconfigure($path, $verbose=FALSE, $removeSvnDirs=FALSE, $forceRemoveAppDir=FALSE) {
    // delete configuration files
    $defaultConfigs = SRA_File::getFileList($path . '/' . basename(SRA_Controller::getSysConfDir()), '/^.*-default.*$/', TRUE);
    foreach($defaultConfigs as $defaultConfig) {
      $config = str_replace('-default', '', $defaultConfig);
      if (file_exists($config)) {
        if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.file', array('file' => $config)) . "\n"; }
        SRA_File::unlink($config); 
      }
    }
    // update php path
    foreach(SRA_File::getFileList($path . '/' . SRA_BIN_DIR_NAME, '/^.*\.php$/') as $script) {
      $lines = file($script);
      for($i=0; $i<count($lines); $i++) {
        if (SRA_Util::beginsWith($lines[$i], '<?php')) { break; }
        unset($lines[$i]);
      }
      $lines = implode('', $lines);
      SRA_File::write($script, $lines);
      if ($verbose) { echo $this->installer->resources->getString('installer.root.configure.updateFile', array('file' => $script)) . "\n"; }
    }
    if (is_dir($logDir = $path . '/' . basename(SRA_Controller::getSysLogDir()))) {
      if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.dir', array('dir' => $logDir)) . "\n"; }
      SRA_File::rmdir($logDir, TRUE); 
    }
    if (is_dir($appDir = $path . '/' . SRA_APP_DIR_NAME) && ($forceRemoveAppDir || !count(SRA_File::getFileList($appDir, '*', TRUE, 3)))) {
      if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.dir', array('dir' => $appDir)) . "\n"; }
      SRA_File::rmdir($appDir, $forceRemoveAppDir); 
    }
    if (is_dir($tmpDir = $path . '/' . basename(SRA_Controller::getSysTmpDir()))) {
      if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.dir', array('dir' => $tmpDir)) . "\n"; }
      SRA_File::rmdir($tmpDir, TRUE); 
    }
    if ($removeSvnDirs && ($svnDirs = SRA_File::getFileList($path, '/\.svn/', TRUE))) {
      foreach($svnDirs as $svnDir) {
        if (basename($svnDir) == '.svn') {
          if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.dir', array('dir' => $svnDir)) . "\n"; }
          SRA_File::rmdir($svnDir, TRUE);
        }
      }
    }
    
    if (is_file($sysConfig = $path . '/' . SRA_SYS_CONFIG)) {
      if ($verbose) { echo $this->installer->resources->getString('installer.root.unconfigure.file', array('file' => $sysConfig)) . "\n"; }
      SRA_File::unlink($sysConfig);
    }
  }
  // }}}
  
  // {{{ _unconfigureApp
  /**
   * unconfigures a framework application by running any uninstall commands, 
   * deleting app-config.xml and deleting any apache configurations. returns a 
   * string containing the results of unconfiguring the application
   * @param string $path the base path to the application
   * @param boolean $runUninstallCmds whether or not to run the application 
   * uninstaller commands
   * @access private
   * @return string
   */
  function _unconfigureApp($path, $runUninstallCmds=TRUE) {
    if ($runUninstallCmds) { $cmdsOutput = $this->_runAppInstallerCmds(SRA_FRAMEWORK_INSTALLER_APP_UNINSTALL, $path); }
    if (file_exists($config = $path . '/' . SRA_APP_CONFIG)) {
      SRA_File::unlink($config);
      $cmdsOutput .= $this->installer->resources->getString('installer.root.unconfigure.file', array('file' => str_replace(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/', '', $config))) . "\n";
    }
    if (file_exists($config = $path . '/' . SRA_CONF_DIR_NAME . '/' . SRA_FRAMEWORK_INSTALLER_APACHE_CONF)) {
      SRA_File::unlink($config);
      $cmdsOutput .= $this->installer->resources->getString('installer.root.unconfigure.file', array('file' => str_replace(SRA_FRAMEWORK_INSTALLER_RELEASE_TMP_DIR_NAME . '/', '', $config))) . "\n";
    }
    return $cmdsOutput;
  }
  // }}}
  
  // {{{ _updateCurrentConfig
  /**
   * updates the current SRA_XmlParser configuration
   * @param SRA_XmlParser $config the updated configuration
   * @access private
   * @return void
   */
  function _updateCurrentConfig(&$config) {
    $this->_app ? $this->_appConfigs[$this->_app] =& $config : $this->_sierraConfig =& $config;
  }
  // }}}
  
  // {{{ _updateSvn
  /**
   * updates the subversion repository identified by $path. returns TRUE on 
   * success, FALSE otherwise
   * @param hash $svnConf the subversion configuration parameters. this hash may 
   * have the following keys:
   *   password: the subversion repository password (optional)
   *   url:      the url to the subversion repository containing the framework/app
   *   user:     the subversion repository username (optional)
   * @param string $path the base path to the subversion repository
   * @access private
   * @return boolean
   */
  function _updateSvn($svnConf, $path) {
    $svn = SRA_File::findInPath('svn');
    
    // TODO
    return TRUE;
  }
  // }}}
}
// }}}
?>
