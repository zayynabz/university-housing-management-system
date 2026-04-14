<?php
// Configuration base de donnees.
// Valeurs par defaut adaptees a un environnement local XAMPP/WAMP/Laragon.

function envValue(string $key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : $value;
}

define('DB_HOST', (string) envValue('DB_HOST', 'localhost'));
define('DB_NAME', (string) envValue('DB_NAME', 'universite_db'));
define('DB_USER', (string) envValue('DB_USER', 'root'));
define('DB_PASS', (string) envValue('DB_PASS', ''));
define('DB_CHARSET', (string) envValue('DB_CHARSET', 'utf8mb4'));
define('APP_BASE_PATH', (string) envValue('APP_BASE_PATH', ''));

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Connexion DB echouee : ' . $e->getMessage());
        }
    }

    return $pdo;
}

function normalizeBasePath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));

    if ($path === '' || $path === '/') {
        return '';
    }

    $path = '/' . trim($path, '/');
    return $path === '/' ? '' : $path;
}

function detectBasePathFromScript(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $directory = str_replace('\\', '/', dirname($scriptName));
    $directory = $directory === '.' ? '' : $directory;

    $knownFolders = ['/admin', '/auth', '/resident', '/technicien', '/config', '/assets', '/docs'];
    foreach ($knownFolders as $folder) {
        if ($directory === $folder) {
            return '';
        }
        if (substr($directory, -strlen($folder)) === $folder) {
            $directory = substr($directory, 0, -strlen($folder));
            break;
        }
    }

    return normalizeBasePath($directory);
}

function appBasePath(): string
{
    if (APP_BASE_PATH !== '') {
        return normalizeBasePath(APP_BASE_PATH);
    }

    return detectBasePathFromScript();
}

function appUrl(string $path = ''): string
{
    $basePath = appBasePath();
    $path = '/' . ltrim($path, '/');

    return $basePath . $path;
}
