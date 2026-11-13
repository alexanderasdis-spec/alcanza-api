# Alcanza API

Backend de **Alcanza — ¿Cuándo me lo compro?**, una aplicación que convierte un antojo en una meta de ahorro con fecha. Esta API guarda las cuentas, el perfil, las metas y los abonos, y la consume el frontend en React (repositorio `alcanza`).

Proyecto final del módulo **Creación de páginas web 2** · Harold Alexander Ceballos Henao

## Tecnologías

| Parte | Herramienta |
|---|---|
| Framework | Laravel 13 (PHP 8.3 o superior) |
| Sesiones | Laravel Sanctum, con tokens Bearer |
| Base de datos | MySQL 8.4, base `data_alcanza` |
| Entorno | Docker con Laravel Sail, dentro de Ubuntu en WSL2 |
| Correos de prueba | Mailpit |
| Anti-robots | Cloudflare Turnstile |
| Pruebas | Postman y Newman (funcionales), k6 (carga y ataque) |
| Protección | Límites de peticiones por IP, por token y por usuario |

## Arquitectura

Cada petición sigue el mismo camino:

```
Ruta → Form Request → Controlador → Servicio → Repositorio (con interfaz) → Modelo
```

| Capa | Carpeta | Responsabilidad |
|---|---|---|
| Rutas | `routes/api.php` | Definen cada dirección y qué la protege |
| Form Requests | `app/Http/Requests` | Validan los datos y responden 422 con mensajes en español |
| Controladores | `app/Http/Controllers` | Reciben la petición y devuelven el JSON |
| Servicios | `app/Services` | Tienen la lógica del negocio |
| Repositorios | `app/Repositories` | Hablan con la base de datos; sus contratos están en `Contracts/` |
| Modelos | `app/Models` | Representan las tablas y calculan valores como el progreso |
| Políticas | `app/Policies` | `MetaPolicy` impide tocar metas de otra persona (403) |
| Middleware | `app/Http/Middleware` | `LimiteTemprano`: primer filtro, limita por IP y por token (429) |
| Reglas | `app/Rules` | `Turnstile`: comprueba con Cloudflare el "no soy un robot" (422) |

La unión entre cada contrato y su repositorio está en `app/Providers/AppServiceProvider.php`, que también define los límites por usuario, de escrituras y de cuentas. Los valores de los límites están en `config/limites.php`.

## Estado

| Hecho | Pendiente |
|---|---|
| Cuentas con Sanctum, recuperación de contraseña y cambio de contraseña | Subir el repositorio a GitHub |
| Perfil con foto (guardada en la base de datos), metas y abonos | Publicar en Render con la base en TiDB Cloud |
| Turnstile y límites de peticiones | Claves reales de Turnstile y Cloudflare frente al sitio |
| Postman (39 peticiones, 77 comprobaciones) y k6 (carga y ataque) | Correo real con Brevo |
| Imagen de producción (`Dockerfile`) probada con Postman | |

## Instalación

Desde una terminal de Ubuntu, en la carpeta del proyecto:

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

- La API queda en `http://localhost/api`.
- Mailpit, donde llegan los correos de "olvidé mi contraseña", queda en `http://localhost:8025`.
- La primera vez, MySQL tarda unos segundos en quedar listo. Si `migrate` falla, espera medio minuto y vuelve a ejecutarlo.
- Si Ubuntu se apaga, los contenedores se detienen. Para trabajar sin cortes, deja `./vendor/bin/sail up` corriendo en una terminal.

### Variables importantes del `.env`

| Variable | Para qué |
|---|---|
| `DB_DATABASE=data_alcanza` | Nombre de la base de datos |
| `FRONTEND_URLS` | Direcciones del frontend que pueden llamar a la API (CORS), separadas por comas |
| `FRONTEND_URL` | Dirección a la que lleva el enlace del correo para restablecer la contraseña |
| `VITE_PORT=5175` | Evita que Sail use el puerto 5173 del frontend |
| `TURNSTILE_SECRET_KEY` | Clave secreta de Cloudflare Turnstile. La de ejemplo es la de prueba, que siempre aprueba; al desplegar se cambia por la real |
| `LIMITE_POR_IP`, `LIMITE_POR_USUARIO`, `LIMITE_ESCRITURAS`, `LIMITE_CUENTAS` | Peticiones por minuto permitidas (ver "Límites de peticiones") |
| `LIMITE_ALMACEN=file` | Dónde se guardan los contadores de los límites |

## Rutas

Todas empiezan por `/api`. Las protegidas necesitan el encabezado `Authorization: Bearer <token>`.

### Públicas (máximo 10 intentos por minuto)

Registro, login y olvidé contraseña exigen además el campo `turnstile`: el código de la verificación "no soy un robot" de **Cloudflare Turnstile**. La regla `app/Rules/Turnstile.php` lo comprueba con Cloudflare usando `TURNSTILE_SECRET_KEY`; si no es válido, responde 422.

| Método | Ruta | Qué hace |
|---|---|---|
| POST | `/registro` | Crea la cuenta y devuelve `{ usuario, token }` |
| POST | `/login` | Inicia sesión y devuelve un token |
| POST | `/olvide-contrasena` | Envía el correo con el enlace para cambiar la contraseña |
| POST | `/restablecer-contrasena` | Guarda la contraseña nueva con el token del correo |

### Con sesión

| Método | Ruta | Qué hace |
|---|---|---|
| GET | `/usuario` | Usuario dueño del token |
| POST | `/logout` | Cierra la sesión actual |
| PUT | `/contrasena` | Cambia la contraseña y cierra las otras sesiones |
| GET | `/perfil` | Consulta el perfil (404 si todavía no existe) |
| POST | `/perfil` | Crea o actualiza el perfil (201 o 200) |
| POST | `/perfil/foto` | Sube o reemplaza la foto de perfil (`multipart/form-data`) |
| DELETE | `/perfil/foto` | Quita la foto de perfil |
| GET | `/fotos/{clave}` | Muestra una foto (pública, sin token, para usarla en `<img>`) |
| GET | `/metas` | Lista las metas con su progreso y sus abonos |
| POST | `/metas` | Crea una meta |
| GET | `/metas/{id}` | Muestra una meta con sus abonos |
| PUT | `/metas/{id}` | Actualiza una meta |
| DELETE | `/metas/{id}` | Elimina una meta y sus abonos |
| POST | `/metas/{id}/abonos` | Registra un abono y revisa si la meta quedó cumplida |

### Códigos de respuesta

| Código | Significa |
|---|---|
| 200 / 201 / 204 | Todo bien / se creó / sin contenido |
| 401 | Falta el token o ya no sirve |
| 403 | Hay sesión, pero la meta es de otra persona |
| 404 | No existe |
| 422 | Datos inválidos; el detalle viene en `errors` |
| 429 | Demasiados intentos |

### Límites de peticiones

Para que una sola IP o una sola cuenta no acapare el servidor, la API cuenta las peticiones por minuto y responde **429** con el encabezado `Retry-After` cuando alguien se pasa:

| Límite | Por minuto | Se cuenta por | Dónde |
|---|---|---|---|
| Filtro temprano | 300 | IP | `app/Http/Middleware/LimiteTemprano.php`, antes de revisar el token |
| Filtro temprano | 120 | Token | El mismo middleware |
| Con sesión | 120 | Usuario | `throttle:por_usuario` |
| Escrituras | 30 | Usuario | `throttle:escrituras`: crear, cambiar, borrar y abonar |
| Cuentas | 10 | IP y ruta | `throttle:cuentas`: registro, login, contraseñas y foto |

El filtro temprano va antes que todo para que rechazar sea barato: no consulta la base de datos para saber quién es. La prueba `pruebas/carga/ataque.js` (30 conexiones sin pausa con una misma cuenta, más una persona normal) dio estos resultados:

| | Peticiones rechazadas por segundo | Respuesta para la persona normal (95 %) |
|---|---|---|
| Sin límite | 0 (atendía todo) | 2,33 s |
| Límite después de revisar el token | 17,8 | 1,76 s |
| Filtro temprano con contadores en archivos | 34,0 | 1,40 s |

El atacante quedó frenado en 120 peticiones por minuto. Aun así, cada petición rechazada pasa por PHP, así que un ataque grande desde muchos equipos se debe frenar antes de llegar al servidor: Cloudflare (reglas de límite) o Nginx (`limit_req`) al desplegar. Detrás de Cloudflare también hay que configurar los proxies de confianza para que Laravel vea la IP real.

## Base de datos

| Tabla | Guarda |
|---|---|
| `users` | Nombre, correo y contraseña cifrada |
| `perfiles` | Usuario dueño, nombre, ahorro mensual y ruta de la foto |
| `metas` | Producto, precio, moneda, descuento, fecha estimada y estado |
| `abonos` | Monto, fecha y meta a la que pertenecen |

`precio_final`, `ahorrado` y `progreso` no se guardan: el modelo `Meta` los calcula y los agrega al JSON.

## Pruebas

La colección está en `postman/`. Para correrla toda con Newman, con Sail encendido:

```bash
docker run --rm --network alcanza-api_sail -v "$(pwd)/postman:/etc/newman" postman/newman:alpine run alcanza-api.postman_collection.json -e alcanza-api-local.postman_environment.json --env-var host=http://laravel.test
```

Resultado actual: 39 peticiones y 77 comprobaciones, 0 fallidas, en local y contra la imagen de producción. La colección crea usuarios con correos `@alcanza.test`, que se pueden borrar después. Envía como `turnstile` el código de prueba de Cloudflare (`XXXX.DUMMY.TOKEN.XXXX`), que solo aceptan las claves de prueba.

En Postman: **Import** → los dos archivos de `postman/` → elegir el entorno "alcanza-api local" → **Run collection**.

### Prueba de carga (k6)

`pruebas/carga/estres.js` sube de 1 a 50 usuarios virtuales en 2 minutos. Cada uno lista las metas, consulta el perfil y registra un abono cada segundo. La meta es que el 95 % de las peticiones tarde menos de 0,8 s y que fallen menos del 1 %.

```bash
docker run --rm --network alcanza-api_sail -v "$(pwd)/pruebas/carga:/scripts" grafana/k6:0.54.0 run /scripts/estres.js
```

Para medir una cantidad fija de usuarios durante 40 segundos, agrega `-e VUS=25` antes de `grafana/k6`. La prueba crea un usuario `estres-…@alcanza.test` con muchos abonos, que conviene borrar al terminar.

Resultados del 10 de noviembre de 2026, en un computador de 4 núcleos con `php artisan serve`:

| Medición | Antes | Después de corregir |
|---|---|---|
| Peticiones fallidas | 0 % | 0 % |
| 95 % de las peticiones, con 1 a 50 usuarios | 4,12 s | 2,61 s |
| Peticiones por segundo | 16,5 | 22,3 |
| `GET /metas` sola, con 673 abonos | 0,31 s y 98 KB | 0,15 s y 32 KB |

Con usuarios fijos: 10 usuarios, 0,26 s (cumple); 25 usuarios, 0,80 s (en el límite); 50 usuarios, 2,07 s (no cumple). Con 50 usuarios, PHP usa cerca del 330 % de los 400 % de CPU: el límite es el servidor de desarrollo, no la base de datos.

Correcciones hechas:

1. La lista de metas pide solo `id`, `monto` y `fecha` de cada abono (`MetaRepository`).
2. Registrar un abono ya no devuelve la lista completa de abonos (`AbonoService`); el frontend le suma el nuevo.
3. OPcache activo en el contenedor (`docker/php/opcache.ini`, montado en `compose.yaml`).

Al desplegar conviene usar `APP_DEBUG=false` con `php artisan optimize`, que guarda la configuración y las rutas en caché. Con eso, 50 usuarios bajaron a 1,64 s y 36 peticiones por segundo. Para más carga hace falta un servidor de producción (Nginx con PHP-FPM, o Laravel Octane) en lugar de `php artisan serve`.

## Publicar en internet

El `Dockerfile` arma la imagen de producción: PHP 8.4 con Apache y OPcache, sin dependencias de desarrollo. Al arrancar, `docker/produccion/iniciar.sh` usa el puerto que indica el hosting (`PORT`), ejecuta `php artisan optimize` y `php artisan migrate --force`.

Para probarla en local, contra la base de Sail:

```bash
docker build -t alcanza-api-produccion .
docker run --rm --network alcanza-api_sail -e PORT=10000 -e APP_KEY=... -e DB_HOST=mysql -e DB_DATABASE=data_alcanza -e DB_USERNAME=sail -e DB_PASSWORD=... alcanza-api-produccion
```

Variables que hay que poner en el hosting:

| Variable | Valor |
|---|---|
| `APP_KEY` | Generada con `php artisan key:generate --show` |
| `APP_ENV=production`, `APP_DEBUG=false` | Modo producción |
| `APP_URL` | Dirección pública de la API |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Datos de la base en la nube |
| `FRONTEND_URLS`, `FRONTEND_URL` | Dirección pública del frontend |
| `MAIL_*` | Servidor de correo real (Brevo) |
| `TURNSTILE_SECRET_KEY` | Clave secreta real de Turnstile |
| `LIMITE_CABECERA_IP` | Encabezado con la IP real del visitante, según el hosting |
| `LOG_CHANNEL=stderr` | Para ver los errores en el panel del hosting |

Plan elegido (planes gratuitos revisados en noviembre de 2026): **Render** para la API, **TiDB Cloud Starter** para la base (compatible con MySQL; Aiven dejó de crear MySQL gratis el 6 de noviembre de 2026), **Cloudflare Pages** para React y **Brevo** para el correo. Las fotos se guardan en la base de datos porque el disco de Render se borra en cada reinicio.

## Comandos útiles

| Comando | Qué hace |
|---|---|
| `./vendor/bin/sail up -d` | Enciende los contenedores |
| `./vendor/bin/sail down` | Los apaga |
| `./vendor/bin/sail artisan migrate` | Crea las tablas que falten |
| `./vendor/bin/sail artisan route:list --path=api` | Lista las rutas |
| `./vendor/bin/sail mysql` | Abre la consola de MySQL |

## Documentación

La documentación completa está en el repositorio del frontend:

- `ALCANZA_documentacion_backend.pdf`: el backend paso a paso.
- `ALCANZA_guia_sustentacion.pdf`: cada ruta con su controlador, servicio y repositorio, con número de línea.
