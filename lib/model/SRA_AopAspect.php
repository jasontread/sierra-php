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
 * references the entity data access object for aop attachments
 * @type string
 */
define('SRA_AOP_CLASS_DAO', 'dao');

/**
 * references the entity value object for aop attachments
 * @type string
 */
define('SRA_AOP_CLASS_VO', 'vo');

/**
 * type identifying that advice should be attached at the end of a method
 * @type string
 */
define('SRA_AOP_ASPECT_WHEN_AFTER', 'after');

/**
 * type identifying that advice should be attached at the beginning of a method
 * @type string
 */
define('SRA_AOP_ASPECT_WHEN_BEFORE', 'before');
// }}}

// {{{ SRA_AopAspect
/**
 * The glue between advice and pointcuts. it defines the code to be attached 
 * (advice) as well as the location(s) where that code should be attached 
 * (pointcut)
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_AopAspect {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * the unique identifier for this aspect
	 * @type string
	 */
	var $_id;
  
  /**
	 * the code that should be applied. this code may perform such activities as 
   * logging, validation, additional logic, etc. It has full access to the 
   * parameters to that method invocation as well as the object instance 
   * variables and any other data and/or code accessible within the current 
   * running php process. the code may additionally pre-maturely exit the method 
   * using return.
	 * @type string
	 */
	var $_advice;
  
  /**
	 * an optional aspect comment. this will be inserted above the code resulting 
   * from this aspect
	 * @type string
	 */
	var $_comment;
  
  /**
   * an array of method names. each value may be either the full method name 
   * (minus parens and param definition), or a regular expression 
   * representing 1 or more method names. these values may optionally be 
   * prefixed with the class type identifier (dao|vo) - for example, 
   * "setName" would reference the method setName in any entity class while 
   * "vo.setName" would only reference the method setName in the value object 
   * for this entity. joinpoints may also be references to methods attached to 
   * a class by means of an introduction
   * @type array
   */
  var $_joinPoints;
	
  /**
	 * when the advice should be applied. SRA_AOP_ASPECT_WHEN_BEFORE signifies 
   * that it will be attached before any other code in that method, while 
   * SRA_AOP_ASPECT_WHEN_AFTER signifies that it will be attached after all the 
   * other code in that method has been executed (before the return of course)
	 * @type string
	 */
	var $_when = SRA_AOP_ASPECT_WHEN_BEFORE;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_AopAspect
	/**
	 * Constructor
   * @param string $id the aspect identifier within $conf
   * @param array $conf the full aop configuration
   * @access  public
	 */
	function SRA_AopAspect($id, & $conf) {
    $this->_id = $id;
    $this->_advice = isset($conf['advice'][$conf['aspect'][$id]['attributes']['advice']]['xml_value']) ? $conf['advice'][$conf['aspect'][$id]['attributes']['advice']]['xml_value'] : $conf['aspect'][$id]['xml_value'];
    $this->_comment = isset($conf['aspect'][$id]['attributes']['comment']) ? $conf['aspect'][$id]['attributes']['comment'] : NULL;
    $this->_joinPoints = explode(' ', (isset($conf['pointcut'][$conf['aspect'][$id]['attributes']['pointcut']]['attributes']['joinpoints']) ? $conf['pointcut'][$conf['aspect'][$id]['attributes']['pointcut']]['attributes']['joinpoints'] : $conf['aspect'][$id]['attributes']['pointcut']));
    $this->_when = isset($conf['advice'][$conf['aspect'][$id]['attributes']['advice']]['attributes']['when']) ? $conf['advice'][$conf['aspect'][$id]['attributes']['advice']]['attributes']['when'] : (isset($conf['aspect'][$id]['attributes']['when']) ? $conf['aspect'][$id]['attributes']['when']: $this->_when);
    
    // validate
    if (!$this->_advice || !count($this->_joinPoints) || ($this->_when != SRA_AOP_ASPECT_WHEN_AFTER && $this->_when != SRA_AOP_ASPECT_WHEN_BEFORE)) {
      $msg = "SRA_AopAspect::SRA_AopAspect: Failed - Missing mandatory values: id=$id, advice: $this->_advice, joinpoints: " . implode(' ', $this->_joinPoints) . ", when: $this->_when";
      SRA_Error::logError($msg, __FILE__, __LINE__);
      $this->err = $msg;
    }
	}
	// }}}
	
  
  // public operations
	// {{{ appliesTo
	/**
	 * returns TRUE if this aspect applies to the given criteria
   * @access  public
	 */
	function appliesTo($class, $method, $when) {
    $applies = FALSE;
    if ($this->_when == $when) {
      foreach($this->_joinPoints as $pointcut) {
        if (trim($pointcut)) {
          $pointClass = FALSE;
          if (SRA_Util::beginsWith($pointcut, SRA_AOP_CLASS_DAO . '.')) {
            $pointClass = 'dao';
            $pointcut = substr($pointcut, strlen(SRA_AOP_CLASS_DAO . '.'));
          }
          else if (SRA_Util::beginsWith($pointcut, SRA_AOP_CLASS_VO . '.')) {
            $pointClass = 'vo';
            $pointcut = substr($pointcut, strlen(SRA_AOP_CLASS_VO . '.'));
          }
          if ((!$pointClass || $pointClass == $class) && ($pointcut == $method || (!SRA_Util::beginsWith($method, $pointcut) && preg_match('/' . $pointcut . '/', $class . '.' . $method)))) {
            $applies = TRUE;
            break;
          }
        }
      }
    }
    return $applies;
	}
	// }}}
  
	// {{{ getId
	/**
	 * getter for _id
   * @access  public
	 */
	function getId() {
    return $this->_id;
	}
	// }}}
  
	// {{{ getAdvice
	/**
	 * getter for _advice
   * @access  public
	 */
	function getAdvice() {
    return $this->_advice;
	}
	// }}}
  
	// {{{ getComment
	/**
	 * getter for _comment
   * @access  public
	 */
	function getComment() {
    return $this->_comment;
	}
	// }}}
  
	// {{{ getJoinPoints
	/**
	 * getter for _joinPoints
   * @access  public
	 */
	function getJoinPoints() {
    return $this->_joinPoints;
	}
	// }}}
  
	// {{{ getWhen
	/**
	 * getter for _when
   * @access  public
	 */
	function getWhen() {
    return $this->_when;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_AopAspect object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_introduction');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
