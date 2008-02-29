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

YAHOO.filesManager = {
	getAjax: function(selectedIn){
		var callback = 
		{ 
			start:function(eventType, args){ 
			},
			success: function(o) {
				var domNode = document.getElementById('files-area');
				var response;
				if(o.responseText){
					response =  eval("("+o.responseText+")");
				}
				if(response){
	//				YAHOO.log(o.responseText);
					domNode.innerHTML = "";
					
					for(var i = 0; i < response.length; i++){
						var proj = new afile(response[i]);
						domNode.appendChild(proj.createHTML());
						if(response[i]['current']){
							YAHOO.filesManager.updateSelected(proj);
						}
						
						
					}
				}else{
					domNode.innerHTML = "";
				}
				YAHOO.projectStringsManager.getAjaxProjectStrings();
			},
			failure: function(o) {
				YAHOO.log('failed!');
			} 
		} 
		YAHOO.util.Connect.asyncRequest('GET', "callback/getFilesForProject.php", callback, null); 
	},

	getSelected: function(){
		return this.selected;
	},
	
	updateSelected: function(selec){
		if(this.selected){
			this.selected.unselect();
		}
		this.selected = selec;
		this.selected.selected();
	}
};

function afile(dataIn){
	this.filename = dataIn['name'];
	this.pct = dataIn['pct'];
	afile.superclass.constructor.call();
	this.initSelectable();
}
YAHOO.extend(afile,selectable);
afile.prototype.isSelected = function(){
 return (this == YAHOO.filesManager.selected);
}


afile.prototype.clicked = function(e){
	YAHOO.util.Event.stopEvent(e);
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
	var target = YAHOO.util.Event.getTarget(e);
	YAHOO.filesManager.updateSelected(this);
	YAHOO.util.Connect.asyncRequest('POST', "callback/setCurrentFile.php", callback, "file="+this.filename);
}
afile.prototype.createHTML = function(){
	this.domElem = document.createElement("li");
	var filename_display = this.filename;
	if(filename_display.length > 100) {
		filename_display = filename_display.substr(0,35) + "(...)" + filename_display.substr(filename_display.length - 50); 
	}
	this.domElem.innerHTML = filename_display + " (" + (this.pct > 0 ? new Number(this.pct).toFixed(1) : 0) + "%)";
	this.addEvents();
	return this.domElem;
}

