<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-01-2015
 #PACKAGE: idoc-config
 #DESCRIPTION: Execute iDoc documents.
 #VERSION: 2.4beta
 #CHANGELOG: 27-01-2015 : Bug fix accenti e virgolette.
			 02-12-2014 : Bug fix su salvataggio.
			 13-06-2014 : Bug fix absolute_url
			 30-03-2013 : Aggiunta funzione idocOnLoad che scatta non appena caricato tutti i parametri.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";
include_once($_BASE_PATH."init/init1.php");
include_once($_BASE_PATH."include/session.php");
define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

$_AP = $_REQUEST['idocap'] ? $_REQUEST['idocap'] : "idoc";
$id = $_REQUEST['idocid'];
$alias = $_REQUEST['alias'];
$ret = GShell("dynarc item-info".($_REQUEST['idocaid'] ? " -aid `".$_REQUEST['idocaid']."`" : " -ap `".$_AP."`")." ".($alias ? "-alias `".$alias."`" : "-id `".$id."`")." -extget javascript,css -get params");
$docInfo = $ret['outarr'];
$id = $_REQUEST['idocid'] = $docInfo['id'];

 // arrayze params //
 $params = array();
 if($docInfo['params'])
 {
  $x = explode("&",$docInfo['params']);
  for($c=0; $c < count($x); $c++)
  {
   $xx = explode("=",$x[$c]);
   if($xx[0])
	$params[$xx[0]] = $xx[1];
  }
 }

 // arrayze http request //
 $httpRequest = "var HTTP_REQUEST = new Array();\n";
 while(list($k,$v) = each($_REQUEST))
 {
  $httpRequest.= "HTTP_REQUEST['".$k."'] = \"".$v."\";\n";
 }

$_CONTENTS = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$docInfo['desc']);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"/><title><?php echo $docInfo['name']; ?></title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<style type='text/css'>
body {
	border: 1px solid #dadada;
	background: #ffffff;
}
<?php echo $docInfo['css'][0]['content']; ?>
</style>
<?php
/* Load CSS */
for($c=0; $c < count($docInfo['css']); $c++)
{
 if($docInfo['css'][$c]['src'])
  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL.$docInfo['css'][$c]['src']."' type='text/css'/>";
}
?>
<table width="<?php echo $params['width'] ? $params['width'] : 640; ?>" height="<?php echo $params['height'] ? $params['height'] : 480; ?>" align='center' valign='middle'>
<tr><td valign='top'><?php echo $_CONTENTS; ?></td></tr>
</table>

<?php
for($c=0; $c < count($docInfo['javascript']); $c++)
{
 if($docInfo['javascript'][$c]['src'])
  echo "<script language='JavaScript' src='".$_ABSOLUTE_URL.$docInfo['javascript'][$c]['src']."' type='text/javascript'></script>";
}
?>

<script>
<?php echo $httpRequest; ?>

function idocAutoSave(callback)
{
 if(!HTTP_REQUEST['ap'] || !HTTP_REQUEST['id'])
  return;

 var xml = idocGetXMLProp();
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(typeof(idocOnSave) == "function")
	  idocOnSave(o,a,callback); 
	 else if(callback)
	  callback(o,a);
	}
 sh.sendCommand("dynarc edit-item -ap `"+HTTP_REQUEST['ap']+"` -id `"+HTTP_REQUEST['id']+"` -extset `idoc.<?php echo $_REQUEST['idocaid'] ? 'aid='.$_REQUEST['idocaid'] : 'ap='.$_AP; ?>,id='<?php echo $_REQUEST['idocid']; ?>',xmlprop='''<![CDATA["+xml+"]]>'''`");
}

function idocAutoLoad()
{
 if(!HTTP_REQUEST['ap'] || !HTTP_REQUEST['id'])
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,params){
	 if(!params) return;
	 for(var c=0; c < params.length; c++)
	 {
	  if(params[c]['type'] == "radio")
	  {
	   var list = document.getElementsByName(params[c]['id']);
	   for(var i=0; i < list.length; i++)
	   {
		if(list[i].value == params[c]['value'])
		 list[i].checked = true;
	   }
	   continue;
	  }

	  var el = document.getElementById(params[c]['id']);
	  if(el)
	  {
	   switch(params[c]['type'])
	   {
	    case 'text' : case 'select' : el.value = params[c]['value']; break;
		case 'textarea' : el.value = params[c]['value']; break;
		case 'checkbox' : el.checked = params[c]['value']==1 ? true : false; break;
	   }
	  }
	 }
	 if(typeof(idocOnLoad) == "function")
	  idocOnLoad(); 
	}
 sh.sendCommand("dynarc exec-func ext:idoc.itmpropget -params `ap="+HTTP_REQUEST['ap']+"&id="+HTTP_REQUEST['id']+"<?php echo $_REQUEST['idocaid'] ? '&idocaid='.$_REQUEST['idocaid'] : 'idocap='.$_AP; ?>&idocid=<?php echo $_REQUEST['idocid']; ?>`");
}

function idocGetXMLProp()
{
 var xmlRet = "<xml>";
 /* Save INPUT */
 var list = document.getElementsByTagName('INPUT');
 for(var c=0; c < list.length; c++)
 {
  var el = list[c];
  if((el.type.toLowerCase() == "radio") && el.name)
  {
   if(el.checked)
   {
    xmlRet+="<param type='radio' id='"+el.name+"' value=\""+el.value.replace("&","&amp;")+"\"/"+">";
   }
   continue;
  }

  if(!el.id) continue;
  switch(el.type.toLowerCase())
  {
   case 'text' : xmlRet+="<param type='text' id='"+el.id+"' value=\""+el.value.E_QUOT().replace("&","&amp;")+"\"/"+">"; break;
   case 'checkbox' : xmlRet+="<param type='checkbox' id='"+el.id+"' value='"+(el.checked ? "1" : "0")+"'/"+">"; break;
  }
 }
 /* Save SELECT */
 var list = document.getElementsByTagName('SELECT');
 for(var c=0; c < list.length; c++)
 {
  var el = list[c];
  if(!el.id) continue;
  xmlRet+="<param type='select' id='"+el.id+"' value=\""+el.value+"\"/"+">";
 }
 /* Save TEXTAREA */
 var list = document.getElementsByTagName('TEXTAREA');
 for(var c=0; c < list.length; c++)
 {
  var el = list[c];
  if(!el.id) continue;
  xmlRet+="<param type='textarea' id='"+el.id+"' value=\""+el.value.E_QUOT().replace("&","&amp;")+"\"/"+">";
 }
 xmlRet+= "</xml>";
 return xmlRet;
}

<?php echo $docInfo['javascript'][0]['content']; ?>
</script>
</body></html>


