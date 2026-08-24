# 为 mvc-v1 做贡献

## 选择语言

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/CONTRIBUTING.md) | [English](CONTRIBUTING_en.md) | [Español](CONTRIBUTING_es.md) | **中文** | [Français](CONTRIBUTING_fr.md) | [Deutsch](CONTRIBUTING_de.md) |

感谢您关注 `mvc-v1`。这是一个带有小型自定义框架的教育性 PHP MVC 项目。

## 开始之前

请先查看已有的 Issues 和 Pull Requests，并保持工作范围清晰、集中。安全相关问题请通过[安全政策](../../.github/SECURITY.md)报告，不要公开提交 Issue。

## 项目约定

请保留教育目的和可见的 `Router → Dispatcher → Controller → Model/View` 流程、文章 CRUD，以及通过 PDO 使用 PostgreSQL 的行为。PHP 基线为 8.5；架构有意保持小巧且不依赖特定框架。

请勿用 Symfony 或 Laravel 替换项目，也不要在没有明确需要时加入 DI/container、ORM 或 repository/service 层。避免与 Issue 无关的大范围重构。

## 分支

从当前目标分支创建聚焦的分支。使用简短且描述性的名称，例如 `fix/article-validation` 或 `docs/contributing`。

## 提交

建议使用 Conventional Commits：`fix(router): preserve path parameters`、`feat(articles): validate excerpt`、`docs: clarify local checks`。提交应小而完整，并有清晰说明。

## 本地检查

```shell
composer install
composer check
```

针对性检查请使用：

```shell
composer test
composer analyse
```

项目目前没有 formatter 或 code-style 命令。修改运行时或数据库行为时，请说明已执行的验证。

## Pull Request

说明改动内容、原因和验证方法。每个 PR 只处理一项任务。公共安装或行为变化时更新相关文档；政策变化时同步翻译。

## 最终清单与安全卫生

- 不包含秘密、生产 `.env`、令牌、cookies 或个人数据。
- `composer check` 通过。
- 行为变化时添加或更新包内回归测试。
- 自定义 MVC 的目的和小型架构仍然明确。
- 适用时说明运行时/数据库验证。
- 适用时更新文档和政策翻译。
