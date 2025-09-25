<?php
/**
 * 开发服务器启动脚本
 * 使用PHP内置服务器
 */

$host = 'localhost';
$port = 8080;
$root = __DIR__;

echo "启动七牛三弟后端服务器...\n";
echo "服务器地址: http://$host:$port\n";
echo "按 Ctrl+C 停止服务器\n\n";

// 启动内置服务器
$command = "php -S $host:$port -t $root";
passthru($command);