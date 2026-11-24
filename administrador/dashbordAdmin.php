<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('administrador');

require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$mesesLargos = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$hoy = ucfirst($dias[(int) date('w')]) . ' ' . date('j') . ' de ' . $mesesLargos[(int) date('n')] . ' de ' . date('Y');
$porConfirmar = [];
$citasSemana = $personal = '—';
$errorBase = false;
try {
	$bd = obtener_conexion();
	$hoyDt = new DateTime('today');
	$lunes = (clone $hoyDt)->modify('-' . ((int) $hoyDt->format('N') - 1) . ' days')->format('Y-m-d');
	$sabado = date('Y-m-d', strtotime($lunes . ' +5 days'));
	$citasSemana = count(buscar_citas($bd, ['desde' => $lunes, 'hasta' => $sabado, 'estados' => ['pendiente', 'confirmada', 'atendida']]));
	$porConfirmar = buscar_citas($bd, ['desde' => date('Y-m-d'), 'estados' => ['pendiente']]);
	$personal = contar_personal_activo($bd);
} catch (Throwable $falla) {
	error_log('Panel admin: ' . $falla->getMessage());
	$errorBase = true;
}
$cifras = [
	['icono' => 'citas',     'valor' => $citasSemana, 'texto' => 'Citas esta semana'],
	['icono' => 'hora',      'valor' => $errorBase ? '—' : count($porConfirmar), 'texto' => 'Pendientes de confirmar'],
	['icono' => 'pacientes', 'valor' => $personal, 'texto' => 'Usuarios del personal'],
];
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Panel de administración · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="dashbordAdmin.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Administrador</p><nav class="menu" aria-label="Navegación de administración"><ul><li><a href="dashbordAdmin.php" aria-current="page">Panel</a></li><li><a href="confirmarcitas.php">Agenda</a></li><li><a href="pacientes.php">Pacientes</a></li><li><a href="usuarios.php">Usuarios</a></li><li><a href="catalogo.php">Catálogo</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Panel de administración</h1><p><?php echo $e($hoy); ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Administrador</span></div></div>
			<main id="contenido" class="contenido">
<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido y que importaste database/pacientes_citas.sql.</p><?php endif; ?>
				<ul class="rejilla resumen-cifras lista-limpia" aria-label="Resumen del consultorio">
				<?php foreach ($cifras as $c): ?>
					<li class="tarjeta cifra"><span class="icono-caja" aria-hidden="true"><span class="icono-mascara" style="--icono: url('<?php echo url_app('Public/img/iconos/icono-' . $c['icono'] . '.png'); ?>')"></span></span><span><strong class="cifra-valor"><?php echo $e($c['valor']); ?></strong><span class="secundario"><?php echo $e($c['texto']); ?></span></span></li>
				<?php endforeach; ?>
				</ul>
				<div class="rejilla">
					<section aria-labelledby="t-confirmar">
						<div class="barra-acciones"><h2 id="t-confirmar">Citas por confirmar</h2><a class="boton secundario-btn" href="confirmarcitas.php?estado=pendiente">Ir a la agenda</a></div>
						<div class="tabla-contenedor" style="margin-top:12px"><table class="table table-hover mb-0">
							<caption class="visualmente-oculto">Citas que esperan confirmación</caption>
							<thead><tr><th scope="col">Fecha</th><th scope="col">Paciente</th><th scope="col">Motivo</th><th scope="col">Doctor</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
							<tbody>
							<?php foreach (array_slice($porConfirmar, 0, 6) as $c): ?>
								<tr>
									<td class="sin-corte"><?php echo $e(fecha_corta($c['fecha']) . ' · ' . hora_legible($c['hora'])); ?></td>
									<td><?php echo $e($c['paciente']); ?></td>
									<td><?php echo $e($c['motivo']); ?></td>
									<td><?php echo $e($c['doctor']); ?></td>
									<td><form action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="confirmar"><input type="hidden" name="cita" value="<?php echo (int) $c['id_cita']; ?>"><input type="hidden" name="volver" value="dashbordAdmin.php"><button class="boton boton-chico" type="submit">Confirmar</button></form></td>
								</tr>
							<?php endforeach; ?>
							<?php if (!$porConfirmar): ?><tr><td colspan="5">No hay citas pendientes de confirmar.</td></tr><?php endif; ?>
							</tbody>
						</table></div>
					</section>
				</div>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Huejutla, Hgo.</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
