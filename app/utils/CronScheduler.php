<?php
namespace App\Utils;

class CronScheduler {
    private $config;
    private $db;

    public function __construct() {
        $this->config = require APP_PATH . '/config/cron.php';
        $this->db = \App\Core\Database::getInstance();
    }

    public function run() {
        if (!$this->config['enabled']) {
            return false;
        }

        foreach ($this->config['jobs'] as $frequency => $jobs) {
            foreach ($jobs as $jobName => $jobConfig) {
                if (!$jobConfig['enabled']) {
                    continue;
                }

                if ($this->shouldRun($jobConfig['schedule'])) {
                    $this->executeJob($jobName, $jobConfig);
                }
            }
        }
    }

    private function shouldRun($schedule) {
        // Parse cron expression (simplified)
        // Format: minute hour day month weekday
        $parts = explode(' ', $schedule);
        if (count($parts) !== 5) {
            return false;
        }

        $now = [
            (int)date('i'), // minute
            (int)date('G'), // hour
            (int)date('j'), // day
            (int)date('n'), // month
            (int)date('w')  // weekday
        ];

        foreach ($parts as $i => $part) {
            if ($part === '*') {
                continue;
            }

            if (strpos($part, '*/') === 0) {
                $interval = (int)substr($part, 2);
                if ($now[$i] % $interval !== 0) {
                    return false;
                }
            } elseif (!in_array($now[$i], explode(',', $part))) {
                return false;
            }
        }

        return true;
    }

    private function executeJob($jobName, $jobConfig) {
        // Queue the job
        $stmt = $this->db->prepare("
            INSERT INTO colas_procesamiento
            (id_empresa, tipo_tarea, nombre_clase, payload, prioridad, disponible_en, created_at)
            VALUES (1, ?, ?, '{}', 0, NOW(), NOW())
        ");

        return $stmt->execute([$jobName, $jobConfig['class'] ?? '']);
    }

    public function schedule($jobName, $schedule, $class, $options = []) {
        // Add new job to schedule
    }
}
