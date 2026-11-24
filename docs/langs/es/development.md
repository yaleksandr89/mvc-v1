# Desarrollo

## Requisitos

Para el escenario recomendado con Docker Compose, el host necesita:

- Git;
- Make;
- Docker con soporte de Compose.

PHP, Composer, PHPUnit, PHPStan y PHP CS Fixer se ejecutan dentro del contenedor PHP. No hace falta instalarlos por separado en el host.

Docker no es obligatorio: el proyecto también puede ejecutarse en un entorno convencional con PHP, Composer, PostgreSQL y un servidor web. Esta opción se describe por separado en [Ejecución sin Docker](#ejecución-sin-docker).

## Primer arranque

| Comando | Qué hace | Notas |
|---|---|---|
| `make init` | Crea `.env.docker`, `logs/` y `runtime/cache/` | Crea `.env.docker` solo si no existe |
| `make build` | Construye la imagen Docker de PHP | |
| `make up` | Inicia Nginx, PHP-FPM y PostgreSQL | Espera a que los servicios estén listos |
| `make composer-install` | Instala las dependencias desde `composer.lock` | Se ejecuta dentro del contenedor PHP |
| `make demo-data` | Carga 50 artículos de demostración | Solo en una tabla `blog_posts` vacía |
| `make db-check` | Muestra la versión de PostgreSQL y el estado de `blog_posts` | Comprobación útil tras el primer arranque |

Tras el arranque, la aplicación está disponible de forma predeterminada en `http://localhost:8080`.

Para detener el entorno, utiliza `make down`: los contenedores se detienen y el volumen de PostgreSQL se conserva.

## Docker Compose

[`compose.yaml`](../../../compose.yaml) inicia tres servicios:

- `php` — PHP 8.5 FPM y herramientas de desarrollo;
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

El contenedor PHP se construye a partir de [`docker/php/Dockerfile`](../../../docker/php/Dockerfile). El usuario `app` recibe el UID/GID del usuario del host, por lo que `vendor`, los informes y otros archivos creados dentro del contenedor no deberían quedar bajo la propiedad de `root`.

La lista completa de comandos para gestionar el entorno está en [Todos los comandos Make](#todos-los-comandos-make).

## Variables de entorno

Docker utiliza `.env.docker`, creado a partir de [`.env.docker.example`](../../../.env.docker.example).

Grupos principales de configuración:

- `HOST_UID`, `HOST_GID` — UID/GID del usuario del host;
- `APP_PORT` — puerto HTTP externo, `8080` por defecto;
- `DB_FORWARD_PORT` — puerto de PostgreSQL en el host;
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — parámetros de PostgreSQL local;
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — configuración de la aplicación;
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — conexión de la aplicación a la base de datos.

Los valores del archivo de ejemplo son solo para desarrollo local. No añadas secretos reales a Git.

Para el arranque manual sin Docker, crea un `.env` en la raíz a partir de [`.env.example`](../../../.env.example). La aplicación carga este archivo por sí misma, sin una biblioteca dotenv externa. Las variables de entorno del proceso ya definidas y los valores de `$_ENV` tienen prioridad y no se sobrescriben con los de `.env`.

El `.env` de la raíz utiliza el formato simple `KEY=VALUE`; el proyecto no usa la sintaxis ampliada de las bibliotecas dotenv.

## PostgreSQL y datos de demostración

En el primer arranque de un nuevo volumen de PostgreSQL, el esquema se crea a partir de [`docs/db_schema_only.sql`](../../db_schema_only.sql). Para la demostración, puedes cargar 50 artículos desde [`docs/db_demo_data.sql`](../../db_demo_data.sql).

| Comando | Qué hace | Notas |
|---|---|---|
| `make db-check` | Comprueba la versión de PostgreSQL y el estado de `blog_posts` | |
| `make demo-data` | Carga 50 artículos de demostración | Solo funciona con una tabla `blog_posts` vacía |
| `make postgres-reinit CONFIRM=postgres18` | Recrea por completo el volumen local de PostgreSQL | Elimina los datos locales actuales |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recrea PostgreSQL y carga inmediatamente 50 artículos | Elimina los datos locales actuales |

> [!IMPORTANT]
> `make demo-data` no sobrescribe artículos existentes. Si la tabla ya contiene datos, el comando termina con un error sin modificarlos. El reinicio completo solo está disponible mediante `postgres-reinit` con confirmación explícita.

## Base de datos de pruebas separada

PHPUnit no trabaja con la base principal `mvc_v1`. Las pruebas utilizan la base separada `mvc_v1_test`.

| Comando | Qué hace | Notas |
|---|---|---|
| `make test-db-create` | Crea `mvc_v1_test` si aún no existe | No elimina la base existente |
| `make test-db-reset` | Recrea el esquema y carga tres artículos de prueba | Se utiliza automáticamente antes de las pruebas |
| `make test-db-check` | Comprueba el nombre de la base y el estado de los datos de prueba | Se esperan 3 artículos con ID del 1 al 3 |
| `make test-db-drop CONFIRM=mvc_v1_test` | Elimina únicamente `mvc_v1_test` | Requiere confirmación explícita |

`make test`, `make test-dox`, `make coverage`, `make coverage-html` y `make check` reinician por sí mismos la base de datos de pruebas antes de ejecutarse.

## Pruebas, análisis y cobertura

| Comando | Qué hace | Notas |
|---|---|---|
| `make test` | Ejecuta PHPUnit | |
| `make test-dox` | Ejecuta PHPUnit con nombres de escenarios legibles | |
| `make cs-check` | Comprueba el estilo PHP y muestra un diff si hay infracciones | No modifica los archivos fuente ni reinicia la base de pruebas |
| `make cs-fix` | Formatea el código PHP | Modifica los archivos fuente; no reinicia la base de pruebas |
| `make analyse` | Ejecuta PHPStan | |
| `make check` | Ejecuta la validación de Composer, la auditoría de Composer, la comprobación de estilo con PHP CS Fixer, PHPStan y PHPUnit | Comprobación integral principal; reinicia antes la base de pruebas |
| `make coverage` | Muestra la cobertura y crea Clover XML | `runtime/coverage.xml` |
| `make coverage-html` | Crea un informe HTML de cobertura | `runtime/coverage/index.html` |
| `make smoke` | Ejecuta una comprobación HTTP de extremo a extremo | Requiere 50 artículos de demostración |

PHP CS Fixer está instalado como dependencia de desarrollo. Las reglas y el ámbito de formato se definen en [`.php-cs-fixer.dist.php`](../../../.php-cs-fixer.dist.php): código PHP en `app/`, `config/`, `core/src/`, `tests/`, `views/`, el archivo `public/index.php` y el propio archivo de configuración. Las reglas arriesgadas están desactivadas; los recursos frontend de terceros no se formatean.

El estándar base de revisión es PER Coding Style 3.1 y PSR-1; el formateador aplica el conjunto de reglas configurado `@PER-CS` y reglas adicionales. La excepción acordada para las plantillas mixtas PHP/HTML y los demás requisitos se describen en [`AGENTS.md`](../../../AGENTS.md).

Sin Docker, utiliza `composer cs:check` para comprobar el estilo y `composer cs:fix` para corregirlo. Con Make, estos comandos se ejecutan en el contenedor PHP como usuario `app`. La caché del formateador se guarda en `runtime/cache/php-cs-fixer.cache` y no se incluye en Git. Los comandos independientes de comprobación y formato no requieren conexión a la base de datos.

La cobertura se utiliza como herramienta de diagnóstico, no como indicador público de calidad. CI envía Clover a Codecov mediante OIDC. No hay un requisito independiente de alcanzar el 100%: las pruebas deben proteger el comportamiento real del proyecto, no ejecutar artificialmente ramas inalcanzables por un porcentaje.

## Comprobación HTTP de extremo a extremo

`make smoke` ejecuta un escenario HTTP real a través del entorno Docker en funcionamiento y comprueba:

- `200` para páginas normales;
- `403` ante un token CSRF no válido;
- redirecciones `302` y la cabecera `Location`;
- `405 Method Not Allowed` y la cabecera `Allow: POST`;
- `404` después de eliminar un artículo;
- la restauración del número original de filas de demostración.

Esta comprobación complementa PHPUnit: las pruebas verifican el código y la integración dentro del proceso PHP, mientras que `make smoke` recorre todo el camino Nginx → PHP-FPM → aplicación → PostgreSQL.

## GitHub Actions

El workflow [`.github/workflows/ci.yml`](../../../.github/workflows/ci.yml) se ejecuta para pull requests a `master`, al hacer push a `master` y manualmente mediante Actions → CI → Run workflow.

En un entorno limpio de GitHub Actions:

1. crea el entorno local;
2. comprueba la configuración de Docker Compose;
3. construye e inicia los contenedores;
4. instala las dependencias desde `composer.lock`;
5. valida el archivo Composer del núcleo MVC integrado;
6. comprueba la sintaxis PHP;
7. ejecuta `make check`: validación estricta de Composer, auditoría de dependencias, comprobación de estilo, PHPStan y PHPUnit;
8. crea un informe de cobertura y envía Clover a Codecov;
9. carga los datos de demostración;
10. comprueba PostgreSQL;
11. ejecuta `make smoke`;
12. elimina la base de datos de pruebas y detiene los contenedores.

Este workflow no despliega la aplicación.

## Todos los comandos Make

`make` sin argumentos muestra la ayuda integrada. A continuación se enumeran todos los comandos definidos por el [`Makefile`](../../../Makefile) actual.

| Comando | Qué hace | Notas |
|---|---|---|
| `make` / `make help` | Muestra la ayuda de los comandos | |
| `make init` | Crea `.env.docker`, `logs/` y `runtime/cache/` | No sobrescribe un `.env.docker` existente |
| `make check-env` | Comprueba la presencia de `.env.docker` y Docker Compose | |
| `make config` | Comprueba la configuración efectiva de Docker Compose | |
| `make build` | Construye la imagen Docker de PHP | |
| `make up` | Inicia el entorno y espera a que los servicios estén listos | |
| `make down` | Detiene el entorno | Conserva el volumen de PostgreSQL |
| `make restart <service>` | Reinicia el servicio seleccionado | `php`, `nginx` o `postgres` |
| `make ps` | Muestra el estado de los contenedores del proyecto | |
| `make log <service>` | Muestra el registro del servicio seleccionado en tiempo real | `php`, `nginx` o `postgres` |
| `make log-all` | Muestra los registros de todos los servicios en tiempo real | |
| `make in <service>` | Abre una shell en el contenedor seleccionado | `php`, `nginx` o `postgres` |
| `make php CMD="..."` | Ejecuta un comando PHP arbitrario como usuario `app` | Por ejemplo: `make php CMD="-v"` |
| `make composer CMD="..."` | Ejecuta un comando Composer arbitrario | Por ejemplo: `make composer CMD="validate"` |
| `make composer-install` | Instala las dependencias desde `composer.lock` | |
| `make test` | Ejecuta PHPUnit | Reinicia antes la base de pruebas |
| `make test-dox` | Ejecuta PHPUnit en formato TestDox | Reinicia antes la base de pruebas |
| `make coverage` | Muestra la cobertura y crea Clover XML | Reinicia antes la base de pruebas |
| `make coverage-html` | Crea un informe HTML de cobertura | Reinicia antes la base de pruebas |
| `make cs-check` | Comprueba el estilo PHP y muestra un diff si hay infracciones | No modifica los archivos fuente ni reinicia la base de pruebas |
| `make cs-fix` | Formatea el código PHP | Modifica los archivos fuente; no reinicia la base de pruebas |
| `make analyse` | Ejecuta PHPStan | |
| `make check` | Ejecuta la validación de Composer, la auditoría de Composer, la comprobación de estilo con PHP CS Fixer, PHPStan y PHPUnit | Reinicia antes la base de pruebas |
| `make smoke` | Ejecuta una comprobación CRUD/CSRF de extremo a extremo mediante HTTP | La base principal debe contener 50 artículos de demostración |
| `make db-check` | Muestra la versión de PostgreSQL y el estado de `blog_posts` | |
| `make demo-data` | Carga 50 artículos de demostración | Solo en una tabla `blog_posts` vacía |
| `make test-db-create` | Crea `mvc_v1_test` si no existe | |
| `make test-db-reset` | Recrea el esquema y los datos de prueba | Elimina solo el esquema dentro de `mvc_v1_test` |
| `make test-db-check` | Comprueba la base de pruebas | |
| `make test-db-drop CONFIRM=mvc_v1_test` | Elimina `mvc_v1_test` | Requiere confirmación exacta |
| `make postgres-reinit CONFIRM=postgres18` | Recrea el volumen local de PostgreSQL | Elimina los datos locales de la base principal |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recrea PostgreSQL y carga 50 artículos | Elimina los datos locales de la base principal |

## Permisos de archivos

Los comandos PHP y Composer que crean archivos se ejecutan en el contenedor como usuario `app`, vinculado al UID/GID del usuario del host mediante `.env.docker`.

Por tanto, el trabajo habitual mediante Make no debería crear archivos propiedad de `root` en la copia de trabajo. No se necesitan operaciones masivas de `chmod`, `chown` ni eliminación de directorios para el ciclo normal de desarrollo.

## Ejecución sin Docker

Docker Compose sigue siendo la forma recomendada y más reproducible de ejecutar el proyecto. Pero si Docker no resulta adecuado por algún motivo, también puedes arrancar el proyecto manualmente.

Aquí no se incluye un tratado aparte sobre la instalación de PHP-FPM y PostgreSQL ni sobre la configuración de Nginx: si has elegido la vía manual, creo que podrás encargarte de la configuración básica de estos servicios. Para no empezar completamente desde cero, el repositorio incluye un ejemplo de configuración de Nginx y la configuración de Docker muestra las extensiones PHP y los parámetros de entorno utilizados.

Necesitarás:

- PHP 8.5 con `mbstring`, `pdo` y `pdo_pgsql`;
- Composer;
- PostgreSQL;
- Nginx u otro servidor web con el directorio raíz apuntando a `public/`.

Configura el `.env` de la raíz según la plantilla [`.env.example`](../../../.env.example). Si es necesario, puedes definir valores mediante el entorno del sistema o PHP-FPM: tienen prioridad sobre `.env`.

El esquema y los datos de demostración están en:

- [`docs/db_schema_only.sql`](../../db_schema_only.sql);
- [`docs/db_demo_data.sql`](../../db_demo_data.sql).

Ejemplo de configuración mínima de Nginx + PHP-FPM: [`docs/conf/nginx-configuration.conf`](../../conf/nginx-configuration.conf). Antes de utilizarla, adapta `server_name`, `root` y `fastcgi_pass` a tu sistema.

Este ejemplo no es suficiente para un despliegue en producción: TLS, gestión de procesos, secretos, copias de seguridad y políticas operativas quedan fuera del alcance del proyecto educativo.
