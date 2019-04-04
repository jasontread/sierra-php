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
this view is an extension of sra-form-input.tpl which uses javascript and xhtml 
to create an input/textarea label/field combination value where the field will 
only be displayed when the user "clicks-in" and changes back to a label when the 
field loses focus. the label displayed will be either the current attribute 
value, or the attribute label when no attribute value exists. the label is 
rendered using a "span" xhtml element. the field element is hidden/shown using 
the 'display' (for textarea fields) or 'opacity' (for input fields). so, for 
input field, the normal tab from field to field behavior is preserved. however, 
for textarea fields this is not the case as the label MUST be clicks in order 
for them to be displayed


PARAMS:
Key            Type          Value/Default     Description

[attr]         [element tag] (value|[cycle])   see sra-attr.tpl - may also be 
                                               used to define the input "type" 
																							 if not the standard "text"
                                               
[cycle]        cycles        [csv cycle vals]  see sra-attr.tpl
                                               
cols                                           the # of cols for a textarea field 
                                               (default 20). not used when dynamicResize
                                               is true

dynamicResize                (1|0)/0           whether or not to dynamically 
                                               resize the field based on the value 
                                               it contains so that the value is 
                                               fully visible within the field. applied
                                               only to input field, not textareas

fieldName                                      if the form name should not be 
                                               the name of the attribute, the 
																							 actual name should be specified 
																							 using this parameter
																							 
fieldNamePre                                   prefix to add to the input field name. alternatively, 
                                               this value can be specified through a template parameter

fieldNamePost                                  postfix to add to the input field name. alternatively, 
                                               this value can be specified through a template parameter

labelClass                                     a class to assign to the label

noValueClass                                   the css class to assign to the 
                                               label/field when no value is present. 
                                               this class will be applied on top of 
                                               any existing class (duplicate 
                                               values in existing class will be 
                                               overriden)
                                               
noValueResource                                if specified, this value will be 
                                               displayed for the label when no value 
                                               is present for the attribute 
                                               INSTEAD of the attribute label
                                               
rows                                           the # of rows for a textarea field 
                                               (default 5). not used when dynamicResize
                                               is true

useTextArea                  (1|0)/0           whether or not to render the 
                                               attribute value in a textarea. the 
																							 default behavior is to render it in 
																							 an "input" element
                                               
Additionally, the template parameters, 'inputCiClass' and 'inputCiStyle' can be 
used to specify a custom css class or style to apply to this field (on top of 
the css class(es)/style(s) already applied through the view parameters). these 
class(es)/style(s) will be applied to both the input field and the click-in 
label. 'inputCiFieldClass', 'inputCiLabelClass', 'inputCiFieldStyle' and 
'inputCiLabelStyle' can likewise be used to specify field and label specific 
class(es)/style(s)
*}

{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', $Template->getVar('fieldNamePre'))}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', $Template->getVar('fieldNamePost'))}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{if ($Util->isObject($attribute) && $displayVal)}{assign var="attribute" value=$displayVal}{/if}
{if $Util->methodExists($attribute, 'getPrimaryKey')}{assign var="attribute" value=$attribute->getPrimaryKey()}{/if}
{if $params->getParam('useTextArea')}{assign var='ptype' value='textarea-attrs'}{else}{assign var='ptype' value='input-attrs'}{/if}

{assign var='labelClass' value=$params->getParam('labelClass')}
{if $labelClass}{$params->concat('class', $labelClass, 'span-attrs')}{/if}
{if $inputCiClass}{$params->concat('class', $inputCiClass, $ptype)}{$params->concat('class', $inputCiClass, 'span-attrs')}{/if}
{if $inputCiStyle}{$params->concat('style', $inputCiStyle, $ptype)}{$params->concat('style', $inputCiStyle, 'span-attrs')}{/if}
{if $inputCiFieldClass}{$params->concat('class', $inputCiFieldClass, $ptype)}{/if}
{if $inputCiFieldStyle}{$params->concat('style', $inputCiFieldStyle, $ptype)}{/if}
{if $inputCiLabelClass}{$params->concat('class', $inputCiLabelClass, 'span-attrs')}{/if}
{if $inputCiLabelStyle}{$params->concat('style', $inputCiLabelStyle, 'span-attrs')}{/if}
{assign var='inputBaseClass' value=$params->getParam1('class', $ptype)}
{assign var='spanBaseClass' value=$params->getParam1('class', 'span-attrs')}
{assign var='noValueClass' value=$params->getParam('noValueClass')}
{assign var='noValueResource' value=$params->getParam('noValueResource')}
{if $noValueResource}{assign var='noValueStr' value=$entity->getEntityResourcesString($noValueResource)}{else}{assign var='noValueStr' value=$attributeLabel}{/if}
{if !$attribute}{assign var='attribute' value=$noValueStr}{/if}
{if $noValueClass && $attribute eq $noValueStr}{$params->concat('class', $noValueClass, $ptype)}{$params->concat('class', $noValueClass, 'span-attrs')}{/if}
{assign var='inputNoValueClass' value=$params->getParam1('class', $ptype)}
{assign var='spanNoValueClass' value=$params->getParam1('class', 'span-attrs')}
{$params->concat('style', 'cursor:pointer;', $ptype)}
{$params->concat('style', 'cursor:pointer;', 'span-attrs')}
{if $noValueClass}{assign var='inputNoValueClass' value=$inputNoValueClass|cat:' '|cat:$noValueClass}{assign var='spanNoValueClass' value=$spanNoValueClass|cat:' '|cat:$noValueClass}{/if}
{assign var='escapedLabel' value=$Template->escapeSingleQuotes($noValueStr)}
{if $params->getParam('useTextArea')}{assign var='hideCss' value="display='none'"}{assign var='showCss' value="display=''"}{assign var='hideCss' value="this.style.display='none'"}{else}{assign var='hideCss' value="this.style.position='absolute'; this.style.opacity=0"}{assign var='showCss' value="opacity=1"}{/if}
{if $params->getParam('useTextArea')}{$params->concat('style', 'display:none', $ptype)}{else}{$params->concat('style', 'position:absolute; opacity:0; overflow:hidden;', $ptype)}{/if}
{assign var='onfocus' value="this.style.cursor='auto'; this.style.position='static'; this.style."|cat:$showCss|cat:"; this.onkeyup(); var label=this.nextSibling.nextSibling; label.style.position='absolute'; label.style.visibility='hidden'; this.select();"}
{assign var='onblur' value="this.style.cursor='pointer';"|cat:$hideCss|cat:"; var label=this.nextSibling.nextSibling; label.style.position='static'; label.style.visibility='inherit'; this.onkeyup(); if (this.value == '"|cat:$escapedLabel|cat:"') this.value='';"}
{assign var='valueCode' value='this.value'}
{if $params->getParam('useTextArea')}{assign var='valueCode' value=$valueCode|cat:".replace(String.fromCharCode(10), '<br />').replace(String.fromCharCode(13), '<br />')"}{/if}
{assign var='onkeyup' value="var label=this.nextSibling.nextSibling; label.innerHTML="|cat:$valueCode|cat:"; var empty=this.value=='' || this.value.replace(' ', '')==''; var ciClass=empty || this.value=='"|cat:$escapedLabel|cat:"' ? '"|cat:$inputNoValueClass|cat:"' : '"|cat:$inputBaseClass|cat:"'; var ciSpanClass=empty || this.value=='"|cat:$escapedLabel|cat:"' ? '"|cat:$spanNoValueClass|cat:"' : '"|cat:$spanBaseClass|cat:"'; ciClass=ciClass?ciClass:null; ciSpanClass=ciSpanClass?ciSpanClass:null; this.className=ciClass; label.className=ciSpanClass; if (empty) this.value='"|cat:$escapedLabel|cat:"'; if (empty) label.innerHTML='"|cat:$escapedLabel|cat:"'; if (empty) this.select();"}
{if $params->getParam('dynamicResize') && !$params->getParam('useTextArea')}{$params->concat('style', 'overflow:hidden;', $ptype)}{assign var='onkeyup' value=$onkeyup|cat:"this.style.width=(label.offsetWidth+4)+'px';"}{/if}
{if $params->getParam('useTextArea')}{assign var='onfocus' value=$onfocus|cat:" this.select();"}{/if}
{$params->concat('onblur', $onblur, $ptype)}
{$params->concat('onfocus', $onfocus, $ptype)}
{$params->concat('onkeyup', $onkeyup, $ptype)}

{* textarea *}
{if $params->getParam('useTextArea')}
{$Template->renderOpen($tplName, 'textarea', $params, '', 0)} name="{$fieldName}" cols="{$params->getParam('cols', 20)}" rows="{$params->getParam('rows', 5)}">{if $attribute neq $noValueStr}{$attribute}{/if}</textarea>
{* input field *}
{else}
{$Template->renderOpen($tplName, 'input', $params, '', 0)} name="{$fieldName}" value="{if $attribute neq $noValueStr}{$Template->escapeHtmlQuotes($attribute)}{/if}" />
{/if}

{* label *}
{$Template->renderOpen($tplName, 'span', $params, '', 0)} onclick="var field=this.previousSibling.previousSibling;{if $params->getParam('useTextArea')} field.style.display='';{/if} field.focus();">{if $params->getParam('useTextArea')}{$Template->lineBreaksToBr($attribute)}{else}{$attribute}{/if}</span>
