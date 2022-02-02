<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-05-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Ultimate default template for all applications, widgets and popup-message.
 #VERSION: 2.6beta
 #CHANGELOG: 14-05-2017 : Aggiunta funzione GetUserInfo.
			 07-05-2017 : Aggiunta classe Header.
			 03-05-2017 : Aggiunto nuove funzioni e colori bottoni.
			 23-04-2017 : Aggiunta funzione GetAboutConfig.
			 17-04-2017 : Aggiunte funzioni GetApplicationList e Redirect.
			 12-09-2016 : Possibilita di non caricare il file config.php
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR, $_RESTRICTED_ACCESS;

$_TPLDIR = "var/templates/gnujiko/";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH.'init/init1.php');
include_once($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
include_once($_BASE_PATH.$_TPLDIR."toolbar.php");
include_once($_BASE_PATH.$_TPLDIR."header.php");
LoadLanguage("calendar");
//-------------------------------------------------------------------------------------------------------------------//
class GnujikoTemplate
{
 var $config, $aboutConfig, $Header;

 function GnujikoTemplate($style="default", $color="default", $configfile="config.php")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_APPLICATION_CONFIG, $_TPLDIR;
  
  $this->config = array();
  if($configfile && file_exists($configfile))
  {
   include_once($configfile);
   $this->config = $_APPLICATION_CONFIG;
  }

  if(!$this->config['title'])					$this->config['title'] = "Senza titolo";
  if(!$this->config['modal'])					$this->config['modal'] = "APPLICATION";		// APPLICATION | WIDGET
  if(!isset($this->config['loginrequired']))	$this->config['loginrequired'] = true;
  
  $this->config['style'] = $style;
  $this->config['color'] = $color;


  $this->cache = array(
	 "includeobjects" => array(),
	 "includeinternalobjects" => array(),
	 "includecss" => array(),
	 "bodystarted" => false,
	);

  $this->meta = array();
  $this->aboutConfig = array();

  $this->Header = null;
  $this->MainMenu = new GnujikoTemplateMainMenu($this);
 }
 //----------------------------------------------------------------------------------------------//
 function GetApplicationList()
 {
  $ret = GShell("system app-list");
  if($ret['error']) return false;
  return $ret['outarr'];
 }
 //----------------------------------------------------------------------------------------------//
 function Redirect($url="")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  if(!$url) {header("Location: ".$_ABSOLUTE_URL); exit;}
  if((stripos($url, "http://") !== false) || (stripos($url, "https://") !== false))
   header("Location: ".$url);
  else
   header("Location: ".$_ABSOLUTE_URL.$url);
 }
 //----------------------------------------------------------------------------------------------//
 function GetAboutConfig($application="", $section="")
 {
  if(!$application) return false;
  if(is_array($this->aboutConfig[$application]))
  {
   if(!$section) return $this->aboutConfig[$application];
   if(is_array($this->aboutConfig[$application][$section]))
	return $this->aboutConfig[$application][$section];
  }

  $ret = GShell("aboutconfig get-config -app '".$application."'".($section ? " -sec '".$section."'" : ""));
  if(!$ret['error'])
  {
   if(!$section) $this->aboutConfig[$application] = $ret['outarr']['config'];
   else $this->aboutConfig[$application][$section] = $ret['outarr']['config'];
  }

  if(!is_array($this->aboutConfig[$application])) return false;
  if($section && !is_array($this->aboutConfig[$application][$section])) return false;

  return $section ? $this->aboutConfig[$application][$section] : $this->aboutConfig[$application];
 }
 //----------------------------------------------------------------------------------------------//
 function GetUserInfo($uid=null)
 {
  $ret = array();
  $db = new AlpaDatabase();
  if(!$uid)
  {
   $ret = array('uid'=>$_SESSION['UID'], 'gid'=>$_SESSION['GID'],
	'username'=>($_SESSION['FULLNAME'] ? $_SESSION['FULLNAME'] : $_SESSION['UNAME']),
	'login'=>$_SESSION['UNAME'], 'homedir'=>$_SESSION['HOMEDIR'], 'email'=>$_SESSION['email'],
	'login_time'=>$_SESSION['LOGINTIME'], 'ip'=>$_SESSION['LOGINIP']);

   // Get rubrica id
   $db->RunQuery("SELECT rubrica_id FROM gnujiko_users WHERE id='".$_SESSION['UID']."'");
   if($db->Read())
	$ret['rubrica_id'] = $db->record['rubrica_id'];
  }
  else
  {
   $db->RunQuery("SELECT group_id,username,email,fullname,homedir,rubrica_id FROM gnujiko_users WHERE id='".$uid."'");
   if($db->Read())
	$ret = array('uid'=>$uid, 'gid'=>$db->record['gid'], 
		'username'=>($db->record['fullname'] ? $db->record['fullname'] : $db->record['username']),
		'login'=>$db->record['username'], 'homedir'=>$db->record['homedir'], 'email'=>$db->record['email'], 
		'rubrica_id'=>$db->record['rubrica_id']);
  }
  $db->Close();
  return $ret;
 }
 //----------------------------------------------------------------------------------------------//


 /* PUBLIC APPLICATION FUNCTIONS */
 //----------------------------------------------------------------------------------------------//
 function AddMetaTag($tagName, $tagContent="")
 {
  $this->meta[] = array('name'=>$tagName, 'content'=>$tagContent);
 }
 //----------------------------------------------------------------------------------------------//
 function Begin($title="")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR, $_RESTRICTED_ACCESS;

  if($this->config['loginrequired'] && !loginRequired())
   die;
  if($_RESTRICTED_ACCESS && !restrictedAccess($_RESTRICTED_ACCESS))
   exit();
  if($this->config['restrictedaccess'] && !$this->loginRestrictedAccess())
   die;

  /* START HTML */
  if($title) $this->config['title'] = $title;
  //echo "<!DOCTYPE html>";   /* OCCHIO - da problemi con gframe */
  echo "<html>";
  echo "<head><meta http-equiv='content-type' content='text/html;charset=UTF-8'/>";
  echo "<title>".$this->config['title']."</title>";
  echo "<link rel='shortcut icon' href='".$_ABSOLUTE_URL."share/images/favicon.png'/>";

  // include extra meta tags
  for($c=0; $c < count($this->meta); $c++)
   echo "<meta name=\"".$this->meta[$c]['name']."\" content=\"".$this->meta[$c]['content']."\"/>";

  $this->includeCoreCode();
  echo "</head>";

  echo "<body onload='gnujikoTemplateOnLoad()'>";
  $this->cache['bodystarted'] = true;
 }
 //----------------------------------------------------------------------------------------------//
 function End()
 {
  $_JS_CODE = "";
  if($this->Header && $this->Header->JavaScript)
   $_JS_CODE.= $this->Header->JavaScript;

  ?>
  <script>
  function gnujikoTemplateOnLoad()
  {
   Template.AppBasePath = "<?php echo $this->config['basepath']; ?>";
   Template.Modal = "<?php echo $this->modal; ?>";

   <?php echo $_JS_CODE; ?>

   Template.init();
  }
  </script>
  <?php

  echo "</body></html>";
 }
 //----------------------------------------------------------------------------------------------//
 function SidebarStart($width=240, $top=60, $pos="left", $params=null)
 {
  $borderColor = ($params && $params['bordercolor']) ? $params['bordercolor'] : "#dadada";
  $backgroundColor = ($params && $params['backgroundcolor']) ? $params['backgroundcolor'] : "#ffffff";

  $style = "width:".$width."px;top:".$top."px;bottom:0px;background:".$backgroundColor.";";

  switch($pos)
  {
   case 'right' : 	$style.= "right:0px;border-left:1px solid ".$borderColor.";"; break;
   default : 		$style.= "left:0px;border-right:1px solid ".$borderColor.";"; break;
  }

  echo "<div class='gnujiko-template-sidebar-fixed' style='".$style."'>";
 }
 //----------------------------------------------------------------------------------------------//
 function SidebarEnd()
 {
  echo "</div>";
 }
 //----------------------------------------------------------------------------------------------//
 function CreateToolbar($height=44)
 {
  return new GnujikoTemplateToolbar($this, $height);
 }
 //----------------------------------------------------------------------------------------------//
 function CreateHeader($type='default')
 {
  $header = new GnujikoTemplateHeader($this,$type);
  if(!$this->Header) $this->Header = $header;
  return $header;
 }
 //----------------------------------------------------------------------------------------------//

 //----------------------------------------------------------------------------------------------//
 /* PUBLIC WIDGET FUNCTIONS */
 //----------------------------------------------------------------------------------------------//
 function StartWidget($title="", $width=640, $type="widget")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR, $_RESTRICTED_ACCESS;

  $this->config['modal'] = "WIDGET";

  /* START HTML */
  if($title) $this->config['title'] = $title;
  echo "<html>";
  echo "<head><meta http-equiv='content-type' content='text/html;charset=UTF-8'/>";
  echo "<title>".$this->config['title']."</title>";
  echo "<link rel='shortcut icon' href='".$_ABSOLUTE_URL."share/images/favicon.png'/>";
  $this->includeCoreCode();
  echo "</head>";

  echo "<body onload='gnujikoTemplateOnLoad()' style='background:transparent'>";
  $this->cache['bodystarted'] = true;

  echo "<div class='gnujiko-template-frame-body' style='width:".$width."px'>";
  echo "<div class='gnujiko-template-widget-body'>";
 }
 //----------------------------------------------------------------------------------------------//
 function EndWidget()
 {
  echo "</div>"; // EOF - body
  echo "</div>"; // EOF - frame
  $this->End();
 }
 //----------------------------------------------------------------------------------------------//

 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
 /* PRIVATE FUNCTION */
 //----------------------------------------------------------------------------------------------//
 function includeCoreCode()
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;
  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$_TPLDIR."template.css' type='text/css'/>";

  $cssFile = "";
  switch($this->config['modal'])
  {
   case 'WIDGET' : $cssFile = $_TPLDIR."styles/widgets/".$this->config['style'].".css"; break;
   default : $cssFile = $_TPLDIR."styles/applications/".$this->config['style'].".css"; break;
  }

  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$cssFile."' type='text/css'/>";
  include_once($_BASE_PATH."include/js/gshell.php");
  include_once($_BASE_PATH."include/layers.php");
  include_once($_BASE_PATH."var/objects/gaplayer/index.php");
  include_once($_BASE_PATH."var/objects/editsearch/index.php");

  switch($this->config['modal'])
  {
   case 'WIDGET' : {
	 if(file_exists($_BASE_PATH.$_TPLDIR."styles/widgets/".$this->config['style'].".css"))
	  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$_TPLDIR."styles/widgets/".$this->config['style'].".css"."' type='text/css'/>";
	} break;

   case 'APPLICATION' : {
	 if(file_exists($_BASE_PATH.$_TPLDIR."styles/applications/".$this->config['style'].".css"))
	  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$_TPLDIR."styles/applications/".$this->config['style'].".css"."' type='text/css'/>";
	} break;
  }

  for($c=0; $c < count($this->cache['includeobjects']); $c++)
   include_once($_BASE_PATH."var/objects/".$this->cache['includeobjects'][$c]."/index.php");

  for($c=0; $c < count($this->cache['includeinternalobjects']); $c++)
   include_once($_BASE_PATH.$_TPLDIR."objects/".$this->cache['includeinternalobjects'][$c]."/index.php");

  for($c=0; $c < count($this->cache['includecss']); $c++)
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$this->config['basepath'].$this->cache['includecss'][$c]."' type='text/css'/>";

  echo "<script language='JavaScript' src='".$_ABSOLUTE_URL.$_TPLDIR."template.js' type='text/javascript'></script>";

 }
 //----------------------------------------------------------------------------------------------//
 function includeObject($objname)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if($this->cache['bodystarted'])
   include_once($_BASE_PATH."var/objects/".$objname."/index.php");
  else
   $this->cache['includeobjects'][] = $objname;
 }
 //----------------------------------------------------------------------------------------------//
 function includeInternalObject($objname)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if($this->cache['bodystarted'])
   include_once($_BASE_PATH.$_TPLDIR."objects/".$objname."/index.php");
  else
   $this->cache['includeinternalobjects'][] = $objname;
 }
 //----------------------------------------------------------------------------------------------//
 function includeCSS($fileName)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if($this->cache['bodystarted'])
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$this->config['basepath'].$fileName."' type='text/css'/>";
  else
   $this->cache['includecss'][] = $fileName;
 }
 //----------------------------------------------------------------------------------------------//
 function loginRestrictedAccess()
 {
  global $_ABSOLUTE_URL;

  if($_SESSION['UNAME'] == "root")
   return true;

  $continue = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
  ?>
  <html><head><meta http-equiv='content-type' content='text/html;charset=UTF-8'/><title>Restricted access</title>
  <link rel='shortcut icon' href="<?php echo $_ABSOLUTE_URL; ?>share/images/favicon.png"/>
  <?php
  $this->includeCoreCode();
  ?>
  <script>
  function bodyOnLoad(){document.getElementById('password').focus();}
  </script>
  </head>
  <body onload="bodyOnLoad()">
  <div class='access-denied'>
   <h3>ACCESSO NEGATO</h3>
    <form method='POST' action="<?php echo $_ABSOLUTE_URL; ?>accounts/LoginAuth.php">
     <input type='hidden' name='continue' value="<?php echo $continue; ?>"/>
     <input type='hidden' name='Username' value="root"/>
     <p>L'accesso a questa pagina è riservato esclusivamente all'amministratore di sistema. (<b>root</b>)</p>
	 <table border='0'>
	 <tr><td valign='top'><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/img/access-key.png" style="vertical-align:top;text-align:left;margin:12px;float:left;"/></td>
		 <td valign='top'>	  <small>Per accedere all'area riservata devi digitare la password di <b>root</b>.</small><br/><br/>
	  <small>Password:</small><br/>
	  <input type='password' class='edit' name='Password' style="width:280px;margin-bottom:10px" maxlength='40' id='password'/> <br/>
	  <input type="submit" class="button-blue" name="signIn" value="Accedi"/>
	  <input type="button" class="button-gray" value="Annulla" onclick="document.location.href='<?php echo $_ABSOLUTE_URL; ?>'"/>
	  <?php
	  if($_REQUEST['err'] == "badlogin")
	   echo "<br/><small style='color:red;'>La password inserita è sbagliata!</small>";
	  ?></td></tr>
	 </table>
   </form>
  </div>
  </body>
  </html>
  <?php
 }
 //----------------------------------------------------------------------------------------------//
 function generateSidebarMenu($items, $_DEFAULT_COLOR='blue')
 {
  global $_ABSOLUTE_URL, $_TPLDIR;

  // Check for selected
  $selectedItem = null;
  for($c=0; $c < count($items); $c++){ if($items[$c]['selected']) { $selectedItem=$items[$c]; break; } }

  // Check by view
  if(!$selectedItem) {
   if(isset($_REQUEST['view']) && $_REQUEST['view']) {
	for($c=0; $c < count($items); $c++) { 
	 if($items[$c]['view'] == $_REQUEST['view']) { $items[$c]['selected'] = true; $selectedItem=$items[$c]; break; } } } }

  // Set first selected
  if(!$selectedItem && count($items)) { $items[0]['selected'] = true; $selectedItem = $items[0]; }

  // Paint
  $out = "<ul class='vertical-menu'>";
  for($c=0; $c < count($items); $c++)
  {
   if(isset($items[$c]['visibled']) && ($items[$c]['visibled'] == false))
	continue;
   $out.= $this->recursiveInsertMenu($items[$c], $_DEFAULT_COLOR);
  }
  $out.= "</ul>";
  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function recursiveInsertMenu($item, $_DEFAULT_COLOR='blue')
 {
  $out = "";
  $col = $item['color'] ? $item['color'] : $_DEFAULT_COLOR;
  
  if($item['type'] == "separator")
   return $out. "<li class='separator'>&nbsp;</li>";

  $out.= "<a href='".($item['url'] ? $item['url'] : '#')."'><li class='";
  if($item['selected'])	$out.= "selected ".$col."-selected";
  else $out.= "item ".$col;
  $out.= "'>";

  if($item['icon'])
   $out.= "<img src='".$item['icon']."' class='vertical-menu-icon'/>";
  $out.= $item['title'];

  $out.= "</li></a>";

  if($item['items'] && is_array($item['items']) && count($item['items']))
  {
   $subout = "";
   for($c=0; $c < count($item['items']); $c++)
   {
	if(isset($item['items'][$c]['visibled']) && ($item['items'][$c]['visibled'] == false))
	 continue;
	$subout.= $this->recursiveInsertMenu($item['items'][$c], $_DEFAULT_COLOR);
   }
   if($subout)
	$out.= "<ul class='vertical-submenu'>".$subout."</ul>";
  }

  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function generatePopupMenu($items, $id='', $menutype='')
 {
  /* MENUTYPES:
		select - popupmenu like as a select box.
  */

  global $_ABSOLUTE_URL, $_TPLDIR;
  $out = "<ul class='popupmenu' id='".$id."'>";
  if(is_array($items) && count($items))
  {
  for($c=0; $c < count($items); $c++)
  {
   $item = $items[$c];

   if($item['type'] == "separator")
   {
	$out.= "<li class='separator'";
	if($item['id'])		$out.= " id='".$item['id']."'";
	$out.= "></li>";
	continue;
   }

   $class = ($item['items'] && count($item['items'])) ? "subitem" : "";
   $title = "";
   $hideOnClick = ($item['items'] && count($item['items'])) ? false : true;
   $onClick = "";

   switch($item['type'])
   {
    case 'label' : 		{ $class="label";			$title=$item['title'];  	$hideOnClick=false; } break;
    case 'edit' : 		{ $class="edit";			$title=$item['content']; 	$hideOnClick=false; } break;
    case 'checklist' : 	{ $class="checklist";		$hideOnClick=false; } break;
    default : {
		 $title=$item['title']; 
		 $onClick = $item['onclick'] ? $item['onclick'] : ($menutype=='select' ? "this.parentNode.setSelectedItem(this)" : "");
		}break;
   }

   $out.= "<li";
   if($item['id'])		$out.= " id='".$item['id']."'";
   if($class)			$out.= " class='".$class."'";
   if($onClick)			$out.= " onclick=\"".$onClick."\"";
   if($hideOnClick)		$out.= " onmouseup='this.hideAll()'";
   if(($menutype == "select") || $item['value'])	$out.= " value=\"".$item['value']."\"";

   if(is_array($item['attributes']))
   {
	reset($item['attributes']);
	while(list($k,$v) = each($item['attributes']))
	{
	 $out.= " ".$k."=\"".$v."\"";
	}
   }

   $out.= ">";
  
   if($item['url'])
    $out.= "<a href='".$item['url']."'>";

   if(!$item['type'])
   {
	if($item['icon'])
	 $out.= "<img src='".$item['icon']."' class='icon'/>";
	else
     $out.= "&nbsp;&nbsp;";
   }
   if($title)
	$out.= "<span>".htmlspecialchars_decode($title, ENT_QUOTES)."</span>";
   if($item['type'] == "checklist")
	$out.= $this->generatePopupMenuChecklist($item['items'], $item['height']);
   else if($item['items'] && count($item['items']))
	$out.= $this->generatePopupMenu($item['items']);
   else if($item['url'])
	$out.= "</a>";

   $out.= "</li>";
  }
  }
  $out.= "</ul>";
  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function generatePopupMenuChecklist($items, $height=100, $id='')
 {
  $out = "<div class='popupmenu-checklist' style='height:".$height."px'><ul class='popupmenu-checklist' id='".$id."'>";
  for($c=0; $c < count($items); $c++)
  {
   $item = $items[$c];
   $out.= "<li><input type='checkbox'/>".$item['title']."</li>";
  }
  $out.= "</ul></div>";
  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function generateToggles($items, $id='', $className='toggles')
 {
  $out = "<ul class='".$className."'".($id ? " id='".$id."'" : "").">";
  for($c=0; $c < count($items); $c++)
  {
   $class = ""; if($c == 0) $class = "first"; else if($c == (count($items)-1)) $class = "last";
   if($items[$c]['selected']) $class = $class ? $class." selected" : "selected";
   $out.= "<li".($class ? " class='".$class."'" : "")." value='".$items[$c]['value']."'>".$items[$c]['title']."</li>";
  }  
  $out.= "</ul>";
  return $out;
 }
 //----------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GnujikoTemplateMainMenu
{
 var $template, $items;

 function GnujikoTemplateMainMenu($tplH)
 {
  $this->template = $tplH;
  $this->init($this->template->config['mainmenu']);
 }
 //------------------------------------------------------------------------------------------------------------------//
 function init($items)
 {
  $this->items = array();
  if(!is_array($items)) return;
  reset($items);
  while(list($idx,$item) = each($items)) { $this->addItem($item); }
 }
 //------------------------------------------------------------------------------------------------------------------//
 function addItem($data)
 {
  $item = new GnujikoTemplateMainMenuItem($this, $data);
  $this->items[] = $item;
  return $item;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function getItemByTag($tagName)
 {
  for($c=0; $c < count($this->items); $c++)
  {
   if($this->items[$c]->getAttribute('tag') == $tagName)
	return $this->items[$c];
  }
 }
 //------------------------------------------------------------------------------------------------------------------//
 function getItems()
 {
  // get items as array
  $ret = array();
  for($c=0; $c < count($this->items); $c++)
   $ret[] = $this->items[$c]->asArray();
  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GnujikoTemplateMainMenuItem
{
 var $MainMenu, $attributes;
 function GnujikoTemplateMainMenuItem($mainMenuH, $data=null)
 {
  $this->MainMenu = $mainMenuH;
  if(is_array($data))
  {
   reset($data);
   while(list($k,$v) = each($data)) { $this->attributes[$k] = $v; }
  }
 }
 //------------------------------------------------------------------------------------------------------------------//
 function setAttribute($name, $value) { $this->attributes[$name] = $value; }
 //------------------------------------------------------------------------------------------------------------------//
 function getAttribute($name) { return $this->attributes[$name]; }
 //------------------------------------------------------------------------------------------------------------------//
 function asArray()
 {
  $ret = array();
  reset($this->attributes);
  while(list($k,$v) = each($this->attributes)) { $ret[$k] = $v; }
  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
}


