<?php
/**
 * フック・プラグインシステム
 * アクションとフィルターの管理
 */

class Hooks {
    private static $actions = [];
    private static $filters = [];

    /**
     * アクションを追加
     */
    public static function addAction($tag, $callback, $priority = 10) {
        if (!isset(self::$actions[$tag])) {
            self::$actions[$tag] = [];
        }
        self::$actions[$tag][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        // 優先度でソート
        usort(self::$actions[$tag], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * アクションを実行
     */
    public static function doAction($tag, ...$args) {
        if (!isset(self::$actions[$tag])) return;

        foreach (self::$actions[$tag] as $hook) {
            call_user_func_array($hook['callback'], $args);
        }
    }

    /**
     * フィルターを追加
     */
    public static function addFilter($tag, $callback, $priority = 10) {
        if (!isset(self::$filters[$tag])) {
            self::$filters[$tag] = [];
        }
        self::$filters[$tag][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        // 優先度でソート
        usort(self::$filters[$tag], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * フィルターを適用
     */
    public static function applyFilters($tag, $value, ...$args) {
        if (!isset(self::$filters[$tag])) return $value;

        foreach (self::$filters[$tag] as $hook) {
            $value = call_user_func_array($hook['callback'], array_merge([$value], $args));
        }

        return $value;
    }
}
?>
