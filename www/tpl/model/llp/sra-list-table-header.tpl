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
Renders a list header based on results provided by the SRA_ListLookupProcessor. 
it is assumed that sra-list-footer.tpl will be rendered. "lp-encl-elem" for the 
enclosing view should be "tr". the header may include attribute labels as well 
as a commands row (page navigation, actions, and limit adjustment commands may 
be included in the commands row which is divided into 3 columns - left, center, 
right). see documentation below for more details.

Template variables

Name                  Type                  Optional  Default    Desc
LLP_DATA              SRA_LookupProcessorData   N                    Data associated with the lookup


PARAMS (type="sra-list-header"):
Key                   Default     Value     Desc

preTpl                                      template to display prior to the header

openForm              1           (1|0)     whether or not this header should open
                                            the form element

displayAttrLabels     1           (1|0)     whether or not to display the attribute 
                                            labels (will be rendered after to the 
																						commands row where applicable)

displayCmdsRow        0           (1|0)     whether or not to display the commands row 
                                            (page navigation, global actions, etc). this row is 
																						displayed below the attribute labels row
																						
displayResultsStr     0           (1|0)     whether or not to display the results string 
                                            if available. for more information, see the 
																						SRA_ListLookupProcessor "resultsResource" parameter
																						documentation. the results string will be 
																						displayed directly above the table
																						
resultsStrClass                             class for the results string enclosing font tag (see attrLabelCellClass)

resultsStrClassTag                          class tag for the results string enclosing font tag (see attrLabelCellClassTag)

resultsStrEncl                              an optional html element to enclose the results string in

resultsStrEnclClass                         a class to apply to "resultsStrEncl" (see attrLabelCellClass)

resultsStrEnclClassTag                      class tag for "resultsStrEnclClass" (see attrLabelCellClassTag)
																						
tableClass                                  for table (see attrLabelCellClass)

tableCycle                                  for table (see attrLabelCellCycle)
																						
tableClassTag                               for table (see attrLabelCellClassTag)

tableBorder           0                     the border size for the table. if -1, 
                                            attribute will not be rendered
																						(if not specified through css formatting)

tableCellPadding      0                     the cell padding for the table. if -1, 
                                            attribute will not be rendered
																						(if not specified through css formatting)

tableCellSpacing      0                     the cellspacing for the table. if -1, 
                                            attribute will not be rendered
																						(if not specified through css formatting)

*}

{assign var="headerParams" value=$LLP_DATA->getParams('sra-list-header')}
{if $headerParams->getParam('preTpl')}{include file=$headerParams->getParam('preTpl')}{/if}
{if $LLP_DATA}
{assign var="bakNumCols" value=0}
{if $headerParams->getParam('numColsTplVar')}
{assign var="bakNumCols" value=$Template->getAppTemplateVar($headerParams->getParam('numColsTplVar'))}
{/if}
{assign var="numCols" value=$headerParams->getParam('numCols', $bakNumCols)}
{assign var="tmp" value=$LLP_DATA->formName}
{assign var="formName" value=$headerParams->getParam('formName', $tmp)}

{if $headerParams->getParam('openForm', '1') eq '1'}<form name="{$formName}" id="{$formName}" action="" method="{$LLP_DATA->formType}">{/if}

<script type="text/javascript">
<!--
{if $LLP_DATA->globalActions}
	var entityActionFields = new Array();
	function globalSelect() {ldelim}
		for(i=0; i<entityActionFields.length; i++) {ldelim}
			document.forms.{$LLP_DATA->formName}[entityActionFields[i]].checked = document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_GLOBAL_ACTION_SELECT_ALL_FIELD_NAME}.checked;
		{rdelim}
	{rdelim}
{/if}
	function clearLLPFields() {ldelim}
		document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME}.value="";
		document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME}.value="";
		document.forms.{$LLP_DATA->formName}.{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME}.value="";
	{rdelim}
//-->
</script>

<input type="hidden" name="{$LLP_DATA->pageField}" value="{$LLP_DATA->getCurrentPage()}" />
<input type="hidden" name="{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_FIELD_NAME}" value="" />
<input type="hidden" name="{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_FIELD_NAME}" value="" />
<input type="hidden" name="{$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_SELECT_ACTION_GLOBAL_FIELD_NAME}" value="0" />
{if $LLP_DATA->limitField}<input type="hidden" name="{$LLP_DATA->limitField}" value="{$LLP_DATA->getLimit()}" />{/if}

{if $LLP_DATA->resultsString && $headerParams->getParam('displayResultsStr') eq '1'}
{if $headerParams->getParam('resultsStrEncl')}<{$headerParams->getParam('resultsStrEncl')}{if $headerParams->getParam('resultsStrEnclClass')}{if $headerParams->getParam('resultsStrEnclClassTag')} {$headerParams->getParam('resultsStrEnclClassTag')}{else} class{/if}="{$headerParams->getParam('resultsStrEnclClass')}"{/if}>{/if}
{if $headerParams->getParam('resultsStrClass')}<font {if $headerParams->getParam('resultsStrClassTag')}{$headerParams->getParam('resultsStrClassTag')}{else}class{/if}="{$headerParams->getParam('resultsStrClass')}">{/if}
{$LLP_DATA->resultsString}
{if $headerParams->getParam('resultsStrClass')}</font>{/if}
{if $headerParams->getParam('resultsStrEncl')}</{$headerParams->getParam('resultsStrEncl')}>{/if}
{/if}

{assign var="cycleName" value=$headerParams->getParam('tableCycle', $headerParams->getParam('tableClass'))}
<table{if $headerParams->getParam('tableClass')}{if $headerParams->getParam('tableClassTag')} {$headerParams->getParam('tableClassTag')}{else} class{/if}="{cycle name=$cycleName values=$headerParams->getParam('tableClass')}"{/if}{if $headerParams->getParam('tableBorder', '0') neq '-1'} border="{$headerParams->getParam('tableBorder', '0')}"{/if}{if $headerParams->getParam('tableCellPadding', '0') neq '-1'} cellpadding="{$headerParams->getParam('tableCellPadding', '0')}"{/if}{if $headerParams->getParam('tableCellSpacing', '0') neq '-1'} cellspacing="{$headerParams->getParam('tableCellSpacing', '0')}"{/if}>

{if $headerParams->getParam('displayAttrLabels', '1') eq '1'}
{include file='sra-list-table-attrs.tpl'}
{assign var="headerParams" value=$params->getTypeSubset('sra-list-header')}
{/if}

{if $headerParams->getParam('displayCmdsRow') eq '1'}
{include file='sra-list-table-cmds.tpl'}
{assign var="headerParams" value=$params->getTypeSubset('sra-list-header')}
{/if}

{/if}
