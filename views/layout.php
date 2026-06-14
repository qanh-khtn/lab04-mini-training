<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$flashTypeMap = [
    'success' => 'success',
    'danger'  => 'danger',
    'error'   => 'danger',
    'warning' => 'warning',
    'info'    => 'info',
];
?>
<!doctype html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title ?? 'Mini Training Center Lead Portal') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/app.js"></script>
</head>
<body>
    <header class="navbar">
        <a class="brand" href="/">Mini Training Center</a>
        <a class="<?= $currentPath === '/' ? 'active' : '' ?>" href="/">Trang chủ</a>
        <a class="<?= $currentPath === '/leads/create' ? 'active' : '' ?>" href="/leads/create">Đăng ký tư vấn</a>
        <?php if (is_logged_in()): ?>
            <a class="<?= $currentPath === '/leads' ? 'active' : '' ?>" href="/leads">Danh sách đăng ký</a>
            <a class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>" href="/dashboard">Bảng điều khiển</a>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a class="<?= $currentPath === '/audit-log' ? 'active' : '' ?>" href="/audit-log">Nhật ký bảo mật</a>
            <?php endif; ?>
            <form class="inline-form" method="POST" action="/logout">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button class="link-btn" type="submit">Đăng xuất</button>
            </form>
        <?php else: ?>
            <a class="<?= $currentPath === '/login' ? 'active' : '' ?>" href="/login">Đăng nhập</a>
        <?php endif; ?>
    </header>

    <?php $flashes = flash_get(); ?>
    <?php if ($flashes): ?>
    <div class="toast-container" id="toast-container">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php $tone = $flashTypeMap[$type] ?? 'info'; ?>
            <?php foreach ($messages as $message): ?>
                <div class="toast toast-<?= h($tone) ?>" role="alert">
                    <span class="toast-icon">
                        <?php if ($tone === 'success'): ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php elseif ($tone === 'danger'): ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <?php elseif ($tone === 'warning'): ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <?php else: ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php endif; ?>
                    </span>
                    <span class="toast-msg"><?= h($message) ?></span>
                    <button class="toast-close" aria-label="Đóng">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <div class="toast-progress"></div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <main class="container">
        <?php require view_path($view); ?>
    </main>

    <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Chuyển giao diện sáng/tối">
        <span class="theme-toggle-inner">
            <svg class="toggle-sun" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg class="toggle-moon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </span>
    </button>
</body>
</html>
