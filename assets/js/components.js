/**
 * Componentes JavaScript (DataTable, Modals, DatePicker, etc.)
 * VANILLA JavaScript - SIN DEPENDENCIAS
 */

// DataTable simple
class SimpleDataTable {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;

        this.options = {
            pageLength: options.pageLength || 25,
            searchable: options.searchable !== false,
            ...options
        };

        this.currentPage = 1;
        this.data = [];
        this.filteredData = [];

        this.init();
    }

    init() {
        this.captureData();
        this.buildUI();
        this.render();
    }

    captureData() {
        const rows = this.table.querySelectorAll('tbody tr');
        this.data = Array.from(rows).map(row => {
            return Array.from(row.cells).map(cell => cell.textContent);
        });
        this.filteredData = [...this.data];
    }

    buildUI() {
        const wrapper = document.createElement('div');
        wrapper.className = 'datatable-wrapper';

        const header = document.createElement('div');
        header.className = 'datatable-header';
        header.innerHTML = `
            <div class="datatable-length">
                Mostrar
                <select class="dt-length">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                registros
            </div>
            <div class="datatable-search">
                <input type="text" class="dt-search" placeholder="Buscar...">
            </div>
        `;

        this.table.parentNode.insertBefore(wrapper, this.table);
        wrapper.appendChild(header);
        wrapper.appendChild(this.table);

        const footer = document.createElement('div');
        footer.className = 'datatable-footer';
        footer.innerHTML = `
            <div class="datatable-info"></div>
            <div class="datatable-pagination"></div>
        `;
        wrapper.appendChild(footer);

        // Event listeners
        wrapper.querySelector('.dt-search').addEventListener('input', (e) => this.search(e.target.value));
        wrapper.querySelector('.dt-length').addEventListener('change', (e) => {
            this.options.pageLength = parseInt(e.target.value);
            this.currentPage = 1;
            this.render();
        });
    }

    search(term) {
        term = term.toLowerCase();
        this.filteredData = this.data.filter(row =>
            row.some(cell => cell.toLowerCase().includes(term))
        );
        this.currentPage = 1;
        this.render();
    }

    render() {
        const start = (this.currentPage - 1) * this.options.pageLength;
        const end = start + this.options.pageLength;
        const page = this.filteredData.slice(start, end);

        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';
        page.forEach(row => {
            const tr = document.createElement('tr');
            row.forEach(cell => {
                const td = document.createElement('td');
                td.textContent = cell;
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        this.renderPagination();
        this.renderInfo();
    }

    renderInfo() {
        const start = (this.currentPage - 1) * this.options.pageLength + 1;
        const end = Math.min(this.currentPage * this.options.pageLength, this.filteredData.length);
        const total = this.filteredData.length;

        const info = this.table.closest('.datatable-wrapper').querySelector('.datatable-info');
        info.textContent = `Mostrando ${start} a ${end} de ${total} registros`;
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageLength);
        const pagination = this.table.closest('.datatable-wrapper').querySelector('.datatable-pagination');

        let html = '<button class="dt-prev" ' + (this.currentPage === 1 ? 'disabled' : '') + '>Anterior</button>';

        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="${this.currentPage === i ? 'active' : ''}">${i}</button>`;
        }

        html += '<button class="dt-next" ' + (this.currentPage === totalPages ? 'disabled' : '') + '>Siguiente</button>';

        pagination.innerHTML = html;

        pagination.querySelectorAll('button').forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('dt-prev')) {
                    this.currentPage = Math.max(1, this.currentPage - 1);
                } else if (btn.classList.contains('dt-next')) {
                    this.currentPage = Math.min(totalPages, this.currentPage + 1);
                } else if (btn.textContent && !isNaN(btn.textContent)) {
                    this.currentPage = parseInt(btn.textContent);
                }
                this.render();
            });
        });
    }
}

// Inicializar DataTables automáticamente
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.datatable').forEach(table => {
        if (table.id) new SimpleDataTable(table.id);
    });
});

// Modal simple
function showModal(title, content, footer = '') {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay show';
    modal.innerHTML = `
        <div class="modal show">
            <div class="modal-header">
                <h3 class="modal-title">${title}</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
            </div>
            <div class="modal-body">${content}</div>
            ${footer ? `<div class="modal-footer">${footer}</div>` : ''}
        </div>
    `;
    document.body.appendChild(modal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });

    return modal;
}

// Toast notification
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.closest('.toast').remove()">×</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Exponer funciones globalmente
window.SimpleDataTable = SimpleDataTable;
window.showModal = showModal;
window.showToast = showToast;
