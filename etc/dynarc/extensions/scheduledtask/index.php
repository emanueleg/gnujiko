<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2014 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 28-08-2014
#PACKAGE: scheduledtasks
#DESCRIPTION: 
#VERSION: 2.0beta
#CHANGELOG: 
#TODO: 
*/

global $_BASE_PATH;

function dynarcextension_scheduledtask_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `status` TINYINT(1) NOT NULL ,
 ADD `xmlparams` LONGTEXT NOT NULL ,
 ADD `shellcommand` LONGTEXT NOT NULL ,
 ADD `postcommand` LONGTEXT NOT NULL ,
 ADD `executer_file` VARCHAR(255) NOT NULL ,
 ADD `executer_name` VARCHAR(64) NOT NULL ,
 ADD `executer_action` VARCHAR(64) NOT NULL ,
 ADD INDEX (`status`)");
 $db->Close();
 return array("message"=>"ScheduledTask extension has been installed into archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `status`, DROP `xmlparams`, DROP `shellcommand`,
  DROP `postcommand`, DROP `executer_file`, DROP `executer_name`, DROP `executer_action`");
 $db->Close();
 return array("message"=>"ScheduledTask extension has been removed from archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_scheduledtask_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'status' : {$status=$args[$c+1]; $c++;} break;
   case 'xmlparams' : {$xmlParams=$args[$c+1]; $c++;} break;
   case 'shellcommand' : {$shellCommand=$args[$c+1]; $c++;} break;
   case 'postcommand' : {$postCommand=$args[$c+1]; $c++;} break;
   case 'executerfile' : case 'exefile' : {$executerFile=$args[$c+1]; $c++;} break;
   case 'executername' : case 'exename' : {$executerName=$args[$c+1]; $c++;} break;
   case 'executeraction' : case 'exeaction' : {$executerAction=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q = "";
 if(isset($status))			$q.= ",status='".$status."'";
 if(isset($xmlParams))		$q.= ",xmlparams='".$db->Purify($xmlParams)."'";
 if(isset($shellCommand))	$q.= ",shellcommand='".$db->Purify($shellCommand)."'";
 if(isset($postCommand))	$q.= ",postcommand='".$db->Purify($postCommand)."'";
 if(isset($executerFile))	$q.= ",executer_file='".$db->Purify($executerFile)."'";
 if(isset($executerName))	$q.= ",executer_name='".$db->Purify($executerName)."'";
 if(isset($executerAction))	$q.= ",executer_action='".$db->Purify($executerAction)."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_scheduledtask_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 $all = false;
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'status' : $status=true; break;
   case 'xmlparams' : $xmlParams=true; break;
   case 'shellcommand' : $shellCommand=true; break;
   case 'postcommand' : $postCommand=true; break;
   case 'executer' : $executer=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT status,xmlparams,shellcommand,postcommand,executer_file,executer_name,executer_action FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();

 if($status || $all)			$itemInfo['status'] = $db->record['status'];
 if($xmlParams || $all)
 {
  include_once($_BASE_PATH."var/lib/xmllib.php");
  $itemInfo['xmlparams'] = array();
  if($db->record['xmlparams'])
  {
   $xmlParams = ltrim(rtrim($db->record['xmlparams']));
   $xml = new GXML();
   if($xml->LoadFromString("<xml>".$xmlParams."</xml>"))
    $_XML_PARAMS = $xml->toArray();
   else
    $_XML_PARAMS = array();
   if($_XML_PARAMS['config'])
    $itemInfo['xmlparams'] = $_XML_PARAMS['config'];
   else
    $itemInfo['xmlparams'] = $_XML_PARAMS;
  }
 }
 if($shellCommand || $all)		$itemInfo['shellcommand'] = $db->record['shellcommand'];
 if($postCommand || $all)		$itemInfo['postcommand'] = $db->record['postcommand'];
 if($executer || $all)			
 {
  $itemInfo['executer_file'] = $db->record['executer_file'];
  $itemInfo['executer_name'] = $db->record['executer_name'];
  $itemInfo['executer_action'] = $db->record['executer_action'];
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 $xml = "<scheduledtask />";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 if(!$node)
  return ;

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_scheduledtask_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
