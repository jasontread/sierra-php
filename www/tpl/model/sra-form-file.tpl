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
template is typically used in conjunction with sra-attr.tpl.


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

showViewLink                 [resource]        if the file exists and this value is 
                                               specified, a link to the file will be 
                                               created, directly below the input field 
                                               using the value as the resource key for 
                                               the link. set this value to [name] to 
                                               use the original file name, [name kb] to 
                                               use the original file name followed by 
                                               the size in kb in parens, or [name mb] to 
                                               use the original file name followed by 
                                               the size in mb in parens. if the file 
                                               does not exist, this label will not be 
                                               linked
                                               
showResetLink                [resource]        if the file exists and this value is 
                                               specified, a link to reset (remove) the 
                                               file will be created, directly below the 
                                               input field using the value as the resource 
                                               key for the link
                                               
useButton                    (1|0)/0           whether or not to display the showView/showReset 
                                               links as a button instead of text
                                               
[field name]   resetFields   [value]           used to specify additional form fields that should 
                                               be set when a reset is invoked (the dom ids)
*}

{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{assign var="resetFields" value=$params->getTypeSubset('resetFields')}

{assign var="fileFieldId" value=$Util->rand(10000, 1000000)}
{assign var="fileFieldId" value='fid'|cat:$fileFieldId}
{$Template->renderOpen($tplName, 'input', $params, '', 0)} name="{$fieldName}" type="file" />
{if $Util->isObject($attribute, 'SRA_FileAttribute') && ($params->getParam('showViewLink') || $params->getParam('showResetLink'))}
<div id="{$fileFieldId}">
{if $params->getParam('showViewLink')}
{assign var="fileLink" value=$entity->parseString($params->getParam('showViewLink'))}
{if $Util->beginsWith($fileLink, '[name')}
{assign var="newFileLink" value=$attribute->getName()}
{assign var="newFileLinkPostfix" value=0}
{if $Util->endsWith($fileLink, 'kb]')}{assign var="newFileLinkPostfix" value=$attribute->getSizeKb()|cat:' kb'}{/if}
{if $Util->endsWith($fileLink, 'mb]')}{assign var="newFileLinkPostfix" value=$attribute->getSizeMb()|cat:' mb'}{/if}
{assign var="fileLink" value=$newFileLink}
{if $newFileLinkPostfix}
{assign var="fileLink" value=$fileLink|cat:' ('|cat:$newFileLinkPostfix|cat:')'}
{/if}
{/if}
{if !$attribute->getEntityId()}
{$fileLink}
{elseif !$params->getParam('useButton')}
<a href="{$attribute->getUri()}" target="_blank">{$fileLink}</a>
{else}
<input onclick="document.location.replace('{$attribute->getUri()}')" type="button" value="{$fileLink}" />
{/if}
{/if}
{if $params->getParam('showResetLink')}
{if !$params->getParam('useButton')}
<a href="javascript:{ldelim}{rdelim}" onclick="document.getElementById('{$fileFieldId}').innerHTML = ''; document.getElementById('{$fileFieldId}Remove').value='1'; {foreach from=$resetFields->getParams() key=field item=value}document.getElementById('{$field}').value='{$value}'; {/foreach}">{$entity->getEntityResourcesString($params->getParam('showResetLink'))}</a>
{else}
<input onclick="document.getElementById('{$fileFieldId}').innerHTML = ''; document.getElementById('{$fileFieldId}Remove').value='1'; {foreach from=$resetFields->getParams() key=field item=value}document.getElementById('{$field}').value='{$value}'; {/foreach}" type="button" value="{$entity->getEntityResourcesString($params->getParam('showResetLink'))}" />
{/if}
</div>
<input id="{$fileFieldId}Remove" name="{$fieldName}_remove" type="hidden" value="0" />
{else}
</div>
{/if}
{/if}
