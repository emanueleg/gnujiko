<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2014
 #PACKAGE: glight-template
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG: 18-10-2014 : Aggiunta funzione AddFile
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/attachments/glattachments.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/attachments/glattachments.css" type="text/css" />
<?php

class GLAttachments
{
 var $Items, $refAP, $refID, $refCat, $height;
 function GLAttachments($ap="",$id=0,$catid=0)
 {
  $this->Items = array();
  $this->refAP = $ap;
  $this->refID = $id;
  $this->refCat = $catid;
  $this->height = "200px";

  if($this->refAP && $this->refID)
   $this->getAttachments();
 }

 function getAttachments()
 {
  $this->Items = array();
  $ret = GShell("dynattachments list -ap '".$this->refAP."'".($this->refCat ? " -cat '".$this->refCat."'" : " -refid '".$this->refID."'"));
  if(!$ret['error'])
   $this->Items = $ret['outarr']['items'];
  return $this->Items;
 }

 function AddFile($url="")
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  if(!$url) return;
  $file = array('name'=>basename($url), 'url'=>$url);
  
  $ext = strtolower(substr($url, strrpos($url, '.')+1));
  if(file_exists($_BASE_PATH."etc/mimetypes.php"))
  {
   include_once($_BASE_PATH."etc/mimetypes.php");
   if($mimetypes[$ext])
	$type = $mimetypes[$ext];
   else if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
	$type = "WEB";
   $file['type'] = $type;
   $file['icons'] = getMimetypeIcons($type);
  }
  $this->Items[] = $file;
 }

 function Paint()
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  echo "<div class='attachments' style='height:".$this->height."'>";
  for($c=0; $c < count($this->Items); $c++)
  {
   $file = $this->Items[$c];
   if($file['type'] == "IMAGE")
    $icon = $file['url'];
   else
    $icon = ($file['icons'] && $file['icons']['size48x48']) ? $file['icons']['size48x48'] : "share/mimetypes/48x48/file.png";
   echo "<div class='attachment' style=\"background-image:url('".$_ABSOLUTE_URL.$icon."')\" filetype='".$file['type']."' attid='".$file['id']."' id='attachment-".$file['id']."' href='".$file['url']."'>";
   echo "<div class='attachbuttons'>";
   echo "<div class='attachbtnbg'></div>";
   echo "<div class='attachbtncont'>";
   echo "<img src='".$_ABSOLUTE_URL."var/templates/glight/objects/attachments/img/download.png' style='float:left;margin-right:3px' title='Scarica'/>";
   echo "<img src='".$_ABSOLUTE_URL."var/templates/glight/objects/attachments/img/edit.png' style='float:left;margin-left:0px' title='Modifica'/>";
   echo "<img src='".$_ABSOLUTE_URL."var/templates/glight/objects/attachments/img/trash.png' style='float:right' title='Rimuovi'/>";
   echo "</div>";
   echo "</div>"; // eof - attachbuttons
   echo "<div class='attachtitle' href='".$file['url']."'>".$file['name']."</div>";
   echo "</div>"; // eof - attachment
  }
  echo "</div>";
 }

}
?>

