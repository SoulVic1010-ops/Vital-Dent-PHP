<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('administrador');

require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$roles = ['paciente' => 'Paciente', 'doctor' => 'Doctor', 'administrador' => 'Administrador'];
$estadosCuenta = ['activo' => ['ok', 'Activa'], 'inactivo' => ['neutro', 'Inactiva']];
$buscar = trim((string) ($_GET['buscar'] ?? ''));
$rol = isset($roles[$_GET['rol'] ?? '']) ? $_GET['rol'] : 'todos';
$estado = isset($estadosCuenta[$_GET['estado'] ?? '']) ? $_GET['estado'] : 'todos';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;

$filtrosUsuarios = ['buscar' => $buscar, 'rol' => $rol, 'estado' => $estado];

$cuentas = [];
$total = 0;
$errorBase = false;
try {
	$conexion = obtener_conexion();
	$total = contar_usuarios($conexion, $filtrosUsuarios);
	$paginas = max(1, (int) ceil($total / $porPagina));
	$pagina = min($pagina, $paginas);
	$cuentas = listar_usuarios($conexion, $filtrosUsuarios, $porPagina, ($pagina - 1) * $porPagina);
} catch (PDOException $error) {
	error_log('Cuentas de usuario: ' . $error->getMessage());
	$errorBase = true;
}
$paginas = max(1, (int) ceil($total / $porPagina));
$meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$ultimoAcceso = function ($fechaHora) use ($meses) {
	if (!$fechaHora) { return 'Sin acceso'; }
	$t = strtotime($fechaHora);
	if (date('Y-m-d', $t) === date('Y-m-d')) { return 'Hoy, ' . date('G:i', $t); }
	if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) { return 'Ayer, ' . date('G:i', $t); }
	return date('j', $t) . ' ' . $meses[(int) date('n', $t)] . ' ' . date('Y', $t);
};
$iniciales = function ($nombre) {
	$partes = preg_split('/\s+/u', trim(preg_replace('/^(dr|dra)\.?\s+/iu', '', $nombre))) ?: [];
	return mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1));
};
$enlacePagina = fn($n) => '?' . http_build_query(array_filter(['buscar' => $buscar, 'rol' => $rol === 'todos' ? '' : $rol, 'estado' => $estado === 'todos' ? '' : $estado, 'pagina' => $n > 1 ? $n : '']));
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cuentas de usuario · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="dashbordAdmin.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Administrador</p><nav class="menu" aria-label="Navegación de administración"><ul><li><a href="dashbordAdmin.php">Panel</a></li><li><a href="confirmarcitas.php">Agenda</a></li><li><a href="pacientes.php">Pacientes</a></li><li><a href="usuarios.php" aria-current="page">Usuarios</a></li><li><a href="catalogo.php">Catálogo</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Cuentas de usuario</h1><p>Consulta, desactiva o elimina las cuentas</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Administrador</span></div></div>
			<main id="contenido" class="contenido">
				<?php if (isset($_GET['creado'])): ?><p class="aviso info" role="status">La cuenta se registró correctamente.</p><?php endif; ?>
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<form class="filtros" action="usuarios.php" method="get" role="search">
					<div class="campo"><label for="buscar">Buscar cuenta</label><input class="form-control" type="search" id="buscar" name="buscar" value="<?php echo $e($buscar); ?>" placeholder="Buscar por nombre o correo"></div>
					<div class="campo campo-corto"><label for="rol">Rol</label><select class="form-select" id="rol" name="rol"><option value="todos">Rol: todos</option><?php foreach ($roles as $clave => $texto): ?><option value="<?php echo $clave; ?>"<?php echo $rol === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
					<div class="campo campo-corto"><label for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="todos">Estado: todos</option><?php foreach ($estadosCuenta as $clave => [, $texto]): ?><option value="<?php echo $clave; ?>"<?php echo $estado === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
					<button class="boton secundario-btn" type="submit">Buscar</button>
					<a class="boton" href="registrarUsuario.php">+ Registrar usuario</a>
				</form>
				<section aria-labelledby="t-cuentas">
					<h2 id="t-cuentas" class="visualmente-oculto">Lista de cuentas</h2>
					<div class="tabla-contenedor"><table class="table table-hover mb-0">
						<caption class="visualmente-oculto">Cuentas de usuario con su rol, estado y último acceso</caption>
						<thead><tr><th scope="col">Usuario</th><th scope="col">Correo</th><th scope="col">Rol</th><th scope="col">Estado</th><th scope="col">Último acceso</th><th scope="col"><span class="visualmente-oculto">Acciones</span></th></tr></thead>
						<tbody>
						<?php foreach ($cuentas as $c): [$clase, $texto] = $estadosCuenta[$c['estado']] ?? ['neutro', $c['estado']]; ?>
							<tr>
								<td><span class="usuario-celda"><span class="avatar" aria-hidden="true"><?php echo $e($iniciales($c['nombre'])); ?></span><strong><?php echo $e($c['nombre']); ?></strong></span></td>
								<td><?php echo $e($c['correo']); ?></td>
								<td><?php echo $e($roles[$c['rol']] ?? $c['rol']); ?></td>
								<td><span class="estado <?php echo $clase; ?>"><?php echo $e($texto); ?></span></td>
								<td class="sin-corte"><?php echo $e($ultimoAcceso($c['ultimo_acceso'])); ?></td>
								<td>
									<?php if ((int) $c['id'] === (int) $usuarioActual['id']): ?><span class="secundario">Tu cuenta</span><?php else:
										$activa = $c['estado'] === 'activo';
										$confirmar = [
											'paciente' => '¿Eliminar a ' . $c['nombre'] . '? Se borrará su cuenta y todo su expediente: citas, consultas, recetas y tratamientos. Esta acción no se puede deshacer.',
											'doctor' => '¿Eliminar al doctor ' . $c['nombre'] . '? Solo se puede si no tiene citas, consultas ni tratamientos registrados. Esta acción no se puede deshacer.',
											'administrador' => '¿Eliminar la cuenta de administrador de ' . $c['nombre'] . '? Esta acción no se puede deshacer.',
										][$c['rol']] ?? '¿Eliminar esta cuenta? Esta acción no se puede deshacer.'; ?>
									<div class="acciones-fila">
										<form action="../../../router/usuarios.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="usuario" value="<?php echo (int) $c['id']; ?>"><input type="hidden" name="estado" value="<?php echo $activa ? 'inactivo' : 'activo'; ?>"><button class="boton secundario-btn boton-chico" type="submit" aria-label="<?php echo $e(($activa ? 'Desactivar' : 'Activar') . ' la cuenta de ' . $c['nombre']); ?>"><?php echo $activa ? 'Desactivar' : 'Activar'; ?></button></form>
										<form action="../../../router/usuarios.php" method="post" data-confirmar="<?php echo $e($confirmar); ?>"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="usuario" value="<?php echo (int) $c['id']; ?>"><button class="boton peligro boton-chico" type="submit" aria-label="<?php echo $e('Eliminar la cuenta de ' . $c['nombre']); ?>">Eliminar</button></form>
									</div>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php if (!$cuentas && !$errorBase): ?><tr><td colspan="6">No hay cuentas que coincidan con la búsqueda.</td></tr><?php endif; ?>
						</tbody>
					</table></div>
					<div class="barra-acciones" style="margin-top:12px">
						<p class="nota-tabla">Mostrando <?php echo count($cuentas); ?> de <?php echo $total; ?> cuentas.</p>
						<?php if ($paginas > 1): ?>
						<nav aria-label="Páginas de cuentas"><ul class="paginacion">
							<li><?php if ($pagina > 1): ?><a href="<?php echo $e($enlacePagina($pagina - 1)); ?>" aria-label="Página anterior">‹</a><?php else: ?><span aria-hidden="true">‹</span><?php endif; ?></li>
							<?php for ($n = 1; $n <= $paginas; $n++): ?><li><a href="<?php echo $e($enlacePagina($n)); ?>"<?php echo $n === $pagina ? ' aria-current="page"' : ''; ?>><?php echo $n; ?></a></li><?php endfor; ?>
							<li><?php if ($pagina < $paginas): ?><a href="<?php echo $e($enlacePagina($pagina + 1)); ?>" aria-label="Página siguiente">›</a><?php else: ?><span aria-hidden="true">›</span><?php endif; ?></li>
						</ul></nav>
						<?php endif; ?>
					</div>
				</section>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Huejutla, Hgo.</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
