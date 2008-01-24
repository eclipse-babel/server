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

YAHOO.projectStringsManager = {
	getAjaxProjectStrings : function(){

		if(!YAHOO.languageManager.getSelected() || 
			!YAHOO.projectManager.getSelected() ||
			!YAHOO.versionManager.getSelected() ||
			!YAHOO.filesManager.getSelected()
		  ){
			var domNode = document.getElementById('projecs-strings-area');
			domNode.innerHTML = "";
			return false;
		} 
	
		var callback = 
		{ 
			sp : this,
			start:function(eventType, args){ 
			},
			success: function(o) {
				var domNode = document.getElementById('projecs-strings-area');
				domNode.innerHTML = "";
				domNode.appendChild(this.sp.creatHTML());
				
				var ntDomNode = document.getElementById('not-translated');
				this.sp.tableDom = document.createElement("table")
				this.sp.tableDom.className = "translatable";
				this.sp.tableDom.cellSpacing = 0;
				this.sp.tableDom.width = "100%"
				ntDomNode.innerHTML = "";
				ntDomNode.appendChild(this.sp.tableDom);
				
				var trCount = 0;
				if(o.responseText){
					var response = eval("("+o.responseText+")");					
//					alert("about to start");
					for(var i = 0; i < response.length; i++){
						var proj = new projectString(response[i]);
						var tr = this.sp.tableDom.insertRow(trCount);
						proj.createHTML(tr);
//						YAHOO.log("tr:"+response[i].text);
						trCount++;
					}
				}
			},
			failure: function(o) {
				YAHOO.log('failed!');
			} 
		} 
		YAHOO.util.Connect.asyncRequest('GET', "callback/getStringsforProject.php", callback, null);
	},

	creatHTML : function(){
		this.tableDom = document.createElement("table");
		this.tableDom.cellSpacing = 0;
		this.tableDom.width = "100%"
		
		tr = this.tableDom.insertRow(0);
		tr.id = "translatable-strings-labels-area";
//		td = tr.insertCell(0);
//		td.innerHTML = "Label";
//		td.width = "10%";
		td = tr.insertCell(0);
		td.innerHTML = "String";
		td.width = "38%";
		td = tr.insertCell(1);
		td.innerHTML = "Last Translation";
		td.width = "50%";
		td = tr.insertCell(2);
		td.innerHTML = "Create On";
		td.width = "12%";
		
		return this.tableDom;
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



function projectString(dataIn){
//stringIdIn,textIn,createdOnIn,translationString){
//['stringId'],response[i]['text'],response[i]['created_on']

	projectString.superclass.constructor.call();
	this.initSelectable();

	this.data = dataIn;
}
YAHOO.extend(projectString,selectable);
projectString.prototype.isSelected = function(){
 return (this == YAHOO.projectStringsManager.selected);
}

projectString.prototype.clicked = function(e){
	showTranslateStringForm(this.data['stringId']);
	YAHOO.projectStringsManager.updateSelected(this);
}
projectString.prototype.createHTML = function(tr){
	this.domElem = tr;

//	td = tr.insertCell(0);
//	td.innerHTML = this.data['label'];
//	td.width = "10%";

	td = tr.insertCell(0);
	td.innerHTML = this.data['text'];
	td.width = "38%";
	
	td = tr.insertCell(1);
	var temp = this.data['translationString'] ? this.data['translationString'] : ''
	td.innerHTML = "<div style='width: 100%; overflow: hidden;'>"+temp+"</div>";
	td.width = "50%";
	
	td = tr.insertCell(2);
	td.innerHTML = this.data['createdOn'];
	td.width = "12%";
	
	this.addEvents();
}

