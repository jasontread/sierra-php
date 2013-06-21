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

// }}}

// {{{ SRA_Matrix
/**
 * Used to represent a matrix: a data array of two or more dimensions 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_Matrix {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	/**
	 * array representing the matrix
	 * @type array
	 */
	var $_matrix = array();
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Matrix
	/**
	 * Constructor
   * @access  private
	 */
	function SRA_Matrix() {
		
	}
	// }}}
	
  // public operations
	
	// {{{ getHeight
	/**
	 * returns the # of rows in this matrix
   * @access  public
	 * @return int
	 */
	function getHeight() {
		return count($this->_matrix);
	}
	// }}}
	
	// {{{ getWidth
	/**
	 * returns the width of a row in the matrix or the max width if $row is not 
	 * specified
	 * @param int $row the row to return the width for. if not specified, the max 
	 * matrix width will be returned
   * @access  public
	 * @return int
	 */
	function getWidth($row=FALSE) {
		if ($row === FALSE || !isset($this->_matrix[$row])) {
			$maxWidth = 0;
			for($i=0; $i<count($this->_matrix); $i++) {
				if ($maxWidth < count($this->_matrix[$i])) {
					$maxWidth = count($this->_matrix[$i]);
				}
			}
			return $maxWidth;
		}
		else {
			return count($this->_matrix[$row]);
		}
	}
	// }}}
	
	// {{{ getValue
	/**
	 * returns the value in the matrix at $row/$col
	 * @param int $row the row to return the value for
	 * @param int $col the col to return the value for
   * @access  public
	 * @return int
	 */
	function getValue($row, $col) {
		return count($this->_matrix[$row][$col]) > 1 ? $this->_matrix[$row][$col] : $this->_matrix[$row][$col][0];
	}
	// }}}
	
	// {{{ pushColumn
	/**
	 * Adds a column to the current row in the matrix
	 * @param mixed $column the column to push, can be an array or a single value
	 * @param int $row the row # to push this column to. if not specified, the last 
	 * row will be used
   * @access  public
	 * @return void
	 */
	function pushColumn($column, $row=FALSE) {
		//echo "PUSH COLUMN $column in ROW $row\n<br>";
		if (!is_array($column)) {
			$column = array($column);
		}
		if (!count($this->_matrix)) {
			$this->_matrix[] = array();
		}
		if ($row === FALSE || !is_numeric($row) || $row < 0) {
			$row = count($this->_matrix) - 1;
		}
		if ($row >= count($this->_matrix)) {
			for($i=count($this->_matrix); $i<$row; $i++) {
				$this->_matrix[$i] = array();
			}
		}
		$this->_matrix[$row][] = $column;
	}
	// }}}
	
	// {{{ pushRow
	/**
	 * Adds a row to the matrix
	 * @param mixed $row the row to push, can be an array or a single value
   * @access  public
	 * @return void
	 */
	function pushRow($row=FALSE) {
		//echo "PUSH ROW $row\n<br>";
		if (!$row) {
			$row = array();
		}
		if (!is_array($row)) {
			$row = array($row);
		}
		$this->_matrix[] = array($row);
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * Returns a string representation of this object
   * @access  public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_Generator object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_matrix');
	}
	// }}}
	
  
  // private operations
  
}
// }}}
