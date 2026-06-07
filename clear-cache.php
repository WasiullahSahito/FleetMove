<?php
/**
 * Cache Cleaner v2 — auto-detects Laravel root
 * Upload to /public_html, visit it, then DELETE immediately.
 */

// ── Auto-detect Laravel root ──────────────────────────────────────────
$candidates = [
    __DIR__ . '/../',
    __DIR__ . '/../../',
    __DIR__ . '/../laravel/',
    __DIR__ . '/',
];

$laravelRoot = null;
foreach ($candidates as $path) {
    $real = realpath($path);
    if ($real && file_exists($real . '/artisan')) {
        $laravelRoot = rtrim($real, '/') . '/';
        break;
    }
}

$results = [];

if (!$laravelRoot) {
    $results[] = "❌ Could not find Laravel root (artisan file not found in any candidate path).";
    $results[] = "📁 __DIR__ = " . __DIR__;
    $parent = scandir(__DIR__ . '/../') ?: [];
    $results[] = "📁 Parent dir contents: " . implode(', ', array_slice($parent, 0, 30));
    $current = scandir(__DIR__) ?: [];
    $results[] = "📁 Current dir contents: " . implode(', ', array_slice($current, 0, 30));
} else {
    $results[] = "✅ Laravel root: <strong>{$laravelRoot}</strong>";

    // 1. Compiled views
    $viewsPath = $laravelRoot . 'storage/framework/views/';
    if (is_dir($viewsPath)) {
        $files = glob($viewsPath . '*.php') ?: [];
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) { unlink($file); $count++; }
        }
        $results[] = "✅ Views cleared: {$count} files deleted";
    } else {
        $results[] = "⚠️ Views dir not found: {$viewsPath}";
    }

    // 2. Route cache
    $cleared = false;
    foreach (['routes-v7.php', 'routes.php'] as $f) {
        $p = $laravelRoot . 'bootstrap/cache/' . $f;
        if (file_exists($p)) { unlink($p); $results[] = "✅ Route cache cleared ({$f})"; $cleared = true; break; }
    }
    if (!$cleared) $results[] = "ℹ️ No route cache file found";

    // 3. Config cache
    $p = $laravelRoot . 'bootstrap/cache/config.php';
    if (file_exists($p)) { unlink($p); $results[] = "✅ Config cache cleared"; }
    else $results[] = "ℹ️ No config cache found";

    // 4. App cache
    $cachePath = $laravelRoot . 'storage/framework/cache/data/';
    if (is_dir($cachePath)) {
        $count = 0;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isFile()) { unlink($file->getPathname()); $count++; }
        }
        $results[] = "✅ App cache cleared: {$count} files";
    } else {
        $results[] = "ℹ️ No app cache dir found";
    }

    // 5. Services & packages
    foreach (['services.php', 'packages.php'] as $f) {
        $p = $laravelRoot . 'bootstrap/cache/' . $f;
        if (file_exists($p)) { unlink($p); $results[] = "✅ {$f} cleared"; }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cache Cleaner v2</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; padding: 40px; max-width: 860px; }
        h2   { color: #f0a500; }
        li   { padding: 5px 0; font-size: 14px; }
        .warn { background: #7c2d2d; color: #fca5a5; padding: 12px 16px; border-radius: 6px; margin-top: 24px; }
        strong { color: #86efac; }
    </style>
</head>
<body>
    <h2>🧹 Cache Cleaner v2</h2>
    <ul>
        <?php foreach ($results as $r): ?>
            <li><?= $r ?></li>
        <?php endforeach; ?>
    </ul>
    <div class="warn">
        ⚠️ <strong>DELETE this file immediately after use!</strong><br>
        Remove <code>clear-cache.php</code> from your server now.
    </div>
</body>
</html>