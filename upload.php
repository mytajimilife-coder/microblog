<?php
/**
 * ファイルアップロード処理
 * TinyMCE エディター用の画像アップロードエンドポイント
 */

session_start();

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/model.php';

// 管理者認証チェック
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '認証が必要です']);
    exit;
}

// POST リクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST メソッドのみ許可されています']);
    exit;
}

try {
    // ファイル存在確認
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('ファイルがアップロードされていません');
    }
    
    // ファイルアップロード処理
    $uploadResult = FileHelper::uploadFile($_FILES['file'], UPLOAD_PATH);
    
    if ($uploadResult['success']) {
        echo json_encode([
            'success' => true,
            'location' => $uploadResult['url'],
            'url' => $uploadResult['url']
        ]);
    } else {
        throw new Exception($uploadResult['message']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>