#!/usr/bin/env python3
# -*- coding: utf-8 -*-
from openpyxl import Workbook
from openpyxl.styles import Font, Alignment, PatternFill
from openpyxl.worksheet.datavalidation import DataValidation

def crear_portada(ws, titulo_doc, codigo_doc, descripcion_breve):
    """Crea portada profesional estándar"""
    ws['A1'] = 'ANACONDA WEB CHILE'
    ws['A1'].font = Font(name='Calibri', size=36, bold=True, color='1F4E78')
    ws.merge_cells('A1:J1')
    ws['A1'].alignment = Alignment(horizontal='center', vertical='center')
    ws.row_dimensions[1].height = 60
    
    ws['A2'] = 'RUT: 77.520.290-4'
    ws['A2'].font = Font(name='Calibri', size=14, bold=True, color='1F4E78')
    ws.merge_cells('A2:J2')
    ws['A2'].alignment = Alignment(horizontal='center', vertical='center')
    
    ws['A4'] = 'SISTEMA DE GESTIÓN DE SEGURIDAD DE LA INFORMACIÓN'
    ws['A4'].font = Font(name='Calibri', size=18, bold=True, color='FFFFFF')
    ws['A4'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
    ws.merge_cells('A4:J4')
    ws['A4'].alignment = Alignment(horizontal='center', vertical='center')
    ws.row_dimensions[4].height = 35
    
    ws['A5'] = 'ISO/IEC 27001:2022'
    ws['A5'].font = Font(name='Calibri', size=16, bold=True, color='FFFFFF')
    ws['A5'].fill = PatternFill(start_color='366092', end_color='366092', fill_type='solid')
    ws.merge_cells('A5:J5')
    ws['A5'].alignment = Alignment(horizontal='center', vertical='center')
    
    ws['A8'] = titulo_doc
    ws['A8'].font = Font(name='Calibri', size=20, bold=True, color='1F4E78')
    ws.merge_cells('A8:J8')
    ws['A8'].alignment = Alignment(horizontal='center', vertical='center')
    ws.row_dimensions[8].height = 35
    
    ws['A9'] = descripcion_breve
    ws['A9'].font = Font(name='Calibri', size=14, bold=True, color='1F4E78')
    ws.merge_cells('A9:J9')
    ws['A9'].alignment = Alignment(horizontal='center', vertical='center')
    ws.row_dimensions[9].height = 30
    
    # Info documento
    row = 11
    ws[f'C{row}'] = 'Código Documental:'
    ws[f'E{row}'] = codigo_doc
    ws[f'C{row}'].font = Font(name='Calibri', size=11, bold=True)
    ws[f'E{row}'].font = Font(name='Calibri', size=11)
    ws.merge_cells(f'C{row}:D{row}')
    ws.merge_cells(f'E{row}:H{row}')
    
    row += 6
    ws[f'A{row}'] = 'PERÍODO DE REPORTE'
    ws[f'A{row}'].font = Font(name='Calibri', size=12, bold=True, color='FFFFFF')
    ws[f'A{row}'].fill = PatternFill(start_color='366092', end_color='366092', fill_type='solid')
    ws.merge_cells(f'A{row}:J{row}')
    ws[f'A{row}'].alignment = Alignment(horizontal='center')
    row += 1
    ws[f'A{row}'] = 'Mayo 2025 - Mayo 2026'
    ws[f'A{row}'].font = Font(name='Calibri', size=14, bold=True, color='1F4E78')
    ws.merge_cells(f'A{row}:J{row}')
    ws[f'A{row}'].alignment = Alignment(horizontal='center')

print("Creando Documento 2: Trazabilidad de Solicitudes de Ventas...")
wb2 = Workbook()
ws2 = wb2.active
ws2.title = "Portada"
crear_portada(ws2, "TRAZABILIDAD DE SOLICITUDES", "AWEB-SGSI-REG-002-V1.0", "COMERCIALES Y GESTIÓN DE SLA")
wb2.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/02_Trazabilidad_Solicitudes_Ventas.xlsx')
print("✅ Documento 2 creado con portada profesional")

print("Creando Documento 3: Auditoría de Interacciones con Clientes...")
wb3 = Workbook()
ws3 = wb3.active
ws3.title = "Portada"
crear_portada(ws3, "AUDITORÍA DE INTERACCIONES", "AWEB-SGSI-REG-003-V1.0", "CON CLIENTES Y CUMPLIMIENTO NORMATIVO")
wb3.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/03_Auditoria_Interacciones_Clientes.xlsx')
print("✅ Documento 3 creado con portada profesional")

print("Creando Documento 4: Registros de Copias de Seguridad...")
wb4 = Workbook()
ws4 = wb4.active
ws4.title = "Portada"
crear_portada(ws4, "REGISTROS DE COPIAS DE SEGURIDAD", "AWEB-SGSI-REG-004-V1.0", "PERIÓDICAS DEL DATACENTER")
wb4.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/04_Registros_Copias_Seguridad.xlsx')
print("✅ Documento 4 creado con portada profesional")

print("Creando Documento 5: Pruebas de Recuperación ante Desastres...")
wb5 = Workbook()
ws5 = wb5.active
ws5.title = "Portada"
crear_portada(ws5, "PRUEBAS DE RECUPERACIÓN", "AWEB-SGSI-REG-005-V1.0", "ANTE DESASTRES - RTO Y RPO")
wb5.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/05_Pruebas_Recuperacion_Desastres.xlsx')
print("✅ Documento 5 creado con portada profesional")

print("\n" + "="*60)
print("✅ LOS 5 DOCUMENTOS EXCEL CREADOS CON PORTADA PROFESIONAL")
print("="*60)
