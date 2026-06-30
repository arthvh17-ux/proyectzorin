<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

// Leer los datos enviados desde registro.html
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->nombre) && !empty($data->usuario) && !empty($data->password) && !empty($data->rol)) {
    $nombre = $data->nombre;
    $usuario = $data->usuario;
    $password = $data->password;
    $rol = $data->rol;

    // Encriptar la contraseña de forma segura para que sea compatible con password_verify
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Insertar en la base de datos usando la columna 'usuario'
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $usuario, $password_hash, $rol]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Usuario registrado exitosamente."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "El usuario ya existe o error en la base de datos."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
}
?>