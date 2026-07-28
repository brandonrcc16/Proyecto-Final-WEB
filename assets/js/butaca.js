const API_BUTACA = 'api/butaca.php';
const API_ZONA_REF = 'api/zona.php';
let cacheButacas = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarSelectsZona();
  cargarButacas();
});
document.getElementById('formButaca').addEventListener('submit', guardarButaca);

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

async function cargarSelectsZona() {
  try {
    const res = await fetch(API_ZONA_REF);
    const items = await res.json();

    const selectFiltro = document.getElementById('filtroZona');
    const selectForm = document.getElementById('butacaZona');

    items.forEach(z => {
      const texto = `${z.nombre_sala} — ${z.nombre}`;

      const opt1 = document.createElement('option');
      opt1.value = z.id_zona;
      opt1.textContent = texto;
      selectFiltro.appendChild(opt1);

      const opt2 = document.createElement('option');
      opt2.value = z.id_zona;
      opt2.textContent = texto;
      selectForm.appendChild(opt2);
    });
  } catch (err) { /* manejado por la tabla principal */ }
}

async function cargarButacas() {
  const tbody = document.getElementById('tablaButacas');
  const idZona = document.getElementById('filtroZona').value;
  const url = idZona ? `${API_BUTACA}?id_zona=${idZona}` : API_BUTACA;

  try {
    const res = await fetch(url);
    const items = await res.json();
    cacheButacas = Array.isArray(items) ? items : [];

    if (cacheButacas.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center texto-tenue py-4">
        No hay butacas registradas para este filtro.
      </td></tr>`;
      return;
    }

    tbody.innerHTML = cacheButacas.map(b => `
      <tr>
        <td>${b.id_butaca}</td>
        <td>${escaparHtml(b.nombre_sala)}</td>
        <td>${escaparHtml(b.nombre_zona)}</td>
        <td>${escaparHtml(b.fila)}</td>
        <td>${b.numero}</td>
        <td><span class="badge badge-${b.estado}">${b.estado}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-oro" onclick="prepararEditarButaca(${b.id_butaca})" data-bs-toggle="modal" data-bs-target="#modalButaca">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-granate" onclick="eliminarButaca(${b.id_butaca})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">
      Error al cargar las butacas. Verifica el servidor y la base de datos.
    </td></tr>`;
  }
}

function prepararNuevaButaca() {
  document.getElementById('formButaca').reset();
  document.getElementById('formButaca').classList.remove('was-validated');
  document.getElementById('butacaId').value = '';
  document.getElementById('tituloModalButaca').textContent = 'Nueva butaca';
}

function prepararEditarButaca(id) {
  const item = cacheButacas.find(b => b.id_butaca == id);
  if (!item) return;
  document.getElementById('formButaca').classList.remove('was-validated');
  document.getElementById('butacaId').value = item.id_butaca;
  document.getElementById('butacaZona').value = item.id_zona;
  document.getElementById('butacaFila').value = item.fila;
  document.getElementById('butacaNumero').value = item.numero;
  document.getElementById('butacaEstado').value = item.estado;
  document.getElementById('tituloModalButaca').textContent = 'Editar butaca';
}

async function guardarButaca(e) {
  e.preventDefault();
  const form = e.target;

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const id = document.getElementById('butacaId').value;
  const payload = {
    id_zona: document.getElementById('butacaZona').value,
    fila: document.getElementById('butacaFila').value.trim(),
    numero: document.getElementById('butacaNumero').value,
    estado: document.getElementById('butacaEstado').value
  };

  const url = id ? `${API_BUTACA}?id=${id}` : API_BUTACA;
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
      mostrarAlerta('alertaButaca', msg);
      return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalButaca')).hide();
    mostrarAlerta('alertaButaca', data.mensaje, 'success');
    cargarButacas();
  } catch (err) {
    mostrarAlerta('alertaButaca', 'No se pudo conectar con el servidor.');
  }
}

async function eliminarButaca(id) {
  if (!confirm('¿Seguro que deseas eliminar esta butaca?')) return;
  try {
    const res = await fetch(`${API_BUTACA}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaButaca', data.error || 'No se pudo eliminar.');
      return;
    }
    mostrarAlerta('alertaButaca', data.mensaje, 'success');
    cargarButacas();
  } catch (err) {
    mostrarAlerta('alertaButaca', 'No se pudo conectar con el servidor.');
  }
}
