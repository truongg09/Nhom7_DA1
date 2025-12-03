<?php
$errors = $errors ?? [];
$old = $old ?? [];
$tour = $tour ?? [];

ob_start();
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>tour-store" method="POST" class="card">
  <div class="card-header">
    <h3 class="card-title mb-0">Thông tin tour</h3>
  </div>
  <div class="card-body">
    <?php include view_path('admin.tours.partials.form_fields'); ?>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="<?= BASE_URL ?>tours" class="btn btn-secondary">Quay lại</a>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>
      Lưu tour
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Thêm tour mới',
    'pageTitle' => $pageTitle ?? 'Thêm tour mới',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý tour', 'url' => BASE_URL . 'tours'],
        ['label' => 'Thêm tour', 'url' => BASE_URL . 'tour-create', 'active' => true],
    ],
]);

