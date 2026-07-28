<?php
/**
 * Incluir este archivo en cada endpoint de api/ (después de conexion.php).
 * Si no hay sesión activa, corta la petición con un error 401 en JSON,
 * en lugar de redirigir (esto es una API, no una página HTML).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Debes iniciar sesión para realizar esta acción."]);
    exit();
}
