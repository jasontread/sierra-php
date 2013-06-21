{*
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | SIERRA : PHP Application Framework  http://code.google.com/p/sierra-php |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | Copyright 2005 Jason Read                                               |
 |                                                                         |
 | Licensed under the Apache License, Version 2.0 (the "License");         |
 | you may not use this file except in compliance with the License.        |
 | You may obtain a copy of the License at                                 |
 |                                                                         |
 |     http://www.apache.org/licenses/LICENSE-2.0                          |
 |                                                                         |
 | Unless required by applicable law or agreed to in writing, software     |
 | distributed under the License is distributed on an "AS IS" BASIS,       |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.|
 | See the License for the specific language governing permissions and     |
 | limitations under the License.                                          |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 
 this template is used to generate the wsdl for model-defined web services
 
*}
<definitions targetNamespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}" xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:soap11="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:tns="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
  <types>
    <schema targetNamespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}" xmlns="http://www.w3.org/2001/XMLSchema">
{if $hasRetrieveService || $hasCreateService || $hasUpdateService || $hasMethodService}
      <complexType name="WsArray">
        <sequence>
{if $hasRetrieveService}
{foreach from=$entities key=name item=data}
          <element minOccurs="0" maxOccurs="unbounded" name="{$name}" type="tns:{$name}" />
{/foreach}
{/if}
          <element minOccurs="0" maxOccurs="unbounded" name="array" type="tns:WsArray" />
          <element minOccurs="0" maxOccurs="unbounded" name="arrayItem" type="tns:WsArrayItem" />
        </sequence>
        <attribute name="key" type="xsd:string" use="optional" />
      </complexType>
      <complexType name="WsArrayItem">
        <attribute name="key" type="xsd:string" use="optional" />
      </complexType>
{/if}
{if $hasSqlService}
      <complexType name="WsCol">
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $hasRetrieveService}
      <complexType name="WsConstraint">
        <attribute name="attr" type="xsd:string" use="required" />
        <attribute name="attrType" type="tns:attrType" use="optional" />
        <attribute name="operator" type="xsd:int" use="optional" />
        <attribute name="value" type="xsd:string" use="optional" />
        <attribute name="valueType" type="tns:valueType" use="optional" />
      </complexType>
      <complexType name="WsConstraintGroup">
        <sequence>
          <element minOccurs="1" maxOccurs="unbounded" name="constraint" type="tns:WsConstraint" />
        </sequence>
        <attribute name="connective" type="tns:connective" use="optional" />
      </complexType>
{/if}
{if $hasCreateService}
      <complexType name="WsCreate">
        <sequence>
          <element minOccurs="1" maxOccurs="unbounded" name="param" type="tns:WsParam" />
        </sequence>
        <attribute name="key" type="xsd:string" use="optional" />
      </complexType>
{/if}
{if $hasDeleteService}
      <complexType name="WsDelete">
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
      <complexType name="WsError">
        <attribute name="key" type="xsd:string" use="optional" />
      </complexType>
{if $hasCreateService || $hasMethodService || $hasUpdateService}
      <complexType name="WsParam">
        <attribute name="name" type="xsd:string" use="required" />
        <attribute name="value" type="xsd:string" use="optional" />
        <attribute name="valueType" type="tns:valueType" use="optional" />
      </complexType>
{/if}
{if $hasSqlService}
      <complexType name="WsQueryResults">
        <sequence>
          <element minOccurs="0" maxOccurs="unbounded" name="row" type="tns:WsRow" />
        </sequence>
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
      <complexType name="WsRequest">
        <sequence>
{if $hasRetrieveService}
          <element minOccurs="0" maxOccurs="unbounded" name="constraintGroup" type="tns:WsConstraintGroup" />
{/if}
{if $hasCreateService }
          <element minOccurs="0" maxOccurs="1" name="create" type="tns:WsCreate" />
{/if}
{if $hasDeleteService} 
          <element minOccurs="0" maxOccurs="1" name="delete" type="tns:WsDelete" />
{/if}
{if $hasRetrieveService}
          <element minOccurs="0" maxOccurs="1" name="retrieve" type="tns:WsRetrieve" />
{/if}
{if $hasUpdateService}
          <element minOccurs="0" maxOccurs="1" name="update" type="tns:WsUpdate" />
{/if}
{if $hasCreateService || $hasMethodService || $hasUpdateService}
          <element minOccurs="0" maxOccurs="unbounded" name="param" type="tns:WsParam" />
{/if}
        </sequence>
{if $hasRetrieveService}
        <attribute name="excludeAttrs" type="xsd:string" use="optional" />
{/if}
{if $unfixedFormat}
        <attribute name="format" type="tns:format" use="optional" />
{/if}
{if $hasRetrieveService}
        <attribute name="includeAttrs" type="xsd:string" use="optional" />
{/if}
{if $unfixedFormat || $formatJson}
        <attribute name="jsDates" type="xsd:boolean" use="optional" />
        <attribute name="dateFormat" type="xsd:string" use="optional" />
        <attribute name="timeFormat" type="xsd:string" use="optional" />
{/if}
{if $hasRetrieveService}
        <attribute name="limit" type="xsd:int" use="optional" />
        <attribute name="offset" type="xsd:int" use="optional" />
{/if}
        <attribute name="password" type="xsd:string" use="optional" />
        <attribute name="requestId" type="xsd:string" use="optional" />
        <attribute name="requestId1" type="xsd:string" use="optional" />
        <attribute name="sessionId" type="xsd:string" use="optional" />
        <attribute name="useSessions" type="xsd:boolean" use="optional" />
        <attribute name="user" type="xsd:string" use="optional" />
{if $hasNonMandatoryValidator && ($hasCreateService || $hasUpdateService)}
        <attribute name="validators" type="xsd:string" use="optional" />
{/if}
      </complexType>
      <complexType name="WsResponse">
        <sequence>
{foreach from=$entities key=name item=data}
          <element minOccurs="0" maxOccurs="unbounded" name="{$name}" type="tns:{$name}" />
{/foreach}
{if $hasSqlService}
          <element minOccurs="0" maxOccurs="1" name="queryResults" type="tns:WsQueryResults" />
{/if}
{if $hasRbService}
          <element minOccurs="0" maxOccurs="1" name="resourceBundle" type="tns:WsResourceBundle" />
{/if}
{if $hasCreateService || $hasDeleteService || $hasUpdateService}
          <element minOccurs="0" maxOccurs="1" name="validationErrors" type="tns:WsValidationErrors" />
{/if}
{if $hasGlobalService}
          <element minOccurs="0" maxOccurs="unbounded" name="array" type="tns:WsArray" />
{/if}
          <element minOccurs="0" maxOccurs="1" name="error" type="tns:WsError" />
{if $formatJson}
          <element minOccurs="0" maxOccurs="1" name="json" type="xsd:string" />
{/if}
{if $formatRaw}
          <element minOccurs="0" maxOccurs="1" name="raw" type="xsd:string" />
{/if}
        </sequence>
{if $hasEntityService}
        <attribute name="action" type="tns:action" use="optional" />
{/if}
        <attribute name="count" type="xsd:int" use="optional" />
        <attribute name="format" type="tns:format" use="required" />
{if $hasGlobalService}
        <attribute name="global" type="xsd:boolean" use="required" />
{/if}
{if $hasRetrieveService}
        <attribute name="limit" type="xsd:int" use="optional" />
        <attribute name="offset" type="xsd:int" use="optional" />
{/if}
        <attribute name="requestId" type="xsd:string" use="optional" />
        <attribute name="requestId1" type="xsd:string" use="optional" />
        <attribute name="service" type="xsd:string" use="required" />
        <attribute name="sessionId" type="xsd:string" use="optional" />
        <attribute name="status" type="tns:status" use="required" />
        <attribute name="time" type="xsd:float" use="required" />
      </complexType>
{if $hasRbService}
      <complexType name="WsResourceBundle">
        <sequence>
          <element minOccurs="0" maxOccurs="unbounded" name="string" type="tns:WsString" />
        </sequence>
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $hasRetrieveService}
      <complexType name="WsRetrieve">
        <attribute name="key" type="xsd:string" use="required" />
        <attribute name="workflowId" type="xsd:int" use="optional" />
      </complexType>
{/if}
{if $hasSqlService}
      <complexType name="WsRow">
        <sequence>
          <element minOccurs="0" maxOccurs="unbounded" name="col" type="tns:WsCol" />
        </sequence>
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $hasRbService}
      <complexType name="WsString">
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $hasUpdateService}
      <complexType name="WsUpdate">
        <sequence>
          <element minOccurs="1" maxOccurs="unbounded" name="param" type="tns:WsParam" />
        </sequence>
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $hasCreateService || $hasDeleteService || $hasUpdateService}
      <complexType name="WsValidationErrors">
        <sequence>
          <element minOccurs="0" maxOccurs="unbounded" name="error" type="tns:WsError" />
        </sequence>
        <attribute name="key" type="xsd:string" use="required" />
      </complexType>
{/if}
{if $usesFiles}
      <complexType name="SRA_File">
        <attribute name="name" type="xsd:string" use="required" />
        <attribute name="size" type="xsd:float"  use="required" />
        <attribute name="type" type="xsd:string" use="required" />
        <attribute name="uri"  type="xsd:string" use="required" />
      </complexType>
{/if}
{foreach from=$entities item=data}
{$data}
{/foreach}
{if $hasEntityService}
      <simpleType name="action">
        <restriction base="xsd:string">
          <enumeration value="create" />
          <enumeration value="delete" />
          <enumeration value="retrieve" />
          <enumeration value="update" />
        </restriction>
      </simpleType>
{/if}
{if $hasRetrieveService}
      <simpleType name="attrType">
        <restriction base="xsd:string">
          <enumeration value="attr" />
          <enumeration value="constant" />
          <enumeration value="get" />
          <enumeration value="getAttr" />
          <enumeration value="global" />
          <enumeration value="post" />
          <enumeration value="session" />
        </restriction>
      </simpleType>
      <simpleType name="connective">
        <restriction base="xsd:string">
          <enumeration value="and" />
          <enumeration value="or" />
        </restriction>
      </simpleType>
{/if}
{if $unfixedFormat}
      <simpleType name="format">
        <restriction base="xsd:string">
{if $formatJson}
          <enumeration value="json" />
{/if}
{if $formatRaw}
          <enumeration value="raw" />
{/if}
{if $formatXml}
          <enumeration value="xml" />
{/if}
        </restriction>
      </simpleType>
{/if}
      <simpleType name="status">
        <restriction base="xsd:string">
          <enumeration value="authentication-failed" />
{if !$skipAppId}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_APP}" />
{/if}
{if $hasCreateService || $hasDeleteService || $hasUpdateService}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_ATTRS}" />
{/if}
{if !$unfixedFormat}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_FORMAT}" />
{/if}
{if $hasRetrieveService}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_LIMIT}" />
{/if}
{if $hasRetrieveService || $hasDeleteService || $hasUpdateService}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PK}" />
{/if}
{if $soapRestricted}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_PROTO}" />
{/if}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_REQUEST}" />
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_SERVICE}" />
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_FAILED}" />
{if $hasRetrieveService && (!$hasCreateService || !$hasRetrieveService || !$hasDeleteService || !$hasUpdateService)}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_NOT_ALLOWED}" />
{/if}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_RESULTS_NOT_AVAILABLE}" />
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_TIMEOUT}" />
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_SUCCESS}" />
{if $hasCreateService || $hasDeleteService || $hasUpdateService}
          <enumeration value="{$smarty.const.SRA_WS_REQUEST_STATUS_INVALID_INPUT}" />
{/if}
        </restriction>
      </simpleType>
{if $hasRetrieveService || $hasCreateService || $hasMethodService || $hasUpdateService}
      <simpleType name="valueType">
        <restriction base="xsd:string">
          <enumeration value="get" />
          <enumeration value="global" />
          <enumeration value="post" />
          <enumeration value="session" />
        </restriction>
      </simpleType>
{/if}
    </schema>
  </types>
  
  <message name="request">
    <part name="request" type="tns:WsRequest" />
  </message>
  <message name="response">
    <part name="response" type="tns:WsResponse" />
  </message>
   
  <portType name="{$Controller->getCurrentAppId()}Rpc">
{foreach from=$services item=service}{if $service->_soap && $service->isPublic()}
    <operation name="{$service->_id}">
       <input message="tns:request"/>
       <output message="tns:response"/>
    </operation>
{/if}{/foreach}
{if !$serviceSpecific}
    <operation name="{$smarty.const.SRA_WS_GATEWAY_NOOP}">
       <input message="tns:request"/>
       <output message="tns:response"/>
    </operation>
{/if}
  </portType>
   
  <binding name="{$Controller->getCurrentAppId()}Binding11" type="tns:{$Controller->getCurrentAppId()}Rpc">
    <soap11:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
{foreach from=$services item=service}{if $service->_soap && ($serviceSpecific || $service->isPublic())}
    <operation name="{$service->_id}">
       <soap11:operation soapAction="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}{if !$skipAppId}{if $rewrite}/{$Controller->getCurrentAppId()}{else}?ws-app={$Controller->getCurrentAppId()}{/if}{/if}" />
       <input>
          <soap11:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </input>
       <output>
          <soap11:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </output>
    </operation>
{/if}{/foreach}
{if !$serviceSpecific}
    <operation name="{$smarty.const.SRA_WS_GATEWAY_NOOP}">
       <soap11:operation soapAction="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}{if !$skipAppId}{if $rewrite}/{$Controller->getCurrentAppId()}{else}?ws-app={$Controller->getCurrentAppId()}{/if}{/if}" />
       <input>
          <soap11:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </input>
       <output>
          <soap11:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </output>
    </operation>
{/if}
  </binding>
  <binding name="{$Controller->getCurrentAppId()}Binding12" type="tns:{$Controller->getCurrentAppId()}Rpc">
    <soap12:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
{foreach from=$services item=service}{if $service->_soap && ($serviceSpecific || $service->isPublic())}
    <operation name="{$service->_id}">
       <soap12:operation/>
       <input>
          <soap12:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </input>
       <output>
          <soap12:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </output>
    </operation>
{/if}{/foreach}
{if !$serviceSpecific}
    <operation name="{$smarty.const.SRA_WS_GATEWAY_NOOP}">
       <soap12:operation/>
       <input>
          <soap12:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </input>
       <output>
          <soap12:body use="literal" namespace="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}"/>
       </output>
    </operation>
{/if}
  </binding>
  
  <service name="{$Controller->getCurrentAppId()}Svc">
    <port name="{$Controller->getCurrentAppId()}Port11" binding="tns:{$Controller->getCurrentAppId()}Binding11">
       <soap11:address location="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}{if $rewrite}{if !$skipAppId}/{$Controller->getCurrentAppId()}{/if}/11{else}{if !$skipAppId}?ws-app={$Controller->getCurrentAppId()}&amp;{else}?{/if}ws-version=11{/if}"/>
    </port>
    <port name="{$Controller->getCurrentAppId()}Port12" binding="tns:{$Controller->getCurrentAppId()}Binding12">
       <soap12:address location="{$Controller->getServerUri()}{$Template->getWsGatewayUri()}{if !$skipAppId}{if $rewrite}/{$Controller->getCurrentAppId()}{else}?ws-app={$Controller->getCurrentAppId()}{/if}{/if}"/>
    </port>
  </service>
</definitions>
