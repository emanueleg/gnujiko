<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-09-2011
 #PACKAGE: ebook
 #DESCRIPTION: Default template for e-book
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

$PGI = array();
//-------------------------------------------------------------------------------------------------------------------//
function manual_setup($data)
{
 global $PGI;
 $PGI = $data;
}
//-------------------------------------------------------------------------------------------------------------------//
function manual_header()
{
 global $PGI;
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $PGI['this'][1]; ?></title></head>
 <link rel='shortcut icon' href='../images/favicon.png' />
 <link rel="stylesheet" href="../include/common.css" type="text/css" />
 <body>

 <table width='908' align='center' cellspacing='0' cellpadding='0' border='0' class='manual-header'>
 <tr><td width='300' valign='top' class='manual-header-left'><a href='index.php'><img src='../images/logo.png' border='0'/></a></td>
	 <td valign='top' class='manual-header-right'>
	  <ul class='topmenu'>
		<?php
		$c = 1;
		if($PGI['ms'])
		{
		 foreach($PGI['ms'] as $k => $secInfo)
		 {
		  echo "<li class='col".$c.($secInfo[0] == $PGI['section'][0] ? " active'>" : "'>")."<a href='".$secInfo[0]."'>".$secInfo[1]."</a></li>";
		  $c++;
		 }
		}
		?>
	  </ul>
     <br/>
	 <hr class='silver'/>
	 </td></tr>
 <tr><td valign='top' class='manual-sidebar'><?php manual_sidebar(); ?></td>
	 <td valign='top' class='manual-contents'>

 <?php
}
//-------------------------------------------------------------------------------------------------------------------//
function manual_sidebar()
{
 global $PGI;
 echo "<ul class='mainmenu'>";
 echo "<li class='mainmenu-home'><a href='../../../'>Home di Gnujiko</a></li>";
 echo "<li class='mainmenu-home'><a href='".$PGI['home'][0]."'>".$PGI['home'][1]."</a></li>";
 if(count($PGI['parents']))
 {
  arsort($PGI['parents']);
  foreach($PGI['parents'] as $k => $parentInfo) 
  {
   if($parentInfo[0] == "index.php")
	continue;
   echo "<li class='mainmenu-section'><a href='".$parentInfo[0]."'>".$parentInfo[1]."</a></li>";
  }
  if($PGI['up'][0])
  {
   echo "<li class='mainmenu-section'><a href='".$PGI['up'][0]."'>".$PGI['up'][1]."</a></li>";
  }
 }
 

 if(count($PGI['toc']))
 {
  foreach($PGI['toc'] as $k => $tocInfo)
  {
   echo "<li".($tocInfo[0] == $PGI['this'][0] ? " class='active'>" : ">")."<a href='".$tocInfo[0]."'>".$tocInfo[1]."</a></li>";
  }
 }

 
 echo "</ul>";
}
//-------------------------------------------------------------------------------------------------------------------//
function manual_navbar()
{
}
//-------------------------------------------------------------------------------------------------------------------//
function manual_footer()
{
 global $PGI;
 ?>
 </td></tr>
 <tr><td valign='top' class='manual-footer-left'>&nbsp;</td>
	 <td valign='top' class='manual-footer-right'>
	 <hr class='silver' style='margin-left:20px;margin-right:20px;margin-bottom:0px;'/>
	 <table width='100%' border='0' cellspacing='0' cellpadding='10'>
	 <tr><td align='left' style='font-size:14px;'><?php
		 if($PGI['prev'][0])
		  echo "&laquo; <a href='".$PGI['prev'][0]."'>".$PGI['prev'][1]."</a>";
		 else
		  echo "&nbsp;";
		?></td> <td align='right' style='font-size:14px;'><?php
		 if($PGI['next'][0])
		  echo "<a href='".$PGI['next'][0]."'>".$PGI['next'][1]."</a> &raquo;";
		 else
		  echo "&nbsp;";
		?></td></tr>
	 <tr><td>&nbsp;</td><td align='right' style='font-size:10px;'>Ultimo aggiornamento: <?php
		if($PGI['lastupdate'][0])
		 echo date('D, j M Y',strtotime($PGI['lastupdate'][0]));
		else
		 echo date('D, j M Y');
		?></td></tr>
	 </table>
	 <hr class='silver' style='margin-left:20px;margin-right:20px;margin-top:0px;'/>
	 <div align='center' style='font-size:12px;color:#888888;margin-bottom:12px;'>
	  Gnujiko 10.1 is free software released under GNU/GPL License<br/>developed by Alpatech mediaware.
	 </div>
	 </td></tr>
 </table>
 </body></html>
 <?php
}
//-------------------------------------------------------------------------------------------------------------------//


