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
Renders a list action (SRA_LookupProcessorAction) section based on the parameters 
specified. An instance of SRA_LookupProcessorAction must be accessible within the 
template variable $action

Template variables

Name                  Type                  Optional  Default    Desc
LLP_DATA              SRA_LookupProcessorData   N                    Data associated with the lookup

action                SRA_LookupProcessorAction N                    The instance of SRA_LookupProcessorAction to render

entity                [Entity]              N                    the current entity being rendered. necessary in order to access its pk

isGlobal              boolean               Y         FALSE      Whether or not the action is global (meaning it should be processed for all selected items)


PARAMS (type="sra-list-action"):
Key                   Default     Value     Desc
NONE



*}
{if $LLP_DATA && $action && ($entity || $isGlobal)}
{* global selector (checkbox) *}
{if $action eq '1'}
{if $isGlobal}
	{assign var="globalFieldName" value=$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_ALL_FIELD_NAME}
{else}
	{assign var="globalFieldNameSeq" value=$LLP_DATA->getNextGlobalFieldSeq()}
	{assign var="globalFieldName" value=$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_FIELD_NAME_PREFIX|cat:$globalFieldNameSeq}
<script type="text/javascript">
<!--
entityActionFields[{$globalFieldNameSeq}] = '{$globalFieldName}';
// -->
</script>
{/if}
<input type="checkbox" name="{$globalFieldName}" value="{if !$isGlobal}{$entity->getPrimaryKey()}{/if}"{if !$isGlobal && $LLP_DATA->isSelected($entity->getPrimaryKey())} checked="checked"{/if}{if $isGlobal} onclick="globalSelect()"{/if} />

{* action *}
{else}
{if $action->aPre}{$action->aPre}{/if}
{if $action->btn && $action->link}
<input{if $action->cssClass} class="{$action->cssClass}"{/if} onclick="{if $action->getMsgConfirm($isGlobal)}if (confirm('{$resources->getString($action->getMsgConfirm($isGlobal))}')) {ldelim}{/if}clearLLPFields();document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME}.value='{$action->id}';{if $isGlobal}document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME}.value='1';{else}document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME}.value='{$entity->getPrimaryKey()}';{/if}document.forms.{$LLP_DATA->formName}.submit();{if $action->getMsgConfirm($isGlobal)}{rdelim}{/if}" type="button" value="{$resources->getString($action->link)}" />
{else}
<a{if $action->cssClass} class="{$action->cssClass}"{/if} href="javascript:{ldelim}{rdelim}" onclick="{if $action->getMsgConfirm($isGlobal)}if (confirm('{$resources->getString($action->getMsgConfirm($isGlobal))}')) {ldelim}{/if}clearLLPFields();document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME}.value='{$action->id}';{if $isGlobal}document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME}.value='1';{else}document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME}.value='{$entity->getPrimaryKey()}';{/if}document.forms.{$LLP_DATA->formName}.submit();{if $action->getMsgConfirm($isGlobal)}{rdelim}{/if}">
{if $action->pre}{$action->pre}{/if}
{if $action->img}<img border="0"{if $action->imgClass} class="{$action->imgClass}"{/if} src="{$action->img}"{if $action->getAlt($isGlobal)} alt="{$resources->getString($action->getAlt($isGlobal))}" title="{$resources->getString($action->getAlt($isGlobal))}"{else} alt=""{/if} />{else}{$resources->getString($action->link)}{/if}
{if $action->post}{$action->post}{/if}
</a>
{/if}
{if $action->aPost}{$action->aPost}{/if}

{/if}

{/if}
