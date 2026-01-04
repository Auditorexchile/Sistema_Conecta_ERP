<?php
/**
 * Conecta ERP - Configuración de Idiomas
 * 10 idiomas soportados en el sistema
 */

return [
    'es' => [
        'code' => 'es',
        'name' => 'Español',
        'native_name' => 'Español',
        'locale' => 'es_ES',
        'flag' => '🇪🇸',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => true,
        'countries' => ['ES', 'CL', 'AR', 'PE', 'CO', 'MX'],
    ],

    'en' => [
        'code' => 'en',
        'name' => 'English',
        'native_name' => 'English',
        'locale' => 'en_US',
        'flag' => '🇺🇸',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['US', 'GB', 'CA', 'AU'],
    ],

    'pt' => [
        'code' => 'pt',
        'name' => 'Português',
        'native_name' => 'Português',
        'locale' => 'pt_BR',
        'flag' => '🇧🇷',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['BR', 'PT'],
    ],

    'fr' => [
        'code' => 'fr',
        'name' => 'Français',
        'native_name' => 'Français',
        'locale' => 'fr_FR',
        'flag' => '🇫🇷',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['FR', 'BE', 'CH', 'CA'],
    ],

    'de' => [
        'code' => 'de',
        'name' => 'Deutsch',
        'native_name' => 'Deutsch',
        'locale' => 'de_DE',
        'flag' => '🇩🇪',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['DE', 'AT', 'CH'],
    ],

    'it' => [
        'code' => 'it',
        'name' => 'Italiano',
        'native_name' => 'Italiano',
        'locale' => 'it_IT',
        'flag' => '🇮🇹',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['IT', 'CH'],
    ],

    'zh' => [
        'code' => 'zh',
        'name' => 'Chinese',
        'native_name' => '中文',
        'locale' => 'zh_CN',
        'flag' => '🇨🇳',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['CN', 'TW', 'HK'],
    ],

    'ja' => [
        'code' => 'ja',
        'name' => 'Japanese',
        'native_name' => '日本語',
        'locale' => 'ja_JP',
        'flag' => '🇯🇵',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['JP'],
    ],

    'ko' => [
        'code' => 'ko',
        'name' => 'Korean',
        'native_name' => '한국어',
        'locale' => 'ko_KR',
        'flag' => '🇰🇷',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['KR'],
    ],

    'hi' => [
        'code' => 'hi',
        'name' => 'Hindi',
        'native_name' => 'हिन्दी',
        'locale' => 'hi_IN',
        'flag' => '🇮🇳',
        'direction' => 'ltr',
        'enabled' => true,
        'default' => false,
        'countries' => ['IN'],
    ],
];
