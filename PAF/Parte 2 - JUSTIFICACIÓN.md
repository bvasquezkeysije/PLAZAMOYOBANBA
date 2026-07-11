**JUSTIFICACIÓN**

## Por qué el proyecto es requerido

El Hotel PlazaMoyobamba utiliza un sistema de gestión desarrollado en Laravel que presenta vulnerabilidades críticas de seguridad (SQLi, Mass Assignment, File Upload → RCE, XSS, IDOR, ausencia de rate limiting, y LFI) que exponen datos sensibles de huéspedes, colaboradores y la operación diaria del hotel. Sin una intervención oportuna, estas fallas pueden ser explotadas por atacantes externos o internos, generando pérdidas económicas, fuga de información privilegiada, daño reputacional e incluso la interrupción total del servicio de alojamiento.

## Qué se hará

Se realizará un análisis de seguridad integral que abarca:

1. **Auditoría de código fuente:** Revisión manual y automatizada del código Laravel para identificar vulnerabilidades OWASP Top 10.
2. **Pruebas de penetración controladas:** Ejecución de ataques éticos (SQLi, Mass Assignment, RCE, XSS, IDOR, fuerza bruta, LFI) en un entorno de laboratorio para confirmar la explotabilidad de cada falla.
3. **Documentación de hallazgos:** Registro detallado de cada vulnerabilidad, su impacto, y evidencia de explotación.
4. **Propuesta de correcciones:** Mitigaciones concretas (validación de entradas, sanitización, control de acceso basado en roles, rate limiting, desinfección de archivos subidos) con el código modificado listo para implementar.

## Cuándo y cómo

El proyecto se ejecuta en 4 fases:

| Fase | Actividad                                          | Duración |
| ---- | -------------------------------------------------- | -------- |
| 1    | Reconocimiento y escaneo de vulnerabilidades       | 1 semana |
| 2    | Explotación controlada y verificación de hallazgos | 2 semana |
| 3    | Documentación y elaboración de informes            | 3 semana |
| 4    | Propuesta de correcciones y validación final       | 4 semana |

Se utiliza la metodología de pruebas de penetración basada en el PTES (Penetration Testing Execution Standard), combinada con el uso de herramientas como nmap, OWASP ZAP, sqlmap, Metasploit, nikto, y scripts personalizados.

## Costos y beneficios

| Concepto | Detalle |
|----------|---------|
| **Costos** | Infraestructura (VPS, licencias gratuitas/open-source): ~S/ 0. Inversión en horas-hombre del equipo de seguridad: académico.|
| **Beneficios** | Corrección de vulnerabilidades críticas, protección de datos de huéspedes, cumplimiento de normativas de protección de datos, continuidad del negocio, mejora de la reputación hotelera, prevención de pérdidas económicas por incidentes. |

El beneficio de prevenir un solo incidente de seguridad (fuga de datos, ransomware, downtime) supera ampliamente la inversión del proyecto.

## Riesgos y alternativas

| Riesgo                                                 | Probabilidad | Mitigación / Alternativa                                                             |
| ------------------------------------------------------ | ------------ | ------------------------------------------------------------------------------------ |
| Que las correcciones introduzcan nuevos bugs           | Media        | Pruebas unitarias y en staging antes de producción                                   |
| Que el cliente no aplique las correcciones             | Alta         | Capacitación al personal de TI y reporte ejecutivo con impacto en negocio            |
| Que surjan vulnerabilidades no cubiertas en el alcance | Media        | Se priorizan las 7 del OWASP Top 10 más críticas; el resto queda como trabajo futuro |
| Indisponibilidad del entorno de pruebas                | Baja         | Uso de contenedores Docker replicables y VPS en la nube como respaldo                |
