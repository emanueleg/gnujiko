<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-11-2014
 #PACKAGE: barcode
 #DESCRIPTION: Barcode functions
 #VERSION: 2.1beta
 #CHANGELOG: 19-11-2014 : Ricerca per barcode nelle varianti. 
 #DEPENDS: 
 
*/

global $_BASE_PATH;

function shell_barcode($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'add-schema' : case 'new-schema' : return barcode_newSchema($args, $sessid, $shellid); break;
  case 'get-schemas' : case 'schema-list' : return barcode_getSchemas($args, $sessid, $shellid); break;


  case 'search' : return barcode_search($args, $sessid, $shellid); break;
  case 'scan' : return barcode_scan($args, $sessid, $shellid); break;

  default : return barcode_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function barcode_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function barcode_newSchema($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-xml' : {$xml=$args[$c+1]; $c++;} break;
  }

 if(!$xml) return array("message"=>"You must specify the xml.", "error"=>"INVALID_XML");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM aboutconfig_appconfig WHERE app_name='barcode' AND app_section='schemas'");
 if($db->Read())
  $xml = $db->record['xml_config'].$xml;
 $db->Close();

 return GShell("aboutconfig set-config -app 'barcode' -sec 'schemas' -xml-config `".$xml."`",$sessid,$shellid);
}
//-------------------------------------------------------------------------------------------------------------------//
function barcode_getSchemas($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'list'=>array());

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $ret = GShell("aboutconfig get -app barcode -sec schemas",$sessid,$shellid);
 if($ret['error']) return array("message"=>"No schema found.", "outarr"=>$outArr);
 $schemas = $ret['outarr']['config'];

 if($schemas['schema'])
  $outArr['list'][] = $schemas['schema'];
 else if(!count($schemas) || is_array($schemas['xml']))
  return array("message"=>"No schema found.", "outarr"=>$outArr);
 else
 {
  $out.= "Trovati ".count($schemas)." elementi.";
  for($c=0; $c < count($schemas); $c++)
   $outArr['list'][] = $schemas[$c];
 }
 $outArr['count'] = count($outArr['list']);

 $out.= $outArr['count']." schema found.\n";
 if($verbose)
 {
  for($c=0; $c < count($outArr['list']); $c++)
  {
   $schema = $outArr['list'][$c];
   $out.= "Schema #".($c+1).": ".$schema['name']." - LEN: ".$schema['length']." - CHUNKS: ".count($schema['chunks'])."\n";
  }
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function barcode_search($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 include_once($_BASE_PATH."include/userfunc.php");

 $out = "";
 $outArr = array('packsearch'=>array(), 'othersearch'=>array('matches'=>array(), 'similar'=>array()), 'aboutbarcode'=>array());

 $qry = "";
 $limit = 25;
 $orderBy = "name ASC";

 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;	// Filter by archive type
   case '-ats' : {$_ATS=$args[$c+1]; $c++;} break;	// Filter by archive types separated by comma ','
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;	// Filter by archive prefix
   case '-aps' : {$_APS=$args[$c+1]; $c++;} break;	// Filter by archive prefixes separated by comma ','

   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '--order-by' : case '-orderby' : {$orderBy=$args[$c+1]; $c++;} break;

   default : {if(!$qry) $qry=$args[$c];} break;
  }

 if(!$qry)
  return array("message"=>"Query is empty.", "error"=>"QUERY_IS_EMPTY");

 if($_ATS)
 {
  $tmp = explode(",", $_ATS);
  $db = new AlpaDatabase();
  for($c=0; $c < count($tmp); $c++)
  {
   $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$tmp[$c]."' AND trash='0'");
   while($db->Read()){$_ARCHIVES[] = $db->record['tb_prefix'];}
  }
  $db->Close();
 }
 else if($_AT)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$_AT."' AND trash='0'");
  while($db->Read()){$_ARCHIVES[] = $db->record['tb_prefix'];}
  $db->Close();
 }
 else if($_APS)
  $_ARCHIVES = explode(",",$_APS);
 else if($_AP)
  $_ARCHIVES[] = $_AP;

 //--------------------------------------------------------------------------------------------------------//

 /* PACK SEARCH */
 $ret = GShell("pack search -barcode `".$qry."` -limit '".$limit."' --no-similar",$sessid,$shellid);
 if(!$ret['error'] && is_array($ret['outarr']))
 {
  if($ret['outarr']['result']) $outArr['packsearch']['result'] = $ret['outarr']['result'];
 }

 /* SEARCH INTO ARCHIVES */
 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name,code_str,barcode FROM dynarc_".$_ARCHIVES[$c]."_items WHERE barcode='".$db->Purify($qry)."' AND trash='0' LIMIT 1");
  if($db->Read())
   $outArr['othersearch']['matches'][] = array('ap'=>$_ARCHIVES[$c], 'id'=>$db->record['id'], 'code_str'=>$db->record['code_str'], 
	'name'=>$db->record['name'], 'barcode'=>$db->record['barcode']);
  else
  {
   // search into variants //
   $query = "SELECT ext.item_id,ext.code,ext.barcode,itm.id,itm.name FROM dynarc_"
	.$_ARCHIVES[$c]."_varcodes AS ext INNER JOIN dynarc_".$_ARCHIVES[$c]."_items AS itm ON ext.item_id=itm.id WHERE ext.barcode='"
	.$db->Purify($qry)."' ORDER BY ext.id ASC LIMIT 1";
   $db->RunQuery($query);
   if($db->Read())
   {
	$a = array('ap'=>$_ARCHIVES[$c], 'id'=>$db->record['item_id'], 'code_str'=>$db->record['code'], 
	'name'=>$db->record['name'], 'barcode'=>$db->record['barcode']);
	if(!$a['code_str'])
	{
	 $db->RunQuery("SELECT code_str FROM dynarc_".$a['ap']."_items WHERE id='".$a['id']."'");
	 $db->Read();
	 $a['code_str'] = $db->record['code_str'];
	}
	$outArr['othersearch']['matches'][] = $a;
   }
   else
   {
    // search for similar
	$db->Close();
	$db = new AlpaDatabase();
    $db->RunQuery("SELECT id,name,code_str,barcode FROM dynarc_".$_ARCHIVES[$c]."_items WHERE barcode LIKE '".$db->Purify($qry)."%' AND trash='0' ORDER BY ".$orderBy." LIMIT ".$limit);
    while($db->Read())
    {
	 $outArr['othersearch']['similar'][] = array('ap'=>$_ARCHIVES[$c], 'id'=>$db->record['id'], 'code_str'=>$db->record['code_str'], 
		'name'=>$db->record['name'], 'barcode'=>$db->record['barcode']);
    }
   }
  }
  $db->Close();
 }

 if(!is_array($outArr['packsearch']['result']) && !count($outArr['othersearch']['matches']))
 {
  /* Get info about barcode */
  $ret = GShell("barcode scan -barcode `".$qry."`",$sessid,$shellid);
  if(!$ret['error'] && $ret['outarr']['results'])
  {
   $outArr['aboutbarcode']['results'] = $ret['outarr']['results'];
  }
 }
 
 /* --- FINISH --- */

 if($verbose)
 {
  if($outArr['packsearch']['result'])
  {
   $res = $outArr['packsearch']['result'];
   $out.= "An article with barcode '".$qry."' was found into pack #".$res['pack_id']."\n";
   $out.= "#".$res['id']." - ".$res['refname']." [".$res['barcode']."]\n";
  }
  else if(count($outArr['othersearch']['matches']))
  {
   $list = $outArr['othersearch']['matches'];
   $out.= count($list)." results found for barcode '".$qry."'\n";
   for($c=0; $c < count($list); $c++)
	$out.= "#".$list[$c]['id']." - ".$list[$c]['name']." [".$list[$c]['barcode']."]\n";
  }
  else if(count($outArr['othersearch']['similar']))
  {
   $list = $outArr['othersearch']['similar'];
   $out.= count($list)." results found with barcode like '".$qry."'\n";
   for($c=0; $c < count($list); $c++)
	$out.= "#".$list[$c]['id']." - ".$list[$c]['name']." [".$list[$c]['barcode']."]\n";
  }
  else
   $out.= "No results found for barcode '".$qry."'\n";
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function barcode_scan($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'results'=>array());

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-barcode' : {$barcodeQry=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose = true; break;
   default : {if(!$barcodeQry) $barcodeQry=$args[$c]; } break;
  }

 if(!$barcodeQry) return array("message"=>"You must specify the barcode.", "error"=>"INVALID_BARCODE");

 // get schemas
 $schemas = array();
 $ret = GShell("barcode get-schemas",$sessid,$shellid);
 if(!$ret['error']) $schemas = $ret['outarr']['list'];
 
 for($c=0; $c < count($schemas); $c++)
 {
  $schema = $schemas[$c];

  // checks
  if($schema['minlen'] && (strlen($barcodeQry) < $schema['minlen']))		continue;
  if($schema['maxlen'] && (strlen($barcodeQry) > $schema['maxlen']))		continue;
  if($schema['fixedlen'] && (strlen($barcodeQry) != $schema['fixedlen']))	continue;
  
  // chunkerize
  $chunks = array();  $idx = 0;
  for($i=0; $i < count($schema['chunks']); $i++)
  {
   $chunks[] = substr($barcodeQry, $idx, $schema['chunks'][$i]['len']);
   $idx+=$schema['chunks'][$i]['len'];
  }

  // get archive prefixes
  $_APS = array();
  if($schema['at'])
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$schema['at']."' AND trash='0'");
   while($db->Read())
    $_APS[] = $db->record['tb_prefix'];
   $db->Close();
  }
  else if($schema['ap'])
   $_APS[] = $schema['ap'];

  for($i=0; $i < count($schema['chunks']); $i++)
  {
   $chunk = $chunks[$i];
   $result = array();
   switch(strtoupper($schema['chunks'][$i]['ref']))
   {
	case 'ITEM_MANCODE' : {
		 /* Fa una ricerca nella lista dei codici articoli produttori all'interno degli archivi specificati */
		 for($j=0; $j < count($_APS); $j++)
		 {
		  $db = new AlpaDatabase();
		  $db->RunQuery("SELECT id,name,code_str FROM dynarc_".$_APS[$j]."_items WHERE manufacturer_code='".$chunk."'");
		  if($db->Read())
		  {
		   $result['ap'] = $_APS[$j];
		   $result['id'] = $db->record['id'];
		   $result['name'] = $db->record['name'];
		   $result['code_str'] = $db->record['code_str'];
		   break;
		  }
		  else
		  {
		   $db->RunQuery("SELECT item_id FROM dynarc_".$_APS[$j]."_mancodes WHERE code='".$chunk."'");
		   if($db->Read())
		   {
			$db->RunQuery("SELECT id,name,code_str FROM dynarc_".$_APS[$j]."_items WHERE id='".$db->record['item_id']."'");
			$db->Read();
		    $result['ap'] = $_APS[$j];
		    $result['id'] = $db->record['id'];
		    $result['name'] = $db->record['name'];
		    $result['code_str'] = $db->record['code_str'];
			break;
		   }
		  }
		  $db->Close();
		 }
		} break;

	case 'ITEM_CODE' : {
		 /* Fa una ricerca nella lista dei codici articoli interni all'interno degli archivi specificati */
		 $db = new AlpaDatabase();
		 for($j=0; $j < count($_APS); $j++)
		 {
		  $db->RunQuery("SELECT id,name,code_str FROM dynarc_".$_APS[$j]."_items WHERE manufacturer_code='".$chunk."'");
		  if($db->Read())
		  {
		   $result['ap'] = $_APS[$j];
		   $result['id'] = $db->record['id'];
		   $result['name'] = $db->record['name'];
		   $result['code_str'] = $db->record['code_str'];
		   break;
		  }
		 }
		 $db->Close();
		} break;

	case 'ITEM_SEQCODE' : {
		 /* Codice sequenziale */

		} break;

    case 'CAT_CODE' : {
		 /* Ricerca tramite codice categoria */

		} break;

	case 'PACK_CODE' : {
		 /* Ricerca codice nei pacchi */

		} break;

   } // eof - switch chunk ref

   if($result['id'] || $result['cat_id'])
	$outArr['results'][] = $result;

  } // eof for - chunks

 } // eof for - schemas

 $outArr['count'] = count($outArr['results']);

 if(!count($outArr['results']))
  $out.= "no results found.";

 if($verbose)
 {
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   if($res['id'])
	$out.= "#".$res['id']." ".$res['code_str']." - ".$res['name']."\n";
   else if($res['cat_id'])
	$out.= "CAT: #".$res['cat_id']." - ".$res['cat_name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


