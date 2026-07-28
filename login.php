<?php
// Página pública: si ya hay sesión iniciada, mandamos directo al inicio.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar sesión · Palacio de Festivales</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="container" style="max-width: 420px;">

    <div class="text-center mb-4">
      <h1 class="texto-oro marquesina" style="font-size:1.9rem;">Palacio de Festivales</h1>
      <p class="texto-tenue mb-0">Inicia sesión para administrar el sistema</p>
    </div>

    <div class="panel">
      <div id="alertaLogin"></div>

      <form id="formLogin" novalidate>
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input type="text" class="form-control" id="loginUsuario" placeholder="admin" required autofocus>
          <div class="invalid-feedback">Ingresa tu usuario.</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="loginPassword" placeholder="••••••••" required>
          <div class="invalid-feedback">Ingresa tu contraseña.</div>
        </div>

        <button type="submit" class="btn btn-oro w-100">
          <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
        </button>
      </form>
    </div>

    <p class="text-center texto-tenue small mt-4">
      Palacio de Festivales
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formLogin').addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const payload = {
    usuario: document.getElementById('loginUsuario').value.trim(),
    password: document.getElementById('loginPassword').value
  };

  const cont = document.getElementById('alertaLogin');
  cont.innerHTML = '';

  try {
    const res = await fetch('api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (!res.ok) {
      cont.innerHTML = `<div class="alert alert-danger py-2">${data.error || 'No se pudo iniciar sesión.'}</div>`;
      return;
    }

    window.location.href = 'index.php';
  } catch (err) {
    cont.innerHTML = `<div class="alert alert-danger py-2">No se pudo conectar con el servidor.</div>`;
  }
});
</script>
</body>
</html>
