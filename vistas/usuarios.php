<?php
// Configuración de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar acceso de administrador
try {
    require_once '../servicios/verificar_admin.php';
    
    // Verificar si hay una sesión activa
    if (!isset($_SESSION['id_usuario'])) {
        // Si no hay sesión, redirigir al login
        header('Location: ../login.html?error=' . urlencode('Debe iniciar sesión para acceder al sistema'));
        exit();
    }
    
    // Verificar si es administrador
    if (!esAdministrador()) {
        // Si no es administrador, redirigir al dashboard con mensaje
        header('Location: ../vistas/dashboard.html?error=' . urlencode('Acceso denegado: Solo los administradores pueden gestionar usuarios'));
        exit();
    } else {
        // Si es administrador, permitir el acceso
        header('Location:../vistas/usuarios.html');
    }
    
} catch (Exception $e) {
    // En caso de error, mostrar mensaje y redirigir
    echo "Error: " . $e->getMessage();
    header('Location: ../login.html?error=sistema');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Gestión de Usuarios</title>
    <link rel="stylesheet" href="../public/componentes/formato-verde.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Añadir FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos específicos para la página de usuarios -->
    <link rel="stylesheet" href="../public/componentes/usuarios.css">
    <link rel="stylesheet" href="../public/componentes/global.css">
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>ARCO</h1>
            <p class="subtlo">Gestión de Inventario</p>
        </div>
        <div class="sidebar-menu">
            <a href="../vistas/dashboard.html" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="../vistas/productos.html" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="../vistas/categorias.html" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="../vistas/movimientos.html" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="../vistas/usuarios.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="../vistas/reportes.html" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="../vistas/configuracion.html" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>Gestión de Usuarios</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar usuarios...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <!-- Se ha eliminado el botón de nuevo usuario ya que no se permite el registro de nuevos usuarios -->
                <div class="alert alert-info" style="margin: 0; padding: 8px 15px;">
                    <i class="fas fa-info-circle"></i> Solo se pueden editar usuarios existentes
                </div>
            </div>
        </div>
        
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Último Acceso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Juan Pérez</td>
                        <td>juan.perez@arco.com</td>
                        <td>Administrador</td>
                        <td>15/05/2023 10:30</td>
                        <td><span class="status status-activo">Activo</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td>María López</td>
                        <td>maria.lopez@arco.com</td>
                        <td>Supervisor</td>
                        <td>14/05/2023 15:45</td>
                        <td><span class="status status-activo">Activo</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td>Carlos Rodríguez</td>
                        <td>carlos.rodriguez@arco.com</td>
                        <td>Vendedor</td>
                        <td>13/05/2023 09:15</td>
                        <td><span class="status status-activo">Activo</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>004</td>
                        <td>Ana Martínez</td>
                        <td>ana.martinez@arco.com</td>
                        <td>Vendedor</td>
                        <td>10/05/2023 11:20</td>
                        <td><span class="status status-inactivo">Inactivo</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <div class="page-item"><i class="fas fa-chevron-left"></i></div>
            <div class="page-item active">1</div>
            <div class="page-item">2</div>
            <div class="page-item">3</div>
            <div class="page-item"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>
    
    <!-- Modal para agregar/editar usuario -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Usuario</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="userForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="userName">Nombre</label>
                        <input type="text" class="form-control" id="userName" required>
                    </div>
                    <div class="form-group">
                        <label for="userLastName">Apellido</label>
                        <input type="text" class="form-control" id="userLastName" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" class="form-control" id="userEmail" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="userPassword">Contraseña</label>
                        <input type="password" class="form-control" id="userPassword">
                    </div>
                    <div class="form-group">
                        <label for="userConfirmPassword">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="userConfirmPassword">
                    </div>
                </div>
                <div class="form-group">
                    <label for="userRole">Rol</label>
                    <select class="form-control" id="userRole" required>
                        <option value="">Seleccionar rol</option>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="userStatus">Estado</label>
                    <select class="form-control" id="userStatus" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelUser">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Incluir el script de notificaciones -->
    <script src="../public/componentes/notificaciones.js"></script>
    
    <script>
        // Función para toggle del sidebar en móvil
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Cerrar sidebar al hacer clic fuera en móvil
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Animaciones de entrada
        function animateOnLoad() {
            const elements = document.querySelectorAll('.users-table tbody tr');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }
        
        // Efecto de búsqueda en tiempo real mejorado
        function setupAdvancedSearch() {
            const searchInput = document.querySelector('.search-bar input');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.toLowerCase();
                
                // Agregar efecto de carga
                const table = document.querySelector('.users-table');
                table.classList.add('loading');
                
                searchTimeout = setTimeout(() => {
                    const rows = document.querySelectorAll('.users-table tbody tr');
                    let visibleCount = 0;
                    
                    rows.forEach((row, index) => {
                        const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const rol = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                        
                        if (nombre.includes(searchTerm) || email.includes(searchTerm) || rol.includes(searchTerm)) {
                            row.style.display = '';
                            row.style.animation = `fadeIn 0.3s ease ${index * 0.05}s both`;
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Mostrar mensaje si no hay resultados
                    const tbody = document.querySelector('.users-table tbody');
                    let noResultsRow = tbody.querySelector('.no-results');
                    
                    if (visibleCount === 0 && searchTerm) {
                        if (!noResultsRow) {
                            noResultsRow = document.createElement('tr');
                            noResultsRow.className = 'no-results';
                            noResultsRow.innerHTML = `
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-search" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                    <br>No se encontraron usuarios que coincidan con "${searchTerm}"
                                </td>
                            `;
                            tbody.appendChild(noResultsRow);
                        }
                    } else if (noResultsRow) {
                        noResultsRow.remove();
                    }
                    
                    table.classList.remove('loading');
                }, 300);
            });
        }
        
        // Mejorar la paginación
        function setupPagination() {
            const pageItems = document.querySelectorAll('.page-item');
            pageItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (!this.classList.contains('active')) {
                        pageItems.forEach(p => p.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Simular carga de página
                        const table = document.querySelector('.users-table');
                        table.style.opacity = '0.5';
                        
                        setTimeout(() => {
                            table.style.opacity = '1';
                            animateOnLoad();
                        }, 500);
                    }
                });
            });
        }
        
        // Validación de formulario en tiempo real
        function setupFormValidation() {
            const form = document.getElementById('userForm');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
        }
        
        function validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let message = '';
            
            // Remover clases previas
            field.classList.remove('error', 'success');
            
            // Validaciones específicas
            switch(field.type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (value && !emailRegex.test(value)) {
                        isValid = false;
                        message = 'Email inválido';
                    }
                    break;
                case 'password':
                    if (value && value.length < 6) {
                        isValid = false;
                        message = 'La contraseña debe tener al menos 6 caracteres';
                    }
                    break;
            }
            
            // Validación de campos requeridos
            if (field.required && !value) {
                isValid = false;
                message = 'Este campo es requerido';
            }
            
            // Aplicar estilos
            if (!isValid) {
                field.classList.add('error');
                showFieldError(field, message);
            } else if (value) {
                field.classList.add('success');
                hideFieldError(field);
            }
            
            return isValid;
        }
        
        function showFieldError(field, message) {
            let errorDiv = field.parentNode.querySelector('.field-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.style.cssText = 'color: #f44336; font-size: 12px; margin-top: 5px; animation: slideInDown 0.3s ease;';
                field.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
        }
        
        function hideFieldError(field) {
            const errorDiv = field.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        // Variable global para almacenar el ID del usuario que se está editando
        let currentUserId = null;
        
        // Función para abrir el modal de usuario
        function openUserModal() {
            document.getElementById('userModal').style.display = 'flex';
        }
        
        // Función para cerrar el modal de usuario
        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
            // Limpiar el formulario al cerrar
            document.getElementById('userForm').reset();
            currentUserId = null;
        }
        
        // Función para cargar los datos del usuario seleccionado
        function loadUserData(userId) {
            currentUserId = userId;
            
            // Realizar petición AJAX para obtener los datos del usuario
            const formData = new FormData();
            formData.append('action', 'obtener');
            formData.append('numeroDocumento', userId);
            
            fetch('../servicios/usuarios_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    // Llenar el formulario con los datos del usuario
                    const usuario = data.datos;
                    
                    document.getElementById('userName').value = usuario.nombre || '';
                    document.getElementById('userLastName').value = usuario.apellido || '';
                    document.getElementById('userEmail').value = usuario.correo;
                    document.getElementById('userRole').value = usuario.rol;
                    document.getElementById('userStatus').value = usuario.estado;
                    
                    // Limpiar campos de contraseña
                    document.getElementById('userPassword').value = '';
                    document.getElementById('userConfirmPassword').value = '';
                    
                    openUserModal();
                } else {
                    mostrarNotificacion('Error', data.mensaje, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error', 'Ha ocurrido un error al cargar los datos del usuario', 'error');
            });
        }
        
        // Función para cargar la lista de usuarios
        function cargarUsuarios() {
            const formData = new FormData();
            formData.append('action', 'listar');
            
            fetch('../servicios/usuarios_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    const tbody = document.querySelector('.users-table tbody');
                    tbody.innerHTML = ''; // Limpiar tabla
                    
                    data.datos.forEach(usuario => {
                        const row = document.createElement('tr');
                        
                        // Mostrar información del usuario
                        const nombreCompleto = `${usuario.nombre} ${usuario.apellido || ''}`;
                        
                        row.innerHTML = `
                            <td>${usuario.num_doc}</td>
                            <td>${nombreCompleto}</td>
                            <td>${usuario.correo}</td>
                            <td>${usuario.rol}</td>
                            <td>${usuario.cargos || 'N/A'}</td>
                            <td><span class="status status-${usuario.estado ? 'activo' : 'inactivo'}">${usuario.estado ? 'Activo' : 'Inactivo'}</span></td>
                            <td class="actions">
                                <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                                <div class="action-icon delete"><i class="fas fa-${usuario.estado ? 'ban' : 'check'}"></i></div>
                            </td>
                        `;
                        
                        // Añadir evento para editar
                        row.querySelector('.edit').addEventListener('click', function() {
                            document.querySelector('.modal-title').textContent = 'Editar Usuario';
                            document.getElementById('userPassword').required = false;
                            document.getElementById('userConfirmPassword').required = false;
                            loadUserData(usuario.num_doc);
                        });
                        
                        // Añadir evento para cambiar estado
                        row.querySelector('.delete').addEventListener('click', function() {
                            const accion = usuario.estado ? 'desactivar' : 'activar';
                            if (confirm(`¿Está seguro de que desea ${accion} este usuario?`)) {
                                cambiarEstadoUsuario(usuario.num_doc);
                            }
                        });
                        
                        tbody.appendChild(row);
                    });
                } else {
                    mostrarNotificacion('Error', data.mensaje, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error', 'Ha ocurrido un error al cargar la lista de usuarios', 'error');
            });
        }
        
        // Función para cambiar el estado de un usuario
        function cambiarEstadoUsuario(numeroDocumento) {
            const formData = new FormData();
            formData.append('action', 'cambiar_estado');
            formData.append('numeroDocumento', numeroDocumento);
            
            fetch('../servicios/usuarios_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    mostrarNotificacion('Éxito', data.mensaje, 'success');
                    cargarUsuarios(); // Recargar la lista de usuarios
                } else {
                    mostrarNotificacion('Error', data.mensaje, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error', 'Ha ocurrido un error al cambiar el estado del usuario', 'error');
            });
        }
        
        // Función para mostrar mensajes al usuario - Ahora usa el sistema de notificaciones
        function mostrarMensaje(titulo, mensaje, tipo) {
            mostrarNotificacion(titulo, mensaje, tipo);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar funcionalidades avanzadas
            animateOnLoad();
            setupAdvancedSearch();
            setupPagination();
            setupFormValidation();
            const modal = document.getElementById('userModal');
            const btnCancelUser = document.getElementById('btnCancelUser');
            const closeModal = document.querySelector('.close-modal');
            const userForm = document.getElementById('userForm');
            
            // Cargar la lista de usuarios al iniciar
            cargarUsuarios();
            
            // Cerrar modal
            btnCancelUser.addEventListener('click', closeUserModal);
            closeModal.addEventListener('click', closeUserModal);
            
            // Cerrar modal al hacer clic fuera del contenido
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeUserModal();
                }
            });
            
            // Implementar búsqueda de usuarios
            const searchInput = document.querySelector('.search-bar input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.users-table tbody tr');
                
                rows.forEach(row => {
                    const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const rol = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    
                    if (nombre.includes(searchTerm) || email.includes(searchTerm) || rol.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Manejar envío del formulario
            userForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validar que las contraseñas coincidan si se están ingresando
                const password = document.getElementById('userPassword').value;
                const confirmPassword = document.getElementById('userConfirmPassword').value;
                
                if (password || confirmPassword) {
                    if (password !== confirmPassword) {
                        mostrarNotificacion('Error', 'Las contraseñas no coinciden', 'error');
                        return;
                    }
                }
                
                // Preparar datos para enviar
                const formData = new FormData();
                formData.append('action', 'actualizar');
                formData.append('numeroDocumento', currentUserId);
                formData.append('nombre', document.getElementById('userName').value);
                formData.append('apellido', document.getElementById('userLastName').value);
                formData.append('correo', document.getElementById('userEmail').value);
                formData.append('rol', document.getElementById('userRole').value);
                formData.append('estado', document.getElementById('userStatus').value);
                
                // Solo enviar contraseña si se ha ingresado una nueva
                if (password) {
                    formData.append('contrasena', password);
                }
                
                // Enviar datos al servidor
                fetch('../servicios/usuarios_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        mostrarNotificacion('Éxito', data.mensaje, 'success');
                        closeUserModal();
                        cargarUsuarios(); // Recargar la lista de usuarios
                    } else {
                        mostrarNotificacion('Error', data.mensaje, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNotificacion('Error', 'Ha ocurrido un error al actualizar el usuario', 'error');
                });
            });
        });
    </script>
    
    <!-- Script de validación de autenticación y permisos -->
    <script src="../public/componentes/auth-validation.js"></script>
</body>
</html>