<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-01-2012
 #PACKAGE: rubrica
 #DESCRIPTION: Simple address book.
 #VERSION: 2.0beta
 #CHANGELOG: 25-01-2012 : Ora solamente le categorie pubblicate saranno visualizzate in rubrica.
 #TODO:
 
*/
?>

<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Rubrica/home.css" type="text/css" />
<div class='rubrica-home-container'>
<?php
$ret = GShell("dynarc cat-list -ap rubrica -where 'published=1'");
if($ret['error'])
{
 echo "<h3 style='color:red;'>".$ret['message']."</h3>";
}
else
{
 $list = $ret['outarr'];
 if(count($list) < 4)
 {
  $cols = count($list);
  $rows = 1;
 }
 else
 {
  $cols = ceil(count($list)/2);
  $rows = 2;
  if($cols > 5)
  {
   $cols = 5;
   $rows = ceil(count($list)/5);
  }
 }
 $idx = 0;
 ?>

 <table align='center' valign='middle' border='0' class='rubrica-home-table' height='100%'>
 <tr><td colspan="<?php echo $cols; ?>">&nbsp;</td></tr>
 <?php
 for($c=0; $c < $rows; $c++)
 {
  echo "<tr>";
  for($i=0; $i < $cols; $i++)
  {
   if($idx == count($list))
   {
	echo "<td>&nbsp;</td>";
	continue;
   }
   $catInfo = $list[$idx];
   $icon = "other";
   if($catInfo['tag'] && file_exists($_BASE_PATH."Rubrica/img/folders/128x128/".$catInfo['tag'].".png"))
	$icon = $catInfo['tag'];
   echo "<td><a href='?cat=".$catInfo['id']."'><img src='".$_ABSOLUTE_URL."Rubrica/img/folders/128x128/".$icon.".png' border='0' style='margin-bottom:12px;'/></a><br/>";
   echo "<span class='rubrica-folder-title'><a href='?cat=".$catInfo['id']."'>".$catInfo['name']."</a></span></td>";
   $idx++;
  }
  echo "</tr>";
 }
 ?>
 <tr><td colspan="<?php echo $cols; ?>">&nbsp;</td></tr>
 </table>
 <?php
}
?>
</div>
<?php

