/**
 * Sistema de notificaciones para el sistema ARCO
 * Proporciona una forma elegante de mostrar mensajes al usuario
 */

// Crear el contenedor de notificaciones si no existe
function crearContenedorNotificaciones() {
    let contenedor = document.getElementById('notificaciones-container');
    
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'notificaciones-container';
        contenedor.style.position = 'fixed';
        contenedor.style.top = '20px';
        contenedor.style.right = '20px';
        contenedor.style.zIndex = '9999';
        document.body.appendChild(contenedor);
    }
    
    return contenedor;
}

/**
 * Muestra una notificación al usuario
 * @param {string} titulo - Título de la notificación
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo de notificación: 'success', 'error', 'warning', 'info'
 * @param {number} duracion - Duración en milisegundos (por defecto 3000ms)
 */
function mostrarNotificacion(titulo, mensaje, tipo = 'info', duracion = 3000) {
    const contenedor = crearContenedorNotificaciones();
    
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion';
    notificacion.style.backgroundColor = 'white';
    notificacion.style.borderRadius = '5px';
    notificacion.style.boxShadow = '0 3px 10px rgba(0, 0, 0, 0.2)';
    notificacion.style.padding = '15px 20px';
    notificacion.style.marginBottom = '10px';
    notificacion.style.minWidth = '300px';
    notificacion.style.maxWidth = '400px';
    notificacion.style.position = 'relative';
    notificacion.style.transform = 'translateX(120%)';
    notificacion.style.transition = 'transform 0.3s ease';
    
    // Establecer color de borde según el tipo
    let borderColor = '#39a900'; // Verde por defecto (success)
    let iconClass = 'fa-check-circle';
    
    switch (tipo) {
        case 'error':
            borderColor = '#d32f2f';
            iconClass = 'fa-times-circle';
            break;
        case 'warning':
            borderColor = '#f57c00';
            iconClass = 'fa-exclamation-triangle';
            break;
        case 'info':
            borderColor = '#1976d2';
            iconClass = 'fa-info-circle';
            break;
    }
    
    notificacion.style.borderLeft = `4px solid ${borderColor}`;
    
    // Crear contenido de la notificación
    notificacion.innerHTML = `
        <div style="display: flex; align-items: flex-start;">
            <div style="margin-right: 15px; color: ${borderColor};">
                <i class="fas ${iconClass}" style="font-size: 24px;"></i>
            </div>
            <div style="flex-grow: 1;">
                <h4 style="margin: 0 0 5px 0; font-weight: 500;">${titulo}</h4>
                <p style="margin: 0; font-size: 14px;">${mensaje}</p>
            </div>
            <div style="margin-left: 10px; cursor: pointer;" class="cerrar-notificacion">
                <i class="fas fa-times"></i>
            </div>
        </div>
    `;
    
    // Añadir al contenedor
    contenedor.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.style.transform = 'translateX(0)';
    }, 10);
    
    // Configurar cierre de la notificación
    const cerrarBtn = notificacion.querySelector('.cerrar-notificacion');
    cerrarBtn.addEventListener('click', () => cerrarNotificacion(notificacion));
    
    // Auto-cerrar después de la duración especificada
    setTimeout(() => cerrarNotificacion(notificacion), duracion);
}

/**
 * Cierra una notificación con animación
 * @param {HTMLElement} notificacion - Elemento de notificación a cerrar
 */
function cerrarNotificacion(notificacion) {
    notificacion.style.transform = 'translateX(120%)';
    
    setTimeout(() => {
        if (notificacion.parentElement) {
            notificacion.parentElement.removeChild(notificacion);
        }
    }, 300);
}