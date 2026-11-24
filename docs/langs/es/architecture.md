# Arquitectura

`mvc-v1` es una aplicación educativa PHP del lado del servidor con un pequeño núcleo MVC propio. La arquitectura se mantiene deliberadamente directa: el router selecciona el escenario, el controlador coordina la petición, el modelo trabaja con los datos, la vista construye la página y la respuesta HTTP final se envía desde un único punto.

El proyecto no pretende ser un framework de propósito general ni reproducir todas las funcionalidades de Symfony, Laravel u otras soluciones de producción.

## Flujo general de la petición

```text
Petición HTTP
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
método del controlador
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
código de estado + cabeceras + cuerpo
```

El script de entrada [`public/index.php`](../../../public/index.php) carga la configuración y las rutas, normaliza la URI, obtiene un `Track` de `Router` y lo entrega a `Dispatcher`.

Si el controlador devuelve una `Page`, se entrega a `View` para generar HTML. Si ya ha devuelto un `Response` o `RedirectResponse`, no se invoca la vista. En ambos casos, el script de entrada recibe un `Response` listo y solo entonces envía el código de estado, las cabeceras y el cuerpo.

## Router y Track

[`Router`](../../../core/src/Router.php) compara la ruta de la petición con las rutas de [`config/routes.php`](../../../config/routes.php).

Al analizar la URI:

- los parámetros posteriores a `?` no intervienen en la selección de la ruta;
- los segmentos con nombre de la ruta se convierten en parámetros;
- el nombre del controlador, el nombre del método y los parámetros se agrupan en un `Track`;
- si no se encuentra ninguna ruta, se crea un `Track` para `ErrorController::notFound()`.

`Router` no crea controladores ni envía nada directamente al cliente.

## Dispatcher

[`Dispatcher`](../../../core/src/Dispatcher.php) se encarga de pasar de un `Track` al método correspondiente del controlador.

Sus tareas son:

1. comprueba el formato permitido del nombre del controlador y del método;
2. comprueba `APP_NAMESPACE`;
3. incluye el archivo del controlador;
4. verifica que la clase y el método existan y sean accesibles;
5. invoca el método con los parámetros de la ruta;
6. acepta únicamente `Page` o `Response`.

Un resultado HTTP normal no se representa mediante una excepción. El controlador devuelve una página, una respuesta normal o un `RedirectResponse`.

`Dispatcher` registra los errores al incluir el controlador, los métodos inaccesibles y otros fallos reales en `logs/dispatcher-errors.txt` y devuelve al usuario un `Response` seguro con estado `500`.

## Page, Response y RedirectResponse

[`Page`](../../../core/src/Page.php) describe una página que aún debe renderizarse. Contiene:

- el nombre del layout;
- metadatos;
- el nombre de la vista;
- los datos de la vista;
- el código de estado HTTP de la página resultante.

Por ejemplo, `ErrorController` devuelve una `Page` normal con estado `404` sin cambiar el código de respuesta global desde el controlador.

[`Response`](../../../core/src/Response.php) representa una respuesta HTTP completa: cuerpo, código de estado y conjunto de cabeceras. La clase comprueba el rango válido de códigos de estado y valida las cabeceras, incluida la protección frente a caracteres de control y nombres duplicados sin distinguir mayúsculas de minúsculas.

[`RedirectResponse`](../../../core/src/RedirectResponse.php) especializa `Response` para las redirecciones: contiene la cabecera `Location`, un cuerpo vacío y permite los códigos `301`, `302`, `303`, `307` y `308`.

Por tanto, las redirecciones, `403 Forbidden` y `405 Method Not Allowed` siguen siendo resultados normales de la ejecución del método; las excepciones se reservan para fallos reales.

## Controladores

El [`Controller`](../../../core/src/Controller.php) base facilita la creación de `Page` mediante `render()` y contiene la lógica común de paginación.

El ejemplo principal está en [`ArticleController`](../../../app/Controllers/ArticleController.php):

- `all()` y `show()` devuelven una `Page`;
- `create()`, `edit()` y `delete()` devuelven `Page|Response`;
- las operaciones POST correctas devuelven un `RedirectResponse`;
- un token CSRF no válido devuelve `Response('Forbidden', 403)`;
- intentar eliminar un artículo con un método distinto de POST devuelve `405` con la cabecera `Allow: POST`;
- si el artículo no existe, se devuelve una `Page` con estado `404`.

El controlador no envía directamente cabeceras HTTP ni termina el proceso PHP mediante `exit`.

## Modelo y PostgreSQL

El [`Model`](../../../core/src/Model.php) base abre una conexión PDO con PostgreSQL a partir de las variables de entorno.

La conexión se configura así:

- los errores PDO se convierten en excepciones;
- los resultados se devuelven como arrays asociativos de forma predeterminada;
- la emulación de sentencias preparadas está desactivada.

Las consultas se ejecutan mediante sentencias preparadas de PDO. El CRUD de artículos se implementa en [`ArticleModal`](../../../app/Models/ArticleModal.php).

Ante una `PDOException`, la información técnica se registra en `logs/database-errors.txt` y después se lanza una `DatabaseException` que conserva la excepción original. `Dispatcher` sabe que el error de base de datos ya se ha registrado, por lo que no lo registra de nuevo y devuelve al usuario una respuesta segura `500`.

## View y envío de la respuesta

[`View`](../../../core/src/View.php) recibe una `Page`, renderiza la vista y el layout mediante el búfer de salida y devuelve un `Response` listo.

Si se produce un error al generar la página:

- `View` limpia únicamente los búferes de salida que ha abierto;
- se registra la causa técnica;
- el usuario recibe una respuesta segura `500`.

El envío normal de la respuesta HTTP se realiza únicamente en [`public/index.php`](../../../public/index.php):

```text
http_response_code()
→ header()
→ echo
```

Es decir, la construcción de la respuesta está separada de su envío efectivo al cliente.

## Validación, CSRF y salida de datos

[`ArticleValidate`](../../../app/Validations/ArticleValidate.php) valida los datos del artículo en el servidor. Si falla la validación, el controlador guarda los valores introducidos y los mensajes en la sesión y después devuelve una redirección.

[`SecurityHelper`](../../../app/Helper/SecurityHelper.php) crea y valida el token CSRF. Las operaciones de modificación `create`, `edit` y `delete` realizan esta comprobación antes de escribir en PostgreSQL.

Los valores dinámicos se escapan al mostrarse. El contenido de los artículos se almacena como texto: los saltos de línea se convierten al visualizarlo y no se ejecuta HTML arbitrario del usuario.

## Límites de gestión de errores

El proyecto distingue las respuestas HTTP normales de los errores reales:

```text
redirección / 403 / 405
    → Response normal
    → sin entrada en el registro de errores

error de PostgreSQL / PDO
    → entrada en el registro de la base de datos
    → DatabaseException
    → respuesta segura 500

error del controlador o de Dispatcher
    → entrada en el registro de Dispatcher
    → respuesta segura 500

error de la vista o del layout
    → entrada en el registro de View
    → respuesta segura 500
```

Los mensajes técnicos de las excepciones no deben aparecer en la respuesta HTML enviada al usuario.

## Qué se mantiene simple a propósito

El proyecto sigue siendo pequeño, por lo que no incluye capas adicionales únicamente por simetría arquitectónica:

- sin contenedor de dependencias;
- sin ORM;
- sin una capa de repositorios independiente sobre cada modelo;
- sin cadena de middleware;
- sin bus de eventos;
- sin tareas en segundo plano;
- sin REST API ni SPA independientes.

En una aplicación de mayor tamaño, algunos de estos límites podrían resultar útiles. Aquí solo ocultarían el flujo MVC principal tras infraestructura adicional.

Para los comandos prácticos de arranque, la base de datos de pruebas, la cobertura y CI, consulta la [guía de desarrollo](development.md).
