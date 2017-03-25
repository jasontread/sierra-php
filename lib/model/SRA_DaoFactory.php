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
 * the file that should be used to register DAOs. the app key will be 
 * appended to this file so each app has its own unique register file
 */
define('SRA_DAO_FACTORY_REGISTER_FILE', '.daos-');
// }}}

// {{{ SRA_DaoFactory
/**
 * Generalized DAO Factory allowing applications to retrieve app specified
 * DAOs. All methods are static. 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_DaoFactory {
  // {{{ Attributes
  // public attributes
  
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_DaoFactory
	/**
	 * Constructor - does nothing
   * @access  private
	 */
	function SRA_DaoFactory() {}
	// }}}
	
  
  // public operations
	
	
	// Static methods
	// {{{ getDao
	/**
	 * Returns a DAO for the current active app and the specified entity 
	 * type. The entity type specified must have been defined in a entity-model 
	 * for that app. If not referencing the default app entity-model, 
	 * the entity name should be preceded by the entity-model identifier as specified 
	 * in the app-config followed by a period (i.e. my-model.MyEntity).
	 * @param string $entity the unique entity identifier of the entity to 
	 * return
   * @param boolean $fresh whether or not a fresh, new instance of the DAO should 
   * be returned. by default, a cached version will be returned if available in 
   * order to take advantage of query caching
	 * @param string $app the app to return the DAO for. if not 
	 * specified, the current active app will be used. If not found in the active
	 * app, other apps will be checked
   * @param int $errorLevel the error level to apply if a dao for $entity is not 
   * found
   * @access  public
	 * @return DAO
	 */
	function &getDao($entity, $fresh = FALSE, $app = FALSE, $debug = FALSE, $errorLevel = SRA_ERROR_PROBLEM) {
	  $dao = NULL;
	  
	  $currentApp = SRA_Controller::getCurrentAppId();
	  $explicitApp = $app ? TRUE : FALSE;
    $app = $app ? $app : $currentApp;
		
    // application is not initialized
    if (!$app) {
      $msg = 'SRA_DaoFactory::getDao: Failed - Application is not currently initialized';
      return SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel);
    }
    
    if (is_object($entity) && method_exists($entity, 'gettype')) {
      $entity = $entity->getType();
    }
    
    $checkApps = array($app);
    if (!$explicitApp) {
      foreach(SRA_Controller::getAllAppIds() as $id) {
        if (!in_array($id, $checkApps)) {
          $checkApps[] = $id;
          $lastApp = $id;
        }
      }
    }
		
		// static cached DAOs
		static $daos = array();
		foreach($checkApps as $app) {
		  if ($app != $currentApp) SRA_Controller::init($app);
  		if (!isset($daos[$app . $entity]) || $fresh) {
  			SRA_Util::printDebug("SRA_DaoFactory::getDao - accessing DAO for app ${app}, entity type ${entity}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
  			$file = SRA_DaoFactory::_getRegisterFile($app);
  			$parser =& SRA_XmlParser::getXmlParser($file);
  			if ($entity && SRA_XmlParser::isValid($parser) && is_array($data =& $parser->getData(array('dao', $entity, 'attributes')))) {
  				require_once($data['file']);
  				$daos[$app . $entity] = new ${data}['class']($entity);
  			}
  			else if ($app == $lastApp) {
  				$msg = "SRA_DaoFactory::getDao: Failed - Invalid app ${app}, entity ${entity} or file ${file}";
  				$dao =& SRA_Error::logError($msg, __FILE__, __LINE__, $errorLevel );
  			}
  		}
  		else $dao =& $daos[$app . $entity];
  		
  		if ($dao) break; 
		}
		if ($app != $currentApp) SRA_Controller::init($currentApp);
		
		return $dao;
	}
	// }}}
	
	
	// {{{ getDaos
	/**
	 * Returns an associative array of all of the DAOs for the specified app
	 * @param string $app the app to return the DAO for. if not 
	 * specified, the current active app will be used 
   * @access  public
	 * @return DAO
	 */
	function &getDaos($app = FALSE) {
		if (!$app) {
			$app = SRA_Controller::getCurrentAppId();
		}
		
		// static cached DAOs
		static $appDAOs = array();
		if (!$appDAOs[$app]) {
			SRA_Util::printDebug("SRA_DaoFactory::getDaos - accessing DAOs for app ${app}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
			$file = SRA_DaoFactory::_getRegisterFile($app);
			$parser =& SRA_XmlParser::getXmlParser($file);
			$data =& $parser->getData(array('dao'));
			$keys = array_keys($data);
			$appDAOs[$app] = array();
			foreach($keys as $key) {
				$appDAOs[$app][$key] =& SRA_DaoFactory::getDao($key, FALSE, $app);
			}
		}
		
		return $appDAOs[$app];
	}
	// }}}
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_DaoFactory object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_daofactory');
	}
	// }}}
	
	// {{{ registerDAO
	/**
	 * 
	 * @param string $app 
	 * @param string $entity 
	 * @param string $daoClass 
	 * @param string $daoFile 
	 * @param string $app 
   * @access  public
	 * @return DAO
	 */
	function registerDAO($entity, $daoClass, $daoFile, $app = FALSE) {
		if (!$app) {
			$app = SRA_Controller::getCurrentAppId();
		}
		SRA_Util::printDebug("SRA_DaoFactory::registerDAO - Registing DAO for app ${app}, entity ${entity}, daoClass ${daoClass}, daoFile ${daoFile}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
		$file = SRA_DaoFactory::_getRegisterFile($app);

		if (!file_exists($file)) {
			$xml = array('daos' => array('dao' => array()));
		}
		else {
			$parser =& SRA_XmlParser::getXmlParser($file, TRUE);
			$xml =& $parser->getData();
		}
		$xml['daos']['dao'][$entity] = array('attributes' => array('key' => $entity, 'class' => $daoClass, 'file' => $daoFile));
		$fp = fopen($file, 'w');
		fwrite($fp, SRA_XmlParser::arrayToXML($xml));
		fclose($fp);
    chmod($file, 0666);
	}
	// }}}
	
	// {{{ resetDAOs
	/**
	 * 
	 * @param string $app 
   * @access  public
	 * @return DAO
	 */
	function resetDAOs($app = FALSE) {
		if (!$app) {
			$app = SRA_Controller::getCurrentAppId();
		}
		SRA_Util::printDebug("SRA_DaoFactory::resetDAOs - Resetting DAOs for app ${app}", SRA_Controller::isSysInDebug(), __FILE__, __LINE__);
		$file = SRA_DaoFactory::_getRegisterFile($app);
		if (file_exists($file)) { unlink($file); }
	}
	// }}}
	
  
  // private operations
	// {{{ _getRegisterFile
	/**
	 * Returns the application specific dao register file
	 * @param string $app the app to return the dao register file for
   * @access  public
	 * @return DAO
	 */
	function _getRegisterFile($app) {
		return SRA_Controller::getAppTmpDir() . '/' . SRA_DAO_FACTORY_REGISTER_FILE . $app . '.xml';
	}
	// }}}
  
}
// }}}
?>
