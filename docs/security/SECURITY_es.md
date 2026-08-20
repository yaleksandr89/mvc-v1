# Seguridad

## Elija un idioma

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/SECURITY.md) | [English](SECURITY_en.md) | **Español** | [中文](SECURITY_zh.md) | [Français](SECURITY_fr.md) | [Deutsch](SECURITY_de.md) |

Informe las posibles vulnerabilidades de forma responsable. `mvc-v1` es un proyecto MVC educativo, pero los problemas de seguridad en routing, CRUD, acceso a PostgreSQL, tratamiento de entrada de usuario y representación de datos se toman tan en serio como en cualquier repositorio público.

## Qué es mejor informar en privado

- Una omisión de protección CSRF al crear, editar o eliminar un artículo.
- Inyección SQL o posibilidad de influir en identificadores SQL o parámetros de consulta.
- XSS almacenado o reflejado, omisión del escape HTML o salida insegura de datos de usuario.
- Posibilidad de sustituir un parámetro route/path mediante query string o actuar sobre otra entidad.
- Revelación de rutas internas, excepciones, configuración, contenido de `.env`, credenciales PostgreSQL u otra información sensible.
- Un fallo en sesión, token CSRF o método HTTP que permita un cambio de estado no deseado.
- Un problema explotable de dependencia que afecte sustancialmente al proyecto.
- Compromiso del código fuente, CI, cadena de dependencias u otro elemento de supply chain.

## Qué puede publicarse en Issues

- Un error de routing sin impacto de seguridad.
- Comportamiento CRUD o validación incorrectos sin impacto de seguridad.
- Un problema de paginación, representación o interfaz.
- Un problema de compatibilidad PHP o PostgreSQL sin revelar datos sensibles.
- Un error de documentación.
- Una solicitud de nueva función o mejora.

Si no está seguro de que un problema afecte la seguridad, use primero el canal privado.

## Cómo informar

- Si la sección Security del repositorio ofrece un formulario privado, úselo primero.
- No publique código de exploit, secretos reales, contenido `.env` de producción, contraseñas PostgreSQL, identificadores de sesión u otros datos sensibles en Issues, Discussions, Pull Requests o logs.
- Si el formulario privado no está disponible temporalmente, cree un Issue público mínimo sin detalles de explotación y solicite un canal privado.
- No revele detalles técnicos de explotación públicamente hasta acordarlo con el mantenedor y que exista una corrección.

## Qué incluir

Cuando sea posible, indique:

- la versión afectada o SHA del commit;
- la versión de PHP;
- la versión de PostgreSQL si el problema se relaciona con la base de datos;
- el área afectada: routing, controller, model/PDO, validation, view/output, session/CSRF, configuration o dependency chain;
- el impacto;
- reproducción mínima;
- fragmento saneado de solicitud, respuesta o log cuando sea útil.

Use solamente datos sintéticos. Nunca incluya contraseñas reales, tokens, cookies, IDs de sesión, contenido `.env` de producción u otros secretos.

## Qué sucede después

- El proyecto tiene un solo mantenedor; no existe SLA garantizado.
- El mantenedor intentará confirmar el informe, reproducir el problema, evaluar el impacto y preparar una corrección.
- No se promete programa de recompensas por errores.
- Coordine la divulgación pública hasta que exista una corrección.
