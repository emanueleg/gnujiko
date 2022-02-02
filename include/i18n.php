<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-04-2011
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Language Support file
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_DICTIONARY, $_LANGUAGE;

$_DICTIONARY = array();

function i18n($str)
{
 global $_DICTIONARY;
 return $_DICTIONARY[$str] ? $_DICTIONARY[$str] : $str;
}

function LoadLanguage($langFile, $lang=null)
{
 global $_BASE_PATH, $_DICTIONARY, $_LANGUAGE;
 if(!$lang)
  $lang = $_LANGUAGE;

 if(file_exists($_BASE_PATH."etc/language/".$lang."/".$langFile.".php"))
  include_once($_BASE_PATH."etc/language/".$lang."/".$langFile.".php");
}
