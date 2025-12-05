<?php
$tour = $tour ?? [];

ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Chi tiết tour</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>tours" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        Quay lại
      </a>
      <a href="<?= BASE_URL ?>tour-edit&id=<?= urlencode($tour['id'] ?? '') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>
        Sửa
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">Tên tour</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['name'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Danh mục</label>
        <p class="form-control-plaintext">
          <?= htmlspecialchars($tour['category_name'] ?? ($tour['category_id'] ?? 'N/A')) ?>
        </p>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Mô tả</label>
        <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($tour['description'] ?? '')) ?></p>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Lịch trình</label>
        <div class="form-control-plaintext">
          <?php
            $scheduleRaw = $tour['schedule'] ?? '';
            $decoded = json_decode($scheduleRaw, true);
            
            if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                // Nếu là {"raw":"..."}
                if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
                    $rawValue = $decoded['raw'];
                    $text = is_array($rawValue) ? implode("\n", array_map('strval', $rawValue)) : (string) $rawValue;
                    echo nl2br(htmlspecialchars($text));
                }
                // Nếu là {"days": [...]} - cấu trúc lịch trình chuẩn
                elseif (is_array($decoded) && isset($decoded['days']) && is_array($decoded['days'])) {
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
                    echo nl2br(htmlspecialchars(implode("\n", $lines)));
                }
                // Nếu là object với nhiều key khác
                elseif (is_array($decoded) && !isset($decoded[0])) {
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
                    echo nl2br(htmlspecialchars(implode("\n", $lines)));
                }
                // Nếu là array đơn giản
                elseif (is_array($decoded)) {
                    $lines = [];
                    foreach ($decoded as $item) {
                        if (is_array($item)) {
                            $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
                        } else {
                            $lines[] = (string) $item;
                        }
                    }
                    echo nl2br(htmlspecialchars(implode("\n", $lines)));
                }
                // Giá trị đơn giản
                else {
                    echo nl2br(htmlspecialchars((string) $decoded));
                }
            } else {
                // Text thuần
                echo nl2br(htmlspecialchars($scheduleRaw));
            }
          ?>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Giá tour (VNĐ)</label>
        <p class="form-control-plaintext">
          <strong class="text-primary"><?= number_format((float) ($tour['price'] ?? 0)) ?> VNĐ</strong>
        </p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Trạng thái</label>
        <p class="form-control-plaintext">
          <span class="badge bg-<?= (int) ($tour['status'] ?? 0) === 1 ? 'success' : 'secondary' ?>">
            <?= (int) ($tour['status'] ?? 0) === 1 ? 'Hoạt động' : 'Ẩn' ?>
          </span>
        </p>
      </div>
      <?php if (!empty($tour['prices'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Bảng giá chi tiết</label>
          <div class="form-control-plaintext">
            <?php
              $pricesRaw = $tour['prices'];
              $decoded = json_decode($pricesRaw, true);
              
              if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                  // Nếu là {"raw":"..."}
                  if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
                      $rawValue = $decoded['raw'];
                      $text = is_array($rawValue) ? implode("\n", array_map('strval', $rawValue)) : (string) $rawValue;
                      echo nl2br(htmlspecialchars($text));
                  }
                  // Nếu là object với nhiều key
                  elseif (is_array($decoded) && !isset($decoded[0])) {
                      $lines = [];
                      foreach ($decoded as $key => $value) {
                          if (is_array($value)) {
                              $value = implode(', ', array_map('strval', $value));
                          }
                          // Format số thành tiền nếu là số
                          if (is_numeric($value)) {
                              $value = number_format((float) $value) . ' VNĐ';
                          }
                          $lines[] = $key . ': ' . (string) $value;
                      }
                      echo nl2br(htmlspecialchars(implode("\n", $lines)));
                  }
                  // Nếu là array
                  elseif (is_array($decoded)) {
                      echo nl2br(htmlspecialchars(implode("\n", array_map('strval', $decoded))));
                  }
                  // Giá trị đơn giản
                  else {
                      echo nl2br(htmlspecialchars((string) $decoded));
                  }
              } else {
                  // Text thuần
                  echo nl2br(htmlspecialchars($pricesRaw));
              }
            ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tour['policies'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Chính sách</label>
          <div class="form-control-plaintext">
            <?php
              $policiesRaw = $tour['policies'];
              $decoded = json_decode($policiesRaw, true);
              
              if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                  // Nếu là {"raw":"..."}
                  if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
                      $rawValue = $decoded['raw'];
                      $text = is_array($rawValue) ? implode("\n", array_map('strval', $rawValue)) : (string) $rawValue;
                      echo nl2br(htmlspecialchars($text));
                  }
                  // Nếu là object với nhiều key
                  elseif (is_array($decoded) && !isset($decoded[0])) {
                      $lines = [];
                      foreach ($decoded as $key => $value) {
                          if (is_array($value)) {
                              $value = implode(', ', array_map('strval', $value));
                          }
                          $lines[] = $key . ': ' . (string) $value;
                      }
                      echo nl2br(htmlspecialchars(implode("\n", $lines)));
                  }
                  // Nếu là array
                  elseif (is_array($decoded)) {
                      echo nl2br(htmlspecialchars(implode("\n", array_map('strval', $decoded))));
                  }
                  // Giá trị đơn giản
                  else {
                      echo nl2br(htmlspecialchars((string) $decoded));
                  }
              } else {
                  // Text thuần
                  echo nl2br(htmlspecialchars($policiesRaw));
              }
            ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tour['suppliers'])): ?>
        <div class="col-12">
          <label class="form-label fw-bold">Nhà cung cấp</label>
          <div class="form-control-plaintext">
            <?php
              $suppliersRaw = $tour['suppliers'];
              $decoded = json_decode($suppliersRaw, true);
              
              if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                  // Nếu là {"raw":"..."}
                  if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
                      $rawValue = $decoded['raw'];
                      $text = is_array($rawValue) ? implode("\n", array_map('strval', $rawValue)) : (string) $rawValue;
                      echo nl2br(htmlspecialchars($text));
                  }
                  // Nếu là object với nhiều key
                  elseif (is_array($decoded) && !isset($decoded[0])) {
                      $lines = [];
                      foreach ($decoded as $key => $value) {
                          if (is_array($value)) {
                              $value = implode(', ', array_map('strval', $value));
                          }
                          $lines[] = $key . ': ' . (string) $value;
                      }
                      echo nl2br(htmlspecialchars(implode("\n", $lines)));
                  }
                  // Nếu là array
                  elseif (is_array($decoded)) {
                      echo nl2br(htmlspecialchars(implode("\n", array_map('strval', $decoded))));
                  }
                  // Giá trị đơn giản
                  else {
                      echo nl2br(htmlspecialchars((string) $decoded));
                  }
              } else {
                  // Text thuần
                  echo nl2br(htmlspecialchars($suppliersRaw));
              }
            ?>
          </div>
        </div>
      <?php endif; ?>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày tạo</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['created_at'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Ngày cập nhật</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($tour['updated_at'] ?? '') ?></p>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết tour',
    'pageTitle' => $pageTitle ?? 'Chi tiết tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý tour', 'url' => BASE_URL . 'tours'],
        ['label' => 'Chi tiết tour', 'url' => BASE_URL . 'tour-show&id=' . urlencode($tour['id'] ?? ''), 'active' => true],
    ],
]);
?>


