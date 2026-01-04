<?php
/**
 * パンくずリスト生成ヘルパー
 * SEOとユーザビリティの向上
 */

class Breadcrumb {
    private $items = [];
    private $separator = ' &gt; ';

    public function __construct() {
        // ホームをデフォルトで追加
        $this->add('ホーム', url('home'));
    }

    /**
     * 項目を追加
     */
    public function add($name, $url = null) {
        $this->items[] = [
            'name' => $name,
            'url' => $url
        ];
        return $this;
    }

    /**
     * 投稿データから自動生成
     */
    public function fromPost($post, $categories = []) {
        if (!empty($categories)) {
            // 最初のカテゴリーを追加
            $cat = $categories[0];
            $this->add($cat['name'], url('?action=category&category=' . $cat['slug']));
        }
        $this->add($post['title']);
        return $this;
    }

    /**
     * HTMLを出力
     */
    public function render() {
        if (empty($this->items)) return '';

        $html = '<nav aria-label="breadcrumb" class="breadcrumb-nav">';
        $html .= '<ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

        foreach ($this->items as $index => $item) {
            $isLast = ($index === count($this->items) - 1);
            $position = $index + 1;

            $html .= '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            
            if ($item['url'] && !$isLast) {
                $html .= '<a itemprop="item" href="' . htmlspecialchars($item['url']) . '">';
                $html .= '<span itemprop="name">' . htmlspecialchars($item['name']) . '</span>';
                $html .= '</a>';
            } else {
                $html .= '<span itemprop="name" class="current">' . htmlspecialchars($item['name']) . '</span>';
            }
            
            $html .= '<meta itemprop="position" content="' . $position . '">';
            $html .= '</li>';

            if (!$isLast) {
                $html .= '<span class="separator">' . $this->separator . '</span>';
            }
        }

        $html .= '</ol></nav>';
        
        $html .= '<style>
            .breadcrumb-nav { margin-bottom: 20px; font-size: 14px; color: #7f8c8d; }
            .breadcrumb-list { list-style: none; padding: 0; display: flex; flex-wrap: wrap; align-items: center; }
            .breadcrumb-item { display: inline; }
            .breadcrumb-item a { color: #3498db; text-decoration: none; }
            .breadcrumb-item a:hover { text-decoration: underline; }
            .breadcrumb-item .current { color: #2c3e50; font-weight: 500; }
            .separator { margin: 0 8px; color: #bdc3c7; }
        </style>';

        return $html;
    }
}
?>
