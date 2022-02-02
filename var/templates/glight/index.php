<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-03-2016
 #PACKAGE: glight-template
 #DESCRIPTION: GLight template for applications
 #VERSION: 2.8beta
 #CHANGELOG: 02-03-2016 : Aggiornato mainmenu.
			 13-02-2016 : Aggiornato mainmenu.
			 10-01-2015 : Aggiornata funzione loginRestrictedAccess
			 11-10-2014 : Sistemata grafica form accesso negato.
			 16-09-2014 : Restricted access integration with global variable RESTRICTED_ACCESS.
			 30-07-2014 : Bug fix vari.
			 09-06-2014 - Bug fix su get selected page.
			 27-02-2014 - Aggiunto traduzioni.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR, $_RESTRICTED_ACCESS;

$_TPLDIR = "var/templates/glight/";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH.'init/init1.php');
include_once($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("calendar");
LoadLanguage("glight");
//-------------------------------------------------------------------------------------------------------------------//
class GLightTemplate
{
 var $config, $title;

 function GLightTemplate($modal="application", $configfile="config.php")
 {
  global $_ABSOLUTE_URL, $_APPLICATION_CONFIG;
 
  $this->title = "Senza titolo";

  if($configfile && file_exists($configfile))
  {
   include_once($configfile);
   $this->config = $_APPLICATION_CONFIG;
   $this->title = $this->config['appname'];
  }
  else
   $this->config = array(
	 "loginrequired" => true,
	 "restrictedaccess" => false,
	 "logo" => "var/templates/glight/img/small-logo.png",
	 "showmainmenubutton" => true
	);

  if(!isset($this->config['loginrequired']))
   $this->config['loginrequired'] = true;
  if(!isset($this->config['logo']))
   $this->config['logo'] = "var/templates/glight/img/small-logo.png";
  if(!isset($this->config['showmainmenubutton']))
   $this->config['showmainmenubutton'] = true;

  $this->config['modal'] = strtoupper($modal);

  $this->cache = array(
	 "includeobjects" => array(),
	 "includeinternalobjects" => array(),
	 "includecss" => array(),
	 "bodystarted" => false
	);

  /* Get selected page */
  $this->cache['mainmenuselidx'] = -1;
  $this->cache['submenuselidx'] = -1;
  $this->cache['selectedmenuitem'] = null;
  for($c=0; $c < count($this->config['mainmenu']); $c++)
  {
   $itm = $this->config['mainmenu'][$c];
   if(!$itm['url'] || !$itm['title'])
	continue;
   $url = $this->config['basepath'].$itm['url'];
   $active = (strpos($_SERVER['REQUEST_URI'],$url) !== FALSE) ? true : false;
   if($active)
	$this->cache['mainmenuselidx'] = $c;
   if(count($itm['subitems']))
   {
    for($i=0; $i < count($itm['subitems']); $i++)
	{
	 $subitm = $itm['subitems'][$i];
	 $suburl = $this->config['basepath'].$subitm['url'];
     $subactive = (strpos($_SERVER['REQUEST_URI'],$suburl) !== FALSE) ? true : false;
	 if($subactive)
	 {
	  if($this->cache['mainmenuselidx'] < 0)
	   $this->cache['mainmenuselidx'] = $c;
	  $this->cache['submenuselidx'] = $i;
	 }
	}
   }
  }
  if($this->cache['mainmenuselidx'] < 0)
   $this->cache['mainmenuselidx'] = 0;
  if(count($this->config['mainmenu']))
   $this->title = $this->config['mainmenu'][$this->cache['mainmenuselidx']]['title'];


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
 function includeCSS($fileName)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if($this->cache['bodystarted'])
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$this->config['basepath'].$fileName."' type='text/css'/>";
  else
   $this->cache['includecss'][] = $fileName;
 }
 //----------------------------------------------------------------------------------------------//
 function includeInternalObject($objname)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if($this->cache['bodystarted'])
   include_once($_BASE_PATH."var/templates/glight/objects/".$objname."/index.php");
  else
   $this->cache['includeinternalobjects'][] = $objname;
 }
 //----------------------------------------------------------------------------------------------//
 function getPathway()
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  $ret = array();

  $x = explode("/",rtrim($this->config['basepath'],"/"));
  if(!count($x))
   $x = array(0=>rtrim($this->config['basepath'],"/"));
  $basepath = "";
  for($c=0; $c < count($x)-1; $c++)
  {
   $basepath.= $x[$c]."/";
   if(!file_exists($_BASE_PATH.$basepath."config.php"))
	break;
   $_APPLICATION_CONFIG = array();
   include_once($_BASE_PATH.$basepath."config.php");
   $pwInfo = $_APPLICATION_CONFIG['pathway'];
   $pwInfo['url'] = $_ABSOLUTE_URL.$basepath.$pwInfo['url'];
   $ret[] = $pwInfo;
  }  

  $pwInfo = $this->config['pathway'];
  $pwInfo['url'] = $_ABSOLUTE_URL.$this->config['basepath'].$pwInfo['url'];
  $ret[] = $pwInfo;
  return $ret;
 }
 //----------------------------------------------------------------------------------------------//
 function includeCoreCode()
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;
  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$_TPLDIR."glight.css' type='text/css'/>";
  include_once($_BASE_PATH."include/js/gshell.php");
  include_once($_BASE_PATH."include/layers.php");

  for($c=0; $c < count($this->cache['includeobjects']); $c++)
   include_once($_BASE_PATH."var/objects/".$this->cache['includeobjects'][$c]."/index.php");
  for($c=0; $c < count($this->cache['includeinternalobjects']); $c++)
   include_once($_BASE_PATH."var/templates/glight/objects/".$this->cache['includeinternalobjects'][$c]."/index.php");
  for($c=0; $c < count($this->cache['includecss']); $c++)
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$this->config['basepath'].$this->cache['includecss'][$c]."' type='text/css'/>";

  echo "<script language='JavaScript' src='".$_ABSOLUTE_URL.$_TPLDIR."glight.js' type='text/javascript'></script>";
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
 function Begin($title="", $modal="")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR, $_RESTRICTED_ACCESS;

  if($this->config['loginrequired'] && !loginRequired())
   die;
  if($_RESTRICTED_ACCESS && !restrictedAccess($_RESTRICTED_ACCESS))
   exit();
  if($this->config['restrictedaccess'] && !$this->loginRestrictedAccess())
   die;

  if($modal)
   $this->config['modal'] = strtoupper($modal);

  if($title) $this->title = $title;
  echo "<html>";
  echo "<head><meta http-equiv='content-type' content='text/html;charset=UTF-8'/>";
  echo "<title>".$this->title."</title>";
  echo "<link rel='shortcut icon' href='".$_ABSOLUTE_URL."share/images/favicon.png'/>";
  $this->includeCoreCode();
  echo "</head>";
  if(strtoupper($this->config['modal']) == "WIDGET")
   echo "<body><div class='glight-widget-outer'>";
  else
   echo "<body onload='bodyOnLoad()'>";
  $this->cache['bodystarted'] = true;
 }
 //----------------------------------------------------------------------------------------------//
 function Header($mode="", $contents="", $right="", $width=700)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;

  if(!$mode && (strtoupper($this->config['modal']) == 'WIDGET'))
   $mode = "widget";
  else if(!$mode)
   $mode = "default";

  if(($mode == "widget") && !$right)
   $right = "BTN_EXIT";

  echo "<div class='glight-template-header'>";
  echo "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
  echo "<tr>";
  switch($mode)
  {
   case 'default' : {
	 echo "<td valign='middle' width='270' style='height:60px;vertical-align:middle;padding-left:30px'>";
	 echo "<img src='".$_ABSOLUTE_URL.$this->config['logo']."'/></td>";
	 echo "<td width='".$width."'>";
	 echo "<span class='glight-template-hdrtitle'>".($contents ? $contents : $this->title)."</span>";
	 echo "</td>";
	} break;

   case 'search' : {
	 echo "<td valign='middle' width='270' style='height:60px;vertical-align:middle;padding-left:30px'>";
	 echo "<a href='".$_ABSOLUTE_URL."'><img src='".$_ABSOLUTE_URL.$this->config['logo']."'/></a></td>";
	 echo "<td width='".$width."'>";
	 if($contents)
	  echo $contents;
	 else
	  echo "<input type='text' class='edit' style='width:450px;float:left' placeholder='".i18n('Search...')."'/><input type='button' class='button-search'/>";
	 echo "</td>";
	} break;

   case 'widget' : {
	 echo "<td valign='middle' style='height:60px;vertical-align:middle;padding-left:10px'>";
	 echo "<span class='glight-template-hdrtitle'>".($contents ? $contents : $this->title)."</span>";
	 echo "</td>";
	} break;

  }

  echo "<td align='right' valign='middle' style='padding-right:30px;vertical-align:middle'>";
  if(strpos($right, "|") !== false)
  {
   $rightX = explode("|",$right);
   for($c=0; $c < count($rightX); $c++)
   {
    switch($rightX[$c])
    {
     case 'BTN_EXIT' : echo "&nbsp;<input type='button' class='button-exit' value='".i18n('Exit')."' onclick='Template.Exit()'/>"; break;
     case 'BTN_SAVE' : echo "&nbsp;<input type='button' class='button-blue' value='".i18n('Save and close')."' onclick='Template.SaveAndExit()'/>"; break;
    }
   }
  }
  else
  {
   switch($right)
   {
    case 'BTN_EXIT' : echo "<input type='button' class='button-exit' value='".i18n('Exit')."' onclick='Template.Exit()'/>"; break;
    case 'BTN_SAVE' : echo "<input type='button' class='button-blue' value='".i18n('Save and close')."' onclick='Template.SaveAndExit()'/>"; break;
    default : echo $right ? $right : "&nbsp;"; break;
   }
  }
  if(($mode != "widget") && $this->config['showmainmenubutton'])
   echo "<img src='".$_ABSOLUTE_URL."var/templates/glight/img/mainmenubtn.png' class='mainmenubutton' title='Menù principale' onclick='Template.ShowMainMenu(this)'/>";
   
  echo "</td></tr>";
  echo "</table>";
  echo "</div>";
 }
 //----------------------------------------------------------------------------------------------//
 function SubHeaderBegin($marginBottom=50, $leftWidth=270, $paddingLeft=30)
 {
  echo "<div class='glight-template-subheader' style='margin-bottom:".$marginBottom."px'>";
  echo "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
  echo "<tr><td".($leftWidth ? " width='".$leftWidth."'" : "")." valign='middle' style='height:50px;vertical-align:middle;padding-left:"
	.$paddingLeft."px'>";
 }
 //----------------------------------------------------------------------------------------------//
 function SubHeaderEnd()
 {
  echo "</td></tr></table>";
  echo "</div>";
 }
 //----------------------------------------------------------------------------------------------//
 function Pathway($marginBottom=50)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  echo "<div class='glight-template-pathbar' style='margin-bottom:".$marginBottom."px'>";
  echo "<ul class='glight-template-pathway' style='margin-left:20px'>";
  $list = $this->getPathway();
  for($c=0; $c < count($list); $c++)
  {
   echo "<li><a href='".$list[$c]['url']."'>".$list[$c]['title']."</a></li>";
  }
  echo "<li class='last'>".$this->title."</li>";
  echo "</ul>";
  echo "</div>";
 }
 //----------------------------------------------------------------------------------------------//
 function prepareMainMenu()
 {
  $ret = array();
  for($c=0; $c < count($this->config['mainmenu']); $c++)
   $ret[] = $this->prepareMainMenuItem($this->config['mainmenu'][$c]);

  if(!$this->cache['selectedmenuitem'] && count($ret))
  {
   $ret[0]['active'] = true;
   $this->cache['selectedmenuitem'] = $ret[0];
  }

  return $ret;
 }
 //----------------------------------------------------------------------------------------------//
 function prepareMainMenuItem($item)
 {
  if($item['type'] == "separator") return $item;
  if(!$item['url']) return $item;

  $ret = $item;
  $url = $this->config['basepath'].$item['url'];
  if(strpos($_SERVER['REQUEST_URI'],$url) !== false)
  {
   $ret['active'] = true;
   $this->cache['selectedmenuitem'] = $ret;
   return $ret;
  }
  if(is_array($item['subitems']) && count($item['subitems']))
  {
   $ret['subitems'] = array();
   for($c=0; $c < count($item['subitems']); $c++)
   {
	$subret = $this->prepareMainMenuItem($item['subitems'][$c]);
	if($subret['active'] || $subret['open'])
	 $ret['open'] = true;
	$ret['subitems'][] = $subret;
   }
  }
  return $ret;
 }
 //----------------------------------------------------------------------------------------------//
 function generateMainMenu()
 {
  // prepare main menu
  $mainMenu = $this->prepareMainMenu();

  $out.= "<ul class='glight-main-menu'>";
  
  for($c=0; $c < count($mainMenu); $c++)
   $out.= $this->recursiveInsertMenu($mainMenu[$c]);

  $out.= "</ul>";
  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function recursiveInsertMenu($item, $parent=null)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;
  
  $url = $this->config['basepath'].$item['url'];

  if($item['type'] == 'separator')
   $out = "<li class='separator'><hr/></li>";
  else
  {
   if(!$item['icon'])
   {
	if(is_array($item['subitems']) && count($item['subitems']))
	{
	 if($item['open'] || $item['active'])
	  $item['icon'] = $_ABSOLUTE_URL."var/templates/glight/img/dropdown-arrow.png";
	 else if($parent)
	  $item['icon'] = $_ABSOLUTE_URL."var/templates/glight/img/rightarrow.png";
	}
   }
   $out = "<a href='".$_ABSOLUTE_URL.$url."'><li class='item".(($item['active'] || $item['open']) ? " selected" : "")."'"
		.($item['icon'] ? " style=\"background: url('".$item['icon']."') 10px center no-repeat;\"" : "").">".$item['title']."</li></a>";
  }

  if(($item['active'] || $item['open']) && count($item['subitems']))
  {
   $out.= "<ul class='glight-sub-menu'>";
   for($c=0; $c < count($item['subitems']); $c++)
	$out.= $this->recursiveInsertMenu($item['subitems'][$c], $item);
   $out.= "</ul>";
  }

  return $out;
 }
 //----------------------------------------------------------------------------------------------//
 function Body($mode="default", $width=700, $leftContents="", $height=0)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;
  $this->cache['bodymode'] = $mode;
  $this->cache['bodywidth'] = $width;
  switch(strtolower($mode))
  {
   case 'monosection' : {
	 echo "<table width='".$width."'".($height ? " height='".$height."'" : "")." align='center' cellspacing='0' cellpadding='0' border='0'>";
	 echo "<tr><td align='center' valign='middle'>";
	} break;

   case 'default' : {
	 echo "<table width='100%'".($height ? " height='".$height."'" : "")." cellspacing='0' cellpadding='0' border='0'>";
	 echo "<tr><td width='300' valign='top'>";
	 /* Main menu */
	 echo "<ul class='glight-main-menu'>";
	 for($c=0; $c < count($this->config['mainmenu']); $c++)
	 {
	  $itm = $this->config['mainmenu'][$c];
      $url = $this->config['basepath'].$itm['url'];
      $active = ($this->cache['mainmenuselidx'] == $c) ? true : false;
	  if($itm['type'] == 'separator')
	   echo "<li class='separator'><hr/></li>";
	  else
       echo "<a href='".$_ABSOLUTE_URL.$url."'><li class='item".($active ? " selected" : "")."'"
		.($itm['icon'] ? " style=\"background: url('".$itm['icon']."') 10px center no-repeat;\"" : "").">".$itm['title']."</li></a>";
	 }
	 echo "</ul>";
	 /* EOF - MAIN MENU */
	 echo $leftContents;
	 echo "</td><td ".($width ? "width='".$width."'" : "")." valign='top'>";
	} break;

   case 'bisection' : {
	 echo "<table width='100%'".($height ? " height='".$height."'" : "")." cellspacing='0' cellpadding='0' border='0'>";
	 echo "<tr><td width='300' valign='top'>";

	 echo $this->generateMainMenu();

	 /* EOF - MAIN MENU */
	 echo $leftContents;
	 echo "</td><td valign='top'>";
	} break;

   case 'widget' : {
	 echo "<table width='".$width."'".($height ? " height='".$height."'" : "")." cellspacing='0' cellpadding='0' border='0'>";
	 echo "<tr><td style='vertical-align:top'>";
	} break;

   case 'fullspace' : {
	 // do nothing //
	} break;

  }
 }
 //----------------------------------------------------------------------------------------------//
 function Footer($contents="",$addBorderTop=false, $height=30, $borderColor='#d8d8d8', $padding=5)
 {
  switch($this->cache['bodymode'])
  {
   case 'monosection' : {
	 echo "</td></tr></table>";
	} break;

   case 'default' : {
	 echo "</td>";
	 /* right contents */
	 echo "<td>&nbsp;</td>";
	 echo "</tr></table>";
	} break;

   case 'bisection' : {
	 echo "</td><td width='30'>&nbsp;</td></tr></table>";
	} break;

   case 'widget' : {
	 echo "</td></tr></table>";
	} break;

  }

  if($contents)
  {
   if($addBorderTop)
   {
    if($this->cache['bodymode'] == "widget")
     echo "<div style='border-top:1px solid ".$borderColor.";padding:".$padding."px;height:".$height."px'>";
    else
	 echo "<div style='border-top:1px solid ".$borderColor.";padding-top:".$padding."px;padding-bottom:".$padding."px;height:".$height."px'>";
   }
   echo $contents;
   if($addBorderTop)
    echo "</div>";
  }

  if(strtoupper($this->config['modal']) == "WIDGET")
   echo "</div>";

  ?>
  <script>
  function bodyOnLoad()
  {
   Template.AppBasePath = "<?php echo $this->config['basepath']; ?>";
   Template.Modal = "<?php echo $this->config['modal']; ?>";
   Template.init();
  }
  </script>
  <?php
 }
 //----------------------------------------------------------------------------------------------//
 function End()
 {
  echo "</body></html>";
 }
 //----------------------------------------------------------------------------------------------//

}
//-------------------------------------------------------------------------------------------------------------------//

?>
