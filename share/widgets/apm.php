<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-10-2013
 #PACKAGE: apm-gui
 #DESCRIPTION: APM - Alpatech Package Manager
 #VERSION: 2.4beta
 #CHANGELOG: 22-10-2013 : Autenticazione.
			 27-04-2013 : Some bug fixes and auto-upload on start-up.
			 05-02-2013 : Bug fix on check depends.
			 01-12-2012 : Bug fix.
			 02-01-2012 : Bug fix
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
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}

$ret = GShell("gpkg list", $_REQUEST['sessid'], $_REQUEST['shellid']);
if($ret['error'])
{
 $msg = str_replace("\n","",$ret['message']);
 ?>
 <script>
 function bodyOnLoad()
 {
  gframe_close("<?php echo $msg; ?>");
 }
 </script>
 <?php
 return;
}

$packages = $ret['outarr'];
$bySections = array();
// get sections //
for($c=0; $c < count($packages); $c++)
{
 $p = $packages[$c]; if(!$p['section']) $p['section'] = "other";
 if(!$bySections[$p['section']])
  $bySections[$p['section']] = array();
 $bySections[$p['section']][] = $p;
}
asort($bySections);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Alpatech Package Manager</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/apm.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/simple-blue.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/htmlgutility/css/popupmenu.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/progressbar.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/htmlgutility/screen.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/jquery/index.php");
?>
</head><body>

<div class="widget">
<table class='header' width='680' border='0' cellspacing='0' cellpadding='0'>
<tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/apmlogo.png"/></td></tr>
</table>

<table class='apmbtnbar' id='apmmainmenu' width='670' border='0' cellspacing='0' cellpadding='0' style='margin-left:3px;margin-top:3px;'>
<tr><td class='btnicon' onclick="_apmUpdate()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/reload.png"/><div><?php echo i18n("Reload"); ?></div></td>
	<td class='btnicon' onclick="_apmUpgrade()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/update.png"/><div><?php echo i18n("Update"); ?></div></td>
	<td class='btnicon' onclick="_apmActionApply()" enabled='false'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/apply_disabled.png"/><div><?php echo i18n("Apply"); ?></div></td>
	<!-- <td class='btnicon' onclick="_apmFind()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/find.png"/><div><?php echo i18n("Search"); ?></div></td> -->
	<td class='btnicon' onclick="_apmSettings()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/settings.png"/><div><?php echo i18n("Settings"); ?></div></td>
	<td>&nbsp;</td></tr>
</table>
<div class='contents'>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='contents-table'>
<tr><td width="166" valign="top" rowspan="2" style="border-right:2px solid #aaccee;border-left:1px solid #aaccee;">
	<div class='apmsections-bar'><?php echo i18n("Sections"); ?></div>
	<select size="20" style="width:100%;height:374px;" onchange="_apmUpdateList()" id='section'>
	<option value='all' selected='true'><?php echo i18n("All"); ?></option>
	<?php
	while(list($k) = each($bySections))
	 echo "<option value='$k'>$k</option>";
	?>
	</select>
	</td>
	<td height="316" valign="top" style="border-right:1px solid #aaccee;">
		<table border='0' cellspacing='0' cellpadding='0' id='packagepopupmenu' style='display:none;'>
		<tr><td>
		<ul class='popupmenu'>
		 <li class='disabled'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-available.png"/> <span><?php echo i18n("Unselect"); ?></span></li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-install.png"/> <span><?php echo i18n("Install"); ?></span></li>
		 <li class='disabled'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-reinstall.png"/> <span><?php echo i18n("Reinstall"); ?></span></li>
		 <li class='disabled'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-upgrade.png"/> <span><?php echo i18n("Upgrade"); ?></span></li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-remove.png"/> <span><?php echo i18n("Remove"); ?></span></li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package-remove.png"/> <span><?php echo i18n("Complete remove"); ?></span></li>
		 <li class='separator'>&nbsp;</li>
		 <li id='packagepopupmenu_property'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/package.png"/> <span><?php echo i18n("Properties"); ?></span></li>
		</ul>
		</td></tr>
		</table>

		<div style="height:310px;padding:0px;overflow:auto;" id="packagelist-div">
		<table width='100%' id='packagelist' cellspacing='0' cellpadding='0' border='0'>
		<tr><th width='18'>S</th><th width='18'>&nbsp;</th><th align='left'><?php echo i18n("Package"); ?></th><th width='100'><?php echo i18n("Avail. ver."); ?></th><th width='100'><?php echo i18n("Inst. ver."); ?></th></tr>
		<?php
		
		for($c=0; $c < count($packages); $c++)
		{
		 $p = $packages[$c];
		 echo "<tr id='pkg-".$p['name']."' onclick='_apmSelectRow(this)'><td onclick='_apmPkgMenu(this)' status='".$p['status']."'>";
		 switch($p['status'])
		 {
		  case 'outdated' : echo "<img src='".$_ABSOLUTE_URL."share/widgets/apm/icons/package-installed-outdated.png'/>"; break;
		  case 'installed' : echo "<img src='".$_ABSOLUTE_URL."share/widgets/apm/icons/package-installed-updated.png'/>"; break;
		  case 'available' : echo "<img src='".$_ABSOLUTE_URL."share/widgets/apm/icons/package-available.png'/>"; break;
		  default : echo "<img src='".$_ABSOLUTE_URL."share/widgets/apm/icons/package-broken.png'/>"; break;
		 }
		 echo "</td><td>&nbsp;</td><td class='pkgname'>".$p['name']."</td><td align='center' class='pkgver'>"
			.$p['version']."</td><td align='center' class='pkgver'>".($p['installed_version'] ? $p['installed_version'] : "&nbsp;")."</td></tr>";
		}
		?>
		</table></div>
	</td></tr>
<tr><td height="84" valign="top" style="border-top:2px solid #aaccee;border-right:1px solid #aaccee;">
	<div id='packagedescription'>&nbsp;</div></td></tr>
</table>
</div>
</div>
<div class='widget-footer'>
<table width='100%' border='0'><tr><td>&nbsp;</td><td width='200'>
<ul class='simple-blue-buttons'>
	<li id='apply-btn' style='visibility:hidden;'><span onclick="_apmActionApply()"><?php echo i18n("Apply"); ?></span></li>
	<li id='close-btn'><span onclick="widget_apm_close()"><?php echo i18n("Close"); ?></span></li>
</ul></td></tr></table>

<div id='packageproperty-form' title='Propriet&agrave;' style='display:none;'>
<table border='0' class='packageproperty_table'>
<tr><td><?php echo i18n("Package"); ?>:</td><td id='package_name'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Section"); ?>:</td><td id='package_section'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Available ver."); ?>:</td><td id='package_version'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Installed ver."); ?>:</td><td id='package_installedversion'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Maintainer"); ?>:</td><td id='package_maintainer'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Essential"); ?>:</td><td id='package_essential'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Depends"); ?>:</td><td id='package_depends'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Pre-depends"); ?>:</td><td id='package_predepends'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Replaces"); ?>:</td><td id='package_replaces'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Conflicts"); ?>:</td><td id='package_conflicts'>&nbsp;</td></tr>
<tr><td><?php echo i18n("Description"); ?>:</td><td id='package_description'>&nbsp;</td></tr>

</table>
</div>

</div>


<div class='infobox' style='display:none;' id='infobox-message'>
 <span class='message'>Updating package list. Please wait!</span>
 <br/>
 <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/img/progress.gif"/>
</div>


<script>
var icons = {'outdated':'package-installed-outdated.png', 'installed':'package-installed-updated.png', 'available':'package-available.png', 'install':'package-install.png','remove':'package-remove.png','reinstall':'package-reinstall.png','upgrade':'package-upgrade.png'}; 
var marked = new Array();
marked['to_be_remove'] = new Array();
marked['to_be_uninstall'] = new Array();
marked['to_be_upgrade'] = new Array();
marked['to_be_install'] = new Array();
marked['to_be_reinstall'] = new Array();

function widget_apm_close()
{
 gframe_close("done!");
}

function bodyOnLoad()
{
 document.addEventListener ? document.addEventListener("mouseup",_apmPopUpClose,false) : document.attachEvent("onmouseup",_apmPopUpClose);
 $('#packageproperty-form').dialog({
	autoOpen: false,
	width: 320,
	height: 300,
	buttons: {
			"Exit" : function(){$('#packageproperty-form').dialog('close');}
		},
	close: function(){$('#packageproperty-form').dialog('close');}
	});

 _apmUpdate();
}

function _apmPopUpClose()
{
 document.getElementById('packagepopupmenu').style.display='none';
}

function apm_getRowById(tb,id)
{
 for(var c=0; c < tb.rows.length; c++)
 {
  if(tb.rows[c].id == id)
   return tb.rows[c];
 }
}

function _apmSelectRow(r)
{
 var tb = document.getElementById('packagelist');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].className == "selected")
   tb.rows[c].className = "";
 }
 r.className = "selected";
 if(!r.data)
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 r.data = a;
	 document.getElementById('packagedescription').innerHTML = "<h4><?php echo i18n('Package description'); ?>: "+a['name']+"</h4>"+a['description'];
	}
  sh.sendCommand("gpkg info '"+r.id.substr(4)+"'");
 }
 else
  document.getElementById('packagedescription').innerHTML = "<h4><?php echo i18n('Package description'); ?>: "+r.data['name']+"</h4>"+r.data['description'];
}

function _apmPkgMenu(td)
{
 var pkg = td.parentNode.id.substr(4);
 var popup = document.getElementById('packagepopupmenu');
 var popupMenu = popup.getElementsByTagName('UL')[0];
 var options = new Array('deselect','install','reinstall','upgrade','uninstall','remove');

 switch(td.getAttribute('status'))
 {
  case 'outdated' : var temp = new Array(td.getAttribute('action') ? 1 : 0,0,0,1,1,1); break;
  case 'installed' : var temp = new Array(td.getAttribute('action') ? 1 : 0,0,1,0,1,1); break;
  case 'available' : var temp = new Array(td.getAttribute('action') ? 1 : 0,1,0,0,0,0); break;
  default : var temp = new Array(td.getAttribute('action') ? 1 : 0,0,0,0,0,0); break;
 }
 
 for(var c=0; c < options.length; c++)
 {
  var opt = popupMenu.getElementsByTagName('LI')[c];
  opt.className = temp[c] ? "" : "disabled";
  opt._option = options[c];
  if(!temp[c])
   opt.onclick = null;
  else
   opt.onclick = function(){_apmPkgMark(pkg,this._option);}
 }
 document.getElementById('packagepopupmenu_property').onclick = function(){_apmPkgProperty(pkg);}

 popup.style.position="absolute";
 popup.style.top = (parseFloat(td.offsetTop)+120) - parseFloat(document.getElementById('packagelist-div').scrollTop);
 popup.style.left = parseFloat(td.offsetLeft)+190;
 popup.style.display = "";
}

function _apmPkgProperty(pkg)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById('package_name').innerHTML = a['name'];
	 document.getElementById('package_section').innerHTML = a['section'];
	 document.getElementById('package_version').innerHTML = a['version'];
	 document.getElementById('package_installedversion').innerHTML = a['installed_version'];
	 document.getElementById('package_maintainer').innerHTML = a['maintainer'];
	 document.getElementById('package_essential').innerHTML = a['essential'] != "0" ? "<b>Yes</b>" : "<i>no</i>";
	 document.getElementById('package_predepends').innerHTML = a['pre-depends'];
	 document.getElementById('package_depends').innerHTML = a['depends'];
	 document.getElementById('package_replaces').innerHTML = a['replaces'];
	 document.getElementById('package_conflicts').innerHTML = a['conflicts'];
	 document.getElementById('package_description').innerHTML = a['description'];
	 $('#packageproperty-form').dialog('open');
	}
 sh.sendCommand("gpkg info '"+pkg+"'");
}

function _apmPkgMark(pkg,action)
{
 var td = apm_getRowById(document.getElementById('packagelist'),'pkg-'+pkg).cells[0];
 td.setAttribute('action',action);
 var img = td.getElementsByTagName('IMG')[0];
 switch(action)
 {
  case 'deselect' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons[td.getAttribute('status')];
	 _apmPkgDeselect(pkg);
	}; break;
  case 'install' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons['install'];
	 _apmPkgSelect(pkg,"install");
	}; break;
  case 'reinstall' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons['reinstall'];
	 _apmPkgSelect(pkg,"reinstall");
	}; break;
  case 'upgrade' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons['upgrade'];
	 _apmPkgSelect(pkg,"upgrade");
	}; break;
  case 'uninstall' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons['remove'];
	 _apmPkgSelect(pkg,"uninstall");
	 /* TODO: checkup per verificare (tramite il comando gpkg reverse-check-depends) se la rimozione di questo pacchetto comporterà la disfunzione di altre applicazioni. */
	}; break;
  case 'remove' : {
	 img.src = BASE_PATH+"share/widgets/apm/icons/"+icons['remove'];
	 _apmPkgSelect(pkg,"remove");
	 /* TODO: checkup per verificare (tramite il comando gpkg reverse-check-depends) se la rimozione di questo pacchetto comporterà la disfunzione di altre applicazioni. */
	}; break;
 }

 if((action=="install") || (action=="reinstall") || (action=="upgrade"))
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 if(!a || (a['UNAVAILABLE'] && a['UNAVAILABLE'].length))
	 {
	  _apmPkgMark(pkg,"deselect");
	  return;
	 }

	 if(a['TO_BE_REMOVE'] && a['TO_BE_REMOVE'].length)
	 {
	  for(var c=0; c < a['TO_BE_REMOVE'].length; c++)
	  {
	   _apmPkgSelect(a['TO_BE_REMOVE'][c],"uninstall");
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['TO_BE_REMOVE'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action','uninstall');
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['remove'];
	  } 
	 }
	 if(a['OUTDATED'] && a['OUTDATED'].length)
	 {
	  for(var c=0; c < a['OUTDATED'].length; c++)
	  {
	   _apmPkgSelect(a['OUTDATED'][c],"upgrade");
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['OUTDATED'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action','upgrade');
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['upgrade'];
	  }
	 }
	 if(a['AVAILABLE'] && a['AVAILABLE'].length)
	 {
	  for(var c=0; c < a['AVAILABLE'].length; c++)
	  {
	   _apmPkgSelect(a['AVAILABLE'][c],action);
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['AVAILABLE'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action',action);
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['install'];
	  }
	 }
	}
  sh.sendCommand("apmgui resolve "+pkg);
 }
 
 if(marked['to_be_remove'].length || marked['to_be_uninstall'].length || marked['to_be_upgrade'].length || marked['to_be_install'].length || marked['to_be_reinstall'].length)
 {
  document.getElementById('apmmainmenu').rows[0].cells[2].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/apply.png";
  document.getElementById('apmmainmenu').rows[0].cells[2].setAttribute('enabled','true');
  document.getElementById('apply-btn').style.visibility='visible';
 }
 else
 {
  document.getElementById('apmmainmenu').rows[0].cells[2].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/apply_disabled.png";
  document.getElementById('apmmainmenu').rows[0].cells[2].setAttribute('enabled','false');
  document.getElementById('apply-btn').style.visibility='hidden';
 }
}

function _apmPkgDeselect(pkg)
{
 if(marked['to_be_remove'].indexOf(pkg) > -1)
  marked['to_be_remove'].splice(marked['to_be_remove'].indexOf(pkg),1);
 else if(marked['to_be_uninstall'].indexOf(pkg) > -1)
  marked['to_be_uninstall'].splice(marked['to_be_uninstall'].indexOf(pkg),1);
 else if(marked['to_be_upgrade'].indexOf(pkg) > -1)
  marked['to_be_upgrade'].splice(marked['to_be_upgrade'].indexOf(pkg),1);
 else if(marked['to_be_install'].indexOf(pkg) > -1)
  marked['to_be_install'].splice(marked['to_be_install'].indexOf(pkg),1);
 else if(marked['to_be_reinstall'].indexOf(pkg) > -1)
  marked['to_be_reinstall'].splice(marked['to_be_reinstall'].indexOf(pkg),1);
}

function _apmPkgSelect(pkg,action)
{
 if(marked['to_be_'+action].indexOf(pkg) > -1)
  marked['to_be_'+action].splice(marked['to_be_'+action].indexOf(pkg),1);
 marked['to_be_'+action].push(pkg);
}

function _apmActionApply()
{
 var td = document.getElementById('apmmainmenu').rows[0].cells[2];
 if(td.getAttribute('enabled') == "false")
  return;

 document.getElementById('apply-btn').style.visibility='hidden';

 var params = "";

 for(var c=0; c < marked['to_be_uninstall'].length; c++)
  params+= (c>0 ? "," : "&tobeuninstall=")+marked['to_be_uninstall'][c];
 for(var c=0; c < marked['to_be_remove'].length; c++)
  params+= (c>0 ? "," : "&toberemove=")+marked['to_be_remove'][c];
 for(var c=0; c < marked['to_be_upgrade'].length; c++)
  params+= (c>0 ? "," : "&tobeupgrade=")+marked['to_be_upgrade'][c];
 for(var c=0; c < marked['to_be_install'].length; c++)
  params+= (c>0 ? "," : "&tobeinstall=")+marked['to_be_install'][c];
 for(var c=0; c < marked['to_be_reinstall'].length; c++)
  params+= (c>0 ? "," : "&tobereinstall=")+marked['to_be_reinstall'][c];
 
 var sh = new GShell();
 sh.OnFinish = function(){
	 // update package status //
	 var a = new Array('uninstall','remove','upgrade','install','reinstall');
	 var s = new Array('available','available','installed','installed','installed');
	 for(var c=0; c < a.length; c++)
	 {
	  for(var i=0; i < marked['to_be_'+a[c]].length; i++)
	  {
	   var tr = apm_getRowById(document.getElementById('packagelist'),'pkg-'+marked['to_be_'+a[c]][i]);
	   if(tr)
	   {
	    tr.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons[s[c]];
		tr.cells[0].setAttribute('status',s[c]);
		tr.cells[0].setAttribute('action',"");
		if((a[c]=="uninstall") || (a[c]=="remove"))
		 tr.cells[4].innerHTML = "&nbsp;";
		else
		 tr.cells[4].innerHTML = tr.cells[3].innerHTML;
	   }
	  }
	  marked['to_be_'+a[c]] = new Array();
	 }
	}
 sh.sendCommand("gframe -f apm.process --fullspace -params "+params.substr(1,params.length-1));
}

function _apmUpdateList()
{
 var sec = document.getElementById('section').value;
 var act = new Array('uninstall','remove','upgrade','install','reinstall');
 var stat = new Array('remove','remove','upgrade','install','reinstall');

 var infobox = document.getElementById('infobox-message');

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 infobox.style.display = "none"; 
	 var tb = document.getElementById('packagelist');
	 while(tb.rows.length > 1)
	  tb.deleteRow(1);
	 if(!a || !a.length)
	  return;
	 for(var c=0; c < a.length; c++)
	 {
	  var p = a[c];
	  var r = tb.insertRow(-1);
	  r.onclick = function(){_apmSelectRow(this);}
	  r.id = "pkg-"+p['name'];
	  r.data = p;
	  var td = r.insertCell(-1);
	  td.setAttribute('status',p['status']);
	  td.onclick = function(){_apmPkgMenu(this);}
	  switch(p['status'])
	  {
	   case 'outdated' : td.innerHTML = "<img src='"+BASE_PATH+"share/widgets/apm/icons/package-installed-outdated.png'/ >"; break;
	   case 'installed' : td.innerHTML =  "<img src='"+BASE_PATH+"share/widgets/apm/icons/package-installed-updated.png'/ >"; break;
	   case 'available' : td.innerHTML =  "<img src='"+BASE_PATH+"share/widgets/apm/icons/package-available.png'/ >"; break;
	   default : td.innerHTML =  "<img src='"+BASE_PATH+"share/widgets/apm/icons/package-broken.png'/ >"; break;
	  }
	  for(var i=0; i < act.length; i++)
	  {
	   if(marked['to_be_'+act[i]].indexOf(p['name']) > -1)
	   {
		td.innerHTML = "<img src='"+BASE_PATH+"share/widgets/apm/icons/"+icons[stat[i]]+"'/ >";
		td.setAttribute('action',stat[i]);
	   }
	  }
	  r.insertCell(-1).innerHTML = "&nbsp;";
	  var td = r.insertCell(-1); td.className = "pkgname"; td.innerHTML = p['name'];
	  var td = r.insertCell(-1); td.className = "pkgver"; td.innerHTML = p['version'];
	  var td = r.insertCell(-1); td.className = "pkgver"; td.innerHTML = p['installed_version'];
	 }
	}
 sh.sendCommand("gpkg list"+(sec != "all" ? " -section '"+sec+"'" : ""));
}

function _apmUpdate()
{
 var infobox = document.getElementById('infobox-message');
 infobox.getElementsByTagName('SPAN')[0].innerHTML = "Updating package list. Please wait!";
 infobox.style.display = "";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  infobox.style.display = "none"; 
	  return;
	 }
	 var sel = document.getElementById('section');
	 while(sel.options.length > 1)
	  sel.removeChild(sel.options[1]);
	 if(a['sections'])
	 {
	  for(var c=0; c < a['sections'].length; c++)
	  {
	   var opt = document.createElement('OPTION');
	   opt.value = a['sections'][c];
	   opt.innerHTML = a['sections'][c];
	   sel.appendChild(opt);
	  }
	  sel.options[0].selected = true;
	  _apmUpdateList();
	 }
	 else
	  infobox.style.display = "none"; 
	}
 sh.sendCommand("apm update");

}

function _apmUpgrade()
{
 var infobox = document.getElementById('infobox-message');
 infobox.getElementsByTagName('SPAN')[0].innerHTML = "Check for packages updates.<br/"+">Please wait!";
 infobox.style.display = "";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  infobox.style.display = "none"; 
	  return;
	 }
	 if(a['outdated'])
	 {
	  var packages = "";
	  for(var c=0; c < a['outdated'].length; c++)
	  {
	   _apmPkgSelect(a['outdated'][c],"upgrade");
	   packages+= ","+a['outdated'][c];
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['outdated'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action','upgrade');
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['upgrade'];
	  }
	  document.getElementById('apmmainmenu').rows[0].cells[2].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/apply.png";
	  document.getElementById('apmmainmenu').rows[0].cells[2].setAttribute('enabled','true');
	  document.getElementById('apply-btn').style.visibility='visible';
	  _apmResolveDepends(packages.substr(1));
	 }
	 else
	 {
	  infobox.style.display = "none"; 
	  alert("<?php echo i18n('All packages are already updated to latest version'); ?>");
	 }
	}
 sh.sendCommand("apm update");
}

function _apmFind()
{
 alert("Funzione da implementare");
}

function _apmSettings()
{
 var sh = new GShell();
 sh.OnOutput = function(){_apmUpdate();}
 sh.sendCommand("gframe -f apm.settings --fullspace");
}

function _apmResolveDepends(packages)
{
 var infobox = document.getElementById('infobox-message');
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 infobox.style.display = "none"; 
	 if(!a)
	 {
	  if(packages.indexOf(",") > 0)
	  {
	   var x = packages.split(",");
	   for(var c=0; c < x.length; c++)
	    _apmPkgMark(x[c],"deselect");
	  }
	  else
	   _apmPkgMark(packages,"deselect");

	  document.getElementById('apmmainmenu').rows[0].cells[2].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/apply_disabled.png";
	  document.getElementById('apmmainmenu').rows[0].cells[2].setAttribute('enabled','false');
	  document.getElementById('apply-btn').style.visibility='hidden';

	  return;
	 }

 	 if(a['UNAVAILABLE'] && a['UNAVAILABLE'].length)
	 {
	  /* verifica quanti pacchetti necessitano di questo pacchetto e li deseleziona */
	  var sh2 = new GShell();
	  sh2.OnOutput = function(oo, aa){
		 if(aa || aa.length)
		 {
		  for(var i=0; i < aa.length; i++)
		   _apmPkgMark(aa[i]['name'],"deselect");
		 }
		}
	  for(var c=0; c < a['UNAVAILABLE'].length; c++)
	   sh2.sendCommand("gpkg reverse-check-depends -package `"+a['UNAVAILABLE'][c]+"`");	  
	  return;
	 }

	 if(a['TO_BE_REMOVE'] && a['TO_BE_REMOVE'].length)
	 {
	  for(var c=0; c < a['TO_BE_REMOVE'].length; c++)
	  {
	   _apmPkgSelect(a['TO_BE_REMOVE'][c],"uninstall");
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['TO_BE_REMOVE'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action','uninstall');
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['remove'];
	  } 
	 }
	 if(a['OUTDATED'] && a['OUTDATED'].length)
	 {
	  for(var c=0; c < a['OUTDATED'].length; c++)
	  {
	   _apmPkgSelect(a['OUTDATED'][c],"upgrade");
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['OUTDATED'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action','upgrade');
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['upgrade'];
	  }
	 }
	 if(a['AVAILABLE'] && a['AVAILABLE'].length)
	 {
	  for(var c=0; c < a['AVAILABLE'].length; c++)
	  {
	   _apmPkgSelect(a['AVAILABLE'][c],"install");
	   var r = apm_getRowById(document.getElementById('packagelist'),'pkg-'+a['AVAILABLE'][c]);
	   if(!r) continue;
	   r.cells[0].setAttribute('action',"install");
	   r.cells[0].getElementsByTagName('IMG')[0].src = BASE_PATH+"share/widgets/apm/icons/"+icons['install'];
	  }
	 }
	}

 sh.sendCommand("apmgui resolve `"+packages+"`");
}
</script>
</body></html>
<?php

