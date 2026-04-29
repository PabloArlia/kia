<?php
require_once __DIR__ . '/init.php';

$gameConfig = game_config_load(); // Ya no necesita argumentos
$objetoJuego = trim((string) ($gameConfig['objeto'] ?? ''));
if ($objetoJuego === '') {
    $objetoJuego = game_config_defaults()['objeto'];
}

$pageTitle = 'Home - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<img class="home" src="img/home.png" />
<div class="home-spacer" aria-hidden="true"></div>
<p class="amarillo">Kia te acerca al mundial</p>

<p class="home-texto">Con la compra de tu Kia participa para poder ganar un boleto doble para un partido de la FIFA World Cup 2026 <sup>TM</sup></p>

<p class="home-texto">Regístrate y calcula el número exacto de <b><?php echo e($objetoJuego); ?></b> que hay en la imagen.</p>
<a class="btn" href="registro-paso1.php">Comenzar</a>
<?php require_once __DIR__ . '/includes/footer.php';
