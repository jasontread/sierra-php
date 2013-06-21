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

// {{{ SRA_AttrMapping
/**
 * data associated with a column mapping
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_AttrMapping {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the name of the attribute
	 * @type string
	 */
	var $_attribute;
	
  /**
	 * the name of the column
	 * @type string
	 */
	var $_column;
	
	/**
	 * identifier for an overriding resource
	 * @type string
	 */
	var $_resource;
	
	/**
	 * identifier for an overriding help resource
	 * @type string
	 */
	var $_resourceHelp;
	
	/**
	 * postfix for the resource strings
	 * @type string
	 */
	var $_resourcePostfix;
	
	/**
	 * prefix for the resource strings
	 * @type string
	 */
	var $_resourcePrefix;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AttrMapping
	/**
	 * Constructor - does nothing
   * @access  public
	 */
	function SRA_AttrMapping($attribute, $column, $resource, $resourceHelp, $resourcePostfix, $resourcePrefix) {
		$this->_attribute = $attribute;
		$this->_column = $column;
		$this->_resource = $resource;
		$this->_resourceHelp = $resourceHelp;
		$this->_resourcePostfix = $resourcePostfix;
		$this->_resourcePrefix = $resourcePrefix;
	}
	// }}}
	
  
  // public operations
	
	// {{{ getMethodName
	/**
	 * Returns the name of this attribute formatted for a method (first letter is 
	 * uppercase)
   * @access  public
	 * @return String
	 */
	function getMethodName() {
		return strtoupper(substr($this->_attribute, 0, 1)) . substr($this->_attribute, 1, strlen($this->_attribute) - 1);
	}
	// }}}
	
	// {{{ toAttributeGenerator
	/**
	 * Converts this SRA_AttrMapping into an actual SRA_AttributeGenerator
	 * @param SRA_AttributeGenerator $attributeGenerator the SRA_AttributeGenerator that 
	 * this mapping was defined in
	 * @param SRA_EntityGenerator[] $entityGenerators All of the SRA_EntityGenerator 
	 * objects in the entity model
   * @access  public
	 */
	function & toAttributeGenerator(& $attributeGenerator, & $entityGenerators) {
		// find SRA_EntityGenerator
		if (!array_key_exists($attributeGenerator->_type, $entityGenerators)) {
			$msg = "SRA_AttrMapping::toAttributeGenerator: Attribute type '$attributeGenerator->_type' is not valid for '$attributeGenerator->_name'";
			return SRA_Error::logError($msg, __FILE__, __LINE__);
		}
		$entity =& $entityGenerators[$attributeGenerator->_type];
		
		// find SRA_AttributeGenerator
		if (!isset($entity->_attributeGenerators[$this->_attribute])) {
			$attribute = FALSE;
			$pieces = explode('_', $this->_attribute);
			foreach($pieces as $piece) {
				if (!isset($entity->_attributeGenerators[$piece])) {
					$attribute = FALSE;
					break;
				}
				$attribute =& $entity->_attributeGenerators[$piece];
				if ($attribute->isEntity()) {
					if (isset($entityGenerators[$attribute->_type])) {
						$entity =& $entityGenerators[$attribute->_type];
					}
					else {
						$attribute = FALSE;
						break;
					}
				}
			}
			if (!$attribute) {
				$msg = "SRA_AttrMapping::toAttributeGenerator: attribute '$this->_attribute' is not valid for '$attributeGenerator->_name'";
				return SRA_Error::logError($msg, __FILE__, __LINE__);
			}
			$attribute = SRA_Util::copyObject($attribute);
			$attribute->_name = $this->_attribute;
      sra_error::logerror('not set');
		}
		else {
			$attribute = SRA_Util::copyObject($entity->_attributeGenerators[$this->_attribute]);
			$attribute->_name = $attributeGenerator->_name . '_' . $attribute->_baseName;
		}
		$attribute->_column = $this->_column;
		if ($this->_resource) {
			$attribute->_resource = $this->_resource;
		}
		if ($this->_resourceHelp) {
			$attribute->_resourceHelp = $this->_resourceHelp;
		}
		$attribute->_resource = $this->_resourcePrefix ? $this->_resourcePrefix . $attribute->_resource : $attribute->_resource;
		$attribute->_resource = $this->_resourcePostfix ? $attribute->_resource . $this->_resourcePostfix : $attribute->_resource;
		$attribute->_resourceHelp = $this->_resourcePrefix ? $this->_resourcePrefix . $attribute->_resourceHelp : $attribute->_resourceHelp;
		$attribute->_resourceHelp = $this->_resourcePostfix ? $attribute->_resourceHelp . $this->_resourcePostfix : $attribute->_resourceHelp;
		$attribute->_attrMapping =& $this;
		
		return $attribute;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_AttrMapping object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_attrmapping');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
