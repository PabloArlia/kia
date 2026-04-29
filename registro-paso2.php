<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['registro_paso1'])) {
    header('Location: registro-paso1.php');
    exit;
}

$paso1 = $_SESSION['registro_paso1'];
$registro_existente = $_SESSION['registro_existente'] ?? false;
$registro_id = $_SESSION['registro_id'] ?? null;

$databaseStatus = db_registration_status();
$errors = [];
$values = [
    'concesionaria' => '',
    'fidelity' => '',
    'modelo' => '',
    'vin' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($databaseStatus !== null) {
        $errors['_general'] = $databaseStatus;
    }

    $values['concesionaria'] = old($_POST, 'concesionaria');
    $values['fidelity'] = old($_POST, 'fidelity');
    $values['modelo'] = old($_POST, 'modelo');
    $values['vin'] = strtoupper(old($_POST, 'vin'));

    if ($values['concesionaria'] === '') {
        $errors['concesionaria'] = 'La concesionaria es obligatoria.';
    }

    if (!in_array($values['fidelity'], ['SI', 'NO'], true)) {
        $errors['fidelity'] = 'Selecciona SI o NO para Kia Fidelity.';
    }

    if ($values['modelo'] === '') {
        $errors['modelo'] = 'El modelo es obligatorio.';
    }
	
	$vin = $values['vin'];

	if ($vin === '') {
		$errors['vin'] = 'El VIN es obligatorio.';
	} else {
		$vin = strtoupper($vin);

		// Solo letras y números
		if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
			$errors['vin'] = 'El número ingresado es incorrecto';
		} else {
			// Guardamos normalizado
			$values['vin'] = $vin;
		}
	}

    if (!$errors) {
        try {
            // Validar que el VIN sea único (no puede repetirse en ningún registro)
            $stmt = db()->prepare('SELECT vin FROM registros WHERE vin = :vin LIMIT 1');
            $stmt->execute([':vin' => $values['vin']]);
            $duplicadoVin = $stmt->fetch();
            
            if ($duplicadoVin) {
                $errors['vin'] = 'Este VIN ya fue registrado.';
            }
        } catch (Throwable $e) {
            $errors['_general'] = 'No se pudo validar el VIN. Detalle: ' . db_error_message($e);
        }
    }

    if (!$errors) {
        try {
            // Crear nuevo registro (INSERT)
            $stmt = db()->prepare('INSERT INTO registros (
                nombre,
                correo,
                telefono,
                fecha_nacimiento,
                estado,
                concesionaria,
                kia_fidelity,
                modelo,
                vin,
                acepta_terminos
            ) VALUES (
                :nombre,
                :correo,
                :telefono,
                :fecha_nacimiento,
                :estado,
                :concesionaria,
                :kia_fidelity,
                :modelo,
                :vin,
                :acepta_terminos
            )');

            $stmt->execute([
                ':nombre' => $paso1['nombre'],
                ':correo' => $paso1['correo'],
                ':telefono' => $paso1['telefono'],
                ':fecha_nacimiento' => $paso1['fecha_nacimiento'],
                ':estado' => $paso1['estado'],
                ':concesionaria' => $values['concesionaria'],
                ':kia_fidelity' => $values['fidelity'] === 'SI' ? 1 : 0,
                ':modelo' => $values['modelo'],
                ':vin' => $values['vin'],
                ':acepta_terminos' => 1,
            ]);

            $_SESSION['registro_id'] = (int) db()->lastInsertId();
            unset($_SESSION['registro_paso1']);
            unset($_SESSION['registro_existente']);

            header('Location: juego.php');
            exit;
        } catch (Throwable $e) {
            $dbMessage = db_error_message($e);

            if (stripos($dbMessage, 'Duplicate entry') !== false) {
                if (stripos($dbMessage, 'vin') !== false) {
                    $errors['vin'] = 'Este VIN ya fue registrado.';
                } else {
                    $errors['_general'] = 'Ya existe un registro con esos datos.';
                }
            } else {
                $errors['_general'] = 'No se pudo guardar el registro. Detalle: ' . $dbMessage;
            }
        }
    }
}

$pageTitle = 'Registro - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" action="registro-paso2.php" class="registro-form">
    <div class="registro-header">
        <h2>Completa con tus datos</h2>
        <img src="img/home.png" />
    </div>
    <div class="registro-fields">
    <div class="field">
        <select id="concesionaria" name="concesionaria" required <?php echo isset($errors['concesionaria']) ? 'title="' . e($errors['concesionaria']) . '" aria-invalid="true"' : ''; ?>>
            <option value="">Concesionaria</option>
            <option value="Kia Aeropuerto" <?php echo $values['concesionaria'] === 'Kia Aeropuerto' ? 'selected' : ''; ?>>Kia Aeropuerto</option>
            <option value="Kia Ahome" <?php echo $values['concesionaria'] === 'Kia Ahome' ? 'selected' : ''; ?>>Kia Ahome</option>
            <option value="Kia Altaria" <?php echo $values['concesionaria'] === 'Kia Altaria' ? 'selected' : ''; ?>>Kia Altaria</option>
            <option value="Kia Altas Montañas" <?php echo $values['concesionaria'] === 'Kia Altas Montañas' ? 'selected' : ''; ?>>Kia Altas Montañas</option>
            <option value="Kia Angelopolis" <?php echo $values['concesionaria'] === 'Kia Angelopolis' ? 'selected' : ''; ?>>Kia Angelopolis</option>
            <option value="Kia Animas" <?php echo $values['concesionaria'] === 'Kia Animas' ? 'selected' : ''; ?>>Kia Animas</option>
            <option value="Kia Avenida" <?php echo $values['concesionaria'] === 'Kia Avenida' ? 'selected' : ''; ?>>Kia Avenida</option>
            <option value="Kia Bahia" <?php echo $values['concesionaria'] === 'Kia Bahia' ? 'selected' : ''; ?>>Kia Bahia</option>
            <option value="Kia Baja Sur" <?php echo $values['concesionaria'] === 'Kia Baja Sur' ? 'selected' : ''; ?>>Kia Baja Sur</option>
            <option value="Kia Bajio" <?php echo $values['concesionaria'] === 'Kia Bajio' ? 'selected' : ''; ?>>Kia Bajio</option>
            <option value="Kia Bernardez" <?php echo $values['concesionaria'] === 'Kia Bernardez' ? 'selected' : ''; ?>>Kia Bernardez</option>
            <option value="Kia Boca" <?php echo $values['concesionaria'] === 'Kia Boca' ? 'selected' : ''; ?>>Kia Boca</option>
            <option value="Kia Bonampak" <?php echo $values['concesionaria'] === 'Kia Bonampak' ? 'selected' : ''; ?>>Kia Bonampak</option>
            <option value="Kia Brisas" <?php echo $values['concesionaria'] === 'Kia Brisas' ? 'selected' : ''; ?>>Kia Brisas</option>
            <option value="Kia Cabos" <?php echo $values['concesionaria'] === 'Kia Cabos' ? 'selected' : ''; ?>>Kia Cabos</option>
            <option value="Kia Cajeme" <?php echo $values['concesionaria'] === 'Kia Cajeme' ? 'selected' : ''; ?>>Kia Cajeme</option>
            <option value="Kia Campestre" <?php echo $values['concesionaria'] === 'Kia Campestre' ? 'selected' : ''; ?>>Kia Campestre</option>
            <option value="Kia Capital" <?php echo $values['concesionaria'] === 'Kia Capital' ? 'selected' : ''; ?>>Kia Capital</option>
            <option value="Kia Caribe" <?php echo $values['concesionaria'] === 'Kia Caribe' ? 'selected' : ''; ?>>Kia Caribe</option>
            <option value="Kia Carretera 57" <?php echo $values['concesionaria'] === 'Kia Carretera 57' ? 'selected' : ''; ?>>Kia Carretera 57</option>
            <option value="Kia Carretera Nacional" <?php echo $values['concesionaria'] === 'Kia Carretera Nacional' ? 'selected' : ''; ?>>Kia Carretera Nacional</option>
            <option value="Kia Center" <?php echo $values['concesionaria'] === 'Kia Center' ? 'selected' : ''; ?>>Kia Center</option>
            <option value="Kia Cholula" <?php echo $values['concesionaria'] === 'Kia Cholula' ? 'selected' : ''; ?>>Kia Cholula</option>
            <option value="Kia Coacalco" <?php echo $values['concesionaria'] === 'Kia Coacalco' ? 'selected' : ''; ?>>Kia Coacalco</option>
            <option value="Kia Coapa" <?php echo $values['concesionaria'] === 'Kia Coapa' ? 'selected' : ''; ?>>Kia Coapa</option>
            <option value="Kia Coatza" <?php echo $values['concesionaria'] === 'Kia Coatza' ? 'selected' : ''; ?>>Kia Coatza</option>
            <option value="Kia Coliman" <?php echo $values['concesionaria'] === 'Kia Coliman' ? 'selected' : ''; ?>>Kia Coliman</option>
            <option value="Kia Corregidora" <?php echo $values['concesionaria'] === 'Kia Corregidora' ? 'selected' : ''; ?>>Kia Corregidora</option>
            <option value="Kia Country" <?php echo $values['concesionaria'] === 'Kia Country' ? 'selected' : ''; ?>>Kia Country</option>
            <option value="Kia Country Club" <?php echo $values['concesionaria'] === 'Kia Country Club' ? 'selected' : ''; ?>>Kia Country Club</option>
            <option value="Kia Cuautitlan" <?php echo $values['concesionaria'] === 'Kia Cuautitlan' ? 'selected' : ''; ?>>Kia Cuautitlan</option>
            <option value="Kia Cumbres" <?php echo $values['concesionaria'] === 'Kia Cumbres' ? 'selected' : ''; ?>>Kia Cumbres</option>
            <option value="Kia del Duero" <?php echo $values['concesionaria'] === 'Kia del Duero' ? 'selected' : ''; ?>>Kia del Duero</option>
            <option value="Kia Del Valle" <?php echo $values['concesionaria'] === 'Kia Del Valle' ? 'selected' : ''; ?>>Kia Del Valle</option>
            <option value="Kia Diamante" <?php echo $values['concesionaria'] === 'Kia Diamante' ? 'selected' : ''; ?>>Kia Diamante</option>
            <option value="Kia Division del norte" <?php echo $values['concesionaria'] === 'Kia Division del norte' ? 'selected' : ''; ?>>Kia Division del norte</option>
            <option value="Kia Dorada" <?php echo $values['concesionaria'] === 'Kia Dorada' ? 'selected' : ''; ?>>Kia Dorada</option>
            <option value="Kia Ecatepec" <?php echo $values['concesionaria'] === 'Kia Ecatepec' ? 'selected' : ''; ?>>Kia Ecatepec</option>
            <option value="Kia Esmeralda" <?php echo $values['concesionaria'] === 'Kia Esmeralda' ? 'selected' : ''; ?>>Kia Esmeralda</option>
            <option value="Kia Fresnillo" <?php echo $values['concesionaria'] === 'Kia Fresnillo' ? 'selected' : ''; ?>>Kia Fresnillo</option>
            <option value="Kia Frontera" <?php echo $values['concesionaria'] === 'Kia Frontera' ? 'selected' : ''; ?>>Kia Frontera</option>
            <option value="Kia Futura" <?php echo $values['concesionaria'] === 'Kia Futura' ? 'selected' : ''; ?>>Kia Futura</option>
            <option value="Kia Galerias" <?php echo $values['concesionaria'] === 'Kia Galerias' ? 'selected' : ''; ?>>Kia Galerias</option>
            <option value="Kia Gonzalez Gallo" <?php echo $values['concesionaria'] === 'Kia Gonzalez Gallo' ? 'selected' : ''; ?>>Kia Gonzalez Gallo</option>
            <option value="Kia Gonzalitos" <?php echo $values['concesionaria'] === 'Kia Gonzalitos' ? 'selected' : ''; ?>>Kia Gonzalitos</option>
            <option value="Kia Guadiana" <?php echo $values['concesionaria'] === 'Kia Guadiana' ? 'selected' : ''; ?>>Kia Guadiana</option>
            <option value="Kia Guerrense" <?php echo $values['concesionaria'] === 'Kia Guerrense' ? 'selected' : ''; ?>>Kia Guerrense</option>
            <option value="Kia Innova" <?php echo $values['concesionaria'] === 'Kia Innova' ? 'selected' : ''; ?>>Kia Innova</option>
            <option value="Kia Interlomas" <?php echo $values['concesionaria'] === 'Kia Interlomas' ? 'selected' : ''; ?>>Kia Interlomas</option>
            <option value="Kia Ixtapaluca" <?php echo $values['concesionaria'] === 'Kia Ixtapaluca' ? 'selected' : ''; ?>>Kia Ixtapaluca</option>
            <option value="Kia Iztapalapa" <?php echo $values['concesionaria'] === 'Kia Iztapalapa' ? 'selected' : ''; ?>>Kia Iztapalapa</option>
            <option value="Kia Juarez" <?php echo $values['concesionaria'] === 'Kia Juarez' ? 'selected' : ''; ?>>Kia Juarez</option>
            <option value="Kia Juventud" <?php echo $values['concesionaria'] === 'Kia Juventud' ? 'selected' : ''; ?>>Kia Juventud</option>
            <option value="Kia Laguna" <?php echo $values['concesionaria'] === 'Kia Laguna' ? 'selected' : ''; ?>>Kia Laguna</option>
            <option value="Kia Laredo" <?php echo $values['concesionaria'] === 'Kia Laredo' ? 'selected' : ''; ?>>Kia Laredo</option>
            <option value="Kia Linda Vista" <?php echo $values['concesionaria'] === 'Kia Linda Vista' ? 'selected' : ''; ?>>Kia Linda Vista</option>
            <option value="Kia Lomas" <?php echo $values['concesionaria'] === 'Kia Lomas' ? 'selected' : ''; ?>>Kia Lomas</option>
            <option value="Kia Lomas Verdes" <?php echo $values['concesionaria'] === 'Kia Lomas Verdes' ? 'selected' : ''; ?>>Kia Lomas Verdes</option>
            <option value="Kia Lopez Mateos" <?php echo $values['concesionaria'] === 'Kia Lopez Mateos' ? 'selected' : ''; ?>>Kia Lopez Mateos</option>
            <option value="Kia Los Fuertes" <?php echo $values['concesionaria'] === 'Kia Los Fuertes' ? 'selected' : ''; ?>>Kia Los Fuertes</option>
            <option value="Kia Malinche" <?php echo $values['concesionaria'] === 'Kia Malinche' ? 'selected' : ''; ?>>Kia Malinche</option>
            <option value="Kia Manantial" <?php echo $values['concesionaria'] === 'Kia Manantial' ? 'selected' : ''; ?>>Kia Manantial</option>
            <option value="Kia Mariano Escobedo" <?php echo $values['concesionaria'] === 'Kia Mariano Escobedo' ? 'selected' : ''; ?>>Kia Mariano Escobedo</option>
            <option value="Kia Max" <?php echo $values['concesionaria'] === 'Kia Max' ? 'selected' : ''; ?>>Kia Max</option>
            <option value="Kia Metepec" <?php echo $values['concesionaria'] === 'Kia Metepec' ? 'selected' : ''; ?>>Kia Metepec</option>
            <option value="Kia Mil Cumbres" <?php echo $values['concesionaria'] === 'Kia Mil Cumbres' ? 'selected' : ''; ?>>Kia Mil Cumbres</option>
            <option value="Kia Morelos" <?php echo $values['concesionaria'] === 'Kia Morelos' ? 'selected' : ''; ?>>Kia Morelos</option>
            <option value="Kia Morelos Sur" <?php echo $values['concesionaria'] === 'Kia Morelos Sur' ? 'selected' : ''; ?>>Kia Morelos Sur</option>
            <option value="Kia Nayarita" <?php echo $values['concesionaria'] === 'Kia Nayarita' ? 'selected' : ''; ?>>Kia Nayarita</option>
            <option value="Kia Norte" <?php echo $values['concesionaria'] === 'Kia Norte' ? 'selected' : ''; ?>>Kia Norte</option>
            <option value="Kia Nova Qro" <?php echo $values['concesionaria'] === 'Kia Nova Qro' ? 'selected' : ''; ?>>Kia Nova Qro</option>
            <option value="Kia Pacific" <?php echo $values['concesionaria'] === 'Kia Pacific' ? 'selected' : ''; ?>>Kia Pacific</option>
            <option value="Kia Pape" <?php echo $values['concesionaria'] === 'Kia Pape' ? 'selected' : ''; ?>>Kia Pape</option>
            <option value="Kia Paricutin" <?php echo $values['concesionaria'] === 'Kia Paricutin' ? 'selected' : ''; ?>>Kia Paricutin</option>
            <option value="Kia Patria" <?php echo $values['concesionaria'] === 'Kia Patria' ? 'selected' : ''; ?>>Kia Patria</option>
            <option value="Kia Pedregal" <?php echo $values['concesionaria'] === 'Kia Pedregal' ? 'selected' : ''; ?>>Kia Pedregal</option>
            <option value="Kia Peninsula" <?php echo $values['concesionaria'] === 'Kia Peninsula' ? 'selected' : ''; ?>>Kia Peninsula</option>
            <option value="Kia Playacar" <?php echo $values['concesionaria'] === 'Kia Playacar' ? 'selected' : ''; ?>>Kia Playacar</option>
            <option value="Kia Plaza del Valle" <?php echo $values['concesionaria'] === 'Kia Plaza del Valle' ? 'selected' : ''; ?>>Kia Plaza del Valle</option>
            <option value="Kia Polanco" <?php echo $values['concesionaria'] === 'Kia Polanco' ? 'selected' : ''; ?>>Kia Polanco</option>
            <option value="Kia Poliforum" <?php echo $values['concesionaria'] === 'Kia Poliforum' ? 'selected' : ''; ?>>Kia Poliforum</option>
            <option value="Kia Primavera" <?php echo $values['concesionaria'] === 'Kia Primavera' ? 'selected' : ''; ?>>Kia Primavera</option>
            <option value="Kia Puerto Escondido" <?php echo $values['concesionaria'] === 'Kia Puerto Escondido' ? 'selected' : ''; ?>>Kia Puerto Escondido</option>
            <option value="Kia Punto Sur" <?php echo $values['concesionaria'] === 'Kia Punto Sur' ? 'selected' : ''; ?>>Kia Punto Sur</option>
            <option value="Kia Ruiz Cortines" <?php echo $values['concesionaria'] === 'Kia Ruiz Cortines' ? 'selected' : ''; ?>>Kia Ruiz Cortines</option>
            <option value="Kia Salina Cruz" <?php echo $values['concesionaria'] === 'Kia Salina Cruz' ? 'selected' : ''; ?>>Kia Salina Cruz</option>
            <option value="Kia San Joaquin" <?php echo $values['concesionaria'] === 'Kia San Joaquin' ? 'selected' : ''; ?>>Kia San Joaquin</option>
            <option value="Kia San Juan" <?php echo $values['concesionaria'] === 'Kia San Juan' ? 'selected' : ''; ?>>Kia San Juan</option>
            <option value="Kia Santa Anita" <?php echo $values['concesionaria'] === 'Kia Santa Anita' ? 'selected' : ''; ?>>Kia Santa Anita</option>
            <option value="Kia Santa Fe" <?php echo $values['concesionaria'] === 'Kia Santa Fe' ? 'selected' : ''; ?>>Kia Santa Fe</option>
            <option value="Kia Satelite" <?php echo $values['concesionaria'] === 'Kia Satelite' ? 'selected' : ''; ?>>Kia Satelite</option>
            <option value="Kia Sendero" <?php echo $values['concesionaria'] === 'Kia Sendero' ? 'selected' : ''; ?>>Kia Sendero</option>
            <option value="Kia Serdan" <?php echo $values['concesionaria'] === 'Kia Serdan' ? 'selected' : ''; ?>>Kia Serdan</option>
            <option value="Kia Sureste" <?php echo $values['concesionaria'] === 'Kia Sureste' ? 'selected' : ''; ?>>Kia Sureste</option>
            <option value="Kia Tajin" <?php echo $values['concesionaria'] === 'Kia Tajin' ? 'selected' : ''; ?>>Kia Tajin</option>
            <option value="Kia Texcoco" <?php echo $values['concesionaria'] === 'Kia Texcoco' ? 'selected' : ''; ?>>Kia Texcoco</option>
            <option value="Kia Tlahuac" <?php echo $values['concesionaria'] === 'Kia Tlahuac' ? 'selected' : ''; ?>>Kia Tlahuac</option>
            <option value="Kia Vallarta" <?php echo $values['concesionaria'] === 'Kia Vallarta' ? 'selected' : ''; ?>>Kia Vallarta</option>
            <option value="Kia Valle Oriente" <?php echo $values['concesionaria'] === 'Kia Valle Oriente' ? 'selected' : ''; ?>>Kia Valle Oriente</option>
            <option value="Kia Vallejo" <?php echo $values['concesionaria'] === 'Kia Vallejo' ? 'selected' : ''; ?>>Kia Vallejo</option>
            <option value="Kia Via Alta" <?php echo $values['concesionaria'] === 'Kia Via Alta' ? 'selected' : ''; ?>>Kia Via Alta</option>
            <option value="Kia Victoria" <?php echo $values['concesionaria'] === 'Kia Victoria' ? 'selected' : ''; ?>>Kia Victoria</option>
            <option value="Kia Villas" <?php echo $values['concesionaria'] === 'Kia Villas' ? 'selected' : ''; ?>>Kia Villas</option>
            <option value="Kia Vision" <?php echo $values['concesionaria'] === 'Kia Vision' ? 'selected' : ''; ?>>Kia Vision</option>
            <option value="Kia Orizaba" <?php echo $values['concesionaria'] === 'Kia Orizaba' ? 'selected' : ''; ?>>Kia Orizaba</option>
            <option value="Kia Juriquilla" <?php echo $values['concesionaria'] === 'Kia Juriquilla' ? 'selected' : ''; ?>>Kia Juriquilla</option>
            <option value="Kia Ciudad Guzmán" <?php echo $values['concesionaria'] === 'Kia Ciudad Guzmán' ? 'selected' : ''; ?>>Kia Ciudad Guzmán</option>
            <option value="Kia Patriotismo" <?php echo $values['concesionaria'] === 'Kia Patriotismo' ? 'selected' : ''; ?>>Kia Patriotismo</option>
            <option value="Kia La Fe" <?php echo $values['concesionaria'] === 'Kia La Fe' ? 'selected' : ''; ?>>Kia La Fe</option>
            <option value="Kia Tapachula" <?php echo $values['concesionaria'] === 'Kia Tapachula' ? 'selected' : ''; ?>>Kia Tapachula</option>
            <option value="Kia Ciudad Valles" <?php echo $values['concesionaria'] === 'Kia Ciudad Valles' ? 'selected' : ''; ?>>Kia Ciudad Valles</option>
        </select>
        <?php if (isset($errors['concesionaria'])): ?>
            <div class="error"><?php echo e($errors['concesionaria']); ?></div>
        <?php endif; ?>
    </div>

    <div class="field">
        <div class="field-with-radios">
            <span class="field-label">La compra fue a través de Kia Fidelity</span>
            <fieldset class="radio-group" <?php echo isset($errors['fidelity']) ? 'aria-invalid="true"' : ''; ?>>
                <label class="radio-label">
                    <input type="radio" name="fidelity" value="SI" <?php echo $values['fidelity'] === 'SI' ? 'checked' : ''; ?>>
                    <span>SI</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="fidelity" value="NO" <?php echo $values['fidelity'] === 'NO' ? 'checked' : ''; ?>>
                    <span>NO</span>
                </label>
            </fieldset>
        </div>
        <?php if (isset($errors['fidelity'])): ?>
            <div class="error" title="<?php echo e($errors['fidelity']); ?>"><?php echo e($errors['fidelity']); ?></div>
        <?php endif; ?>
    </div>

    <div class="field">
        <select id="modelo" name="modelo" required <?php echo isset($errors['modelo']) ? 'title="' . e($errors['modelo']) . '" aria-invalid="true"' : ''; ?>>
            <option value="">Modelo que se compró</option>
            <option value="K3" <?php echo $values['modelo'] === 'K3' ? 'selected' : ''; ?>>K3</option>
            <option value="K4" <?php echo $values['modelo'] === 'K4' ? 'selected' : ''; ?>>K4</option>
            <option value="Sonet" <?php echo $values['modelo'] === 'Sonet' ? 'selected' : ''; ?>>Sonet</option>
            <option value="Seltos" <?php echo $values['modelo'] === 'Seltos' ? 'selected' : ''; ?>>Seltos</option>
            <option value="Sportage" <?php echo $values['modelo'] === 'Sportage' ? 'selected' : ''; ?>>Sportage</option>
            <option value="Sorento" <?php echo $values['modelo'] === 'Sorento' ? 'selected' : ''; ?>>Sorento</option>
            <option value="Telluride" <?php echo $values['modelo'] === 'Telluride' ? 'selected' : ''; ?>>Telluride</option>
            <option value="Niro" <?php echo $values['modelo'] === 'Niro' ? 'selected' : ''; ?>>Niro</option>
            <option value="EV6" <?php echo $values['modelo'] === 'EV6' ? 'selected' : ''; ?>>EV6</option>
        </select>
        <?php if (isset($errors['modelo'])): ?>
            <div class="error"><?php echo e($errors['modelo']); ?></div>
        <?php endif; ?>
    </div>

    <div class="field"> 
        <input maxlength="17" type="text" id="vin" name="vin" value="<?php echo e($values['vin']); ?>" required placeholder="Vin de tu vehículo" <?php echo isset($errors['vin']) ? 'title="' . e($errors['vin']) . '" aria-invalid="true"' : ''; ?>>
        <?php if (isset($errors['vin'])): ?>
            <div class="error"><?php echo e($errors['vin']); ?></div>
        <?php endif; ?>
        <small>VIN de tu vehículo, asegúrate que sean 17 caracteres alfanuméricos</small>
    </div>

    </div>
    <div class="actions">
        <button class="btn" type="submit">Guardar</button>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php';
