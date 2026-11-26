<?php
require_once __DIR__ . '/../../database/modelos.php';

$citasAgenda = [];
$errorAgenda = false;
$filtrosAgenda = [];
try {
	$bd = obtener_conexion();
	if ($usuarioActual['rol'] === 'doctor') {
		$idDoctorAgenda = id_doctor_de_usuario($bd, (int) $usuarioActual['id']);
		if ($idDoctorAgenda === null) {
			$errorAgenda = true;
		} else {
			$filtrosAgenda['id_doctor'] = $idDoctorAgenda;
		}
	}
	if (in_array($_GET['estado'] ?? '', ['pendiente', 'confirmada', 'cancelada', 'atendida'], true)) {
		$filtrosAgenda['estados'] = [(string) $_GET['estado']];
	}
	if (!empty($_GET['desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['desde'])) {
		$filtrosAgenda['desde'] = (string) $_GET['desde'];
	}
	if (!$errorAgenda) {
		$citasAgenda = buscar_citas($bd, $filtrosAgenda);
	}
} catch (Throwable $falla) {
	error_log('Agenda de citas: ' . $falla->getMessage());
	$errorAgenda = true;
}
?>
<section class="tarjeta">
	<div class="barra-acciones"><h2>Citas</h2><a class="boton" href="<?php echo $e($urlCita); ?>">Registrar cita</a></div>
	<?php if ($errorAgenda): ?><p class="aviso error" role="alert">No se pudo cargar la agenda. Verifica la conexión y los permisos del doctor.</p><?php endif; ?>
	<form class="barra-acciones" method="get">
		<label for="estado-agenda">Estado</label>
		<select id="estado-agenda" name="estado">
			<option value="">Todas</option>
			<?php foreach (['pendiente' => 'Pendientes', 'confirmada' => 'Confirmadas', 'atendida' => 'Atendidas', 'cancelada' => 'Canceladas'] as $claveEstado => $nombreEstado): ?>
				<option value="<?php echo $claveEstado; ?>"<?php echo ($_GET['estado'] ?? '') === $claveEstado ? ' selected' : ''; ?>><?php echo $nombreEstado; ?></option>
			<?php endforeach; ?>
		</select>
		<button class="boton secundario-btn" type="submit">Filtrar</button>
	</form>
	<div class="tabla-contenedor"><table>
		<thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Doctor</th><th>Motivo</th><th>Estado</th><th>Acciones</th></tr></thead>
		<tbody>
		<?php foreach ($citasAgenda as $citaAgenda): ?>
			<tr>
				<td><?php echo $e(fecha_corta($citaAgenda['fecha'], true)); ?></td>
				<td><?php echo $e(hora_legible($citaAgenda['hora'])); ?></td>
				<td><?php echo $e($citaAgenda['paciente']); ?></td>
				<td><?php echo $e($citaAgenda['doctor']); ?></td>
				<td><?php echo $e($citaAgenda['motivo']); ?></td>
				<td><?php echo $e(ESTADOS_CITA[$citaAgenda['estado']][1]); ?></td>
				<td class="acciones-fila">
					<?php if ($citaAgenda['estado'] === 'pendiente'): ?>
						<form action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="confirmar"><input type="hidden" name="cita" value="<?php echo (int) $citaAgenda['id_cita']; ?>"><input type="hidden" name="volver" value="<?php echo $e($urlCita === 'Reprogramarcitas.php' ? 'confirmarcitas.php' : 'misCistas.php'); ?>"><button class="boton boton-chico" type="submit">Confirmar</button></form>
					<?php endif; ?>
					<?php if (in_array($citaAgenda['estado'], ['pendiente', 'confirmada'], true)): ?>
						<a class="boton secundario-btn boton-chico" href="<?php echo $e($urlCita . '?cita=' . (int) $citaAgenda['id_cita']); ?>">Reprogramar</a>
						<form action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="cancelar"><input type="hidden" name="cita" value="<?php echo (int) $citaAgenda['id_cita']; ?>"><input type="hidden" name="volver" value="<?php echo $e($urlCita === 'Reprogramarcitas.php' ? 'confirmarcitas.php' : 'misCistas.php'); ?>"><button class="boton peligro boton-chico" type="submit">Cancelar</button></form>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php if (!$citasAgenda): ?><tr><td colspan="7">No hay citas para mostrar.</td></tr><?php endif; ?>
		</tbody>
	</table></div>
</section>
