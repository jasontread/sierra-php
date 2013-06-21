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
Renders an html display footer

PARAMS (type="sra-html-footer"):
Key                   Default     Value     Desc

renderHtmlClose       0            (0|1)    whether or not to render a body and html close

preTpl                                      a template to display before the footer 
                                            is rendered
																						
postTpl                                     a template to display after the footer 
                                            is rendered (but before the html close 
																						is rendered if applicable)

enclElem                          [html el] an optional html element tag that should 
                                            be used to enclose this footer. 
																						for example, to enclose the footer 
																						in a table cell, this param would be 
																						"td" and  the corresponding open and 
																						close <td></td> elements would be used 
																						to enclose the attribute
																						
enclElemClass                               an optional css class that should be 
                                            specified for the enclElem. this may 
																						be a range of values that should be 
																						cycled through, once for each instance 
																						of the enclElem open tag. if this is 
																						the case, the different classes should 
																						be comma separated (e.g. "myclass1,myclass2")
																						
enclElemCycle                               an optional cycle name to apply (if 
                                            enclElemClass cycles are used). this 
																						will uniquely identify the cycle where 
																						other duplicate cycles exist that would 
																						otherwise interfere. otherwise, the 
																						name will be the value specified in 
																						'enclElemClass'
																						
enclElemClassTag      class                 if an enclElem attribute other than 
                                            "class" should be used to express the 
																						"enclElemClass" value, then this 
																						parameter should define that attribute 
																						tag. For example, if instead of alternating 
																						specifying a "class" you wished to bypass 
																						css and just specify a bgcolor, the 
																						value of this parameter would be "bgcolor"
																						
*}
{assign var="myParams" value=$params->getTypeSubset('sra-html-footer')}
{if $myParams->getParam('preTpl')}{include file=$myParams->getParam('preTpl')}{/if}
{if $myParams->getParam('enclElem')}
{assign var="cycleName" value=$myParams->getParam('enclElemCycle', $myParams->getParam('enclElemClass'))}
<{$myParams->getParam('enclElem')}{if $myParams->getParam('enclElemClass')} {if $myParams->getParam('enclElemClassTag')}{$myParams->getParam('enclElemClassTag')}{else}class{/if}="{cycle name=$cycleName values=$myParams->getParam('enclElemClass')}"{/if}>
{/if}

{if $myParams->getParam('enclElem')}</{$myParams->getParam('enclElem')}>{/if}

{if $myParams->getParam('postTpl')}{include file=$myParams->getParam('postTpl')}{/if}

{if $myParams->getParam('renderHtmlClose')  eq '1'}
</body>
</html>
{/if}
