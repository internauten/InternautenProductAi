<?php

class AdminInternautenProductAiGenerateController extends ModuleAdminController
{
    protected function extractFirstParagraphHtml($content)
    {
        $content = trim((string) $content);
        if ($content === '') {
            return '';
        }

        if (preg_match('/<p\b[^>]*>.*?<\/p>/is', $content, $matches)) {
            return trim((string) $matches[0]);
        }

        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($content)));
        if ($text === '') {
            return '';
        }

        return '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    protected function translate($string)
    {
        if ($this->module && method_exists($this->module, 'l')) {
            return $this->module->l($string, 'AdminInternautenProductAiGenerateController');
        }

        return $string;
    }

    public function __construct()
    {
        $this->bootstrap = false;
        $this->ajax = true;
        $this->display_header = false;
        $this->display_footer = false;
        parent::__construct();
    }

    protected function jsonResponse(array $payload, $statusCode = 200)
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code((int) $statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        die(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function displayAjaxGenerateDescription()
    {
        $this->ajaxProcessGenerateDescription();
    }

    public function displayAjaxGenerateBulkDescriptions()
    {
        $this->ajaxProcessGenerateBulkDescriptions();
    }

    public function displayAjaxSearchBulkProducts()
    {
        $this->ajaxProcessSearchBulkProducts();
    }

    public function ajaxProcessGenerateDescription()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            if (!$this->module || !method_exists($this->module, 'generateProductDescription')) {
                throw new Exception($this->translate('Das Modul konnte nicht geladen werden.'));
            }

            $productName = trim((string) Tools::getValue('product_name'));
            $sourceText = trim((string) Tools::getValue('source_text'));
            $translateTo = trim((string) Tools::getValue('translate_to'));

            if ($sourceText !== '') {
                $description = $this->module->translateText($sourceText, $translateTo !== '' ? $translateTo : 'English');
            } else {
                if ($productName === '') {
                    throw new Exception($this->translate('Der Produktname fehlt.'));
                }

                $description = $this->module->generateProductDescription($productName);
            }

            restore_error_handler();

            $this->jsonResponse(array(
                'success' => true,
                'description' => $description,
                'translated' => $sourceText !== '',
                'target_language' => $translateTo !== '' ? $translateTo : 'English',
            ));
        } catch (Throwable $exception) {
            restore_error_handler();

            $this->jsonResponse(array(
                'success' => false,
                'message' => $exception->getMessage(),
            ), 400);
        }
    }

    public function ajaxProcessGenerateBulkDescriptions()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            if (!$this->module || !method_exists($this->module, 'generateProductDescription')) {
                throw new Exception($this->translate('Das Modul konnte nicht geladen werden.'));
            }

            $rawIds = Tools::getValue('product_ids');
            if (!is_array($rawIds)) {
                $rawIds = $rawIds !== null ? explode(',', (string) $rawIds) : array();
            }

            $productIds = array();
            foreach ($rawIds as $rawId) {
                $idProduct = (int) $rawId;
                if ($idProduct > 0) {
                    $productIds[$idProduct] = $idProduct;
                }
            }
            $productIds = array_values($productIds);

            if (!$productIds) {
                throw new Exception($this->translate('Es wurden keine Produkte ausgewählt.'));
            }

            $idLang = (int) Tools::getValue('id_lang');
            if ($idLang <= 0) {
                $idLang = (int) $this->context->language->id;
            }

            $results = array();
            $successCount = 0;
            $failedCount = 0;

            foreach ($productIds as $idProduct) {
                try {
                    $row = Db::getInstance()->getRow(
                        'SELECT `name`'
                        . ' FROM `' . _DB_PREFIX_ . 'product_lang`'
                        . ' WHERE `id_product` = ' . (int) $idProduct
                        . ' AND `id_lang` = ' . (int) $idLang
                        . ' ORDER BY `id_shop` ASC'
                    );

                    $productName = isset($row['name']) ? trim((string) $row['name']) : '';
                    if ($productName === '') {
                        throw new Exception($this->translate('Produkt nicht gefunden oder ohne Namen.'));
                    }

                    $generatedDescription = $this->module->generateProductDescription($productName);
                    $translatedDescription = $this->module->translateText($generatedDescription, 'English');
                    $finalDescription = $generatedDescription;
                    $shortDescription = $this->extractFirstParagraphHtml($generatedDescription);
                    $translatedShortDescription = $this->extractFirstParagraphHtml($translatedDescription);

                    $updated = (bool) Db::getInstance()->update(
                        'product_lang',
                        array(
                            'description' => pSQL($finalDescription, true),
                            'description_short' => pSQL($shortDescription, true),
                        ),
                        '`id_product` = ' . (int) $idProduct . ' AND `id_lang` = ' . (int) $idLang
                    );

                    if ($translatedDescription !== '') {
                        $englishLang = Db::getInstance()->getRow(
                            'SELECT `id_lang`'
                            . ' FROM `' . _DB_PREFIX_ . 'lang`'
                            . ' WHERE `active` = 1 AND `iso_code` = "en"'
                        );

                        if ($englishLang && isset($englishLang['id_lang'])) {
                            $targetLangId = (int) $englishLang['id_lang'];
                            if ($targetLangId !== $idLang) {
                                Db::getInstance()->update(
                                    'product_lang',
                                    array(
                                        'description' => pSQL($translatedDescription, true),
                                        'description_short' => pSQL($translatedShortDescription, true),
                                    ),
                                    '`id_product` = ' . (int) $idProduct . ' AND `id_lang` = ' . (int) $targetLangId
                                );
                            }
                        }
                    }

                    if (!$updated) {
                        throw new Exception($this->translate('Beschreibung konnte nicht gespeichert werden.'));
                    }

                    $successCount += 1;
                    $results[] = array(
                        'id_product' => (int) $idProduct,
                        'name' => $productName,
                        'success' => true,
                        'message' => $this->translate('Beschreibung aktualisiert.'),
                    );
                } catch (Throwable $exception) {
                    $failedCount += 1;
                    $results[] = array(
                        'id_product' => (int) $idProduct,
                        'name' => isset($productName) ? $productName : '',
                        'success' => false,
                        'message' => $exception->getMessage(),
                    );
                }
            }

            restore_error_handler();

            $this->jsonResponse(array(
                'success' => true,
                'summary' => array(
                    'success' => $successCount,
                    'failed' => $failedCount,
                ),
                'results' => $results,
            ));
        } catch (Throwable $exception) {
            restore_error_handler();

            $this->jsonResponse(array(
                'success' => false,
                'message' => $exception->getMessage(),
            ), 400);
        }
    }

    public function ajaxProcessSearchBulkProducts()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $idLang = (int) Tools::getValue('id_lang');
            if ($idLang <= 0) {
                $idLang = (int) $this->context->language->id;
            }

            $idShop = (int) $this->context->shop->id;
            $queryText = trim((string) Tools::getValue('query'));

            $query = new DbQuery();
            $query->select('p.id_product, pl.name, p.reference, pl.description_short');
            $query->from('product', 'p');
            $query->innerJoin(
                'product_lang',
                'pl',
                'pl.id_product = p.id_product'
                . ' AND pl.id_lang = ' . (int) $idLang
                . ' AND pl.id_shop = ' . (int) $idShop
            );
            $query->where('CHAR_LENGTH(TRIM(COALESCE(pl.description_short, ""))) < 20');

            if ($queryText !== '') {
                $escaped = pSQL($queryText);
                $conditions = array(
                    'pl.name LIKE "%' . $escaped . '%"',
                    'p.reference LIKE "%' . $escaped . '%"',
                );

                if (ctype_digit($queryText)) {
                    $conditions[] = 'p.id_product = ' . (int) $queryText;
                }

                $query->where('(' . implode(' OR ', $conditions) . ')');
                $query->orderBy('pl.name ASC');
            } else {
                $query->orderBy('p.id_product DESC');
            }

            $query->limit(100);

            $rows = Db::getInstance()->executeS($query);
            $products = array();

            foreach ((array) $rows as $row) {
                $idProduct = isset($row['id_product']) ? (int) $row['id_product'] : 0;
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $reference = isset($row['reference']) ? trim((string) $row['reference']) : '';

                if ($idProduct <= 0 || $name === '') {
                    continue;
                }

                $label = '#' . $idProduct . ' - ' . $name;
                if ($reference !== '') {
                    $label .= ' [' . $reference . ']';
                }

                $products[] = array(
                    'id_product' => $idProduct,
                    'name' => $name,
                    'reference' => $reference,
                    'label' => $label,
                );
            }

            restore_error_handler();

            $this->jsonResponse(array(
                'success' => true,
                'products' => $products,
            ));
        } catch (Throwable $exception) {
            restore_error_handler();

            $this->jsonResponse(array(
                'success' => false,
                'message' => $exception->getMessage(),
            ), 400);
        }
    }
}
