<?php
// Configuración de sesión - debe establecerse antes de session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

session_start();
require_once 'config.php';

/**
 * Manejador unificado de autenticación para ARCO
 * Procesa tanto el login como el registro con validaciones de seguridad
 */

// Función para conectar a la base de datos
function conectarBaseDatos() {
    
    $conexionBD = new mysqli("localhost", "root","", "slayk");
    
    if ($conexionBD->connect_error) {
        die("Error de conexión: " . $conexionBD->connect_error);
    }
    
    return $conexionBD;
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
function registrarIntentoFallido($direccionIP) {
    $conexionBD = conectarBaseDatos();
    
    // Limpiar intentos antiguos (más de 15 minutos)
    $consulta = $conexionBD->prepare("DELETE FROM login_attempts WHERE timestamp < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $consulta->execute();
    
    // Registrar nuevo intento
    $consulta = $conexionBD->prepare("INSERT INTO login_attempts (ip_address, timestamp) VALUES (?, NOW())");
    $consulta->bind_param("s", $direccionIP);
    $consulta->execute();
    
    // Verificar si hay demasiados intentos
    $consulta = $conexionBD->prepare("SELECT COUNT(*) as intentos FROM login_attempts WHERE ip_address = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $consulta->bind_param("s", $direccionIP);
    $consulta->execute();
    $resultadoConsulta = $consulta->get_result();
    $filaResultado = $resultadoConsulta->fetch_assoc();
    
    $conexionBD->close();
    
    return $filaResultado['intentos'];
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
    // Verificar que existe el token en la solicitud
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
        error_log("Token CSRF faltante en la solicitud POST desde IP: " . $_SERVER['REMOTE_ADDR']);
        header("Location: ../login.php?error=" . urlencode("Error de seguridad: Token CSRF faltante. Por favor, intente nuevamente."));
        exit();
    }
    
    // Verificar que existe el token en la sesión
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        error_log("Token CSRF faltante en la sesión para IP: " . $_SERVER['REMOTE_ADDR']);
        header("Location: ../login.php?error=" . urlencode("Error de seguridad: Sesión inválida. Por favor, intente nuevamente."));
        exit();
    }
    
    // Verificar que los tokens coinciden
    if (!verificarTokenCSRF($_POST['csrf_token'])) {
        error_log("Token CSRF inválido desde IP: " . $_SERVER['REMOTE_ADDR'] . " - Token recibido: " . substr($_POST['csrf_token'], 0, 10) . "...");
        // Regenerar token para la próxima solicitud
        unset($_SESSION['csrf_token']);
        header("Location: ../login.php?error=" . urlencode("Error de seguridad: Token CSRF inválido. Por favor, intente nuevamente."));
        exit();
    }
    
    // Log de verificación exitosa (solo para debugging, remover en producción)
    error_log("Token CSRF verificado exitosamente para IP: " . $_SERVER['REMOTE_ADDR']);
}

// Procesar login
if ($action === 'login') {
    $numeroDocumento = sanitizeInput($_POST['numeroDocumento']);
    $contrasena = $_POST['contrasena']; // No sanitizamos la contraseña para no alterar caracteres especiales
    
    // Verificar si hay demasiados intentos de login
    $direccionIP = $_SERVER['REMOTE_ADDR'];
    $intentosLogin = registrarIntentoFallido($direccionIP);
    
    if ($intentosLogin > 5) {
        header("Location: ../login.php?error=" . urlencode("Demasiados intentos de inicio de sesión. Por favor, intente más tarde."));
        exit();
    }
    
    if (empty($numeroDocumento) || empty($contrasena)) {
        header("Location: ../login.php?error=" . urlencode("Por favor, complete todos los campos."));
        exit();
    }
    
    $conexionBD = conectarBaseDatos();
    
    // Consulta preparada para prevenir inyección SQL
    $consultaUsuario = $conexionBD->prepare("SELECT num_doc, nombre, contrasena, rol FROM usuarios WHERE num_doc = ?");
    $consultaUsuario->bind_param("s", $numeroDocumento);
    $consultaUsuario->execute();
    $resultadoUsuario = $consultaUsuario->get_result();
    
    // Aplicar cifrado MD5 a la contraseña ingresada
    $contrasenaEncriptada = md5($contrasena);
    
    if ($resultadoUsuario->num_rows === 1) {
        $datosUsuario = $resultadoUsuario->fetch_assoc();
        
        if ($datosUsuario['contrasena'] === $contrasenaEncriptada) {
            // Login exitoso
            $_SESSION['id_usuario'] = $datosUsuario['num_doc'];
            $_SESSION['nombre_usuario'] = $datosUsuario['nombre'];
            $_SESSION['rol_usuario'] = $datosUsuario['rol'];
            
            // Registrar la hora de inicio de sesión
            $actualizarAcceso = $conexionBD->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE num_doc = ?");
            $actualizarAcceso->bind_param("i", $datosUsuario['num_doc']);
            $actualizarAcceso->execute();
            
            // Redirigir según el rol
            if ($datosUsuario['rol'] === 'admin' || $datosUsuario['rol'] === 'administrador') {
                header("Location: ../vistas/dashboard.html");
            } else {
                header("Location: ../vistas/productos.html");
            }
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: ../login.php?error=" . urlencode("Número de documento o contraseña incorrectos."));
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: ../login.php?error=" . urlencode("Número de documento o contraseña incorrectos."));
        exit();
    }
    
    // Move connection close before any exit() calls to ensure it's always executed
    $conexionBD->close();
    header("Location: ../login.html?error=" . urlencode("Número de documento o contraseña incorrectos."));
    exit();
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