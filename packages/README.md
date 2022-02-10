# Alpatech Package Manager - Pacchetti di aggiornamento / estensione

La struttura del path per i download è:

  repoInfo['url']."/dists/".$repoInfo['ver']."/".$repoInfo['section']."/versionlist.xml";

In particolare

* http://gnujiko.alpatech.it/dists/10.1/main/info/index.php - fornisce un elenco (xml) dei pacchetti disponibili e delle dipendenze
* http://gnujiko.alpatech.it/dists/10.1/main/updateversionlist.php - forza il refresh delle cache sul server (?)
* http://gnujiko.alpatech.it/dists/10.1/main/versionlist.xml - fornisce un elenco (xml) delle versioni dei pacchetti disponibili sul repo
* http://gnujiko.alpatech.it/dists/10.1/main/getfile.php?package=XXXX&account=YYYYY&token=ZZZZZZ - scarica un pacchetto XXXXX usando l'account YYYYYY e il token ZZZZZZ (solo utenti registrati)

NB Mancano alcuni pacchetti e non tutti quelli qui presenti sono all'ultima versione disponibile. Si accettanno PR per integrazioni/aggiornamenti.