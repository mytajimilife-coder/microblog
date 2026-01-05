<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title ?? $siteInfo['name']); ?></title>
    <meta name="description" content="<?php echo h($seoDescription ?? $siteInfo['description']); ?>">
    <meta name="keywords" content="ブログ, blog, microblog, 日本语, 日本語">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4facfe">
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>

    <?php if (isset($action) && $action === 'post'): ?>
        <?php echo UXHelper::getReadingProgressAssets(); ?>
    <?php endif; ?>
    
    <!-- Open Graph Meta Tags -->
    <?php if (isset($seoImage)): ?>
        <meta property="og:image" content="<?php echo h($seoImage); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo h($seoTitle ?? $title ?? $siteInfo['name']); ?>">
    <meta property="og:description" content="<?php echo h($seoDescription ?? $siteInfo['description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo h(URLHelper::current()); ?>">
    <meta property="og:site_name" content="<?php echo h($siteInfo['name']); ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($seoTitle ?? $title ?? $siteInfo['name']); ?>">
    <meta name="twitter:description" content="<?php echo h($seoDescription ?? $siteInfo['description']); ?>">
    <?php if (isset($seoImage)): ?>
        <meta name="twitter:image" content="<?php echo h($seoImage); ?>">
    <?php endif; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?php echo JAPANESE_FONT_FAMILY; ?>;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .site-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
        }
        
        .site-title:hover {
            color: #3498db;
        }
        
        .site-description {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }
        
        nav a {
            text-decoration: none;
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        nav a:hover {
            background-color: #ecf0f1;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .content-area {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .widget {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .widget-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .post {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .post:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .post-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .post-title a {
            color: #2c3e50;
            text-decoration: none;
        }
        
        .post-title a:hover {
            color: #3498db;
        }
        
        .post-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .post-excerpt {
            color: #555;
            line-height: 1.7;
            margin-bottom: 15px;
        }
        
        .read-more {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .read-more:hover {
            text-decoration: underline;
        }
        
        .featured-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .post-content {
            line-height: 1.8;
            color: #444;
        }
        
        .post-content h1,
        .post-content h2,
        .post-content h3,
        .post-content h4,
        .post-content h5,
        .post-content h6 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .post-content h1 { font-size: 28px; }
        .post-content h2 { font-size: 24px; }
        .post-content h3 { font-size: 20px; }
        
        .post-content p {
            margin-bottom: 15px;
        }
        
        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .post-content blockquote {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px 20px;
            margin: 20px 0;
            font-style: italic;
        }
        
        .post-content ul,
        .post-content ol {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        .post-content li {
            margin-bottom: 8px;
        }
        
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .tag:hover {
            background: #3498db;
            color: white;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-item {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        /* Comment Widget */
        .comment-widget-list {
            list-style: none;
            padding: 0;
        }

        .comment-widget-item {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #ecf0f1;
        }

        .comment-widget-item:last-child {
            border-bottom: none;
        }

        .comment-widget-item a {
            text-decoration: none;
            color: #2c3e50;
            display: block;
        }

        .comment-widget-item a:hover .post-title-mini {
            color: #3498db;
        }

        .post-title-mini {
            font-size: 13px;
            color: #666;
            font-style: italic;
        }

        .comment-excerpt-mini {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 4px;
            line-height: 1.4;
        }
        
        .category-item a {
            color: #2c3e50;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-item a:hover {
            color: #3498db;
        }
        
        .pagination {
            margin-top: 30px;
            text-align: center;
        }
        
        .pagination-list {
            display: inline-flex;
            list-style: none;
            gap: 5px;
        }
        
        .pagination-item {
            margin: 0;
        }
        
        .pagination-link {
            display: block;
            padding: 10px 15px;
            background: white;
            border: 1px solid #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination-link:hover,
        .pagination-link-active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .pagination-ellipsis {
            padding: 10px;
            color: #666;
        }
        
        footer {
            background: #2c3e50;
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 50px;
        }
        
        .error-page {
            text-align: center;
            padding: 50px 20px;
        }
        
        .error-title {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .back-home {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .back-home:hover {
            background: #2980b9;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .content-area,
            .widget {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="site-info">
                    <a href="<?php echo url(); ?>" class="site-title"><?php echo h($siteInfo['name']); ?></a>
                    <p class="site-description"><?php echo h($siteInfo['description']); ?></p>
                </div>
                <nav>
                    <ul>
                        <li><a href="<?php echo url(); ?>">ホーム</a></li>
                        <li><a href="<?php echo url('categories'); ?>">カテゴリー</a></li>
                        <li><a href="<?php echo url('tags'); ?>">タグ</a></li>
                        <li><a href="<?php echo url('contact'); ?>">お問い合わせ</a></li>
                        <li><a href="<?php echo url('admin'); ?>">管理</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <main class="content-area">