<?php
/**
 * ソーシャル共有ヘルパー
 * SNS共有ボタン生成
 */

class SocialShare {
    
    /**
     * 共有ボタンHTML生成
     */
    public static function generateButtons($url, $title, $description = '') {
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);
        $encodedDescription = urlencode($description);
        
        $html = '<div class="social-share-buttons">';
        
        // Twitter
        $twitterUrl = "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}";
        $html .= self::createButton('Twitter', $twitterUrl, '#1DA1F2', '
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
            </svg>
        ');
        
        // Facebook
        $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
        $html .= self::createButton('Facebook', $facebookUrl, '#1877F2', '
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
            </svg>
        ');
        
        // LINE
        $lineUrl = "https://social-plugins.line.me/lineit/share?url={$encodedUrl}";
        $html .= self::createButton('LINE', $lineUrl, '#00B900', '
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
            </svg>
        ');
        
        // はてなブックマーク
        $hatenaUrl = "https://b.hatena.ne.jp/entry/{$encodedUrl}";
        $html .= self::createButton('はてブ', $hatenaUrl, '#00A4DE', '
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9.5 16.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm0-4c-1.93 0-3.5-1.57-3.5-3.5S7.57 5.5 9.5 5.5 13 7.07 13 9s-1.57 3.5-3.5 3.5zm7.5 4.5h-2v-8h2v8z"/>
            </svg>
        ');
        
        // Pocket
        $pocketUrl = "https://getpocket.com/edit?url={$encodedUrl}&title={$encodedTitle}";
        $html .= self::createButton('Pocket', $pocketUrl, '#EF3F56', '
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.813 10l-5.667 5.667a1.002 1.002 0 01-1.414 0L6.065 10H4v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8h-1.187zM6 4v4h12V4H6z"/>
            </svg>
        ');
        
        // コピーボタン
        $html .= self::createCopyButton($url);
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * 個別ボタン作成
     */
    private static function createButton($name, $url, $color, $icon) {
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="share-btn" style="background-color: %s;" title="%sで共有">
                %s
                <span>%s</span>
            </a>',
            htmlspecialchars($url),
            $color,
            htmlspecialchars($name),
            $icon,
            htmlspecialchars($name)
        );
    }
    
    /**
     * URLコピーボタン
     */
    private static function createCopyButton($url) {
        return sprintf(
            '<button class="share-btn copy-btn" data-url="%s" style="background-color: #6c757d;" title="URLをコピー">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                <span>コピー</span>
            </button>',
            htmlspecialchars($url)
        );
    }
    
    /**
     * スタイルとスクリプト
     */
    public static function getAssets() {
        return '
        <style>
        .social-share-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }
        
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .share-btn svg {
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .share-btn span {
                display: none;
            }
            .share-btn {
                padding: 10px;
            }
        }
        </style>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // URLコピー機能
            document.querySelectorAll(".copy-btn").forEach(function(btn) {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    var url = this.getAttribute("data-url");
                    
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(url).then(function() {
                            alert("URLをコピーしました！");
                        });
                    } else {
                        // フォールバック
                        var textarea = document.createElement("textarea");
                        textarea.value = url;
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand("copy");
                        document.body.removeChild(textarea);
                        alert("URLをコピーしました！");
                    }
                });
            });
        });
        </script>
        ';
    }
}
?>
