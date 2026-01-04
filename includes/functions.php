<?php
/**
 * 共通関数ライブラリ
 * ユーティリティ関数群
 */

/**
 * 日本語テキスト処理
 */
class JapaneseTextProcessor {
    
    /**
     * 日本語テキストの文字数計算
     */
    public static function mb_strlen_safe($string, $encoding = 'UTF-8') {
        return mb_strlen($string, $encoding);
    }
    
    /**
     * 日本語対応のテキスト切り詰め
     */
    public static function truncate($text, $length = 100, $suffix = '...', $encoding = 'UTF-8') {
        if (mb_strlen($text, $encoding) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length, $encoding) . $suffix;
    }
    
    /**
     * 日本語対応の文字列検索
     */
    public static function mb_strpos($haystack, $needle, $offset = 0, $encoding = 'UTF-8') {
        return mb_strpos($haystack, $needle, $offset, $encoding);
    }
    
    /**
     * 日本語対応の文字列置換
     */
    public static function mb_str_replace($search, $replace, $subject, $encoding = 'UTF-8') {
        return mb_ereg_replace($search, $replace, $subject);
    }
}

/**
 * URL 生成・処理
 */
class URLHelper {
    
    /**
     * スラッグ生成（日本語対応）
     */
    public static function generateSlug($text, $encoding = 'UTF-8') {
        // 全角文字を半角に変換
        $text = mb_convert_kana($text, 'a', $encoding);
        
        // 日本語文字を、ローマ字に変換（簡易版）
        $text = self::japaneseToRoman($text);
        
        // 特殊文字を除去
        $text = preg_replace('/[^a-zA-Z0-9\-]/', '-', $text);
        
        // 複数のハイフンを1つに
        $text = preg_replace('/-+/', '-', $text);
        
        // 先頭・末尾のハイフンを除去
        $text = trim($text, '-');
        
        return strtolower($text);
    }
    
    /**
     * 簡易日本語→ローマ字変換
     */
    private static function japaneseToRoman($text) {
        // 基本的なひらがな、カタカナの変換
        $map = [
            // ひらがな
            'あ' => 'a', 'い' => 'i', 'う' => 'u', 'え' => 'e', 'お' => 'o',
            'か' => 'ka', 'き' => 'ki', 'く' => 'ku', 'け' => 'ke', 'こ' => 'ko',
            'さ' => 'sa', 'し' => 'shi', 'す' => 'su', 'せ' => 'se', 'そ' => 'so',
            'た' => 'ta', 'ち' => 'chi', 'つ' => 'tsu', 'て' => 'te', 'と' => 'to',
            'な' => 'na', 'に' => 'ni', 'ぬ' => 'nu', 'ね' => 'ne', 'の' => 'no',
            'は' => 'ha', 'ひ' => 'hi', 'ふ' => 'fu', 'へ' => 'he', 'ほ' => 'ho',
            'ま' => 'ma', 'み' => 'mi', 'む' => 'mu', 'め' => 'me', 'も' => 'mo',
            'や' => 'ya', 'ゆ' => 'yu', 'よ' => 'yo',
            'ら' => 'ra', 'り' => 'ri', 'る' => 'ru', 'れ' => 're', 'ろ' => 'ro',
            'わ' => 'wa', 'を' => 'wo', 'ん' => 'n',
            
            // カタカナ
            'ア' => 'a', 'イ' => 'i', 'ウ' => 'u', 'エ' => 'e', 'オ' => 'o',
            'カ' => 'ka', 'キ' => 'ki', 'ク' => 'ku', 'ケ' => 'ke', 'コ' => 'ko',
            'サ' => 'sa', 'シ' => 'shi', 'ス' => 'su', 'セ' => 'se', 'ソ' => 'so',
            'タ' => 'ta', 'チ' => 'chi', 'ツ' => 'tsu', 'テ' => 'te', 'ト' => 'to',
            'ナ' => 'na', 'ニ' => 'ni', 'ヌ' => 'nu', 'ネ' => 'ne', 'ノ' => 'no',
            'ハ' => 'ha', 'ヒ' => 'hi', 'フ' => 'fu', 'ヘ' => 'he', 'ホ' => 'ho',
            'マ' => 'ma', 'ミ' => 'mi', 'ム' => 'mu', 'メ' => 'me', 'モ' => 'mo',
            'ヤ' => 'ya', 'ユ' => 'yu', 'ヨ' => 'yo',
            'ラ' => 'ra', 'リ' => 'ri', 'ル' => 'ru', 'レ' => 're', 'ロ' => 'ro',
            'ワ' => 'wa', 'ヲ' => 'wo', 'ン' => 'n',
        ];
        
        foreach ($map as $jp => $roman) {
            $text = str_replace($jp, $roman, $text);
        }
        
        return $text;
    }
    
    /**
     * URL構築
     */
    public static function build($path = '', $params = []) {
        $url = BASE_URL . ltrim($path, '/');
        
        if (!empty($params)) {
            $query = http_build_query($params);
            $url .= '?' . $query;
        }
        
        return $url;
    }
    
    /**
     * 現在のURL取得
     */
    public static function current() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        return $protocol . '://' . $host . $uri;
    }
}

/**
 * ファイル処理
 */
class FileHelper {
    
    /**
     * ファイルのアップロード処理
     */
    public static function uploadFile($file, $destination = null) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'ファイルアップロードエラー'];
        }
        
        // ファイルサイズチェック
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'ファイルサイズが大きすぎます'];
        }
        
        // 拡張子チェック
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => '許可されていないファイル形式です'];
        }
        
        // ファイル名生成
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // アップロード先
        if ($destination === null) {
            $destination = UPLOAD_PATH;
        }
        
        // ディレクトリ作成
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $uploadPath = $destination . $filename;
        
        // ファイル移動
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $uploadPath,
                'url' => url($uploadPath)
            ];
        } else {
            return ['success' => false, 'message' => 'ファイルの保存に失敗しました'];
        }
    }
    
    /**
     * ファイル削除
     */
    public static function deleteFile($filepath) {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * ファイルサイズFormatted表示
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * 日時処理
 */
class DateTimeHelper {
    
    /**
     * 日本時間での日付 форматия
     */
    public static function format($timestamp, $format = 'Y-m-d H:i:s') {
        return date($format, $timestamp);
    }
    
    /**
     * 日本語日付 форматия
     */
    public static function formatJapanese($timestamp) {
        $date = date('Y年n月j日', $timestamp);
        $time = date('H:i', $timestamp);
        return $date . ' ' . $time;
    }
    
    /**
     * 相対時間表示（日本語）
     */
    public static function timeAgo($timestamp) {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'たった今';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . '分前';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . '時間前';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . '日前';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . 'ヶ月前';
        } else {
            $years = floor($diff / 31536000);
            return $years . '年前';
        }
    }
}

/**
 * セキュリティ関連
 */
class SecurityHelper {
    
    /**
     * CSRF トークン生成・検証
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * XSS対策
     */
    public static function sanitize($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * パスワード強度チェック
     */
    public static function checkPasswordStrength($password) {
        $score = 0;
        $feedback = [];
        
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = '8文字以上で入力してください';
        }
        
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = '小文字を含めてください';
        }
        
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = '大文字を含めてください';
        }
        
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = '数字を含めてください';
        }
        
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = '特殊文字を含めてください';
        }
        
        return [
            'score' => $score,
            'strength' => $score < 3 ? 'weak' : ($score < 4 ? 'medium' : 'strong'),
            'feedback' => $feedback
        ];
    }
}

/**
 * HTML出力支援
 */
class HTMLHelper {
    
    /**
     * 入力値のエスケープ
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * ページネーションHTML生成
     */
    public static function pagination($currentPage, $totalPages, $baseUrl, $params = []) {
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav class="pagination"><ul>';
        
        // 前へ
        if ($currentPage > 1) {
            $prevParams = array_merge($params, ['page' => $currentPage - 1]);
            $html .= '<li><a href="' . htmlspecialchars(URLHelper::build($baseUrl, $prevParams)) . '">前へ</a></li>';
        }
        
        // ページ番号
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li><a href="' . htmlspecialchars(URLHelper::build($baseUrl, array_merge($params, ['page' => 1]))) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li><span>...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $class = $i == $currentPage ? ' class="active"' : '';
            $pageParams = array_merge($params, ['page' => $i]);
            $html .= '<li' . $class . '><a href="' . htmlspecialchars(URLHelper::build($baseUrl, $pageParams)) . '">' . $i . '</a></li>';
        }
        
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li><span>...</span></li>';
            }
            $html .= '<li><a href="' . htmlspecialchars(URLHelper::build($baseUrl, array_merge($params, ['page' => $totalPages]))) . '">' . $totalPages . '</a></li>';
        }
        
        // 次へ
        if ($currentPage < $totalPages) {
            $nextParams = array_merge($params, ['page' => $currentPage + 1]);
            $html .= '<li><a href="' . htmlspecialchars(URLHelper::build($baseUrl, $nextParams)) . '">次へ</a></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * 抜粋生成
     */
    public static function excerpt($content, $length = 150) {
        // HTMLタグを除去
        $text = strip_tags($content);
        
        // 日本語対応の切り詰め
        return JapaneseTextProcessor::truncate($text, $length, '...');
    }
}

/**
 * バリデーション
 */
class Validator {
    
    /**
     * メールアドレスValidation
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * URL Validation
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * 日本語文字列Validation
     */
    public static function japaneseText($text, $minLength = 0, $maxLength = null) {
        $length = JapaneseTextProcessor::mb_strlen_safe($text);
        
        if ($length < $minLength) {
            return ['valid' => false, 'message' => '文字数が不足しています'];
        }
        
        if ($maxLength !== null && $length > $maxLength) {
            return ['valid' => false, 'message' => '文字数が多すぎます'];
        }
        
        return ['valid' => true];
    }
}
?>