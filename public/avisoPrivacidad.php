<?php
declare(strict_types=1);
require_once __DIR__ . '/../../database/ConsultorioModelo.php';
/*
 * Aviso de privacidad integral de Vital Dent.
 * Texto base para el proyecto: antes de usarlo en un consultorio real debe revisarlo un abogado
 * (Ley Federal de Protección de Datos Personales en Posesión de los Particulares, DOF 20/03/2025).
 */
$actualizado = '16 de noviembre de 2026';
// Correo para solicitudes de privacidad (derechos ARCO). Si se deja vacío, solo se muestran recepción y teléfono.
$correoPrivacidad = (string) (consultorio()['correo'] ?? ''); // se toma de la tabla vd_consultorio
$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Aviso de privacidad de Vital Dent: qué datos recabamos, para qué los usamos y cómo ejercer tus derechos.">
	<title>Aviso de privacidad · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Playfair+Display:wght@600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../Public/css/stylePublic.css">


</head>
<body class="publico">
	 <a class="saltar" href="#contenido">Saltar al contenido principal</a>
  <header class="publico-cabecera">
    <div class="envoltura">
      
      <a class="marca" href="../index.php"><img src="<?php echo imagen_consultorio('logo_url', '../../'); ?>" alt=""><span>Vital Dent</span></a>
      <nav class="publico-menu" aria-label="Secciones del sitio">
        <ul>
          <li><a class="fw-bold" href="../index.php">Inicio</a></li>
          <li><a class="fw-bold" href="quienesSomos.php">Quienes Somos</a></li>
          <li><a class="fw-bold" href="citas.php">Citas</a></li>
          <li><a class="fw-bold" href="tratamientos.php">Tratamientos</a></li>
          <li><a class="fw-bold" href="contacto.php">Contacto</a></li>
        </ul>
      </nav>
      <div class="acciones">
        <a class="boton secundario-btn" href="../private/autenticacion/crearCuenta.php">Crear cuenta</a>
        <a class="boton" href="../private/autenticacion/login.php">Iniciar sesión</a>
      </div>
    </div>
  </header>
	<main id="contenido">
		<section class="portada portada-vd portada-sola" aria-labelledby="titulo-aviso">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Tus datos, protegidos</p>
					<h1 id="titulo-aviso">Aviso de <span>privacidad</span></h1>
					<p class="portada-descripcion">Te explicamos qué datos personales recabamos, para qué los usamos, con quién los compartimos y cómo puedes ejercer tus derechos sobre ellos.</p>
					<p class="aviso-fecha">Última actualización: <?php echo $e($actualizado); ?> · Versión <?php echo dato_consultorio('aviso_privacidad_version'); ?></p>
				</div>
			</div>
		</section>

		<section class="seccion">
			<div class="envoltura aviso-legal">
				<nav class="aviso-indice" aria-label="Contenido del aviso">
					<p class="cat-sobretitulo">Contenido</p>
					<ol>
						<li><a href="#responsable">Responsable</a></li>
						<li><a href="#datos">Datos que recabamos</a></li>
						<li><a href="#sensibles">Datos de salud</a></li>
						<li><a href="#finalidades">Para qué los usamos</a></li>
						<li><a href="#transferencias">Con quién los compartimos</a></li>
						<li><a href="#tecnologias">Cookies y servicios externos</a></li>
						<li><a href="#derechos">Tus derechos (ARCO)</a></li>
						<li><a href="#seguridad">Seguridad y conservación</a></li>
						<li><a href="#cambios">Cambios al aviso</a></li>
					</ol>
				</nav>

				<article class="aviso-texto">
					<section id="responsable">
						<h2>1. ¿Quién es responsable de tus datos?</h2>
						<p><strong>Vital Dent</strong>, consultorio dental con domicilio en <strong><?php echo dato_consultorio('direccion_completa'); ?></strong>, teléfono <a href="<?php echo dato_consultorio('telefono_enlace'); ?>"><?php echo dato_consultorio('telefono_legible'); ?></a>, es responsable del uso y protección de tus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>
					</section>

					<section id="datos">
						<h2>2. ¿Qué datos personales recabamos?</h2>
						<p>Los obtenemos cuando creas tu cuenta, llenas el cuestionario inicial, agendas una cita, acudes a consulta o registras un pago, ya sea en este sitio, en la app o en recepción:</p>
						<ul>
							<li><strong>Identificación y contacto:</strong> nombre completo, correo electrónico, teléfono, fecha de nacimiento, sexo y domicilio (calle, colonia, código postal, municipio y estado).</li>
							<li><strong>Datos de tu cuenta:</strong> correo y contraseña. La contraseña se guarda cifrada; nadie del consultorio puede verla.</li>
							<li><strong>Contacto de emergencia:</strong> nombre, parentesco y teléfono de la persona que nos indiques. Te pedimos avisarle que nos compartiste sus datos.</li>
							<li><strong>Citas y pagos:</strong> fechas, horarios y motivos de tus citas; montos, medio de pago y, en transferencias, el número de operación. <strong>No guardamos números de tarjeta.</strong></li>
							<li><strong>Datos de salud:</strong> se detallan en el siguiente apartado.</li>
						</ul>
					</section>

					<section id="sensibles">
						<h2>3. Datos de salud (datos sensibles)</h2>
						<p>Para atenderte necesitamos datos que la ley considera <strong>sensibles</strong>: motivo de consulta, última visita al dentista, tipo de sangre, alergias, enfermedades, medicamentos que tomas, antecedentes odontológicos y hábitos, así como los diagnósticos, recetas, tratamientos y avances que registra tu doctor.</p>
						<p>Solo los usamos para tu atención dental y solo los ven el personal autorizado y el doctor que te atiende. Nos das tu <strong>consentimiento expreso</strong> al marcar la casilla «Acepto el aviso de privacidad» al crear tu cuenta o al llenar el cuestionario inicial; si te registra el doctor en el consultorio, te lo pedimos por escrito.</p>
					</section>

					<section id="finalidades">
						<h2>4. ¿Para qué usamos tus datos?</h2>
						<h3>Finalidades necesarias para atenderte</h3>
						<ul>
							<li>Crear y administrar tu cuenta en el sitio y la app.</li>
							<li>Agendar, confirmar, reprogramar o cancelar tus citas.</li>
							<li>Integrar y actualizar tu expediente clínico.</li>
							<li>Realizar diagnósticos, tratamientos y recetas, y darles seguimiento.</li>
							<li>Registrar tus pagos y emitir tus recibos.</li>
							<li>Comunicarnos contigo sobre tus citas y tratamientos, y contactar a tu contacto de emergencia si fuera necesario.</li>
							<li>Proteger la seguridad de tu cuenta y del sistema.</li>
						</ul>
						<h3>Finalidades adicionales (opcionales)</h3>
						<ul>
							<li>Invitarte a responder encuestas de satisfacción.</li>
							<li>Enviarte información sobre promociones o nuevos tratamientos.</li>
						</ul>
						<p>Puedes negarte a las finalidades adicionales en cualquier momento, sin que eso afecte tu atención, avisándonos por los medios del apartado 7.</p>
					</section>

					<section id="transferencias">
						<h2>5. ¿Con quién compartimos tus datos?</h2>
						<p>No vendemos ni rentamos tus datos. Solo los compartimos:</p>
						<ul>
							<li>Con laboratorios dentales o especialistas que participen en tu tratamiento, cuando sea necesario para atenderte.</li>
							<li>Con autoridades sanitarias o judiciales, cuando una ley o una orden lo exija.</li>
						</ul>
						<p>Cualquier otra transferencia requerirá tu consentimiento.</p>
					</section>

					<section id="tecnologias">
						<h2>6. Cookies y servicios externos</h2>
						<ul>
							<li><strong>Cookie de sesión:</strong> solo usamos una cookie técnica para mantenerte conectado mientras usas tu portal. La sesión se cierra sola tras 15 minutos sin actividad. No usamos cookies de publicidad ni de seguimiento.</li>
							<li><strong>Códigos postales:</strong> al escribir tu código postal en el cuestionario, consultamos el servicio COPOMEX para llenar colonia, municipio y estado. Solo se envía el código postal.</li>
							<li><strong>Tipografías:</strong> el sitio carga sus fuentes desde Google Fonts, que recibe la dirección IP de tu dispositivo.</li>
							<li><strong>WhatsApp:</strong> si nos escribes con el botón de mensajes, la conversación se rige también por las políticas de WhatsApp.</li>
							<li><strong>App y biometría:</strong> si usas la huella o el reconocimiento facial de tu teléfono para confirmar un pago en la app, esa validación la hace tu propio teléfono; Vital Dent no recibe ni guarda tus datos biométricos.</li>
						</ul>
					</section>

					<section id="derechos">
						<h2>7. Tus derechos (ARCO) y cómo ejercerlos</h2>
						<p>Tienes derecho a <strong>Acceder</strong> a tus datos, <strong>Rectificarlos</strong> si son inexactos, <strong>Cancelarlos</strong> cuando ya no sean necesarios y <strong>Oponerte</strong> a su uso para fines específicos. También puedes revocar tu consentimiento o limitar el uso de tus datos.</p>
						<p>Tu correo y teléfono puedes actualizarlos tú mismo en <strong>Mi perfil</strong>. Para cualquier otra solicitud, acude a recepción<?php if ($correoPrivacidad !== ''): ?>, escríbenos a <a href="mailto:<?php echo $e($correoPrivacidad); ?>"><?php echo $e($correoPrivacidad); ?></a><?php endif; ?> o llámanos al <a href="<?php echo dato_consultorio('telefono_enlace'); ?>"><?php echo dato_consultorio('telefono_legible'); ?></a>, indicando:</p>
						<ul>
							<li>Tu nombre y un medio para responderte.</li>
							<li>Una identificación oficial (o la de tu representante legal).</li>
							<li>Qué derecho quieres ejercer y sobre qué datos.</li>
						</ul>
						<p>Te responderemos en un máximo de 20 días hábiles y, si procede, aplicaremos el cambio dentro de los 15 días hábiles siguientes. Algunos datos del expediente clínico no pueden eliminarse antes del plazo que fija la norma de salud.</p>
						<p>Si consideras que tus derechos no fueron atendidos, puedes acudir a la Secretaría Anticorrupción y Buen Gobierno, autoridad encargada de la protección de datos personales.</p>
					</section>

					<section id="seguridad">
						<h2>8. Seguridad y conservación</h2>
						<p>Protegemos tus datos con contraseñas cifradas, acceso por roles (paciente, doctor y administración), cierre automático de sesión y conexión segura. Conservamos tu expediente clínico durante el tiempo que exige la NOM-004-SSA3-2012 del expediente clínico (al menos 5 años desde la última atención) y los demás datos solo mientras sean necesarios para las finalidades descritas.</p>
					</section>

					<section id="cambios">
						<h2>9. Cambios a este aviso</h2>
						<p>Cualquier cambio a este aviso lo publicaremos en esta misma página, indicando la fecha de la última actualización.</p>
						<p class="aviso-volver"><a class="boton-contorno" href="#contenido">Volver arriba</a></p>
					</section>
				</article>
			</div>
		</section>
	</main>
<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>
