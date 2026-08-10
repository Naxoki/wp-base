<?php
/**
 * Archivo: plugins-base.php
 * Objetivo: Instalar el pool de plugins base del proyecto desde distintas
 *           fuentes (wordpress.org o GitHub), decidiendo en el mismo paso
 *           cuáles se activan y cuáles quedan solo instalados. Sin
 *           commitear ningún plugin en el repo.
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
//
// 'fuente' => 'wordpress_org' usa el slug del repositorio oficial.
// 'fuente' => 'github' usa la URL del repositorio (con o sin .git);
//             se resuelve la rama por defecto y se descarga el zip.
// 'activar' => true/false define si la casilla de "Activar" viene
//              premarcada por defecto (siempre se puede cambiar al vuelo).

$plugins_base = array(
	'classic-editor' => array(
		'nombre'  => 'Classic Editor',
		'fuente'  => 'wordpress_org',
		'activar' => true,
	),
	'wps-hide-login' => array(
		'nombre'  => 'WPS Hide Login',
		'fuente'  => 'wordpress_org',
		'activar' => false,
	),
	'wp-migrate-db' => array(
		'nombre'  => 'WP Migrate Lite',
		'fuente'  => 'wordpress_org',
		'activar' => false,
	),
	'advanced-custom-fields-pro' => array(
		'nombre'  => 'Advanced Custom Fields PRO',
		'fuente'  => 'github',
		'url'     => 'https://github.com/pronamic/advanced-custom-fields-pro.git',
		'activar' => false,
	),
	'admin-menu-editor-pro' => array(
		'nombre'  => 'Admin Menu Editor Pro',
		'fuente'  => 'github',
		'url'     => 'https://github.com/Naxoki/admin-menu-editor-pro.git',
		'activar' => false,
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

/**
 * Convierte una URL de repo de GitHub (con o sin .git) en la URL del zip de
 * su rama por defecto, resolviendo la rama vía la API de GitHub.
 */
function pb_github_zip_url( string $repo_url ): ?string {
	if ( ! preg_match( '#github\.com/([^/]+)/([^/.]+)#', $repo_url, $coincidencia ) ) {
		return null;
	}
	$owner = $coincidencia[1];
	$repo  = $coincidencia[2];

	$respuesta = wp_remote_get( "https://api.github.com/repos/{$owner}/{$repo}", array(
		'headers' => array( 'User-Agent' => 'wp-tools-plugins-base' ),
		'timeout' => 15,
	) );

	if ( is_wp_error( $respuesta ) ) {
		return null;
	}

	$datos = json_decode( wp_remote_retrieve_body( $respuesta ), true );
	$rama  = $datos['default_branch'] ?? 'main';

	return "https://github.com/{$owner}/{$repo}/archive/refs/heads/{$rama}.zip";
}

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

/**
 * Instala un plugin desde wordpress.org por su slug. No activa.
 * Devuelve ['ok' => bool, 'mensaje' => string, 'archivo' => string|null].
 */
function pb_instalar_desde_wordpress_org( string $slug ): array {
	$info = plugins_api( 'plugin_information', array(
		'slug'   => $slug,
		'fields' => array( 'sections' => false ),
	) );

	if ( is_wp_error( $info ) ) {
		return array( 'ok' => false, 'mensaje' => 'No se encontró en wordpress.org: ' . $info->get_error_message(), 'archivo' => null );
	}

	$skin      = new PB_Silent_Skin();
	$upgrader  = new Plugin_Upgrader( $skin );
	$resultado = $upgrader->install( $info->download_link );

	if ( is_wp_error( $resultado ) ) {
		return array( 'ok' => false, 'mensaje' => 'Error al instalar: ' . $resultado->get_error_message(), 'archivo' => null );
	}
	if ( $resultado !== true ) {
		return array( 'ok' => false, 'mensaje' => 'No se pudo instalar (revisa permisos de escritura en wp-content/plugins).', 'archivo' => null );
	}

	return array( 'ok' => true, 'mensaje' => 'Instalado.', 'archivo' => $upgrader->plugin_info() );
}

/**
 * Instala un plugin desde un repositorio de GitHub. No activa.
 * Renombra la carpeta resultante ("repo-rama") al slug esperado.
 * Devuelve ['ok' => bool, 'mensaje' => string, 'archivo' => string|null].
 */
function pb_instalar_desde_github( string $slug, string $repo_url ): array {
	$zip_url = pb_github_zip_url( $repo_url );
	if ( ! $zip_url ) {
		return array( 'ok' => false, 'mensaje' => 'No se pudo resolver la URL de descarga de GitHub.', 'archivo' => null );
	}

	$skin      = new PB_Silent_Skin();
	$upgrader  = new Plugin_Upgrader( $skin );
	$resultado = $upgrader->install( $zip_url );

	if ( is_wp_error( $resultado ) ) {
		return array( 'ok' => false, 'mensaje' => 'Error al instalar desde GitHub: ' . $resultado->get_error_message(), 'archivo' => null );
	}
	if ( $resultado !== true ) {
		return array( 'ok' => false, 'mensaje' => 'No se pudo instalar desde GitHub (revisa permisos de escritura).', 'archivo' => null );
	}

	$archivo_instalado = $upgrader->plugin_info();
	if ( ! $archivo_instalado ) {
		return array( 'ok' => true, 'mensaje' => 'Se instaló, pero no se identificó el archivo principal.', 'archivo' => null );
	}

	// GitHub arma la carpeta como "repo-rama" (ej: admin-menu-editor-pro-main).
	// Se renombra al slug esperado para que quede prolija y sea reconocible después.
	$carpeta_actual = dirname( $archivo_instalado );
	if ( $carpeta_actual !== $slug && $carpeta_actual !== '.' ) {
		$origen  = WP_PLUGIN_DIR . '/' . $carpeta_actual;
		$destino = WP_PLUGIN_DIR . '/' . $slug;
		if ( ! file_exists( $destino ) && @rename( $origen, $destino ) ) {
			$archivo_instalado = str_replace( $carpeta_actual . '/', $slug . '/', $archivo_instalado );
		}
	}

	return array( 'ok' => true, 'mensaje' => 'Instalado desde GitHub.', 'archivo' => $archivo_instalado );
}

/**
 * Instala un plugin desde la fuente que corresponda, o detecta que ya
 * estaba instalado. No activa — eso lo decide el llamador.
 */
function pb_instalar_plugin( string $slug, array $datos_plugin ): array {
	$archivo_existente = pb_encontrar_archivo_principal( $slug );
	if ( $archivo_existente ) {
		return array( 'ok' => true, 'mensaje' => 'Ya estaba instalado.', 'archivo' => $archivo_existente );
	}

	if ( $datos_plugin['fuente'] === 'github' ) {
		return pb_instalar_desde_github( $slug, $datos_plugin['url'] );
	}

	return pb_instalar_desde_wordpress_org( $slug );
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
		body { font-family: system-ui, sans-serif; max-width: 680px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
		h1 { font-size: 1.4rem; }
		.fila { display: flex; align-items: center; gap: 20px; padding: 10px 0; border-bottom: 1px solid #eee; }
		.fila .nombre { flex: 1; }
		.fila .badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; background: #eee; color: #555; margin-left: 6px; }
		.fila label { display: flex; align-items: center; gap: 4px; font-size: 0.85rem; white-space: nowrap; }
		button { margin-top: 24px; padding: 10px 20px; background: #1a1a1a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.95rem; }
		ul.log { list-style: none; padding: 0; margin-top: 20px; }
		ul.log li { padding: 8px 12px; border-radius: 6px; margin-bottom: 6px; }
		ul.log li.ok { background: #e8f5e9; color: #1b5e20; }
		ul.log li.error { background: #fdecea; color: #611a15; }
		.success { background: #e8f5e9; color: #1b5e20; padding: 16px; border-radius: 6px; margin-top: 20px; }
		.warning { background: #fff3cd; color: #664d03; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
		a.next-step { display: inline-block; margin-top: 8px; }
	</style>
</head>
<body>

<h1>🔌 Plugins base</h1>

<?php if ( ! pb_es_post() ) : ?>
	<div class="warning">
		⚠️ <strong>WPS Hide Login</strong>: si lo activas, cambia la URL de <code>/wp-login.php</code> de
		inmediato. Entra a <em>Ajustes → WPS Hide Login</em> para confirmar o cambiar la nueva URL antes
		de cerrar sesión — si la pierdes, la única forma de recuperar el acceso es renombrar la carpeta
		del plugin por FTP/administrador de archivos.
	</div>
<?php endif; ?>

<?php if ( pb_es_post() ) : ?>

	<p>Procesando...</p>
	<ul class="log">
	<?php
	// Salida progresiva: cada plugin se procesa y se imprime antes de seguir
	// con el siguiente. Requiere zlib.output_compression en Off (lo mismo
	// que ya valida wp-tools/wp-check.php) para verse en vivo.
	$a_instalar = isset( $_POST['instalar'] ) && is_array( $_POST['instalar'] ) ? array_map( 'sanitize_key', $_POST['instalar'] ) : array();
	$a_activar  = isset( $_POST['activar'] ) && is_array( $_POST['activar'] ) ? array_map( 'sanitize_key', $_POST['activar'] ) : array();

	foreach ( $a_instalar as $slug ) {
		if ( ! isset( $plugins_base[ $slug ] ) ) {
			continue;
		}
		$datos_plugin = $plugins_base[ $slug ];

		$resultado = pb_instalar_plugin( $slug, $datos_plugin );
		$mensaje   = $resultado['mensaje'];

		if ( $resultado['ok'] && $resultado['archivo'] && in_array( $slug, $a_activar, true ) ) {
			if ( is_plugin_active( $resultado['archivo'] ) ) {
				$mensaje .= ' Ya estaba activo.';
			} else {
				$activado = activate_plugin( $resultado['archivo'] );
				$mensaje .= is_wp_error( $activado )
					? ( ' No se pudo activar: ' . $activado->get_error_message() )
					: ' Activado.';
			}
		}

		$clase = $resultado['ok'] ? 'ok' : 'error';
		$icono = $resultado['ok'] ? '✅' : '⚠️';

		echo '<li class="' . esc_attr( $clase ) . '">' . $icono . ' <strong>' . esc_html( $datos_plugin['nombre'] ) . '</strong>: ' . esc_html( $mensaje ) . '</li>';

		if ( ob_get_level() > 0 ) {
			@ob_flush();
		}
		flush();
	}
	?>
	</ul>

	<div class="success">
		<strong>Listo.</strong> Revisa el detalle de cada plugin arriba.<br>
		<a class="next-step" href="<?= htmlspecialchars( get_main_url( 'wp-tools' ) ) ?>/wp-admin/plugins.php">Ver plugins en wp-admin →</a><br>
		<a class="next-step" href="examples.php">Ir a examples.php →</a>
	</div>

<?php else : ?>

	<p>Marca qué instalar y, en la misma pasada, qué activar de una vez.</p>

	<form method="POST">
		<?php foreach ( $plugins_base as $slug => $datos_plugin ) : ?>
			<div class="fila">
				<span class="nombre">
					<?= esc_html( $datos_plugin['nombre'] ) ?>
					<span class="badge"><?= $datos_plugin['fuente'] === 'github' ? 'GitHub' : 'wordpress.org' ?></span>
				</span>
				<label><input type="checkbox" name="instalar[]" value="<?= esc_attr( $slug ) ?>" checked> Instalar</label>
				<label><input type="checkbox" name="activar[]" value="<?= esc_attr( $slug ) ?>" <?= $datos_plugin['activar'] ? 'checked' : '' ?>> Activar</label>
			</div>
		<?php endforeach; ?>

		<button type="submit">Procesar</button>
	</form>

<?php endif; ?>

</body>
</html>