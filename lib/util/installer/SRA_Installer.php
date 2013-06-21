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
require_once('SRA_InstallerMenu.php');
// }}}

// {{{ Constants

/**
 * the header to include when rendering the framework configuration
 * @type string
 */
define('SRA_FRAMEWORK_INSTALLER_HEADER', "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<!DOCTYPE sierra-config PUBLIC \"-//SIERRA//DTD SIERRA CONFIG//EN\"\n  \"http://sierra-php.googlecode.com/svn/trunk/etc/sierra-config.dtd\">\n");

/**
 * the default name of the installer xml file (located in the framework or app 
 * "etc" directory)
 * @type string
 */
define('SRA_INSTALLER_CONFIG_FILE_NAME', 'installer.xml');

/**
 * the base directory for the framework
 * @type string
 */
define('SRA_INSTALLER_FRAMEWORK_DIR', dirname(dirname(dirname(dirname(__FILE__)))));

/**
 * path to the framework installer confuration file
 * @type string
 */
define('SRA_INSTALLER_FRAMEWORK_INSTALLER_CONFIG', SRA_INSTALLER_FRAMEWORK_DIR . '/etc/' . SRA_INSTALLER_CONFIG_FILE_NAME);

/**
 * the framework temp directory
 * @type string
 */
define('SRA_INSTALLER_FRAMEWORK_TMP_DIR', SRA_INSTALLER_FRAMEWORK_DIR . '/tmp');

/**
 * the error message to display if the temp directory cannot be created (not 
 * localized because framework cannot be initialized if this directory cannot be 
 * created)
 * @type string
 */
define('SRA_INSTALLER_FRAMEWORK_TMP_DIR_ERROR', 'ERROR: Unable to create temp directory "' . SRA_INSTALLER_FRAMEWORK_TMP_DIR . '". Check your permissions and try to run the installer again.');

/**
 * the name of the application specific quick installer script used by the 
 * quickInstall method. this script is executed BEFORE the application is 
 * initialized
 * @type string
 */
define('SRA_INSTALLER_QUICK_INSTALL', 'quick-install.php');

/**
 * the value returned by a "defaults-handler" indicating that a prompt value 
 * should be skipped
 * @type string
 */
define('SRA_INSTALLER_PROMPT_SKIP', '_skip_');
// }}}

// {{{ SRA_Installer
/**
 * this is the base "abstract" class for framework installers. installers 
 * defined in an xml file based on the installer dtd (the "installer" element 
 * "src" attribute) MUST extend this class. for more information, see the 
 * documentation provided in the installer dtd and the API in this class and 
 * SRA_FrameworkInstaller
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util.installer
 */
class SRA_Installer {
  // public attributes
  
  /**
   * a reference to the installer handler class instance ("class" in the 
   * installer XML confuration)
   * @type object
   */
  var $installer;
  
  /**
   * the name of this installer
   * @type string
   */
  var $name;
  
  /**
   * a reference to the resources that should be used for this installer 
   * instance
   * @type SRA_ResourceBundle
   */
  var $resources;
  
  /**
   * a reference to the app resources (if this installer is being run for a 
   * specific application)
   * @type SRA_ResourceBundle
   */
  var $resourcesApp;
  
  /**
   * a reference to the framework (global) resources
   * @type SRA_ResourceBundle
   */
  var $resourcesSys;
  
  /**
   * standard input pointer
   * @type int
   */
  var $stdin;
  
  
  // private attributes
  
  /**
   * a reference to the "installer" attributes from the xml confuration
   * @type hash
   */
  var $_conf;
  
  /**
   * the menus associated with this installer (indexed by the menu "key")
   * @type SRA_InstallerMenu[]
   */
  var $_menus;
  
  /**
   * a stack for the current displayed menu hierarchy
   * @type array
   */
  var $_menuStack = array();
  
  
  // public methods
  
  // {{{ clear
  /**
   * clears the screen
   * @access public
   * @return void
   */
  function clear() {
    passthru(SRA_File::findInPath('clear'));
  }
  // }}}
  
  // {{{ confirm
  /**
   * prompts the user to answer yes or no to $question and returns their 
   * response as a boolean value
   * @param string $question the question to prompt the user with to confirm
   * @param boolean $default the default value (TRUE or FALSE). if not specified
   * no default will be used (user must enter yes or no)
   * @access public
   * @return boolean
   */
  function confirm($question, $default=NULL) {
    $yes = $this->resourcesSys->getString('installer.confirm.yes');
    $no = $this->resourcesSys->getString('installer.confirm.no');
    return $this->getUserInput($question, $default === TRUE ? $yes : ($default === FALSE ? $no : NULL), FALSE, array($yes, $no), TRUE) === $yes;
  }
  // }}}
  
  // {{{ displayMenu
  /**
   * clears the screen and displays the current menu
   * @param string $msg an optional message to display on top of the menu
   * @access private
   * @return void
   */
  function displayMenu($msg=NULL) {
    $this->clear();
    if (SRA_InstallerMenu::isValid($menu =& $this->getCurrentMenu())) {
      // display options
      if ($options = $menu->getMenuOptions()) {
        if ($msg) { echo "$msg\n\n"; }
        $maxl = strlen($this->name);
        $title = $menu->getTitle();
        $maxl = strlen($title) > $maxl ? strlen($title) : $maxl;
        $keys = array_keys($options);
        $selBuffer = strlen(count($options) . '') + 3;
        foreach($keys as $key) {
          $maxl = strlen($options[$key]) + $selBuffer > $maxl ? strlen($options[$key]) + $selBuffer : $maxl;
        }
        $exitStr = '[' . ($this->isRootMenu() ? 'X' : 'x') . '] ' . $this->resourcesSys->getString($this->isRootMenu() ? 'installer.exit' : 'installer.return');
        $maxl = strlen($exitStr) > $maxl ? strlen($exitStr) : $maxl;
        $this->_printMenuLine($this->name, $maxl, '+', '-', TRUE);
        $this->_printMenuLine($title, $maxl, '|', ' ', TRUE);
        $this->_printMenuLine('', $maxl, '|', ' ');
        $selCounter = 1;
        $cmdOptions = array('X' => TRUE, 'x' => TRUE);
        $default = '';
        foreach($keys as $key) {
          $id = $selCounter++;
          $cmdOptions[$id] = $key;
          $this->_printMenuLine('[' . $id . '] ' . $options[$key], $maxl);
          if ($menu->defaultItem && $menu->defaultItem->id == $key) { $default = $id; }
        }
        $this->_printMenuLine($exitStr, $maxl);
        $this->_printMenuLine('', $maxl, '+', '-');
        $cmd = $this->getUserInput($this->resourcesSys->getString('installer.command'), $default, FALSE, array_keys($cmdOptions));
        if ($cmd == '0') {
          $this->_menuStack = array($this->_menuStack[0]);
          $this->displayMenu();
        }
        else if ($cmd == 'x' || $cmd == 'X') {
          $cmd == 'X' ? $this->terminate() : $this->showPreviousMenu();
        }
        else if (isset($cmdOptions[$cmd]) && SRA_InstallerMenuItem::isValid($item =& $menu->getMenuItem($cmdOptions[$cmd]))) {
          $this->displayMenu($item->run());
        }
        else {
          $this->sysError(__LINE__);
        }
      }
      // no options, display previous menu
      else if (!$this->isRootMenu()) {
        $this->showPreviousMenu($msg);
      }
      // root menu and no options - system error
      else {
        $this->sysError(__LINE__);
      }
    }
    else {
      $this->sysError(__LINE__);
    }
  }
  // }}}
  
  // {{{ getCurrentMenu
  /**
   * returns a reference to the current menu
   * @access private
   * @return SRA_InstallerMenu
   */
  function &getCurrentMenu() {
    return $this->_menus[$this->_menuStack[count($this->_menuStack) - 1]];
  }
  // }}}
  
  // {{{ getCurrentMenuLevel
  /**
   * returns the current hierarchical menu level (where 1==root menu, 2==first 
   * child menu, etc.)
   * @access private
   * @return int
   */
  function getCurrentMenuLevel() {
    return count($this->_menuStack);
  }
  // }}}
  
  // {{{ getUserInput
  /**
   * waits for a user to provide data
   * @param string $msg the input message
   * @param string $default the default value
   * @param boolean $allowBlank whether or not to allow blank input
   * @param string $validate an optional validation function or an array of 
   * options
   * @param boolean $displayOptions if $validate is an array of options, should 
   * those options be displayed to the user?
   * @access private
   * @return string
   */
  function getUserInput($msg, $default='', $allowBlank=FALSE, $validate=NULL, $displayOptions=FALSE) {
    $optionsTxt = is_array($validate) && count($validate) && $displayOptions ? ' (' . implode('|', $validate) . ')' : '';
    do {
      $prompt = $msg . $optionsTxt . (strlen($default . '') ? ' [' . $default . ']' : '') . '> ';
      if ($this->_readline) {
        $cmd = readline($prompt);
      }
      else {
        echo $prompt;
        $cmd = trim(fgets($this->stdin,1000));
      }
      $cmd = strlen($cmd) ? $cmd : $default;
      if ($validate && $cmd && ((is_array($validate) && !in_array($cmd, $validate)) || (!eval('return ' . $validate . "('$cmd');")))) continue;
      if (strlen($cmd) || $allowBlank) return $cmd;
    } while(TRUE);
  }
  // }}}
  
  // {{{ isRootMenu
  /**
   * returns TRUE if the installer is currently on the root menu
   * @access private
   * @return boolean
   */
  function isRootMenu() {
    return count($this->_menuStack) == 1;
  }
  // }}}
  
  // {{{ menuIsValid
  /**
   * returns TRUE if $menu is a valid menu
   * @param string $menu the identifier of the menu to validate
   * @access private
   * @return boolean
   */
  function menuIsValid($menu) {
    return isset($this->_menus[$menu]);
  }
  // }}}
  
  // {{{ quickInstall
  /**
   * invoked by bin/sra-quick-install.php to quickly configure the framework 
   * using default settings
   * @param string $app if specified, this app id will be added to sierra-config 
   * and [app]/bin/quick-install.php will be run if it exists
   * @param boolean $uninstall whether or not $app should be uninstalled
   * @param boolean $skipModel whether or not the $app entity models should also 
   * be initialized
   * @access private
   * @return void
   */
  function quickInstall($app=NULL, $uninstall=FALSE, $skipModel=FALSE) {
    if (!is_dir(SRA_INSTALLER_FRAMEWORK_TMP_DIR)) {
      echo 'mkdir ' . SRA_INSTALLER_FRAMEWORK_TMP_DIR . "\n";
      mkdir(SRA_INSTALLER_FRAMEWORK_TMP_DIR) ? chmod(SRA_INSTALLER_FRAMEWORK_TMP_DIR, 0777) : $this->terminate(SRA_INSTALLER_FRAMEWORK_TMP_DIR_ERROR);
    }
    include_once(SRA_INSTALLER_FRAMEWORK_DIR . '/lib/core/SRA_Controller.php');
    
    // create configuration files
    $defaultConfigs = SRA_File::getFileList(SRA_Controller::getSysConfDir(), '/^.*-default.*$/', TRUE);
    foreach($defaultConfigs as $defaultConfig) {
      $config = str_replace('-default', '', $defaultConfig);
      if (!file_exists($config)) {
        echo "cp $defaultConfig $config\n";
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
      }
    }
    
    if (!is_dir($logDir = SRA_INSTALLER_FRAMEWORK_DIR . '/' . SRA_DEFAULT_LOG_DIR)) {
      echo "mkdir $logDir\n";
      mkdir($logDir) ? chmod($logDir, 0777) : $this->installer->terminate($this->installer->resources->getString('installer.root.configure.error.logDir', array('logDir' => $logDir)));
    }
    if (!is_dir($appDir = SRA_DIR . '/' . SRA_APP_DIR_NAME)) {
      echo "mkdir $appDir\n";
      mkdir($appDir); 
    }
    
    if (!is_file($conf = SRA_DIR . '/' . SRA_SYS_CONFIG) || $app) {
      echo (is_file($conf) ? 'mod' : 'touch') . " $conf\n";
      $parser =& SRA_XmlParser::getXmlParser($conf, TRUE, TRUE, 'sierra-config');
      $data =& $parser->getData();
      $data = isset($data['sierra-config']) ? $data : array('sierra-config' => array('attributes' => array()));
      if ($app && !$uninstall && !isset($data['sierra-config']['app'])) { $data['sierra-config']['app'] = array(); }
      if ($app && !$uninstall && !isset($data['sierra-config']['app'][$app])) { $data['sierra-config']['app'][$app] = array('attributes' => array('key' => $app)); }
      if ($app && $uninstall && isset($data['sierra-config']['app'][$app])) { unset($data['sierra-config']['app'][$app]); }
      if (isset($data['sierra-config']['app']) && !count($data['sierra-config']['app'])) { unset($data['sierra-config']['app']); }
      $parser->setData($data);
      $parser->write(NULL, SRA_FRAMEWORK_INSTALLER_HEADER);
      SRA_Controller::getSysConf(TRUE);
    }
    if ($app && !$uninstall) {
      if (is_file($script = $appDir . '/' . $app . '/' . SRA_BIN_DIR_NAME . '/' . SRA_INSTALLER_QUICK_INSTALL)) {
        passthru(is_executable($script) ? $script : SRA_File::findInPath('php') . ' ' . $script);
      }
      SRA_Controller::init($app, TRUE, $skipModel, TRUE);
    }
  }
  // }}}
  
  // {{{ run
  /**
   * starts the installer. this method should not be overriden
   * @param string $conf either the sierra relative or absolute path to the 
   * installer xml confuration OR the id of an app that this installer has 
   * been launched for (the confuration file will be looked for in 
   * [app dir]/etc/installer.xml). if not specified, the default framework 
   * installer confuration will be used (sierra/etc/installer.xml)
   * @param string $locale the locale code (optional)
   * @access public
   * @return void
   */
  function run($conf=NULL, $locale=NULL) {
    $this->_readline = function_exists('readline');
    if (!$this->_readline) { $this->stdin = fopen('php://stdin', 'r'); }
    if (!is_dir(SRA_INSTALLER_FRAMEWORK_TMP_DIR)) { 
      mkdir(SRA_INSTALLER_FRAMEWORK_TMP_DIR) ? chmod(SRA_INSTALLER_FRAMEWORK_TMP_DIR, 0777) : $this->terminate(SRA_INSTALLER_FRAMEWORK_TMP_DIR_ERROR);
    }
    if ($locale) { $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $locale; }
    include_once(SRA_INSTALLER_FRAMEWORK_DIR . '/lib/core/SRA_Controller.php');
    $this->resourcesSys =& SRA_Controller::getSysResources();
    $conf = $conf ? $conf : SRA_INSTALLER_FRAMEWORK_INSTALLER_CONFIG;
    if (!file_exists($conf) && SRA_Controller::appKeyIsValid($conf)) {
      SRA_Controller::init($conf, TRUE, TRUE, TRUE);
      $conf = SRA_Controller::getAppConfDir() . '/' . SRA_INSTALLER_CONFIG_FILE_NAME;
      $this->resourcesApp =& SRA_Controller::getAppResources();
    }
    else if (!file_exists($conf) && file_exists(SRA_INSTALLER_FRAMEWORK_DIR . '/' . $conf)) {
      $conf = SRA_INSTALLER_FRAMEWORK_DIR . '/' . $conf;
    }
    if (!file_exists($conf) || SRA_Error::isError($parser =& SRA_XmlParser::getXmlParser($conf, TRUE))) {
      $this->terminate($this->resourcesSys->getString('installer.error.config', array('config' => $conf)));
    }
    else {
      // validate confuration
      $conf =& $parser->getData(array('installer'));
      $this->_conf = $conf['attributes'];
      if (isset($this->_conf['resources'])) {
        $this->resources =& SRA_ResourceBundle::getBundle($this->_conf['resources']);
      }
      else if ($this->resourcesApp) {
        $this->resources =& $this->resourcesApp;
      }
      else {
        $this->resources =& $this->resourcesSys;
      }
      if (!SRA_ResourceBundle::isValid($this->resources)) {
        $this->terminate($this->resourcesSys->getString('installer.error.resources', array('resources' => $this->_conf['resources'])));
      }
      if (!$this->_conf['resource']) {
        $this->terminate($this->resourcesSys->getString('installer.error.resource'));
      }
      $this->name = $this->resources->getString($this->_conf['resource']);
      if (!isset($this->_conf['src'])) {
        $this->terminate($this->resourcesSys->getString('installer.error.src'));
      }
      require_once($this->_conf['src']);
      $className = isset($this->_conf['class']) ? $this->_conf['class'] : str_replace('.' . SRA_SYS_PHP_EXTENSION, '', basename($this->_conf['src']));
      if (!class_exists($className)) {
        $this->terminate($this->resourcesSys->getString('installer.error.class', array('class' => $className)));
      }
      if (!is_object($this->installer = new ${className}())) {
        $this->terminate($this->resourcesSys->getString('installer.error.class.instantiate', array('class' => $className)));
      }
      $this->installer->installer =& $this;
      
      // load menus
      $keys = array_keys($conf['menu']);
      foreach ($keys as $key) { $this->_menus[$key] = TRUE; }
      foreach ($keys as $key) {
        if (!SRA_InstallerMenu::isValid($this->_menus[$key] = new SRA_InstallerMenu($conf['menu'][$key], $this))) {
          $this->terminate($this->resourcesSys->getString('installer.error.menu', array('menu' => $key)));
        }
      }
      if (!SRA_InstallerMenu::isValid($this->_menus[$this->_conf['root-menu']])) {
        $this->terminate($this->resourcesSys->getString('installer.error.rootMenu', array('menu' => $this->_conf['root-menu'])));
      }
      
      array_push($this->_menuStack, $this->_conf['root-menu']);
      $this->displayMenu();
    }
  }
  // }}}
  
  // {{{ setMenu
  /**
   * sets the current menu to $menu. returns TRUE on success FALSE otherwise
   * @param string $menu the identifier of the new menu
   * @access private
   * @return boolean
   */
  function setMenu($menu) {
    $results = FALSE;
    $currentMenu =& $this->getCurrentMenu();
    if ($this->menuIsValid($menu) && $menu != $currentMenu->id) {
      $this->_menuStack[] = $menu;
      $results = TRUE;
    }
    return $results;
  }
  // }}}
  
  // {{{ setPreviousMenu
  /**
   * pops the current menu off the menu stack
   * @access private
   * @return void
   */
  function setPreviousMenu() {
    array_pop($this->_menuStack);
  }
  // }}}
  
  // {{{ showPreviousMenu
  /**
   * shows the previous menu (or exits if currently showing the root menu)
   * @param string $msg an optional message to display
   * @access private
   * @return void
   */
  function showPreviousMenu($msg=NULL) {
    if ($this->isRootMenu()) {
      $this->terminate($msg);
    }
    else {
      $this->setPreviousMenu();
      $this->displayMenu($msg);
    }
  }
  // }}}
  
  // {{{ sysError
  /**
   * displays the system error including file name and line # and terminates the 
   * installer
   * @param int $line the line number where the system error occurred
   * @access private
   * @return void
   */
  function sysError($line) {
    $this->terminate($this->resourcesSys->getString('installer.error.sys', array('msg' => basename(__FILE__) . '/' . $line)));
  }
  // }}}
  
  // {{{ terminate
  /**
   * exits the installer. this method should not be overriden
   * @param string $msg an optional message to display prior to exiting
   * @access private
   * @return void
   */
  function terminate($msg=NULL) {
    if ($msg) echo $msg . "\n";
    if (!$this->_readline) { fclose($this->stdin); }
    exit;
  }
  // }}}
  
  // {{{ waitForEnter
  /**
   * displays the "press enter to continue" message and waits for the user to do 
   * so
   * @param string $msg the message to display
   * @access public
   * @return void
   */
  function waitForEnter($msg) {
    $this->clear();
    echo $msg . "\n\n";
    $this->getUserInput($this->resourcesSys->getString('installer.waitForEnter'), '', TRUE);
  }
  // }}}
  
  
  // private methods
  // {{{ _printMenuLine
  /**
   * prints a menu line based on the parameters specified
   * @param string $text the menu line text
   * @param int $length the longest menu line $text length
   * @param char $edgeChar the character to use on the edges
   * @param char $spaceChar the character to use for spacing
   * @param boolean $center whether or not to center $text
   * @access private
   * @return void
   */
  function _printMenuLine($text, $length, $edgeChar='|', $spaceChar=' ', $center=FALSE) {
    echo $edgeChar . $spaceChar;
    $buffer = $length - strlen($text);
    $lbuffer = $center ? floor($buffer/2) : 0;
    $rbuffer = $center ? ceil($buffer/2) : $buffer;
    for($i=0; $i<$lbuffer; $i++) echo $spaceChar;
    echo $text;
    for($i=0; $i<$rbuffer; $i++) echo $spaceChar;
    echo $spaceChar . $edgeChar . "\n";
  }
  // }}}
  
  
  // static methods
  
  // {{{ isValid
  /**
   * static method that returns true if $object is a SRA_Installer (or sub-class 
   * instance)
   * @param object $object the object to evaluate
   * @access public
   * @return boolean
   */
  function isValid( &$object ) {
    return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_installer' || is_subclass_of($object, 'sra_installer')));
  }
  // }}}
}
// }}}
?>
