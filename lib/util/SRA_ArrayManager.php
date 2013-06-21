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

// {{{ SRA_ArrayManager
/**
 * This class manages some of the more complex aspects of handling arrays.
 * Each object instantiation manages a single array.
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_ArrayManager {
    // {{{ Properties
    /**
     * The array managed by the SRA_ArrayManager object.
     * @type   Object
     * @access private
     */
    var $_data;
    // }}} 

    // {{{ SRA_ArrayManager()
    /**
     * Class constructor. Validates the data parameter and sets the _data
     * attribute.
     *
     * @param   data : Object - The array that the SRA_ArrayManager object
     * should manage.
     * @access  public
     * @return  
     */
    function SRA_ArrayManager(& $data) {
      if (!is_array($data)) {
        $msg = 'SRA_ArrayManager::SRA_ArrayManager: Failed - data parameter is not an array. It is of type \'' . gettype($data) . '\'';
			  $this->err =& SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_ENTITY_DEBUG);
      }
      else {
        $this->_data =& $data;
      }
    }
    // }}}

    // {{{ getData()
    /**
     * Returns the data associated with the keys parameter specified.
     * Returns an OPERATIONAL level SRA_Error object if the array element
     * specified by the keys does not exist.
     *
     * @param   keys : String[] - An array of keys representing which data
     * should be returned. The keys should be ordered in descending order.
     * In other words, the first key should be contained within the 1st
     * level of the array, the second within the second level and so on.
     * If this parameter is not provided, a reference to the entire _data
     * attribute will be returned.
     * @access  public
     * @return  Object
     * @author  Jason Read <jason@idir.org>
     */
    function & getData($keys=NULL)
    {
        if (isset($this->_data))
        {
			// Convert single elements to an array
			if ($keys && !is_array($keys))
			{
				$keys = array($keys);
			}
			
			// Return all data
			if (!$keys)
			{
				return $this->_data;
			}
			// Return sub-data
			else
			{
				$data =& $this->_data;
				foreach ($keys as $key)
				{
					// SRA_Error
					if (!is_scalar($key) || !is_array($data))
					{
						$msg = 'SRA_ArrayManager::getData: Failed - Invalid key: type \'' . gettype($key) . 
							   '\' or data: type\'' . gettype($data) . '\'';
						return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
					}
					// Sub data does not exist
					if (!array_key_exists($key, $data))
					{
						$msg = "SRA_ArrayManager::getData: Failed - Sub-key '$key' does not exist (Sub-keys - " . implode('::', $keys) . ')';
						return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL, SRA_CONTROLLER_DEBUG);
					}
					else
					{
						$temp =& $data;
						$data =& $temp[$key];
					}
				}
				$msg = 'SRA_ArrayManager::getData: Sub-keys - \'' . implode('::', $keys) . '\' found';
				SRA_Util::printDebug($msg, SRA_CONTROLLER_DEBUG, __FILE__, __LINE__);
				
				return $data;
			}
        }
		return NULL;
    }
    // }}}
	
    // {{{ getElement()
    /**
     * Returns a numerically indexed value in an array regardless of whether 
	 * or not it is associative
     *
	 * @param   array $arr The array to return the value from
     * @param	int $idx the index of the value to return
	 * @access  public
     * @return  string
     * @author  Jason Read <jason@idir.org>
     */
    function getElement(& $arr, $idx)
    {
			$keys = array_keys($arr);
			return $arr[$keys[$idx]];
    }
    // }}}
	
    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter is an 
     * SRA_ArrayManager object
     *
     * @param   object : Object - The object to validate.
	   * @access  public
     * @return  boolean
     */
    function isValid(& $object) {
      return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_arraymanager');
    }
    // }}}

    // {{{ merge()
    /**
     * This method may be used to merge two arrays each represented by an
     * SRA_ArrayManager object. The returned SRA_ArrayManager object will consist
     * of the contents of both arrays. If duplicate data exists, the data
     * within the 'primary' SRA_ArrayManager parameter will overwrite that in
     * the 'secondary' SRA_ArrayManager parameter.
     *
     * @param   primary : Object - The primary array. The data in
     * this array will overwrite any duplicate data in the secondary
     * parameter. This parameter may be an array or an SRA_ArrayManager object.
     * @param   secondary : Object - The second array object. The
     * data in this array will be overwritten by the data in the primary
     * array if duplicate data is found. This parameter may be an array or 
	 * an SRA_ArrayManager object.
     * @access  public
     * @return  SRA_ArrayManager
     * @author  Jason Read <jason@idir.org>
     */
    function & merge(& $primary, & $secondary)
    {
		// Validate parameters
		if ((!is_array($primary) && !SRA_ArrayManager::isValid($primary)) || 
			(!is_array($secondary) && !SRA_ArrayManager::isValid($secondary)))
		{
			$msg = 'SRA_ArrayManager::merge: Failed - primary or secondary parameters are not valid types. ' . 
				   'primary type: \'' . gettype($primary) . '\' secondary type: \'' . gettype($secondary) . '\'';
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
		}
		
        // Extract arrays from ArrayManger objects
		if (SRA_ArrayManager::isValid($primary))
		{
			$temp =& $primary;
			$primary =& $temp->getData();
		}
		if (SRA_ArrayManager::isValid($secondary))
		{
			$temp =& $secondary;
			$secondary =& $temp->getData();
		}
		
		// Go through each element in the secondary array and add it to the 
		// primary if it is not already defined there.
		$keys = array_keys($secondary);
		foreach ($keys as $key)
		{
			// Call method recursively for imbedded arrays
			if (array_key_exists($key, $primary) && is_array($primary[$key]) && 
				array_key_exists($key, $secondary) && is_array($secondary[$key]))
			{
				$temp =& SRA_ArrayManager::merge($primary[$key], $secondary[$key]);
				$primary[$key] =& $temp->getData();
			}
			else if (!array_key_exists($key, $primary))
			{
				$primary[$key] =& $secondary[$key];
			}
		}
		return new SRA_ArrayManager($primary);
    }
    // }}}
    
    
	// {{{ removeFromArray
	/**
	 * removes $val from $arr if it is present
	 * @param array $arr the array to remove $val from
   * @param mixed $val the value or values to remove
	 * @return array
	 */
	function removeFromArray($arr, $val) {
    $val = is_array($val) ? $val : array($val);
    $newArr = is_array($arr) ? array() : NULL;
    if (is_array($arr)) {
      foreach($arr as $key => $aval) {
        if (!in_array($aval, $val)) {
          $newArr[$key] = $aval;
        }
      }
    }
    return $newArr;
	}
	// }}}

}
// }}}

?>
