<?php
// Báo cáo vận hành tour dành cho ADMIN
// Tự tính toán số liệu thống kê và báo cáo chi tiết ngay trong view này.

ob_start();

// Lấy tham số lọc thời gian
$fromDate = $_GET['from_date'] ?? '';
$toDate   = $_GET['to_date'] ?? '';

// Kết nối DB
$pdo = getDB();

// Thống kê tổng quan
$stats = [
    'totalTours'     => 0,
    'totalBookings'  => 0,
    'totalRevenue'   => 0,
];

// Báo cáo theo tour
$tourReport = [];

if ($pdo) {
    // Tổng số tour đang hoạt động
    $stats['totalTours'] = (int)$pdo->query('SELECT COUNT(*) FROM tours WHERE status = 1')->fetchColumn();

    // Điều kiện WHERE động theo thời gian cho bảng bookings
    $conditions = [];
    $params     = [];

    if (!empty($fromDate)) {
        $conditions[]        = 'b.start_date >= :from_date';
        $params['from_date'] = $fromDate;
    }

    if (!empty($toDate)) {
        $conditions[]      = 'b.end_date <= :to_date';
        $params['to_date'] = $toDate;
    }

    $whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Tổng số booking và doanh thu trong một query duy nhất
    $sqlStats = "
        SELECT 
            COUNT(*) as total_bookings,
            COALESCE(SUM(t.price), 0) as total_revenue
        FROM bookings b
        LEFT JOIN tours t ON t.id = b.tour_id
        {$whereSql}
    ";
    $stmt = $pdo->prepare($sqlStats);
    $stmt->execute($params);
    $result = $stmt->fetch();
    $stats['totalBookings'] = (int)($result['total_bookings'] ?? 0);
    $stats['totalRevenue'] = (float)($result['total_revenue'] ?? 0);

    // Báo cáo chi tiết doanh thu/chi phí/lợi nhuận theo từng tour
    // Giả định: hiện tại chưa lưu chi phí nên chi phí = 0, lợi nhuận = doanh thu
    $sqlReport = "
        SELECT
            t.id,
            t.name,
            COUNT(b.id)                      AS booking_count,
            COALESCE(SUM(t.price), 0)        AS revenue
        FROM tours t
        LEFT JOIN bookings b ON b.tour_id = t.id {$whereSql}
        GROUP BY t.id, t.name
        ORDER BY revenue DESC
    ";

    $stmt = $pdo->prepare($sqlReport);
    $stmt->execute($params);
    $tourReport = $stmt->fetchAll();
}
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <!-- Bộ lọc thời gian báo cáo -->
        <div class="card bg-light mb-4 border-0">
          <div class="card-body p-3">
            <form method="get" class="row g-3" id="filterForm" onsubmit="return validateFilterForm(event)">
              <input type="hidden" name="act" value="dashboard">
              <div class="col-md-3">
                <label for="from_date" class="form-label small fw-semibold text-muted mb-1">
                  <i class="bi bi-calendar-event me-1"></i>Từ ngày
                </label>
                <input
                  type="date"
                  id="from_date"
                  name="from_date"
                  value="<?= htmlspecialchars($fromDate) ?>"
                  class="form-control"
                >
              </div>
              <div class="col-md-3">
                <label for="to_date" class="form-label small fw-semibold text-muted mb-1">
                  <i class="bi bi-calendar-event-fill me-1"></i>Đến ngày
                </label>
                <input
                  type="date"
                  id="to_date"
                  name="to_date"
                  value="<?= htmlspecialchars($toDate) ?>"
                  class="form-control"
                >
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                  <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="<?= BASE_URL . 'dashboard' ?>" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-x-circle me-1"></i>Xóa lọc
                </a>
              </div>
              <div class="col-md-12">
                <small class="text-muted">
                  <i class="bi bi-info-circle me-1"></i>
                  Chọn khoảng ngày tương ứng với tháng / quý / năm để so sánh hiệu quả các tour.
                </small>
              </div>
            </form>
          </div>
        </div>

        <!-- Thống kê nhanh tổng quan -->
        <div class="row g-3 mb-4">
          <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="card text-bg-primary h-100 shadow-sm border-0">
              <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="flex-grow-1">
                    <h6 class="text-uppercase mb-2 small fw-semibold opacity-90">Tổng số tour hoạt động</h6>
                    <h2 class="mb-0 fw-bold">
                      <?= number_format($stats['totalTours']) ?>
                    </h2>
                  </div>
                  <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-map-fill fs-2"></i>
                  </div>
                </div>
                <p class="small mb-0 opacity-75">
                  <i class="bi bi-info-circle me-1"></i>
                  Tính cả tour có ít nhất một booking trong khoảng thời gian.
                </p>
              </div>
            </div>
          </div>

          <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="card text-bg-success h-100 shadow-sm border-0">
              <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="flex-grow-1">
                    <h6 class="text-uppercase mb-2 small fw-semibold opacity-90">Tổng số lượt đặt tour</h6>
                    <h2 class="mb-0 fw-bold">
                      <?= number_format($stats['totalBookings']) ?>
                    </h2>
                  </div>
                  <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-journal-check fs-2"></i>
                  </div>
                </div>
                <p class="small mb-0 opacity-75">
                  <i class="bi bi-info-circle me-1"></i>
                  Số lượng booking trong khoảng thời gian được chọn.
                </p>
              </div>
            </div>
          </div>

          <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="card text-bg-danger h-100 shadow-sm border-0">
              <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="flex-grow-1">
                    <h6 class="text-uppercase mb-2 small fw-semibold opacity-90">Tổng doanh thu ước tính</h6>
                    <h2 class="mb-0 fw-bold">
                      <?= number_format($stats['totalRevenue'], 0, ',', '.') ?> <small class="fs-6">₫</small>
                    </h2>
                  </div>
                  <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-cash-stack fs-2"></i>
                  </div>
                </div>
                <p class="small mb-0 opacity-75">
                  <i class="bi bi-info-circle me-1"></i>
                  Doanh thu = tổng giá tour của các lượt đặt trong khoảng thời gian.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bảng báo cáo doanh thu - chi phí - lợi nhuận theo tour -->
        <div class="mt-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
              Doanh thu, chi phí, lợi nhuận theo tour
            </h5>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4" style="width: 50px;">STT</th>
                      <th>Tour</th>
                      <th class="text-end">Số lượt đặt</th>
                      <th class="text-end">Doanh thu</th>
                      <th class="text-end">Chi phí</th>
                      <th class="text-end">Lợi nhuận</th>
                      <th class="text-end pe-4">Biên lợi nhuận (%)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($tourReport)): ?>
                      <?php $i = 1; ?>
                      <?php foreach ($tourReport as $row): ?>
                        <?php
                          $bookingCount = (int)($row['booking_count'] ?? 0);
                          $revenue      = (float)($row['revenue'] ?? 0);
                          $cost         = 0.0; // Chưa có trường chi phí trong CSDL, tạm thời = 0
                          $profit       = $revenue - $cost;
                          $margin       = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                        ?>
                        <tr>
                          <td class="ps-4 fw-semibold text-muted"><?= $i++ ?></td>
                          <td>
                            <span class="fw-semibold"><?= htmlspecialchars($row['name'] ?? 'Không xác định') ?></span>
                          </td>
                          <td class="text-end">
                            <span class="badge bg-info"><?= number_format($bookingCount) ?></span>
                          </td>
                          <td class="text-end">
                            <span class="text-success fw-semibold"><?= number_format($revenue, 0, ',', '.') ?> ₫</span>
                          </td>
                          <td class="text-end">
                            <span class="text-danger"><?= number_format($cost, 0, ',', '.') ?> ₫</span>
                          </td>
                          <td class="text-end">
                            <span class="fw-bold text-primary"><?= number_format($profit, 0, ',', '.') ?> ₫</span>
                          </td>
                          <td class="text-end pe-4">
                            <span class="badge bg-success"><?= number_format($margin, 1, ',', '.') ?>%</span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                          <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                          Chưa có dữ liệu cho khoảng thời gian được chọn.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="alert alert-info mt-3 mb-0 border-0">
            <i class="bi bi-info-circle me-2"></i>
            <small>
              <strong>Lưu ý:</strong> Báo cáo tổng hợp số tiền thu được (doanh thu), tổng chi phí phát sinh (hiện chưa có dữ liệu chi phí nên hiển thị 0)
              và lợi nhuận ước tính của từng tour. Có thể so sánh hiệu quả các tour với nhau bằng cách lọc theo khoảng thời gian
              tương ứng với từng tháng, quý, năm.
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::Row-->

<script>
function validateFilterForm(event) {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    
    if (!fromDate && !toDate) {
        event.preventDefault();
        alert('Vui lòng chọn ít nhất một ngày để lọc dữ liệu!');
        return false;
    }
    
    if (fromDate && toDate && fromDate > toDate) {
        event.preventDefault();
        alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc!');
        return false;
    }
    
    return true;
}
</script>

<?php
$content = ob_get_clean();

// Hiển thị layout với nội dung
view('layouts.AdminLayout', [
    'title' => 'Báo Cáo & Thống Kê',
    'pageTitle' => 'Báo Cáo & Thống Kê',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => true],
    ],
]);
?>