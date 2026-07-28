<?php require_once 'config/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Espectáculos · Palacio de Festivales</title>
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
        <li class="nav-item"><a class="nav-link active" href="espectaculo.php"><i class="bi bi-music-note-beamed"></i> Espectáculos</a></li>
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

  <div class="panel">
    <div class="panel-titulo">
      <span><i class="bi bi-music-note-beamed"></i> Catálogo de Espectáculos</span>
      <button class="btn btn-oro btn-sm" data-bs-toggle="modal" data-bs-target="#modalEspectaculo" onclick="prepararNuevoEspectaculo()">
        <i class="bi bi-plus-lg"></i> Nuevo espectáculo
      </button>
    </div>

    <div id="alertaEspectaculo"></div>

    <div class="table-responsive">
      <table class="table table-teatro align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Duración</th>
            <th>Descripción</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaEspectaculos">
          <tr><td colspan="6" class="text-center texto-tenue py-4">
            <div class="spinner-border spinner-oro" role="status"></div>
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal Crear / Editar Espectáculo -->
<div class="modal fade" id="modalEspectaculo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEspectaculo" novalidate>
        <div class="modal-header">
          <h5 class="modal-title texto-oro" id="tituloModalEspectaculo">Nuevo espectáculo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="espectaculoId">

          <div class="mb-3">
            <label class="form-label">Nombre del espectáculo</label>
            <input type="text" class="form-control" id="espectaculoNombre" placeholder="Ej: Sinfonía de Otoño" required minlength="3">
            <div class="invalid-feedback">Ingresa un nombre de al menos 3 caracteres.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select class="form-select" id="espectaculoTipo" required>
              <option value="">Selecciona un tipo...</option>
              <option value="Concierto">Concierto</option>
              <option value="Teatro">Teatro</option>
              <option value="Danza">Danza</option>
              <option value="Ópera">Ópera</option>
              <option value="Otro">Otro</option>
            </select>
            <div class="invalid-feedback">Selecciona el tipo de espectáculo.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Duración (minutos)</label>
            <input type="number" class="form-control" id="espectaculoDuracion" min="1" placeholder="Ej: 110" required>
            <div class="invalid-feedback">Ingresa una duración válida mayor a 0.</div>
          </div>

          <div class="mb-1">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" id="espectaculoDescripcion" rows="3" placeholder="Sinopsis o detalles del espectáculo..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-oro" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-oro">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="pie">Palacio de Festivales</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/espectaculo.js"></script>
</body>
</html>
