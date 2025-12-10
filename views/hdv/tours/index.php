<?php
$bookings = $bookings ?? [];
$statuses = $statuses ?? [];

// Helper function để map status ID sang tên từ database
$getStatusName = static function ($status) use ($statuses) {
    if (empty($status)) {
        return 'N/A';
    }
    
    foreach ($statuses ?? [] as $statusItem) {
        if ((string)$statusItem['id'] === (string)$status) {
            return $statusItem['name'] ?? 'N/A';
        }
    }
    
    return (string) $status;
};

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0"><?= htmlspecialchars($pageTitle ?? 'Tour được phân công') ?></h3>
  </div>
  <div class="card-body">
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
      <p class="text-muted mb-0">Chưa có booking nào được phân công.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th class="text-center">ID</th>
              <th>Tour</th>
              <th>Người tạo</th>
              <th>Trạng thái</th>
              <th>Ngày bắt đầu</th>
              <th>Ngày kết thúc</th>
              <th>Ngày tạo</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $booking): ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($booking['id']) ?></td>
                <td><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($booking['created_by_name'] ?? 'N/A') ?></td>
                <td>
                  <span class="badge bg-info">
                    <?= htmlspecialchars($getStatusName($booking['status'] ?? '')) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($booking['created_at'] ?? '') ?></td>
                <td class="d-flex justify-content-center gap-2">
                  <a href="<?= BASE_URL ?>booking-show?id=<?= urlencode($booking['id']) ?>" class="btn btn-sm btn-info" title="Xem chi tiết booking">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= BASE_URL ?>booking-diary&id=<?= urlencode($booking['id']) ?>" class="btn btn-sm btn-warning" title="Xem ghi chú">
                    <i class="bi bi-journal-text"></i>
                  </a>
                </td>
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
    'title' => $title ?? 'Tour được phân công',
    'pageTitle' => $pageTitle ?? 'Tour được phân công',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour được phân công', 'url' => BASE_URL . 'tours', 'active' => true],
    ],
]);
?>

