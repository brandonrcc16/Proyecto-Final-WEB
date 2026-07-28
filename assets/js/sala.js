const API_SALA = 'api/sala.php';
let cacheSalas = [];

document.addEventListener('DOMContentLoaded', cargarSalas);
document.getElementById('formSala').addEventListener('submit', guardarSala);

function mostrarAlerta(contenedorId, mensaje, tipo = 'danger') {
  const cont = document.getElementById(contenedorId);
  cont.innerHTML = `<div class="alert alert-${tipo} py-2">${mensaje}</div>`;
  setTimeout(() => { cont.innerHTML = ''; }, 5000);
}

function escaparHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto ?? '';
  return div.innerHTML;
}

async function cargarSalas() {
  const tbody = document.getElementById('tablaSalas');
  try {
    const res = await fetch(API_SALA);
    const salas = await res.json();
    cacheSalas = Array.isArray(salas) ? salas : [];

    if (cacheSalas.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center texto-tenue py-4">
        No hay salas registradas todavía. Crea la primera con el botón "Nueva sala".
      </td></tr>`;
      return;
    }

    tbody.innerHTML = cacheSalas.map(s => `
      <tr>
        <td>${s.id_sala}</td>
        <td>${escaparHtml(s.nombre)}</td>
        <td>${s.capacidad}</td>
        <td>${escaparHtml(s.ubicacion || '—')}</td>
        <td><span class="badge badge-${s.estado}">${s.estado}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-oro" onclick="prepararEditarSala(${s.id_sala})" data-bs-toggle="modal" data-bs-target="#modalSala">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-granate" onclick="eliminarSala(${s.id_sala})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">
      Error al cargar las salas. Verifica que el servidor y la base de datos estén activos.
    </td></tr>`;
  }
}

function prepararNuevaSala() {
  document.getElementById('formSala').reset();
  document.getElementById('formSala').classList.remove('was-validated');
  document.getElementById('salaId').value = '';
  document.getElementById('tituloModalSala').textContent = 'Nueva sala';
}

function prepararEditarSala(id) {
  const sala = cacheSalas.find(s => s.id_sala == id);
  if (!sala) return;
  document.getElementById('formSala').classList.remove('was-validated');
  document.getElementById('salaId').value = sala.id_sala;
  document.getElementById('salaNombre').value = sala.nombre;
  document.getElementById('salaCapacidad').value = sala.capacidad;
  document.getElementById('salaUbicacion').value = sala.ubicacion || '';
  document.getElementById('salaDescripcion').value = sala.descripcion || '';
  document.getElementById('salaEstado').value = sala.estado;
  document.getElementById('tituloModalSala').textContent = 'Editar sala';
}

async function guardarSala(e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const id = document.getElementById('salaId').value;
  const payload = {
    nombre: document.getElementById('salaNombre').value.trim(),
    capacidad: document.getElementById('salaCapacidad').value,
    ubicacion: document.getElementById('salaUbicacion').value.trim(),
    descripcion: document.getElementById('salaDescripcion').value.trim(),
    estado: document.getElementById('salaEstado').value
  };

  const url = id ? `${API_SALA}?id=${id}` : API_SALA;
  const metodo = id ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method: metodo,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (!res.ok) {
      const msg = data.errores ? data.errores.join('<br>') : (data.error || 'Ocurrió un error.');
      mostrarAlerta('alertaSala', msg);
      return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalSala')).hide();
    mostrarAlerta('alertaSala', data.mensaje, 'success');
    cargarSalas();
  } catch (err) {
    mostrarAlerta('alertaSala', 'No se pudo conectar con el servidor.');
  }
}

async function eliminarSala(id) {
  if (!confirm('¿Seguro que deseas eliminar esta sala? Esta acción no se puede deshacer.')) return;
  try {
    const res = await fetch(`${API_SALA}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaSala', data.error || 'No se pudo eliminar la sala.');
      return;
    }
    mostrarAlerta('alertaSala', data.mensaje, 'success');
    cargarSalas();
  } catch (err) {
    mostrarAlerta('alertaSala', 'No se pudo conectar con el servidor.');
  }
}
