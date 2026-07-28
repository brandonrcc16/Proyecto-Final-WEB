<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerZonas($pdo); break;
    case 'POST':   crearZona($pdo); break;
    case 'PUT':    actualizarZona($pdo); break;
    case 'DELETE': eliminarZona($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

function validarZona(PDO $pdo, array $d): array {
    $errores = [];

    if (empty($d['id_sala']) || !is_numeric($d['id_sala'])) {
        $errores[] = "Debes seleccionar una sala.";
    } else {
        $chk = $pdo->prepare("SELECT id_sala FROM sala WHERE id_sala = ?");
        $chk->execute([intval($d['id_sala'])]);
        if (!$chk->fetch()) $errores[] = "La sala seleccionada no existe.";
    }

    if (empty($d['nombre']) || mb_strlen(trim($d['nombre'])) < 2) {
        $errores[] = "El nombre de la zona debe tener al menos 2 caracteres.";
    }

    if (!isset($d['multiplicador_precio']) || !is_numeric($d['multiplicador_precio']) || floatval($d['multiplicador_precio']) <= 0) {
        $errores[] = "El multiplicador de precio debe ser un número mayor a 0 (ej: 1.00, 1.50, 2.00).";
    }

    return $errores;
}

function obtenerZonas(PDO $pdo) {
    $base = "SELECT z.*, s.nombre AS nombre_sala FROM zona z JOIN sala s ON s.id_sala = z.id_sala";
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($base . " WHERE z.id_zona = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Zona no encontrada"], 404);
        responder($fila);
    } elseif (isset($_GET['id_sala'])) {
        $stmt = $pdo->prepare($base . " WHERE z.id_sala = ? ORDER BY z.nombre");
        $stmt->execute([intval($_GET['id_sala'])]);
        responder($stmt->fetchAll());
    } else {
        $stmt = $pdo->query($base . " ORDER BY z.id_zona DESC");
        responder($stmt->fetchAll());
    }
}

function crearZona(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarZona($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    $stmt = $pdo->prepare("INSERT INTO zona (id_sala, nombre, multiplicador_precio) VALUES (?, ?, ?)");
    $stmt->execute([
        intval($d['id_sala']),
        trim($d['nombre']),
        floatval($d['multiplicador_precio'])
    ]);
    responder(["mensaje" => "Zona creada correctamente", "id" => (int)$pdo->lastInsertId()], 201);
}

function actualizarZona(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $errores = validarZona($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    $existe = $pdo->prepare("SELECT id_zona FROM zona WHERE id_zona = ?");
    $existe->execute([$id]);
    if (!$existe->fetch()) responder(["error" => "Zona no encontrada"], 404);

    $stmt = $pdo->prepare("UPDATE zona SET id_sala=?, nombre=?, multiplicador_precio=? WHERE id_zona=?");
    $stmt->execute([
        intval($d['id_sala']),
        trim($d['nombre']),
        floatval($d['multiplicador_precio']),
        $id
    ]);
    responder(["mensaje" => "Zona actualizada correctamente"]);
}

function eliminarZona(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM zona WHERE id_zona = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) responder(["error" => "Zona no encontrada"], 404);
        responder(["mensaje" => "Zona eliminada correctamente"]);
    } catch (PDOException $e) {
        responder(["error" => "No se puede eliminar: la zona tiene butacas asociadas."], 409);
    }
}
