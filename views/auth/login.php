<?php
$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
?>
<section class="page-header">
    <h1><?= h($title ?? 'Đăng nhập quản trị') ?></h1>
    <p>Đăng nhập bằng tài khoản admin hoặc staff để xem lead và dashboard.</p>
</section>

<form class="card login-card" method="POST" action="/login" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

    <?php if (!empty($errors['login'])): ?>
        <div class="alert alert-error"><?= h($errors['login']) ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= h($old['email'] ?? '') ?>" autocomplete="username">
    </div>

    <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input id="password" type="password" name="password" autocomplete="current-password">
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="remember_me" value="1">
            Nhớ tôi trong 30 ngày
        </label>
    </div>

    <button type="submit" class="btn btn-primary">Đăng nhập</button>
</form>
