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
/**
 * the name of the template variable under which the name of the 
 * SRA_TestCase class will be stored
 * @type string
 */
define('SRA_TEST_CASE_CLASSNAME_TPL_VAR', 'class');

/**
 * the name of the template variable under which the SRA_TestCase $_results will 
 * be stored
 * @type string
 */
define('SRA_TEST_CASE_RESULTS_TPL_VAR', 'results');

/**
 * the default output results template to use
 * @type string
 */
define('SRA_TEST_CASE_DEFAULT_TEMPLATE', 'test/sra-test-results.tpl');
// }}}

// {{{ SRA_TestCase
/**
 * this class is modeled after the junit testing framework. some of the comments 
 * below have been taken from the api for junit.framework.SRA_TestCase
 * 
 * a test case defines the fixture to run multiple tests. to define a test case:
 * 
 * 1) implement a subclass of SRA_TestCase. an empty SRA_TestCase implementation 
 *    is provided ([SIERRA DIR]/lib/util/SRA_TestCaseImpl.php) and can be copied 
 *    as the starting point for your implementation
 * 2) define any necessary instance variables that store the state of the 
 *    fixture
 * 3) initialize the fixture state by overriding setUp if necessary
 * 4) define 1 or more methods named: test* which implement your testing logic 
 *    (1)
 * 5) clean-up after a test by overriding tearDown if necessary
 * 6) run your test cases using the providing script: 
 *    sierra/bin/run-tests.php [testCaseFileN]. for more information on running 
 *    that script run: sierra/bin/sra-run-tests.php --help
 * 
 * NOTES:
 * (1) Your test* methods, may invoke any of the assert methods provided by the 
 *     this parent class. these include the following: 
 *      $this->assertClass($class, $value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertCount($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertEqual($expected, $value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertFalse($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNotClass($class, $value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNotCount($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNotEqual($expected, $value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNotNull($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNotType($type, $value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertNull($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertTrue($value, $lineNumber=NULL, $msg=NULL)
 *      $this->assertType($type, $value, $lineNumber=NULL, $msg=NULL)
 *     the setUp and tearDown method implementations may also invoke the assert* 
 *     methods. the line number ($lineNumber=NULL, $msg=NULL) is optional but should be 
 *     specified for tracking purposes. the PHP constant __LINE__ should used 
 *     for that parameter
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.test
 */
class SRA_TestCase {
  // {{{ Attributes
  // public attributes
  
  /**
   * the number of tests that failed
   * @type int
   */
  var $failed = 0;
  
  /**
   * the total number of tests run
   * @type int
   */
  var $total = 0;
  
  /**
   * the number of tests that passed
   * @type int
   */
  var $passed = 0;
  
	/**
	 * this attribute stores the overall results of running all of the tests 
   * (after the 'runTests' method has been invoked - it is NULL prior to 
   * invoking 'runTests'). it is TRUE if all of the tests ran successfully, 
   * FALSE otherwise
	 * @type boolean
	 */
	var $status = NULL;
  
  // private attributes
	
	/**
	 * stores the name of the current method being invoked
	 * @type string
	 */
	var $_currentMethod;
	
	/**
	 * used to store the test case results stats. indexed by method name, followed 
	 * by assertion line number and result. the result is an associative array 
	 * containing the following index values:
	 *  class:  if $value was an object, the name of the instance class
	 *  expected: the expected value if applicable
	 *  method: the name of the assertion method that generated this result
	 *  passed: TRUE if the assertion passed, FALSE otherwise
	 *  type:   the data type of $value
	 *  value:  a reference to the $value
	 * @type array
	 */
	var $_results = array();
	
  // }}}
	
  
  // public operations
	
	// {{{ assertClass
	/**
	 * This method asserts that $value is an object is of the class specified
	 * @param string $class the name of the class that $value should be an 
	 * instance of
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertClass($class, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertClass', is_object($value) && $class == get_class($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertCount
	/**
	 * This method asserts that $value is an array and has 1 or more elements
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertCount($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertCount', is_array($value) && count($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertEqual
	/**
	 * This method utilizes SRA_Util::equal to determine if $expected and $value are 
	 * equal. For more information on what constitutes equality, see the api 
	 * documentation for that method
	 * @param mixed $expected the expected value. this may be any data type 
	 * including scalar, array, object
	 * @param mixed $value the actual value
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertEqual($expected, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertEqual', SRA_Util::equal($expected, $value), $value, $lineNumber, $msg, $expected);
	}
	// }}}
	
	// {{{ assertFalse
	/**
	 * This method utilizes SRA_Util::convertBoolean to determine if $value is FALSE
	 * For more information on what constitutes FALSE, see the api documentation 
	 * for that method
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertFalse($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertFalse', SRA_Util::isBoolean($value) && !SRA_Util::convertBoolean($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertNotClass
	/**
	 * This method asserts that value is an object and is NOT of the class specified
	 * @param string $class the name of the class that $value should be an 
	 * instance of
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNotClass($class, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNotClass', is_object($value) && $class != get_class($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertNotCount
	/**
	 * This method asserts that $value is an empty array
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNotCount($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNotCount', is_array($value) && !count($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertNotEqual
	/**
	 * This method utilizes SRA_Util::equal to determine if $expected and $value are 
	 * NOT equal. For more information on what constitutes equality, see the api 
	 * documentation for that method
	 * @param mixed $expected the expected value. this may be any data type 
	 * including scalar, array, object
	 * @param mixed $value the actual value
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNotEqual($expected, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNotEqual', !SRA_Util::equal($expected, $value), $value, $lineNumber, $msg, $expected);
	}
	// }}}
	
	// {{{ assertNotNull
	/**
	 * This method utilizes asserts that $value is not NULL. $value is NULL if 
	 * it == NULL or !isset
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNotNull($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNotNull', !($value == NULL) || isset($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertNotType
	/**
	 * This method asserts that value is NOT of the data type specified
	 * @param string $type the name of the data type
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNotType($type, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNotType', $type != gettype($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertNull
	/**
	 * This method utilizes asserts that $value is NULL. $value is NULL if 
	 * it == NULL or !isset
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertNull($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertNull', ($value == NULL) || !isset($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertTrue
	/**
	 * This method utilizes SRA_Util::convertBoolean to determine if $value is TRUE
	 * For more information on what constitutes TRUE, see the api documentation 
	 * for SRA_Util::isBoolean
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 */
	function assertTrue($value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertTrue', SRA_Util::isBoolean($value) && SRA_Util::convertBoolean($value), $value, $lineNumber, $msg);
	}
	// }}}
	
	// {{{ assertType
	/**
	 * This method asserts that value is of the data type specified
	 * @param string $type the name of the data type
	 * @param mixed $value the value to check
	 * @param int $lineNumber the line number in the SRA_TestCase implementation where 
	 * this assertion was invoked from
   * @param string $msg optional message to include in the results for this 
   * assertion
   * @access public
	 * @return void
	 */
	function assertType($type, $value, $lineNumber=NULL, $msg=NULL) {
		$this->_addResult('assertType', $type == gettype($value), $value, $lineNumber, $msg);
	}
	// }}}
  
	// {{{ runTests
	/**
	 * runs the tests associated with this unit test class and outputs the results 
   * using $template. returns TRUE on success (successful invocation of the unit 
   * tests, NOT success of all of the tests), FALSE otherwise
   * @param string $template the smarty template to use for test results output. 
   * if not specified, the default template: SRA_TEST_CASE_DEFAULT_TEMPLATE will 
   * be used. for details on how to create a custom output template, review the 
   * smarty code in the default template
   * @access public
   * @return boolean
	 */
	function runTests($template=NULL) {
    $resources =& SRA_Controller::getSysResources();
    $template = $template ? $template : SRA_TEST_CASE_DEFAULT_TEMPLATE;
		$methods = get_class_methods(get_class($this));
		if (SRA_Error::isError($this->setUp())) { return FALSE; }
    $this->status = TRUE;
		foreach ($methods as $method) {
			if (SRA_Util::beginsWith($method, 'test')) {
				$this->_currentMethod = $method;
				$this->${method}();
			}
		}
		$this->tearDown();
		$tpl =& SRA_Controller::getAppTemplate();
		$tpl->assign(SRA_TEST_CASE_CLASSNAME_TPL_VAR, get_class($this));
		$tpl->assign(SRA_TEST_CASE_RESULTS_TPL_VAR, $this->_results);
    $tpl->assign('assertionStr', $assertionStr = $resources->getString('run-tests.text.assertion'));
    $tpl->assign('dataTypeStr', $dataTypeStr = $resources->getString('run-tests.text.dataType'));
    $tpl->assign('lineStr', $lineStr = $resources->getString('run-tests.text.line'));
    $tpl->assign('methodStr', $methodStr = $resources->getString('run-tests.text.method'));
    $tpl->assign('msgStr', $msgStr = $resources->getString('run-tests.text.msg'));
    $tpl->assign('resultStr', $resultStr = $resources->getString('run-tests.text.result'));
    $tpl->assign('valueStr', $valueStr = $resources->getString('run-tests.text.value'));
    $tpl->assign('passedStr', $passedStr = $resources->getString('run-tests.text.passed'));
    $tpl->assign('failedStr', $failedStr = $resources->getString('run-tests.text.failed'));
    $assertionLen = strlen($assertionStr) + 2;
    $dataTypeLen = strlen($dataTypeStr) + 2;
    $lineLen = strlen($lineStr) + 2;
    $methodLen = strlen($methodStr) + 2;
    $msgLen = strlen($msgStr) + 2;
    $resultLen = strlen($resultStr) + 2;
    if ((strlen($passedStr) + 2) > $resultLen) $resultLen = strlen($passedStr) + 2;
    if ((strlen($failedStr) + 2) > $resultLen) $resultLen = strlen($failedStr) + 2;
    $valueLen = strlen($valueStr) + 2;
    
    $foundValue = FALSE;
    foreach(array_keys($this->_results) as $method) {
      if ((strlen($method) + 2) > $methodLen) $methodLen = strlen($method) + 2;
      foreach(array_keys($this->_results[$method]) as $line) {
        if ((strlen($line) + 2) > $lineLen) $lineLen = strlen($line) + 2;
        if ((strlen($this->_results[$method][$line]['method']) + 2) > $assertionLen) $assertionLen = strlen($this->_results[$method][$line]['method']) + 2;
        if ((strlen($this->_results[$method][$line][$this->_results[$method][$line]['class'] ? 'class' : 'type']) + 2) > $dataTypeLen) $dataTypeLen = strlen($this->_results[$method][$line][$this->_results[$method][$line]['class'] ? 'class' : 'type']) + 2;
        $valStr = $this->_results[$method][$line]['class'] ? '' : $this->_results[$method][$line]['value'] . ($this->_results[$method][$line]['expected'] && !$this->_results[$method][$line]['passed'] ? ' (expected: ' . $this->_results[$method][$line]['expected'] . ')' : '');
        if ((strlen($valStr) + 2) > $valueLen) $valueLen = strlen($valStr) + 2;
        if ((strlen($this->_results[$method][$line]['msg']) + 2) > $msgLen) $msgLen = strlen($this->_results[$method][$line]['msg']) + 2;
        if ($valStr) {
          $foundValue = TRUE;
        }
      }
    }
    if (!$foundValue) {
      $tpl->assign('valueStr', '');
      $valueLen = 0;
    }
    
    $tpl->assign('assertionLen', '%-' . $assertionLen . 's');
    $tpl->assign('dataTypeLen', '%-' . $dataTypeLen . 's');
    $tpl->assign('lineLen', '%-' . $lineLen . 's');
    $tpl->assign('methodLen', '%-' . $methodLen . 's');
    $tpl->assign('msgLen', '%-' . $msgLen . 's');
    $tpl->assign('resultLen', '%-' . $resultLen . 's');
    $tpl->assign('valueLen', '%-' . $valueLen . 's');
    $border = '';
    for($i=0; $i<($assertionLen + $dataTypeLen + $lineLen + $methodLen + $msgLen + $resultLen + $valueLen - 2); $i++) {
      $border .= '-';
    }
    $tpl->assign('border', $border);
		$tpl->display($template);
    return TRUE;
	}
	// }}}
	
	// {{{ setUp
	/**
	 * This method may be overriden by SRA_TestCase implementations to implement any 
	 * setup related functionality such as setting instance variable for use in 
	 * the test* methods. if this method returns an SRA_Error object, the test 
	 * execution will be aborted. this implementation does nothing. 
   * @access public
	 * @return void
	 */
	function setUp() {}
	// }}}
	
	// {{{ tearDown
	/**
	 * This method may be overriden by SRA_TestCase implementations to implement any 
	 * teardown related functionality such as releasing resources that were 
	 * opened by the setUp method
   * @access public
	 * @return void
	 */
	function tearDown() {}
	// }}}
  
	
  // private operations
	
	// {{{ _addResult
	/**
	 * 
	 * @param string $method the name of the assertion method
	 * @param boolean $result the result value (TRUE or FALSE)
	 * @param mixed $value reference to the value that was tested
	 * @param int $lineNumber the line number from where the assertion was called
   * @access public
	 * @return void
	 */
	function _addResult($method, $result, $value, $lineNumber, $msg, $expected = NULL) {
    $this->total++;
    if (!$result) { 
      $this->failed++;
      $this->status = FALSE; 
    }
    else {
      $this->passed++;
    }
    $class = is_object($value) ? get_class($value) : '';
    $this->_results[$this->_currentMethod][$lineNumber] = array('class'  => is_object($value) ? get_class($value) : '', 
                                                  'expected' => $expected, 
                                                  'method' => $method, 
                                                  'msg' => $msg,
                                                  'passed' => $result,
                                                  'type'   => gettype($value),
                                                  'value'  => $value);
	}
	// }}}
	
  
  // static operations
  
  // {{{ isValid()
  /**
   * Static method that returns TRUE if the object parameter references a
   * valid SRA_TestCase object.
   *
   * @param   object $object the object to validate.
   * @access public
   * @return  boolean
   */
  function isValid($object) {
      return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && (strtolower(get_class($object)) == 'sra_testcase' || is_subclass_of($object, 'SRA_TestCase')));
  }
  // }}}
  
}
// }}}
?>
