<?php
session_start();
require_once 'config.php';

/**
 * Manejador para la gestión de usuarios existentes
 * Solo permite actualizar usuarios, no crear nuevos
 */

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Debe iniciar sesión para acceder a esta función.']);
    exit();
}

// Verificar si el usuario tiene permisos de administrador
if ($_SESSION['rol_usuario'] !== 'admin' && $_SESSION['rol_usuario'] !== 'administrador') {
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para gestionar usuarios.']);
    exit();
}

// Función para sanitizar entradas
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Procesar solicitud según la acción
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Actualizar usuario existente
if ($action === 'actualizar') {
    // Obtener y sanitizar datos del formulario
    $numeroDocumento = isset($_POST['numeroDocumento']) ? intval($_POST['numeroDocumento']) : 0;
    $nombre = sanitizeInput($_POST['nombre']);
    $correo = sanitizeInput($_POST['correo']);
    $rol = sanitizeInput($_POST['rol']);
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : ''; // No sanitizamos la contraseña
    
    // Validar que el número de documento sea válido
    if ($numeroDocumento <= 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Número de documento inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Verificar si el usuario existe
    $sentencia = $conexion->prepare("SELECT num_doc FROM usuarios WHERE num_doc = ?");
    $sentencia->bind_param("i", $numeroDocumento);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'El usuario no existe']);
        $conexion->close();
        exit();
    }
    
    // Preparar la consulta SQL para actualizar el usuario
    if (!empty($contrasena)) {
        // Si se proporciona una nueva contraseña, actualizarla también
        $contrasena_md5 = md5($contrasena);
        $sentencia = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, estado = ?, contrasena = ? WHERE num_doc = ?");
        $sentencia->bind_param("sssisi", $nombre, $correo, $rol, $estado, $contrasena_md5, $numeroDocumento);
    } else {
        // Si no se proporciona contraseña, actualizar solo los otros campos
        $sentencia = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, estado = ? WHERE num_doc = ?");
        $sentencia->bind_param("sssii", $nombre, $correo, $rol, $estado, $numeroDocumento);
    }
    
    // Ejecutar la consulta
    if ($sentencia->execute()) {
        echo json_encode(['exito' => true, 'mensaje' => 'Usuario actualizado correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error al actualizar el usuario: ' . $conexion->error]);
    }
    
    $conexion->close();
    exit();
}

// Obtener lista de usuarios
if ($action === 'listar') {
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Consultar todos los usuarios
    $sentencia = $conexion->prepare("SELECT num_doc, nombre, apellido, correo, rol, cargos, num_telefono, estado FROM usuarios ORDER BY num_doc DESC");
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    
    $usuarios = [];
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    
    echo json_encode(['exito' => true, 'datos' => $usuarios]);
    $conexion->close();
    exit();
}

// Obtener detalles de un usuario específico
if ($action === 'obtener') {
    $numeroDocumento = isset($_POST['numeroDocumento']) ? intval($_POST['numeroDocumento']) : 0;
    
    if ($numeroDocumento <= 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Número de documento inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Consultar el usuario
    $sentencia = $conexion->prepare("SELECT num_doc, nombre, apellido, correo, rol, cargos, num_telefono, estado FROM usuarios WHERE num_doc = ?");
    $sentencia->bind_param("i", $numeroDocumento);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
    } else {
        $usuario = $resultado->fetch_assoc();
        echo json_encode(['exito' => true, 'datos' => $usuario]);
    }
    
    $conexion->close();
    exit();
}

// Cambiar estado de un usuario (activar/desactivar)
if ($action === 'cambiar_estado') {
    $numeroDocumento = isset($_POST['numeroDocumento']) ? intval($_POST['numeroDocumento']) : 0;
    
    if ($numeroDocumento <= 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Número de documento inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Obtener el estado actual
    $sentencia = $conexion->prepare("SELECT estado FROM usuarios WHERE num_doc = ?");
    $sentencia->bind_param("i", $numeroDocumento);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
        $conexion->close();
        exit();
    }
    
    $fila = $resultado->fetch_assoc();
    $nuevo_estado = $fila['estado'] ? 0 : 1; // Cambiar el estado
    
    // Actualizar el estado
    $sentencia = $conexion->prepare("UPDATE usuarios SET estado = ? WHERE num_doc = ?");
    $sentencia->bind_param("ii", $nuevo_estado, $numeroDocumento);
    
    if ($sentencia->execute()) {
        echo json_encode(['exito' => true, 'mensaje' => 'Estado del usuario actualizado correctamente', 'nuevo_estado' => $nuevo_estado]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error al actualizar el estado del usuario: ' . $conexion->error]);
    }
    
    $conexion->close();
    exit();
}

// Si no se especifica una acción válida
echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
exit();
?>