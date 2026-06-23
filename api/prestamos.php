<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 1. REGISTRAR PRÉSTAMO (Método POST)
// ==========================================
if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->usuario_id) && !empty($data->activo_id) && !empty($data->fecha_limite)) {
        $usuario_id = $data->usuario_id;
        $activo_id = $data->activo_id;
        $fecha_limite = $data->fecha_limite;

        // Iniciar transacción para asegurar consistencia
        $pdo->beginTransaction();

        // A. Crear el registro del préstamo
        $stmt = $pdo->prepare("INSERT INTO movimientos_prestamos (usuario_id, activo_id, fecha_limite, estado_prestamo) VALUES (?, ?, ?, 'activo')");
        $stmt->execute([$usuario_id, $activo_id, $fecha_limite]);

        // B. Actualizar el estado del activo a 'prestado'
        $stmtUpdate = $pdo->prepare("UPDATE activos SET estado = 'prestado' WHERE id = ?");
        $stmtUpdate->execute([$activo_id]);

        // C. Registrar en la Bitácora de Trazabilidad
        $accion = 'PRESTAMO_REGISTRADO';
        $descripcion_bitacora = "El usuario ID {$usuario_id} solicitó el activo ID {$activo_id}.";
        
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, descripcion) VALUES (?, ?, ?)");
        $stmtBitacora->execute([$usuario_id, $accion, $descripcion_bitacora]);

        $pdo->commit();

        echo json_encode(["status" => "success", "message" => "Préstamo registrado exitosamente y auditado."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos para el préstamo."]);
    }
}

// ==========================================
// 2. REGISTRAR DEVOLUCIÓN (Método PUT)
// ==========================================
elseif ($method == 'PUT') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->prestamo_id) && !empty($data->activo_id)) {
        $prestamo_id = $data->prestamo_id;
        $activo_id = $data->activo_id;

        $pdo->beginTransaction();

        // A. Actualizar fecha de devolución y estado del movimiento
        $stmt = $pdo->prepare("UPDATE movimientos_prestamos SET fecha_devolucion = NOW(), estado_prestamo = 'devuelto' WHERE id = ?");
        $stmt->execute([$prestamo_id]);

        // B. Regresar el activo a estado 'disponible'
        $stmtUpdate = $pdo->prepare("UPDATE activos SET estado = 'disponible' WHERE id = ?");
        $stmtUpdate->execute([$activo_id]);

        // C. Registrar devolución en la Bitácora de Trazabilidad
        $accion = 'DEVOLUCION_REGISTRADA';
        $descripcion_bitacora = "Se devolvió y liberó el activo ID {$activo_id}.";
        
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, descripcion) VALUES (1, ?, ?)");
        $stmtBitacora->execute([$accion, $descripcion_bitacora]);

        $pdo->commit();

        echo json_encode(["status" => "success", "message" => "Devolución procesada correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan ID de préstamo y ID de activo."]);
    }
}
?>