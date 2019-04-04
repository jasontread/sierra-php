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
Base class used to display an attribute value in a form "input" element. This 
template is typically used in conjunction with sra-attr.tpl


PARAMS:
Key            Type          Value/Default     Description

[attr]         [element tag] (value|[cycle])   see sra-attr.tpl - may also be 
                                               used to define the input "type" 
																							 if not the standard "text"
                                               
[cycle]        cycles        [csv cycle vals]  see sra-attr.tpl
                                               
cols                                           the # of cols for a textarea field (default 20)

fieldName                                      if the form name should not be 
                                               the name of the attribute, the 
																							 actual name should be specified 
																							 using this parameter
																							 
fieldNamePre                                   prefix to add to the input field name. alternatively, 
                                               this value can be specified through a template parameter

fieldNamePost                                  postfix to add to the input field name. alternatively, 
                                               this value can be specified through a template parameter
																							 
imbedValue                   (1|0)/1           whether or not to imbed the current 
                                               attribute value into the 
																							 input/textarea field
                                               
rows                                           the # of rows for a textarea field (default 5)

useTextArea                  (1|0)/0           whether or not to render the 
                                               attribute value in a textarea. the 
																							 default behavior is to render it in 
																							 an "input" element
*}

{assign var="myParams" value=$Template->getVar('params')}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', $Template->getVar('fieldNamePre'))}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', $Template->getVar('fieldNamePost'))}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{if $Util->isObject($attribute) && $displayVal}{assign var="attribute" value=$displayVal}{/if}
{if $Util->methodExists($attribute, 'getPrimaryKey')}{assign var="attribute" value=$attribute->getPrimaryKey()}{/if}
{if $Util->isObject($attribute) && $Util->methodExists($attribute, 'toString')}{assign var="attribute" value=$attribute->format()}{/if}

{* textarea *}
{if $myParams->getParam('useTextArea')}
{$Template->renderOpen($tplName, 'textarea', $myParams, '', 0)} name="{$fieldName}" rows="{$myParams->getParam('rows', 5)}" cols="{$myParams->getParam('cols', 20)}">{if $myParams->getParam('imbedValue', '1')}{$attribute}{/if}</textarea>
{else}
{$Template->renderOpen($tplName, 'input', $myParams, '', 0)} name="{$fieldName}"{if $myParams->getParam('imbedValue', '1')} value="{if $attributeType eq 'boolean'}{if $attribute}1{else if $attribute === $smarty.const.FALSE}0{/if}{else}{$Template->escapeHtmlQuotes($attribute)}{/if}"{/if} />
{/if}
