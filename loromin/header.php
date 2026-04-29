<?php
if (!isset($title)) {
  $titlehead = 'Kia';
} else {
  $titlehead = $title.' - Kia';
}
$file = basename($_SERVER["SCRIPT_FILENAME"],'.php');
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="<?=urladmin?>img/logo.png" type="image/x-icon">
    <title><?=$titlehead?></title>
	<script src="<?=urladmin?>tabler/jquery.min.js"></script>
	<script src="<?=urladmin?>tabler/tabler.min.js"></script>
	<link rel="stylesheet" href="<?=urladmin?>tabler/tabler.min.css<?=(debug)?'?'.time():''?>">
  </head>
  <body data-bs-theme="dark" class="pg_<?=$file?>">
	<?php if(!in_array($file,array('index'))): ?>
	<div class="page">
	  <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
          <div class="navbar">
            <div class="container-xl">
              <div class="row flex-fill align-items-center">
                <div class="col">
                  <ul class="navbar-nav">
                    <li class="nav-item <?=in_array($file,array('usuarios','conversacion'))?'active':''?>">
                      <a class="nav-link" href="<?=urladmin?>usuarios.php">
                        <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                          <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-line"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 10.663c0 -4.224 -4.041 -7.663 -9 -7.663s-9 3.439 -9 7.663c0 3.783 3.201 6.958 7.527 7.56c1.053 .239 .932 .644 .696 2.133c-.039 .238 -.184 .932 .777 .512c.96 -.42 5.18 -3.201 7.073 -5.48c1.304 -1.504 1.927 -3.029 1.927 -4.715v-.01z" /></svg>
                        </span>
                        <span class="nav-link-title">
                          Usuarios
                        </span>
                      </a>
                    </li>
                    <li class="nav-item <?=in_array($file,array('limpiar'))?'active':''?>">
                      <a class="nav-link" href="<?=urladmin?>limpiar.php">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7l0 -3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1l0 3" /></svg>
                        </span>
                        <span class="nav-link-title">
                          Limpiar
                        </span>
                      </a>
                    </li>
                    <li class="nav-item <?=in_array($file,array('juego-config'))?'active':''?>">
                      <a class="nav-link" href="<?=urladmin?>juego-config.php">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-photo-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M19.5 19.4l-3.8 -3.8" /><path d="M5 19a1 1 0 0 1 -1 -1v-12a1 1 0 0 1 1 -1h14a1 1 0 0 1 1 1v7" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l4 4" /><path d="M14 14l1 -1c.67 -.644 1.379 -.824 2.06 -.541" /><path d="M18 22a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" /></svg>
                        </span>
                        <span class="nav-link-title">
                          Juego
                        </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a target="_blank" class="nav-link" href="<?=urladmin?>../">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-external-link"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 7h-5a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2v-5" /><path d="M10 14l10 -10" /><path d="M15 4l5 0l0 5" /></svg>
                        </span>
                        <span class="nav-link-title">
                          Ver promo
                        </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>
	  <?php endif; ?>
	<div class="page page-center">
	  <div class="">
        <div class="page-body">
          <div class="container-xl">