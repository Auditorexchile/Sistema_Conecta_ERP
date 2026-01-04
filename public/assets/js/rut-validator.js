/**
 * Conecta ERP - Validador de Identificadores por País
 * Formateo automático y validación de RUT, CUIT, NIT, RFC, etc.
 */

/**
 * Validar y formatear RUT chileno (CRÍTICO)
 * Acepta: 15895771K, 15895771-K, 15.895.771-K
 * Retorna: 15.895.771-K (siempre formateado)
 */
function validateAndFormatRUT(rut) {
    if (!rut) return { valid: false, formatted: '', message: 'RUT requerido' };

    // Limpiar RUT (quitar puntos, guiones, espacios)
    let cleanRUT = rut.toString().replace(/[.\-\s]/g, '').toUpperCase();

    // Validar largo mínimo
    if (cleanRUT.length < 2) {
        return { valid: false, formatted: rut, message: 'RUT muy corto' };
    }

    // Separar número y dígito verificador
    const dv = cleanRUT.slice(-1);
    const number = cleanRUT.slice(0, -1);

    // Validar que el número sea numérico
    if (!/^\d+$/.test(number)) {
        return { valid: false, formatted: rut, message: 'RUT debe contener solo números' };
    }

    // Validar que DV sea número o K
    if (!/^[0-9K]$/.test(dv)) {
        return { valid: false, formatted: rut, message: 'Dígito verificador inválido' };
    }

    // Calcular dígito verificador esperado
    let sum = 0;
    let multiplier = 2;

    for (let i = number.length - 1; i >= 0; i--) {
        sum += parseInt(number[i]) * multiplier;
        multiplier = multiplier === 7 ? 2 : multiplier + 1;
    }

    let expectedDV = 11 - (sum % 11);
    let expectedDVStr;

    if (expectedDV === 11) {
        expectedDVStr = '0';
    } else if (expectedDV === 10) {
        expectedDVStr = 'K';
    } else {
        expectedDVStr = expectedDV.toString();
    }

    // Formatear RUT (XX.XXX.XXX-X)
    let formattedNumber = number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    let formatted = `${formattedNumber}-${dv}`;

    // Validar DV
    const valid = dv === expectedDVStr;

    return {
        valid,
        formatted,
        message: valid ? 'RUT válido' : 'RUT inválido - dígito verificador incorrecto',
        dv: dv,
        expectedDV: expectedDVStr
    };
}

/**
 * Validar CUIT argentino
 */
function validateAndFormatCUIT(cuit) {
    if (!cuit) return { valid: false, formatted: '', message: 'CUIT requerido' };

    let clean = cuit.replace(/[\-\s]/g, '');

    if (clean.length !== 11) {
        return { valid: false, formatted: cuit, message: 'CUIT debe tener 11 dígitos' };
    }

    if (!/^\d+$/.test(clean)) {
        return { valid: false, formatted: cuit, message: 'CUIT debe contener solo números' };
    }

    // Formatear XX-XXXXXXXX-X
    const formatted = `${clean.substring(0, 2)}-${clean.substring(2, 10)}-${clean.substring(10)}`;

    // Validación básica (sin algoritmo completo)
    return { valid: true, formatted, message: 'CUIT válido' };
}

/**
 * Validar RUC peruano
 */
function validateAndFormatRUC(ruc) {
    if (!ruc) return { valid: false, formatted: '', message: 'RUC requerido' };

    let clean = ruc.replace(/[\s]/g, '');

    if (clean.length !== 11) {
        return { valid: false, formatted: ruc, message: 'RUC debe tener 11 dígitos' };
    }

    if (!/^\d+$/.test(clean)) {
        return { valid: false, formatted: ruc, message: 'RUC debe contener solo números' };
    }

    return { valid: true, formatted: clean, message: 'RUC válido' };
}

/**
 * Validar NIT colombiano
 */
function validateAndFormatNIT(nit) {
    if (!nit) return { valid: false, formatted: '', message: 'NIT requerido' };

    let clean = nit.replace(/[\-\s]/g, '');

    if (clean.length < 9 || clean.length > 11) {
        return { valid: false, formatted: nit, message: 'NIT debe tener entre 9 y 11 dígitos' };
    }

    if (!/^\d+$/.test(clean)) {
        return { valid: false, formatted: nit, message: 'NIT debe contener solo números' };
    }

    // Formatear XXXXXXXX-X
    const dv = clean.slice(-1);
    const number = clean.slice(0, -1);
    const formatted = `${number}-${dv}`;

    return { valid: true, formatted, message: 'NIT válido' };
}

/**
 * Validar RFC mexicano
 */
function validateAndFormatRFC(rfc) {
    if (!rfc) return { valid: false, formatted: '', message: 'RFC requerido' };

    let clean = rfc.toUpperCase().replace(/[\s]/g, '');

    // RFC puede ser de 12 o 13 caracteres
    if (clean.length < 12 || clean.length > 13) {
        return { valid: false, formatted: rfc, message: 'RFC debe tener 12 o 13 caracteres' };
    }

    // Validación básica de formato
    if (!/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/.test(clean)) {
        return { valid: false, formatted: rfc, message: 'Formato de RFC inválido' };
    }

    return { valid: true, formatted: clean, message: 'RFC válido' };
}

/**
 * Validar CNPJ brasileño
 */
function validateAndFormatCNPJ(cnpj) {
    if (!cnpj) return { valid: false, formatted: '', message: 'CNPJ requerido' };

    let clean = cnpj.replace(/[.\-\/\s]/g, '');

    if (clean.length !== 14) {
        return { valid: false, formatted: cnpj, message: 'CNPJ debe tener 14 dígitos' };
    }

    if (!/^\d+$/.test(clean)) {
        return { valid: false, formatted: cnpj, message: 'CNPJ debe contener solo números' };
    }

    // Formatear XX.XXX.XXX/0001-XX
    const formatted = `${clean.substring(0, 2)}.${clean.substring(2, 5)}.${clean.substring(5, 8)}/${clean.substring(8, 12)}-${clean.substring(12)}`;

    return { valid: true, formatted, message: 'CNPJ válido' };
}

/**
 * Validar EIN estadounidense
 */
function validateAndFormatEIN(ein) {
    if (!ein) return { valid: false, formatted: '', message: 'EIN requerido' };

    let clean = ein.replace(/[\-\s]/g, '');

    if (clean.length !== 9) {
        return { valid: false, formatted: ein, message: 'EIN debe tener 9 dígitos' };
    }

    if (!/^\d+$/.test(clean)) {
        return { valid: false, formatted: ein, message: 'EIN debe contener solo números' };
    }

    // Formatear XX-XXXXXXX
    const formatted = `${clean.substring(0, 2)}-${clean.substring(2)}`;

    return { valid: true, formatted, message: 'EIN válido' };
}

/**
 * Validar identificador genérico (otros países)
 */
function validateAndFormatGeneric(identifier, type) {
    if (!identifier) return { valid: false, formatted: '', message: `${type} requerido` };

    // Validación básica
    const clean = identifier.toUpperCase().replace(/[\s]/g, '');

    if (clean.length < 3) {
        return { valid: false, formatted: identifier, message: `${type} muy corto` };
    }

    return { valid: true, formatted: clean, message: `${type} válido` };
}

/**
 * Función principal: Validar y formatear según país
 */
function validateIdentifier(identifier, countryCode) {
    const config = getCountryConfig(countryCode);

    if (!config) {
        return { valid: false, formatted: identifier, message: 'País no soportado' };
    }

    switch (countryCode.toUpperCase()) {
        case 'CL':
            return validateAndFormatRUT(identifier);
        case 'AR':
            return validateAndFormatCUIT(identifier);
        case 'PE':
            return validateAndFormatRUC(identifier);
        case 'CO':
            return validateAndFormatNIT(identifier);
        case 'MX':
            return validateAndFormatRFC(identifier);
        case 'BR':
            return validateAndFormatCNPJ(identifier);
        case 'US':
            return validateAndFormatEIN(identifier);
        default:
            return validateAndFormatGeneric(identifier, config.identifierType);
    }
}

/**
 * Auto-formatear input mientras se escribe
 */
function autoFormatIdentifierInput(input) {
    const countryCode = input.getAttribute('data-country') || 'CL';

    input.addEventListener('input', function(e) {
        const cursorPosition = this.selectionStart;
        const oldValue = this.value;
        const oldLength = oldValue.length;

        // Validar y formatear
        const result = validateIdentifier(this.value, countryCode);

        if (result.formatted) {
            this.value = result.formatted;

            // Ajustar posición del cursor
            const newLength = this.value.length;
            const diff = newLength - oldLength;
            const newPosition = cursorPosition + diff;
            this.setSelectionRange(newPosition, newPosition);
        }

        // Actualizar clase visual
        if (this.value.length > 0) {
            if (result.valid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }

        // Actualizar mensaje de error
        const feedback = this.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = result.message;
        }
    });

    // Al perder foco, validar final
    input.addEventListener('blur', function() {
        if (this.value) {
            const result = validateIdentifier(this.value, countryCode);
            this.value = result.formatted;
        }
    });
}

/**
 * Inicializar validadores en toda la página
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-formatear todos los inputs con data-identifier="true"
    const identifierInputs = document.querySelectorAll('input[data-identifier="true"]');
    identifierInputs.forEach(input => {
        autoFormatIdentifierInput(input);
    });
});
