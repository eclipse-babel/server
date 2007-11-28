/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

function getAjaxLanguages(){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			YAHOO.log(o.responseText);
			var langDomNode = document.getElementById('language-area');
			langDomNode.innerHTML = o.responseText;
			YAHOO.util.Event.onAvailable("language-choices",setupSelectLanguageCB);
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('GET', "callback/getLanguages.php", callback, null); 
}

function setupSelectLanguageCB(){
	var langs  = YAHOO.util.Dom.getElementsByClassName("","li","language-choices");
	for(var i =0; i < langs.length; i++){
		YAHOO.util.Event.addListener(langs[i],"click",setLanguagePref);
	}
}

function setLanguagePref(e){
	YAHOO.util.Event.stopEvent(e);
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			showCurrentLanguage(o.responseText);
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	var target = YAHOO.util.Event.getTarget(e)
	YAHOO.util.Connect.asyncRequest('POST', "callback/setCurrentLangue.php", callback, "lang="+target);
}

function showCurrentLanguage(curLang){
	var display = '<h3>Current Language: '+curLang+' (<a id="change-lang" href="#">change)</h3>';	
	YAHOO.util.Event.onAvailable("change-lang",function(){ YAHOO.util.Event.addListener("change-lang","click",getAjaxLanguages); });

	var langDomNode = document.getElementById('language-area');
	langDomNode.innerHTML = display;
}
