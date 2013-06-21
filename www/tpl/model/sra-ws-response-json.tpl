{if $request->_callback}{$request->_callback}{/if}{if $isJquery || $request->_callback}({/if}{ldelim} 
"format": "{$request->_format}", 
{if !$request->_global && $request->_action}"action": "{$request->_action}", 
{/if}
{if $Template->strlen($request->_resultCount)}"count": {$request->_resultCount}, 
{/if}
"global": {if $request->_global}1{else}0{/if}, 
{if $Template->strlen($request->_limit)}"limit": {$request->_limit}, 
{/if}
{if $Template->strlen($request->_offset)}"offset": {$request->_offset}, 
{/if}
{if $Template->strlen($request->_requestId)}"requestId": "{$request->_requestId}", 
{/if}
{if $Template->strlen($request->_requestId1)}"requestId1": "{$request->_requestId1}", 
{/if}
"service": "{if $service}{$service->_id}{else}{$request->_id}{/if}", 
{if $Template->strlen($request->_sessionId)}"sessionId": "{$request->_sessionId}", 
{/if}
"status": "{$request->_status}", 
"time": {$Controller->getRunTime()}
{if $request->_isSelectQuery && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}
, "queryResults": [
{foreach from=$request->_results key=rowNum item=row}
{ldelim}
{assign var=started value=0}
{foreach from=$row key=colName item=col}
{if $started}, {/if}
"{$colName}": {$Util->toJson($col)}
{assign var=started value=1}
{/foreach}
{rdelim}
{/foreach}
]
{/if}
{if $request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS && $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_RB}
, "resourceBundle": {ldelim}
{assign var=started value=0}
{foreach from=$request->_results key=key item=str}
{if $started}, {/if}
"{$key}": {$Util->toJson($str)}
{assign var=started value=1}
{/foreach}
{rdelim}
{/if}
{if $service->_type neq $smarty.const.SRA_WS_GLOBAL_TYPE_RB && $service->_type neq $smarty.const.SRA_WS_GLOBAL_TYPE_SQL && ($response || $response eq 0)}
, "response": {if $request->_format eq $smarty.const.SRA_WS_FORMAT_XML}{assign var=xml value=$Util->attrToXml($response, $request->_includeAttrs, $request->_excludeAttrs)}{$Util->toJson($xml)}{else}{$Util->toJson($response, $request->_includeAttrs, $request->_excludeAttrs, $request->_jsDates)}{/if}
{/if}
{if !$request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT && $entity && $entity->validateErrors}
, "validationErrors": {ldelim}
{assign var=started value=0}
{foreach from=$entity->validateErrors key=key item=msg}
{if $started}, {/if}
"{$key}": {$Util->toJson($msg)}
{assign var=started value=1}
{/foreach}
{rdelim}
{elseif $request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_FAILED && $request->_methodErrorMsg}
, "error": {$Util->toJson($request->_methodErrorMsg)}
{/if}
{rdelim}{if $isJquery || $request->_callback}){/if}
