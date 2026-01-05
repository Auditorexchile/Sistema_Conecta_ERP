<?php
session_start();
require_once '../../app/core/Database.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$db = \App\Core\Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parámetros Globales - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .params-container { padding: 20px; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .tab.active { background: #3498db; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus { border-color: #3498db; outline: none; }

        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #229954; }

        .param-item { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #3498db; }
        .param-item label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .param-item input[type="checkbox"] { width: 20px; height: 20px; }

        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="params-container">
        <h1><i class="fas fa-cog"></i> Parámetros Globales del Sistema</h1>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('general')">General</button>
            <button class="tab" onclick="cambiarTab('fiscal')">Fiscal</button>
            <button class="tab" onclick="cambiarTab('email')">Email</button>
            <button class="tab" onclick="cambiarTab('integraciones')">Integraciones</button>
            <button class="tab" onclick="cambiarTab('seguridad')">Seguridad</button>
        </div>

        <!-- Tab General -->
        <div id="tab-general" class="tab-content active">
            <div class="card">
                <h3>Configuración General</h3>
                <form id="formGeneral">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre de la Aplicación</label>
                            <input type="text" id="app_name" value="Conecta ERP">
                        </div>
                        <div class="form-group">
                            <label>Idioma Predeterminado</label>
                            <select id="idioma">
                                <option value="es">Español</option>
                                <option value="en">English</option>
                                <option value="pt">Português</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Zona Horaria</label>
                            <select id="timezone">
                                <option value="America/Santiago">Santiago (GMT-3)</option>
                                <option value="America/Buenos_Aires">Buenos Aires (GMT-3)</option>
                                <option value="America/Lima">Lima (GMT-5)</option>
                                <option value="America/Bogota">Bogotá (GMT-5)</option>
                                <option value="America/Mexico_City">México (GMT-6)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Moneda Base</label>
                            <select id="moneda_base">
                                <option value="CLP" selected>CLP - Peso Chileno</option>
                                <option value="USD">USD - Dólar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="ARS">ARS - Peso Argentino</option>
                                <option value="PEN">PEN - Sol Peruano</option>
                                <option value="COP">COP - Peso Colombiano</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Formato de Fecha</label>
                            <select id="formato_fecha">
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="Y-m-d">YYYY-MM-DD</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Decimales Moneda</label>
                            <input type="number" id="decimales" value="2" min="0" max="4">
                        </div>
                    </div>

                    <h4 style="margin-top: 30px;">Opciones del Sistema</h4>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="multi_empresa" checked>
                            <span>Habilitar Multi-Empresa (Multi-tenancy)</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="modo_mantenimiento">
                            <span>Modo Mantenimiento (Desactiva acceso para usuarios)</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="registro_publico" checked>
                            <span>Permitir Registro Público de Empresas</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="trial_automatico" checked>
                            <span>Activar Trial Automático (14 días) para nuevas empresas</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Configuración General
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab Fiscal -->
        <div id="tab-fiscal" class="tab-content">
            <div class="card">
                <h3>Configuración Fiscal</h3>
                <form id="formFiscal">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>País Fiscal Principal</label>
                            <select id="pais_fiscal">
                                <option value="CL" selected>Chile</option>
                                <option value="AR">Argentina</option>
                                <option value="PE">Perú</option>
                                <option value="CO">Colombia</option>
                                <option value="MX">México</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Régimen Tributario (Chile)</label>
                            <select id="regimen">
                                <option value="14A">14 A - Renta Presunta</option>
                                <option value="14B" selected>14 B - Semi Integrado</option>
                                <option value="14D">14 D - Pro Pyme General</option>
                                <option value="14D3">14 D(3) - Pro Pyme Transparente</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tasa IVA (%)</label>
                            <input type="number" id="tasa_iva" value="19" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Tasa Impuesto Primera Categoría (%)</label>
                            <input type="number" id="tasa_primera_cat" value="27" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Código Actividad Económica</label>
                            <input type="text" id="cod_actividad" placeholder="620100">
                        </div>
                        <div class="form-group">
                            <label>RUT Representante Legal</label>
                            <input type="text" id="rut_representante" placeholder="12.345.678-9">
                        </div>
                    </div>

                    <h4 style="margin-top: 30px;">Documentos Tributarios Electrónicos (DTE)</h4>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="dte_habilitado" checked>
                            <span>Habilitar Emisión de DTE (SII Chile)</span>
                        </label>
                    </div>
                    <div class="form-grid" style="margin-top: 15px;">
                        <div class="form-group">
                            <label>RUT Empresa Certificado</label>
                            <input type="text" id="dte_rut_cert">
                        </div>
                        <div class="form-group">
                            <label>Archivo Certificado (.pfx)</label>
                            <input type="file" id="dte_certificado" accept=".pfx,.p12">
                        </div>
                        <div class="form-group">
                            <label>Clave Certificado</label>
                            <input type="password" id="dte_clave_cert">
                        </div>
                        <div class="form-group">
                            <label>Ambiente SII</label>
                            <select id="dte_ambiente">
                                <option value="certificacion">Certificación (Pruebas)</option>
                                <option value="produccion">Producción</option>
                            </select>
                        </div>
                    </div>

                    <h4 style="margin-top: 30px;">Folios DTE</h4>
                    <table style="width: 100%; margin-top: 10px;">
                        <thead>
                            <tr style="background: #34495e; color: white;">
                                <th style="padding: 12px;">Tipo Documento</th>
                                <th>Folio Desde</th>
                                <th>Folio Hasta</th>
                                <th>Folio Actual</th>
                                <th>Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 10px;">33 - Factura Electrónica</td>
                                <td>1</td>
                                <td>1000</td>
                                <td>345</td>
                                <td>655</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px;">34 - Factura Exenta</td>
                                <td>1</td>
                                <td>500</td>
                                <td>120</td>
                                <td>380</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px;">39 - Boleta Electrónica</td>
                                <td>1</td>
                                <td>5000</td>
                                <td>1250</td>
                                <td>3750</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px;">52 - Guía de Despacho</td>
                                <td>1</td>
                                <td>1000</td>
                                <td>89</td>
                                <td>911</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px;">61 - Nota de Crédito</td>
                                <td>1</td>
                                <td>500</td>
                                <td>23</td>
                                <td>477</td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Configuración Fiscal
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab Email -->
        <div id="tab-email" class="tab-content">
            <div class="card">
                <h3>Configuración de Email</h3>
                <form id="formEmail">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Servidor SMTP</label>
                            <input type="text" id="smtp_host" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label>Puerto SMTP</label>
                            <input type="number" id="smtp_port" value="587">
                        </div>
                        <div class="form-group">
                            <label>Usuario SMTP</label>
                            <input type="email" id="smtp_user" placeholder="noreply@empresa.cl">
                        </div>
                        <div class="form-group">
                            <label>Contraseña SMTP</label>
                            <input type="password" id="smtp_pass">
                        </div>
                        <div class="form-group">
                            <label>Encriptación</label>
                            <select id="smtp_encryption">
                                <option value="tls" selected>TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="">Ninguna</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email Remitente</label>
                            <input type="email" id="email_from" placeholder="noreply@empresa.cl">
                        </div>
                        <div class="form-group">
                            <label>Nombre Remitente</label>
                            <input type="text" id="email_from_name" value="Conecta ERP">
                        </div>
                    </div>

                    <h4 style="margin-top: 30px;">Opciones de Email</h4>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="email_facturas" checked>
                            <span>Enviar facturas automáticamente por email</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="email_recordatorios" checked>
                            <span>Enviar recordatorios de pago</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="email_reportes">
                            <span>Enviar reportes programados</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Configuración Email
                    </button>
                    <button type="button" class="btn btn-primary" style="margin-top: 20px; margin-left: 10px;" onclick="testEmail()">
                        <i class="fas fa-paper-plane"></i> Enviar Email de Prueba
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab Integraciones -->
        <div id="tab-integraciones" class="tab-content">
            <div class="card">
                <h3>Integraciones con Servicios Externos</h3>

                <h4>Pasarelas de Pago</h4>
                <div class="form-grid">
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="transbank_enabled">
                            <span><strong>Transbank Webpay</strong> (Chile)</span>
                        </label>
                        <input type="text" placeholder="Commerce Code" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="password" placeholder="API Key" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="mercadopago_enabled">
                            <span><strong>MercadoPago</strong> (Latinoamérica)</span>
                        </label>
                        <input type="text" placeholder="Public Key" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="password" placeholder="Access Token" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <h4 style="margin-top: 30px;">Bancos</h4>
                <div class="param-item">
                    <label>
                        <input type="checkbox" id="banco_chile">
                        <span><strong>Banco de Chile</strong> - Conciliación Automática</span>
                    </label>
                </div>
                <div class="param-item">
                    <label>
                        <input type="checkbox" id="banco_estado">
                        <span><strong>BancoEstado</strong> - Importación de Cartolas</span>
                    </label>
                </div>

                <h4 style="margin-top: 30px;">Previred (Chile)</h4>
                <div class="param-item">
                    <label>
                        <input type="checkbox" id="previred_enabled">
                        <span><strong>Previred</strong> - Generación automática REM (Remuneraciones)</span>
                    </label>
                    <input type="text" placeholder="RUT Empresa" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <input type="password" placeholder="Clave Previred" style="margin-top: 10px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <h4 style="margin-top: 30px;">Otras Integraciones</h4>
                <div class="param-item">
                    <label>
                        <input type="checkbox" id="whatsapp_enabled">
                        <span><strong>WhatsApp Business API</strong> - Notificaciones</span>
                    </label>
                </div>
                <div class="param-item">
                    <label>
                        <input type="checkbox" id="google_drive">
                        <span><strong>Google Drive</strong> - Backup en la nube</span>
                    </label>
                </div>

                <button class="btn btn-success" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Guardar Integraciones
                </button>
            </div>
        </div>

        <!-- Tab Seguridad -->
        <div id="tab-seguridad" class="tab-content">
            <div class="card">
                <h3>Configuración de Seguridad</h3>
                <form id="formSeguridad">
                    <h4>Contraseñas</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Longitud Mínima Contraseña</label>
                            <input type="number" id="pwd_min_length" value="8" min="6" max="20">
                        </div>
                        <div class="form-group">
                            <label>Días para Expiración Contraseña</label>
                            <input type="number" id="pwd_expiry_days" value="90">
                        </div>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="pwd_require_uppercase" checked>
                            <span>Requerir mayúsculas</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="pwd_require_numbers" checked>
                            <span>Requerir números</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="pwd_require_special" checked>
                            <span>Requerir caracteres especiales</span>
                        </label>
                    </div>

                    <h4 style="margin-top: 30px;">Sesiones</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tiempo de Inactividad (minutos)</label>
                            <input type="number" id="session_timeout" value="30">
                        </div>
                        <div class="form-group">
                            <label>Máximo de Sesiones Simultáneas</label>
                            <input type="number" id="max_sessions" value="3">
                        </div>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="force_logout_other">
                            <span>Cerrar otras sesiones al iniciar sesión nueva</span>
                        </label>
                    </div>

                    <h4 style="margin-top: 30px;">Autenticación</h4>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="two_factor_required">
                            <span>Requerir autenticación de dos factores (2FA)</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="captcha_enabled" checked>
                            <span>Habilitar CAPTCHA en inicio de sesión</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Intentos de Login Fallidos (antes de bloqueo)</label>
                        <input type="number" id="max_login_attempts" value="5">
                    </div>

                    <h4 style="margin-top: 30px;">Auditoría</h4>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="audit_all_changes" checked>
                            <span>Auditar todos los cambios en el sistema</span>
                        </label>
                    </div>
                    <div class="param-item">
                        <label>
                            <input type="checkbox" id="audit_login" checked>
                            <span>Registrar todos los inicios de sesión</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Retención de Logs de Auditoría (días)</label>
                        <input type="number" id="audit_retention" value="365">
                    </div>

                    <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Configuración de Seguridad
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }

        document.getElementById('formGeneral')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const datos = {
                app_name: document.getElementById('app_name').value,
                idioma: document.getElementById('idioma').value,
                timezone: document.getElementById('timezone').value,
                moneda_base: document.getElementById('moneda_base').value,
                formato_fecha: document.getElementById('formato_fecha').value,
                decimales: document.getElementById('decimales').value,
                multi_empresa: document.getElementById('multi_empresa').checked,
                modo_mantenimiento: document.getElementById('modo_mantenimiento').checked,
                registro_publico: document.getElementById('registro_publico').checked,
                trial_automatico: document.getElementById('trial_automatico').checked
            };

            try {
                const res = await fetch('/api/parametros/guardar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ seccion: 'general', datos })
                });
                const result = await res.json();
                if (result.success) {
                    alert('Configuración guardada correctamente');
                }
            } catch (error) {
                alert('Error al guardar configuración');
            }
        });

        document.getElementById('formFiscal')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            alert('Configuración fiscal guardada');
        });

        document.getElementById('formEmail')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            alert('Configuración de email guardada');
        });

        document.getElementById('formSeguridad')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            alert('Configuración de seguridad guardada');
        });

        function testEmail() {
            if (confirm('¿Enviar email de prueba a su dirección?')) {
                alert('Email de prueba enviado. Verifique su bandeja de entrada.');
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
