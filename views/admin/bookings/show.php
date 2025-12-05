<?php
$booking = $booking ?? [];

// Helper function để extract text từ giá trị có thể là JSON hoặc text thuần
$extractText = static function ($val, $skipKey = null) {
    if (empty($val)) {
        return '';
    }
    $str = trim((string) $val);
    if (empty($str)) {
        return '';
    }
    
    // Thử decode JSON
    $decoded = json_decode($str, true);
    
    // Nếu decode thành công và là array/object
    if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
        // Nếu có skipKey và object có key đó, chỉ lấy giá trị của key đó
        if ($skipKey && is_array($decoded) && !isset($decoded[0]) && isset($decoded[$skipKey])) {
            $value = $decoded[$skipKey];
            if (is_array($value)) {
                return implode("\n", array_map('strval', $value));
            }
            return (string) $value;
        }
        
        // Nếu là object với nhiều key, convert thành text format
        if (is_array($decoded) && !isset($decoded[0])) {
            $lines = [];
            foreach ($decoded as $key => $value) {
                if (is_array($value)) {
                    $valueStr = [];
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $valueStr[] = json_encode($item, JSON_UNESCAPED_UNICODE);
                        } else {
                            $valueStr[] = (string) $item;
                        }
                    }
                    $value = implode(', ', $valueStr);
                }
                $lines[] = $key . ': ' . (string) $value;
            }
            return implode("\n", $lines);
        }
        
        // Nếu là array, join thành text
        if (is_array($decoded)) {
            $lines = [];
            foreach ($decoded as $item) {
                if (is_array($item)) {
                    $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
                } else {
                    $lines[] = (string) $item;
                }
            }
            return implode("\n", $lines);
        }
        
        // Nếu là giá trị đơn giản
        return (string) $decoded;
    }
    
    // Nếu không phải JSON hoặc là text thuần thì trả về nguyên văn
    return $str;
};

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
    <h3 class="card-title mb-0">Chi tiết booking</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>bookings" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        Quay lại
      </a>
      <a href="<?= BASE_URL ?>booking-edit&id=<?= urlencode($booking['id'] ?? '') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>
        Sửa
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">Tour</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Người tạo</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['created_by_name'] ?? 'N/A') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Hướng dẫn viên</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['assigned_guide_name'] ?? 'Chưa phân công') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Trạng thái</label>
        <p class="form-control-plaintext">
          <span class="badge bg-info"><?= htmlspecialchars($getStatusName($booking['status'] ?? '')) ?></span>
        </p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày bắt đầu</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày kết thúc</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></p>
      </div>
      <?php if (!empty($booking['schedule_detail'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Chi tiết lịch trình</label>
          <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($extractText($booking['schedule_detail']))) ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($booking['service_detail'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Chi tiết dịch vụ</label>
          <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($extractText($booking['service_detail']))) ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($booking['diary'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Nhật ký</label>
          <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($extractText($booking['diary'], 'entries'))) ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($booking['lists_file'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Danh sách file</label>
          <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($extractText($booking['lists_file']))) ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($booking['notes'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Ghi chú</label>
          <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($booking['notes'])) ?></div>
        </div>
      <?php endif; ?>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày tạo</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['created_at'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày cập nhật</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($booking['updated_at'] ?? '') ?></p>
      </div>
    </div>

    <?php if (!empty($statusLogs)): ?>
      <hr class="my-4" />
      <h5 class="mb-3">Lịch sử thay đổi trạng thái</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead class="table-light">
            <tr>
              <th>Trạng thái cũ</th>
              <th>Trạng thái mới</th>
              <th>Người thay đổi</th>
              <th>Ghi chú</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($statusLogs as $log): ?>
              <tr>
                <td><?= htmlspecialchars($getStatusName($log['old_status'] ?? '')) ?></td>
                <td><?= htmlspecialchars($getStatusName($log['new_status'] ?? '')) ?></td>
                <td><?= htmlspecialchars($log['changed_by_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($log['note'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['changed_at'] ?? '') ?></td>
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
    'title' => $title ?? 'Chi tiết booking',
    'pageTitle' => $pageTitle ?? 'Chi tiết booking',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý Booking', 'url' => BASE_URL . 'bookings'],
        ['label' => 'Chi tiết booking', 'url' => BASE_URL . 'booking-show&id=' . urlencode($booking['id'] ?? ''), 'active' => true],
    ],
]);

