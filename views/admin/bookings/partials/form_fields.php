<?php
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

// Helper function để map status ID sang tên
$getStatusName = static function ($status) {
    if (empty($status)) {
        return '';
    }
    
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

// Helper function để map status name về ID (ngược lại)
$getStatusId = static function ($statusName) {
    if (empty($statusName)) {
        return '';
    }
    
    $statusMap = [
        'Đã xác nhận' => '1',
        'Đang xử lý' => '2',
        'Đã hoàn thành' => '3',
        'Đã hủy' => '4',
        'Chờ xác nhận' => '5',
    ];
    
    $statusStr = trim((string) $statusName);
    return $statusMap[$statusStr] ?? $statusStr;
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
      <option value="1" <?= (string) $value('status') === '1' ? 'selected' : '' ?>>Đã xác nhận</option>
      <option value="2" <?= (string) $value('status') === '2' ? 'selected' : '' ?>>Đang xử lý</option>
      <option value="3" <?= (string) $value('status') === '3' ? 'selected' : '' ?>>Đã hoàn thành</option>
      <option value="4" <?= (string) $value('status') === '4' ? 'selected' : '' ?>>Đã hủy</option>
      <option value="5" <?= (string) $value('status') === '5' ? 'selected' : '' ?>>Chờ xác nhận</option>
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
    <textarea name="lists_file" class="form-control" rows="3" placeholder="Danh sách file, mỗi file một dòng"><?= htmlspecialchars($extractText($value('lists_file'))) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Ghi chú</label>
    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($value('notes')) ?></textarea>
  </div>
</div>

