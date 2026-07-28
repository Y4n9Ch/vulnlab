# 🎯 VulnLab - Web Security Training Lab

A comprehensive web security training lab covering common web vulnerability types.

[中文版](README.md) | English

## 📋 Vulnerability Categories

| Category | Description |
|----------|-------------|
| SQL Injection | SQL Injection vulnerability exercises |
| XSS | Cross-Site Scripting |
| Command Injection | Command Injection vulnerabilities |
| File Upload | File Upload vulnerabilities |
| File Inclusion | File Inclusion (Local/Remote) |
| SSRF | Server-Side Request Forgery |
| XXE | XML External Entity Injection |
| SSTI | Server-Side Template Injection |
| CSRF | Cross-Site Request Forgery |
| Deserialization | PHP Deserialization vulnerabilities |
| Path Traversal | Directory Traversal vulnerabilities |
| Auth Bypass | Authentication & Authorization issues |
| Logic Flaws | Business Logic Security |

## 🚀 Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/Y4n9Ch/vulnlab.git
cd vulnlab

# Start all services
docker-compose up -d

# Access the lab
# http://localhost:8080
```

### Option 2: PHPStudy

1. Install PHPStudy (PHP 7.0+, MySQL 5.7+)
2. Place the project in the web root directory
3. Import `sql/init.sql` into the database
4. Modify database configuration in `config/database.php`
5. Visit `http://localhost/vulnlab`

## 📁 Project Structure

```
vulnlab/
├── Dockerfile          # Docker image definition
├── docker-compose.yml  # Docker compose configuration
├── index.php          # Homepage
├── category.php       # Vulnerability category page
├── challenge.php      # Challenge page
├── login.php          # Login page
├── register.php       # Register page
├── config/            # Configuration files
│   └── database.php
├── includes/          # Common components
├── challenges/        # Vulnerability challenges
│   ├── sqli/         # SQL Injection
│   ├── xss/          # XSS
│   ├── cmdi/         # Command Injection
│   └── ...
├── sql/              # Database initialization
│   └── init.sql
└── assets/           # Frontend resources
```

## ⚠️ Disclaimer

This lab is for security learning and testing purposes only. Do not use it for illegal activities. Any consequences arising from the use of this lab are borne by the user.

## 📝 License

MIT License

---

## 📌 About This Project

This project is **AI-assisted**, designed to provide a **safe and controlled learning environment for web vulnerabilities**, helping to understand the principles, exploitation methods, and defense strategies of common web vulnerabilities.

> **This project will no longer be updated unless there are special circumstances.**
