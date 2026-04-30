<?php
require_once '../init.php';

if (empty($_SESSION['admin'])) {
    header('Location: ' . urladmin . 'index.php');
    exit;
}

$tycDir = dirname(__DIR__) . '/tyc/';
$error = null;
$success = null;
$content = '';

// --- Lógica para listar y cargar archivos de T&C ---

// Obtener todos los archivos .html de la carpeta /tyc/
$htmlFiles = glob($tycDir . '*.html');
$availableFiles = [];
foreach ($htmlFiles as $file) {
    $availableFiles[] = basename($file);
}

// Ordenar los archivos por fecha ascendente, igual que en juego-config.php
usort($availableFiles, function ($a, $b) {
    preg_match('/(\d{2}-\d{2}-\d{4})/', $a, $matchesA);
    preg_match('/(\d{2}-\d{2}-\d{4})/', $b, $matchesB);

    $dateA = $matchesA[1] ?? null;
    $dateB = $matchesB[1] ?? null;

    // default primero
    if (!$dateA) return -1;
    if (!$dateB) return 1;

    $timestampA = DateTime::createFromFormat('d-m-Y', $dateA)->getTimestamp();
    $timestampB = DateTime::createFromFormat('d-m-Y', $dateB)->getTimestamp();

    return $timestampA <=> $timestampB;
});

// Obtener el archivo seleccionado del parámetro GET
$selectedFile = $_GET['file'] ?? ($availableFiles[0] ?? null);

if ($selectedFile && in_array($selectedFile, $availableFiles, true)) {
    $filePath = $tycDir . basename($selectedFile);
    $raw = @file_get_contents($filePath);
    if ($raw !== false) {
        $content = $raw;
    } else {
        $error = "No se pudo leer el archivo " . htmlspecialchars($selectedFile) . ".";
    }
} elseif (!empty($availableFiles)) {
    $error = "El archivo seleccionado no es válido.";
}

// --- Fin de la lógica de carga ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileToSave = $_POST['selected_file'] ?? null;
    $newContent = $_POST['content'] ?? '';

    if ($fileToSave && in_array($fileToSave, $availableFiles, true)) {
        $savePath = $tycDir . basename($fileToSave);

        if (@file_put_contents($savePath, $newContent) !== false) {
            $success = 'Contenido guardado correctamente para ' . htmlspecialchars($fileToSave);
            $content = $newContent; // Actualizar el contenido en la vista
        } else {
            $error = 'No se pudo guardar el contenido en el archivo ' . htmlspecialchars($fileToSave) . '.';
        }
    } else {
        $error = 'Archivo de T&C no válido para guardar.';
    }
}

$title = 'Editar Términos y Condiciones';
include 'header.php';
?>

<div class="page-body">
    <div class="container-xl">
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label" for="tyc_file_selector">Seleccionar archivo de T&C</label>
                        <select class="form-select" id="tyc_file_selector" onchange="if(this.value) window.location.href = 'editar-tyc.php?file=' + this.value;">
                            <?php if (empty($availableFiles)): ?>
                                <option>No hay archivos en la carpeta /tyc/</option>
                            <?php else: ?>
                                <?php foreach ($availableFiles as $file): ?>
                                    <option value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= ($selectedFile === $file) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <input type="hidden" name="selected_file" value="<?= htmlspecialchars($selectedFile, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label" for="content">Contenido del archivo</label>
                        <textarea class="form-control" id="content" name="content" rows="20" style="font-family: monospace;"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" <?= empty($availableFiles) ? 'disabled' : '' ?>>Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>