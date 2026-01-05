<?php
/**
 * コメントコントローラー
 * コメントの投稿・取得処理
 */

class CommentController {
    private $db;
    private $commentModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->commentModel = new Comment($pdo);
    }
    
    /**
     * コメント投稿処理
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $postId = (int)($_POST['post_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $name = trim($_POST['author_name'] ?? '');
        $email = trim($_POST['author_email'] ?? '');
        $url = trim($_POST['author_url'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        // バリデーション
        if ($postId <= 0 || empty($name) || empty($content)) {
            $_SESSION['comment_error'] = '必須項目を入力してください。';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['comment_error'] = '有効なメールアドレスを入力してください。';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        // コメントステータス（デフォルトは承認制か即時公開か）
        // ここでは簡単なスパム対策として pending にする場合もあるが、一旦 approved にする
        $status = 'approved'; 
        
        $data = [
            'post_id' => $postId,
            'parent_id' => $parentId,
            'author_name' => $name,
            'author_email' => $email,
            'author_url' => $url,
            'author_ip' => $_SERVER['REMOTE_ADDR'],
            'content' => $content,
            'status' => $status
        ];
        
        try {
            $this->commentModel->create($data);
            $_SESSION['comment_success'] = 'コメントを投稿しました。';
        } catch (Exception $e) {
            $_SESSION['comment_error'] = 'コメントの投稿に失敗しました。';
        }
        
        header("Location: " . $_SERVER['HTTP_REFERER'] . "#comments");
        exit;
    }
}
