<?php require_once __DIR__ . '/../../database/ConsultorioModelo.php'; ?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Contacto · Vital Dent</title>
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
          <li><a class="fw-bold" href="contacto.php" aria-current="page">Contacto</a></li>
        </ul>
      </nav>
      <div class="acciones">
        <a class="boton secundario-btn" href="../private/autenticacion/crearCuenta.php">Crear cuenta</a>
        <a class="boton" href="../private/autenticacion/login.php">Iniciar sesión</a>
      </div>
    </div>
  </header>
	<main id="contenido">
		<section class="portada portada-vd portada-sola" aria-labelledby="titulo-contacto">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Contacto</p>
					<h1 id="titulo-contacto">Estamos para <span>ayudarte</span></h1>
					<p class="portada-descripcion">Llámanos, escríbenos por WhatsApp o visítanos en el consultorio. Si ya tienes cuenta, también puedes solicitar tu cita en línea.</p>
					<div class="portada-botones">
						<a class="boton-portada boton-portada-principal" href="<?php echo dato_consultorio('telefono_enlace'); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>Llamar al <?php echo dato_consultorio('telefono_legible'); ?></a>
						<a class="boton-portada boton-portada-secundario" href="citas.php">Agendar cita</a>
					</div>
					<ul class="portada-datos">
						<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><b>Lun – Vie</b> <?php echo dato_consultorio('horario_lv_corto'); ?> · <b>Sáb</b> <?php echo dato_consultorio('horario_sab_corto'); ?></li>
						<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg><a href="<?php echo dato_consultorio('mapa_url'); ?>" target="_blank" rel="noopener"><b><?php echo dato_consultorio('direccion'); ?></b>, <?php echo dato_consultorio('ciudad'); ?></a></li>
						<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><a href="<?php echo dato_consultorio('telefono_enlace'); ?>"><b><?php echo dato_consultorio('telefono_legible'); ?></b></a></li>
					</ul>
				</div>
			</div>
		</section>
	</main>
	<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>