<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-01-2015
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Gnujiko Commercial Documents.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_SHELL_CMD_PATH;

$_APPLICATION_CONFIG = array(
	"appname"=>"Documenti Commerciali",
	"basepath"=>"GCommercialDocs/",
	"mainmenu"=>array()
);

$ret = GShell("dynarc cat-list -ap commercialdocs -where 'parent_id=0 AND published=1'");
$list = $ret['outarr'];
for($c=0; $c < count($list); $c++)
{
 $catInfo = $list[$c];
 $ct = strtolower($catInfo['tag']);
 switch($ct)
 {
  case 'preemptives' : $icon = "icons/doc-blue.png"; break;
  case 'orders' : $icon = "icons/doc-orange.png"; break;
  case 'ddt' : $icon = "icons/doc-violet.png"; break;
  case 'invoices' : $icon = "icons/doc-green.png"; break;
  case 'vendororders' : $icon = "icons/doc-red.png"; break;
  case 'purchaseinvoices' : case 'paymentnotice' : $icon = "icons/doc-yellow.png"; break;
  case 'intervreports' : $icon = "icons/doc-maroon.png"; break;
  case 'creditsnote' : $icon = "icons/doc-sky.png"; break;
  case 'debitsnote' : $icon = "icons/doc-red.png"; break;

  default : $icon = "icons/doc-gray.png"; break;
 }
 $_APPLICATION_CONFIG['mainmenu'][] = array('title'=>$catInfo['name'], 'url'=>'index.php?ct='.$ct, 'icon'=>$icon);
}

function showMainMenu($template)
{
 global $_ABSOLUTE_URL;
 echo "<ul class='glight-main-menu'>";
 for($c=0; $c < count($template->config['mainmenu']); $c++)
 {
  $itm = $template->config['mainmenu'][$c];
  if(!$itm['url'] || !$itm['title'])
  {
   // is separator
   echo "<li class='separator'>&nbsp;</li>";
   continue;
  }

  $url = $template->config['basepath'].$itm['url'];
  $active = ($template->cache['mainmenuselidx'] == $c) ? true : false;
  echo "<a href='".$_ABSOLUTE_URL.$url."'><li class='item".($active ? " selected" : "")."'>";
  echo "<img src='".$_ABSOLUTE_URL.$template->config['basepath'].$itm['icon']."'/>";
  echo "<span class='item-title-singleline'>".$itm['title']."</span>";
  if($itm['counter'])
   echo "<em>".$itm['counter']."</em>";
  echo "</li></a>";
 }
 echo "</ul>";
}

