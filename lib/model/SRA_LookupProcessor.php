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

// {{{ SRA_LookupProcessor
/**
 * Base class for SRA_LookupProcessors as defined in the "view" "lookup-processor" 
 * documentation in the entity-model dtd. A lookup processor is a simple class 
 * with a single static method invoked when an entity view is rendered that 
 * utilizes the SRA_LookupProcessor. This method, "lookup" returns an array of 
 * entities each of which will be rendered utilizing that corresponding view. 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_LookupProcessor {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	/**
	 * the name of the Entity that this SRA_LookupProcessor pertains to
	 * @type SRA_Params
	 */
	var $_entityName;
	
	/**
	 * SRA_Params specific to the SRA_LookupProcessor
	 * @type SRA_Params
	 */
	var $_params;
  // }}}
  
  // {{{ Operations
  // SRA_LookupProcessor
	/**
	 * constructor, this method MUST be invoked from the corresponding implementation 
	 * constructor
	 * @param util/SRA_Params $params SRA_LookupProcessor implementation specific 
	 * parameters. The required/optional parameters and their corresponding 
	 * descriptions should be described in the corresponding SRA_LookupProcessor api. 
	 * These params will be all of the params specified for the view, not just 
	 * those specific to the SRA_LookupProcessor
	 * @param string $entityName the name of the entity that the SRA_LookupProcessor 
	 * should be returning. 
   * @access  public
	 */
	function SRA_LookupProcessor(& $params, $entityName) {
		$this->_params =& $params;
		$this->_entityName = $entityName;
	}
	// }}}
	
  
  // public operations  
	
	
	
	// {{{ lookup
	/**
	 * This method, returns an array of entities each of which will be rendered 
	 * utilizing the view that the SRA_LookupProcessor has been configured for. This 
	 * method is abstract and must not be called directly. Instead it should be 
	 * overwritten by the corresponding SRA_LookupProcessor subclass being used. This 
	 * method should return an array. If it returns a value other than an array, 
	 * the view will not be renedered. 
   * @access  public static
	 * @return {$entityName}[]
	 */
	function lookup() {
		// this method MUST be overriden in the SRA_LookupProcessor subclass implementation
		$msg = "SRA_LookupProcessor::lookup: SRA_Error - Cannot be invoked directly for entity '${entityName}'";
		return SRA_Error::logError($msg, __FILE__, __LINE__);
	}
	// }}}
	
	// {{{ renderHeader
	/**
	 * Renders html that should be output before any entities
   * @access  public
	 * @return String
	 */
	function renderHeader() {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
	}
	// }}}
	
	// {{{ renderFooter
	/**
	 * Renders html that should be output after all entities
   * @access  public
	 * @return String
	 */
	function renderFooter() {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
	}
	// }}}
	
	// {{{ renderEntityPostfix
	/**
	 * Renders html that should be output after each entity returned by this processor
	 * @param Object $entity a reference to the entity being rendered
   * @access  public
	 * @return String
	 */
	function renderEntityPostfix(& $entity) {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
	}
	// }}}
	
	// {{{ renderEntityPrefix
	/**
	 * Renders html that should be output before each entity returned by this processor
	 * @param Object $entity a reference to the entity being rendered
   * @access  public
	 * @return String
	 */
	function renderEntityPrefix(& $entity) {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
	}
	// }}}
	
	// {{{ renderAttributePostfix
	/**
	 * Renders html that should be output after each entity attribute
	 * @param Object $entity a reference to the entity being rendered
	 * @param mixed $attr a reference to the attribute being rendered
	 * @param string $attrName the name of the attribute being rendered
   * @access  public
	 * @return String
	 */
	function renderAttributePostfix(& $entity, & $attr, $attrName) {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
	}
	// }}}
	
	// {{{ renderAttributePrefix
	/**
	 * Renders html that should be output before each entity attribute
	 * @param Object $entity a reference to the entity being rendered
	 * @param mixed $attr a reference to the attribute being rendered
	 * @param string $attrName the name of the attribute being rendered
   * @access  public
	 * @return String
	 */
	function renderAttributePrefix(& $entity, & $attr, $attrName) {
		// this method CAN be overriden in the SRA_LookupProcessor subclass implementation
		print('');
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
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_LookupProcessor object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_lookupprocessor');
	}
	// }}}
	
  
  // private operations
  
}
// }}}
?>
