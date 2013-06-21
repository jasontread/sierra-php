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
Renders an html display header

PARAMS (type="sra-html-header"):
Key                   Default     Value     Desc
																						
renderHtmlOpen        0            (0|1)    whether or not to render the opening 
                                            html, head, title, and body elements
																						
docType               http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd
                                            if "renderHtmlOpen" is true, specifies 
																						the html doc type to apply to the 
																						header
																						
title                 '$getLabel()'          a valid VO::parseString($str) $str 
                                            value to use in rendering the title 
																						if "renderHtmlOpen" is True
																						
jscriptIncl																	a javascript file that should be 
                                            included in the html head
																						
cssIncl   																	a css file that should be included 
                                            in the html head
																						
bodyClass                                   an optional class attribute to assign to 
                                            the open body element if "renderHtmlOpen" 
																						is true
																						
bodyId                                      an optional id attribute to assign to 
                                            the open body element if "renderHtmlOpen" 
																						is true
																						
bodyAttrs                                   an optional string containing additional 
                                            html body element attributes

preTpl                                      a template to display before the header 
                                            is rendered (but after the html open 
																						tags if applicable)
																						
postTpl                                     a template to display after the header 
                                            is rendered
																						
label																				an optional header label that will be 
                                            placed after the html header (after 
																						the preTpl if applicable). This label 
																						will be passed to VO::parseString($str) 
																						for the entity, followed by 
																						SRA_ResourceBundle::getString($key) for 
																						the application resource bundle
																						
labelCss                                    optional css to apply to the label 
                                            using a font tag
																						
preLabel																		html/text to display before the label

postLabel 																	html/text to display after the label

enclElem                          [html el] an optional html element tag that should 
                                            be used to enclose this header. 
																						for example, to enclose the header 
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
{assign var="myParams" value=$params->getTypeSubset('sra-html-header')}
{if $myParams->getParam('renderHtmlOpen')  eq '1'}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"{$myParams->getParam('docType', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd')}">
<html>
	<head>
		<title>{assign var=tmp value='{ldelim}$getLabel(){rdelim}'}{$entity->parseString($myParams->getParam('title', $tmp))}</title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  	{if $myParams->getParam('jscriptIncl')}<script src="{$myParams->getParam('jscriptIncl')}" type="text/javascript"></script>{/if}
  	{if $myParams->getParam('cssIncl')}<link href="{$myParams->getParam('cssIncl')}" rel="stylesheet" type="text/css" />{/if}
	</head>
<body{if $myParams->getParam('bodyClass')} class="{$myParams->getParam('bodyClass')}"{/if}{if $myParams->getParam('bodyId')} id="{$myParams->getParam('bodyId')}"{/if} {$myParams->getParam('bodyAttrs', '')}>
{/if}

{if $myParams->getParam('preTpl')}{include file=$myParams->getParam('preTpl')}{/if}
{if $myParams->getParam('enclElem')}
{assign var="cycleName" value=$myParams->getParam('enclElemCycle', $myParams->getParam('enclElemClass'))}
<{$myParams->getParam('enclElem')}{if $myParams->getParam('enclElemClass')} {if $myParams->getParam('enclElemClassTag')}{$myParams->getParam('enclElemClassTag')}{else}class{/if}="{cycle name=$cycleName values=$myParams->getParam('enclElemClass')}"{/if}>
{/if}

{if $myParams->getParam('label')}
{$myParams->getParam('preLabel', '')}
{if $myParams->getParam('labelCss')}<font class="{$myParams->getParam('labelCss')}">{/if}
{assign var=mlabel value=$myParams->getParam('label')}
{$resources->getString($entity->parseString($mlabel))}
{if $myParams->getParam('labelCss')}</font>{/if}
{$myParams->getParam('postLabel', '')}
{/if}

{if $myParams->getParam('enclElem')}</{$myParams->getParam('enclElem')}>{/if}

{if $myParams->getParam('postTpl')}{include file=$myParams->getParam('postTpl')}{/if}

