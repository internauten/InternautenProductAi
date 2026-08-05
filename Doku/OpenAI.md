# Vorschläge von OpenAI

## Optimierter Systemprompt

Du bist ein professioneller E-Commerce-Texter mit sommelier-artigem Stil und Fokus auf hochwertige Spirituosen, insbesondere Whisky. Erstelle elegante, sensorische und glaubwuerdige HTML-Produktbeschreibungen fuer deutschsprachige Onlineshops.

Nutze ausschliesslich die bereitgestellten Produktinformationen. Wenn Details wie Herkunft, Alter, Fassreifung, Duft, Geschmack, Textur oder Nachklang nicht eindeutig aus den Daten oder dem Produktnamen hervorgehen, formuliere stilvoll und allgemein, ohne konkrete Fakten zu erfinden.

Die Sprache ist genussorientiert, praezise und hochwertig, aber nie uebertrieben oder pathetisch. Verwende Schweizer Doppel-S, also nie &szlig;. Kodiere Umlaute und Sonderzeichen in HTML-Entities, z. B. ä, ö, ü, ä, ö, ü. Antworte ausschliesslich mit sauberem HTML ohne Markdown-Codebloecke.

## Optimierte Promptvorlage

Erstelle fuer das Produkt "{{product_name}}" eine sommelier-artige Produktbeschreibung fuer einen deutschsprachigen Onlineshop mit Fokus auf Whisky und Premium-Spirituosen.

Falls vorhanden, nutze diese Zusatzinformationen:
Kategorie: {{category}}
Marke/Destillerie: {{brand}}
Herkunft/Region: {{region}}
Alter: {{age}}
Alkoholgehalt: {{abv}}
Fassreifung: {{cask}}
Inhalt: {{volume}}
Besonderheiten: {{features}}
Vorhandene Tasting Notes: {{tasting_notes}}

Inhalt:

- beschreibe das Produkt mit eleganter, sensorischer und genussorientierter Sprache
- gehe nur dann konkret auf Herkunft, Fassreifung, Duft, Geschmack, Mundgefuehl und Nachklang ein, wenn diese Informationen vorhanden oder eindeutig aus dem Produktnamen ableitbar sind
- stelle Charakter und Stil hochwertig dar, ohne erfundene Fakten zu ergaenzen
- vermittle, zu welchem Genussmoment oder Anlass der Whisky besonders gut passt
- schliesse den Beitrag mit etwas Speziellem zu dieser Abfuellung oder Serie, aber nur wenn es dazu eine belastbare Information gibt

Format:

- gib ausschliesslich sauberes HTML fuer die PrestaShop-Produktbeschreibung zurueck
- beginne mit einem atmosphaerischen Absatz in <p>
- ergaenze eine Zwischenueberschrift in <h2>, zum Beispiel Charakter, Verkostungsnotizen oder Genussprofil
- fuege 4 bis 6 praegnante Stichpunkte in einer <ul> mit <li>-Elementen ein
- schliesse mit einem kurzen, stilvollen Absatz zum Finish oder Genussmoment ab

Stil:

- sommelier-artig, praezise, hochwertig und vertrauenswuerdig
- bildhaft und genussvoll, aber nicht kitschig oder uebertrieben
- keine Fantasieangaben
- keine Hinweise auf KI
- keine Emojis
- keine Markdown-Codebloecke
- Schweizer Doppel-S verwenden, kein &
- Umlaute und Sonderzeichen HTML-codieren

## Zusatzfelder wenn vorhanden

product_id
product_name
category
brand
region
age
abv
cask
volume
features
tasting_notes

## Beispiel für eine Batch-Zeile

```json
{
  "custom_id": "whisky-0001",
  "method": "POST",
  "url": "/v1/responses",
  "body": {
    "model": "gpt-4.1-mini",
    "instructions": "Du bist ein professioneller E-Commerce-Texter mit sommelier-artigem Stil und Fokus auf hochwertige Spirituosen, insbesondere Whisky. Erstelle elegante, sensorische und glaubwuerdige HTML-Produktbeschreibungen fuer deutschsprachige Onlineshops. Nutze ausschliesslich die bereitgestellten Produktinformationen. Wenn Details wie Herkunft, Alter, Fassreifung, Duft, Geschmack, Textur oder Nachklang nicht eindeutig aus den Daten oder dem Produktnamen hervorgehen, formuliere stilvoll und allgemein, ohne konkrete Fakten zu erfinden. Die Sprache ist genussorientiert, praezise und hochwertig, aber nie uebertrieben oder pathetisch. Verwende Schweizer Doppel-S, also nie &szlig;. Kodiere Umlaute und Sonderzeichen in HTML-Entities, z. B. ä, ö, ü, ä, ö, ü. Antworte ausschliesslich mit sauberem HTML ohne Markdown-Codebloecke.",
    "input": "Erstelle fuer das Produkt \"Glenfiddich 12 Years Single Malt Scotch Whisky\" eine sommelier-artige Produktbeschreibung fuer einen deutschsprachigen Onlineshop mit Fokus auf Whisky und Premium-Spirituosen.\n\nFalls vorhanden, nutze diese Zusatzinformationen:\nKategorie: Single Malt Scotch Whisky\nMarke/Destillerie: Glenfiddich\nHerkunft/Region: Schottland, Speyside\nAlter: 12 Jahre\nAlkoholgehalt: 40%\nFassreifung: \nInhalt: 70 cl\nBesonderheiten: \nVorhandene Tasting Notes: \n\nInhalt:\n- beschreibe das Produkt mit eleganter, sensorischer und genussorientierter Sprache\n- gehe nur dann konkret auf Herkunft, Fassreifung, Duft, Geschmack, Mundgefuehl und Nachklang ein, wenn diese Informationen vorhanden oder eindeutig aus dem Produktnamen ableitbar sind\n- stelle Charakter und Stil hochwertig dar, ohne erfundene Fakten zu ergaenzen\n- vermittle, zu welchem Genussmoment oder Anlass der Whisky besonders gut passt\n- schliesse den Beitrag mit etwas Speziellem zu dieser Abfuellung oder Serie, aber nur wenn es dazu eine belastbare Information gibt\n\nFormat:\n- gib ausschliesslich sauberes HTML fuer die PrestaShop-Produktbeschreibung zurueck\n- beginne mit einem atmosphaerischen Absatz in <p>\n- ergaenze eine Zwischenueberschrift in <h2>, zum Beispiel Charakter, Verkostungsnotizen oder Genussprofil\n- fuege 4 bis 6 praegnante Stichpunkte in einer <ul> mit <li>-Elementen ein\n- schliesse mit einem kurzen, stilvollen Absatz zum Finish oder Genussmoment ab\n\nStil:\n- sommelier-artig, praezise, hochwertig und vertrauenswuerdig\n- bildhaft und genussvoll, aber nicht kitschig oder uebertrieben\n- keine Fantasieangaben\n- keine Hinweise auf KI\n- keine Emojis\n- keine Markdown-Codebloecke\n- Schweizer Doppel-S verwenden, kein &szlig;\n- Umlaute und Sonderzeichen HTML-codieren"
  }
}
```

## Wichtig für deine 1.000 Produkte

Du musst den Prompt nicht manuell 1.000-mal kopieren.
Du erstellst ein kleines Skript oder eine Exportlogik, die automatisch pro CSV-Zeile so einen Batch-Request erzeugt.

Prinzip:

- Systemprompt = immer gleich
- Promptvorlage = immer gleich
- Produktdaten = pro Whisky anders

# ich habe eine gtin13 info. kann das nützen

Ja, **GTIN-13 kann sehr nützlich sein** — aber nicht direkt als Textgrundlage.

Wichtig: Das Modell kann aus einer GTIN-13 **nicht automatisch und zuverlässig** ableiten, welcher Whisky es ist, welche Fassreifung er hat oder wie er schmeckt. Eine GTIN ist vor allem eine **eindeutige Produktkennung**, kein beschreibender Inhalt.

## Wofür GTIN-13 nützlich ist

### 1. Eindeutige Zuordnung der Ergebnisse

Du kannst die GTIN als `custom_id` oder zusätzliches Feld verwenden. So kannst du nach dem Batch die generierten Beschreibungen wieder sauber deinem Shop-Produkt zuordnen.

Beispiel:

```jsonl
{
  "custom_id": "gtin-5010327000176",
  "method": "POST",
  "url": "/v1/responses",
  "body": {
    "model": "gpt-4.1-mini",
    "instructions": "...",
    "input": "Erstelle eine HTML-Produktbeschreibung fuer das Produkt:\n\nGTIN-13: 5010327000176\nProduktname: Glenfiddich 12 Years Single Malt Scotch Whisky\n..."
  }
}
```

### 2. Dubletten erkennen

Bei 1.000 Whiskys kann es passieren, dass Produkte doppelt vorkommen, z. B. mit leicht abweichendem Namen:

```text
Glenfiddich 12 Years 70cl
Glenfiddich 12 Jahre Single Malt 0.7l
Glenfiddich 12 YO
```

Wenn die GTIN gleich ist, weisst du: Es ist sehr wahrscheinlich dasselbe Produkt.

### 3. Externe Produktdaten anreichern

Das ist der grösste Vorteil.

Du kannst die GTIN nutzen, um vor der Texterstellung Zusatzdaten aus anderen Quellen zu holen, z. B.:

- Lieferanten-Feed
- PIM-System
- ERP
- Herstellerdaten
- GS1-/Produktdatenbanken
- bestehende Shopdaten
- Merchant-/Katalogdaten

Dann hättest du nicht nur:

```text
GTIN: 5010327000176
Produktname: Glenfiddich 12 Years
```

sondern idealerweise:

```text
GTIN: 5010327000176
Produktname: Glenfiddich 12 Years Single Malt Scotch Whisky
Marke: Glenfiddich
Kategorie: Single Malt Scotch Whisky
Herkunft: Schottland, Speyside
Alter: 12 Jahre
Alkoholgehalt: 40%
Inhalt: 70 cl
Fassreifung: Bourbon- und Sherryfaesser
Tasting Notes: Birne, Eiche, Malz, sanfter Nachklang
```

Damit werden die Beschreibungen deutlich besser und faktisch sicherer.

## Was GTIN-13 nicht leisten sollte

Du solltest dem Modell nicht einfach sagen:

```text
Erstelle eine Beschreibung fuer GTIN 5010327000176.
```

Das ist zu wenig. Die GTIN allein reicht für eine gute Beschreibung nicht aus, ausser dein System macht vorher automatisch eine Produktdaten-Abfrage.

## Empfehlung für deinen Batch

Ich würde die GTIN in jedem Datensatz mitführen, aber nicht prominent in die Produktbeschreibung schreiben lassen.

Sinnvoller Aufbau:

```text
GTIN-13: {{gtin13}}
Produktname: {{product_name}}
Kategorie: {{category}}
Marke/Destillerie: {{brand}}
Herkunft/Region: {{region}}
Alter: {{age}}
Alkoholgehalt: {{abv}}
Inhalt: {{volume}}
Fassreifung: {{cask}}
Tasting Notes: {{tasting_notes}}
Besonderheiten: {{features}}
```

Und im Prompt ergänzen:

```text
Die GTIN-13 dient nur zur eindeutigen Produktzuordnung und soll nicht in der Beschreibung erwaehnt werden.
```

## Wichtig bei Excel/CSV

Speichere die GTIN-13 unbedingt als **Text**, nicht als Zahl. Sonst kann Excel Probleme machen, z. B. Formatierung, Rundung oder wissenschaftliche Schreibweise.

Also besser:

```csv
gtin13,product_name,brand,region
"5010327000176","Glenfiddich 12 Years Single Malt Scotch Whisky","Glenfiddich","Speyside"
```

## Kurz gesagt

Ja, GTIN-13 ist nützlich für:

- eindeutige Zuordnung
- Dublettenprüfung
- Datenanreicherung
- sauberen Re-Import in PrestaShop

Aber: **Für die eigentliche Beschreibung braucht das Modell Produktdaten.** Die GTIN allein sollte nicht als Inhaltsquelle verwendet werden.

# Welche öffentlichen datenbanken für die abfrage via gtin gibt es?

Ja, es gibt einige Datenbanken/APIs, über die du per **GTIN/EAN-13** Produktdaten abfragen kannst. Wichtig: Für Whisky ist die Abdeckung je nach Datenbank sehr unterschiedlich. Oft bekommst du Name, Marke, Kategorie, Inhalt oder Bild — aber selten zuverlässige Tasting Notes oder Fassdetails.

## Gute Anlaufstellen für GTIN-Abfragen

| Dienst                          |                  Typ |           API | Bemerkung                                                      |
| ------------------------------- | -------------------: | ------------: | -------------------------------------------------------------- |
| **Open Food Facts**             |      offen/kostenlos |            ja | Sehr gut für Lebensmittel/Getränke, teilweise auch Spirituosen |
| **Open Products Facts**         |      offen/kostenlos |            ja | Allgemeine Produktdatenbank, weniger vollständig               |
| **GS1 Verified by GS1 / GEPIR** |            offiziell | eingeschränkt | Offizielle GS1-Daten, meist Account/Vertrag nötig              |
| **UPCitemdb**                   | kommerziell/freemium |            ja | Breite Barcode-Datenbank, oft brauchbar                        |
| **Barcode Lookup**              |          kommerziell |            ja | Gute allgemeine Produktdaten, API meist kostenpflichtig        |
| **EAN-Search**                  |          kommerziell |            ja | Auf EAN/GTIN spezialisiert, kostenpflichtig                    |
| **Go-UPC**                      |          kommerziell |            ja | GTIN/UPC/EAN Lookup, API verfügbar                             |
| **Wikidata**                    |                offen |            ja | Kann einzelne Produkte enthalten, aber unvollständig           |

---

## 1. Open Food Facts

Für Whisky und Spirituosen würde ich zuerst **Open Food Facts** testen.

API-Beispiel:

```text
https://world.openfoodfacts.org/api/v2/product/5060044483776.json
```

Beispiel mit GTIN:

```text
https://world.openfoodfacts.org/api/v2/product/{{gtin13}}.json
```

Typische Felder:

- Produktname
- Marke
- Kategorie
- Menge/Inhalt
- Herkunftsangaben, falls vorhanden
- Bilder
- Zutaten/Nährwerte, bei Spirituosen oft weniger relevant

Vorteile:

- kostenlos
- offene API
- recht einfach nutzbar
- gute Quelle für Basisdaten

Nachteile:

- bei Whisky nicht immer vollständig
- Daten sind nutzergeneriert
- sensorische Beschreibung meist nicht vorhanden

API-Dokumentation:

```text
https://openfoodfacts.github.io/openfoodfacts-server/api/
```

---

## 2. Open Products Facts

Open Products Facts ist ein Schwesterprojekt für allgemeine Produkte.

Beispiel:

```text
https://world.openproductsfacts.org/api/v2/product/5060044483776.json
```

Kann nützlich sein, ist aber je nach Sortiment oft weniger gut gefüllt als Open Food Facts.

---

## 3. GS1 / Verified by GS1

GS1 ist die offizielle Organisation hinter GTINs. Theoretisch ist das die sauberste Quelle für Hersteller-/Markeninhaber-Daten.

Relevant sind:

```text
Verified by GS1
GEPIR
GS1 Registry Platform
```

Aber: Diese Dienste sind meist nicht einfach als offene kostenlose Massen-API nutzbar. Oft brauchst du:

- GS1-Account
- Vertrag
- API-Zugang
- ggf. Nutzungslizenz

Für einen professionellen Shop kann es sich trotzdem lohnen, vor allem zur Prüfung von:

- Markeninhaber
- Produktidentität
- GTIN-Validität
- Dubletten

Wichtig: Eine GTIN sagt nicht automatisch das Herstellungsland des Whiskys aus. Der GS1-Präfix zeigt nur die GS1-Vergabestelle bzw. Nummernorganisation, nicht zwingend die Herkunft des Produkts.

---

## 4. UPCitemdb

UPCitemdb ist eine bekannte Barcode-Datenbank mit API.

Website:

```text
https://www.upcitemdb.com/
```

API-Beispiel:

```text
https://api.upcitemdb.com/prod/trial/lookup?upc=5060044483776
```

Vorteile:

- einfache API
- oft Produktname, Marke, Beschreibung, Bilder
- teilweise kostenlose Test-/Trial-Nutzung

Nachteile:

- Limits
- kommerzielle Nutzung ggf. kostenpflichtig
- Datenqualität schwankt

---

## 5. Barcode Lookup

Website:

```text
https://www.barcodelookup.com/
```

API:

```text
https://www.barcodelookup.com/api
```

Vorteile:

- breite Produktabdeckung
- oft Bilder und Produktnamen
- kann für E-Commerce-Anreicherung nützlich sein

Nachteile:

- API normalerweise kostenpflichtig
- Nutzungsbedingungen beachten

---

## 6. EAN-Search

Website:

```text
https://www.ean-search.org/
```

Das ist eine auf EAN/GTIN spezialisierte Datenbank mit API-Angeboten.

Vorteile:

- auf europäische EANs ausgerichtet
- gut für Produktidentifikation

Nachteile:

- kostenpflichtig
- je nach Produktkategorie unterschiedliche Abdeckung

curl "https://api.ean-search.org/api?token=abcdef&op=barcode-lookup&format=json&ean=5060044483776"

---

## 7. Go-UPC

Website:

```text
https://go-upc.com/
```

Bietet ebenfalls Barcode-Lookups und API-Zugriff.

Vorteile:

- einfache API
- allgemeine Produktdaten
- oft Produktname, Marke, Kategorie, Bild

Nachteile:

- kostenpflichtig/freemium
- Whisky-spezifische Details nicht garantiert

---

## 8. Wikidata

Wikidata kann GTINs enthalten, ist aber für 1.000 Shopprodukte wahrscheinlich nur als Ergänzung interessant.

Abfrage ist über SPARQL möglich:

```text
https://query.wikidata.org/
```

Vorteile:

- offen
- maschinenlesbar
- gut für bekannte Marken/Produkte

Nachteile:

- sehr unvollständig für konkrete Shopartikel
- selten vollständige Handelsproduktdaten

---

## Empfohlener Workflow für deine Whisky-Daten

Ich würde die Quellen in dieser Reihenfolge testen:

1. **Open Food Facts**
2. **Open Products Facts**
3. **UPCitemdb**
4. **Barcode Lookup / EAN-Search / Go-UPC**
5. **Hersteller- oder Lieferantendaten**
6. **GS1**, falls offizieller Datenabgleich nötig ist

Der beste praktische Ansatz ist ein mehrstufiges Enrichment:

```text
GTIN-13
→ Produktdaten aus Datenbanken abfragen
→ Name, Marke, Inhalt, Kategorie, Region, Alkoholgehalt etc. normalisieren
→ Dubletten entfernen
→ OpenAI-Batch mit den angereicherten Daten erstellen
→ HTML-Beschreibungen generieren
→ Import in PrestaShop
```

---

## Beispiel: Open Food Facts per GTIN abfragen

Python-Minibeispiel:

```python
import requests

gtin = "5010327000176"

url = f"https://world.openfoodfacts.org/api/v2/product/{gtin}.json"
response = requests.get(url, timeout=10)
data = response.json()

if data.get("status") == 1:
    product = data["product"]
    print("Name:", product.get("product_name"))
    print("Marke:", product.get("brands"))
    print("Menge:", product.get("quantity"))
    print("Kategorien:", product.get("categories"))
else:
    print("Produkt nicht gefunden")
```

---

## Wichtig für Whisky-Beschreibungen

Nutze GTIN-Daten vor allem für:

- korrekten Produktnamen
- Marke/Destillerie
- Flaschengrösse
- Kategorie
- ggf. Alkoholgehalt
- Bild/Herstellerangaben
- Dublettenprüfung

Für Dinge wie:

- Fassreifung
- Tasting Notes
- Duft
- Geschmack
- Finish
- limitierte Editionen
- Awards

sind GTIN-Datenbanken oft nicht zuverlässig genug. Diese Informationen solltest du eher aus Lieferantendaten, Herstellertexten oder vorhandenen Shopdaten ziehen.

Kurz gesagt: **GTIN ist sehr nützlich zum Anreichern und Abgleichen, aber nicht allein ausreichend für hochwertige Whiskytexte.**
