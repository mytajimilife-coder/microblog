<?php
/**
 * TOC（目次）ジェネレーター
 * 投稿内の見出しから自動的に目次を作成
 */

class TOCGenerator {
    
    /**
     * コンテンツから目次を抽出
     */
    public static function generate($content) {
        preg_match_all('/<(h[2-4])>(.*?)<\/h[2-4]>/i', $content, $matches, PREG_SET_ORDER);
        
        if (empty($matches)) return '';

        $toc = '<div class="post-toc">';
        $toc .= '<h3>目次</h3>';
        $toc .= '<ul>';
        
        foreach ($matches as $match) {
            $tag = $match[1];
            $title = strip_tags($match[2]);
            $slug = self::slugify($title);
            
            $toc .= '<li class="toc-' . $tag . '">';
            $toc .= '<a href="#' . $slug . '">' . htmlspecialchars($title) . '</a>';
            $toc .= '</li>';
        }
        
        $toc .= '</ul></div>';
        
        return $toc;
    }

    /**
     * コンテンツの見出しにIDを付与
     */
    public static function injectIDs($content) {
        return preg_replace_callback('/<(h[2-4])>(.*?)<\/h[2-4]>/i', function($matches) {
            $tag = $matches[1];
            $title = $matches[2];
            $slug = self::slugify(strip_tags($title));
            return "<{$tag} id=\"{$slug}\">{$title}</{$tag}>";
        }, $content);
    }

    private static function slugify($text) {
        return 'toc-' . md5($text);
    }
}
?>
