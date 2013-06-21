<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$package} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
{include file=_quicksearch.tpl}
</head>
<body id="package" onload="document.getElementById('quicksearch').onkeyup = updateQsTips; document.getElementById('quicksearch').focus()">
  <h1>{$apiresources->getString('text.package')} {$package}</h1>
  <hr />
  
  <form onsubmit="return false">
  
  <div style="float:right">
    <b><label for="quicksearch">{$apiresources->getString('text.quicksearch')}:</label></b><br />
    <input id="quicksearch" type="text" onfocus="clearQsTips()" onblur="hideQsTips()" autocomplete="off" onclick="this.select()" /><br />
    <div id="quicksearch_tips" style="border: 1px solid #333; position:absolute; display:none"></div>
  </div>
  
  <h2>{$apiresources->getString('package.links')}</h2>
  <ul>
    <li><a href="#comments">{$apiresources->getString('package.comment')}</a></li>
{if $constants}
    <li><a href="#constants">{$apiresources->getString('text.constants')}</a></li>
    <li><a href="#constant_details">{$apiresources->getString('package.constantDetails')}</a></li>
{/if}
{if $functions}
    <li><a href="#functions">{$apiresources->getString('text.functions')}</a></li>
{/if}
{if $classes}
    <li><a href="#classes">{$apiresources->getString('text.classes')}</a></li>
{/if}
{if $dtds}
    <li><a href="#dtds">{$apiresources->getString('text.dtds')}</a></li>
{/if}
  </ul>
  
  <h2 id="comments">{$apiresources->getString('text.comment')}</h2>
  <pre>{if $comment}{assign var=comment value=$comment|escape}{$Template->lineBreaksToBr($comment)}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
  
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
{/if}

{if $functions}
  <h2 id="functions">{$apiresources->getString('package.summary.function')}</h2>
  <table border="1" width="100%">
{foreach from=$functions key=id item=name}
    <tr id="function_{$name}">
      <td><pre><a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}../functions/{$id}.html{else}{$uri}?function={$id}{/if}" target="contentFrame">{$name}</a></pre></td>
      <td><pre>{assign var=comment value=$apigenerator->_getFunctionComment($id)|escape}{if $comment}{$comment}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
{/if}
  
{if $classes}
  <h2 id="classes">{$apiresources->getString('package.summary.class')}</h2>
  <table border="1" width="100%">
{foreach from=$classes key=id item=name}
    <tr id="class_{$name}">
      <td><pre><a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}../classes/{$id}.html{else}{$uri}?class={$id}{/if}" target="contentFrame">{$name}</a></pre></td>
      <td><pre>{assign var=comment value=$apigenerator->_getClassComment($id)|escape}{if $comment}{$comment}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
{/if}

{if $dtds}
  <h2 id="dtds">{$apiresources->getString('package.summary.dtd')}</h2>
  <table border="1" width="100%">
{foreach from=$dtds key=id item=name}
    <tr id="dtd_{$name}">
      <td><pre><a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}../dtds/{$id}.html{else}{$uri}?dtd={$id}{/if}" target="contentFrame">{$name}</a></pre></td>
      <td><pre>{assign var=comment value=$apigenerator->_getDtdComment($id)|escape}{if $comment}{$comment}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
{/if}


{if $constants}
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
