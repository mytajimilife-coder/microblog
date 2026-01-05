<?php
/**
 * ニュースレターコントローラー
 * 購読処理
 */

require_once __DIR__ . '/../models/subscriber.php';

class NewsletterController {
    private $db;
    private $subscriberModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->subscriberModel = new Subscriber($pdo);
    }
    
    /**
     * 購読処理
     */
    public function subscribe() {
        header('Content-Type: application/json');
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '有効なメールアドレスを入力してください。']);
            exit;
        }
        
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ua = $_SERVER['HTTP_USER_AGENT'];
            
            $this->subscriberModel->subscribe($email, $ip, $ua);
            
            echo json_encode(['success' => true, 'message' => '購読ありがとうございます！最新情報をお届けします。']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'エラーが発生しました。時間を置いて再度お試しください。']);
        }
        exit;
    }
    
    /**
     * 購読解除処理（URL経由など）
     */
    public function unsubscribe($email) {
        // セキュリティのために後ほどトークン制にすることも検討
        $this->subscriberModel->unsubscribe($email);
        echo "購読を解除しました。";
    }
}
