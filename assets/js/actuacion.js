const API_ACTUACION = 'api/actuacion.php';
const API_ESPECTACULO_REF = 'api/espectaculo.php';
const API_SALA_REF = 'api/sala.php';
let cacheActuaciones = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarActuaciones();
  cargarSelectEspectaculos();
  cargarSelectSalas();
});
document.getElementById('formActuacion').addEventListener('submit', guardarActuacion);

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

function formatearFecha(fecha) {
  const [a, m, d] = fecha.split('-');
  return `${d}/${m}/${a}`;
}

async function cargarSelectEspectaculos() {
  const select = document.getElementById('actuacionEspectaculo');
  try {
    const res = await fetch(API_ESPECTACULO_REF);
    const items = await res.json();
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = it.id_espectaculo;
      opt.textContent = `${it.nombre} (${it.tipo})`;
      select.appendChild(opt);
    });
  } catch (err) { /* el error general ya se maneja en la tabla */ }
}

async function cargarSelectSalas() {
  const select = document.getElementById('actuacionSala');
  try {
    const res = await fetch(API_SALA_REF);
    const items = await res.json();
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = it.id_sala;
      opt.textContent = `${it.nombre} (cap. ${it.capacidad})`;
      select.appendChild(opt);
    });
  } catch (err) { /* el error general ya se maneja en la tabla */ }
}

async function cargarActuaciones() {
  const tbody = document.getElementById('tablaActuaciones');
  try {
    const res = await fetch(API_ACTUACION);
    const items = await res.json();
    cacheActuaciones = Array.isArray(items) ? items : [];

    if (cacheActuaciones.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center texto-tenue py-4">
        No hay actuaciones programadas todavía.
      </td></tr>`;
      return;
    }

    tbody.innerHTML = cacheActuaciones.map(a => `
      <tr>
        <td>${a.id_actuacion}</td>
        <td>${escaparHtml(a.nombre_espectaculo)}</td>
        <td>${escaparHtml(a.nombre_sala)}</td>
        <td>${formatearFecha(a.fecha)}</td>
        <td>${a.hora.substring(0,5)}</td>
        <td>$${parseFloat(a.precio_base).toFixed(2)}</td>
        <td><span class="badge badge-${a.estado}">${a.estado}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-oro" onclick="prepararEditarActuacion(${a.id_actuacion})" data-bs-toggle="modal" data-bs-target="#modalActuacion">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-granate" onclick="eliminarActuacion(${a.id_actuacion})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">
      Error al cargar las actuaciones. Verifica el servidor y la base de datos.
    </td></tr>`;
  }
}

function prepararNuevaActuacion() {
  document.getElementById('formActuacion').reset();
  document.getElementById('formActuacion').classList.remove('was-validated');
  document.getElementById('actuacionId').value = '';
  document.getElementById('tituloModalActuacion').textContent = 'Nueva actuación';
}

function prepararEditarActuacion(id) {
  const item = cacheActuaciones.find(a => a.id_actuacion == id);
  if (!item) return;
  document.getElementById('formActuacion').classList.remove('was-validated');
  document.getElementById('actuacionId').value = item.id_actuacion;
  document.getElementById('actuacionEspectaculo').value = item.id_espectaculo;
  document.getElementById('actuacionSala').value = item.id_sala;
  document.getElementById('actuacionFecha').value = item.fecha;
  document.getElementById('actuacionHora').value = item.hora.substring(0,5);
  document.getElementById('actuacionPrecio').value = item.precio_base;
  document.getElementById('actuacionEstado').value = item.estado;
  document.getElementById('tituloModalActuacion').textContent = 'Editar actuación';
}

async function guardarActuacion(e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    const fechaSeleccionada = document.getElementById('actuacionFecha').value;
   const hoy = new Date().toISOString().split('T')[0];
  if (fechaSeleccionada < hoy) {
    mostrarAlerta('alertaActuacion', 'La fecha de la función no puede ser un día que ya pasó.');
    return;
  }
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const id = document.getElementById('actuacionId').value;
  const payload = {
    id_espectaculo: document.getElementById('actuacionEspectaculo').value,
    id_sala: document.getElementById('actuacionSala').value,
    fecha: document.getElementById('actuacionFecha').value,
    hora: document.getElementById('actuacionHora').value,
    precio_base: document.getElementById('actuacionPrecio').value,
    estado: document.getElementById('actuacionEstado').value
  };

  const url = id ? `${API_ACTUACION}?id=${id}` : API_ACTUACION;
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
      mostrarAlerta('alertaActuacion', msg);
      return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalActuacion')).hide();
    mostrarAlerta('alertaActuacion', data.mensaje, 'success');
    cargarActuaciones();
  } catch (err) {
    mostrarAlerta('alertaActuacion', 'No se pudo conectar con el servidor.');
  }
}

async function eliminarActuacion(id) {
  if (!confirm('¿Seguro que deseas eliminar esta actuación?')) return;
  try {
    const res = await fetch(`${API_ACTUACION}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaActuacion', data.error || 'No se pudo eliminar.');
      return;
    }
    mostrarAlerta('alertaActuacion', data.mensaje, 'success');
    cargarActuaciones();
  } catch (err) {
    mostrarAlerta('alertaActuacion', 'No se pudo conectar con el servidor.');
  }
}
