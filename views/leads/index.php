<?php
$leads = $leads ?? [];
$canViewLeads = $canViewLeads ?? false;
$courseLabels = $courseLabels ?? [];
$scheduleLabels = $scheduleLabels ?? [];
?>
<section class="page-header">
    <h1><?= h($title ?? 'Danh sách đăng ký tư vấn') ?></h1>
    <p><?= h($canViewLeads ? 'Theo dõi các đăng ký mới nhất từ form tư vấn.' : 'Đăng ký của bạn đã được ghi nhận.') ?></p>
</section>

<?php if (!$canViewLeads): ?>
    <section class="card">
        <h2>Cảm ơn bạn đã gửi thông tin.</h2>
        <p>Đội tư vấn sẽ liên hệ theo khung giờ bạn chọn. Khu vực danh sách lead chỉ hiển thị sau khi đăng nhập.</p>
        <a class="btn btn-secondary" href="/login">Đăng nhập quản trị</a>
    </section>
<?php elseif ($leads === []): ?>
    <section class="card">
        <h2>Chưa có lead nào.</h2>
        <p>Khi người học gửi form tư vấn, thông tin sẽ xuất hiện tại đây.</p>
        <a class="btn btn-primary" href="/leads/create">Thêm đăng ký</a>
    </section>
<?php else: ?>
    <section class="grid-2">
        <div class="info-card">
            <h4>Tổng lead</h4>
            <p><strong><?= h((string)count($leads)) ?></strong> đăng ký tư vấn</p>
        </div>
        <div class="info-card">
            <h4>Thao tác</h4>
            <p><a class="btn btn-sm btn-primary" href="/leads/create">Thêm đăng ký</a></p>
        </div>
    </section>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Khóa học</th>
                    <th>Lịch tư vấn</th>
                    <th>Nội dung</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= h($lead['created_at'] ?? '') ?></td>
                        <td><strong><?= h($lead['full_name'] ?? '') ?></strong></td>
                        <td>
                            <?= h($lead['email'] ?? '') ?><br>
                            <span><?= h($lead['phone'] ?? '') ?></span>
                        </td>
                        <td>
                            <span class="badge badge-new"><?= h($courseLabels[$lead['course_interest'] ?? ''] ?? ($lead['course_interest'] ?? '')) ?></span>
                        </td>
                        <td><?= h($scheduleLabels[$lead['schedule'] ?? ''] ?? ($lead['schedule'] ?? '')) ?></td>
                        <td><?= h($lead['message'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
