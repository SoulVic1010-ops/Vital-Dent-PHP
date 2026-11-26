<?php require_once __DIR__ . '/../../database/ConsultorioModelo.php'; ?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Solicita una cita con Vital Dent en Huejutla.">
	<title>Citas · Vital Dent</title>
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
          <li><a class="fw-bold" href="citas.php" aria-current="page">Citas</a></li>
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
		<section class="portada portada-vd" aria-labelledby="titulo-citas">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Agenda tu visita</p>
					<h1 id="titulo-citas">Estamos listos para <span>atenderte</span></h1>
					<p class="portada-descripcion">Solicita una cita desde tu portal y consulta tus horarios disponibles. Si es tu primera visita, crea una cuenta para comenzar.</p>
					<div class="portada-botones">
						<a class="boton-portada boton-portada-principal" href="../private/autenticacion/crearCuenta.php"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18M8 15h3"/></svg>Crear cuenta</a>
						<a class="boton-portada boton-portada-secundario" href="../private/autenticacion/login.php">Ya tengo cuenta</a>
					</div>
				</div>
			<aside class="portada-tarjeta">
					<h2>Horario de atención</h2>
					<div class="tabla-contenedor">
						<table class="table table-sm horario mb-0">
							<tbody>
								<tr>
									<th scope="row">Lunes a viernes</th>
									<td><?php echo dato_consultorio('horario_lv'); ?></td>
								</tr>
								<tr>
									<th scope="row">Sábado</th>
									<td><?php echo dato_consultorio('horario_sab'); ?></td>
								</tr>
								<tr>
									<th scope="row">Domingo</th>
									<td><?php echo dato_consultorio('horario_dom'); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</aside>
			</div>
		</section>
		<section class="seccion">
			<div class="envoltura">
				<div class="seccion-titulo">
					<p class="cat-sobretitulo">Paso a paso</p>
					<h2>Cómo agendar</h2>
					<p>Tu solicitud y confirmación quedan disponibles en el portal.</p>
			 </div>
			 <ol class="rejilla-pasos">
				 <li class="tarjeta">
					 <h3>1. Crea tu cuenta</h3>
					 <p class="secundario">Registra tus datos de contacto.</p>
				 </li>
				 <li class="tarjeta">
					 <h3>2. Elige horario</h3>
					 <p class="secundario">Selecciona el motivo y una hora disponible.</p>
				 </li>
				 <li class="tarjeta">
					 <h3>3. Recibe confirmación</h3>
					 <p class="secundario">Consulta el estado de tu cita en línea.</p>
				 </li>
			 </ol>
		 </div>
	  </section>
	  <section class="seccion alterna">
		  <div class="envoltura llamado">
			  <div>
				  <h2>¿Tienes dudas?</h2>
				  <p class="secundario">Llámanos al <a href="<?php echo dato_consultorio('telefono_enlace'); ?>"><?php echo dato_consultorio('telefono_legible'); ?></a>.</p>
			  </div>
			  <a class="boton secundario-btn" href="contacto.php">Ver contacto</a>
		  </div>
	  </section>
  </main>
<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>

	<script src="../../Public/js/tablas.js" defer></script>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>
