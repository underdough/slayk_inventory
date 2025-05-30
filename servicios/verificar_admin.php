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
    // Verificar si existe una sesión activa
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol_usuario'])) {
        return false;
    }
    
    // Verificar si el rol es de administrador
    $rol = strtolower($_SESSION['rol_usuario']);
    return in_array($rol, ['admin', 'administrador']);
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