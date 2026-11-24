<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idPaciente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$paciente = null;
$registros = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$paciente = ficha_paciente($bd, $idPaciente);
	$registros = $paciente ? historial_clinico($bd, $paciente['id_paciente']) : [];
} catch (Throwable $falla) {
	error_log('Historial médico: ' . $falla->getMessage());
	$errorBase = true;
}
$tipos = ['consulta' => ['neutro', 'Consulta', 'Consultas'], 'receta' => ['pendiente', 'Receta', 'Recetas'], 'avance' => ['ok', 'Avance', 'Avances'], 'tratamiento' => ['neutro', 'Tratamiento', 'Tratamientos'], 'cita' => ['neutro', 'Cita', 'Citas atendidas']];
$periodos = ['todo' => 'Todo el periodo', '6m' => 'Últimos 6 meses', '30d' => 'Últimos 30 días'];
$periodo = isset($periodos[$_GET['periodo'] ?? '']) ? $_GET['periodo'] : 'todo';
$tipo = isset($tipos[$_GET['tipo'] ?? '']) ? $_GET['tipo'] : 'todos';
$desde = ['todo' => '0000-00-00', '6m' => date('Y-m-d', strtotime('-6 months')), '30d' => date('Y-m-d', strtotime('-30 days'))][$periodo];
$visibles = array_values(array_filter($registros, fn($r) => $r['fecha'] >= $desde && ($tipo === 'todos' || $r['tipo'] === $tipo)));
$pestana = 'historial';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Historial médico · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php" aria-current="page">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Expediente del paciente</h1><p><?php echo $paciente ? 'Pacientes / ' . $e($paciente['nombre']) . ' / Historial' : 'Historial médico'; ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<?php if ($paciente): ?>
				<?php require __DIR__ . '/../../partes/ficha-expediente.php'; ?>
				<form class="filtros" method="get" aria-label="Filtrar historial">
					<input type="hidden" name="id" value="<?php echo (int) $paciente['id_paciente']; ?>">
					<div class="campo campo-corto"><label for="periodo">Periodo</label><select class="form-select" id="periodo" name="periodo"><?php foreach ($periodos as $clave => $texto): ?><option value="<?php echo $clave; ?>"<?php echo $periodo === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
					<div class="campo campo-corto"><label for="tipo">Tipo de registro</label><select class="form-select" id="tipo" name="tipo"><option value="todos">Todos los registros</option><?php foreach ($tipos as $clave => [, , $plural]): ?><option value="<?php echo $clave; ?>"<?php echo $tipo === $clave ? ' selected' : ''; ?>><?php echo $plural; ?></option><?php endforeach; ?></select></div>
					<button class="boton secundario-btn" type="submit">Filtrar</button>
					<button class="boton secundario-btn" type="button" id="imprimir">Imprimir historial</button>
				</form>
				<section aria-labelledby="t-historial">
					<h2 id="t-historial" class="visualmente-oculto">Historial médico de <?php echo $e($paciente['nombre']); ?></h2>
					<div class="tabla-contenedor"><table class="table mb-0">
						<caption class="visualmente-oculto">Consultas, recetas, tratamientos, avances y citas atendidas, del más reciente al más antiguo</caption>
						<thead><tr><th scope="col">Fecha</th><th scope="col">Tipo</th><th scope="col">Detalle</th><th scope="col">Registró</th></tr></thead>
						<tbody>
						<?php foreach ($visibles as $r): [$clase, $texto] = $tipos[$r['tipo']]; ?>
							<tr><td class="sin-corte"><time datetime="<?php echo $e($r['fecha']); ?>"><?php echo $e(fecha_corta($r['fecha'], true)); ?></time></td><td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td><td><?php echo $e($r['detalle']); ?><?php if ($r['tipo'] === 'consulta'): ?> <a class="boton-texto" href="consultas.php?editar=<?php echo (int) $r['orden']; ?>" aria-label="Editar la consulta del <?php echo $e(fecha_corta($r['fecha'], true)); ?>">Editar</a><?php endif; ?></td><td class="sin-corte"><?php echo $e($r['doctor']); ?></td></tr>
						<?php endforeach; ?>
						<?php if (!$visibles): ?><tr><td colspan="4"><?php echo $registros ? 'No hay registros con esos filtros.' : 'El paciente todavía no tiene registros clínicos.'; ?></td></tr><?php endif; ?>
						</tbody>
					</table></div>
					<p class="nota-tabla">Mostrando <?php echo count($visibles); ?> de <?php echo count($registros); ?> registros. Cada registro se crea al guardar una consulta, receta o avance. Si te equivocaste en una consulta, usa «Editar» (solo en las que tú registraste); los tratamientos y avances se corrigen en la pestaña Tratamientos.</p>
				</section>
				<script>document.getElementById('imprimir').addEventListener('click', function () { window.print(); });</script>
				<?php elseif (!$errorBase): ?><p class="aviso alerta" role="status">No encontramos ese paciente. <a href="historialpacientes.php">Volver a la lista de pacientes</a></p>
				<?php endif; ?>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
