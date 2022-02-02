<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-02-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice 2 - Generate RiBa
 #VERSION: 2.1beta
 #CHANGELOG: 05-02-2014 : Bug fix su showSubjectInfo
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");
include_once($_BASE_PATH."include/company-profile.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("banksearch");
$template->includeCSS("generateriba.css");

$template->Begin("Generazione RiBa");

$template->Header("default", "Generazione RiBa &raquo; riepilogo", "BTN_EXIT");

$template->SubHeaderBegin();
echo "&nbsp;";
$template->SubHeaderEnd();

$template->Body("monosection",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='doclist'>
<tr><!-- <th width='16'><input type='checkbox'/></th> -->
	<th width='16'>Nr.</th>
	<th width='80' style='text-align:right'>Importo</th>
	<th width='60'>Scadenza</th>
	<th width='100'>Documento</th>
	<th>Cliente</th>
	<th width='80'>C.F. / P.IVA</th>
	<!-- <th width='80'>ABI - CAB</th> -->
	<th width='35'>ABI</th>
	<th width='35'>CAB</th>
</tr>
<?php
$ret = GShell("backoffice generate-riba-summary -ids '".$_REQUEST['ids']."'");
$results = $ret['outarr']['results'];

for($c=0; $c < count($results); $c++)
{
 $record = $results[$c];
 echo "<tr id='".$record['id']."'><td align='center'>".($c+1)."</td>";
 echo "<td align='right'".($record['paid'] ? " style='color:green'" : "").">".number_format($record['amount'],2,',','.')." &euro;</td>";
 echo "<td>".date('d.m.Y',strtotime($record['expire_date']))."</td>";
 echo "<td><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$record['docinfo']['id']."' target='GCD-".$record['docinfo']['id']."'>"
	.$record['docinfo']['name']."</a></td>";
 echo "<td><span class='linkblue' onclick='showSubjectInfo(".$record['docinfo']['subject_id'].",this)'>".$record['docinfo']['subject_name']."</span></td>";
 echo "<td>";
 if($record['docinfo']['subject_vatnumber'])
  echo $record['docinfo']['subject_vatnumber'];
 else if($record['docinfo']['subject_taxcode'])
  echo $record['docinfo']['subject_taxcode'];
 else
  echo "&nbsp;";
 echo "</td>";

 $abi=""; $cab="";
 if($record['docinfo']['bank_support'])
 {
  $abi = $record['bankinfo']['abi'] ? $record['bankinfo']['abi'] : ($record['bankinfo']['iban'] ? substr($record['bankinfo']['iban'],5,5) : "&nbsp;");
  $cab = $record['bankinfo']['cab'] ? $record['bankinfo']['cab'] : ($record['bankinfo']['iban'] ? substr($record['bankinfo']['iban'],10,5) : "&nbsp;");
 }
 /*echo "<td><input type='text' class='bank' placeholder='seleziona una banca' style='width:120px;height:26px' value='"
	.(($abi && $cab) ? $abi.' - '.$cab : '')."'/></td>";*/
 echo "<td align='center'>".($abi ? $abi : '&nbsp;')."</td>";
 echo "<td align='center'>".($cab ? $cab : '&nbsp;')."</td>";
 echo "</tr>";
}

$coName = $_COMPANY_PROFILE['name'];
$coAddr = $_COMPANY_PROFILE['addresses']['registered_office']['address'];
$coCity = $_COMPANY_PROFILE['addresses']['registered_office']['city'];
$coZip = $_COMPANY_PROFILE['addresses']['registered_office']['zip'];
$coProv = $_COMPANY_PROFILE['addresses']['registered_office']['prov'];
$coVatNum = $_COMPANY_PROFILE['vatnumber'];
$coTaxCode = $_COMPANY_PROFILE['taxcode'];

$coBankName = count($_COMPANY_PROFILE['banks']) ? $_COMPANY_PROFILE['banks'][0]['name'] : "";
$coBankSia = count($_COMPANY_PROFILE['banks']) ? $_COMPANY_PROFILE['banks'][0]['sia'] : "";
$coBankAbi = count($_COMPANY_PROFILE['banks']) ? $_COMPANY_PROFILE['banks'][0]['abi'] : "";
$coBankCab = count($_COMPANY_PROFILE['banks']) ? $_COMPANY_PROFILE['banks'][0]['cab'] : "";
$coBankCC = count($_COMPANY_PROFILE['banks']) ? $_COMPANY_PROFILE['banks'][0]['cc'] : "";

?>
</table>
<br/><br/>
<div class="riba-form" style="margin-bottom:100px">
 <div class="riba-form-header">Dati azienda ordinante</div>
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
		<tr><td>Data creazione:</td><td><input type='text' class='calendar' id='ctime' value="<?php echo date('d/m/Y'); ?>"/></td></tr>
		<tr><td>Data disposizione:</td><td><input type='text' class='calendar' id='disptime' value="<?php echo date('d/m/Y'); ?>"/></td></tr>
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
			<td colspan='3'><input type='text' class='edit' style='width:250px;font-size:12px' id='ribatitle' value="RB-<?php echo date('dmY'); ?>"/><br/><small class='lightgray'>es: RB-Banca_XX-del-<?php echo date('dmY'); ?></small></td></tr>
	   </table>
	  </td>
  </tr>
  </table>
 </div>
 <div class="riba-form-footer">
  <input type='button' class='button-gray' value='Annulla' onclick='abort()' style='float:left'/>
  <input type='button' class='button-blue' value="Genera RiBa &raquo;" onclick='generateRiBa()' style='float:right'/>
 </div>
</div>
<br/><br/>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->Footer();
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
Template.OnExit = function(){
 window.history.go(-1);
 return false;
}

Template.OnSaveRequest = function(id,data)
{
 
}

Template.OnInit = function(){
	/*var tb = document.getElementById('doclist');
	for(var c=1; c < tb.rows.length; c++)
	{
	 var ed = tb.rows[c].cells[6].getElementsByTagName('INPUT')[0];
	 this.initEd(ed, "bank");
	}*/
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
 /*var sel = r.cells[6].getElementsByTagName('SELECT')[0];
 while(sel.options.length > 1)
  sel.removeChild(sel.options[1]);*/
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){
		 if(!a || !a['banks'])
		  return;
		 document.location.reload();
		 /*for(var c=0; c < a['banks'].length; c++)
		 {
		  var bank = a['banks'][c];
		  var opt = document.createElement('OPTION');
		  opt.value = bank['id'];
		  opt.innerHTML = bank['name'];
		  opt.setAttribute('abi',bank['abi']);
		  opt.setAttribute('cab',bank['cab']);
		  sel.appendChild(opt);
		 }*/

		}
	 sh2.sendCommand("dynarc item-info -ap rubrica -id '"+id+"' -extget banks");
	}
 sh.sendCommand("gframe -f rubrica.edit -params 'id="+id+"'");
}

function abort()
{
 window.history.go(-1);
}

function generateRiBa()
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

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href="editriba.php?id="+a['id'];
	}
 sh.sendCommand("backoffice generate-riba -ids '<?php echo $_REQUEST['ids']; ?>' -title `"+title+"` -ctime '"+ctime+"' -availtime '"+availtime+"' -bankname `"+bankName+"` -bankid '"+bankId+"' -sia '"+bankSia+"' -abi '"+bankAbi+"' -cab '"+bankCab+"' -cc '"+bankCC+"'");
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
?>
