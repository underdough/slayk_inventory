/**
 * Sistema de verificación de administrador para ARCO
 * Maneja la visibilidad y acceso a funciones administrativas
 */

// Verificar si el usuario es administrador
function verificarRolAdministrador() {
    return fetch('../servicios/verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.usuario) {
                const rol = data.usuario.rol_usuario ? data.usuario.rol_usuario.toLowerCase() : '';
                return  rol === 'administrador';
            }
            return false;
        })
        .catch(error => {
            console.error('Error verificando rol de administrador:', error);
            return false;
        });
}

// Configurar acceso basado en rol
function configurarAccesoAdmin() {
    verificarRolAdministrador().then(esAdmin => {
        const enlaceUsuarios = document.querySelector('a[href="usuarios.php"], a[href="../vistas/usuarios.php"]');
        
        if (enlaceUsuarios) {
            if (!esAdmin) {
                // Si no es administrador, modificar el enlace para mostrar mensaje
                enlaceUsuarios.addEventListener('click', function(e) {
                    e.preventDefault();
                    mostrarAlertaAccesoDenegado();
                });
                
                // Agregar clase visual para indicar restricción
                enlaceUsuarios.classList.add('restricted-access');
                enlaceUsuarios.title = 'Acceso restringido - Solo administradores';
                
                // Agregar ícono de candado
                const icono = enlaceUsuarios.querySelector('i');
                if (icono) {
                    icono.classList.remove('fa-users');
                    icono.classList.add('fa-lock');
                }
            }
        }
        
        // Agregar indicador visual en el dashboard si no es admin
        if (!esAdmin) {
            agregarIndicadorRestriccion();
        }
    });
}

// Mostrar alerta de acceso denegado
function mostrarAlertaAccesoDenegado() {
    // Verificar si existe la función showAlert del dashboard
    if (typeof showAlert === 'function') {
        showAlert('Acceso denegado: Solo los administradores pueden gestionar usuarios', 'error');
    } else {
        // Fallback para páginas sin sistema de alertas
        alert('Acceso denegado: Solo los administradores pueden gestionar usuarios');
    }
}

// Agregar indicador visual de restricción
function agregarIndicadorRestriccion() {
    const enlaceUsuarios = document.querySelector('a[href="usuarios.php"], a[href="../vistas/usuarios.php"]');
    if (enlaceUsuarios && !enlaceUsuarios.querySelector('.restriction-badge')) {
        const badge = document.createElement('span');
        badge.className = 'restriction-badge';
        badge.innerHTML = '<i class="fas fa-lock" style="font-size: 10px; margin-left: 5px; opacity: 0.7;"></i>';
        enlaceUsuarios.appendChild(badge);
    }
}

// Verificar acceso directo a usuarios.php
function verificarAccesoDirecto() {
    // Solo ejecutar en la página de usuarios
    if (window.location.pathname.includes('usuarios.php')) {
        verificarRolAdministrador().then(esAdmin => {
            if (!esAdmin) {
                // Redirigir al dashboard con mensaje de error
                window.location.href = 'dashboard.html?error=' + encodeURIComponent('Acceso denegado: Solo los administradores pueden gestionar usuarios');
            } else{
                // Si es admin, permitir acceso
                window.location.href = 'usuarios.html';
            }
        });
    }
}

// Estilos CSS para elementos restringidos
function agregarEstilosRestriccion() {
    const style = document.createElement('style');
    style.textContent = `
        .restricted-access {
            opacity: 0.6;
            position: relative;
        }
        
        .restricted-access:hover {
            opacity: 0.8;
            cursor: not-allowed;
        }
        
        .restriction-badge {
            color: #f39c12;
        }
        
        .restricted-access::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 40%, rgba(243, 156, 18, 0.1) 50%, transparent 60%);
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);
}

// Inicializar verificación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    agregarEstilosRestriccion();
    configurarAccesoAdmin();
    verificarAccesoDirecto();
});

// Exportar funciones para uso global
window.verificarRolAdministrador = verificarRolAdministrador;
window.configurarAccesoAdmin = configurarAccesoAdmin;
window.mostrarAlertaAccesoDenegado = mostrarAlertaAccesoDenegado;