<?php
/**
 * 项目安装脚本
 * 用于创建数据库和初始化数据
 */

require_once 'core/Database.php';

try {
    // 读取数据库配置
    $config = require_once 'config/database.php';
    
    // 连接MySQL服务器（不指定数据库）
    $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // 创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "数据库创建成功\n";
    
    // 选择数据库
    $pdo->exec("USE {$config['dbname']}");
    
    // 读取并执行SQL文件
    $sql = file_get_contents('database/schema.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "数据表创建成功\n";
    
    // 创建上传目录
    $uploadDirs = [
        '../uploads/',
        '../uploads/images/',
        '../uploads/models/'
    ];
    
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "创建目录: $dir\n";
        }
    }
    
    echo "项目安装完成！\n";
    echo "请访问前端页面开始使用。\n";
    
} catch (Exception $e) {
    echo "安装失败: " . $e->getMessage() . "\n";
}