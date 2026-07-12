# OBJETIVOS Y ALCANCE

## Objetivo general

Realizar una auditoría de seguridad integral al sistema de gestión hotelera PlazaMoyobamba, identificando, explotando y documentando vulnerabilidades críticas (OWASP Top 10) mediante la metodología PTES, con el fin de proponer medidas correctivas que protejan los datos sensibles de huéspedes y colaboradores, garanticen la continuidad operativa del negocio y aseguren el cumplimiento de la normativa peruana e internacional aplicable.

## Objetivos específicos

1. **Identificar vulnerabilidades** en el sistema PlazaMoyobamba mediante reconocimiento, escaneo y análisis de código fuente, cubriendo las categorías del OWASP Top 10.

2. **Explotar cada vulnerabilidad** en un entorno controlado para demostrar su impacto real en la confidencialidad, integridad y disponibilidad de la información.

3. **Documentar cada hallazgo** siguiendo la metodología PTES (5 fases: Reconocimiento, Escaneo, Obtención de Acceso, Mantenimiento del Acceso y Borrado de Huellas).

4. **Proponer medidas correctivas** específicas para cada vulnerabilidad, priorizando soluciones de bajo costo y alta efectividad.

5. **Generar un informe profesional** (PAF) que sirva como evidencia técnica para la dirección del hotel y como guía de remediación para el equipo de TI.

## Alcance

El proyecto abarca:

- **Alcance técnico:** Pruebas de penetración sobre la aplicación web del sistema PlazaMoyobamba desplegada en el VPS 37.60.230.11 (puerto 80), incluyendo el frontend, backend (Laravel 12 / PHP 8.2) y base de datos (PostgreSQL 16.14).
- **Alcance geográfico:** Entorno de laboratorio controlado, infraestructura del Hotel PlazaMoyobamba (Lambayeque, Perú).
- **Alcance temporal:** Julio 2026. Las pruebas se realizaron en horario fuera de operación crítica para no afectar la disponibilidad del servicio.
- **Fuera de alcance:** Pruebas de ingeniería social, ataques físicos, análisis de infraestructura de red externa al VPS, aplicaciones móviles o sistemas de terceros.
