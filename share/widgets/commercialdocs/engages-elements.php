<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-03-2016
 #PACKAGE: commercialdocs
 #DESCRIPTION: Impegna articoli su ordine,commessa,ecc...
 #VERSION: 2.1beta
 #CHANGELOG: 31-03-2016 : Integrato con interventi commesse.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$imgPath = $_ABSOLUTE_URL."share/widgets/commercialdocs/img/";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Engages Elements</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
if(!$_REQUEST['doctype'])
 $_REQUEST['doctype'] = "order";
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/engages-elements.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:600px;height:480px">
 <h3 class="header">Impegna articoli</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/templatedefault/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page" style="height:390px;overflow:auto;padding:10px">
 <!-- CONTENTS -->
 <span style='font-family:arial,sans-serif;font-size:14px;color:#3364C3;'><b>Impegna i seguenti articoli su</b></span>
 <select style="width:150px" id="docreftype" onchange="docRefTypeChanged(this)">
 <?php
  $docTypes = array("order"=>"Ordine", "intervreport"=>"Rapp. d&lsquo;intervento");
  if(file_exists($_BASE_PATH."Commesse/index.php"))
   $docTypes['commessa'] = "Commessa";
  while(list($k,$v)=each($docTypes))
  {
   echo "<option value='".$k."'".($k == $_REQUEST['doctype'] ? " selected='selected'>" : ">").$v."</option>";
  }
 ?>
 </select><br/>
 <?php
  $width = ($_REQUEST['doctype'] == "commessa") ? "360px" : "80%";
 ?>
 <input type='text' class='edit' style="width:<?php echo $width; ?>;margin-top:5px" id='docref' placeholder="Digita il n. del doc. di riferimento"/>
 <select id='comminterv' style="width:210px;<?php if($_REQUEST['doctype'] != 'commessa') echo 'display:none'; ?>"></select>

 <div class="gmutable" style="margin-top:20px;border-bottom:0px">
 <table id='doctable' class="gmutable" width="600" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='20'>&nbsp;</th>
	 <th id='code' width='80'>cod.</th>
	 <th id='description'>descrizione</th>
	 <th id='qty' width='40' style='text-align:center'>qt&agrave;</th>
 </tr>
 </table>
 </div>
 <!-- EOF CONTENTS -->
 </div>


 <div class="default-widget-footer">
  <span class="left-button blue" onclick="submit()">Conferma</span> 
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
 </div>

</div>

<script>
var tb = null;
var PARAMS = null;
var DOCTYPE = "<?php echo $_REQUEST['doctype']; ?>";

function bodyOnLoad(extraParams)
{
 PARAMS = extraParams;

 var div = document.getElementById('doctable').parentNode;
 div.style.height = div.parentNode.offsetHeight-90;
 div.style.width = div.offsetWidth;

 document.getElementById('doctable').style.display="";

 tb = new GMUTable(document.getElementById('doctable'));
 var docRefAP = "";
 var docRefCT = "";
 switch(DOCTYPE)
 {
  case 'order' : 		{docRefAP="commercialdocs"; docRefCT="ORDERS";} break;
  case 'intervreport' : {docRefAP="commercialdocs"; docRefCT="INTERVREPORTS";} break;
  case 'commessa' : 	{docRefAP="commesse"; docRefCT="";} break;
 }
 var docRefEd = EditSearch.init(document.getElementById('docref'), "dynarc item-find -ap '"+docRefAP+"' -ct '"+docRefCT+"' -field name `","` -limit 10 --order-by 'name ASC'", "id", "name", "items", true);
 docRefEd.onchange = function(){docRefChanged(this);}

 if(extraParams)
 {
  if(extraParams['xmlelements'])
  {
   var div = document.createElement('DIV');
   div.style.display = "none";
   div.innerHTML = extraParams['xmlelements'];
   document.body.appendChild(div);
   var elements = div.getElementsByTagName('ITEM');

   for(var c=0; c < elements.length; c++)
   {
    var r = tb.AddRow();
	r.cell['code'].setValue(elements[c].getAttribute('code'));
	r.cell['description'].setValue(elements[c].getAttribute('name'));
	r.cell['qty'].setValue(elements[c].getAttribute('qty'));				r.cell['qty'].style.textAlign='center';
   }
   document.body.removeChild(div);
  }
 }
}

function submit()
{
 var docType = document.getElementById("docreftype").value;
 var docRef = document.getElementById("docref");
 var docRefAP = "";
 var docRefID = 0;

 if(docRef.value && docRef.data)
  var docRefID = docRef.data['id'];

 switch(docType)
 {
  case 'order' : case 'intervreport' : docRefAP='commercialdocs'; break;
  case 'commessa' : docRefAP='commesse'; break;
 }

 var ret = new Array();
 ret['docrefap'] = docRefAP;
 ret['docrefid'] = docRefID;
 ret['docrefname'] = docRef.value;

 if(docType == "commessa")
 {
  var sel = document.getElementById('comminterv');
  ret['intervid'] = sel.value;
 }

 gframe_close("done!",ret);
}

function docRefTypeChanged(sel)
{
 var docRefAP = "";
 var docRefCT = "";

 switch(sel.value)
 {
  case 'order' : 		{docRefAP='commercialdocs'; docRefCT='ORDERS';} break;
  case 'intervreport' : {docRefAP='commercialdocs'; docRefCT='INTERVREPORTS';} break;
  case 'commessa' :		{docRefAP='commesse'; docRefCT='';} break;
 }

 if(sel.value == "commessa")
 {
  document.getElementById('docref').style.width = "360px";
  document.getElementById('comminterv').style.display = "";
 }
 else
 {
  document.getElementById('docref').style.width = "80%";
  document.getElementById('comminterv').style.display = "none";
 }

 var docRefEd = document.getElementById('docref');
 docRefEd.value = "";
 docRefEd.esHinst.startQry = "dynarc item-find -ap '"+docRefAP+"'"+(docRefCT ? " -ct '"+docRefCT+"'" : "")+" -field name `";
 docRefEd.esHinst.endQry = "` -limit 10 --order-by 'name ASC'";
}

function docRefChanged(ed)
{
 var sel = document.getElementById('comminterv');
 while(sel.options.length)
  sel.remove(0);

 var docType = document.getElementById("docreftype").value;

 if(!ed.data) return;
 if(docType != "commessa") return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items']) return;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a['items'][c]['id'];
	  opt.innerHTML = a['items'][c]['name'];
	  sel.appendChild(opt);
	 }

	}

 sh.sendCommand("dynarc item-list -ap commesseinterv -ct 'COMMESSA-"+ed.data['id']+"'");
}
</script>
</body></html>
<?php

