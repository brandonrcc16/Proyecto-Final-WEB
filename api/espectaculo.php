<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerEspectaculos($pdo); break;
    case 'POST':   crearEspectaculo($pdo); break;
    case 'PUT':    actualizarEspectaculo($pdo); break;
    case 'DELETE': eliminarEspectaculo($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

function validarEspectaculo(array $d): array {
    $errores = [];
    if (empty($d['nombre']) || mb_strlen(trim($d['nombre'])) < 3) {
        $errores[] = "El nombre del espectáculo debe tener al menos 3 caracteres.";
    }
    if (empty($d['tipo'])) {
        $errores[] = "Debes indicar el tipo de espectáculo (Concierto, Teatro, Danza, etc.).";
    }
    if (!isset($d['duracion_min']) || !is_numeric($d['duracion_min']) || intval($d['duracion_min']) <= 0) {
        $errores[] = "La duración (minutos) debe ser un número mayor a 0.";
    }
    return $errores;
}

function obtenerEspectaculos(PDO $pdo) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM espectaculo WHERE id_espectaculo = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Espectáculo no encontrado"], 404);
        responder($fila);
    } else {
        $stmt = $pdo->query("SELECT * FROM espectaculo ORDER BY id_espectaculo DESC");
        responder($stmt->fetchAll());
    }
}

function crearEspectaculo(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarEspectaculo($d);
    if ($errores) responder(["errores" => $errores], 400);

    $stmt = $pdo->prepare("INSERT INTO espectaculo (nombre, tipo, descripcion, duracion_min) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        trim($d['nombre']),
        trim($d['tipo']),
        trim($d['descripcion'] ?? ''),
        intval($d['duracion_min'])
    ]);
    responder(["mensaje" => "Espectáculo creado correctamente", "id" => (int)$pdo->lastInsertId()], 201);
}

function actualizarEspectaculo(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $errores = validarEspectaculo($d);
    if ($errores) responder(["errores" => $errores], 400);

    $existe = $pdo->prepare("SELECT id_espectaculo FROM espectaculo WHERE id_espectaculo = ?");
    $existe->execute([$id]);
    if (!$existe->fetch()) responder(["error" => "Espectáculo no encontrado"], 404);

    $stmt = $pdo->prepare("UPDATE espectaculo SET nombre=?, tipo=?, descripcion=?, duracion_min=? WHERE id_espectaculo=?");
    $stmt->execute([
        trim($d['nombre']),
        trim($d['tipo']),
        trim($d['descripcion'] ?? ''),
        intval($d['duracion_min']),
        $id
    ]);
    responder(["mensaje" => "Espectáculo actualizado correctamente"]);
}

function eliminarEspectaculo(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM espectaculo WHERE id_espectaculo = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) responder(["error" => "Espectáculo no encontrado"], 404);
        responder(["mensaje" => "Espectáculo eliminado correctamente"]);
    } catch (PDOException $e) {
        responder(["error" => "No se puede eliminar: el espectáculo tiene actuaciones asociadas."], 409);
    }
}
