<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$buscar = trim((string) ($_GET['buscar'] ?? ''));
$recetas = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$idDoctor = id_doctor_de_usuario($bd, (int) $usuarioActual['id']) ?? -1;
	$recetas = recetas_de_doctor($bd, $idDoctor);
} catch (Throwable $falla) {
	error_log('Recetas: ' . $falla->getMessage());
	$errorBase = true;
}
$lista = array_values(array_filter($recetas, function ($r) use ($buscar) {
	return $buscar === '' || mb_stripos($r['paciente'] . ' ' . $r['folio'] . ' ' . $r['medicamentos'], $buscar) !== false;
}));
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Recetas · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php" aria-current="page">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Recetas</h1><p>Recetas emitidas a tus pacientes</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<form class="filtros" action="recetas.php" method="get" role="search">
					<div class="campo"><label for="buscar">Buscar receta</label><input class="form-control" type="search" id="buscar" name="buscar" value="<?php echo $e($buscar); ?>" placeholder="Paciente, folio o medicamento"></div>
					<button class="boton secundario-btn" type="submit">Buscar</button>
					<a class="boton" href="consultas.php">+ Nueva receta</a>
				</form>
				<section aria-labelledby="t-recetas">
					<h2 id="t-recetas" class="visualmente-oculto">Recetas emitidas</h2>
					<div class="tabla-contenedor"><table class="table table-hover mb-0">
						<caption class="visualmente-oculto">Recetas emitidas por el doctor</caption>
						<thead><tr><th scope="col">Folio</th><th scope="col">Fecha</th><th scope="col">Paciente</th><th scope="col">Medicamentos</th><th scope="col">Consulta</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
						<tbody>
						<?php foreach ($lista as $r): ?>
							<tr>
								<td class="sin-corte"><?php echo $e($r['folio']); ?></td>
								<td class="sin-corte"><time datetime="<?php echo $e($r['fecha']); ?>"><?php echo $e(fecha_corta($r['fecha'], true)); ?></time></td>
								<td><a href="historialMedico.php?id=<?php echo (int) $r['id_paciente']; ?>"><?php echo $e($r['paciente']); ?></a></td>
								<td><?php echo $e($r['medicamentos'] ?? '—'); ?></td>
								<td><?php echo $e($r['motivo'] ?: mb_strimwidth($r['diagnostico'], 0, 60, '…')); ?></td>
								<td><a class="boton secundario-btn boton-chico" href="receta.php?id=<?php echo (int) $r['id_receta']; ?>" aria-label="Ver e imprimir la receta <?php echo $e($r['folio']); ?>">Imprimir</a></td>
							</tr>
						<?php endforeach; ?>
						<?php if (!$lista && !$errorBase): ?><tr><td colspan="6"><?php echo $recetas ? 'No hay recetas que coincidan con la búsqueda.' : 'Todavía no has emitido recetas. Se crean al registrar una consulta con medicamentos.'; ?></td></tr><?php endif; ?>
						</tbody>
					</table></div>
					<p class="nota-tabla">Mostrando <?php echo count($lista); ?> de <?php echo count($recetas); ?> recetas. Las recetas se registran junto con la consulta.</p>
				</section>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
