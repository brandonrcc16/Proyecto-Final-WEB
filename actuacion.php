<?php require_once 'config/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actuaciones · Palacio de Festivales</title>
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
        <li class="nav-item"><a class="nav-link active" href="actuacion.php"><i class="bi bi-calendar-event"></i> Actuaciones</a></li>
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
      <span><i class="bi bi-calendar-event"></i> Actuaciones (funciones)</span>
      <button class="btn btn-oro btn-sm" data-bs-toggle="modal" data-bs-target="#modalActuacion" onclick="prepararNuevaActuacion()">
        <i class="bi bi-plus-lg"></i> Nueva actuación
      </button>
    </div>

    <div id="alertaActuacion"></div>

    <div class="table-responsive">
      <table class="table table-teatro align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Espectáculo</th>
            <th>Sala</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Precio base</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaActuaciones">
          <tr><td colspan="8" class="text-center texto-tenue py-4">
            <div class="spinner-border spinner-oro" role="status"></div>
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal Crear / Editar Actuación -->
<div class="modal fade" id="modalActuacion" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formActuacion" novalidate>
        <div class="modal-header">
          <h5 class="modal-title texto-oro" id="tituloModalActuacion">Nueva actuación</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="actuacionId">

          <div class="mb-3">
            <label class="form-label">Espectáculo</label>
            <select class="form-select" id="actuacionEspectaculo" required>
              <option value="">Selecciona un espectáculo...</option>
            </select>
            <div class="invalid-feedback">Selecciona un espectáculo.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Sala</label>
            <select class="form-select" id="actuacionSala" required>
              <option value="">Selecciona una sala...</option>
            </select>
            <div class="invalid-feedback">Selecciona una sala.</div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha</label>
              <input type="date" class="form-control" id="actuacionFecha" required min="<?= date('Y-m-d') ?>">
              <div class="invalid-feedback">Ingresa una fecha válida.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Hora</label>
              <input type="time" class="form-control" id="actuacionHora" required>
              <div class="invalid-feedback">Ingresa una hora válida.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Precio base ($)</label>
            <input type="number" step="0.01" class="form-control" id="actuacionPrecio" min="0.01" placeholder="Ej: 25.00" required>
            <div class="invalid-feedback">Ingresa un precio válido mayor a 0.</div>
          </div>

          <div class="mb-1">
            <label class="form-label">Estado</label>
            <select class="form-select" id="actuacionEstado">
              <option value="programada">Programada</option>
              <option value="cancelada">Cancelada</option>
              <option value="finalizada">Finalizada</option>
            </select>
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
<script src="assets/js/actuacion.js"></script>
</body>
</html>
