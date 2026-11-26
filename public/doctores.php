<?php
declare(strict_types=1);
require_once __DIR__ . '/../../database/modelos.php';

$doctores = [];
$errorBase = false;
try {
	$doctores = doctores_publicos(obtener_conexion());
} catch (Throwable $falla) {
	error_log('Directorio público de doctores: ' . $falla->getMessage());
	$errorBase = true;
}
$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Doctores · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Playfair+Display:wght@600;700&amp;display=swap" rel="stylesheet">
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
		<section class="portada portada-vd portada-sola" aria-labelledby="titulo-doctores">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Equipo médico</p>
					<h1 id="titulo-doctores">Nuestros <span>doctores</span></h1>
					<p class="portada-descripcion">Conoce al equipo de Vital Dent: especialistas que te acompañan desde la valoración hasta el seguimiento de tu tratamiento.</p>
					<div class="portada-botones">
						<a class="boton-portada boton-portada-principal" href="citas.php"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18M8 15h3"/></svg>Solicitar una cita</a>
						<a class="boton-portada boton-portada-secundario" href="quienesSomos.php">Quiénes somos</a>
					</div>
				</div>
			</div>
		</section>
		<section class="seccion">
			<div class="envoltura">
		
		<?php if ($errorBase): ?>
			<p class="aviso error" role="alert">No se pudo consultar el directorio. Inténtalo más tarde.</p>
		<?php elseif (!$doctores): ?>
			<p class="aviso info">Pronto publicaremos nuestro equipo médico.</p>
		<?php else: ?>
			<ul class="rejilla rejilla-3">
			<?php foreach ($doctores as $doctor): ?>
				<li class="tarjeta">
					<h2><?php echo $e($doctor['nombre']); ?></h2>
					<p><?php echo $e($doctor['especialidad']); ?></p>
					<?php if ($doctor['biografia']): ?><p><?php echo nl2br($e($doctor['biografia'])); ?></p><?php endif; ?>
					<a class="boton" href="citas.php">Solicitar una cita</a>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
			</div>
		</section>
	</main>
	<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>
