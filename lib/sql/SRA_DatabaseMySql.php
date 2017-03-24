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
 * the max # of characters allowed for a CHAR column to be used instead of 
 * VARCHAR by default (in order to reduce table fragmentation caused by lots of 
 * small varchar columns)
 */
define('SRA_DATABASE_MYSQL_USE_CHAR_MAX_LEN', 64);

/**
 * whether or not to use the mysql_pconnect option when connecting to the 
 * database
 * @type boolean
 */
if (!defined('SRA_DATABASE_MYSQL_USE_PCONNECT')) define('SRA_DATABASE_MYSQL_USE_PCONNECT', FALSE);
// }}}

// {{{ Includes
// }}}

// {{{ SRA_DatabaseMySql
/**
 * This class extends from the abstract SRA_Database class. It is utilized to
 * handle database interraction with a MySQL server. To use the mysql_pconnect 
 * option, simply define the constant SRA_DATABASE_MYSQL_USE_PCONNECT and set it 
 * equal to TRUE
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_DatabaseMySql extends SRA_Database {
    // {{{ Properties
    // }}}

    // {{{ SRA_Database()
    /**
     * Empty constructor to keep PHP from calling parent constructor.
     *
     * @access  public
     */
    function SRA_DatabaseMySql() {
        // NOTE: Empty constructor to keep PHP from calling parent constructor.
    }
    // }}}

    // {{{ _closeConn()
    /**
     * This method closes one database connection using the appropriate
     * database close command. It is called by the parent close method.
     *
     * @param object $conn the database connection to close.
     * @access  protected
     * @return  void
     */
    function _closeConn($conn) {
        function_exists('mysqli_connect') ? mysqli_close($conn) : mysql_close($conn);
    }
    // }}}

    // {{{ commit()
    /**
     * This method implements the parent commit method. See SRA_Database class
     * api for info.
     *
     * @param object $conn the database connection object to commit a
     * transaction for. If null (default) this method will simply return
     * SRA_Database::processCommit. Otherwise it will attempt to commit the
     * transaction using the specified connection.
     * @access  public
     * @return  void
     */
    function commit($conn=null) {
			
        // NOTE: MySql uses the sql statments
        // "BEGIN; END; ROLLBACK;" to control transactions.

        /*
         * The commit() command algorithim is this:
         * Step 1: SRA_DatabaseMySql::commit();
         * Step 2: SRA_Database::processCommit();
         * Step 3: SRA_DatabaseMySql::commit($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processCommit().
            $result = $this->processCommit();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::commit() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // NOTE: This query returns a result even if the db isn't
            // currently in a transaction.
            // Meaning this query has no effect unless the db is currently
            // in a transaction.
            $result = function_exists('mysqli_connect') ? mysqli_query($conn, 'COMMIT') : mysql_query('COMMIT', $conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::commit(): SRA_Error: ". (function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn));
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
				
    }
    // }}}

    // {{{ convertBlob()
    /**
     * This method implements the parent convertBlob method. See SRA_Database
     * class api for info.
     *
     * @param string $blob the blob value to convert.
     * @access  public
     * @return  string
     */
    function &convertBlob(&$blob) {
			if (!isset($blob)) { return 'NULL'; }
        // NOTE: This assumes the  blob or text datatype is being used.
				return "'" . (function_exists('mysqli_connect') ? mysqli_escape_string($blob) : mysql_escape_string($blob)) . "'";
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
      $msg = 'SRA_DatabaseMySql::convertDate: Passed parameter is not a valid SRA_GregorianDate object';
      return(SRA_Error::logError($msg, __FILE__, __LINE__));
    }
    return "'" . $dateTime->format($dateTime->isDateOnly() ? SRA_DB_DATE_FORMAT : SRA_DB_TIME_FORMAT) . "'";
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

    // {{{ convertFloat()
    /**
     * This method implements the parent convertFloat method. See SRA_Database
     * class api for info.
     *
     * @param float $value the float to convert.
     * @access  public
     * @return  string
     */
    function convertFloat($value) {
			if (!isset($value)) { return 'NULL'; }
        // NOTE:  uses the FLOAT type for floats.

        // Check for number.
        if (!is_numeric($value))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMySql::convertFloat(): Passed parameter in not numeric it's a ".gettype($value).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
        // NOTE: floatval() not implemented until PHP 4.2.0. Use cast.
        return("'" . (float)$value . "'");
    }
    // }}}

    // {{{ convertText()
    /**
     * This method implements the parent convertText method. See SRA_Database
     * class api for info.
     *
     * @param string $text the text to convert.
     * @access  public
     * @return  string
     */
    function convertText($text) {
			if (!isset($text)) { return 'NULL'; }
        // NOTE:  uses the CHAR() or VARCHAR() type for text.

        // Check for string.
        if (is_object($text))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMySql::convertText(): Passed parameter in not a string it's a ".gettype($text).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        return("'". (function_exists('mysqli_connect') ? mysqli_escape_string($text) : mysql_escape_string($text)) . "'");
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

    // {{{ execute()
    /**
     * This method implements the parent execute method. See SRA_Database class
     * api for info.
     *
     * @param string $query the query to execute. See SRA_Database::execute
     * method api for more info.
     * @param string $incCol the name of the db managed incremental
     * column to retrieve and store in the SRA_ExecuteSet return object the
     * incremental value that was used (applied only to INSERT queries
     * performed on tables that contain db managed incremental columns).
     * See SRA_Database::execute method api for more info.
     * @param int $errorLevel the query error logging level. See
     * SRA_Database::execute method api for more info.
     * @param object $conn the database connection object to perform
     * the query on. If null (default) this method will simply return
     * SRA_Database::processUpdate. Otherwise it will attempt to perform the
     * query using the specified connection.
     * @access  public
     * @return SRA_ExecuteSet
     */
    function &execute($query, $incCol=FALSE, $errorLevel=SRA_ERROR_PROBLEM, $conn=NULL)
    {
        /*
         * The execute() command algorithim is this:
         * Step 1: SRA_Database::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_Database::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            $execute_set = $this->processUpdate($query, $incCol, $errorLevel);
            if (SRA_Error::isError($execute_set))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::execute() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            return($execute_set);
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.
						// echo "$query<br />\n";
            //return new SRA_ExecuteSet(1);
						function_exists('mysqli_connect') ? mysqli_select_db($conn, $this->_dbName): mysql_select_db($this->_dbName, $conn);
            $result = function_exists('mysqli_connect') ? mysqli_query($conn, $query) : mysql_query($query, $conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::execute(): ".(function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn))." $query";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            // Create an SRA_ExecuteSet.
            $xs = new SRA_ExecuteSet(function_exists('mysqli_connect') ? mysqli_affected_rows($conn) : mysql_affected_rows($conn));

            // Check for incCol and INSERT
            if ($incCol)
            {
                // Retrieve auto generated id from the last query and add
                // it to the SRA_ExecuteSet.

                $xs->setSequenceValue(function_exists('mysqli_connect') ? mysqli_insert_id($conn) : mysql_insert_id($conn));
            }
            // echo "$query " . mysql_affected_rows($conn) . "<br />\n";
            return($xs);
        }
    }
    // }}}

    // {{{ fetch()
    /**
     * This method implements the parent fetch method. See SRA_Database class
     * api for info.
     *
     * @param string $query the query to fetch. (See SRA_Database::fetch
     * method api for more info).
     * @param array $types the query return types. (See
     * SRA_Database::fetch method api for more info).
		 * @param int $limit (See SRA_Database::fetch method api for more info)
		 * @param int $offset (See SRA_Database::fetch method api for more info)
     * @param int $errorLevel the error level for the query. (See
     * SRA_Database::fetch method api for more info).
     * @access  public
     * @return  SRA_ResultSet
     */
    function &fetch($query, $types=NULL, $limit=FALSE, $offset=FALSE, $errorLevel=SRA_ERROR_PROBLEM)
    {
				// Check for cache
				if ($results =& $this->_fetchInCache($query)) {
					return $results;
				}

        $conn = $this->_getAppDbConnection($query, TRUE);
        function_exists('mysqli_connect') ? mysqli_select_db($this->_getAppDbConnection(NULL, TRUE), $this->_dbName) : mysql_select_db($this->_dbName, $this->_getAppDbConnection(NULL, TRUE));

        if (SRA_Error::isError($conn))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMySql::fetch() Failed to _getAppDbConnection().";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
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
				// echo "$query<br />\n";
				$query = SRA_Database::applyLimitAndOffset($query, $limit, $offset);
        // Perform query.
        $result = function_exists('mysqli_connect') ? mysqli_query($conn, $query, MYSQLI_USE_RESULT) : mysql_unbuffered_query($query, $conn);

        if (!$result)
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMySql::fetch: ". (function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn))." Query: $query";
            // NOTE: This SRA_Error uses $errorLevel.
            return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
        }

        // Lowercase the types. Used in switch().
        if ($types) {
          $keys = array_keys($types);
          foreach($keys as $key)
          {
              $types[$key] = strtolower($types[$key]);
          }
        }

        // New SRA_ResultSet.
        $fs = new SRA_ResultSet($this, $query, $baseQuery, $limit, $offset);

        // Cast results.
        $num_cols = function_exists('mysqli_connect') ? mysqli_num_fields($result) : mysql_num_fields($result);

        while ($row = function_exists('mysqli_connect') ? mysqli_fetch_row($result) : mysql_fetch_row($result))
        {
            // Switch on each col.
            for ($j = 0; $j < $num_cols; ++$j)
            {
              if ($row[$j] === NULL) {
                $row[$j] = NULL;
              }
              else {
                // This is where the cast happens.
                $type = $types && isset($types[$j]) ? $types[$j] : SRA_DATA_TYPE_STRING;
                switch ($type)
                {
                    case SRA_DATA_TYPE_BLOB :
                        // NOTE: Should be fine coming out of db.
                        //$row[$j] = stripslashes($row[$j]);
                    break;

                    case SRA_DATA_TYPE_BOOLEAN :
											$row[$j] = $this->unconvertBoolean($row[$j]);
											break;

                    case SRA_DATA_TYPE_TIME :
                    case SRA_DATA_TYPE_DATE :
                        if ($row[$j])
                            $row[$j] = strstr($row[$j], '0000') ? NULL : new SRA_GregorianDate($row[$j], $this->_timeZone);
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

                    default: // Cast to string if type is unknown
                        $msg = "SRA_DatabaseMySql::fetch(): Unknown type, $type, in types array. Casting as String.";
                        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
                        $row[$j] = (string)$row[$j];

                }
              }
            }
            $fs->add($row);
        }
        if (function_exists('mysqli_connect')) mysqli_free_result($result);
        
				// add to cache
				$this->_addFetchToCache($query, $fs);
				
        // Return.
        return($fs);
    }
    // }}}

    // {{{ getNextSequence()
    /**
     * This method implements the parent getNextSequence method. See
     * SRA_Database class api for info.
     *
     * Because MySql does not utilize sequences, for this method to function,
     * the sequence name passed must correspond to a single column, integer,
     * auto-incrementing data field. So, for example, if this method was
     * called with the parameter "_sequence_my_sequence", then a table must
     * exist in the mysql database named "_sequence_my_sequence" with a single
     * auto-incrementing column. To get the next sequence, this method will
     * simply insert a value into the table (i.e. "INSERT INTO
     * _sequence_my_sequence (0)") and return the value of the incremental
     * column.
     *
     * @param string $sequence the name of the sequence to return the
     * next value for.
     * @param int $errorLevel the query error logging level. See
     * SRA_Database::getNextSequence method api for more info.
     * @param object $conn the database connection object to perform
     * the sequence query on. If null (default) this method will simply
     * return SRA_Database::processNextSequence. Otherwise it will attempt to
     * perform the query using the specified connection. See
     * SRA_Database::getNextSequence api for more info.
     * @access  public
     * @return  int
     */
    function getNextSequence($sequence, $errorLevel=SRA_ERROR_PROBLEM, $conn=null)
    {
        /*
         * The execute() command algorithim is this:
         * Step 1: SRA_DatabaseMySql::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabaseMySql::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            return $this->processNextSequence($sequence, $errorLevel);
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.
            $result = function_exists('mysqli_connect') ? mysqli_query($conn, "INSERT INTO $sequence VALUES (0)") : mysql_query("INSERT INTO $sequence VALUES (0)", $conn);

            if (!$result)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::getNextSequence: Failed - ". (function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn)) .", $sequence";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            return(function_exists('mysqli_connect') ? mysqli_insert_id($conn) : mysql_insert_id($conn));
        }
    }
    // }}}
	
    // {{{ importFile()
    /**
     * Implements the SRA_Database::importFile method. See api for this method for more info. This method returns 
	 * an SRA_Error object if it is not implemented by the child SRA_Database class.
	 * 
	 * @param	string $fileName the name of the file to import. This file must be delimited with the same 
	 *			number of columns as are in the table.
	 * @param	string $tableName the name of the table to insert the data into.
	 * @param	boolean $deleteFile whether or not the file should be deleted if the import is successful. 
	 * 			By default this value is true.
	 * @param	string $delimiter the delimiter to use for the file import. The default value for this is a 
	 * 			comma. Note: If a delimiter occurs in a data column, it should be escaped using backslash.
     *
     * @access  public
     * @return  boolean
     */
    function importFile($fileName, $tableName, $deleteFile=true, $delimiter=",")
    {
		if (isset($fileName) && file_exists($fileName) && is_string($tableName))
		{
			// TODO
			$msg = "SRA_DatabaseMySql::importFile(): This method is not implemented.";
			return(SRA_Error::logError($msg, __FILE__, __LINE__));
		}
		else
		{
			$msg = "SRA_DatabasePostgreSql::importFile: Failed - Invalid fileName or tableName parameters.";
			return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
		}
    }
    // }}}
    
    // {{{ selectDb
    /**
     * this method can be used to change the currently selected database. it 
     * returns TRUE on success, FALSE otherwise
     * @param string $newDb the name of the new database to select
     * @access public
     * @return boolean
     */
    function selectDb($newDb) {
      $this->_dbName = $newDb;
      function_exists('mysqli_connect') ? mysqli_select_db($this->_getAppDbConnection(), $this->_dbName) : mysql_select_db($this->_dbName, $this->_getAppDbConnection());
      if ($this->_readOnlyDb) {
        function_exists('mysqli_connect') ? mysqli_select_db($this->_getAppDbConnection(NULL, TRUE), $this->_dbName) : mysql_select_db($this->_dbName, $this->_getAppDbConnection(NULL, TRUE));
      }
      return TRUE;
    }
    // }}}

    // {{{ _openConn()
    /**
     * This method is used to open one connection to a database server. It
     * is called by the SRA_Database parent class in order to open needed
     * database connections. It returns either a connection object (if
     * successful) or an SRA_Error object if not.
     *
     * @param array $config the connection data to use for the
     * connection. (See SRA_Database::_openConn api for more info).
     * @access  protected
     * @return  object
     */
    function _openConn($config)
    {
        if (function_exists('mysqli_connect')) {
          $conn = mysqli_connect($config['server'] . ($config['port'] ? ':' . $config['port'] : ''), $config['user'], $config['password']);
        }
        else if (SRA_DATABASE_MYSQL_USE_PCONNECT) {
          $conn = mysql_pconnect($config['server'] . ($config['port'] ? ':' . $config['port'] : ''), $config['user'], $config['password']);
        }
        else {
          $conn = mysql_connect($config['server'] . ($config['port'] ? ':' . $config['port'] : ''), $config['user'], $config['password']);
        }
        
        if (!$conn)
        {
            // SRA_Error.
            $msg = 'SRA_DatabaseMySql::_openConn(): mysql_connect() failed - server: ' . $config['server'] . ' user: ' . $config['user'] . ' password: ' . $config['password'];
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
        if (!(function_exists('mysqli_connect') ? mysqli_select_db($conn, $config['name']) : mysql_select_db($config['name'], $conn)))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMySql::_openConn(): mysql_select_db() failed select db, {$config['name']}.";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
        $this->_dbName = $config['name'];
        
        return($conn);
    }
    // }}}

    // {{{ rollback()
    /**
     * This method implements the parent rollback method. See SRA_Database
     * class api for info.
     *
     * @param object $conn the database connection object to rollback a
     * transaction for. If null (default) this method will simply return
     * SRA_Database::processRollback. Otherwise it will attempt to rollback the
     * transaction using the specified connection.
     * @access  public
     * @return  void
     */
    function rollback($conn=null)
    {
			
        // NOTE: MySql uses the sql statments
        // "BEGIN; END; ROLLBACK;" to control transactions.

        /*
         * The rollback() command algorithim is this:
         * Step 1: SRA_DatabaseMySql::rollback();
         * Step 2: SRA_Database::processRollback();
         * Step 3: SRA_DatabaseMySql::rollback($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processRollback().
            $result = $this->processRollback();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::rollback() SRA_Error returned from processRollback().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // NOTE: This query returns a result even if the db isn't
            // currently in a transaction.
            // Meaning this query has no effect unless the db is currently
            // in a transaction.

            $result = function_exists('mysqli_connect') ? mysqli_query($conn, 'ROLLBACK') : mysql_query('ROLLBACK', $conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::rollback(): SRA_Error: ". (function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn));
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
    }
    // }}}

    // {{{ startTransaction()
    /**
     * This method implements the SRA_Database::startTransaction method.  See
     * this method's api for more info.
     *
     * @param object $conn the database connection to start the
     * transaction for. If null (default) this method will simply return
     * SRA_Database::startTransactions.
     * @access  public
     * @return  void
     */
    function startTransaction($conn=null)
    {
			
        // NOTE: MySql uses the sql statments
        // "BEGIN; END; ROLLBACK;" to control transactions.

        /*
         * The startTransaction() command algorithim is this:
         * Step 1: SRA_Database::startTransaction();
         * Step 2: SRA_Database::startTransactions();
         * Step 3: SRA_Database::startTransaction($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->startTransactions().
            $result = $this->startTransactions();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::startTransaction() SRA_Error returned from startTransactions().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // NOTE: This query returns a result even if the db is
            // currently in a transaction.

            // PHP and MySql ignore cummulative startTransaction() calls.
            // Meaning, if you call startTransaction(), run some queries and
            // then call startTransaction() again, the second startTransaction()
            //  is ignored. You only need to call commit() once and all of the
            // queries beginnig from the first startTransaction() call are
            // committed.

            $result = function_exists('mysqli_connect') ? mysqli_query($conn, 'BEGIN') : mysql_query('BEGIN', $conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMySql::startTransaction(): SRA_Error: ". (function_exists('mysqli_connect') ? mysqli_error($conn) : mysql_error($conn));
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
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
	function getColumnDefinition(& $table, & $column, $dbRefIntegrity = TRUE, $addCheckConstraint = FALSE) {
		$definition = parent::getColumnDefinition($table, $column, $dbRefIntegrity, $addCheckConstraint);
    $type = $column->getType();
    // blob
    $maxFileSize = $column->hasValidator('maxFileSize') ? $column->getVars('maxFileSize') : ($column->hasValidator('maxLength') ? $column->getVars('maxLength') : 0);
    if ($type == SRA_DATA_TYPE_BLOB && $maxFileSize >= pow(2, 24)) {
      $definition = str_replace('BLOB', 'LONGBLOB', $definition);
    }
    else if ($type == SRA_DATA_TYPE_BLOB && $maxFileSize >= pow(2, 16)) {
      $definition = str_replace('BLOB', 'MEDIUMBLOB', $definition);
    }
    // char
    else if ($type == SRA_DATA_TYPE_STRING && $column->hasValidator('maxLength') && $column->getVars('maxLength') <= SRA_DATABASE_MYSQL_USE_CHAR_MAX_LEN) {
      $maxLen = $column->getVars('maxLength');
      $definition = str_replace("VARCHAR(${maxLen})", "CHAR(${maxLen})", $definition);
    }
    // text
    else if ($type == SRA_DATA_TYPE_STRING && $column->hasValidator('maxLength') && $column->getVars('maxLength') > 255) {
      $maxLen = $column->getVars('maxLength');
      $definition = str_replace("VARCHAR(${maxLen})", ($maxLen >= pow(2,24) ? 'LONGTEXT' : ($maxLen >= pow(2,16) ? 'MEDIUMTEXT' : 'TEXT')), $definition);
    }
    // timestamp
    else if ($type == SRA_DATA_TYPE_TIME) {
      $definition = str_replace('TIMESTAMP', 'DATETIME', $definition);
    }
    
		return $definition;
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
			$def = parent::getTableDefinition($table, $dbRefIntegrity);
			
			if ($dbRefIntegrity) {
				$columns =& $table->getColumns();
				$keys = array_keys($columns);
				foreach ($keys as $key) {
					if ($columns[$key]->getReferences()) {
						$def .= $def ? ",\n" : '';
						$def .= '  CONSTRAINT fk' . $columns[$key]->getName() . ' FOREIGN KEY (' . $columns[$key]->getName() . ') REFERENCES ' . $columns[$key]->getReferences();
						$def .= $columns[$key]->isOnDeleteCascade() ? ' ON DELETE CASCADE' : ' ON DELETE SET NULL';
					}
				}
			}
			
			return $def;
    }
    // }}}
    
    
    // {{{ isValid()
    /**
     * Static method that returns TRUE if the object parameter references a
     * valid SRA_DatabaseMySql object.
     *
     * @param   object $object the object to validate.
     * @access  public
     * @return  boolean
     */
    function isValid($object) {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_databasemysql' || is_subclass_of($object, 'SRA_DatabaseMySql')));
    }
    // }}}

}
// }}}

?>
