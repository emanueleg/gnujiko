<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-06-2012
 #PACKAGE: imageupload
 #DESCRIPTION: Form for upload from the server.
 #VERSION: 2.1beta
 #CHANGELOG: 24-06-2013 : Now you can click on the image to select directly bypassing the "Select" button. (if allow_multiple = false)
 #TODO:
 #DEPENDS:
 
*/

function fullescape($in)
{
 /*Thanks to omid@omidsakhi.com that his code gave me an idea. */
 /* Full escape function without % sign */
  $out = '';
  for ($i=0;$i<strlen($in);$i++)
  {
    $hex = dechex(ord($in[$i]));
    if ($hex=='')
       $out = $out.urlencode($in[$i]);
    else
       $out = $out.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
  }
  $out = str_replace('+','20',$out);
  $out = str_replace('_','5F',$out);
  $out = str_replace('.','2E',$out);
  $out = str_replace('-','2D',$out);
  return $out;
}

?>
<div class="imageupload-form-section">
<hr style="margin-top:0px;"/>
<span style='margin-left:10px;'>Posizione: /<?php echo $_REQUEST['path']; ?></span>
<?php
if($_REQUEST['path'])
{
 if(strpos($_REQUEST['path'],"/") !== false)
 {
  $x = explode("/",$_REQUEST['path']);
  array_splice($x,count($x)-1,1);
  $retpath = implode("/",$x);
 }
 else
  $retpath = "";
 
 ?>
 
 <span id="<?php echo fullescape($retpath); ?>" class="button-blue" style="float:right;margin-right:20px;margin-top:-4px;"><span onclick='openDir(this)'>../ <small>(torna su)</small></span></span>
 <?php
}
?>
<hr style="margin-bottom:0px;"/>
</div>

<div class="imageupload-form-section" style="height:370px;overflow:auto;">

<?php
$ret = GShell("ls -filter png,jpg,jpeg,gif,bmp".($_REQUEST['path'] ? " `".$_REQUEST['path']."`" : ""));
$dirs = $ret['outarr']['dirs'];
$files = $ret['outarr']['files'];

for($c=0; $c < count($dirs); $c++)
{
 $dir = $dirs[$c];
 echo "<div class='block-dir' id='".fullescape($dir['path'])."'><img onclick='openDir(this)' src='".$_ABSOLUTE_URL."share/widgets/imageupload/img/folder.png'/><br/>";
 echo "<span onclick='openDir(this)'>".$dir['name']."</span></div>";
}

for($c=0; $c < count($files); $c++)
{
 $file = $files[$c];
 echo "<div class='block-img' id='".fullescape($file['path'])."' style=\"background-image:url('".$_ABSOLUTE_URL.$_USERS_HOMES.$_SESSION['HOMEDIR']."/".$file['path']."')\">";
 if($_PARAMS['allowmultiple'])
  echo "<input type='checkbox' class='checkbox' onchange='selectFile(this)'/>";
 echo "<span onclick='showPreview(this)'>".$file['name']."</span></div>";
}

?>

</div>

<div class="imageupload-form-footer" style="height:42px;">
	<hr style='margin-top:0px;'/>
	<span class="button-blue" style="margin-left:10px;"><span onclick="submit()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/imageupload/img/upload.png"/>Carica</span></span>

</div>

<script>
var SELECTED_FILES = new Array();
var ALLOW_MULTIPLE = <?php echo $_PARAMS['allowmultiple'] ? "true" : "false"; ?>;

function bodyOnLoad()
{
 gframe_resize(500,550);
}

function openDir(a)
{
 var fid = a.parentNode.id;
 var path = decodeFID(fid);

 var href = document.location.href.replace('#','');
 if(href.indexOf("&path=") > 0)
 {
  var x = href.indexOf("&path=")+6;
  var y = href.indexOf("&",x);
  if(y > 0)
   var oldPath = href.substr(x, y-x);
  else
   var oldPath = href.substr(x);
  document.location.href = href.replace("&path="+oldPath,"&path="+urlencode(path));
 }
 else
  document.location.href = href+"&path="+urlencode(path);
}

function selectFile(cb)
{
 var fid = cb.parentNode.id;
 cb.parentNode.className = cb.checked ? "block-img block-selected" : "block-img";

 if(cb.checked)
  SELECTED_FILES.push(fid);
}

function showPreview(a)
{
 var fid = a.parentNode.id;
 if(!ALLOW_MULTIPLE)
 {
  if(SELECTED_FILES.length)
   document.getElementById(SELECTED_FILES[0]).className = "block-img";
  a.parentNode.className = "block-img block-selected";
  SELECTED_FILES[0] = fid;
  return submit();
 }

}

function decodeFID(fid)
{
 var str = "";
 var p = 0;
 while(p < fid.length)
 {
  str+= "%"+fid.substr(p,2);
  p+= 2;
 }
 str = unescape(str);
 return str;
}

function urlencode(str) 
{
 str = escape(str);
 str = str.replace('+', '%2B');
 str = str.replace('%20', '+');
 str = str.replace('*', '%2A');
 str = str.replace('/', '%2F');
 str = str.replace('@', '%40');
 return str;
}

function submit()
{
 var ret = new Array();
 ret['mode'] = "FROM_SERVER";
 ret['files'] = new Array();

 var basepath = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'; ?>";
 for(var c=0; c < SELECTED_FILES.length; c++)
 {
  SELECTED_FILES[c] = basepath+decodeFID(SELECTED_FILES[c]);
  var arr = new Array();
  
  /* Detect file info*/
  var x = SELECTED_FILES[c].lastIndexOf(".");
  if(x < 1)
   x = SELECTED_FILES[c].length;
  else
   arr['extension'] = SELECTED_FILES[c].substr(x+1);

  var y = SELECTED_FILES[c].lastIndexOf("/");
  if(y > 0)
  {
   arr['path'] = SELECTED_FILES[c].substr(0,y).replace(basepath,"");
   arr['name'] = SELECTED_FILES[c].substr(y+1, (x-y)-1);
  }
  arr['fullname'] = SELECTED_FILES[c];

  ret['files'].push(arr);
 }
 
 if(!ret['files'].length)
  return alert("Nessun file selezionato");

 var msg = SELECTED_FILES.length > 1 ? SELECTED_FILES.length+" files selected." : "File selected: "+SELECTED_FILES[0];
 gframe_close(msg, ret);
}
</script>
<?php

