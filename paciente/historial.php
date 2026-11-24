<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$tipos = ['cita' => ['neutro', 'Cita', 'Citas'], 'consulta' => ['ok', 'Consulta', 'Consultas'], 'receta' => ['pendiente', 'Receta', 'Recetas'], 'tratamiento' => ['neutro', 'Tratamiento', 'Tratamientos'], 'avance' => ['ok', 'Avance', 'Avances']];
$tipo = isset($tipos[$_GET['tipo'] ?? '']) ? $_GET['tipo'] : '';
$pacienteBD = null;
$alergias = $registros = [];
$antecedentes = ['odontologico' => [], 'habito' => []];
$errorBase = false;
try {
	$bd = obtener_conexion();
	$pacienteBD = paciente_de_usuario($bd, (int) $usuarioActual['id']);
	$id = (int) $pacienteBD['id_paciente'];
	$alergias = alergias_de($bd, $id);
	$antecedentes = array_merge($antecedentes, antecedentes_de($bd, $id));
	$registros = historial_clinico($bd, $id);
} catch (Throwable $falla) {
	error_log('Historial paciente: ' . $falla->getMessage());
	$errorBase = true;
}
$visibles = array_values(array_filter($registros, fn($r) => $tipo === '' || $r['tipo'] === $tipo));
$o = fn($v) => ($v === null || trim((string) $v) === '') ? 'Ninguno registrado' : $v;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal del paciente de Vital Dent: historial médico.">
  <title>Historial médico · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<!-- RF-42 Consultar historial médico (solo lectura) · RNF-12, RNF-14 -->
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
          <li><a href="tratamiento.php">Tratamientos</a></li>
          <li><a href="historial.php" aria-current="page">Historial médico</a></li>
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
          <h1>Historial médico</h1>
          <p>Tus consultas, recetas y tratamientos</p>
        </div>
        <div class="usuario">
          <span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span>
          <span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Paciente</span>
        </div>
      </div>

      <main id="contenido" class="contenido">
        <?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
        <p class="aviso alerta" role="note">Esta información es de solo lectura. Si encuentras un dato incorrecto, coméntalo con tu doctor en tu próxima cita.</p>

        <div class="rejilla rejilla-izq-280">
          <!-- Datos clínicos generales (solo lectura) -->
          <aside class="tarjeta" aria-labelledby="t-datos">
            <h2 id="t-datos">Datos clínicos</h2>
            <dl class="datos" style="grid-template-columns:1fr">
              <dt>Alergias</dt>
              <dd><?php if ($alergias): foreach ($alergias as $al): ?><span class="estado error"><?php echo $e($al); ?></span> <?php endforeach; else: ?>Ninguna registrada<?php endif; ?></dd>
              <dt>Antecedentes</dt>
              <dd><?php echo $e($antecedentes['odontologico'] ? implode(', ', $antecedentes['odontologico']) : 'Ninguno registrado'); ?></dd>
              <dt>Hábitos</dt>
              <dd><?php echo $e($antecedentes['habito'] ? implode(', ', $antecedentes['habito']) : 'Ninguno registrado'); ?></dd>
              <dt>Enfermedades</dt>
              <dd><?php echo $e($o($pacienteBD['enfermedades'] ?? null)); ?></dd>
              <dt>Tipo de sangre</dt>
              <dd><?php echo $e($pacienteBD['tipo_sangre'] ?? '' ?: 'Sin registrar'); ?></dd>
            </dl>
          </aside>

          <!-- RF-42: historial cronológico -->
          <section aria-labelledby="t-registros">
            <h2 id="t-registros">Registros</h2>
            <form class="opciones" role="search" action="historial.php" method="get" style="display:flex;margin-bottom:12px">
              <label class="visualmente-oculto" for="filtro">Filtrar por tipo</label>
              <select id="filtro" name="tipo" style="width:auto">
                <option value="">Todos los registros</option>
                <?php foreach ($tipos as $clave => [, , $plural]): ?><option value="<?php echo $clave; ?>"<?php echo $tipo === $clave ? ' selected' : ''; ?>><?php echo $plural; ?></option><?php endforeach; ?>
              </select>
              <button class="boton secundario-btn" type="submit">Filtrar</button>
              <button class="boton secundario-btn" type="button" onclick="window.print()">Imprimir</button>
            </form>
            <div class="tabla-contenedor">
              <table>
                <caption class="visualmente-oculto">Tu historial médico, del más reciente al más antiguo</caption>
                <thead>
                  <tr><th scope="col">Fecha</th><th scope="col">Tipo</th><th scope="col">Detalle</th><th scope="col">Doctor</th></tr>
                </thead>
                <tbody>
                <?php foreach ($visibles as $r): [$clase, $texto] = $tipos[$r['tipo']]; ?>
                  <tr><td class="sin-corte"><time datetime="<?php echo $e($r['fecha']); ?>"><?php echo $e(fecha_corta($r['fecha'], true)); ?></time></td><td><span class="estado <?php echo $clase; ?>"><?php echo $texto; ?></span></td><td><?php echo $e($r['detalle']); ?></td><td class="sin-corte"><?php echo $e($r['doctor']); ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$visibles && !$errorBase): ?><tr><td colspan="4"><?php echo $registros ? 'No hay registros de ese tipo.' : 'Todavía no tienes registros. Aparecerán después de tu primera consulta.'; ?></td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
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
