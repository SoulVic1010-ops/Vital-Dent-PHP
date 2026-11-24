<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('administrador');
require_once __DIR__ . '/../../../database/modelos.php';
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$editar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$tratamientos = [];
$edicion = null;
$errorBase = false;
try {
	$tratamientos = catalogo_completo(obtener_conexion());
	foreach ($tratamientos as $t) {
		if ((int) $t['id_catalogo'] === $editar) { $edicion = $t; }
	}
} catch (Throwable $falla) {
	error_log('Catálogo admin: ' . $falla->getMessage());
	$errorBase = true;
}
$publicados = count(array_filter($tratamientos, fn($t) => (int) $t['publicado'] === 1));
$v = fn($campo) => $edicion[$campo] ?? '';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Catálogo · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="dashbordAdmin.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Administrador</p><nav class="menu" aria-label="Navegación de administración"><ul><li><a href="dashbordAdmin.php">Panel</a></li><li><a href="confirmarcitas.php">Agenda</a></li><li><a href="pacientes.php">Pacientes</a></li><li><a href="usuarios.php">Usuarios</a></li><li><a href="catalogo.php" aria-current="page">Catálogo</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Catálogo de tratamientos</h1><p>Tratamientos que se muestran en el sitio público</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Administrador</span></div></div>
			<main id="contenido" class="contenido">
				<?php if ($errorBase): ?><p class="aviso error" role="alert">No se pudo leer la base de datos. Revisa que MySQL esté encendido en XAMPP.</p><?php endif; ?>
				<div class="barra-acciones">
					<p class="secundario"><?php echo $publicados; ?> de <?php echo count($tratamientos); ?> tratamientos publicados en el sitio.</p>
					<div class="acciones">
						<a class="boton secundario-btn" href="../../public/tratamientos.php" target="_blank" rel="noopener">Ver en el sitio</a>
						<a class="boton" href="catalogo.php#nuevo-tratamiento">+ Agregar tratamiento</a>
					</div>
				</div>

				<ul class="rejilla catalogo-admin" aria-label="Tratamientos del catálogo">
				<?php foreach ($tratamientos as $t): $publicado = (int) $t['publicado'] === 1; ?>
					<li class="tarjeta catalogo-bracket<?php echo $publicado ? '' : ' oculto'; ?>">
						<div class="catalogo-imagen"><?php if ($t['imagen']): ?><img src="../../../Public/img/<?php echo $e($t['imagen']); ?>" alt="<?php echo $e($t['nombre']); ?>"><?php else: ?><span>Sin imagen</span><?php endif; ?></div>
						<div class="catalogo-contenido">
							<p class="catalogo-categoria"><?php echo $e($t['categoria']); ?></p>
							<h3><?php echo $e($t['nombre']); ?></h3>
							<p class="secundario"><?php echo $e($t['descripcion']); ?></p>
							<p class="catalogo-precio"><?php $precio = precio_catalogo($t['precio_desde'] ?? null); echo $precio ? 'Desde <strong>' . $e($precio) . '</strong> MXN' : 'Precio según valoración'; ?></p>
							<p><span class="estado <?php echo $publicado ? 'ok' : 'neutro'; ?>"><?php echo $publicado ? 'Publicado' : 'Oculto'; ?></span></p>
							<div class="catalogo-acciones">
								<a class="boton secundario-btn boton-chico" href="catalogo.php?editar=<?php echo (int) $t['id_catalogo']; ?>#nuevo-tratamiento" aria-label="Editar <?php echo $e($t['nombre']); ?>">Editar</a>
								<form action="../../../router/catalogo.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="visibilidad"><input type="hidden" name="id" value="<?php echo (int) $t['id_catalogo']; ?>"><button class="boton boton-chico<?php echo $publicado ? ' peligro' : ''; ?>" type="submit" aria-label="<?php echo ($publicado ? 'Ocultar ' : 'Publicar ') . $e($t['nombre']); ?>"><?php echo $publicado ? 'Ocultar' : 'Publicar'; ?></button></form>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
				</ul>
				<?php if (!$tratamientos && !$errorBase): ?><p class="secundario">El catálogo está vacío. Agrega el primer tratamiento.</p><?php endif; ?>

				<section id="nuevo-tratamiento" class="tarjeta" aria-labelledby="t-nuevo">
					<h2 id="t-nuevo"><?php echo $edicion ? 'Editar · ' . $e($edicion['nombre']) : 'Agregar tratamiento'; ?></h2>
					<form action="../../../router/catalogo.php" method="post" enctype="multipart/form-data"><?php echo campo_csrf(); ?>
						<input type="hidden" name="accion" value="guardar">
						<?php if ($edicion): ?><input type="hidden" name="id" value="<?php echo (int) $edicion['id_catalogo']; ?>"><?php endif; ?>
						<p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
						<div class="rejilla rejilla-2">
							<div class="campo"><label for="nombre">Nombre <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="nombre" name="nombre" required minlength="3" maxlength="100" value="<?php echo $e($v('nombre')); ?>" placeholder="Ej. Brackets metálicos"></div>
							<div class="campo"><label for="categoria">Etiqueta <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="categoria" name="categoria" required minlength="3" maxlength="60" value="<?php echo $e($v('categoria')); ?>" placeholder="Ej. Opción clásica"></div>
						</div>
						<div class="campo"><label for="descripcion">Descripción corta <span class="obligatorio" aria-hidden="true">*</span></label><textarea class="form-control" id="descripcion" name="descripcion" minlength="10" maxlength="160" required aria-describedby="ayuda-descripcion"><?php echo $e($v('descripcion')); ?></textarea><p class="ayuda" id="ayuda-descripcion">Entre 10 y 160 caracteres; aparece debajo del nombre en la tarjeta.</p></div>
						<div class="campo"><label for="caracteristicas">Características</label><textarea class="form-control" id="caracteristicas" name="caracteristicas" maxlength="2000" placeholder="Una por línea" aria-describedby="ayuda-caracteristicas"><?php echo $e($v('caracteristicas')); ?></textarea><p class="ayuda" id="ayuda-caracteristicas">Se muestran en la tarjeta con una palomita. Escribe una por línea (se recomiendan 3).</p></div>
						<div class="campo"><label for="precio_desde">Precio de referencia «Desde» (MXN)</label><input class="form-control" type="number" id="precio_desde" name="precio_desde" min="0" max="999999" step="0.01" inputmode="decimal" value="<?php echo $e($v('precio_desde')); ?>" placeholder="Ej. 15000" aria-describedby="ayuda-precio"><p class="ayuda" id="ayuda-precio">Opcional. Se muestra en la tarjeta del sitio como «Desde $…». Si lo dejas vacío, dice «Precio según valoración». El costo final de cada paciente lo pone el doctor al registrar su tratamiento.</p></div>
						<div class="rejilla rejilla-2">
							<div class="campo"><label for="imagen">Imagen</label><input class="form-control" type="file" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp" aria-describedby="ayuda-imagen"><p class="ayuda" id="ayuda-imagen">JPG, PNG o WEBP de hasta 2 MB.<?php echo $edicion && $edicion['imagen'] ? ' Si no eliges otra, se conserva la actual.' : ''; ?></p></div>
							<div class="campo"><label for="visible">Visibilidad</label><select class="form-select" id="visible" name="visible"><option value="1">Publicado en el sitio</option><option value="0"<?php echo $edicion && (int) $edicion['publicado'] === 0 ? ' selected' : ''; ?>>Oculto</option></select></div>
						</div>
						<div class="acciones"><?php if ($edicion): ?><a class="boton secundario-btn" href="catalogo.php">Cancelar edición</a><?php else: ?><button class="boton secundario-btn" type="reset">Limpiar</button><?php endif; ?><button class="boton" type="submit"><?php echo $edicion ? 'Guardar cambios' : 'Guardar tratamiento'; ?></button></div>
					</form>
				</section>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Av. Juárez 45, Huejutla, Hgo.</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
