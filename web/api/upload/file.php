<?php
// 统一静态文件输出（含 CORS），支持 .glb

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Range');
header('Access-Control-Expose-Headers: Content-Disposition, Content-Length');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

$rel = isset($_GET['path']) ? $_GET['path'] : '';
if (!$rel) {
    http_response_code(400);
    echo 'missing path';
    exit;
}

// 仅允许访问 uploads 与 public 目录
if (!(strpos($rel, 'uploads/') === 0 || strpos($rel, 'public/') === 0)) {
    http_response_code(403);
    echo 'forbidden';
    exit;
}

// 规范化并拼接到磁盘路径
$safeRel = str_replace(['..\\','../','..'], '', $rel);
$file = realpath(__DIR__ . '/../../' . $safeRel);

// 防穿越：必须仍在项目根下
$root = realpath(__DIR__ . '/../../');
if (!$file || strpos($file, $root) !== 0 || !is_file($file)) {
    http_response_code(404);
    echo 'not found';
    exit;
}

// 根据扩展名设置 Content-Type
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
switch ($ext) {
    case 'glb':
        $mime = 'model/gltf-binary';
        break;
    case 'gltf':
        $mime = 'model/gltf+json';
        break;
    case 'png': $mime = 'image/png'; break;
    case 'jpg':
    case 'jpeg': $mime = 'image/jpeg'; break;
    case 'webp': $mime = 'image/webp'; break;
    case 'gif': $mime = 'image/gif'; break;
    case 'mp4': $mime = 'video/mp4'; break;
    case 'bin': $mime = 'application/octet-stream'; break;
}

header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=31536000, immutable');

// 下载处置与文件名
$forceDownload = isset($_GET['download']) ? (int)$_GET['download'] : 0;
$suggestName = isset($_GET['filename']) ? trim($_GET['filename']) : '';
if ($suggestName === '') {
    $suggestName = basename($file);
}
// 简单清理文件名，避免注入
$suggestName = str_replace(["\r","\n"], '', $suggestName);
if ($forceDownload) {
    $disp = 'attachment';
} else {
    $disp = 'inline';
}
// 同时提供标准与UTF-8扩展，增强兼容
$cd = sprintf("Content-Disposition: %s; filename=\"%s\"; filename*=UTF-8''%s", $disp, $suggestName, rawurlencode($suggestName));
header($cd);
header('Content-Length: ' . filesize($file));

// 输出文件
readfile($file);
exit;

