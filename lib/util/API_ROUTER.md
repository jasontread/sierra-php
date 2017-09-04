Sierra APIs are defined, managed and deployed using a PHP class called an `API Controller`. This class uses Javadoc style commands and annotations to describe the API and is deployed using the `CH_ApiRouter`:

    ch_include('CH_ApiRouter.php');
    if ($router =& CH_ApiRouter::create()) $router->route();

## Current Version Redirection
APIs may be versioned. Use the following to enable dynamic version redirection (from a non-versioned base):

    ch_include('CH_ApiRouter.php');
    if ($router =& CH_ApiRouter::create()) $router->redirect($suffix);

The `$suffix` parameter is an optional pre-release identifier (or an array of such identifiers) that may be considered when evaluating for versions eligible for redirection. This parameter is optional and if not specified, only production level versions (those without a suffix) will be used.

In order to use version redirection, the script that initiates it should reside in a the directory containing versioned API sub-directories named as: `v[version][suffix]`:

* `[version]` is a numeric derived, incrementing identifier such as _1.1_, _1.0.1_, _2.0_, etc. - redirection will occur to the directory representing the highest eligible version
* `[suffix]` is an pre-release version identifier such as _b_, _beta_, _a_, _alpha_, _rc1_, etc. These must begin with an alphabetic character directly following `[version]`. For example: _1.0.1a_, _2.0rc1_. Redirects to pre-release versions only occurs if that suffix is equal to or contained by the `$suffix` parameter

## API Controllers
When obtaining a reference to a `CH_ApiRouter` instance, the path to the API controller to deploy may be specified. Optionally, the controller can be defined by convention using a PHP source file with `Controller` in the file name, located in the same directory as the script handling the http request. This file must contain a class definition and API method(s). If the path or class is not valid, `CH_ApiRouter::create` method returns NULL (check error logs for reason). 

### API Controller Instantiation
API controllers are instantiated by `CH_ApiRouter` for every request using either the `new` operator, or an optional `@singleton` method. A reference to the router will be provided as the first parameter in the method instantiation (e.g. `new MyApiController($router)` or `MyApiController::newInstance($router)`).

## Global API Description and Annotations
The description and settings for the API are defined in the class-level comment block (the comment beginning with `/**` and ending with `*/` located above `class MyApiController {`). This block should contain a description and setting annotations with the format `@[setting] [value]`.

### Global API Annotations
The following global API annotations may be defined:

* `@api` [value] the name of the API - if not specified, the application ID will be used. This value will be used for the response header `sierra-api`
* `@api-key-validation` [function name] optional name of a global function that may be used to validate API keys prior to method invocation. The function should have this signature `function_name($apiKey, $api, $method, $params): boolean`. If the function returns TRUE, the call will be assumed to be valid. If it returns FALSE, it will be assumed to be invalid resulting in a 401 Unauthorized response. Only methods flagged with `@api-key-validate` will be validated
* `@api-key-name` [name] the name of the request header, query, form variable or cookie value where the API key should be accessed (in that order) in order to invoke the `@api-key-validation` function. If no value is found, and an attempt to invoke an `@api-key-validate` method is made, a 400 Bad Request response will result. The default value for this configuration is `api-key`. Multiple values may be specified each separated by a comma
* `@api-key-validate` if set, all API methods will be flagged for API key validation - may be overridden at the method level
* `@api-version` [value] the API version - if not specified, and the script instantiating the API router exists in a directory that conforms to the version naming convention above, the name of that directory will be used. This value will be used for the response header `sierra-api-version`
* `@async` set this flag to enable asynchronous execution of all API methods. Asynchronous execution is forked from a web request on the command line. Duplicate concurrent requests are also merged to a single process
* `@beautify` if set, json responses will be beautified by default (rendered in human readable format) and the content-type for those responses will be `text/json` instead of `application/json`
* `@beautify-round` [value] used to specify rounding precision when beautifying json responses - this is necessary due to the python module used which inadvertently adds floating point digits. The default value for this setting is 2
* `@bool-false` [value] the default value to use to represent boolean false - default is _false_
* `@bool-true` [value] the default value to use to represent boolean true - default is _true_
* `@cache-scope` [type] scope for local API method response cache - the following options are supported:
  * `cookie` [name] include method parameters and the cookie value associated with [name] (e.g. `@cache-scope cookie jsessionid`) - separate multiple cookie values with commas 
  * `none` do not cache locally - `cache-ttl` will be used for http cache response headers only
  * `params` include method parameters in the cache key (this is the default behavior)
  * `request` [name] include method parameters and the request (get or post) value associated with [name] (e.g. `@cache-scope request apiToken`) - separate multiple cookie values with commas 
* `@cache-ttl` [number] number of seconds to cache API method responses - the cache setting determines how/if the router performs local caching as well as use of http cache response headers (e.g. `cache-control`). This setting may be overridden by API methods. Use a value of `0` when API responses should not be cached locally or remotely (e.g. `Cache-Control: no-cache`). To suppress cache-control response headers, do not assign any value to this annotation - this is the default behavior
* `@cache-ttl-doc` [number] number of seconds to allow generated API docs to be cached - default is 1 hour unless otherwise specified using this annotation
* `@doc-swagger` [uri] URI that will trigger generation of a _Swagger API Declaration_ [as defined here](https://github.com/wordnik/swagger-spec/blob/master/versions/1.2.md#52-api-declaration). The default URI trigger is `/api-docs`. If annotation is used with no [uri] value, auto documentation will be disabled
* `@doc-mashape` [uri] URI that will trigger generation of a _Mashape_ XML API description [as defined here](https://www.mashape.com/docs/describe/overview-xml). The default URI trigger is `/mashape.xml`. If annotation is used with no [uri] value, auto documentation will be disabled
* `@doc-tag` [value] optional tag(s) to associate with API documentation endpoints - multiple tags may be defined - the first will be the primary
* `@entity-example` [entity] [example] Example JSON for this entity. This value is used in API documentation. It may be a string or a path to a file or the name of a method for the model or DAO classes. If the latter, the method may return either an instance of that entity, or a JSON string
* `@entity-exclude` [entity] [attr] This setting may be used to designate entity attributes that should be excluded from method responses and API documentation - multiple excludes may be defined
* `@entity-include` [entity] [attr] This setting may be used to designate entity attributes that should be included in method responses and API documentation - multiple includes may be used - all other attributes will be excluded
* `@entity-ref` [entity] [ref] if any of the API methods return entities, this annotation may be used to redefine how those entities are named in API documentation
* `@format-date` [format] format string to use to convert date response values - if not specified, the application format will be used
* `@format-time` [format] format string to use to convert time response values - if not specified the application format will be used
* `@header` [header] [value] http headers to add or to remove (prefix headers to remove with `-`) for every API method call - multiple headers may be defined
* `@max-execution-time` [value] the max execution time in seconds to permit for methods calls and documentation generation
* `@memory-limit` [value] the memory limit to permit for method calls and documentation generation (e.g. `32m`)
* `@name` [value] the name for this API (used in documentation) - if not specified, the class name will be used
* `@singleton` [method] if the controller should be instantiated using a singleton method (as opposed to the `new` operator, this setting may specify the name of a static class method that should be used
* `@skip-callback-param` don't show the callback parameter in method documentation
* `@status` [condition] [code] [description] the http status codes to use under various conditions. [description] is used in generated API documentation. The following conditions are supported:
  * `error` status code for an error conditions not already covered - default is `500 Internal Server Error`
  * `exception` status code for an uncaught exception (API method invocations are wrapped in try/catch) - default is `500 Internal Server Error`
  * `invalid` status code when required API method parameters are missing, or if parameters fail validation - default is `400 Bad Request`. This status code can also be generated by an API method if the return value for that method is a string in the format `invalid-[param name]-[optional description]` where `[param name]` is the name of one of the method parameters. If this occurs, the `invalid` response code will be used and the response header `sierra-api-invalid` will be set using the value `[param name]-[optional description]`
  * `not-found` status code when an unrecognized API method is encountered - default is `404 Not Found`
  * `null` status code when an API method that is supposed to return a value, returns NULL - default is `503 Service Unavailable`
  * `unauthorized` status code when request is denied due to failure of `api-key-validation` - default is `401 Unauthorized` if API key is present, `400 Bad Request` if it is not
  * `ok` status code when a request is successful - default is `200 OK`
* `@status-doc-skip` [condition] suppresses status related documentation for a specific condition or status code - multiple annotations may be specified - by default, all status conditions are documented (using method or global codes and descriptions)
* `@swagger-auth` [name] for swagger generated API docs - identifies the name of an Authorization Object in the Resource Listing that all methods must adhere to. This tag may also be overridden at the method level
* `@tag` [value] optional tag(s) to associate with this API - multiple tags may be defined - the first will be the primary
* `@url` [value] the base URL for the API - all API method routes are relative to this URL. If not specified, the URL is determined from the current request

### Class API Sample
The following is a sample of an API class comment section with annotations:

    /**
     * This API provides access to some data
     * @api testapi
     * @api-version 1.0
     * @cache-ttl 0
     * @header -X-Powered-By
     * @singleton newInstance
     * @url http://api.testapi.com/v1
     */
    class MyApiController {


## Method API Description and Annotations
Each controller may implement one or more API methods. An API method is defined using a class function. The description and settings are defined in the function comment section (the comment block beginning with `/**` and ending with `*/` located directly above the function signature - `function myApiMethod {`). All public methods, excluding the constructor are assumed to be API methods (unless the `@skip-api` annotation used)

### Method API Annotations
Methods automatically inherit global API settings. The following method annotations are supported (may override the same global setting):

* `@api-key-validate` if set, access to this method will only be allowed if the `@api-key-validation` function returns TRUE (otherwise a 401 Unauthorized or 400 Bad Request response will result)
* `@api-key-validate-skip` set this flag to skip API key validation where `@api-key-validate` has been set at the global scope
* `@async` set this flag to enable asynchronous execution of this API method. Asynchronous execution is forked from a web request on the command line. Duplicate concurrent requests are also merged to a single process
* `@beautify` if set, json responses will be beautified by default
* `@beautify-round` [value] used to specify rounding precision when beautifying json responses - this is necessary due to the python module used which inadvertently adds floating point digits
* `@bool-false` [value] the default string to use to represent boolean false
* `@bool-true` [value] the default string to use to represent boolean true
* `@cache-scope` [value] cache scope for this API method
* `@cache-ttl` [number] number of seconds to cache API method responses
* `@content-type-other` [value] content-type to use for simple/primitive type API method responses - if not specified, `text/plain` will be used
* `@doc-hidden` don't show this method in API docs
* `@error` [response] [code] [description] defines an exception case to trigger by a certain method response value - if the method response value is exactly equal (===) to [response], the http response code [code] will be used. If [response] is a string, it should be enclosed in quotes (e.g. `@error 'bad_response' 503 this is really bad`). [description] is used in API documentation
* `@format-date` format string to use to convert date response values
* `@format-time` format string to use to convert time response values
* `@header` [header] [value] http headers to add or to remove (prefix headers to remove with `-`) for this API method call - multiple headers may be defined
* `@http-method` [value] [nickname] / [summary] / [note] an http method to limit use of this this method to - if not specified, only `GET` will be used. Valid http methods are `GET`, `POST`, `PUT` and `DELETE`. Can be specified multiple times if a method should support multiple methods. [nickname], [summary] and [note] are optional descriptions for that specific HTTP method. [nickname] should be an alphanumeric value (underscores also allowed), [summary] must be 120 characters or less, [note] can be longer. In Swagger documentation [nickname] is required. If not specified, the http method followed by method name will be used (spaces replaced with underscores)
* `@max-execution-time` [value] the max execution time in seconds to permit for this method
* `@memory-limit` [value] the memory limit to permit for this method (e.g. `32m`)
* `@name` [value] the name to use for this method in API documentation - if not specified, the method name (minus placeholders) will be used
* `@param` [type] [name] [description] an API method parameter description including datatype, name and description. Parameters become optional if a default value is assigned to them in the signature (e.g. `function myApiMethod($param1=TRUE)`). Use `NULL` for optional parameters with no default value (e.g. `function myApiMethod($param1=NULL)`). A parameter with no default value becomes required in invocation validation and API documentation. Parameter datatypes are limited to the following:
  * `string`
  * `float`
  * `int` or `integer`
  * `bool` or `boolean`
  * `date` a hybrid of the `string` type - parameter values will be validated and converted to a unix timestamp
  * `timestamp` a hybrid of the `string` type - parameter values will be validated and converted to a unix timestamp
* `@param-example` [name] [example value] an example value for the parameter - used in API documentation
* `@param-options` [name] [options] a list of options for a parameter. [options] may be either a comma separated list, path to a properties file, or a PHP code snippet that sets an $options variable (code snippet should end with ;). These are used for parameter validation and API documentation
* `@param-regex` [name] [regex] an optional regular expression to use to validate a parameter
* `@param-no-validate` [name] if set, automatic value validation for this parameter will be skipped
* `@return` [type] the data type returned by this method - this may one of the parameter datatypes listed above or the name of an application entity. Arrays are supported using a `[]` suffix. If no `@return` annotation is defined, or `@return void` is used, the method response will be ignored. If [type] is an entity, that entity's corresponding class and attribute names and associated help content will be used in generated API documentation. Entity attribute example values can be defined in the help string using the format `example: '[value]'`
* `@return-csv-method` [method] optional name of a method that will produce a CSV formatted string when the `return` type is an application entity. CSV output format is used when the http invocation includes a `.csv` suffix or the `csv` parameter is true. This method should accept 1 argument `$header`, a boolean value determining whether or not a header should be included in the CSV string returned. This method may use the global variables `$_utilDateFormat` and `$_utilTimeFormat` for date and time format strings
* `@return-example` example return value for this method. This value is used in API documentation. It may be a string or a path to a file
* `@route` [value] the URI route for this method - if not defined, the method name will be used. The route is relative to the API `@url`. Routes may contain `param` placeholders using the format `{[param name]}`. For example, the route `@route users/{id}` can be invoked using `users/123` or `users?id=123`. Use of placeholds requires Apache rewrites rules (see below)
* `@set-cookie` [name] if set, the return value will be set to a browser cookie using [name]
* `@skip-api` if set - the method will be ignored
* `@skip-callback-param` don't show the callback parameter in method documentation
* `@status` [condition] [code] [description] overrides default http status codes for given conditions (see documentation above)
* `@status-doc-skip` [condition] suppresses status related documentation for a specific condition for this method
* `@swagger-auth` [name] for swagger generated API docs - identifies the name of an Authorization Object in the Resource Listing that this methods must adhere to. May be used to override the global auth. If the global settings designate authorization (which covers all methods), but this method does not require it, [name] can be set to the value 'none'
* `@tag` [value] optional tag(s) to associate with this API method - multiple tags may be defined - the first will be the primary

### Method API Sample
The following is a sample of an API method comment section with annotations:

    /**
     * Returns a list of values
     * @param string $type The type of list to return
     * @param-example $type food
     * @param-options $type value-types
     * @name List Values
     * @return ValueList
     * @route list/{type}
     * @tag meta
     */
    public function _list($type='food') {

## Using Route Placeholders
In order to use placeholders in routes, the Apache RewriteEngine should be enabled with rules for up to the maximum depth of such placeholders. For example, the following rewrite rules (placed in `.htaccess`) support up to 10 placeholders:

    # Enable support for dynamic API method param placeholders
    RewriteEngine On
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*)/(.*) index.php?%{QUERY_STRING}
    RewriteRule ^(.*) index.php?%{QUERY_STRING}

## Custom Response Headers
Unless explicitly removed by `header-remove`, the following custom response headers are provided for each request:

* `sierra-api` the name of the API
* `sierra-api-version` the API version
* `sierra-api-cached` true if the response was retrieved from cached
* `sierra-api-condition` the response condition
* `sierra-api-doc` the documentation generated (if applicable)
* `sierra-api-invalid` if a parameter argument fails validation, this will be set to [param name]-[validation failed]. This response header may also be generated by an API method if the return value for that method is a string in the format `invalid-[param name]-[optional description]` where `[param name]` is the name of one of the method parameters. In this scenario, the value of this response header will be `[param name]-[optional description]`
* `sierra-api-method` the http method (if applicable)
* `sierra-api-runtime` the API method runtime
* `sierra-api-route` the fixed portion of the method route (i.e. no placeholders - if applicable)

## Global API Method Parameters
API method invocation supports both method and global parameters. Method parameters are defines using the `@param` annotation in the method comment block, while global parameters are supported for any method call. The following is a list of the global API parameters supported. These are automatically included in generated API documentation.

* `format-date` format string to use to represent dates - overrides default formatting - formatting options are [documented here](http://www.php.net/manual/en/function.date.php)  (this parameter is only documented if the method response contains a `date` or `time` attribute)
* `callback` the name of a callback function the response should be padded/wrapped by - this parameter is automatically appended by [jQuery](http://jquery.org)
* `use-date-object` when set, dates and timestamps will be rendered as javascript `Date` objects instead of strings (this parameter is only documented if the method response contains a `date` or `time` attribute)