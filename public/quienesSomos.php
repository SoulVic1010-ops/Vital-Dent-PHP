<?php
declare(strict_types=1);
require_once __DIR__ . '/../../database/modelos.php';

$doctores = [];
$errorBase = false;
try {
    $doctores = doctores_publicos(obtener_conexion(), 3);
} catch (Throwable $falla) {
    error_log('Equipo médico en «Quiénes somos»: ' . $falla->getMessage());
    $errorBase = true;
}
$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Quienes Somos · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Playfair+Display:wght@600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../Public/css/stylePublic.css">


	<link rel="manifest" href="../../Public/manifest.webmanifest">
	<meta name="theme-color" content="#01579B">
	<script src="../../Public/js/instalar.js" defer></script>
</head>
<body class="publico">
   <a class="saltar" href="#contenido">Saltar al contenido principal</a>
  <header class="publico-cabecera">
    <div class="envoltura">
      <a class="marca" href="../index.php"><img src="<?php echo imagen_consultorio('logo_url', '../../'); ?>" alt=""><span>Vital Dent</span></a>
      <nav class="publico-menu" aria-label="Secciones del sitio">
        <ul>
          <li><a class="fw-bold" href="../index.php">Inicio</a></li>
          <li><a class="fw-bold" href="quienesSomos.php" aria-current="page">Quienes Somos</a></li>
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
		<section class="portada portada-vd portada-sola" aria-labelledby="titulo-nosotros">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Quiénes somos</p>
					<h1 id="titulo-nosotros">Conoce <span>Vital Dent</span></h1>
					<p class="portada-descripcion">Somos un centro clínico de ortodoncia en Huejutla enfocado en tu salud bucal, con atención personalizada y seguimiento constante en cada etapa de tu tratamiento.</p>
					<div class="portada-botones">
						<a class="boton-portada boton-portada-principal" href="#instalar-app" data-instalar-app><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="3.5" width="19" height="13" rx="2"/><path d="M8 20.5h8M12 16.5v4M12 6.5v6M9.5 10l2.5 2.5 2.5-2.5"/></svg>Descargar app web de Vital Dent</a>
						<a class="boton-portada boton-portada-secundario" href="doctores.php">Ver doctores</a>
					</div>
				</div>
			</div>
		</section>
  <section id="nosotros" class="seccion" aria-labelledby="t-nosotros">
    <div class="envoltura">
      <div class="seccion-titulo">
        <h2 id="t-nosotros"><?php echo dato_consultorio('nombre'); ?></h2>
        <p><?php echo nl2br(dato_consultorio('quienes_somos')); ?></p>
      </div>
    </div>
    <?php if (consultorio()['mision'] || consultorio()['vision']): ?>
      <div class="envoltura mt-4">
        <ul class="rejilla-servicios">
          <?php if (consultorio()['mision']): ?><li class="tarjeta servicio p-3"><h3>Misión</h3><p><?php echo nl2br(dato_consultorio('mision')); ?></p></li><?php endif; ?>
          <?php if (consultorio()['vision']): ?><li class="tarjeta servicio p-3"><h3>Visión</h3><p><?php echo nl2br(dato_consultorio('vision')); ?></p></li><?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </section>
    <section id="doctores" class="seccion" aria-labelledby="t-doctores">
      <div class="envoltura">
        <div class="seccion-titulo">
          <h2 id="t-doctores">Nuestros doctores</h2>
          <p>Conoce a nuestro equipo de profesionales dedicados a tu salud bucal.</p>
        </div>
<?php if ($errorBase): ?>
        <p class="aviso error mb-0" role="alert">No se pudo consultar nuestro equipo médico. Inténtalo más tarde.</p>
<?php elseif ($doctores === []): ?>
        <p class="secundario text-center mb-0">Muy pronto podrás conocer a nuestro equipo médico.</p>
<?php else: ?>
        <ul class="doc-tarjetas">
<?php foreach ($doctores as $doctor): ?>
<?php
    $foto = basename((string) ($doctor['foto'] ?? ''));
    $rutaFoto = __DIR__ . '/../../Public/img/doctores/' . $foto;
?>
          <li class="cat-tarjeta doc-tarjeta">
            <div class="doc-foto">
<?php if ($foto !== '' && is_file($rutaFoto)): ?>
              <img src="../../Public/img/doctores/<?= rawurlencode($foto) ?>" alt="" loading="lazy" decoding="async">
<?php else: ?>
              <span class="doc-iniciales" aria-hidden="true"><?= $e(iniciales((string) $doctor['nombre'])) ?></span>
<?php endif; ?>
            </div>
            <div class="cat-cuerpo">
<?php if (!empty($doctor['especialidad'])): ?>
              <p class="cat-categoria"><?= $e($doctor['especialidad']) ?></p>
<?php endif; ?>
              <h3><?= $e($doctor['nombre']) ?></h3>
<?php if (!empty($doctor['biografia'])): ?>
              <p class="cat-descripcion doc-bio"><?= nl2br($e($doctor['biografia'])) ?></p>
<?php endif; ?>
              <a class="cat-boton" href="citas.php">Agendar cita<span class="visualmente-oculto"> con <?= $e($doctor['nombre']) ?></span></a>
            </div>
          </li>
<?php endforeach; ?>
        </ul>
        <p class="doc-ver-todos"><a class="boton-contorno" href="doctores.php">Ver todos los doctores</a></p>
<?php endif; ?>
      </div>
    </section>
	</main>
	<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>