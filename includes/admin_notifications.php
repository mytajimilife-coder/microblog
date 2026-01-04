<?php
/**
 * 管理者通知センター
 */

class AdminNotifications {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * すべての未処理通知件数を取得
     */
    public function getGlobalCount() {
        $count = 0;
        
        // 未承認コメント
        $res = $this->db->fetch("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'");
        $count += (int)$res['count'];
        
        // 未読お問い合わせ
        $res = $this->db->fetch("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
        $count += (int)$res['count'];
        
        return $count;
    }

    /**
     * 通知リストを生成
     */
    public function getNotificationList() {
        $notifications = [];
        
        // 未承認コメント
        $comments = $this->db->fetch("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'");
        if ($comments['count'] > 0) {
            $notifications[] = [
                'type' => 'comment',
                'title' => '承認待ちコメント',
                'text' => $comments['count'] . '件のコメントが承認を待っています。',
                'url' => 'comments.php',
                'icon' => '💬',
                'priority' => 'high'
            ];
        }
        
        // 未読メッセージ
        $messages = $this->db->fetch("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
        if ($messages['count'] > 0) {
            $notifications[] = [
                'type' => 'contact',
                'title' => '未読お問い合わせ',
                'text' => $messages['count'] . '件の新しいメッセージがあります。',
                'url' => 'contact.php',
                'icon' => '✉️',
                'priority' => 'medium'
            ];
        }

        // バックアップリマインダー（1週間以上前の場合）
        $lastBackup = $this->db->fetch("SELECT MAX(created_at) as last FROM activity_logs WHERE action = 'backup'");
        if (!$lastBackup['last'] || strtotime($lastBackup['last']) < strtotime('-7 days')) {
            $notifications[] = [
                'type' => 'system',
                'title' => 'バックアップ推奨',
                'text' => '前回のバックアップから1週間以上経過しています。',
                'url' => 'backup.php',
                'icon' => '💾',
                'priority' => 'low'
            ];
        }
        
        return $notifications;
    }
}
?>
