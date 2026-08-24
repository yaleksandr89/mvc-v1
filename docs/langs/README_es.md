# MVC en PHP

[![Source Code](https://img.shields.io/badge/source-yaleksandr89%2Fmvc--v1-blue.svg?style=flat-square)](https://github.com/yaleksandr89/mvc-v1)
[![CI](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml/badge.svg)](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.4-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](../../LICENSE.md)

<p align="center">
  <img
    src="../img/mvc-v1-readme-cover.png"
    alt="MVC en PHP — blog educativo con un núcleo MVC propio, PostgreSQL y Docker Compose"
    width="100%"
  >
</p>

## Elige un idioma

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../README.md) | [English](./README_en.md) | **Seleccionado** | [中文](./README_zh.md) | [Français](./README_fr.md) | [Deutsch](./README_de.md) |

Este es uno de mis primeros proyectos con una implementación propia de MVC, sin apoyarse en un framework ya preparado. El ejemplo es un blog sencillo de artículos: enrutamiento, controladores, modelos, vistas y CRUD están organizados de forma que todo el recorrido de una petición pueda seguirse directamente en el código del proyecto.

El proyecto evita deliberadamente convertirse en una colección de bibliotecas ya hechas. PostgreSQL se utiliza directamente mediante PDO, sin ORM. El código de producción no depende de bibliotecas PHP de terceros: el núcleo MVC propio se conecta como el paquete local de Composer `yaa/mvc-framework`, y la aplicación se apoya en PHP y en las extensiones obligatorias `mbstring`, `PDO` y `pdo_pgsql`.

> [!NOTE]
> **ACTUALIZACIÓN 2026.** La idea original no ha cambiado: sigue siendo el mismo proyecto MVC pequeño, ahora actualizado para PHP 8.5, versiones actuales de PostgreSQL y Bootstrap y un tratamiento más seguro de las peticiones que modifican datos. Docker Compose ofrece un entorno local reproducible sin instalar PHP ni Composer en el host, pero el arranque manual sin Docker sigue estando soportado.

## Funcionalidades

- crear, ver, editar y eliminar artículos;
- PostgreSQL mediante PDO y consultas parametrizadas;
- paginación de la lista de artículos;
- validación del lado del servidor conservando los valores introducidos cuando hay errores;
- protección CSRF en las peticiones que modifican datos;
- mensajes después de las operaciones CRUD;
- escape de la salida dinámica;
- `Page`, `Response` y `RedirectResponse` tipados;
- Docker Compose con Nginx, PHP-FPM y PostgreSQL.

## Inicio rápido

Se necesitan Git, Make y Docker con soporte para Compose.

| Comando | Qué hace | Nota |
|---|---|---|
| `git clone https://github.com/yaleksandr89/mvc-v1.git` | Clona el repositorio | |
| `cd mvc-v1` | Entra en el directorio del proyecto | |
| `make init` | Crea `.env.docker` y los directorios de trabajo | Normalmente se ejecuta una vez después de clonar |
| `make build` | Construye la imagen Docker de PHP | |
| `make up` | Inicia Nginx, PHP-FPM y PostgreSQL | |
| `make composer-install` | Instala las dependencias desde `composer.lock` | PHP y Composer no son necesarios en el host |
| `make demo-data` | Carga 50 artículos de demostración | Solo en una tabla `blog_posts` vacía |
| `make down` | Detiene el entorno | Los datos de PostgreSQL se conservan |

Después del arranque, la aplicación está disponible en [http://localhost:8080](http://localhost:8080).

> [!IMPORTANT]
> `make demo-data` solo funciona con una tabla `blog_posts` vacía. Si ya contiene datos, el comando se detiene sin sobrescribir nada. Para recrear por completo el almacenamiento local de PostgreSQL y cargar inmediatamente los datos de demostración, utiliza `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1`; este comando elimina el volumen local de PostgreSQL.

Los detalles del entorno, la base de datos de pruebas aislada, el resto de comandos y el arranque manual sin Docker están descritos en la [guía de desarrollo](../development.md).

## Cómo funciona la aplicación

```text
Petición HTTP
    ↓
Router
    ↓
Dispatcher
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php → código de estado + cabeceras + cuerpo
```

`Page` describe una página que todavía debe renderizarse, mientras que `Response` y `RedirectResponse` representan respuestas HTTP ya completas. El código de estado, las cabeceras y el cuerpo se envían únicamente desde el punto de entrada `public/index.php`.

El modelo de artículos trabaja con PostgreSQL directamente mediante PDO. Los fallos reales permanecen como excepciones dentro de la aplicación, se registran en la capa correspondiente y se convierten en una respuesta segura `500` sin mostrar detalles técnicos al usuario.

El enrutamiento, los límites de responsabilidad y el recorrido completo de la petición se explican en la [guía de arquitectura](../architecture.md).

## Comprobaciones y cobertura

| Comando | Qué hace | Nota |
|---|---|---|
| `make test` | Ejecuta PHPUnit | Antes reinicia la base de datos de pruebas aislada |
| `make test-dox` | Ejecuta PHPUnit con nombres de escenarios legibles | |
| `make check` | Comprueba Composer, dependencias, PHPStan y PHPUnit | Comprobación integral principal |
| `make coverage` | Muestra la cobertura y crea Clover XML | Resultado: `runtime/coverage.xml` |
| `make coverage-html` | Crea un informe HTML de cobertura | Resultado: `runtime/coverage/index.html` |
| `make smoke` | Comprueba la aplicación mediante peticiones HTTP reales | Requiere 50 artículos de demostración |

Las pruebas utilizan la base de datos independiente `mvc_v1_test`, por lo que la base principal de desarrollo no se modifica durante su ejecución.

La lista completa de comandos Make y la configuración de CI están descritas en la [guía de desarrollo](../development.md).

## Qué se mantiene simple a propósito

- el núcleo MVC sigue siendo pequeño para que el recorrido principal de una petición pueda seguirse directamente en el código;
- no se añadió un contenedor de inyección de dependencias independiente;
- no se añadieron ORM ni una capa adicional de repositorios; el acceso a PostgreSQL se realiza mediante PDO;
- no se introdujeron una cadena de middleware ni un sistema de eventos.

Estas decisiones y los límites de responsabilidad se explican con más detalle en la [guía de arquitectura](../architecture.md).

## Comentarios y soporte

- errores reproducibles → [GitHub Issues](https://github.com/yaleksandr89/mvc-v1/issues);
- preguntas e ideas → [GitHub Discussions](https://github.com/yaleksandr89/mvc-v1/discussions).

---

<p align="center">
  Si el proyecto te resultó útil, dale una estrella en GitHub: así será más fácil que otros desarrolladores lo encuentren. 🤘
</p>
