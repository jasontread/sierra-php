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

// {{{ Includes
// }}}

// {{{ Constants

/**
 * the linux clock configuration
 * @type string
 */
define('SRA_TIME_ZONE_CLOCK_CONFIG', '/etc/sysconfig/clock');

/**
 * the identifier for the GMT time zone
 * @type string
 */
define('SRA_TIME_ZONE_GMT', 'GMT');

/**
 * the timezone file used by the  operating system
 * @type string
 */
define('SRA_TIME_ZONE_SYSTEM', '/etc/localtime');

/**
 * the identifier for the UTC time zone
 * @type string
 */
define('SRA_TIME_ZONE_UTC', 'UTC');

/**
 * the path to use (in addition to the standard path) when looking for the zdump 
 * utility used to determine timezone ranges
 * @type string
 */
define('SRA_TIME_ZONE_ZDUMP_PATH', '/sbin:/usr/sbin:/usr/local/sbin');

/**
 * the path to the zoneinfo directory
 * @type string
 */
define('SRA_TIME_ZONE_ZONEINFO_PATH', '/usr/share/zoneinfo');

/**
 * the prefix to use for time zone cache files
 * @type string
 */
define('SRA_TIME_ZONE_ZONE_CACHE_PREFIX', '.tz-');

/**
 * the path to the zone.tab file
 * @type string
 */
define('SRA_TIME_ZONE_ZONETAB_PATH', SRA_TIME_ZONE_ZONEINFO_PATH . '/zone.tab');
// }}}

// {{{ SRA_TimeZone
/**
 * The SRA_TimeZone class manages data and behavior pertaining to a time zone 
 * including interraction with SRA_GregorianDate
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_TimeZone {
  
  // {{{ attributes
  /**
   * time zone identifier (i.e. America/Boise)
   * @type string
   */
  var $_id;
  
  /**
   * the standard abbreviation for this time zone
   * @type string
   */
  var $_abbr;
  
  /**
   * the dst abbreviation for this time zone (if $_dst is TRUE)
   * @type string
   */
  var $_abbrDst;
  
  /**
   * an optional time zone comment as specified in zone.tab
   * @type string
   */
  var $_comment;
  
  /**
   * the ISO 3166 2-character country code that this time zone pertains to
   * @type string
   */
  var $_country;
  
  /**
   * whether or not this time zone uses daylight savings
   * @type boolean
   */
  var $_dst = FALSE;
  
  /**
   * time zone latitude in ISO 6709 sign-degrees-minutes-seconds format, either 
   * +-DDMM or +-DDMMSS
   * @type string
   */
  var $_latitude;
  
  /**
   * time zone longitude in ISO 6709 sign-degrees-minutes-seconds format, either 
   * +-DDMM or +-DDMMSS
   * @type string
   */
  var $_longitude;
  
  /**
   * an array of hashes identifying the ranges when this time zone observes and 
   * does not observe daylight savings time. this hash will be ordered by start 
   * time and contains the following keys:
   *   start : YmdHis representing the start time for this range
   *   end   : YmdHis representing the end time for this range
   *   dst   : boolean - whether or not this is a dst range
   *   offset: the GMT offset in seconds for this range
   * @type array
   */
  var $_ranges;
  
  /**
   * the absolute path to the zone file for this timezone (located in 
   * SRA_TIME_ZONE_ZONEINFO_PATH)
   * @type string
   */
  var $_zoneFile;
  // }}} 
  
  
  // {{{ SRA_TimeZone
  /**
   * this constructor should never be called outside of this class. Instead, 
   * use the singleton getTimeZone method
   * @param array $attrs optional array of initization attributes
   * @access private
   */
  function SRA_TimeZone($attrs=NULL) {
    if ($attrs && is_array($attrs)) {
      if (isset($attrs['id'])) $this->_id = $attrs['id'];
      if (isset($attrs['abbr'])) $this->_abbr = $attrs['abbr'];
      if (isset($attrs['abbrDst'])) $this->_abbrDst = $attrs['abbrDst'];
      if (isset($attrs['comment'])) $this->_comment = $attrs['comment'];
      if (isset($attrs['country'])) $this->_country = $attrs['country'];
      if (isset($attrs['dst'])) $this->_dst = $attrs['dst'];
      if (isset($attrs['latitude'])) $this->_latitude = $attrs['latitude'];
      if (isset($attrs['longitude'])) $this->_longitude = $attrs['longitude'];
      if (isset($attrs['ranges'])) $this->_ranges = $attrs['ranges'];
      if (isset($attrs['zoneFile'])) $this->_zoneFile = $attrs['zoneFile'];
    }
  }
  // }}}

  // {{{ copy
  /**
   * this method returns a copy of this SRA_TimeZone object
   * @access public
   * @return SRA_TimeZone
   */
  function &copy() {
    $tz = new SRA_TimeZone();
    $tz->_id = $this->_id;
    $tz->_abbr = $this->_abbr;
    $tz->_abbrDst = $this->_abbrDst;
    $tz->_comment = $this->_comment;
    $tz->_country = $this->_country;
    $tz->_dst = $this->_dst;
    $tz->_latitude = $this->_latitude;
    $tz->_longitude = $this->_longitude;
    $tz->_zoneFile = $this->_zoneFile;
    return $tz;
  }
  // }}}
  
  // {{{ equals
  /**
   * returns TRUE if this  SRA_TimeZone is the same as $tz
   * @param mixed $tz the time zone to compare with
   * @access public
   * @return boolean
   */
  function equals(&$tz) {
    return SRA_TimeZone::isValid($tz) && $this->getId() == $tz->getId();
  }
  // }}}

  // {{{ getAbbr
  /**
   * returns the abbreviation for this timezone and $date
   * @param SRA_GregorianDate $data the date to return the timezone for. it not 
   * specified, the non-dst (if applicable) abbreviation will be returned
   * @access public
   * @return string
   */
  function getAbbr(&$date) {
    return $this->_dst && SRA_GregorianDate::isValid($date) && $this->isDaylightSavings($date) ? $this->_abbrDst : $this->_abbr;
  }
  // }}}
  
  // {{{ getComment
  /**
   * returns the value of the _comment attribute
   * @access public
   * @return string
   */
  function getComment() {
    return $this->_comment;
  }
  // }}}
  
  // {{{ getCountry
  /**
   * returns the value of the _country attribute
   * @access public
   * @return string
   */
  function getCountry() {
    return $this->_country;
  }
  // }}}
  
  // {{{ getDaylightSavingsEnd
  /**
   * returns an SRA_GregorianDate object representing the exact date/time when 
   * daylight savings ends for this time zone (when applicable, otherwise 
   * returns NULL)
   * @param int $year the year to evaluate. if not specified, the current year 
   * will be used
   * @access public
   * @return SRA_GregorianDate
   */
  function getDaylightSavingsEnd($year=NULL) {
    $year = $year ? $year : date('Y');
    
    if ($this->_dst && ($ranges =& $this->getRanges())) {
      foreach(array_keys($ranges) as $key) {
        if ($ranges[$key]['dst'] && $year == substr($ranges[$key]['end'], 0, 4)) {
          return new SRA_GregorianDate($ranges[$key]['end']);
        }
      }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getDaylightSavingsStart
  /**
   * returns an SRA_GregorianDate object representing the exact date/time when 
   * daylight savings begins for this time zone (when applicable, otherwise 
   * returns NULL)
   * @param int $year the year to evaluate. if not specified, the current year 
   * will be used
   * @access public
   * @return SRA_GregorianDate
   */
  function getDaylightSavingsStart($year=NULL) {
    $year = $year ? $year : date('Y');
    
    if ($this->_dst && ($ranges =& $this->getRanges())) {
      foreach(array_keys($ranges) as $key) {
        if ($ranges[$key]['dst'] && $year == substr($ranges[$key]['start'], 0, 4)) {
          return new SRA_GregorianDate($ranges[$key]['start']);
        }
      }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getGmtHourOffset
  /**
   * returns GMT offset (above) in hours (including decimals)
   * @param mixed $date SRA_GregorianDate to consider for daylight savings 
   * (optional)
   * @access public
   * @return int
   */
  function getGmtHourOffset(&$date) {
    return $this->getGmtOffset($date)/60;
  }
  // }}}

  // {{{ getGmtOffset
  /**
   * returns the difference in minutes between the this timezone and the GMT
   * timezone for $date. this value may be positive or negative
   * @param mixed $date SRA_GregorianDate to return the offset for. if not 
   * specified, the current date will be assumed
   * @access public
   * @return int
   */
  function getGmtOffset(&$date=NULL) {
    return $this->getSecGmtOffset($date)/60;
  }
  // }}}

  // {{{ getSecGmtOffset
  /**
   * returns the difference in seconds between the this timezone and the GMT
   * timezone for $date. this value may be positive or negative
   * @param mixed $date SRA_GregorianDate to return the offset for. if not 
   * specified, the current date will be assumed
   * @access public
   * @return int
   */
  function getSecGmtOffset(&$date=NULL) {
    if ($range =& $this->getRange($date)) {
      return $range['offset'];
    }
  }
  // }}}
  
  // {{{ getGmtOffsetForYear
  /**
   * returns the gmt offset to use for $year
   * @param int $year the year to return the offset for. if not specified, the 
   * current year will be assumed
   * @param boolean $dst whether or not to return the $dst offset for that year
   * @access public
   * @return hash
   */
  function getGmtOffsetForYear($year=NULL, $dst=FALSE) {
    if (!$year) { $year = date('Y'); }
    
    if ($ranges =& $this->getRanges()) {
      foreach(array_keys($ranges) as $key) {
        if (((!$dst && !$ranges[$key]['dst']) || ($dst && $ranges[$key]['dst'])) && $year == substr($ranges[$key]['end'], 0, 4)) {
          return $ranges[$key]['offset']/60;
        }
      }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getGmtOffsetString
  /**
   * this method returns the GMT offset for this time zone as a string using the 
   * format +/-HHMM (example: -0200 or +0700). if the $date parameter is 
   * provided, the offset will take into account that date and apply the 
   * necessary daylight savings differential (when that date falls within a 
   * daylight savings period). otherwise, regardless of whether or not the time 
   * zone uses daylight savings, the standard offset (non-daylight savings 
   * adjusted) will be returned
   * @param mixed $date SRA_GregorianDate to consider for 
   * daylight savings (optional)
   * @access public
   * @return string
   */
  function getGmtOffsetString(&$date) {
    $offset=$this->getGmtOffset($date);
    return ($offset > 0 ? '+' : '-') . sprintf('%02d', floor(abs($offset)/60)) . sprintf('%02d', abs($offset)%60);
  }
  // }}}

  // {{{ getGmtOffsetStringISO8601
  /**
   * this method returns the GMT offset for this time zone as an ISO 8601 
   * compliant string using the format +/-HH:MM (example: -02:00 or +07:00). 
   * this is basically the same as 'getOffsetString' but it has a colon dividing 
   * hours and minutes. if the $date parameter is provided, the offset will take 
   * into account that date and apply the necessary daylight savings 
   * differential (when that date falls within a daylight savings period). 
   * otherwise, regardless of whether or not the time zone uses daylight 
   * savings, the standard offset (non-daylight savings adjusted) will be 
   * returned
   * @param mixed $date SRA_GregorianDate to consider for 
   * daylight savings (optional)
   * @access public
   * @return string
   */
  function getGmtOffsetStringISO8601(&$date) {
    $offset=$this->getGmtOffset($date);
    return ($offset > 0 ? '+' : '-') . sprintf('%02d', floor(abs($offset)/60)) . ':' . sprintf('%02d', abs($offset)%60);
  }
  // }}}
  
  // {{{ getId
  /**
   * returns the value of the _id attribute
   * @access public
   * @return string
   */
  function getId() {
    return $this->_id;
  }
  // }}}
  
  // {{{ getLatitude
  /**
   * returns the value of the _latitude attribute
   * @access public
   * @return string
   */
  function getLatitude() {
    return $this->_latitude;
  }
  // }}}
  
  // {{{ getLongitude
  /**
   * returns the value of the _longitude attribute
   * @access public
   * @return string
   */
  function getLongitude() {
    return $this->_longitude;
  }
  // }}}
  
  // {{{ getRange
  /**
   * returns the range associated with $date. this will be a hash with the 
   * following keys:
   *   start : YmdHis representing the start time for this range
   *   end   : YmdHis representing the end time for this range
   *   dst   : boolean - whether or not this is a dst range
   *   offset: the GMT offset in seconds for this range
   * @param SRA_GregorianDate $date the date to return the range for. if not 
   * specified, the current time will be assumed
   * @access public
   * @return hash
   */
  function getRange(&$date) {
    static $_cachedRanged = array();
    
    if (!$date) { $date = new SRA_GregorianDate(); }
    $compare = $date->format('YmdHis');
    if (isset($_cachedRanged[$compare])) return $_cachedRanged[$compare] ? $_cachedRanged[$compare] : NULL;
    
    if ($ranges =& $this->getRanges()) {
      foreach(array_keys($ranges) as $key) {
        if ($next) { return $ranges[$key]; }
        
        if ($compare >= $ranges[$key]['start'] && $compare <= $ranges[$key]['end']) {
          if ($ranges[$key]['dst'] && $date->format('YmdH') == substr($ranges[$key]['end'], 0, 10) && $date->_dstOverlap) {
            $next = TRUE;
          }
          else {
            $_cachedRanged[$compare] = $ranges[$key];
            return $ranges[$key];
          }
        }
      }
    }
    $_cachedRanged[$compare] = FALSE;
    return NULL;
  }
  // }}}
  
  // {{{ getRanges
  /**
   * returns the value of the _ranges attribute. the first time this method is 
   * invoked, those values will be unserialized from the ranges cache file
   * @access public
   * @return array
   */
  function &getRanges() {
    if (!isset($this->_ranges)) {
      $cfile = $this->_getRangesCacheFile();
      if (file_exists($cfile)) {
        include($cfile);
      }
      if (!is_array($this->_ranges)) {
        $msg = 'SRA_TimeZone::getRanges: Error - Unable to get ranges from cache file "' . $cfile . '"';
        SRA_Error::logError($msg, __FILE__, __LINE__);
      }
    }
    return $this->_ranges;
  }
  // }}}
  
  // {{{ getZoneFile
  /**
   * returns the value of the _zoneFile attribute
   * @access public
   * @return string
   */
  function getZoneFile() {
    return $this->_zoneFile;
  }
  // }}}

  // {{{ isDaylightSavings
  /**
   * returns TRUE if this time zone uses daylight savings. if the $date 
   * parameter is provided, the method will take into account that date and 
   * return TRUE ONLY if the time zone uses daylight savings AND that date falls 
   * within a daylight savings period
   * @param mixed $date SRA_GregorianDate to consider for 
   * daylight savings (optional)
   * @access public
   * @return  boolean
   */
  function isDaylightSavings(&$date) {
    return $this->_dst && SRA_GregorianDate::isValid($date) ? 
              $date->compare($start = $this->getDaylightSavingsStart($date->getYear())) >= 0 && $date->compare($end = $this->getDaylightSavingsEnd($date->getYear())) <= 0 && (!$date->_dstOverlap || $date->format('YmdH') != $end->format('YmdH')) : 
              $this->_dst;
  }
  // }}}
  
  // {{{ _getRangesCacheFile
  /**
   * returns the path of the file used to cache the ranges for this time zone
   * @param string $id allows this method to be invoked statically
   * @access public
   * @return string
   */
  function _getRangesCacheFile($id=NULL) {
    return SRA_Controller::getSysTmpDir() . '/' . SRA_TIME_ZONE_ZONE_CACHE_PREFIX . 'ranges-' . str_replace('/', '_', $id ? $id : $this->_id) . '.' . SRA_SYS_PHP_EXTENSION;
  }
  // }}}
    
    
  // static methods
  // {{{ getAllTimeZones
  /**
   * returns an array of all of the names of the time zones currently available 
   * in the operating system
   * @param mixed $country a single 2-character ISO 3166 country code, or an 
   * array of country codes to use to filter the timezones returned. if  
   * specified, only the time zones for that country will be returned. set this 
   * parameter to 1 to use SRA_Controller::getAppDefaultCountry
   * @param boolean $hash whether or not to return the options array as a hash
   * instead of an array where the hash key is also the timezone identifier
   * @param boolean $clearCache set to TRUE for force clearing of the zone cache
   * @access public
   * @return string[]
   */
  function &getAllTimeZones($country=NULL, $hash=FALSE, $clearCache=FALSE) {
    static $cachedTimeZones = array();
    
    if ($country === 1) $country = SRA_Controller::getAppDefaultCountry();
    $cacheKey = ($country ? $country : 'all') . '_' . $hash;
    if (array_key_exists($cacheKey, $cachedTimeZones) && !$clearCache) {
      return $cachedTimeZones[$cacheKey];
    }
    
    $ztCache = SRA_Controller::getSysTmpDir() . '/' . SRA_TIME_ZONE_ZONE_CACHE_PREFIX . 'zones';
    if ($clearCache || !file_exists($ztCache) || SRA_File::compareMTimes($ztCache, SRA_TIME_ZONE_ZONETAB_PATH) != 1) {
      
      $countries = array();
      $tzcache = array();
      foreach(file(SRA_TIME_ZONE_ZONETAB_PATH) as $line) {
        if (preg_match('/^([A-Z]{2})[\s]+([\+\-][0-9]+)([\+\-][0-9]+)[\s]+([\S]+)([\s]+[\S][\s\S]*)?$/', $line, $m) && file_exists($tzfile = SRA_TIME_ZONE_ZONEINFO_PATH . '/' . $m[4])) {
          $tzcache[$m[4]] = $m[1];
          $countries[$m[1]] = TRUE;
        }
      }
      
      // add gmt, utc and country specific time zones
      $addlTimeZones = array(SRA_TIME_ZONE_ZONEINFO_PATH . '/' . SRA_TIME_ZONE_GMT, SRA_TIME_ZONE_ZONEINFO_PATH . '/' . SRA_TIME_ZONE_UTC);
      foreach(array_keys($countries) as $temp) {
        if (is_dir($cdir = SRA_TIME_ZONE_ZONEINFO_PATH . '/' . $temp)) {
          foreach(SRA_File::getFileList($cdir) as $tzfile) {
            $addlTimeZones[] = $tzfile;
          }
        }
      }
      foreach($addlTimeZones as $tzfile) {
        if (!isset($tzcache[$tzfile])) {
          $tzcache[str_replace(SRA_TIME_ZONE_ZONEINFO_PATH . '/', '', $tzfile)] = dirname($tzfile) == SRA_TIME_ZONE_ZONEINFO_PATH ? NULL : basename(dirname($tzfile));
        }
      }
      
      ksort($tzcache);
      
      // cache
      SRA_File::write($ztCache, serialize($tzcache));
      SRA_File::chmod($ztCache, 0666);
    }
    
    if (!$tzcache && file_exists($ztCache)) {
      $tzcache = unserialize(SRA_File::toString($ztCache));
    }
    
    $timezones = $country ? array() : array_keys($tzcache);
    
    // filter for a specific country
    if ($country) {
      $country = strtoupper($country);
      foreach(array_keys($tzcache) as $zone) {
        if ($tzcache[$zone] === NULL || $country == $tzcache[$zone]) {
          $timezones[] = $zone;
        }
      }
    }
    
    if ($hash) {
      foreach(array_keys($timezones) as $key) {
        $timezones[$timezones[$key]] = $timezones[$key];
        unset($timezones[$key]);
      }
    }
    
    $cachedTimeZones[$cacheKey] = $timezones;
    
    return $timezones;
  }
  // }}}
  
  // {{{ getCountries
  /**
   * returns a hash of countries for which timezone configurations exist. the 
   * key in this hash will be the country code and the value will be the country 
   * name
   * @param boolean $clearCache set to TRUE for force clearing of the cache
   * @access public
   * @return hash
   */
  function getCountries($clearCache=FALSE) {
    static $tzCountries = array();
    
    if (!$tzCountries || $clearCache) {
      $cacheFile = SRA_Controller::getSysTmpDir() . '/' . SRA_TIME_ZONE_ZONE_CACHE_PREFIX . 'countries';
      if ($clearCache || !file_exists($cacheFile) || SRA_File::compareMTimes($cacheFile, SRA_TIME_ZONE_ZONETAB_PATH) != 1) {
        $resources =& SRA_ResourceBundle::getBundle('iso3166');
        
        foreach(file(SRA_TIME_ZONE_ZONETAB_PATH) as $line) {
          if (preg_match('/^([A-Z]{2})?/', $line, $m)) {
            if (trim($m[1])) $countries[$m[1]] = TRUE;
          }
        }
        
        foreach(array_keys($countries) as $country) {
          $tzCountries[$country] = $resources->getString($country);
        }
        asort($tzCountries);
        $tzCountries =& SRA_Util::arrayMoveToTop($tzCountries, strtoupper(SRA_Controller::getAppDefaultCountry()));
        SRA_File::write($cacheFile, serialize($tzCountries));
        SRA_File::chmod($cacheFile, 0666);
      }
      if (!$tzCountries && file_exists($cacheFile)) {
        $tzCountries = unserialize(SRA_File::toString($cacheFile));
      }
    }
    
    return $tzCountries;
  }
  // }}}
  
  // {{{ getSystemTimeZone
  /**
   * returns a reference to a object representing the timezone currently in use 
   * by the operating system (/etc/localtime)
   * @access public
   * @return SRA_TimeZone
   */
  function &getSystemTimeZone() {
    // if /etc/localtime is a link, just see what it is linked to
    if (is_link(SRA_TIME_ZONE_SYSTEM) && ($link = readlink(SRA_TIME_ZONE_SYSTEM)) && SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone(str_replace(SRA_TIME_ZONE_ZONEINFO_PATH . '/', '', $link)))) {
      return $tz;
    }
    // otherwise check in /etc/sysconfig/clock
    else if (is_array($clock = SRA_File::propertiesFileToArray(SRA_TIME_ZONE_CLOCK_CONFIG, 0)) && (isset($clock['ZONE']) || isset($clock['zone'])) && SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone(SRA_Util::stripQuotes(isset($clock['ZONE']) ? $clock['ZONE'] : $clock['zone'])))) {
      return $tz;
    }
    // last resort: compare filesizes of /etc/localtime and all of the timezone files in /usr/share/zoneinfo
    else {
      $size = filesize(SRA_TIME_ZONE_SYSTEM);
      foreach(file(SRA_TIME_ZONE_ZONETAB_PATH) as $line) {
        if (preg_match('/^([A-Z]{2})[\s]+([\+\-][0-9]+)([\+\-][0-9]+)[\s]+([\S]+)([\s]+[\S][\s\S]*)?$/', $line, $m) && file_exists($tzfile = SRA_TIME_ZONE_ZONEINFO_PATH . '/' . $m[4])) {
          if (filesize($tzfile) == $size) {
            return SRA_TimeZone::getTimeZone($m[4]);
          }
        }
      }
    }
  }
  // }}}
  
  // {{{ getTimeZone
  /**
   * static singleton method used to obtain a reference to a time zone object. 
   * if $id is not a valid time zone identifier, NULL will be returned. if $id 
   * is not specified, the app time zone will be returned
   * @param string $id the unique time zone name identifying which one should 
   * be returned. if not specified, the application time zone will be returned
   * @param boolean $clearCache whether or not to clear the time zone cache if 
   * it exists
   * @access public
   * @return SRA_TimeZone
   */
  function &getTimeZone($id=NULL, $clearCache=FALSE) {
    static $_cachedTimeZones = array();
    
    if (!$id) {
      $conf =& SRA_Controller::getAppConf();
      $id = isset($conf['time-zone']) ? $conf['time-zone'] : NULL;
    }
    if (!$id) {
      $conf =& SRA_Controller::getSysConf();
      $id = isset($conf['time-zone']) ? $conf['time-zone'] : NULL;
    }
    if (!$id) { return SRA_TimeZone::getSystemTimeZone(); }
    
    $tz = NULL;
    if (file_exists($tzfile = SRA_TIME_ZONE_ZONEINFO_PATH . '/' . $id)) {
      if (!$clearCache && isset($_cachedTimeZones[$id])) {
        $tz =& $_cachedTimeZones[$id];
      }
      else {
        $tzCache = SRA_Controller::getSysTmpDir() . '/' . SRA_TIME_ZONE_ZONE_CACHE_PREFIX . str_replace('/', '_', $id);
        if ($clearCache || !file_exists($tzCache) || SRA_File::compareMTimes($tzCache, $tzfile) != 1) {
          $tzattrs = array('id' => $id, 'zoneFile' => $tzfile);
          if (preg_match('/^([A-Z]{2})[\s]+([\+\-][0-9]+)([\+\-][0-9]+)[\s]+([\S]+)([\s]+[\S][\s\S]*)?$/', shell_exec(SRA_File::findInPath('grep') . " '" . $id . "' " . SRA_TIME_ZONE_ZONETAB_PATH), $m)) {
            $tzattrs['country'] = $m[1];
            $tzattrs['latitude'] = $m[2];
            $tzattrs['longitude'] = $m[3];
            $tzattrs['comment'] = isset($m[5]) ? trim($m[5]) : NULL;
          }
          else {
            $tzattrs['country'] = dirname($tzfile) == SRA_TIME_ZONE_ZONEINFO_PATH ? NULL : basename(dirname($tzfile));
          }
          
          // generate and cache time zone ranges
          if (exec(SRA_File::findInPath('zdump', SRA_TIME_ZONE_ZDUMP_PATH) . ' -v ' . $tzfile, $tmp)) {
            $ranges = array();
            $idx = -1;
            $monthCache = array();
            foreach($tmp as $range) {
              if (preg_match('/=[\s]*[\S]{3}[\s]+([\S]{3})[\s]+([0-9]+)[\s]+([0-9]{2}):([0-9]{2}):([0-9]{2})[\s]+([0-9]{4})[\s]+([\S]+)[\s]+isdst[\s]*=[\s]*([01])[\s]+gmtoff[\s]*=[\s]*(\-?[0-9]+)/i', $range, $m)) {
                if (!isset($monthCache[$m[1]])) $monthCache[$m[1]] = SRA_GregorianDate::getMonthFromStr($m[1]);
                
                $stamp = ($m[6] . (strlen($monthCache[$m[1]]) == 1 ? '0' : '') . $monthCache[$m[1]] . (strlen($m[2]) == 1 ? '0' : '') . $m[2] . $m[3] . $m[4] . $m[5])*1;
                $dstRange = $m[8] ? TRUE : FALSE;
                $offset = $m[9]*1;
                if ($dstRange && !$dst) $dst = $dstRange;
                if ($dstRange) $abbrDst = $m[7];
                if (!$dstRange) $abbr = $m[7];
                if ($idx == -1 || $dstRange != $ranges[$idx]['dst'] || $offset != $ranges[$idx]['offset']) {
                  $ranges[++$idx] = array('start' => $stamp, 'end' => $stamp, 'dst' => $dstRange, 'offset' => $offset);
                }
                else {
                  $ranges[$idx]['end'] = $stamp;
                }
              }
            }
            // cache
            $tzattrs['dst'] = $dst;
            $tzattrs['abbr'] = $abbr;
            $tzattrs['abbrDst'] = $abbrDst ? $abbrDst : $abbr;
            
            $rangeCacheFile = SRA_TimeZone::_getRangesCacheFile($id);
            SRA_File::write($rangeCacheFile, $buffer = '<?php ' . SRA_Util::bufferArray($ranges, 'this->_ranges') . ' ?>');
            SRA_File::chmod($rangeCacheFile, 0666);
          }
          else {
            $msg = 'SRA_TimeZone::getTimeZone: Error - Unable to zdump timezone ' . $id;
            SRA_Error::logError($msg, __FILE__, __LINE__);
          }
          
          // cache time zone
          SRA_File::write($tzCache, serialize($tzattrs));
        }
        $tz = new SRA_TimeZone($tzattrs ? $tzattrs : unserialize(SRA_File::toString($tzCache)));
        $_cachedTimeZones[$id] =& $tz;
      }
    }
    
    return $tz;
  }
  // }}}
  
  // {{{ getTimeZoneByOffset
  /**
   * returns the timezone that utilizes the GMT $offset specified
   * @param int $offset the offset to look for
   * @param boolean $dst whether or not this is a dst offset (default is FALSE)
   * @param int $year the year to consider (if not specified, the current year 
   * will be assumed)
   * @access public
   * @return SRA_TimeZone
   */
  function &getTimeZoneByOffset($offset, $dst=FALSE, $year=NULL) {
    static $_offsetTimeZones;
    
    $key = $offset . ($dst ? '_dst' : '');
    if (!$_offsetTimeZones) {
      $_offsetTimeZones = array();
      foreach(array_keys($allTimeZones =& SRA_TimeZone::getAllTimeZones()) as $i) {
        if (($ofs = $allTimeZones[$i]->getGmtOffsetForYear($year) . '') && !isset($_offsetTimeZones[$ofs])) { $_offsetTimeZones[$ofs] =& $allTimeZones[$i]; }
        if (($ofs = $allTimeZones[$i]->getGmtOffsetForYear($year, TRUE)) && !isset($_offsetTimeZones[$ofs])) { $_offsetTimeZones[$ofs . '_dst'] =& $allTimeZones[$i]; }
      }
    }
    return isset($_offsetTimeZones[$key]) ? $_offsetTimeZones[$key] : ($nl = NULL);
  }
  // }}}
  
  // {{{ getTzEnv
  /**
   * returns the a reference to an SRA_TimeZone instance representing the 
   * current timezone environment setting
   * @param boolean $id when TRUE the id of the timezone will be returned 
   * instead of a reference to the SRA_TimeZone instance
   * @access public
   * @return SRA_TimeZone
   */
  function &getTzEnv($id=FALSE) {
    if (!getenv('TZ') || !SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone(getenv('TZ')))) {
      $tz =& SRA_TimeZone::getSystemTimeZone();
    }
    return $id ? $tz->getId() : $tz;
  }
  // }}}
	
  // {{{ isValid
  /**
   * static method that returns true if $object is a SRA_TimeZone instance
   * @param object $object the object to evaluate
   * @access public
   * @return boolean
   */
  function isValid( &$object ) {
    return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_timezone');
  }
  // }}}
  
  // {{{ setSystemTimeZone
  /**
   * sets the system timezone to $tz. this method can ONLY be invoked by the 
   * root user since root permissions are required in order to make this change
   * returns TRUE on success, FALSE otherwise
   * @param mixed $tz the new timezone to set. either the name or an actual 
   * SRA_TimeZone reference
   * @access public
   * @return boolean
   */
  function setSystemTimeZone(&$tz) {
    $ret = FALSE;
    
    if (is_string($tz)) { $tz =& SRA_TimeZone::getTimeZone($tz); }
    
    if (SRA_TimeZone::isValid($tz) && is_writable(dirname(SRA_TIME_ZONE_CLOCK_CONFIG))) {
      SRA_File::unlink(SRA_TIME_ZONE_SYSTEM);
      SRA_File::symlink($tz->getZoneFile(), SRA_TIME_ZONE_SYSTEM);
      exec(SRA_File::findInPath('hwclock', SRA_TIME_ZONE_ZDUMP_PATH) . ' --systohc');
      $clock = SRA_File::propertiesFileToArray(SRA_TIME_ZONE_CLOCK_CONFIG, 0);
      $clock[isset($clock['ZONE']) ? 'ZONE' : 'zone'] = '"' . $tz->getId() . '"';
      SRA_File::propertiesArrayToFile(SRA_TIME_ZONE_CLOCK_CONFIG, $clock);
      $ret = TRUE;
    }
    
    return $ret;
  }
  // }}}
  
  // {{{ setTzEnvVar
  /**
   * updates the timezone environment variable using the $tz specified. returns 
   * TRUE if the variable was changed, false otherwise
   * @param mixed $tz the new timezone to set. either the name or an actual 
   * SRA_TimeZone reference
   * @access public
   * @return boolean
   */
  function setTzEnvVar(&$tz) {
    if (is_string($tz)) { $tz =& SRA_TimeZone::getTimeZone($tz); }
    if (SRA_TimeZone::isValid($tz)) {
      if (function_exists('date_default_timezone_set')) date_default_timezone_set($tz->getId());
      return putenv('TZ=' . $tz->getId());
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ validateCode
  /**
   * validates a time zone code
   * @param string $code the time zone code to validate
   * @access public
   * @return boolean
   */
  function validateCode($code) {
    return SRA_TimeZone::isValid(SRA_TimeZone::getTimeZone($code)) ? TRUE : FALSE;
  }
  // }}}

}
// }}}

?>
