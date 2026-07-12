# ANÁLISIS DE LA INFRAESTRUCTURA TECNOLÓGICA

## 1. Diagrama de red (lógica)

```
                    ┌─────────────────────────────────────────────┐
                    │          Internet / Cliente                 │
                    └──────────────────┬──────────────────────────┘
                                       │
                                       │ HTTP (puerto 80)
                                       ▼
                    ┌─────────────────────────────────────────────┐
                    │          VPS 37.60.230.11                   │
                    │          Ubuntu Server 22.04                │
                    └──────────────────┬──────────────────────────┘
                                       │
                              Docker Host
                                       │
              ┌────────────────────────┼──────────────────────────┐
              │                        │                          │
              ▼                        ▼                          ▼
   ┌─────────────────────┐  ┌─────────────────────┐  ┌──────────────────────┐
   │   plazamoyobamba-web │  │ plazamoyobamba-app  │  │  plazamoyobamba-db   │
   │      (Nginx)         │  │   (PHP-FPM 8.2)     │  │  (PostgreSQL 16.14)  │
   │      puerto 80       │─▶│   Laravel 12.58.0   │─▶│   puerto 5432        │
   │                      │  │                     │  │  plaza_user          │
   └──────────────────────┘  └─────────────────────┘  │  BD: plazamoyobanba  │
                                                       └──────────────────────┘
```

## 2. Inventario de software

| Componente | Tecnología | Versión | Propósito |
|------------|-----------|---------|-----------|
| Sistema operativo | Ubuntu Server | 22.04 LTS | Host del VPS |
| Contenedorización | Docker | 24.x | Aislamiento de servicios |
| Servidor web | Nginx | 1.24 | Proxy inverso y contenido estático |
| Lenguaje backend | PHP | 8.2.32 | Procesamiento de peticiones dinámicas |
| Framework | Laravel | 12.58.0 | Estructura MVC del sistema hotelero |
| Base de datos | PostgreSQL | 16.14 | Persistencia de datos |
| DNS / Dominio | (ninguno) | — | Se accede por IP directamente en entorno de pruebas |
| Cliente de BD | pgAdmin / psql | — | Administración remota de BD |

## 3. Configuración de seguridad actual (hallazgos)

| Componente | Fallo de seguridad | Riesgo |
|------------|-------------------|--------|
| Nginx | No hay WAF ni filtrado de IPs | Permitidos ataques DDoS, SQLi, XSS sin restricción |
| Apache/Nginx | No hay limitación de tasa (rate limiting) | Fuerza bruta en login ilimitada |
| Laravel | APP_DEBUG=true en producción | Stack traces expuestos con información sensible |
| Laravel | Validación de tipos de archivo insuficiente | Subida de webshell (RCE) |
| Laravel | Protección Mass Assignment deficiente | Escalación de privilegios |
| Laravel | Sanitización de entrada insuficiente | XSS almacenado, SQLi |
| PostgreSQL | Usuario plaza_user con permisos amplios | Acceso completo a BD desde app |
| Docker | Puertos internos no aislados | Potencial pivoting entre contenedores |

## 4. Evaluación del consumo energético (impacto ambiental)

| Componente | Consumo estimado (W) | Horas/día | Consumo diario (kWh) |
|------------|---------------------|-----------|---------------------|
| VPS (servidor virtual) | ~50 W (estimado) | 24 | 1.2 |
| Enrutador/switch local | ~15 W | 24 | 0.36 |
| Estación de trabajo (pentesting) | ~150 W | 8 | 1.2 |
| **Total estimado** | **215 W** | — | **2.76 kWh/día** |

**Nota:** El consumo de un VPS es compartido con otros tenants del hipervisor; el valor de 50 W es una estimación conservadora basada en el promedio de consumo de servidores virtualizados.
