<?php
// Configuración de sesión - debe establecerse antes de session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

session_start();
require_once 'servicios/config.php';

/**
 * Página de login con token CSRF para ARCO
 * Genera y valida tokens CSRF para prevenir ataques Cross-Site Request Forgery
 */

// Función para generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Generar token CSRF para el formulario
$csrfToken = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Acceso al Sistema</title>

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="public/componentes/login-pure.css">
    
    <!-- Iconos de Font Awesome (única librería externa que mantenemos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Contenedor principal de autenticación -->
    <div class="cont-auten">
        <!-- Sección de imagen/logo -->
        <div class="img-auten">
            <div>
                <h2>Sistema ARCO</h2>
                <p>Gestión de Inventarios</p>
                <img src="public/componentes/img/logo2.png" alt="Logo ARCO" style="max-width: 150px;">
            </div>
        </div>
        
        <!-- Sección de formulario -->
        <div class="form-auten">
            <!-- Contenedor para mensajes de alerta -->
            <div id="alertContainer"></div>
            
            <!-- Título del formulario -->
            <div class="titulo-inicio">
                <h3><i class="titulo-inicio"></i>Iniciar Sesión</h3>
            </div>
            
            <!-- Formulario de Login -->
            <form id="formlogin" action="servicios/autenticador.php" method="post">
                <!-- Campos ocultos para el procesamiento del formulario -->
                <input type="hidden" name="action" value="login">
                <!-- Token CSRF generado dinámicamente -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                
                <!-- Campo de número de documento -->
                <div class="inputs-login">
                    <label for="numeroDocumento" class="txt-form">Número de Documento</label>
                    <input type="text" class="input-form" id="numeroDocumento" name="numeroDocumento" minlength="6" maxlength="12" title="Solo se permiten números" pattern="[0-9]+" tabindex="1" required>
                </div>
                
                <!-- Campo de contraseña -->
                <div class="inputs-login">
                    <label for="contrasena" class="txt-form">Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" class="input-form" id="contrasena" name="contrasena" maxlength="20" minlength="8" pattern="[a-zA-Z0-9\-\_\@\!]" title="Solo letras y números, algunos caracteres especiales son permitidos como -,_,@,!; El resto no está permitido" tabindex="2" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                    </div>
                </div>
                
                <!-- Opción recordar usuario -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="recuerdame" name="recuerdame">
                    <label class="form-check-label" for="recuerdame">Recordarme</label>
                </div>
                
                <!-- Botón de envío -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
                
                <!-- Enlace para recuperar contraseña -->
                <div class="text-center mt-3">
                    <a href="vistas/recuperar-contra.html" class="txt-olvidado">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
            
            <!-- Mensaje informativo -->
            <div class="alerta alerta-info mt-4">
                <i class="fas fa-info-circle me-2"></i> Solo los usuarios previamente registrados pueden acceder al sistema.
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Función para mostrar mensajes de alerta
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }
        
        // Inicialización cuando el DOM está cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Validación del formulario de login
            const loginForm = document.getElementById('formlogin');
            
            loginForm.addEventListener('submit', function(e) {
                // Aquí puedes agregar validaciones adicionales si es necesario
            });
            
            // Verificar si hay mensajes de error o éxito en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const errorMsg = urlParams.get('error');
            const successMsg = urlParams.get('success');
            
            if (errorMsg) {
                showAlert(decodeURIComponent(errorMsg), 'danger');
            }
            
            if (successMsg) {
                showAlert(decodeURIComponent(successMsg), 'success');
            }
        });
    </script>
</body>
</html>