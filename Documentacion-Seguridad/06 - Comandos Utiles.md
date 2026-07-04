# 06 - Comandos Útiles

## Tor
```bash
# Iniciar Tor
sudo systemctl start tor

# Verificar Tor
torsocks curl https://check.torproject.org/api/ip

# curl via Tor
torsocks curl http://192.168.18.31:8001/
```

## SQLmap via Tor
```bash
torsocks sqlmap -u "http://192.168.18.31:8001/login" \
  --data="login=admin&password=test" \
  --batch --level=3 --risk=2
```

## Nmap
```bash
nmap -Pn -sV -p 8001 192.168.18.31
```

## Metasploit
```bash
msfconsole -q
use auxiliary/scanner/http/http_version
set RHOSTS 192.168.18.31
set RPORT 8001
run
```

## k6 Load Test
```bash
k6 run script.js
```

## Httrack (clonar web)
```bash
httrack "http://192.168.18.31:8001" -O ./clon -r3
```

## Docker (para reconstruir)
```bash
cd ~/Sistema-PlazaMoyobanba
docker compose down
docker compose up -d --build
docker compose logs -f
```

## Git
```bash
git clone https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba.git
cd Sistema-PlazaMoyobanba
git pull
```
