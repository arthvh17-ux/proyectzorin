<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Se agregan b.fecha_inicio y b.fecha_limite para que la API las devuelva en el JSON
    $stmt = $pdo->query("SELECT b.id, b.usuario_accion_id, b.accion, b.detalles, 
                                b.fecha_evento, b.fecha_inicio, b.fecha_limite, 
                                u.nombre as usuario_nombre 
                         FROM bitacora_trazabilidad b 
                         JOIN usuarios u ON b.usuario_accion_id = u.id 
                         ORDER BY b.id DESC");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
}
?>