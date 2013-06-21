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
require_once('test/SRA_TestCase.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_TestCaseImpl
/**
 * this class can be used as the starting point for creating test cases. for  
 * more information, see the api documentation in lib/test/SRA_TestCase.php
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.test
 */
class SRA_TestCaseImpl extends SRA_TestCase {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	/* Place any instance variable declarations here. instance variables are 
   * useful where multiple test methods require use of them. they can be 
	 * initially set using the setUp method and released (if necessary) using the 
	 * tearDown method
	 */
	
	
  // }}}
	
	// CONSTRUCTOR OPTIONAL
  
  // public operations
	
	// {{{ setUp
	/**
	 * this method can be used to perform any setup related functionality such as 
	 * setting instance variables that will be used within the test case. it will 
	 * be invoked before any test methods
   * @access public
	 * @return void
	 */
	function setUp() {
		// Add setup tasks here
		
	}
	// }}}
	
	// {{{ tearDown
	/**
	 * this method can be used to release any resources, open connections, etc. 
	 * that were established by the setUp method. it will be invoked after all of 
	 * the test methods
   * @access public
	 * @return void
	 */
	function tearDown() {
		// Add tear down tasks here
		
	}
	// }}}
	
	// {{{ test
	/**
	 * all methods beginning with 'test' will be invoked and the results recorded 
	 * for the test case. implement your testing logic using these methods
   * @access public
	 * @return void
	 */
	function test() {
		// implement testing logic here
	}
	// }}}
  
	
  // private operations
	
}
// }}}
?>
