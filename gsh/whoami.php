<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-12-2009
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Print the effective user name
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_whoami($args, $sessid)
{
 $output = "";
 $sessInfo = sessionInfo($sessid);
 $output = $sessInfo['uname'];

 return array('message'=>$output, 'outarr'=>array('uid'=>$sessInfo['uid'],'gid'=>$sessInfo['gid'],'uname'=>$sessInfo['uname']));
}

