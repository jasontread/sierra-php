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
this view may be used to render an address entity which adheres to the field 
specification defined in 'sierra/etc/l10n/address-formats.dtd'. These 
fields include the following:

country:    ISO3166 2-character country identifier. if NOT an attribute of the 
            entity for this view, the 'country' parameter can be specified. 
            otherwise, SRA_COUNTRY_DEFAULT will be used. the country will 
            determine the output formatting and layout for the address entity 
            based on the country address format configurations defined in 
            'sierra/etc/l10n/address-formats.xml'. this is accomplished using 
            the sierra address format class: 
            'sierra/lib/util/l10n/SRA_AddressFormat.php'. for more information, 
            review the inline documentation provided in this DTD and class
locality:   may also be represented by one of the following attribute names: 
            city, cityRegion, county, countyCity, postalDistrict, or suburb
postalCode: may also be represented by one of the following attribute names: 
            districtTown, pinCode or zip
region:     may also be represented by one of the following attribute names: 
            districtRegion, federalSubject, prefecture, province, state, or 
            village
street:     this field is the only one that supports multi-line entries. line 
            breaks it contains will automatically be converted to xhtml <br />
            elements if output is raw. if output is through an attribute view, 
            this conversion will not take place. may also be represented by the 
            following attribute name: furtherDivisions
            
for more information, review the field mappings definition in 
'sierra/etc/l10n/address-field-mappings.properties'

in order to use this view to render an address you simply need to define it as 
the 'template' within the entity view definitions. for example:

<entity key="Address">
  ...
  <!-- used for the input view of this address: note use of "view" param -->
  <view key="input" template="l10n/sra-address.tpl">
    <param id="view" value="input" />
  </view>
  <!-- used for the out view of this address: uses default "output" view or raw -->
  <view key="output" template="l10n/sra-address.tpl" />
  ...
</entity>

the fields noted above will be output in raw form UNLESS a view is defined for 
them named 'output' (or alternatively the value provided using the 'view' 
parameter described below). the address will be rendered within a block-level 
'div' element. each row in the address is also rendered using a 'div' element, 
and each column/field is rendered using a 'span' element (inline). css based 
formatting may be applied using the 'class*' parameters defined below.


PARAMS:
Key            Type          Value/Default     Description

address        class                           the css class to assign to the 
                                               'div' element that surrounds the 
                                               entire address
                                               
col            class                           the css class to assign to each 
                                               column 'span' element in the 
                                               address
                                               
row            class                           the css class to assign to each 
                                               row 'div' element in the address
                                               
[field]        class         (country|locality the css class to assign to the
                              |postalCode      column 'span' element for the 
                              |region|street)  field specified. if both [field] 
                                               and 'col' classes are defined, 
                                               both will be applied to the 
                                               'span' for that field, but the 
                                               [field] class will be first, and 
                                               thus override any duplicate 
                                               styles defined by the 'col' class
                                               
country                                        ISO3166 2-character country 
                                               identifier to use for this 
                                               address. if the entity this view 
                                               is being rendered for has a valid 
                                               country attribute, that value 
                                               will override this one. if 
                                               neither this parameter, nor an 
                                               entity country attribute are 
                                               present, the SRA_AddressFormat 
                                               constant SRA_COUNTRY_DEFAULT will 
                                               be used instead

view                                           the name of the attribute view to 
                                               check for and use when valid for 
                                               any of the fields defined above. 
                                               if not specified, this view will 
                                               check for an attribute view named 
                                               'output' for each field. if the 
                                               view is not valid for any field, 
                                               that field will be output in raw 
                                               form instead
*}

{$Template->includeOnce('util/l10n/SRA_AddressFormat.php')}
{assign var="myParams" value=$Template->getVarByRef('params')}
{assign var="classParams" value=$myParams->getTypeSubset('class')}
{if $entity->isAttribute('country')}{assign var="country" value=$entity->getAttribute('country')}{assign var="country" value=$SRA_Country->getCode($country)}{/if}
{if !$country && $myParams->getParam('country')}{assign var="country" value=$myParams->getParam('country')}{assign var="country" value=$SRA_Country->getCode($country)}{/if}
{if !$country}{assign var="country" value=$smarty.const.SRA_COUNTRY_DEFAULT}{/if}
{if $myParams->getParam('view')}{assign var="view" value=$myParams->getParam('view')}{else}{assign var="view" value=$myParams->getParam('output')}{/if}
{assign var="af" value=$SRA_AddressFormat->getInstance($country)}
{if $af}
{assign var="addressGrid" value=$af->getFieldsAsGrid()}
{assign var="addressFields" value=$SRA_AddressFormat->getFieldValuesFromEntity($entity, $view)}

<div{if $classParams->getParam('address')} class="{$classParams->getParam('address')}"{/if}>
{assign var="currentRow" value=0}
{foreach from=$Util->getArray($smarty.const.SRA_ADDRESS_FORMAT_MAX_ROWS,1) item=row}
{foreach from=$Util->getArray($smarty.const.SRA_ADDRESS_FORMAT_MAX_COLS,1) item=col}
{foreach from=$addressGrid key=gridRow item=cols}
{foreach from=$cols key=gridCol item=field}
{if $row eq $gridRow && $col eq $gridCol}
{if $currentRow neq $row}<div{if $classParams->getParam('row')} class="{$classParams->getParam('row')}"{/if}>{assign var="currentRow" value=$row}{/if}
{assign var=attr value=$field.attr}
{assign var=val value=$addressFields[$attr]}
<span{if $classParams->getParam('col') || $classParams->getParam($attr)} class="{if $classParams->getParam($attr)}{$classParams->getParam($attr)}{/if}{if $classParams->getParam('col')} {$classParams->getParam('col')}{/if}"{/if}>{if $val}{if !$field.showKey && $field.options && $field.options[$val]}{$field.options[$val]}{else}{$val}{/if}{else}&nbsp;{/if}</span>
{/if}
{/foreach}
{/foreach}
{/foreach}
{if $currentRow eq $row}</div>{/if}
{/foreach}
</div>
{/if}
