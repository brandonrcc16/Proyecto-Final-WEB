<?php require_once 'config/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Palacio de Festivales</title>
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
        <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i>Resumen</a></li>
        <li class="nav-item"><a class="nav-link" href="sala.php"><i class="bi bi-building"></i> Salas</a></li>
        <li class="nav-item"><a class="nav-link" href="espectaculo.php"><i class="bi bi-music-note-beamed"></i> Espectáculos</a></li>
        <li class="nav-item"><a class="nav-link" href="actuacion.php"><i class="bi bi-calendar-event"></i> Actuaciones</a></li>
        <li class="nav-item"><a class="nav-link" href="zona.php"><i class="bi bi-map"></i> Zonas</a></li>
        <li class="nav-item"><a class="nav-link" href="butaca.php"><i class="bi bi-grid-3x3-gap"></i> Butacas</a></li>
        <li class="nav-item"><a class="nav-link" href="entrada.php"><i class="bi bi-ticket-perforated"></i> Entradas</a></li>
        <li class="nav-item ms-lg-3 d-flex align-items-center">
          <span class="nav-link disabled pe-2"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></span>
        </li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">

  <div class="mb-4">
    <h1 class="texto-oro" style="font-size:1.8rem;"><i class="bi bi-speedometer2"></i> Resumen</h1>
    <p class="texto-tenue mb-0">Resumen general de ventas y ocupación del Palacio.</p>
  </div>

  <div id="alertaDashboard"></div>

  <!-- Tarjetas de totales -->
  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="panel text-center">
        <div class="texto-tenue small text-uppercase" style="letter-spacing:1px;">Entradas vendidas</div>
        <div class="texto-oro" style="font-family:var(--fuente-display); font-size:2.4rem; font-weight:700;" id="statEntradas">—</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel text-center">
        <div class="texto-tenue small text-uppercase" style="letter-spacing:1px;">Ingresos totales</div>
        <div class="texto-oro" style="font-family:var(--fuente-display); font-size:2.4rem; font-weight:700;" id="statIngresos">—</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel text-center">
        <div class="texto-tenue small text-uppercase" style="letter-spacing:1px;">Funciones programadas</div>
        <div class="texto-oro" style="font-family:var(--fuente-display); font-size:2.4rem; font-weight:700;" id="statFunciones">—</div>
      </div>
    </div>
  </div>

  <!-- Ocupación por función -->
  <div class="panel">
    <div class="panel-titulo">
      <span><i class="bi bi-bar-chart-fill"></i> Ocupación por función</span>
    </div>

    <div id="listaOcupacion">
      <div class="text-center texto-tenue py-4">
        <div class="spinner-border spinner-oro" role="status"></div>
      </div>
    </div>
  </div>

</div>

<footer class="pie">Palacio de Festivales</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
