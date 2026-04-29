<?php
require_once __DIR__ . '/init.php';

if (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', (string) $_GET['id'])) {
    header('Location: juego.php');
    exit;
}

$registro = null;

try {
    $stmt = db()->prepare('SELECT numero_ingresado, created_at FROM juego_resultados WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_GET['id']]);
    $registro = $stmt->fetch();
} catch (Throwable $e) {
    $registro = [
        'db_error' => db_error_message($e),
    ];
}
if (!$registro) {
    header('Location: home.php');
    exit;
}

$gameConfig = game_config_load();
$objetoJuego = (string) $gameConfig['objeto'];

$objetoTexto = trim($objetoJuego) !== '' ? $objetoJuego : game_config_defaults()['objeto'];

$pageTitle = 'Resolución - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<img class="home" src="img/home.png" />
<div class="home-spacer" aria-hidden="true"></div>
<p class="amarillo2">¡Gracias por haber participado!<br/>
Tu respuesta fue <?php echo e((string) $registro['numero_ingresado']); ?> <?php echo e($objetoTexto); ?>.</p>

<p class="home-texto">Sí tu cálculo es exacto o uno de los más cercanos al real, nos pondremos en contacto contigo al finalizar el corte semanal dentro de las 72 horas siguientes.</p>

<?php require_once __DIR__ . '/includes/footer.php';
