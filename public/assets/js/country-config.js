/**
 * Conecta ERP - Configuración de Países
 * Configuración de 12 países con validación de identificadores
 */

const COUNTRIES_CONFIG = {
    CL: {
        code: 'CL',
        name: 'Chile',
        identifierType: 'RUT',
        identifierFormat: 'XX.XXX.XXX-X',
        identifierExample: '12.345.678-9',
        currency: 'CLP',
        currencySymbol: '$',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Santiago',
        phoneCode: '+56',
        taxRate: 19.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    AR: {
        code: 'AR',
        name: 'Argentina',
        identifierType: 'CUIT',
        identifierFormat: 'XX-XXXXXXXX-X',
        identifierExample: '20-12345678-9',
        currency: 'ARS',
        currencySymbol: '$',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Buenos_Aires',
        phoneCode: '+54',
        taxRate: 21.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    PE: {
        code: 'PE',
        name: 'Perú',
        identifierType: 'RUC',
        identifierFormat: 'XXXXXXXXXXX',
        identifierExample: '20123456789',
        currency: 'PEN',
        currencySymbol: 'S/',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Lima',
        phoneCode: '+51',
        taxRate: 18.00,
        decimalSeparator: '.',
        thousandsSeparator: ','
    },
    CO: {
        code: 'CO',
        name: 'Colombia',
        identifierType: 'NIT',
        identifierFormat: 'XXXXXXXX-X',
        identifierExample: '12345678-9',
        currency: 'COP',
        currencySymbol: '$',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Bogota',
        phoneCode: '+57',
        taxRate: 19.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    MX: {
        code: 'MX',
        name: 'México',
        identifierType: 'RFC',
        identifierFormat: 'XXXX000000XXX',
        identifierExample: 'ABC123456XYZ',
        currency: 'MXN',
        currencySymbol: '$',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Mexico_City',
        phoneCode: '+52',
        taxRate: 16.00,
        decimalSeparator: '.',
        thousandsSeparator: ','
    },
    BR: {
        code: 'BR',
        name: 'Brasil',
        identifierType: 'CNPJ',
        identifierFormat: 'XX.XXX.XXX/0001-XX',
        identifierExample: '12.345.678/0001-90',
        currency: 'BRL',
        currencySymbol: 'R$',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'America/Sao_Paulo',
        phoneCode: '+55',
        taxRate: 18.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    US: {
        code: 'US',
        name: 'Estados Unidos',
        identifierType: 'EIN',
        identifierFormat: 'XX-XXXXXXX',
        identifierExample: '12-3456789',
        currency: 'USD',
        currencySymbol: '$',
        dateFormat: 'MM/DD/YYYY',
        timezone: 'America/New_York',
        phoneCode: '+1',
        taxRate: 0.00,
        decimalSeparator: '.',
        thousandsSeparator: ','
    },
    ES: {
        code: 'ES',
        name: 'España',
        identifierType: 'CIF/NIF',
        identifierFormat: 'X0000000X',
        identifierExample: 'A12345678',
        currency: 'EUR',
        currencySymbol: '€',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'Europe/Madrid',
        phoneCode: '+34',
        taxRate: 21.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    FR: {
        code: 'FR',
        name: 'Francia',
        identifierType: 'SIRET',
        identifierFormat: 'XXXXXXXXXXXXXX',
        identifierExample: '12345678901234',
        currency: 'EUR',
        currencySymbol: '€',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'Europe/Paris',
        phoneCode: '+33',
        taxRate: 20.00,
        decimalSeparator: ',',
        thousandsSeparator: ' '
    },
    DE: {
        code: 'DE',
        name: 'Alemania',
        identifierType: 'USt-IdNr',
        identifierFormat: 'DEXXXXXXXXX',
        identifierExample: 'DE123456789',
        currency: 'EUR',
        currencySymbol: '€',
        dateFormat: 'DD.MM.YYYY',
        timezone: 'Europe/Berlin',
        phoneCode: '+49',
        taxRate: 19.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    IT: {
        code: 'IT',
        name: 'Italia',
        identifierType: 'Partita IVA',
        identifierFormat: 'XXXXXXXXXXX',
        identifierExample: '12345678901',
        currency: 'EUR',
        currencySymbol: '€',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'Europe/Rome',
        phoneCode: '+39',
        taxRate: 22.00,
        decimalSeparator: ',',
        thousandsSeparator: '.'
    },
    GB: {
        code: 'GB',
        name: 'Reino Unido',
        identifierType: 'VAT Number',
        identifierFormat: 'GBXXXXXXXXX',
        identifierExample: 'GB123456789',
        currency: 'GBP',
        currencySymbol: '£',
        dateFormat: 'DD/MM/YYYY',
        timezone: 'Europe/London',
        phoneCode: '+44',
        taxRate: 20.00,
        decimalSeparator: '.',
        thousandsSeparator: ','
    }
};

/**
 * Obtener configuración de país
 */
function getCountryConfig(countryCode) {
    return COUNTRIES_CONFIG[countryCode.toUpperCase()] || null;
}

/**
 * Obtener lista de países
 */
function getCountriesList() {
    return Object.values(COUNTRIES_CONFIG);
}

/**
 * Cambiar configuración al seleccionar país
 */
function onCountryChange(countryCode, updateElements = {}) {
    const config = getCountryConfig(countryCode);
    if (!config) return;

    // Actualizar moneda si se proporciona elemento
    if (updateElements.currency) {
        const currencyElement = document.getElementById(updateElements.currency);
        if (currencyElement) {
            currencyElement.value = config.currency;
            // Trigger change event
            currencyElement.dispatchEvent(new Event('change'));
        }
    }

    // Actualizar zona horaria
    if (updateElements.timezone) {
        const timezoneElement = document.getElementById(updateElements.timezone);
        if (timezoneElement) {
            timezoneElement.value = config.timezone;
        }
    }

    // Actualizar placeholder de identificador
    if (updateElements.identifier) {
        const identifierElement = document.getElementById(updateElements.identifier);
        if (identifierElement) {
            identifierElement.placeholder = config.identifierExample;
            identifierElement.setAttribute('data-country', countryCode);
            identifierElement.setAttribute('data-type', config.identifierType);

            // Actualizar label
            const label = document.querySelector(`label[for="${updateElements.identifier}"]`);
            if (label) {
                label.textContent = config.identifierType;
            }
        }
    }

    // Actualizar formato de fecha
    if (updateElements.dateFormat) {
        const dateElement = document.getElementById(updateElements.dateFormat);
        if (dateElement) {
            dateElement.setAttribute('data-format', config.dateFormat);
        }
    }

    return config;
}
