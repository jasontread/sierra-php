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
 * Constant that identifies the default date/time format that should be used
 * when inserting into timestamp type columns
 * @type   String
 * @access public
 */
define('SRA_DB_POSTGRESQL_TIME_FORMAT', 'Y-m-d H:i:sP');
// }}}

// {{{ Includes
// }}}

// {{{ SRA_DatabasePostgreSql
/**
 * This class extends from the abstract SRA_Database class. It is utilized to
 * handle database interraction with a PostgreSql server.
 *
 * @author Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_DatabasePostgreSql extends SRA_Database
{
    // {{{ Properties
    // }}}

    // {{{ SRA_DatabasePostgreSql()
    /**
     * Empty constructor to keep PHP from calling parent constructor.
     *
     * @access  public
     */
    function SRA_DatabasePostgreSql()
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
        // NOTE: If there is open large object resource on the connection, do
        // not close the connection before closing all large object resources.
        pg_close($conn);
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
			
        // NOTE: PostgreSql uses the sql statments
        // "BEGIN WORK; END WORK; ROLLBACK WORK;" to control transactions.

        /*
         * The commit() command algorithim is this:
         * Step 1: SRA_DatabasePostgreSql::commit();
         * Step 2: SRA_Database::processCommit();
         * Step 3: SRA_DatabasePostgreSql::commit($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processCommit().
            $result = $this->processCommit();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::commit() SRA_Error returned from processUpdate().";
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
            $result = pg_query($conn, 'END WORK');

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::commit(): SRA_Error: ". pg_last_error($conn);
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
    }
    // }}}
    
    // {{{ unconvertBoolean()
    /**
     * unconverts a boolean
     *
     * @param   mixed $bool boolean value to convert
     * @access  public
     * @return  string
     */
    function unconvertBoolean($bool) {
			return strtolower($bool) == 't' ? TRUE : (strtolower($bool) == 'f' ? FALSE : NULL);
    }
    // }}}
    
    // {{{ convertBoolean()
    /**
     * postgres provides a boolean data type, so just convert the value 
     * appropriately
     *
     * @param   boolean $bool boolean value to convert
     * @access  public
     * @return  String
     */
    function convertBoolean($bool) {
			if (SRA_Util::convertBoolean($bool) || $bool === $this->_boolTrue) {
				$val = "'t'";
			}
			else if (isset($bool)) {
				$val = "'f'";
			}
			else {
				$val = 'NULL';
			}
			return $val;
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
    function &convertBlob(&$blob)
    {
			if (!isset($blob)) { return 'NULL'; }
        // NOTE: This assumes the Postgres bytea datatype is being used.

        return("'" . pg_escape_bytea($blob) . "'");
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
    return "'" . $dateTime->format($dateTime->isDateOnly() ? SRA_DB_DATE_FORMAT : SRA_DB_POSTGRESQL_TIME_FORMAT) . "'";
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
    function convertFloat($value)
    {
			if (!isset($value)) { return 'NULL'; }
        // NOTE: Postgresql uses the FLOAT type for floats.

        // Check for number.
        if (!is_numeric($value))
        {
            // SRA_Error.
            $msg = "SRA_DatabasePostgreSql::convertFloat(): Passed parameter in not numeric it's a ".gettype($value).".";
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
    function convertText($text)
    {
			if (!isset($text)) { return 'NULL'; }
        // NOTE: Postgresql uses the CHAR() or VARCHAR() type for text.

        // Check for string.
        if (is_object($text))
        {
            // SRA_Error.
            $msg = "SRA_DatabasePostgreSql::convertText(): Passed parameter in not a string it's a ".gettype($text).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        return("'". pg_escape_string($text) . "'");
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
     * @return  SRA_ExecuteSet
     */
    function &execute($query, $incCol=FALSE, $errorLevel=SRA_ERROR_PROBLEM, $conn=NULL)
    {
        /*
         * The execute() command algorithim is this:
         * Step 1: SRA_DatabasePostgreSql::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabasePostgreSql::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            $execute_set = $this->processUpdate($query, $incCol, $errorLevel);
            if (SRA_Error::isError($execute_set))
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::execute() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            return($execute_set);
        }
        else
        {
            // echo "$query \n";
            // Step 3:
            // There's a $conn. Execute query.
            $result = pg_query($conn, $query);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::execute(): ".pg_last_error($conn)." $query";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            // Create an SRA_ExecuteSet.
            $xs = new SRA_ExecuteSet(pg_affected_rows($result));

            // Check for incCol and INSERT
            if ($incCol) {
              // try the LASTVAL function
              if (!SRA_ResultSet::isValid($results =& $this->fetch('SELECT LASTVAL()', array(SRA_DATA_TYPE_INT))) || !$results->count()) {
                $msg = "SRA_DatabasePostgreSql::execute(): Could not return the pg_last_oid() for query: $query and serial column: $incCol";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
              }
              $row =& $results->next();
  
              $xs->setSequenceValue($row[0]);
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
     * SRA_Database::fetch method api for more info)
		 * @param int $limit (See SRA_Database::fetch method api for more info)
		 * @param int $offset (See SRA_Database::fetch method api for more info)
     * @param int $errorLevel the error level for the query. (See
     * SRA_Database::fetch method api for more info).
     * @access  public
     * @return  SRA_ResultSet
     */
    function &fetch($query, $types=NULL, $limit = FALSE, $offset = FALSE, $errorLevel=SRA_ERROR_PROBLEM)
    {

        $conn = $this->_getAppDbConnection($query, TRUE);

        if (SRA_Error::isError($conn))
        {
            // SRA_Error.
            $msg = "SRA_DatabasePostgreSql::fetch() Failed to _getAppDbConnection().";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
				
				// add limit and offset
				$baseQuery = $query;
				$query = SRA_Database::applyLimitAndOffset($query, $limit, $offset);
				//echo $query . "\n";

        // Perform query.
        $result = pg_query($conn, $query);

        if (!$result)
        {
            // SRA_Error.
            $msg = "SRA_DatabasePostgreSql::fetch(): ". pg_last_error($conn)."$query";
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
        $num_rows = pg_num_rows($result);

        for ($i = 0; $i < $num_rows; ++$i)
        {
            $row = pg_fetch_array($result, $i, PGSQL_NUM);//PGSQL_NUM PGSQL_ASSOC

            // Cast all types.
            $num_cols = count($row);

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
                        // NOTE: stripcslashes() reverses pg_escape_bytea().
                        $row[$j] = stripcslashes($row[$j]);
                    break;

                    case SRA_DATA_TYPE_BOOLEAN :
                    	$row[$j] = $this->unconvertBoolean($row[$j]);
											break;

                    case SRA_DATA_TYPE_TIME :
                    case SRA_DATA_TYPE_DATE :
                        if ($row[$j]) {
                          $row[$j] = new SRA_GregorianDate($row[$j], $type == SRA_DATA_TYPE_TIME && strlen($row[$j]) < 22 ? $this->_timeZone : NULL);
                          if ($type == SRA_DATA_TYPE_DATE) { $row[$j]->setDateOnly(TRUE); }
                          if ($type == SRA_DATA_TYPE_TIME && SRA_GregorianDate::isValid($row[$j])) $row[$j]->setTimeZone($this->_timeZone);
                        }
                    break;

                    case SRA_DATA_TYPE_FLOAT :
                    case SRA_DATA_TYPE_INT :
                      $row[$j] = $row[$j]*1;
                      break;
                    case SRA_DATA_TYPE_STRING :
                      $row[$j] = stripslashes($row[$j]) . '';
                      break;

                    default: // Cast unknown types to string
                        $msg = "SRA_DatabasePostgreSql::fetch(): Unknown type, $type, in types array. Casting as String.";
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
     * for more info.
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
         * Step 1: SRA_DatabasePostgreSql::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabasePostgreSql::execute($query, $incCol, $errorLevel, $conn);
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
            $result = pg_query($conn, "SELECT nextval('" . $sequence . "')");

            if (!$result)
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::getNextSequence: Failed - ". pg_last_error($conn)."$sequence";
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
                $msg = "SRA_DatabasePostgreSql::getNextSequence: Failed - Sequence call did not return a value.";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }
        }
    }
    // }}}
	
    // {{{ importFile()
    /**
     * Implements the SRA_Database::importFile method. See api for this method for more info. This method returns 
	 * an SRA_Error object if it is not implemented by the child SRA_Database class. For SRA_DatabasePostgreSql, the db user 
	 * must have superuser priveleges in the database in order to be able to import a file. Additionally, this
	 * method requires that an ftp user name, password, and import file directory have been specified in the db
	 * config array.
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
		if (isset($fileName) && file_exists($fileName) && is_string($tableName) && isset($this->_config['ftp-user']) && 
			$this->_config['ftp-password'] && $this->_config['import-file-dir'])
		{
			// Upload file to db servers
			foreach ($this->_config['servers'] as $server)
			{
				$fp = ftp_connect($server);
				if ($fp && ftp_login($fp, $this->_config['ftp-user'], $this->_config['ftp-password']))
				{
					if (!ftp_put($fp, $this->_config['import-file-dir'] . "/" . basename($fileName), $fileName, FTP_BINARY))
					{
						$msg = "SRA_DatabasePostgreSql::importFile: Failed - Could not write to db server $server file: " . 
								$this->_config['import-file-dir'] . "/" . basename($fileName);
						return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
					}
					ftp_quit($fp);
				}
				else
				{
					$msg = "SRA_DatabasePostgreSql::importFile: Failed - Could not access db server $server through ftp to upload import file.";
					return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
				}
			}
			
			$sql = "COPY $tableName from '" . $this->_config['import-file-dir'] . "/" . basename($fileName) . "' using delimiters '$delimiter'";
			if (SRA_Error::isError($es =& $this->execute($sql)))
			{
				$msg = "SRA_DatabasePostgreSql::importFile: Failed - Unable to run import file query: '$sql'.";
				return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
			}
			else if ($deleteFile)
			{
				unlink($fileName);
			}
			
			// Delete file from db servers
			foreach ($this->_config['servers'] as $server)
			{
				$fp = ftp_connect($server);
				if ($fp && ftp_login($fp, $this->_config['ftp-user'], $this->_config['ftp-password']))
				{
					if (!ftp_delete($fp, $this->_config['import-file-dir'] . "/" . basename($fileName)))
					{
						$msg = "SRA_DatabasePostgreSql::importFile: Warning - Could not delete import file from db server $server: " . 
								$this->_config['import-file-dir'] . "/" . basename($fileName);
						SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel);
					}
					ftp_quit($fp);
				}
				else
				{
					$msg = "SRA_DatabasePostgreSql::importFile: Warning - Could not access db server $server through ftp to delete import file.";
					SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel);
				}
			}
		}
		else
		{
			$msg = "SRA_DatabasePostgreSql::importFile: Failed - Invalid fileName or tableName parameters, or ftp-user, ftp-password, or " . 
				   "import-file-dir not specified in the config array.";
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
        $conn = pg_connect("host={$config['server']} dbname={$config['name']} user={$config['user']} password={$config['password']}" . ($config['port'] ? " port={$config['port']}" : ''));

        if (!$conn)
        {
            // SRA_Error.
            $msg = "SRA_DatabasePostgreSql::_openConn(): pg_connect() failed.";
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
			
        // NOTE: PostgreSql uses the sql statments
        // "BEGIN WORK; END WORK; ROLLBACK WORK;" to control transactions.

        /*
         * The rollback() command algorithim is this:
         * Step 1: SRA_DatabasePostgreSql::rollback();
         * Step 2: SRA_Database::processRollback();
         * Step 3: SRA_DatabasePostgreSql::rollback($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processRollback().
            $result = $this->processRollback();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::rollback() SRA_Error returned from processRollback().";
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

            $result = pg_query($conn, 'ROLLBACK WORK');

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::rollback(): SRA_Error: ". pg_last_error($conn);
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
			
        // NOTE: PostgreSql uses the sql statments
        // "BEGIN WORK; END WORK; ROLLBACK WORK;" to control transactions.

        /*
         * The startTransaction() command algorithim is this:
         * Step 1: SRA_DatabasePostgreSql::startTransaction();
         * Step 2: SRA_Database::startTransactions();
         * Step 3: SRA_DatabasePostgreSql::startTransaction($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->startTransactions().
            $result = $this->startTransactions();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::startTransaction() SRA_Error returned from startTransactions().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // Step 3:
            // There's a $conn. Execute query.

            // NOTE: This query returns a result even if the db is
            // currently in a transaction.

            // PHP and PostgreSql ignore cummulative startTransaction() calls.
            // Meaning, if you call startTransaction(), run some queries and
            // then call startTransaction() again, the second startTransaction()
            //  is ignored. You only need to call commit() once and all of the
            // queries beginnig from the first startTransaction() call are
            // committed.

            $result = pg_query($conn, 'BEGIN WORK');

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabasePostgreSql::startTransaction(): SRA_Error: ". pg_last_error($conn);
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
			$definition = str_replace('AUTO_INCREMENT', '', $definition);
			if (!$column->getColumnType()) {
				$definition = str_replace('INT', 'SERIAL', $definition);
			}
		}
    // blob
    if ($column->getType() == SRA_DATA_TYPE_BLOB) {
      $definition = str_replace('BLOB', 'BYTEA', $definition);
    }
    // boolean
    if ($column->getType() == SRA_DATA_TYPE_BOOLEAN) {
      $definition = str_replace('ENUM(' . $this->convertBoolean(TRUE) . ',' . $this->convertBoolean(FALSE) . ')', 'BOOLEAN', $definition);
    }
    // float
    if ($column->getType() == SRA_DATA_TYPE_FLOAT) {
      $definition = str_replace('DOUBLE', 'FLOAT', $definition);
    }
    // date/time
    if ($column->getType() == SRA_DATA_TYPE_DATE || $column->getType() == SRA_DATA_TYPE_TIME) {
      $definition = str_replace('DEFAULT 0', 'DEFAULT NULL', $definition);
      if ($column->getType() == SRA_DATA_TYPE_TIME) {
        $definition = str_replace('TIMESTAMP', 'TIMESTAMP WITH TIME ZONE', $definition);
      }
    }
		
		return $definition;
	}
	// }}}
  
  
    // {{{ isValid()
    /**
     * Static method that returns TRUE if the object parameter references a
     * valid SRA_DatabasePostgreSql object.
     *
     * @param   object $object the object to validate.
     * @access  public
     * @return  boolean
     */
    function isValid($object) {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_databasepostgresql' || is_subclass_of($object, 'SRA_DatabasePostgreSql')));
    }
    // }}}

}
// }}}

?>
