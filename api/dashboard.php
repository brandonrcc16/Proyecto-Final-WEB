<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/sesion_api.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'GET') {
    responder(["error" => "Método no permitido"], 405);
}

// ---------- Totales generales (solo entradas activas, no anuladas) ----------
$stmt = $pdo->query("SELECT
        COUNT(*) AS total_entradas,
        COALESCE(SUM(precio_final), 0) AS ingresos_totales
    FROM entrada
    WHERE estado = 'activa'");
$totales = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS total_anuladas FROM entrada WHERE estado = 'anulada'");
$anuladas = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS total_actuaciones FROM actuacion WHERE estado = 'programada'");
$funcionesProgramadas = $stmt->fetch();

// ---------- Ocupación por función (actuación) ----------
// total_butacas: butacas físicas de la sala de esa función (sin contar mantenimiento)
// vendidas: entradas activas para esa función
$sql = "SELECT
            a.id_actuacion,
            e.nombre AS nombre_espectaculo,
            s.nombre AS nombre_sala,
            a.fecha,
            a.hora,
            a.estado,
            a.precio_base,
            (SELECT COUNT(*) FROM butaca b
                JOIN zona z ON z.id_zona = b.id_zona
                WHERE z.id_sala = a.id_sala AND b.estado = 'disponible') AS total_butacas,
            (SELECT COUNT(*) FROM entrada en
                WHERE en.id_actuacion = a.id_actuacion AND en.estado = 'activa') AS vendidas,
            (SELECT COALESCE(SUM(en.precio_final), 0) FROM entrada en
                WHERE en.id_actuacion = a.id_actuacion AND en.estado = 'activa') AS ingresos_funcion
        FROM actuacion a
        JOIN espectaculo e ON e.id_espectaculo = a.id_espectaculo
        JOIN sala s ON s.id_sala = a.id_sala
        ORDER BY a.fecha, a.hora";

$stmt = $pdo->query($sql);
$funciones = $stmt->fetchAll();

foreach ($funciones as &$f) {
    $totalButacas = (int) $f['total_butacas'];
    $vendidas = (int) $f['vendidas'];
    $f['ocupacion_pct'] = $totalButacas > 0 ? round(($vendidas / $totalButacas) * 100, 1) : 0.0;
}
unset($f);

responder([
    "total_entradas"       => (int) $totales['total_entradas'],
    "ingresos_totales"     => (float) $totales['ingresos_totales'],
    "total_anuladas"       => (int) $anuladas['total_anuladas'],
    "funciones_programadas"=> (int) $funcionesProgramadas['total_actuaciones'],
    "funciones"            => $funciones
]);
