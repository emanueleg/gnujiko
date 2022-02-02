<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-09-2013
 #PACKAGE: pos-module
 #DESCRIPTION: POS Module - Daily closing
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$catId = $_REQUEST['catid'] ? $_REQUEST['catid'] : 0;
$date = $_REQUEST['date'] ? strtotime($_REQUEST['date']) : strtotime(date('Y-m-d'));

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Chiusura giornaliera</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<style type="text/css">
table.standardtable th {
 font-family: arial, sans-serif;
 font-size: 12px;
}

table.standardtable td {
 font-family: arial, sans-serif;
 font-size: 12px;
 height: 30px;
 border-bottom: 1px solid #dadada;
 vertical-align: middle;
}

table.standardtable td.foot {
 font-size: 14px;
 border-right: 1px solid #dadada;
 border-top: 1px solid #dadada;
 padding-right:5px;
}

</style>

<div class='standardwidget' style='width:600px;height:480px'>
 <table width='100%' border='0'>
 <tr><td><h2>Chiusura giornaliera</h2></td>
	 <td align='right' valign='top' style='font-size:13px'>data: 
		<input type='text' class='edit' style='width:80px' value="<?php echo date('d/m/Y',$date); ?>" onchange="datechange(this)"/></td></tr>
 </table>
 <hr/>
 <table width="100%" class="standardtable" border="0" cellspacing="0">
 <tr><th width='40' align='center'>Ora</th>
	 <th align='left'>Documento</th>
	 <th align='right' width='70'>Imponibile</th>
	 <th align='center' width='70'>IVA</th>
	 <th align='center' width='70'>Totale</th></tr>
 </table>
 <div style="width:600px;height:300px;overflow:auto;">
 <table width="100%" class="standardtable" border="0" cellspacing="0">
 <?php
 $ret = GShell("dynarc item-list -ap commercialdocs".($catId ? " -cat '".$catId."'" : " -ct RECEIPTS")." -where `ctime >= '"
	.date('Y-m-d',$date)."' AND ctime < '".date('Y-m-d',strtotime("+1 day",$date))."'` -extget cdinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $list = $ret['outarr']['items'];
 $amount = 0;
 $vat = 0;
 $total = 0;
 for($c=0; $c < count($list); $c++)
 {
  $itm = $list[$c];
  echo "<tr><td width='40'>".date('H:i',$itm['mtime'])."</td>";
  echo "<td><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$itm['id']."' target='GCD-".$itm['id']."'>".$itm['name']."</a></td>";
  echo "<td align='right' width='70'>".number_format($itm['amount'],2,",",".")."</td>";
  echo "<td align='right' width='70'>".number_format($itm['vat'],2,",",".")."</td>";
  echo "<td align='right' width='70' style='padding-right:10px'>".number_format($itm['tot_netpay'],2,",",".")."</td></tr>";
  $amount+= $itm['amount'];
  $vat+= $itm['vat'];
  $total+= $itm['tot_netpay'];
 }
 ?>
 </table>
 </div>

 <table width="100%" class="standardtable" border="0" cellspacing="0" style="margin-bottom:30px;margin-top:20px">
 <tr><td class='foot'>Totale ricevute: <?php echo count($list); ?></td>
	 <td align='right' width='70' class='foot'><b><?php echo number_format($amount,2,",","."); ?></b></td>
	 <td align='right' width='70' class='foot'><b><?php echo number_format($vat,2,",","."); ?></b></td>
	 <td align='right' width='70' class='foot' style='border-right:0px'><b><?php echo number_format($total,2,",","."); ?></b></td>
	 <td width='18' class='foot' style='border-right:0px'>&nbsp;</td></tr>
 </table>

 <input type='button' class='button-blue' value='Conferma' onclick='submit()'/> 
 <input type='button' class='button-red' value="Annulla" onclick='abort()'/>
</div>

<script>

function bodyOnLoad()
{

}

function datechange(ed)
{
 var date = strdatetime_to_iso(ed.value);
 if(!date)
 {
  alert("Formato data non valido");
  ed.focus();
  return;
 }

 date = date.substr(0,10);

 var href = document.location.href;
 if(href.indexOf("&date=") > 0)
  href = href.replace("&date=<?php echo date('Y-m-d',$date); ?>","&date="+date);
 else
  href+= "&date="+date;

 document.location.href = href;
}

function abort()
{
 gframe_close();
}


function submit()
{
 var catId = <?php echo $catId ? $catId : "0"; ?>;
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 alert("Chiusura cassa completata! I movimenti sono stati registrati nella Prima Nota");
	 gframe_close(o,a);
	}
 sh.sendCommand("pos daily-closure -date `<?php echo date('Y-m-d',$date); ?>`"+(catId ? " -cat '"+catId+"'" : " -ct RECEIPTS"));
}
</script>

</body></html>

