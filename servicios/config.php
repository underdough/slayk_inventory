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

// Configuración de sesión - Estas configuraciones deben establecerse antes de session_start()
// Se han movido a los archivos individuales que inician sesiones

// Zona horaria
date_default_timezone_set('America/Bogota');

// Función para conectar a la base de datos
function conectarDB() {
    $conn = new mysqli("localhost", "root", "", "slayk");
    
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
