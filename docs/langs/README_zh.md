# 项目：使用基本框架示例实现MVC架构模式

## 选择语言:

| Русский  | English                              | Español                              | 中文                              | Français                              | Deutsch                              |
|----------|--------------------------------------|--------------------------------------|---------------------------------|---------------------------------------|--------------------------------------|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | **已选** | [Français](./README_fr.md) | [Deutsch](./README_de.md) |

## 使用的技术栈:

- PHP 8
- Postgresql (PDO)
- Bootstrap 5.3

## 描述:

该项目使用简单的自制框架作为示例，实现了MVC架构模式。在框架内，针对"文章"部分实现了CRUD操作：

<details>
  <summary>创建</summary>

![ajax filter is in operation](../img/mvc-create-article.gif)
</details>

<details>
  <summary>显示</summary>

![ajax filter is in operation](../img/mvc-read-article.gif)
</details>

<details>
  <summary>更新</summary>

![ajax filter is in operation](../img/mvc-update-article.gif)
</details>

<details>
  <summary>删除</summary>

![ajax filter is in operation](../img/mvc-delete-article.gif)
</details>

在创建和更新文章时实现了验证：

<details>
  <summary>验证过程 Process</summary>

![ajax filter is in operation](../img/mvc-validation.gif)
</details>

在目录`docs/conf/`中，包含：`nginx-configuration.conf` - 用于`nginx`的配置文件示例。

## 运行项目:

1. 将配置添加到您的服务器上。您可以使用`docs/conf/`中的文件作为基础。
2. 运行 `composer i`
3. 将 `.env.example` 重命名为 `.env` 并填写 `# DB info` 部分
4. 创建数据库并导入文件 `db_dump.sql` 的内容（导入示例命令：`sudo -iu postgres psql -U postgres mvc_v1 < /www/mvc-v1.col/docs/db_dump.sql`），该文件位于 `docs/` 文件夹中。

> 2024年所做的更改很小。我故意保留了原始的核心结构，没有添加容器功能、DI等。因为这是我最早的作品之一，所以我决定几乎保持其原始形式 😇
