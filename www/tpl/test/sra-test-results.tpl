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
*}

{* 
Used as the default template for displaying SRA_TestCase results. For more 
information, see lib/util/SRA_TestCase.php
*}
{$border}
{$resources->getString('run-tests.text.testResults')} {$class}:
{$border}
{$methodStr|string_format:$methodLen}{$lineStr|string_format:$lineLen}{$assertionStr|string_format:$assertionLen}{$resultStr|string_format:$resultLen}{$dataTypeStr|string_format:$dataTypeLen}{if $valueStr}{$valueStr|string_format:$valueLen}{/if}{$msgStr|string_format:$msgLen}
{$border}
{foreach from=$results key=method item=result}
{foreach from=$result key=line item=data}
{$method|string_format:$methodLen}{$line|string_format:$lineLen}{$data.method|string_format:$assertionLen}{if $data.passed}{$passedStr|string_format:$resultLen}{else}{$failedStr|string_format:$resultLen}{/if}{if $data.class}{$data.class|string_format:$dataTypeLen}{else}{$data.type|string_format:$dataTypeLen}{/if}{if $data.class}{assign var=temp value=''}{else}{assign var=temp value=$data.value}{if $data.expected && !$data.passed}{assign var=value value=$temp|cat:' (expected: '|cat:$data.expected|:cat')'}{/if}{/if}{if $valueStr}{$temp|string_format:$valueLen}{/if}{if $data.msg}{$data.msg}{/if}

{/foreach}
{/foreach}


