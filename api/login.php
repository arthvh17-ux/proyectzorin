<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->usuario) && !empty($data->password)) {
    $usuario = $data->usuario;
    $password = $data->password;

    // Buscar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])) {
        // Autenticación exitosa
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Inicio de sesión exitoso.",
            "usuario_id" => $user['id'],
            "usuario" => $user['usuario'],
            "rol" => $user['rol']
        ]);
    } else {
        // Credenciales incorrectas
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Usuario o contraseña incorrectos."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
}
?>