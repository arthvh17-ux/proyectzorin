<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

// Leer los datos JSON enviados desde el formulario en dashboard.html
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->nombre) && !empty($data->tipo) && !empty($data->estado)) {
    $nombre = $data->nombre;
    $tipo = $data->tipo;
    $descripcion = !empty($data->descripcion) ? $data->descripcion : '';
    $ubicacion = !empty($data->ubicacion) ? $data->ubicacion : '';
    $estado = $data->estado;

    try {
        $pdo->beginTransaction();

        // 1. Insertar el nuevo activo en la base de datos
        $stmt = $pdo->prepare("INSERT INTO activos (nombre_activo, tipo, descripcion, ubicacion, estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $tipo, $descripcion, $ubicacion, $estado]);
        $id_activo = $pdo->lastInsertId();

        // 2. Registrar el movimiento en la bitácora de auditoría
        // Nota: Asumimos ID 1 del administrador principal o el que tengas en sesión.
        $usuario_accion_id = 1; 
        $detalles = "Se registró el nuevo activo: " . $nombre;
        
        $log = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, detalles) VALUES (?, ?, ?)");
        $log->execute([$usuario_accion_id, "REGISTRO_ACTIVO", $detalles]);

        $pdo->commit();

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Activo registrado exitosamente."]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios (nombre, tipo, estado)."]);
}
?>