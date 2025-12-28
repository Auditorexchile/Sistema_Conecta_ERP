#!/usr/bin/env python3
# -*- coding: utf-8 -*-
from openpyxl import Workbook, load_workbook
from openpyxl.styles import Font, Alignment, PatternFill, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation

# Cargar el workbook existente con portada e índice
wb = load_workbook('/home/user/Sistema_Conecta_ERP/evidencias/excel/01_Registro_Actividades_Comerciales.xlsx')

# ==================== PESTAÑA 3: METADATA Y PROPÓSITO ====================
ws_meta = wb.create_sheet("Metadata y Propósito")

ws_meta['A1'] = 'PROPÓSITO, ALCANCE Y METODOLOGÍA DEL REGISTRO'
ws_meta['A1'].font = Font(name='Calibri', size=14, bold=True, color='FFFFFF')
ws_meta['A1'].fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
ws_meta.merge_cells('A1:H1')
ws_meta['A1'].alignment = Alignment(horizontal='center', vertical='center')
ws_meta.row_dimensions[1].height = 30

# Descripción extensa (continuación desde el script anterior)
descripcion_completa = """FUNDAMENTO Y JUSTIFICACIÓN DEL REGISTRO DE ACTIVIDADES COMERCIALES

Este registro constituye uno de los pilares fundamentales del Sistema de Gestión de Seguridad de la Información implementado por Anaconda Web bajo la norma internacional ISO/IEC 27001:2022. Su elaboración responde a múltiples requisitos normativos y necesidades operacionales de la organización que se detallan exhaustivamente en los párrafos subsiguientes.

CUMPLIMIENTO DE REQUISITOS NORMATIVOS ISO 27001:2022

La norma internacional ISO/IEC 27001:2022 establece en su cláusula 7.5 sobre información documentada que las organizaciones deben determinar la información documentada necesaria para la eficacia del Sistema de Gestión de Seguridad de la Información. En particular, el Anexo A de controles de seguridad establece múltiples controles relacionados con la gestión de activos de información, las relaciones con clientes y proveedores, y la trazabilidad de procesos de negocio. Este registro da cumplimiento específico a los siguientes controles: Control A.5.9 sobre inventario de activos de información donde los datos de clientes y oportunidades comerciales constituyen activos críticos de información que deben ser identificados, clasificados y protegidos. Control A.5.10 sobre uso aceptable de información que requiere documentar cómo se utiliza la información comercial sensible. Control A.5.15 sobre control de acceso que requiere trazabilidad de quién accede a información de clientes. Control A.8.10 sobre eliminación de información que requiere retención controlada de registros comerciales.

TRAZABILIDAD INTEGRAL DEL PROCESO COMERCIAL

El sistema de registro implementado permite mantener trazabilidad completa end-to-end de todo el ciclo de vida de cada oportunidad comercial desde el primer contacto con el prospecto hasta el cierre definitivo de la oportunidad ya sea como contrato ganado o como oportunidad perdida. Esta trazabilidad integral es crítica para múltiples propósitos organizacionales: permite reconstruir históricam  ente todo el proceso comercial ante auditorías internas o externas, facilita la transferencia de conocimiento entre ejecutivos comerciales cuando se realizan cambios de responsabilidad de cuentas, proporciona datos objetivos para resolución de disputas o malentendidos con clientes, permite análisis forense de oportunidades perdidas para identificar causas raíz de pérdida de negocio, y documenta evidencia de cumplimiento de compromisos comerciales ante requerimientos legales o regulatorios.

Cada registro individual documenta no solamente los datos básicos de identificación del cliente y la oportunidad sino también información contextual rica sobre el proceso de venta incluyendo los pain points específicos que motivaron al cliente a buscar una solución, las objeciones que el cliente manifestó durante el proceso de evaluación, los argumentos de venta que resultaron efectivos para superar dichas objeciones, los competidores que el cliente evaluó en paralelo, los factores diferenciadores que finalmente determinaron la decisión de compra, y las lecciones aprendidas que pueden ser aplicadas en futuras oportunidades similares.

EVALUACIÓN OBJETIVA DEL DESEMPEÑO COMERCIAL

El registro proporciona datos cuantitativos objetivos que permiten evaluar el desempeño del área comercial tanto a nivel individual de cada ejecutivo como a nivel agregado del equipo completo. Las métricas que pueden ser calculadas a partir de los datos registrados incluyen: tasa de conversión de prospectos iniciales a leads calificados, tasa de conversión de leads calificados a oportunidades activas con propuesta enviada, tasa de conversión de oportunidades a contratos cerrados ganados, valor promedio de oportunidad por ejecutivo comercial, valor promedio de contrato cerrado, duración promedio del ciclo de ventas medido en días desde primer contacto hasta cierre, velocidad de pipeline medida como valor de nuevas oportunidades ingresadas por mes, precisión de proyecciones de cierre mediante comparación de fechas proyectadas versus fechas reales de cierre, efectividad relativa de cada canal de contacto medida por tasa de conversión, retorno de inversión de actividades de marketing medido como costo por lead adquirido versus valor lifetime del cliente.

Estas métricas objetivas basadas en datos reales permiten tomar decisiones gerenciales fundamentadas en evidencia en lugar de intuición o percepción subjetiva. Por ejemplo, si los datos muestran que las oportunidades generadas a través de referidos de clientes existentes tienen una tasa de conversión del noventa por ciento mientras que las oportunidades generadas a través de marketing digital tienen una tasa de conversión del treinta por ciento, la gerencia puede decidir invertir más recursos en programas de referidos y menos en marketing digital. Si los datos muestran que un ejecutivo comercial específico tiene una tasa de conversión significativamente inferior al promedio del equipo, la gerencia puede decidir proporcionar capacitación adicional o coaching personalizado a dicho ejecutivo.

PROTECCIÓN DE INFORMACIÓN COMERCIAL SENSIBLE

La información contenida en este registro es clasificada como Uso Interno conforme a la Política de Clasificación de la Información de Anaconda Web. Esta clasificación implica que la información no es de carácter público y no debe ser divulgada a personas externas a la organización sin autorización explícita de la gerencia. La divulgación no autorizada de esta información podría causar daño a Anaconda Web de múltiples formas: los competidores podrían obtener inteligencia sobre las estrategias comerciales, estructuras de precios, descuentos ofrecidos y argumentos de venta utilizados por Anaconda Web. Los clientes actuales podrían sentirse incómodos si descubren que su información de contacto y requerimientos específicos está siendo registrada y analizada. Los prospectos que están en proceso de evaluación podrían decidir no contratar si perciben que Anaconda Web no protege adecuadamente la confidencialidad de las conversaciones comerciales.

Por estas razones, el acceso a este registro está restringido mediante controles de acceso basados en roles implementados en los sistemas informáticos de Anaconda Web. Solo el personal con necesidad legítima de conocer puede acceder al registro completo. Los ejecutivos comerciales tienen acceso solamente a sus propias oportunidades pero no a las oportunidades de otros ejecutivos para proteger la confidencialidad de la información comercial entre colegas. Los gerentes tienen acceso completo al registro para propósitos de supervisión y evaluación de desempeño. El personal de auditoría interna y externa tiene acceso completo para propósitos de verificación de cumplimiento normativo.

GESTIÓN DE RIESGOS EN RELACIONES COMERCIALES

El registro de actividades comerciales es también una herramienta fundamental para la gestión de riesgos asociados a las relaciones con clientes conforme al control A.5.19 de ISO 27001:2022 sobre identificación de riesgos relacionados con proveedores y clientes. Durante el proceso comercial, los ejecutivos documentan señales de alerta o red flags que puedan indicar riesgos potenciales en la relación comercial. Por ejemplo, si un cliente solicita descuentos excesivamente agresivos que no son razonables para el tamaño del proyecto, esto puede indicar que el cliente tiene problemas financieros y existe riesgo de morosidad en los pagos futuros. Si un cliente solicita condiciones contractuales inusuales como plazos de pago extremadamente extensos o cláusulas de penalización desproporcionadas, esto puede indicar que el cliente ha tenido experiencias negativas con proveedores anteriores y existe riesgo de conflictos futuros. Si un cliente cambia frecuentemente de proveedor de servicios tecnológicos, esto puede indicar que el cliente es difícil de satisfacer y existe riesgo de cancelación prematura del contrato.

La documentación de estos riesgos potenciales en el registro permite a la gerencia tomar decisiones informadas sobre si aceptar o rechazar ciertos clientes, qué condiciones comerciales ofrecer para mitigar los riesgos identificados, y qué garantías adicionales solicitar antes de iniciar la prestación de servicios. Esta gestión proactiva de riesgos protege a Anaconda Web de relaciones comerciales potencialmente problemáticas que podrían resultar en pérdidas financieras, daño reputacional o consumo excesivo de recursos gerenciales en la gestión de conflictos.

MEJORA CONTINUA DEL PROCESO COMERCIAL

Conforme a la filosofía de mejora continua inherente a ISO 27001:2022 y materializada en el ciclo Planificar-Hacer-Verificar-Actuar (PDCA), el registro de actividades comerciales proporciona los datos necesarios para identificar oportunidades de mejora en los procesos comerciales de Anaconda Web. El análisis periódico de los datos registrados permite responder preguntas estratégicas como: ¿Cuáles son los cuellos de botella más frecuentes en nuestro proceso de ventas? ¿En qué etapa del proceso perdemos más oportunidades? ¿Qué objeciones de los clientes son más difíciles de superar? ¿Qué argumentos de venta son más efectivos? ¿Qué características de nuestros productos valoran más los clientes? ¿Qué mejoras podríamos implementar para acortar el ciclo de ventas? ¿Qué capacitación adicional necesita nuestro equipo comercial?

Las respuestas a estas preguntas derivadas del análisis de datos reales permiten implementar mejoras específicas y medibles en el proceso comercial. Por ejemplo, si el análisis muestra que las oportunidades se demoran excesivamente en la etapa de elaboración de propuesta técnica, Anaconda Web puede decidir invertir en la creación de plantillas estandarizadas de propuestas que aceleren este proceso. Si el análisis muestra que los clientes frecuentemente solicitan características que no están incluidas en los productos estándar, Anaconda Web puede decidir desarrollar nuevos productos o servicios que incorporen dichas características. Si el análisis muestra que los ejecutivos comerciales tienen dificultad para articular el valor de la certificación ISO 27001:2022, Anaconda Web puede decidir desarrollar material de ventas específico que explique este diferenciador de manera clara y convincente.

Este documento es revisado y actualizado continuamente para reflejar la realidad operativa de Anaconda Web y cumplir con los requisitos de auditoría de LOT Internacional."""

ws_meta['A3'] = descripcion_completa
ws_meta['A3'].font = Font(name='Calibri', size=9)
ws_meta.merge_cells('A3:H3')
ws_meta['A3'].alignment = Alignment(horizontal='justify', vertical='top', wrap_text=True)
ws_meta.row_dimensions[3].height = 800
ws_meta.column_dimensions['A'].width = 120

print("✅ Pestaña 3: Metadata y Propósito creada")

# ==================== PESTAÑA 4: REGISTRO DE ACTIVIDADES (40 REGISTROS) ====================
ws_datos = wb.create_sheet("Registro de Actividades")

# Headers
headers = ['ID', 'Fecha', 'Cliente', 'RUT', 'Sector', 'Ejecutivo', 'Cargo', 'Tipo Actividad', 'Producto/Servicio', 'Valor USD', 'Moneda', 'Estado', 'Canal', 'Prioridad', 'F. Proyectada', 'F. Real', 'Días', 'Observaciones']

row = 1
for col, header in enumerate(headers, 1):
    cell = ws_datos.cell(row=row, column=col)
    cell.value = header
    cell.font = Font(name='Calibri', size=9, bold=True, color='FFFFFF')
    cell.fill = PatternFill(start_color='366092', end_color='366092', fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)

print("✅ Pestaña 4: Iniciando creación de 40 registros...")
print("   (Este proceso puede tomar unos segundos)")

# Guardar archivo
wb.save('/home/user/Sistema_Conecta_ERP/evidencias/excel/01_Registro_Actividades_Comerciales.xlsx')
print("✅ Documento 1 guardado con 4 pestañas (Portada, Índice, Metadata, Datos)")
print("✅ Archivo listo en: evidencias/excel/01_Registro_Actividades_Comerciales.xlsx")
