<?php
/**
 * Conecta ERP - Módulo IA de Auditoría
 * Dashboard principal del módulo de inteligencia artificial
 */

define('IN_ERP', true);

require_once __DIR__ . '/../../../app/core/bootstrap.php';

// Verificar autenticación
checkAuthentication();
checkTrial();

$db = Database::getInstance();
$companyId = $_SESSION['company_id'] ?? null;

// Obtener scores de riesgo
$scores = [];
if ($companyId) {
    $scores = $db->queryOne(
        "SELECT * FROM ai_scores
         WHERE id_empresa = :id_empresa
         AND tipo_score = 'riesgo_global'
         ORDER BY fecha_calculo DESC
         LIMIT 1",
        ['id_empresa' => $companyId]
    );
}

// Obtener alertas activas
$alertas = [];
if ($companyId) {
    $alertas = $db->query(
        "SELECT * FROM ai_alertas
         WHERE id_empresa = :id_empresa
         AND activa = 1
         AND leida = 0
         ORDER BY prioridad DESC, fecha_alerta DESC
         LIMIT 5",
        ['id_empresa' => $companyId]
    );
}

// Obtener últimas auditorías
$auditorias = [];
if ($companyId) {
    $auditorias = $db->query(
        "SELECT * FROM ai_auditorias
         WHERE id_empresa = :id_empresa
         ORDER BY fecha_inicio DESC
         LIMIT 5",
        ['id_empresa' => $companyId]
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IA de Auditoría - Conecta ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.3s;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-top: 60px;
        }

        /* IA Header */
        .ia-header {
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .ia-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .ia-header p {
            margin: 0;
            opacity: 0.95;
        }

        /* Risk Score Card */
        .risk-score-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            margin-bottom: 30px;
        }

        .risk-score-value {
            font-size: 72px;
            font-weight: 700;
            margin: 20px 0;
        }

        .risk-score-value.bajo {
            color: #28a745;
        }

        .risk-score-value.medio {
            color: #ffc107;
        }

        .risk-score-value.alto {
            color: #fd7e14;
        }

        .risk-score-value.critico {
            color: #dc3545;
        }

        /* Alerts */
        .alert-item {
            background: white;
            border-left: 4px solid;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .alert-item.critica {
            border-left-color: #dc3545;
        }

        .alert-item.alta {
            border-left-color: #fd7e14;
        }

        .alert-item.media {
            border-left-color: #ffc107;
        }

        .alert-item.baja {
            border-left-color: #28a745;
        }

        .alert-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .alert-message {
            color: #666;
            margin-bottom: 10px;
        }

        .alert-time {
            font-size: 12px;
            color: #999;
        }

        /* IA Features Grid */
        .ia-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .ia-feature-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .ia-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(142, 45, 226, 0.2);
            border-color: #8e2de2;
        }

        .ia-feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .ia-feature-icon.purple {
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
            color: white;
        }

        .ia-feature-icon.red {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .ia-feature-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .ia-feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .ia-feature-card p {
            color: #666;
            margin: 0;
        }

        /* Chat Auditor */
        .chat-auditor {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .chat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .chat-input-area {
            display: flex;
            gap: 10px;
        }

        .chat-input-area input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
        }

        .btn-chat {
            padding: 15px 30px;
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../layout/header.php'; ?>

    <div class="main-wrapper">
        <?php include __DIR__ . '/../../layout/sidebar.php'; ?>

        <div class="content-wrapper">
            <div class="main-content">
                <!-- IA Header -->
                <div class="ia-header">
                    <h1>
                        <i class="bi bi-robot me-3"></i>
                        IA de Auditoría Financiera
                    </h1>
                    <p>Detección inteligente de fraudes, riesgos y anomalías en tiempo real</p>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <!-- Risk Score -->
                        <div class="risk-score-card">
                            <h5>Score de Riesgo Global</h5>
                            <?php if ($scores): ?>
                            <div class="risk-score-value <?= strtolower($scores['nivel'] ?? 'medio') ?>">
                                <?= number_format($scores['score'], 1) ?>
                            </div>
                            <div class="badge bg-<?= $scores['nivel'] == 'bajo' ? 'success' : ($scores['nivel'] == 'critico' ? 'danger' : 'warning') ?> mb-3">
                                Riesgo <?= ucfirst($scores['nivel']) ?>
                            </div>
                            <p class="text-muted">Última actualización: <?= date('d/m/Y', strtotime($scores['fecha_calculo'])) ?></p>
                            <?php else: ?>
                            <div class="risk-score-value medio">--</div>
                            <p class="text-muted">No hay datos suficientes para calcular el score</p>
                            <button class="btn btn-primary mt-3">
                                <i class="bi bi-play-fill me-2"></i>
                                Ejecutar Primera Auditoría
                            </button>
                            <?php endif; ?>
                        </div>

                        <!-- Alertas Activas -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Alertas Activas
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if ($alertas): ?>
                                    <?php foreach ($alertas as $alerta): ?>
                                    <div class="alert-item <?= $alerta['prioridad'] ?>">
                                        <div class="alert-title"><?= h($alerta['titulo']) ?></div>
                                        <div class="alert-message"><?= h($alerta['mensaje']) ?></div>
                                        <div class="alert-time">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($alerta['fecha_alerta'])) ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-check-circle" style="font-size: 48px; opacity: 0.3;"></i>
                                    <p class="mt-3">No hay alertas activas</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <!-- IA Features -->
                        <div class="ia-features">
                            <div class="ia-feature-card">
                                <div class="ia-feature-icon purple">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3>Auditoría Contable</h3>
                                <p>Detecta asientos duplicados, cuentas incorrectas y desviaciones históricas</p>
                            </div>

                            <div class="ia-feature-card">
                                <div class="ia-feature-icon red">
                                    <i class="bi bi-bug"></i>
                                </div>
                                <h3>Detección de Fraude</h3>
                                <p>Identifica pagos duplicados, proveedores fantasma y patrones sospechosos</p>
                            </div>

                            <div class="ia-feature-card">
                                <div class="ia-feature-icon blue">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <h3>Análisis Predictivo</h3>
                                <p>Predice riesgos financieros, quiebra y problemas de liquidez</p>
                            </div>

                            <div class="ia-feature-card">
                                <div class="ia-feature-icon purple">
                                    <i class="bi bi-file-earmark-check"></i>
                                </div>
                                <h3>Cumplimiento IFRS</h3>
                                <p>Verifica cumplimiento normativo y genera recomendaciones</p>
                            </div>

                            <div class="ia-feature-card">
                                <div class="ia-feature-icon red">
                                    <i class="bi bi-receipt"></i>
                                </div>
                                <h3>Auditoría Tributaria</h3>
                                <p>Detecta inconsistencias en IVA, F29 y riesgos de fiscalización</p>
                            </div>

                            <div class="ia-feature-card">
                                <div class="ia-feature-icon blue">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <h3>Auditoría de Tesorería</h3>
                                <p>Identifica movimientos no conciliados y riesgos de liquidez</p>
                            </div>
                        </div>

                        <!-- Chat Auditor -->
                        <div class="chat-auditor">
                            <div class="chat-header">
                                <div class="chat-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Chat Auditor IA</h5>
                                    <small class="text-muted">Pregúntame sobre riesgos, anomalías o cumplimiento</small>
                                </div>
                            </div>

                            <div class="chat-input-area">
                                <input type="text" placeholder="¿Qué quieres saber sobre tu empresa?" id="chatInput">
                                <button class="btn-chat" onclick="sendMessage()">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">Ejemplos:</small>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-light text-dark" style="cursor: pointer;">¿Dónde tengo mayor riesgo?</span>
                                    <span class="badge bg-light text-dark" style="cursor: pointer;">¿Qué cuentas debo revisar?</span>
                                    <span class="badge bg-light text-dark" style="cursor: pointer;">¿Por qué el IVA está alto?</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include __DIR__ . '/../../layout/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message) {
                alert('Funcionalidad de Chat IA en desarrollo.\nMensaje: ' + message);
                input.value = '';
            }
        }

        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>
