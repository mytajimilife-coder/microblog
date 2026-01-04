<?php
/**
 * API エントリポイント
 * 例: api.php?action=posts&id=1
 *     api.php?action=search&q=キーワード
 */

require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/api_manager.php';
require_once 'includes/controllers/api_controller.php';

$db = new Database();
$pdo = $db->connect();
$apiManager = new APIManager($pdo);

// APIキー認証 (Header または GET)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
$client = $apiManager->validateKey($apiKey);

if (!$client) {
    APIManager::sendResponse(['status' => 'error', 'message' => '無効なAPIキーです'], 401);
}

$controller = new APIController($pdo);
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// JSON入力の取得
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

switch ($action) {
    case 'posts':
        if ($method === 'POST') {
            // 新規投稿作成
            $response = $controller->createPost($input, $client['user_id']);
        } else {
            // 取得
            if (isset($_GET['id'])) {
                $response = $controller->getPost($_GET['id']);
            } else {
                $response = $controller->getPosts($_GET);
            }
        }
        break;

    case 'search':
        $response = $controller->search($_GET['q'] ?? '');
        break;

    case 'stats':
        $response = $controller->getStats();
        break;

    default:
        $response = ['status' => 'error', 'message' => '未知のアクションです', 'code' => 400];
        break;
}

// エラーコードが含まれている場合はそのコードで送信
$status = $response['code'] ?? 200;
unset($response['code']);

APIManager::sendResponse($response, $status);
?>
