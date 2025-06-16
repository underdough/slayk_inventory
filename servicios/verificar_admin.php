<?php
/**
 * Verificador de acceso de administrador para ARCO
 * Verifica si el usuario tiene permisos de administrador para acceder a ciertas vistas
 */

// Configuración de seguridad para sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Iniciar sesión
session_start();

/**
 * Función para verificar si el usuario es administrador
 * @return bool True si es administrador, False en caso contrario
 */
function esAdministrador() {
   // Verificar si las variables de sesión están definidas
    if (!isset($_SESSION['id_usuarios']) || !isset($_SESSION['rol'])) {
        return false;
    }

    // Obtener el ID del usuario y el rol de la sesión
    $id_usuarios = $_SESSION['id_usuarios'];
    $rol = $_SESSION['rol'];

    // Conectar a la base de datos
    $db = new PDO('mysql:host=localhost;dbname=arco_bdd', 'usuario', 'contraseña');

    // Consultar la base de datos para verificar el usuario y el rol
    $stmt = $db->prepare("SELECT id, rol FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id_usuarios]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe y el rol coincide
    if ($user && $user['rol'] == $rol) {
        return true;
    } else {
        // Si no coincide, invalidar la sesión
        session_unset();
        session_destroy();
        return false;
    }
    
}

/**
 * Función para redirigir si no es administrador
 * @param string $paginaRedirect Página a la que redirigir si no es admin
 */
function verificarAccesoAdmin($paginaRedirect = '../vistas/dashboard.html') {
    if (!esAdministrador()) {
        // Si no es administrador, redirigir con mensaje de error
        $_SESSION['error_acceso'] = 'No tienes permisos para acceder a esta sección. Solo los administradores pueden gestionar usuarios.';
        header("Location: $paginaRedirect");
        exit();
    }
}

/**
 * Función para obtener información del usuario actual
 * @return array|null Información del usuario o null si no está autenticado
 */
function obtenerInfoUsuario() {
    if (!isset($_SESSION['id_usuario'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['id_usuario'],
        'nombre' => $_SESSION['nombre_usuario'] ?? 'Usuario',
        'rol' => $_SESSION['rol_usuario'] ?? 'usuario',
        'es_admin' => esAdministrador()
    ];
}

// Si se llama directamente este archivo, verificar y responder con JSON
if (basename($_SERVER['PHP_SELF']) === 'verificar_admin.php') {
    header('Content-Type: application/json');
    
    $usuario = obtenerInfoUsuario();
    
    if (!$usuario) {
        echo json_encode([
            'autenticado' => false,
            'es_admin' => false,
            'mensaje' => 'No hay sesión activa'
        ]);
    } else {
        echo json_encode([
            'autenticado' => true,
            'es_admin' => $usuario['es_admin'],
            'usuario' => $usuario,
            'mensaje' => $usuario['es_admin'] ? 'Acceso de administrador confirmado' : 'Usuario sin permisos de administrador'
        ]);
    }
    exit();
}
?>