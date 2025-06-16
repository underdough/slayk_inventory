<?php
// Archivo de prueba para verificar el sistema de administrador
session_start();

echo "<h2>Estado de la Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Pruebas de Verificación:</h2>";

// Incluir el verificador de admin
require_once 'servicios/verificar_admin.php';

echo "<p><strong>¿Es administrador?:</strong> " . (esAdministrador() ? 'SÍ' : 'NO') . "</p>";

$usuario = obtenerInfoUsuario();
if ($usuario) {
    echo "<h3>Información del Usuario:</h3>";
    echo "<ul>";
    echo "<li>ID: " . $usuario['id'] . "</li>";
    echo "<li>Nombre: " . $usuario['nombre'] . "</li>";
    echo "<li>Rol: " . $usuario['rol'] . "</li>";
    echo "<li>Es Admin: " . ($usuario['es_admin'] ? 'SÍ' : 'NO') . "</li>";
    echo "</ul>";
} else {
    echo "<p><strong>No hay usuario autenticado</strong></p>";
}

echo "<br><a href='login.html'>Ir al Login</a>";
echo " | <a href='vistas/dashboard.html'>Ir al Dashboard</a>";
echo " | <a href='vistas/usuarios.php'>Ir a Usuarios (Solo Admin)</a>";
?>