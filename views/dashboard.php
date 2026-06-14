<?php $sessionDemo = $sessionDemo ?? false; ?>

<div class="page-header">
    <h1>Bảng điều khiển</h1>
    <p>Chào mừng trở lại, <strong><?= h($_SESSION['user_name'] ?? 'Admin') ?></strong>.</p>
</div>

<div class="grid-4" style="margin-bottom:20px">
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#1769e0,#0f4da8)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <h3><?= h($_SESSION['user_name'] ?? '') ?></h3>
        <p style="margin-bottom:8px;color:var(--muted);font-size:13px"><?= h($_SESSION['user_email'] ?? '') ?></p>
        <span class="badge badge-open"><?= h($_SESSION['user_role'] ?? 'staff') ?></span>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#168251,#0d5c38)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        </div>
        <h3>Danh sách đăng ký</h3>
        <p style="color:var(--muted);font-size:13px">Xem các lead tư vấn được lưu trong storage/leads.json</p>
        <a class="btn btn-sm btn-success" href="/leads" style="margin-top:8px">Mở danh sách</a>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#b7790d,#8b5e0a)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path><path d="M4.93 4.93a10 10 0 0 0 0 14.14"></path></svg>
        </div>
        <h3>Phiên làm việc</h3>
        <p style="color:var(--muted);font-size:13px">Session: <code><?= h(session_name()) ?></code><br>Timeout: <?= (int)($_ENV['SESSION_IDLE_LIMIT'] ?? 900) ?>s</p>
        <a class="btn btn-sm btn-secondary" href="/session-demo" style="margin-top:8px">Chi tiết session</a>
    </div>
    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#c92a2a,#9f1d1d)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        </div>
        <h3>Nhật ký bảo mật</h3>
        <p style="color:var(--muted);font-size:13px">Ghi nhận toàn bộ sự kiện login, logout, honeypot, rate limit</p>
        <a class="btn btn-sm btn-danger" href="/audit-log" style="margin-top:8px">Xem audit log</a>
    </div>
    <?php else: ?>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#5d6f86,#3a4a5e)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </div>
        <h3>Đăng xuất</h3>
        <p style="color:var(--muted);font-size:13px">Kết thúc phiên làm việc và xóa remember token</p>
        <form class="inline-form" method="POST" action="/logout" style="margin-top:8px">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <button type="submit" class="btn btn-sm btn-danger">Đăng xuất</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php if ($sessionDemo): ?>
<div class="card">
    <h3 style="margin:0 0 16px;font-size:16px">Chi tiết Session</h3>
    <div class="grid-2">
        <div class="info-card"><h4>User ID</h4><p><?= h($_SESSION['user_id'] ?? '') ?></p></div>
        <div class="info-card"><h4>Vai trò</h4><p><?= h($_SESSION['user_role'] ?? '') ?></p></div>
        <div class="info-card"><h4>Hoạt động cuối</h4><p><?= h(date('d/m/Y H:i:s', (int)($_SESSION['last_activity_at'] ?? time()))) ?></p></div>
        <div class="info-card"><h4>Session Context (16 ký tự đầu)</h4><p><code><?= h(substr((string)($_SESSION['session_context'] ?? ''), 0, 16)) ?>...</code></p></div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="margin:0 0 4px;font-size:15px">Đăng xuất khỏi hệ thống</h3>
            <p style="margin:0;color:var(--muted);font-size:13px">Kết thúc phiên làm việc, xóa remember token và cookie.</p>
        </div>
        <form class="inline-form" method="POST" action="/logout">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <button type="submit" class="btn btn-danger">Đăng xuất</button>
        </form>
    </div>
</div>
<?php endif; ?>
