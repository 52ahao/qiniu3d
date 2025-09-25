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
    $config = require __DIR__ . '/../../config/config.php';
    
    // 将 php.ini 中的大小单位（如 2M/512K）转为字节
    $toBytes = function($val) {
        if (is_numeric($val)) return (int)$val;
        $val = trim($val);
        $num = (int)$val;
        $unit = strtolower(substr($val, -1));
        switch ($unit) {
            case 'g': return $num * 1024 * 1024 * 1024;
            case 'm': return $num * 1024 * 1024;
            case 'k': return $num * 1024;
            default: return (int)$val;
        }
    };
    
    if (!isset($_FILES['image'])) {
        Response::error('未接收到文件', 400);
    }

    // 细化错误码提示，便于排查 ini 限制
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['image']['error'];
        switch ($err) {
            case UPLOAD_ERR_INI_SIZE:
                // 超过 php.ini 的 upload_max_filesize 限制
                Response::error('超过服务器单文件大小限制（upload_max_filesize）', 400);
            case UPLOAD_ERR_FORM_SIZE:
                // 超过表单 MAX_FILE_SIZE 限制
                Response::error('超过表单允许的文件大小限制（MAX_FILE_SIZE）', 400);
            case UPLOAD_ERR_PARTIAL:
                Response::error('文件仅部分上传，请重试', 400);
            case UPLOAD_ERR_NO_FILE:
                Response::error('未选择文件', 400);
            case UPLOAD_ERR_NO_TMP_DIR:
                Response::error('服务器临时目录缺失', 500);
            case UPLOAD_ERR_CANT_WRITE:
                Response::error('服务器写入失败', 500);
            case UPLOAD_ERR_EXTENSION:
                Response::error('服务器扩展中断了上传', 500);
            default:
                Response::error('文件上传失败', 400);
        }
    }

    $file = $_FILES['image'];
    $uploadConfig = isset($config['upload']) ? $config['upload'] : [];
    $maxSize = isset($uploadConfig['max_size']) && is_numeric($uploadConfig['max_size'])
        ? (int)$uploadConfig['max_size']
        : 10 * 1024 * 1024; // 合理的保底值 10MB
    
    // 检查文件大小（按业务配置）
    if ($file['size'] > $maxSize) {
        $phpUploadMax = $toBytes(ini_get('upload_max_filesize'));
        $phpPostMax = $toBytes(ini_get('post_max_size'));
        Response::error(
            '文件大小超过限制',
            400,
            [
                'file_size' => $file['size'],
                'max_size' => $maxSize,
                'php_upload_max_filesize' => $phpUploadMax,
                'php_post_max_size' => $phpPostMax
            ]
        );
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