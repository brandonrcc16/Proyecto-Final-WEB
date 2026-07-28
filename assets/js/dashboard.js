const API_DASHBOARD = 'api/dashboard.php';

document.addEventListener('DOMContentLoaded', cargarDashboard);

function escaparHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto ?? '';
  return div.innerHTML;
}

function formatearFecha(fecha) {
  const [a, m, d] = fecha.split('-');
  return `${d}/${m}/${a}`;
}

function formatearMoneda(valor) {
  return `$${parseFloat(valor).toFixed(2)}`;
}

function colorOcupacion(pct) {
  if (pct >= 85) return 'bg-danger';
  if (pct >= 50) return 'bg-warning';
  return 'bg-success';
}

async function cargarDashboard() {
  const cont = document.getElementById('listaOcupacion');
  try {
    const res = await fetch(API_DASHBOARD);
    const data = await res.json();

    if (!res.ok) {
      document.getElementById('alertaDashboard').innerHTML =
        `<div class="alert alert-danger py-2">${data.error || 'No se pudieron cargar las estadísticas.'}</div>`;
      return;
    }

    document.getElementById('statEntradas').textContent = data.total_entradas;
    document.getElementById('statIngresos').textContent = formatearMoneda(data.ingresos_totales);
    document.getElementById('statFunciones').textContent = data.funciones_programadas;

    if (!Array.isArray(data.funciones) || data.funciones.length === 0) {
      cont.innerHTML = `<p class="text-center texto-tenue py-4">Aún no hay funciones programadas.</p>`;
      return;
    }

    cont.innerHTML = data.funciones.map(f => `
      <div class="mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
        <div class="d-flex justify-content-between flex-wrap">
          <div>
            <div class="fw-semibold texto-oro">${escaparHtml(f.nombre_espectaculo)}</div>
            <div class="texto-tenue small">${escaparHtml(f.nombre_sala)} · ${formatearFecha(f.fecha)} ${f.hora.substring(0,5)}
              <span class="badge badge-${f.estado} ms-1">${f.estado}</span>
            </div>
          </div>
          <div class="text-end">
            <div class="small texto-tenue">Ingresos: <span class="texto-oro fw-semibold">${formatearMoneda(f.ingresos_funcion)}</span></div>
            <div class="small texto-tenue">${f.vendidas} / ${f.total_butacas} butacas vendidas</div>
          </div>
        </div>
        <div class="progress mt-2" style="height: 10px; background-color: rgba(255,255,255,0.08);">
          <div class="progress-bar ${colorOcupacion(f.ocupacion_pct)}" role="progressbar"
               style="width: ${f.ocupacion_pct}%;" aria-valuenow="${f.ocupacion_pct}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="text-end small texto-tenue mt-1">${f.ocupacion_pct}% de ocupación</div>
      </div>
    `).join('');
  } catch (err) {
    cont.innerHTML = `<p class="text-center text-danger py-4">Error al cargar las estadísticas del dashboard.</p>`;
  }
}
