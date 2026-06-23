<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Obtiene todo el historial de trazabilidad ordenado por el evento más reciente
    $stmt = $pdo->query("SELECT b.*, u.nombre as usuario_nombre 
                         FROM bitacora_trazabilidad b 
                         JOIN usuarios u ON b.usuario_accion_id = u.id 
                         ORDER BY b.fecha_evento DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
}
?>