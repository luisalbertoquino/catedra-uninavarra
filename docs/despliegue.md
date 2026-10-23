# Despliegue en servidor de pruebas (OpenLiteSpeed + MySQL)

Guía para montar la plataforma en un servidor Linux con OpenLiteSpeed. Los comandos se ejecutan como `root`.

> **Puerto 7080:** es la consola de administración (WebAdmin) de OpenLiteSpeed. El sitio se publica en otro *listener* (80, o 8088 mientras se prueba).

## 1. Revisar lo que ya está instalado
```bash
lsb_release -a; ls /usr/local/lsws/; ls /usr/local/lsws/ | grep lsphp
mysql --version; git --version; node -v; composer -V
```

## 2. PHP 8.3 para LiteSpeed
> Usa **lsphp83**: Laravel 10 es compatible con PHP 8.1–8.3 (no con 8.4). MariaDB 10.6+ / 11.x funciona igual que MySQL.
```bash
wget -O - https://repo.litespeed.sh | bash          # repositorio de LiteSpeed (si no está agregado)
apt install -y lsphp83 lsphp83-common lsphp83-mysql lsphp83-curl lsphp83-intl lsphp83-opcache
/usr/local/lsws/lsphp83/bin/php -m | grep -iE "pdo_mysql|mbstring|xml|zip|gd|curl|intl|fileinfo|openssl|tokenizer|bcmath"
# Si falta alguna extensión: apt search lsphp83 e instalar el paquete correspondiente.
ln -sf /usr/local/lsws/lsphp83/bin/php /usr/local/bin/php   # PHP por consola (si no hay otro PHP instalado)
php -v
```

## 3. Composer y Node.js (para compilar estilos)
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer && rm composer-setup.php
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt install -y nodejs
```

## 4. Base de datos
```bash
mysql -u root -p
```
```sql
CREATE DATABASE catedra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'catedra'@'localhost' IDENTIFIED BY 'CAMBIA-ESTA-CLAVE-LARGA';
GRANT ALL PRIVILEGES ON catedra.* TO 'catedra'@'localhost';
FLUSH PRIVILEGES;
```
MySQL debe escuchar solo en `127.0.0.1` (no abrir el puerto 3306 en el firewall).

## 5. Código
```bash
cd /var/www
git clone https://github.com/luisalbertoquino/catedra-uninavarra.git
cd catedra-uninavarra
```
Si el repositorio es privado, usa un *token* de GitHub (Settings → Developer settings → Personal access tokens, solo lectura de este repositorio) o una *deploy key* SSH.

## 6. Configuración (`.env`)
```bash
cp .env.example .env
nano .env
```
Valores mínimos:
```dotenv
APP_NAME="Cátedra UNINAVARRA"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP-O-DOMINIO          # https://… cuando se configure el certificado

DB_DATABASE=catedra
DB_USERNAME=catedra
DB_PASSWORD=CAMBIA-ESTA-CLAVE-LARGA

MAIL_MAILER=log                      # hasta configurar el correo
SESSION_SECURE_COOKIE=false          # true cuando haya HTTPS
```

## 7. Instalación
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --seed --force    # crea tablas + datos de demostración (cuentas de prueba)
php artisan storage:link
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R nobody:nogroup storage bootstrap/cache   # usuario con el que corre OpenLiteSpeed
chmod -R ug+rwX storage bootstrap/cache
```

Crea tu administrador con una contraseña real:
```bash
php artisan catedra:crear-admin tu-correo@uninavarra.edu.co --nombre="Tu nombre"
```

## 8. OpenLiteSpeed (consola en `https://IP:7080`)
1. **Virtual Hosts → Add**
   - Virtual Host Name: `catedra`
   - Virtual Host Root: `/var/www/catedra-uninavarra/`
   - Config File: `$SERVER_ROOT/conf/vhosts/catedra/vhconf.conf` (crearlo cuando lo pregunte)
   - Follow Symbolic Link: `Yes` · Enable Scripts/ExtApps: `Yes`
2. En el virtual host:
   - **General → Document Root:** `$VH_ROOT/public/` · **Index Files:** `index.php`
   - **Script Handler → Add:** Suffixes `php`, Handler Type `LiteSpeed SAPI`, Handler `lsphp83`
   - **Rewrite:** Enable Rewrite `Yes`, Auto Load from .htaccess `Yes`
3. **Listeners:** agrega o edita el listener (puerto 80 o 8088) y en **Virtual Host Mappings** asocia `catedra` con el dominio o `*`.
4. **Graceful Restart**.

Prueba: `http://IP-O-DOMINIO/` debe mostrar la portada y `/login` el ingreso.

## 9. Cargar los emprendimientos reales
Los datos reales (nombres de estudiantes y logos) **no están en GitHub**. Se generan en el equipo de desarrollo y se copian al servidor:

```bash
# En el equipo de desarrollo (carpeta del proyecto): genera database/data/ con el JSON y los logos
php artisan catedra:exportar-portafolio
scp -r database/data root@SERVIDOR:/var/www/catedra-uninavarra/database/

# En el servidor
cd /var/www/catedra-uninavarra
php artisan db:seed --class=PortafolioRealSeeder --force
chown -R nobody:nogroup storage
```
El seeder crea o actualiza la edición con su configuración, las categorías, los 15 emprendimientos con equipos y logos, y elimina los emprendimientos ficticios de esa edición. Se puede ejecutar varias veces sin duplicar.

Alternativa desde el navegador: `php artisan catedra:limpiar-demo` y luego **Admin → Importar Excel** con el Excel y los logos.
## 9.1 Jurados y práctica de evaluación
```bash
php artisan catedra:jurados-prueba --quitar-demo   # crea los 2 jurados y el Profesor experto (muestra las contraseñas una vez)
php artisan catedra:jurados-prueba --limpiar       # al terminar la práctica: quita el video de ejemplo y las evaluaciones de práctica
```

## 10. Actualizaciones
Después de cada `git push`:
```bash
cd /var/www/catedra-uninavarra && bash scripts/deploy.sh
```

## 11. Pendientes para producción
- **HTTPS:** Let's Encrypt (en OpenLiteSpeed: *Listeners → SSL*, o `certbot certonly --webroot -w /var/www/catedra-uninavarra/public -d dominio`). Luego `APP_URL=https://…` y `SESSION_SECURE_COOKIE=true`.
- **Correo:** ver la sección siguiente.
- **Cuentas de prueba:** eliminarlas o cambiar sus contraseñas antes de abrir el sitio al público.
- **Copias de seguridad:** `mysqldump` diario de la base y copia de `storage/app/public` (logos e imágenes subidas).

## 12. Correo (verificación de cuentas)
Opción recomendada: **enviar por el correo institucional** (Google Workspace o Microsoft 365 de UNINAVARRA) con una cuenta tipo `no-responder@uninavarra.edu.co`. La reputación del dominio ya existe y no hay que mantener un servidor de correo.

Si se monta un servidor propio, debe ser **solo de envío** y estar protegido para no terminar en listas negras:
- Postfix escuchando solo en `127.0.0.1` (`inet_interfaces = loopback-only`): no recibe correo y no es un *open relay*.
- Registros DNS **SPF**, **DKIM** (OpenDKIM) y **DMARC** del dominio, y **PTR** (DNS inverso) de la IP del servidor apuntando al mismo nombre.
- Límite de envío por hora y revisión de `/var/log/mail.log`; la plataforma ya limita registros y reenvíos por IP.
- Verificar la IP en listas negras (MXToolbox) antes de empezar.
