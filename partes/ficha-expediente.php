<?php
/**
 * Encabezado del expediente del paciente y pestañas (Figma W17, W18 y W20).
 * Antes de incluirlo define: $e, $paciente (nombre, expediente, edad, telefono, alergias[]) y $pestana.
 */
$partesNombre = preg_split('/\s+/u', trim($paciente['nombre'])) ?: [];
$inicialesPaciente = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1) . mb_substr($partesNombre[1] ?? '', 0, 1));
$idFicha = isset($paciente['id_paciente']) ? (int) $paciente['id_paciente'] : 0;
$pestanasExpediente = [
	'datos'        => ['expediente.php' . ($idFicha ? '?id=' . $idFicha : ''), 'Datos generales'],
	'historial'    => ['historialMedico.php' . ($idFicha ? '?id=' . $idFicha : ''), 'Historial médico'],
	'consultas'    => ['consultas.php' . ($idFicha ? '?paciente=' . $idFicha : ''), 'Consultas'],
	'tratamientos' => ['tratamientos.php' . ($idFicha ? '?id=' . $idFicha : ''), 'Tratamientos'],
];
?>
<section class="tarjeta ficha-paciente" aria-label="Paciente">
	<span class="avatar avatar-grande" aria-hidden="true"><?php echo $e($inicialesPaciente); ?></span>
	<div class="ficha-datos"><h2><?php echo $e($paciente['nombre']); ?></h2><p class="secundario"><?php echo $e(implode(' · ', array_filter([$paciente['expediente'] ?? '', isset($paciente['edad']) && $paciente['edad'] !== null ? $paciente['edad'] . ' años' : '', $paciente['telefono'] ?? '']))); ?></p></div>
	<p class="ficha-alergias"><?php foreach (($paciente['alergias'] ?? []) as $a): ?><span class="estado error">Alergia: <?php echo $e(mb_strtolower($a)); ?></span> <?php endforeach; ?></p>
	<a class="boton" href="consultas.php<?php echo isset($paciente['id_paciente']) ? '?paciente=' . (int) $paciente['id_paciente'] : ''; ?>">+ Nueva consulta</a>
</section>
<nav aria-label="Secciones del expediente"><ul class="pestanas">
	<?php foreach ($pestanasExpediente as $clave => [$url, $texto]): ?>
	<li><a href="<?php echo $url; ?>"<?php echo $pestana === $clave ? ' aria-current="page"' : ''; ?>><?php echo $texto; ?></a></li>
	<?php endforeach; ?>
</ul></nav>
