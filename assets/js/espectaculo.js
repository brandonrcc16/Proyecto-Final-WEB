const API_ESPECTACULO = 'api/espectaculo.php';
let cacheEspectaculos = [];

document.addEventListener('DOMContentLoaded', cargarEspectaculos);
document.getElementById('formEspectaculo').addEventListener('submit', guardarEspectaculo);

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

async function cargarEspectaculos() {
  const tbody = document.getElementById('tablaEspectaculos');
  try {
    const res = await fetch(API_ESPECTACULO);
    const items = await res.json();
    cacheEspectaculos = Array.isArray(items) ? items : [];

    if (cacheEspectaculos.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center texto-tenue py-4">
        No hay espectáculos registrados todavía.
      </td></tr>`;
      return;
    }

    tbody.innerHTML = cacheEspectaculos.map(s => `
      <tr>
        <td>${s.id_espectaculo}</td>
        <td>${escaparHtml(s.nombre)}</td>
        <td>${escaparHtml(s.tipo)}</td>
        <td>${s.duracion_min} min</td>
        <td class="texto-tenue">${escaparHtml((s.descripcion || '').substring(0, 60))}${(s.descripcion||'').length > 60 ? '…' : ''}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-oro" onclick="prepararEditarEspectaculo(${s.id_espectaculo})" data-bs-toggle="modal" data-bs-target="#modalEspectaculo">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-granate" onclick="eliminarEspectaculo(${s.id_espectaculo})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">
      Error al cargar los espectáculos. Verifica el servidor y la base de datos.
    </td></tr>`;
  }
}

function prepararNuevoEspectaculo() {
  document.getElementById('formEspectaculo').reset();
  document.getElementById('formEspectaculo').classList.remove('was-validated');
  document.getElementById('espectaculoId').value = '';
  document.getElementById('tituloModalEspectaculo').textContent = 'Nuevo espectáculo';
}

function prepararEditarEspectaculo(id) {
  const item = cacheEspectaculos.find(s => s.id_espectaculo == id);
  if (!item) return;
  document.getElementById('formEspectaculo').classList.remove('was-validated');
  document.getElementById('espectaculoId').value = item.id_espectaculo;
  document.getElementById('espectaculoNombre').value = item.nombre;
  document.getElementById('espectaculoTipo').value = item.tipo;
  document.getElementById('espectaculoDuracion').value = item.duracion_min;
  document.getElementById('espectaculoDescripcion').value = item.descripcion || '';
  document.getElementById('tituloModalEspectaculo').textContent = 'Editar espectáculo';
}

async function guardarEspectaculo(e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const id = document.getElementById('espectaculoId').value;
  const payload = {
    nombre: document.getElementById('espectaculoNombre').value.trim(),
    tipo: document.getElementById('espectaculoTipo').value,
    duracion_min: document.getElementById('espectaculoDuracion').value,
    descripcion: document.getElementById('espectaculoDescripcion').value.trim()
  };

  const url = id ? `${API_ESPECTACULO}?id=${id}` : API_ESPECTACULO;
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
      mostrarAlerta('alertaEspectaculo', msg);
      return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalEspectaculo')).hide();
    mostrarAlerta('alertaEspectaculo', data.mensaje, 'success');
    cargarEspectaculos();
  } catch (err) {
    mostrarAlerta('alertaEspectaculo', 'No se pudo conectar con el servidor.');
  }
}

async function eliminarEspectaculo(id) {
  if (!confirm('¿Seguro que deseas eliminar este espectáculo?')) return;
  try {
    const res = await fetch(`${API_ESPECTACULO}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaEspectaculo', data.error || 'No se pudo eliminar.');
      return;
    }
    mostrarAlerta('alertaEspectaculo', data.mensaje, 'success');
    cargarEspectaculos();
  } catch (err) {
    mostrarAlerta('alertaEspectaculo', 'No se pudo conectar con el servidor.');
  }
}
