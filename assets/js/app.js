/**
 * JavaScript principal del sistema ERP
 * VANILLA JavaScript - SIN DEPENDENCIAS (No jQuery, No librerías externas)
 */

(function() {
    'use strict';

    // Objeto global ConectaERP
    window.ConectaERP = {
        baseURL: window.location.origin,

        // Inicializar aplicación
        init() {
            this.initEvents();
            this.initDropdowns();
            this.initRUTValidation();
            this.hideAlertsAuto();
        },

        // Inicializar eventos globales
        initEvents() {
            // Toggle navbar en móvil
            const navToggler = document.getElementById('navbarToggler');
            if (navToggler) {
                navToggler.addEventListener('click', () => {
                    document.getElementById('navbarMenu').classList.toggle('show');
                });
            }

            // Cerrar alertas
            document.querySelectorAll('.btn-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.alert').style.display = 'none';
                });
            });

            // Confirmación de eliminación
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.href || this.dataset.url;
                    const mensaje = this.dataset.mensaje || '¿Está seguro de eliminar este registro?';
                    ConectaERP.confirm(mensaje, () => window.location.href = url);
                });
            });
        },

        // Dropdowns
        initDropdowns() {
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const menu = this.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                            if (m !== menu) m.classList.remove('show');
                        });
                        menu.classList.toggle('show');
                    }
                });
            });

            // Cerrar dropdowns al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                        m.classList.remove('show');
                    });
                }
            });
        },

        // Validar RUT chileno
        validarRUT(rut) {
            rut = rut.replace(/[^0-9kK]/g, '');
            if (rut.length < 2) return false;

            const dv = rut.slice(-1).toUpperCase();
            const numero = rut.slice(0, -1);

            let suma = 0, multiplo = 2;
            for (let i = numero.length - 1; i >= 0; i--) {
                suma += multiplo * parseInt(numero[i]);
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }

            const resto = suma % 11;
            let dvCalculado = 11 - resto;

            if (dvCalculado === 11) dvCalculado = '0';
            else if (dvCalculado === 10) dvCalculado = 'K';
            else dvCalculado = dvCalculado.toString();

            return dv === dvCalculado;
        },

        // Formatear RUT
        formatRUT(rut) {
            rut = rut.replace(/[^0-9kK]/g, '');
            if (rut.length < 2) return rut;

            const dv = rut.slice(-1);
            const numero = rut.slice(0, -1);
            return numero.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv.toUpperCase();
        },

        // Inicializar validación RUT
        initRUTValidation() {
            document.querySelectorAll('input.rut').forEach(input => {
                input.addEventListener('input', function() {
                    this.value = ConectaERP.formatRUT(this.value);
                });

                input.addEventListener('blur', function() {
                    if (this.value && !ConectaERP.validarRUT(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        },

        // Formatear número como moneda
        formatMoney(amount) {
            return '$ ' + parseInt(amount || 0).toLocaleString('es-CL');
        },

        // Parsear moneda a número
        parseMoney(moneyString) {
            return parseInt((moneyString || '').replace(/[^\d]/g, '')) || 0;
        },

        // Auto-ocultar alertas
        hideAlertsAuto() {
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.style.display = 'none', 300);
                });
            }, 5000);
        },

        // Mostrar mensaje de éxito
        success(message, title = 'Éxito') {
            this.showAlert('success', title, message);
        },

        // Mostrar mensaje de error
        error(message, title = 'Error') {
            this.showAlert('error', title, message);
        },

        // Mostrar alerta personalizada
        showAlert(type, title, message) {
            const overlay = document.createElement('div');
            overlay.className = 'swal-overlay show';
            overlay.innerHTML = `
                <div class="swal-container">
                    <div class="swal-icon ${type}"></div>
                    <div class="swal-title">${title}</div>
                    <div class="swal-text">${message}</div>
                    <div class="swal-buttons">
                        <button class="btn btn-primary" onclick="this.closest('.swal-overlay').remove()">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        },

        // Confirmación
        confirm(message, callback, title = 'Confirmación') {
            const overlay = document.createElement('div');
            overlay.className = 'swal-overlay show';
            overlay.innerHTML = `
                <div class="swal-container">
                    <div class="swal-icon question"></div>
                    <div class="swal-title">${title}</div>
                    <div class="swal-text">${message}</div>
                    <div class="swal-buttons">
                        <button class="btn btn-secondary" onclick="this.closest('.swal-overlay').remove()">Cancelar</button>
                        <button class="btn btn-primary btn-confirm">Sí, continuar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            overlay.querySelector('.btn-confirm').addEventListener('click', () => {
                overlay.remove();
                if (typeof callback === 'function') callback();
            });
        },

        // Mostrar loading
        showLoading() {
            if (!document.querySelector('.loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay show';
                overlay.innerHTML = '<div class="loading-spinner"></div>';
                document.body.appendChild(overlay);
            }
        },

        // Ocultar loading
        hideLoading() {
            const overlay = document.querySelector('.loading-overlay');
            if (overlay) overlay.remove();
        },

        // Petición AJAX
        ajax(url, data, method = 'POST', callback) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (typeof callback === 'function') callback(response);
                } else {
                    ConectaERP.error('Error en la petición');
                }
            };

            const params = Object.keys(data).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k])).join('&');
            xhr.send(params);
        }
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ConectaERP.init());
    } else {
        ConectaERP.init();
    }

})();
