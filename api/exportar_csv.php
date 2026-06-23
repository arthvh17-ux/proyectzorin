<?php
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=reporte_activos_" . date('Y-m-d') . ".csv");

require_once 'conexion.php';

$output = fopen("php://output", "w");

// Encabezados del archivo CSV
fputcsv($output, ['ID', 'Nombre Activo', 'Tipo', 'Estado', 'Descripción', 'Fecha de Registro']);

// Obtener inventario de activos
$stmt = $pdo->query("SELECT * FROM activos ORDER BY id ASC");
$activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Llenar datos en el CSV
foreach ($activos as $activo) {
    fputcsv($output, [
        $activo['id'],
        $activo['nombre_activo'],
        $activo['tipo'],
        $activo['estado'],
        $activo['descripcion'],
        $activo['created_at']
    ]);
}

fclose($output);
exit;
?>