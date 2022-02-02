<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-09-2012
 #PACKAGE: fileupload
 #DESCRIPTION: Widget for upload files from multiple source.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:

*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_SOURCE, $_PARAMS;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_SOURCE = "";
switch($_REQUEST['source'])
{
 case 'server' : $_SOURCE = "server"; break;
 case 'google' : $_SOURCE = "google"; break;
 default : $_SOURCE = "local"; break;
}

/* EXTRACT PARAMS */
if(($_REQUEST['allowmultiple'] == "true") || ($_REQUEST['allowmultiple'] == 1))
 $_PARAMS['allowmultiple'] = true;
else
 $_PARAMS['allowmultiple'] = false;
if(($_REQUEST['showoptions'] == "true") || ($_REQUEST['showoptions'] == 1))
 $_PARAMS['showoptions'] = true;
else
 $_PARAMS['showoptions'] = false;

if($_REQUEST['allow'])
 $_PARAMS['allow'] = $_REQUEST['allow'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>File upload</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/fileupload/fileupload.css" type="text/css" />
</head><body>
<div class="fileupload-form-header"><i style='margin-left:10px;'>Carica un file</i><a href='#' class='closebtn' onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/fileupload/img/widget_close.png" border="0"/></a>

 <div style="margin:10px;margin-top:5px;">
  <input type='radio' name='uploadmode' onclick="fileupload_selectSource('local')" <?php if($_SOURCE == "local") echo "checked='true'"; ?>>Carica dal computer</input> 
  <input type='radio' name='uploadmode' onclick="fileupload_selectSource('server')" <?php if($_SOURCE == "server") echo "checked='true'"; ?>>Carica dal server</input> 
  <!-- <input type='radio' name='uploadmode' onclick="fileupload_selectSource('google')" <?php if($_SOURCE == "google") echo "checked='true'"; ?>>Cerca su Google</input> -->
  <?php
  if($_PARAMS['showoptions'])
   echo "<a href='#' style=\"float:right;font-size:10px;color:#013397;\">opzioni</a>";
  ?>
 </div>
</div>
<?php
switch($_SOURCE)
{
 case "local" : include($_BASE_PATH."share/widgets/fileupload/local.php"); break;
 case "server" : include($_BASE_PATH."share/widgets/fileupload/server.php"); break;
 case "google" : include($_BASE_PATH."share/widgets/fileupload/google.php"); break;
}
?>

<script>
function fileupload_selectSource(sourceName)
{
 var href = document.location.href.replace('#','');
 if(href.indexOf("&source=") > 0)
  document.location.href = href.replace("&source=<?php echo $_SOURCE; ?>","&source="+sourceName);
 else
  document.location.href = href+"&source="+sourceName;
}
</script>
</body></html>
<?php

