<?php
$errors = $errors ?? [];
$old = $old ?? [];
$category = $category ?? [];

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

<form action="<?= BASE_URL ?>category-store" method="POST" class="card">
  <div class="card-header">
    <h3 class="card-title mb-0">Thêm danh mục tour</h3>
  </div>
  <div class="card-body">
    <?php include view_path('admin.categories.partials.form_fields'); ?>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="<?= BASE_URL ?>categories" class="btn btn-secondary">Quay lại</a>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>
      Lưu danh mục
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Thêm danh mục tour',
    'pageTitle' => $pageTitle ?? 'Thêm danh mục tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý danh mục tour', 'url' => BASE_URL . 'categories'],
        ['label' => 'Thêm danh mục tour', 'url' => BASE_URL . 'category-create', 'active' => true],
    ],
]);


