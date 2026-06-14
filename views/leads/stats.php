<?php
$total          = $total          ?? 0;
$byCourse       = $byCourse       ?? [];
$bySchedule     = $bySchedule     ?? [];
$byStatus       = $byStatus       ?? [];
$courseLabels   = $courseLabels   ?? [];
$scheduleLabels = $scheduleLabels ?? [];
$statusLabels   = $statusLabels   ?? [];
$maxCourse      = max(array_values($byCourse)   ?: [1]);
$maxSchedule    = max(array_values($bySchedule) ?: [1]);

$courseColors   = ['web' => '#2563eb', 'mobile' => '#16a34a', 'data' => '#ca8a04', 'ai' => '#7c3aed', 'other' => '#64748b'];
$scheduleColors = ['morning' => '#0ea5e9', 'afternoon' => '#f97316', 'evening' => '#8b5cf6', 'weekend' => '#10b981'];
$statusColors   = ['new' => '#2563eb', 'consulting' => '#ca8a04', 'done' => '#16a34a'];
?>
<div class="page-header">
    <h1><?= h($title ?? 'Thống kê đăng ký') ?></h1>
    <p>Tổng quan số liệu từ <strong><?= $total ?></strong> đăng ký tư vấn trong hệ thống.</p>
</div>

<!-- Status summary -->
<div class="grid-4" style="margin-bottom:20px">
    <div class="card" style="margin-bottom:0;text-align:center;border-top:3px solid #2563eb">
        <div style="font-size:36px;font-weight:900;color:#2563eb"><?= $total ?></div>
        <div style="color:var(--muted);font-size:13px;margin-top:4px">Tổng đăng ký</div>
    </div>
    <div class="card" style="margin-bottom:0;text-align:center;border-top:3px solid #2563eb">
        <div style="font-size:36px;font-weight:900;color:#2563eb"><?= $byStatus['new'] ?? 0 ?></div>
        <div style="color:var(--muted);font-size:13px;margin-top:4px">Mới</div>
    </div>
    <div class="card" style="margin-bottom:0;text-align:center;border-top:3px solid #ca8a04">
        <div style="font-size:36px;font-weight:900;color:#ca8a04"><?= $byStatus['consulting'] ?? 0 ?></div>
        <div style="color:var(--muted);font-size:13px;margin-top:4px">Đang tư vấn</div>
    </div>
    <div class="card" style="margin-bottom:0;text-align:center;border-top:3px solid #16a34a">
        <div style="font-size:36px;font-weight:900;color:#16a34a"><?= $byStatus['done'] ?? 0 ?></div>
        <div style="color:var(--muted);font-size:13px;margin-top:4px">Hoàn thành</div>
    </div>
</div>

<div class="grid-2">
    <!-- By course -->
    <div class="card" style="margin-bottom:0">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700">Theo khóa học</h3>
        <?php foreach ($byCourse as $key => $count): ?>
        <?php $pct = $total > 0 ? round($count / $maxCourse * 100) : 0; ?>
        <?php $color = $courseColors[$key] ?? '#64748b'; ?>
        <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:13px;font-weight:600"><?= h($courseLabels[$key] ?? $key) ?></span>
                <span style="font-size:13px;color:var(--muted)"><strong style="color:var(--text)"><?= $count ?></strong> đăng ký</span>
            </div>
            <div style="height:8px;background:var(--line);border-radius:99px;overflow:hidden">
                <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:99px;transition:width .4s ease"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- By schedule -->
    <div class="card" style="margin-bottom:0">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700">Theo khung giờ</h3>
        <?php foreach ($bySchedule as $key => $count): ?>
        <?php $pct = $total > 0 ? round($count / $maxSchedule * 100) : 0; ?>
        <?php $color = $scheduleColors[$key] ?? '#64748b'; ?>
        <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:13px;font-weight:600"><?= h($scheduleLabels[$key] ?? $key) ?></span>
                <span style="font-size:13px;color:var(--muted)"><strong style="color:var(--text)"><?= $count ?></strong> đăng ký</span>
            </div>
            <div style="height:8px;background:var(--line);border-radius:99px;overflow:hidden">
                <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:99px;transition:width .4s ease"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn btn-primary" href="/leads">← Danh sách đăng ký</a>
    <a class="btn btn-success" href="/leads/export">↓ Xuất CSV</a>
</div>
