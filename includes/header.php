<?php
if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}

$gameConfig = game_config_load();
$imagenAuto = isset($gameConfig['imagen_auto']) ? $gameConfig['imagen_auto'] : 'img/home.png';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css?<?=time()?>">
    <script src="scripts.js" defer></script>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-01NX7RNZ7S"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-01NX7RNZ7S');
</script>
</head>
<body>
<header class="site-header">
    <nav class="site-nav">
        <div class="nav-group">
            <a href="mecanica.php">Mecánica</a>
            <a href="registro-paso1.php">Registro</a>
            <a href="tyc.php">Términos y Condiciones</a>
        </div>
        <a class="site-logo" href="home.php" aria-label="Kia Home">
            <img src="img/logo.png" alt="Kia">
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="Abrir menu">
            <span class="menu-toggle-lines">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>
        <div class="nav-group">
            <a href="preguntas-frecuentes.php">Preguntas frecuentes</a>
            <a href="aviso-privacidad.php">Aviso de privacidad</a>
            <a href="ganadores.php">Ganadores</a>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <a href="mecanica.php">Mecánica</a>
            <a href="registro-paso1.php">Registro</a>
            <a href="tyc.php">TyC</a>
            <a href="preguntas-frecuentes.php">Preguntas Frecuentes</a>
            <a href="aviso-privacidad.php">Aviso de privacidad</a>
            <a href="ganadores.php">Ganadores</a>
        </div>
    </nav>
</header>
<main>
    <div class="main-layout">
        <aside class="main-media" aria-hidden="true" style="background-image: url('<?php echo e($imagenAuto); ?>'); background-position: center center; background-size: cover; background-repeat: no-repeat;"></aside>
        <section class="main-content">
