<!DOCTYPE html>
<html lang="ja" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?><?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo THEME_PATH; ?>/style.css">
    <script>
        // ダークモード初期化スクリプト (Flash防止のため即時実行)
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <a href="<?php echo url('home'); ?>" class="logo">
                    <?php echo htmlspecialchars(SITE_NAME); ?>
                </a>
                <button onclick="toggleTheme()" class="theme-toggle" id="theme-btn">
                    <span class="mode-icon">🌓</span>
                    <span class="mode-text">テーマ変更</span>
                </button>
            </nav>
        </header>

<script>
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }
</script>
