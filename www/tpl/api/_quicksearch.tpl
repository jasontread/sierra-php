<script type="text/javascript">
<!--
var _qsAbortNext = false;
var _tipsIdx = 0;
var _tipIds;

function clearQsTips() {ldelim}
  document.getElementById('quicksearch_tips').innerHTML = '';
{rdelim}
function hideQsTips() {ldelim}
  setTimeout("document.getElementById('quicksearch_tips').style.display = 'none'", 100);
{rdelim}
function showQsTips() {ldelim}
  document.getElementById('quicksearch_tips').style.display = '';
{rdelim}
function qsTipsVisible() {ldelim}
  return document.getElementById('quicksearch_tips').style.display != 'none';
{rdelim}
function updateQsTips(evt) {ldelim}
  if (qsTipsVisible() && evt && (evt.keyCode == 40 || evt.keyCode == 38 || evt.keyCode == 13)) {ldelim}
    switch(evt.keyCode) {ldelim}
      // up
      case 38:
      // down
      case 40:
        if (_tipsIdx >= 0) document.getElementById(_tipIds[_tipsIdx]).style.backgroundColor = '';
        evt.keyCode == 38 ? _tipsIdx-- : _tipsIdx++;
        if (_tipsIdx > _tipIds.length) _tipsIdx = 0;
        if (_tipsIdx < 0) _tipsIdx = _tipIds.length - 1;
        if (_tipsIdx >= 0) document.getElementById(_tipIds[_tipsIdx]).style.backgroundColor = '#ddd';
        break;
      // enter
      case 13:
        if (_tipsIdx >= 0 && _tipIds[_tipsIdx]) {ldelim}
          document.location.replace('#' + _tipIds[_tipsIdx].substr(3));
        {rdelim}
        break;
    {rdelim}
    return;
  {rdelim}
  
  _tipsIdx = -1;
  _tipIds = [];
  var str = document.getElementById('quicksearch').value.toLowerCase();
  var matches = {ldelim}{rdelim}
  var match = false;
  
  if (str) {ldelim}
{if $constants}
  // check constants
{foreach from=$constants key=name item=props}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_constant_{$name}'] = "{$name}";
  {rdelim}
{/foreach}
{/if}

{if $src.attrs}
  // check attributes
{foreach from=$src.attrs key=id item=attr}
  if ("{$attr.name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_attr_{$attr.name}'] = "{$attr.name}";
  {rdelim}
{/foreach}
{/if}

{if $src.methods}
  // check methods
{foreach from=$src.methods key=id item=method}
  if ("{$method.name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_method_{$method.name}'] = "{$method.name}";
  {rdelim}
{/foreach}
{/if}

{if $classes}
  // check classes
{foreach from=$classes key=id item=name}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_class_{$name}'] = "{$name}";
  {rdelim}
{/foreach}
{/if}

{if $functions}
  // check functions
{foreach from=$functions key=id item=name}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_function_{$name}'] = "{$name}";
  {rdelim}
{/foreach}
{/if}

{if $dtds}
  // check dtds
{foreach from=$dtds key=id item=name}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_dtd_{$name}'] = "{$name}";
  {rdelim}
{/foreach}
{/if}

{if $packages}
  // check packages
{foreach from=$packages key=name item=package}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_package_{$name}'] = "{$name}";
  {rdelim}
{/foreach}
{/if}

{if $src.elements}
  // elements
{foreach from=$src.elements key=name item=element}
  if ("{$name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_element_{$name}'] = "{$name}";
  {rdelim}
{if $element.attributes}
{foreach from=$element.attributes item=attr}
  // {$name} element attributes
  if ("{$name|lower}".indexOf(str) != -1 || "{$name|lower}::{$attr.name|lower}".indexOf(str) != -1 || "{$attr.name|lower}".indexOf(str) != -1) {ldelim}
    match = true;
    matches['qs_element_{$name}_{$attr.name}'] = "{$name}::{$attr.name}";
  {rdelim}
{/foreach}
{/if}
{/foreach}
{/if}

  {rdelim}
  
  if (match) {ldelim}
    var html = '';
    for(var i in matches) {ldelim}
      _tipIds.push(i);
      html += '<div id="' + i + '" style="background-color: white; cursor: pointer;" onclick="document.location.replace(\'#\' + this.id.substr(3))">' + matches[i] + '</div>\n';
    {rdelim}
    document.getElementById('quicksearch_tips').innerHTML = html;
    showQsTips();
  {rdelim}
  else {ldelim}
    hideQsTips();
  {rdelim}
{rdelim}
-->
</script>
