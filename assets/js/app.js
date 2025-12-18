/**
 * JavaScript principal del sistema ERP
 */

(function() {
    'use strict';

    // Configuración global
    window.ConectaERP = {
        baseURL: document.querySelector('base')?.href || window.location.origin,
        csrfToken: null,

        // Inicializar aplicación
        init: function() {
            this.initComponents();
            this.bindEvents();
        },

        // Inicializar componentes
        initComponents: function() {
            // Inicializar Select2 en todos los selects
            if ($.fn.select2) {
                $('select.select2').select2({
                    theme: 'bootstrap-5',
                    language: 'es',
                    width: '100%'
                });
            }

            // Inicializar DatePicker
            if (window.flatpickr) {
                flatpickr('.datepicker', {
                    dateFormat: 'd-m-Y',
                    locale: 'es'
                });

                flatpickr('.datetimepicker', {
                    enableTime: true,
                    dateFormat: 'd-m-Y H:i',
                    locale: 'es'
                });
            }

            // Inicializar DataTables
            if ($.fn.DataTable) {
                $('.datatable').DataTable();
            }

            // Auto-format RUT inputs
            this.initRUTFormat();

            // Auto-format moneda inputs
            this.initMoneyFormat();
        },

        // Bind eventos
        bindEvents: function() {
            const self = this;

            // Confirmación de eliminación
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const url = $(this).attr('href') || $(this).data('url');
                const mensaje = $(this).data('mensaje') || '¿Está seguro de eliminar este registro?';

                self.confirm(mensaje, function() {
                    window.location.href = url;
                });
            });

            // AJAX forms
            $(document).on('submit', 'form.ajax-form', function(e) {
                e.preventDefault();
                self.submitAjaxForm($(this));
            });

            // Toggle sidebar en móvil
            $('.btn-toggle-sidebar').on('click', function() {
                $('.sidebar').toggleClass('show');
            });
        },

        // Formatear RUT chileno
        initRUTFormat: function() {
            $(document).on('input', 'input.rut', function() {
                let rut = $(this).val().replace(/[^0-9kK]/g, '');

                if (rut.length > 1) {
                    const dv = rut.slice(-1);
                    let numero = rut.slice(0, -1);

                    // Formatear número
                    numero = numero.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    $(this).val(numero + '-' + dv.toUpperCase());
                }
            });

            // Validar RUT al salir del campo
            $(document).on('blur', 'input.rut', function() {
                const rut = $(this).val();
                if (rut && !ConectaERP.validarRUT(rut)) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">RUT inválido</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
        },

        // Validar RUT chileno
        validarRUT: function(rut) {
            rut = rut.replace(/[^0-9kK]/g, '');

            if (rut.length < 2) return false;

            const dv = rut.slice(-1).toUpperCase();
            const numero = rut.slice(0, -1);

            let suma = 0;
            let multiplo = 2;

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

        // Formatear campos de moneda
        initMoneyFormat: function() {
            $(document).on('input', 'input.money', function() {
                let valor = $(this).val().replace(/[^\d]/g, '');
                if (valor) {
                    valor = parseInt(valor).toLocaleString('es-CL');
                    $(this).val(valor);
                }
            });
        },

        // Formatear número a moneda
        formatMoney: function(amount) {
            return '$ ' + parseInt(amount).toLocaleString('es-CL');
        },

        // Parsear moneda a número
        parseMoney: function(moneyString) {
            return parseInt(moneyString.replace(/[^\d]/g, '')) || 0;
        },

        // Mostrar loading overlay
        showLoading: function() {
            if (!$('.loading-overlay').length) {
                $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
            }
        },

        // Ocultar loading overlay
        hideLoading: function() {
            $('.loading-overlay').remove();
        },

        // Mostrar mensaje de éxito
        success: function(message, title = 'Éxito') {
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: title,
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        },

        // Mostrar mensaje de error
        error: function(message, title = 'Error') {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message
                });
            } else {
                alert(message);
            }
        },

        // Mostrar confirmación
        confirm: function(message, callback, title = 'Confirmación') {
            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: title,
                    text: message,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            } else {
                if (confirm(message) && typeof callback === 'function') {
                    callback();
                }
            }
        },

        // Enviar formulario AJAX
        submitAjaxForm: function($form) {
            const self = this;
            const url = $form.attr('action');
            const method = $form.attr('method') || 'POST';
            const formData = new FormData($form[0]);

            self.showLoading();

            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        self.success(response.message);

                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        } else if ($form.data('reload')) {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        self.error(response.message);
                    }
                },
                error: function(xhr) {
                    self.hideLoading();
                    self.error('Error al procesar la solicitud');
                    console.error(xhr);
                }
            });
        },

        // Realizar petición AJAX
        ajax: function(url, data, method = 'POST', successCallback, errorCallback) {
            const self = this;

            $.ajax({
                url: url,
                method: method,
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (typeof successCallback === 'function') {
                        successCallback(response);
                    }
                },
                error: function(xhr) {
                    if (typeof errorCallback === 'function') {
                        errorCallback(xhr);
                    } else {
                        self.error('Error en la petición AJAX');
                    }
                }
            });
        }
    };

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        ConectaERP.init();
    });

})();
