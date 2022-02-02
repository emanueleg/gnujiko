<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-10-2016
 #PACKAGE: database-lib
 #DESCRIPTION: Database access library for Gnujiko
 #VERSION: 2.12beta
 #CHANGELOG: 28-10-2016 : Bugfix line 76.
			 24-10-2016 : MySQLi integration.
			 07-03-2016 : Aggiunto preOutput su funzione backup per integrazione con gshell.
			 04-02-2016 : Aggiunta funzione saveDebugError.
			 11-04-2013 : Sistemato i permessi ai files.
			 25-03-2013 : Aggiornata fuzione backup.
			 05-03-2013 : Bug fix e aggiunto opzioni in funzione Backup.
			 19-02-2013 : Bug fix on function RunQueryFromFile under Windows.
			 17-02-2013 : Bug fix on function RunQueryFromFile
			 15-02-2013 : Backup function added.
			 22-01-2013 - Bug fix Purify()
			 11-01-2013 - Some bug fix.
			 26-02-2011 - Bug fix with special chars
 #TODO:
 
*/

define('AUTOSETSQLMODE', false);

class AlpaDatabase	//--- CLASS FOR MANAGE DATABASE ---//
{
 var $db;
 var $lastQuery;
 var $lastQueryResult;
 var $record;
 var $Error;

 function AlpaDatabase($host=null, $user=null, $pass=null, $database=null)
 {
  global $_BASE_PATH, $_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASSWORD, $_DATABASE_NAME;
  $db_host = $host ? $host : $_DATABASE_HOST;
  $db_user = $user ? $user : $_DATABASE_USER;
  $db_pass = $pass ? $pass : $_DATABASE_PASSWORD;
  $dbase = $database ? $database : $_DATABASE_NAME;

  $this->db = @mysqli_connect($db_host, $db_user, $db_pass, $dbase);
  if($this->db == false)
  {
   $this->Error = "Server connect failed";
   return false;
  }

  if(!AUTOSETSQLMODE)
  {
   $this->RunQuery("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
   define('AUTOSETSQLMODE', true);
  }

 }

 function Connected() { return isset($this->db);}
 
 function Close() 
 {
  if(isset($this->db) && $this->lastQueryResult) @mysqli_free_result($this->lastQueryResult);
  if(isset($this->db)) @mysqli_close($this->db); 
 }

 function RunQuery($query)
 {
  $this->lastQuery = $query;
  $this->lastQueryResult = @mysqli_query($this->db, $query);
  if(!$this->lastQueryResult)
  {
   $this->Error = $this->db ? mysqli_error($this->db) : "Unknown error. (unable to detect MySQLi error, invalid db link)";
   if($this->Error && isset($_COOKIE['GNUJIKO-ENABLE-DEBUG']) && $_COOKIE['GNUJIKO-ENABLE-DEBUG'])
	$this->saveDebugError();
  }
  return $this->lastQueryResult;
 }

 function GetInsertId() { return mysqli_insert_id($this->db); }

 function EscapeString($string="") { return mysqli_escape_string($this->db, $string); }

 function Read()
 {
  $this->record = array();
  $this->record = @mysqli_fetch_array($this->lastQueryResult);
  if($this->record)
  {
   while(list($k,$v) = each($this->record))
   {
    $this->record[$k] = $this->utf8_decode($v);
   }
  }
  return $this->record;
 }

 function RunQueryFromFile($filename)
 {
  $fp = fopen($filename, 'rt');
  if (!$fp) return false;
  $qry = "";
  while ($line = fgets($fp))
  {
   if (substr($line, 0, 2) == "--") continue;
   if ( ($line == "") or ($line == " ") ) continue;

   $qry.= $line;
   if(strrpos($qry, ';') > (strlen($qry)-3))
   {
	$qry = rtrim($qry,";");
	/* bug fix on windows machine */
	$db2 = new AlpaDatabase();
	$db2->RunQuery($qry);
	$db2->Close();
    $qry = "";
   }
  }
  fclose($fp);
 }

 function GetFields($table)
 {
  $this->RunQuery("SELECT * FROM ".$table." WHERE 1 LIMIT 1");
  $this->Read();
  $fields_count = @mysqli_num_fields($this->lastQueryResult);
  $ret = array();
  for ($c = 0; $c < $fields_count; $c++)
  {
   $finfo = @mysqli_fetch_field_direct($this->lastQueryResult,$c);
   $ret[$c][name] = $finfo->name;
   $ret[$c][type] = $finfo->type;
   $ret[$c][len] = $finfo->length;
   $ret[$c][flags] = $finfo->flags;
  }
  return $ret;
 }

 function FieldsInfo($table) 
 { /* E' simile a GetFields, solamente che viene ritornato un array dove le chiavi principali non sono numeriche, ma bensì contengono il nome del campo */
  $this->RunQuery("SELECT * FROM ".$table." WHERE 1 LIMIT 1");
  $this->Read();
  $fields_count = @mysqli_num_fields($this->lastQueryResult);
  $ret = array();
  for ($c = 0; $c < $fields_count; $c++)
  {
   $finfo = @mysqli_fetch_field_direct($this->lastQueryResult,$c);
   $name = $finfo->name;
   $ret[$name][type] = $finfo->type;
   $ret[$name][len] = $finfo->length;
   $ret[$name][flags] = $finfo->flags;
  }
  return $ret;
 }

 function Backup($tables="*", $options=null, $fileName="", $preOutput=null)
 {
  global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

  $out = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";
  if($tables == "*")
  {
   $tables = array();
   $this->RunQuery("SHOW TABLES");
   while($this->Read())
   {
	$tables[] = $this->record[0];
   }
  }
  else
   $tables = is_array($tables) ? $tables : explode(',',$tables);

  if($fileName)
  {
   // open file for write.//
   $fileH = @fopen($_BASE_PATH.$fileName,"w");
   if(!$fileH)
    return array("message"=>"Database backup error: Unable to open file ".$fileName." for write. Permission denied!","error"=>"FILE_PERMISSION_DENIED");
  }

  foreach($tables as $table)
  {
   if($preOutput)
   {
	gshPreOutput($preOutput['shellid'], "Backup of table ".$table."...", $preOutput['msgtype']);
   }
   $out.= "DROP TABLE IF EXISTS `".$table."`;\n\n";

   $this->RunQuery("SHOW CREATE TABLE ".$table);
   $this->Read();
   $out.= $this->record[1].";\n\n";

   if($options && $options[$table])
   {
	if($options[$table] == "CREATEONLY")
	 continue;
   }

   $f = $this->GetFields($table);
   $this->RunQuery("SELECT COUNT(*) FROM ".$table." WHERE 1");
   $this->Read();
   if($this->record[0])
   {
    $this->RunQuery("SELECT * FROM ".$table." WHERE 1");
    while($this->Read())
    {
	 $q = "INSERT INTO `".$table."` VALUES(";
     for ($x=0; $x < count($f); $x++)
      $q.= "'".str_replace("\\\"",'"',$this->Purify($this->record[$f[$x][name]]))."',";
     $q = rtrim($q,",");
     $q.= ");\n";
	 $out.= $q;
    }
   }
   
   if($fileH)
   {
    @fwrite($fileH,$out);
	$out = "";
   }

  }

  if($fileH)
  {
   @chmod($_BASE_PATH.$fileName,$_DEFAULT_FILE_PERMS);
   @fclose($fileH);
   return array('message'=>"done!");
  }
  else
   return $out;
 }

 function Export($filename, $arrTables)
 {
  $file = fopen($filename, 'wt');
  if ($file == null) return false;
  
  for ($c=0; $c < count($arrTables); $c++)
  {
   $f = $this->GetFields($arrTables[$c]);
   fwrite($file, "TRUNCATE TABLE `".$arrTables[$c]."`;\n");
   $this->RunQuery("SELECT * FROM ".$arrTables[$c]." WHERE 1");
   while ($this->Read())
   {
    $q = "INSERT INTO `".$arrTables[$c]."` VALUES(";
    for ($x=0; $x < count($f); $x++)
     $q.= "'".$this->record[$f[$x][name]]."',";
    $q = rtrim($q,",");
    $q.= ");\n";
    fwrite($file,$q);
   }
  }
  fclose($file);
  return true;
 }

 function Purify($str)
 {
  $ret = htmlentities($str,ENT_QUOTES,"UTF-8");
  $ret = html_entity_decode($ret,ENT_QUOTES);
  $ret = addslashes($ret);
  return $ret;
 }

 function utf8_decode($val)
 {
  $ret = htmlentities($val,ENT_QUOTES);
  $ret = html_entity_decode($ret,ENT_QUOTES,"UTF-8");
  return $ret;
 }

 function saveDebugError()
 {
  $qry = $this->lastQuery;
  $ctime = date('Y-m-d H:i:s');
  $dbgDB = new AlpaDatabase();
  $dbgDB->RunQuery("INSERT INTO system_debug(ctime,log_type,log_query,ret_message) VALUES('".$ctime."','DB','"
	.$dbgDB->Purify($qry)."','".$dbgDB->Purify($this->Error)."')");
  $dbgDB->Close();
 }
}


