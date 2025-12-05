<?php
$errors = $errors ?? [];
$booking = $booking ?? [];
$old = $old ?? [];

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

<form action="<?= BASE_URL ?>booking-update" method="POST" class="card">
  <input type="hidden" name="id" value="<?= htmlspecialchars($booking['id'] ?? $old['id'] ?? '') ?>" />
  <div class="card-header">
    <h3 class="card-title mb-0">Cập nhật thông tin booking</h3>
  </div>
  <div class="card-body">
    <?php include view_path('admin.bookings.partials.form_fields'); ?>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="<?= BASE_URL ?>bookings" class="btn btn-secondary">Quay lại</a>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>
      Cập nhật
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chỉnh sửa booking',
    'pageTitle' => $pageTitle ?? 'Chỉnh sửa booking',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý Booking', 'url' => BASE_URL . 'bookings'],
        ['label' => 'Chỉnh sửa booking', 'url' => BASE_URL . 'booking-edit&id=' . urlencode($booking['id'] ?? ''), 'active' => true],
    ],
]);

