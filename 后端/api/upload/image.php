<?php
require_once '../../core/Database.php';
require_once '../../core/Response.php';
require_once '../../core/JWT.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法不允许', 405);
}

// 验证JWT token
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $auth = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    Response::error('未提供认证token', 401);
}

$payload = JWT::decode($token);
if (!$payload) {
    Response::error('认证token无效', 401);
}

try {
    $config = require_once '../../config/config.php';
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        Response::error('文件上传失败', 400);
    }
    
    $file = $_FILES['image'];
    $uploadConfig = $config['upload'];
    
    // 检查文件大小
    if ($file['size'] > $uploadConfig['max_size']) {
        Response::error('文件大小超过限制', 400);
    }
    
    // 检查文件类型
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $uploadConfig['allowed_types'])) {
        Response::error('不支持的文件类型', 400);
    }
    
    // 创建上传目录
    $uploadDir = '../../uploads/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 生成唯一文件名
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // 移动文件
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        Response::error('文件保存失败', 500);
    }
    
    // 返回文件URL
    $fileUrl = 'uploads/images/' . $fileName;
    
    Response::success([
        'url' => $fileUrl,
        'filename' => $fileName,
        'size' => $file['size']
    ], '文件上传成功');
    
} catch (Exception $e) {
    Response::error('上传失败: ' . $e->getMessage(), 500);
}