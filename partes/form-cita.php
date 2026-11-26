<?php
require_once __DIR__ . '/../../database/modelos.php';

$pacientesCita = $doctoresCita = [];
$errorCitas = false;
try {
	$bd = obtener_conexion();
	$pacientesCita = pacientes_para_selector($bd);
	$doctoresCita = doctores_activos($bd);
} catch (Throwable $falla) {
	error_log('Formulario de cita: ' . $falla->getMessage());
	$errorCitas = true;
}
$idPacienteSeleccionado = (int) ($_GET['paciente'] ?? 0);
$idCitaEditar = (int) ($_GET['cita'] ?? 0);
$citaEditar = null;
$doctorActual = $usuarioActual['rol'] === 'doctor' && !$errorCitas
	? id_doctor_de_usuario($bd, (int) $usuarioActual['id'])
	: null;
if ($usuarioActual['rol'] === 'doctor' && $doctorActual === null) {
	$errorCitas = true;
}
if (!$errorCitas && $idCitaEditar > 0) {
	$citaEditar = cita_para_editar($bd, $idCitaEditar, $usuarioActual['rol'] === 'doctor' ? $doctorActual : null);
	if (!$citaEditar) {
		$errorCitas = true;
	}
}
$doctorSeleccionado = $usuarioActual['rol'] === 'doctor'
	? $doctorActual
	: (int) ($citaEditar['id_doctor'] ?? array_key_first($doctoresCita));
if ($citaEditar) {
	$idPacienteSeleccionado = (int) $citaEditar['id_paciente'];
}
?>
<section class="tarjeta">
	<h2><?php echo $citaEditar ? 'Reprogramar cita' : 'Registrar cita'; ?></h2>
	<?php if ($errorCitas): ?>
		<p class="aviso error" role="alert">No se pudo cargar la información para agendar. Inténtalo más tarde.</p>
	<?php elseif (!$pacientesCita || !$doctoresCita || ($usuarioActual['rol'] === 'doctor' && !$doctorSeleccionado)): ?>
		<p class="aviso alerta">Registra al menos un paciente y un doctor activo antes de agendar.</p>
	<?php else: ?>
		<form class="formulario-consulta" action="../../../router/citas.php" method="post">
			<?php echo campo_csrf(); ?>
			<input type="hidden" name="accion" value="agendar">
			<input type="hidden" name="cita" value="<?php echo (int) ($citaEditar['id_cita'] ?? 0); ?>">
			<input type="hidden" name="volver" value="<?php echo $e($urlAgenda); ?>">
			<div class="campo">
				<label for="paciente">Paciente</label>
				<select class="form-select" id="paciente" name="paciente" required>
					<option value="">Selecciona un paciente</option>
					<?php foreach ($pacientesCita as $pacienteCita): ?>
						<option value="<?php echo (int) $pacienteCita['id_paciente']; ?>"<?php echo $idPacienteSeleccionado === (int) $pacienteCita['id_paciente'] ? ' selected' : ''; ?>><?php echo $e($pacienteCita['nombre']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ($usuarioActual['rol'] === 'administrador'): ?>
				<div class="campo"><label for="doctor">Doctor</label><select class="form-select" id="doctor" name="doctor" required>
					<?php foreach ($doctoresCita as $idDoctorCita => $nombreDoctor): ?><option value="<?php echo (int) $idDoctorCita; ?>"<?php echo (int) $doctorSeleccionado === (int) $idDoctorCita ? ' selected' : ''; ?>><?php echo $e($nombreDoctor); ?></option><?php endforeach; ?>
				</select></div>
			<?php else: ?>
				<input type="hidden" name="doctor" value="<?php echo (int) $doctorSeleccionado; ?>">
				<p class="ayuda">Doctor: <?php echo $e($doctoresCita[$doctorSeleccionado] ?? ''); ?></p>
			<?php endif; ?>
			<div class="rejilla rejilla-2">
				<div class="campo"><label for="fecha">Fecha</label><input class="form-control" type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $e($citaEditar['fecha'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" required></div>
				<div class="campo"><label for="hora">Hora</label><input class="form-control" type="time" id="hora" name="hora" step="1800" value="<?php echo $e($citaEditar['hora'] ?? '09:00'); ?>" required></div>
			</div>
			<div class="campo"><label for="motivo">Motivo</label><input class="form-control" id="motivo" name="motivo" required maxlength="120" value="<?php echo $e($citaEditar['motivo'] ?? ''); ?>"></div>
			<div class="acciones">
				<a class="boton secundario-btn" href="<?php echo $e($urlAgenda); ?>">Volver</a>
				<button class="boton" type="submit"><?php echo $citaEditar ? 'Guardar horario' : 'Agendar cita'; ?></button>
			</div>
		</form>
	<?php endif; ?>
</section>
