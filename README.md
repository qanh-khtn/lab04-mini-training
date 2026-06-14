# Mini Training Center Lead Portal

Ứng dụng PHP thuần cho **Lab04** — cổng nhận lead tư vấn khóa học, triển khai đầy đủ bảo mật form/session theo yêu cầu môn học Lập trình Web với PHP.

Ứng dụng được xây dựng theo kiến trúc **Front Controller + Router + MVC**, không dùng framework, không dùng database — toàn bộ dữ liệu lưu trên file JSON.

---

## Yêu cầu môi trường

- PHP 8.1 trở lên (khuyên dùng PHP 8.2+)
- Composer
- Git

Kiểm tra phiên bản:

```bash
php -v
composer -V
git --version
```

---

## Cài đặt và khởi động

**Bước 1 — Clone repository:**

```bash
git clone https://github.com/qanh-khtn/lab04-mini-training.git
cd lab04-mini-training
```

**Bước 2 — Cài đặt autoload:**

```bash
composer dump-autoload
```

Lệnh này sinh file `vendor/autoload.php` theo cấu hình PSR-4 trong `composer.json`. Không cần cài thêm thư viện ngoài nào.

**Bước 3 — Khởi động server:**

```bash
php -S localhost:8000 -t public
```

**Bước 4 — Mở trình duyệt tại:**

```
http://localhost:8000
```

Thư mục `storage/` sẽ tự tạo file `leads.json` và `audit.log` khi có dữ liệu ghi vào lần đầu.

---

## Tài khoản demo

| Role  | Email                    | Mật khẩu    |
|-------|--------------------------|-------------|
| Admin | `admin@center.edu.vn`    | `Admin@1234` |
| Staff | `staff@center.edu.vn`    | `Staff@1234` |

Mật khẩu được lưu dưới dạng hash bằng `password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12])` và xác thực bằng `password_verify()`. Không lưu mật khẩu dạng plaintext ở bất kỳ đâu.

Tài khoản admin có thêm quyền xem trang `/audit-log`. Tài khoản staff chỉ xem được danh sách lead và bảng điều khiển.

---

## Danh sách route

### Route công khai (không cần đăng nhập)

| Method | URL               | Controller@Action              | Mô tả                                              |
|--------|-------------------|--------------------------------|----------------------------------------------------|
| GET    | `/`               | `HomeController@index`         | Trang chủ giới thiệu ứng dụng                      |
| GET    | `/leads/create`   | `LeadController@create`        | Form đăng ký tư vấn khóa học                      |
| POST   | `/leads`          | `LeadController@store`         | Validate + anti-spam + lưu JSON + PRG redirect     |
| GET    | `/login`          | `AuthController@loginView`     | Form đăng nhập                                     |
| POST   | `/login`          | `AuthController@handleLogin`   | Xác thực + regenerate session + redirect           |

### Route yêu cầu đăng nhập

| Method | URL               | Controller@Action              | Mô tả                                              |
|--------|-------------------|--------------------------------|----------------------------------------------------|
| GET    | `/leads`          | `LeadController@index`         | Danh sách lead, tìm kiếm, sắp xếp, phân trang     |
| GET    | `/leads/export`   | `LeadController@export`        | Xuất toàn bộ lead ra file CSV (UTF-8 BOM)         |
| GET    | `/leads/stats`    | `LeadController@stats`         | Trang thống kê theo trạng thái, khóa học, giờ học |
| POST   | `/leads/status`   | `LeadController@updateStatus`  | Cập nhật trạng thái lead (CSRF required)           |
| POST   | `/leads/delete`   | `LeadController@destroy`       | Xóa lead (CSRF required)                          |
| GET    | `/dashboard`      | `DashboardController@index`    | Bảng điều khiển                                   |
| GET    | `/session-demo`   | `DashboardController@sessionDemo` | Hiển thị chi tiết dữ liệu session phục vụ debug |
| POST   | `/logout`         | `AuthController@logout`        | Đăng xuất sạch (CSRF required)                    |

### Route chỉ dành cho Admin

| Method | URL          | Controller@Action            | Mô tả                                    |
|--------|--------------|------------------------------|------------------------------------------|
| GET    | `/audit-log` | `DashboardController@auditLog` | Nhật ký bảo mật, có bộ lọc theo loại event |

### Xử lý lỗi (Router tự động)

| Điều kiện                                   | HTTP Status         |
|---------------------------------------------|---------------------|
| URL không tồn tại trong bảng route          | 404 Not Found       |
| URL tồn tại nhưng method HTTP không khớp    | 405 Method Not Allowed (kèm header `Allow:`) |

---

## Cấu trúc thư mục

```
lab04-mini-training/
│
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php        # Đăng nhập, đăng xuất
│   │   ├── DashboardController.php   # Dashboard, session-demo, audit-log
│   │   ├── HomeController.php        # Trang chủ
│   │   └── LeadController.php        # CRUD lead, export CSV, stats
│   ├── Core/
│   │   └── Router.php                # Đăng ký route, dispatch, 404/405
│   └── Support/
│       ├── Response.php              # view(), json(), redirect()
│       └── helpers.php               # h(), flash_set/get, is_logged_in(),
│                                     # require_login(), csrf_token/verify(),
│                                     # audit_log(), check_session_timeout(),
│                                     # check_remember_me(), storage_path()
│
├── public/
│   ├── index.php                     # Front Controller: security headers,
│   │                                 # session config, route registration
│   └── assets/
│       ├── style.css                 # CSS custom properties, dark mode
│       └── app.js                    # Dark mode toggle, toast auto-hide
│
├── views/
│   ├── layout.php                    # HTML shell, navbar, toast container
│   ├── home.php                      # Trang chủ
│   ├── dashboard.php                 # Dashboard + session-demo
│   ├── audit_log.php                 # Bảng nhật ký bảo mật
│   ├── auth/
│   │   └── login.php                 # Form đăng nhập
│   ├── leads/
│   │   ├── create.php                # Form đăng ký tư vấn
│   │   ├── index.php                 # Danh sách lead
│   │   └── stats.php                 # Trang thống kê
│   └── errors/
│       ├── 404.php
│       └── 405.php
│
├── storage/
│   ├── leads.json                    # Dữ liệu lead (tự tạo khi chạy)
│   ├── audit.log                     # Nhật ký bảo mật (tự tạo khi chạy)
│   └── remember_tokens.json          # Token ghi nhớ đăng nhập (tự tạo)
│
├── composer.json                     # PSR-4 autoload: App\ -> app/
└── vendor/
```

---

## Tính năng bảo mật

### Session cookie

Session được cấu hình trong `public/index.php` trước khi gọi `session_start()`:

- Tên session: `PORTAL_SESSID`
- `HttpOnly = true`: JavaScript không đọc được cookie qua `document.cookie`
- `SameSite = Lax`: chặn CSRF dạng cross-site POST
- `Secure = true` khi phát hiện HTTPS, `false` khi chạy local HTTP
- `session_regenerate_id(true)` sau khi đăng nhập thành công và sau khi logout

### HTTP Security Headers

Gửi kèm mọi response qua `public/index.php`:

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'
```

### CSRF Protection

- Token sinh bằng `bin2hex(random_bytes(32))` (64 ký tự), lưu vào `$_SESSION`
- Mọi form POST đều có hidden field `csrf_token`
- Xác thực bằng `hash_equals()` (constant-time, chống timing attack)
- Trả về HTTP 403 nếu token sai hoặc thiếu

### Validation server-side

Áp dụng trong `LeadController::store()`, hoạt động độc lập với HTML:

| Field              | Rule                                                        |
|--------------------|-------------------------------------------------------------|
| `full_name`        | required, độ dài 2–100 ký tự                                |
| `email`            | required, `FILTER_VALIDATE_EMAIL`                           |
| `phone`            | required, khớp regex `/^0[0-9]{9}$/` (10 số, đầu bằng 0)  |
| `course_interest`  | required, `in_array()` trong `COURSE_OPTIONS`               |
| `schedule`         | required, `in_array()` trong `SCHEDULE_OPTIONS`             |
| `message`          | optional, cắt tối đa 1000 ký tự                             |

Khi lỗi: server trả HTTP 422, render lại form với lỗi đúng từng field và giữ nguyên giá trị cũ (`$old`).

### Escape output

Mọi dữ liệu người dùng nhập khi hiển thị ra HTML đều qua hàm `h()`:

```php
function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

### Honeypot

Form đăng ký tư vấn chứa field `<input name="website">` ẩn bởi CSS (`display: none !important; visibility: hidden`). Server kiểm tra nếu field này có giá trị thì từ chối request và ghi `HONEYPOT_TRIGGERED` vào audit log. Thông báo lỗi trả về cố tình mơ hồ để không lộ cơ chế cho bot.

### Rate Limiting

Mỗi lần submit lead thành công, thời điểm được lưu vào `$_SESSION['last_lead_submit_at']`. Nếu submit lại trong vòng 5 giây, server trả HTTP 429 và ghi `RATE_LIMIT_BLOCKED` vào audit log.

### PRG Pattern

Sau khi `LeadController::store()` lưu dữ liệu thành công, server gửi `HTTP 302 Location: /leads` thay vì render HTML trực tiếp. Trình duyệt redirect về `GET /leads` — nhấn F5 không gửi lại POST.

### Idle Timeout

`check_session_timeout()` chạy ở đầu mỗi request trong `public/index.php`. Nếu thời gian không hoạt động vượt quá `SESSION_IDLE_LIMIT` (mặc định 900 giây = 15 phút), session bị hủy sạch và người dùng bị redirect về `/login`.

### Session Context

Sau khi đăng nhập, một hash `SHA-256(User-Agent + IP)` được lưu vào session. Mỗi request tiếp theo `check_session_context()` so sánh lại — nếu context thay đổi (thiết bị hoặc mạng khác), session bị hủy ngay.

### Remember Me

Khi tích "Ghi nhớ đăng nhập":
1. Server sinh token ngẫu nhiên 64 ký tự bằng `bin2hex(random_bytes(32))`
2. Lưu `SHA-256(token)` vào `storage/remember_tokens.json` cùng `expires_at` (30 ngày)
3. Gửi raw token xuống cookie `PORTAL_REMEMBER` với `HttpOnly`
4. Mỗi lần xác thực qua cookie: token cũ bị xóa, token mới được phát hành (rotating token)
5. Khi đăng xuất: record bị xóa khỏi JSON và cookie bị expire ngay lập tức

---

## Audit Log

File `storage/audit.log` ghi lại các sự kiện bảo mật theo định dạng:

```
[2026-06-14 08:30:00] LOGIN_SUCCESS ip=::1 email=admin@center.edu.vn
[2026-06-14 08:35:12] LEAD_SUBMITTED ip=::1 email=user@gmail.com course=web
[2026-06-14 08:36:00] HONEYPOT_TRIGGERED ip=::1 email=bot@test.com
[2026-06-14 08:37:05] RATE_LIMIT_BLOCKED ip=::1 email=user@gmail.com
[2026-06-14 09:00:00] SESSION_TIMEOUT ip=::1 idle=901s
[2026-06-14 09:01:00] LOGOUT ip=::1 email=admin@center.edu.vn
```

Các sự kiện được ghi: `LOGIN_SUCCESS`, `LOGIN_FAILED`, `LOGOUT`, `LEAD_SUBMITTED`, `LEAD_DELETED`, `LEAD_STATUS_UPDATED`, `LEADS_EXPORTED`, `HONEYPOT_TRIGGERED`, `RATE_LIMIT_BLOCKED`, `SESSION_TIMEOUT`, `CSRF_FAIL`.

Trang `/audit-log` chỉ dành cho tài khoản admin, có bộ lọc theo loại sự kiện.

---

## Tính năng mở rộng (làm thêm)

Ngoài các yêu cầu bắt buộc của Lab04, ứng dụng có thêm:

- **Dark Mode**: chuyển đổi sáng/tối qua nút cố định góc dưới phải, lưu trạng thái vào `localStorage`, tránh FOUC bằng script tải trước body
- **Tìm kiếm lead**: lọc theo tên, email, số điện thoại bằng `mb_strtolower()`, hỗ trợ tiếng Việt
- **Phân trang**: 10 bản ghi mỗi trang, giữ nguyên tham số tìm kiếm và sắp xếp khi chuyển trang
- **Sắp xếp cột**: click tiêu đề cột để sắp xếp tăng/giảm, server validate theo `SORT_WHITELIST` và `DIR_WHITELIST`
- **Quản lý trạng thái lead**: dropdown trên mỗi dòng, tự submit khi đổi, ghi audit log
- **Xuất CSV**: UTF-8 BOM để Excel hiển thị đúng tiếng Việt, ghi số bản ghi vào audit log
- **Trang thống kê**: 4 card theo trạng thái, biểu đồ thanh theo khóa học và khung giờ, CSS thuần không dùng thư viện
- **Audit Log với bộ lọc**: lọc theo loại event, phân quyền chỉ admin

---

## Ghi chú

- Toàn bộ dữ liệu lưu trên file JSON trong `storage/`, không dùng database. Thư mục `storage/` đã được thêm vào `.gitignore` để không commit dữ liệu thật lên GitHub.
- File `storage/users.php` chứa thông tin tài khoản demo với mật khẩu đã hash, không phải plaintext.
- Ứng dụng không dùng framework, không dùng thư viện JavaScript ngoài — toàn bộ là PHP thuần, CSS thuần và JavaScript thuần.
