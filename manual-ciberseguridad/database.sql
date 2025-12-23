-- Base de Datos: Manual de Ciberseguridad - AuditorEx Chile SpA
-- Fecha: 2024
-- Autor: AuditorEx Chile

CREATE DATABASE IF NOT EXISTS conectae_estudiosbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE conectae_estudiosbd;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(200),
    email VARCHAR(150),
    rol ENUM('admin', 'instructor', 'estudiante') DEFAULT 'estudiante',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de módulos
CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    titulo VARCHAR(500) NOT NULL,
    descripcion TEXT,
    orden INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de temas
CREATE TABLE IF NOT EXISTS temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    numero INT NOT NULL,
    titulo VARCHAR(500) NOT NULL,
    contenido LONGTEXT,
    orden INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de casos reales
CREATE TABLE IF NOT EXISTS casos_reales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema_id INT NOT NULL,
    numero INT NOT NULL,
    titulo VARCHAR(500) NOT NULL,
    empresa VARCHAR(200),
    pais VARCHAR(100) DEFAULT 'Chile',
    anio INT,
    descripcion TEXT,
    analisis TEXT,
    consecuencias TEXT,
    lecciones_aprendidas TEXT,
    referencias TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de ejercicios
CREATE TABLE IF NOT EXISTS ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema_id INT NOT NULL,
    numero INT NOT NULL,
    titulo VARCHAR(500) NOT NULL,
    tipo ENUM('practico', 'teorico', 'laboratorio', 'investigacion') DEFAULT 'practico',
    enunciado TEXT,
    objetivos TEXT,
    pasos TEXT,
    solucion TEXT,
    recursos_necesarios TEXT,
    tiempo_estimado INT COMMENT 'Minutos',
    dificultad ENUM('basico', 'intermedio', 'avanzado') DEFAULT 'intermedio',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de progreso del usuario
CREATE TABLE IF NOT EXISTS progreso_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tema_id INT NOT NULL,
    completado TINYINT(1) DEFAULT 0,
    porcentaje INT DEFAULT 0,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_completado TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progreso (usuario_id, tema_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario por defecto
INSERT INTO usuarios (usuario, password, nombre_completo, email, rol)
VALUES ('auditorexchile.cl', MD5('3214A3S'), 'AuditorEx Chile SpA', 'gerencia@auditorexchile.cl', 'admin');

-- Insertar módulos
INSERT INTO modulos (numero, titulo, descripcion, orden) VALUES
(1, 'CONCEPTOS BÁSICOS DE REDES E APLICADA A LA SEGURIDAD', 'Fundamentos de redes, protocolos, modelo OSI y detección de anomalías con Machine Learning', 1),
(2, 'ETHICAL HACKING PARA AMENAZAS AVANZADAS Y DEFENSA DIGITAL CON KALI LINUX, METASPLOIT', 'Virtualización, instalación de Kali Linux, comandos básicos y herramientas de pentesting', 2),
(3, 'ESTRATEGIAS AVANZADAS EN CIBERSEGURIDAD, ASISTIDAS POR IA Y MACHINE LEARNING', 'Malware, ingeniería social, ataques DoS/DDoS, APTs y defensa con IA', 3),
(4, 'PENTESTING PROFESIONAL: MÉTODOS, HERRAMIENTAS Y VALIDACIÓN DE VULNERABILIDADES', 'Nmap, Nessus, Metasploit Framework, Meterpreter y explotación de vulnerabilidades', 4),
(5, 'CIBERINTELIGENCIA, FORENSE DIGITAL Y GESTIÓN DE INCIDENTES CON IA', 'OSINT, gestión de incidentes, análisis forense y respuesta ante ransomware', 5),
(6, 'FUNDAMENTOS MODERNOS DE LA CIBERSEGURIDAD 4.0', 'Ciberseguridad en la era de IA, protección de datos, IoT y gobernanza', 6);

-- Índices para optimización
CREATE INDEX idx_modulos_orden ON modulos(orden);
CREATE INDEX idx_temas_modulo ON temas(modulo_id, orden);
CREATE INDEX idx_casos_tema ON casos_reales(tema_id);
CREATE INDEX idx_ejercicios_tema ON ejercicios(tema_id);
CREATE INDEX idx_progreso_usuario ON progreso_usuario(usuario_id, tema_id);

-- Vista de estadísticas
CREATE VIEW v_estadisticas_modulos AS
SELECT
    m.id,
    m.numero,
    m.titulo,
    COUNT(DISTINCT t.id) as total_temas,
    COUNT(DISTINCT c.id) as total_casos,
    COUNT(DISTINCT e.id) as total_ejercicios
FROM modulos m
LEFT JOIN temas t ON m.id = t.modulo_id
LEFT JOIN casos_reales c ON t.id = c.tema_id
LEFT JOIN ejercicios e ON t.id = e.tema_id
GROUP BY m.id, m.numero, m.titulo;
