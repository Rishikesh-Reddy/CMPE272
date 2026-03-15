<?php
/**
 * NullCastle Systems — db.php
 * Centralised PDO connection helper.
 * Include this file wherever a DB connection is needed.
 */

function get_pdo(): ?PDO {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'nullcastle';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: 'iamgrooooooooot';

    if (!$host || !$name || !$user) return null;

    try {
        return new PDO(
            "pgsql:host={$host};port={$port};dbname={$name};sslmode=require",
            $user, $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        return null;
    }
}
