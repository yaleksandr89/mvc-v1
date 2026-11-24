# 开发

## 环境要求

推荐的 Docker Compose 运行方式要求宿主机安装：

- Git;
- Make;
- 支持 Compose 的 Docker。

PHP、Composer、PHPUnit、PHPStan 和 PHP CS Fixer 均在 PHP 容器内运行，无需在宿主机单独安装。

Docker 并非必需：项目也可以在具有 PHP、Composer、PostgreSQL 和 Web 服务器的常规环境中运行。具体见[不使用 Docker 运行](#不使用-docker-运行)。

## 首次启动

| 命令 | 作用 | 说明 |
|---|---|---|
| `make init` | 创建 `.env.docker`、`logs/` 和 `runtime/cache/` | 仅在 `.env.docker` 不存在时创建 |
| `make build` | 构建 PHP Docker 镜像 | |
| `make up` | 启动 Nginx、PHP-FPM 和 PostgreSQL | 等待服务就绪 |
| `make composer-install` | 根据 `composer.lock` 安装依赖 | 在 PHP 容器内执行 |
| `make demo-data` | 加载 50 篇演示文章 | 仅写入空的 `blog_posts` 表 |
| `make db-check` | 显示 PostgreSQL 版本和 `blog_posts` 状态 | 适合首次启动后的检查 |

启动后，应用默认可通过 `http://localhost:8080` 访问。

使用 `make down` 停止环境：容器会停止，PostgreSQL 数据卷会保留。

## Docker Compose

[`compose.yaml`](../../../compose.yaml) 启动三个服务：

- `php` — PHP 8.5 FPM 和开发工具；
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

PHP 容器通过 [`docker/php/Dockerfile`](../../../docker/php/Dockerfile) 构建。用户 `app` 使用宿主机用户的 UID/GID，因此在容器内创建的 `vendor`、报告及其他文件不应归 `root` 所有。

环境管理命令的完整列表见[全部 Make 命令](#全部-make-命令)。

## 环境变量

Docker 使用基于 [`.env.docker.example`](../../../.env.docker.example) 创建的 `.env.docker`。

主要配置组：

- `HOST_UID`, `HOST_GID` — 宿主机用户的 UID/GID；
- `APP_PORT` — 外部 HTTP 端口，默认为 `8080`；
- `DB_FORWARD_PORT` — 宿主机上的 PostgreSQL 端口；
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — 本地 PostgreSQL 参数；
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — 应用设置；
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — 应用的数据库连接。

示例文件中的值仅用于本地开发。不要将真实密钥提交到 Git。

不使用 Docker 手动运行时，根据 [`.env.example`](../../../.env.example) 在根目录创建 `.env`。应用自行加载该文件，不依赖第三方 dotenv 库。已有的进程环境变量和 `$_ENV` 中的值优先，不会被 `.env` 中的值覆盖。

根目录的 `.env` 使用简单的 `KEY=VALUE` 格式；项目不使用 dotenv 库的扩展语法。

## PostgreSQL 与演示数据

首次启动新的 PostgreSQL 数据卷时，使用 [`docs/db_schema_only.sql`](../../db_schema_only.sql) 创建数据库模式。演示时可以从 [`docs/db_demo_data.sql`](../../db_demo_data.sql) 加载 50 篇文章。

| 命令 | 作用 | 说明 |
|---|---|---|
| `make db-check` | 检查 PostgreSQL 版本和 `blog_posts` 状态 | |
| `make demo-data` | 加载 50 篇演示文章 | 仅适用于空的 `blog_posts` 表 |
| `make postgres-reinit CONFIRM=postgres18` | 完全重建本地 PostgreSQL 数据卷 | 删除当前本地数据 |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | 重建 PostgreSQL 并立即加载 50 篇文章 | 删除当前本地数据 |

> [!IMPORTANT]
> `make demo-data` 不覆盖已有文章。如果表中已有数据，命令会报错退出而不修改数据。只有通过显式确认的 `postgres-reinit` 才能完全重置。

## 独立测试数据库

PHPUnit 不操作主数据库 `mvc_v1`。测试使用独立的 `mvc_v1_test` 数据库。

| 命令 | 作用 | 说明 |
|---|---|---|
| `make test-db-create` | 在 `mvc_v1_test` 不存在时创建它 | 不删除已有数据库 |
| `make test-db-reset` | 重建数据库模式并加载三篇测试文章 | 测试前自动使用 |
| `make test-db-check` | 检查数据库名称和测试数据状态 | 应有 3 篇文章，ID 为 1 至 3 |
| `make test-db-drop CONFIRM=mvc_v1_test` | 仅删除 `mvc_v1_test` | 需要显式确认 |

`make test`、`make test-dox`、`make coverage`、`make coverage-html` 和 `make check` 在运行前自动重置测试数据库。

## 测试、分析与覆盖率

| 命令 | 作用 | 说明 |
|---|---|---|
| `make test` | 运行 PHPUnit | |
| `make test-dox` | 以易读的场景名称运行 PHPUnit | |
| `make cs-check` | 检查 PHP 代码风格，存在违规时显示 diff | 不修改源文件，也不重置测试数据库 |
| `make cs-fix` | 格式化 PHP 代码 | 修改源文件；不重置测试数据库 |
| `make analyse` | 运行 PHPStan | |
| `make check` | 执行 Composer 验证、Composer 审计、PHP CS Fixer 风格检查、PHPStan 和 PHPUnit | 主要综合检查；运行前重置测试数据库 |
| `make coverage` | 显示覆盖率并生成 Clover XML | `runtime/coverage.xml` |
| `make coverage-html` | 生成 HTML 覆盖率报告 | `runtime/coverage/index.html` |
| `make smoke` | 执行端到端 HTTP 检查 | 需要 50 篇演示文章 |

PHP CS Fixer 作为开发依赖安装。规则和格式化范围在 [`.php-cs-fixer.dist.php`](../../../.php-cs-fixer.dist.php) 中定义：`app/`、`config/`、`core/src/`、`tests/`、`views/` 中的 PHP 代码、`public/index.php` 文件及配置文件本身。风险规则已禁用；第三方前端资源不参与格式化。

代码审查以 PER Coding Style 3.1 和 PSR-1 为基础；格式化工具使用已配置的 `@PER-CS` 规则集及附加规则。混合 PHP/HTML 模板的约定例外和其他要求见 [`AGENTS.md`](../../../AGENTS.md)。

不使用 Docker 时，用 `composer cs:check` 检查风格，用 `composer cs:fix` 修复风格。通过 Make 运行时，这些命令以 `app` 用户身份在 PHP 容器内执行。格式化工具缓存保存在 `runtime/cache/php-cs-fixer.cache`，不纳入 Git。单独的风格检查和格式化命令不需要数据库连接。

覆盖率用作诊断工具，而非公开质量指标。CI 通过 OIDC 将 Clover 发送到 Codecov。没有必须达到 100% 的单独要求：测试应保护项目的实际行为，而不是为了百分比人为执行不可达分支。

## 端到端 HTTP 检查

`make smoke` 通过已启动的 Docker 环境运行真实 HTTP 场景，检查：

- 普通页面返回 `200`；
- CSRF 令牌无效时返回 `403`；
- `302` 重定向及 `Location` 响应头；
- `405 Method Not Allowed` 及 `Allow: POST` 响应头；
- 删除文章后返回 `404`；
- 恢复原始演示数据行数。

此检查补充 PHPUnit：测试验证 PHP 进程内的代码和集成，而 `make smoke` 覆盖 Nginx → PHP-FPM → 应用 → PostgreSQL 的完整路径。

## GitHub Actions

工作流 [`.github/workflows/ci.yml`](../../../.github/workflows/ci.yml) 在面向 `master` 的 pull request、向 `master` 推送变更时运行，也可通过 Actions → CI → Run workflow 手动触发。

在全新的 GitHub Actions 环境中，它会：

1. 创建本地环境；
2. 检查 Docker Compose 配置；
3. 构建并启动容器；
4. 根据 `composer.lock` 安装依赖；
5. 验证内置 MVC 核心的 Composer 文件；
6. 检查 PHP 语法；
7. 运行 `make check`：Composer 严格验证、依赖审计、风格检查、PHPStan 和 PHPUnit；
8. 生成覆盖率报告并将 Clover 发送到 Codecov；
9. 加载演示数据；
10. 检查 PostgreSQL；
11. 运行 `make smoke`；
12. 删除测试数据库并停止容器。

此工作流不部署应用。

## 全部 Make 命令

不带参数的 `make` 显示内置帮助。以下是当前 [`Makefile`](../../../Makefile) 定义的全部命令。

| 命令 | 作用 | 说明 |
|---|---|---|
| `make` / `make help` | 显示命令帮助 | |
| `make init` | 创建 `.env.docker`、`logs/` 和 `runtime/cache/` | 不覆盖已有的 `.env.docker` |
| `make check-env` | 检查 `.env.docker` 和 Docker Compose 是否存在 | |
| `make config` | 检查最终生效的 Docker Compose 配置 | |
| `make build` | 构建 PHP Docker 镜像 | |
| `make up` | 启动环境并等待服务就绪 | |
| `make down` | 停止环境 | 保留 PostgreSQL 数据卷 |
| `make restart <service>` | 重启选定服务 | `php`、`nginx` 或 `postgres` |
| `make ps` | 显示项目容器状态 | |
| `make log <service>` | 实时显示选定服务的日志 | `php`、`nginx` 或 `postgres` |
| `make log-all` | 实时显示所有服务的日志 | |
| `make in <service>` | 打开选定容器的 Shell | `php`、`nginx` 或 `postgres` |
| `make php CMD="..."` | 以 `app` 用户身份执行任意 PHP 命令 | 例如：`make php CMD="-v"` |
| `make composer CMD="..."` | 执行任意 Composer 命令 | 例如：`make composer CMD="validate"` |
| `make composer-install` | 根据 `composer.lock` 安装依赖 | |
| `make test` | 运行 PHPUnit | 运行前重置测试数据库 |
| `make test-dox` | 以 TestDox 格式运行 PHPUnit | 运行前重置测试数据库 |
| `make coverage` | 显示覆盖率并生成 Clover XML | 运行前重置测试数据库 |
| `make coverage-html` | 生成 HTML 覆盖率报告 | 运行前重置测试数据库 |
| `make cs-check` | 检查 PHP 代码风格，存在违规时显示 diff | 不修改源文件，也不重置测试数据库 |
| `make cs-fix` | 格式化 PHP 代码 | 修改源文件；不重置测试数据库 |
| `make analyse` | 运行 PHPStan | |
| `make check` | 执行 Composer 验证、Composer 审计、PHP CS Fixer 风格检查、PHPStan 和 PHPUnit | 运行前重置测试数据库 |
| `make smoke` | 通过 HTTP 执行端到端 CRUD/CSRF 检查 | 主数据库必须包含 50 篇演示文章 |
| `make db-check` | 显示 PostgreSQL 版本和 `blog_posts` 状态 | |
| `make demo-data` | 加载 50 篇演示文章 | 仅写入空的 `blog_posts` 表 |
| `make test-db-create` | 在 `mvc_v1_test` 不存在时创建它 | |
| `make test-db-reset` | 重建数据库模式和测试数据 | 仅删除 `mvc_v1_test` 内的数据库模式 |
| `make test-db-check` | 检查测试数据库 | |
| `make test-db-drop CONFIRM=mvc_v1_test` | 删除 `mvc_v1_test` | 需要精确确认 |
| `make postgres-reinit CONFIRM=postgres18` | 重建本地 PostgreSQL 数据卷 | 删除主数据库的本地数据 |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | 重建 PostgreSQL 并加载 50 篇文章 | 删除主数据库的本地数据 |

## 文件权限

会创建文件的 PHP 和 Composer 命令以 `app` 用户身份在容器内执行，该用户通过 `.env.docker` 与宿主机用户的 UID/GID 关联。

因此，通过 Make 正常工作时，不应在工作副本中生成归 `root` 所有的文件。常规开发周期不需要批量执行 `chmod`、`chown` 或删除目录。

## 不使用 Docker 运行

Docker Compose 仍是推荐且最易复现的运行方式。但如果由于某种原因不适合使用 Docker，也可以手动启动项目。

这里不再单独详述 PHP-FPM、PostgreSQL 的安装和 Nginx 的配置：既然选择了手动方式，相信你可以完成这些服务的基本配置。为了避免完全从零开始，仓库提供了 Nginx 配置示例，Docker 配置也展示了所使用的 PHP 扩展和环境参数。

需要以下组件：

- 带有 `mbstring`、`pdo` 和 `pdo_pgsql` 的 PHP 8.5；
- Composer;
- PostgreSQL;
- Nginx 或其他 Web 服务器，文档根目录指向 `public/`。

按照 [`.env.example`](../../../.env.example) 模板配置根目录的 `.env`。必要时可通过系统环境或 PHP-FPM 设置值，这些值优先于 `.env`。

数据库模式和演示数据位于：

- [`docs/db_schema_only.sql`](../../db_schema_only.sql);
- [`docs/db_demo_data.sql`](../../db_demo_data.sql).

Nginx + PHP-FPM 最小配置示例：[`docs/conf/nginx-configuration.conf`](../../conf/nginx-configuration.conf)。使用前需根据系统调整 `server_name`、`root` 和 `fastcgi_pass`。

此示例不足以用于生产部署：TLS、进程管理、密钥、备份和运维策略超出本教学项目的范围。
