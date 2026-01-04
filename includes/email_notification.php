<?php
/**
 * メール通知システム
 * コメント、予約投稿などの通知
 */

class EmailNotification {
    private $from;
    private $fromName;
    
    public function __construct($from = null, $fromName = null) {
        $this->from = $from ?? 'noreply@' . $_SERVER['HTTP_HOST'];
        $this->fromName = $fromName ?? SITE_NAME;
    }
    
    /**
     * メール送信
     */
    public function send($to, $subject, $message, $isHtml = true) {
        $headers = [];
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->from . '>';
        $headers[] = 'Reply-To: ' . $this->from;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        if ($isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }
        
        $result = @mail($to, $subject, $message, implode("\r\n", $headers));
        
        return [
            'success' => $result,
            'message' => $result ? 'メールを送信しました' : 'メール送信に失敗しました'
        ];
    }
    
    /**
     * 新規コメント通知
     */
    public function notifyNewComment($post, $comment, $adminEmail) {
        $subject = '【' . SITE_NAME . '】新しいコメントがあります';
        
        $message = $this->getEmailTemplate([
            'title' => '新しいコメント通知',
            'content' => '
                <p><strong>記事:</strong> ' . htmlspecialchars($post['title']) . '</p>
                <p><strong>投稿者:</strong> ' . htmlspecialchars($comment['author_name']) . '</p>
                <p><strong>コメント:</strong></p>
                <div style="background: #f5f5f5; padding: 15px; border-left: 3px solid #3498db;">
                    ' . nl2br(htmlspecialchars($comment['content'])) . '
                </div>
                <p style="margin-top: 20px;">
                    <a href="' . url('admin/comments.php') . '" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        コメントを確認
                    </a>
                </p>
            '
        ]);
        
        return $this->send($adminEmail, $subject, $message);
    }
    
    /**
     * 予約投稿公開通知
     */
    public function notifyScheduledPublished($post, $authorEmail) {
        $subject = '【' . SITE_NAME . '】予約投稿が公開されました';
        
        $message = $this->getEmailTemplate([
            'title' => '予約投稿公開通知',
            'content' => '
                <p>予約していた記事が公開されました。</p>
                <p><strong>記事タイトル:</strong> ' . htmlspecialchars($post['title']) . '</p>
                <p><strong>公開日時:</strong> ' . date('Y年m月d日 H:i', strtotime($post['published_at'])) . '</p>
                <p style="margin-top: 20px;">
                    <a href="' . url('?action=post&id=' . $post['id']) . '" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        記事を見る
                    </a>
                </p>
            '
        ]);
        
        return $this->send($authorEmail, $subject, $message);
    }
    
    /**
     * パスワードリセット通知
     */
    public function sendPasswordReset($email, $resetToken, $resetUrl) {
        $subject = '【' . SITE_NAME . '】パスワードリセット';
        
        $message = $this->getEmailTemplate([
            'title' => 'パスワードリセット',
            'content' => '
                <p>パスワードリセットのリクエストを受け付けました。</p>
                <p>以下のリンクをクリックして、新しいパスワードを設定してください。</p>
                <p style="margin-top: 20px;">
                    <a href="' . htmlspecialchars($resetUrl) . '" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        パスワードをリセット
                    </a>
                </p>
                <p style="margin-top: 20px; color: #666; font-size: 12px;">
                    このリンクは24時間有効です。<br>
                    心当たりがない場合は、このメールを無視してください。
                </p>
            '
        ]);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * 週次レポート
     */
    public function sendWeeklyReport($adminEmail, $stats) {
        $subject = '【' . SITE_NAME . '】週次レポート';
        
        $message = $this->getEmailTemplate([
            'title' => '週次レポート',
            'content' => '
                <h2>今週の統計</h2>
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px; border: 1px solid #ddd;">新規投稿</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>' . $stats['new_posts'] . '</strong>件</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">新規コメント</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>' . $stats['new_comments'] . '</strong>件</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px; border: 1px solid #ddd;">総閲覧数</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>' . number_format($stats['total_views']) . '</strong>回</td>
                    </tr>
                </table>
                <p style="margin-top: 20px;">
                    <a href="' . url('admin/statistics.php') . '" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        詳細を見る
                    </a>
                </p>
            '
        ]);
        
        return $this->send($adminEmail, $subject, $message);
    }
    
    /**
     * メールテンプレート
     */
    private function getEmailTemplate($data) {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        
        return '
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: \'Hiragino Sans\', \'Yu Gothic\', \'Meiryo\', sans-serif; background: #f5f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f7fa; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- ヘッダー -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 24px;">' . htmlspecialchars(SITE_NAME) . '</h1>
                        </td>
                    </tr>
                    
                    <!-- コンテンツ -->
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="margin-top: 0; color: #2c3e50;">' . htmlspecialchars($title) . '</h2>
                            ' . $content . '
                        </td>
                    </tr>
                    
                    <!-- フッター -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px;">
                            <p style="margin: 0;">© ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME) . '</p>
                            <p style="margin: 5px 0 0 0;">
                                <a href="' . BASE_URL . '" style="color: #3498db; text-decoration: none;">サイトを見る</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        ';
    }
}
?>
