<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('doctor');

// Figma W16 · Alta de paciente por el doctor
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Registrar paciente · Vital Dent</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<body>
	<a class="saltar" href="#contenido">Saltar al contenido principal</a>
	<div class="portal"><header class="lateral"><a class="marca" href="panel.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a><p class="rol">Rol: Doctor</p><nav class="menu" aria-label="Navegación del doctor"><ul><li><a href="panel.php">Panel</a></li><li><a href="misCistas.php">Agenda</a></li><li><a href="historialpacientes.php" aria-current="page">Pacientes</a></li><li><a href="consultas.php">Consultas</a></li><li><a href="tratamientos.php">Tratamientos</a></li><li><a href="recetas.php">Recetas</a></li><li><a href="perfil.php">Mi perfil</a></li></ul></nav><form action="../../../router/logout.php" method="post"><?php echo campo_csrf(); ?><button type="submit">Cerrar sesión</button></form></header>
		<div class="principal"><div class="barra-superior"><div><h1>Registrar paciente</h1><p>Pacientes / Nuevo</p></div><div class="usuario"><span class="avatar" aria-hidden="true"><?php echo iniciales_usuario($usuarioActual['nombre']); ?></span><span><strong><?php echo htmlspecialchars($usuarioActual["nombre"], ENT_QUOTES, "UTF-8"); ?></strong><br>Doctor</span></div></div>
			<main id="contenido" class="contenido">
				<form class="formulario-consulta" id="form-paciente" action="../../../router/pacientes.php" method="post"><?php echo campo_csrf(); ?><input type="hidden" name="accion" value="registrar">
					<p class="ayuda">Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
					<div class="rejilla rejilla-2">
						<section class="tarjeta rejilla" aria-labelledby="t-datos">
							<h2 id="t-datos">Datos personales y de contacto</h2>
							<div class="rejilla rejilla-3">
								<div class="campo"><label for="nombre">Nombre(s) <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="nombre" name="nombre" autocomplete="off" required maxlength="60" placeholder="Ej. Ana"></div>
								<div class="campo"><label for="paterno">Apellido paterno <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="paterno" name="apellido_paterno" autocomplete="off" required maxlength="60" placeholder="Ej. Torres"></div>
								<div class="campo"><label for="materno">Apellido materno</label><input class="form-control" id="materno" name="apellido_materno" autocomplete="off" maxlength="60" placeholder="Ej. Ríos"></div>
							</div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="nacimiento">Fecha de nacimiento <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="date" id="nacimiento" name="fecha_nacimiento" max="<?php echo $hoy; ?>" required></div>
								<div class="campo"><label for="sexo">Sexo <span class="obligatorio" aria-hidden="true">*</span></label><select class="form-select" id="sexo" name="sexo" required><option value="">Selecciona una opción</option><option value="F">Mujer</option><option value="M">Hombre</option><option value="otro">Otro / prefiere no decir</option></select></div>
							</div>
							<div class="rejilla rejilla-2">
								<div class="campo"><label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" type="tel" id="telefono" name="telefono" inputmode="numeric" pattern="[0-9 ]{10,14}" required placeholder="771 234 5678" aria-describedby="ayuda-telefono"><p class="ayuda" id="ayuda-telefono">Ingresa 10 dígitos.</p></div>
								<div class="campo"><label for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" placeholder="nombre@correo.com"></div>
							</div>
							<div class="campo"><label for="domicilio">Domicilio</label><input class="form-control" id="domicilio" name="domicilio" placeholder="Calle, número, colonia y municipio"></div>
						</section>
						<div class="rejilla">
							<section class="tarjeta rejilla" aria-labelledby="t-antecedentes">
								<h2 id="t-antecedentes">Antecedentes odontológicos</h2>
								<div class="campo"><label for="motivo">Motivo de la primera visita <span class="obligatorio" aria-hidden="true">*</span></label><input class="form-control" id="motivo" name="motivo" required placeholder="Ej. Valoración general"></div>
								<div class="campo"><label for="ultima">Última visita al dentista</label><select class="form-select" id="ultima" name="ultima_visita"><option value="">Selecciona una opción</option><option>Hace menos de 6 meses</option><option>Hace 6 a 12 meses</option><option>Hace 1 a 2 años</option><option>Hace más de 2 años</option><option>Nunca</option></select></div>
								<div class="campo"><label for="observaciones">Observaciones</label><textarea class="form-control" id="observaciones" name="observaciones" placeholder="Ej. Sensibilidad al frío en piezas inferiores."></textarea></div>
							</section>
							<section class="tarjeta rejilla" aria-labelledby="t-alergias">
								<h2 id="t-alergias">Alergias</h2>
								<ul class="chips" id="lista-alergias" aria-label="Alergias agregadas"></ul>
								<div class="fila-agregar">
									<div class="campo"><label for="nueva-alergia">Alergia</label><input class="form-control" id="nueva-alergia" placeholder="Ej. Penicilina" aria-describedby="error-alergias"></div>
									<button class="boton secundario-btn" type="button" id="agregar-alergia">+ Agregar alergia</button>
								</div>
								<label class="casilla"><input type="checkbox" id="sin-alergias" name="sin_alergias" value="1"> Ninguna conocida</label>
								<p class="ayuda">Si no tiene alergias, marca “Ninguna conocida”.</p>
								<p class="mensaje-error" id="error-alergias" role="alert" hidden>Agrega al menos una alergia o marca “Ninguna conocida”.</p>
							</section>
						</div>
					</div>
					<div class="acciones"><a class="boton secundario-btn" href="historialpacientes.php">Cancelar</a><button class="boton" type="submit">Guardar paciente</button></div>
				</form>
				<script>
				// Alergias como etiquetas que se pueden quitar; exige al menos una o "Ninguna conocida"
				(function () {
					var lista = document.getElementById('lista-alergias'), campo = document.getElementById('nueva-alergia'),
						ninguna = document.getElementById('sin-alergias'), error = document.getElementById('error-alergias');
					function agregar() {
						var valor = campo.value.trim();
						if (!valor) { campo.focus(); return; }
						var existe = Array.prototype.some.call(lista.querySelectorAll('input'), function (i) { return i.value.toLowerCase() === valor.toLowerCase(); });
						if (!existe) {
							var li = document.createElement('li'); li.className = 'chip';
							var texto = document.createElement('span'); texto.textContent = valor;
							var oculto = document.createElement('input'); oculto.type = 'hidden'; oculto.name = 'alergias[]'; oculto.value = valor;
							var quitar = document.createElement('button'); quitar.type = 'button'; quitar.textContent = '×'; quitar.setAttribute('aria-label', 'Quitar ' + valor);
							quitar.addEventListener('click', function () { li.remove(); campo.focus(); });
							li.append(texto, oculto, quitar); lista.appendChild(li);
						}
						ninguna.checked = false; error.hidden = true; campo.value = ''; campo.focus();
					}
					document.getElementById('agregar-alergia').addEventListener('click', agregar);
					campo.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { ev.preventDefault(); agregar(); } });
					ninguna.addEventListener('change', function () {
						if (ninguna.checked) { lista.innerHTML = ''; error.hidden = true; }
					});
					document.getElementById('form-paciente').addEventListener('submit', function (ev) {
						if (!ninguna.checked && !lista.children.length) { ev.preventDefault(); error.hidden = false; campo.focus(); }
					});
				})();
				</script>
			</main><footer class="pie"><p>© 2026 Consultorio Vital Dent · Agenda confidencial</p></footer></div>
	</div>
	<script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
	<script src="../../../Public/js/menu.js"></script>
</body>
</html>
