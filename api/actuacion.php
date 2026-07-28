<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerActuaciones($pdo); break;
    case 'POST':   crearActuacion($pdo); break;
    case 'PUT':    actualizarActuacion($pdo); break;
    case 'DELETE': eliminarActuacion($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

function validarActuacion(PDO $pdo, array $d): array {
    $errores = [];

    if (empty($d['id_espectaculo']) || !is_numeric($d['id_espectaculo'])) {
        $errores[] = "Debes seleccionar un espectáculo.";
    } else {
        $chk = $pdo->prepare("SELECT id_espectaculo FROM espectaculo WHERE id_espectaculo = ?");
        $chk->execute([intval($d['id_espectaculo'])]);
        if (!$chk->fetch()) $errores[] = "El espectáculo seleccionado no existe.";
    }

    if (empty($d['id_sala']) || !is_numeric($d['id_sala'])) {
        $errores[] = "Debes seleccionar una sala.";
    } else {
        $chk = $pdo->prepare("SELECT id_sala FROM sala WHERE id_sala = ?");
        $chk->execute([intval($d['id_sala'])]);
        if (!$chk->fetch()) $errores[] = "La sala seleccionada no existe.";
    }

    if (empty($d['fecha']) || !DateTime::createFromFormat('Y-m-d', $d['fecha'])) {
        $errores[] = "La fecha no es válida (formato requerido AAAA-MM-DD).";
    } elseif ($d['fecha'] < date('Y-m-d')) {
        $errores[] = "La fecha de la función no puede ser un día que ya pasó.";
    }

    if (empty($d['hora']) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $d['hora'])) {
        $errores[] = "La hora no es válida (formato requerido HH:MM).";
    }

    if (!isset($d['precio_base']) || !is_numeric($d['precio_base']) || floatval($d['precio_base']) <= 0) {
        $errores[] = "El precio base debe ser un número mayor a 0.";
    }

    if (isset($d['estado']) && !in_array($d['estado'], ['programada', 'cancelada', 'finalizada'])) {
        $errores[] = "Estado de actuación no válido.";
    }

    return $errores;
}

function consultaBase(): string {
    return "SELECT a.*, e.nombre AS nombre_espectaculo, e.tipo AS tipo_espectaculo,
                   s.nombre AS nombre_sala
            FROM actuacion a
            JOIN espectaculo e ON e.id_espectaculo = a.id_espectaculo
            JOIN sala s ON s.id_sala = a.id_sala";
}

function obtenerActuaciones(PDO $pdo) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare(consultaBase() . " WHERE a.id_actuacion = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Actuación no encontrada"], 404);
        responder($fila);
    } elseif (isset($_GET['estado'])) {
        $stmt = $pdo->prepare(consultaBase() . " WHERE a.estado = ? ORDER BY a.fecha, a.hora");
        $stmt->execute([$_GET['estado']]);
        responder($stmt->fetchAll());
    } else {
        $stmt = $pdo->query(consultaBase() . " ORDER BY a.fecha DESC, a.hora DESC");
        responder($stmt->fetchAll());
    }
}

function crearActuacion(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarActuacion($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    $stmt = $pdo->prepare("INSERT INTO actuacion (id_espectaculo, id_sala, fecha, hora, precio_base, estado)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        intval($d['id_espectaculo']),
        intval($d['id_sala']),
        $d['fecha'],
        $d['hora'],
        floatval($d['precio_base']),
        $d['estado'] ?? 'programada'
    ]);
    responder(["mensaje" => "Actuación creada correctamente", "id" => (int)$pdo->lastInsertId()], 201);
}

function actualizarActuacion(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $errores = validarActuacion($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    $existe = $pdo->prepare("SELECT id_actuacion FROM actuacion WHERE id_actuacion = ?");
    $existe->execute([$id]);
    if (!$existe->fetch()) responder(["error" => "Actuación no encontrada"], 404);

    $stmt = $pdo->prepare("UPDATE actuacion SET id_espectaculo=?, id_sala=?, fecha=?, hora=?, precio_base=?, estado=? WHERE id_actuacion=?");
    $stmt->execute([
        intval($d['id_espectaculo']),
        intval($d['id_sala']),
        $d['fecha'],
        $d['hora'],
        floatval($d['precio_base']),
        $d['estado'] ?? 'programada',
        $id
    ]);
    responder(["mensaje" => "Actuación actualizada correctamente"]);
}

function eliminarActuacion(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM actuacion WHERE id_actuacion = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) responder(["error" => "Actuación no encontrada"], 404);
        responder(["mensaje" => "Actuación eliminada correctamente"]);
    } catch (PDOException $e) {
        responder(["error" => "No se puede eliminar: la actuación tiene entradas vendidas."], 409);
    }
}
