<?php
$host = 'db'; // Nombre del servicio en tu docker-compose.yml
$dbname = 'sistema_activos';
$username = 'root';
$password = 'rootpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conexión exitosa a la base de datos"; // Descomentar solo para probar
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>