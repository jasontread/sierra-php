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
require_once('SRA_SchemaTable.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_EntitySchema 
/**
 * Used to represent the schema associated with an entity. This is the structure 
 * of tables and columns that constitute the entity's persistability
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_EntitySchema  {
  // {{{ Attributes
  // public attributes
	
  // private attributes
	/**
   * whether or not the database is handing referential integrity
	 */
	 
	var $_dbRefIntegrity;
	
  /**
	 * DDL camel case flag
	 * @type boolean
	 */
	var $_ddlCamelCase;
	
  /**
	 * DDL upper case flag
	 * @type boolean
	 */
	var $_ddlUpperCase;
	
  /**
	 * DTD camel case flag
	 * @type boolean
	 */
	var $_dtdCamelCase;
	
	/**
	 * the name of the entity that this schema pertains to
	 * @type string
	 */
	var $_name;
	
  /**
	 * a schema consist of 1 or more tables each of which contains columns 
	 * referencing scalar attributes
	 * @type SRA_SchemaTable[]
	 */
	var $_tables = array();
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntitySchema 
	/**
	 * Constructor
	 * @param string $name the name of the entity that this schema pertains to
   * @access  public
	 */
	function SRA_EntitySchema ($name) {
		$this->setName($name);
	}
	// }}}
	
  
  // public operations
	// {{{ addTable
	/**
	 * adds a table to the schema
	 * @param EntityTable $table the table to add
   * @access  public
	 * @return void
	 */
	function addTable(& $table) {
		if (is_array($table)) {
			$keys = array_keys($table);
			foreach($keys as $key) {
				$this->addTable($table[$key]);
			}
		}
		else {
			if (SRA_SchemaTable::isValid($table)) {
				$this->_tables[$table->getName()] =& $table;
			}
		}
	}
	// }}}
	
	// {{{ getName
	/**
	 * returns the name of this entity this schema pertains to
   * @access  public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}
	// }}}
	
	// {{{ getPrimaryTable
	/**
	 * returns a reference to the primary table for this entity schema
   * @access  public
	 * @return SRA_SchemaTable
	 */
	function & getPrimaryTable() {
		$keys = array_keys($this->_tables);
		foreach ($keys as $key) {
			if ($this->_tables[$key]->isPrimary()) {
				return $this->_tables[$key];
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getTables
	/**
	 * returns a reference to $_tables
	 * @param string $name if specified, only a reference to the table specified 
	 * will be returned. if $name is not a valid table in the schema, FALSE will 
	 * be returned
   * @access  public
	 * @return SRA_SchemaTable[]
	 */
	function & getTables($name = FALSE) {
		if ($name && isset($this->_tables[$name])) {
			return $this->_tables[$name];
		}
		else if (!$name) {
			return $this->_tables;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ isDbRefIntegrity
	/**
	 * returns the _dbRefIntegrity flag
   * @access  public
	 * @return boolean
	 */
	function isDbRefIntegrity() {
		return $this->_dbRefIntegrity;
	}
	// }}}
	
	// {{{ isDdlCamelCase
	/**
	 * returns the _ddlCamelCase flag
   * @access  public
	 * @return boolean
	 */
	function isDdlCamelCase() {
		return $this->_ddlCamelCase;
	}
	// }}}
	
	// {{{ isDdlUpperCase
	/**
	 * returns the _ddlUpperCase flag
   * @access  public
	 * @return boolean
	 */
	function isDdlUpperCase() {
		return $this->_ddlUpperCase;
	}
	// }}}
	
	// {{{ isDtdCamelCase
	/**
	 * returns the _dtdCamelCase flag
   * @access  public
	 * @return boolean
	 */
	function isDtdCamelCase() {
		return $this->_dtdCamelCase;
	}
	// }}}
  
	// {{{ isProcessed
	/**
	 * passthru to Table::isProcessed for the $table specified
	 * @param string $table the name of the table
   * @access  public
	 * @return void
	 */
	function isProcessed($table) {
    $keys = array_keys($this->_tables);
    foreach ($keys as $key) {
      if ($this->_tables[$key]->getName() == $table) {
        return $this->_tables[$key]->isProcessed();
      }
    }
	}
	// }}}
	
	// {{{ setDbRefIntegrity
	/**
	 * sets the schema dbRefIntegrity flag
	 * @param string $dbRefIntegrity the dbRefIntegrity to set
   * @access  public
	 * @return void
	 */
	function setDbRefIntegrity($dbRefIntegrity) {
		$this->_dbRefIntegrity = $dbRefIntegrity;
	}
	// }}}
	
	// {{{ setDdlCamelCase
	/**
	 * sets the schema ddlCamelCase flag
	 * @param string $ddlCamelCase the ddlCamelCase to set
   * @access  public
	 * @return void
	 */
	function setDdlCamelCase($ddlCamelCase) {
		$this->_ddlCamelCase = $ddlCamelCase;
	}
	// }}}
	
	// {{{ setDdlUpperCase
	/**
	 * sets the schema ddlUpperCase flag
	 * @param string $ddlUpperCase the ddlUpperCase to set
   * @access  public
	 * @return void
	 */
	function setDdlUpperCase($ddlUpperCase) {
		$this->_ddlUpperCase = $ddlUpperCase;
	}
	// }}}
	
	// {{{ setDtdCamelCase
	/**
	 * sets the schema dtdCamelCase flag
	 * @param string $dtdCamelCase the dtdCamelCase to set
   * @access  public
	 * @return void
	 */
	function setDtdCamelCase($dtdCamelCase) {
		$this->_dtdCamelCase = $dtdCamelCase;
	}
	// }}}
	
	// {{{ setName
	/**
	 * sets the name of the entity that this schema pertains to
	 * @param string $name the name to set
   * @access  public
	 * @return void
	 */
	function setName($name) {
		$this->_name = $name;
	}
	// }}}
  
	// {{{ setProcessed
	/**
	 * passthru to Table::setProcessed for the $table specified
	 * @param string $table the name of the table to set processed
   * @access  public
	 * @return void
	 */
	function setProcessed($table, $val=TRUE) {
    $keys = array_keys($this->_tables);
    foreach ($keys as $key) {
      if ($this->_tables[$key]->getName() == $table) {
        $this->_tables[$key]->setProcessed($val);
      }
    }
	}
	// }}}
	
	// {{{ setTables
	/**
	 * sets the tables of the schema
	 * @param EntityTable[] $tables the tables to set
   * @access  public
	 * @return void
	 */
	function setTables(& $tables) {
		if (is_array($tables)) {
			$keys = array_keys($tables);
			foreach ($keys as $key) {
				$this->addTable($tables[$key]);
			}
		}
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_EntitySchema  object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_entityschema');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
