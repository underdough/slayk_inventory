<?php
session_start();
require_once 'config.php';

/**
 * Script de autenticación para el sistema ARCO
 * Maneja el proceso de inicio de sesión y redirección
 */

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['user_id'])) {
    // Redirigir al dashboard si ya está autenticado
    header("Location: ../vistas/dashboard.html");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST["correo"]) ? sanitizeInput($_POST["correo"]) : '';
    $password = isset($_POST["contrasena"]) ? $_POST["contrasena"] : ''; // No sanitizamos la contraseña para no alterar caracteres especiales
    
    // Verificar si hay demasiados intentos de login
    if (function_exists('registrarIntentoFallido')) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $intentos = registrarIntentoFallido($ip);
        
        if ($intentos > 5) {
            $_SESSION["login_error"] = "Demasiados intentos de inicio de sesión. Por favor, intente más tarde.";
            header("Location: ../login.php");
            exit();
        }
    }
    
    // Validar entrada
    if (empty($username) || empty($password)) {
        $_SESSION["login_error"] = "Por favor, complete todos los campos.";
        header("Location: ../login.php");
        exit();
    } else {
        // Obtener conexión a la base de datos
        $conn = conectarDB();
        
        // Consulta preparada para prevenir inyección SQL
        $stmt = $conn->prepare("SELECT `id`, `correo`, `contrasena`, role FROM `usuarios` WHERE `correo` = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Aplicar cifrado MD5 a la contraseña ingresada
        $password_md5 = md5($password);
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            // Verificar contraseña con cifrado MD5
            if ($password_md5 === $row["contrasena"]) {
                // Iniciar sesión
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["correo"] = $row["correo"];
                $_SESSION["rol"] = $row["rol"];
                
                // Recordar usuario si se seleccionó la casilla
                if (isset($_POST["remember"]) && $_POST["remember"] == "on") {
                    $token = bin2hex(random_bytes(16));
                    
                    // Almacenar token en la base de datos
                    $stmt = $conn->prepare("UPDATE `usuarios` SET remember_token = ? WHERE `id` = ?");
                    $stmt->bind_param("si", $token, $row["id"]);
                    $stmt->execute();
                    
                    // Establecer cookie (30 días)
                    setcookie("remember_token", $token, time() + (86400 * 30), "/");
                }
                
                // Redirigir según el rol
                header("Location: ../vistas/dashboard.html");
                exit();
            } else {
                $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
                header("Location: ../login.php");
                exit();
            }
        } else {
            $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
            header("Location: ../login.php");
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Función para sanitizar entradas si no está definida en config.php
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?>
