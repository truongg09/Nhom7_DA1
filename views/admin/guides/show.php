<?php
// Trang xem chi tiết hồ sơ Hướng dẫn viên
ob_start();

$profile = $profile ?? [];

// Helper hiển thị giá trị với mặc định "Chưa cập nhật"
$display = function ($value): string {
    $flatten = static function ($input): array {
        $result = [];
        $stack = is_array($input) ? $input : [$input];
        while ($stack) {
            $item = array_shift($stack);
            if (is_array($item)) {
                foreach ($item as $child) {
                    $stack[] = $child;
                }
                continue;
            }
            if (is_scalar($item)) {
                $str = trim((string)$item, " \t\n\r\0\x0B\"'");
                if ($str !== '') {
                    $result[] = $str;
                }
            }
        }
        return $result;
    };

    if ($value === null || $value === '') {
        return '<span class="text-muted">Chưa cập nhật</span>';
    }

    // Chuỗi JSON → mảng → flatten
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $value = $decoded;
        }
    }

    if (is_array($value)) {
        $items = $flatten($value);
        if (empty($items)) {
            return '<span class="text-muted">Chưa cập nhật</span>';
        }
        return htmlspecialchars(implode(', ', $items));
    }

    return nl2br(htmlspecialchars(trim((string)$value, "\"'")));
};

$displayAvatar = static function ($value): string {
    if ($value === null || $value === '') {
        return '<span class="text-muted">Chưa cập nhật</span>';
    }

    $url = htmlspecialchars((string)$value, ENT_QUOTES);

    return '
      <div class="d-flex align-items-center">
        <img
          src="' . $url . '"
          alt="Ảnh hồ sơ"
          class="rounded-circle border"
          style="width: 96px; height: 96px; object-fit: cover;"
        >
      </div>
    ';
};
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h3 class="card-title mb-0">Hồ sơ Hướng dẫn viên</h3>
          <br>
          <small class="text-muted">Thông tin tài khoản & hồ sơ chi tiết</small>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL . 'guides' ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
          </a>
          <a href="<?= BASE_URL . 'guide-edit&id=' . $guide['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Sửa
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($profile) && empty($guide)): ?>
          <div class="alert alert-warning mb-0">
            Chưa có hồ sơ cho HDV này.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <tbody>
                <tr><th style="width:30%;">Họ tên</th><td><?= $display($guide['name'] ?? null) ?></td></tr>
                <tr><th>Email</th><td><?= $display($guide['email'] ?? null) ?></td></tr>
                <tr>
                  <th>Trạng thái</th>
                  <td>
                    <?php if ((int)($guide['status'] ?? 0) === 1): ?>
                      <span class="badge bg-success">Hoạt động</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Khóa</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr><th>Điện thoại</th><td><?= $display($profile['phone'] ?? null) ?></td></tr>
                <tr><th>Ngày sinh</th><td><?= $display($profile['birthdate'] ?? null) ?></td></tr>
                <tr><th>Ảnh</th><td><?= $displayAvatar($profile['avatar'] ?? null) ?></td></tr>
                <tr><th>Chứng chỉ chuyên môn</th><td><?= $display($profile['certificate'] ?? null) ?></td></tr>
                <tr><th>Ngôn ngữ sử dụng</th><td><?= $display($profile['languages'] ?? null) ?></td></tr>
                <tr><th>Kinh nghiệm (năm)</th><td><?= $display($profile['experience'] ?? null) ?></td></tr>
                <tr><th>Lịch sử dẫn tour</th><td><?= $display($profile['history'] ?? null) ?></td></tr>
                <tr><th>Đánh giá năng lực / rating</th><td><?= $display($profile['rating'] ?? null) ?></td></tr>
                <tr><th>Tình trạng sức khoẻ</th><td><?= $display($profile['health_status'] ?? null) ?></td></tr>
                <tr><th>Phân loại HDV</th><td><?= $display($profile['group_type'] ?? null) ?></td></tr>
                <tr><th>Chuyên tuyến / thế mạnh</th><td><?= $display($profile['speciality'] ?? null) ?></td></tr>
                <tr><th>Ngày tạo</th><td><?= $display($guide['created_at'] ?? null) ?></td></tr>
                <tr><th>Cập nhật gần nhất</th><td><?= $display($guide['updated_at'] ?? null) ?></td></tr>
                <?php if (!empty($profile['created_at']) || !empty($profile['updated_at'])): ?>
                  <tr><th>Ngày tạo hồ sơ</th><td><?= $display($profile['created_at'] ?? null) ?></td></tr>
                  <tr><th>Ngày cập nhật hồ sơ</th><td><?= $display($profile['updated_at'] ?? null) ?></td></tr>
                <?php endif; ?>
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


