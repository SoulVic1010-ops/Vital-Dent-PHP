<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$idTratamiento = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT) ?: 0;
$tratamientos = $avances = [];
$seleccion = $proximaSesion = $proximaCita = null;
$errorBase = false;
try {
	$bd = obtener_conexion();
	$pacienteBD = paciente_de_usuario($bd, (int) $usuarioActual['id']);
	$tratamientos = tratamientos_de($bd, (int) $pacienteBD['id_paciente']);
	foreach ($tratamientos as $t) {
		if ((int) $t['id_tratamiento'] === $idTratamiento) { $seleccion = $t; }
	}
	$seleccion = $seleccion ?? ($tratamientos[0] ?? null);
	$avances = $seleccion ? avances_de($bd, (int) $seleccion['id_tratamiento']) : [];
	// próxima sesión: la sesión programada más cercana (de hoy en adelante) y la próxima cita del paciente
	$hoy = date('Y-m-d');
	foreach ($avances as $a) {
		if ($a['estado'] === 'programada' && $a['fecha'] >= $hoy && ($proximaSesion === null || $a['fecha'] < $proximaSesion['fecha'])) { $proximaSesion = $a; }
	}
	$proximaCita = buscar_citas($bd, ['id_paciente' => (int) $pacienteBD['id_paciente'], 'desde' => $hoy, 'estados' => ['pendiente', 'confirmada']])[0] ?? null;
} catch (Throwable $falla) {
	error_log('Tratamientos paciente: ' . $falla->getMessage());
	$errorBase = true;
}
$mesesLargos = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaLarga = function ($iso) use ($mesesLargos) { [$a, $m, $d] = explode('-', $iso); return (int) $d . ' de ' . $mesesLargos[(int) $m] . ' de ' . $a; };
$sesiones = fn($n) => $n . ($n == 1 ? ' sesión' : ' sesiones');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal del paciente de Vital Dent: tratamientos.">
  <title>Tratamientos · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<!-- RF-35 Consultar tratamientos · RF-36 Consultar avance · RNF-12, RNF-14 -->
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
          <li><a href="citas.php">Mis citas</a></li>
          <li><a href="tratamiento.php" aria-current="page">Tratamientos</a></li>
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
          <h1>Mis tratamientos</h1>
          <p>Avance de tus tratamientos sesión por sesión</p>
        </div>
        <div class="usuario">
          <span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span>
          <span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Paciente</span>
        </div>
      </div>

      <main id="contenido" class="contenido">
        <?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
        <?php if (!$tratamientos && !$errorBase): ?>
        <section class="tarjeta"><h2>Aún no tienes tratamientos</h2><p class="secundario">Cuando tu doctor registre un tratamiento, aquí verás su avance sesión por sesión.</p><p><a class="boton" href="citas.php#agendar">Agendar valoración</a></p></section>
        <?php elseif ($seleccion): ?>
        <div class="rejilla rejilla-izq-340">
          <!-- RF-35: lista de tratamientos del paciente -->
          <section aria-labelledby="t-lista">
            <h2 id="t-lista">Tratamientos</h2>
            <ul class="rejilla" style="list-style:none;margin:0;padding:0">
            <?php foreach ($tratamientos as $t): [$clase, $texto] = ESTADOS_TRATAMIENTO[$t['estado']]; $actual = $t['id_tratamiento'] === $seleccion['id_tratamiento']; $idBarra = 'p-' . (int) $t['id_tratamiento']; ?>
            <li>
              <article class="tarjeta"<?php echo $actual ? ' style="border:2px solid var(--teal)"' : ''; ?>>
                <h3><a href="tratamiento.php?t=<?php echo (int) $t['id_tratamiento']; ?>#detalle"<?php echo $actual ? ' aria-current="true"' : ''; ?>><?php echo $e($t['nombre']); ?></a></h3>
                <p class="secundario">Desde <time datetime="<?php echo $e($t['fecha_inicio']); ?>"><?php echo $e(fecha_corta($t['fecha_inicio'], true)); ?></time> · <span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></p>
                <label class="visualmente-oculto" for="<?php echo $idBarra; ?>">Avance de <?php echo $e($t['nombre']); ?></label>
                <progress id="<?php echo $idBarra; ?>" max="100" value="<?php echo (int) $t['porcentaje']; ?>"><?php echo (int) $t['porcentaje']; ?> %</progress>
                <p class="secundario"><?php echo (int) $t['realizadas']; ?> de <?php echo $e($sesiones((int) $t['sesiones_totales'])); ?> · <strong><?php echo (int) $t['porcentaje']; ?> %</strong></p>
              </article>
            </li>
            <?php endforeach; ?>
            </ul>
          </section>

          <!-- RF-36: detalle y avance sesión por sesión -->
          <article id="detalle" class="tarjeta" aria-labelledby="t-detalle">
            <header>
              <h2 id="t-detalle"><?php echo $e($seleccion['nombre']); ?></h2>
              <p class="secundario"><?php echo $e($seleccion['doctor']); ?> · Inició el <time datetime="<?php echo $e($seleccion['fecha_inicio']); ?>"><?php echo $e($fechaLarga($seleccion['fecha_inicio'])); ?></time></p>
              <label for="av-detalle"><?php echo (int) $seleccion['realizadas']; ?> de <?php echo $e($sesiones((int) $seleccion['sesiones_totales'])); ?> completadas</label>
              <progress id="av-detalle" max="100" value="<?php echo (int) $seleccion['porcentaje']; ?>"><?php echo (int) $seleccion['porcentaje']; ?> %</progress>
            </header>
            <dl class="datos" style="margin-top:14px">
              <dt>Costo total</dt><dd><?php echo $e(dinero($seleccion['costo'])); ?></dd>
            </dl>
            <?php
            $citaMismoDia = $proximaSesion && $proximaCita && $proximaCita['fecha'] === $proximaSesion['fecha'];
            $iconoCalendario = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="3"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/></svg>';
            ?>
            <?php if ($proximaSesion || $proximaCita): ?>
            <div class="proxima-sesion" role="status">
              <span class="proxima-icono"><?php echo $iconoCalendario; ?></span>
              <div class="proxima-texto">
                <p class="proxima-etiqueta"><?php echo $proximaSesion ? 'Tu próxima sesión' : 'Tu próxima cita'; ?></p>
                <?php if ($proximaSesion): ?>
                <p class="proxima-fecha">Sesión <?php echo (int) $proximaSesion['numero_sesion']; ?> · <time datetime="<?php echo $e($proximaSesion['fecha']); ?>"><?php echo $e(fecha_corta($proximaSesion['fecha'], true)); ?></time><?php echo $citaMismoDia ? ' · ' . $e(hora_legible($proximaCita['hora'])) : ''; ?></p>
                <p class="secundario"><?php echo $e($proximaSesion['descripcion']); ?><?php echo $citaMismoDia ? '' : ' · Aún no tienes cita para ese día.'; ?></p>
                <?php else: ?>
                <p class="proxima-fecha"><time datetime="<?php echo $e($proximaCita['fecha']); ?>"><?php echo $e(fecha_corta($proximaCita['fecha'], true)); ?></time> · <?php echo $e(hora_legible($proximaCita['hora'])); ?></p>
                <p class="secundario"><?php echo $e($proximaCita['motivo']); ?> · <?php echo $proximaCita['estado'] === 'confirmada' ? 'Confirmada' : 'Pendiente de confirmar'; ?></p>
                <?php endif; ?>
              </div>
              <?php if ($proximaSesion && !$citaMismoDia): ?><a class="boton boton-chico" href="citas.php#agendar">Agendar cita</a><?php else: ?><a class="boton secundario-btn boton-chico" href="citas.php">Ver mis citas</a><?php endif; ?>
            </div>
            <?php elseif ($seleccion['estado'] !== 'finalizado'): ?>
            <div class="proxima-sesion sin-fecha" role="status">
              <span class="proxima-icono"><?php echo $iconoCalendario; ?></span>
              <div class="proxima-texto">
                <p class="proxima-etiqueta">Tu próxima sesión</p>
                <p class="secundario">Aún no tienes agendada tu siguiente sesión.</p>
              </div>
              <a class="boton boton-chico" href="citas.php#agendar">Agendar cita</a>
            </div>
            <?php endif; ?>


            <section aria-labelledby="t-avances" style="margin-top:18px">
              <h3 id="t-avances">Avances registrados por el doctor</h3>
              <?php if ($avances): ?>
              <ol class="linea-tiempo" reversed>
              <?php foreach ($avances as $a): $prog = $a['estado'] === 'programada'; ?>
                <li<?php echo $prog ? ' class="programada"' : ''; ?>>
                  <div class="cabecera"><span>Sesión <?php echo (int) $a['numero_sesion']; ?><?php echo $prog ? ' (programada)' : ' · ' . (int) $a['porcentaje'] . ' %'; ?></span><time datetime="<?php echo $e($a['fecha']); ?>"><?php echo $e(fecha_corta($a['fecha'], true)); ?></time></div>
                  <p class="secundario"><?php echo $e($a['descripcion']); ?></p>
                  <?php if (!empty($a['indicaciones'])): ?><p class="sesion-nota"><strong>Cuidados:</strong> <?php echo nl2br($e($a['indicaciones'])); ?></p><?php endif; ?>
                  <?php if (!empty($a['id_receta'])): ?><p class="sesion-nota sesion-receta"><strong>Receta <?php echo $e($a['folio']); ?>:</strong> <?php echo $e($a['medicamentos'] ?: $a['receta_indicaciones']); ?><?php echo $a['medicamentos'] && $a['receta_indicaciones'] ? ' · ' . $e($a['receta_indicaciones']) : ''; ?></p><?php endif; ?>
                </li>
              <?php endforeach; ?>
              </ol>
              <?php else: ?><p class="secundario">Tu doctor todavía no registra avances de este tratamiento.</p><?php endif; ?>
            </section>
          </article>
        </div>
        <?php endif; ?>
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
