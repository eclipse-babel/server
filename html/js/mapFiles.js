/*******************************************************************************
 * Copyright (c) 2009 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors: 
 *    Eclipse Foundation - initial API and implementation
*******************************************************************************/

function showMapFiles(project_id, version){
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
	YAHOO.util.Connect.asyncRequest('POST', "callback/getMapFiles.php", callback, "project_id="+project_id+"&version="+version);
	this.setupCB();
}

function setupCB(){
	// document.onmouseup = null;
	// YAHOO.util.Event.addListener("clear-btn","click",clearHints);
}