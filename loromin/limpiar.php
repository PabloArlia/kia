<?php
require_once '../init.php';

if (empty($_SESSION['admin'])) {
    header('Location: ' . urladmin . 'index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        db()->exec('SET FOREIGN_KEY_CHECKS = 0');
        db()->exec('TRUNCATE TABLE juego_resultados');
        db()->exec('TRUNCATE TABLE registros');
        db()->exec('SET FOREIGN_KEY_CHECKS = 1');

        $_SESSION['admin_flash'] = 'La promocion fue restaurada. Se truncaron registros y resultados.';
        header('Location: ' . urladmin . 'usuarios.php');
        exit;
    } catch (Throwable $e) {
        // Ensure FK checks are restored even if an error occurs.
        try {
            db()->exec('SET FOREIGN_KEY_CHECKS = 1');
        } catch (Throwable $ignored) {
        }

        $error = 'No se pudo limpiar la promo: ' . $e->getMessage();
    }
}

include 'header.php';
?>
<div class="page-body">
    <div class="container-xl">
        <div class="card border-danger">
            <div class="card-header">
                <h3 class="card-title text-danger">Restaurar promo</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Esta accion vacia por completo las tablas <strong>registros</strong> y <strong>juego_resultados</strong>.
                    No se puede deshacer.
                </p>

                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <form method="post" onsubmit="return confirm('Se truncaran las tablas registros y juego_resultados. ¿Continuar?');">
                    <input type="hidden" name="confirmar" value="1">
                    <a href="<?=urladmin?>usuarios.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger">Confirmar y limpiar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
