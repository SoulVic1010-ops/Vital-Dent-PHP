<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');

require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$mesesLargos = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$hoy = ucfirst($dias[(int) date('w')]) . ' ' . date('j') . ' de ' . $mesesLargos[(int) date('n')] . ' de ' . date('Y');
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$citasHoy = [];
$pendientes = $totalPacientes = $enCurso = '—';
$errorBase = false;
try {
	$bd = obtener_conexion();
	$idDoctor = id_doctor_de_usuario($bd, (int) $usuarioActual['id']) ?? -1;
	$citasHoy = buscar_citas($bd, ['desde' => date('Y-m-d'), 'hasta' => date('Y-m-d'), 'id_doctor' => $idDoctor, 'estados' => ['pendiente', 'confirmada', 'atendida']]);
	$pendientes = count(buscar_citas($bd, ['desde' => date('Y-m-d'), 'id_doctor' => $idDoctor, 'estados' => ['pendiente']]));
	$totalPacientes = contar_pacientes($bd);
	$enCurso = contar_tratamientos_en_curso($bd, $idDoctor);
} catch (Throwable $falla) {
	error_log('Panel doctor: ' . $falla->getMessage());
	$errorBase = true;
}
$cifras = [
	['icono' => 'citas',        'valor' => $errorBase ? '—' : count($citasHoy), 'texto' => 'Citas de hoy'],
	['icono' => 'hora',         'valor' => $pendientes, 'texto' => 'Pendientes de confirmar'],
	['icono' => 'tratamientos', 'valor' => $enCurso, 'texto' => 'Tratamientos en curso'],
	['icono' => 'pacientes',    'valor' => $totalPacientes, 'texto' => 'Pacientes registrados'],
];
$accesos = [
	['registrarPaciente.php', 'pacientes', 'Registrar paciente'], ['historialpacientes.php', 'perfil', 'Buscar paciente'],
	['consultas.php', 'historial', 'Registrar consulta'], ['recetas.php', 'historial', 'Ver recetas'], ['registrarCita.php', 'citas', 'Registrar cita'],
];
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Panel · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php" aria-current="page">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1><?php echo $saludo . ', ' . $e($usuarioActual['nombre']); ?></h1><p><?php echo $e($hoy); ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido y que importaste database/pacientes_citas.sql.</p><?php endif; ?>
				<ul class="rejilla resumen-cifras lista-limpia" aria-label="Resumen del día">
				<?php foreach ($cifras as $c): ?>
					<li class="tarjeta cifra"><span class="icono-caja" aria-hidden="true"><span class="icono-mascara" style="--icono: url('<?php echo url_app('Public/img/iconos/icono-' . $c['icono'] . '.png'); ?>')"></span></span><span><strong class="cifra-valor"><?php echo $e($c['valor']); ?></strong><span class="secundario"><?php echo $e($c['texto']); ?></span></span></li>
				<?php endforeach; ?>
				</ul>
				<div class="rejilla rejilla-lateral">
					<section aria-labelledby="t-hoy">
						<div class="barra-acciones"><h2 id="t-hoy">Citas de hoy</h2><a class="boton secundario-btn" href="misCistas.php">Ver agenda</a></div>
						<div class="tabla-contenedor" style="margin-top:12px"><table class="table table-hover mb-0">
							<caption class="visualmente-oculto">Citas programadas para hoy</caption>
							<thead><tr><th scope="col">Hora</th><th scope="col">Paciente</th><th scope="col">Motivo</th><th scope="col">Estado</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
							<tbody>
							<?php foreach ($citasHoy as $c): [$clase, $texto] = ESTADOS_CITA[$c['estado']]; ?>
								<tr>
									<td class="sin-corte"><?php echo $e(hora_legible($c['hora'])); ?></td>
									<td><a href="expediente.php?id=<?php echo (int) $c['id_paciente']; ?>"><?php echo $e($c['paciente']); ?></a></td>
									<td><?php echo $e($c['motivo']); ?></td>
									<td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td>
									<td>
									<?php if ($c['estado'] === 'confirmada'): ?>
										<a class="boton boton-chico" href="consultas.php?paciente=<?php echo (int) $c['id_paciente']; ?>&amp;cita=<?php echo (int) $c['id_cita']; ?>">Atender</a>
									<?php elseif ($c['estado'] === 'pendiente'): ?>
										<form action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="confirmar"><input type="hidden" name="cita" value="<?php echo (int) $c['id_cita']; ?>"><input type="hidden" name="volver" value="panel.php"><button class="boton secundario-btn boton-chico" type="submit">Confirmar</button></form>
									<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							<?php if (!$citasHoy && !$errorBase): ?><tr><td colspan="5">No tienes citas para hoy.</td></tr><?php endif; ?>
							</tbody>
						</table></div>
					</section>
					<nav class="tarjeta" aria-labelledby="t-accesos">
						<h2 id="t-accesos">Accesos rápidos</h2>
						<ul class="accesos-lista">
						<?php foreach ($accesos as [$url, $icono, $texto]): ?>
							<li><a href="<?php echo $url; ?>"><span class="icono-mascara" style="--icono: url('<?php echo url_app('Public/img/iconos/icono-' . $icono . '.png'); ?>')" aria-hidden="true"></span><?php echo $texto; ?></a></li>
						<?php endforeach; ?>
						</ul>
					</nav>
				</div>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
