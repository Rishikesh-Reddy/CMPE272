<?php
/**
 * NullCastle Systems — db.php
 * Centralised PDO connection helper (PostgreSQL).
 *
 * Usage anywhere in the project:
 *   require_once __DIR__ . '/db.php';
 *   $pdo = get_pdo();          // returns PDO|null
 *
 * Configuration via environment variables (set in .env / server config):
 *   DB_HOST   — default: localhost
 *   DB_PORT   — default: 5432
 *   DB_NAME   — default: nullcastle
 *   DB_USER   — default: postgres
 *   DB_PASS   — (no hardcoded fallback in production)
 *
 * The connection is created once per PHP request (singleton).
 * Errors are written to the PHP error log — never exposed to the browser.
 */

declare(strict_types=1);

/* ----------------------------------------------------------
 *  Internal connector — called only by get_pdo().
 *  Never call this directly from application code.
 * ---------------------------------------------------------- */
function _nc_connect(): ?PDO {
    $host = (string)(getenv('DB_HOST') ?: 'localhost');
    $port = (string)(getenv('DB_PORT') ?: '5432');
    $name = (string)(getenv('DB_NAME') ?: 'nullcastle');
    $user = (string)(getenv('DB_USER') ?: 'postgres');
    $pass = (string)(getenv('DB_PASS') ?: '');   // no hardcoded password

    if ($host === '' || $name === '' || $user === '') {
        error_log('[NullCastle db.php] Missing required DB environment variables.');
        return null;
    }

    // connect_timeout in the DSN is the correct way to set a TCP-level timeout
    // for pgsql — PDO::ATTR_TIMEOUT is silently ignored by the pgsql driver.
    $dsn = "pgsql:host={$host};port={$port};dbname={$name};connect_timeout=5";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => false,   // no persistent connections — safer for web
            PDO::ATTR_EMULATE_PREPARES   => false,   // use native prepared statements
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('[NullCastle db.php] Connection failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Returns the shared PDO instance for this PHP request (singleton).
 * Returns null if the connection could not be established.
 * Subsequent calls within the same request return the cached instance
 * without reconnecting, even if the first attempt failed.
 */
function get_pdo(): ?PDO {
    static $instance = null;
    static $failed   = false;   // don't retry a failed connect within the same request

    if ($failed)            return null;
    if ($instance !== null) return $instance;

    $instance = _nc_connect();
    if ($instance === null) $failed = true;

    return $instance;
}