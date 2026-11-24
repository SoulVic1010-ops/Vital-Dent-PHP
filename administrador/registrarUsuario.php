<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('administrador');

// Figma W25 · el formulario se guarda en router/registrar_usuario.php
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$roles = [
	'doctor'        => ['Doctor', 'Pacientes, consultas, tratamientos, recetas y agenda.'],
	'administrador' => ['Administrador', 'Agenda, pacientes, usuarios y catálogo.'],
	'paciente'      => ['Paciente', 'Sus citas, tratamientos e historial.'],
];
$previo = $_SESSION['form_usuario'] ?? [];
unset($_SESSION['form_usuario']);
$valor = fn($campo, $defecto = '') => (string) ($previo[$campo] ?? $defecto);
$rolElegido = isset($roles[$valor('rol')]) ? $valor('rol') : (isset($roles[$_GET['rol'] ?? '']) ? $_GET['rol'] : 'doctor');
$errores = [
	'datos'    => 'Revisa los campos marcados: faltan datos o no tienen el formato correcto.',
	'correo'   => 'Ya existe una cuenta con ese correo electrónico.',
	'cedula'   => 'Ya hay un doctor registrado con esa cédula profesional.',
	'servicio' => 'No se pudo guardar la cuenta. Revisa que MySQL esté encendido e inténtalo de nuevo.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Registrar usuario · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="dashbordAdmin.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Administrador</p><nav class="menu" aria-label="Navegación de administración"><ul><li><a href="dashbordAdmin.php">Panel</a></li><li><a href="confirmarcitas.php">Agenda</a></li><li><a href="pacientes.php">Pacientes</a></li><li><a href="usuarios.php" aria-current="page">Usuarios</a></li><li><a href="catalogo.php">Catálogo</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Registrar usuario</h1><p>Usuarios / Nuevo</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Administrador</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($error): ?><p class="aviso error" role="alert"><?php echo $e($error); ?></p><?php endif; ?>
				<form class="formulario-consulta" id="form-usuario" action="../../../router/registrar_usuario.php" method="post" autocomplete="off"><?php echo campo_csrf(); ?>
					<div class="rejilla rejilla-2">
						<section class="tarjeta rejilla" aria-labelledby="t-cuenta">
							<h2 id="t-cuenta">Datos de la cuenta</h2>
							<p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
							<div class="campo"><label for="nombre">Nombre completo <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="nombre" name="nombre" value="<?php echo $e($valor('nombre')); ?>" required minlength="2" maxlength="120" placeholder="Ej. Carla Méndez Olvera"></div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="correo">Correo electrónico <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="email" id="correo" name="correo" value="<?php echo $e($valor('correo')); ?>" required placeholder="nombre@vitaldent.mx"></div>
								<div class="campo"><label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="tel" id="telefono" name="telefono" value="<?php echo $e($valor('telefono')); ?>" required pattern="[0-9 +]{7,24}" placeholder="789 896 1020"></div>
							</div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="contrasena">Contraseña temporal <span class="obligatorio" aria-hidden="true">*</span></label>
									<div class="campo-con-boton"><input class="form-control" type="text" id="contrasena" name="contrasena" required minlength="8" pattern="(?=.*[A-ZÁÉÍÓÚÑ])(?=.*[0-9]).{8,}" aria-describedby="ayuda-contrasena" spellcheck="false"><button class="boton secundario-btn boton-chico" type="button" id="generar">Generar</button></div></div>
								<div class="campo"><label for="estado">Estado <span class="obligatorio" aria-hidden="true">*</span></label><select class="form-select" id="estado" name="estado" required><option value="activo"<?php echo $valor('estado', 'activo') === 'activo' ? ' selected' : ''; ?>>Activa</option><option value="inactivo"<?php echo $valor('estado') === 'inactivo' ? ' selected' : ''; ?>>Inactiva</option></select></div>
							</div>
							<p class="ayuda" id="ayuda-contrasena">Mínimo 8 caracteres, una mayúscula y un número. Compártela con la persona; podrá cambiarla en Mi perfil.</p>
						</section>
						<section class="tarjeta rejilla" aria-labelledby="t-rol">
							<h2 id="t-rol">Rol <span class="obligatorio" aria-hidden="true">*</span></h2>
							<fieldset><legend class="ayuda">El rol define a qué módulos tendrá acceso.</legend>
								<div class="opciones-rol">
								<?php foreach ($roles as $clave => [$nombreRol, $modulos]): ?>
									<label class="opcion-rol"><input type="radio" name="rol" value="<?php echo $clave; ?>"<?php echo $rolElegido === $clave ? ' checked' : ''; ?> required><span><strong><?php echo $nombreRol; ?></strong><span><?php echo $modulos; ?></span></span></label>
								<?php endforeach; ?>
								</div>
							</fieldset>
							<div class="rejilla rejilla-2" id="datos-doctor">
								<div class="campo"><label for="especialidad">Especialidad <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="especialidad" name="especialidad" value="<?php echo $e($valor('especialidad')); ?>" maxlength="80" placeholder="Ej. Ortodoncia"></div>
								<div class="campo"><label for="cedula">Cédula profesional <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="cedula" name="cedula" value="<?php echo $e($valor('cedula')); ?>" pattern="[0-9A-Za-z\-]{5,20}" placeholder="Ej. 12345678"></div>
								<p class="ayuda" style="grid-column:1/-1">El doctor aparecerá en la sección “Nuestros doctores” del sitio público.</p>
							</div>
						</section>
					</div>
					<div class="acciones"><a class="boton secundario-btn" href="usuarios.php">Cancelar</a><button class="boton" type="submit">Registrar usuario</button></div>
				</form>
				<script>
				// Muestra los datos del doctor solo para ese rol y genera contraseñas temporales seguras
				(function () {
					var bloque = document.getElementById('datos-doctor');
					var campos = bloque.querySelectorAll('input');
					function actualizar() {
						var esDoctor = document.querySelector('input[name="rol"]:checked').value === 'doctor';
						bloque.hidden = !esDoctor;
						campos.forEach(function (c) { c.required = esDoctor; c.disabled = !esDoctor; });
					}
					document.querySelectorAll('input[name="rol"]').forEach(function (r) { r.addEventListener('change', actualizar); });
					actualizar();
					document.getElementById('generar').addEventListener('click', function () {
						var letras = 'abcdefghijkmnpqrstuvwxyz', mayus = 'ABCDEFGHJKLMNPQRSTUVWXYZ', nums = '23456789', todo = letras + mayus + nums;
						var azar = new Uint32Array(10); crypto.getRandomValues(azar);
						var clave = mayus[azar[0] % mayus.length] + nums[azar[1] % nums.length];
						for (var i = 2; i < 10; i++) { clave += todo[azar[i] % todo.length]; }
						var campo = document.getElementById('contrasena'); campo.value = clave; campo.focus(); campo.select();
					});
				})();
				</script>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Huejutla, Hgo.</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
