<{if !$request->_isSoap}ws-{/if}response format="{$request->_format}" 
{if !$request->_global && $request->_action}             action="{$request->_action}" 
{/if}
{if $Template->strlen($request->_resultCount)}             count="{$request->_resultCount}" 
{/if}
             global="{if $request->_global}1{else}0{/if}" 
{if $Template->strlen($request->_limit)}             limit="{$request->_limit}" 
{/if}
{if $Template->strlen($request->_offset)}             offset="{$request->_offset}" 
{/if}
{if $Template->strlen($request->_requestId)}             request{if $request->_isSoap}Id{else}-id{/if}="{$Template->escapeHtmlChars($request->_requestId)}" 
{/if}
{if $Template->strlen($request->_requestId1)}             request{if $request->_isSoap}Id{else}-id{/if}1="{$Template->escapeHtmlChars($request->_requestId1)}" 
{/if}
             service="{if $service}{$service->_id}{else}{$request->_id}{/if}" 
{if $Template->strlen($request->_sessionId)}             session{if $request->_isSoap}Id{else}-id{/if}="{$Template->escapeHtmlChars($request->_sessionId)}" 
{/if}
             status="{$request->_status}" 
             time="{$Controller->getRunTime()}">{if $response || $response eq '0'}{if $service->_type neq $smarty.const.SRA_WS_GLOBAL_TYPE_RB && $service->_type neq $smarty.const.SRA_WS_GLOBAL_TYPE_SQL}{if $request->_format eq $smarty.const.SRA_WS_FORMAT_JSON}<{if !$request->_isSoap}ws-{/if}json><![CDATA[{$Util->toJson($response, $request->_includeAttrs, $request->_excludeAttrs, $request->_jsDates)}]]></{if !$request->_isSoap}ws-{/if}json>{elseif $request->_format eq $smarty.const.SRA_WS_FORMAT_XML}{if $request->_isSoap || $request->_global}{assign var=camelCase value=1}{else}{assign var=camelCase value=0}{/if}{$Util->attrToXml($response, $request->_includeAttrs, $request->_excludeAttrs, 0, 0, $camelCase)}{else}<{if !$request->_isSoap}ws-{/if}raw><![CDATA[{$response}]]></{if !$request->_isSoap}ws-{/if}raw>{/if}{/if}{/if}
{if $request->_isSelectQuery && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}
  <{if $request->_isSoap}queryResults{else}ws-query-results{/if}>
{foreach from=$request->_results key=rowNum item=row}
    <{if !$request->_isSoap}ws-{/if}row key="{$rowNum+1}">
{foreach from=$row key=colName item=col}
      <{if !$request->_isSoap}ws-{/if}col key="{$colName}"><![CDATA[{$col}]]></{if !$request->_isSoap}ws-{/if}col>
{/foreach}
    </{if !$request->_isSoap}ws-{/if}row>
{/foreach}
  </{if $request->_isSoap}queryResults{else}ws-query-results{/if}>
{/if}
{if $request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS && $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_RB}
  <{if $request->_isSoap}resourceBundle{else}ws-resource-bundle{/if}>
{foreach from=$request->_results key=key item=str}
    <{if !$request->_isSoap}ws-{/if}string key="{$key}"><![CDATA[{$str}]]></{if !$request->_isSoap}ws-{/if}string>
{/foreach}
  </{if $request->_isSoap}resourceBundle{else}ws-resource-bundle{/if}>
{/if}
{if !$request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT && $entity && $entity->validateErrors}
  <{if $request->_isSoap}validationErrors{else}ws-validation-errors{/if}>
{foreach from=$entity->validateErrors key=key item=msg}
    <{if !$request->_isSoap}ws-{/if}error key="{$key}"><![CDATA[{$msg}]]></{if !$request->_isSoap}ws-{/if}error>
{/foreach}
  </{if $request->_isSoap}validationErrors{else}ws-validation-errors{/if}>
{elseif $request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_FAILED && $request->_methodErrorMsg}
  <{if !$request->_isSoap}ws-{/if}error><![CDATA[{$request->_methodErrorMsg}]]></{if !$request->_isSoap}ws-{/if}error>
{/if}
</{if !$request->_isSoap}ws-{/if}response>
