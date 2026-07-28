const API_ZONA = 'api/zona.php';
const API_SALA_REF_ZONA = 'api/sala.php';
let cacheZonas = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarZonas();
  cargarSelectSalasZona();
});
document.getElementById('formZona').addEventListener('submit', guardarZona);

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

async function cargarSelectSalasZona() {
  const select = document.getElementById('zonaSala');
  try {
    const res = await fetch(API_SALA_REF_ZONA);
    const items = await res.json();
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = it.id_sala;
      opt.textContent = it.nombre;
      select.appendChild(opt);
    });
  } catch (err) { /* manejado por la tabla principal */ }
}

async function cargarZonas() {
  const tbody = document.getElementById('tablaZonas');
  try {
    const res = await fetch(API_ZONA);
    const items = await res.json();
    cacheZonas = Array.isArray(items) ? items : [];

    if (cacheZonas.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center texto-tenue py-4">
        No hay zonas registradas todavía.
      </td></tr>`;
      return;
    }

    tbody.innerHTML = cacheZonas.map(z => `
      <tr>
        <td>${z.id_zona}</td>
        <td>${escaparHtml(z.nombre_sala)}</td>
        <td>${escaparHtml(z.nombre)}</td>
        <td>× ${parseFloat(z.multiplicador_precio).toFixed(2)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-oro" onclick="prepararEditarZona(${z.id_zona})" data-bs-toggle="modal" data-bs-target="#modalZona">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-granate" onclick="eliminarZona(${z.id_zona})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">
      Error al cargar las zonas. Verifica el servidor y la base de datos.
    </td></tr>`;
  }
}

function prepararNuevaZona() {
  document.getElementById('formZona').reset();
  document.getElementById('formZona').classList.remove('was-validated');
  document.getElementById('zonaId').value = '';
  document.getElementById('tituloModalZona').textContent = 'Nueva zona';
}

function prepararEditarZona(id) {
  const item = cacheZonas.find(z => z.id_zona == id);
  if (!item) return;
  document.getElementById('formZona').classList.remove('was-validated');
  document.getElementById('zonaId').value = item.id_zona;
  document.getElementById('zonaSala').value = item.id_sala;
  document.getElementById('zonaNombre').value = item.nombre;
  document.getElementById('zonaMultiplicador').value = item.multiplicador_precio;
  document.getElementById('tituloModalZona').textContent = 'Editar zona';
}

async function guardarZona(e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const id = document.getElementById('zonaId').value;
  const payload = {
    id_sala: document.getElementById('zonaSala').value,
    nombre: document.getElementById('zonaNombre').value.trim(),
    multiplicador_precio: document.getElementById('zonaMultiplicador').value
  };

  const url = id ? `${API_ZONA}?id=${id}` : API_ZONA;
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
      mostrarAlerta('alertaZona', msg);
      return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalZona')).hide();
    mostrarAlerta('alertaZona', data.mensaje, 'success');
    cargarZonas();
  } catch (err) {
    mostrarAlerta('alertaZona', 'No se pudo conectar con el servidor.');
  }
}

async function eliminarZona(id) {
  if (!confirm('¿Seguro que deseas eliminar esta zona?')) return;
  try {
    const res = await fetch(`${API_ZONA}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaZona', data.error || 'No se pudo eliminar.');
      return;
    }
    mostrarAlerta('alertaZona', data.mensaje, 'success');
    cargarZonas();
  } catch (err) {
    mostrarAlerta('alertaZona', 'No se pudo conectar con el servidor.');
  }
}
