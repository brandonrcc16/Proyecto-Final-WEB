<?php
/**
 * EJECUTAR UNA SOLA VEZ desde el navegador:
 *   http://localhost/palacio_festivales/crear_admin.php
 *
 * Crea el usuario administrador (usuario: admin / contraseña: admin123)
 * con la contraseña hasheada usando password_hash() de PHP.
 * Puedes borrar este archivo después de usarlo (opcional, no es obligatorio
 * porque el script no vuelve a crear el usuario si ya existe).
 */
require_once __DIR__ . '/config/conexion.php';

$usuario = 'admin';
$passwordPlano = 'admin123';
$nombreCompleto = 'Administrador del Palacio';

$stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE usuario = ?");
$stmt->execute([$usuario]);

header('Content-Type: text/html; charset=UTF-8');

if ($stmt->fetch()) {
    echo "<p>El usuario <strong>{$usuario}</strong> ya existe. No se creó nada nuevo.</p>";
    echo "<p><a href='login.php'>Ir a iniciar sesión</a></p>";
    exit();
}

$hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuario (usuario, password_hash, nombre_completo) VALUES (?, ?, ?)");
$stmt->execute([$usuario, $hash, $nombreCompleto]);

echo "<p>Usuario administrador creado correctamente.</p>";
echo "<p>Usuario: <strong>{$usuario}</strong><br>Contraseña: <strong>{$passwordPlano}</strong></p>";
echo "<p><a href='login.php'>Ir a iniciar sesión</a></p>";
