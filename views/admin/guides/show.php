<?php
// Trang xem chi tiết hồ sơ Hướng dẫn viên
ob_start();

$profile = $profile ?? [];

// Helper hiển thị giá trị với mặc định "Chưa cập nhật"
$display = function ($value): string {
    if ($value === null || $value === '') {
        return '<span class="text-muted">Chưa cập nhật</span>';
    }

    // Nếu là mảng (hoặc chuỗi JSON) thì encode gọn để tránh warning
    if (is_array($value)) {
        $value = array_filter($value, static fn($v) => $v !== '' && $v !== null);
        if (empty($value)) {
            return '<span class="text-muted">Chưa cập nhật</span>';
        }
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $decoded = array_filter($decoded, static fn($v) => $v !== '' && $v !== null);
            if (!empty($decoded)) {
                return htmlspecialchars(json_encode($decoded, JSON_UNESCAPED_UNICODE));
            }
            return '<span class="text-muted">Chưa cập nhật</span>';
        }
    }

    return nl2br(htmlspecialchars((string)$value));
};
?>

<div class="row">
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Thông tin tài khoản</h3>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted d-block">Họ tên</small>
          <span class="fw-semibold"><?= htmlspecialchars($guide['name'] ?? '') ?></span>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block">Email</small>
          <span class="fw-semibold"><?= htmlspecialchars($guide['email'] ?? '') ?></span>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block">Trạng thái</small>
          <?php if ((int)($guide['status'] ?? 0) === 1): ?>
            <span class="badge bg-success">Hoạt động</span>
          <?php else: ?>
            <span class="badge bg-secondary">Khóa</span>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block">Ngày tạo</small>
          <span><?= htmlspecialchars($guide['created_at'] ?? '') ?></span>
        </div>
        <div>
          <small class="text-muted d-block">Cập nhật gần nhất</small>
          <span><?= htmlspecialchars($guide['updated_at'] ?? '') ?></span>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="<?= BASE_URL . 'guides' ?>" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
        <a href="<?= BASE_URL . 'guide-edit&id=' . $guide['id'] ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil-square me-1"></i> Sửa
        </a>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Hồ sơ chi tiết</h3>
        <span class="text-muted small">user_id: <?= htmlspecialchars($guide['id']) ?></span>
      </div>
      <div class="card-body">
        <?php if (empty($profile)): ?>
          <div class="alert alert-warning mb-0">
            Chưa có hồ sơ cho HDV này.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <tbody>
                <tr><th style="width:35%;">Điện thoại</th><td><?= $display($profile['phone'] ?? null) ?></td></tr>
                <tr><th>Ngày sinh</th><td><?= $display($profile['birthdate'] ?? null) ?></td></tr>
                <tr><th>Ảnh (URL)</th><td><?= $display($profile['avatar'] ?? null) ?></td></tr>
                <tr><th>Chứng chỉ chuyên môn</th><td><?= $display($profile['certificate'] ?? null) ?></td></tr>
                <tr><th>Ngôn ngữ sử dụng</th><td><?= $display($profile['languages'] ?? null) ?></td></tr>
                <tr><th>Kinh nghiệm (năm)</th><td><?= $display($profile['experience'] ?? null) ?></td></tr>
                <tr><th>Lịch sử dẫn tour</th><td><?= $display($profile['history'] ?? null) ?></td></tr>
                <tr><th>Đánh giá năng lực / rating</th><td><?= $display($profile['rating'] ?? null) ?></td></tr>
                <tr><th>Tình trạng sức khoẻ</th><td><?= $display($profile['health_status'] ?? null) ?></td></tr>
                <tr><th>Phân loại HDV</th><td><?= $display($profile['group_type'] ?? null) ?></td></tr>
                <tr><th>Chuyên tuyến / thế mạnh</th><td><?= $display($profile['speciality'] ?? null) ?></td></tr>
                <tr><th>Ngày tạo hồ sơ</th><td><?= $display($profile['created_at'] ?? null) ?></td></tr>
                <tr><th>Ngày cập nhật</th><td><?= $display($profile['updated_at'] ?? null) ?></td></tr>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Hồ sơ HDV',
    'pageTitle' => 'Hồ sơ chi tiết HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Hướng dẫn viên', 'url' => BASE_URL . 'guides', 'active' => false],
        ['label' => 'Hồ sơ chi tiết', 'url' => BASE_URL . 'guide-show&id=' . $guide['id'], 'active' => true],
    ],
]);
?>


