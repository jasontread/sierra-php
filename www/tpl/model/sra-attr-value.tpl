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
This is a helper template for sra-attr.tpl. It is used to recursively display 
attribute values
*}

{assign var="myParams" value=$Template->getVarByRef('sraAttrParams')}
{assign var="tplName" value="sra-attr"}
{assign var="displayCondsParams" value=$params->getTypeSubset('displayConds')}
{assign var="formatStrParams" value=$Template->getVarByRef('formatStrParams')}
{assign var="enclParams" value=$Template->getVarByRef('enclParams')}
{assign var="posParams" value=$Template->getVarByRef('posParams')}
{assign var="postParams" value=$Template->getVarByRef('postParams')}
{assign var="postTplParams" value=$Template->getVarByRef('postTplParams')}
{assign var="preParams" value=$Template->getVarByRef('preParams')}
{assign var="preTplParams" value=$Template->getVarByRef('preTplParams')}
{assign var="tplParams" value=$Template->getVarByRef('tplParams')}
{assign var="attrVal" value=$Template->getVarByRef('sraAttrVal')}
{assign var="maxEnclElems" value=$enclParams->getParam('maxEnclElems', 5)}
{if $myParams->getParam('useOptionsLabel')}{assign var="options" value=$entity->getOptionsMap($attributeName, 1)}{/if}

{* value is an array *}
{if $Template->isArray($attrVal)}
{assign var=started value=0}
{foreach from=$attrVal item=arrayVal}
{if $started && $myParams->getParam('between')}{$myParams->getParam('between')}{/if}
{if $myParams->getParam('useOptionsLabel') && $options && $options[$arrayVal]}{assign var="arrayVal" value=$options[$arrayVal]}{/if}
{if $myParams->getParam('convertHtml') || ($Template->defined('SRA_CONVERT_OUTPUT_TO_HTML') && $smarty.const.SRA_CONVERT_OUTPUT_TO_HTML)}{assign var="arrayVal" value=$arrayVal|escape:'html'}{/if}
{if $myParams->getParam('decimals') || $myParams->getParam('decimals') === '0'}{assign var="arrayVal" value=$Template->numberFormat($arrayVal, $myParams->getParam('decimals'))}{/if}
{* pre array val templates, text/html *}
{if $preTplParams->getParam('arrayVal')}{include file=$preTplParams->getParam('arrayVal')}{/if}
{$preParams->getParam('arrayVal')}
{* array val enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value="arrayVal"|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($tplName, $encl, $myParams, "arrayVal", 1, $arrayVal)}{/if}
{/foreach}

{* property value *}
{assign var="formatString" value=$formatStrParams->getParam('arrayVal')}

{* display using template *}
{if $tplParams->getParam('arrayVal')}
{assign var='attribute', $arrayVal}
{include file=$tplParams->getParam('arrayVal')}

{* display using entity parseString call *}
{elseif $formatString}
{if $Util->methodExists($arrayVal, 'parseString')}{$arrayVal->parseString($formatString)}{else}{$entity->parseString($formatString)}{/if}

{* display in raw form *}
{else}
{if $Template->isObject($arrayVal) || $Template->isArray($arrayVal)}
{assign var='sraAttrVal', $arrayVal}
{include file='sra-attr-value.tpl'}
{else}

{* check for display condition *}
{assign var="condCode" value=$property|cat:$arrayVal}
{assign var="condCodeF" value=$property|cat:"FALSE"}
{assign var="condCodeT" value=$property|cat:"TRUE"}

{* if display condition exists, then change the display value based on the value for that condition *}
{if $displayCondsParams->getParam($condCode)}
{assign var="tmp" value=$displayCondsParams->getParam($condCode)}
{assign var="arrayVal" value=$entity->parseString($tmp)}

{* if boolean display condition exists, then use the appropriate display value *}
{elseif $displayCondsParams->getParam($condCodeF) && $displayCondsParams->getParam($condCodeT)}
{if $arrayVal}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeT)}
{assign var="arrayVal" value=$entity->parseString($tmp)}
{else}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeF)}
{assign var="arrayVal" value=$entity->parseString($tmp)}
{/if}
{/if}

{$arrayVal}
{/if}
{/if}

{* array val enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value="arrayVal"|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}</{$encl}>{/if}
{/foreach}
{* post array val templates, text/html *}
{$postParams->getParam('arrayVal')}
{if $postTplParams->getParam('arrayVal')}{include file=$postTplParams->getParam('arrayVal')}{/if}

{assign var=started value=1}
{/foreach}



{* value is an object *}
{elseif $Template->isObject($attrVal)}

{* has render method *}
{if $Util->methodExists($attrVal, 'render')}
{$Util->invokeMethod($attrVal, 'render')}

{* has toString method *}
{elseif $Util->methodExists($attrVal, 'toString')}
{$Util->invokeMethod($attrVal, 'toString')}

{* use Util::objectToString *}
{else}
{$Util->objectToString($attrVal)}
{/if}



{* scalar value - just display *}
{else}

{* check for display condition *}
{assign var="condCode" value=$property|cat:$attrVal}
{assign var="condCodeF" value=$property|cat:"FALSE"}
{assign var="condCodeT" value=$property|cat:"TRUE"}

{* if display condition exists, then change the display value based on the value for that condition *}
{if $displayCondsParams->getParam($condCode)}
{assign var="tmp" value=$displayCondsParams->getParam($condCode)}
{assign var="attrVal" value=$entity->parseString($tmp)}

{* if boolean display condition exists, then use the appropriate display value *}
{elseif $displayCondsParams->getParam($condCodeF) && $displayCondsParams->getParam($condCodeT)}
{if $attrVal}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeT)}
{assign var="attrVal" value=$entity->parseString($tmp)}
{else}
{assign var="tmp" value=$displayCondsParams->getParam($condCodeF)}
{assign var="attrVal" value=$entity->parseString($tmp)}
{/if}
{/if}

{$attrVal}
{/if}
