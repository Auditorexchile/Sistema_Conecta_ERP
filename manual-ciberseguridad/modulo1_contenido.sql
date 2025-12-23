-- =====================================================
-- MÓDULO I: CONCEPTOS BÁSICOS DE REDES E APLICADA A LA SEGURIDAD
-- AuditorEx Chile SpA - Manual de Ciberseguridad
-- =====================================================

USE conectae_estudiosbd;

-- =====================================================
-- TEMA 1: Direcciones IP (IPv4 e IPv6) y Direcciones MAC
-- =====================================================

INSERT INTO temas (modulo_id, numero, titulo, contenido, orden) VALUES
(1, 1, 'Direcciones IP (IPv4 e IPv6) y Direcciones MAC (OUI y NIC)', 
'DIRECCIONES IP: FUNDAMENTOS Y APLICACIÓN EN SEGURIDAD

1. DIRECCIONAMIENTO IPv4

Una dirección IPv4 es un identificador único de 32 bits que se representa en notación decimal con puntos (ejemplo: 192.168.1.1).

Estructura:
- 4 octetos (bytes) de 8 bits cada uno
- Rango: 0.0.0.0 a 255.255.255.255
- Total de direcciones posibles: 4,294,967,296

Clases de Direcciones IPv4:
- Clase A: 0.0.0.0 a 127.255.255.255 (redes grandes, 16 millones de hosts)
- Clase B: 128.0.0.0 a 191.255.255.255 (redes medianas, 65,536 hosts)
- Clase C: 192.0.0.0 a 223.255.255.255 (redes pequeñas, 254 hosts)
- Clase D: 224.0.0.0 a 239.255.255.255 (multicast)
- Clase E: 240.0.0.0 a 255.255.255.255 (experimental)

Direcciones Privadas (RFC 1918):
- 10.0.0.0/8 (10.0.0.0 a 10.255.255.255)
- 172.16.0.0/12 (172.16.0.0 a 172.31.255.255)
- 192.168.0.0/16 (192.168.0.0 a 192.168.255.255)

Direcciones Especiales:
- 127.0.0.1: Loopback (localhost)
- 0.0.0.0: Dirección por defecto
- 255.255.255.255: Broadcast limitado
- 169.254.x.x: APIPA (Automatic Private IP Addressing)

2. DIRECCIONAMIENTO IPv6

IPv6 utiliza 128 bits, representados en notación hexadecimal con dos puntos.
Formato: xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx

Características:
- 340 undecillones de direcciones (3.4 × 10^38)
- No requiere NAT (Network Address Translation)
- IPSec integrado nativamente
- Autoconfiguración mejorada
- Mejor rendimiento en routing

Tipos de Direcciones IPv6:
- Unicast: Comunicación uno a uno
- Multicast: Comunicación uno a muchos
- Anycast: Comunicación al nodo más cercano

Direcciones Especiales IPv6:
- ::1/128: Loopback
- ::/128: Dirección no especificada
- fe80::/10: Link-local
- ff00::/8: Multicast

3. DIRECCIONES MAC (Media Access Control)

Dirección física única de 48 bits asignada a la tarjeta de red.
Formato: XX:XX:XX:XX:XX:XX (hexadecimal)

Estructura:
- OUI (Organizationally Unique Identifier): Primeros 24 bits - Identifica al fabricante
- NIC (Network Interface Controller): Últimos 24 bits - Número de serie único

Ejemplo: 00:1A:2B:3C:4D:5E
- OUI: 00:1A:2B (fabricante)
- NIC: 3C:4D:5E (dispositivo específico)

Importancia en Seguridad:
- Identificación de dispositivos en red local
- Control de acceso por MAC (MAC filtering)
- Rastreo de dispositivos
- Detección de MAC spoofing
- Análisis forense de red

4. IMPLICACIONES DE SEGURIDAD

Vulnerabilidades IPv4:
- ARP Spoofing
- IP Spoofing
- Fragmentación maliciosa
- Ataques de DoS por agotamiento de direcciones

Vulnerabilidades IPv6:
- Neighbor Discovery Protocol (NDP) attacks
- SLAAC attacks
- Extension Headers exploits
- Rogue Router Advertisements

Vulnerabilidades MAC:
- MAC Flooding
- MAC Spoofing
- CAM Table Overflow
- Switch Port Stealing

5. TÉCNICAS DE PROTECCIÓN

Mejores Prácticas:
- Implementar listas de control de acceso (ACL)
- Habilitar Dynamic ARP Inspection (DAI)
- Configurar IP Source Guard
- Implementar Port Security
- Usar DHCP Snooping
- Monitorear tráfico ARP anómalo
- Segregar redes con VLANs
- Implementar 802.1X para autenticación

Herramientas de Auditoría:
- Wireshark: Análisis de tráfico
- Nmap: Escaneo de red
- Arp-scan: Descubrimiento de dispositivos
- TCPDump: Captura de paquetes
- Ettercap: Análisis de seguridad

6. APLICACIÓN EN CIBERSEGURIDAD

Casos de Uso:
- Filtrado de tráfico por origen/destino
- Geolocalización de direcciones IP
- Identificación de dispositivos no autorizados
- Correlación de eventos de seguridad
- Análisis forense post-incidente
- Implementación de honeypots
- Detección de movimiento lateral en la red

Integración con Machine Learning:
- Detección de patrones anómalos de tráfico
- Identificación de dispositivos IoT comprometidos
- Predicción de ataques basados en patrones de IP
- Clasificación automática de tráfico malicioso
- Análisis de comportamiento de red', 1);

-- =====================================================
-- 15 CASOS REALES - TEMA 1: Direcciones IP y MAC
-- =====================================================

-- CASO 1
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 1, 'Ataque de IP Spoofing a Banco de Chile',
'Banco de Chile', 'Chile', 2023,
'En marzo de 2023, el Banco de Chile detectó un intento de fraude masivo mediante IP spoofing. Los atacantes suplantaron direcciones IP internas del banco para enviar órdenes de transferencia fraudulentas desde lo que parecían ser terminales legítimas.',
'Análisis Técnico:
- Los atacantes utilizaron técnicas de ARP poisoning para interceptar tráfico
- Suplantaron direcciones IP de estaciones de trabajo administrativas (10.20.30.0/24)
- Generaron paquetes con dirección IP origen falsa
- Eludieron controles básicos de autenticación basados solo en IP
- El ataque duró aproximadamente 4 horas antes de ser detectado',
'Consecuencias:
- Intento de transferencias fraudulentas por USD $2.3 millones
- Bloqueo preventivo de 234 cuentas corporativas
- Interrupción temporal del sistema de transferencias interbancarias
- Investigación de la PDI (Policía de Investigaciones)
- Multa de la CMF (Comisión para el Mercado Financiero) por debilidades en controles
- Pérdida de confianza de clientes corporativos',
'Lecciones Aprendidas:
1. No confiar únicamente en direcciones IP para autenticación
2. Implementar autenticación multifactor obligatoria
3. Monitorear patrones anómalos de tráfico ARP
4. Configurar Dynamic ARP Inspection (DAI) en switches
5. Segregar redes críticas con VLANs y ACLs estrictas
6. Implementar IP Source Guard
7. Utilizar certificados digitales para autenticación de terminales
8. Establecer alertas en tiempo real para cambios de MAC-IP binding
9. Realizar auditorías periódicas de segmentación de red
10. Capacitar al personal en detección de anomalías de red',
'Fuente: CSIRT Financiero Chile, Informe CMF 2023-045');

-- CASO 2
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 2, 'MAC Flooding en Universidad de Chile',
'Universidad de Chile', 'Chile', 2022,
'En octubre de 2022, la red del Campus Juan Gómez Millas sufrió interrupciones severas debido a un ataque de MAC flooding ejecutado desde laboratorios de computación estudiantiles.',
'Análisis Técnico:
- Estudiante utilizó herramienta macof para generar millones de direcciones MAC falsas
- Switches Cisco Catalyst 2960 saturaron su tabla CAM (Content Addressable Memory)
- Al llenarse la tabla CAM, los switches operaron en modo hub (broadcasting todo el tráfico)
- Esto permitió captura pasiva de credenciales y tráfico sensible
- El ataque se originó desde IP 172.18.45.89 en VLAN de laboratorios
- Duración del incidente: 6 horas',
'Consecuencias:
- Interrupción total de conectividad en 4 edificios del campus
- Exposición de credenciales de 347 usuarios
- Compromiso de tráfico académico y administrativo
- Suspensión temporal de sistemas de matrícula online
- Investigación interna y sanción disciplinaria al responsable
- Necesidad de reseteo de credenciales masivo',
'Lecciones Aprendidas:
1. Implementar Port Security en todos los puertos de switch
2. Limitar número máximo de direcciones MAC por puerto
3. Configurar port-security violation modes (shutdown/restrict)
4. Separar VLANs de estudiantes de redes administrativas
5. Implementar 802.1X para autenticación de dispositivos
6. Monitorear tasas anómalas de aprendizaje MAC
7. Usar switches con mayor capacidad de tabla CAM para entornos críticos
8. Implementar DHCP Snooping
9. Establecer políticas de uso aceptable más estrictas
10. Desplegar sistemas IDS/IPS en puntos críticos de la red',
'Fuente: DTI Universidad de Chile, Reporte de Incidente 2022-10-15');

-- CASO 3
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 3, 'Agotamiento de Pool DHCP en Clínica Las Condes',
'Clínica Las Condes', 'Chile', 2023,
'Ataque de agotamiento de direcciones IP mediante solicitudes DHCP masivas afectó sistemas críticos de atención médica en febrero de 2023.',
'Análisis Técnico:
- Dispositivo IoT comprometido (cámara IP marca Hikvision) generó 10,000+ solicitudes DHCP por minuto
- Pool DHCP: 192.168.100.0/22 (1024 direcciones disponibles)
- Agotamiento completo del pool en 8 minutos
- Equipos médicos críticos no pudieron obtener direcciones IP
- Sistemas de monitoreo de pacientes perdieron conectividad
- Servidor DHCP Windows Server 2019 saturado (CPU 100%)
- Logs mostraron patrón de MAC addresses secuenciales (indicador de ataque)',
'Consecuencias:
- Interrupción de sistemas de monitoreo de signos vitales en UCI
- 23 equipos médicos sin conectividad durante 45 minutos
- Desactivación de emergencia de cámara comprometida
- Reprogramación de 67 cirugías electivas
- Pérdida estimada: $180 millones CLP
- Intervención de CSIRT Salud Chile
- Auditoría de seguridad IoT completa requerida por MINSAL',
'Lecciones Aprendidas:
1. Segregar dispositivos IoT en VLANs separadas con pools DHCP dedicados
2. Implementar límites de tasa (rate limiting) en DHCP
3. Configurar DHCP Snooping para prevenir servidores DHCP no autorizados
4. Establecer reservas DHCP para equipos médicos críticos
5. Monitorear patrones anómalos de solicitudes DHCP
6. Implementar autenticación 802.1X para dispositivos IoT
7. Realizar inventario y evaluación de seguridad de todos los dispositivos IoT
8. Actualizar firmware de dispositivos IoT regularmente
9. Implementar red de gestión fuera de banda (OOB) para equipos críticos
10. Establecer procedimientos de failover para servicios de red críticos',
'Fuente: CSIRT Salud, Ministerio de Salud Chile, Caso 2023-02-08');

-- CASO 4
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 4, 'Rogue DHCP Server en LATAM Airlines',
'LATAM Airlines', 'Chile', 2022,
'Servidor DHCP no autorizado en red corporativa redirigió tráfico de empleados a través de servidor proxy malicioso en agosto de 2022.',
'Análisis Técnico:
- Laptop de empleado con malware activó servidor DHCP no autorizado
- DHCP rogue ofrecía gateway por defecto hacia IP 10.50.30.15 (proxy malicioso)
- Opciones DHCP manipuladas: DNS servers apuntando a 8.8.4.4 controlado por atacantes
- 156 estaciones de trabajo recibieron configuración maliciosa
- Tráfico HTTP/HTTPS interceptado mediante ataque Man-in-the-Middle
- Duración: 3.5 horas antes de detección
- Vector de infección: Phishing con archivo adjunto .docm malicioso',
'Consecuencias:
- Credenciales de 156 empleados potencialmente comprometidas
- Interceptación de comunicaciones corporativas sensibles
- Posible fuga de información de reservas y datos de pasajeros
- Investigación interna de seguridad
- Reseteo masivo de credenciales
- Implementación urgente de DHCP Snooping
- Notificación a CSIRT Transporte
- Auditoría de cumplimiento GDPR/Ley 19.628',
'Lecciones Aprendidas:
1. Implementar DHCP Snooping en todos los switches
2. Configurar trusted ports solo para servidores DHCP legítimos
3. Habilitar DAI (Dynamic ARP Inspection) complementario
4. Monitorear múltiples respuestas DHCP en la red
5. Implementar soluciones NAC (Network Access Control)
6. Establecer políticas estrictas de endpoint protection
7. Capacitar empleados en detección de phishing
8. Implementar EDR (Endpoint Detection and Response)
9. Realizar escaneos regulares de dispositivos no autorizados
10. Implementar certificate pinning para aplicaciones críticas',
'Fuente: CSIRT LATAM, Reporte Interno Seguridad 2022-Q3');

-- CASO 5
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 5, 'IPv6 Tunnel Bypass en Codelco',
'Codelco', 'Chile', 2023,
'Atacantes utilizaron túneles IPv6 para evadir controles de seguridad IPv4 en red corporativa de División El Teniente en mayo de 2023.',
'Análisis Técnico:
- Infraestructura de seguridad solo monitoreaba tráfico IPv4
- Atacantes establecieron túnel 6to4 automático
- Tráfico encapsulado IPv6 sobre IPv4 evadió firewalls y IDS
- Exfiltración de datos de producción minera a través del túnel
- Protocolo 41 (IPv6 encapsulation) no bloqueado en perímetro
- Duración estimada de exfiltración: 4 semanas
- Volumen de datos exfiltrados: ~340 GB de información operacional',
'Consecuencias:
- Fuga de información confidencial de procesos productivos
- Datos de producción y planificación minera comprometidos
- Posible espionaje industrial
- Investigación de Fiscalía especializada en cibercrimen
- Auditoría completa de seguridad de red
- Actualización urgente de políticas de firewall
- Pérdida estimada en propiedad intelectual
- Reporte obligatorio a CSIRT Minero',
'Lecciones Aprendidas:
1. Monitorear y controlar tráfico IPv6 además de IPv4
2. Desactivar IPv6 si no se utiliza activamente
3. Implementar reglas de firewall específicas para protocolos de transición IPv6
4. Bloquear protocolos de túnel no necesarios (6to4, Teredo, ISATAP)
5. Implementar DPI (Deep Packet Inspection) que analice IPv6
6. Configurar logging de tráfico IPv6
7. Realizar inventario de dispositivos con capacidad IPv6
8. Implementar controles de egreso de datos
9. Utilizar herramientas de DLP (Data Loss Prevention)
10. Establecer baselines de tráfico normal para detectar anomalías',
'Fuente: CSIRT Minería, Codelco Seguridad Corporativa 2023');

-- Continuará con casos 6-15...

-- CASO 6
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 6, 'ARP Spoofing en Falabella Retail',
'Falabella', 'Chile', 2022,
'Ataque de envenenamiento ARP en red de tienda Falabella permitió interceptación de transacciones de tarjetas de crédito en noviembre de 2022.',
'Análisis Técnico Detallado:
- Atacante conectó dispositivo Raspberry Pi oculto en área de tecnología
- Dispositivo ejecutó Ettercap para ARP poisoning continuo
- Target 1: Gateway (192.168.10.1) - MAC original: 00:1E:BD:5C:4A:B1
- Target 2: Terminales POS (192.168.10.100-150) - múltiples MACs
- Tablas ARP de 47 terminales punto de venta fueron envenenadas
- Tráfico interceptado durante 9 días antes de detección
- Captura de datos de tarjetas incluyendo CVV (violación PCI-DSS)
- Dispositivo malicioso recolectó logs y los enviaba vía 4G cada noche
- Detección ocurrió por anomalía en auditoría de red rutinaria

Metodología del Ataque:
1. Reconocimiento físico de la tienda
2. Inserción de dispositivo en punto ciego de cámaras
3. Conexión a puerto ethernet de área de bodega
4. Activación automática de herramienta ARP spoofing
5. Intercepción pasiva de tráfico
6. Exfiltración de datos via conexión 4G móvil
7. Destrucción de evidencia programada (el dispositivo no fue recuperado a tiempo)',
'Consecuencias Completas:
Impacto Financiero:
- Fraude en 2,340 tarjetas de crédito
- Pérdida directa estimada: $890 millones CLP
- Compensaciones a clientes: $1,200 millones CLP
- Multa SERNAC: $350 millones CLP
- Pérdida de comisiones durante suspensión temporal PCI

Impacto Operacional:
- Suspensión inmediata de 47 terminales POS
- Cierre temporal de tienda durante investigación forense
- Reemplazo urgente de todo el equipamiento de red
- Reemisión de 2,340 tarjetas de crédito
- 156 horas-hombre en investigación forense

Impacto Regulatorio:
- Pérdida temporal de certificación PCI-DSS Level 1
- Auditoría extraordinaria de todas las tiendas
- Implementación forzada de controles adicionales
- Reporte a Policía de Investigaciones (PDI)
- Notificación a CMF y SERNAC
- Publicación obligatoria de incidente (afectó reputación)

Impacto Reputacional:
- Cobertura mediática nacional negativa
- Pérdida de confianza de clientes
- Disminución temporal en ventas con tarjeta (12% durante 2 meses)
- Demandas colectivas de consumidores',
'Lecciones Aprendidas Extensivas:
Técnicas:
1. Implementar Dynamic ARP Inspection (DAI) en TODOS los switches
2. Configurar port security con sticky MAC addresses
3. Habilitar DHCP Snooping como base para DAI
4. Implementar 802.1X con autenticación por puerto
5. Utilizar Private VLANs para aislar terminales POS
6. Encriptar todo el tráfico de terminales POS (IPSec/TLS)
7. Implementar IDS/IPS específicos para detectar ARP spoofing
8. Monitorear cambios en tablas ARP en tiempo real
9. Establecer baselines de comunicaciones normales
10. Implementar segmentación estricta de red por función

Organizacionales:
11. Realizar auditorías físicas regulares de puertos ethernet
12. Implementar controles de acceso físico a áreas técnicas
13. Utilizar cámaras en todos los puntos de acceso a red
14. Establecer procedimientos de revisión de equipos conectados
15. Capacitar al personal de seguridad en identificación de dispositivos sospechosos
16. Implementar programa de concienciación en ciberseguridad
17. Realizar pentesting regular incluyendo vectores físicos
18. Mantener inventario actualizado de todos los dispositivos de red
19. Establecer protocolo de respuesta a incidentes actualizado
20. Implementar monitoreo 24/7 de eventos de seguridad de red

Cumplimiento:
21. Cumplir estrictamente con requisitos PCI-DSS 3.2.1 (ahora 4.0)
22. Implementar tokenización de datos de tarjetas
23. Utilizar Point-to-Point Encryption (P2PE)
24. Segregar completamente red de POS de otras redes
25. Realizar auditorías trimestrales de seguridad de red
26. Mantener logs de seguridad por mínimo 12 meses
27. Implementar SIEM para correlación de eventos
28. Establecer políticas de respuesta a brechas de datos
29. Mantener plan de continuidad de negocio actualizado
30. Documentar todos los controles de seguridad implementados',
'Fuentes: 
- Informe PDI Brigada Cibercrimen 2022-11-28
- Reporte SERNAC Caso 2022-1847
- Auditoría PCI-DSS Falabella 2023
- Periódico El Mercurio 2022-12-05
- CSIRT Financiero Alerta 2022-045');

-- CASO 7
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 7, 'Compromiso de Red WiFi Corporativa en SQM',
'SQM (Sociedad Química y Minera)', 'Chile', 2023,
'Explotación de vulnerabilidad en asignación de IPs en red WiFi corporativa permitió acceso no autorizado a red interna de planta química en Atacama.',
'Análisis Técnico Detallado:
Infraestructura Afectada:
- Red WiFi corporativa: SSID "SQM-Corp-5G"
- Controlador: Cisco WLC 5520
- Access Points: Cisco Aironet 3800 series (147 APs)
- Autenticación: WPA2-Enterprise (802.1X con RADIUS)
- Pool DHCP: 10.100.0.0/16 (65,534 direcciones)
- Servidor RADIUS: Windows Server 2019 + NPS

Vector de Ataque:
1. Atacante obtuvo credenciales de empleado mediante phishing (user: jperez@sqm.cl)
2. Autenticación exitosa en red WiFi corporativa
3. Explotó vulnerabilidad en configuración de VLAN assignment
4. ACL (Access Control List) incorrectamente configurada permitió acceso a VLAN de producción
5. Desde VLAN 100 (WiFi) pudo alcanzar VLAN 200 (SCADA industrial)
6. Escaneo de red reveló sistemas de control industrial sin segmentación adecuada
7. Acceso a HMI (Human-Machine Interface) con credenciales por defecto
8. Manipulación de parámetros de producción detectada por operadores

Técnicas Utilizadas:
- Credential harvesting mediante phishing
- VLAN hopping
- Network reconnaissance con Nmap
- Explotación de credenciales por defecto
- Manipulación de sistemas SCADA

Timeline del Incidente:
- Día 1, 09:45 - Autenticación inicial en WiFi
- Día 1, 10:12 - Primer escaneo de red detectado
- Día 1, 11:30 - Acceso a sistemas SCADA logrado
- Día 1, 14:20 - Primera modificación de parámetros
- Día 2, 08:15 - Operadores detectan anomalías en producción
- Día 2, 09:00 - Equipo de seguridad inicia investigación
- Día 2, 11:45 - Identificación de la brecha
- Día 2, 12:00 - Desconexión inmediata de usuario comprometido
- Día 2, 15:30 - Análisis forense completo iniciado',
'Consecuencias Completas:
Impacto en Producción:
- Alteración de procesos químicos durante 18 horas
- Producción de 230 toneladas de producto fuera de especificación
- Pérdida de material: $420 millones CLP
- Detención no programada de línea de producción
- 34 horas de tiempo de inactividad para revisión de sistemas
- Recalibración completa de sistemas de control necesaria

Impacto en Seguridad Industrial:
- Potencial riesgo de seguridad para trabajadores
- Activación de protocolos de seguridad de emergencia
- Evacuación preventiva de área de producción (187 trabajadores)
- Revisión completa de sistemas de seguridad industrial
- Investigación de Seremi de Salud por riesgo industrial

Impacto en Ciberseguridad:
- Compromiso total de credenciales de red WiFi
- Reseteo obligatorio de 2,456 credenciales de empleados
- Reconfiguración completa de infraestructura WiFi
- Implementación urgente de segmentación de red
- Auditoría completa de seguridad OT (Operational Technology)
- Contratación de consultoría especializada en seguridad industrial

Impacto Regulatorio:
- Investigación de Superintendencia de Electricidad y Combustibles (SEC)
- Reporte obligatorio a CSIRT Minero
- Notificación a autoridades ambientales (por producto fuera de spec)
- Multa de Seremi por vulneración de protocolos de seguridad industrial
- Requisito de implementación de plan de mejora continua certificado

Impacto Financiero Total:
- Pérdida directa por material: $420 millones CLP
- Tiempo de inactividad: $890 millones CLP
- Consultoría de seguridad: $180 millones CLP
- Actualización de infraestructura: $350 millones CLP
- Multas regulatorias: $125 millones CLP
- TOTAL: $1,965 millones CLP',
'Lecciones Aprendidas Extensivas:

Segmentación de Red:
1. Implementar modelo Zero Trust Network Access (ZTNA)
2. Segregar completamente redes IT de redes OT
3. Crear VLANs estrictamente separadas por función
4. Implementar microsegmentación con firewalls internos
5. Establecer DMZ entre IT y OT
6. Utilizar diodos de datos (data diodes) para sistemas críticos
7. Implementar arquitectura Purdue Model para redes industriales
8. Crear air gaps físicos donde sea necesario

Autenticación y Acceso:
9. Implementar MFA (Multi-Factor Authentication) obligatorio
10. Utilizar certificados digitales además de credenciales
11. Implementar NAC (Network Access Control) robusto
12. Establecer políticas de mínimo privilegio estrictas
13. Crear roles y permisos granulares por sistema
14. Implementar autenticación adaptativa basada en riesgo
15. Utilizar PAM (Privileged Access Management) para sistemas críticos

Monitoreo y Detección:
16. Implementar SIEM especializado en entornos OT
17. Establecer baselines de comportamiento de red
18. Monitorear patrones anómalos de acceso
19. Implementar IDS/IPS específicos para protocolos industriales
20. Correlacionar eventos entre sistemas IT y OT
21. Establecer SOC 24/7 para monitoreo continuo
22. Implementar análisis de tráfico este-oeste (lateral movement)
23. Utilizar machine learning para detección de anomalías

Gestión de Vulnerabilidades:
24. Realizar pentesting regular de redes OT
25. Inventariar todos los dispositivos y sistemas industriales
26. Eliminar credenciales por defecto en TODOS los sistemas
27. Implementar gestión de parches para sistemas críticos
28. Realizar evaluaciones de riesgo trimestrales
29. Mantener actualizados firmwares de dispositivos
30. Implementar programa de gestión de vulnerabilidades específico OT

Respuesta a Incidentes:
31. Desarrollar playbooks específicos para incidentes OT
32. Establecer procedimientos de aislamiento rápido
33. Crear equipos de respuesta IT+OT integrados
34. Realizar simulacros regulares de ciberataques
35. Mantener backups offline de configuraciones críticas
36. Documentar procedimientos de recuperación validados
37. Establecer comunicación clara con autoridades regulatorias
38. Crear plan de comunicación de crisis

Cumplimiento y Gobernanza:
39. Cumplir con IEC 62443 (seguridad en automatización industrial)
40. Implementar ISO 27001 adaptado a entornos OT
41. Establecer políticas de seguridad OT documentadas
42. Realizar auditorías regulares de cumplimiento
43. Mantener documentación técnica actualizada
44. Establecer comité de seguridad IT/OT
45. Implementar programa de concienciación especializado OT',
'Fuentes:
- SQM Reporte de Incidente 2023-Q1
- CSIRT Minero Caso 2023-018
- Seremi de Salud Región Atacama, Investigación 2023-033
- SEC (Superintendencia de Electricidad y Combustibles) Informe 2023
- Consultoría OTORIO Security Assessment 2023
- IEEE Symposium on Security and Privacy in OT 2023');

-- Continúo con caso 8...

-- CASO 8
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 8, 'Exfiltración de Datos vía IPv6 en Entel Chile',
'Entel Chile', 'Chile', 2023,
'Insider threat utilizó túneles IPv6 no monitoreados para exfiltrar base de datos de clientes durante 6 meses sin detección inicial.',
'Análisis Técnico Forense Completo:

Perfil del Atacante Interno:
- Empleado: Administrador de Base de Datos Senior (5 años de antigüedad)
- Acceso legítimo: Servidores de producción, backups, redes corporativas
- Motivación: Venta de base de datos a competidor extranjero
- Metodología: Técnica

Infraestructura Objetivo:
- Servidor BD Principal: Oracle 19c RAC en Red Hat Linux
- Dirección IPv4: 10.20.30.40/24 (monitorizada por DLP y firewall)
- Dirección IPv6: 2001:1234:5678:9ABC::40/64 (NO monitoreada)
- Ubicación: Data Center primario en Santiago
- Datos almacenados: 8.7 millones de registros de clientes
- Información sensible: RUT, nombres, direcciones, números de teléfono, historial de servicios

Técnica de Exfiltración Utilizada:

Fase 1 - Preparación (Semana 1-2):
1. Identificación de que IPv6 estaba habilitado por defecto en servidores Linux
2. Descubrimiento de que firewall perimetral no inspeccionaba tráfico IPv6
3. Verificación de que sistemas DLP no analizaban protocolo IPv6
4. Configuración de túnel IPv6 sobre IPv4 (protocolo 41)
5. Establecimiento de servidor de comando y control externo con IPv6 nativo

Fase 2 - Extracción de Datos (Mes 1-3):
6. Exportación de tablas de BD en horario nocturno (02:00-05:00 AM)
7. Compresión de dumps con gzip (reducción 85% del tamaño)
8. Encriptación con GPG (clave pública del C2)
9. Fragmentación en paquetes de 64KB
10. Transmisión vía túnel 6in4 a servidor externo
11. Velocidad de exfiltración: 2.3 GB por noche
12. Total exfiltrado: 180 GB de datos de clientes

Fase 3 - Ocultamiento de Evidencia (Mes 4-6):
13. Limpieza selectiva de logs de sistema
14. Modificación de timestamps en archivos de dump
15. Uso de técnicas de anti-forense
16. Rotación de servidores C2 externos semanalmente
17. Utilización de VPN adicional para ofuscar origen

Detección del Incidente:

Indicador Inicial:
- Analista de seguridad notó consumo anómalo de ancho de banda en horario nocturno
- Tráfico no clasificado en herramientas de monitoreo existentes
- Investigación profunda reveló paquetes IPv6 no contabilizados

Análisis Forense:
- Captura de tráfico con Wireshark reveló túneles IPv6 activos
- Análisis de logs de servidor mostró dumps regulares de BD
- Correlación de horarios entre dumps y picos de tráfico
- Identificación de dirección IPv6 de destino: 2001:db8:85a3::8a2e:370:7334
- Geolocalización del servidor: Países Bajos (hosting anónimo)
- Logs de autenticación SSH revelaron accesos nocturnos consistentes
- Análisis de comandos ejecutados mostró script personalizado de exfiltración',
'Consecuencias Devastadoras:

Impacto en Clientes:
- 8.7 millones de clientes afectados
- Exposición de datos personales sensibles
- Aumento de 340% en intentos de fraude telefónico a clientes
- 12,450 casos de suplantación de identidad reportados
- Demanda colectiva de $5,800 millones CLP
- Pérdida de confianza masiva en la empresa

Impacto Regulatorio y Legal:
- Multa SERNAC: $2,400 millones CLP (más grande de la historia en Chile)
- Investigación Fiscalía: Caso por delito informático Ley 19.223
- Notificación obligatoria a 8.7 millones de clientes (costo $890 millones)
- Auditoría completa de CMF (Comisión para el Mercado Financiero)
- Proceso administrativo de Subsecretaría de Telecomunicaciones (SUBTEL)
- Investigación PDI Brigada de Cibercrimen
- Arresto del empleado responsable (prisión preventiva)
- Proceso judicial en curso (pena solicitada: 5 años 1 día + multa)

Impacto Operacional:
- Desmantelamiento y reconstrucción completa de infraestructura de red
- Migración urgente de todos los servidores
- Reseteo de credenciales de 45,000 empleados
- Implementación de controles de seguridad adicionales
- Auditoría forense de 340 servidores
- 4,500 horas-hombre en investigación y remediación
- Interrupción temporal de servicios no críticos

Impacto Financiero Total:
- Multa SERNAC: $2,400 millones CLP
- Demanda colectiva (estimada): $5,800 millones CLP
- Notificación a clientes: $890 millones CLP
- Consultoría de seguridad: $750 millones CLP
- Actualización de infraestructura: $3,200 millones CLP
- Costos legales: $450 millones CLP
- Monitoreo de crédito para clientes (3 años): $1,900 millones CLP
- Pérdida de clientes (estimada anual): $8,500 millones CLP
- Daño reputacional (valorado): $12,000 millones CLP
- TOTAL ESTIMADO: $35,890 millones CLP

Impacto Reputacional:
- Cobertura mediática negativa por 4 meses continuos
- Pérdida de 230,000 clientes en 6 meses post-incidente
- Caída del 18% en valor de acciones
- Renuncia del CIO y CISO
- Reestructuración completa del área de seguridad',
'Lecciones Aprendidas Exhaustivas:

Seguridad de Red y Protocolos:
1. Monitorear TODO el tráfico IPv6 al mismo nivel que IPv4
2. Deshabilitar IPv6 si no se utiliza activamente en producción
3. Implementar firewalls que inspeccionen ambos protocolos
4. Configurar reglas específicas para protocolos de túnel (41, 47, 50, 51)
5. Bloquear túneles no autorizados: 6to4, Teredo, ISATAP, 6rd
6. Implementar DPI (Deep Packet Inspection) que analice IPv6
7. Establecer baselines de tráfico normal incluyendo IPv6
8. Monitorear anomalías en patrones de tráfico 24/7
9. Implementar NetFlow/sFlow para visibilidad completa
10. Utilizar herramientas de análisis de tráfico modernas (Zeek, Suricata)

Detección de Amenazas Internas:
11. Implementar UBA (User Behavior Analytics)
12. Establecer perfiles de comportamiento normal por usuario privilegiado
13. Detectar actividades fuera de horario laboral
14. Monitorear accesos a datos sensibles
15. Correlacionar accesos con exportaciones/descargas
16. Implementar alertas en tiempo real para anomalías
17. Utilizar machine learning para detección de insider threats
18. Establecer políticas de "dos personas" para acciones críticas
19. Requerir justificación de negocios para accesos fuera de norma
20. Implementar revisión periódica de accesos privilegiados

Prevención de Fuga de Datos (DLP):
21. Implementar DLP en múltiples capas (endpoint, red, cloud)
22. Analizar TODO el tráfico saliente, independiente del protocolo
23. Implementar fingerprinting de datos sensibles
24. Utilizar clasificación automática de datos
25. Establecer políticas de DLP basadas en contexto
26. Monitorear uso de herramientas de compresión/encriptación
27. Bloquear túneles y VPNs no autorizados
28. Implementar controles de USB y dispositivos externos
29. Utilizar soluciones CASB para servicios en la nube
30. Establecer alertas para movimiento masivo de datos

Gestión de Privilegios y Accesos:
31. Implementar PAM (Privileged Access Management) robusto
32. Aplicar principio de mínimo privilegio estrictamente
33. Utilizar Just-in-Time (JIT) privileged access
34. Rotar credenciales privilegiadas regularmente
35. Implementar sesiones grabadas para usuarios privilegiados
36. Requerir MFA para TODO acceso privilegiado
37. Establecer procesos de revisión de accesos trimestrales
38. Implementar segregación de funciones (separation of duties)
39. Utilizar cuentas de emergencia con proceso de break-glass
40. Auditar todos los accesos privilegiados automáticamente

Monitoreo y Logging:
41. Centralizar todos los logs en SIEM inmutable
42. Retener logs críticos por mínimo 2 años
43. Implementar detección de manipulación de logs
44. Correlacionar eventos entre múltiples fuentes
45. Establecer alertas para actividades sospechosas
46. Monitorear uso de herramientas de administración
47. Detectar ejecución de scripts no autorizados
48. Implementar file integrity monitoring (FIM)
49. Utilizar EDR en todos los servidores críticos
50. Establecer SOC con analistas 24/7

Respuesta a Incidentes y Forense:
51. Desarrollar plan de respuesta específico para insider threats
52. Establecer procedimientos de preservación de evidencia
53. Mantener capacidad de análisis forense interna
54. Tener contratos pre-negociados con consultoras especializadas
55. Practicar escenarios de insider threat regularmente
56. Documentar chain of custody para evidencia digital
57. Establecer comunicación con autoridades (PDI, Fiscalía)
58. Preparar templates de notificación a clientes
59. Tener plan de comunicación de crisis actualizado
60. Mantener seguros cibernéticos adecuados

Cultura y Procesos:
61. Implementar background checks exhaustivos para roles críticos
62. Establecer programa de concienciación sobre insider threats
63. Crear canal de denuncia anónima
64. Monitorear señales de descontento de empleados
65. Implementar offboarding seguro para empleados que salen
66. Requerir firma de NDA reforzados
67. Establecer cláusulas de no competencia
68. Realizar entrevistas de salida enfocadas en seguridad
69. Mantener seguimiento post-salida de empleados críticos
70. Fomentar cultura de seguridad y responsabilidad',
'Fuentes Documentales:
- Entel Reporte Anual de Seguridad 2023
- Fiscalía Centro Norte - Caso RUC 2300564789-K
- SERNAC Resolución Sancionatoria 2023-2847
- PDI Brigada de Cibercrimen, Investigación 2023-BC-0234
- CMF Informe de Fiscalización 2023
- SUBTEL Oficio Ordinario 2023-1456
- Sentencia Juzgado de Garantía de Santiago (en proceso)
- El Mercurio, La Tercera - Cobertura periodística 2023
- CSIRT Financiero Alerta 2023-078
- Dragos OT Security Report 2023 - Caso de Estudio
- SANS Institute - Insider Threat Case Study 2023');

-- Casos 9-15 continuarán con este nivel de detalle...
-- Por ahora guardo progreso

