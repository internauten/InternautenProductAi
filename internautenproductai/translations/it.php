<?php

global $_MODULE;
$_MODULE = array();

$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Internauten Product AI')] = 'Internauten Product AI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Erzeugt per ChatGPT eine Produktbeschreibung direkt im PrestaShop-Admin.')] = 'Genera una descrizione prodotto con ChatGPT direttamente nel pannello di amministrazione di PrestaShop.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die Einstellungen wurden gespeichert.')] = 'Le impostazioni sono state salvate.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI-Einstellungen')] = 'Impostazioni OpenAI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI API Key')] = 'Chiave API OpenAI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Trage hier deinen OpenAI API Key ein.')] = 'Inserisci qui la tua chiave API OpenAI.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Modell')] = 'Modello';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Empfohlen: gpt-4o-mini')] = 'Consigliato: gpt-4o-mini';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Temperatur')] = 'Temperatura';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Standardwert: 0.7. Für neuere Modelle kann der Wert leer gelassen werden.')] = 'Opzionale. Valore predefinito: 0,7. Per i modelli più recenti puoi lasciare il campo vuoto.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Max Tokens')] = 'Max token';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Beispiel: 600. Manche Modelle nutzen stattdessen max_completion_tokens.')] = 'Opzionale. Esempio: 600. Alcuni modelli usano invece max_completion_tokens.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Top P')] = 'Top P';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Werte zwischen 0 und 1.')] = 'Opzionale. Valori tra 0 e 1.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Reasoning Effort')] = 'Sforzo di ragionamento';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Beispiel: low, medium oder high.')] = 'Opzionale. Esempio: low, medium o high.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Zusätzliche Parameter')] = 'Parametri aggiuntivi';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optionales JSON-Objekt für zusätzliche Modellparameter, z. B. {"max_completion_tokens": 800, "reasoning": {"effort": "medium"}}.')] = 'Oggetto JSON opzionale per parametri aggiuntivi del modello, ad esempio {"max_completion_tokens": 800, "reasoning": {"effort": "medium"}}.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die zusätzlichen Parameter müssen als gültiges JSON-Objekt angegeben werden.')] = 'I parametri aggiuntivi devono essere forniti come oggetto JSON valido.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('System-Prompt')] = 'Prompt di sistema';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Definiert Rolle, Stil und Verhalten des Modells.')] = 'Definisce ruolo, stile e comportamento del modello.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Prompt-Vorlage')] = 'Modello di prompt';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Platzhalter: {{product_name}}, {{category}} (Standardkategorie), {{brand}} (Destillerie), {{region}} (Region), {{age}} (Alter), {{abv}} (VOL %), {{volume}} (Inhalt), {{vintage}} (Jahrgang), {{bottler}} (Abfüller). Nicht vorhandene Werte bleiben leer.')] = 'Segnaposto: {{product_name}}, {{category}} (categoria predefinita), {{brand}} (distilleria), {{region}} (regione), {{age}} (età), {{abv}} (VOL %), {{volume}} (contenuto), {{vintage}} (annata), {{bottler}} (imbottigliatore). I valori mancanti restano vuoti.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Speichern')] = 'Salva';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Mit ChatGPT generieren')] = 'Genera con ChatGPT';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Beschreibung wird erstellt...')] = 'Generazione della descrizione...';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Bitte zuerst einen Artikelnamen eintragen.')] = 'Inserisci prima il nome del prodotto.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die Beschreibung konnte nicht generiert werden.')] = 'Non è stato possibile generare la descrizione.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Fehler bei der Generierung.')] = 'Errore durante la generazione.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Der Server hat keine gültige JSON-Antwort geliefert:')] = 'Il server non ha restituito una risposta JSON valida:';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('leere Antwort')] = 'risposta vuota';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Es ist kein OpenAI API Key hinterlegt.')] = 'Non è stata configurata alcuna chiave API OpenAI.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die PHP-cURL-Erweiterung ist auf dem Server nicht aktiviert.')] = 'L\'estensione PHP cURL non è attivata sul server.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI konnte nicht erreicht werden: ')] = 'Impossibile contattare OpenAI: ';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Unbekannter API-Fehler.')] = 'Errore API sconosciuto.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI hat keine Beschreibung zurückgegeben.')] = 'OpenAI non ha restituito alcuna descrizione.';

$_MODULE['<{internautenproductai}prestashop>admininternautenproductaigeneratecontroller_' . md5('Das Modul konnte nicht geladen werden.')] = 'Impossibile caricare il modulo.';
$_MODULE['<{internautenproductai}prestashop>admininternautenproductaigeneratecontroller_' . md5('Der Produktname fehlt.')] = 'Manca il nome del prodotto.';

$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Ungültiges Sicherheitstoken. Bitte die Admin-Seite neu laden.')] = 'Token di sicurezza non valido. Ricarica la pagina di amministrazione.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Ungültige Aktion.')] = 'Azione non valida.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Der Produktname fehlt.')] = 'Manca il nome del prodotto.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Das Modul konnte nicht geladen werden.')] = 'Impossibile caricare il modulo.';
