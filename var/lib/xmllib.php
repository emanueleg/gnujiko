<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-08-2011
 #PACKAGE: xml-lib
 #DESCRIPTION: XML library
 #VERSION: 2.0beta
 #CHANGELOG: 22-08-2011 - Bug fix in function array_to_xml
			 26-02-2011 - Bug fix with special chars
			 27-05-2011 - Ho modificato la funzione xml_purify levando via il \n dagli indici perchè dava fastidio sul parsering dei contenuti. 
						  Convertiva in <br/> tutti i ritorni a capo, quindi produceva degli effetti indesiderati se all'interno del contenuto c'erano ad esempio dei tag <style></style>
 #TODO:
 
*/

// XMLTHING CLASS TANKS TO wickedfather@hotmail.com FROM http://it2.php.net/manual/en/function.xml-parse-into-struct.php //
class XMLThing
{
    var $rawXML;
    var $valueArray = array();
    var $keyArray = array();
    var $parsed = array();
    var $index = 0;
    var $attribKey = 'attributes';
    var $valueKey = 'value';
    var $cdataKey = 'cdata';
    var $isError = false;
    var $error = '';

    function XMLThing($xml = NULL)
    {
        $this->rawXML = $xml;
    }

    function parse($xml = NULL)
    {
        if (!is_null($xml))
        {
            $this->rawXML = $xml;
        }

        $this->isError = false;
           
        if (!$this->parse_init())
        {
            return false;
        }

        $this->index = 0;
        $this->parsed = $this->parse_recurse();
        $this->status = 'parsing complete';

        return $this->parsed;
    }

    function parse_recurse()
    {       
        $found = array();
        $tagCount = array();

        while (isset($this->valueArray[$this->index]))
        {
            $tag = $this->valueArray[$this->index];
            $this->index++;

            if ($tag['type'] == 'close')
            {
                return $found;
            }

            if ($tag['type'] == 'cdata')
            {
                $tag['tag'] = $this->cdataKey;
                $tag['type'] = 'complete';
            }

            $tagName = $tag['tag'];

            if (isset($tagCount[$tagName]))
            {       
                if ($tagCount[$tagName] == 1)
                {
                    $found[$tagName] = array($found[$tagName]);
                }
                   
                $tagRef =& $found[$tagName][$tagCount[$tagName]];
                $tagCount[$tagName]++;
            }
            else   
            {
                $tagCount[$tagName] = 1;
                $tagRef =& $found[$tagName];
            }

            switch ($tag['type'])
            {
                case 'open':
                    $tagRef = $this->parse_recurse();

                    if (isset($tag['attributes']))
                    {
                        $tagRef[$this->attribKey] = $tag['attributes'];
                    }
                       
                    if (isset($tag['value']))
                    {
                        if (isset($tagRef[$this->cdataKey]))   
                        {
                            $tagRef[$this->cdataKey] = (array)$tagRef[$this->cdataKey];   
                            array_unshift($tagRef[$this->cdataKey], $tag['value']);
                        }
                        else
                        {
                            $tagRef[$this->cdataKey] = $tag['value'];
                        }
                    }
                    break;

                case 'complete':
                    if (isset($tag['attributes']))
                    {
                        $tagRef[$this->attribKey] = $tag['attributes'];
                        $tagRef =& $tagRef[$this->valueKey];
                    }

                    if (isset($tag['value']))
                    {
                        $tagRef = $tag['value'];
                    }
                    break;
            }           
        }

        return $found;
    }

    function parse_init()
    {
        $this->parser = xml_parser_create();

        $parser = $this->parser;
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);    
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);       
        if (!$res = (bool)xml_parse_into_struct($parser, $this->rawXML, $this->valueArray, $this->keyArray))
        {
            $this->isError = true;
            $this->error = 'error: '.xml_error_string(xml_get_error_code($parser)).' at line '.xml_get_current_line_number($parser);
        }
        xml_parser_free($parser);

        return $res;
    }
}
//-------------------------------------------------------------------------------------------------------------------//
// ALPATECH CLASS USING XMLTHING //
//-------------------------------------------------------------------------------------------------------------------//
class GXMLNode //--- CLASS FOR READ XML-DOM ---//
{
 var $Name;
 var $Attributes = array();
 var $Nodes = array();
 var $ArrayNodes = array();
 function GXMLNode($parent,$name, $arr)
 {
  $this->Name = $name;
  $this->Attributes = $arr['attributes'];
  if(!$arr)
   return;
  while(list($k,$v) = each($arr))
  {
   if($v[0])
   {
	for($c=0; $c < count($v); $c++)
	 $this->Nodes[] = new GXMLNode($this,$k,$v[$c]);
   }
   else if(!$arr['attributes'])
    $this->Nodes[] = new GXMLNode($this,$k,$v);
   else
   {
	switch($k)
	{
	 case 'value' : continue; break;
	 case 'attributes' : continue; break;
	 default : $this->Nodes[] = new GXMLNode($this,$k,$v); break;
	}
   }
  }
 }
 function Count() {return count($this->Nodes);}
 function GetElementsByTagName($s)
 {
  $ret = array();
  for($c=0; $c < $this->Count(); $c++)
  {
   if(strtolower($this->Nodes[$c]->Name) == strtolower($s))
    $ret[] = $this->Nodes[$c];
  }
  return $ret;
 }
 function getAttribute($an,$rn="")
 {
  if (!isset($this->Attributes[$an]))
   return $rn;
  return $this->Attributes[$an];
 }
 function getString($an,$rn="")
 {
  $ret = "";
  if (!isset($this->Attributes[$an]))
   return $rn;
  return $ret.$this->Attributes[$an];
 }
 function getInt($an,$rn=0)
 {
  $ret = 0;
  if (!isset($this->Attributes[$an]))
   return $rn;
  return $ret+$this->Attributes[$an];
 }
 function toString()
 {
  $xStr = "<".$this->Name." ";
  while(list($k,$v) = each($this->Attributes))
   $xStr.= " $k='$v'";
  $xStr.= ">";
  for($c=0; $c < count($this->Nodes); $c++)
   $xStr.= $this->Nodes[$c]->toString();
  $xStr.= "</".$this->Name.">";
  return $xStr;
 }
 function toArray()
 {
  $ret = array();
  if(count($this->Attributes))
  {
   while(list($k,$v) = each($this->Attributes))
    $ret[$k] = $v;
  }
  if(count($this->Nodes))
  {
   if(count($this->Nodes) > 1)
   {
    if($this->Nodes[0]->Name == $this->Nodes[1]->Name)
	 $useNum = true;
   }
   for($c=0; $c < count($this->Nodes); $c++)
   {
	if($useNum)
	 $ret[] = $this->Nodes[$c]->toArray();
	else
	 $ret[$this->Nodes[$c]->Name] = $this->Nodes[$c]->toArray();
   }
  }
  return $ret; 
 }
}

class GXML extends GXMLNode
{
 var $xml;
 var $Nodes = array();
 var $ArrayNodes = array();
 function GXML($filename="")
 {
  if($filename != "")
   $this->LoadFromFile($filename);
 }
 function LoadFromFile($filename)
 {
  if(!($fp = @fopen($filename, "r"))) 
   return false;
  $data = "";
  while (!feof($fp)) {
   $data.= fread($fp, 8192);
  }
  @fclose($fp);
  return $this->LoadFromString($data);
 }
 function LoadFromString($str)
 {
  $xmlt = new XMLThing($str);
  $this->xml = $xmlt->parse();  
  if(!$this->xml)
   return null;
  $rootnode = $this->xml['xml'] ? $this->xml['xml'] : $this->xml;
  $this->Attributes = $rootnode['attributes'];
  $this->RootNode = $rootnode;
  while(list($k,$v) = each($rootnode))
  {
   if($v[0])
   {
	for($c=0; $c < count($v); $c++)
	 $this->Nodes[] = new GXMLNode($this,$k,$v[$c]);
   }
   else if(!$rootnode['attributes'])
    $this->Nodes[] = new GXMLNode($this,$k,$v);
   else
   {
	switch($k)
	{
	 case 'value' : continue; break;
	 case 'attributes' : continue; break;
	 default : $this->Nodes[] = new GXMLNode($this,$k,$v); break;
	}
   }
  }
  return true;
 }
 function toString()
 {
  $xStr = "<xml>";
  for($c=0; $c < count($this->Nodes); $c++)
   $xStr.= $this->Nodes[$c]->toString();
  $xStr.= "</xml>";
  return $xStr;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function array_to_xml($arr, $root='xml')
{
 /* DO NOT EDIT THIS CODE TO AVOID PROBLEMS */
 if(!$arr)
  return null;
 if(is_int($root))
  $root = "item";

 $ret = "<$root";
 $sub = "";
 foreach($arr as $k => $v)
 {
  if(is_int($k))
  {
   if(is_array($v))
   {
    $subs = array();
    $sub.= "<item";
    foreach($v as $sk => $sv)
    {
     if(is_array($sv))
      $subs[] = array_to_xml($sv,$sk);
     else
      $sub.= " $sk=\"".xml_purify($sv)."\"";
    }
    if(!count($subs))
     $sub.= "/>"; /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
    else
    {
     $sub.= ">"; /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
     for($c=0; $c < count($subs); $c++)
      $sub.= $subs[$c];
     $sub.= "</item>"; /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
    }
   }
   else /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
    $sub.= "<item>".xml_purify($v)."</item>";
  }
  else if(is_array($v))
   $sub.= array_to_xml($v, $k);
  else
   $ret.= " $k=\"".xml_purify($v)."\"";
 }
 if($sub)
  $ret.= ">".$sub."</$root>"; /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
 else
  $ret.= "/>"; /* DO NOT ADD \n AT THE END OF KEY, CAUSE SHELL PROBLEMS */
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function xml_purify($str)
{
 $a = array("&","<",">",'"',"'");
 $b = array("&amp;","&lt;","&gt;","&quot;","&apos;");
 $str = preg_replace('/[\x{00ff}-\x{ffff}]/u', '*', $str);
 return str_replace($a,$b,$str);
}


