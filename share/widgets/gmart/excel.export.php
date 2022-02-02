<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-09-2013
 #PACKAGE: gmart
 #DESCRIPTION: Export to file Excel.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";

/* GET PRICELISTS */
$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$pricelists = $ret['outarr'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Excel - Export</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/jstree/checkbox.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
?>
<style type='text/css'>
.tree {
	margin:0; 
	font-family:Verdana; 
	font-size:10px; 
}

.treediv {
	height:420px;
	overflow: auto;
}

span.smalltext {
	font-family: arial,sans-serif;
	font-size: 12px;
	font-weight: bold;
}

table.default td {
	font-family: arial,sans-serif;
	font-size: 12px;
}
</style>
</head><body>

<div class='standardwidget' style='width:800px;'>
 <h2>Esporta su file Excel.</h2>
 <hr/>
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr><td width="300" valign="top">
	 <span class="smalltext">Seleziona le categorie di prodotti da esportare.</span><br/><br/>
	 <div class='treediv'>
	  <div class="demo source" id="tree_div">
	   <ul>
		<?php
		$ret = GShell("dynarc cat-list -ap '".$_AP."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
		 echo "<li id='".$list[$c]['id']."'><a href='#'><ins>&nbsp;</ins>".html_entity_decode($list[$c]['name'],ENT_QUOTES,"UTF-8")."</a> <ul><li class='last'><a class='loading' href='#'><ins>&nbsp;</ins>Loading...</a></li></ul></li>";
		?>
	   </ul>
	  </div>
	 </div>
	 </td>

	 <td style="border-right:1px solid #eeeeee" width="25">&nbsp;</td>
	 <td width="25">&nbsp;</td>

	 <td valign="top" style="font-size:13px;font-family:arial,sans-serif">
	 <span class="smalltext">Seleziona le colonne da includere</span><br/>
	  <p>
	  <label><input type='checkbox' id='col_code' checked='true'/>codice</label><br/>
	  <label><input type='checkbox' id='col_name' checked='true'/>descrizione</label><br/>
	  <label><input type='checkbox' id='col_vendorprice' checked='true'/>prezzo di listino</label><br/>
	  <label><input type='checkbox' id='col_vatrate' checked='true'/>aliq. IVA</label><br/>
	  <label><input type='checkbox' id='col_cm' checked='true'/>C&M</label><br/>
	  <label><input type='checkbox' id='col_cost' checked='true'/>costo</label><br/>
	  <label><input type='checkbox' id='col_costvatincluded' checked='true'/>costo +IVA</label><br/>
	  <label><input type='checkbox' id='col_baseprice' checked='true'/>prezzo base</label><br/>
	  </p>
	  <br/>
     <span class="smalltext">Per ciascun listino prezzi includi queste colonne</span><br/>
	  <label><input type='checkbox' id='col_pl_markuprate' checked='true'/>% ricarico</label><br/>
	  <label><input type='checkbox' id='col_pl_discount' checked='true'/>% sconto</label><br/>
	  <label><input type='checkbox' id='col_pl_finalprice' checked='true'/>prezzo finale</label><br/>
	  <label><input type='checkbox' id='col_pl_vatrate' checked='true'/>aliq. IVA</label><br/>
	  <label><input type='checkbox' id='col_pl_finalpricevatincluded' checked='true'/>prezzo finale +IVA</label><br/>
	 </td>
 </tr>
 </table>
  <div id="footer">
   <input type='button' class='button-blue' value='Procedi' onclick='submit()'/> 
   <input type='button' class='button-gray' value='Annulla' onclick='abort()'/>
  </div>
</div>

<script>
var TREE = "";
function bodyOnLoad()
{
 $(function(){

	$("#tree_div").tree({

	 ui : {
		 theme_name: "checkbox"
		},

	 plugins: {
		 checkbox : {}
		},

	 callback : {
	  onload : function(treeobj){
		 TREE = treeobj;

		},
	  onselect : function(node,treeobj){
		},
	  onopen : function(node,treeobj){
		 if((treeobj.children(node).length == 1) && ( (treeobj.children(node)[0].className == "last leaf") || (treeobj.children(node)[0].className == "last")) )
		 {
		  var parentId = node.id;

		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
			 treeobj.remove(treeobj.children(node)[0]);
			 if(!a) return;

			 for(var c=0; c < a.length; c++)
			 {
			  var nm = a[c]['name'];
			  var newnode = treeobj.create({ data: nm, id:a[c]['id'] }, node );
			  newnode.attr("id",a[c]['id']);
			  var ldng = treeobj.create({data : "loading..." }, newnode);
			  treeobj.close_branch(newnode,true);
			  newnode.children("ul:eq(0)").remove().end().append("<ul><li class='last'><a class='loading' href='#'><ins>&nbsp;</ins>Loading...</a></li></ul>");
			 }
			}
		  sh.sendCommand("dynarc cat-list -ap `<?php echo $_AP; ?>` -parent '"+parentId+"'");
		 }
		},
	 }
	});
	});

}

function submit()
{
 var mytree = jQuery.tree.reference("#tree_div");
 mytree.focus();
 var list = $.tree.plugins.checkbox.get_checked();
 var selected = new Array();

 var catIds = "";

 for(var c=0; c < list.length; c++)
 {
  if(mytree.get_text(list[c]) == "")
   continue;
  var id = list[c].id;
  catIds+= ","+id;
 }

 if(!catIds)
  return alert("Attenzione! Non è stata selezionata nessuna categoria");


 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnPreOutput = function(){}
 sh.OnFinish = function(o,a){gframe_close(o,a);}
 sh.sendCommand("gmart export-to-excel -ap `<?php echo $_AP; ?>` -cats `"+catIds.substr(1)+"`");
}


function abort()
{
 gframe_close();
}

function close()
{
 gframe_close("done",1);
}
</script>

</body></html>
