<?php
// Configuración de sesión - debe establecerse antes de session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

session_start();
require_once 'servicios/config.php';

/**
 * Página de login combinada para ARCO
 * Maneja tanto la presentación del formulario como el procesamiento del login
 */

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['id_usuario'])) {
    // Redirigir al dashboard si ya está autenticado
    header("Location: vistas/dashboard.html");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numeroDocumento = isset($_POST["numeroDocumento"]) ? sanitizar($_POST["numeroDocumento"]) : '';
    $contrasena = isset($_POST["contrasena"]) ? $_POST["contrasena"] : ''; // No sanitizamos la contraseña para no alterar caracteres especiales
    
    // Validar entrada
    if (empty($numeroDocumento) || empty($contrasena)) {
        $_SESSION["error_login"] = "Por favor, complete todos los campos.";
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
                
                // Registrar la hora de inicio de sesión
                $actualizarAcceso = $conexion->prepare("UPDATE `usuarios` SET `ultimo_acceso` = NOW() WHERE `num_doc` = ?");
                $actualizarAcceso->bind_param("i", $fila["num_doc"]);
                $actualizarAcceso->execute();
                
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
                
                // Redirigir según el rol - todos los administradores van al dashboard
                if ($fila["rol"] === 'admin' || $fila["rol"] === 'administrador') {
                    header("Location: vistas/dashboard.html");
                } else {
                    header("Location: vistas/productos.html");
                }
                exit();
            } else {
                $_SESSION["error_login"] = "Número de documento o contraseña incorrectos.";
            }
        } else {
            $_SESSION["error_login"] = "Número de documento o contraseña incorrectos.";
        }
        
        $sentencia->close();
        $conexion->close();
    }
}

// Obtener mensajes de error o éxito
$errorMessage = isset($_SESSION['error_login']) ? $_SESSION['error_login'] : '';
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Limpiar mensajes de la sesión
unset($_SESSION['error_login']);
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión de inventarios ARCO - Inicio de sesión">
    <meta name="keywords" content="inventario, gestión, ARCO, login">
    <meta name="author" content="Equipo ARCO">
    
    <!-- Título de la página -->
    <title>ARCO - Iniciar Sesión</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="public/componentes/login-pure.css">
    
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Contenedor principal -->
    <div class="login-container">
        <!-- Panel izquierdo con branding -->
        <div class="login-brand">
            <div class="brand-content">
                <div class="logo">
                    <i class="fas fa-boxes"></i>
                    <h1>ARCO</h1>
                </div>
                <h2>Sistema de Gestión de Inventarios</h2>
                <p>Controla y administra tu inventario de manera eficiente y segura</p>
                
                <!-- Características destacadas -->
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-chart-line"></i>
                        <span>Reportes en tiempo real</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seguridad avanzada</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Acceso multiplataforma</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel derecho con formulario -->
        <div class="login-form-container">
            <div class="login-form">
                <!-- Encabezado del formulario -->
                <div class="form-header">
                    <h3>Iniciar Sesión</h3>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>
                
                <!-- Mensajes de alerta -->
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error" id="alertMessage">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                    <button type="button" class="alert-close" onclick="closeAlert()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success" id="alertMessage">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($successMessage); ?></span>
                    <button type="button" class="alert-close" onclick="closeAlert()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Formulario de login -->
                <form method="POST" action="login.php" id="loginForm" novalidate>
                    <!-- Campo de número de documento -->
                    <div class="form-group">
                        <label for="numeroDocumento">
                            <i class="fas fa-id-card"></i>
                            Número de Documento
                        </label>
                        <input 
                            type="text" 
                            id="numeroDocumento" 
                            name="numeroDocumento" 
                            placeholder="Ingresa tu número de documento"
                            pattern="[0-9]{6,12}"
                            title="El número de documento debe tener entre 6 y 12 dígitos"
                            maxlength="12"
                            required
                            autocomplete="username"
                        >
                        <div class="input-error" id="documentoError"></div>
                    </div>
                    
                    <!-- Campo de contraseña -->
                    <div class="form-group">
                        <label for="contrasena">
                            <i class="fas fa-lock"></i>
                            Contraseña
                        </label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="contrasena" 
                                name="contrasena" 
                                placeholder="Ingresa tu contraseña"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        <div class="input-error" id="contrasenaError"></div>
                    </div>
                    
                    <!-- Opciones adicionales -->
                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="recuerdame" id="recuerdame">
                            <span class="checkmark"></span>
                            Recordarme
                        </label>
                        
                        <a href="recuperacion.html" class="forgot-password">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    
                    <!-- Botón de envío -->
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </button>
                </form>
                
                <!-- Enlaces adicionales -->
                <div class="form-footer">
                    <p>¿No tienes una cuenta? <a href="registro.html">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript -->
    <script>
        // Función para alternar visibilidad de contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('contrasena');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
        
        // Función para cerrar alertas
        function closeAlert() {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                alert.style.display = 'none';
            }
        }
        
        // Auto-cerrar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            }
            
            // Validación del formulario
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const numeroDocumento = document.getElementById('numeroDocumento').value;
                const contrasena = document.getElementById('contrasena').value;
                
                // Limpiar errores previos
                document.getElementById('documentoError').textContent = '';
                document.getElementById('contrasenaError').textContent = '';
                
                let hasErrors = false;
                
                // Validar número de documento
                if (!numeroDocumento) {
                    document.getElementById('documentoError').textContent = 'El número de documento es requerido';
                    hasErrors = true;
                } else if (!/^[0-9]{6,12}$/.test(numeroDocumento)) {
                    document.getElementById('documentoError').textContent = 'El número de documento debe tener entre 6 y 12 dígitos';
                    hasErrors = true;
                }
                
                // Validar contraseña
                if (!contrasena) {
                    document.getElementById('contrasenaError').textContent = 'La contraseña es requerida';
                    hasErrors = true;
                }
                
                if (hasErrors) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>