<?php
$category = $category ?? [];

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Chi tiết danh mục tour</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>categories" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        Quay lại
      </a>
      <a href="<?= BASE_URL ?>category-edit&id=<?= urlencode($category['id'] ?? '') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>
        Sửa
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">ID</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($category['id'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Tên danh mục</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($category['name'] ?? '') ?></p>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Mô tả</label>
        <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($category['description'] ?? '')) ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Trạng thái</label>
        <p class="form-control-plaintext">
          <span class="badge bg-<?= (int) ($category['status'] ?? 0) === 1 ? 'success' : 'secondary' ?>">
            <?= (int) ($category['status'] ?? 0) === 1 ? 'Hoạt động' : 'Ẩn' ?>
          </span>
        </p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày tạo</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($category['created_at'] ?? '') ?></p>
      </div>
      <?php if (!empty($category['updated_at'])): ?>
        <div class="col-md-6">
          <label class="form-label fw-bold">Ngày cập nhật</label>
          <p class="form-control-plaintext"><?= htmlspecialchars($category['updated_at']) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết danh mục tour',
    'pageTitle' => $pageTitle ?? 'Chi tiết danh mục tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý danh mục tour', 'url' => BASE_URL . 'categories'],
        ['label' => 'Chi tiết danh mục tour', 'url' => BASE_URL . 'category-show&id=' . urlencode($category['id'] ?? ''), 'active' => true],
    ],
]);
?>


