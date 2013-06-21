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
 
 this template is displayed whenever an attempt to access an application is made 
 while the SRA_EntityModeler is currently building. it can be overriden by the 
 application by creating an app (in 'www/tpl/') template using the same name 
 (sra-model-wait.tpl). additionally, the resource strings can be overriden in 
 the application resource bundle
*}
<!-- lock file: {$lockFile} -->
<html>
  <head>
    <title>{$resources->getString('model.wait.title')}</title>
  </head>
  <body style="background-color: #eee; font-family: arial, helvetica; padding: 20px">
    <h1>{$resources->getString('model.wait.title')}</h1>
    <p>{$resources->getString('model.wait.body')}</p>
    <p style="font-size: smaller; margin-bottom: 30px;"><a href="javascript:{ldelim}{rdelim}" onclick="var div = document.getElementById('description'); div.style.visibility = div.style.visibility=='visible' ? 'hidden' : 'visible'; this.innerHTML = div.style.visibility=='visible' ? '{$resources->getString('model.wait.hideDescription')}' : '{$resources->getString('model.wait.showDescription')}'">{$resources->getString('model.wait.showDescription')}</a></p>
    <p><span id="description" style="border: 1px dotted blue; background: #fff; font-family: courier; font-size: smaller; padding: 10px; visibility: hidden">{$resources->getString('model.wait.lockFile')} {$entityModel}</span></p>
  </body>
</html>

