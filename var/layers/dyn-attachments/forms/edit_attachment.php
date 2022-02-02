<?php

global $_BASE_PATH, $_ABSOLUTE_URL;

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

$ret = GShell("dynattachments info -id ".$_REQUEST['id']);
if(!$ret['error'])
 $info = $ret['outarr'];
else
{
 ?>
 <h4 style='color:#f31903;'>Error: <?php echo $ret['message']; ?></h4>
 <?php
 return;
}

?>
<div style='background:#c1d9ff;font-size:20px;font-family:Arial;height:33px;padding-top:6px;'>&nbsp;&nbsp;Modifica attributi allegato <img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/img/edit.png" align="right" valign="top" style="margin-top:-14px;"/></div>
<br/>
<p style='margin:5px;'>Titolo: <input type='text' size='30' id="edatt_<?php echo $info['id']; ?>_name" value="<?php echo $info['name']; ?>"/></p>
<p style='margin:5px;'>Tipo: <select id="edatt_<?php echo $info['id']; ?>_type"><?php
	$types = array('IMAGE'=>"Immagine","PDF"=>"PDF","AUDIO"=>"Audio","VIDEO"=>"Video","SPREADSHEET"=>"Spreadsheet","ZIP"=>"Zip","SVG"=>"SVG","WEB"=>"Web");
	while(list($k,$v) = each($types))
	{
	 echo "<option value='$k'".($info['type'] == $k ? " selected='selected'>" : ">").$v."</option>";
	}
	?></select></p>
<?php 
	if(!$info['type'] || ($info['type'] == "WEB"))
	{
	 ?>
	 <p style='margin:5px;'>URL: <input type='text' size='30' id="edatt_<?php echo $info['id']; ?>_url" value="<?php echo $info['url']; ?>"/></p>
	 <?php
	 $descRows = 3;
	}
?>	
<p style='margin:5px;'>Keywords: <input type='text' size='30' id="edatt_<?php echo $info['id']; ?>_keywords" value="<?php echo $info['keywords']; ?>"/></p>
<p style='margin:5px;'>Rendi pubblico: <input type='radio' name='attpublish' id="edatt_<?php echo $info['id']; ?>_published" <?php if($info['published']) echo "checked='true'"; ?>>Si</input> <input type='radio' name='attpublish' <?php if(!$info['published']) echo "checked='true'"; ?>>No</input></p>
<hr style='border:0px;height:1px;background:#bbbbbb;' width='100%'/>
<p style='margin:5px;'>Breve descrizione:</p>
<textarea style='width:100%;' rows="<?php echo $descRows ? $descRows : 5; ?>" id="edatt_<?php echo $info['id']; ?>_desc"><?php echo $info['description']; ?></textarea>
<hr style='border:0px;height:1px;background:#bbbbbb;' width='100%'/>
<p style='margin:5px;'><input type='button' onclick='saveAttachment(<?php echo $info['id']; ?>)' value='Salva'/> <input type='button' onclick='attachmentsFormClose()' value='Annulla'/></p>
<?php

