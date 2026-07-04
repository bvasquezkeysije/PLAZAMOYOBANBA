# Security Testing - Sistema PlazaMoyobanba

## Equipo
- **Keysi** → Ataque: SQL Injection
- **Brigit** → Ataque: Mass Assignment / Escalación de Roles
- **Alexander** → Ataque: File Upload / RCE

## Documentación
1. [[01 - Reconocimiento]]
2. [[02 - SQL Injection]]
3. [[03 - Mass Assignment]]
4. [[04 - File Upload RCE]]
5. [[05 - Modificaciones al Codigo]]
6. [[06 - Comandos Utiles]]

## Rama con vulnerabilidades
- **Rama:** `sistema-con-vulnerabilidades`
- **Repo:** https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba
- **Estado:** ✅ Subida a GitHub

## Despliegue rápido en servidor
```bash
cd ~/Sistema-PlazaMoyobanba
git fetch origin
git checkout sistema-con-vulnerabilidades
docker compose down
docker compose up -d --build
```

## Herramientas
- Metasploit
- k6
- httrack
- Selenium
- Tor (torsocks)
- SQLmap
- nmap
- curl
