<html>
<head>
  <title>{$apiResources->getString('overview.title')}</title>
{if $wsCssUri}
  <link rel="stylesheet" type="text/css" href="{$wsCssUri}" />
{else}
  <style type="text/css">
{php}include(SRA_DIR . '/www/tpl/model/sra-ws-api.css');{/php}
  </style>
{/if}
</head>
<body id="wsOverview">
  <h1>{$apiResources->getString('overview.title')}</h1>
  
  <p id="wsGateway">
  <h2>{$apiResources->getString('overview.wsGateway')}</h2>
  {$apiResources->getString('overview.wsGatewayDesc')} <span class="wsUri">{$wsUri}</span>
  </p>
  
  <p id="webServices">
  <h2>{$apiResources->getString('overview.webServices')}</h2>
  {$apiResources->getString('overview.webServicesDesc')}
  <table id="wsServicesList">
    <tr>
      <th id="serviceName">{$apiResources->getString('text.service')}</th>
      <th id="apiLink">{$apiResources->getString('text.api')}</th>
      <th id="wsdlLink">{$apiResources->getString('text.wsdl')}</th>
    </tr>
{foreach from=$services item=service}{if $service->isPublic()}
    <tr>
      <td class="serviceName">{$service->_id}</td>
      <td class="apiLink"><a href="{$service->getApiUri()}">{$apiResources->getString('text.view')}</a><a href="{$service->getExecXmlUri()}" style="text-decoration:none">&nbsp;</a></td>
      <td class="wsdlLink">{if $service->_soap}<a href="{$service->getWsdlUri()}">{$apiResources->getString('text.download')}</a>{else}{$apiResources->getString('text.soapNotEnabled')}{/if}</td>
    </tr>
{/if}{/foreach}
  </table>
  </p>
  {if $containsSoapService}<p id="wsdlLink"><a href="{$wsdlUri}">{$apiResources->getString('overview.wsdlLink')}</a></p>
{/if}
</body>
</html>