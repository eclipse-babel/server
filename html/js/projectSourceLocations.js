/*******************************************************************************
 * Copyright (c) 2013 IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Kit Lo (IBM) - [402192] Extract project source files from Git repositories for translation
 *******************************************************************************/

function showProjectSourceLocations(project_id, version){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			var domNode = document.getElementById('files-area');
			domNode.innerHTML = o.responseText;		
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.util.Connect.asyncRequest('POST', "callback/getProjectSourceLocations.php", callback, "project_id="+project_id+"&version="+version);
	this.setupCB();
}

function showPluginExcludePatterns(project_id, version){
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			var domNode = document.getElementById('patterns-area');
			domNode.innerHTML = o.responseText;		
		},
		failure: function(o) {
			YAHOO.log('failed!');
		}
	} 
	YAHOO.util.Connect.asyncRequest('POST', "callback/getExcludePatterns.php", callback, "project_id="+project_id+"&version="+version);
	this.setupCB();
}