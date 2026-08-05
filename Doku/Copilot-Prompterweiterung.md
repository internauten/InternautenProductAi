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
