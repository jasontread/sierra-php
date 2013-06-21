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
Used by sra-list-table-header.tpl and sra-list-table-footer.tpl to render a labels row. 
The $LLP_DATA->numCols attribute will be set as a result of using this template

PARAMS (type="sra-list-attrs"):
Key                   Default     Value     Desc

attrsTplVar                                 the template variable under which the 
                                            attribute NAMES can be retrieved
																						
sortableAll           0           (0|1)     whether or not all of the attributes 
                                            are sortable. alternatively, the 
																						sortable attributes may be specified 
																						using "sortableAttrsTplVar"
																						
sortableAttrsTplVar                         the template variable under which the 
                                            names of the attributes that are sortable 
																						can be referenced
																						
defaultSort                      (asc|desc) the default sort method for single sort 
                                            links. if neither descSortImg or ascSortImg 
																						are specified, and this parameter is 
																						specified, the attribute label itself 
																						will become the sort link, toggling 
																						between sort methods, starting with 
																						defaultSort
																						
sortOneAttrOnly       1           (1|0)     whether or not only 1 attribute should 
                                            be sortable at any given time
																						
sortAscResource                             the resource to use as the alt tag for 
                                            ascending sort image links
																						
sortDescResource                            the resource to use as the alt tag for 
                                            descending sort image links
																						
sortImg                                     if both ascending and descending links 
                                            should be contained within the same image, 
																						this image path can be specified here 
																						parameter. All of the X and Y coords 
																						below must be specified in order to 
																						use this sort image type. "toggleSortImgs" 
																						cannot be used in conjunction with 
																						"sortImg"
																						
sortImgAsc                                  optional image to display instead of "sortImg" 
                                            when the column is already sorted asc
																						
sortImgDesc                                 optional image to display instead of "sortImg" 
                                            when the column is already sorted desc
																						
sortImgAscShape       rect                  image map shape for the ascending link in the sortImg

sortImgAscCoords                            coords for the asceding link in the sortImg map. 
                                            if not specified, this link will not be generated
																						
sortImgDescShape      rect                  image map shape for the descending link in the sortImg

sortImgDescCoords                           coords for the desceding link in the sortImg map. 
                                            if not specified, this link will not be generated

sortImgResShape       rect                  image map shape for the reset link in the sortImg

sortImgResCoords                            coords for the reset link in the sortImg map. 
                                            if not specified, this link will not be generated
																						
sortImgAlign                                align attribute value for the sort image
																						
sortImgClass                                css class for the desc sort image

sortImgPos            0           (0-5)     the relative position of the  
                                            sort image link, where 3 is the position 
																						of the attribute label

sortImgPre                                  html to render prior to the sort image

sortImgPost                                 html to render after to the sort image
																						
descSortImg                                 the descending sort image path

descSortImgAlign                            align attribute value for the desc 
                                            sort image
																						
descSortImgClass                            css class for the desc sort image

descSortImgPos        0           (0-5)     the relative position of the descending 
                                            sort image link, where 3 is the position 
																						of the attribute label

descSortImgPre                              html to render prior to the desc sort image

descSortImgPost                             html to render after to the desc sort image

ascSortImg                                  the ascending sort image path

ascSortImgAlign                             align attribute value for the asc 
                                            sort image
																						
ascSortImgClass                             css class for the asc sort image

ascSortImgPos         0           (0-5)     the relative position of the ascending 
                                            sort image link, where 3 is the position 
																					  of the attribute label

ascSortImgPre                               html to render prior to the asc sort image

ascSortImgPost                              html to render after to the asc sort image

resSortImg                                  the reset sort image path. the reset 
                                            sort image will cause any sorting applied 
																						to an attribute to be reset
																						
resAscSortImg                               the reset sort image path. this image 
                                            will only be displayed when the current 
																						sort method is ascending

resDescSortImg                              the reset sort image path. this image 
                                            will only be displayed when the current 
																						sort method is descending
																						
resSortImgCheckbox    0           (1|0)     whether or not to use a simple checkbox 
                                            to represent the resSortImg ("resSortImg" 
																						is not required in this case)

resSortImgAlign                             align attribute value for the res 
                                            sort image
																						
resSortImgClass                             css class for the res sort image

resSortImgPos         0           (0-5)     the relative position of the reset 
                                            sort image link, where 3 is the position 
																					  of the attribute label

resSortImgPre                               html to render prior to the res sort image

resSortImgPost                              html to render after to the res sort image

resSortImgHide        1           (1|0)     whether or not to hide the res sort image 
                                            when no sorting has been applied to an 
																						attribute
																						
resSortResource                             the resource to use as the alt tag for 
                                            reset sort links
																						
toggleSortImgs        0           (1|0)     whether or not to toggle sort images 
                                            beginning with the image representing 
																						"defaultSort"
																						
cellType              th          (td|th)   the cell type for attribute labels
																						
cellClass                                   an optional css class that should be 
                                            specified for the attribute label cells. 
																						this may be a range of values that should be 
																						cycled through, for each instance of the 
																						attribute label cells. if this is 
																						the case, the cycled classes should 
																						be comma separated (i.e. "myclass1,myclass2")
																						if the value of this parameter is 
																						[LABEL] (brackets included), then the 
																						class name will be the label for the 
																						attribute itself
																						
cellCycle                                   an optional cycle name to apply (if 
                                            cellClass cycles are used). this 
																						will uniquely identify the cycle where 
																						other duplicate cycles exist that would 
																						otherwise interfere. otherwise, the 
																						name will be the value specified in 
																						'cellClass'
																						
cellClassTag                                if an cell attribute value other than 
                                            "class" should be used for the 
																						cellClass value, this parameter 
																						defines that attribute tag. For example, 
																						if instead of "class" you wished to bypass 
																						css and just specify a bgcolor, the 
																						value of this parameter would be "bgcolor" 
																						and the cell would be rendered as:
																						<th bgcolor="[cycle(cellClass)]">

attrLabelAlign                              the cell alignment for attribute labels

rowClass                                    for attribute labels row (see cellClass)
																						
rowCycle                                    for attribute labels row (see cellCycle)
																						
rowClassTag                                 for attribute labels row (see cellClassTag)

actionResource                              resource to use as the Action column heading. if not 
                                            specified, &nbsp; will be used
*}


{assign var="attrsParams" value=$LLP_DATA->getParams('sra-list-attrs')}
{assign var="cycleName" value=$attrsParams->getParam('rowCycle', $attrsParams->getParam('rowClass'))}
{assign var="cellTag" value=$attrsParams->getParam('cellType', 'th')}
{assign var="actionStr" value=$resources->getString($attrsParams->getParam('actionResource', '&nbsp;'))}
{assign var="sortOneAttrOnly" value=$attrsParams->getParam('sortOneAttrOnly', '1')}
{assign var="toggleSortImgs" value=$attrsParams->getParam('toggleSortImgs')}
{assign var="sortAscResource" value=$attrsParams->getParam('sortAscResource')}
{assign var="sortDescResource" value=$attrsParams->getParam('sortDescResource')}
{assign var="resSortResource" value=$attrsParams->getParam('resSortResource')}
{assign var="resSortImgHide" value=$attrsParams->getParam('resSortImgHide')}
{assign var="descSortPrefix" value=$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_DESC_SORT_CONSTRAINT_PREFIX}
{assign var="ascSortPrefix" value=$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_ASC_SORT_CONSTRAINT_PREFIX}
{assign var="sortOrderPrefix" value=$smarty.const.SRA_LIST_LOOKUP_PROCESSOR_DYNAMIC_SORT_ORDER_FIELD_PREFIX}
{assign var="sortAscStr" value=$smarty.const.SRA_QUERY_BUILDER_SORT_ASC}
{assign var="sortDescStr" value=$smarty.const.SRA_QUERY_BUILDER_SORT_DESC}
{assign var="addedHiddenFields" value="0"}
<tr{if $attrsParams->getParam('rowClass')}{if $attrsParams->getParam('rowClassTag')} {$attrsParams->getParam('rowClassTag')}{else} class{/if}="{cycle name=$cycleName values=$attrsParams->getParam('rowClass')}"{/if}>
{foreach from=$Template->getAppTemplateVar($attrsParams->getParam('attrsTplVar')) item=attr}
{assign var="sortMethod" value=$LLP_DATA->getSortMethod($attr)}
{$LLP_DATA->incNumCols()}
{if $LLP_DATA->actionsPos === $colIndex}{$LLP_DATA->incNumCols()}<{$cellTag}{if $attrsParams->getParam('attrLabelAlign')} align="{$attrsParams->getParam('attrLabelAlign')}"{/if}{if $attrsParams->getParam('cellClass')}{if $attrsParams->getParam('cellClassTag')} {$attrsParams->getParam('cellClassTag')}{else} class{/if}="{cycle name=$cycleName values=$attrsParams->getParam('cellClass')}"{/if}>{$actionStr}</{$cellTag}>{/if}
{assign var="isSortable" value=0}
{if $attrsParams->getParam('sortableAll') eq '1' || $LLP_DATA->isSortable($attr, $attrsParams->getParam('sortableAttrsTplVar'))}{assign var="isSortable" value=1}{/if}
{assign var="cycleName" value=$attrsParams->getParam('cellCycle', $attrsParams->getParam('cellClass'))}
<{$cellTag}{if $attrsParams->getParam('attrLabelAlign')} align="{$attrsParams->getParam('attrLabelAlign')}"{/if}{if $attrsParams->getParam('cellClass')}{if $attrsParams->getParam('cellClassTag')} {$attrsParams->getParam('cellClassTag')}{else} class{/if}="{cycle name=$cycleName values=$attrsParams->getParam('cellClass')}"{/if}>
{if $isSortable && !$sortOneAttrOnly}
<input name="{$descSortPrefix}{$attr}" type="hidden" value="{if $sortMethod eq $sortDescStr}{$attr}{/if}" />
<input name="{$ascSortPrefix}{$attr}" type="hidden" value="{if $sortMethod eq $sortAscStr}{$attr}{/if}" />
{/if}
{foreach from=$Util->getArray(5) item=idx}
{if $isSortable && $idx == $attrsParams->getParam('sortImgPos') && $attrsParams->getParam('sortImg')}
{$attrsParams->getParam('sortImgPre', '')}
<img{if $attrsParams->getParam('sortImgAlign')} align="{$attrsParams->getParam('sortImgAlign')}"{/if}{if $attrsParams->getParam('sortImgClass')} border="0" class="{$attrsParams->getParam('sortImgClass')}"{/if} src="{if $sortMethod eq $sortAscStr && $attrsParams->getParam('sortImgAsc')}{$attrsParams->getParam('sortImgAsc')}{elseif $sortMethod eq $sortDescStr && $attrsParams->getParam('sortImgDesc')}{$attrsParams->getParam('sortImgDesc')}{else}{$attrsParams->getParam('sortImg')}{/if}" usemap="#{$attr}_sortMap" ismap="ismap" alt="" />
{$attrsParams->getParam('sortImgPost', '')}
{/if}
{if $isSortable && $idx == $attrsParams->getParam('descSortImgPos') && $attrsParams->getParam('descSortImg') && (!$toggleSortImgs || ($toggleSortImgs && $sortMethod neq $sortDescStr))}
{$attrsParams->getParam('descSortImgPre', '')}
<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='{$attr}';{if !$sortOneAttrOnly}document.forms.{$LLP_DATA->formName}.{$sortOrderPrefix}0.value='{$attr}';{/if}document.forms.{$LLP_DATA->formName}.submit();"><img{if $attrsParams->getParam('descSortImgAlign')} align="{$attrsParams->getParam('descSortImgAlign')}"{/if} border="0"{if $attrsParams->getParam('descSortImgClass')} class="{$attrsParams->getParam('descSortImgClass')}"{/if} src="{$attrsParams->getParam('descSortImg')}"{if $sortDescResource} alt="{$resources->getString($sortDescResource)}" title="{$resources->getString($sortDescResource)}"{/if} /></a>
{$attrsParams->getParam('descSortImgPost', '')}
{/if}
{if $isSortable && $idx == $attrsParams->getParam('ascSortImgPos') && $attrsParams->getParam('ascSortImg') && (!$toggleSortImgs || ($toggleSortImgs && $sortMethod neq $sortAscStr))}
{$attrsParams->getParam('ascSortImgPre', '')}
<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='{$attr}';{if !$sortOneAttrOnly}document.forms.{$LLP_DATA->formName}.{$sortOrderPrefix}0.value='{$attr}';{/if}document.forms.{$LLP_DATA->formName}.submit();"><img{if $attrsParams->getParam('ascSortImgAlign')} align="{$attrsParams->getParam('ascSortImgAlign')}"{/if} border="0"{if $attrsParams->getParam('ascSortImgClass')} class="{$attrsParams->getParam('ascSortImgClass')}"{/if} src="{$attrsParams->getParam('ascSortImg')}"{if $sortAscResource} alt="{$resources->getString($sortAscResource)}" title="{$resources->getString($sortAscResource)}"{/if} /></a>
{$attrsParams->getParam('ascSortImgPost', '')}
{/if}
{if $isSortable && $idx == $attrsParams->getParam('resSortImgPos') && ($attrsParams->getParam('resSortImg') || $attrsParams->getParam('resAscSortImg') || $attrsParams->getParam('resDescSortImg') || $attrsParams->getParam('resSortImgCheckbox')) && $sortMethod}
{$attrsParams->getParam('resSortImgPre', '')}
{if $attrsParams->getParam('resSortImg') || $attrsParams->getParam('resAscSortImg') || $attrsParams->getParam('resDescSortImg')}
{assign var="resSortImg" value=$attrsParams->getParam('resSortImg')}
{if $attrsParams->getParam('resAscSortImg') && $sortMethod eq $sortAscStr}
	{assign var="resSortImg" value=$attrsParams->getParam('resAscSortImg')}
{/if}
{if $attrsParams->getParam('resDescSortImg') && $sortMethod eq $sortDescStr}
	{assign var="resSortImg" value=$attrsParams->getParam('resDescSortImg')}
{/if}
<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.submit();"><img{if $attrsParams->getParam('resSortImgAlign')} align="{$attrsParams->getParam('resSortImgAlign')}"{/if} border="0"{if $attrsParams->getParam('resSortImgClass')} class="{$attrsParams->getParam('resSortImgClass')}"{/if} src="{$resSortImg}"{if $resSortResource} alt="{$resources->getString($resSortResource)}" title="{$resources->getString($resSortResource)}"{/if} /></a>
{else}
<input type="checkbox" name="{$attr}ResCB" checked="checked" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.submit();"{if $attrsParams->getParam('resSortImgClass')} class="{$attrsParams->getParam('resSortImgClass')}"{/if}{if $resSortResource} alt="{$resources->getString($resSortResource)}" title="{$resources->getString($resSortResource)}"{/if} />
{/if}
{$attrsParams->getParam('resSortImgPost', '')}
{/if}
{if $idx == 3}
{if $isSortable && $attrsParams->getParam('defaultSort') && !$attrsParams->getParam('ascSortImg') && !$attrsParams->getParam('descSortImg') && !$attrsParams->getParam('sortImg')}<a href="javascript:{ldelim}{rdelim}" onclick="clearLLPFields();document.forms.{$LLP_DATA->formName}.{$LLP_DATA->getSortMethodPrefix($attr, $attrsParams->getParam('defaultSort'))}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$LLP_DATA->getToggleSortMethodPrefix($attr, $attrsParams->getParam('defaultSort'))}{if !$sortOneAttrOnly}{$attr}{/if}.value='{$attr}';{if !$sortOneAttrOnly}document.forms.{$LLP_DATA->formName}.{$sortOrderPrefix}0.value='{$attr}';{/if}document.forms.{$LLP_DATA->formName}.submit();">{/if}{$LLP_DATA->entity->getEntityLabel($attr)}{if $isSortable && $attrsParams->getParam('defaultSort') && !$attrsParams->getParam('ascSortImg') && !$attrsParams->getParam('descSortImg') && !$attrsParams->getParam('sortImg')}</a>{/if}
{/if}
{/foreach}
{if $isSortable && $attrsParams->getParam('sortImg')}
<map id="{$attr}_sortMap" name="{$attr}_sortMap">
{if $attrsParams->getParam('sortImgAscCoords')}
	<area shape="{$attrsParams->getParam('sortImgAscShape', 'rect')}" coords="{$attrsParams->getParam('sortImgAscCoords')}" href="javascript:clearLLPFields();document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='{$attr}';{if !$sortOneAttrOnly}document.forms.{$LLP_DATA->formName}.{$sortOrderPrefix}0.value='{$attr}';{/if}document.forms.{$LLP_DATA->formName}.submit();"{if $sortAscResource} alt="{$resources->getString($sortAscResource)}" title="{$resources->getString($sortAscResource)}"{/if} />
{/if}
{if $attrsParams->getParam('sortImgDescCoords')}
	<area shape="{$attrsParams->getParam('sortImgDescShape', 'rect')}" coords="{$attrsParams->getParam('sortImgDescCoords')}" href="javascript:clearLLPFields();document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='{$attr}';{if !$sortOneAttrOnly}document.forms.{$LLP_DATA->formName}.{$sortOrderPrefix}0.value='{$attr}';{/if}document.forms.{$LLP_DATA->formName}.submit();"{if $sortAscResource} alt="{$resources->getString($sortDescResource)}" title="{$resources->getString($sortDescResource)}"{/if} />
{/if}
{if $attrsParams->getParam('sortImgResCoords')}
	<area shape="{$attrsParams->getParam('sortImgResShape', 'rect')}" coords="{$attrsParams->getParam('sortImgResCoords')}" href="javascript:clearLLPFields();document.forms.{$LLP_DATA->formName}.{$descSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.{$ascSortPrefix}{if !$sortOneAttrOnly}{$attr}{/if}.value='';document.forms.{$LLP_DATA->formName}.submit();"{if $resSortResource} alt="{$resources->getString($resSortResource)}" title="{$resources->getString($resSortResource)}"{/if} />
{/if}
</map>
{/if}

{if $sortOneAttrOnly && !$addedHiddenFields}
{assign var="sortAttr" value=$LLP_DATA->getFirstSortAttr()}
<input name="{$descSortPrefix}" type="hidden" value="{if $sortAttr && $LLP_DATA->getSortMethod($sortAttr) eq $sortDescStr}{$sortAttr}{/if}" />
<input name="{$ascSortPrefix}" type="hidden" value="{if $sortAttr && $LLP_DATA->getSortMethod($sortAttr) eq $sortAscStr}{$sortAttr}{/if}" />
{else}
{if !$addedHiddenFields}<input name="{$sortOrderPrefix}0" type="hidden" value="" />{/if}
<input name="{$sortOrderPrefix}{$order+1}" type="hidden" value="{$attr}" />
{/if}
{assign var="addedHiddenFields" value="1"}

{if $LLP_DATA->actionsPos === 'L'}{$LLP_DATA->incNumCols()}<{$cellTag}{if $attrsParams->getParam('attrLabelAlign')} align="{$attrsParams->getParam('attrLabelAlign')}"{/if}{if $attrsParams->getParam('cellClass')}{if $attrsParams->getParam('cellClassTag')} {$attrsParams->getParam('cellClassTag')}{else} class{/if}="{cycle name=$cycleName values=$attrsParams->getParam('cellClass')}"{/if}>{$actionStr}</{$cellTag}>{/if}
{assign var="colIndex" value=0}
{assign var="colIndex" value=$colIndex+0}
</{$cellTag}>
{assign var="colIndex" value=$colIndex+1}
{/foreach}
{if $LLP_DATA->actionsPos === 'R' || $LLP_DATA->actionsPos eq $colIndex}{$LLP_DATA->incNumCols()}<{$cellTag}{if $attrsParams->getParam('attrLabelAlign')} align="{$attrsParams->getParam('attrLabelAlign')}"{/if}{if $attrsParams->getParam('cellClass')}{if $attrsParams->getParam('cellClassTag')} {$attrsParams->getParam('cellClassTag')}{else} class{/if}="{cycle name=$cycleName values=$attrsParams->getParam('cellClass')}"{/if}>{$actionStr}</{$cellTag}>{/if}
</tr>
