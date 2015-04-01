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
 * debug flag (debug message are output to error logging)
 * @type boolean
 */
define('SRA_CACHE_DEBUG', FALSE);

/**
 * the name of the garbage collection file
 * @type string
 */
define('SRA_CACHE_GC_FILE', '.sracachegc');

/**
 * the interval to use for cache garbage collection (seconds)
 * @type int
 */
define('SRA_CACHE_GC_INTERVAL', 30);

/**
 * the name of the garbage collection lock file
 * @type string
 */
define('SRA_CACHE_GC_LOCK', '.sracachegc.lck');

/**
 * the maximum amount of time that the garbage collector can be locked (seconds)
 * @type int
 */
define('SRA_CACHE_GC_MAX_LOCK', 300);

/**
 * the prefix to use for cache files, the application id will be added to this 
 * prefix at runtime
 * @type string
 */
define('SRA_CACHE_PREFIX', '.sracache-');
// }}}

/**
 * global variable used for cache
 * @type array
 */
$_sraCache = array();

// {{{ SRA_Cache
/**
 * this static class provides a means of caching both simple/scalar and 
 * complex/object variables. the variables are cached as a serialized value. 
 * when used in conjunction with sierra/tmp mounted as a ramdisk, the cache will 
 * essentially be stored in memory and thus very quickly accessible. this class 
 * also manages periodic cache garbage collection. If the PHP apc module is 
 * present, it's caching logic will be used - otherwise, the application's 
 * temp directory will be used
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_Cache {
  
  // {{{ cacheIsset
	/**
	 * returns TRUE if cache value is present
   * @param string $name the cache name value to check
   * @param boolean $modtime if TRUE and the cache exists, the cache file mod 
   * time will be returns instead of TRUE
	 * @access public
	 * @return mixed
	 */
  function cacheIsset($name, $modtime=FALSE) {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::cacheIsset - invoked for "' . $name . '"', __FILE__, __LINE__);
    
    // use APC if present
    if (!isset($argc) && function_exists('apc_exists')) return apc_exists($name);
    
    SRA_Cache::_garbageCollector();
    
    global $_sraCache;
    
    SRA_Cache::_cleanUpCache($name);
    
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::cacheIsset - cache value "' . $name . '" ' . (isset($_sraCache[$name]) ? ' found in global cache hash' : (file_exists($cfile = SRA_Cache::_getCacheFile($name, TRUE)) ? ' found in cache file ' . $cfile : ' not found')), __FILE__, __LINE__);
    
    if ($modtime && file_exists($cfile = SRA_Cache::_getCacheFile($name, TRUE))) {
      return filemtime($cfile);
    }
    else {
      return isset($_sraCache[$name]) || file_exists(SRA_Cache::_getCacheFile($name, TRUE));
    }
  }
  // }}}
  
  // {{{ deleteCache
	/**
	 * deletes the cache $name if it exists. returns TRUE if any cache was deleted
   * FALSE otherwise
   * @param string $name the cache name
	 * @access public
	 * @return boolean
	 */
  function deleteCache($name) {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::deleteCache - invoked for "' . $name . '"', __FILE__, __LINE__);
    
    // use APC if present
    if (!isset($argc) && function_exists('apc_delete')) return apc_delete($name);
    
    SRA_Cache::_garbageCollector();
    
    global $_sraCache;
    
    $ret = FALSE;
    if (isset($_sraCache[$name])) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::deleteCache - cache value "' . $name . '" deleted from global cache hash', __FILE__, __LINE__);
      unset($_sraCache[$name]);
      $ret = TRUE;
    }
    if (file_exists($cfile = SRA_Cache::_getCacheFile($name, TRUE))) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::deleteCache - cache value "' . $name . '" deleted from cache file ' . $cfile, __FILE__, __LINE__);
      SRA_File::unlink($cfile);
      $ret = TRUE;
    }
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::deleteCache - cache value "' . $name . '" ' . ($ret ? ' was deleted' : 'was not deleted'), __FILE__, __LINE__);
    
    return $ret;
  }
  // }}}
  
  // {{{ getCache
	/**
	 * returns an in memory cached value if it is present
   * @param string $name the cache name value to return
	 * @access public
	 * @return mixed
	 */
  function &getCache($name) {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::getCache - invoked for "' . $name . '"', __FILE__, __LINE__);
    
    // use APC if present
    if (!isset($argc) && function_exists('apc_fetch')) return apc_fetch($name);
    
    SRA_Cache::_garbageCollector();
    
    global $_sraCache;
    
    SRA_Cache::_cleanUpCache($name);
    
    if (isset($_sraCache[$name])) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::getCache - cache value "' . $name . '" found in global cache hash', __FILE__, __LINE__);
      
      return $_sraCache[$name]['val'];
    }
    else if (file_exists($cfile = SRA_Cache::_getCacheFile($name, TRUE))) {
      $_sraCache[$name] = array();
      if (SRA_Util::beginsWith(basename($cfile), SRA_CACHE_PREFIX . $name . '-') && ($ttl = str_replace(SRA_CACHE_PREFIX . $name . '-', '', basename($cfile)))) {
        $_sraCache[$name]['ttl'] = $ttl;
      }
      $_sraCache[$name]['val'] = unserialize(SRA_File::toString($cfile));
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::getCache - cache value "' . $name . '" found in cache file ' . $cfile, __FILE__, __LINE__);
      return $_sraCache[$name]['val'];
    }
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::getCache - cache value "' . $name . '" not found', __FILE__, __LINE__);
    return $nl = NULL;
  }
  // }}}
  
  // {{{ setCache
	/**
	 * used to set an in memory cache value. returns TRUE on success
   * @param string $name the cache name to assign
   * @param mixed $val the cache value
   * @param int $ttl an optional ttl for this cache value (# of seconds this 
   * cache should remain valid)
	 * @access public
	 * @return boolean
	 */
  function setCache($name, &$val, $ttl=NULL) {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::setCache - invoked for "' . $name . '" with value "' . $val . '" ' . ($ttl ? 'and ttl "' . $ttl . '"' : ' and no ttl'), __FILE__, __LINE__);
    
    // use APC if present
    if (!isset($argc) && function_exists('apc_store')) return apc_store($name, $val, $ttl);
    
    SRA_Cache::_garbageCollector();
    
    global $_sraCache;
    
    $_sraCache[$name] = array();
    $_sraCache[$name]['val'] =& $val;
    if ($ttl) $_sraCache[$name]['ttl'] = time() + $ttl;
    
    if (SRA_Cache::cacheIsset($name)) SRA_Cache::deleteCache($name);
    $ret = SRA_File::write($cfile = SRA_Cache::_getCacheFile($name, $ttl), serialize($val)) === TRUE;
    chmod($cfile, 0666);
    
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::setCache - cache value "' . $name . '" write to cache file ' . $cfile . ' was ' . ($ret ? 'successful' : 'unsuccessful'), __FILE__, __LINE__);
    
    return $ret;
  }
  // }}}
  
  // {{{ _cleanUpCache
	/**
	 * cleans up any expired cache
   * @param string $name the cache name
	 * @access public
	 * @return void
	 */
  function _cleanUpCache($name) {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_cleanUpCache - invoked for "' . $name . '"', __FILE__, __LINE__);
    
    global $_sraCache;
    
    $cfile = SRA_Cache::_getCacheFile($name, TRUE);
    
    // check memory cache
    if (isset($_sraCache[$name]) && (!file_exists($cfile) || (isset($_sraCache[$name]['ttl']) && time() > $_sraCache[$name]['ttl']))) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_cleanUpCache - cache value "' . $name . '" removed from global cache hash', __FILE__, __LINE__);
      unset($_sraCache[$name]);
    }
    // check file cache
    if (file_exists($cfile) && SRA_Util::beginsWith(basename($cfile), SRA_CACHE_PREFIX . $name . '-') && 
       ($ttl = str_replace(SRA_CACHE_PREFIX . $name . '-', '', basename($cfile))) && time() > $ttl) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_cleanUpCache - cache value "' . $name . '" cache file deleted', __FILE__, __LINE__);
      SRA_File::unlink($cfile);
    }
  }
  // }}}
  
  // {{{ _garbageCollector
	/**
	 * performs cache garbage collection
	 * @access public
	 * @return void
	 */
  function _garbageCollector() {
    if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_garbageCollector - invoked', __FILE__, __LINE__);
    
    if ((!file_exists($gcFile = SRA_Controller::getSysTmpDir() . '/' . SRA_CACHE_GC_FILE) || filemtime($gcFile) <= (time() - SRA_CACHE_GC_INTERVAL)) && 
        (!file_exists($lockFile = SRA_Controller::getSysTmpDir() . '/' . SRA_CACHE_GC_LOCK) || filemtime($lockFile) <= (time() - SRA_CACHE_GC_MAX_LOCK))) {
      if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_garbageCollector - checking for stale cache with GMT time ' . date('m/d/Y H:i:s'), __FILE__, __LINE__);
      fclose(fopen($lockFile, 'w'));
      fclose(fopen($gcFile, 'w'));
      chmod($lockFile, 0666);
      chmod($gcFile, 0666);
      
      foreach(SRA_File::getFileList(SRA_Controller::getSysTmpDir(), '/' . SRA_CACHE_PREFIX . '/') as $item) {
        $pieces = explode('-', basename($item));
        $ttl = $pieces[count($pieces) - 1];
        if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_garbageCollector - checking cache item "' . basename($item) . '"' . (is_numeric($ttl) ? ' set to be deleted on ' . date('m/d/Y H:i:s', $ttl) : ''), __FILE__, __LINE__);
        
        if (is_numeric($ttl) && time() > ($ttl*1)) {
          unlink($item);
          if (SRA_CACHE_DEBUG) SRA_Error::logError('SRA_Cache::_garbageCollector - cache item ' . basename($item) . ' deleted', __FILE__, __LINE__);
        }
        else if (SRA_CACHE_DEBUG) {
          SRA_Error::logError('SRA_Cache::_garbageCollector - cache item ' . basename($item) . ' will not be deleted', __FILE__, __LINE__);
        }
      }
      
      unlink($lockFile);
      
    }
    else if (SRA_CACHE_DEBUG) {
      SRA_Error::logError('SRA_Cache::_garbageCollector - will not run cache file: ' . (!file_exists($gcFile = SRA_Controller::getSysTmpDir() . '/' . SRA_CACHE_GC_FILE) || filemtime($gcFile) <= (time() - SRA_CACHE_GC_INTERVAL)) . '/lock file:' . (!file_exists($lockFile = SRA_Controller::getSysTmpDir() . '/' . SRA_CACHE_GC_LOCK) || filemtime($lockFile) <= (time() - SRA_CACHE_GC_MAX_LOCK)), __FILE__, __LINE__);
    }
  }
  // }}}
  
  // {{{ _getCacheFile
	/**
	 * returns the path to the cache file to use for the given parameters
   * @param string $name the cache name to assign
   * @param int $ttl an optional ttl for this cache value. set this parameter to 
   * TRUE if you want this method to return any existing instance of this cache 
   * file regardless of ttl (if a file exists, otherwise the name of the 
   * standard non-ttl file will be returned)
	 * @access public
	 * @return string
	 */
  function _getCacheFile($name, $ttl=NULL) {
    
    if (strlen($name) > 200) {
      $id = 0;
      foreach(preg_split('//', $name, -1) as $c) {
        if (trim($c)) {
          $id += ord($c);
        }
      }
      $name = $id;
    }
    
    $name = SRA_Controller::isAppInitialized() ? SRA_Controller::getCurrentAppId() . '-' . $name : $name;
    
    if ($ttl === TRUE) {
      if ($files = SRA_File::getFileList(SRA_Controller::getSysTmpDir(), '/' . SRA_CACHE_PREFIX . $name . '\-[0-9]{10}/')) {
        return $files[0];
      }
    }
    $cfile = SRA_Controller::getSysTmpDir() . '/' . SRA_CACHE_PREFIX . $name . ($ttl ? '-' . (time() + $ttl) : '');
    
    return $cfile;
  }
  // }}}
  
}
// }}}
?>
