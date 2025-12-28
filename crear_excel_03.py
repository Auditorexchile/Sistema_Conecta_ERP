#!/usr/bin/env python3
# -*- coding: utf-8 -*-
from openpyxl import Workbook
from openpyxl.styles import Font, Alignment, PatternFill, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation

wb = Workbook()
ws = wb.active
ws.title = "Auditoría Interacciones"

# Encabezado
ws['A1'] = 'CÓDIGO DOCUMENTAL: AWEB-SGSI-REG-003'
ws['A1'].font = Font(name='Arial', size=14, bold=True, color='1F4E78')
ws.merge_cells('A1:M1')
ws['A1'].alignment = Alignment(horizontal='center', vertical='center')
ws.row_dimensions[1].height = 25

ws['A2'] = 'SISTEMA DE GESTIÓN DE SEGURIDAD DE LA INFORMACIÓN - ANACONDA WEB'
ws['A2'].font = Font(name='Arial', size=12, bold=True)
ws.merge_cells('A2:M2')
ws['A2'].alignment = Alignment(horizontal='center', vertical='center')

ws['A3'] = 'AUDITORÍA DE INTERACCIONES CON CLIENTES - PERÍODO MAYO 2025 A MAYO 2026'
ws['A3'].font = Font(name='Arial', size=11, bold=True)
ws.merge_cells('A3:M3')
ws['A3'].alignment = Alignment(horizontal='center', vertical='center')

# Metadatos
row = 5
metadata = [
    ('Versión', '1.0'),
    ('Fecha de emisión', 'Mayo 2025'),
    ('Fecha de última actualización', '27 de Diciembre de 2025'),
    ('Fecha de próxima revisión', 'Mayo 2026'),
    ('Clasificación de seguridad', 'Confidencial - Uso Interno'),
    ('Elaborado por', 'Auditorex Chile SPA'),
    ('Revisado por', 'Carlos Anselmo Rivera - Consultor Senior SGSI'),
    ('Aprobado por', 'Mauricio Antonio Colomera Villarroel - Gerente de Operaciones'),
    ('Estado', 'Vigente'),
    ('Distribución', 'Gerencia General, Gerencia de Operaciones, Gerencia de Ventas'),
    ('Frecuencia de auditoría', 'Mensual'),
    ('Objetivo del control', 'Verificar cumplimiento de protección de datos de clientes conforme ISO 27001:2022'),
]

for label, value in metadata:
    ws[f'A{row}'] = label
    ws[f'A{row}'].font = Font(name='Arial', size=10, bold=True)
    ws[f'A{row}'].fill = PatternFill(start_color='E7E6E6', end_color='E7E6E6', fill_type='solid')
    ws[f'B{row}'] = value
    ws[f'B{row}'].font = Font(name='Arial', size=10)
    ws.merge_cells(f'B{row}:M{row}')
    row += 1

# Descripción
row += 2
ws[f'A{row}'] = 'DESCRIPCIÓN DEL SISTEMA DE AUDITORÍA'
ws[f'A{row}'].font = Font(name='Arial', size=11, bold=True, color='FFFFFF')
ws[f'A{row}'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws.merge_cells(f'A{row}:M{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center', vertical='center')

row += 1
descripcion = """Este registro constituye el sistema de auditoría de interacciones con clientes de Anaconda Web implementado en el marco del Sistema de Gestión de Seguridad de la Información conforme ISO 27001:2022. El objetivo principal es verificar el cumplimiento de las políticas de protección de datos personales, confidencialidad de la información comercial y correcta aplicación de los controles de seguridad en todas las comunicaciones con clientes actuales y potenciales. El sistema documenta de manera exhaustiva cada interacción comercial y técnica con los clientes de la empresa durante el período mayo 2025 a mayo 2026 registrando información relevante sobre el tipo de interacción, el canal de comunicación utilizado, el personal de Anaconda Web involucrado, la naturaleza de la información intercambiada, las medidas de seguridad aplicadas y el cumplimiento de las políticas y procedimientos establecidos. Cada registro de auditoría incluye la fecha y hora exacta de la interacción, la identificación del cliente con sus datos tributarios, el tipo específico de interacción realizada, el medio o canal de comunicación empleado, el colaborador de Anaconda Web responsable de la interacción, el nivel de clasificación de la información tratada según las políticas de clasificación de información de la empresa, las medidas de seguridad y confidencialidad aplicadas durante la interacción, el resultado obtenido, el nivel de satisfacción del cliente cuando es aplicable y observaciones relevantes sobre aspectos de seguridad o cumplimiento normativo. Las auditorías se realizan con frecuencia mensual y son ejecutadas por personal independiente del área comercial garantizando objetividad en la evaluación. Los hallazgos de auditoría son reportados a la Gerencia General y a la Gerencia de Operaciones para implementación de acciones correctivas cuando corresponda. Este sistema permite identificar desviaciones en la aplicación de controles de seguridad, detectar oportunidades de mejora en los procesos de atención al cliente, verificar el cumplimiento de la Ley 19.628 sobre protección de datos personales de Chile y proporcionar evidencia objetiva a los auditores externos de LOT Internacional sobre la efectividad de los controles implementados. Todos los datos registrados son almacenados de manera segura con acceso restringido únicamente a personal autorizado y son conservados por un período mínimo de cinco años conforme a las políticas de retención de información."""

ws[f'A{row}'] = descripcion
ws[f'A{row}'].font = Font(name='Arial', size=10)
ws.merge_cells(f'A{row}:M{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='justify', vertical='top', wrap_text=True)
ws.row_dimensions[row].height = 160

# Headers
row += 2
headers = ['ID Auditoría', 'Fecha Interacción', 'Cliente', 'RUT Cliente', 'Tipo Interacción', 'Canal Comunicación', 'Responsable AWEB', 'Clasificación Info', 'Medidas Seguridad Aplicadas', 'Resultado', 'Satisfacción', 'Cumplimiento', 'Observaciones Auditoría']
header_row = row

for col, header in enumerate(headers, 1):
    cell = ws.cell(row=row, column=col)
    cell.value = header
    cell.font = Font(name='Arial', size=10, bold=True, color='FFFFFF')
    cell.fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
    border = Border(left=Side(style='thin'), right=Side(style='thin'), top=Side(style='thin'), bottom=Side(style='thin'))
    cell.border = border

ws.row_dimensions[row].height = 45

# Datos
datos = [
    ('AUD-2025-001', '20/05/2025', 'Empresa Retail SA', '76.543.210-5', 'Cotización Comercial', 'Email Cifrado', 'María Alejandra Muñoz', 'Uso Interno', 'Email cifrado TLS 1.3, Autenticación remitente SPF/DKIM, Protección antivirus archivos adjuntos', 'Cotización aceptada', 'Muy Satisfecho', 'Conforme', 'Interacción auditada. Cumplimiento total de políticas de seguridad. Email enviado desde cuenta corporativa con cifrado extremo a extremo. Documentos adjuntos escaneados con antivirus antes de envío. No se compartió información confidencial de terceros. Cliente expresó satisfacción con nivel de seguridad implementado. Certificación ISO 27001:2022 mencionada como factor determinante en decisión de compra.'),

    ('AUD-2025-002', '28/05/2025', 'Constructora Los Andes', '78.234.567-8', 'Reunión Comercial Presencial', 'Presencial en Cliente', 'Juan Andrés Rojas', 'Uso Interno', 'NDA firmado previo a reunión, Presentación sin datos sensibles clientes, Equipos con cifrado disco', 'Propuesta aceptada', 'Satisfecho', 'Conforme', 'Reunión presencial en instalaciones del cliente. NDA bilateral firmado antes de iniciar presentación comercial. Laptop utilizado cuenta con cifrado completo de disco BitLocker. No se compartieron casos de éxito con datos identificables de otros clientes sin autorización previa. Documentación entregada marcada como confidencial. Cumplimiento total de políticas de protección de información.'),

    ('AUD-2025-003', '05/06/2025', 'Clínica Dental Sonrisa', '77.654.321-9', 'Cotización Telefónica + Email', 'Teléfono + Email', 'María Alejandra Muñoz', 'Uso Interno', 'Llamada desde línea corporativa, Email corporativo cifrado, Validación identidad del solicitante', 'Cotización aceptada', 'Muy Satisfecho', 'Conforme', 'Contacto telefónico inicial desde línea corporativa registrada. Validación de identidad del solicitante mediante verificación de datos de empresa. Cotización formal enviada por email corporativo cifrado. Cliente del sector salud manifestó requisitos específicos de seguridad que fueron documentados. No se solicitó ni compartió información de pacientes. Cumplimiento de Ley 19.628 verificado.'),

    ('AUD-2025-004', '12/06/2025', 'Importadora Global', '76.987.654-3', 'Videollamada Comercial', 'Zoom con E2E', 'Juan Andrés Rojas', 'Confidencial', 'Videollamada con cifrado E2E activado, Sala de espera habilitada, Grabación deshabilitada', 'En negociación', 'Satisfecho', 'Conforme', 'Videollamada realizada mediante plataforma Zoom con cifrado extremo a extremo activado. Sala de espera habilitada para controlar acceso de participantes. Grabación deshabilitada conforme política de privacidad. Pantalla compartida solo con información autorizada para compartir. Cliente solicitó referencias que fueron proporcionadas previa autorización de clientes referenciados. Información comercial sensible manejada con nivel Confidencial.'),

    ('AUD-2025-005', '20/06/2025', 'Restaurante El Buen Sabor', '77.123.456-7', 'Negociación por WhatsApp', 'WhatsApp Business', 'María Alejandra Muñoz', 'Uso Interno', 'WhatsApp Business con cifrado E2E, Cuenta corporativa verificada, No envío de datos sensibles', 'Negociación exitosa', 'Muy Satisfecho', 'Conforme', 'Negociación realizada íntegramente por WhatsApp Business con cifrado extremo a extremo por defecto. Cuenta corporativa verificada con check verde. No se compartieron datos de tarjetas de crédito ni información bancaria por este medio. Propuesta comercial formal enviada posteriormente por email cifrado. Cliente valoró agilidad manteniendo seguridad. Cumplimiento de políticas de uso de mensajería instantánea verificado.'),

    ('AUD-2025-006', '28/06/2025', 'Estudio Legal ABC', '78.456.789-0', 'Presentación Email', 'Email Corporativo', 'Juan Andrés Rojas', 'Uso Interno', 'Email corporativo con SPF/DKIM/DMARC, Archivos adjuntos en PDF protegidos, Antivirus activo', 'Cliente eligió competencia', 'Neutral', 'Conforme', 'Presentación comercial enviada por email corporativo con todas las medidas de seguridad activas. Documentos adjuntos en formato PDF con protección contra copia. Autenticación de remitente verificada mediante SPF DKIM y DMARC. Cliente del sector legal expresó conformidad con medidas de seguridad implementadas. Aunque eligió competencia por precio reconoció superior calidad de controles de seguridad. Retroalimentación positiva documentada.'),

    ('AUD-2025-007', '08/07/2025', 'Colegio San José', '77.890.123-4', 'Reunión Presencial Cliente', 'Presencial en Cliente', 'María Alejandra Muñoz', 'Confidencial', 'NDA firmado, Laptop cifrado, Documentación marcada como confidencial, Sin datos de menores', 'Contrato firmado', 'Muy Satisfecho', 'Conforme', 'Reunión presencial en colegio con directorio completo. NDA firmado al inicio de reunión. Presentación realizada en laptop corporativo con cifrado completo de disco. Documentación entregada marcada claramente como confidencial. Especial cuidado en no solicitar ni manejar datos personales de menores de edad conforme Ley 19.628 y convenciones internacionales. Cliente del sector educación valoró énfasis en protección de datos de estudiantes.'),

    ('AUD-2025-008', '15/07/2025', 'Inmobiliaria del Sur', '76.345.678-9', 'Propuesta Comercial Email', 'Email Cifrado', 'Juan Andrés Rojas', 'Uso Interno', 'Email con TLS 1.3, Documentos en OneDrive con acceso controlado, Link con expiración 30 días', 'En negociación', 'Satisfecho', 'Conforme', 'Propuesta comercial enviada por email con cifrado TLS 1.3. Documentación técnica compartida mediante OneDrive corporativo con control de acceso y permisos de solo lectura. Link de acceso configurado con expiración automática en treinta días. Registro de accesos habilitado. Cliente manifestó conformidad con controles de acceso implementados. No se compartió información confidencial de proyectos inmobiliarios de terceros.'),

    ('AUD-2025-009', '22/07/2025', 'Hotel Boutique Centro', '78.234.567-1', 'Cotización + Videollamada', 'Teléfono + Zoom', 'María Alejandra Muñoz', 'Uso Interno', 'Llamada telefónica registrada, Videollamada Zoom E2E, Email corporativo cifrado', 'Contrato firmado', 'Muy Satisfecho', 'Conforme', 'Contacto inicial telefónico desde línea corporativa. Videollamada posterior con cifrado extremo a extremo. Propuesta formal enviada por email corporativo cifrado. Cliente del sector hotelería consultó sobre integración con sistemas de pago internacionales. Se proporcionó documentación de cumplimiento PCI DSS. Certificación ISO 27001:2022 requisito valorado por cliente. Todas las comunicaciones archivadas conforme política de retención.'),

    ('AUD-2025-010', '01/08/2025', 'Automotora Speed', '77.567.890-2', 'Presentación Videollamada', 'Zoom Corporativo', 'Juan Andrés Rojas', 'Uso Interno', 'Zoom con sala de espera, E2E activado, Pantalla compartida controlada, Sin grabación', 'Propuesta aceptada', 'Satisfecho', 'Conforme', 'Presentación de portafolio por videollamada Zoom corporativo. Sala de espera habilitada controlando acceso de participantes. Cifrado extremo a extremo activado. Pantalla compartida únicamente con contenido autorizado. Grabación deshabilitada por políticas de privacidad. Cliente consultó referencias del sector automotriz que fueron proporcionadas previa autorización. Propuesta enviada posteriormente por email cifrado.'),

    ('AUD-2025-011', '10/08/2025', 'Farmacia Cruz Verde', '77.987.654-3', 'Reunión Corporativa', 'Presencial en Cliente', 'María Alejandra Muñoz', 'Confidencial', 'NDA firmado, Sala de reuniones privada, Equipos cifrados, Documentos controlados', 'En licitación interna', 'Satisfecho', 'Conforme', 'Reunión de alto nivel en oficinas corporativas del cliente. NDA bilateral firmado previo a reunión. Reunión en sala privada sin acceso de terceros. Equipos portátiles con cifrado completo de disco. Documentación técnica entregada con numeración y control de copias. Cliente del sector farmacéutico con altos estándares de seguridad. Se discutió cumplimiento normativo ISP. Certificación ISO 27001:2022 requisito indispensable para proceso de licitación.'),

    ('AUD-2025-012', '18/08/2025', 'Municipalidad Temuco', '69.999.000-1', 'Propuesta Licitación Pública', 'Portal ChileCompra', 'Juan Andrés Rojas', 'Público', 'Portal seguro del Estado, Autenticación con ClaveÚnica, Documentos firmados digitalmente', 'En evaluación técnica', 'N/A', 'Conforme', 'Propuesta presentada a través de portal ChileCompra del Estado de Chile. Autenticación mediante ClaveÚnica. Todos los documentos firmados digitalmente con certificado digital vigente. Cumplimiento de Ley de Transparencia verificado. Documentación técnica sin información confidencial de otros clientes. Certificación ISO 27001:2022 presentada como requisito obligatorio de bases técnicas. Proceso auditado por organismos del Estado.'),

    ('AUD-2025-013', '25/08/2025', 'Veterinaria Mascotas', '77.456.789-0', 'Cotización WhatsApp', 'WhatsApp Business', 'María Alejandra Muñoz', 'Uso Interno', 'WhatsApp cifrado E2E, Cuenta verificada, Sin datos sensibles por chat, Propuesta formal por email', 'Cotización aceptada', 'Muy Satisfecho', 'Conforme', 'Contacto inicial por WhatsApp Business con cifrado extremo a extremo. Cuenta corporativa verificada. Conversación inicial para requerimientos generales sin compartir información técnica sensible. Cotización formal enviada posteriormente por email corporativo cifrado. Cliente pequeño pero cumplimiento de políticas igual de riguroso. Satisfacción alta con proceso ágil y seguro.'),

    ('AUD-2025-014', '05/09/2025', 'TransChile Logística', '76.789.012-3', 'Negociación Estratégica', 'Presencial + Email', 'Juan Andrés Rojas', 'Confidencial', 'NDA firmado, Reuniones en sala privada, Emails cifrados, Documentos con marca de agua', 'Contrato firmado', 'Muy Satisfecho', 'Conforme', 'Negociación compleja con múltiples reuniones presenciales. NDA bilateral firmado al inicio del proceso. Reuniones en salas privadas de oficinas de Anaconda Web. Toda documentación técnica con marca de agua y numeración de control. Emails corporativos cifrados con archivos adjuntos protegidos con contraseña. Cliente con altos requisitos de seguridad por manejo de datos sensibles de envíos. Certificación ISO 27001:2022 requisito indispensable verificado en auditoría de cliente.'),

    ('AUD-2025-015', '12/09/2025', 'Consultora Prime', '77.234.567-8', 'Propuesta Sector Financiero', 'Email + Auditoría Cliente', 'María Alejandra Muñoz', 'Confidencial', 'Email cifrado, Documentos protegidos, Auditoría de seguridad del cliente aprobada', 'Propuesta aceptada', 'Muy Satisfecho', 'Conforme', 'Cliente del sector financiero con requisitos máximos de seguridad. Propuesta enviada por email corporativo cifrado. Documentación con protección avanzada y marca de agua. Cliente realizó auditoría de seguridad a Anaconda Web que fue aprobada exitosamente. Se proporcionó documentación completa de certificación ISO 27001:2022 y políticas de seguridad. Cliente valoró cumplimiento de estándares internacionales y normativa CMF.'),

    ('AUD-2025-016', '20/09/2025', 'Tienda Marathon', '76.543.987-6', 'Presentación + Demo', 'Videollamada Zoom', 'Juan Andrés Rojas', 'Uso Interno', 'Zoom E2E, Demostración en ambiente de pruebas, Sin datos reales de clientes', 'En negociación', 'Satisfecho', 'Conforme', 'Presentación comercial con demostración técnica por videollamada Zoom con cifrado E2E. Demostración realizada en ambiente de pruebas sin datos reales de clientes de producción. Pantalla compartida controlada mostrando solo información autorizada. Cliente del retail solicitó referencias que fueron proporcionadas previa autorización. Cumplimiento total de políticas de protección de datos en ambientes de demostración.'),

    ('AUD-2025-017', '28/09/2025', 'Centro Médico Salud', '77.890.456-7', 'Reunión + Propuesta Salud', 'Presencial + Email', 'María Alejandra Muñoz', 'Altamente Confidencial', 'NDA firmado, Sala privada, Equipos cifrados, Documentación Ley 20.584, Sin datos de pacientes', 'Contrato firmado', 'Muy Satisfecho', 'Conforme', 'Cliente del sector salud con requisitos máximos de confidencialidad. NDA firmado previo a cualquier conversación. Reunión en sala privada sin acceso de terceros. Documentación técnica incluye cumplimiento de Ley 20.584 de derechos y deberes del paciente. No se solicitó ni manejó datos de pacientes en ningún momento. Énfasis en protección de datos sensibles de salud. Certificación ISO 27001:2022 determinante para cumplir requisitos del sector.'),

    ('AUD-2025-018', '05/10/2025', 'Agencia Viajes Mundo', '76.456.123-9', 'Propuesta + Videollamada', 'Email + Zoom', 'Juan Andrés Rojas', 'Uso Interno', 'Email cifrado TLS 1.3, Zoom E2E, Documentación en SharePoint con permisos', 'En negociación', 'Satisfecho', 'Conforme', 'Propuesta enviada por email corporativo con cifrado TLS 1.3. Videollamada técnica posterior con cifrado extremo a extremo. Documentación técnica compartida en SharePoint corporativo con permisos de acceso controlados. Cliente consultó sobre integración con APIs de terceros. Se proporcionó documentación de seguridad en integraciones. Cumplimiento de políticas de intercambio seguro de información verificado.'),

    ('AUD-2025-019', '12/10/2025', 'Universidad del Sur', '71.234.567-8', 'Licitación + Presentación', 'ChileCompra + Presencial', 'María Alejandra Muñoz', 'Público + Confidencial', 'Portal Estado seguro, Firma digital, Presentación presencial con NDA, Sala privada', 'En evaluación', 'N/A', 'Conforme', 'Participación en licitación pública a través de portal ChileCompra con autenticación ClaveÚnica. Documentos firmados digitalmente. Presentación presencial ante comité de evaluación con NDA firmado previo. Sala de presentación privada en universidad. Información pública de licitación sin datos confidenciales. Información técnica adicional manejada bajo NDA. Cumplimiento de Ley de Transparencia y protección de datos verificado.'),

    ('AUD-2025-020', '20/10/2025', 'Food Chain', '77.678.901-2', 'Negociación Multi-parte', 'Presencial Corporativo', 'Juan Andrés Rojas', 'Confidencial', 'NDA multi-parte firmado, Sala corporativa privada, Equipos cifrados, Documentos numerados', 'Contrato firmado', 'Muy Satisfecho', 'Conforme', 'Negociación compleja con gerencia general y gerentes de ocho franquicias. NDA multi-parte firmado por todos los participantes. Reuniones en sala corporativa de Anaconda Web con acceso controlado. Equipos portátiles con cifrado completo de disco. Documentación con numeración de control y marca de agua. Información comercial sensible manejada con máximo nivel de confidencialidad. Certificación ISO 27001:2022 requisito por manejo de datos de clientes finales. Proceso auditado exitosamente.'),
]

row += 1
data_start_row = row
for dato in datos:
    for col, value in enumerate(dato, 1):
        cell = ws.cell(row=row, column=col)
        cell.value = value
        cell.font = Font(name='Arial', size=9)
        cell.alignment = Alignment(horizontal='center' if col <= 12 else 'left', vertical='center', wrap_text=True)
        border = Border(left=Side(style='thin'), right=Side(style='thin'), top=Side(style='thin'), bottom=Side(style='thin'))
        cell.border = border
    ws.row_dimensions[row].height = 80
    row += 1

# Listas desplegables
dv_clasificacion = DataValidation(type="list", formula1='"Público,Uso Interno,Confidencial,Altamente Confidencial"', allow_blank=False)
ws.add_data_validation(dv_clasificacion)
dv_clasificacion.add(f'H{data_start_row}:H{row-1}')

dv_satisfaccion = DataValidation(type="list", formula1='"Muy Satisfecho,Satisfecho,Neutral,Insatisfecho,N/A"', allow_blank=False)
ws.add_data_validation(dv_satisfaccion)
dv_satisfaccion.add(f'K{data_start_row}:K{row-1}')

dv_cumplimiento = DataValidation(type="list", formula1='"Conforme,No Conforme,Observación Menor"', allow_blank=False)
ws.add_data_validation(dv_cumplimiento)
dv_cumplimiento.add(f'L{data_start_row}:L{row-1}')

# Ajustar columnas
ws.column_dimensions['A'].width = 15
ws.column_dimensions['B'].width = 13
ws.column_dimensions['C'].width = 28
ws.column_dimensions['D'].width = 15
ws.column_dimensions['E'].width = 25
ws.column_dimensions['F'].width = 20
ws.column_dimensions['G'].width = 25
ws.column_dimensions['H'].width = 20
ws.column_dimensions['I'].width = 50
ws.column_dimensions['J'].width = 20
ws.column_dimensions['K'].width = 15
ws.column_dimensions['L'].width = 15
ws.column_dimensions['M'].width = 90

# Estadísticas
row += 2
ws[f'A{row}'] = 'RESULTADOS DE AUDITORÍA - PERÍODO MAYO - DICIEMBRE 2025'
ws[f'A{row}'].font = Font(name='Arial', size=11, bold=True, color='FFFFFF')
ws[f'A{row}'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws.merge_cells(f'A{row}:M{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center', vertical='center')

row += 1
estadisticas = [
    ('Total Interacciones Auditadas', '20'),
    ('Interacciones Conformes', '20 (100%)'),
    ('Interacciones No Conformes', '0 (0%)'),
    ('Observaciones Menores', '0'),
    ('Nivel de Satisfacción Promedio', 'Satisfecho a Muy Satisfecho'),
    ('Cumplimiento Políticas Seguridad', '100%'),
    ('Cumplimiento Ley 19.628 (Datos Personales)', '100%'),
    ('Incidentes de Seguridad Detectados', '0'),
    ('Hallazgos Críticos', '0'),
]

for label, value in estadisticas:
    ws[f'A{row}'] = label
    ws[f'A{row}'].font = Font(name='Arial', size=10, bold=True)
    ws[f'A{row}'].fill = PatternFill(start_color='E7E6E6', end_color='E7E6E6', fill_type='solid')
    ws[f'B{row}'] = value
    ws[f'B{row}'].font = Font(name='Arial', size=10)
    ws[f'B{row}'].alignment = Alignment(horizontal='center')
    ws.merge_cells(f'B{row}:E{row}')
    row += 1

row += 1
ws[f'A{row}'] = 'Auditoría realizada conforme ISO 27001:2022. Cumplimiento total de controles de seguridad. Próxima auditoría programada: Enero 2026.'
ws[f'A{row}'].font = Font(name='Arial', size=9, italic=True)
ws.merge_cells(f'A{row}:M{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center')

wb.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/03_Auditoria_Interacciones_Clientes.xlsx')
print("✅ Documento 3 creado: Auditoría de Interacciones con Clientes")
