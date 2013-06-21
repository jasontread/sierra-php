<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$Controller->getAppShortName()} :: {$entity->getEntityType()}</title>
{if $wsCssUri}
  <link rel="stylesheet" type="text/css" href="{$wsCssUri}" />
{else}
  <style type="text/css">
{php}include(SRA_DIR . '/www/tpl/model/sra-ws-api.css');{/php}
  </style>
{/if}
</head>
<body id="wsDetail">
  <h1>{$Controller->getAppShortName()} :: {$entity->getEntityType()}</h1>
  
  <p id="wsEntity">
    <table>
      <tr>
        <th id="wsAttrName">{$apiResources->getString('service.attribute')}</th>
        <th id="wsAttrReadOnly">{$apiResources->getString('service.attributeReadOnly')}</th>
        <th id="wsAttrSetOnly">{$apiResources->getString('service.attributeSetOnly')}</th>
        <th id="wsAttrDataType">{$apiResources->getString('service.attributeDataType')}</th>
        <th id="wsAttrRequired">{$apiResources->getString('text.required')}</th>
        <th id="wsAttrDesc">{$apiResources->getString('service.attributeDesc')}</th>
      </tr>
{foreach from=$entity->getWsAttributes() item=attr}
      <tr>
        <td class="wsAttrName">{$attr}{if $entity->getPrimaryKeyAttribute() eq $attr}<sup>1</sup>{assign var=pkFound value=1}{/if}</td>
        <td class="wsAttrReadOnly">{if $entity->isAttributeReadOnly($attr)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
        <td class="wsAttrSetOnly">{if $entity->isAttributeSetOnly($attr)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
        <td class="wsAttrDataType">{if $entity->attributeIsEntity($attr)}<a href="{$Template->strReplace('[entity]', $entity->getAttributeType($attr), $entityUri)}">{/if}{if $entity->getAttributeType($attr)}{$entity->getAttributeType($attr)}{if $entity->attributeIsEntity($attr)}</a>{/if}{if $entity->getAttributeCardinality($attr)}[{$entity->getAttributeCardinality($attr)}]{/if}{else}{$smarty.const.SRA_DATA_TYPE_STRING}{/if}</td>
        <td id="wsAttrRequired">{if $entity->isAttributeReadOnly($attr)}{$apiResources->getString('text.na')}{elseif $entity->isAttributeRequired($attr, 1)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
        <td class="wsAttrDesc">{if $entity->getHelpContent($attr)}{$entity->getHelpContent($attr)}{else}{$entity->getEntityLabel($attr)}{/if}{if $entity->isAttributeSetOnly($attr)}. {$apiResources->getString('service.attribute.notIncluded')}{/if}</td>
      </tr>
{/foreach}
    </table>
{if $pkFound}
    <sup>1</sup> {$apiResources->getString('service.primaryKey')}
{/if}
  </p>
  
  <p id="wsOverview"><a href="{$apiUri}">{$apiResources->getString('service.overviewLink')}</a></p>
</form>
</body>
</html>