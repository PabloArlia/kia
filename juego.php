<?php
require_once __DIR__ . '/init.php';

define('JUEGO_TIEMPO_SEGUNDOS', 31);

if (!isset($_SESSION['registro_id'])) {
    header('Location: registro-paso1.php');
    exit;
}

$resultadoExistenteId = null;

try {
    $stmtResultado = db()->prepare('SELECT id FROM juego_resultados WHERE registro_id = :registro_id ORDER BY id DESC LIMIT 1');
    $stmtResultado->execute([
        ':registro_id' => (int) $_SESSION['registro_id'],
    ]);
    $resultadoExistenteId = $stmtResultado->fetchColumn();
} catch (Throwable $e) {
    $resultadoExistenteId = null;
}

if ($resultadoExistenteId) {
    header('Location: resolucion.php?id=' . (int) $resultadoExistenteId);
    exit;
}

$gameConfig = game_config_load(); // Ya no necesita argumentos
$preguntaJuego = (string) $gameConfig['pregunta'];
$imagenJuego = (string) $gameConfig['imagen'];

$preguntaJuegoHtml = preg_replace_callback(
    '/\*(.+?)\*/u',
    static function (array $matches): string {
        return '<strong>' . e($matches[1]) . '</strong>';
    },
    e($preguntaJuego)
);

if ($preguntaJuegoHtml === null) {
    $preguntaJuegoHtml = e($preguntaJuego);
}

if (!is_file(__DIR__ . '/' . ltrim($imagenJuego, '/'))) {
    $imagenJuego = game_config_defaults()['imagen'];
}

$errors = [];
$numero = '';

if (!isset($_SESSION['juego_inicio_ts'])) {
    $_SESSION['juego_inicio_ts'] = time();
}

$juegoInicioTs = (int) $_SESSION['juego_inicio_ts'];
$segundosRestantes = max(0, JUEGO_TIEMPO_SEGUNDOS - (time() - $juegoInicioTs));
$segundosRestantesTexto = str_pad((string) (int) $segundosRestantes, 2, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = old($_POST, 'numero');
    $tiempoAgotado = (time() - $juegoInicioTs) >= JUEGO_TIEMPO_SEGUNDOS;
    $numeroGuardado = 0;

    if (!$tiempoAgotado && ($numero === '' || !preg_match('/^-?[0-9]+$/', $numero))) {
        $errors[] = 'Ingresa un numero valido.';
    }

    if (!$tiempoAgotado) {
        $numeroGuardado = (int) $numero;
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('INSERT INTO juego_resultados (registro_id, numero_ingresado) VALUES (:registro_id, :numero)');
            $stmt->execute([
                ':registro_id' => (int) $_SESSION['registro_id'],
                ':numero' => $numeroGuardado,
            ]);

            $resultadoId = (int) db()->lastInsertId();
            unset($_SESSION['juego_inicio_ts']);
            header('Location: resolucion.php?id=' . $resultadoId);
            exit;
        } catch (Throwable $e) {
            $errors[] = 'No se pudo guardar el resultado del juego. Detalle: ' . db_error_message($e);
        }
    }
}

$pageTitle = 'Juego - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" action="juego.php" id="juego-form">
    <div class="registro-header">
        <h2><span class="timer" id="timer-header"><?php echo e($segundosRestantesTexto); ?></span></h2>
        <img src="img/home.png" />
    </div>
    <p class="home-texto"><?php echo $preguntaJuegoHtml; ?></p>
    <img class="juego-imagen" src="<?php echo e($imagenJuego); ?>" alt="Imagen del juego" />
    <div class="field">
        <input type="number" id="numero" name="numero" value="<?php echo e($numero); ?>" required placeholder="Ingresa el número que calculaste" <?php echo isset($errors[0]) ? 'title="' . e($errors[0]) . '" aria-invalid="true"' : ''; ?> min="-999999999" max="999999999">
    </div>
    <div class="actions">
        <button class="btn" type="submit" id="enviar-btn">Enviar</button>
    </div>
</form>

<script>
(function () {
    var segundos = <?php echo (int) $segundosRestantes; ?>;
    var timerEl = document.getElementById('timer');
    var timerHeaderEl = document.getElementById('timer-header');
    var formEl = document.getElementById('juego-form');
    var btnEl = document.getElementById('enviar-btn');
    var autoSubmitted = false;

    function formatearSegundos(valor) {
        var n = Number(valor);
        if (n < 10) {
            return '0' + String(n);
        }
        return String(n);
    }

    function actualizarReloj() {
        var texto = '00:' + formatearSegundos(segundos);
        if (timerEl) {
            timerEl.textContent = texto;
        }
        if (timerHeaderEl) {
            timerHeaderEl.textContent = texto;
        }
    }

    function enviarPorTiempo() {
        if (autoSubmitted) {
            return;
        }
        autoSubmitted = true;
        btnEl.disabled = true;
        formEl.submit();
    }

    if (segundos <= 0) {
        segundos = 0;
        actualizarReloj();
        enviarPorTiempo();
        return;
    }

    actualizarReloj();

    var interval = setInterval(function () {
        segundos -= 1;
        if (segundos < 0) {
            segundos = 0;
        }
        actualizarReloj();

        if (segundos === 0) {
            clearInterval(interval);
            enviarPorTiempo();
        }
    }, 1000);
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php';
