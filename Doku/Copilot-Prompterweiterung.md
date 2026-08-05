# Anweisung an Copilot für erweitertes Prompt (Claude-sonnet-5 hat zweimal nichts geliefert, darum mit Claude-Opus-5)

Die Promptvorlage wurde um einige Felder ergänzt. Kannst Du diese zusätzlichen Felder bitte auch füllen? Die Felder sind:
Kategorie: {{category}} (aus Standardkategorie des Artikels)
Marke/Destillerie: {{brand}} (aus Eigenschft->Destillerie)
Herkunft/Region: {{region}} (aus Eigenschaft->Region)
Alter: {{age}} (aus Eigenschaft->Alter)
Alkoholgehalt: {{abv}} (aus Eigenschaft->VOL %)
Inhalt: {{volume}} (aus Eigenschaft->Inhalt)
Jahrgang: {{vintage}} (aus Eigenschaft->Jahrgang)
Wenn ein Feld vorhanden ist dieses füllen. Ansonsten leer lassen.

## Resultat

Die zusätzlichen Prompt-Platzhalter werden nun gefüllt.

**Neu in internautenproductai.php:**

- `getProductPromptPlaceholders($idProduct, $idLang)` liest die Standardkategorie (`id_category_default` → `category_lang.name`) sowie alle Produkt-Eigenschaften (`feature_product` + `feature_lang` + `feature_value_lang`).
- `matchFeaturePlaceholder()` mappt Eigenschaftsnamen normalisiert (klein, ohne Sonderzeichen) auf die Platzhalter: Destillerie/Distillery/Marke → `brand`, Region/Herkunft → `region`, Alter/Age → `age`, VOL %/Alkoholgehalt/ABV → `abv`, Inhalt/Volumen/Füllmenge → `volume`, Jahrgang/Vintage → `vintage`.
- `applyPromptPlaceholders()` ersetzt alle Platzhalter und entfernt nicht aufgelöste `{{…}}`, sodass fehlende Werte leer bleiben.
- `generateProductDescription($productName, array $placeholders = array())` — zweiter Parameter optional, alte Aufrufe bleiben kompatibel.
- Die Standard-Prompt-Vorlage enthält jetzt den Block „Falls vorhanden, nutze diese Zusatzinformationen“ mit allen sieben Feldern; der Hilfetext des Vorlagenfelds listet alle Platzhalter auf.

**Datenzulieferung:**

- AdminInternautenProductAiGenerateController.php: Bulk-Lauf und Einzelgenerierung ermitteln die Platzhalter über die Produkt-ID.
- ajax.php: Fallback-Endpoint ebenso.
- admin-product.js: neue `findProductId()` sendet `id_product` mit (aus Formularfeld oder URL), damit im Produkt-Editor die Eigenschaften ebenfalls einfliessen.

Version auf 2.3.0 erhöht, Übersetzungen in de/en/fr/it aktualisiert, PHP-Lint fehlerfrei. (Node ist nicht installiert, daher konnte das JS nicht per `node --check` geprüft werden.)

Made changes.

# Anweisung an Copilot Prompt Test

Kannst du mir unterhalb der Liste wo ich produkte wählen kann eine zusärtzlichen Button einbauen? Dieser soll das erste gewählte Produkt nehmen und mir in einem Pop-Up den vom System generierten Prompt anzeigen.

## Antwort

Der Prompt-Vorschau-Button ist eingebaut.

**internautenproductai.php**

- Neue Methode `buildPromptPreview($productName, $placeholders)` liefert Modell, System-Prompt und den fertig aufgelösten User-Prompt (nutzt dieselbe `applyPromptPlaceholders()`-Logik wie die Generierung — also identisch zu dem, was tatsächlich an OpenAI geht).
- Neuer Button „Prompt des ersten Produkts anzeigen“ direkt neben dem Generieren-Button unterhalb der Produktliste.
- Modal-Markup (`internauten-ai-prompt-modal`) mit Overlay, Titelzeile inkl. Artikelnummer/Name und Schliessen-Button.
- JS: nimmt das erste Element der aktuellen Auswahl, ruft die neue AJAX-Aktion, rendert Modell/System-Prompt/User-Prompt als `<pre>`-Blöcke. Schliessen per Button, Klick auf das Overlay oder Escape.

**AdminInternautenProductAiGenerateController.php**

- Neue Aktion `PreviewPrompt` (`displayAjaxPreviewPrompt` / `ajaxProcessPreviewPrompt`): ermittelt Produktname in Deutsch, sammelt die Platzhalter über `getProductPromptPlaceholders()` und gibt die Vorschau als JSON zurück — ohne API-Aufruf, also ohne Kosten.

Version auf 2.4.0 erhöht, PHP-Lint fehlerfrei.

Made changes.

## Optimierte Promptvorlage

Erstelle fuer das Produkt "{{product_name}}" eine sommelier-artige Produktbeschreibung fuer einen deutschsprachigen Onlineshop mit Fokus auf Whisky und Premium-Spirituosen.

Falls vorhanden, nutze diese Zusatzinformationen:
Kategorie: {{category}}
Destillerie: {{brand}}
Herkunft/Region: {{region}}
Alter: {{age}}
Alkoholgehalt: {{abv}}
Inhalt: {{volume}}
Jahrgang: {{vintage}}
Abfüller {{bottler}}

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

## Optimierter Systemprompt

Du bist ein professioneller E-Commerce-Texter mit sommelier-artigem Stil und Fokus auf hochwertige Spirituosen, insbesondere Whisky. Erstelle elegante, sensorische und glaubwuerdige HTML-Produktbeschreibungen fuer deutschsprachige Onlineshops.

Nutze ausschliesslich die bereitgestellten Produktinformationen. Wenn Details wie Herkunft, Alter, Fassreifung, Duft, Geschmack, Textur oder Nachklang nicht eindeutig aus den Daten oder dem Produktnamen hervorgehen, formuliere stilvoll und allgemein, ohne konkrete Fakten zu erfinden.

Die Sprache ist genussorientiert, praezise und hochwertig, aber nie uebertrieben oder pathetisch. Verwende Schweizer Doppel-S, also nie &. Kodiere Umlaute und Sonderzeichen in HTML-Entities, z. B. ä, ö, ü, ä, ö, ü. Antworte ausschliesslich mit sauberem HTML ohne Markdown-Codebloecke.
