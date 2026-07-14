<?php
/**
 * Archivo: examples.php
 * Objetivo: Catálogo vivo de snippets HTML/CSS/JS reutilizables en este
 *           theme (galería, tabla, modales, formularios, utilidades CSS...).
 *           Los snippets de JS se leen en vivo desde assets/js/helpers.js,
 *           así que nunca quedan desincronizados del código real.
 *
 * Ubicacion: wp-tools/examples.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

// ----------------------------------------
// Helpers de retorno
// ----------------------------------------

/**
 * Extrae un bloque de código de helpers.js delimitado por
 * "// === HELPER: nombre ===" y "// === /HELPER ===", para mostrarlo
 * como snippet real (nunca queda desactualizado respecto al código real).
 */
function ex_get_helper_snippet( string $nombre ): string {
	static $contenido = null;

	if ( $contenido === null ) {
		$ruta      = get_template_directory() . '/assets/js/helpers.js';
		$contenido = file_exists( $ruta ) ? file_get_contents( $ruta ) : '';
	}

	$patron = '/\/\/ === HELPER: ' . preg_quote( $nombre, '/' ) . " ===\n(.*?)\n\/\/ === \/HELPER ===/s";
	if ( preg_match( $patron, $contenido, $coincidencia ) ) {
		return trim( $coincidencia[1] );
	}

	return "// No se encontró el helper '{$nombre}' en assets/js/helpers.js";
}

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

function ex_render_snippet( string $codigo, string $lenguaje = 'js' ): void {
	echo '<figure class="highlight"><pre class="bg-dark text-light p-3 rounded"><code class="language-' . esc_attr( $lenguaje ) . '">' . esc_html( $codigo ) . '</code></pre></figure>';
}

get_header();
?>

<div class="content">
	<div class="container py-5">

		<div class="p-5 mb-5 bg-dark rounded-3">
			<h1>Ejemplos &amp; Snippets</h1>
			<p class="lead mb-0">Fragmentos reutilizables de HTML, CSS y JS ya integrados con las librerías de este theme (Bootstrap, GLightbox, <code>helpers.js</code>).</p>
		</div>

		<!-- ============ GALERÍA (GLightbox) ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Galería <span class="badge bg-danger">GLightbox</span></h2>
			</div>
			<p>Galería de imágenes con lightbox, usando la librería ya vendorizada en <code>resources/</code>.</p>

			<div class="row g-2 mb-3">
				<div class="col-6 col-md-3">
					<a class="glightbox" href="https://picsum.photos/800/600?1" data-gallery="demo-galeria">
						<img src="https://picsum.photos/300/200?1" class="img-fluid rounded" alt="">
					</a>
				</div>
				<div class="col-6 col-md-3">
					<a class="glightbox" href="https://picsum.photos/800/600?2" data-gallery="demo-galeria">
						<img src="https://picsum.photos/300/200?2" class="img-fluid rounded" alt="">
					</a>
				</div>
				<div class="col-6 col-md-3">
					<a class="glightbox" href="https://picsum.photos/800/600?3" data-gallery="demo-galeria">
						<img src="https://picsum.photos/300/200?3" class="img-fluid rounded" alt="">
					</a>
				</div>
				<div class="col-6 col-md-3">
					<a class="glightbox" href="https://picsum.photos/800/600?4" data-gallery="demo-galeria">
						<img src="https://picsum.photos/300/200?4" class="img-fluid rounded" alt="">
					</a>
				</div>
			</div>
			<?php
			ex_render_snippet(
				'<a class="glightbox" href="imagen-grande.jpg" data-gallery="mi-galeria">' . "\n"
				. '  <img src="miniatura.jpg" class="img-fluid rounded">' . "\n"
				. '</a>',
				'html'
			);
			?>
		</section>

		<!-- ============ TABLA (JSdataTable) ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Tabla con orden, búsqueda y paginado <span class="badge bg-primary">Custom JS</span></h2>
			</div>
			<p>Tabla HTML potenciada con <code>JSdataTable</code> — orden por click en columna, buscador y paginado, sin librerías externas.</p>

			<table class="table table-striped table-bordered JSdataTable" data-paging="true" data-searching="true" data-info="true" data-ordering="true">
				<thead>
					<tr><th>Nombre</th><th>Cargo</th><th>Ciudad</th><th>Edad</th></tr>
				</thead>
				<tbody>
					<tr><td>Ana Pérez</td><td>Diseñadora</td><td>Santiago</td><td>29</td></tr>
					<tr><td>Juan Soto</td><td>Desarrollador</td><td>Valparaíso</td><td>34</td></tr>
					<tr><td>Marta Ruiz</td><td>Gerente</td><td>Concepción</td><td>41</td></tr>
					<tr><td>Pedro Díaz</td><td>Soporte</td><td>Santiago</td><td>25</td></tr>
					<tr><td>Laura Gómez</td><td>Marketing</td><td>La Serena</td><td>31</td></tr>
					<tr><td>Diego Rojas</td><td>Ventas</td><td>Antofagasta</td><td>38</td></tr>
				</tbody>
			</table>
			<?php ex_render_snippet( '<table class="JSdataTable" data-paging="true" data-searching="true" data-info="true" data-ordering="true">' . "\n" . '  ...' . "\n" . '</table>', 'html' ); ?>
			<?php ex_render_snippet( ex_get_helper_snippet( 'JSdataTable' ) ); ?>
		</section>

		<!-- ============ MODALES ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Modales <span class="badge bg-primary">Custom JS</span></h2>
			</div>
			<p>Modales generados dinámicamente sobre Bootstrap 5, sin escribir el HTML del modal a mano.</p>

			<div class="d-flex flex-wrap gap-2 mb-3">
				<button type="button" class="btn btn-primary" onclick="JSmodalAlert('Aviso', 'Este es un mensaje de ejemplo', 'small', 'center')">JSmodalAlert</button>
				<button type="button" class="btn btn-primary" onclick="JSmodalContent('Contenido', '.ex-modal-content-demo', 'medium', 'center')">JSmodalContent</button>
			</div>
			<div class="ex-modal-content-demo d-none">
				<p>Este HTML vive oculto en la página y se muestra dentro del modal al hacer click.</p>
			</div>

			<?php ex_render_snippet( ex_get_helper_snippet( 'JSmodalAlert' ) ); ?>
			<?php ex_render_snippet( ex_get_helper_snippet( 'JSmodalContent' ) ); ?>
		</section>

		<!-- ============ VIDEO ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Video en modal <span class="badge bg-primary">Custom JS</span></h2>
			</div>
			<p>Embebe YouTube, Vimeo o Facebook dentro de un modal, detectando la plataforma automáticamente por la URL.</p>

			<button type="button" class="btn btn-primary" onclick="JSvideoLaunch('Video de ejemplo', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', true, true, 'large', 'center')">JSvideoLaunch</button>

			<?php ex_render_snippet( ex_get_helper_snippet( 'JSvideoLaunch' ) ); ?>
		</section>

		<!-- ============ POPUP ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Ventana emergente <span class="badge bg-primary">Custom JS</span></h2>
			</div>
			<p>Abre <code>window.open()</code> configurado íntegramente por atributos <code>data-win-*</code>, sin JS adicional en la página.</p>

			<button type="button" class="btn btn-primary JSwindowPopup" data-win-url="https://wordpress.org" data-win-size="640x480" data-win-align="center,center" data-win-scroll="yes">JSwindowPopup</button>

			<?php ex_render_snippet( ex_get_helper_snippet( 'JSwindowPopup' ) ); ?>
		</section>

		<!-- ============ MAPA ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Mapa (Google Maps + Waze) <span class="badge bg-primary">Custom JS</span></h2>
			</div>
			<p>Modal con enlaces directos a Google Maps y Waze, más iframe embebido opcional.</p>

			<button type="button" class="btn btn-primary JSmapLaunch" data-map-address="Plaza de Armas, Santiago, Chile" data-map-iframe="true">JSmapLaunch</button>

			<?php ex_render_snippet( ex_get_helper_snippet( 'JSmapLaunch' ) ); ?>
		</section>

		<!-- ============ FORMULARIOS ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Formulario con validación <span class="badge bg-success">Bootstrap 5</span></h2>
			</div>
			<p>Validación nativa de Bootstrap 5 (<code>.needs-validation</code>), ya inicializada en <code>app.js</code> — no hace falta JS adicional.</p>

			<form class="needs-validation row g-3" novalidate>
				<div class="col-md-6">
					<label class="form-label">Nombre</label>
					<input type="text" class="form-control" required>
					<div class="invalid-feedback">Este campo es obligatorio.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label">Email</label>
					<input type="email" class="form-control" required>
					<div class="invalid-feedback">Ingresa un email válido.</div>
				</div>
				<div class="col-12">
					<label class="form-label">Mensaje</label>
					<textarea class="form-control" rows="3" required></textarea>
					<div class="invalid-feedback">Escribe un mensaje.</div>
				</div>
				<div class="col-12">
					<button type="submit" class="btn btn-primary">Enviar</button>
				</div>
			</form>

			<?php
			ex_render_snippet(
				'<form class="needs-validation" novalidate>' . "\n"
				. '  <input type="text" class="form-control" required>' . "\n"
				. '  <div class="invalid-feedback">Este campo es obligatorio.</div>' . "\n"
				. '</form>',
				'html'
			);
			?>
		</section>

		<!-- ============ UTILIDADES CSS ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Utilidades CSS <span class="badge bg-success">Bootstrap 5</span></h2>
			</div>
			<p>Ya incluidas en Bootstrap 5 core: <code>float-*</code>, <code>m-*</code>/<code>p-*</code>, <code>d-*</code>, <code>position-*</code>, <code>text-*</code>. No requieren CSS adicional del theme.</p>

			<div class="d-flex gap-2 flex-wrap mb-3 align-items-start">
				<div class="p-3 bg-warning">p-3</div>
				<div class="p-3 bg-info">p-3</div>
				<div class="position-relative bg-light p-3 border" style="width:200px">
					position-relative
					<span class="position-absolute top-0 end-0 badge bg-danger">absolute</span>
				</div>
			</div>
			<?php
			ex_render_snippet(
				'<div class="position-relative">' . "\n"
				. '  position-relative' . "\n"
				. '  <span class="position-absolute top-0 end-0 badge bg-danger">absolute</span>' . "\n"
				. '</div>',
				'html'
			);
			?>
		</section>

		<!-- ============ CARRUSEL ============ -->
		<section class="mb-5">
			<div class="border-bottom pb-2 mb-3">
				<h2>Carrusel <span class="badge bg-success">Bootstrap 5</span></h2>
			</div>
			<p>Carrusel nativo de Bootstrap 5 con transición <code>carousel-fade</code>.</p>

			<div id="exampleCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
				<div class="carousel-inner rounded">
					<div class="carousel-item active"><img src="https://picsum.photos/1140/400?10" class="d-block w-100" alt=""></div>
					<div class="carousel-item"><img src="https://picsum.photos/1140/400?11" class="d-block w-100" alt=""></div>
					<div class="carousel-item"><img src="https://picsum.photos/1140/400?12" class="d-block w-100" alt=""></div>
				</div>
				<button class="carousel-control-prev" type="button" data-bs-target="#exampleCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#exampleCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
				</button>
			</div>
			<?php ex_render_snippet( '<div class="carousel slide carousel-fade" data-bs-ride="carousel">' . "\n" . '  ...' . "\n" . '</div>', 'html' ); ?>
		</section>

	</div>
</div>

<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/helpers.js' ); ?>"></script>
<script>
	// Inicializa GLightbox solo en esta página (si el theme no lo hace ya de forma global en app.js).
	document.addEventListener( 'DOMContentLoaded', function () {
		if ( typeof GLightbox !== 'undefined' ) {
			GLightbox( { selector: '.glightbox' } );
		}
	} );
</script>

<?php get_footer(); ?>
