{ldelim}
  "apiVersion": "{if $router->_settings.api_version}{$router->_settings.api_version}{else}1.0{/if}",
  "swaggerVersion": "1.2",
  "basePath": "{$router->_settings.url_base_actual}",
  "resourcePath": "{$router->_settings.url_resource}",
  "produces": [
{if $router->_settings.beautify}
    "application/json",
    "text/json"
{else}
    "application/json"
{/if}
  ],
  "authorizations": {ldelim} {if $router->_settings.swagger_auth}"{$router->_settings.swagger_auth}": []{/if} {rdelim},
  "apis": [
{foreach from=$router->_methods item=method}{if !$method.doc_hidden}{assign var=last_method value=$method.name}{/if}{/foreach}
{foreach from=$router->_methods item=method}
{if !$method.doc_hidden}
    {ldelim}
      "path": "{$router->_settings.url_resource}{$method.route.fixed}{foreach from=$method.route.placeholders item=placeholder}/{ldelim}{$placeholder}{rdelim}{/foreach}",
      "description": "{$method.name}{if $method.description}: {$method.description|replace:'"':'\"'}{/if}",
      "operations": [
{foreach from=$method.http_methods key=i item=http}{assign var=last_http value=$http}{/foreach}
{foreach from=$method.http_methods key=i item=http}
        {ldelim}
          "method": "{$http}",
          "summary": "{if $method.http_method_summaries[$i]}{$method.http_method_summaries[$i]}{else}{$method.name}{/if}",
          "notes": "{if $method.http_method_notes[$i]}{$method.http_method_notes[$i]}{else}{$method.description|replace:'"':'\"'}{/if}",
          "nickname": "{$method.http_method_nicknames[$i]}",
{if $method.swagger_auth}
          "authorizations": {ldelim} {if $method.swagger_auth neq 'none'}"{$method.swagger_auth}": []{/if} {rdelim},
{/if}
          "parameters": [
{assign var=last_param value=0}
{foreach from=$method.params key=name item=param}{assign var=last_param value=$name}{/foreach}
{if $method.return.entity}{assign var=entity value=$router->getEntities($method.return.type)}
{if $entity.hasDate}
            {ldelim}
              "paramType": "{if $http eq 'GET'}query{else}form{/if}",
              "name": "format-date",
              "description": "{$api_resources->getString('api.formatDate')}",
              "required": false,
              "type": "string",
              "defaultValue": "{$method.format_time}"
            {rdelim},
            {ldelim}
              "paramType": "{if $http eq 'GET'}query{else}form{/if}",
              "name": "use-date-object",
              "description": "{$api_resources->getString('api.useDateObject')}",
              "required": false,
              "type": "boolean",
              "defaultValue": false
            {rdelim}{if $last_param},{/if}

{/if}
{if !$method.skip_callback_param}
            {ldelim}
              "paramType": "{if $http eq 'GET'}query{else}form{/if}",
              "name": "callback",
              "description": "{$api_resources->getString('api.callback')}",
              "required": false,
              "type": "string"
            {rdelim}{if $last_param},{/if}
{/if}

{/if}
{foreach from=$method.params key=name item=param}
{assign var=type value=$param.type}
            {ldelim}
              "paramType": "{if $param.placeholder}path{elseif $http eq 'GET'}query{else}form{/if}",
              "name": "{$name}",
{if $param.description}
              "description": "{$param.description|replace:'"':'\"'}",
{/if}
              "required": {if $param.required || $param.placeholder}true{else}false{/if},
              "type": "{if $type eq 'int'}integer{elseif $type eq 'float'}number{elseif $type eq 'boolean' || $type eq 'bool'}boolean{else}string{/if}",
{if $type eq 'date' || $type eq 'timestamp'}
              "format": "{if $type eq 'date'}date{else}date-time{/if}",
{/if}
{if $type eq 'boolean' || $type eq 'bool' || ($param.default && $param.default neq 'NULL' && $param.default neq 'null')}
              "defaultValue": {if $type eq 'string'}"{$param.default}"{elseif $type eq 'boolean' || $type eq 'bool'}{if $param.default}true{else}false{/if}{elseif $param.default}{$param.default}{else}null{/if},
{/if}
{if $param.options}
{foreach from=$param.options item=option}{assign var=last_option value=$option}{/foreach}
              "enum": [{foreach from=$param.options item=option}"{$option}"{if $option neq $last_option}, {/if}{/foreach}],
{/if}
              "allowMultiple": {if $param.array}true{else}false{/if}

            {rdelim}{if $name neq $last_param},{/if}

{/foreach}
          ],{assign var=type value=$method.return.type}

{if $type eq 'date' || $type eq 'timestamp'}
          "format": "{if $type eq 'date'}date{else}date-time{/if}",
{/if}
{if $method.return.array}
          "items": {ldelim}
{if $type eq 'date' || $type eq 'timestamp'}
            "format": "{if $type eq 'date'}date{else}date-time{/if}",
{/if}
            "type": "{if $method.return.entity}{$method.return.type_label}{elseif $type eq 'int'}integer{elseif $type eq 'float'}number{elseif $type eq 'boolean' || $type eq 'bool'}boolean{else}string{/if}"
          {rdelim},
{/if}
{assign var=started value=0}
{foreach from=$method.status_codes_num key=code item=description}{if !$method.status_doc_skip[$code]}{if $code gt 299}{assign var=last_code value=$code}{/if}{/if}{/foreach}
{foreach from=$method.status_codes_num key=code item=description}
{if !$method.status_doc_skip[$code]}
{if $code gt 299}
{if !$started}
          "responseMessages": [
{/if}
            {ldelim}
              "code": {$code},
              "message": "{if $description}{$description}{else}{$api_resources->getString('api.noErrorDescription')}{/if}"
            {rdelim}{if $code neq $last_code},{/if}

{assign var=started value=1}
{/if}
{/if}
{/foreach}
{if $started}
          ],
{/if}
{if $method.return.entity && $method.return.csv_method}
          "produces": [
{if $router->_settings.beautify}
            "application/json",
            "text/json",
{else}
            "application/json",
{/if}
            "text/csv"
          ],
{/if}
          "type": "{if $method.return.array}array{else}{if $method.return.entity}{$method.return.type_label}{elseif $type eq 'int'}integer{elseif $type eq 'float'}number{elseif $type eq 'boolean' || $type eq 'bool'}boolean{elseif $type eq 'void'}void{else}string{/if}{/if}"
        {rdelim}{if $http neq $last_http},{/if}

{/foreach}
      ]
    {rdelim}{if $method.name neq $last_method},{/if}

{/if}
{/foreach}
  ],
  "models": {ldelim}
{foreach from=$router->getEntities() key=type item=entity}{assign var=last_entity value=$entity.type_label}{/foreach}
{foreach from=$router->getEntities() key=type item=entity}
    "{$entity.type_label}": {ldelim}
      "id": "{$entity.type_label}",
{if $entity.description}
      "description": "{$entity.description|replace:'"':'\"'}",
{/if}
      "required": [
{foreach from=$entity.attributes key=name item=attribute}{if $attribute.required}{assign var=last_required value=$name}{/if}{assign var=last_attribute value=$name}{/foreach}
{foreach from=$entity.attributes key=name item=attribute}
{if $attribute.required}
        "{$name}"{if $name neq $last_required},{/if}

{/if}
{/foreach}
      ],
      "properties": {ldelim}
{foreach from=$entity.attributes key=name item=attribute}
{assign var=type value=$attribute.type}
        "{$name}": {ldelim}
{if $attribute.description}
          "description": "{$attribute.description|replace:'"':'\"'}",
{/if}
          "required": {if $attribute.required}true{else}false{/if},
{if $type eq 'date' || $type eq 'timestamp'}
          "format": "{if $type eq 'date'}date{else}date-time{/if}",
{/if}
{if $attribute.default || $type eq 'boolean' || $type eq 'bool'}
          "defaultValue": {if $type eq 'string'}"{$attribute.default}"{elseif $type eq 'boolean' || $type eq 'bool'}{if $attribute.default}true{else}false{/if}{elseif $attribute.default}{$attribute.default}{else}null{/if},
{/if}
{if $attribute.options}
{foreach from=$attribute.options key=option item=label}{assign var=last_option value=$option}{/foreach}
          "enum": [{foreach from=$attribute.options key=option item=label}"{$option}"{if $option neq $last_option}, {/if}{/foreach}],
{/if}
{if $attribute.array}
          "items": {ldelim}
{if $type eq 'date' || $type eq 'timestamp'}
            "format": "{if $type eq 'date'}date{else}date-time{/if}",
{/if}
            "type": "{if $attribute.entity}{$attribute.type_label}{elseif $type eq 'int'}integer{elseif $type eq 'float'}number{elseif $type eq 'boolean' || $type eq 'bool'}boolean{else}string{/if}"
          {rdelim},
{/if}
          "type": "{if $attribute.array}array{elseif $type eq 'int'}integer{elseif $type eq 'float'}number{elseif $type eq 'boolean' || $type eq 'bool'}boolean{else}string{/if}"
        {rdelim}{if $name neq $last_attribute},{/if}

{/foreach}
      {rdelim}
    {rdelim}{if $entity.type_label neq $last_entity},{/if}

{/foreach}
  {rdelim}
{rdelim}
