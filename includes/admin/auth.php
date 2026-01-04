<?php
/**
 * 管理者認証システム
 * ログイン・認証処理
 */

class AdminAuth {
    private $db;
    private $userModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->userModel = new User($pdo);
    }
    
    /**
     * ログイン試行
     */
    public function login($username, $password) {
        try {
            // ユーザー検索
            $user = $this->userModel->findByUsernameOrEmail($username);
            
            if (!$user) {
                return ['success' => false, 'message' => 'ユーザーが見つかりません'];
            }
            
            // ステータスチェック
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'アカウントが無効です'];
            }
            
            // パスワード検証
            if (!$this->userModel->verifyPassword($user, $password)) {
                return ['success' => false, 'message' => 'パスワードが正しくありません'];
            }
            
            // セッション設定
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_display_name'] = $user['display_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // 最終ログイン時間更新
            $this->userModel->updateLastLogin($user['id']);
            
            return ['success' => true, 'message' => 'ログインしました'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'ログインエラー: ' . $e->getMessage()];
        }
    }
    
    /**
     * ログアウト
     */
    public function logout() {
        session_destroy();
        session_start();
        $_SESSION['admin_message'] = 'ログアウトしました';
    }
    
    /**
     * 認証状態チェック
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            return false;
        }
        
        // セッションタイムアウトチェック
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
            $this->logout();
            $_SESSION['admin_error'] = 'セッションが期限切れです';
            return false;
        }
        
        // ログイン時間更新
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    /**
     * 権限チェック
     */
    public function hasPermission($requiredRole = 'author') {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userRole = $_SESSION['admin_role'];
        
        $roleHierarchy = [
            'admin' => 3,
            'editor' => 2,
            'author' => 1
        ];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 1;
        
        return $userLevel >= $requiredLevel;
    }
}

// グローバル関数
function isLoggedIn() {
    global $pdo;
    
    if (!isset($GLOBALS['admin_auth'])) {
        $GLOBALS['admin_auth'] = new AdminAuth($pdo);
    }
    
    return $GLOBALS['admin_auth']->isLoggedIn();
}

function hasPermission($role = 'author') {
    global $pdo;
    
    if (!isset($GLOBALS['admin_auth'])) {
        $GLOBALS['admin_auth'] = new AdminAuth($pdo);
    }
    
    return $GLOBALS['admin_auth']->hasPermission($role);
}

function getCurrentUser() {
    global $pdo;
    
    if (!isset($GLOBALS['admin_auth'])) {
        $GLOBALS['admin_auth'] = new AdminAuth($pdo);
    }
    
    $currentUser = null;
    if (isLoggedIn()) {
        $currentUser = [
            'id' => $_SESSION['admin_user_id'],
            'username' => $_SESSION['admin_username'],
            'display_name' => $_SESSION['admin_display_name'],
            'role' => $_SESSION['admin_role']
        ];
    }
    
    return $currentUser;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requirePermission($role = 'author') {
    requireLogin();
    
    if (!hasPermission($role)) {
        $_SESSION['admin_error'] = 'この操作を実行する権限がありません';
        header('Location: index.php');
        exit;
    }
}

function logout() {
    global $pdo;
    
    if (!isset($GLOBALS['admin_auth'])) {
        $GLOBALS['admin_auth'] = new AdminAuth($pdo);
    }
    
    $GLOBALS['admin_auth']->logout();
    header('Location: login.php');
    exit;
}

function getAdminMessage() {
    $message = $_SESSION['admin_message'] ?? '';
    $type = $_SESSION['admin_message_type'] ?? 'info';
    
    unset($_SESSION['admin_message'], $_SESSION['admin_message_type']);
    
    return ['message' => $message, 'type' => $type];
}

function setAdminMessage($message, $type = 'info') {
    $_SESSION['admin_message'] = $message;
    $_SESSION['admin_message_type'] = $type;
}
?>