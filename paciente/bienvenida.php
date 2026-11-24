<?php
require_once __DIR__ . '/../../../router/auth.php';
$usuarioActual = exigir_rol('paciente');
require_once __DIR__ . '/../../../database/modelos.php';
// Si ya lo llenó, no se vuelve a mostrar: pasa directo a su panel.
try {
	if (paciente_de_usuario(obtener_conexion(), (int) $usuarioActual['id'])['cuestionario_completo']) {
		header('Location: dashbord.php');
		exit;
	}
} catch (Throwable $falla) {
	error_log('Cuestionario inicial: ' . $falla->getMessage());
}
$e = function ($texto) { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Cuestionario inicial del paciente de Vital Dent.">
  <title>Cuestionario inicial · Vital Dent</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../Public/css/estilos.css">
</head>
<!-- RF-12 Cuestionario inicial en el primer acceso · RF-13 Validar datos del paciente · RNF-08, RNF-09, RNF-12 · Figma W07 (3 pasos) -->
<body class="simple">
  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  <header>
    <a class="marca" href="dashbord.php"><img src="../../../Public/img/logo-vital-icono.png" alt=""><span>Vital Dent</span></a>
    <p class="secundario" style="margin:0" aria-live="polite">Paso <span data-numero-paso>1</span> de 3</p>
  </header>

  <main id="contenido">
    <h1>Cuestionario inicial</h1>
    <p class="secundario">Hola, <?php echo $e($usuarioActual['nombre']); ?>. El doctor revisará esta información antes de tu primera consulta. Los campos con <span class="obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
    <p class="secundario"><a href="dashbord.php">Llenarlo después</a> · te lo recordaremos cada vez que inicies sesión.</p>

    <nav aria-label="Progreso del cuestionario">
      <ol class="pasos">
        <li data-indicador="1" aria-current="step">Datos personales</li>
        <li data-indicador="2">Datos clínicos</li>
        <li data-indicador="3">Contacto de emergencia</li>
      </ol>
    </nav>

    <form class="tarjeta cuestionario" id="cuestionario" action="../../../router/cuestionario.php" method="post" style="padding:24px"><?php echo campo_csrf(); ?>

      <!-- Paso 1 · Datos personales -->
      <fieldset data-paso="1">
        <legend>Paso 1 de 3 · Datos personales</legend>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="nacimiento">Fecha de nacimiento <span class="obligatorio" aria-hidden="true">*</span></label>
            <input class="form-control" type="date" id="nacimiento" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="campo">
            <label for="sexo">Sexo <span class="obligatorio" aria-hidden="true">*</span></label>
            <select class="form-select" id="sexo" name="sexo" required>
              <option value="">Selecciona una opción</option>
              <option value="F">Mujer</option>
              <option value="M">Hombre</option>
              <option value="otro">Otro / prefiero no decir</option>
            </select>
          </div>
        </div>
        <div class="campo">
          <label for="domicilio">Calle y número <span class="obligatorio" aria-hidden="true">*</span></label>
          <input class="form-control" id="domicilio" name="domicilio" autocomplete="street-address" required maxlength="200" placeholder="Ej. Av. Juárez 123, interior 2">
        </div>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="codigo-postal">Código postal</label>
            <input class="form-control" type="text" id="codigo-postal" name="codigo_postal" inputmode="numeric" autocomplete="postal-code" pattern="[0-9]{5}" maxlength="5" aria-describedby="ayuda-codigo-postal">
            <p class="ayuda" id="ayuda-codigo-postal">Al ingresar 5 dígitos, buscaremos la colonia, municipio y estado. También puedes dejarlo vacío y escribir tu domicilio manualmente.</p>
          </div>
          <div class="campo">
            <label for="colonia">Colonia</label>
            <select class="form-select" id="colonia" name="colonia" autocomplete="address-level3">
              <option value="">Ingresa un código postal</option>
            </select>
          </div>
          <div class="campo">
            <label for="municipio">Municipio o alcaldía</label>
            <input class="form-control" type="text" id="municipio" name="municipio" autocomplete="address-level2" readonly>
          </div>
          <div class="campo">
            <label for="estado">Estado</label>
            <input class="form-control" type="text" id="estado" name="estado" autocomplete="address-level1" readonly>
          </div>
        </div>
        <p id="estado-sepomex" class="ayuda" role="status" aria-live="polite"></p>
      </fieldset>

      <!-- Paso 2 · Datos clínicos -->
      <fieldset data-paso="2">
        <legend>Paso 2 de 3 · Datos clínicos</legend>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="motivo">Motivo de la consulta <span class="obligatorio" aria-hidden="true">*</span></label>
            <input class="form-control" id="motivo" name="motivo" required placeholder="Ej. Quiero valorar la colocación de brackets">
          </div>
          <div class="campo">
            <label for="ultima">Última visita al dentista <span class="obligatorio" aria-hidden="true">*</span></label>
            <select class="form-select" id="ultima" name="ultima_visita" required>
              <option value="">Selecciona una opción</option>
              <option>Hace menos de 6 meses</option>
              <option>Hace 6 a 12 meses</option>
              <option>Hace más de 1 año</option>
              <option>Nunca</option>
            </select>
          </div>
        </div>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="sangre">Tipo de sangre <span class="obligatorio" aria-hidden="true">*</span></label>
            <select class="form-select" id="sangre" name="tipo_sangre" required>
              <option value="">Selecciona una opción</option>
              <option>O+</option><option>O-</option><option>A+</option><option>A-</option>
              <option>B+</option><option>B-</option><option>AB+</option><option>AB-</option>
              <option value="desconocido">No lo sé</option>
            </select>
          </div>
          <div class="campo">
            <label for="alergias">Alergias <span class="obligatorio" aria-hidden="true">*</span></label>
            <input class="form-control" id="alergias" name="alergias" placeholder="Ej. Penicilina, látex" aria-describedby="error-alergias">
            <label class="casilla"><input type="checkbox" id="sin-alergias" name="sin_alergias" value="1"> No tengo alergias conocidas</label>
            <p class="mensaje-error" id="error-alergias" role="alert" hidden>Escribe tus alergias o marca “No tengo alergias conocidas”.</p>
          </div>
        </div>

        <fieldset data-grupo="antecedentes">
          <legend class="etiqueta">Antecedentes odontológicos <span class="obligatorio" aria-hidden="true">*</span></legend>
          <div class="opciones">
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="ortodoncia"> Ortodoncia previa</label>
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="extracciones"> Extracciones</label>
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="endodoncia"> Endodoncia</label>
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="resinas"> Resinas</label>
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="bruxismo"> Bruxismo</label>
            <label class="opcion"><input type="checkbox" name="antecedentes[]" value="ninguno" data-ninguno> Ninguno</label>
          </div>
          <p class="mensaje-error" role="alert" hidden>Elige al menos una opción.</p>
        </fieldset>

        <fieldset data-grupo="sangrado">
          <legend class="etiqueta">¿Te sangran las encías al cepillarte? <span class="obligatorio" aria-hidden="true">*</span></legend>
          <div class="opciones">
            <label class="opcion"><input type="radio" name="sangrado" value="si"> Sí</label>
            <label class="opcion"><input type="radio" name="sangrado" value="a-veces"> A veces</label>
            <label class="opcion"><input type="radio" name="sangrado" value="no"> No</label>
          </div>
          <p class="mensaje-error" role="alert" hidden>Selecciona una opción para continuar.</p>
        </fieldset>

        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="enfermedades">Enfermedades o condiciones médicas</label>
            <textarea class="form-control" id="enfermedades" name="enfermedades" placeholder="Ej. diabetes, hipertensión"></textarea>
          </div>
          <div class="campo">
            <label for="medicamentos">Medicamentos actuales</label>
            <textarea class="form-control" id="medicamentos" name="medicamentos" placeholder="Nombre y frecuencia"></textarea>
          </div>
        </div>
      </fieldset>

      <!-- Paso 3 · Contacto de emergencia -->
      <fieldset data-paso="3">
        <legend>Paso 3 de 3 · Contacto de emergencia</legend>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="contacto">Nombre completo <span class="obligatorio" aria-hidden="true">*</span></label>
            <input class="form-control" id="contacto" name="contacto_nombre" required placeholder="Ej. Juan López Cruz">
          </div>
          <div class="campo">
            <label for="parentesco">Parentesco <span class="obligatorio" aria-hidden="true">*</span></label>
            <select class="form-select" id="parentesco" name="parentesco" required>
              <option value="">Selecciona una opción</option>
              <option>Madre</option><option>Padre</option><option>Pareja</option>
              <option>Hijo o hija</option><option>Hermano o hermana</option><option>Otro</option>
            </select>
          </div>
        </div>
        <div class="rejilla rejilla-2">
          <div class="campo">
            <label for="tel-contacto">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label>
            <input class="form-control" type="tel" id="tel-contacto" name="contacto_telefono" inputmode="numeric" pattern="[0-9 ]{10,14}" required placeholder="771 998 0011" aria-describedby="ayuda-tel">
            <p class="ayuda" id="ayuda-tel">Ingresa 10 dígitos.</p>
          </div>
          <div class="campo">
            <label for="tel-alterno">Teléfono alternativo</label>
            <input class="form-control" type="tel" id="tel-alterno" name="contacto_telefono_alterno" inputmode="numeric" pattern="[0-9 ]{10,14}" placeholder="Opcional">
          </div>
        </div>
        <p class="aviso info">Solo lo usaremos si surge una emergencia durante tu atención.</p>
        <label class="casilla"><input type="checkbox" name="acepta_aviso" required> He leído y acepto el <a href="../../public/avisoPrivacidad.php" target="_blank" rel="noopener">aviso de privacidad</a>, incluido el uso de mis datos de salud para mi atención. <span class="obligatorio" aria-hidden="true">*</span></label>
      </fieldset>

      <div class="acciones" style="justify-content:space-between">
        <button class="boton secundario-btn" type="button" data-anterior hidden>Anterior</button>
        <span></span>
        <button class="boton" type="button" data-siguiente hidden>Guardar y continuar</button>
        <button class="boton" type="submit" data-finalizar>Finalizar</button>
      </div>
    </form>
  </main>
  <script>
  // Cuestionario en 3 pasos: valida cada paso antes de avanzar. Sin JavaScript se muestran los 3 pasos juntos.
  (function () {
    var form = document.getElementById('cuestionario');
    var pasos = form.querySelectorAll('[data-paso]'), total = pasos.length, actual = 1;
    var anterior = form.querySelector('[data-anterior]'), siguiente = form.querySelector('[data-siguiente]'), finalizar = form.querySelector('[data-finalizar]');
    var sinAlergias = document.getElementById('sin-alergias'), alergias = document.getElementById('alergias');

    function grupoValido(grupo) {
      var ok = grupo.querySelector('input:checked') !== null;
      grupo.querySelector('.mensaje-error').hidden = ok;
      return ok;
    }
    function pasoValido(n) {
      var paso = pasos[n - 1], valido = true, primero = null;
      paso.querySelectorAll('input, select, textarea').forEach(function (c) {
        if (!c.checkValidity()) { valido = false; primero = primero || c; }
      });
      paso.querySelectorAll('[data-grupo]').forEach(function (g) {
        if (!grupoValido(g)) { valido = false; primero = primero || g.querySelector('input'); }
      });
      if (n === 2) {
        var okAlergias = sinAlergias.checked || alergias.value.trim() !== '';
        document.getElementById('error-alergias').hidden = okAlergias;
        alergias.setAttribute('aria-invalid', okAlergias ? 'false' : 'true');
        if (!okAlergias) { valido = false; primero = primero || alergias; }
      }
      if (!valido && primero) { if (primero.reportValidity && !primero.checkValidity()) { primero.reportValidity(); } primero.focus(); }
      return valido;
    }
    function mostrar(n) {
      actual = n;
      pasos.forEach(function (p, i) { p.hidden = i !== n - 1; });
      document.querySelectorAll('[data-indicador]').forEach(function (li) {
        var i = +li.dataset.indicador;
        li.classList.toggle('hecho', i < n);
        if (i === n) { li.setAttribute('aria-current', 'step'); } else { li.removeAttribute('aria-current'); }
      });
      document.querySelector('[data-numero-paso]').textContent = n;
      anterior.hidden = n === 1;
      siguiente.hidden = n === total;
      finalizar.hidden = n !== total;
      pasos[n - 1].querySelector('legend').scrollIntoView({ block: 'nearest' });
    }
    siguiente.addEventListener('click', function () { if (pasoValido(actual)) { mostrar(actual + 1); pasos[actual - 1].querySelector('input, select, textarea').focus(); } });
    anterior.addEventListener('click', function () { mostrar(actual - 1); });
    form.addEventListener('submit', function (ev) {
      for (var n = 1; n <= total; n++) { if (!pasoValido(n)) { ev.preventDefault(); mostrar(n); pasoValido(n); return; } }
    });
    // "Ninguno" excluye a las demás opciones y viceversa
    form.querySelectorAll('[data-grupo="antecedentes"] input').forEach(function (c) {
      c.addEventListener('change', function () {
        if (!c.checked) { return; }
        form.querySelectorAll('[data-grupo="antecedentes"] input').forEach(function (o) {
          if (o !== c && (c.hasAttribute('data-ninguno') || o.hasAttribute('data-ninguno'))) { o.checked = false; }
        });
        grupoValido(c.closest('[data-grupo]'));
      });
    });
    form.querySelectorAll('[data-grupo="sangrado"] input').forEach(function (r) { r.addEventListener('change', function () { grupoValido(r.closest('[data-grupo]')); }); });
    sinAlergias.addEventListener('change', function () { alergias.disabled = sinAlergias.checked; if (sinAlergias.checked) { alergias.value = ''; document.getElementById('error-alergias').hidden = true; } });
    mostrar(1);
  })();
  </script>
  <script src="../../../Public/js/sepomex.js" defer></script>
  <script src="../../../Public/js/sesion.js" defer></script>
	<script src="../../../Public/js/avisos.js" defer></script>
	<script src="../../../Public/js/tablas.js" defer></script>
</body>
</html>
