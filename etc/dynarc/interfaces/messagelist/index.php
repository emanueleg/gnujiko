<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-11-2015
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Message list interface for GShell pre-output messages.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: 
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

$_BASE_PATH = "../../../../";
include_once($_BASE_PATH."init/init1.php");

?>
<style type='text/css'>
 div.iface-msglist-container {
	 background: #ffffff;
	 width: 640px;
	 border: 1px solid #d8d8d8;
	 border-radius: 8px;
	 box-shadow: 0px 0px 5px #888888;
	 padding: 5px;
	}

 div.iface-msglist-container h3 {
	 font-family: arial, sans-serif;
	 font-size: 16px;
	 font-weight: normal;
	 margin-top: 3px;
	 margin-bottom: 3px;
	}

 div.iface-msglist-container h4 {
	 font-family: arial, sans-serif;
	 font-size: 14px;
	 font-weight: normal;
	 margin-top: 3px;
	 margin-bottom: 3px;
	}

 div.iface-msglist-terminal {
	 background: #000000;
	 color: #ffffff;
	 height: 300px;
	 overflow: auto;
	}

 div.iface-msglist-terminal div {
	 padding: 3px;
	 color: #ffffff;
	 font-family: monospace;
	 font-size: 10px;
	}

</style>

<div class="iface-msglist-container">
 <h3 id="ifacemsglist-<?php echo $_REQUEST['shellid']; ?>-title">&nbsp;</h3>
 <div class="iface-msglist-terminal" id="ifacemsglist-<?php echo $_REQUEST['shellid']; ?>">
 </div>
</div>


<script>
var SHELL_ID = "<?php echo $_REQUEST['shellid']; ?>";
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/interfaces/messagelist/iface-msglist.js" type="text/javascript"></script>
<?php

