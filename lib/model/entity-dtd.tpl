
<!--
{$entity->getDtdName()}: {if $entity->_apiResource}{$resources->getString($entity->_apiResource)}{else}{$resources->getString($entity->_resource)}{if $entity->_resourceHelp} - {$resources->getString($entity->_resourceHelp)}{/if}{/if}


{foreach from=$entity->getAttributes() item=attribute}
{if $attribute}{$attribute->getNestedElementDtdName()|string_format:"%-25s"}{if $attribute->_apiResource}{$resources->getString($attribute->_apiResource)}{else}{$resources->getString($attribute->_resource)}{if $attribute->_resourceHelp} - {$resources->getString($attribute->_resourceHelp)}{/if}{/if}{if $attribute->_type eq $smarty.const.SRA_DATA_TYPE_BOOLEAN} - {$resources->getString('text.attr-zero-or-one')}{/if}
{/if}


{/foreach}
-->
<!ELEMENT {$entity->getDtdName()} {if $entity->getDtdElements()}({assign var="started" value="0"}{foreach from=$entity->getDtdElements() item=attribute}{if $started}, {/if}{$attribute->getNestedElementDtdName()}{$attribute->getNestedElementDtdModfier()}{assign var="started" value="1"}{/foreach}{foreach from=$entity->_renderAppend item=property}{if $started}, {/if}{if $entity->_dtdCamelCase}{$entity->_name}{$property}{else}{$Util->camelCaseToDashes($entity->_name)}-{$Util->camelCaseToDashes($property)}{/if}?{assign var="started" value="1"}{/foreach}){else}EMPTY{/if}>
{foreach from=$entity->getDtdAttributes() item=attribute}
<!ATTLIST {$entity->getDtdName()} {$attribute->getDtdName()} CDATA #IMPLIED>
{/foreach}
{foreach from=$entity->getDtdElements() item=attribute}
<!ELEMENT {$attribute->getNestedElementDtdName()} ({if $attribute->isEntity() || $attribute->_isFile}{$attribute->getSubElementDtdName()}{else}#PCDATA{/if})>
{if $attribute->_cardinality}
<!ATTLIST {$attribute->getNestedElementDtdName()} key CDATA #IMPLIED>
{/if}
{/foreach}
{foreach from=$entity->_renderAppend item=property}
<!ELEMENT {if $entity->_dtdCamelCase}{$entity->_name}{$property}{else}{$Util->camelCaseToDashes($entity->_name)}-{$Util->camelCaseToDashes($property)}{/if} (#PCDATA)>
{/foreach}
