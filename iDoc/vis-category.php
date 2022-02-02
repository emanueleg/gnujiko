<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-03-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: IDoc (Interactive Documents) - Configuration panel.
 #VERSION: 2.1beta
 #CHANGELOG:
 #TODO:
 
*/

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>iDoc/vis-category.css" type="text/css" />
<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'>
<tr><td valign='top' width='200' class='subcat-container'>
	 <ul class='catmenu'>
	  <?php
	  $ret = GShell("dynarc cat-list -ap idoc -parent `".$_REQUEST['parent']."`");
	  $list = $ret['outarr'];
	  $db = new AlpaDatabase();
	  for($c=0; $c < count($list); $c++)
	  {
	   if(!$_REQUEST['cat'] && ($c == 0))
		$_REQUEST['cat'] = $list[$c]['id'];
	   if(file_exists($_BASE_PATH."iDoc/icons/".strtolower($list[$c]['tag']).".png"))
		$icon = strtolower($list[$c]['tag']).".png";
	   else
		$icon = "other.png";
	   
	   $db->RunQuery("SELECT COUNT(*) FROM dynarc_idoc_items WHERE cat_id='".$list[$c]['id']."' AND trash='0'");
	   $db->Read();
	   $list[$c]['total_items_count'] = $db->record[0];

	   if($list[$c]['total_items_count'])
		$em = " <em>".$list[$c]['total_items_count']."</em>";
	   else
		$em = "";
	   echo "<li id='".$list[$c]['id']."' onclick='selectCat(this)'".($list[$c]['id'] == $_REQUEST['cat'] ? " class='selected'>" : ">")
		."<img src='".$_ABSOLUTE_URL."iDoc/icons/".$icon."'/> ".$list[$c]['name'].$em."</li>";
	  }
	  $db->Close();
	  ?>

	 </ul>
	</td>

	<td valign='top' class='pagecontainer'>
	<?php
	include($_BASE_PATH."iDoc/idoclist.php");
	?>

	</td></tr>
</table>

<script>
function selectCat(li)
{
 document.location.href = "index.php?<?php echo $_REQUEST['copy'] ? 'copy='.$_REQUEST['copy'].'&' : ''; ?>vis=category&parent=<?php echo $_REQUEST['parent']; ?>&cat="+li.id;
}
</script>
<?php


