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
| Pruebas | Postman y Newman |

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

La unión entre cada contrato y su repositorio está en `app/Providers/AppServiceProvider.php`.

## Instalación

Desde una terminal de Ubuntu, en la carpeta del proyecto:

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
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

## Rutas

Todas empiezan por `/api`. Las protegidas necesitan el encabezado `Authorization: Bearer <token>`.

### Públicas (máximo 10 intentos por minuto)

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

Resultado actual: 37 peticiones y 72 comprobaciones, 0 fallidas. La colección crea usuarios con correos `@alcanza.test`, que se pueden borrar después.

En Postman: **Import** → los dos archivos de `postman/` → elegir el entorno "alcanza-api local" → **Run collection**.

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
