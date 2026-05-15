<?php
declare(strict_types=1);

/**
 * Hub tracker loader for NullCastle (local XAMPP).
 * Override hub URL via marketplace-config.local.php: return ['hub_base' => 'http://localhost/.../public'];
 */

if (!defined('MP_SITE_ID')) {
    define('MP_SITE_ID', 'nullcastle');
}

if (!defined('MP_HUB_BASE')) {
    $local = __DIR__ . '/marketplace-config.local.php';
    if (is_readable($local)) {
        $cfg = require $local;
        if (is_array($cfg) && !empty($cfg['hub_base']) && is_string($cfg['hub_base'])) {
            define('MP_HUB_BASE', rtrim($cfg['hub_base'], '/'));
        }
    }
}
if (!defined('MP_HUB_BASE')) {
    $fromEnv = getenv('MP_HUB_BASE');
    if ($fromEnv === false || $fromEnv === '') {
        $fromEnv = (string) ($_SERVER['MP_HUB_BASE'] ?? '');
    }
    if ($fromEnv !== '') {
        define('MP_HUB_BASE', rtrim($fromEnv, '/'));
    }
}
if (!defined('MP_HUB_BASE')) {
    define('MP_HUB_BASE', 'http://localhost/marketplace/marketplace-hub/public');
}

function mp_script_tag(string $siteId, string $productId): string
{
    $src = htmlspecialchars(MP_HUB_BASE . '/assets/mp-client.js', ENT_QUOTES, 'UTF-8');
    $sid = htmlspecialchars($siteId, ENT_QUOTES, 'UTF-8');
    $pid = htmlspecialchars($productId, ENT_QUOTES, 'UTF-8');

    return '<script src="' . $src . '" data-mp-site="' . $sid . '" data-mp-product="' . $pid . '" defer></script>';
}
