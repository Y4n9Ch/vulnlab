# VulnLab 项目协作说明

## 项目概述

VulnLab 是一个基于 PHP 8.1、Apache 与 MySQL 8.0 的本地 Web 安全训练靶场。项目采用 PHP 单体结构，题目元数据存储在 MySQL，具体漏洞交互由 `challenges/<category>/*.php` 实现。

本项目仅用于本地、授权和教育场景。不得将靶场中的技术用于未授权目标。

## 目录结构

- `index.php`：训练大厅、学习路线和分类目录。
- `category.php`：分类题目列表与难度筛选。
- `challenge.php`：题目容器、提示、源码辅助和 Flag 提交。
- `challenges/`：按漏洞分类存放题目实现。
- `includes/`：公共函数、认证、页头与页脚。
- `config/database.php`：数据库连接和自动迁移入口。
- `config/migrations/`：已存在数据卷使用的增量迁移。
- `sql/init.sql`：MySQL 首次创建数据卷时的基础结构和题目数据。
- `assets/css/style.css`：全站样式。
- `assets/js/app.js`：筛选、Flag 提交等页面交互。
- `uploads/`：上传类题目的隔离练习目录。

## 常用命令

```powershell
docker compose up -d --build
docker compose ps
docker compose logs -f web
docker compose down
```

首次启动后访问 `http://localhost/`。如果数据库卷已经存在，应用首次连接数据库时会自动执行 `config/migrations/` 下尚未执行的迁移。

PHP 语法检查：

```powershell
docker compose exec web sh -lc "find /var/www/html -name '*.php' -print0 | xargs -0 -n1 php -l"
```

## 代码规范

- 所有文本文件统一使用 UTF-8 编码。
- 页面输出默认使用 `h()` 转义；只有题目明确需要演示输出漏洞时才绕过转义。
- 平台自身的数据库查询使用 PDO 预处理；不安全拼接只允许出现在对应漏洞题文件中。
- 新题文件使用两位数字序号加英文短名，例如 `10_header_trust.php`。
- 新题必须包含明确目标、可观察结果和仅在满足漏洞条件后展示的验证令牌。
- UI 保持训练工具风格：信息密度适中、移动端可用、交互状态明确，卡片圆角不超过 8px。
- 手工修改使用小范围补丁，避免重写与当前任务无关的代码。

## 新增题目流程

1. 在 `challenges/<category>/` 创建题目 PHP 文件。
2. 在 `config/migrations/` 添加幂等增量迁移，写入题目元数据。
3. 如果需要让纯 SQL 初始化立即包含数据，也同步维护 `sql/init.sql`；应用迁移必须仍可独立工作。
4. 题目难度只能使用 `easy`、`medium` 或 `hard`。
5. 检查题目在正常输入、错误输入和成功条件下的页面状态。
6. 运行全量 PHP 语法检查，并在桌面和移动视口检查布局。

## 教学规则

- 回复用户时必须称呼用户为“小羊”，并使用中文。
- 采用苏格拉底式引导，不直接提供当前关卡的完整解题 Payload，也不直接给出最终 Flag。
- 用户询问具体关卡时，先阅读对应题目源码，再从漏洞成因、输入边界和响应差异引导排查。
- 用户卡住时可给出与当前靶场不同的伪代码案例，但仍不泄露本题完整答案。
- 多步骤任务使用 Todo 列表持续跟踪。

## 外网与错误处理

访问外网前设置 Clash Verge Rev 代理：

```powershell
$env:https_proxy = "http://127.0.0.1:7897"
$env:http_proxy = "http://127.0.0.1:7897"
```

默认优先使用本地文件和本地运行结果，避免无必要的高频外部请求。遇到报错时，先说明可执行的解决方案，再向用户确认需要其参与的操作。
