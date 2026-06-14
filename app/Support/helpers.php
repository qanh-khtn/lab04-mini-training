<?php
declare(strict_types=1);

function root_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);

    if ($path === '') {
        return $root;
    }

    return $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function storage_path(string $path = ''): string
{
    $storage = root_path('storage');

    if (!is_dir($storage)) {
        mkdir($storage, 0775, true);
    }

    if ($path === '') {
        return $storage;
    }

    return $storage . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function view_path(string $view): string
{
    $view = trim(str_replace('\\', '/', $view), '/');

    return root_path('views/' . $view . '.php');
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url, int $status = 302): void
{
    http_response_code($status);
    header('Location: ' . $url);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function flash_get(?string $type = null): array
{
    if ($type !== null) {
        $messages = $_SESSION['_flash'][$type] ?? [];
        unset($_SESSION['_flash'][$type]);

        return $messages;
    }

    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $messages;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (is_logged_in()) {
        return;
    }

    flash_set('danger', 'Vui lòng đăng nhập để tiếp tục.');
    redirect('/login');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_verify(): void
{
    $token = (string)($_POST['csrf_token'] ?? '');
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');

    if ($token !== '' && $sessionToken !== '' && hash_equals($sessionToken, $token)) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '403 Forbidden — CSRF token invalid';
    exit;
}

function audit_log(string $event, array $extra = []): void
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $parts = ['[' . date('Y-m-d H:i:s') . ']', $event, 'ip=' . portal_log_value($ip)];

    foreach ($extra as $key => $value) {
        $safeKey = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string)$key);
        $parts[] = $safeKey . '=' . portal_log_value($value);
    }

    file_put_contents(storage_path('audit.log'), implode(' ', $parts) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function check_session_timeout(): void
{
    $idleLimit = (int)($_ENV['SESSION_IDLE_LIMIT'] ?? 900);
    $now = time();
    $lastActivity = (int)($_SESSION['last_activity_at'] ?? 0);

    if (is_logged_in() && $lastActivity > 0 && ($now - $lastActivity) > $idleLimit) {
        audit_log('SESSION_TIMEOUT', [
            'user' => $_SESSION['user_email'] ?? 'unknown',
            'idle_seconds' => $now - $lastActivity,
        ]);

        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('danger', 'Phiên làm việc đã hết hạn, vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['last_activity_at'] = $now;
}

function check_session_context(): void
{
    $context = portal_session_context();

    if (empty($_SESSION['session_context'])) {
        $_SESSION['session_context'] = $context;
        return;
    }

    if (hash_equals((string)$_SESSION['session_context'], $context)) {
        return;
    }

    if (is_logged_in()) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('danger', 'Phiên đăng nhập không còn hợp lệ, vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['session_context'] = $context;
}

function remember_tokens_file(): string
{
    return storage_path('remember_tokens.json');
}

function check_remember_me(): void
{
    if (is_logged_in()) {
        return;
    }

    $plainToken = (string)($_COOKIE['PORTAL_REMEMBER'] ?? '');

    if ($plainToken === '') {
        return;
    }

    $hash = hash('sha256', $plainToken);
    $tokens = portal_read_json_file(remember_tokens_file());
    $record = $tokens[$hash] ?? null;

    if (!is_array($record)) {
        portal_expire_remember_cookie();
        return;
    }

    unset($tokens[$hash]);
    portal_write_json_file(remember_tokens_file(), $tokens);

    if ((int)($record['expires_at'] ?? 0) < time()) {
        portal_expire_remember_cookie();
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (string)($record['user_id'] ?? '');
    $_SESSION['user_email'] = (string)($record['email'] ?? '');
    $_SESSION['user_name'] = (string)($record['name'] ?? '');
    $_SESSION['user_role'] = (string)($record['role'] ?? '');
    $_SESSION['session_context'] = portal_session_context();
    $_SESSION['last_activity_at'] = time();

    set_remember_token();
}

function set_remember_token(?array $user = null): void
{
    $user ??= [
        'id' => $_SESSION['user_id'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
    ];

    if (empty($user['id']) || empty($user['email'])) {
        return;
    }

    $plainToken = bin2hex(random_bytes(32));
    $hash = hash('sha256', $plainToken);
    $expiresAt = time() + (60 * 60 * 24 * 30);
    $tokens = portal_read_json_file(remember_tokens_file());
    $tokens = portal_prune_remember_tokens($tokens);
    $tokens[$hash] = [
        'user_id' => (string)$user['id'],
        'email' => (string)$user['email'],
        'name' => (string)($user['name'] ?? $user['email']),
        'role' => (string)($user['role'] ?? 'staff'),
        'created_at' => time(),
        'expires_at' => $expiresAt,
    ];

    portal_write_json_file(remember_tokens_file(), $tokens);
    setcookie('PORTAL_REMEMBER', $plainToken, portal_cookie_options($expiresAt));
}

function clear_remember_token(): void
{
    $plainToken = (string)($_COOKIE['PORTAL_REMEMBER'] ?? '');

    if ($plainToken !== '') {
        $hash = hash('sha256', $plainToken);
        $tokens = portal_read_json_file(remember_tokens_file());
        unset($tokens[$hash]);
        portal_write_json_file(remember_tokens_file(), $tokens);
    }

    portal_expire_remember_cookie();
}

function portal_log_value(mixed $value): string
{
    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    } elseif (is_array($value) || is_object($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    return preg_replace('/\s+/', '_', trim((string)$value));
}

function portal_session_context(): string
{
    $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

    return hash('sha256', $userAgent . '|' . $ip);
}

function portal_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function portal_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => portal_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function portal_expire_remember_cookie(): void
{
    setcookie('PORTAL_REMEMBER', '', portal_cookie_options(time() - 3600));
    unset($_COOKIE['PORTAL_REMEMBER']);
}

function portal_read_json_file(string $file): array
{
    if (!is_file($file)) {
        return [];
    }

    $contents = file_get_contents($file);
    $data = json_decode($contents === false ? '' : $contents, true);

    return is_array($data) ? $data : [];
}

function portal_write_json_file(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function portal_prune_remember_tokens(array $tokens): array
{
    $now = time();

    return array_filter($tokens, static function (array $record) use ($now): bool {
        return (int)($record['expires_at'] ?? 0) >= $now;
    });
}
