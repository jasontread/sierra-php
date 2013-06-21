<?php
// {{{ Header
/*
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
 */
// }}}

// {{{ Imports
require_once(dirname(dirname(realpath(__FILE__))) . '/core/SRA_Controller.php');
require_once('model/SRA_WSGateway.php');
// }}}

/**
 * this script is the controller for the model-based web services functionality. 
 * it processes a GET/POST parameter-based or an xml based request and returns 
 * a raw or json/xml formatted response. 
 *
 * the request processes the following dynamically bound GET/POST parameters:
 *
 *   ws1, ws2, ws3, ws4 and ws5: dynamic binding variables where the wsN 
 *   parameters can be the following values:
 *     ws1: ws-app, ws-request-xml
 *     ws2: ws, ws-doc, ws-soap-version
 *     ws3: ws-action, ws-doc, ws-format, ws-soap-version
 *     ws4: ws-id, ws-format, ws-soap-version
 *     ws5: ws-format, ws-soap-version
 *   and the possible parameter combinations are the following:
 *     ws-app=&ws-request-xml=
 *       invoke a web service request using the request xml "ws-request-xml"
 *     ws-app=&ws=
 *       invoke web service "ws"
 *     ws-app=&ws=&ws-action=
 *       invoke action "ws-action" of web service "ws"
 *     ws-app=&ws=&ws-action=&ws-id=
 *       invoke action "ws-action" of web service "ws" for entity with primary 
 *       key "ws-id"
 *     ws-app=&ws=&ws-action=&ws-format=
 *       invoke action "ws-action" of web service "ws" and output results in 
 *       "ws-format"
 *     ws-app=&ws=&ws-action=&ws-id=&ws-format=
 *       invoke action "ws-action" of web service "ws" for entity with primary 
 *       key "ws-id" and output results in "ws-format"
 *     ws-app=&ws-doc=
 *       show documentation for all app web services
 *     ws-app=&ws=&ws-doc=
 *       show documentation for web service "ws"
 *
 * the following parameters correspond directly with elements and attributes 
 * defined in ws-request.dtd. those correlations are identified in parenthesis. 
 * review the dtd documentation for those elements/attributes for more 
 * information:
 *   ws                          the name of the web service (ws-request/key)
 *   ws-action                   (create|delete|update) for entity web 
 *                               services only - the action to invoke
 *   ws-app                      the identifier of the app (ws-request/app)
 *   ws-asynchronous             (1|0) specifies whether or not the output of 
 *                               this request should be buffered to a session 
 *                               variable instead of output to the client. when 
 *                               true, ws-use-sessions will implicitely be true
 *   ws-constraintM-*            used to specify constraint groups, where M is 
 *                               unique to the group M (ws-constraint-group/key)
 *                               M is the synonymous as "key" in ws-request.dtd
 *   ws-constraintM-attrN        attribute N for constraint group M 
 *                               (ws-constraint/app)
 *   ws-constraintM-connective   (and|or) constraint group join connective 
 *                               (ws-constraint-group/connective)
 *   ws-constraintM-operatorN    the operator between attribute N and value N in
 *                               constraint group M (ws-constraint/operator)
 *   ws-constraintM-typeN        the type for attribute N and constraint group M
 *                               (ws-constraint/attr-type)
 *   ws-constraintM-valueN       the value for attribute N and constraint group 
 *                               M (ws-constraint/value)
 *   ws-constraintM-value-typeN  the value type for attribute N and constraint 
 *                               group M (ws-constraint/value-type)
 *   ws-date-format              The formatting string to use in the response 
 *                               for date values. if not specified and the 
 *                               'ws-time-format' parameter below is specified, 
 *                               'ws-time-format' will be used for both date and 
 *                               time values. If neither are specified, the 
 *                               default application date format will be use
 *   ws-doc                      (api|wsdl) this parameter is not part of 
 *                               ws-request.dtd. it causes this script to 
 *                               generate web services documentation instead of 
 *                               processing a request. the two forms of 
 *                               documentation it can generate are api and wsdl 
 *                               where api is human readable and wsdl is machine 
 *                               readable for soap web services. this parameter 
 *                               may be used in conjunction with "ws" to display 
 *                               only the api/wsdl documentation for that 
 *                               service
 *   ws-exclude                  space or comma separated list of attributes to 
 *                               exclude (ws-request/exclude-attrs)
 *   ws-format                   (json|xml|raw) the desired response format 
 *                               (ws-request/format)
 *   ws-id                       the primary key of the entity to retrieve, 
 *                               delete or update (retrieve/key), (delete/key) 
 *                               or (update/key)
 *   ws-include                  space or comma separated list of attributes to 
 *                               include (ws-request/include-attrs)
 *   ws-js-dates                 if true and the "format" is "json", date values 
 *                               will be encoded using the javascript Date 
 *                               constructor
 *   ws-limit                    the result limit for this service request 
 *                               (ws-request/limit)
 *   ws-meta-format              (json|xml|none) the desired response metadata 
 *                               format (ws-request/meta-format)
 *   ws-offset                   the request result set offset
 *                               (ws-request/offset)
 *   ws-paramN                   used to specify attributes for create/update 
 *                               service requests and method parameters for 
 *                               global service requests (ws-param/key)
 *   ws-paramN-value             the value of param N (ws-param/value)
 *   ws-paramN-value-type        the value type of param N (ws-param/value-type)
 *   ws-password                 password for authentication. user and password 
 *                               can also be specified in the http headers. 
 *                               however, if user or password are specified in 
 *                               the get parameters or ws-request-xml, they will 
 *                               override the values in the http headers
 *   ws-query                    used in conjunction with a previous 
 *                               "asynchronous" request in order to query for 
 *                               the results of that previous request 
 *                               (ws-request/query)
 *   ws-request-id               an optional identifier for the service request 
 *                               (ws-request/request-id)
 *   ws-request-id1              a second optional identifier for the service 
 *                               request (ws-request/request-id1)
 *   ws-request-xml              used to specify the request in xml format 
 *                               instead of using GET/POST parameters. this 
 *                               request should be structured in compliance with 
 *                               ws-request.dtd
 *   ws-session-id               the session id for this request. when set, 
 *                               ws-use-sessions will implicitely be true
 *   ws-use-sessions             whether or not to use sessions for this request
 *   ws-user                     user for authentication. user and password can
 *                               also be specified in the http headers. however, 
 *                               if user or password are specified in the get 
 *                               parameters or ws-request-xml, they will 
 *                               override the values in the http headers
 *   ws-validators               an optional space or comma separated list of 
 *                               additional validators to invoke 
 *                               (applies only to entity create/update requests) 
 *                               (ws-request/validator)
 *   ws-soap-version             the soap version... one of the SRA_WS_VERSION_* 
 *                               constant values
 *   ws-time-format              The formatting string to use in the response 
 *                               for time values. If not specified, the default 
 *                               application time format will be used
 *   ws-wf-id                    if the entity is stored within a workflow 
 *                               instance, this attribute may be used to specify 
 *                               the workflow identifier (retrieve/workflow-id)
 *   [ws-param]=[ws-param-value] parameters that are not prefixed with "ws-" and 
 *                               not used by a ws-param or ws-constraint (where 
 *                               type="post" or type="get") will be considered 
 *                               to be ws-param parameters where the parameter 
 *                               name/key is ws-param and the value is 
 *                               ws-param-value
 *   [ws-param]-type             the ws-param-value-type for [ws-param] ("get" 
 *                               and "post" types are not supported)
 *
 * the response will be in 1 of 3 formats per the "ws-format" and 
 * "ws-meta-format" specified in the request and/or service definition (for more 
 * information, review the ws-response.dtd documentation):
 *   ws-format=json       the results will be encoded in json format. for 
 *                        example, a retrieve request that returns an array of 
 *                        entities will be encoded as a json array of objects
 *   ws-format=xml        the results will be encoded in xml format (applies to 
 *                        entity or array of entity results only)
 *   ws-format=raw        the results will be the raw output from the service 
 *                        (applies to global "method" type services only)
 *   ws-meta-format=json  the metadata will be encoded in json format where the 
 *                        keys are the attributes and sub-elements of the 
 *                        "ws-response" in ws-response.dtd
 *   ws-meta-format=xml   response will be formatted in xml in compliance with 
 *                        the ws-response.dtd doctype
 *   ws-meta-format=none  metadata will be excluded from the response (only the 
 *                        results will be returned)
 *   ws-callback          for JSON responses only - wraps the results in a function
 *                        with the name provided by this parameter
 */
// {{{ sra-ws-gateway
global $_wsGateway;

$_wsGateway = new SRA_WSGateway(array_merge($_GET, $_POST));
//sra_error::logerror('start: ' . $_GET['ws2']);
//sra_error::logerror($_GET);
if (SRA_WSGateway::isValid($_wsGateway) && $_wsGateway->process()) {
  // work around for IE https bug with cache control
  if (preg_match('/https/i', SRA_Controller::getServerUri())) {
    header('Cache-Control: private');
    header('Pragma: private');
  }
  ob_start();
  $_wsGateway->printResponse();
  $output = ob_get_contents();
  ob_end_clean();
  print($output);
  //sra_error::logerror('stop: ' . $_GET['ws2']);
}
else {
  $_tpl =& SRA_Controller::getSysTemplate();
  if (isset($_wsGateway->_errorMsg)) { $_tpl->assign('msg', $_wsGateway->_errorMsg); }
  if ($_wsGateway->_isSoap) { header('Content-Type: ' . ($_wsGateway->_request->_soapVersion == SRA_WS_VERSION_1_1 ? 'text/xml' : 'application/soap+xml')); }
  $_tpl->display($_wsGateway->_isSoap ? 'model/sra-soap-response.tpl' : 'model/sra-ws-error.tpl');
}
// }}}
?>
