# sierra-php PHP Framework

This is a framework I created initially in 2001, in the days of PHP 4 and prior
to the advent of the many great PHP frameworks in existence today. I've 
continued to develop it through the years, but I'm no longer actively supporting 
it.

## Links
* [API Docs](http://api.sierra-php.org)
* [PHP Architect Magazine Article - June 2009](http://api.sierra-php.org/php-architect-overview-article-june-2009.pdf)

## Description
*sierra-php* is a PHP 4 and 5 compatible framework. It provides a foundation of 
reusable object-oriented code for Linux based PHP software.

## Quickstart
        git pull https://github.com/cloudharmony/sierra-php.git
        php sierra-php/bin/sra-quick-install.php
        php sierra-php/bin/sra-installer.php

## Directory Structure
Applications are installed in `sierra-php/app` by default. Each application
directory contains the following sub-directories (some are optional)

* *bin*: command line scripts
* *etc*: configuration related files including the mandatory `app-config.xml`
* *etc/l10n*: localization properties files including
* *lib*: PHP classes
* *lib/model*: Generated model files (entity classes and DAOs)
* *www*: Web related files
* *www/html*: Web visible files - set this as your document root
* *www/tpl*: Smarty templates

## Features
* **DRY**: built specifically to eliminate redundant code and tasks, significantly increase productivity, and decrease supported codebase. For example, how many times have you written a login screen or validated an email address or written a select or update query... with sierra-php those redundant tasks are replaced with a few lines of configuration like <restrict-access match="admin.php"> or <attribute name="email" depends="email" /> so you can focus on implementing your business logic quickly and effectively
* **O/R mapping**: provides an xml-based declarative method for defining the application's object model. The framework utilizes the object model to automatically generate the data access and value objects (DAOs and VOs) used to provide CRUD (create, read, update, delete) functionality for that object model. Additionally, the framework will automatically generate the DDL necessary to create and update the underlying RDBMS schema and can be optionally configured to automatically invoke that DDL against the RDBMS whenever changes to the object model are made. The object model also allows application developers to define entity and attribute views (input forms, read-only views, PDF-based views, etc.), implement validation logic, impose aspect oriented (AOP) modifications of the generated classes, and much more.
* **Web Services**: allows exposure of model and custom functionality via SOAP and REST web services. These web services can be used to perform standard CRUD logic within the model as well as other custom functionality including database queries, printing, etc. Web service I/O supports both JSON and XML. Additionally, full API documentation, invocation samples, and WSDLs are automatically generated
* **Authentication**: provides a declarative approach for defining HTTP-based application authentication and access restrictions. LDAP, database and OS authentication methods are supported as well as a combinations of those methods.
* **Configuration management**: provides an xml-based declarative method for defining configurable software parameters such as database connections, access restrictions, authentication, task scheduling, localization, logging, and custom parameters
* **UI Templates**: uses the Smarty template engine for separation of presentation from other application logic
* **Logging**: includes a logger and the ability to define framework, application, authentication and other custom log files
* **Database abstraction**: supports abstraction for MySQL, PostgreSQL, and MsSQL database servers including in conjunction with the O/R mapping automatic schema synchronization
* **Localization**: using language specific properties files and automatically detected browser locale preferences, sierra-php can localize applications by displaying the language strings from the properties files that most closely approach the users' locale preferences. Additional localization features includes time zones, address formats, currencies and number formats, and date formatting
* **Workflow management**: allows developers to implement complex workflow processes within applications utlizing an xml-based declarative design approach
* **Utility functionality**: provides a variety of utility classes for file management, email messaging, xml parsing, and much more
* **Out-of-the-box UI templates**: provides configurable Smarty-based templates for creating object and attribute views including complex tables, form inputs, AJAX-based tooltips, click-in fields, files, images, arrays, pagination and more
* **PHP Console**: provides a terminal console for interracting with the framework, applications, and PHP using a command-line interface. This is useful for testing and maintenance procedures that might otherwise require creation of temporary scripts. It includes bash-like history toggling, and class-introspection-based tab completion
* **Multiple-application support**: each instance of the framework can support zero or more application deployments. Each application can utilize its own custom configurations and inherit from global configurations
* **Installer**: the framework includes a command-line installer used for the initial framework configuration, database configurations, updates from subversion, and installation/updates/configuration of sierra-php applications including Apache configuration
