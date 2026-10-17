# Cátedra UNINAVARRA · Plataforma de emprendimientos

Vitrina pública de los emprendimientos de la Cátedra UNINAVARRA / LION HUB EVENT. Es reutilizable en cada edición semestral.

- **Público:** inicio con video promocional, anuncio de votación con cuenta regresiva y cronograma; listado con búsqueda y filtros; ficha de cada emprendimiento (video pitch, galería, equipo, contacto, compartir); ranking de emprendimientos y de inversionistas; archivo de ediciones.
- **Comunidad:** registro, **un voto por persona** en cada edición (se puede cambiar mientras la votación esté abierta), calificación de 1 a 5 y comentarios.
- **Inversionistas / aliados:** registran *intenciones de apoyo* (inversión, donación, mentoría, alianza o compra). **No se maneja dinero en línea.** Aparecen en el ranking público solo si lo autorizan.
- **Jurados:** panel propio (`/jurado`) para ver el video de cada equipo y calificarlo con la rúbrica de la edición. El **Profesor experto** (nombre configurable) tiene más peso.
- **Admin:** dashboard, ediciones (con rúbrica y jurado experto), emprendimientos, jurados, resultados (comité + comunidad, finalistas automáticos, matriz de evaluaciones), apoyos, comentarios, usuarios, categorías y exportes CSV.

## Stack
Laravel 10 · PHP 8.1 · MySQL · Blade + Tailwind CSS 3 + Alpine.js · Font Awesome · Chart.js (admin)

## Instalación local (Laragon)
```bash
composer install
npm install
cp .env.example .env && php artisan key:generate   # ajustar DB_* (BD: emprendimientos)
php artisan migrate --seed                          # datos de DEMOSTRACIÓN
php artisan storage:link
npm run build        # o: npm run dev
php artisan serve    # o: virtual host apuntando a /public
```

Usuarios de demo (contraseña `password`):
- `admin@uninavarra.edu.co`: administrador
- `experto@uninavarra.edu.co`: jurado Profesor experto · `jurado1..4@uninavarra.edu.co`: jurados
- `inversionista@demo.co`: inversionista
- `estudiante1..70@uninavarra.edu.co`: comunidad

> Los emprendimientos, personas y cifras del seeder son ficticios. Las imágenes vienen de picsum.photos y los videos de ejemplo son los promocionales de la Cátedra.
> Para agregar solo los datos de jurados a una base existente: `php artisan db:seed --class=JuryDemoSeeder`.

## Evaluación del comité y puntaje final
1. Cada edición tiene una **rúbrica** (por defecto: innovación, viabilidad comercial, viabilidad técnica, proyección y sostenibilidad; 20 % cada uno, escala 1 a 5). Se edita en *Admin → Ediciones*.
2. Cada evaluación de un jurado se convierte a 0–100 según el peso de cada criterio.
3. **Puntaje del comité** = promedio ponderado de los jurados; el Profesor experto pesa ×2 (configurable).
4. **Puntaje final** = comité × 70 % + comunidad × 30 % (la comunidad se normaliza: el más votado = 100).
5. Si un emprendimiento no tiene evaluaciones, el admin puede escribir un puntaje manual.

## Seguridad y anti-bots
- Registro y recuperación de contraseña: campo trampa invisible, tiempo mínimo de llenado, bloqueo de correos desechables, máx. 3 por minuto y 10 por día por IP, y CAPTCHA opcional (Cloudflare Turnstile).
- Límite general de peticiones por IP (`CATEDRA_RATE_LIMIT_PER_MINUTE`).
- Cabeceras de seguridad (anti-clickjacking, nosniff, referrer y permissions policy; HSTS en HTTPS).
- Contraseñas de mínimo 8 caracteres con letras y números; correo verificado para votar, comentar y aportar.
- Jurados y administradores no votan como comunidad.

## Configuración (`.env`)
| Variable | Por defecto | Uso |
|---|---|---|
| `CATEDRA_REQUIRE_VERIFIED_TO_VOTE` | `true` | Exige correo verificado para votar |
| `CATEDRA_REQUIRE_VERIFIED_TO_COMMENT` | `true` | Exige correo verificado para comentar |
| `CATEDRA_REQUIRE_VERIFIED_TO_PLEDGE` | `true` | Exige correo verificado para registrar apoyos |
| `CATEDRA_MODERATE_COMMENTS` | `false` | Los comentarios quedan ocultos hasta que el admin los apruebe |
| `CATEDRA_RATE_LIMIT_PER_MINUTE` | `180` | Peticiones por minuto permitidas por IP |
| `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` | vacío | Activan el CAPTCHA en registro y recuperación de contraseña |
| `MAIL_MAILER` | `log` | En producción, configurar SMTP para enviar los correos de verificación |

En producción: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS y `SESSION_SECURE_COOKIE=true`.

Cada edición define en el admin: fechas de votación y final, video promocional, % de peso de la comunidad (30 %), votos por persona (1), número de finalistas, rúbrica, nombre y peso del jurado experto y, opcionalmente, un dominio de correo para restringir el voto (ej. `uninavarra.edu.co`).

## Flujo semestral
1. **Admin → Ediciones → Nueva edición** (se copian el cronograma y la rúbrica anteriores; ajustar fechas).
2. Cargar los emprendimientos con su video pitch (YouTube, Vimeo o Drive), logo, portada, galería y equipo.
3. **Admin → Jurados**: crear las cuentas (marcar al Profesor experto) y enviarles sus credenciales.
4. **Activar** la edición: pasa a la portada y queda disponible para los jurados.
5. Durante la votación, seguir el dashboard y la matriz de evaluaciones. Al cierre, en **Resultados**, marcar finalistas y publicar resultados (esto cierra la evaluación de los jurados).
6. Tras la gran final, asignar la posición final (1 = ganador).

## Pruebas
```bash
php artisan test   # usa la base emprendimientos_testing (crearla vacía antes)
```

## Personalización de marca
Colores en `tailwind.config.js` (`ink`, `lion`, `volt`, `aqua`). Logo en `resources/views/components/brand.blade.php` (imágenes en `public/images`). Contacto en `config/catedra.php`.
