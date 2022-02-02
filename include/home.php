<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-12-2011
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Default home page
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<style type='text/css'>
body {
	background: #e5ecfa;
}

div.container {
	width: 900px;
	height: 600px;
	background: url(share/images/gnujikobase/background.png) top left no-repeat;
	position: absolute;
	top: 50%;
	left: 50%;
	margin-left: -450px;
	margin-top: -300px;
	padding: 20px;
}

hr {
	height: 1px;
	border: 0px;
	background: #aaccee;
	width: 860px;
	float: left;
}

h3 {
	font-family: Trebuchet, Arial, Sans;
	font-size: 22px;
	color: #f79223;
	font-weight: normal;
	text-align: center;
	margin-bottom: 10px;
}

a.blue {
	font-family: Arial;
	font-size: 16px;
	font-weight: bold;
	color: #0169c9;
}

a.green {
	font-family: Arial;
	font-size: 12px;
	color: #015a01;
}

p, li {
	font-size: 12px;
	font-family: Arial;
}

p {
	margin-top: 4px;
}

div.footer {
	border-top: 1px solid #aaccee; 
	font-size:12px;
	font-family:arial;
	width:840;
	padding:8px;
	position: absolute;
	bottom: 40px;
}
</style>

<body>
<div class='container'>
 <img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/biglogo.png"/>
 <hr/>
 <h3>Benvenuto in Gnujiko Framework 10.1</h3>

 <table border='0' width='840' style='margin-left:20px;' cellspacing='8'>
  <tr><td valign='top'>
		<p>
		Hai scelto di installare la versione base che include solo il minimo indispensabile per far girare il sistema.<br/>
		Giusto lo stretto necessario per poter crearsi ad esempio una distribuzione personalizzata da zero e installare solamente i pacchetti che vi servono.</p>
		<p>
		Questa versione include:
		<ul>
		 <li>Un gestore per l&lsquo;interazione con i database MySQL.</li>
		 <li>Un parser XML.</li>
		 <li>Un compressore / scompattatore di archivi zip.</li>
		 <li>Un gestore per gli account e strumenti per gestire utenti e gruppi.</li>
		 <li>Un terminale a linea di comando.</li>
		 <li>Strumenti per l&lsquo;installazione e la compilazione dei pacchetti.</li>
		</ul>
		</p>
		<p>
		Gnujiko &egrave; un framework che utilizza come punto di forza una shell integrata facile da utilizzare sia in Javascript che in PHP; non ci sono limiti nella personalizzazione dei suoi componenti e nell&lsquo;interazione con le varie applicazioni. </p>
	  </td><td valign='top' width='360'>
			<img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/structure.png"/>
			<br/><div align='center' style='font-size:12px;font-family: arial;'>Gnujiko 10.1 base structure</div>
		   </td>
  </tr>
 </table>

 <table border='0' width='840' style='margin-left:20px;margin-top:10px;' cellspacing='18'>
  <tr><td valign='top' width='280' style='border-right:1px solid #aaccee;'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/account.png" style='text-align:left;float:left;vertical-align:top;margin-right:10px;margin-bottom:20px;'/>
		<a class='blue' href='accounts/index.php'>Account Manager</a>
		<p>Gestisci tutte le informazioni relative al tuo account.<br/>
		   Password di accesso, email e dati personali.</p>
	  </td>
	  <td valign='top' width='280' style='border-right:1px solid #aaccee;'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/shell.png" style='text-align:left;vertical-align:top;float:left;margin-right:10px;margin-bottom:40px;'/>
		<a class='blue' href='gshell.php'>Gnujiko Shell</a>
		<p>E&lsquo; possibile interagire con Gnujiko anche attraverso un terminale virtuale a linea di comando.<br/><br/>
		<a class='green' href="<?php echo $_ABSOLUTE_URL; ?>share/userguide/it/gshellcommands.php">Lista dei comandi GShell &raquo;</a></p>
	  </td>
	  <td valign='top' width='280'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/user-guide.png" style='text-align:left;vertical-align:top;float:left;margin-right:10px;margin-bottom:20px;'/>
		<a class='blue' href="<?php echo $_ABSOLUTE_URL; ?>share/userguide/">Guida all&lsquo;uso</a>
		<p>Manuale utente per la configurazione del sistema e l&lsquo;installazione dei pacchetti.</p>
	  </td>
  </tr>
 </table>

 <div align='center' class='footer'>
 Gnujiko &egrave; un prodotto sviluppato da <a class='green' href="http://www.alpatech.it">Alpatech mediaware</a> rilasciato sotto licenza <a class='green' href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html#SEC1">GNU General Public License v.2</a>
 </div>
</div>
</body></html>
<?php

