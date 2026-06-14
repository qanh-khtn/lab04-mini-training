<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class LeadController
{
    private const COURSE_OPTIONS   = ['web', 'mobile', 'data', 'ai', 'other'];
    private const SCHEDULE_OPTIONS = ['morning', 'afternoon', 'evening', 'weekend'];
    private const STATUS_OPTIONS   = ['new', 'consulting', 'done'];
    private const SORT_WHITELIST   = ['created_at', 'full_name', 'course_interest', 'schedule', 'status'];
    private const DIR_WHITELIST    = ['asc', 'desc'];
    private const PER_PAGE         = 10;

    public function index(): void
    {
        $q    = trim((string)($_GET['q']         ?? ''));
        $sort = $_GET['sort'] ?? 'created_at';
        $dir  = $_GET['dir']  ?? 'desc';
        $page = max(1, (int)($_GET['page']       ?? 1));

        if (!in_array($sort, self::SORT_WHITELIST, true)) { $sort = 'created_at'; }
        if (!in_array($dir,  self::DIR_WHITELIST,  true)) { $dir  = 'desc'; }

        $all = $this->readLeads();

        if ($q !== '') {
            $ql  = mb_strtolower($q, 'UTF-8');
            $all = array_values(array_filter($all, function (array $lead) use ($ql): bool {
                return str_contains(mb_strtolower((string)($lead['full_name'] ?? ''), 'UTF-8'), $ql)
                    || str_contains(mb_strtolower((string)($lead['email']     ?? ''), 'UTF-8'), $ql)
                    || str_contains(mb_strtolower((string)($lead['phone']     ?? ''), 'UTF-8'), $ql);
            }));
        }

        usort($all, function (array $a, array $b) use ($sort, $dir): int {
            $va = mb_strtolower((string)($a[$sort] ?? ''), 'UTF-8');
            $vb = mb_strtolower((string)($b[$sort] ?? ''), 'UTF-8');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total    = count($all);
        $lastPage = max(1, (int)ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);
        $leads    = array_slice($all, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        Response::view('leads/index', [
            'title'          => 'Danh sách đăng ký tư vấn',
            'leads'          => is_logged_in() ? $leads : [],
            'canViewLeads'   => is_logged_in(),
            'courseLabels'   => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
            'statusLabels'   => $this->statusLabels(),
            'q'              => $q,
            'sort'           => $sort,
            'dir'            => $dir,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'total'          => $total,
        ]);
    }

    public function create(): void
    {
        Response::view('leads/create', [
            'title'          => 'Đăng ký tư vấn khóa học',
            'errors'         => [],
            'old'            => $this->emptyOldInput(),
            'courseLabels'   => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
        ]);
    }

    public function store(): void
    {
        csrf_verify();
        $old = $this->oldInput();

        if (trim((string)($_POST['website'] ?? '')) !== '') {
            audit_log('HONEYPOT_TRIGGERED', ['email' => $old['email'], 'phone' => $old['phone']]);
            $this->renderCreateWithErrors(['_form' => 'Yêu cầu không hợp lệ. Vui lòng thử lại sau.'], $old, 422);
        }

        $now        = time();
        $lastSubmit = (int)($_SESSION['last_lead_submit_at'] ?? 0);
        if ($lastSubmit > 0 && ($now - $lastSubmit) < 5) {
            audit_log('RATE_LIMIT_BLOCKED', ['email' => $old['email']]);
            $this->renderCreateWithErrors(['_form' => 'Bạn gửi yêu cầu quá nhanh. Vui lòng chờ ít nhất 5 giây.'], $old, 429);
        }

        $errors = $this->validate($old);
        if ($errors !== []) { $this->renderCreateWithErrors($errors, $old, 422); }

        $lead = [
            'id'              => bin2hex(random_bytes(8)),
            'full_name'       => $old['full_name'],
            'email'           => $old['email'],
            'phone'           => $old['phone'],
            'course_interest' => $old['course_interest'],
            'schedule'        => $old['schedule'],
            'message'         => $old['message'],
            'status'          => 'new',
            'created_at'      => date('Y-m-d H:i:s'),
            'ip'              => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $leads   = $this->readLeads();
        $leads[] = $lead;
        $this->writeLeads($leads);

        $_SESSION['last_lead_submit_at'] = $now;
        audit_log('LEAD_SUBMITTED', ['lead_id' => $lead['id'], 'email' => $lead['email'], 'course' => $lead['course_interest']]);

        flash_set('success', 'Cảm ơn bạn đã đăng ký. Trung tâm sẽ liên hệ tư vấn trong thời gian sớm nhất.');
        redirect('/leads');
    }

    public function updateStatus(): void
    {
        require_login();
        csrf_verify();

        $id     = trim((string)($_POST['id']     ?? ''));
        $status = trim((string)($_POST['status'] ?? ''));

        if (!in_array($status, self::STATUS_OPTIONS, true)) {
            flash_set('danger', 'Trạng thái không hợp lệ.');
            redirect('/leads');
        }

        $leads = $this->readLeads();
        $found = false;
        foreach ($leads as &$lead) {
            if ((string)($lead['id'] ?? '') === $id) {
                $lead['status'] = $status;
                $found = true;
                break;
            }
        }
        unset($lead);

        if (!$found) {
            flash_set('danger', 'Không tìm thấy đăng ký.');
            redirect('/leads');
        }

        $this->writeLeads($leads);
        audit_log('LEAD_STATUS_UPDATED', ['lead_id' => $id, 'status' => $status, 'by' => $_SESSION['user_email'] ?? '']);
        flash_set('success', 'Đã cập nhật trạng thái.');
        redirect('/leads');
    }

    public function destroy(): void
    {
        require_login();
        csrf_verify();

        $id    = trim((string)($_POST['id'] ?? ''));
        $leads = $this->readLeads();
        $found = false;

        $leads = array_values(array_filter($leads, function (array $lead) use ($id, &$found): bool {
            if ((string)($lead['id'] ?? '') === $id) { $found = true; return false; }
            return true;
        }));

        if (!$found) { flash_set('danger', 'Không tìm thấy đăng ký cần xóa.'); redirect('/leads'); }

        $this->writeLeads($leads);
        audit_log('LEAD_DELETED', ['lead_id' => $id, 'by' => $_SESSION['user_email'] ?? '']);
        flash_set('success', 'Đã xóa đăng ký thành công.');
        redirect('/leads');
    }

    public function export(): void
    {
        require_login();

        $leads  = $this->readLeads();
        $labels = $this->courseLabels();
        $sched  = $this->scheduleLabels();
        $statLb = $this->statusLabels();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="leads_' . date('Ymd_His') . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        if ($out === false) { exit; }
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['ID', 'Họ và tên', 'Email', 'Số ĐT', 'Khóa học', 'Khung giờ', 'Trạng thái', 'Nhu cầu', 'Thời gian', 'IP']);

        foreach (array_reverse($leads) as $lead) {
            fputcsv($out, [
                $lead['id']              ?? '',
                $lead['full_name']       ?? '',
                $lead['email']           ?? '',
                $lead['phone']           ?? '',
                $labels[$lead['course_interest'] ?? ''] ?? '',
                $sched[$lead['schedule']         ?? ''] ?? '',
                $statLb[$lead['status']          ?? ''] ?? 'Mới',
                $lead['message']                        ?? '',
                $lead['created_at']                     ?? '',
                $lead['ip']                             ?? '',
            ]);
        }

        fclose($out);
        audit_log('LEADS_EXPORTED', ['by' => $_SESSION['user_email'] ?? '', 'count' => count($leads)]);
        exit;
    }

    public function stats(): void
    {
        require_login();

        $leads         = $this->readLeads();
        $courseLabels  = $this->courseLabels();
        $scheduleLabels= $this->scheduleLabels();
        $statusLabels  = $this->statusLabels();

        $byCourse   = array_fill_keys(array_keys($courseLabels),   0);
        $bySchedule = array_fill_keys(array_keys($scheduleLabels), 0);
        $byStatus   = array_fill_keys(array_keys($statusLabels),   0);

        foreach ($leads as $lead) {
            $c = $lead['course_interest'] ?? '';
            $s = $lead['schedule']        ?? '';
            $t = $lead['status']          ?? 'new';
            if (isset($byCourse[$c]))   { $byCourse[$c]++; }
            if (isset($bySchedule[$s])) { $bySchedule[$s]++; }
            if (isset($byStatus[$t]))   { $byStatus[$t]++; } else { $byStatus['new']++; }
        }

        Response::view('leads/stats', [
            'title'          => 'Thống kê đăng ký',
            'total'          => count($leads),
            'byCourse'       => $byCourse,
            'bySchedule'     => $bySchedule,
            'byStatus'       => $byStatus,
            'courseLabels'   => $courseLabels,
            'scheduleLabels' => $scheduleLabels,
            'statusLabels'   => $statusLabels,
        ]);
    }

    private function renderCreateWithErrors(array $errors, array $old, int $status): void
    {
        Response::view('leads/create', [
            'title'          => 'Đăng ký tư vấn khóa học',
            'errors'         => $errors,
            'old'            => $old,
            'courseLabels'   => $this->courseLabels(),
            'scheduleLabels' => $this->scheduleLabels(),
        ], $status);
    }

    private function validate(array $input): array
    {
        $errors = [];
        if ($input['full_name'] === '') { $errors['full_name'] = 'Vui lòng nhập họ và tên.'; }
        elseif ($this->length($input['full_name']) > 100) { $errors['full_name'] = 'Họ và tên tối đa 100 ký tự.'; }
        if ($input['email'] === '') { $errors['email'] = 'Vui lòng nhập email.'; }
        elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Email không đúng định dạng.'; }
        if ($input['phone'] === '') { $errors['phone'] = 'Vui lòng nhập số điện thoại.'; }
        elseif (!preg_match('/^0[0-9]{9}$/', $input['phone'])) { $errors['phone'] = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.'; }
        if (!in_array($input['course_interest'], self::COURSE_OPTIONS, true)) { $errors['course_interest'] = 'Vui lòng chọn khóa học hợp lệ.'; }
        if (!in_array($input['schedule'], self::SCHEDULE_OPTIONS, true)) { $errors['schedule'] = 'Vui lòng chọn khung giờ hợp lệ.'; }
        if ($this->length($input['message']) > 500) { $errors['message'] = 'Ghi chú tối đa 500 ký tự.'; }
        return $errors;
    }

    private function oldInput(): array
    {
        return [
            'full_name'       => trim((string)($_POST['full_name']       ?? '')),
            'email'           => trim((string)($_POST['email']           ?? '')),
            'phone'           => trim((string)($_POST['phone']           ?? '')),
            'course_interest' => trim((string)($_POST['course_interest'] ?? '')),
            'schedule'        => trim((string)($_POST['schedule']        ?? '')),
            'message'         => trim((string)($_POST['message']         ?? '')),
        ];
    }

    private function emptyOldInput(): array
    {
        return ['full_name' => '', 'email' => '', 'phone' => '', 'course_interest' => '', 'schedule' => '', 'message' => ''];
    }

    private function readLeads(): array
    {
        $file = storage_path('leads.json');
        if (!is_file($file)) { return []; }
        $data = json_decode((string)file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    private function writeLeads(array $leads): void
    {
        file_put_contents(storage_path('leads.json'), json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function courseLabels(): array
    {
        return ['web' => 'Lập trình Web', 'mobile' => 'Lập trình Mobile', 'data' => 'Phân tích dữ liệu', 'ai' => 'AI ứng dụng', 'other' => 'Khác'];
    }

    private function scheduleLabels(): array
    {
        return ['morning' => 'Buổi sáng', 'afternoon' => 'Buổi chiều', 'evening' => 'Buổi tối', 'weekend' => 'Cuối tuần'];
    }

    private function statusLabels(): array
    {
        return ['new' => 'Mới', 'consulting' => 'Đang tư vấn', 'done' => 'Hoàn thành'];
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
