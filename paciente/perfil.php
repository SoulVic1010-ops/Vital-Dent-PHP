<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
require_once __DIR__ . '/../../../database/modelos.php';
$perfilBD = ['nombre' => $usuarioActual['nombre'], 'correo' => $usuarioActual['correo'], 'telefono' => '', 'creado_en' => null];
$pacientePerfil = null;
try {
	$bdPerfil = obtener_conexion();
	$perfilBD = perfil_usuario($bdPerfil, (int) $usuarioActual['id']) ?: $perfilBD;
	if ($usuarioActual['rol'] === 'paciente') { $pacientePerfil = paciente_de_usuario($bdPerfil, (int) $usuarioActual['id']); }
} catch (Throwable $falla) {
	error_log('Perfil: ' . $falla->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal del paciente de Vital Dent: mi perfil.">
  <title>Mi perfil · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<!-- RF-17 Actualizar datos de contacto · RF-05 Cambiar contraseña · RF-06 Cerrar sesión · RNF-08, RNF-09, RNF-10 -->
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
          <li><a href="historial.php">Historial médico</a></li>
          <li><a href="perfil.php" aria-current="page">Mi perfil</a></li>
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
          <h1>Mi perfil</h1>
          <p>Actualiza tus datos de contacto y tu contraseña</p>
        </div>
        <div class="usuario">
          <span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span>
          <span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Paciente</span>
        </div>
      </div>

      <main id="contenido" class="contenido perfil-paciente">
        <div class="rejilla rejilla-lateral">
          <!-- RF-17: actualizar datos de contacto -->
          <section class="tarjeta" aria-labelledby="t-contacto">
            <header style="display:flex;gap:14px;align-items:center;margin-bottom:12px">
              <span class="avatar" aria-hidden="true" style="width:56px;height:56px;font-size:1.2rem"><?php echo $e(iniciales($perfilBD['nombre'])); ?></span>
              <div>
                <h2 style="margin:0"><?php echo $e($perfilBD['nombre']); ?></h2>
                <p class="secundario" style="margin:0"><?php echo $e('Paciente' . ($perfilBD['creado_en'] ? ' desde ' . fecha_corta(substr($perfilBD['creado_en'], 0, 10), true) : '') . ($pacientePerfil ? ' · Expediente ' . $pacientePerfil['expediente'] : '')); ?></p>
              </div>
            </header>

            <form action="../../../router/perfil.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="contacto">
              <fieldset class="contacto-datos">
                <legend id="t-contacto">Datos de contacto</legend>
                <p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
                <div class="rejilla rejilla-2">
                  <div class="campo">
                    <label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo $e($perfilBD['telefono']); ?>" pattern="[0-9 ]{10,14}" inputmode="numeric" autocomplete="tel" required aria-describedby="ayuda-tel">
                    <p class="ayuda" id="ayuda-tel">10 dígitos.</p>
                  </div>
                  <div class="campo">
                    <label for="correo">Correo electrónico <span class="obligatorio" aria-hidden="true">*</span></label>
                    <input type="email" id="correo" name="correo" value="<?php echo $e($perfilBD['correo']); ?>" autocomplete="email" required>
                  </div>
                </div>
                <div class="campo">
                  <label for="domicilio">Domicilio</label>
                  <input type="text" id="domicilio" name="domicilio" value="<?php echo $e($pacientePerfil['domicilio'] ?? ''); ?>" autocomplete="street-address">
                </div>
                <div class="campo">
                  <label for="emergencia">Contacto de emergencia</label>
                  <input type="text" id="emergencia" name="emergencia" value="<?php echo $e($pacientePerfil['contacto_emergencia'] ?? ''); ?>">
                </div>
              </fieldset>
              <p class="aviso info">Tu nombre y tus datos clínicos solo los puede modificar el doctor.</p>
              <div class="acciones">
                <button class="boton secundario-btn" type="reset">Cancelar</button>
                <button class="boton" type="submit">Guardar cambios</button>
              </div>
            </form>
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
