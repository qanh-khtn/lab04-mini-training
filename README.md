# Mini Training Center Lead Portal

Ứng dụng PHP thuần cho **Lab04** — cổng nhận lead tư vấn khóa học, triển khai đầy đủ bảo mật form/session theo yêu cầu môn học.

## Khởi động

```bash
composer dump-autoload
php -S localhost:8000 -t public
```

Mở trình duyệt tại `http://localhost:8000`.

## Tài khoản demo

| Role | Email | Mật khẩu |
|------|-------|----------|
| Admin | `admin@center.edu.vn` | `Admin@1234` |
| Staff | `staff@center.edu.vn` | `Staff@1234` |

Mật khẩu lưu bằng `password_hash(PASSWORD_BCRYPT, cost=12)`, xác thực bằng `password_verify()`.

## Routes

| Method | URL | Mô tả |
|--------|-----|-------|
| GET | `/` | Trang chủ |
| GET | `/leads/create` | Form đăng ký tư vấn |
| POST | `/leads` | Validate + anti-spam + lưu JSON + PRG redirect |
| GET | `/leads` | Danh sách lead (yêu cầu đăng nhập) |
| GET | `/login` | Form đăng nhập |
| POST | `/login` | Xử lý đăng nhập |
| POST | `/logout` | Đăng xuất (có CSRF) |
| GET | `/dashboard` | Bảng điều khiển (yêu cầu đăng nhập) |
| GET | `/session-demo` | Thông tin session debug |
| GET | `/audit-log` | Nhật ký bảo mật (chỉ admin) |
| * | URL không tồn tại | 404 Not Found |
| * | Sai method | 405 Method Not Allowed |

## Cấu trúc thư mục

```
lab04-mini-training/
├── app/
│   ├── Controllers/        # HomeController, LeadController, AuthController, DashboardController
│   ├── Core/Router.php     # Front Controller router (404/405)
│   └── Support/            # Response, helpers.php
├── public/
│   ├── index.php           # Front Controller, session config, route registration
│   └── assets/             # style.css, app.js
├── views/
│   ├── layout.php
│   ├── home.php
│   ├── leads/              # create.php, index.php
│   ├── auth/login.php
│   ├── dashboard.php
│   ├── audit_log.php
│   └── errors/             # 404.php, 405.php
├── storage/                # leads.json, audit.log (tự tạo khi chạy)
└── vendor/
```

## Tính năng bảo mật

| Tính năng | Chi tiết |
|-----------|---------|
| Session cookie | `PORTAL_SESSID`, `HttpOnly`, `SameSite=Lax`, `Secure` khi HTTPS |
| Security headers | `X-Frame-Options`, `X-Content-Type-Options`, `CSP` |
| CSRF | Token `bin2hex(random_bytes(32))`, verify bằng `hash_equals()`, trả 403 nếu sai |
| Validation | required, email format, phone `/^0[0-9]{9}$/`, length, in-list |
| Honeypot | Field `website` ẩn bằng CSS, block nếu có giá trị |
| Rate limit | Không cho submit lead 2 lần trong 5 giây (session) |
| PRG | Sau POST thành công redirect GET, tránh resubmit |
| Flash message | Hiện đúng 1 lần sau redirect (toast tự ẩn sau 5 giây) |
| Idle timeout | 15 phút không hoạt động → logout tự động |
| Session context | Hash UA + IP, logout nếu context thay đổi |
| Remember Me | Rotating SHA-256 token, 30 ngày, không lưu password |
| Audit log | Ghi file `storage/audit.log` các event: LOGIN, LOGOUT, LEAD, HONEYPOT, RATE_LIMIT, TIMEOUT |
