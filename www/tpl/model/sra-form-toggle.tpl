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
This template is used to render a cardinality attribute toggle field as a single 
item drop down list. It can be used in conjunction with an "id-constraint" 
defined within the referenced attribute


PARAMS:
Key            Type           Value/Default     Description

[attr]         [element tag]  (value|[cycle])   see sra-attr.tpl
                                               
[cycle]        cycles         [csv cycle vals]  see sra-attr.tpl

fieldName                                       if the form name should not be 
                                                the name of the attribute, the 
																							  actual name should be specified 
																							  using this parameter
																							 
fieldNamePre                                    prefix to add to the input field name

fieldNamePost                                   postfix to add to the input field name
																							 
firstOption                   [resource]        optional resource identifier to display as the first 
                                                option in the select input field (does not 
																							  apply to radio buttons or checkboxes
                                               
canDelete                     [resource]        this parameter will cause a button to be displayed 
                                                to the right of the field allowing the user to 
                                                delete the selected attribute. this button will 
                                                set the appropriate hidden form value and submit 
                                                the form that this field is a member of. the value 
                                                should be the resource key of the text
                                                to display on the corresponding button
                                                
canCreate                     [resource]        this parameter will cause a button to be displayed 
                                                to the right of the field allowing the user to 
                                                create a new attribute. this button will 
                                                set the appropriate hidden form value and submit 
                                                the form that this field is a member of. the value 
                                                should be the resource key of the text to display 
                                                on the corresponding button
                                                
canView                       [resource]        this parameter will cause a button to 
                                                be displayed to the right of the field allowing the 
                                                user to view that attribute. this button will 
                                                simply submit the form that this field is a member
                                                of. the value should be the resource key of the text
                                                to display on the corresponding button
                                                
deleteConfirm                 [resource]        resource identifier to display in a javascript 
                                                confirm dialog when the user clicks on the delete 
                                                button (only applicable when canDelete is set)
                                                
[field name]   createFields   [value]           used to specify additional form fields that should 
                                                be set to [value] when a new button is clicked
                                                
[field name]   deleteFields   [value]           used to specify additional form fields that should 
                                                be set to [value] when a delete button is clicked
                                                
[field name]   viewFields     [value]           used to specify additional form fields that should 
                                                be set to [value] when the view button is clicked

*}

{assign var="tplName" value="sra-form-toggle"}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{assign var="evalAttribute" value=$Template->getFormValue($fieldName)}
{assign var="options" value=$entity->getOptionsMap($attributeName, 1, 1)}
{if !$params->getParam('firstOption')}{assign var="evalAttribute" value=$options[0]}{/if}

{$Template->renderOpen($tplName, 'select', $params, '', 0)} name="{$fieldName}">
{if $params->getParam('firstOption')}{$Template->renderOpen($tplName, 'option', $params, 0, 0)} value="">{$resources->getString($params->getParam('firstOption'))}</option>{/if}

{foreach from=$options key=attr item=label}
{$Template->renderOpen($tplName, 'option', $params, 'option', 0)}{if $Util->equal($evalAttribute, $attr)} selected="selected"{/if} value="{$Template->escapeHtmlQuotes($attr)}">{$resources->getString($label)}</option>
{/foreach}
{if $params->getParam('canCreate') && $evalAttribute eq 'NEW'}
{$Template->renderOpen($tplName, 'option', $params, 'option', 0)} selected="selected" value="">{$resources->getString($params->getParam('canCreate'))}</option>
{/if}
</select>

{if $params->getParam('canView')}
{assign var="viewFields" value=$params->getTypeSubset('viewFields')}
<input onclick="{foreach from=$viewFields->getParams() key=field item=value}this.form.{$field}.value='{$value}'; {/foreach}this.form.submit()" type="button" value="{$resources->getString($params->getParam('canView'))}" />
{/if}

{if $params->getParam('canDelete')}
{assign var="deleteFields" value=$params->getTypeSubset('deleteFields')}
{foreach from=$options key=attr item=label}
<input name="{$attributeName}_{$attr}_remove" type="hidden" value="0" />
{/foreach}
<input onclick="{if $params->getParam('deleteConfirm')}if (confirm('{$resources->getString($params->getParam('deleteConfirm'))}')) {ldelim} {/if}this.form['{$attributeName}_' + this.form.{$fieldName}.options[this.form.{$fieldName}.selectedIndex].value + '_remove'].value='1'; {foreach from=$deleteFields->getParams() key=field item=value}this.form.{$field}.value='{$value}'; {/foreach}this.form.submit();{if $params->getParam('deleteConfirm')} {rdelim}{/if}" type="button" value="{$resources->getString($params->getParam('canDelete'))}" />
{/if}

{if $params->getParam('canCreate')}
{assign var="createFields" value=$params->getTypeSubset('createFields')}
<input onclick="this.form.{$fieldName}.options[this.form.{$fieldName}.selectedIndex].value='NEW'; {foreach from=$createFields->getParams() key=field item=value}this.form.{$field}.value='{$value}'; {/foreach}this.form.submit();" type="button" value="{$resources->getString($params->getParam('canCreate'))}" />
{/if}

