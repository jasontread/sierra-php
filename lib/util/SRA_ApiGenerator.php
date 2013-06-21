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
require_once('SRA_Cache.php');
// }}}

// {{{ Constants
/**
 * the default cache TTL for api metadata
 * @type int
 */
define('SRA_API_GENERATOR_DEFAULT_CACHE_TTL', 300);

/**
 * space separated list of the names of the files that can be placed in package 
 * directories to produce package-level comments
 * @type string
 */
define('SRA_API_GENERATOR_PACKAGE_README_FILE', 'README .readme');
// }}}

// {{{ SRA_ApiGenerator
/**
 * utility classes that may be used to generate HTML formatted API documentation 
 * for PHP classes and DTDs. API documentation should adhere to the javadoc 
 * annotation style in order for this class to be able to properly extract the 
 * documentation. this class should be instantiated prior to invoking the 
 * 'render' method
 * @author  Jason Read <jason@idir.org>
 * @package sierra.util
 */
class SRA_ApiGenerator {
  
  /**
   * the # of seconds to allow the api generator to cache data related to 
   * generated the api. set to 0 for no caching
   * @type int
   *
   */
  var $_cache;
  
  /**
   * an optional CSS URI to link to each of the rendered pages
   * @type string
   *
   */
  var $_cssUri;
  
  /**
   * the absolute base path (or array of base paths) 
   * containing the sub-directories/packages, classes and DTDs for which the API 
   * documentation should be generated for. suffix a path with two underscore 
   * (__) if you want that directory itself to also be considered a package. 
   * only subdirectories containing at least 1 php or dtd file will be included 
   * in the package list
   * @type mixed
   *
   */
  var $_path;
  
  /**
   * whether or not the api documentation should be generated recursively
   * @type boolean
   *
   */
  var $_recursive;
  
  /**
   * if you have setup a rewrite rule for the script invoking this method, this 
   * parameter may be used to specify the URI of that script. Here is a what the 
   * rewrite rules should look like (where the rewrite URI is "/api")
   * @type string
   *
   */
  var $_rewriteUri;
  
  /**
   * an array of the names of directories that should not 
   * be included in the API documentation (including sub-directories)
   * @type array
   *
   */
  var $_skipDirs;
  
  /**
   * the title for this API documentation
   * @type string
   *
   */
  var $_title;
  
  
  // {{{ SRA_ApiGenerator
  /**
   * instantiates a new instance of the API generator with the parameters 
   * specified
   * @param string $title the title for this API documentation
   * @param mixed $path the absolute base path (or array of base paths) 
   * containing the sub-directories/packages, classes and DTDs for which the API 
   * documentation should be generated for. suffix a path with two underscore 
   * (__) if you want that directory itself to also be considered a package. 
   * only subdirectories containing at least 1 php or dtd file will be included 
   * in the package list
   * @param array $skipDirs an array of the names of directories that should not 
   * be included in the API documentation (including sub-directories)
   * @param string $cssUri an optional CSS URI to link to each of the rendered 
   * pages
   * @param boolean $recursive whether or not the api documentation should be 
   * generated recursively from $path
   */
  function SRA_ApiGenerator($title, $path, $skipDirs=NULL, $cssUri=NULL, $recursive=TRUE) {
    $this->_title = $title;
    $this->_path = is_array($path) ? $path : array($path);
    $this->_skipDirs = $skipDirs;
    $this->_cssUri = $cssUri;
    $this->_recursive = $recursive;
  }
  
  // {{{ generate
  /**
   * generates the api into the directory specified
   * @param string $path the path to the directory where the api should be 
   * generated to
   * @return boolean
   */
  function generate($path) {
    if (is_dir($path) && is_writable($path) && !SRA_File::getFileList($path)) {
      $tpl =& SRA_Controller::getSysTemplate();
      mkdir($path . '/classes');
      mkdir($path . '/dtds');
      mkdir($path . '/functions');
      mkdir($path . '/packages');
      
      echo "GENERATING HOME\n";
      ob_start();
      $this->render('/');
      SRA_File::write($path . '/index.html', ob_get_contents());
      ob_end_clean();
      echo "GENERATING PACKAGE LIST\n";
      $_GET = array('packagelist' => TRUE);
      ob_start();
      $this->render('/');
      SRA_File::write($path . '/packages.html', ob_get_contents());
      ob_end_clean();
      echo "GENERATING CLASS LIST\n";
      $tpl->assign('package', NULL);
      $_GET = array('classlist' => TRUE);
      ob_start();
      $this->render('/');
      SRA_File::write($path . '/classes.html', ob_get_contents());
      ob_end_clean();
      echo "GENERATING OVERVIEW\n";
      $_GET = array('overview' => TRUE);
      ob_start();
      $this->render('/');
      SRA_File::write($path . '/overview.html', ob_get_contents());
      ob_end_clean();
      foreach(array_keys($this->_getPackages()) as $package) {
        mkdir($path . '/packages/' . $package);
        echo "GENERATING $package CLASS LIST\n";
        $_GET = array('classlist' => TRUE, 'package' => $package);
        ob_start();
        $this->render('/');
        SRA_File::write($path . '/packages/' . $package . '/classes.html', ob_get_contents());
        ob_end_clean();
        
        echo "GENERATING $package PACKAGE OVERVIEW\n";
        $_GET = array('package' => $package);
        ob_start();
        $this->render('/');
        SRA_File::write($path . '/packages/' . $package . '.html', ob_get_contents());
        ob_end_clean();
      }
      foreach(array_keys($this->_getClasses()) as $class) {
        echo "GENERATING $class CLASS SUMMARY\n";
        $_GET = array('class' => $class);
        ob_start();
        $this->render('/');
        SRA_File::write($path . '/classes/' . $class . '.html', ob_get_contents());
        ob_end_clean();
      }
      foreach(array_keys($this->_getDtds()) as $dtd) {
        echo "GENERATING $dtd DTD SUMMARY\n";
        $_GET = array('dtd' => $dtd);
        ob_start();
        $this->render('/');
        SRA_File::write($path . '/dtds/' . $dtd . '.html', ob_get_contents());
        ob_end_clean();
      }
      foreach(array_keys($this->_getFunctions()) as $func) {
        echo "GENERATING $func FUNCTION SUMMARY\n";
        $_GET = array('function' => $func);
        ob_start();
        $this->render('/');
        SRA_File::write($path . '/functions/' . $func . '.html', ob_get_contents());
        ob_end_clean();
      }
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  // }}}
  
  // {{{ render
  /**
   * use this method to generate API documentation dynamically. to do so, simply 
   * include this class into your web accessible PHP script, and invoke this 
   * method when the script is requested
   * @param string $rewriteUri if you have setup a rewrite rule for the script 
   * invoking this method, this parameter may be used to specify the URI of that 
   * script. Here is a what the rewrite rules should look like (where the 
   * rewrite URI is "/api") in .htaccess if this feature is used (otherwise, 
   * ugly URLs will be used)... substitute [api-script] with the name of your 
   * script:
   * RewriteEngine On
   * RewriteRule ^api/classes/(.*).html /[api-script].php?class=$1
   * RewriteRule ^api/classes.html /[api-script].php?classlist
   * RewriteRule ^api/dtds/(.*).html /[api-script].php?dtd=$1
   * RewriteRule ^api/functions/(.*).html /[api-script].php?function=$1
   * RewriteRule ^api/packages/(.*)/classes.html /[api-script].php?classlist&package=$1
   * RewriteRule ^api/packages/(.*).html /[api-script].php?package=$1
   * RewriteRule ^api/packages.html /[api-script].php?packagelist
   * RewriteRule ^api/overview.html /[api-script].php?overview
   * RewriteRule ^api/ /[api-script].php
   * @param int $cache the # of seconds to allow the api generator to cache 
   * data related to generated the api. set to 0 for no caching
   * @return void
   */
   function render($rewriteUri=NULL, $cache=SRA_API_GENERATOR_DEFAULT_CACHE_TTL) {
     if ($rewriteUri === TRUE) $rewriteUri = '/';
     
     $this->_rewriteUri = $rewriteUri;
     $this->_cache = $cache;
     
     $tpl =& SRA_Controller::getSysTemplate();
     $tpl->assign('title', $this->_title);
     $tpl->assignByRef('apiresources', SRA_ResourceBundle::getBundle('api'));
     $tpl->assignByRef('apigenerator', $this);
     $tpl->assign($this->_rewriteUri == '/' ? 'rewriteBase' : ($this->_rewriteUri ? 'rewriteUri' : 'uri'), $this->_rewriteUri == '/' ? TRUE : ($this->_rewriteUri ? $this->_rewriteUri : $_SERVER['SCRIPT_NAME']));
     
     if ($this->_cssUri) $tpl->assign('cssUri', $this->_cssUri);
     
     // create frames
     if (!count($_GET)) {
       $tpl->display('api/index.tpl');
     }
     // package list (upper left)
     else if (isset($_GET['packagelist'])) {
       $tpl->assign('packages', array_keys($this->_getPackages()));
       $tpl->display('api/package-list.tpl');
     }
     // class list (lower left)
     else if (isset($_GET['classlist'])) {
       if (isset($_GET['package'])) {
         $tpl->assign('package', $_GET['package']);
         $packages = $this->_getPackages();
         if (isset($packages[$_GET['package']])) {
           $classes = $this->_getClasses(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']]));
           $constants = $this->_getConstants(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']]));
           $dtds = $this->_getDtds(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']]));
           $functions = $this->_getFunctions(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']]));
         }
       }
       else {
         $classes = $this->_getClasses(TRUE);
         $constants = $this->_getConstants(TRUE);
         $dtds = $this->_getDtds(TRUE);
         $functions = $this->_getFunctions(TRUE);
       }
       $tpl->assign('classes', $classes);
       $tpl->assign('constants', $constants ? count($constants) : 0);
       $tpl->assign('dtds', $dtds);
       $tpl->assign('functions', $functions);
       $tpl->display('api/class-list.tpl');
     }
     // overview
     else if (isset($_GET['overview'])) {
       $tpl->assign('packages', $this->_getPackages());
       $tpl->assign('constants', $this->_getConstants(TRUE));
       $tpl->assign('comments', $this->_getPackageComments());
       $tpl->display('api/overview.tpl');
     }
     // class description
     else if (isset($_GET['class'])) {
       $classes = $this->_getClasses();
       if (isset($classes[$_GET['class']])) {
         $classes1 = $this->_getClasses(TRUE);
         $tpl->assign('path', $path = $classes[$_GET['class']]);
         $parsed =& SRA_Util::parsePhpSource($classes[$_GET['class']]);
         $api = $parsed['classes'][$classes1[$_GET['class']]];
         if ($api['extends'] && in_array($api['extends'], $classes1)) {
           $api['extendsName'] = array_search($api['extends'], $classes1);
         }
         $api['package'] = $this->_getPackage($_GET['class']);
         $tpl->assign('constants', $this->_getClassConstants($path));
         $tpl->assign('src', $api);
         $tpl->assign('class', $_GET['class']);
         $tpl->display('api/class.tpl');
       }
     }
     // dtd description
     else if (isset($_GET['dtd'])) {
       $dtds = $this->_getDtds();
       if (isset($dtds[$_GET['dtd']])) {
         $tpl->assign('path', $path = $dtds[$_GET['dtd']]);
         $tpl->assign('src', SRA_Util::parseDtd($path));
         $tpl->assign('dtd', basename($path));
         $tpl->assign('package', $this->_getPackage($_GET['dtd']));
         $tpl->display('api/dtd.tpl');
       }
     }
     // function description
     else if (isset($_GET['function'])) {
       $functions = $this->_getFunctions();
       if (isset($functions[$_GET['function']])) {
         $functions1 = $this->_getFunctions(TRUE);
         $tpl->assign('path', $path = $functions[$_GET['function']]);
         $parsed =& SRA_Util::parsePhpSource($functions[$_GET['function']]);
         $api = $parsed['functions'][$functions1[$_GET['function']]];
         $api['package'] = $this->_getPackage($_GET['function']);
         $tpl->assign('src', $api);
         $tpl->assign('function', $_GET['function']);
         $tpl->display('api/function.tpl');
       }
     }
     // package description
     else if (isset($_GET['package'])) {
       $tpl->assign('classes', $this->_getClasses(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']])));
       $tpl->assign('constants', $this->_getConstants(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']])));
       $tpl->assign('dtds', $this->_getDtds(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']])));
       $tpl->assign('functions', $this->_getFunctions(TRUE,  array('package' => $_GET['package'], 'dir' => $packages[$_GET['package']])));
       $comments = $this->_getPackageComments();
       $tpl->assign('comment', isset($comments[$_GET['package']]) ? $comments[$_GET['package']] : NULL);
       $tpl->assign('package', $_GET['package']);
       $tpl->display('api/package.tpl');
     }
   }
   // }}}
   
   
  // {{{ _getClasses
  /**
   * returns a hash of classes in $this->_path. this hash will be indexed by the 
   * class/dtd name and the value in the hash will be the absolute path to the 
   * directory for that class or the basename of the class or DTD
   * @param boolean $basename whether or not the value in the return hash should 
   * be the absolute path to the file containing the class OR the basename of 
   * the class
   * @param mixed $path an alternate path. if not specified $this->_path will 
   * be used
   * @return hash
   */
  function _getClasses($basename=FALSE, $path=NULL, $property='classes') {
    $path = $path ? $path : $this->_path;
    if (!is_array($path)) $path = array($path);
    
    $ckey = 'sra_api_classes_' . $property . '_' . $basename . '_' . str_replace('/', '.', implode('_', $path) . '_' . ($this->_skipDirs ? implode('_', $this->_skipDirs) : '') . '_' . $this->_recursive);
    if ($this->_cache && ($classes =& SRA_Cache::getCache($ckey))) {
      return $classes;
    }
    
    $classes = array();
    foreach($this->_getPackages($path, $this->_skipDirs, $this->_recursive, $this->_cache) as $package => $dir) {
      if (isset($path['package']) && !SRA_Util::beginsWith($package, $path['package'])) continue;
      
      $files = SRA_File::getFileList($dir, '/\.php$/', FALSE);
      
      foreach($files as $file) {
        $fileMeta = SRA_Util::parsePhpSource($file);
        
        if ($fileMeta[$property]) {
          foreach(array_keys($fileMeta[$property]) as $key) {
            $name = ($property == 'constants' ? '' : $package . '.') . $fileMeta[$property][$key]['name'];
    
            $classes[$name] = $basename ? ($property == 'constants' ? $fileMeta[$property][$key]['value'] : $fileMeta[$property][$key]['name']) : $file;
          }
        }
      }
    }
    asort($classes);
    
    // cache classes
    if ($this->_cache) SRA_Cache::setCache($ckey, $classes, $this->_cache);
    
    return $classes;
  }
  // }}}
  
  // {{{ _getClassComment
  /**
   * returns the api comment for the class specified
   * @param string $id the id of the class
   * @return string
   */
  function _getClassComment($id) {
    $classes = $this->_getClasses();
    $classes1 = $this->_getClasses(TRUE);
    
    $parsed =& SRA_Util::parsePhpSource($classes[$id]);
    return $parsed['classes'][$classes1[$id]]['comment'];
  }
  // }}}
  
  // {{{ _getClassConstants
  /**
   * returns the a hash of the constants/values in the class identified by 
   * $name. returns NULL if no constants exist in that file
   * @param string $path the path to the file containing the class
   * @return mixed
   */
  function _getClassConstants($path) {
    $parsed =& SRA_Util::parsePhpSource($path);
    return isset($parsed['constants']) ? $parsed['constants'] : NULL;
  }
  // }}}
  
  // {{{ _getClassExtends
  /**
   * returns the name of the class that $id extends (if applicable)
   * @param string $id the id of the class
   * @return string
   */
  function _getClassExtends($id) {
    $classes = $this->_getClasses();
    $classes1 = $this->_getClasses(TRUE);
    
    $parsed =& SRA_Util::parsePhpSource($classes[$id]);
    return isset($parsed['classes'][$classes1[$id]]['extends']) ? $parsed['classes'][$classes1[$id]]['extends'] : NULL;
  }
  // }}}
  
  // {{{ _getClassId
  /**
   * returns the id of the class specified by $name
   * @param string $name the name of the class
   * @return string
   */
  function _getClassId($name) {
    $classes1 = $this->_getClasses(TRUE);
    
    if (in_array($name, $classes1)) {
      return array_search($name, $classes1);
    }
  }
  // }}}
  
  // {{{ _getConstants
  /**
   * returns a hash of constants in $this->_path. this hash will be indexed by 
   * the constant name and the value in the hash will be the absolute path to 
   * the php source file containing that constant or the value of the function
   * @param boolean $value whether or not the value in the return hash should 
   * be the value of the constant or the absolute path to the file containing 
   * the constant
   * @param mixed $path an alternate path. if not specified $this->_path will 
   * be used
   * @return hash
   */
  function _getConstants($value=FALSE, $path=NULL) {
    return $this->_getClasses($value, $path, 'constants');
  }
  // }}}
  
  // {{{ _getConstantProperties
  /**
   * returns the api comment for the constant specified
   * @param string $id the id of the constant
   * @return string
   */
  function _getConstantProperties($id) {
    $constants = $this->_getConstants();
    
    $parsed =& SRA_Util::parsePhpSource($constants[$id]);
    return $parsed['constants'][$id];
  }
  // }}}
  
  // {{{ _getConstantFile
  /**
   * returns the file containing the constant specified
   * @param string $id the id of the constant
   * @return string
   */
  function _getConstantFile($id) {
    $constants = $this->_getConstants();
    return $constants[$id];
  }
  // }}}
  
  // {{{ _getDtds
  /**
   * returns a hash of DTDs in $this->_path. this hash will be indexed by the 
   * class/dtd name and the value in the hash will be the absolute path to the 
   * directory for that class or the basename of the class or DTD
   * @param boolean $basename whether or not the value in the return hash should 
   * be the absolute path to the file containing the class OR the basename of 
   * the class
   * @param mixed $path an alternate path. if not specified $this->_path will 
   * be used
   * @return hash
   */
  function _getDtds($basename=FALSE, $path=NULL) {
    $path = $path ? $path : $this->_path;
    if (!is_array($path)) $path = array($path);
    
    $ckey = 'sra_api_dtds_' . $basename . '_' . str_replace('/', '.', implode('_', $path) . '_' . ($this->_skipDirs ? implode('_', $this->_skipDirs) : '') . '_' . $this->_recursive);
    if ($this->_cache && ($dtds =& SRA_Cache::getCache($ckey))) {
      return $dtds;
    }
    
    $dtds = array();
    foreach($this->_getPackages($path, $this->_skipDirs, $this->_recursive, $this->_cache) as $package => $dir) {
      if (isset($path['package']) && !SRA_Util::beginsWith($package, $path['package'])) continue;
      
      $files = SRA_File::getFileList($dir, '/\.dtd$/', FALSE);
      
      foreach($files as $file) {
        $name = $package . '.' . basename($file);

        $dtds[$name] = $basename ? basename($file) : $file;
      }
    }
    asort($dtds);
    
    // cache classes
    if ($this->_cache) SRA_Cache::setCache($ckey, $dtds, $this->_cache);
    
    return $dtds;
  }
  // }}}
  
  // {{{ _getDtdComment
  /**
   * returns the api comment for the dtd specified
   * @param string $id the id of the dtd
   * @return string
   */
  function _getDtdComment($id) {
   $dtds = $this->_getDtds();
   if (isset($dtds[$id])) {
     $src = SRA_Util::parseDtd($dtds[$id]);
     return $src['comment'] ? $src['comment'] : NULL;
   }
  }
  // }}}
  
  // {{{ _getFunctions
  /**
   * returns a hash of functions in $this->_path. this hash will be indexed by 
   * the function name and the value in the hash will be the absolute path to 
   * the php source file containing that function or the name of the function
   * @param boolean $basename whether or not the value in the return hash should 
   * be the absolute path to the file containing the class OR the basename of 
   * the class
   * @param mixed $path an alternate path. if not specified $this->_path will 
   * be used
   * @return hash
   */
  function _getFunctions($basename=FALSE, $path=NULL) {
    return $this->_getClasses($basename, $path, 'functions');
  }
  // }}}
  
  // {{{ _getFunctionComment
  /**
   * returns the api comment for the function specified
   * @param string $id the id of the function
   * @return string
   */
  function _getFunctionComment($id) {
    $functions = $this->_getFunctions();
    $functions1 = $this->_getFunctions(TRUE);
    
    $parsed =& SRA_Util::parsePhpSource($functions[$id]);
    return $parsed['functions'][$functions1[$id]]['comment'];
  }
  // }}}
  
  // {{{ _getPackage
  /**
   * returns the name of the package for the item $id
   * @param string $id the item to return the package for
   * @return string
   */
  function _getPackage($id) {
    $package = '';
    $pieces = explode('.', $id);
    for($i=0; $i<count($pieces)-(SRA_Util::endsWith($id, '.dtd') ? 2 : 1); $i++) {
      $package .= $package ? '.' : '';
      $package .= $pieces[$i];
    }
    return $package;
  }
  // }}}
   
  // {{{ _getPackages
  /**
   * returns a hash of packages in $this->_path. this hash will be indexed by the 
   * package name and the value in the hash will be the absolute path to the 
   * directory for that class
   * @return hash
   */
  function _getPackages() {
    
    $ckey = 'sra_api_packages_' . str_replace('/', '.', implode('_', $this->_path) . '_' . ($this->_skipDirs ? implode('_', $this->_skipDirs) : '') . '_' . $this->_recursive);
    if ($this->_cache && ($packages =& SRA_Cache::getCache($ckey))) {
      return $packages;
    }
    
    $packages = array();
    foreach($this->_path as $dir) {
      $includeBase = FALSE;
      if (!is_dir($dir) && SRA_Util::endsWith($dir, '__')) {
        $dir = substr($dir, 0, -2);
        $includeBase = TRUE;
      }
      if (is_dir($dir)) {
        $subdirs = SRA_File::getFileList($dir, '*', $this->_recursive, 2);
        if ($includeBase) $subdirs[] = $dir;
        foreach($subdirs as $package) {
          $subdir = $package;
          $package = str_replace($includeBase ? dirname($dir) : $dir, '', $package);
          if (substr($package, 0, 1) == '/') $package = substr($package, 1);
          if (substr($package, 0, 1) == '.' || strpos($package, '/.')) continue;
          $skip = FALSE;
          if ($this->_skipDirs) {
            foreach($this->_skipDirs as $skipDir) {
              if (strpos($package, $skipDir) === 0 || strpos($subdir, $skipDir) === 0) {
                $skip = TRUE;
                break;
              }
            }
          }
          if ($skip) continue;
          if (!SRA_File::getFileList($subdir, '/\.dtd$/', TRUE) && !SRA_File::getFileList($subdir, '/\.php$/', TRUE)) continue;
          
          $packages[str_replace('/', '.', $package)] = $subdir;
        }
      }
    }
    asort($packages);
    
    // cache packages
    if ($this->_cache) SRA_Cache::setCache($ckey, $packages, $this->_cache);
    
    return $packages;
  }
  // }}}
  
  // {{{ _getPackageComments
  /**
   * returns a hash of package comments. this hash will be indexed by the 
   * package name and the value in the hash will be the comment value. package 
   * comments are defined by a file in the root package directory named one of 
   * the files names in SRA_API_GENERATOR_PACKAGE_README_FILE
   * @return hash
   */
  function _getPackageComments() {
    $ckey = 'sra_api_package_comments_' . str_replace('/', '.', implode('_', $this->_path) . '_' . ($this->_skipDirs ? implode('_', $this->_skipDirs) : '') . '_' . $this->_recursive);
    if ($this->_cache && ($comments =& SRA_Cache::getCache($ckey))) {
      return $comments;
    }
    
    $packages = $this->_getPackages();
    $comments = array();
    $readmes = explode(' ', SRA_API_GENERATOR_PACKAGE_README_FILE);
    foreach($packages as $id => $package) {
      foreach($readmes as $readme) {
        if (file_exists($package . '/' . $readme)) {
          $comments[$id] = SRA_File::toString($package . '/' . $readme);
        }
      }
    }
    
    // cache comments
    if ($this->_cache) SRA_Cache::setCache($ckey, $comments, $this->_cache);
    
    return $comments;
  }
  // }}}
  
  // {{{ _getSubclasses
  /**
   * returns a hash of all of the known subclasses of $class. the return hash 
   * will be indexed by class id and the value will be the name of the subclass
   * @param string $class the name of the class to return the subclasses for
   * @return hash
   */
  function _getSubclasses($class) {
    $class = strtolower($class);
    
    $ckey = 'sra_api_subclasses_' . $class . '_' . str_replace('/', '.', implode('_', $this->_path) . '_' . ($this->_skipDirs ? implode('_', $this->_skipDirs) : '') . '_' . $this->_recursive);
    if ($this->_cache && ($subclasses =& SRA_Cache::getCache($ckey))) {
      return $subclasses;
    }
    
    $subclasses = array();
    
    foreach($this->_getClasses(TRUE) as $lookup => $name) {
      if (strtolower($this->_getClassExtends($lookup)) == $class) {
        $subclasses[$lookup] = $name;
      }
    }
    $subclasses = $subclasses ? $subclasses : NULL;
    
    // cache subclasses
    if ($this->_cache) SRA_Cache::setCache($ckey, $subclasses, $this->_cache);
    
    return $subclasses;
  }
  // }}}
}
// }}}
?>
