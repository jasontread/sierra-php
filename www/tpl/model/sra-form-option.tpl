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
Base class used to display an attribute value where the scope of that value is 
limited to a set of options defined for that attribute. This template is 
typically used in conjunction with sra-attr.tpl


PARAM KEY DEFINITIONS:
[attr]:       html attribute
[element id]: element identifier. uses the following format ([-[property]] is optional): 
               [element tag][-[property]]-attrs
[property]:   property of the output. only applicable to non-select options:
               input:     the option input field (radio button or checkbox)
               label:     the option label 
							 option:    the option field for an option


PARAMS:
Key            Type           Value/Default     Description

[attr]         [element id]   (value|[cycle])   see sra-attr.tpl
                                               
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
                                                
includeOtherOption            (1|0)/0           whether or not to include an "other" 
                                                option. for select fields this will be 
                                                an additional option at the bottom of 
                                                the selector that when selected will 
                                                use a javascript 'prompt' to ask the user
                                                for an other value. for radio 
                                                buttons, the other option will be 
                                                implemented as an additional option 
                                                that when clicked will do the same.
                                                other options CANNOT be used with 
                                                checkbox type options
																							 
maxEnclElems   encl           (1-N)/5           see sra-attr.tpl
                                               
[property][N]  encl           [html element]    see sra-attr.tpl

[property][N]  enclClose      (0|1)/0           see sra-attr.tpl
                                               
[property]     pos            (0|1|2)/          see sra-attr.tpl - only applies where useSelect is 0
                               input=1
                               label=2                     

[property]     post           [html/text]       see sra-attr.tpl

[property]     postTpl        [template name]   see sra-attr.tpl

[property]     pre            [html/text]       see sra-attr.tpl

[property]     preTpl         [template name]   see sra-attr.tpl

otherOptionCallback                             if 'includeOtherOption' is true, this 
                                                value may be specified defining a static 
                                                method callback to invoke when the 
                                                user enters an "other" value. this 
                                                method should have the signature: 
                                                (field : Object, value : String, text : String) : void
                                                where value is the option value and 
                                                text is the option text
                                                
otherOptionLabel              [resource]        if 'includeOtherOption' is true, this 
                                                value may be specified defining the 
                                                other option text. if not specified, the 
                                                default 'model.form-option.other-label' 
                                                in sierra.properties will be used
                                                
otherOptionPrompt             [resource]        if 'includeOtherOption' is true, this 
                                                value may be specified defining the 
                                                prompt text. if not specified, the 
                                                default 'model.form-option.other-prompt' 
                                                in sierra.properties will be used
																							 
useSelect                     (1|0)/1           whether or not to render the options 
                                                in a select input field. this is  
																							  the default behavior. to display 
																							  them utilizing checkboxes or radio 
																							  buttons, set this parameter to 0, 
																							  and add an [attr] (above) for "type" 
																							  (element "input") with the value 
																							  "radio" or "checkbox" (i.e. <input type="checkbox"...)
                                                
useLabels                     (1|0)/0           whether or not to use labels if useSelect is 0
                                                this will cause each checkbox/radio + text to be 
                                                surrounded by a label so either the input field
                                                or the label can be clicked to change the value
																								
[val]          options        [resource]        defines the options that should be displayed 
                                                where [val] is the value that will be stored 
																								if that option is selected, and [resource] is 
																								the resources key for the label for that option.
																								if not specified, the $entity->getOptionsMap 
																								will be used to determine the options to 
																								render. If [val] is equal to a PHP boolean 
																								constant, then the Util::convertBoolean and 
																								Util::isBoolean methods will be used to determine 
																								which option is selected
*}

{assign var="tplName" value="sra-form-option"}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{assign var="evalAttribute" value=$attribute}
{assign var="optionsParams" value=$params->getTypeSubset('options')}
{assign var="filterAttrEqParams" value=$params->getTypeSubset('filterAttrEq')}
{assign var="filterAttrNeqParams" value=$params->getTypeSubset('filterAttrNeq')}
{if $optionsParams->getCount()}
{assign var="lookupVals" value="1"}
{assign var="options" value=$optionsParams->getParams()}
{else}
{assign var="lookupVals" value="0"}
{assign var="options" value=$entity->getOptionsMap($attributeName, 1)}
{/if}

{assign var="useOtherOption" value="0"}
{if $params->getParam('includeOtherOption') && !$params->getParam('type', 0, 'checkbox')}
{assign var="otherOptionLabel" value=$params->getParam('otherOptionLabel')}
{if $otherOptionLabel}{assign var="otherOptionLabel" value=$entity->getEntityResourcesString($otherOptionLabel)}{else}{assign var="otherOptionLabel" value=$resources->getString('model.form-option.other-label')}{/if}
{assign var="otherOptionLabelEscaped" value=$Template->escapeHtmlQuotes($otherOptionLabel)}
{assign var="otherOptionPrompt" value=$params->getParam('otherOptionPrompt')}
{if $otherOptionPrompt}{assign var="otherOptionPrompt" value=$entity->getEntityResourcesString($otherOptionPrompt)}{else}{assign var="otherOptionPrompt" value=$resources->getString('model.form-option.other-prompt')}{/if}
{assign var="otherOptionPrompt" value=$Template->escapeHtmlQuotes($otherOptionPrompt)}
{assign var="otherOptionAttr" value="_OTHER_OPTION_"}
{$Template->arrayPush($options, $otherOptionLabel, $otherOptionAttr)}
{assign var="useOtherOption" value="1"}
{/if}

{* select input field *}
{if $params->getParam('useSelect', 1)}
{assign var="useLabels" value=$params->getParam('useLabels', 1)}
{if $params->getParam('includeOtherOption')}
{assign var='onchange' value="var otherIdx=this.options.length-1; if (this.selectedIndex==otherIdx) "|cat:$smarty.ldelim|cat:" var other = prompt('"|cat:$otherOptionPrompt|cat:"', this.options[otherIdx].value); if (other != null) "|cat:$smarty.ldelim|cat:" this.options[otherIdx].value=other; this.options[otherIdx].text=other ? other : '"|cat:$otherOptionLabelEscaped|cat:"'; "}
{assign var='otherOptionCallback' value=$params->getParam('otherOptionCallback')}
{if $otherOptionCallback}{assign var='onchange' value=$onchange|cat:$otherOptionCallback|cat:"(this, other, other ? other : '"|cat:$otherOptionLabelEscaped|cat:"')"}{/if}
{assign var='onchange' value=$onchange|cat:$smarty.rdelim|cat:$smarty.rdelim}
{$params->concat('onchange', $onchange, 'select-attrs')}
{/if}
{$Template->renderOpen($tplName, 'select', $params, '', 0)} name="{$fieldName}">
{if $params->getParam('firstOption')}{$Template->renderOpen($tplName, 'option', $params, '', 0)} value="">{$resources->getString($params->getParam('firstOption'))}</option>{/if}

{assign var="optionSet" value="0"}
{assign var="foundAttr" value="0"}
{foreach from=$options key=attr item=label}
{assign var="optionSet" value="1"}
{if $lookupVals}{assign var="label" value=$resources->getString($label)}{/if}
{$Template->renderOpen($tplName, 'option', $params, 'option', 0, 0, 1)}{if ($useOtherOption && $attr eq $otherOptionAttr && !$foundAttr && $attribute) || $Util->equal($evalAttribute, $attr)} selected="selected"{/if} value="{if $useOtherOption && $attr eq $otherOptionAttr}{if !$foundAttr && $attribute}{$Template->escapeHtmlQuotes($attribute)}{/if}{else}{$Template->escapeHtmlQuotes($attr)}{/if}">{if $useOtherOption && $attr eq $otherOptionAttr && !$foundAttr && $attribute}{$attribute}{else}{$label}{/if}</option>
{if $Util->equal($evalAttribute, $attr)}{assign var="foundAttr" value="1"}{/if}
{/foreach}
</select>


{* checkbox/radio button *}
{else}
{assign var="enclParams" value=$params->getTypeSubset('encl')}
{assign var="enclCloseParams" value=$params->getTypeSubset('enclClose')}
{assign var="posParams" value=$params->getTypeSubset('pos')}
{assign var="postParams" value=$params->getTypeSubset('post')}
{assign var="postTplParams" value=$params->getTypeSubset('postTpl')}
{assign var="preParams" value=$params->getTypeSubset('pre')}
{assign var="preTplParams" value=$params->getTypeSubset('preTpl')}
{assign var="maxEnclElems" value=$enclParams->getParam('maxEnclElems', 5)}
{assign var="labelPos" value=$posParams->getParam('label')}
{assign var="inputPos" value=$posParams->getParam('input')}
{if !$labelPos}{assign var="labelPos" value="2"}{/if}
{if !$inputPos}{assign var="inputPos" value="1"}{/if}
{assign var="foundAttr" value="0"}

{foreach from=$options key=attr item=label}
{if $useLabels}{$Template->renderOpen($tplName, 'label', $params, $property, 1, 0, 1)}{/if}
{if $lookupVals}{assign var="label" value=$resources->getString($label)}{/if}
{if $Util->equal($evalAttribute, $attr)}{assign var="foundAttr" value="1"}{/if}

{foreach from=$Util->getArray(2) item=idx}
{assign var="property" value=""}
{if $idx eq $inputPos}{assign var="property" value="input"}
{elseif $idx eq $labelPos}{assign var="property" value="label"}{/if}

{if $property}

{* pre option templates, text/html *}
{if $preTplParams->getParam($property)}{include file=$preTplParams->getParam($property)}{/if}
{$preParams->getParam($property)}

{* option enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{if !$enclCloseParams->getParam($tmp)}{$Template->renderOpen($attrTplName, $encl, $attrParams, $property, 1, $displayVal, 1)}{else}{$Template->renderOpen($attrTplName, $encl, $attrParams, $property, 0, $displayVal, 1)} />{/if}{/if}
{/foreach}

{if $property eq "label"}{if $useOtherOption && $attr eq $otherOptionAttr}<span id="{$fieldName}OtherLabel">{if $useOtherOption && $attr eq $otherOptionAttr && !$foundAttr && $attribute}{$attribute}{else}{$label}{/if}</span>{else}{$label}{/if}
{else}{$Template->renderOpen($tplName, 'input', $params, $property, 0, 0, 1)} name="{$fieldName}{if $params->getParam('type', 0, 'checkbox')}_{$Template->escapeHtmlQuotes($attr)}{/if}"{if (($useOtherOption && $attr eq $otherOptionAttr && !$foundAttr && $attribute) || $Util->equal($evalAttribute, $attr) || ($entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_BOOLEAN && $params->getParam('type', 0, 'radio') && !$attribute && !$attr && $attribute !== $smarty.const.NULL))} checked="checked"{/if} value="{if $useOtherOption && $attr eq $otherOptionAttr}{if !$foundAttr && $attribute}{$Template->escapeHtmlQuotes($attribute)}{/if}{else}{$Template->escapeHtmlQuotes($attr)}{/if}"{if $useOtherOption && $attr eq $otherOptionAttr} onchange="if (this.checked) {ldelim} var other = prompt('{$otherOptionPrompt}', this.value); this.value=other; document.getElementById('{$fieldName}OtherLabel').innerHTML=other ? other : '{$otherOptionLabelEscaped}';{if $params->getParam('type', 0, 'checkbox')} this.name='{$fieldName}_'+other;{/if} {rdelim}"{/if} />{/if}

{* option enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl && !$enclCloseParams->getParam($tmp)}</{$encl}>{/if}
{/foreach}

{* post option templates, text/html *}
{$postParams->getParam($property)}
{if $postTplParams->getParam($property)}{include file=$postTplParams->getParam($property)}{/if}

{/if}

{/foreach}
{if $useLabels}</label>{/if}
{/foreach}
{/if}
