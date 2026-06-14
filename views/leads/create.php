<?php
$errors = $errors ?? [];
$old = $old ?? [];
$courseLabels = $courseLabels ?? [];
$scheduleLabels = $scheduleLabels ?? [];
?>
<section class="page-header">
    <h1><?= h($title ?? 'Đăng ký tư vấn khóa học') ?></h1>
    <p>Điền thông tin để đội tư vấn liên hệ đúng khóa học và khung giờ bạn mong muốn.</p>
</section>

<form class="card lead-form-card" method="POST" action="/leads" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

    <div class="honeypot" aria-hidden="true">
        <label for="website">Website</label>
        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    <?php if (!empty($errors['_form'])): ?>
        <div class="alert alert-error"><?= h($errors['_form']) ?></div>
    <?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label for="full_name">Họ và tên</label>
            <input id="full_name" class="<?= !empty($errors['full_name']) ? 'input-error' : '' ?>" type="text" name="full_name" value="<?= h($old['full_name'] ?? '') ?>" maxlength="100" autocomplete="name">
            <?php if (!empty($errors['full_name'])): ?>
                <p class="error-text"><?= h($errors['full_name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" class="<?= !empty($errors['email']) ? 'input-error' : '' ?>" type="email" name="email" value="<?= h($old['email'] ?? '') ?>" autocomplete="email">
            <?php if (!empty($errors['email'])): ?>
                <p class="error-text"><?= h($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input id="phone" class="<?= !empty($errors['phone']) ? 'input-error' : '' ?>" type="tel" name="phone" value="<?= h($old['phone'] ?? '') ?>" inputmode="numeric" autocomplete="tel">
            <?php if (!empty($errors['phone'])): ?>
                <p class="error-text"><?= h($errors['phone']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="course_interest">Khóa học quan tâm</label>
            <select id="course_interest" class="<?= !empty($errors['course_interest']) ? 'input-error' : '' ?>" name="course_interest">
                <option value="">Chọn khóa học</option>
                <?php foreach ($courseLabels as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= ($old['course_interest'] ?? '') === $value ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['course_interest'])): ?>
                <p class="error-text"><?= h($errors['course_interest']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="schedule">Khung giờ tư vấn</label>
            <select id="schedule" class="<?= !empty($errors['schedule']) ? 'input-error' : '' ?>" name="schedule">
                <option value="">Chọn khung giờ</option>
                <?php foreach ($scheduleLabels as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= ($old['schedule'] ?? '') === $value ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['schedule'])): ?>
                <p class="error-text"><?= h($errors['schedule']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="message">Nhu cầu tư vấn</label>
        <textarea id="message" class="<?= !empty($errors['message']) ? 'input-error' : '' ?>" name="message" rows="6" maxlength="2000"><?= h($old['message'] ?? '') ?></textarea>
        <?php if (!empty($errors['message'])): ?>
            <p class="error-text"><?= h($errors['message']) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Gửi đăng ký</button>
    <a class="btn btn-secondary" href="/">Hủy</a>
</form>
