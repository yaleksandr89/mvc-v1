# 架构

`mvc-v1` 是一个带有小型自研 MVC 核心的教学型服务端 PHP 应用。架构有意保持直接：路由器选择处理场景，控制器协调请求，模型处理数据，视图生成页面，最终的 HTTP 响应在统一入口发送。

本项目并非通用框架，也不试图复现 Symfony、Laravel 或其他工业级方案的全部功能。

## 整体请求流程

```text
HTTP 请求
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
控制器方法
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
状态码 + 响应头 + 响应体
```

入口脚本 [`public/index.php`](../../../public/index.php) 加载配置和路由，规范化 URI，从 `Router` 获取 `Track` 并将其传递给 `Dispatcher`。

如果控制器返回 `Page`，则将其交给 `View` 生成 HTML。如果控制器已经返回 `Response` 或 `RedirectResponse`，则不调用视图。两种情况下，入口脚本都先取得完整的 `Response`，然后才发送状态码、响应头和响应体。

## Router 与 Track

[`Router`](../../../core/src/Router.php) 将请求路径与 [`config/routes.php`](../../../config/routes.php) 中的路由进行匹配。

解析 URI 时：

- `?` 后的参数不参与路由选择；
- 命名路由片段转换为参数；
- 控制器名、方法名和参数封装到 `Track` 中；
- 如果找不到路由，则创建指向 `ErrorController::notFound()` 的 `Track`。

`Router` 不创建控制器，也不自行向客户端发送任何内容。

## Dispatcher

[`Dispatcher`](../../../core/src/Dispatcher.php) 负责从 `Track` 转到对应的控制器方法。

它执行以下操作：

1. 检查控制器名和方法名的合法格式；
2. 检查 `APP_NAMESPACE`；
3. 引入控制器文件；
4. 确认类和方法存在且可访问；
5. 使用路由参数调用方法；
6. 只接受 `Page` 或 `Response`。

正常 HTTP 结果不使用异常表示。控制器返回页面、普通响应或 `RedirectResponse`。

`Dispatcher` 将控制器文件引入错误、方法不可访问及其他真实故障记录到 `logs/dispatcher-errors.txt`，并向用户返回状态为 `500` 的安全 `Response`。

## Page、Response 与 RedirectResponse

[`Page`](../../../core/src/Page.php) 描述尚待渲染的页面。它包含：

- 布局名称；
- 元数据；
- 视图名称；
- 视图数据；
- 最终页面的 HTTP 状态码。

例如，`ErrorController` 返回状态为 `404` 的普通 `Page`，而不在控制器中修改全局响应状态码。

[`Response`](../../../core/src/Response.php) 表示完整的 HTTP 响应：响应体、状态码和一组响应头。该类检查状态码的合法范围并验证响应头，包括防止控制字符和不区分大小写的重复名称。

[`RedirectResponse`](../../../core/src/RedirectResponse.php) 是用于重定向的 `Response` 特化：包含 `Location` 响应头、空响应体，并允许状态码 `301`、`302`、`303`、`307` 和 `308`。

因此，重定向、`403 Forbidden` 和 `405 Method Not Allowed` 仍是方法执行的正常结果，异常只用于真实故障。

## 控制器

基础 [`Controller`](../../../core/src/Controller.php) 通过 `render()` 帮助创建 `Page`，并包含通用分页逻辑。

主要示例位于 [`ArticleController`](../../../app/Controllers/ArticleController.php)：

- `all()` 和 `show()` 返回 `Page`；
- `create()`、`edit()` 和 `delete()` 返回 `Page|Response`；
- 成功的 POST 操作返回 `RedirectResponse`；
- 无效的 CSRF 令牌导致返回 `Response('Forbidden', 403)`；
- 使用非 POST 方法尝试删除文章会返回 `405`，并带有 `Allow: POST` 响应头；
- 文章不存在时返回状态为 `404` 的 `Page`。

控制器不直接发送 HTTP 响应头，也不通过 `exit` 终止 PHP 进程。

## 模型与 PostgreSQL

基础 [`Model`](../../../core/src/Model.php) 根据环境变量建立到 PostgreSQL 的 PDO 连接。

连接配置如下：

- PDO 错误转换为异常；
- 默认以关联数组返回结果；
- 禁用预处理语句模拟。

查询通过 PDO 预处理语句执行。文章 CRUD 在 [`ArticleModal`](../../../app/Models/ArticleModal.php) 中实现。

发生 `PDOException` 时，技术信息记录到 `logs/database-errors.txt`，随后抛出保留原始异常的 `DatabaseException`。`Dispatcher` 知道数据库错误已经记录，因此不会重复记录，而是向用户返回安全的 `500` 响应。

## View 与响应发送

[`View`](../../../core/src/View.php) 接收 `Page`，通过输出缓冲渲染视图和布局，然后返回完整的 `Response`。

生成页面期间发生错误时：

- `View` 只清理由自己开启的输出缓冲区；
- 记录技术原因；
- 向用户返回安全的 `500` 响应。

正常 HTTP 响应仅在 [`public/index.php`](../../../public/index.php) 中发送：

```text
http_response_code()
→ header()
→ echo
```

也就是说，响应的构建与实际向客户端发送响应相互分离。

## 验证、CSRF 与数据输出

[`ArticleValidate`](../../../app/Validations/ArticleValidate.php) 在服务端验证文章数据。验证失败时，控制器将输入值和消息保存在会话中，然后返回重定向。

[`SecurityHelper`](../../../app/Helper/SecurityHelper.php) 创建并验证 CSRF 令牌。修改操作 `create`、`edit` 和 `delete` 在写入 PostgreSQL 之前执行此检查。

动态值在输出时进行转义。文章内容以文本存储：显示时转换换行符，不执行用户提供的任意 HTML。

## 错误处理边界

项目区分正常 HTTP 响应和真实错误：

```text
重定向 / 403 / 405
    → 普通 Response
    → 不记录错误日志

PostgreSQL / PDO 错误
    → 记录数据库日志
    → DatabaseException
    → 安全的 500 响应

控制器或 Dispatcher 错误
    → 记录 Dispatcher 日志
    → 安全的 500 响应

视图或布局错误
    → 记录 View 日志
    → 安全的 500 响应
```

异常的技术消息不得出现在发送给用户的 HTML 响应中。

## 有意保持简单的部分

项目保持小规模，因此不会仅为架构对称而引入额外层：

- 没有依赖容器；
- 没有 ORM；
- 没有为每个模型额外引入 Repository 层；
- 没有中间件链；
- 没有事件总线；
- 没有后台任务；
- 没有独立的 REST API 或 SPA。

在更大型的应用项目中，其中一些边界可能有用。但在这里，它们只会让主要 MVC 流程被额外基础设施掩盖。

实际启动命令、测试数据库、覆盖率与 CI 见[开发指南](development.md)。
