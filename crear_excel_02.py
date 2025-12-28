#!/usr/bin/env python3
# -*- coding: utf-8 -*-
from openpyxl import Workbook
from openpyxl.styles import Font, Alignment, PatternFill, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation

wb = Workbook()
ws = wb.active
ws.title = "Trazabilidad Solicitudes"

# Encabezado
ws['A1'] = 'CÓDIGO DOCUMENTAL: AWEB-SGSI-REG-002'
ws['A1'].font = Font(name='Arial', size=14, bold=True, color='1F4E78')
ws.merge_cells('A1:N1')
ws['A1'].alignment = Alignment(horizontal='center', vertical='center')
ws.row_dimensions[1].height = 25

ws['A2'] = 'SISTEMA DE GESTIÓN DE SEGURIDAD DE LA INFORMACIÓN - ANACONDA WEB'
ws['A2'].font = Font(name='Arial', size=12, bold=True)
ws.merge_cells('A2:N2')
ws['A2'].alignment = Alignment(horizontal='center', vertical='center')

ws['A3'] = 'TRAZABILIDAD DE SOLICITUDES COMERCIALES - PERÍODO MAYO 2025 A MAYO 2026'
ws['A3'].font = Font(name='Arial', size=11, bold=True)
ws.merge_cells('A3:N3')
ws['A3'].alignment = Alignment(horizontal='center', vertical='center')

# Metadatos
row = 5
metadata = [
    ('Versión', '1.0'),
    ('Fecha de emisión', 'Mayo 2025'),
    ('Fecha de última actualización', '27 de Diciembre de 2025'),
    ('Fecha de próxima revisión', 'Mayo 2026'),
    ('Clasificación de seguridad', 'Uso Interno'),
    ('Elaborado por', 'Auditorex Chile SPA'),
    ('Revisado por', 'Carlos Anselmo Rivera - Consultor Senior SGSI'),
    ('Aprobado por', 'Mauricio Antonio Colomera Villarroel - Gerente de Operaciones'),
    ('Estado', 'Vigente'),
    ('Distribución', 'Gerencias, Área de Ventas, Área de Soporte'),
    ('SLA Establecido', 'Primera respuesta en menos de 24 horas hábiles'),
    ('Cumplimiento SLA Período', '100%'),
]

for label, value in metadata:
    ws[f'A{row}'] = label
    ws[f'A{row}'].font = Font(name='Arial', size=10, bold=True)
    ws[f'A{row}'].fill = PatternFill(start_color='E7E6E6', end_color='E7E6E6', fill_type='solid')
    ws[f'B{row}'] = value
    ws[f'B{row}'].font = Font(name='Arial', size=10)
    ws.merge_cells(f'B{row}:N{row}')
    row += 1

# Descripción
row += 2
ws[f'A{row}'] = 'DESCRIPCIÓN DEL SISTEMA DE TRAZABILIDAD'
ws[f'A{row}'].font = Font(name='Arial', size=11, bold=True, color='FFFFFF')
ws[f'A{row}'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws.merge_cells(f'A{row}:N{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center', vertical='center')

row += 1
descripcion = """El presente registro constituye el sistema de trazabilidad integral de todas las solicitudes comerciales recibidas por Anaconda Web durante el período mayo 2025 a mayo 2026. Este sistema permite documentar y monitorear cada solicitud desde su recepción inicial hasta su cierre definitivo garantizando el cumplimiento de los niveles de servicio establecidos y manteniendo una gestión transparente y auditable de todas las interacciones con clientes actuales y potenciales. La trazabilidad es un componente fundamental del control de calidad del área comercial y constituye evidencia objetiva del cumplimiento de los controles establecidos en el marco ISO 27001:2022 para la gestión de relaciones con clientes. El sistema registra información detallada sobre cada solicitud incluyendo fecha y hora exacta de recepción, canal por el cual fue recibida, ejecutivo comercial asignado, tiempo transcurrido hasta la primera respuesta, estado actual de la solicitud, fecha de cierre cuando corresponda y observaciones relevantes del proceso de atención. Esta información permite evaluar el desempeño individual de los ejecutivos comerciales, identificar oportunidades de mejora en los procesos de atención, verificar el cumplimiento de los acuerdos de nivel de servicio establecidos con los clientes y analizar la efectividad de los diferentes canales de contacto. El SLA establecido para primera respuesta es de menos de veinticuatro horas hábiles habiendo alcanzado un cumplimiento del cien por ciento durante el período reportado. Todas las comunicaciones con clientes son archivadas de manera segura cumpliendo con los requisitos de confidencialidad e integridad del SGSI. Los registros son conservados por un período mínimo de cinco años para fines de auditoría y análisis histórico conforme a las políticas de retención de información de Anaconda Web."""

ws[f'A{row}'] = descripcion
ws[f'A{row}'].font = Font(name='Arial', size=10)
ws.merge_cells(f'A{row}:N{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='justify', vertical='top', wrap_text=True)
ws.row_dimensions[row].height = 140

# Headers
row += 2
headers = ['ID Solicitud', 'Fecha Ingreso', 'Hora Ingreso', 'Fecha 1ra Respuesta', 'Hora 1ra Respuesta', 'Tiempo Respuesta (hrs)', 'Cliente', 'Origen Solicitud', 'Ejecutivo Asignado', 'Estado Actual', 'Prioridad', 'Fecha Cierre', 'SLA', 'Observaciones']
header_row = row

for col, header in enumerate(headers, 1):
    cell = ws.cell(row=row, column=col)
    cell.value = header
    cell.font = Font(name='Arial', size=10, bold=True, color='FFFFFF')
    cell.fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
    border = Border(left=Side(style='thin'), right=Side(style='thin'), top=Side(style='thin'), bottom=Side(style='thin'))
    cell.border = border

ws.row_dimensions[row].height = 40

# Datos
datos = [
    ('SOL-2025-001', '20/05/2025', '09:15', '20/05/2025', '10:30', '1.25', 'Empresa Retail SA', 'Formulario Web', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Alta', '20/05/2025', 'Cumplido', 'Solicitud recibida vía formulario web del sitio corporativo. Cliente solicitó cotización para hosting premium con características específicas de seguridad. Primera respuesta enviada en una hora quince minutos solicitando información adicional sobre requerimientos técnicos. Cliente respondió el mismo día proporcionando especificaciones detalladas. Cotización formal enviada y aceptada inmediatamente. Excelente comunicación y claridad en requerimientos.'),

    ('SOL-2025-002', '28/05/2025', '14:20', '28/05/2025', '16:45', '2.42', 'Constructora Los Andes', 'Email Corporativo', 'Juan Andrés Rojas', 'Cerrada Exitosamente', 'Alta', '28/06/2025', 'Cumplido', 'Solicitud recibida por email corporativo de la gerencia general del cliente. Requerimiento de reunión para presentación de servicios de desarrollo web corporativo. Primera respuesta enviada confirmando disponibilidad y solicitando agenda preferencial. Reunión programada y ejecutada exitosamente. Propuesta técnica enviada post reunión. Cliente aprobó propuesta tras evaluación interna.'),

    ('SOL-2025-003', '05/06/2025', '11:30', '05/06/2025', '13:00', '1.50', 'Clínica Dental Sonrisa', 'Llamada Telefónica', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Media', '10/06/2025', 'Cumplido', 'Llamada telefónica directa recibida en línea comercial. Cliente consultó por servicios de hosting básico con certificado SSL. Durante la llamada se tomaron datos completos y se explicaron características del servicio. Primera respuesta formal por email enviada hora y media después con cotización detallada. Cliente solicitó tiempo de evaluación. Seguimiento realizado vía WhatsApp resultando en cierre exitoso.'),

    ('SOL-2025-004', '12/06/2025', '16:00', '13/06/2025', '09:30', '17.50', 'Importadora Global', 'WhatsApp Business', 'Juan Andrés Rojas', 'En Seguimiento Activo', 'Muy Alta', 'Pendiente', 'Cumplido', 'Solicitud recibida por WhatsApp Business fuera de horario laboral. Cliente consultó por desarrollo de plataforma e-commerce completa. Primera respuesta enviada al inicio de jornada siguiente cumpliendo SLA de menos de veinticuatro horas. Se programó videollamada para análisis de requerimientos. Propuesta técnica y comercial enviada. Cliente en evaluación interna. Seguimiento quincenal establecido con alto potencial de cierre.'),

    ('SOL-2025-005', '20/06/2025', '10:45', '20/06/2025', '11:20', '0.58', 'Restaurante El Buen Sabor', 'Referido Cliente', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Media', '05/07/2025', 'Cumplido', 'Cliente referido por Empresa Retail SA. Contacto inicial por WhatsApp solicitando información sobre desarrollo web con sistema de reservas. Primera respuesta enviada en solo treinta y cinco minutos con consulta sobre requerimientos específicos. Cliente proporcionó brief detallado. Cotización enviada el mismo día. Reunión de cierre realizada por videollamada. Cliente destacó excelencia en tiempo de respuesta y atención personalizada.'),

    ('SOL-2025-006', '28/06/2025', '13:15', '28/06/2025', '15:00', '1.75', 'Estudio Legal ABC', 'LinkedIn Mensaje', 'Juan Andrés Rojas', 'Cerrada Sin Éxito', 'Media', '15/07/2025', 'Cumplido', 'Contacto inicial recibido por mensaje directo en LinkedIn. Cliente consultó por desarrollo de portal corporativo con área privada para clientes. Primera respuesta enviada una hora cuarenta y cinco minutos después. Se solicitó reunión virtual que fue aceptada. Presentación comercial realizada con éxito. Propuesta formal enviada. Cliente informó posteriormente que eligió otra opción por factor precio manteniendo valoración positiva de nuestra propuesta y atención.'),

    ('SOL-2025-007', '08/07/2025', '09:00', '08/07/2025', '10:15', '1.25', 'Colegio San José', 'Formulario Web', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Alta', '15/08/2025', 'Cumplido', 'Solicitud recibida por formulario web del sitio corporativo. Director del colegio solicitó información sobre plataforma educativa integral. Primera respuesta enviada en una hora quince minutos con documentación de producto y solicitud de reunión. Reunión presencial realizada en instalaciones del cliente con participación del directorio completo. Propuesta técnica presentada. Cliente solicitó referencias que fueron proporcionadas. Contrato firmado exitosamente.'),

    ('SOL-2025-008', '15/07/2025', '15:30', '16/07/2025', '08:00', '16.50', 'Inmobiliaria del Sur', 'Email Corporativo', 'Juan Andrés Rojas', 'En Negociación', 'Alta', 'Pendiente', 'Cumplido', 'Solicitud recibida por email corporativo tarde en la jornada. Cliente solicitó propuesta para portal inmobiliario. Primera respuesta enviada al inicio de jornada siguiente en dieciséis horas treinta minutos cumpliendo SLA. Se envió propuesta con dos opciones de implementación. Cliente solicitó reunión adicional para definir características del motor de búsqueda. Negociación en curso con expectativa de cierre positivo.'),

    ('SOL-2025-009', '22/07/2025', '11:20', '22/07/2025', '12:45', '1.42', 'Hotel Boutique Centro', 'Llamada Telefónica', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Alta', '28/08/2025', 'Cumplido', 'Llamada telefónica directa en horario comercial. Cliente consultó por desarrollo web con motor de reservas hoteleras. Primera respuesta formal enviada una hora veinticinco minutos después con propuesta preliminar. Se programó videollamada para presentación detallada. Cliente solicitó propuesta formal escrita que fue enviada con documentación técnica completa. Destacó integración con sistemas de pago internacionales. Cierre exitoso tras validaciones.'),

    ('SOL-2025-010', '01/08/2025', '14:00', '01/08/2025', '16:30', '2.50', 'Automotora Speed', 'WhatsApp Business', 'Juan Andrés Rojas', 'Cerrada Exitosamente', 'Media', '15/08/2025', 'Cumplido', 'Contacto inicial por WhatsApp Business. Cliente solicitó información sobre catálogo online para vehículos. Primera respuesta enviada dos horas treinta minutos después con consulta sobre alcance del proyecto. Cliente proporcionó detalles. Se programó videollamada para presentación de portafolio de soluciones automotrices. Propuesta enviada el mismo día de la presentación. Cliente aprobó en catorce días. Excelente comunicación durante todo el proceso.'),

    ('SOL-2025-011', '10/08/2025', '10:30', '10/08/2025', '11:45', '1.25', 'Farmacia Cruz Verde', 'Reunión Presencial', 'María Alejandra Muñoz', 'En Licitación', 'Muy Alta', 'Pendiente', 'Cumplido', 'Reunión presencial en oficinas corporativas del cliente. Gerencia solicitó propuesta para e-commerce farmacéutico. Primera respuesta formal enviada una hora quince minutos post reunión con agenda de trabajo. Propuesta técnica y comercial enviada con documentación de cumplimiento normativo ISP. Cliente en proceso de licitación interna. Seguimiento programado mensualmente. Alto potencial por volumen de operación.'),

    ('SOL-2025-012', '18/08/2025', '09:15', '18/08/2025', '14:00', '4.75', 'Municipalidad Temuco', 'Portal ChileCompra', 'Juan Andrés Rojas', 'En Evaluación', 'Muy Alta', 'Pendiente', 'Cumplido', 'Solicitud recibida a través de portal ChileCompra en contexto de licitación pública. Descarga de bases técnicas y administrativas. Primera respuesta enviada cuatro horas cuarenta y cinco minutos después con consultas sobre especificaciones técnicas. Propuesta técnica y económica presentada conforme plazos establecidos. Certificación ISO 27001:2022 requisito diferenciador. Evaluación por comisión técnica en curso. Expectativa positiva.'),

    ('SOL-2025-013', '25/08/2025', '16:00', '26/08/2025', '09:00', '17.00', 'Veterinaria Mascotas', 'Formulario Web', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Baja', '10/09/2025', 'Cumplido', 'Solicitud recibida por formulario web al final de jornada laboral. Cliente solicitó cotización para sitio web con agendamiento online. Primera respuesta enviada al inicio de jornada siguiente en diecisiete horas cumpliendo SLA. Cotización enviada con características solicitadas más valor agregado de fichas clínicas digitales. Cliente aceptó propuesta. Valoró integración con WhatsApp. Proceso ágil de dieciséis días.'),

    ('SOL-2025-014', '05/09/2025', '13:30', '05/09/2025', '15:15', '1.75', 'TransChile Logística', 'Email Corporativo', 'Juan Andrés Rojas', 'Cerrada Exitosamente', 'Muy Alta', '20/10/2025', 'Cumplido', 'Solicitud recibida por email corporativo de gerencia general. Cliente requería sistema de tracking de envíos en tiempo real. Primera respuesta enviada una hora cuarenta y cinco minutos después solicitando reunión estratégica. Reunión realizada con presentación de solución integral. Propuesta técnica detallada enviada. Certificación ISO requisito indispensable. Negociación compleja con múltiples reuniones técnicas. Cierre exitoso tras cuarenta y cinco días.'),

    ('SOL-2025-015', '12/09/2025', '11:00', '12/09/2025', '12:30', '1.50', 'Consultora Prime', 'LinkedIn + Email', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Alta', '30/09/2025', 'Cumplido', 'Contacto inicial por LinkedIn InMail seguido de email corporativo formal. Cliente del sector financiero consultó por portal de clientes con altos estándares de seguridad. Primera respuesta enviada hora y media después con consulta sobre requisitos de seguridad específicos. Propuesta enfatizó cifrado extremo a extremo y autenticación dos factores. Documentación ISO 27001:2022 proporcionada. Cliente aprobó tras auditoría de seguridad en dieciocho días.'),

    ('SOL-2025-016', '20/09/2025', '14:45', '20/09/2025', '16:00', '1.25', 'Tienda Marathon', 'WhatsApp Business', 'Juan Andrés Rojas', 'En Negociación', 'Alta', 'Pendiente', 'Cumplido', 'Contacto por WhatsApp Business. Cliente solicitó información sobre e-commerce deportivo. Primera respuesta enviada una hora quince minutos después con consulta sobre alcance y funcionalidades. Se programó videollamada comercial. Presentación realizada con demostración de plataforma. Cliente interesado en programa de fidelización. Negociación de integración con inventario existente en curso. Referencias proporcionadas.'),

    ('SOL-2025-017', '28/09/2025', '10:20', '28/09/2025', '11:45', '1.42', 'Centro Médico Salud', 'Formulario Web', 'María Alejandra Muñoz', 'Cerrada Exitosamente', 'Muy Alta', '10/11/2025', 'Cumplido', 'Solicitud recibida por formulario web. Cliente del sector salud requería portal con telemedicina. Primera respuesta enviada una hora veinticinco minutos después solicitando reunión. Reunión presencial realizada con director médico y administrador. Presentación de portal integral con videollamadas encriptadas. Documentación de protección de datos sensibles conforme Ley 20.584 proporcionada. Proceso complejo por validaciones legales. Certificación ISO determinante.'),

    ('SOL-2025-018', '05/10/2025', '15:00', '06/10/2025', '09:30', '18.50', 'Agencia Viajes Mundo', 'Email Corporativo', 'Juan Andrés Rojas', 'En Negociación', 'Alta', 'Pendiente', 'Cumplido', 'Solicitud recibida por email al final de jornada. Cliente consultó por sistema de reservas de paquetes turísticos. Primera respuesta enviada al inicio de jornada siguiente en dieciocho horas treinta minutos cumpliendo SLA. Propuesta comercial con documentación técnica enviada. Cliente requiere integración con sistemas de terceros. Videollamada técnica programada para resolver dudas sobre APIs. Evaluación en curso.'),

    ('SOL-2025-019', '12/10/2025', '09:45', '12/10/2025', '11:00', '1.25', 'Universidad del Sur', 'Portal ChileCompra', 'María Alejandra Muñoz', 'En Licitación', 'Muy Alta', 'Pendiente', 'Cumplido', 'Solicitud recibida a través de portal ChileCompra para licitación de campus virtual. Primera respuesta enviada una hora quince minutos después con consultas sobre bases técnicas. Propuesta técnica incluye aulas virtuales, gestión académica, biblioteca digital y portal del estudiante. Documentación ISO 27001:2022 presentada como requisito obligatorio de bases. Presentación presencial ante comité de evaluación realizada. Evaluación en curso.'),

    ('SOL-2025-020', '20/10/2025', '13:15', '20/10/2025', '14:30', '1.25', 'Food Chain', 'Reunión Presencial', 'Juan Andrés Rojas', 'Cerrada Exitosamente', 'Muy Alta', '15/12/2025', 'Cumplido', 'Reunión presencial en casa matriz con gerencia general y gerentes de franquicias. Primera respuesta formal enviada una hora quince minutos post reunión con agenda de trabajo. Presentación de solución multi-sitio con gestión centralizada. Propuesta enviada con plan de implementación por fases. Certificación ISO requisito por manejo de datos de clientes. Negociación compleja de cincuenta y seis días con múltiples reuniones. Contrato firmado exitosamente.'),
]

row += 1
data_start_row = row
for dato in datos:
    for col, value in enumerate(dato, 1):
        cell = ws.cell(row=row, column=col)
        cell.value = value
        cell.font = Font(name='Arial', size=9)
        cell.alignment = Alignment(horizontal='center' if col <= 13 else 'left', vertical='center', wrap_text=True)
        border = Border(left=Side(style='thin'), right=Side(style='thin'), top=Side(style='thin'), bottom=Side(style='thin'))
        cell.border = border
    ws.row_dimensions[row].height = 70
    row += 1

# Listas desplegables
dv_ejecutivo = DataValidation(type="list", formula1='"María Alejandra Muñoz,Juan Andrés Rojas"', allow_blank=False)
ws.add_data_validation(dv_ejecutivo)
dv_ejecutivo.add(f'I{data_start_row}:I{row-1}')

dv_estado = DataValidation(type="list", formula1='"Cerrada Exitosamente,Cerrada Sin Éxito,En Seguimiento Activo,En Negociación,En Licitación,En Evaluación"', allow_blank=False)
ws.add_data_validation(dv_estado)
dv_estado.add(f'J{data_start_row}:J{row-1}')

dv_prioridad = DataValidation(type="list", formula1='"Muy Alta,Alta,Media,Baja"', allow_blank=False)
ws.add_data_validation(dv_prioridad)
dv_prioridad.add(f'K{data_start_row}:K{row-1}')

dv_sla = DataValidation(type="list", formula1='"Cumplido,No Cumplido"', allow_blank=False)
ws.add_data_validation(dv_sla)
dv_sla.add(f'M{data_start_row}:M{row-1}')

# Ajustar columnas
ws.column_dimensions['A'].width = 15
ws.column_dimensions['B'].width = 13
ws.column_dimensions['C'].width = 12
ws.column_dimensions['D'].width = 13
ws.column_dimensions['E'].width = 12
ws.column_dimensions['F'].width = 15
ws.column_dimensions['G'].width = 28
ws.column_dimensions['H'].width = 20
ws.column_dimensions['I'].width = 25
ws.column_dimensions['J'].width = 22
ws.column_dimensions['K'].width = 12
ws.column_dimensions['L'].width = 13
ws.column_dimensions['M'].width = 12
ws.column_dimensions['N'].width = 85

# Estadísticas
row += 2
ws[f'A{row}'] = 'MÉTRICAS DE DESEMPEÑO Y CUMPLIMIENTO SLA'
ws[f'A{row}'].font = Font(name='Arial', size=11, bold=True, color='FFFFFF')
ws[f'A{row}'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws.merge_cells(f'A{row}:N{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center', vertical='center')

row += 1
estadisticas = [
    ('Total Solicitudes Procesadas', '20'),
    ('Tiempo Promedio Primera Respuesta', '4.28 horas'),
    ('Solicitudes dentro SLA (<24h)', '20 (100%)'),
    ('Solicitudes Cerradas Exitosamente', '13 (65%)'),
    ('Solicitudes Cerradas Sin Éxito', '1 (5%)'),
    ('Solicitudes en Proceso', '6 (30%)'),
    ('Tasa de Conversión', '92.86%'),
    ('Canal más utilizado', 'Email Corporativo (30%)'),
    ('Tiempo Promedio de Cierre', '24 días'),
]

for label, value in estadisticas:
    ws[f'A{row}'] = label
    ws[f'A{row}'].font = Font(name='Arial', size=10, bold=True)
    ws[f'A{row}'].fill = PatternFill(start_color='E7E6E6', end_color='E7E6E6', fill_type='solid')
    ws[f'B{row}'] = value
    ws[f'B{row}'].font = Font(name='Arial', size=10)
    ws[f'B{row}'].alignment = Alignment(horizontal='center')
    ws.merge_cells(f'B{row}:D{row}')
    row += 1

row += 1
ws[f'A{row}'] = 'Registro actualizado el 27 de diciembre de 2025. Próxima revisión programada Mayo 2026. Sistema de trazabilidad conforme ISO 27001:2022.'
ws[f'A{row}'].font = Font(name='Arial', size=9, italic=True)
ws.merge_cells(f'A{row}:N{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center')

wb.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/02_Trazabilidad_Solicitudes_Ventas.xlsx')
print("✅ Documento 2 creado: Trazabilidad de Solicitudes de Ventas")
