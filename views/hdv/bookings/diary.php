<?php
$booking = $booking ?? [];

// Helper function để extract text từ giá trị có thể là JSON hoặc text thuần
$extractText = static function ($val) {
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
        // Nếu là object với key "raw", chỉ lấy giá trị của "raw"
        if (is_array($decoded) && !isset($decoded[0]) && isset($decoded['raw']) && count($decoded) === 1) {
            $value = $decoded['raw'];
            if (is_array($value)) {
                return implode("\n", array_map('strval', $value));
            }
            return (string) $value;
        }
        
        // Nếu là object với nhiều key, convert thành text format
        if (is_array($decoded) && !isset($decoded[0])) {
            $lines = [];
            foreach ($decoded as $key => $value) {
                if ($key === 'raw') {
                    if (is_array($value)) {
                        $lines = array_merge($lines, array_map('strval', $value));
                    } else {
                        $lines[] = (string) $value;
                    }
                } else {
                    if (is_array($value)) {
                        $value = implode(', ', array_map('strval', $value));
                    }
                    $lines[] = $key . ': ' . (string) $value;
                }
            }
            return implode("\n", $lines);
        }
        
        // Nếu là array
        if (is_array($decoded)) {
            return implode("\n", array_map('strval', $decoded));
        }
        
        return (string) $decoded;
    }
    
    // Nếu không phải JSON, trả về text thuần
    return $str;
};

$currentDiary = $extractText($booking['diary'] ?? '');

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Nhật ký - Booking #<?= htmlspecialchars($booking['id'] ?? '') ?></h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>tours" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label fw-bold">Tour</label>
      <p class="form-control-plaintext"><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></p>
    </div>
    
    <form method="POST" action="<?= BASE_URL ?>booking-diary&id=<?= urlencode($booking['id'] ?? '') ?>">
      <div class="mb-3">
        <label class="form-label fw-bold">Nhật ký <span class="text-danger">*</span></label>
        <textarea name="diary" class="form-control" rows="15" required><?= htmlspecialchars($currentDiary) ?></textarea>
        <small class="form-text text-muted">
          Ghi lại các hoạt động, sự kiện, và cảm nhận trong quá trình thực hiện booking.
        </small>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i> Lưu nhật ký
        </button>
        <a href="<?= BASE_URL ?>tours" class="btn btn-secondary">
          <i class="bi bi-x-circle me-1"></i> Hủy
        </a>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Nhật ký',
    'pageTitle' => $pageTitle ?? 'Nhật ký',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour được phân công', 'url' => BASE_URL . 'tours'],
        ['label' => 'Nhật ký', 'url' => BASE_URL . 'booking-diary&id=' . urlencode($booking['id'] ?? ''), 'active' => true],
    ],
]);
?>

