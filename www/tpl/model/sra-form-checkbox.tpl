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
A simple template used to represent a boolean value as a single checkbox. This 
template is typically used in conjunction with sra-attr.tpl. This representation 
of a boolean value does not allow for NULL values, only TRUE or FALSE


PARAMS:
Key            Type          Value/Default     Description

[attr]         [element tag] (value|[cycle])   see sra-attr.tpl - may also be 
                                               used to define the input "type" 
																							 if not the standard "text"
                                               
[cycle]        cycles        [csv cycle vals]  see sra-attr.tpl

fieldName                                      if the form name should not be 
                                               the name of the attribute, the 
																							 actual name should be specified 
																							 using this parameter
																							 
fieldNamePre                                   prefix to add to the input field name

fieldNamePost                                  postfix to add to the input field name

*}

{assign var="myParams" value=$Template->getVar('params')}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{assign var="onchange" value="this.previousSibling.value=this.checked ? '1' : '0';"}
{$myParams->concat('onchange', $onchange, 'input-attrs', 0)}

<input name="{$fieldName}" type="hidden" value="{if $attribute}1{else}0{/if}" />{$Template->renderOpen($tplName, 'input', $myParams, '', 0)}{if $attribute} checked="checked"{/if} name="{$fieldName}Tmp" type="checkbox" />
