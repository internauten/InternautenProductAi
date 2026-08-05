<?php

global $_MODULE;
$_MODULE = array();

$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Internauten Product AI')] = 'Internauten Product AI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Erzeugt per ChatGPT eine Produktbeschreibung direkt im PrestaShop-Admin.')] = 'Génère une description produit avec ChatGPT directement dans l\'administration PrestaShop.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die Einstellungen wurden gespeichert.')] = 'Les paramètres ont été enregistrés.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI-Einstellungen')] = 'Paramètres OpenAI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI API Key')] = 'Clé API OpenAI';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Trage hier deinen OpenAI API Key ein.')] = 'Saisissez ici votre clé API OpenAI.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Modell')] = 'Modèle';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Empfohlen: gpt-4o-mini')] = 'Recommandé : gpt-4o-mini';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Temperatur')] = 'Température';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Standardwert: 0.7. Für neuere Modelle kann der Wert leer gelassen werden.')] = 'Optionnel. Valeur par défaut : 0,7. Pour les modèles récents, vous pouvez laisser ce champ vide.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Max Tokens')] = 'Max tokens';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Beispiel: 600. Manche Modelle nutzen stattdessen max_completion_tokens.')] = 'Optionnel. Exemple : 600. Certains modèles utilisent plutôt max_completion_tokens.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Top P')] = 'Top P';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Werte zwischen 0 und 1.')] = 'Optionnel. Valeurs comprises entre 0 et 1.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Reasoning Effort')] = 'Effort de raisonnement';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optional. Beispiel: low, medium oder high.')] = 'Optionnel. Exemple : low, medium ou high.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Zusätzliche Parameter')] = 'Paramètres supplémentaires';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Optionales JSON-Objekt für zusätzliche Modellparameter, z. B. {"max_completion_tokens": 800, "reasoning": {"effort": "medium"}}.')] = 'Objet JSON optionnel pour des paramètres de modèle supplémentaires, par exemple {"max_completion_tokens": 800, "reasoning": {"effort": "medium"}}.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die zusätzlichen Parameter müssen als gültiges JSON-Objekt angegeben werden.')] = 'Les paramètres supplémentaires doivent être fournis sous forme d’un objet JSON valide.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('System-Prompt')] = 'Prompt système';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Definiert Rolle, Stil und Verhalten des Modells.')] = 'Définit le rôle, le style et le comportement du modèle.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Prompt-Vorlage')] = 'Modèle de prompt';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Platzhalter: {{product_name}}, {{category}} (Standardkategorie), {{brand}} (Destillerie), {{region}} (Region), {{age}} (Alter), {{abv}} (VOL %), {{volume}} (Inhalt), {{vintage}} (Jahrgang). Nicht vorhandene Werte bleiben leer.')] = 'Variables : {{product_name}}, {{category}} (catégorie par défaut), {{brand}} (distillerie), {{region}} (région), {{age}} (âge), {{abv}} (degré d\'alcool), {{volume}} (contenance), {{vintage}} (millésime). Les valeurs absentes restent vides.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Speichern')] = 'Enregistrer';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Mit ChatGPT generieren')] = 'Générer avec ChatGPT';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Beschreibung wird erstellt...')] = 'Génération de la description...';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Bitte zuerst einen Artikelnamen eintragen.')] = 'Veuillez d\'abord saisir un nom de produit.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die Beschreibung konnte nicht generiert werden.')] = 'La description n\'a pas pu être générée.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Fehler bei der Generierung.')] = 'Erreur lors de la génération.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Der Server hat keine gültige JSON-Antwort geliefert:')] = 'Le serveur n\'a pas renvoyé de réponse JSON valide :';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('leere Antwort')] = 'réponse vide';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Es ist kein OpenAI API Key hinterlegt.')] = 'Aucune clé API OpenAI n\'est configurée.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Die PHP-cURL-Erweiterung ist auf dem Server nicht aktiviert.')] = 'L\'extension PHP cURL n\'est pas activée sur le serveur.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI konnte nicht erreicht werden: ')] = 'Impossible de joindre OpenAI : ';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('Unbekannter API-Fehler.')] = 'Erreur API inconnue.';
$_MODULE['<{internautenproductai}prestashop>internautenproductai_' . md5('OpenAI hat keine Beschreibung zurückgegeben.')] = 'OpenAI n\'a renvoyé aucune description.';

$_MODULE['<{internautenproductai}prestashop>admininternautenproductaigeneratecontroller_' . md5('Das Modul konnte nicht geladen werden.')] = 'Le module n\'a pas pu être chargé.';
$_MODULE['<{internautenproductai}prestashop>admininternautenproductaigeneratecontroller_' . md5('Der Produktname fehlt.')] = 'Le nom du produit est manquant.';

$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Ungültiges Sicherheitstoken. Bitte die Admin-Seite neu laden.')] = 'Jeton de sécurité invalide. Veuillez recharger la page d\'administration.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Ungültige Aktion.')] = 'Action invalide.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Der Produktname fehlt.')] = 'Le nom du produit est manquant.';
$_MODULE['<{internautenproductai}prestashop>ajax_' . md5('Das Modul konnte nicht geladen werden.')] = 'Le module n\'a pas pu être chargé.';
