<?php
$logs        = $logs        ?? [];
$filterEvent = $filterEvent ?? '';
$validEvents = $validEvents ?? [];
?>
<section class="page-header">
    <h1><?= h($title ?? 'Nhật ký bảo mật') ?></h1>
    <p>Các sự kiện bảo mật mới nhất được ghi từ <code>storage/audit.log</code>.</p>
</section>

<!-- Filter bar -->
<form method="GET" action="/audit-log" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px">
    <select name="event" style="min-width:220px">
        <option value="">Tất cả sự kiện</option>
        <?php foreach ($validEvents as $ev): ?>
            <option value="<?= h($ev) ?>" <?= $filterEvent === $ev ? 'selected' : '' ?>><?= h($ev) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Lọc</button>
    <?php if ($filterEvent !== ''): ?>
        <a class="btn btn-secondary" href="/audit-log">Xóa lọc</a>
        <span style="font-size:13px;color:var(--muted)">Đang lọc: <strong><?= h($filterEvent) ?></strong> — <?= count($logs) ?> dòng</span>
    <?php endif; ?>
</form>

<?php if ($logs === []): ?>
    <section class="card">
        <h2 style="margin:0 0 8px;font-size:18px">Chưa có sự kiện nào<?= $filterEvent !== '' ? ' khớp với bộ lọc' : '' ?>.</h2>
        <p style="color:var(--muted)">Login, logout, honeypot, rate limit và lead submit sẽ được ghi lại tại đây.</p>
    </section>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Sự kiện</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $line): ?>
                    <?php
                    $event    = 'UNKNOWN';
                    $time     = '';
                    $details  = $line;

                    if (preg_match('/^\[([^\]]+)\]\s+([A-Z_]+)\s*(.*)$/', $line, $m)) {
                        $time    = $m[1];
                        $event   = $m[2];
                        $details = trim($m[3]);
                    }

                    $tone = match ($event) {
                        'LOGIN_SUCCESS', 'LOGOUT', 'LEAD_SUBMITTED', 'LEADS_EXPORTED' => 'log-success',
                        'LOGIN_FAILED', 'HONEYPOT_TRIGGERED', 'RATE_LIMIT_BLOCKED',
                        'SESSION_TIMEOUT', 'CSRF_FAIL'                                => 'log-danger',
                        'LEAD_DELETED'                                                => 'log-warning',
                        default                                                       => 'log-warning',
                    };
                    ?>
                    <tr>
                        <td style="white-space:nowrap;font-size:12px;color:var(--muted)"><?= h($time) ?></td>
                        <td><strong class="<?= h($tone) ?>"><?= h($event) ?></strong></td>
                        <td><code style="font-size:12px"><?= h($details) ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
