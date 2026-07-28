<?php
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'POST') {
    responder(["error" => "Método no permitido"], 405);
}

$d = obtenerEntradaJSON();

$usuario = trim($d['usuario'] ?? '');
$password = (string) ($d['password'] ?? '');

if ($usuario === '' || $password === '') {
    responder(["error" => "Debes ingresar usuario y contraseña."], 400);
}

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario = ?");
$stmt->execute([$usuario]);
$fila = $stmt->fetch();

if (!$fila || !password_verify($password, $fila['password_hash'])) {
    responder(["error" => "Usuario o contraseña incorrectos."], 401);
}

// Regenerar el id de sesión al iniciar sesión 
session_regenerate_id(true);

$_SESSION['usuario_id'] = $fila['id_usuario'];
$_SESSION['usuario_nombre'] = $fila['nombre_completo'];

responder([
    "mensaje" => "Bienvenido, " . $fila['nombre_completo'],
    "usuario" => $fila['nombre_completo']
]);
