{ldelim}
  "apiVersion": "1.0",
  "swaggerVersion": "1.2",
  "apis": [
  {foreach from=$apis item=api}{assign var=last_api value=$api}{/foreach}
  {foreach from=$apis item=api}
  {assign var=desc value="api.resources."|cat:$api}
  {ldelim}
    "path": "{if $uriPrefix}{$uriPrefix}{/if}/{$api}/api",
    "description": "{$api_resources->getString($desc)}"
  {rdelim}{if $api neq $last_api},{/if}
  {/foreach}
  ],
  "authorizations": {ldelim}
    "api-key": {ldelim}
      "type": "apiKey",
      "passAs": "header",
      "keyname": "Authorization"
    {rdelim}
  {rdelim},
  "info": {ldelim}
    "title": "{$api_resources->getString('api.resources.title')}",
    "description": "{$api_resources->getString('api.resources.desc')}",
    "termsOfServiceUrl": "{$server_uri}/terms",
    "contact": "{$api_resources->getString('api.resources.email')}"
  {rdelim}
{rdelim}
