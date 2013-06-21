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
Renders an entity in a grid based table layout. This is a table that contains 
1 or more row sets. each row set consists of 1 or more column sets. a column 
set consists of labels and data. headers can also be applied at a table, row set 
and column set level. the grid can support up to R=0-[maxRows] and N=0-[maxCols]


PARAMS:
Key            Type                      Value/Default    Description

[attr]         [elem[-type]]-attrs       (value|[cycle])  optional html element attribute name/value pairs 
                                                          such as "border"/"0", "cellpadding"/"1". these
																													values will be applied to each instance of the 
																													corresponding html element. for example, to apply 
																													attributes to the html "table" element, the type 
																													for this parameter would be "table-attrs". for 
																													a row, it would be "tr-attrs", and a column 
																													"td-attrs". more granular element specification 
																													can be performed utilizing the optional element type 
																													identifiers ("-type") automatically appended to the 
																													following cell types:
																													  "header": column containing the table header
																													  "rowSetHeader": column containing a row header
																														"colSetHeader": column containing a column set header
																														"labelHeader": column containing a label header
																														"attrHeader": column containing an attribute header
																														"label": column containing a label
																														"attr": column containing an attribute value
																														"heighPad": height padding column
																														"widthPad": width padding column
																														"buttons": column containing buttons
																														"submit" : submit button
																														"reset"  : reset button
																														"button" : button
																														"[tplVar]" : column containing a template variable 
																														             (the name of the last template variable 
																																				 displayed in that colum is the -type)
																													
[cycle]        cycles                    [csv cycle vals] cycles referenced within any of the "[attr]" 
                                                          parameters. cycles are defined as a comma 
                                                          separated list of values that should be 
                                                          cycled through once for every impression 
                                                          of the attribute. for example, a cycle 
                                                          value "red,blue", would toggle red and blue 
                                                          values for each instance of the attr that 
                                                          referenced that cycle. cycles are reset for 
																													each instance of this template. to skip a value 
																													in a cycle, no value should be specified. for 
																													example, "red,,blue" would cycle through red 
																													and blue values with one attribute in the 
																													middle being skipped (the attribute will not 
																													be rendered at all in that case)
																													
header                                                    string to place in the header. the actual value 
                                                          rendered will be the value returned from 
                                                          $entity->parseString([header]). the header will 
																													be placed in the top row of the table using a 
																													"th" cell that spans the entire table
																													
maxRows                                  [int]/50         the max value of R

maxCols                                  [int]/10         the max value of N
																													
rowSetHeader   row-set-config[R]                          string to use as the header for row-set R. the actual 
                                                          value rendered will be the value returned from 
                                                          $entity->parseString([header]). if specified, the 
																													header will be placed at the top of the row-set
                                                          
rowSetHeaderCol row-set-config[R]        (td|th)/th       the type of column (td or th) to use to display 
                                                          the row header. the default is th
																													
ignoreEmpty    row-set-config[R]         (0|1)/1          whether or not to ignore this row (or all rows if [R] is 
                                                          left out) if all of its columns are empty
                                                          
colSetAlign    col-set-config[R-C]       (left|right|center)/  specifies explicit column alignment
																													
colSetFormat   col-set-config[R-C]       (vert|horz)/vert the attribute cell layout for col-set N in row-set R
                                                          "vert" signifies that attribute cells will be displayed 
																													vertically, while "horz" signifies that they will be 
																													displayed horizontally. attributes will be displayed 
																													in the order they are specified in the "col-set[NR]" 
																													params xml
                                                          
colSetHeaderCol col-set-config[R-C]      (td|th)/th       the type of column (td or th) to use to display 
                                                          the column header. the default is th
                                                          
colSetHeaderClass col-set-config[R-C]                     allows a custom class to be applied to the label
                                                             
colSetHeaderStyle col-set-config[R-C]                     allows a custom style to be applied to the label
                                                          
colSetLabelCol col-set-config[R-C]       (td|th)/mixed    the type of column (td or th) to use to display 
                                                          labels. the default is td for "vert" format and 
                                                          td for "horz" format
																													
colSetLabelPos col-set-config[R-C]       (0|1|2)/1        defines where attribute column labels should be 
                                                          rendered for col-set N in row-set R. these values 
																													have the following meaning:
																													0: do not show labels
																													1: (default) show labels in a column to the left in 
																													   "vert" format, or on top in "horz" format
																												  2: show labels in a column to the right in "vert" 
																													   format, or on the bottom in "horz" format
                                                             
colSetLabelClass col-set-config[R-C]                      allows a custom class to be applied to the label

colSetLabelEncl  col-set-config[R-C]     span             if colSetLabelClass, colSetLabelOnclick or colSetLabelStyle 
                                                          are specified, this parameter can be used to specify what 
                                                          xhtml element to use to enclose the label. the default 
                                                          element is "span"

colSetLabelOnclick col-set-config[R-C]                    allows a custom onclick event to be applied to the label. 
                                                          the value for this parameter may contain the tokens 
                                                          [entity] or [attr] which will be replaced with the 
                                                          corresponding entity and attribute name
                                                             
colSetLabelStyle col-set-config[R-C]                      allows a custom style to be applied to the label
																													
[component]    col-set[R-C]              [conf]           defines the position and order of grid components
																													to display in row-set R, col-set N. [component] and 
																													[conf] are defines as follows:
																													  [component]: "btn:(submit|reset|button|close)": for a form button of 
																														             that corresponding type
																																				 "var:[varName]": a template variable
																																				 if not present, colset will be ignored
																																				 "[attr]": the name of an entity attribute
																														[conf]:      if the [component] is an attribute, this value can 
																														             be used to define the attribute view that should be 
																																				 rendered. if not specified, then the attribute will 
																																				 be displayed in raw form. if the [component] is a 
																																				 button (submit, reset, or button), this value can 
																																				 be used to define the resource identifier that should 
																																				 be used as the text for that button
																													
colSetWidth    col-set-config[R-C]                        the total width in cells for col-set R-C (if different 
                                                          then the standard width as determined by "colSetFormat")
																													
colSetHeight   col-set-config[R-C]                        the total height in cells for col-set R-C (if different 
                                                          then the standard height as determined by "colSetFormat")
																													
widthPadding   col-set-config[R-C]       (1|2|3)/1        what to do with extra width in col sets. the following 
                                                          options are possible
																													1: span multiple columns (default). extra space is even distributed
																													2: add filler cells to the left
																													3: add filler cells to the right
																													
heightPadding  col-set-config[R-C]       (1|2|3)/1        what to do with extra height in col sets. the following 
                                                          options are possible
																													1: span multiple rows (default). extra space is even distributed
																													2: add filler cells to the bottom
																													3: add filler cells to the top
																													
colSetHeader   col-set-config[R-C]       [resource]       optional header string to display at the head of col-set[R-C] 
																													the actual value rendered will be the value returned from 
																													$entity->parseString([colSetHeader]). the header will be 
																													placed directly above the col-set using a "th" cell 
																													that spans the entire col-set
																													
labelHeader    col-set-config[R-C]                        only applies to "vert" col-sets. if specified, 
                                                          $entity->parseString for this value will 
																													be rendered in a "th" cell directly above the label 
																													column (but below colSetHeader if applicable)
																													
attrHeader     col-set-config[R-C]                        only applies to "vert" col-sets. if specified, 
                                                          $entity->parseString for this value will 
																													be rendered in a "th" cell directly above the attribute 
																													column (but below colSetHeader if applicable)
																													
renderForm     form                      (0|1|2)/0        whether or not to enclose the table in a form (use 
                                           0=No           [elem]-attrs to render appropriate method, action, etc. 
																					 1=Yes  				if either "submitResource" or "resetResource" are 
																					 2=Open only		specified, they will be rendered in a "td" at the bottom 
																													of the table that spans the entire table
																													
submitResource form                                       resource string for the submit button (if not specified 
                                                          the submit button will not be rendered)

resetResource form                                        resource string for the reset button (if not specified, 
                                                          the cancel button will not be rendered)
                                                          
[attr]        post-attrs                 [view]           attributes and their corresponding views that should be 
                                                          rendered after to the table for the grid
                                                          
[attr]        pre-attrs                  [view]           attributes and their corresponding views that should be 
                                                          rendered prior to the table for the grid
                                                          
insertPre                                                 the relative or absolute path to an html file to insert 
                                                          prior to the table rendering
                                                          
insertPost                                                the relative or absolute path to an html file to insert 
                                                          after the table rendering
                                                          
insertTplPre                                              same as insertPre but the contents of this file will 
                                                          be parsed by the smarty template engine and thus must 
                                                          adhere to smarty standards such as ldelim/rdelim
                                                          
insertTplPost                                             same as insertPost but the contents of this file will 
                                                          be parsed by the smarty template engine and thus must 
                                                          adhere to smarty standards such as ldelim/rdelim
                                                          
jsSrcInsertPre                                            the relative or absolute path to a javascript source file 
                                                          to insert prior to the table rendering (the contents of 
                                                          this file will be imbedded between <script> tags)
                                                          
jsSrcInsertPost                                           the relative or absolute path to a javascript source file 
                                                          to insert after the table rendering (the contents of 
                                                          this file will be imbedded between <script> tags)
                                                          
jsTplInsertPre                                            same as jsSrcInsertPre but the contents of this file will 
                                                          be parsed by the smarty template engine and thus must 
                                                          adhere to smarty standards such as ldelim/rdelim
                                                          
jsTplInsertPost                                           same as jsSrcInsertPost but the contents of this file will 
                                                          be parsed by the smarty template engine and thus must 
                                                          adhere to smarty standards such as ldelim/rdelim
                                                          
noOpenTable                              (1|0)/0          whether or not to open the table element for this grid

noCloseTable                             (1|0)/0          whether or not to close the table element for this grid
																													
*** Default [row|col]-set-config[N] parameters may be specified using type: "[row|col]-set-config". 
    If a corresponding config for [row|col]-set R/N is not provided, the default will be used
*}
{assign var="gridParams" value=$params}
{assign var="gridTplName" value="sra-grid"}
{$Template->initTemplate($gridTplName)}

{if $gridParams->getParam('renderForm')}{$Template->renderOpen($gridTplName, 'form', $gridParams)}{/if}
{if $gridParams->hasType('attr-encl')}{assign var="attrEncl" value=$gridParams->getTypeSubset('attr-encl')}{/if}
{if $gridParams->hasType('label-encl')}{assign var="labelEncl" value=$gridParams->getTypeSubset('label-encl')}{/if}
{if $gridParams->hasType('row-set-config')}{assign var="defaultRowSetConfig" value=$gridParams->getTypeSubset('row-set-config')}{/if}
{if $gridParams->hasType('col-set-config')}{assign var="defaultColSetConfig" value=$gridParams->getTypeSubset('col-set-config')}{/if}
{assign var="r" value=$gridParams->getParam('maxRows',50)}
{assign var="c" value=$gridParams->getParam('maxCols',10)}
{assign var="matrix" value=$Util->getNewMatrix()}
{assign var="cellTypeMatrix" value=$Util->getNewMatrix()}
{assign var="cellIdMatrix" value=$Util->getNewMatrix()}
{assign var="widthMatrix" value=$Util->getNewMatrix()}
{assign var="alignMatrix" value=$Util->getNewMatrix()}
{assign var="heightMatrix" value=$Util->getNewMatrix()}
{assign var="currentRow" value="0"}

{foreach from=$Util->getArray($r,0) item=row}
	{assign var="currentRow" value=$matrix->getHeight()}
	{assign var="rowSetId" value="row-set"|cat:$row}
	{assign var="rowSetConfigId" value="row-set-config"|cat:$row}
	{assign var="rowSetConfig" value="0"}
  {assign var="rowSetHeaderCol" value="th"}
	{if $gridParams->hasType($rowSetConfigId)}{assign var="rowSetConfig" value=$gridParams->getTypeSubset($rowSetConfigId)}{/if}
  {if $rowSetConfig && $rowSetConfig->hasParam("rowSetHeaderCol")}{assign var="rowSetHeaderCol" value=$rowSetConfig->getParam("rowSetHeaderCol")}{/if}
	{if ($rowSetConfig && $rowSetConfig->getParam('rowSetHeader')) || ($defaultRowSetConfig && $defaultRowSetConfig->getParam('rowSetHeader'))}
		{$cellTypeMatrix->pushRow($rowSetHeaderCol)}
		{$cellIdMatrix->pushRow("rowSetHeader")}
		{$widthMatrix->pushRow("-1")}
    {$alignMatrix->pushRow("-1")}
		{$heightMatrix->pushRow("1")}
		{if $rowSetConfig && $rowSetConfig->getParam('rowSetHeader')}
			{assign var="tmp" value=$rowSetConfig->getParam('rowSetHeader')}
			{$matrix->pushRow($entity->parseString($tmp))}
		{else}
			{assign var="tmp" value=$defaultRowSetConfig->getParam('rowSetHeader')}
			{$matrix->pushRow($entity->parseString($tmp))}
		{/if}
		{assign var="currentRow" value=$currentRow+1}
	{/if}
	{foreach from=$Util->getArray($c,0) item=col}
		{assign var="colSetId" value="col-set"|cat:$row|cat:"-"|cat:$col}
		{if $gridParams->hasType($colSetId)}
			{assign var="reduceRowCount" value="0"}
			{assign var="colSet" value=$gridParams->getTypeSubset($colSetId)}
			{assign var="colSetConfigId" value="col-set-config"|cat:$row|cat:"-"|cat:$col}
			{assign var="colSetConfig" value="0"}
			{if $gridParams->hasType($colSetConfigId)}{assign var="colSetConfig" value=$gridParams->getTypeSubset($colSetConfigId)}{/if}
			{assign var="colSetFormat" value="vert"}
			{assign var="colSetHeader" value=""}
			{assign var="labelHeader" value=""}
			{assign var="attrHeader" value=""}
      {assign var="colSetHeaderCol" value="th"}
      {assign var="colSetHeaderClass" value=""}
      {assign var="colSetHeaderStyle" value=""}
      {assign var="colSetLabelCol" value=0}
			{assign var="colSetLabelPos" value="1"}
      {assign var="colSetLabelClass" value=0}
      {assign var="colSetLabelEncl" value="span"}
      {assign var="colSetLabelOnclick" value=0}
      {assign var="colSetLabelStyle" value=0}
			{assign var="widthPadding" value="1"}
			{assign var="heightPadding" value="1"}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("colSetFormat")}{assign var="colSetFormat" value=$defaultColSetConfig->getParam("colSetFormat")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("colSetFormat")}{assign var="colSetFormat" value=$colSetConfig->getParam("colSetFormat")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("colSetHeader")}{assign var="colSetHeader" value=$defaultColSetConfig->getParam("colSetHeader")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("colSetHeader")}{assign var="colSetHeader" value=$colSetConfig->getParam("colSetHeader")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("labelHeader")}{assign var="labelHeader" value=$defaultColSetConfig->getParam("labelHeader")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("labelHeader")}{assign var="labelHeader" value=$colSetConfig->getParam("labelHeader")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("attrHeader")}{assign var="attrHeader" value=$defaultColSetConfig->getParam("attrHeader")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("attrHeader")}{assign var="attrHeader" value=$colSetConfig->getParam("attrHeader")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetHeaderCol")}{assign var="colSetHeaderCol" value=$defaultColSetConfig->getParam("colSetHeaderCol")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetHeaderCol")}{assign var="colSetHeaderCol" value=$colSetConfig->getParam("colSetHeaderCol")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetHeaderClass")}{assign var="colSetHeaderClass" value=$defaultColSetConfig->getParam("colSetHeaderClass")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetHeaderClass")}{assign var="colSetHeaderClass" value=$colSetConfig->getParam("colSetHeaderClass")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetHeaderStyle")}{assign var="colSetHeaderStyle" value=$defaultColSetConfig->getParam("colSetHeaderStyle")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetHeaderStyle")}{assign var="colSetHeaderStyle" value=$colSetConfig->getParam("colSetHeaderStyle")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelCol")}{assign var="colSetLabelCol" value=$defaultColSetConfig->getParam("colSetLabelCol")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelCol")}{assign var="colSetLabelCol" value=$colSetConfig->getParam("colSetLabelCol")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelPos")}{assign var="colSetLabelPos" value=$defaultColSetConfig->getParam("colSetLabelPos")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelPos")}{assign var="colSetLabelPos" value=$colSetConfig->getParam("colSetLabelPos")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelClass")}{assign var="colSetLabelClass" value=$defaultColSetConfig->getParam("colSetLabelClass")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelClass")}{assign var="colSetLabelClass" value=$colSetConfig->getParam("colSetLabelClass")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelEncl")}{assign var="colSetLabelEncl" value=$defaultColSetConfig->getParam("colSetLabelEncl")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelEncl")}{assign var="colSetLabelEncl" value=$colSetConfig->getParam("colSetLabelEncl")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelOnclick")}{assign var="colSetLabelOnclick" value=$defaultColSetConfig->getParam("colSetLabelOnclick")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelOnclick")}{assign var="colSetLabelOnclick" value=$colSetConfig->getParam("colSetLabelOnclick")}{/if}
      {if $defaultColSetConfig && $defaultColSetConfig->hasParam("colSetLabelStyle")}{assign var="colSetLabelStyle" value=$defaultColSetConfig->getParam("colSetLabelStyle")}{/if}
			{if $colSetConfig && $colSetConfig->hasParam("colSetLabelStyle")}{assign var="colSetLabelStyle" value=$colSetConfig->getParam("colSetLabelStyle")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("widthPadding")}{assign var="widthPadding" value=$defaultColSetConfig->getParam("widthPadding")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("widthPadding")}{assign var="widthPadding" value=$colSetConfig->getParam("widthPadding")}{/if}
			{if $defaultColSetConfig && $defaultColSetConfig->getParam("heightPadding")}{assign var="heightPadding" value=$defaultColSetConfig->getParam("heightPadding")}{/if}
			{if $colSetConfig && $colSetConfig->getParam("heightPadding")}{assign var="heightPadding" value=$colSetConfig->getParam("heightPadding")}{/if}
      {if !$colSetLabelCol && $colSetLabelPos}{if $colSetFormat eq "vert"}{assign var="colSetLabelCol" value="td"}{else}{assign var="colSetLabelCol" value="th"}{/if}{/if}
			{assign var="baseColSetWidth" value="0"}
			{assign var="baseColSetHeight" value="0"}
			{if $colSetHeader}{assign var="baseColSetHeight" value=$baseColSetHeight+1}{/if}
			{if $colSetFormat eq "vert"}
				{assign var="baseColSetWidth" value=$baseColSetWidth+1}
				{if $colSetLabelPos > 0}{assign var="baseColSetWidth" value=$baseColSetWidth+1}{/if}
				{foreach from=$colSet->getParams() key=attr item=view}{assign var="baseColSetHeight" value=$baseColSetHeight+1}{/foreach}
			{else}
				{if $colSetLabelPos > 0}{assign var="baseColSetHeight" value=$baseColSetHeight+1}{/if}
				{foreach from=$colSet->getParams() key=attr item=view}{assign var="baseColSetWidth" value=$baseColSetWidth+1}{/foreach}
			{/if}
			{foreach from=$colSet->getParams() key=attr item=view}{assign var="lastAttr" value=$attr}{/foreach}
			
			{if $colSetConfig && $colSetConfig->getParam("colSetWidth")}
				{assign var="colSetWidth" value=$colSetConfig->getParam("colSetWidth")}
			{else}
				{assign var="colSetWidth" value=$baseColSetWidth}
			{/if}
			{if $colSetConfig && $colSetConfig->getParam("colSetAlign")}
				{assign var="colSetAlign" value=$colSetConfig->getParam("colSetAlign")}
      {else}
        {assign var="colSetAlign" value="0"}
			{/if}
			{if $colSetConfig && $colSetConfig->getParam("colSetHeight")}
				{assign var="colSetHeight" value=$colSetConfig->getParam("colSetHeight")}
			{else}
				{assign var="colSetHeight" value=$baseColSetHeight}
			{/if}
			{if $widthPadding eq "1"}
			{assign var="colWidth1" value="1"}
			{assign var="colWidthN" value="1"}
			{if $baseColSetWidth < $colSetWidth}
				{assign var="tmp" value=$colSetWidth-$baseColSetWidth}
				{assign var="tmp" value=$tmp/$baseColSetWidth}
				{assign var="colWidth1" value=$Template->ceil($tmp)+1}
				{assign var="colWidthN" value=$Template->floor($tmp)+1}
			{/if}
			{else}
				{assign var="widthPad" value="0"}
				{if $baseColSetWidth < $colSetWidth}
					{assign var="widthPad" value=$colSetWidth-$baseColSetWidth}
				{/if}
			{/if}
			{if $heightPadding eq "1"}
			{assign var="colHeight1" value="1"}
			{assign var="colHeightN" value="1"}
			{if $baseColSetHeight < $colSetHeight}
				{assign var="tmp" value=$colSetHeight-$baseColSetHeight}
				{assign var="tmp" value=$tmp/$baseColSetHeight}
				{assign var="colHeight1" value=$Template->ceil($tmp)+1}
				{assign var="colHeightN" value=$Template->floor($tmp)+1}
			{/if}
			{else}
				{assign var="heightPad" value="0"}
				{if $baseColSetHeight < $colSetHeight}
					{assign var="heightPad" value=$colSetHeight-$baseColSetHeight-1}
				{/if}
			{/if}
			
			{if $heightPadding eq "3" && $heightPad}
				{$matrix->pushColumn(" ", $currentRow)}
				{$cellTypeMatrix->pushColumn("td", $currentRow)}
				{$cellIdMatrix->pushColumn("heightPad", $currentRow)}
				{$widthMatrix->pushColumn($colSetWidth, $currentRow)}
        {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
				{$heightMatrix->pushColumn($heightPad, $currentRow)}
				{assign var="currentRow" value=$currentRow+$heightPad}
				{assign var="reduceRowCount" value=$reduceRowCount+$heightPad}
			{/if}
			
			{if $colSetHeader}
				{$cellTypeMatrix->pushColumn($colSetHeaderCol, $currentRow)}
				{$cellIdMatrix->pushColumn("colSetHeader", $currentRow)}
				{$widthMatrix->pushColumn($colSetWidth, $currentRow)}
        {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
				{$heightMatrix->pushColumn("1", $currentRow)}
        {assign var="header" value=$entity->parseString($colSetHeader)}
        {if $colSetHeaderClass or $colSetHeaderStyle}
        {assign var="tmp" value="<span"}
        {if $colSetHeaderClass}{assign var="tmp" value=$tmp|cat:" class='"|cat:$colSetHeaderClass|cat:"'"}{/if}
        {if $colSetHeaderStyle}{assign var="tmp" value=$tmp|cat:" style='"|cat:$colSetHeaderStyle|cat:"'"}{/if}
        {assign var="header" value=$tmp|cat:">"|cat:$header|cat:"</span>"}
        {/if}
				{$matrix->pushColumn($header, $currentRow)}
				{assign var="currentRow" value=$currentRow+1}
				{assign var="reduceRowCount" value=$reduceRowCount+1}
			{/if}
			
			{assign var="attrPos" value="2"}
			{if $colSetLabelPos eq 0 || $colSetLabelPos eq 2}{assign var="attrPos" value="1"}{/if}
			
			{* vertical col-set layout *}
			{if $colSetFormat eq "vert"}
				{if $attrHeader || $labelHeader}
					{if $widthPadding eq "3" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{$cellTypeMatrix->pushColumn("th", $currentRow)}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{foreach from=$Util->getArray(2) item=colIdx}
						{if $widthPadding eq "1"}
							{assign var="cellWidth" value=$colWidthN}
							{if $colIdx eq 1}{assign var="cellWidth" value=$colWidth1}{/if}
						{else}
							{assign var="cellWidth" value="1"}
						{/if}
						{if $heightPadding eq "1"}
							{assign var="cellHeight" value=$colHeightN}
						{else}
							{assign var="cellHeight" value="1"}
						{/if}
						{if ($colSetLabelPos eq $colIdx) && $labelHeader || ($attrPos eq $colIdx && $attrHeader)}
							{if $colSetLabelPos eq $colIdx && $labelHeader}
								{$matrix->pushColumn($entity->parseString($labelHeader), $currentRow)}
								{$cellTypeMatrix->pushColumn("th", $currentRow)}
								{$cellIdMatrix->pushColumn("labelHeader", $currentRow)}
							{else}
								{$matrix->pushColumn($entity->parseString($attrHeader), $currentRow)}
								{$cellTypeMatrix->pushColumn("th", $currentRow)}
								{$cellIdMatrix->pushColumn("attrHeader", $currentRow)}
							{/if}
							{$widthMatrix->pushColumn($cellWidth, $currentRow)}
              {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
							{$heightMatrix->pushColumn($cellHeight, $currentRow)}
						{/if}
					{/foreach}
					{if $widthPadding eq "2" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{$cellTypeMatrix->pushColumn("th", $currentRow)}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{assign var="currentRow" value=$currentRow+1}
					{assign var="reduceRowCount" value=$reduceRowCount+1}
				{/if}
				{foreach from=$colSet->getParams() key=attr item=view}
					{if $widthPadding eq "3" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{$cellTypeMatrix->pushColumn("td", $currentRow)}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{foreach from=$Util->getArray(2) item=colIdx}
						{if $widthPadding eq "1"}
							{assign var="cellWidth" value=$colWidthN}
							{if $colIdx eq 1}{assign var="cellWidth" value=$colWidth1}{/if}
						{else}
							{assign var="cellWidth" value="1"}
						{/if}
						{if $heightPadding eq "1"}
							{assign var="cellHeight" value=$colHeightN}
							{if $attr eq $lastAttr}{assign var="cellHeight" value=$colHeight1}{/if}
						{else}
							{assign var="cellHeight" value="1"}
						{/if}
						{if ($colSetLabelPos eq $colIdx) || ($attrPos eq $colIdx)}
							{if $colSetLabelPos eq $colIdx}
                {assign var="label" value=$entity->getEntityLabel($attr)}
                {if $colSetLabelClass or $colSetLabelOnclick or $colSetLabelStyle}
                {assign var="tmp" value="<"|cat:$colSetLabelEncl}
                {if $colSetLabelOnclick}{assign var="tmpOnclick" value=$Template->strReplace('[attr]', $attr, $colSetLabelOnclick)}{assign var="tmpOnclick" value=$Template->strReplace('[entity]', $entity->getEntityType(), $tmpOnclick)}{/if}
                {if $colSetLabelClass}{assign var="tmp" value=$tmp|cat:" class='"|cat:$colSetLabelClass|cat:"'"}{/if}
                {if $tmpOnclick}{assign var="tmp" value=$tmp|cat:' onclick="'|cat:$tmpOnclick|cat:'"'}{/if}
                {if $colSetLabelStyle}{assign var="tmp" value=$tmp|cat:" style='"|cat:$colSetLabelStyle|cat:"'"}{/if}
                {assign var="label" value=$tmp|cat:">"|cat:$label|cat:"</"|cat:$colSetLabelEncl|cat:">"}
                {/if}
								{$matrix->pushColumn($label, $currentRow)}
								{$cellTypeMatrix->pushColumn($colSetLabelCol, $currentRow)}
								{$cellIdMatrix->pushColumn("label", $currentRow)}
							{else}
								{assign var="conf" value=$Template->explode(' ', $view)}
								{assign var="tmp" value=$Template->startBuffering()}
								{foreach from=$Template->explode(' ', $attr) key="id" item="attr"}
								{assign var="view" value=$conf[$id]}
								{if $attr eq 'btn:submit' || $attr eq 'btn:reset' || $attr eq 'btn:button' || $attr eq 'btn:close'}
								{assign var="btn" value=$Template->strReplace('btn:', '', $attr)}
								{$Template->renderOpen($gridTplName, 'input', $gridParams, $btn, 0)}{if $btn eq 'close'} onclick="window.close()"{/if} type="{if $btn eq 'close'}button{else}{$btn}{/if}"{if $view} value="{$Template->escapeHtmlQuotes($resources->getString($view))}"{/if} />
								{assign var="cellId" value="buttons"}
								{elseif $Util->beginsWith($attr, 'var:')}
								{assign var="tplVar" value=$Template->strReplace('var:', '', $attr)}
								{$Template->getVar($tplVar)}
								{assign var="cellId" value=$tplVar}
								{else}
								{assign var="cellId" value="attr"}
								{if $view}{$entity->renderAttribute($attr, $view)}{else}{$entity->getAttribute($attr)}{/if}
								{/if}
								{/foreach}
								{$matrix->pushColumn($Template->stopBuffering(), $currentRow)}
								{$cellIdMatrix->pushColumn($cellId, $currentRow)}
								{$cellTypeMatrix->pushColumn("td", $currentRow)}
							{/if}
							{$widthMatrix->pushColumn($cellWidth, $currentRow)}
              {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
							{$heightMatrix->pushColumn($cellHeight, $currentRow)}
						{/if}
					{/foreach}
					{if $widthPadding eq "2" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{$cellTypeMatrix->pushColumn("td", $currentRow)}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{assign var="currentRow" value=$currentRow+1}
					{assign var="reduceRowCount" value=$reduceRowCount+1}
				{/foreach}
			
			{* horizontal col-set layout *}
			{else}
				{foreach from=$Util->getArray(2) item=colIdx}
					{if $widthPadding eq "3" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{if $colSetLabelPos eq $colIdx}
							{$cellTypeMatrix->pushColumn($colSetLabelCol, $currentRow)}
						{else}
							{$cellTypeMatrix->pushColumn("td", $currentRow)}
						{/if}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{assign var="started" value="0"}
					{foreach from=$colSet->getParams() key=attr item=view}
						{if $widthPadding eq "1"}
							{assign var="cellWidth" value=$colWidthN}
							{if !$started}{assign var="cellWidth" value=$colWidth1}{/if}
						{else}
							{assign var="cellWidth" value="1"}
						{/if}
						{if $heightPadding eq "1"}
							{assign var="cellHeight" value=$colHeightN}
							{if $attr eq $lastAttr}{assign var="cellHeight" value=$colHeight1}{/if}
						{else}
							{assign var="cellHeight" value="1"}
						{/if}
						{if ($colSetLabelPos eq $colIdx) || ($attrPos eq $colIdx)}
							{if $colSetLabelPos eq $colIdx}
                {assign var="label" value=$entity->getEntityLabel($attr)}
                {if $colSetLabelClass or $colSetLabelOnclick or $colSetLabelStyle}
                {assign var="tmp" value="<"|cat:$colSetLabelEncl}
                {if $colSetLabelOnclick}{assign var="tmpOnclick" value=$Template->strReplace('[attr]', $attr, $colSetLabelOnclick)}{assign var="tmpOnclick" value=$Template->strReplace('[entity]', $entity->getEntityType(), $tmpOnclick)}{/if}
                {if $colSetLabelClass}{assign var="tmp" value=$tmp|cat:" class='"|cat:$colSetLabelClass|cat:"'"}{/if}
                {if $tmpOnclick}{assign var="tmp" value=$tmp|cat:' onclick="'|cat:$tmpOnclick|cat:'"'}{/if}
                {if $colSetLabelStyle}{assign var="tmp" value=$tmp|cat:" style='"|cat:$colSetLabelStyle|cat:"'"}{/if}
                {assign var="label" value=$tmp|cat:">"|cat:$label|cat:"</"|cat:$colSetLabelEncl|cat:">"}
                {/if}
								{$matrix->pushColumn($label, $currentRow)}
								{$cellTypeMatrix->pushColumn($colSetLabelCol, $currentRow)}
								{$cellIdMatrix->pushColumn("label", $currentRow)}
							{else}
								{assign var="conf" value=$Template->explode(' ', $view)}
								{assign var="tmp" value=$Template->startBuffering()}
								{foreach from=$Template->explode(' ', $attr) key="id" item="attr"}
								{assign var="view" value=$conf[$id]}
								{if $attr eq 'btn:submit' || $attr eq 'btn:reset' || $attr eq 'btn:button'}
								{assign var="cellId" value="buttons"}
								{assign var="btn" value=$Template->strReplace('btn:', '', $attr)}
								{$Template->renderOpen($gridTplName, 'input', $gridParams, $btn, 0)}{if $btn eq 'close'} onclick="window.close()"{/if} type="{if $btn eq 'close'}button{else}{$btn}{/if}"{if $view} value="{$Template->escapeHtmlQuotes($resources->getString($view))}"{/if} />
								{elseif $Util->beginsWith($attr, 'var:')}
								{assign var="tplVar" value=$Template->strReplace('var:', '', $attr)}
								{$Template->getVar($tplVar)}
								{assign var="cellId" value=$tplVar}
								{else}
								{assign var="cellId" value="attr"}
								{if $view}{$entity->renderAttribute($attr, $view)}{else}{$entity->getAttribute($attr)}{/if}
								{/if}
								{/foreach}
								{$matrix->pushColumn($Template->stopBuffering(), $currentRow)}
								{$cellIdMatrix->pushColumn($cellId, $currentRow)}
								{$cellTypeMatrix->pushColumn("td", $currentRow)}
							{/if}
							{$widthMatrix->pushColumn($cellWidth, $currentRow)}
              {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
							{$heightMatrix->pushColumn($cellHeight, $currentRow)}
							{assign var="started" value="1"}
						{/if}
					{/foreach}
					{if $widthPadding eq "2" && $widthPad}
						{$matrix->pushColumn(" ", $currentRow)}
						{if $colSetLabelPos eq $colIdx}
							{$cellTypeMatrix->pushColumn($colSetLabelCol, $currentRow)}
						{else}
							{$cellTypeMatrix->pushColumn("td", $currentRow)}
						{/if}
						{$cellIdMatrix->pushColumn("widthPad", $currentRow)}
						{$widthMatrix->pushColumn($widthPad, $currentRow)}
            {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
						{$heightMatrix->pushColumn("1", $currentRow)}
					{/if}
					{assign var="currentRow" value=$currentRow+1}
					{assign var="reduceRowCount" value=$reduceRowCount+1}
				{/foreach}
			{/if}
			{if $heightPadding eq "2" && $heightPad}
				{$matrix->pushColumn(" ", $currentRow)}
				{$cellTypeMatrix->pushColumn("td", $currentRow)}
				{$cellIdMatrix->pushColumn("heightPad", $currentRow)}
				{$widthMatrix->pushColumn($colSetWidth, $currentRow)}
        {$alignMatrix->pushColumn($colSetAlign, $currentRow)}
				{$heightMatrix->pushColumn($heightPad, $currentRow)}
			{/if}
			{assign var="currentRow" value=$currentRow-$reduceRowCount}
		{/if}
	{/foreach}
{/foreach}

{assign var="preAttrs" value=$gridParams->getTypeSubset('pre-attrs')}
{foreach from=$preAttrs->getParams() key=attr item=view}
{$entity->renderAttribute($attr, $view)}
{/foreach}

{if $gridParams->getParam('insertPre')}{assign var=insertPre value=$gridParams->getParam('insertPre')}{$File->toString($insertPre)}{/if}
{if $gridParams->getParam('insertTplPre')}{assign var=insertTplPre value=$gridParams->getParam('insertTplPre')}{$Template->display($insertTplPre)}{/if}
{if $gridParams->getParam('jsSrcInsertPre')}{assign var=jsSrcInsertPre value=$gridParams->getParam('jsSrcInsertPre')}<script type="text/javascript">{$File->toString($jsSrcInsertPre)}</script>{/if}
{if $gridParams->getParam('jsTplInsertPre')}{assign var=jsTplInsertPre value=$gridParams->getParam('jsTplInsertPre')}<script type="text/javascript">{$Template->display($jsTplInsertPre)}</script>{/if}
{if !$gridParams->getParam('noOpenTable')}{$Template->renderOpen($gridTplName, 'table', $gridParams)}{/if}
{if $gridParams->getParam('header')}
{$Template->renderOpen($gridTplName, 'tr', $gridParams)}
{$Template->renderOpen($gridTplName, 'th', $gridParams, 'header', 0)} colspan="{$matrix->getWidth()}">
{$entity->parseString($gridParams->getParam('header'))}
</th>
</tr>
{/if}
{foreach from=$Util->getArray($matrix->getHeight(),0) item=row}
{assign var="rowSetConfigId" value="row-set-config"|cat:$row}
{assign var="rowSetConfig" value="0"}
{if $gridParams->hasType($rowSetConfigId)}{assign var="rowSetConfig" value=$gridParams->getTypeSubset($rowSetConfigId)}{/if}
{assign var="renderRow" value="1"}
{if (!$rowSetConfig || !$defaultRowSetConfig) || (($rowSetConfig || $defaultRowSetConfig) && ($rowSetConfig->getParam('ignoreEmpty', '1') eq '1' || (!$rowSetConfig && $defaultRowSetConfig && $defaultRowSetConfig->getParam('ignoreEmpty', '1') eq '1')))}
{assign var="renderRow" value="0"}
{foreach from=$Util->getArray($matrix->getWidth($row),0) item=col}
{if $Template->trim($matrix->getValue($row,$col))}{assign var="renderRow" value="1"}{/if}
{/foreach}
{/if}
{if $renderRow}
{$Template->renderOpen($gridTplName, 'tr', $gridParams)}
{foreach from=$Util->getArray($matrix->getWidth($row),0) item=col}
{assign var="tag" value=$cellTypeMatrix->getValue($row,$col)}
{assign var="tagId" value=$cellIdMatrix->getValue($row,$col)}
{assign var="width" value=$widthMatrix->getValue($row,$col)}
{assign var="height" value=$heightMatrix->getValue($row,$col)}
{assign var="align" value=$alignMatrix->getValue($row,$col)}
{$Template->renderOpen($gridTplName, $tag, $gridParams, $tagId, 0)}{if $align} align="{$align}"{/if}{if $width >= -1 && $width != 1} colspan="{if $width == "0"}1{elseif $width == "-1"}{$matrix->getWidth()}{else}{$width}{/if}"{/if}{if $height >= -1 && $height != 1} rowspan="{if $height == "0"}1{elseif $height == "-1"}{$matrix->getHeight()}{else}{$height}{/if}"{/if}>
{$matrix->getValue($row,$col)}
</{$tag}>
{/foreach}
</tr>
{/if}
{/foreach}

{if !$gridParams->getParam('noCloseTable')}</table>{/if}

{if $gridParams->getParam('insertPost')}{assign var=insertPost value=$gridParams->getParam('insertPost')}{$File->toString($insertPost)}{/if}
{if $gridParams->getParam('insertTplPost')}{assign var=insertTplPost value=$gridParams->getParam('insertTplPost')}{$Template->display($insertTplPost)}{/if}
{if $gridParams->getParam('jsSrcInsertPost')}{assign var=jsSrcInsertPost value=$gridParams->getParam('jsSrcInsertPost')}<script type="text/javascript">{$File->toString($jsSrcInsertPost)}</script>{/if}
{if $gridParams->getParam('jsTplInsertPost')}{assign var=jsTplInsertPost value=$gridParams->getParam('jsTplInsertPost')}<script type="text/javascript">{$Template->display($jsTplInsertPost)}</script>{/if}

{assign var="postAttrs" value=$gridParams->getTypeSubset('post-attrs')}
{foreach from=$postAttrs->getParams() key=attr item=view}
{$entity->renderAttribute($attr, $view)}
{/foreach}

{if $gridParams->getParam('renderForm') eq '1'}</form>{/if}
{assign var="defaultColSetConfig" value='0'}
{assign var="defaultRowSetConfig" value='0'}
