<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class AuthController
{
    public function loginView(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }

        Response::view('auth/login', [
            'title' => 'Đăng nhập quản trị',
            'errors' => [],
            'old' => ['email' => ''],
        ]);
    }

    public function handleLogin(): void
    {
        csrf_verify();

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']);
        $user = $this->users()[$email] ?? null;

        if (!$user || !password_verify($password, $user['password_hash'])) {
            audit_log('LOGIN_FAILED', ['email' => $email !== '' ? $email : 'empty']);

            Response::view('auth/login', [
                'title' => 'Đăng nhập quản trị',
                'errors' => ['login' => 'Email hoặc mật khẩu không chính xác.'],
                'old' => ['email' => $email],
            ], 422);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['session_context'] = portal_session_context();
        $_SESSION['last_activity_at'] = time();

        if ($remember) {
            set_remember_token($user);
        } else {
            clear_remember_token();
        }

        audit_log('LOGIN_SUCCESS', [
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

        flash_set('success', 'Đăng nhập thành công.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        csrf_verify();

        audit_log('LOGOUT', [
            'email' => $_SESSION['user_email'] ?? 'unknown',
            'role' => $_SESSION['user_role'] ?? 'unknown',
        ]);

        clear_remember_token();
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('success', 'Bạn đã đăng xuất thành công.');
        redirect('/login');
    }

    private function users(): array
    {
        return [
            'admin@center.edu.vn' => [
                'id' => '1',
                'email' => 'admin@center.edu.vn',
                'name' => 'Quản trị viên',
                'role' => 'admin',
                'password_hash' => '$2y$12$P1NITKu3gqept.ePCv/yzeZzyYx0./aWPE8Q1o6JChVNYper7Dlka',
            ],
            'staff@center.edu.vn' => [
                'id' => '2',
                'email' => 'staff@center.edu.vn',
                'name' => 'Tư vấn viên',
                'role' => 'staff',
                'password_hash' => '$2y$12$/6GdK2ORALMBJIoPv89GYuLhJofdCGGG63hIKv3vJzEH0CcpaXYlO',
            ],
        ];
    }
}
