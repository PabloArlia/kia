<?php
require_once __DIR__ . '/init.php';

$gameConfig = game_config_load();
$objetoJuego = trim((string) ($gameConfig['objeto'] ?? ''));
if ($objetoJuego === '') {
    $objetoJuego = game_config_defaults()['objeto'];
}

$pageTitle = 'Preguntas Frecuentes - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="faq-scroll">
<h2 class="mecanica-title">Preguntas Frecuentes</h2>
<ol class="frecuente-lista faq-lista">

	<li>
		<h3>¿Hasta cuándo puedo participar en la promoción?</h3>
		<p>El periodo de participación de la mecánica de la promoción inicia a las 00:00:01 hrs del día 21 de abril de 2026 y concluye a las 23:59:59 hrs del día 30 de abril de 2026.</p>
	</li>

	<li>
		<h3>¿Cómo participo después de comprar mi vehículo?</h3>
		<ol>
			<li>Una vez finalizado el proceso de compra en la concesionaria de su preferencia, al participante se le otorgará el número de identificación vehicular (VIN).</li>
			<li>Posteriormente deberá ingresar al sitio web www.kiateacercaalmundial.com y registrar los siguientes datos:</li>
		</ol>

		<ul style="margin-left: 20px;">
			<li>Nombre completo.</li>
			<li>Correo electrónico.</li> 
			<li>Teléfono.</li>
			<li>Nombre de la concesionaria en donde adquirió el vehículo.</li>
			<li>Número de identificación vehicular (VIN) a 17 dígitos.</li>
		</ul>

		<ol start="3">
			<li>Participa en la dinámica del juego.</li>
		</ol>
	</li>

	<li>
		<h3>¿Qué me van a preguntar durante mi registro?</h3>
		<p>En este paso se le preguntará al participante si adquirió el vehículo con el programa KIA FIDELITY; deberá responder SÍ o NO de acuerdo con su compra en la concesionaria.</p>
	</li>

	<li>
		<h3>¿Qué debo aceptar para poder participar?</h3>
		<p>Para continuar, se le pedirá al participante aceptar los términos y condiciones, así como el Aviso de Privacidad.</p>
	</li>

	<li>
		<h3>¿En qué consiste la dinámica o juego?</h3>
		<p>Una vez aceptado lo anterior, el participante tendrá acceso a una sesión de juego con una duración de 30 segundos. Durante la sesión, se mostrará una imagen que contiene diversos <b>balones</b> relacionados con el mundial.</p>
		<p>El participante deberá observar la imagen y calcular el número exacto de <b>balones</b> que aparecen en ella. Al finalizar el tiempo, deberá ingresar su respuesta con el número total de <b>balones</b> identificados.</p>
	</li>

	<li>
		<h3>¿Qué tengo que hacer durante el juego?</h3>
		<p>El participante deberá observar la imagen y calcular el número exacto de <b>balones</b> que aparecen en ella.</p>
	</li>

	<li>
		<h3>¿Cómo se determina si puedo ganar?</h3>
		<p>Los participantes cuya respuesta sea exacta o la más cercana al número exacto serán considerados como posibles ganadores de un boleto doble para asistir a un partido de la Copa Mundial de la FIFA 2026<sup>™</sup>.</p>
	</li>

	<li>
		<h3>¿Cómo se seleccionan los ganadores?</h3>
		<p>Al finalizar la vigencia de la promoción, se tendrá un plazo de 72 horas después del periodo de corte de cada etapa para realizar un ranking y determinar a los posibles ganadores de un boleto doble, quienes serán los 10 participantes que hayan acertado o se hayan aproximado a la cantidad de <b>balones</b> sin excederse del número exacto.</p>
	</li>

	<li>
		<h3>¿Qué pasa si hay empate?</h3>
		<ul>
			<li>Como primer criterio de desempate, se seleccionará al participante que haya realizado primero su registro de participación, considerando la fecha, hora y minuto del registro.</li>
			<li>Si el empate persistiera, como segundo criterio se elegirá al participante que haya comprado la unidad con el valor más alto.</li>
		</ul>
	</li>

	<li>
		<h3>¿Cómo me avisan si soy posible ganador?</h3>
		<p>Los participantes tendrán el carácter de posibles ganadores hasta en tanto se verifique si cumplieron con todos los requisitos de participación y serán contactados dentro de las 72 (setenta y dos) horas hábiles siguientes al finalizar la vigencia de la promoción, a través de la dirección electrónica promociones@lpagency.mx.</p>
	</li>

	<li>
		<h3>¿Qué documentos me van a pedir si soy posible ganador?</h3>
		<ul>
			<li>Identificación oficial vigente por ambos lados (IFE/INE).</li>
			<li>Clave Única de Registro de Población (CURP). Si la identificación oficial presentada es INE, ya no es necesaria.</li>
			<li>Constancia de Identificación Fiscal no mayor a tres meses de expedición.</li>
		</ul>
	</li>

	<li>
		<h3>¿Qué pasa después de enviar mis documentos?</h3>
		<p>Una vez recibidos los documentos antes señalados, éstos se revisarán, verificarán y validarán para confirmar que cumplan con todos los requisitos señalados en las bases. En 72 (setenta y dos) horas hábiles siguientes se le confirmará como ganador.</p>
	</li>

	<li>
		<h3>¿Qué pasa si no envío mis documentos o no respondo?</h3>
		<p>En caso de no recibir respuesta por parte del posible ganador, o bien de no recibir la información ni documentación, se perderá el derecho de reclamar y recibir el incentivo ofrecido. Por tanto, no se le podrá confirmar como ganador y se procederá a contactar al siguiente participante, y así sucesivamente.</p>
	</li>

	<li>
		<h3>¿Cuándo soy oficialmente ganador?</h3>
		<p>Serán considerados ganadores hasta en tanto se verifique que cumplieron en su totalidad con las condiciones y requisitos de participación.</p>
	</li>

	<li>
		<h3>¿Cómo se entrega el incentivo?</h3>
		<p>La entrega de los incentivos se realizará una vez que se haya confirmado al participante como ganador y éste se obliga a suscribir en favor del organizador o responsable de la promoción el recibo de entrega y conformidad. Los boletos son digitales y se entregarán a través de la plataforma digital que determine KIA®.</p>
	</li>

	<li>
		<h3>¿Dónde puedo ver la lista de ganadores?</h3>
		<p>La publicación de ganadores de los boletos dobles se llevará a cabo en www.kiateacercaalmundial.com el 13 de mayo de 2026.</p>
	</li>

	<li>
		<h3>¿Qué gastos no incluye el incentivo?</h3>
		<p>El participante será responsable de gestionar y cubrir gastos tales como hospedaje, transportación aérea o terrestre, alimentación, traslados, seguros de viaje y cualquier otro gasto adicional a los boletos dobles de la FIFA<sup>™</sup>.</p>
	</li>
    <li>
        <h3>¿Tienes alguna otra duda o aclaración?</h3>
        <p>
        Comunícate al número teléfónico: 8009530783<br>
        También puedes contactarnos vía WhatsApp al: 55 4880 9585<br>
        O correo electrónico: <a href="mailto:promociones@lpagency.mx" style="color:#fff">promociones@lpagency.mx</a><br>
        De lunes a viernes de 9 am a 18 horas.</p>
    </li>
</ol>

</div>
<?php require_once __DIR__ . '/includes/footer.php';
