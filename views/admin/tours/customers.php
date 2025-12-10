<?php
$tour = $tour ?? [];
$customers = $customers ?? [];

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Khách hàng - <?= htmlspecialchars($tour['name'] ?? '') ?></h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>tours" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
      </a>
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($customers)): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Tour này chưa có khách hàng nào.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th class="text-center">STT</th>
              <th>Tên khách hàng</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php foreach ($customers as $customer): ?>
              <tr>
                <td class="text-center"><?= $i++ ?></td>
                <td><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Khách hàng',
    'pageTitle' => $pageTitle ?? 'Khách hàng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý tour', 'url' => BASE_URL . 'tours'],
        ['label' => 'Khách hàng', 'url' => BASE_URL . 'tour-customers&id=' . urlencode($tour['id'] ?? ''), 'active' => true],
    ],
]);
?>

