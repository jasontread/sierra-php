<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$apiresources->getString('overview.title')} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
{include file=_quicksearch.tpl}
</head>
<body id="overview" onload="document.getElementById('quicksearch').onkeyup = updateQsTips; document.getElementById('quicksearch').focus()">
  <h1>{$title} {$apiresources->getString('overview.api')}</h1>
  <hr />
  
  <form onsubmit="return false">
  
  <div style="float:right">
    <b><label for="quicksearch">{$apiresources->getString('text.quicksearch')}:</label></b><br />
    <input id="quicksearch" type="text" onfocus="clearQsTips()" onblur="hideQsTips()" autocomplete="off" onclick="this.select()" /><br />
    <div id="quicksearch_tips" style="border: 1px solid #333; position:absolute; display:none"></div>
  </div>
  
{if $constants}
  <ul>
    <li><a href="#packages">{$apiresources->getString('text.packages')}</a></li>
    <li><a href="#constants">{$apiresources->getString('package.summary.constant')}</a></li>
    <li><a href="#constant_details">{$apiresources->getString('package.constantDetails')}</a></li>
  </ul>
{/if}
  <h2 id="packages">{$title} {$apiresources->getString('packages')}</h2>
  <table border="1" width="100%">
    <tr>
      <th>{$apiresources->getString('text.name')}</th>
      <th>{$apiresources->getString('text.comment')}</th>
    </tr>
{foreach from=$packages key=id item=package}
    <tr id="package_{$id}">
      <td><a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}packages/{$package}.html{else}{$uri}?package={$package}{/if}" target="contentFrame">{$id}</a></td>
      <td>{if $comments[$id]}{assign var=comment value=$comments[$id]|escape}{$Template->lineBreaksToBr($comment)}{else}{$apiresources->getString('overview.noPackageComment')}{/if}</td>
    </tr>
{/foreach}
  </table>
  <br /><br />
  
{if $constants}
  <h2 id="constants">{$apiresources->getString('package.summary.constant')}</h2>
  <table border="1" width="100%">
{foreach from=$constants key=name item=val}
{assign var=props value=$apigenerator->_getConstantProperties($name)}
    <tr>
      <td><pre>{if $props.access}{$props.access} {/if}{if $props.type}{$props.type} {/if}</pre></td>
      <td>
        <pre><a href="#constant_{$name}">{$name}</a></pre>
{if $props.comment}<pre>{$props.comment|escape}</pre>{/if}
      </td>
    </tr>
{/foreach}
  </table>
  
  <h2 id="constant_details">{$apiresources->getString('package.constantDetails')}</h2>
{assign var=started value=0}
{foreach from=$constants key=name item=val}
{assign var=props value=$apigenerator->_getConstantProperties($name)}
{if $started} <hr />{/if}
  <h3 id="constant_{$name}">{$name}</h3>
  <pre>{if $props.access}{$props.access} {/if}{if $props.type}{$props.type} {/if}<b>{$name}</b>{if $props.value} = {$props.value}{/if}</pre>
  <pre>{if $props.comment}{$props.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
  <p><b>{$apiresources->getString('constants.file')}</b>: {$apigenerator->_getConstantFile($name)}</p>
{foreach from=$props key=id item=val}
{if $id neq 'name' && $id neq 'access' && $id neq 'type' && $id neq 'comment' && $id neq 'value'}
  <p>{$id} = {$val}</p>
{/if}
{/foreach}
{assign var=started value=1}
{/foreach}
{/if}
  </form>

</body>
</html>
