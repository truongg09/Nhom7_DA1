<?php
$errors = $errors ?? [];
$old = $old ?? [];
$booking = $booking ?? [];

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

<form action="<?= BASE_URL ?>booking-store" method="POST" class="card">
  <div class="card-header">
    <h3 class="card-title mb-0">Thông tin booking</h3>
  </div>
  <div class="card-body">
    <?php include view_path('admin.bookings.partials.form_fields'); ?>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="<?= BASE_URL ?>bookings" class="btn btn-secondary">Quay lại</a>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>
      Lưu booking
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Thêm booking mới',
    'pageTitle' => $pageTitle ?? 'Thêm booking mới',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý Booking', 'url' => BASE_URL . 'bookings'],
        ['label' => 'Thêm booking', 'url' => BASE_URL . 'booking-create', 'active' => true],
    ],
]);

