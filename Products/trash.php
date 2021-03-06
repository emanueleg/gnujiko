<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-06-2016
 #PACKAGE: gmart
 #DESCRIPTION: Trash form
 #VERSION: 2.4beta
 #CHANGELOG: 26-06-2016 : Bugfix trash empty.
			 02-03-2016 : Aggiunto process message
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
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Products/trash-view.css" type="text/css" />
<table width='780px' align='center' cellspacing='0' cellpadding='0' border='0' id='mastertable'>
<tr><td valign='top'><div class='catalogchoice-title'>Prodotti nel cestino</div></td></tr>
<tr><td valign="top">
	<a href='#' onclick='restoreSelected()' class='smalllink'>Ripristina selezionati</a>&nbsp;&nbsp;
	<a href='#' onclick='deleteSelected()' class='smalllink'>Rimuovi dal cestino</a>&nbsp;&nbsp;
	<a href='#' onclick='emptyTrash()' class='smalllink'>Svuota cestino</a>
</td></tr>
<tr><td valign='top'>
	<div class="trash-container" id="trashcontainer">
	<table width='100%' class='itemlist' id="itemlist" cellspacing='0' cellpadding='0' border='0'>
	<tr><th width='40'><input type='checkbox' onchange='selectAll(this.checked,this)'/></th>
		<th width='40'>TIPO</td>
		<th width='180'>NOME</th>
		<th>DESCRIZIONE</th></tr>
	<?php
	$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
	$ret = GShell("dynarc trash list -ap `".$_AP."`");
	$list = $ret['outarr'];
	for($c=0; $c < count($list['categories']); $c++)
	{
	 $cat = $list['categories'][$c];
	 echo "<tr id='".$cat['id']."' type='category'>";
	 echo "<td><input type='checkbox' class='checkbox' onchange='itemSelect(this)'/>";
	 echo "<td><img src='".$_ABSOLUTE_URL."Products/img/folder.png' width='22'/></td>";
	 echo "<td><div class='title'><span>".($cat['name'] ? $cat['name'] : 'senza nome')."</span></div>";
	 echo "<div class='info'><i>code:</i> <b>".$item['code_str']."</b></div></td>";
	 echo "<td><div class='description'>".$cat['desc']."</div></td>";
	}
	for($c=0; $c < count($list['items']); $c++)
	{
	 $item = $list['items'][$c];
	 echo "<tr id='".$item['id']."' type='item'>";
	 echo "<td><input type='checkbox' class='checkbox' onchange='itemSelect(this)'/>";
	 echo "<td><img src='".$_ABSOLUTE_URL."Products/img/product.png' width='22'/></td>";
	 echo "<td><div class='title'><span>".($item['name'] ? $item['name'] : 'senza nome')."</span></div>";
	 echo "<div class='info'><i>code:</i> <b>".$item['code_str']."</b></div></td>";
	 echo "<td><div class='description'>".$item['desc']."</div></td>";
	}
	?>
	</table>
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
 document.getElementById('trashcontainer').style.height = h-120;
}

function itemSelect(cb)
{
 var tr = cb.parentNode.parentNode;
 if(cb.checked)
 {
  tr.className = "selected";
  //gframe_shotmessage("Item select", tr.id, "SELECT");
 }
 else
 {
  tr.className = "";
  //gframe_shotmessage("Item unselect", tr.id, "UNSELECT");
 }
}

function selectAll(selected,cbObj)
{
 if(cbObj)
 { 
  var tb = cbObj.parentNode.parentNode.parentNode;
  for(var c=1; c < tb.rows.length; c++)
  {
   var cb = tb.rows[c].cells[0].getElementsByTagName('INPUT')[0];
   if(cb.checked != selected)
   {
    cb.checked = selected;
    itemSelect(cb);
   }
  }
 }
}

function restoreSelected()
{
 var tb = document.getElementById('itemlist');
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.cells[0].getElementsByTagName('INPUT')[0].checked == true)
  {
   if(r.getAttribute('type') == 'category')
	q+= " -cat "+r.id;
   else
	q+= " -id "+r.id;
  }
 }
 if(!q)
  return alert("Nessun elemento ?? stato selezionato");
 if(!confirm("Gli elementi selezionati verranno ripristinati. Continuare?"))
  return;

 var sh = new GShell();
 sh.showProcessMessage("Ripristino in corso","Attendere prego, &egrave; in corso l&lsquo;operazione di ripristino");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(){
	 this.hideProcessMessage();
	 alert("Gli elementi selezionati sono stati ripristinati.");
	 document.location.href="index.php";
	}
 sh.sendCommand("dynarc trash restore -ap `<?php echo $_AP; ?>`"+q);
}

function deleteSelected()
{
 var tb = document.getElementById('itemlist');
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.cells[0].getElementsByTagName('INPUT')[0].checked == true)
  {
   if(r.getAttribute('type') == 'category')
	q+= " -cat "+r.id;
   else
	q+= " -id "+r.id;
  }
 }
 if(!q)
  return alert("Nessun elemento ?? stato selezionato");
 if(!confirm("Gli elementi selezionati verranno rimossi permanentemente. Continuare?"))
  return;

 var sh = new GShell();
 sh.showProcessMessage("Rimozione dal cestino","Attendere prego, &egrave; in corso la rimozione degli elementi selezionati dal cestino");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(){
	 this.hideProcessMessage();
	 alert("Gli elementi selezionati sono stati rimossi dal cestino.");
	 document.location.href="index.php";
	}
 sh.sendCommand("dynarc trash remove -ap `<?php echo $_AP; ?>`"+q);
}

function emptyTrash()
{
 if(!confirm("Sei sicuro di voler svuotare il cestino?"))
  return;

 var sh = new GShell();
 sh.showProcessMessage("Svuotamento cestino","Attendere prego, &egrave; in corso lo svuotamento del cestino");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(){
	 this.hideProcessMessage();
	 alert("Il cestino ?? stato svuotato");
	 document.location.href="index.php";
	}
 sh.sendCommand("dynarc trash empty -ap `<?php echo $_AP; ?>`");
}

</script>
</body></html>
<?php

