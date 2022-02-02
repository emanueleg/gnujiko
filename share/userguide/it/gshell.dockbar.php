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
    0 => 'gshell.dockbar.php',
    1 => 'dockbar',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.cp.php',
    1 => 'cp',
  ),
  'next' =>
  array (
    0 => 'gshell.echo.php',
    1 => 'echo',
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
<h3>dockbar</h3> <p>Gestisce i pulsanti nella barra delle applicazioni.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>dockbar</strong> <u>AZIONE</u> [<u>opzioni</u>]...<u><br /> </u></p> <p>&nbsp;</p> <h4><strong>AZIONI</strong></h4> <ul>     <li><a href="#dockbar-insert">insert</a></li>     <li><a href="#dockbar-edit">edit</a></li>     <li><a href="#dockbar-delete">delete</a></li>     <li><a href="#dockbar-list">list</a></li> </ul> <p>&nbsp;</p> <p>&nbsp;</p> <h3><a name="dockbar-insert">dockbar insert</a></h3> <p>Inserisce un pulsante nella barra delle applicazioni.<br />Per inserire un elemento nella barra delle applicazioni &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>dockbar</strong> <strong>insert</strong> -name <u>NAME</u> -loader <u>LOADERFILE</u> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4>OPZIONI</h4> <p>Il comando <strong>dockbar</strong> <strong>insert</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>-name</strong> <span style="text-decoration: underline;">NAME</span></p> <p style="margin-left: 80px;">Specifica un nome da assegnare al nuovo pulsante.</p> <p style="margin-left: 40px;"><strong>-loader</strong> <u>LOADERFILE</u></p> <p style="margin-left: 80px;">Specificare il file necessario al caricamento del bottone.</p> <p style="margin-left: 40px;"><strong>-group</strong> <u>GROUPNAME</u></p> <p style="margin-left: 80px;">Se specificato, il bottone sar&agrave; accessibile solo agli utenti membri di quel gruppo.</p> <p style="margin-left: 40px;"><strong>-perms</strong> <u>MODE</u></p> <p style="margin-left: 80px;">Imposta i permessi di accesso a quel bottone. Di default &egrave; 440.</p> <p style="margin-left: 40px;"><strong>-ordering</strong> <u>NUMBER</u></p> <p style="margin-left: 80px;">Imposta la posizione del bottone. Es: 0 = in prima posizione, 1&nbsp;=&nbsp;in seconda, 2&nbsp;= in terza posizione, ecc...<br /> Se non viene specificato questo parametro il sistema lo accoda (in ultima posizione).</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>id</strong> - ID del bottone.<br /> <strong>name</strong> - Nome del bottone.<br /> <strong>loader</strong> - File loader.<br /> <strong>ordering</strong> - Posizione del bottone nella lista.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per inserire bottoni nella dockbar &egrave; necessario avere i privilegi di amministratore (root).<br /> <strong>INVALID_NAME</strong> - Hai dimenticato di assegnare un nome a questo bottone.<br /> <strong>INVALID_LOADER_FILE</strong> - E&lsquo; necessario specificare il file loader per questo bottone.</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <div class="indent"><dl>     <dt>Attenzione:</dt>     <dd>Per inserire bottoni alla dockbar &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd> </dl> <p><tt>sudo dockbar insert -name &quot;test button&quot; -loader share/widgets/applications/test/dockbar.php</tt><br /> <strong><br /> </strong></p></div> <p>&nbsp;</p> <hr /> <p>&nbsp;</p> <h3><a name="dockbar-edit">dockbar edit</a></h3> <p>Modifica un pulsante della barra delle applicazioni.<br />Per modificare un elemento della barra delle applicazioni &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>dockbar edit</strong> -id <u>ITEM ID</u> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>dockbar</strong> <strong>edit</strong> accetta le seguenti opzioni.</p> <p style="margin-left: 40px;"><strong>-id</strong> <u>ITEM ID</u></p> <p style="margin-left: 80px;">Specificare l&lsquo;ID del bottone.</p> <p style="margin-left: 40px;"><strong>-name</strong> <u>NEW NAME</u></p> <p style="margin-left: 80px;">Rinomina il bottone.</p> <p style="margin-left: 40px;"><strong>-loader</strong> <u>FILE&nbsp;LOADER</u></p> <p style="margin-left: 80px;">Modifica il riferimento al file loader del bottone.</p> <p style="margin-left: 40px;"><strong>-ordering</strong> <u>POSITION</u></p> <p style="margin-left: 80px;">Specifica in quale posizione si vuole il bottone. 0&nbsp;=&nbsp;prima posizione, 1&nbsp;=&nbsp;seconda, 2&nbsp;=&nbsp;terza, ecc...</p> <p style="margin-left: 40px;"><strong>-published</strong> 0 | 1</p> <p style="margin-left: 80px;">Inserire <strong>1</strong> per rendere visibile il bottone, <strong>0</strong> per nasconderlo.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Il comando <strong>dockbar</strong> <strong>edit</strong> non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per modificare i bottoni della dockbar &egrave; necessario avere i privilegi di amministratore (root).<br /> <br /> <strong>INVALID_ITEM</strong> - Hai dimenticato di specificare l&lsquo;ID del bottone da modificare.</p> <p>&nbsp;</p> <h4>ESEMPI</h4> <div class="indent"><dl>     <dt>Attenzione:</dt>     <dd>Per modificare i bottoni della dockbar &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd> </dl> <p><tt>sudo dockbar edit -id 2 -name &quot;new test button&quot;</tt><br /> <tt>sudo dockbar edit -id 2 -perms 400</tt><br /> <strong><br /> </strong></p></div> <p>&nbsp;</p><hr /><p>&nbsp;</p>  <h3><a name="dockbar-delete">dockbar delete</a></h3> <p>Rimuove un pulsante dalla barra delle applicazioni.<br />Per rimuovere un elemento dalla barra delle applicazioni &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>dockbar</strong> <strong>delete</strong> -id <u>ITEM&nbsp;ID</u><br /> <strong>dockbar</strong> <strong>delete</strong> -name <u>ITEM NAME</u></p> <p>&nbsp;</p> <h4>DESCRIZIONE</h4> <p>Per rimuovere un elemento dovete specificare il suo ID (-id) oppure il suo nome (-name).</p><p>&nbsp;</p> <h4>OUTPUT</h4> <p>Il comando <strong>dockbar</strong> <strong>delete</strong> non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.</p> <p>&nbsp;</p> <h4>ERRORI</h4><p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per rimuovere i bottoni dalla dockbar &egrave; necessario avere i privilegi di amministratore (root).<br /> <br /> <strong>INVALID_ITEM</strong> - Hai dimenticato di specificare l&lsquo;ID del bottone da rimuovere.<br /><br /><strong>ITEM_DOES_NOT_EXISTS</strong> - L&lsquo; elemento che hai specificato non esiste.</p><p>&nbsp;</p><h4>ESEMPI</h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per rimuovere i bottoni dalla dockbar &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo dockbar delete -id 2</tt><br /> <tt>sudo dockbar delete -name &quot;test button&quot;</tt><br /> <strong><br /> </strong></p></div><p>&nbsp;</p><hr /><p>&nbsp;</p>  <h3><a name="dockbar-list">dockbar list</a></h3> <p>Mostra la lista degli elementi nella barra delle applicazioni.</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>dockbar</strong> <strong>list</strong> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4>OPZIONI</h4> <p>Il comando <strong>dockbar list</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>--order-by</strong> <u>FIELD&nbsp;AND&nbsp;METHOD</u></p> <p style="margin-left: 80px;">Mostra la lista ordinata in base ai criteri specificati.<br /> Le possibili modalit&agrave; sono:</p> <table cellspacing="1" cellpadding="1" border="0" style="margin-left: 80px; width: 580px; height: 107px;">     <tbody>         <tr>             <td>&nbsp;&quot;<tt>name ASC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine alfabetico dalla A alla Z.</td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>name DESC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine alfabetico dalla Z alla A.</td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>ordering ASC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine di inserimento/manuale. <em>(default)</em></td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>ordering DESC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine di inserimento/manuale,<br />&nbsp;dall&lsquo;ultimo al primo.</td>         </tr>     </tbody> </table> <p style="margin-left: 80px;">Negli argomenti specificate la modalit&agrave; racchiusa tra virgolette &quot; &quot;.</p> <p style="margin-left: 40px;"><strong>--include-unpublished</strong></p><p style="margin-left: 80px;">Se specificato, verranno inclusi nella lista anche gli elementi non pubblicati.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Il comando <strong>dockbar</strong> <strong>list</strong> ritorna un array di informazioni:</p><p style="margin-left: 40px;"><strong>id</strong> - ID&nbsp;dell&lsquo;elemento.<br /><strong>name</strong> - Nome dell&lsquo;elemento.<br /><strong>loader</strong> - Percorso completo del file loader.<br /><strong>published</strong> - 1=pubblicato , 0=non pubblicato.</p><p>&nbsp;</p><h4>ESEMPI</h4><p style="margin-left: 40px;"><tt>dockbar list --order-by &quot;name ASC&quot;<br />dockbar list --order-by &quot;ordering DESC&quot; --include-unpublished<br /></tt></p><p>&nbsp;</p> <hr /> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/dockbar.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.mainmenu.php">mainmenu</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

