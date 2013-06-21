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
Used by sra-list-table-header.tpl and sra-list-table-footer.tpl to render a command row 


PARAMS (type="sra-list-cmds"):
Key                   Default     Value     Desc
																						
cellType              td          (td|th)   the cell type the command columns

rowClass                                    for commands row (see attrLabelCellClass)

rowCycle                                    for commands row (see attrLabelCellCycle)
																						
rowClassTag                                 for commands row (see attrLabelCellClassTag)

leftColClass                                for commands row left column (see attrLabelCellClass)
																						
leftColCycle                                for commands row left column (see attrLabelCellCycle)
												 									
leftColClassTag                             for commands row left column (see attrLabelCellClassTag)

leftColAlign        left                    alignment for commands row left column

leftColSpan         1                       the colspan for the left row

centerColClass                              for commands row center column (see attrLabelCellClass)
																						
centerColCycle                              for commands row center column (see attrLabelCellCycle)
												 									
centerColClassTag                           for commands row center column (see attrLabelCellClassTag)

centerColAlign      center                  alignment for commands row center column

centerColSpan       numCols-leftColSpan-rightColSpan the colspan for the center row

rightColClass                               for commands row right column (see attrLabelCellClass)
																						
rightColCycle                               for commands row right column (see attrLabelCellCycle)
												 									
rightColClassTag                            for commands row right column (see attrLabelCellClassTag)

rightColAlign       left                    alignment for commands row right column

rightColSpan        1                       the colspan for the right row

numCols                                     the total # of columns in the table. 
                                            not necessary if "attrsTplVar" is defined

numColsTplVar                               optional template variable that contains 
                                            the value defining the # of columns in the 
																						table. not necessary if "attrsTplVar" is defined
																						
firstLink                                   resource key for the "first" link. if 
                                            not specified, the numeric value 1 
																						be used. 
																						
firstLinkImg                                optional image to be used for the first link. 
                                            "firstLink" will be used as the alt text 
																						for this image if specified
																						
firstLinkImgDis                             optional image to be used for the first link 
                                            when it is not enabled
																						
firstLinkPos         8            (0-20)    position for the "first" link. the following is
                                            a description of the different available positions:
																						 0:     do not show
																						 1-5:   reservered for left column
																						 6-15:  reserved for center column
																						 15-20: reserved for right column
																						Thus each column, left, center, right, 
																						can be used to show any of the 7 command 
																						row elements: first link, previous link, 
																						pages links, next link, last link, 
																						actions links, and limit selector and 
																						in any relative position in those columns
																						(i.e. 1 is the first element in the  
																						column, 2 is the second, etc)

firstLinkPre                                html to place before the "first" link

firstLinkPost        &nbsp;                 html to place after the "first" link

firstLinkHide        0            (1|0)     whether or not to hide the "first" link 
                                            when the user is on the first page. if 
																						displayed, it will not be a link (just text)

previousLink                                resource key for the "previous" link. if 
                                            not specified, it will not be displayed
																						
previousLinkImg                             optional image to be used for the previous link. 
                                            "previousLink" will be used as the alt text 
																						for this image if specified
																						
previousLinkImgDis                          optional image to be used for the previous link
                                            when it is not enabled

previousLinkPos      9            (0-20)    position for the "previous" link. see 
                                            "firstLinkPos" comments for more details 
																						on its use

previousLinkPre                             html to place before the "previous" link

previousLinkPost     &nbsp;                 html to place after the "previous" link

previouLinkHide      0            (1|0)     whether or not to hide the "previous" link 
                                            when the user is on the first page. if 
																						displayed, it will not be a link (just text)

nextLink                                    resource key for the "next" link. if 
                                            not specified, it will not be displayed
																						
nextLinkImg                                 optional image to be used for the next link. 
                                            "nextLink" will be used as the alt text 
																						for this image if specified
																						
nextLinkImgDis                              optional image to be used for the next link
                                            when it is not enabled

nextLinkPos          11           (0-20)    position for the "next" link. see 
                                            "firstLinkPos" comments for more details 
																						on its use

nextLinkPre    			                        html to place before the "next" link

nextLinkPost    		 &nbsp;                 html to place after the "next" link

nextLinkHide         0            (1|0)     whether or not to hide the "next" link 
                                            when the user is on the last page. if 
																						displayed, it will not be a link (just text)
																						
lastLink                                    resource key for the "last" link. if 
                                            not specified, the numeric value [# pages] 
																						will be used
																						
lastLinkImg                                 optional image to be used for the last link. 
                                            "lastLink" will be used as the alt text 
																						for this image if specified
																						
lastLinkImgDis                              optional image to be used for the last link
                                            when it is not enabled
																						
lastLinkPos          12           (0-20)    position for the "last" link. see 
                                            "firstLinkPos" comments for more details 
																						on its use

lastLinkPre    			                        html to place before the "last" link

lastLinkPost    		 &nbsp;                 html to place after the "last" link

lastLinkHide         0            (1|0)     whether or not to hide the "last" link 
                                            when the user is on the last page. if 
																						displayed, it will not be displayed 
																						as a link (just text)

pagesLinksPos        10           (0-20)    position for the pages links. pages links 
                                            are the links to other pages in the 
																						result (i.e. 1 2 3 4) where each number 
																						links directly to that corresponding 
																						page of result. see "firstLinkPos" 
																						comments for more details on its use

maxPages                          10        the max # of page numbers to display
                                            in the pages links section. for example, 
																						if a result set has 100 pages, but 
																						"maxPages" is only 10, and the user is 
																						on the first page, then only page numbers 
																						1-10 will be displayed in the pages links 
																						section
																						
pagesPre      			                        html to place before the "pages" links

pagePost        		 &nbsp;                 html to place after the "pages" links

pagesIterPre   			                        html to place before each page # in the pages links

pageIterPost    		                        html to place after each page # in the pages links
																						
curPageEnclElem      b            [html el] the html element to enclose the current 
                                            page selector in. To use a special font 
																						to highlight the current page, this parameter 
																						could be defined as "font" with the associated 
																						font class defined in curPageEnclElemClass
																						
curPageEnclElemClass                        anchor (A) class for "curPageEnclElem" (see attrLabelCellClass)

curPageEnclElemClassTag                     anchor (A) attribute tag for "curPageEnclElem" (see attrLabelCellClassTag)

pagesSelPos          0            (0-20)    defines if/where to display a pages selector
                                            for the table (allows the user to change 
																						the page number using a drop down selector)

maxSelPages          250          (1-N)     the number of pages to display in the pages selector

pagesSelClass                               the css class to apply to the pages selector. 
                                            the pages selector is rendered as a "select" 
																						field with a size of 1 and an onchange action 
																						event that submits the enclosing form
																						
pagesSelResource                            resource text to display prior to the 
                                            pages selector
																						
pagesSelLabelPre                            html to place before the "pagesSelResource" label

pagesSelLabelPost                           html to place after the "pagesSelResource" label

pagesSelIterPre   			                    resource to place before each page # in the pages selector

pagesSelIterPost    		                    resource to place after each page # in the pages selector

actionsPos           0            (0-20)    if global (table wide) actions should be displayed, 
                                            this defines where those should be displayed. 
																						see sra-list-action.tpl for more information
																						
limitSelPos          0            (0-20)    defines if/where to display a limit selector
                                            for the table (allows the user to change 
																						the number of results displayed per page)

limitMin             10           (1-N)     the minimum limit selector value. < limitMax

limitMax             50           (1-N)     the maximum limit selector value. > limitMin

limitIncrement       10           (1-N)     the limit selector increment value 
                                            (i.e. for($i=[limitMax]; $i<=[limitMax]; $i+=[limitIncrement]) 
																						is the loop that will be used to determine 
																						the limit values available to the user in the
																						limit selector)

limitSelClass                               the css class to apply to the limit selector. 
                                            the limit selector is rendered as a "select" 
																						field with a size of 1 and an onchange action 
																						event that submits the enclosing form
																						
limitSelResource                            resource text to display prior to the 
                                            limit selector
																						
limitSelLabelPre                            html to place before the "limitSelResource" label

limitSelLabelPost                           html to place after the "limitSelResource" label

limitSelIterPre   			                    resource to place before each value in the limit selector

limitSelIterPost    		                    resource to place after each value in the limit selector

resultsStrPos         0           (0-20)    defines if/where to display the results string in 
                                            the commands row. for more information, see the 
																						SRA_ListLookupProcessor "resultsResource" parameter
																						documentation
																						
resultsStrClass                             class for the results string enclosing font tag (see attrLabelCellClass)

resultsStrClassTag                          class tag for the results string enclosing font tag (see attrLabelCellClassTag)

resultsStrEncl                              an optional html element to enclose the results string in

resultsStrEnclClass                         a class to apply to "resultsStrEncl" (see attrLabelCellClass)

resultsStrEnclClassTag                      class tag for "resultsStrEnclClass" (see attrLabelCellClassTag)

resultsStrPre   			                      html to render before the results string

resultsStrPost    		                      html to render after the results string

cmdTpl1                                     an optional template to display within the 
                                            command row in the position specified
																						
cmdTpl1Pos                        (1-20)    where cmdTpl1 should be displayed
																						
cmdTpl2                                     an optional template to display within the 
                                            command row in the position specified
																						
cmdTpl2Pos                        (1-20)    where cmdTpl2 should be displayed
																						
cmdTpl3                                     an optional template to display within the 
                                            command row in the position specified
																						
cmdTpl3Pos                        (1-20)    where cmdTpl3 should be displayed

*}

{assign var="cmdsParams" value=$LLP_DATA->getParams('sra-list-cmds')}
{assign var="leftColspan" value=$cmdsParams->getParam('leftColSpan',1)}
{assign var="rightColspan" value=$cmdsParams->getParam('rightColSpan',1)}
{assign var="centerColspan" value=$LLP_DATA->numCols-$leftColspan-$rightColspan}
{assign var="cycleName" value=$cmdsParams->getParam('rowCycle', $cmdsParams->getParam('rowClass'))}
{assign var="cellTag" value=$cmdsParams->getParam('cellType', 'td')}
{assign var="currentPage" value=$LLP_DATA->getCurrentPage()}
{assign var="lastPage" value=$LLP_DATA->getLastPage()}
{assign var="nextPage" value=$LLP_DATA->getNextPage()}
{assign var="firstLinkHide" value=$cmdsParams->getParam('firstLinkHide')}
{assign var="lastLinkHide" value=$cmdsParams->getParam('lastLinkHide')}
{assign var="previousLinkHide" value=$cmdsParams->getParam('previousLinkHide')}
{assign var="nextLinkHide" value=$cmdsParams->getParam('nextLinkHide')}
{$LLP_DATA->incSelectorSuffix()}
<tr{if $cmdsParams->getParam('rowClass')}{if $cmdsParams->getParam('rowClassTag')} {$cmdsParams->getParam('rowClassTag')}{else} class{/if}="{cycle name=$cycleName values=$cmdsParams->getParam('rowClass')}"{/if}>
{foreach from=$Util->getArray(20) item=idx}
{* left column *}
{if $idx==1}{assign var="cycleName" value=$cmdsParams->getParam('leftColCycle', $cmdsParams->getParam('leftColClass'))}<{$cellTag} align="{$cmdsParams->getParam('leftColAlign', 'left')}"{if $cmdsParams->getParam('leftColClass')}{if $cmdsParams->getParam('leftColClassTag')} {$cmdsParams->getParam('leftColClassTag')}{else} class{/if}="{cycle name=$cycleName values=$cmdsParams->getParam('leftColClass')}"{/if} colspan="{$leftColspan}">{/if}
{* center column *}
{if $idx==6}{assign var="cycleName" value=$cmdsParams->getParam('centerColCycle', $cmdsParams->getParam('centerColClass'))}<{$cellTag} align="{$cmdsParams->getParam('centerColAlign', 'center')}"{if $cmdsParams->getParam('centerColClass')}{if $cmdsParams->getParam('centerColClassTag')} {$cmdsParams->getParam('centerColClassTag')}{else} class{/if}="{cycle name=$cycleName values=$cmdsParams->getParam('centerColClass')}"{/if} colspan="{$centerColspan}">{/if}
{* right column *}
{if $idx==16}{assign var="cycleName" value=$cmdsParams->getParam('rightColCycle', $cmdsParams->getParam('rightColClass'))}<{$cellTag} align="{$cmdsParams->getParam('rightColAlign', 'left')}"{if $cmdsParams->getParam('rightColClass')}{if $cmdsParams->getParam('rightColClassTag')} {$cmdsParams->getParam('rightColClassTag')}{else} class{/if}="{cycle name=$cycleName values=$cmdsParams->getParam('rightColClass')}"{/if} colspan="{$rightColspan}">{/if}

{* first link *}
{if $idx==$cmdsParams->getParam('firstLinkPos', 8)}
{$cmdsParams->getParam('firstLinkPre', '')}
{if !$firstLinkHide || ($firstLinkHide eq '1' && $currentPage gt 1)}
{if $cmdsParams->getParam('firstLink')}{assign var="linkText" value=$resources->getString($cmdsParams->getParam('firstLink'))}{else}{assign var="linkText" value='1'}{/if}
{if $currentPage gt 1}<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='1';document.forms.{$LLP_DATA->formName}.submit();" />{/if}{if $cmdsParams->getParam('firstLinkImg')}<img src="{if !($currentPage gt 1) && $cmdsParams->getParam('firstLinkImgDis')}{$cmdsParams->getParam('firstLinkImgDis')}{else}{$cmdsParams->getParam('firstLinkImg')}{/if}" border="0" alt="{$linkText}" title="{$linkText}" />{else}{$linkText}{/if}{if $currentPage gt 1}</a>{/if}
{/if}
{$cmdsParams->getParam('firstLinkPost', '&nbsp;')}
{/if}

{* previous link *}
{if $idx==$cmdsParams->getParam('previousLinkPos', 9) && $cmdsParams->getParam('previousLink')}
{$cmdsParams->getParam('previousLinkPre', '')}
{if !$previousLinkHide || ($previousLinkHide eq '1' && $currentPage gt 1)}
{if $cmdsParams->getParam('previousLink')}{assign var="linkText" value=$resources->getString($cmdsParams->getParam('previousLink'))}{else}{assign var="linkText" value=$LLP_DATA->getPreviousPage()}{/if}
{if $currentPage gt 1}<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='{$LLP_DATA->getPreviousPage()}';document.forms.{$LLP_DATA->formName}.submit();" />{/if}{if $cmdsParams->getParam('previousLinkImg')}<img src="{if !($currentPage gt 1) && $cmdsParams->getParam('previousLinkImgDis')}{$cmdsParams->getParam('previousLinkImgDis')}{else}{$cmdsParams->getParam('previousLinkImg')}{/if}" border="0" alt="{$linkText}" title="{$linkText}" />{else}{$linkText}{/if}{if $currentPage gt 1}</a>{/if}
{/if}
{$cmdsParams->getParam('previousLinkPost', '&nbsp;')}
{/if}

{* pages links *}
{if $idx==$cmdsParams->getParam('pagesLinksPos', 10)}
{$cmdsParams->getParam('pagesPre', '')}
{assign var="tmp" value=0}
{foreach from=$LLP_DATA->getPagesArray($cmdsParams->getParam('maxPages', '10'), $currentPage) item=pageNum}
{if $tmp}{$cmdsParams->getParam('pagesIterPost', '')}{/if}
{$cmdsParams->getParam('pagesIterPre', '')}
<a href="javascript:{ldelim}{rdelim}"{if $pageNum eq $currentPage && $cmdsParams->getParam('curPageEnclElemClass')}{if $cmdsParams->getParam('curPageEnclElemClassTag')}{$cmdsParams->getParam('curPageEnclElemClassTag')}{else}class{/if}="{$cmdsParams->getParam('curPageEnclElemClass')}" {/if} onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='{$pageNum}';document.forms.{$LLP_DATA->formName}.submit();" />{if $pageNum eq $currentPage}<{$cmdsParams->getParam('curPageEnclElem', 'b')}>{/if}{$pageNum}{if $pageNum eq $currentPage}</{$cmdsParams->getParam('curPageEnclElem', 'b')}>{/if}</a>
{assign var="tmp" value=1}
{/foreach}
{$cmdsParams->getParam('pagesPost', '&nbsp;')}
{/if}

{* page selector *}
{if $idx==$cmdsParams->getParam('pagesSelPos') && $LLP_DATA->pageField}
{if $cmdsParams->getParam('pagesSelResource')}
{$cmdsParams->getParam('pagesSelLabelPre', '')}
{$resources->getString($cmdsParams->getParam('pagesSelResource'))}
{$cmdsParams->getParam('pagesSelLabelPost', '')}
{/if}
<select{if $cmdsParams->getParam('pagesSelClass')} class="{$cmdsParams->getParam('pagesSelClass')}"{/if} name="{$LLP_DATA->pageField}Sel{$LLP_DATA->selectorSuffix}" size="1" onchange="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value=document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}Sel{$LLP_DATA->selectorSuffix}.options[document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}Sel{$LLP_DATA->selectorSuffix}.selectedIndex].value;document.forms.{$LLP_DATA->formName}.submit();">
{foreach from=$LLP_DATA->getPagesArray($cmdsParams->getParam('maxSelPages', '250'), $currentPage) item=page}
<option value="{$page}"{if $page eq $currentPage} selected="selected"{/if}>{if $cmdsParams->getParam('pagesSelIterPre')}{$resources->getString($cmdsParams->getParam('pagesSelIterPre'))}{/if}{$page}{if $cmdsParams->getParam('pagesSelIterPost')}{$resources->getString($cmdsParams->getParam('pagesSelIterPost'))}{/if}</option>
{/foreach}
</select>
{/if}

{* next link *}
{if $idx==$cmdsParams->getParam('nextLinkPos', 11) && $cmdsParams->getParam('nextLink')}
{$cmdsParams->getParam('nextLinkPre', '')}
{if !$nextLinkHide || ($nextLinkHide eq '1' && $currentPage neq $lastPage)}
{if $cmdsParams->getParam('nextLink')}{assign var="linkText" value=$resources->getString($cmdsParams->getParam('nextLink'))}{else}{assign var="linkText" value=$nextPage}{/if}
{if $currentPage neq $lastPage}<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='{$nextPage}';document.forms.{$LLP_DATA->formName}.submit();" >{/if}{if $cmdsParams->getParam('nextLinkImg')}<img src="{if $currentPage eq $lastPage && $cmdsParams->getParam('nextLinkImgDis')}{$cmdsParams->getParam('nextLinkImgDis')}{else}{$cmdsParams->getParam('nextLinkImg')}{/if}" border="0" alt="{$linkText}" title="{$linkText}" />{else}{$linkText}{/if}{if $currentPage neq $lastPage}</a>{/if}
{/if}
{$cmdsParams->getParam('nextLinkPost', '&nbsp;')}
{/if}

{* last link *}
{if $idx==$cmdsParams->getParam('lastLinkPos', 12)}
{$cmdsParams->getParam('lastLinkPre', '')}
{if !$lastLinkHide || ($lastLinkHide eq '1' && $currentPage neq $lastPage)}
{if $cmdsParams->getParam('lastLink')}{assign var="linkText" value=$resources->getString($cmdsParams->getParam('lastLink'))}{else}{assign var="linkText" value=$lastPage}{/if}
{if $currentPage neq $lastPage}<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='{$lastPage}';document.forms.{$LLP_DATA->formName}.submit();" >{/if}{if $cmdsParams->getParam('lastLinkImg')}<img src="{if $currentPage eq $lastPage && $cmdsParams->getParam('lastLinkImgDis')}{$cmdsParams->getParam('lastLinkImgDis')}{else}{$cmdsParams->getParam('lastLinkImg')}{/if}" border="0" alt="{$linkText}" title="{$linkText}" />{else}{$linkText}{/if}{if $currentPage neq $lastPage}</a>{/if}
{/if}
{$cmdsParams->getParam('lastLinkPost', '&nbsp;')}
{/if}

{* limit selector *}
{if $idx==$cmdsParams->getParam('limitSelPos') && $LLP_DATA->limitField}
{if $cmdsParams->getParam('limitSelResource')}
{$cmdsParams->getParam('limitSelLabelPre', '')}
{$resources->getString($cmdsParams->getParam('limitSelResource'))}
{$cmdsParams->getParam('limitSelLabelPost', '')}
{/if}
<select{if $cmdsParams->getParam('limitSelClass')} class="{$cmdsParams->getParam('limitSelClass')}"{/if} name="{$LLP_DATA->limitField}Sel{$LLP_DATA->selectorSuffix}" size="1" onchange="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->pageField}.value='1';document.forms.{$LLP_DATA->formName}.{$LLP_DATA->limitField}.value=document.forms.{$LLP_DATA->formName}.{$LLP_DATA->limitField}Sel{$LLP_DATA->selectorSuffix}.options[document.forms.{$LLP_DATA->formName}.{$LLP_DATA->limitField}Sel{$LLP_DATA->selectorSuffix}.selectedIndex].value;document.forms.{$LLP_DATA->formName}.submit();">
{assign var="tmp1" value=$cmdsParams->getParam('limitMax', 50)}
{assign var="tmp2" value=$cmdsParams->getParam('limitMin', 10)}
{assign var="tmp1" value=$tmp1-$tmp2}
{assign var="tmp3" value=$cmdsParams->getParam('limitIncrement', 10)}
{assign var="tmp" value=$tmp1/$tmp3}
{assign var="tmp" value=$tmp+1}
{foreach from=$Util->getArray($tmp, $tmp2, $tmp3) item=limit}
<option value="{$limit}"{if $limit eq $LLP_DATA->getLimit()} selected="selected"{/if}>{if $cmdsParams->getParam('limitSelIterPre')}{$resources->getString($cmdsParams->getParam('limitSelIterPre'))}{/if}{$limit}{if $cmdsParams->getParam('limitSelIterPost')}{$resources->getString($cmdsParams->getParam('limitSelIterPost'))}{/if}</option>
{/foreach}
</select>
{/if}

{* actions *}
{if $idx==$cmdsParams->getParam('actionsPos') && $LLP_DATA->getActions(1)}
{if $LLP_DATA->getParam('actionEncl')}<{$LLP_DATA->getParam('actionEncl')}>{/if}
{foreach from=$LLP_DATA->getActions(1) item=action}
{assign var="isGlobal" value=1}
{include file='sra-list-action.tpl'}
{assign var="isGlobal" value=0}
{assign var="action" value=0}
{/foreach}
{if $LLP_DATA->getParam('actionEncl')}</{$LLP_DATA->getParam('actionEncl')}>{/if}
{/if}

{* results string *}
{if $idx==$cmdsParams->getParam('resultsStrPos') && $LLP_DATA->resultsString}
{$cmdsParams->getParam('resultsStrPre', '')}
{if $cmdsParams->getParam('resultsStrEncl')}<{$cmdsParams->getParam('resultsStrEncl')}{if $cmdsParams->getParam('resultsStrEnclClass')}{if $cmdsParams->getParam('resultsStrEnclClassTag')} {$cmdsParams->getParam('resultsStrEnclClassTag')}{else} class{/if}="{$cmdsParams->getParam('resultsStrEnclClass')}"{/if}>{/if}
{if $cmdsParams->getParam('resultsStrClass')}<font {if $cmdsParams->getParam('resultsStrClassTag')}{$cmdsParams->getParam('resultsStrClassTag')}{else}class{/if}="{$cmdsParams->getParam('resultsStrClass')}">{/if}
{$LLP_DATA->resultsString}
{if $cmdsParams->getParam('resultsStrClass')}</font>{/if}
{if $cmdsParams->getParam('resultsStrEncl')}</{$cmdsParams->getParam('resultsStrEncl')}>{/if}
{$cmdsParams->getParam('resultsStrPost', '')}
{/if}

{* template 1 *}
{if $idx==$cmdsParams->getParam('cmdTpl1Pos')}
{include file=$cmdsParams->getParam('cmdTpl1')}
{/if}

{* template 2 *}
{if $idx==$cmdsParams->getParam('cmdTpl2Pos')}
{include file=$cmdsParams->getParam('cmdTpl2')}
{/if}

{* template 3 *}
{if $idx==$cmdsParams->getParam('cmdTpl3Pos')}
{include file=$cmdsParams->getParam('cmdTpl3')}
{/if}

{* close column *}
{if $idx==5 || $idx==15 || $idx==20}</{$cellTag}>{/if}
{/foreach}
</tr>
