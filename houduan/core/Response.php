<?php
/**
 * 响应处理类
 */
class Response {
    public static function success($data = null, $message = '操作成功', $code = 200) {
        self::json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
    }
    
    public static function error($message = '操作失败', $code = 400, $data = null) {
        self::json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
    }
    
    public static function json($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}