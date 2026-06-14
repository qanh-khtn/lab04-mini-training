<?php
$logs = $logs ?? [];
?>
<section class="page-header">
    <h1><?= h($title ?? 'Nhật ký bảo mật') ?></h1>
    <p>Các sự kiện bảo mật mới nhất được đọc đảo chiều từ <code>storage/audit.log</code>.</p>
</section>

<?php if ($logs === []): ?>
    <section class="card">
        <h2>Chưa có sự kiện audit.</h2>
        <p>Login, logout, honeypot, rate limit và lead submit sẽ được ghi lại tại đây.</p>
    </section>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Dòng log</th>
                    <th>Sự kiện</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $line): ?>
                    <?php
                    $event = 'UNKNOWN';
                    if (preg_match('/^\[[^\]]+\]\s+([A-Z_]+)/', $line, $matches)) {
                        $event = $matches[1];
                    }
                    $eventTone = match ($event) {
                        'LOGIN_SUCCESS', 'LOGOUT', 'LEAD_SUBMITTED' => 'log-success',
                        'LOGIN_FAILED', 'HONEYPOT_TRIGGERED', 'RATE_LIMIT_BLOCKED', 'SESSION_TIMEOUT' => 'log-danger',
                        default => 'log-warning',
                    };
                    ?>
                    <tr>
                        <td><code><?= h($line) ?></code></td>
                        <td><strong class="<?= h($eventTone) ?>"><?= h($event) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
