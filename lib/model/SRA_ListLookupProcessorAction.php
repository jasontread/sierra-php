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
 * identifies an update action, the LLP results will still be displayed with the 
 * corresponding updated entities reflected
 */
define('SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_UPDATE', 1);

/**
 * identifies a delete action, the LLP results will still be displayed with the 
 * corresponding deleted entities removed. cannot be used in conjunction with 
 * any of the SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW* types
 */
define('SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_DELETE', 2);

/**
 * identifies a view action. the LLP results will not be displayed. ONLY 1 
 * of the SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW* constants should be 
 * used
 */
define('SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW', 4);

/**
 * identifies a new action. same as SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_VIEW, 
 * but a new entity will be instantiated and rendered using the view specified. 
 * If this action is associated to a specific entity, that entity will be copied
 * into the view specified for this action
 */
define('SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW', 8);

/**
 * identifies an action that involves editing the entity (updating, inserting, 
 * deleting)
 */
define('SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_EDIT', SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_UPDATE + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_DELETE + SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_NEW);
// }}}

// {{{ SRA_ListLookupProcessorAction
/**
 * Used to store all of the data associated with a list lookup processor action
 * see the SRA_ListLookupProcessor class header api for more details
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_ListLookupProcessorAction {
  // {{{ Attributes
  // public attributes
  /**
	 * unique identifier for this action
	 * @type string
	 */
	var $id;
	
  /**
	 * the resource to use as the alt/title string for the action link
	 * @type string
	 */
	var $alt;
	
  /**
	 * the resource to use as the alt/title string for the action in global links
	 * @type string
	 */
	var $globalAlt;
	
  /**
	 * html code to render after the close </a> tag
	 * @type string
	 */
	var $aPost;
	
  /**
	 * html code to render before the close </a> tag
	 * @type string
	 */
	var $aPre;
	
	/**
	 * whether or not to render the view action as a button. the default behavior 
   * is to render it as a link. $link must also be specified if this is the case 
	 * it will be used for the button text
	 * @type boolean
	 */
	var $btn;
	
  /**
	 * the html class to use in the <a.../ > or <button... /> tag generated for the action link
	 * @type string
	 */
	var $cssClass;
	
  /**
	 * the resource to display when an action has occurred successfully (displayed 
	 * in a javascript alert window)
	 * @type string
	 */
	var $msgComplete;
	
  /**
	 * the resource to display if an action is initiated (clicked) (displayed 
	 * in a javascript confirm window)
	 * @type string
	 */
	var $msgConfirm;
	
  /**
	 * the resource to display when an action has occurred successfully globally (displayed 
	 * in a javascript alert window)
	 * @type string
	 */
	var $globalMsgComplete;
	
  /**
	 * the resource to display if an action is initiated globally (clicked) (displayed 
	 * in a javascript confirm window)
	 * @type string
	 */
	var $globalMsgConfirm;
	
  /**
	 * the img src value to use for the action link. Only 1, _img or _link should 
	 * be specified, if both are specified, $link will be ignored
	 * @type string
	 */
	var $img;
	
  /**
	 * class to assign to the rendered image ($img)
	 * @type string
	 */
	var $imgClass;
	
  /**
	 * the resource to use as the text for the action link
	 * @type string
	 */
	var $link;
	
	/**
	 * whether or not this action cannot be applied globally (NOTE: it will still 
	 * show up in the global actions links unless explicitely specified otherwise)
	 * @type boolean
	 */
	var $nonGlobal;
	
  /**
	 * html code to render after the action link (but before the close </a> tag)
	 * @type string
	 */
	var $post;
	
  /**
	 * html code to render before the action link (but after the close </a> tag)
	 * @type string
	 */
	var $pre;
	
  /**
	 * the action type. this value is a bitmask representing one or more of the 
	 * SRA_LIST_LOOKUP_PROCESSOR_ACTION_TYPE_* constants above
	 * @type int
	 */
	var $type;
	
	/**
	 * the id of the view that should be displayed
	 * @type string
	 */
	var $view;
	
	/**
	 * the id of the view that should be displayed if the action is completed 
	 * successfully
	 * @type string
	 */
	var $viewFwd;
	
  // private attributes
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_ListLookupProcessorAction
	/**
	 * Constructor
   * @access  public
	 */
	function SRA_ListLookupProcessorAction($id, $aPost, $aPre, $cssClass, $link, $btn, 
                                     $img, $imgClass, $alt, $globalAlt, $msgComplete, $msgConfirm, 
																		 $globalMsgComplete, $globalMsgConfirm, $post, 
																		 $pre, $type, $view, $viewFwd, $nonGlobal) {
		$this->id = $id;
		$this->aPost = $aPost;
		$this->aPre = $aPre;
		$this->cssClass = $cssClass;
		$this->link = $link;
		$this->btn = $btn;
		$this->img = $img;
		$this->imgClass = $imgClass;
		$this->alt = $alt;
		$this->globalAlt = $alt;
		if ($globalAlt) {
			$this->globalAlt = $globalAlt;
		}
		$this->msgComplete = $msgComplete;
		$this->msgConfirm = $msgConfirm;
		$this->globalMsgComplete = $msgComplete;
		$this->globalMsgConfirm = $msgConfirm;
		if ($globalMsgComplete) {
			$this->globalMsgComplete = $globalMsgComplete;
		}
		if ($globalMsgConfirm) {
			$this->globalMsgConfirm = $globalMsgConfirm;
		}
		$this->post = $post;
		$this->pre = $pre;
		$this->type = $type;
		$this->view = $view;
		$this->viewFwd = $viewFwd;
		$this->nonGlobal = $nonGlobal;
	}
	// }}}
	
  
  // public operations
	
	// {{{ getAlt
	/**
	 * 
   * @access  public
	 */
	function getAlt($global = FALSE) {
		return !$global ? $this->alt : $this->globalAlt;
	}
	// }}}
	
	// {{{ getMsgComplete
	/**
	 * 
   * @access  public
	 */
	function getMsgComplete($global = FALSE) {
		return !$global ? $this->msgComplete : $this->globalMsgComplete;
	}
	// }}}
	
	// {{{ getMsgConfirm
	/**
	 * 
   * @access  public
	 */
	function getMsgConfirm($global = FALSE) {
		return !$global ? $this->msgConfirm : $this->globalMsgConfirm;
	}
	// }}}
	
	
	// Static methods
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_ListLookupProcessorAction object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_listlookupprocessoraction');
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
