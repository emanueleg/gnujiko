<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-05-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs - Column settings.
 #VERSION: 2.3beta
 #CHANGELOG: 31-05-2016 : Aggiunto argomento continue.
			 24-05-2016 : Aggiunta colonna ccpapply (contributo cassa previdenziale).
			 03-02-2015 : Aggiunto colonne profitto , margine e computo metrico.
 #TODO: Fare extra-columns
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_COMMERCIALDOCS_CONFIG, $_COMPANY_PROFILE;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");
//include_once($_BASE_PATH."include/company-profile.php");
//include_once($_BASE_PATH."etc/commercialdocs/config.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("gmutable");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Colonne personalizzabili");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");

$_DEF_DOC_COLUMNS = array(
	'code'=> array('title'=>'Codice', 'width'=>70),
	'vencode'=> array('title'=>"Cod. art. forn.", 'width'=>70),
	'mancode'=> array('title'=>"Cod. art. produttore.", 'width'=>70),
	'sn'=> array('title'=>"S.N.", 'width'=>100),
	'lot'=> array('title'=>"Lotto", 'width'=>100),
	'account'=> array('title'=>"Conto", 'width'=>60),
	'brand'=> array('title'=>"Marca", 'width'=>100),
	'description'=> array('title'=>"Articolo / Descrizione", 'width'=>250),
	'metric'=>array('title'=>"Computo metrico"),
	'qty'=> array('title'=>"Qta", 'width'=>40),
	'units'=> array('title'=>"U.M.", 'width'=>40),
	'coltint'=> array('title'=>"Colore/Tinta", 'width'=>100),
	'sizmis'=> array('title'=>"Taglia/Misura", 'width'=>100),
	'plbaseprice'=> array('title'=>"Pr. base", 'width'=>60),
	'plmrate'=> array('title'=>"% ric.", 'width'=>60),
	'pldiscperc'=> array('title'=>"% sconto", 'width'=>60),
	'vendorprice'=> array('title'=>"Pr. Acq.", 'width'=>60),
	'unitprice'=> array('title'=>"Pr. Unit", 'width'=>60),
	'weight'=> array('title'=>"Peso unit.", 'width'=>60),
	'discount'=> array('title'=>"Sconto", 'width'=>60),
	'discount2'=> array('title'=>"Sconto2", 'width'=>60),
	'discount3'=> array('title'=>"Sconto3", 'width'=>60),
	'vat'=> array('title'=>"I.V.A.", 'width'=>40),
	'price'=> array('title'=>"Totale", 'width'=>120),
	'profit'=> array('title'=>"Guadagno", 'width'=>60),
	'margin'=> array('title'=>"% Margine", 'width'=>60),
	'vatprice'=> array('title'=>"Tot. + IVA", 'width'=>120),
	'pricelist'=> array('title'=>"Listino", 'width'=>120),
	'docref'=> array('title'=>"Doc. di rif.", 'width'=>180),
	'vendorname'=> array('title'=>"Fornitore", 'width'=>120),
	'ritaccapply'=> array('title'=>"Rit. acc.", 'width'=>60),
	'ccpapply'=> array('title'=>"Cassa prev.", 'width'=>60)
);

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec columns");
if(!$ret['error'])
 $config = $ret['outarr']['config'];
else
 $configErr = $ret['message'];


/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Personalizza colonne</h1>
<?php
$ret = GShell("dynarc cat-list -ap commercialdocs");
$_CATEGORIES = $ret['outarr'];
$_TBIDS = array();

for($c=0; $c < count($_CATEGORIES); $c++)
{
 $catInfo = $_CATEGORIES[$c];
 $tag = strtolower($catInfo['tag']);
 $tbid = "tb-".$tag;
 $_TBIDS[] = $tbid;
 ?>
 <h3>Disposizione colonne all&lsquo;interno di <?php echo $catInfo['name']; ?></h3>
 <div class="gmutable" style="height:300px;margin-top:10px;padding:0px">
  <table id="<?php echo $tbid; ?>" class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0">
   <tr>
	<th width='32'><input type='checkbox'/></th>
	<th id='tag' width='100'>TAG</th>
	<th id='title' editable='true'>TITOLO</th>
	<th id='width' width='80' editable='true' format='number'>DIMENSIONE</th>
   </tr>
   <?php
   $_USER_COLUMNS = array();
   if(is_array($config[$tag]))
   {
	for($i=0; $i < count($config[$tag]); $i++)
	{
	 $col = $config[$tag][$i];
	 echo "<tr class='selected'><td><input type='checkbox' checked='true'/></td><td>".$col['tag']."</td><td><span class='graybold'>".$col['title']."</span></td><td><span class='graybold'>".$col['width']."</span></td></tr>";
	 $_USER_COLUMNS[$col['tag']] = true;
	}
   }
   reset($_DEF_DOC_COLUMNS);
   while(list($k,$v)=each($_DEF_DOC_COLUMNS))
   {
	if($_USER_COLUMNS[$k]) continue;
	echo "<tr><td><input type='checkbox'/></td><td>".$k."</td><td><span class='graybold'>".$v['title']."</span></td><td><span class='graybold'>"
		.$v['width']."</span></td></tr>";
   }
   ?>
  </table>
 </div>
 <?php
}
?>

<hr/>
<input type='button' class='button-blue' value="Salva configurazione" onclick="saveConfig()"/>
<br/>
<br/>
<br/>
<br/>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
var TABLES = new Array();
var tbids = "<?php echo implode(',',$_TBIDS); ?>";

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php?continue="+this.getVar('continue');
	return false;
}

Template.OnSave = function()
{
 saveConfig(function(){Template.Exit();});
 return false;
}

Template.OnInit = function(){
 var tblist = tbids.split(',');
 for(var c=0; c < tblist.length; c++)
 {
  var TB = new GMUTable(document.getElementById(tblist[c]), {autoresize:true, autoaddrows:false});
  TB.tag = tblist[c].substr(3);
  TABLES.push(TB);
 }
}

function saveConfig(callback)
{
 var xml = "";
 for(var c=0; c < TABLES.length; c++)
 {
  var TB = TABLES[c];
  xml+= "<"+TB.tag+">";
  var sel = TB.GetSelectedRows();
  for(var i=0; i < sel.length; i++)
   xml+= "<column tag=\""+sel[i].cell['tag'].getValue()+"\" title=\""+sel[i].cell['title'].getValue()+"\" width=\""+sel[i].cell['width'].getValue()+"\"/"+">";
  xml+= "</"+TB.tag+">";
 }

 saveFinish(xml,callback);
}

function saveFinish(xml,callback)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 if(callback) return callback();
	 alert("Salvataggio completato!");
	 Template.Exit();
	}
 sh.sendCommand("aboutconfig set-config -app gcommercialdocs -sec columns -xml-config `"+xml+"`");
}

</script>
<?php

$template->End();

?>
