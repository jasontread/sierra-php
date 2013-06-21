<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$function} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
</head>
<body id="function">
  <h1>{$apiresources->getString('function')} {$src.name}</h1>
{if $src.package}&nbsp;&nbsp;&nbsp;&nbsp;package <a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}packages/{$src.package}.html{else}{$uri}?package={$src.package}{/if}" target="contentFrame">{$src.package}</a>{/if}
  <hr />
  <pre>{if $src.access}{$src.access} {/if}{if $src.return}{if $apigenerator->_getClassId($src.return)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}classes/{$src.return}.html{else}{$uri}?class={$src.return}{/if}" target="contentFrame">{/if}{$src.return}{if $apigenerator->_getClassId($src.return)}</a>{/if}{else}void{/if} function {if $src.returnRef}&amp;{/if}<b>{$src.name}</b>({assign var=started value=0}{foreach from=$src.params item=param}{if $started}, {/if}{if $param.type}{if $apigenerator->_getClassId($param.type)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}classes/{$param.type}.html{else}{$uri}?class={$param.type}{/if}" target="contentFrame">{/if}{$param.type}{if $apigenerator->_getClassId($param.type)}</a>{/if} {/if}${$param.name}{if $param.value}={$param.value}{/if}{assign var=started value=1}{/foreach})</pre>
  <h2>{$apiresources->getString('text.comment')}</h2>
  <pre>{if $src.comment}{$src.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
{if $src.params}
  <h2>{$apiresources->getString('function.params')}</h2>
  <table border="1" width="100%">
    <tr>
      <th>{$apiresources->getString('text.params.name')}</th>
      <th>{$apiresources->getString('text.params.type')}</th>
      <th>{$apiresources->getString('text.comment')}</th>
      <th>{$apiresources->getString('text.params.default')}</th>
      <th>{$apiresources->getString('text.params.byref')}</th>
    </tr>
{foreach from=$src.params item=param}
    <tr>
      <td>{$param.name}</td>
      <td>{if $param.type}{$param.type}{else}{$apiresources->getString('text.notSpecified')}{/if}</td>
      <td><pre>{if $param.comment}{$param.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
      <td>{if $param.value}{$param.value}{else}{$apiresources->getString('text.none')}{/if}</td>
      <td>{if $param.byRef}{$apiresources->getString('text.yes')}{else}{$apiresources->getString('text.no')}{/if}</td>
    </tr>
{/foreach}
  </table>
{/if}
  <h2>{$apiresources->getString('function.other')}</h2>
  <table border="1" width="100%">
  <tr>
    <th>{$apiresources->getString('function.file')}</th>
    <td>{$path}</td>
  </tr>
{foreach from=$src key=id item=val}
{if $id neq 'params' && $id neq 'return' && $id neq 'returnRef' && $id neq 'name' && $id neq 'comment' && $id neq 'access' && $id neq 'package'}
  <tr>
    <th>{$id}</th>
    <td>{$val|escape}</td>
  </tr>
{/if}
{/foreach}
  </table>
</body>
</html>
