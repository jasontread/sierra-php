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
require_once('SRA_InstallerMenuItem.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_InstallerMenu
/**
 * defines a single installer menu. an installer menu displays options available 
 * to the user in that menu. the installer uses a hierarchical navigation model 
 * and will automatically add a menu option to return to the parent menu (in a 
 * nested menu) or exit the installer (from the top-level menu)
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util.installer
 */
class SRA_InstallerMenu {
  // public attributes
  /**
	 * the unique identifier for this menu
	 * @type string
	 */
	var $id;
  
  /**
	 * the default menu item for this menu (if applicable)
	 * @type SRA_InstallerMenuItem
	 */
	var $defaultItem;
  
  
  // private attributes
  /**
	 * the installer instance this menu belongs to
	 * @type SRA_Installer
	 */
	var $_installer;
  
  /**
	 * the menu items belonging to this menu indexed by menu item "key"
	 * @type SRA_InstallerMenuItem[]
	 */
	var $_items = array();
  
  /**
	 * the resource identifier for this menu's title. use the "getTitle" method to 
   * get the localized value
	 * @type string
	 */
	var $_resource;
  
  /**
	 * the name of a method in the installer that should be invoked to retrieve 
   * custom resource keys to include in "resource" whenever the title for this 
   * menu is rendered
	 * @type string
	 */
	var $_resourceKeys;
  
  
	// {{{ SRA_InstallerMenu
	/**
	 * parses the menu configuration data and sets the appropriate instance 
   * variables. if there is a problem with the configuration, the instance 
   * variable "err" will be assigned to an appropriate error object (the error 
   * will be logged)
   * @param array $conf the configuration to parse
   * @param SRA_Installer $installer the installer instance this menu belongs to
   * @access public
	 */
	function SRA_InstallerMenu($conf, &$installer) {
    $this->id = $conf['attributes']['key'];
    $this->_resource = $conf['attributes']['resource'] ? $conf['attributes']['resource'] : $this->id;
    $this->_resourceKeys = isset($conf['attributes']['resource-keys']) ? $conf['attributes']['resource-keys'] : NULL;
    $this->_installer =& $installer;
    if (!$conf['menu-item']) {
      $err = 'no menu items specified';
    }
    else {
      $keys = array_keys($conf['menu-item']);
      foreach ($keys as $key) {
        if (!SRA_InstallerMenuItem::isValid($this->_items[$key] = new SRA_InstallerMenuItem($conf['menu-item'][$key], $this, $installer))) {
          $err = "menu-item $key produced error";
          break;
        }
      }
    }
    if (isset($conf['attributes']['default']) && !isset($this->_items[$conf['attributes']['default']])) {
      $err = 'default menu-item ' . $conf['attributes']['default'] . ' is not valid';
    }
    else if (isset($conf['attributes']['default'])) {
      $this->defaultItem =& $this->_items[$conf['attributes']['default']];
    }
    if ($this->_resourceKeys && !method_exists($installer->installer, $this->_resourceKeys)) {
      $err = 'resource-keys method ' . $this->_resourceKeys . ' is not valid';
    }
    
    if ($err) { $this->err = SRA_Error::logError('SRA_InstallerMenu: ' . $this->id . ' Failed - ' . $err, __FILE__, __LINE__); }
	}
	// }}}
  
	// {{{ getMenuItem
	/**
	 * returns the menu item identified by $id
   * @param string $id the identifier of the menu item to return
	 * @access public
	 * @return SRA_InstallerMenuItem
	 */
	function &getMenuItem($id) {
		$keys = array_keys($this->_items);
    foreach($keys as $key) {
      $items =& $this->_items[$key]->getMenuItems();
      if (isset($items[$id]) && SRA_InstallerMenuItem::isValid($items[$id])) {
        return $items[$id];
      }
    }
    $nl = NULL;
    return $nl;
	}
	// }}}
  
	// {{{ getMenuOptions
	/**
	 * returns a hash of menu options where the key is the menu-item identifier 
   * and the value is the menu-item title
	 * @access public
	 * @return hash
	 */
	function getMenuOptions() {
    $options = array();
		$keys = array_keys($this->_items);
    foreach($keys as $key) {
      if ($itemOptions = $this->_items[$key]->getMenuOptions()) {
        $options = array_merge($options, $itemOptions);
      }
    }
    return $options;
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
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * Static method that returns true if the object parameter is a 
   * SRA_InstallerMenu object
	 * @param object $object the object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_installermenu');
	}
	// }}}
  
}
// }}}
?>
