<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Consulta todos los usuarios de tu base de datos
    $stmt = $pdo->query("SELECT id, nombre, usuario FROM usuarios ORDER BY nombre ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
}
?>