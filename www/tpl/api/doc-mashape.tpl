<api xmlns="http://mashape.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://mashape.com http://www.mashape.com/schema/mashape-4.0.xsd">
{if $router->_settings.oauth_authorization_url}
  <authentication type="OAuth2">
    <configuration name="authorizationUrl" value="{$router->_settings.oauth_authorization_url}" />
    <configuration name="accessTokenUrl" value="{$router->_settings.oauth_token_url}" />
{foreach from=$router->_settings.oauth_scope key=value item=desc}
    <configuration name="scope" value="{$value}">
{if $desc}
      <description><![CDATA[{$desc}]]></description>
{/if}
    </configuration>
{/foreach}
  </authentication>
{/if}
{foreach from=$router->_methods item=method}
{if !$method.doc_hidden}
{foreach from=$method.http_methods item=http}
  <endpoint name="{$method.name}"{if $method.tag_primary} group="{$method.tag_primary}"{/if} http="{$http}">
    <route><![CDATA[{$method.route.fixed}{foreach from=$method.route.placeholders item=placeholder}/{ldelim}{$placeholder}{rdelim}{/foreach}]]></route>
{if $method.description}
    <description><![CDATA[{$method.description}]]></description>
{/if}
    <parameters>
{foreach from=$method.params key=name item=param}
      <parameter type="{if $param.type eq 'float' || $param.type eq 'int'}number{elseif $param.type eq 'boolean' || $param.type eq 'bool'}boolean{else}string{/if}" optional="{if $param.required || $param.placeholder}false{else}true{/if}" name="{$name}"{if $param.default} default="{$param.default}"{/if}>
{if $param.description}
        <description><![CDATA[{$param.description}]]></description>
{/if}
{if $param.example}
        <example><![CDATA[{$param.example}]]></example>
{/if}
{if $param.options}
        <values>
{foreach from=$param.options item=option}
          <value>{$option}</value>
{/foreach}
        </values>
{/if}
      </parameter>
{/foreach} 
{if $method.return.entity}{assign var=entity value=$router->getEntities($method.return.type)}
      <parameter type="string" optional="true" name="callback">
        <description><![CDATA[{$api_resources->getString('api.callback')}]]></description>
        <example><![CDATA[my_callback_function]]></example>
      </parameter>
{if $entity.hasDate && !$method.skip_date_params}
      <parameter type="string" optional="true" name="format-date" default="{$method.format_time}">
        <description><![CDATA[{$api_resources->getString('api.formatDate')}]]></description>
        <example><![CDATA[{$method.format_time}]]></example>
      </parameter>
      <parameter type="boolean" optional="true" name="use-date-object" default="false">
        <description><![CDATA[{$api_resources->getString('api.useDateObject')}]]></description>
      </parameter>
{/if}
      <parameter type="boolean" optional="true" name="beautify" default="{if $method.beautify}true{else}false{/if}">
        <description><![CDATA[{$api_resources->getString('api.beautify')}]]></description>
      </parameter>
{if $method.return.csv_method}
			<parameter type="boolean" optional="true" name="csv" default="false">
			  <description><![CDATA[{$api_resources->getString('api.return.csv')}]]></description>
			</parameter>
{/if}
{/if}
{if $method.api_key_validate}
      <parameter type="string" optional="false" name="{$router->_settings.api_key_name}">
        <description><![CDATA[{$api_resources->getString('api.apiKey')}]]></description>
        <example><![CDATA[4fx2yy9nZw6b3cLJKXpPcTIhDRkdYXM5]]></example>
      </parameter>
{/if}
    </parameters>
{if $method.return.type neq 'void'}
    <response type="{if $method.return.entity}{if $method.return.array}List[{/if}{$method.return.type_label}{if $method.return.array}]{/if}{else}string{/if}"/>
{/if}
    <errors>
{foreach from=$method.status_codes_num key=code item=description}
{if !$method.status_doc_skip[$code]}
{if $code gt 299}
      <error code="{$code}">
{if $description}
        <description><![CDATA[{$description}]]></description>
{/if}
      </error>
{/if}
{/if}
{/foreach}
    </errors>
  </endpoint>
{/foreach}
{/if}
{/foreach}
{foreach from=$router->getEntities() key=type item=entity}
  <model name="{$entity.type_label}">
{if $entity.description}
    <description><![CDATA[{$entity.description}]]></description>
{/if}
{if $entity.example}
    <example>
    <![CDATA[
{$entity.example}
    ]]>
    </example>
{/if}
    <fields>
{foreach from=$entity.attributes key=name item=attribute}
{if $attribute.entity}
      <complex name="{$name}" optional="{if $attribute.required}false{else}true{/if}" type="{if $attribute.array}List[{/if}{$attribute.type_label}{if $attribute.array}]{/if}">
{if $attribute.description}
        <description><![CDATA[{$attribute.description}]]></description>
{/if}
      </complex>
{else}
      <simple name="{$name}" optional="{if $attribute.required}false{else}true{/if}" type="{if $attribute.array}List[{/if}{if $attribute.type eq 'float' || $attribute.type eq 'int'}number{elseif $attribute.type eq 'boolean' || $attribute.type eq 'bool'}boolean{else}string{/if}{if $attribute.array}]{/if}"{if $attribute.default} default="{$attribute.default}"{/if}>
{if $attribute.description}
        <description><![CDATA[{$attribute.description}]]></description>
{/if}
{if $attribute.example}
        <example><![CDATA[{$param.example}]]></example>
{/if}
{if $attribute.options}
        <values>
{foreach from=$attribute.options key=option item=label}
          <value>{$option}</value>
{/foreach}
        </values>
{/if}
      </simple>
{/if}
{/foreach}
    </fields>
  </model>
{/foreach}
</api>
