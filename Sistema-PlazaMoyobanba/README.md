# Sistema PlazaMoyobanba

Proyecto Laravel para Hotel PlazaMoyobanba con:
- Login por correo o usuario
- Roles (`admin`, `recepcionista`)
- Panel admin inicial (`Dashboard`, `Usuarios`)
- Despliegue con Docker (`php-fpm + nginx + postgres`)

## Requisitos servidor (Ubuntu 24.04)

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu noble stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin git
sudo usermod -aG docker $USER
```

Cerrar sesión y volver a entrar por SSH.

## Despliegue

```bash
cd ~
git clone https://github.com/bvasquezkeysije/Sistema-SolyLuna.git
cd Sistema-PlazaMoyobanba
cp .env.example .env
```

Editar `.env` para producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://plazamoyobanba.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=plazamoyobanba-db
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Levantar:

```bash
chmod +x scripts/deploy-prod.sh
./scripts/deploy-prod.sh
```

## Cloudflare Tunnel (si ya está creado)

Configura el tunnel para apuntar al contenedor web:
- URL de origen: `http://localhost:80`

Si usas archivo config de cloudflared:

```yaml
tunnel: plazamoyobanba-tunnel
credentials-file: /etc/cloudflared/<TUNNEL_ID>.json
ingress:
  - hostname: plazamoyobanba.com
    service: http://localhost:80
  - service: http_status:404
```

Reiniciar servicio:

```bash
sudo systemctl restart cloudflared
sudo systemctl status cloudflared
```

## Credenciales iniciales

- `admin@gmail.com` / `123`

También puedes entrar con usuario:
- `admin`
- `adminrapido`
