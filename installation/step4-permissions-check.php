<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: makedist
 #DESCRIPTION: Permission check.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH;
//-------------------------------------------------------------------------------------------------------------------//
if(!$_REQUEST['ftp-user'] && !$_REQUEST['ftp-passwd'] && !$_REQUEST['ftp-path'])
{
 /* CHECK FILES AND FOLDERS PERMISSIONS */
 $blacklist = array();
 $ret = ls_recursiveIncludeFiles($_BASE_PATH, "","name","");
 for($c=0; $c < count($ret); $c++)
 {
  if(!is_readable($_BASE_PATH.$ret[$c]) || !is_writable($_BASE_PATH.$ret[$c]))
  {
   $blacklist[] = $ret[$c];
   if(count($blacklist) >= 50)
	break;
  }
 }
}

if(!count($blacklist))
{
 $params = "step=5&lang=".$_POST['lang'];
 $params.= "&database-host=".$_POST['database-host'];
 $params.= "&database-name=".$_POST['database-name'];
 $params.= "&database-user=".$_POST['database-user'];
 $params.= "&database-passwd=".$_POST['database-passwd'];
 $params.= "&ftp-server=".$_POST['ftp-server'];
 $params.= "&ftp-path=".$_POST['ftp-path'];
 $params.= "&ftp-user=".$_POST['ftp-user'];
 $params.= "&ftp-passwd=".$_POST['ftp-passwd'];
 $params.= "&def-file-perms=".$_POST['def-file-perms'];
 $params.= "&root-password=".$_POST['root-password'];
 $params.= "&primary-user=".$_POST['primary-user'];
 $params.= "&primary-password=".$_POST['primary-password'];
 header("Location: ".$_ABSOLUTE_URL."installation/index.php?".$params);
 exit();
}

function ls_recursiveIncludeFiles($basepath, $dir, $orderBy="name",$filters=null)
{
 $ret = array();
 $dirs = array();
 $files = array();

 $d = dir($basepath.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($basepath.$Entry)) // is a directory //
   $dirs[] = $Entry;
  else
  {
   if($filters)
   {
	$pos = strrpos($entry,".");
	if($pos === false)
	 continue;
	$ext = substr($entry,$pos+1);
	if(!in_array($ext,$filters))
	 continue;
   }
   $files[] = $Entry;
  }
 }

 if(count($files))
 {
  switch($orderBy)
  {
   case 'name' : case 'name ASC' : sort($files); break;
   case 'name DESC' : rsort($files); break;
  }
 }
 foreach($files as $k => $v)
 {
  $ret[] = ltrim($v,"/");
 }

 if(count($dirs))
 {
  switch($orderBy)
  {
   case 'name' : case 'name ASC' : sort($dirs); break;
   case 'name DESC' : rsort($dirs); break;
  }
 }
 foreach($dirs as $k => $v)
 {
  $sublist = ls_recursiveIncludeFiles($basepath,$v,$orderBy,$filters);
  for($c=0; $c < count($sublist); $c++)
   $ret[] = $sublist[$c];
 }

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
installer_begin(i18n("Install &raquo; Permission check"), sprintf(i18n("step <b>%d</b> of <b>%d</b>"),3,4));
?>
<style type='text/css'>
div.contents {background: url(img/perms.png) right 30px no-repeat;}
table.form td {
	font-family:Arial;
	font-size:13px;
	color#000000;
	padding-bottom: 10px;
}

div.filelist {
	height: 210px;
	overflow: auto;
}

div.filelist div.file {
	width: 180px;
	height: 12px;
	font-family: Arial;
	font-size: 12px;
	line-height: 12px;
	overflow: hidden;
	display: block;
	float: left;
	margin-left: 10px;
	color: #000000;
}

</style>
<?php
installer_startContents();

?>
<form action="index.php" method="POST" id='mainform'>
<input type='hidden' name='step' value="4"/>
<input type='hidden' name='lang' value="<?php echo $_REQUEST['lang']; ?>"/>
<input type='hidden' name='database-host' value="<?php echo $_REQUEST['database-host']; ?>"/>
<input type='hidden' name='database-name' value="<?php echo $_REQUEST['database-name']; ?>"/>
<input type='hidden' name='database-user' value="<?php echo $_REQUEST['database-user']; ?>"/>
<input type='hidden' name='database-passwd' value="<?php echo $_REQUEST['database-passwd']; ?>"/>
<input type='hidden' name='ftp-server' value="<?php echo $_REQUEST['ftp-server']; ?>"/>
<input type='hidden' name='ftp-path' value="<?php echo $_REQUEST['ftp-path']; ?>"/>
<input type='hidden' name='ftp-user' value="<?php echo $_REQUEST['ftp-user']; ?>"/>
<input type='hidden' name='ftp-passwd' value="<?php echo $_REQUEST['ftp-passwd']; ?>"/>
<input type='hidden' name='def-file-perms' value="<?php echo $_REQUEST['def-file-perms']; ?>"/>
<input type='hidden' name='root-password' value="<?php echo $_REQUEST['root-password']; ?>"/>
<input type='hidden' name='primary-user' value="<?php echo $_REQUEST['primary-user']; ?>"/>
<input type='hidden' name='primary-password' value="<?php echo $_REQUEST['primary-password']; ?>"/>


<div style="font-family:Arial;font-size:13px;text-align:center;padding-bottom:20px;color:#b50000;"><i><?php echo i18n("If you want to continue the installation without access to FTP is necessary that <b>all the folders and files</b> within your folder Gnujiko have permission to <b>read and write</b>."); ?></i></div>
<hr/>

<div class='filelist'>
<?php
for($c=0; $c < count($blacklist); $c++)
{
 echo "<div class='file'>".$blacklist[$c]."</div>";
}
if(count($blacklist) >= 50)
 echo "<div class='file' style='color:#3364C3;font-size:13px;height:15px;line-height:15px;'><b>".i18n('and more...')."</b></div>";
?>
</div>

</form>
<?php
installer_endContents();
?>
<div class="footer">
 <a href='#' id='submit-button' class='right-button' onclick='submit()'><span><?php echo i18n("Try again"); ?> &raquo;</span></a>
</div>

<script>
function submit()
{
 document.getElementById('mainform').submit();
}
</script>
<?php
installer_end();

