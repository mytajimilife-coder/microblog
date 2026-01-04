<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メンテナンス中 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body {
            font-family: 'Hiragino Sans', 'Yu Gothic', sans-serif;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #2c3e50;
            text-align: center;
        }
        .container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 90%;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        p {
            line-height: 1.6;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        .timer {
            font-size: 14px;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🛠️</div>
        <h1>メンテナンス中です</h1>
        <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
        <div class="timer">再開まで今しばらくお待ちください。</div>
    </div>
</body>
</html>
