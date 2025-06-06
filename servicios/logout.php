<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Eliminar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Eliminar la cookie de "recordar usuario" si existe
if (isset($_COOKIE["token_recordar"])) {
    setcookie("token_recordar", "", time() - 3600, "/");
}

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: ../login.html");
exit();
?>