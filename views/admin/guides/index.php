<?php
// Danh sách tài khoản Hướng dẫn viên
ob_start();
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách Hướng dẫn viên</h3>
        <a href="<?= BASE_URL . 'guide-create' ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Thêm HDV
        </a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>STT</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Nhóm</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-center">Ngày tạo</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($guides)): ?>
                <?php $i = 1; ?>
                <?php foreach ($guides as $guide): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($guide['name']) ?></td>
                    <td><?= htmlspecialchars($guide['email']) ?></td>
                    <td><?= htmlspecialchars($guide['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($guide['group_type'] ?? '') ?></td>
                    <td class="text-center">
                      <?php if ((int)$guide['status'] === 1): ?>
                        <span class="badge bg-success">Hoạt động</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Khóa</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($guide['created_at'] ?? '') ?>
                    </td>
                    <td class="text-end">
                      <a href="<?= BASE_URL . 'guide-show&id=' . $guide['id'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= BASE_URL . 'guide-edit&id=' . $guide['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a
                        href="<?= BASE_URL . 'guide-delete&id=' . $guide['id'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa HDV này?');"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted">
                    Chưa có HDV nào trong hệ thống.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::Row-->

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Quản lý HDV',
    'pageTitle' => 'Quản lý HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Hướng dẫn viên', 'url' => BASE_URL . 'guides', 'active' => true],
    ],
]);
?>



