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
            'title' => 'Dashboard',
            'sessionDemo' => false,
        ]);
    }

    public function sessionDemo(): void
    {
        require_login();

        Response::view('dashboard', [
            'title' => 'Session demo',
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

        $file = storage_path('audit.log');
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

        Response::view('audit_log', [
            'title' => 'Audit log',
            'logs' => array_reverse($lines === false ? [] : $lines),
        ]);
    }
}
