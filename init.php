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

function game_config_file_path(): string
{
    $targetDate = date('Y-m-d');
    $targetDateTime = new DateTime('today');
    $configDir = __DIR__ . '/data/';
    $files = glob($configDir . 'juego-*-*-*.json');

    if ($files) {
        foreach ($files as $file) {
            $filename = basename($file);
            // Busca archivos con el formato: juego-DD-MM-YYYY-DD-MM-YYYY.json
            if (preg_match('/^juego-(\d{2}-\d{2}-\d{4})-(\d{2}-\d{2}-\d{4})\.json$/', $filename, $matches)) {
                $startDate = DateTime::createFromFormat('d-m-Y', $matches[1]);
                $endDate = DateTime::createFromFormat('d-m-Y', $matches[2]);
                
                if ($startDate && $endDate) {
                    $startDate->setTime(0, 0, 0);
                    $endDate->setTime(23, 59, 59);
                    if ($targetDateTime >= $startDate && $targetDateTime <= $endDate) {
                        return $file; // Se encontró un archivo para la fecha actual
                    }
                }
            }
        }
    }

    // Si no se encuentra un archivo por fecha, se usa el archivo por defecto.
    return __DIR__ . '/data/juego-default.json';
}

function game_config_load(): array
{
    $defaults = game_config_defaults();
    $filePath = game_config_file_path();

    if (!is_file($filePath)) {
        return $defaults; // File not found, return defaults
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return $defaults; // Failed to read or empty file, return defaults
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults; // Invalid JSON, return defaults
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

function game_config_save(array $config): bool
{
    $defaults = game_config_defaults();
    $current = game_config_load();

    $pregunta = isset($config['pregunta']) ? trim((string) $config['pregunta']) : $current['pregunta'];
    $objeto = isset($config['objeto']) ? trim((string) $config['objeto']) : $current['objeto'];
    $imagen = isset($config['imagen']) ? trim((string) $config['imagen']) : $current['imagen'];
    $imagenAuto = isset($config['imagen_auto']) ? trim((string) $config['imagen_auto']) : $current['imagen_auto'];
    $fechaCorte = isset($config['fecha_corte']) ? trim((string) $config['fecha_corte']) : $current['fecha_corte'];

    $payload = [
        'pregunta' => $pregunta !== '' ? $pregunta : $defaults['pregunta'],
        'objeto' => $objeto !== '' ? $objeto : $defaults['objeto'],
        'imagen' => $imagen !== '' ? $imagen : $defaults['imagen'],
        'imagen_auto' => $imagenAuto !== '' ? $imagenAuto : $defaults['imagen_auto'],
        'fecha_corte' => $fechaCorte !== '' ? $fechaCorte : $defaults['fecha_corte'],
    ];

    $filePath = game_config_file_path();
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
