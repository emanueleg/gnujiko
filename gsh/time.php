<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-12-2009
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Echo time
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_time($args, $sessid)
{
 $output = date('d/m/Y H:i');
 $outArr = array();
 $outArr['date'] = date('d/m/Y');
 $outArr['time'] = date('H:i');
 return array('message'=>$output, 'outarr'=>$outArr);
}
