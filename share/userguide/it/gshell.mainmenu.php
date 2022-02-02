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
    0 => 'gshell.mainmenu.php',
    1 => 'mainmenu',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.ls.php',
    1 => 'ls',
  ),
  'next' =>
  array (
    0 => 'gshell.man.php',
    1 => 'man',
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
<h3>mainmenu</h3> <p>Gestisce il menu principale di Gnujiko.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>mainmenu</strong> <u>AZIONE</u> [<u>opzioni</u>]...<u><br /> </u></p> <p>&nbsp;</p> <h4><strong>AZIONI</strong></h4> <ul><li><a href="#mainmenu-insert">insert</a></li><li><a href="#mainmenu-edit">edit</a></li><li><a href="#mainmenu-delete">delete</a></li><li><a href="#mainmenu-list">list</a></li></ul> <p>&nbsp;</p> <p>&nbsp;</p> <h3><a name="mainmenu-insert">mainmenu insert</a></h3> <p>Inserisce un elemento nel menu principale.<br />Per inserire un elemento nella barra delle applicazioni &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>mainmenu</strong> <strong>insert</strong> -name <u>NAME</u> -url <span style="text-decoration: underline;">URL</span> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4>OPZIONI</h4> <p>Il comando <strong>mainmenu</strong> <strong>insert</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>-name</strong> <span style="text-decoration: underline;">NAME</span></p> <p style="margin-left: 80px;">Specifica un nome da assegnare al nuovo elemento.</p> <p style="margin-left: 40px;"><strong>-url</strong> <u>URL</u></p> <p style="margin-left: 80px;">Inserire un URL valido.</p><p style="margin-left: 40px;"><strong>-icon</strong> <u>ICON&nbsp;FILE</u></p><p style="margin-left: 80px;">Indicare il percorso completo dell&lsquo;icona da assegnare al nuovo elemento.<br />Di norma le icone devono essere di (24, 36, 48 pixels).</p><p style="margin-left: 40px;"><strong>-large-icon</strong> <u>ICON FILE</u></p><p style="margin-left: 80px;">In alcuni template &egrave; possibile visualizzare il menu principale con le icone grandi (64px o 128px).<br />Specificare quindi il percorso completo dell&lsquo;icona grande da assegnare al nuovo elemento; se omesso verr&agrave; utilizzata l&lsquo;icona piccola che ridimensionata, per&ograve;, apparir&agrave; inevitabilmente un po sgranata.</p> <p style="margin-left: 40px;"><strong>-group</strong> <u>GROUPNAME</u></p> <p style="margin-left: 80px;">Se specificato, il link sar&agrave; accessibile solo agli utenti membri di quel gruppo.</p> <p style="margin-left: 40px;"><strong>-perms</strong> <u>MODE</u></p> <p style="margin-left: 80px;">Imposta i permessi di accesso a quel bottone. Di default &egrave; 444.</p> <p style="margin-left: 40px;"><strong>-ordering</strong> <u>NUMBER</u></p> <p style="margin-left: 80px;">Imposta la posizione del link. Es: 0 = in prima posizione, 1&nbsp;=&nbsp;in seconda, 2&nbsp;= in terza posizione, ecc...<br /> Se non viene specificato questo parametro il sistema lo accoda (in ultima posizione).</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>id</strong> - ID del bottone.<br /> <strong>name</strong> - Nome del bottone.<br /> <strong>url</strong> - File loader.<br /><strong>icon</strong> - Percorso completo dell&lsquo;icona piccola.<br /><strong>largeicon</strong> - Percorso completo dell&lsquo;icona grande.<br /> <strong>ordering</strong> - Posizione del link nella lista.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per inserire elementi nel menu principale &egrave; necessario avere i privilegi di amministratore (root).<br /><br /> <strong>INVALID_NAME</strong> - Hai dimenticato di assegnare un nome a questo elemento.<br /><br /> <strong>INVALID_URL</strong> - E&lsquo; necessario specificare un URL valido per questo elemento.</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <div class="indent"><dl><dt>Attenzione:</dt><dd>Per inserire elementi al menu principale &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo mainmenu insert -name &quot;my app&quot; -url myapp/index.php</tt><br /><br /><tt>sudo mainmenu insert -name &quot;my app&quot; -url myapp/index.php -icon myapp/img/icon.png -large-icon myapp/img/bigicon.png -group admin -perms 440</tt><br /> <strong><br /> </strong></p></div> <p>&nbsp;</p> <hr /> <p>&nbsp;</p> <h3><a name="mainmenu-edit">mainmenu edit</a></h3> <p>Modifica un elemento del menu principale.<br />Per modificare un elemento del menu principale &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>mainmenu edit</strong> -id <u>ITEM ID</u> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>mainmenu</strong> <strong>edit</strong> accetta le seguenti opzioni.</p> <p style="margin-left: 40px;"><strong>-id</strong> <u>ITEM ID</u></p> <p style="margin-left: 80px;">Specificare l&lsquo;ID del elemento.</p> <p style="margin-left: 40px;"><strong>-name</strong> <u>NEW NAME</u></p> <p style="margin-left: 80px;">Rinomina l&lsquo;elemento.</p> <p style="margin-left: 40px;"><strong>-url</strong> <span style="text-decoration: underline;">URL</span></p> <p style="margin-left: 80px;">Modifica l&lsquo;URL a cui punta l&lsquo;elemento.</p><p style="margin-left: 40px;"><strong>-icon</strong> <u>ICON&nbsp;FILE</u></p><p style="margin-left: 80px;">Modifica il path relativo all&lsquo;icona piccola.</p><p style="margin-left: 40px;"><strong>-large-icon</strong> <u>ICON FILE</u></p><p style="margin-left: 80px;">Modifica il path relarivo all&lsquo;icona grande.</p> <p style="margin-left: 40px;"><strong>-ordering</strong> <u>POSITION</u></p> <p style="margin-left: 80px;">Specifica in quale posizione si vuole l&lsquo;elemento. 0&nbsp;=&nbsp;prima posizione, 1&nbsp;=&nbsp;seconda, 2&nbsp;=&nbsp;terza, ecc...</p> <p style="margin-left: 40px;"><strong>-published</strong> 0 | 1</p> <p style="margin-left: 80px;">Inserire <strong>1</strong> per rendere visibile l&lsquo;elemento, <strong>0</strong> per nasconderlo.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Il comando <strong>mainmenu</strong> <strong>edit</strong> non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per modificare gli elementi del menu principale &egrave; necessario avere i privilegi di amministratore (root).<br /> <br /> <strong>INVALID_ITEM</strong> - Hai dimenticato di specificare l&lsquo;ID dell&lsquo;elemento da modificare.</p> <p>&nbsp;</p> <h4>ESEMPI</h4> <div class="indent"><dl><dt>Attenzione:</dt><dd>Per modificare gli elementi del menu principale &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo mainmenu edit -id 2 -name &quot;my application&quot;</tt><br /> <tt>sudo mainmenu edit -id 2 -perms 400</tt><br /> <strong><br /> </strong></p></div> <p>&nbsp;</p><hr /><p>&nbsp;</p>  <h3><a name="mainmenu-delete">mainmenu delete</a></h3> <p>Rimuove un elemento dal menu principale.<br />Per rimuovere un elemento dal menu principale &egrave; necessario avere i privilegi di amministratore (root).</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>mainmenu</strong> <strong>delete</strong> -id <u>ITEM&nbsp;ID</u><br /> <strong>mainmenu</strong> <strong>delete</strong> -name <u>ITEM NAME</u></p> <p>&nbsp;</p> <h4>DESCRIZIONE</h4> <p>Per rimuovere un elemento dovete specificare il suo ID (-id) oppure il suo nome (-name).</p><p>&nbsp;</p> <h4>OUTPUT</h4> <p>Il comando <strong>mainmenu</strong> <strong>delete</strong> non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.</p> <p>&nbsp;</p> <h4>ERRORI</h4><p style="margin-left: 40px;"><strong>YOU_MUST_BE_ROOT</strong> - Per rimuovere gli elementi dal menu principale &egrave; necessario avere i privilegi di amministratore (root).<br /> <br /> <strong>INVALID_ITEM</strong> - Hai dimenticato di specificare l&lsquo;ID dell&lsquo;elemento da rimuovere.<br /><br /><strong>ITEM_DOES_NOT_EXISTS</strong> - L&lsquo; elemento che hai specificato non esiste.</p><p>&nbsp;</p><h4>ESEMPI</h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per rimuovere gli elementi dal menu principale &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo mainmenu delete -id 2</tt><br /> <tt>sudo mainmenu delete -name &quot;my app&quot;</tt><br /> <strong><br /> </strong></p></div><p>&nbsp;</p><hr /><p>&nbsp;</p>  <h3><a name="mainmenu-list">mainmenu list</a></h3> <p>Mostra la lista degli elementi del menu principale.</p> <p>&nbsp;</p> <h4>SINOSSI</h4> <p style="margin-left: 40px;"><strong>mainmenu</strong> <strong>list</strong> [<u>opzioni</u>]</p> <p>&nbsp;</p> <h4>OPZIONI</h4> <p>Il comando <span style="font-weight: bold;">mainmenu</span><strong> list</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>--order-by</strong> <u>FIELD&nbsp;AND&nbsp;METHOD</u></p> <p style="margin-left: 80px;">Mostra la lista ordinata in base ai criteri specificati.<br /> Le possibili modalit&agrave; sono:</p> <table cellspacing="1" cellpadding="1" border="0" style="margin-left: 80px; width: 580px; height: 107px;">     <tbody>         <tr>             <td>&nbsp;&quot;<tt>name ASC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine alfabetico dalla A alla Z.</td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>name DESC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine alfabetico dalla Z alla A.</td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>ordering ASC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine di inserimento/manuale. <em>(default)</em></td>         </tr>         <tr>             <td>&nbsp;&quot;<tt>ordering DESC</tt>&quot;</td>             <td>&nbsp;Elenca in ordine di inserimento/manuale,<br />&nbsp;dall&lsquo;ultimo al primo.</td>         </tr>     </tbody> </table> <p style="margin-left: 80px;">Negli argomenti specificate la modalit&agrave; racchiusa tra virgolette &quot; &quot;.</p> <p style="margin-left: 40px;"><strong>--include-unpublished</strong></p><p style="margin-left: 80px;">Se specificato, verranno inclusi nella lista anche gli elementi non pubblicati.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Il comando <strong>mainmenu</strong> <strong>list</strong> ritorna un array di informazioni:</p><p style="margin-left: 40px;"><strong>id</strong> - ID&nbsp;dell&lsquo;elemento.<br /><strong>name</strong> - Nome dell&lsquo;elemento.<br /><strong>url</strong> - URL dell&lsquo;elemento.<br /><strong>icon</strong> - Percorso completo dell&lsquo;icona.<br /><strong>largeicon</strong> - Percorso completo dell&lsquo;icona grande.<br /><strong>published</strong> - 1=pubblicato , 0=non pubblicato.</p><p>&nbsp;</p><h4>ESEMPI</h4><p style="margin-left: 40px;"><tt>mainmenu list --order-by &quot;name ASC&quot;<br />mainmenu list --order-by &quot;ordering DESC&quot; --include-unpublished<br /></tt></p><p>&nbsp;</p> <hr /> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/mainmenu.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.dockbar.php">dockbar</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p>

<?php manual_footer();

