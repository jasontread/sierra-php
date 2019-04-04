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
this view can be used to create a "click-in" effect from one attribute view to 
another. for example, if you wanted to display a label by default, but have that 
label change to a drop down select field whenever the user clicks on it. this 
view uses xhtml spans, css (position & visibility) and javascript to implement 
this behavior


PARAMS:
Key            Type          Value/Default     Description

view                                           the view to display by default. 
                                               if not specified, the raw attribute 
                                               value will be used
                                               
ciView                                         the click-in view (REQUIRED)

hideMSecs                                      the # of milliseconds to leave the 
                                               ciView displayed over the the users
                                               mouse cursor leaves the span it is 
                                               in. default is 1000 (1 second)
                                               
labelClass                                     a class to assign to the label
                                               
noValueClass                                   the css class to assign to the 
                                               label/field when no value is present. 
                                               this class will be applied on top of 
                                               any existing class (duplicate 
                                               values in existing class will be 
                                               overriden)
                                               
noValueResource                                if specified, this value will be 
                                               displayed when 'showLabel' is true 
                                               and no value is present for the attribute 
                                               INSTEAD of the attribute label
                                               
showLabel                    (0|1)/1           whether or not to show the attribute 
                                               label if the value is not present
                                               
syncValue                    (0|1)/0           if this view is being used to toggle 
                                               a field, where the label is the current 
                                               value of that field, this parameter 
                                               may be set to true in which case 
                                               the value displayed in the label 
                                               will be updated whenever the field 
                                               is changed. ONLY text and select 
                                               fields (single select only) are supported. 
                                               additionally, when true, the field will 
                                               be given focus whenever ciView is 
                                               displayed. the click-in will NOT 
                                               occur if the field is disabled
*}

{assign var='myParams' value=$Template->getVar('params')}
{assign var='view' value=$myParams->getParam('view')}
{assign var='ciView' value=$myParams->getParam('ciView')}
{assign var='hideMSecs' value=$myParams->getParam('hideMSecs', 1000)}
{assign var='labelClass' value=$myParams->getParam('labelClass')}
{assign var='noValueClass' value=$myParams->getParam('noValueClass')}
{if $labelClass && $noValueClass}{assign var='noValueClass' value=$noValueClass|cat:' '|cat:$labelClass}{/if}
{assign var='noValueResource' value=$myParams->getParam('noValueResource')}
{if $noValueResource}{assign var='noValueStr' value=$entity->getEntityResourcesString($noValueResource)}{else}{assign var='noValueStr' value=$attributeLabel}{/if}
{assign var='showLabel' value=$myParams->getParam('showLabel', 1)}
{assign var='syncValue' value=$myParams->getParam('syncValue')}

{if $ciView}
<span onmouseover="if (this._hideTimer) {ldelim} clearTimeout(this._hideTimer); this._hideTimer=null; {rdelim}" onmouseout="if (this._hideTimer) this.onmouseover(); document._ciLabel=this.nextSibling.nextSibling; this._hideTimer=setTimeout('document._ciLabel._showCi(true)', {$hideMSecs});" style="position:absolute; visibility:hidden;">{$entity->renderAttribute($attributeName, $ciView)}</span>
<span {if $labelClass || ($noValueClass && $attribute===NULL)}class="{if $noValueClass && $attribute===NULL}{$noValueClass}{else}{$labelClass}{/if}" {/if}onclick="this._showCi();" onmouseover="if (!this._initialized) {ldelim} this._initialized=true; this._showCi=function(hide) {ldelim} {if $syncValue}var field=this._getField(); {/if} if (hide) document._ciLabel=null; this.style.position=hide ? 'static' : 'absolute'; this._visible=hide; this.style.visibility=hide ? 'inherit' : 'hidden'; var ci=this.previousSibling.previousSibling; ci.style.position=hide ? 'absolute' : 'static'; ci[hide ? 'onmouseover' : 'onmouseout'](); ci.style.visibility=hide ? 'hidden' : 'inherit'; {if $syncValue} if (field) {ldelim} if (hide) {ldelim} var value=field.options ? (field.options[field.selectedIndex] && field.options[field.selectedIndex].value ? field.options[field.selectedIndex].text : '') : field.value; var blank=value.replace(' ', '')=='' || value=='{$noValueStr}'; this.innerHTML=blank ? {if !$showLabel}this.innerHTML{else}'{$noValueStr}'{/if} : value; {if $noValueClass} this.className=blank ? '{$noValueClass}' : {if $labelClass}'{$labelClass}'{else}null{/if}; {/if} field.style.overflow='hidden'; {rdelim} else if (!hide)  field.style.overflow='visible'; field.focus(); if (field.select) field.select(); {rdelim} {/if} {rdelim}; {if $syncValue} this._getField=function(start) {ldelim} start=start ? start : this.previousSibling.previousSibling; var attrName='{$attributeName}'; if (start.name && start.name.indexOf(attrName) !== -1) return start; else {ldelim} if (start.childNodes && start.childNodes.length > 0) {ldelim} for(var i=0; i<start.childNodes.length; i++) {ldelim} var sub=this._getField(start.childNodes[i]); if (sub) return sub; {rdelim} {rdelim} {rdelim} return null; {rdelim}; var field=this._getField(); if (field) {ldelim} field._ciLabel=this; {rdelim}{/if} this.onmouseover=null; {rdelim}" style="cursor:pointer">{if !$showLabel || $attribute !== NULL}{if $view}{$entity->renderAttribute($attributeName, $view)}{else}{$attribute}{/if}{else}{$noValueStr}{/if}</span>
{/if}
