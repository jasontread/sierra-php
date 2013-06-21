<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$dtd} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
{include file=_quicksearch.tpl}
</head>
<body id="dtd" onload="document.getElementById('quicksearch').onkeyup = updateQsTips; document.getElementById('quicksearch').focus()">
  <h1 id="top">{$dtd}</h1>
{if $package}<p>&nbsp;&nbsp;&nbsp;&nbsp;package <a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}../packages/{$package}.html{else}{$uri}?package={$package}{/if}" target="contentFrame">{$package}</a></p>{/if}
  <hr />
  
  <form onsubmit="return false">
  
  <div style="float:right">
    <b><label for="quicksearch">{$apiresources->getString('text.quicksearch')}:</label></b><br />
    <input id="quicksearch" type="text" onfocus="clearQsTips()" onblur="hideQsTips()" autocomplete="off" onclick="this.select()" /><br />
    <div id="quicksearch_tips" style="border: 1px solid #333; position:absolute; display:none"></div>
  </div>
  
  <h2>{$apiresources->getString('dtd.links')}</h2>
  <ul>
    <li><a href="#comments">{$apiresources->getString('dtd.comment')}</a></li>
{if $src.entities}
    <li><a href="#entities">{$apiresources->getString('dtd.entities')}</a></li>
{/if}
{if $src.elements}
    <li><a href="#elements">{$apiresources->getString('dtd.elements')}</a></li>
    <li><a href="#element_details">{$apiresources->getString('dtd.elementDetails')}</a></li>
{/if}
  </ul>
  
  <h2 id="comments">{$apiresources->getString('text.comment')}</h2>
  <pre>{if $src.comment}{$src.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
  
{if $src.entities}
  <h2 id="entities">{$apiresources->getString('dtd.entities')}</h2>
  <table border="1" width="100%">
    <tr>
      <th>{$apiresources->getString('text.name')}</th>
      <th>{$apiresources->getString('text.value')}</th>
      <th>{$apiresources->getString('text.comment')}</th>
    </tr>
{foreach from=$src.entities key=name item=entity}
    <tr>
      <th><pre>{$name}</pre></th>
      <td>{$entity.value}</td>
      <td><pre>{if $entity.comment}{$entity.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
{/if}

{if $src.elements}
  <h2 id="elements">{$apiresources->getString('dtd.elements')}</h2>
  <table border="1" width="100%">
{foreach from=$src.elements key=name item=element}
    <tr>
      <th><pre><a href="#element_{$name}">{$name}</a>{if $name eq $src.root}<strong> [{$apiresources->getString('dtd.root')}]</strong>{/if}</pre></th>
      <td><pre>{if $element.comment}{$element.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
  
{assign var=started value=0}
  <h2 id="element_details">{$apiresources->getString('dtd.elementDetails')}</h2>
  <a href="#top" style="float:right">{$apiresources->getString('text.top')}</a>
{foreach from=$src.elements key=name item=element}
{if $started} <hr />{/if}
  <a href="#top" style="float:right">{$apiresources->getString('text.top')}</a>
  <h3 id="element_{$name}">{$name}{if $name eq $src.root} [{$apiresources->getString('dtd.root')}]{/if}</h3>
  <pre>{if $element.comment}{$element.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre>
{if $element.used}
  <h4>{$apiresources->getString('dtd.used')}</h4>
  <ul>
{foreach from=$element.used item=parent}
    <li><a href="#element_{$parent}">{$parent}</a></li>
{/foreach}
  </ul>
{/if}

  <h4>{$apiresources->getString('dtd.subelements')}</h4>
{if !$element.elements}
  <p>{$apiresources->getString('dtd.subelements.none')}</p>
{else}
{if $element.mixed}<p>*{$apiresources->getString('dtd.subelements.mixed')}</p>{/if}
  <table border="1">
    <tr>
      <th>{$apiresources->getString('dtd.subelement')}</th>
      <th>{$apiresources->getString('dtd.required')}</th>
      <th>{$apiresources->getString('dtd.many')}</th>
    </tr>
{foreach from=$element.elements item=subelement}
    <tr>
      <td><pre>{if $subelement.set}({assign var=sstarted value=0}{foreach from=$subelement.set item=selement}{if $sstarted} | {/if}{if $selement|lower neq '#pcdata'}<a href="#element_{$selement}">{/if}{$selement}{if $selement|lower neq '#pcdata'}</a>{/if}{assign var=sstarted value=1}{/foreach}){else}{if $subelement.name|lower neq '#pcdata'}<a href="#element_{$subelement.name}">{/if}{$subelement.name}{if $subelement.name|lower neq '#pcdata'}</a>{/if}{/if}</pre></td>
      <td>{if $subelement.required}{$apiresources->getString('text.yes')}{else}{$apiresources->getString('text.no')}{/if}</td>
      <td>{if $subelement.many}{$apiresources->getString('text.yes')}{else}{$apiresources->getString('text.no')}{/if}</td>
    </tr>
{/foreach}
  </table>
{/if}

  <h4>{$apiresources->getString('dtd.attributes')}</h4>
{if !$element.attributes}
  <p>{$apiresources->getString('dtd.attributes.none')}</p>
{else}
  <table border="1">
    <tr>
      <th>{$apiresources->getString('text.name')}</th>
      <th>{$apiresources->getString('dtd.attribute.type')}</th>
      <th>{$apiresources->getString('dtd.attribute.default')}</th>
      <th>{$apiresources->getString('dtd.required')}</th>
      <th>{$apiresources->getString('text.comment')}</th>
    </tr>
{foreach from=$element.attributes item=attr}
    <tr id="element_{$name}_{$attr.name}">
      <th><pre>{$attr.name}</pre></th>
      <td><pre>{if $attr.options}{assign var=ostarted value=0}({foreach from=$attr.options item=option}{if $ostarted} | {/if}{$option}{assign var=ostarted value=1}{/foreach}){else}{$attr.type}{/if}</pre></td>
      <td>{if $attr.default}<pre>{$attr.default}</pre>{else}{$apiresources->getString('dtd.attribute.default.none')}{/if}</td>
      <td>{if $attr.required}{$apiresources->getString('text.yes')}{else}{$apiresources->getString('text.no')}{/if}</td>
      <td><pre>{if $attr.comment}{$attr.comment|escape}{else}{$apiresources->getString('text.noComments')}{/if}</pre></td>
    </tr>
{/foreach}
  </table>
{/if}

{assign var=started value=1}
{/foreach}
{/if}
  
  </form>
</body>
</html>
