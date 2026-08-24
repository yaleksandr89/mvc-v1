# 安全

## 选择语言

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/SECURITY.md) | [English](SECURITY_en.md) | [Español](SECURITY_es.md) | **中文** | [Français](SECURITY_fr.md) | [Deutsch](SECURITY_de.md) |

请负责任地报告潜在漏洞。`mvc-v1` 是教育性 MVC 项目，但路由、CRUD、PostgreSQL 访问、用户输入处理和数据输出中的安全问题会像任何公开仓库一样被认真对待。

## 更适合私下报告的内容

- 创建、编辑或删除文章时绕过 CSRF 防护。
- SQL 注入，或影响 SQL 标识符或查询参数的能力。
- 存储型或反射型 XSS、绕过 HTML 转义，或不安全地输出用户数据。
- 通过 query string 替换 route/path 参数，或以其他方式操作其他实体。
- 泄露内部路径、异常、配置、`.env` 内容、PostgreSQL 凭据或其他敏感信息。
- 会话、CSRF token 或 HTTP 方法处理漏洞，可导致非预期状态变更。
- 对本项目有实质影响的可利用依赖问题。
- 源代码、CI、依赖链或其他供应链环节被破坏。

## 可以在 Issues 中公开的内容

- 没有安全影响的路由错误。
- 没有安全影响的错误 CRUD 行为或验证错误。
- 分页、输出或界面问题。
- 不泄露敏感数据的 PHP 或 PostgreSQL 兼容性问题。
- 文档错误。
- 新功能或改进请求。

如果不确定问题是否影响安全，请先使用私有渠道。

## 如何报告

- 如果仓库的 Security 区域提供私密漏洞报告表单，请优先使用它。
- 不要在 Issues、Discussions、Pull Requests 或日志中公开 exploit 代码、真实秘密、生产 `.env` 内容、PostgreSQL 密码、session identifiers 或其他敏感数据。
- 私密表单暂时不可用时，请创建不含利用细节的最小公开 Issue，并请求私密联系渠道。
- 在与维护者达成一致并且修复可用之前，不要公开披露技术利用细节。

## 应提供的内容

尽可能提供：

- 受影响版本或提交 SHA；
- PHP 版本；
- 问题涉及数据库时的 PostgreSQL 版本；
- 受影响区域：routing、controller、model/PDO、validation、view/output、session/CSRF、configuration 或 dependency chain；
- 影响；
- 最小复现；
- 有用时提供经清理的请求、响应或日志片段。

仅使用合成数据。切勿包含真实密码、令牌、cookies、session IDs、生产 `.env` 内容或其他秘密。

## 后续处理

- 项目只有一名维护者；不保证 SLA。
- 维护者会尽力确认报告、复现问题、评估影响并准备修复。
- 不承诺漏洞奖励计划。
- 在修复可用前请协调公开披露。
