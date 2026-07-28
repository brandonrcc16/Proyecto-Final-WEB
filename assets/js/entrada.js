const API_ENTRADA = 'api/entrada.php';
const API_ACTUACION_REF = 'api/actuacion.php';
const API_ZONA_REF_ENTRADA = 'api/zona.php';
const API_BUTACA_REF = 'api/butaca.php';

let precioBaseSeleccionado = 0;
let multiplicadorSeleccionado = 1;

document.addEventListener('DOMContentLoaded', () => {
  cargarSelectActuaciones();
  cargarEntradas();
});

document.getElementById('formEntrada').addEventListener('submit', venderEntrada);
document.getElementById('entradaActuacion').addEventListener('change', alCambiarActuacion);
document.getElementById('entradaZona').addEventListener('change', alCambiarZona);
document.getElementById('entradaClienteDocumento').addEventListener('input', validarCampoCedula);

/**
 * Valida una cédula ecuatoriana usando el algoritmo oficial de dígito
 * verificador (módulo 10). Devuelve true/false.
 */
function validarCedulaEcuatoriana(cedula) {
  if (!/^\d{10}$/.test(cedula)) return false;

  const digitos = cedula.split('').map(Number);
  const provincia = parseInt(cedula.substring(0, 2), 10);
  if (provincia < 1 || provincia > 24) return false;

  const tercerDigito = digitos[2];
  if (tercerDigito >= 6) return false; // 0-5: persona natural

  const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
  let suma = 0;
  for (let i = 0; i < 9; i++) {
    let valor = digitos[i] * coeficientes[i];
    if (valor > 9) valor -= 9;
    suma += valor;
  }
  const decena = Math.ceil(suma / 10) * 10;
  let digitoVerificador = decena - suma;
  if (digitoVerificador === 10) digitoVerificador = 0;

  return digitoVerificador === digitos[9];
}

function validarCampoCedula() {
  const campo = document.getElementById('entradaClienteDocumento');
  if (validarCedulaEcuatoriana(campo.value.trim())) {
    campo.setCustomValidity('');
  } else {
    campo.setCustomValidity('Cédula ecuatoriana no válida.');
  }
}

function mostrarAlerta(contenedorId, mensaje, tipo = 'danger') {
  const cont = document.getElementById(contenedorId);
  cont.innerHTML = `<div class="alert alert-${tipo} py-2">${mensaje}</div>`;
  if (tipo !== 'success') setTimeout(() => { cont.innerHTML = ''; }, 6000);
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

/* -------------------- Cascada de selects -------------------- */

async function cargarSelectActuaciones() {
  const select = document.getElementById('entradaActuacion');
  try {
    const res = await fetch(`${API_ACTUACION_REF}?estado=programada`);
    const items = await res.json();
    items.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id_actuacion;
      opt.dataset.idSala = a.id_sala;
      opt.dataset.precioBase = a.precio_base;
      opt.textContent = `${a.nombre_espectaculo} — ${a.nombre_sala} — ${formatearFecha(a.fecha)} ${a.hora.substring(0,5)}`;
      select.appendChild(opt);
    });
  } catch (err) {
    mostrarAlerta('alertaEntrada', 'No se pudieron cargar las funciones disponibles.');
  }
}

async function alCambiarActuacion() {
  const selectActuacion = document.getElementById('entradaActuacion');
  const selectZona = document.getElementById('entradaZona');
  const selectButaca = document.getElementById('entradaButaca');

  selectZona.innerHTML = '<option value="">Selecciona una zona...</option>';
  selectButaca.innerHTML = '<option value="">Primero selecciona una zona...</option>';
  selectButaca.disabled = true;
  document.getElementById('precioEstimado').textContent = '';

  if (!selectActuacion.value) {
    selectZona.disabled = true;
    return;
  }

  const opcion = selectActuacion.selectedOptions[0];
  const idSala = opcion.dataset.idSala;
  precioBaseSeleccionado = parseFloat(opcion.dataset.precioBase);

  selectZona.disabled = false;

  try {
    const res = await fetch(`${API_ZONA_REF_ENTRADA}?id_sala=${idSala}`);
    const zonas = await res.json();
    zonas.forEach(z => {
      const opt = document.createElement('option');
      opt.value = z.id_zona;
      opt.dataset.multiplicador = z.multiplicador_precio;
      opt.textContent = `${z.nombre} (× ${parseFloat(z.multiplicador_precio).toFixed(2)})`;
      selectZona.appendChild(opt);
    });
  } catch (err) {
    mostrarAlerta('alertaEntrada', 'No se pudieron cargar las zonas de la sala.');
  }
}

async function alCambiarZona() {
  const selectActuacion = document.getElementById('entradaActuacion');
  const selectZona = document.getElementById('entradaZona');
  const selectButaca = document.getElementById('entradaButaca');

  selectButaca.innerHTML = '<option value="">Cargando butacas disponibles...</option>';
  document.getElementById('precioEstimado').textContent = '';

  if (!selectZona.value) {
    selectButaca.disabled = true;
    selectButaca.innerHTML = '<option value="">Primero selecciona una zona...</option>';
    return;
  }

  const opcionZona = selectZona.selectedOptions[0];
  multiplicadorSeleccionado = parseFloat(opcionZona.dataset.multiplicador);
  const idActuacion = selectActuacion.value;
  const idZona = selectZona.value;

  selectButaca.disabled = false;

  try {
    const res = await fetch(`${API_BUTACA_REF}?disponibles=1&id_actuacion=${idActuacion}&id_zona=${idZona}`);
    const butacas = await res.json();

    if (!Array.isArray(butacas) || butacas.length === 0) {
      selectButaca.innerHTML = '<option value="">No hay butacas disponibles en esta zona</option>';
      selectButaca.disabled = true;
      return;
    }

    selectButaca.innerHTML = '<option value="">Selecciona una butaca...</option>';
    butacas.forEach(b => {
      const opt = document.createElement('option');
      opt.value = b.id_butaca;
      opt.textContent = `Fila ${b.fila} · Asiento ${b.numero}`;
      selectButaca.appendChild(opt);
    });

    const precioEstimado = (precioBaseSeleccionado * multiplicadorSeleccionado).toFixed(2);
    document.getElementById('precioEstimado').textContent = `Precio estimado: $${precioEstimado}`;
  } catch (err) {
    mostrarAlerta('alertaEntrada', 'No se pudieron cargar las butacas disponibles.');
  }
}

/* -------------------- Venta de entrada -------------------- */

async function venderEntrada(e) {
  e.preventDefault();
  const form = e.target;

  validarCampoCedula();

  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const payload = {
    id_actuacion: document.getElementById('entradaActuacion').value,
    id_butaca: document.getElementById('entradaButaca').value,
    cliente_nombre: document.getElementById('entradaClienteNombre').value.trim(),
    cliente_documento: document.getElementById('entradaClienteDocumento').value.trim(),
    cliente_email: document.getElementById('entradaClienteEmail').value.trim()
  };

  try {
    const res = await fetch(API_ENTRADA, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (!res.ok) {
      const msg = data.errores ? data.errores.join('<br>') : (data.error || 'Ocurrió un error.');
      mostrarAlerta('alertaEntrada', msg);
      return;
    }

    mostrarAlerta('alertaEntrada',
      `Entrada vendida. Código: <strong>${data.codigo}</strong> — Precio: $${parseFloat(data.precio_final).toFixed(2)}`,
      'success');

    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('entradaClienteDocumento').setCustomValidity('');
    document.getElementById('entradaZona').innerHTML = '<option value="">Primero selecciona una función...</option>';
    document.getElementById('entradaZona').disabled = true;
    document.getElementById('entradaButaca').innerHTML = '<option value="">Primero selecciona una zona...</option>';
    document.getElementById('entradaButaca').disabled = true;
    document.getElementById('precioEstimado').textContent = '';

    cargarEntradas();
  } catch (err) {
    mostrarAlerta('alertaEntrada', 'No se pudo conectar con el servidor.');
  }
}

/* -------------------- Listado de entradas (boletos) -------------------- */

async function cargarEntradas() {
  const cont = document.getElementById('listaEntradas');
  try {
    const res = await fetch(API_ENTRADA);
    const items = await res.json();

    if (!Array.isArray(items) || items.length === 0) {
      cont.innerHTML = `<p class="text-center texto-tenue py-4">Aún no se han vendido entradas.</p>`;
      return;
    }

    cont.innerHTML = items.map(en => `
      <div class="boleto">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="codigo">${en.codigo}</div>
            <div class="fw-semibold">${escaparHtml(en.nombre_espectaculo)}</div>
            <div class="texto-tenue small">${escaparHtml(en.nombre_sala)} · ${escaparHtml(en.nombre_zona)} · Fila ${escaparHtml(en.fila)}, Asiento ${en.numero}</div>
            <div class="texto-tenue small">${formatearFecha(en.fecha)} ${en.hora.substring(0,5)}</div>
            <div class="small mt-1">${escaparHtml(en.cliente_nombre)} · ${escaparHtml(en.cliente_documento)}</div>
          </div>
          <div class="text-end">
            <div class="texto-oro fw-bold">$${parseFloat(en.precio_final).toFixed(2)}</div>
            <span class="badge badge-${en.estado} mb-2">${en.estado}</span><br>
            <button class="btn btn-sm btn-granate mt-1" onclick="anularEntrada(${en.id_entrada})">
              <i class="bi bi-x-circle"></i> Anular
            </button>
          </div>
        </div>
      </div>
    `).join('');
  } catch (err) {
    cont.innerHTML = `<p class="text-center text-danger py-4">Error al cargar las entradas vendidas.</p>`;
  }
}

async function anularEntrada(id) {
  if (!confirm('¿Anular esta entrada? La butaca quedará libre nuevamente para esta función.')) return;
  try {
    const res = await fetch(`${API_ENTRADA}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      mostrarAlerta('alertaEntrada', data.error || 'No se pudo anular la entrada.');
      return;
    }
    mostrarAlerta('alertaEntrada', data.mensaje, 'success');
    cargarEntradas();
  } catch (err) {
    mostrarAlerta('alertaEntrada', 'No se pudo conectar con el servidor.');
  }
}
