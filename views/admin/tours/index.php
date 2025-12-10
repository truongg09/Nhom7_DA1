<?php
$tours = $tours ?? [];

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
        <h3 class="card-title mb-0"><?= htmlspecialchars($pageTitle ?? 'Danh sách Tour') ?></h3>
        <a href="<?= BASE_URL ?>tour-create" class="btn btn-primary ms-auto">
          <i class="bi bi-plus-circle me-1"></i>
          Thêm Tour mới
        </a>
      </div>
      <div class="card-body">
        <?php if (empty($tours)): ?>
          <p class="text-muted mb-0">Chưa có tour nào. Hãy thêm tour mới.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>STT</th>
                  <th>Tên tour</th>
                  <th>Danh mục</th>
                  <th>Lịch trình</th>
                  <th>Giá</th>
                  <th class="text-center">Trạng thái</th>
                  <th class="text-center">Ngày tạo</th>
                  <th class="text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($tours as $tour): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($tour['name']) ?></td>
                    <td><?= htmlspecialchars($tour['category_name'] ?? $tour['category_id']) ?></td>
                    <?php
                      $scheduleRaw = $tour['schedule'] ?? '';
                      $scheduleDisplay = '';
                      $decoded = json_decode($scheduleRaw, true);
                      
                      if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                          // Nếu là {"raw":"..."}
                          if (is_array($decoded) && count($decoded) === 1 && isset($decoded['raw'])) {
                              $rawValue = $decoded['raw'];
                              $text = is_array($rawValue) ? implode(', ', array_map('strval', $rawValue)) : (string) $rawValue;
                              $scheduleDisplay = mb_strimwidth($text, 0, 70, '...');
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
                              $fullText = implode(' | ', $lines);
                              $scheduleDisplay = mb_strimwidth($fullText, 0, 70, '...');
                          }
                          // Nếu là object với nhiều key khác
                          elseif (is_array($decoded) && !isset($decoded[0])) {
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
                              $fullText = implode(' | ', $lines);
                              $scheduleDisplay = mb_strimwidth($fullText, 0, 70, '...');
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
                              $fullText = implode(' | ', $lines);
                              $scheduleDisplay = mb_strimwidth($fullText, 0, 70, '...');
                          }
                          // Giá trị đơn giản
                          else {
                              $scheduleDisplay = mb_strimwidth((string) $decoded, 0, 70, '...');
                          }
                      } else {
                          // Text thuần
                          $scheduleDisplay = mb_strimwidth($scheduleRaw, 0, 70, '...');
                      }
                    ?>
                    <td class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($scheduleDisplay) ?>">
                      <?= htmlspecialchars($scheduleDisplay) ?>
                    </td>
                    <td><strong><?= number_format((float) $tour['price']) ?></strong></td>
                    <td class="text-center">
                      <span class="badge bg-<?= (int) $tour['status'] === 1 ? 'success' : 'secondary' ?>">
                        <?= (int) $tour['status'] === 1 ? 'Hoạt động' : 'Ẩn' ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($tour['created_at']) ?>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2 w-100">
                        <a href="<?= BASE_URL ?>tour-show&id=<?= urlencode($tour['id']) ?>" class="btn btn-info btn-sm" title="Xem chi tiết">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>tour-edit&id=<?= urlencode($tour['id']) ?>" class="btn btn-warning btn-sm" title="Sửa">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="<?= BASE_URL ?>tour-delete" method="POST" onsubmit="return confirm('Xác nhận xóa tour?');" class="m-0">
                          <input type="hidden" name="id" value="<?= htmlspecialchars($tour['id']) ?>" />
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
    'title' => $title ?? 'Danh sách Tour',
    'pageTitle' => $pageTitle ?? 'Danh sách Tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý tour', 'url' => BASE_URL . 'tours', 'active' => true],
    ],
]);

