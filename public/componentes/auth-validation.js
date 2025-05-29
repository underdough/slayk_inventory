/**
 * Validación avanzada para el sistema de autenticación ARCO
 * Este script proporciona validación en tiempo real y medidas de seguridad
 * para los formularios de inicio de sesión y registro
 */

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los formularios
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Referencias a los campos de contraseña del registro
    const registerPassword = document.getElementById('registerPassword');
    const registerConfirmPassword = document.getElementById('registerConfirmPassword');
    
    // Función para mostrar mensajes de alerta
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alert);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    }
    
    // Función para validar la fortaleza de la contraseña
    function validatePasswordStrength(password) {
        // Debe tener al menos 8 caracteres
        if (password.length < 8) {
            return {
                valid: false,
                message: 'La contraseña debe tener al menos 8 caracteres'
            };
        }
        
        // Debe contener al menos una letra
        if (!/[A-Za-z]/.test(password)) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos una letra'
            };
        }
        
        // Debe contener al menos un número
        if (!/\d/.test(password)) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos un número'
            };
        }
        
        // Opcional: verificar si contiene caracteres especiales para mayor seguridad
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        return {
            valid: true,
            strong: hasSpecialChar,
            message: hasSpecialChar ? 'Contraseña fuerte' : 'Contraseña aceptable'
        };
    }
    
    // Función para mostrar la fortaleza de la contraseña
    function updatePasswordStrength() {
        if (!registerPassword) return;
        
        const password = registerPassword.value;
        if (!password) {
            // Si no hay contraseña, no mostrar nada
            return;
        }
        
        const strengthResult = validatePasswordStrength(password);
        
        // Buscar o crear el elemento para mostrar la fortaleza
        let strengthFeedback = document.getElementById('passwordStrength');
        if (!strengthFeedback) {
            strengthFeedback = document.createElement('div');
            strengthFeedback.id = 'passwordStrength';
            strengthFeedback.className = 'form-text';
            registerPassword.parentNode.appendChild(strengthFeedback);
        }
        
        // Actualizar el mensaje y el color según la fortaleza
        if (!strengthResult.valid) {
            strengthFeedback.className = 'form-text text-danger';
            strengthFeedback.textContent = strengthResult.message;
        } else if (strengthResult.strong) {
            strengthFeedback.className = 'form-text text-success';
            strengthFeedback.textContent = strengthResult.message;
        } else {
            strengthFeedback.className = 'form-text text-warning';
            strengthFeedback.textContent = strengthResult.message;
        }
    }
    
    // Validar que las contraseñas coincidan en tiempo real
    function validatePasswordsMatch() {
        if (!registerPassword || !registerConfirmPassword) return;
        
        const password = registerPassword.value;
        const confirmPassword = registerConfirmPassword.value;
        
        if (!confirmPassword) {
            // Si el campo de confirmación está vacío, no mostrar mensaje
            return;
        }
        
        // Buscar o crear el elemento para mostrar el mensaje
        let matchFeedback = document.getElementById('passwordMatch');
        if (!matchFeedback) {
            matchFeedback = document.createElement('div');
            matchFeedback.id = 'passwordMatch';
            matchFeedback.className = 'form-text';
            registerConfirmPassword.parentNode.appendChild(matchFeedback);
        }
        
        // Actualizar el mensaje según si coinciden o no
        if (password === confirmPassword) {
            matchFeedback.className = 'form-text text-success';
            matchFeedback.textContent = 'Las contraseñas coinciden';
            registerConfirmPassword.setCustomValidity('');
        } else {
            matchFeedback.className = 'form-text text-danger';
            matchFeedback.textContent = 'Las contraseñas no coinciden';
            registerConfirmPassword.setCustomValidity('Las contraseñas no coinciden');
        }
    }
    
    // Validar el formato del correo electrónico
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Sanitizar entradas para prevenir XSS
    function sanitizeInput(input) {
        return input
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .replace(/\//g, '&#x2F;');
    }
    
    // Agregar eventos para validación en tiempo real
    if (registerPassword) {
        registerPassword.addEventListener('input', function() {
            updatePasswordStrength();
            validatePasswordsMatch();
        });
    }
    
    if (registerConfirmPassword) {
        registerConfirmPassword.addEventListener('input', validatePasswordsMatch);
    }
    
    // Validar formulario de registro antes de enviar
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            // Validar que las contraseñas coincidan
            if (registerPassword.value !== registerConfirmPassword.value) {
                e.preventDefault();
                showAlert('Las contraseñas no coinciden');
                return;
            }
            
            // Validar fortaleza de la contraseña
            const strengthResult = validatePasswordStrength(registerPassword.value);
            if (!strengthResult.valid) {
                e.preventDefault();
                showAlert(strengthResult.message);
                return;
            }
            
            // Validar formato de correo electrónico
            const emailInput = document.getElementById('registerEmail');
            if (emailInput && !validateEmail(emailInput.value)) {
                e.preventDefault();
                showAlert('Por favor, introduce un correo electrónico válido');
                return;
            }
            
            // Sanitizar entradas para prevenir XSS
            const inputs = registerForm.querySelectorAll('input[type="text"], input[type="email"]');
            inputs.forEach(input => {
                input.value = sanitizeInput(input.value);
            });
        });
    }
    
    // Validar formulario de login antes de enviar
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Sanitizar entradas para prevenir XSS
            const inputs = loginForm.querySelectorAll('input[type="text"]');
            inputs.forEach(input => {
                input.value = sanitizeInput(input.value);
            });
        });
    }
    
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
    
    // Cambiar a la pestaña de registro si viene de un intento de registro
    if (urlParams.get('register')) {
        const registerTab = document.getElementById('register-tab');
        if (registerTab) {
            const tab = new bootstrap.Tab(registerTab);
            tab.show();
        }
    }
});

// Función para mostrar/ocultar contraseñas
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

// Función para prevenir ataques de fuerza bruta
let loginAttempts = 0;
const maxLoginAttempts = 5;
const lockoutTime = 15 * 60 * 1000; // 15 minutos en milisegundos

function checkLoginAttempts() {
    // Obtener intentos almacenados en localStorage
    const storedAttempts = localStorage.getItem('loginAttempts');
    const storedTimestamp = localStorage.getItem('loginLockTime');
    
    if (storedAttempts) {
        loginAttempts = parseInt(storedAttempts);
    }
    
    if (storedTimestamp) {
        const lockTime = parseInt(storedTimestamp);
        const currentTime = new Date().getTime();
        
        // Si el tiempo de bloqueo ha pasado, reiniciar los intentos
        if (currentTime > lockTime) {
            loginAttempts = 0;
            localStorage.removeItem('loginAttempts');
            localStorage.removeItem('loginLockTime');
        }
    }
    
    return loginAttempts >= maxLoginAttempts;
}

function incrementLoginAttempts() {
    loginAttempts++;
    localStorage.setItem('loginAttempts', loginAttempts);
    
    if (loginAttempts >= maxLoginAttempts) {
        const lockUntil = new Date().getTime() + lockoutTime;
        localStorage.setItem('loginLockTime', lockUntil);
    }
}

function resetLoginAttempts() {
    loginAttempts = 0;
    localStorage.removeItem('loginAttempts');
    localStorage.removeItem('loginLockTime');
}

// ===== FUNCIONES DE VALIDACIÓN DE PERMISOS DE ADMINISTRADOR =====

// Función para verificar si el usuario está autenticado
function verificarAutenticacion() {
    // Determinar la ruta correcta según la ubicación del archivo
    const rutaVerificacion = window.location.pathname.includes('/vistas/') 
        ? '../servicios/verificar_sesion.php' 
        : 'servicios/verificar_sesion.php';
    
    const rutaLogin = window.location.pathname.includes('/vistas/') 
        ? '../login.php' 
        : 'login.php';
    
    return fetch(rutaVerificacion)
        .then(response => response.json())
        .then(data => {
            if (!data.autenticado) {
                // Redirigir al login si no está autenticado
                window.location.href = rutaLogin;
                return false;
            }
            return data;
        })
        .catch(error => {
            console.error('Error verificando autenticación:', error);
            window.location.href = rutaLogin;
            return false;
        });
}

// Función para verificar si el usuario es administrador
function verificarPermisoAdmin() {
    return verificarAutenticacion()
        .then(userData => {
            if (!userData || (userData.rol !== 'admin' && userData.rol !== 'administrador')) {
                // Mostrar mensaje de acceso denegado
                mostrarAccesoDenegado();
                return false;
            }
            return true;
        });
}

// Función para mostrar mensaje de acceso denegado
function mostrarAccesoDenegado() {
    document.body.innerHTML = `
        <div style="
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 20px;
        ">
            <div style="
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                max-width: 500px;
            ">
                <i class="fas fa-shield-alt" style="font-size: 4rem; margin-bottom: 20px; color: #ff6b6b;"></i>
                <h1 style="margin: 0 0 20px 0; font-size: 2.5rem; font-weight: 600;">Acceso Denegado</h1>
                <p style="margin: 0 0 30px 0; font-size: 1.2rem; opacity: 0.9;">No tienes permisos para acceder a esta sección.</p>
                <p style="margin: 0 0 30px 0; font-size: 1rem; opacity: 0.8;">Solo los administradores pueden gestionar usuarios y privilegios.</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <button onclick="window.location.href='dashboard.html'" style="
                        background: #4CAF50;
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 1rem;
                        font-weight: 500;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    " onmouseover="this.style.background='#45a049'" onmouseout="this.style.background='#4CAF50'">
                        <i class="fas fa-home"></i> Ir al Dashboard
                    </button>
                    <button onclick="window.location.href='../servicios/logout.php'" style="
                        background: #f44336;
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 1rem;
                        font-weight: 500;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    " onmouseover="this.style.background='#da190b'" onmouseout="this.style.background='#f44336'">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Función para inicializar la validación en páginas que requieren permisos de admin
function inicializarValidacionAdmin() {
    // Mostrar loading mientras se verifica
    document.body.style.opacity = '0.5';
    
    verificarPermisoAdmin()
        .then(tienePermiso => {
            if (tienePermiso) {
                // Restaurar visibilidad si tiene permisos
                document.body.style.opacity = '1';
            }
            // Si no tiene permisos, mostrarAccesoDenegado() ya se encargó
        });
}

// Función para validar acceso general (solo autenticación)
function inicializarValidacionGeneral() {
    verificarAutenticacion();
}

// Auto-ejecutar validación si se detecta que es una página de usuarios
if (window.location.pathname.includes('usuarios.html')) {
    document.addEventListener('DOMContentLoaded', inicializarValidacionAdmin);
}