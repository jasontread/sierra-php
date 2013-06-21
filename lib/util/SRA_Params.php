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
 * keyword used to identify params with NO type
 * @type string
 */
define('SRA_PARAMS_NO_TYPE', 'no-type');

/**
 * delimeter between key and type for parameters
 * @type string
 */
define('SRA_PARAMS_TYPE_DELIM', '::');
// }}}

// {{{ SRA_Params
/**
 * Generic container for parameters
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_Params {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * holds all of the params managed by this object
	 * @type array
	 */
	var $_params = array();
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_Params
	/**
	 * Constructor
	 * @param array $params an array of parameters that this object should manage. 
	 * these must adhere to one of the following formats:
	 *	1) array of key/value pairs
	 * 	2) array containing the following sub-elements:
	 *			index of array (key value), type (optional), decrypt (optional), value
	 *  3) array containing the following sub-elements:
	 *			index of array (key value), attributes.type (optional), attributes.value
   * @access  public
	 */
	function SRA_Params($params = FALSE) {
		if (is_array($params)) {
			$keys = array_keys($params);
			foreach ($keys as $key) {
				if (array_key_exists('id', $params[$key]) && array_key_exists('value', $params[$key])) {
          $params[$key]['value'] = SRA_Params::parseValue($params[$key]['value']);
					if (isset($params[$key]['decrypt'])) $params[$key]['value'] = $params[$key]['decrypt']($params[$key]['value']);
					$this->_params[$this->_getPkey($params[$key]['id'], $params[$key]['type'])] = $params[$key];
				}
				else if (array_key_exists('id', $params[$key]['attributes'])) {
					$type=NULL ;
					if (isset($params[$key]['attributes']['type'])) {
						$type = $params[$key]['attributes']['type'];
					}
					if (isset($params[$key]['attributes']['decrypt'])) $params[$key]['attributes']['value'] = $params[$key]['attributes']['decrypt']($params[$key]['attributes']['value']);
					$this->_params[$this->_getPkey($params[$key]['attributes']['id'], $type)] = $params[$key]['attributes'];
				}
				else {
					if (strstr($key, SRA_PARAMS_TYPE_DELIM)) {
						$tmp = explode(SRA_PARAMS_TYPE_DELIM, $key);
						$this->_params[$key] = array('id' => $tmp[0], 'type' => $tmp[1], 'value' => SRA_Params::parseValue($params[$key]));
					}
					else {
						$this->_params[$key] = array('id' => $key, 'type' => FALSE, 'value' => SRA_Params::parseValue($params[$key]));
					}
				}
			}
		}
	}
	// }}}
	
  
  // public operations
	
	// {{{ append
	/**
	 * adds a new parameter to the params contained within this object. returns 
	 * TRUE if the parameter was appended successfully, FALSE otherwise
	 * @param string $key the param key
	 * @param mixed $value the param value
	 * @param string $type the param type
	 * @param boolean $override whether or not to override the parameter value if 
	 * it already exists
	 * @access public
	 * @return boolean
	 */
	function append($key, $value, $type=NULL, $override = TRUE) {
		$pkey = $this->_getPkey($key, $type);
		if (!$key || !$pkey || (!$override && isset($this->_params[$pkey]))) {
			return FALSE;
		}
		$this->_params[$pkey] = array('id' => $key, 'type' => $type, 'value' => SRA_Params::parseValue($value));
	}
	// }}}
  
	// {{{ concat
	/**
	 * adds or concatenates a value based on the params specified. if the 
   * $key/$type specified already exists, the new value will be concatenated 
   * to it, otherwise it will be added (if $noAdd is not TRUE)
	 * @param string $key the param key
	 * @param mixed $value the param value
   * @param string $lead if this parameter already exists, this value will be 
   * added between the 2 concatenated values
	 * @param string $type the param type
   * @param boolean $toEnd whether or not to concatenate this new value to the 
   * end or the front of the current value
   * @param boolean $noAdd when TRUE, if a value is not already present for the 
   * $key/$type specified, the value will not be added
	 * @access public
	 * @return boolean
	 */
	function concat($key, $value, $type=NULL, $toEnd=TRUE, $lead=' ', $noAdd=FALSE) {
		$pkey = $this->_getPkey($key, $type);
		if (!$key || !$pkey || ($noAdd && !isset($this->_params[$pkey]))) {
			return FALSE;
		}
    $prefix = $toEnd && isset($this->_params[$pkey]) ? $this->_params[$pkey]['value'] . $lead : '';
    $suffix = !$toEnd && isset($this->_params[$pkey]) ? $lead . $this->_params[$pkey]['value'] : '';
		$this->_params[$pkey] = array('id' => $key, 'type' => $type, 'value' => $prefix . SRA_Params::parseValue($value) . $suffix);
	}
	// }}}
	
	// {{{ getCount
	/**
	 * returns the # of parameters this SRA_Params instance has
	 * @return int
	 */
	function getCount() {
		return count($this->_params);
	}
	// }}}
	
	// {{{ getKeys
	/**
	 * returns the keys for the parameters
	 * @return string
	 */
	function getKeys() {
		return array_keys($this->_params);
	}
	// }}}
	
	// {{{ getParam
	/**
	 * returns the param value specified
	 * @param string $key the param name or key to return
	 * @param mixed $default the default value to return if the parameter specified 
	 * does not exist
	 * @param mixed $match if specified, the parameter value specified must match 
	 * this value for it to be returned, otherwise, $default will be returned
	 * @access public
	 * @return string
	 */
	function getParam($mkey, $default=FALSE, $match=FALSE) {
		if (isset($this->_params[$mkey])) {
			return $this->_params[$mkey]['value'];
		}
		$keys = array_keys($this->_params);
		$pkey = FALSE;
		foreach ($keys as $key) {
			if ($this->_params[$key]['id'] == $mkey) {
				$pkey = $key;
				break;
			}
		}
		return ($pkey && (!$match || ($match && $match == $this->_params[$pkey]['value']))) ? $this->_params[$pkey]['value'] : SRA_Params::parseValue($default);
	}
	// }}}
  
	// {{{ getParam1
	/**
	 * returns the param value specified
	 * @param string $key the id of the param to return
   * @param string $type the type of the param to return
	 * @param mixed $default the default value to return if the parameter specified 
	 * does not exist
	 * @param mixed $match if specified, the parameter value specified must match 
	 * this value for it to be returned, otherwise, $default will be returned
	 * @access public
	 * @return string
	 */
	function getParam1($id, $type, $default=FALSE, $match=FALSE) {
		return $this->getParam($this->_getPkey($id, $type), $default, $match);
	}
	// }}}
	
	// {{{ getParams
	/**
	 * returns all params as an associative array of key/value pairs
	 * @param mixed $type an optional type filter. only params of this specified 
	 * type will be returned. $type can also be an array of multiple types to 
	 * return. to return only params that do NOT have a type assigned, use a $type 
	 * SRA_PARAMS_NO_TYPE
	 * @param boolean $matchSubstring whether or not to return params where $type 
	 * is a substring (or equal to) that param's type
	 * @param boolean $appendType whether or not to append the parameter type to 
	 * its associative index using '::' as the delimeter
	 * @access public
	 * @return array
	 */
	function getParams($type=NULL, $matchSubstring = FALSE, $appendType = FALSE) {
		$params = array();
		$keys = array_keys($this->_params);
		if ($type && !is_array($type)) {
			$type = array($type);
			$tkeys = array_keys($type);
		}
		foreach ($keys as $key) {
			$substringMatch = FALSE;
			if ($matchSubstring && is_array($type) && count($type) && $this->_params[$key]['type']) {
				foreach ($tkeys as $tkey) {
					if (strstr($this->_params[$key]['type'], $type[$tkey])) {
						$substringMatch = TRUE;
						break;
					}
				}
			}
			if ($substringMatch || !$type || ($type && $this->_params[$key]['type'] && in_array($this->_params[$key]['type'], $type)) || 
					($type && !$this->_params[$key]['type'] && in_array(SRA_PARAMS_NO_TYPE, $type))) {
				if ($appendType && $this->_params[$key]['type']) {
					$params[$key] = $this->_params[$key]['value'];
				}
				else {
					$params[$this->_params[$key]['id']] = $this->_params[$key]['value'];
				}
			}
		}
		return $params;
	}
	// }}}
	
	
	// {{{ getParamType
	/**
	 * returns the type for a specified parameter
	 * @param string $key the key of the param to return the type for. this may 
	 * contain the type appended (or not)
	 * @access public
	 * @return string or FALSE
	 */
	function getParamType($key) {
		if (isset($this->_params[$key])) {
			$pieces = explode(SRA_PARAMS_TYPE_DELIM, $key);
			return $pieces[1];
		}
		$pkeys = array_keys($this->_params);
		foreach ($pkeys as $pkey) {
			if ($this->_params[$pkey]['id'] == $key || $pkey == $key) {
				return $this->_params[$pkey]['type'];
			}
		}
		return FALSE;
	}
	// }}}
  
  
	// {{{ getTypes
	/**
	 * returns an array corresponding with all of the types in this params object
	 * @access public
	 * @return array
	 */
	function getTypes() {
		$types = array();
		$keys = array_keys($this->_params);
		foreach ($keys as $key) {
			if (isset($this->_params[$key]['type']) && !in_array($this->_params[$key]['type'], $types)) { 
        $types[] = $this->_params[$key]['type']; 
      }
		}
		return $types;
	}
	// }}}
	
	
	// {{{ getTypeSubset
	/**
	 * returns a new SRA_Params object representing a type based subset of the params 
	 * in this object
	 * @param mixed $type the type subset to return
	 * @param boolean $matchSubstring whether or not to return params where $type 
	 * is a substring (or equal to) that param's type
	 * @access public
	 * @return SRA_Params
	 */
	function &getTypeSubset($type, $matchSubstring = FALSE) {
		$params = new SRA_Params();
		$ps = $this->getParams($type, $matchSubstring, TRUE);
		foreach ($ps as $key => $p) {
			$params->_params[$key] = $this->_params[$key];
		}
		return $params;
	}
	// }}}
	
	
	// {{{ hasParam
	/**
	 * returns TRUE if the param exists
	 * @param mixed $param the param to check
	 * @access public
	 * @return boolean
	 */
	function hasParam($mkey) {
		if (isset($this->_params[$mkey])) {
			return TRUE;
		}
		$keys = array_keys($this->_params);
		foreach ($keys as $key) {
			if ($this->_params[$key]['id'] == $mkey) {
				return TRUE;
			}
		}
		return FALSE;
	}
	// }}}
	
	
	// {{{ hasType
	/**
	 * returns TRUE if the type exists
	 * @param mixed $type the type to check
	 * @access public
	 * @return boolean
	 */
	function hasType($type) {
		return count($this->getParams($type)) > 0;
	}
	// }}}
	
	
	// {{{ toString
	/**
	 * returns a string version of this SRA_Params instance
	 * @access public
	 * @return String
	 */
	function toString() {
		return SRA_Util::objectToString($this);
	}
	// }}}
	
	
	// Private methods
	// {{{ _getPkey
	/**
	 * return the key that should be used for the parameter specified
	 * @param string $key the base parameter key
	 * @param string $type the parameter type 
	 * @access private
	 * @return string
	 */
	function _getPkey($key, $type) {
		$pkey = $key;
		if ($type) {
			$pkey .= SRA_PARAMS_TYPE_DELIM . $type;
		}
		return $pkey;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid
	/**
	 * static method that returns true if the object parameter is a SRA_Params 
   * object
	 * @param Object $object The object to validate
	 * @access public
	 * @return boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_params');
	}
	// }}}
  
	// {{{ parseValue
	/**
	 * parses a parameter value substituting any imbedded identifiers. these 
   * include the following:
   *  {SRA_DIR}: the path to the framework
   *  {APP_DIR}: the path to the active application
	 * @param string $value
	 * @access public
	 * @return string
	 */
	function parseValue($value) {
		return str_replace('{SRA_DIR}', SRA_DIR, str_replace('{APP_DIR}', SRA_Controller::getAppDir(), $value));
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
