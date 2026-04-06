<?php

if (!ob_get_level()) {
    ob_start();
}

@ini_set('display_errors', '0');

function internautenJsonResponse(array $payload, $statusCode = 200)
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

function internautenFindPrestashopRoot()
{
    $candidates = array(
        isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') : '',
        dirname(__FILE__, 2),
        dirname(__FILE__, 3),
        getcwd(),
        '/var/www/html',
        '/usr/src/prestashop',
    );

    foreach (array_unique(array_filter($candidates)) as $candidate) {
        if (is_file($candidate . '/config/config.inc.php') && is_file($candidate . '/init.php')) {
            return $candidate;
        }
    }

    return null;
}

$prestashopRoot = internautenFindPrestashopRoot();
if (!$prestashopRoot) {
    internautenJsonResponse(array(
        'success' => false,
        'message' => 'PrestaShop-Stammverzeichnis konnte nicht gefunden werden.',
    ), 500);
}

require_once $prestashopRoot . '/config/config.inc.php';
require_once $prestashopRoot . '/init.php';
require_once dirname(__FILE__) . '/internautenproductai.php';

function internautenTranslate($string)
{
    static $module = null;

    if ($module === null) {
        $module = Module::getInstanceByName('internautenproductai');
    }

    if ($module && method_exists($module, 'l')) {
        return $module->l($string, 'ajax');
    }

    return $string;
}

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    $expectedToken = InternautenProductAi::getAjaxToken();
    $token = (string) Tools::getValue('token');

    if ($token === '' || !hash_equals($expectedToken, $token)) {
        throw new Exception(internautenTranslate('Ungültiges Sicherheitstoken. Bitte die Admin-Seite neu laden.'));
    }

    if ((string) Tools::getValue('action') !== 'GenerateDescription') {
        throw new Exception(internautenTranslate('Ungültige Aktion.'));
    }

    $productName = trim((string) Tools::getValue('product_name'));
    if ($productName === '') {
        throw new Exception(internautenTranslate('Der Produktname fehlt.'));
    }

    $module = Module::getInstanceByName('internautenproductai');
    if (!$module || !method_exists($module, 'generateProductDescription')) {
        throw new Exception(internautenTranslate('Das Modul konnte nicht geladen werden.'));
    }

    $description = $module->generateProductDescription($productName);
    restore_error_handler();

    internautenJsonResponse(array(
        'success' => true,
        'description' => $description,
    ));
} catch (Throwable $exception) {
    restore_error_handler();

    internautenJsonResponse(array(
        'success' => false,
        'message' => $exception->getMessage(),
    ), 400);
}
