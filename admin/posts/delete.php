<?php
/**
 * 投稿削除処理
 */

requirePermission('author');

try {
    // CSRFトークン検証
    $adminAuth = new AdminAuth($pdo);
    $adminAuth->validateCSRF();
    
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('投稿IDが無効です');
    }
    
    // 投稿存在確認
    $post = $postModel->find($id);
    if (!$post) {
        throw new Exception('投稿が見つかりません');
    }
    
    // 権限チェック
    $currentUser = getCurrentUser();
    if ($post['author_id'] != $currentUser['id'] && !hasPermission('editor')) {
        throw new Exception('この投稿を削除する権限がありません');
    }
    
    // アイキャッチ画像削除
    if ($post['featured_image']) {
        FileHelper::deleteFile($post['featured_image']);
    }
    
    // データベースから削除（関連データはCASCADEで自動削除）
    $postModel->delete($id);
    
    setAdminMessage('投稿を削除しました', 'success');
    
} catch (Exception $e) {
    setAdminMessage($e->getMessage(), 'error');
}

header('Location: index.php?action=posts');
exit;
?>