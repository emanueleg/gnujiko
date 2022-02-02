<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-10-2013
 #PACKAGE: apm-gui
 #DESCRIPTION: Gnujiko account registration.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_GNUJIKO_ACCOUNT, $_GNUJIKO_TOKEN;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("apm-gui");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Account Registration</title></head><body>
 <?php echo $msg; ?>
 <script>
 function bodyOnLoad()
 {
  gform_close("<?php echo $msg; ?>");
 }
 </script></body></html>
 <?php
 return;
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Account Registration</title>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<?php
if($_REQUEST['setcode'])
{
 ?>
 <div class='standardwidget' style='width:640px;height:420px'>
 <h2>Abilitazione account</h2>
 <hr/>
 <p style="font-family:arial,times;font-size:14px">
 Se la donazione &egrave; andata a buon fine, dovresti aver ricevuto una mail con i dati del tuo account.<br/><br/>
 Inserisci qui di seguito il nome dell&lsquo;account (che solitamente corrisponde alla mail con cui hai fatto la donazione) ed il codice token attribuito.
 </p>
 <p style="margin-bottom:140px">
 <table border="0" cellspacing="10" style="margin-top:40px">
 <tr><td align='right'><label><b>ACCOUNT:</b></label></td>
	 <td><input type="text" class="edit" style="width:240px" id="account" placeholder="Inserisci il nome del tuo account"/></td></tr>
 <tr><td align='right'><label><b>TOKEN:</b></label></td>
	 <td><input type="text" class="edit" style="width:260px" id="token" placeholder="Inserisci il codice token da 32 caratteri"/></td></tr>
 </table>
 </p>
 <hr/>
 <input type='button' class='button-blue' style='float:right;' value='Continua' onclick="enableAccount()"/> 
 <input type='button' class='button-gray' style='float:right;margin-right:10px' value='Annulla' onclick='abort()'/> 
 </div>
 <?php
}
else if($_REQUEST['invalidtoken'])
{
 ?>
 <div class='standardwidget' style='width:640px;height:370px'>
 <h2>Account non valido</h2>
 <hr/>
 <p style="font-family:arial,times;font-size:14px">
 Il tuo account non &egrave; valido.<br/>Inserisci il nome dell&lsquo;account ed il token che ti &egrave; stato inviato per email.
 </p>
 <p style="margin-bottom:140px">
 <table border="0" cellspacing="10" style="margin-top:40px">
 <tr><td align='right'><label><b>ACCOUNT:</b></label></td>
	 <td><input type="text" class="edit" style="width:240px" id="account" placeholder="Inserisci il nome del tuo account" value="<?php echo $_GNUJIKO_ACCOUNT; ?>"/></td></tr>
 <tr><td align='right'><label><b>TOKEN:</b></label></td>
	 <td><input type="text" class="edit" style="width:260px" id="token" placeholder="Inserisci il codice token da 32 caratteri" value="<?php echo $_GNUJIKO_TOKEN; ?>"/></td></tr>
 </table>
 </p>
 <hr/>
 <input type='button' class='button-blue' style='float:right;' value='Continua' onclick="enableAccount()"/> 
 <input type='button' class='button-gray' style='float:right;margin-right:10px' value='Annulla' onclick='abort()'/> 
 </div>
 <?php
}
else
{
 ?>
 <div class='standardwidget' style='width:640px;height:440px'>
 <h2>Registrazione account</h2>
 <hr/>
 <form method="post" name="paypal_form" id="paypal_form" target="blank" action="https://www.paypal.com/cgi-bin/webscr">
 <input type="hidden" name="business" value="paypal@alpatech.it" />
 <input type="hidden" name="cmd" value="_donations" />
 <input type="hidden" name="return" value="http://gnujiko.alpatech.it/donations/conferma_donazione.php" />
 <input type="hidden" name="cancel_return" value="http://gnujiko.alpatech.it/donations/donazione_annullata.php" />
 <input type="hidden" name="notify_url" value="http://gnujiko.alpatech.it/donations/ipn.php" />
 <input type="hidden" name="rm" value="2" />
 <input type="hidden" name="currency_code" value="EUR" />
 <input type="hidden" name="lc" value="IT" />
 <input type="hidden" name="cbt" value="Continua" />
 <input type="hidden" name="item_name" value="Progetto Gnujiko" />
 <input type="hidden" name="amount" value="10.00" />
 <input type="hidden" name="first_name" />
 <input type="hidden" name="last_name" />
 <input type="hidden" name="address1" />
 <input type="hidden" name="city" />
 <input type="hidden" name="state" />
 <input type="hidden" name="zip" />
 <input type="hidden" name="email" />
 </form>
 <p style="font-family:arial,times;font-size:12px">
 Gnujiko &egrave; sviluppato da programmatori volontari che giornalmente ampliano di funzionalit&agrave; e migliorano questo software per permettere a tutti di avere uno strumento semplice ma al tempo stesso completo per la gestione di una piccola impresa o per diventare parte integrante di realt&agrave; pi&ugrave; complesse.<br/><br/>
 In questo momento di crisi che sta investendo un po tutta l&lsquo;Italia e non solo ci sentiamo di dare il nostro contributo a tutte quelle piccole imprese e aziende che desiderano rinnovarsi offrendo questo prodotto e la nostra esperienza nel campo delle applicazioni gestionali.<br/><br/>
 Riceviamo molte email di ringraziamento, e di questo ve ne siamo davvero grati perch&egrave; ci d&agrave; una spinta per andare avanti, offriamo a tutti supporto nella personalizzazione e risoluzione di bug, sviluppiamo applicazioni e rilasciamo aggiornamenti quasi giornalieri, ne siamo fieri del vasto numero di utenti che utilizzano il nostro software, però ahim&egrave; la stragrande maggioranza degli utenti si dimentica di offrire un contributo, una donazione anche minima per permetterci di far fronte alle spese di gestione e per il lavoro che svolgiamo, e con il boom di download che c&lsquo;&egrave; stato in quest&lsquo;ultimo periodo e la mole di richieste e informazioni dei nuovi utenti ci ha portato inevitabilmente ad investire nelle infrastrutture per permettere di dare a tutti il miglior supporto.<br/>
 Pertanto ci troviamo costretti a chiedere una donazione obbligatoria di 10,00&euro; che &egrave; una cifra irrisoria rispetto alle svariate migliaia di euro che ne vale il programma, ma almeno ci permette di far fronte alle spese di gestione.<br/><br/>
 Clicca sul tasto prosegui per effettuare la donazione, una volta completata ti verr&agrave; inviata un email con un codice e la procedura per l&lsquo;attivazione dell&lsquo;account, da quel momento in poi potrai installare ed aggiornare tutte le applicazioni di Gnujiko che vorrai.<br/><br/>
 Grazie per il tuo contributo.<br/>
 Lo staff del progetto Gnujiko.
 </p>
 <hr/>
 <input type='button' class='button-yellow' value="Ho gia un account" onclick='setCode()'/>
 <input type='button' class='button-blue' style='float:right;' value='Prosegui' onclick="makeDonation()"/> 
 <input type='button' class='button-gray' style='float:right;margin-right:10px' value='Annulla' onclick='abort()'/> 
 </div>
 <?php
}
?>

<script>
function bodyOnLoad()
{
}

function abort()
{
 gframe_close();
}

function makeDonation()
{
 var href = document.location.href;
 href+= "&setcode=true";
 document.getElementById("paypal_form").submit();
 window.setTimeout(function(){document.location.href=href;},1000); 
}

function setCode()
{
 var href = document.location.href;
 href+= "&setcode=true";
 document.location.href=href;
}

function enableAccount()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){gframe_close("done!",true);}
 sh.sendSudoCommand("apm edit-account -name `"+document.getElementById("account").value+"` -token `"+document.getElementById("token").value+"`");
}
</script>
</body></html>
<?php

