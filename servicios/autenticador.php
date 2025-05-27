<?php
session_start();
require_once 'config.php';

/**
 * Manejador unificado de autenticación para ARCO
 * Procesa tanto el login como el registro con validaciones de seguridad
 */

// Función para conectar a la base de datos
function conectarDB() {
    global $servername, $username, $password, $dbname;
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    return $conn;
}

// Función para sanitizar entradas
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para validar correo electrónico
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para validar contraseña
function validarContrasena($contrasena) {
    // Debe tener al menos 8 caracteres, una letra y un número
    return (strlen($contrasena) >= 8 && 
            preg_match('/[A-Za-z]/', $contrasena) && 
            preg_match('/\d/', $contrasena));
}

// Función para registrar intentos de login fallidos
function registrarIntentoFallido($ip) {
    $conn = conectarDB();
    
    // Limpiar intentos antiguos (más de 15 minutos)
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE timestamp < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute();
    
    // Registrar nuevo intento
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, timestamp) VALUES (?, NOW())");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    
    // Verificar si hay demasiados intentos
    $stmt = $conn->prepare("SELECT COUNT(*) as intentos FROM login_attempts WHERE ip_address = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $conn->close();
    
    return $row['intentos'];
}

// Función para generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Procesar solicitud
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Verificar token CSRF para todas las solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        // Token CSRF inválido
        header("Location: ../login.php?error=" . urlencode("Error de seguridad. Por favor, intente nuevamente."));
        exit();
    }
}

// Procesar login
if ($action === 'login') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password']; // No sanitizamos la contraseña para no alterar caracteres especiales
    
    // Verificar si hay demasiados intentos de login
    $ip = $_SERVER['REMOTE_ADDR'];
    $intentos = registrarIntentoFallido($ip);
    
    if ($intentos > 5) {
        header("Location: ../login.php?error=" . urlencode("Demasiados intentos de inicio de sesión. Por favor, intente más tarde."));
        exit();
    }
    
    if (empty($username) || empty($password)) {
        header("Location: ../login.php?error=" . urlencode("Por favor, complete todos los campos."));
        exit();
    }
    
    $conn = conectarDB();
    
    // Consulta preparada para prevenir inyección SQL
    $stmt = $conn->prepare("SELECT id, username, password, role FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Aplicar cifrado MD5 a la contraseña ingresada
    $password_md5 = md5($password);
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if ($row['password'] === $password_md5) {
            // Login exitoso
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // Registrar la hora de inicio de sesión
            $stmt = $conn->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            
            // Redirigir según el rol
            if ($row['role'] === 'admin') {
                header("Location: ../vistas/dashboard.html");
            } else {
                header("Location: ../vistas/productos.html");
            }
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: ../login.php?error=" . urlencode("Usuario o contraseña incorrectos."));
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: ../login.php?error=" . urlencode("Usuario o contraseña incorrectos."));
        exit();
    }
    
    $conn->close();
}

// La funcionalidad de registro ha sido eliminada
// Solo se permite el acceso a usuarios ya registrados en la base de datos
    
    // Esta sección ha sido eliminada como parte de la remoción de la funcionalidad de registro
    if (!empty($errores)) {
        $error_msg = implode("<br>", $errores);
        header("Location: ../login.php?error=" . urlencode($error_msg) . "&register=1");
        exit();
    }


// Si no se especificó una acción válida, redirigir al login
header("Location: ../login.php");
exit();
?>