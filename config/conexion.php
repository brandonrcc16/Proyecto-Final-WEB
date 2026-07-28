<?php
/**
 * Conexión a la base de datos usando PDO.
 * Proyecto: Palacio de Festivales
 *
 * Configurado para XAMPP (usuario root sin contraseña por defecto).
 * Si tu instalación de XAMPP tiene contraseña para 'root', cámbiala abajo.
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Responder inmediatamente a peticiones preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$DB_HOST = "localhost";
$DB_NAME = "palacio_festivales";
$DB_USER = "root";
$DB_PASS = "";
$DB_CHARSET = "utf8mb4";

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $opciones);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "No se pudo conectar a la base de datos. Verifica que XAMPP (MySQL) esté iniciado y que la base 'palacio_festivales' exista.",
        "detalle" => $e->getMessage()
    ]);
    exit();
}

/**
 * Lee y decodifica el cuerpo JSON de la petición.
 */
function obtenerEntradaJSON(): array {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * Responde con JSON y termina la ejecución.
 */
function responder($data, int $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
