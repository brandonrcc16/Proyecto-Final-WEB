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
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Resumen</a></li>
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

  <div class="text-center mb-5">
    <h1 class="texto-oro display-5">Bienvenido al Palacio</h1>
    <p class="texto-tenue">Selecciona un módulo para administrar salas, espectáculos, funciones y venta de entradas.</p>
  </div>

  <div class="row g-4">

    <div class="col-md-6 col-lg-4">
      <a href="dashboard.php" class="tarjeta-modulo">
        <i class="bi bi-speedometer2 icono"></i>
        <h3>Resumen</h3>
        <p>Entradas vendidas, ingresos totales y ocupación por función.</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="sala.php" class="tarjeta-modulo">
        <i class="bi bi-building icono"></i>
        <h3>Salas</h3>
        <p>Auditorios del palacio: capacidad, ubicación y estado.</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="espectaculo.php" class="tarjeta-modulo">
        <i class="bi bi-music-note-beamed icono"></i>
        <h3>Espectáculos</h3>
        <p>Catálogo de conciertos, obras de teatro y danza.</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="actuacion.php" class="tarjeta-modulo">
        <i class="bi bi-calendar-event icono"></i>
        <h3>Actuaciones</h3>
        <p>Funciones programadas: espectáculo, sala, fecha y precio.</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="zona.php" class="tarjeta-modulo">
        <i class="bi bi-map icono"></i>
        <h3>Zonas</h3>
        <p>Divisiones de cada sala (VIP, Preferencia, General).</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="butaca.php" class="tarjeta-modulo">
        <i class="bi bi-grid-3x3-gap icono"></i>
        <h3>Butacas</h3>
        <p>Asientos físicos de cada zona: fila, número y estado.</p>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="entrada.php" class="tarjeta-modulo">
        <i class="bi bi-ticket-perforated icono"></i>
        <h3>Entradas</h3>
        <p>Venta de boletos: elige función, zona y butaca disponible.</p>
      </a>
    </div>

  </div>

</div>

<footer class="pie">Palacio de Festivales</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
