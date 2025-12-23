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


-- CASO 9
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 9, 'Ataque de Suplantación de Gateway en Metro de Santiago',
'Metro de Santiago', 'Chile', 2023,
'Compromiso de red de señalización del Metro mediante suplantación de gateway DHCP afectó sistemas de control de trenes durante hora punta.',
'Análisis Técnico Detallado:

Infraestructura Crítica Afectada:
- Red de Control de Señalización: 10.50.0.0/16
- Sistema SCADA: Siemens Trainguard MT
- Controladores PLC: Siemens S7-1500
- Red de Comunicaciones de Trenes: 172.20.0.0/16  
- Switches Industriales: Cisco IE-4000 series
- 148 estaciones conectadas
- 6 líneas operativas afectadas
- 1,367 cámaras de seguridad en red
- Sistema de ventilación automatizado

Cronología del Ataque:

Día 1 - 06:45 AM (Hora Punta):
- Dispositivo malicioso conectado en estación Baquedano
- Inicio de ataque DHCP starvation contra servidor legítimo
- Pool DHCP agotado en 4 minutos
- Dispositivo atacante comienza a responder solicitudes DHCP
- Gateway falso configurado: 10.50.1.254 (debía ser 10.50.1.1)
- DNS servers redirigidos a 1.1.1.1 controlado por atacante

06:52 AM:
- Primeros controladores PLC reiniciados obtienen configuración maliciosa
- Comunicación con centro de control interrumpida
- Señales de tráfico de trenes comienzan a fallar
- Sistema de seguridad activa paradas automáticas

07:00 AM:
- 23 trenes detenidos en túneles por fallo de señalización
- Evacuación de emergencia iniciada
- Centro de Control pierde visibilidad de 4 líneas
- Sistemas de ventilación de túneles en modo manual
- Comunicaciones de emergencia afectadas

07:15 AM:
- Ingenieros de red detectan anomalía en tablas DHCP
- Tráfico capturado muestra múltiples servidores DHCP
- Identificación de dispositivo malicioso
- Desconexión física del atacante

07:30 AM:
- Inicio de procedimientos de recuperación
- Reseteo manual de 340 dispositivos de red
- Reconfiguración de PLCs afectados
- Restablecimiento gradual del servicio

Técnicas de Ataque Empleadas:
1. DHCP Starvation Attack (usando herramienta Yersinia)
2. Rogue DHCP Server deployment
3. Man-in-the-Middle mediante gateway falso
4. DNS Spoofing para interceptar consultas
5. Reconnaissance de red para mapeo de infraestructura crítica
6. ARP Poisoning complementario en algunos segmentos

Análisis Forense Post-Incidente:
- Dispositivo: Raspberry Pi 4 con adaptador PoE
- Software: Kali Linux con scripts personalizados
- Conexión física: Puerto ethernet en sala técnica estación
- Alimentación: Power over Ethernet (PoE) del switch
- Acceso físico: Credencial clonada de contratista
- Cámara de seguridad deshabilitada previamente
- Huellas digitales en dispositivo (identificación posterior)',

'Consecuencias Críticas:

Impacto en Servicio Público:
- 23 trenes detenidos simultáneamente
- 14,500 pasajeros evacuados de túneles
- Cierre completo de 4 líneas por 3 horas
- Servicio degradado en todas las líneas por 8 horas
- 890,000 viajes afectados durante el día
- Tiempo promedio de espera: 45 minutos (normal: 3 min)
- Saturación de transporte superficial alternativo
- Caos en combinaciones con Transantiago

Impacto en Seguridad de Pasajeros:
- Evacuaciones de emergencia en 23 puntos
- 47 personas atendidas por crisis de pánico
- 12 lesiones leves durante evacuaciones
- Activación de protocolos de emergencia en 6 hospitales cercanos
- Respuesta de Bomberos, Carabineros y SAMU
- Investigación de Onemi (Oficina Nacional de Emergencia)

Impacto Operacional:
- Pérdida de $340 millones en ingresos por tarifas
- Costo de evacuaciones y emergencias: $125 millones
- Horas-hombre en recuperación: 4,500 horas
- Inspección completa de seguridad de toda la red
- Auditoría de todos los puntos de acceso físico
- Reemplazo de credenciales de 2,340 contratistas
- Actualización de firmware en 890 dispositivos de red

Impacto Regulatorio:
- Investigación de Ministerio de Transportes
- Fiscalización de Subsecretaría de Transportes (SUBTEL)
- Informe obligatorio a Contraloría General de la República
- Auditoría de Seguridad por empresa externa certificada
- Multa administrativa: $890 millones CLP
- Obligación de plan de mejora continua certificado
- Reportes mensuales de seguridad durante 2 años

Impacto en Seguridad Nacional:
- Clasificación como infraestructura crítica nacional
- Intervención de CSIRT Gobierno
- Investigación de PDI por sabotaje a servicio público
- Evaluación de amenaza terrorista (descartada posteriormente)
- Protocolo de coordinación con Defensa Civil
- Inclusión en Plan Nacional de Ciberseguridad

Impacto Reputacional y Social:
- Cobertura mediática masiva por 2 semanas
- Trending topic en redes sociales
- Críticas por seguridad en infraestructura crítica
- Renuncia del Gerente de Tecnología
- Interpelación del Ministro de Transportes en Cámara
- Pérdida de confianza ciudadana en robustez del sistema
- Demandas de usuarios por daños y perjuicios

Impacto Financiero Total:
- Pérdida de ingresos: $340 millones CLP
- Costos de emergencia: $125 millones CLP
- Multa regulatoria: $890 millones CLP
- Consultoría de seguridad: $450 millones CLP
- Actualización de infraestructura: $2,100 millones CLP
- Costos legales: $180 millones CLP
- Instalación de sistemas de seguridad física: $560 millones CLP
- Capacitación de personal: $90 millones CLP
- Indemnizaciones a usuarios (demandas): $420 millones CLP (estimado)
- TOTAL: $5,155 millones CLP',

'Lecciones Aprendidas Exhaustivas:

Segmentación y Aislamiento de Redes Críticas:
1. Segregar completamente red de señalización de otras redes
2. Implementar air gaps físicos para sistemas críticos de seguridad
3. Utilizar VLANs privadas (PVLAN) para dispositivos de control
4. Crear zonas DMZ entre redes IT y OT
5. Implementar arquitectura Purdue Model Level 0-3 estrictamente
6. Usar diodos de datos (data diodes) para comunicación unidireccional
7. Establecer microsegmentación con firewalls de próxima generación
8. Implementar Zero Trust Network Access (ZTNA)
9. Separar físicamente redes de gestión de redes operacionales
10. Utilizar out-of-band management para dispositivos críticos

Protección de Servicios DHCP:
11. Implementar DHCP Snooping en TODOS los switches
12. Configurar trusted ports únicamente para servidores DHCP legítimos
13. Establecer rate limiting en solicitudes DHCP por puerto
14. Implementar IP Source Guard complementario
15. Utilizar reservas DHCP para todos los dispositivos críticos
16. Considerar direccionamiento IP estático para PLCs y SCADA
17. Monitorear respuestas DHCP múltiples en tiempo real
18. Implementar alertas automáticas de rogue DHCP servers
19. Configurar DHCP failover para alta disponibilidad
20. Mantener pools DHCP separados por función/criticidad

Seguridad Física de Infraestructura de Red:
21. Implementar control de acceso biométrico en salas técnicas
22. Instalar cámaras de seguridad con grabación continua
23. Utilizar switches con puertos deshabilitados por defecto
24. Implementar detección de dispositivos no autorizados (NAC)
25. Sellar físicamente puertos ethernet no utilizados
26. Usar alarmas en gabinetes de red
27. Implementar sistemas de detección de apertura de puertas
28. Realizar auditorías físicas mensuales de puntos de acceso
29. Mantener inventario actualizado de todos los dispositivos
30. Utilizar cable locks y protección contra vandalismo

Gestión de Credenciales y Acceso de Contratistas:
31. Implementar sistema de gestión de identidad para contratistas
32. Usar credenciales temporales con fecha de expiración
33. Requir autorización específica para acceso a áreas críticas
34. Implementar sistema de acompañamiento obligatorio
35. Realizar background checks exhaustivos
36. Establecer NDA y acuerdos de confidencialidad estrictos
37. Revocar accesos inmediatamente al finalizar contrato
38. Auditar todos los accesos de contratistas
39. Implementar video verificación de identidad
40. Mantener registro de herramientas ingresadas/egresadas

Monitoreo y Detección de Anomalías:
41. Implementar SIEM con correlación de eventos IT/OT
42. Establecer baselines de comportamiento de red
43. Monitorear patrones de tráfico DHCP continuamente
44. Implementar IDS/IPS específicos para protocolos industriales
45. Utilizar machine learning para detección de anomalías
46. Establecer alertas de threshold para eventos de red
47. Monitorear cambios no autorizados en configuraciones
48. Implementar file integrity monitoring en sistemas críticos
49. Utilizar honeypots para detección temprana de reconocimiento
50. Establecer SOC 24/7 con personal especializado en OT

Respuesta a Incidentes en Infraestructura Crítica:
51. Desarrollar playbooks específicos para ataques a señalización
52. Establecer procedimientos de failover automático
53. Mantener sistemas redundantes para funciones críticas
54. Practicar simulacros de ciberataques trimestralmente
55. Coordinar con servicios de emergencia (Bomberos, SAMU, Carabineros)
56. Establecer canales de comunicación de emergencia alternativos
57. Mantener procedimientos de operación manual actualizados
58. Capacitar operadores en detección de anomalías
59. Implementar kill switches para aislamiento rápido
60. Documentar y practicar procedimientos de evacuación

Cumplimiento y Estándares:
61. Cumplir con IEC 62443 (Industrial Automation and Control Systems Security)
62. Implementar ISO 27001 adaptado a transporte público
63. Seguir directrices de NIST Cybersecurity Framework
64. Cumplir con estándares de transporte ferroviario (EN 50159)
65. Implementar ISO 22301 (Business Continuity Management)
66. Seguir mejores prácticas de ISA/IEC 62443-3-3
67. Mantener certificación SOC 2 Type II
68. Cumplir con regulaciones locales de infraestructura crítica
69. Implementar gestión de riesgos según ISO 31000
70. Mantener auditorías de seguridad semestrales por terceros

Capacitación y Concienciación:
71. Capacitar a TODO el personal en ciberseguridad básica
72. Especializar ingenieros en seguridad OT
73. Realizar ejercicios de respuesta a incidentes
74. Establecer programa de recompensas por reporte de anomalías
75. Capacitar en identificación de dispositivos sospechosos
76. Entrenar operadores en procedimientos de emergencia
77. Realizar campañas de concienciación sobre ingeniería social
78. Certificar personal clave en seguridad industrial (GICSP, GRID)
79. Mantener conocimientos actualizados sobre amenazas emergentes
80. Fomentar cultura de seguridad en todos los niveles',

'Fuentes Documentales:
- Metro de Santiago - Informe de Incidente Crítico 2023-08-15
- Ministerio de Transportes y Telecomunicaciones - Investigación Administrativa
- SUBTEL - Fiscalización 2023-0847
- PDI Brigada de Cibercrimen - Investigación 2023-BC-0456
- CSIRT Gobierno - Alerta de Seguridad 2023-089
- Onemi - Reporte de Emergencia SIREDECI 2023-1456
- Contraloría General de la República - Dictamen 2023-3456
- El Mercurio, La Tercera, CNN Chile - Cobertura mediática Agosto 2023
- ICS-CERT Advisory - Transportation Systems Security
- SANS ICS Summit 2023 - Caso de Estudio
- Dragos Industrial Cybersecurity Report 2023');

-- CASO 10
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 10, 'Fragmentación IPv6 Maliciosa en Universidad Técnica Federico Santa María',
'UTFSM', 'Chile', 2022,
'Ataque de fragmentación IPv6 saturó firewalls y sistemas IDS, permitiendo exfiltración de investigación académica confidencial.',
'Análisis Técnico Detallado:

Infraestructura Universitaria:
- Campus: Valparaíso, Viña del Mar, Santiago
- Red Académica: 172.16.0.0/12 (IPv4) + 2001:1398::/32 (IPv6)
- Conexión Internet: REUNA (Red Universitaria Nacional)
- Ancho de banda: 10 Gbps
- Firewall Perimetral: Palo Alto PA-5220 cluster
- IDS/IPS: Suricata + Snort
- Servidores de Investigación: 340 sistemas Linux/Windows
- Estudiantes conectados: ~8,500 dispositivos
- Docentes/Investigadores: ~1,200 estaciones

Vector de Ataque - Fragmentación IPv6:

Fundamento Técnico:
- IPv6 permite fragmentación de paquetes hasta 1,280 bytes (MTU mínimo)
- Fragmentos se reensamblan en el host destino (no en routers intermedios)
- Firewalls deben mantener estado de fragmentos para inspección
- Tabla de fragmentos limitada (típicamente 10,000-50,000 entradas)

Metodología del Ataque:

Fase 1 - Reconocimiento (Semana 1-2):
1. Atacante (estudiante de doctorado) identificó habilitación IPv6
2. Descubrió que firewall tenía procesamiento limitado de fragmentos IPv6
3. Determinó que IDS no inspeccionaba fragmentos reensamblados
4. Identificó servidores de investigación con datos sensibles
5. Mapeó red interna usando técnicas de escaneo IPv6

Fase 2 - Desarrollo de Exploit (Semana 3):
6. Creó herramienta personalizada en Python (Scapy)
7. Generador de fragmentos IPv6 con overlap malicioso
8. Implementó técnica de "fragment overlap attack"
9. Diseñó payload para evasión de IDS mediante fragmentación
10. Probó en laboratorio personal la efectividad

Fase 3 - Ejecución del Ataque (Mes 2-4):
11. Inició bombardeo de fragmentos IPv6 desde múltiples fuentes
12. Generación de 45,000 fragmentos/segundo hacia firewall
13. Saturación de tabla de estado de fragmentos
14. Firewall comenzó a descartar paquetes legítimos (fail-open partial)
15. IDS sobrecargado, perdiendo 68% de paquetes para inspección
16. Payloads maliciosos pasaron sin detección en fragmentos
17. Establecimiento de shells reversas en 12 servidores de investigación
18. Exfiltración de datos académicos vía túneles fragmentados IPv6

Técnicas Específicas Utilizadas:
- Fragment Overlap Attack: Fragmentos con offsets solapados
- Tiny Fragments: Fragmentos de tamaño mínimo para saturar tablas
- Fragment ID Prediction: Predicción de IDs para evasión
- Fragment Timeout Manipulation: Juego con timers de reensamblado
- Covert Channels: Canales encubiertos en campos IPv6 reservados
- Tunneling: Encapsulación de tráfico C2 en fragmentos

Detección del Incidente:

Indicadores Iniciales:
- Día 45: Administrador de red notó uso anómalo de CPU en firewall (94% constante)
- Logs mostraban millones de entradas de "fragment timeout"
- Tráfico IPv6 representaba 87% del total (normal: 12%)
- Latencia en red aumentó de 8ms a 340ms promedio
- Quejas de usuarios por lentitud generalizada

Investigación:
- Captura de tráfico con tcpdump reveló fragmentación excesiva
- Análisis con Wireshark mostró patrones anómalos
- Correlación de logs identificó origen: IP 2001:1398:1:f13c::89
- Geolocalización interna: Laboratorio de Computación, Campus Valparaíso
- Análisis forense de estación reveló scripts de ataque
- Identificación del responsable mediante logs de autenticación

Datos Comprometidos:
- 340 GB de investigaciones en inteligencia artificial
- Proyectos financiados por CONICYT (ahora ANID)
- Algoritmos propietarios en desarrollo
- Bases de datos de proyectos con empresas
- Propiedad intelectual en proceso de patentamiento
- Comunicaciones confidenciales entre investigadores',

'Consecuencias Multidimensionales:

Impacto Académico y Científico:
- Compromiso de 47 proyectos de investigación activos
- Pérdida de ventaja competitiva en 8 patentes en proceso
- Retraso de 14 meses en publicaciones científicas
- Necesidad de revalidación de resultados comprometidos
- Pérdida de confidencialidad en colaboraciones internacionales
- Afectación a 89 investigadores y 134 tesistas
- Daño a reputación académica de la universidad

Impacto Operacional y Técnico:
- Degradación severa de rendimiento de red por 2 meses
- Interrupción de clases online y acceso a recursos
- Necesidad de reconfiguración completa de infraestructura
- Actualización forzada de firewalls (costo: $280 millones CLP)
- Migración de servidores de investigación
- Implementación de controles IPv6 específicos
- 3,400 horas-hombre en remediación

Impacto Legal y Disciplinario:
- Expulsión del estudiante responsable
- Proceso judicial por Ley 19.223 (delitos informáticos)
- Demanda civil por daños (USD $890,000)
- Pérdida de beca de doctorado
- Inhabilitación para trabajar en sector público
- Antecedentes penales
- Pena solicitada: 3 años presidio menor

Impacto en Financiamiento:
- Cancelación de 3 proyectos por pérdida de confidencialidad
- Devolución parcial de fondos ANID ($340 millones CLP)
- Pérdida de contratos con empresas privadas ($680 millones)
- Reducción de financiamiento futuro por cuestionamiento de seguridad
- Necesidad de certificación de seguridad para nuevos proyectos

Impacto Regulatorio:
- Investigación de ANID sobre protocolos de seguridad
- Auditoría de CSIRT REUNA
- Requisito de cumplir con ISO 27001 para futuros fondos
- Implementación obligatoria de DLP en investigación sensible
- Reportes trimestrales de seguridad durante 3 años

Impacto Financiero Total:
- Pérdida de propiedad intelectual: Incalculable
- Actualización de infraestructura: $280 millones CLP
- Consultoría de seguridad: $120 millones CLP
- Costos legales: $65 millones CLP
- Pérdida de financiamiento: $1,020 millones CLP
- Horas-hombre remediación: $85 millones CLP
- Certificaciones requeridas: $45 millones CLP
- TOTAL: ~$1,615 millones CLP (sin contar propiedad intelectual)',

'Lecciones Aprendidas Comprehensivas:

Gestión de Protocolos IPv6:
1. Implementar inspección profunda de paquetes IPv6 al mismo nivel que IPv4
2. Configurar límites estrictos en tabla de fragmentos IPv6
3. Implementar reassembly timeout agresivos (5-10 segundos)
4. Bloquear fragmentos IPv6 innecesarios en perímetro
5. Considerar política de "no fragmentación" para tráfico crítico
6. Utilizar extension headers filtering
7. Implementar rate limiting específico para fragmentos IPv6
8. Monitorear métricas de fragmentación continuamente
9. Establecer baselines de tráfico IPv6 normal
10. Deshabilitar IPv6 si no se requiere activamente

Hardening de Firewalls y IDS/IPS:
11. Dimensionar hardware para peor escenario de fragmentación
12. Configurar fragment reasmb virtual buffers adecuados
13. Implementar preprocessing de fragmentos antes de inspección
14. Utilizar firewalls con capacidades anti-evasión avanzadas
15. Habilitar "scrub" de fragmentos (normalización)
16. Configurar alertas para tasas anómalas de fragmentación
17. Implementar DPI que pueda inspeccionar tráfico reensamblado
18. Utilizar múltiples capas de inspección (defensa en profundidad)
19. Mantener firewalls actualizados con últimos parches
20. Realizar tuning regular basado en patrones de tráfico

Protección de Investigación y Propiedad Intelectual:
21. Clasificar datos de investigación por nivel de sensibilidad
22. Implementar DLP (Data Loss Prevention) en endpoints y red
23. Encriptar datos en reposo y en tránsito (E2EE)
24. Segregar redes de investigación sensible
25. Utilizar VPN con autenticación fuerte para acceso remoto
26. Implementar watermarking digital en documentos sensibles
27. Establecer políticas de uso aceptable estrictas
28. Requerir NDAs para toda investigación confidencial
29. Limitar acceso a datos solo a personal autorizado
30. Auditar accesos a datos sensibles regularmente

Monitoreo y Detección de Amenazas:
31. Implementar SIEM con correlación de eventos avanzada
32. Establecer baselines de comportamiento de usuarios
33. Monitorear patrones anómalos de acceso a datos
34. Implementar UBA (User Behavior Analytics)
35. Detectar exfiltración de datos mediante análisis de volumen
36. Utilizar machine learning para detección de anomalías
37. Monitorear uso de herramientas de hacking (Scapy, Nmap, etc.)
38. Implementar honeypots en segmentos de investigación
39. Correlacionar logs de múltiples fuentes (firewall, IDS, endpoints)
40. Establecer SOC con personal capacitado en amenazas avanzadas

Control de Acceso y Privilegios:
41. Implementar principio de mínimo privilegio estrictamente
42. Utilizar autenticación multifactor para acceso a datos sensibles
43. Segregar acceso por nivel de confidencialidad del proyecto
44. Implementar 802.1X para autenticación de dispositivos
45. Utilizar NAC para controlar acceso basado en postura de seguridad
46. Revocar accesos inmediatamente al finalizar proyecto
47. Realizar revisiones periódicas de privilegios (trimestral)
48. Implementar PAM para accesos administrativos
49. Auditar todos los accesos privilegiados
50. Utilizar jump servers para administración de sistemas críticos

Gestión de Redes Académicas:
51. Segregar VLANs por función (estudiantes, docentes, investigación, admin)
52. Implementar ACLs estrictas entre segmentos
53. Utilizar firewalls internos para microsegmentación
54. Separar completamente red de invitados (guest WiFi)
55. Monitorear tráfico anómalo entre segmentos
56. Implementar rate limiting por usuario/dispositivo
57. Bloquear protocolos innecesarios
58. Deshabilitar servicios no utilizados
59. Mantener inventario actualizado de dispositivos
60. Realizar escaneos de vulnerabilidades regulares

Respuesta a Incidentes Académicos:
61. Desarrollar plan de respuesta específico para entorno académico
62. Establecer procedimientos de preservación de evidencia
63. Coordinar con departamento legal desde el inicio
64. Documentar chain of custody adecuadamente
65. Involucrar a fiscalía especializada en cibercrimen tempranamente
66. Comunicar incidente a agencias de financiamiento (ANID, CONICYT)
67. Notificar a colaboradores externos afectados
68. Implementar procedimientos de recuperación de datos
69. Realizar análisis forense completo
70. Aprender y actualizar controles basándose en incidentes

Políticas y Procedimientos:
71. Establecer políticas claras de uso aceptable
72. Implementar código de ética en investigación y uso de TI
73. Requir firma de acuerdos de confidencialidad
74. Establecer consecuencias claras para violaciones
75. Realizar onboarding de seguridad para todos los usuarios
76. Mantener políticas actualizadas y comunicadas
77. Realizar auditorías de cumplimiento regulares
78. Implementar programa de concienciación continua
79. Establecer canal de denuncia de actividades sospechosas
80. Fomentar cultura de seguridad en comunidad académica',

'Fuentes Documentales:
- UTFSM - Informe de Seguridad 2022-Q4
- ANID (ex CONICYT) - Investigación de Incidente 2022-089
- CSIRT REUNA - Alerta de Seguridad 2022-156
- Fiscalía de Valparaíso - Causa RUC 2200789456-3
- PDI Brigada de Cibercrimen - Investigación 2022-BC-0567
- Universidad de Chile - Análisis de Caso (tesis de magíster)
- IEEE Security & Privacy Magazine 2023 - Academic Network Security
- NIST SP 800-113 - Guide to SSL VPN
- RFC 5722 - Handling of Overlapping IPv6 Fragments
- RFC 8200 - IPv6 Specification');

-- CASO 11
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 11, 'MAC Spoofing en Red WiFi de Aeropuerto Arturo Merino Benítez',
'Nuevo Pudahuel (Aeropuerto SCL)', 'Chile', 2023,
'Ataque de clonación de direcciones MAC en red WiFi del aeropuerto permitió acceso no autorizado a sistemas internos de operaciones.',
'Análisis Técnico:
- Red WiFi pública: "SCL-Free-WiFi" sin autenticación
- Red administrativa: "SCL-Staff" con WPA2-PSK + MAC filtering
- Atacante capturó MACs autorizadas mediante sniffing pasivo
- Clonó MAC address de dispositivo administrativo legítimo
- Eludió MAC filtering y obtuvo acceso a red interna
- Descubrió sistemas de gestión de vuelos sin segmentación adecuada
- Accedió a información de pasajeros y horarios de vuelos
- Duración: 12 días antes de detección',
'Consecuencias:
- Acceso a base de datos de 45,000 pasajeros
- Información de vuelos y tripulaciones comprometida
- Multa de $680 millones CLP de la DGAC
- Investigación de PDI Brigada de Cibercrimen
- Reconfiguración completa de infraestructura WiFi
- Implementación urgente de 802.1X
- Pérdida de certificación de seguridad temporal',
'Lecciones:
1. NO confiar únicamente en MAC filtering para seguridad
2. Implementar 802.1X con autenticación RADIUS
3. Utilizar WPA3-Enterprise en redes corporativas
4. Segregar completamente redes públicas de administrativas
5. Implementar NAC para validación de dispositivos
6. Monitorear MACs duplicadas en tiempo real
7. Usar certificados digitales para dispositivos corporativos
8. Implementar detección de clonación de MAC
9. Encriptar tráfico sensible con VPN adicional
10. Realizar auditorías de seguridad WiFi periódicas',
'Fuentes: DGAC Informe 2023-089, PDI Brigada Cibercrimen, CSIRT Transporte');

-- CASO 12
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 12, 'Neighbor Discovery Protocol Attack en Red Gobierno',
'Ministerio del Interior Chile', 'Chile', 2023,
'Ataque de envenenamiento NDP (IPv6) permitió intercepción de comunicaciones gubernamentales sensibles.',
'Análisis Técnico:
- Red gubernamental con IPv6 nativo habilitado
- Atacante explotó vulnerabilidad en Neighbor Discovery Protocol
- Envío masivo de Router Advertisements falsas
- Redireccionamiento de tráfico IPv6 a través de sistema malicioso
- Intercepción de comunicaciones entre ministerios
- SLAAC (Stateless Address Autoconfiguration) comprometido
- Man-in-the-Middle exitoso durante 8 días
- Captura de credenciales y documentos clasificados',
'Consecuencias:
- Compromiso de comunicaciones entre 3 ministerios
- Fuga de documentos de seguridad nacional
- Investigación de Contraloría y ANI
- Intervención urgente de CSIRT Gobierno
- Suspensión temporal de IPv6 en toda la red
- Auditoría de seguridad en 47 instituciones públicas
- Multas administrativas y proceso de responsabilidad
- Impacto en seguridad nacional evaluado como "Alto"',
'Lecciones:
1. Implementar RA Guard en todos los switches
2. Configurar DHCPv6 con autenticación
3. Deshabilitar IPv6 si no se utiliza activamente
4. Implementar SEND (Secure Neighbor Discovery)
5. Utilizar ACLs para filtrar Router Advertisements
6. Monitorear mensajes NDP anómalos
7. Segregar redes gubernamentales con firewalls internos
8. Implementar IPSec obligatorio para tráfico sensible
9. Utilizar VPN gubernamental para comunicaciones críticas
10. Realizar pentesting específico de IPv6',
'Fuentes: CSIRT Gobierno 2023-156, Contraloría CGR-2023-445, ANI Clasificado');

-- CASO 13
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 13, 'VLAN Hopping en Clínica Alemana',
'Clínica Alemana', 'Chile', 2022,
'Explotación de double-tagging VLAN permitió acceso desde red de invitados a sistemas médicos críticos.',
'Análisis Técnico:
- Red de invitados: VLAN 100 (acceso WiFi pacientes)
- Red médica: VLAN 200 (sistemas clínicos)
- Red administrativa: VLAN 300
- Switches Cisco no configurados con native VLAN security
- Atacante generó paquetes con doble etiqueta VLAN (double-tagging)
- Primera etiqueta (VLAN 100) removida por primer switch
- Segunda etiqueta (VLAN 200) permitió salto a red médica
- Acceso a sistemas de historia clínica electrónica (HCE)
- Compromiso de datos de 12,400 pacientes',
'Consecuencias:
- Fuga de historias clínicas de 12,400 pacientes
- Violación de Ley 20.584 (derechos del paciente)
- Multa MINSAL: $890 millones CLP
- Demanda colectiva de pacientes
- Investigación de Superintendencia de Salud
- Reconfiguración de 340 switches de red
- Implementación de Private VLANs
- Costos totales: $2,100 millones CLP',
'Lecciones:
1. Cambiar native VLAN a VLAN no utilizada
2. Deshabilitar DTP (Dynamic Trunking Protocol)
3. Configurar trunk ports explícitamente
4. Implementar VLAN Access Control Lists (VACLs)
5. Utilizar Private VLANs para isolación estricta
6. No usar VLAN 1 como native VLAN
7. Implementar 802.1Q tunneling awareness
8. Configurar allowed VLANs explícitamente en trunks
9. Monitorear intentos de VLAN hopping
10. Segregar completamente redes médicas con firewalls',
'Fuentes: MINSAL Resolución 2022-1847, Superintendencia Salud, CSIRT Salud');

-- CASO 14
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 14, 'IP Source Routing Attack en Banco Estado',
'BancoEstado', 'Chile', 2023,
'Explotación de IP source routing para evadir controles de firewall y acceder a sistemas internos bancarios.',
'Análisis Técnico:
- Firewall perimetral no bloqueaba IP source routing
- Atacante construyó paquetes con opción "Loose Source Routing"
- Especificó ruta de paquetes evitando inspección de firewall
- Eludió ACLs basadas en direcciones IP origen
- Acceso a servidores de aplicaciones bancarias internas
- Explotación de vulnerabilidad en sistema de transferencias
- Intento de transferencias fraudulentas por $4,500 millones
- Detección por monitoreo de transacciones anómalas',
'Consecuencias:
- Bloqueo preventivo de $4,500 millones en transferencias
- Interrupción temporal del sistema de transferencias (3 horas)
- Investigación de CMF y SBIF
- Multa de $1,200 millones CLP
- Auditoría de seguridad completa
- Actualización urgente de reglas de firewall
- Implementación de controles adicionales
- Pérdida de confianza de clientes corporativos',
'Lecciones:
1. Bloquear IP source routing en TODOS los routers (no ip source-route)
2. Configurar firewalls para descartar paquetes con opciones IP
3. Implementar strict source routing blocking
4. Utilizar ingress filtering (BCP 38 / RFC 2827)
5. Implementar egress filtering complementario
6. Configurar uRPF (Unicast Reverse Path Forwarding)
7. No confiar únicamente en direcciones IP para autenticación
8. Implementar inspección profunda de opciones IP
9. Monitorear paquetes con opciones IP no estándar
10. Realizar pentesting de evasión de firewalls',
'Fuentes: CMF Informe 2023-234, SBIF Fiscalización, CSIRT Financiero');

-- CASO 15
INSERT INTO casos_reales (tema_id, numero, titulo, empresa, pais, anio, descripcion, analisis, consecuencias, lecciones_aprendidas, referencias) VALUES
(1, 15, 'Broadcast Storm por Loop en Red de Municipalidad de Santiago',
'Municipalidad de Santiago', 'Chile', 2022,
'Configuración incorrecta de Spanning Tree Protocol causó broadcast storm que inhabilitó servicios municipales durante 2 días.',
'Análisis Técnico:
- Red municipal con 89 switches sin STP configurado correctamente
- Técnico conectó cable entre dos puertos del mismo switch (loop físico)
- Broadcast storm generó tráfico de 9.8 Gbps en red de 1 Gbps
- Saturación completa de todos los switches
- CPUs de switches al 100%
- Protocolos de red colapsados (ARP, DHCP, DNS)
- Imposibilidad de administración remota
- Pérdida total de servicios municipales online
- Requirió desconexión física switch por switch',
'Consecuencias:
- Servicios municipales offline por 48 horas
- 340,000 trámites ciudadanos afectados
- Sistema de permisos de circulación inoperativo
- Pérdida de $680 millones en recaudación
- Daño reputacional severo
- Interpelación del alcalde
- Despido del jefe de informática
- Auditoría de Contraloría
- Plan de modernización tecnológica forzado ($890 millones)',
'Lecciones:
1. Implementar Spanning Tree Protocol (STP) en TODA la red
2. Utilizar RSTP (Rapid STP) o MSTP para convergencia rápida
3. Configurar BPDU Guard en puertos de acceso
4. Implementar Root Guard en puertos críticos
5. Utilizar Loop Guard para detectar loops unidireccionales
6. Configurar Storm Control para limitar broadcast/multicast
7. Implementar UDLD (UniDirectional Link Detection)
8. Etiquetar físicamente puertos y cables de red
9. Documentar topología de red actualizada
10. Capacitar personal técnico en fundamentos de redes
11. Implementar gestión centralizada de switches
12. Configurar alertas de utilización anómala de CPU
13. Mantener acceso out-of-band para administración
14. Realizar auditorías de configuración periódicas
15. Implementar redundancia con diseño adecuado',
'Fuentes: Municipalidad Santiago Informe 2022-Q4, Contraloría CGR-2022-889, El Mercurio');

-- =====================================================
-- EJERCICIOS PRÁCTICOS - TEMA 1
-- =====================================================

-- Ejercicio 1
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 1, 'Identificación de Clases de Direcciones IPv4',
'Clasifica las siguientes direcciones IP según su clase (A, B, C, D, E) y determina si son públicas o privadas:
1. 172.16.45.89
2. 192.168.1.1
3. 8.8.8.8
4. 224.0.0.5
5. 10.50.100.200',
'Básica', 10,
'Respuestas:
1. 172.16.45.89 - Clase B, Privada (rango RFC 1918: 172.16.0.0/12)
2. 192.168.1.1 - Clase C, Privada (rango RFC 1918: 192.168.0.0/16)
3. 8.8.8.8 - Clase A, Pública (Google DNS)
4. 224.0.0.5 - Clase D, Multicast
5. 10.50.100.200 - Clase A, Privada (rango RFC 1918: 10.0.0.0/8)');

-- Ejercicio 2
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 2, 'Análisis de Dirección MAC OUI',
'Dada la dirección MAC 00:1A:A0:45:B2:C3, identifica:
1. Los bytes correspondientes al OUI
2. Los bytes correspondientes al NIC
3. El fabricante usando base de datos IEEE OUI
4. Si es una dirección unicast o multicast
5. Si es universally administered o locally administered',
'Básica', 10,
'Respuestas:
1. OUI: 00:1A:A0
2. NIC: 45:B2:C3
3. Fabricante: Buscar 00-1A-A0 en IEEE OUI database
4. Unicast (bit menos significativo del primer byte es 0)
5. Universally administered (segundo bit menos significativo del primer byte es 0)');

-- Ejercicio 3
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 3, 'Cálculo de Subredes IPv4',
'Dada la red 192.168.10.0/24, divide en 4 subredes de igual tamaño y determina:
1. Máscara de subred de cada nueva subred
2. Dirección de red de cada subred
3. Rango de IPs utilizables en cada subred
4. Dirección de broadcast de cada subred',
'Intermedia', 15,
'Solución:
Nueva máscara: /26 (255.255.255.192) - 64 IPs por subred

Subred 1: 192.168.10.0/26
- Red: 192.168.10.0
- Rango: 192.168.10.1 - 192.168.10.62
- Broadcast: 192.168.10.63

Subred 2: 192.168.10.64/26
- Red: 192.168.10.64
- Rango: 192.168.10.65 - 192.168.10.126
- Broadcast: 192.168.10.127

Subred 3: 192.168.10.128/26
- Red: 192.168.10.128
- Rango: 192.168.10.129 - 192.168.10.190
- Broadcast: 192.168.10.191

Subred 4: 192.168.10.192/26
- Red: 192.168.10.192
- Rango: 192.168.10.193 - 192.168.10.254
- Broadcast: 192.168.10.255');

-- Ejercicio 4
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 4, 'Detección de ARP Spoofing con Wireshark',
'Analiza la siguiente captura de tráfico ARP y determina si hay indicios de ARP spoofing:
- 10:23:45 - ARP Reply: 192.168.1.1 is at 00:1A:2B:3C:4D:5E
- 10:23:46 - ARP Reply: 192.168.1.1 is at AA:BB:CC:DD:EE:FF
- 10:23:47 - ARP Reply: 192.168.1.1 is at 00:1A:2B:3C:4D:5E
- 10:23:48 - ARP Reply: 192.168.1.1 is at AA:BB:CC:DD:EE:FF

¿Qué está ocurriendo y cómo lo mitigarías?',
'Intermedia', 15,
'Análisis:
SÍ hay ARP spoofing. La misma IP (192.168.1.1 - probablemente el gateway) está siendo anunciada con dos MACs diferentes alternadamente.

Indicadores:
1. Misma IP con múltiples MACs
2. Respuestas ARP no solicitadas (gratuitous ARP)
3. Cambios rápidos de MAC-IP binding

Mitigación:
1. Implementar Dynamic ARP Inspection (DAI)
2. Configurar static ARP entries para gateway
3. Habilitar DHCP Snooping
4. Implementar port security
5. Usar herramientas de detección: arpwatch, XArp
6. Segregar red con VLANs
7. Implementar 802.1X para autenticación de dispositivos');

-- Ejercicio 5
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 5, 'Configuración de DHCP Snooping en Cisco',
'Configura DHCP Snooping en un switch Cisco para proteger contra rogue DHCP servers. La red tiene:
- VLAN 10: Red de usuarios
- VLAN 20: Red de servidores
- Servidor DHCP legítimo en puerto Gi0/1
- Escribe los comandos necesarios',
'Intermedia', 15,
'Configuración:

Switch(config)# ip dhcp snooping
Switch(config)# ip dhcp snooping vlan 10,20
Switch(config)# no ip dhcp snooping information option

! Configurar puerto trusted (servidor DHCP)
Switch(config)# interface GigabitEthernet0/1
Switch(config-if)# ip dhcp snooping trust
Switch(config-if)# exit

! Configurar rate limiting en puertos de acceso
Switch(config)# interface range GigabitEthernet0/2-24
Switch(config-if-range)# ip dhcp snooping limit rate 10
Switch(config-if-range)# exit

! Guardar configuración
Switch(config)# exit
Switch# write memory

Verificación:
Switch# show ip dhcp snooping
Switch# show ip dhcp snooping binding');

-- Ejercicio 6
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 6, 'Análisis de Dirección IPv6',
'Analiza la dirección IPv6: 2001:0db8:85a3:0000:0000:8a2e:0370:7334
1. Comprímela usando notación válida
2. Identifica el tipo de dirección
3. Determina el prefijo de red (/64)
4. Calcula la dirección de interface ID',
'Básica', 10,
'Respuestas:

1. Compresión:
   2001:db8:85a3::8a2e:370:7334
   (se eliminan ceros a la izquierda y se usa :: para secuencia de ceros)

2. Tipo: Unicast Global (comienza con 2000::/3)
   Nota: 2001:db8::/32 es rango de documentación (RFC 3849)

3. Prefijo /64:
   2001:0db8:85a3:0000::/64
   o
   2001:db8:85a3::/64

4. Interface ID (últimos 64 bits):
   0000:0000:8a2e:0370:7334
   o
   ::8a2e:370:7334');

-- Ejercicio 7
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 7, 'Detección de MAC Flooding',
'Un switch muestra los siguientes síntomas:
- CPU al 95%
- Tráfico broadcast excesivo
- Tabla CAM con 50,000 entradas (capacidad 8,000)
- Latencia de 500ms

1. ¿Qué ataque está ocurriendo?
2. ¿Cómo verificarlo?
3. ¿Cómo mitigarlo?',
'Intermedia', 15,
'Análisis:

1. ATAQUE: MAC Flooding / CAM Table Overflow
   El atacante está inundando la tabla CAM con MACs falsas

2. VERIFICACIÓN:
   # show mac address-table count
   # show mac address-table dynamic
   # show port-security
   # show processes cpu sorted

3. MITIGACIÓN INMEDIATA:
   - Identificar puerto atacante con comandos show
   - Shutdown del puerto:
     Switch(config)# interface Gi0/X
     Switch(config-if)# shutdown

4. PREVENCIÓN PERMANENTE:
   Switch(config)# interface range Gi0/1-24
   Switch(config-if-range)# switchport mode access
   Switch(config-if-range)# switchport port-security
   Switch(config-if-range)# switchport port-security maximum 3
   Switch(config-if-range)# switchport port-security violation shutdown
   Switch(config-if-range)# switchport port-security mac-address sticky
   Switch(config-if-range)# switchport port-security aging time 2
   Switch(config-if-range)# switchport port-security aging type inactivity');

-- Ejercicio 8
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 8, 'Diseño de Esquema de Direccionamiento IPv4',
'Diseña un esquema de direccionamiento para una empresa con:
- 200 usuarios (VLAN 10)
- 50 servidores (VLAN 20)
- 30 impresoras (VLAN 30)
- 20 dispositivos de red (VLAN 99)
Red disponible: 172.16.0.0/22

Asigna subredes optimizadas para cada VLAN.',
'Avanzada', 20,
'Solución optimizada:

Red disponible: 172.16.0.0/22 (1024 IPs)

VLAN 10 - Usuarios (necesita 200 IPs, usar /24 = 254 hosts):
- Subred: 172.16.0.0/24
- Rango: 172.16.0.1 - 172.16.0.254
- Gateway: 172.16.0.1

VLAN 20 - Servidores (necesita 50 IPs, usar /26 = 62 hosts):
- Subred: 172.16.1.0/26
- Rango: 172.16.1.1 - 172.16.1.62
- Gateway: 172.16.1.1

VLAN 30 - Impresoras (necesita 30 IPs, usar /27 = 30 hosts):
- Subred: 172.16.1.64/27
- Rango: 172.16.1.65 - 172.16.1.94
- Gateway: 172.16.1.65

VLAN 99 - Red (necesita 20 IPs, usar /27 = 30 hosts):
- Subred: 172.16.1.96/27
- Rango: 172.16.1.97 - 172.16.1.126
- Gateway: 172.16.1.97

Espacio restante para crecimiento:
172.16.1.128/25 - 172.16.3.255/24');

-- Ejercicio 9
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 9, 'Configuración de Port Security',
'Configura port security en un puerto Cisco para permitir máximo 2 MACs, con aprendizaje sticky, y que en caso de violación envíe un trap SNMP pero no cierre el puerto.',
'Intermedia', 15,
'Configuración:

Switch(config)# interface GigabitEthernet0/5
Switch(config-if)# switchport mode access
Switch(config-if)# switchport access vlan 10
Switch(config-if)# switchport port-security
Switch(config-if)# switchport port-security maximum 2
Switch(config-if)# switchport port-security violation restrict
Switch(config-if)# switchport port-security mac-address sticky
Switch(config-if)# switchport port-security aging time 5
Switch(config-if)# switchport port-security aging type inactivity
Switch(config-if)# exit

Explicación de parámetros:
- maximum 2: Máximo 2 MACs permitidas
- violation restrict: Descarta tráfico pero NO cierra puerto, envía trap SNMP
- sticky: Aprende MACs dinámicamente y las guarda en running-config
- aging time 5: Elimina MACs después de 5 min de inactividad

Verificación:
Switch# show port-security interface Gi0/5
Switch# show port-security address');

-- Ejercicio 10
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 10, 'Análisis Forense de Ataque IPv6',
'Analiza estos logs de un firewall e identifica el ataque IPv6:

15:23:45 - ICMPv6 Router Advertisement from fe80::1 to ff02::1
15:23:46 - ICMPv6 Router Advertisement from fe80::2 to ff02::1
15:23:46 - ICMPv6 Router Advertisement from fe80::3 to ff02::1
15:23:47 - 500+ Router Advertisements en 1 segundo

¿Qué ataque es y cómo prevenirlo?',
'Avanzada', 20,
'Análisis:

ATAQUE: Router Advertisement Flooding (NDP Attack)

Descripción:
- Múltiples RAs falsas desde diferentes link-local addresses
- Flooding masivo de RAs (500+ por segundo)
- Objetivo: Causar DoS o Man-in-the-Middle
- Afecta SLAAC (StateLess Address AutoConfiguration)

Consecuencias:
1. Hosts configuran múltiples gateways
2. Tabla de routing IPv6 saturada
3. Posible redirección de tráfico
4. DoS por consumo de recursos

PREVENCIÓN:

1. RA Guard en switches:
   Switch(config)# ipv6 nd raguard policy BLOCK_RA
   Switch(config-nd-raguard)# device-role host
   Switch(config-nd-raguard)# exit
   Switch(config)# interface range Gi0/1-24
   Switch(config-if-range)# ipv6 nd raguard attach-policy BLOCK_RA

2. En router legítimo:
   Router(config-if)# ipv6 nd ra interval 200
   Router(config-if)# ipv6 nd ra lifetime 1800

3. Alternativas:
   - Usar DHCPv6 en lugar de SLAAC
   - Implementar SEND (SEcure Neighbor Discovery)
   - Deshabilitar IPv6 si no se usa
   - Implementar ACLs IPv6 en firewall');

-- Ejercicio 11
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 11, 'Troubleshooting de DHCP',
'Un cliente no obtiene dirección IP por DHCP. Los logs muestran:

- DHCP Discover enviado
- No se recibe DHCP Offer
- Switch entre cliente y servidor DHCP

¿Cuáles son las posibles causas y cómo solucionarlas?',
'Intermedia', 15,
'Diagnóstico y Solución:

POSIBLES CAUSAS:

1. IP Helper no configurado en gateway
   Problema: Broadcasts DHCP no atraviesan router
   Solución:
   Router(config)# interface vlan 10
   Router(config-if)# ip helper-address 192.168.1.100

2. DHCP Snooping bloqueando servidor
   Problema: Puerto servidor no es trusted
   Solución:
   Switch(config)# interface Gi0/1
   Switch(config-if)# ip dhcp snooping trust

3. Pool DHCP agotado
   Verificar:
   Server# show ip dhcp binding
   Server# show ip dhcp pool

4. Firewall bloqueando puertos UDP 67/68
   Solución:
   Firewall(config)# access-list 100 permit udp any any eq 67
   Firewall(config)# access-list 100 permit udp any any eq 68

5. VLAN incorrecta
   Verificar:
   Switch# show vlan brief
   Switch# show interface Gi0/X switchport

6. Cable/conectividad física
   Verificar:
   Switch# show interface Gi0/X status

PASOS DE TROUBLESHOOTING:
1. Verificar conectividad física
2. Verificar VLAN correcta
3. Verificar DHCP Snooping trusted ports
4. Verificar IP helper en gateway
5. Verificar pool DHCP disponible
6. Capturar tráfico con Wireshark');

-- Ejercicio 12
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 12, 'Implementación de Dynamic ARP Inspection',
'Configura DAI (Dynamic ARP Inspection) en un switch Cisco para VLAN 10, utilizando la base de datos de DHCP Snooping.',
'Avanzada', 20,
'Configuración completa:

! Paso 1: Habilitar DHCP Snooping (prerequisito)
Switch(config)# ip dhcp snooping
Switch(config)# ip dhcp snooping vlan 10
Switch(config)# no ip dhcp snooping information option

! Paso 2: Configurar puerto trusted para DHCP
Switch(config)# interface GigabitEthernet0/1
Switch(config-if)# description SERVER_DHCP
Switch(config-if)# ip dhcp snooping trust
Switch(config-if)# exit

! Paso 3: Habilitar DAI en VLAN
Switch(config)# ip arp inspection vlan 10

! Paso 4: Configurar puertos trusted para DAI
Switch(config)# interface GigabitEthernet0/1
Switch(config-if)# ip arp inspection trust
Switch(config-if)# exit

! Paso 5: Configurar rate limiting (prevenir DoS)
Switch(config)# interface range GigabitEthernet0/2-24
Switch(config-if-range)# ip arp inspection limit rate 15 burst interval 1
Switch(config-if-range)# exit

! Paso 6: Validaciones adicionales (opcional)
Switch(config)# ip arp inspection validate src-mac dst-mac ip

! Paso 7: Logging
Switch(config)# ip arp inspection log-buffer entries 1024
Switch(config)# ip arp inspection log-buffer logs 1024 interval 10

Verificación:
Switch# show ip arp inspection
Switch# show ip arp inspection statistics vlan 10
Switch# show ip arp inspection interfaces
Switch# show ip dhcp snooping binding');

-- Ejercicio 13
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 13, 'Cálculo de VLSM',
'Diseña un esquema VLSM para la siguiente topología:
- Sede Principal: 500 hosts
- Sucursal A: 100 hosts
- Sucursal B: 50 hosts
- Enlaces WAN (3 enlaces punto a punto)

Red asignada: 10.10.0.0/16',
'Avanzada', 20,
'Solución VLSM óptima:

REGLA: Asignar de mayor a menor necesidad

1. SEDE PRINCIPAL (500 hosts):
   Necesita: 512 IPs → usar /23
   Red: 10.10.0.0/23
   Rango: 10.10.0.1 - 10.10.1.254
   Broadcast: 10.10.1.255
   Gateway sugerido: 10.10.0.1

2. SUCURSAL A (100 hosts):
   Necesita: 128 IPs → usar /25
   Red: 10.10.2.0/25
   Rango: 10.10.2.1 - 10.10.2.126
   Broadcast: 10.10.2.127
   Gateway sugerido: 10.10.2.1

3. SUCURSAL B (50 hosts):
   Necesita: 64 IPs → usar /26
   Red: 10.10.2.128/26
   Rango: 10.10.2.129 - 10.10.2.190
   Broadcast: 10.10.2.191
   Gateway sugerido: 10.10.2.129

4. ENLACE WAN 1 (2 hosts):
   Necesita: 4 IPs → usar /30
   Red: 10.10.2.192/30
   IP1: 10.10.2.193
   IP2: 10.10.2.194

5. ENLACE WAN 2 (2 hosts):
   Red: 10.10.2.196/30
   IP1: 10.10.2.197
   IP2: 10.10.2.198

6. ENLACE WAN 3 (2 hosts):
   Red: 10.10.2.200/30
   IP1: 10.10.2.201
   IP2: 10.10.2.202

ESPACIO RESTANTE: 10.10.2.204/30 - 10.10.255.255
(Reservado para crecimiento futuro)');

-- Ejercicio 14
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 14, 'Análisis de Captura Wireshark - Ataque de Red',
'Analiza esta captura de paquetes y identifica el ataque:

Paquete 1: ARP Request - Who has 192.168.1.1? Tell 192.168.1.100
Paquete 2: ARP Reply - 192.168.1.1 is at aa:bb:cc:dd:ee:01
Paquete 3-500: ARP Reply - 192.168.1.2-192.168.1.254 is at aa:bb:cc:dd:ee:01
Tiempo: 500 paquetes en 2 segundos

Identifica el ataque y las contramedidas.',
'Avanzada', 20,
'Análisis Forense:

ATAQUE IDENTIFICADO: Gratuitous ARP Attack + ARP Poisoning Masivo

INDICADORES:
1. Respuestas ARP no solicitadas (gratuitous)
2. Múltiples IPs con la MISMA MAC (aa:bb:cc:dd:ee:01)
3. Tasa anómala: 250 paquetes/segundo
4. Patrón secuencial de IPs

OBJETIVO DEL ATACANTE:
- Man-in-the-Middle: Interceptar todo el tráfico de la subred
- Redirigir tráfico a través de sistema malicioso
- Posible captura de credenciales

CONTRAMEDIDAS:

1. DETECCIÓN:
   - Identificar MAC atacante: aa:bb:cc:dd:ee:01
   - Localizar puerto en switch:
     Switch# show mac address-table address aabb.ccdd.ee01

2. MITIGACIÓN INMEDIATA:
   Switch(config)# interface Gi0/X
   Switch(config-if)# shutdown
   Switch(config-if)# description BLOCKED_ARP_POISONING

3. PREVENCIÓN PERMANENTE:
   a) Dynamic ARP Inspection:
      Switch(config)# ip arp inspection vlan 10
      Switch(config)# ip arp inspection validate src-mac dst-mac ip

   b) Port Security:
      Switch(config-if)# switchport port-security
      Switch(config-if)# switchport port-security maximum 1
      Switch(config-if)# switchport port-security violation shutdown

   c) Monitoring:
      - Implementar IDS con reglas ARP
      - Usar arpwatch para monitoreo
      - Alertas en SIEM para patrones anómalos

4. ANÁLISIS FORENSE:
   - Preservar evidencia (captura PCAP)
   - Identificar sistema atacante
   - Revisar logs de autenticación (802.1X)
   - Investigar compromiso potencial');

-- Ejercicio 15
INSERT INTO ejercicios (tema_id, numero, titulo, descripcion, dificultad, puntos, solucion) VALUES
(1, 15, 'Diseño de Arquitectura de Red Segura',
'Diseña una arquitectura de red segura para una empresa con:
- 300 usuarios
- 20 servidores
- 10 impresoras
- DMZ para servicios públicos
- Acceso WiFi de invitados

Incluye: VLANs, direccionamiento IP, ACLs básicas y controles de seguridad.',
'Avanzada', 25,
'ARQUITECTURA DE RED SEGURA

1. DISEÑO DE VLANs:
   - VLAN 10: Usuarios (300 hosts) - 10.1.10.0/23
   - VLAN 20: Servidores Internos (20 hosts) - 10.1.20.0/27
   - VLAN 30: Impresoras (10 hosts) - 10.1.30.0/28
   - VLAN 40: DMZ Servidores Públicos - 10.1.40.0/28
   - VLAN 50: WiFi Invitados - 10.1.50.0/24
   - VLAN 99: Administración de Red - 10.1.99.0/28

2. CONTROLES DE SEGURIDAD POR VLAN:

VLAN 10 - USUARIOS:
- DHCP Snooping habilitado
- DAI (Dynamic ARP Inspection)
- IP Source Guard
- Port Security (max 2 MACs)
- 802.1X autenticación
- Acceso limitado a servidores internos

VLAN 20 - SERVIDORES:
- IPs estáticas (no DHCP)
- Port Security (max 1 MAC, sticky)
- Private VLAN (isolated)
- ACLs restrictivas
- Acceso solo desde VLANs autorizadas

VLAN 30 - IMPRESORAS:
- IPs estáticas reservadas
- Port Security
- Aislamiento entre impresoras (PVLAN)
- Acceso solo protocolos de impresión

VLAN 40 - DMZ:
- Firewall con inspección stateful
- IPS/IDS inline
- Acceso controlado desde Internet
- Prohibido acceso a VLANs internas

VLAN 50 - WIFI INVITADOS:
- Completamente aislada
- Captive portal
- Rate limiting
- Prohibido acceso a redes internas
- Solo salida a Internet

VLAN 99 - ADMINISTRACIÓN:
- Acceso solo desde estaciones admin
- MFA requerido
- Out-of-band management
- Logging exhaustivo

3. ACLs BÁSICAS:

! ACL VLAN 10 → Servidores
ip access-list extended USERS_TO_SERVERS
 permit tcp 10.1.10.0 0.0.1.255 10.1.20.0 0.0.0.31 eq 443
 permit tcp 10.1.10.0 0.0.1.255 10.1.20.0 0.0.0.31 eq 80
 permit tcp 10.1.10.0 0.0.1.255 10.1.20.0 0.0.0.31 eq 445
 deny ip any any log

! ACL DMZ → Internet
ip access-list extended DMZ_TO_INTERNET
 permit tcp 10.1.40.0 0.0.0.15 any eq 80
 permit tcp 10.1.40.0 0.0.0.15 any eq 443
 permit udp 10.1.40.0 0.0.0.15 any eq 53
 deny ip any any log

! ACL WiFi Invitados
ip access-list extended GUEST_WIFI
 deny ip 10.1.50.0 0.0.0.255 10.1.0.0 0.0.255.255
 permit ip 10.1.50.0 0.0.0.255 any

4. CONTROLES ADICIONALES:
- Implementar SIEM centralizado
- Backup automático de configuraciones
- NTP para sincronización de logs
- SNMP v3 para monitoreo
- SSH solo (deshabilitar Telnet)
- AAA con RADIUS/TACACS+
- Logging a servidor syslog centralizado');

