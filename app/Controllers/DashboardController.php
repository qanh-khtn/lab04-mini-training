<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class DashboardController
{
    public function index(): void
    {
        require_login();

        Response::view('dashboard', [
            'title'       => 'Bảng điều khiển',
            'sessionDemo' => false,
        ]);
    }

    public function sessionDemo(): void
    {
        require_login();

        Response::view('dashboard', [
            'title'       => 'Chi tiết phiên làm việc',
            'sessionDemo' => true,
        ]);
    }

    public function auditLog(): void
    {
        require_login();

        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            flash_set('danger', 'Chỉ tài khoản admin được xem audit log.');
            redirect('/dashboard');
        }

        $validEvents = [
            'LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGOUT',
            'LEAD_SUBMITTED', 'LEAD_DELETED', 'LEADS_EXPORTED',
            'LEAD_STATUS_UPDATED',
            'HONEYPOT_TRIGGERED', 'RATE_LIMIT_BLOCKED',
            'SESSION_TIMEOUT', 'CSRF_FAIL',
        ];

        $filterEvent = trim((string)($_GET['event'] ?? ''));
        if ($filterEvent !== '' && !in_array($filterEvent, $validEvents, true)) {
            $filterEvent = '';
        }

        $file  = storage_path('audit.log');
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $lines = array_reverse($lines === false ? [] : $lines);

        if ($filterEvent !== '') {
            $lines = array_values(array_filter($lines, function (string $line) use ($filterEvent): bool {
                return (bool)preg_match('/\]\s+' . preg_quote($filterEvent, '/') . '\b/', $line);
            }));
        }

        Response::view('audit_log', [
            'title'        => 'Nhật ký bảo mật',
            'logs'         => $lines,
            'filterEvent'  => $filterEvent,
            'validEvents'  => $validEvents,
        ]);
    }
}
