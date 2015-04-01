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
	//alert("sectionid");
	 
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange =function() {		
		if (xmlHttp.readyState == 4) {	
		//alert("esresrs");
			document.getElementById("cat_div").innerHTML=xmlHttp.responseText;
			if(catvar!="")
			document.getElementById("catid").value=catvar;
		}
		
	}	
	var url = live_url+"index.php?option=com_jek2story&view=jesubmit&task=getCat&did=" + did;
	
	xmlHttp.open("GET", url, true)
	xmlHttp.send(null);			
}

function display_slogan(dis)
{
	if(dis==0)
	{
	document.getElementById("slogan_label").style.display="none";
	document.getElementById("slogan").value="";
	}
	else
	document.getElementById("slogan_label").style.display="block";
}