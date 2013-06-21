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
 * the template variable under which this class instance will be accessible for 
 * a given SRA_ListLookupProcessor::lookup invocations
 */
define('LIST_LOOKUP_PROCESSOR_DATA_TEMPLATE_VAR', 'listData');
// }}}

// {{{ SRA_ListLookupProcessorData
/**
 * Used to store all of the data associated with a particular SRA_ListLookupProcessor::lookup 
 * invocation. The instance of this class representing that data is stored in the 
 * SRA_Template using the key value 
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_ListLookupProcessorData {
  // {{{ Attributes
  // public attributes
	/**
	 * the position for the actions, either the leftmost (L) or rightmost (R), or 
	 * a numbered column (column #s begin at 0) column
	 * @type string
	 */
	var $actionsPos = FALSE;
	
	/**
	 * the name of the entity the lookup was processed for
	 * @type string
	 */
	var $entityName;
	
	/**
	 * a new instance of $entityName
	 * @type Entity
	 */
	var $entity;
	
  /**
	 * the name of the html form containing the list lookup processor field elements
	 * @type string
	 */
	var $formName;
	
	/**
	 * the form type (get or post)
	 * @type string
	 */
	var $formType;
	
	/**
	 * whether or not global actions exist for the LLP view
	 * @type boolean
	 */
	var $globalActions;
	
	/**
	 * the name of the limit field
	 * @type string
	 */
	var $limitField;
	
	/**
	 * the number of columns in the table
	 * @type int
	 */
	var $numCols = 0;
	
	/**
	 * the name of the page # field
	 * @type string
	 */
	var $pageField;
	
	/**
	 * SRA_Params for the LLP
	 * @type SRA_Params
	 */
	var $params;
	
	/**
	 * the results string for the current data
	 * @type string
	 */
	var $resultsString;
	
	/**
	 * the current suffix to use for drop down selectors
	 * @type int
	 */
	var $selectorSuffix = 0;
	
  // private attributes
  /**
	 * ordered array of actions (in the order they should be displayed) for the 
	 * current list view
	 * @type SRA_LookupProcessorAction[]
	 */
	var $_actions;
	
  /**
	 * ordered array of global actions (in the order they should be displayed) for the 
	 * current list view
	 * @type SRA_LookupProcessorAction[]
	 */
	var $_globalActions;
	
  /**
	 * the current page # (between 1 and lastPage)
	 * @type int
	 */
	var $_currentPage;
	
	/**
	 * dynamic sort attr/method associative array
	 * @type array
	 */
	var $_dynamicSortAttrs = array();
	
  /**
	 * associative array of all of the current form values (index by field name)
	 * @type array
	 */
	var $_fieldValues;
	
  /**
	 * sequence for global select checkboxes
	 * @type int
	 */
	var $_globalFieldSeq = 0;
	
  /**
	 * the # of results displayed per page
	 * @type int
	 */
	var $_limit;
	
  /**
	 * the total # of results
	 * @type int
	 */
	var $_resultCount;
	
	/**
	 * primary keys values of the entities selected for a view or action
	 * @type array()
	 */
	var $_selectedEntities = array();
	
	/**
	 * ordered array defining the sort order of attrs contained in _dynamicSortAttrs
	 * @type array()
	 */
	var $_sortOrder = array();
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_ListLookupProcessorData
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_ListLookupProcessorData($entityName, & $actions, & $globalActions, $currentPage, $fieldValues, $limit, 
																	 $resultCount, $formName, $formType, $limitField, 
																	 $pageField, $dynamicSortAttrs, $sortOrder, $selectedEntities, $actionsPos,
																	 $resultsResource, $useGlobalActions, $params) {
		$this->entityName = $entityName;
		$dao =& SRA_DaoFactory::getDao($entityName);
		$this->entity = $dao->newInstance();
		$this->_actions = $actions;
		$this->_globalActions = $globalActions;
		$this->_currentPage = $currentPage;
		$this->_fieldValues = $fieldValues;
		$this->_limit = $limit;
		$this->_resultCount = $resultCount;
		$this->formName = $formName;
		$this->formType = $formType;
		$this->limitField = $limitField;
		$this->pageField = $pageField;
		$this->globalActions = $useGlobalActions;
		$this->params = $params;
		if (is_array($dynamicSortAttrs) && count($dynamicSortAttrs)) {
			$this->_dynamicSortAttrs = $dynamicSortAttrs;
			if (is_array($sortOrder) && count($sortOrder)) {
				foreach ($sortOrder as $order => $attr) {
					if (isset($dynamicSortAttrs[$attr])) {
						$this->_sortOrder[] = $attr;
					}
				}
			}
			else {
				$attrs = array_keys($dynamicSortAttrs);
				foreach ($attrs as $attr) {
					$this->_sortOrder[] = $attr;
				}
			}
		}
		if (is_array($selectedEntities)) {
			$this->_selectedEntities = $selectedEntities;
		}
		if ($actionsPos != 'L' && $actionsPos != 'R' && $actionsPos !== FALSE) {
			$this->actionsPos = (int) $actionsPos;
		}
		else if ($actionsPos == 'L' || $actionsPos == 'R') {
			$this->actionsPos = $actionsPos;
		}
		if ($resultsResource) {
			$params = array('start' => $this->getStartPos(), 'end' => $this->getEndPos(), 
										  'count' => $this->getResultCount(), 'page' => $this->getCurrentPage(), 
											'pageCount' => $this->getLastPage());
			$rb =& SRA_Controller::getAppResources();
			$this->resultsString = $rb->getString($resultsResource, $params);
		}
	}
	// }}}
	
  
  // public operations
	// {{{ getActions
	/**
	 * @param boolean $global whether or not to return the global actions
   * @access  public
	 * @return SRA_LookupProcessorAction[]
	 */
	function getActions($global = FALSE) {
		return $global ? $this->_globalActions : $this->_actions;
	}
	// }}}
	
	// {{{ getCurrentPage
	/**
   * @access  public
	 * @return #_currentPage
	 */
	function getCurrentPage() {
		$lastPage = $this->getLastPage();
		return $this->_currentPage <= $lastPage ? $this->_currentPage : $lastPage;
	}
	// }}}
	
	// {{{ getEndPos
	/**
   * @access  public
	 * @return the result end position for the current list view (between #_limit and #_resultCount)
	 */
	function getEndPos() {
		return ($this->getCurrentPage() * $this->_limit) <= $this->_resultCount ? $this->getCurrentPage() * $this->_limit : $this->_resultCount;
	}
	// }}}
	
	// {{{ getFieldValues
	/**
	 * returns the value for the field specified or all fields
	 * @param string $fieldName optional name of the field to return the value for 
	 * if not specified, the full associative array of field/value pairs will be 
	 * returned
   * @access  public
	 * @return mixed
	 */
	function & getFieldValues($fieldName = FALSE) {
		return $fieldName ? $this->_fieldValues[$fieldName] : $this->_fieldValues;
	}
	// }}}
	
	// {{{ getFirstSortAttr
	/**
	 * returns the name of the first sortable attribute (FALSE if none)
   * @access  public
	 * @return string or FALSE
	 */
	function getFirstSortAttr() {
		if (count($this->_dynamicSortAttrs)) {
			$keys = array_keys($this->_dynamicSortAttrs);
			return $keys[0];
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getLastPage
	/**
   * @access  public
	 * @return #_lastPage
	 */
	function getLastPage() {
		return ceil($this->_resultCount / $this->_limit);
	}
	// }}}
	
	// {{{ getLimit
	/**
   * @access  public
	 * @return #_limit
	 */
	function getLimit() {
		return $this->_limit;
	}
	// }}}
	
	// {{{ getNextGlobalFieldSeq
	/**
	 * returns the next global field sequence value
   * @access  public
	 * @return int
	 */
	function getNextGlobalFieldSeq() {
		return $this->_globalFieldSeq++;
	}
	// }}}
	
	// {{{ getNextPage
	/**
	 * returns the next page #. if the current page is the last page, then returns 1
   * @access  public
	 * @return int
	 */
	function getNextPage() {
		return $this->getCurrentPage() == $this->getLastPage() ? 1 : $this->getCurrentPage() + 1;
	}
	// }}}
	
	// {{{ getPreviousPage
	/**
	 * returns the previous page #. if the current page is the first page, then returns the last page
   * @access  public
	 * @return int
	 */
	function getPreviousPage() {
		return $this->getCurrentPage() == 1 ? $this->getLastPage() : $this->getCurrentPage() - 1;
	}
	// }}}
	
	// {{{ getPagesArray
	/**
	 * returns a numerical array containing all of the pages available for the 
	 * current list view
	 * @param int $max the maximum # of pages to return in the array
	 * @param int $median if $max is specified, defines the median position for 
	 * the pages array. meaning the array will contain a maximum of max/2 pages 
	 * prior to that median page # and max/2 after. the default median is the 
	 * #_currentPage
   * @access  public
	 * @return array
	 */
	function getPagesArray($max = FALSE, $median = FALSE) {
		$start = 1;
		$end = $this->getLastPage();
		if ($max && $max < $end) {
			if (!$median) {
				$median = $this->getCurrentPage();
			}
			if (($median + $max) > $end) {
				$start = $end - $max;
			}
			else {
				if (($median - ceil($max/2)) > 1) {
					$start = $median - ceil($max/2);
				}
				$end = $start + $max;
			}
		}
		$pages = array();
		for($i=$start; $i<=$end; $i++) {
			$pages[] = $i;
		}
		return $pages;
	}
	// }}}
	
	// {{{ getParam
	/**
	 * returns a param value
	 * @param string $key the identifier of the param to return
   * @access  public
	 * @return SRA_Params
	 */
	function getParam($key) {
		return $this->params->getParam($key);
	}
	// }}}
	
	// {{{ getParams
	/**
	 * returns the SRA_Params (or a subtype)
   * @access  public
	 * @return SRA_Params
	 */
	function & getParams($type = FALSE) {
		if ($type) {
			return $this->params->getTypeSubset($type);
		}
		else {
			return $this->params;
		}
	}
	// }}}
	
	// {{{ getResultCount
	/**
   * @access  public
	 * @return #_resultCount
	 */
	function getResultCount() {
		return $this->_resultCount;
	}
	// }}}
	
	// {{{ getSortMethod
	/**
	 * returns the associated sort method for the attribute specified, or FALSE if 
	 * no sorting has been applied to that attrible
   * @access  public
	 * @return asc|desc|FALSE
	 */
	function getSortMethod($attr) {
		return isset($this->_dynamicSortAttrs[$attr]) ? $this->_dynamicSortAttrs[$attr] : FALSE;
	}
	// }}}
	
	// {{{ getSortMethods
	/**
	 * returns an associative array of all current sort methods indexed by attribute 
	 * name where the value is the sort method
   * @access  public
	 * @return array
	 */
	function getSortMethods() {
		return $this->_dynamicSortAttrs;
	}
	// }}}
	
	// {{{ getSortOrder
	/**
	 * returns an associative array of all current sort attributes indexed by their 
	 * order in the sort
   * @access  public
	 * @return array
	 */
	function getSortOrder() {
		return $this->_sortOrder;
	}
	// }}}
	
	// {{{ getSortMethodPrefix
	/**
	 * returns the dynamic sort method form field prefix
   * @access  public
	 * @return SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_(ASC|DESC)_SORT_CONSTRAINT_PREFIX
	 */
	function getSortMethodPrefix($attr, $default) {
		if (!$sortMethod = $this->getSortMethod($attr)) {
			$sortMethod = $this->_toggleSortMethod($default);
		}
		return $sortMethod == SRA_QUERY_BUILDER_SORT_ASC ? SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX : SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX;
	}
	// }}}
	
	// {{{ getToggleSortMethod
	/**
	 * returns the associated toggle sort method for a given attribute. meaning, 
	 * if the current dynamically set sort method for the attribute is ascending, 
	 * then this method will return descending, and vise-versa. if no current 
	 * sort method exists for the attribute, then $default is returned
   * @access  public
	 * @return asc|desc
	 */
	function getToggleSortMethod($attr, $default=FALSE) {
		return isset($this->_dynamicSortAttrs[$attr]) ? $this->_toggleSortMethod($this->_dynamicSortAttrs[$attr]) : $default;
	}
	// }}}
	
	// {{{ getToggleSortMethodPrefix
	/**
	 * returns the dynamic toggled sort method form field prefix
   * @access  public
	 * @return SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_(ASC|DESC)_SORT_CONSTRAINT_PREFIX
	 */
	function getToggleSortMethodPrefix($attr, $default) {
		$sortMethod = $this->getToggleSortMethod($attr, $default);
		return $sortMethod == SRA_QUERY_BUILDER_SORT_ASC ? SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX : SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX;
	}
	// }}}
	
	// {{{ getStartPos
	/**
   * @access  public
	 * @return the result starting position for the current list view (between 1 and #_resultCount)
	 */
	function getStartPos() {
		$pos = (($this->getCurrentPage() - 1) * $this->_limit) + 1;
		return $pos >= 0 ? $pos : 0;
	}
	// }}}
	
	// {{{ incNumCols
	/**
	 * increments the # of columns in the table
   * @access  public
	 * @return void
	 */
	function incNumCols() {
		$this->numCols++;
	}
	// }}}
	
	// {{{ incSelectorSuffix
	/**
	 * increments #incSelectorSuffix
   * @access  public
	 * @return void
	 */
	function incSelectorSuffix() {
		$this->selectorSuffix++;
	}
	// }}}
	
	// {{{ isSelected
	/**
	 * returns TRUE if the entity specified is selected
   * @access  public
	 * @return boolean
	 */
	function isSelected($pk) {
		return in_array($pk, $this->_selectedEntities);
	}
	// }}}
	
	// {{{ isSortable
	/**
	 * returns TRUE if the attr specified is sortable
   * @access  public
	 * @return boolean
	 */
	function isSortable($attr, $sortableAttrs) {
		return is_array($sortableAttrs) && $attr && in_array($attr, $sortableAttrs);
	}
	// }}}
	
	// {{{ _toggleSortMethod
	/**
	 * returns the opposite of the sort method specified
   * @access  public
	 * @return string
	 */
	function _toggleSortMethod($method) {
		return $method == SRA_QUERY_BUILDER_SORT_ASC ? SRA_QUERY_BUILDER_SORT_DESC : SRA_QUERY_BUILDER_SORT_ASC;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_ListLookupProcessorData object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_listlookupprocessordata');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
