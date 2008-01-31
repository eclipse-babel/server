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
	trCounter : 0,
	
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
				var values = new Object();
				values.cssID = "translatable-strings-labels-area";
				values.cssClass = "";
				values.string = "String";
				values.translation = "Last Translation";
				values.translator = "User";
				values.createdon = "Created On";
				this.tableDom = this.sp.createHTML(values)
				domNode.appendChild(this.tableDom);

				translationClear();
				
				if(o.responseText){
					var response = eval("("+o.responseText+")");					
					for(var i = 0; i < response.length; i++){
						var proj = new projectString(response[i]);
						proj.createHTML(this.tableDom);
					}
				}
			},
			failure: function(o) {
				YAHOO.log('failed!');
			} 
		} 
		YAHOO.util.Connect.asyncRequest('GET', "callback/getStringsforProject.php", callback, null);
	},
	
	createHTML : function(values,appenToDOm){
		var tableDom;
		var tr;
		if(typeof appenToDOm == "undefined"){
			tableDom = document.createElement("table");
			tableDom.cellSpacing = 0;
			tableDom.width = "100%";
			tr = tableDom.insertRow(0);
			this.trCounter = 1;
		}else{
			tableDom = appenToDOm;
			tr = tableDom.insertRow(this.trCounter);
			this.trCounter++;
		}
		
		tr.id =  values.cssID;//"translatable-strings-labels-area";
		tr.class =  values['cssClass'];//"translatable-strings-labels-area";
		td = tr.insertCell(0);
		td.innerHTML = values['string']//"String";
		td.width = "30%";
		td = tr.insertCell(1);
		td.innerHTML = values['translation']//"Last Translation";
		td.width = "50%";
		td = tr.insertCell(2);
		td.innerHTML = values['translator']//"User";
		td.width = "8%";
		td = tr.insertCell(3);
		td.innerHTML = values['createdon']//"Created On";
		td.width = "12%";
		
		if(typeof appenToDOm == "undefined"){
			return tableDom;
		}else{
			return tr;
		}
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
projectString.prototype.createHTML = function(tableDom){
	var values = new Object();
	values.cssID = "";
	values.cssClass = "";
	values.string = this.data['text'];
	var temp = this.data['translationString'] ? this.data['translationString'] : ''
	values.translation = "<div style='width: 100%; overflow: hidden;'>"+temp+"</div>";
	values.translator = this.data['translator'];
	values.createdon = this.data['createdOn'];
	
	var lineDome = YAHOO.projectStringsManager.createHTML(values,tableDom);
	this.domElem = lineDome;
	this.addEvents();
}

