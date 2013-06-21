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

// }}}

// {{{ SRA_InstallerMenuItem
/**
 * defines an action that may be invoked through the installer. this action may 
 * be either displaying another sub-menu, or collecting some data from the user 
 * and using that data to make a configuration change, or some other 
 * installation related action
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util.installer
 */
class SRA_InstallerMenuItem {
  // public attributes
  /**
	 * the unique identifier for this menu item
	 * @type string
	 */
	var $id;
  
  
  // private attributes
  
  /**
	 * the base configuration of this menu item
	 * @type hash
	 */
	var $_conf;
  
  /**
	 * if the purpose of this menu-item is to display data (the data displayed is 
   * the string returned by "handler"), this attribute can be set to TRUE (1) in 
   * which case the screen will be cleared, the data displayed, and the user 
   * prompted "Press enter to continue"
	 * @type boolean
	 */
	var $_displayData;
  
  /**
	 * whether or not to exit the installer after this menu item action has been 
   * completed successfully
	 * @type boolean
	 */
	var $_exit;
  
  /**
	 * the installer instance this menu belongs to
	 * @type SRA_Installer
	 */
	var $_installer;
  
  /**
	 * the menu instance this menu item belongs to
	 * @type SRA_InstallerMenu
	 */
	var $_menu;
  
  /**
	 * the name of the method in the installer object that should be invoked to 
   * handle the action associated with this menu item. either "handler" or 
   * "menu" may be specified, but not both. this method should have the 
   * following signature: 
   *   (string : $menuItem, hash : $data) : string
   * where $menuItem is the menu-item "key", $data is a hash (indexed by 
   * resource identifier) containing data collection based on the resource 
   * identifiers specified in the "prompt" attribute and the return value 
   * (optional) is a message that should be displayed to the user
	 * @type string
	 */
	var $_method;
  
  /**
	 * this optional attribute may be used to dynamically populate menu items. it 
   * should be the name of an installer method that should be invoked and return 
   * an array of hashes (or single hash) each of which defines a single menu 
   * item to be displayed in the menu where each hash may have the same 
   * attributes (minus "builder") as the "menu-item" element. if this method 
   * return NULL, this menu-item will be excluded from the menu. the menu items 
   * configurations returned will automatically inherit attributes of this 
   * menu-item that are not defined
	 * @type string
	 */
	var $_methodBuilder;
  
  /**
	 * if "prompt" is specified, this attribute may be used to specify the name of 
   * a method in the installer object that should be invoked to determine the 
   * default values for the data that the user will be prompted for. this method 
   * should have the following signature:
   *   (string : $menuItem, string : $id, hash : $data) : string
   * where $menuItem is the menu-item "key", $id is the "prompt" resource 
   * identifier of the question that this method should return the default value 
   * for, and $data is the hash (indexed by resource identifier) of data that 
   * has already been collected. if this method returns NULL, the "prompt" 
   * default value will be used ([default])
	 * @type string
	 */
	var $_methodDefaults;
  
  /**
	 * this optional method may be used to specify the name of a method in the 
   * installer object that should be invoked to determine whether or not this 
   * menu-item should be included in the menu. this method should have the 
   * following signature:
   *   (string : $menuItem) : boolean
   * where $menuItem is the menu-item "key"
	 * @type string
	 */
	var $_methodInclude;
  
  /**
	 * if "prompt" is specified, this attribute may be used to specify the name of 
   * a method in the installer object that should be invoked to determine 
   * whether or not user input is valid for a given "prompt" resource 
   * identifier. this method should have the following signature:
   *   (string : $menuItem, string : $id, string : $val, hash : $data) : boolean
   * where $menuItem is the menu-item "key", $id is the "prompt" resource 
   * identifier of the question that this method should validate, $val is the 
   * value provided by the user, and $data is the hash (indexed by resource 
   * identifier) of data that has already been collected. if defined, this 
   * method should return TRUE if the data is valid, FALSE otherwise. if it 
   * returns FALSE, the entire menu-item action will be aborted
	 * @type string
	 */
	var $_methodValidator;
  
  /**
	 * whether or not the previous menu should be displayed after this menu item 
   * action has been completed successfully
	 * @type boolean
	 */
	var $_previousMenu;
  
  /**
	 * this attribute may be used to prompt the user for data prior to invoking 
   * $_method. when set, this attribute will be an array of hashes (indexed by 
   * the prompt value key - the resource identifier minus $_promptKeyRemove), 
   * where each each has the following keys:
   *   allowNull:       boolean indicating whether or not this question is 
   *                    optional
   *   default:         the default value
   *   options:         an array of valid response options
   *   question:        the question to prompt the user with
   *   removeIfDefault: boolean indicating whether or not this prompt value 
   *                    should be excluded from the $data hash if it is equal to 
   *                    [default]
   *   validateFailMsg: custom message that should be displayed in the user 
   *                    enter invalid data (when $_methodValidator returns 
   *                    FALSE)
	 * @type array
	 */
	var $_prompt;
  
  /**
	 * an optional header that should be displayed prior to prompting the user for 
   * data
	 * @type string
	 */
	var $_promptHeader;
  
  /**
	 * the resource identifier for this menu item's title. use the "getTitle" 
   * method to get the localized value
	 * @type string
	 */
	var $_resource;
  
  
  /**
	 * resource identifier for the confirmation message that should be displayed 
   * (user must respond positively) before the action associated with this menu 
   * item will be invoked. use the "getConfirm" method to get the localized 
   * value
	 * @type string
	 */
	var $_resourceConfirm;
  
  /**
	 * the name of a method in "class" that should be invoked to retrieve custom 
   * resource keys to included in "resource" and "confirm" whenever they are rendered
	 * @type string
	 */
	var $_resourceKeys;
  
  /**
	 * if this menu item should display another menu, this attribute will be the 
   * identifier of that menu
	 * @type SRA_InstallerMenu
	 */
	var $_subMenu;
  
  
	// {{{ SRA_InstallerMenuItem
	/**
	 * parses the menu item configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_InstallerMenu $menu the menu instance this menu item belongs to
   * @param SRA_Installer $installer the installer instance this menu belongs to
   * @access public
	 */
	function SRA_InstallerMenuItem($conf, &$menu, &$installer) {
    if (isset($conf['attributes'])) $conf = $conf['attributes'];
    
    $this->_conf = $conf;
    $this->id = $conf['key'];
    $this->_resourceConfirm = isset($conf['confirm']) ? $conf['confirm'] : NULL;
    $this->_displayData = isset($conf['display-data']) && $conf['display-data'] == '1';
    $this->_exit = isset($conf['exit']) && $conf['exit'] == '1';
    $this->_resource = $conf['resource'] ? $conf['resource'] : $this->id;
    $this->_resourceKeys = isset($conf['resource-keys']) ? $conf['resource-keys'] : NULL;
    $this->_installer =& $installer;
    $this->_previousMenu = isset($conf['previous-menu']) && $conf['previous-menu'] == '1';
    $this->_subMenu = isset($conf['menu']) ? $conf['menu'] : NULL;
    if ($this->_subMenu && !$installer->menuIsValid($this->_subMenu)) {
      $err = 'menu ' . $this->_subMenu . ' is not valid';
    }
    $this->_menu =& $menu;
    $this->_method = isset($conf['handler']) ? $conf['handler'] : NULL;
    if ($this->_method && !method_exists($installer->installer, $this->_method)) {
      $err = 'handler method ' . $this->_method . ' is not valid';
    }
    $this->_methodBuilder = isset($conf['builder']) ? $conf['builder'] : NULL;
    if ($this->_methodBuilder && !method_exists($installer->installer, $this->_methodBuilder)) {
      $err = 'builder method ' . $this->_methodBuilder . ' is not valid';
    }
    $this->_methodDefaults = isset($conf['defaults-handler']) ? $conf['defaults-handler'] : NULL;
    if ($this->_methodDefaults && !method_exists($installer->installer, $this->_methodDefaults)) {
      $err = 'defaults-handler method ' . $this->_methodDefaults . ' is not valid';
    }
    $this->_methodInclude = isset($conf['include-handler']) ? $conf['include-handler'] : NULL;
    $methodInclude = $this->_methodInclude;
    $reverse = SRA_Util::beginsWith($methodInclude, '!');
    if ($reverse) { $methodInclude = substr($methodInclude, 1); }
    if ($methodInclude && !method_exists($installer->installer, $methodInclude)) {
      $err = 'include-handler method ' . $this->_methodInclude . ' is not valid';
    }
    $this->_methodValidator = isset($conf['validator']) ? $conf['validator'] : NULL;
    if ($this->_methodValidator && !method_exists($installer->installer, $this->_methodValidator)) {
      $err = 'validator method ' . $this->_methodValidator . ' is not valid';
    }
    if ($this->_resourceKeys && !method_exists($installer->installer, $this->_resourceKeys)) {
      $err = 'resource-keys method ' . $this->_resourceKeys . ' is not valid';
    }
    if (isset($conf['prompt'])) {
      $promptKeyRemove = isset($conf['prompt-key-remove']) ? $conf['prompt-key-remove'] : NULL;
      $this->_prompt = array();
      foreach(explode(' ', $conf['prompt']) as $resource) {
        $prompt = explode('|', $installer->resources->getString($resource));
        $id = $promptKeyRemove ? str_replace($promptKeyRemove, '', $resource) : $resource;
        $this->_prompt[$id] = array();
        $this->_prompt[$id]['allowNull'] = isset($prompt[4]) && $prompt[4] == '1';
        if (isset($prompt[2]) && $prompt[2] !== '') { $this->_prompt[$id]['default'] = $prompt[2]; }
        if (isset($prompt[1]) && $prompt[1] !== '') { $this->_prompt[$id]['options'] = explode('/', $prompt[1]); }
        $this->_prompt[$id]['question'] = $prompt[0];
        $this->_prompt[$id]['removeIfDefault'] = isset($prompt[3]) && $prompt[3] == '1';
        if (isset($prompt[5]) && $prompt[5] !== '') { $this->_prompt[$id]['validateFailMsg'] = $prompt[5]; }
      }
      $this->_promptHeader = isset($conf['prompt-header']) ? $installer->resources->getString($conf['prompt-header']) : NULL;
    }
    if (!$this->_subMenu && !$this->_method) {
      $err = 'either "menu" or "handler" must be specified';
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_InstallerMenuItem: ' . $this->id . ' Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
  
	// {{{ getConfirm
	/**
	 * returns the localized confirm message for this menu item (or NULL if there 
   * is not one)
	 * @access public
	 * @return string
	 */
	function getConfirm() {
    $resourceKeys = $this->_resourceKeys;
		return $this->_resourceConfirm ? $this->_installer->resources->getString($this->_resourceConfirm, $resourceKeys ? $this->_installer->installer->${resourceKeys}() : NULL) : NULL;
	}
	// }}}
  
	// {{{ getMenuItem
	/**
	 * returns the menu item identified by $id. this will usually be an instance 
   * of this menu item, unless $this->_methodBuilder is defined and returns 
   * multiplie menu items
   * @param string $id the identifier of the menu item to return
	 * @access public
	 * @return SRA_InstallerMenuItem
	 */
	function &getMenuItem($id) {
		$menuItems =& $this->getMenuItems();
    $nl = NULL;
    return isset($menuItems[$id]) && SRA_InstallerMenuItem::isValid($menuItems[$id]) ? $menuItems[$id] : $nl;
	}
	// }}}
  
	// {{{ getMenuItems
	/**
	 * returns an associative array of menu items corresponding with this menu 
   * item. this array will be indexed by the menu item identifier. usually this 
   * will simply be a single SRA_InstallerMenuItem instance, UNLESS 
   * $this->_methodBuilder has been defined. this method returns NULL if no 
   * items should be included for this menu item (if $this->_methodBuilder is 
   * defined and does not return any items or if $this->_methodInclude is 
   * defined and returns FALSE)
	 * @access public
	 * @return SRA_InstallerMenuItem[]
	 */
	function &getMenuItems() {
    $options = array();
    $methodInclude = $this->_methodInclude;
    $reverse = SRA_Util::beginsWith($methodInclude, '!');
    if ($reverse) { $methodInclude = substr($methodInclude, 1); }
    if (!$methodInclude || (!$reverse && $this->_installer->installer->${methodInclude}()) || ($reverse && !$this->_installer->installer->${methodInclude}())) {
      $methodBuilder = $this->_methodBuilder;
      if (!$methodBuilder) {
        $options[$this->id] =& $this;
      }
      else if ($builderOptions = $this->_installer->installer->${methodBuilder}()) {
        foreach($builderOptions as $conf) {
          $conf = array_merge($this->_conf, $conf);
          if ($conf['builder'] == $this->_conf['builder']) unset($conf['builder']);
          if (SRA_InstallerMenuItem::isValid($item = new SRA_InstallerMenuItem($conf, $this->_menu, $this->_installer)) && ($items =& $item->getMenuItems())) {
            $keys = array_keys($items);
            foreach($keys as $key) {
              $options[$key] = $items[$key];
            }
          }
        }
      }
    }
    $nl = NULL;
    return count($options) ? $options : $nl;
	}
	// }}}
  
	// {{{ getMenuOptions
	/**
	 * returns a hash representing the options that should be included in the menu 
   * that this menu-item pertains. the key in this hash will be the  menu-item 
   * id and the value will be the menu option title. this method returns NULL if 
   * no options should be included in the menu for this menu item
	 * @access public
	 * @return hash
	 */
	function getMenuOptions() {
    $options = array();
    if ($items =& $this->getMenuItems()) {
      $keys = array_keys($items);
      foreach($keys as $key) {
        $options[$items[$key]->id] = $items[$key]->getTitle();
      }
    }
    return count($options) ? $options : NULL;
	}
	// }}}
  
	// {{{ getTitle
	/**
	 * returns the localized title for this menu
	 * @access public
	 * @return string
	 */
	function getTitle() {
    $resourceKeys = $this->_resourceKeys;
		return $this->_installer->resources->getString($this->_resource, $resourceKeys ? $this->_installer->installer->${resourceKeys}() : NULL);
	}
	// }}}
  
	// {{{ run
	/**
	 * invoked by the installer when this menu item has been selected by the user
   * returns a optional notification string to display on top of the next menu
	 * @access public
	 * @return string
	 */
	function run() {
    $ret = '';
    if (!$this->_resourceConfirm || $this->_installer->confirm($this->getConfirm())) {
      // invoke handler method
      if ($this->_method) {
        $data = NULL;
        if ($this->_prompt) {
          $data = array();
          $this->_installer->clear();
          if ($this->_promptHeader) {
            echo $this->_promptHeader . "\n\n";
          }
          $keys = array_keys($this->_prompt);
          $methodDefaults = $this->_methodDefaults;
          $methodValidator = $this->_methodValidator;
          for($i=0; $i<count($keys); $i++) {
            $key = $keys[$i];
            $default = $methodDefaults && ($temp = $this->_installer->installer->${methodDefaults}($this->id, $key, $data)) ? $temp : $this->_prompt[$key]['default'];
            
            if ($methodDefaults && $default == SRA_INSTALLER_PROMPT_SKIP) { continue; }
            
            $data[$key] = $this->_installer->getUserInput($this->_prompt[$key]['question'], $default, $this->_prompt[$key]['allowNull'], $this->_prompt[$key]['options'], TRUE);
            if (trim(strtolower($data[$key])) == strtolower($this->_installer->resourcesSys->getString('installer.prompt.na'))) { $data[$key] = NULL; }
            if ($this->_prompt[$key]['removeIfDefault'] && $data[$key] == $this->_prompt[$key]['default']) {
              unset($data[$key]);
            }
            if ($methodValidator && !$this->_installer->installer->${methodValidator}($this->id, $key, $data[$key], $data)) {
              echo ($this->_prompt[$key]['validateFailMsg'] ? $this->_prompt[$key]['validateFailMsg'] : $this->_installer->resourcesSys->getString('installer.error.validator')) . "\n";
              $i--;
            }
          }
        }
        $method = $this->_method;
        $msg = $this->_installer->installer->${method}($this->id, $data);
        $ret = $this->_displayData && $msg ? $this->_installer->waitForEnter($msg) : $msg;
        if ($this->_previousMenu) { $this->_installer->setPreviousMenu(); }
        if ($this->_exit) { $this->_installer->terminate($ret); }
      }
      // display nested menu
      if ($this->_subMenu) {
        $this->_installer->setMenu($this->_subMenu);
      }
    }
    return $ret;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a 
   * SRA_InstallerMenuItem object
	 * @param object $object the object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_installermenuitem');
	}
	// }}}
  
}
// }}}
?>
