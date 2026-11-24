<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('administrador');
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

$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$nombre = $usuarioActual['nombre'] ?? 'Administrador';
$iniciales = mb_strtoupper(implode('', array_map(function ($p) { return mb_substr($p, 0, 1); }, array_slice(preg_split('/\s+/', trim($nombre)), 0, 2))));
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Mi perfil · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="dashbordAdmin.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Administrador</p><nav class="menu" aria-label="Navegación de administración"><ul><li><a href="dashbordAdmin.php">Panel</a></li><li><a href="confirmarcitas.php">Agenda</a></li><li><a href="pacientes.php">Pacientes</a></li><li><a href="usuarios.php">Usuarios</a></li><li><a href="catalogo.php">Catálogo</a></li><li><a href="perfil.php" aria-current="page">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Mi perfil</h1><p>Actualiza tus datos de contacto y tu contraseña</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Administrador</span></div></div>
			<main id="contenido" class="contenido">
				<div class="rejilla rejilla-lateral">
					<!-- Datos de contacto (Figma W13, adaptado al rol de administrador) -->
					<section class="tarjeta" aria-labelledby="t-contacto">
						<header class="perfil-cabecera">
							<span class="avatar avatar-grande" aria-hidden="true"><?php echo $e($iniciales); ?></span>
							<div><h2><?php echo $e($nombre); ?></h2><p class="secundario">Administrador · Consultorio Vital Dent</p></div>
						</header>
						<form action="../../../router/perfil.php" method="post"><input type="hidden" name="accion" value="contacto"><?php echo campo_csrf(); ?>
							<fieldset>
								<legend id="t-contacto">Datos de contacto</legend>
								<p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
								<div class="rejilla rejilla-2">
									<div class="campo"><label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="tel" id="telefono" name="telefono" value="<?php echo $e($perfilBD['telefono']); ?>" pattern="[0-9 ]{10,14}" inputmode="numeric" autocomplete="tel" required aria-describedby="ayuda-tel"><p class="ayuda" id="ayuda-tel">10 dígitos, sin espacios.</p></div>
									<div class="campo"><label for="correo">Correo electrónico <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="email" id="correo" name="correo" value="<?php echo $e($perfilBD['correo']); ?>" autocomplete="email" required></div>
								</div>
							</fieldset>
							<p class="aviso info">Tu nombre y tu rol solo los puede cambiar otro administrador.</p>
							<div class="acciones"><button class="boton secundario-btn" type="reset">Cancelar</button><button class="boton" type="submit">Guardar cambios</button></div>
						</form>
					</section>

					<div class="rejilla">
						<section class="tarjeta" aria-labelledby="t-contrasena">
							<h2 id="t-contrasena">Cambiar contraseña</h2>
							<form action="../../../router/perfil.php" method="post"><input type="hidden" name="accion" value="contrasena"><?php echo campo_csrf(); ?>
								<div class="campo"><label for="actual">Contraseña actual <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="password" id="actual" name="actual" autocomplete="current-password" required></div>
								<div class="campo"><label for="nueva">Contraseña nueva <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="password" id="nueva" name="nueva" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9]).{8,}" required aria-describedby="reglas"><p class="ayuda" id="reglas">Mínimo 8 caracteres, una mayúscula y un número. Debe ser distinta a la actual.</p></div>
								<div class="campo"><label for="confirmar">Confirmar contraseña nueva <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="password" id="confirmar" name="confirmar" autocomplete="new-password" required placeholder="Repite la contraseña nueva"></div>
								<button class="boton" type="submit">Actualizar contraseña</button>
							</form>
						</section>
						<section class="tarjeta" aria-labelledby="t-salir">
							<h2 id="t-salir">Cerrar sesión</h2>
							<p class="secundario">Sal de tu cuenta en este navegador.</p>
							<form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button class="boton peligro" type="submit">Cerrar sesión</button></form>
						</section>
					</div>
				</div>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Huejutla, Hgo.</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
