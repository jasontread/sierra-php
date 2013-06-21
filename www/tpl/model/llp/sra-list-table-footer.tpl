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
Renders a list footer based on results provided by the SRA_ListLookupProcessor. 
it is assumed that sra-list-header.tpl as well as the corresponding entities 
have already been rendered. "lp-encl-elem" for the enclosing view should be 
"tr". the footer may include attribute labels as well as a commands row (page 
navigation, actions, and limit adjustment commands may be included in the 
commands row which is divided into 3 columns - left, center, right). see 
documentation below for more details.

Template variables

Name                  Type                  Optional  Default    Desc
LLP_DATA              SRA_LookupProcessorData   N                    Data associated with the lookup


PARAMS (type="sra-list-footer"):
Key                   Default     Value     Desc

postTpl                                     template to display after to the footer

closeForm             1           (1|0)     whether or not this footer should close
                                            the form element

displayAttrLabels     0           (1|0)     whether or not to display the attribute 
                                            labels (will be rendered prior to the 
																						commands row where applicable)
																						
displayCmdsRow        1           (1|0)     whether or not to display the commands row 
                                            (page navigation, global actions, etc). this row is 
																						displayed below the attribute labels row
																						
displayResultsStr     0           (1|0)     whether or not to display the results string 
                                            if available. for more information, see the 
																						SRA_ListLookupProcessor "resultsResource" parameter
																						documentation. the results string will be 
																						displayed directly below the table
																						
resultsStrClass                             class for the results string enclosing font tag (see attrLabelCellClass)

resultsStrClassTag                          class tag for the results string enclosing font tag (see attrLabelCellClassTag)

resultsStrEncl                              an optional html element to enclose the results string in

resultsStrEnclClass                         a class to apply to "resultsStrEncl" (see attrLabelCellClass)

resultsStrEnclClassTag                      class tag for "resultsStrEnclClass" (see attrLabelCellClassTag)
																						
*}

{assign var="footerParams" value=$LLP_DATA->getParams('sra-list-footer')}

{if $LLP_DATA}
{assign var="bakNumCols" value=0}
{if $footerParams->getParam('numColsTplVar')}
{assign var="bakNumCols" value=$Template->getAppTemplateVar($footerParams->getParam('numColsTplVar'))}
{/if}
{assign var="numCols" value=$footerParams->getParam('numCols', $bakNumCols)}
{assign var="formName" value=$footerParams->getParam('formName', 0)}

{if $formName}{assign var="formName" value="'"|cat:$formName|cat:"'"}{/if}

{if $footerParams->getParam('displayAttrLabels') eq '1'}
{include file='sra-list-table-attrs.tpl'}
{assign var="footerParams" value=$params->getTypeSubset('sra-list-footer')}
{/if}

{if $footerParams->getParam('displayCmdsRow', '1') eq '1'}
{include file='sra-list-table-cmds.tpl'}
{assign var="footerParams" value=$params->getTypeSubset('sra-list-footer')}
{/if}

</table>

{if $LLP_DATA->resultsString && $footerParams->getParam('displayResultsStr') eq '1'}
{if $footerParams->getParam('resultsStrEncl')}<{$footerParams->getParam('resultsStrEncl')}{if $footerParams->getParam('resultsStrEnclClass')}{if $footerParams->getParam('resultsStrEnclClassTag')} {$footerParams->getParam('resultsStrEnclClassTag')}{else} class{/if}="{$footerParams->getParam('resultsStrEnclClass')}"{/if}>{/if}
{if $footerParams->getParam('resultsStrClass')}<font {if $footerParams->getParam('resultsStrClassTag')}{$footerParams->getParam('resultsStrClassTag')}{else}class{/if}="{$footerParams->getParam('resultsStrClass')}">{/if}
{$LLP_DATA->resultsString}
{if $footerParams->getParam('resultsStrClass')}</font>{/if}
{if $footerParams->getParam('resultsStrEncl')}</{$footerParams->getParam('resultsStrEncl')}>{/if}
{/if}

{if $footerParams->getParam('closeForm', '1') eq '1'}</form>{/if}
{/if}

{if $footerParams->getParam('postTpl')}{include file=$footerParams->getParam('postTpl')}{/if}
