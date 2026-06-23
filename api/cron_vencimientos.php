<?php
require_once 'conexion.php';

try {
    // A. Actualizar a 'vencido' los préstamos cuya fecha límite sea menor al momento actual y sigan activos
    $stmt = $pdo->prepare("UPDATE movimientos_prestamos SET estado_prestamo = 'vencido' WHERE fecha_limite < NOW() AND estado_prestamo = 'activo'");
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        // B. Auditar automáticamente en la bitácora
        $stmtBitacora = $pdo->query("INSERT INTO bitacora_trazabilidad (usuario_accion_id, accion, descripcion) 
                                     VALUES (1, 'CRON_PRESTAMOS_VENCIDOS', 'Se detectaron y auditaron $rowCount préstamos vencidos automáticamente.')");
        echo " Tareas CRON ejecutadas: $rowCount préstamos actualizados a vencidos.";
    } else {
        echo " No hay préstamos que vencer.";
    }

} catch (PDOException $e) {
    echo "Error en tarea CRON: " . $e->getMessage();
}
?>