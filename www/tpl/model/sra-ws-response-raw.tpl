{if $request->_isSelectQuery && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}
{assign var=started value=0}
{foreach from=$results->_results key=rowNum item=row}
{if !$started}
{assign var=cstarted value=0}
{foreach from=$row key=colName item=col}{if $cstarted}, {/if}{$colName}{assign var=cstarted value=1}{/foreach}

{/if}
{assign var=cstarted value=0}
{foreach from=$row key=colName item=col}{if $cstarted}, {/if}{$col}{assign var=cstarted value=1}{/foreach}
{assign var=started value=1}

{/foreach}
{/if}
{if $request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS && $service->_type eq $smarty.const.SRA_WS_GLOBAL_TYPE_RB}
{foreach from=$results->_results key=key item=str}
{$key}={$str}

{/foreach}
{/if}
{if $response}
{if $request->_format eq $smarty.const.SRA_WS_FORMAT_JSON}{$Util->toJson($response, $request->_includeAttrs, $request->_excludeAttrs, 1)}{elseif $request->_format eq $smarty.const.SRA_WS_FORMAT_XML}{$Util->attrToXml($response, $request->_includeAttrs, $request->_excludeAttrs)}{else}{$response}{/if}
{/if}
{if !$request->_global && $request->_status eq $smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT && $entity && $entity->validateErrors}
ERRORS:
{foreach from=$entity->validateErrors key=key item=msg}
{$key}={$msg}

{/foreach}
{/if}
