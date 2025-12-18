# ConectaERP - Sistema de Gestión Contable Completo

## 🎯 Descripción

Sistema ERP de Contabilidad Nivel Softland para empresas chilenas. Cumple con normativa SII, NIIF y operación real de estudios contables. Incluye plan de cuentas jerárquico de 6 niveles, comprobantes contables, libros legales, IVA/F29, declaraciones juradas, cierres contables, auditoría completa y trazabilidad total.

**Versión:** 1.0.0
**Autor:** Auditorexchile
**Licencia:** Propietario
**Nivel:** Producción / Comercial

---

## ✨ Características Principales

### 1. Plan de Cuentas Jerárquico (6 Niveles)
- ✅ Estructura jerárquica de 1 a 6 niveles
- ✅ Numeración libre y flexible
- ✅ Clasificación: Activo, Pasivo, Patrimonio, Ingresos, Gastos, Orden
- ✅ Cuentas imputables y no imputables
- ✅ Naturaleza: Deudora / Acreedora
- ✅ Configuración por cuenta:
  - Centro de costo obligatorio
  - Auxiliar obligatorio (cliente/proveedor/trabajador)
  - Documento obligatorio
  - Afecta IVA / Exenta / No afecta
  - Cuenta honorarios
  - Cuenta activo fijo
  - Cuenta gasto rechazado
  - Multimoneda
  - Bloqueo por periodo
  - Vigencia temporal
- ✅ Importar/Exportar plan completo
- ✅ Ver movimientos por cuenta
- ✅ Recalcular saldos automáticamente

### 2. Comprobantes Contables (9 Tipos)
- ✅ **Ingreso:** Registros de ingresos
- ✅ **Egreso:** Registros de egresos
- ✅ **Traspaso:** Movimientos entre cuentas
- ✅ **Ajuste:** Ajustes contables
- ✅ **Apertura:** Asientos de apertura
- ✅ **Cierre:** Asientos de cierre
- ✅ **Provisión:** Provisiones contables
- ✅ **Centralización:** Centralizaciones automáticas
- ✅ **Reverso:** Reversos automáticos

**Funcionalidades:**
- Numeración automática por tipo y periodo
- Control de cuadratura (Debe = Haber)
- Estados: Borrador, Contabilizado, Anulado, Reversado
- Validación de periodo abierto
- Centro de costo por línea
- Auxiliar por línea (cliente/proveedor/trabajador)
- Documentos asociados por línea
- Glosa general y por línea
- Multimoneda con tipo de cambio
- Adjuntar documentos digitales
- Trazabilidad completa
- Sin eliminación física (soft delete)

### 3. Centros de Costo
- ✅ Estructura jerárquica
- ✅ Codificación libre
- ✅ Múltiples niveles
- ✅ Asignación por línea de comprobante
- ✅ Reportes por centro de costo

### 4. Auxiliares
- ✅ Clientes
- ✅ Proveedores
- ✅ Trabajadores
- ✅ Otros
- ✅ RUT validación chilena
- ✅ Datos completos de contacto
- ✅ Integración con plan de cuentas

### 5. Libros Contables y Legales

**Libros Contables:**
- ✅ Libro Diario
- ✅ Libro Mayor
- ✅ Balance de Comprobación (8 columnas)
- ✅ Balance General (Clasificado por cuenta)
- ✅ Estado de Resultados
- ✅ Flujo de Caja

**Libros Legales SII:**
- ✅ Libro de Compras
- ✅ Libro de Ventas
- ✅ Libro de Honorarios
- ✅ Libro de Remuneraciones (integración futura)
- ✅ Libro de Retenciones
- ✅ Libro de Activo Fijo (integración futura)

**Funcionalidades:**
- Filtros por periodo, cuenta, auxiliar, centro de costo
- Exportación PDF, Excel, CSV
- Formato oficial SII
- Vista previa en pantalla
- Generación automática

### 6. IVA y Formulario 29 (F29)

**Configuración IVA:**
- ✅ Tasas configurables
- ✅ IVA Débito Fiscal
- ✅ IVA Crédito Fiscal
- ✅ IVA Proporcional
- ✅ IVA Uso Común
- ✅ IVA No Recuperable
- ✅ Remanentes mes anterior

**Formulario 29:**
- ✅ Interfaz espejo SII
- ✅ Códigos oficiales:
  - 511: Ventas Netas
  - 512: Débito Fiscal
  - 520: Compras Netas
  - 521: Crédito Fiscal
  - 522: Crédito Uso Común
  - 524: Crédito Activo Fijo
  - 528: Remanente mes anterior
  - 538: Total Crédito
  - 562: IVA Determinado
  - 563: Créditos Especiales
  - 564: IVA Retenido Terceros
  - 566: Remanente Crédito
  - 151: Honorarios Pagados
  - 152: Retención Honorarios
  - 36: PPM
  - 91: Total a Pagar
  - 93: Total a Favor
- ✅ Cálculos automáticos
- ✅ Ajustes manuales con registro
- ✅ Vista previa
- ✅ Exportar para declaración
- ✅ Cierre mensual de IVA

### 7. Declaraciones Juradas (DJ)

Sistema completo de 13 Declaraciones Juradas:

- ✅ **DJ 1879** - Honorarios
- ✅ **DJ 1887** - Remuneraciones
- ✅ **DJ 1948** - Retenciones
- ✅ **DJ 1835** - Arriendos
- ✅ **DJ 1847** - Pagos al Extranjero
- ✅ **DJ 1812** - Rentas sin Retención
- ✅ **DJ 1822** - Donaciones
- ✅ **DJ 1874** - Ingresos por Arriendo
- ✅ **DJ 1929** - Gastos Rechazados
- ✅ **DJ 1807** - Activo Fijo
- ✅ **DJ 1873** - Inversiones
- ✅ **DJ 1811** - Socios y Accionistas
- ✅ **DJ 1840** - Retiros y Dividendos

**Funcionalidades:**
- Toma datos automáticos desde contabilidad
- Validación de totales vs contabilidad
- Trazabilidad hasta comprobante origen
- Exportación formato SII
- Vista previa
- Estados: Borrador, Validado, Presentado, Rectificado
- Registro de folio SII
- Observaciones y notas

### 8. Cierres Contables

**Cierre Mensual:**
- ✅ Validaciones de integridad
- ✅ Verificación de cuadratura
- ✅ Bloqueo de periodo
- ✅ Informe de diferencias
- ✅ Usuario responsable
- ✅ Fecha y hora de cierre
- ✅ Posibilidad de reapertura con autorización

**Cierre Anual:**
- ✅ Cálculo de resultado del ejercicio
- ✅ Asiento automático a patrimonio
- ✅ Arrastre de saldos al nuevo año
- ✅ Bloqueo definitivo de año
- ✅ Generación de apertura automática
- ✅ Validaciones NIIF

### 9. Periodos Contables
- ✅ Control de periodos abiertos/cerrados
- ✅ Año fiscal configurable
- ✅ Bloqueo automático o manual
- ✅ Reapertura controlada
- ✅ Validación antes de registrar

### 10. Documentos y Planimetría

**Tipos de Documentos:**
- ✅ Facturas (afectas/exentas)
- ✅ Notas de Crédito
- ✅ Notas de Débito
- ✅ Boletas
- ✅ Boletas de Honorarios
- ✅ Liquidaciones
- ✅ Cheques
- ✅ Transferencias
- ✅ Documentos internos

**Planimetría Contable:**
- ✅ Configuración de asientos automáticos por tipo documento
- ✅ Cuentas cargo/abono predefinidas
- ✅ Cuenta IVA automática
- ✅ Cuenta retención
- ✅ Cuenta redondeo
- ✅ Centro de costo por defecto
- ✅ Simulador de asientos

### 11. Auditoría y Seguridad

**Bitácora de Auditoría:**
- ✅ Registro completo de todas las acciones
- ✅ Usuario, fecha, hora
- ✅ Módulo y acción realizada
- ✅ Datos antes y después (JSON)
- ✅ IP y User Agent
- ✅ Sin eliminación física de registros

**Permisos:**
- ✅ Perfiles de usuario
- ✅ Permisos por módulo
- ✅ Ver, Crear, Editar, Eliminar, Autorizar, Cerrar
- ✅ Control granular
- ✅ Perfil Administrador con acceso total
- ✅ Perfil Contador
- ✅ Perfil Asistente Contable
- ✅ Perfil Consulta (solo lectura)

### 12. Multiempresa
- ✅ Soporte para múltiples empresas
- ✅ Plan de cuentas independiente por empresa
- ✅ Configuración individual
- ✅ RUT, razón social, representante legal
- ✅ Tipo de contribuyente
- ✅ Régimen tributario (General, 14A, 14B, 14D3, Pro-Pyme)

### 13. Multimoneda
- ✅ Soporte CLP, USD, EUR, UF
- ✅ Tipos de cambio por fecha
- ✅ Carga manual o automática
- ✅ Conversión automática
- ✅ Reportes en moneda base o extranjera

### 14. Integración ERP
- ✅ Preparado para integración con:
  - Módulo de Compras
  - Módulo de Ventas
  - Módulo de Bancos
  - Módulo de Remuneraciones
  - Módulo de Inventario
  - Módulo de Activo Fijo
- ✅ Asientos automáticos configurables
- ✅ Centralización automática

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales (26 tablas)

1. **emp_empresas** - Empresas del sistema
2. **sys_usuarios** - Usuarios del sistema
3. **sys_perfiles** - Perfiles de usuario
4. **sys_permisos** - Permisos por perfil y módulo
5. **sys_auditoria** - Bitácora de auditoría completa
6. **con_plan_cuentas** - Plan de cuentas (6 niveles)
7. **con_centros_costo** - Centros de costo
8. **con_auxiliares** - Auxiliares (clientes, proveedores, trabajadores)
9. **con_periodos** - Periodos contables
10. **con_tipos_documentos** - Tipos de documentos
11. **con_planimetria** - Planimetría contable
12. **con_comprobantes** - Comprobantes (cabecera)
13. **con_comprobantes_detalle** - Detalle de comprobantes
14. **con_iva_configuracion** - Configuración IVA
15. **con_libro_compras** - Libro de compras
16. **con_libro_ventas** - Libro de ventas
17. **con_libro_honorarios** - Libro de honorarios
18. **con_f29** - Formulario 29
19. **con_declaraciones_juradas** - DJ Maestra
20. **con_dj1879_honorarios** - DJ 1879
21. **con_dj1887_remuneraciones** - DJ 1887
22. **con_dj1948_retenciones** - DJ 1948
23. **con_dj_generica** - DJ genérica (para otras DJ)
24. **con_cierres** - Cierres contables
25. **con_monedas** - Monedas
26. **con_tipos_cambio** - Tipos de cambio
27. **con_documentos_adjuntos** - Documentos adjuntos
28. **con_configuracion** - Configuración general del módulo

### Vistas SQL

- **vw_libro_diario** - Vista del libro diario
- **vw_libro_mayor** - Vista del libro mayor

### Triggers

- **trg_actualizar_totales_comprobante** - Actualiza totales automáticamente

---

## 📁 Estructura del Proyecto

```
Sistema_Conecta_ERP/
├── assets/
│   ├── css/
│   │   ├── login.css
│   │   ├── style.css
│   │   └── contabilidad.css
│   ├── js/
│   │   ├── app.js
│   │   └── contabilidad.js
│   └── images/
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema_completo_contable.sql
├── includes/
│   ├── functions.php
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
├── modules/
│   ├── contabilidad/
│   │   ├── controllers/
│   │   │   ├── plan_cuentas.php
│   │   │   ├── comprobante.php
│   │   │   ├── libro_diario.php
│   │   │   ├── libro_mayor.php
│   │   │   ├── f29.php
│   │   │   └── ...
│   │   ├── models/
│   │   └── views/
│   ├── dashboard/
│   │   └── controllers/
│   │       └── index.php
│   └── usuarios/
├── uploads/
│   └── documentos/
├── public/
├── index.php
├── login.php
├── logout.php
└── README.md
```

---

## 🚀 Instalación

### Requisitos

- **PHP:** 7.4 o superior
- **MySQL/MariaDB:** 5.7 o superior
- **Servidor Web:** Apache/Nginx
- **Extensiones PHP:**
  - PDO
  - PDO_MySQL
  - mbstring
  - json
  - session

### Pasos de Instalación

1. **Clonar o descargar el repositorio**

```bash
git clone https://github.com/Auditorexchile/Sistema_Conecta_ERP.git
cd Sistema_Conecta_ERP
```

2. **Crear la base de datos**

```sql
CREATE DATABASE conectae_conectaerpbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Importar el esquema**

```bash
mysql -u conectae_conectaerpuser -p conectae_conectaerpbd < database/schema_completo_contable.sql
```

O desde phpMyAdmin: Importar el archivo `database/schema_completo_contable.sql`

4. **Configurar credenciales**

Editar `config/config.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'conectae_conectaerpbd');
define('DB_USER', 'conectae_conectaerpuser');
define('DB_PASS', 'pt125824caraud');
```

5. **Configurar permisos**

```bash
chmod 755 -R /ruta/a/Sistema_Conecta_ERP
chmod 777 -R /ruta/a/Sistema_Conecta_ERP/uploads
```

6. **Acceder al sistema**

```
http://localhost/Sistema_Conecta_ERP/login.php
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE:** Cambiar la contraseña del administrador inmediatamente después del primer acceso.

---

## 👤 Usuarios y Permisos

### Perfiles Predefinidos

1. **Administrador**
   - Acceso total al sistema
   - Gestión de usuarios y permisos
   - Configuración general

2. **Contador**
   - Acceso completo al módulo contable
   - Crear, editar, autorizar comprobantes
   - Generar cierres
   - Acceso a todos los reportes

3. **Asistente Contable**
   - Crear y editar comprobantes
   - Ver libros e informes
   - Sin permisos de cierre

4. **Consulta**
   - Solo lectura
   - Ver reportes
   - Sin permisos de edición

---

## 🔧 Configuración Inicial

### 1. Crear Empresa

1. Ir a **Administración > Empresas**
2. Clic en "Nueva Empresa"
3. Completar datos:
   - RUT
   - Razón Social
   - Giro
   - Dirección
   - Representante Legal
   - Contador
   - Tipo de contribuyente
   - Régimen tributario

### 2. Crear Plan de Cuentas

Opción A: **Importar Plan Estándar**
1. Ir a **Contabilidad > Plan de Cuentas**
2. Clic en "Importar Plan"
3. Seleccionar archivo Excel con estructura:
   - Código
   - Nombre
   - Nivel
   - Clasificación
   - Naturaleza
   - Imputable

Opción B: **Crear Manualmente**
1. Ir a **Contabilidad > Plan de Cuentas**
2. Clic en "Nueva Cuenta"
3. Completar formulario
4. Repetir para cada cuenta

### 3. Configurar Centros de Costo (Opcional)

1. Ir a **Contabilidad > Centros de Costo**
2. Crear estructura jerárquica

### 4. Configurar Tipos de Cambio

1. Ir a **Contabilidad > Configuración > Tipos de Cambio**
2. Cargar tipos de cambio para USD, EUR, UF

### 5. Configurar IVA

1. Ir a **Contabilidad > IVA y F29 > Configuración**
2. Configurar:
   - Tasa IVA (19%)
   - Tasa retención honorarios (12.25%)
   - Cuentas contables IVA
   - Proporcionalidad (si aplica)

---

## 📊 Uso del Sistema

### Registrar un Comprobante

1. Ir a **Contabilidad > Comprobantes > Nuevo Ingreso/Egreso/Traspaso**
2. Completar cabecera:
   - Fecha contable
   - Glosa general
3. Agregar líneas de detalle:
   - Cuenta contable
   - Debe / Haber
   - Centro de costo (opcional)
   - Auxiliar (opcional)
   - Glosa línea
4. Verificar cuadratura (Debe = Haber)
5. Clic en "Guardar" (queda como Borrador)
6. Clic en "Contabilizar" para confirmar

### Generar Libro Diario

1. Ir a **Contabilidad > Libros > Libro Diario**
2. Seleccionar periodo (desde - hasta)
3. Aplicar filtros (opcional):
   - Cuenta específica
   - Centro de costo
   - Auxiliar
4. Clic en "Generar"
5. Exportar a PDF o Excel

### Generar F29

1. Ir a **Contabilidad > IVA y F29 > Formulario 29**
2. Seleccionar mes y año
3. Clic en "Recalcular" (toma datos automáticos)
4. Revisar y ajustar si es necesario
5. Guardar
6. Exportar para declaración

### Realizar Cierre Mensual

1. Ir a **Contabilidad > Cierres > Cierre Mensual**
2. Seleccionar periodo
3. El sistema validará:
   - Todos los comprobantes contabilizados
   - No hay descuadres
   - F29 cerrado
4. Clic en "Cerrar Periodo"
5. Confirmar acción

---

## 🔐 Seguridad

- ✅ Contraseñas encriptadas con bcrypt
- ✅ Protección CSRF
- ✅ Validación de inputs
- ✅ Sesiones seguras
- ✅ Control de permisos granular
- ✅ Auditoría completa de acciones
- ✅ Sin eliminación física de datos
- ✅ Registro de IP y User Agent

---

## 📝 Normativa y Cumplimiento

### Normativa SII (Servicio de Impuestos Internos)
- ✅ Libros legales según formato SII
- ✅ Formulario 29 con códigos oficiales
- ✅ Declaraciones Juradas formato SII
- ✅ Retenciones según normativa
- ✅ Libro de Compras y Ventas
- ✅ Libro de Honorarios

### Normas NIIF
- ✅ Balance de 8 columnas
- ✅ Estado de Resultados por función
- ✅ Flujo de Caja método directo
- ✅ Clasificación de activos y pasivos
- ✅ Reconocimiento de ingresos y gastos

### Buenas Prácticas Contables
- ✅ Partida doble
- ✅ Cuadratura obligatoria
- ✅ Trazabilidad total
- ✅ Sin alteración de registros históricos
- ✅ Auditoría completa

---

## 🆘 Soporte y Ayuda

Para soporte técnico, consultas o reportar problemas:

- **Email:** soporte@conectaerp.cl
- **Web:** https://www.auditorexchile.cl
- **GitHub Issues:** https://github.com/Auditorexchile/Sistema_Conecta_ERP/issues

---

## 📜 Licencia

Este software es propietario y confidencial.

Copyright © 2024 Auditorexchile. Todos los derechos reservados.

Uso restringido a licenciatarios autorizados.

---

## 🙏 Créditos

- **Desarrollado por:** Auditorexchile
- **Arquitectura:** Nivel Softland
- **Tecnologías:** PHP, MySQL, Bootstrap 5, jQuery
- **Versión:** 1.0.0
- **Fecha:** Diciembre 2024

---

## 🔄 Changelog

### Versión 1.0.0 (2024-12-18)
- ✅ Implementación completa del módulo contable
- ✅ Base de datos con 26 tablas principales
- ✅ Plan de cuentas 6 niveles
- ✅ Comprobantes contables (9 tipos)
- ✅ Libros contables y legales
- ✅ IVA y Formulario 29
- ✅ Declaraciones Juradas (13 tipos)
- ✅ Cierres contables
- ✅ Auditoría y seguridad
- ✅ Multiempresa
- ✅ Multimoneda
- ✅ Sistema de permisos
- ✅ Dashboard con indicadores

---

## 🚧 Roadmap Futuro

### Versión 1.1 (Q1 2025)
- [ ] Módulo de Activo Fijo
- [ ] Integración Remuneraciones
- [ ] Conciliación Bancaria
- [ ] Presupuestos

### Versión 1.2 (Q2 2025)
- [ ] API REST
- [ ] Aplicación móvil
- [ ] Firma electrónica
- [ ] Portal del contador

### Versión 2.0 (Q3 2025)
- [ ] Inteligencia Artificial
- [ ] Predicciones financieras
- [ ] Análisis avanzado
- [ ] Integración con bancos

---

**🎉 ¡Gracias por usar ConectaERP!**

Sistema diseñado y desarrollado con ❤️ por Auditorexchile para contadores y empresas chilenas.
