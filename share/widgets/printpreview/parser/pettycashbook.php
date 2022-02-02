<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-09-2012
 #PACKAGE: commercialdocs
 #DESCRIPTION: CommercialDocs parser for print preview.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

?>
<script>
function loadPreview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;
	 autoInsertRows(a['items']);
	}
 sh.sendCommand("<?php echo $_REQUEST['qry']; ?>");
}
</script>
<?php
