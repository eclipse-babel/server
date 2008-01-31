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


function selectable(){
}
selectable.prototype.initSelectable = function(){
	this.hoverColor = "LightSkyBlue";
	this.bgColor = "white";
	this.selectedColor = "lightblue";
}
selectable.prototype.mouseOver = function(){
//	YAHOO.util.Dom.addClass(this.domElem,"hovering");
	
	YAHOO.util.Dom.setStyle(this.domElem,"background",this.hoverColor);
}
selectable.prototype.mouseOut = function(){
	if(this.isSelected()){
		this.selected();
	}else{
//		YAHOO.util.Dom.removeClass(this.domElem,"hovering");
		YAHOO.util.Dom.setStyle(this.domElem,"background",this.bgColor);
	}
}
selectable.prototype.selected = function(){
	YAHOO.util.Dom.setStyle(this.domElem,"background",this.selectedColor);
}
selectable.prototype.unselect = function(){
	YAHOO.util.Dom.setStyle(this.domElem,"background",this.bgColor);
}

selectable.prototype.addEvents = function(){
	YAHOO.util.Event.addListener(this.domElem,"click",this.clicked,this,true);
	YAHOO.util.Event.addListener(this.domElem,"mouseover",this.mouseOver,this,true);
	YAHOO.util.Event.addListener(this.domElem,"mouseout",this.mouseOut,this,true);
}


YAHOO.tranlsation = new Object();

YAHOO.tranlsation.posted = false;


function showTranslateStringForm(stringIdIn){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			var langDomNode = document.getElementById('translation-form-container');
			langDomNode.innerHTML = o.responseText;
			YAHOO.util.Event.onAvailable("translation-form",setupTranslatFormCB);
			
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('POST', "callback/getCurrentStringTranslation.php", callback, "string_id="+stringIdIn);
}

function setupTranslatFormCB(){
	YAHOO.util.Event.addListener("translation-form","submit",translationSumbit);
}


function translationClear(){
	if(YAHOO.tranlsation.posted == true){
		YAHOO.tranlsation.posted = false;
	}else{
		var langDomNode = document.getElementById('translation-form-container');
		langDomNode.innerHTML = "";
	}
}

function translationSumbit(e){
	YAHOO.util.Event.stopEvent(e);
	var target = YAHOO.util.Event.getTarget(e);
		
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			YAHOO.projectStringsManager.getAjaxProjectStrings();
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	
	YAHOO.tranlsation.posted = true;
	
	var post = "string_id="+target.string_id.value+
			   "&translation="+sub(target.translation.value)+
			   "&translate_action="+e.explicitOriginalTarget.value;
	YAHOO.util.Connect.asyncRequest('POST', "callback/setStringTranslation.php", callback, post);
}

function sub(it){
	it = it.replace(/\+/g,"%2b");
	return it.replace(/&/g,"%26"); 
}
