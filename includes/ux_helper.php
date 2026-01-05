<?php
/**
 * UX Helper
 * ユーザー体験向上のためのユーティリティ
 */

class UXHelper {
    
    /**
     * 読了時間の推定
     * @param string $content 記事本文
     * @param int $charsPerMinute 1分あたりの読書文字数（日本語平均 500〜600）
     * @return int 推定読了時間（分）
     */
    public static function estimateReadingTime($content, $charsPerMinute = 500) {
        $text = strip_tags($content);
        // 空白や改行を除去して純粋な文字数をカウント
        $cleanText = preg_replace('/\s+/', '', $text);
        $charCount = mb_strlen($cleanText, 'UTF-8');
        
        $minutes = ceil($charCount / $charsPerMinute);
        return max(1, $minutes);
    }
    
    /**
     * 読了時間表示用のHTML
     */
    public static function getReadingTimeHTML($content) {
        $minutes = self::estimateReadingTime($content);
        return sprintf(
            '<span class="reading-time">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                読了目安: 約 %d 分
            </span>',
            $minutes
        );
    }
    
    /**
     * 読書進捗バーのAssets
     */
    public static function getReadingProgressAssets() {
        return '
        <style>
        #reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
            z-index: 9999;
            transition: width 0.1s ease;
        }
        </style>
        <div id="reading-progress"></div>
        <script>
        window.addEventListener("scroll", function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = (winScroll / height) * 100;
            document.getElementById("reading-progress").style.width = scrolled + "%";
        });
        </script>
        ';
    /**
     * 検索ワードのハイライト
     */
    public static function highlightSearchTerms($text, $query) {
        if (empty($query)) return $text;
        
        $query = preg_quote($query, '/');
        return preg_replace('/(' . $query . ')/iu', '<mark class="search-highlight">$1</mark>', $text);
    }
}
