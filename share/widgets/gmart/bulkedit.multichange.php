<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-09-2013
 #PACKAGE: gmart
 #DESCRIPTION: Bulk edit - multi change.
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>BulkEdit - MultiChange</title>
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
 <h2>Azioni di gruppo</h2>
 <hr/>
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr><td width="300" valign="top">
	 <span class="smalltext">Seleziona le categorie di prodotti da modificare.</span><br/><br/>
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
	 <span class="smalltext">Seleziona le modifiche da apportare</span><br/><br/>
	 <table width='100%' cellspacing='5' cellpadding='0' border='0' class='default'>
	 <tr><td width='200'><input type='checkbox' id='cb_brand'/> Modifica marca:</td>
		 <td><input type='text' id='brand' class='edit' style='width:150px' onchange="autocheck(this)"/></td></tr>

	 <tr><td><input type='checkbox' id='cb_vendorname'/> Modifica fornitore:</td>
		 <td><input type='text' id='vendorname' class='edit' style='width:150px' onchange="autocheck(this)"/></td></tr>

	 <tr><td><input type='checkbox' id='cb_vendorprice'/> Imposta prezzo d&lsquo;acquisto:</td>
		 <td><input type='text' id='vendorprice' class='edit' style='width:50px' onchange="autocheck(this,'currency')"/> &euro;</td></tr>

	 <tr><td><input type='checkbox' id='cb_cm'/> Imposta C&M (costi e margini):</td>
		 <td><input type='text' id='cm' class='edit' style='width:50px' onchange="autocheck(this)"/> %</td></tr>

	 <tr><td><input type='radio' id='cb_baseprice' name='baseprice'/> Imposta prezzo di base:</td>
		 <td><input type='text' id='baseprice' class='edit' style='width:50px' onchange="autocheck(this,'currency')"/> &euro;</td></tr>

	 <tr><td><input type='radio' id='cb_increasebaseprice' name='baseprice'/> aumenta del:</td>
		 <td><input type='text' id='increasebaseprice' class='edit' style='width:30px' onchange="autocheck(this)"/> %</td></tr>

	 <tr><td><input type='checkbox' id='cb_discount'/> Applica uno sconto del:</td>
		 <td><input type='text' id='discount' class='edit' style='width:30px' onchange="autocheck(this)"/> % &nbsp; su 
		 <select style='width:150px' id='discount_pricelist'>
		  <option value='0'>tutti i listini</option>
		  <?php
		  for($c=0; $c < count($pricelists); $c++)
		   echo "<option value='".$pricelists[$c]['id']."'>".$pricelists[$c]['name']."</option>";
		  ?>
		 </select>
		 </td></tr>

	 <tr><td><input type='checkbox' id='cb_markuprate'/> Applica un ricarico del:</td>
		 <td><input type='text' id='markuprate' class='edit' style='width:30px' onchange="autocheck(this)"/> % &nbsp; su 
		 <select style='width:150px' id='markuprate_pricelist'>
		  <option value='0'>tutti i listini</option>
		  <?php
		  for($c=0; $c < count($pricelists); $c++)
		   echo "<option value='".$pricelists[$c]['id']."'>".$pricelists[$c]['name']."</option>";
		  ?>
		 </select>
		 </td></tr>

	 <tr><td><input type='checkbox' id='cb_vatrate'/> Modifica aliquota IVA:</td>
		 <td><input type='text' id='vatrate' class='edit' style='width:30px' onchange="autocheck(this)"/> %</td></tr>

	 <tr><td><input type='checkbox' id='cb_units'/> Modifica unit&agrave; di misura:</td>
		 <td><input type='text' id='units' class='edit' style='width:30px' onchange="autocheck(this)"/></td></tr>

	 </table>
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

 var es = EditSearch.init(document.getElementById("vendorname"), "dynarc search -ap 'rubrica' -ct VENDORS -fields code_str,name `","` --order-by `name ASC` -limit 10","id","name","items",true);
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

 var qry = "";

 if(document.getElementById('cb_brand').checked)
  qry+= " -brand `"+document.getElementById('brand').value+"`";

 if(document.getElementById('cb_vendorname').checked)
  qry+= document.getElementById('vendorname').data ? " -vendorid '"+document.getElementById('vendorname').data['id']+"'" : " -vendor `"+document.getElementById('vendorname').value+"`";

 if(document.getElementById('cb_vendorprice').checked)
  qry+= " -vendorprice '"+parseCurrency(document.getElementById('vendorprice').value)+"'";

 if(document.getElementById('cb_cm').checked)
  qry+= " -cm '"+parseFloat(document.getElementById('cm').value)+"'";

 if(document.getElementById('cb_baseprice').checked)
  qry+= " -baseprice '"+parseCurrency(document.getElementById('baseprice').value)+"'";
 else if(document.getElementById('cb_increasebaseprice').checked)
  qry+= " -increase-baseprice '"+parseFloat(document.getElementById('increasebaseprice').value)+"'";

 if(document.getElementById('cb_discount').checked)
  qry+= " -discount '"+parseFloat(document.getElementById('discount').value)+"' -discount-pricelist '"+document.getElementById('discount_pricelist').value+"'";

 if(document.getElementById('cb_markuprate').checked)
  qry+= " -markuprate '"+parseFloat(document.getElementById('markuprate').value)+"' -markuprate-pricelist '"+document.getElementById('markuprate_pricelist').value+"'";

 if(document.getElementById('cb_vatrate').checked)
  qry+= " -vatrate '"+parseFloat(document.getElementById('vatrate').value)+"'";

 if(document.getElementById('cb_units').checked)
  qry+= " -units '"+document.getElementById('units').value+"'";

 if(!qry)
  return alert("Attenzione! Non è stata selezionata nessuna modifica da fare!");

 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnPreOutput = function(){}
 sh.OnFinish = function(o,a){gframe_close(o,a);}
 sh.sendCommand("gmart bulkedit -ap `<?php echo $_AP; ?>` -cats `"+catIds.substr(1)+"`"+qry);
}

function autocheck(ed, format)
{
 var cb = document.getElementById("cb_"+ed.id);
 cb.checked = ed.value ? true : false;

 if(cb.type == "radio")
 {
  var list = document.getElementsByName(cb.name);
  for(var c=0; c < list.length; c++)
  {
   if(list[c] == cb)
	continue;
   document.getElementById(list[c].id.substr(3)).value = "";
  }
 }

 if(format)
  autoformat(ed,format);
}

function autoformat(ed, format)
{
 if(!ed.value) return;
 switch(format)
 {
  case 'currency' : ed.value = formatCurrency(parseCurrency(ed.value)); break;
 }
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
