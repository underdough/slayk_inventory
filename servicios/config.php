<?php
// Archivo de configuración para la base de datos y otras configuraciones globales

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_bd');
define('DB_PASS', 'contraseña_bd');
define('DB_NAME', 'arco_inventario');

// Configuración de la aplicación
define('APP_NAME', 'ARCO');
define('APP_VERSION', '1.0.0');

// Configuración de rutas
define('BASE_URL', 'http://localhost/arco/');
define('ROOT_PATH', dirname(__FILE__) . '/');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Zona horaria
date_default_timezone_set('America/Bogota');

// Función para conectar a la base de datos
function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
    return $conn;
}

// Función para sanitizar entradas
function sanitizar($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}
?>
