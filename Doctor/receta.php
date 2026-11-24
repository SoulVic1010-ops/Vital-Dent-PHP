<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idReceta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$receta = null;
$medicamentos = $alergias = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$receta = receta_completa($bd, $idReceta);
	if ($receta) {
		$medicamentos = medicamentos_de_receta($bd, $idReceta);
		$alergias = alergias_de($bd, (int) $receta['id_paciente']);
	}
} catch (Throwable $falla) {
	error_log('Receta: ' . $falla->getMessage());
	$errorBase = true;
}
$o = fn($v) => ($v === null || $v === '') ? '—' : $v;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Receta · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php" aria-current="page">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Receta</h1><p><?php echo $receta ? 'Recetas / ' . $e($receta['folio']) . ' · ' . $e($receta['paciente']) : 'Recetas'; ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<?php if (!$receta): ?>
				<?php if (!$errorBase): ?><p class="aviso alerta" role="status">No encontramos esa receta.</p><?php endif; ?>
				<p><a class="boton secundario-btn" href="recetas.php">Volver a recetas</a></p>
				<?php else: ?>
				<div class="barra-acciones no-imprimir">
					<a class="boton secundario-btn" href="recetas.php">Volver a recetas</a>
					<button class="boton" type="button" id="imprimir">Imprimir receta</button>
				</div>
				<article class="tarjeta documento" aria-labelledby="t-documento">
					<header class="documento-encabezado">
						<div class="documento-marca"><img src="../../../Public/img/logo-vital-icono.png" alt=""><div><strong>Consultorio Vital Dent</strong><br><span class="secundario">Av. Juárez 45, Centro, Huejutla, Hgo. · Tel. 789 896 1020</span></div></div>
						<dl class="documento-folio"><dt>Folio</dt><dd><?php echo $e($receta['folio']); ?></dd><dt>Fecha</dt><dd><time datetime="<?php echo $e($receta['fecha']); ?>"><?php echo $e(fecha_corta($receta['fecha'], true)); ?></time></dd></dl>
					</header>
					<h2 id="t-documento">Receta médica</h2>
					<dl class="datos">
						<dt>Doctor</dt><dd><?php echo $e($receta['doctor'] . ' · ' . $receta['especialidad']); ?></dd>
						<dt>Cédula profesional</dt><dd><?php echo $e($receta['cedula_profesional']); ?></dd>
						<dt>Paciente</dt><dd><?php echo $e($receta['paciente']); ?></dd>
						<dt>Expediente</dt><dd><?php echo $e($o($receta['expediente'])); ?></dd>
						<dt>Edad</dt><dd><?php $edadPaciente = edad($receta['fecha_nacimiento']); echo $edadPaciente === null ? '—' : $edadPaciente . ' años'; ?></dd>
						<dt>Alergias</dt><dd><?php echo $alergias ? $e(implode(', ', $alergias)) : 'Ninguna registrada'; ?></dd>
					</dl>
					<div class="tabla-contenedor"><table class="table mb-0">
						<caption class="visualmente-oculto">Medicamentos recetados</caption>
						<thead><tr><th scope="col">Medicamento</th><th scope="col">Dosis</th><th scope="col">Frecuencia</th><th scope="col">Duración</th></tr></thead>
						<tbody>
						<?php foreach ($medicamentos as $m): ?>
							<tr><td><?php echo $e($m['nombre']); ?></td><td><?php echo $e($o($m['dosis'])); ?></td><td><?php echo $e($o($m['frecuencia'])); ?></td><td><?php echo $e($o($m['duracion'])); ?></td></tr>
						<?php endforeach; ?>
						</tbody>
					</table></div>
					<h3>Indicaciones</h3>
					<p><?php echo nl2br($e($receta['indicaciones'])); ?></p>
					<p class="documento-firma"><span><?php echo $e($receta['doctor']); ?></span><br><span class="secundario">Firma del doctor</span></p>
				</article>
				<script>document.getElementById('imprimir').addEventListener('click', function () { window.print(); });</script>
				<?php endif; ?>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
