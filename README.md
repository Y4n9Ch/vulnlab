# 🎯 VulnLab - Web安全靶场

中文 | [English](README.en.md)

一个用于学习Web安全的综合靶场，涵盖常见Web漏洞类型。

## 📋 漏洞分类

| 分类 | 说明 |
|------|------|
| SQL注入 | SQL Injection漏洞练习 |
| XSS | 跨站脚本攻击 |
| 命令注入 | Command Injection |
| 文件上传 | File Upload漏洞 |
| 文件包含 | File Inclusion（本地/远程） |
| SSRF | 服务端请求伪造 |
| XXE | XML外部实体注入 |
| SSTI | 服务端模板注入 |
| CSRF | 跨站请求伪造 |
| 反序列化 | PHP反序列化漏洞 |
| 路径穿越 | 目录遍历漏洞 |
| 认证漏洞 | 认证与授权问题 |
| 逻辑漏洞 | 业务逻辑安全 |

## 🚀 快速开始

### 方式一：Docker（推荐）

```bash
# 克隆项目
git clone https://github.com/Y4n9Ch/vulnlab.git
cd vulnlab

# 启动所有服务
docker-compose up -d

# 访问靶场
# http://localhost:8080
```

### 方式二：PHPStudy

1. 安装 PHPStudy（PHP 7.0+，MySQL 5.7+）
2. 将项目放入网站根目录
3. 导入 `sql/init.sql` 到数据库
4. 修改 `config/database.php` 中的数据库配置
5. 访问 `http://localhost/vulnlab`

## 📁 项目结构

```
vulnlab/
├── Dockerfile          # Docker镜像定义
├── docker-compose.yml  # Docker编排配置
├── index.php          # 首页
├── category.php       # 漏洞分类页
├── challenge.php      # 挑战关卡页
├── login.php          # 登录页
├── register.php       # 注册页
├── config/            # 配置文件
│   └── database.php
├── includes/          # 公共组件
├── challenges/        # 漏洞关卡
│   ├── sqli/         # SQL注入
│   ├── xss/          # XSS
│   ├── cmdi/         # 命令注入
│   └── ...
├── sql/              # 数据库初始化
│   └── init.sql
└── assets/           # 前端资源
```

## ⚠️ 免责声明

本靶场仅供安全学习和测试使用，请勿用于非法用途。使用本靶场进行任何活动所产生的后果由使用者自行承担。

## 📝 License

MIT License

---

## 📌 关于本项目

本项目由 **AI辅助生成**，旨在为个人提供一个**安全可控的Web漏洞学习环境**，帮助理解常见Web漏洞的原理、利用方式及防御方法。

> **如无特殊情况，本项目将不再更新。**
