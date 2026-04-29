<?php

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function db_error_message(Throwable $error): string
{
    $message = trim($error->getMessage());

    if ($message === '') {
        return 'Error no especificado de MySQL.';
    }

    return $message;
}

function db_table_exists(string $tableName): bool
{
    $stmt = db()->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table LIMIT 1');
    $stmt->execute([
        ':schema' => DB_NAME,
        ':table' => $tableName,
    ]);

    return (bool) $stmt->fetchColumn();
}

function db_registration_status(): ?string
{
    try {
        db();
    } catch (Throwable $error) {
        return 'No se pudo conectar a MySQL usando la base "' . DB_NAME . '". Detalle: ' . db_error_message($error);
    }

    try {
        if (!db_table_exists('registros')) {
            return 'La conexion a MySQL funciona, pero falta la tabla "registros" en la base "' . DB_NAME . '". Revisa o importa el archivo sql/schema.sql.';
        }
    } catch (Throwable $error) {
        return 'Se pudo conectar a MySQL, pero no se pudo validar la tabla "registros". Detalle: ' . db_error_message($error);
    }

    return null;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function old(array $source, string $key): string
{
    return isset($source[$key]) ? trim((string) $source[$key]) : '';
}

function game_config_defaults(): array
{
    return [
        'pregunta' => '¿Dinos cuántas llaves hay en la imagen?',
        'objeto' => 'llaves',
        'imagen' => 'img/home.png',
        'imagen_auto' => 'img/home.png',
        'fecha_corte' => '04 de mayo del 2026',
    ];
}

/**
 * Finds the game configuration file for a given date.
 * Scans the 'data/' directory for files named 'juego-YYYY-MM-DD-YYYY-MM-DD.json'.
 *
 * @param string|null $targetDate The date to check against (YYYY-MM-DD). Defaults to today.
 * @return string|false The full path to the matching config file, or false if none found.
 */
function game_config_file_path(?string $targetDate = null): string|false
{
    $targetDate = $targetDate ?? date('Y-m-d');
    $configDir = __DIR__ . '/data/';
    $files = glob($configDir . 'juego-*-*-*.json'); // Look for files matching the date pattern

    if (empty($files)) {
        return false;
    }

    $targetTimestamp = strtotime($targetDate);

    foreach ($files as $file) {
        $filename = basename($file);
        // Expected format: juego-YYYY-MM-DD-YYYY-MM-DD.json
        if (preg_match('/^juego-(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})\.json$/', $filename, $matches)) {
            $startDate = $matches[1];
            $endDate = $matches[2];

            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate . ' 23:59:59'); // Include the whole end day

            if ($targetTimestamp >= $startTimestamp && $targetTimestamp <= $endTimestamp) {
                return $file; // Found a matching config file
            }
        }
    }

    return false; // No matching config file for the target date
}

/**
 * Lists all game configuration files found in the data/ directory.
 *
 * @return array An array of filenames.
 */
function game_config_list_files(): array
{
    $dataDir = __DIR__ . '/data/';
    $files = glob($dataDir . '*.json');
    if (empty($files)) {
        return [];
    }
    return array_map('basename', $files);
}

function game_config_load(?string $filename = null): array
{
    $defaults = game_config_defaults();
    $filePathToLoad = null;

    if ($filename) {
        // Case 1: A specific filename was requested (e.g., from admin dropdown)
        $filePathToLoad = __DIR__ . '/data/' . basename($filename);
    }
    else {
        // Case 2: No specific filename, try to find the config for the current date
        $filePathToLoad = game_config_file_path(date('Y-m-d'));

        // Case 3: If no date-ranged config, fall back to juego-default.json
        if ($filePathToLoad === false) {
            $defaultJsonPath = __DIR__ . '/data/juego-default.json';
            if (is_file($defaultJsonPath)) {
                $filePathToLoad = $defaultJsonPath;
            } else {
                // Case 4: If even juego-default.json is not found, return hardcoded defaults
                return $defaults;
            }
        }
    }

    // Now attempt to load from the determined filePathToLoad
    if (!is_file($filePathToLoad)) {
        return $defaults; // File not found, return defaults
    }

    $raw = @file_get_contents($filePathToLoad);
    if ($raw === false || trim($raw) === '') {
        return $defaults; // Failed to read or empty file, return defaults
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults; // Invalid JSON, return defaults
    }

    // Merge with defaults to ensure all keys are present, and override with loaded values
    return array_merge($defaults, $decoded);
        return $defaults;
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return $defaults;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults;
    }

    $pregunta = isset($decoded['pregunta']) ? trim((string) $decoded['pregunta']) : '';
    $objeto = isset($decoded['objeto']) ? trim((string) $decoded['objeto']) : '';
    $imagen = isset($decoded['imagen']) ? trim((string) $decoded['imagen']) : '';
    $imagenAuto = isset($decoded['imagen_auto']) ? trim((string) $decoded['imagen_auto']) : '';
    $fechaCorte = isset($decoded['fecha_corte']) ? trim((string) $decoded['fecha_corte']) : '';

    return [
        'pregunta' => $pregunta !== '' ? $pregunta : $defaults['pregunta'],
        'objeto' => $objeto !== '' ? $objeto : $defaults['objeto'],
        'imagen' => $imagen !== '' ? $imagen : $defaults['imagen'],
        'imagen_auto' => $imagenAuto !== '' ? $imagenAuto : $defaults['imagen_auto'],
        'fecha_corte' => $fechaCorte !== '' ? $fechaCorte : $defaults['fecha_corte'],
    ];
}

function game_config_save(array $config, string $filename): bool
{
    $defaults = game_config_defaults();

    $payload = [
        'pregunta' => $config['pregunta'] ?? $defaults['pregunta'],
        'objeto' => $config['objeto'] ?? $defaults['objeto'],
        'imagen' => $config['imagen'] ?? $defaults['imagen'],
        'imagen_auto' => $config['imagen_auto'] ?? $defaults['imagen_auto'],
        'fecha_corte' => $config['fecha_corte'] ?? $defaults['fecha_corte'],
    ];

    $filePath = __DIR__ . '/data/' . basename($filename);
    $dir = dirname($filePath);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    return @file_put_contents($filePath, $json) !== false;
}
