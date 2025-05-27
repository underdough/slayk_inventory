/**
 * Validación de formulario de registro
 * Este script valida que las contraseñas coincidan en tiempo real
 */

document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a los campos de contraseña
    const contrasena = document.getElementById('contrasena');
    const confirmarContrasena = document.getElementById('confirmar_contrasena');
    const formulario = document.querySelector('form');
    
    // Función para validar que las contraseñas coincidan
    function validarContrasenas() {
        if (confirmarContrasena.value === '') {
            // Si el campo está vacío, no mostrar mensaje
            confirmarContrasena.setCustomValidity('');
            return;
        }
        
        if (contrasena.value !== confirmarContrasena.value) {
            confirmarContrasena.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmarContrasena.setCustomValidity('');
        }
    }
    
    // Agregar eventos para validar en tiempo real
    contrasena.addEventListener('input', validarContrasenas);
    confirmarContrasena.addEventListener('input', validarContrasenas);
    
    // Validar antes de enviar el formulario
    formulario.addEventListener('submit', function(event) {
        validarContrasenas();
        
        // Si hay algún error de validación, el formulario no se enviará automáticamente
        if (!formulario.checkValidity()) {
            event.preventDefault();
        }
    });
});