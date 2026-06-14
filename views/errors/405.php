<?php $allowedMethods = $allowedMethods ?? []; ?>
<div class="error-page">
    <div class="error-code error-code-405">405</div>
    <h1 class="error-title">Phương thức không được hỗ trợ</h1>
    <p class="error-desc">
        Route này không hỗ trợ phương thức
        <span class="error-method"><?= h($_SERVER['REQUEST_METHOD'] ?? 'HTTP') ?></span>.
        <?php if ($allowedMethods !== []): ?>
            Phương thức hợp lệ: <strong><?= h(implode(', ', $allowedMethods)) ?></strong>.
        <?php endif; ?>
    </p>
    <a class="btn btn-primary" href="/">Về trang chủ</a>
</div>
