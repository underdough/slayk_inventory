<?php
session_start();
require_once 'config.php';

/**
 * Script de autenticación para el sistema ARCO
 * Maneja el proceso de inicio de sesión y redirección
 */

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['id_usuario'])) {
    // Redirigir al dashboard si ya está autenticado
    header("Location: ../vistas/dashboard.html");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numeroDocumento = isset($_POST["numeroDocumento"]) ? sanitizeInput($_POST["numeroDocumento"]) : '';
    $contrasena = isset($_POST["contrasena"]) ? $_POST["contrasena"] : ''; // No sanitizamos la contraseña para no alterar caracteres especiales
    
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
    if (empty($numeroDocumento) || empty($contrasena)) {
        $_SESSION["error_login"] = "Por favor, complete todos los campos.";
        header("Location: ../login.html");
        exit();
    } else {
        // Obtener conexión a la base de datos
        $conexion = conectarDB();
        
        // Consulta preparada para prevenir inyección SQL
        $sentencia = $conexion->prepare("SELECT `num_doc`, `nombre`, `contrasena`, `rol` FROM `usuarios` WHERE `num_doc` = ?");
        $sentencia->bind_param("s", $numeroDocumento);
        $sentencia->execute();
        $resultado = $sentencia->get_result();
        
        // Aplicar cifrado MD5 a la contraseña ingresada
        $contrasena_md5 = md5($contrasena);
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            
            // Verificar contraseña con cifrado MD5
            if ($contrasena_md5 === $fila["contrasena"]) {
                // Iniciar sesión
                $_SESSION["id_usuario"] = $fila["num_doc"];
                $_SESSION["nombre_usuario"] = $fila["nombre"];
                $_SESSION["rol_usuario"] = $fila["rol"];
                
                // Recordar usuario si se seleccionó la casilla
                if (isset($_POST["recuerdame"]) && $_POST["recuerdame"] == "on") {
                    $token = bin2hex(random_bytes(16));
                    
                    // Almacenar token en la base de datos
                    $sentencia = $conexion->prepare("UPDATE `usuarios` SET token_recordar = ? WHERE `num_doc` = ?");
                    $sentencia->bind_param("si", $token, $fila["num_doc"]);
                    $sentencia->execute();
                    
                    // Establecer cookie (30 días)
                    setcookie("token_recordar", $token, time() + (86400 * 30), "/");
                }
                
                // Redirigir según el rol
                if ($fila["rol"] === 'admin' || $fila["rol"] === 'administrador') {
                    header("Location: ../vistas/dashboard.html");
                } else {
                    header("Location: ../vistas/productos.html");
                }
                exit();
            } else {
                $_SESSION["error_login"] = "Número de documento o contraseña incorrectos.";
                header("Location: ../login.html");
                exit();
            }
        } else {
            $_SESSION["error_login"] = "Número de documento o contraseña incorrectos.";
            header("Location: ../login.html");
            exit();
        }
        
        $sentencia->close();
        $conexion->close();
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
