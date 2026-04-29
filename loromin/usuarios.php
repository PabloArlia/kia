<?php
require_once '../init.php';

if (empty($_SESSION['admin'])) {
    header('Location: ' . urladmin . 'index.php');
    exit;
}

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$usuarios = db()->query("
    SELECT
        r.id,
        r.nombre,
        r.correo,
        r.telefono,
        r.fecha_nacimiento,
        r.estado,
        r.concesionaria,
        r.kia_fidelity,
        r.modelo,
        r.vin,
        r.created_at,
        jr.numero_ingresado
    FROM registros r
    LEFT JOIN juego_resultados jr ON jr.registro_id = r.id
    ORDER BY r.created_at ASC, r.id ASC
")->fetchAll();

// Crear array combinado de usuarios y cambios ordenados por tiempo
$eventos = [];
foreach ($usuarios as $u) {
    $eventos[] = [
        'tipo' => 'usuario',
        'timestamp' => $u['created_at'],
        'data' => $u
    ];
}

// Ordenar por timestamp
usort($eventos, function($a, $b) {
    return strtotime($a['timestamp']) - strtotime($b['timestamp']);
});

if (isset($_GET['xls']) && $_GET['xls'] === '1') {
    $filename = 'usuarios_' . date('Ymd_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<table border=\"1\">";
    echo '<tr>';
    echo '<th>Tipo de evento</th>';
    echo '<th>Hora</th>';
    echo '<th>#</th>';
    echo '<th>Numero ingresado</th>';
    echo '<th>Nombre</th>';
    echo '<th>Correo</th>';
    echo '<th>Telefono</th>';
    echo '<th>Fecha nac.</th>';
    echo '<th>Estado</th>';
    echo '<th>Concesionaria</th>';
    echo '<th>Fidelity</th>';
    echo '<th>Modelo</th>';
    echo '<th>VIN</th>';
    echo '<th>Detalles</th>';
    echo '</tr>';

    foreach ($eventos as $evento) {
        echo '<tr>';
        if ($evento['tipo'] === 'usuario') {
            $u = $evento['data'];
            echo '<td>Registro usuario</td>';
            echo '<td>' . htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . (int)$u['id'] . '</td>';
            echo '<td>' . ($u['numero_ingresado'] !== null ? (int)$u['numero_ingresado'] : '') . '</td>';
            echo '<td>' . htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['telefono'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['estado'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['concesionaria'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . ($u['kia_fidelity'] ? 'Si' : 'No') . '</td>';
            echo '<td>' . htmlspecialchars($u['modelo'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($u['vin'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td></td>';
        }
        echo '</tr>';
    }

    echo '</table>';
    exit;
}

include 'header.php';
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Usuarios registrados</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="?xls=1" class="btn btn-success me-2">Descargar XLS</a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if ($flash): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped card-table">
                        <thead>
                            <tr>
                                <th>Tipo de evento</th>
                                <th>Hora</th>
                                <th>#</th>
                                <th>Número ingresado</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Fecha nac.</th>
                                <th>Estado</th>
                                <th>Concesionaria</th>
                                <th>Fidelity</th>
                                <th>Modelo</th>
                                <th>VIN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($eventos)): ?>
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">Sin registros aún.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($eventos as $evento): ?>
                            <?php if ($evento['tipo'] === 'usuario'): ?>
                            <?php $u = $evento['data']; ?>
                            <tr>
                                <td><span class="badge bg-blue-lt">Usuario</span></td>
                                <td class="text-muted small"><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= (int)$u['id'] ?></td>
                                <td>
                                    <?php if ($u['numero_ingresado'] !== null): ?>
                                        <strong><?= (int)$u['numero_ingresado'] ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['concesionaria'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($u['kia_fidelity']): ?>
                                        <span class="badge bg-green-lt">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-lt">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['modelo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><code><?= htmlspecialchars($u['vin'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            </tr>
                            <?php else: ?>
                            <?php $cambio = $evento['data']; ?>
                            <tr style="background-color: #222222;">
                                <td><span class="badge bg-warning-lt">Cambio pregunta</span></td>
                                <td class="text-muted small"><strong><?= htmlspecialchars($cambio['timestamp'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td colspan="11">
                                    <strong>Pregunta:</strong> <?= htmlspecialchars($cambio['pregunta'], ENT_QUOTES, 'UTF-8') ?><br>
                                    <strong>Objeto:</strong> <?= htmlspecialchars($cambio['objeto'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
