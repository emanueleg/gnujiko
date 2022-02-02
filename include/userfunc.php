<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-06-2017
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Common users and groups functions
 #VERSION: 2.9beta
 #CHANGELOG: 05-06-2017 : Aggiornata funzione _getGroupUserList.
			 20-05-2017 : Bug fix with shared on functions canRead, canWrite, canExecute.
			 07-02-2017 : Aggiunta funzione _getUserHomedir.
			 19-04-2013 : Bug fix in function _userGroups()
			 12-01-2013 : Aggiunto funzioni _getUserName e _getGroupName
			 13-11-2012 : Bug fix in function _userInGroupId.
			 02-08-2012 : Bug fix.
			 30-04-2012 : Aggiunto funzione _getUID + gestione condivisioni su GMOD.
			 25-01-2012 : Bug fix in function _userInGroup.
 #TODO:
 
*/


function _isRoot($uid=0)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM gnujiko_users WHERE id='".$uid."'");
 if(!$db->Read())
  return false;
 if($db->record['name'] != "root")
  return false;
 $db->Close();
 return true;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getGID($groupName)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='".$groupName."' LIMIT 1");
 if(!$db->Read())
  return false;
 $gid = $db->record['id'];
 $db->Close();
 return $gid;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getUID($userName)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='".$userName."' LIMIT 1");
 if(!$db->Read())
  return false;
 $uid = $db->record['id'];
 $db->Close();
 return $uid;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getUserName($uid)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$uid."' LIMIT 1");
 $db->Read();
 $ret = $db->record['username'];
 $db->Close();
 return $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getGroupName($gid)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$gid."' LIMIT 1");
 $db->Read();
 $ret = $db->record['name'];
 $db->Close();
 return $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getUserHomedir($uid=0, $sessid=0)
{
 global $_USERS_HOMES;

 $ret = "";
 if(!$uid && $sessid)
 {
  $sessInfo = sessionInfo($sessid);
  if($sessInfo['uname'] == "root")
   return $ret;
  $uid = $sessInfo['uid'];
 }
 if(!$uid) return false;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$uid."'");
 if(!$db->Read()) { $db->Close(); return false; }
 $ret = $_USERS_HOMES.$db->record['homedir']."/";
 $db->Close();

 return $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
function _userInGroup($groupName, $uid=null)
{
 //--- return true if user is into group ---//
 if(!$uid && $_SESSION['UNAME'] == 'root')
  return true;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_groups WHERE name='".$groupName."'");
 if(!$db->Read())
  return false;

 $gid = $db->record['id'];

 if(!$uid && ($_SESSION['GID'] == $gid))
  return true;
 
 $db->Close();
 return _userInGroupId($gid,$uid);
}
//----------------------------------------------------------------------------------------------------------------------//
function _userInGroupId($gid, $uid=null)
{
 //--- return true if user is into group ---//
 if(!$uid && ($_SESSION['UNAME'] == 'root'))
  return true;

 if(!$uid)
  $uid = $_SESSION['UID'];

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT group_id FROM gnujiko_users WHERE id='".$uid."'");
 $db->Read();
 if($db->record['group_id'] == $gid)
 {
  $db->Close();
  return true;
 }
 $db->RunQuery("SELECT * FROM gnujiko_usergroups WHERE gid='".$gid."' AND uid='".$uid."'");
 if(!$db->Read())
 {
  $db->Close();
  return false;
 }
 $db->Close();
 return true;
}
//----------------------------------------------------------------------------------------------------------------------//
function _userGroups($uid=null)
{
 //--- return a list of all user groups ---//
 $ret = array();

 if(!$uid)
  $uid = $_SESSION['UID'];

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT group_id,username FROM gnujiko_users WHERE id='".$uid."'");
 if(!$db->Read())
 {
  $db->Close();
  return $ret;
 }

 if($db->record['username'] != "root")
 {
  $db2 = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_usergroups WHERE uid='".$uid."'");
  while($db->Read())
  {
   $db2->RunQuery("SELECT id,name FROM gnujiko_groups WHERE id='".$db->record['gid']."'");
   $db2->Read();
   $ret[] = array('id'=>$db2->record['id'],'name'=>$db2->record['name']);
  }
  $db2->Close();
 }
 else
 {
  $db->RunQuery("SELECT id,name FROM gnujiko_groups WHERE 1 ORDER BY name ASC");
  while($db->Read())
  {
   $ret[] = array('id'=>$db->record['id'],'name'=>$db->record['name']);
  }
 }
 $db->Close();
 return $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
function _getGroupUserList($gid=null, $returnOnlyIds=false)
{
 //--- return a list of all users(members) of the group ---//
 $ret = array();
 $ids = array();

 $db = new AlpaDatabase();
 $qry = "SELECT u.id,u.username,u.fullname,u.email,u.homedir FROM gnujiko_usergroups AS i";
 $qry.= " LEFT JOIN gnujiko_users AS u ON u.id=i.uid";
 $qry.= " WHERE i.gid='".($gid ? $gid : $_SESSION['GID'])."'";
 $db->RunQuery($qry);
 while($db->Read())
 {
  $ret[] = array('id'=>$db->record['id'], 'name'=>$db->record['username'], 'fullname'=>$db->record['fullname'],
	'email'=>$db->record['email'], 'homedir'=>$db->record['homedir']);
  $ids[] = $db->record['id'];
 }
 $db->Close();

 return $returnOnlyIds ? $ids : $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
define('GMOD_READABLE',4);
define('GMOD_WRITEABLE',2);
define('GMOD_EXECUTABLE',1);
class GMOD
{
 var $MOD, $OWNER, $GROUP, $SHGROUPS, $SHUSERS;
 function GMOD($mod=null, $owner_id=null, $group_id=null, $shGroups="", $shUsers="")
 {
  $this->MOD = 0;
  $this->OWNER = $owner_id ? $owner_id : $_SESSION['UID'];
  $this->GROUP = $group_id ? $group_id : $_SESSION['GID'];
  $this->SHGROUPS = array();
  $this->SHUSERS = array();

  $this->set($mod,$owner_id,$group_id,$shGroups,$shUsers);
 }
 
 function set($mod=777, $owner_id=null, $group_id=null, $shGroups="", $shUsers="")
 {
  if($owner_id)
   $this->OWNER = $owner_id;
  if($group_id)
   $this->GROUP = $group_id;

  /* shared groups */
  if($shGroups != "")
  {
   $tmp = str_replace(array('#,',',#'), "", $shGroups);
   $x = explode(",",$tmp);
   for($c=0; $c < count($x); $c++)
   {
	$xx = explode("=",$x[$c]);
	if(!$xx[0])
	 continue;
	$this->SHGROUPS[trim($xx[0])] = trim($xx[1]);
   }
  }

  /* shared users */
  if($shUsers != "")
  {
   $tmp = str_replace(array('#,',',#'), "", $shUsers);
   $x = explode(",",$tmp);
   for($c=0; $c < count($x); $c++)
   {
	$xx = explode("=",$x[$c]);
	if(!$xx[0])
	 continue;
	$this->SHUSERS[trim($xx[0])] = trim($xx[1]);
   }
  }

  if(is_numeric($mod))
  {
   if($mod == 0)
    $this->MOD = 0;
   else if($mod > 777)
	$this->MOD = 777;
   else
	$this->MOD = $mod;
  }
  else
  {
   $l = strlen($mod);
   for($c=0; $c < $l; $c++)
   {
	$n = pow(10,floor(($c > 0 ? $c : 1)/3));
	switch($mod[$l-$c-1])
	{
	 case 'r' : $this->MOD+=(4*$n); break;
	 case 'w' : $this->MOD+=(2*$n); break;
	 case 'x' : $this->MOD+=(1*$n); break;
	}
   }
  }
 }

 function toString()
 {
  $s="";
  $s.= $this->MOD;
  $ret = "";
  for($c=0; $c < strlen($s); $c++)
   $ret.= ($s[$c]&4 ? 'r' : '-').($s[$c]&2 ? 'w' : '-').($s[$c]&1 ? 'x' : '-');
  return $ret;
 }

 function canRead($uid=null)
 {
  if(!$uid && $_SESSION['UNAME'] == 'root')
   return true;
  $usrid = $uid ? $uid : $_SESSION['UID'];
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$usrid."'");
  $db->Read();
  if($db->record['username'] == "root")
  {
   $db->Close();
   return true;
  }
  $db->Close();

  $mods = "".$this->MOD;
  if($usrid == $this->OWNER)
   return ($mods[0] & 4 ? true : false);
  else if(_userInGroupId($this->GROUP, $usrid))
   return ($mods[1] & 4 ? true : false);
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='$usrid'");
   if($db->Read() && ($db->record['username'] == "root"))
   {
    $db->Close();
    return true;
   }
   $db->Close();

   /* Share check */
   if($this->SHUSERS[$usrid])
	return ($this->SHUSERS[$usrid] & 4 ? true : false);
   if(count($this->SHGROUPS))
   {
	reset($this->SHGROUPS);
    while(list($k,$v) = each($this->SHGROUPS))
    {
	 if(_userInGroupId($k,$usrid))
	  return ($this->SHGROUPS[$k] & 4 ? true : false);
    }
   }

   return ($mods[2] & 4 ? true : false);
  }
 }

 function canWrite($uid=null)
 {
  if(!$uid && $_SESSION['UNAME'] == 'root')
   return true;
  $usrid = $uid ? $uid : $_SESSION['UID'];
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$usrid."'");
  $db->Read();
  if($db->record['username'] == "root")
  {
   $db->Close();
   return true;
  }
  $db->Close();
  $mods = strval($this->MOD);
  if($usrid == $this->OWNER)
   return (($mods[0] & 2) ? true : false);
  else if(_userInGroupId($this->GROUP, $usrid))
   return (($mods[1] & 2) ? true : false);
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$usrid."'");
   if($db->Read() && ($db->record['username'] == "root"))
   {
    $db->Close();
    return true;
   }
   $db->Close();

   /* Share check */
   if($this->SHUSERS[$usrid])
	return ($this->SHUSERS[$usrid] & 2 ? true : false);
   if(count($this->SHGROUPS))
   {
	reset($this->SHGROUPS);
    while(list($k,$v) = each($this->SHGROUPS))
    {
	 if(_userInGroupId($k,$usrid))
	  return ($this->SHGROUPS[$k] & 2 ? true : false);
    }
   }

   return ($mods[2] & 2 ? true : false);
  }
 }

 function canExecute($uid=null)
 {
  if(!$uid && $_SESSION['UNAME'] == 'root')
   return true;
  $usrid = $uid ? $uid : $_SESSION['UID'];
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$usrid."'");
  $db->Read();
  if($db->record['username'] == "root")
  {
   $db->Close();
   return true;
  }
  $db->Close();

  $mods = "".$this->MOD;
  if($usrid == $this->OWNER)
   return ($mods[0] & 1 ? true : false);
  else if(_userInGroupId($this->GROUP, $usrid))
   return ($mods[1] & 1 ? true : false);
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='$usrid'");
   if($db->Read() && ($db->record['username'] == "root"))
   {
    $db->Close();
    return true;
   }
   $db->Close();

   /* Share check */
   if($this->SHUSERS[$usrid])
	return ($this->SHUSERS[$usrid] & 1 ? true : false);
   if(count($this->SHGROUPS))
   {
	reset($this->SHGROUPS);
    while(list($k,$v) = each($this->SHGROUPS))
    {
	 if(_userInGroupId($k,$usrid))
	  return ($this->SHGROUPS[$k] & 1 ? true : false);
    }
   }

   return ($mods[2] & 1 ? true : false);
  }
 }

 function toArray($uid=null)
 {
  return array('uid'=>$this->OWNER,'gid'=>$this->GROUP,'mod'=>$this->MOD,'can_read'=>$this->canRead($uid),'can_write'=>$this->canWrite($uid),'can_execute'=>$this->canExecute($uid));
 }

 function userQuery($sessid=0, $uid=0, $table="")
 {
  $sessInfo = sessionInfo($sessid);
  if(!$uid && ($sessInfo['uname'] == 'root'))
   return "1";
  $uid = $uid ? $uid : $sessInfo['uid'];
  if(!$uid)
   return "_mod LIKE '%4' OR _mod LIKE '%6'";
  $q = "_mod LIKE '%4' OR _mod LIKE '%6' OR (uid='$uid' AND _mod>=400)";
  //--- groups ---//
  $ug = _userGroups($uid);
  $ug[] = array('id'=>$sessInfo['gid']);
  if(count($ug) > 0)
  {
   $q.= " OR ((";
   $q.= "gid='".$ug[0]['id']."'";
   for($c=1; $c < count($ug); $c++)
    $q.= " OR gid='".$ug[$c]['id']."'";
   $q.= ") AND (_mod LIKE '_4%' OR _mod LIKE '_6%'))";
  }
  //--- shared ---//
  if($table)
  {
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo($table);
   if($fields['shusrs'])
    $q.= " OR shusrs LIKE '%,".$uid."=4,%' OR shusrs LIKE '%,".$uid."=6,%'";
   if($fields['shgrps'])
   {
    if(count($ug) > 0)
    {
     for($c=0; $c < count($ug); $c++)
 	  $q.= " OR shgrps LIKE '%,".$ug[$c]['id']."=4,%' OR shgrps LIKE '%,".$ug[$c]['id']."=6,%'";
    }
   }
   $db->Close();
  }

  return $q;
 }
}
//----------------------------------------------------------------------------------------------------------------------//
