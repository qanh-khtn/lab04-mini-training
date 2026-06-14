# Mini Training Center Lead Portal

Dự án PHP thuần cho Lab04: form đăng ký tư vấn khóa học, lưu lead vào JSON, đăng nhập admin/staff để xem danh sách và audit log.

## Chạy dự án

```bash
composer dump-autoload
php -S localhost:8000 -t public
```

Mở `http://localhost:8000`.

## Tài khoản demo

| Email | Mật khẩu | Role |
| --- | --- | --- |
| `admin@center.edu.vn` | `Admin@123` | `admin` |
| `staff@center.edu.vn` | `Staff@123` | `staff` |

Mật khẩu trong code được lưu bằng `password_hash(PASSWORD_DEFAULT)` và xác thực bằng `password_verify()`.

## Routes chính

| Method | Path | Mục đích |
| --- | --- | --- |
| GET | `/` | Trang chủ |
| GET | `/leads/create` | Form đăng ký tư vấn |
| POST | `/leads` | Validate, chống spam, lưu lead, PRG redirect |
| GET | `/leads` | Hiển thị lead cho người đã đăng nhập |
| GET | `/login` | Form đăng nhập |
| POST | `/login` | Xử lý đăng nhập |
| POST | `/logout` | Đăng xuất có CSRF |
| GET | `/dashboard` | Dashboard bắt buộc đăng nhập |
| GET | `/session-demo` | Demo session bắt buộc đăng nhập |
| GET | `/audit-log` | Audit log, chỉ admin |

## Điểm bảo mật đã có

- Session cookie: `PORTAL_SESSID`, `HttpOnly`, `SameSite=Lax`, `Secure` khi HTTPS.
- HTTP security headers và CSP trong `public/index.php`.
- CSRF token cho mọi form POST.
- Server-side validation đầy đủ cho form lead.
- Honeypot `website` ẩn bằng CSS `display:none !important`.
- Rate limit submit lead 5 giây bằng session.
- Flash message dùng PRG pattern.
- Idle timeout 15 phút qua `check_session_timeout()`.
- Logout chỉ dùng POST, có CSRF, clear remember token và regenerate session id.
- Remember Me dùng rotating token, lưu SHA-256 trong `storage/remember_tokens.json`.
- Audit log các event bảo mật vào `storage/audit.log`.
- 404/405 render HTML qua view.

## Storage

- `storage/leads.json`: dữ liệu lead.
- `storage/audit.log`: audit log.
- `storage/remember_tokens.json`: remember-me token đã hash.

Thư mục `storage/` được tạo tự động khi app cần ghi file.
