<?php
/**
 * サイドバーヘルパー
 * サイドバーに表示するデータを取得
 */

class SidebarHelper {
    
    /**
     * 最近のコメントを取得
     */
    public static function getRecentComments($pdo, $limit = 5) {
        $commentModel = new Comment($pdo);
        return $commentModel->getRecent($limit);
    }
}
