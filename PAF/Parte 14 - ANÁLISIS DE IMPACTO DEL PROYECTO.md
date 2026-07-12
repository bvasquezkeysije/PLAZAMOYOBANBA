# ANÁLISIS DE IMPACTO DEL PROYECTO

## 10.1. Evaluación de impacto del proyecto en aspectos de salud

El proyecto de auditoría de seguridad al Sistema PlazaMoyobamba identificó 9 vulnerabilidades críticas que exponen datos sensibles de huéspedes y colaboradores. La implementación de las medidas correctivas propuestas tiene un impacto positivo directo en la salud ocupacional del personal de TI y administrativo del hotel. Al reducir la probabilidad de incidentes de seguridad como filtración de datos personales, robo de credenciales o denegación de servicio, se disminuye el estrés laboral, la sobrecarga de trabajo reactivo y la ansiedad asociada a la gestión de crisis informáticas. Además, se previene la exposición del personal a software malicioso que pudiera comprometer su privacidad o la de sus familias a través de ataques dirigidos.

A nivel internacional, la adopción de medidas como la sanitización de entradas (XSS), consultas parametrizadas (SQLi) y control de acceso (IDOR) contribuye a estándares globales de protección de datos, evitando que brechas de seguridad en el hotel peruano expongan información de turistas internacionales, lo que podría tener implicaciones legales y de salud pública transfronterizas.

| Medida de seguridad | Impacto en la salud en la organización | Impacto en otras empresas del rubro | Normativa relacionada | Responsabilidades del ingeniero |
|---|---|---|---|---|
| Corrección de SQLi (consultas parametrizadas) | Reduce crisis por acceso no autorizado a datos de huéspedes, disminuyendo estrés del personal TI | Empresas hoteleras nacionales e internacionales se benefician al elevar el estándar de seguridad en el sector turismo peruano | Ley N.° 29733 – Protección de Datos Personales (Perú) | Garantizar que las soluciones de seguridad no comprometan el bienestar físico o mental de los usuarios ni del personal técnico |
| Corrección de XSS Almacenado (CSP + sanitización) | Elimina riesgo de ataques que puedan mostrar contenido dañino o perturbador a empleados que usan el sistema a diario | Hoteles que intercambian datos con el sistema se protegen de ataques cross-site que podrían afectar a sus propios empleados | EU Cybersecurity Act | Implementar controles que protejan la salud psicosocial de los trabajadores expuestos a sistemas informáticos |
| Implementación de rate limiting y protección DDoS | Evita denegación de servicio que obligaría al personal a trabajar horas extra no planificadas para restaurar el sistema | Empresas del rubro hotelero que dependen de sistemas similares se benefician al conocer las mejores prácticas de mitigación | Ley N.° 30096 – Ley de Delitos Informáticos (Perú) | Asegurar la continuidad operativa del sistema para evitar sobrecarga laboral del personal |

---

## 10.2. Evaluación de impacto del proyecto en seguridad

La implementación de las medidas correctivas propuestas fortalece significativamente la postura de seguridad del Hotel PlazaMoyobamba. Cada vulnerabilidad identificada fue explotada exitosamente durante las pruebas de penetración, demostrando su impacto real. La corrección de la inyección SQL elimina la posibilidad de que un atacante acceda a toda la base de datos sin autenticación. La protección contra Mass Assignment impide la escalación no autorizada de privilegios. La validación de archivos en uploads cierra el vector de ejecución remota de comandos (RCE). Estas medidas, en conjunto, garantizan la confidencialidad, integridad y disponibilidad de la información del hotel.

A nivel internacional, las vulnerabilidades identificadas corresponden al OWASP Top 10, por lo que su corrección alinea al hotel con estándares globales de seguridad web. Esto facilita la interoperabilidad segura con sistemas de reservas internacionales, pasarelas de pago y plataformas turísticas globales.

| Medida de seguridad | Impacto en la seguridad en la organización | Impacto en otras empresas del rubro | Normativa relacionada | Responsabilidades del ingeniero |
|---|---|---|---|---|
| Corrección de File Upload → RCE (validación MIME + extensiones) | Elimina la posibilidad de ejecución remota de comandos en el servidor (www-data). Se protege el .env con credenciales de BD | Empresas hoteleras que usan Laravel/PHP se benefician al conocer este vector de ataque crítico | NIST SP 800-53 – Security and Privacy Controls | Diseñar e implementar soluciones que salvaguarden la integridad, disponibilidad y confidencialidad de la información |
| Corrección de Mass Assignment ($guarded en modelos) | Impide que usuarios registrados escalen a administrador manipulando campos no protegidos | Cadenas hoteleras que usan frameworks MVC pueden auditar sus propios modelos contra Mass Assignment | OWASP Top 10 – A01:2021 (Broken Access Control) | Anticipar riesgos de escalación de privilegios y gestionar vulnerabilidades en la capa de acceso a datos |
| Corrección de IDOR (Laravel Policies) | Asegura que cada usuario solo acceda a los recursos que le pertenecen, protegiendo datos de ventas y clientes | Empresas del sector que exponen APIs sin autenticación pueden replicar la solución de Policies | Ley N.° 30096 – Ley de Delitos Informáticos (Perú) | Implementar control de acceso basado en políticas para cada recurso sensible |
| Desactivación de Debug Mode (APP_DEBUG=false) | Elimina la exposición de stack traces, variables de entorno y consultas SQL en producción | Cualquier aplicación Laravel en producción se beneficia al conocer el riesgo de APP_DEBUG=true | OWASP Top 10 – A05:2021 (Security Misconfiguration) | Verificar la configuración de producción antes del despliegue |

---

## 10.3. Evaluación de impacto del proyecto en aspectos legales

El proyecto asegura que el Hotel PlazaMoyobamba cumpla con la legislación peruana e internacional aplicable en materia de protección de datos y delitos informáticos. La Ley N.° 29733 (Protección de Datos Personales) exige que las organizaciones implementen medidas técnicas adecuadas para salvaguardar los datos personales que procesan. La explotación exitosa de vulnerabilidades como SQLi, IDOR y LFI demostró que el sistema no cumplía con este requisito, exponiendo nombres completos, DNI, correos electrónicos y teléfonos de huéspedes. Las medidas correctivas propuestas cierran estas brechas, mitigando el riesgo de sanciones administrativas (hasta 100 UIT), demandas civiles y responsabilidad penal por parte de la empresa.

A nivel internacional, el hotel interactúa con turistas de diversas nacionalidades, por lo que también debe considerar el cumplimiento de regulaciones como el GDPR (Europa) y la EU Cybersecurity Act. La implementación de las medidas propuestas facilita la trazabilidad y auditoría de los accesos al sistema, generando evidencia digital que puede ser presentada ante organismos regulatorios.

| Medida de seguridad | Impacto legal en la organización | Impacto legal en otras empresas | Normativa relacionada | Responsabilidades del ingeniero |
|---|---|---|---|---|
| Corrección de SQLi + LFI (protección de datos) | Evita filtración masiva de datos personales de huéspedes (nombres, DNI, emails) que podría generar multas por incumplimiento de Ley 29733 | Empresas hoteleras peruanas que manejan datos similares deben implementar medidas equivalentes para cumplir con la ley | Ley N.° 29733 – Ley de Protección de Datos Personales (Perú) | Asegurar que los sistemas cumplan con las leyes de protección de datos personales, propiedad intelectual y delitos informáticos |
| Implementación de logs y monitoreo | Permite la trazabilidad de accesos y modificaciones, facilitando auditorías legales y forenses ante incidentes | Empresas del rubro pueden adoptar el mismo enfoque de logging seguro para cumplir con estándares de evidencia digital | NIST Cybersecurity Framework (EE. UU.) | Generar valor a la organización evitando pérdida de credibilidad y multas por incumplimiento normativo |

---

## 10.4 Evaluación de impacto del proyecto en aspectos sociales

La implementación de las medidas de seguridad propuestas protege la identidad cultural y la privacidad de los huéspedes y colaboradores del Hotel PlazaMoyobamba, ubicado en la región Lambayeque, Perú. Al prevenir la filtración de datos personales como nombres, preferencias de consumo, hábitos de viaje y datos de contacto, se respeta la diversidad cultural de los usuarios del sistema. Los huéspedes, tanto nacionales como internacionales, pueden utilizar los servicios del hotel sin temor a que sus datos sean expuestos o utilizados para fines no autorizados.

Las vulnerabilidades como XSS Almacenado y Debug Mode podrían haber sido utilizadas para robar sesiones y suplantar identidades, lo que habría afectado la confianza de los usuarios en el sistema y en la institución. La corrección de estas fallas contribuye a crear un entorno digital seguro que respeta las diferencias culturales, lingüísticas y sociales de todos los usuarios.

| Medida de seguridad | Impacto en la identidad cultural de las personas | Impacto cultural en otros países | Normativa relacionada | Responsabilidades del ingeniero |
|---|---|---|---|---|
| Corrección de XSS Almacenado (CSP + sanitización) | Protege la privacidad de las interacciones digitales de huéspedes y empleados, respetando sus preferencias y datos culturales | Turistas internacionales que usan el sistema confían en que sus datos no serán expuestos ni manipulados | Declaración Universal de Derechos Humanos, Art. 19 | Diseñar soluciones que garanticen que las tecnologías no vulneren la identidad cultural personal ni limiten la libre expresión |
| Corrección de IDOR + SQLi (control de acceso a datos) | Evita que preferencias personales (tipo de habitación, servicios contratados, método de pago) sean expuestas sin autorización | Empresas hoteleras internacionales que interactúan con el sistema se benefician de un estándar común de protección de datos culturales | Carta Iberoamericana de Derechos Digitales | Asegurar que la solución respete diferentes lenguas, costumbres y prácticas culturales de los trabajadores y clientes |
