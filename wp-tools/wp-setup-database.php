<?php
/**
 * Archivo: crear_base_datos.php
 * Objetivo: Crear una base de datos MySQL usando PDO.
 */

$dbname = "wp_base";
$dbuser = "root";
$dbpass = "root";
$dbhost = "localhost";
$dbencode = "utf8mb4_unicode_ci";

function get_main_url($remove = null)
{
	$https = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
	$protocol = $https ? 'https://' : 'http://';
	$domain = $protocol.$_SERVER['SERVER_NAME'];
	$script = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
	
	if (!empty($script)) {
		$domain .= '/'.$script;
	}

	if ($remove != null) {
		$domain = str_replace($remove, '', $domain);
	}

	return rtrim($domain, '/');
}

try {
    // Conexion sin seleccionar base de datos, porque primero la vamos a crear
    $dsn = "mysql:host={$dbhost};charset=utf8mb4";

    $pdo = new PDO($dsn, $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Crear base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE {$dbencode}";
    $pdo->exec($sql);

    echo "Base de datos '{$dbname}' creada correctamente o ya existia.";
    echo "Datos utilizados:";
	echo "<hr>";
	echo "Nombre: ".$dbname."<br>";
	echo "User: ".$dbuser."<br>";
	echo "Pass: ".$dbpass."<br>";
	echo "Host: ".$dbhost."<br>";
	echo "Codificacion: ".$dbencode."<br>";
	echo "<hr>";
    echo 'Haga click para iniciar la instalacion de WP <a href="'.get_main_url('wp-tools').'">aqui</a>';
} catch (PDOException $e) {
    echo "Error al crear la base de datos: " . $e->getMessage();
}
