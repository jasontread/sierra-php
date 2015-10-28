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
require_once('util/SRA_TimeZone.php');
// }}}

// {{{ Constants
/**
 * the format string for an IS0 8601 date string 
 * (example: 2004-02-12T15:19:21+00:00)
 * @type string
 */
define('SRA_GREGORIAN_DATE_FORMAT_ISO8601', 'Y-m-dTH:i:sP');

/**
 * the format string for an RFC 2822 date string 
 * (example: Thu, 21 Dec 2000 16:01:07 +0200)
 * @type string
 */
define('SRA_GREGORIAN_DATE_FORMAT_RFC2822', 'D, j M Y H:i:s O');

/**
 * the number of hours to limit relative time usage for in the "format" method 
 * when the format string is "RT"
 * @type int
 */
define('SRA_GREGORIAN_DATE_RELATIVE_TIME_CAP', 6);

/**
 * used by the jump method to identify a jump unit of 1 day
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_DAY', 86400);

/**
 * used by the jump method to identify a jump unit of 1 minute
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_MINUTE', 60);

/**
 * used by the jump method to identify a jump unit of 1 hour
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_HOUR', 3600);

/**
 * used by the jump method to identify a jump unit of 1 second
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_SECOND', 1);

/**
 * used by the jump method to identify a jump unit of 1 week
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_WEEK', 604800);

/**
 * used by the jump method to identify a jump unit of 1 month
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_MONTH', -1);

/**
 * used by the jump method to identify a jump unit of 1 year
 * @type int
 */
define('SRA_GREGORIAN_DATE_UNIT_YEAR', -2);
// }}}

// {{{ SRA_GregorianDate
/**
 * this class is used to manage gregorian based dates and times without the use 
 * of the PHP date and time functions (allowing it to support dates prior to the 
 * Unix Epoch (January 1 1970 00:00:00 GMT)). it may be used to represent both 
 * a date and time or just a date (time is irrelevant). this object can be 
 * stored in a database as an 8 character string (when 'dateOnly' is TRUE) or a
 * 14 character string (when 'dateOnly' is FALSE) - for more info, see the 
 * 'encode' method below
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_GregorianDate {
  // public attributes
  /**
   * an alternate default date format for 'toString'. if not specified, the 
   * default app-config (or sierra-config) date/date-only format will be used
   * @type string
   */
  var $toStringFormat;
  
  
  // private attributes
  /**
   * whether or not this object represents just a date (time is irrelevant)
   * @type boolean
   */
  var $_dateOnly = TRUE;
  
  /**
   * the date month day (1-31)
   * @type int
   */
  var $_day;
  
  /**
   * this attribute defines whether or not this date/time occurs during the dst 
   * rollback overlap period (i.e. the second 1:00 hour on 11/2/2008)
   * @type boolean
   */
  var $_dstOverlap;
  
  /**
   * the date hour (i.e. 0-23)
   * @type int
   */
  var $_hour;
  
  /**
   * the date minute (i.e. 0-59)
   * @type int
   */
  var $_minute;
  
  /**
   * the date month (1-12)
   * @type int
   */
  var $_month;
  
  /**
   * the date second (i.e. 0-59)
   * @type int
   */
  var $_second;
  
  /**
   * the time zone used by this object (applicable only when time is relevant - 
   * 'dateOnly' is FALSE)
   * @type SRA_TimeZone
   */
  var $_tz;
  
  /**
   * the date year (i.e. 2001)
   * @type int
   */
  var $_year;
  
  
  // {{{ SRA_GregorianDate
  /**
   * instantiates a new gregorian date object based on the parameters specified. 
   * if NO parameters are specified, the object will be created for the current 
   * system date/time and app (or system) time zone. if ONLY $year, $month and 
   * $day parameters are specified, it will be assumed that the time is not 
   * relevant ('dateOnly' will be set to TRUE)
   * @param mixed $year the year (YYYY or YY - if the latter format YY is used, 
   * 69-99 will be assumed to be 1969-1999 and 00-68 will be assumed to be 
   * 2000-2068), OR the full gregorian encoded date string (as retrieved from 
   * 'encode' - YYYYMMDDHHMMSS (HHMMSS are optional)). when this parameter is an 
   * encoded date string (strlen == 8 or 14), the other parameters will be 
   * ignored OR textual date/time description where this description adheres to 
   * the formats described for calendar date items here: 
   * http://www.gnu.org/software/tar/manual/html_node/tar_111.html#SEC111 
   * and for time of day items here: 
   * http://www.gnu.org/software/tar/manual/html_node/tar_112.html#SEC112
   * and for time zone items here:
   * http://www.gnu.org/software/tar/manual/html_node/tar_113.html#SEC113
   * @param mixed $month the month (1-12) OR the time zone for this new date
   * @param int $day the day (1-31)
   * @param int $hour the hour (0-23)
   * @param int $minute the minute (0-59)
   * @param int $second the second (0-59)
   * @param SRA_TimeZone $tz optional time zone to use for this date object. 
   * applies only when 'dateOnly' is FALSE. if not specified, the current app 
   * (or system time zone will be used)
   * @access public
   */
  function SRA_GregorianDate($year=NULL, $month=NULL, $day=NULL, $hour=NULL, $minute=NULL, $second=NULL, $tz=NULL) {
    $this->_init = TRUE;
    
    // time zone parameter was passed in $month place
    if ($month && SRA_TimeZone::isValid($month)) { $tz = $month; }
    
    // $year is a unix timestamp
    if (is_numeric($year) && (!(strlen($year) == 2 || strlen($year) == 4 || strlen($year) == 14 || strlen($year) == 8) || (!SRA_Util::beginsWith($year, '19') && !SRA_Util::beginsWith($year, '20')))) {
      $timestamp = $year;
      $year = date('Y', $timestamp);
      $month = date('n', $timestamp);
      $day = date('j', $timestamp);
      $hour = date('G', $timestamp);
      $minute = date('i', $timestamp);
      $second = date('s', $timestamp);
    }
    
    // convert encoded date string parameter
    if (isset($year) && is_numeric($year) && (strlen($year) == 14 || strlen($year) == 8)) {
      $month = substr($year, 4, 2);
      $day = substr($year, 6, 2);
      if (strlen($year) == 14) {
        $hour = substr($year, 8, 2);
        $minute = substr($year, 10, 2);
        $second = substr($year, 12, 2);
      }
      $year = substr($year, 0, 4);
    }
    // convert date/time string
    else if (isset($year) && !is_numeric($year)) {
      $dateStr = $year;
      
      // ISO 8601
      if (preg_match('/[0-9]{2}T[0-9]{2}/', $dateStr, $m)) {
        $dateStr = str_replace($m[0], str_replace('T', ' ', $m[0]), $dateStr);
      }
      
      $year = NULL;
      
      // DATE/TIME
      // MM.DD.(YY|YYYY).HH.MM.SS
      if (preg_match('/^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4})\.([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,2})$/', trim($dateStr), $matches)) {
        $month = $matches[1];
        $day = $matches[2];
        $year = $matches[3];
        $hour = $matches[4];
        $minute = $matches[5];
        $second = $matches[6];
        $dateStr = '';
      }
      
      // TIME
      // ISO 8601 (HH:MM:SS or HH:MM or HH)
      if (preg_match('/([0-9]{1,2}):([0-9]{1,2})?:?([0-9]{1,2})? ?([a|A|p|P])?.*/', $dateStr, $matches)) {
        $match = $matches[0];
        $hour = $matches[4] && strtolower($matches[4]) == 'a' && $matches[1] == 12 ? 0 : ($matches[4] && strtolower($matches[4]) == 'p' && $matches[1] < 12 ? $matches[1] + 12 : $matches[1]);
        if ($matches[2]) { $minute = $matches[2]; }
        if ($matches[3]) { $second = $matches[3]; }
        // YEAR
        if (preg_match('/ ([1-2][0-9]{3})/', $dateStr, $matches)) {
          $year = $matches[1];
        }
        // TIMEZONE
        if (preg_match('/:.*([a-z|A-Z|\/]+)?([+|-])([0-9]{2}):?([0-9]{2})? *([a-z|A-Z]{3})?.*/', $dateStr, $matches)) {
          if ($tz || !$matches[1] || !SRA_TimeZone::isValid($tz =& SRA_TimeZone::getTimeZone($matches[1]))) {
            $offset = ($matches[2] . (($matches[3]*60) + (isset($matches[4]) ? $matches[4]*1 : 0))) * 1;
            if (!SRA_TimeZone::isValid($tz)) { $tz =& SRA_Controller::getAppTimeZone(); }
          }
        }
        $dateStr = str_replace($match, '', $dateStr);
      }
      
      // DATE
      // 1972 sep 24
      if (preg_match('/([0-9]{4}) *([a-zA-Z\.]+) *([0-9]{1,2})?.*/', $dateStr, $matches) && ($matches[2] = SRA_GregorianDate::getMonthFromStr($matches[2]))) {
        $day = $matches[3];
        $month = $matches[2];
        $year = $matches[1] ? $matches[1] : (is_numeric($year) ? $year : NULL);
      }
      // 24 sep 1972 or 24-sept-72 or 24sep97
      else if (preg_match('/([0-9]{1,2})-? *([a-zA-Z\.]+)-? *([0-9]{1,4})?.*/', $dateStr, $matches) && ($matches[2] = SRA_GregorianDate::getMonthFromStr($matches[2]))) {
        $day = $matches[1];
        $month = $matches[2];
        $year = $matches[3] ? $matches[3] : (is_numeric($year) ? $year : NULL);
      }
      // sep 24, 1997 or september 24
      else if (preg_match('/([a-zA-Z\.]+)-? *([0-9]{1,2}),?-? *([0-9]{1,4})?.*/', $dateStr, $matches) && ($matches[1] = SRA_GregorianDate::getMonthFromStr($matches[1]))) {
        $month = $matches[1];
        $day = $matches[2];
        $year = $matches[3] ? $matches[3] : (is_numeric($year) ? $year : NULL);
      }
      // ISO 8601 (YYYY-MM-DD or YY-MM-DD)
      else if (preg_match('/([0-9]{1,4})?-?([0-9]{1,2})-([0-9]{1,2})*/', $dateStr, $matches)) {
        $year = $matches[1] ? $matches[1] : (is_numeric($year) ? $year : NULL);
        $month = $matches[2];
        $day = $matches[3];
      }
      // Common U.S. format: MM/DD/YY or MM/DD/YYYY
      else if (preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/?([0-9]{1,4})?.*/', $dateStr, $matches)) {
        $month = $matches[1];
        $day = $matches[2];
        $year = $matches[3] ? $matches[3] : (is_numeric($year) ? $year : NULL);
      }
    }
    
    // prefix 2-character years
    $year = isset($year) && strlen($year) == 2 && is_numeric($year) ? ($year >= 69 && $year <= 99 ? '19' . $year : '20' . $year) : $year; 
    
    $noParams = !isset($year) && !isset($month) && !isset($day) && !isset($hour) && !isset($minute) && !isset($second);
    
    // date/time
    if ($noParams || isset($hour) || isset($minute) || isset($second)) {
      $this->_dateOnly = FALSE;
    }
    
    // set timezone
    if (!$this->_dateOnly) {
      if (SRA_TimeZone::isValid($tz)) {
        $this->_tz =& $tz;
      }
      else {
        $this->_tz =& SRA_Controller::getAppTimeZone();
      }
    }
    
    // create based on current date/time
    if ($noParams) {
      $this->setYear(date('Y'));
      $this->setMonth(date('n'));
      $this->setDay(date('j'));
      $this->setHour(date('G'));
      $this->setMinute(date('i'));
      $this->setSecond(date('s'));
    }
    // create based on parameters
    else {
      $this->setYear($year && is_numeric($year) ? $year : date('Y'));
      $this->setMonth($month && is_numeric($month) ? $month : date('n'));
      $this->setDay($day && is_numeric($day) ? $day : date('j'));
      if (isset($hour) || isset($minute) || isset($second)) {
        $this->setHour($hour && is_numeric($hour) ? $hour : 0);
        $this->setMinute($minute && is_numeric($minute) ? $minute : 0);
        $this->setSecond($second && is_numeric($second) ? $second : 0);
      }
    }
    
    // timezone offset
    if (isset($offset)) {
      // jump (when timezone offset specified, but timezone could not be found)
      if ($jumpMinutes = $tz->getGmtOffset($this) - $offset) {
        $this->jump(SRA_GREGORIAN_DATE_UNIT_MINUTE, $jumpMinutes);
      }
    }
    
    $this->_init = FALSE;
  }
  // }}}
  
  
  
  // {{{ compare
  /**
   * compares 2 SRA_GregorianDate instances. returns 0 if they represent the 
   * exact same date/time, -1 if $date is before this date, and +1 if $date is 
   * after this date. time zone is also considered in the evaluation. if either 
   * date is a 'dateOnly', the time will be ignored in the comparison for both
   * @param SRA_GregorianDate $date the date to compare with
   * @param boolean $dateOnly if both this and $date are non-dateOnly dates, 
   * this parameter can be used to compare the dates only (ignore times)
   * @access public
   * @return int
   */
  function compare(&$date, $dateOnly=FALSE) {
    if (is_scalar($date)) { $date = new SRA_GregorianDate($date); }
    
    return SRA_GregorianDate::isValid($date) ? $this->toInt() == $date->toInt() ? ($dateOnly || $this->isDateOnly() || $date->isDateOnly() || $this->toIntTime() == $date->toIntTime() ? 0 : ($this->toIntTime() < $date->toIntTime() ? -1 : 1)) : ($this->toInt() < $date->toInt() ? -1 : 1) : FALSE;
  }
  // }}}
  
  // {{{ copy
  /**
   * returns a new instance of SRA_GregorianDate with the same date as this 
   * instance
   * @access public
   * @return SRA_GregorianDate
   */
  function &copy() {
    return new SRA_GregorianDate($this->_year, $this->_month, $this->_day, $this->_hour, $this->_minute, $this->_second, $this->_tz);
  }
  // }}}
  
  // {{{ cron
  /**
   * used to determine whether or not a cron formatted schedule string is valid 
   * for the current time (or for the SRA_GregorianDate instance when invoked as 
   * an instance method). if $schedule is not a valid formatted cron schedule 
   * string, this method will return NULL. otherwise, TRUE or FALSE will be 
   * returned (use the === operator to evaluate the results)
   * @param string $schedule the cron-formatted schedule string. this string 
   * is a space separated list of time identifiers in the following order:
   *   1: the schedule minute of the hour (between 0 and 59)
   *   2: the schedule hour (0 and 23 where 0 is midnight)
   *   3: the schedule day of month
   *   4: the schedule month (1-12)
   *   5: the schedule day of week (0-6 where 0=sunday)
   * '*' in any of these identifiers means that the schedule should occur in 
   * all instances of that type of identifier. additionally, multiple comma 
   * separated values may be specified for each identifier. for more 
   * information, there are a variety of resources available on the web 
   * regarding "cron scheduling"
   * @access public
   * @return boolean
   */
  function cron($schedule) {
    $do = FALSE;
    $dt = SRA_GregorianDate::isValid($this) && !$this->isDateOnly() ? $this : new SRA_GregorianDate();
    $schedule = explode(' ', $schedule);
    if (SRA_GregorianDate::isValid($dt) && $schedule && count($schedule) == 5) {
      $scheduleParts['minute'] = explode(',', $schedule[0]);
      $scheduleParts['hour'] = explode(',', $schedule[1]);
      $scheduleParts['dom'] = explode(',', $schedule[2]);
      $scheduleParts['month'] = explode(',', $schedule[3]);
      $scheduleParts['dow'] = explode(',', $schedule[4]);
      $keys = array_keys($scheduleParts['minute']);
      foreach($keys as $key) {
        if ($scheduleParts['minute'][$key] != '*' && (!is_numeric($scheduleParts['minute'][$key]) || ($scheduleParts['minute'][$key] < 0 || $scheduleParts['minute'][$key] > 59))) {
          $do = NULL;
        }
        else if ($scheduleParts['minute'][$key] == '*') {
          $scheduleParts['minute'] = '*';
          break;
        }
      }
      $keys = array_keys($scheduleParts['hour']);
      foreach($keys as $key) {
        if ($scheduleParts['hour'][$key] != '*' && (!is_numeric($scheduleParts['hour'][$key]) || ($scheduleParts['hour'][$key] < 0 || $scheduleParts['hour'][$key] > 23))) {
          $do = NULL;
        }
        else if ($scheduleParts['hour'][$key] == '*') {
          $scheduleParts['hour'] = '*';
          break;
        }
      }
      $keys = array_keys($scheduleParts['dom']);
      foreach($keys as $key) {
        if ($scheduleParts['dom'][$key] != '*' && (!is_numeric($scheduleParts['dom'][$key]) || ($scheduleParts['dom'][$key] < 1 || $scheduleParts['dom'][$key] > 31))) {
          $do = NULL;
        }
        else if ($scheduleParts['dom'][$key] == '*') {
          $scheduleParts['dom'] = '*';
          break;
        }
      }
      $keys = array_keys($scheduleParts['month']);
      foreach($keys as $key) {
        if ($scheduleParts['month'][$key] != '*' && (!is_numeric($scheduleParts['month'][$key]) || ($scheduleParts['month'][$key] < 1 || $scheduleParts['month'][$key] > 12))) {
          $do = NULL;
        }
        else if ($scheduleParts['month'][$key] == '*') {
          $scheduleParts['month'] = '*';
          break;
        }
      }
      $keys = array_keys($scheduleParts['dow']);
      foreach($keys as $key) {
        if ($scheduleParts['dow'][$key] != '*' && (!is_numeric($scheduleParts['dow'][$key]) || ($scheduleParts['dow'][$key] < 0 || $scheduleParts['dow'][$key] > 6))) {
          $do = NULL;
        }
        else if ($scheduleParts['dow'][$key] == '*') {
          $scheduleParts['dow'] = '*';
          break;
        }
      }
      $do = $do !== NULL ? ($scheduleParts['dow'] == '*' || in_array($dt->getDayOfWeek(), $scheduleParts['dow'])) && ($scheduleParts['month'] == '*' || in_array($dt->getMonth(), $scheduleParts['month']) || in_array($dt->getMonth(TRUE), $scheduleParts['month'])) && ($scheduleParts['dom'] == '*' || in_array($dt->getDay(), $scheduleParts['dom']) || in_array($dt->getDay(TRUE), $scheduleParts['dom'])) && ($scheduleParts['hour'] == '*' || in_array($dt->getHour(), $scheduleParts['hour']) || in_array($dt->getHour(TRUE), $scheduleParts['hour'])) && ($scheduleParts['minute'] == '*' || in_array($dt->getMinute(), $scheduleParts['minute']) || in_array($dt->getMinute(TRUE), $scheduleParts['minute'])) : $do;
    }
    return $do;
  }
  // }}}
  
  // {{{ encode
  /**
   * returns this gregorian date object as an encoded date string in the format 
   * YYYYMMDDHHMMSS (HHMMSS are only applicable when 'dateOnly' is FALSE). thus
   * the return value will be 8 characters in length when dateOnly is TRUE, 14
   * characters in length otherwise
   * @access public
   * @return string
   */
  function encode() {
    return $this->format($this->isDateOnly() ? 'Ymd' : 'YmdHis');
  }
  // }}}
  
  // {{{ equals
  /**
   * returns TRUE if this data is equal to $date. in order to be equal, the 
   * 'format' string must be equal and the timezone must be equal (for 
   * non-date only dates only)
   * @param mixed $date the date to compare with
   * @access public
   * @return boolean
   */
  function equals(& $date) {
    return SRA_GregorianDate::isValid($date) && $this->encode() == $date->encode() && ($this->isDateOnly() || (($tz =& $this->getTimeZone()) && $tz->equals($date->getTimeZone())));
  }
  // }}}
  
  // {{{ format
  /**
   * used to convert this gregorian date object to a formatted string based on 
   * the $format string provided. if this method is called statically, a new 
   * the formatted string will be for the current time
   * @param string $format the format string to use. if not specified, 
   * SRA_Controller::getAppDateOnlyFormat() will be used (see documentation in 
   * sierra/etc/app-config*.dtd for the "date-only-format" attribute)
   * 
   * The following characters are allowed in the $format parameter string (break 
   * any of these characters by preceding it with \):
   * 
   *  DAY FORMAT CHARACTERS
   *    d  day of the month, 2 digits with leading zeros: 01-31
   *    D  a textual representation of a day, three letters: Mon through Sun
   *    j  day of the month without leading zeros: 1-31
   *    l  a full textual representation of the day of the week: Sunday - Saturday
   *    L  lowercase full textual representation of the day of the week: sunday - saturday
   *    N  ISO-8601 numeric representation of the day of the week: 1 (for Monday) through 7 (for Sunday)
   *    S  english ordinal suffix for the day of the month: st, nd, rd or th. works well with j
   *    w  numeric representation of the day of the week: 0 (for Sunday) through 6 (for Saturday)
   *    z  the day of the year (starting from 0): 0 through 365
   *
   *  WEEK FORMAT CHARACTERS
   *    W  ISO-8601 week number of year, weeks starting on monday: example: 42 (the 42nd week in the year)
   *
   *  MONTH FORMAT CHARACTERS
   *    F  a full textual representation of a month, such as January or March: January through December
   *    m  numeric representation of a month, with leading zeros: 01 through 12
   *    M  a short textual representation of a month, three letters: Jan through Dec
   *    n  numeric representation of a month, without leading zeros: 1 through 12
   *    t  number of days in the given month: 28 through 31
   *
   *  YEAR FORMAT CHARACTERS
   *    L  whether it's a leap year: 1 if it is a leap year, 0 otherwise
   *    o  ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead: examples: 1999 or 2003
   *    Y  a full numeric representation of a year, 4 digits: Examples: 1999 or 2003
   *    y  a two digit representation of a year: Examples: 99 or 03
   *
   * The format characters below are ONLY applicable for non-dateOnly dates. if 
   * used otherwise, they will be replaced with empty string
   *  TIME FORMAT CHARACTERS
   *    a  lowercase Ante meridiem and Post meridiem: am or pm
   *    A  uppercase Ante meridiem and Post meridiem: AM or PM
   *    g  12-hour format of an hour without leading zeros: 1 through 12
   *    G  24-hour format of an hour without leading zeros: 0 through 23
   *    h  12-hour format of an hour with leading zeros: 01 through 12
   *    H  24-hour format of an hour with leading zeros: 00 through 23
   *    i  minutes with leading zeros: 00 to 59
   *    s  seconds with leading zeros: 00 to 59
   * 
   *  TIMEZONE FORMAT CHARACTERS
   *    e  timezone identifier: i.e. America/Boise, GMT, etc.
   *    I  whether or not the date is in daylight saving time: 1 if daylight saving time, 0 otherwise
   *    O  difference to Greenwich time (GMT) in hours: example: +0200
   *    P  difference to Greenwich time (GMT) with colon between hours and minutes: example: +02:00
   *    T  timezone abbreviation: examples: EST, MDT
   *    Z  timezone offset in seconds. the offset for timezones west of GMT is always negative, and for those east of GMT is always positive: -43200 through 50400
   *
   *  FULL DATE/TIME
   *    c  ISO 8601 date: 2004-02-12T15:19:21+00:00 (basically a shortcut to format string SRA_GREGORIAN_DATE_FORMAT_ISO8601)
   *    r  RFC 2822 formatted date: example: Thu, 21 Dec 2000 16:01:07 +0200 (basically a shortcut to format string SRA_GREGORIAN_DATE_FORMAT_RFC2822)
   *
   *  RELATIVE DATE STRINGS
   *    when a relative token is used and a relative value is not possible, the 
   *    application date-format or date-only-format wil be used
   *    R  relative date string such as "Today", "Tommorow", "Yesterday", 
   *       "3 days ago" (up to 6 days max), "1 week ago", "In 2 weeks", 
   *       "1 month ago", "In 2 months", "In 1 year", "1 year ago", etc. if no 
   *       valid relative strings are possible, this token will be replaced with 
   *       the date string using the app or sys default format. when a relative 
   *       date string is used, any other date tokens will be ignored
   *    R1 relative date string - days only
   *    RT relative time string - same as R, but includes relative times such as 
   *       "About 5 minutes ago", "About 2 hours ago", "In about 3 hours", 
   *       "30 seconds ago", etc. relative time strings are used for up to +/- 6
   *       SRA_GREGORIAN_DATE_RELATIVE_TIME_CAP hours. when a relative time 
   *       string is used, any other time tokens will be ignored. if not, a 
   *       relative date string will be used
   * @param boolean $ignoreDateTokens whether or not to ignore date tokens in 
   * $format
   * @param boolean $ignoreTimeTokens whether or not to ignore time tokens in 
   * $format
   * @param boolean $ignoreRelativeTokens whether or not to ignore relative 
   * date/time tokens in $format
   * @access public
   * @return string
   */
  function format($format=NULL, $ignoreDateTokens=FALSE, $ignoreTimeTokens=FALSE, $ignoreRelativeTokens=FALSE) {
    if ($this && SRA_GregorianDate::isValid($this)) {
      $dt =& $this;
    }
    else {
      $dt = new SRA_GregorianDate();
    }
    
    $formatted = '';
    $dow = $dt->getDayOfWeek();
    $format = $format ? $format : ($dt->isDateOnly() ? SRA_Controller::getAppDateOnlyFormat() : SRA_Controller::getAppDateFormat());
    $resources =& SRA_Controller::getSysResources();
    for($i=0; $i<strlen($format); $i++) {
      $str = substr($format, $i, 1);
      $str1 = substr($format, $i+1, 1);
      
      if ($str == 'R' && ($str1 == '1' || $str1 == 'T')) {
        $str .= $str1;
        $i++;
      }
      
      // break character
      if ($str == '\\') {
        $i++;
        $str = substr($format, $i, 1);
      }
      
      // day
      else if ($str === 'd') { $str = $ignoreDateTokens ? '' : $dt->getDay(TRUE); }
      else if ($str === 'D') { $str = $ignoreDateTokens ? '' : $resources->getString('date.day.abbr.' . $dow); }
      else if ($str === 'j') { $str = $ignoreDateTokens ? '' : $dt->getDay(); }
      else if ($str === 'l') { $str = $ignoreDateTokens ? '' : $resources->getString('date.day.' . $dow); }
      else if ($str === 'L') { $str = $ignoreDateTokens ? '' : strtolower($resources->getString('date.day.' . $dow)); }
      else if ($str === 'N') { $str = $ignoreDateTokens ? '' : $dt->getISO8601DayOfWeek(); }
      else if ($str === 'S') { $str = $ignoreDateTokens ? '' : SRA_Util::getEnglishOrdinalSuffix($dt->getDay()); }
      else if ($str === 'w') { $str = $ignoreDateTokens ? '' : $dow; }
      else if ($str === 'z') { $str = $ignoreDateTokens ? '' : $dt->getDayOfYear(); }
      // week
      else if ($str === 'W') { $str = $ignoreDateTokens ? '' : $dt->getISO8601WeekOfYear(); }
      // month
      else if ($str === 'F') { $str = $ignoreDateTokens ? '' : $resources->getString('date.month.' . $dt->getMonth()); }
      else if ($str === 'm') { $str = $ignoreDateTokens ? '' : $dt->getMonth(TRUE); }
      else if ($str === 'M') { $str = $ignoreDateTokens ? '' : $resources->getString('date.month.abbr.' . $dt->getMonth()); }
      else if ($str === 'n') { $str = $ignoreDateTokens ? '' : $dt->getMonth(); }
      else if ($str === 't') { $str = $ignoreDateTokens ? '' : $dt->getNumDaysInMonth(); }
      // year
      else if ($str === 'L') { $str = $ignoreDateTokens ? '' : $dt->isLeapYear() ? '1' : '0'; }
      else if ($str === 'o') { $str = $ignoreDateTokens ? '' : $dt->getISO8601Year(); }
      else if ($str === 'Y') { $str = $ignoreDateTokens ? '' : $dt->getYear(); }
      else if ($str === 'y') { $str = $ignoreDateTokens ? '' : substr($dt->getYear(), 2, 2); }
      // time
      else if ($str === 'a') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : strtolower($resources->getString($dt->getHour()>11 ? 'date.meridiem.pm' : 'date.meridiem.am')); }
      else if ($str === 'A') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $resources->getString($dt->getHour()>11 ? 'date.meridiem.pm' : 'date.meridiem.am'); }
      else if ($str === 'g') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getHour12(); }
      else if ($str === 'G') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getHour(); }
      else if ($str === 'h') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getHour12(TRUE); }
      else if ($str === 'H') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getHour(TRUE); }
      else if ($str === 'i') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getMinute(TRUE); }
      else if ($str === 's') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->getSecond(TRUE); }
      // timezone
      else if ($str === 'e') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->_tz->getId(); }
      else if ($str === 'I') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->isInDaylightSavings() ? '1' : '0'; }
      else if ($str === 'O') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->_tz->getGmtOffsetString($dt); }
      else if ($str === 'P') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->_tz->getGmtOffsetStringISO8601($dt); }
      else if ($str === 'T' && $format != SRA_GREGORIAN_DATE_FORMAT_ISO8601) { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->_tz->getAbbr($dt); }
      else if ($str === 'Z') { $str = $ignoreTimeTokens ? '' : $dt->isDateOnly() ? '' : $dt->_tz->getGmtOffset($dt)*60*60; }
      // full date/time
      else if ($str === 'c') { 
        $str = $ignoreDateTokens ? '' : $dt->format(SRA_GREGORIAN_DATE_FORMAT_ISO8601, $includeTime);
        $str = $dt->_tz ? str_replace($dt->_tz->getAbbr($dt), 'T', $str) : str_replace('::', '', $str);
      }
      else if ($str === 'r') { $str = $ignoreDateTokens ? '' : $dt->format(SRA_GREGORIAN_DATE_FORMAT_RFC2822, $includeTime); }
      // relative date strings
      else if ($str === 'R' || $str === 'R1' || $str === 'RT') {
        if ($ignoreRelativeTokens) {
          $str = '';
        }
        else {
          $baseStr = $str;
          $now = new SRA_GregorianDate();
          $isPast = $dt->compare($now) < 0;
          $ignoreDateTokens = TRUE;
          $relativeTime = FALSE;
          
          if ($str === 'RT' && ($nymdh = $now->format('YmdH')) && ($ymdh = $dt->format('YmdH')) && (($isPast && ($ymdh + SRA_GREGORIAN_DATE_RELATIVE_TIME_CAP) >= $nymdh) || (!$isPast && ($ymdh - SRA_GREGORIAN_DATE_RELATIVE_TIME_CAP) <= $nymdh))) {
            $numSeconds = abs($now->getUnixTimeStamp() - $dt->getUnixTimeStamp());
            $numMinutes = round($numSeconds/60);
            $numHours = round($numMinutes/60);
            if ($numSeconds > 55 && $numSeconds < 60) $numSeconds = 60;
            if ($numMinutes > 55 && $numMinutes < 60) $numMinutes = 60;
            
            $str = $resources->getString('date.relative.' . (!$numSeconds && !$numMinutes && !$numHours ? 'now' : ($numSeconds < 60 ? 'second' : ($numMinutes < 60 ? 'minute' : 'hour')) . ($isPast ? 'Ago' : '') . (($numSeconds < 60 && $numSeconds > 1) || ($numMinutes < 60 && $numMinutes > 1) || $numHours > 1 ? 's' : '')), array('num' => $numSeconds < 60 ? $numSeconds : ($numMinutes < 60 ? $numMinutes : $numHours)));
            $relativeTime = TRUE;
          }
          else if (($numYears = abs($now->getYear() - $dt->getYear())) && $str != 'R1' && $now->format('md') == $dt->format('md')) { $str = $resources->getString('date.relative.year' . ($isPast ? 'Ago' : '') . ($numYears == 1 ? '' : 's'), array('num' => $numYears)); }
          else if (($numMonths = $dt->getMonthsDelta($now)) && $str != 'R1' && $now->format('d') == $dt->format('d')) { $str = $resources->getString('date.relative.month' . ($isPast ? 'Ago' : '') . ($numMonths == 1 ? '' : 's'), array('num' => $numMonths)); }
          else if (($sameYear = $now->format('Y') == $dt->format('Y')) && ((($numDays = $dt->getDayDelta($now))) == 1)) { $str = $resources->getString('date.relative.' . ($isPast ? 'yesterday' : 'tomorrow')); }
          else if ($sameYear && !$numDays) { $str = $resources->getString('date.relative.today'); }
          else if ($sameYear && $numDays && (($numDays/7) <= 5) && $str != 'R1' && !($numDays % 7)) { $str = $resources->getString('date.relative.week' . ($isPast ? 'Ago' : '') . ($numDays == 7 ? '' : 's'), array('num' => ($numDays/7))); }
          else if ($sameYear && $numDays < 7) { $str = $resources->getString('date.relative.day' . ($isPast ? 'Ago' : '') . 's', array('num' => ($numDays))); }
          else {
            $ignoreDateTokens = FALSE;
            $str = $dt->format(str_replace($str, '', $str === 'RT' ? SRA_Controller::getAppDateFormat() : SRA_Controller::getAppDateOnlyFormat())); 
          }
          $ignoreTimeTokens = $baseStr == 'RT' && $ignoreDateTokens;
          if ($format == 'RT' && !$relativeTime && $ignoreDateTokens) {
            $str .= ' ' . $dt->format(NULL, TRUE, FALSE, TRUE);
            $str = str_replace(' // ', ' ', $str);
          }
        }
      }
      $formatted .= $str;
    }
    return trim($formatted);
  }
  // }}}
  
  // {{{ fromRelativeStr
  /**
   * creates a new relative SRA_GregorianDate object based on input in the 
   * format "YYYY-MM-DD HH:MM:SS" (time is optional). where any of the date 
   * values (YYYY, MM, DD, HH, MM, or SS) may be replaced with a relative 
   * modifier in the format "+n" where n is the increase from the current 
   * timestamp. For example, to specify the 1st of the following month, the 
   * $expr would be: "+0-+1-01" - where +0 signifies the current year, and +1 
   * signifies the following month. if the current month was december (12), the 
   * following month will be January and the year will be incremented 
   * automatically. Another example: to specify exactly one week from the 
   * current time, $expr would be "+0-+0-+7" - where the first +0 signifies the 
   * current year, the second +0 signifies the current month, and +7 signifies 7 
   * days from the current date. month and year rollovers resulting in the 1 
   * week jump will be automatically applied (for example, if the action was 
   * created on 12/28). negative increments can be applied by enclosing the 
   * increment value "n" in parenthesis. for example, to specify 1 month minus 1 
   * week from the current date, $expr would be: "+0-+1-+(7)". returns NULL on 
   * error
   * @param string $expr the relative date expression to use
   * @param SRA_GregorianDate $start the start date. if not specified, the 
   * current date will be used
   * @access	public static
   * @return	SRA_GregorianDate
   */
  function fromRelativeStr($expr, $start=NULL) {
    $pieces = explode(' ', $expr);
    $dateParts = explode('-', $pieces[0]);
    if (isset($pieces[1])) { $timeParts = explode(':', $pieces[1]); }
    if (count($dateParts) != 3) {
      return NULL;
    }
    $date = SRA_GregorianDate::isValid($start) ? $start : new SRA_GregorianDate();
    
    // first set explicit values
    if (!SRA_Util::beginsWith($dateParts[0], '+')) { $date->setYear($dateParts[0]); }
    if (!SRA_Util::beginsWith($dateParts[1], '+')) { $date->setMonth($dateParts[1]); }
    if (!SRA_Util::beginsWith($dateParts[2], '+')) { $date->setDay($dateParts[2]); }
    $date->setHour($timeParts && isset($timeParts[0]) && !SRA_Util::beginsWith($timeParts[0], '+') ? $timeParts[0] : (SRA_Util::beginsWith($timeParts[0], '+') ? $date->getHour() : 0));
    $date->setMinute($timeParts && isset($timeParts[1]) && !SRA_Util::beginsWith($timeParts[1], '+') ? $timeParts[1] : (SRA_Util::beginsWith($timeParts[1], '+') ? $date->getMinute() : 0));
    $date->setSecond($timeParts && isset($timeParts[2]) && !SRA_Util::beginsWith($timeParts[2], '+') ? $timeParts[2] : (SRA_Util::beginsWith($timeParts[2], '+') ? $date->getSecond() : 0));
    
    // now set relative values
    if (SRA_Util::beginsWith($dateParts[0], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_YEAR, SRA_Util::getRelativeNumericVal($dateParts[0])); }
    if (SRA_Util::beginsWith($dateParts[1], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_MONTH, SRA_Util::getRelativeNumericVal($dateParts[1])); }
    if (SRA_Util::beginsWith($dateParts[2], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_DAY, SRA_Util::getRelativeNumericVal($dateParts[2])); }
    if ($timeParts && isset($timeParts[0]) && SRA_Util::beginsWith($timeParts[0], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_HOUR, SRA_Util::getRelativeNumericVal($timeParts[0])); }
    if ($timeParts && isset($timeParts[1]) && SRA_Util::beginsWith($timeParts[1], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_MINUTE, SRA_Util::getRelativeNumericVal($timeParts[1])); }
    if ($timeParts && isset($timeParts[2]) && SRA_Util::beginsWith($timeParts[2], '+')) { $date->jump(SRA_GREGORIAN_DATE_UNIT_SECOND, SRA_Util::getRelativeNumericVal($timeParts[2])); }
    
    return $date;
  }
  // }}}
  
  // {{{ get
  /**
   * wrapper method to the corresonding get method for the property specified
   * @param string $property the property to get: 
   * (day|hour|meridiem|minute|month|second|year)
   * @access public
   * @return string
   */
   function get($property) {
     return $this->format($property=='day' ? 'j' : ($property=='hour' ? 'H' : ($property=='meridiem' ? 'a' : ($property=='minute' ? 'i' : ($property=='month' ? 'n' : ($property=='second' ? 's' : ($property=='year' ? 'Y' : NULL)))))));
   }
   // }}}
  
  // {{{ getDay
  /**
   * returns the date month day 1-31 or 01-31 when $pad is TRUE
   * @param boolean $pad whether or not to pad the day with a leading 0 when it 
   * is < 10
   * @access public
   * @return mixed
   */
  function getDay($pad=FALSE) {
    return $pad ? sprintf('%02d', $this->_day) : $this->_day;
  }
  // }}}
  
  // {{{ getDayDelta
  /**
   * compares this date with $compare and returns the # of days that separate 
   * them
   * @param SRA_GregorianDate $compare the date to compare with. if not 
   * specified the current date will be used
   * @access	public
   * @return	int
   */
  function getDayDelta(&$compare) {
    $compare = $compare ? $compare : new SRA_GregorianDate();
    if (SRA_GregorianDate::isValid($compare)) {
      $copy =& $compare->copy();
      $past = $this->compare($copy) < 0;
      $delta = 0;
      $ymd = $this->format('Ymd');
      while($ymd != ($cymd = $copy->format('Ymd'))) {
        $delta++;
        $copy->jump(SRA_GREGORIAN_DATE_UNIT_DAY, $past ? -1 : 1);
      }
      return $delta;
    }
  }
  // }}}
  
  // {{{ getDayOfWeek
  /**
   * returns the numeric representation of the day of week for this date 
   * (0=sun and 6=sat) OR for the $year/$month/$day specified (when invoked 
   * statically)
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @param int $day the day. the current date day used if not specified
   * @access public OR public static
   * @return int
   */
  function getDayOfWeek($year=NULL, $month=NULL, $day=NULL) {
    $year = $year ? $year : $this->_year;
    $month = $month ? $month : $this->_month;
    $day = $day ? $day : $this->_day;
    $mcodes = array(0, 3, 3, 6, 1, 4, 6, 2, 5, 0, 3, 5);
    $mlcodes = array(6, 2);
    $y1 = substr($year, 0, 2) * 1;
    $y2 = substr($year, 2, 2) * 1;
    
    $c = (3 - ($y1 % 4)) * 2;
    $y = $y2 + floor($y2/4);
    $m = $month < 3 && SRA_GregorianDate::isLeapYear($year) ? $mlcodes[$month-1] : $mcodes[$month-1];
    return ($c+$y+$m+$day) % 7;
  }
  // }}}
  
  // {{{ getDayOfYear
  /**
   * returns the numeric representation of the day of year for the this date 
   * (0-364 or 365 for leap years) OR for the $year/$month/$day specified (when 
   * invoked statically)
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @param int $day the day. the current date day used if not specified
   * @access public OR public static
   * @return int
   */
  function getDayOfYear($year=NULL, $month=NULL, $day=NULL) {
    $year = $year ? $year : $this->_year;
    $month = $month ? $month : $this->_month;
    $day = $day ? $day : $this->_day;
    $num = 0;
    for($i=1; $i<$month; $i++) { $num += SRA_GregorianDate::getNumDaysInMonth($year, $i); }
    return $num + $day - 1;
  }
  // }}}
  
  // {{{ getEaster
  /**
   * returns a date object representing when easter occurs for the year of this 
   * date object, or for $year is invoked statically
   * @param int $year the year. the current date year used if not specified
   * @access public OR public static
   * @return SRA_GregorianDate
   */
  function getEaster($year=NULL) {
    $year = $year ? $year : $this->getYear();
		$g = $year % 19;
		$c = (int)($year / 100);
		$h = (int)($c - ($c / 4) - ((8*$c+13) / 25) + 19*$g + 15) % 30;
		$i = (int)$h - (int)($h / 28)*(1 - (int)($h / 28)*(int)(29 / ($h + 1))*((int)(21 - $g) / 11));
		$j = ($year + (int)($year/4) + $i + 2 - $c + (int)($c/4)) % 7;
		$l = $i - $j;
		$m = 3 + (int)(($l + 40) / 44);
		$d = $l + 28 - 31 * ((int)($m / 4));
		return new SRA_GregorianDate($year, $m, $d);
  }
  // }}}
  
  // {{{ getISO8601DayOfWeek
  /**
   * returns the ISO 8601 numeric representation of the day of week for this 
   * date (1=Mon and 7=Sun) OR for the $year/$month/$day specified (when invoked 
   * statically). this is slightly different to the behavior of 'getDayOfWeek' 
   * in that weeks start on Monday and are numbered 1-7 instead of 0-6
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @param int $day the day. the current date day used if not specified
   * @access public OR public static
   * @return int
   */
  function getISO8601DayOfWeek($year=NULL, $month=NULL, $day=NULL) {
    $dow = SRA_GregorianDate::getDayOfWeek($year ? $year : $this->_year, $month ? $month : $this->_month, $day ? $day : $this->_day);
    return $dow == 0 ? 7 : $dow;
  }
  // }}}
  
  // {{{ getISO8601EndDate
  /**
   * returns the ISO 8601 year end date for the this date OR for the $year 
   * specified (when invoked statically). the ISO 8601 end date is the day prior 
   * to the following year's ISO 8601 start date (see below)
   * @param int $year the year. the current date year used if not specified
   * @access public OR public static
   * @return SRA_GregorianDate
   */
  function &getISO8601EndDate($year=NULL) {
    $year = $year ? $year : $this->_year;
    $end = SRA_GregorianDate::getISO8601StartDate($year + 1);
    $end->jump(SRA_GREGORIAN_DATE_UNIT_DAY, -1);
    return $end;
  }
  // }}}
  
  // {{{ getISO8601NumWeeks
  /**
   * returns the number of ISO 8601 weeks in this date's year, or for the $year 
   * specified (when invoked statically). this is the # of Thursdays occuring 
   * in that year starting from the ISO 8601 start date (see below)
   * @param int $year the year. the current date year used if not specified
   * @access public OR public static
   * @return int
   */
  function getISO8601NumWeeks($year=NULL) {
    $start =& SRA_GregorianDate::getISO8601StartDate($year);
    $end =& SRA_GregorianDate::getISO8601EndDate($year);
    $weeks = 0;
    while($start->compare($end) < 0) { 
      $start->jump(SRA_GREGORIAN_DATE_UNIT_WEEK);
      $weeks++; 
    }
    return $weeks;
  }
  // }}}
  
  // {{{ getISO8601StartDate
  /**
   * returns the ISO 8601 year start date for the this date OR for the $year 
   * specified (when invoked statically). the ISO 8601 start date is the Monday 
   * of the week with the year's first Thursday in it
   * @param int $year the year. the current date year used if not specified
   * @access public OR public static
   * @return SRA_GregorianDate
   */
  function &getISO8601StartDate($year=NULL) {
    $year = $year ? $year : $this->_year;
    $start = new SRA_GregorianDate($year, 1, 1);
    // find the first thursday
    while($start->getDayOfWeek() != 4) { $start->jump(SRA_GREGORIAN_DATE_UNIT_DAY); }
    // backtrack to the prior monday
    while($start->getDayOfWeek() != 1) { $start->jump(SRA_GREGORIAN_DATE_UNIT_DAY, -1); }
    return $start;
  }
  // }}}
  
  // {{{ getISO8601WeekOfYear
  /**
   * returns the ISO-8601 week number for this gregorian date (1-53) OR for the 
   * $year/$month/$day specified (when invoked statically). the week number can 
   * be described as counting Thursdays (week 12 contains the 12th Thursday of 
   * the year). thus the first day of week 1 CAN occur in the prior year and the 
   * last day of the last week CAN occur in the next year. ISO-8601 considers 
   * Monday to be the first day of the week. for more information, see 
   * http://en.wikipedia.org/wiki/ISO_8601
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @param int $day the day. the current date day used if not specified
   * @access public OR public static
   * @return int
   */
  function getISO8601WeekOfYear($year=NULL, $month=NULL, $day=NULL) {
    $year = $year ? $year : $this->_year;
    $month = $month ? $month : $this->_month;
    $day = $day ? $day : $this->_day;
    $date = new SRA_GregorianDate($year, $month, $day);
    $start =& SRA_GregorianDate::getISO8601StartDate($year);
    $end =& SRA_GregorianDate::getISO8601EndDate($year);
    
    // week is in last week of prior year
    if ($date->compare($start) < 0) {
      return SRA_GregorianDate::getISO8601NumWeeks($year-1);
    }
    // week is in first week of following year
    else if ($date->compare($end) > 0) {
      return 1;
    }
    // count weeks
    else {
      $week = 0;
      do {
        $week++;
        $start->jump(SRA_GREGORIAN_DATE_UNIT_WEEK);
      } while($date->compare($start) > 1);
    }
  }
  // }}}
  
  // {{{ getISO8601Year
  /**
   * returns the ISO 8601 year for the this date OR for the $year/$month/$day 
   * specified (when invoked statically). the ISO 8601 year is the year in which 
   * the week this date resides in occurred. for more information see the api 
   * documentation for 'getISO8601WeekOfYear'
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @param int $day the day. the current date day used if not specified
   * @access public OR public static
   * @return int
   */
  function getISO8601Year($year=NULL, $month=NULL, $day=NULL) {
    $year = $year ? $year : $this->_year;
    $month = $month ? $month : $this->_month;
    $day = $day ? $day : $this->_day;
    $date = new SRA_GregorianDate($year, $month, $day);
    $start =& SRA_GregorianDate::getISO8601StartDate($year);
    $end =& SRA_GregorianDate::getISO8601EndDate($year);
    return $date->compare($start) < 0 ? $year - 1 : ($date->compare($end) > 0 ? $year + 1 : $year);
  }
  // }}}
  
  // {{{ getHour
  /**
   * returns the date hour 0-23 or 00-23 when $pad is TRUE. returns NULL if 
   * 'dateOnly' is TRUE
   * @param boolean $pad whether or not to pad the hour with a leading 0 when 
   * it is < 10
   * @access public
   * @return mixed
   */
  function getHour($pad=FALSE) {
    return $this->isDateOnly() ? NULL : ($pad ? sprintf('%02d', $this->_hour) : $this->_hour);
  }
  // }}}
  
  // {{{ getHour12
  /**
   * returns the date hour (as a 12-hour representation) 1-12 or 01-12 when $pad 
   * is TRUE. returns NULL if 'dateOnly' is TRUE
   * @param boolean $pad whether or not to pad the hour with a leading 0 when 
   * it is < 10
   * @access public
   * @return mixed
   */
  function getHour12($pad=FALSE) {
    $hour12 = $this->_hour==0 ? 12 : ($this->_hour>12 ? $this->_hour-12 : $this->_hour);
    return $this->isDateOnly() ? NULL : ($pad ? sprintf('%02d', $hour12) : $hour12);
  }
  // }}}
  
  // {{{ getMinute
  /**
   * returns the date minute 0-59 or 00-59 when $pad is TRUE
   * @param boolean $pad whether or not to pad the minute with a leading 0 when 
   * it is < 10. returns NULL if 'dateOnly' is TRUE
   * @access public
   * @return mixed
   */
  function getMinute($pad=FALSE) {
    return $this->isDateOnly() ? NULL : ($pad ? sprintf('%02d', $this->_minute) : $this->_minute);
  }
  // }}}
  
  // {{{ getMinuteDelta
  /**
   * compares this date with $compare and returns the # of minutes that separate 
   * them
   * @param SRA_GregorianDate $compare the date to compare with. if not 
   * specified the current date will be used
   * @access	public
   * @return	int
   */
  function getMinuteDelta(&$compare) {
    $compare = $compare ? $compare : new SRA_GregorianDate();
    return round(abs($compare->getUnixTimestamp() - $this->getUnixTimestamp())/60);
  }
  // }}}
  
  // {{{ getSecondDelta
  /**
   * compares this date with $compare and returns the # of seconds that separate 
   * them
   * @param SRA_GregorianDate $compare the date to compare with. if not 
   * specified the current date will be used
   * @access	public
   * @return	int
   */
  function getSecondDelta(&$compare) {
    $compare = $compare ? $compare : new SRA_GregorianDate();
    return abs($compare->getUnixTimestamp() - $this->getUnixTimestamp());
  }
  // }}}
  
  // {{{ getMonth
  /**
   * returns the date month day 1-12 or 01-12 when $pad is TRUE
   * @param boolean $pad whether or not to pad the month with a leading 0 when 
   * it is < 10
   * @access public
   * @return mixed
   */
  function getMonth($pad=FALSE) {
    return $pad ? sprintf('%02d', $this->_month) : $this->_month;
  }
  // }}}
  
  // {{{ getMonthFromStr
  /**
   * attempts to parse the month string $str and return the corresponding month 
   * number (1-12) for that month. $str can be either a full month name, or an 
   * abbreviation (with or without trainling period). the search is also not 
   * case-sensitive. returns NULL if not successful
   * @param string $str the month string to parse. example: September, sept, jun
   * sept.
   * @access public static
   * @return int
   */
  function getMonthFromStr($str) {
    if ($str) {
      if (SRA_Util::endsWith($str, '.')) { $str = substr($str, 0, -1); }
      
      $resources =& SRA_Controller::getSysResources();
      for($i=1; $i<=12; $i++) {
        if (SRA_Util::beginsWith($resources->getString('date.month.' . $i), $str, FALSE)) {
          return $i;
        }
      }
    }
    return NULL;
  }
  // }}}
  
  // {{{ getMonthsDelta
  /**
   * compares this date with $compare and returns the # of months that separate 
   * them
   * @param SRA_GregorianDate $compare the date to compare with. if not 
   * specified, the current date will be used
   * @access	public
   * @return	int
   */
  function getMonthsDelta(&$compare) {
    $compared = $compared ? $compared : new SRA_GregorianDate();
    if (SRA_GregorianDate::isValid($compare)) {
      $copy =& $compare->copy();
      $past = $this->compare($copy) < 0;
      $delta = 0;
      while($this->format('Ym') != $copy->format('Ym')) {
        $delta++;
        $copy->jump(SRA_GREGORIAN_DATE_UNIT_MONTH, $past ? -1 : 1);
      }
      return $delta;
    }
  }
  // }}}
  
  // {{{ getNumDaysInMonth
  /**
   * returns the # of days in the month/year for this date object OR for the 
   * $month/$year specified (when invoked statically)
   * @param int $year the year. the current date year used if not specified
   * @param int $month the month. the current date month used if not specified
   * @access public OR public static
   * @return int
   */
  function getNumDaysInMonth($year=NULL, $month=NULL) {
    $year = $year ? $year : $this->_year;
    $month = $month ? $month : $this->_month;
    $days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    return $month == 2 && SRA_GregorianDate::isLeapYear($year) ? 29 : $days[$month-1];
  }
  // }}}
  
  // {{{ getPropertyRange
  /**
   * returns an array of possible values for the property specified. the $range 
   * parameter may have the following structure:
   *         "N1 N3 N5"   : only options N1, N3 and N5 will be available
   *         "N1-N3"      : only options >= N1 and <= N3 will be available
   *				 "N1-N3 N5-N8": combination of the above
   *				 "-N1"        : the current property value to N1
   *				 "N1-"        : N1 to the current property value
   *				 "-"          : only the current property value
   *				 "-+3"        : the current property value or the current value +1, +2 and +3
   *				 "-!3"        : the current property value or the current value -1, -2, and -3
   *				 "N1-+3"      : N1 or N1+1, or N1+2, or N1+3
   *				 "*"          : all possible values for that property. for the year 
   *				                property, this range will be 1901-2038
   *         "%N"         : only values from the resulting set that are evenly divisible by N
   *         "(^|v)"      : sort method: ^ = ascending order, v = descending order. the default 
   *                        behavior is to apply the natural sort resulting from the parameters 
   *                        specified above
   *         "#"          : sets the current property values as the first in the property array 
   *                        (followed by the sort method specified)
   *
   * @param string $property the property to return the range for: (day|hour|hour12|meridiem|minute|month|second|year)
   * @param string $range the range to return. defaults to '*'
   * @param string $formatString optional format string defining the display value 
   *               format for each range value. if $formatString 
   *               contains the substring "{$rangeVal}", then that value will be 
   *               substituted with the actual range value and the display value 
   *               will be the value returned by the app resource bundle using 
   *               the resulting string as the key. if $formatString is not 
   *               specified, the following default formatStrings will be assumed:
   *                day:      'j'
   *                hour[12]: 'H'
   *                meridiem: 'A'
   *                minute:   'i'
   *                month:    'n'
   *                second:   's'
   *                year:     'Y'
   *                
   * @access public static
   * @return associative array (key = range value (with leading zeros), value = display value)
   */
  function getPropertyRange($property, $range='*', $formatString=FALSE) {
    $rb =& SRA_Controller::getAppResources();
    $workingDate = new SRA_GregorianDate();
    $workingDate->jumpToStartOfYear();
    $propertyRange = array();
    $divisors = array();
    $sort = '^';
    $firstCurrent = FALSE;
    if ($property == 'day') {
      $keyString = 'd';
      $formatString = $formatString ? $formatString : 'j';
      $propertyRange = SRA_Util::getArray(31);
    }
    if ($property == 'hour') {
      $keyString = 'G';
      $formatString = $formatString ? $formatString : 'H';
      $propertyRange = SRA_Util::getArray(24,0);
    }
    if ($property == 'hour12') {
      $keyString = 'g';
      $formatString = $formatString ? $formatString : 'H';
      $propertyRange = SRA_Util::getArray(12);
      $property = 'hour';
    }
    if ($property == 'meridiem') {
      $keyString = 'G';
      $formatString = $formatString ? $formatString : 'A';
      $propertyRange = array(1, 20);
      $setMethod = 'setHour';
    }
    if ($property == 'minute') {
      $keyString = 'i';
      $formatString = $formatString ? $formatString : 'i';
      $propertyRange = SRA_Util::getArray(60,0);
    }
    if ($property == 'month') {
      $keyString = 'n';
      $formatString = $formatString ? $formatString : 'n';
      $propertyRange = SRA_Util::getArray(12);
    }
    if ($property == 'second') {
      $keyString = 's';
      $formatString = $formatString ? $formatString : 's';
      $propertyRange = SRA_Util::getArray(60,0);
    }
    if ($property == 'year') {
      $keyString = 'Y';
      $formatString = $formatString ? $formatString : 'Y';
      $propertyRange = SRA_Util::getArray(138,1901);
    }
    if (!$setMethod) {
      $setMethod = 'set' . strtoupper(substr($property, 0, 1)) . substr($property, 1);
    }
    if (!$getMethod) {
      $getMethod = 'get' . strtoupper(substr($property, 0, 1)) . substr($property, 1);
    }
    if ($range && $range != '*') {
      $baseRange = $propertyRange;
      $propertyRange = array();
      $pieces = explode(' ', $range);
      foreach ($pieces as $piece) {
        $piece = trim($piece);
        if (!$piece) {
          continue;
        }
        if (strstr($piece, '-')) {
          $rangePieces = explode('-', $piece);
          $start = isset($rangePieces[0]) && strlen($rangePieces[0]) ? $rangePieces[0] : $this->${getMethod}();
          $end = isset($rangePieces[1]) && strlen($rangePieces[1]) ? $rangePieces[1] : $this->${getMethod}();
          if (strstr($start, '+')) {
            $start = str_replace('+', '', $start);
            $start = $this->${getMethod}() + $start;
          }
          if (strstr($end, '+')) {
            $end = str_replace('+', '', $end);
            $end = $this->${getMethod}() + $end;
          }
          if (strstr($start, '!')) {
            $start = str_replace('!', '', $start);
            $start = $this->${getMethod}() - $start;
          }
          if (strstr($end, '!')) {
            $end = str_replace('!', '', $end);
            $end = $this->${getMethod}() - $end;
          }
          if ($property == 'day') {
            $start = $start >= 1 ? $start : 1;
          }
          if ($property == 'hour') {
            $start = $start >= 0 ? $start : 0;
          }
          if ($property == 'hour12' || $property == 'month') {
            $start = $start >= 0 ? $start : 12;
          }
          if ($property == 'minute' || $property == 'second') {
            $start = $start >= 0 ? $start : 0;
          }
          if ($property == 'year') {
            $start = $start >= 1901 ? $start : 1901;
          }
          for($i=$start; $i<=$end; $i++) {
            $val = $i;
            if ($property == 'day') {
              $val = $val <= 31 ? $val : $val % 31;
            }
            if ($property == 'hour') {
              $val = $val <= 23 ? $val : $val % 24;
            }
            if ($property == 'hour12' || $property == 'month') {
              $val = $val <= 12 ? $val : $val % 12;
            }
            if ($property == 'minute' || $property == 'second') {
              $val = $val <= 59 ? $val : $val % 60;
            }
            if ($property == 'year') {
              $val = $val <= 2038 ? $val : $val % 2038;
            }
            $propertyRange[$val] = TRUE;
          }
          $propertyRange = array_keys($propertyRange);
        }
        else if (substr($piece, 0, 1) == '%') {
          $divisors[] = substr($piece, 1);
        }
        else if ($piece == '*') {
          $propertyRange = $baseRange;
        }
        else if ($piece == '^' || $piece == 'v') {
          $sort = $piece;
        }
        else if ($piece == '#') {
          $firstCurrent = TRUE;
        }
        else {
          $propertyRange[] = $piece;
        }
      }
    }
    $myRange = array();
    foreach($propertyRange as $val) {
      $workingDate->${setMethod}($val);
      $key = (int) $workingDate->format($keyString);
      $cont = TRUE;
      foreach ($divisors as $div) {
        if ($key % $div) {
          $cont = FALSE;
        }
      }
      if ($cont) {
        $myRange[$property == 'meridiem' ? ($key == 1 ? 'am' : 'pm') : $key] = strstr($formatString, '{$rangeVal}') ? $rb->getString(str_replace('{$rangeVal}', $val, $formatString)) : $workingDate->format($formatString);
      }
    }
    if ($sort == '^') {
      ksort($myRange);
    }
    else if ($sort == 'v') {
      krsort($myRange);
    }
    if ($firstCurrent) {
      $current = new SRA_GregorianDate();
      $curKey = (int) $current->format($keyString);
      $tmp = array();
      if (isset($myRange[$curKey])) {
        $tmp[$curKey] = $myRange[$curKey];
        foreach($myRange as $key => $val) {
          if ($key != $curKey) {
            $tmp[$key] = $val;
          }
        }
        $myRange = $tmp;
      }
    }
    
    return $myRange;
  }
  // }}}
  
  // {{{ getSecond
  /**
   * returns the date second 0-59 or 00-59 when $pad is TRUE. returns NULL if 
   * 'dateOnly' is TRUE
   * @param boolean $pad whether or not to pad the second with a leading 0 when 
   * it is < 10
   * @access public
   * @return mixed
   */
  function getSecond($pad=FALSE) {
    return $this->isDateOnly() ? NULL : ($pad ? sprintf('%02d', $this->_second) : $this->_second);
  }
  // }}}
  
  // {{{ getTimeZone
  /**
   * returns the time zone for this date instance (if applicable)
   * @access public
   * @return SRA_TimeZone
   */
  function &getTimeZone() {
    return $this->_tz;
  }
  // }}}
  
  // {{{ getUnixTimeStamp
  /**
   * returns the current time measured in the number of seconds since the Unix 
   * Epoch (January 1 1970 00:00:00 GMT). Warning: do not use this method for 
   * dates that occurred prior to the unix epoch
   * @access public
   * @return int
   */
  function getUnixTimeStamp() {
    $tz = $this->isDateOnly() ? SRA_Controller::getAppTimeZone() : $this->_tz;
    $oldTz = SRA_TimeZone::getTzEnv();
    SRA_TimeZone::setTzEnvVar($tz);
    $timestamp = mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day, $this->_year);
    SRA_TimeZone::setTzEnvVar($oldTz);
    return $timestamp;
  }
  // }}}
  
  // {{{ getYear
  /**
   * returns the date year
   * @access public
   * @return int
   */
  function getYear() {
    return $this->_year;
  }
  // }}}
  
  // {{{ isDateOnly
  /**
   * returns TRUE if this gregorian date object represents ONLY a date (time is 
   * irrelevant)
   * @access public
   * @return boolean
   */
  function isDateOnly() {
    return $this->_dateOnly;
  }
  // }}}
  
  // {{{ isDstOverlap
  /**
   * returns TRUE if this date/time is during the dst rollback overlap period 
   * (i.e. the 2nd 1:00 hour on 11/2/2008)
   * @access public
   * @return boolean
   */
  function isDstOverlap() {
    if (!$this->isDateOnly() && $this->_dstOverlap && SRA_GregorianDate::isValid($end = $this->_tz->getDaylightSavingsEnd($this)) && $this->format('YmdH') == $end->format('YmdH')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ isFuture
  /**
   * returns TRUE if this date is in the future (after the current date)
   * @access	public
   * @return	boolean
   */
  function isFuture() {
    $today = new SRA_GregorianDate();
    return $today->compare($this) < 0;
  }
  // }}}
  
  // {{{ isInDaylightSavings
  /**
   * returns TRUE if this date object uses a timezone that applies daylight 
   * savings and the current date is within the daylight savings time period. 
   * applies only to non-dateOnly objects
   * @access public
   * @return boolean
   */
  function isInDaylightSavings() {
    return $this->_tz->isDaylightSavings($this);
  }
  // }}}
  
  // {{{ isLeapYear
  /**
   * returns TRUE if this date object year is a leap year OR if $year is a leap 
   * year (when invoked statically), FALSE otherwise
   * @param int $year the year. the current date year used if not specified
   * @access public OR public static
   * @return boolean
   */
  function isLeapYear($year=NULL) {
    $year = $year ? $year : $this->_year;
    return is_numeric($year) && ($year%4) == 0 && (($year%100) != 0 || ($year%400) == 0);
  }
  // }}}
  
  // {{{ isPast
  /**
   * returns TRUE if this date is in the past (prior to the current date)
   * @access	public
   * @return	boolean
   */
  function isPast() {
    $today = new SRA_GregorianDate();
    return $today->compare($this) > 0;
  }
  // }}}
  
  // {{{ isToday
  /**
   * returns TRUE if this date is today
   * @access	public
   * @return	boolean
   */
  function isToday() {
    $today = new SRA_GregorianDate(NULL, $this->_tz);
    return $today->format('Ymd') == $this->format('Ymd');
  }
  // }}}
  
  // {{{ isTommorow
  /**
   * returns TRUE if this date is today
   * @access	public
   * @return	boolean
   */
  function isTomorrow() {
    $today = new SRA_GregorianDate(NULL, $this->_tz);
    return ($today->format('Ymd') + 1) == $this->format('Ymd');
  }
  // }}}
  
  // {{{ isYesterday
  /**
   * returns TRUE if this date is today
   * @access	public
   * @return	boolean
   */
  function isYesterday() {
    $today = new SRA_GregorianDate(NULL, $this->_tz);
    return ($today->format('Ymd') - 1) == $this->format('Ymd');
  }
  // }}}
  
  // {{{ isTodayOrFuture
  /**
   * returns TRUE if this date is today or a future date
   * @access	public
   * @return	boolean
   */
  function isTodayOrFuture() {
    return $this->isToday() || $this->isFuture();
  }
  // }}}
  
  // {{{ isWeekday
  /**
   * returns TRUE if this date is a weekday (M-F)
   * @access	public
   * @return	boolean
   */
  function isWeekday() {
    $d = $this->format('w');
    return $d > 0 && $d < 6;
  }
  // }}}
  
  // {{{ isValid
  /**
   * static method that returns true if $object is a SRA_GregorianDate instance
   * @param object $object the object to evaluate
   * @access public
   * @return boolean
   */
  function isValid( &$object ) {
    return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_gregoriandate');
  }
  // }}}
  
  // {{{ jump
  /**
   * used to move this date from one date to another based on the $quantity and 
   * $unit specified. for example: advance date forward by 1 week, or back by 
   * 1 month. appropriate adjustments to all of this date's data attributes will 
   * be made (example: if jumping 1 month forward starting in December, the year 
   * will automatically be advanced). IF the $unit is 
   * SRA_GREGORIAN_DATE_UNIT_MONTH, and the current day is greater than the # of 
   * days in the new month, the current day will be changed to the last day of 
   * that month. For example, if jumping 1 month forward from 1/31, the new date 
   * would be 2/28 (2/29 for leap years). returns TRUE on success
   * @param int $unit the unit of measure. this value MUST correspond with one 
   * of the SRA_GREGORIAN_DATE_UNIT_* constants. $unit can also be a combination 
   * of a number of unit designator where the supported unit designators are the
   * following:
   *   s: second
   *   i: minute
   *   h: hour
   *   d: day
   *   w: week
   *   m: month
   *   y: year
   * @param int $quantity the quantity of $unit to jump. if not specified, 1 
   * will be assumed
   * @access public
   * @return boolean
   */
  function jump($unit, $quantity=1) {
    // convert string-based unit (e.g. 1d, 3w)
    if ($unit && !is_numeric($unit) && in_array($u = strtolower(substr($unit, -1)), array('s', 'i', 'h', 'd', 'w', 'm', 'y'))) {
      $quantity = strlen($unit) > 1 ? substr($unit, 0, strlen($unit) - 1) : 1;
      switch($u) {
        case 's':
          $unit = SRA_GREGORIAN_DATE_UNIT_SECOND;
          break;
        case 'i':
          $unit = SRA_GREGORIAN_DATE_UNIT_MINUTE;
          break;
        case 'h':
          $unit = SRA_GREGORIAN_DATE_UNIT_HOUR;
          break;
        case 'd':
          $unit = SRA_GREGORIAN_DATE_UNIT_DAY;
          break;
        case 'w':
          $unit = SRA_GREGORIAN_DATE_UNIT_WEEK;
          break;
        case 'm':
          $unit = SRA_GREGORIAN_DATE_UNIT_MONTH;
          break;
        case 'y':
          $unit = SRA_GREGORIAN_DATE_UNIT_YEAR;
          break;
      }
    }
    
    if ($unit == SRA_GREGORIAN_DATE_UNIT_DAY || $unit == SRA_GREGORIAN_DATE_UNIT_MINUTE || 
        $unit == SRA_GREGORIAN_DATE_UNIT_HOUR || $unit == SRA_GREGORIAN_DATE_UNIT_SECOND || 
        $unit == SRA_GREGORIAN_DATE_UNIT_WEEK || $unit == SRA_GREGORIAN_DATE_UNIT_MONTH || 
        $unit == SRA_GREGORIAN_DATE_UNIT_YEAR) {
      switch ($unit) {
        case SRA_GREGORIAN_DATE_UNIT_DAY:
          for($i=0; $i<abs($quantity); $i++) {
            $this->_day += $quantity > 0 ? 1 : -1;
            if ($this->_day == 0) {
              $this->_day = 32;
              $this->jump(SRA_GREGORIAN_DATE_UNIT_MONTH, -1);
            }
            else if ($this->_day > $this->getNumDaysInMonth()) {
              $this->_day = 1;
              $this->jump(SRA_GREGORIAN_DATE_UNIT_MONTH);
            }
          }
          break;
        case SRA_GREGORIAN_DATE_UNIT_MINUTE: 
          $this->_minute += $quantity;
          $hours = 0;
          while($this->_minute < 0 || $this->_minute > 59) {
            $hours += $this->_minute < 0 ? -1 : 1;
            $this->_minute += $this->_minute < 0 ? 60 : -60;
          }
          if ($hours !== 0) { $this->jump(SRA_GREGORIAN_DATE_UNIT_HOUR, $hours); }
          break;
        case SRA_GREGORIAN_DATE_UNIT_HOUR: 
          $this->_hour += $quantity;
          $days = 0;
          while($this->_hour < 0 || $this->_hour > 23) {
            $days += $this->_hour < 0 ? -1 : 1;
            $this->_hour += $this->_hour < 0 ? 24 : -24;
          }
          if ($days !== 0) { $this->jump(SRA_GREGORIAN_DATE_UNIT_DAY, $days); }
          break;
        case SRA_GREGORIAN_DATE_UNIT_SECOND: 
          $this->_second += $quantity;
          $minutes = 0;
          while($this->_second < 0 || $this->_second > 59) {
            $minutes += $this->_second < 0 ? -1 : 1;
            $this->_second += $this->_second < 0 ? 60 : -60;
          }
          if ($minutes !== 0) { $this->jump(SRA_GREGORIAN_DATE_UNIT_MINUTE, $minutes); }
          break;
        case SRA_GREGORIAN_DATE_UNIT_WEEK:
          $this->jump(SRA_GREGORIAN_DATE_UNIT_DAY, 7 * $quantity);
          break;
        case SRA_GREGORIAN_DATE_UNIT_MONTH: 
          $this->_month += $quantity;
          $years = 0;
          while ($this->_month < 1 || $this->_month > 12) {
            $years += $this->_month < 1 ? -1 : 1;
            $this->_month += $this->_month < 1 ? 12 : -12;
          }
          if ($years !== 0) { $this->jump(SRA_GREGORIAN_DATE_UNIT_YEAR, $years); }
          
          // if current day is now > number of days in the new month, change the 
          // day to the last day of the month
          if ($this->_day > $this->getNumDaysInMonth()) { $this->_day = $this->getNumDaysInMonth(); }
          break;
        case SRA_GREGORIAN_DATE_UNIT_YEAR: 
          $this->setYear($this->_year + $quantity);
          break;
      }
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpTo
  /**
   * used to jump to the 'nth' 'day' of a month. example: the first sunday of 
   * march
   * @param int $dow the day of week to jump to (0=sunday, 6=saturday)
   * @param int $week the week jump to. default is 1. example: 1=1st $dow of 
   * $month - ($dow=0, $week=1, $month=3 would be the first Sunday of March). 
   * $week MUST be between 1 and 5 and the jump will NEVER go into the next 
   * month. so to jump to the last week of $month, simply set $week=5
   * @param int $month an optional month. if not specified, the current month 
   * will be used
   * @access public
   * @return boolean
   */
  function jumpTo($dow, $week=1, $month=NULL) {
    if (is_numeric($dow) && $dow >= 0 && $dow <= 6 && is_numeric($week) && $week >= 0 && $week <= 5 && (!isset($month) || (is_numeric($month) && $month >= 1 && $month <=12))) {
      $month = $month ? $month : $this->_month;
      $this->setMonth($month);
      $this->setDay(1);
      while($this->getDayOfWeek() != $dow) { $this->jump(SRA_GREGORIAN_DATE_UNIT_DAY); }
      for($i=1; $i<$week; $i++) { $this->jump(SRA_GREGORIAN_DATE_UNIT_WEEK); }
      // backtrack if month was past
      while($this->getMonth() != $month) { $this->jump(SRA_GREGORIAN_DATE_UNIT_WEEK, -1); }
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpToEndOfDay
  /**
   * used to jump to end of the current day
   * @access public
   * @return boolean
   */
  function jumpToEndOfDay() {
    if (!$this->isDateOnly()) {
      $this->setHour(23);
      $this->jumpToEndOfHour();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpToStartOfDay
  /**
   * used to jump to start of the current day
   * @access public
   * @return boolean
   */
  function jumpToStartOfDay() {
    if (!$this->isDateOnly()) {
      $this->setHour(0);
      $this->jumpToStartOfHour();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpToEndOfHour
  /**
   * used to jump to end of the current hour
   * @access public
   * @return boolean
   */
  function jumpToEndOfHour() {
    if (!$this->isDateOnly()) {
      $this->setMinute(59);
      $this->setSecond(59);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpToStartOfHour
  /**
   * used to jump to start of the current hour
   * @access public
   * @return boolean
   */
  function jumpToStartOfHour() {
    if (!$this->isDateOnly()) {
      $this->setMinute(0);
      $this->setSecond(0);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ jumpToEndOfMonth
  /**
   * used to jump to end of the current month
   * @access public
   * @return boolean
   */
  function jumpToEndOfMonth() {
    $this->setDay($this->getNumDaysInMonth());
    $this->jumpToEndOfDay();
    return TRUE;
  }
  // }}}
  
  // {{{ jumpToStartOfMonth
  /**
   * used to jump to start of the current month
   * @access public
   * @return boolean
   */
  function jumpToStartOfMonth() {
    $this->setDay(1);
    $this->jumpToStartOfDay();
    return TRUE;
  }
  // }}}
  
  // {{{ jumpToEndOfYear
  /**
   * used to jump to end of the current year
   * @access public
   * @return boolean
   */
  function jumpToEndOfYear() {
    $this->setMonth(12);
    $this->jumpToEndOfMonth();
    return TRUE;
  }
  // }}}
  
  // {{{ jumpToStartOfYear
  /**
   * used to jump to start of the current year
   * @access public
   * @return boolean
   */
  function jumpToStartOfYear() {
    $this->setMonth(1);
    $this->jumpToStartOfMonth();
    return TRUE;
  }
  // }}}
  
  // {{{ jumpToEndOfWeek
  /**
   * used to jump to end of the current week (Saturday)
   * @access public
   * @return boolean
   */
  function jumpToEndOfWeek() {
    $this->setDayOfWeek(6);
    $this->jumpToEndOfDay();
    return TRUE;
  }
  // }}}
  
  // {{{ jumpToStartOfWeek
  /**
   * used to jump to start of the current week (Sunday)
   * @access public
   * @return boolean
   */
  function jumpToStartOfWeek() {
    $this->setDayOfWeek(0);
    $this->jumpToStartOfDay();
    return TRUE;
  }
  // }}}
  
  // {{{ newInstanceFromNtp
  /**
   * uses an NTP server to instantiate a new SRA_GregorianDate object. returns 
   * NULL if $ntpServer could not be queried
   * @param string $ntpServer the ntp server to use
   * @param int $timeout the # of seconds to wait to establish a connection with
   * $ntpServer. default is 3 seconds
   * @param int $port the port to use to connect to $ntpServer (defaults to the 
   * standard ntp port 13)
   * @access public
   * @return SRA_GregorianDate
   */
  function &newInstanceFromNtp($ntpServer, $timeout = 3, $port = 123) {
    if ($fp = @fsockopen('udp://' . $ntpServer, $port, $errno, $errstr, $timeout)) {
      @socket_set_timeout($fp, $timeout);
      fputs($fp, "\n");
      $reply  = fread($fp,1);
      $bytes = stream_get_meta_data($fp);
      $reply .= fread($fp,$bytes['unread_bytes']);
      fclose($fp);
      return $reply;
    }
    return NULL;
    $ntpDate = NULL;
    $start = time();
    if ($fp = @fsockopen($ntpServer, $port, $errno, $errstr, $timeout)) {
      $timeStr = fread($fp, 50);
      if (preg_match('/([0-9]{2}.*[0-9]{2}:[0-9]{2}:[0-9]{2}).*([a-zA-Z]{3})/', $timeStr, $m)) {
        include_once('util/l10n/SRA_Country.php');
        $country = new SRA_Country();
        $tz = NULL;
        foreach(array_keys($timezones =& $country->getTimeZones()) as $key) {
          if ($timezones[$key]->_abbr == trim($m[2]) || $timezones[$key]->_abbrDst == trim($m[2]) || $timezones[$key]->getId() == trim($m[2])) {
            $tz =& $timezones[$key];
            break;
          }
        }
        $ntpDate = new SRA_GregorianDate($m[1], $tz);
        $ntpDate->setTimeZone(SRA_Controller::getAppTimeZone());
      }
      fclose($fp);
    }
    return $ntpDate;
  }
  // }}}
  
  // {{{ parseString
  /**
   * this method can be used to parse a string replacing inbedded date time tags 
   * as necessary. the tags are replaced according to the SRA_GregorianDate 
   * object properties. the possible tags are those listed in the php date 
   * function api. tags must be imbedded as follows:
   *
   * {DTR_"format string"(+/-quantity)(SECOND|MINUTE|HOUR|DAY|WEEK|MONTH|YEAR)}
   *
   * multiple imbedded tags may exist.
   *
   * so, for example, to add next year to a string you would need to have the 
   * following tag imbedded in that string: {DTR_"Y"(+1)(YEAR)}
   * @param string $string the string to parse
   * @access public
   * @return string
   */
  function parseString($string) {
    $full=false;
    $dateTime=$this->copy();
    if (preg_match("'\{ *DTR_(\"|\')(.*?)(\"|\') *\((\+|\-)(.*?)\) *\((SECOND|MINUTE|HOUR|DAY|WEEK|MONTH|YEAR)\) *\}'si", $string, $matches)) {
      $full=true;
      $increment=0;
      $replace="";
      if (count($matches)==7) {
        $increment=$matches[5];
        if ($matches[4]=="-") { $increment*=-1; }
        switch (strtoupper($matches[6])) {
          case 'SECOND':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_SECOND, $increment);
            break;
          case 'MINUTE':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_MINUTE, $increment);
            break;
          case 'HOUR':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_HOUR, $increment);
            break;
          case 'DAY':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_DAY, $increment);
            break;
          case 'WEEK':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_WEEK, $increment);
            break;
          case 'MONTH':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_MONTH, $increment);
            break;
          case 'YEAR':
            $dateTime->jump(SRA_GREGORIAN_DATE_UNIT_YEAR, $increment);
            break;
        }
      }
    }
    if (count($matches)==7 || preg_match("'\{ *DTR_(\"|\')(.*?)(\"|\') *\}'si", $string, $matches)) {
      if (count($matches)>2) {
        $replace=$dateTime->format($matches[2]);
      }
      if ($full) {
        $string=preg_replace("'\{ *DTR_(\"|\')(.*?)(\"|\') *\((\+|\-)(.*?)\) *\((SECOND|MINUTE|HOUR|DAY|WEEK|MONTH|YEAR)\) *\}'si", $replace, $string, 1);
      }
      else {
        $string=preg_replace("'\{ *DTR_(\"|\')(.*?)(\"|\') *\}'si", $replace, $string, 1);
      }
      return $this->parseString($string);
    }
    else {
      return $string;
    }
  }
  // }}}
  
  // {{{ setAppTimeZone
  /**
   * this method converts the date's time zone to the currently selected app 
   * time zone (if an app is currently selected)
   * @param boolean $convert whether or not to adjust this date's time by the 
   * difference in offsets between the current time zone and the new time zone. 
   * default is TRUE
   * @access public
   * @return boolean
   */
  function &setAppTimeZone($convert=TRUE) {
    return $this->setTimeZone(SRA_Controller::getAppTimeZone(), $convert);
  }
  // }}}
  
  // {{{ setDateOnly
  /**
   * sets whether or not this gregorian date object represents ONLY a date. 
   * returns TRUE if the value was changed. hour, minute and second will default 
   * to 0 and the current app (or system) time zone will be set when a date is 
   * changed from dateOnly=TRUE to dateOnly=FALSE. these values will be set to 
   * NULL when the opposite change is made
   * @param boolean $dateOnly the value to set
   * @access public
   * @return boolean
   */
  function setDateOnly($dateOnly) {
    if ($dateOnly != $this->_dateOnly) {
      if (!$dateOnly) {
        $this->_hour = 0;
        $this->_minute = 0;
        $this->_second = 0;
        $this->_tz =& SRA_Controller::getAppTimeZone();
      }
      else {
        $this->_hour = NULL;
        $this->_minute = NULL;
        $this->_second = NULL;
        $this->_tz = NULL;
      }
      $this->_dateOnly = $dateOnly;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setDay
  /**
   * sets the date month day. returns TRUE if the value was changed. Note: IF 
   * $day exceeds the # of days in the current month, the day will be changed 
   * to the max # of days in that month instead. example: if the month was 
   * February and $day is 31, the day that is actually set would be 28 (or 29 
   * for a leap year)
   * @param int $day the day to set (1-31)
   * @access public
   * @return boolean
   */
  function setDay($day) {
    if ($day != $this->_day && $day >= 1 && $day <= 32) {
      $day = $day > $this->getNumDaysInMonth() ? $this->getNumDaysInMonth() : $day;
      $this->_day = $day*1;
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setDayOfWeek
  /**
   * sets the day of the current week. returns TRUE on success
   * @param int $day the day of week to set (0=Sun, 6=Sat)
   * @access public
   * @return boolean
   */
  function setDayOfWeek($dow) {
    if ($dow >= 0 && $dow <= 6) {
      $cdow = $this->getDayOfWeek();
      while($dow != $this->getDayOfWeek()) {
        $this->jump(SRA_GREGORIAN_DATE_UNIT_DAY, $cdow > $dow ? -1 : 1);
      }
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setDstOverlap
  /**
   * sets the _dstOverlap attribute
   * @param boolean $dstOverlap the overlap value to set
   * @access public
   * @return boolean
   */
  function setDstOverlap($dstOverlap) {
    $this->_dstOverlap = $dstOverlap;
    return TRUE;
  }
  // }}}
  
  // {{{ setHour
  /**
   * sets the date hour. returns TRUE if the value was changed. applies only 
   * to non-dateOnly dates
   * @param int $hour the hour to set (0-23)
   * @access public
   * @return boolean
   */
  function setHour($hour) {
    if (!$this->isDateOnly() && $hour != $this->_hour && $hour >= 0 && $hour <= 23) {
      $this->_hour = $hour*1;
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setMinute
  /**
   * sets the date minute. returns TRUE if the value was changed. applies only 
   * to non-dateOnly dates
   * @param int $minute the minute to set (0-59)
   * @access public
   * @return boolean
   */
  function setMinute($minute) {
    if (!$this->isDateOnly() && $minute != $this->_minute && $minute >= 0 && $minute <= 59) {
      $this->_minute = $minute*1;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setMonth
  /**
   * sets the date month. returns TRUE if the value was changed. if the current 
   * day exceeds the max # of days in the new month, the current day will be 
   * changed to the last day of the current month
   * @param int $month the month to set (1-12)
   * @access public
   * @return boolean
   */
  function setMonth($month) {
    if ($month != $this->_month && $month >= 1 && $month <= 12) {
      $this->_month = $month*1;
      if ($this->_day > $this->getNumDaysInMonth()) { $this->_day = $this->getNumDaysInMonth(); }
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setSecond
  /**
   * sets the date second. returns TRUE if the value was changed. applies only 
   * to non-dateOnly dates
   * @param int $second the second to set (0-59)
   * @access public
   * @return boolean
   */
  function setSecond($second) {
    if (!$this->isDateOnly() && $second != $this->_second && $second >= 0 && $second <= 59) {
      $this->_second = $second*1;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setTimeZone
  /**
   * sets the date time zone. returns TRUE if the value was changed and set 
   * successfully. applies only to non-dateOnly dates
   * @param SRA_TimeZone $tz the new time zone to use
   * @param boolean $convert whether or not to adjust this date's time by the 
   * difference in offsets between the current time zone and the new time zone. 
   * default is TRUE
   * @access public
   * @return boolean
   */
  function setTimeZone(&$tz, $convert=TRUE) {
    if (!$this->isDateOnly() && SRA_TimeZone::isValid($tz) && !$tz->equals($this->_tz)) {
      if ($convert && SRA_TimeZone::isValid($this->_tz)) {
        $offset = $tz->getGmtOffset($this) - $this->_tz->getGmtOffset($this);
        if (!$offset) $offset = $tz->getGmtOffset($this) - $this->_tz->getGmtOffset();
        $this->jump(SRA_GREGORIAN_DATE_UNIT_MINUTE, $offset);
      }
      $this->_tz =& $tz;
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setUnixTimeStamp
  /**
   * sets the values for this timestamp using a unix $timestamp
   * @param int $timestamp the timestamp to set
   * @access public
   * @return boolean
   */
  function setUnixTimeStamp($timestamp) {
    if ($timestamp) {
      $this->setYear(date('Y', $timestamp));
      $this->setMonth(date('n', $timestamp));
      $this->setDay(date('j', $timestamp));
      if (!$this->isDateOnly()) {
        $tz =& $this->getTimeZone();
        $oldTz = SRA_TimeZone::getTzEnv();
        SRA_TimeZone::setTzEnvVar($tz);
        $this->setHour(date('G', $timestamp));
        $this->setMinute(date('i', $timestamp));
        $this->setSecond(date('s', $timestamp));
        SRA_TimeZone::setTzEnvVar($oldTz);
      }
      $this->validateDst();
      return TRUE;
    }
    return FALSE;
  }
  // }}}
  
  // {{{ setYear
  /**
   * sets the date year. returns TRUE if the value was changed. if the current 
   * day exceeds the max # of days in the new month, the current day will be 
   * changed to the last day of the current month (applies only when changing 
   * year from a leap year to a non leap year when date is 2/29 in the original 
   * year)
   * @param int $year the year to set (YY or YYYY). year must be greater than 
   * 1752 when the gregorian calendar was official adopted. if $year is only 2 
   * characters, they will be prefixed with the current century (i.e. 06 will be 
   * prefixed will '20')
   * @access public
   * @return boolean
   */
  function setYear($year) {
    if (strlen($year) == 2) { $year = substr(date('Y'), 0, 2) * 1; }
    if ($year != $this->_year && $year >= 1752 && $year <= 9999) {
      $this->_year = $year*1;
      if ($this->_day > $this->getNumDaysInMonth()) { $this->_day = $this->getNumDaysInMonth(); }
      $this->validateDst();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ setYmd
  /**
   * shortcut method to set all (or at least 1), year, month, and/or day. 
   * returns TRUE if any value was set
   * @param int $year the year to set
   * @param int $month the month to set
   * @param int $day the day to set
   * @access public
   * @return boolean
   */
  function setYmd($year=NULL, $month=NULL, $day=NULL) {
    if ($year) { $ret1 = $this->setYear($year); }
    if ($month) { $ret2 = $this->setMonth($month); }
    if ($day) { $ret3 = $this->setDay($day); }
    $this->validateDst();
    return $ret1 || $ret2 || $ret3;
  }
  // }}}
  
  // {{{ toInt
  /**
   * converts the date portion of this date instance to an integer value that 
   * can be used to compare it with other dates. please note: unlike unix 
   * timestamps, the values in this integer do not correspond with seconds, 
   * rather it is a numeric representation of the year/month/day values in the 
   * following format: YYYYMMDD. this method utilizes the GMT timezone
   * @access public
   * @return int
   */
  function toInt() {
    $tz =& $this->_tz;
    $this->setTimeZone(SRA_TimeZone::getTimeZone(SRA_TIME_ZONE_GMT));
    $val = ($this->getYear() . $this->getMonth(TRUE) . $this->getDay(TRUE)) * 1;
    $this->setTimeZone($tz);
    return $val;
  }
  // }}}
  
  // {{{ toIntDateTime
  /**
   * converts this date and time for this date to an integer value that 
   * can be used to compare it with other dates. please note: unlike unix 
   * timestamps, the values in this integer do not correspond with seconds, 
   * rather it is a numeric representation of the 
   * year/month/day/hour/minute/second values in the following format: 
   * YYYYMMDDHHIISS. this method utilizes the GMT timezone
   * @access public
   * @return int
   */
  function toIntDateTime() {
    if ($this->isDateOnly()) {
      $this->setDateOnly(FALSE);
      $resetDateOnly = TRUE;
    }
    $tz =& $this->_tz;
    $this->setTimeZone(SRA_TimeZone::getTimeZone(SRA_TIME_ZONE_GMT));
    $val = ($this->getYear() . $this->getMonth(TRUE) . $this->getDay(TRUE) . $this->getHour(TRUE) . $this->getMinute(TRUE) . $this->getSecond(TRUE)) * 1;
    $this->setTimeZone($tz);
    if ($resetDateOnly) { $this->setDateOnly(TRUE); }
    return $val;
  }
  // }}}
  
  // {{{ toIntTime
  /**
   * converts the time portion of this date instance to an integer value that 
   * can be used to compare it with other dates. please note: unlike unix 
   * timestamps, the values in this integer do not correspond with seconds, 
   * rather it is a numeric representation of the hour/minute/second values in 
   * the following format: HHMMSS. 0 is returns for dateOnly type dates. this 
   * method utilizes the GMT timezone
   * @access public
   * @return int
   */
  function toIntTime() {
    $tz =& $this->_tz;
    $this->setTimeZone(SRA_TimeZone::getTimeZone(SRA_TIME_ZONE_GMT));
    $val = !$this->isDateOnly() ? ($this->getHour(TRUE) . $this->getMinute(TRUE) . $this->getSecond(TRUE)) * 1 : 0;
    $this->setTimeZone($tz);
    return $val;
  }
  // }}}
  
  // {{{ toString
  /**
   * returns this date formatted using the app-config date/time format or 
   * $this->toStringFormat if member attribute has been set
   * @param boolean $dateOnly optional parameter allowing the output to be 
   * either the $dateOnly based format (when 1 or TRUE), or the full timestamp 
   * format (when 0 or FALSE). if not specified (NULL), the format used will be 
   * based on the _dateOnly flag for this instance
   * @access public
   * @return string
   */
  function toString($dateOnly=NULL) {
    return $this->format($dateOnly === TRUE || $dateOnly === 1 ? SRA_Controller::getAppDateOnlyFormat() : ($dateOnly === FALSE || $dateOnly === 0 ? SRA_Controller::getAppDateFormat() : $this->toStringFormat));
  }
  // }}}
  
  // {{{ validateDst
  /**
   * this method advances this date/time object by 1 hour if it used a dst 
   * timezone and the time it represents resides in one of the non-existent 
   * dst times (i.e. 3/9/2008 2:00 AM will become 3/9/2008 3:00 AM for the 
   * America/Denver timezone). returns TRUE if the time was changed, false 
   * otherwise
   * @access public
   * @return boolean
   */
  function validateDst() {
    if (!$this->_init && !$this->_dateOnly && ($end = $this->_tz->getDaylightSavingsStart($this->getYear())) && $this->format('YmdH') == ($end->format('YmdH') - 1)) {
      $this->_hour++;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ validateNtpServer
  /**
   * validates an ntp server using ntpq -p
   * @param string $server the server to validate
   * @access public
   * @return boolean
   */
  function validateNtpServer($server) {
    return ($ntpq = SRA_File::findInPath('ntpq', array('/sbin', '/usr/sbin'))) && preg_match('/jitter/', shell_exec("$ntpq -p -c \"timeout 1000\" $server")) ? TRUE : FALSE;
  }
  // }}}
  
}
// }}}
?>
