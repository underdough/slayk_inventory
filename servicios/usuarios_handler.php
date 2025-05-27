<?php
session_start();
require_once 'config.php';

/**
 * Manejador para la gestión de usuarios existentes
 * Solo permite actualizar usuarios, no crear nuevos
 */

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=" . urlencode("Debe iniciar sesión para acceder a esta función."));
    exit();
}

// Verificar si el usuario tiene permisos de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../vistas/dashboard.html?error=" . urlencode("No tiene permisos para gestionar usuarios."));
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
if ($action === 'update') {
    // Obtener y sanitizar datos del formulario
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $nombre = sanitizeInput($_POST['nombre']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $status = isset($_POST['status']) ? 1 : 0;
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // No sanitizamos la contraseña
    
    // Validar que el ID de usuario sea válido
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conn = conectarDB();
    
    // Verificar si el usuario existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario no existe']);
        $conn->close();
        exit();
    }
    
    // Preparar la consulta SQL para actualizar el usuario
    if (!empty($password)) {
        // Si se proporciona una nueva contraseña, actualizarla también
        $password_md5 = md5($password);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, role = ?, status = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $nombre, $email, $role, $status, $password_md5, $user_id);
    } else {
        // Si no se proporciona contraseña, actualizar solo los otros campos
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, role = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssii", $nombre, $email, $role, $status, $user_id);
    }
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario: ' . $conn->error]);
    }
    
    $conn->close();
    exit();
}

// Obtener lista de usuarios
if ($action === 'list') {
    // Conectar a la base de datos
    $conn = conectarDB();
    
    // Consultar todos los usuarios
    $stmt = $conn->prepare("SELECT id, nombre, username, email, role, status, last_login, created_at FROM usuarios ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $usuarios]);
    $conn->close();
    exit();
}

// Obtener detalles de un usuario específico
if ($action === 'get') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conn = conectarDB();
    
    // Consultar el usuario
    $stmt = $conn->prepare("SELECT id, nombre, username, email, role, status, last_login, created_at FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    } else {
        $usuario = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $usuario]);
    }
    
    $conn->close();
    exit();
}

// Cambiar estado de un usuario (activar/desactivar)
if ($action === 'toggle_status') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
        exit();
    }
    
    // Conectar a la base de datos
    $conn = conectarDB();
    
    // Obtener el estado actual
    $stmt = $conn->prepare("SELECT status FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        $conn->close();
        exit();
    }
    
    $row = $result->fetch_assoc();
    $new_status = $row['status'] ? 0 : 1; // Cambiar el estado
    
    // Actualizar el estado
    $stmt = $conn->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado del usuario actualizado correctamente', 'new_status' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado del usuario: ' . $conn->error]);
    }
    
    $conn->close();
    exit();
}

// Si no se especifica una acción válida
echo json_encode(['success' => false, 'message' => 'Acción no válida']);
exit();
?>