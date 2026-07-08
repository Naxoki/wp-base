<?php

function ok($status)
{
    return $status
        ? '<span style="color:green;font-weight:bold;">✔</span>'
        : '<span style="color:red;font-weight:bold;">✘</span>';
}

$checks = [

    [
        'name' => 'PHP 8.1+',
        'ok' => version_compare(PHP_VERSION, '8.1', '>='),
        'value' => PHP_VERSION
    ],

    [
        'name' => 'OpenSSL',
        'ok' => extension_loaded('openssl'),
        'value' => extension_loaded('openssl') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'cURL',
        'ok' => function_exists('curl_init'),
        'value' => function_exists('curl_init') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'allow_url_fopen',
        'ok' => ini_get('allow_url_fopen'),
        'value' => ini_get('allow_url_fopen') ? 'On' : 'Off'
    ],

    [
        'name' => 'JSON',
        'ok' => extension_loaded('json'),
        'value' => extension_loaded('json') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'XML',
        'ok' => extension_loaded('xml'),
        'value' => extension_loaded('xml') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'ZIP',
        'ok' => extension_loaded('zip'),
        'value' => extension_loaded('zip') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'GD',
        'ok' => extension_loaded('gd'),
        'value' => extension_loaded('gd') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'Imagick',
        'ok' => extension_loaded('imagick'),
        'value' => extension_loaded('imagick') ? 'Disponible' : 'Opcional'
    ],

    [
        'name' => 'MySQLi',
        'ok' => extension_loaded('mysqli'),
        'value' => extension_loaded('mysqli') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'PDO MySQL',
        'ok' => extension_loaded('pdo_mysql'),
        'value' => extension_loaded('pdo_mysql') ? 'Disponible' : 'No'
    ],

    [
        'name' => 'MBString',
        'ok' => extension_loaded('mbstring'),
        'value' => extension_loaded('mbstring') ? 'Disponible' : 'No'
    ],
	
	[
		'name' => 'zlib.output_compression',
		'ok' => !ini_get('zlib.output_compression'),
		'value' => ini_get('zlib.output_compression') ? 'On (recomendado Off para WP)' : 'Off'
	],

];

echo "<h2>WordPress Environment Check</h2>";

echo "<table border='1' cellpadding='8' cellspacing='0'>";

echo "<tr>
        <th>Componente</th>
        <th>Estado</th>
        <th>Valor</th>
      </tr>";

foreach ($checks as $check) {

    echo "<tr>";

    echo "<td>{$check['name']}</td>";

    echo "<td align='center'>" . ok($check['ok']) . "</td>";

    echo "<td>{$check['value']}</td>";

    echo "</tr>";
}

echo "</table>";

echo "<br>";

echo "<h3>Conectividad</h3>";

$urls = [
    "https://api.wordpress.org/core/version-check/1.7/",
    "https://downloads.wordpress.org/"
];

echo "<table border='1' cellpadding='8' cellspacing='0'>";

echo "<tr>
        <th>URL</th>
        <th>Estado</th>
      </tr>";

foreach ($urls as $url) {

    $ok = @file_get_contents($url) !== false;

    echo "<tr>";

    echo "<td>$url</td>";

    echo "<td align='center'>" . ok($ok) . "</td>";

    echo "</tr>";
}

echo "</table>";

echo "<br>";

echo "<h3>Configuración PHP</h3>";

echo "<table border='1' cellpadding='8' cellspacing='0'>";

$config = [
    'memory_limit',
    'max_execution_time',
    'upload_max_filesize',
    'post_max_size',
    'max_input_vars'
];

foreach ($config as $item) {

    echo "<tr>";

    echo "<td>$item</td>";

    echo "<td>" . ini_get($item) . "</td>";

    echo "</tr>";
}

echo "</table>";