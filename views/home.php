<?php
// Sử dụng layout và truyền nội dung vào
ob_start();
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <!-- Default box -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Chào mừng đến với hệ thống quản lý tour</h3>
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
        <?php if (isLoggedIn()): ?>
          <!-- Thống kê nhanh trên dashboard -->
          <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3">
              <div class="card text-bg-primary h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                      <i class="bi bi-map-fill fs-1"></i>
                    </div>
                    <div>
                      <h6 class="text-uppercase mb-1 small">Tổng số tour</h6>
                      <h3 class="mb-0">
                        <?= number_format($stats['totalTours'] ?? 0) ?>
                      </h3>
                    </div>
                  </div>
                  <span class="small opacity-75">Tổng số tour đang có trong hệ thống</span>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3">
              <div class="card text-bg-success h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                      <i class="bi bi-journal-check fs-1"></i>
                    </div>
                    <div>
                      <h6 class="text-uppercase mb-1 small">Tổng số đặt tour</h6>
                      <h3 class="mb-0">
                        <?= number_format($stats['totalBookings'] ?? 0) ?>
                      </h3>
                    </div>
                  </div>
                  <span class="small opacity-75">Số lượng đơn đặt tour trong hệ thống</span>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3">
              <div class="card text-bg-warning h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                      <i class="bi bi-people-fill fs-1"></i>
                    </div>
                    <div>
                      <h6 class="text-uppercase mb-1 small">Tổng số khách hàng</h6>
                      <h3 class="mb-0">
                        <?= number_format($stats['totalCustomers'] ?? 0) ?>
                      </h3>
                    </div>
                  </div>
                  <span class="small opacity-75">Khách hàng đã đăng ký/đặt tour</span>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3">
              <div class="card text-bg-danger h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                      <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                    <div>
                      <h6 class="text-uppercase mb-1 small">Tổng doanh thu</h6>
                      <h3 class="mb-0">
                        <?= number_format($stats['totalRevenue'] ?? 0, 0, ',', '.') ?> đ
                      </h3>
                    </div>
                  </div>
                  <span class="small opacity-75">Tổng doanh thu từ các đơn đặt tour</span>
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-check-circle-fill me-2"></i>
              Đăng nhập thành công!
            </h4>
            <p class="mb-0">
              Xin chào, <strong><?= htmlspecialchars($user->name) ?></strong>! 
              Bạn đã đăng nhập với quyền <strong><?= $user->isAdmin() ? 'Admin' : 'Hướng dẫn viên' ?></strong>.
            </p>
          </div>

          <div class="mt-4">
            <h3 class="mb-3">
              <i class="bi bi-info-circle-fill me-2 text-primary"></i>
              Thông tin tài khoản
            </h3>
            <div class="list-group">
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">
                    <i class="bi bi-envelope me-2"></i>
                    Email
                  </h5>
                </div>
                <p class="mb-1"><?= htmlspecialchars($user->email) ?></p>
              </div>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">
                    <i class="bi bi-person-badge me-2"></i>
                    Vai trò
                  </h5>
                </div>
                <p class="mb-1">
                  <?= $user->isAdmin() ? 'Quản trị viên' : 'Hướng dẫn viên' ?>
                </p>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              Chưa đăng nhập
            </h4>
            <p class="mb-0">
              Vui lòng <a href="<?= BASE_URL ?>?act=login" class="alert-link">đăng nhập</a> để sử dụng đầy đủ chức năng.
            </p>
          </div>
        <?php endif; ?>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
</div>
<!--end::Row-->

<?php
$content = ob_get_clean();

// Hiển thị layout với nội dung
view('layouts.AdminLayout', [
    'title' => $title ?? 'Trang chủ - Website Quản Lý Tour',
    'pageTitle' => 'Trang chủ',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => true],
    ],
]);
?>
