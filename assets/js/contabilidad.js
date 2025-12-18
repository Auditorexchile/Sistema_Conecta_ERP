/**
 * JavaScript para el módulo de Contabilidad
 * VANILLA JavaScript - SIN DEPENDENCIAS
 */

const Contabilidad = {
    init() {
        this.initComprobantes();
        this.initPlanCuentas();
    },

    // Comprobantes contables
    initComprobantes() {
        // Agregar línea
        document.querySelectorAll('.btn-agregar-linea').forEach(btn => {
            btn.addEventListener('click', () => this.agregarLineaDetalle());
        });

        // Eliminar línea
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-eliminar-linea')) {
                e.target.closest('tr').remove();
                this.calcularTotales();
            }
        });

        // Calcular totales al cambiar debe/haber
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('debe-input') || e.target.classList.contains('haber-input')) {
                this.calcularTotales();
            }
        });
    },

    agregarLineaDetalle() {
        const tbody = document.querySelector('#detalle-comprobante tbody');
        if (!tbody) return;

        const numLinea = tbody.querySelectorAll('tr').length + 1;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center">${numLinea}</td>
            <td>
                <select name="detalle[${numLinea}][id_cuenta]" class="form-control form-control-sm" required>
                    <option value="">Seleccionar...</option>
                </select>
            </td>
            <td>
                <input type="number" name="detalle[${numLinea}][debe]" class="form-control form-control-sm text-end debe-input" value="0" step="0.01">
            </td>
            <td>
                <input type="number" name="detalle[${numLinea}][haber]" class="form-control form-control-sm text-end haber-input" value="0" step="0.01">
            </td>
            <td>
                <input type="text" name="detalle[${numLinea}][glosa_detalle]" class="form-control form-control-sm">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btn-eliminar-linea">✕</button>
            </td>
        `;

        tbody.appendChild(tr);
    },

    calcularTotales() {
        let totalDebe = 0;
        let totalHaber = 0;

        document.querySelectorAll('.debe-input').forEach(input => {
            totalDebe += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.haber-input').forEach(input => {
            totalHaber += parseFloat(input.value) || 0;
        });

        const diferencia = totalDebe - totalHaber;

        const totalDebeEl = document.getElementById('total-debe');
        const totalHaberEl = document.getElementById('total-haber');
        const diferenciaEl = document.getElementById('diferencia');

        if (totalDebeEl) totalDebeEl.textContent = ConectaERP.formatMoney(totalDebe);
        if (totalHaberEl) totalHaberEl.textContent = ConectaERP.formatMoney(totalHaber);
        if (diferenciaEl) {
            diferenciaEl.textContent = ConectaERP.formatMoney(Math.abs(diferencia));
            diferenciaEl.className = diferencia === 0 ? 'diferencia-ok' : 'diferencia-error';
        }
    },

    // Plan de cuentas
    initPlanCuentas() {
        // Filtros
        const buscar = document.getElementById('buscar-cuenta');
        if (buscar) {
            buscar.addEventListener('input', () => this.filtrarCuentas());
        }

        document.querySelectorAll('#filtro-nivel, #filtro-clasificacion, #filtro-imputable').forEach(select => {
            select.addEventListener('change', () => this.filtrarCuentas());
        });
    },

    filtrarCuentas() {
        const buscar = (document.getElementById('buscar-cuenta')?.value || '').toLowerCase();
        const nivel = document.getElementById('filtro-nivel')?.value || '';
        const clasificacion = document.getElementById('filtro-clasificacion')?.value || '';
        const imputable = document.getElementById('filtro-imputable')?.value || '';

        document.querySelectorAll('#tabla-plan-cuentas tbody tr').forEach(row => {
            const texto = row.textContent.toLowerCase();
            let mostrar = true;

            if (buscar && !texto.includes(buscar)) mostrar = false;
            if (nivel && row.dataset.nivel != nivel) mostrar = false;
            if (clasificacion && row.dataset.clasificacion !== clasificacion) mostrar = false;
            if (imputable !== '' && row.dataset.imputable != imputable) mostrar = false;

            row.style.display = mostrar ? '' : 'none';
        });
    }
};

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Contabilidad.init());
} else {
    Contabilidad.init();
}
