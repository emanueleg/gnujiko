<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2016
 #PACKAGE: companyprofile-config-language-pack-it
 #DESCRIPTION: Italian translation file for companyprofile-config.
 #VERSION: 2.4beta
 #CHANGELOG: 17-12-2016 : Aggiunto voce discount su pricelists.
			 02-05-2016 : Aggiunto voci di nuove classi IVA.
			 18-12-2012 : Cash resources included.
			 29-11-2012 : Aggiunto magazzini.
 #TODO:
 
*/

global $_DICTIONARY;

$_DICTIONARY["Company Profile"] = "Profilo Aziendale";
$_DICTIONARY["Enter information for your business, custom letterheads, etc."] = "Inserisci i dati relativi alla tua azienda, personalizza carte intestate, ecc.";
$_DICTIONARY["Create a business profile"] = "Creare un profilo aziendale";
$_DICTIONARY["Profile updated"] = "Profilo aggiornato";

/* share/widgets/config.companyprofile.php */
$_DICTIONARY["Company profile configuration"] = "Configurazione del profilo aziendale";
$_DICTIONARY["Generality"] = "Generalità";
$_DICTIONARY["Addresses"] = "Sedi / Indirizzi";
$_DICTIONARY["Accounting"] = "Contabilità";
$_DICTIONARY["Banks"] = "Conti correnti";
$_DICTIONARY["VAT rates"] = "Aliquote IVA";
$_DICTIONARY["Pricelists"] = "Listini";

/* share/widgets/config-companyprofile/generality.php */
$_DICTIONARY["Company name:"] = "Denominazione:";
$_DICTIONARY["Legal representative:"] = "Legale rappresentante:";
$_DICTIONARY["Tax code:"] = "Codice fiscale:";
$_DICTIONARY["VAT number:"] = "Partita IVA:";
$_DICTIONARY["R.E.A.:"] = "R.E.A.:";
$_DICTIONARY["Company code (ATECOFIN):"] = "Codice attività (ATECOFIN):";
$_DICTIONARY["Web site:"] = "Sito web:";
$_DICTIONARY["Company logo:"] = "Logo aziendale:";
$_DICTIONARY["Abort"] = "Annulla";
$_DICTIONARY["Apply"] = "Applica";
$_DICTIONARY["Save and close"] = "Salva e chiudi";
$_DICTIONARY["Invalid file format. Only these extensions are allowed: jpg, bmp, png and gif."] = "Formato dell'immagine non valido. I tipi di file supportati sono: jpg, bmp, png e gif.";
$_DICTIONARY["The changes have been made with success!"] = "Le modifiche sono state apportate con successo!";

/* share/widgets/config-companyprofile/addresses.php */
$_DICTIONARY["Registered office"] = "Sede legale";
$_DICTIONARY["ADDRESS"] = "INDIRIZZO";
$_DICTIONARY["Address:"] = "Indirizzo:";
$_DICTIONARY["City:"] = "Città:";
$_DICTIONARY["Zip:"] = "C.A.P.:"; // zip code //
$_DICTIONARY["Prov.:"] = "Prov.:";
$_DICTIONARY["Country:"] = "Paese:";
$_DICTIONARY["CONTACTS"] = "RECAPITI";
$_DICTIONARY["Add phone"] = "Aggiungi telefono";
$_DICTIONARY["Title: (eg: Business, Home, ...)"] = "titolo: (es: Ufficio, Casa, ...)";
$_DICTIONARY["insert number"] = "inserisci numero";
$_DICTIONARY["Add fax"] = "Aggiungi fax";
$_DICTIONARY["Title: (eg: Office Fax)"] = "titolo: (es: Fax Ufficio)";
$_DICTIONARY["Add cell phone"] = "Aggiungi cellulare";
$_DICTIONARY["title: (eg: Mario cell phone)"] = "titolo: (es: Cellulare Mario)";
$_DICTIONARY["Add email"] = "Aggiungi email";
$_DICTIONARY["title: (eg: Administration)"] = "titolo: (es: Amministrazione)";
$_DICTIONARY["enter email"] = "inserisci email";
$_DICTIONARY["no address provided"] = "nessun recapito fornito";
$_DICTIONARY["Operating Location / Headquarters"] = "Ubicazione esercizio / Sede principale";
$_DICTIONARY["Other locations"] = "Altre sedi";
$_DICTIONARY["add new<br/>location"] = "aggiungi<br/>nuova sede";
$_DICTIONARY["Name:"] = "Denominazione:";
$_DICTIONARY["Are you sure you want to remove this phone number?"] = "Sei sicuro di voler rimuovere questo numero di telefono?";
$_DICTIONARY["Are you sure you want to remove this fax number?"] = "Sei sicuro di voler rimuovere questo numero di fax?";
$_DICTIONARY["Are you sure you want to remove this cell phone number?"] = "Sei sicuro di voler rimuovere questo numero di cellulare?";
$_DICTIONARY["Are you sure you want to remove this email?"] = "Sei sicuro di voler rimuovere questa email?";
$_DICTIONARY["Are you sure you want to remove this location?"] = "Sei sicuro di voler rimuovere questa sede?";
$_DICTIONARY["Saved!"] = "Salvataggio completato!";
$_DICTIONARY["copy from Registered office"] = "copia dalla Sede legale";

/* share/widgets/config-companyprofile/accounting.php */
$_DICTIONARY["Accounting regime:"] = "Regime contabile:";
$_DICTIONARY["simplified"] = "semplificato";
$_DICTIONARY["ordinary"] = "ordinario";
$_DICTIONARY["VAT payment frequency:"] = "Periodicità liquidazione IVA:";
$_DICTIONARY["monthly"] = "mensile";
$_DICTIONARY["quarterly"] = "trimestrale";
$_DICTIONARY["Interest on quarterly VAT:"] = "Interessi su IVA trimestrale:";
$_DICTIONARY["N. decimal pricing:"] = "N. decimali sui prezzi:";
$_DICTIONARY["VAT rate most frequently used:"] = "Aliquota usata più di frequente:";
$_DICTIONARY["Tax regime:"] = "Regime fiscale:";
$_DICTIONARY["Percentage of tax payment:"] = "Percentuale d'acconto IVA:";
$_DICTIONARY["Rate of the VAT payable."] = "Percentuale dell'acconto IVA da versare.";
$_DICTIONARY["Amount of stamp duty on receipts:"] = "Importo bollo su ricevute:";
$_DICTIONARY["Amount of stamp duty on receipts."] = "Importo del bollo sulle ricevute di pagamento.";
$_DICTIONARY["Rate of stamp duty on routes:"] = "Aliquota imposta di bollo su tratte:";
$_DICTIONARY["Percentage of stamp duty on the routes."] = "Percentuale dell'imposta di bollo sulle tratte.";
$_DICTIONARY["Rounding stamps:"] = "Arrotondamento bolli:";
$_DICTIONARY["tenths"] = "decimi";
$_DICTIONARY["cents"] = "centesimi";
$_DICTIONARY["thousandths"] = "millesimi";
$_DICTIONARY["Riba collection costs. to be charged:"] = "Spese incasso Ri.Ba. da addebitare:";
$_DICTIONARY["These are the costs of collection to be charged for each type of Ri.Ba."] = "Si tratta delle spese di incasso da addebitare per ogni tipo di effetto.";
$_DICTIONARY["For the ordinary management of the accounting you need to install the package:"] = "Per la gestione della contabilità ordinaria è necessario installare il pacchetto:";
$_DICTIONARY["Install"] = "Installa";

/* share/widgets/config-companyprofile/banks.php */
$_DICTIONARY["add new<br/>bank"] = "aggiungi<br/>nuova banca";
$_DICTIONARY["Bank:"] = "Banca:";
$_DICTIONARY["Holder:"] = "Intestatario:";
$_DICTIONARY["ACCOUNT DETAILS"] = "DETTAGLI CONTO";
$_DICTIONARY["ABI"] = "ABI";
$_DICTIONARY["CAB"] = "CAB";
$_DICTIONARY["C/A"] = "C/C";
$_DICTIONARY["IBAN"] = "IBAN";
$_DICTIONARY["Start balance:"] = "Bilancio di partenza:";
$_DICTIONARY["Current balance:"] = "Bilancio attuale:";
$_DICTIONARY["BANK CONTACTS"] = "RECAPITI BANCA";
$_DICTIONARY["Are you sure you want to remove this bank?"] = "Sei sicuro di voler rimuovere questa banca?";

/* share/widgets/config-companyprofile/vatrates.php */
$_DICTIONARY["CODE"] = "CODICE";
$_DICTIONARY["DESCRIPTION"] = "DESCRIZIONE";
$_DICTIONARY["TYPE"] = "TIPO";
$_DICTIONARY["PERCENTAGE"] = "PERCENTUALE";
$_DICTIONARY["Taxable"] = "Imponibile";
$_DICTIONARY["Not taxable"] = "Non imponibile";
$_DICTIONARY["Free"] = "Esente";
$_DICTIONARY["Excluding"] = "Escluso";
$_DICTIONARY["Not subject"] = "Non soggetto";
$_DICTIONARY["Not deductible"] = "Indetraibile";
$_DICTIONARY["Purch. extra-EUR"] = "Acq. extra-UE";
$_DICTIONARY["Purch. in-EUR"] = "Acq. intra-UE";
$_DICTIONARY["Purch. rev. charge"] = "Acq. rev. charge";

/* share/widgets/config-companyprofile/pricelist.php */
$_DICTIONARY["NAME"] = "NOME";
$_DICTIONARY["MARKUP"] = "RICARICO";
$_DICTIONARY["DISCOUNT"] = "SCONTO";
$_DICTIONARY["VAT"] = "IVA";

/* share/widgets/config-companyprofile/chartofaccounts.php */
$_DICTIONARY["Chart of accounts"] = "Piano dei conti";

/* share/widgets/config-companyprofile/stores.php */
$_DICTIONARY["Stores"] = "Magazzini";

/* share/widgets/config-companyprofile/cashresources.php */
$_DICTIONARY["Resources"] = "Risorse";
$_DICTIONARY["Cash"] = "Cassa";
$_DICTIONARY["Bank"] = "Banca";
$_DICTIONARY["Creditcard"] = "Carta di credito";

