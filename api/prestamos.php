<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Respuesta inmediata para la solicitud de verificación del navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 1. REGISTRAR PRÉSTAMO (Método POST)
// ==========================================
if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->usuario_id) && !empty($data->activo_id) && !empty($data->fecha_inicio) && !empty($data->fecha_limite)) {
        $usuario_id = $data->usuario_id;
        $activo_id = $data->activo_id;
        $fecha_inicio = $data->fecha_inicio; 
        $fecha_limite = $data->fecha_limite;
        
        $descripcion = !empty($data->descripcion) ? $data->descripcion : '';
        
       $stmtUbicacion = $pdo->prepare(" SELECT ubicacion FROM activos WHERE id = ?");

        $stmtUbicacion->execute([$activo_id]);

        $activo = $stmtUbicacion->fetch(PDO::FETCH_ASSOC);

        $ubicacion = $activo['ubicacion'] ?? 'Sin ubicación';
        try {
            $pdo->beginTransaction();

            // A. Insertar en movimientos_prestamos
            $stmt = $pdo->prepare("INSERT INTO movimientos_prestamos (usuario_id, activo_id, descripcion, fecha_limite, estado_prestamo) VALUES (?, ?, ?, ?, 'activo')");
            $stmt->execute([$usuario_id, $activo_id, $descripcion, $fecha_limite]);

            // B. Actualizar el estado del activo a 'prestado'
            $stmtUpdate = $pdo->prepare("UPDATE activos SET estado = 'prestado' WHERE id = ?");
            $stmtUpdate->execute([$activo_id]);

            // C. Registrar en la Bitácora de Trazabilidad (se incluye la ubicación en el texto descriptivo)
            $accion = 'PRESTAMO_REGISTRADO';
            $descripcion_bitacora = "El usuario ID {$usuario_id} solicitó el activo ID {$activo_id}. Ubicación: {$ubicacion}.";
            
            $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, detalles, fecha_evento, fecha_inicio, fecha_limite) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtBitacora->execute([$usuario_id, $accion, $descripcion_bitacora, $fecha_inicio, $fecha_inicio, $fecha_limite]);
            
            $pdo->commit();

            echo json_encode(["status" => "success", "message" => "Préstamo registrado exitosamente y auditado."]);

        } catch (Exception $e) {
            // Si ocurre cualquier error en las consultas, se revierten los cambios
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(["status" => "error", "message" => "Error al procesar la base de datos: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para el préstamo."]);
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
        
        // ADAPTACIÓN: Obtenemos la fecha tipo datetime-local pasada desde JavaScript, si no, toma la del servidor por defecto
        $fecha_devolucion = date('Y-m-d H:i:s');
        try {
            $pdo->beginTransaction();

            // A. Actualizar fecha de devolución y estado del movimiento usando la fecha exacta de frontend
            $stmt = $pdo->prepare("UPDATE movimientos_prestamos SET fecha_devolucion = ?, estado_prestamo = 'devuelto' WHERE id = ?");
            $stmt->execute([$fecha_devolucion, $prestamo_id]);

            // B. Regresar el activo a estado 'disponible'
            $stmtUpdate = $pdo->prepare("UPDATE activos SET estado = 'disponible' WHERE id = ?");
            $stmtUpdate->execute([$activo_id]);

            // C. Registrar devolución en la Bitácora de Trazabilidad (se pasa la variable formateada $fecha_devolucion)
            $accion = 'DEVOLUCION_REGISTRADA';
            $descripcion_bitacora = "Se devolvió y liberó el activo ID {$activo_id}.";
            
            $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, detalles, fecha_evento, fecha_inicio, fecha_limite) VALUES (1, ?, ?, ?, NULL, NULL)");
            $stmtBitacora->execute([$accion, $descripcion_bitacora, $fecha_devolucion]);

            $pdo->commit();

            echo json_encode(["status" => "success", "message" => "Devolución procesada correctamente."]);
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(["status" => "error", "message" => "Error al procesar la devolución: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan ID de préstamo y ID de activo."]);
    }
}
?>