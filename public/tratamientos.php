<?php
declare(strict_types=1);
require_once __DIR__ . '/../../database/modelos.php';

// Tratamientos publicados desde el panel del administrador (Catálogo); si MySQL no responde o no hay ninguno publicado, se muestra el catálogo base.
$catalogo = obtener_catalogo_publico() ?: [
	['categoria' => 'Opción clásica', 'nombre' => 'Brackets metálicos', 'descripcion' => 'Una alternativa tradicional con piezas metálicas colocadas en la parte externa de los dientes.', 'caracteristicas' => "Piezas resistentes\nSe ven al sonreír\nAjustes periódicos en consulta", 'imagen' => 'brackets-metalicos.png'],
	['categoria' => 'Apariencia discreta', 'nombre' => 'Brackets cerámicos', 'descripcion' => 'Se colocan en la parte externa de los dientes y sus piezas son de tono similar al dental.', 'caracteristicas' => "Tono parecido al diente\nMás discretos que los metálicos\nCuidado con alimentos que manchan", 'imagen' => 'brackets-ceramicos.png'],
	['categoria' => 'Piezas transparentes', 'nombre' => 'Brackets de zafiro', 'descripcion' => 'Una opción estética con piezas transparentes que se fijan en la superficie de los dientes.', 'caracteristicas' => "Casi no se notan\nPiezas transparentes\nRequieren limpieza cuidadosa", 'imagen' => 'brackets-zafiro.png'],
	['categoria' => 'Mecanismo integrado', 'nombre' => 'Brackets autoligables', 'descripcion' => 'Incorporan un mecanismo que sujeta el arco sin las ligaduras elásticas tradicionales.', 'caracteristicas' => "Sin ligas elásticas\nCierre integrado en cada pieza\nRevisiones según tu plan", 'imagen' => 'brackets-autoligables.png'],
];
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

// Pestañas del catálogo: solo aparecen los grupos que tienen tratamientos publicados.
$nombresGrupo = ['brackets' => 'Brackets', 'alineadores' => 'Alineadores', 'otros' => 'Otros tratamientos'];
$grupoDe = static function (array $t): string {
	$texto = mb_strtolower(($t['nombre'] ?? '') . ' ' . ($t['categoria'] ?? ''));
	if (str_contains($texto, 'bracket')) { return 'brackets'; }
	if (str_contains($texto, 'alineador')) { return 'alineadores'; }
	return 'otros';
};
$grupos = [];
foreach ($catalogo as $t) { $grupos[$grupoDe($t)] = true; }
$rutaImagen = static fn(string $imagen): string => '../../Public/img/' . implode('/', array_map('rawurlencode', explode('/', $imagen)));
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Conoce los tratamientos odontológicos de Vital Dent.">
	<title>Tratamientos · Vital Dent</title>
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
          <li><a class="fw-bold" href="tratamientos.php" aria-current="page">Tratamientos</a></li>
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
		<section class="portada portada-vd" aria-labelledby="titulo-tratamientos">
			<div class="envoltura">
				<div class="portada-texto">
					<p class="portada-insignia">✦ Servicios odontológicos</p>
					<h1 id="titulo-tratamientos">Tratamientos para cuidar tu <span>salud bucal</span></h1>
					<p class="portada-descripcion">Cada plan comienza con una valoración para conocer tus necesidades y explicarte las opciones disponibles.</p>
					<div class="portada-botones">
						<a class="boton-portada boton-portada-principal" href="citas.php"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18M8 15h3"/></svg>Solicitar una cita</a>
						<a class="boton-portada boton-portada-secundario" href="#titulo-catalogo-brackets">Ver catálogo</a>
					</div>
				</div>
			<aside class="portada-tarjeta">
				<picture><source srcset="../../Public/img/consulta.webp" type="image/webp"><img class="portada-tarjeta-imagen w-100 h-100 border-5" src="../../Public/img/consulta.jpg" alt="Imagen del consultorio dental Vital Dent" width="1200" height="800" decoding="async"></picture>
				<h2>Atención personalizada</h2>
				<p class="secundario">El tratamiento adecuado depende de una valoración clínica. Nuestro equipo te orientará durante cada etapa.</p>
			</aside>
			</div>
		</section>
		<section class="seccion catalogo-seccion" aria-labelledby="titulo-catalogo-brackets">
			<div class="envoltura">
				<div class="seccion-titulo cat-titulo">
					<p class="cat-sobretitulo text-center mx-auto">Catálogo de ortodoncia</p>
					<h2 id="titulo-catalogo-brackets">Encuentra el tipo de brackets ideal para ti</h2>
					<p class="text-center mx-auto">Compara las opciones y solicita una valoración para recibir orientación personalizada.</p>
				</div>
<?php if (count($grupos) > 1): ?>
				<div class="cat-pestanas" role="group" aria-label="Filtrar el catálogo">
					<button type="button" class="activa" data-filtro="todos" aria-pressed="true">Todos</button>
<?php foreach ($nombresGrupo as $clave => $nombreGrupo): if (!isset($grupos[$clave])) { continue; } ?>
					<button type="button" data-filtro="<?php echo $e($clave); ?>" aria-pressed="false"><?php echo $e($nombreGrupo); ?></button>
<?php endforeach; ?>
				</div>
<?php endif; ?>
				<ul class="cat-tarjetas">
<?php foreach ($catalogo as $t): $rasgosTarjeta = lineas_catalogo($t['caracteristicas'] ?? ''); ?>
					<li class="cat-tarjeta" data-grupo="<?php echo $e($grupoDe($t)); ?>">
						<div class="cat-imagen">
<?php if (!empty($t['imagen'])): ?>
							<img src="<?php echo $e($rutaImagen((string) $t['imagen'])); ?>" alt="" loading="lazy" decoding="async" onerror="this.hidden=true">
<?php else: ?>
							<svg width="72" height="72" viewBox="0 0 64 64" aria-hidden="true"><path d="M20 8c-8 0-13 6-13 15 0 8 4 13 6 20 2 8 3 15 7 15 5 0 4-15 12-15s7 15 12 15c4 0 5-7 7-15 2-7 6-12 6-20 0-9-5-15-13-15-6 0-8 3-12 3S26 8 20 8z" fill="#fff" stroke="#9CC3D6" stroke-width="2"/></svg>
<?php endif; ?>
						</div>
						<div class="cat-cuerpo">
<?php if (!empty($t['categoria'])): ?>
							<p class="cat-categoria"><?php echo $e($t['categoria']); ?></p>
<?php endif; ?>
							<h3><?php echo $e($t['nombre']); ?></h3>
							<p class="cat-descripcion"><?php echo $e($t['descripcion']); ?></p>
<?php $precio = precio_catalogo($t['precio_desde'] ?? null); ?>
							<p class="cat-precio"><?php echo $precio ? '<span>Desde</span> <strong>' . $e($precio) . '</strong> <span>MXN</span>' : '<span>Precio según valoración</span>'; ?></p>
<?php if ($rasgosTarjeta): ?>
							<ul class="cat-rasgos">
<?php foreach ($rasgosTarjeta as $rasgo): ?>
								<li><svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><circle cx="9" cy="9" r="9" fill="#E3F1F8"/><path d="M5.2 9.3l2.4 2.4 5-5.2" fill="none" stroke="#01579B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $e($rasgo); ?></li>
<?php endforeach; ?>
							</ul>
<?php endif; ?>
							<a class="cat-boton" href="citas.php">Solicitar valoración<span class="visualmente-oculto"> de <?php echo $e($t['nombre']); ?></span></a>
						</div>
					</li>
<?php endforeach; ?>
				</ul>
				<p class="cat-nota text-center mx-auto">La opción adecuada depende de una valoración clínica; los resultados y tiempos pueden variar en cada persona.</p>
			</div>
		</section>
	</main>
<?php $app = '../'; require __DIR__ . '/../partes/pie-publico.php'; ?>
	<a class="boton-mensaje" href="<?php echo dato_consultorio('whatsapp_url'); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.6 7.4L3 21l1.6-5.2A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4.5"/></svg></a>
	<script src="../../Public/js/catalogo.js" defer></script>
	<script src="../../Public/js/menu.js"></script>
</body>
</html>
