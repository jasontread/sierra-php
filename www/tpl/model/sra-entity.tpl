{*
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
*}

{* 
Base template for rendering an entity. provides a very flexible means 
of describing how that entity should be rendered including html formatting, 
placement and order of the entity propertiues and attributes, and more


PARAM KEY DEFINITIONS:
[attr]:       html attribute
[attr name]:  the name of an attribute belonging to the entity
[element id]: element identifier. uses the following format ([-[property]] is optional): 
               [element tag][-[property]]-attrs
[cycle]:      cycle identifier - these cycles are unique to each instance of this template
[property]:   property of the output. one of the following:
               [attr name]: name of a specific attribute being rendered as defined 
							              in the "attrs" type parameters for this template (see below)
							 attrs      : the entity attributes that should be rendered as part of this view
							 label      : the entity label
							 name       : the name of the entity
               output     : the entire output of this template
[view id]:    the unique identifier for an attribute view as defined in the entity-model 
              xml for the entity


PARAMS:
Key             Type          Value/Default    Description

[attr]          [element id]  (value|[cycle])  optional html element attribute name/value pairs 
                                               such as "class"/"boldText", "align"/"center". these
                                               values will be applied to each instance of the 
                                               corresponding html element. for example, to apply 
                                               attributes to the html "font" element, the type 
                                               for this parameter would be "font-attrs". more 
                                               granular element specification can be performed 
                                               utilizing the optional property type 
                                               identifiers ("[-[property]]"). this value will 
																							 correspond with one of the [property] types 
																							 specified in the Definitions section above.
																							 if the value specified is "{$VAL}", then the 
																							 property value will become the attribute value 
																							 in the element specified

[attr name]     attrs         [view id]        defines the attributes to render as part of this 
                                               entity view, the order in which to render them, 
																							 and an optional [view id] to use when rendering 
																							 them. if the [view id] value is "RAW", the 
																							 attribute will be rendered in its raw form
                                               
[cycle]         cycles        [csv cycle vals] cycles referenced within any of the "[attr]" 
                                               parameters. cycles are defined using a comma 
                                               separated list of values that should be 
                                               cycled through once for every impression 
                                               of the attribute. for example, a cycle 
                                               value "red,blue", would toggle red and blue 
                                               values for each instance of the attr that 
                                               referenced that cycle. to skip a value 
                                               in a cycle, no value should be specified. for 
                                               example, "red,,blue" would cycle through red 
                                               and blue values with one attribute in the 
                                               middle being skipped (the attribute will not 
                                               be rendered at all in that case)
																							 
maxEnclElems    encl          (1-N)/5          the max # of possible "encl" parameters for a 
                                               [property]
																							 
[property]      formatStr                      if the display value for the property specified 
                                               is different than the default value, this 
																							 parameter can be specified. it will be 
																							 passed to the $entity->parseString() method and 
																							 the return value displayed. for [attr name] 
																							 properties, if "formatStr" is specified, it 
																							 will be displayed instead of both the raw attribute 
																							 value and the view (if specified)
                                               
[property][N]   encl          [html element]   html element(s) that the property specified should 
                                               be enclosed in. [N] may be blank, or a value between 
																							 1 and "maxEnclElems", where just "[property]" will 
																							 be rendered first, followed by "[property]1", 
																							 "[property]2", ... "[property][maxEnclElems]" 
                                               
[property]      pos           (0|1|2|3)/       the relative placement for the property specified
                              attrs=2          where this value signifies the order in which the 
                              label=0          property should be rendered. if the pos value for 
                              name=0           a given property is 0, then that property will not 
														                   be rendered. the default values for each property 
														                   type are provided to the left. Not applicable for 
														                   the "[attr name]" and "output" properties

[property]      post          [html/text]      html/text to display after the property specified

[property]      postTpl       [template name]  template to display after the property specified.
                                               templates are rendered after any post html/text

[property]      pre           [html/text]      html/text to display before the property specified

[property]      preTpl        [template name]  template to display before the property specified.
                                               templates are rendered before any post html/text

*}

{assign var="tplName" value="sra-entity"}
{$Template->initTemplate($tplName)}
{assign var="attrsParams" value=$params-getTypeSubset('attrs')}
{assign var="enclParams" value=$params-getTypeSubset('encl')}
{assign var="formatStrParams" value=$params-getTypeSubset('formatStr')}
{assign var="posParams" value=$params-getTypeSubset('pos')}
{assign var="postParams" value=$params-getTypeSubset('post')}
{assign var="postTplParams" value=$params-getTypeSubset('postTpl')}
{assign var="preParams" value=$params-getTypeSubset('pre')}
{assign var="preTplParams" value=$params-getTypeSubset('preTpl')}
{assign var="maxEnclElems" value=$enclParams->getParam('maxEnclElems', 5)}

{* pre output templates, text/html *}
{if $preTplParams->getParam('output')}{include file=$preTplParams->getParam('output')}{/if}
{$preParams->getParam('output')}

{* output enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value="output"|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($tplName, $encl, $params, "output")}{/if}
{/foreach}


{foreach from=$Util->getArray(3) item=idx}
{assign var="property" value=""}
{assign var="displayVal" value=""}
{if $idx eq $posParams->getParam('attrs', 2)}{assign var="property" value="attrs"}{/if}
{if $idx eq $posParams->getParam('label')}{assign var="property" value="label"}{assign var="displayVal" value=$entityLabel}{/if}
{if $idx eq $posParams->getParam('name')}{assign var="property" value="name"}{assign var="displayVal" value=$entitytName}{/if}

{if $property}

{* pre property templates, text/html *}
{if $preTplParams->getParam($property)}{include file=$preTplParams->getParam($property)}{/if}
{$preParams->getParam($property)}

{* property enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($tplName, $encl, $params, $property, 1, $displayVal)}{/if}
{/foreach}

{* property value *}
{assign var="formatString" value=$formatStrParams->getParam($property)}

{* display using entity parseString call *}
{if $formatString}{$entity->parseString($formatString)}

{* display in raw form *}
{else}{$displayVal}{/if}

{* display attributes *}
{if $property eq "attrs"}
{foreach from=$attrsParams->getParams() key=attr item=view}
{assign var="attrVal" value=$entity->getAttribute($attr)}
{* pre attribute value templates, text/html *}
{if $preTplParams->getParam($attr)}{include file=$preTplParams->getParam($attr)}{/if}
{$preParams->getParam($attr)}

{* attribute value enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$attr|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($tplName, $encl, $params, $attr, 1, $attrVal)}{/if}
{/foreach}

{* attribute value *}
{assign var="formatString" value=$formatStrParams->getParam($attr)}

{* display using entity parseString call *}
{if $formatString}{$entity->parseString($formatString)}

{* display in raw form *}
{elseif $view eq "RAW"}{$attrVal}

{* display using view *}
{else}{$entity->renderAttribute($attr, $view)}{/if}

{* attribute value enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$attr|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}</{$encl}>{/if}
{/foreach}

{* post attribute value templates, text/html *}
{$postParams->getParam($attr)}
{if $postTplParams->getParam($attr)}{include file=$postTplParams->getParam($attr)}{/if}
{/foreach}
{/if}

{* property enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}</{$encl}>{/if}
{/foreach}

{* post property templates, text/html *}
{$postParams->getParam($property)}
{if $postTplParams->getParam($property)}{include file=$postTplParams->getParam($property)}{/if}

{/if}
{/foreach}


{* output enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value="output"|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}</{$encl}>{/if}
{/foreach}

{* post output templates, text/html *}
{$postParams->getParam('output')}
{if $postTplParams->getParam('output')}{include file=$postTplParams->getParam('output')}{/if}
