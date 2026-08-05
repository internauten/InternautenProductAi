<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InternautenProductAi extends Module
{
    const CONFIG_API_KEY = 'IPA_OPENAI_API_KEY';
    const CONFIG_MODEL = 'IPA_OPENAI_MODEL';
    const CONFIG_MODEL_LIST = 'IPA_OPENAI_MODEL_LIST';
    const CONFIG_TEMPERATURE = 'IPA_OPENAI_TEMPERATURE';
    const CONFIG_MAX_TOKENS = 'IPA_OPENAI_MAX_TOKENS';
    const CONFIG_TOP_P = 'IPA_OPENAI_TOP_P';
    const CONFIG_REASONING_EFFORT = 'IPA_OPENAI_REASONING_EFFORT';
    const CONFIG_EXTRA_PARAMETERS = 'IPA_OPENAI_EXTRA_PARAMETERS';
    const CONFIG_SYSTEM_PROMPT = 'IPA_SYSTEM_PROMPT';
    const CONFIG_PROMPT_TEMPLATE = 'IPA_PROMPT_TEMPLATE';

    public function __construct()
    {
        $this->name = 'internautenproductai';
        $this->tab = 'administration';
        $this->version = '2.4.0';
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
            && Configuration::updateValue(self::CONFIG_MODEL_LIST, '')
            && Configuration::updateValue(self::CONFIG_TEMPERATURE, '0.7')
            && Configuration::updateValue(self::CONFIG_MAX_TOKENS, '')
            && Configuration::updateValue(self::CONFIG_TOP_P, '')
            && Configuration::updateValue(self::CONFIG_REASONING_EFFORT, '')
            && Configuration::updateValue(self::CONFIG_EXTRA_PARAMETERS, '')
            && Configuration::updateValue(self::CONFIG_SYSTEM_PROMPT, $this->getDefaultSystemPrompt())
            && Configuration::updateValue(self::CONFIG_PROMPT_TEMPLATE, $this->getDefaultPromptTemplate());
    }

    public function uninstall()
    {
        return Configuration::deleteByName(self::CONFIG_API_KEY)
            && Configuration::deleteByName(self::CONFIG_MODEL)
            && Configuration::deleteByName(self::CONFIG_MODEL_LIST)
            && Configuration::deleteByName(self::CONFIG_TEMPERATURE)
            && Configuration::deleteByName(self::CONFIG_MAX_TOKENS)
            && Configuration::deleteByName(self::CONFIG_TOP_P)
            && Configuration::deleteByName(self::CONFIG_REASONING_EFFORT)
            && Configuration::deleteByName(self::CONFIG_EXTRA_PARAMETERS)
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
            $temperature = trim((string) Tools::getValue(self::CONFIG_TEMPERATURE));
            $maxTokens = trim((string) Tools::getValue(self::CONFIG_MAX_TOKENS));
            $topP = trim((string) Tools::getValue(self::CONFIG_TOP_P));
            $reasoningEffort = trim((string) Tools::getValue(self::CONFIG_REASONING_EFFORT));
            $extraParameters = trim((string) Tools::getValue(self::CONFIG_EXTRA_PARAMETERS));
            $systemPrompt = trim((string) Tools::getValue(self::CONFIG_SYSTEM_PROMPT));
            $promptTemplate = trim((string) Tools::getValue(self::CONFIG_PROMPT_TEMPLATE));

            if ($model === '') {
                $model = 'gpt-4o-mini';
            }

            if ($temperature === '') {
                $temperature = '0.7';
            }

            if ($systemPrompt === '') {
                $systemPrompt = $this->getDefaultSystemPrompt();
            }

            if ($promptTemplate === '') {
                $promptTemplate = $this->getDefaultPromptTemplate();
            }

            if ($extraParameters !== '' && !$this->isValidJsonObject($extraParameters)) {
                $output .= $this->displayError($this->l('Die zusätzlichen Parameter müssen als gültiges JSON-Objekt angegeben werden.'));
            } else {
                Configuration::updateValue(self::CONFIG_API_KEY, $apiKey);
                $this->refreshModelCache($apiKey);
                Configuration::updateValue(self::CONFIG_MODEL, $model);
                Configuration::updateValue(self::CONFIG_TEMPERATURE, $temperature);
                Configuration::updateValue(self::CONFIG_MAX_TOKENS, $maxTokens);
                Configuration::updateValue(self::CONFIG_TOP_P, $topP);
                Configuration::updateValue(self::CONFIG_REASONING_EFFORT, $reasoningEffort);
                Configuration::updateValue(self::CONFIG_EXTRA_PARAMETERS, $extraParameters);
                Configuration::updateValue(self::CONFIG_SYSTEM_PROMPT, $systemPrompt);
                Configuration::updateValue(self::CONFIG_PROMPT_TEMPLATE, $promptTemplate);

                $output .= $this->displayConfirmation($this->l('Die Einstellungen wurden gespeichert.'));
            }
        }

        return $output . $this->renderForm() . $this->renderBulkGenerationPanel();
    }

    protected function renderBulkGenerationPanel()
    {
        $idLang = (int) $this->context->language->id;

        $ajaxUrl = $this->context->link->getAdminLink(
            'AdminInternautenProductAiGenerate',
            true,
            array(),
            array(
                'ajax' => 1,
                'action' => 'GenerateBulkDescriptionSingle',
            )
        );

        $searchAjaxUrl = $this->context->link->getAdminLink(
            'AdminInternautenProductAiGenerate',
            true,
            array(),
            array(
                'ajax' => 1,
                'action' => 'SearchBulkProducts',
            )
        );

        $previewAjaxUrl = $this->context->link->getAdminLink(
            'AdminInternautenProductAiGenerate',
            true,
            array(),
            array(
                'ajax' => 1,
                'action' => 'PreviewPrompt',
            )
        );

        $texts = array(
            'selectRequired' => $this->l('Bitte wähle mindestens ein Produkt aus.'),
            'loading' => $this->l('Beschreibungen werden im Hintergrund generiert...'),
            'progress' => $this->l('Verarbeite %d von %d...'),
            'searchLoading' => $this->l('Produkte werden geladen...'),
            'done' => $this->l('Fertig: %d erfolgreich, %d fehlgeschlagen.'),
            'genericError' => $this->l('Die Bulk-Generierung konnte nicht durchgeführt werden.'),
            'invalidJsonError' => $this->l('Der Server hat keine gültige JSON-Antwort geliefert.'),
            'emptyResponseLabel' => $this->l('leere Antwort'),
            'noProductsFound' => $this->l('Keine Produkte gefunden.'),
            'selectedCount' => $this->l('Ausgewählt: %d Produkte'),
            'previewRequired' => $this->l('Bitte wähle zuerst ein Produkt aus.'),
            'previewLoading' => $this->l('Prompt wird geladen...'),
            'previewError' => $this->l('Der Prompt konnte nicht ermittelt werden.'),
            'previewModel' => $this->l('Modell'),
            'previewSystem' => $this->l('System-Prompt'),
            'previewUser' => $this->l('User-Prompt'),
        );

        $categoryRows = Db::getInstance()->executeS(
            'SELECT c.id_category, cl.name'
            . ' FROM `' . _DB_PREFIX_ . 'category` c'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.id_category = c.id_category AND cl.id_lang = ' . (int) $idLang . ')'
            . ' WHERE c.active = 1'
            . ' ORDER BY cl.name ASC, c.id_category ASC'
        );

        $html = '';
        $html .= '<div class="panel">';
        $html .= '<h3><i class="icon-magic"></i> ' . htmlspecialchars($this->l('Bulk-Generierung für Produkte'), ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<p>' . htmlspecialchars($this->l('Wähle mehrere Produkte aus. Für jedes ausgewählte Produkt wird eine Beschreibung generiert und in der aktuellen Backoffice-Sprache gespeichert.'), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<div class="form-group">';
        $html .= '<label for="internauten-ai-bulk-search">' . htmlspecialchars($this->l('Produkte filtern'), ENT_QUOTES, 'UTF-8') . '</label>';
        $html .= '<input type="text" id="internauten-ai-bulk-search" class="form-control" placeholder="' . htmlspecialchars($this->l('Suche nach ID, Name oder Referenz...'), ENT_QUOTES, 'UTF-8') . '" style="margin-bottom:8px;">';
        $html .= '<label for="internauten-ai-bulk-category-filter">' . htmlspecialchars($this->l('Hauptkategorie'), ENT_QUOTES, 'UTF-8') . '</label>';
        $html .= '<select id="internauten-ai-bulk-category-filter" class="form-control" style="margin-bottom:8px;">';
        $html .= '<option value="">' . htmlspecialchars($this->l('Alle Kategorien'), ENT_QUOTES, 'UTF-8') . '</option>';
        foreach ((array) $categoryRows as $categoryRow) {
            $categoryId = isset($categoryRow['id_category']) ? (int) $categoryRow['id_category'] : 0;
            $categoryName = isset($categoryRow['name']) ? trim((string) $categoryRow['name']) : '';
            if ($categoryId <= 0) {
                continue;
            }
            if ($categoryName === '') {
                $categoryName = $this->l('Kategorie') . ' #' . $categoryId;
            }
            $html .= '<option value="' . (int) $categoryId . '">' . htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $html .= '</select>';
        $html .= '<label for="internauten-ai-bulk-products">' . htmlspecialchars($this->l('Produkte auswählen'), ENT_QUOTES, 'UTF-8') . '</label>';
        $html .= '<select id="internauten-ai-bulk-products" class="form-control" multiple="multiple" size="14"></select>';
        $html .= '<div style="margin-top:8px;">';
        $html .= '<button type="button" id="internauten-ai-bulk-select-visible" class="btn btn-default btn-sm" style="margin-right:8px;">' . htmlspecialchars($this->l('Alle sichtbaren auswählen'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '<button type="button" id="internauten-ai-bulk-clear-selection" class="btn btn-default btn-sm">' . htmlspecialchars($this->l('Auswahl leeren'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '</div>';
        $html .= '<p class="help-block">' . htmlspecialchars($this->l('Tipp: Mit Strg/Cmd oder Shift kannst du mehrere Produkte markieren.'), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
        $html .= '<button type="button" id="internauten-ai-bulk-generate" class="btn btn-primary" style="margin-right:8px;">' . htmlspecialchars($this->l('Beschreibungen für Auswahl generieren'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '<button type="button" id="internauten-ai-bulk-preview-prompt" class="btn btn-default">' . htmlspecialchars($this->l('Prompt des ersten Produkts anzeigen'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '<p id="internauten-ai-bulk-selected-count" style="margin-top:10px; margin-bottom:0;"></p>';
        $html .= '<p id="internauten-ai-bulk-status" style="margin-top:10px;"></p>';
        $html .= '<ul id="internauten-ai-bulk-results" class="list-unstyled" style="margin-top:10px; max-height: 240px; overflow:auto;"></ul>';
        $html .= '</div>';

        $html .= '<div id="internauten-ai-prompt-modal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.5);">';
        $html .= '<div style="background:#fff; max-width:900px; margin:40px auto; padding:20px; border-radius:4px; max-height:85vh; display:flex; flex-direction:column;">';
        $html .= '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">';
        $html .= '<h3 id="internauten-ai-prompt-modal-title" style="margin:0;">' . htmlspecialchars($this->l('Generierter Prompt'), ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<button type="button" id="internauten-ai-prompt-modal-close" class="btn btn-default btn-sm">' . htmlspecialchars($this->l('Schliessen'), ENT_QUOTES, 'UTF-8') . '</button>';
        $html .= '</div>';
        $html .= '<div id="internauten-ai-prompt-modal-body" style="overflow:auto; flex:1 1 auto;"></div>';
        $html .= '</div>';
        $html .= '</div>';

        $scriptConfig = array(
            'ajaxUrl' => $ajaxUrl,
            'searchAjaxUrl' => $searchAjaxUrl,
            'previewAjaxUrl' => $previewAjaxUrl,
            'idLang' => $idLang,
            'texts' => $texts,
        );

        $html .= '<script>(function(){'
            . 'var cfg=' . json_encode($scriptConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';'
            . 'var search=document.getElementById("internauten-ai-bulk-search");'
            . 'var categoryFilter=document.getElementById("internauten-ai-bulk-category-filter");'
            . 'var select=document.getElementById("internauten-ai-bulk-products");'
            . 'var selectVisible=document.getElementById("internauten-ai-bulk-select-visible");'
            . 'var clearSelection=document.getElementById("internauten-ai-bulk-clear-selection");'
            . 'var button=document.getElementById("internauten-ai-bulk-generate");'
            . 'var previewButton=document.getElementById("internauten-ai-bulk-preview-prompt");'
            . 'var modal=document.getElementById("internauten-ai-prompt-modal");'
            . 'var modalBody=document.getElementById("internauten-ai-prompt-modal-body");'
            . 'var modalTitle=document.getElementById("internauten-ai-prompt-modal-title");'
            . 'var modalClose=document.getElementById("internauten-ai-prompt-modal-close");'
            . 'var selectedCount=document.getElementById("internauten-ai-bulk-selected-count");'
            . 'var status=document.getElementById("internauten-ai-bulk-status");'
            . 'var results=document.getElementById("internauten-ai-bulk-results");'
            . 'if(!search||!select||!selectVisible||!clearSelection||!button||!selectedCount||!status||!results){return;}'
            . 'var selectedIds={};'
            . 'var searchTimer=null;'
            . 'function parseJsonResponse(response){return response.text().then(function(text){var data=null;try{data=JSON.parse(text);}catch(e){var preview=(text||"").replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim().slice(0,220);throw new Error(cfg.texts.invalidJsonError+" "+(preview||cfg.texts.emptyResponseLabel));}if(!response.ok||!data.success){throw new Error((data&&data.message)||cfg.texts.genericError);}return data;});}'
            . 'function updateSelectedCount(){selectedCount.textContent=cfg.texts.selectedCount.replace("%d",String(Object.keys(selectedIds).length));}'
            . 'function renderOptions(products){select.innerHTML="";if(!products.length){var emptyOpt=document.createElement("option");emptyOpt.value="";emptyOpt.disabled=true;emptyOpt.textContent=cfg.texts.noProductsFound;select.appendChild(emptyOpt);return;}products.forEach(function(item){var option=document.createElement("option");var id=String(item.id_product||"");option.value=id;option.textContent=item.label||("#"+id+" - "+(item.name||""));if(selectedIds[id]){option.selected=true;}select.appendChild(option);});}'
            . 'function syncCurrentSelections(){Array.prototype.forEach.call(select.options,function(opt){if(!opt.value){return;}if(opt.selected){selectedIds[opt.value]=true;}else{delete selectedIds[opt.value];}});updateSelectedCount();}'
            . 'function fetchProducts(query){var body=new URLSearchParams();body.append("id_lang",String(cfg.idLang));body.append("query",query||"");if(categoryFilter){body.append("category_id",categoryFilter.value||"");}return fetch(cfg.searchAjaxUrl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin",body:body.toString()}).then(parseJsonResponse).then(function(data){return data.products||[];});}'
            . 'function queueSearch(){if(searchTimer){clearTimeout(searchTimer);}searchTimer=setTimeout(function(){syncCurrentSelections();status.textContent=cfg.texts.searchLoading;fetchProducts((search.value||"").trim()).then(function(products){renderOptions(products);status.textContent="";}).catch(function(error){status.textContent=error.message||cfg.texts.genericError;renderOptions([]);});},250);}'
            . 'search.addEventListener("input",queueSearch);'
            . 'if(categoryFilter){categoryFilter.addEventListener("change",queueSearch);}'
            . 'select.addEventListener("change",syncCurrentSelections);'
            . 'selectVisible.addEventListener("click",function(){'
                . 'Array.prototype.forEach.call(select.options,function(opt){'
                    . 'if(!opt.value){return;}'
                    . 'opt.selected=true;'
                    . 'selectedIds[opt.value]=true;'
                . '});'
                . 'updateSelectedCount();'
            . '});'
            . 'clearSelection.addEventListener("click",function(){'
                . 'selectedIds={};'
                . 'Array.prototype.forEach.call(select.options,function(opt){opt.selected=false;});'
                . 'updateSelectedCount();'
            . '});'
            . 'function appendResult(idProduct,name,message,success){'
                . 'var li=document.createElement("li");'
                . 'li.textContent="#"+idProduct+" - "+(name||"")+": "+(message||"");'
                . 'li.style.color=success?"#2e7d32":"#c62828";'
                . 'results.appendChild(li);'
            . '}'
            . 'function processQueue(ids){'
                . 'var total=ids.length;var index=0;var successCount=0;var failedCount=0;'
                . 'function next(){'
                    . 'if(index>=total){'
                        . 'status.textContent=cfg.texts.done.replace("%d",String(successCount)).replace("%d",String(failedCount));'
                        . 'button.disabled=false;'
                        . 'selectedIds={};'
                        . 'updateSelectedCount();'
                        . 'queueSearch();'
                        . 'return;'
                    . '}'
                    . 'var id=ids[index];'
                    . 'index+=1;'
                    . 'status.textContent=cfg.texts.progress.replace("%d",String(index)).replace("%d",String(total));'
                    . 'var body=new URLSearchParams();'
                    . 'body.append("id_lang",String(cfg.idLang));'
                    . 'body.append("id_product",String(id));'
                    . 'fetch(cfg.ajaxUrl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin",body:body.toString()})'
                        . '.then(parseJsonResponse)'
                        . '.then(function(data){successCount+=1;appendResult(data.id_product,data.name,data.message,true);})'
                        . '.catch(function(error){failedCount+=1;appendResult(id,"",error.message||cfg.texts.genericError,false);})'
                        . '.finally(next);'
                . '}'
                . 'next();'
            . '}'
            . 'button.addEventListener("click",function(){'
                . 'syncCurrentSelections();'
                . 'var selected=Object.keys(selectedIds);'
                . 'if(!selected.length){window.alert(cfg.texts.selectRequired);return;}'
                . 'button.disabled=true;'
                . 'status.textContent=cfg.texts.loading;'
                . 'results.innerHTML="";'
                . 'processQueue(selected);'
            . '});'
            . 'function closeModal(){if(modal){modal.style.display="none";}}'
            . 'function renderPromptSection(label,value){'
                . 'var wrapper=document.createElement("div");'
                . 'wrapper.style.marginBottom="14px";'
                . 'var heading=document.createElement("strong");'
                . 'heading.textContent=label;'
                . 'var pre=document.createElement("pre");'
                . 'pre.style.whiteSpace="pre-wrap";'
                . 'pre.style.wordBreak="break-word";'
                . 'pre.style.marginTop="6px";'
                . 'pre.textContent=value||"";'
                . 'wrapper.appendChild(heading);'
                . 'wrapper.appendChild(pre);'
                . 'return wrapper;'
            . '}'
            . 'if(previewButton&&modal&&modalBody){'
                . 'if(modalClose){modalClose.addEventListener("click",closeModal);}'
                . 'modal.addEventListener("click",function(event){if(event.target===modal){closeModal();}});'
                . 'document.addEventListener("keydown",function(event){if(event.key==="Escape"){closeModal();}});'
                . 'previewButton.addEventListener("click",function(){'
                    . 'syncCurrentSelections();'
                    . 'var selected=Object.keys(selectedIds);'
                    . 'if(!selected.length){window.alert(cfg.texts.previewRequired);return;}'
                    . 'previewButton.disabled=true;'
                    . 'status.textContent=cfg.texts.previewLoading;'
                    . 'var body=new URLSearchParams();'
                    . 'body.append("id_lang",String(cfg.idLang));'
                    . 'body.append("id_product",selected[0]);'
                    . 'fetch(cfg.previewAjaxUrl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin",body:body.toString()})'
                        . '.then(parseJsonResponse)'
                        . '.then(function(data){'
                            . 'status.textContent="";'
                            . 'if(modalTitle){modalTitle.textContent="#"+data.id_product+" - "+(data.name||"");}'
                            . 'modalBody.innerHTML="";'
                            . 'modalBody.appendChild(renderPromptSection(cfg.texts.previewModel,data.model||""));'
                            . 'modalBody.appendChild(renderPromptSection(cfg.texts.previewSystem,data.system_prompt||""));'
                            . 'modalBody.appendChild(renderPromptSection(cfg.texts.previewUser,data.user_prompt||""));'
                            . 'modal.style.display="block";'
                        . '})'
                        . '.catch(function(error){status.textContent=error.message||cfg.texts.previewError;})'
                        . '.finally(function(){previewButton.disabled=false;});'
                . '});'
            . '}'
                    . 'updateSelectedCount();'
            . 'queueSearch();'
        . '})();</script>';

        return $html;
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
        $this->refreshModelCache(Configuration::get(self::CONFIG_API_KEY));

        $helper->fields_value = array(
            self::CONFIG_API_KEY => Configuration::get(self::CONFIG_API_KEY),
            self::CONFIG_MODEL => Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini',
            self::CONFIG_TEMPERATURE => Configuration::get(self::CONFIG_TEMPERATURE) ?: '0.7',
            self::CONFIG_MAX_TOKENS => Configuration::get(self::CONFIG_MAX_TOKENS),
            self::CONFIG_TOP_P => Configuration::get(self::CONFIG_TOP_P),
            self::CONFIG_REASONING_EFFORT => Configuration::get(self::CONFIG_REASONING_EFFORT),
            self::CONFIG_EXTRA_PARAMETERS => Configuration::get(self::CONFIG_EXTRA_PARAMETERS),
            self::CONFIG_SYSTEM_PROMPT => Configuration::get(self::CONFIG_SYSTEM_PROMPT) ?: $this->getDefaultSystemPrompt(),
            self::CONFIG_PROMPT_TEMPLATE => Configuration::get(self::CONFIG_PROMPT_TEMPLATE) ?: $this->getDefaultPromptTemplate(),
        );

        $modelField = array(
            'type' => 'text',
            'label' => $this->l('Modell'),
            'name' => self::CONFIG_MODEL,
            'required' => true,
            'desc' => $this->l('Empfohlen: gpt-4o-mini'),
        );

        $availableModels = $this->getCachedAvailableModels();
        if (!empty($availableModels)) {
            $modelField = array(
                'type' => 'select',
                'label' => $this->l('Modell'),
                'name' => self::CONFIG_MODEL,
                'required' => true,
                'desc' => $this->l('Verfügbare Modelle werden automatisch von OpenAI geladen.'),
                'options' => array(
                    'query' => $availableModels,
                    'id' => 'id',
                    'name' => 'name',
                ),
            );
        }

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
                    $modelField,
                    array(
                        'type' => 'text',
                        'label' => $this->l('Temperatur'),
                        'name' => self::CONFIG_TEMPERATURE,
                        'required' => false,
                        'desc' => $this->l('Optional. Für klassische Modelle ist 0.7 ein sinnvoller Standard. Für GPT-5 oder o-Modelle lieber leer lassen, damit der API-Default verwendet wird.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Max Tokens'),
                        'name' => self::CONFIG_MAX_TOKENS,
                        'required' => false,
                        'desc' => $this->l('Optional. Beispiel: 600. Manche Modelle nutzen stattdessen max_completion_tokens.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Top P'),
                        'name' => self::CONFIG_TOP_P,
                        'required' => false,
                        'desc' => $this->l('Optional. Werte zwischen 0 und 1.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Reasoning Effort'),
                        'name' => self::CONFIG_REASONING_EFFORT,
                        'required' => false,
                        'desc' => $this->l('Optional. Beispiel: low, medium oder high.'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Zusätzliche Parameter'),
                        'name' => self::CONFIG_EXTRA_PARAMETERS,
                        'rows' => 6,
                        'cols' => 80,
                        'autoload_rte' => false,
                        'desc' => $this->l('Optionales JSON-Objekt für zusätzliche Modellparameter, z. B. {"max_completion_tokens": 800, "reasoning": {"effort": "medium"}}.'),
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
                        'desc' => $this->l('Platzhalter: {{product_name}}, {{category}} (Standardkategorie), {{brand}} (Destillerie), {{region}} (Region), {{age}} (Alter), {{abv}} (VOL %), {{volume}} (Inhalt), {{vintage}} (Jahrgang). Nicht vorhandene Werte bleiben leer.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Speichern'),
                ),
            ),
        );

        $formHtml = $helper->generateForm(array($fieldsForm));

        $formHtml .= '<script type="text/javascript">(function(){'
            . 'var modelField=document.querySelector("input[name=\"' . self::CONFIG_MODEL . '\"], select[name=\"' . self::CONFIG_MODEL . '\"]");'
            . 'if(!modelField){return;}'
            . 'function findRow(name){var field=document.querySelector("input[name=\""+name+"\"], textarea[name=\""+name+"\"], select[name=\""+name+"\"]");if(!field){return null;}var row=field.closest(".form-group");if(row){return row;}var parent=field.parentElement;while(parent&&parent.tagName!=="FORM"){if(parent.classList&&parent.classList.contains("form-group")){return parent;}parent=parent.parentElement;}return null;}'
            . 'function setRowVisible(name,visible){var row=findRow(name);if(row){row.style.display=visible?"":"none";}}'
            . 'function getField(name){return document.querySelector("input[name=\""+name+"\"], textarea[name=\""+name+"\"], select[name=\""+name+"\"]");}'
            . 'function fillDefaults(model){var isReasoningModel=model.indexOf("gpt-5")!==-1||model.indexOf("gpt-4.1")!==-1||model.indexOf("o1")!==-1||model.indexOf("o3")!==-1||model.indexOf("o4")!==-1;var supportsTopP=model.indexOf("gpt-5")===-1&&model.indexOf("o1")===-1&&model.indexOf("o3")===-1&&model.indexOf("o4")===-1;var usesCompletionTokens=model.indexOf("gpt-5")!==-1||model.indexOf("gpt-4.1")!==-1||model.indexOf("gpt-4o")!==-1||model.indexOf("o1")!==-1||model.indexOf("o3")!==-1||model.indexOf("o4")!==-1;setRowVisible("' . self::CONFIG_REASONING_EFFORT . '",isReasoningModel);setRowVisible("' . self::CONFIG_TOP_P . '",supportsTopP&&!isReasoningModel);var tempField=getField("' . self::CONFIG_TEMPERATURE . '");if(tempField){if(isReasoningModel){tempField.value="";}else if(!tempField.value){tempField.value="0.7";}}var maxField=getField("' . self::CONFIG_MAX_TOKENS . '");if(maxField&&(!maxField.value||maxField.value==="600"||maxField.value==="0")){maxField.value=usesCompletionTokens?"800":"600";}var reasoningField=getField("' . self::CONFIG_REASONING_EFFORT . '");if(reasoningField&&(!reasoningField.value||reasoningField.value==="")){reasoningField.value=isReasoningModel?"medium":"";}var topPField=getField("' . self::CONFIG_TOP_P . '");if(topPField&&(!topPField.value||topPField.value==="")){topPField.value=supportsTopP&&!isReasoningModel?"0.9":"";}var maxRow=findRow("' . self::CONFIG_MAX_TOKENS . '");if(maxRow){var help=maxRow.querySelector(".help-block, .form-control-comment");if(help){help.textContent=usesCompletionTokens?"Optional. Beispiel: 800. Für dieses Modell wird typischerweise max_completion_tokens verwendet.":"Optional. Beispiel: 600. Manche Modelle nutzen stattdessen max_completion_tokens.";}}}'
            . 'function updateParameterFields(){var model=(modelField.value||"").toLowerCase();fillDefaults(model);}'
            . 'modelField.addEventListener("change",updateParameterFields);'
            . 'modelField.addEventListener("input",updateParameterFields);'
            . 'updateParameterFields();'
        . '})();</script>';

        return $formHtml;
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

    public function translateText($text, $targetLanguage = 'English')
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $apiKey = trim((string) Configuration::get(self::CONFIG_API_KEY));
        $model = trim((string) (Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini'));
        $temperature = trim((string) Configuration::get(self::CONFIG_TEMPERATURE));
        if ($temperature === '') {
            $temperature = $this->supportsTemperature($model) ? '0.7' : '';
        }
        $maxTokens = trim((string) Configuration::get(self::CONFIG_MAX_TOKENS));
        $topP = trim((string) Configuration::get(self::CONFIG_TOP_P));
        $reasoningEffort = trim((string) Configuration::get(self::CONFIG_REASONING_EFFORT));
        $extraParameters = trim((string) Configuration::get(self::CONFIG_EXTRA_PARAMETERS));

        if ($apiKey === '') {
            throw new Exception($this->l('Es ist kein OpenAI API Key hinterlegt.'));
        }

        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        if (!function_exists('curl_init')) {
            throw new Exception($this->l('Die PHP-cURL-Erweiterung ist auf dem Server nicht aktiviert.'));
        }

        $translatedLanguage = trim((string) $targetLanguage);
        if ($translatedLanguage === '') {
            $translatedLanguage = 'English';
        }

        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a professional e-commerce translator. Translate the provided text to ' . $translatedLanguage . '. Preserve HTML markup, meaning and tone. Return only the translated text without markdown or explanations.',
                ),
                array(
                    'role' => 'user',
                    'content' => $text,
                ),
            ),
        );

        if ($this->supportsTemperature($model) && $temperature !== '' && is_numeric($temperature)) {
            $payload['temperature'] = (float) $temperature;
        }

        if ($maxTokens !== '' && is_numeric($maxTokens)) {
            if ($this->usesMaxCompletionTokens($model)) {
                $payload['max_completion_tokens'] = (int) $maxTokens;
            } else {
                $payload['max_tokens'] = (int) $maxTokens;
            }
        }

        if ($this->supportsTopP($model) && $topP !== '' && is_numeric($topP)) {
            $payload['top_p'] = (float) $topP;
        }

        if ($reasoningEffort !== '') {
            $payload['reasoning_effort'] = $reasoningEffort;
        }

        if ($extraParameters !== '') {
            $payload = array_replace($payload, $this->parseExtraParameters($extraParameters));
        }

        $attempt = 0;
        $currentPayload = $payload;

        while (true) {
            $attempt++;
            $curl = curl_init('https://api.openai.com/v1/chat/completions');

            curl_setopt_array($curl, array(
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ),
                CURLOPT_POSTFIELDS => json_encode($currentPayload),
            ));

            $response = curl_exec($curl);
            $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($response === false || $curlError) {
                throw new Exception($this->l('OpenAI konnte nicht erreicht werden: ') . $curlError);
            }

            $data = json_decode($response, true);

            if ($statusCode < 400) {
                break;
            }

            $message = isset($data['error']['message']) ? $data['error']['message'] : $this->l('Unbekannter API-Fehler.');
            $unsupportedParameters = $this->extractUnsupportedParameters($message);

            if ($unsupportedParameters === array() || $attempt >= 3) {
                throw new Exception($message);
            }

            $currentPayload = $this->removeUnsupportedParameters($currentPayload, $unsupportedParameters);
        }

        $content = '';
        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim((string) $data['choices'][0]['message']['content']);
        }

        if ($content === '') {
            throw new Exception($this->l('OpenAI hat keine Übersetzung zurückgegeben.'));
        }

        if (preg_match('/```(?:html)?\s*(.*?)```/is', $content, $matches)) {
            $content = trim($matches[1]);
        }

        return $content;
    }

    /**
     * Resolves the prompt placeholders that come from the product itself
     * (default category plus the whisky related product features).
     */
    public function getProductPromptPlaceholders($idProduct, $idLang = null)
    {
        $placeholders = array(
            'category' => '',
            'brand' => '',
            'region' => '',
            'age' => '',
            'abv' => '',
            'volume' => '',
            'vintage' => '',
        );

        $idProduct = (int) $idProduct;
        if ($idProduct <= 0) {
            return $placeholders;
        }

        $idLang = $idLang === null ? (int) $this->context->language->id : (int) $idLang;

        $categoryRow = Db::getInstance()->getRow(
            'SELECT cl.`name`'
            . ' FROM `' . _DB_PREFIX_ . 'product` p'
            . ' INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl'
            . ' ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $idLang . ')'
            . ' WHERE p.`id_product` = ' . (int) $idProduct
        );

        if ($categoryRow && isset($categoryRow['name'])) {
            $placeholders['category'] = trim((string) $categoryRow['name']);
        }

        $featureRows = Db::getInstance()->executeS(
            'SELECT fl.`name` AS feature_name, fvl.`value` AS feature_value'
            . ' FROM `' . _DB_PREFIX_ . 'feature_product` fp'
            . ' INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl'
            . ' ON (fl.`id_feature` = fp.`id_feature` AND fl.`id_lang` = ' . (int) $idLang . ')'
            . ' INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl'
            . ' ON (fvl.`id_feature_value` = fp.`id_feature_value` AND fvl.`id_lang` = ' . (int) $idLang . ')'
            . ' WHERE fp.`id_product` = ' . (int) $idProduct
        );

        foreach ((array) $featureRows as $featureRow) {
            $key = $this->matchFeaturePlaceholder(isset($featureRow['feature_name']) ? $featureRow['feature_name'] : '');
            if ($key === '' || !isset($placeholders[$key]) || $placeholders[$key] !== '') {
                continue;
            }

            $value = trim((string) (isset($featureRow['feature_value']) ? $featureRow['feature_value'] : ''));
            if ($value !== '') {
                $placeholders[$key] = $value;
            }
        }

        return $placeholders;
    }

    protected function matchFeaturePlaceholder($featureName)
    {
        $normalized = Tools::strtolower(trim((string) $featureName));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);

        if ($normalized === '') {
            return '';
        }

        $map = array(
            'destillerie' => 'brand',
            'distillerie' => 'brand',
            'distillery' => 'brand',
            'marke' => 'brand',
            'brand' => 'brand',
            'region' => 'region',
            'herkunft' => 'region',
            'alter' => 'age',
            'age' => 'age',
            'vol' => 'abv',
            'volprozent' => 'abv',
            'alkoholgehalt' => 'abv',
            'abv' => 'abv',
            'inhalt' => 'volume',
            'volumen' => 'volume',
            'fuellmenge' => 'volume',
            'jahrgang' => 'vintage',
            'vintage' => 'vintage',
        );

        return isset($map[$normalized]) ? $map[$normalized] : '';
    }

    protected function applyPromptPlaceholders($template, $productName, array $placeholders = array())
    {
        $values = array_merge(array('product_name' => (string) $productName), $placeholders);

        foreach ($values as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        // Unknown or unresolved placeholders are left empty on purpose.
        return preg_replace('/\{\{\s*[a-z0-9_]+\s*\}\}/i', '', $template);
    }

    public function buildPromptPreview($productName, array $placeholders = array())
    {
        $systemPrompt = trim((string) (Configuration::get(self::CONFIG_SYSTEM_PROMPT) ?: $this->getDefaultSystemPrompt()));
        $promptTemplate = trim((string) (Configuration::get(self::CONFIG_PROMPT_TEMPLATE) ?: $this->getDefaultPromptTemplate()));

        if ($systemPrompt === '') {
            $systemPrompt = $this->getDefaultSystemPrompt();
        }

        if ($promptTemplate === '') {
            $promptTemplate = $this->getDefaultPromptTemplate();
        }

        return array(
            'model' => trim((string) (Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini')),
            'system_prompt' => $systemPrompt,
            'user_prompt' => $this->applyPromptPlaceholders($promptTemplate, $productName, $placeholders),
            'placeholders' => $placeholders,
        );
    }

    public function generateProductDescription($productName, array $placeholders = array())
    {
        $apiKey = trim((string) Configuration::get(self::CONFIG_API_KEY));
        $model = trim((string) (Configuration::get(self::CONFIG_MODEL) ?: 'gpt-4o-mini'));
        $temperature = trim((string) Configuration::get(self::CONFIG_TEMPERATURE));
        if ($temperature === '') {
            $temperature = $this->supportsTemperature($model) ? '0.7' : '';
        }
        $maxTokens = trim((string) Configuration::get(self::CONFIG_MAX_TOKENS));
        $topP = trim((string) Configuration::get(self::CONFIG_TOP_P));
        $reasoningEffort = trim((string) Configuration::get(self::CONFIG_REASONING_EFFORT));
        $extraParameters = trim((string) Configuration::get(self::CONFIG_EXTRA_PARAMETERS));
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

        $userPrompt = $this->applyPromptPlaceholders($promptTemplate, $productName, $placeholders);

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
        );

        if ($this->supportsTemperature($model) && $temperature !== '' && is_numeric($temperature)) {
            $payload['temperature'] = (float) $temperature;
        }

        if ($maxTokens !== '' && is_numeric($maxTokens)) {
            if ($this->usesMaxCompletionTokens($model)) {
                $payload['max_completion_tokens'] = (int) $maxTokens;
            } else {
                $payload['max_tokens'] = (int) $maxTokens;
            }
        }

        if ($this->supportsTopP($model) && $topP !== '' && is_numeric($topP)) {
            $payload['top_p'] = (float) $topP;
        }

        if ($reasoningEffort !== '') {
            $payload['reasoning_effort'] = $reasoningEffort;
        }

        if ($extraParameters !== '') {
            $payload = array_replace($payload, $this->parseExtraParameters($extraParameters));
        }

        $attempt = 0;
        $lastMessage = '';
        $currentPayload = $payload;

        while (true) {
            $attempt++;
            $curl = curl_init('https://api.openai.com/v1/chat/completions');

            curl_setopt_array($curl, array(
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ),
                CURLOPT_POSTFIELDS => json_encode($currentPayload),
            ));

            $response = curl_exec($curl);
            $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($response === false || $curlError) {
                throw new Exception($this->l('OpenAI konnte nicht erreicht werden: ') . $curlError);
            }

            $data = json_decode($response, true);

            if ($statusCode < 400) {
                break;
            }

            $message = isset($data['error']['message']) ? $data['error']['message'] : $this->l('Unbekannter API-Fehler.');
            $unsupportedParameters = $this->extractUnsupportedParameters($message);

            if ($unsupportedParameters === array() || $attempt >= 3) {
                throw new Exception($message);
            }

            $currentPayload = $this->removeUnsupportedParameters($currentPayload, $unsupportedParameters);
            $lastMessage = $message;
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

    protected function refreshModelCache($apiKey)
    {
        if (trim((string) $apiKey) === '') {
            return array();
        }

        $models = $this->fetchAvailableModels($apiKey);
        Configuration::updateValue(self::CONFIG_MODEL_LIST, json_encode($models));

        return $models;
    }

    protected function fetchAvailableModels($apiKey)
    {
        $models = array();
        $apiKey = trim((string) $apiKey);
        if ($apiKey === '') {
            return $models;
        }

        $curl = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $apiKey,
            ),
        ));

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $statusCode >= 400) {
            return $models;
        }

        $data = json_decode($response, true);
        if (!isset($data['data']) || !is_array($data['data'])) {
            return $models;
        }

        foreach ($data['data'] as $modelData) {
            if (!isset($modelData['id']) || !is_string($modelData['id'])) {
                continue;
            }

            $id = $modelData['id'];
            $models[] = array(
                'id' => $id,
                'name' => $id,
            );
        }

        usort($models, function ($left, $right) {
            return strcmp($left['id'], $right['id']);
        });

        return $models;
    }

    protected function getCachedAvailableModels()
    {
        $raw = trim((string) Configuration::get(self::CONFIG_MODEL_LIST));
        if ($raw === '') {
            return array();
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }

        return $decoded;
    }

    protected function supportsTemperature($model)
    {
        $normalized = strtolower((string) $model);

        return strpos($normalized, 'gpt-5') === false
            && strpos($normalized, 'o1') === false
            && strpos($normalized, 'o3') === false
            && strpos($normalized, 'o4') === false;
    }

    protected function supportsTopP($model)
    {
        $normalized = strtolower((string) $model);

        return strpos($normalized, 'gpt-5') === false
            && strpos($normalized, 'o1') === false
            && strpos($normalized, 'o3') === false
            && strpos($normalized, 'o4') === false;
    }

    protected function usesMaxCompletionTokens($model)
    {
        $normalized = strtolower((string) $model);

        return strpos($normalized, 'gpt-5') !== false || strpos($normalized, 'o1') !== false || strpos($normalized, 'o3') !== false || strpos($normalized, 'o4') !== false || strpos($normalized, 'gpt-4.1') !== false || strpos($normalized, 'gpt-4o') !== false;
    }

    protected function extractUnsupportedParameters($message)
    {
        $message = strtolower((string) $message);
        $unsupported = array();
        $supportedParameters = array(
            'temperature',
            'top_p',
            'reasoning_effort',
            'max_tokens',
            'max_completion_tokens',
        );

        if (strpos($message, 'unsupported parameter') === false
            && strpos($message, 'unsupported value') === false
            && strpos($message, 'does not support') === false
            && strpos($message, 'not supported') === false) {
            return $unsupported;
        }

        foreach ($supportedParameters as $parameter) {
            if (strpos($message, $parameter) !== false) {
                $unsupported[] = $parameter;
            }
        }

        return $unsupported;
    }

    protected function removeUnsupportedParameters($payload, $unsupportedParameters)
    {
        foreach ($unsupportedParameters as $parameter) {
            unset($payload[$parameter]);
        }

        return $payload;
    }

    protected function isValidJsonObject($value)
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return true;
        }

        if ($trimmed[0] !== '{') {
            return false;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded);
    }

    protected function parseExtraParameters($value)
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return array();
        }

        if ($trimmed[0] !== '{') {
            throw new Exception($this->l('Die zusätzlichen Parameter müssen als gültiges JSON-Objekt angegeben werden.'));
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            throw new Exception($this->l('Die zusätzlichen Parameter müssen als gültiges JSON-Objekt angegeben werden.'));
        }

        return $decoded;
    }

    protected function getDefaultSystemPrompt()
    {
        return 'Du bist ein professioneller E-Commerce-Texter mit sommelier-artigem Stil und Fokus auf hochwertige Spirituosen, insbesondere Whisky. Erstelle elegante, sensorische und glaubwürdige HTML-Produktbeschreibungen für deutschsprachige Onlineshops. Betone bei Whisky – sofern aus dem Produktnamen plausibel ableitbar – Herkunft, Reifung, Duftbild, Geschmacksprofil, Textur und Nachklang. Wenn Details fehlen, bleibe stilvoll und allgemein, ohne Fakten zu erfinden. Die Sprache soll genussorientiert, präzise und hochwertig sein, aber nie übertrieben oder pathetisch. Antworte ausschließlich mit sauberem HTML ohne Markdown-Codeblöcke.';
    }

    protected function getDefaultPromptTemplate()
    {
        return "Erstelle für das Produkt \"{{product_name}}\" eine sommelier-artige Produktbeschreibung für einen deutschsprachigen Onlineshop mit Fokus auf Whisky und Premium-Spirituosen.\n\n"
            . "Falls vorhanden, nutze diese Zusatzinformationen:\n"
            . "Kategorie: {{category}}\n"
            . "Marke/Destillerie: {{brand}}\n"
            . "Herkunft/Region: {{region}}\n"
            . "Alter: {{age}}\n"
            . "Alkoholgehalt: {{abv}}\n"
            . "Inhalt: {{volume}}\n"
            . "Jahrgang: {{vintage}}\n\n"
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
