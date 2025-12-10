<?php
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

<?php if ($msg = getFlash('success')): ?>
  <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if ($msg = getFlash('error')): ?>
  <div class="alert alert-danger"><?= $msg ?></div>
<?php endif; ?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Danh sách Booking</h3>
        <a href="<?= BASE_URL ?>booking-create" class="btn btn-primary ms-auto">
          <i class="bi bi-plus-circle me-1"></i>
          Thêm Booking mới
        </a>
      </div>
      <div class="card-body">
        <?php if (empty($bookings)): ?>
          <p class="text-muted mb-0">Chưa có booking nào. Hãy thêm booking mới.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>STT</th>
                  <th>Tour</th>
                  <th>Người tạo</th>
                  <th>Hướng dẫn viên</th>
                  <th class="text-center">Trạng thái</th>
                  <th class="text-center">Ngày bắt đầu</th>
                  <th class="text-center">Ngày kết thúc</th>
                  <th class="text-center">Ngày tạo</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($bookings as $booking): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($booking['created_by_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($booking['assigned_guide_name'] ?? 'Chưa phân công') ?></td>
                    <td class="text-center">
                      <span class="badge bg-info">
                        <?= htmlspecialchars($getStatusName($booking['status'] ?? '')) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($booking['created_at'] ?? '') ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex justify-content-end gap-2 w-100">
                        <a href="<?= BASE_URL ?>booking-show?id=<?= urlencode($booking['id']) ?>" class="btn btn-info btn-sm" title="Xem chi tiết">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>booking-diary&id=<?= urlencode($booking['id']) ?>" class="btn btn-success btn-sm" title="Xem ghi chú">
                          <i class="bi bi-journal-text"></i>
                        </a>
                        <a href="<?= BASE_URL ?>booking-edit?id=<?= urlencode($booking['id']) ?>" class="btn btn-warning btn-sm" title="Sửa">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="<?= BASE_URL ?>booking-delete" method="POST" onsubmit="return confirm('Xác nhận xóa booking?');" class="m-0">
                          <input type="hidden" name="id" value="<?= htmlspecialchars($booking['id']) ?>" />
                          <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!--end::Row-->

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

