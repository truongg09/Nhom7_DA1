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
        // Lọc theo ngày khởi hành tour (start_date trong bảng bookings)
        $conditions[]        = 'b.start_date >= :from_date';
        $params['from_date'] = $fromDate;
    }

    if (!empty($toDate)) {
        // Lọc theo ngày kết thúc tour (end_date trong bảng bookings)
        $conditions[]      = 'b.end_date <= :to_date';
        $params['to_date'] = $toDate;
    }

    $whereSql = '';
    if (!empty($conditions)) {
        $whereSql = 'WHERE ' . implode(' AND ', $conditions);
    }

    // Tổng số booking trong khoảng thời gian
    $sqlTotalBookings = "SELECT COUNT(*) FROM bookings b {$whereSql}";
    $stmt = $pdo->prepare($sqlTotalBookings);
    $stmt->execute($params);
    $stats['totalBookings'] = (int)$stmt->fetchColumn();

    // Tổng doanh thu = tổng (giá tour) của các lượt đặt
    $sqlTotalRevenue = "
        SELECT COALESCE(SUM(t.price), 0)
        FROM bookings b
        JOIN tours t ON t.id = b.tour_id
        {$whereSql}
    ";
    $stmt = $pdo->prepare($sqlTotalRevenue);
    $stmt->execute($params);
    $stats['totalRevenue'] = (float)$stmt->fetchColumn();

    // Báo cáo chi tiết doanh thu/chi phí/lợi nhuận theo từng tour
    // Giả định: hiện tại chưa lưu chi phí nên chi phí = 0, lợi nhuận = doanh thu
    $sqlReport = "
        SELECT
            t.id,
            t.name,
            COUNT(b.id)                      AS booking_count,
            COALESCE(SUM(t.price), 0)        AS revenue
        FROM tours t
        LEFT JOIN bookings b ON b.tour_id = t.id
        {$whereSql}
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
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Báo Cáo & Thống Kê</h3>
        <div class="card-tools">
          <button
            type="button"
            class="btn btn-tool"
            data-lte-toggle="card-collapse"
            title="Collapse"
          >
            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Bộ lọc thời gian báo cáo (tháng / quý / năm theo khoảng ngày) -->
        <form method="get" class="row g-3 mb-4">
          <input type="hidden" name="act" value="home">
          <div class="col-md-3">
            <label for="from_date" class="form-label small fw-semibold">Từ ngày</label>
            <input
              type="date"
              id="from_date"
              name="from_date"
              value="<?= htmlspecialchars($fromDate) ?>"
              class="form-control form-control-sm"
            >
          </div>
          <div class="col-md-3">
            <label for="to_date" class="form-label small fw-semibold">Đến ngày</label>
            <input
              type="date"
              id="to_date"
              name="to_date"
              value="<?= htmlspecialchars($toDate) ?>"
              class="form-control form-control-sm"
            >
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-sm me-2">
              <i class="bi bi-funnel me-1"></i>Lọc
            </button>
            <a href="<?= BASE_URL . 'home' ?>" class="btn btn-outline-secondary btn-sm me-2">
              Xóa lọc
            </a>
            <span class="small text-muted">
              Chọn khoảng ngày tương ứng với tháng / quý / năm để so sánh hiệu quả các tour.
            </span>
          </div>
        </form>

        <!-- Thống kê nhanh tổng quan -->
        <div class="row mb-4">
          <div class="col-sm-4 col-lg-4 mb-3">
            <div class="card text-bg-primary h-100 shadow-sm">
              <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-2">
                  <div class="me-3">
                    <i class="bi bi-map-fill fs-1"></i>
                  </div>
                  <div>
                    <h6 class="text-uppercase mb-1 small">Tổng số tour hoạt động</h6>
                    <h3 class="mb-0">
                      <?= number_format($stats['totalTours']) ?>
                    </h3>
                  </div>
                </div>
                <span class="small opacity-75">Tính cả tour có ít nhất một booking trong khoảng thời gian.</span>
              </div>
            </div>
          </div>

          <div class="col-sm-4 col-lg-4 mb-3">
            <div class="card text-bg-success h-100 shadow-sm">
              <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-2">
                  <div class="me-3">
                    <i class="bi bi-journal-check fs-1"></i>
                  </div>
                  <div>
                    <h6 class="text-uppercase mb-1 small">Tổng số lượt đặt tour</h6>
                    <h3 class="mb-0">
                      <?= number_format($stats['totalBookings']) ?>
                    </h3>
                  </div>
                </div>
                <span class="small opacity-75">Số lượng booking trong khoảng thời gian được chọn.</span>
              </div>
            </div>
          </div>

          <div class="col-sm-4 col-lg-4 mb-3">
            <div class="card text-bg-danger h-100 shadow-sm">
              <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-2">
                  <div class="me-3">
                    <i class="bi bi-cash-stack fs-1"></i>
                  </div>
                  <div>
                    <h6 class="text-uppercase mb-1 small">Tổng doanh thu ước tính</h6>
                    <h3 class="mb-0">
                      <?= number_format($stats['totalRevenue'], 0, ',', '.') ?> đ
                    </h3>
                  </div>
                </div>
                <span class="small opacity-75">
                  Doanh thu = tổng giá tour của các lượt đặt trong khoảng thời gian.
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Bảng báo cáo doanh thu - chi phí - lợi nhuận theo tour -->
        <div class="mt-4">
          <h5 class="mb-3">
            <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
            Doanh thu, chi phí, lợi nhuận theo tour
          </h5>

          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>STT</th>
                  <th>Tour</th>
                  <th class="text-end">Số lượt đặt</th>
                  <th class="text-end">Doanh thu</th>
                  <th class="text-end">Chi phí</th>
                  <th class="text-end">Lợi nhuận</th>
                  <th class="text-end">Biên lợi nhuận (%)</th>
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
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['name'] ?? 'Không xác định') ?></td>
                      <td class="text-end"><?= number_format($bookingCount) ?></td>
                      <td class="text-end text-success"><?= number_format($revenue, 0, ',', '.') ?> đ</td>
                      <td class="text-end text-danger"><?= number_format($cost, 0, ',', '.') ?> đ</td>
                      <td class="text-end fw-semibold"><?= number_format($profit, 0, ',', '.') ?> đ</td>
                      <td class="text-end">
                        <?= number_format($margin, 1, ',', '.') ?>%
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted">
                      Chưa có dữ liệu cho khoảng thời gian được chọn.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <p class="small text-muted mt-2 mb-0">
            * Báo cáo tổng hợp số tiền thu được (doanh thu), tổng chi phí phát sinh (hiện chưa có dữ liệu chi phí nên hiển thị 0)
            và lợi nhuận ước tính của từng tour. Có thể so sánh hiệu quả các tour với nhau bằng cách lọc theo khoảng thời gian
            tương ứng với từng tháng, quý, năm.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::Row-->

<?php
$content = ob_get_clean();

// Hiển thị layout với nội dung
view('layouts.AdminLayout', [
    'title' => 'Báo cáo vận hành tour',
    'pageTitle' => 'Báo cáo vận hành tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => true],
    ],
]);
?>