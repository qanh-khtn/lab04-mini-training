<?php
$leads          = $leads          ?? [];
$canViewLeads   = $canViewLeads   ?? false;
$courseLabels   = $courseLabels   ?? [];
$scheduleLabels = $scheduleLabels ?? [];
$statusLabels   = $statusLabels   ?? [];
$q              = $q              ?? '';
$sort           = $sort           ?? 'created_at';
$dir            = $dir            ?? 'desc';
$page           = $page           ?? 1;
$lastPage       = $lastPage       ?? 1;
$total          = $total          ?? 0;

function sortLink(string $col, string $label, string $currentSort, string $currentDir, string $q, int $page): string {
    $nextDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    $arrow   = '';
    if ($currentSort === $col) {
        $arrow = $currentDir === 'asc' ? ' ↑' : ' ↓';
    }
    $qs = http_build_query(array_filter(['q' => $q, 'sort' => $col, 'dir' => $nextDir, 'page' => $page > 1 ? $page : null]));
    return '<a href="/leads?' . htmlspecialchars($qs, ENT_QUOTES) . '" style="color:inherit;text-decoration:none;white-space:nowrap">'
         . htmlspecialchars($label, ENT_QUOTES) . $arrow . '</a>';
}
?>
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:6px">
    <div class="page-header" style="margin-bottom:0">
        <h1><?= h($title ?? 'Danh sách đăng ký tư vấn') ?></h1>
        <p><?= h($canViewLeads ? 'Theo dõi và quản lý các đăng ký từ form tư vấn.' : 'Đăng ký của bạn đã được ghi nhận.') ?></p>
    </div>
    <?php if ($canViewLeads): ?>
    <a class="btn btn-secondary btn-sm" href="/leads/stats" style="margin-top:6px">Xem thống kê</a>
    <?php endif; ?>
</div>

<?php if (!$canViewLeads): ?>
    <section class="card">
        <h2 style="margin:0 0 8px;font-size:18px">Cảm ơn bạn đã gửi thông tin.</h2>
        <p style="color:var(--muted)">Đội tư vấn sẽ liên hệ theo khung giờ bạn chọn. Khu vực danh sách lead chỉ hiển thị sau khi đăng nhập.</p>
        <a class="btn btn-secondary" href="/login" style="margin-top:12px">Đăng nhập quản trị</a>
    </section>
<?php else: ?>

    <!-- Search + Actions -->
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        <form method="GET" action="/leads" style="display:flex;gap:8px;flex:1;min-width:200px">
            <input type="hidden" name="sort" value="<?= h($sort) ?>">
            <input type="hidden" name="dir"  value="<?= h($dir) ?>">
            <input type="text" name="q" value="<?= h($q) ?>"
                   placeholder="Tìm theo tên, email, SĐT..."
                   style="flex:1;min-width:0">
            <button type="submit" class="btn btn-primary" style="width:80px">Tìm</button>
            <?php if ($q !== ''): ?>
                <a class="btn btn-secondary" style="width:80px;text-align:center" href="/leads?sort=<?= h($sort) ?>&dir=<?= h($dir) ?>">Xóa</a>
            <?php endif; ?>
        </form>
        <a class="btn btn-success" style="width:80px;text-align:center" href="/leads/export">↓ CSV</a>
        <a class="btn btn-primary" style="width:80px;text-align:center" href="/leads/create">+ Thêm</a>
    </div>

    <!-- Count info -->
    <p style="margin-bottom:10px;color:var(--muted);font-size:13px">
        <?php if ($q !== ''): ?>
            Kết quả "<strong><?= h($q) ?></strong>": <strong><?= $total ?></strong> đăng ký
        <?php elseif ($total === 0): ?>
            Chưa có đăng ký nào.
        <?php else: ?>
            Tổng <strong><?= $total ?></strong> đăng ký — trang <strong><?= $page ?></strong>/<strong><?= $lastPage ?></strong>
        <?php endif; ?>
    </p>

    <?php if ($total === 0 && $q === ''): ?>
        <section class="card">
            <h2 style="margin:0 0 8px;font-size:18px">Chưa có lead nào.</h2>
            <p style="color:var(--muted)">Khi người học gửi form tư vấn, thông tin sẽ xuất hiện tại đây.</p>
            <a class="btn btn-primary" href="/leads/create" style="margin-top:12px">Thêm đăng ký</a>
        </section>
    <?php elseif ($leads !== []): ?>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= sortLink('created_at', 'Thời gian', $sort, $dir, $q, $page) ?></th>
                    <th><?= sortLink('full_name',  'Họ tên',    $sort, $dir, $q, $page) ?></th>
                    <th>Liên hệ</th>
                    <th><?= sortLink('course_interest', 'Khóa học', $sort, $dir, $q, $page) ?></th>
                    <th><?= sortLink('schedule', 'Lịch tư vấn', $sort, $dir, $q, $page) ?></th>
                    <th><?= sortLink('status', 'Trạng thái', $sort, $dir, $q, $page) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                <?php
                $statusBadge = match($lead['status'] ?? 'new') {
                    'consulting' => 'badge-pending',
                    'done'       => 'badge-open',
                    default      => 'badge-new',
                };
                ?>
                <tr>
                    <td style="white-space:nowrap;font-size:12px;color:var(--muted)"><?= h($lead['created_at'] ?? '') ?></td>
                    <td><strong><?= h($lead['full_name'] ?? '') ?></strong>
                        <?php if (!empty($lead['message'])): ?>
                        <br><span style="font-size:11px;color:var(--muted)" title="<?= h($lead['message']) ?>">
                            <?= h(mb_substr($lead['message'], 0, 30)) ?><?= mb_strlen($lead['message']) > 30 ? '…' : '' ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px">
                        <?= h($lead['email'] ?? '') ?><br>
                        <span style="color:var(--muted)"><?= h($lead['phone'] ?? '') ?></span>
                    </td>
                    <td><span class="badge badge-new"><?= h($courseLabels[$lead['course_interest'] ?? ''] ?? '') ?></span></td>
                    <td style="font-size:13px"><?= h($scheduleLabels[$lead['schedule'] ?? ''] ?? '') ?></td>
                    <td>
                        <form method="POST" action="/leads/status" style="display:inline-flex;gap:4px;align-items:center">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= h($lead['id'] ?? '') ?>">
                            <select name="status" onchange="this.form.submit()" style="min-height:28px;padding:3px 6px;font-size:12px">
                                <?php foreach ($statusLabels as $val => $lbl): ?>
                                    <option value="<?= h($val) ?>" <?= ($lead['status'] ?? 'new') === $val ? 'selected' : '' ?>><?= h($lbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="/leads/delete"
                              onsubmit="return confirm('Xóa đăng ký của <?= h(addslashes($lead['full_name'] ?? '')) ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= h($lead['id'] ?? '') ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($lastPage > 1): ?>
    <?php $baseQs = http_build_query(array_filter(['q' => $q, 'sort' => $sort, 'dir' => $dir])); ?>
    <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap">
        <?php if ($page > 1): ?>
            <a class="btn btn-secondary btn-sm" href="/leads?<?= $baseQs ?>&page=<?= $page - 1 ?>">← Trước</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($lastPage, $page + 2); $p++): ?>
            <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"
               href="/leads?<?= $baseQs ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $lastPage): ?>
            <a class="btn btn-secondary btn-sm" href="/leads?<?= $baseQs ?>&page=<?= $page + 1 ?>">Sau →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
<?php endif; ?>
