<?php
/**
 * Markdownプロセッサ
 * MarkdownテキストをHTMLに変換（軽量実装）
 */

class MarkdownProcessor {
    
    /**
     * テキストをHTMLにパース
     */
    public static function parse($text) {
        $text = htmlspecialchars($text, ENT_NOQUOTES);

        // 見出し (H1-H6)
        $text = preg_replace('/^###### (.*)$/m', '<h6>$1</h6>', $text);
        $text = preg_replace('/^##### (.*)$/m', '<h5>$1</h5>', $text);
        $text = preg_replace('/^#### (.*)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);

        // 太字・斜体
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // リンク
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $text);

        // 引用
        $text = preg_replace('/^> (.*)$/m', '<blockquote>$1</blockquote>', $text);

        // コードブロック・インラインコード
        $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
        $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);

        // 水平線
        $text = preg_replace('/^---$/m', '<hr>', $text);

        // 改行
        $text = nl2br($text);

        return $text;
    }
}
?>
