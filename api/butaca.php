<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerButacas($pdo); break;
    case 'POST':   crearButaca($pdo); break;
    case 'PUT':    actualizarButaca($pdo); break;
    case 'DELETE': eliminarButaca($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

function validarButaca(PDO $pdo, array $d): array {
    $errores = [];

    if (empty($d['id_zona']) || !is_numeric($d['id_zona'])) {
        $errores[] = "Debes seleccionar una zona.";
    } else {
        $chk = $pdo->prepare("SELECT id_zona FROM zona WHERE id_zona = ?");
        $chk->execute([intval($d['id_zona'])]);
        if (!$chk->fetch()) $errores[] = "La zona seleccionada no existe.";
    }

    if (empty($d['fila']) || mb_strlen(trim($d['fila'])) > 5) {
        $errores[] = "La fila es obligatoria (máx. 5 caracteres).";
    }

    if (!isset($d['numero']) || !is_numeric($d['numero']) || intval($d['numero']) <= 0) {
        $errores[] = "El número de butaca debe ser un entero mayor a 0.";
    }

    if (isset($d['estado']) && !in_array($d['estado'], ['disponible', 'mantenimiento'])) {
        $errores[] = "Estado de butaca no válido.";
    }

    return $errores;
}

function obtenerButacas(PDO $pdo) {
    $base = "SELECT b.*, z.nombre AS nombre_zona, z.id_sala, s.nombre AS nombre_sala
              FROM butaca b
              JOIN zona z ON z.id_zona = b.id_zona
              JOIN sala s ON s.id_sala = z.id_sala";

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($base . " WHERE b.id_butaca = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Butaca no encontrada"], 404);
        responder($fila);
        return;
    }

    // Modo especial: butacas DISPONIBLES para una actuación concreta
    // (excluye butacas en mantenimiento y butacas ya vendidas para esa actuación)
    if (isset($_GET['disponibles']) && isset($_GET['id_actuacion'])) {
        $idActuacion = intval($_GET['id_actuacion']);

        $act = $pdo->prepare("SELECT id_sala FROM actuacion WHERE id_actuacion = ?");
        $act->execute([$idActuacion]);
        $actuacion = $act->fetch();
        if (!$actuacion) responder(["error" => "Actuación no encontrada"], 404);

        $sql = $base . " WHERE z.id_sala = ? AND b.estado = 'disponible'
                AND b.id_butaca NOT IN (
                    SELECT id_butaca FROM entrada
                    WHERE id_actuacion = ? AND estado = 'activa'
                )";
        $params = [$actuacion['id_sala'], $idActuacion];

        if (isset($_GET['id_zona']) && $_GET['id_zona'] !== '') {
            $sql .= " AND b.id_zona = ?";
            $params[] = intval($_GET['id_zona']);
        }
        $sql .= " ORDER BY z.nombre, b.fila, b.numero";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        responder($stmt->fetchAll());
        return;
    }

    if (isset($_GET['id_zona'])) {
        $stmt = $pdo->prepare($base . " WHERE b.id_zona = ? ORDER BY b.fila, b.numero");
        $stmt->execute([intval($_GET['id_zona'])]);
        responder($stmt->fetchAll());
        return;
    }

    $stmt = $pdo->query($base . " ORDER BY b.id_butaca DESC");
    responder($stmt->fetchAll());
}

function crearButaca(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarButaca($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    try {
        $stmt = $pdo->prepare("INSERT INTO butaca (id_zona, fila, numero, estado) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            intval($d['id_zona']),
            strtoupper(trim($d['fila'])),
            intval($d['numero']),
            $d['estado'] ?? 'disponible'
        ]);
        responder(["mensaje" => "Butaca creada correctamente", "id" => (int)$pdo->lastInsertId()], 201);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            responder(["error" => "Ya existe una butaca con esa fila y número en la zona seleccionada."], 409);
        }
        throw $e;
    }
}

function actualizarButaca(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $errores = validarButaca($pdo, $d);
    if ($errores) responder(["errores" => $errores], 400);

    $existe = $pdo->prepare("SELECT id_butaca FROM butaca WHERE id_butaca = ?");
    $existe->execute([$id]);
    if (!$existe->fetch()) responder(["error" => "Butaca no encontrada"], 404);

    try {
        $stmt = $pdo->prepare("UPDATE butaca SET id_zona=?, fila=?, numero=?, estado=? WHERE id_butaca=?");
        $stmt->execute([
            intval($d['id_zona']),
            strtoupper(trim($d['fila'])),
            intval($d['numero']),
            $d['estado'] ?? 'disponible',
            $id
        ]);
        responder(["mensaje" => "Butaca actualizada correctamente"]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            responder(["error" => "Ya existe una butaca con esa fila y número en la zona seleccionada."], 409);
        }
        throw $e;
    }
}

function eliminarButaca(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM butaca WHERE id_butaca = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) responder(["error" => "Butaca no encontrada"], 404);
        responder(["mensaje" => "Butaca eliminada correctamente"]);
    } catch (PDOException $e) {
        responder(["error" => "No se puede eliminar: la butaca tiene entradas vendidas."], 409);
    }
}
