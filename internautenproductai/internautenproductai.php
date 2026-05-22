<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InternautenProductAi extends Module
{
    const CONFIG_API_KEY = 'IPA_OPENAI_API_KEY';
    const CONFIG_MODEL = 'IPA_OPENAI_MODEL';
    const CONFIG_SYSTEM_PROMPT = 'IPA_SYSTEM_PROMPT';
    const CONFIG_PROMPT_TEMPLATE = 'IPA_PROMPT_TEMPLATE';

    public function __construct()
    {
        $this->name = 'internautenproductai';
        $this->tab = 'administration';
        $this->version = '1.2.0';
        $this->author = 'die.internauten.ch GmbH';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Internauten Product AI');
        $this->description = $this->l('Erzeugt per ChatGPT eine Produktbeschreibung direkt im PrestaShop-Admin.');
        $this->ps_versions_compliancy = array(
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
        );
    }

    public function install()
    {
        return parent::install()
            && $this->installTab()
            && $this->registerHook('displayBackOfficeHeader')
            && Configuration::updateValue(self::CONFIG_MODEL, 'gpt-4o-mini')
            && Configuration::updateValue(self::CONFIG_SYSTEM_PROMPT, $this->getDefaultSystemPrompt())
            && Configuration::updateValue(self::CONFIG_PROMPT_TEMPLATE, $this->getDefaultPromptTemplate());
    }

    public function uninstall()
    {
        return Configuration::deleteByName(self::CONFIG_API_KEY)
            && Configuration::deleteByName(self::CONFIG_MODEL)
            && Configuration::deleteByName(self::CONFIG_SYSTEM_PROMPT)
            && Configuration::deleteByName(self::CONFIG_PROMPT_TEMPLATE)
            && $this->uninstallTab()
            && parent::uninstall();
    }

    protected function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminInternautenProductAiGenerate');
        if ($tabId) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = 'AdminInternautenProductAiGenerate';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->active = 0;

        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Internauten Product AI';
        }

        return (bool) $tab->add();
    }

    protected function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminInternautenProductAiGenerate');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);
        return (bool) $tab->delete();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitInternautenProductAi')) {
            $apiKey = trim((string) Tools::getValue(self::CONFIG_API_KEY));
            $model = trim((string) Tools::getValue(self::CONFIG_MODEL));
            $systemPrompt = trim((string) Tools::getValue(self::CONFIG_SYSTEM_PROMPT));
            $promptTemplate = trim((string) Tools::getValue(self::CONFIG_PROMPT_TEMPLATE));

            if ($model === '') {
                $model = 'gpt-4o-mini';
            }

            if ($systemPrompt === '') {
                $systemPrompt = $this->getDefaultSystemPrompt();
            }

            if ($promptTemplate === '') {
                $promptTemplate = $this->getDefaultPromptTemplate();
            }

            Configuration::updateValue(self::CONFIG_API_KEY, $apiKey);
            Configuration::updateValue(self::CONFIG_MODEL, $model);
            Configuration::updateValue(self::CONFIG_SYSTEM_PROMPT, $systemPrompt);
            Configuration::updateValue(self::CONFIG_PROMPT_TEMPLATE, $promptTemplate);

            $output .= $this->displayConfirmation($this->l('Die Einstellungen wurden gespeichert.'));
        }

        return $output . $this->renderForm() . $this->renderBulkGenerationPanel();
    }

    protected function renderBulkGenerationPanel()
    {
        $idLang = (int) $this->context->language->id;
        $products = $this->getBulkGenerationProducts(500);

        $ajaxUrl = $this->context->link->getAdminLink(
            'AdminInternautenProductAiGenerate',
            true,
            array(),
            array(
                'ajax' => 1,
                'action' => 'GenerateBulkDescriptions',
            )
        );

        $texts = array(
            'selectRequired' => $this->l('Bitte wähle mindestens ein Produkt aus.'),
            'loading' => $this->l('Beschreibungen werden generiert...'),
            'done' => $this->l('Fertig: %d erfolgreich, %d fehlgeschlagen.'),
            'genericError' => $this->l('Die Bulk-Generierung konnte nicht durchgeführt werden.'),
            'invalidJsonError' => $this->l('Der Server hat keine gültige JSON-Antwort geliefert.'),
            'emptyResponseLabel' => $this->l('leere Antwort'),
        );

        $optionsHtml = '';
        foreach ($products as $product) {
            $idProduct = (int) $product['id_product'];
            $name = trim((string) $product['name']);
            $reference = trim((string) $product['reference']);

            if ($name === '') {
                continue;
            }

            $label = '#' . $idProduct . ' - ' . $name;
            if ($reference !== '') {
                $label .= ' [' . $reference . ']';
            }

            $optionsHtml .= '<option value="' . $idProduct . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
        }

        if ($optionsHtml === '') {
            $optionsHtml = '<option value="" disabled="disabled">'
                . htmlspecialchars($this->l('Keine Produkte gefunden.'), ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        $html = '';
        $html .= '<div class="panel">';
        $html .= '<h3><i class="icon-magic"></i> ' . htmlspecialchars($this->l('Bulk-Generierung für Produkte'), ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<p>' . htmlspecialchars($this->l('Wähle mehrere Produkte aus. Für jedes ausgewählte Produkt wird eine Beschreibung generiert und in der aktuellen Backoffice-Sprache gespeichert.'), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<div class="form-group">';
        $html .= '<label for="internauten-ai-bulk-search">' . htmlspecialchars($this->l('Produkte filtern'), ENT_QUOTES, 'UTF-8') . '</label>';
        $html .= '<input type="text" id="internauten-ai-bulk-search" class="form-control" placeholder="' . htmlspecialchars($this->l('Suche nach ID, Name oder Referenz...'), ENT_QUOTES, 'UTF-8') . '" style="margin-bottom:8px;">';
        $html .= '<label for="internauten-ai-bulk-products">' . htmlspecialchars($this->l('Produkte auswählen'), ENT_QUOTES, 'UTF-8') . '</label>';
        $html .= '<select id="internauten-ai-bulk-products" class="form-control" multiple="multiple" size="14">' . $optionsHtml . '</select>';
        $html .= '<div style="margin-top:8px;">';
        $html .= '<button type="button" id="internauten-ai-bulk-select-visible" class="btn btn-default btn-sm" style="margin-right:8px;">' . htmlspecialchars($this->l('Alle sichtbaren auswählen'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '<button type="button" id="internauten-ai-bulk-clear-selection" class="btn btn-default btn-sm">' . htmlspecialchars($this->l('Auswahl leeren'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '</div>';
        $html .= '<p class="help-block">' . htmlspecialchars($this->l('Tipp: Mit Strg/Cmd oder Shift kannst du mehrere Produkte markieren.'), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
        $html .= '<button type="button" id="internauten-ai-bulk-generate" class="btn btn-primary">' . htmlspecialchars($this->l('Beschreibungen für Auswahl generieren'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '<p id="internauten-ai-bulk-status" style="margin-top:10px;"></p>';
        $html .= '<ul id="internauten-ai-bulk-results" class="list-unstyled" style="margin-top:10px; max-height: 240px; overflow:auto;"></ul>';
        $html .= '</div>';

        $scriptConfig = array(
            'ajaxUrl' => $ajaxUrl,
            'idLang' => $idLang,
            'texts' => $texts,
        );

        $html .= '<script>(function(){'
            . 'var cfg=' . json_encode($scriptConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';'
            . 'var search=document.getElementById("internauten-ai-bulk-search");'
            . 'var select=document.getElementById("internauten-ai-bulk-products");'
            . 'var selectVisible=document.getElementById("internauten-ai-bulk-select-visible");'
            . 'var clearSelection=document.getElementById("internauten-ai-bulk-clear-selection");'
            . 'var button=document.getElementById("internauten-ai-bulk-generate");'
            . 'var status=document.getElementById("internauten-ai-bulk-status");'
            . 'var results=document.getElementById("internauten-ai-bulk-results");'
            . 'if(!search||!select||!selectVisible||!clearSelection||!button||!status||!results){return;}'
            . 'search.addEventListener("input",function(){'
                . 'var query=(search.value||"").toLowerCase().trim();'
                . 'Array.prototype.forEach.call(select.options,function(opt){'
                    . 'if(!opt.value){return;}'
                    . 'var match=!query||opt.text.toLowerCase().indexOf(query)!==-1;'
                    . 'opt.hidden=!match;'
                . '});'
            . '});'
            . 'selectVisible.addEventListener("click",function(){'
                . 'Array.prototype.forEach.call(select.options,function(opt){'
                    . 'if(!opt.value){return;}'
                    . 'if(!opt.hidden){opt.selected=true;}'
                . '});'
            . '});'
            . 'clearSelection.addEventListener("click",function(){'
                . 'Array.prototype.forEach.call(select.options,function(opt){opt.selected=false;});'
            . '});'
            . 'button.addEventListener("click",function(){'
                . 'var selected=Array.prototype.slice.call(select.options).filter(function(opt){return opt.selected&&opt.value;}).map(function(opt){return opt.value;});'
                . 'if(!selected.length){window.alert(cfg.texts.selectRequired);return;}'
                . 'button.disabled=true;'
                . 'status.textContent=cfg.texts.loading;'
                . 'results.innerHTML="";'
                . 'var body=new URLSearchParams();'
                . 'body.append("id_lang",String(cfg.idLang));'
                . 'selected.forEach(function(id){body.append("product_ids[]",id);});'
                . 'fetch(cfg.ajaxUrl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin",body:body.toString()})'
                    . '.then(function(response){return response.text().then(function(text){var data=null;try{data=JSON.parse(text);}catch(e){var preview=(text||"").replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim().slice(0,220);throw new Error(cfg.texts.invalidJsonError+" "+(preview||cfg.texts.emptyResponseLabel));}if(!response.ok||!data.success){throw new Error((data&&data.message)||cfg.texts.genericError);}return data;});})'
                    . '.then(function(data){'
                        . 'var summary=data.summary||{success:0,failed:0};'
                        . 'status.textContent=cfg.texts.done.replace("%d",String(summary.success||0)).replace("%d",String(summary.failed||0));'
                        . '(data.results||[]).forEach(function(item){var li=document.createElement("li");li.textContent="#"+item.id_product+" - "+(item.name||"")+": "+(item.message||"");li.style.color=item.success?"#2e7d32":"#c62828";results.appendChild(li);});'
                    . '})'
                    . '.catch(function(error){status.textContent=error.message||cfg.texts.genericError;})'
                    . '.finally(function(){button.disabled=false;});'
            . '});'
        . '})();</script>';

        return $html;
    }

    protected function getBulkGenerationProducts($limit)
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $query = new DbQuery();
        $query->select('p.id_product, pl.name, p.reference');
        $query->from('product', 'p');
        $query->innerJoin(
            'product_lang',
            'pl',
            'pl.id_product = p.id_product AND pl.id_lang = ' . $idLang . ' AND pl.id_shop = ' . $idShop
        );
        $query->orderBy('p.id_product DESC');
        $query->limit((int) $limit);

        $rows = Db::getInstance()->executeS($query);
        return is_array($rows) ? $rows : array();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitInternautenProductAi';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int) $this->context->language->id;
        $helper->allow_employee_form_lang = (int) $this->context->language->id;
        $helper->fields_value = array(
            self::CONFIG_API_KEY => Configuration::get(self::CONFIG_API_KEY),
            self::CONFIG_MODEL => Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini',
            self::CONFIG_SYSTEM_PROMPT => Configuration::get(self::CONFIG_SYSTEM_PROMPT) ?: $this->getDefaultSystemPrompt(),
            self::CONFIG_PROMPT_TEMPLATE => Configuration::get(self::CONFIG_PROMPT_TEMPLATE) ?: $this->getDefaultPromptTemplate(),
        );

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('OpenAI-Einstellungen'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('OpenAI API Key'),
                        'name' => self::CONFIG_API_KEY,
                        'required' => false,
                        'desc' => $this->l('Trage hier deinen OpenAI API Key ein.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Modell'),
                        'name' => self::CONFIG_MODEL,
                        'required' => true,
                        'desc' => $this->l('Empfohlen: gpt-4o-mini'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('System-Prompt'),
                        'name' => self::CONFIG_SYSTEM_PROMPT,
                        'rows' => 8,
                        'cols' => 80,
                        'autoload_rte' => false,
                        'desc' => $this->l('Definiert Rolle, Stil und Verhalten des Modells.'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Prompt-Vorlage'),
                        'name' => self::CONFIG_PROMPT_TEMPLATE,
                        'rows' => 8,
                        'cols' => 80,
                        'autoload_rte' => false,
                        'desc' => $this->l('Nutze {{product_name}} als Platzhalter für den Artikelnamen.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Speichern'),
                ),
            ),
        );

        return $helper->generateForm(array($fieldsForm));
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') !== 'AdminProducts') {
            return;
        }

        $ajaxUrl = $this->context->link->getAdminLink(
            'AdminInternautenProductAiGenerate',
            true,
            array(),
            array(
                'ajax' => 1,
                'action' => 'GenerateDescription',
            )
        );

        $fallbackAjaxUrl = $this->context->link->getBaseLink()
            . ltrim($this->_path, '/')
            . 'ajax.php?action=GenerateDescription&token='
            . self::getAjaxToken();

        $jsPath = $this->_path . 'views/js/admin-product.js';
        $jsFile = $this->local_path . 'views/js/admin-product.js';
        if (is_file($jsFile)) {
            $jsPath .= '?v=' . (int) filemtime($jsFile);
        }

        $this->context->controller->addJS($jsPath);

        Media::addJsDef(array(
            'internautenProductAi' => array(
                'ajaxUrl' => $ajaxUrl,
                'fallbackAjaxUrl' => $fallbackAjaxUrl,
                'buttonLabel' => $this->l('Mit ChatGPT generieren'),
                'loadingLabel' => $this->l('Beschreibung wird erstellt...'),
                'errorNoName' => $this->l('Bitte zuerst einen Artikelnamen eintragen.'),
                'genericError' => $this->l('Die Beschreibung konnte nicht generiert werden.'),
                'generationError' => $this->l('Fehler bei der Generierung.'),
                'invalidJsonError' => $this->l('Der Server hat keine gültige JSON-Antwort geliefert:'),
                'emptyResponseLabel' => $this->l('leere Antwort'),
            ),
        ));
    }

    public static function getAjaxToken()
    {
        return hash_hmac('sha256', 'internautenproductai_ajax', _COOKIE_KEY_);
    }

    public function generateProductDescription($productName)
    {
        $apiKey = trim((string) Configuration::get(self::CONFIG_API_KEY));
        $model = trim((string) (Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini'));
        $systemPrompt = trim((string) (Configuration::get(self::CONFIG_SYSTEM_PROMPT) ?: $this->getDefaultSystemPrompt()));
        $promptTemplate = trim((string) (Configuration::get(self::CONFIG_PROMPT_TEMPLATE) ?: $this->getDefaultPromptTemplate()));

        if ($apiKey === '') {
            throw new Exception($this->l('Es ist kein OpenAI API Key hinterlegt.'));
        }

        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        if ($systemPrompt === '') {
            $systemPrompt = $this->getDefaultSystemPrompt();
        }

        if ($promptTemplate === '') {
            $promptTemplate = $this->getDefaultPromptTemplate();
        }

        $userPrompt = str_replace('{{product_name}}', $productName, $promptTemplate);

        if (!function_exists('curl_init')) {
            throw new Exception($this->l('Die PHP-cURL-Erweiterung ist auf dem Server nicht aktiviert.'));
        }

        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $systemPrompt,
                ),
                array(
                    'role' => 'user',
                    'content' => $userPrompt,
                ),
            ),
            'temperature' => 0.7,
        );

        $curl = curl_init('https://api.openai.com/v1/chat/completions');

        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
        ));

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false || $curlError) {
            throw new Exception($this->l('OpenAI konnte nicht erreicht werden: ') . $curlError);
        }

        $data = json_decode($response, true);

        if ($statusCode >= 400) {
            $message = isset($data['error']['message']) ? $data['error']['message'] : $this->l('Unbekannter API-Fehler.');
            throw new Exception($message);
        }

        $content = '';
        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim((string) $data['choices'][0]['message']['content']);
        }

        if ($content === '') {
            throw new Exception($this->l('OpenAI hat keine Beschreibung zurückgegeben.'));
        }

        if (preg_match('/```(?:html)?\s*(.*?)```/is', $content, $matches)) {
            $content = trim($matches[1]);
        }

        return $content;
    }

    protected function getDefaultSystemPrompt()
    {
        return 'Du bist ein professioneller E-Commerce-Texter mit sommelier-artigem Stil und Fokus auf hochwertige Spirituosen, insbesondere Whisky. Erstelle elegante, sensorische und glaubwürdige HTML-Produktbeschreibungen für deutschsprachige Onlineshops. Betone bei Whisky – sofern aus dem Produktnamen plausibel ableitbar – Herkunft, Reifung, Duftbild, Geschmacksprofil, Textur und Nachklang. Wenn Details fehlen, bleibe stilvoll und allgemein, ohne Fakten zu erfinden. Die Sprache soll genussorientiert, präzise und hochwertig sein, aber nie übertrieben oder pathetisch. Antworte ausschließlich mit sauberem HTML ohne Markdown-Codeblöcke.';
    }

    protected function getDefaultPromptTemplate()
    {
        return "Erstelle für das Produkt \"{{product_name}}\" eine sommelier-artige Produktbeschreibung für einen deutschsprachigen Onlineshop mit Fokus auf Whisky und Premium-Spirituosen.\n\n"
            . "Inhalt:\n"
            . "- beschreibe den Whisky mit einer eleganten, sensorischen und genussorientierten Sprache\n"
            . "- gehe, wenn aus dem Namen erkennbar, auf Herkunft, Fassreifung, Duft, Geschmack, Mundgefühl und Nachklang ein\n"
            . "- stelle Charakter und Stil des Produkts hochwertig dar, ohne erfundene Fakten zu ergänzen\n"
            . "- vermittle, zu welchem Genussmoment oder Anlass der Whisky besonders gut passt\n\n"
            . "Format:\n"
            . "- gib ausschließlich sauberes HTML für die PrestaShop-Produktbeschreibung zurück\n"
            . "- beginne mit einem atmosphärischen Absatz in <p>\n"
            . "- ergänze eine Zwischenüberschrift in <h3> wie Verkostungsnotizen oder Charakter\n"
            . "- füge 4 bis 6 prägnante Stichpunkte in einer <ul> mit <li>-Elementen ein\n"
            . "- schließe mit einem kurzen, stilvollen Absatz zum Finish oder Genussmoment ab\n\n"
            . "Stil:\n"
            . "- sommelier-artig, präzise, hochwertig und vertrauenswürdig\n"
            . "- bildhaft und genussvoll, aber nicht kitschig oder übertrieben\n"
            . "- keine Fantasieangaben, keine Hinweise auf KI, keine Emojis, keine Markdown-Codeblöcke";
    }
}
