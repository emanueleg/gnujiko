<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-02-2016
 #PACKAGE: apm-gui
 #DESCRIPTION: Process form form APM.
 #VERSION: 2.6beta
 #CHANGELOG: 02-02-2016 : Aggiunto messaggio account restrictions.
			 16-03-2015 : Lancia comando update-cache una volta finito di installare i pacchetti.
			 14-03-2015 : Return 1 on success.
			 22-10-2013 : Autenticazione.
			 05-02-2013 : Bug fix on check depends.
			 02-01-2012 : Multi language.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("apm-gui");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Resolve</title></head><body>
 <?php echo $msg; ?>
 <script>
 function bodyOnLoad()
 {
  gform_close("<?php echo $msg; ?>");
 }
 </script></body></html>
 <?php
 return;
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Install</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/process.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/simple-blue.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/progressbar.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<div class="widget">
<table class='header' width='480' border='0' cellspacing='0' cellpadding='0'>
<tr><td width='46' valign='middle' rowspan='2'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package_big.png"/></td>
	<td valign='middle'><h2><?php echo i18n('Installing packages'); ?></h2></td></tr>
<tr><td align='center' id='messages'><?php echo i18n('Downloading and configuring the selected packages.'); ?></td></tr>
<tr><td colspan='2'><div class='progressbar' id='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>
</table>
<div class='contents'>
<table id='packagelist' border='0' width='100%' cellspacing='0'>
<?php
$toberemove = $_REQUEST['toberemove'] ? explode(",",$_REQUEST['toberemove']) : array();
$tobeuninstall = $_REQUEST['tobeuninstall'] ? explode(",",$_REQUEST['tobeuninstall']) : array();
$tobeupgrade = $_REQUEST['tobeupgrade'] ? explode(",",$_REQUEST['tobeupgrade']) : array();
$tobeinstall = $_REQUEST['tobeinstall'] ? explode(",",$_REQUEST['tobeinstall']) : array();
$tobereinstall = $_REQUEST['tobereinstall'] ? explode(",",$_REQUEST['tobereinstall']) : array();

if(!$_REQUEST['execordering'])
{
 // re-check depends a make a correct execution ordering. //
 $packages = "";
 if(count($tobeupgrade))
  $packages.= ",".implode(",",$tobeupgrade);
 if(count($tobeinstall))
  $packages.= ",".implode(",",$tobeinstall);
 if(count($tobereinstall))
  $packages.= ",".implode(",",$tobereinstall);

 $ret = GShell("gpkg resolve `".ltrim($packages,",")."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $list = $ret['outarr']['execordering'];

 $packages = "";
 if(count($toberemove))
  $packages.= ",".implode(",",$toberemove);
 if(count($tobeuninstall))
  $packages.= ",".implode(",",$tobeuninstall);
 for($c=0; $c < count($list); $c++)
  $packages.= ",".$list[$c];

 $_REQUEST['execordering'] = ltrim($packages,",");
}

$x = explode(",",$_REQUEST['execordering']);
for($c=0; $c < count($x); $c++)
{
 $pkg = $x[$c];
 if(!$pkg || ($pkg == ""))
  continue;

 if(in_array($pkg, $toberemove))
  echo "<tr id='pkg-".$pkg."' action='remove' name='".$pkg."'><td class='pkgname'>".$pkg."</td><td width='220' align='center'><span style='color:#f31903'>".i18n('to be remove')."</td><td width='80'><div class='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>";
 else if(in_array($pkg, $tobeuninstall))
  echo "<tr id='pkg-".$pkg."' action='uninstall' name='".$pkg."'><td class='pkgname'>".$pkg."</td><td width='220' align='center'><span style='color:#f31903'>".i18n('to be uninstall')."</td><td width='80'><div class='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>";
 else if(in_array($pkg, $tobeupgrade))
  echo "<tr id='pkg-".$pkg."' action='upgrade' name='".$pkg."'><td class='pkgname'>".$pkg."</td><td width='220' align='center'><span style='color:#3364c3'>".i18n('to be upgrade')."</td><td width='80'><div class='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>";
 else if(in_array($pkg, $tobeinstall))
  echo "<tr id='pkg-".$pkg."' action='install' name='".$pkg."'><td class='pkgname'>".$pkg."</td><td width='220' align='center'><span style='color:#666666'>".i18n('to be install')."</td><td width='80'><div class='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>";
 else if(in_array($pkg, $tobereinstall))
  echo "<tr id='pkg-".$pkg."' action='reinstall' name='".$pkg."'><td class='pkgname'>".$pkg."</td><td width='220' align='center'><span style='color:#666666'>".i18n('to be reinstall')."</td><td width='80'><div class='progressbar'><div class='blue' style='width:1%'>&nbsp;</div></div></td></tr>";
}
?>
</table>
</div>
</div>
<div class='widget-footer'>
<table width='100%' border='0'><tr><td>&nbsp;</td><td width="140">
<ul class='simple-blue-buttons'>
	<li id='abort-btn'><span onclick="widget_apminstall_abort()"><?php echo i18n('Abort'); ?></span></li>
	<li id='close-btn' style='display:none'><span onclick="widget_apminstall_close()"><?php echo i18n('Close'); ?></span></li>
</ul></td></tr></table>
</div>

<script>
function widget_apminstall_abort()
{
 gframe_close("aborted.");
}

function widget_apminstall_close()
{
 gframe_close("done!",1);
}

function bodyOnLoad()
{
 var totPerc = 0;

 var sh = new GShell();
 sh.OnError = function(err,errcode){
	 switch(errcode)
	 {
	  case "PACKAGE_DOES_NOT_EXISTS" : alert("Errore: pacchetto non esistente"); break;
	  case "INVALID_ACCOUNT" : {
		 var sh2 = new GShell();
		 sh2.OnOutput = function(o,a){
			 if(!a)
			 {
			  alert("Operazione annullata");
			  return gframe_close();
			 }
			 document.location.reload();
			}
		 sh2.sendCommand("gframe -f apm.accountreg");
		} break;
	  case "INVALID_TOKEN" : {
		 var sh2 = new GShell();
		 sh2.OnOutput = function(o,a){
			 if(!a)
			 {
			  alert("Operazione annullata");
			  return gframe_close();
			 }
			 document.location.reload();
			}
		 sh2.sendCommand("gframe -f apm.accountreg -params `invalidtoken=true`");	
		} break;
	  case "ACCOUNT_RESTRICTIONS" : {
		 var sh2 = new GShell();
		 sh2.OnOutput = function(o,a){gframe_close();}
		 sh2.sendCommand("gframe -f apm.accessdenied -params `errormessage="+err+"`");			 
		} break;
	  default : alert(err);
	 }
	}
 sh.OnPreOutput = function(o,a, msgType, msgRef){
	 var r = apminstall_getRowById(tb,"pkg-"+msgRef);
	 if(!r) return;
	 switch(msgType)
	 {
	  case "DOWNLOAD_PACKAGE" : r.cells[1].innerHTML = "<span style='color:#CCCCCC'><?php echo i18n('downloading...'); ?></span>"; break;
	  case "DECOMPRESS_PACKAGE" : r.cells[1].innerHTML = "<span style='color:#999999'><?php echo i18n('decompress'); ?></span>"; break;
	  case "COPY_FILES" : r.cells[1].innerHTML = "<span style='color:#666666'><?php echo i18n('copy files...'); ?></span>"; break;
	  case "CONFIGURING_PACKAGE" : r.cells[1].innerHTML = "<span style='color:#3364C3'><?php echo i18n('configuration...'); ?></span>"; break;
	  case "PACKAGE_INSTALLED" : { 
		 r.cells[1].innerHTML = "<span style='color:#00CC00'><?php echo i18n('installed'); ?></span>";
		 var b = r.cells[2].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
		 b.style.width = "100%";
		 b.className = "green";
		 var oldPerc = r.cells[2].getAttribute('perc');
		 totPerc+= 100 - (oldPerc ? parseFloat(oldPerc) : 0);
		} break;
	  case "PACKAGE_UPDATED" : {
		 r.cells[1].innerHTML = "<span style='color:#00CC00'><?php echo i18n('updated'); ?></span>";
		 var b = r.cells[2].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
		 b.style.width = "100%";
		 b.className = "green";
		 var oldPerc = r.cells[2].getAttribute('perc');
		 totPerc+= 100 - (oldPerc ? parseFloat(oldPerc) : 0);
		} break;
	  case "REMOVING_PACKAGE" : r.cells[1].innerHTML = "<span style='color:#00CC00'><b><?php echo i18n('removing...'); ?></b></span>"; break;
	  case "PACKAGE_REMOVED" : {
		 r.cells[1].innerHTML = "<span style='color:#00CC00'><?php echo i18n('removed'); ?></span>";
		 var b = r.cells[2].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
		 b.style.width = "100%";
		 b.className = "green";
		 var oldPerc = r.cells[2].getAttribute('perc');
		 totPerc+= 100 - (oldPerc ? parseFloat(oldPerc) : 0);
		} break;
	  case "DOWNLOADING" : {
		 if(a)
		 {
		  var b = r.cells[2].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
		  b.style.width = a['percentage']+"%";
		  var oldPerc = r.cells[2].getAttribute('perc');
		  r.cells[2].setAttribute('perc',a['percentage']);
		  totPerc+= parseFloat(a['percentage']) - (oldPerc ? parseFloat(oldPerc) : 0);
		 }
		}
	 }
	 var perc = (100/(100*tb.rows.length))*totPerc;
	 document.getElementById('progressbar').getElementsByTagName('DIV')[0].style.width = Math.floor(perc)+"%";
	}

 sh.OnFinish = function(){
	 document.getElementById('messages').innerHTML = "<?php echo i18n('The changes have been applied with success!'); ?>";
	 document.getElementById('progressbar').getElementsByTagName('DIV')[0].style.width = "100%";
	 document.getElementById('progressbar').getElementsByTagName('DIV')[0].className = "green";
	 document.getElementById('abort-btn').style.display='none';
	 document.getElementById('close-btn').style.display='';

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(){alert("<?php echo i18n('The changes have been applied with success!'); ?>");}
	 sh2.sendCommand("system check-for-updates -force --set-last-update now");
	}

 var tb = document.getElementById('packagelist');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  switch(r.getAttribute('action'))
  {
   case 'remove' : sh.sendCommand("gpkg remove -r -package '"+r.getAttribute('name')+"'"); break;
   case 'uninstall' : sh.sendCommand("gpkg remove -package '"+r.getAttribute('name')+"'"); break;
   case 'upgrade' : {
	 sh.sendCommand("gpkg download -package '"+r.getAttribute('name')+"'");
	 sh.sendCommand("gpkg upgrade -package '"+r.getAttribute('name')+"' --no-resolve");
	} break;
   case 'install' : {
 	 sh.sendCommand("gpkg download -package '"+r.getAttribute('name')+"'");
	 sh.sendCommand("gpkg install -package '"+r.getAttribute('name')+"' --no-resolve");
	} break;
   case 'reinstall' : {
 	 sh.sendCommand("gpkg download -package '"+r.getAttribute('name')+"'");
	 sh.sendCommand("gpkg reinstall -package '"+r.getAttribute('name')+"'");
	} break;
  }
 }
}

function apminstall_getRowById(tb,id)
{
 for(var c=0; c < tb.rows.length; c++)
 {
  if(tb.rows[c].id == id)
   return tb.rows[c];
 }
}
</script>
</body></html>
<?php

