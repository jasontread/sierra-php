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

/**
 * the max # of characters allowed for a CHAR column to be used instead of 
 * VARCHAR2 by default (in order to reduce table fragmentation caused by lots of 
 * small varchar columns)
 */
define('SRA_DATABASE_ORACLE_USE_CHAR_MAX_LEN', 64);

// {{{ Includes
/**
 * whether or not to use the oci_pconnect option when connecting to the 
 * database
 * @type boolean
 */
if (!defined('SRA_DATABASE_ORACLE_USE_PCONNECT')) define('SRA_DATABASE_ORACLE_USE_PCONNECT', FALSE);
// }}}

// {{{ SRA_DatabaseOracle
/**
 * This class extends from the abstract SRA_Database class. It is utilized to
 * handle database interraction with an Oracle server. OCI8 extensions must be 
 * installed. sequence columns are implemented by creating both a sequence and 
 * a trigger for each such column
 * @author Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_DatabaseOracle extends SRA_Database
{
    // {{{ Properties
    // }}}

    // {{{ SRA_DatabaseOracle()
    /**
     * Empty constructor to keep PHP from calling parent constructor.
     *
     * @access  public
     */
    function SRA_DatabaseOracle()
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
        oci_close($conn);
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
         * Step 1: SRA_DatabaseOracle::commit();
         * Step 2: SRA_Database::processCommit();
         * Step 3: SRA_DatabaseOracle::commit($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processCommit().
            $result = $this->processCommit();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::commit() SRA_Error returned from processUpdate().";
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
            $result = oci_commit($conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::commit(): SRA_Error: ". oci_error($conn);
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
			return strtolower($bool) == '1' ? TRUE : (strtolower($bool) == '0' ? FALSE : NULL);
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
				$val = "'1'";
			}
			else if (isset($bool)) {
				$val = "'0'";
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
      // use base64 encoding for blobs
			if (!isset($blob)) { return 'NULL'; }
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
    return "to_date('" . $dateTime->format($dateTime->isDateOnly() ? 'm-d-Y' : 'm-d-Y H:i:s') . "', '" . ($dateTime->isDateOnly() ? 'mm-dd-YYYY' : 'mm-dd-YYYY HH24:MI:SS') . "')";
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
            $msg = "SRA_DatabaseOracle::convertFloat(): Passed parameter in not numeric it's a ".gettype($value).".";
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
            $msg = "SRA_DatabaseOracle::convertText(): Passed parameter in not a string it's a ".gettype($text).".";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }

        return("'". str_replace("'", "''", $text) . "'");
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
         * Step 1: SRA_DatabaseOracle::execute($query, $incCol, $errorLevel);
         * Step 2: SRA_Database::processUpdate($query, $incCol, $errorLevel);
         * Step 3: SRA_DatabaseOracle::execute($query, $incCol, $errorLevel, $conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass query to $this->processUpdate().
            $execute_set = $this->processUpdate($query, $incCol, $errorLevel);
            if (SRA_Error::isError($execute_set))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::execute() SRA_Error returned from processUpdate().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            return($execute_set);
        }
        else {
          // check if longer than 4000 chars and use php variable binding if so
          $bindVars = array();
          if (strlen($query) > 4000 && preg_match_all("/'([\S]+)'/", $query, $m)) {
            foreach(array_keys($m[1]) as $i) {
              if (strlen($m[1][$i]) > 4000) {
                $bindVars[$i] = $m[1][$i];
                $query = str_replace("'" . $bindVars[$i] . "'", ':b' . $i, $query);
                $bindVars[$i] = str_replace("''", "'", $bindVars[$i]);
              }
            }
          }
          
          // Step 3:
          // There's a $conn. Execute query.
          if ($stmt = oci_parse($conn, $query)) {
            $lobs = array();
            foreach(array_keys($bindVars) as $i) {
              $lobs[$i] = oci_new_descriptor($conn);
              if (!$lobs[$i]->writeTemporary($bindVars[$i]) || !oci_bind_by_name($stmt, ":b$i", $lobs[$i], -1, OCI_B_BLOB)) {
                SRA_Error::logError("SRA_DatabaseOracle::execute - Error: Unable to bind variable :b$i in query $query");
              }
            }
            if (($result = oci_execute($stmt, $this->_autoCommit ? OCI_COMMIT_ON_SUCCESS : OCI_NO_AUTO_COMMIT)) && $lobs) {
              foreach(array_keys($lobs) as $i) {
                $lobs[$i]->close();
              }
            }
          }

          if (!$stmt || $result === FALSE)
          {
              // SRA_Error.
              $err = oci_error($stmt);
              if (is_array($err)) $err = implode(', ', $err);
              $msg = "SRA_DatabaseOracle::execute(): ".$err." $query";
              // NOTE: This SRA_Error uses $errorLevel.
              return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
          }

          // Create an SRA_ExecuteSet.
          $xs = new SRA_ExecuteSet(oci_num_rows($stmt));
          oci_free_statement($stmt);

          // Check for incCol and INSERT
          if ($incCol && preg_match('/^insert\s+into\s+(\S+)\s/i', $query, $m)) {
            $query = "SELECT max($incCol) FROM $m[1]";
            if (($stmt = oci_parse($conn, $query)) && ($result = oci_execute($stmt)) && ($row = oci_fetch_row($stmt))) {
              $incColVal = $row[0];
              $xs->setSequenceValue($incColVal);
            }
            else {
              $msg = "SRA_DatabaseOracle::execute: Error - unable to retrieve incremental value for column $incCol using query $query";
              return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }
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
            $msg = "SRA_DatabaseOracle::fetch() Failed to _getAppDbConnection().";
            return(SRA_Error::logError($msg, __FILE__, __LINE__));
        }
				
				// add limit and offset
				$baseQuery = $query;
				
				if ($limit || $offset) {
				  $query = 'SELECT * FROM (SELECT  x.*, rownum as r FROM (' . $query . ') x ) WHERE r ';
				  if ($limit && $offset) $query .= 'BETWEEN ' . $offset . ' AND ' . ($limit + $offset);
				  else if ($limit) $query .= ' <= ' . ($limit + $offset);
				  else if ($offset) $query .= 'rownum >= ' . $offset;
				}
				// echo $query . "\n";
				// sra_error::logerror($query);

        // Perform query
        if ($stmt = oci_parse($conn, $query)) {
          $result = oci_execute($stmt);
        }

        if (!$stmt || !$result) {
          $msg = "SRA_DatabaseOracle::fetch - OCI Error: ". oci_error($conn)." CONN: $conn STMT: $stmt RESULT: $result QUERY: $query";
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
        $num_rows = oci_num_rows($stmt);
        $num_cols = oci_num_fields($stmt);

        while($row = oci_fetch_row($stmt)) {

            // Cast all types.

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
                    case SRA_DATA_TYPE_BOOLEAN :
                    	$row[$j] = $this->unconvertBoolean($row[$j]);
											break;

                    case SRA_DATA_TYPE_TIME :
                    case SRA_DATA_TYPE_DATE :
                        if ($row[$j]) {
                          $row[$j] = str_replace('.', ':', str_replace('.000000', '', $row[$j]));
                          if ($row[$j]) { $row[$j] = new SRA_GregorianDate($row[$j], $this->_timeZone); }
                          if ($type == SRA_DATA_TYPE_DATE && SRA_GregorianDate::isValid($row[$j])) { $row[$j]->setDateOnly(TRUE); }
                          if ($type == SRA_DATA_TYPE_TIME && SRA_GregorianDate::isValid($row[$j])) $row[$j]->setTimeZone($this->_timeZone);
                        }
                    break;

                    case SRA_DATA_TYPE_FLOAT :
                    case SRA_DATA_TYPE_INT :
                      $row[$j] = $row[$j]*1;
                      break;
                    case SRA_DATA_TYPE_BLOB :
                    case SRA_DATA_TYPE_STRING :
                      // clobs
                      if (is_object($row[$j])) {
                        $row[$j] = $row[$j]->read($row[$j]->size());
                      }
                      // normal strings
                      else {
                        $row[$j] = rtrim($row[$j]);
                      }
                      if ($type == SRA_DATA_TYPE_BLOB) $row[$j] = base64_decode($row[$j]);
                      break;

                    default: // Cast unknown types to string
                        $msg = "SRA_DatabaseOracle::fetch(): Unknown type, $type, in types array. Casting as String.";
                        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
                        $row[$j] = (string)$row[$j];
                }
              }
            }
            $fs->add($row);
        }
        oci_free_statement($stmt);

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
            if ($stmt = oci_parse($conn, "SELECT $sequence.nextval FROM DUAL")) {
              $result = oci_execute($stmt);
            }

            if (!$stmt || !$result)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::getNextSequence: Failed - ". oci_error($conn)."$sequence";
                // NOTE: This SRA_Error uses $errorLevel.
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }

            if (oci_num_rows($result))
            {
                $row = oci_fetch_row($result);
                return (int) $row[0];
            }
            else
            {
                $msg = "SRA_DatabaseOracle::getNextSequence: Failed - Sequence call did not return a value.";
                return(SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel));
            }
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
        if (SRA_DATABASE_ORACLE_USE_PCONNECT) {
          $conn = oci_pconnect($config['user'], $config['password'], $config['server'] . ($config['port'] ? ':' . $config['port'] : '') . '/' . $config['name']);
        }
        else {
          $conn = oci_connect($config['user'], $config['password'], $config['server'] . ($config['port'] ? ':' . $config['port'] : '') . '/' . $config['name']);
        }
        
        if (!$conn)
        {
            // SRA_Error.
            $msg = "SRA_DatabaseOracle::_openConn(): pg_connect() failed: " . oci_error();
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
         * Step 1: SRA_DatabaseOracle::rollback();
         * Step 2: SRA_Database::processRollback();
         * Step 3: SRA_DatabaseOracle::rollback($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->processRollback().
            $result = $this->processRollback();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::rollback() SRA_Error returned from processRollback().";
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

            $result = oci_rollback($conn);

            if ($result === FALSE)
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::rollback(): SRA_Error: ". oci_error($conn);
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
    }
    // }}}

    // {{{ startTransaction
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
         * Step 1: SRA_DatabaseOracle::startTransaction();
         * Step 2: SRA_Database::startTransactions();
         * Step 3: SRA_DatabaseOracle::startTransaction($conn);
         */
        if ($conn === NULL)
        {
            // Step 1:
            // Pass to $this->startTransactions().
            $result = $this->startTransactions();
            if (SRA_Error::isError($result))
            {
                // SRA_Error.
                $msg = "SRA_DatabaseOracle::startTransaction() SRA_Error returned from startTransactions().";
                return(SRA_Error::logError($msg, __FILE__, __LINE__));
            }
        }
        else
        {
            // NOT NEEDED
        }
    }
    // }}}
		
		
	// {{{ getColumnDefinition
	/**
	 * provides an sql statement defining the data type, constraints, and 
	 * referential integrity (optional) for a given SRA_SchemaColumn. this method 
	 * may be implemented by each of the underlying database types if the 
	 * default ORACLE implementation does not suffice
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
	  $def = parent::getColumnDefinition($table, $column, $dbRefIntegrity, $addCheckConstraint);
	  
		// sequence types
		if ($column->isSequence()) {
      $def = str_replace('PRIMARY KEY', '', $def);
			$def = str_replace('AUTO_INCREMENT', '', $def);
		}
    // boolean
    if ($column->getType() == SRA_DATA_TYPE_BOOLEAN) {
      $def = str_replace('ENUM(' . $this->convertBoolean(TRUE) . ',' . $this->convertBoolean(FALSE) . ')', 'CHAR(1)', $def) . ' check (' . $column->getName() . " in ( '1', '0' ))";
    }
    // float
    if ($column->getType() == SRA_DATA_TYPE_FLOAT) {
      $def = str_replace('DOUBLE', 'FLOAT', $def);
    }
    // date/time
    if ($column->getType() == SRA_DATA_TYPE_DATE || $column->getType() == SRA_DATA_TYPE_TIME) {
      $def = str_replace('DEFAULT 0', 'DEFAULT NULL', $def);
    }
    // string
    if ($column->getType() == SRA_DATA_TYPE_STRING) {
      $def = str_replace('VARCHAR', 'VARCHAR2', $def);
      if ($column->getVars('maxLength')  > 4000) {
        $def = str_replace('VARCHAR2(' . $column->getVars('maxLength') . ')', 'CLOB', $def); 
      }
    }
    // char
    if ($column->getType() == SRA_DATA_TYPE_STRING && $column->hasValidator('maxLength') && $column->getVars('maxLength') <= SRA_DATABASE_ORACLE_USE_CHAR_MAX_LEN) {
      $maxLen = $column->getVars('maxLength');
      $def = str_replace("VARCHAR2(${maxLen})", "CHAR(${maxLen})", $def);
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
    // generate triggers for auto incrementing columns
    $ddl = '';
    foreach(array_keys($columns =& $table->getColumns()) as $key) {
      if ($columns[$key]->isSequence()) {
        $seq = strtoupper($table->getName() . $columns[$key]->getName() . '_SEQ');
        $trigger = strtoupper($table->getName() . $columns[$key]->getName() . '_SEQT');
        $ddl .= "CREATE SEQUENCE $seq START WITH 1 INCREMENT BY 1 NOMAXVALUE;\n";
        $ddl .= "CREATE TRIGGER $trigger\n";
        $ddl .= 'BEFORE INSERT ON ' . $table->getName() . "\n";
        $ddl .= "FOR EACH ROW\n";
        $ddl .= "BEGIN\n";
        $ddl .= "SELECT ${seq}.NEXTVAL INTO :NEW." . $columns[$key]->getName() . " FROM DUAL:#:\n";
        $ddl .= "END;";
      }
    }
    return $ddl;
  }
  // }}}
  
  // {{{ isValid
  /**
   * Static method that returns TRUE if the object parameter references a
   * valid SRA_DatabaseOracle object.
   *
   * @param   object $object the object to validate.
   * @access  public
   * @return  boolean
   */
  function isValid($object) {
      return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_databaseoracle' || is_subclass_of($object, 'SRA_DatabaseOracle')));
  }
  // }}}

}
// }}}

?>
