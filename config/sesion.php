<?php
/**
 * Incluir este archivo al INICIO de cada página protegida (antes de
 * cualquier salida HTML). Si no hay una sesión activa, redirige a
 * login.php y detiene la ejecución.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
