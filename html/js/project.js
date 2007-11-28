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

function getAjaxProjects(){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			YAHOO.log(o.responseText);
			var domNode = document.getElementById('project-area');
			domNode.innerHTML = o.responseText;
			YAHOO.util.Event.onAvailable("project-choices",setupSelectProjectCB);
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('GET', "callback/getProjects.php", callback, null); 
}

function setupSelectProjectCB(){
	var langs  = YAHOO.util.Dom.getElementsByClassName("","li","project-choices");
	for(var i =0; i < langs.length; i++){
		YAHOO.log(langs[i].innerHTML);
		YAHOO.util.Event.addListener(langs[i],"click",setProjectPref);
	}
}

function setProjectPref(e){
	YAHOO.util.Event.stopEvent(e);
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			showCurrentProject(o.responseText);
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	var target = YAHOO.util.Event.getTarget(e)
	YAHOO.log(target)
	YAHOO.util.Connect.asyncRequest('POST', "callback/setCurrentProject.php", callback, "proj="+target);
}

function showCurrentProject(curProj){
	var display = '<h3>Current Project: '+curProj+' (<a id="change-proj" href="#">change)</h3>';	
	YAHOO.util.Event.onAvailable("change-lang",function(){ YAHOO.util.Event.addListener("change-proj","click",getAjaxProjects); });

	var langDomNode = document.getElementById('project-area');
	langDomNode.innerHTML = display;
}
