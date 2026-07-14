<?php
/**
 * Archivo: wp-quick-setup.php
 * Objetivo: Formulario único para montar un proyecto WordPress desde cero:
 *           crea la base de datos (PDO), genera wp-config.php con claves
 *           reales y ejecuta la instalación de WordPress (wp_install),
 *           todo en un solo paso. Reemplaza el flujo manual de
 *           wp-setup-database.php + formulario de wp-admin/install.php.
 *
 * Ubicacion: wp-tools/wp-quick-setup.php (un nivel bajo la raiz del proyecto,
 *            junto a wp-setup-database.php y wp-check.php).
 *
 * IMPORTANTE: herramienta de desarrollo local. Eliminar o restringir el
 * acceso antes de subir el proyecto a un entorno público.
 */

// ----------------------------------------
// Helpers booleanos
// ----------------------------------------

function qs_ya_instalado(): bool {
	return file_exists( dirname( __DIR__ ) . '/wp-config.php' );
}

function qs_es_post(): bool {
	return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// ----------------------------------------
// Helpers de retorno
// ----------------------------------------

function get_main_url( $remove = null ) {
	$https    = isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off';
	$protocol = $https ? 'https://' : 'http://';
	$domain   = $protocol . $_SERVER['SERVER_NAME'];
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
 * Pide las claves secretas reales a la API de WordPress.org.
 * Si no hay conexión a internet, genera un set local igual de válido.
 */
function qs_generar_salts(): string {
	$respuesta = @file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/' );
	if ( $respuesta !== false && strlen( $respuesta ) > 100 ) {
		return $respuesta;
	}

	$claves   = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' );
	$charlist = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
	$salida   = '';
	foreach ( $claves as $clave ) {
		$valor = '';
		for ( $i = 0; $i < 64; $i++ ) {
			$valor .= $charlist[ random_int( 0, strlen( $charlist ) - 1 ) ];
		}
		$valor   = addslashes( $valor );
		$salida .= "define( '{$clave}', '{$valor}' );\n";
	}
	return $salida;
}

function qs_slug_bd( string $nombre_proyecto ): string {
	$slug = strtolower( trim( $nombre_proyecto ) );
	$slug = preg_replace( '/[^a-z0-9_]+/', '_', $slug );
	$slug = trim( $slug, '_' );
	return $slug !== '' ? $slug : 'wp_base';
}

function qs_password_segura(): string {
	$charlist = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#%&*';
	$pass     = '';
	for ( $i = 0; $i < 16; $i++ ) {
		$pass .= $charlist[ random_int( 0, strlen( $charlist ) - 1 ) ];
	}
	return $pass;
}

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

/**
 * Crea la base de datos con PDO (mismo enfoque que wp-setup-database.php).
 */
function qs_crear_base_datos( string $host, string $usuario, string $password, string $bd, string $collation ): void {
	$dsn = "mysql:host={$host};charset=utf8mb4";
	$pdo = new PDO( $dsn, $usuario, $password, array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	) );

	$pdo->exec( "CREATE DATABASE IF NOT EXISTS `{$bd}` CHARACTER SET utf8mb4 COLLATE {$collation}" );
}

function qs_escribir_wp_config( array $datos ): void {
	$raiz    = dirname( __DIR__ );
	$muestra = $raiz . '/wp-config-sample.php';

	if ( ! file_exists( $muestra ) ) {
		throw new Exception( 'No se encontró wp-config-sample.php en la raíz del proyecto.' );
	}
	$contenido = file_get_contents( $muestra );

	$contenido = preg_replace( "/define\(\s*'DB_NAME',\s*'[^']*'\s*\);/", "define( 'DB_NAME', '{$datos['db_name']}' );", $contenido );
	$contenido = preg_replace( "/define\(\s*'DB_USER',\s*'[^']*'\s*\);/", "define( 'DB_USER', '{$datos['db_user']}' );", $contenido );
	$contenido = preg_replace( "/define\(\s*'DB_PASSWORD',\s*'[^']*'\s*\);/", "define( 'DB_PASSWORD', '{$datos['db_password']}' );", $contenido );
	$contenido = preg_replace( "/define\(\s*'DB_HOST',\s*'[^']*'\s*\);/", "define( 'DB_HOST', '{$datos['db_host']}' );", $contenido );

	$patron_salts = "/define\(\s*'AUTH_KEY'.*?define\(\s*'NONCE_SALT',\s*'[^']*'\s*\);/s";
	if ( preg_match( $patron_salts, $contenido ) ) {
		$contenido = preg_replace( $patron_salts, trim( $datos['salts'] ), $contenido );
	} else {
		$contenido = str_replace( "define( 'AUTH_KEY',         'put your unique phrase here' );", $datos['salts'], $contenido );
	}

	$contenido = preg_replace( "/\\\$table_prefix\s*=\s*'[^']*';/", "\$table_prefix = '{$datos['table_prefix']}';", $contenido );

	// Fijar WP_HOME/WP_SITEURL explícitamente. Sin esto, wp_install() adivina
	// la URL a partir de la ruta del script que instala (wp-tools/wp-quick-setup.php)
	// y arma URLs rotas como /wp-tools/wp-quick-setup.php/wp-content/...
	$defines_url = "define( 'WP_HOME', '{$datos['site_url']}' );\ndefine( 'WP_SITEURL', '{$datos['site_url']}' );\n\n";
	$patron_marcador = "/(\/\*\s*That's all, stop editing!.*?\*\/)/s";
	if ( preg_match( $patron_marcador, $contenido ) ) {
		$contenido = preg_replace( $patron_marcador, $defines_url . '$1', $contenido );
	} else {
		// Fallback por si el sample no trae el comentario estándar: insertar antes del require de wp-settings.php.
		$contenido = str_replace( "require_once ABSPATH . 'wp-settings.php';", $defines_url . "require_once ABSPATH . 'wp-settings.php';", $contenido );
	}

	if ( file_put_contents( $raiz . '/wp-config.php', $contenido ) === false ) {
		throw new Exception( 'No se pudo escribir wp-config.php (revisa permisos de escritura en la carpeta raíz).' );
	}
}

function qs_instalar_wordpress( array $datos ): array {
	$raiz = dirname( __DIR__ );

	define( 'WP_INSTALLING', true );
	require_once $raiz . '/wp-load.php';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	if ( is_blog_installed() ) {
		throw new Exception( 'WordPress ya está instalado en esta base de datos.' );
	}

	$resultado = wp_install(
		$datos['site_title'],
		$datos['admin_user'],
		$datos['admin_email'],
		1,
		'',
		$datos['admin_password'],
		''
	);

	if ( wp_get_theme( 'wp-base' )->exists() ) {
		switch_theme( 'wp-base' );
	}

	return $resultado;
}

// ----------------------------------------
// Procesamiento del POST
// ----------------------------------------

$error            = null;
$resultado        = null;
$carpeta_proyecto = basename( dirname( __DIR__ ) );

if ( qs_es_post() && ! qs_ya_instalado() ) {
	try {
		$nombre_proyecto = trim( $_POST['project_name'] ?? '' );
		$datos = array(
			'db_name'        => trim( $_POST['db_name'] ?? '' ) ?: qs_slug_bd( $nombre_proyecto ),
			'db_user'        => trim( $_POST['db_user'] ?? 'root' ),
			'db_password'    => $_POST['db_password'] ?? 'root',
			'db_host'        => trim( $_POST['db_host'] ?? 'localhost' ),
			'db_collation'   => trim( $_POST['db_collation'] ?? 'utf8mb4_unicode_ci' ),
			'table_prefix'   => trim( $_POST['table_prefix'] ?? 'wp_' ),
			'site_title'     => trim( $_POST['site_title'] ?? $nombre_proyecto ) ?: 'Proyecto WordPress',
			'admin_user'     => trim( $_POST['admin_user'] ?? 'admin' ),
			'admin_password' => trim( $_POST['admin_password'] ?? '' ) ?: qs_password_segura(),
			'admin_email'    => trim( $_POST['admin_email'] ?? '' ),
			'salts'          => qs_generar_salts(),
			'site_url'       => get_main_url( 'wp-tools' ),
		);

		if ( $datos['admin_email'] === '' ) {
			throw new Exception( 'El email del administrador es obligatorio.' );
		}

		qs_crear_base_datos( $datos['db_host'], $datos['db_user'], $datos['db_password'], $datos['db_name'], $datos['db_collation'] );
		qs_escribir_wp_config( $datos );
		$resultado = qs_instalar_wordpress( $datos );
		$resultado['admin_password_generada'] = $datos['admin_password'];
		$resultado['admin_user']              = $datos['admin_user'];
		$resultado['db_name']                 = $datos['db_name'];
	} catch ( Throwable $e ) {
		$error = 'Error al crear el proyecto: ' . $e->getMessage();
	}
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Setup rápido — WP Base</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		body { font-family: system-ui, sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
		h1 { font-size: 1.4rem; }
		label { display: block; margin-top: 14px; font-weight: 600; font-size: 0.9rem; }
		input { width: 100%; padding: 8px 10px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
		input[readonly] { background: #f0f0f0; color: #555; cursor: not-allowed; }
		fieldset { border: 1px solid #e0e0e0; border-radius: 8px; margin-top: 24px; padding: 4px 16px 16px; }
		legend { font-weight: 700; padding: 0 6px; }
		button { margin-top: 24px; padding: 10px 20px; background: #1a1a1a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.95rem; }
		.error { background: #fdecea; color: #611a15; padding: 12px 16px; border-radius: 6px; margin-top: 20px; }
		.success { background: #e8f5e9; color: #1b5e20; padding: 16px; border-radius: 6px; margin-top: 20px; }
		.success code { background: #fff; padding: 2px 6px; border-radius: 4px; }
		small { color: #666; }
		hr { border: none; border-top: 1px solid #e0e0e0; margin: 16px 0; }
	</style>
</head>
<body>

<h1>Setup rápido de proyecto WordPress</h1>

<?php if ( qs_ya_instalado() && ! $resultado ) : ?>
	<div class="error">
		Ya existe un <code>wp-config.php</code> en este proyecto. Esta herramienta solo se usa para el
		montaje inicial. Si quieres empezar de cero, elimina <code>wp-config.php</code> manualmente
		(y opcionalmente la base de datos) y vuelve a cargar esta página.
	</div>

<?php elseif ( $resultado ) : ?>
	<div class="success">
		<strong>✅ WordPress instalado correctamente.</strong>
		<hr>
		Base de datos: <code><?= htmlspecialchars( $resultado['db_name'] ) ?></code><br>
		Usuario admin: <code><?= htmlspecialchars( $resultado['admin_user'] ) ?></code><br>
		Contraseña: <code><?= htmlspecialchars( $resultado['admin_password_generada'] ) ?></code>
		<small>(guárdala, no se volverá a mostrar)</small>
		<hr>
		<a href="<?= htmlspecialchars( get_main_url( 'wp-tools' ) ) ?>/wp-login.php">Ir al login de wp-admin →</a>
	</div>

<?php else : ?>

	<?php if ( $error ) : ?>
		<div class="error">⚠️ <?= htmlspecialchars( $error ) ?></div>
	<?php endif; ?>

	<form method="POST">
		<fieldset>
			<legend>Proyecto</legend>
			<label>Nombre del proyecto
				<input type="text" name="project_name" value="<?= htmlspecialchars( $carpeta_proyecto ) ?>" readonly>
			</label>
			<label>Título del sitio
				<input type="text" name="site_title" placeholder="(usa el nombre del proyecto si lo dejas vacío)">
			</label>
		</fieldset>

		<fieldset>
			<legend>Base de datos</legend>
			<label>Host <input type="text" name="db_host" value="localhost"></label>
			<label>Nombre de la BD <input type="text" name="db_name" placeholder="(se genera del nombre del proyecto si lo dejas vacío)"></label>
			<label>Usuario MySQL <input type="text" name="db_user" value="root"></label>
			<label>Contraseña MySQL <input type="text" name="db_password" value="root"></label>
			<label>Collation <input type="text" name="db_collation" value="utf8mb4_unicode_ci"></label>
			<label>Prefijo de tablas <input type="text" name="table_prefix" value="wp_"></label>
		</fieldset>

		<fieldset>
			<legend>Usuario administrador</legend>
			<label>Usuario <input type="text" name="admin_user" value="admin" required></label>
			<label>Contraseña <input type="text" name="admin_password" placeholder="(se genera una segura si lo dejas vacío)"></label>
			<label>Email <input type="email" name="admin_email" required></label>
		</fieldset>

		<button type="submit">Crear base de datos e instalar WordPress</button>
	</form>

<?php endif; ?>

</body>
</html>