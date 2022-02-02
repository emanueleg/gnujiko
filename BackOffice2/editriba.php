<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice 2 - Edit RiBa
 #VERSION: 2.2beta
 #CHANGELOG: 15-04-2014 : Rimosso temporaneamente il tasto stampa.
 #TODO: Manca la stampa
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");
include_once($_BASE_PATH."include/company-profile.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("banksearch");
$template->includeCSS("editriba.css");

//-------------------------------------------------------------------------------------------------------------------//
$ret = GShell("backoffice riba-info -id '".$_REQUEST['id']."'");
if(!$ret['error'])
 $ribaInfo = $ret['outarr'];

$coName = $_COMPANY_PROFILE['name'];
$coAddr = $_COMPANY_PROFILE['addresses']['registered_office']['address'];
$coCity = $_COMPANY_PROFILE['addresses']['registered_office']['city'];
$coZip = $_COMPANY_PROFILE['addresses']['registered_office']['zip'];
$coProv = $_COMPANY_PROFILE['addresses']['registered_office']['prov'];
$coVatNum = $_COMPANY_PROFILE['vatnumber'];
$coTaxCode = $_COMPANY_PROFILE['taxcode'];

$coBankName = $ribaInfo['bank_name'];
$coBankSia = $ribaInfo['bank_sia'];
$coBankAbi = $ribaInfo['bank_abi'];
$coBankCab = $ribaInfo['bank_cab'];
$coBankCC = $ribaInfo['bank_cc'];

//-------------------------------------------------------------------------------------------------------------------//
$template->Begin("Modifica RiBa");

$template->Header("default", $ribaInfo['name'], "BTN_EXIT");

$template->SubHeaderBegin(10,210);
?>
&nbsp;</td>
 <td width='400' align='left'><input type='button' class="button-blue menuwhite" value='Menu' connect='mainmenu' id='menubutton'/>
		<ul class='popupmenu' id='mainmenu'>
		 <!-- <li onclick='printRiBa()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/> Stampa RiBa</li> -->
		 <li onclick='generateCDI()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/> Genera file CDI</li>
		 <li onclick='exportRiBa()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/> Esporta distinta in Excel</li>
		 <li class='separator'>&nbsp;</li>
		 <li onclick='deleteRiBa()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/> Elimina questo RiBa</li>
		</ul></td>
 <td width='400' align='right'>
  <input type='button' class='button-gray' value='Chiudi' onclick='abort()'/>
  <input type='button' class='button-blue' value="Salva e chiudi" onclick='saveRiBa()'/>
 </td>
 <td>&nbsp;
<?php
$template->SubHeaderEnd();

$template->Body("monosection",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div class="riba-form">
 <div class="riba-form-header">Dettagli Ri.Ba.</div>
 <div class="riba-form-body">
  <table width='100%' cellspacing='0' cellpadding='0' border='0'>
  <tr><td width='50%' valign='top'>
	   <!-- LEFT -->
	   <span class='company-name'><?php echo $coName; ?></span><br/>
	   <span class='company-addr'><?php echo $coAddr; ?><br/><?php echo $coZip." - ".$coCity." (".$coProv.")"; ?></span><br/>
	   <br/>
	   <span class='company-vatnum'><?php
		 if($coVatNum == $coTaxCode)
		  echo "P.IVA / C.F.: ".$coVatNum;
		 else if($coVatNum)
		  echo "P.IVA: ".$coVatNum;
		 else if($coTaxCode)
		  echo "C.F.: ".$coTaxCode;
		?></span><br/>
	   <br/>
	   <table border='0' cellspacing='3' cellpadding='0'>
		<tr><td>Data creazione:</td><td><input type='text' class='calendar' id='ctime' value="<?php echo date('d/m/Y',$ribaInfo['ctime']); ?>"/></td></tr>
		<tr><td>Data disposizione:</td><td><input type='text' class='calendar' id='disptime' value="<?php echo date('d/m/Y',strtotime($ribaInfo['availdate'])); ?>"/></td></tr>
	   </table>
	  </td><td valign='top'>
	   <!-- RIGHT -->
	   <table border='0' cellspacing='3' cellpadding='0'>
		<tr><td align='right'>Banca: </td><td colspan='3'><input type='text' class='bank' id='banksupport' style='width:250px' readonly='true' value="<?php echo $coBankName; ?>" refid="0"/></td></tr>
		<tr><td colspan='4'>&nbsp;</td></tr>
		<tr><td align='right'>Codice Sia: </td><td colspan='3'><b id='banksupport_sia'><?php echo $coBankSia; ?></b></td></tr>
		<tr><td align='right'>ABI: </td><td><b id='banksupport_abi'><?php echo $coBankAbi; ?></b></td>
			<td align='right'>CAB: </td><td><b id='banksupport_cab'><?php echo $coBankCab; ?></b></td></tr>
		<tr><td align='right'>Conto corrente: </td><td colspan='3'><b id='banksupport_cc'><?php echo $coBankCC; ?></b></td></tr>
		<tr><td colspan='4'>&nbsp;</td></tr>
		<tr><td align='right' valign='top' style='padding-top:8px'>Etichetta: </td>
			<td colspan='3'><input type='text' class='edit' style='width:250px;font-size:12px' id='ribatitle' value="<?php echo $ribaInfo['name']; ?>"/><br/><small class='lightgray'>es: RB-Banca_XX-del-<?php echo date('dmY'); ?></small></td></tr>
	   </table>
	  </td>
  </tr>
  </table>
 </div>
</div>
<br/><br/>
<h3 style='text-align:left;'>Elenco rate</h3>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='doclist'>
<tr><th width='16'><input type='checkbox'/></th>
	<th width='60'>Scadenza</th>
	<th width='100'>Documento</th>
	<th>Cliente</th>
	<th width='250'>Banca</th>
	<th width='80' style='text-align:right'>Importo</th>
</tr>
<?php
$list = $ribaInfo['elements'];
for($c=0; $c < count($list); $c++)
{
 $record = $list[$c];
 echo "<tr id='".$record['id']."'><td><input type='checkbox'/></td>";
 echo "<td>".date('d.m.Y',strtotime($record['expire_date']))."</td>";
 echo "<td><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$record['docref_id']."' target='GCD-".$record['docref_id']."'>"
	.$record['docref_name']."</a></td>";
 echo "<td><span class='linkblue' onclick='showSubjectInfo(".$record['subject_id'].",this)'>".$record['subject_name']."</span></td>";
 echo "<td><input type='text' class='bank' refid='".$record['bank_id']."' subjid='".$record['subject_id']."' style='width:250px' placeholder='seleziona una banca' value=\"".$record['bank_name']."\" readonly='true'/></td>";
 echo "<td align='right'>".number_format($record['amount'],2,',','.')." &euro;</td>";
 echo "</tr>";
}
?>
</table>
<br/>
<div align='left'>
<input type='button' class="button-trash" value="Elimina rate selezionate" onclick='deleteSelectedElements()'/>
</div>
<div style="border-top:1px solid #d8d8d8;border-bottom:1px solid #d8d8d8;padding:10px;margin-top:10px;margin-bottom:10px;text-align:right">
 <span class='mediumtext'>Totale importi: <?php echo number_format($ribaInfo['tot_amount'],2,',','.'); ?> &euro;</span>
</div>
<br/><br/><br/><br/>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->Footer();
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
Template.OnExit = function(){
 abort();
 return false;
}

Template.OnSaveRequest = function(id,data)
{
 
}

Template.OnInit = function(){
	this.initBtn(document.getElementById('menubutton'), "popupmenu");
	var tb = document.getElementById('doclist');
	this.initSortableTable(tb);
	for(var c=1; c < tb.rows.length; c++)
	{
	 var ed = tb.rows[c].cells[4].getElementsByTagName('INPUT')[0];
	 this.initEd(ed, "bank");
	}
	this.initEd(document.getElementById('ctime'),"date");
	this.initEd(document.getElementById('disptime'),"date");
	this.initEd(document.getElementById('banksupport'),"bank").OnSearch = function(){
		 document.getElementById('banksupport_sia').innerHTML = (this.data && this.data['sia']) ? this.data['sia'] : "";
		 document.getElementById('banksupport_abi').innerHTML = (this.data && this.data['abi']) ? this.data['abi'] : "";
		 document.getElementById('banksupport_cab').innerHTML = (this.data && this.data['cab']) ? this.data['cab'] : "";
		 document.getElementById('banksupport_cc').innerHTML = (this.data && this.data['cc']) ? this.data['cc'] : "";
		};
}

function showSubjectInfo(id,span)
{
 var r = span.parentNode.parentNode;
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){}
	 sh2.sendCommand("dynarc item-info -ap rubrica -id '"+id+"' -extget banks");
	}
 sh.sendCommand("gframe -f rubrica.edit -params 'id="+id+"'");
}

function abort()
{
 if(window.opener)
  window.close();
 else
  document.location.href = "riba.php";
}

function deleteRiBa()
{
 if(!confirm("Sei sicuro di voler eliminare questa Ri.Ba.?"))
  return false;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 if(window.opener)
	 {
	  window.opener.reload();
	  window.close();
	 }
	 else
	  document.location.href = "riba.php";
	}
 sh.sendCommand("backoffice delete-riba -id '<?php echo $ribaInfo['id']; ?>'");
}

function saveRiBa()
{
 var title = document.getElementById('ribatitle').value;
 var ctime = document.getElementById('ctime').isodate;
 var availtime = document.getElementById('disptime').isodate;
 var bankName = document.getElementById('banksupport').value;
 var bankId = document.getElementById('banksupport').getId();
 var bankSia = document.getElementById('banksupport_sia').innerHTML;
 var bankAbi = document.getElementById('banksupport_abi').innerHTML;
 var bankCab = document.getElementById('banksupport_cab').innerHTML;
 var bankCC = document.getElementById('banksupport_cc').innerHTML;

 var tb = document.getElementById('doclist');

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(o,a){
	 if(window.opener)
	 {
	  window.opener.document.location.reload();
	  window.close();
	 }
	 else
	  document.location.href = "riba.php";
	}
 sh.sendCommand("backoffice edit-riba -id '<?php echo $ribaInfo['id']; ?>' -title `"+title+"` -ctime '"+ctime+"' -availtime '"+availtime+"' -bankname `"+bankName+"` -bankid '"+bankId+"' -sia '"+bankSia+"' -abi '"+bankAbi+"' -cab '"+bankCab+"' -cc '"+bankCC+"'");
 for(var c=1; c < tb.rows.length; c++)
 {
  var ed = tb.rows[c].cells[4].getElementsByTagName('INPUT')[0];
  if(!ed.data)
   continue;
  sh.sendCommand("backoffice edit-riba-element -id '"+tb.rows[c].id+"' -bankid '"+ed.data['id']+"' -bankname `"+ed.data['name']+"` -abi '"+ed.data['abi']+"' -cab '"+ed.data['cab']+"' -cc '"+ed.data['cc']+"'");
 }

}

function deleteSelectedElements()
{
 var tb = document.getElementById("doclist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Devi selezionare almeno una rata");

 if(!confirm("Sei sicuro di voler eliminare le rate selezionate da questa RiBa?"))
  return;

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= ","+sel[c]['id'];
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 /* TODO: rimuovere dalla lista le righe e ricalcolare il totale senza fare il refresh della pagina altrimenti si possono perdere le modifiche */
	}
 sh.sendCommand("backoffice remove-riba-elements -riba '<?php echo $ribaInfo['id']; ?>' -ids '"+q.substr(1)+"'");
}

function exportRiBa()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("backoffice export-riba-to-excel -riba '<?php echo $ribaInfo['id']; ?>'");
}

function generateCDI()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("backoffice export-riba-to-cbi -riba '<?php echo $ribaInfo['id']; ?>'");
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
?>
