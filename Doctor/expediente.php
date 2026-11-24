<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');

require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idPaciente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$editar = isset($_GET['editar']);
$pestana = 'datos';
$registro = null;
$alergias = $antecedentes = $citasPaciente = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	if (!$idPaciente) {
		$idPaciente = primer_paciente_id($bd);
	}
	$registro = paciente_por_id($bd, $idPaciente);
	if ($registro) {
		$alergias = alergias_de($bd, $idPaciente);
		$antecedentes = antecedentes_de($bd, $idPaciente);
		$citasPaciente = buscar_citas($bd, ['id_paciente' => $idPaciente], 'c.fecha DESC, c.hora DESC');
	}
} catch (Throwable $falla) {
	error_log('Expediente: ' . $falla->getMessage());
	$errorBase = true;
}
$paciente = $registro ? ['id_paciente' => $idPaciente, 'nombre' => $registro['nombre'], 'expediente' => $registro['expediente'], 'edad' => edad($registro['fecha_nacimiento']), 'telefono' => $registro['telefono'], 'alergias' => $alergias] : null;
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaLarga = function ($iso) use ($meses) { if (!$iso) { return '—'; } [$a, $m, $d] = explode('-', $iso); return (int) $d . ' de ' . $meses[(int) $m] . ' de ' . $a; };
$sexos = ['F' => 'Mujer', 'M' => 'Hombre', 'otro' => 'Otro / prefiere no decir'];
$sangrado = ['si' => 'Sí', 'a-veces' => 'A veces', 'no' => 'No'];
$o = fn($v) => ($v === null || $v === '') ? '—' : $v;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Expediente del paciente · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php" aria-current="page">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Expediente del paciente</h1><p>Pacientes / <?php echo $e($registro["nombre"] ?? "Paciente"); ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido y que importaste database/pacientes_citas.sql.</p><?php endif; ?>
				<?php if (!$registro && !$errorBase): ?>
				<p class="aviso alerta" role="status">No encontramos ese paciente. <a href="historialpacientes.php">Volver a la lista de pacientes</a>.</p>
				<?php elseif ($registro): ?>
				<?php require __DIR__ . '/../../partes/ficha-expediente.php'; ?>
				<div class="rejilla rejilla-2">
					<section class="tarjeta rejilla" aria-labelledby="t-personales">
						<div class="barra-acciones"><h2 id="t-personales">Datos personales</h2>
						<?php if ($editar): ?><span class="estado pendiente">Editando</span><?php else: ?><a class="boton secundario-btn boton-chico" href="?id=<?php echo $idPaciente; ?>&amp;editar=1#t-personales">Editar datos</a><?php endif; ?></div>
						<?php if ($editar): ?>
						<form class="rejilla" action="../../../router/pacientes.php" method="post"><?php echo campo_csrf(); ?>
							<input type="hidden" name="accion" value="actualizar"><input type="hidden" name="paciente" value="<?php echo $idPaciente; ?>">
							<p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
							<div class="campo"><label for="nombre">Nombre completo <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="nombre" name="nombre" value="<?php echo $e($registro['nombre']); ?>" required minlength="2" maxlength="120"></div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="nacimiento">Fecha de nacimiento <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="date" id="nacimiento" name="fecha_nacimiento" value="<?php echo $e($registro['fecha_nacimiento']); ?>" max="<?php echo date('Y-m-d'); ?>" required></div>
								<div class="campo"><label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="tel" id="telefono" name="telefono" value="<?php echo $e($registro['telefono']); ?>" pattern="[0-9 ]{10,14}" required aria-describedby="ayuda-tel"><p class="ayuda" id="ayuda-tel">10 dígitos.</p></div>
							</div>
							<div class="campo"><label for="correo">Correo</label><input class="form-control" type="email" id="correo" name="correo" value="<?php echo $e($registro['correo']); ?>"></div>
							<div class="campo"><label for="domicilio">Domicilio</label><input class="form-control" id="domicilio" name="domicilio" value="<?php echo $e($registro['domicilio']); ?>" maxlength="200"></div>
							<div class="acciones"><a class="boton secundario-btn" href="expediente.php?id=<?php echo $idPaciente; ?>">Cancelar</a><button class="boton" type="submit">Guardar cambios</button></div>
						</form>
						<?php else: ?>
						<dl class="datos">
							<dt>Nombre completo</dt><dd><?php echo $e($registro['nombre']); ?></dd>
							<dt>Fecha de nacimiento</dt><dd><?php echo $e($fechaLarga($registro['fecha_nacimiento']) . ($paciente['edad'] !== null ? ' (' . $paciente['edad'] . ' años)' : '')); ?></dd>
							<dt>Sexo</dt><dd><?php echo $e($sexos[$registro['sexo']] ?? '—'); ?></dd>
							<dt>Teléfono</dt><dd><?php echo $registro['telefono'] ? '<a href="tel:' . $e(preg_replace('/\D/', '', $registro['telefono'])) . '">' . $e($registro['telefono']) . '</a>' : '—'; ?></dd>
							<dt>Correo</dt><dd><?php echo $e($o($registro['correo'])); ?></dd>
							<dt>Domicilio</dt><dd><?php echo $e($o($registro['domicilio'])); ?></dd>
							<dt>Contacto de emergencia</dt><dd><?php echo $e($registro['contacto_emergencia'] ? $registro['contacto_emergencia'] . ($registro['parentesco'] ? ' (' . mb_strtolower($registro['parentesco']) . ')' : '') . ($registro['tel_emergencia'] ? ' · ' . $registro['tel_emergencia'] : '') : '—'); ?></dd>
						</dl>
						<?php endif; ?>
					</section>
					<section class="tarjeta rejilla" aria-labelledby="t-antecedentes">
						<div class="barra-acciones"><h2 id="t-antecedentes">Antecedentes y alergias</h2><span class="estado <?php echo $registro['cuestionario_completo'] ? 'ok' : 'pendiente'; ?>"><?php echo $registro['cuestionario_completo'] ? 'Cuestionario completo' : 'Cuestionario pendiente'; ?></span></div>
						<dl class="datos">
							<dt>Alergias</dt><dd><?php if ($alergias): foreach ($alergias as $a): ?><span class="estado error"><?php echo $e($a); ?></span> <?php endforeach; else: ?>Ninguna registrada<?php endif; ?></dd>
							<dt>Motivo de consulta</dt><dd><?php echo $e($o($registro['motivo_consulta'])); ?></dd>
							<dt>Tipo de sangre</dt><dd><?php echo $e($o($registro['tipo_sangre'] === 'desconocido' ? 'No lo sabe' : $registro['tipo_sangre'])); ?></dd>
							<dt>Antecedentes odontológicos</dt><dd><?php echo $e(isset($antecedentes['odontologico']) ? implode(', ', $antecedentes['odontologico']) : '—'); ?></dd>
							<dt>Última visita al dentista</dt><dd><?php echo $e($o($registro['ultima_visita'])); ?></dd>
							<dt>Sangrado de encías</dt><dd><?php echo $e($sangrado[$registro['sangrado']] ?? '—'); ?></dd>
							<dt>Enfermedades</dt><dd><?php echo $e($o($registro['enfermedades'])); ?></dd>
							<dt>Medicamentos</dt><dd><?php echo $e($o($registro['medicamentos'])); ?></dd>
							<dt>Hábitos</dt><dd><?php echo $e(isset($antecedentes['habito']) ? implode(', ', $antecedentes['habito']) : '—'); ?></dd>
							<dt>Observaciones</dt><dd><?php echo $e($o($registro['observaciones'])); ?></dd>
						</dl>
					</section>
				</div>
				<section class="tarjeta rejilla" aria-labelledby="t-citas-paciente">
					<div class="barra-acciones"><h2 id="t-citas-paciente">Citas</h2><a class="boton boton-chico" href="registrarCita.php?paciente=<?php echo $idPaciente; ?>">+ Agendar cita</a></div>
					<?php if ($citasPaciente): ?>
					<ul class="lista-simple">
					<?php foreach (array_slice($citasPaciente, 0, 8) as $c): [$clase, $texto] = ESTADOS_CITA[$c['estado']]; ?>
						<li><span><strong><?php echo $e(fecha_corta($c['fecha'], true) . ' · ' . hora_legible($c['hora'])); ?></strong><br><span class="secundario"><?php echo $e($c['motivo'] . ' · ' . $c['doctor']); ?></span></span><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></li>
					<?php endforeach; ?>
					</ul>
					<?php else: ?><p class="secundario">Este paciente todavía no tiene citas.</p><?php endif; ?>
				</section>
				<?php endif; ?>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
