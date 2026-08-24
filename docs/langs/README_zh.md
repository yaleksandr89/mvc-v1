# PHP MVC

[![Source Code](https://img.shields.io/badge/source-yaleksandr89%2Fmvc--v1-blue.svg?style=flat-square)](https://github.com/yaleksandr89/mvc-v1)
[![CI](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml/badge.svg)](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.4-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](../../LICENSE.md)

<p align="center">
  <img
    src="../img/mvc-v1-readme-cover.png"
    alt="PHP MVC——使用自研 MVC 核心、PostgreSQL 和 Docker Compose 的教学博客"
    width="100%"
  >
</p>

## 选择语言

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | **已选择** | [Français](./README_fr.md) | [Deutsch](./README_de.md) |

这是我较早独立实现 MVC 的项目之一，没有直接依赖现成框架。示例是一个简单的文章博客：路由、控制器、模型、视图和 CRUD 被组织在一起，因此可以直接沿着项目自身的代码追踪一次请求的完整路径。

项目有意避免堆叠现成库。PostgreSQL 通过 PDO 直接访问，不使用 ORM。生产代码中没有第三方 PHP 库：自研 MVC 核心以本地 Composer 包 `yaa/mvc-framework` 接入，应用本身依赖 PHP 以及必需的 `mbstring`、`PDO` 和 `pdo_pgsql` 扩展。

> [!NOTE]
> **2026 更新。** 项目的初衷没有改变：它仍然是同一个小型 MVC 项目，但已经更新到 PHP 8.5、当前版本的 PostgreSQL 和 Bootstrap，并加强了对修改数据请求的安全处理。Docker Compose 提供可复现的本地运行环境，宿主机无需安装 PHP 或 Composer；同时仍然支持不使用 Docker 的手动部署方式。

## 功能

- 创建、查看、编辑和删除文章；
- 通过 PDO 和参数化查询使用 PostgreSQL；
- 文章列表分页；
- 服务端验证，并在验证失败后保留已输入的数据；
- 对修改数据的请求进行 CSRF 防护；
- CRUD 操作后的提示消息；
- 对动态输出进行转义；
- 类型明确的 `Page`、`Response` 和 `RedirectResponse`；
- 使用 Nginx、PHP-FPM 和 PostgreSQL 的 Docker Compose 环境。

## 快速开始

需要 Git、Make，以及支持 Compose 的 Docker。

| 命令 | 作用 | 备注 |
|---|---|---|
| `git clone https://github.com/yaleksandr89/mvc-v1.git` | 克隆仓库 | |
| `cd mvc-v1` | 进入项目目录 | |
| `make init` | 创建 `.env.docker` 和工作目录 | 通常在克隆后执行一次 |
| `make build` | 构建 PHP Docker 镜像 | |
| `make up` | 启动 Nginx、PHP-FPM 和 PostgreSQL | |
| `make composer-install` | 根据 `composer.lock` 安装依赖 | 宿主机无需安装 PHP 和 Composer |
| `make demo-data` | 导入 50 篇演示文章 | 仅适用于空的 `blog_posts` 表 |
| `make down` | 停止环境 | PostgreSQL 数据会保留 |

启动后，可通过 [http://localhost:8080](http://localhost:8080) 访问应用。

> [!IMPORTANT]
> `make demo-data` 仅适用于空的 `blog_posts` 表。如果表中已经有数据，命令会停止且不会覆盖现有内容。若要完全重建本地 PostgreSQL 存储并立即导入演示数据，请使用 `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1`；该命令会删除本地 PostgreSQL volume。

环境说明、独立测试数据库、其他命令以及不使用 Docker 的手动运行方式都记录在[开发指南](../development.md)中。

## 应用如何工作

```text
HTTP 请求
    ↓
Router
    ↓
Dispatcher
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php → 状态码 + 响应头 + 响应体
```

`Page` 描述仍需渲染的页面，而 `Response` 和 `RedirectResponse` 表示已经完整构建的 HTTP 响应。状态码、响应头和响应体只会在入口脚本 `public/index.php` 中发送。

文章模型通过 PDO 直接访问 PostgreSQL。真正的故障在应用内部仍以异常表示，由相应层记录日志，并转换为安全的 `500` 响应，不向用户暴露技术细节。

路由、职责边界以及完整请求路径详见[架构说明](../architecture.md)。

## 检查与覆盖率

| 命令 | 作用 | 备注 |
|---|---|---|
| `make test` | 运行 PHPUnit | 运行前会重置独立测试数据库 |
| `make test-dox` | 以易读的场景名称运行 PHPUnit | |
| `make check` | 检查 Composer、依赖、PHPStan 和 PHPUnit | 主要综合检查 |
| `make coverage` | 显示覆盖率并生成 Clover XML | 输出：`runtime/coverage.xml` |
| `make coverage-html` | 生成 HTML 覆盖率报告 | 输出：`runtime/coverage/index.html` |
| `make smoke` | 通过真实 HTTP 请求检查应用 | 需要 50 篇演示文章 |

测试使用独立的 `mvc_v1_test` 数据库，因此运行测试时不会修改主要开发数据库。

完整的 Make 命令列表和 CI 配置见[开发指南](../development.md)。

## 有意保持简单的部分

- MVC 核心保持小巧，便于直接从代码中追踪主要请求路径；
- 未添加独立的依赖注入容器；
- 未添加 ORM 或额外的 Repository 层，PostgreSQL 访问直接通过 PDO 完成；
- 未引入中间件链或事件系统。

这些选择及职责边界在[架构说明](../architecture.md)中有更详细的说明。

## 反馈

- 可复现的错误 → [GitHub Issues](https://github.com/yaleksandr89/mvc-v1/issues)；
- 问题和想法 → [GitHub Discussions](https://github.com/yaleksandr89/mvc-v1/discussions)。

---

<p align="center">
  如果这个项目对你有帮助，欢迎在 GitHub 上点一个 Star，这也能让更多开发者更容易找到它。🤘
</p>
