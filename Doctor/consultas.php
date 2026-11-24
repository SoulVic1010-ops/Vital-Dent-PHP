<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idPaciente = filter_input(INPUT_GET, 'paciente', FILTER_VALIDATE_INT) ?: 0;
$idCita = filter_input(INPUT_GET, 'cita', FILTER_VALIDATE_INT) ?: 0;
// ?editar=ID: corregir una consulta ya guardada (solo la puede editar el doctor que la registró)
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$edicion = null;
$idDoctor = -1;
$buscar = trim((string) ($_GET['buscar'] ?? ''));
$paciente = null;
$citasAbiertas = $consultasPrevias = $pacientes = $citasHoy = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$idDoctor = id_doctor_de_usuario($bd, (int) $usuarioActual['id']) ?? -1;
	if ($idEditar) {
		$edicion = consulta_para_editar($bd, $idEditar, $idDoctor);
		$idPaciente = $edicion ? (int) $edicion['id_paciente'] : 0;
	}
	$paciente = $idPaciente ? ficha_paciente($bd, $idPaciente) : null;
	if ($paciente) {
		// citas del paciente con este doctor (últimos 30 días) que todavía no tienen consulta registrada
		$citasAbiertas = $edicion ? [] : citas_sin_consulta($bd, $paciente['id_paciente'], $idDoctor);
		$consultasPrevias = consultas_previas($bd, $paciente['id_paciente']);
	} else {
		$pacientes = listar_pacientes($bd, $buscar);
		$citasHoy = buscar_citas($bd, ['desde' => date('Y-m-d'), 'hasta' => date('Y-m-d'), 'id_doctor' => $idDoctor, 'estados' => ['pendiente', 'confirmada']]);
	}
} catch (Throwable $falla) {
	error_log('Consultas: ' . $falla->getMessage());
	$errorBase = true;
}
$idsAbiertas = array_map('intval', array_column($citasAbiertas, 'id_cita'));
if (!in_array($idCita, $idsAbiertas, true)) {
	$idCita = ($citasAbiertas && $citasAbiertas[0]['fecha'] === date('Y-m-d')) ? $idsAbiertas[0] : 0;
}
$corto = fn($texto, $n = 90) => mb_strimwidth((string) $texto, 0, $n, '…');
// Valores del formulario: vacíos para una consulta nueva o los guardados al editar
$valor = fn(string $campo, string $porDefecto = '') => $edicion ? (string) ($edicion[$campo] ?? '') : $porDefecto;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Consultas · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php">Pacientes</a></li><li><a href="consultas.php" aria-current="page">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1><?php echo $edicion ? 'Editar consulta' : 'Registrar consulta'; ?></h1><p><?php echo $paciente ? 'Pacientes / ' . $e($paciente['nombre']) . ($edicion ? ' / Editar consulta del ' . $e(fecha_corta($edicion['fecha'], true)) : ' / Nueva consulta') : 'Elige al paciente para registrar su consulta y receta'; ?></p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<?php if ($idEditar && !$edicion && !$errorBase): ?><p class="aviso alerta" role="status">No puedes editar esa consulta: no existe o la registró otro doctor. Solo el doctor que la registró puede corregirla.</p><?php endif; ?>
				<?php if (!$paciente): ?>
				<?php if ($idPaciente && !$errorBase): ?><p class="aviso alerta" role="status">No encontramos ese paciente. Elígelo de la lista.</p><?php endif; ?>
				<?php if ($citasHoy): ?>
				<section class="tarjeta" aria-labelledby="t-hoy">
					<h2 id="t-hoy">Tus citas de hoy</h2>
					<ul class="lista-simple">
					<?php foreach ($citasHoy as $c): ?>
						<li><span><strong><?php echo $e(hora_legible($c['hora']) . ' · ' . $c['paciente']); ?></strong><br><span class="secundario"><?php echo $e($c['motivo']); ?></span></span><a class="boton boton-chico" href="consultas.php?paciente=<?php echo (int) $c['id_paciente']; ?>&amp;cita=<?php echo (int) $c['id_cita']; ?>">Atender</a></li>
					<?php endforeach; ?>
					</ul>
				</section>
				<?php endif; ?>
				<form class="filtros" action="consultas.php" method="get" role="search">
					<div class="campo"><label for="buscar">Buscar paciente</label><input class="form-control" type="search" id="buscar" name="buscar" value="<?php echo $e($buscar); ?>" placeholder="Nombre o expediente"></div>
					<button class="boton secundario-btn" type="submit">Buscar</button>
				</form>
				<section aria-labelledby="t-elegir">
					<h2 id="t-elegir">Elige al paciente atendido</h2>
					<div class="tabla-contenedor"><table class="table table-hover mb-0">
						<caption class="visualmente-oculto">Pacientes para registrar una consulta</caption>
						<thead><tr><th scope="col">Paciente</th><th scope="col">Expediente</th><th scope="col">Alergias</th><th scope="col">Última visita</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
						<tbody>
						<?php foreach ($pacientes as $p): ?>
							<tr>
								<td><?php echo $e($p['nombre']); ?></td>
								<td><?php echo $e($p['expediente'] ?? '—'); ?></td>
								<td><?php echo $p['alergias'] ? '<span class="estado error">' . $e($p['alergias']) . '</span>' : '—'; ?></td>
								<td><?php echo $p['ultima'] ? $e(fecha_corta($p['ultima'], true)) : '—'; ?></td>
								<td><a class="boton boton-chico" href="consultas.php?paciente=<?php echo (int) $p['id_paciente']; ?>">Registrar consulta</a></td>
							</tr>
						<?php endforeach; ?>
						<?php if (!$pacientes && !$errorBase): ?><tr><td colspan="5">No hay pacientes que coincidan con la búsqueda.</td></tr><?php endif; ?>
						</tbody>
					</table></div>
				</section>
				<?php else: ?>
				<?php if ($paciente['alergias']): ?><p class="aviso error" role="alert">Alergias: <?php echo $e(mb_strtolower(implode(' y ', $paciente['alergias']))); ?>. Revisa la receta.</p><?php endif; ?>

				<form action="../../../router/consultas.php" method="post" class="formulario-consulta" id="form-consulta"><?php echo campo_csrf(); ?>
					<input type="hidden" name="paciente" value="<?php echo (int) $paciente['id_paciente']; ?>">
					<?php if ($edicion): ?><input type="hidden" name="consulta" value="<?php echo (int) $edicion['id_consulta']; ?>">
					<p class="aviso info" role="note">Estás corrigiendo una consulta ya guardada. Al guardar se reemplazan sus datos y su receta<?php echo $edicion['folio'] ? ' (' . $e($edicion['folio']) . ')' : ''; ?>.</p><?php endif; ?>
					<div class="rejilla rejilla-2">
						<section class="tarjeta rejilla" aria-labelledby="t-consulta">
							<h2 id="t-consulta">Consulta</h2>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="paciente-nombre">Paciente</label><input class="form-control" id="paciente-nombre" value="<?php echo $e($paciente['nombre'] . ' · ' . $paciente['expediente']); ?>" readonly aria-describedby="cambiar-paciente"><?php if (!$edicion): ?><p class="ayuda" id="cambiar-paciente"><a href="consultas.php">Cambiar de paciente</a></p><?php endif; ?></div>
								<div class="campo"><label for="fecha">Fecha <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="date" id="fecha" name="fecha" value="<?php echo $e($valor('fecha', date('Y-m-d'))); ?>" max="<?php echo date('Y-m-d'); ?>" required></div>
							</div>
							<?php if ($edicion): ?>
							<div class="campo"><label for="cita-relacionada">Cita relacionada</label><input class="form-control" id="cita-relacionada" readonly value="<?php echo $edicion['cita_fecha'] ? $e(fecha_corta($edicion['cita_fecha'], true) . ' · ' . hora_legible(substr((string) $edicion['cita_hora'], 0, 5)) . ' · ' . $edicion['cita_motivo']) : 'Sin cita'; ?>"></div>
							<?php else: ?>
							<div class="campo"><label for="cita">Cita relacionada</label><select class="form-select" id="cita" name="cita" aria-describedby="ayuda-cita"><option value="">Sin cita</option><?php foreach ($citasAbiertas as $c): ?><option value="<?php echo (int) $c['id_cita']; ?>"<?php echo (int) $c['id_cita'] === $idCita ? ' selected' : ''; ?>><?php echo $e(fecha_corta($c['fecha'], true) . ' · ' . hora_legible($c['hora']) . ' · ' . $c['motivo']); ?></option><?php endforeach; ?></select><p class="ayuda" id="ayuda-cita">Al guardar, la cita elegida queda como atendida.</p></div>
							<?php endif; ?>
							<div class="campo"><label for="diagnostico">Diagnóstico <span class="obligatorio" aria-hidden="true">*</span></label><textarea class="form-control" id="diagnostico" name="diagnostico" required minlength="3" maxlength="2000" placeholder="Ej. Pulpitis irreversible en pieza 36; conductos instrumentados."><?php echo $e($valor('diagnostico')); ?></textarea></div>
							<div class="campo"><label for="observaciones">Observaciones</label><textarea class="form-control" id="observaciones" name="observaciones" maxlength="2000" placeholder="Ej. Paciente refiere menor dolor."><?php echo $e($valor('observaciones')); ?></textarea></div>
							<div class="campo"><label for="seguimiento">Seguimiento <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="seguimiento" name="seguimiento" required minlength="3" maxlength="255" placeholder="Ej. Sesión 3 de endodoncia en 1 semana" value="<?php echo $e($valor('seguimiento')); ?>"></div>
						</section>

						<section id="receta" class="tarjeta rejilla" aria-labelledby="t-receta">
							<div class="barra-acciones"><h2 id="t-receta">Receta</h2><button class="boton secundario-btn boton-chico" type="button" id="agregar-medicamento">+ Agregar medicamento</button></div>
							<div class="tabla-contenedor"><table class="table mb-0" id="tabla-receta">
								<caption class="visualmente-oculto">Medicamentos de la receta</caption>
								<thead><tr><th scope="col">Medicamento</th><th scope="col">Dosis</th><th scope="col">Frecuencia</th><th scope="col">Duración</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
								<tbody><tr id="sin-medicamentos"><td colspan="5" class="secundario" style="white-space:normal">Sin medicamentos. Agrégalos abajo si la consulta lleva receta.</td></tr></tbody>
							</table></div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="medicamento">Medicamento</label><input class="form-control" id="medicamento" maxlength="100" placeholder="Ej. Amoxicilina 500 mg" aria-describedby="error-medicamento"><p class="mensaje-error" id="error-medicamento" hidden></p></div>
								<div class="campo"><label for="dosis">Dosis</label><input class="form-control" id="dosis" maxlength="50" placeholder="1 cápsula"></div>
							</div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="frecuencia">Frecuencia</label><input class="form-control" id="frecuencia" maxlength="50" placeholder="Cada 8 h"></div>
								<div class="campo"><label for="duracion">Duración</label><input class="form-control" id="duracion" maxlength="50" placeholder="7 días"></div>
							</div>
							<div class="campo"><label for="indicaciones">Indicaciones <span class="ayuda">(obligatorias si hay medicamentos)</span></label><textarea class="form-control" id="indicaciones" name="indicaciones" minlength="3" maxlength="2000" placeholder="Ej. Evitar masticar del lado izquierdo. Tomar con alimentos."><?php echo $e($valor('indicaciones')); ?></textarea></div>
						</section>
					</div>
					<div class="acciones">
						<a class="boton secundario-btn" href="<?php echo $edicion ? 'historialMedico.php?id=' . (int) $paciente['id_paciente'] : 'consultas.php'; ?>">Cancelar</a>
						<button class="boton secundario-btn" type="submit" name="accion" value="imprimir" id="guardar-imprimir">Guardar e imprimir receta</button>
						<button class="boton" type="submit" name="accion" value="guardar"><?php echo $edicion ? 'Guardar cambios' : 'Guardar consulta'; ?></button>
					</div>
				</form>

				<?php if ($consultasPrevias): ?>
				<section class="tarjeta" aria-labelledby="t-previas">
					<div class="barra-acciones"><h2 id="t-previas">Consultas anteriores</h2><a class="boton-texto" href="historialMedico.php?id=<?php echo (int) $paciente['id_paciente']; ?>">Ver historial completo</a></div>
					<ul class="lista-simple">
					<?php foreach ($consultasPrevias as $c): ?>
						<li><span><strong><time datetime="<?php echo $e($c['fecha']); ?>"><?php echo $e(fecha_corta($c['fecha'], true)); ?></time></strong> · <?php echo $e($corto($c['diagnostico'])); ?><br><span class="secundario">Seguimiento: <?php echo $e($corto($c['seguimiento'])); ?></span></span><span class="acciones-fila"><?php if ($c['id_receta']): ?><a class="boton secundario-btn boton-chico" href="receta.php?id=<?php echo (int) $c['id_receta']; ?>">Receta <?php echo $e($c['folio']); ?></a><?php endif; ?><?php if ((int) $c['id_doctor'] === (int) $idDoctor && (!$edicion || (int) $c['id_consulta'] !== (int) $edicion['id_consulta'])): ?> <a class="boton secundario-btn boton-chico" href="consultas.php?editar=<?php echo (int) $c['id_consulta']; ?>">Editar</a><?php endif; ?></span></li>
					<?php endforeach; ?>
					</ul>
				</section>
				<?php endif; ?>
				<script>
				// Agrega o quita medicamentos de la receta y avisa si chocan con las alergias del paciente
				(function () {
					var alergias = <?php echo json_encode(array_map('mb_strtolower', $paciente['alergias']), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
					var grupos = {
						'penicilina': ['penicilina', 'amoxicilina', 'ampicilina', 'dicloxacilina'],
						'aines': ['ibuprofeno', 'naproxeno', 'diclofenaco', 'ketorolaco', 'aspirina', 'acetilsalicílico'],
						'sulfas': ['sulfametoxazol', 'sulfadiazina', 'sulfa'],
						'látex': [], 'latex': []
					};
					var nombres = ['medicamento', 'dosis', 'frecuencia', 'duracion'];
					var $ = function (id) { return document.getElementById(id); };
					var campo = $('medicamento'), error = $('error-medicamento'), vacio = $('sin-medicamentos'), indicaciones = $('indicaciones');
					var cuerpo = document.querySelector('#tabla-receta tbody'), indice = 0;
					function revisar() {
						var texto = campo.value.toLowerCase(), choque = null;
						alergias.forEach(function (a) { (grupos[a] || [a]).forEach(function (g) { if (g && texto.indexOf(g) !== -1) { choque = a; } }); });
						error.hidden = !choque; error.textContent = choque ? 'El paciente es alérgico a: ' + choque + '.' : '';
						campo.setAttribute('aria-invalid', choque ? 'true' : 'false');
						return !choque;
					}
					function actualizar() {
						var hay = cuerpo.querySelectorAll('tr[data-medicamento]').length > 0;
						vacio.hidden = hay; indicaciones.required = hay; $('guardar-imprimir').disabled = !hay;
					}
					function agregar() {
						var valores = nombres.map(function (id) { return $(id).value.trim(); });
						if (!valores[0] || !revisar()) { return false; }
						var fila = cuerpo.insertRow(); fila.dataset.medicamento = '1';
						valores.forEach(function (v, i) {
							var celda = fila.insertCell(); celda.textContent = v || '—';
							var oculto = document.createElement('input'); oculto.type = 'hidden';
							oculto.name = 'receta[' + indice + '][' + nombres[i] + ']'; oculto.value = v; celda.appendChild(oculto);
						});
						var quitar = document.createElement('button'); quitar.type = 'button'; quitar.className = 'boton secundario-btn boton-chico'; quitar.textContent = 'Quitar';
						quitar.setAttribute('aria-label', 'Quitar ' + valores[0]); quitar.dataset.quitar = '1'; fila.insertCell().appendChild(quitar);
						indice++;
						nombres.forEach(function (id) { $(id).value = ''; });
						actualizar();
						return true;
					}
					campo.addEventListener('input', revisar);
					$('agregar-medicamento').addEventListener('click', function () { agregar(); campo.focus(); });
					nombres.forEach(function (id) { $(id).addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { ev.preventDefault(); agregar(); campo.focus(); } }); });
					cuerpo.addEventListener('click', function (ev) {
						if (ev.target.dataset && ev.target.dataset.quitar) { ev.target.closest('tr').remove(); actualizar(); campo.focus(); }
					});
					$('form-consulta').addEventListener('submit', function (ev) {
						// si el doctor escribió un medicamento sin presionar "Agregar", se agrega antes de guardar
						if (campo.value.trim() && !agregar()) { ev.preventDefault(); campo.focus(); return; }
						if (indicaciones.required && indicaciones.value.trim().length < 3) { ev.preventDefault(); indicaciones.reportValidity(); }
					});
					// al editar, se cargan los medicamentos que ya tenía la receta
					<?php echo json_encode($edicion ? array_map(static fn($m) => [(string) $m['nombre'], (string) $m['dosis'], (string) $m['frecuencia'], (string) $m['duracion']], $edicion['medicamentos']) : [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>.forEach(function (valores) {
						nombres.forEach(function (id, i) { $(id).value = valores[i] || ''; });
						agregar();
					});
					actualizar();
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
