<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');

require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$buscar = trim((string) ($_GET['buscar'] ?? ''));
$filtros = ['todos' => 'Todos los pacientes', 'proxima' => 'Con próxima cita', 'sin-cuestionario' => 'Cuestionario pendiente'];
$filtro = isset($filtros[$_GET['filtro'] ?? '']) ? $_GET['filtro'] : 'todos';
$pacientes = [];
$total = 0;
$errorBase = false;
try {
	$bd = obtener_conexion();
	$total = contar_pacientes($bd);
	$pacientes = array_values(array_filter(listar_pacientes($bd, $buscar), fn($p) => $filtro === 'todos' || ($filtro === 'proxima' ? $p['proxima'] !== null : !$p['cuestionario_completo'])));
} catch (Throwable $falla) {
	error_log('Pacientes: ' . $falla->getMessage());
	$errorBase = true;
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Pacientes · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php" aria-current="page">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Pacientes</h1><p><?php echo $errorBase ? "Consulta expedientes y citas" : $total . " pacientes registrados"; ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido y que importaste database/pacientes_citas.sql.</p><?php endif; ?>
				<form class="filtros" method="get" role="search" aria-label="Buscar pacientes">
					<div class="campo"><label for="buscar">Buscar paciente</label><input class="form-control" type="search" id="buscar" name="buscar" value="<?php echo $e($buscar); ?>" placeholder="Nombre o expediente"></div>
					<div class="campo campo-corto"><label for="filtro">Mostrar</label><select class="form-select" id="filtro" name="filtro"><?php foreach ($filtros as $clave => $texto): ?><option value="<?php echo $clave; ?>"<?php echo $filtro === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
					<button class="boton secundario-btn" type="submit">Buscar</button>
					<a class="boton" href="registrarPaciente.php">+ Registrar paciente</a>
				</form>
				<section aria-labelledby="t-pacientes">
					<h2 id="t-pacientes" class="visualmente-oculto">Lista de pacientes</h2>
					<div class="tabla-contenedor"><table class="table table-hover mb-0">
						<caption class="visualmente-oculto">Pacientes registrados en Vital Dent</caption>
						<thead><tr><th scope="col">Paciente</th><th scope="col">Expediente</th><th scope="col">Teléfono</th><th scope="col">Última visita</th><th scope="col">Próxima cita</th><th scope="col">Alergias</th><th scope="col"><span class="visualmente-oculto">Acciones</span></th></tr></thead>
						<tbody>
						<?php foreach ($pacientes as $p): ?>
							<tr>
								<td><strong><?php echo $e($p['nombre']); ?></strong><?php if (!$p['cuestionario_completo']): ?><br><span class="secundario">Cuestionario pendiente</span><?php endif; ?></td>
								<td class="sin-corte"><?php echo $e($p['expediente']); ?></td>
								<td class="sin-corte"><?php echo $e($p['telefono'] ?: '—'); ?></td>
								<td class="sin-corte"><?php echo $p['ultima'] ? $e(fecha_corta($p['ultima'], true)) : 'Sin visitas'; ?></td>
								<td class="sin-corte"><?php echo $p['proxima'] ? $e(fecha_corta(substr($p['proxima'], 0, 10)) . ' · ' . hora_legible(substr($p['proxima'], 11, 5))) : '—'; ?></td>
								<td><?php echo $p['alergias'] ? '<span class="estado error">' . $e($p['alergias']) . '</span>' : '<span class="secundario">Ninguna registrada</span>'; ?></td>
								<td><div class="acciones-fila"><a class="boton secundario-btn boton-chico" href="expediente.php?id=<?php echo (int) $p['id_paciente']; ?>">Abrir</a> <a class="boton secundario-btn boton-chico" href="registrarCita.php?paciente=<?php echo (int) $p['id_paciente']; ?>">Agendar cita</a></div></td>
							</tr>
						<?php endforeach; ?>
						<?php if (!$pacientes && !$errorBase): ?><tr><td colspan="7">No hay pacientes que coincidan con la búsqueda.</td></tr><?php endif; ?>
						</tbody>
					</table></div>
					<p class="nota-tabla">Mostrando <?php echo count($pacientes); ?> de <?php echo $total; ?> pacientes. La búsqueda coincide con nombre y expediente.</p>
				</section>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
