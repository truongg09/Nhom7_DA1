<?php
// Helper function để map status ID sang tên (nếu là số) hoặc giữ nguyên (nếu là text)
$getStatusName = static function ($status) {
    if (empty($status)) {
        return 'N/A';
    }
    
    // Nếu là số, map sang tên trạng thái
    $statusMap = [
        '1' => 'Đã xác nhận',
        '2' => 'Đang xử lý',
        '3' => 'Đã hoàn thành',
        '4' => 'Đã hủy',
        '5' => 'Chờ xác nhận',
    ];
    
    $statusStr = (string) $status;
    return $statusMap[$statusStr] ?? $statusStr;
};

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Danh sách Booking</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>booking-create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Thêm Booking mới
      </a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
      <p class="text-muted mb-0">Chưa có booking nào. Hãy thêm booking mới.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th class="text-center">ID</th>
              <th>Tour</th>
              <th>Người tạo</th>
              <th>Hướng dẫn viên</th>
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
                <td><?= htmlspecialchars($booking['assigned_guide_name'] ?? 'Chưa phân công') ?></td>
                <td>
                  <span class="badge bg-info">
                    <?= htmlspecialchars($getStatusName($booking['status'] ?? '')) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($booking['created_at'] ?? '') ?></td>
                <td class="d-flex justify-content-center gap-2">
                  <a href="<?= BASE_URL ?>booking-show&id=<?= urlencode($booking['id']) ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= BASE_URL ?>booking-edit&id=<?= urlencode($booking['id']) ?>" class="btn btn-sm btn-warning" title="Sửa">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <form action="<?= BASE_URL ?>booking-delete" method="POST" onsubmit="return confirm('Xác nhận xóa booking?');">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($booking['id']) ?>" />
                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
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
    'title' => $title ?? 'Danh sách Booking',
    'pageTitle' => $pageTitle ?? 'Quản lý Booking',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý Booking', 'url' => BASE_URL . 'bookings', 'active' => true],
    ],
]);

