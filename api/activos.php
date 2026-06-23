<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 1. OBTENER ACTIVOS (Método GET)
// ==========================================
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Consultar un activo específico
        $stmt = $pdo->prepare("SELECT * FROM activos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($data ? $data : ["message" => "Activo no encontrado"]);
    } else {
        // Listar todos los activos
        $stmt = $pdo->query("SELECT * FROM activos ORDER BY id DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    }
}

// ==========================================
// 2. CREAR ACTIVO Y AUDITORÍA (Método POST)
// ==========================================
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->nombre_activo) && !empty($data->tipo)) {
        // Validación básica
        $nombre = $data->nombre_activo;
        $tipo = $data->tipo;
        $descripcion = isset($data->descripcion) ? $data->descripcion : '';
        $estado = isset($data->estado) ? $data->estado : 'disponible';

        // Insertar activo
        $stmt = $pdo->prepare("INSERT INTO activos (nombre_activo, tipo, estado, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $tipo, $estado, $descripcion]);

        // Registrar en la Bitácora de Trazabilidad (Auditoría Avanzada)
        // Nota: Asumimos el usuario ID 1 (Administrador) para el ejemplo de auditoría
        $usuario_accion_id = 1; 
        $accion = 'REGISTRO_ACTIVO';
        $descripcion_bitacora = "Se registró el nuevo activo: " . $nombre;
        
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, descripcion) VALUES (?, ?, ?)");
        $stmtBitacora->execute([$usuario_accion_id, $accion, $descripcion_bitacora]);

        echo json_encode(["status" => "success", "message" => "Activo registrado correctamente y guardado en auditoría."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Datos incompletos (nombre y tipo son obligatorios)."]);
    }
}

// ==========================================
// 3. ACTUALIZAR ACTIVO (Método PUT)
// ==========================================
elseif ($method == 'PUT') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->estado)) {
        $id = $data->id;
        $nuevoEstado = $data->estado;

        // Actualizar estado del activo
        $stmt = $pdo->prepare("UPDATE activos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevoEstado, $id]);

        // Registrar acción en la Bitácora de Auditoría
        $usuario_accion_id = 1; // Administrador
        $accion = 'ACTUALIZACION_ESTADO_ACTIVO';
        $descripcion_bitacora = "El activo ID: " . $id . " cambió su estado a: " . $nuevoEstado;
        
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, descripcion) VALUES (?, ?, ?)");
        $stmtBitacora->execute([$usuario_accion_id, $accion, $descripcion_bitacora]);

        echo json_encode(["status" => "success", "message" => "Activo actualizado y auditado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan parámetros (id y nuevo estado)."]);
    }
}
?>