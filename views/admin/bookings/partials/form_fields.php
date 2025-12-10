<?php
$statuses = $statuses ?? [];
$value = static function ($key, $default = '') use ($old, $booking) {
    if (isset($old[$key])) {
        return $old[$key];
    }

    if (isset($booking[$key])) {
        return $booking[$key];
    }

    return $default;
};

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
            $result = (string) $value;
            // Loại bỏ "raw: " nếu có ở đầu
            $result = preg_replace('/^raw:\s*/i', '', $result);
            return $result;
        }
        
        // Nếu là object với key "raw", chỉ lấy giá trị của "raw" (không hiển thị "raw:")
        if (is_array($decoded) && !isset($decoded[0]) && isset($decoded['raw']) && count($decoded) === 1) {
            $value = $decoded['raw'];
            if (is_array($value)) {
                return implode("\n", array_map('strval', $value));
            }
            $result = (string) $value;
            // Loại bỏ "raw: " nếu có ở đầu (xử lý nested)
            $result = preg_replace('/^raw:\s*/i', '', $result);
            return $result;
        }
        
        // Nếu là object với nhiều key, convert thành text format (nhưng bỏ qua key "raw")
        if (is_array($decoded) && !isset($decoded[0])) {
            $lines = [];
            foreach ($decoded as $key => $value) {
                // Bỏ qua key "raw" khi hiển thị
                if ($key === 'raw') {
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
                    $result = (string) $value;
                    // Loại bỏ "raw: " nếu có ở đầu
                    $result = preg_replace('/^raw:\s*/i', '', $result);
                    return $result;
                }
                
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
        $result = (string) $decoded;
        // Loại bỏ "raw: " nếu có ở đầu
        $result = preg_replace('/^raw:\s*/i', '', $result);
        return $result;
    }
    
    // Nếu không phải JSON hoặc là text thuần thì loại bỏ "raw: " nếu có ở đầu
    $result = $str;
    // Loại bỏ "raw: " hoặc "raw: raw: " nếu có ở đầu (xử lý nested)
    $result = preg_replace('/^(raw:\s*)+/i', '', $result);
    return $result;
};

// Helper function để map status ID sang tên từ database
$getStatusName = static function ($status) use ($statuses) {
    if (empty($status)) {
        return '';
    }
    
    foreach ($statuses as $statusItem) {
        if ((string)$statusItem['id'] === (string)$status) {
            return $statusItem['name'] ?? '';
        }
    }
    
    return (string) $status;
};
?>

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label">Tour <span class="text-danger">*</span></label>
    <select name="tour_id" class="form-select" required>
      <option value="">-- Chọn tour --</option>
      <?php foreach ($tours as $tour): ?>
        <option value="<?= htmlspecialchars($tour['id']) ?>" <?= (string) $value('tour_id') === (string) $tour['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($tour['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Người tạo</label>
    <select name="created_by" class="form-select">
      <option value="">-- Chọn người tạo --</option>
      <?php foreach ($users as $user): ?>
        <option value="<?= htmlspecialchars($user['id']) ?>" <?= (string) $value('created_by') === (string) $user['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Hướng dẫn viên</label>
    <select name="assigned_guide_id" class="form-select">
      <option value="">-- Chọn hướng dẫn viên --</option>
      <?php foreach ($users as $user): ?>
        <?php if ($user['role'] === 'huong_dan_vien' || $user['role'] === 'admin'): ?>
          <option value="<?= htmlspecialchars($user['id']) ?>" <?= (string) $value('assigned_guide_id') === (string) $user['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($user['name']) ?>
          </option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="">-- Chọn trạng thái --</option>
      <?php foreach ($statuses ?? [] as $statusItem): ?>
        <option value="<?= htmlspecialchars($statusItem['id']) ?>" <?= (string) $value('status') === (string) $statusItem['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($statusItem['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Ngày bắt đầu</label>
    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($value('start_date')) ?>" />
  </div>
  <div class="col-md-6">
    <label class="form-label">Ngày kết thúc</label>
    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($value('end_date')) ?>" />
  </div>
  <div class="col-12">
    <label class="form-label">Chi tiết lịch trình</label>
    <textarea name="schedule_detail" class="form-control" rows="4"><?= htmlspecialchars($extractText($value('schedule_detail'))) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Chi tiết dịch vụ</label>
    <textarea name="service_detail" class="form-control" rows="4"><?= htmlspecialchars($extractText($value('service_detail'))) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Nhật ký</label>
    <textarea name="diary" class="form-control" rows="4"><?= htmlspecialchars($extractText($value('diary'), 'entries')) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Danh sách file</label>
    <?php 
    $currentFile = $value('lists_file');
    $fileUrl = '';
    $originalFileName = '';
    
    if (!empty($currentFile)) {
        // Kiểm tra xem có phải là JSON không
        $decoded = json_decode($currentFile, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            // Định dạng mới với url và original_name
            if (isset($decoded['url']) && isset($decoded['original_name'])) {
                $fileUrl = $decoded['url'];
                $originalFileName = $decoded['original_name'];
            }
            // Định dạng cũ với raw
            elseif (isset($decoded['raw'])) {
                $fileUrl = $decoded['raw'];
                $originalFileName = basename($fileUrl);
            }
            // String trong JSON
            elseif (is_string($decoded)) {
                $fileUrl = $decoded;
                $originalFileName = basename($fileUrl);
            }
        } else {
            // Text thuần
            $fileUrl = $currentFile;
            $originalFileName = basename($fileUrl);
        }
    }
    ?>
    <?php 
    // Lấy booking ID từ booking hoặc old
    $bookingId = $booking['id'] ?? $old['id'] ?? '';
    ?>
    <?php if (!empty($fileUrl) && !empty($bookingId)): ?>
      <div class="mb-2">
        <?php 
        $displayName = $originalFileName ?: basename($fileUrl);
        if (empty($displayName) || $displayName === $fileUrl) {
          $displayName = 'danh_sach_booking_' . $bookingId;
        }
        ?>
        <div>
          <i class="bi bi-file-earmark me-1"></i>
          <a href="<?= BASE_URL ?>booking-download-lists-file?id=<?= urlencode($bookingId) ?>" class="text-decoration-none">
            <?= htmlspecialchars($displayName) ?>
          </a>
        </div>
      </div>
    <?php endif; ?>
    <input type="file" name="lists_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.jpg,.jpeg,.png" />
  </div>
  <div class="col-12">
    <label class="form-label">Ghi chú</label>
    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($value('notes')) ?></textarea>
  </div>
</div>

