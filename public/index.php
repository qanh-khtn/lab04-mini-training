<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Core\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = __DIR__ . $path;

    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';

session_name('PORTAL_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

check_remember_me();
check_session_timeout();
check_session_context();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/leads', [LeadController::class, 'index']);
$router->get('/leads/create', [LeadController::class, 'create']);
$router->post('/leads', [LeadController::class, 'store']);
$router->get('/login', [AuthController::class, 'loginView']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/session-demo', [DashboardController::class, 'sessionDemo']);
$router->get('/audit-log', [DashboardController::class, 'auditLog']);
$router->get('/leads/export', [LeadController::class, 'export']);
$router->get('/leads/stats',  [LeadController::class, 'stats']);
$router->post('/leads/delete', [LeadController::class, 'destroy']);
$router->post('/leads/status', [LeadController::class, 'updateStatus']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$router->dispatch($method, $path);
