<?php
require_once __DIR__ . '/init.php';

$pageTitle = 'Terminos y Condiciones - ' . APP_NAME;

require_once __DIR__ . '/includes/header.php';
?>
<div class="faq-scroll aviso-privacidad tyc-legal">
	<h2 class="mecanica-title">Términos y Condiciones</h2>
	<div class="tyc-legal-content">
		<div class="WordSection1">
<?php
// --- Lógica para cargar el T&C correcto por fecha ---

$tycFilePath = '';
$targetDate = new DateTime('today');
$tycDir = __DIR__ . '/tyc/';
$files = glob($tycDir . 'juego-*.html');

if ($files) {
    foreach ($files as $file) {
        $filename = basename($file);
        // Busca archivos con el formato: juego-DD-MM-YYYY-DD-MM-YYYY.html
        if (preg_match('/^juego-(\d{2}-\d{2}-\d{4})-(\d{2}-\d{2}-\d{4})\.html$/', $filename, $matches)) {
            $startDate = DateTime::createFromFormat('d-m-Y', $matches[1]);
            $endDate = DateTime::createFromFormat('d-m-Y', $matches[2]);

            // Asegurarse que las fechas son válidas y continuar solo si lo son
            if ($startDate && $endDate) {
                $startDate->setTime(0, 0, 0);
                $endDate->setTime(23, 59, 59);

                if ($targetDate >= $startDate && $targetDate <= $endDate) {
                    $tycFilePath = $file; // Se encontró un archivo para la fecha actual
                    break;
                }
            }
        }
    }
}

// Si no se encuentra un archivo por fecha, se usa el archivo por defecto.
if ($tycFilePath === '') {
    $tycFilePath = $tycDir . 'juego-default.html';
}

if (is_file($tycFilePath)) {
    echo file_get_contents($tycFilePath);
} else {
    echo '<p>No se encontraron los términos y condiciones.</p>';
}
// --- Fin de la lógica de carga ---
?>
		</div>
	</div>
<?php require_once __DIR__ . '/includes/footer.php';
