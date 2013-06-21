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

// {{{ SRA_DatabaseSqLite
/**
 * database class for interracting with an sqlite database. NOTE: this database 
 * class works ONLY with PHP PDO currently
 * @author Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_DatabaseSqLite extends SRA_Database {
  
  // {{{ SRA_DatabaseSqLite
  /**
   * empty constructor to keep PHP from calling parent constructor
   * @access  public
   */
  function SRA_DatabaseSqLite() { }
  // }}}

  // {{{ _closeConn()
  /**
   * closes a sqlite database connection
   * @param PDO $conn the connection to close
   * @access private
   * @return void
   */
  function _closeConn($conn) {
    $conn = NULL;
  }
  // }}}

  // {{{ commit
  /**
   * commits a database transaction. returns TRUE on success, FALSE otherwise
   * @param PDO $conn the database instance to commit
   * @access public
   * @return boolean
   */
  function commit($conn=null) {
    if ($conn === NULL) {
      $result = $this->processCommit();
      if (SRA_Error::isError($result)) {
        $msg = 'SRA_DatabaseSqLite::commit - Failed due to error from SRA_Database::processCommit';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
    }
    else {
      if ($conn->_sraInTransaction && $conn->commit()) {
        $conn->_sraInTransaction = FALSE;
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }
  // }}}

  // {{{ convertBlob
  /**
   * this method implements the parent convertBlob method. see SRA_Database 
   * class api for info
   * @param mixed $blob the value to convert
   * @access public
   * @return string
   */
  function &convertBlob(&$blob) {
    if (!isset($blob)) { return 'NULL'; }
    
    $conn = $this->_getAppDbConnection($query);
    
    if (!$conn || SRA_Error::isError($conn)) {
      $msg = "SRA_DatabaseSqLite::convertText - failed to _getAppDbConnection";
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    
    return $conn->quote($blob);
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
    if (!isset($dateTime)) { return 'NULL'; }
    if (!SRA_GregorianDate::isValid($dateTime)) {
      $msg = 'SRA_DatabaseSqLite::convertDate: Passed parameter is not a valid SRA_GregorianDate object';
      return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    return "'" . $dateTime->format($dateTime->isDateOnly() ? 'Ymd' : 'YmdHis') . "'";
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
    if (SRA_GregorianDate::isValid($dateTime)) {
      $tz =& $dateTime->getTimeZone();
      $dateTime->setTimeZone($this->_timeZone);
    }
    $time = $this->convertDate($dateTime);
    if (SRA_GregorianDate::isValid($dateTime)) {
      $dateTime->setTimeZone($tz);
    }
    return $time;
  }
  // }}}
  
  // {{{ convertFloat
  /**
   * this method implements the parent convertFloat method. see SRA_Database
   * class api for info
   * @param float $value the value to convert
   * @access public
   * @return float
   */
  function convertFloat($value) {
    if (!isset($value)) { return 'NULL'; }
    return $value;
  }
  // }}}
  
  // {{{ convertText
  /**
   * this method implements the parent convertText method. see SRA_Database
   * class api for info
   * @param string $text the text to convert
   * @access public
   * @return string
   */
  function convertText($text) {
    return $this->convertBlob($text);
  }
  // }}}
  
  // {{{ convertString
  /**
   * alias for convertText
   * @param string $str the string to convert
   * @access public
   * @return string
   */
  function convertString($str) {
    return $this->convertText($str);
  }
  // }}}
  
  // {{{ execute
  /**
   * this method implements the parent execute method. see SRA_Database class
   * api for info
   * @param string $query the query to execute. see SRA_Database::execute
   * method api for more info
   * @param boolean $incCol the name of the db managed incremental column to 
   * retrieve and store in the SRA_ExecuteSet return object the incremental 
   * value that was used (applied only to INSERT queries performed on tables 
   * that contain db managed incremental columns). see SRA_Database::execute 
   * method api for more info.
   * @param int $errorLevel the query error logging level. see
   * SRA_Database::execute method api for more info
   * @param PDO $conn the database connection object to perform the query on. if 
   * null (default) this method will simply return SRA_Database::processUpdate. 
   * otherwise it will attempt to perform the query using the specified 
   * connection
   * @access public
   * @return SRA_ExecuteSet
   */
  function &execute($query, $incCol=FALSE, $errorLevel=SRA_ERROR_PROBLEM, $conn=NULL) {
    if ($conn === NULL) {
      $xs =& $this->processUpdate($query, $incCol, $errorLevel);
      if (SRA_Error::isError($xs)) {
        $msg = 'SRA_DatabaseSqLite::execute - Failed because error returned from SRA_Database::processUpdate';
        return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
      }
      return $xs;
    }
    else {
      $count = $conn->exec($query);

      $xs = new SRA_ExecuteSet($count);

      if ($incCol) { $xs->setSequenceValue($conn->lastInsertId()); }
      
      return($xs);
    }
  }
  // }}}

  // {{{ fetch
  /**
   * this method implements the parent fetch method. See SRA_Database class api 
   * for info
   * @param string $query the query to fetch (see SRA_Database::fetch method api 
   * for more info)
   * @param array types the query return types. (see SRA_Database::fetch method 
   * api for more info)
   * @param int $limit (see SRA_Database::fetch method api for more info)
   * @param int $offset (see SRA_Database::fetch method api for more info)
   * @param int $errorLevel the error level for the query. (See 
   * SRA_Database::fetch method api for more info)
   * @access public
   * @return SRA_ResultSet
   */
  function &fetch($query, $types=NULL, $limit=NULL, $offset=NULL, $errorLevel=SRA_ERROR_PROBLEM) {
    // Check for cache
    if ($results =& $this->_fetchInCache($query)) { return $results; }
    
    $conn = $this->_getAppDbConnection($query, TRUE);
    
    if (!$conn || SRA_Error::isError($conn)) {
      $msg = "SRA_DatabaseSqLite::fetch - failed to _getAppDbConnection";
      return SRA_Error::logError($msg, __FILE__, __LINE__);
    }
    
    // add limit and offset
    $baseQuery = $query;
    if ($limit && $offset) {
      $query .= " LIMIT $offset, $limit";
    }
    else if ($limit) {
      $query .= " LIMIT $limit";
    }
    else if ($offset) {
      $query .= " LIMIT $offset, 1000000000";
    }
    $query = SRA_Database::applyLimitAndOffset($query, $limit, $offset);
    
    $result = $conn->query($query);
    
    if (!$result) {
      $msg = 'SRA_DatabaseSqLite::fetch: Query "' . $query . '" failed with errors: ' . implode(', ', $conn->errorInfo());
      return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
    }
    
    if ($types) {
      foreach(array_keys($types) as $key) { $types[$key] = strtolower($types[$key]); }
    }
    
    $rs = new SRA_ResultSet($this, $query, $baseQuery, $limit, $offset);
    
    while ($row =& $result->fetch(PDO::FETCH_NUM)) {
      $cols = $cols ? $cols : count($row);
      for ($j = 0; $j < $cols; ++$j) {
        $type = $types && isset($types[$j]) ? $types[$j] : SRA_DATA_TYPE_STRING;
        switch ($type) {
          case SRA_DATA_TYPE_BOOLEAN :
            $row[$j] = $this->unconvertBoolean($row[$j]);
            break;
          
          case SRA_DATA_TYPE_TIME :
          case SRA_DATA_TYPE_DATE :
            if ($row[$j]) { $row[$j] = new SRA_GregorianDate($row[$j], $this->_timeZone); }
            if ($type == SRA_DATA_TYPE_DATE && SRA_GregorianDate::isValid($row[$j])) { $row[$j]->setDateOnly(TRUE); }
            if ($type == SRA_DATA_TYPE_TIME && SRA_GregorianDate::isValid($row[$j])) $row[$j]->setTimeZone($this->_timeZone);
            break;
          case SRA_DATA_TYPE_FLOAT :
          case SRA_DATA_TYPE_INT :
            $row[$j] = $row[$j]*1;
            break;
          case SRA_DATA_TYPE_STRING :
            $row[$j] = $row[$j] . '';
            break;
        }
      }
      $rs->add($row);
    }

    // add to cache
    $this->_addFetchToCache($query, $rs);
    
    // Return.
    return $rs;
  }
  // }}}

  // {{{ getNextSequence
  /**
   * this method implements the parent getNextSequence method. see 
   * SRA_Database class api for info
   * @param string $sequence the name of the sequence to return the next value 
   * for
   * @param	int $errorLevel the query error logging level. see 
   * SRA_Database::getNextSequence method api for more info.
   * @param	PDO $conn the database connection object to perform the sequence 
   * query on. if null (default) this method will simply return 
   * SRA_Database::processNextSequence. otherwise it will attempt to perform 
   * the query using the specified connection. see 
   * SRA_Database::getNextSequence api for more info.
   * @access public
   * @return int
   */
  function getNextSequence($sequence, $errorLevel = SRA_ERROR_PROBLEM, $conn = NULL) {
    if ($conn === NULL) {
      return $this->processNextSequence($sequence, $errorLevel);
    }
    else if ($result = $conn->query("INSERT INTO $sequence VALUES (0)")) {
      return $conn->lastInsertId();
    }
    else {
      $msg = "SRA_DatabaseSqLite::getNextSequence: Failed for sequence '$sequency' due to the following errors: " . implode(', ', $conn->errorInfo());
      return SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel);
    }
  }
  // }}}

  // {{{ _openConn
  /**
   * this method is used to open one connection to a database server. it is 
   * called by the SRA_Database parent class in order to open needed database 
   * connections. It returns either a connection object (if successful) or an 
   * SRA_Error object if not
   * @param array $config the setup to use for the connection (see 
   * SRA_Database::_openConn api for more info). for sqlite databases, only the 
   * database name is used which should be the path to the database file
   * @return PDO
   */
  function _openConn($config) {
    if (isset($config['name']) && is_dir(dirname($config['name'])) && is_writable(dirname($config['name'])) && (!file_exists($config['name']) || is_writable($config['name']))) {
      return new PDO('sqlite:' . $config['name']);
    }
    else {
      $msg = 'SRA_DatabaseSqLite::_openConn: Failed - invalid database: ' . $config['name'];
      return SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel);
    }
  }
  // }}}

  // {{{ rollback
  /**
   * this method implements the parent rollback method. see SRA_Database class 
   * api for info
   * @param PDO $conn the database connection object to rollback a transaction 
   * for. if null (default) this method will simply return 
   * SRA_Database::processRollback. otherwise it will attempt to rollback the
   * transaction using the specified connection
   * @access public
   * @return boolean
   */
  function rollback($conn=null) {
    if ($conn === NULL) {
      $result = $this->processRollback();
      if (SRA_Error::isError($result)) {
        $msg = 'SRA_DatabaseSqLite::rollback - Failed due to error from SRA_Database::processRollback';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
    }
    else {
      if ($conn->_sraInTransaction && $conn->rollback()) {
        $conn->_sraInTransaction = FALSE;
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }
  // }}}

  // {{{ startTransaction
  /**
   * this method implements the SRA_Database::startTransaction method. see this 
   * method's api for more info
   * @param PDO $conn the database connection to start the transaction for. if 
   * null (default) this method will simply return 
   * SRA_Database::startTransactions.
   * @access public
   * @return boolean
   */
  function startTransaction($conn=null) {
    if ($conn === NULL) {
      $result = $this->startTransactions();
      if (SRA_Error::isError($result)) {
        $msg = 'SRA_DatabaseSqLite::startTransaction - Failed due to error from SRA_Database::startTransactions';
        return SRA_Error::logError($msg, __FILE__, __LINE__);
      }
    }
    else {
      if ($conn->beginTransaction()) {
        $conn->_sraInTransaction = TRUE;
        return TRUE;
      }
      else {
        $conn->_sraInTransaction = FALSE;
        return FALSE;
      }
    }
  }
  // }}}
		
		
	// {{{ getColumnDefinition()
	/**
	 * provides an sql statement defining the data type, constraints, and 
	 * referential integrity (optional) for a given SRA_SchemaColumn. this method 
	 * may be implemented by each of the underlying database types if the 
	 * default MYSQL implementation does not suffice
	 * @param SRA_SchemaTable $table the SRA_SchemaTable that the column belongs 
   * to
	 * @param SRA_SchemaColumn $column the SRA_SchemaColumn to create the 
   * definition for
	 * @param boolean $dbRefIntegrity whether or not referential integrity should 
	 * be enforced at the database layer (not supported in SQLite)
	 * @param boolean $addCheckConstraint whether or not to add a check constraint 
	 * to the column definition (not supported in SQLite)
	 * @access public
	 * @return string
	 */
	function getColumnDefinition(& $table, & $column, $dbRefIntegrity = TRUE, $addCheckConstraint = FALSE) {
		$definition = parent::getColumnDefinition($table, $column, $dbRefIntegrity, $addCheckConstraint);
		
		// sequence types
		if ($column->isSequence()) {
      $definition = str_replace('PRIMARY KEY', '', $definition);
			$definition = str_replace('AUTO_INCREMENT', 'PRIMARY KEY AUTOINCREMENT', $definition);
		}
    // boolean
    if ($column->getType() == SRA_DATA_TYPE_BOOLEAN) {
      $definition = str_replace('ENUM(' . $this->convertBoolean(TRUE) . ',' . $this->convertBoolean(FALSE) . ')', 'INTEGER', $definition);
    }
    // date/time
    if ($column->getType() == SRA_DATA_TYPE_DATE || $column->getType() == SRA_DATA_TYPE_TIME) {
      $definition = str_replace('DEFAULT 0', 'DEFAULT NULL', $definition);
      $definition = str_replace('TIMESTAMP', 'INTEGER', $definition);
      $definition = str_replace('DATE', 'INTEGER', $definition);
    }
    // float
    if ($column->getType() == SRA_DATA_TYPE_FLOAT) {
      $definition = str_replace('DOUBLE', 'REAL', $definition);
    }
    // integer
    if ($column->getType() == SRA_DATA_TYPE_INT) {
      $definition = str_replace('INT', 'INTEGER', $definition);
    }
    // string
    if ($column->getType() == SRA_DATA_TYPE_STRING) {
      $definition = str_replace('VARCHAR(' . $column->getVars('maxLength') . ')', 'TEXT', $definition);
    }
		return $definition;
	}
	// }}}
  
  
  // {{{ getTableDefinition
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
    $pkname = $table->getPrimaryKey();
    if ($pkname) $pk =& $table->getColumns(is_array($pkname) ? $pkname[0] : $pkname);
    return $pk && $pk->isSequence() ? '' : parent::getTableDefinition($table, $dbRefIntegrity);
  }
  // }}}
  
  
  // {{{ isValid
  /**
   * Static method that returns TRUE if the object parameter references a
   * valid SRA_DatabaseSqLite object
   * @param  object $object the object to validate.
   * @access public
   * @return boolean
   */
  function isValid($object) {
    return is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && 
           (strtolower(get_class($object)) == 'sra_databasesqlite' || is_subclass_of($object, 'SRA_DatabaseSqLite'));
  }
  // }}}

}
// }}}

?>
