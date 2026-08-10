<?php
/**
 * Archivo: plugins-base.php
 * Objetivo: Instalar el pool de plugins base del proyecto (sin activarlos —
 *           la activación queda manual, según lo que necesite cada sitio),
 *           descargándolos directamente desde wordpress.org con las mismas
 *           clases nativas que usa wp-admin (Plugin_Upgrader), sin
 *           commitear los plugins en el repo.
 *
 * Ubicacion: wp-tools/plugins-base.php (continúa el flujo de
 *            wp-quick-setup.php — se pensó para visitarse justo después).
 *
 * IMPORTANTE: herramienta de desarrollo local. Eliminar o restringir el
 * acceso antes de subir el proyecto a un entorno público.
 */

// ----------------------------------------
// Lista de plugins base — editar libremente
// ----------------------------------------

$plugins_base = array(
	'advanced-custom-fields' => 'Advanced Custom Fields',
	'classic-editor'         => 'Classic Editor',
	'wps-hide-login'         => 'WPS Hide Login',
	'wp-migrate-db'          => 'WP Migrate Lite',
);

// Plugins premium / respaldados: no están en wordpress.org, no se pueden
// descargar automáticamente por slug. Se instalan a mano subiendo el .zip
// con licencia desde wp-admin, o completando 'url' con un storage privado
// propio (incluso así, Plugin_Upgrader::install() acepta cualquier URL de
// .zip directa, no solo wordpress.org).
$plugins_premium = array(
	'menu-editor-pro' => array(
		'nombre' => 'Admin Menu Editor Pro',
		'nota'   => 'No está en wordpress.org. Instalar manualmente el .zip con licencia desde wp-admin → Plugins → Añadir → Subir plugin.',
		'url'    => '',
	),
);

// ----------------------------------------
// Helpers booleanos
// ----------------------------------------

function pb_wp_instalado(): bool {
	return file_exists( dirname( __DIR__ ) . '/wp-config.php' );
}

function pb_es_post(): bool {
	return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// ----------------------------------------
// Helpers de retorno
// ----------------------------------------

function get_main_url( $remove = null ) {
	$https    = isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off';
	$protocol = $https ? 'https://' : 'http://';
	$host     = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
	$domain   = $protocol . $host;
	$script   = trim( dirname( $_SERVER['SCRIPT_NAME'] ), '/' );

	if ( ! empty( $script ) ) {
		$domain .= '/' . $script;
	}

	if ( $remove != null ) {
		$domain = str_replace( $remove, '', $domain );
	}

	return rtrim( $domain, '/' );
}

/**
 * Busca el archivo principal de un plugin ya instalado, a partir de su slug
 * (nombre de carpeta), recorriendo get_plugins().
 */
function pb_encontrar_archivo_principal( string $slug ): ?string {
	foreach ( get_plugins() as $archivo => $datos ) {
		if ( strpos( $archivo, $slug . '/' ) === 0 || $archivo === $slug . '.php' ) {
			return $archivo;
		}
	}
	return null;
}

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

/**
 * Instala un plugin por su slug de wordpress.org, sin activarlo — la
 * activación queda a criterio manual, según lo que necesite cada sitio.
 * Devuelve siempre ['ok' => bool, 'mensaje' => string].
 */
function pb_procesar_plugin( string $slug ): array {
	$archivo_existente = pb_encontrar_archivo_principal( $slug );

	if ( $archivo_existente ) {
		return array( 'ok' => true, 'mensaje' => 'Ya estaba instalado.' );
	}

	// Buscar el plugin en el repositorio oficial.
	$info = plugins_api( 'plugin_information', array(
		'slug'   => $slug,
		'fields' => array( 'sections' => false ),
	) );

	if ( is_wp_error( $info ) ) {
		return array( 'ok' => false, 'mensaje' => 'No se encontró en wordpress.org: ' . $info->get_error_message() );
	}

	// Descargar e instalar, en modo silencioso (sin la UI de wp-admin).
	$skin     = new PB_Silent_Skin();
	$upgrader = new Plugin_Upgrader( $skin );
	$resultado = $upgrader->install( $info->download_link );

	if ( is_wp_error( $resultado ) ) {
		return array( 'ok' => false, 'mensaje' => 'Error al instalar: ' . $resultado->get_error_message() );
	}
	if ( $resultado !== true ) {
		return array( 'ok' => false, 'mensaje' => 'No se pudo instalar (revisa permisos de escritura en wp-content/plugins).' );
	}

	return array( 'ok' => true, 'mensaje' => 'Instalado (sin activar).' );
}

/**
 * Instala un plugin premium desde un .zip accesible por URL (storage propio,
 * no wordpress.org), sin activarlo. Mismo mecanismo de Plugin_Upgrader, sin
 * pasar por plugins_api() porque estos plugins no están en el repositorio
 * oficial.
 */
function pb_procesar_plugin_desde_zip( string $slug, string $url ): array {
	if ( $url === '' ) {
		return array( 'ok' => false, 'mensaje' => 'Sin URL configurada — instalar manualmente el .zip desde wp-admin.' );
	}

	$archivo_existente = pb_encontrar_archivo_principal( $slug );
	if ( $archivo_existente ) {
		return array( 'ok' => true, 'mensaje' => 'Ya estaba instalado.' );
	}

	$skin      = new PB_Silent_Skin();
	$upgrader  = new Plugin_Upgrader( $skin );
	$resultado = $upgrader->install( $url );

	if ( is_wp_error( $resultado ) ) {
		return array( 'ok' => false, 'mensaje' => 'Error al instalar: ' . $resultado->get_error_message() );
	}
	if ( $resultado !== true ) {
		return array( 'ok' => false, 'mensaje' => 'No se pudo instalar (revisa la URL o permisos de escritura).' );
	}

	return array( 'ok' => true, 'mensaje' => 'Instalado (sin activar).' );
}

// ----------------------------------------
// Arranque
// ----------------------------------------

if ( ! pb_wp_instalado() ) {
	?>
	<!DOCTYPE html>
	<html lang="es">
	<head><meta charset="UTF-8"><title>Plugins base — WP Base</title></head>
	<body style="font-family: system-ui, sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px;">
		<h1>⚠️ WordPress todavía no está instalado</h1>
		<p>Esta herramienta instala plugins sobre un WordPress ya montado. Primero corre el setup inicial.</p>
		<p><a href="wp-quick-setup.php">← Ir a wp-quick-setup.php</a></p>
	</body>
	</html>
	<?php
	exit;
}

require_once dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';
require_once ABSPATH . 'wp-admin/includes/file.php';

// Evita que Plugin_Upgrader pida credenciales FTP en un entorno local con
// permisos de escritura directos (XAMPP/WAMP/Laragon/Docker en desarrollo).
WP_Filesystem();

/**
 * Skin "silencioso": evita que Plugin_Upgrader imprima su HTML de progreso
 * (pensado para pantallas de wp-admin), ya que nosotros armamos nuestra
 * propia salida más abajo.
 */
class PB_Silent_Skin extends WP_Upgrader_Skin {
	public function header() {}
	public function footer() {}
	public function feedback( $string, ...$args ) {}
	public function error( $errors ) {}
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Plugins base — WP Base</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		body { font-family: system-ui, sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
		h1 { font-size: 1.4rem; }
		label { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #eee; }
		button { margin-top: 24px; padding: 10px 20px; background: #1a1a1a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.95rem; }
		ul.log { list-style: none; padding: 0; margin-top: 20px; }
		ul.log li { padding: 8px 12px; border-radius: 6px; margin-bottom: 6px; }
		ul.log li.ok { background: #e8f5e9; color: #1b5e20; }
		ul.log li.error { background: #fdecea; color: #611a15; }
		.success { background: #e8f5e9; color: #1b5e20; padding: 16px; border-radius: 6px; margin-top: 20px; }
		a.next-step { display: inline-block; margin-top: 8px; }
	</style>
</head>
<body>

<h1>🔌 Plugins base</h1>

<?php if ( ! pb_es_post() ) : ?>
	<div style="background:#fff3cd; color:#664d03; padding:12px 16px; border-radius:6px; margin-bottom:16px;">
		⚠️ Estos plugins quedan <strong>instalados pero no activados</strong> — la activación es manual,
		según lo que necesite cada sitio. Ojo en particular con <strong>WPS Hide Login</strong>: al
		activarlo cambia la URL de <code>/wp-login.php</code>, así que entra de inmediato a
		<em>Ajustes → WPS Hide Login</em> para confirmar o cambiar la nueva URL antes de cerrar sesión —
		si la pierdes, la única forma de recuperar el acceso es renombrar la carpeta del plugin por
		FTP/administrador de archivos.
	</div>
<?php endif; ?>

<?php if ( pb_es_post() ) : ?>

	<p>Instalando...</p>
	<ul class="log">
	<?php
	// Salida progresiva: cada plugin se procesa y se imprime antes de seguir
	// con el siguiente, en vez de esperar a tener todos los resultados.
	// Nota: para que se vea en vivo (no todo junto al final), zlib.output_compression
	// debe estar en Off — lo mismo que ya valida wp-tools/wp-check.php.
	$seleccionados = isset( $_POST['plugins'] ) && is_array( $_POST['plugins'] ) ? $_POST['plugins'] : array();

	foreach ( $seleccionados as $slug ) {
		$slug = sanitize_key( $slug );
		if ( ! isset( $plugins_base[ $slug ] ) ) {
			continue;
		}

		$resultado = pb_procesar_plugin( $slug );
		$clase     = $resultado['ok'] ? 'ok' : 'error';
		$icono     = $resultado['ok'] ? '✅' : '⚠️';

		echo '<li class="' . esc_attr( $clase ) . '">' . $icono . ' <strong>' . esc_html( $plugins_base[ $slug ] ) . '</strong>: ' . esc_html( $resultado['mensaje'] ) . '</li>';

		if ( ob_get_level() > 0 ) {
			@ob_flush();
		}
		flush();
	}

	$premium_seleccionados = isset( $_POST['plugins_premium'] ) && is_array( $_POST['plugins_premium'] ) ? $_POST['plugins_premium'] : array();

	foreach ( $premium_seleccionados as $slug ) {
		$slug = sanitize_key( $slug );
		if ( ! isset( $plugins_premium[ $slug ] ) ) {
			continue;
		}

		$datos_plugin = $plugins_premium[ $slug ];
		$resultado    = pb_procesar_plugin_desde_zip( $slug, $datos_plugin['url'] );
		$clase        = $resultado['ok'] ? 'ok' : 'error';
		$icono        = $resultado['ok'] ? '✅' : '⚠️';

		echo '<li class="' . esc_attr( $clase ) . '">' . $icono . ' <strong>' . esc_html( $datos_plugin['nombre'] ) . '</strong> <small>(premium)</small>: ' . esc_html( $resultado['mensaje'] ) . '</li>';

		if ( ob_get_level() > 0 ) {
			@ob_flush();
		}
		flush();
	}
	?>
	</ul>

	<div class="success">
		<strong>Listo.</strong> Quedaron instalados (sin activar) — revisa el detalle de cada uno arriba.<br>
		<a class="next-step" href="<?= htmlspecialchars( get_main_url( 'wp-tools' ) ) ?>/wp-admin/plugins.php">Activar los que necesites en wp-admin →</a><br>
		<a class="next-step" href="examples.php">Ir a examples.php →</a>
	</div>

<?php else : ?>

	<p>Selecciona qué plugins instalar (quedan instalados, sin activar — se descargan directo desde wordpress.org, no viven en el repo).</p>

	<form method="POST">
		<?php foreach ( $plugins_base as $slug => $nombre ) : ?>
			<label>
				<input type="checkbox" name="plugins[]" value="<?= esc_attr( $slug ) ?>" checked>
				<?= esc_html( $nombre ) ?> <small style="color:#888">(<?= esc_html( $slug ) ?>)</small>
			</label>
		<?php endforeach; ?>

		<button type="submit">Instalar seleccionados</button>
	</form>

	<h2 style="margin-top:32px;">Plugins premium / respaldados</h2>
	<p><small>No están en wordpress.org — solo se pueden auto-instalar si les agregas una URL de descarga privada en el array <code>$plugins_premium</code> del script. Sin URL, hay que subir el .zip a mano desde wp-admin.</small></p>

	<form method="POST">
		<?php foreach ( $plugins_premium as $slug => $datos_plugin ) : ?>
			<label>
				<input type="checkbox" name="plugins_premium[]" value="<?= esc_attr( $slug ) ?>" <?= $datos_plugin['url'] ? 'checked' : 'disabled' ?>>
				<?= esc_html( $datos_plugin['nombre'] ) ?>
				<small style="color:#888">— <?= esc_html( $datos_plugin['nota'] ) ?></small>
			</label>
		<?php endforeach; ?>

		<button type="submit" <?= array_filter( $plugins_premium, fn( $p ) => $p['url'] ) ? '' : 'disabled' ?>>Instalar premium seleccionados</button>
	</form>

<?php endif; ?>

</body>
</html>