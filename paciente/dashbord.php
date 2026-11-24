<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$proxima = null;
$cuestionarioPendiente = false;
$enCurso = $actividad = [];
try {
	$bd = obtener_conexion();
	$pacienteBD = paciente_de_usuario($bd, (int) $usuarioActual['id']);
	$cuestionarioPendiente = !$pacienteBD['cuestionario_completo'];
	$proxima = buscar_citas($bd, ['id_paciente' => (int) $pacienteBD['id_paciente'], 'desde' => date('Y-m-d'), 'estados' => ['pendiente', 'confirmada']])[0] ?? null;
	$tratamientos = tratamientos_de($bd, (int) $pacienteBD['id_paciente']);
	$enCurso = array_values(array_filter($tratamientos, fn($t) => $t['estado'] !== 'finalizado'));
	// actividad reciente: últimos registros clínicos
	$textos = ['consulta' => 'Consulta registrada', 'receta' => 'Nueva receta', 'avance' => 'Avance de tratamiento', 'tratamiento' => 'Tratamiento iniciado', 'cita' => 'Cita atendida'];
	foreach (array_slice(historial_clinico($bd, (int) $pacienteBD['id_paciente']), 0, 4) as $r) {
		$actividad[] = ['fecha' => $r['fecha'], 'texto' => $textos[$r['tipo']] . ': ' . mb_strimwidth(preg_replace('/^RX-\d+: /', '', $r['detalle']), 0, 70, '…')];
	}
	usort($actividad, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));
	$actividad = array_slice($actividad, 0, 4);
} catch (Throwable $falla) {
	error_log('Panel paciente: ' . $falla->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal del paciente de Vital Dent: inicio.">
  <title>Inicio · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
  <script src="../../../Public/js/dashboard.js" defer></script>
</head>
<body>
  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  <div class="portal">
    <!-- Barra lateral: marca, rol y navegación principal del paciente -->
    <header class="lateral">
      <a class="marca" href="dashbord.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a>
      <p class="rol">Rol: Paciente</p>
      <nav class="menu" aria-label="Navegación del paciente">
        <ul>
          <li><a href="dashbord.php" aria-current="page">Inicio</a></li>
          <li><a href="citas.php">Mis citas</a></li>
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
          <h1>Hola, <?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></h1>
          <p>Este es el resumen de tu atención</p>
        </div>
        <div class="usuario">
          <span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span>
          <span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Paciente</span>
        </div>
      </div>

      <main id="contenido" class="contenido">
        <?php if ($cuestionarioPendiente): ?><p class="aviso alerta" role="status">Aún no llenas tu cuestionario inicial. El doctor lo necesita antes de tu primera consulta. <a href="bienvenida.php">Llenarlo ahora</a></p><?php endif; ?>
        <!-- Resumen rápido: próxima cita -->
        <div class="rejilla">
          <section class="tarjeta destacado" aria-labelledby="t-proxima">
            <h2 id="t-proxima">Tu próxima cita</h2>
            <?php if ($proxima): [$claseCita, $textoCita] = ESTADOS_CITA[$proxima['estado']]; ?>
            <p><strong><?php echo $e($proxima['motivo']); ?></strong></p>
            <p class="secundario">
              <time datetime="<?php echo $e($proxima['fecha'] . 'T' . $proxima['hora']); ?>"><?php echo $e(fecha_corta($proxima['fecha'], true) . ', ' . hora_legible($proxima['hora'])); ?></time> · <?php echo $e($proxima['doctor']); ?>
            </p>
            <p><span class="estado <?php echo $claseCita; ?>"><?php echo $textoCita; ?></span></p>
            <?php else: ?>
            <p>No tienes citas próximas.</p>
            <p><a class="boton secundario-btn" href="citas.php#agendar">Agendar cita</a></p>
            <?php endif; ?>
          </section>
        </div>

        <!-- Accesos rápidos a los módulos del paciente -->
        <nav aria-label="Accesos rápidos">
          <ul class="rejilla rejilla-accesos" style="list-style:none;margin:0;padding:0">
            <li><a class="tarjeta acceso" href="citas.php#agendar"><strong>Agendar cita</strong><span>Elige día y hora</span></a></li>
            <li><a class="tarjeta acceso" href="tratamiento.php"><strong>Tratamientos</strong><span><?php echo count($enCurso) === 1 ? '1 en curso' : count($enCurso) . ' en curso'; ?></span></a></li>
            <li><a class="tarjeta acceso" href="historial.php"><strong>Historial médico</strong><span>Solo lectura</span></a></li>
            <li><a class="tarjeta acceso" href="perfil.php"><strong>Mi perfil</strong><span>Datos de contacto</span></a></li>
          </ul>
        </nav>

        <div class="rejilla rejilla-2">
          <section class="tarjeta" aria-labelledby="t-avance">
            <h2 id="t-avance">Avance de tratamientos</h2>
            <?php foreach ($enCurso as $i => $t): ?>
            <p<?php echo $i ? ' style="margin-top:12px"' : ''; ?>><label for="av-<?php echo (int) $t['id_tratamiento']; ?>"><?php echo $e($t['nombre'] . ' · ' . $t['realizadas'] . ' de ' . $t['sesiones_totales'] . ' sesiones'); ?></label></p>
            <progress id="av-<?php echo (int) $t['id_tratamiento']; ?>" max="100" value="<?php echo (int) $t['porcentaje']; ?>"><?php echo (int) $t['porcentaje']; ?> %</progress>
            <?php endforeach; ?>
            <?php if (!$enCurso): ?><p class="secundario">No tienes tratamientos en curso.</p><?php endif; ?>
          </section>

          <section class="tarjeta" aria-labelledby="t-actividad">
            <h2 id="t-actividad">Actividad reciente</h2>
            <p id="estado-actividad" class="ayuda" aria-live="polite"><?php echo $actividad ? 'Tienes ' . count($actividad) . (count($actividad) === 1 ? ' elemento reciente.' : ' elementos recientes.') : 'Sin actividad reciente.'; ?></p>
            <ul id="lista-actividad" class="lista-simple">
            <?php foreach ($actividad as $a): ?>
              <li><span><?php echo $e($a['texto']); ?></span><time datetime="<?php echo $e($a['fecha']); ?>"><?php echo $e(fecha_corta($a['fecha'])); ?></time></li>
            <?php endforeach; ?>
            </ul>
            <button class="boton secundario-btn mt-3" id="alternar-actividad" type="button" aria-controls="lista-actividad" aria-pressed="false">Marcar actividad como revisada</button>
          </section>
        </div>
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
