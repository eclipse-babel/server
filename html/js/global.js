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

YAHOO.widget.Logger.enableBrowserConsole();


function hideThings(hide){
	switch(hide){
		case "language-area":
			YAHOO.util.Dom.setStyle("project-area","display","none");
		case "project-area":
			YAHOO.util.Dom.setStyle("string-choices","display","none");
		case "string-choices":
			YAHOO.util.Dom.setStyle("translation-area","display","none");
		case "translation-area":
	}
}

function showThings(hide){
	switch(hide){
		case "translation-area":
			YAHOO.util.Dom.setStyle("translation-area","display","block");
		case "string-choices":
			YAHOO.util.Dom.setStyle("translation-area","display","block");
		case "project-area":
			YAHOO.util.Dom.setStyle("string-choices","display","block");
		case "language-area":
			YAHOO.util.Dom.setStyle("project-area","display","block");
	}
}


function getAjaxProjectStrings(){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			YAHOO.log(o.responseText);
			var langDomNode = document.getElementById('string-area');
			langDomNode.innerHTML = o.responseText;
			YAHOO.util.Event.onAvailable("string-choices",setupSelectStringCB);
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('GET', "callback/getStringsforProject.php", callback, null); 
}

function setupSelectStringCB(){
	var langs  = YAHOO.util.Dom.getElementsByClassName("","li","string-choices");
	for(var i =0; i < langs.length; i++){
		YAHOO.util.Event.addListener(langs[i],"click",showTranslateStringForm);
	}
}




function showTranslateStringForm(e){
	showThings("translation-area");

	YAHOO.util.Event.stopEvent(e);
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			var langDomNode = document.getElementById('translation-area');
			langDomNode.innerHTML = "<br>"+o.responseText;
			YAHOO.util.Event.onAvailable("translation-form",setupTranslatFormCB);
			
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	var target = YAHOO.util.Event.getTarget(e);

	YAHOO.util.Connect.asyncRequest('POST', "callback/getCurrentStringTranslation.php", callback, "string_id="+target);
}

function setupTranslatFormCB(){
	YAHOO.util.Event.addListener("translation-form","submit",translationSumbit);
}

function translationSumbit(e){
	YAHOO.util.Event.stopEvent(e);
	var target = YAHOO.util.Event.getTarget(e);

	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			alert("thanks for the translation!");
			YAHOO.log(o.responseText);
//			showTranslateStringForm();
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('POST', "callback/setStringTranslation.php", callback, "string_id="+target.string_id.value+"&translation="+target.translation.value);
}



