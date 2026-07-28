<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    obtenerEntradas($pdo); break;
    case 'POST':   crearEntrada($pdo); break;
    case 'PUT':    actualizarEntrada($pdo); break;
    case 'DELETE': eliminarEntrada($pdo); break;
    default:       responder(["error" => "Método no permitido"], 405);
}

/**
 * Valida una cédula ecuatoriana usando el algoritmo oficial de dígito
 * verificador (módulo 10). Se valida siempre en el backend, sin confiar
 * en la validación del navegador.
 */
function validarCedulaEcuatoriana(string $cedula): bool {
    if (!preg_match('/^\d{10}$/', $cedula)) return false;

    $digitos = array_map('intval', str_split($cedula));
    $provincia = intval(substr($cedula, 0, 2));
    if ($provincia < 1 || $provincia > 24) return false;

    if ($digitos[2] >= 6) return false; // 0-5: cédula de persona natural

    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $valor = $digitos[$i] * $coeficientes[$i];
        if ($valor > 9) $valor -= 9;
        $suma += $valor;
    }
    $decena = ceil($suma / 10) * 10;
    $digitoVerificador = (int) ($decena - $suma);
    if ($digitoVerificador === 10) $digitoVerificador = 0;

    return $digitoVerificador === $digitos[9];
}

function validarDatosCliente(array $d): array {
    $errores = [];
    if (empty($d['cliente_nombre']) || mb_strlen(trim($d['cliente_nombre'])) < 3) {
        $errores[] = "El nombre del cliente debe tener al menos 3 caracteres.";
    }
    if (empty($d['cliente_documento']) || !validarCedulaEcuatoriana(trim($d['cliente_documento']))) {
        $errores[] = "La cédula ingresada no es una cédula ecuatoriana válida.";
    }
    if (!empty($d['cliente_email']) && !filter_var($d['cliente_email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }
    if (empty($d['id_actuacion']) || !is_numeric($d['id_actuacion'])) {
        $errores[] = "Debes seleccionar una función (actuación).";
    }
    if (empty($d['id_butaca']) || !is_numeric($d['id_butaca'])) {
        $errores[] = "Debes seleccionar una butaca.";
    }
    return $errores;
}

function generarCodigo(): string {
    return strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
}

function consultaBase(): string {
    return "SELECT en.*, a.fecha, a.hora, a.precio_base, a.estado AS estado_actuacion,
                   e.nombre AS nombre_espectaculo,
                   s.nombre AS nombre_sala,
                   b.fila, b.numero, z.nombre AS nombre_zona
            FROM entrada en
            JOIN actuacion a ON a.id_actuacion = en.id_actuacion
            JOIN espectaculo e ON e.id_espectaculo = a.id_espectaculo
            JOIN sala s ON s.id_sala = a.id_sala
            JOIN butaca b ON b.id_butaca = en.id_butaca
            JOIN zona z ON z.id_zona = b.id_zona";
}

function obtenerEntradas(PDO $pdo) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare(consultaBase() . " WHERE en.id_entrada = ?");
        $stmt->execute([intval($_GET['id'])]);
        $fila = $stmt->fetch();
        if (!$fila) responder(["error" => "Entrada no encontrada"], 404);
        responder($fila);
    } elseif (isset($_GET['id_actuacion'])) {
        $stmt = $pdo->prepare(consultaBase() . " WHERE en.id_actuacion = ? ORDER BY en.fecha_compra DESC");
        $stmt->execute([intval($_GET['id_actuacion'])]);
        responder($stmt->fetchAll());
    } else {
        $stmt = $pdo->query(consultaBase() . " ORDER BY en.fecha_compra DESC");
        responder($stmt->fetchAll());
    }
}

function crearEntrada(PDO $pdo) {
    $d = obtenerEntradaJSON();
    $errores = validarDatosCliente($d);
    if ($errores) responder(["errores" => $errores], 400);

    $idActuacion = intval($d['id_actuacion']);
    $idButaca = intval($d['id_butaca']);

    // 1. Verificar que la actuación exista y esté programada
    $stmt = $pdo->prepare("SELECT * FROM actuacion WHERE id_actuacion = ?");
    $stmt->execute([$idActuacion]);
    $actuacion = $stmt->fetch();
    if (!$actuacion) responder(["error" => "La función seleccionada no existe."], 404);
    if ($actuacion['estado'] !== 'programada') {
        responder(["error" => "No se pueden vender entradas para una función cancelada o finalizada."], 400);
    }

    // 2. Verificar que la butaca exista, pertenezca a la sala de la actuación y esté disponible
    $stmt = $pdo->prepare("SELECT b.*, z.multiplicador_precio, z.id_sala
                            FROM butaca b JOIN zona z ON z.id_zona = b.id_zona
                            WHERE b.id_butaca = ?");
    $stmt->execute([$idButaca]);
    $butaca = $stmt->fetch();
    if (!$butaca) responder(["error" => "La butaca seleccionada no existe."], 404);
    if ($butaca['id_sala'] != $actuacion['id_sala']) {
        responder(["error" => "La butaca seleccionada no pertenece a la sala de esta función."], 400);
    }
    if ($butaca['estado'] !== 'disponible') {
        responder(["error" => "La butaca seleccionada está en mantenimiento y no se puede vender."], 400);
    }

    // 3. Verificar que la butaca no esté ya vendida para esta actuación
    $stmt = $pdo->prepare("SELECT id_entrada FROM entrada WHERE id_actuacion = ? AND id_butaca = ? AND estado = 'activa'");
    $stmt->execute([$idActuacion, $idButaca]);
    if ($stmt->fetch()) {
        responder(["error" => "Esa butaca ya fue vendida para esta función. Elige otra."], 409);
    }

    // 4. Calcular precio final = precio_base de la actuación * multiplicador de la zona
    $precioFinal = round(floatval($actuacion['precio_base']) * floatval($butaca['multiplicador_precio']), 2);

    // 5. Generar código único de entrada
    do {
        $codigo = generarCodigo();
        $chk = $pdo->prepare("SELECT id_entrada FROM entrada WHERE codigo = ?");
        $chk->execute([$codigo]);
    } while ($chk->fetch());

    try {
        $stmt = $pdo->prepare("INSERT INTO entrada
            (id_actuacion, id_butaca, cliente_nombre, cliente_documento, cliente_email, precio_final, codigo, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activa')");
        $stmt->execute([
            $idActuacion,
            $idButaca,
            trim($d['cliente_nombre']),
            trim($d['cliente_documento']),
            trim($d['cliente_email'] ?? ''),
            $precioFinal,
            $codigo
        ]);
        responder([
            "mensaje" => "Entrada vendida correctamente",
            "id" => (int)$pdo->lastInsertId(),
            "codigo" => $codigo,
            "precio_final" => $precioFinal
        ], 201);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            responder(["error" => "Esa butaca ya fue vendida para esta función. Elige otra."], 409);
        }
        throw $e;
    }
}

function actualizarEntrada(PDO $pdo) {
    // Solo se permite actualizar datos del cliente o anular la entrada (no cambiar butaca/actuación)
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $d = obtenerEntradaJSON();

    $existe = $pdo->prepare("SELECT * FROM entrada WHERE id_entrada = ?");
    $existe->execute([$id]);
    $entrada = $existe->fetch();
    if (!$entrada) responder(["error" => "Entrada no encontrada"], 404);

    $errores = [];
    if (empty($d['cliente_nombre']) || mb_strlen(trim($d['cliente_nombre'])) < 3) {
        $errores[] = "El nombre del cliente debe tener al menos 3 caracteres.";
    }
    if (empty($d['cliente_documento']) || !validarCedulaEcuatoriana(trim($d['cliente_documento']))) {
        $errores[] = "La cédula ingresada no es una cédula ecuatoriana válida.";
    }
    if (!empty($d['cliente_email']) && !filter_var($d['cliente_email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }
    if (isset($d['estado']) && !in_array($d['estado'], ['activa', 'anulada'])) {
        $errores[] = "Estado de entrada no válido.";
    }
    if ($errores) responder(["errores" => $errores], 400);

    $stmt = $pdo->prepare("UPDATE entrada SET cliente_nombre=?, cliente_documento=?, cliente_email=?, estado=? WHERE id_entrada=?");
    $stmt->execute([
        trim($d['cliente_nombre']),
        trim($d['cliente_documento']),
        trim($d['cliente_email'] ?? ''),
        $d['estado'] ?? $entrada['estado'],
        $id
    ]);
    responder(["mensaje" => "Entrada actualizada correctamente"]);
}

function eliminarEntrada(PDO $pdo) {
    if (!isset($_GET['id'])) responder(["error" => "Falta el parámetro id"], 400);
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM entrada WHERE id_entrada = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) responder(["error" => "Entrada no encontrada"], 404);
    responder(["mensaje" => "Entrada eliminada correctamente. La butaca queda libre para esa función."]);
}
