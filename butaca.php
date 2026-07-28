<?php require_once 'config/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Butacas · Palacio de Festivales</title>
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
        <li class="nav-item"><a class="nav-link active" href="butaca.php"><i class="bi bi-grid-3x3-gap"></i> Butacas</a></li>
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
      <span><i class="bi bi-grid-3x3-gap"></i> Butacas</span>
      <button class="btn btn-oro btn-sm" data-bs-toggle="modal" data-bs-target="#modalButaca" onclick="prepararNuevaButaca()">
        <i class="bi bi-plus-lg"></i> Nueva butaca
      </button>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Filtrar por zona</label>
        <select class="form-select" id="filtroZona" onchange="cargarButacas()">
          <option value="">Todas las zonas</option>
        </select>
      </div>
    </div>

    <div id="alertaButaca"></div>

    <div class="table-responsive">
      <table class="table table-teatro align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Sala</th>
            <th>Zona</th>
            <th>Fila</th>
            <th>Número</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaButacas">
          <tr><td colspan="7" class="text-center texto-tenue py-4">
            <div class="spinner-border spinner-oro" role="status"></div>
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal Crear / Editar Butaca -->
<div class="modal fade" id="modalButaca" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formButaca" novalidate>
        <div class="modal-header">
          <h5 class="modal-title texto-oro" id="tituloModalButaca">Nueva butaca</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="butacaId">

          <div class="mb-3">
            <label class="form-label">Zona</label>
            <select class="form-select" id="butacaZona" required>
              <option value="">Selecciona una zona...</option>
            </select>
            <div class="invalid-feedback">Selecciona una zona.</div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fila</label>
              <input type="text" class="form-control" id="butacaFila" maxlength="5" placeholder="Ej: A" required>
              <div class="invalid-feedback">Ingresa la fila (máx. 5 caracteres).</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Número</label>
              <input type="number" class="form-control" id="butacaNumero" min="1" placeholder="Ej: 12" required>
              <div class="invalid-feedback">Ingresa un número válido mayor a 0.</div>
            </div>
          </div>

          <div class="mb-1">
            <label class="form-label">Estado</label>
            <select class="form-select" id="butacaEstado">
              <option value="disponible">Disponible</option>
              <option value="mantenimiento">Mantenimiento</option>
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
<script src="assets/js/butaca.js"></script>
</body>
</html>
