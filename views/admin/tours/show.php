<?php
$tour = $tour ?? [];

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Chi tiết tour</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>tours" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        Quay lại
      </a>
      <a href="<?= BASE_URL ?>tour-edit&id=<?= urlencode($tour['id'] ?? '') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>
        Sửa
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">Tên tour</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['name'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Danh mục</label>
        <p class="form-control-plaintext">
          <?= htmlspecialchars($tour['category_name'] ?? ($tour['category_id'] ?? 'N/A')) ?>
        </p>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Mô tả</label>
        <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($tour['description'] ?? '')) ?></p>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Lịch trình</label>
        <div class="form-control-plaintext">
          <?php
            $scheduleRaw = $tour['schedule'] ?? '';
            $scheduleData = json_decode($scheduleRaw, true);

            if (is_array($scheduleData) && isset($scheduleData['days']) && is_array($scheduleData['days'])) {
                echo '<ul class="mb-0">';
                foreach ($scheduleData['days'] as $day) {
                    $date = $day['date'] ?? '';
                    $activities = $day['activities'] ?? [];
                    echo '<li><strong>' . htmlspecialchars($date) . ':</strong> ';
                    if (is_array($activities)) {
                        echo implode(', ', array_map('htmlspecialchars', $activities));
                    } else {
                        echo htmlspecialchars($activities);
                    }
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<pre class="mb-0">' . htmlspecialchars($scheduleRaw) . '</pre>';
            }
          ?>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Giá tour (VNĐ)</label>
        <p class="form-control-plaintext">
          <strong class="text-primary"><?= number_format((float) ($tour['price'] ?? 0)) ?> VNĐ</strong>
        </p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Trạng thái</label>
        <p class="form-control-plaintext">
          <span class="badge bg-<?= (int) ($tour['status'] ?? 0) === 1 ? 'success' : 'secondary' ?>">
            <?= (int) ($tour['status'] ?? 0) === 1 ? 'Hoạt động' : 'Ẩn' ?>
          </span>
        </p>
      </div>
      <?php if (!empty($tour['images'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Ảnh</label>
          <div class="form-control-plaintext">
            <?php
              $images = json_decode($tour['images'], true);
              if (is_array($images)) {
                  echo '<div class="d-flex flex-wrap gap-2">';
                  foreach ($images as $img) {
                      echo '<span class="badge bg-info">' . htmlspecialchars($img) . '</span>';
                  }
                  echo '</div>';
              } else {
                  echo '<pre class="mb-0">' . htmlspecialchars($tour['images']) . '</pre>';
              }
            ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tour['prices'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Bảng giá chi tiết</label>
          <div class="form-control-plaintext">
            <pre class="mb-0"><?= htmlspecialchars($tour['prices']) ?></pre>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tour['policies'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Chính sách</label>
          <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($tour['policies'])) ?></p>
        </div>
      <?php endif; ?>
      <?php if (!empty($tour['suppliers'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Nhà cung cấp</label>
          <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($tour['suppliers'])) ?></p>
        </div>
      <?php endif; ?>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày tạo</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['created_at'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày cập nhật</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['updated_at'] ?? '') ?></p>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết tour',
    'pageTitle' => $pageTitle ?? 'Chi tiết tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý tour', 'url' => BASE_URL . 'tours'],
        ['label' => 'Chi tiết tour', 'url' => BASE_URL . 'tour-show&id=' . urlencode($tour['id'] ?? ''), 'active' => true],
    ],
]);
?>


