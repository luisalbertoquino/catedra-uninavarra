# Cátedra UNINAVARRA · Plataforma de emprendimientos

Vitrina pública de los emprendimientos de la Cátedra UNINAVARRA / LION HUB EVENT. Es reutilizable en cada edición semestral.

- **Público:** inicio con cuenta regresiva y cronograma, listado con búsqueda y filtros, ficha de cada emprendimiento (video pitch, galería, equipo, contacto, compartir), ranking con podio y archivo de ediciones.
- **Comunidad:** registro, voto (hasta N por edición; se puede retirar mientras la votación esté abierta), calificación de 1 a 5 y comentarios.
- **Inversionistas / aliados:** registran *intenciones de apoyo* (inversión, donación, mentoría, alianza o compra). **No se maneja dinero en línea.**
- **Admin:** dashboard con gráficas, ediciones (se precargan con la configuración de la anterior), emprendimientos con equipo y galería, resultados (puntaje del comité + votación ponderada, selección automática de finalistas), gestión de apoyos, moderación de comentarios, usuarios, categorías y exportes CSV.

## Stack
Laravel 10 · PHP 8.1 · MySQL · Blade + Tailwind CSS 3 + Alpine.js · Chart.js (admin)

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
- `inversionista@demo.co`: inversionista
- `estudiante1..70@uninavarra.edu.co`: comunidad

> Los emprendimientos, personas y cifras del seeder son ficticios. Las imágenes vienen de picsum.photos.

## Configuración (`.env`)
| Variable | Por defecto | Uso |
|---|---|---|
| `CATEDRA_REQUIRE_VERIFIED_TO_VOTE` | `true` | Exige correo verificado para votar |
| `CATEDRA_REQUIRE_VERIFIED_TO_COMMENT` | `false` | Exige correo verificado para comentar |
| `CATEDRA_MODERATE_COMMENTS` | `false` | Los comentarios quedan ocultos hasta que el admin los apruebe |
| `MAIL_MAILER` | `log` | En producción, configurar SMTP para enviar los correos de verificación |

Cada edición define en el admin: fechas de votación y final, % de peso de la comunidad (30 % por defecto), votos por persona, número de finalistas y, opcionalmente, un dominio de correo para restringir el voto (ej. `uninavarra.edu.co`).

## Flujo semestral
1. **Admin → Ediciones → Nueva edición** (se copia el cronograma anterior; ajustar fechas).
2. Cargar los emprendimientos con su video pitch (YouTube, Vimeo o Drive), logo, portada, galería y equipo.
3. **Activar** la edición: pasa a la portada.
4. Durante la votación, seguir el dashboard. Al cierre, registrar los puntajes del comité en **Resultados**, marcar finalistas y publicar resultados.
5. Tras la gran final, asignar la posición final (1 = ganador).

## Personalización de marca
Colores en `tailwind.config.js` (`ink`, `lion`, `volt`, `aqua`). Logo provisional en `resources/views/components/brand.blade.php`; reemplazarlo por el oficial. Contacto en `config/catedra.php`.
