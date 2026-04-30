<?php
require_once __DIR__ . '/init.php';

$errors = [];
$values = [
    'nombre' => '',
    'correo' => '',
    'telefono' => '',
    'fecha_nacimiento' => '',
    'estado' => '',
    'acepta_terminos' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nombre'] = old($_POST, 'nombre');
    $values['correo'] = old($_POST, 'correo');
    $values['telefono'] = old($_POST, 'telefono');
    $values['fecha_nacimiento'] = old($_POST, 'fecha_nacimiento');
    $values['estado'] = old($_POST, 'estado');
    $values['acepta_terminos'] = isset($_POST['acepta_terminos']) ? '1' : '';

    if ($values['nombre'] === '') {
        $errors['nombre'] = 'El nombre es obligatorio.';
    }

    if (!filter_var($values['correo'], FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = 'Ingresa un correo valido.';
    }
	
	if ($values['telefono'] === '') {
		$errors['telefono'] = 'El teléfono es obligatorio.';
	} elseif (!preg_match('/^[0-9\-\s]+$/', $values['telefono'])) {
		$errors['telefono'] = 'El teléfono solo puede contener números, espacios o guiones.';
	} else {
		// Quitamos espacios y guiones para contar solo los dígitos
		$soloNumeros = preg_replace('/[\s\-]/', '', $values['telefono']);

		if (strlen($soloNumeros) <= 6) {
			$errors['telefono'] = 'El teléfono debe tener más de 6 dígitos.';
		} elseif (strlen($soloNumeros) > 12) {
			$errors['telefono'] = 'El teléfono no debe tener más de 12 dígitos.';
		}
	}

    // Verificar duplicados de correo/teléfono ANTES de validar otros campos
    if (!isset($errors['correo'])) {
        try {
            $stmt = db()->prepare('
                SELECT id, nombre, correo, telefono, fecha_nacimiento, estado, concesionaria, kia_fidelity, modelo, vin 
                FROM registros 
                WHERE (correo = :correo OR telefono = :telefono) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                LIMIT 1');
            $stmt->execute([
                ':correo' => $values['correo'],
                ':telefono' => $values['telefono'],
            ]);

            $duplicado = $stmt->fetch();
            if ($duplicado) {
                if (isset($duplicado['correo']) && $duplicado['correo'] === $values['correo']) {
                    // Email ya registrado - cargar datos del registro existente y redirigir a paso 2
                    $_SESSION['registro_paso1'] = [
                        'nombre' => $duplicado['nombre'],
                        'correo' => $duplicado['correo'],
                        'telefono' => $duplicado['telefono'],
                        'fecha_nacimiento' => $duplicado['fecha_nacimiento'],
                        'estado' => $duplicado['estado'],
                        'acepta_terminos' => '1',
                    ];
                    $_SESSION['registro_id'] = (int) $duplicado['id'];
                    $_SESSION['registro_existente'] = true;
                    
                    header('Location: registro-paso2.php');
                    exit;
                }
            }
        } catch (Throwable $e) {
            $errors['_general'] = 'No se pudo validar si el correo o telefono ya existen. Detalle: ' . db_error_message($e);
        }
    }

	$fecha = $values['fecha_nacimiento'];
	if ($fecha === '') {
		$errors['fecha_nacimiento'] = 'La fecha de nacimiento es obligatoria.';
	} else {
        $dia = null;
        $mes = null;
        $anio = null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            // Formato estándar de input[type="date"]: YYYY-MM-DD
            [$anio, $mes, $dia] = explode('-', $fecha);
        } else {
            // Compatibilidad para fechas ingresadas manualmente: DD/MM/AAAA o DD-MM-AA
            $fechaNormalizada = str_replace('-', '/', $fecha);
            $partes = explode('/', $fechaNormalizada);

            if (count($partes) === 3) {
                [$dia, $mes, $anio] = $partes;

                if (strlen((string)$anio) === 2) {
                    $anio = ((int)$anio > (int)date('y')) ? '19' . $anio : '20' . $anio;
                }
            }
        }

        if ($dia === null || $mes === null || $anio === null) {
            $errors['fecha_nacimiento'] = 'Formato de fecha inválido.';
        } elseif (!checkdate((int)$mes, (int)$dia, (int)$anio)) {
            $errors['fecha_nacimiento'] = 'La fecha ingresada no es válida.';
        } else {
            $fechaObj = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', (int)$anio, (int)$mes, (int)$dia));

            if (!$fechaObj) {
                $errors['fecha_nacimiento'] = 'La fecha ingresada no es válida.';
            } else {
                $hoy = new DateTime('today');
                if ($fechaObj > $hoy) {
                    $errors['fecha_nacimiento'] = 'La fecha ingresada no es válida.';
                } else {
                    $edad = $hoy->diff($fechaObj)->y;
                    if ($edad < 18) {
                        $errors['fecha_nacimiento'] = 'Debes ser mayor de 18 años.';
                    } elseif ($edad >= 99) {
                        $errors['fecha_nacimiento'] = 'La edad debe ser menor de 99 años.';
                    } else {
                        // Guardar en formato compatible con input[type="date"] y MySQL DATE.
                        $values['fecha_nacimiento'] = $fechaObj->format('Y-m-d');
                    }
                }
            }
        }
	}

    if ($values['estado'] === '') {
        $errors['estado'] = 'El estado es obligatorio.';
    }
    if ($values['acepta_terminos'] !== '1') {
        $errors['acepta_terminos'] = 'Debes aceptar terminos legales.';
    }

    if (!$errors) {
        $_SESSION['registro_paso1'] = $values;
        header('Location: registro-paso2.php');
        exit;
    }
}

$pageTitle = 'Registro - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" action="registro-paso1.php" class="registro-form">
    
    <div class="registro-header">
        <h2>Completa con tus datos</h2>
        <img src="img/home.png" />
    </div>
    <div class="registro-fields">
    <?php if (isset($errors['_general'])): ?>
        <div class="error"><?php echo e($errors['_general']); ?></div>
    <?php endif; ?>
    <p class="mecanica-texto">Si ya te has registrado, puedes logearte solo ingresando tu correo electrónico.</p>
    <div class="field">
        <input type="text" id="nombre" name="nombre" value="<?php echo e($values['nombre']); ?>"  placeholder="Nombre completo igual a la factura de auto comprado" <?php echo isset($errors['nombre']) ? 'title="' . e($errors['nombre']) . '" aria-invalid="true"' : ''; ?>>
        <?php if (isset($errors['nombre'])): ?>
            <div class="error"><?php echo e($errors['nombre']); ?></div>
        <?php endif; ?>
    </div>
    <div class="field">
        <input type="email" id="correo" name="correo" value="<?php echo e($values['correo']); ?>" required placeholder="Correo electrónico" <?php echo isset($errors['correo']) ? 'title="' . e($errors['correo']) . '" aria-invalid="true"' : ''; ?>>
        <?php if (isset($errors['correo'])): ?>
            <div class="error"><?php echo e($errors['correo']); ?></div>
        <?php endif; ?>
    </div>
    <div class="field">
        <input type="text" id="telefono" name="telefono" value="<?php echo e($values['telefono']); ?>"  placeholder="Teléfono" <?php echo isset($errors['telefono']) ? 'title="' . e($errors['telefono']) . '" aria-invalid="true"' : ''; ?>>
        <?php if (isset($errors['telefono'])): ?>
            <div class="error"><?php echo e($errors['telefono']); ?></div>
        <?php endif; ?>
    </div>
    <div class="field">
        <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo e($values['fecha_nacimiento']); ?>"  placeholder="Fecha de nacimiento" inputmode="numeric" autocomplete="bday" <?php echo isset($errors['fecha_nacimiento']) ? 'title="' . e($errors['fecha_nacimiento']) . '" aria-invalid="true"' : ''; ?>>
        <?php if (isset($errors['fecha_nacimiento'])): ?>
            <div class="error"><?php echo e($errors['fecha_nacimiento']); ?></div>
        <?php endif; ?>
    </div>
    <div class="field">
        <select id="estado" name="estado"  <?php echo isset($errors['estado']) ? 'title="' . e($errors['estado']) . '" aria-invalid="true"' : ''; ?>>
            <option value="" <?php echo $values['estado'] === '' ? 'selected' : ''; ?>>Estado</option>
            <option value="Aguascalientes" <?php echo $values['estado'] === 'Aguascalientes' ? 'selected' : ''; ?>>Aguascalientes</option>
            <option value="Baja California" <?php echo $values['estado'] === 'Baja California' ? 'selected' : ''; ?>>Baja California</option>
            <option value="Baja California Sur" <?php echo $values['estado'] === 'Baja California Sur' ? 'selected' : ''; ?>>Baja California Sur</option>
            <option value="Campeche" <?php echo $values['estado'] === 'Campeche' ? 'selected' : ''; ?>>Campeche</option>
            <option value="Chiapas" <?php echo $values['estado'] === 'Chiapas' ? 'selected' : ''; ?>>Chiapas</option>
            <option value="Chihuahua" <?php echo $values['estado'] === 'Chihuahua' ? 'selected' : ''; ?>>Chihuahua</option>
            <option value="Ciudad de Mexico" <?php echo $values['estado'] === 'Ciudad de Mexico' ? 'selected' : ''; ?>>Ciudad de Mexico</option>
            <option value="Coahuila" <?php echo $values['estado'] === 'Coahuila' ? 'selected' : ''; ?>>Coahuila</option>
            <option value="Colima" <?php echo $values['estado'] === 'Colima' ? 'selected' : ''; ?>>Colima</option>
            <option value="Durango" <?php echo $values['estado'] === 'Durango' ? 'selected' : ''; ?>>Durango</option>
            <option value="Estado de Mexico" <?php echo $values['estado'] === 'Estado de Mexico' ? 'selected' : ''; ?>>Estado de Mexico</option>
            <option value="Guanajuato" <?php echo $values['estado'] === 'Guanajuato' ? 'selected' : ''; ?>>Guanajuato</option>
            <option value="Guerrero" <?php echo $values['estado'] === 'Guerrero' ? 'selected' : ''; ?>>Guerrero</option>
            <option value="Hidalgo" <?php echo $values['estado'] === 'Hidalgo' ? 'selected' : ''; ?>>Hidalgo</option>
            <option value="Jalisco" <?php echo $values['estado'] === 'Jalisco' ? 'selected' : ''; ?>>Jalisco</option>
            <option value="Michoacan" <?php echo $values['estado'] === 'Michoacan' ? 'selected' : ''; ?>>Michoacan</option>
            <option value="Morelos" <?php echo $values['estado'] === 'Morelos' ? 'selected' : ''; ?>>Morelos</option>
            <option value="Nayarit" <?php echo $values['estado'] === 'Nayarit' ? 'selected' : ''; ?>>Nayarit</option>
            <option value="Nuevo Leon" <?php echo $values['estado'] === 'Nuevo Leon' ? 'selected' : ''; ?>>Nuevo Leon</option>
            <option value="Oaxaca" <?php echo $values['estado'] === 'Oaxaca' ? 'selected' : ''; ?>>Oaxaca</option>
            <option value="Puebla" <?php echo $values['estado'] === 'Puebla' ? 'selected' : ''; ?>>Puebla</option>
            <option value="Queretaro" <?php echo $values['estado'] === 'Queretaro' ? 'selected' : ''; ?>>Queretaro</option>
            <option value="Quintana Roo" <?php echo $values['estado'] === 'Quintana Roo' ? 'selected' : ''; ?>>Quintana Roo</option>
            <option value="San Luis Potosi" <?php echo $values['estado'] === 'San Luis Potosi' ? 'selected' : ''; ?>>San Luis Potosi</option>
            <option value="Sinaloa" <?php echo $values['estado'] === 'Sinaloa' ? 'selected' : ''; ?>>Sinaloa</option>
            <option value="Sonora" <?php echo $values['estado'] === 'Sonora' ? 'selected' : ''; ?>>Sonora</option>
            <option value="Tabasco" <?php echo $values['estado'] === 'Tabasco' ? 'selected' : ''; ?>>Tabasco</option>
            <option value="Tamaulipas" <?php echo $values['estado'] === 'Tamaulipas' ? 'selected' : ''; ?>>Tamaulipas</option>
            <option value="Tlaxcala" <?php echo $values['estado'] === 'Tlaxcala' ? 'selected' : ''; ?>>Tlaxcala</option>
            <option value="Veracruz" <?php echo $values['estado'] === 'Veracruz' ? 'selected' : ''; ?>>Veracruz</option>
            <option value="Yucatan" <?php echo $values['estado'] === 'Yucatan' ? 'selected' : ''; ?>>Yucatan</option>
            <option value="Zacatecas" <?php echo $values['estado'] === 'Zacatecas' ? 'selected' : ''; ?>>Zacatecas</option>
        </select>
        <?php if (isset($errors['estado'])): ?>
            <div class="error"><?php echo e($errors['estado']); ?></div>
        <?php endif; ?>
    </div>
    </div>
    <div class="field">
        <label>
            <input type="checkbox" name="acepta_terminos" value="1" <?php echo $values['acepta_terminos'] === '1' ? 'checked' : ''; ?> <?php echo isset($errors['acepta_terminos']) ? 'title="' . e($errors['acepta_terminos']) . '" aria-invalid="true"' : ''; ?>>
            Aceptar <a href="#">Términos y condiciones</a> y <a href="aviso-privacidad.php">Aviso de privacidad</a>
        </label>
        <?php if (isset($errors['acepta_terminos'])): ?>
            <div class="error"><?php echo e($errors['acepta_terminos']); ?></div>
        <?php endif; ?>
    </div>

    <div class="actions">
        <button class="btn" type="submit">Siguiente</button>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateInput = document.getElementById('fecha_nacimiento');

    function isDateInputSupported() {
        var testInput = document.createElement('input');
        testInput.setAttribute('type', 'date');
        return testInput.type === 'date';
    }

    if (dateInput && isDateInputSupported()) {

        dateInput.addEventListener('focus', function() {
            dateInput.setAttribute('type', 'date');
        });

        dateInput.addEventListener('blur', function() {
            // solo vuelve a text si está vacío (o espacios)
            if (!dateInput.value || dateInput.value.trim() === '') {
                dateInput.setAttribute('type', 'text');
            }
        });
    }
});
</script>
