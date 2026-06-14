<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class LeadController
{
    private const COURSE_OPTIONS = ['web', 'mobile', 'data', 'ai', 'other'];
    private const SCHEDULE_OPTIONS = ['morning', 'afternoon', 'evening', 'weekend'];

    public function index(): void
    {
        Response::view('leads/index', [
            'title' => 'Danh sách đăng ký tư vấn',
            'leads' => is_logged_in() ? array_reverse($this->readLeads()) : [],
            'canViewLeads' => is_logged_in(),
            'courseLabels' => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
        ]);
    }

    public function create(): void
    {
        Response::view('leads/create', [
            'title' => 'Đăng ký tư vấn khóa học',
            'errors' => [],
            'old' => $this->emptyOldInput(),
            'courseLabels' => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
        ]);
    }

    public function store(): void
    {
        csrf_verify();

        $old = $this->oldInput();

        if (trim((string)($_POST['website'] ?? '')) !== '') {
            audit_log('HONEYPOT_TRIGGERED', [
                'email' => $old['email'],
                'phone' => $old['phone'],
            ]);

            $this->renderCreateWithErrors(['_form' => 'Yêu cầu không hợp lệ. Vui lòng thử lại sau.'], $old, 422);
        }

        $now = time();
        $lastSubmit = (int)($_SESSION['last_lead_submit_at'] ?? 0);

        if ($lastSubmit > 0 && ($now - $lastSubmit) < 5) {
            audit_log('RATE_LIMIT_BLOCKED', [
                'email' => $old['email'],
                'seconds_since_last_submit' => $now - $lastSubmit,
            ]);

            $this->renderCreateWithErrors(['_form' => 'Bạn gửi yêu cầu quá nhanh. Vui lòng chờ ít nhất 5 giây.'], $old, 429);
        }

        $errors = $this->validate($old);

        if ($errors !== []) {
            $this->renderCreateWithErrors($errors, $old, 422);
        }

        $lead = [
            'id' => bin2hex(random_bytes(8)),
            'full_name' => $old['full_name'],
            'email' => $old['email'],
            'phone' => $old['phone'],
            'course_interest' => $old['course_interest'],
            'schedule' => $old['schedule'],
            'message' => $old['message'],
            'created_at' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $leads = $this->readLeads();
        $leads[] = $lead;
        $this->writeLeads($leads);

        $_SESSION['last_lead_submit_at'] = $now;
        audit_log('LEAD_SUBMITTED', [
            'lead_id' => $lead['id'],
            'email' => $lead['email'],
            'course' => $lead['course_interest'],
        ]);

        flash_set('success', 'Cảm ơn bạn đã đăng ký. Trung tâm sẽ liên hệ tư vấn trong thời gian sớm nhất.');
        redirect('/leads');
    }

    private function renderCreateWithErrors(array $errors, array $old, int $status): void
    {
        Response::view('leads/create', [
            'title' => 'Đăng ký tư vấn khóa học',
            'errors' => $errors,
            'old' => $old,
            'courseLabels' => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
        ], $status);
    }

    private function validate(array $input): array
    {
        $errors = [];

        if ($input['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên.';
        } elseif ($this->length($input['full_name']) > 100) {
            $errors['full_name'] = 'Họ và tên tối đa 100 ký tự.';
        }

        if ($input['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($input['phone'] === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        } elseif (!preg_match('/^0[0-9]{9}$/', $input['phone'])) {
            $errors['phone'] = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.';
        }

        if (!in_array($input['course_interest'], self::COURSE_OPTIONS, true)) {
            $errors['course_interest'] = 'Vui lòng chọn khóa học quan tâm hợp lệ.';
        }

        if (!in_array($input['schedule'], self::SCHEDULE_OPTIONS, true)) {
            $errors['schedule'] = 'Vui lòng chọn khung giờ tư vấn hợp lệ.';
        }

        if ($this->length($input['message']) > 500) {
            $errors['message'] = 'Ghi chú tối đa 500 ký tự.';
        }

        return $errors;
    }

    private function oldInput(): array
    {
        return [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'course_interest' => trim((string)($_POST['course_interest'] ?? '')),
            'schedule' => trim((string)($_POST['schedule'] ?? '')),
            'message' => trim((string)($_POST['message'] ?? '')),
        ];
    }

    private function emptyOldInput(): array
    {
        return [
            'full_name' => '',
            'email' => '',
            'phone' => '',
            'course_interest' => '',
            'schedule' => '',
            'message' => '',
        ];
    }

    private function readLeads(): array
    {
        $file = storage_path('leads.json');

        if (!is_file($file)) {
            return [];
        }

        $contents = file_get_contents($file);
        $data = json_decode($contents === false ? '' : $contents, true);

        return is_array($data) ? $data : [];
    }

    private function writeLeads(array $leads): void
    {
        file_put_contents(
            storage_path('leads.json'),
            json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function courseLabels(): array
    {
        return [
            'web' => 'Lập trình Web',
            'mobile' => 'Lập trình Mobile',
            'data' => 'Phân tích dữ liệu',
            'ai' => 'AI ứng dụng',
            'other' => 'Khác',
        ];
    }

    private function scheduleLabels(): array
    {
        return [
            'morning' => 'Buổi sáng',
            'afternoon' => 'Buổi chiều',
            'evening' => 'Buổi tối',
            'weekend' => 'Cuối tuần',
        ];
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
