<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
	"http://www.w3.org/TR/html4/frameset.dtd">
<html>
  <head>
    <title>{$title}</title>
  </head>
  <frameset cols="20%,80%">
    <frameset rows="30%,70%">
      <frame src="{if $rewriteUri || $rewriteBase}{$rewriteUri}packages.html{else}{$uri}?packagelist{/if}" name="packagesFrame" title="{$apiresources->getString('packagesFrame.title')}">
      <frame src="{if $rewriteUri || $rewriteBase}{$rewriteUri}classes.html{else}{$uri}?classlist{/if}" name="classesFrame" title="{if !$skipDtds}{$apiresources->getString('classesFrame.title')}{else}{$apiresources->getString('classesFrame.title.noDtds')}{/if}">
    </frameset>
    <frame src="{if $rewriteUri || $rewriteBase}{$rewriteUri}overview.html{else}{$uri}?overview{/if}" name="contentFrame" title="{if !$skipDtds}{$apiresources->getString('contentFrame.title')}{else}{$apiresources->getString('contentFrame.title.noDtds')}{/if}">
  </frameset>
</html>
