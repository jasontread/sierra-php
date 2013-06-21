<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$class} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
{include file=_quicksearch.tpl}
</head>
<body id="class" onload="document.getElementById('quicksearch').onkeyup = updateQsTips; document.getElementById('quicksearch').focus()">
  <h1 id="top">{$apiresources->getString('class')} {$src.name}</h1>
{if $src.package}<p>&nbsp;&nbsp;&nbsp;&nbsp;package <a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}../packages/{$src.package}.html{else}{$uri}?package={$src.package}{/if}" target="contentFrame">{$src.package}</a></p>{/if}
{if $src.extends}{if $src.package}<br />{/if}<p>&nbsp;&nbsp;&nbsp;&nbsp;extends {if $src.extendsName}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$src.extendsName}.html{else}{$uri}?class={$src.extendsName}{/if}" target="contentFrame">{/if}{$src.extends}{if $src.extendsName}</a></p>{/if}{/if}
{assign var=subclasses value=$apigenerator->_getSubclasses($src.name)}
{if $subclasses}
  <h2>{$apiresources->getString('class.subclasses')}</h2>
{assign var=started value=0}{foreach from=$subclasses key=id item=name}{if $started}, {/if}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$id}.html{else}{$uri}?class={$id}{/if}" target="contentFrame">{$name}</a>{assign var=started value=1}{/foreach}
{/if}
  <hr />
  <form onsubmit="return false">
  
  <div style="float:right">
    <b><label for="quicksearch">{$apiresources->getString('text.quicksearch')}:</label></b><br />
    <input id="quicksearch" type="text" onfocus="clearQsTips()" onblur="hideQsTips()" autocomplete="off" onclick="this.select()" /><br />
    <div id="quicksearch_tips" style="border: 1px solid #333; position:absolute; display:none"></div>
  </div>
  
  <pre>{if $src.access}[{$src.access}] {/if}class {$src.name}{if $src.extends} extends {if $src.extendsName}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$src.extendsName}.html{else}{$uri}?class={$src.extendsName}{/if}" target="contentFrame">{/if}{$src.extends}{if $src.extendsName}</a>{/if}{/if}</pre>
  
  <h2>{$apiresources->getString('class.links')}</h2>
  <ul>
    <li><a href="#comments">{$apiresources->getString('class.comment')}</a></li>
{if $constants}
    <li><a href="#constants">{$apiresources->getString('text.constants')}</a></li>
    <li><a href="#constant_details">{$apiresources->getString('class.constantDetails')}</a></li>
{/if}
{if $src.attrs}
    <li><a href="#attrs">{$apiresources->getString('text.attrs')}</a></li>
    <li><a href="#attr_details">{$apiresources->getString('class.attrDetails')}</a></li>
{/if}
{if $src.methods}
    <li><a href="#methods">{$apiresources->getString('text.methods')}</a></li>
    <li><a href="#method_details">{$apiresources->getString('class.methodDetails')}</a></li>
{/if}
    <li><a href="#other">{$apiresources->getString('text.other')}</a></li>
  </ul>
  
  <h2 id="comments">{$apiresources->getString('text.comment')}</h2>
  <pre>{if $src.comment}{$src.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
  
{if $constants}
  <h2 id="constants">{$apiresources->getString('class.constants')}</h2>
  <table border="1" width="100%">
{foreach from=$constants key=name item=props}
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
  
{if $src.attrs}
  <h2 id="attrs">{$apiresources->getString('class.attrs')}</h2>
  <table border="1" width="100%">
{foreach from=$src.attrs key=id item=attr}
    <tr>
      <td><pre>{if $attr.access}{$attr.access} {/if}{if $attr.type}{if $apigenerator->_getClassId($attr.type)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($attr.type)}.html{else}{$uri}?class={$apigenerator->_getClassId($attr.type)}{/if}" target="contentFrame">{/if}{$attr.type}{if $apigenerator->_getClassId($attr.type)}</a>{/if} {/if}</pre></td>
      <td>
        <pre><a href="#attr_{$attr.name}">{$attr.name}</a></pre>
{if $attr.comment}<pre>{$attr.comment|escape}</pre>{/if}
      </td>
    </tr>
{/foreach}
  </table>
{/if}

{if $src.methods}
  <h2 id="methods">{$apiresources->getString('class.methods')}</h2>
  <table border="1" width="100%">
{foreach from=$src.methods key=id item=method}
    <tr>
      <td><pre>{if $method.access}{$method.access} {/if}{if $method.return}{if $apigenerator->_getClassId($method.return)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($method.return)}.html{else}{$uri}?class={$apigenerator->_getClassId($method.return)}{/if}" target="contentFrame">{/if}{$method.return}{if $apigenerator->_getClassId($method.return)}</a>{/if}{else}void{/if}</pre></td>
      <td>
        <pre>{if $method.returnRef}&amp;{/if}<a href="#method_{$method.name}">{$method.name}</a>({assign var=started value=0}{foreach from=$method.params item=param}{if $started}, {/if}{if $param.type}{if $apigenerator->_getClassId($param.type)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($param.type)}.html{else}{$uri}?class={$apigenerator->_getClassId($param.type)}{/if}" target="contentFrame">{/if}{$param.type}{if $apigenerator->_getClassId($param.type)}</a>{/if} {/if}${$param.name}{if $param.value}={$param.value}{/if}{assign var=started value=1}{/foreach})</pre>
{if $method.comment}<pre>{$method.comment|escape}</pre>{/if}
      </td>
    </tr>
{/foreach}
  </table>
{/if}

{if $constants}
{assign var=started value=0}
  <h2 id="constant_details">{$apiresources->getString('class.constantDetails')}</h2>
  <a href="#top" style="float:right">{$apiresources->getString('text.top')}</a>
{foreach from=$constants key=name item=props}
{if $started} <hr />{/if}
  <h3 id="constant_{$name}">{$name}</h3>
  <pre>{if $props.access}{$props.access} {/if}{if $props.type}{$props.type} {/if}<b>{$name}</b>{if $props.value} = {$props.value}{/if}</pre>
  <pre>{if $props.comment}{$props.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
{foreach from=$props key=id item=val}
{if $id neq 'name' && $id neq 'access' && $id neq 'type' && $id neq 'comment' && $id neq 'value'}
  <p>{$id} = {$val}</p>
{/if}
{/foreach}
{assign var=started value=1}
{/foreach}
{/if}

{if $src.attrs}
{assign var=started value=0}
  <h2 id="attr_details">{$apiresources->getString('class.attrDetails')}</h2>
  <a href="#top" style="float:right">{$apiresources->getString('text.top')}</a>
{foreach from=$src.attrs key=id item=attr}
{if $started} <hr />{/if}
  <h3 id="attr_{$attr.name}">{$attr.name}</h3>
  <pre>{if $attr.access}{$attr.access} {/if}{if $attr.type}{if $apigenerator->_getClassId($attr.type)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($attr.type)}.html{else}{$uri}?class={$apigenerator->_getClassId($attr.type)}{/if}" target="contentFrame">{/if}{$attr.type}{if $apigenerator->_getClassId($attr.type)}</a>{/if} {/if}var <b>${$attr.name}</b>{if $attr.value} = {$attr.value}{/if}</pre>
  <pre>{if $attr.comment}{$attr.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
{foreach from=$attr key=id item=val}
{if $id neq 'name' && $id neq 'access' && $id neq 'type' && $id neq 'comment' && $id neq 'value'}
  <p>{$id} = {$val}</p>
{/if}
{/foreach}
{assign var=started value=1}
{/foreach}
{/if}

{if $src.methods}
{assign var=started value=0}
  <h2 id="method_details">{$apiresources->getString('class.methodDetails')}</h2>
  <a href="#top" style="float:right">{$apiresources->getString('text.top')}</a>
{foreach from=$src.methods key=id item=method}
{if $started} <hr />{/if}
  <h3 id="method_{$method.name}">{$method.name}</h3>
  <pre>{if $method.access}{$method.access} {/if}{if $method.return}{if $apigenerator->_getClassId($method.return)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($method.return)}.html{else}{$uri}?class={$apigenerator->_getClassId($method.return)}{/if}" target="contentFrame">{/if}{$method.return}{if $apigenerator->_getClassId($method.return)}</a>{/if}{else}void{/if} function {if $method.returnRef}&amp;{/if}<b>{$method.name}</b>({assign var=started value=0}{foreach from=$method.params item=param}{if $started}, {/if}{if $param.type}{if $apigenerator->_getClassId($param.type)}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}{$apigenerator->_getClassId($param.type)}.html{else}{$uri}?class={$apigenerator->_getClassId($param.type)}{/if}" target="contentFrame">{/if}{$param.type}{if $apigenerator->_getClassId($param.type)}</a>{/if} {/if}${$param.name}{if $param.value}={$param.value}{/if}{assign var=started value=1}{/foreach})</pre>
  <pre>{if $method.comment}{$method.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
{if $method.params}
  <h2>{$apiresources->getString('class.method.params')}</h2>
  <table border="1" width="100%">
    <tr>
      <th>{$apiresources->getString('text.params.name')}</th>
      <th>{$apiresources->getString('text.params.type')}</th>
      <th>{$apiresources->getString('text.comment')}</th>
      <th>{$apiresources->getString('text.params.default')}</th>
      <th>{$apiresources->getString('text.params.byref')}</th>
    </tr>
{foreach from=$method.params item=param}
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
{foreach from=$method key=id item=val}
{if $id neq 'name' && $id neq 'access' && $id neq 'type' && $id neq 'comment' && $id neq 'return' && $id neq 'returnRef' && $id neq 'params'}
  <p>{$id} = {$val}</p>
{/if}
{/foreach}
{assign var=started value=1}
{/foreach}
{/if}
  
  <h2>{$apiresources->getString('function.other')}</h2>
  <table id="other" border="1" width="100%">
  <tr>
    <th>{$apiresources->getString('class.file')}</th>
    <td>{$path}</td>
  </tr>
{foreach from=$src key=id item=val}
{if $id neq 'methods' && $id neq 'extends' && $id neq 'attrs' && $id neq 'name' && $id neq 'comment' && $id neq 'extendsName' && $id neq 'package'}
  <tr>
    <th>{$id}</th>
    <td>{$val|escape}</td>
  </tr>
{/if}
{/foreach}
  </table>
  </form>
</body>
</html>
