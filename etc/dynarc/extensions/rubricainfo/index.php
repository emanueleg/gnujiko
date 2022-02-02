<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica extended informations.
 #VERSION: 2.15beta
 #CHANGELOG: 30-04-2016 : Aggiunta aliquota iva predefinita.
			 19-03-2015 : Aggiunto ore assistenza disponibili.
			 28-02-2015 : Aggiunto pa_code.
			 14-12-2014 : Bug fix su get extranotes.
			 14-10-2014 : Aggiunta email di default per le notifiche
			 29-09-2014 : Aggiunto punti fidelity card.
			 03-07-2014 : Aggiunto causali predefinite documenti.
			 26-05-2014 : Aggiunto banca d'appoggio
			 03-05-2014 : Bug fix on import.
			 13-12-2013 : Integrazione con gli agenti ed il login.
			 05-11-2013 : Aggiunto extra notes
			 06-09-2013 : Aggiunto Fidelity Card
			 14-03-2013 : Completate funzioni di sync import & export.
			 31-01-2013 : Aggiunto campo 'distance'
			 03-12-2012 : Completamento delle funzioni principali.
			 21-06-2012 : Pricelist added.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `iscompany` TINYINT( 1 ) NOT NULL , 
	ADD `taxcode` VARCHAR( 16 ) NOT NULL ,
	ADD `vatnumber` VARCHAR( 11 ) NOT NULL ,
	ADD `paymentmode` TINYINT( 1 ) NOT NULL ,
	ADD `pricelist_id` INT( 11 ) NOT NULL ,
	ADD `distance` FLOAT NOT NULL,
	ADD `fidelitycard` VARCHAR( 32 ) NOT NULL ,
	ADD `extranotes` TEXT NOT NULL ,
	ADD `agent_id` INT(11) NOT NULL,
	ADD `user_id` INT(11) NOT NULL , 
	ADD `login` VARCHAR(32) NOT NULL , 
	ADD `password` VARCHAR(32) NOT NULL, 
	ADD `ourbanksupport_id` INT(11) NOT NULL,
	ADD `gcdcausals` VARCHAR( 255 ) NOT NULL,
	ADD `fidelitycard_points` FLOAT NOT NULL,
	ADD `default_email` VARCHAR(255) NOT NULL,
	ADD `pa_code` VARCHAR(6) NOT NULL,
	ADD `assist_avail_hours` FLOAT NOT NULL,
	ADD `vat_id` INT(11) NOT NULL,
	ADD INDEX (`fidelitycard`,`agent_id`)");
 $db->Close();

 return array("message"=>"Rubrica main-info extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `iscompany`, 
	DROP `taxcode`, 
	DROP `vatnumber`, 
	DROP `paymentmode`, 
	DROP `pricelist_id`, 
	DROP `distance` ,
	DROP `fidelitycard` ,
	DROP `fidelitycard_points` ,
	DROP `extranotes`,
	DROP `agent_id`,
	DROP `user_id`,
	DROP `login`,
	DROP `password`,
	DROP `ourbanksupport_id`,
	DROP `gcdcausals`,
	DROP `default_email`,
	DROP `pa_code`,
	DROP `assist_avail_hours`,
	DROP `vat_id`");
 $db->Close();

 return array("message"=>"Rubrica main-info extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $userInfo = null;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'iscompany' : {$iscompany=$args[$c+1]; $c++;} break;
   case 'taxcode' : {$taxcode=$args[$c+1]; $c++;} break;
   case 'vatnumber' : {$vatnumber=$args[$c+1]; $c++;} break;
   case 'paymentmode' : {$paymentmode=$args[$c+1]; $c++;} break;
   case 'pricelist' : {$pricelist=$args[$c+1]; $c++;} break;
   case 'distance' : {$distance=$args[$c+1]; $c++;} break;
   case 'fidelitycard' : {$fidelityCard=$args[$c+1]; $c++;} break;
   case 'fidelitycardpoints' : {$fidelityCardPoints=$args[$c+1]; $c++;} break;
   case 'extranotes' : {$extraNotes=$args[$c+1]; $c++;} break;
   case 'agent' : case 'agentid' : {$agentId=$args[$c+1]; $c++;} break;
   case 'userid' : {$userId=$args[$c+1]; $c++;} break;
   case 'login' : {$login=$args[$c+1]; $c++;} break;
   case 'passwd' : case 'password' : {$password=$args[$c+1]; $c++;} break;
   case 'ourbank' : case 'ourbanksupport' : {$ourBankSupportId=$args[$c+1]; $c++;} break;
   case 'gcdcausals' : {$gcdCausals=$args[$c+1]; $c++;} break;
   case 'defaultemail' : case 'defemail' : {$defaultEmail=$args[$c+1]; $c++;} break;
   case 'pacode' : {$paCode=$args[$c+1]; $c++;} break;
   case 'tothours' : case 'availablehours' : case 'availhours' : {$assistAvailHours=$args[$c+1]; $c++;} break;
   case 'vatid' : {$vatId=$args[$c+1]; $c++;} break;
  }

 if($userId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_users WHERE id='".$userId."'");
  if($db->Read())
  {
   $userInfo = $db->record;
   if(!$login)	$login = $db->record['username'];
  }
  $db->Close();
 }

 $db = new AlpaDatabase();

 $q = "";
 if(isset($iscompany)){$itemInfo['iscompany'] = $iscompany;
  $q.= ",iscompany='$iscompany'";}
 if(isset($taxcode)){$itemInfo['taxcode'] = $taxcode;
  $q.= ",taxcode='$taxcode'";}
 if(isset($vatnumber)){$itemInfo['vatnumber'] = $vatnumber;
  $q.= ",vatnumber='$vatnumber'";}
 if(isset($paymentmode)){$itemInfo['paymentmode'] = $paymentmode;
  $q.= ",paymentmode='$paymentmode'";}
 if(isset($pricelist)){$itemInfo['pricelist_id'] = $pricelist;
  $q.= ",pricelist_id='$pricelist'";}
 if(isset($distance)){$itemInfo['distance'] = $distance;
  $q.= ",distance='".$distance."'";}
 if(isset($fidelityCard)){$itemInfo['fidelitycard'] = $fidelityCard;
  $q.= ",fidelitycard='".$fidelityCard."'";}
 if(isset($fidelityCardPoints)){$itemInfo['fidelitycard_points'] = $fidelityCardPoints;
  $q.= ",fidelitycard_points='".$fidelityCardPoints."'";}
 if(isset($extraNotes)){$itemInfo['extranotes'] = $extraNotes;
  $q.= ",extranotes='".$db->Purify($extraNotes)."'";}
 if(isset($agentId)){$itemInfo['agent_id'] = $agentId;
  $q.= ",agent_id='".$agentId."'";}
 if(isset($userId)){$itemInfo['user_id'] = $userId;
  $q.= ",user_id='".$userId."'";}
 if(isset($login)){$itemInfo['login'] = $login;
  $q.= ",login='".$login."'";}
 if(isset($password)){$itemInfo['password'] = $password;
  $q.= ",password='".$password."'";}
 if(isset($ourBankSupportId)){$itemInfo['ourbanksupport_id'] = $ourBankSupportId;
  $q.= ",ourbanksupport_id='".$ourBankSupportId."'";}
 if(isset($gcdCausals))
  $q.= ",gcdcausals='".$gcdCausals."'";
 if(isset($defaultEmail)){$itemInfo['default_email'] = $defaultEmail;
  $q.= ",default_email='".$defaultEmail."'";}
 if(isset($paCode)){$itemInfo['pacode'] = $paCode;
  $q.= ",pa_code='".$paCode."'";}
 if(isset($assistAvailHours)){$itemInfo['assist_avail_hours'] = $assistAvailHours;
  $q.= ",assist_avail_hours='".$assistAvailHours."'";}
 if(isset($vatId)){$itemInfo['vat_id'] = $vatId;
  $q.= ",vat_id='".$vatId."'";}

 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".substr($q,1)." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 if($userInfo && $login && $password)
 {
  $db = new AlpaDatabase();
  $encpasswd = md5($password.$userInfo['regtime']);
  $db->RunQuery("UPDATE gnujiko_users SET username='".$login."',password='".$encpasswd."',rubrica_id='".$itemInfo['id']."' WHERE id='".$userId."'");
  $db->Close();
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'iscompany' : $iscompany=true; break;
   case 'taxcode' : $taxcode=true; break;
   case 'vatnumber' : $vatnumber=true; break;
   case 'paymentmode' : $paymentmode=true; break;
   case 'pricelist' : $pricelist=true; break;
   case 'distance' : $distance=true; break;
   case 'fidelitycard' : $fidelityCard=true; break;
   case 'fidelitycardpoints' : $fidelityCardPoints=true; break;
   case 'extranotes' : $extraNotes=true; break;
   case 'agent' : $agent=true; break;
   case 'userid' : $userId=true; break;
   case 'login' : $login=true; break;
   case 'passwd' : case 'password' : $passwd=true; break;
   case 'ourbank' : case 'ourbanksupport' : $ourBankSupport=true; break;
   case 'gcdcausals' : $gcdCausals=true; break;
   case 'defaultemail' : case 'defemail' : $defaultEmail=true; break;
   case 'pacode' : $paCode=true; break;
   case 'tothours' : case 'availablehours' : case 'availhours' : $assistAvailHours=true; break;
   case 'vatid' : $vatId=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $itemInfo['iscompany'] = $db->record['iscompany'];
 $itemInfo['taxcode'] = $db->record['taxcode'];
 $itemInfo['vatnumber'] = $db->record['vatnumber'];
 $itemInfo['paymentmode'] = $db->record['paymentmode'];
 $itemInfo['pricelist_id'] = $db->record['pricelist_id'];
 $itemInfo['distance'] = $db->record['distance'];
 $itemInfo['fidelitycard'] = $db->record['fidelitycard'];
 $itemInfo['fidelitycard_points'] = $db->record['fidelitycard_points'];
 $itemInfo['extranotes'] = $db->record['extranotes'];
 $itemInfo['pacode'] = $db->record['pa_code'];
 $itemInfo['assist_avail_hours'] = $db->record['assist_avail_hours'];
 $itemInfo['vat_id'] = $db->record['vat_id'];

 $itemInfo['user_id'] = $db->record['user_id'];
 $itemInfo['login'] = $db->record['login'];
 $itemInfo['password'] = $db->record['password'];
 $itemInfo['ourbanksupport_id'] = $db->record['ourbanksupport_id'];
 $itemInfo['default_email'] = $db->record['default_email'];

 if($gcdCausals || $all)
 {
  $gcdcausals = $db->record['gcdcausals'];
  $itemInfo['gcdcausals'] = array();
  if($gcdcausals)
  {
   $x = explode(";",$gcdcausals);
   for($c=0; $c < count($x); $c++)
   {
	if(!$x[$c]) continue;
	$xx = explode("=",$x[$c]);
	$docTag = $xx[0];
	$typeId = $xx[1];
	if($docTag)
	 $itemInfo['gcdcausals'][$docTag] = $typeId;
   }
  }
 }

 if($agent || $all)
 {
  $itemInfo['agent_id'] = $db->record['agent_id'];
  if($db->record['agent_id'])
  {
   // get agent name //
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$db->record['agent_id']."'");
   $db2->Read();
   $itemInfo['agent_name'] = $db2->record['name'];
   $db2->Close();
  }
 }

 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();

 $cloneInfo['taxcode'] = $db->record['taxcode'];
 $cloneInfo['vatnumber'] = $db->record['vatnumber'];
 $cloneInfo['paymentmode'] = $db->record['paymentmode'];
 $cloneInfo['iscompany'] = $db->record['iscompany'];
 $cloneInfo['pricelist_id'] = $db->record['pricelist_id'];
 $cloneInfo['distance'] = $db->record['distance'];
 $cloneInfo['fidelitycard'] = $db->record['fidelitycard'];
 $cloneInfo['fidelitycard_points'] = $db->record['fidelitycard_points'];
 $cloneInfo['extranotes'] = $db->record['extranotes'];
 $cloneInfo['agent_id'] = $db->record['agent_id'];
 $cloneInfo['user_id'] = $db->record['user_id'];
 $cloneInfo['login'] = $db->record['login'];
 $cloneInfo['password'] = $db->record['password'];
 $cloneInfo['ourbanksupport_id'] = $db->record['ourbanksupport_id'];
 $cloneInfo['default_email'] = $db->record['default_email'];
 $cloneInfo['pacode'] = $db->record['pa_code'];
 $cloneInfo['assist_avail_hours'] = $db->record['assist_avail_hours'];
 $cloneInfo['vat_id'] = $db->record['vat_id'];

 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET taxcode='".$db->record['taxcode']."',vatnumber='"
	.$db->record['vatnumber']."',paymentmode='".$db->record['paymentmode']."',iscompany='".$db->record['iscompany']."',pricelist_id='"
	.$db->record['pricelist_id']."',distance='".$db->record['distance']."',fidelitycard='".$db->record['fidelitycard']."',fidelitycard_points='"
	.$db->record['fidelitycard_points']."',extranotes='".$db2->Purify($db->record['extranotes'])."',agent_id='".$db->record['agent_id']."',user_id='"
	.$db->record['user_id']."',login='".$db->record['login']."',password='".$db->record['password']."',ourbanksupport_id='"
	.$db->record['ourbanksupport_id']."',default_email='".$db->record['default_email']."',pa_code='".$db->record['pa_code']."',assist_avail_hours='"
	.$db->record['assist_avail_hours']."',vat_id='".$cloneInfo['vat_id']."' WHERE id='".$cloneInfo['id']."'");
 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<rubricainfo taxcode='".$db->record['taxcode']."' vatnumber='".$db->record['vatnumber']."' paymentmode='"
	.$db->record['paymentmode']."' iscompany='".$db->record['iscompany']."' pricelist_id='".$db->record['pricelist_id']."' distance='"
	.$db->record['distance']."' fidelitycard='".$db->record['fidelitycard']."' fidelitycardpoints='"
	.$db->record['fidelitycard_points']."' agentid='".$db->record['agent_id']."' userid='".$db->record['user_id']."' login='"
	.$db->record['login']."' password='".$db->record['password']."' ourbanksupportid='".$db->record['ourbanksupport_id']."' defaultemail='"
	.$db->record['default_email']."' pacode='".$db->record['pa_code']."' assistavailhours='".$db->record['assist_avail_hours']."' vatid='"
	.$db->record['vat_id']."'/>";
 $db->Close();
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return;

 $q = "";
 if($iscompany = $node->getString('iscompany'))  					$q.= ",iscompany='$iscompany'";
 if($taxcode = $node->getString('taxcode'))		 					$q.= ",taxcode='$taxcode'";
 if($vatnumber = $node->getString('vatnumber'))  					$q.= ",vatnumber='$vatnumber'";
 if($paymentmode = $node->getString('paymentmode'))  				$q.= ",paymentmode='$paymentmode'";
 if($pricelist = $node->getString('pricelist_id'))	 				$q.= ",pricelist_id='$pricelist'";
 if($distance = $node->getString('distance'))		  				$q.= ",distance='".$distance."'";
 if($fidelityCard = $node->getString('fidelitycard')) 				$q.= ",fidelitycard='".$fidelityCard."'";
 if($fidelityCardPoints = $node->getString('fidelitycardpoints')) 	$q.= ",fidelitycard_points='".$fidelityCardPoints."'";
 if($agentId = $node->getString('agentid')) 						$q.= ",agent_id='".$agentId."'";
 if($userId = $node->getString('userid')) 							$q.= ",user_id='".$userId."'";
 if($login = $node->getString('login'))								$q.= ",login='".$login."'";
 if($password = $node->getString('password'))						$q.= ",password='".$password."'";
 if($ourBankSupportId = $node->getString('ourbanksupportid'))		$q.= ",ourbanksupport_id='".$ourBankSupportId."'";
 if($defaultEmail = $node->getString('defaultemail'))				$q.= ",default_email='".$defaultEmail."'";
 if($paCode = $node->getString('pacode'))							$q.= ",pa_code='".$paCode."'";
 if($vatId = $node->getString('vatid'))								$q.= ",vat_id='".$vatId."'";
 if($assistAvailHours = $node->getString('assistavailhours'))		$q.= ",assist_avail_hours='".$assistAvailHours."'";

 if($q != "")
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT user_id FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($db->record['user_id'])
 {
  // disassocia l'utente
  $db->RunQuery("UPDATE gnujiko_users SET rubrica_id='0' WHERE id='".$db->record['user_id']."'");
 }

 // disassocia gli agenti
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET agent_id='0' WHERE agent_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 
 if($isCategory)
  return;

 $xml = "";
 $attachments = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<rubricainfo taxcode='".$db->record['taxcode']."' vatnumber='".$db->record['vatnumber']."' paymentmode='"
	.$db->record['paymentmode']."' iscompany='".$db->record['iscompany']."' pricelist_id='".$db->record['pricelist_id']."' distance='"
	.$db->record['distance']."' fidelitycard='".$db->record['fidelitycard']."' fidelitycardpoints='"
	.$db->record['fidelitycard_points']."' agentid='".$db->record['agent_id']."' userid='".$db->record['user_id']."' login='"
	.$db->record['login']."' password='".$db->record['password']."' ourbanksupportid='".$db->record['ourbanksupport_id']."' defaultemail='"
	.$db->record['default_email']."' pacode='".$db->record['pa_code']."' assistavailhours='".$db->record['assist_avail_hours']."' vatid='"
	.$db->record['vat_id']."'/>";
 $db->Close();


 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USERS_HOMES;
 if($isCategory)
  return;

 $node = $xmlNode->GetElementsByTagName('rubricainfo');
 $node = $node[0];

 $q = "";
 if($iscompany = $node->getString('iscompany'))						  $q.= ",iscompany='$iscompany'";
 if($taxcode = $node->getString('taxcode'))							  $q.= ",taxcode='$taxcode'";
 if($vatnumber = $node->getString('vatnumber'))						  $q.= ",vatnumber='$vatnumber'";
 if($paymentmode = $node->getString('paymentmode'))					  $q.= ",paymentmode='$paymentmode'";
 if($pricelist = $node->getString('pricelist_id'))					  $q.= ",pricelist_id='$pricelist'";
 if($distance = $node->getString('distance'))						  $q.= ",distance='".$distance."'";
 if($fidelityCard = $node->getString('fidelitycard'))				  $q.= ",fidelitycard='".$fidelityCard."'";
 if($fidelityCardPoints = $node->getString('fidelitycardpoints'))	  $q.= ",fidelitycard_points='".$fidelityCardPoints."'";
 if($agentId = $node->getString('agentid'))							  $q.= ",agent_id='".$agentId."'";
 if($userId = $node->getString('userid'))							  $q.= ",user_id='".$userId."'";
 if($login = $node->getString('login'))								  $q.= ",login='".$login."'";
 if($password = $node->getString('password'))						  $q.= ",password='".$password."'";
 if($ourBankSupportId = $node->getString('ourbanksupportid'))		  $q.= ",ourbanksupport_id='".$ourBankSupportId."'";
 if($defaultEmail = $node->getString('defaultemail'))				  $q.= ",default_email='".$defaultEmail."'";
 if($paCode = $node->getString('pacode'))							  $q.= ",pa_code='".$paCode."'";
 if($vatId = $node->getString('vatid'))								  $q.= ",vat_id='".$vatId."'";
 if($assistAvailHours = $node->getString('assistavailhours'))		  $q.= ",assist_avail_hours='".$assistAvailHours."'";

 if($q != "")
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

