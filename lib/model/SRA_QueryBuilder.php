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
require_once('SRA_QueryBuilderConstraintGroup.php');
// }}}

// {{{ Constants
/**
 * defines ascending sort order
 */
define('SRA_QUERY_BUILDER_SORT_ASC', 'asc');

/**
 * defines descending sort order
 */
define('SRA_QUERY_BUILDER_SORT_DESC', 'desc');
// }}}

// {{{ SRA_QueryBuilder
/**
 * 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_QueryBuilder {
  // {{{ Attributes
  // public attributes
	
  // private attributes
	
  /**
	 * the groups of constraints to apply. each non-sorting constraint group will 
	 * be joined using AND
	 * @type SRA_QueryBuilderConstraintGroup[]
	 */
	var $_constraintGroups;
	
  /**
	 * the name of the entity that the query is being constructed for
	 * @type string
	 */
	var $_entityName;
	
  /**
	 * entities returned by this query builder
	 * @type Entity[]
	 */
	var $_entities;
	
	/**
	 * the query limit. 0 == no limit
	 * @type int
	 */
	var $_limit = FALSE;
	
	/**
	 * the query offset. 0 == no offset
	 * @type int
	 */
	var $_offset = FALSE;
	
	/**
	 * the total result count (without considering limit and offsets)
	 * @type int
	 */
	var $_resultCount;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_QueryBuilder
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_QueryBuilder($entityName, & $constraintGroups, $limit = 0, $offset = 0) {
		$this->_entityName = $entityName;
		$this->_constraintGroups =& $constraintGroups;
		if ($limit > 0) {
			$this->_limit = $limit;
		}
		if ($offset > 0) {
			$this->_offset = $offset;
		}
	}
	// }}}
	
  
  // public operations
	// {{{ getEntities
	/**
	 * this method returns all of the matching entities of type $entityName 
	 * according to the constraints, limit and offset parameters specified when 
	 * the query builder was constructed
   * @access  public
	 * @return Entity[]
	 */
	function & getEntities() {
		if (!isset($this->_entities)) {
			$dao =& SRA_DaoFactory::getDao($this->_entityName);
			$query = $this->getQuery();
			// echo "QUERY: $query $this->_limit / $this->_offset\n";
			$this->_entities =& $dao->findByQuery($query, $this->_limit, $this->_offset, strstr(strtolower($query), 'left join'));
			$this->_resultCount = $dao->totalResultCount ? $dao->totalResultCount : 0;
		}
		return $this->_entities;
	}
	// }}}
	
	// {{{ getLimit
	/**
	 * returns the #_limit
   * @access  public
	 * @return int
	 */
	function getLimit() {
		return $this->_limit;
	}
	// }}}
	
	// {{{ getOffset
	/**
	 * returns the #_offset
   * @access  public
	 * @return int
	 */
	function getOffset() {
		return $this->_offset;
	}
	// }}}
	
	// {{{ getQuery
	/**
	 * returns the sql query for the constraints and entity specified, minus 
	 * limits and offsets because implementing those in sql is database specific
   * @access  public
	 * @return string
	 */
	function getQuery() {
		$dao =& SRA_DaoFactory::getDao($this->_entityName);
		$daos =& $dao->getDaos();
		$pkColumn = $dao->getColumnName();
		$entityTable = $dao->getTableName();
		$query = 'SELECT ' . $entityTable . '.' . $pkColumn . ' FROM ' . $entityTable;
		$externalTables = array();
		$keys = array_keys($this->_constraintGroups);
		foreach ($keys as $key) {
			$ckeys = array_keys($this->_constraintGroups[$key]->constraints);
			foreach ($ckeys as $ckey) {
				$aTable = FALSE;
				if (is_array($tattrs = $dao->getTableName($this->_constraintGroups[$key]->constraints[$ckey]->attribute))) {
					$externalTables[$tattrs['table']] = $tattrs['pkColumn'];
					$aTable = $tattrs['table'];
				}
				if (isset($daos[$this->_constraintGroups[$key]->constraints[$ckey]->attribute]) && $aTable && $aTable != ($eTable = $daos[$this->_constraintGroups[$key]->constraints[$ckey]->attribute]->getTableName())) {
					$aTable = !$aTable ? $eTable : $aTable;
					$externalTables[$eTable] = array($aTable, $daos[$this->_constraintGroups[$key]->constraints[$ckey]->attribute]->getColumnName());
				}
			}
		}
		foreach ($externalTables as $table => $fpkCol) {
			if (is_array($fpkCol)) {
				$query .= " LEFT JOIN ${table} ON $fpkCol[0].$fpkCol[1]=${table}.$fpkCol[1]";
			}
			else {
				$query .= " LEFT JOIN ${table} ON ${entityTable}.${pkColumn}=${table}.${fpkCol}";
			}
		}
		$whereStarted = FALSE;
		foreach ($keys as $key) {
			if ($this->_constraintGroups[$key]->hasNonSortingConstraint()) {
				$ckeys = array_keys($this->_constraintGroups[$key]->constraints);
				if ($whereStarted) {
					$query .= ' AND (';
				}
				else {
					$query .= ' WHERE (';
					$whereStarted = TRUE;
				}
				$started = FALSE;
				foreach ($ckeys as $ckey) {
					if (!$this->_constraintGroups[$key]->constraints[$ckey]->isSortingConstraint()) {
						if ($started) {
							if ($this->_constraintGroups[$key]->joinMethod == SRA_QUERY_BUILDER_CONSTRAINT_GROUP_JOIN_METHOD_AND) {
								$query .= ' AND ';
							}
							else {
								$query .= ' OR ';
							}
						}
						$tableName = $entityTable;
						if (is_array($tattrs = $dao->getTableName($this->_constraintGroups[$key]->constraints[$ckey]->attribute))) {
							$tableName = $tattrs['table'];
						}
						$vkeys = $this->_constraintGroups[$key]->constraints[$ckey]->getKeys();
						$vstarted = FALSE;
						$query .= '(';
						foreach ($vkeys as $vkey) {
							if ($vstarted) {
								$query .= ' OR ';
							}
							$attributeName = $this->_constraintGroups[$key]->constraints[$ckey]->attribute;
							$columnName = $dao->getColumnName($attributeName);
							// don't apply limit or offset if primary key is involved in query
							if ($columnName == $dao->getColumnName()) {
								$this->_limit = FALSE;
								$this->_offset = FALSE;
							}
							$query .= $tableName . '.' . $columnName . 
												$this->_constraintGroups[$key]->constraints[$ckey]->getSqlOperator($vkey) . 
												$this->_constraintGroups[$key]->constraints[$ckey]->getSqlValue($dao, $vkey);
							$vstarted = TRUE;
						}
						$query .= ')';
						$started = TRUE;
					}
				}
				$query .= ' )';
			}
		}
		foreach ($keys as $key) {
			$ckeys = array_keys($this->_constraintGroups[$key]->constraints);
			$started = FALSE;
			foreach ($ckeys as $ckey) {
				$tableName = $entityTable;
				if (is_array($tattrs = $dao->getTableName($this->_constraintGroups[$key]->constraints[$ckey]->attribute))) {
					$tableName = $tattrs['table'];
				}
				if ($this->_constraintGroups[$key]->constraints[$ckey]->isSortingConstraint() && ($sortColumns = $dao->getSortColumnName($this->_constraintGroups[$key]->constraints[$ckey]->attribute, $tableName))) {
					if (!$started) {
						$query .= ' ORDER BY';
					}
					else {
						$query .= ', ';
					}
					$query .= ' ' . $sortColumns;
					$query .= ($this->_constraintGroups[$key]->constraints[$ckey]->type & SRA_QUERY_BUILDER_CONSTRAINT_TYPE_SORT_DESC) ? ' DESC' : ' ASC';
					$started = TRUE;
				}
			}
		}
		return $query;
	}
	// }}}

	
	// {{{ getResultCount
	/**
	 * returns the #_resultCount
   * @access  public
	 * @return int
	 */
	function getResultCount() {
		$this->getEntities();
		return $this->_resultCount;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_QueryBuilder object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_querybuilder');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
