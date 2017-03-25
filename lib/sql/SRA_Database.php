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

// {{{ Constants
/**
 * Constant that identifies the default date format that should be used
 * when inserting into date type columns. Child SRA_Database* objects may or
 * may not use this same format. If they do not, they must have and use their
 * own constant identifying this value.
 * @type   string
 * @access public
 */
define('SRA_DB_DATE_FORMAT', 'Y-m-d');

/**
 * Constant that identifies the default date/time format that should be used
 * when inserting into timestamp type columns. Child SRA_Database* objects may or
 * may not use this same format. If they do not, they must have and use their
 * own constant identifying this value.
 * @type   string
 * @access public
 */
define('SRA_DB_TIME_FORMAT', 'Y-m-d H:i:s');

/**
 * Identifies (for internal SRA_Database class use only) that an database
 * server's connection status is currently closed.
 * @type   int
 * @access private
 */
define('_SRA_DB_CONNECTION_CLOSED', 1);

/**
 * Identifies (for internal SRA_Database class use only) that a database server
 * is currently in an error status (cannot be used).
 * @type   int
 * @access private
 */
define('_SRA_DB_CONNECTION_ERROR', 2);

/**
 * Identifies (for internal SRA_Database class use only) that an database
 * server's connection status is currently open.
 * @type   int
 * @access private
 */
define('_SRA_DB_CONNECTION_OPEN', 4);

/**
 * the default db host
 * @type string
 */
define('SRA_DB_DEFAULT_HOST', '127.0.0.1');

/**
 * Constant that identifies database objects of type mssql.
 * @type   string
 * @access public
 */
define('SRA_DB_TYPE_MSSQL', 'mssql');

/**
 * Constant that identifies database objects of type mysql.
 * @type   string
 * @access public
 */
define('SRA_DB_TYPE_MYSQL', 'mysql');

/**
 * Constant that identifies database objects of type postgresql.
 * @type   string
 * @access public
 */
define('SRA_DB_TYPE_POSTGRESQL', 'pgsql');

/**
 * Constant that identifies database objects of type sqlite.
 * @type   string
 * @access public
 */
define('SRA_DB_TYPE_SQLITE', 'sqlite');

/**
 * Constant that identifies database objects of type oracle.
 * @type   string
 * @access public
 */
define('SRA_DB_TYPE_ORACLE', 'oracle');

/**
 * the default values for boolean FALSE data types (the first value is the value 
 * that will be inserted into columns of that type. for more information, see 
 * the "db" element "boolean-false" and "boolean-true" attribute definitions in 
 * sierra-config*.dtd
 * @type   string
 * @access public
 */
define('SRA_DB_BOOL_FALSE_VALS', "'0' 0");

/**
 * the default PHP representation for boolean FALSE values
 * @type   mixed
 * @access public
 */
define('SRA_DB_BOOL_FALSE', FALSE);

/**
 * same as SRA_DB_BOOL_FALSE_VALS for TRUE
 * @type   string
 * @access public
 */
define('SRA_DB_BOOL_TRUE_VALS', "'1' 1");

/**
 * the default PHP representation for boolean TRUE values
 * @type   mixed
 * @access public
 */
define('SRA_DB_BOOL_TRUE', TRUE);

/**
 * Debug constant
 * @type   boolean
 * @access public
 */
define('SRA_DB_DEBUG', FALSE);

/**
 * the maximum # of cached queries to allow for a given PHP process in 
 * _getFetchCacheParams
 * @type   int
 * @access public
 */
define('SRA_DB_QUERY_CACHE_LIMIT', 1000);

// }}}

// {{{ Includes
include_once(SRA_LIB_DIR .'/sql/SRA_ResultSet.php');
include_once(SRA_LIB_DIR .'/sql/SRA_ExecuteSet.php');
// }}}

// {{{ SRA_Database
/**
 * This abstract class is used for DB abstraction.
 * A SRA_Database wrapper class used to encapsulate database specific
 * functions and methods. Because this is an abstract class it will never
 * be instantiated by itself, but rather child SRA_Database* objects will be
 * instantiated which contain specific functionality for one database
 * server type.
 *
 * @author Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_Database {
    // {{{ Properties
    /**
     * values for boolean FALSE
		 * 
     * @type   array
     * @access private
     */
    var $_boolFalseVals;
		
    /**
     * PHP representation for boolean FALSE
		 * 
     * @type   mixed
     * @access private
     */
    var $_boolFalse;
		
    /**
     * values for boolean TRUE
		 * 
     * @type   array
     * @access private
     */
    var $_boolTrueVals;
		
    /**
     * PHP representation for boolean TRUE
		 * 
     * @type   mixed
     * @access private
     */
    var $_boolTrue;
    
    /**
     * an optional database to use for read-only queries
     * @type string
     * @access private
     */
    var $_readOnlyDb;
		
    /**
     * See the sierra-conf.dtd for more details
		 * 
     * @type   string[]
     * @access protected
     */
    var $_config;
    /**
     * This attribute references the connections to database servers for
     * this database object. If more than 1 read-write database server is
     * provided in the _config attribute, this array will be instantiated
     * and handled based on the replication options specified in the
     * _replicationOptions attribute.
     * @type   Object[]
     * @access private
     */
    var $_dbs;
    /**
     * This attribute is used to keep track of whether or not the SRA_Database
     * object is currently in the middle of a transaction. This attribute
     * may affect the behavior of the SRA_Database*::execute method calls
     * (dependent on the SRA_Database type). For more info see the
     * startTransactin and commit method api.
     * @type   boolean
     * @access protected
     */
    var $_inTransaction = FALSE;
		
		/**
		 * Used to store fetch table cache
     * @type array
     * @access protected
		 */
		var $_fetchCache = array();
		
		/**
		 * The SRA_TimeZone to use for Date* types
     * @type SRA_TimeZone
     * @access protected
		 */
		var $_timeZone;
		 
    // }}}

    // {{{ SRA_Database()
    /**
     * do NOT instantiate this, or any of the SRA_Database* classes directly. 
     * instead, use the singleton SRA_Database::getDatabase method to obtain a 
     * reference
     * @access private
     */
    function SRA_Database() {}
    // }}}
		
    // {{{ applyLimitAndOffset
    /**
     * Returns the query properly formatted with LIMIT and OFFSET sql 
		 * constraints if valid and specified
     *
		 * @param string $query the sql query to apply the constraints to
		 * @param int $limit the LIMIT constraint
		 * @param int $offset the OFFSET constraint
     * @access  public
		 * @return string
     */
    function applyLimitAndOffset($query, $limit, $offset) {
			if (!strstr(strtolower($query), ' limit ') && !strstr(strtolower($query), ' offset ')) {
				if ($limit > 0) {
					$query .= " LIMIT ${limit}";
				}
				if ($offset > 0) {
					$query .= " OFFSET ${offset}";
				}
			}
			return $query;
    }
    // }}}
    
    // {{{ clearCache()
    /**
     * clears any query cache in the database instance
     * @return  void
     */
    function clearCache() {
			$this->_fetchCache = array();
    }
    // }}}

    // {{{ close()
    /**
     * Closes all of the db connections, release all resources. To close
     * the database connections this method calls the child closeConn
     * method for every databases connection in the _dbs
     * arrays. This method returns an SRA_Error object if any errors occur.
     * Otherwise it returns nothing.
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        // Close all connections in _dbs.
				$this->_dbs = is_array($this->_dbs) ? $this->_dbs : array($this->_dbs);
        $keys = array_keys($this->_dbs);
        foreach ($keys as $key)
        {
            $this->_closeConn($this->_dbs[$key]);
            unset($this->_dbs[$key]);
        }
    }
    // }}}

    // {{{ commit()
    /**
     * Abstract method that commits the database changes done during a
     * transaction that is in progress. This method MUST be implemented in
     * the child SRA_Database* classes.One important element in this method is
     * that, similiar to the SRA_Database*::execute/SRA_Database::processUpdate
     * methods, this is a pass up - pass down query. This means that the
     * SRA_Database* method will initially be called (with no conn parameter
     * specified). This method will then simply call (and return) the
     * SRA_Database::processCommit method (which in turn will call the
     * SRA_Database*::commit method with the conn parameter specified for each
     * of the replication servers).
		 *
		 * This method MUST set the _inTransaction flag to FALSE
     *
     * @param object $conn the database connection object to commit the
     * transaction for. If not specified this method will simply return
     * the SRA_Database(parent)::processCommit method (which will in turn call
     * this method for all of the replication servers).
     * @access  public
     * @return  void
     */
    function commit($conn=NULL)
    {
        // SRA_Error.
        $msg = "SRA_Database::commit(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}
		
    // {{{ convert()
    /**
     * generic convert method. this simply makes a passthru call to the correct 
		 * convert* method based on the $type specified. this method does not need 
		 * to be overriden by subclasses
     *
     * @param   string $type the data type
		 * @param   mixed $data the data to convert
     * @access  public
     * @return  string
     */
    function convert($type, $data) {
			switch ($type) {
				case SRA_DATA_TYPE_BLOB :
					return $this->convertBlob($data);
				case SRA_DATA_TYPE_BOOLEAN :
					return $this->convertBoolean($data);
				case SRA_DATA_TYPE_TIME :
          return $this->convertTime($data);
				case SRA_DATA_TYPE_DATE :
					return $this->convertDate($data);
				case SRA_DATA_TYPE_FLOAT :
					return $this->convertFloat($data);
				case SRA_DATA_TYPE_INT :
					return $this->convertInt($data);
				case SRA_DATA_TYPE_STRING :
					return $this->convertText($data);
				default: 
					$msg = "SRA_Database::convert: SRA_Error - Invalid type: ${type}";
					return(SRA_Error::logError($msg, __FILE__, __LINE__));
			}
    }
    // }}}

    // {{{ convertBlob()
    /**
     * Convert a blob to the underlying db type. Call this method to
     * prepare blob values for insertion. Returns an SRA_Error object if the
     * blob parameter is not a blob. This is an abstract method that must
     * be implemented by the child SRA_Database* classes.
     *
     * @param string $blob the blob value to convert.
     * @access  public
     * @return  string
     */
    function convertBlob($blob)
    {
        // SRA_Error.
        $msg = "SRA_Database::convertBlob(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ convertBoolean()
    /**
     * Convert a boolean to the underlying db type. Call this method to
     * prepare boolean values for insertion. for more information, see the "db" element 
		 * "boolean-false" and "boolean-true" attribute definitions in sierra-config*.dtd
     * this method can be overriden by the corresponding db implementation
     *
     * @param   boolean $bool boolean value to convert
     * @access  public
     * @return  string
     */
    function convertBoolean($bool) {
			if (SRA_Util::convertBoolean($bool) || $bool === $this->_boolTrue) {
				$val = $this->_boolTrueVals[0];
			}
			else if (isset($bool)) {
				$val = $this->_boolFalseVals[0];
			}
			else {
				$val = NULL;
			}
			return is_string($val) ? "'${val}'" : (isset($val) ? $val : 'NULL');
    }
    // }}}

  // {{{ convertDate
  /**
   * Convert a dateOnly SRA_GregorianDate object into a database readable 
   * string. This format is required for insertion into the database. this is an 
   * abstract method that must be implemented by the child SRA_Database* 
   * classes. This method returns an SRA_Error object if the $dateTime parameter 
   * is not a valid SRA_GregorianDate object
   * @param SRA_GregorianDate $dateTime the date to convert
   * @access public
   * @return string
   */
  function convertDate($dateTime) {
    // SRA_Error.
    $msg = "SRA_Database::convertDate(): This method is abstract.";
    return(SRA_Error::logError($msg, __FILE__, __LINE__));
  }
  // }}}
  
  // {{{ convertTime
  /**
   * Convert a date/time SRA_GregorianDate object into a database readable 
   * string using the db timezone. This format is required for insertion into 
   * the database. this is an abstract method that must be implemented by the 
   * child SRA_Database* classes. This method returns an SRA_Error object if the
   * $dateTime parameter is not a valid SRA_GregorianDate object
   * @param SRA_GregorianDate $dateTime the time to convert
   * @access public
   * @return string
   */
  function convertTime($dateTime) {
    // SRA_Error.
    $msg = "SRA_Database::convertDate(): This method is abstract.";
    return(SRA_Error::logError($msg, __FILE__, __LINE__));
  }
  // }}}

    // {{{ convertFloat()
    /**
     * Convert a Float to the underlying db type. Call this method to
     * prepare Float values for insertion. Returns an SRA_Error if the float
     * parameter is not a number. This is an abstract method that must be
     * implemented by the child SRA_Database* classes.
     *
     * @param float $value the float to prepare.
     * @access  public
     * @return  string
     */
    function convertFloat($value)
    {
        // SRA_Error.
        $msg = "SRA_Database::convertFloat(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}
		
    // {{{ convertInt()
    /**
     * Added for consistency between types. No actual conversion is necessary 
		 * for an Integer so this method simply returns the value passed. If a 
		 * subclass requires any conversion, this method can be overriden.
     *
     * @param int $value the int to prepare.
     * @access  public
     * @return  string
     */
    function convertInt($value)
    {
        return $value === NULL ? 'NULL' : $value;
    }
    // }}}

    // {{{ convertText()
    /**
     * Convert a text string to the underlying db string type. Escape any
     * characters that would crash the query. Call this method to prepare
     * text values for insertion. This is an abstract method that must be
     * implemented by the child SRA_Database* classes.
     *
     * @param string $text string to convert.
     * @access  public
     * @return  string
     */
    function convertText($text)
    {
        // SRA_Error.
        $msg = "SRA_Database::convertText(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ execute()
    /**
     * Abstract method used to executes a SQL query. If an error occurs
     * during execution, this method will log a new error with the SQL
     * query as part of the error message, and return an error object.
     * Otherwise it returns an SRA_ExecuteSet object which will contain the
     * number of rows affected by the query and possibly the
     * sequenceColumn value is this is an insert query and an auto
     * incremental column is being used (see SRA_Database::execute::query api
     * for more detail). This method is used with queries that don't
     * return a result set / RecordSet object. Because this method is
     * abstract, it MUST be implemented by the child SRA_Database* object.
     * This method also will either auto commit the query, or buffer it
     * based on what is specified by the _autoCommit attribute.
     *
     * The basic flow of a SRA_Database*::execute method is that it is called
     * by another class (db parameter is null), this method calls the
     * parent::processUpdate method, this method then runs the update
     * query (by calling the child execute method with a db connection
     * parameter specified) for each of the active replication servers. It
     * then returns either the number of records affected (as returned by
     * the first execute call), or an SRA_Error object (if returned by all
     * execute calls). If an SRA_Error occurs, the query will be written to a
     * buffer file for future processing. If a buffer file already exists
     * for any db server, the query will be written directly to that file
     * (execute will not be called at all). Buffer files are routinely
     * processed by the BGP and deleted when successful (when db server
     * accepts all queries).
     *
     * @param string $query the query to execute. This should be an
     * update/insert/ or delete query.
     * @param int $errorLevel this optional parameter defines the error
     * level of the SRA_Error object that should will be created if the db
     * query fails. The default value is SRA_ERROR_PROBLEM. This value may
     * be set to SRA_ERROR_OPERATIONAL if you do not wish for the error to
     * be logged.
     * @param string $incCol if the query is an insert query, and a db
     * managed auto-incremental column is being used by the table being
     * inserted into, and this value is needed by the Object calling the
     * method, this value will correspond with the name of the
     * auto-incremental column.
     *
     * If this parameter is specified, and an INSERT query is being
     * performed, this method will attempt to retrieve the value of the
     * incremental column that was used by the database during the insert
     * and add this value to the resultant SRA_ExecuteSet object.
     * @param object $conn this parameter will be null when the execute
     * method is initially called by another object. However, when the
     * call is passed up to SRA_Database::processUpdate method, this method
     * will pass the query back down to the SRA_Database*::execute method with
     * the conn parameter specified (one call for each replication
     * server).  At this time the SRA_Database*::execute method will actually
     * attempt to process that query running the database specified
     * execute command and returning applicable # of rows that were
     * affected by the query (or an SRA_Error object). These return values
     * will then be passed back down to the original call to the execute
     * method and returned to the original calling object.
     * @access  public
     * @return  SRA_ExecuteSet
     */
    function &execute($query, $incCol=FALSE, $errorLevel=SRA_ERROR_PROBLEM, $conn=NULL)
    {
        // SRA_Error.
        $msg = "SRA_Database::execute(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ fetch()
    /**
     * This abstract method executes a SQL query when implemented by a child
     * SRA_Database* class. If an error occurs during execution, this method will
     * log a new error with the SQL query as part of the error message, and
     * return an error object. Otherwise it returns a reference to a RecordSet
     * object.
     *
     * @param string $query the SQL query to execute.
     * @param array $types the data types in sequential order that will be returned
     * by this query. These types can be any of the SRA_DATA_TYPE_*
     * constants. if not specified, all types will be assumed to be text 
     * (SRA_DATA_TYPE_STRING)
		 * @param	int $limit only return a maximum of this # of rows
		 * @param int $offset skip that many rows before beginning to return rows
		 *					THE FIRST ROW IS AN OFFSET OF 0
		 * @param boolean $getActualCount whether or not to set the ACTUAL # of 
		 * 					rows in the resulting SRA_ResultSet instance if $limit or $offset 
		 * 					constraints are used
     * @param int $errorLevel this optional parameter defines the error
     * level of the SRA_Error object that should will be created if the db
     * query fails. The default value is SRA_ERROR_PROBLEM. This value may
     * be set to SRA_ERROR_OPERATIONAL if you do not wish for the error to
     * be logged.
     * @access  public
     * @return	SRA_ResultSet
     */
    function &fetch($query, $types=NULL, $limit=FALSE, $offset=FALSE, $getActualCount=TRUE, $errorLevel=SRA_ERROR_PROBLEM)
    {
        // SRA_Error.
        $msg = "SRA_Database::fetch(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}
		
    // {{{ getColumnDefinition()
    /**
     * provides an sql statement defining the data type, constraints, and 
		 * referential integrity (optional) for a given SRA_SchemaColumn. this method 
		 * may be implemented by each of the underlying database types if the 
		 * default MYSQL implementation does not suffice
     *
		 * @param SRA_SchemaTable $table the SRA_SchemaTable that the column belongs to
     * @param SRA_SchemaColumn $column the SRA_SchemaColumn to create the definition for
		 * @param boolean $dbRefIntegrity whether or not referential integrity should 
		 * be enforced at the database layer
		 * @param boolean $addCheckConstraint whether or not to add a check constraint 
		 * to the column definition
     * @access  public
     * @return	string
     */
    function getColumnDefinition(& $table, & $column, $dbRefIntegrity = TRUE, $addCheckConstraint = TRUE) {
			$def = '';
			
			// default return type is for mysql
			if ($column->getColumnType()) {
				$def = $column->getColumnType();
			}
			else {
				$type = $column->getType();
				switch ($type) {
					case SRA_DATA_TYPE_BLOB:
            $def = 'BLOB';
						break;
					case SRA_DATA_TYPE_BOOLEAN:
						$def = 'ENUM(' . $this->convertBoolean(TRUE) . ',' . $this->convertBoolean(FALSE) . ')';
						break;
					case SRA_DATA_TYPE_DATE:
            $def = 'DATE';
            break;
          case SRA_DATA_TYPE_TIME:
						$def = 'TIMESTAMP';
						break;
					case SRA_DATA_TYPE_FLOAT:
						$def = 'DOUBLE';
						break;
					case SRA_DATA_TYPE_INT:
						$def = 'INT';
						break;
					case SRA_DATA_TYPE_STRING:
						$def = $column->hasValidator('maxLength') ? 'VARCHAR(' . $column->getVars('maxLength') . ')' : 'TEXT';
						break;
				}
			}
			$default = $column->getDefault();
			if ($default && (is_numeric($default) || substr($default, 0, 1) == '"' || substr($default, 0, 1) == "'")) {
        if ($column->getType() == SRA_DATA_TYPE_BOOLEAN) {
          $default = SRA_Util::convertBoolean(eval('return ' . $default . ';')) ? $this->convertBoolean(TRUE) : $this->convertBoolean(FALSE);
        }
				$def .= ' DEFAULT ';
				$def .= $column->getType() == SRA_DATA_TYPE_INT ? $default : "'" . str_replace("'", "\'", SRA_Util::stripQuotes(SRA_Util::stripQuotes($default), "'", "'")) . "'";
			}
			else if (!$column->hasValidator('required') && !$column->isSequence() && !$table->isPrimaryKey($column->getName())) {
				$def .= ' DEFAULT NULL';
			}
			$def .= $column->hasValidator('required') || $table->isPrimaryKey($column->getName()) ? ' NOT NULL' : '';
			if ($column->isSequence()) {
				$def .= ' AUTO_INCREMENT';
			}
			if ($dbRefIntegrity && $column->getReferences()) {
				$def .= ' REFERENCES ' . $column->getReferences();
				$def .= $column->isOnDeleteCascade() ? ' ON DELETE CASCADE' : ' ON DELETE SET NULL';
			}
			
			// add CHECK constraints (not supported by mysql)
			if ($addCheckConstraint) {
				// max validator
				if ($column->hasValidator('max')) {
					$def .= ' CHECK (' . $column->getName() . ' <= ' . $column->getVars('max') . ')';
				}
				// min validator
				else if ($column->hasValidator('min')) {
					$def .= ' CHECK (' . $column->getName() . ' >= ' . $column->getVars('min') . ')';
				}
				// range validator
				else if ($column->hasValidator('range')) {
					$def .= ' CHECK (' . $column->getName() . ' BETWEEN ' . $column->getVars('min') . ' AND ' . $column->getVars('max') . ')';
				}
			}
			
			return $def;
    }
    // }}}
    
    // {{{ getQueryValue
    /**
     * used to retrieve a single query value. returns NULL if the query does not 
     * return a row
     * @param SRA_Database $db the SRA_Database connection
     * @param string $query the query to return the value from. this query 
     * should only have one column. the value of the first column/first row will 
     * be returned
     * @param string $type the data type for the column being retrieved
     * @access  public
     * @return	string
     */
    function getQueryValue(& $db, $query, $type=SRA_DATA_TYPE_STRING) {
			if (SRA_ResultSet::isValid($results =& $db->fetch($query, array($type))) && $results->count()) {
        $row =& $results->next();
        return $row[0];
      }
      return NULL;
    }
    // }}}
		
    // {{{ getTableDefinition()
    /**
     * provides an sql statement defining the constraints for a given 
		 * SRA_SchemaTable. this method may be implemented by each of the underlying 
		 * database types if the default implementation does not suffice
     *
     * @param SRA_SchemaTable $table the SRA_SchemaTable to create the definition for
		 * @param boolean $dbRefIntegrity whether or not referential integrity should 
		 * be enforced at the database layer
     * @access  public
     * @return	string
     */
    function getTableDefinition(& $table, $dbRefIntegrity = TRUE) {
			$def = '';
			
			$pk = $table->getPrimaryKey();
			if (is_array($pk) && count($pk)) {
				$def = 'PRIMARY KEY (';
				$started = FALSE;
				foreach ($pk as $primaryKey) {
					$def .= $started ? ',' : '';
					$def .= $primaryKey;
					$started = TRUE;
				}
				$def .= ')';
			}
			else if (!is_array($pk) && $pk) {
				$def = "PRIMARY KEY (${pk})";
			}
			
			return $def;
    }
    // }}}
    
    // {{{ getExtraTableDdl
    /**
     * this method allows implementing databases to provide DDL that should be 
     * rendered outside of the table definition
     * @param SRA_SchemaTable $table the SRA_SchemaTable
     * @access public
     * @return string
     */
    function getExtraTableDdl(& $table) {
			return NULL;
    }
    // }}}
		
    // {{{ getRecordCount()
    /**
     * returns total # of records returned for a given query
     *
     * @param   SRA_Database $db the SRA_Database connection
     * @param   string $query the query
     * @access  public
     * @return	int
     */
    function getRecordCount(&$db, $query) {
      if (!strpos(strtolower($query), ' having ')) {
        $parseQuery = SRA_Database::parseQuery($query);
        $pieces = explode(' ', $parseQuery['orderConstraint']);
        $col = 'count(*)';
        $groupFound = FALSE;
        foreach ($pieces as $piece) {
          $eval = strtolower(trim($piece));
          if (!$eval || $eval == 'by') {
            continue;
          }
          if ($eval == 'group') {
            $groupFound = TRUE;
            continue;
          }
          if ($groupFound) {
            $col = "count(distinct ${piece})";
            break;
          }
        }
        $parseQuery['select'] = array($col => $col);
        return SRA_Database::getQueryValue($db, SRA_Database::reverseParseQuery($parseQuery, FALSE), SRA_DATA_TYPE_INT);
      }
      else {
        $results =& $db->fetch($query);
        return $results->getTotalCount();
      }
    }
    // }}}

    // {{{ _getAppDbConnection()
    /**
     * This method is called by the child SRA_Database* classes to retrieve a
     * database connection to utilize for a query. This method manages all
     * of the connections as well as replication. This method will also
     * run the additional execute queries if the application side updates
     * are enabled for replication (i.e. returns first read-write database
     * object, then opens connection to remaining read-write servers, and
     * executes the query for each of those servers by calling the child
     * SRA_Database*::execute method).
     * @param string $query the query statement that the database
     * object is intended for. This query is evaluated is replication is
     * in place and an appropriate database object returned. If left
     * blank, it will be assumed that the query is an update query and
     * requires a valid read-write connection.
     * @param boolean $fetch whether or not this is for a fetch query
     * @access  protected
     * @return  object
     */
    function _getAppDbConnection($query=NULL, $fetch=FALSE) {
      if ($this->_readOnlyDb && $fetch && SRA_Database::isValid($db =& SRA_Controller::getAppDb($this->_readOnlyDb)) && (is_resource($conn = $db->_getAppDbConnection($query)) || (get_class($conn) && strtolower(get_class($conn)) != 'sra_error'))) {
        return $conn;
      }
			else if (!$this->_dbs || (!is_resource($this->_dbs) && (!get_class($this->_dbs) || strtolower(get_class($this->_dbs)) == 'sra_error'))) {
				$config=array();
				$config['host'] = $this->_config['host'];
				$config['server'] = $this->_config['host'];
				$config['name'] = isset($this->_config['name']) ? $this->_config['name'] : $this->_config['key'];
				$config['user'] = $this->_config['user'];
				$config['password'] = $this->_config['password'];
				if (isset($this->_config['password-decrypt'])) $config['password'] = $this->_config['password-decrypt']($config['password']);
				$config['port'] = $this->_config['port'];

				$this->_dbs = $this->_openConn($config);

				if (SRA_Error::isError($this->_dbs)) {
					$errorLevel = SRA_ERROR_PROBLEM;
				
					// Check log file for any special error levels to set
					if (isset($this->_config['error-level'])) {
						$errorLevel = $this->_config['error-level'];
					}
					$msg = "SRA_Database::_getAppDbConnection(): Problem connecting to db.";
					return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
				}
			}
		
			return $this->_dbs;
    }
    // }}}

    // {{{ getNextSequence()
    /**
     * Abstract method used to return the next sequence value from the database sequence specified.
     * If an error occurs during execution, this method will log a new error with the SQL query as
     * part of the error message, and return an error object. Otherwise it returns an integer value
     * representing the next sequence value. Because this method is abstract, it MUST be implemented
     * by the child SRA_Database* object. This method also will either auto commit the query, or buffer
     * it based on what is specified by the _autoCommit attribute. See the SRA_Database::execute api for
     * more details on the flow of the nextSequence methods.
     *
     * @param string $sequence the name of the sequence to return the next value for.
     * @param	int $errorLevel this optional parameter defines the error level of the SRA_Error object
     * that should will be created if the db query fails. The default value is SRA_ERROR_PROBLEM. This
     * value may be set to SRA_ERROR_OPERATIONAL if you do not wish for the error to be logged.
     * @param	object $conn this parameter will be null when the getNextSequence method is initially
     * called by another object. However, when the call is passed up to SRA_Database::processGetNextSequence
     * method, this method will pass the query back down to the SRA_Database*::getNextSequence method with the
     * conn parameter specified (one call for each replication server).  At this time the
     * SRA_Database*::getNextSequence method will actually attempt to process that query running the database
     * specified sequence query and returning applicable sequence value. This return value will then be
     * passed back down to the original call to the getNextSequence method and returned to the original
     * calling object.
     * @access  public
     * @return  int
     */
    function getNextSequence($sequence, $errorLevel = SRA_ERROR_PROBLEM, $conn = FALSE)
    {
        $msg = "SRA_Database::getNextSequence(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}
		
		// {{{ getDefaultPort
		/**
		 * Static method that returns the default port for the database type 
		 * specified
		 *
		 * @param  string $dbType the database type. must correspond with one of the 
		 * SRA_DB_TYPE_* constants
		 * @access  public
		 * @return  int
		 */
		function getDefaultPort($dbType) {
			if (SRA_DB_TYPE_MYSQL == $dbType) {
				return 3306;
			}
			else if (SRA_DB_TYPE_POSTGRESQL == $dbType) {
				return 5432;
			}
			else if (SRA_DB_TYPE_MSSQL == $dbType) {
				return 1433;
			}
			// unknown type
			return FALSE;
		}
		// }}}
	
    // {{{ importFile()
    /**
     * This method is abstract and as such must be implemented by the child SRA_Database classes. Some database 
	 * types may not support this method. Please view the child SRA_Database object api for this method before 
	 * using it. This method imports a delimited file into a database table using the parameters specified. 
	 * This method returns an SRA_Error object if the file import is not successful.
	 * 
	 * @param	string $fileName the name of the file to import. This file must be delimited with the same 
	 *			number of columns as are in the table.
	 * @param	string $tableName the name of the table to insert the data into.
	 * @param	boolean $deleteFile whether or not the file should be deleted if the import is successful. 
	 * 			By default this value is TRUE.
	 * @param	string $delimiter the delimiter to use for the file import. The default value for this is a 
	 * 			comma. Note: If a delimiter occurs in a data column, it should be escaped using backslash.
     *
     * @access  public
     * @return  boolean
     */
    function importFile($fileName, $tableName, $deleteFile=TRUE, $delimiter=",")
    {
        $msg = "SRA_Database::importFile(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ isInTransaction()
    /**
     * Returns a boolean value specifying if the SRA_Database object is currently in a transaction.
     *
     * @access  public
     * @return  boolean
     */
    function isInTransaction()
    {
        return $this->_inTransaction;
    }
    // }}}
    
    // {{{ isType
    /**
     * returns TRUE if this SRA_Database instance is of the type specified
     * @param int $type the type to check for. one of the SRA_DB_TYPE_* 
     * constants
     * @access  public
     * @return  boolean
     */
    function isType($type) {
      return $this->_config['type'] == $type;
    }
    // }}}

    // {{{ isValid()
    /**
     * Static method that returns TRUE if the object parameter references a
     * valid SRA_Database object.
     *
     * @param object $object the object to validate.
     * @access  public
     * @return  boolean
     */
    function isValid($object) {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_database' || is_subclass_of($object, 'SRA_Database')));
    }
    // }}}
		
		// {{{ parseQuery()
    /**
     * parses a query and returns it in the following format:
		 *   array('select' => array('column1' => 'column1 alias', ... ,'columnN' => 'columnN alias'),
		 *         'from'   => array('table1'  => 'table1 alias', ... , 'tableN'  => 'tableN alias'),
		 *         'fromConstraint' => array('tableN' => '[if a JOIN was used, the method used for the join. this should be used in place of adding tableN to the FROM clause]'), 
		 *         'constraint' => '[everything that is part of the WHERE clause (if applicable)]',
		 *         'orderConstraint' => [everything after the WHERE clause (i.e. ORDER BY...)])
     *
     * @param   string $query the query to parse
     * @access  public static
     * @return  array
     */
    function parseQuery($query) {
			$query = str_replace('	', ' ', $query);
			$pieces = explode(' ', $query);
			$selectFound = FALSE;
			$fromFound = FALSE;
			$whereFound = FALSE;
			$orderFound = FALSE;
			$commaFound = TRUE;
			$nextCommaFound = FALSE;
			$select = array();
			$from = array();
			$fromConstraints = array();
			$constraint = '';
			$orderConstraint = '';
			$key = FALSE;
			$buffer = '';
			$lastTable = FALSE;
			foreach ($pieces as $piece) {
				$piece = trim($piece);
				$eval = strtolower(trim($piece));
				if ($fromFound) {
					$buffer .= ' ' . $piece;
				}
				if (!$whereFound && (!$eval || $eval == 'as')) {
					continue;
				}
				if ((!$whereFound && !$commaFound && $eval == ',') || ($fromFound && ($eval == 'join' || $eval == 'straight_join' || $eval == 'left' || $eval == 'right' || $eval == 'inner' || $eval == 'outer' || $eval == 'cross' || $eval == 'natural'))) {
					$commaFound = TRUE;
					continue;
				}
				if ($nextCommaFound) {
					$commaFound = TRUE;
					$nextCommaFound = FALSE;
				}
				if (!$whereFound && !$orderFound && substr($eval,-1) == ',') {
					$piece = substr($piece, 0, strlen($substr)-1);
					$nextCommaFound = TRUE;
				}
				if (!$whereFound && $eval == 'where') {
					$whereFound = TRUE;
					continue;
				}
				if (!$orderFound && ($eval == 'order' || $eval == 'group' || $eval == 'having' || $eval == 'union' || $eval == 'intersect')) {
					$orderFound = TRUE;
				}
				if (!$fromFound && $eval == 'from') {
					$fromFound = TRUE;
					$commaFound = TRUE;
					$buffer = '';
					continue;
				}
				if (!$selectFound && $eval == 'select') {
					$selectFound = TRUE;
					continue;
				}
				if ($orderFound) {
					$orderConstraint .= ' ' . $piece;
				}
				else if ($whereFound) {
					$constraint .= ' ' . $piece;
				}
				else if ($fromFound) {
					$val = $piece;
					if ($commaFound) {
						$key = $val;
						$commaFound = FALSE;
					}
					$from[$key] = $val;
					$lastTable = $key;
				}
				else if ($selectFound) {
					$val = $piece;
					if ($commaFound) {
						$key = $val;
						$commaFound = FALSE;
						$buffer = $piece;
					}
					$select[$key] = $val;
				}
				if ($lastTable && !$whereFound && !$orderFound) {
					if (!isset($fromConstraints[$lastTable])) {
						$fromConstraints[$lastTable] = '';
					}
					$fromConstraints[$lastTable] .= $buffer;
					$buffer = '';
				}
			}
			$keys = array_keys($fromConstraints);
			foreach ($keys as $key) {
				$fromConstraints[$key] = trim($fromConstraints[$key]);
        if (SRA_Util::endsWith($fromConstraints[$key], ',')) {
          $fromConstraints[$key] = substr($fromConstraints[$key], 0, -1);
        }
				if ($fromConstraints[$key] == $key) {
					unset($fromConstraints[$key]);
				}
			}
			$results = array('select' => $select, 'from' => $from, 'fromConstraints' => $fromConstraints, 'constraint' => trim($constraint), 'orderConstraint' => trim($orderConstraint));
			return $results;
    }
    // }}}
		
		// {{{ reverseParseQuery()
    /**
     * converts #parseQuery results into a query
     *
     * @param   array $parseResults #parseQuery array to convert back into a query
     * @param boolean $includeOrderConstraint whether or not to include any 
     * order constraints in the query
     * @access  public static
     * @return  string
     */
		function reverseParseQuery($parseResults, $includeOrderConstraint=TRUE) {
			$query = 'SELECT';
			$started = FALSE;
			foreach($parseResults['select'] as $col => $alias) {
				$query .= ($started ? ', ' : ' ') . $col;
				$query .= $col != $alias ? ' as ' . $alias : '';
				$started = TRUE;
			}
			$query .= ' FROM';
			$started = FALSE;
			foreach($parseResults['from'] as $table => $alias) {
        $query .= $started && !strstr(strtolower($parseResults['fromConstraints'][$table]), 'join') ? ', ' : ' ';
				$query .= !isset($parseResults['fromConstraints'][$table]) ? $table . ($table != $alias ? ' ' . $alias : '') : $parseResults['fromConstraints'][$table];
				$started = TRUE;
			}
      $query .= $parseResults['constraint'] ? ' WHERE ' . $parseResults['constraint'] : '';
			if ($includeOrderConstraint) { $query .= $parseResults['orderConstraint'] ? ' ' . $parseResults['orderConstraint'] : ''; }
			return $query;
		}
		// }}}
		
		
    // {{{ unconvertBoolean()
    /**
     * Unconverts a boolean value from a database boolean data type column. if 
		 * the $bool value equals (===) any of the #_boolTrueVals or #_boolFalseVals 
		 * values for this database, the corresponding boolean value will be 
		 * returned, otherwise, NULL will be returned. this method can be overriden 
     * by the corresponding db implementation
     *
     * @param   boolean $bool boolean value to convert
     * @access  public
     * @return  boolean
     */
    function unconvertBoolean($bool) {
			foreach ($this->_boolTrueVals as $val) {
				if ($bool === $val) {
					return TRUE;
				}
			}
			foreach ($this->_boolFalseVals as $val) {
				if ($bool === $val) {
					return FALSE;
				}
			}
			return NULL;
    }
    // }}}

    // {{{ _openConn()
    /**
     * This method is used to open one connection to a database server. It
     * is called by the SRA_Database parent class in order to open needed
     * database connections. It returns either a connection object (if
     * successful) or an SRA_Error object if not.
     *
     * @param array $config the configuration parameters for the
     * database server to be connected to. This array will/must contain
     * the following values:
     *
     * 'server' => IP address or resolvable name of the server.
     * 'port' => The database server port to use
     * 'name' => The name of the database
     * 'user' => The authentication user name
     * 'password' => The authentication password
     * 'type' => The database type
     *
     * @access  protected
     * @return  object
     */
    function _openConn($config)
    {
        // SRA_Error.
        $msg = "SRA_Database::_openConn(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ processCommit()
    /**
     * This method is simliar in functionality to the
     * SRA_Database::processUpdate method. It is called by the
     * SRA_Database*::commit method in order to process all SRA_Database commits
     * (from the current transaction).  To accomplish this, this method
     * simply calls the SRA_Database*::commit method once for every
     * replication server.  If any of the commits fail, the entire
     * _queryBuffer is written to a buffer file for that database server
     * for future processing. 
     *
     * @access  public
     * @return  void
     */
    function processCommit()
    {
        // Step 2:
        // See SRA_Database*::commit() for steps.

        if (!$this->_inTransaction)
        {
            return(SRA_Error::logError("SRA_Database::processCommit: Failed - Cannot commit, no transaction is in process.",
                                    __FILE__, __LINE__));
        }

				// Get the connection.
				$conn = $this->_getAppDbConnection();
				if (SRA_Error::isError($conn))
				{
						// SRA_Error.
						$msg = "SRA_Database::processCommit() Failed to _getAppDbConnection().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}

				// Call $this->commit() with a connection parameter.
				$result = $this->commit($conn);

				if (SRA_Error::isError($result))
				{
						// SRA_Error.
						$msg = "SRA_Database::processCommit() SRA_Error returned from commit().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}
				$this->_inTransaction=FALSE;

				// Returning!!!
				return;
    }
    // }}}

    // {{{ processNextSequence()
    /**
     * This method exists only in the SRA_Database class. It is used by the child
     * classes to distribute and process any sequence queries (queries to retrieve
     * a table sequence). The flow of this process is detailed in the
     * SRA_Database::getNextSequence api. Basically, this is the method that manages
     * these types of calls. This method is necessary in order to implement database
     * replication. This method is simply the gateway between the initial
     * SRA_Database*::getNextSequence method and the actual call to run the next sequence
     * query. SRA_Database*::getNextSequence.
     *
     * @param string $sequence the name of the sequence to return the next value for.
     * @param int $errorLevel defines the error level of the SRA_Error object that should
     * will be created if the db query fails. This value may be set to SRA_ERROR_OPERATIONAL
     * if you do not wish for the error to be logged.
     * @access  protected
     * @return  void
     */
    function processNextSequence($sequence, $errorLevel)
    {
			// Get the connection.
			$conn = $this->_getAppDbConnection("INSERT query");
			if (SRA_Error::isError($conn))
			{
					// SRA_Error.
					$msg = "SRA_Database::processNextSequence: Failed to _getAppDbConnection().";
					return(SRA_Error::logError($msg, __FILE__, __LINE__));
			}
	
			// Call $this->getNextSequence() with a connection parameter.
			$sequence = $this->getNextSequence($sequence, $errorLevel, $conn);
	
			if (SRA_Error::isError($sequence))
			{
					// SRA_Error.
					$msg = "SRA_Database::processNextSequence: SRA_Error returned from getNextSequence().";
					return(SRA_Error::logError($msg, __FILE__, __LINE__));
			}
	
			return $sequence;
    }
    // }}}

    // {{{ processRollback()
    /**
     * This method is simliar in functionality to the
     * SRA_Database::processUpdate method. It is called by the
     * SRA_Database*::rollback method in order to process all SRA_Database
     * rollbacks (from the current transaction).  To accomplish this, this
     * method simply calls the SRA_Database*::rollback method once for every
     * replication server.
     *
     * @access  protected
     * @return  void
     */
    function processRollback()
    {
        // Step 2:
        // See SRA_Database*::rollback() for steps.

        if (!$this->_inTransaction)
        {
            return(SRA_Error::logError("SRA_Database::processRollback: Failed - Cannot commit, no transaction is in process.",
                                    __FILE__, __LINE__));
        }

				// Get the connection.
				$conn = $this->_getAppDbConnection();
				if (SRA_Error::isError($conn))
				{
						// SRA_Error.
						$msg = "SRA_Database::processRollback() Failed to _getAppDbConnection().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}

				// Call $this->startTransaction() with a connection parameter.
				$result = $this->rollback($conn);

				if (SRA_Error::isError($result))
				{
						// SRA_Error.
						$msg = "SRA_Database::processRollback() SRA_Error returned from rollback().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}
				$this->_inTransaction=FALSE;

				// Returning!!!
				return;
    }
    // }}}

    // {{{ processUpdate()
    /**
     * This method exists only in the SRA_Database class. It is used by the
     * child classes to distribute and process any update queries
     * (INSERT/UPDATE/DELETE queries). The flow of this process is
     * detailed in the SRA_Database::execute api. Basically, this is the
     * method that manages any database updates. This method is necessary
     * in order to implement database replication. This method is simply
     * the gateway between the initial SRA_Database*::execute method and the
     * actual call to execute a query. SRA_Database*::execute calls
     * SRA_Database::processUpdate which calls SRA_Database*::execute.
     *
     * @param string $query the query to execute.
     * @param string $incCol if the query is an insert query, and a db managed
     * auto-incremental column is being used by the table being inserted into,
     * and this value is needed by the Object calling the method, this value
     * will correspond with the name of the auto-incremental column.
     * @param int $errorLevel defines the error level of the SRA_Error object that should
     * will be created if the db query fails. This value may be set to SRA_ERROR_OPERATIONAL
     * if you do not wish for the error to be logged.
     * @access  protected
     * @return  SRA_ExecuteSet
     */
    function &processUpdate($query, $incCol, $errorLevel)
    {
        // Step 2:
        // See SRA_Database*::execute() for steps.

				// Get the connection.
				$conn = $this->_getAppDbConnection($query);
				if (SRA_Error::isError($conn))
				{
						// SRA_Error.
						$msg = "SRA_Database::processUpdate() Failed to _getAppDbConnection().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}

				// Call $this->execute() with a connection parameter.
				$execute_set = $this->execute($query, $incCol, $errorLevel, $conn);

				if (SRA_Error::isError($execute_set))
				{
						// SRA_Error.
						$msg = "SRA_Database::processUpdate() SRA_Error returned from execute().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
				}

				return($execute_set);
    }
    // }}}
		
		// {{{ recordCount()
		/**
		 * Returns the # of records that exist in a given database table for a 
		 * given criteria
		 *
		 * @param SRA_Database $db The SRA_Database instance to query
		 * @param string $table the name of the table
		 * @param array $vals an associative array of column/value pairs to check. 
		 * all of the values in this array must be sql ready. if this parameter is 
		 * not specified then the return value will be the # of all of the records 
		 * in that database table
		 * @param string $constraint an additional query constraint to apply (for 
		 * example: TYPE='1')
		 * @access  public
		 * @return  int
		 */
		function recordCount(& $db, $table, $vals = FALSE, $constraint = FALSE) {
			if (!SRA_Database::isValid($db)) {
				$msg = "SRA_Database::recordCount: Failed - db parameter is not a valid SRA_Database object";
				return(SRA_Error::logError($msg, __FILE__, __LINE__));
			}
			if (!$table) {
				$msg = "SRA_Database::recordCount: Failed - table parameter was not specified";
				return(SRA_Error::logError($msg, __FILE__, __LINE__));
			}
			$sql = "SELECT COUNT(*) FROM ${table}";
			if (is_array($vals)) {
				$started = FALSE;
				foreach ($vals as $column => $val) {
					if ($started) {
						$sql .= ' AND ';
					}
					else {
						$sql .= ' WHERE ';
					}
					$sql .= "${column} = ${val}";
					$started = TRUE;
				}
			}
			if ($constraint) {
				if ($started) {
					$sql .= ' AND ';
				}
				else {
					$sql .= ' WHERE ';
				}
				$sql .= " ${constraint}";
				$started = TRUE;
			}
			if (SRA_Error::isError($results =& $db->fetch($sql, array(SRA_DATA_TYPE_INT)))) {
				return $results;
			}
			$row =& $results->next();
			return $row[0];
		}
		// }}}

    // {{{ rollback()
    /**
     * Abstract method that cancels any database changes done during a
     * transaction that is in progress. This method must be implemented by
     * the child SRA_Database* classes. This method returns the
     * SRA_Database::processRollback method is the conn parameter is not
     * specified.
     *
     * @param object $conn the database connection object to rollback a
     * transaction for. If null (default) this method will simply return
     * SRA_Database::processRollback. Otherwise it will attempt to rollback
     * the transaction using the specified connection.
     * @access  public
     * @return  void
     */
    function rollback($conn=NULL)
    {
        // SRA_Error.
        $msg = "SRA_Database::rollback(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ startTransaction()
    /**
     * This method will start a new transaction for the current database
     * connections. Since this action is database specific, this is an
     * abstract method that must be implemented by the SRA_Database* child
     * classes. One important element in this method is that, similiar to
     * the SRA_Database*::execute/SRA_Database::processUpdate methods, this is a
     * pass up - pass down query. This means that the SRA_Database* method
     * will initially be called (with no conn parameter specified). This
     * method will then simply call (and return) the
     * SRA_Database::startTransactions method (which in turn will call the
     * SRA_Database*::startTransaction method with the conn parameter
     * specified for each of the replication servers).
		 * 
		 * This method MUST set the _inTransaction flag to TRUE
     *
     * @param object $conn the database connection object to start the
     * transaction for. If not specified this method will simply return
     * the SRA_Database(parent)::startTransactions method (which will in turn
     * call this method for all of the replication servers).
     * @access  public
     * @return  void
     */
    function startTransaction($conn=NULL)
    {
        // SRA_Error.
        $msg = "SRA_Database::startTransaction(): This method is abstract.";
        return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    // }}}

    // {{{ startTransactions()
    /**
     * This method is the gateway for a child SRA_Database* object to be able
     * to start a transaction. Because the SRA_Database object manages
     * replication, all transactions (which imply update queries) must
     * pass through all of the replcation servers. Hence, it is necessary
     * to start a transaction on all of the database servers whenever a
     * transaction is needed.  This is accomplished by this method simply
     * calling the SRA_Database::startTransaction method for each of the
     * replication servers. See the SRA_Database::startTransaction api for
     * more info.  This method also rollsback any existing transaction.
     *
     * @access  protected
     * @return  void
     */
    function startTransactions()
    {
        // Step 2:
        // See SRA_Database*::startTransaction() for steps.

        if ($this->_inTransaction)
        {
            return(SRA_Error::logError("SRA_Database::startTransaction: Failed - Transaction cannot be started. One is already in progress.",
                                    __FILE__, __LINE__));
        }

				// Get the connection.
				$conn = $this->_getAppDbConnection();
				if (SRA_Error::isError($conn))
				{
						// SRA_Error.
						$msg = "SRA_Database::startTransactions() Failed to _getAppDbConnection().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}

				// Call $this->startTransaction() with a connection parameter.
				$result = $this->startTransaction($conn);

				if (SRA_Error::isError($result))
				{
						// SRA_Error.
						$msg = "SRA_Database::startTransactions() SRA_Error returned from startTransaction().";
						return(SRA_Error::logError($msg, __FILE__, __LINE__));
				}
				$this->_inTransaction=TRUE;
				return;
    }
    // }}}
		
		
    // {{{ _fetchInCache()
    /**
     * This method is used to determine whether or not cache exists for a given 
		 * query based on the database "table-cache" configuration element
		 * (see configuration description for more information)
     *
     * @param string $query the query
     * @access  protected
     * @return  SRA_ResultSet (if cache exists) or FALSE otherwise
     */
    function & _fetchInCache($query)
    {
				
				// only check if in memory cache does not exist
				if (!array_key_exists($query, $this->_fetchCache)) {
					$this->_fetchCache[$query] = FALSE;
					
					// cache parameters exist
					if ($params =& $this->_getFetchCacheParams($query)) {
						if ($ccount = count($cacheParams) > SRA_DB_QUERY_CACHE_LIMIT) $cacheParams = array();
						if (file_exists($params['file']) && ($params['expire'] == 0 || (fileMTime($params['file']) + ($param['expire']*60)) >= time())) {
							// cache found
							SRA_Util::printDebug("SRA_Database::_fetchInCache - Cache exist for query $query current time: " . time() . ", expire time: " . (fileMTime($params['file']) + ($param['expire']*60)), SRA_DB_DEBUG, __FILE__, __LINE__);
							$this->_fetchCache[$query] = unserialize(SRA_File::tostring($params['file']));
						}
						else if (isset($params['file']) && isset($param['expire']) && (fileMTime($params['file']) + (((int) $param['expire'])*60)) < time()) {
							SRA_Util::printDebug("SRA_Database::_fetchInCache - Cache is expired for query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
						}
						else {
							SRA_Util::printDebug("SRA_Database::_fetchInCache - Cache does not exist for query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
						}
					}
					else {
						SRA_Util::printDebug("SRA_Database::_fetchInCache - Cache parameters not defined for query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
					}
				}
				else {
					SRA_Util::printDebug("SRA_Database::_fetchInCache - Memory cache exists for query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
				}
				// reset SRA_ResultSet
				if (SRA_ResultSet::isValid($this->_fetchCache[$query])) {
					$this->_fetchCache[$query]->reset();
				}
				return $this->_fetchCache[$query];
    }
    // }}}
		
		
    // {{{ _addFetchToCache()
    /**
     * This method is used to write the results of a fetch to cache (if cache 
		 * should be written at all)
     *
     * @param string $query the query
		 * @param SRA_ResultSet $results the results of the query
     * @access  protected
     * @return  void
     */
    function _addFetchToCache($query, & $results)
    {
			
			// cache parameters exist
			if (!$this->_fetchCache[$query] && ($params =& $this->_getFetchCacheParams($query))) {
				$fp = fopen($params['file'], 'w');
				fwrite($fp, serialize($results));
				fclose($fp);
				$this->_fetchCache[$query] =& $results;
				SRA_Util::printDebug("SRA_Database::_addFetchToCache - Added cache for query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
			}
    }
    // }}}
		
		
    // {{{ _getFetchCacheFileName()
    /**
     * This method returns the cache parameters for a given query
     *
     * @param string $query the query
     * @access  private
     * @return  mixed
     */
    function & _getFetchCacheParams($query)
    {
			static $cacheParams = array();
			if (!array_key_exists($query, $cacheParams)) {
                                if ($ccount = count($cacheParams) > 100) $cacheParams = array();
				$cacheParams[$query] = FALSE;
				
				// determine which table is being queried
				$tmp = explode(' ', $query);
				$tables = array();
				foreach ($tmp as $piece) {
					if ($next && !(trim(strtolower($piece)) == 'where' || trim(strtolower($piece)) == '')) {
						$tables[] = trim($piece);
					}
					else if ($next) {
						break;
					}
					if (trim(strtolower($piece)) == 'from') {
						$next = TRUE;
					}
				}
				sort($tables);
				$key = trim(implode(' ', $tables));
				SRA_Util::printDebug("SRA_Database::_getFetchCacheParams - Looking for key: $key", SRA_DB_DEBUG, __FILE__, __LINE__);
				// cache parameters have been defined
				if (count($tables) && $this->_config['table-cache'][$key]) {
					$dir = SRA_DIR . '/tmp/';
					if ($this->_config['table-cache'][$key]['attributes']['dir']) {
						$dir = $this->_config['table-cache'][$key]['attributes']['dir'];
					}
					$expire = $this->_config['table-cache'][$key]['attributes']['expire'];
					$fileName = str_replace('<', '', str_replace('=', '', str_replace('>', '', str_replace('from', '', str_replace('select', '', str_replace('?', '', str_replace('|', '', str_replace('%', '', str_replace('/', '', str_replace('"', '', str_replace("'", '', str_replace(',', '', str_replace(';', '', str_replace('*', '', str_replace(')', '', str_replace('(', '', str_replace(' ', '', $query)))))))))))))))));
					if (strlen($fileName) <=255 && strlen($fileName) > 0) {
						SRA_Util::printDebug("SRA_Database::_getFetchCacheParams - Cache parameters set for key $key, file ${dir}${fileName}, expire $expire, query $query", SRA_DB_DEBUG, __FILE__, __LINE__);
						$cacheParams[$query] = array('file' => $dir . $fileName, 'expire' => $expire);
					}
				}
			}
			return $cacheParams[$query];
    }
    // }}}
		
		
    // {{{ getBooleanArray()
    /**
     * returns an array of possible boolean values based on the string specified
     *
     * @param   string $str the string
     * @access  public static
     * @return  array
     */
		function getBooleanArray($str) {
			$vals = array();
			$pieces = explode(' ', $str);
			foreach ($pieces as $piece) {
				// string
				if (strstr($piece, "'")) {
					$vals[] = str_replace("'", '', $piece);
				}
				else {
					if (is_numeric($piece)) {
						$vals[] = $piece * 1;
					}
					else {
						$vals[] = constant($piece);
					}
				}
			}
			return $vals;
		}
		// }}}
    
    
    // {{{ getDatabase
    /**
     * singleton method for this class
     *
     * @param array $config The database config array. See the api for the 
     * SRA_Database::_config attribute for more info.
     * @access  public static
     * @return  SRA_Database
     */
		function & getDatabase($configs) {
			// Check to make sure $configs is an array.
			if (!is_array($configs)) {
        $msg = "SRA_Database::getDatabase: Failed - Passed 'configs' parameter in not an array";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
			}

			// Check to make sure $configs has all of the needed elements.
			if (!isset($configs['type'])) {
        $msg = "SRA_Database::getDatabase: Failed - configs array doesn't contain a 'type' element";
        return SRA_Error::logError($msg, __FILE__, __LINE__);
			}

			if (!isset($configs['host'])) {
        $configs['host'] = SRA_DB_DEFAULT_HOST;
			}

			// switch on type.
			switch ($configs['type']) {
        case SRA_DB_TYPE_POSTGRESQL :
          include_once(SRA_LIB_DIR . '/sql/SRA_DatabasePostgreSql.php');
          $db = new SRA_DatabasePostgreSql();
          break;

        case SRA_DB_TYPE_MSSQL :
          include_once(SRA_LIB_DIR . '/sql/SRA_DatabaseMsSql.php');
          $db = new SRA_DatabaseMsSql();
          break;
          
        case SRA_DB_TYPE_SQLITE :
          include_once(SRA_LIB_DIR . '/sql/SRA_DatabaseSqLite.php');
          $db = new SRA_DatabaseSqLite();
          break;
          
        case SRA_DB_TYPE_ORACLE :
          include_once(SRA_LIB_DIR . '/sql/SRA_DatabaseOracle.php');
          $db = new SRA_DatabaseOracle();
          break;

        default:
          include_once(SRA_LIB_DIR . '/sql/SRA_DatabaseMySql.php');
          $db = new SRA_DatabaseMySql();
          break;
			}

			// Now set all of the properties.
			$db->_config = $configs;
      if (isset($configs['time-zone']) && SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone($configs['time-zone']))) {
        $db->_timeZone =& $tz;
      }
      else {
        $db->_timeZone =& SRA_Controller::getAppTimeZone();
      }
			$booleanFalse = SRA_DB_BOOL_FALSE_VALS;
			$booleanTrue = SRA_DB_BOOL_TRUE_VALS;
			if (isset($configs['bool-false'])) {
				$booleanFalse = $configs['bool-false'];
			}
			if (isset($configs['bool-true'])) {
				$booleanTrue = $configs['bool-true'];
			}
			$db->_boolFalseVals = SRA_Database::getBooleanArray($booleanFalse);
			$db->_boolTrueVals = SRA_Database::getBooleanArray($booleanTrue);
			$db->_boolFalse = SRA_DB_BOOL_FALSE;
			$db->_boolTrue = SRA_DB_BOOL_TRUE;
			$db->_readOnlyDb = isset($configs['read-only-db']) ? $configs['read-only-db'] : NULL;
			if (isset($configs['bool-false-rep'])) {
				$tmp = SRA_Database::getBooleanArray($configs['bool-false-rep']);
				if (count($tmp)) {
					$db->_boolFalse = $tmp[0];
				}
			}
			if (isset($configs['bool-true-rep'])) {
				$tmp = SRA_Database::getBooleanArray($configs['bool-true-rep']);
				if (count($tmp)) {
					$db->_boolTrue = $tmp[0];
				}
			}

			$db->_inTransaction = FALSE;
      
      return $db;
		}
		// }}}
}
// }}}

?>
