<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$apiresources->getString('classesFrame.title')} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
</head>
<body id="class-list">
  <h3>{if $package}<a href="{if $rewriteUri || $rewriteBase}{if $rewriteUri}{$rewriteUri}{else}../../{/if}packages/{$package}.html{else}{$uri}?package={$package}{/if}" target="contentFrame">{$package}</a>{else}<a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}overview.html{else}{$uri}?overview{/if}" target="contentFrame">{if $dtds && $classes}{$apiresources->getString('packagesFrame.allClasses')}{elseif $dtds}{$apiresources->getString('packagesFrame.allClasses.noClasses')}{else}{$apiresources->getString('packagesFrame.allClasses.noDtds')}{/if}</a>{/if}</h3>
{if $classes}
  <h4>{$apiresources->getString('text.classes')}</h4>
{foreach from=$classes key=id item=name}
  <a href="{if $rewriteUri || $rewriteBase}{if !$package}{$rewriteUri}{else}../../{/if}classes/{$id}.html{else}{$uri}?class={$id}{/if}" target="contentFrame">{$name}</a><br />
{/foreach}
{/if}
{if $functions}
  <h4>{$apiresources->getString('text.functions')}</h4>
{foreach from=$functions key=id item=name}
  <a href="{if $rewriteUri || $rewriteBase}{if !$package}{$rewriteUri}{else}../../{/if}functions/{$id}.html{else}{$uri}?function={$id}{/if}" target="contentFrame">{$name}</a><br />
{/foreach}
{/if}
{if $dtds}
  <h4>{$apiresources->getString('text.dtds')}</h4>
{foreach from=$dtds key=id item=name}
  <a href="{if $rewriteUri || $rewriteBase}{if !$package}{$rewriteUri}{else}../../{/if}dtds/{$id}.html{else}{$uri}?dtd={$id}{/if}" target="contentFrame">{$name}</a><br />
{/foreach}
{/if}
</body>
</html>
