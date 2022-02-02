<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-04-2013
 #PACKAGE: gmart
 #DESCRIPTION: Catalog choice form
 #VERSION: 2.2beta
 #CHANGELOG: 13-04-2013 : Bug fix nella creazione di nuovi cataloghi.
			 04-02-2013 : Possibilità di creare nuovi cataloghi.
 #TODO: Da fare il multi-lingua.
 
*/

basicapp_header_begin();
?>
<table width='100%' border='0' cellspacing="4" cellpadding="5">
<tr><td width='180' align='right' valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>Products/img/logo.png"/></td>
	<td>&nbsp;</td></tr>
</table>
<?php
basicapp_header_end();

basicapp_contents_begin();
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='780px' align='center' cellspacing='0' cellpadding='0' border='0' id='mastertable'>
<tr><td valign='top'><div class='catalogchoice-title'>Scegli un catalogo</div></td></tr>
<tr><td valign="top" align="right"><a href='#' onclick="newCatalog()" class="new-catalog-link"><img src="<?php echo $_ABSOLUTE_URL; ?>Products/img/add.gif" border="0"/> Crea un nuovo catalogo</a></td></tr>
<tr><td valign='top'>
	<div class="catalog-container" id="catalogcontainer">
	 <?php
	 for($c=0; $c < count($_CATALOGS); $c++)
	 {
	  $thumbnail = "img/generic-product.jpg";
	  if($_CATALOGS[$c]['thumb_img'])
	   $thumbnail = $_ABSOLUTE_URL.$_CATALOGS[$c]['thumb_img'];
	  echo "<div id='".$_CATALOGS[$c]['id']."' class='bigcatalog ".($_CATALOGS[$c]['params']['gmart-theme'] ? $_CATALOGS[$c]['params']['gmart-theme'] : "light-green")."'>";
	  echo "<div class='headtit'>CATALOGO<a class='editbtn' href='#' onclick='editCatalog(".$_CATALOGS[$c]['id'].")'><img src='".$_ABSOLUTE_URL."Products/img/edit.png' border='0' title='Modifica'/></a></div>";
	  echo "<div class='title' onclick='catalogChoice(this.parentNode.id)'><i>".$_CATALOGS[$c]['name']."</i></div>";
	  echo "<div class='label' onclick='catalogChoice(this.parentNode.id)'><i>".$_CATALOGS[$c]['name']."</i></div>";
	  echo "<div class='thumbnail' style='background-image:url(".$thumbnail.")' onclick='catalogChoice(this.parentNode.id)'>&nbsp;</div>";
	  echo "</div>";
	 }
	 ?>	 
	</div>
</td></tr>
</table>

<?php
//-------------------------------------------------------------------------------------------------------------------//
basicapp_contents_end();

if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>

<script>
function desktopOnLoad()
{
 var h = document.getElementById('mastertable').parentNode.offsetHeight;
 document.getElementById('catalogcontainer').style.height = h-120;
}

function catalogChoice(id)
{
 document.location.href = ABSOLUTE_URL+"Products/index.php?aid="+id;
}

function editCatalog(id)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 document.location.reload();
	}
 sh.sendCommand("gframe -f gmart/edit.catalog -params `id="+id+"`");
}

function newCatalog()
{
 var _name = prompt("Specifica un nome da assegnare al nuovo catalogo");
 if(!_name)
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 sh.sendSudoCommand("dynarc archive-list -type gmart -a --ret-count || dynarc new-archive -name `"+_name+"` -prefix 'gmart_'+*.count -group gmart -type gmart --default-cat-perms 660 --default-item-perms 660 -inherit gmart --hidden");
}
</script>
</body></html>
<?php

