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
Base class used to display an attribute value in a file. This template is 
typically used in conjunction with sra-attr.tpl


PARAMS:
Key            Type          Value/Default     Description

[attr]         [element tag] (value|[cycle])   see sra-attr.tpl - may also be 
                                               used to define the input "type" 
																							 if not the standard "text"
                                               
[cycle]        cycles        [csv cycle vals]  see sra-attr.tpl

link                         [resource]        if the file exists and this value is 
                                               specified, a link to the file will be 
                                               created using the value as the resource key for 
                                               the link. set this value to [name] to 
                                               use the original file name, [name kb] to 
                                               use the original file name followed by 
                                               the size in kb in parens, or [name mb] to 
                                               use the original file name followed by 
                                               the size in mb in parens. if the file 
                                               does not exist, this label will not be 
                                               linked

*}
{assign var="myParams" value=$Template->getVarByRef('params')}
{if $Util->isObject($attribute, 'SRA_FileAttribute')}
{assign var="fileLink" value=$entity->parseString($myParams->getParam('link'))}
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
{else}
<a href="{$attribute->getUri()}">{$fileLink}</a>
{/if}
{/if}
