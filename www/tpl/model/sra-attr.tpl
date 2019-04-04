{if $params->getParam('stripNewlines', 0)}{assign var=outputBuffered value=$Template->startBuffering()}{/if}
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
Base template for rendering an entity attribute. provides a very flexible means 
of describing how that attribute should be rendered including placement of the 
label/value, html formatting, etc. where additional formatting is necessary, the 
"tpl" param type can be used to render the attribute,label, etc, in a template 
that provides that additional formatting functionality. for example, to render 
an attribute in an input field rather than as straight text, a "tpl" type param 
with a key of "value" and value of "sra-form-text.tpl" could be specified


PARAM KEY DEFINITIONS:
[attr]:       html attribute
[element id]: element identifier. uses the following format ([-[property]] is optional): 
               [element tag][-[property]]-attrs
[cycle]:      cycle identifier - these identifiers are shared accross every 
              output that utilizes this template in a single http request process
[property]:   property of the output. one of the following:
               arrayVal:  if the attribute value is an array, this is the name of 
							            the property for each value in that array
               fieldName: the field name for the attribute if rendered in a form element
						   index:     the attribute index
						   label:     the attribute label
							 name:      the entity name for the attribute
               optionLabel: if this attribute value represents an option. the 
                          label for that option may be displayed instead of the 
                          option value. in order to display an optionLabel, the 
                          array from $entity->getOptionsMap must return an index 
                          value equal to the value of the attribute
               output:    the entire output of this template
						   value:     the attribute value


PARAMS:
id              type          value/default    Description

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
																							 the value of this and all other parameters is 
																							 passed through $entity->parseString if it 
																							 is prefixed with "parse:" and can thus contain 
																							 imbedded attribute identifiers as well as 
																							 "{$renderAttributeName}" or "{$renderEntityName}" 
																							 which will be substituted
																							 for the currently rendered attribute/entity name. 
																							 additionally, if the value contains "[$VAL]", that 
																							 value will be substituted with the value of the 
																							 property
																							 
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
                                               
addLineBreaks                 (0|1)/0          set to true (1) for line breaks (\n) in the 
                                               attribute to be automatically replaced with 
                                               <br /> tags
                                               
addLineBreaksElement          [str]/"br"       use this to customize the line break element 
                                               to use. the default is <br />
                                               
between                       [html/text]      html/text to display between arrayVals
                                               
decimals                      [int]/NULL       if the value/arrayVal is a floating point number, this 
                                               parameter may be specified to explicitely state how many 
                                               decimal places should be displayed in the output. this 
                                               parameter only applies when this template is reponsible for 
                                               the value/arrayVal output
                                               
convertHtml                   (0|1)/0          whether or not the value/arrayVal values should be 
                                               converted to html using the PHP htmlspecialchars function. 
                                               when true the following conversions will occur 
                                               automatically:
                                                 '&' (ampersand) becomes '&amp;'
                                                 double quote becomes '&quot;'
                                                 single quote becomes '&#039;'
                                                 '<' (less than) becomes '&lt;'
                                                 '>' (greater than) becomes '&gt;'
                                               the same behavior can be accomplished by setting the 
                                               constant SRA_CONVERT_OUTPUT_TO_HTML to TRUE. this 
                                               parameter only applies when this template is reponsible for 
                                               the value/arrayVal output
																							 
maxEnclElems    encl          (1-N)/5          the max # of possible "encl" parameters for a 
                                               [property]
																							 
[method]        invoke[-arrayVal] [param]      if the display value for the attribute specified 
                                               is different than the default value (see "tpl" 
																							 type description for default property values), 
																							 AND the property value is an object, this parameter 
																							 can be specified. it will be passed to the 
																							 $attribute->[method]([param]) method and the 
																							 return value displayed. [param] is optional and 
																							 will be passed to the method call as the first 
																							 parameter value. multiple methods can be specified 
																							 each of which will be invoked on the results of 
																							 the last invoked method and in the order specified. 
                                               the [-arrayVal] can be used to specify that the methods 
                                               should be called on each object value in an attribute
                                               consisting of an array of objects
																							 
[property][val] displayConds  [formatStr]      used to apply conditional display logic for a 
                                               property. if a parameter [property][val] is 
																							 specified, where [val] is the value of 
																							 the property, then the value of that parameter 
																							 passed to $entity->parseString() will be 
																							 rendered instead for that property. for boolean 
																							 property values, if value is a boolean and "[property]TRUE" 
																							 and "[property]FALSE" are specified, then if the property 
																							 value is a boolean (as determined by Util::isBoolean) 
																							 and equals FALSE (as determined by Util::convertBoolean)
																							 then "[property]FALSE" will be used. If it is a boolean 
																							 and equals TRUE then "[property]TRUE" will be used.
																							 "[property]NULL" can also be specified to determine 
																							 the text that should be displayed if the value is NULL
																							 (or not set) and "[property]SET" can be used to 
																							 specify the text to display if the value is set 
																							 to anything and "[property]NOTSET" can be used to 
                                               specify the text to display if the value evaluates
                                               to FALSE (0, empty string, NULL, empty array, etc.). 
                                               If "[property]NULL" is specified, and 
																							 the value is NULL and a "formatStr" has been 
																							 specified for the property, ONLY the "[property]NULL" 
																							 resource will be displayed
																							 
[property]      formatStr                      if the display value for the property specified 
                                               is different than the default value (see "tpl" 
																							 type description for default property values), 
																							 this parameter can be specified. it will be 
																							 passed to the $entity->parseString() method and 
																							 the return value displayed
                                               
[property][N]   encl          [html element]   html element(s) that the property specified should 
                                               be enclosed in. [N] may be blank, or a value between 
																							 1 and "maxEnclElems", where just "[property]" will 
																							 be rendered first, followed by "[property]1", 
																							 "[property]2", ... "[property][maxEnclElems]" 
                                               
[property][N]   enclClose     (0|1)/0          whether or not the "encl" element specified, should 
                                               be quick closed in which case the "property" will be 
                                               displayed to the right of that element instead of 
                                               between the open and close tags for it.
                                               
[property]      pos           (0|1|2|3|4|5)/   the relative placement for the property specified
                              fieldName=0      where this value signifies the order in which the 
                              index=0          property should be rendered. if the pos value for 
                              label=0          a given property is 0, then that property will not 
														  name=0           be rendered. the default values for each property 
                              optionLabel=0    type are provided to the left. Not applicable for 
														  value=3          "output" or "arrayVal" properties

[property]      post          [html/text]      html/text to display after the property specified

[property]      postTpl       [template name]  template to display after the property specified.
                                               templates are rendered after any post html/text

[property]      pre           [html/text]      html/text to display before the property specified

[property]      preTpl        [template name]  template to display before the property specified.
                                               templates are rendered before any post html/text

[property]      tpl           [template name]  an optional template to use to render the property 
                                               specified. if not specified, the property is 
																							 rendered as follows:
																							  index:  numerical index value for the attribute
																								label:  default label defined for the attribute
																								output: NA
																								value:  if the value is an array, then each element 
																								        of that array will be rendered separately
																												(elements must be scalar or object). 
																												if the value is scalar, it will be rendered 
																												as-is. if the value is an object, it will be 
																												rendered by calling the "render" method if it 
																												exists, or the "toString" method if it exists, 
																												Util::objectToString otherwise. arrays may be 
																												nested, for each element in the array, the 
																												same rendering output conditions mentioned 
																												here will apply (i.e. array, object, scalar 
																												default formatting)
                                                        
stripNewlines                 (0|1)/0          set to true (1) to have newline characters stripped 
                                               from the output of this template
                                               
useOptionsLabel               (0|1)/0          for array attributes that use options only. setting this 
                                               to true will result in the option label being displayed 
                                               instead of the option value

*}

{assign var="attrParams" value=$Template->getVar('params')}
{assign var="attrTplName" value="sra-attr"}
{$Template->initTemplate($attrTplName)}

{assign var="displayCondsParams" value=$params->getTypeSubset('displayConds')}
{assign var="formatStrParams" value=$params->getTypeSubset('formatStr')}
{assign var="enclParams" value=$params->getTypeSubset('encl')}
{assign var="enclCloseParams" value=$params->getTypeSubset('enclClose')}
{assign var="invokeParams" value=$params->getTypeSubset('invoke')}
{assign var="invokeArrayValParams" value=$params->getTypeSubset('invoke-arrayVal')}
{assign var="lineBreakParams" value=$params->getTypeSubset('lineBreaks')}
{assign var="posParams" value=$params->getTypeSubset('pos')}
{assign var="postParams" value=$params->getTypeSubset('post')}
{assign var="postTplParams" value=$params->getTypeSubset('postTpl')}
{assign var="preParams" value=$params->getTypeSubset('pre')}
{assign var="preTplParams" value=$params->getTypeSubset('preTpl')}
{assign var="tplParams" value=$params->getTypeSubset('tpl')}
{assign var="maxEnclElems" value=$enclParams->getParam('maxEnclElems', 5)}
{$Template->assignByRef('displayCondsParams', $displayCondsParams)}
{$Template->assignByRef('formatStrParams', $formatStrParams)}
{$Template->assignByRef('enclParams', $enclParams)}
{$Template->assignByRef('invokeParams', $invokeParams)}
{$Template->assignByRef('posParams', $posParams)}
{$Template->assignByRef('postParams', $postParams)}
{$Template->assignByRef('postTplParams', $postTplParams)}
{$Template->assignByRef('preParams', $preParams)}
{$Template->assignByRef('preTplParams', $preTplParams)}
{$Template->assignByRef('tplParams', $tplParams)}
{* add line breaks *}
{if $params->getParam('addLineBreaks')}{assign var='attribute' value=$Template->lineBreaksToBr($attribute, $attrParams->getParam('addLineBreaksElement', 'br'))}{/if}

{* pre output templates, text/html *}
{if $preTplParams->getParam('output')}{$Template->display($preTplParams->getParam('output'))}{/if}
{$preParams->getParam('output')}

{* output enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value="output"|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($attrTplName, $encl, $attrParams, 'output')}{/if}
{/foreach}


{foreach from=$Util->getArray(5) item=idx}
{assign var="property" value=""}
{assign var="displayVal" value=""}
{if $idx eq $posParams->getParam('fieldName')}{assign var="property" value="fieldName"}{assign var="displayVal" value=$fieldName}{/if}
{if $idx eq $posParams->getParam('index')}{assign var="property" value="index"}{assign var="displayVal" value=$attributeIndex}{/if}
{if $idx eq $posParams->getParam('label')}{assign var="property" value="label"}{assign var="displayVal" value=$attributeLabel}{/if}
{if $idx eq $posParams->getParam('name')}{assign var="property" value="name"}{assign var="displayVal" value=$attributeName}{/if}
{if $idx eq $posParams->getParam('optionLabel')}
{assign var="property" value="optionLabel"}
{assign var="options" value=$entity->getOptionsMap($attributeName, 1)}
{if $options[$attribute]}{assign var="displayVal" value=$options[$attribute]}{else}{assign var="displayVal" value=$attribute}{/if}
{/if}
{if $idx eq $posParams->getParam('value', '3')}
{assign var="property" value="value"}
{assign var="displayVal" value=$attribute}
{if $invokeParams->getParams()}
{foreach from=$invokeParams->getParams() key=method item=p}
{assign var="displayVal" value=$Util->invokeMethod($displayVal, $method, $p)}
{/foreach}
{/if}
{/if}

{if $property}

{* check for display condition *}
{assign var="condCode" value=$property|cat:$displayVal}
{assign var="condCodeF" value=$property|cat:"FALSE"}
{assign var="condCodeT" value=$property|cat:"TRUE"}
{assign var="condCodeNull" value=$property|cat:"NULL"}
{assign var="condCodeSet" value=$property|cat:"SET"}
{assign var="condCodeNotSet" value=$property|cat:"NOTSET"}
{assign var="displayedNull" value="0"}

{* if display condition exists, then change the display value based on the value for that condition *}
{if $displayCondsParams->getParam($condCode)}
{assign var="tmp" value=$displayCondsParams->getParam($condCode)}
{assign var="displayVal" value=$entity->parseString($tmp)}

{* if boolean display condition exists, then use the appropriate display value *}
{elseif $Util->isBoolean($displayVal) && ($displayCondsParams->getParam($condCodeF) || $displayCondsParams->getParam($condCodeT))}
{if $Util->convertBoolean($displayVal) && $displayCondsParams->getParam($condCodeT)}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeT)}
{assign var="displayVal" value=$entity->parseString($tmp)}
{elseif !$Util->convertBoolean($displayVal) && $displayCondsParams->getParam($condCodeF)}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeF)}
{assign var="displayVal" value=$entity->parseString($tmp)}
{/if}

{* NULL display conditions *}
{elseif $displayCondsParams->getParam($condCodeNull) && !$Util->isBoolean($displayVal) && !$displayVal}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeNull)}
{assign var="displayVal" value=$entity->parseString($tmp)}
{assign var="displayedNull" value="1"}

{* SET display conditions *}
{elseif $displayCondsParams->getParam($condCodeSet) && $displayVal}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeSet)}
{assign var="displayVal" value=$entity->parseString($tmp)}

{* NOTSET display conditions *}
{elseif $displayCondsParams->getParam($condCodeNotSet) && !$displayVal}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeNotSet)}
{assign var="displayVal" value=$entity->parseString($tmp)}
{/if}

{* pre property templates, text/html *}
{if $preTplParams->getParam($property)}{$Template->display($preTplParams->getParam($property))}{/if}
{$preParams->getParam($property)}

{* property enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{if !$enclCloseParams->getParam($tmp)}{$Template->renderOpen($attrTplName, $encl, $attrParams, $property, 1, $displayVal)}{else}{$Template->renderOpen($attrTplName, $encl, $attrParams, $property, 0, $displayVal)} />{/if}{/if}
{/foreach}

{* property value *}
{assign var="formatString" value=$formatStrParams->getParam($property)}

{* display using template *}
{if $tplParams->getParam($property)}{assign var='displayVal' value=$displayVal}{$Template->display($tplParams->getParam($property))}

{* display using entity parseString call *}
{elseif $displayedNull eq "0" && $formatString}{$entity->parseString($formatString)}

{* display in raw form *}
{else}

{* check for object methods *}
{if $property eq "value" && ($Template->isObject($displayVal) || $Template->isArray($displayVal))}
{$Template->assignByRef('sraAttrVal', $displayVal)}
{$Template->display('model/sra-attr-value.tpl')}
{else}{if $attrParams->getParam('convertHtml') || ($Template->defined('SRA_CONVERT_OUTPUT_TO_HTML') && $smarty.const.SRA_CONVERT_OUTPUT_TO_HTML)}{assign var="displayVal" value=$displayVal|escape:'html'}{/if}{if $attrParams->getParam('decimals') || $attrParams->getParam('decimals') === '0'}{assign var="displayVal" value=$Template->numberFormat($displayVal, $attrParams->getParam('decimals'))}{/if}{$displayVal}
{/if}
{/if}

{* property enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl && !$enclCloseParams->getParam($tmp)}</{$encl}>{/if}
{/foreach}

{* post property templates, text/html *}
{$postParams->getParam($property)}
{if $postTplParams->getParam($property)}{$Template->display($postTplParams->getParam($property))}{/if}

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
{if $postTplParams->getParam('output')}{$Template->display($postTplParams->getParam('output'))}{/if}
{assign var='fieldNamePre' value=''}
{assign var='fieldNamePost' value=''}
{if $outputBuffered}{$Template->stopBuffering(1)}{/if}
