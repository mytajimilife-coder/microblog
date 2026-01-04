<?php
/**
 * SEO最適化ヘルパー
 * メタタグ、OGP、構造化データ生成
 */

class SEOHelper {
    
    /**
     * メタタグ生成
     */
    public static function generateMetaTags($data) {
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $keywords = $data['keywords'] ?? '';
        $image = $data['image'] ?? '';
        $url = $data['url'] ?? '';
        $type = $data['type'] ?? 'article';
        
        $html = '';
        
        // 基本メタタグ
        if ($title) {
            $html .= '<title>' . htmlspecialchars($title) . '</title>' . "\n";
            $html .= '<meta name="title" content="' . htmlspecialchars($title) . '">' . "\n";
        }
        
        if ($description) {
            $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        }
        
        if ($keywords) {
            $html .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">' . "\n";
        }
        
        // OGP (Open Graph Protocol)
        $html .= '<meta property="og:type" content="' . htmlspecialchars($type) . '">' . "\n";
        
        if ($title) {
            $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
        }
        
        if ($description) {
            $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
        }
        
        if ($image) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        if ($url) {
            $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
        }
        
        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        
        if ($title) {
            $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
        }
        
        if ($description) {
            $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
        }
        
        if ($image) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        return $html;
    }
    
    /**
     * 構造化データ（JSON-LD）生成
     */
    public static function generateStructuredData($type, $data) {
        $structuredData = [];
        
        switch ($type) {
            case 'article':
                $structuredData = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $data['title'] ?? '',
                    'description' => $data['description'] ?? '',
                    'image' => $data['image'] ?? '',
                    'datePublished' => $data['published_at'] ?? date('c'),
                    'dateModified' => $data['updated_at'] ?? date('c'),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $data['author'] ?? ''
                    ]
                ];
                break;
                
            case 'website':
                $structuredData = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $data['name'] ?? '',
                    'url' => $data['url'] ?? '',
                    'description' => $data['description'] ?? ''
                ];
                break;
                
            case 'breadcrumb':
                $items = [];
                foreach ($data['items'] ?? [] as $index => $item) {
                    $items[] = [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item['name'],
                        'item' => $item['url'] ?? ''
                    ];
                }
                
                $structuredData = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $items
                ];
                break;
        }
        
        return '<script type="application/ld+json">' . "\n" . 
               json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . 
               "\n" . '</script>';
    }
    
    /**
     * スラッグ生成（日本語対応）
     */
    public static function generateSlug($text, $maxLength = 100) {
        // 日本語をローマ字に変換（簡易版）
        $slug = URLHelper::generateSlug($text);
        
        // 空の場合はランダム生成
        if (empty($slug)) {
            $slug = 'post-' . uniqid();
        }
        
        // 長さ制限
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $slug = rtrim($slug, '-');
        }
        
        return $slug;
    }
    
    /**
     * キーワード抽出（簡易版）
     */
    public static function extractKeywords($text, $limit = 10) {
        // HTMLタグ除去
        $text = strip_tags($text);
        
        // 日本語の場合は形態素解析が必要だが、簡易版として単語分割
        $words = preg_split('/[\s、。！？\n\r]+/u', $text);
        
        // 頻度カウント
        $wordCount = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word, 'UTF-8') >= 2) {
                $wordCount[$word] = ($wordCount[$word] ?? 0) + 1;
            }
        }
        
        // 頻度順にソート
        arsort($wordCount);
        
        // 上位を取得
        return array_slice(array_keys($wordCount), 0, $limit);
    }
    
    /**
     * 読了時間計算
     */
    public static function calculateReadingTime($content, $wordsPerMinute = 400) {
        $text = strip_tags($content);
        
        // 日本語の場合は文字数で計算
        $length = mb_strlen($text, 'UTF-8');
        $minutes = ceil($length / $wordsPerMinute);
        
        return max(1, $minutes);
    }
    
    /**
     * 抜粋生成（SEO最適化）
     */
    public static function generateExcerpt($content, $length = 160) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        
        $excerpt = mb_substr($text, 0, $length, 'UTF-8');
        
        // 最後の句点で切る
        $lastPeriod = mb_strrpos($excerpt, '。', 0, 'UTF-8');
        if ($lastPeriod !== false && $lastPeriod > $length * 0.7) {
            $excerpt = mb_substr($excerpt, 0, $lastPeriod + 1, 'UTF-8');
        } else {
            $excerpt .= '...';
        }
        
        return $excerpt;
    }
    
    /**
     * Canonical URL生成
     */
    public static function getCanonicalUrl($url) {
        return '<link rel="canonical" href="' . htmlspecialchars($url) . '">';
    }
    
    /**
     * robots メタタグ生成
     */
    public static function getRobotsTag($index = true, $follow = true) {
        $content = [];
        $content[] = $index ? 'index' : 'noindex';
        $content[] = $follow ? 'follow' : 'nofollow';
        
        return '<meta name="robots" content="' . implode(', ', $content) . '">';
    }
}
?>
