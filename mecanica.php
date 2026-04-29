<?php
require_once __DIR__ . '/init.php';

$gameConfig = game_config_load(); // Ya no necesita argumentos
$objetoJuego = trim((string) ($gameConfig['objeto'] ?? ''));
$fechaCorteJuego = trim((string) ($gameConfig['fecha_corte'] ?? ''));

$defaults = game_config_defaults();

$objetoTexto = $objetoJuego !== '' ? $objetoJuego : $defaults['objeto'];
$fechaCorteTexto = $fechaCorteJuego !== '' ? $fechaCorteJuego : $defaults['fecha_corte'];

if ($objetoJuego === '') {
    $objetoJuego = $defaults['objeto'];
}

$pageTitle = 'Mecánica - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="registro-form">
    <div class="registro-header">
        <div></div>
        <img src="img/home.png" />
    </div>
    <div>
        <h2 class="mecanica-title">Mecánica</h2>
        <ol class="mecanica-lista">
            <li>Calcula el número exacto de <b><?php echo e($objetoJuego); ?></b> que hay en la imagen.</li>
            <li>Tienes 30 segundos para observar.</li>
            <li>Posteriormente deberás poner en la línea del costado el número que creas correcto.</li>
            <li>Si tu cálculo es exacto o es uno de los que más se acerca sin exceder el número real de <?php echo e($objetoTexto); ?>, nos pondremos en contacto contigo dentro de las 72 horas posteriores al corte del <?php echo e($fechaCorteTexto); ?>.</li>
            <li>A partir de que des clic en el botón PARTICIPAR empieza a correr el tiempo.</li>
        </ol>
        <p class="mecanica-texto">¿Estas listo?</p>
    </div>


    <a class="btn" href="registro-paso1.php">Participar</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php';
