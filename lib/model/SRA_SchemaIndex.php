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

// {{{ SRA_SchemaIndex
/**
 * represents a single index in a schema table
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_SchemaIndex {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the columns definition for this index
	 * @type string
	 */
	var $_columns;
	
  /**
	 * an index modifier (for mysql: [UNIQUE|FULLTEXT|SPATIAL])
	 * @type string
	 */
	var $_modifier = '';
	
  /**
	 * the unique name of the index
	 * @type string
	 */
	var $_name;
	
  /**
	 * any additional sql to append to the CREATE INDEX statement
	 * @type string
	 */
	var $_postfix = '';
	
  /**
	 * the table that this index pertains to
	 * @type string
	 */
	var $_table;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_SchemaIndex
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_SchemaIndex($columns, $name, $table) {
		$this->setColumns($columns);
		$this->setName($name);
		$this->setTable($table);
	}
	// }}}
	
  
  // public operations
	
	// {{{ getColumns
	/**
	 * returns the columns associated with this index
   * @access  public
	 * @return string
	 */
	function getColumns() {
		return $this->_columns;
	}
	// }}}
	
	// {{{ getModifier
	/**
	 * returns the modifier of this index
   * @access  public
	 * @return string
	 */
	function getModifier() {
		return $this->_modifier;
	}
	// }}}
	
	// {{{ getName
	/**
	 * returns the name of this index
   * @access  public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}
	// }}}
	
	// {{{ getPostfix
	/**
	 * returns the postfix for this index
   * @access  public
	 * @return string
	 */
	function getPostfix() {
		return $this->_postfix;
	}
	// }}}
	
	// {{{ getTable
	/**
	 * returns the table for this index
   * @access  public
	 * @return string
	 */
	function getTable() {
		return $this->_table;
	}
	// }}}
	
	// {{{ setColumns
	/**
	 * sets the columns of the index
	 * @param string $columns the columns to set
   * @access  public
	 * @return void
	 */
	function setColumns($columns) {
		$this->_columns = $columns;
	}
	// }}}
	
	// {{{ setModifier
	/**
	 * sets the index modifier
	 * @param string $modifier the modifier to set
   * @access  public
	 * @return void
	 */
	function setModifier($modifier) {
		$this->_modifier = $modifier;
	}
	// }}}
	
	// {{{ setName
	/**
	 * sets the index name
	 * @param string $name the name to set
   * @access  public
	 * @return void
	 */
	function setName($name) {
		$this->_name = $name;
	}
	// }}}
	
	// {{{ setPostfix
	/**
	 * sets the index postfix
	 * @param string $postfix the postfix to set
   * @access  public
	 * @return void
	 */
	function setPostfix($postfix) {
		$this->_postfix = $postfix;
	}
	// }}}
	
	// {{{ setTable
	/**
	 * sets the index table
	 * @param string $table the table to set
   * @access  public
	 * @return void
	 */
	function setTable($table) {
		$this->_table = $table;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_SchemaIndex object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_schemaindex');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
