<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-12-2013
 #PACKAGE: barcodegen
 #DESCRIPTION: Barcode Generator
 #VERSION: 2.0beta-5.1.0
 #CHANGELOG: 
 #TODO:
 

This script is free for personal use. The program is provide "AS IS"
without warranty of any kind. If you want to use it as commercial use, you have to purchase it on http://www.barcodephp.com
You must let the copyright intact.

Ce script est gratuit pour usage personnel. Le programme est fourni "TEL QUEL" sans aucune garantie que ce soit.
Si vous voulez l'utiliser pour un usage commercial,vous devez l'acheter sur http://www.barcodephp.com
Vous devez laisser les droits d'auteur intacts.

*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

// Including all required classes
require_once($_BASE_PATH.'var/objects/barcodegen/class/BCGFontFile.php');
require_once($_BASE_PATH.'var/objects/barcodegen/class/BCGColor.php');
require_once($_BASE_PATH.'var/objects/barcodegen/class/BCGDrawing.php');

// Including the barcode technology
require_once($_BASE_PATH.'var/objects/barcodegen/class/BCGcode39.barcode.php');

//-------------------------------------------------------------------------------------------------------------------//
$text = isset($_REQUEST['barcode']) ? $_REQUEST['barcode'] : 'ERROR, undefined barcode';
/* TODO: possibilità di scegliere il colore di sfondo e quello delle barre */

//-------------------------------------------------------------------------------------------------------------------//
$font = new BCGFontFile($_BASE_PATH.'var/objects/barcodegen/font/Arial.ttf', 18);
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);
//-------------------------------------------------------------------------------------------------------------------//
$drawException = null;
try 
{
 $code = new BCGcode39();
 $code->setScale(2); // Resolution
 $code->setThickness(30); // Thickness
 $code->setForegroundColor($color_black); // Color of bars
 $code->setBackgroundColor($color_white); // Color of spaces
 $code->setFont($font); // Font (or 0)
 $code->parse($text); // Text
} 
catch(Exception $exception) 
{
 $drawException = $exception;
}
//-------------------------------------------------------------------------------------------------------------------//
/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$drawing = new BCGDrawing('', $color_white);
if($drawException) 
 $drawing->drawException($drawException);
else
{
 $drawing->setBarcode($code);
 $drawing->draw();
}
//-------------------------------------------------------------------------------------------------------------------//
// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="barcode.png"');
//-------------------------------------------------------------------------------------------------------------------//
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
//-------------------------------------------------------------------------------------------------------------------//
?>

