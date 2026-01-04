<?php
/**
 * Conecta ERP - Register Completo (5 Pasos)
 * Registro profesional con validación multipaís
 */

require_once __DIR__ . '/../app/core/bootstrap.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('/app/dashboard/dashboard.php');
}

$error = '';
$success = '';
$currentStep = 1;

// Cargar configuraciones para los selects
$countries = require APP_PATH . '/config/countries.php';
$currencies = require APP_PATH . '/config/currencies.php';
$languages = require APP_PATH . '/config/languages.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentStep = (int)($_POST['current_step'] ?? 1);

    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Validar todos los datos
        $pais = sanitize($_POST['pais'] ?? '', 'string');
        $identificadorTributario = sanitize($_POST['identificador_tributario'] ?? '', 'string');
        $razonSocial = sanitize($_POST['razon_social'] ?? '', 'string');
        $nombreFantasia = sanitize($_POST['nombre_fantasia'] ?? '', 'string');
        $giro = sanitize($_POST['giro'] ?? '', 'string');
        $tipoEmpresa = sanitize($_POST['tipo_empresa'] ?? 'empresa', 'string');
        $direccion = sanitize($_POST['direccion'] ?? '', 'string');
        $comuna = sanitize($_POST['comuna'] ?? '', 'string');
        $region = sanitize($_POST['region'] ?? '', 'string');

        // Representante legal
        $nombres = sanitize($_POST['nombres'] ?? '', 'string');
        $apellidoPaterno = sanitize($_POST['apellido_paterno'] ?? '', 'string');
        $apellidoMaterno = sanitize($_POST['apellido_materno'] ?? '', 'string');
        $identificadorPersonal = sanitize($_POST['identificador_personal'] ?? '', 'string');
        $emailRepresentante = sanitize($_POST['email_representante'] ?? '', 'email');
        $telefonoMovil = sanitize($_POST['telefono_movil'] ?? '', 'string');

        // Seguridad
        $emailAcceso = sanitize($_POST['email_acceso'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Plan
        $idPlan = (int)($_POST['id_plan'] ?? 1);

        // Términos
        $aceptaTerminos = isset($_POST['acepta_terminos']);
        $aceptaPrivacidad = isset($_POST['acepta_privacidad']);

        // Validaciones
        if (empty($pais) || empty($identificadorTributario) || empty($razonSocial)) {
            $error = 'Datos de empresa incompletos';
        } elseif (empty($nombres) || empty($apellidoPaterno) || empty($emailRepresentante)) {
            $error = 'Datos del representante legal incompletos';
        } elseif (empty($emailAcceso) || empty($password)) {
            $error = 'Datos de acceso incompletos';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Las contraseñas no coinciden';
        } elseif (!$aceptaTerminos || !$aceptaPrivacidad) {
            $error = 'Debe aceptar términos y condiciones';
        } else {
            // Verificar que no exista empresa con ese identificador
            $exists = $db->exists('empresas',
                'identificador_tributario = :id AND codigo_pais = :pais AND deleted_at IS NULL',
                ['id' => $identificadorTributario, 'pais' => $pais]
            );

            if ($exists) {
                $error = 'Ya existe una empresa registrada con ese identificador tributario';
            } else {
                // Verificar email único
                $emailExists = $db->exists('usuarios_acceso',
                    'email = :email AND deleted_at IS NULL',
                    ['email' => $emailAcceso]
                );

                if ($emailExists) {
                    $error = 'El email ya está registrado';
                } else {
                    // Validar fortaleza de contraseña
                    $passwordValidation = $security->validatePasswordStrength($password);
                    if (!$passwordValidation['valid']) {
                        $error = implode('. ', $passwordValidation['errors']);
                    } else {
                        // TODO VÁLIDO - REGISTRAR
                        $db->beginTransaction();

                        try {
                            // Obtener configuración del país
                            $countryConfig = getCountryConfig($pais);

                            // 1. Crear empresa
                            $empresaId = $db->insert('empresas', [
                                'codigo_pais' => $pais,
                                'identificador_tributario' => $identificadorTributario,
                                'tipo_identificador' => $countryConfig['identifier_type'],
                                'razon_social' => $razonSocial,
                                'nombre_fantasia' => $nombreFantasia,
                                'giro_actividad' => $giro,
                                'tipo_empresa' => $tipoEmpresa,
                                'email_empresa' => $emailRepresentante,
                                'telefono_movil' => $telefonoMovil,
                                'estado' => 'trial',
                                'fecha_registro' => date('Y-m-d H:i:s'),
                                'fecha_trial_inicio' => date('Y-m-d H:i:s'),
                                'fecha_trial_fin' => date('Y-m-d H:i:s', strtotime('+14 days')),
                                'dias_trial_restantes' => 14,
                                'id_plan' => $idPlan
                            ]);

                            if (!$empresaId) {
                                throw new Exception('Error al crear empresa');
                            }

                            // 2. Crear dirección
                            $db->insert('empresas_direcciones', [
                                'id_empresa' => $empresaId,
                                'tipo_direccion' => 'legal',
                                'direccion' => $direccion,
                                'pais' => $pais,
                                'region_estado' => $region,
                                'comuna_ciudad' => $comuna,
                                'principal' => 1,
                                'activa' => 1
                            ]);

                            // 3. Crear usuario de acceso
                            $usuarioId = $db->insert('usuarios_acceso', [
                                'email' => $emailAcceso,
                                'password_hash' => $security->hashPassword($password),
                                'tipo_usuario' => 'admin_empresa',
                                'id_empresa' => $empresaId,
                                'activo' => 1,
                                'verificado' => 1,
                                'ultimo_cambio_password' => date('Y-m-d H:i:s')
                            ]);

                            if (!$usuarioId) {
                                throw new Exception('Error al crear usuario');
                            }

                            // 4. Crear representante legal
                            $db->insert('representantes_legales', [
                                'id_empresa' => $empresaId,
                                'id_usuario' => $usuarioId,
                                'nombres' => $nombres,
                                'apellido_paterno' => $apellidoPaterno,
                                'apellido_materno' => $apellidoMaterno,
                                'identificador_personal' => $identificadorPersonal,
                                'tipo_identificador' => $countryConfig['identifier_type'],
                                'pais_nacionalidad' => $pais,
                                'email' => $emailRepresentante,
                                'telefono_movil' => $telefonoMovil,
                                'activo' => 1
                            ]);

                            // 5. Crear configuración de empresa
                            $db->insert('configuracion_empresa', [
                                'id_empresa' => $empresaId,
                                'codigo_pais' => $pais,
                                'moneda_base' => $countryConfig['currency'],
                                'idioma_principal' => 'es',
                                'zona_horaria' => $countryConfig['timezone'],
                                'formato_fecha' => $countryConfig['date_format_php'],
                                'separador_decimal' => $countryConfig['decimal_separator'],
                                'separador_miles' => $countryConfig['thousands_separator'],
                                'tax_rate' => $countryConfig['tax_rate']
                            ]);

                            // 6. Crear control de trial
                            $db->insert('control_trial', [
                                'id_empresa' => $empresaId,
                                'fecha_inicio_trial' => date('Y-m-d H:i:s'),
                                'fecha_fin_trial' => date('Y-m-d H:i:s', strtotime('+14 days')),
                                'dias_totales' => 14,
                                'dias_usados' => 0,
                                'dias_restantes' => 14,
                                'trial_activo' => 1,
                                'trial_vencido' => 0
                            ]);

                            // 7. Crear suscripción trial
                            $plan = $db->queryOne('SELECT * FROM planes WHERE id = :id', ['id' => $idPlan]);

                            $db->insert('suscripciones', [
                                'id_empresa' => $empresaId,
                                'id_plan' => $idPlan,
                                'estado' => 'trial',
                                'fecha_inicio' => date('Y-m-d H:i:s'),
                                'fecha_trial_inicio' => date('Y-m-d H:i:s'),
                                'fecha_trial_fin' => date('Y-m-d H:i:s', strtotime('+14 days')),
                                'dias_trial_usados' => 0,
                                'es_trial' => 1,
                                'trial_vencido' => 0,
                                'periodo_pago' => 'mensual',
                                'monto' => $plan['precio_mensual'] ?? 0,
                                'moneda' => $countryConfig['currency'],
                                'renovacion_automatica' => 0
                            ]);

                            $db->commit();

                            // Registro exitoso - Auto login
                            $sessionHandler->create($usuarioId, [
                                'company_id' => $empresaId,
                                'company_name' => $razonSocial,
                                'company_status' => 'trial',
                                'country_code' => $pais
                            ]);

                            $_SESSION['user_type'] = 'admin_empresa';
                            $_SESSION['email'] = $emailAcceso;
                            $_SESSION['company_id'] = $empresaId;
                            $_SESSION['company_name'] = $razonSocial;
                            $_SESSION['company_status'] = 'trial';
                            $_SESSION['country_code'] = $pais;
                            $_SESSION['trial_days_remaining'] = 14;
                            $_SESSION['currency'] = $countryConfig['currency'];
                            $_SESSION['timezone'] = $countryConfig['timezone'];

                            // Redirigir al dashboard
                            redirect('/app/dashboard/dashboard.php');

                        } catch (Exception $e) {
                            $db->rollback();
                            $error = 'Error al registrar: ' . $e->getMessage();
                            logMessage('Error en registro: ' . $e->getMessage(), 'error');
                        }
                    }
                }
            }
        }
    } else {
        // Navegación entre pasos
        if (isset($_POST['next_step'])) {
            $currentStep = min(5, $currentStep + 1);
        } elseif (isset($_POST['prev_step'])) {
            $currentStep = max(1, $currentStep - 1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Conecta ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .steps-indicator {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            background: #f8f9fa;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 0;
        }
        .step:last-child::after {
            display: none;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            position: relative;
            z-index: 1;
            margin-bottom: 8px;
        }
        .step.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }
        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
        .register-body {
            padding: 40px;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
        }
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            background: #e0e0e0;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            width: 0%;
        }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-medium { background: #ffc107; width: 50%; }
        .strength-strong { background: #28a745; width: 75%; }
        .strength-very-strong { background: #0056b3; width: 100%; }
        .plan-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .plan-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .plan-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .plan-card input[type="radio"] {
            display: none;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h1>Crear Cuenta - Conecta ERP</h1>
            <p>Comienza tu prueba gratuita de 14 días</p>
        </div>

        <div class="steps-indicator">
            <div class="step <?= $currentStep >= 1 ? 'active' : '' ?> <?= $currentStep > 1 ? 'completed' : '' ?>">
                <div class="step-number">1</div>
                <div class="step-label">Empresa</div>
            </div>
            <div class="step <?= $currentStep >= 2 ? 'active' : '' ?> <?= $currentStep > 2 ? 'completed' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Representante</div>
            </div>
            <div class="step <?= $currentStep >= 3 ? 'active' : '' ?> <?= $currentStep > 3 ? 'completed' : '' ?>">
                <div class="step-number">3</div>
                <div class="step-label">Seguridad</div>
            </div>
            <div class="step <?= $currentStep >= 4 ? 'active' : '' ?> <?= $currentStep > 4 ? 'completed' : '' ?>">
                <div class="step-number">4</div>
                <div class="step-label">Plan</div>
            </div>
            <div class="step <?= $currentStep == 5 ? 'active' : '' ?>">
                <div class="step-number">5</div>
                <div class="step-label">Confirmar</div>
            </div>
        </div>

        <div class="register-body">
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="current_step" id="currentStep" value="<?= $currentStep ?>">

                <!-- PASO 1: Datos de la Empresa -->
                <div class="form-section <?= $currentStep == 1 ? 'active' : '' ?>" id="step1">
                    <h4 class="mb-4">Datos de la Empresa</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pais" class="form-label">País *</label>
                            <select class="form-select" id="pais" name="pais" required>
                                <option value="">Seleccione país</option>
                                <?php foreach ($countries as $code => $country): ?>
                                <option value="<?= $code ?>" <?= ($_POST['pais'] ?? DEFAULT_COUNTRY) == $code ? 'selected' : '' ?>>
                                    <?= h($country['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipo_empresa" class="form-label">Tipo de Entidad *</label>
                            <select class="form-select" name="tipo_empresa" required>
                                <option value="empresa">Empresa</option>
                                <option value="persona_natural">Persona Natural con Giro</option>
                                <option value="profesional_independiente">Profesional Independiente</option>
                                <option value="ong">ONG / Fundación</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="identificador_tributario" class="form-label">
                            <span id="identifier_label">RUT</span> *
                        </label>
                        <input type="text"
                               class="form-control"
                               id="identificador_tributario"
                               name="identificador_tributario"
                               placeholder="12.345.678-9"
                               data-identifier="true"
                               data-country="CL"
                               value="<?= h($_POST['identificador_tributario'] ?? '') ?>"
                               required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="razon_social" class="form-label">Razón Social *</label>
                        <input type="text"
                               class="form-control"
                               name="razon_social"
                               value="<?= h($_POST['razon_social'] ?? '') ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="nombre_fantasia" class="form-label">Nombre Fantasía / Comercial</label>
                        <input type="text"
                               class="form-control"
                               name="nombre_fantasia"
                               value="<?= h($_POST['nombre_fantasia'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="giro" class="form-label">Giro / Actividad Económica *</label>
                        <input type="text"
                               class="form-control"
                               name="giro"
                               value="<?= h($_POST['giro'] ?? '') ?>"
                               required>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="direccion" class="form-label">Dirección *</label>
                            <input type="text"
                                   class="form-control"
                                   name="direccion"
                                   value="<?= h($_POST['direccion'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="comuna" class="form-label">Comuna/Ciudad *</label>
                            <input type="text"
                                   class="form-control"
                                   name="comuna"
                                   value="<?= h($_POST['comuna'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="region" class="form-label">Región/Estado</label>
                        <input type="text"
                               class="form-control"
                               name="region"
                               value="<?= h($_POST['region'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Login
                        </a>
                        <button type="submit" name="next_step" class="btn btn-primary">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 2: Representante Legal -->
                <div class="form-section <?= $currentStep == 2 ? 'active' : '' ?>" id="step2">
                    <h4 class="mb-4">Representante Legal</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text"
                                   class="form-control"
                                   name="nombres"
                                   value="<?= h($_POST['nombres'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                            <input type="text"
                                   class="form-control"
                                   name="apellido_paterno"
                                   value="<?= h($_POST['apellido_paterno'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                        <input type="text"
                               class="form-control"
                               name="apellido_materno"
                               value="<?= h($_POST['apellido_materno'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="identificador_personal" class="form-label">
                            <span id="identifier_personal_label">RUT Personal</span> *
                        </label>
                        <input type="text"
                               class="form-control"
                               id="identificador_personal"
                               name="identificador_personal"
                               data-identifier="true"
                               data-country="CL"
                               value="<?= h($_POST['identificador_personal'] ?? '') ?>"
                               required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email_representante" class="form-label">Email *</label>
                            <input type="email"
                                   class="form-control"
                                   name="email_representante"
                                   value="<?= h($_POST['email_representante'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_movil" class="form-label">Teléfono Móvil *</label>
                            <input type="tel"
                                   class="form-control"
                                   name="telefono_movil"
                                   value="<?= h($_POST['telefono_movil'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="prev_step" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="submit" name="next_step" class="btn btn-primary">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 3: Seguridad -->
                <div class="form-section <?= $currentStep == 3 ? 'active' : '' ?>" id="step3">
                    <h4 class="mb-4">Configuración de Seguridad</h4>

                    <div class="mb-3">
                        <label for="email_acceso" class="form-label">Email de Acceso al Sistema *</label>
                        <input type="email"
                               class="form-control"
                               name="email_acceso"
                               id="email_acceso"
                               value="<?= h($_POST['email_acceso'] ?? $_POST['email_representante'] ?? '') ?>"
                               required>
                        <small class="text-muted">Este será tu usuario para ingresar al sistema</small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <div class="position-relative">
                            <input type="password"
                                   class="form-control"
                                   name="password"
                                   id="password"
                                   required>
                            <i class="bi bi-eye password-toggle" id="togglePassword" style="position:absolute;right:16px;top:12px;cursor:pointer;"></i>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <small class="text-muted" id="strengthText">Mínimo 12 caracteres, incluye mayúsculas, minúsculas, números y símbolos</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar Contraseña *</label>
                        <input type="password"
                               class="form-control"
                               name="password_confirm"
                               id="password_confirm"
                               required>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="generatePassword">
                            <i class="bi bi-key me-2"></i>Generar Contraseña Segura
                        </button>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="prev_step" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="submit" name="next_step" class="btn btn-primary">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 4: Selección de Plan -->
                <div class="form-section <?= $currentStep == 4 ? 'active' : '' ?>" id="step4">
                    <h4 class="mb-4">Selecciona tu Plan</h4>

                    <?php
                    $planes = $db->query("SELECT * FROM planes WHERE activo = 1 AND visible = 1 ORDER BY orden_display");
                    foreach ($planes as $plan):
                    ?>
                    <div class="plan-card" onclick="selectPlan(<?= $plan['id'] ?>)">
                        <input type="radio" name="id_plan" value="<?= $plan['id'] ?>" id="plan<?= $plan['id'] ?>" <?= $plan['destacado'] ? 'checked' : '' ?>>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1"><?= h($plan['nombre']) ?></h5>
                                <p class="text-muted mb-2"><?= h($plan['descripcion']) ?></p>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i><?= $plan['max_empresas'] ?> empresa<?= $plan['max_empresas'] > 1 ? 's' : '' ?></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i><?= $plan['usuarios_ilimitados'] ? 'Usuarios ilimitados' : $plan['max_usuarios'] . ' usuario(s)' ?></li>
                                    <?php if ($plan['tiene_trial']): ?>
                                    <li><i class="bi bi-check-circle text-success me-2"></i><?= $plan['dias_trial'] ?> días de prueba gratis</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="text-end">
                                <?php if ($plan['precio_mensual'] > 0): ?>
                                <h4 class="mb-0"><?= formatMoney($plan['precio_mensual'], $plan['moneda']) ?></h4>
                                <small class="text-muted">/mes</small>
                                <?php else: ?>
                                <h4 class="mb-0 text-success">Gratis</h4>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="prev_step" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="submit" name="next_step" class="btn btn-primary">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 5: Confirmación -->
                <div class="form-section <?= $currentStep == 5 ? 'active' : '' ?>" id="step5">
                    <h4 class="mb-4">Confirmación</h4>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Estás a punto de crear tu cuenta con 14 días de prueba gratis
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                        <label class="form-check-label" for="acepta_terminos">
                            Acepto los <a href="#" target="_blank">Términos y Condiciones</a> *
                        </label>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="acepta_privacidad" id="acepta_privacidad" required>
                        <label class="form-check-label" for="acepta_privacidad">
                            Acepto la <a href="#" target="_blank">Política de Privacidad</a> *
                        </label>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="prev_step" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="submit" name="action" value="register" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Crear Cuenta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/country-config.js"></script>
    <script src="assets/js/rut-validator.js"></script>
    <script>
        // Cambio de país
        document.getElementById('pais').addEventListener('change', function() {
            const config = onCountryChange(this.value, {
                identifier: 'identificador_tributario'
            });

            if (config) {
                document.getElementById('identifier_label').textContent = config.identifierType;
                document.getElementById('identifier_personal_label').textContent = config.identifierType + ' Personal';

                const identifierInput = document.getElementById('identificador_tributario');
                const identifierPersonal = document.getElementById('identificador_personal');

                identifierInput.setAttribute('data-country', config.code);
                identifierPersonal.setAttribute('data-country', config.code);
            }
        });

        // Toggle password
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Password strength
        document.getElementById('password').addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');

            bar.className = 'password-strength-bar strength-' + strength.replace('_', '-');

            const messages = {
                'weak': 'Contraseña débil',
                'medium': 'Contraseña media',
                'strong': 'Contraseña fuerte',
                'very_strong': 'Contraseña muy fuerte'
            };

            text.textContent = messages[strength] || '';
        });

        // Generate password
        document.getElementById('generatePassword').addEventListener('click', function() {
            const password = generateSecurePassword(16, {
                uppercase: true,
                lowercase: true,
                numbers: true,
                special: true,
                avoid_ambiguous: true
            });

            document.getElementById('password').value = password;
            document.getElementById('password_confirm').value = password;
            document.getElementById('password').type = 'text';
            document.getElementById('password').dispatchEvent(new Event('input'));
        });

        // Select plan
        function selectPlan(planId) {
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            document.getElementById('plan' + planId).checked = true;
        }
    </script>
</body>
</html>
