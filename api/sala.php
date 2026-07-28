<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerSalas($pdo); break;
    case 'POST':   crearSala($pdo); break;
    case 'PUT':    actualizarSala($pdo); break;
    case 'DELETE': eliminarSala($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

function validarSala(array $d): array {
    $errores = [];
    if (empty($d['nombre']) || mb_strlen(trim($d['nombre'])) < 3) {
        $errores[] = "El nombre de la sala debe tener al menos 3 caracteres.";
    }
    if (!isset($d['capacidad']) || !is_numeric($d['capacidad']) || intval($d['capacidad']) <= 0) {
        $errores[] = "La capacidad debe ser un número entero mayor a 0.";
    }
    if (isset($d['estado']) && !in_array($d['estado'], ['activa', 'inactiva'])) {
        $errores[] = "El estado debe ser 'activa' o 'inactiva'.";
    }
    return $errores;
}

function obtenerSalas(PDO $pdo) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM sala WHERE id_sala = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Sala no encontrada"], 404);
        responder($fila);
    } else {
        $stmt = $pdo->query("SELECT * FROM sala ORDER BY id_sala DESC");
        responder($stmt->fetchAll());
    }
}

function crearSala(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarSala($d);
    if ($errores) responder(["errores" => $errores], 400);

    $stmt = $pdo->prepare("INSERT INTO sala (nombre, capacidad, ubicacion, descripcion, estado)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        trim($d['nombre']),
        intval($d['capacidad']),
        trim($d['ubicacion'] ?? ''),
        trim($d['descripcion'] ?? ''),
        $d['estado'] ?? 'activa'
    ]);
    responder(["mensaje" => "Sala creada correctamente", "id" => (int)$pdo->lastInsertId()], 201);
}

function actualizarSala(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $errores = validarSala($d);
    if ($errores) responder(["errores" => $errores], 400);

    $existe = $pdo->prepare("SELECT id_sala FROM sala WHERE id_sala = ?");
    $existe->execute([$id]);
    if (!$existe->fetch()) responder(["error" => "Sala no encontrada"], 404);

    $stmt = $pdo->prepare("UPDATE sala SET nombre=?, capacidad=?, ubicacion=?, descripcion=?, estado=? WHERE id_sala=?");
    $stmt->execute([
        trim($d['nombre']),
        intval($d['capacidad']),
        trim($d['ubicacion'] ?? ''),
        trim($d['descripcion'] ?? ''),
        $d['estado'] ?? 'activa',
        $id
    ]);
    responder(["mensaje" => "Sala actualizada correctamente"]);
}

function eliminarSala(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM sala WHERE id_sala = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) responder(["error" => "Sala no encontrada"], 404);
        responder(["mensaje" => "Sala eliminada correctamente"]);
    } catch (PDOException $e) {
        responder(["error" => "No se puede eliminar: la sala tiene zonas o actuaciones asociadas."], 409);
    }
}
