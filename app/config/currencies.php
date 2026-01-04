<?php
/**
 * Conecta ERP - Configuración de Monedas
 * Todas las monedas soportadas en el sistema
 */

return [
    'CLP' => [
        'code' => 'CLP',
        'name' => 'Peso Chileno',
        'symbol' => '$',
        'decimal_places' => 0,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before', // before o after
        'countries' => ['CL'],
        'enabled' => true,
    ],

    'UF' => [
        'code' => 'UF',
        'name' => 'Unidad de Fomento',
        'symbol' => 'UF',
        'decimal_places' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before',
        'countries' => ['CL'],
        'enabled' => true,
    ],

    'UTM' => [
        'code' => 'UTM',
        'name' => 'Unidad Tributaria Mensual',
        'symbol' => 'UTM',
        'decimal_places' => 0,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before',
        'countries' => ['CL'],
        'enabled' => true,
    ],

    'ARS' => [
        'code' => 'ARS',
        'name' => 'Peso Argentino',
        'symbol' => '$',
        'decimal_places' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before',
        'countries' => ['AR'],
        'enabled' => true,
    ],

    'PEN' => [
        'code' => 'PEN',
        'name' => 'Sol Peruano',
        'symbol' => 'S/',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['PE'],
        'enabled' => true,
    ],

    'COP' => [
        'code' => 'COP',
        'name' => 'Peso Colombiano',
        'symbol' => '$',
        'decimal_places' => 0,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before',
        'countries' => ['CO'],
        'enabled' => true,
    ],

    'MXN' => [
        'code' => 'MXN',
        'name' => 'Peso Mexicano',
        'symbol' => '$',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['MX'],
        'enabled' => true,
    ],

    'BRL' => [
        'code' => 'BRL',
        'name' => 'Real Brasileño',
        'symbol' => 'R$',
        'decimal_places' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'before',
        'countries' => ['BR'],
        'enabled' => true,
    ],

    'USD' => [
        'code' => 'USD',
        'name' => 'Dólar Estadounidense',
        'symbol' => '$',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['US'],
        'enabled' => true,
    ],

    'CAD' => [
        'code' => 'CAD',
        'name' => 'Dólar Canadiense',
        'symbol' => 'C$',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['CA'],
        'enabled' => true,
    ],

    'EUR' => [
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
        'decimal_places' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'symbol_position' => 'after',
        'countries' => ['ES', 'FR', 'DE', 'IT', 'PT', 'NL'],
        'enabled' => true,
    ],

    'GBP' => [
        'code' => 'GBP',
        'name' => 'Libra Esterlina',
        'symbol' => '£',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['GB'],
        'enabled' => true,
    ],

    'JPY' => [
        'code' => 'JPY',
        'name' => 'Yen Japonés',
        'symbol' => '¥',
        'decimal_places' => 0,
        'decimal_separator' => '',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['JP'],
        'enabled' => true,
    ],

    'CNY' => [
        'code' => 'CNY',
        'name' => 'Yuan Chino',
        'symbol' => '¥',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['CN'],
        'enabled' => true,
    ],

    'INR' => [
        'code' => 'INR',
        'name' => 'Rupia India',
        'symbol' => '₹',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before',
        'countries' => ['IN'],
        'enabled' => true,
    ],
];
