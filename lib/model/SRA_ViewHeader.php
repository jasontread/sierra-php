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

// {{{ SRA_ViewHeader
/**
 * Represents a single view header
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_ViewHeader {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the header string to be output
	 * @type string
	 */
	var $_str;
	
  /**
	 * indicates whether the header should replace a previous similar header, or 
	 * add a second header of the same type
	 * @type boolean
	 */
	var $_replace;
	
  /**
	 * force the HTTP response code to the specified valuee
	 * @type int
	 */
	var $_responseCode;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_ViewHeader
	/**
	 * Constructor - does nothing
   * @access  public
	 */
	function SRA_ViewHeader($str, $replace = FALSE, $responseCode = FALSE) {
		$this->_str = $str;
		$this->_replace = $replace === TRUE || $replace == '1';
		$this->_responseCode = $responseCode;
	}
	// }}}
	
  
  // public operations
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_ViewHeader object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_viewheader');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
