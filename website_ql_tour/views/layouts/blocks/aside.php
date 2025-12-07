<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <a href="<?= BASE_URL . 'home' ?>" class="brand-link">
      <img src="<?= asset('dist/assets/img/AdminLTELogo.png') ?>" alt="AdminLTE Logo"
        class="brand-image opacity-75 shadow" />
      <span class="brand-text fw-light">Quản Lý Tour</span>
    </a>
  </div>
  <!--end::Sidebar Brand-->

  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">

      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

        <!-- Dashboard (ai cũng thấy) -->
        <li class="nav-item">
          <a href="<?= BASE_URL . 'home' ?>" class="nav-link">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- ==========================
              MENU CHO ADMIN
        =========================== -->
        <?php if (isAdmin()): ?>

        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-airplane-engines"></i>
            <p>
              Quản lý Tour
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>?act=booking-list" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Quản lý đặt tour</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-person-gear"></i>
            <p>
              Quản lý Người dùng
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Danh sách Người dùng</p>
              </a>
            </li>
          </ul>
        </li>

        <?php endif; ?>
        <!-- END ADMIN -->

        <!-- ==========================
              MENU CHO HƯỚNG DẪN VIÊN (HDV)
        =========================== -->
        <?php if (isGuide()): ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-airplane-engines"></i>
            <p>
              Hướng dẫn viên
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL . '?act=hdv-tours' ?>" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Vận Hành Tour</p>
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>
        <!-- END HDV -->

        <!-- HỆ THỐNG -->
        <li class="nav-header">HỆ THỐNG</li>

        <li class="nav-item">
          <a href="<?= BASE_URL . 'logout' ?>" class="nav-link">
            <i class="nav-icon bi bi-box-arrow-right"></i>
            <p>Đăng xuất</p>
          </a>
        </li>

      </ul>

    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
