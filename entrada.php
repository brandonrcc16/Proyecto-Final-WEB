<?php require_once 'config/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entradas · Palacio de Festivales</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-marquesina navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      Palacio de Festivales
      <span class="marquesina-sub">Sistema de gestión y boletería</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Resumen</a></li>
        <li class="nav-item"><a class="nav-link" href="sala.php"><i class="bi bi-building"></i> Salas</a></li>
        <li class="nav-item"><a class="nav-link" href="espectaculo.php"><i class="bi bi-music-note-beamed"></i> Espectáculos</a></li>
        <li class="nav-item"><a class="nav-link" href="actuacion.php"><i class="bi bi-calendar-event"></i> Actuaciones</a></li>
        <li class="nav-item"><a class="nav-link" href="zona.php"><i class="bi bi-map"></i> Zonas</a></li>
        <li class="nav-item"><a class="nav-link" href="butaca.php"><i class="bi bi-grid-3x3-gap"></i> Butacas</a></li>
        <li class="nav-item"><a class="nav-link active" href="entrada.php"><i class="bi bi-ticket-perforated"></i> Entradas</a></li>
        <li class="nav-item ms-lg-3 d-flex align-items-center">
          <span class="nav-link disabled pe-2"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></span>
        </li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">

  <div class="row g-4">

    <!-- Formulario de venta -->
    <div class="col-lg-5">
      <div class="panel">
        <div class="panel-titulo">
          <span><i class="bi bi-ticket-perforated"></i> Vender entrada</span>
        </div>

        <div id="alertaEntrada"></div>

        <form id="formEntrada" novalidate>
          <div class="mb-3">
            <label class="form-label">Función (actuación)</label>
            <select class="form-select" id="entradaActuacion" required>
              <option value="">Selecciona una función...</option>
            </select>
            <div class="invalid-feedback">Selecciona una función programada.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Zona</label>
            <select class="form-select" id="entradaZona" required disabled>
              <option value="">Primero selecciona una función...</option>
            </select>
            <div class="invalid-feedback">Selecciona una zona.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Butaca disponible</label>
            <select class="form-select" id="entradaButaca" required disabled>
              <option value="">Primero selecciona una zona...</option>
            </select>
            <div class="invalid-feedback">Selecciona una butaca disponible.</div>
            <div class="form-text texto-tenue" id="precioEstimado"></div>
          </div>

          <hr class="hr-oro">

          <div class="mb-3">
            <label class="form-label">Nombre del cliente</label>
            <input type="text" class="form-control" id="entradaClienteNombre" placeholder="Nombre completo" required minlength="3">
            <div class="invalid-feedback">Ingresa el nombre completo del cliente.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Cédula del cliente</label>
            <input type="text" class="form-control" id="entradaClienteDocumento" placeholder="Ej: 1712345678" required maxlength="10" inputmode="numeric" autocomplete="off">
            <div class="invalid-feedback" id="feedbackCedula">Ingresa una cédula ecuatoriana válida (10 dígitos).</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Correo electrónico (opcional)</label>
            <input type="email" class="form-control" id="entradaClienteEmail" placeholder="correo@ejemplo.com">
            <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>
          </div>

          <button type="submit" class="btn btn-oro w-100">
            <i class="bi bi-ticket-perforated"></i> Confirmar venta
          </button>
        </form>
      </div>
    </div>

    <!-- Listado de entradas vendidas -->
    <div class="col-lg-7">
      <div class="panel">
        <div class="panel-titulo">
          <span><i class="bi bi-list-check"></i> Entradas vendidas</span>
        </div>

        <div id="listaEntradas">
          <div class="text-center texto-tenue py-4">
            <div class="spinner-border spinner-oro" role="status"></div>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<footer class="pie">Palacio de Festivales </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/entrada.js"></script>
</body>
</html>
