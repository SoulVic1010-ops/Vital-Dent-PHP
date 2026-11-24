<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$proximas = $anteriores = $doctores = $ocupados = [];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$paciente = paciente_de_usuario($bd, (int) $usuarioActual['id']);
	foreach (buscar_citas($bd, ['id_paciente' => (int) $paciente['id_paciente']], 'c.fecha DESC, c.hora DESC') as $c) {
		if ($c['fecha'] >= date('Y-m-d') && in_array($c['estado'], ['pendiente', 'confirmada'], true)) { array_unshift($proximas, $c); } else { $anteriores[] = $c; }
	}
	$doctores = doctores_activos($bd);
	$ocupados = horarios_ocupados($bd);
} catch (Throwable $falla) {
	error_log('Mis citas: ' . $falla->getMessage());
	$errorBase = true;
}
$manana = date('Y-m-d', strtotime('+1 day'));
$fechaInicial = date('w', strtotime($manana)) === '0' ? date('Y-m-d', strtotime('+2 day')) : $manana;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal del paciente de Vital Dent: mis citas.">
  <title>Mis citas · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<!-- RF-34 Consultar citas · RF-41 Agendar cita · RF-19 Validar disponibilidad · RNF-07, RNF-08, RNF-09 -->
<body>
  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  <div class="portal">
    <!-- Barra lateral: marca, rol y navegación principal del paciente -->
    <header class="lateral">
      <a class="marca" href="dashbord.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a>
      <p class="rol">Rol: Paciente</p>
      <nav class="menu" aria-label="Navegación del paciente">
        <ul>
          <li><a href="dashbord.php">Inicio</a></li>
          <li><a href="citas.php" aria-current="page">Mis citas</a></li>
          <li><a href="tratamiento.php">Tratamientos</a></li>
          <li><a href="historial.php">Historial médico</a></li>
          <li><a href="perfil.php">Mi perfil</a></li>
        </ul>
      </nav>
      <!-- RF-06: cerrar sesión se envía con POST para invalidar la sesión en el servidor -->
      <form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?>
        <button type="submit">Cerrar sesión</button>
      </form>
    </header>

    <div class="principal">
      <!-- Encabezado de la página: título y usuario con sesión -->
      <div class="barra-superior">
        <div>
          <h1>Mis citas</h1>
          <p>Consulta tus citas y agenda una nueva</p>
        </div>
        <div class="usuario">
          <span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span>
          <span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Paciente</span>
        </div>
      </div>

      <main id="contenido" class="contenido">
        <?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo consultar la base de datos. Revisa que MySQL esté encendido y que importaste database/pacientes_citas.sql.</p><?php endif; ?>
        <div class="rejilla rejilla-lateral">
          <div class="rejilla">
            <!-- RF-34: listado de citas próximas y anteriores -->
            <section aria-labelledby="t-proximas">
              <h2 id="t-proximas">Próximas citas</h2>
              <div class="tabla-contenedor">
                <table>
                  <caption class="visualmente-oculto">Citas programadas</caption>
                  <thead><tr><th scope="col">Fecha</th><th scope="col">Hora</th><th scope="col">Motivo</th><th scope="col">Doctor</th><th scope="col">Estado</th><th scope="col"><span class="visualmente-oculto">Acción</span></th></tr></thead>
                  <tbody>
                  <?php foreach ($proximas as $c): [$clase, $texto] = ESTADOS_CITA[$c['estado']]; ?>
                    <tr>
                      <td class="sin-corte"><time datetime="<?php echo $e($c['fecha']); ?>"><?php echo $e(fecha_corta($c['fecha'], true)); ?></time></td>
                      <td class="sin-corte"><?php echo $e(hora_legible($c['hora'])); ?></td>
                      <td><?php echo $e($c['motivo']); ?></td>
                      <td><?php echo $e($c['doctor']); ?></td>
                      <td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td>
                      <td><button class="boton peligro boton-chico" type="button" data-cancelar="<?php echo (int) $c['id_cita']; ?>" data-detalle="<?php echo $e($c['motivo'] . ' · ' . fecha_corta($c['fecha']) . ' · ' . hora_legible($c['hora'])); ?>">Cancelar</button></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$proximas): ?><tr><td colspan="6">No tienes citas próximas. Agenda una en el formulario.</td></tr><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </section>

            <section aria-labelledby="t-anteriores">
              <h2 id="t-anteriores">Citas anteriores</h2>
              <div class="tabla-contenedor">
                <table>
                  <caption class="visualmente-oculto">Historial de citas</caption>
                  <thead><tr><th scope="col">Fecha</th><th scope="col">Hora</th><th scope="col">Motivo</th><th scope="col">Estado</th></tr></thead>
                  <tbody>
                  <?php foreach (array_slice($anteriores, 0, 10) as $c): [$clase, $texto] = ESTADOS_CITA[$c['estado']]; ?>
                    <tr><td class="sin-corte"><?php echo $e(fecha_corta($c['fecha'], true)); ?></td><td class="sin-corte"><?php echo $e(hora_legible($c['hora'])); ?></td><td><?php echo $e($c['motivo']); ?></td><td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td></tr>
                  <?php endforeach; ?>
                  <?php if (!$anteriores): ?><tr><td colspan="4">Aún no tienes citas anteriores.</td></tr><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </section>
          </div>

          <!-- RF-41 y RF-19: agendar cita mostrando solo horarios disponibles -->
          <section id="agendar" class="tarjeta" aria-labelledby="t-agendar">
            <h2 id="t-agendar">Agendar nueva cita</h2>
            <form id="form-agendar" action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?>
              <input type="hidden" name="accion" value="solicitar"><input type="hidden" name="volver" value="citas.php">
              <p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
              <div class="campo">
                <label for="doctor">Doctor <span class="obligatorio" aria-hidden="true">*</span></label>
                <select id="doctor" name="doctor" required>
                  <?php foreach ($doctores as $idD => $nombreD): ?><option value="<?php echo (int) $idD; ?>"><?php echo $e($nombreD); ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="campo">
                <label for="fecha">Fecha <span class="obligatorio" aria-hidden="true">*</span></label>
                <input type="date" id="fecha" name="fecha" value="<?php echo $e($fechaInicial); ?>" min="<?php echo date('Y-m-d'); ?>" required aria-describedby="aviso-fecha">
                <p class="mensaje-error" id="aviso-fecha" role="alert" hidden></p>
              </div>
              <fieldset>
                <legend>Hora disponible <span class="obligatorio" aria-hidden="true">*</span></legend>
                <div class="horarios" id="horarios">
                <?php foreach (horarios_del_dia($fechaInicial) as $h): ?>
                  <label><input type="radio" name="hora" value="<?php echo $h; ?>" required><span><?php echo $e(str_replace([' a. m.', ' p. m.'], '', hora_legible($h))); ?></span></label>
                <?php endforeach; ?>
                </div>
                <p class="ayuda">Los horarios tachados ya están ocupados.</p>
              </fieldset>
              <div class="campo">
                <label for="motivo">Motivo de la cita <span class="obligatorio" aria-hidden="true">*</span></label>
                <select id="motivo" name="motivo" required>
                  <option value="">Selecciona un motivo</option>
                  <?php foreach (MOTIVOS_CITA as $m): ?><option><?php echo $e($m); ?></option><?php endforeach; ?>
                </select>
              </div>
              <p class="ayuda">Tu cita quedará <strong>pendiente</strong> hasta que el consultorio la confirme.</p>
              <button class="boton" type="submit">Solicitar cita</button>
            </form>
          </section>
        </div>

        <dialog class="dialogo" id="dialogo-cancelar" aria-labelledby="t-cancelar">
          <form action="../../../router/citas.php" method="post"><?php echo campo_csrf(); ?>
            <input type="hidden" name="accion" value="cancelar"><input type="hidden" name="volver" value="citas.php"><input type="hidden" name="cita" id="cancelar-id">
            <h2 id="t-cancelar">¿Cancelar tu cita?</h2>
            <p class="secundario" id="cancelar-detalle"></p>
            <div class="campo"><label for="motivo-cancelacion">Motivo (opcional)</label><textarea id="motivo-cancelacion" name="motivo_cancelacion" maxlength="255" placeholder="Ej. No podré asistir ese día"></textarea></div>
            <div class="acciones"><button class="boton secundario-btn" type="button" data-cerrar>Volver</button><button class="boton peligro" type="submit">Sí, cancelar cita</button></div>
          </form>
        </dialog>
        <script>
        // Horarios libres según doctor y fecha (los ocupados se muestran tachados) y confirmación al cancelar
        (function () {
          var ocupados = <?php echo json_encode($ocupados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
          var fecha = document.getElementById('fecha'), doctor = document.getElementById('doctor'), caja = document.getElementById('horarios'), aviso = document.getElementById('aviso-fecha');
          function corta(h) { var p = h.split(':'), n = +p[0]; return (n % 12 || 12) + ':' + p[1]; }
          function pintar() {
            var dia = new Date(fecha.value + 'T12:00:00').getDay(), tomadas = ocupados[fecha.value + '|' + doctor.value] || [], ahora = new Date();
            caja.innerHTML = '';
            aviso.hidden = true;
            if (!fecha.value) { return; }
            if (dia === 0) { aviso.textContent = 'El consultorio no abre en domingo. Elige otra fecha.'; aviso.hidden = false; return; }
            for (var m = 540, fin = dia === 6 ? 840 : 1080; m < fin; m += 30) {
              var h = ('0' + Math.floor(m / 60)).slice(-2) + ':' + ('0' + m % 60).slice(-2);
              var ocupado = tomadas.indexOf(h) !== -1 || new Date(fecha.value + 'T' + h + ':00') < ahora;
              var l = document.createElement('label'), r = document.createElement('input'), s = document.createElement('span');
              r.type = 'radio'; r.name = 'hora'; r.value = h; r.required = true; r.disabled = ocupado; s.textContent = corta(h);
              l.append(r, s);
              if (ocupado) { var o = document.createElement('span'); o.className = 'visualmente-oculto'; o.textContent = ' (ocupado)'; l.appendChild(o); }
              caja.appendChild(l);
            }
            if (!caja.querySelector('input:not(:disabled)')) { aviso.textContent = 'Ya no hay horarios libres ese día. Elige otra fecha.'; aviso.hidden = false; }
          }
          fecha.addEventListener('change', pintar); doctor.addEventListener('change', pintar); pintar();
          var dialogo = document.getElementById('dialogo-cancelar'), origen = null;
          if (dialogo && dialogo.showModal) {
            document.querySelectorAll('[data-cancelar]').forEach(function (b) { b.addEventListener('click', function () { origen = b; document.getElementById('cancelar-id').value = b.dataset.cancelar; document.getElementById('cancelar-detalle').textContent = b.dataset.detalle; dialogo.showModal(); }); });
            dialogo.querySelector('[data-cerrar]').addEventListener('click', function () { dialogo.close(); });
            dialogo.addEventListener('close', function () { if (origen) { origen.focus(); } });
          }
        })();
        </script>
      </main>

      <footer class="pie">
        <p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Centro, Huejutla, Hgo. · Tel. <a href="tel:+527898961020">789 896 1020</a></p>
      </footer>
    </div>
  </div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
