var req;
var reqID;
var lang;
var projID   = 0;
var projIdx  = 0;
var projWid  = 0;
var entryID  = 0;
var entryIdx = 0;
var isDirty  = false;

function ratover(idx) {
  divItem = document.getElementById('myPdiv' + idx);
  if (divItem.className != "projSel")
    divItem.className = "graphOut";
}

function ratout(idx) {
  divItem = document.getElementById('myPdiv' + idx);
  if (divItem.className != "projSel")
    divItem.className = "graph";
  
}

function ratoverE(idx) {
  divItem = document.getElementById('myEdiv' + idx);
  if (divItem.className == "entryDone")
    divItem.className = "entryHi1";
  else if (divItem.className == "entryUnDone")
    divItem.className = "entryHi2";
}

function ratoutE(idx) {
  divItem = document.getElementById('myEdiv' + idx);
  if (divItem.className == "entryHi1")
    divItem.className = "entryDone";
  else if (divItem.className == "entryHi2")
    divItem.className = "entryUnDone";
}



function langChange(l) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  lang = l;
  resetEntryEdit(lang,0);
  resetEntryList(lang,0);
  resetProjectList(lang);
}


function selProj(id,idx) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  if (projIdx) {
    if (divItem = document.getElementById('myPdiv' + projIdx)) {
      divItem.className = "graph";
      divItem.style.width = projWid;
    }
  }
  projIdx = idx;
  projID  = id
  if (divItem = document.getElementById('myPdiv' + projIdx)) {
    projWid = divItem.style.width;
    divItem.className = "projSel";
    divItem.style.width = "220px";
  }
  resetEntryList(lang,projID);
  resetEntryEdit(lang,0);
}

function selEntry(id,idx) {
  if (isDirty) {
    if (confirm("Save changes to this entry?"))
      PostEntry();
  }
  if (entryIdx) {
    if (divItem = document.getElementById('myEdiv' + entryIdx)) {
      if ((divItem.className == "entrySel1") || (divItem.className == "entryDone")) 
        divItem.className = "entryDone";
      else 
        divItem.className = "entryUnDone";
    }
  }
  entryIdx = idx;
  if (divItem = document.getElementById('myEdiv' + entryIdx)) {
    if ((divItem.className == "entryDone") || (divItem.className == "entryHi1"))
      divItem.className = "entrySel1";
    else 
      divItem.className = "entrySel2";
    entryID = divItem.innerHTML;
  }
  resetEntryEdit(lang,1);    

}

function PostEntry() {
  ctrl1 = "";
  ctrl2 = "";
  if (divItem = document.getElementById('ctrl1'))
    ctrl1 = divItem.value;
  if (divItem = document.getElementById('ctrl2')) 
    ctrl2 = divItem.value;
  isDirty = false;
  loadXMLDoc("post_entry.php?lang=" + lang + "&proj=" + projID + "&entry=" + entryID + "&ctrl2=" + ctrl2 + "&ctrl1=" + ctrl1,4);
}



function resetEntryEdit(l,id) {
  if (id) {
    if (lang && projID && entryIdx && (divItem = document.getElementById('entryEdit')))
      divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
    loadXMLDoc("get_entry.php?lang=" + lang + "&proj=" + projID + "&entry=" + entryID,3);
  } 
  else {
    if (divItem = document.getElementById('entryEdit'))
      divItem.innerHTML = "";    
  }
  isDirty = false;
}


function resetEntryList(l,p) {
  entryID  = 0;
  entryIdx = 0;
  if (p == 0) {
    divItem = document.getElementById('entryDiv');
    divItem.innerHTML = "";
  }
  else {
    if (divItem = document.getElementById('entryDiv'))
      divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
    loadXMLDoc("get_entries.php?lang=" + l + "&proj=" + p,2);
  }
}





function resetProjectList(l) {
  projID = 0;
  if (divItem = document.getElementById('projDiv'))
    divItem.innerHTML = "<div style='height:489px;'><center><p /><br /><br /><br /><img src='/spinner.gif'><br />loading...</center>";
  loadXMLDoc("get_projects.php?lang=" + l,1);
}





function processReqChange() {
  if (req.readyState == 4) {
    if (req.status == 200) {
      if (reqID == 1) {
        if (divItem = document.getElementById('projDiv'))
        divItem.innerHTML = req.responseText;    
        
      }
      else if (reqID == 2) {
        if (divItem = document.getElementById('entryDiv'))
        divItem.innerHTML = req.responseText;    
      }
      else if (reqID == 3) {
        if (divItem = document.getElementById('entryEdit'))
        divItem.innerHTML = req.responseText;    
        
      }
      else if (reqID == 4) {
        
        
      }
    }
    else {
      ; //
    }
  }
}


function loadXMLDoc(url,id) {
  reqID = id;
 
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest();
    req.onreadystatechange = processReqChange;
    req.open('GET', url, true);
    req.send(null);
  }
  else if (window.ActiveXObject) {
    req = new ActiveXObject('Msxml2.XMLHTTP');
    if (req) {
      req.onreadystatechange = processReqChange;
      req.open('GET', url, true);
      req.send();
    }
  }
}


function dirty() {
  isDirty = true;
}

