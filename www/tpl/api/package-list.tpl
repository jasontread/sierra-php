<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$apiresources->_locale->getLanguage()}">
<head>
	<title>{$apiresources->getString('packagesFrame.title')} - {$title}</title>
{if $cssUri}
	<style type="text/css" title="currentStyle" media="screen">
		@import "{$cssUri};
	</style>
{/if}
</head>
<body id="package-list">
  <h2>{$title}</h2>
  <p>
    <a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}classes.html{else}{$uri}?classlist{/if}" target="classesFrame">{$apiresources->getString('packagesFrame.allClasses')}</a>
  </p>
  <h3>{$apiresources->getString('text.packages')}</h3>
  <p>
{foreach from=$packages item=package}
    <a href="{if $rewriteUri || $rewriteBase}{$rewriteUri}packages/{$package}/classes.html{else}{$uri}?classlist&package={$package}{/if}" target="classesFrame">{$package}</a><br />
{/foreach}
  </p>
</body>
</html>
