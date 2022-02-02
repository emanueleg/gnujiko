<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-04-2016
 #PACKAGE: gstore
 #DESCRIPTION: Modifica movimento di magazzino
 #VERSION: 2.1beta
 #CHANGELOG: 09-04-2016 : Aggiunto vendor
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH."var/templates/glight/index.php");
include_once($_BASE_PATH."include/userfunc.php");
$template = new GLightTemplate("widget");
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("contactsearch");

$movInfo = array();
$docRefInfo = null;
$itemInfo = array();
$_COLTINT = array();
$_SIZMIS = array();

// Get movement
$ret = GShell("store movement-info -id '".$_REQUEST['id']."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
{
 $movInfo = $ret['outarr'];

 if($movInfo['ref_ap'] && $movInfo['ref_id'])
 {
  // get item info and variants
  $ret = GShell("dynarc item-info -ap '".$movInfo['ref_ap']."' -id '".$movInfo['ref_id']."' -extget variants", $_REQUEST['sessid'], $_REQUEST['shellid']);
  if(!$ret['error'])
  {
   $itemInfo = $ret['outarr'];
   if(is_array($itemInfo['variants']) && count($itemInfo['variants']['colors']))
   {
	for($c=0; $c < count($itemInfo['variants']['colors']); $c++)
	 $_COLTINT[] = $itemInfo['variants']['colors'][$c]['name'];
   }
   if(is_array($itemInfo['variants']) && count($itemInfo['variants']['tint']))
   {
	for($c=0; $c < count($itemInfo['variants']['tint']); $c++)
	 $_COLTINT[] = $itemInfo['variants']['tint'][$c]['name'];
   }

   if(is_array($itemInfo['variants']) && count($itemInfo['variants']['sizes']))
   {
	for($c=0; $c < count($itemInfo['variants']['sizes']); $c++)
	 $_SIZMIS[] = $itemInfo['variants']['sizes'][$c]['name'];
   }
   if(is_array($itemInfo['variants']) && count($itemInfo['variants']['dim']))
   {
	for($c=0; $c < count($itemInfo['variants']['dim']); $c++)
	 $_SIZMIS[] = $itemInfo['variants']['dim'][$c]['name'];
   }
   if(is_array($itemInfo['variants']) && count($itemInfo['variants']['other']))
   {
	for($c=0; $c < count($itemInfo['variants']['other']); $c++)
	 $_SIZMIS[] = $itemInfo['variants']['other'][$c]['name'];
   }
  }
 }

 if($movInfo['doc_ap'] && $movInfo['doc_id'])
 {
  $ret = GShell("dynarc item-info -ap '".$movInfo['doc_ap']."' -id '".$movInfo['doc_id']."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
  if(!$ret['error'])
   $docRefInfo = $ret['outarr'];
 }

 if($movInfo['vendor_id'])
 {
  $ret = GShell("dynarc item-info -ap rubrica -id '".$movInfo['vendor_id']."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
  if(!$ret['error']) $movInfo['vendor_name'] = $ret['outarr']['name'];
 }

}

// Get store list
$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$storelist = $ret['outarr'];
$_STORE_BY_ID = array();
for($c=0; $c < count($storelist); $c++)
 $_STORE_BY_ID[$storelist[$c]['id']] = $storelist[$c];

// Get causals
$_CAUSALS_CT = array('','UPLOAD','DOWNLOAD','TRANSFER');
$ret = GShell("dynarc item-list -ap storemovcausals -ct '".$_CAUSALS_CT[$movInfo['action']]."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
$causalList = $ret['outarr']['items'];
$_CAUSAL_BY_CODE = array();
for($c=0; $c < count($causalList); $c++)
 $_CAUSAL_BY_CODE[$causalList[$c]['code_str']] = $causalList[$c];

// Doc types
$_DOCTYPE_LIST = array();
if(file_exists($_BASE_PATH."GCommercialDocs/index.php"))
 $_DOCTYPE_LIST[] = array('ap'=>'commercialdocs', 'name'=>'Documento commerciale');
if(file_exists($_BASE_PATH."Tickets/index.php"))
 $_DOCTYPE_LIST[] = array('ap'=>'tickets', 'name'=>'Ticket');
if(file_exists($_BASE_PATH."Contracts/index.php"))
 $_DOCTYPE_LIST[] = array('ap'=>'contracts', 'name'=>'Contratto');
if(file_exists($_BASE_PATH."Commesse/index.php"))
 $_DOCTYPE_LIST[] = array('ap'=>'commesse', 'name'=>'Commessa');

$_DOCTYPE_BY_AP = array();
for($c=0; $c < count($_DOCTYPE_LIST); $c++)
 $_DOCTYPE_BY_AP[$_DOCTYPE_LIST[$c]['ap']] = $_DOCTYPE_LIST[$c];

$_TITLE = "";
switch($movInfo['action'])
{
 case '2' : $_TITLE.= "scarico"; break;
 case '3' : $_TITLE.= "movimentazione"; break;
 default : $_TITLE.= "carico"; break;
}

$template->Begin("Dettagli movimento");

$rightContents = "<span class='smalltext'>data e ora:</span> <input type='text' class='calendar' value='".date('d/m/Y', $movInfo['ctime'])."' id='cdate'/> <input type='text' class='edit' id='ctime' style='width:50px' placeholder='hh:mm' value='".date('H:i',$movInfo['ctime'])."'/>";

$template->Header("widget", "Dettagli ".$_TITLE, $rightContents);

//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0,0,10);
?>
<span class='smalltext'>Causale: </span>
<input type='text' class='dropdown' readonly='true' connect='causallist' id='causalselect' style='width:180px' retval="<?php echo $movInfo['causal']; ?>" value="<?php echo $_CAUSAL_BY_CODE[$movInfo['causal']]['name']; ?>"/>
<ul class='popupmenu' id='causallist'>
<?php
for($c=0; $c < count($causalList); $c++)
 echo "<li value='".$causalList[$c]['code_str']."'>".$causalList[$c]['name']."</li>";
?>
</ul>

<span class='smalltext' style='margin-left:25px'>Magazzino: </span>
<input type='text' class='dropdown' readonly='true' connect='storelist' id='storeselect' placeholder="seleziona un magazzino" style='width:180px' retval="<?php echo $movInfo['store_id']; ?>" value="<?php echo $_STORE_BY_ID[$movInfo['store_id']]['name']; ?>"/>
<ul class='popupmenu' id='storelist'>
<?php
for($c=0; $c < count($storelist); $c++)
 echo "<li value='".$storelist[$c]['id']."'><img src='".$_ABSOLUTE_URL."share/widgets/gstore/img/storeicon.png'/>".$storelist[$c]['name']."</li>";
?>
</ul>

<span class='smalltext' style='margin-left:25px'>Qt&agrave;: </span>
<input type='text' class='edit' style='width:50px' id='qty' value="<?php echo $movInfo['qty']; ?>"/>

<span class='smalltext' style='margin-left:25px'>U.M.: </span>
<input type='text' class='edit' style='width:50px' id='units' value="<?php echo $movInfo['units']; ?>"/>


<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div style="height:280px;width:760px;margin:10px;border:0px">
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
  <tr><td width='150'>
		<span class="smalltext gray">Cod: </span> 
		<?php
		 if($movInfo['ref_at'] && $movInfo['ref_ap'] && $movInfo['ref_id'])
		  echo "<span class='smalltext link blue' onclick='showArticle(\"".$movInfo['ref_at']."\",\"".$movInfo['ref_ap']."\",\"".$movInfo['ref_id']."\")'><b>".$movInfo['code']."</b></span>";
		 else
		  echo "<span class='smalltext black'><b>".$movInfo['code']."</b></span>";
		?>
	  </td>
	  <td colspan='3'>
		<span class="smalltext gray">Descrizione art.: </span> 
		<?php
		 if($movInfo['ref_at'] && $movInfo['ref_ap'] && $movInfo['ref_id'])
		  echo "<span class='smalltext link blue' onclick='showArticle(\"".$movInfo['ref_at']."\",\"".$movInfo['ref_ap']."\",\"".$movInfo['ref_id']."\")'><b>".$movInfo['name']."</b></span>";
		 else
		  echo "<span class='smalltext black'><b>".$movInfo['name']."</b></span>";
		?>
	  </td>
  </tr>

  <tr><td colspan='4'>&nbsp;</td></tr>

  <tr><td><span class="smalltext gray">Fornitore:</span></td>
	  <td colspan='3'><input type='text' class='contact' style='width:578px' id='vendor' placeholder="Digita il nome del fornitore" limit="5" refid="<?php echo $movInfo['vendor_id']; ?>" value="<?php echo $movInfo['vendor_name']; ?>"/></td>
  </tr>

  <tr><td colspan='4'>&nbsp;</td></tr>

  <tr>
	<td><span class="smalltext gray">Lotto: </span>
		<input type='text' class='edit' style='width:80px' id='lot' value="<?php echo $movInfo['lot']; ?>"/></td>
    <td><span class="smalltext gray">S.N.: </span>
		<input type='text' class='edit' style='width:150px' id='serialnumber' value="<?php echo $movInfo['serialnumber']; ?>"/></td>
	<td><span class="smalltext gray">Variante: </span>
		<input type='text' class='dropdown' style='width:120px' id='sizmis' connect='sizmislist' retval="<?php echo $movInfo['variant_sizmis']; ?>" value="<?php echo $movInfo['variant_sizmis']; ?>"/>
		<ul class='popupmenu' id='sizmislist'>
		 <?php
		  for($c=0; $c < count($_SIZMIS); $c++)
		   echo "<li value=\"".$_SIZMIS[$c]."\">".$_SIZMIS[$c]."</li>";
		 ?>
		</ul>
	</td>
	<td><span class="smalltext gray">Colore: </span>
		<input type='text' class='dropdown' style='width:120px' id='coltint' connect='coltintlist' retval="<?php echo $movInfo['variant_coltint']; ?>" value="<?php echo $movInfo['variant_coltint']; ?>"/>
		<ul class='popupmenu' id='coltintlist'>
		 <?php
		  for($c=0; $c < count($_COLTINT); $c++)
		   echo "<li value=\"".$_COLTINT[$c]."\">".$_COLTINT[$c]."</li>";
		 ?>
		</ul>
	</td>
	
  </tr>

  <tr><td colspan='4'>&nbsp;</td></tr>

  <tr><td><span class="smalltext gray">Documento di riferimento:</span></td>
	  <td colspan='3'>
		<input type='radio' name='docreftype' <?php if($docRefInfo) echo "checked='true'"; ?>/><span class="smalltext gray">interno</span>
		<input type='text' class='dropdown' style='width:194px' id='doctype' connect='doctypelist' placeholder="Tipologia" retval="<?php echo $movInfo['doc_ap']; ?>" value="<?php echo $_DOCTYPE_BY_AP[$movInfo['doc_ap']]['name']; ?>"/>
		<ul class='popupmenu' id='doctypelist'>
		 <?php
		  for($c=0; $c < count($_DOCTYPE_LIST); $c++)
		   echo "<li value='".$_DOCTYPE_LIST[$c]['ap']."'>".$_DOCTYPE_LIST[$c]['name']."</li>";
		 ?>
		</ul>
		<input type='text' class='search' style='width:315px' id='intdocref' placeholder="Cerca un documento" value="<?php echo $docRefInfo ? $docRefInfo['name'] : ''; ?>" ap="<?php echo $movInfo['doc_ap']; ?>" refid="<?php echo $movInfo['doc_id']; ?>" into="<?php echo $docRefInfo ? $docRefInfo['cat_id'] : ''; ?>" fields="code_str,name"/>
	  </td>
  </tr>
  <tr><td>&nbsp;</td>
	  <td colspan='3' style='padding-top:5px'>
	   <input type='radio' name='docreftype' <?php if(!$docRefInfo) echo "checked='true'"; ?>/><span class="smalltext gray">altro</span>
	   <input type='text' class='edit' style='width:514px;margin-left:14px' id='extdocref' value="<?php echo $movInfo['doc_ref']; ?>"/>
	  </td>
  </tr>

 </table>
</div>
<textarea class="textarea" style="width:780px;height:50px;margin-bottom:5px;resize:none" placeholder="Inserisci qui eventuali note" id="notes"><?php echo $movInfo['notes']; ?></textarea>
<?php
//-------------------------------------------------------------------------------------------------------------------//

$footer = "<input type='button' class='button-blue' value='Salva' onclick='SubmitAction()'/>";
$footer.= "<input type='button' class='button-gray' value='Chiudi' style='margin-left:5px' onclick='gframe_close()'/>";
$footer.= "<input type='button' class='button-red' value='Elimina' style='float:right' onclick='DeleteMovement()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var MOV_ID = "<?php echo $movInfo['id']; ?>";
var DOCREF_AP = "<?php echo $movInfo['doc_ap']; ?>";
var DOCREF_ID = "<?php echo $movInfo['doc_id']; ?>";


Template.OnInit = function(){
 this.initEd(document.getElementById('storeselect'), "dropdown").onchange = function(){};
 this.initEd(document.getElementById('causalselect'), "dropdown").onchange = function(){};
 this.initEd(document.getElementById('sizmis'), "dropdown");
 this.initEd(document.getElementById('coltint'), "dropdown");
 this.initEd(document.getElementById('doctype'), "dropdown").onchange = function(){
		 document.getElementById('intdocref').setAttribute('ap',this.getValue());
		 document.getElementById('intdocref').setAttribute('ct',"");
		 document.getElementById('intdocref').setAttribute('into',"");
		 document.getElementById('intdocref').value = "";
		 Template.initEd(document.getElementById('intdocref'), 'search');
		 document.getElementById('intdocref').focus();
		};
 this.initEd(document.getElementById('cdate'), 'date');
 
 if((DOCREF_AP != "") && parseFloat(DOCREF_ID))
  this.initEd(document.getElementById('intdocref'), 'search');

 this.initEd(document.getElementById("vendor"), "contactextended");
}

function SubmitAction()
{
 var ctime = document.getElementById('cdate').isodate;
 if(document.getElementById('ctime').value != "")
  ctime+= " "+document.getElementById('ctime').value;

 var storeId = document.getElementById('storeselect').getValue();
 if(!storeId) return alert("Devi selezionare un magazzino");
 var causal = document.getElementById('causalselect').getValue();
 var qty = document.getElementById('qty').value;
 if(!qty || !parseFloat(qty))
  return alert("La quantità non può essere minore o uguale a zero");
 var units = document.getElementById('units').value;
 var lot = document.getElementById('lot').value;
 var sn = document.getElementById('serialnumber').value;
 var colTint = document.getElementById('coltint').getValue();
 var sizMis = document.getElementById('sizmis').getValue();
 var notes = document.getElementById("notes").value;

 var docRefName = "";
 var docRefAp = "";
 var docRefId = 0;

 var vendorId = document.getElementById('vendor').getId();

 if(document.getElementsByName('docreftype')[1].checked == true)
  docRefName = document.getElementById('extdocref').value;
 else
 {
  if(document.getElementById('doctype').getValue() && document.getElementById('intdocref').value)
  {
   docRefAp = document.getElementById('doctype').getValue();
   docRefId = document.getElementById('intdocref').data ? document.getElementById('intdocref').data['id'] : document.getElementById('intdocref').getAttribute('refid');
  }
 }

 var sh = new GShell();
 sh.showProcessMessage("Salvataggio in corso", "Attendere prego, &egrave; in corso il salvataggio dei dati");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 gframe_close(o,true);
	}

 var cmd = "store edit-movement -id '"+MOV_ID+"' -ctime '"+ctime+"' -store '"+storeId+"' -causal `"+causal+"` -qty '"+qty+"' -units '"+units+"' -lot `"+lot+"` -serialnumber `"+sn+"` -coltint `"+colTint+"` -sizmis `"+sizMis+"` -note `"+notes+"` -docap '"+docRefAp+"' -docid '"+docRefId+"' -docref `"+docRefName+"` -vendorid '"+vendorId+"'";

 sh.sendCommand(cmd);
}

function showArticle(at, ap, id)
{
 gframe_hide();
 var sh = new GShell();
 sh.OnError = function(err){alert(err); gframe_show();}
 sh.OnOutput = function(){gframe_show();}

 switch(at)
 {
  case 'gmart' : sh.sendCommand("gframe -f gmart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gproducts' : sh.sendCommand("gframe -f gproducts/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gpart' : sh.sendCommand("gframe -f gpart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gmaterial' : sh.sendCommand("gframe -f gmaterial/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gbook' : sh.sendCommand("gframe -f gbook/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
 }
}

function DeleteMovement()
{
 if(!confirm("Sei sicuro di voler eliminare questo movimento?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 gframe_close(o,true);
	}
 sh.sendCommand("store delete-movement -id '"+MOV_ID+"'");
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

