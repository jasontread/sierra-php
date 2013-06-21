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
Base class used to display an attribute value in a form "input" text element 
with an ajax based tips selector. In order to utilize this view, you must 
specify an ajax-gateway-uri and 1 or more ajax services in your entity model. 
This template is typically used in conjunction with sra-attr.tpl


PARAMS:
Key            Type          Value/Default     Description

[attr]         [element tag] (value|[cycle])   see sra-attr.tpl - may also be 
                                               used to define the input "type" 
																							 if not the standard "text"
                                               
[cycle]        cycles        [csv cycle vals]  see sra-attr.tpl
                                               
ajaxService    ajaxTips                        the name of the ajax service that 
                                               can be used to generate the tips. 
                                               if the return "results" each 
                                               contain more than 1 associative 
                                               element, the 1st will be used as 
                                               the field value, and the second 
                                               as the tip. this may be useful in 
                                               cases where the user value will 
                                               be a name of some sort, while the 
                                               desired form value is the primary 
                                               key for that value. if only 1 
                                               value is contained in the 
                                               "results", that value will be 
                                               used for both the tip and form 
                                               value (MANDATORY)

fieldName                                      if the form name should not be 
                                               the name of the attribute, the 
																							 actual name should be specified 
																							 using this parameter
																							 
fieldNamePre                                   prefix to add to the input field name

fieldNamePost                                  postfix to add to the input field name
																							 
imbedValue                   (1|0)/1           whether or not to imbed the current 
                                               attribute value into the 
																							 input/textarea field
                                               
serviceParam   ajaxTips                        an optional param name to pass to 
                                               the ajax service (for global 
                                               services only) with the current 
                                               value of the input field
                                               
manualLoad     ajaxTips      (1|0)/0           whether or not the ajax tip load 
                                               (ajaxTipsAddSelector(...)) should
                                               be performed manually
                                               
minLength      ajaxTips                        the min field value length 
                                               before invoking the ajax service
                                               the default value is 1 character
                                               
invokeLimit    ajaxTips                        a limit to apply to the ajax 
                                               service invocation. the default 
                                               value is 20
                                               
invokeOffset   ajaxTips                        an offset to apply to the ajax 
                                               service invocation
                                               
skipFields                                     template variable, when true, the 
                                               fields will not be rendered (only 
                                               the javascript fucntions will be 
                                               rendered instead)
                                               
skipScriptTag                                  whether or not to render the 
                                               javascript script tag
                                               
tipsClass      ajaxTips                        the name of the class for the 
                                               tips container div. this class 
                                               style should be hidden by default
                                               the default style params for 
                                               this class are:
                                                  background: #eee;
                                                  border: 1px solid #656565;
                                                  cursor: pointer;
                                                  margin: 0;
                                                  padding: 1px;
                                                  position: absolute;
                                                  visibility: hidden;
                                               [class] div
                                                  padding: 2px 4px 2px 4px;
                                               
tipsSelClass   ajaxTips                        the name of the class for 
                                               selected tips. the default  
                                               style params for this class are:
                                                  background: #8c8c8c;
                                                  color: #fff;
*}

{* include javascript methods *}
{if !$Template->getVar('ajaxTipsCodeRendered')}
{$Template->assign('ajaxTipsCodeRendered', '1')}
{if !$skipScriptTag}
<script type="text/javascript">
<!--
{/if}
var ajaxTipsLastRequest;
// {ldelim}{ldelim}{ldelim} ajaxTipsHandleReturn
/**
 * handles ajax request responses
 * @access  public
 * @return void
 */
ajaxTipsHandleReturn = function() {ldelim}
  // if (ajaxTipsLastRequest) {ldelim} OS.setOsTitle("RESPONSE: " + ajaxTipsLastRequest.responseText + " READY STATE: " + ajaxTipsLastRequest.readyState); {rdelim}
  if (ajaxTipsLastRequest.readyState==4 || ajaxTipsLastRequest.readyState=="complete") {ldelim}
    //alert(ajaxTipsLastRequest.responseText);
    eval('response=' + ajaxTipsLastRequest.responseText);
    if (response) {ldelim}
      var input = document.getElementById(response.requestId);
      if (input && input._focused && !input._isHidden()) {ldelim}
        input.ajaxTipsLimit = response.limit;
        if (response.status == 'success' && ajaxTipsGetArrayLength(response.response) > 0 && (response.requestId1 == input.value || input.value.indexOf(response.requestId1) == 0 || response.requestId.indexOf(input.value) == 0)) {ldelim}
          input.resultCount = response.count;
          input.suggestions = response.response;
          input.suggestionsText = response.requestId1;
          ajaxTipsShow(input, input.tipsDiv, input.suggestions);
        {rdelim}
        else {ldelim}
          input.tipsDiv.hide();
        {rdelim}
      {rdelim}
    {rdelim}
  {rdelim}
{rdelim};
// {rdelim}{rdelim}{rdelim}

    
// {ldelim}{ldelim}{ldelim} ajaxTipsEncode
/**
 * url encodes str
 * @param String str the string to encode
 * @access  public
 * @return void
 */
ajaxTipsEncode = function(str) {ldelim}
  if (!str.replace) {ldelim}
    return str;
  {rdelim}
  str = escape(str);
  str = str.replace(new RegExp('\\+', "gim"), '%2B');
  str = str.replace(new RegExp('\\/', "gim"), '%2F');
  return str;
{rdelim};
// {rdelim}{rdelim}{rdelim}


// {ldelim}{ldelim}{ldelim} ajaxTipsGetArrayLength
/**
 * returns the length of an array regardless of whether or not it is associative
 * @param Array arr the array to return the length for
 * @access  public
 * @return void
 */
ajaxTipsGetArrayLength = function(arr) {ldelim}
  if (arr && arr.length) {ldelim}
    return arr.length;
  {rdelim}
  var counter = 0;
  if (arr) {ldelim}
    for(var i in arr) {ldelim}
      counter++;
    {rdelim}
  {rdelim}
  return counter;
{rdelim};
// {rdelim}{rdelim}{rdelim}


// {ldelim}{ldelim}{ldelim} ajaxTipsGetXmlHttpObject
/**
 * returns a cross browser compliant XMLHttpRequest object
 * @param Object handler the handler function for the XMLHttpRequest
 * @access  public
 * @return void
 */
ajaxTipsGetXmlHttpObject = function(handler) {ldelim}
  var objXmlHttp=null;
  
  if (navigator.userAgent.indexOf("MSIE")>=0) {ldelim} 
    var strName="Msxml2.XMLHTTP";
    if (navigator.appVersion.indexOf("MSIE 5.5")>=0) {ldelim}
      strName="Microsoft.XMLHTTP";
    {rdelim} 
    try {ldelim} 
      objXmlHttp=new ActiveXObject(strName);
      objXmlHttp.onreadystatechange=handler;
      return objXmlHttp;
    {rdelim} 
    catch(e) {ldelim} 
      return;
    {rdelim} 
  {rdelim} 
  if (navigator.userAgent.indexOf("Mozilla")>=0) {ldelim}
    objXmlHttp=new XMLHttpRequest();
    objXmlHttp.onload=handler;
    objXmlHttp.onerror=handler;
    return objXmlHttp;
  {rdelim}
{rdelim};
// {rdelim}{rdelim}{rdelim}


// {ldelim}{ldelim}{ldelim} ajaxTipsShow
/**
 * shows the tips currently relevant to input in tipsDiv
 * @param Object input the text input component
 * @param Object tipsDiv the div that should contain the tips
 * @param String[] suggestions an array of suggestions to match
 * @access  public
 * @return void
 */
ajaxTipsShow = function(input, tipsDiv, suggestions) {ldelim}
  input = typeof(input) == 'object' ? input : document.getElementById(input);
  if (input) {ldelim}
    tipsDiv = typeof(tipsDiv) == 'object' ? tipsDiv : document.getElementById(tipsDiv);
    var tips = new Array();
    if (suggestions && ajaxTipsGetArrayLength(suggestions) > 0 && input.value != '') {ldelim}
      var text = input.value;
      text = text.toLowerCase();
      tipsDiv.numTips = 0;
      for(var i in suggestions) {ldelim}
        var keys = new Array();
        if (!suggestions[i].toLowerCase) {ldelim}
          for(key in suggestions[i]) {ldelim}
            keys.push(key);
          {rdelim}
        {rdelim}
        var base = keys.length == 0 ? suggestions[i] : suggestions[i][keys[keys.length == 2 ? 1 : 0]];
        if (base) {ldelim}
          var val = base.toLowerCase();
          if (input.skipFilter || (val.indexOf(text) !== -1 && val.length >= text.length) || (i && i.toLowerCase && i.toLowerCase() == text)) {ldelim}
            tips[i] = base;
            tipsDiv.numTips++;
          {rdelim}
        {rdelim}
      {rdelim}
      var inputRef = 'document.getElementById(\'' + input.id + '\')';
      var suggestHtml = '';
      for(var i in tips) {ldelim}
        suggestHtml += '<div id="' + tipsDiv.id + i + '" onmouseover="this.parentNode.lastIdx=\'' + i + '\';" onclick="' + inputRef + '.selectCurrentTip();"' + (tipsDiv.tipsClass ? '' : ' style="padding: 2px 4px 2px 4px;"') + '>' + tips[i] + "</div>\n";
      {rdelim}
      // add more/less
      if (input.showPrevious() || input.showNext()) {ldelim}
        var str = '{$resources->getString('ajax-tips.results')} <strong>' + (input.ajaxTipsOffset + 1) + '</strong> - <strong>';
        str += ((input.ajaxTipsOffset + input.ajaxTipsLimit < input.resultCount ? input.ajaxTipsOffset + input.ajaxTipsLimit : input.resultCount)) + '</strong> {$resources->getString('ajax-tips.of')} <strong>';
        str += input.resultCount + '</strong> (' + (input.showPrevious() ? '{$resources->getString('ajax-tips.left')}' : '');
        str += (input.showPrevious() && input.showNext() ? ', ' : '') + (input.showNext() ? '{$resources->getString('ajax-tips.right')}' : '') + ')';
        suggestHtml += '<div' + (tipsDiv.tipsClass ? '' : ' style="padding: 2px 4px 2px 4px;"') + ' style="font-size: smaller">' + str + '</div>\n';
      {rdelim}
      tipsDiv.innerHTML = suggestHtml;
    {rdelim}
    tipsDiv.tips = tips;
    tipsDiv.tipKeys = new Array();
    var tmp = "";
    for(var i in tips) {ldelim}
      tipsDiv.tipKeys.push(i);
      tmp += " " + i;
    {rdelim}
    tipsDiv.refresh();
  {rdelim}
{rdelim};
// {rdelim}{rdelim}{rdelim}


// {ldelim}{ldelim}{ldelim} addTipsSelector
/**
 * this method adds tip/suggestion capabilities to the input box specified
 * @param Object input the text input component
 * @param int limit the ajax service invocation limit
 * @param int minSize the min character length before showing tips
 * @param int offset the initial ajax service invocation offset. if 'moreLess' 
 * is true, this value will change as the user navigates through the results
 * @param String service the name of the ajax service
 * @param String serviceParam an optional param name to pass to the ajax service 
 * with the current value of the input field
 * @param String tipsSelClass the naminput.ajaxTipsOffsete of the selected tip class
 * @param Object tipsDiv the div that should contain the tips
 * @param String loadingImg optional image that should be displayed while the 
 * tips are loading
 * @param String loadingTxt optional text that should be displayed (to the right 
 * of loadingImg if specified) while the tips are loading
 * @param Object onEnterTarget the object containing the onEnterMethod
 * @param String onEnterMethod an optional event that will be triggered ONLY 
 * after the user has selected an item and pressed enter. the signature for this 
 * method should be onEnterMethod(selectedId : String, selectedValue : String) : void
 * @param boolean skipFilter whether or not to skip filtering of the results 
 * when they are returned (by default results are filtered so only those results 
 * that contain the text in the input box at any given moment will be displayed)
 * @params mixed params any additional ajax invocation params. if this is an 
 * object with the method 'getAjaxTipsParams', that method will be invoked 
 * in real-time whenever the ajax service lookup is invoked. this method should 
 * have the following signature: getAjaxTipsParams(input : Object) : params (hash).
 * alternatively, this parameter may be a reference to a function with the same 
 * signature
 * @params boolean moreLess whether or not to include more/less links if the 
 * search results are greater than the # of results is greater then limit
 * @access  public
 * @return void
 */
ajaxTipsAddSelector = function(input, limit, minSize, offset, service, serviceParam, tipsClass, tipsSelClass, tipsDiv, valueInput, loadingImg, loadingTxt, onEnterTarget, onEnterMethod, skipFilter, params, moreLess) {ldelim}
  input = typeof(input) == 'object' ? input : document.getElementById(input);
  tipsDiv = typeof(tipsDiv) == 'object' ? tipsDiv : document.getElementById(tipsDiv);
  valueInput = typeof(valueInput) == 'object' ? valueInput : document.getElementById(valueInput);
  
  if (!input.id) input.id = Math.round(Math.random() * 100000);
  if (!tipsDiv.id) tipsDiv.id = Math.round(Math.random() * 100000);
  if (!valueInput.id) valueInput.id = Math.round(Math.random() * 100000);
  
  input.ajaxTipsLimit = limit;
  input.ajaxTipsMinSize = minSize;
  input.ajaxTipsBaseOffset = offset;
  input.ajaxTipsOffset = offset;
  input.ajaxTipsReqCounter = 0;
  input.ajaxTipsService = service;
  input.ajaxTipsServiceParam = serviceParam;
  input.ajaxTipsParams = params;
  input.ajaxTipSuggestions = new Array();
  input.tipsDiv = tipsDiv.id ? tipsDiv : document.getElementById(tipsDiv);
  input.valueInput = valueInput;
  input.loadingImg = loadingImg;
  input.loadingTxt = loadingTxt;
  input.onEnterTarget = onEnterTarget;
  input.onEnterMethod = onEnterMethod;
  input.skipFilter = skipFilter;
  input.moreLess = moreLess;
  tipsDiv.tipsClass = tipsClass;
  tipsDiv.tipsSelClass = tipsSelClass;
  if (input && tipsDiv) {ldelim}
    /**
     * returns true if an item is currently selected
     * @return boolean
     */
    input.isSelected = function() {ldelim}
      return this._baseVal && this.value == this._baseVal;
    {rdelim};
    /**
     * returns the currently selected id
     * @return string
     */
    input.getSelectedId = function() {ldelim}
      return this.isSelected ? this.valueInput.value : null;
    {rdelim};
    /**
     * returns the currently selected value
     * @return string
     */
    input.getSelectedValue = function() {ldelim}
      return this.isSelected ? this.value : null;
    {rdelim};
    /**
     * whether or not to show the previous link
     * @return void
     */
    input.showPrevious = function() {ldelim}
      return this.moreLess && this.resultCount && this.resultCount > this.ajaxTipsLimit && this.ajaxTipsOffset > 0;
    {rdelim};
    /**
     * navigates to the previous results page
     * @return void
     */
    input.previous = function() {ldelim}
      if (this.showPrevious()) {ldelim}
        this.ajaxTipsOffset = this.ajaxTipsOffset > this.ajaxTipsLimit ? this.ajaxTipsOffset - this.ajaxTipsLimit : 0;
        this.showTips(false, true, 250);
      {rdelim}
    {rdelim};
    /**
     * whether or not to show the next link
     * @return void
     */
    input.showNext = function() {ldelim}
      return this.moreLess && this.resultCount && this.resultCount > this.ajaxTipsLimit && (this.ajaxTipsOffset + this.ajaxTipsLimit) < this.resultCount;
    {rdelim};
    /**
     * navigates to the next results page
     * @return void
     */
    input.next = function() {ldelim}
      if (this.showNext()) {ldelim}
        this.ajaxTipsOffset = this.ajaxTipsOffset + this.ajaxTipsLimit;
        this.showTips(false, true, 250);
      {rdelim}
    {rdelim};
    
    if (input.onkeyup) {ldelim} input.onkeyup1 = input.onkeyup; {rdelim}
    input.onkeyup = function(evt) {ldelim}
      if (evt && !this.abortNext) {ldelim}
        switch(evt.keyCode) {ldelim}
          // previous
          case 37: 
            if (this.tipsDiv.isVisible()) {ldelim}
              this.previous();
              this.abortNext = true;
            {rdelim}
            break;
          // next
          case 39: 
            if (this.tipsDiv.isVisible()) {ldelim}
              this.next();
              this.abortNext = true;
            {rdelim}
            break;
          // down
          case 40: 
            this.tipsDiv.down();
            this.abortNext = true;
            break;
          // up
          case 38: 
            this.tipsDiv.up();
            this.abortNext = true;
            break;
          // skip keys (enter)
          case 13:
            break;
          // tab key (lose focus)
          case 9:
            this._focused = false;
            break;
          default:
            this._focused ? this.showTips() : this._focused = true;
            break;
        {rdelim}
        // solves safari issue
        if (this.abortNext) {ldelim}
          setTimeout("var input = document.getElementById('" + this.id + "'); if (input) {ldelim} input.abortNext=false; {rdelim}", 1);
        {rdelim}
      {rdelim}
      if (this.onkeyup1) {ldelim} this.onkeyup1(evt); {rdelim}
    {rdelim};
    input.selectCurrentTip = function(noFocus) {ldelim}
      var idx = this.tipsDiv.getIdx();
      // OS.setOsTitle("SET VALUE TO IDX " + idx);
      if (idx != null && this.suggestions[idx]) {ldelim}
        var suggestion = this.suggestions[idx];
        var keys = new Array();
        if (!this.suggestions[idx].toLowerCase) {ldelim}
          for(key in this.suggestions[idx]) {ldelim}
            keys.push(key);
          {rdelim}
        {rdelim}
        this.value = keys.length == 0 ? this.suggestions[idx] : this.suggestions[idx][keys[keys.length == 2 ? 1 : 0]];
        this._baseVal = this.value;
        this.valueInput.value = keys.length == 0 ? idx : this.suggestions[idx][keys[0]];
        // OS.setOsTitle("SET VALUE TO: " + this.valueInput.value);
      {rdelim}
      this.tipsDiv.hide();
      if (!noFocus) {ldelim}
        this.ignoreFocus = true;
        setTimeout("document.getElementById('" + this.id + "').focus()", 1);
      {rdelim}
    {rdelim};
    if (input.onkeypress) {ldelim} input.onkeypress1 = input.onkeypress; {rdelim}
    input.onkeypress = function(evt) {ldelim}
      var ret = true;
      if (evt && evt.keyCode == 13) {ldelim} 
        if (!this.tipsDiv.isVisible() && this.onEnterTarget && this.onEnterMethod && this.onEnterTarget[this.onEnterMethod] && this.isSelected()) {ldelim}
          this.onEnterTarget[this.onEnterMethod](this.getSelectedId, this.getSelectedValue);
        {rdelim}
        this.selectCurrentTip();
        this.ignoreFocus = true;
        ret = false;
      {rdelim}
      if (this.onkeypress1) {ldelim} this.onkeypress1(evt); {rdelim}
      return ret;
    {rdelim};
    if (input.onblur) {ldelim} input.onblur1 = input.onblur; {rdelim}
    input.onblur = function() {ldelim}
      this._focused = false; 
      if (this.tipsDiv.isVisible()) {ldelim}
        this.selectCurrentTip(true);
      {rdelim}
      if (this != this.valueInput) {ldelim} this.valueInput.value = this.valueInput.value && (!this._baseVal || this.value == this._baseVal) ? this.valueInput.value : ""; {rdelim}
      setTimeout("var input = document.getElementById('" + this.id + "'); if (input) {ldelim} input.tipsDiv.hide(); {rdelim}", 1000);
      // OS.setOsTitle("SET VALUE1 TO: " + this.valueInput.value + " base: " + this._baseVal);
      if (this.onblur1) {ldelim} this.onblur1(); {rdelim}
    {rdelim};
    if (input.onfocus) {ldelim} input.onfocus1 = input.onfocus; {rdelim}
    input.onfocus = function() {ldelim}
      this._focused = true;
      if (!this.ignoreFocus && !this.tipsDiv.isVisible()) {ldelim}
        this.showTips();
      {rdelim}
      this.ignoreFocus = false;
      if (this.onfocus1) {ldelim} this.onfocus1(); {rdelim}
    {rdelim};
    input.showTips = function(noQueue, force, timer) {ldelim}
      if (this._isHidden()) {ldelim} return; {rdelim}
      
      if (!noQueue) {ldelim}
        if (this.queueTimer) {ldelim} 
          clearTimeout(this.queueTimer);
          this.queueTimer = null;
        {rdelim}
        this.queueTimer = setTimeout("document.getElementById('" + this.id + "').showTips(true, " + (force ? 'true' : 'false') + ")", timer ? timer : 1000);
      {rdelim}
      else if (force || this.value.length >= this.ajaxTipsMinSize) {ldelim}
        this.queueTimer = null;
        // need new suggestions
        if (force || ((!this._baseVal || this.value != this._baseVal) && (!this.suggestionsText || ((this.ajaxTipsServiceParam || this.ajaxTipsParams) && ((this.skipFilter && this.value != this.suggestionsText) || this.value.toLowerCase().indexOf(this.suggestionsText) == -1 || ajaxTipsGetArrayLength(this.suggestions) == this.ajaxTipsLimit))))) {ldelim}
          if (!force) {ldelim} 
            this.ajaxTipsOffset = this.ajaxTipsBaseOffset;
          {rdelim}
          this.tipsDiv.idx = null;
          this.ajaxTipsReqCounter++;
          this.suggestionsText = this.value.toLowerCase();
          if (this.loadingImg || this.loadingTxt) {ldelim}
            this.tipsDiv.innerHTML = '<div style="padding: 5px">' + (this.loadingImg ? '<img alt="' + (this.loadingTxt ? this.loadingTxt : '') + '" src="' + this.loadingImg + '" style="float: left" />' : '') + (this.loadingTxt ? '<span style="margin-left: 5px">' + this.loadingTxt + '</span>' : '') + '</div>';
            this.tipsDiv.style.visibility = "visible";
          {rdelim}
          else {ldelim}
            this.tipsDiv.hide();
          {rdelim}
          ajaxTipsLastRequest = ajaxTipsGetXmlHttpObject();
          var request = '<ws-request key="' + this.ajaxTipsService + '" limit="' + this.ajaxTipsLimit + '"' + (this.ajaxTipsOffset ? ' offset="' + this.ajaxTipsOffset + '"' : '') + ">";
          request += this.ajaxTipsServiceParam ? '\n  <ws-param key="' + this.ajaxTipsServiceParam + '"><![CDATA[' + this.value + ']]></ws-param>' : '';
          
          var ajaxTipsParams = this.ajaxTipsParams && this.ajaxTipsParams.getAjaxTipsParams ? this.ajaxTipsParams.getAjaxTipsParams(this) : (typeof(this.ajaxTipsParams) == 'function' ? this.ajaxTipsParams(this) : this.ajaxTipsParams);
          if (ajaxTipsParams) {ldelim}
            for(var i in ajaxTipsParams) {ldelim}
              request += '\n  <ws-param key="' + i + '"><![CDATA[' + ajaxTipsParams[i] + ']]></ws-param>';
            {rdelim}
          {rdelim}
          request += '\n</ws-request>';
          ajaxTipsLastRequest.inputValue = this.value;
          ajaxTipsLastRequest = ajaxTipsGetXmlHttpObject(ajaxTipsHandleReturn);
          ajaxTipsLastRequest.open("GET", "{$Template->getWsGatewayUri()}?ws-app={$Controller->getCurrentAppId()}&ws-request-id=" + ajaxTipsEncode(this.id) + "&ws-request-id1=" + ajaxTipsEncode(this.value) + "&ws-request-xml=" + ajaxTipsEncode(request), true);
          ajaxTipsLastRequest.send(null);
          // OS.setOsTitle("SENT REQUEST " + this.ajaxTipsReqCounter + (this.suggestions ? " num suggestions: " + ajaxTipsGetArrayLength(this.suggestions) : ""));
        {rdelim}
        // existing suggestions will work
        else if (ajaxTipsGetArrayLength(this.suggestions) > 0) {ldelim}
          // OS.setOsTitle("Filter existing suggestions");
          ajaxTipsShow(this, this.tipsDiv, this.suggestions);
        {rdelim}
      {rdelim}
      else {ldelim}
        if (this.queueTimer) {ldelim} 
          clearTimeout(this.queueTimer);
        {rdelim}
        this.queueTimer = null;
        this.tipsDiv.hide();
      {rdelim}
    {rdelim};
    input._isHidden = function() {ldelim}
      var node = this;
      var hidden = false;
      do {ldelim}
        if (node.style && node.style.visibility == 'hidden' && node.style.opacity == 0) {ldelim}
          hidden = true;
          break;
        {rdelim}
        node = node.parentNode;
      {rdelim} while(node);
      return hidden;
    {rdelim};
    tipsDiv.down = function() {ldelim}
      if (this.tips) {ldelim}
        this.idx = this.idx == null || this.idx >= this.numTips || (this.idx + 1) >= this.numTips ? 0 : this.idx + 1;
        this.updateSelectedTip();
      {rdelim}
    {rdelim};
    tipsDiv.up = function() {ldelim}
      if (this.tips) {ldelim}
        this.idx = this.idx == null || this.idx >= this.numTips ? (this.idx == null ? this.numTips - 1 : 0) : (this.idx - 1 >= 0 ? this.idx - 1 : this.numTips - 1);
        this.updateSelectedTip();
      {rdelim}
    {rdelim};
    tipsDiv.updateSelectedTip = function(idx) {ldelim}
      this.idx = idx ? idx : this.idx;
      if (this.tips) {ldelim}
        for(var i in this.tips) {ldelim}
          var obj = document.getElementById(this.id + i);
          obj.className = i == this.tipKeys[this.idx] && this.tipsSelClass ? this.tipsSelClass : null;
          if (!this.tipsSelClass) {ldelim}
            obj.style.backgroundColor = i == this.tipKeys[this.idx] ? "#8c8c8c" : "transparent";
            obj.style.color = i == this.tipKeys[this.idx] ? "#fff" : "inherit";
          {rdelim}
          this.lastIdx = i == this.tipKeys[this.idx] ? i : this.lastIdx;
        {rdelim}
      {rdelim}
    {rdelim};
    tipsDiv.getIdx = function() {ldelim}
      return this.lastIdx;
    {rdelim};
    tipsDiv.hide = function() {ldelim}
      this.tips = null;
      this.idx = null;
      this.style.visibility = "hidden";
    {rdelim};
    tipsDiv.isVisible = function() {ldelim}
      return this.style.visibility == "visible";
    {rdelim};
    tipsDiv.refresh = function() {ldelim}
      if (this.tips && ajaxTipsGetArrayLength(this.tips) > 0) {ldelim} 
        this.style.visibility = "visible";
      {rdelim}
      else {ldelim}
        this.hide();
      {rdelim}
    {rdelim};
  {rdelim}
{rdelim};
// {rdelim}{rdelim}{rdelim}
{if !$skipScriptTag}
-->
</script>
{/if}
{/if}


{if !$skipFields}
{assign var="myParams" value=$Template->getVarByRef('params')}
{assign var="ajaxTipParams" value=$myParams->getTypeSubset('ajaxTips')}
{assign var="fieldName" value=$params->getParam('fieldName', $fieldName)}
{assign var="fieldNamePre" value=$params->getParam('fieldNamePre', '')}
{assign var="fieldNamePost" value=$params->getParam('fieldNamePost', '')}
{assign var="fieldName" value=$fieldNamePre|cat:$fieldName|cat:$fieldNamePost}
{if ($Util->isObject($attribute) && $displayVal)}{assign var="attribute" value=$displayVal}{/if}
{if $Util->methodExists($attribute, 'getPrimaryKey')}{assign var="attribute" value=$attribute->getPrimaryKey()}{/if}
<input id="{$fieldName}" name="{$fieldName}" type="hidden"{if $myParams->getParam('imbedValue', '1')} value="{$Template->escapeHtmlQuotes($attribute)}"{/if} />
{$Template->renderOpen($tplName, 'input', $myParams, '', 0)} id="{$fieldName}Displ" autocomplete="off" name="{$fieldName}Displ"{if $myParams->getParam('imbedValue', '1')} value="{$Template->escapeHtmlQuotes($attribute)}"{/if} />
<div id="{$fieldName}Tips" {if $ajaxTipParams->getParam('tipsClass')}class="{$ajaxTipParams->getParam('tipsClass')}"{else}style="background: #eee; border: 1px solid #656565; cursor: pointer; margin: 0; padding: 1px; position: absolute; visibility: hidden;"{/if}></div>
{if !$ajaxTipParams->getParam('manualLoad')}<script type="text/javascript">ajaxTipsAddSelector("{$fieldName}Displ", {if $ajaxTipParams->getParam("invokeLimit")}{$ajaxTipParams->getParam("invokeLimit")}{else}20{/if}, {if $ajaxTipParams->getParams("minLength")}{$ajaxTipParams->getParams("minLength")}{else}1{/if}, {if $ajaxTipParams->getParam("invokeOffset")}{$ajaxTipParams->getParam("invokeOffset")}{else}null{/if}, "{$ajaxTipParams->getParam("ajaxService")}", {if $ajaxTipParams->getParam("serviceParam")}"{$ajaxTipParams->getParam("serviceParam")}"{else}null{/if}, {if $ajaxTipParams->getParam("tipsClass")}"{$ajaxTipParams->getParam("tipsClass")}"{else}null{/if}, {if $ajaxTipParams->getParam("tipsSelClass")}"{$ajaxTipParams->getParam("tipsSelClass")}"{else}null{/if}, "{$fieldName}Tips", "{$fieldName}");</script>{/if}
{/if}
