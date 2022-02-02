<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-02-2013
 #PACKAGE: makedist-language-pack-it
 #DESCRIPTION: Italian translation file for makedist.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_DICTIONARY;

/* steps */
$_DICTIONARY["welcome"] = "benvenuti";
$_DICTIONARY["step <b>%d</b> of <b>%d</b>"] = "fase <b>%d</b> di <b>%d</b>";
$_DICTIONARY["Yes"] = "Si";
$_DICTIONARY["No"] = "No";
$_DICTIONARY["Try again"] = "Riprova";
$_DICTIONARY["Error"] = "Errore";


/* step0-select-language.php */
$_DICTIONARY["Welcome to Gnujiko installation wizard"] = "Benvenuti all'installazione di Gnujiko";
$_DICTIONARY["PLEASE SELECT YOUR LANGUAGE:"] = "SCEGLI LA LINGUA:";
$_DICTIONARY["Install"] = "Installa";
$_DICTIONARY["Install &raquo; Select language"] = "Installazione &raquo; Seleziona lingua";

/* step1-database-config.php */
$_DICTIONARY["Install &raquo; Database configuration"] = "Installazione &raquo; Configurazione del database";
$_DICTIONARY["Next"] = "Avanti";
$_DICTIONARY["Specify the server on which the database resides."] = "Specificare il server in cui risiede il database.";
$_DICTIONARY["Specify a MySQL user."] = "Indicare un utente MySQL.";
$_DICTIONARY["Enter the password for the MySQL user."] = "Inserire la password per l'utente MySQL.";
$_DICTIONARY["Specify the name of the database."] = "Specificare il nome del database.";
$_DICTIONARY["Specify a MySQL user who has sufficient permissions to create databases or you will need to create the database manually."] = "Indicare un utente MySQL che abbia i permessi sufficienti per creare database altrimenti sarà necessario creare il database manualmente.";
$_DICTIONARY["I can not connect to the MySQL server, check you have entered the correct parameters."] = "Non riesco a connettermi al server MySQL, controlla di avere inserito i parametri giusti.";
$_DICTIONARY["There is already a database called <b>%s</b>, you want to overwrite it?"] = "Esiste gi&agrave; un database che si chiama <b>%s</b>, desideri sovrascriverlo?";
$_DICTIONARY["I can connect to the MySQL server using the credentials that you gave me, but I can not create the database, that user probably does not have sufficient permissions. <br/><br/> In order to continue you must create the database manually, you should do it through the control panel or MySQL using the tools provided by your service provider / maintainer. <br/><br/> Once you have created the database click on the &lsquo;<b>Try again</b>&lsquo, to continue."] = "Riesco a connettermi al server MySQL con le credenziali che mi hai dato, per&ograve; non riesco a creare il database, probabilmente quell&lsquo;utente non ha sufficienti permessi.<br/><br/>Per poter proseguire &egrave; necessario a questo punto creare il
database manualmente; devi farlo tu attraverso il pannello di controllo MySQL o utilizzando gli strumenti forniti dal tuo provider / maintainer.<br/><br/>Una volta creato il database clicca sul tasto &lsquo;<b>Riprova</b>&lsquo; per continuare.";
$_DICTIONARY["You must specify the server on which the database resides. It is usually localhost"] = "Devi specificare il server in cui risiede il database. Di solito è localhost";
$_DICTIONARY["You must specify a user MySQL. In most cases, root or admin."] = "Devi indicare un utente MySQL. Nella maggior parte dei casi è root oppure admin.";
$_DICTIONARY["You must specify the name of the database. Ex: gnujiko"] = "Devi indicare il nome del database. Es: gnujiko";
$_DICTIONARY["Are you sure you want to overwrite the database"] = "Sei sicuro di voler sovrascrivere il database";
$_DICTIONARY["All data will be lost!"] = "Tutti i dati andranno perduti!";

/* step2-ftp-settings.php */
$_DICTIONARY["Install &raquo; FTP settings"] = "Installazione &raquo; Impostazioni FTP";
$_DICTIONARY["Specify the FTP server"] = "Specificare il server FTP";
$_DICTIONARY["Enter an FTP user."] = "Indicare un utente FTP.";
$_DICTIONARY["Enter the password for FTP access."] = "Inserire la password per l&lsquo;accesso FTP.";
$_DICTIONARY["Enter the full path to the Gnujiko directory"] = "Percorso completo alla cartella di Gnujiko";
$_DICTIONARY["Connection failed!"] = "Connessione fallita!";
$_DICTIONARY["I can not connect to the FTP server, check you have entered the correct parameters."] = "Non riesco a connettermi via FTP, controlla di avere inserito i parametri giusti.";
$_DICTIONARY["Login failed!"] = "Accesso fallito!";
$_DICTIONARY["I can not login to FTP using the credentials that you gave me, check that your user name and password are correct."] = "Non riesco ad accedere a FTP con le credenziali che mi hai fornito, controlla che il nome utente e la password siano corrette.";
$_DICTIONARY["FTP path wrong!"] = "Path FTP sbagliata!";
$_DICTIONARY["I can connect to FTP server using the credentials that you provided, but I can not access to the root folder of Gnujiko. <br/><br/>Probably the path you provided is incorrect."] = "Riesco a connettermi al server FTP con le credenziali che hai fornito, però non riesco ad accedere alla cartella radice di Gnujiko.<br/><br/>Probabilmente la path (il percorso) che hai fornito è sbagliata.";

/* step3-account-settings.php */
$_DICTIONARY["Install &raquo; Account settings"] = "Installazione &raquo; Impostazioni account";
$_DICTIONARY["Password for the administrator"] = "Password per l&lsquo;amministratore";
$_DICTIONARY["Retype password"] = "Ridigita la password";
$_DICTIONARY["Enter a name for the primary user"] = "Indicare un nome per l'utente principale";
$_DICTIONARY["Password for the primary user"] = "Password per l'utente principale";
$_DICTIONARY["Now you must choose a password for the administrator (root) and enter a name and a password to assign to the first user."] = "Ora devi scegliere una password per l'amministratore (root) ed indicare un nome ed una password da assegnare all'utente principale.";
$_DICTIONARY["You must enter a valid password for the administrator"] = "Devi inserire una password valida per l'amministratore";
$_DICTIONARY["The administrator passwords do not match."] = "Le password per l'amministratore non coincidono.";
$_DICTIONARY["You must specify a valid name for the first user"] = "Devi specificare un nome valido per il primo utente";
$_DICTIONARY["You must enter a valid password for the first user"] = "Devi inserire una password valida per il primo utente";
$_DICTIONARY["The user passwords do not match."] = "Le password dell'utente non coincidono.";

/* step4-permissions-check.php */
$_DICTIONARY["Install &raquo; Permission check"] = "Installazione &raquo; Verifica permessi a files e cartelle";
$_DICTIONARY["If you want to continue the installation without access to FTP is necessary that <b>all the folders and files</b> within your folder Gnujiko have permission to <b>read and write</b>."] = "Se desideri continuare l'installazione senza l'accesso a FTP è necessario che <b>tutte le cartelle</b> e <b>tutti i files</b> all'interno della vostra cartella di Gnujiko abbiano i permessi in <b>lettura e scrittura</b>.";
$_DICTIONARY["and more..."] = "e tanti altri...";

/* step5-database-import.php */
$_DICTIONARY["Install &raquo; Database import"] = "Installazione &raquo; Importazione del database";
$_DICTIONARY["Wait until the system import the main database, depending on the size this may take several minutes."] = "Attendere che il sistema importi il database principale, a seconda delle dimensioni può impiegarci anche qualche minuto.";
$_DICTIONARY["Importing the database..."] = "Importazione del database principale in corso...";
$_DICTIONARY["Error. Can not continue!"] = "Errore. Impossibile continuare!";
$_DICTIONARY["The problem occurred in the step:"] = "Il problema è avvenuto nella fase:";
$_DICTIONARY["Error code:"] = "Codice errore restituito:";
$_DICTIONARY["message"] = "messaggio";
$_DICTIONARY["Updating config.php"] = "Aggiornamento file config.php";
$_DICTIONARY["Creating root user"] = "Creazione dell'utente root (amministratore)";
$_DICTIONARY["Creating first user"] = "Creazione dell'utente principale";
$_DICTIONARY["Investigate the incident"] = "Indaga sull'accaduto";


/* step6-check-for-updates.php */
$_DICTIONARY["Installation completed"] = "Installazione completata";
$_DICTIONARY["completed"] = "completato";
$_DICTIONARY["Finish"] = "Finito";
$_DICTIONARY["Installation Complete!"] = "Installazione completata!";
$_DICTIONARY["The installation was completed! <br/> You can close this window"] = "L'installazione è stata completata! <br/> Puoi chiudere questa finestra";

