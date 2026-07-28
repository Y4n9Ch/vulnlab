<?php
/**
 * 数据库配置文件
 * PHPStudy 默认配置 / Docker 默认配置
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnlab');

// Flag 密钥前缀
define('FLAG_PREFIX', 'flag{');
define('FLAG_SUFFIX', '}');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $connection = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);
        runMigrations($connection);
        $pdo = $connection;
    }
    return $pdo;
}

// 数据卷已存在时，Docker 的初始化 SQL 不会再次执行，因此在应用启动时补增量迁移。
function runMigrations(PDO $pdo) {
    static $migrated = false;
    if ($migrated) return;

    $lockName = DB_NAME . '_schema_migrations';
    $lock = $pdo->prepare("SELECT GET_LOCK(?, 10)");
    $lock->execute([$lockName]);
    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Unable to acquire migration lock');
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
            version VARCHAR(100) PRIMARY KEY,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $migrationDir = __DIR__ . '/migrations';
        $files = is_dir($migrationDir) ? (glob($migrationDir . '/*.php') ?: []) : [];
        sort($files, SORT_STRING);
        foreach ($files as $file) {
            $version = basename($file, '.php');
            $stmt = $pdo->prepare("SELECT 1 FROM schema_migrations WHERE version = ?");
            $stmt->execute([$version]);
            if ($stmt->fetchColumn()) continue;

            $migration = require $file;
            if (!is_callable($migration)) {
                throw new RuntimeException("Invalid migration: {$version}");
            }

            // MySQL DDL 会隐式提交；迁移应保持幂等，失败后由下次请求继续补齐。
            $migration($pdo);
            $stmt = $pdo->prepare("INSERT INTO schema_migrations (version) VALUES (?)");
            $stmt->execute([$version]);
        }
        $migrated = true;
    } finally {
        $release = $pdo->prepare("SELECT RELEASE_LOCK(?)");
        $release->execute([$lockName]);
    }
}

// 不安全的数据库连接（用于漏洞题目）
function getVulnDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);
    }
    return $pdo;
}
