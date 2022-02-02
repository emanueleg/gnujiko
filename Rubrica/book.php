<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-10-2013
 #PACKAGE: rubrica
 #DESCRIPTION: Simple address book.
 #VERSION: 2.1beta
 #CHANGELOG: 10-10-2013 : Aggiunto campo email su lista e bug-fix.
			 25-01-2012 : Ora solamente le categorie pubblicate saranno visualizzate in rubrica.
 #TODO: manca il tasto stampa.
 
*/

include_once($_BASE_PATH."var/objects/gserppagenav/index.php");
include_once($_BASE_PATH."var/objects/dyntable/dyntable.php");

$ret = GShell("dynarc cat-list -ap rubrica -where 'published=1'");
$list = $ret['outarr'];

$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 10;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Rubrica/book.css" type="text/css" />
<div class='rubrica-book-container'>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='270' style='padding-top:15px;'>
	<?php
	for($c=0; $c < count($list); $c++)
	{
	 $catInfo = $list[$c];
     $icon = "other";
     if($catInfo['tag'] && file_exists($_BASE_PATH."Rubrica/img/folders/64x64/".$catInfo['tag'].".png"))
	  $icon = $catInfo['tag'];
	 echo "<div class='rubrica-tab".($catInfo['id'] == $_REQUEST['cat'] ? "-active" : "")."'>";
	 echo "<a href='?cat=".$catInfo['id']."'><img src='".$_ABSOLUTE_URL."Rubrica/img/folders/64x64/".$icon.".png' border='0'/> ".$catInfo['name']."</a>";
	 echo "</div>";
	}
	?>
	</td><td valign='top'>
	<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
	<tr><td valign='top' height='1%' style='padding-top:5px;'> <!-- HEADER -->
		 <?php
		 /* Get cat info */
		 $ret = GShell("dynarc cat-info -ap rubrica -id `".$_REQUEST['cat']."`");
		 if($ret['error'])
		 {
		  $catInfo = null;
		  echo "<h3 style='color:#f31903;'>".$ret['message']."</h3>";
		 }
		 else
		 {
		  $catInfo = $ret['outarr'];
		 }
		 ?>
		 <div class='rubrica-header'>
		  <div class='rubrica-header-title'><?php echo i18n('Address book'); ?> <?php echo $catInfo['name']; ?><hr class='greenbold'/></div>
		 </div>
		</td></tr>

	<tr><td valign='top' style="background:#ffffff;padding-left:16px;padding-right:16px;">
		  <!-- TOOLBARS -->
		  <div class='rubrica-toolbar'>
		  <?php
		  $ret = GShell("dynarc item-list -ap 'rubrica' -cat `".$catInfo['id']."` -limit 10 --order-by 'name ASC' -extget contacts");
		  if($ret['error'])
		   echo "<h4 style='color:#f31903'>".$ret['message']."</h4>";
		  else
		   $list = $ret['outarr']['items'];
		  $count = $ret['outarr']['count'];
		  $from = $count ? 1 : 0;
		  $to = $count > 10 ? 10 : $count;
		  ?>
		   <div id='results'>
			<table width='100%' border='0'><tr>
			 <td><span id='pagenum'><?php echo $from; ?>-<?php echo $to; ?></span> <?php echo i18n('on'); ?> <span id='pagetot'><?php echo $count; ?></span></td>
			 <td><div id='ordering'><b><?php echo i18n('In order of'); ?>:</b> [ <a href='#' id='orderby_ctime' onclick='_orderby(this)'><?php echo i18n('reg. date'); ?></a> ][ <a href='#' id='orderby_name' onclick='_orderby(this)'><?php echo i18n('alphabetical'); ?><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/uarrow.png" border='0' style='margin-left:4px;' id='orderby_arrow'/></a> ][ <a href='#' id='orderby_code_str' onclick='_orderby(this)'><?php echo i18n('code'); ?></a> ]</div></td>
			 <td><span style='font-size:12px;'><?php echo i18n('Results per page:'); ?></span>
				 <select id='rpp' onchange='rpp_change(this)'>
				  <?php
				   $xx = array(10,20,30,40,50,60,70,80,90,100);
				   for($c=0; $c < count($xx); $c++)
					echo "<option value='".$xx[$c]."'".($_RPP == $xx[$c] ? " selected='selected'>" : ">").$xx[$c]."</option>";
				  ?>
				 </select>
			 <td><div id='GSERPPAGENAVSPACE'></div></td>
			 </tr>
			</table>
		   </div>
		  </div>
		  <!-- TOOLBAR -->
		  <div class='rubrica-toolbar'>
		   <table width='100%' border='0' cellspacing='0' cellpadding='0'><tr>
			<td>
			 <input type='button' id='newbtn' value="<?php echo i18n('Add'); ?>" onclick='_new()'/>
			 <input type='button' id='copybtn' value="<?php echo i18n('Copy'); ?> &raquo;" onclick='_copy()' style="visibility:hidden;"/>
			 <input type='button' id='movebtn' value="<?php echo i18n('Move'); ?> &raquo;" onclick='_move()' style="visibility:hidden;"/>
			 <input type='button' id='deletebtn' value="<?php echo i18n('Delete selected'); ?>" onclick='_deleteSelected()' style="visibility:hidden;"/>
			</td>
			<td width='1%'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/search_icon.png"/></td>
			<td width='1%'><input type='text' id='search' value='' onchange='_search()'/></td>
			<td width='1%'><input type='button' id='searchbtn' value="<?php echo i18n('Search'); ?>" onclick='_search()'/></td>
			<td>&nbsp;</td></tr>
		   </table>
		  </div>
		  <!-- LIST -->
		  <table width='100%' id='resultstable' class='dyntable' cellspacing='0' cellpadding='0' border='0' style='margin-top:4px;'>
		   <tr><th width='60' id='column-code'><?php echo i18n('Code'); ?></th>
		   	   <th id='column-name'><?php echo i18n('Name and surname / Company name'); ?></th>
			   <th id='column-address'><?php echo i18n('Address'); ?></th>
			   <th id='column-phone'><?php echo i18n('Phone'); ?></th>
			   <th id='column-email'><?php echo i18n('Email'); ?></th>
		   </tr>
		   <?php
			for($c=0; $c < count($list); $c++)
			{
			 $itm = $list[$c];
			 echo "<tr id='".$itm['id']."'>";
			 echo "<td><a href='#' onclick='_show(".$itm['id'].")' style='color:blue;'>".$itm['code_str']."</a></td>";
			 echo "<td><a href='#' onclick='_show(".$itm['id'].")' style='color:blue;'>".$itm['name']."</a></td>";
			 if(count($itm['contacts']))
			 {
			  $address = $itm['contacts'][0]['address']." ".$itm['contacts'][0]['city'];
			  echo "<td>".$address."</td>";
			  $phone = $itm['contacts'][0]['phone'] ? $itm['contacts'][0]['phone'] : $itm['contacts'][0]['phone2'];
			  if(!$phone)
			   $phone = $itm['contacts'][0]['cell'];
			  echo "<td>".($phone ? $phone : "&nbsp;")."</td>";
			  echo "<td>".($itm['contacts'][0]['email'] ? $itm['contacts'][0]['email'] : "&nbsp;")."</td>";
			 } 
			 else
			 {
			  echo "<td>&nbsp;</td>";
			  echo "<td>&nbsp;</td>";
			  echo "<td>&nbsp;</td>";
			 }
			 echo "</tr>";
			}
		   ?>
		  </table>
		  <!-- EOF - LIST -->
		</td></tr>

	<tr><td valign='top' height='1%'> <!-- FOOTER -->
		 <div class='rubrica-footer'>
		  <div class='rubrica-footer-contents'>&nbsp;</div>
		 </div>
		</td></tr>
	</table>
	</td></tr>
</table>
</div>

<script>
var ORDERBY = "name ASC";

var SERP = new GSERPPageNav(<?php echo $count ? $count : 0; ?>,<?php echo $_RPP; ?>);
document.getElementById('GSERPPAGENAVSPACE').appendChild(SERP.O);
SERP.autoupdate = false;
SERP.OnChange = function(_currPage, _start, _rpp){
	 _update();
	}

var TB = new DynTable(document.getElementById('resultstable'),{selectable:true});
TB.OnSelect = function(sel){
	 if(sel.length)
	 {
	  document.getElementById('copybtn').style.visibility = "visible";
	  document.getElementById('movebtn').style.visibility = "visible";
	  document.getElementById('deletebtn').style.visibility = "visible";
	 }
	 else
	 {
	  document.getElementById('copybtn').style.visibility = "hidden";
	  document.getElementById('movebtn').style.visibility = "hidden";
	  document.getElementById('deletebtn').style.visibility = "hidden";
	 }
	}

function _update(pg)
{
 document.getElementById('copybtn').style.visibility = "hidden";
 document.getElementById('movebtn').style.visibility = "hidden";
 document.getElementById('deletebtn').style.visibility = "hidden";

 var _start = isNaN(pg) ? SERP.CurrentPage * SERP.ResultsPerPage : pg * SERP.ResultsPerPage;
 var qry = "";

 if(document.getElementById('search').value)
 {
  var s = htmlentities(document.getElementById('search').value);
  qry = " -where \"(name LIKE '"+s+"%' OR name LIKE '%"+s+"%' OR name LIKE '%"+s+"')\" -cat <?php echo $catInfo['id']; ?>";
 }
 else
  qry = " -cat <?php echo $catInfo['id']; ?>";

 var sh = new GShell();
 sh.OnOutput = function(o,a){_updateList(a);}
 sh.sendCommand("dynarc item-list -ap `rubrica`"+qry+" -extget contacts --return-serp-info --order-by '"+ORDERBY+"' -limit "+_start+","+SERP.ResultsPerPage);
}

function _updateList(a)
{
 TB.empty();

 if(!a || !a['items'])
 {
  SERP.O.style.display='none';
  document.getElementById('pagenum').innerHTML = "0-0";
  document.getElementById('pagetot').innerHTML = "0";
  return;
 }
 var d = new Date();

 for(var c=0; c < a['items'].length; c++)
 {
  var itm = a['items'][c];
  var r = TB.insertRow(-1);
  r.id = itm['id'];
  r.getCell('column-code').innerHTML = "<a href='#' onclick='_show("+itm['id']+")'>"+itm['code_str']+"</a>";
  r.getCell('column-name').innerHTML = "<a href='#' onclick='_show("+itm['id']+")'>"+itm['name']+"</a>";
  if(itm['contacts'])
  {
   var address = itm['contacts'][0]['address']+" "+itm['contacts'][0]['city'];
   r.getCell('column-address').innerHTML = address;
   var phone = itm['contacts'][0]['phone'] ? itm['contacts'][0]['phone'] : itm['contacts'][0]['phone2'];
   if(!phone)
	phone = itm['contacts'][0]['cell'];
   if(phone)
	r.getCell('column-phone').innerHTML = phone;
   r.getCell('column-email').innerHTML = itm['contacts'][0]['email'] ? itm['contacts'][0]['email'] : "&nbsp;";
  }
 }
 if(a['serpinfo'])
 {
  SERP.Update(a['count'],a['serpinfo']['resultsperpage'],a['serpinfo']['currentpage']-1);
  SERP.O.style.display='';
  var from = parseFloat(a['serpinfo']['datafrom'])+1;
  var to = (from-1) + parseFloat(a['serpinfo']['resultsperpage']);
  if(to > a['count'])
   to = a['count'];
  document.getElementById('pagenum').innerHTML = from+"-"+to;
  document.getElementById('pagetot').innerHTML = a['count'];
 }
}

function _show(id)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 _update();
	}
 sh.sendCommand("gframe -f rubrica.edit -params `id="+id+"`");
}

function _new()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 _show(a['id']);
	}
 sh.sendCommand("gframe -f rubrica.new -params `cat=<?php echo $catInfo['id']; ?>`");
}

function _move()
{
 var sel = TB.getSelectedRows();
 if(!sel.length)
 {
  alert("<?php echo i18n('You must select at least one element.'); ?>");
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var itmq = "";
	 for(var c=0; c < sel.length; c++)
	 {
	  itmq+= " -id "+sel[c].id;
	 }

	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var msg = "<?php echo i18n('%d contacts are moved.'); ?>";
		 alert(msg.replace('%d',sel.length));
		 _update();
		}
	 sh.sendCommand("dynarc item-move -ap rubrica -cat "+a+itmq);
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap=rubrica`");
}

function _copy()
{
 var sel = TB.getSelectedRows();
 if(!sel.length)
 {
  alert("<?php echo i18n('You must select at least one element.'); ?>");
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var itmq = "";
	 for(var c=0; c < sel.length; c++)
	 {
	  itmq+= " -id "+sel[c].id;
	 }

	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var msg = "<?php echo i18n('%d contacts has been copied.'); ?>";
		 alert(msg.replace('%d',sel.length));
		 _update();
		}
	 sh.sendCommand("dynarc item-copy -ap rubrica -cat "+a+itmq);
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap=rubrica`");
}

function _deleteSelected()
{
 if(!confirm("<?php echo i18n('Are you sure you want to delete the selected contacts?'); ?>"))
  return;

 var sel = TB.getSelectedRows();
 if(!sel.length)
 {
  alert("<?php echo i18n('You must select at least one element.'); ?>");
  return;
 }
 var itmq = "";
 for(var c=0; c < sel.length; c++)
 {
  itmq+= " -id "+sel[c].id;
 }
 
 var sh = new GShell();
 sh.OnOutput = function(){
	 _update();
	}
 sh.sendCommand("dynarc delete-item -ap `rubrica`"+itmq);
}

function _search()
{
 _update(0);
}

function _orderby(a)
{
 var __orderMethod = "";
 var img = document.getElementById('orderby_arrow');
 if(img.parentNode == a)
 {
  if(img.src.substr(img.src.length-15,15) == "/img/darrow.png")
  {
   img.src = BASE_PATH+"share/widgets/dynarc/img/uarrow.png";
   __orderMethod = "ASC";
  }
  else
  {
   img.src = BASE_PATH+"share/widgets/dynarc/img/darrow.png";
   __orderMethod = "DESC";
  }
 }
 else
 {
  img.src = BASE_PATH+"share/widgets/dynarc/img/uarrow.png";
  __orderMethod = "ASC";
  a.appendChild(img);
 }
 var __orderBy = a.id.substr(8,a.id.length);
 
 ORDERBY = __orderBy+" "+__orderMethod;
 _update(0);
}

function rpp_change(sel)
{
 SERP.ResultsPerPage = sel.value;
 _update(0);
}
</script>
<?php

