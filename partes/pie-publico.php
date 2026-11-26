<?php
require_once __DIR__ . '/../../database/ConsultorioModelo.php';
$app = $app ?? '';
$anio = date('Y');
?>
<footer class="publico-pie">
	<div class="envoltura">
		<div class="pie-marca">
			<a class="marca" href="<?= $app ?>index.php"><img src="<?= imagen_consultorio('logo_url', $app . '../') ?>" alt=""><span><?php echo dato_consultorio('nombre'); ?></span></a>
			<p><?php echo dato_consultorio('descripcion'); ?></p>
		</div>
		<nav aria-label="Enlaces del pie de página">
			<h2>Explora</h2>
			<ul>
				<li><a href="<?= $app ?>index.php">Inicio</a></li>
				<li><a href="<?= $app ?>public/tratamientos.php">Tratamientos</a></li>
				<li><a href="<?= $app ?>public/citas.php">Citas</a></li>
				<li><a href="<?= $app ?>public/doctores.php">Doctores</a></li>
				<li><a href="<?= $app ?>private/autenticacion/login.php">Iniciar sesión</a></li>
			</ul>
		</nav>
		<div>
			<h2>Horario</h2>
			<ul>
				<li>Lunes a viernes<br><span class="pie-dato"><?php echo dato_consultorio('horario_lv'); ?></span></li>
				<li>Sábado<br><span class="pie-dato"><?php echo dato_consultorio('horario_sab'); ?></span></li>
				<li>Domingo<br><span class="pie-dato"><?php echo dato_consultorio('horario_dom'); ?></span></li>
			</ul>
		</div>
		<div>
			<h2>Contacto</h2>
			<address>
				<ul>
					<li><a href="<?php echo dato_consultorio('mapa_url'); ?>" target="_blank" rel="noopener"><?php echo dato_consultorio('direccion_pie'); ?><br><?php echo dato_consultorio('ciudad_pie'); ?></a></li>
					<li><a href="<?php echo dato_consultorio('telefono_enlace'); ?>"><?php echo dato_consultorio('telefono_legible'); ?></a></li>
					<?php if (consultorio()['correo']): ?><li><a href="mailto:<?php echo dato_consultorio('correo'); ?>"><?php echo dato_consultorio('correo'); ?></a></li><?php endif; ?>
					<?php if (consultorio()['facebook_url']): ?><li><a href="<?php echo dato_consultorio('facebook_url'); ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif; ?>
					<?php if (consultorio()['instagram_url']): ?><li><a href="<?php echo dato_consultorio('instagram_url'); ?>" target="_blank" rel="noopener">Instagram</a></li><?php endif; ?>
				</ul>
			</address>
		</div>
	</div>
	<div class="envoltura pie-legal">
		<p>© <?= $anio ?> Vital Dent. Todos los derechos reservados. · <a href="<?= $app ?>public/avisoPrivacidad.php">Aviso de privacidad</a></p>
	</div>
</footer>
