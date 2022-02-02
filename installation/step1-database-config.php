<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-10-2016
 #PACKAGE: makedist
 #DESCRIPTION: Database config form.
 #VERSION: 2.1beta
 #CHANGELOG: 27-10-2016 : MySQLi integration.
 #TODO:
 
*/

global $_LANGUAGE, $_ABSOLUTE_URL;

$_ERR = "";

//-------------------------------------------------------------------------------------------------------------------//
if(isset($_POST['action']))
{
 switch($_POST['action'])
 {
  case 'database-check' : {
	 $dbhost = trim($_POST['database-host']);
	 $dbname = trim($_POST['database-name']);
	 $username = trim($_POST['database-user']);
	 $password = trim($_POST['database-passwd']);
 
	 $db = @mysqli_connect($dbhost,$username,$password);
	 if($db == false)
	  $_ERR = "CONNECT_FAIL";
	 else if(@mysqli_select_db($db, $dbname) && !isset($_POST['database-overwrite']))
	  $_ERR = "ALREADY_EXISTS";
	 else
	 {
	  if($_POST['database-overwrite'])
	   @mysqli_query($db, "DROP DATABASE IF EXISTS ".$dbname);
	  if(!@mysqli_query($db, "CREATE DATABASE IF NOT EXISTS ".$dbname))
	  {
	   $_ERR = "PERMISSION_DENIED";
	   break;
	  }
	  $params = "step=2&lang=".$_POST['lang'];
	  $params.= "&database-host=".$_POST['database-host'];
	  $params.= "&database-name=".$_POST['database-name'];
	  $params.= "&database-user=".$_POST['database-user'];
	  $params.= "&database-passwd=".$_POST['database-passwd'];
	  header("Location: ".$_ABSOLUTE_URL."installation/index.php?".$params);
	  exit();
	 }
	} break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
installer_begin(i18n("Install &raquo; Database configuration"), sprintf(i18n("step <b>%d</b> of <b>%d</b>"),1,4));
?>
<style type='text/css'>
table.form {background: url(img/mysql.png) top right no-repeat;}
table.form td {
	font-family:Arial;
	font-size:13px;
	color#000000;
	padding-bottom: 10px;
}

</style>
<?php
installer_startContents();

if($_ERR)
{
 echo "<div class='error-box' id='error-box' style='width:180px;height:180px;left:420px;top:150px;'>";
 switch($_ERR)
 {
  case 'CONNECT_FAIL' : echo "<h3>".i18n("Error").":</h3>".i18n("I can not connect to the MySQL server, check you have entered the correct parameters."); break;
  case 'ALREADY_EXISTS' : echo "<h3>ATTENZIONE!</h3>".sprintf(i18n("There is already a database called <b>%s</b>, you want to overwrite it?"),$_POST['database-name'])."<br/><br/><br/><a href='#' class='right-button' onclick='unoverwriteDB()'><span>".i18n("No")."</span></a><a href='#' class='right-button' onclick='overwriteDB()'><span>".i18n("Yes")."</span></a>"; break;
  case 'PERMISSION_DENIED' : echo i18n("I can connect to the MySQL server using the credentials that you gave me, but I can not create the database, that user probably does not have sufficient permissions. <br/><br/> In order to continue you must create the database manually, you should do it through the control panel or MySQL using the tools provided by your service provider / maintainer. <br/><br/> Once you have created the database click on the &lsquo;<b>Try again</b>&lsquo, to continue."); break;
 }
 echo "</div>";
}

?>
<form action="index.php" method="POST" id='mainform'>
<input type='hidden' name='action' value='database-check'/>
<input type='hidden' name='step' value="<?php echo $_REQUEST['step']; ?>"/>
<input type='hidden' name='lang' value="<?php echo $_REQUEST['lang']; ?>"/>

<table class='form' width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='middle' width='180'><b>DATABASE HOST:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Specify the server on which the database resides."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='database-host' id='database-host' value="<?php echo isset($_POST['database-host']) ? $_POST['database-host'] : 'localhost'; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>DATABASE USER:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Specify a MySQL user."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='database-user' id='database-user' value="<?php echo $_POST['database-user']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>DATABASE PASSWORD:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Enter the password for the MySQL user."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='database-passwd' id='database-passwd' value="<?php echo $_POST['database-passwd']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>DATABASE NAME:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Specify the name of the database."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='database-name' id='database-name' value="<?php echo $_POST['database-name']; ?>"/></div>
		<div class='smallgray'><?php echo i18n("Specify a MySQL user who has sufficient permissions to create databases or you will need to create the database manually."); ?></div>
	</td></tr>

</table>
</form>
<?php
installer_endContents();
?>
<div class="footer">
 <a href='#' id='submit-button' class='right-button' onclick='submit()' <?php if($_ERR == "ALREADY_EXISTS") echo "style='display:none;'"; ?>><span><?php echo i18n($_ERR ? "Try again" : "Next"); ?> &raquo;</span></a>
</div>

<script>
function submit()
{
 var _dbHost = document.getElementById('database-host').value;
 var _dbUser = document.getElementById('database-user').value;
 var _dbPasswd = document.getElementById('database-passwd').value;
 var _dbName = document.getElementById('database-name').value;

 if(!_dbHost){
  alert("<?php echo i18n('You must specify the server on which the database resides. It is usually localhost'); ?>");
  document.getElementById('database-host').focus();
  return;
 }

 if(!_dbUser){
  alert("<?php echo i18n('You must specify a user MySQL. In most cases, root or admin.'); ?>");
  document.getElementById('database-user').focus();
  return;
 }

 if(!_dbName){
  alert("<?php echo i18n('You must specify the name of the database. Ex: gnujiko'); ?>");
  document.getElementById('database-name').focus();
  return;
 }

 document.getElementById('mainform').submit();
}

function unoverwriteDB()
{
 document.getElementById('error-box').style.display = "none";
 document.getElementById('submit-button').style.display = "";
 document.getElementById('database-name').focus();
 document.getElementById('database-name').select();
}

function overwriteDB()
{
 if(!confirm("<?php echo i18n('Are you sure you want to overwrite the database'); ?> '"+document.getElementById('database-name').value+"' ?\n<?php echo i18n('All data will be lost!'); ?>"))
  return;
 
 var inp = document.createElement('INPUT');
 inp.type = "hidden";
 inp.name = "database-overwrite";
 inp.value = "true";
 document.getElementById('mainform').appendChild(inp);
 document.getElementById('mainform').submit();
}
</script>
<?php
installer_end();

