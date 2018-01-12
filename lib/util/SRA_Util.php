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
/**
 * regular expression used to locate an ip address
 * @type string
 */
define('SRA_UTIL_IP_REGEX', '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/');

/**
 * the name of the aspell cli utility used by the getSpellCheckLanguages and 
 * spellcheck methods. aspell must be installed and present in the $PATH in 
 * order to use those methods
 * @type string
 */
define('SRA_UTIL_ASPELL', 'aspell');

/**
 * unit identifiers for a 'bit', this is the smallest unit size. the 
 * SRA_UTIL_DATA_UNIT_* constants are used by the SRA_Util::normalizeDataUnit 
 * method below
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_BIT', 'b');

/**
 * unit identifiers for a 'byte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_BYTE', 'B');

/**
 * unit identifiers for a 'kilobit'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_KILOBIT', 'Kb');

/**
 * unit identifiers for a 'kilobyte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_KILOBYTE', 'KB');

/**
 * unit identifiers for a 'megabit'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_MEGABIT', 'Mb');

/**
 * unit identifiers for a 'megabyte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_MEGABYTE', 'MB');

/**
 * unit identifiers for a 'gigabit'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_GIGABIT', 'Gb');

/**
 * unit identifiers for a 'gigabyte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_GIGABYTE', 'GB');

/**
 * unit identifiers for a 'terabit'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_TERABIT', 'Tb');

/**
 * unit identifiers for a 'terabyte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_TERABYTE', 'TB');

/**
 * unit identifiers for a 'petabit'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_PETABIT', 'Pb');

/**
 * unit identifiers for a 'petabyte'
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_PETABYTE', 'PB');

/**
 * space separated list of unit labels used to identify a bit. if a label is 
 * prefixed with : the search for it will not be case sensitive
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_BIT_LABELS', 'b,b/s,:bit,:bit/s,bs,,bS,bps,bpS,b/S,:bits,:bits/s,');

/**
 * space separated list of unit labels used to identify a byte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_BYTE_LABELS', 'B,B/s,:byte,:byte/s,,Bs,BS,Bps,BpS,B/S,:bytes,:bytes/s,');

/**
 * space separated list of unit labels used to identify a kilobit
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_KILOBIT_LABELS', 'Kb,Kb/s,:kilobit,:kilobit/s,kb,Kbs,kbs,KbS,kbS,Kbps,KbpS,kbps,kbpS,Kb/S,kb/s,kb/S,:kbit,:kbits,:kbit/s,:kilo bit,:kilobits,:kilobits/s,:kilo bits');

/**
 * space separated list of unit labels used to identify a kilobyte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_KILOBYTE_LABELS', 'KB,KB/s,:kilobyte,:kilobyte/s,kB,KBs,kBs,KBS,kBS,KBps,KBpS,kBps,kBpS,KB/S,kB/s,kB/S,:kbyte,:kbytes,:kbyte/s,:kilo byte,:kilobytes,:kilobytes/s,:kilo bytes');

/**
 * space separated list of unit labels used to identify a megabit
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_MEGABIT_LABELS', 'Mb,Mb/s,:megabit,:megabit/s,mb,Mbs,mbs,MbS,mbS,Mbps,MbpS,mbps,mbpS,Mb/S,mb/s,mb/S,:mbit,:mbits,:mbit/s,:mega bit,:megabits,:megabits/s,:mega bits');

/**
 * space separated list of unit labels used to identify a megabyte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_MEGABYTE_LABELS', 'MB,MB/s,megabyte,megabyte/s,mB,MBs,mBs,MBS,mBS,MBps,MBpS,mBps,mBpS,MB/S,mB/s,mB/S,:mbyte,:mbytes,:mbyte/s,:mega byte,megabytes,megabytes/s,:mega bytes');

/**
 * space separated list of unit labels used to identify a gigabit
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_GIGABIT_LABELS', 'Gb,Gb/s,:gigabit,:gigabit/s,gb,Gbs,gbs,GbS,gbS,Gbps,GbpS,gbps,gbpS,Gb/S,gb/s,gb/S,:gbit,:gbits,:gbit/s,:giga bit,:gigabits,:gigabits/s,:giga bits');

/**
 * space separated list of unit labels used to identify a gigabyte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_GIGABYTE_LABELS', 'GB,GB/s,:gigabyte,:gigabyte/s,gB,GBs,gBs,GBS,gBS,GBps,GBpS,gBps,gBpS,GB/S,gB/s,gB/S,:gbyte,:gbytes,:gbyte/s,:giga byte,:gigabytes,:gigabytes/s,:giga bytes');

/**
 * space separated list of unit labels used to identify a terabit
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_TERABIT_LABELS', 'Tb,Tb/s,:terabit,:terabit/s,tb,Tbs,tbs,TbS,tbS,Tbps,TbpS,tbps,tbpS,Tb/S,tb/s,tb/S,:tbit,:tbits,:tbit/s,:tera bit,:terabits,:terabits/s,:tera bits');

/**
 * space separated list of unit labels used to identify a terabyte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_TERABYTE_LABELS', 'TB,TB/s,:terabyte,:terabyte/s,tB,TBs,tBs,TBS,tBS,TBps,TBpS,tBps,tBpS,TB/S,tB/s,tB/S,:tbyte,:tbytes,:tbyte/s,:tera byte,:terabytes,:terabytes/s,:tera bytes');

/**
 * space separated list of unit labels used to identify a petabit
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_PETABIT_LABELS', 'Pb,Pb/s,:petabit,:petabit/s,pb,Pbs,pbs,PbS,pbS,Pbps,PbpS,pbps,pbpS,Pb/S,pb/s,pb/S,:pbit,:pbits,:pbit/s,:peta bit,:petabits,:petabits/s,:peta bits');

/**
 * space separated list of unit labels used to identify a petabyte
 * @type string
 */
define('SRA_UTIL_DATA_UNIT_PETABYTE_LABELS', 'PB,PB/s,:petabyte,:petabyte/s,pB,PBs,pBs,PBS,pBS,PBps,PBpS,pBps,pBpS,PB/S,pB/s,pB/S,:pbyte,:pbytes,:pbyte/s,:peta byte,:petabytes,:petabytes/s,:peta bytes');

/**
 * measurement identifiers for a 'centimeter'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_CENTIMETER', 'cm');

/**
 * measurement identifiers for a 'foot'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_FOOT', 'ft');

/**
 * measurement identifiers for an 'inch'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_INCH', 'in');

/**
 * measurement identifiers for a 'kilometer'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_KILOMETER', 'km');

/**
 * measurement identifiers for a 'meter'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_METER', 'm');

/**
 * measurement identifiers for a 'mile'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_MILE', 'mi');

/**
 * measurement identifiers for a 'yard'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_YARD', 'yd');

/**
 * measurement identifiers for a 'gram'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_GRAM', 'gm');

/**
 * measurement identifiers for a 'kilogram'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_KILOGRAM', 'kg');

/**
 * measurement identifiers for an 'ounce'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_OUNCE', 'oz');

/**
 * measurement identifiers for a 'pound'
 * @type string
 */
define('SRA_UTIL_MEASUREMENT_POUND', 'lb');

/**
 * the root username
 * @type string
 */
define('SRA_UTIL_ROOT', 'root');

/**
 * path to the services config file used by SRA_Util::getServiceName($port)
 * @type string
 */
define('SRA_UTIL_SERVICES_CONFIG', '/etc/services');

/**
 * string token that identifies an invalid password in SRA_Util::suCmds
 * @type string
 */
 define('SRA_UTIL_SU_CMDS_INVALID_PSWD', 'incorrect password|: Authentication failure');

/**
 * string token that identifies an invalid user in SRA_Util::suCmds
 * @type string
 */
 define('SRA_UTIL_SU_CMDS_INVALID_USER', 'not exist|Unknown id:');

/**
 * the name of the temporary script created by SRA_Util::suCmds
 * @type string
 */
define('SRA_UTIL_SU_CMDS_SCRIPT', '.su-cmds');

/**
 * string token that identifies the password prompt in SRA_Util::suCmds and 
 * SRA_Util::suPswd
 * @type string
 */
define('SRA_UTIL_SU_PASSWORD', '"assword:"');

/**
 * string token that identifies an bad password in SRA_Util::suPswd
 * @type string
 */
define('SRA_UTIL_SU_PSWD_BAD', 'BAD');

/**
 * string token that identifies the retype password command in SRA_Util::suPswd
 * @type string
 */
define('SRA_UTIL_SU_PSWD_RETYPE', 'etype');

/**
 * The value to use in the printDebug method to identify that the app debug flag should be used
 * @type int
 */
define('SRA_UTIL_USE_APP_CONFIG_DEBUG', -1);

/**
 * The value to use in the printDebug method to identify that the system debug flag should be used
 * @type int
 */
define('SRA_UTIL_USE_SYS_CONFIG_DEBUG', -2);

/**
 * regular expression used to strip out non-url-safe characters
 * @type string
 */
define('SRA_UTIL_URL_SAFE_REGEX', '|[^\w-_\.]|');

// }}}

// {{{ Includes
// }}}

// {{{ SRA_Util
/**
* This class is used to house random methods that do not logically belong in any other classes.
* Generally these methods will be used for minor calculations and processes that may be useful
* to other classes. All of the methods in this class are static.
*
* @author:  Jason Read <jason@idir.org>
* @package sierra.util
*/
class SRA_Util {
    // {{{ Properties

    // }}}

    // {{{ addDelimString()
    /**
     * This method is used add or imbed a delimeter string. For example, if parsing a csv file you
     * would want to use this method to convert commas in between quotes to a delim string that
     * could later be converted back to a comma. This method would perform that function.
     *
     * @param   string - The String to add the delimeter string to
     * @param	delim - The delimeter to imbed a delimeter string for (by default a comma).
     * @param	quote - The quote for the string (only delims between this will be replaced).
     * 					The default is a double quote.
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function addDelimString( $string, $delim=",", $quote='"' )
    {
        $temp=explode($quote, $string);
        for ($i=1; $i<count($temp); $i+=2)
            $temp[$i]=str_replace($delim, "<delim>", $temp[$i]);

        return implode($quote, $temp);
    }
    // }}}

    // {{{ addSqlOption()
    /**
     * This static method adds an option to a sql statement. It uses the WHERE clause if no options
     * exist in the sql statement. Otherwise it uses the AND clause.
     *
     * @param   sql - The sql statement to add the option to.
     * @param	option - The option to add to the sql statement.
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function addSqlOption( $sql, $option )
    {
        if (strpos($sql, "WHERE"))
            $sql.=" AND " . $option;
        else
            $sql.=" WHERE " . $option;
        return $sql;
    }
    // }}}
    
    // {{{ applyLimitOffset
    /**
     * used to apply limit and offset contraints to an array. returns a new 
     * array with those constraints applied
     * @param array $arr the array to apply the limit/offset to
     * @param int $limit the limit (if none, no limit will be applied)
     * @param int $offset the offset (if none, no offset will be applied)
     * @param boolean $preserveKeys whether or not to preserve the keys in $arr
     * @access	public static
     * @return	array
     */
    function &applyLimitOffset(& $arr, $limit, $offset, $preserveKeys=FALSE) {
      $newArray = array();
      $keys = array_keys($arr);
      $offsetCounter = 0;
      $counter = 0;
      foreach($keys as $key) {
        if (!$offset || ($offset && $offsetCounter >= $offset)) {
          $counter++;
          $preserveKeys ? $newArray[$key] =& $arr[$key] : $newArray[] =& $arr[$key];
          if ($limit && $counter == $limit) { break; }
        }
        $offsetCounter++;
      }
      return $newArray;
    }
    // }}}

    // {{{ arrayMerge()
    /**
     * This method merges two arrays based on the parameters specified.
     *
     * @param   master : String[] - The master array to merge from. This will be a key based array.
     * @param	merge : String[] - The merge array. This will also be a key based array. At the end of the method
     * 			all of the key based values in the master array will exist in this array.
     * @param	overwriteExisting : boolean - Optional parameter that specifies whether or not existing values should
     * 			be overwritten if then exist already in the merge array. The default value for this is true.
     * @access	public static
     * @return	String[]
     * @author	Jason Read <jason@idir.org>
     */
    function &arrayMerge( & $master, $merge, $overwriteExisting=true )
    {
        $keys = array_keys($master);
        foreach ($keys as $key)
        {
            if (is_array($master[$key]))
            {
                if (!array_key_exists($key, $merge) || !is_array($merge[$key]))
				{
                    $merge[$key] = array();
				}
                $merge[$key] =& SRA_Util::arrayMerge($master[$key], $merge[$key], $overwriteExisting);
            }
            else if (is_array($merge) && (!array_key_exists($key, $merge) || $overwriteExisting))
			{
                $merge[$key] = $master[$key];
			}
        }
        return $merge;
    }
    // }}}
	
    // {{{ arrayToCsv()
    /**
     * This method converts an array of arrays of strings to a csv formatted string. Each row in 
	 * the array will be separated by a line break "\n" and each column will be separated by a 
	 * comma and surrounded by double quotes (if the useQuotes parameter is true).
     *
     * @param   report : String[][] - An array of string arrays. Each array in the first array will 
	 * be considered a row and each element in the second arrays will be considered a column. 
     * @param	useQuotes : boolean - Whether or not each column should be double quote delimited. 
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function &arrayToCsv( & $report, $useQuotes = true )
    {
		// Validate table
        if (!is_array($report))
		{
			$msg = "SRA_Util::arrayToCsv: Failed - report parameter is not an array: '" . gettype($report) . "'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SAP_APPLICATION_DEBUG);
		}
		
		$keys = array_keys($report);
		$buffer = "";
		$firstRow = true;
		foreach ($keys as $key)
		{
			// Validate row
			if (!is_array($report[$key]))
			{
				$msg = "SRA_Util::arrayToCsv: Failed - report row is not an array: '" . gettype($report[$key]) . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SAP_APPLICATION_DEBUG);
			}
			$tkeys = array_keys($report[$key]);
			$start = true;
			foreach ($tkeys as $tkey)
			{
				if ($start)
				{
					if (!$firstRow)
					{
						$buffer .= "\r\n";
					}
					else
					{
						$firstRow = false;
					}
					$start = false;
				}
				else
				{
					$buffer .= ",";
				}
				if ($useQuotes && strstr($report[$key][$tkey], ","))
				{
					$buffer .= "\"";
					$buffer .= str_replace("\"", "'", $report[$key][$tkey]);
				}
				else
				{
					$buffer .= $report[$key][$tkey];
				}
				if ($useQuotes && strstr($report[$key][$tkey], ","))
				{
					$buffer .= "\"";
				}
			}
		}
		return $buffer;
    }
    // }}}
    
	// {{{ toJson
	/**
	 * converts $attr to the appropriate representation in a javascript evaluable 
   * string. the global variables $_utilDateFormat and $_utilTimeFormat 
   * may be specified to use an alternate date/time format
	 * @param mixed $attr the attribute to render
   * @param array $include if $attr is an entity, the attributes to render
   * @param array $exclude if $attr is an entity, the attributes not to render
   * @param boolean $javascriptDate whether or not is $attr is a 
   * SRA_GregorianDate object, it should be converted to a javascript Date 
   * object
   * @param boolean $skipKeyQuotes whether or not to surround hash keys with 
   * double quotes
   * @access  public
	 * @return String
	 */
	function toJson(& $attr, $include=NULL, $exclude=NULL, $javascriptDate=FALSE, $skipKeyQuotes=FALSE) {

    global $_utilDateFormat;
    global $_utilTimeFormat;
    
    $js = '';
		if (is_array($attr)) {
      $numeric = SRA_Util::isNumericArray($attr);
      $js .= $numeric ? '[' : '{';
      $keys = array_keys($attr);
      foreach($keys as $key) {
        $js .= $keys[0] == $key ? '' : ', ';
        $js .= $numeric ? '' : ($skipKeyQuotes ? $key : '"' . str_replace('"', '\"', $key) . '"') . ': ';
        $js .= SRA_Util::toJson($attr[$key], $include, $exclude, $javascriptDate, $skipKeyQuotes);
      }
      $js .= $numeric ? ']' : '}';
    }
    else {
      if ((is_numeric($attr) && !SRA_Util::beginsWith($attr, '+') && (substr($attr, 0, 2) == '0.' || !SRA_Util::beginsWith($attr, '0'))) || preg_match('/new Date\((.*)\)/', $attr)) {
        $js .= $attr;
      }
      else if (is_bool($attr)) {
        $js .= $attr ? 'true' : 'false';
      }
      else if (class_exists('SRA_GregorianDate') && SRA_GregorianDate::isValid($attr) && $javascriptDate) {
        $js .= 'new Date(' . $attr->getYear() . ', ' . ($attr->getMonth() - 1) . ', ' . $attr->getDay() . (!$attr->isDateOnly() ? ', ' . $attr->getHour() . ', ' . $attr->getMinute() . ', ' . $attr->getSecond() : '') . ')';
      }
      else if (class_exists('SRA_GregorianDate') && SRA_GregorianDate::isValid($attr)) {
        $js .= '"' . ($attr->isDateOnly() && $_utilDateFormat ? $attr->format($_utilDateFormat) : (!$attr->isDateOnly() && $_utilTimeFormat ? $attr->format($_utilTimeFormat) : $attr->toString())) . '"';
      }
      else if (class_exists('SRA_FileAttribute') && SRA_FileAttribute::isValid($attr)) {
        $xmlArray = $attr->toXmlArray();
        $js .= SRA_Util::toJson($xmlArray['attributes']);
      }
      else if (is_object($attr) && method_exists($attr, 'toJson')) {
        $js .= $attr->toJson($include, $exclude, NULL, $javascriptDate, $skipKeyQuotes);
      }
      else if ($attr) {
        $js .= '"' . str_replace("\r", '\n', str_replace("\n", '\n', str_replace('"', '\"', str_replace('\\', '\\\\', $attr)))) . '"';
      }
      else {
        $js .= 'null';
      }
    }
    return $js;
	}
	// }}}
  
	// {{{ attrToXml
	/**
	 * converts $attr to the appropriate representation in a xml renderable array. 
   * the global variables $_utilDateFormat and $_utilTimeFormat may be 
   * specified to use an alternate date/time format
	 * @param mixed $attr the attribute to render
   * @param array $include if $attr is an entity, the attributes to render
   * @param array $exclude if $attr is an entity, the attributes not to render
   * @param string $fileElementName the element name to use for file attributes
   * @param boolean $usePrimaryKey whether or not to use primary key to 
   * serialize entities (otherwise the 'toXmlArray' method will be used)
   * @param boolean $camelCase whether or not to force the xml attributes and 
   * elements to camel case (otherwise, model defined format will be used)
   * @param string $ikey optional parameter used internally for recursive calls
   * @access public
	 * @return string
	 */
	function attrToXml(& $attr, $include, $exclude, $fileElementName=NULL, $usePrimaryKey=FALSE, $camelCase=FALSE, $ikey=NULL) {
    global $_utilAttrToXmlSoap;
    global $_utilDateFormat;
    global $_utilTimeFormat;
    
    $xml = NULL;
		if (is_array($attr)) {
      $xml = '<array' . ($ikey ? ' key="' . htmlspecialchars($ikey) . '"' : '') . ">\n";
      $isHash = SRA_Util::isHash($attr);
      foreach(array_keys($attr) as $key) {
        if ((is_object($attr[$key]) && method_exists($attr, 'toXmlArray')) || is_array($attr[$key])) {
          $val = SRA_Util::attrToXml($attr[$key], $include, $exclude, $fileElementName, $usePrimaryKey, $camelCase, $isHash ? $key : NULL);
          $xml .= $val . "\n";
        }
      }
      foreach(array_keys($attr) as $key) {
        if ((!is_object($attr[$key]) || !method_exists($attr, 'toXmlArray')) && !is_array($attr[$key])) {
          $val = SRA_Util::attrToXml($attr[$key], $include, $exclude, $fileElementName, $usePrimaryKey, $camelCase);
          $tval = trim($val);
          if (!SRA_Util::beginsWith($tval, '<') || !SRA_Util::endsWith($tval, '>') || SRA_Util::beginsWith($tval, '<![CDATA[')) {
            $val = '<' . ($camelCase ? 'arrayItem' : 'array-item') . ($isHash ? ' key="' . htmlspecialchars($key) . '"' : '') . '>' . $val . '</' . ($camelCase ? 'arrayItem' : 'array-item') . ">\n";
          }
          $xml .= $val;
        }
      }
      $xml .= "</array>\n";
    }
    else {
      if (is_numeric($attr)) {
        $xml = $attr;
      }
      else if (is_bool($attr)) {
        $xml = $attr ? '1' : '0';
      }
      else if (SRA_GregorianDate::isValid($attr)) {
        $xml = $_utilAttrToXmlSoap ? $attr->format('c') : ($attr->isDateOnly() && $_utilDateFormat ? $attr->format($_utilDateFormat) : (!$attr->isDateOnly() && $_utilTimeFormat ? $attr->format($_utilTimeFormat) : $attr->toString()));
      }
      else if (SRA_FileAttribute::isValid($attr)) {
        $xml = SRA_XmlParser::arrayToXML($attr->toXmlArray($fileElementName));
      }
      else if (is_object($attr) && method_exists($attr, 'toXmlArray')) {
        $xml = $usePrimaryKey && method_exists($attr, 'getPrimaryKey') && $attr->entityHasPersistence() ? $attr->getPrimaryKey() : SRA_XmlParser::arrayToXML($attr->toXmlArray($include, $exclude, $usePrimaryKey, $camelCase));
      }
      else if ($attr) {
        $xml = '<![CDATA[' . $attr . ']]>';
      }
    }
    return $xml;
	}
	// }}}
  
	// {{{ isHash
	/**
	 * returns TRUE if $arr is a hash (array indexes do not start with 0 and 
   * increment)
	 * @param array $arr the array to check
   * @access public
	 * @return boolean
	 */
	function isHash(&$arr) {
    if (is_array($arr)) {
      $idx = 0;
      foreach(array_keys($arr) as $key) {
        if ($idx++ !== $key) { return TRUE; }
      }
    }
    return FALSE;
	}
	// }}}
		
	// {{{ beginsWith
	/**
	 * returns TRUE if the $str specified begins with $substr
	 * @param string $str the string to chec
	 * @param string $substr the string to check for
	 * @param boolean $caseSensitive whether or not case sensitive
   * @access  public
	 * @return boolean
	 */
	function beginsWith($str, $substr, $caseSensitive = TRUE) {
		if (!$caseSensitive) {
			$str = strtolower($str);
			$substr = strtolower($substr);
		}
		return (substr($str, 0, strlen($substr)) == $substr);
	}
	// }}}
	
	// {{{ endsWith
	/**
	 * returns TRUE if the $str specified ends with $substr
	 * @param string $str the string to chec
	 * @param string $substr the string to check for
	 * @param boolean $caseSensitive whether or not case sensitive
   * @access  public
	 * @return boolean
	 */
	function endsWith($str, $substr, $caseSensitive = TRUE) {
		if (!$caseSensitive) {
			$str = strtolower($str);
			$substr = strtolower($substr);
		}
		return (substr($str,-strlen($substr)) == $substr);
	}
	// }}}
  
	// {{{ arrayMoveToTop
	/**
	 * moves the array element $key to the top of the array and returns the new 
   * array
	 * @param array $arr the array
	 * @param string $key the hash key of the element to move
   * @access public
	 * @return array
	 */
	function &arrayMoveToTop(&$arr, $key) {
		if (is_array($arr) && $key && isset($arr[$key])) {
      $narr = array($key => $arr[$key]);
      foreach(array_keys($arr) as $i) {
        if ($i != $key) {
          $narr[$i] = $arr[$i];
        }
      }
      return $narr;
    }
    else {
      return $arr;
    }
	}
	// }}}

    // {{{ bufferArray()
    /**
     * This method is used to convert an array into text (which may be imported into php at some
     * point in the future to become an array). This method supports any number of levels of arrays.
     *
     * @param   array. Array. The array to buffer.
	 * @param	name. String. The name to give the array.
	 * @param	tabs. int. The number of spaces to indent each successive level.
	 * @param	recursive. boolean. Whether or not this is a recursive call.
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function bufferArray( & $array, $name, $indent=2, $recursive = false )
    {
		if (!$recursive)
		{
			$buffer = '$' . $name . " = array(";
		}
		else
		{
			$buffer = "array(";
		}

        foreach ($array as $key => $value)
        {
            if (isset($addComma))
            {
                $buffer .= ",\n";
                for ($i=0; $i<=$indent; $i++)
				{
                    $buffer .= " ";
				}
            }
			
			if (!is_int($key))
			{
				$key = "'" . str_replace("'", "\\'", $key) . "'";
			}
			
            if (is_array($value))
			{
                $buffer .= $key . " => \n";
                for ($i=0; $i<=$indent; $i++)
				{
                    $buffer .= " ";
				}
				$buffer .= SRA_Util::bufferArray($value, $name, $indent * 2, true);
			}
            else
			{
				if (!is_int($value))
				{
					$value = str_replace("'", "\\'", $value);
          $value = "'" . str_replace("\\\\'", "\\'", $value) . "'";
				}
                $buffer .= $key . " => " . $value;
			}
            $addComma=true;
        }
        $buffer .= ")";
		if (!$recursive)
		{
			$buffer .= ";";
		}
        return($buffer);
    }
    // }}}
	
		
	// {{{ camelCaseToDashes
	/**
	 * returns the camel case variable name in a dash format
	 * e.g. myVariableName would return my-variable-name
	 * @param string $str the string to convert
	 * @param boolean $upper whether or not the new value should be all uppercase 
	 * default is all lowercase
   * @access  public
	 * @return String
	 */
	function camelCaseToDashes($str, $upper = FALSE) {
		return SRA_Util::camelCaseConvert($str, '-', $upper);
	}
	// }}}
	
	
	// {{{ camelCaseToUnderscores
	/**
	 * returns the camel case variable name in an underscore format
	 * e.g. myVariableName would return my_variable_name
	 * @param string $str the string to convert
	 * @param boolean $upper whether or not the new value should be all uppercase 
	 * default is all lowercase
   * @access  public
	 * @return String
	 */
	function camelCaseToUnderscores($str, $upper = FALSE) {
		return SRA_Util::camelCaseConvert($str, '_', $upper);
	}
	// }}}
  
  
	// {{{ camelCaseConvert
	/**
	 * converts a camel case (ie 'myNameIsBob') value into a fixed case value using 
   * $delim as the value to place between each camel case occurrence. for 
   * example, if $delim was '_' the value above would become 'my_name_is_bob'
	 * @param string $str the string to convert
   * @param string $delim the delimeter to insert between each camel case occurrence
	 * @param boolean $upper whether or not the new value should be all uppercase 
	 * default is all lowercase
   * @access  public
	 * @return String
	 */
	function camelCaseConvert($str, $delim, $upper = FALSE) {
		$newStr = '';
		for($i=0; $i<strlen($str); $i++) {
      $char = substr($str, $i, 1);
			if ($char != $delim && !$lastCap && strtoupper($char) == $char && $i > 0 && !is_numeric($char)) {
				$newStr .= $delim;
			}
      $lastCap = strtoupper($char) == $char && !is_numeric($char);
			$newStr .= $char;
		}
		if ($upper) {
			$newStr = strtoupper($newStr);
		}
		else {
			$newStr = strtolower($newStr);
		}
		return $newStr;
	}
	// }}}
  
  
	// {{{ checkBrowserCompatibility
	/**
	 * checks for browser compatibility and displays an error page when an 
   * incompatible browser is encountered. when the php script is being run at 
   * the command line, the compatibility check will be bypassed
	 * @param mixed $browsers either a text file containing line separated list of 
   * supported browsers or an array of supported browsers. for example: 
   * array('Firefox', 'Safari')
   * @param string $errorTpl the template to display when an incompatible 
   * browser is encountered
   * @access  public
	 * @return void
	 */
	function checkBrowserCompatibility($supportedBrowsers, $errorTpl) {
		if (!is_array($supportedBrowsers) && file_exists($supportedBrowsers)) {
      $supportedBrowsers = file($supportedBrowsers);
    }
    if (!isset($_SERVER['argv'][0]) && is_array($supportedBrowsers) && count($supportedBrowsers)) {
      $found = FALSE;
      foreach($supportedBrowsers as $supportedBrowser) {
        if (!SRA_Util::beginsWith($supportedBrowser, '#') && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), strtolower(trim($supportedBrowser))) !== FALSE) {
          $found = TRUE;
          break;
        }
      }
      if (!$found) {
        $tpl =& SRA_Controller::getAppTemplate();
        $tpl->display($errorTpl);
        exit;
      }
    }
	}
	// }}}
		
		
	// {{{ codeToString()
	/**
	 * This method converts a php code string into the resultant string (results of executing the 
	 * php code). This function must also reside outside of the SRA_Util class in order for the regular 
	 * expression call back function to work.
	 *
	 * @param   $code. String. The php code to execute. This should only contain 1 line of code with 
	 * 			or without the trailing ;
	 * @access	public static
	 * @return	String
	 * @author	Jason Read <jason@idir.org>
	 */
	function codeToString( $code )
	{
		$code = is_array($code) ? $code[1] : $code;
		// Check if is valid
		if (!is_string($code)) {
			return(SRA_Error::logError("SRA_Util::codeToString: Failed - Invalid code: '$code'", 
								   __FILE__, __LINE__, SRA_ERROR_PROBLEM));
		}
		// Add semi-colon
		$code = trim($code);
		if (substr($code, strlen($code) - 1 , 1) != ";") {
			$code .= ";\n";
		}
		$code = '$value = ' . $code;
    eval($code);
    return isset($value) ? $value : SRA_Error::logError("SRA_Util::codeToString: Failed - Invalid code: '$code'", __FILE__, __LINE__, SRA_ERROR_PROBLEM);

	}
	// }}}
	
    // {{{ compareBitMasks()
    /**
     * This method compares two bitmasks. It returns true if all of the bits specified in the mask 
	 * parameter are also specified in the compare parameter. 
     *
	 * @param   $mask. int. The bit mask containing the bits that need to be verified.
	 * @param   $compare. int. The bits to validate.
	 * @param   $full. int. Bitmask containing all of the possible bits for the mask parameter set.
     * @access	public static
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
	function compareBitMasks( $mask, $compare, $full )
	{
		for ($i=1; $i<$full; $i*=2)
		{
			echo "checking $i \n";
			// Bit occurs in compare mask
			if ($i & $compare)
			{
				echo "$i occurs in compare mask\n";
				if (!($i & $mask))
				{
					echo "$i does not occur in mask, failed\n";
					return false;
				}
				else
				{
					echo "$i occurs in mask, continuing\n";
				}
			}
			else
			{
				echo "$i does not occur in compare mask\n";
			}
		}
		return true;
	}
	// }}}
	
	// {{{ isBoolean()
	/**
	 * returns TRUE if $bool is a valid representation of the boolean data type
	 * the following are considered valid representations:
	 *	 boolean constants TRUE/FALSE
	 *   integers 1/0
	 *	 strings '1'/'0'
	 *   strings 'true'/'false' (not case sensitive)
	 *   strings 't'/'f'
	 *   strings 'yes'/'no'
	 *   strings 'y'/'n'
   *   strings 'on'/'off'
	 *
	 * @param   bool - The boolean string to convert
	 * @access	public static
	 * @return	boolean
	 */
	function isBoolean($bool) {
		$lbool = strtolower($bool);
		return (gettype($bool) == gettype(TRUE) || $bool === 1 || $bool === 0 || 
						$lbool === 'true' || $lbool === 'false' || $lbool === 't' || $lbool === 'f' || 
						$lbool === 'yes' || $lbool === 'no' || $lbool === 'y' || $lbool === 'n' || 
						$lbool === '1' || $lbool === '0' || $lbool === 'on' || $lbool === 'off');
	}
	// }}}

	// {{{ convertBoolean()
	/**
	 * returns TRUE if $bool is a valid boolean data type representation of TRUE
	 * (see isBoolean api documentation for more details on what is considered 
	 * a valid boolean data type representation). returns NULL if $bool is not a 
   * valid boolean type
	 * @param mixed $bool The boolean string to convert
   * @param boolean $default the default value to return
	 * @access public static
	 * @return mixed
	 */
	function convertBoolean($bool, $default=NULL) {
		return SRA_Util::isBoolean($bool) ? $bool === TRUE || $bool === 1 || 
						strtolower($bool) === 'true' || strtolower($bool) === 't' || 
            strtolower($bool) === 'yes' || strtolower($bool) === 'y' || 
            $bool === '1' || strtolower($bool) === 'on' : $default;
	}
	// }}}
	
    // {{{ extractArrayValue()
    /**
     * Extracts and returns the value in the array identified by the key parameter. 
	 * Returns the return parameter (default is NULL) is that array key is not set.
     *
     * @param   array : Object[] - The array from which the value should be 
	 * 			extracted.
	 * @param   key : String - The array key identifying the value that should be 
	 * 			extracted.
	 * @param   typeCast : String - A type cast to apply to the value. This parameter is optional. By default no type cast is applied.
	 * @param   returnValue : Object - The value to return if the array key specified 
	 * 			is not set. This is an optional parameter. The default value is NULL.
     * @access	public static
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function &extractArrayValue( & $array, $key, $typeCast = NULL, $returnValue = NULL )
    {
        if (!is_array($array) || !is_scalar($key))
		{
			return SRA_Error::logError("SRA_Util::extractArrayValue: Failed - array '" . gettype($array) . 
								   "' parameter is not an array or key '$key' is not scalar", 
								   __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		
		if (array_key_exists($key, $array))
		{
			switch ($typeCast)
			{
				case "int" : 
				case "integer" : 
					return (int) $array[$key];
				case "bool" : 
				case "boolean" : 
					return (bool) $array[$key];
				case "string" : 
					return (string) $array[$key];
				case "float" : 
				case "double" : 
				case "real" : 
					return $array[$key];
				case "array" : 
					return (array) $array[$key];
				case "object" : 
					return (object) $array[$key];
				default:
					return $array[$key];
			}
		}
		return $returnValue;
    }
    // }}}
	
    // {{{ getArrayLocation()
    /**
     * Returns a reference to a location in an array containing the value specified. 
	 * Returns an OPERATIONAL SRA_Error object if the key is not found. 
     *
	 * @param	array : Object - The array to search for the value in
	 * @param	value : String - The value to search for
	 * @param	recursive : boolean - Optional parameter specifying whether or not 
	 * the value should be searched for recursively. The default value for this 
	 * parameter is false.
	 * @param	arrayKey : String - Optional parameter specifying the element key name. 
	 * If this parameter is not specified, the array key will be used. 
     * @access  public static
     * @return  Object
     * @author  Jason Read <jason@idir.org>
     */
    function &getArrayLocation(& $array, $value, $recursive = false, $arrayKey = NULL)
    {
		// Validate parameters
        if (!is_array($array) || !is_scalar($value) || !is_bool($recursive) || ($key && !is_scalar($key)))
		{
            $msg = 'SRA_Util::getArrayLocation: Failed - Invalid parameters: array: \'' . gettype($array) . 
				   '\' value: \'' . gettype($value) . '\' recursive: \'' . gettype($recursive) . '\' key: \'' . 
				   gettype($key) . '\'';
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));
		}
		
		$keys = array_keys($array);
		foreach ($keys as $key)
		{
			// Use array keys
			if (!$arrayKey && $key == $value)
			{
				return $array[$key];
			}
			else if ($arrayKey && is_array($array[$key]))
			{
				$akeys = array_keys($array[$key]);
				foreach ($akeys as $akey)
				{
					if ($akey == $arrayKey && !is_array($array[$key][$akey]) && $array[$key][$akey] == $value)
					{
						return $array[$key];
					}
					else if ($recursive && is_array($array[$key][$akey]))
					{
						if (!SRA_Error::isError($temp =& SRA_Util::getArrayLocation($array[$key][$akey], $value, true, $arrayKey)))
						{
							return $temp;
						}
					}
				}
			}
			else if (!$arrayKey && $recursive && is_array($array[$key]))
			{
				if (!SRA_Error::isError($temp =& SRA_Util::getArrayLocation($array[$key], $value, true)))
				{
					return $temp;
				}
			}
		}
		
		// Array key not found
		$msg = "SRA_Util::getArrayLocation: Failed - Unable to locate value '$value'";
		return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL));
		
    }
    // }}}
		
  // {{{ getFileExtension
  /**
   * Returns the file extension for a given file (the string following the 
   * last .) or empty string if there is not extension
   * @param string $fileName the file to return the extension of
   * @param	boolean $urlSafe whether or not to make the return value url safe
   * @access public static
   * @return string
   */
  function getFileExtension($fileName, $urlSafe = FALSE) {
    $pieces = explode('.', $fileName);
    if ($urlSafe) {
      return preg_replace(SRA_UTIL_URL_SAFE_REGEX, '', $pieces[count($pieces) - 1]);
    }
    else {
      return count($pieces) > 1 ? $pieces[count($pieces) - 1] : '';
    }
  }
  // }}}
		
  // {{{ getFileNameWOExtension
  /**
   * Returns the file extension for a given file (the string following the 
   * last .)
   * @param string $fileName the file to return the extension of
   * @param	boolean $urlSafe whether or not to make the return value url safe
   * @access  public static
   * @return  String
   */
  function getFileNameWOExtension($fileName, $urlSafe = FALSE) {
    $pieces = explode('.', basename($fileName));
    $fileName = '';
    $started = FALSE;
    for($i=0; $i<count($pieces) - 1; $i++) {
      if ($started) {
        $fileName .= '.';
      }
      $fileName .= $pieces[$i];
      $started = TRUE;
    }
    if ($urlSafe) {
      return preg_replace(SRA_UTIL_URL_SAFE_REGEX, '', $fileName);
    }
    else {
      return $fileName;
    }
  }
  // }}}
	
    // {{{ getPhpIniMemoryLimit()
    /**
     * This method returns the memory_limit specified in the php.ini file in
     * bytes.
     *
     * @access  public static
     * @return  String
     * @author  Jason Read <jason@idir.org>
     */
    function getPhpIniMemoryLimit()
    {
        $size = ini_get('memory_limit');

        if ($size == "")
        {
            // SRA_Error.
            $msg = "SRA_Util::getIniMemoryLimit() SRA_Error getting the memory_limit value from php.ini. Did you configure PHP with --enable-memory-limit?";
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));
        }

        $scan['MB'] = 1048576;
        $scan['M']  = 1048576;
        $scan['KB'] = 1024;
        $scan['K']  = 1024;

        while (list($key) = each($scan))
        {
            if ((strlen($size) > strlen($key)) && (substr($size,strlen($size) - strlen($key))==$key))
            {
                $size = substr($size, 0, strlen($size) - strlen($key)) * $scan[$key];
                break;
            }
        }
        return($size);
    }
    // }}}
    
    // {{{ getPrefixedArrayValues
    /**
     * returns a sub-array containing all of the values in $arr that are 
     * are prefixed with $prefix
     * @param string $prefix the prefix value
     * @param array $arr the array to search
     * @param boolean $removePrefix whether or not to remove $prefix from the 
     * return values. default is TRUE
     * @return	array
     */
    function &getPrefixedArrayValues($prefix, $arr, $removePrefix=TRUE) {
      $newArr = array();
      foreach($arr as $val) {
        if (strpos($val, $prefix) === 0) {
          $newArr[] = $removePrefix ? substr($val, strlen($prefix)) : $val;
        }
      }
      return $newArr;
    }
    // }}}
    
  // {{{ getCurrentUser
  /**
   * same as the php 'get_current_user' function but returns the correct 
   * username for child processes (the 'get_current_user' function returns the 
   * username for the parent process even if the child process owner is 
   * different)
   * @return string
   */
  function getCurrentUser() {
    $tmp = posix_getpwuid(posix_geteuid());
    return $tmp['name'];
  }
  // }}}
    
  // {{{ getProcessAttr
  /**
   * returns a process attribute
   * @param string $attr the attribute to return. some examples of attributes 
   * are: %cpu, %mem, egid, egroup, etime, euid, euser, ppid, args. for more 
   * information, see the man page for 'ps'
   * @param int $pid the pid of the process. if not specified, the pid of the 
   * current running process will be used
   * @return string
   */
  function getProcessAttr($attr, $pid=NULL) {
    $pid = $pid ? $pid : getmypid();
    $rattr = NULL;
    exec(SRA_File::findInPath(SRA_PS_PATH) . ' -p ' . $pid . ' -o "pid ' . $attr . '"', $processes);
    foreach ($processes as $process) {
      $process = trim($process);
      if ($pid == substr($process, 0, strpos($process, ' '))*1) {
        $rattr = trim(substr($process, strpos($process, ' ')+1));
      }
    }
    return $rattr;
  }
  // }}}
    
  // {{{ getProcessHash
  /**
   * returns a hash of the currently running processes where the key is the pid 
   * and the value is the process command
   * @param string $val the process value to use as the hash value. the default 
   * value is the process command. alternatively, any of the "ps" output value 
   * identifiers may be specified
   * @return hash
   */
  function getProcessHash($val='command') {
    $hash = array();
    exec(SRA_File::findInPath(SRA_PS_PATH) . ' -A -w -o "pid ' . $val . '"', $processes);
    foreach ($processes as $process) {
      $process = trim($process);
      $pid = substr($process, 0, strpos($process, ' '))*1;
      $cmd = substr($process, strpos($process, ' ') + 1);
      $hash[$pid] = $cmd;
    }
    return $hash;
  }
  // }}}

  // {{{ getProcessId
  /**
   * this method returns the pid of the $script process. NULL is returned if 
   * no process currently exists. if $multiple is TRUE, the return value will be
   * an array containing all of the matching pids (or NULL if none)
   * @param string $script the name of the script, either the full path or 
   * just the name
   * @param	boolean $last whether or not the pid of the oldest or most recent 
   * process should be returned if multiple are running
   * @param mixed $user an optional uid or username to limit the search to (only 
   * processes owned by this user will be returned)
   * @param boolean $multiple whether or not to return multiple pids if they 
   * exist (return value will be an array)
   * @return mixed
   */
  function getProcessId($script, $last=TRUE, $user=NULL, $multiple=FALSE) {
    if ($multiple) { $pids = array(); }
    
    $script = basename($script);
    $script = strlen($script) > 30 ? substr($script, strlen($script) - 30) : $script;
    if ($script) {
      $processes = SRA_Util::getProcessHash(isset($user) ? 'uid user command' : 'command');
      if ($last) { $processes = array_reverse($processes, TRUE); }
      foreach ($processes as $pid => $cmd) {
        if (isset($user)) {
          $pieces = explode(' ', trim($cmd));
          if ($user != trim($pieces[0]) && $user != trim($pieces[1])) { continue; }
        }
        
        if (strpos($cmd, $script) !== FALSE) {
          if ($multiple) {
            $pids[] = $pid;
          }
          else {
            return $pid;
          }
        }
      }
    }
    return $multiple && $pids ? $pids : NULL;
  }
  // }}}

  // {{{ getProcessName
  /**
   * returns the name of the process identified by $pid. returns NULL if $pid is 
   * not a valid process. the name of the process is the "command" keyword from 
   * ps
   * @param int $pid the id of the process
   * @return mixed
   */
  function getProcessName($pid) {
    return SRA_Util::getProcessAttr('args');
  }
  // }}}
  
  // {{{ getProcessRuntime
  /**
   * returns the runtime of the process identifiedy by $pid in seconds. returns 
   * NULL if the process is not valid
   * @param int $pid the pid of the process
   * @return int
   */
  function getProcessRuntime($pid) {
    $runtime = NULL;
    if ($etime = SRA_Util::getProcessAttr('etime', $pid)) {
      $pieces = explode(':', $etime);
      if (count($pieces) == 3) $runtime = ($pieces[0]*60*60) + ($pieces[1]*60) + $pieces[2];
      else if (count($pieces) == 2) $runtime = ($pieces[0]*60) + $pieces[1];
      else if (count($pieces) == 1) $runtime = $pieces[0]*1;
    }
    return $runtime;
  }
  // }}}
    
  // {{{ getRelativeNumericVal
  /**
   * returns the numeric value for a relative $expr where $expr is in the format 
   * "+n" and n may be designated as negative by enclosing it in parenthesis
   * @param string $expr the expression value to return
   * @access	public static
   * @return	int
   */
  function getRelativeNumericVal($expr) {
    $expr = SRA_Util::beginsWith($expr, '+') ? substr($expr, 1) : $expr;
    return SRA_Util::beginsWith($expr, '(') && SRA_Util::endsWith($expr, ')') ? -1 * substr($expr, 1, -1) : 1 * $expr;
  }
  // }}}
    
  // {{{ getSpellCheckLanguages
  /**
   * returns the spellcheck languages as an associative array where the key is 
   * the locale code (see SRA_Locale::getLocale($code...)), and the value is 
   * the locale label (see SRA_Locale::getLabel()). returns NULL if aspell is 
   * not available or if the dictionary path cannot be determined or read from
   * @access	public static
   * @return	array
   */
  function &getSpellCheckLanguages() {
    if ($aspell = SRA_File::findInPath(SRA_UTIL_ASPELL)) {
      // determine dictionary directory
      exec($aspell . ' config dict-dir', $results);
      if (isset($results[0]) && is_dir($results[0]) && is_readable($results[0])) {
        $languages = array();
        $files = SRA_File::getFileList($results[0], '/^.*.multi$/');
        foreach($files as $file) {
          $code = str_replace('.multi', '', basename($file));
          if ((strlen($code) == 2 || strlen($code) == 5) && SRA_Locale::isValid($locale =& SRA_Locale::getLocale($code))) {
            $languages[strtolower($code)] = strlen($code) == 2 ? $locale->getLanguageName() : $locale->getLabel();
          }
        }
        return $languages;
      }
    }
    return NULL;
  }
  // }}}
    
  // {{{ getDefaultSpellCheckLanguage
  /**
   * returns the default spell check language code for the current active user
   * this is done by first accessing the spellcheck languages using 
   * SRA_Util::getSpellCheckLanguages() followed by the user's locales using 
   * SRA_Controller::getUserLocales(), and then finding the first match in the 
   * spellcheck languages that exists in the user's locales. if not match is 
   * found the first spell check language will be returned. if there are no 
   * spellcheck languages NULL will be returned
   *
   * @access	public static
   * @return	String
   */
  function getDefaultSpellCheckLanguage() {
    $dictionaries =& SRA_Util::getSpellCheckLanguages();
    if ($dictionaries) {
      $locales =& SRA_Controller::getUserLocales();
      $keys = array_keys($locales);
      foreach($keys as $key) {
        $code = $locales[$key]->getLanguage() . '_' . $locales[$key]->getCountry();
        if (isset($dictionaries[$code])) {
          return $code;
        }
        else if (isset($dictionaries[$locales[$key]->getLanguage()])) {
          return $locales[$key]->getLanguage();
        }
      }
      $keys = array_keys($dictionaries);
      return $keys[0];
    }
    return NULL;
  }
  // }}}
		
    // {{{ getEnglishOrdinalSuffix
    /**
     * returns the english ordinal suffix for $number (st, nd, rd, or th)
     * @param int $number the number to return the ordinal suffix for
     * @access publicstatic
     * @return String
     */
    function getEnglishOrdinalSuffix($number) {
      $suffix = '';
      if (is_numeric($number)) {
        $ln = (int) substr($number, -1); 
        $sln = (int) substr($number, -2); 
        $r = array('st','nd','rd'); 
        $es = (($sln < 11 || $sln > 19) && $ln > 0 && $ln < 4); 
        $suffix = $es ? $r[$ln - 1] : 'th';
      }
      return $suffix;
    }
    // }}}
    
    // {{{ getTextBetween()
    /**
     * Returns a substring of the text provided that is between the startToken 
		 * and the endToken
     *
     * @param   startToken. String - the start string. If this start token is 
		 *					not found the returned text will start at the beginning of the 
		 * 					text
		 * @param 	endToken. String - the end string. If this end token is not 
		 *					found, the returned text will end at the end of the text.
		 * @param		text. String - the text
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function &getTextBetween( $startToken, $endToken, & $text )
    {
        $startPos = strpos($text, $startToken);
				if (!$startPos) {
					$startPos = 0;
				}
				else {
					$startPos += strlen($startToken);
				}
				$endPos = strpos($text, $endToken, $startPos);
				if (!$endPos) {
					$endPos = strlen($text);
				}
				return substr($text, $startPos, $endPos - $startPos);
    }
    // }}}

    // {{{ htmlToText()
    /**
     * This method is used to convert html to plain text (no html tags)
     *
     * @param   html - 	The html text to convert to text
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function htmlToText( $html )
    {
        $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                         "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                         "'([\r\n])[\s]+'",                 // Strip out white space
                         "'&(quot|#34);'i",                 // Replace html entities
                         "'&(amp|#38);'i",
                         "'&(lt|#60);'i",
                         "'&(gt|#62);'i",
                         "'&(nbsp|#160);'i",
                         "'&(iexcl|#161);'i",
                         "'&(cent|#162);'i",
                         "'&(pound|#163);'i",
                         "'&(copy|#169);'i",
                         "'&#(\d+);'e");                    // evaluate as php

        $replace = array ("",
                          "",
                          "\\1",
                          "\"",
                          "&",
                          "<",
                          ">",
                          " ",
                          chr(161),
                          chr(162),
                          chr(163),
                          chr(169),
                          "chr(\\1)");

        return preg_replace ($search, $replace, $html);
    }
    // }}}
    
    // {{{ implodeSkipEmpty
    /**
     * same as the php implode function but skips $pieces values that are empty 
     * (do not evaluate to PHP true)
     * @param string $glue the string to insert between each value in $pieces
     * @param array $pieces the array to implode
     * @param boolean $lead whether or not to lead the return value with $glue 
     * if it is not empty
     * @param boolean $trim whether or not to trim the values in $pieces
     * @access	public static
     * @return	string
     */
    function implodeSkipEmpty($glue, $pieces, $lead=FALSE, $trim=TRUE) {
      $value = '';
      foreach($pieces as $piece) {
        if ($trim) { $piece = trim($piece); }
        if ($piece) {
          $value .= $value != '' ? $glue : '';
          $value .= $piece;
        }
      }
      return $lead && $value ? $glue . $value : $value;
    }
    // }}}
    
    // {{{ includeAttributeInOutput
    /**
     * returns TRUE if ($include is an array and $attr is in it or if $include 
     * is not an array) and ($exclude is an array and $attr is not in it or if 
     * $exclude is not an array)
     *
     * @param   string $attr the name of the attribute to check
     * @param   mixed $include the include array. may include nested values 
     * for entity type attributes delimited by '_'
     * @param   mixed $exclude the exclude array. may include nested values 
     * for entity type attributes delimited by '_'
     * @return	boolean
     */
    function includeAttributeInOutput($attr, $include, $exclude) {
      $found = TRUE;
      if (is_array($include)) {
        $found = FALSE;
        foreach($include as $id) {
          if ($attr == $id || strpos($id, $attr . '_') === 0) { $found = TRUE; break; }
        }
      }
      if (!$found) { return FALSE; }
      
      if (is_array($exclude)) {
        foreach($exclude as $id) {
          if ($attr == $id) { return FALSE; }
        }
      }
      return TRUE;
    }
    // }}}

  // {{{ isProcessActive
  /**
   * returns TRUE if a process identified by $pid is currently active
   * @param int $pid the id of the process
   * @return boolean
   */
  function isProcessActive($pid) {
    return preg_match('/' . $pid . '/', shell_exec(SRA_File::findInPath(SRA_PS_PATH) . ' -p ' . $pid)) ? TRUE : FALSE;
  }
  // }}}
	
    // {{{ isSubclassOf()
    /**
     * This method is very simliar to the built in php is_subclass_of function with the 
	 * exception that the object parameter may be either an object or a class name 
	 * (php function requires it to be an object). This method returns true if the 
	 * parent parameter class type is a class that the child parameter extends from.
     *
     * @param   parent : Object - The parent object or class name. This parameter must 
	 * be passed as a variable (i.e. "MyClass" wll not work... 
	 * instead use $temp = "MyClass"). 
	 * @param	child : Object - The child object or class name. This parameter must 
	 * be passed as a variable (i.e. "MyClass" wll not work... 
	 * instead use $temp = "MyClass"). 
     * @access	public static
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function isSubclassOf( & $parent, & $child )
    {
		if (is_object($parent))
		{
			$parent = get_class($parent);
		}
		if(is_string($child))
		{ 
			do 
			{ 
				$child = get_parent_class($child); 
				// Not a child class
				if(!$child){
					return false;
				}
			} while ($child==$parent); 
			return true; 
		} 
		// Use standard php function
		else
		{
			return is_subclass_of($child, $parent);
		}
    }
    // }}}

  // {{{ killProcess
  /**
   * kills $pid if it is active. returns TRUE on success, FALSE if the kill 
   * fails, and NULL if $pid is not valid
   * @param int $pid the process id to kill
   * @access public
   * @return boolean
   */
  function killProcess($pid) {
    if (SRA_Util::isProcessActive($pid)) {
      exec('kill -9 ' . $pid);
      return SRA_Util::isProcessActive($pid);
    }
    return NULL;
  }
  // }}}
    
  // {{{ objToArray 
  /**
   * returns the attributes associated with an object as an associative array 
   * where the array index is the attribute name. all leading underscores are 
   * removed from the attribute names. only attributes with leading underscores 
   * are included in the returned array
   *
   * @param   obj : Object - the object to return the array for
   * @access  public
   * @return  array
   */
  function &objToArray(& $obj) {
    $attrs = array();
    $keys = array_keys(get_object_vars($obj));
    if (is_array($keys)) {
      foreach ($keys as $key) {
        if (substr($key, 0, 1) == '_') {
          $attrs[substr($key, 1, strlen($key))] = $obj->{$key};
        }
      }
    }
    return $attrs;
  }
  // }}}
  
  // {{{ parseEmailString 
  /**
   * used to parse an email string and return the corresponding name and email 
   * values from the string. an email string may be in any of the following 
   * formats:
   *   john@doe.com
   *   <john@doe.com>
   *   John Doe <john@doe.com>
   *   "John Doe" <john@doe.com>
   * the return value will be a hash with the keys 'email' and 'name' where 
   * those values were extracted from the email string provided. name may not 
   * be present in the return value if it is not part of the email string
   * @param string $str the email string to parse
   * @access public
   * @return array
   */
  function parseEmailString($str) {
    $pieces = explode(' ', $str);
    $email = $pieces[count($pieces) - 1];
    $email = SRA_Util::stripQuotes($email, '<', '>');
    if (count($pieces) > 1) {
      $name = '';
      for($i=0; $i<count($pieces) - 1; $i++) {
        $name .= ($i > 0 ? ' ' : '') . $pieces[$i];
      }
      $name = SRA_Util::stripQuotes($name);
      $name = SRA_Util::stripQuotes($name, "'", "'");
    }
    $results = array();
    if ($email) {
      $results['email'] = $email;
    }
    if ($name) {
      $results['name'] = $name;
    }
    return $results;
  }
  // }}}

    // {{{ printDebug()
    /**
     * This method displays a debug statement followed by a newline character if the debug parameter
     * is true. If no debug statement is specified it checks the SRA_Controller::DEBUG constant. If this
     * is true, it displays the debug info.
     *
     * @param   output 	-	The debug output. If this is an array or object, it will be printed using print_r
		 * @param   debug 	- 	The debug boolean flag. if not specified, the SRA_Controller::isAppInDebug() method will be 
		 * 					called to determine if debug output should be produced
     * @param   file 	-	The file where the debug message originated from (optional).
     * @param   line 	-	The line number where the debug message originated from (optional).
     * @param   pre 	-	Boolean. Wrap the output in <pre></pre> tags for display in browser (optional).
     * @param	quiet    -   Whether or not the output should be quiet... meaning, only the output is displayed, followed by a newline
		 * @param traceFile - an optional file to output to
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function printDebug( $output, $debug=SRA_UTIL_USE_SYS_CONFIG_DEBUG, $file="", $line=0, $pre=false, $quiet=false, $timestamp=false, $traceFile=false )
    {
			$buffer = '';
			if ($timestamp) {
				$buffer .= date('Y-m-d H:i:s', mktime()) . '  ';
			}
			if ($debug === SRA_UTIL_USE_APP_CONFIG_DEBUG) {
				$debug = SRA_Controller::isAppInDebug();
			}
			else if ($debug === SRA_UTIL_USE_SYS_CONFIG_DEBUG) {
				$debug = SRA_Controller::isSysInDebug();
			}
			if ($debug) {
				if ($quiet) {
						$buffer = $output . "\n";
				}
				else {
					if ($pre) {
							$buffer .= '<pre>';
					}
	
					$buffer .= 'DEBUG: ';
					if ($file != "") {
							$file=explode("/", $file);
							$buffer .= 'SRA_File: ' . $file[count($file)-1] . ',    ';
					}
					if ($line>0) {
						$buffer .= 'Line: ' . $line . ',    ';
					}
	
					$type = gettype($output);
					if ($type == 'array' || $type == 'object') {
						ob_start();
						print_r($output);
						$buffer .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$buffer .= $output . "\n";
					}
	
					if ($pre) {
						$buffer .= '</pre>';
					}
				}
			}
			if (!$traceFile) {
				echo $buffer;
			}
			else {
				$fp = fopen($traceFile, 'a');
				fwrite($fp, $buffer);
				fclose($fp);
			}
    }
    // }}}
    
  // {{{ rand()
  /**
   * wrapper for the php rand function (for use in templates)
   * @param int $min min value
   * @param int $max max value
   * @access public static
   * @return int
   */
  function rand($min, $max) {
    return rand($min, $max);
  }
  // }}}

    // {{{ removeDelimString()
    /**
     * This method is used remove a delimeter string added through the addDelimString method..
     *
     * @param   string - The String to remove the delimeter string from
     * @param	delim - The delimeter to replace the delimeter string with. The default is a comma.
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function removeDelimString( $string, $delim="," )
    {
        return str_replace("<delim>", $delim, $string);
    }
    // }}}

    // {{{ replaceArrayKey()
    /**
     * This method is used replace an array key. It simply unsets the array element with the key
     * identified by the oldKey parameter and adds the value at that element to a new key identified
     * by the newKey parameter. If the oldKey does not exist, or the newKey does, the array will be
     * returned as is.
     *
     * @param   arr - The array to replace the key in.
     * @param	oldKey - The key to be replaced
     * @param	newKey - The key to add
     * @param	caseSensitive - Whether or not the comparison should be case sensitive
     * @access	public static
     * @return	String[]
     * @author	Jason Read <jason@idir.org>
     */
    function &replaceArrayKey( & $arr, $oldKey, $newKey, $caseSensitive=TRUE )
    {
        if (!array_key_exists($newKey, $arr))
        {
            $arrKeys = array_keys($arr);
            foreach ($arrKeys as $key)
            {
                $unset=false;
                if ($caseSensitive)
                {
                    if ($key == $oldKey)
                        $unset=true;
                }
                else
                {
                    if (strtolower($key) == strtolower($oldKey))
                        $unset=true;
                }

                if ($unset)
                {
                    $arr[$newKey]=$arr[$oldKey];
                    unset($arr[$oldKey]);
                    break;
                }
            }
        }
    }
    // }}}
    
    // {{{ sendEmail
    /**
     * sends an email message based on the following parameters:
     *
     * @param mixed $to the recipient email address. if $to is an array, 1 
     * message will be sent to each recipient. multiple messages can be sent to 
     * in the same message instance by separating each recipient with a comma
     * (i.e. "john@john.com, Jason <jason@jason.com>")
     * @param string $subject the email subject
     * @param string $message the text version of the email message. either  
     * $message or $messageHtml MUST be specified. if both are specified, the 
     * message will be sent with both content types. otherwise, only the content 
     * type specified will be sent
     * @param string $messageHtml the html version of the email message
     * @param string $from the sender email address. if not specified, the 
     * default from address will be used ([process user]@[server domain])
     * @param string $fromName the sender name (optional). can also be specified 
     * in $from using the syntax "[email] <[name]>"
     * @param mixed $toName the recipient name (optional). if $to is an array, 
     * and $toName is specified, it much also be an array with the same # of 
     * values. can also be specified in $from using the syntax 
     * "[email] <[name]>"
     * @param string $cc optional comma separated list of carbon copy recipients
     * @param string $bcc optional comma separated list of blind carbon copy 
     * recipients
     * @param mixed $attachments a single file absolute path/SRA_FileAttribute 
     * object or array of absolute file paths/SRA_FileAttribute objects that 
     * should be attached to this email. the names assigned to these attachments 
     * will be the names of the files themselves. content type must be able to 
     * be determined based on the file extension (for paths - the extension must 
     * have a corresponding content type in /etc/mime.types). NOTE: if a key in 
     * this array is not numeric, that attachment will be considered to be 
     * inline where the Content-ID is the array key. This feature may be useful 
     * in order to imbed images that will be referenced in the html formatted 
     * email using <img src="cid:[Content-ID]" />
     * @access	public static
     * @return	void
     */
		function sendEmail($to, $subject, $message=NULL, $messageHtml=NULL, $from=NULL, $fromName=NULL, $toName=NULL, $cc=NULL, $bcc=NULL, &$attachments) {
      $to = !is_array($to) ? array($to) : $to;
      $toName = $toName && !is_array($toName) ? array($toName) : $toName;
      $fromEmail = $from;
      $from = $from ? ($fromName ? $fromName . ' <' . $from . '>' : $from) : NULL;
      
      if ($attachments && !is_array($attachments)) {
        $tmp = array();
        $tmp[] =& $attachments;
        $attachments =& $tmp; 
      }
      // make sure  content-types are valid available for attachments
      if ($attachments) {
        $keys = array_keys($attachments);
        foreach($keys as $key) {
          if (!SRA_FileAttribute::isValid($attachments[$key])) {
            if (!file_exists($attachments[$key]) || !is_readable($attachments[$key])) {
              SRA_Error::logError('SRA_Util::sendEmail: Attachment "' . $attachments[$key] . '" will not be included in email message because it does not exist, or it is not readable', __FILE__, __LINE__);
              unset($attachments[$key]);
            }
            else if (SRA_Error::isError(SRA_File::getMimeType($attachments[$key]))) {
              SRA_Error::logError('SRA_Util::sendEmail: Attachment "' . $attachments[$key] . '" will not be included in email message because a content type cannot be determined for it', __FILE__, __LINE__);
              unset($attachments[$key]);
            }
          }
        }
      }
      $keys = array_keys($to);
      foreach($keys as $key) {
        $headers = $from ? 'From: ' . $from . "\n" : '';
        $headers .= $from ? 'Return-Path: ' . $from . "\n" : '';
        $headers .= $cc ? "Cc: ${cc}\n" : '';
        $headers .= $bcc ? "Bcc: ${bcc}\n" : '';
        $mimeMessage = ($message && $messageHtml) || $attachments;
        if ($mimeMessage) {
          $boundary = '==Multipart_Boundary_' . md5(uniqid(time()));
          $headers .= 'MIME-Version: 1.0' ."\n";
          $headers .= 'Content-Type: multipart/' . ($attachments ? 'mixed' : 'alternative') . '; boundary="' . $boundary . '"' . "\n\n";
          $headers .= 'This is a multi-part message in MIME format.' . "\n";
        }
        if ($message) {
          $headers .= $mimeMessage ? '--' . $boundary . "\n" : '';
          $headers .= 'Content-Type: text/plain; charset=ISO-8859-1' ."\n"; 
          $headers .= 'Content-Transfer-Encoding: 8bit'. "\n\n";
          $headers .= $message . "\n";
        }
        if ($messageHtml) {
          $headers .= $mimeMessage ? '--' . $boundary . "\n" : '';
          $headers .= 'Content-Type: text/html; charset=ISO-8859-1' ."\n";
          $headers .= 'Content-Transfer-Encoding: 8bit'. "\n\n";
          $headers .= $messageHtml . "\n"; 
        }
        if ($attachments) {
          $akeys = array_keys($attachments);
          foreach($akeys as $akey) {
            if (!SRA_FileAttribute::isValid($attachments[$akey])) {
              $fileName = basename($attachments[$akey]);
              $headers .= '--' . $boundary . "\n";
              $headers .= 'Content-Type: ' . SRA_File::getMimeType($attachments[$akey]) . ";\n";
              $headers .= " name=\"$fileName\"\n";
              $headers .= 'Content-Disposition: ' . (is_numeric($akey) ? 'attachment' : 'inline') . ";\n";
              $headers .= " filename=\"$fileName\"\n";
              $headers .= "Content-Transfer-Encoding: base64\n";
              $headers .= is_numeric($akey) ? '' : "Content-ID:<$akey>\n";
              $headers .= "\n";
              $fp = fopen($attachments[$akey], 'rb');
              $headers .= chunk_split(base64_encode(fread($fp, filesize($attachments[$akey])))) . "\n\n";
              fclose($fp);
            }
            else {
              $headers .= '--' . $boundary . "\n";
              $headers .= 'Content-Type: ' . $attachments[$akey]->getType() . ";\n";
              $headers .= ' name="' . $attachments[$akey]->getName() . "\"\n";
              $headers .= 'Content-Disposition: ' . (is_numeric($akey) ? 'attachment' : 'inline') . ";\n";
              $headers .= ' filename="' . $attachments[$akey]->getName() . "\"\n";
              $headers .= "Content-Transfer-Encoding: base64\n";
              $headers .= is_numeric($akey) ? '' : "Content-ID:<$akey>\n";
              $headers .= "\n";
              $headers .= chunk_split(base64_encode($attachments[$akey]->getBytes())) . "\n\n";
            }
          }
        }
        $headers .= $mimeMessage ? '--' . $boundary . "--\n" : '';
        $recipient = $toName && $toName[$key] ? $toName[$key] . ' <' . $to[$key] . '>' : $to[$key];
        mail($recipient, $subject,'', $headers, $fromEmail ? "-f <{$fromEmail}>" : '');
      }
		}
		// }}}
    
    
    // {{{ spellcheck
    /**
     * used to spellcheck $str. the return value is an associative array with 
     * the following values:
     *  word:        the word that was mispelled
     *  suggestions: an array containing correction suggestion (NULL if none)
     *  offsets:     character positions in $str where the mispelling occured 
     *               (will contain 1 or more character positions if mispelled 
     *               multiple times)
     * this array will be indexed by "word". if aspell is not enabled, or 
     * another error occurs, this method will will return NULL
     * @param string $str the string to spellcheck. alternatively, $str may be 
     * a file path
     * @param string $lang the language code. this MUST be one of the language 
     * codes returned by getSpellCheckLanguages. if not valid, or not specified, 
     * SRA_Util::getDefaultSpellCheckLanguage will be used
     * @param boolean $html whether or not to perform the spellcheck in html 
     * mode (html tags will be ignored)
     * @param array $skip optional array of words to skip if misspelled
     * @access	public static
     * @return	array
     */
    function spellcheck($str, $lang=NULL, $html=FALSE, $skip=NULL) {
      if ($aspell = SRA_File::findInPath(SRA_UTIL_ASPELL)) {
        $lang = $lang ? $lang : SRA_Util::getDefaultSpellCheckLanguage();
        $delete = FALSE;
        if (is_file($str)) {
          $str = SRA_File::toString($str);
        }
        $file = SRA_File::createRandomFile(FALSE, "", "", str_replace("\n", '^', $str));
        $options = ' --lang=' . $lang . ' -a' . ($html ? ' -H' : '') . ' < ';
        exec($aspell . $options . $file, $output);
        SRA_File::unlink($file);
        $results = array();
        $keys = array_keys($output);
        foreach($keys as $key) {
          $eval = substr($output[$key], 0, 1);
          if ($eval == '&' || $eval == '?' || $eval == '#') {
            $pieces = explode(':', $output[$key]);
            $head = explode(' ', $pieces[0]);
            if (!is_array($skip) || !in_array($head[1], $skip)) {
              if (!isset($results[$head[1]])) {
                $results[$head[1]] = array('word' => $head[1], 'suggestions' => (isset($pieces[1]) ? explode(', ', $pieces[1]) : NULL), 'offsets' => array());
                if (isset($pieces[1])) { $results[$head[1]]['suggestions'][0] = trim($results[$head[1]]['suggestions'][0]); }
              }
              $results[$head[1]]['offsets'][] = $head[count($head) - 1];
            }
          }
        }
        return $results;
      }
      return NULL;
    }
    // }}}

    // {{{ stripQuotes()
    /**
     * This method is used to strip quotes (or another quote token) from a string. It returns the stripped string
     *
     * @param   string - The String to strip the quotes from.
     * @param	quoteTokenStart - The start quote token to strip (by default this is a double quote)
     * @param	quoteTokenEnd - The end quote token to strip (by default this is a double quote)
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function stripQuotes( $string, $quoteTokenStart='"', $quoteTokenEnd='"' )
    {
        if (strlen($string)<3)
        {
            if ($string == $quoteTokenStart . $quoteTokenEnd)
                return "";
            else
                return $string;
        }

        if (substr($string, 0, 1) == $quoteTokenStart && substr($string, strlen($string)-1, 1) == $quoteTokenEnd)
        {
            return substr($string, 1, strlen($string)-2);
        }
        else
        {
            return $string;
        }
    }
    // }}}
    
    // {{{ strToHtml
    /**
     * replaces linebreaks with html <br /> tags and other special html 
     * characters to HTML entities
     * @param string $str the string to convert
     * @access	public static
     * @return	void
     */
    function strToHtml($str) {
      return str_replace("\n", '<br />', htmlspecialchars($str));
    }
    // }}}
	
    // {{{ swapValues()
    /**
     * Swaps two values
     *
     * @param   currVal : String - The current value (this is a reference parameter
     * @param	firstVal : String - The first value
     * @param	seconVal : String - The second value
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function swapValues( & $currVal, $firstVal, $secondVal )
    {
        if (!$currVal || $currVal == $secondVal)
		{
			$currVal = $firstVal;
		}
		else
		{
			$currVal = $secondVal;
		}
    }
    // }}}

    // {{{ writeHttpToFile()
    /**
     * This method is used to write an http request to a file. To do this it extracts all of the
     * related data from the GLOBAL variables $_POST, $_GET, $_COOKIE, $_SERVER, and $_ENV
     * and writes them to a file containing arrays specifying these values. The optional errorMsg
     * attribute may be specified to have an errorMsg array attribute added to the 'values' array (the
     * array in which all of the values are stored) along with the other globals ('_POST', '_GET', etc.).
     *
     * @param   file - 		The file where the data should be saved.
     * @param   errorMsg - 	An optional error message that will be added to the array if specified.
     * @access	public static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function writeHttpToFile( $file, $errorMsg="" )
    {
        if ($fp = fopen($file, "w"))
        {
            fwrite ($fp, "<?php\n");
            fwrite ($fp, "\$values = array(\n");
            fwrite ($fp, "'errorMsg' => '" . str_replace("'", "\\'", $errorMsg) . "', \n");
            fwrite ($fp, "'_POST' => " . SRA_Util::bufferArray($_POST) . ", \n");
            fwrite ($fp, "'_GET' => " . SRA_Util::bufferArray($_GET) . ", \n");
            fwrite ($fp, "'_COOKIE' => " . SRA_Util::bufferArray($_COOKIE) . ", \n");
            fwrite ($fp, "'_SERVER' => " . SRA_Util::bufferArray($_SERVER) . ", \n");
            fwrite ($fp, "'_ENV' => " . SRA_Util::bufferArray($_ENV) . " \n");
            fwrite ($fp, ");\n?>");
            fclose($fp);
        }
        else
        {
            SRA_Error::logError("Utl::writeHttpToFile() failed: Unable to open file '{$file}'", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
        }
    }
    // }}}

    // {{{ validateBit()
    /**
     * Returns true if the bit parameter specified is a single bit in the fullBitMask parameter.
     *
	 * @param	bit : int - The bit to validate.
	 * @param	fullBitMask : int - The bitmask to validate against.
     * @access  public static
     * @return  boolean
     * @author  Jason Read <jason@idir.org>
     */
    function validateBit($bit, $fullBitMask) {
      $bit = is_numeric($bit) && !is_int($bit) ? (int) $bit : $bit;
      $fullBitMask = is_numeric($fullBitMask) && !is_int($fullBitMask) ? (int) $fullBitMask : $fullBitMask;
      
      // Both parameter must be integers
      if (!is_int($bit) || !is_int($fullBitMask)) {
        return FALSE;
      }
      return (($bit & $fullBitMask) == $bit);
    }
    // }}}
		
		
  // {{{ validateEmail
  /**
   * returns TRUE if an email address is properly constructed, FALSE otherwise
   * @param string $email the email address to validate
   * @access	public static
   * @return	boolean
   */
  function validateEmail($email) {
      return preg_match("/^([0-9,a-z,A-Z]+)([.,_]([0-9,a-z,A-Z]+))*[@]([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[.]([0-9,a-z,A-Z]){2}([0-9,a-z,A-Z])?$/",$email);
  }
  // }}}
  
  
  // {{{ ipv4ToLong
  /**
   * converts an IP address to it's corresponding numeric value
   * @param string $ip the ip address (ipv4 or ipv6) to convert
   * @access public
   * @return long
   */
  function ipv4ToLong($ip) {
    include_once('model/SRA_AttributeValidator.php');
    $num = NULL;
    
    if (SRA_AttributeValidator::ipv4($ip)) {
      $ipArr = explode('.', $ip);
      $num = ($ipArr[0] * 0x1000000) + ($ipArr[1] * 0x10000) + ($ipArr[2] * 0x100) + ($ipArr[3]);
    }
    
    return $num;
  }
  // }}}
  
  
  // {{{ longToIpv4
  /**
   * converts a numeric IPv4 address to dot notation
   * @param long $num the IP number to convert
   * @access public
   * @return string
   */
  function longToIpv4($num) {
    $ipArr = array(0 => floor($num/0x1000000));
    $ipArr[1] = ($num & 0xFF0000)  >> 16;
    $ipArr[2] = ($num & 0xFF00  )  >> 8;
    $ipArr[3] =  $num & 0xFF;
    return implode('.', $ipArr);
  }
  // }}}
		

  // {{{ validateIp
  /**
   * This method is used to validate an IP address against an array of valid IPs. If the IP
   * address is valid it returns true. Otherwise it returns false.
   *
   * @param array $validateAddresses an array of valid IP addresses. This array may contain
   * 			wild card values for any of the ip address  sub-nets. For example, to allow all IP
   * 			addresses in the 192.168.1.0 network, the value in this array would be 192.168.1.*.
   * 			If not restriction is desired, this array should consist of a single value: '*'.
   *
   * @param string $validateAddress optional parameter specifying the IP address to validate.
   * 			If this parameter is not specified the current IP address in the globals $_SERVER
   * 			array will be used ($_SERVER['REMOTE_ADDR']).
   *
   * @access	public
   * @return	boolean
   */
  function validateIp( $validateAddresses, $validateAddress=false )
  {
    if ($validateAddresses && !is_array($validateAddresses)) { $validateAddresses = array($validateAddresses); }
      if (is_array($validateAddresses))
      {
          if (in_array("*", $validateAddresses))
          {
              return true;
          }
          if (!$validateAddress)
          {
              $validateAddress = $_SERVER['REMOTE_ADDR'];
          }
          if (!$validateAddress)
          {
              SRA_Error::logError("Utl::validateIp() failed: validateAddress not specified and not available through _SERVER global.",
                              __FILE__, __LINE__, SRA_ERROR_PROBLEM);
              return false;
          }
          $ipBits = explode(".", $validateAddress);
          for ($i=0; $i< count($ipBits); $i++)
          {
              $ip = "";
              for ($n=0; $n<=$i; $n++)
              {
                  $ip .= $ipBits[$n];
                  if ($n != count($ipBits) - 1)
                      $ip .= ".";
              }
              for ($n=$i; $n<count($ipBits) - 1; $n++)
              {
                  if ($n != 0 && $n != $i)
                  {
                      $ip .= ".";
                  }
                  $ip .= "*";
              }
              if (in_array($ip, $validateAddresses))
              {
                  return true;
              }
          }
          return false;
      }
      else
      {
          SRA_Error::logError("Utl::validateIp() failed: Invalid validateAddresses parameter.",
                          __FILE__, __LINE__, SRA_ERROR_PROBLEM);
          return false;
      }
  }
  // }}}
		
		
  // {{{ sortObjects
	/**
	 * used to sort objects based on a given attribute
	 * @param object[] objects - the array of objects to sort. the array index 
	 * associations will be maintained by this method. alternatively, this may be 
   * an array of associative arrays
	 * @param string getter - the name of the getter method, object attribute or 
   * array index to sort on
	 * @param boolean desc - whether or not to sort the objects in descending order 
	 * the default is ascending order
   * @param int $maintainKeys whether or not to maintain the array keys or reset 
   * them in the returned sorted array
	 * @return object[]
	 */
	function &sortObjects(&$objects, $getter, $desc = TRUE, $maintainKeys=TRUE) {
		$keys = array_keys($objects);
    
    $key = $keys[0];
    if (is_object($objects[$key])) {
      if (method_exists($objects[$key], $getter)) {
        $isAttr = FALSE;
      }
      else if (method_exists($objects[$key], $tmp = 'get' . strtoupper(substr($getter, 0, 1)) . substr($getter, 1))) {
        $isAttr = FALSE;
        $getter = $tmp;
      }
      else if (in_array('_' . $getter, get_class_vars(get_class($objects[$key])))) {
        $getter = '_' . $getter;
        $isAttr = TRUE;
      }
      else {
        $isAttr = TRUE;
      }
    }
    
		$sortVals = array();
		foreach ($keys as $key) {
			$val = is_object($objects[$key]) ? (!$isAttr && method_exists($objects[$key], $getter) ? $objects[$key]->{$getter}() : (isset($objects[$key]->{$getter}) ? $objects[$key]->{$getter} : $objects[$key]->_{$getter})) : $objects[$key][$getter];
			$sortVals[$key] = $val;
		}
		if ($desc) {
			arsort($sortVals);
		}
		else {
			asort($sortVals);
		}
		$sorted = array();
		$keys = array_keys($sortVals);
		foreach ($keys as $key) {
      if ($maintainKeys) {
        $sorted[$key] =& $objects[$key];
      }
      else {
        $sorted[] =& $objects[$key];
      }
		}
		
		return $sorted;
	}
	// }}}
	
	
  // {{{ getCondValFromTable
	/**
	 * Used to return a value from a two dimensional table
	 * @param array[][] table - the table to evaluate
	 * @param int condValCol - the conditional value column
	 * @param String condVal - the conditional value
	 * @param int valCol - the value column
	 * @return String
	 */
	function getCondValFromTable($table, $condValCol, $condVal, $valCol) {
		$keys = array_keys($table);
		foreach ($keys as $key) {
			if ($table[$key][$condValCol] == $condVal) {
				return $table[$key][$valCol];
			}
		}
		return FALSE;
	}
	// }}}
	
	
  // {{{ getFixedWidthString
	/**
	 * Used to add padding to a string so that it conforms with a specified width
	 * @param String string - the string to pad
	 * @param int width - the desired fixed width of the string
	 * @return String
	 */
	function getFixedWidthString($string, $width = FALSE) {
		if (is_array($string)) {
			$width = $string['width'];
			$string = $string['string'];
		}
		if ($width > strlen($string)) {
			for ($i=strlen($string); $i<$width; $i++) {
				$string .= ' ';
			}
		}
		return $string;
	}
	// }}}
	
	
	// {{{ objectToString
	/**
	 * Converts an object instance into a string representation using the print_r
	 * function and output buffering (ob_start)
	 * @param object : Object - the object to convert
	 * @return String
	 */
	function &objectToString(& $object) {
		ob_start();
		print_r($object);
		$string =& ob_get_contents();
		ob_end_clean();
		return $string;
	}
	// }}}
	
	
	// {{{ copyObject
	/**
	 * Creates a copy of an object
	 * @param object : Object - the object to copy
	 * @return Object
	 */
	function copyObject($object) {
		return $object;
	}
	// }}}
	
	
	// {{{ removeLeadingSlash
	/**
	 * removes a leading slash from a string
	 * @param string : $str - the string to remove the leading slash from
	 * @return string
	 */
	function removeLeadingSlash($str) {
		if (substr($str, 0, 1) == '/') {
			return substr($str, 1);
		}
		return $str;
	}
	// }}}
	
	
	// {{{ getObjectAttr
	/**
	 * gets the attribute specified from the baseObj if it exists
	 * 
	 * @param mixed $baseObj the base object or array to start from
	 * @param string $attrId the attribute identifies. this may be a combinantion 
	 * of sub-methods and array indices. methods must not require parameters. 
	 * for example, attrId "getMailingAddress->getStreet.line1" would return line 1 
	 * of the mailing address for object $baseObj assuming that getStreet returned 
	 * some form of Address object containing a getStreet method. Additionally, if 
	 * getMaillingAddress had returned multiple Address objects, the return value 
	 * would be an array containing all of the line 1 strings for those addresses
	 * @return mixed
	 */
	function getNestedAttr(& $baseObj, $attrId) {
		$pieces = explode('->', $attrId);
		$keys = array();
		foreach ($pieces as $piece) {
			$keys[] = explode('.', $piece);
		}
		$var = $baseObj;
		for($i=0; $i<count($keys); $i++) {
			for($n = 0;$n<count($keys[$i]); $n++) {
				$key = $keys[$i][$n];
				$vkeys = is_array($var) ? array_keys($var) : FALSE;
				if (is_object($var) && method_exists($var, $key)) {
					$var = $var->${key}();
				}
				else if (is_array($var) && isset($var[$key])) {
					$var = $var[$key];
				}
				else if (is_array($var) && isset($var[$vkeys[0]]) && is_object($var[$vkeys[0]]) && method_exists($var[$vkeys[0]], $key)) {
					$tmp = array();
					foreach ($vkeys as $vkey) {
						if (is_object($var[$vkey]) && method_exists($var[$vkey], $key)) {
							$tmp[] = $var[$vkey]->${key}();
						}
					}
					$var = $tmp;
				}
				else if (is_array($var) && isset($var[$vkeys[0]]) && isset($var[$vkeys[0]][$key])) {
					$tmp = array();
					foreach ($vkeys as $vkey) {
						if (isset($var[$vkey][$key])) {
							$tmp[] = $var[$vkey][$key];
						}
					}
					$var = $tmp;
				}
			}
		}
		return $var;
	}
	// }}}
		
	// {{{ getArray()
	/**
	 * simple method that returns an array of size $size
	 *
	 * @param int $size the size of the array to return
	 * @param int $start the starting index of the array
	 * @param int $increment the increment
	 * @access 	public static
	 * @return 	array
	 */
	function getArray($size, $start = 1, $increment = 1) {
		if (is_array($size)) {
			$increment = $size[2];
			$start = $size[1];
			$size = $size[0];
		}
		$arr = array();
		for ($i=0; $i<$size; $i++) {
			$arr[] = $start + ($i * $increment);
		}
		return $arr;
	}
	// }}}
	
	// {{{ getArrayReverse()
	/**
	 * same as #getArray, but returns elements in reverse order
	 *
	 * @param int $size the size of the array to return
	 * @param int $start the starting index of the array
	 * @param int $increment the increment
	 * @access 	public static
	 * @return 	array
	 */
	function getArrayReverse($size, $start = 1, $increment = 1) {
		$arr = SRA_Util::getArray($size, $start, $increment);
		rsort($arr);
		return $arr;
	}
	// }}}
	
	// {{{ equal()
	/**
	 * returns TRUE if $attr and $match are equal based on the following criteria:
	 *   1) if $attr and $match are both the same scalar type and equal, then return TRUE. 
	 *   2) if $attr and $match are both objects with equals methods, then return $attr->equals($match)
	 *   3) if $attr or $match are objects with a "getPrimaryKey" method, invoke those methods and compare the results, return TRUE if equal
	 *   4) if $attr is an array, and $match is not, then check 1-3 for each element in $attr, return TRUE if any matches are found
	 *   5) if $attr is an array and $match is an array, then check 4 for each element of $match and return TRUE if all return TRUE
	 *   6) if $attr and $match are both booleans (as determined by SRA_Util::isBoolean) and equal (as determined by SRA_Util::convertBoolean), then return TRUE
	 *
	 * @param mixed $attr the attribute value
	 * @param mixed $match the value to match
	 * @access 	public static
	 * @return 	boolean
	 */
	function equal(& $attr, & $match) {
    // evaluate single characters
    if (is_scalar($attr) && is_scalar($match) && preg_match('/^[a-zA-Z]$/', $attr) && preg_match('/^[a-zA-Z]$/', $match)) {
      return $attr === $match;
    }
    
		// evalutate booleans
		if (is_scalar($attr) && SRA_Util::isBoolean($attr) && is_scalar($match) && SRA_Util::isBoolean($match)) {
			return SRA_Util::convertBoolean($attr) === SRA_Util::convertBoolean($match);
		}
    
    // evaluate dates
    if (SRA_GregorianDate::isValid($attr) || SRA_GregorianDate::isValid($match)) {
      return SRA_GregorianDate::isValid($attr) ? $attr->equals($match) : $match->equals($attr);
    }
		
		if (!isset($attr) && !isset($match)) {
			return TRUE;
		}
		if (is_scalar($attr) && is_scalar($match) && (($attr == $match && strlen($attr) == strlen($match)))) {
			return TRUE;
		}
		if (is_object($attr) && is_object($match) && method_exists($attr, 'equals') && method_exists($match, 'equals')) {
			return $attr->equals($match);
		}
		$apk = FALSE;
		if (is_object($attr) && method_exists($attr, 'getPrimaryKey')) {
			$apk = $attr->getPrimaryKey();
		}
		$mpk = FALSE;
		if (is_object($match) && method_exists($match, 'getPrimaryKey')) {
			$mpk = $match->getPrimaryKey();
		}
		if (($apk || $mpk) && ($apk == $mpk || (is_scalar($match) && $apk == $match) || (is_scalar($attr) && $attr == $mpk))) {
			return TRUE;
		}
		if (is_array($attr) && is_array($match)) {
			$keys = array_keys($attr);
			$mkeys = array_keys($match);
			foreach ($mkeys as $mkey) {
				$found = FALSE;
				foreach ($keys as $key) {
					if (SRA_Util::equal($attr[$key], $match[$mkey])) {
						$found = TRUE;
						break;
					}
				}
				if (!$found) {
					break;
				}
			}
		}
		else if (is_array($attr)) {
			$keys = array_keys($attr);
			foreach ($keys as $key) {
				if (SRA_Util::equal($attr[$key], $match)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getNewMatrix()
	/**
	 * returns a new instance of a SRA_Matrix object
	 *
	 * @access 	public static
	 * @return 	SRA_Matrix
	 */
	function getNewMatrix() {
		include_once('SRA_Matrix.php');
		return new SRA_Matrix();
	}
	// }}}
	
	// {{{ invokeMethod()
	/**
	 * invoked a method on an object if it exists and returns the value of that 
	 * method call. returns $obj if $obj is not an object or the $method does not 
	 * exist
	 *
	 * @param Object $obj the object to invoke the method on
	 * @param string $method the name of the method
	 * @param string $param1 the first parameter (optional)
	 * @param string $param2 the second parameter (optional)
	 * @param string $param3 the third parameter (optional)
	 * @access 	public static
	 * @return 	mixed
	 */
	function &invokeMethod(& $obj, $method, $param1 = FALSE, $param2 = FALSE, $param3 = FALSE) {
		if (is_object($obj) && method_exists($obj, $method)) {
			if ($param3) {
				return $obj->${method}($param1, $param2, $param3);
			}
			else if ($param2) {
				return $obj->${method}($param1, $param2);
			}
			else if ($param1) {
				return $obj->${method}($param1);
			}
			else {
				return $obj->${method}();
			}
		}
		return $obj;
	}
	// }}}
	
	// {{{ methodExists()
	/**
	 * returns TRUE if $obj is an object and has a method $method
	 *
	 * @param mixed $obj the object to check
	 * @param string $method the name of the method
	 * @access 	public static
	 * @return 	boolean
	 */
	function methodExists(& $obj, $method) {
		return is_object($obj) && method_exists($obj, $method);
	}
	// }}}
	
	// {{{ mergeObject()
	/**
	 * recursively merges the attributes of 2 objects. only those attrs that exist 
	 * in $merge and not in $obj (or are not set) will be merged unless $overwrite 
   * is TRUE
	 *
	 * @param Object $obj the object to merge into
	 * @param Object $merge the object to merge with
   * @param boolean $overwrite whether or not to overwrite attributes in $obj 
   * that exist in $merge
	 * @access 	public static
	 * @return 	void
	 */
	function mergeObject(& $obj, & $merge, $overwrite=FALSE) {
		$attrs = get_object_vars($merge);
		foreach ($attrs as $attr => $val) {
      if (!isset($obj->$attr) || $overwrite) {
        $obj->$attr = $val;
      }
      else if (is_object($merge->$attr) && is_object($obj->$attr)) {
        SRA_Util::mergeObject($obj->$attr, $val, $overwrite);
      }
      else if (is_array($merge->$attr) && is_array($obj->$attr)) {
        SRA_Util::mergeArray($obj->$attr, $val, $overwrite);
      }
		}
	}
	// }}}
	
	
	// {{{ mergeArray()
	/**
	 * recursively merges the 2 arrays. only those attrs that exist in $arr2 
	 * and not in $arr1 (or are not set) will be merged
	 *
	 * @param array $arr1 the array to merge into
	 * @param array $arr2 the array to merge with
   * @param boolean $overwrite whether or not to overwrite elements in $obj 
   * that exist in $merge
	 * @access 	public static
	 * @return 	void
	 */
	function mergeArray(& $arr1, & $arr2, $overwrite=FALSE) {
		$keys = array_keys($arr2);
		foreach ($keys as $key) {
      if (!isset($arr1[$key]) || $overwrite) {
        $arr1[$key] = $arr2[$key];
      }
      else if (is_object($arr1[$key]) && is_object($arr2[$key])) {
        SRA_Util::mergeObject($arr1[$key], $arr2[$key], $overwrite);
      }
      else if (is_array($arr1[$key]) && is_array($arr2[$key])) {
        SRA_Util::mergeArray($arr1[$key], $arr2[$key], $overwrite);
      }
		}
	}
	// }}}
  
	// {{{ isObject()
	/**
	 * used to validate an object is actually an object and optionally of a 
   * specific type
	 *
	 * @param Object $obj the object to validate
	 * @param string $the type to validate. optional, if not specified the $obj 
   * will simply be validated as an object
	 * @access 	public static
	 * @return 	void
	 */
	function isObject(& $obj, $type=FALSE) {
		if (is_object($obj)) {
      if (!$type || strtolower(get_class($obj)) == strtolower($type)) {
        return TRUE;
      }
    }
    return FALSE;
	}
	// }}}
  
  
	// {{{ isNumericArray()
	/**
	 * returns true if $arr is an array with a numeric incrementing index starting 
   * at 0
	 * @param array $arr the array to check
	 * @access 	public static
	 * @return 	boolean
	 */
	function isNumericArray(& $arr) {
		if (is_array($arr)) {
      $keys = array_keys($arr);
      $start = 0;
      foreach($keys as $key) {
        if ($start !== $key) { return FALSE; }
        $start++;
      }
      return TRUE;
    }
    return FALSE;
	}
	// }}}
  
  
	// {{{ substituteParams
	/**
	 * substitutes params in $str with their values in $params where the values 
   * are embedded using the format "${param name}"
   * 
	 * @param string $str the string to parse
   * @param array $params the param values to substitute
	 * @access	public
	 * @return	string 
	 */
	function substituteParams($str, $params) {
    if (is_array($params)) {
      $keys = array_keys($params);
      foreach($keys as $key) {
        $str = str_replace('${' . $key . '}', $params[$key], $str);
      }
    }
    return $str;
	}
	// }}}
  
  
	// {{{ getGlobal
	/**
	 * returns the value of a global variable
   * 
	 * @param string $name the name of the global variable to return
	 * @access	public
	 * @return	string 
	 */
	function &getGlobal($name) {
    global ${$name};
    return ${$name};
	}
	// }}}
  
	// {{{ setGlobal
	/**
	 * sets a global variable
	 * @param string $name the name of the global variable to set
   * @param mixed $val the value to set (passed and set by reference)
	 * @access	public
	 * @return	void 
	 */
	function &setGlobal($name, $val) {
    global ${$name};
    ${$name} = $val;
	}
	// }}}
  
	// {{{ cliPrompt
	/**
	 * prompts the user for an input value from the command line and returns that 
   * value
	 * @param string $question the question to ask when prompting
	 * @param boolean $required whether or not a response is required. if not 
   * required and the user does not provide a value, NULL will be returned
   * @param array $options an optional array of valid response values. if 
   * specified, the user, will be continually asked $question until they provide 
   * one of these values
   * @access public
	 * @return string
	 */
	function cliPrompt($question, $required=TRUE, $options=NULL) {
    if ($options) {
      foreach(array_keys($options) as $key) {
        $options[$key] = strtolower($options[$key]);
      }
    }
    $_readline = function_exists('readline');
    if (!$_readline) { $stdin = fopen('php://stdin', 'r'); }
    echo "\n";
    while(TRUE) {
      if ($_readline) {
        $response = readline($question . ' ');
      }
      else {
        echo $question . ' ';
        $response = strtolower(trim(fgets($stdin, 1000)));
      }
      if (($response && (!$options || in_array(strtolower(trim($response)), $options))) || !$required) { break; }
    }
    if (!$_readline) { fclose($stdin); }
    return $response ? $response : NULL;
	}
	// }}}
  
	// {{{ suCmds
	/**
	 * uses expect (http://expect.nist.gov) to login as another user and execute 
   * the commands specified by $cmds. returns a hash indexed by the $cmds where 
   * the value is the result of executing those commands if the login was 
   * successful (returns TRUE if $cmds is not specified). otherwise, returns 0 
   * if expect is not installed, -1 for invalid user, -2 for invalid password
	 * @param string $user the name of the user to login as
	 * @param string $pswd the user's password
	 * @param mixed $cmds the commands to execute. if not specified, TRUE will be 
   * returned if the login is successful. alternatively, this parameter can be 
   * a single string command in which case the return value will be a string 
   * representing the results of invoking that command instead of a hash indexed 
   * by command
   * @param hash $callbacks an optional array of callback methods to execute on 
   * the return values for $cmds. this value should be indexed by $cmd. when 
   * specified the return values will be substituted with the results of 
   * invoking those callback methods. each value specified can either be a 
   * full function call with the key ${results} which will be substituted with 
   * the command result value or just the function name in which case the 
   * function will be invoked with the command return value as the first 
   * parameter
   * @param boolean $skipSuIfEqual when TRUE and SRA_Util::getCurrentUser() is 
   * the same as $user, su will not be performed
   * @param boolean $asynchronous when TRUE, the su and command execution will 
   * be invoked asynchronously using a separate process and the return value 
   * will always be NULL (the results of the execution will be unknown)
   * @access public
	 * @return mixed
	 */
	function suCmds($user, $pswd, $cmds=NULL, $callbacks=NULL, $skipSuIfEqual=FALSE, $asynchronous=FALSE) {
    if ($cmds && !is_array($cmds)) {
      $cmds = array($cmds);
      $returnFirst = TRUE;
    }
    
    // create expect su script if it does not already exist
    $skipSu = $skipSuIfEqual && $user == SRA_Util::getCurrentUser();
    
    // make sure expect is installed
    if (!$skipSu && !($expect = SRA_File::findInPath('expect'))) {
			return 0;
    }
    
    $script = SRA_Controller::getSysTmpDir() . '/' . SRA_UTIL_SU_CMDS_SCRIPT;
    if (!$skipSu && !file_exists($script)) {
      $fp = fopen($script, 'w');
      fwrite($fp, "#!${expect} -f\n");
      fwrite($fp, "set user [lindex \$argv 0]\n");
      fwrite($fp, "set password [lindex \$argv 1]\n");
      fwrite($fp, "spawn su - \$user\n");
      fwrite($fp, "expect {\n");
      fwrite($fp, SRA_UTIL_SU_PASSWORD . ' {' . "\n");
      fwrite($fp, 'send "$password\r"' . "\n");
      fwrite($fp, 'for {set i 2} {$i < $argc} {incr i} {' . "\n");
      fwrite($fp, '  set cmd [lindex $argv $i]' . "\n");
      fwrite($fp, '  send "$cmd\r"' . "\n");
      fwrite($fp, "}\n");
      fwrite($fp, 'send "exit 1\r"' . "\n");
      fwrite($fp, 'expect eof }}');
      fclose($fp);
      chmod($script, 0755);
    }
    
    $results = array();
    if ($skipSu) {
      if ($cmds) {
        foreach($cmds as $tmp) {
          ob_start();
          passthru($tmp . ($asynchronous ? ' > /dev/null &' : ''));
          $results[$tmp] = trim(ob_get_contents());
          ob_end_clean();
        }
      }
      if ($asynchronous) { return NULL; }
    }
    else {
      $cmd = "$script $user $pswd";
      foreach($cmds as $tmp) {
        if (trim($tmp)) {
          $cmd .= ' ' . (strpos($tmp, ' ') ? '"' . str_replace('"', '\"', $tmp) . '"' : $tmp);
        }
      }
      ob_start();
      $cmd = $cmd . ($asynchronous ? ' > /dev/null &' : '');
      passthru($cmd);
      $buffer = ob_get_contents();
      ob_end_clean();
      if ($asynchronous) { return NULL; }
      
      if ($cmds && is_array($cmds)) {
        $lines = explode("\n", $buffer);
        foreach($cmds as $idx => $cmd) {
          $started = FALSE;
          foreach($lines as $line) {
            if (SRA_Util::endsWith(trim($line), isset($cmds[$idx + 1]) ? $cmds[$idx + 1] : 'exit 1')) { $started = FALSE; }
            if ($started) { $results[$cmd] .= ($results[$cmd] ? "\n" : '') . trim($line); }
            if (SRA_Util::endsWith(trim($line), $cmd)) {
              $results[$cmd] = '';
              $started = TRUE;
            }
          }
        }
      }
    }
    
    if ($callbacks && is_array($callbacks)) {
      foreach($callbacks as $cmd => $callback) {
        if (isset($results[$cmd])) {
          eval('$results[$cmd]=' . (strpos($callback, '(') ? SRA_Util::substituteParams($callback, array('results' => '$results[$cmd]')) : ($callback . '($results[$cmd])')) . ';');
        }
      }
    }
    
    if (!$skipSu) {
      foreach(explode('|', SRA_UTIL_SU_CMDS_INVALID_USER) as $tmp) {
        if (strpos($buffer, trim($tmp)) !== FALSE) {
          return -1;
        }
      }
      foreach(explode('|', SRA_UTIL_SU_CMDS_INVALID_PSWD) as $tmp) {
        if (strpos($buffer, trim($tmp)) !== FALSE) {
          return -2;
        }
      }
    }
    return $cmds ? ($returnFirst ? $results[$cmds[0]] : $results) : TRUE;
	}
	// }}}
  
	// {{{ passwd
	/**
	 * uses expect (http://expect.nist.gov) to change the current running user's 
   * password. alternatively, if the current running user is root, $user may be 
   * specified identifying the name of another user to change the password for. 
   * returns TRUE on success, FALSE otherwise
	 * @param string $pswd the new password
   * @param string $curPswd the existing password. required, unless being 
   * invoked by the root user
	 * @param string $user the username to change the password for. if not 
   * specified, the current user's password will be changed
   * @param string $output an optional reference variable that will be set with 
   * the results of the passwd execution
   * @access public
	 * @return boolean
	 */
	function passwd($pswd, $curPswd=NULL, $user=NULL, &$output) {
    $ret = FALSE;
    if (($curPswd || SRA_Util::getCurrentUser() == SRA_UTIL_ROOT) && (!$user || SRA_Util::getCurrentUser() == SRA_UTIL_ROOT) && ($expect = SRA_File::findInPath('expect'))) {
      $fp = fopen($script = SRA_File::createRandomFile(NULL, '', '', '', TRUE), 'w');
      fwrite($fp, "#!${expect} -f\n");
      fwrite($fp, 'spawn passwd' . ($user ? ' ' . $user : '') . "\n");
      if (SRA_Util::getCurrentUser() != SRA_UTIL_ROOT) {
        fwrite($fp, 'expect "current"' . "\n");
        fwrite($fp, 'send "' . $curPswd . '\r"' . "\n");
      }
      fwrite($fp, 'expect "ew" {' . "\n");
      fwrite($fp, '  send "' . $pswd . '\r"' . "\n");
      fwrite($fp, '  expect {' . "\n");
      fwrite($fp, '    "' . SRA_UTIL_SU_PSWD_RETYPE. '" {' . "\n");
      fwrite($fp, '      send "' . $pswd . '\r"' . "\n");
      fwrite($fp, '    }' . "\n");
      if (SRA_Util::getCurrentUser() != SRA_UTIL_ROOT) {
        fwrite($fp, '    "BAD" {' . "\n");
        fwrite($fp, '      send \003' . "\n");
        fwrite($fp, '    }' . "\n");
      }
      
      fwrite($fp, '  }' . "\n");
      fwrite($fp, '}' . "\n");
      fwrite($fp, "expect eof\n");
      fclose($fp);
      chmod($script, 0755);
      ob_start();
      passthru($script);
      $output = ob_get_contents();
      ob_end_clean();
      $ret = strpos($output, SRA_UTIL_SU_PSWD_RETYPE) !== FALSE ? TRUE : FALSE;
    }
    return $ret;
	}
	// }}}
  
	// {{{ suPasswd
	/**
	 * uses expect (http://expect.nist.gov) to change a system user's password. 
   * return TRUE on success, FALSE otherwise
	 * @param string $user the name of the user to change the password for
	 * @param string $pswd the user's current password (or root's password if 
   * $root is TRUE)
   * @param string $newPswd the new password to set
	 * @param boolean $root whether or not to use the root user to change the 
   * password. when TRUE, $pswd should be the root user password and not the 
   * password of $user
   * @param string $output an optional reference variable that will be set with 
   * the results of the passwd execution
   * @access public
	 * @return boolean
	 */
	function suPasswd($user, $pswd, $newPswd, $root=FALSE, &$output) {
    $ret = FALSE;
    if ($user && $pswd && $newPswd && $pswd != $newPswd && SRA_Util::suCmds($root ? 'root' : $user, $pswd) === TRUE) {
      $expect = SRA_File::findInPath('expect');
      $fp = fopen($script = SRA_File::createRandomFile(NULL, '', '', '', TRUE), 'w');
      fwrite($fp, "#!${expect} -f\n");
      fwrite($fp, 'spawn su ' . ($root ? 'root' : $user) . "\n");
      fwrite($fp, 'expect ' . SRA_UTIL_SU_PASSWORD . "\n");
      fwrite($fp, 'send "' . $pswd . '\r"' . "\n");
      fwrite($fp, 'send "passwd' . ($root ? ' ' . $user : '') . '\r"' . "\n");
      if (!$root) {
        fwrite($fp, 'expect ' . SRA_UTIL_SU_PASSWORD . "\n");
        fwrite($fp, 'send "' . $pswd . '\r"' . "\n");
      }
      fwrite($fp, "expect New\n");
      fwrite($fp, 'send "' . $newPswd . '\r"' . "\n");
      fwrite($fp, "expect {\n");
      if (!$root) {
        fwrite($fp, '  "' . SRA_UTIL_SU_PSWD_BAD . '" { ' . "\n");
        fwrite($fp, '    send \003' . "\n");
        fwrite($fp, '    exit' . "\n");
        fwrite($fp, "  }\n");
      }
      fwrite($fp, '  "' . SRA_UTIL_SU_PSWD_RETYPE . '" { send "' . $newPswd . '\r"' . " }\n");
      fwrite($fp, "}\n");
      fwrite($fp, 'send "exit\r"' . "\n");
      fwrite($fp, 'expect eof');
      fclose($fp);
      chmod($script, 0755);
      ob_start();
      passthru($script);
      $output = ob_get_contents();
      ob_end_clean();
      $ret = strpos($output, SRA_UTIL_SU_PSWD_RETYPE) !== FALSE ? TRUE : FALSE;
    }
    return $ret;
	}
	// }}}
  
	// {{{ propertiesStringToHash
	/**
	 * this method converts a properties string into a hash. a properties string 
   * is a string containing key/value pairs where each pair is separated by a 
   * newline character and the key/value pair is separated by a equals sign. 
   * for example, the string:
   *   test1=test 1 string
   *   test2=test 2 string
   * would result in this method returns the hash: 
   *   array('test1' => 'test 1 string', 'test2' => 'test 2 string')
   * comments can be delimited using a preceding #
	 * @param string $str the properties string to parses
	 * @return hash
	 */
	function propertiesStringToHash($str) {
    $hash = array();
    $lines = explode("\n", $str);
    foreach($lines as $line) {
      if (substr($line, 0, 1) != '#' && substr($line, 0, 1) != ';' && strstr($line, '=')) {
        $pair = explode('=', $line);
        $pkey = trim($pair[0]);
        $pvalue = substr(strstr($line, '='), 1);
        
        // check for line breaks
        $pvalue = str_replace("\\\\n", "[#BREAK#]", $pvalue);
        $pvalue = str_replace("\\n", "\n", $pvalue);
        $pvalue = str_replace("[#BREAK#]", "\\n", $pvalue);
        
        // Check for embedded php code
        if (strstr($pvalue, "php::")) {
          $pvalue = preg_replace_callback("'php::(.*?)::php'si", "codeToString", $pvalue);
        }
        $hash[$pkey] = $pvalue;
      }
    }
    return $hash;
	}
	// }}}
  
	// {{{ isAlphaNumeric
	/**
	 * returns TRUE if $str is an alphanumeric string
	 * @param string $str the string to check
	 * @return boolean
	 */
	function isAlphaNumeric($str) {
    return preg_match('/[^A-Za-z0-9]/', $str) ? FALSE : TRUE;
	}
	// }}}
  
	// {{{ trimLeadingZeros
	/**
	 * trims any leading zeros from $str. if $str consists of only 0s, 0 will be 
   * returned
	 * @param string $str the string to trim the leading zeros from
   * @access public
	 * @return string
	 */
	function trimLeadingZeros($str) {
		$trimmed = '';
    $started = FALSE;
    for($i=0; $i<strlen($str); $i++) {
      $char = substr($str, $i, 1);
      if ($char !== '0') { $started = TRUE; }
      $trimmed .= $started ? $char : '';
    }
    return strlen($trimmed) ? $trimmed : '0';
	}
	// }}}
  
	// {{{ trimTrailingZeros
	/**
	 * trims any trailing zeros from $str. if $str consists of only 0s, 0 will be 
   * returned
	 * @param string $str the string to trim the leading zeros from
   * @access public
	 * @return string
	 */
	function trimTrailingZeros($str) {
		$trimmed = '';
    $started = FALSE;
    for($i=strlen($str)-1; $i>=0; $i--) {
      $char = substr($str, $i, 1);
      if ($char !== '0') { $started = TRUE; }
      if ($started) { $trimmed = $char . $trimmed; }
    }
    return strlen($trimmed) ? $trimmed : '0';
	}
	// }}}
  
	// {{{ normalizeDataUnit
	/**
	 * this method is used to normalize a data unit. normalization involves first 
   * determining the unit size by parsing $data for a standard unit symbol or 
   * name and then applying the correct multiplier to convert the numeric value 
   * in $data to the new $unit of measure. return return value will either be 
   * the numeric representation of $data in $unit or the numeric value followed 
   * by the specified $suffix (if $suffix is specified). if $data cannot be 
   * parsed its numeric value will be returned. the following unit labels are 
   * recognized ($data can be parsed with or without spaces between the numeric 
   * value and the unit label) - note labels are case sensative except for the 
   * non abbreviated value (i.e. both 'bit' and 'BIT' are recognized):
   *   UNIT      LABELS
   *   bit:      b bs bS bps bpS b/s b/S bit bit/s
   *   byte:     B Bs BS Bps BpS B/s B/S byte byte/s
   *   kilobit:  Kb kb Kbs kbs KbS kbS Kbps KbpS kbps kbpS Kb/s Kb/S kb/s kb/S kilobit[s] kbit[s] kbit/s kilobit/s kilo bit[s]
   *   kilobyte: KB kB KBs kBs KBS kBS KBps KBpS kBps kBpS KB/s KB/S kB/s kB/S kilobyte[s] kbyte[s] kbyte/s kilobyte/s kilo byte[s]
   *   megabit:  Mb mb Mbs mbs MbS mbS Mbps MbpS mbps mbpS Mb/s Mb/S mb/s mb/S megabit[s] mbit[s] mbit/s megabit/s mega bit[s]
   *   megabyte: MB mB MBs mBs MBS mBS MBps MBpS mBps mBpS MB/s MB/S mB/s mB/S megabyte[s] mbyte[s] mbyte/s megabyte/s mega byte[s]
   *   gigabit:  Gb gb Gbs gbs GbS gbS Gbps GbpS gbps gbpS Gb/s Gb/S gb/s gb/S gigabit[s] gbit[s] gbit/s gigabit/s giga bit[s]
   *   gigabyte: GB gB GBs gBs GBS gBS GBps GBpS gBps gBpS GB/s GB/S gB/s gB/S gigabyte[s] gbyte[s] gbyte/s gigabyte/s giga byte[s]
   *   terabit:  Tb tb Tbs tbs TbS tbS Tbps TbpS tbps tbpS Tb/s Tb/S tb/s tb/S terabit[s] tbit[s] tbit/s terabit/s tera bit[s]
   *   terabyte: TB tB TBs tBs TBS tBS TBps TBpS tBps tBpS TB/s TB/S tB/s tB/S terabyte[s] tbyte[s] tbyte/s terabyte/s tera byte[s]
   * NOTE: this method is not localized - it interprets and outputs only english 
   * strings
	 * @param mixed $data the data unit size and unit size label to normalize 
   * represents as a string (i.e. '567Mb'), or a hash with the following 2 keys:
   * 'value': the value to convert, 'unit' the current unit identifier
   * @param int $unit the identifier of the unit that $data should be converted 
   * to. this value must correspond with one of the SRA_UTIL_DATA_UNIT_* 
   * constants (see the constant declarations at the top of this file). if not 
   * specified, the corresponding unit (bits or bytes depending on the unit 
   * label in $data) that will represent $data in the smallest whole number will 
   * be used
   * @param mixed $suffix if the return value should include the unit size 
   * label suffix this attribute may be used to specify which label to use. the 
   * options are 1 for the same abbreviation in the new $unit, 2 for the default 
   * unit size abbreviation or 3 for the default unit size name or a custom 
   * suffix string. NOTE: if $suffix is 2 or 3 and the $data unit identifier 
   * includes the per second identifier, the new suffix will also include this 
   * identifier (the default labels are the first 4 labels in the 
   * SRA_UTIL_DATA_UNIT_*_LABELS constants)
   * @param boolean $base2 whether or not to use base 2 or base 10 conversions
   * i.e. is a kilobyte 2^10 or 1024 bytes or 10^3 or 1000 bytes. the correct 
   * conversion is base 10 according to the International Electrotechnical 
   * Commission (IEC), the International Committee for Weights and Measures 
   * (CIPM) and the Institute of Electrical and Electronics Engineers (IEEE)
   * for more information see: http://physics.nist.gov/cuu/Units/binary.html
   * @param int $decimals the max # of decimals to include in the normalized 
   * value
   * @access public
	 * @return mixed
	 */
	function normalizeDataUnit($data, $unit=NULL, $suffix=1, $decimals=2, $base2=FALSE) {
    static $_cachedNormalizedDataUnits;
    if (!isset($_cachedNormalizedDataUnits)) $_cachedNormalizedDataUnits = array();
    
    if ($unit && strpos($unit, '/') && count($tmp = explode('/', $unit)) == 2) {
      $unit = $tmp[0];
    }
    if (is_array($data)) {
      $normalized = $data['value']*1;
      $dataUnit = $data['unit'];
    }
    else {
      $normalized = $data*1;
    }
    
    // checked for cached value
    $ckey = $data . '_' . $unit . '_' . $suffix . '_' . $decimals . '_' . $base2;
    if (isset($_cachedNormalizedDataUnits[$ckey])) {
      return $_cachedNormalizedDataUnits[$ckey];
    }
    
    $labelsHash = array(SRA_UTIL_DATA_UNIT_PETABYTE => SRA_UTIL_DATA_UNIT_PETABYTE_LABELS, 
    								SRA_UTIL_DATA_UNIT_PETABIT => SRA_UTIL_DATA_UNIT_PETABIT_LABELS,
	                  SRA_UTIL_DATA_UNIT_TERABYTE => SRA_UTIL_DATA_UNIT_TERABYTE_LABELS, 
                    SRA_UTIL_DATA_UNIT_TERABIT => SRA_UTIL_DATA_UNIT_TERABIT_LABELS, 
                    SRA_UTIL_DATA_UNIT_GIGABYTE => SRA_UTIL_DATA_UNIT_GIGABYTE_LABELS, 
                    SRA_UTIL_DATA_UNIT_GIGABIT => SRA_UTIL_DATA_UNIT_GIGABIT_LABELS, 
                    SRA_UTIL_DATA_UNIT_MEGABYTE => SRA_UTIL_DATA_UNIT_MEGABYTE_LABELS, 
                    SRA_UTIL_DATA_UNIT_MEGABIT => SRA_UTIL_DATA_UNIT_MEGABIT_LABELS, 
                    SRA_UTIL_DATA_UNIT_KILOBYTE => SRA_UTIL_DATA_UNIT_KILOBYTE_LABELS, 
                    SRA_UTIL_DATA_UNIT_KILOBIT => SRA_UTIL_DATA_UNIT_KILOBIT_LABELS, 
                    SRA_UTIL_DATA_UNIT_BYTE => SRA_UTIL_DATA_UNIT_BYTE_LABELS, 
                    SRA_UTIL_DATA_UNIT_BIT => SRA_UTIL_DATA_UNIT_BIT_LABELS);
    if ($data && ($dataUnit || !is_numeric($data)) && (($unit && $labelsHash[$unit]) || !$unit)) {
      $ldata = strtolower($dataUnit ? $dataUnit : $data);
      foreach ($labelsHash as $evalUnit => $labels) {
        $labels = array_reverse(explode(',', $labels));
        foreach ($labels as $idx => $label) {
          $lower = substr($label, 0, 1) == ':' ? TRUE : FALSE;
          if ($lower) { $label = strtolower(substr($label, 1)); }
          if (trim($label) && (!$foundLabel || strlen($label) > strlen($foundLabel)) && preg_match('/' . str_replace('/', '\/', $label) . '/', $lower ? $ldata : ($dataUnit ? $dataUnit : $data))) {
            $useUpper = strpos($dataUnit ? $dataUnit : $data, $label) === FALSE ? TRUE : FALSE;
            $perS = (!$lower && SRA_Util::endsWith(strtolower($label), 's')) || ($lower && SRA_Util::endsWith(strtolower($label), '/s'));
            $includeSpace = preg_match('/' . str_replace('/', '\/', ' ' . $label) . '/', $lower ? $ldata : ($dataUnit ? $dataUnit : $data));
            $useLower = $lower;
            $useIdx = $idx;
            $foundLabel = $label;
            $dataUnit = $evalUnit;
            $useBits = $dataUnit == SRA_UTIL_DATA_UNIT_PETABIT || $dataUnit == SRA_UTIL_DATA_UNIT_TERABIT || $dataUnit == SRA_UTIL_DATA_UNIT_GIGABIT || $dataUnit == SRA_UTIL_DATA_UNIT_MEGABIT || $dataUnit == SRA_UTIL_DATA_UNIT_KILOBIT || $dataUnit == SRA_UTIL_DATA_UNIT_BIT;
          }
        }
      }
      if ($dataUnit) {
        switch($dataUnit) {
          case SRA_UTIL_DATA_UNIT_BIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 9007199254740000 : 8000000000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 1125899906842500 : 1000000000000000),
                                 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 8796093022208 : 8000000000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 8589934592 : 8000000000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '/', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '/', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => 1));
            break;
          case SRA_UTIL_DATA_UNIT_BYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 1125899906842624 : 1000000000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 140737488355328 : 125000000000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 137438953472 : 125000000000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 134217728 : 125000000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '/', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '/', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => 8));
            break;
          case SRA_UTIL_DATA_UNIT_KILOBIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 8796093022208 : 8000000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 1099511627776 : 1000000000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 8589934592 : 8000000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '/', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000));
            break;
          case SRA_UTIL_DATA_UNIT_KILOBYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 137438953472 : 125000000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 134217728 : 125000000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '/', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 8192 : 8000));
            break;
          case SRA_UTIL_DATA_UNIT_MEGABIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 8589934592 : 8000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000));
            break;
          case SRA_UTIL_DATA_UNIT_MEGABYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 134217728 : 125000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '/', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 8388608 : 8000000));
            break;
          case SRA_UTIL_DATA_UNIT_GIGABIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 134217728 : 125000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000));
            break;
          case SRA_UTIL_DATA_UNIT_GIGABYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 1048576 : 1000000),
             										 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '/', 'val' => $base2 ? 131072 : 125000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '/', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 8589934592 : 8000000000));
            break;
          case SRA_UTIL_DATA_UNIT_TERABIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '*', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 134217728 : 125000000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 137438953472 : 125000000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 1099511627776 : 1000000000000));
            break;
          case SRA_UTIL_DATA_UNIT_TERABYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '*', 'val' => $base2 ? 128 : 125),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '*', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 8589934592 : 8000000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 8796093022208 : 8000000000000));
            break;
          case SRA_UTIL_DATA_UNIT_PETABIT:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => 8),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '*', 'val' => 1),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '/', 'val' => $base2 ? 128 : 125),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '*', 'val' => $base2 ? 131072 : 125000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 134217728 : 125000000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 137438953472 : 125000000000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 140737488355328 : 125000000000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 1125899906842500 : 1000000000000000));
            break;
          case SRA_UTIL_DATA_UNIT_PETABYTE:
            $conversions = array(SRA_UTIL_DATA_UNIT_PETABYTE => array('op' => '/', 'val' => 1),
                                 SRA_UTIL_DATA_UNIT_PETABIT => array('op' => '*', 'val' => 8),
																 SRA_UTIL_DATA_UNIT_TERABYTE => array('op' => '*', 'val' => $base2 ? 1024 : 1000),
                                 SRA_UTIL_DATA_UNIT_TERABIT => array('op' => '*', 'val' => $base2 ? 8192 : 8000),
                                 SRA_UTIL_DATA_UNIT_GIGABYTE => array('op' => '*', 'val' => $base2 ? 1048576 : 1000000),
                                 SRA_UTIL_DATA_UNIT_GIGABIT => array('op' => '*', 'val' => $base2 ? 8388608 : 8000000),
                                 SRA_UTIL_DATA_UNIT_MEGABYTE => array('op' => '*', 'val' => $base2 ? 1073741824 : 1000000000),
                                 SRA_UTIL_DATA_UNIT_MEGABIT => array('op' => '*', 'val' => $base2 ? 8589934592 : 8000000000),
                                 SRA_UTIL_DATA_UNIT_KILOBYTE => array('op' => '*', 'val' => $base2 ? 1099511627776 : 1000000000000),
                                 SRA_UTIL_DATA_UNIT_KILOBIT => array('op' => '*', 'val' => $base2 ? 8796093022208 : 8000000000000),
                                 SRA_UTIL_DATA_UNIT_BYTE => array('op' => '*', 'val' => $base2 ? 1125899906842500 : 1000000000000000),
                                 SRA_UTIL_DATA_UNIT_BIT => array('op' => '*', 'val' => $base2 ? 9007199254740000 : 8000000000000000));
            break;
        }
        $keys = array_keys($conversions);
        if (!$unit) {
          for($i=0; $i<count($keys); $i++) {
            if (($useBits && ($keys[$i] == SRA_UTIL_DATA_UNIT_PETABIT || $keys[$i] == SRA_UTIL_DATA_UNIT_TERABIT || $keys[$i] == SRA_UTIL_DATA_UNIT_GIGABIT || $keys[$i] == SRA_UTIL_DATA_UNIT_MEGABIT || $keys[$i] == SRA_UTIL_DATA_UNIT_KILOBIT || $keys[$i] == SRA_UTIL_DATA_UNIT_BIT)) || (!$useBits && ($keys[$i] == SRA_UTIL_DATA_UNIT_PETABYTE || $keys[$i] == SRA_UTIL_DATA_UNIT_TERABYTE || $keys[$i] == SRA_UTIL_DATA_UNIT_GIGABYTE || $keys[$i] == SRA_UTIL_DATA_UNIT_MEGABYTE || $keys[$i] == SRA_UTIL_DATA_UNIT_KILOBYTE || $keys[$i] == SRA_UTIL_DATA_UNIT_BYTE))) {
              if ($normalized >= $conversions[$keys[$i]]['val'] && (!$lastUnit || $normalized < $conversions[$lastUnit]['val'])) {
                $unit = $keys[$i];
                break;
              }
              $lastUnit = $keys[$i];
            }
          }
          if (!$unit) { $unit = $dataUnit; }
        }
        if ($conversions[$unit]) {
          eval('$normalized' . $conversions[$unit]['op'] . '=' . $conversions[$unit]['val'] . ';');
        }
      }
      if ($unit && $labelsHash[$unit]) {
        $labels = array_reverse(explode(',', $labelsHash[$unit]));
        $suffix = $suffix === 1 || $suffix === 2 || $suffix === 3 ? $labels[$suffix == 1 ? $useIdx : (($suffix == 2 ? 0 : 2) + ($perS ? 1 : 0))] . ($suffix === 3 && !$perS && $normalized > 1 ? 's' : '') : $suffix;
        if (SRA_Util::endsWith($suffix, 'ss')) { $suffix = substr($suffix, 0, -1); }
        if (substr($suffix, 0, 1) == ':') { $suffix = substr($suffix, 1); }
        if ($normalized <= 1 && !$perS && SRA_Util::endsWith($suffix, 's')) { $suffix = substr($suffix, 0, -1); }
        if ($normalized > 1 && $useLower && !SRA_Util::endsWith($suffix, 's')) { $suffix .= 's'; }
        if ($useLower && $useUpper) { $suffix = strtoupper(substr($suffix, 0, 1)) . substr($suffix, 1); }
      }
    }
    if (strpos($normalized, '.') !== FALSE) { $normalized = SRA_Util::trimTrailingZeros(round($normalized, $decimals))*1; }
    $val = $normalized . (!$suffix || $suffix === 1 || $suffix === 2 || $suffix === 3 ? '' : ($includeSpace ? ' ' : '') . $suffix);
    $_cachedNormalizedDataUnits[$ckey] = $val;
    
    return $val;
	}
	// }}}
  
	// {{{ normalizeMeasurement
	/**
	 * this method is used to normalize a measurement. normalization involves first 
   * determining the unit size by parsing $data for a standard unit symbol or 
   * name and then applying the correct multiplier to convert the numeric value 
   * in $data to the new $unit of measure. return return value will either be 
   * the numeric representation of $data in $unit or the numeric value followed 
   * by the specified $suffix (if $suffix is specified). if $data cannot be 
   * parsed its numeric value will be returned. the following unit labels are 
   * recognized ($data can be parsed with or without spaces between the numeric 
   * value and the unit label) - labels are not case sensitive. conversion cannot 
   * occur between classes of measurement for obvious reasons.
   *   UNIT        METRIC? LABELS
   *   LENGTH MEASUREMENTS
   *   Centimeter: Y       centimeter(s) cm cms
   *   Foot:       N       foot feet ft fts
   *   Inch:       N       inch(es) in ins
   *   Kilometer:  Y       kilometer(s) km kms
   *   Meter:      Y       meter(s) m ms
   *   Mile:       N       mile(s) mi mis
   *   Yard:       N       yard(s) yd yds
   *   
   *   WEIGHT MEASUREMENTS
   *   Gram:       Y       gram(s) gm gms
   *   Kilogram:   Y       kilogram(s) kg kgs
   *   Ounce:      N       ounce(s) oz ozs
   *   Pound:      N       pound(s) lb lbs
	 * @param mixed $data the data unit size and unit size label to normalize 
   * represents as a string (i.e. '516 LBS'), or a hash with the following 2/3 keys:
   * 'value': the value to convert, 'unit' the current unit identifier, and 
   * 'multiplier' (optional) - a multiplier to apply to 'value'
   * @param int $unit the identifier of the unit that $data should be converted 
   * to. this value must correspond with one of the SRA_UTIL_MEASUREMENT_* 
   * constants (see the constant declarations at the top of this file). if not 
   * specified, the corresponding unit that will represent $data in the smallest 
   * whole number will be used
   * @param mixed $suffix if the return value should include the unit size 
   * label suffix this attribute may be used to specify which label to use. the 
   * options are 1 for the same abbreviation in the new $unit, 2 for the unit 
   * abbreviation or 3 for the unit name
   * @param int $decimals the max # of decimals to include in the normalized 
   * value
   * @param SRA_Locale $locale by default if $unit is not specified, $data will 
   * be normalized using the smallest unit of measure in the same measurement 
   * systems as specified in $data (i.e. 1000 meters will be converted to 1 kilometer 
	 * NOT 0.62 miles). if you want to change this behavior, you may specify this 
	 * locale parameter and if $locale uses metric measurements, $data will be 
	 * normalized to the measurement system used by that locale
   * @access public
	 * @return mixed
	 */
	function normalizeMeasurement($data, $unit=NULL, $suffix=1, $decimals=2, $locale=NULL) {
    if (is_array($data)) {
      $normalized = $data['value']*1;
      $dataUnit = isset($data['unit']) ? $data['unit'] : NULL;
			if (isset($data['multiplier']) && is_numeric($data['multiplier']) && $data['multiplier'] > 0) {
				$normalized *= $data['multiplier'];
			}
			$data = $data['value'];
			$includeSpace = TRUE;
    }
    else {
      $normalized = $data*1;
    }
		
    $labelsHash = array(SRA_UTIL_MEASUREMENT_CENTIMETER => ':cm,:cms,:centimeter,:centimeters', 
                    SRA_UTIL_MEASUREMENT_FOOT => ':ft,:fts,:foot,:feet', 
                    SRA_UTIL_MEASUREMENT_INCH => ':in,:ins,:inch,:inches', 
                    SRA_UTIL_MEASUREMENT_KILOMETER => ':km,:kms,:kilometer,:kilometers', 
                    SRA_UTIL_MEASUREMENT_METER => ':m,:ms,:meter,:meters', 
                    SRA_UTIL_MEASUREMENT_MILE => ':mi,:mis,:mile,:miles', 
                    SRA_UTIL_MEASUREMENT_YARD => ':yd,:yds,:yard,:yards', 
                    SRA_UTIL_MEASUREMENT_GRAM => ':gm,:gms,:gram,:grams', 
                    SRA_UTIL_MEASUREMENT_KILOGRAM => ':kg,:kgs,:kilogram,:kilograms', 
                    SRA_UTIL_MEASUREMENT_OUNCE => ':oz,:ozs,:ounce,:ounces',
                    SRA_UTIL_MEASUREMENT_POUND => ':lb,:lbs,:pound,:pounds');
    if ($data && ($dataUnit || !is_numeric($data)) && (($unit && $labelsHash[$unit]) || !$unit)) {
      $ldata = strtolower($dataUnit ? $dataUnit : $data);
      foreach ($labelsHash as $evalUnit => $labels) {
        $labels = array_reverse(explode(',', $labels));
        foreach ($labels as $idx => $label) {
          $lower = substr($label, 0, 1) == ':' ? TRUE : FALSE;
          if ($lower) { $label = strtolower(substr($label, 1)); }
          if (trim($label) && (!$foundLabel || strlen($label) > strlen($foundLabel)) && preg_match('/' . str_replace('/', '\/', $label) . '/', $lower ? $ldata : ($dataUnit ? $dataUnit : $data))) {
            $useUpper = strpos($dataUnit ? $dataUnit : $data, $label) === FALSE ? TRUE : FALSE;
            $includeSpace = !$includeSpace ? (preg_match('/' . str_replace('/', '\/', ' ' . $label) . '/', $lower ? $ldata : ($dataUnit ? $dataUnit : $data))) : TRUE;
            $useLower = $lower;
            $useIdx = $idx;
            $foundLabel = $label;
            $dataUnit = $evalUnit;
						$useMetric = SRA_Locale::isValid($locale) ? $locale->isMetric() : $dataUnit == SRA_UTIL_MEASUREMENT_CENTIMETER || $dataUnit == SRA_UTIL_MEASUREMENT_KILOMETER || $dataUnit == SRA_UTIL_MEASUREMENT_METER || $dataUnit == SRA_UTIL_MEASUREMENT_GRAM || $dataUnit == SRA_UTIL_MEASUREMENT_KILOGRAM;
          }
        }
      }
      if ($dataUnit) {
        switch($dataUnit) {
          case SRA_UTIL_MEASUREMENT_FOOT:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 5280),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '/', 'val' => 3280.839895),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '/', 'val' => 3.280839895),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '/', 'val' => 3),
	                               SRA_UTIL_MEASUREMENT_FOOT => array('op' => '*', 'val' => 1),
	                               SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 12),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 30.48));
	            break;
          case SRA_UTIL_MEASUREMENT_INCH:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 63360.000000068429),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '/', 'val' => 39370.0787402),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '/', 'val' => 39.3700787402),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '/', 'val' => 36),
																 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '/', 'val' => 12),
											           SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 1),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 2.54));
	            break;
          case SRA_UTIL_MEASUREMENT_KILOMETER:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 1.609344),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '*', 'val' => 1),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '*', 'val' => 1000),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '*', 'val' => 1093.613298),
																 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '*', 'val' => 3280.839895),
											           SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 39370.0787402),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 100000));
	            break;
          case SRA_UTIL_MEASUREMENT_METER:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 1609.344),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '/', 'val' => 1000),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '*', 'val' => 1),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '*', 'val' => 1.093613298),
															 	 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '*', 'val' => 3.280839895),
											           SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 39.3700787402),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 100));
	            break;
          case SRA_UTIL_MEASUREMENT_MILE:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '*', 'val' => 1),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '*', 'val' => 1.609344),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '*', 'val' => 1609.344),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '*', 'val' => 1759.999999456512),
																 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '*', 'val' => 5279.99999997888),
											           SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 63360.000000068429),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 160934.4));
	            break;
          case SRA_UTIL_MEASUREMENT_YARD:
	          $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 1760),
	                               SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '/', 'val' => 1093.613298),
	                               SRA_UTIL_MEASUREMENT_METER => array('op' => '/', 'val' => 1.093613298),
	                               SRA_UTIL_MEASUREMENT_YARD => array('op' => '*', 'val' => 1),
																 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '*', 'val' => 2.999999999988),
											           SRA_UTIL_MEASUREMENT_INCH => array('op' => '*', 'val' => 36.00000000003888),
	                               SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 91.44));
	            break;
          case SRA_UTIL_MEASUREMENT_CENTIMETER:
            $conversions = array(SRA_UTIL_MEASUREMENT_MILE => array('op' => '/', 'val' => 160934.4),
                                 SRA_UTIL_MEASUREMENT_KILOMETER => array('op' => '/', 'val' => 100000),
                                 SRA_UTIL_MEASUREMENT_METER => array('op' => '/', 'val' => 100),
                                 SRA_UTIL_MEASUREMENT_YARD => array('op' => '/', 'val' => 91.44),
																 SRA_UTIL_MEASUREMENT_FOOT => array('op' => '/', 'val' => 30.48),
										             SRA_UTIL_MEASUREMENT_INCH => array('op' => '/', 'val' => 2.54),
                                 SRA_UTIL_MEASUREMENT_CENTIMETER => array('op' => '*', 'val' => 1));
            break;
          case SRA_UTIL_MEASUREMENT_GRAM:
            $conversions = array(SRA_UTIL_MEASUREMENT_KILOGRAM => array('op' => '/', 'val' => 1000),
                                 SRA_UTIL_MEASUREMENT_POUND => array('op' => '/', 'val' => 453.59237),
                                 SRA_UTIL_MEASUREMENT_OUNCE => array('op' => '/', 'val' => 28.349523125), 
																 SRA_UTIL_MEASUREMENT_GRAM => array('op' => '*', 'val' => 1));
            break;
          case SRA_UTIL_MEASUREMENT_KILOGRAM:
            $conversions = array(SRA_UTIL_MEASUREMENT_KILOGRAM => array('op' => '*', 'val' => 1),
																 SRA_UTIL_MEASUREMENT_POUND => array('op' => '*', 'val' => 2.204622622),
                                 SRA_UTIL_MEASUREMENT_OUNCE => array('op' => '*', 'val' => 35.2739619),
																 SRA_UTIL_MEASUREMENT_GRAM => array('op' => '*', 'val' => 1000));
            break;
          case SRA_UTIL_MEASUREMENT_OUNCE:
            $conversions = array(SRA_UTIL_MEASUREMENT_KILOGRAM => array('op' => '/', 'val' => 35.2739619),
                                 SRA_UTIL_MEASUREMENT_POUND => array('op' => '/', 'val' => 16),
                                 SRA_UTIL_MEASUREMENT_OUNCE => array('op' => '*', 'val' => 1),
																 SRA_UTIL_MEASUREMENT_GRAM => array('op' => '*', 'val' => 28.349523125));
            break;
          case SRA_UTIL_MEASUREMENT_POUND:
            $conversions = array(SRA_UTIL_MEASUREMENT_KILOGRAM => array('op' => '/', 'val' => 2.204622622),
                                 SRA_UTIL_MEASUREMENT_POUND => array('op' => '*', 'val' => 1),
                                 SRA_UTIL_MEASUREMENT_OUNCE => array('op' => '*', 'val' => 15.999999977510703),
																 SRA_UTIL_MEASUREMENT_GRAM => array('op' => '*', 'val' => 453.59237));
            break;
        }
        $keys = array_keys($conversions);
        if (!$unit) {
          for($i=0; $i<count($keys); $i++) {
            if (($useMetric && ($keys[$i] == SRA_UTIL_MEASUREMENT_KILOMETER || $keys[$i] == SRA_UTIL_MEASUREMENT_METER || $keys[$i] == SRA_UTIL_MEASUREMENT_CENTIMETER || $keys[$i] == SRA_UTIL_MEASUREMENT_GRAM || $keys[$i] == SRA_UTIL_MEASUREMENT_KILOGRAM)) || (!$useMetric && ($keys[$i] == SRA_UTIL_MEASUREMENT_FOOT || $keys[$i] == SRA_UTIL_MEASUREMENT_INCH || $keys[$i] == SRA_UTIL_MEASUREMENT_MILE || $keys[$i] == SRA_UTIL_MEASUREMENT_YARD || $keys[$i] == SRA_UTIL_MEASUREMENT_OUNCE || $keys[$i] == SRA_UTIL_MEASUREMENT_POUND))) {
              if ($normalized >= $conversions[$keys[$i]]['val'] && (!$lastUnit || $normalized < $conversions[$lastUnit]['val'])) {
                $unit = $keys[$i];
                break;
              }
              $lastUnit = $keys[$i];
            }
          }
          if (!$unit) { $unit = $lastUnit ? $lastUnit : $dataUnit; }
        }
        if ($conversions[$unit]) {
          eval('$normalized' . $conversions[$unit]['op'] . '=' . $conversions[$unit]['val'] . ';');
        }
      }
      if ($unit && $labelsHash[$unit]) {
        $labels = array_reverse(explode(',', $labelsHash[$unit]));
        $suffix = $suffix === 1 || $suffix === 2 || $suffix === 3 ? $labels[$suffix == 1 ? $useIdx : (($suffix == 2 ? 0 : 2))] . ($suffix === 3 && $normalized > 1 ? 's' : '') : $suffix;
        if (SRA_Util::endsWith($suffix, 'ss')) { $suffix = substr($suffix, 0, -1); }
        if (substr($suffix, 0, 1) == ':') { $suffix = substr($suffix, 1); }
        if ($normalized <= 1 && SRA_Util::endsWith($suffix, 's')) { $suffix = substr($suffix, 0, -1); }
        if ($normalized > 1 && $useLower && !SRA_Util::endsWith($suffix, 's')) { $suffix .= 's'; }
        if ($useLower && $useUpper) { $suffix = strtoupper(substr($suffix, 0, 1)) . substr($suffix, 1); }
      }
    }
    if (strpos($normalized, '.') !== FALSE) { $normalized = SRA_Util::trimTrailingZeros(round($normalized, $decimals))*1; }
		if ($suffix == 'feets') $suffix = 'feet';
		if ($suffix == 'feet' && $normalized == 1) $suffix = 'foot';
    return $normalized . (!$suffix || $suffix === 1 || $suffix === 2 || $suffix === 3 ? '' : ($includeSpace ? ' ' : '') . $suffix);
	}
	// }}}
  
	// {{{ parsePhpSource
	/**
	 * this method parses a PHP 4.x source file and utilizes javadoc formatted 
   * comments to construct a hash of metadata pertaining to that file. this hash 
   * will contain the following keys (comment values can be imbedded using the
   * localizeable format "{$[app resource bundle string]}"):
   *   constants:     array of hashes (indexed by name) with the following keys:
   *     comment:     the constant comment
   *     name:        the name of the constant
   *     value:       the constant value
   *     [other]:     other metadata (metadata value keys are prefixed with @)
   *   classes:       array of hashes (indexed by name) with the following keys:
   *     access:      the class access - if specified (e.g. public, private)
   *     comment:     the class comment
   *     extends:     the name of another class extended by this class
   *     name:        the class name
   *     [other]:     other metadata (metadata value keys are prefixed with @)
   *     attrs:       array of hashes (indexed by name) with the following keys:
   *       access:    attribute access - if specified (e.g. public, private)
   *       comment:   the attribute comment
   *       name:      the name of the attribute
   *       static:    TRUE if attribute is static
   *       value:     the attribute value
   *       [other]:   other metadata (metadata value keys are prefixed with @)
   *     methods:     array of hashes (indexed by name) with the following keys:
   *       access:    method access - if specified (e.g. public, private)
   *       comment:   the method comment
   *       name:      the name of the method
   *       params:    array of hashes (indexed by name) with the following keys:
   *         byRef:   whether or not this parameter is passed by reference
   *         comment: the parameter comment
   *         name:    the name of the parameter
   *         type:    the parameter data type
   *         value:   the default parameter value (if applicable)
   *       returnRef: whether or not this method returns value by reference
   *       static:    TRUE if method is static
   *       [other]:   other metadata (metadata value keys are prefixed with @)
   *    functions:    same as 'methods' documented above, but for non-class 
   *                  defined functions
	 * @param string $file the path to the php source file that should be parsed
   * @param boolean $cache whether or not the parse results should be cached
   * (cache will be updated if the source file changes)
   * @access public
	 * @return mixed
	 */
	function &parsePhpSource($file, $cache=TRUE) {
    if (!file_exists($file)) {
      return NULL;
    }
    $ckey = 'sra_parse_php_source_' . str_replace('/', '.', $file);
    if ($cache) include_once('SRA_Cache.php');
    
    if ($cache && ($modtime = SRA_Cache::cacheIsset($ckey, TRUE)) && $modtime > filemtime($file)) return SRA_Cache::getCache($ckey);
    
    $resources =& SRA_Controller::getAppResources();
    $inClass = NULL;
		$metadata = array();
    $lines = file($file);
    foreach(array_keys($lines) as $i) {
      $mergeComment = FALSE;
      $mergePoint = NULL;
      $lines[$i] = trim($lines[$i]);
      $line = $lines[$i];
      if ($inComment && (SRA_Util::beginsWith($line, '*/') || SRA_Util::endsWith($line, '*/'))) {
        $inComment = FALSE;
        if ($api['comment'] && preg_match_all('/\{\$(\w*|.*|_*)\}/', $api['comment'], $m)) {
          foreach($m[1] as $key => $val) {
            $api['comment'] = str_replace($m[0][$key], $resources->getString($m[1][$key]), $api['comment']);
          }
        }
      }
      else if ($inComment) {
        $pline = substr($line, strpos($line, '*') + 1);
        
        if (SRA_Util::beginsWith($line, '*')) { $line = $pline . "\n"; }
        if (SRA_Util::beginsWith(trim($line), '@') && preg_match('/@[\S]+/', trim($line), $m)) { 
          $apiIdx = substr($m[0], 1);
          $line = trim(substr(trim($line), strlen($apiIdx) + 1));
          if (isset($api[$apiIdx])) { 
            is_array($api[$apiIdx]) ? $api[$apiIdx][] = '' : $api[$apiIdx] = array($api[$apiIdx], ''); 
          }
        }
        $val = ((is_array($api[$apiIdx]) && $api[$apiIdx][count($api[$apiIdx]) - 1]) || (!is_array($api[$apiIdx]) && $api[$apiIdx]) ? ' ' : '') . $line;
        is_array($api[$apiIdx]) ? $api[$apiIdx][count($api[$apiIdx]) - 1] .= $val : $api[$apiIdx] .= $val;
      }
      // javadoc comment start
      else if (!$inComment && SRA_Util::beginsWith($line, '/**')) {
        $cspaces = NULL;
        $inComment = TRUE;
        $api = array();
        $apiIdx = 'comment';
      }
      // constant
      else if (!$inComment && SRA_Util::beginsWith($line, 'define(') && preg_match('/define\([\'\"](.*)[\'\"]\,(.*)\)/', $line, $m) && count($m) == 3) {
        $name = trim($m[1]);
        if (!isset($metadata['constants'])) { $metadata['constants'] = array(); }
        $metadata['constants'][$name] = array('name' => $name, 'value' => trim($m[2]));
        $mergeComment = TRUE;
        $mergePoint = array('constants', "'$name'");
      }
      // class
      else if (!$inComment && (preg_match('/class\s(.*)\sextends\s(.*)\s?/', $line, $m) || preg_match('/class\s(.*)\s/', $line, $m))) {
				$access = preg_match('/([a-z]+)\s+function/', $line, $m1) ? $m1[1] : NULL;
        if (!isset($metadata['classes'])) { $metadata['classes'] = array(); }
        $className = trim($m[1]);
        $metadata['classes'][$className] = array('name' => $className);
				if ($access) $metadata['classes'][$className]['access'] = $access;
        if (isset($m[2]) && trim($m[2])) { $metadata['classes'][$className]['extends'] = trim(str_replace('{', '', $m[2])); }
        $mergeComment = TRUE;
        $mergePoint = array('classes', "'$className'");
      }
      // attributes
      else if (!$inComment && $className && (preg_match('/var (.*)\s?=\s?(.*)\s?;/', $line, $m) || preg_match('/var (.*)\s?;/', $line, $m))) {
				if ($isStatic = preg_match('/static.*function/', $line) ? TRUE : FALSE) $line = str_replace('static ', '', $line);
				$access = preg_match('/([a-z]+)\s+function/', $line, $m1) ? $m1[1] : NULL;
        if (!isset($metadata['classes'][$className]['attrs'])) { $metadata['classes'][$className]['attrs'] = array(); }
        $m[1] = trim(str_replace('$', '', $m[1]));
        $metadata['classes'][$className]['attrs'][$m[1]] = array('name' => $m[1]);
				if ($access) $metadata['classes'][$className]['attrs'][$m[1]]['access'] = $access;
				if ($isStatic) $metadata['classes'][$className]['attrs'][$m[1]]['static'] = TRUE;
        if (isset($m[2]) && trim($m[2])) { $metadata['classes'][$className]['attrs'][$m[1]]['value'] = trim($m[2]); }
        $mergeComment = TRUE;
        $mergePoint = array('classes', "'$className'", 'attrs', "'$m[1]'");
      }
      // methods/functions
      else if (!$inComment && preg_match('/function\s+([\S]+)\((.*)\)/', $line, $m)) {
				if ($isStatic = preg_match('/static.*function/', $line) ? TRUE : FALSE) $line = str_replace('static ', '', $line);
				$access = preg_match('/([a-z]+)\s+function/', $line, $m1) ? $m1[1] : NULL;
        $code = '$metadata' . ($className ? "['classes']['$className']['methods']" : "['functions']");
        eval("if (!isset($code)) $code = array();");
        $returnRef = SRA_Util::beginsWith(trim($m[1]), '&') ? '1' : '0';
        if ($returnRef) $m[1] = trim(substr(trim($m[1]), 1));
        $idx = $m[1];
        
        eval($code . '["' . $idx . '"] = array("name" => $m[1]' . ($access ? ', "access" => "' . $access . '"' : '') . ', "returnRef" => $returnRef' . ($isStatic ? ', "static" => TRUE' : '') . ');');
        if (trim($m[2])) {
          $papi = NULL;
          for($n=$i-1; $n>0; $n--) {
            if (!$lines[$n]) { 
              continue; 
            }
            else if ((SRA_Util::beginsWith($lines[$n], '*/') || SRA_Util::endsWith($lines[$n], '*/')) && isset($api['param'])) {
              $papi = $api['param'];
              unset($api['param']);
            }
            break;
          }
          eval($code . '["' . $idx . '"]["params"] = array();');
          foreach(explode(',', $m[2]) as $param) {
            preg_match('/\$(.*)\s?=\s?(.*)/', $param, $m);
            $param = array('name' => trim($m ? '$' . $m[1] : $param));
            $param['byRef'] = SRA_Util::beginsWith($param['name'], '&') ? '1' : '0';
            if ($param['byRef']) $param['name'] = trim(substr($param['name'], 1));
            $param['name'] = str_replace('$', '', $param['name']);
            
            if ($m) { $param['value'] = trim($m[2]); }
            if ($papi) {
              if (!is_array($papi)) { $papi = array($papi); }
              foreach($papi as $p) {
                $pieces = explode(' ', $p);
                foreach(array_keys($pieces) as $pkey) { if (!trim($pieces[$pkey])) { unset($pieces[$pkey]); } }
                $pp = array(); foreach($pieces as $tmp) { $pp[] = SRA_Util::beginsWith(trim($tmp), '$') ? substr($tmp, 1) : $tmp; }
                if ($pp[0] == $param['name'] || $pp[1] == $param['name'] || $pp[0] == substr($param['name'], 1) || $pp[1] == substr($param['name'], 1)) {
                  if ($pp[1] == $param['name'] || $pp[1] == substr($param['name'], 1)) { $param['type'] = trim($pp[0]); }
                  $param['comment'] = trim(substr($p, strpos($p, substr($param['name'], 1)) + strlen(substr($param['name'], 1))));
                  if ($param['comment'] && preg_match_all('/\{\$(\w*|.*|_*)\}/', $param['comment'], $m)) {
                    foreach($m[1] as $key => $val) {
                      $param['comment'] = str_replace($m[0][$key], $resources->getString($m[1][$key]), $param['comment']);
                    }
                  }
                }
              }
            }
            eval($code . '["' . $idx . '"]["params"][$param["name"]] = $param;');
          }
        }
        $mergeComment = TRUE;
        $mergePoint = $className ? array('classes', "'$className'", 'methods', "'$idx'") : array('functions', "'$idx'");
      }
      
      if ($mergeComment) {
        for($n=$i-1; $n>0; $n--) {
          if (!$lines[$n]) { 
            continue; 
          }
          else if (SRA_Util::beginsWith($lines[$n], '*/') || SRA_Util::endsWith($lines[$n], '*/')) {
            $code = '$metadata';
            foreach($mergePoint as $key) { $code .= "[$key]"; }
            eval($code . ' = array_merge(' . $code . ', $api);');
          }
          break;
        }
      }
    }
    
    if ($cache) SRA_Cache::setCache($ckey, $metadata);
    
		// print_r($metadata);
		// exit;
    return $metadata;
	}
	// }}}
  
	// {{{ parseDtd
	/**
	 * used to parse a DTD. the return value will be a hash with the following 
   * values:
   *   comment:      any comments provided for the DTD
   *   entities:     a hash of DTD entities indexed by entity name with the 
   *                 following sub-keys:
   *     name:       the entity name
   *     value:      the entity value
   *     comment:    the entity comment
   *   elements:     a hash of DTD elements indexed by element name with the 
   *                 following sub-keys:
   *     name:       the element name
   *     comment:    the element comment
   *     elements:   an array of sub-elements where the value contains the 
   *                 following sub-keys:
   *       name:     the sub-element name
   *       set:      if the sub-element is a choice of different sub-elements, 
   *                 this sub-key will be populated instead of 'name' which is 
   *                 an array of the names of possible sub-elements
   *       required: whether or not this sub-element is required
   *       many:     whether or not many of these sub-elements may exist (if 
   *                 FALSE, only 1 sub-element of this type may exist)
   *     mixed:      TRUE if the sub-elements are mixed (* modified) - meaning 
   *                 this element may contain zero or more occurrences of them
   *     attributes: an array of attributes that this element may contain 
   *                 indexed by attribute name where each attribute may have the 
   *                 following sub-keys:
   *       name:     the attribute name
   *       comment:  the attribute comment
   *       options:  if this attribute can be one of a set of options, this 
   *                 attribute will be an array representing those options
   *       type:     the attribute type, either CDATA, PCDATA, ID, IDREF, 
   *                 'options' (if 'options' is set), or an entity name
   *       default:  the  default value
   *       required: whether or not this attribute is required
   *     used:       an array of names of other elements where this element is 
   *                 used (not specified for the root element)
   *   root:         the name of the root element
	 * @param string $file the absolute path to the DTD to parse
   * @param boolean $cache whether or not the parse results should be cached
   * (cache will be updated if the source file changes)
	 * @return mixed
	 */
	function parseDtd($file, $cache=TRUE) {
    if (!file_exists($file)) {
      return NULL;
    }
    $ckey = 'sra_parse_dtd_' . str_replace('/', '.', $file);
    if ($cache) include_once('SRA_Cache.php');
    
    if ($cache && ($modtime = SRA_Cache::cacheIsset($ckey, TRUE)) && $modtime > filemtime($file)) {
      return SRA_Cache::getCache($ckey);
    }
    
    $inComment = FALSE;
    $comments = array();
    $metadata = array('comment' => NULL, 'entities' => array(), 'elements' => array(), 'root' => NULL);
    $lines = file($file);
    foreach(array_keys($lines) as $i) {
      $m = NULL;
      $line = trim($lines[$i]);
      if (!$line && !$inComment) continue;
      
      // comment end
      if ($inComment && SRA_Util::endsWith($line, '-->')) {
        $comment .= substr($line, 0, strpos($line, '-->'));
        $inComment = FALSE;
        if (trim($comment)) array_push($comments, $comment);
      }
      // add to comment
      else if ($inComment) {
        if (!preg_match('/Used in:/i', $line)) $comment .= $lines[$i];
      }
      // comment start
      else if (!$inComment && SRA_Util::beginsWith($line, '<!--')) {
        $comment = substr($line, 4);
        $inComment = TRUE;
      }
      // entity
      else if (preg_match('/<![\s]*entity[\s]*%[\s]*([\S]+)[\s]*([\S]+)[\s]*>/i', $line, $m)) {
        $metadata['entities'][$m[1]] = array('name' => $m[1], 'value' => SRA_Util::stripQuotes($m[2]));
        if (count($comments)) $metadata['entities'][$m[1]]['comment'] = array_pop($comments);
      }
      // element
      else if (preg_match('/<![\s]*element[\s]+([\S]+)[\s]*\(([\s\S]+)\)([*]?)[\s]*>/i', $line, $m) || preg_match('/<![\s]*element[\s]+([\S]+)[\s]*([\S]+)[\s]*>/i', $line, $m)) {
        $metadata['elements'][$m[1]] = array('name' => $m[1]);
        if (trim(strtolower($m[2])) != 'empty') {
          $metadata['elements'][$m[1]]['elements'] = array();
          $metadata['elements'][$m[1]]['mixed'] = $m[3] == '*';
          $m[2] = SRA_Util::stripQuotes($m[2], '(', ')');
          $m[2] = explode(',', $m[2]);
          $subelements = array();
          foreach($m[2] as $name) {
            $name = trim($name);
            $mod = NULL;
            if (SRA_Util::endsWith($name, '*') || SRA_Util::endsWith($name, '+') || SRA_Util::endsWith($name, '?')) {
              $mod = substr($name, -1, 1);
              $name = substr($name, 0, -1);
            }
            $set = NULL;
            if (strpos($name, '|')) {
              $name = SRA_Util::stripQuotes($name, '(', ')');
              $set = explode('|', $name);
              foreach(array_keys($set) as $skey) {
                $set[$skey] = trim($set[$skey]);
              }
            }
            $subelement = array();
            $subelement[$set ? 'set' : 'name'] = $set ? $set : $name;
            $subelement['required'] = $mod != '*' && $mod != '?' ? TRUE : FALSE;
            $subelement['many'] = $mod && $mod != '?' ? TRUE : FALSE;
            $metadata['elements'][$m[1]]['elements'][] = $subelement;
          }
        }
        if (count($comments)) $metadata['elements'][$m[1]]['comment'] = array_pop($comments);
      }
      // attribute
      else if (($inAttrList && preg_match('/[\s]*([\S]+)[\s]+([\S]+)[\s]+([\S]+)/', $line, $m)) || (!$inAttrList && preg_match('/<![\s]*attlist[\s]+([\S]+)[\s]+([\S]+)[\s]+([\S]+)[\s]+([\S]+)/i', $line, $m)) || (!$inAttrList && preg_match('/<![\s]*attlist[\s]+([\S]+)/i', $line, $m))) {
        if (count($m) < 3) {
          $attrElement = $m[1];
          $inAttrList = TRUE;
          continue;
        }
        
        $attrElement = trim($inAttrList ? $attrElement : $m[1]);
        $name = trim($inAttrList ? $m[1] : $m[2]);
        $type = trim($inAttrList ? $m[2] : $m[3]);
        if (substr($type, 0, 1) == '%' && substr($type, -1, 1) == ';') $type = substr($type, 1, -1);
        $default = trim($inAttrList ? $m[3] : $m[4]);
        if (substr($default, -1, 1) == '>') $default = substr($default, 0, -1);
        
        $inAttrList = preg_match('/>/', $line) ? FALSE : TRUE;
        if (!isset($metadata['elements'][$attrElement])) $metadata['elements'][$attrElement] = array('name' => $attrElement);
        if (!isset($metadata['elements'][$attrElement]['attributes'])) $metadata['elements'][$attrElement]['attributes'] = array();
        $metadata['elements'][$attrElement]['attributes'][$name] = array('name' => $name, 'type' => $type, 'required' => strtolower($default) == '#required');
        if (substr($default, 0, 1) != '#') {
          $metadata['elements'][$attrElement]['attributes'][$name]['default'] = $default;
        }
        if (substr($type, 0, 1) == '(' && substr($type, -1, 1) == ')') {
          $type = SRA_Util::stripQuotes($type, '(', ')');
          $options = explode('|', $type);
          foreach(array_keys($options) as $okey) {
            $options[$okey] = trim($options[$okey]);
          }
          $metadata['elements'][$attrElement]['attributes'][$name]['options'] = $options;
          $metadata['elements'][$attrElement]['attributes'][$name]['type'] = 'options'; 
        }
      }
      // end attribute
      else if ($inAttrList && preg_match('/>/', $line)) {
        $inAttrList = FALSE;
      }
    }
    
    // extract attribute comments from element if applicable
    foreach(array_keys($metadata['elements']) as $element) {
      if (isset($metadata['elements'][$element]['comment']) && isset($metadata['elements'][$element]['attributes'])) {
        $comment = explode("\n", $metadata['elements'][$element]['comment']);
        $ckeys = array_keys($comment);
        foreach(array_keys($metadata['elements'][$element]['attributes']) as $attr) {
          if (!isset($metadata['elements'][$element]['attributes'][$attr]['comment'])) {
            $inComment = FALSE;
            $acomment = '';
            foreach($ckeys as $ckey) {
              if (!isset($comment[$ckey])) continue;
              
              if (!$inComment && preg_match('/^' . $attr . '[\s]+/', $comment[$ckey])) {
                $acomment = trim(substr($comment[$ckey], strlen($attr) + 1));
                $commentBuff = '';
                for($i=0; $i<strlen($comment[$ckey]) - strlen($acomment)-1; $i++) {
                  $commentBuff .= ' ';
                }
                unset($comment[$ckey]);
                $inComment = TRUE;
              }
              else if ($inComment && (!$comment[$ckey] || preg_match('/^[\s]/', $comment[$ckey]))) {
                $acomment .= "\n" . str_replace($commentBuff, '', str_replace('	', ' ', $comment[$ckey]));
                unset($comment[$ckey]);
              }
              else if ($inComment) {
                $inComment = FALSE;
                break;
              }
            }
            if ($acomment) {
              $metadata['elements'][$element]['attributes'][$attr]['comment'] = $acomment;
            }
          }
        }
        $metadata['elements'][$element]['comment'] = trim(implode("\n", $comment));
      }
    }
    
    // Determine root element
    $elements = array();
    foreach(array_keys($metadata['elements']) as $element) {
      if (!isset($elements[$element])) {
        $elements[$element] = array();
      }
      foreach(array_keys($metadata['elements'][$element]['elements']) as $i) {
        $subelements = isset($metadata['elements'][$element]['elements'][$i]['name']) ? array($metadata['elements'][$element]['elements'][$i]['name']) : $metadata['elements'][$element]['elements'][$i]['set'];
        foreach($subelements as $subelement) {
          if (substr($subelement, 0, 1) == '#') continue;
          
          if (!isset($elements[$subelement])) {
            $elements[$subelement] = array();
          }
          $elements[$subelement][] = $element;
        }
      }
    }
    foreach($elements as $element => $within) {
      if (!$within) {
        $metadata['root'] = $element;
      }
      else {
        $metadata['elements'][$element]['used'] = $within;
      }
    }
    
    // DTD comment
    if (count($comments)) {
      while($comment = array_pop($comments)) {
        if (SRA_Util::beginsWith(trim($comment), '+~~')) continue;
        $metadata['comment'] = $comment;
        break;
      }
    }
    
    if ($cache) {
      SRA_Cache::setCache($ckey, $metadata);
    }
    
    return $metadata;
	}
	// }}}
  
	// {{{ validateStaticMethodPath
	/**
	 * used to validate a static method path which is a class path followed by two 
   * colons and the method name. to do so, this method includes the class file 
   * and verifies that the method exists in the class. returns TRUE if the path 
   * is valid, FALSE otherwise. Here are some examples of static method paths:
   *   'users/User::validateEmail' - looks for class in 
   *                                   [app]/lib/users/User.php and method 
   *                                   'validateEmail' in that class
   * NOTE: the .php extension is not needed
	 * @param string $path the static method path to validate
   * @param boolean $returnFilePath when true, the absolute path to the PHP 
   * source file will be returned if $path is valid instead of TRUE or FALSE
   * @access public
	 * @return mixed
	 */
	function validateStaticMethodPath($path, $returnFilePath=FALSE) {
    $valid = FALSE;
    $phpExt = '.' . SRA_SYS_PHP_EXTENSION;
    if (preg_match('/(.*)::(.*)/', $path, $m) && count($m) == 3 && file_exists($file = SRA_File::getRelativePath(FALSE, $m[1] . (SRA_Util::endsWith($m[1], $phpExt) ? '' : $phpExt), 'lib'))) {
      include_once($file);
      $m[2] = trim(strtolower($m[2]));
      foreach(get_class_methods(substr(basename($file), 0, strlen($phpExt)*-1)) as $method) {
        if (trim(strtolower($method)) == $m[2]) {
          $valid = TRUE;
          break;
        }
      }
    }
    return $returnFilePath && $valid && $file ? $file : $valid;
	}
	// }}}
  
	// {{{ invokeStaticMethodPath
	/**
	 * invokes a static method using a static method path (see 
   * 'validateStaticMethodPath' above) and returns the results of invoking that 
   * method. returns NULL if the method is not valid
	 * @param string $path the path to static method to invoke
   * @param array $params an array of  parameters to invoke. these will be 
   * passed into the method as arguments where the first value in the array is 
   * the first argument, the 2nd is the second, and so on. this parameter is 
   * optional
   * @access public
	 * @return mixed
	 */
	function &invokeStaticMethodPath($path, &$params) {
    $ret = NULL;
    $phpExt = '.' . SRA_SYS_PHP_EXTENSION;
    if ((!$params || is_array($params)) && preg_match('/(.*)::(.*)/', $path, $m) && count($m) == 3 && file_exists($file = SRA_File::getRelativePath(FALSE, $m[1] . (SRA_Util::endsWith($m[1], $phpExt) ? '' : $phpExt), 'lib'))) {
      include_once($file);
      $className = substr(basename($file), 0, strlen($phpExt)*-1);
      $m[2] = trim(strtolower($m[2]));
      foreach(get_class_methods($className) as $method) {
        if (trim(strtolower($method)) == $m[2]) {
          $methodName = $method;
          break;
        }
      }
      if ($methodName) {
        $code = '$ret =& ' . $className . '::' . $methodName . '(';
        if ($params) {
          $keys = array_keys($params);
          foreach($keys as $key) {
            $code .= $key == $keys[0] ? '' : ', ';
            $code .= '$params[' . $key . ']';
          }
        }
        $code .= ');';
        eval($code);
      }
    }
    return $ret;
	}
	// }}}
  
	// {{{ getStaticMethodPathApi
	/**
	 * returns the api (if $path is properly documented) for the class and method 
   * defined by $path. this is a hash with the same keys as defined for methods 
   * in SRA_Util::parsePhpSource. returns NULL if unsuccessful
	 * @param string $path the static method path to return the api for
   * @access public
	 * @return hash
	 */
	function getStaticMethodPathApi($path) {
    if (($api =& SRA_Util::parsePhpSource(SRA_Util::validateStaticMethodPath($path, TRUE))) && isset($api['classes'])) {
      foreach(array_keys($api['classes']) as $key) {
        if (isset($api['classes'][$key]['name']) && strpos($path, $api['classes'][$key]['name'] . '::') !== FALSE && isset($api['classes'][$key]['methods'])) {
          foreach(array_keys($api['classes'][$key]['methods']) as $mkey) {
            if (strpos($path, $api['classes'][$key]['name'] . '::' . $api['classes'][$key]['methods'][$mkey]['name']) !== FALSE) {
              return $api['classes'][$key]['methods'][$mkey];
            }
          }
        }
      }
    }
    return NULL;
	}
	// }}}
  
	// {{{ ping
	/**
	 * pings $host and returns TRUE if it is reachable, FALSE otherwise
	 * @param string $host the host to ping. either a hostname or IP address
   * @access public
	 * @return boolean
	 */
	function ping($host) {
    require_once('model/SRA_AttributeValidator.php');
    $ip = SRA_AttributeValidator::ip($host) ? $host : gethostbyname($host);
    $ping = shell_exec(SRA_File::findInPath('ping') . ' -c 1 -w 1 ' . $ip);
    return SRA_AttributeValidator::ip($ip) && preg_match('/ 0% packet loss/', $ping) ? TRUE : FALSE;
	}
	// }}}
  
	// {{{ fork
	/**
	 * executes a command as a new process. this command will fork off the current 
   * php process and thus will not stall execution. returns the PID of the 
   * forked command if successful
	 * @param string $cmd the command to fork
	 * @param boolean $nohup whether or not to use the nohp execution option for 
	 * $cmd
   * @access public
	 * @return int
	 */
	function fork($cmd, $nohup = FALSE) {
	  if ($nohup) $nohup = SRA_File::findInPath('nohup');
    exec((is_file($nohup) ? $nohup . ' ' : '') . $cmd . ' > /dev/null 2>&1 &');
    return SRA_Util::getProcessId($cmd);
	}
	// }}}
  
	// {{{ getServiceName
	/**
	 * uses the /etc/services file to attempt to determine the name of the service 
   * for a given port
	 * @param int $port the port to lookup
   * @param string $proto the protocol (tcp or udp)
   * @access public
	 * @return string
	 */
  function getServiceName($port, $proto='tcp') {
    static $_utilServiceMappings;
    if (!$_utilServiceMappings && file_exists(SRA_UTIL_SERVICES_CONFIG)) {
      $_utilServiceMappings = array();
      foreach(file(SRA_UTIL_SERVICES_CONFIG) as $line) {
        if (preg_match('/^(\S*)\s*(\S*)\s/', $line, $m)) {
          $_utilServiceMappings[$m[2]] = $m[1];
        }
      }
    }
    
    return $_utilServiceMappings && isset($_utilServiceMappings[$port . '/' . $proto]) ? $_utilServiceMappings[$port . '/' . $proto] : NULL;
  }
  // }}}
  
	// {{{ getServicePort
	/**
	 * uses the /etc/services file to attempt to determine the port used by 
   * $services
	 * @param string $service the name of the service to return the port for
   * @access public
	 * @return int
	 */
  function getServicePort($service) {
    static $_utilServiceMappings1;
    if (!$_utilServiceMappings1 && file_exists(SRA_UTIL_SERVICES_CONFIG)) {
      $_utilServiceMappings1 = array();
      foreach(file(SRA_UTIL_SERVICES_CONFIG) as $line) {
        if (preg_match('/^(\S*)\s*(\S*)\s/', $line, $m)) {
          $tmp = explode('/', $m[2]);
          $_utilServiceMappings1[strtolower($m[1])] = $tmp[0]*1;
        }
      }
    }
    
    return $_utilServiceMappings1 && isset($_utilServiceMappings1[strtolower($service)]) ? $_utilServiceMappings1[strtolower($service)] : NULL;
  }
  // }}}
  
	// {{{ isPrivateNetworkIp
	/**
	 * returns TRUE if $ip is on a private IPv4 network (10.0.0.0-10.255.255.255 
   * OR 172.16.0.0-172.31.255.255 OR 192.168.0.0-192.168.255.255)
	 * @param string $ip the IP address to check
   * @access public
	 * @return boolean
	 */
  function isPrivateNetworkIp($ip) {
    $pieces = explode('.', $ip);
    return count($pieces) == 4 && ($pieces[0] == 10 || ($pieces[0] == 172 && $pieces[1] >= 16 && $pieces[1] <= 31) || ($pieces[0] == 192 && $pieces[1] == 168)) ? TRUE : FALSE;
  }
  // }}}
  
	// {{{ createThumbnail
	/**
	 * creates a thumbnail image using the php gd image manipulation functions. 
   * returns TRUE on success, FALSE otherwise. the proportional dimensions of 
   * the image will be maintained, so the thumbnail image will have either 
   * height=$height OR width=$width but not both and neither height nor width in 
   * the thumbnail will exceed the $height and $width specified. this method 
   * uses ImageMagick if it is installed, php-gd otherwise
	 * @param string $src the source image. should have a correct file extension:
   * jpg, gif or png
   * @param int $theight the maximum height. at least 1, theight or twidth must 
   * be specified
   * @param int $twidth the maximum width. at least 1, theight or twidth must be
   * specified
   * @param string $thumb path to the thumbnail image that should be created. if 
   * not specified, the new image will be named the same as $src but with a "-t"
   * suffix (preceeding the file extension)
   * @access public
	 * @return boolean
	 */
  function createThumbnail($src, $theight, $twidth, $thumb) {
    $ret = FALSE;
    if ((($imageMagick = SRA_File::findInPath(SRA_FILE_ATTRIBUTE_IMAGE_MAGICK_CONVERT)) || function_exists('imagecreatefromjpeg')) && 
        ($attrs = getimagesize($src)) && $attrs[0] && $attrs[1] && $attrs[2] && ($theight || $twidth)) {
      $ewidth = $attrs[0];
      $eheight = $attrs[1];
      if (!$theight) $theight = $twidth;
      if (!$twidth) $twidth = $theight;
      
      if ($ewidth > $eheight) {
        $width = $twidth;
        $height = round($eheight*($theight/$ewidth));
      }
      if ($ewidth < $eheight) {
        $width = round($ewidth*($twidth/$eheight));
        $height = $theight;
      }
      if ($ewidth == $eheight) {
        $width = $twidth;
        $height = $theight;
      }
      $thumb = $thumb ? $thumb : dirname($src) . '/' . SRA_Util::getFileNameWOExtension($src) . '-t.' . SRA_Util::getFileExtension($src);
      
      if ($imageMagick) {
        $cmd = $imageMagick  . ' -thumbnail ' . $width . 'x' . $height . ' ' . $src . '[0] ' . $thumb;
        exec($cmd);
        $ret = file_exists($thumb);
      }
      else {
        switch($attrs[2]) {
          case IMAGETYPE_GIF:
            $i = imagecreatefromgif($src);
            break;
          case IMAGETYPE_JPEG:
            $i = imagecreatefromjpeg($src);
            break;
          case IMAGETYPE_PNG:
            $i = imagecreatefrompng($src);
            break;
        }
        if ($i) {
          $t = ImageCreateTrueColor($width, $height);
          imagecopyresampled($t, $i, 0, 0, 0, 0, $width, $height, $ewidth, $eheight);
          switch($attrs[2]) {
            case IMAGETYPE_GIF:
              imagegif($t, $thumb);
              break;
            case IMAGETYPE_JPEG:
              imagejpeg($t, $thumb);
              break;
            case IMAGETYPE_PNG:
              imagepng($t, $thumb);
              break;
          }
          imagedestroy($i);
          $ret = $t ? TRUE : FALSE;
          if ($t) imagedestroy($t);
        }
      }
    }
    return $ret;
  }
  // }}}
  
  
  /**
   * PHP implementation of the Porter Stemming Algorithm written by Iain Argent 
   * for Complinet Ltd., 17/2/00. Translated from the PERL version at 
   * http://www.muscat.com/~martin/p.txt. Version 1.1 (Includes British English 
   * endings). Reduces words to their base stem for search engines and indexing
   * this code was borrowed from http://www.weberdev.com/get_example-1503.html
   * @param string $word the word to step
   * @return string
   */
  function stem($word) {
    $step2list=array('ational'=>'ate', 'tional'=>'tion', 'enci'=>'ence', 
                     'anci'=>'ance', 'izer'=>'ize', 'iser'=>'ise', 'bli'=>'ble', 
                     'alli'=>'al', 'entli'=>'ent', 'eli'=>'e', 'ousli'=>'ous', 
                     'ization'=>'ize', 'isation'=>'ise', 'ation'=>'ate', 
                     'ator'=>'ate', 'alism'=>'al', 'iveness'=>'ive', 
                     'fulness'=>'ful', 'ousness'=>'ous', 'aliti'=>'al', 
                     'iviti'=>'ive', 'biliti'=>'ble', 'logi'=>'log');
    
    $step3list=array('icate'=>'ic', 'ative'=>'', 'alize'=>'al', 'alise'=>'al', 
                     'iciti'=>'ic', 'ical'=>'ic', 'ful'=>'', 'ness'=>'');
    
    $c = "[^aeiou]"; # consonant
    $v = "[aeiouy]"; # vowel
    $C = "${c}[^aeiouy]*"; # consonant sequence
    $V = "${v}[aeiou]*"; # vowel sequence
    
    $mgr0 = "^(${C})?${V}${C}"; # [C]VC... is m>0
    $meq1 = "^(${C})?${V}${C}(${V})?" . '$'; # [C]VC[V] is m=1
    $mgr1 = "^(${C})?${V}${C}${V}${C}"; # [C]VCVC... is m>1
    $_v = "^(${C})?${v}"; # vowel in stem
    
    if (strlen($word)<3) return $word;
    
    $word=preg_replace("/^y/", "Y", $word);
    
    #Step 1a
    $word=preg_replace("/(ss|i)es$/", "\\1", $word);        # sses-> ss, ies->es
    $word=preg_replace("/([^s])s$/", "\\1", $word);         #        ss->ss but s->null
    
    #Step 1b
    if (preg_match("/eed$/", $word)) {
      $stem=preg_replace("/eed$/", '', $word);
      if (preg_match("/$mgr0/", $stem)) {
        $word=preg_replace("/.$/", '', $word);
      }
    }
    elseif (preg_match("/(ed|er|ing)$/", $word)) {
      $stem=preg_replace("/(ed|er|ing)$/", '', $word);
      if (preg_match("/$_v/", $stem)) {
        $word=$stem;
  
        if (preg_match("/(at|bl|iz|is)$/", $word)) {
          $word=preg_replace("/(at|bl|iz|is)$/", "\\1e", $word);
        }
  
        elseif (preg_match("/([^aeiouylsz])\\1$/", $word)) {
          $word=preg_replace("/.$/", '', $word);
        }
  
        elseif (preg_match("/^${C}${v}[^aeiouwxy]$/", $word)) {
          $word.="e";
        }
      }
    }
    
    #Step 1c (weird rule)
    if (preg_match("/y$/", $word)) {
      $stem=preg_replace("/y$/", '', $word);
      if (preg_match("/$_v/", $stem))
              $word=$stem."i";
    }
    
    #Step 2
    if (preg_match("/(ational|tional|enci|anci|izer|iser|bli|alli|entli|eli|ousli|ization|isation|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/", $word, $matches)) {
      $stem=preg_replace("/(ational|tional|enci|anci|izer|iser|bli|alli|entli|eli|ousli|ization|isation|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/",'', $word);
      $suffix=$matches[1];
      if (preg_match("/$mgr0/", $stem)) {
        $word=$stem.$step2list[$suffix];
      }
    }
    
    #Step 3
    if (preg_match("/(icate|ative|alize|alise|iciti|ical|ful|ness)$/", $word, $matches)) {
      $stem=preg_replace("/(icate|ative|alize|alise|iciti|ical|ful|ness)$/", '', $word);
      $suffix=$matches[1];
      if (preg_match("/$mgr0/", $stem)) {
        $word=$stem.$step3list[$suffix];
      }
    }
    
    #Step 4
    if (preg_match("/(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize|ise)$/", $word, $matches)) {
      $stem=preg_replace("/(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize|ise)$/", '', $word);
      $suffix=$matches[1];
      if (preg_match("/$mgr1/", $stem)) {
        $word=$stem;
      }
    }
    elseif (preg_match("/(s|t)ion$/", $word)) {
      $stem=preg_replace("/(s|t)ion$/", "\\1", $word);
      if (preg_match("/$mgr1/", $stem)) $word=$stem;
    }
    
    #Step 5
    if (preg_match("/e$/", $word, $matches)) {
      $stem=preg_replace("/e$/", '', $word);
      if (preg_match("/$mgr1/", $stem) | (preg_match("/$meq1/", $stem) & ~preg_match("/^${C}${v}[^aeiouwxy]$/", $stem))) {
        $word=$stem;
      }
    }
    if (preg_match("/ll$/", $word) & preg_match("/$mgr1/", $word)) $word=preg_replace("/.$/", '', $word);
    
    # and turn initial Y back to y
    preg_replace("/^Y/", "y", $word);
    
    return $word;
  }
  
  /**
   * parses a URI and return the pieces as a hash containing the following 
   * possible keys (values not present in the URI will not in the hash):
   *   user: username
   *   pswd: password
   *   protocol: protocol (i.e. http, https, ftp) - always lowercase
   *   host: hostname or IP - always lowercase
   *   port: port
   *   path: url path
   *   params: hash of URI parameters
   * this method is not case sensitive. for example, the $uri 
   * "Https://test:mypass@test.com:32/test/script?hello=world&hi=yes" would result in the 
   * following hash being returned:
   *   user => test
   *   pswd => mypass
   *   protocol => https
   *   host => test.com
   *   port => 32
   *   path => /test/script
   *   params => (hash)
   *     hello => world
   *     hi => yes
   * @param string $uri the uri to parse
   * @return hash
   */
  function parseUri($uri) {
    $parsed = NULL;
  	if (preg_match('/([a-zA-Z]+):\/\/((.*)@)?([a-zA-Z0-9\.\-]+)(:([0-9]+))?(\/.*)?(\?(.*))?/i', trim($uri), $m)) {
      $parsed = array();
      if ($m[3]) {
        if ($tmp = strpos($m[3], ':')) {
          $parsed['user'] = substr($m[3], 0, $tmp);
          $parsed['pswd'] = substr($m[3], $tmp + 1);
        }
        else {
          $parsed['user'] = $m[3];
        }
      }
      $parsed['protocol'] = strtolower($m[1]);
      $parsed['host'] = strtolower($m[4]);
      if (count($m) > 5) {
        if ($m[6]) $parsed['port'] = $m[6]*1;
        if ($m[7]) {
          if ($tmp = strpos($m[7], '?')) {
            $parsed['path'] = substr($m[7], 0, $tmp);
            $params = array();
            foreach(explode('&', substr($m[7], $tmp + 1)) as $param) {
              $params[substr($param, 0, $tmp = strpos($param, '='))] = substr($param, $tmp + 1);
            }
            $parsed['params'] = $params;
          }
          else {
            $parsed['path'] = $m[7];
          }
        }
      }
    }
    return $parsed;
  }
  
  
  /**
   * converts an object to a hash recursively using get_object_vars
   * @param object $obj the object to convert
   * @return hash
   */
  function objToHash(&$obj) {
  	if (is_object($obj)) {
      $hash = get_object_vars($obj);
      foreach(array_keys($hash) as $key) {
        if (is_object($hash[$key])) {
          $hash[$key] = SRA_Util::objToHash($hash[$key]);
        }
      }
    }
    return $hash;
  }
  
  
  /**
   * may be used to lookup MX DNS records. the return value will be an array of 
   * hashes each with the following keys
   * @param string $domain the domain to lookup
   * @param string $dns the dns server to use
   * @param int $timeout the amount of time to wait for a response before 
   * timing out
   * @return boolean
   */
  function mxlookup($domain, $dns, $timeout=2) {
    $mx = new _mxlookup($domain, $dns, $timeout);
    return $mx->arrMX ? $mx->arrMX : NULL;
  }
  
  
  /**
   * checks if port is already in use by attempting to make a tcp socket 
   * connection
   * @param int $port the port to check
   * @param string $host the host to verify the port on. if not specified, 
   * localhost will be used
   * @param int $timeout the amount of time in seconds to wait for a response
   * @return boolean
   */
  function portInUse($port, $host='localhost', $timeout=1) {
    if ($sock = fsockopen("tcp://$host", $port, $errno, $errstr, $timeout)) {
      fclose($sock);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  
  
  /**
   * generates a random password based on the parameters specified
   * @param int $length the desired password length - must be between 2 and 64
   * @param int $strength the desired password strength - either 1, 2, or 3 
   * where 1 is the weakest and 3 is the strongest
   * @return string
   */
  function generatePassword($length=8,$level=2){
    list($usec, $sec) = explode(' ', microtime());
    srand((float) $sec + ((float) $usec * 100000));
    
    $length = is_int($length) && $length > 1 && $length < 64 ? $length : 8;
    $level = $level != 1 && $level != 2 && $level != 3 ? 2 : $level;
    
    $validchars = array();
    $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
    $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";
    
    $password  = "";
    $counter   = 0;
    
    while ($counter < $length) {
      $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);
      
      // All character must be different
      if (!strstr($password, $actChar)) {
        $password .= $actChar;
        $counter++;
      }
    }
    
    return $password;
  }
  
  
  /**
   * converts any empty strings in $hash to NULL
   * @param array $hash the hash to convert
   * @param boolean $unsetArr whether or not to unset empty string (or NULL) 
   * sub-elements of $hash
   * @return void
   */
  function emptyStringToNull(&$hash, $unsetArr=TRUE) {
    if (is_array($hash)) {
      foreach(array_keys($hash) as $key) {
        if (is_string($hash[$key]) && !trim($hash[$key])) {
          $hash[$key] = NULL;
        }
        else if (is_array($hash[$key])) {
          SRA_Util::emptyStringToNull($hash[$key]);
          $setNull = TRUE;
          foreach(array_keys($hash[$key]) as $akey) {
            if ($hash[$key][$akey] !== NULL) {
              $setNull = FALSE;
              break;
            }
            else if ($unsetArr) {
              unset($hash[$key][$akey]);
            }
          }
          if ($setNull) $hash[$key] = NULL;
        }
      }
    }
  }
  
  
  /**
   * removes an item or items from $arr matching the regular expression $regex 
   * where the items that are removed use keys or values that match $regex
   * @param array $arr the array to operate on
   * @param string $regex the regular expression to match
   * @param boolean $matchKey whether or not to match the array key (default is 
   * TRUE). if FALSE, array value will be matched
   * @return void
   */
  function removeKeyFromArray(&$arr, $regex, $matchKey=TRUE) {
    foreach(array_keys($arr) as $key) {
      if (preg_match($regex, $matchKey ? $key : $arr[$key])) {
        unset($arr[$key]);
      }
    }
  }
  
  
  /**
   * this method can be used to acquire a semaphore lock using a generally 
   * non-blocking algorithm. It differs from the PHP sem_acquire function in 
   * that it uses the file system and PIDs to maintain semaphores. Once the 
   * current PHP process terminates, the semaphore will be automatically 
   * released if SRA_Util::semRelease has not already been called. this method 
   * returns TRUE if the semaphore was acquired successfully, FALSE otherwise
   * @param string $id a unique identifier for this semaphore. must be unique 
   * for the current application (if an application is initialized)
   * @param int $timeout the number of seconds that the current holder of this 
   * lock (if applicable) can hold this lock. if exceeded, this method will 
   * attempt to kill the process to free the lock
   * @return boolean
   */
  function semAcquire($id, $timeout=NULL) {
    $lockf = SRA_Util::semLockFile($id);
    $result = FALSE;
    
    // check for timeout condition
    if ($timeout && is_numeric($timeout) && $timeout > 0 && 
        ($pid = SRA_Util::semPid($id)) && filemtime($lockf) && 
        (filemtime($lockf) + $timeout) < time()) {
      SRA_Util::killProcess($pid);
    }
    
    if (!SRA_Util::semPid($id)) {
      $key = str_replace(SRA_Controller::getAppTmpDir() . '/.sem-', '', $lockf);
      // convert the key into a numeric value required by sem_get
      $semId = 0;
      for($i=0; $i<strlen($key); $i++) {
        $semId += ord(substr($key, $i, 1));
      }
      if (function_exists('sem_get')) $sem = sem_get($semId);
      if (!function_exists('sem_get') || sem_acquire($sem)) {
        // create lock
        $fp = fopen($lockf, 'w');
        fwrite($fp, getmypid());
        fclose($fp);
        if (function_exists('sem_get')) sem_release($sem);
        SRA_Controller::registerShutdownMethod($cl = 'SRA_Util', 'semRelease', $p = array($id));
        $result = TRUE;
      }
      else {
        $result = FALSE;
      }
    }
    return $result;
  }
  
  /**
   * releases a lock previously acquired using SRA_Util::semAcquire
   * @param string $id the identifier of the semaphore
   * @return boolean
   */
  function semRelease($id) {
    if (file_exists($lockf = SRA_Util::semLockFile($id))) {
      return SRA_File::unlink($lockf);
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * returns the path to the sem lock file for $id
   * @param string $id the identifier of the semaphore
   * @return boolean
   */
  function semLockFile($id) {
    return SRA_Controller::getAppTmpDir() . '/.sem-' . urlencode($id) . '-' . SRA_Controller::getCurrentAppId();
  }
  
  /**
   * returns the PID of the process that currently has a lock on the semaphore 
   * $id. returns NULL if $id is not locked
   * @param string $id the identifier of the semaphore
   * @return boolean
   */
  function semPid($id) {
    return file_exists($lockf = SRA_Util::semLockFile($id)) && ($pid = SRA_File::toString($lockf)*1) && SRA_Util::isProcessActive($pid) ? $pid : NULL;
  }
  
  /**
   * returns the current system load average
   * @param int $freq the frequency of the load average to return. 1, 5 or 15 
   * minutes. the default is 5 minutes
   * @return float
   */
  function getLoadAverage($freq=5) {
    $avg = NULL;
    $freq = $freq == 1 || $freq == 5 || $freq == 15 ? $freq : 5;
    $idx = $freq == 5 ? 2 : ($freq == 15 ? 3 : 1);
    if (($output = shell_exec(SRA_File::findInPath('uptime'))) && 
        preg_match('/average: ([0-9]+\.[0-9]+), ([0-9]+\.[0-9]+), ([0-9]+\.[0-9]+)/', $output, $m) && 
        isset($m[$idx]) && is_numeric($m[$idx])) {
      $avg = $m[$idx]*1;
    }
    return $avg;
  }
  
  /**
   * returns the current system uptime
   * @return string
   */
  function getUptime($freq=5) {
    $uptime = NULL;
    if (($output = shell_exec(SRA_File::findInPath('uptime'))) && strpos($output, 'up')) {
      $uptime = trim(substr($output, $start = strpos($output, 'up') + 3, strpos($output, ',') - $start));
    }
    return $uptime;
  }
  
  /**
   * returns the current system memory usage percentage represented as a 
   * percentage of the total memory available. the maximum value returned is 
   * 100% representing no free memory
   * @param boolean $swap set to true to return swap usage instead
   * @return float
   */
  function getMemoryUsage($swap=FALSE) {
    $usage = NULL;
    $s = $swap ? 'Swap:' : 'Mem:';
    if (($output = shell_exec(SRA_File::findInPath('free'))) && 
        preg_match('/' . $s . '\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/', $output, $m)) {
      $usage = number_format(($m[2]/$m[1])*100, 2);
    }
    return $usage;
  }
  
  /**
   * returns the total amount of memory available in $unit
   * @param boolean $swap set to true to return swap available instead
   * @return int
   */
  function getMemorySize($swap=FALSE) {
    $mem = NULL;
    $s = $swap ? 'Swap:' : 'Mem:';
    if (($output = shell_exec(SRA_File::findInPath('free'))) && 
        preg_match('/' . $s . '\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/', $output, $m)) {
      $mem = $m[1]*1;
    }
    return $mem;
  }
  
  /**
   * returns the total amount of memory used in kilobytes
   * @param boolean $swap set to true to return swap used instead
   * @return int
   */
  function getMemoryUsed($swap=FALSE) {
    $used = NULL;
    $s = $swap ? 'Swap:' : 'Mem:';
    if (($output = shell_exec(SRA_File::findInPath('free'))) && 
        preg_match('/' . $s . '\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/', $output, $m)) {
      $used = $m[2]*1;
    }
    return $used;
  }
  
  /**
   * returns the IP addresses currently assigned
   * @param boolean $skipPrivate whether or not to skip private IPs (default 
   * TRUE)
   * @param boolean $skipPublic whether or not to skip public IPs (default 
   * FALSE)
   * @param boolean $skipLoopback whether or not to skip the loopback address 
   * (127.0.0.1) (default TRUE)
   * @return string[]
   */
  function getIps($skipPrivate=TRUE, $skipPublic=FALSE, $skipLoopback=TRUE) {
    $ips = array();
    $s = $swap ? 'Swap:' : 'Mem:';
    if (($output = shell_exec(SRA_File::findInPath('ifconfig', '/sbin'))) && 
        preg_match_all('/inet addr:(\S+)/', $output, $m)) {
      foreach($m[1] as $ip) {
        $private = SRA_Util::isPrivateNetworkIp($ip);
        if (($ip != '127.0.0.1' || !$skipLoopback) && ($ip == '127.0.0.1' || !$skipPrivate || !$private) && ($ip == '127.0.0.1' || !$skipPublic || $private)) {
          $ips[] = $ip;
        }
      }
    }
    return $ips;
  }
  
  /**
   * returns the current disk space usage as a hash indexed by partition mount 
   * location
   * @param int $type the type of usage stat to return. 1=percentage used, 
   * 2=percentage remaining, 3=megabytes used, 4=megabytes remaining, 5=size 
   * (megabytes). Default is 1, percentage available
   * @return array
   */
  function getDiskSpaceUsage($type=1) {
    $usage = array();
    if ($output = shell_exec(SRA_File::findInPath('df') . ' -m')) {
      $lines = explode("\n", $output);
      for($i=1; $i<count($lines); $i++) {
        if (preg_match_all('/(\S+)/', $lines[$i], $m)) {
          if (count($m[1]) == 1 && isset($lines[$i + 1])) {
            $lines[$i + 1] = $lines[$i] . ' ' . $lines[$i + 1];
          }
          if (count($m[1]) < 5) continue;
          
          switch($type) {
            case 2:
              $val = 100 - ($m[1][4]*1);
              break;
            case 3:
              $val = $m[1][2]*1;
              break;
            case 4:
              $val = $m[1][3]*1;
              break;
            case 5:
              $val = $m[1][1]*1;
              break;
            default:
              $val = $m[1][4]*1;
          }
          $usage[$m[1][5]] = $val;
        }
      }
    }
    return $usage;
  }
  
  /**
   * shuffles (randomizes) an array while maintaining key associations
   * @param array $arr the array to shuffle
   * @return array
   */
  function & arrayShuffle($arr) {
    $shuffled = array();
    $randomized = array_rand($arr, count($arr)); 
    foreach($randomized as $key) { 
       $shuffled[$key] = $arr[$key]; 
    } 
    return $shuffled;
  }
  
}
// }}}

// {{{ codeToString()
/**
 * Used function used to forward to SRA_Util:: function (see method api for more
 * info)
 */
function codeToString($code) {
	return SRA_Util::codeToString($code);
}
// }}}


/**
 * utility class uses by SRA_Util::mxlookup
 */
class _mxlookup {
  var $dns_socket = NULL;
  var $QNAME = "";
  var $dns_packet= NULL;
  var $ANCOUNT = 0;
  var $cIx = 0;
  var $dns_repl_domain;
  var $arrMX = array();

  function _mxlookup($domain, $dns, $timeout)
  {
     $this->QNAME($domain);
     $this->pack_dns_packet();
     $dns_socket = fsockopen("udp://$dns", 53);

     fwrite($dns_socket,$this->dns_packet,strlen($this->dns_packet));
     @socket_set_timeout($dns_socket, $timeout);
     $this->dns_reply  = fread($dns_socket,1);
     $bytes = stream_get_meta_data($dns_socket);
     $this->dns_reply .= fread($dns_socket,$bytes['unread_bytes']);
     fclose($dns_socket);
     $this->cIx=6;
     $this->ANCOUNT   = $this->gord(2);
     $this->cIx+=4;
     $this->parse_data($this->dns_repl_domain);
     $this->cIx+=7;

     for($ic=1;$ic<=$this->ANCOUNT;$ic++)
     {
       $QTYPE = ord($this->gdi($this->cIx));
       if($QTYPE!==15){print("[MX Record not returned]"); die();}
       $this->cIx+=9;
       $mxPref = ord($this->gdi($this->cIx));
       $this->parse_data($curmx);
       $this->arrMX[] = array("MX_Pref" => $mxPref, "MX" => $curmx);
       $this->cIx+=3;
     }
  }

  function parse_data(&$retval)
  {
    $arName = array();
    $byte = ord($this->gdi($this->cIx));
    while($byte!==0)
    {
      if($byte==192) //compressed
      {
        $tmpIx = $this->cIx;
        $this->cIx = ord($this->gdi($cIx));
        $tmpName = $retval;
        $this->parse_data($tmpName);
        $retval=$retval.".".$tmpName;
        $this->cIx = $tmpIx+1;
        return;
      }
      $retval="";
      $bCount = $byte;
      for($b=0;$b<$bCount;$b++)
      {
        $retval .= $this->gdi($this->cIx);
      }
      $arName[]=$retval;
     $byte = ord($this->gdi($this->cIx));
   }
   $retval=join(".",$arName);
 }

 function gdi(&$cIx,$bytes=1)
 {
   $this->cIx++;
   return(substr($this->dns_reply, $this->cIx-1, $bytes));
 }

  function QNAME($domain)
  {
    $dot_pos = 0; $temp = "";
    while($dot_pos=strpos($domain,"."))
    {
      $temp   = substr($domain,0,$dot_pos);
      $domain = substr($domain,$dot_pos+1);
      $this->QNAME .= chr(strlen($temp)).$temp;
    }
    $this->QNAME .= chr(strlen($domain)).$domain.chr(0);
  }

  function gord($ln=1)
  {
    $reply="";
    for($i=0;$i<$ln;$i++){
     $reply.=ord(substr($this->dns_reply,$this->cIx,1));
     $this->cIx++;
     }

    return $reply;
  }

  function pack_dns_packet()
  {
    $this->dns_packet = chr(0).chr(1).
                        chr(1).chr(0).
                        chr(0).chr(1).
                        chr(0).chr(0).
                        chr(0).chr(0).
                        chr(0).chr(0).
                        $this->QNAME.
                        chr(0).chr(15).
                        chr(0).chr(1);
  }

}
?>
