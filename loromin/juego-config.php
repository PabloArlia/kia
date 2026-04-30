<?php
require_once '../init.php';

if (empty($_SESSION['admin'])) {
    header('Location: ' . urladmin . 'index.php');
    exit;
}

$configDir = dirname(__DIR__) . '/data/';
$defaults = game_config_defaults();
$error = null;
$success = null;

// --- Lógica para listar y cargar archivos de configuración ---

// Obtener todos los archivos .json de la carpeta /data/
$jsonFiles = glob($configDir . '*.json');
$availableFiles = [];
foreach ($jsonFiles as $file) {
    $availableFiles[] = basename($file);
}

// Ordenar los archivos por fecha ascendente.
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

// Get the selected file from GET parameter, or null if not set
$selectedFile = $_GET['file'] ?? null;
$config = [];

if ($selectedFile && in_array($selectedFile, $availableFiles, true)) {
    $filePath = $configDir . basename($selectedFile);
    $raw = @file_get_contents($filePath);
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $config = array_merge($defaults, $decoded);
        } else {
            $error = "El archivo " . htmlspecialchars($selectedFile) . " contiene JSON inválido.";
            $config = $defaults;
        }
    } else {
        $error = "No se pudo leer el archivo " . htmlspecialchars($selectedFile) . ".";
        $config = $defaults;
    }
} else {
    // Si no hay archivo seleccionado o no es válido, cargamos el que corresponde por fecha o el default.
    // Esta es la única función que necesitamos de init.php para saber cuál es el activo.
    $config = game_config_load();
    $selectedFile = basename(game_config_file_path());
}

// --- Fin de la lógica de carga ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = trim((string) ($_POST['pregunta'] ?? ''));
    $objeto = trim((string) ($_POST['objeto'] ?? ''));
    $fechaCorte = trim((string) ($_POST['fecha_corte'] ?? ''));
    $periodo = trim((string) ($_POST['periodo'] ?? ''));
    $imagenConfig = $config['imagen'];
    $imagenAutoConfig = $config['imagen_auto'];

    if ($pregunta === '') {
        $error = 'La pregunta es obligatoria.';
    } elseif ($objeto === '') {
        $error = 'El objeto es obligatorio.';
    } elseif ($fechaCorte === '') {
        $error = 'La fecha de corte es obligatoria.';
    } elseif ($periodo === '') {
        $error = 'El periodo es obligatorio.';
    }

    if (!$error && isset($_FILES['imagen']) && is_array($_FILES['imagen']) && (int) ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = (int) ($_FILES['imagen']['error'] ?? UPLOAD_ERR_OK);

        if ($uploadError !== UPLOAD_ERR_OK) {
            $error = 'No se pudo subir la imagen.';
        } else {
            $tmpName = (string) ($_FILES['imagen']['tmp_name'] ?? '');
            $fileName = (string) ($_FILES['imagen']['name'] ?? '');
            $fileSize = (int) ($_FILES['imagen']['size'] ?? 0);

            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $error = 'Archivo de imagen inválido.';
            } elseif ($fileSize <= 0 || $fileSize > (4 * 1024 * 1024)) {
                $error = 'La imagen debe pesar máximo 4 MB.';
            } else {
                $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

                if (!in_array($extension, $permitidas, true)) {
                    $error = 'Formato no permitido. Usa JPG, PNG, WEBP, GIF o AVIF.';
                } else {
                    $mime = (string) (@mime_content_type($tmpName) ?: '');
                    $mimesValidos = [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                        'image/avif',
                    ];

                    if ($mime !== '' && !in_array($mime, $mimesValidos, true)) {
                        $error = 'El archivo no parece ser una imagen válida.';
                    } else {
                        $uploadsDir = dirname(__DIR__) . '/img/juego';
                        if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
                            $error = 'No se pudo crear la carpeta de imágenes del juego.';
                        } else {
                            $safeExt = $extension === 'jpeg' ? 'jpg' : $extension;
                            $newFileName = 'pregunta_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
                            $targetPath = $uploadsDir . '/' . $newFileName;

                            if (!move_uploaded_file($tmpName, $targetPath)) {
                                $error = 'No se pudo guardar la imagen subida.';
                            } else {
                                $imagenConfig = 'img/juego/' . $newFileName;
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$error && isset($_FILES['imagen_auto']) && is_array($_FILES['imagen_auto']) && (int) ($_FILES['imagen_auto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = (int) ($_FILES['imagen_auto']['error'] ?? UPLOAD_ERR_OK);

        if ($uploadError !== UPLOAD_ERR_OK) {
            $error = 'No se pudo subir la imagen del auto.';
        } else {
            $tmpName = (string) ($_FILES['imagen_auto']['tmp_name'] ?? '');
            $fileName = (string) ($_FILES['imagen_auto']['name'] ?? '');
            $fileSize = (int) ($_FILES['imagen_auto']['size'] ?? 0);

            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $error = 'Archivo de imagen del auto inválido.';
            } elseif ($fileSize <= 0 || $fileSize > (4 * 1024 * 1024)) {
                $error = 'La imagen del auto debe pesar máximo 4 MB.';
            } else {
                $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

                if (!in_array($extension, $permitidas, true)) {
                    $error = 'Formato no permitido. Usa JPG, PNG, WEBP, GIF o AVIF.';
                } else {
                    $mime = (string) (@mime_content_type($tmpName) ?: '');
                    $mimesValidos = [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                        'image/avif',
                    ];

                    if ($mime !== '' && !in_array($mime, $mimesValidos, true)) {
                        $error = 'El archivo no parece ser una imagen válida.';
                    } else {
                        $uploadsDir = dirname(__DIR__) . '/img';
                        if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
                            $error = 'No se pudo crear la carpeta de imágenes.';
                        } else {
                            $safeExt = $extension === 'jpeg' ? 'jpg' : $extension;
                            $newFileName = 'auto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
                            $targetPath = $uploadsDir . '/' . $newFileName;

                            if (!move_uploaded_file($tmpName, $targetPath)) {
                                $error = 'No se pudo guardar la imagen del auto.';
                            } else {
                                $imagenAutoConfig = 'img/' . $newFileName;
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$error) {
        $nuevoConfig = [
            'pregunta' => $pregunta,
            'objeto' => $objeto,
            'imagen' => $imagenConfig,
            'imagen_auto' => $imagenAutoConfig,
            'fecha_corte' => $fechaCorte,
            'periodo' => $periodo,
        ];

        // --- Lógica para guardar el archivo de configuración ---
        $fileToSave = $_POST['selected_file'] ?? null;
        if ($fileToSave && in_array($fileToSave, $availableFiles, true)) {
            $savePath = $configDir . basename($fileToSave);
            $json = json_encode($nuevoConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($json !== false && @file_put_contents($savePath, $json) !== false) {
                $success = 'Configuración guardada correctamente para ' . htmlspecialchars($fileToSave);
                $config = array_merge($defaults, $nuevoConfig); // Recargar datos en la vista
            } else {
                $error = 'No se pudo guardar la configuración en el archivo ' . htmlspecialchars($fileToSave) . '.';
            }
        // --- Fin de la lógica de guardado ---
        } else {
            $error = 'Archivo de configuración no válido para guardar.';
        }
    } else {
        $config['pregunta'] = $pregunta;
        $config['objeto'] = $objeto;
        $config['fecha_corte'] = $fechaCorte;
        $config['periodo'] = $periodo;
        // Mantener las imágenes aunque haya otro error
        $config['imagen'] = $imagenConfig;
        $config['imagen_auto'] = $imagenAutoConfig;
    }
}

$title = 'Configuración del juego';
include 'header.php';
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Configuración del juego</h2>
                <div class="mb-3">
                    <label class="form-label" for="config_file_selector">Seleccionar archivo de configuración</label>
                    <select class="form-select" id="config_file_selector" onchange="if(this.value) window.location.href = 'juego-config.php?file=' + this.value;">
                        <?php foreach ($availableFiles as $file): ?>
                            <option value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($selectedFile === $file) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">
                        El archivo actualmente cargado es: <strong><?= htmlspecialchars($selectedFile, ENT_QUOTES, 'UTF-8') ?></strong>.
                        Si no se selecciona uno, se carga el archivo por fecha o el por defecto.
                    </small>
                </div>
                <div class="text-muted">Administra pregunta, objeto, imagenes e imagen del auto sin usar base de datos.</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="selected_file" value="<?= htmlspecialchars($selectedFile, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label" for="pregunta">Pregunta</label>
                        <textarea class="form-control" id="pregunta" name="pregunta" rows="3" required><?= htmlspecialchars((string) $config['pregunta'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        <small class="form-hint">Tip: usa *texto* para mostrarlo en negrita en el juego. Ejemplo: ¿Cuántas *llaves* hay en la imagen?</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="objeto">Objeto</label>
                        <input class="form-control" type="text" id="objeto" name="objeto" value="<?= htmlspecialchars((string) $config['objeto'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <small class="form-hint">Ejemplo: llaves, pelotas, conos.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="fecha_corte">Fecha de corte</label>
                        <input class="form-control" type="text" id="fecha_corte" name="fecha_corte" value="<?= htmlspecialchars((string) ($config['fecha_corte'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        <small class="form-hint">Ejemplo: 04 de mayo de 2026.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="periodo">Periodo</label>
                        <input class="form-control" type="text" id="periodo" name="periodo" value="<?= htmlspecialchars((string) ($config['periodo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        <small class="form-hint">Ejemplo: del 21 de abril al 30 de abril de 2026.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="imagen">Imagen del juego (opcional)</label>
                        <input class="form-control" type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp,.gif,.avif,image/*">
                        <small class="form-hint">Si no subes una nueva imagen, se conserva la actual.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Imagen actual del juego</label>
                        <img src="<?= htmlspecialchars('../' . (string) $config['imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="Imagen del juego" style="max-width: 260px; width: 100%; height: auto; border: 1px solid #444;">
                        <div class="form-hint mt-1">Ruta guardada: <?= htmlspecialchars((string) $config['imagen'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="imagen_auto">Imagen del auto (opcional)</label>
                        <input class="form-control" type="file" id="imagen_auto" name="imagen_auto" accept=".jpg,.jpeg,.png,.webp,.gif,.avif,image/*">
                        <small class="form-hint">Si no subes una nueva imagen, se conserva la actual.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Imagen actual del auto</label>
                        <img src="<?= htmlspecialchars('../' . (string) $config['imagen_auto'], ENT_QUOTES, 'UTF-8') ?>" alt="Imagen del auto" style="max-width: 260px; width: 100%; height: auto; border: 1px solid #444;">
                        <div class="form-hint mt-1">Ruta guardada: <?= htmlspecialchars((string) $config['imagen_auto'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
 