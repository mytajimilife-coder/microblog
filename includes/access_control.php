<?php
/**
 * アクセス制御システム (RBAC)
 * ユーザー権限の厳格な管理
 */

class AccessControl {
    private static $roles = [
        'admin'  => ['manage_settings', 'manage_users', 'manage_all_posts', 'manage_categories', 'publish_posts', 'edit_posts', 'delete_posts', 'view_stats'],
        'editor' => ['manage_all_posts', 'manage_categories', 'publish_posts', 'edit_posts', 'delete_posts', 'view_stats'],
        'author' => ['publish_posts', 'edit_own_posts', 'delete_own_posts']
    ];

    /**
     * 現在のユーザーが特定の権限を持っているかチェック
     */
    public static function can($capability, $ownerId = null) {
        if (!isset($_SESSION['user_role'])) return false;
        
        $role = $_SESSION['user_role'];
        if (!isset(self::$roles[$role])) return false;

        // 特権管理者
        if ($role === 'admin') return true;

        $caps = self::$roles[$role];

        // 一般的な権限チェック
        if (in_array($capability, $caps)) return true;

        // 自分のコンテンツに対するチェック
        if ($ownerId !== null && isset($_SESSION['user_id']) && $ownerId == $_SESSION['user_id']) {
            if ($capability === 'edit_posts' && in_array('edit_own_posts', $caps)) return true;
            if ($capability === 'delete_posts' && in_array('delete_own_posts', $caps)) return true;
        }

        return false;
    }

    /**
     * 権限がない場合にアクセスを遮断
     */
    public static function require($capability, $ownerId = null) {
        if (!self::can($capability, $ownerId)) {
            http_response_code(403);
            die('アクセス権限がありません。');
        }
    }
}
?>
