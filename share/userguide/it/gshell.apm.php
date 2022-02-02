<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();
include_once("./toc/gshellcommands.inc");

$setup = array (
  'section' =>
  array (
	0 => 'gshellcommands.php',
	1 => 'Comandi GShell',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'gshell.apm.php',
    1 => 'apm',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'next' =>
  array (
    0 => 'gshell.cp.php',
    1 => 'cp',
  ),
  'lastupdate' =>
  array (
	0 => '2011-12-19',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>apm</h3> <p>Alpatech Package Manager. Gestore dei <strike>pacchetti</strike> repository di Gnujiko.<br /> Nelle vecchie versioni di Gnujiko, APM&nbsp;gestiva tramite un interfaccia grafica l&lsquo;installazione/disinstallazione dei pacchetti; ora questo comando si limita solamente alla gestione dei repository. <br />Per creare/installare/disinstallare i pacchetti vedere il comando <a href="gshell.gpkg.php">gpkg</a>.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>apm </strong>AZIONE [<u>opzioni</u>]<u><br /> </u></p> <p>&nbsp;</p> <h4>AZIONI</h4> <p style="margin-left: 40px;"><strong>update</strong></p> <p style="margin-left: 80px;">Aggiorna il database con la lista dei pacchetti disponibili collegandosi ai repository segnalati nel file <strong>etc/apm/sources.list</strong>.</p> <h4 style="margin-left: 80px;">OUTPUT</h4> <p style="margin-left: 80px;">Il comando <strong>apm update</strong> ritorna un array di informazioni:</p> <p style="margin-left: 120px;"><strong>sections</strong> [0,1,2,...]</p> <p style="margin-left: 160px;">Ritorna l&lsquo;elenco dei nomi delle sezioni trovate.</p> <p style="margin-left: 120px;"><strong>packages</strong> [0,1,2,...]</p> <p style="margin-left: 160px;">Ritorna l&lsquo;elenco di tutti i pacchetti disponibili.</p> <p>&nbsp;</p> <p style="margin-left: 40px;"><strong>clean</strong></p> <p style="margin-left: 80px;">Svuota il database dei pacchetti disponibili non installati, mantenendo quelli installati.</p> <p>&nbsp;</p> <p style="margin-left: 40px;"><strong>add-repository</strong> -url <u>REPOSITORY&nbsp;URL</u> -ver <span style="text-decoration: underline;">GNUJIKO</span><u> VERSION</u> -sec <u>REPOSITORY&nbsp;SECTION</u></p> <p style="margin-left: 80px;">Aggiunge un repository (canale software). L&lsquo;azione add-repository accetta le seguenti opzioni (tutte e 3 obbligatorie).</p> <p style="margin-left: 80px;"><strong>-url</strong> <u>REPOSITORY&nbsp;URL</u></p> <p style="margin-left: 120px;">Specificare l&lsquo;URL del repository. Es: http://gnujiko.alpatech.it</p> <p style="margin-left: 80px;"><strong>-ver</strong> <u>GNUJIKO_VERSION</u></p> <p style="margin-left: 120px;">Specificare la versione di Gnujiko. Almeno di non sapere quello a cui si va in contro, bisogna <strong>SEMPRE</strong> specificare la versione corrente di Gnujiko, in questo caso la <strong>10.1</strong>; altrimenti si rischia di installare un pacchetto non compatibile con il sistema in uso o peggio ancora causare dei problemi gravi di sovrascrittura dei pacchetti installati che hanno lo stesso nome con conseguente stallo del sistema.</p> <p style="margin-left: 80px;"><strong>-sec</strong> <u>REPOSITORY_SECTION</u></p> <p style="margin-left: 120px;">Specificare la sezione di appartenenza del repository. Ad esempio: main, beta, custom, ecc.<br /> Di norma le sezioni beta vengono utilizzate per quei pacchetti ancora da testare che possono presentare bug o funzioni non completate. Utile ai beta tester.<br /> Le sezioni main invece sono quelle stabili, bug-fixed.<br /> Le sezioni custom possono invece essere quelle personalizzate per un dato cliente. Ad esempio se al cliente Mario Rossi ho fatto una modifica o personalizzazione che &egrave; utile solo per quel cliente e non per altri, creer&ograve; magari una sezione chiamata appunto mariorossi o meglio ancora codificata (Es: ABC123DEFG5544), ovviamente poi, a quel cliente, rimuover&ograve; dalla lista dei repository la sezione main cambiandola con quella personalizzata.</p> <h4 style="margin-left: 80px;">ERRORI</h4><p style="margin-left: 80px;">Il comando <strong>apm add-repository</strong> pu&ograve; generare i seguenti tipi di errore:</p><p style="margin-left: 120px;"><strong>REPOSITORY_ALREADY_EXISTS</strong> - Si sta cercando di creare un repository che gi&agrave; esiste.<br /><strong>UNABLE_TO_WRITE</strong> - Il sistema non riesce a scrivere sul file <strong>etc/apm/sources.list</strong>, verificare i permessi di accesso in scrittura a tale file.</p><p>&nbsp;</p> <p style="margin-left: 40px;"><strong>delete-repository</strong> -url <u>REPOSITORY&nbsp;URL</u> -ver <span style="text-decoration: underline;">GNUJIKO</span><u> VERSION</u> -sec <u>REPOSITORY&nbsp;SECTION</u></p> <p style="margin-left: 80px;">Rimuove un repository (canale software) dall elenco. L&lsquo;azione delete-repository, come con add-repository, accetta le opzioni <strong>-url</strong> , <strong>-ver</strong> , <strong>-sec</strong> (tutte e 3 obbligatorie).</p><h4 style="margin-left: 80px;">ERRORI</h4><p style="margin-left: 80px;">Il comando apm delete-repository pu&ograve; generare i seguenti tipi di errore:</p><p style="margin-left: 120px;"><strong>UNABLE_TO_READ_FILE</strong> - Il sistema non riesce a leggere dal file <strong>etc/apm/sources.list</strong>, verificare i permessi in lettura a tale file.<br /><strong>REPOSITORY_NOT_FOUND</strong> - Il repository che si cerca di eliminare non esiste.<br /><strong>UNABLE_TO_WRITE</strong> - Il sistema non riesce a scrivere sul file <strong>etc/apm/sources.list</strong>, verificare i permessi di accesso in scrittura a tale file.</p> <p>&nbsp;</p> <p style="margin-left: 40px;"><strong>repository-list</strong> [--verbose]</p> <p style="margin-left: 80px;">Mostra l&lsquo;elenco dei repository che si trovano all&lsquo;interno del file <strong>etc/apm/sources.list</strong>. Aggiungendo l&lsquo;opzione <strong>--verbose</strong> verr&agrave; mostrata la lista anche a video, altrimenti verr&agrave; solamente ritornato l&lsquo;array di informazioni.</p> <h4 style="margin-left: 80px;">OUTPUT</h4> <p style="margin-left: 80px;">Il comando <strong>apm repository-list</strong> ritorna un array di informazioni:</p> <p style="margin-left: 120px;"><strong>type</strong> - Indica il tipo di repository. (url, ftp o media) dove:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>url</strong> - indica il canale classico: inizia per http://<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>ftp</strong> - indica un canale FTP anonimo (che non necessita di username e password per accedervi):&nbsp;inizia per ftp://<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>media</strong> - indica che il canale non si trova su internet/intranet ma su un supporto/drive locale:&nbsp;esempio chiavette usb, dischi esterni, una cartella locale, ecc...<br /><br /><strong>url</strong> - Indica l&lsquo;url del repository comprensivo del prefisso (http://... , ftp://... , ecc...).<br /><br /><strong>ver</strong> - Versione di Gnujiko per cui &egrave; stato creato il repository.<br /><br /><strong>section</strong> - Sezione del repository. (Es: main, beta, ...)</p><h4 style="margin-left: 80px;">ERRORI</h4><p style="margin-left: 80px;">Il comando apm repository-list pu&ograve; generare i seguenti errori:</p><p style="margin-left: 120px;"><strong>REPOSITORY_LIST_ERROR</strong> - Il sistema non riesce a leggere dal file <strong>etc/apm/sources.list</strong>, verificare i permessi in lettura a tale file.</p>                 <p>&nbsp;&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <div class="indent"><dl>     <dt>Attenzione:</dt>     <dd>Per utilizzare il comando <strong>apm</strong> &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd> </dl> <p><tt>sudo apm update</tt><br /> <tt>sudo apm add-repository -url http://betatest.alpatech.it -sec beta -ver 10.1</tt><br /> <tt>sudo apm add-repository -url F:/myfolder/ -sec custom -ver 10.1</tt><strong><br /> </strong></p></div> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/apm.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.gpkg.php">gpkg</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.apm.php">apm</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

