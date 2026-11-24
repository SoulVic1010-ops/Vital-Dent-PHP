<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idPaciente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$idTratamiento = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT) ?: 0;
// ?editar=1 corrige el tratamiento elegido; ?avance=ID corrige ese avance (solo el doctor que lleva el tratamiento)
$pideEditar = ($_GET['editar'] ?? '') === '1';
$idAvanceEditar = filter_input(INPUT_GET, 'avance', FILTER_VALIDATE_INT) ?: 0;
$idDoctor = -1;
$paciente = $seleccion = null;
$tratamientos = $avances = $catalogo = $enCurso = [];
$conIndicaciones = false;
$errorBase = false;
try {
	$bd = obtener_conexion();
	$idDoctor = id_doctor_de_usuario($bd, (int) $usuarioActual['id']) ?? -1;
	$catalogo = catalogo_para_selector($bd);
	$paciente = $idPaciente ? ficha_paciente($bd, $idPaciente) : null;
	if ($paciente) {
		$tratamientos = tratamientos_de($bd, $paciente['id_paciente']);
		foreach ($tratamientos as $t) {
			if ((int) $t['id_tratamiento'] === $idTratamiento) { $seleccion = $t; }
		}
		$seleccion = $seleccion ?? ($tratamientos[0] ?? null);
		$avances = $seleccion ? avances_de($bd, (int) $seleccion['id_tratamiento']) : [];
		$conIndicaciones = avances_tienen_indicaciones($bd);
	} else {
		// sin paciente elegido: tratamientos en curso o pausados que lleva este doctor
		$enCurso = tratamientos_activos_de_doctor($bd, $idDoctor);
	}
} catch (Throwable $falla) {
	error_log('Tratamientos: ' . $falla->getMessage());
	$errorBase = true;
}
$esMio = $seleccion && (int) $seleccion['id_doctor'] === (int) $idDoctor;
$tratEditar = $pideEditar && $esMio ? $seleccion : null;
$avanceEditar = null;
if ($idAvanceEditar && $esMio) {
	foreach ($avances as $a) {
		if ((int) $a['id_avance'] === $idAvanceEditar) { $avanceEditar = $a; }
	}
}
$pestana = 'tratamientos';
$siguiente = $seleccion ? min((int) $seleccion['sesiones_totales'], (int) $seleccion['realizadas'] + 1) : 1;
$sugerido = $avanceEditar ? (int) $avanceEditar['porcentaje'] : ($seleccion ? (int) min(100, round(((int) $seleccion['realizadas'] + 1) / max(1, (int) $seleccion['sesiones_totales']) * 100)) : 0);
$sesiones = fn($n) => $n . ($n == 1 ? ' sesión' : ' sesiones');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tratamientos · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php" aria-current="page">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1><?php echo $paciente ? 'Expediente del paciente' : 'Tratamientos'; ?></h1><p><?php echo $paciente ? 'Pacientes / ' . $e($paciente['nombre']) . ' / Tratamientos' : 'Tratamientos en curso de tus pacientes'; ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<?php if (!$paciente): ?>
				<?php if ($idPaciente && !$errorBase): ?><p class="aviso alerta" role="status">No encontramos ese paciente.</p><?php endif; ?>
				<section aria-labelledby="t-en-curso">
					<div class="barra-acciones"><h2 id="t-en-curso">Tratamientos en curso</h2><a class="boton secundario-btn boton-chico" href="historialpacientes.php">Buscar paciente</a></div>
					<div class="tabla-contenedor" style="margin-top:12px"><table class="table table-hover mb-0">
						<caption class="visualmente-oculto">Tratamientos en curso o pausados de tus pacientes</caption>
						<thead><tr><th scope="col">Paciente</th><th scope="col">Tratamiento</th><th scope="col">Inicio</th><th scope="col">Avance</th><th scope="col">Estado</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
						<tbody>
						<?php foreach ($enCurso as $t): [$clase, $texto] = ESTADOS_TRATAMIENTO[$t['estado']]; ?>
							<tr>
								<td><?php echo $e($t['paciente']); ?><br><span class="secundario"><?php echo $e($t['expediente']); ?></span></td>
								<td><?php echo $e($t['nombre']); ?></td>
								<td class="sin-corte"><time datetime="<?php echo $e($t['fecha_inicio']); ?>"><?php echo $e(fecha_corta($t['fecha_inicio'], true)); ?></time></td>
								<td class="sin-corte"><?php echo (int) $t['realizadas'] . '/' . (int) $t['sesiones_totales'] . ' · ' . (int) $t['porcentaje'] . ' %'; ?></td>
								<td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td>
								<td><a class="boton boton-chico" href="tratamientos.php?id=<?php echo (int) $t['id_paciente']; ?>&amp;t=<?php echo (int) $t['id_tratamiento']; ?>">Registrar avance</a></td>
							</tr>
						<?php endforeach; ?>
						<?php if (!$enCurso && !$errorBase): ?><tr><td colspan="6">No tienes tratamientos en curso. Para iniciar uno, abre el expediente del paciente.</td></tr><?php endif; ?>
						</tbody>
					</table></div>
					<p class="nota-tabla">Para registrar un tratamiento nuevo, busca al paciente y abre la pestaña Tratamientos de su expediente.</p>
				</section>
				<?php else: ?>
				<?php require __DIR__ . '/../../partes/ficha-expediente.php'; ?>
				<div class="rejilla rejilla-lateral">
					<div class="rejilla">
						<section aria-labelledby="t-tratamientos">
							<div class="barra-acciones"><h2 id="t-tratamientos">Tratamientos del paciente</h2><a class="boton boton-chico" href="#registrar-tratamiento">+ Registrar tratamiento</a></div>
							<div class="tabla-contenedor" style="margin-top:12px"><table class="table table-hover mb-0">
								<caption class="visualmente-oculto">Tratamientos de <?php echo $e($paciente['nombre']); ?></caption>
								<thead><tr><th scope="col">Tratamiento</th><th scope="col">Inicio</th><th scope="col">Avance</th><th scope="col">Estado</th></tr></thead>
								<tbody>
								<?php foreach ($tratamientos as $t): [$clase, $texto] = ESTADOS_TRATAMIENTO[$t['estado']]; $actual = $seleccion && $t['id_tratamiento'] === $seleccion['id_tratamiento']; ?>
									<tr>
										<td><a href="tratamientos.php?id=<?php echo (int) $paciente['id_paciente']; ?>&amp;t=<?php echo (int) $t['id_tratamiento']; ?>"<?php echo $actual ? ' aria-current="true"' : ''; ?>><?php echo $e($t['nombre']); ?></a></td>
										<td class="sin-corte"><time datetime="<?php echo $e($t['fecha_inicio']); ?>"><?php echo $e(fecha_corta($t['fecha_inicio'], true)); ?></time></td>
										<td class="sin-corte"><?php echo (int) $t['realizadas'] . '/' . (int) $t['sesiones_totales'] . ' · ' . (int) $t['porcentaje'] . ' %'; ?></td>
										<td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td>
									</tr>
								<?php endforeach; ?>
								<?php if (!$tratamientos): ?><tr><td colspan="4">El paciente aún no tiene tratamientos registrados.</td></tr><?php endif; ?>
								</tbody>
							</table></div>
						</section>
						<?php if ($seleccion): ?>
						<section class="tarjeta" aria-labelledby="t-evolucion">
							<div class="barra-acciones"><h2 id="t-evolucion">Evolución · <?php echo $e($seleccion['nombre']); ?></h2><?php if ($esMio): ?><a class="boton secundario-btn boton-chico" href="tratamientos.php?id=<?php echo (int) $paciente['id_paciente']; ?>&amp;t=<?php echo (int) $seleccion['id_tratamiento']; ?>&amp;editar=1#registrar-tratamiento">Editar tratamiento</a><?php endif; ?></div>
							<p class="secundario"><?php echo $e($seleccion['doctor']); ?> · <?php echo (int) $seleccion['realizadas']; ?> de <?php echo $e($sesiones((int) $seleccion['sesiones_totales'])); ?> · costo <?php echo $e(dinero($seleccion['costo'])); ?> · saldo <?php echo $e(dinero($seleccion['saldo'])); ?></p>
							<?php if ($avances): ?>
							<ol class="linea-tiempo">
							<?php foreach ($avances as $a): $prog = $a['estado'] === 'programada'; ?>
								<li<?php echo $prog ? ' class="programada"' : ''; ?>><div class="cabecera"><span>Sesión <?php echo (int) $a['numero_sesion']; ?><?php echo $prog ? ' (programada)' : ' · ' . (int) $a['porcentaje'] . ' %'; ?></span><time class="secundario" datetime="<?php echo $e($a['fecha']); ?>"><?php echo $e(fecha_corta($a['fecha'], true)); ?></time></div><p class="secundario"><?php echo $e($a['descripcion']); ?></p><?php if (!empty($a['indicaciones'])): ?><p class="sesion-nota"><strong>Cuidados:</strong> <?php echo nl2br($e($a['indicaciones'])); ?></p><?php endif; ?><?php if ($esMio): ?><a class="boton-texto" href="tratamientos.php?id=<?php echo (int) $paciente['id_paciente']; ?>&amp;t=<?php echo (int) $seleccion['id_tratamiento']; ?>&amp;avance=<?php echo (int) $a['id_avance']; ?>#registrar-avance" aria-label="Editar la sesión <?php echo (int) $a['numero_sesion']; ?>">Editar</a><?php endif; ?></li>
							<?php endforeach; ?>
							</ol>
							<?php else: ?><p>Todavía no hay avances registrados.</p><?php endif; ?>
						</section>
						<?php endif; ?>
					</div>

					<div class="rejilla">
						<?php if ($seleccion): ?>
						<section id="registrar-avance" class="tarjeta" aria-labelledby="t-avance">
							<h2 id="t-avance"><?php echo $avanceEditar ? 'Editar avance' : 'Registrar avance'; ?></h2>
							<p class="secundario"><?php echo $e($seleccion['nombre']); ?> · sesión <?php echo $avanceEditar ? (int) $avanceEditar['numero_sesion'] . ($avanceEditar['estado'] === 'programada' ? ' (programada)' : '') : $siguiente; ?> de <?php echo (int) $seleccion['sesiones_totales']; ?></p>
							<form action="../../../router/tratamientos.php" method="post"><?php echo campo_csrf(); ?>
								<input type="hidden" name="accion" value="<?php echo $avanceEditar ? 'editar_avance' : 'avance'; ?>">
								<?php if ($avanceEditar): ?><input type="hidden" name="avance" value="<?php echo (int) $avanceEditar['id_avance']; ?>"><?php endif; ?>
								<input type="hidden" name="paciente" value="<?php echo (int) $paciente['id_paciente']; ?>">
								<input type="hidden" name="tratamiento" value="<?php echo (int) $seleccion['id_tratamiento']; ?>">
								<div class="campo"><label for="fecha">Fecha <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="date" id="fecha" name="fecha" value="<?php echo $e($avanceEditar ? $avanceEditar['fecha'] : date('Y-m-d')); ?>" required></div>
								<label class="opcion"<?php echo $avanceEditar ? ' hidden' : ''; ?>><input type="checkbox" name="programada" value="1" id="programada"<?php echo $avanceEditar && $avanceEditar['estado'] === 'programada' ? ' checked' : ''; ?>> Es una sesión programada (aún no se realiza)</label>
								<div class="campo"><label for="descripcion">Descripción del avance <span class="obligatorio" aria-hidden="true">*</span></label><textarea class="form-control" id="descripcion" name="descripcion" required minlength="3" maxlength="2000" placeholder="Ej. Instrumentación y conformación de 3 conductos."><?php echo $avanceEditar ? $e($avanceEditar['descripcion']) : ''; ?></textarea></div>
								<?php if ($conIndicaciones): ?>
								<div class="campo"><label for="indicaciones">Cuidados para el paciente</label><textarea class="form-control" id="indicaciones" name="indicaciones" maxlength="500" placeholder="Ej. Evita alimentos duros o pegajosos por 48 horas y usa cera si algún bracket lastima." aria-describedby="ayuda-indicaciones"><?php echo $avanceEditar ? $e($avanceEditar['indicaciones'] ?? '') : ''; ?></textarea><p class="ayuda" id="ayuda-indicaciones">Opcional. El paciente los verá en su módulo de tratamientos, debajo de esta sesión.</p></div>
								<?php else: ?>
								<p class="aviso info">Para anotar cuidados para el paciente en cada sesión, importa <code>database/avances_indicaciones.sql</code> en phpMyAdmin.</p>
								<?php endif; ?>
								<div class="campo" id="campo-porcentaje"<?php echo $avanceEditar && $avanceEditar['estado'] === 'programada' ? ' hidden' : ''; ?>><label for="porcentaje">Porcentaje de avance · <output id="porcentaje-valor" for="porcentaje"><?php echo $sugerido; ?> %</output></label><input type="range" id="porcentaje" name="porcentaje" min="0" max="100" step="1" value="<?php echo $sugerido; ?>"></div>
								<div class="campo"><label for="estado">Estado del tratamiento <span class="obligatorio" aria-hidden="true">*</span></label><select class="form-select" id="estado" name="estado" required><?php foreach (ESTADOS_TRATAMIENTO as $clave => [, $texto]): ?><option value="<?php echo $clave; ?>"<?php echo $seleccion['estado'] === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
								<?php if ($avanceEditar): ?>
								<div class="acciones"><a class="boton secundario-btn" href="tratamientos.php?id=<?php echo (int) $paciente['id_paciente']; ?>&amp;t=<?php echo (int) $seleccion['id_tratamiento']; ?>">Cancelar</a><button class="boton" type="submit">Guardar cambios</button></div>
								<?php else: ?>
								<div class="acciones"><button class="boton secundario-btn" type="reset">Limpiar</button><button class="boton" type="submit">Guardar avance</button></div>
								<?php endif; ?>
							</form>
						</section>
						<?php endif; ?>

						<section id="registrar-tratamiento" class="tarjeta" aria-labelledby="t-nuevo">
							<h2 id="t-nuevo"><?php echo $tratEditar ? 'Editar tratamiento' : 'Registrar tratamiento'; ?></h2>
							<?php if ($tratEditar): ?><p class="secundario">Corrige los datos de «<?php echo $e($tratEditar['nombre']); ?>». Los avances ya registrados no cambian.</p><?php endif; ?>
							<form action="../../../router/tratamientos.php" method="post"><?php echo campo_csrf(); ?>
								<input type="hidden" name="accion" value="<?php echo $tratEditar ? 'editar_tratamiento' : 'tratamiento'; ?>">
								<?php if ($tratEditar): ?><input type="hidden" name="tratamiento" value="<?php echo (int) $tratEditar['id_tratamiento']; ?>"><?php endif; ?>
								<input type="hidden" name="paciente" value="<?php echo (int) $paciente['id_paciente']; ?>">
								<div class="campo"><label for="catalogo">Del catálogo</label><select class="form-select" id="catalogo" name="catalogo"><option value="">Otro tratamiento</option><?php foreach ($catalogo as $c): ?><option value="<?php echo (int) $c['id_catalogo']; ?>"<?php echo $tratEditar && (int) $tratEditar['id_catalogo'] === (int) $c['id_catalogo'] ? ' selected' : ''; ?> data-precio="<?php echo $c['precio_desde'] !== null ? $e((string) (float) $c['precio_desde']) : ''; ?>"><?php echo $e($c['nombre']); ?></option><?php endforeach; ?></select></div>
								<div class="campo"><label for="nombre-tratamiento">Nombre <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="nombre-tratamiento" name="nombre" required minlength="3" maxlength="100" placeholder="Ej. Endodoncia pieza 36" value="<?php echo $tratEditar ? $e($tratEditar['nombre']) : ''; ?>"></div>
								<div class="campo"><label for="fecha-inicio">Inicio <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="date" id="fecha-inicio" name="fecha_inicio" value="<?php echo $e($tratEditar ? $tratEditar['fecha_inicio'] : date('Y-m-d')); ?>" required></div>
								<div class="campo"><label for="sesiones">Número de sesiones <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="number" id="sesiones" name="sesiones" min="<?php echo $tratEditar ? max(1, (int) $tratEditar['realizadas']) : 1; ?>" max="99" value="<?php echo $tratEditar ? (int) $tratEditar['sesiones_totales'] : 1; ?>" required<?php echo $tratEditar ? ' aria-describedby="ayuda-sesiones"' : ''; ?>><?php if ($tratEditar): ?><p class="ayuda" id="ayuda-sesiones">No puede ser menor que las <?php echo (int) $tratEditar['realizadas']; ?> sesiones ya realizadas.</p><?php endif; ?></div>
								<div class="campo"><label for="costo">Costo total (MXN) <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="number" id="costo" name="costo" min="0" max="999999" step="0.01" inputmode="decimal" required placeholder="Ej. 5400" value="<?php echo $tratEditar ? $e((string) (float) $tratEditar['costo']) : ''; ?>"></div>
								<?php if ($tratEditar): ?>
								<div class="campo"><label for="estado-tratamiento">Estado <span class="obligatorio" aria-hidden="true">*</span></label><select class="form-select" id="estado-tratamiento" name="estado" required><?php foreach (ESTADOS_TRATAMIENTO as $clave => [, $texto]): ?><option value="<?php echo $clave; ?>"<?php echo $tratEditar['estado'] === $clave ? ' selected' : ''; ?>><?php echo $texto; ?></option><?php endforeach; ?></select></div>
								<div class="acciones"><a class="boton secundario-btn" href="tratamientos.php?id=<?php echo (int) $paciente['id_paciente']; ?>&amp;t=<?php echo (int) $tratEditar['id_tratamiento']; ?>">Cancelar</a><button class="boton" type="submit">Guardar cambios</button></div>
								<?php else: ?>
								<div class="acciones"><button class="boton" type="submit">Registrar tratamiento</button></div>
								<?php endif; ?>
							</form>
						</section>
					</div>
				</div>
				<script>
				(function () {
					var rango = document.getElementById('porcentaje'), programada = document.getElementById('programada');
					if (rango) {
						rango.addEventListener('input', function () { document.getElementById('porcentaje-valor').value = rango.value + ' %'; });
						programada.addEventListener('change', function () { document.getElementById('campo-porcentaje').hidden = programada.checked; });
					}
					// al elegir del catálogo se proponen el nombre y el costo de referencia; el doctor los ajusta al caso del paciente
					var catalogo = document.getElementById('catalogo'), nombre = document.getElementById('nombre-tratamiento'), costo = document.getElementById('costo');
					var costoPropuesto = <?php echo $tratEditar ? "'__editar__'" : "''"; ?>; // al editar no se cambia el costo guardado
					catalogo.addEventListener('change', function () {
						if (!catalogo.value) { return; }
						var opcion = catalogo.options[catalogo.selectedIndex];
						nombre.value = opcion.text;
						var precio = opcion.getAttribute('data-precio') || '';
						// solo se cambia si el doctor no escribió otro costo (si el elegido no tiene precio, se deja vacío)
						if (costo.value === '' || costo.value === costoPropuesto) { costo.value = precio; costoPropuesto = precio; }
					});
				})();
				</script>
				<?php endif; ?>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
