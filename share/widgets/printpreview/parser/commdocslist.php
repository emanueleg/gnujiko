<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-05-2013
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
 var query = "<?php echo $_REQUEST['qry']; ?>";
 query = query.replace(/\ '''/g, " `");
 query = query.replace(/\''' /g, "` ");

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;
	 autoInsertRows(a['items']);
	}
 sh.sendCommand(query);
}
</script>
<?php
