<?php
/**
 * Verificador de sesión para ARCO
 * Verifica si el usuario está autenticado y devuelve información de la sesión
 */

// Configuración de seguridad para sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Iniciar sesión
session_start();

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Función para responder con JSON
function responderJSON($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar si existe una sesión activa
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    responderJSON([
        'autenticado' => false,
        'mensaje' => 'No hay sesión activa'
    ]);
}

// Verificar si la sesión no ha expirado (opcional: agregar tiempo de expiración)
if (isset($_SESSION['ultimo_acceso'])) {
    $tiempoInactividad = 3600; // 1 hora en segundos
    if (time() - $_SESSION['ultimo_acceso'] > $tiempoInactividad) {
        // Destruir sesión expirada
        session_destroy();
        responderJSON([
            'autenticado' => false,
            'mensaje' => 'Sesión expirada'
        ]);
    }
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();

// Obtener información del usuario de la sesión
$documentoNumero = $_SESSION['id_usuario'] ?? null;
$nombreUsuario = $_SESSION['nombre_usuario'] ?? null;
$rolUsuario = $_SESSION['rol_usuario'] ?? null;

// Validar que los datos esenciales estén presentes
if (!$documentoNumero || !$rolUsuario) {
    // Si faltan datos críticos, destruir la sesión
    session_destroy();
    responderJSON([
        'autenticado' => false,
        'mensaje' => 'Datos de sesión incompletos'
    ]);
}

// Opcional: Verificar en base de datos si el usuario sigue activo
// (Descomenta si quieres validación adicional contra la BD)
/*
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT estado FROM usuarios WHERE documento_numero = ?");
    $stmt->execute([$documentoNumero]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario || $usuario['estado'] !== 'activo') {
        session_destroy();
        responderJSON([
            'autenticado' => false,
            'mensaje' => 'Usuario inactivo o no encontrado'
        ]);
    }
} catch (PDOException $e) {
    // En caso de error de BD, mantener la sesión pero registrar el error
    error_log("Error verificando usuario en BD: " . $e->getMessage());
}
*/

// Responder con información de la sesión
responderJSON([
    'autenticado' => true,
    'id_usuario' => $documentoNumero,
    'nombre_usuario' => $nombreUsuario,
    'rol' => $rolUsuario,
    'es_admin' => in_array(strtolower($rolUsuario), ['admin', 'administrador']),
    'ultimo_acceso' => $_SESSION['ultimo_acceso'] ?? null,
    'mensaje' => 'Sesión válida'
]);
?>