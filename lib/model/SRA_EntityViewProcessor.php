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

// {{{ SRA_EntityViewProcessor
/**
 * Represents the data associated with a view processor
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_EntityViewProcessor {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * unique identifier for the view
	 * @type string
	 */
	var $_id;
	
	/**
	 * the path to the command line utility that should be run
	 * @type string
	 */
	var $_path;
	
	/**
	 * input arguments that should be passed into the command line program. the 
	 * following variables can be used:
	 * 						
	 * 						{$outputFile} 		- if specified the output of the "view" or 
	 * 																of the previous SRA_EntityViewProcessor will be 
	 * 																serialized to a file and the path 
	 * 																to that file will be substituted with 
	 * 																every occurrence of this variable in the 
	 * 																args attribute. this file will be 
	 * 																automatically deleted upon completion of 
	 * 																the processing
	 * 																
	 * 						{$randomFile[N]}	- if specified, a random empty file will be 
	 * 																created and the path to that file will be 
	 * 																substituted with every occurrence of this 
	 * 																variable in the args attribute. the [N] is 
	 * 																optional to differential between different 
	 * 																random files. these files will be 
	 * 																automatically deleted upon completion of 
	 * 																the processing
	 * 																
	 * 						{$attr[_subAttr]}	- if specified, the corresponding entity or 
	 * 																entity attribute will be substituted for 
	 * 																every occurrence of this variable in the 
	 * 																args attribute. If this is an object, it 
	 * 																will be specified in a serialized form 
	 *																unless a "toString" method exists, in 
	 *																which case the results from that method 
	 *																call will be used
	 *
	 *						{LENGTH} 					- will be substituted with the size in bytes 
	 * 																of the buffered output prior to execution 
	 * 																of this processor
	 * @type string
	 */
	var $_args;
	
	/**
	 * whether or not the output of the view or preceding SRA_EntityViewProcessor will 
	 * be input directly to the program STDIN. if the program requires 
	 * input as a file, then use the {$outputFile} in the args attribute as 
	 * defined above
	 * @type boolean
	 */
	var $_inputView;
	
	/**
	 * the path to the output file. this attribute may contain the 
	 * {$randomFile[N]} {LENGTH}, or {$attr[_subAttr]} variables which will be 
	 * converted to their corresponding values as defined above. If this attribute 
	 * is not specified, then the output will be retrieved from the program STDOUT
	 * @type string
	 */
	var $_outputFilePath;
	
	/**
	 * a command or commands (separated by ;) to be executed after running the 
	 * command line program. any of the "args" variables ({$outputFile}, 
	 * {$randomFile[N]}, {LENGTH}, or {$attr[_subAttr]}) may also be specified in 
	 * this attribute
	 * @type string
	 */
	var $_postProcessCmd;
	
	/**
	 * a command or commands (separated by ;) to be executed prior to running the 
	 * command line program. any of the "args" variables ({$outputFile}, 
	 * {$randomFile[N]}, {LENGTH}, or {$attr[_subAttr]}) may also be specified in 
	 * this attribute
	 * @type string
	 */
	var $_preProcessCmd;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntityViewProcessor
	/**
	 * Constructor - does nothing
   * @access  public
	 */
	function SRA_EntityViewProcessor($id, $path, $args, $inputView, $outputFilePath, $postProcessCmd, $preProcessCmd) {
		$this->_id = $id;
		$this->_path = $path;
		$this->_args = $args;
		$this->_inputView = $inputView === TRUE || $inputView != '0';
		$this->_outputFilePath = $outputFilePath;
		$this->_postProcessCmd = $postProcessCmd;
		$this->_preProcessCmd = $preProcessCmd;
	}
	// }}}
	
  
  // public operations
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_EntityViewProcessor object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_entityviewprocessor');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
