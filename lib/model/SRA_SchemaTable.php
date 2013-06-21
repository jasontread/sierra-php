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
require_once('SRA_SchemaColumn.php');
require_once('SRA_SchemaIndex.php');
// }}}

// {{{ SRA_SchemaTable
/**
 * represents a single table in a schema. a table contains one or more columns
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_SchemaTable {
  // {{{ Attributes
  // public attributes
	
  // private attributes
	
  /**
	 * the columns that make up this table
	 * @type SRA_SchemaColumn[]
	 */
	var $_columns = array();
	
  /**
	 * an UPDATE query SET constraint to use in place of a deleting records in 
	 * this table (i.e. "enabled=1")
	 * @type string
	 */
	var $_deleteConstraint;
	
  /**
	 * indexes that are used by this table
	 * @type SRA_SchemaIndex[]
	 */
	var $_indexes = array();
	
	/**
	 * optional entity specific mysql storage engine to use (override the default)
	 * @type string
	 */
	var $_mysqlTableType;
	
  /**
	 * the name of the table
	 * @type string
	 */
	var $_name;
	
  /**
	 * a default ORDER BY sql constraint to apply in SELECT queries
	 * @type string
	 */
	var $_orderConstraint;
	
  /**
	 * whether or not this table is the primary table for the SRA_EntitySchema  it 
	 * pertains to
	 * @type boolean
	 */
	var $_primary = FALSE;
	
  /**
	 * the name or names of the columns that constitute the unique index for 
	 * records in this table
	 * @type mixed
	 */
	var $_primaryKey;
  
  /**
	 * used during the dao creation to determine whether or not a table has 
   * already been included in dao process
	 * @type boolean
	 */
	var $_processed = FALSE;
	
  /**
	 * an optional constraint that should be applied to all SELECT queries
	 * @type string
	 */
	var $_selectConstraint;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_SchemaTable
	/**
	 * Constructor
   * @access public
	 */
	function SRA_SchemaTable($name) {
		$this->setName($name);
	}
	// }}}
	
  
  // public operations
	// {{{ addColumn
	/**
	 * adds a column to the table
	 * @param EntityColumn $column the column to add. if this is an array, each 
	 * column will be added
	 * @param boolean $overwrite whether or not this columns' properties should 
	 * overwrite any existing column properties (if this column has already been 
	 * added to the table)
   * @access public
	 * @return void
	 */
	function addColumn(& $column, $overwrite = FALSE) {
		if (is_array($column)) {
			$keys = array_keys($column);
			foreach($keys as $key) {
				$this->addColumn($column[$key], $overwrite);
			}
		}
		else {
			if (SRA_SchemaColumn::isValid($column)) {
				// column doesn't exist, just add it
				if (!isset($this->_columns[$column->getName()])) {
					$this->_columns[$column->getName()] =& $column;
				}
				// column already exists, add attribute to existing column
				else {
					$cascadeOriginal = $this->_columns[$column->getName()]->isOnDeleteCascade();
					$cascadeNew = $column->isOnDeleteCascade();
					if ($overwrite) {
						$tmp = $this->_columns[$column->getName()];
						$this->_columns[$column->getName()] =& $column;
						$this->_columns[$column->getName()]->addAttribute($tmp->getAttributes());
						$this->_columns[$column->getName()]->setReadConstraints(array_merge($tmp->getReadConstraints(), $this->_columns[$column->getName()]->getReadConstraints()));
					}
					else {
						$this->_columns[$column->getName()]->addAttribute($column->getAttributes());
						$this->_columns[$column->getName()]->setReadConstraints(array_merge($this->_columns[$column->getName()]->getReadConstraints(), $column->getReadConstraints()));
					}
					if ($cascadeOriginal || $cascadeNew) {
						$this->_columns[$column->getName()]->setOnDeleteCascade(TRUE);
					}
				}
			}
		}
	}
	// }}}
	
	// {{{ addIndex
	/**
	 * adds a index to the table
	 * @param EntityIndex $index the index to add. if this is an array, each 
	 * index will be added
   * @access public
	 * @return void
	 */
	function addIndex(& $index) {
		if (is_array($index)) {
			$keys = array_keys($index);
			foreach($keys as $key) {
				$this->addIndex($index[$key]);
			}
		}
		else {
			if (SRA_SchemaIndex::isValid($index)) {
				$this->_indexes[$index->getName()] =& $index;
			}
		}
	}
	// }}}
	
	// {{{ addPrimaryKey
	/**
	 * adds a new primary key to the table
	 * @param string $primaryKey the name of the column to add as a primary key
   * @access public
	 * @return void
	 */
	function addPrimaryKey($primaryKey) {
		if (is_array($primaryKey)) {
			$keys = array_keys($primaryKey);
			foreach($keys as $key) {
				$this->addPrimaryKey($primaryKey[$key]);
			}
		}
		else {
			if (is_array($this->_primaryKey)) {
				if (!in_array($primaryKey, $this->_primaryKey)) {
					$this->_primaryKey[] = $primaryKey;
				}
			}
			else if (isset($this->_primaryKey) && $this->_primaryKey != $primaryKey) {
				$this->_primaryKey = array($this->_primaryKey, $primaryKey);
			}
			else {
				$this->_primaryKey = $primaryKey;
			}
		}
	}
	// }}}
	
	// {{{ getAttributeColumn
	/**
	 * returns the SRA_SchemaColumn corresponding to a specific attribute
	 * @param string $attr the name of the attribute to return the column for. if 
	 * not specified, the column for the primary key attribute will be returned
   * @access public
	 * @return SRA_SchemaColumn
	 */
	function & getAttributeColumn($attr=FALSE) {
		if (!$attr) {
			return $this->_columns[$this->getPrimaryKey()];
		}
		$keys = array_keys($this->_columns);
		foreach ($keys as $key) {
			if ($this->_columns[$key]->hasAttribute($attr)) {
				return $this->_columns[$key];
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getAttributeGroups
	/**
	 * returns all of the attribute groups belonging to this table. each group 
	 * is a set of attributes that constitute a record in this table
   * @access public
	 * @return array
	 */
	function getAttributeGroups() {
		$groups = array();
		$keys = array_keys($this->_columns);
		foreach ($keys as $key) {
      if (is_object($this->_columns[$key])) {
        $attributes = $this->_columns[$key]->getAttributes();
        foreach ($attributes as $attribute) {
          $id = $this->_columns[$key]->getReadConstraints($attribute) ? $this->_columns[$key]->getReadConstraints($attribute) : 0;
          if (!isset($groups[$id])) {
            $groups[$id] = array();
          }
          $pieces = explode('_', $attribute);
          $groups[$id][$attribute] = $pieces[0];
        }
      }
      else {
        unset($this->_columns[$key]);
      }
		}
		$primaryKeyColumn = $this->getAttributeColumn();
		$keys = array_keys($groups);
		foreach ($keys as $key) {
			if (!count($groups[$key]) || (count($groups[$key]) == 1 && $primaryKeyColumn && isset($groups[$key][$primaryKeyColumn->getBaseAttribute()]))) {
				unset($groups[$key]);
			}
		}
		return $groups;
	}
	// }}}
	
	// {{{ getColumns
	/**
	 * returns a reference to $_columns
	 * @param string $name if specified, only a reference to the column specified 
	 * will be returned. if $name is not a valid column in the schema, FALSE will 
	 * be returned
   * @access public
	 * @return SRA_SchemaColumn[]
	 */
	function & getColumns($name = FALSE) {
    foreach(array_keys($this->_columns) as $key) {
      if (!SRA_SchemaColumn::isValid($this->_columns[$key])) {
        unset($this->_columns[$key]);
      }
    }
    
		if ($name && isset($this->_columns[$name])) {
			return $this->_columns[$name];
		}
		else if (!$name) {
			return $this->_columns;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getDeleteConstraint
	/**
	 * returns the deleteConstraint of this table
   * @access public
	 * @return string
	 */
	function getDeleteConstraint() {
		return $this->_deleteConstraint;
	}
	// }}}
	
	// {{{ getIndexes
	/**
	 * returns a reference to $_indexes
	 * @param string $name if specified, only a reference to the index specified 
	 * will be returned. if $name is not a valid index in the schema, FALSE will 
	 * be returned
   * @access public
	 * @return SRA_SchemaIndex[]
	 */
	function & getIndexes($name = FALSE) {
		if ($name && isset($this->_indexes[$name])) {
			return $this->_indexes[$name];
		}
		else if (!$name) {
			return $this->_indexes;
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getMysqlTableType
	/**
	 * returns the mysql storage engine for this table (if a custom entity specific 
	 * storage engine has been specified)
   * @access public
	 * @return string
	 */
	function getMysqlTableType() {
		return $this->_mysqlTableType;
	}
	// }}}
	
	// {{{ getName
	/**
	 * returns the name of this table
   * @access public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}
	// }}}
	
	// {{{ getOrderConstraint
	/**
	 * returns the order constraint (if any) for this table
   * @access public
	 * @return string
	 */
	function getOrderConstraint() {
		return $this->_orderConstraint;
	}
	// }}}
	
	// {{{ getPrimaryKey
	/**
	 * returns the primary key for this table
   * @access public
	 * @return string
	 */
	function getPrimaryKey() {
		return $this->_primaryKey;
	}
	// }}}
	
	// {{{ getSelectConstraint
	/**
	 * returns the selectConstraint of this table
   * @access public
	 * @return string
	 */
	function getSelectConstraint() {
		return $this->_selectConstraint;
	}
	// }}}
	
	// {{{ isPrimary
	/**
	 * returns the _primary flag for this table
   * @access public
	 * @return boolean
	 */
	function isPrimary() {
		return $this->_primary;
	}
	// }}}
	
	// {{{ isPrimaryKey
	/**
	 * returns TRUE if the $column specified is one of this table's primary keys
	 * @param string $column the name of the column to check
   * @access public
	 * @return boolean
	 */
	function isPrimaryKey($column) {
		return (!is_array($this->_primaryKey) && $column == $this->_primaryKey) || (is_array($this->_primaryKey) && in_array($column, $this->_primaryKey));
	}
	// }}}
  
	// {{{ isProcessed
	/**
	 * returns the _processed flag for this table
   * @access public
	 * @return boolean
	 */
	function isProcessed() {
		return $this->_processed;
	}
	// }}}
	
	// {{{ primaryKeyIsSequence
	/**
	 * returns TRUE if this table contains only 1 primary key and that column is a 
	 * sequence
   * @access public
	 * @return boolean
	 */
	function primaryKeyIsSequence() {
		return !is_array($this->_primaryKey) && $this->_columns[$this->_primaryKey]->isSequence();
	}
	// }}}
	
	// {{{ setColumns
	/**
	 * sets the columns of the schema
	 * @param EntityColumn[] $columns the columns to set
   * @access public
	 * @return void
	 */
	function setColumns(& $columns) {
		if (is_array($columns)) {
			$keys = array_keys($columns);
			foreach ($keys as $key) {
				$this->addColumn($columns[$key]);
			}
		}
	}
	// }}}
	
	// {{{ setDeleteConstraint
	/**
	 * sets the table deleteConstraint
	 * @param string $deleteConstraint the deleteConstraint to set
   * @access public
	 * @return void
	 */
	function setDeleteConstraint($deleteConstraint) {
		$this->_deleteConstraint = $deleteConstraint;
	}
	// }}}
	
	// {{{ setIndexes
	/**
	 * sets the indexs of the schema
	 * @param EntityIndex[] $indexs the indexs to set
   * @access public
	 * @return void
	 */
	function setIndexes(& $indexs) {
		if (is_array($indexs)) {
			$keys = array_keys($indexs);
			foreach ($keys as $key) {
				$this->addIndex($indexs[$key]);
			}
		}
	}
	// }}}
	
	// {{{ setMysqlTableType
	/**
	 * sets the mysql table type
	 * @param string $mysqlTableType the mysqlTableType to set
   * @access public
	 * @return void
	 */
	function setMysqlTableType($mysqlTableType) {
		$this->_mysqlTableType = $mysqlTableType;
	}
	// }}}
	
	// {{{ setName
	/**
	 * sets the table name
	 * @param string $name the name to set
   * @access public
	 * @return void
	 */
	function setName($name) {
		$this->_name = $name;
	}
	// }}}
	
	// {{{ setOrderConstraint
	/**
	 * sets the table order constraint
	 * @param mixed $orderConstraint the order constraint to set
   * @access public
	 * @return void
	 */
	function setOrderConstraint($orderConstraint) {
		$this->_orderConstraint = $orderConstraint;
	}
	// }}}
	
	// {{{ setPrimary
	/**
	 * sets the table _primary flag
	 * @param mixed $primary the flag to set
   * @access public
	 * @return void
	 */
	function setPrimary($primary) {
		$this->_primary = $primary;
	}
	// }}}
	
	// {{{ setPrimaryKey
	/**
	 * sets the table primary key
	 * @param mixed $primaryKey the primary keys to set. this may be a single 
	 * value or an array of multiple column references if the primary key is not 
	 * tied to a single column
   * @access public
	 * @return void
	 */
	function setPrimaryKey($primaryKey) {
		$this->_primaryKey = $primaryKey;
	}
	// }}}
  
	// {{{ setProcessed
	/**
	 * sets the table _processed flag
	 * @param mixed $processed the flag to set
   * @access public
	 * @return void
	 */
	function setProcessed($processed) {
		$this->_processed = $processed;
	}
	// }}}
	
	// {{{ setSelectConstraint
	/**
	 * sets the table selectConstraint
	 * @param string $selectConstraint the selectConstraint to set
   * @access public
	 * @return void
	 */
	function setSelectConstraint($selectConstraint) {
		$this->_selectConstraint = $selectConstraint;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_SchemaTable object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_schematable');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
