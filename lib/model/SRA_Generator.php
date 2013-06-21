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

// {{{ SRA_Generator
/**
 * Used to generate an entity model entity
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_Generator {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	/**
   * whether or not the ddl name for this Entity/Attribute was generated
	 * @type boolean
	 */
	var $_ddlNameGenerated = FALSE;

  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Generator
	/**
	 * Abstract superclass for all SRA_Generators
   * @access  private
	 */
	function SRA_Generator() {}
	// }}}
	
  
  // public operations  
	
	
	// {{{ getDtdName
	/**
	 * Returns the DTD name value for this entity
   * @access  public
	 * @return String
	 */
	function getDtdName() {
		if ($this->_dtdCamelCase) {
			return $this->_name;
		}
		else {
			return SRA_Util::camelCaseToDashes($this->_name);
		}
	}
	// }}}
	
	
	// {{{ getTypeDtdName
	/**
	 * Returns the DTD name value for this entity's type
   * @access  public
	 * @return String
	 */
	function getTypeDtdName() {
		if ($this->_dtdCamelCase) {
			return $this->_type;
		}
		else {
			return SRA_Util::camelCaseToDashes($this->_type);
		}
	}
	// }}}
	
	
	// {{{ getFileDtdName
	/**
	 * Returns the DTD name value for a file based on the constant value 
	 * SRA_FILE_DTD_NAME
   * @access  public
	 * @return String
	 */
	function getFileDtdName() {
		if ($this->_dtdCamelCase) {
			return SRA_ENTITY_MODELER_FILE_DTD_NAME;
		}
		else {
			return SRA_Util::camelCaseToDashes(SRA_ENTITY_MODELER_FILE_DTD_NAME);
		}
	}
	// }}}
	
	
	// {{{ hasDtdSubElements
	/**
	 * Returns TRUE if this SRA_Generator contains DTD sub-elements (sub entities or 
	 * files)
   * @access  public
	 * @return String
	 */
	function hasDtdSubElements() {
		$keys = array_keys($this->_attributeGenerators);
		foreach ($keys as $key) {
			if (!$this->_attributeGenerators[$key]->_skipPersistence && ($this->_attributeGenerators[$key]->_isFile || $this->_attributeGenerators[$key]->isEntity())) {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getResource
	/**
	 * determines the correct resource to use
   * @access  public
	 * @return String
	 */
	function getResource(& $conf, & $resources, $prefixes = FALSE, $append = '', $final = FALSE) {
		if (isset($conf['attributes']['resource' . $append])) {
			return $conf['attributes']['resource' . $append];
		}
		else {
			$key = $conf['attributes']['key'] . $append;
			if ($prefixes) {
				$prefixes = !is_array($prefixes) ? array($prefixes) : $prefixes;
				foreach ($prefixes as $prefix) {
					if (SRA_Generator::validateResource($prefix . '.' . $key, $resources)) {
						return $prefix . '.' . $key;
					}
				}
			}
			if (SRA_Generator::validateResource($key, $resources)) {
				return $key;
			}
		}
		if ($final && SRA_Generator::validateResource($final, $resources)) {
			return $final;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getHelpResource
	/**
	 * determines the correct help resource to use
   * @access  public
	 * @return String
	 */
	function getHelpResource(& $conf, & $resources, $prefixes = FALSE, $append = '', $final = FALSE) {
		return $this->getResource($conf, $resources, $prefixes, '-help', $final);
	}
	// }}}
	
	
	// {{{ getResourceName
	/**
	 * Returns the name of this attribute's associated resource, parsed for 
	 * imbedded variables
   * @access  public
	 * @return String
	 */
	function getResourceName() {
		return str_replace("}", " . '", str_replace("{\$", "' . \$this->_", $this->_resource)); 
	}
	// }}}
	
	
	// {{{ getHelpResourceName
	/**
	 * Returns the name of this attribute's associated help resource, parsed for 
	 * imbedded variables
   * @access  public
	 * @return String
	 */
	function getHelpResourceName() {
		return str_replace("}", " . '", str_replace("{\$", "' . \$this->_", $this->_resourceHelp)); 
	}
	// }}}
	
	
	// {{{ setEntityGenerators
	/**
	 * Sets a reference to all of the entity generators
	 * @param SRA_EntityGenerator[] $entityGenerators an array of all of the 
	 * SRA_EntityGenerator objects in the entity model
   * @access  public
	 * @return void
	 */
	function setEntityGenerators(& $entityGenerators) {
		$this->_entityGenerators =& $entityGenerators;
    if (isset($this->_attributeGenerators)) {
      $keys = array_keys($this->_attributeGenerators);
      foreach ($keys as $key) {
        if (SRA_Error::isError($this->_attributeGenerators[$key]->setEntityGenerators($entityGenerators))) {
          $msg = "SRA_Generator::setEntityGenerators: Failed for " . $this->_name;
          return SRA_Error::logError($msg, __FILE__, __LINE__);
        }
      }
    }
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * Returns a string representation of this object
   * @access  public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
	
	// Static methods
	
	// {{{ getDdlName()
	/**
	 * Static method that returns the correct ddl name (for columns or tables) to 
	 * use given the actual etc specified value (if not null), and the base name 
	 * of the Entity/Attribute
	 *
	 * @param  string $confName the configuration specified name
	 * @param string $name the name of the Entity/Attribute
	 * @param boolean $ddlUpperCase whether or not generated ddl should be uppercase
	 * @param string $prefix prefix to add to any generated ddl name
	 * @param string $postfix postfix to add to any generated ddl name
	 * @param boolean $cardinality whether or not cardinality is used (will result 
	 * in any trailing "s" being removed from generated names
	 * @access	public
	 * @return	string
	 */
	function getDdlName($confName, $name, $ddlUpperCase, $prefix = '', $postfix = '', $cardinality = FALSE) {
		if (isset($confName) && !$prefix && !$postfix) {
			return $confName;
		}
		else {
			$this->_ddlNameGenerated = TRUE;
			if (!$this->_ddlCamelCase) {
				$name = SRA_Util::camelCaseToUnderscores($name, $ddlUpperCase);
			}
			$prefix = $prefix && !$this->_ddlCamelCase && !SRA_Util::endsWith($prefix, '_') ? $prefix . '_' : $prefix;
      if (SRA_Util::beginsWith($name, $prefix)) $prefix = '';
			$postfix = $postfix && !$this->_ddlCamelCase && !SRA_Util::beginsWith($postfix, '_') ? '_' . $postfix : $postfix;
      if (SRA_Util::endsWith($name, $postfix)) $postfix = '';
			$name = $prefix . $name . $postfix;
			
			// remove trailing s for plural attributes
			if ($cardinality && SRA_Util::endsWith($name, 's') && !SRA_Util::endsWith($name, 'is')) {
				$name = substr($name, 0, strlen($name) - 1);
			}
			return $name;
		}
	}
	// }}}
  
  
	// {{{ getDefaultViews
	/**
	 * returns the default views configuration for a given conf. returns an 
   * SRA_Error object if an error occurs
	 * @param array $conf 
   * @access public
	 * @return mixed
	 */
	function getDefaultViews($conf, & $globalViews) {

    // default views
    $defaultViews = array();
    $keys = array_keys($conf);
    foreach($keys as $key) {
      $defaultViews[$key] = $conf[$key]['attributes'];
      if (!isset($defaultViews[$key]['id']) || !isset($defaultViews[$key]['types']) || !isset($defaultViews[$key]['view'])) {
        $msg = "SRA_Generator::getDefaultViews: Failed - Cannot instantiate default views for entity-model '${conf}'. id, types, and view are required for all default views";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      else if (!$globalViews || !isset($globalViews[$defaultViews[$key]['view']])) {
        $msg = "SRA_Generator::getDefaultViews: Failed - Cannot instantiate default views for entity-model '${conf}'. " . $defaultViews[$key]['view'] . ' is not a valid global view';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
      $defaultViews[$key]['includeAttrs'] = isset($defaultViews[$key]['attrs']) && !SRA_Util::beginsWith($defaultViews[$key]['attrs'], '!') ? explode(' ', $defaultViews[$key]['attrs']) : NULL;
      $defaultViews[$key]['excludeAttrs'] = isset($defaultViews[$key]['attrs']) && SRA_Util::beginsWith($defaultViews[$key]['attrs'], '!') ? explode(' ', substr($defaultViews[$key]['attrs'], 1)) : NULL;
      $defaultViews[$key]['includeEntities'] = isset($defaultViews[$key]['entities']) && !SRA_Util::beginsWith($defaultViews[$key]['entities'], '!') ? explode(' ', $defaultViews[$key]['entities']) : NULL;
      $defaultViews[$key]['excludeEntities'] = isset($defaultViews[$key]['entities']) && SRA_Util::beginsWith($defaultViews[$key]['entities'], '!') ? explode(' ', substr($defaultViews[$key]['entities'], 1)) : NULL;
      $defaultViews[$key]['includeDepends'] = isset($defaultViews[$key]['depends']) && !SRA_Util::beginsWith($defaultViews[$key]['depends'], '!') ? explode(' ', $defaultViews[$key]['depends']) : NULL;
      $defaultViews[$key]['excludeDepends'] = isset($defaultViews[$key]['depends']) && SRA_Util::beginsWith($defaultViews[$key]['depends'], '!') ? explode(' ', substr($defaultViews[$key]['depends'], 1)) : NULL;
      $defaultViews[$key]['types'] = explode(' ', $defaultViews[$key]['types']);
    }
    return $defaultViews;
	}
	// }}}

	
	// {{{ getViews()
	/**
	 * Static method that instantiate any views specified in the given configuration
	 *
	 * @param  array $viewConf the view configuration to check
	 * @param SRA_EntityViewProcessor[] $viewProcessors view processors that may be applied to the views
	 * @param boolean $isAttribute whether or not the views are for an attribute
	 * @access	public
	 * @return	SRA_EntityView[]
	 */
	function getViews(& $viewConf, & $viewProcessors, $isAttribute = FALSE) {
		$views = array();
		if (is_array($viewConf) && count($viewConf)) {
			$keys = array_keys($viewConf);
			foreach ($keys as $key) {
        $iterate = $isAttribute && isset($viewConf[$key]['attributes']['iterate']) ? $viewConf[$key]['attributes']['iterate'] : TRUE;
				$vps = array();
        $viewConf[$key]['attributes']['view-processors'] = !isset($viewConf[$key]['attributes']['view-processors']) ? NULL : $viewConf[$key]['attributes']['view-processors'];
				$vpIds = explode(' ', $viewConf[$key]['attributes']['view-processors']);
				foreach ($vpIds as $vpId) {
					if (isset($viewProcessors[$vpId])) {
						$vps[$vpId] = $viewProcessors[$vpId];
					}
				}
        $viewConf[$key]['attributes']['attrs'] = !isset($viewConf[$key]['attributes']['attrs']) ? NULL : $viewConf[$key]['attributes']['attrs'];
        $viewConf[$key]['attributes']['default'] = !isset($viewConf[$key]['attributes']['default']) ? NULL : $viewConf[$key]['attributes']['default'];
        $viewConf[$key]['attributes']['extends'] = !isset($viewConf[$key]['attributes']['extends']) ? NULL : $viewConf[$key]['attributes']['extends'];
        $viewConf[$key]['attributes']['field-name'] = !isset($viewConf[$key]['attributes']['field-name']) ? NULL : $viewConf[$key]['attributes']['field-name'];
        $viewConf[$key]['attributes']['forward-view'] = !isset($viewConf[$key]['attributes']['forward-view']) ? NULL : $viewConf[$key]['attributes']['forward-view'];
        $viewConf[$key]['attributes']['id-constraint'] = !isset($viewConf[$key]['attributes']['id-constraint']) ? NULL : $viewConf[$key]['attributes']['id-constraint'];
        $viewConf[$key]['attributes']['lookup-processor'] = !isset($viewConf[$key]['attributes']['lookup-processor']) ? NULL : $viewConf[$key]['attributes']['lookup-processor'];
        $viewConf[$key]['attributes']['lp-no-results-msg'] = !isset($viewConf[$key]['attributes']['lp-no-results-msg']) ? NULL : $viewConf[$key]['attributes']['lp-no-results-msg'];
        $viewConf[$key]['attributes']['lp-no-results-tpl'] = !isset($viewConf[$key]['attributes']['lp-no-results-tpl']) ? NULL : $viewConf[$key]['attributes']['lp-no-results-tpl'];
        $viewConf[$key]['attributes']['mime-type'] = !isset($viewConf[$key]['attributes']['mime-type']) ? NULL : $viewConf[$key]['attributes']['mime-type'];
        $viewConf[$key]['attributes']['cardinality-attr'] = !isset($viewConf[$key]['attributes']['cardinality-attr']) ? NULL : $viewConf[$key]['attributes']['cardinality-attr'];
        $viewConf[$key]['param'] = !isset($viewConf[$key]['param']) ? NULL : $viewConf[$key]['param'];
        $viewConf[$key]['attributes']['post'] = !isset($viewConf[$key]['attributes']['post']) ? NULL : $viewConf[$key]['attributes']['post'];
        $viewConf[$key]['attributes']['post-template'] = !isset($viewConf[$key]['attributes']['post-template']) ? NULL : $viewConf[$key]['attributes']['post-template'];
        $viewConf[$key]['attributes']['pre'] = !isset($viewConf[$key]['attributes']['pre']) ? NULL : $viewConf[$key]['attributes']['pre'];
        $viewConf[$key]['attributes']['pre-template'] = !isset($viewConf[$key]['attributes']['pre-template']) ? NULL : $viewConf[$key]['attributes']['pre-template'];
        $viewConf[$key]['attributes']['render-attributes'] = !isset($viewConf[$key]['attributes']['render-attributes']) ? NULL : $viewConf[$key]['attributes']['render-attributes'];
        $viewConf[$key]['attributes']['skip-attributes'] = !isset($viewConf[$key]['attributes']['skip-attributes']) ? NULL : $viewConf[$key]['attributes']['skip-attributes'];
        $viewConf[$key]['attributes']['template'] = !isset($viewConf[$key]['attributes']['template']) ? NULL : $viewConf[$key]['attributes']['template'];
        $viewConf[$key]['view-header'] = !isset($viewConf[$key]['view-header']) ? NULL : $viewConf[$key]['view-header'];
        $viewConf[$key]['view-label'] = !isset($viewConf[$key]['view-label']) ? NULL : $viewConf[$key]['view-label'];
        $viewConf[$key]['view-value'] = !isset($viewConf[$key]['view-value']) ? NULL : $viewConf[$key]['view-value'];
				$views[$key] = new SRA_EntityView($key, $viewConf[$key]['attributes']['attrs'], $viewConf[$key]['attributes']['default'], 
															$viewConf[$key]['attributes']['extends'], $viewConf[$key]['attributes']['field-name'], $viewConf[$key]['attributes']['forward-view'], 
															$viewConf[$key]['attributes']['id-constraint'], $isAttribute, $iterate, 
															$viewConf[$key]['attributes']['lookup-processor'], $viewConf[$key]['attributes']['lp-no-results-msg'], 
															$viewConf[$key]['attributes']['lp-no-results-tpl'], $viewConf[$key]['attributes']['mime-type'], 
															$viewConf[$key]['attributes']['cardinality-attr'], $viewConf[$key]['param'],
															$viewConf[$key]['attributes']['post'], $viewConf[$key]['attributes']['post-template'], 
                              $viewConf[$key]['attributes']['pre'], $viewConf[$key]['attributes']['pre-template'], 
															$viewConf[$key]['attributes']['render-attributes'], $viewConf[$key]['attributes']['skip-attributes'], 
															$viewConf[$key]['attributes']['template'], $viewConf[$key]['view-header'], 
															$viewConf[$key]['view-label'], $vps, 
															$viewConf[$key]['view-value']);
                              
				if (!SRA_EntityView::isValid($views[$key])) {
					$msg = "SRA_Generator::getViews: Failed - View '${key} specified for attribute " . $conf['attributes']['key'] . " is not valid";
					return SRA_Error::logError($msg, __FILE__, __LINE__);
				}
			}
		}
		return $views;
	}
	// }}}
	
	
	// {{{ validateResource
	/**
	 * Used to validate a 'resource' keys
	 * @param string $resource the resource key to validate. this key must exist 
	 * in the app SRA_ResourceBundle
	 * @param string $resources optional path to the resource bundle to use
   * @access  public
	 * @return String
	 */
	function validateResource($resource, $resources = FALSE) {
		$rb =& SRA_Controller::getAppResources();
		if ($resources) {
			foreach ($resources as $res) {
				$rb =& SRA_ResourceBundle::merge($rb, SRA_ResourceBundle::getBundle($res));
			}
		}
		if (strstr($resource, "{\$") || $rb->containsKey($resource)) {
			return TRUE;
		}
		return FALSE;
	}
	// }}}
	
  
  // private operations
  
}
// }}}
?>
