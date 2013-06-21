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
require_once('model/SRA_ViewHeader.php');
require_once('model/SRA_EntityViewProcessor.php');
require_once('util/SRA_Params.php');
// }}}

// {{{ Constants
/**
 * default template variable name for attribute values
 */
define('ATTRIBUTE_VIEW_TEMPLATE_VAR_NAME', 'attribute');

/**
 * default template variable name for entity values
 */
define('ENTITY_VIEW_TEMPLATE_VAR_NAME', 'entity');

/**
 * "attrs" prefix used to identify that the attributes should be determined 
 * using a PHP code snippet
 */
define('ENTITY_VIEW_CODE_PREFIX', 'code:');
// }}}

// {{{ SRA_EntityView
/**
 * Represents the data associated with a view
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.model
 */
class SRA_EntityView {
  // {{{ Attributes
  // public attributes
	
  // private attributes
  /**
	 * unique identifier for the view
	 * @type string
	 */
	var $_id;
	
  /**
	 * the order that attributes should be rendered, if not specified, then they 
	 * will be rendered in the order they are defined
	 * @type array
	 */
	var $_attributes = FALSE;
	
  /**
	 * an code snippet that should be used to determine which attributes to display
	 * @type string
	 */
	var $_attributesCode = FALSE;
	
  /**
	 * whether or not this is the default view for the entity
	 * @type boolean
	 */
	var $_default;
	
  /**
	 * the identifier of a view that this view extends
	 * @type string
	 */
	var $_extends;
  
	/**
	 * an alternate field name to use for attribute views. if specified, this name 
   * will be used instead of the attribute name. this will break automatic form 
   * instantiation using newInstanceFromForm
	 * @type string
	 */
  var $_fieldName;
	
	/**
	 * if this view forwards this view to nested attributes/entities, the fowardView 
	 * may be specified to identify the view that should be rendered when this 
	 * forwarding occurs. otherwise, the same view id used by this view will be 
	 * rendered
	 * @type string
	 */
	var $_forwardView = FALSE;

	/**
	 * specifies the php code that will provide the list of ids for the entities 
   * that should be displayed. applies only when the view is assigned to an 
   * attribute, and that attribute has upper bound cardinality > 1, and that 
   * attribute represents an entity
	 * @type string
	 */
  var $_idConstraint;
	
	/**
	 * whether or not this view is for an attribute
	 * @type boolean
	 */
	var $_isAttribute;
	
	/**
	 * if the view is for an attribute, and the attribute is an array, then the 
	 * default behavior is to display the "template" once for each instance of the 
	 * attribute in that array. setting iterate to "0" will result in the template 
	 * only being displayed once for the entire array of attribute values
	 * @type boolean
	 */
	var $_iterate = FALSE;
	
	/**
	 * full path to the PHP file/class that extends model/SRA_LookupProcessor. If a 
	 * SRA_LookupProcessor is specified, that class must implement the static method 
	 * SRA_LookupProcessor::lookup as described in the SRA_LookupProcessor api. This 
	 * method will return an array of entities of the type that encloses this 
	 * view. SRA_LookupProcessors can ONLY be used for entity views, and not for 
	 * attributes. If a SRA_LookupProcessor is defined, this view will become 
	 * statically accessible via the {entity}::render($viewId) method and the 
	 * entire view definition will be processed once for each entity instance 
	 * returned by the SRA_LookupProcessor. the view "params" will be passed into the 
	 * SRA_LookupProcessor::lookup method along with the entity name. Examples:
	 * 		"MyLookupProcessor": SRA_LookupProcessor subclass is stored in 
	 *		the standard lib directory for the app
	 *									
	 *		"processors/MyLookupProcessor": SRA_LookupProcessor subclass is 
	 *		stored in the sub-directory "processors" beneath the statard 
	 *		lib directory for the app
	 *									
	 *		"model/SRA_ListLookupProcessor": a powerful lookup processor 
	 *		located in SRA_DIR/lib/model/ that can be utilized to display 
	 *		lists of entities in any format including the ability to 
	 *		segment the view into multiple pages.
	 * @type string
	 */
	var $_lookupProcessor;
	
	/**
	 * the lookup processor class name
	 * @type string
	 */
	var $_lookupProcessorClass;
	
	/**
	 * a message (resource key) to display if the lookup processor returns no entities
	 * @type string
	 */
	var $_lpNoResultsMsg;
	
	/**
	 * a template to display if the lookup processor returns no entities
	 * @type string
	 */
	var $_lpNoResultsTpl;
	
	/**
	 * Content-type header that should be output for this view
	 * @type string
	 */
	var $_mimeType;
	
	/**
	 * used for attribute views only where a cardinality property is defined for 
	 * that attribute. in that scenario, this attribute specifies which entity 
	 * attribute specifies the # of attributes of that type that should referenced 
	 * to the attribute. if the actual # of instances exceeds that #, the view 
	 * will automatically ignore the extra instances. if not enough  instances 
	 * currently exist, new instances will be created. 
	 * @type string
	 */
	var $_multiplictyAttr;
	
  /**
	 * An optional SRA_Params object specified for this view
	 * @type SRA_Params
	 */
	var $_params;
  
  /**
	 * html to render last (after the post-template if specified)
	 * @type string
	 */
	var $_post;
	
  /**
	 * the last smarty template to be displayed for the entity. may perform view 
	 * functions such as setting up a footer
	 * @type string
	 */
	var $_postTemplate;
  
  /**
	 * html to render first (before the pre-template if specified)
	 * @type string
	 */
	var $_pre;
	
  /**
	 * the first smarty template to be displayed for the entity. may perform view 
	 * functions such as setting up a header
	 * @type string
	 */
	var $_preTemplate;
	
	/**
	 * whether or not the entity's or attribute's sub-attributes should be 
	 * rendered. if TRUE, they will be rendered after the "template" and before 
	 * the "post-template". this option only applies for entity views, or an 
	 * attribute that references another entity. if the latter is the case, then 
	 * 1 of 3 scenarios apply:
	 * 	1) if "attrs" has been specified, then each of 
	 * 		 those attributes will be rendered using the entity's 
	 * 		 "renderAttribute" method
	 * 	2) if "attrs" has not been specified, and the 
	 * 		 referenced entity has a view with the same identifier, the 
	 * 		 entity's "render" method will be invoked for that view. 
	 * 		 "skip-attributes" may also be specified in this scenario
	 *  3) if "render-attributes" is FALSE, then no attribute rendering will be 
	 * 		 performed. However, the "template" will be displayed with the 
	 *		 "attribute" value set to the instance of the entity or entitites
	 * @type boolean
	 */
	var $_renderAttributes = FALSE;
	
  /**
	 * space separated list of attributes that should not be rendered when 
	 * "render-attributes" is true
	 * @type array
	 */
	var $_skipAttributes = FALSE;
	
  /**
	 * the primary smarty template utilized to render the entity or attribute
	 * @type string
	 */
	var $_template;
	
  /**
	 * specifies the variable name under which the attribute value should be 
	 * stored for the smarty template. If none is specified, then the variable 
	 * name will be "attribute"
	 * @type string
	 */
	var $_templateVar = ENTITY_VIEW_TEMPLATE_VAR_NAME;
	
	/**
	 * additional http header outputs that should precede the output for an entity 
	 * view
	 * @type SRA_ViewHeader[]
	 */
	var $_viewHeaders = array();
	
	/**
	 * used to override the default label for an entity/attribute within a view. 
	 * indexed by entity/attribute name
	 * @type array
	 */
	var $_viewLabels;
	
	/**
	 * SRA_EntityViewProcessor objects that should be invoked on the output of this 
	 * view. these will be executed in the order specified where the input to the 
	 * first will be the output from the "view" template, and the input to the 2nd 
	 * will be the output from the first and so on. all template outputs 
	 * (including post-template) will be buffered and input to the first 
	 * SRA_EntityViewProcessor
	 * @type SRA_EntityViewProcessor[]
	 */
	var $_viewProcessors;
	
	/**
	 * used override the default value for an attribute. indexed by attribute name 
	 * @type array
	 */
	var $_viewValues;
	
  // }}}
  
  // {{{ Operations
  // constructor(s)
	// {{{ SRA_EntityView
	/**
	 * Constructor - does nothing
   * @access  public
	 */
	function SRA_EntityView($id, $attributes, $default, $extends, $fieldName, $forwardView, $idConstraint, $isAttribute, $iterate, 
										 $lookupProcessor, $lpNoResultsMsg, $lpNoResultsTpl, 
										 $mimeType, $multiplictyAttr, $params, $post, $postTemplate, 
										 $pre, $preTemplate, $renderAttributes, $skipAttributes, $template, 
										 $viewHeaders, $viewLabels, $viewProcessors, 
										 $viewValues) {
		$this->_id = $id;
		if (strstr($attributes, ENTITY_VIEW_CODE_PREFIX)) {
			$this->_attributesCode = str_replace(ENTITY_VIEW_CODE_PREFIX, '', $attributes);
		}
		if (trim($attributes) && !is_array($attributes)) {
			$this->_attributes = explode(' ', $attributes);
		}
		else if (is_array($attributes)) {
			$this->_attributes = $attributes;
		}
		$this->_default = $default === TRUE || $default == '1';
		$this->_extends = $extends;
    $this->_fieldName = $fieldName;
		$this->_forwardView = $forwardView;
    $this->_idConstraint = $idConstraint;
		$this->_isAttribute = $isAttribute === TRUE || $isAttribute == '1';
		if ($iterate === '1') {
			$this->_iterate = TRUE;
		}
		if ($lookupProcessor && ($path = SRA_File::getRelativePath(FALSE, $lookupProcessor . '.' . SRA_SYS_PHP_EXTENSION))) {
			$this->_lookupProcessor = $path;
			$this->_lookupProcessorClass = basename($lookupProcessor);
			$this->_lpNoResultsMsg = $lpNoResultsMsg;
			$this->_lpNoResultsTpl = $lpNoResultsTpl;
		}
    else if ($lookupProcessor) {
      $msg = "SRA_EntityView::SRA_EntityView: Warning: Path to ${lookupProcessor} could not be found";
      SRA_Error::logError($msg, __FILE__, __LINE__);
    }
		if ($mimeType) {
			$this->_viewHeaders['Content-type: ' . $mimeType] = new SRA_ViewHeader('Content-type: ' . $mimeType, TRUE);
		}
		$this->_multiplictyAttr = $multiplictyAttr;
		if ($params) {
			$this->_params = new SRA_Params($params);
		}
    $this->_post = $post;
		$this->_postTemplate = $postTemplate;
    $this->_pre = $pre;
		$this->_preTemplate = $preTemplate;
		$this->_renderAttributes = $renderAttributes === TRUE || $renderAttributes == '1';
		if (trim($skipAttributes) && !is_array($skipAttributes)) {
			$this->_skipAttributes = explode(' ', $skipAttributes);
		}
		else if (is_array($skipAttributes)) {
			$this->_skipAttributes = $skipAttributes;
		}
		$this->_template = $template;
		if ($isAttribute) {
			$this->_templateVar = ATTRIBUTE_VIEW_TEMPLATE_VAR_NAME;
		}
		if (is_array($viewHeaders)) {
			$keys = array_keys($viewHeaders);
			foreach ($keys as $key) {
				if (!SRA_ViewHeader::isValid($viewHeaders[$key])) {
					$viewHeaders[$key] = new SRA_ViewHeader($key, $viewHeaders[$key]['attributes']['replace'], $viewHeaders[$key]['attributes']['response-code']);
				}
				$this->_viewHeaders[$key] = $viewHeaders[$key];
			}
		}
		if (is_array($viewLabels)) {
			$this->_viewLabels = array();
			$keys = array_keys($viewLabels);
			foreach ($keys as $key) {
				if (isset($viewLabels[$key]['attributes']['label'])) {
					$viewLabels[$key] = $viewLabels[$key]['attributes']['label'];
				}
				$this->_viewLabels[$key] = $viewLabels[$key];
			}
		}
		if (is_array($viewProcessors)) {
			$this->_viewProcessors = array();
			$keys = array_keys($viewProcessors);
			foreach ($keys as $key) {
				if (!SRA_EntityViewProcessor::isValid($viewProcessors[$key])) {
					$viewProcessors[$key] = new SRA_EntityViewProcessor($key, $viewProcessors[$key]['attributes']['path'], $viewProcessors[$key]['attributes']['args'], 
																										$viewProcessors[$key]['attributes']['input-view'], $viewProcessors[$key]['attributes']['output-file-path'], 
																										$viewProcessors[$key]['attributes']['post-process-cmd'], $viewProcessors[$key]['attributes']['pre-process-cmd']);
				}
				$this->_viewProcessors[$key] = $viewProcessors[$key];
			}
		}
		if (is_array($viewValues)) {
			$this->_viewValues = array();
			$keys = array_keys($viewValues);
			foreach ($keys as $key) {
				if (isset($viewValues[$key]['attributes']['value'])) {
					$viewValues[$key] = $viewValues[$key]['attributes']['value'];
				}
				$this->_viewValues[$key] = $viewValues[$key];
			}
		}
	}
	// }}}
	
  
  // public operations
	
	// {{{ getAttributes()
	/**
	 * returns the attributes that should be displayed for this view if applicable
	 * this also removes any skip attributes
	 *
	 * @access	public
	 * @return	String[]
	 */
	function getAttributes($skipAttributes = FALSE) {
		
		
		$attributes = array();
		
		if (is_array($this->_attributes)) {
			foreach ($this->_attributes as $attr) {
				$add = TRUE;
				if (is_array($this->_skipAttributes)) {
					foreach ($this->_skipAttributes as $sattr) {
						if ($attr == $sattr) {
							$add = FALSE;
							break;
						}
					}
				}
				
				if ($add) {
					$attributes[] = $attr;
				}
			}
		}
		
		if (count($attributes)) {
			return $attributes;
		}
		else {
			return FALSE;
		}
	}
	// }}}
	
	// {{{ getLabel()
	/**
	 * returns the label for a given key if applicable
	 *
	 * @param string $key the key to return the label for
	 * @access	public
	 * @return	String or FALSE
	 */
	function getLabel($key) {
		if (is_array($this->_viewLabels) && isset($this->_viewLabels[$key])) {
			return $this->_viewLabels[$key];
		}
		return FALSE;
	}
	// }}}
	
	// {{{ getValue()
	/**
	 * returns the value for a given key if applicable
	 *
	 * @param string $key the key to return the value for
	 * @access	public
	 * @return	String or FALSE
	 */
	function getValue($key) {
		if (is_array($this->_viewValues) && isset($this->_viewValues[$key])) {
			return $this->_viewValues[$key];
		}
		return FALSE;
	}
	// }}}
	
	
	// {{{ hasViewProcessors()
	/**
	 * returns TRUE if this view has any _viewProcessors
	 *
	 * @access	public
	 * @return	boolean
	 */
	function hasViewProcessors() {
		return (is_array($this->_viewProcessors) && count($this->_viewProcessors));
	}
	// }}}
	
	
	// Static methods
	
	// {{{ getGlobalFieldNamePostfix()
	/**
	 * Static method used to returned a global fieldname postfix set by another 
	 * process. this is used for rendering entities and will cause all of the 
	 * field names in that rendering process to be postfixed by this value. the
	 * field name postfix is only applied when using an $entity->render method 
	 *
	 * @param  string $name only used when setting the global field name
	 * @access	public
	 * @return	string
	 */
	function getGlobalFieldNamePostfix($name = FALSE) {
		static $postfix = '';
		if ($name !== FALSE) {
			$postfix = $name;
		}
		return $postfix;
	}
	// }}}
	
	// {{{ getGlobalFieldNamePrefix()
	/**
	 * Static method used to returned a global fieldname prefix set by another 
	 * process. this is used for rendering entities and will cause all of the 
	 * field names in that rendering process to be prefixed by this value. the
	 * field name prefix is only applied when using an $entity->render method 
	 *
	 * @param  string $name only used when setting the global field name
	 * @access	public
	 * @return	string
	 */
	function getGlobalFieldNamePrefix($name = FALSE) {
		static $prefix = '';
		if ($name !== FALSE) {
			$prefix = $name;
		}
		return $prefix;
	}
	// }}}
	
	
	// {{{ isValid()
	/**
	 * Static method that returns true if the object parameter is a SRA_EntityView object.
	 *
	 * @param  Object $object The object to validate
	 * @access	public
	 * @return	boolean
	 */
	function isValid( & $object ) {
		return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_entityview');
	}
	// }}}
	
	
	// {{{ mergeExtends()
	/**
	 * Static method that returns true if the object parameter is a SRA_EntityView object.
	 *
	 * @param  SRA_EntityView[] $views the base views
	 * @param  SRA_EntityView[] $globals the global views to merge with
	 * @access	public
	 * @return	void
	 */
	function mergeExtends(& $views, & $globals) {
		$keys = array_keys($views);
		foreach ($keys as $key) {
			if ($views[$key]->_extends) {
				$ekeys = explode(' ', $views[$key]->_extends);
				foreach ($ekeys as $ekey) {
					if (isset($globals[$ekey])) {
						SRA_Util::mergeObject($views[$key], $globals[$ekey]);
					}
				}
			}
		}
	}
	// }}}
	
	
	// {{{ setDefaultView()
	/**
	 * sets a default view out of an array of SRA_EntityView objects if one has not 
	 * already been designated. if multiple default views are designated, only 
	 * the first will be designated in the views that are returned
	 *
	 * @param  SRA_EntityView[] $views
	 * @access	public
	 * @return	void
	 */
	function setDefaultView(& $views) {
		$keys = array_keys($views);
		$defaultFound = FALSE;
		foreach ($keys as $key) {
			if ($views[$key]->_default) {
				if (!$defaultFound) {
					$defaultFound = TRUE;
				}
				else {
					$views[$key]->_default = FALSE;
				}
			}
		}
		if (!$defaultFound) {
			$views[$keys[0]]->_default = TRUE;
		}
	}
	// }}}
	
  
  // private operations

  
}
// }}}
?>
