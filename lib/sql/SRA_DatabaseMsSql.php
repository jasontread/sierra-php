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
 * Whether or not the mssql server supports the LIMIT/OFFSET sql syntax
 * @type   boolean
 * @access public
 */
define('SRA_DATABASE_MSSQL_SUPPORTS_LIMIT', FALSE);
// }}}

// {{{ Includes
// }}}

// {{{ SRA_DatabaseMsSql
/**
 * This class extends from the abstract SRA_Database class. It is utilized to
 * handle database interraction with a MS Sql server (SQL Server). In
 * order for sql server access to function tcp/ip connectivity must be
 * activated in MS SQL, using "Server Network SRA_Utility".  This class
 * utilizes the TDS protocol (Tabular Data Stream) to connect to sql
 * server using the native php sql server functions.
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_DatabaseMsSql extends SRA_Database
{
    // {{{ Properties
    // }}}

    // {{{ SRA_DatabaseMySql()
    /**
     * Empty constructor to keep PHP from calling parent constructor.
     *
     * @access  public
     */
    function SRA_DatabaseMsSql()
    {
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
    function _closeConn($conn)
    {
        mssql_close($conn);
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
    function commit($conn=NULL)
    {
			
        // NOTE: MsSql uses the sql statments
        // "BEGIN TRANSACTION; COMMIT TRANSACTION; ROLLBACK TRANSACTION;" to
        // control transactions.

        /*
         * The commit() command algorithim is this:
         * Step 1: SRA_DatabaseMsSql::commit();
         * Step 2: SRA_Database::processCommit();
         * Step 3: SRA_DatabaseMsSql::commit($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processCommit().
            $result = $this->processCommit();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::commit() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // Get the warning if there is one.
            ob_start();
            $result = mssql_query('COMMIT TRANSACTION', $conn);
            $warning = ob_get_contents();
            ob_end_clean();

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::commit(): SRA_Error: $warning";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
    }
    // }}}

    // {{{ convertBlob()
    /**
     * This method implements the parent convertBlob method. See SRA_Database
     * class api for info. MsSql encodes blobs using base64 encryption.
     * Consequently, all blob fields must be text fields, not binary fields.
     *
     * @param string $blob the blob value to convert.
     * @access  public
     * @return  string
     */
    function &convertBlob(&$blob)
    {
			if (!isset($blob)) { return 'NULL'; }
        // NOTE: Using the MsSql 'text' data type.
        return("'" . base64_encode($blob) . "'");
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
     * class api for info
     * @param float $value the float to convert.
     * @access  public
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function convertFloat($value)
    {
			if (!isset($value)) { return 'NULL'; }
        // NOTE: MsSql uses the float datatype

        // Check for number.
        if (!is_numeric($value))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::convertFloat(): Passed parameter in not numeric it's a ".gettype($value).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
        // NOTE: floatval() not implemented until PHP 4.2.0. Use cast.
        return("'" . (float)$value . "'");
    }
    // }}}

    // {{{ convertText()
    /**
     * This method implements the parent convertText method. See SRA_Database
     * class api for info
     * @param string $text the text to convert.
     * @access  public
     * @return  string
     */
    function convertText($text)
    {
			if (!isset($text)) { return 'NULL'; }
        // NOTE: MsSql uses the CHAR() or VARCHAR() or TEXT type for text.

        // Check for string.
        if (is_object($text))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::convertText(): Passed parameter in not a string it's a ".gettype($text).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        return("'" . str_replace("'", "''", $text) . "'");
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
     * @param  string $query the query to execute. See SRA_Database::execute
     * method api for more info.
     * @param  string $incCol the name of the db managed incremental
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
         * Step 1: SRA_DatabaseMsSql::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabaseMsSql::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            $execute_set = $this->processUpdate($query, $incCol, $errorLevel);
            if (SRA_Error::isError($execute_set))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::execute() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }

            return($execute_set);
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // Get the warning if there is one.
            ob_start();
            $result = mssql_query($query, $conn);
            $warning = ob_get_contents();
            ob_end_clean();

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::execute(): $warning : $query";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            // Get the rows affected.
            // Get the warning if there is one.
            ob_start();
            $affected_result = mssql_query("SELECT @@ROWCOUNT", $conn);
            $warning = ob_get_contents();
            ob_end_clean();
            if ($affected_result === FALSE)
                {
                    // SRA_Error.
                    $msg = "SRA_DatabaseMsSql::execute(): SRA_Error SELECTing @@ROWCOUNT : $warning";
                    return(SRA_Error::logError($msg, __FILE__, __LINE__));
                }

            $affected_row = mssql_fetch_row($affected_result);

            // Create an SRA_ExecuteSet.
            $xs = new SRA_ExecuteSet($affected_row[0]);

            // Check for incCol and INSERT
            if ($incCol)
            {
                // Retrieve the IDENTITY field value from the last query and add
                // it to the SRA_ExecuteSet.

                // Get the warning if there is one.
                ob_start();
                $current_result = mssql_query("SELECT @@IDENTITY AS $incCol", $conn);
                $warning = ob_get_contents();
                ob_end_clean();

                if ($current_result === FALSE)
                {
                    // SRA_Error.
                    $msg = "SRA_DatabaseMsSql::execute(): SRA_Error getting incCol using SELECT @@IDENTITY AS $incCol ".$warning;
                    return(SRA_Error::logError($msg, __FILE__, __LINE__));
                }

                $current_row = mssql_fetch_row($current_result);

                $xs->setSequenceValue($current_row[0]);
            }

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
		 * @param int $limit (See SRA_Database::fetch method api for more info). 
		 * 					LIMIT and OFFSET are not supported by mssql, so this behavior 
		 * 					is implemented programatically
		 * @param int $offset (See SRA_Database::fetch method api for more info)
     * @param int $errorLevel the error level for the query. (See
     * SRA_Database::fetch method api for more info).
     * @access  public
     * @return SRA_ResultSet
     */
    function &fetch($query, $types=NULL, $limit=FALSE, $offset=FALSE, $errorLevel=SRA_ERROR_PROBLEM)
    {
        $conn = $this->_getAppDbConnection($query, TRUE);

        if (SRA_Error::isError($conn))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::fetch() Failed to _getAppDbConnection().";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
				
				// add limit and offset
				if (SRA_DATABASE_MSSQL_SUPPORTS_LIMIT) {
					$baseQuery = $query;
					$query = SRA_Database::applyLimitAndOffset($query, $limit, $offset);
				}

        // Perform query.
        // Get the warning if there is one.
        ob_start();
        $result = mssql_query($query, $conn);
        $warning = ob_get_contents();
        ob_end_clean();

        if (!$result)
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::fetch(): $warning : $query";
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
        $num_cols = mssql_num_fields($result);

				$recCursor = 0;
        while ($row = mssql_fetch_row($result))
        {
					$recCursor++;
					// check offset
					if (!SRA_DATABASE_MSSQL_SUPPORTS_LIMIT && $offset > 0 && $recCursor < ($offset + 1)) {
						continue;
					}
					// check limit
					if (!SRA_DATABASE_MSSQL_SUPPORTS_LIMIT && $limit > 0 && $recCursor > $limit) {
						break;
					}
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
                        // NOTE: blob types are stored as base64 encoded text.
                        $row[$j] = base64_decode($row[$j]);
                    break;

                    case SRA_DATA_TYPE_BOOLEAN :
                    	$row[$j] = $this->unconvertBoolean($row[$j]);
											break;

                    case SRA_DATA_TYPE_TIME :
                    case SRA_DATA_TYPE_DATE :
                        // NOTE: Date is retrieved from mssql with a time of 12:00am.
                        if ($row[$j])
                        {
                            $row[$j] = new SRA_GregorianDate($row[$j], $type == SRA_DATA_TYPE_TIME ? $this->_timeZone : NULL);
                            if ($type == SRA_DATA_TYPE_DATE) { $row[$j]->setDateOnly(TRUE); }
                        }
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
                        $msg = "SRA_DatabaseMsSql::fetch(): Unknown type, $type, in types array. Casting as String.";
                        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
                        $row[$j] = (string)$row[$j];

                }
              }
            }

            $fs->add($row);
        }

        // Return.
        return($fs);
    }
    // }}}

    // {{{ getNextSequence()
    /**
     * This method implements the parent getNextSequence method. See SRA_Database class api for info.
     *
     * @param string $sequence the name of the sequence to return the next value for.
     * @param	int $errorLevel the query error logging level. See SRA_Database::getNextSequence method api
     * for more info
     * @param	object $conn the database connection object to perform the sequence query on. If null
     * (default) this method will simply return SRA_Database::processNextSequence. Otherwise it will attempt
     * to perform the query using the specified connection. See SRA_Database::getNextSequence api for more info.
     * @access  public
     * @return  int
     */
    function getNextSequence($sequence, $errorLevel = SRA_ERROR_PROBLEM, $conn = NULL)
    {
        /*
         * The execute() command algorithim is this:
         * Step 1: SRA_DatabaseMsSql::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabaseMsSql::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            return $this->processNextSequence($sequence, $errorLevel);
        }
        else
        {
            // TODO

            /*
            // Step 3:
            // There's a $conn. Execute query.
            $result = pg_query($conn, "SELECT next_val('$sequence')");

            if (!$result)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::getNextSequence: Failed - ". pg_last_error($conn)."$query";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            if (pg_num_rows($result))
            {
                $row = pg_fetch_array($result, 0, PGSQL_NUM);
                return (int) $row[0];
            }
            else
            {
                $msg = "SRA_DatabaseMsSql::getNextSequence: Failed - Sequence call did not return a value.";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }
            */
            return(SRA_Error::logError("SRA_DatabaseMsSql::getNextSequence: Failed - Not yet implemented", __FILE__, __LINE__, $errorLevel));
        }
    }
    // }}}
	
    // {{{ importFile()
    /**
     * Implements the SRA_Database::importFile method. See api for this method for more info. This method returns 
	 * an SRA_Error object if it is not implemented by the child SRA_Database class.
	 * 
	 * @param string $fileName the name of the file to import. This file must be delimited with the same 
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
			$msg = "SRA_DatabaseMsSql::importFile(): This method is not implemented.";
			return(SRA_Error::logError($msg, __FILE__, __LINE__));
		}
		else
		{
			$msg = "SRA_DatabasePostgreSql::importFile: Failed - Invalid fileName or tableName parameters.";
			return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
		}
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
        // NOTE: Check all parameters because mssql_connect() crashes php, if
        // any of the server, username or password fields are blank.
        if (   trim($config['host'])    == ''
            || trim($config['user']) == ''
            || trim($config['password'])  == ''
            )
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::_openConn(): Cannot call mssql_connect() with a blank parameter.";
            $msg .= " server={$config['host']}, user:={$config['user']}, password={$config['password']}.";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        // All connection parameters are good. Connect!
        ob_start();
        $conn = mssql_connect($config['host'], $config['user'], $config['password']);
        $warning = ob_get_contents();
        ob_end_clean();

        if (!$conn)
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::_openConn(): mssql_connect() failed: $warning";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        if (!mssql_select_db($config['name'], $conn))
        {
            // SRA_Error.
            $msg = "SRA_DatabaseMsSql::_openConn(): mssql_select_db() failed select db, {$config['name']}.";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

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
     * SRA_Database::processRollback. Otherwise it will attempt to rollback
     * the transaction using the specified connection.
     * @access  public
     * @return  void
     */
    function rollback($conn=NULL)
    {
			
        // NOTE: MsSql uses the sql statments
        // "BEGIN TRANSACTION; COMMIT TRANSACTION; ROLLBACK TRANSACTION;" to
        // control transactions.

        /*
         * The rollback() command algorithim is this:
         * Step 1: SRA_DatabaseMsSql::rollback();
         * Step 2: SRA_Database::processRollback();
         * Step 3: SRA_DatabaseMsSql::rollback($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processRollback().
            $result = $this->processRollback();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::rollback() SRA_Error returned from processRollback().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // Get the warning if there is one.
            ob_start();
            $result = mssql_query('ROLLBACK TRANSACTION', $conn);
            $warning = ob_get_contents();
            ob_end_clean();

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::rollback(): SRA_Error: $warning";
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
    function startTransaction($conn=NULL)
    {
			
        // NOTE: MsSql uses the sql statments
        // "BEGIN TRANSACTION; COMMIT TRANSACTION; ROLLBACK TRANSACTION;" to
        // control transactions.

        /*
         * The startTransaction() command algorithim is this:
         * Step 1: SRA_DatabaseMsSql::startTransaction();
         * Step 2: SRA_Database::startTransactions();
         * Step 3: SRA_DatabaseMsSql::startTransaction($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->startTransactions().
            $result = $this->startTransactions();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::startTransaction() SRA_Error returned from startTransactions().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // Get the warning if there is one.
            ob_start();
            $result = mssql_query('BEGIN TRANSACTION', $conn);
            $warning = ob_get_contents();
            ob_end_clean();

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseMsSql::startTransaction(): SRA_Error: $warning";
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
	function getColumnDefinition(& $table, & $column, $dbRefIntegrity = TRUE, $addCheckConstraint = TRUE) {
		$definition = parent::getColumnDefinition($table, $column, $dbRefIntegrity, $addCheckConstraint);
		
		// sequence types
		if ($column->isSequence()) {
			$definition = str_replace('AUTO_INCREMENT', 'IDENTITY(1,1)', $definition);
		}
		
		return $definition;
	}
	// }}}
  
  
    // {{{ isValid()
    /**
     * Static method that returns TRUE if the object parameter references a
     * valid SRA_DatabaseMsSql object.
     *
     * @param   object $object the object to validate.
     * @access  public
     * @return  boolean
     */
    function isValid($object) {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_databasemssql' || is_subclass_of($object, 'SRA_DatabaseMsSql')));
    }
    // }}}

}
// }}}

?>
