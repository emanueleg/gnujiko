<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-04-2014
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Official Gnujiko Desktop - base package
 #VERSION: 2.2beta
 #CHANGELOG: 09-04-2014 : Aggiunto possibilità di avere template personalizzato.
			 30-04-2012 : Aggiunto funzione desktopOnLoad() che scatta dopo il caricamento del desktop (su funzione: bodyOnLoad).
			 25-01-2012 : Ora le icone del package manager e del pannello di controllo vengono mostrate solo a chi fa parte del gruppo admin.
 #TODO:
 
*/

if($_DESKTOP_TEMPLATE && file_exists($_BASE_PATH."include/footers/".$_DESKTOP_TEMPLATE.".php"))
{
 include($_BASE_PATH."include/footers/".$_DESKTOP_TEMPLATE.".php");
}
else
{
?>
<!-- EOF CONTENTS -->
	</td>
</tr>

<!-- FOOTER -->
<tr><td colspan='3' class="desktop-footer" align="left" valign="top">
	 <div class="desktop-footer-container">
	 <table width="100%" cellspacing="0" cellpadding="0" border="0">
	 <tr><td valign="top" width="20%">
	  <img src="<?php echo $_ABSOLUTE_URL; ?>include/headings/desktop/img/mainmenu-btn.png" style="cursor:pointer;" onclick="showGnujikoMainMenu()"/>
	  </td><td valign="top" width="30%">
	  <div class="desktop-search" style="margin-left: 200px">
	   <img src="<?php echo $_ABSOLUTE_URL; ?>include/footers/desktop/img/search.png"/>
	   <input type="text" class="desktop-edit-search" id="hacktvsearch"/>
	  </div>
	 </td>
	 <td valign="top">&nbsp;</td></tr>
	 </table>
	 </div>
	</td>
</tr>
</table>

<ul class="desktop-mainmenu" id="gnujikomainmenu" style="visibility:hidden">
  <?php
	$ret = GShell("system app-list");
	$appList = $ret['outarr'];
	$first = false;
	for($c=0; $c < count($appList); $c++)
	{
	 $itm = $appList[$c];
	 $active = (strpos($_SERVER['REQUEST_URI'],$itm['url']) !== FALSE) ? true : false;
	 echo "<li".($c==0 ? " class='first'>" : ">")."<a href='".$_ABSOLUTE_URL.$itm['url']."'><img src='".$_ABSOLUTE_URL.$itm['icon']."' width='22' height='22'/> ".$itm['name']."</a></li>";
	}
	if(count($appList))
	 $first = true;
	if(_userInGroup("admin"))
	{
   	 /* Package manager and config buttons */
   	 if(file_exists($_BASE_PATH."share/widgets/apm.php") || file_exists($_BASE_PATH."share/widgets/config.php"))
   	 {
	  if(count($appList))
	   echo "<li class='separator'><hr/></li>";
	  if(file_exists($_BASE_PATH."share/widgets/apm.php"))
	  {
	   echo "<li".(!$first ? " class='first'" : "")."><a href='#' onclick='runPackageManager()'><img src='"
		.$_ABSOLUTE_URL."include/headings/desktop/img/packagemanager.png' width='22' height='22'/> ".i18n("Package manager")."</a></li>";
	   $first = true;
	  }
	  if(file_exists($_BASE_PATH."share/widgets/config.php"))
	  {
	   echo "<li".(!$first ? " class='first'" : "")."><a href='#' onclick='runConfig()'><img src='"
		.$_ABSOLUTE_URL."include/headings/desktop/img/config.png' width='22' height='22'/> ".i18n("Configuration")."</a></li>";
	   $first = true;
	  }
     }
	}
  ?>
  <li class="separator"><hr/></li>
  <li class="last"><a href="<?php echo $_ABSOLUTE_URL.'accounts/Logout.php?continue='.$_ABSOLUTE_URL; ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>include/headings/desktop/img/logout.png" width="36" height="36"/> <?php echo i18n("Exit"); ?></a></li>
</ul>

<script>
var MAINMENU_HEIGHT = 0;
function bodyOnLoad()
{
 document.addEventListener ? document.addEventListener("mouseup",hideGnujikoMainMenu,false) : document.attachEvent("onmouseup",hideGnujikoMainMenu);
 MAINMENU_HEIGHT = parseFloat(document.getElementById('gnujikomainmenu').offsetHeight);

 /* ADJUST GAPMESSAGE */
 var gmBtn = document.getElementById('gnujikodesktop-runupdatebtn');
 if(gmBtn)
  window.setTimeout(function(){gnujikodesktopbase_showGapMessage(gmBtn);}, 2000);

 if(typeof(desktopOnLoad) == "function")
  desktopOnLoad();
}

function gnujikodesktopbase_showGapMessage(btn)
{
 var gmDiv = document.getElementById('gnujikodesktop-gapmessage');
 if(!gmDiv) return;

 var left = (btn.offsetLeft+Math.floor(btn.offsetWidth/2)) - Math.floor(gmDiv.offsetWidth/2);
 gmDiv.style.marginLeft = left+"px";
 gmDiv.style.marginTop = "20px";
 gmDiv.style.visibility = "visible";
}

function gnujikodesktopbase_closeGapMessage()
{
 var gmDiv = document.getElementById('gnujikodesktop-gapmessage');
 if(gmDiv) gmDiv.style.display = "none";

 var sh = new GShell();
 sh.sendCommand("export APM_NO_SHOW_NOTIFY=1");
}

function showGnujikoMainMenu()
{
 var tb = document.getElementById("desktop-base-table");
 var menu = document.getElementById('gnujikomainmenu');
 menu.style.left = parseFloat(tb.offsetLeft)-6;
 menu.style.top = ((parseFloat(tb.offsetTop)+parseFloat(tb.offsetHeight))-MAINMENU_HEIGHT)-28;
 if(parseFloat(menu.style.top) < 0)
  menu.style.top = 0;
 menu.style.visibility = "visible";
}

function hideGnujikoMainMenu()
{
 document.getElementById('gnujikomainmenu').style.visibility="hidden";
}

<?php
if(file_exists($_BASE_PATH."share/widgets/apm.php"))
{
 ?>
 function runPackageManager(autoupdate)
 {
  var sh = new GShell();
  sh.OnFinish = function(){document.location.reload();}
  sh.sendSudoCommand("gframe -f apm"+(autoupdate ? " -params `autoupdate=true`" : ""));
 }
 <?php
}

if(file_exists($_BASE_PATH."share/widgets/config.php"))
{
 ?>
 function runConfig()
 {
  var sh = new GShell();
  sh.OnFinish = function(){document.location.reload();}
  sh.sendSudoCommand("gframe -f config --fullspace");
 }
 <?php
}
?>

</script>
<?php
}

