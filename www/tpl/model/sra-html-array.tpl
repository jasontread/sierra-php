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
Displays an array attribute

PARAMS:
Key          Type     Default     Value     Desc

listType                          (ul|ol)   if attributes are being displayed in 
                                            a list, defines the type of list to 
																						use
																						
listClass                                   if attributes are being displayed in 
                                            a list, defines an optional class to 
																						apply to that list
																						
listId                                      if attributes are being displayed in 
                                            a list, defines an optional id to 
																						apply to that list
																						
listClass                                   an optional class to apply to the list
																						
listCycle                                   an optional cycle name to apply (if 
                                            list cycles are used). See 
																						"enclElemCycle" comments below for 
																						more information
																						
listClassTag          class                 an alternate attribute tag to express "class"
                                            in the enclosing "listType" element
                                            See "enclElemClassTag" comments below 
																						for more information

displStr                                    if this attribute is an entity, 
                                            this parameter defines the parse str 
																						that should be used as the display 
																						value for it. if this parameter is 
																						specified, then the attribute MUST 
																						be an entity or DateTime object or 
																						a runtime exception will occur
                                            

enclElem                          [html el] an optional html element tag that should 
                                            be used to enclose this attribute. 
																						for example, to enclose the attribute 
																						in a table cell, this param would be 
																						"td" and  the corresponding open and 
																						close <td></td> elements would be used 
																						to enclose the attribute
																						
enclElemClass                               an optional css class that should be 
                                            specified for the enclElem. this may 
																						be a range of values that should be 
																						cycled through, once for each instance 
																						of the enclElem open tag. if this is 
																						the case, the different classes should 
																						be comma separated (e.g. "myclass1,myclass2")
																						
enclElemCycle                               an optional cycle name to apply (if 
                                            enclElemClass cycles are used). this 
																						will uniquely identify the cycle where 
																						other duplicate cycles exist that would 
																						otherwise interfere. otherwise, the 
																						name will be the value specified in 
																						'enclElemClass'
																						
enclElemClassTag      class                 if an enclElem attribute other than 
                                            "class" should be used to express the 
																						"enclElemClass" value, then this 
																						parameter should define that attribute 
																						tag. For example, if instead of alternating 
																						specifying a "class" you wished to bypass 
																						css and just specify a bgcolor, the 
																						value of this parameter would be "bgcolor"
																						
linked                 0          (0|1)     specify 1 for this parameter if the 
                                            attribute should be wrapped in an 
																						html link tag
																						
linkClass                                   an optional css class for the link
																						
linkCycle                                   an optional cycle name for the linkClass
																						
linkClassTag          class                 an alternate attribute tag to express "linkClass"
                                            in the enclosing "a" element
                                            See "enclElemClassTag" comments below 
																						for more information
																						
linkDisplStr                                if the attribute is an entity, this 
                                            defines the parse string for the visible 
																						text in the link whereas the displStr 
																						defines the actual link. if this 
																						param is not specified, then displStr 
																						will be used for both
																						
linkResource                                if the actual attribute value should 
                                            not be displayed, this param defines 
																						a resource value that should in its 
																						place. otherwise, the attribute value 
																						will be used for both link and display 
																						values
																						
linkPre                                     an optional link prefix to add such 
                                            as "mailto:" if the attribute is an 
																						email address
																						
isDate                0           (0|1)     whether or not this attribute is a 
                                            Date. if True, then the displStr 
																						should be the SRA_GregorianDate::format($str)
																						$str value
																						
isBool                0           (0|1)     whether or not this is a boolean 
                                            attribute. if True, then labelTrue 
																						or labelFalse will be displayed instead 
																						of the actual boolean value 1 or 0
																						
labelTrue             form.yes              an application or system resource 
                                            bundle key identifying the label 
																						that should be displayed for a TRUE
																						value corresponding to the attribute.
																						may alternatively be html code for 
																						rending an image or other non-text
																						value

labelFalse            form.no               an application or system resource 
                                            bundle key identifying the label 
																						that should be displayed for a FALSE
																						value corresponding to the attribute.
																						may alternatively be html code for 
																						rending an image or other non-text
																						value

labelTpl                          [tpl]     if the label is displayed, it may be 
                                            rendered using a template. this is 
                                            optional as the default behavior is 
                                            to simply display the label according 
																						to the label characteristics specified
																						(labelPre, labelPost, labelPos, 
																						labelFor and labelClass). the label 
																						value is stored in the template 
																						variable [$attributeLabel]

labelPre                                    html/text to display before the label                      

labelPost             &nbsp;                html/text to display after the label

labelPos              0           (0|1|2|3) the relative placement for the label. 
                                            there are up to 3 ordered positions 
																						for this template: label, attr value,
																						and attr index. this 
																						value defines where the label should 
																						be displayed relative to the other 
																						3 elements. NOTE: ALL POSITIONS MUST 
																						BE UNIQUE. IF YOU CHANGE ONE, YOU 
																						MUST CHANGE THEM ALL SUCH THAT NO 
																						DUPLICATE POSITIONS WILL EXIST BETWEEN 
																						EACH OF THE ELEMENTS.

labelClass                                  an optional font class to apply to 
                                            the label 
																						(e.g. <font class="[labelClass]">[label]</font>)
																						
labelCycle                                  an optional cycle name to apply (if 
                                            labelClass cycles are used). See 
																						"enclElemCycle" comments above for 
																						more information
																						
labelClassTag         class                 an alternate attribute tag to express "labelClass"
                                            in the enclosing "font" element
                                            See "enclElemClassTag" comments above 
																						for more information

pre                                         html/text to display before each attribute value

post                  &nbsp;                html/text to display after each attribute value

pos                   3           (1|2|3)   the relative placement for the attribute value
                                            See 'labelPos' comnent for more info
																						
class                                       an optional class to apply to 
                                            each attribute value in the form of an 
																						enclosing font class
																						
cycle                                       an optional cycle name to apply (if 
                                            class cycles are used). See 
																						"enclElemCycle" comments above for 
																						more information
																						
classTag              class                 an alternate attribute tag to express "class"
                                            in the enclosing "input" element
                                            See "enclElemClassTag" comments above 
																						for more information

indexTpl                          [tpl]     if the index is displayed, it may be 
                                            rendered using a template. see 
																						'labelTpl' comments for more info. 
																						the index value is stored in the 
																						template variable [$attributeIndex]

indexPre                                    html/text to display before the index

indexPost             &nbsp;                html/text to display after the index

indexPos              0           (0|1|2|3) the relative placement for the attr index
                                            See 'labelPos' comnent for more info

indexClass                                  an optional font class to apply to 
                                            the attribute index
																						(e.g. <font class="[indexClass]">[attribute index]</font>)
																						
indexCycle                                  an optional cycle name to apply (if 
                                            indexClass cycles are used). See 
																						"enclElemCycle" comments above for 
																						more information
																						
indexClassTag         class                 an alternate attribute tag to express "indexClass"
                                            in the enclosing "font" element
                                            See "enclElemClassTag" comments above 
																						for more information

*}

{* custom settings *}
{if $params->getParam('enclElem')}
{assign var="cycleName" value=$params->getParam('enclElemCycle', $params->getParam('enclElemClass'))}
<{$params->getParam('enclElem')}{if $params->getParam('enclElemClass')} {if $params->getParam('enclElemClassTag')}{$params->getParam('enclElemClassTag')}{else}class{/if}="{cycle name=$cycleName values=$params->getParam('enclElemClass')}"{/if}>
{/if}
{assign var="labelPos" value=$params->getParam('labelPos')}
{assign var="indexPos" value=$params->getParam('indexPos')}
{assign var="pos" value=$params->getParam('pos', 3)}

{foreach from=$Util->getArray(3) item=idx}
{* label *}
{if $labelPos eq $idx}
{$params->getParam('labelPre', '')}
{assign var="cycleName" value=$params->getParam('labelCycle', $params->getParam('labelClass'))}
{if $params->getParam('labelClass')}<font {if $params->getParam('labelClassTag')}{$params->getParam('labelClassTag')}{else}class{/if}="{cycle name=$cycleName values=$params->getParam('labelClass')}">{/if}
{if $params->getParam('labelTpl')}{include file=$params->getParam('labelTpl')}{else}{$attributeLabel}{/if}
{if $params->getParam('labelClass')}</font>{/if}
{$params->getParam('labelPost', '&nbsp;')}

{* index *}
{elseif $indexPos eq $idx && $attributeIndex}
{$params->getParam('indexPre', '')}
{assign var="cycleName" value=$params->getParam('indexCycle', $params->getParam('indexClass'))}
{if $params->getParam('indexClass')}<font {if $params->getParam('indexClassTag')}{$params->getParam('indexClassTag')}{else}class{/if}="{cycle name=$cycleName values=$params->getParam('indexClass')}">{/if}
{if $params->getParam('indexTpl')}{include file=$params->getParam('indexTpl')}{else}{$attributeIndex}{/if}
{if $params->getParam('indexClass')}</font>{/if}
{$params->getParam('indexPost', '&nbsp;')}

{* attribute value *}
{elseif $pos eq $idx}
{assign var="cycleName" value=$params->getParam('listCycle', $params->getParam('listClass'))}
{if $params->getParam('listType')}<{$params->getParam('listType')}{if $params->getParam('listClass')}{if $params->getParam('listClassTag')}{$params->getParam('listClassTag')}{else}class{/if}="{cycle name=$cycleName values=$class}"{/if}>{/if}
{foreach from=$attribute item=attr}
{$params->getParam('pre', '')}
{assign var="class" value=$params->getParam('class')}
{assign var="cycleName" value=$params->getParam('cycle', $class)}
{if $class || $params->getParam('listType')}<{if $params->getParam('listType')}li{else}font{/if}{if $class} {if $params->getParam('classTag')}{$params->getParam('classTag')}{else}class{/if}="{cycle name=$cycleName values=$class}"{/if}>{/if}
{if $params->getParam('linked') eq '1' && $attr && (!$params->getParam('displStr') || ($params->getParam('displStr') && $attr->parseString($params->getParam('displStr'))))}
{assign var="linkCycleName" value=$params->getParam('linkCycle', $params->getParam('linkClass'))}
<a{if $params->getParam('linkClass')}{if $params->getParam('linkClassTag')} {$params->getParam('linkClassTag')}{else} class{/if}="{cycle name=$linkCycleName values=$params->getParam('linkClass')}"{/if} href="{$params->getParam('linkPre', '')}{if $params->getParam('displStr')}{$attr->parseString($params->getParam('displStr'))}{else}{$attr}{/if}">
{/if}
{if $params->getParam('isDate')}
{if $attr}
{assign var="tmp" value=$params->getParam('displStr')}
{assign var="tmp" value=$attr->format($params->getParam('linkDisplStr', $tmp))}
{$tmp}
{/if}
{elseif $params->getParam('displStr')}
{if $attr}
{assign var="tmp" value=$params->getParam('displStr')}
{assign var="tmp" value=$attr->parseString($params->getParam('linkDisplStr', $tmp))}
{$tmp}
{/if}
{else}
{if $params->getParam('linkResource')}
{$resources->getString($params->getParam('linkResource'))}
{else}
{if $params->getParam('isBool')}
{if $attr}
{$params->getParam('labelTrue', 'form.yes')}
{else}
{$params->getParam('labelFalse', 'form.no')}
{/if}
{else}
{$resources->getString($attr)}
{/if}
{/if}
{/if}
{if $params->getParam('linked') eq '1' && $attr && (!$params->getParam('displStr') || ($params->getParam('displStr') && $attr->parseString($params->getParam('displStr'))))}</a>{/if}
{if $class || $params->getParam('listType')}</{if $params->getParam('listType')}li{else}font{/if}>{/if}
{if $params->getParam('listType')}
{$params->getParam('post', '')}
{else}
{$params->getParam('post', '&nbsp;')}
{/if}
{/foreach}
{if $params->getParam('listType')}</{$params->getParam('listType')}>{/if}
{/if}
{/foreach}

{if $params->getParam('enclElem')}</{$params->getParam('enclElem')}>{/if}

