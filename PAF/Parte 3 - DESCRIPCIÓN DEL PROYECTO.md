# DESCRIPCIÓN DEL PROYECTO

## Descripción del funcionamiento del Sistema

El Sistema PlazaMoyobamba es una aplicación web desarrollada en Laravel 12 con PostgreSQL como motor de base de datos. Está diseñada para la gestión integral de un hotel, cubriendo las siguientes funcionalidades:

- **Gestión de usuarios y roles:** Permite registrar, autenticar y asignar roles (admin, gerente, recepcionista, contador, limpieza) mediante Spatie Permissions.
- **Gestión de habitaciones:** CRUD de habitaciones con tipos, precios, estados (disponible, ocupada, mantenimiento).
- **Gestión de reservas:** Registro de reservas con fechas de ingreso/salida, asignación de habitaciones, y estados.
- **Gestión de ventas:** Facturación de servicios (alojamiento, consumos), generación de comprobantes, y registro de pagos.
- **Gestión de huéspedes/cliente:** Registro de datos personales, historial de estadías, y documentos de identidad.
- **Dashboard administrativo:** Panel con métricas de ocupación, ingresos, y gráficos para la toma de decisiones.
- **Módulo de reportes:** Exportación de datos de ventas, ocupación y rentabilidad.

## Selección de objetivos

### Selección de usuarios

El sistema es utilizado por los siguientes perfiles dentro del hotel:

| Usuario | Área | Responsabilidad |
|---------|------|----------------|
| Administrador | Gerencia General | Control total del sistema, gestión de usuarios, reportes |
| Gerente | Administración | Supervisión de operaciones, dashboard, reportes |
| Recepcionista | Recepción | Reservas, check-in/check-out, registro de huéspedes |
| Contador | Contabilidad | Facturación, cierre de caja, reportes financieros |
| Personal de Limpieza | Mantenimiento | Actualización de estado de habitaciones |

### Expectativas de usuario

- **Recepcionistas:** Agilidad en el registro de huéspedes y reservas, interfaz clara, respuesta rápida.
- **Gerentes/Administradores:** Datos precisos en tiempo real sobre ocupación e ingresos, reportes exportables.
- **Contadores:** Integridad de los datos financieros, trazabilidad de pagos y facturación.
- **Personal de limpieza:** Interfaz simple para marcar habitaciones como listas o en mantenimiento.

### Expectativas de la organización

La organización espera que el proyecto de auditoría de seguridad:

1. Identifique y demuestre todas las vulnerabilidades críticas del sistema mediante pruebas controladas.
2. Proporcione evidencia clara (capturas, logs, código) de cada hallazgo para respaldar las correcciones.
3. Entregue un plan de mitigación priorizado según el impacto en el negocio.
4. Capacite al equipo de TI del hotel para mantener la postura de seguridad a futuro.
5. Genere documentación profesional que sirva como antecedente para futuras auditorías y cumplimiento normativo.