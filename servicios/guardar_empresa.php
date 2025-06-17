<?php
session_start();

require_once 'verificar_admin.php';

if (!esAdministrador()) {
    // http_response_code(403);
    echo "Acceso denegado: Se requieren permisos de administrador.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "Solicitud no válida";
    exit;
}

// Conexión a la base de datos
try {
    $db = new PDO('mysql:host=localhost;dbname=arco_bdd', 'usuario', 'contrasena');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Capturar y limpiar los datos del formulario
$nombre = $_POST['companyName'] ?? '';
$nif = $_POST['companyTaxId'] ?? '';
$direccion = $_POST['companyAddress'] ?? '';
$ciudad = $_POST['companyCity'] ?? '';
$telefono = $_POST['companyPhone'] ?? '';
$email = $_POST['companyEmail'] ?? '';

$logoNombre = null;

// Procesar el archivo del logo (si se envió)
if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['companyLogo']['tmp_name'];
    $nombreOriginal = basename($_FILES['companyLogo']['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extPermitidas = ['jpg', 'jpeg', 'png'];

    if (in_array($extension, $extPermitidas)) {
        $logoNombre = uniqid('logo_') . '.' . $extension;
        $destino = '../uploads/' . $logoNombre;

        if (!is_dir('../uploads')) {
            mkdir('../uploads', 0755, true);
        }

        move_uploaded_file($tmpPath, $destino);
    }
}

// Consulta SQL
$sql = "INSERT INTO `empresa` (`nombre`, `nif`, `direccion`, `ciudad`, `telefono`, `email`, `logo`)
        VALUES (:nombre, :nif, :direccion, :ciudad, :telefono, :email, :logo)";

$stmt = $db->prepare($sql);
if($stmt->execute([
    'nombre' => $nombre,
    'nif' => $nif,
    'direccion' => $direccion,
    'ciudad' => $ciudad,
    'telefono' => $telefono,
    'email' => $email,
    'logo' => $logoNombre,
])){
    echo"Guardado";

} else {
    echo"No guardado";
}
    
exit;
