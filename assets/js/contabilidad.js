/**
 * JavaScript para el módulo de Contabilidad
 */

var Contabilidad = {

    // Inicializar módulo
    init: function() {
        this.initComprobantes();
        this.initPlanCuentas();
        this.initF29();
    },

    // ======================================
    // COMPROBANTES CONTABLES
    // ======================================

    initComprobantes: function() {
        const self = this;

        // Agregar línea de detalle
        $(document).on('click', '.btn-agregar-linea', function() {
            self.agregarLineaDetalle();
        });

        // Eliminar línea de detalle
        $(document).on('click', '.btn-eliminar-linea', function() {
            $(this).closest('tr').remove();
            self.calcularTotales();
        });

        // Calcular al cambiar debe/haber
        $(document).on('input', '.debe-input, .haber-input', function() {
            self.calcularTotales();
        });

        // Autocompletar cuenta contable
        if ($.fn.select2) {
            $('.cuenta-select').select2({
                theme: 'bootstrap-5',
                ajax: {
                    url: '?module=contabilidad&action=buscar_cuentas_ajax',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items
                        };
                    }
                },
                minimumInputLength: 1,
                placeholder: 'Buscar cuenta...'
            });
        }

        // Validar comprobante antes de guardar
        $(document).on('click', '.btn-contabilizar', function(e) {
            e.preventDefault();
            if (self.validarComprobante()) {
                $(this).closest('form').submit();
            }
        });
    },

    // Agregar línea de detalle al comprobante
    agregarLineaDetalle: function() {
        const tbody = $('#detalle-comprobante tbody');
        const numLinea = tbody.find('tr').length + 1;

        const html = `
            <tr>
                <td class="text-center">${numLinea}</td>
                <td>
                    <select name="detalle[${numLinea}][id_cuenta]" class="form-select form-select-sm cuenta-select" required>
                        <option value="">Seleccionar...</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="detalle[${numLinea}][debe]" class="form-control form-control-sm text-end debe-input" value="0" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" name="detalle[${numLinea}][haber]" class="form-control form-control-sm text-end haber-input" value="0" step="0.01" min="0">
                </td>
                <td>
                    <select name="detalle[${numLinea}][id_centro_costo]" class="form-select form-select-sm">
                        <option value="">Sin CC</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="detalle[${numLinea}][glosa_detalle]" class="form-control form-control-sm" maxlength="500">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-linea">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.append(html);

        // Re-inicializar Select2 en la nueva línea
        tbody.find('tr:last .cuenta-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    },

    // Calcular totales del comprobante
    calcularTotales: function() {
        let totalDebe = 0;
        let totalHaber = 0;

        $('.debe-input').each(function() {
            const valor = parseFloat($(this).val()) || 0;
            totalDebe += valor;
        });

        $('.haber-input').each(function() {
            const valor = parseFloat($(this).val()) || 0;
            totalHaber += valor;
        });

        const diferencia = totalDebe - totalHaber;

        $('#total-debe').text(ConectaERP.formatMoney(totalDebe));
        $('#total-haber').text(ConectaERP.formatMoney(totalHaber));
        $('#diferencia').text(ConectaERP.formatMoney(Math.abs(diferencia)));

        if (diferencia === 0) {
            $('#diferencia').removeClass('diferencia-error').addClass('diferencia-ok');
            $('#diferencia-badge').removeClass('bg-danger').addClass('bg-success').text('Cuadrado');
        } else {
            $('#diferencia').removeClass('diferencia-ok').addClass('diferencia-error');
            $('#diferencia-badge').removeClass('bg-success').addClass('bg-danger').text('Descuadrado');
        }
    },

    // Validar comprobante
    validarComprobante: function() {
        // Verificar que hay al menos 2 líneas
        const numLineas = $('#detalle-comprobante tbody tr').length;
        if (numLineas < 2) {
            ConectaERP.error('El comprobante debe tener al menos 2 líneas');
            return false;
        }

        // Verificar que está cuadrado
        const diferencia = parseFloat($('#diferencia').text().replace(/[^\d]/g, '')) || 0;
        if (diferencia !== 0) {
            ConectaERP.error('El comprobante debe estar cuadrado (Debe = Haber)');
            return false;
        }

        // Verificar que todas las líneas tienen cuenta seleccionada
        let cuentasValidas = true;
        $('.cuenta-select').each(function() {
            if (!$(this).val()) {
                cuentasValidas = false;
                $(this).addClass('is-invalid');
            }
        });

        if (!cuentasValidas) {
            ConectaERP.error('Todas las líneas deben tener una cuenta seleccionada');
            return false;
        }

        return true;
    },

    // ======================================
    // PLAN DE CUENTAS
    // ======================================

    initPlanCuentas: function() {
        const self = this;

        // Expandir/colapsar cuentas
        $(document).on('click', '.cuenta-item', function(e) {
            if ($(e.target).is('i.fa-chevron-down, i.fa-chevron-right')) {
                $(this).find('i.fa-chevron-down, i.fa-chevron-right').toggleClass('fa-chevron-down fa-chevron-right');
                $(this).nextUntil('.cuenta-nivel-' + $(this).data('nivel')).toggle();
            }
        });

        // Calcular código de cuenta según nivel padre
        $('#id_cuenta_padre').on('change', function() {
            const idPadre = $(this).val();
            if (idPadre) {
                // AJAX para obtener siguiente código
                ConectaERP.ajax(
                    '?module=contabilidad&action=obtener_siguiente_codigo',
                    { id_padre: idPadre },
                    'POST',
                    function(response) {
                        if (response.success) {
                            $('#codigo_cuenta').val(response.codigo);
                        }
                    }
                );
            }
        });
    },

    // ======================================
    // FORMULARIO 29
    // ======================================

    initF29: function() {
        const self = this;

        // Recalcular F29
        $(document).on('click', '.btn-recalcular-f29', function() {
            ConectaERP.showLoading();

            const periodo = $('#periodo').val();
            const empresa = $('#id_empresa').val();

            ConectaERP.ajax(
                '?module=contabilidad&action=recalcular_f29',
                { periodo: periodo, id_empresa: empresa },
                'POST',
                function(response) {
                    ConectaERP.hideLoading();
                    if (response.success) {
                        self.actualizarValoresF29(response.data);
                        ConectaERP.success('F29 recalculado correctamente');
                    } else {
                        ConectaERP.error(response.message);
                    }
                }
            );
        });

        // Calcular automáticamente totales al cambiar valores
        $(document).on('input', '.f29-valor input', function() {
            self.calcularF29();
        });
    },

    // Actualizar valores del F29
    actualizarValoresF29: function(data) {
        for (const key in data) {
            $(`#${key}`).val(data[key]);
        }
        this.calcularF29();
    },

    // Calcular totales del F29
    calcularF29: function() {
        // Débito Fiscal
        const debitoFiscal = parseFloat($('#codigo_512_debito_fiscal').val()) || 0;

        // Crédito Fiscal
        const creditoFiscal = parseFloat($('#codigo_521_credito_fiscal').val()) || 0;
        const creditoUsoComun = parseFloat($('#codigo_522_credito_uso_comun').val()) || 0;
        const creditoActivoFijo = parseFloat($('#codigo_524_credito_activo_fijo').val()) || 0;
        const remanenteAnterior = parseFloat($('#codigo_528_remanente_mes_anterior').val()) || 0;

        const totalCredito = creditoFiscal + creditoUsoComun + creditoActivoFijo + remanenteAnterior;
        $('#codigo_538_total_credito').val(totalCredito);

        // IVA Determinado
        const ivaDeterminado = debitoFiscal - totalCredito;
        $('#codigo_562_iva_determinado').val(Math.max(0, ivaDeterminado));

        // Remanente
        const remanente = totalCredito - debitoFiscal;
        $('#codigo_566_remanente_credito').val(Math.max(0, remanente));

        // Retención Honorarios
        const retencionHonorarios = parseFloat($('#codigo_152_retencion_honorarios').val()) || 0;

        // PPM
        const ppm = parseFloat($('#codigo_36_ppm').val()) || 0;

        // Total a Pagar
        const totalPagar = Math.max(0, ivaDeterminado) + retencionHonorarios + ppm;
        $('#codigo_91_total_a_pagar').val(totalPagar);

        // Actualizar visualización
        $('.total-pagar .valor').text(ConectaERP.formatMoney(totalPagar));
        $('.total-favor .valor').text(ConectaERP.formatMoney(Math.max(0, remanente)));
    },

    // ======================================
    // LIBROS CONTABLES
    // ======================================

    exportarLibro: function(tipo, formato) {
        ConectaERP.showLoading();

        const params = {
            tipo: tipo,
            formato: formato,
            fecha_desde: $('#fecha_desde').val(),
            fecha_hasta: $('#fecha_hasta').val(),
            id_empresa: $('#id_empresa').val()
        };

        const queryString = $.param(params);
        const url = `?module=contabilidad&action=exportar_libro&${queryString}`;

        window.open(url, '_blank');

        setTimeout(function() {
            ConectaERP.hideLoading();
        }, 2000);
    },

    // ======================================
    // UTILIDADES
    // ======================================

    // Obtener nombre de cuenta por ID
    obtenerNombreCuenta: function(idCuenta, callback) {
        ConectaERP.ajax(
            '?module=contabilidad&action=obtener_cuenta',
            { id_cuenta: idCuenta },
            'POST',
            function(response) {
                if (response.success && typeof callback === 'function') {
                    callback(response.data);
                }
            }
        );
    }
};

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    Contabilidad.init();
});
