<div class="hero">
    <h1>Trung tâm Đào tạo<br>Công nghệ Chuyên nghiệp</h1>
    <p>Đăng ký tư vấn miễn phí, chúng tôi sẽ liên hệ trong vòng 24 giờ.</p>
    <a class="btn btn-primary" href="/leads/create">Đăng ký tư vấn ngay</a>
    <?php if (is_logged_in()): ?>
        <a class="btn btn-secondary" href="/leads" style="margin-left:10px">Xem danh sách đăng ký</a>
    <?php else: ?>
        <a class="btn btn-secondary" href="/login" style="margin-left:10px">Đăng nhập quản trị</a>
    <?php endif; ?>
</div>

<div class="grid-4" style="margin-top:8px">
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#1769e0,#0f4da8)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
        </div>
        <h3>Lập trình Web</h3>
        <p>HTML, CSS, JavaScript, PHP, React - từ cơ bản đến nâng cao, có dự án thực tế.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#168251,#0d5c38)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
        </div>
        <h3>Lập trình Mobile</h3>
        <p>Flutter, React Native - xây dựng app iOS &amp; Android thực chiến.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#b7790d,#8b5e0a)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        </div>
        <h3>Data Science</h3>
        <p>Python, SQL, Machine Learning - phân tích dữ liệu và trực quan hóa dữ liệu.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
        </div>
        <h3>AI &amp; Automation</h3>
        <p>Ứng dụng AI vào công việc, tự động hóa quy trình với các công cụ hiện đại.</p>
    </div>
</div>

<div class="grid-2" style="margin-top:18px">
    <div class="card">
        <h3 style="margin:0 0 10px;font-size:16px">Quy trình đăng ký tư vấn</h3>
        <ol style="margin:0;padding-left:20px;color:var(--muted);line-height:2">
            <li>Điền form đăng ký với thông tin liên hệ và khóa học quan tâm</li>
            <li>Hệ thống xác nhận và lưu thông tin đăng ký</li>
            <li>Tư vấn viên liên hệ theo khung giờ bạn chọn</li>
            <li>Tư vấn 1-1 và chọn khóa học phù hợp</li>
        </ol>
    </div>
    <div class="card">
        <h3 style="margin:0 0 10px;font-size:16px">Bảo mật &amp; An toàn</h3>
        <ul style="margin:0;padding-left:20px;color:var(--muted);line-height:2">
            <li>CSRF token bảo vệ mọi form gửi dữ liệu</li>
            <li>Honeypot và rate limit chống spam tự động</li>
            <li>Session bảo mật với HttpOnly, SameSite=Lax</li>
            <li>Nhật ký bảo mật ghi lại toàn bộ sự kiện quan trọng</li>
        </ul>
    </div>
</div>
