# Internauten Product AI

![Sponsor](https://img.shields.io/badge/Sponsoring-Welcome-f5b301?style=for-the-badge)
![AI](https://img.shields.io/badge/AI-OpenAI%20%2F%20ChatGPT-412991?style=for-the-badge)
![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%2B%20%7C%209.x-DF0067?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Dieses PrestaShop-Modul ergänzt im Produkt-Admin einen Button, mit dem anhand des Artikelnamens automatisch eine Produktbeschreibung über OpenAI/ChatGPT erzeugt und direkt in das Beschreibungsfeld eingefügt wird.

## Funktionen

- Button direkt am Beschreibungstext im Produkt-Admin
- Generierung auf Basis des Produktnamens
- Batch-Generierung für mehrere ausgewählte Produkte in der Modul-Konfiguration
- Serverseitige Produktsuche (Name, Referenz, Produkt-ID) für große Kataloge
- OpenAI API Key, Modell und Prompt im Modul konfigurierbar
- HTML-Ausgabe für die direkte Nutzung in PrestaShop

## Installation

1. Den Ordner `internautenproductai` als ZIP packen oder direkt in `modules/internautenproductai` hochladen.
2. Im PrestaShop-Backoffice das Modul installieren.
3. Unter **Module > Internauten Product AI > Konfigurieren** den OpenAI API Key eintragen.
4. Ein Produkt öffnen und den Button **Mit ChatGPT generieren** nutzen oder die Batch-Funktion in der Modul-Konfiguration verwenden.

## Batch-Funktion

In der Modul-Konfiguration steht ein Bereich **Bulk-Generierung für Produkte** zur Verfügung.

- Suche nach Produkten über Name, Referenz oder Produkt-ID
- Mehrfachauswahl inkl. **Alle sichtbaren auswählen** und **Auswahl leeren**
- Live-Anzeige, wie viele Produkte aktuell ausgewählt sind
- Verarbeitung aller ausgewählten Produkte in einem Lauf mit Ergebnisliste (Erfolg/Fehler pro Produkt)
- Pro verarbeitetem Produkt wird `description` durch den neu generierten Text ersetzt
- Pro verarbeitetem Produkt wird `description_short` mit dem ersten Absatz der generierten Beschreibung befüllt

Hinweis: Die Batch-Verarbeitung arbeitet in der aktuell im Backoffice ausgewählten Sprache.

## Hinweise

- Standardmodell: `gpt-4o-mini`
- Der Prompt kann im Modul angepasst werden.
- Bestehender Beschreibungstext wird bei der Generierung ersetzt.
- Der erste Absatz der generierten Beschreibung wird zusätzlich in die Kurzbeschreibung übernommen.

## Release Tagging

GitHub Releases are created automatically when you push a tag in this format:

- `vX.X.X` (example: `v1.1.2`)

The repository includes a helper script that reads the version from `internautenproductai/internautenproductai.php`, creates the matching tag and pushes it automatically:

```bash
./scripts/tag-release.sh
```

If you only want to create the local tag without pushing it:

```bash
./scripts/tag-release.sh --local-only
```

The workflow then builds and uploads:

- internautenproductai-module-v1.1.2.zip

## Develope

Dammit die Container bei jedem neuen Modul nicht jedesmal neu erstellt werden müssen, versuchen wir es mit symlinks.

Voraussetzungen: im compose hat es unter volumes einen Eintrag - /home/dmo/internauten:/internauten

1. Bash ins WSL2 und holen des Repos aus dem fork
   ```bash
   cd ~/internauten
   git clone https://github.com/yourgithub/InternautenProductAi.git
   ```
2. set owner, goup and rights
   ```bash
   sudo chown -R www-data:www-data ~/internauten/InternautenProductAi/internautenproductai
   sudo chmod -R go+w ~/internauten/InternautenProductAi/internautenproductai
   ```
3. Bash in den Container und create symlink and set group:owner
   ```bash
   ln -s /internauten/InternautenProductAi/internautenproductai /var/www/html/modules/internautenproductai
   (chown -h www-data:www-data ~/InternautenProductAi/internautenproductai)
   chown -h www-data:www-data /var/www/html/modules/internautenproductai
   ```
4. Activate and configure Module in Prestashop  
   In Prestashop backend go to Module Manager / not installed Modules and install the module.

## License

This project is licensed under the MIT License. See details [`LICENSE`](LICENSE).

Copyright (c) 2026 die.internauten.ch GmbH
