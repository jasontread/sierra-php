<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$Controller->getAppShortName()} :: {$service->_id}</title>
{if $wsCssUri}
  <link rel="stylesheet" type="text/css" href="{$wsCssUri}" />
{else}
  <style type="text/css">
{php}include(SRA_DIR . '/www/tpl/model/sra-ws-api.css');{/php}
  </style>
{/if}
</head>
<body id="wsDetail">
<form>
{if $service->isPublic()}
  <h1>{$Controller->getAppShortName()} :: {$service->_id}</h1>
  {if $service->_api || ($api && $api.comment)}<p id="wsApi">{if $service->_api}{$appResources->getString($service->_api)}{/if}{if $api && $api.comment}{if $service->_api}<br /><br />{/if}{$api.comment}{/if}</p>
{/if}
{if !$service->_global && ($service->_retrieve || $service->_create || $service->_update)}
  <h2 id="entityDesc">{$entity->getEntityType()}</h2>
  <p id="wsEntity">
    {$apiResources->getString('service.entityDesc')} 
    {assign var=started value=0}
    {if $service->_retrieve}<a href="#retrieve">{$apiResources->getString('text.retrieve')}</a>{if $service->_create || $service->_update || $service->_delete}{if !$service->_update && !$service->_delete} {$apiResources->getString('text.or')} {else}, {/if}{/if}{/if}
    {if $service->_create}<a href="#create">{$apiResources->getString('text.create')}</a>{if $service->_update || $service->_delete}{if !$service->_delete} {$apiResources->getString('text.or')} {else}, {/if}{/if}{/if}
    {if $service->_update}<a href="#update">{$apiResources->getString('text.update')}</a>{if $service->_delete} {$apiResources->getString('text.or')} {/if}{/if}
    {if $service->_delete}<a href="#delete">{$apiResources->getString('text.delete')}</a>{/if}
    {$apiResources->getString('service.entityDesc1')}
    <span class="wsEntityName">{$entity->getEntityType()}</span>
    {$apiResources->getString('service.entityDesc2')}
    <table>
      <tr>
        <th id="wsAttrName">{$apiResources->getString('service.attribute')}</th>
        <th id="wsAttrReadOnly">{$apiResources->getString('service.attributeReadOnly')}</th>
        <th id="wsAttrSetOnly">{$apiResources->getString('service.attributeSetOnly')}</th>
        <th id="wsAttrDataType">{$apiResources->getString('service.attributeDataType')}</th>
{if $service->_create}
        <th id="wsAttrRequired">{$apiResources->getString('text.required')}</th>
{/if}
        <th id="wsAttrDesc">{$apiResources->getString('service.attributeDesc')}</th>
      </tr>
{foreach from=$entity->getWsAttributes() item=attr}
{if $Util->includeAttributeInOutput($attr, $service->_includeAttrs, $service->_excludeAttrs)}
      <tr>
        <td class="wsAttrName">{$attr}{if $entity->getPrimaryKeyAttribute() eq $attr}<sup>1</sup>{assign var=pkFound value=1}{/if}</td>
        <td class="wsAttrReadOnly">{if $entity->isAttributeReadOnly($attr)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
        <td class="wsAttrSetOnly">{if $entity->isAttributeSetOnly($attr)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
        <td class="wsAttrDataType">{if $entity->attributeIsEntity($attr)}<a href="{$Template->strReplace('[entity]', $entity->getAttributeType($attr), $entityUri)}">{/if}{if $entity->getAttributeType($attr)}{$entity->getAttributeType($attr)}{if $entity->attributeIsEntity($attr)}</a>{/if}{if $entity->getAttributeCardinality($attr)}[{$entity->getAttributeCardinality($attr)}]{/if}{else}{$smarty.const.SRA_DATA_TYPE_STRING}{/if}</td>
{if $service->_create}
        <td id="wsAttrRequired">{if $entity->isAttributeReadOnly($attr)}{$apiResources->getString('text.na')}{elseif $entity->isAttributeRequired($attr, 1)}{$resources->getString('text.yes')}{else}{$resources->getString('text.no')}{/if}</td>
{/if}
        <td class="wsAttrDesc">{if $entity->getHelpContent($attr)}{$entity->getHelpContent($attr)}{else}{$entity->getEntityLabel($attr)}{/if}{if $entity->isAttributeSetOnly($attr)}. {$apiResources->getString('service.attribute.notIncluded')}{/if}</td>
      </tr>
{if $entity->getAttributeCardinality($attr) && !$entity->isAttributeReadOnly($attr) && $service->_update}
      <tr>
        <td class="wsAttrName">{$attr}_remove</td>
        <td class="wsAttrReadOnly">{$apiResources->getString('text.na')}</td>
        <td class="wsAttrSetOnly">{$apiResources->getString('text.na')}</td>
        <td class="wsAttrDataType">{if $entity->attributeIsEntity($attr)}<a href="{$Template->strReplace('[entity]', $entity->getAttributeType($attr), $entityUri)}">{/if}{if $entity->getAttributeType($attr)}{$entity->getAttributeType($attr)}{if $entity->attributeIsEntity($attr)}</a>{/if}{else}{$smarty.const.SRA_DATA_TYPE_STRING}{/if}</td>
{if $service->_create}
        <td id="wsAttrRequired">{$resources->getString('text.no')}</td>
{/if}
        <td class="wsAttrDesc">{$apiResources->getString('service.attribute.remove')} "{$attr}" {$apiResources->getString('service.attribute.remove1')} {$apiResources->getString('service.attribute.notIncluded')}</td>
      </tr>
{/if}
{/if}
{/foreach}
    </table>
{if $pkFound}
    <sup>1</sup> {$apiResources->getString('service.primaryKey')}
{/if}
  </p>
{/if}
  <h2 id="input">{$apiResources->getString('service.input')}</h2>
  <p id="wsInput">
    <p id="inputDesc">
      {if $service->_rest && $service->_soap}{$apiResources->getString('service.inputDesc')}{elseif $service->_rest}{$apiResources->getString('service.inputDescRest')}{else}{$apiResources->getString('service.inputDescSoap')}{/if}
      <span class="wsUri">{$wsUri}</span>
      {if $service->_rest}<p>{$apiResources->getString('service.inputDesc1Rest')}</p>
{/if}
      {if $service->_soap}<p>{$apiResources->getString('service.inputDesc1Soap')} <a href="{$service->getWsdlUri()}">{$apiResources->getString('text.wsdl')}</a></p>
{/if}
      <p>{$apiResources->getString('service.inputDesc1')}</p>
      <table>
        <tr>
          {if $service->_rest}<th id="wsParam">{$apiResources->getString('service.param')}</th>
          <th id="wsParamXml">{$apiResources->getString('service.paramXml')}</th>
{/if}
          {if $service->_soap}<th id="wsParamSoap">{$apiResources->getString('service.paramWsdl')}</th>
{/if}
          <th id="wsParamDataType">{$apiResources->getString('service.dataType')}</th>
          <th id="wsParamRequired">{$apiResources->getString('text.required')}</th>
          <th id="wsParamDesc">{$apiResources->getString('service.paramDesc')}</th>
        </tr>
{if $service->_rest}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws={$service->_id}</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;key="{$service->_id}"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamRequired">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.ws')}: <span class="serviceName">{$service->_id}</span>{if $rewrite && $service->_rest}. {$apiResources->getString('service.param.ws.rewrite')} {$wsUri}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}{/if}</td>
        </tr>
{if ($service->_global && (!$api || $api.params.params)) || (!$service->_global && ($service->_create || $service->_update))}
        <tr>
          {if $service->_rest}<td class="wsParam">ws-paramN=?<br />{$apiResources->getString('service.param.params.getpost')}=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;ws-param name="?"{else}{if $service->_create}<br />&nbsp;&lt;ws-create&gt;<br />&nbsp;&nbsp;&lt;ws-param name="?"{/if}{if $service->_update}<br />&nbsp;&lt;ws-update&gt;<br />&nbsp;&nbsp;&lt;ws-param name="?"{/if}{/if}</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;param name="?"{else}{if $service->_create}<br />&nbsp;&lt;create&gt;<br />&nbsp;&nbsp;&lt;param name="?"{/if}{if $service->_update}<br />&nbsp;&lt;update&gt;<br />&nbsp;&nbsp;&lt;param name="?"{/if}{/if}</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{if $service->_global}{if $api.params.params.comment}{$api.params.params.comment}{else}{$apiResources->getString('service.param.paramsGlobal')}{/if}{else}{$apiResources->getString('service.param.params')}{/if} {$apiResources->getString('service.param.params1')}{if $service->_rest} {$apiResources->getString('service.param.params2')}{/if}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-paramN-value=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;ws-param value="?"{else}{if $service->_create}<br />&nbsp;&lt;ws-create&gt;<br />&nbsp;&nbsp;&lt;ws-param value="?"{/if}{if $service->_update}<br />&nbsp;&lt;ws-update&gt;<br />&nbsp;&nbsp;&lt;ws-param value="?"{/if}{/if}</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;param value="?"{else}{if $service->_create}<br />&nbsp;&lt;create&gt;<br />&nbsp;&nbsp;&lt;param value="?"{/if}{if $service->_update}<br />&nbsp;&lt;update&gt;<br />&nbsp;&nbsp;&lt;param value="?"{/if}{/if}</td>
{/if}
          <td class="wsParamDataType">{$apiResources->getString('text.mixed')}</td>
          <td class="wsParamOptional">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.paramsValue')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-paramN-value-type=?<br />{$apiResources->getString('service.param.paramsValueType.getpost')}=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;ws-param value-type="?"{else}{if $service->_create}<br />&nbsp;&lt;ws-create&gt;<br />&nbsp;&nbsp;&lt;ws-param value-type="?"{/if}{if $service->_update}<br />&nbsp;&lt;ws-update&gt;<br />&nbsp;&nbsp;&lt;ws-param value-type="?"{/if}{/if}</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;{if $service->_global}<br />&nbsp;&nbsp;&lt;param valueType="?"{else}{if $service->_create}<br />&nbsp;&lt;create&gt;<br />&nbsp;&nbsp;&lt;param valueType="?"{/if}{if $service->_update}<br />&nbsp;&lt;update&gt;<br />&nbsp;&nbsp;&lt;param valueType="?"{/if}{/if}</td>
{/if}
          <td class="wsParamDataType">({$smarty.const.SRA_WS_VALUE_TYPE_CONSTANT}|{$smarty.const.SRA_WS_VALUE_TYPE_GET}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_GLOBAL}|{$smarty.const.SRA_WS_VALUE_TYPE_POST}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_SESSION}|{$smarty.const.SRA_WS_VALUE_TYPE_FILE})</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.paramsValueType')}</td>
        </tr>
{/if}
{if !$skipAppId}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-app={$Controller->getCurrentAppId()}</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;app="{$Controller->getCurrentAppId()}"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamRequired">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.app')}: <span class="wsAppName">{$Controller->getCurrentAppId()}</span>{if $rewrite && $service->_rest}. {$apiResources->getString('service.param.ws-app.rewrite')}: {$wsUri}/{$Controller->getCurrentAppId()}{/if}</td>
        </tr>
{/if}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-asynchronous=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;asynchronous="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">(0|1)/0</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.asynchronous')}</td>
        </tr>
{/if}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-doc=?</td>
          <td class="wsParamXml">{$apiResources->getString('text.na')}</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">({$smarty.const.SRA_WS_GATEWAY_DOC_TYPE_API}|{$smarty.const.SRA_WS_GATEWAY_DOC_TYPE_WSDL})</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.doc')}{if $rewrite}. {$apiResources->getString('service.param.ws.rewrite')} {$wsUri}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/({$smarty.const.SRA_WS_GATEWAY_DOC_TYPE_API}|{$smarty.const.SRA_WS_GATEWAY_DOC_TYPE_WSDL}){/if}</td>
        </tr>
{if !$service->_global && $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-exclude=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;exclude-attrs="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;excludeAttrs="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.excludeAttrs')}</td>
        </tr>
{/if}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-format=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;format="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;format="?"</td>
{/if}
          <td class="wsParamDataType">{if $service->_formatFixed}{$service->_format}{else}({$smarty.const.SRA_WS_FORMAT_JSON}|{$smarty.const.SRA_WS_FORMAT_XML}{if $response->_global}|{$smarty.const.SRA_WS_FORMAT_RAW}{/if}){/if}{if $service->_format}/{$service->_format}{/if}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.format')}{if $rewrite && $service->_rest}. {$apiResources->getString('service.param.ws.rewrite')} {$wsUri}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/({$smarty.const.SRA_WS_FORMAT_JSON}|{$smarty.const.SRA_WS_FORMAT_XML}{if $response->_global}|{$smarty.const.SRA_WS_FORMAT_RAW}{/if}){/if}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-callback=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;callback="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">NA</td>
{/if}
          <td class="wsParamDataType">string</td>
          <td class="wsParamOptional">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.callback')}</td>
        </tr>
{if !$service->_global && $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-include=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;include-attrs="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;includeAttrs="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.includeAttrs')}</td>
        </tr>
{/if}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-js-dates=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;js-dates="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;jsDates="?"</td>
{/if}
          <td class="wsParamDataType">(0|1)/1</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.jsDates')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-date-format=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;date-format="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;dateFormat="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}/"{$Controller->getAppDateOnlyFormat()}"</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.dateFormat')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-time-format=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;time-format="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;timeFormat="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}/"{$Controller->getAppDateFormat()}"</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.timeFormat')}</td>
        </tr>
{if $usesLimit || $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-limit=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;limit="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;limit="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_INT}{if $service->_limit}/{$service->_limit}{/if}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{if $service->_retrieve || !$api.params.limit.comment}{$apiResources->getString('service.param.limit')}{else}{$api.params.limit.comment}{/if}{if $service->_limitFixed}{$apiResources->getString('service.param.limitFixed')} {$service->_limit}{else}{if $service->_limit} {$apiResources->getString('service.param.limit1')} {$service->_limit}{/if}{/if}</td>
        </tr>
{/if}
{if $service->_rest}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-meta-format=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;meta-format="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">{if $service->_metaFormatFixed}{$service->_metaFormat}{else}({$smarty.const.SRA_WS_META_FORMAT_JSON}|{$smarty.const.SRA_WS_META_FORMAT_XML}|{$smarty.const.SRA_WS_META_FORMAT_NONE}){if $service->_metaFormat}/{$service->_metaFormat}{/if}{/if}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.metaFormat')}</td>
        </tr>
{/if}
{if $usesOffset || $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-offset=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;offset="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;offset="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_INT}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{if $service->_retrieve || !$api.params.offset.comment}{$apiResources->getString('service.param.offset')}{else}{$api.params.offset.comment}{/if}</td>
        </tr>
{/if}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-query=?/td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;query="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">(0|1)/0</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.query')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-request-id=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;request-id="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;requestId="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.requestId')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-request-id1=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;request-id1="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;requestId1="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.requestId1')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-session-id=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;session-id="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;sessionId="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.sessionId')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-use-sessions=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;use-sessions="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;useSessions="?"</td>
{/if}
          <td class="wsParamDataType">(0|1)/0</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.useSessions')}</td>
        </tr>
{if !$service->_global && ($service->_create || $service->_update) && $entity->hasNonMandatoryValidator()}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-validators=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;validators="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;validators="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">
            {$apiResources->getString('service.param.validators')}
            <ul>
{foreach from=$entity->getEntityValidators() item=validator}{if !$entity->isValidatorMandatory($validator)}
              <li>{$validator}</li>
{/if}{/foreach}
            </ul>
          </td>
        </tr>
{/if}
{if !$service->_global && ($service->_create || $service->_delete || $service->_update)}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-action=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;({if $service->_create}&lt;ws-create&gt;{/if}|{if $service->_update}{if $service->_create}<br />&nbsp;&nbsp;{/if}&lt;ws-update&gt;{/if}{if $service->_delete}{if $service->_create || $service->_update}|<br />&nbsp;&nbsp;{/if}&lt;ws-delete&gt;{/if})</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;({if $service->_create}&lt;create&gt;{/if}{if $service->_update}{if $service->_create}|<br />&nbsp;&nbsp;{/if}&lt;update&gt;{/if}{if $service->_delete}{if $service->_create || $service->_update}|<br />&nbsp;&nbsp;{/if}&lt;delete&gt;{/if})</td>
{/if}
          <td class="wsParamDataType">({if $service->_retrieve}{$smarty.const.SRA_WS_REQUEST_RETRIEVE}{/if}{if $service->_create}{if $service->_retrieve}|<br />&nbsp;{/if}{$smarty.const.SRA_WS_REQUEST_CREATE}{/if}{if $service->_update}{if $service->_retrieve || $service->_create}|<br />&nbsp;{/if}{$smarty.const.SRA_WS_REQUEST_UPDATE}{/if}{if $service->_delete}{if $service->_retrieve || $service->_create || $service->_update}|<br />&nbsp;{/if}{$smarty.const.SRA_WS_REQUEST_DELETE}{/if})</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.action')}{if $rewrite && $service->_rest}. {$apiResources->getString('service.param.ws.rewrite')} {$wsUri}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/(create|update|delete){/if}</td>
        </tr>
{/if}
{if !$service->_global && $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-id=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;(&lt;ws-retrieve key="?"|{if $service->_update}<br />&nbsp;&nbsp;&lt;ws-update key="?"{/if}{if $service->_delete}|<br />&nbsp;&nbsp;&lt;ws-delete key="?"{/if})</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;(&lt;retrieve key="?"|{if $service->_update}<br />&nbsp;&nbsp;&lt;update key="?"{/if}{if $service->_delete}|<br />&nbsp;&nbsp;&lt;delete key="?"{/if})</td>
{/if}
          <td class="wsParamDataType">{$apiResources->getString('text.mixed')}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.retrieve')} <span class="wsPrimaryKeyName">{$entity->getPrimaryKeyAttribute()}</span> {$apiResources->getString('service.param.retrieve1')}</td>
        </tr>
{/if}
{if $service->_rest}
        <tr>
          <td class="wsParam">ws-request-xml=?</td>
          <td class="wsParamXml">{$apiResources->getString('text.na')}</td>
          {if $service->_soap}<td class="wsParamSoap">{$apiResources->getString('text.na')}</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.xml')}</td>
        </tr>
{/if}
{if $service->_retrieve && $Controller->getAppWorkflows()}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-wf-id=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-retrieve workflow-id="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;retrieve workflow-id="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_INT}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.workflowId')}</td>
        </tr>
{/if}
{if $service->_retrieve}
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-*</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;</td>
{/if}
          <td class="wsParamDataType">{$apiResources->getString('text.na')}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraintGroup')}{if $service->_rest}{$apiResources->getString('service.param.constraintGroupRest')}{/if}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-connective=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group<br />&nbsp;&nbsp;connective="?"&gt;</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup<br />&nbsp;&nbsp;connective="?"&gt;</td>
{/if}
          <td class="wsParamDataType">({$smarty.const.SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE}|{$smarty.const.SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_DISJUNCTIVE})/{$smarty.const.SRA_WS_CONSTRAINT_GROUP_CONNECTIVE_CONJUNCTIVE}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.connective')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-attrN=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;<br />&nbsp;&nbsp;&lt;ws-constraint attr="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;<br />&nbsp;&nbsp;&lt;constraint attr="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.yes')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraint.attr')}{if $service->_rest}{$apiResources->getString('service.param.constraint.attrRest')}{/if}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-valueN=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;<br />&nbsp;&nbsp;&lt;ws-constraint value="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;<br />&nbsp;&nbsp;&lt;constraint value="?"</td>
{/if}
          <td class="wsParamDataType">{$apiResources->getString('text.mixed')}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraint.value')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-operatorN=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;<br />&nbsp;&nbsp;&lt;ws-constraint operator="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;<br />&nbsp;&nbsp;&lt;constraint operator="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_INT}/1</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraint.operator')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-typeN=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;<br />&nbsp;&nbsp;&lt;ws-constraint attr-type="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;<br />&nbsp;&nbsp;&lt;constraint attrType="?"</td>
{/if}
          <td class="wsParamDataType">({$smarty.const.SRA_WS_VALUE_TYPE_ATTR}|{$smarty.const.SRA_WS_VALUE_TYPE_CONSTANT}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_GET}|{$smarty.const.SRA_WS_VALUE_TYPE_GET_ATTR}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_GLOBAL}|{$smarty.const.SRA_WS_VALUE_TYPE_POST}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_SESSION})/{$smarty.const.SRA_WS_VALUE_TYPE_ATTR}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraint.attrType')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">ws-constraintM-value-typeN=?</td>
          <td class="wsParamXml">&lt;ws-request...&gt;<br />&nbsp;&lt;ws-constraint-group&gt;<br />&nbsp;&nbsp;&lt;ws-constraint value-type="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request...&gt;<br />&nbsp;&lt;constraintGroup&gt;<br />&nbsp;&nbsp;&lt;constraint valueType="?"</td>
{/if}
          <td class="wsParamDataType">({$smarty.const.SRA_WS_VALUE_TYPE_CONSTANT}|{$smarty.const.SRA_WS_VALUE_TYPE_GET}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_GLOBAL}|{$smarty.const.SRA_WS_VALUE_TYPE_POST}|<br />&nbsp;{$smarty.const.SRA_WS_VALUE_TYPE_SESSION})</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.constraint.valueType')}</td>
        </tr>
{/if}
{if $service->_authenticate && $authenticated}
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-user=?</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;user="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;user="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.user')}</td>
        </tr>
        <tr>
          {if $service->_rest}<td class="wsParam">&amp;ws-password=[{$smarty.const.SRA_DATA_TYPE_STRING}]</td>
          <td class="wsParamXml">&lt;ws-request<br />&nbsp;password="?"</td>
{/if}
          {if $service->_soap}<td class="wsParamSoap">&lt;request<br />&nbsp;password="?"</td>
{/if}
          <td class="wsParamDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
          <td class="wsParamOptional">{$resources->getString('text.no')}</td>
          <td class="wsParamDesc">{$apiResources->getString('service.param.password')}</td>
        </tr>
{/if}
      </table>
    </p>
{if $service->_global}
    <p id="wsInputGlobal">
      {$apiResources->getString('service.input.examples')}
      <p>
{if $service->_rest}
        <input type="button" onclick="document.getElementById('wsInputGlobalVars').style.display='block'; document.getElementById('wsInputGlobalXml').style.display='none'; document.getElementById('wsInputGlobalSoap').style.display='none';" value="{$apiResources->getString('text.getpost')}" />
        <input type="button" onclick="document.getElementById('wsInputGlobalVars').style.display='none'; document.getElementById('wsInputGlobalXml').style.display='block'; document.getElementById('wsInputGlobalSoap').style.display='none';" value="{$apiResources->getString('text.xml')}" />
{/if}
{if $service->_soap}
        <input type="button" onclick="document.getElementById('wsInputGlobalVars').style.display='none'; document.getElementById('wsInputGlobalXml').style.display='none'; document.getElementById('wsInputGlobalSoap').style.display='block';" value="{$apiResources->getString('text.soap')}" />
{/if}
      </p>
{if $service->_rest}
      <p class="wsExample" id="wsInputGlobalVars">
        {$apiResources->getString('service.input.inputGlobal1')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}?{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;{/if}ws-param1=param1&amp;ws-param1-value=val1
      </p>
      <p class="wsExample" id="wsInputGlobalXml" style="display: none">
        {$apiResources->getString('service.input.inputGlobal1')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-param name="param1" value="val1" /&gt;<br />
        &lt;/ws-request&gt;
      </p>
{/if}
{if $service->_soap}
      <p class="wsExample" id="wsInputGlobalSoap" style="display: none">
        {$apiResources->getString('service.input.inputGlobal1')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;param name="param1" value="val1" /&gt;<br />
        &lt;/request&gt;
      </p>
{/if}
    </p>
{else}
{if $service->_retrieve}
    <h3 id="retrieve">{$apiResources->getString('service.input.retrieve')}</h3>
    <p id="wsInputRetrieve">
      {$apiResources->getString('service.input.examples')}
      <p>
{if $service->_rest}
        <input type="button" onclick="document.getElementById('wsInputRetrieveVars').style.display='block'; document.getElementById('wsInputRetrieveXml').style.display='none'; document.getElementById('wsInputRetrieveSoap').style.display='none';" value="{$apiResources->getString('text.getpost')}" />
        <input type="button" onclick="document.getElementById('wsInputRetrieveVars').style.display='none'; document.getElementById('wsInputRetrieveXml').style.display='block'; document.getElementById('wsInputRetrieveSoap').style.display='none';" value="{$apiResources->getString('text.xml')}" />
{/if}
{if $service->_soap}
        <input type="button" onclick="document.getElementById('wsInputRetrieveVars').style.display='none'; document.getElementById('wsInputRetrieveXml').style.display='none'; document.getElementById('wsInputRetrieveSoap').style.display='block';" value="{$apiResources->getString('text.soap')}" />
{/if}
      </p>
{if $service->_rest}
      <p class="wsExample" id="wsInputRetrieveVars">
        {$apiResources->getString('service.input.inputRetrieve1')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_RETRIEVE}{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_RETRIEVE}{/if}<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve2')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_RETRIEVE}/123{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_RETRIEVE}&amp;ws-id=123{/if}<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve3')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_RETRIEVE}?{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_RETRIEVE}&amp;{/if}ws-constraint1-attr1=attr1&amp;ws-constraint1-value1=val1
      </p>
      <p class="wsExample" id="wsInputRetrieveXml" style="display: none">
        {$apiResources->getString('service.input.inputRetrieve1')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if} /&gt;<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve2')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-retrieve key="123" /&gt;<br />
        &lt;/ws-request&gt;<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve3')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-constraint-group&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;ws-constraint attr="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&lt;/ws-constraint-group&gt;<br />
        &lt;/ws-request&gt;
      </p>
{/if}
{if $service->_soap}
      <p class="wsExample" id="wsInputRetrieveSoap" style="display: none">
        {$apiResources->getString('service.input.inputRetrieve1')}:<br />
        &lt;request /&gt;<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve2')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;retrieve key="123" /&gt;<br />
        &lt;/request&gt;<br /><br />
        
        {$apiResources->getString('service.input.inputRetrieve3')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;constraintGroup&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;constraint attr="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&lt;/constraintGroup&gt;<br />
        &lt;/request&gt;
      </p>
{/if}
    </p>
{/if}
{if $service->_create}
    <h3 id="create">{$apiResources->getString('service.input.create')}</h3>
    <p id="wsInputCreate">
      {$apiResources->getString('service.input.examples')}
      <p>
{if $service->_rest}
        <input type="button" onclick="document.getElementById('wsInputCreateVars').style.display='block'; document.getElementById('wsInputCreateXml').style.display='none'; document.getElementById('wsInputCreateSoap').style.display='none';" value="{$apiResources->getString('text.getpost')}" />
        <input type="button" onclick="document.getElementById('wsInputCreateVars').style.display='none'; document.getElementById('wsInputCreateXml').style.display='block'; document.getElementById('wsInputCreateSoap').style.display='none';" value="{$apiResources->getString('text.xml')}" />
{/if}
{if $service->_soap}
        <input type="button" onclick="document.getElementById('wsInputCreateVars').style.display='none'; document.getElementById('wsInputCreateXml').style.display='none'; document.getElementById('wsInputCreateSoap').style.display='block';" value="{$apiResources->getString('text.soap')}" />
{/if}
      </p>
{if $service->_rest}
      <p class="wsExample" id="wsInputCreateVars">
        {$apiResources->getString('service.input.inputCreate1')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_CREATE}?{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_CREATE}&amp;{/if}ws-param1=attr1&amp;ws-param1-value=val1&amp;ws-param2=attr2&amp;ws-param2-value=val2
      </p>
      <p class="wsExample" id="wsInputCreateXml" style="display: none">
        {$apiResources->getString('service.input.inputCreate1')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-create&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;ws-param name="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;ws-param name="attr2" value="val2" /&gt;<br />
        &nbsp;&nbsp;&lt;/ws-create&gt;<br />
        &lt;/ws-request&gt;
      </p>
{/if}
{if $service->_soap}
      <p class="wsExample" id="wsInputCreateSoap" style="display: none">
        {$apiResources->getString('service.input.inputCreate1')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;create&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;param name="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;param name="attr2" value="val2" /&gt;<br />
        &nbsp;&nbsp;&lt;/create&gt;<br />
        &lt;/request&gt;
      </p>
{/if}
    </p>
{/if}
{if $service->_update}
    <h3 id="update">{$apiResources->getString('service.input.update')}</h3>
    <p id="wsInputUpdate">
      {$apiResources->getString('service.input.examples')}
      <p>
{if $service->_rest}
        <input type="button" onclick="document.getElementById('wsInputUpdateVars').style.display='block'; document.getElementById('wsInputUpdateXml').style.display='none'; document.getElementById('wsInputUpdateSoap').style.display='none';" value="{$apiResources->getString('text.getpost')}" />
        <input type="button" onclick="document.getElementById('wsInputUpdateVars').style.display='none'; document.getElementById('wsInputUpdateXml').style.display='block'; document.getElementById('wsInputUpdateSoap').style.display='none';" value="{$apiResources->getString('text.xml')}" />
{/if}
{if $service->_soap}
        <input type="button" onclick="document.getElementById('wsInputUpdateVars').style.display='none'; document.getElementById('wsInputUpdateXml').style.display='none'; document.getElementById('wsInputUpdateSoap').style.display='block';" value="{$apiResources->getString('text.soap')}" />
{/if}
      </p>
{if $service->_rest}
      <p class="wsExample" id="wsInputUpdateVars">
        {$apiResources->getString('service.input.inputUpdate1')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_UPDATE}/123?{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_UPDATE}&amp;ws-id=123&amp;{/if}ws-param1=attr1&amp;ws-param1-value=val1&amp;ws-param2=attr2&amp;ws-param2-value=val2
      </p>
      <p class="wsExample" id="wsInputUpdateXml" style="display: none">
        {$apiResources->getString('service.input.inputUpdate1')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-update key="123"&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;ws-param name="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;ws-param name="attr2" value="val2" /&gt;<br />
        &nbsp;&nbsp;&lt;/ws-update&gt;<br />
        &lt;/ws-request&gt;
      </p>
{/if}
{if $service->_soap}
      <p class="wsExample" id="wsInputUpdateSoap" style="display: none">
        {$apiResources->getString('service.input.inputUpdate1')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;update key="123"&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;param name="attr1" value="val1" /&gt;<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;param name="attr2" value="val2" /&gt;<br />
        &nbsp;&nbsp;&lt;/update&gt;<br />
        &lt;/request&gt;
      </p>
{/if}
    </p>
{/if}
{if $service->_delete}
    <h3 id="delete">{$apiResources->getString('service.input.delete')}</h3>
    <p id="wsInputDelete">
      {$apiResources->getString('service.input.examples')}
      <p>
{if $service->_rest}
        <input type="button" onclick="document.getElementById('wsInputDeleteVars').style.display='block'; document.getElementById('wsInputDeleteXml').style.display='none'; document.getElementById('wsInputDeleteSoap').style.display='none';" value="{$apiResources->getString('text.getpost')}" />
        <input type="button" onclick="document.getElementById('wsInputDeleteVars').style.display='none'; document.getElementById('wsInputDeleteXml').style.display='block'; document.getElementById('wsInputDeleteSoap').style.display='none';" value="{$apiResources->getString('text.xml')}" />
{/if}
{if $service->_soap}
        <input type="button" onclick="document.getElementById('wsInputDeleteVars').style.display='none'; document.getElementById('wsInputDeleteXml').style.display='none'; document.getElementById('wsInputDeleteSoap').style.display='block';" value="{$apiResources->getString('text.soap')}" />
{/if}
      </p>
{if $service->_rest}
      <p class="wsExample" id="wsInputDeleteVars">
        {$apiResources->getString('service.input.inputDelete1')}:<br />
        {$wsUri}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/{$service->_id}/{$smarty.const.SRA_WS_REQUEST_DELETE}/123{else}?ws={$service->_id}{if !$skipAppId}&amp;ws-app={$Controller->getCurrentAppId()}{/if}&amp;ws-action={$smarty.const.SRA_WS_REQUEST_DELETE}&amp;ws-id=123{/if}
      </p>
      <p class="wsExample" id="wsInputDeleteXml" style="display: none">
        {$apiResources->getString('service.input.inputDelete1')}:<br />
        {$wsUri}?ws-request-xml=<br />
        &lt;ws-request key="{$service->_id}"{if !$skipAppId} app="{$Controller->getCurrentAppId()}"{/if}&gt;<br />
        &nbsp;&nbsp;&lt;ws-delete key="123" /&gt;<br />
        &lt;/ws-request&gt;
      </p>
{/if}
{if $service->_soap}
      <p class="wsExample" id="wsInputDeleteSoap" style="display: none">
        {$apiResources->getString('service.input.inputDelete1')}:<br />
        &lt;request&gt;<br />
        &nbsp;&nbsp;&lt;delete key="123" /&gt;<br />
        &lt;/request&gt;
      </p>
{/if}
    </p>
{/if}
{/if}
  </p>
  
  <h2 id="output">{$apiResources->getString('service.output')}</h2>
  <p id="wsOutput">
    <p id="inputDesc">
    {$apiResources->getString('service.outputDesc')}
    <table>
      <tr>
        <th id="wsOutputJson">{$apiResources->getString('service.output.json')}</th>
        <th id="wsOutputXml">{$apiResources->getString('service.output.xml')}</th>
        <th id="wsOutputSoap">{$apiResources->getString('service.output.soap')}</th>
        <th id="wsOutputDataType">{$apiResources->getString('service.output.dataType')}</th>
        <th id="wsOutputMetadata">{$apiResources->getString('service.output.metadata')}</th>
        <th id="wsOutputDesc">{$apiResources->getString('service.output.desc')}</th>
      </tr>
{if !$service->_global}
      <tr>
        <td class="wsOutputJson">{ldelim} action: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response action="?"</td>
        <td class="wsOutputSoap">&lt;response action="?"</td>
        <td class="wsOutputDataType">({if $service->_retrieve}retrieve{/if}{if $service->_create}{if $service->_retrieve}|<br />&nbsp;{/if}create{/if}{if $service->_update}{if $service->_retrieve || $service->_create}|<br />&nbsp;{/if}update{/if}{if $service->_delete}{if $service->_retrieve || $service->_create || $service->_update}|<br />&nbsp;{/if}delete{/if})</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.action')}</td>
      </tr>
{/if}
{if $usesLimit || $service->_retrieve}
      <tr>
        <td class="wsOutputJson">{ldelim} count: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response count="?"</td>
        <td class="wsOutputSoap">&lt;response count="?"</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_INT}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{if $service->_retrieve}{$apiResources->getString('service.output.count')}{elseif $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_METHOD}{$apiResources->getString('service.output.countMethod')}{elseif $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_RB}{$apiResources->getString('service.output.countRb')}{else}{$apiResources->getString('service.output.countQuery')}{/if}</td>
      </tr>
{/if}
      <tr>
        <td class="wsOutputJson">{ldelim} format: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response format="?"</td>
        <td class="wsOutputSoap">&lt;response format="?"</td>
        <td class="wsOutputDataType">({if $service->_formatFixed}{$service->_format}{else}{$smarty.const.SRA_WS_FORMAT_JSON}|{$smarty.const.SRA_WS_FORMAT_XML}{if $response->_global}|{$smarty.const.SRA_WS_FORMAT_RAW}{/if}{/if})</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{if $response->_global}{$apiResources->getString('service.output.formatGlobal')}{else}{$apiResources->getString('service.output.format')}{/if}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} global: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response global="?"</td>
        <td class="wsOutputSoap">&lt;response global="?"</td>
        <td class="wsOutputDataType">{if $service->_global}1{else}0{/if}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{if $response->_global}{$apiResources->getString('service.output.global')}{else}{$apiResources->getString('service.output.nonglobal')}{/if}</td>
      </tr>
{if $usesLimit || $service->_retrieve}
      <tr>
        <td class="wsOutputJson">{ldelim} limit: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response limit="?"</td>
        <td class="wsOutputSoap">&lt;response limit="?"</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_INT}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.limit')}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} offset: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response offset="?"</td>
        <td class="wsOutputSoap">&lt;response offset="?"</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_INT}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.offset')}</td>
      </tr>
{/if}
      <tr>
        <td class="wsOutputJson">{ldelim} requestId: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response request-id="?"</td>
        <td class="wsOutputSoap">&lt;response requestId="?"</td>
        <td class="wsOutputDataType">{$apiResources->getString('text.mixed')}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.requestId')}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} requestId1: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response request-id1="?"</td>
        <td class="wsOutputSoap">&lt;response requestId1="?"</td>
        <td class="wsOutputDataType">{$apiResources->getString('text.mixed')}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.requestId1')}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} service: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response service="?"</td>
        <td class="wsOutputSoap">&lt;response service="?"</td>
        <td class="wsOutputDataType">({$service->_id})</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.service')}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} sessionId: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response session-id="?"</td>
        <td class="wsOutputSoap">&lt;response sessionId="?"</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.sessionId')}</td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} status: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response status="?"</td>
        <td class="wsOutputSoap">&lt;response status="?"</td>
        <td class="wsOutputDataType">
          ({$smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}|<br />
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_FAILED}|<br />
{if !$skipAppId}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_APP}|<br />
{/if}
{if !$service->_global && ($service->_create || $service->_update)}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_ATTRS}|<br />
{/if}
{if !$service->_global && ($service->_delete || $service->_update)}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PK}|<br />
{/if}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_FORMAT}|<br />
{if !$service->_global && ($service->_create || $service->_update)}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT}|<br />
{/if}
{if $usesLimit || $service->_retrieve}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_LIMIT}|<br />
{/if}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_META_FORMAT}|<br />
{if !$service->_rest || !$service->_soap}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PROTO}|<br />
{/if}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_REQUEST}|<br />
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_SERVICE}|<br />
{if $service->_ipAuthenticator}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_IP_NOT_ALLOWED}|<br />
{/if}
{if !$service->_global && (!$service->_create || !$service->_delete || !$service->_retrieve || !$service->_update)}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_NOT_ALLOWED}|<br />
{/if}
{if $service->_authenticate && $authenticated}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_AUTH_FAILED}|<br />
{/if}
{if $service->_rest}
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_RESULTS_NOT_AVAILABLE}|<br />
          &nbsp;{$smarty.const.SRA_WS_REQUEST_STATUS_TIMEOUT}
{/if})
        </td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">
          {$apiResources->getString('service.output.status')}
          <ul>
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}: {$apiResources->getString('service.output.status.success')}</li>
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_FAILED}: {$apiResources->getString('service.output.status.failed')}</li>
{if !$skipAppId}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_APP}: {$apiResources->getString('service.output.status.invalidApp')}</li>
{/if}
{if !$service->_global && ($service->_create || $service->_update)}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_ATTRS}: {$apiResources->getString('service.output.status.invalidAttrs')}</li>
{/if}
{if !$service->_global && ($service->_delete || $service->_update)}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PK}: {$apiResources->getString('service.output.status.invalidPk')}</li>
{/if}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_FORMAT}: {$apiResources->getString('service.output.status.invalidFormat')}</li>
{if !$service->_global && ($service->_create || $service->_update)}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT}: {$apiResources->getString('service.output.status.invalidInput')}</li>
{/if}
{if $usesLimit || $service->_retrieve}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_LIMIT}: {$apiResources->getString('service.output.status.invalidLimit')}</li>
{/if}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_META_FORMAT}: {$apiResources->getString('service.output.status.invalidMetaFormat')}</li>
{if !$service->_rest || !$service->_soap}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PROTO}: {if !$service->_rest}{$apiResources->getString('service.output.status.invalidProtoRest')}{else}{$apiResources->getString('service.output.status.invalidProtoSoap')}{/if}</li>
{/if}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_REQUEST}: {$apiResources->getString('service.output.status.invalidRequest')}</li>
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_SERVICE}: {$apiResources->getString('service.output.status.invalidService')}</li>
{if $service->_ipAuthenticator}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_IP_NOT_ALLOWED}: {$apiResources->getString('service.output.status.ipNotAllowed')}</li>
{/if}
{if !$service->_global && (!$service->_create || !$service->_delete || !$service->_retrieve || !$service->_update)}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_NOT_ALLOWED}: {$apiResources->getString('service.output.status.notAllowed')}</li>
{/if}
{if $service->_authenticate && $authenticated}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_AUTH_FAILED}: {$apiResources->getString('service.output.status.authFailed')}</li>
{/if}
{if $service->_rest}
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_RESULTS_NOT_AVAILABLE}: {$apiResources->getString('service.output.status.notAvailable')}</li>
          <li>{$smarty.const.SRA_WS_REQUEST_STATUS_TIMEOUT}: {$apiResources->getString('service.output.status.timeout')}</li>
{/if}
          </ul>
        </td>
      </tr>
      <tr>
        <td class="wsOutputJson">{ldelim} time: "?" ... </td>
        <td class="wsOutputXml">&lt;ws-response time="?"</td>
        <td class="wsOutputSoap">&lt;response time="?"</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_FLOAT}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.yes')}</td>
        <td class="wsOutputDesc">{$apiResources->getString('service.output.time')}</td>
      </tr>
{if ($service->_global && $api.return && $api.return neq 'void') || (!$service->_global && ($service->_create || $service->_retrieve || $service->_update))}
      <tr>
        <td class="wsOutputJson">{ldelim} response: {if $service->_global}?{else}{if $service->_retrieve}[ {/if}{$entity->getEntityType()}{if $service->_retrieve}, ]{/if}{/if} </td>
        <td class="wsOutputXml">&lt;ws-response ...&gt;<br />&nbsp;{if $service->_global}?{else}{$entity->getEntityType()}{if $service->_retrieve}[0..*]{/if}{/if}<br />&lt;/ws-response&gt;</td>
        <td class="wsOutputSoap">&lt;response ...&gt;<br />&nbsp;{if $service->_global}?{else}{$entity->getEntityType()}{if $service->_retrieve}[0..*]{/if}{/if}<br />&lt;/response&gt;</td>
        <td class="wsOutputDataType">{if $service->_global}{$api.return}{else}{$entity->getEntityType()}{if $service->_retrieve}[0..*]{/if}{/if}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.no')}</td>
        <td class="wsOutputDesc">{if $service->_global}{$apiResources->getString('service.output.responseGlobal')}{else}{$apiResources->getString('service.output.response')}<ul>{if $service->_retrieve}<li>{$apiResources->getString('service.output.responseRetrieve')}</li>{/if}{if $service->_create}<li>{$apiResources->getString('service.output.responseCreate')}</li>{/if}{if $service->_update}<li>{$apiResources->getString('service.output.responseUpdate')}</li>{/if}{if $service->_delete}<li>{$apiResources->getString('service.output.responseDelete')}</li>{/if}</ul>{/if}</td>
      </tr>
{/if}
{if $service->_global && $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_SQL}
      <tr>
        <td class="wsOutputJson">{ldelim} queryResults: <br />&nbsp;[{ldelim}[col1]: "[val1]", [col2]: "[val2]"{rdelim}, <br />&nbsp;{ldelim}[col1]: "[val1]", [col2]: "[val2]"{rdelim}]<br />&nbsp;...</td>
        <td class="wsOutputXml">&lt;ws-response ...&gt;<br />&nbsp;&lt;ws-query-results&gt;<br />&nbsp;&nbsp;&lt;ws-row key="[row num]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;ws-col key="[col1]"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;![CDATA[val1]]&gt;<br />&nbsp;&nbsp;&nbsp;&lt;/ws-col&gt;<br />&nbsp;&nbsp;&nbsp;&lt;ws-col key="[col2]"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;![CDATA[val2]]&gt;<br />&nbsp;&nbsp;&nbsp;&lt;/ws-col&gt;<br />&nbsp;&nbsp;&lt;/ws-row&gt;<br />&nbsp;&lt;/ws-query-results&gt;<br />&lt;/ws-response&gt;</td>
        <td class="wsOutputSoap">&lt;response ...&gt;<br />&nbsp;&lt;queryResults&gt;<br />&nbsp;&nbsp;&lt;row key="[row num]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;col key="[col1]"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;![CDATA[val1]]&gt;<br />&nbsp;&nbsp;&nbsp;&lt;/col&gt;<br />&nbsp;&nbsp;&nbsp;&lt;col key="[col2]"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;![CDATA[val2]]&gt;<br />&nbsp;&nbsp;&nbsp;&lt;/col&gt;<br />&nbsp;&nbsp;&lt;/row&gt;<br />&nbsp;&lt;/queryResults&gt;<br />&lt;/response&gt;</td>
        <td class="wsOutputDataType">{$apiResources->getString('text.na')}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.no')}</td>
        <td class="wsOutputDesc">{if $service->_rest}<p>{$apiResources->getString('service.output.queryResultsJson')}</p>{/if}<p>{$apiResources->getString('service.output.queryResultsXml')}</p></td>
      </tr>
{/if}
{if $service->_global && $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_RB}
      <tr>
        <td class="wsOutputJson">{ldelim} resourceBundle: <br />&nbsp;{ldelim}[key1]: "[val1]",<br />&nbsp;&nbsp;[key2]: "[val2]"{rdelim}<br />&nbsp;...</td>
        <td class="wsOutputXml">&lt;ws-response ...&gt;<br />&nbsp;&lt;ws-resource-bundle&gt;<br />&nbsp;&nbsp;&lt;ws-string key="[key1]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[val1]]&gt;<br />&nbsp;&nbsp;&lt;/ws-string&gt;<br />&nbsp;&nbsp;&lt;ws-string key="[key2]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[val2]]&gt;<br />&nbsp;&nbsp;&lt;/ws-string&gt;<br />&nbsp;&lt;/ws-resource-bundle&gt;<br />&lt;/ws-response&gt;</td>
        <td class="wsOutputSoap">&lt;response ...&gt;<br />&nbsp;&lt;resourceBundle&gt;<br />&nbsp;&nbsp;&lt;string key="[key1]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[val1]]&gt;<br />&nbsp;&nbsp;&lt;/string&gt;<br />&nbsp;&nbsp;&lt;string key="[key2]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[val2]]&gt;<br />&nbsp;&nbsp;&lt;/string&gt;<br />&nbsp;&lt;/resourceBundle&gt;<br />&lt;/response&gt;</td>
        <td class="wsOutputDataType">{$apiResources->getString('text.na')}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.no')}</td>
        <td class="wsOutputDesc">{if $service->_rest}<p>{$apiResources->getString('service.output.rbJson')}</p>{/if}<p>{$apiResources->getString('service.output.rbXml')}</p></td>
      </tr>
{/if}
{if !$service->_global && ($service->_create || $service->_update)}
      <tr>
        <td class="wsOutputJson">{ldelim} validationErrors: <br />&nbsp;{ldelim}[attr1]: "[err1]",<br />&nbsp;&nbsp;[attr2]: "[err2]"{rdelim}<br />&nbsp;...</td>
        <td class="wsOutputXml">&lt;ws-response ...&gt;<br />&nbsp;&lt;ws-validation-errors&gt;<br />&nbsp;&nbsp;&lt;ws-error key="[attr1]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[err1]]&gt;<br />&nbsp;&nbsp;&lt;/ws-error&gt;<br />&nbsp;&nbsp;&lt;ws-error key="[attr2]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[err2]]&gt;<br />&nbsp;&nbsp;&lt;/ws-error&gt;<br />&nbsp;&lt;/ws-validation-errors&gt;<br />&lt;/ws-response&gt;</td>
        <td class="wsOutputSoap">&lt;response ...&gt;<br />&nbsp;&lt;validationErrors&gt;<br />&nbsp;&nbsp;&lt;error key="[attr1]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[err1]]&gt;<br />&nbsp;&nbsp;&lt;/error&gt;<br />&nbsp;&nbsp;&lt;error key="[attr2]"&gt;<br />&nbsp;&nbsp;&nbsp;&lt;![CDATA[err2]]&gt;<br />&nbsp;&nbsp;&lt;/error&gt;<br />&nbsp;&lt;/validationErrors&gt;<br />&lt;/response&gt;</td>
        <td class="wsOutputDataType">{$apiResources->getString('text.na')}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.no')}</td>
        <td class="wsOutputDesc"><p>{$apiResources->getString('service.output.validationErrors')}</p>{if $service->_rest}<p>{$apiResources->getString('service.output.validationErrorsJson')}</p>{/if}<p>{$apiResources->getString('service.output.validationErrorsXml')}</p></td>
      </tr>
{elseif $service->_global}
      <tr>
        <td class="wsOutputJson">{ldelim} error: "[error]"<br />&nbsp;...</td>
        <td class="wsOutputXml">&lt;ws-response ...&gt;<br />&nbsp;&lt;ws-error&gt;<br />&nbsp;&nbsp;&lt;![CDATA[error]]&gt;<br />&nbsp;&lt;/ws-error&gt;<br />&lt;/ws-response&gt;</td>
        <td class="wsOutputSoap">&lt;response ...&gt;<br />&nbsp;&lt;error&gt;<br />&nbsp;&nbsp;&lt;![CDATA[error]]&gt;<br />&nbsp;&lt;/error&gt;<br />&lt;/response&gt;</td>
        <td class="wsOutputDataType">{$smarty.const.SRA_DATA_TYPE_STRING}</td>
        <td class="wsOutputMetadata">{$resources->getString('text.no')}</td>
        <td class="wsOutputDesc"><p>{$apiResources->getString('service.output.error')}</p></td>
      </tr>
{/if}
    </table>
    </p>
  </p>
{else}
  {$apiResources->getString('service.notPublic')}
{/if}
  <p id="wsOverview"><a href="{$apiUri}">{$apiResources->getString('service.overviewLink')}</a></p>
</form>
</body>
</html>