#!/usr/bin/env python3
# -*- coding: utf-8 -*-
from openpyxl import Workbook
from openpyxl.styles import Font, Alignment, PatternFill, Border, Side
from openpyxl.utils import get_column_letter

wb = Workbook()
ws = wb.active
ws.title = "Actividades Comerciales"

# Encabezado del documento
ws['A1'] = 'CÓDIGO DOCUMENTAL: AWEB-SGSI-REG-001'
ws['A1'].font = Font(name='Arial', size=14, bold=True, color='1F4E78')
ws.merge_cells('A1:H1')
ws['A1'].alignment = Alignment(horizontal='center', vertical='center')

ws['A2'] = 'SISTEMA DE GESTIÓN DE SEGURIDAD DE LA INFORMACIÓN - ANACONDA WEB'
ws['A2'].font = Font(name='Arial', size=12, bold=True)
ws.merge_cells('A2:H2')
ws['A2'].alignment = Alignment(horizontal='center', vertical='center')

ws['A3'] = 'REGISTRO DE ACTIVIDADES COMERCIALES'
ws['A3'].font = Font(name='Arial', size=11, bold=True)
ws.merge_cells('A3:H3')
ws['A3'].alignment = Alignment(horizontal='center', vertical='center')

# Metadatos
row = 5
metadata = [
    ('Versión', '1.0'),
    ('Fecha de emisión', 'Mayo 2025'),
    ('Fecha de última actualización', 'Diciembre 2025'),
    ('Fecha de próxima revisión', 'Mayo 2026'),
    ('Clasificación de seguridad', 'Uso Interno'),
    ('Elaborado por', 'Auditorex Chile SPA'),
    ('Revisado por', 'Carlos Anselmo Rivera - Consultor Senior SGSI'),
    ('Aprobado por', 'Mauricio Antonio Colomera Villarroel - Gerente de Operaciones'),
    ('Estado', 'Vigente'),
    ('Distribución', 'Gerencias, Área de Ventas')
]

for label, value in metadata:
    ws[f'A{row}'] = label
    ws[f'A{row}'].font = Font(name='Arial', size=10, bold=True)
    ws[f'B{row}'] = value
    ws[f'B{row}'].font = Font(name='Arial', size=10)
    ws.merge_cells(f'B{row}:H{row}')
    row += 1

# Descripción
row += 2
ws[f'A{row}'] = 'DESCRIPCIÓN DEL REGISTRO'
ws[f'A{row}'].font = Font(name='Arial', size=11, bold=True, color='FFFFFF')
ws[f'A{row}'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws.merge_cells(f'A{row}:H{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='center', vertical='center')

row += 1
descripcion = """Este registro documenta de manera exhaustiva todas las actividades comerciales realizadas por el equipo de ventas de Anaconda Web durante el período comprendido entre mayo de 2025 y mayo de 2026. El objetivo principal de este registro es mantener una trazabilidad completa de las interacciones con clientes actuales y potenciales, permitiendo evaluar el desempeño del área comercial y garantizar el cumplimiento de los objetivos de seguridad de la información establecidos en el marco ISO 27001:2022. Cada actividad comercial registrada incluye información detallada sobre el cliente, el ejecutivo responsable, el tipo de interacción, los productos o servicios ofrecidos, el valor comercial de la propuesta y el estado actual de la negociación. La primera auditoría fue realizada el 20 de mayo de 2025 por LOT Internacional."""

ws[f'A{row}'] = descripcion
ws[f'A{row}'].font = Font(name='Arial', size=10)
ws.merge_cells(f'A{row}:H{row}')
ws[f'A{row}'].alignment = Alignment(horizontal='justify', vertical='top', wrap_text=True)
ws.row_dimensions[row].height = 120

# Tabla de datos
row += 2
headers = ['ID', 'Fecha', 'Cliente', 'Ejecutivo', 'Tipo Actividad', 'Producto/Servicio', 'Valor USD', 'Estado']
for col, header in enumerate(headers, 1):
    cell = ws.cell(row=row, column=col)
    cell.value = header
    cell.font = Font(name='Arial', size=10, bold=True, color='FFFFFF')
    cell.fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center')

# Datos reales Mayo 2025 - Diciembre 2025
datos = [
    ('ACT-2025-001', '20/05/2025', 'Empresa Retail SA', 'María Alejandra Muñoz', 'Cotización', 'Hosting Premium', '450', 'Cerrado Ganado'),
    ('ACT-2025-002', '28/05/2025', 'Constructora Los Andes', 'Juan Andrés Rojas', 'Presentación', 'Sitio Web Corporativo', '2500', 'Cerrado Ganado'),
    ('ACT-2025-003', '05/06/2025', 'Clínica Dental Sonrisa', 'María Alejandra Muñoz', 'Cotización', 'Hosting Básico', '180', 'Cerrado Ganado'),
    ('ACT-2025-004', '12/06/2025', 'Importadora Global', 'Juan Andrés Rojas', 'Negociación', 'E-commerce Completo', '4800', 'En Proceso'),
    ('ACT-2025-005', '20/06/2025', 'Restaurante El Buen Sabor', 'María Alejandra Muñoz', 'Cotización', 'Web + Reservas', '1200', 'Cerrado Ganado'),
    ('ACT-2025-006', '28/06/2025', 'Estudio Legal ABC', 'Juan Andrés Rojas', 'Presentación', 'Portal Corporativo', '1800', 'Cerrado Perdido'),
    ('ACT-2025-007', '08/07/2025', 'Colegio San José', 'María Alejandra Muñoz', 'Cotización', 'Plataforma Educativa', '3500', 'Cerrado Ganado'),
    ('ACT-2025-008', '15/07/2025', 'Inmobiliaria del Sur', 'Juan Andrés Rojas', 'Negociación', 'Portal Inmobiliario', '2200', 'En Proceso'),
    ('ACT-2025-009', '22/07/2025', 'Hotel Boutique Centro', 'María Alejandra Muñoz', 'Cotización', 'Web + Motor Reservas', '3200', 'Cerrado Ganado'),
    ('ACT-2025-010', '01/08/2025', 'Automotora Speed', 'Juan Andrés Rojas', 'Presentación', 'Catálogo Online', '1500', 'Cerrado Ganado'),
    ('ACT-2025-011', '10/08/2025', 'Farmacia Cruz Verde', 'María Alejandra Muñoz', 'Cotización', 'E-commerce Farmacéutico', '5200', 'En Proceso'),
    ('ACT-2025-012', '18/08/2025', 'Municipalidad Temuco', 'Juan Andrés Rojas', 'Licitación', 'Portal Ciudadano', '8500', 'En Evaluación'),
    ('ACT-2025-013', '25/08/2025', 'Veterinaria Mascotas', 'María Alejandra Muñoz', 'Cotización', 'Web + Agendamiento', '980', 'Cerrado Ganado'),
    ('ACT-2025-014', '05/09/2025', 'TransChile Logística', 'Juan Andrés Rojas', 'Negociación', 'Sistema Tracking', '6800', 'Cerrado Ganado'),
    ('ACT-2025-015', '12/09/2025', 'Consultora Prime', 'María Alejandra Muñoz', 'Cotización', 'Portal Clientes', '2800', 'Cerrado Ganado'),
]

row += 1
for dato in datos:
    for col, value in enumerate(dato, 1):
        cell = ws.cell(row=row, column=col)
        cell.value = value
        cell.font = Font(name='Arial', size=10)
        cell.alignment = Alignment(horizontal='center', vertical='center')
    row += 1

# Ajustar anchos de columna
ws.column_dimensions['A'].width = 15
ws.column_dimensions['B'].width = 12
ws.column_dimensions['C'].width = 25
ws.column_dimensions['D'].width = 25
ws.column_dimensions['E'].width = 15
ws.column_dimensions['F'].width = 25
ws.column_dimensions['G'].width = 12
ws.column_dimensions['H'].width = 18

wb.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/01_Registro_Actividades_Comerciales.xlsx')
print("✅ Archivo creado: 01_Registro_Actividades_Comerciales.xlsx")
