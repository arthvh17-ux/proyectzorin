<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->id) && isset($data->estado)) {
    $id = $data->id;
    $nuevo_estado = $data->estado;

    try {
        $pdo->beginTransaction();

        // 1. Actualizamos el estado del activo
        $stmt = $pdo->prepare("UPDATE activos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);

        // 2. Registramos la acción en la bitácora de trazabilidad (Auditoría)
        // Nota: Por defecto se asume usuario_accion_id = 1 (Admin general), adáptalo si usas sesiones.
        $usuario_accion_id = 1; 
        $detalle_accion = "El activo ID " . $id . " cambió su estado a: " . strtoupper($nuevo_estado);
        
        $log = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, detalles) VALUES (?, ?, ?)");
        $log->execute([$usuario_accion_id, "ACTUALIZACION_ESTADO", $detalle_accion]);

        $pdo->commit();

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Estado actualizado correctamente."]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error de base de datos: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
}
?>