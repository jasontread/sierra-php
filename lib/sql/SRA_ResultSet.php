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
// }}}

// {{{ Includes
// }}}

// {{{ SRA_ResultSet

/**
 * Represents the results from performing a database query
 *
 * @author	Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_ResultSet {
    // {{{ Properties
    /**
     * the query that produced this result set without any limit or offset constraints
     * @type string
     * @access private
     */
    var $_baseQuery;
		
    /**
     * the SRA_Database that produced this result set
     * @type   SRA_Database
     * @access private
     */
    var $_db;
		
    /**
     * the limit applied to this query (if any)
     * @type   int
     * @access private
     */
    var $_limit;
    
    /**
     * the offset applied to this query (if any)
     * @type   int
     * @access private
     */
    var $_offset;
		
    /**
     * Holds the current position in the _records array.
     * @type   int
     * @access private
     */
    var $_position;

    /**
     * Holds the array of current records.
     * @type   string[]
     * @access private
     */
    var $_records;
		
		/**
		 * Stores the column names from the query
		 * @type string[]
		 * @access private
		 */
		var $_columnNames;
		
		/**
		 * the # of records to return by the count() method. if not specfied, 
		 * count($this->_records) will be returned
		 * @type int
		 * @access package
		 */
		var $_numRecords = FALSE;
		
    /**
     * the query that produced this result set
     * @type   string
     * @access private
     */
    var $_query;
		
    // }}}

    // {{{ SRA_ResultSet()
    /**
     * Constructor. Used to instantiate a new SRA_ResultSet object.
     *
		 * @param SRA_Database $db the SRA_Database instance that produced this result set
     * @param string $query the query that this object will represent. this 
     * 			parameter is used to provides associative row arrays in the next method
		 * @param string $baseQuery the query without any limit or offset parameters
     * @param array $records the records to set to the _records instance variable.
     * 			The default for this parameter is false and it is not required.
     * @access  public
     * @return  void
     */
    function SRA_ResultSet(& $db, $query, $baseQuery, $limit, $offset, $records=FALSE)
    {
				$this->_db =& $db;
				$this->_query = $query;
				$this->_baseQuery = $baseQuery;
				$this->_limit = $limit;
        $this->_offset = $offset;
        $this->_position = 0;

        if (FALSE !== $records)
        {
            $this->_records = $records;
        }
				$data = SRA_Database::parseQuery($query);
				$this->_columnNames = array_values($data['select']);
    }
    // }}}

    // {{{ add()
    /**
     * Add a record to the end of the record array.
     *
     * @param array record record to add.
     * @access  public
     * @return  void
     */
    function add($record)
    {
        $this->_records[] = $record;
    }
    // }}}
		
    // {{{ count()
    /**
     * Same as getCount
     *
     * @access  public
     * @return  int
     */
    function count()
    {
			return(count($this->_records));
    }
    // }}}
    
    // {{{ eof
    /**
     * returns TRUE if the record pointer is currently at the end of the result
     * set
     * @access  public
     * @return  boolean
     */
    function eof() {
      return $this->_position == $this->count();
    }
    // }}}

    // {{{ getCount()
    /**
     * Returns the # of records in this result set
     *
     * @access  public
     * @return  int
     */
    function getCount()
    {
			return(count($this->_records));
    }
    // }}}
    
    // {{{ getDelimitedResults
    /**
     * returns the results as a delimited string
     * @param string $delimiter the delimiter to user (default is a comma). if 
     * any of the values in the result set contains this delimiter, they will 
     * be enclosed with double quotes (with any embedded double quotes escaped)
     * @param boolean $includeHeader whether or not to include a header row 
     * containing the column names. default is FALSE
     * @param boolean $lineBreak the line break character(s). default is \n
     * @param string $finalLineBreak if you are using this method to 
     * construct a list such as "a, b, c, d" from a single column result set, 
     * and you wish the final value in that list to use a custom line break 
     * (such as ", and "), then this parameter may be used to specify that 
     * custom delimiter (where $delimiter is '' and $lineBreak is ', ')
     * @access public
     * @return string
     */
    function getDelimitedResults($delimiter = ',', $includeHeader=FALSE, $lineBreak="\n", $finalLineBreak='') {
      $delimited = '';
      $this->reset();
      $started = FALSE;
      while($row =& $this->next(TRUE)) {
        $delimited .= $started ? ($finalLineBreak && $this->eof() ? $finalLineBreak : $lineBreak) : '';
        $keys = array_keys($row);
        if (!$started && $includeHeader) {
          foreach($keys as $key) {
            $delimited .= $key != $keys[0] ? $delimiter : '';
            $delimited .= strpos($key, $delimiter) === FALSE ? $key : '"' . str_replace('"', '\"', $key) . '"';
          }
        }
        foreach($keys as $key) {
          $delimited .= $key != $keys[0] ? $delimiter : '';
          $delimited .= strpos($row[$key], $delimiter) === FALSE ? $row[$key] : '"' . str_replace('"', '\"', $row[$key]) . '"';
        }
        $started = TRUE;
      }
      return $delimited;
    }
    // }}}
		
    // {{{ getTotalCount()
    /**
     * Returns the total # of records in this result set. this is the total # of 
		 * records that would have been returned had not offset or limit been 
		 * applied
     *
     * @access  public
     * @return  int
     */
    function getTotalCount()
    {
			if ($this->_limit || $this->_offset) {
				if ($this->_numRecords === FALSE) {
          if ($this->getCount() == $this->_limit) {
            $this->_numRecords = SRA_Database::getRecordCount($this->_db, $this->_baseQuery);
          }
          else {
            $this->_numRecords = $this->getCount() + $this->_offset;
          }
				}
				return $this->_numRecords;
			}
			else {
				return $this->getCount();
			}
    }
    // }}}
	
    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter references a
     * valid SRA_ResultSet object.
     *
     * @param object $object the object to validate.
     * @access  public
     * @return  boolean
     */
    function isValid($object)
    {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_resultset');
    }
    // }}}

    // {{{ next()
    /**
     * Get the next record field.
     *
     * @param boolean $associative optional parameter specifying whether the returned 
     * array should be associative based on the column name defined in the query. the 
     * default value for this parameter is FALSE, meaning the returned array will have 
     * a numerical index. In order for this functionality to work, the column names/aliases 
		 * must be explicitely defined in the query (i.e. * will not work)
     * @access  public
     * @return  array
     */
    function next($associative = FALSE)
    {
        // Check for next record.
        $r_cnt = count($this->_records);
        if ($this->_position < $r_cnt && $r_cnt > 0) {

            ++$this->_position;

            // note the -1.
            $record = $this->_records[$this->_position-1];
						if (!$associative) {
							return($record);
						}
						else {
							$newRecord = array();
							for($i=0; $i<count($record); $i++) {
								$newRecord[$this->_columnNames[$i]] = $record[$i];
							}
							return $newRecord;
						}
        }

        // No more records.
        return FALSE;

    }
    // }}}

    // {{{ reset()
    /**
     * Reset the SRA_ResultSet position to 0.
     *
     * @access  public
     * @return  void
     */
    function reset()
    {
        $this->_position = 0;
    }
    // }}}

    // {{{ seek()
    /**
     * Move the pointer to a certain record. Records start at 0.
     *
     * @param int $pos postion to goto.
     * @access  public
     * @return  void
     */
    function seek($pos)
    {
        $this->_position = $pos;
    }
    // }}}

}
// }}}

?>