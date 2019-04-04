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
Displays a timestamp selector field consisting of select fields for all of the 
major components of a timestamp (DateTime object in Sierra). the values of those 
fields will be concatenated to form a complete and valid value for the attribute 
when the form is submitted to the server. This template is typically used in 
conjunction with sra-attr.tpl


PARAM KEY DEFINITIONS:
[attr]:       html attribute
[element id]: element identifier. uses the following format ([-[property]] is optional): 
               [element tag][-[property]]-attrs
[cycle]:      cycle identifier - these identifiers are shared accross every 
              output that utilizes this template in a single http request process
[property]:   property of the output. one of the following:
               day:      the timestamp day (default format string: 'j')
							 hour:     the timestamp hour (default format string: 'H')
							 meridiem: the timestamp meridiem (AM/PM) (default format string: 'A')
							 minute:   the timestamp minute (default format string: 'i')
							 month:    the timestamp month (default format string: 'n')
							 second:   the timestamp second (default format string: 's')
							 year:     the timestamp year (default format string: 'Y')
							 
[range]       space separated range designator for a timestamp property. this 
              designator may be specified in the following forms:
							 "N1 N3 N5"   : only options N1, N3 and N5 will be available
               "N1-N3"      : only options >= N1 and <= N3 will be available
							 "N1-N3 N5-N8": combination of the above
							 "-N1"        : the current property value to N1
							 "N1-"        : N1 to the current property value
							 "-"          : only the current property value
							 "-+3"        : the current property value or the current value +1, +2 and +3
							 "-!3"        : the current property value or the current value -1, -2, and -3
							 "N1-+3"      : N1 or N1+1, or N1+2, or N1+3
							 "*"          : all possible values for that property. for the year 
							                property, this range will be 1901-2038
               "%N"         : only values from the resulting set that are evenly divisible by N
							 "(^|v)"      : sort method: ^ = ascending order, v = descending order. the default 
		                          behavior is to apply the natural sort resulting from the parameters 
		                          specified above
							 "#"          : sets the current date values as the first in the property array 
		                          (followed by the sort method specified)

PARAMS:
Key             Type          Value/Default    Description

[attr]          [element tag] (value|[cycle])  see sra-attr.tpl
                                               
[cycle]         cycles        [csv cycle vals] see sra-attr.tpl

fieldName                                      if the form name should not be 
                                               the name of the attribute, the 
																							 actual name should be specified 
																							 using this parameter
																							 
fieldNamePre                                   prefix to add to the input field name

fieldNamePost                                  postfix to add to the input field name
																							 
[property]      formatStr                      if the default formatStr for the property specified 
                                               is different than the desired display value, this 
																							 parameter can be specified. it will be 
																							 passed to the $dateTime->format() 
																							 method and the return value displayed. the default 
																							 formatStr values for each property type are 
																							 shown above. optionally, if the format string 
																							 contains the substring "{$rangeVal}", then that 
																							 value will be substituted with the actual range 
																							 value for each property value and the display value 
                                               will be the value returned by the app resource 
																							 bundle using the resulting string as the key
																							 
maxEnclElems    encl          (1-N)/5          the max # of possible "encl" parameters for a 
                                               [property]
                                               
[property][N]   encl          [html element]   html element(s) that the property specified should 
                                               be enclosed in. [N] may be blank, or a value between 
																							 1 and "maxEnclElems", where just "[property]" will 
																							 be rendered first, followed by "[property]1", 
																							 "[property]2", ... "[property][maxEnclElems]" 

[property]      firstOption   [resource]       optional resource identifier to display as the first 
                                               option of the specified property
                                               
[property]      pos           (0-7)/           the relative placement for the property specified
                              day=2            where this value signifies the order in which the 
                              hour=0           property should be rendered. if the pos value for 
                              meridiem=0       a given property is 0, then that property will not 
														  minute=0         be rendered. the default values for each property 
														  month=1          type are provided to the left
															second=0
															year=3

[property]      post          [html/text]      html/text to display after the property specified

[property]      postTpl       [template name]  template to display after the property specified.
                                               templates are rendered after any post html/text

[property]      pre           [html/text]      html/text to display before the property specified

[property]      preTpl        [template name]  template to display before the property specified.
                                               templates are rendered before any post html/text
																							 
[property]      range         [range]          the range of values that should be displayed for 
                                               the property specified. if no range is given, the 
																							 full range of values available for that property 
																							 (same as range="*") for more information see the 
																							 [range] definition above and $Date->getPropertyRange

*}

{assign var="tplName" value="sra-form-date"}
{$Template->initTemplate($tplName)}

{assign var="baseFieldName" value=$fieldName}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{assign var="enclParams" value=$params->getTypeSubset('encl')}
{assign var="firstOptionParams" value=$params->getTypeSubset('firstOption')}
{assign var="formatStrParams" value=$params->getTypeSubset('formatStr')}
{assign var="posParams" value=$params->getTypeSubset('pos')}
{assign var="postParams" value=$params->getTypeSubset('post')}
{assign var="postTplParams" value=$params->getTypeSubset('postTpl')}
{assign var="preParams" value=$params->getTypeSubset('pre')}
{assign var="preTplParams" value=$params->getTypeSubset('preTpl')}
{assign var="rangeParams" value=$params->getTypeSubset('range')}
{assign var="maxEnclElems" value=$enclParams->getParam('maxEnclElems', 5)}
{assign var="dayStartIdx" value="0"}
{assign var="hourStartIdx" value="0"}
{assign var="meridiemStartIdx" value="0"}
{assign var="minuteStartIdx" value="0"}
{assign var="monthStartIdx" value="0"}
{assign var="secondStartIdx" value="0"}
{assign var="yearStartIdx" value="0"}
{assign var="seq" value=$Template->getUniqueSeq()}
{assign var="functionName" value="updateTimestamp"|cat:$seq}

{foreach from=$Util->getArray(7) item=idx}
{assign var="property" value=""}

{if $idx eq $posParams->getParam('day', 2)}{assign var="property" value="day"}{if $firstOptionParams->getParam($property)}{assign var="dayStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('hour')}{assign var="property" value="hour"}{if $firstOptionParams->getParam($property)}{assign var="hourStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('meridiem')}{assign var="property" value="meridiem"}{if $firstOptionParams->getParam($property)}{assign var="meridiemStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('minute')}{assign var="property" value="minute"}{if $firstOptionParams->getParam($property)}{assign var="minuteStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('month', 1)}{assign var="property" value="month"}{if $firstOptionParams->getParam($property)}{assign var="monthStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('second')}{assign var="property" value="second"}{if $firstOptionParams->getParam($property)}{assign var="secondStartIdx" value="1"}{/if}{/if}
{if $idx eq $posParams->getParam('year', 3)}{assign var="property" value="year"}{if $firstOptionParams->getParam($property)}{assign var="yearStartIdx" value="1"}{/if}{/if}

{if $property}

{* pre property templates, text/html *}
{if $preTplParams->getParam($property)}{include file=$preTplParams->getParam($property)}{/if}
{$preParams->getParam($property)}

{* property enclose element *}
{foreach from=$Util->getArray($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}{$Template->renderOpen($tplName, $encl, $params, $property, 1, $displayVal)}{/if}
{/foreach}

{$Template->renderOpen($tplName, 'select', $params, $property, 0)} name="tmp_{$property|cat:$seq}" onchange="{$functionName}(this)">
{if $firstOptionParams->getParam($property)}{$Template->renderOpen($tplName, 'option', $params, $property, 0)} value="0">{$resources->getString($firstOptionParams->getParam($property))}</option>{/if}
{assign var="prop" value=$property}
{if $property eq 'hour' && $posParams->getParam('meridiem')}{assign var="prop" value='hour12'}{/if}
{foreach from=$Date->getPropertyRange($prop, $rangeParams->getParam($property), $formatStrParams->getParam($property)) key=val item=displ}
{$Template->renderOpen($tplName, 'option', $params, $property, 0)} value="{$val}"{if $attribute && ($attribute->get($property) eq $val || ($prop eq 'hour12' && ($attribute->get($property) - 12) eq $val))} selected="selected"{/if}>{$displ}</option>
{/foreach}
</select>

{* property enclose element *}
{foreach from=$Util->getArrayReverse($maxEnclElems,0) item=enclIdx}
{if $enclIdx eq 0}{assign var="enclIdx" value=""}{/if}
{assign var="tmp" value=$property|cat:$enclIdx}
{assign var="encl" value=$enclParams->getParam($tmp)}
{if $encl}</{$encl}>{/if}
{/foreach}

{* post property templates, text/html *}
{$postParams->getParam($property)}
{if $postTplParams->getParam($property)}{include file=$postTplParams->getParam($property)}{/if}

{/if}
{/foreach}

<input name="{$fieldName}" type="hidden" value="{if $attribute && $attribute->isDateOnly()}{$attribute->format('Y-m-d')}{elseif $attribute}{$attribute->format('Y-m-d H:i:s')}{/if}" />

<script type="text/javascript">
<!--
function {$functionName}(field) {ldelim}
	var day = -1;
	var month = -1;
	var year = -1;
{if $entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_TIME}
	var hour = -1;
	var minute = -1;
	var second = -1;
{/if}
{if $posParams->getParam('day')}
	if (field.form.tmp_day{$seq}.selectedIndex >= {$dayStartIdx})
		day = field.form.tmp_day{$seq}.options[field.form.tmp_day{$seq}.selectedIndex].value;
{else}
	day = '{if $attribute}{$attribute->format('d')}{else}01{/if}';
{/if}
{if $posParams->getParam('month')}
	if (field.form.tmp_month{$seq}.selectedIndex >= {$monthStartIdx})
		month = field.form.tmp_month{$seq}.options[field.form.tmp_month{$seq}.selectedIndex].value;
{else}
		month = '{if $attribute}{$attribute->format('m')}{else}01{/if}';
{/if}
{if $posParams->getParam('year')}
	if (field.form.tmp_year{$seq}.selectedIndex >= {$yearStartIdx})
		year = field.form.tmp_year{$seq}.options[field.form.tmp_year{$seq}.selectedIndex].value;
{else}
		year = '{if $attribute}{$attribute->format('Y')}{else}1901{/if}';
{/if}
{if $entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_TIME}
{if $posParams->getParam('second')}
	if (field.form.tmp_second{$seq}.selectedIndex >= {$secondStartIdx})
		second = field.form.tmp_second{$seq}.options[field.form.tmp_second{$seq}.selectedIndex].value;
{else}
		second = '{if $attribute}{$attribute->format('s')}{else}00{/if}';
{/if}
{if $posParams->getParam('hour')}
	if (field.form.tmp_hour{$seq}.selectedIndex >= {$hourStartIdx})
		hour = field.form.tmp_hour{$seq}.options[field.form.tmp_hour{$seq}.selectedIndex].value;
{else}
		hour = '{if $attribute}{$attribute->format('H')}{else}00{/if}';
{/if}
{if $posParams->getParam('meridiem') && $posParams->getParam('hour')}
	if (hour != false && field.form.tmp_meridiem{$seq}.selectedIndex >= {$meridiemStartIdx} && field.form.tmp_meridiem{$seq}.options[field.form.tmp_meridiem{$seq}.selectedIndex].value == 'pm') {ldelim}
		hour *= 1;
		hour += 12;
		if (hour == 24)
			hour = 12;
	{rdelim}
	else {ldelim}
		if (hour == 12)
			hour = 0;
	{rdelim}
	hour += '';
{/if}
{if $posParams->getParam('minute')}
	if (field.form.tmp_minute{$seq}.selectedIndex >= {$minuteStartIdx})
		minute = field.form.tmp_minute{$seq}.options[field.form.tmp_minute{$seq}.selectedIndex].value;
{else}
		minute = '{if $attribute}{$attribute->format('i')}{else}00{/if}';
{/if}
{/if}
	if (day != -1 && month != -1 && year != -1{if $entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_TIME} && second != -1 && hour != -1 && minute != -1{/if}) {ldelim}
		if (day.length != 2)
			day = '0' + day;
		if (month.length != 2)
			month = '0' + month;
		if (year.length != 4)
			year = '0' + year;
{if $entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_TIME}
		if (hour.length != 2)
			hour = '0' + hour;
		if (minute.length != 2)
			minute = '0' + minute;
		if (second.length != 2)
			second = '0' + second;
{/if}
		field.form['{$fieldName}'].value=year + "-" + month + "-" + day{if $entity->getAttributeType($attributeName) eq $smarty.const.SRA_DATA_TYPE_TIME} + " " + hour + ":" + minute + ":" + second{/if};
		//alert(field.form['{$fieldName}'].value);
	{rdelim}
	else {ldelim}
		//alert("Not set: Day - " + day + " Hour: " + hour + " Min: " + minute + " Month: " + month + " Second: " + second + " Year: " + field.form.tmp_year{$seq}.selectedIndex);
		field.form['{$fieldName}'].value="";
	{rdelim}
	
{rdelim}
// -->
</script>
