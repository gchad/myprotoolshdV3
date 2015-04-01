/**

* @package   JE K2 STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 



//++++++++++++++++++++++++++ Mail Section +++++++++++++++++++++++++++

var xmlHttp
//******************************** Check Browser Compability******************
function GetXmlHttpObject()
{
	
var xmlHttp=null;
try
 {
 // Firefox, Opera 8.0+, Safari
 xmlHttp=new XMLHttpRequest();
 }
catch (e)
 {
 //Internet Explorer
 try
  {
  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
  }
 catch (e)
  {
  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
 }
return xmlHttp;
}

function select_cate(did)
{
	
	if(did==0){
		alert('Please Select Category');
		return false;
	}else{
	var mylive_url = document.getElementById("live_url").value;
	var itemid = document.getElementById("k2itemid").value;
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange =function() {		
		if (xmlHttp.readyState == 4) {			
			//alert(xmlHttp.responseText);return false;
			document.getElementById("extrafield_data").style.display="block";
			document.getElementById("extrafield_data").innerHTML =xmlHttp.responseText;
			
			initExtraFieldsEditor();
			 $K2('img.calendar').each(function() {
                                inputFieldID = $K2(this).prev().attr('id');
                                imgFieldID = $K2(this).attr('id');
                                Calendar.setup({
                                    inputField : inputFieldID,
                                    ifFormat : "%Y-%m-%d",
                                    button : imgFieldID,
                                    align : "Tl",
                                    singleClick : true
                                });
                            });
		
		}
		
	}	
	var url = mylive_url+"index.php?tmpl=component&option=com_jek2story&view=jesubmit&task=getExtrafield&did=" + did + "&itemid="+itemid;
	
	xmlHttp.open("GET", url, true)
	xmlHttp.send(null);			
}
}
function cap_refresh()
{
	var mylive_url = document.getElementById("live_url").value;
	
	
	
	document.getElementById("default_cap_div").style.display='none';	
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange =function() {		
		if (xmlHttp.readyState == 4) {
			document.getElementById("refresh_cap_div").innerHTML=xmlHttp.responseText;
		}
	}	
	var url = mylive_url+"index.php?option=com_jek2story&view=jesubmit&task=refresh_captchacr&tmpl=component";
	xmlHttp.open("GET", url, true)
	xmlHttp.send(null);			
}

function update_status(nid,uid,itemno)
{
		var form = document.adminForm;	
		document.getElementById('option').value = 'com_jek2story';
		document.getElementById('task').value = 'published_story';
		document.getElementById('nid').value = nid;
		document.getElementById('uid').value = uid;
		document.getElementById('itemno').value = itemno;
		
		form.submit();
}

function get_item(cid)
{
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange =function() {		
		if (xmlHttp.readyState == 4) {			
			//alert(xmlHttp.responseText);
			document.getElementById("md_item_mydiv").innerHTML=xmlHttp.responseText;
			
		}
	}	
	var jelurl=document.getElementById("jelive_url").value;
	
	var url = jelurl+"index.php?tmpl=component&option=com_jek2story&view=itemlist&task=getitem&k2category="+cid+"&item_id1="+ajax_item_id;

	xmlHttp.open("GET", url, true)
	xmlHttp.send(null);			
}

