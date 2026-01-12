<div align="center">
  <img src="docs/logo.png" alt="Microblog Logo" width="200"/>
  
  # Microblog
  
  ### 🚀 至高の執筆体験をあなたに
  
  [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
  [![PHP Version](https://img.shields.io/badge/PHP-7.4%20~%208.3-777BB4?logo=php)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql)](https://www.mysql.com/)
  [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-12%2B-336791?logo=postgresql)](https://www.postgresql.org/)
  
  **Microblog**は、個人の執筆体験を極限まで高めるために設計された、軽量かつパワフルな日本語専用ブログエンジンです。
  
  [🌐 デモを見る](https://mytajimilife-coder.github.io/microblog/) | [📖 ドキュメント](#) | [🐛 問題を報告](https://github.com/mytajimilife-coder/microblog/issues)
  
</div>

---

## ✨ 主な特徴

### 🎨 **美しいデザイン**
- **マルチテーマ対応**: 「Sleek Premium」と「Cute Pastel」の2種類のテーマを標準搭載
- **ダークモード**: システム連動の自動切り替え機能
- **レスポンシブデザイン**: あらゆるデバイスで完璧に表示

### 🔐 **プロフェッショナルなセキュリティ**
- **IPファイアウォール**: 不正アクセスを自動ブロック
- **Brute-force対策**: ログイン試行回数制限
- **管理者活動ログ**: すべての操作を記録・監視
- **セキュリティヘッダー**: XSS、CSRF、Clickjacking対策

### 🚀 **高速パフォーマンス**
- **ファイルベースキャッシュ**: 超高速ページ表示
- **画像自動最適化**: アップロード時に自動リサイズ
- **遅延読み込み**: スムーズなユーザー体験

### ✍️ **執筆支援機能**
- **Markdownサポート**: 直感的な記法で執筆
- **自動保存 (AutoSave)**: 作業内容を自動保存
- **投稿予約**: 指定日時に自動公開
- **自動目次 (TOC)**: 見出しから自動生成

### 📊 **SEO・SNS最適化**
- **OGPタグ自動生成**: SNSシェア時の見栄えを最適化
- **JSON-LD構造化データ**: 検索エンジンに最適化
- **日本語スラッグ生成**: URLを自動で最適化
- **Sitemap・RSS**: 自動生成機能

### 🔌 **REST API & 拡張性**
- **REST API**: 外部アプリやモバイルからの投稿・取得が自由自在
- **Headless CMS**: フロントエンドを自由に選択可能
- **プラグインシステム**: 機能を簡単に拡張

### 🛠️ **管理画面**
- **直感的なUI**: 初心者でも簡単に操作可能
- **ワンクリック設定**: SEO、画像最適化、セキュリティをGUIで管理
- **バックアップ・復元**: データを安全に保護
- **アクセス統計**: 訪問者数やページビューを可視化

---

## 🚀 クイックスタート

### 必要要件

- **PHP**: 7.4 ~ 8.3
- **データベース**: MySQL 5.7+ または PostgreSQL 12+
- **拡張機能**: GDライブラリ (画像処理用)

### インストール手順

1. **ファイルをダウンロード**
   ```bash
   git clone https://github.com/mytajimilife-coder/microblog.git
   cd microblog
   ```

2. **Webサーバーにアップロード**
   - ファイルをWebサーバーのドキュメントルートにアップロード

3. **インストーラーを実行**
   - ブラウザで `http://yoursite.com/install/` にアクセス
   - 画面の指示に従ってDB情報を入力

4. **執筆開始！**
   - 管理画面 (`/admin/`) からログイン
   - すぐに記事を書き始められます

---

## 📂 プロジェクト構造

```
microblog/
├── admin/              # プロフェッショナル管理パネル
│   ├── index.php       # ダッシュボード
│   ├── posts.php       # 投稿管理
│   ├── settings.php    # サイト設定
│   ├── security.php    # セキュリティ管理
│   └── ...
├── includes/           # コアロジック
│   ├── api.php         # REST API
│   ├── cache.php       # キャッシュシステム
│   ├── security.php    # セキュリティ機能
│   ├── seo.php         # SEO最適化
│   └── ...
├── themes/             # テーマフォルダ
│   ├── sleek/          # Sleek Premiumテーマ
│   └── cute/           # Cute Pastelテーマ
├── config/             # 設定ファイル
├── install/            # 自動インストーラー
├── docs/               # GitHub Pages ドキュメント
├── api.php             # REST API エントリポイント
├── index.php           # メインフロントエンド
├── rss.php             # RSSフィード
├── sitemap.php         # サイトマップ
└── LICENSE             # MITライセンス
```

---

## 🌍 多言語対応

Microblogは以下の言語に対応しています：

- 🇯🇵 日本語 (Japanese)
- 🇰🇷 한국어 (Korean)
- 🇺🇸 English
- 🇨🇳 中文 (Chinese)

---

## 🤝 貢献

貢献を歓迎します！以下の方法で参加できます：

1. このリポジトリをフォーク
2. 新しいブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

---

## 📄 ライセンス

このプロジェクトは [MIT License](LICENSE) の下でライセンスされています。

---

## 🙏 謝辞

Microblogを使用していただき、ありがとうございます！

問題や提案がある場合は、[Issues](https://github.com/mytajimilife-coder/microblog/issues) でお知らせください。

---

<div align="center">
  Made with ❤️ by TajimiOZ
  
  ⭐ このプロジェクトが気に入ったら、スターをお願いします！
</div>
