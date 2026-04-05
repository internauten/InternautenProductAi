<?php

class AdminInternautenProductAiGenerateController extends ModuleAdminController
{
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
                throw new Exception('Das Modul konnte nicht geladen werden.');
            }

            $productName = trim((string) Tools::getValue('product_name'));
            if ($productName === '') {
                throw new Exception('Der Produktname fehlt.');
            }

            $description = $this->module->generateProductDescription($productName);
            restore_error_handler();

            $this->jsonResponse(array(
                'success' => true,
                'description' => $description,
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
