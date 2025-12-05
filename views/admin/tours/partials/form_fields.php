<?php
$value = static function ($key, $default = '') use ($old, $tour) {
    if (isset($old[$key])) {
        return $old[$key];
    }

    if (isset($tour[$key])) {
        return $tour[$key];
    }

    return $default;
};

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
        // Nếu là {"raw":"..."} thì lấy phần text bên trong
        if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
            $rawValue = $decoded['raw'];
            if (is_array($rawValue)) {
                return implode("\n", array_map('strval', $rawValue));
            }
            return (string) $rawValue;
        }
        
        // Nếu là {"days": [...]} - cấu trúc lịch trình chuẩn
        if (is_array($decoded) && isset($decoded['days']) && is_array($decoded['days'])) {
            $lines = [];
            foreach ($decoded['days'] as $day) {
                if (is_array($day)) {
                    $date = $day['date'] ?? '';
                    $activities = $day['activities'] ?? [];
                    $dayText = '';
                    if ($date) {
                        $dayText .= $date;
                    }
                    if (is_array($activities) && !empty($activities)) {
                        $dayText .= ($date ? ': ' : '') . implode(', ', array_map('strval', $activities));
                    } elseif (!empty($activities)) {
                        $dayText .= ($date ? ': ' : '') . (string) $activities;
                    }
                    if ($dayText) {
                        $lines[] = $dayText;
                    }
                } else {
                    $lines[] = (string) $day;
                }
            }
            return implode("\n", $lines);
        }
        
        // Nếu là object với nhiều key khác, convert thành text format
        if (is_array($decoded) && !isset($decoded[0])) {
            $lines = [];
            foreach ($decoded as $key => $value) {
                if (is_array($value)) {
                    // Xử lý array lồng nhau
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
        
        // Nếu là array đơn giản, join thành text
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

?>

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label">Tên tour <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($value('name')) ?>" required />
  </div>
  <div class="col-md-6">
    <label class="form-label">Danh mục tour</label>
    <select name="category_id" class="form-select">
      <option value="">-- Chọn danh mục --</option>
      <option value="1" <?= (string) $value('category_id') === '1' ? 'selected' : '' ?>>Tour trong nước</option>
      <option value="2" <?= (string) $value('category_id') === '2' ? 'selected' : '' ?>>Tour quốc tế</option>
    </select>
  </div>
  <div class="col-12">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($value('description')) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Lịch trình</label>
    <textarea name="schedule" class="form-control" rows="4" placeholder="Ví dụ: 01/07: Thăm vịnh Hạ Long..."><?= htmlspecialchars($extractText($value('schedule'))) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Bảng giá chi tiết</label>
    <textarea name="prices" class="form-control" rows="3" placeholder="Ví dụ: Người lớn: 1,500,000 VNĐ&#10;Trẻ em: 800,000 VNĐ"><?= htmlspecialchars($extractText($value('prices'))) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Chính sách</label>
    <textarea name="policies" class="form-control" rows="3"><?= htmlspecialchars($extractText($value('policies'))) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Nhà cung cấp</label>
    <textarea name="suppliers" class="form-control" rows="3"><?= htmlspecialchars($extractText($value('suppliers'))) ?></textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">Giá tour (VNĐ) <span class="text-danger">*</span></label>
    <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($value('price')) ?>" required />
  </div>
  <div class="col-md-4">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="1" <?= (int) $value('status', 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
      <option value="0" <?= (int) $value('status', 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
    </select>
  </div>
</div>

