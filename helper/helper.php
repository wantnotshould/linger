<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

function dd(...$args): void
{
    $exit = true;
    if (count($args) && is_bool(end($args))) {
        $exit = array_pop($args);
    }

    $isCli = php_sapi_name() === 'cli';

    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $caller = $trace[0] ?? null;
    $locationStr = isset($caller['file'], $caller['line'])
        ? "{$caller['file']} 第 {$caller['line']} 行"
        : '[未知位置]';

    if ($isCli) {
        foreach ($args as $i => $arg) {
            echo "\033[36m[调试变量 #" . ($i + 1) . "]\033[0m\n";
            var_dump($arg);
            echo "\n";
        }
        echo "\033[33m调试位置:\033[0m{$locationStr}\n";

        if ($exit) {
            exit(1);
        }
        return;
    }

    if (ob_get_level()) ob_end_clean();

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>调试输出</title>
<style>
    body {
        color: #1f2328;
        padding: 20px;
    }
    .ld-container {
        background-color: #fff;
        color: #1f2328;
        padding: 16px;
        margin: 16px 0;
        border-radius: 6px;
        font-size: 14px;
        white-space: pre-wrap;
        word-wrap: break-word;
        box-shadow: 0 2px 3px rgba(0,0,0,0.05);
    }
    .ld-location {
        background: #ddf4ff;
        color: #1f2328;
        padding: 10px 16px;
        margin: 16px 0;
        border-radius: 4px;
        font-weight: bold;
    }
    details {
        margin: 16px 0;
        background: #f6f8fa;
        padding: 12px;
        border-radius: 6px;
        color: #1f2328;
        border: 1px solid #ddd;
    }
    summary {
        cursor: pointer;
        font-weight: bold;
        color: #0969da;
    }
</style></head><body>
HTML;

    foreach ($args as $i => $arg) {
        echo '<details open>';
        echo '<summary>调试变量 #' . ($i + 1) . '</summary>';
        echo '<div class="ld-container">';
        ob_start();
        var_dump($arg);
        echo htmlspecialchars(ob_get_clean(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo '</div></details>';
    }

    echo '<div class="ld-location">调试位置: ' . htmlspecialchars($locationStr) . '</div>';
    echo '</body></html>';

    if ($exit) {
        exit(1);
    }
}
