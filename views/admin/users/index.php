<?php
// Danh sách tài khoản người dùng
ob_start();
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Tất cả tài khoản người dùng</h3>
        <a href="<?= BASE_URL . 'user-create' ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Thêm người dùng
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
                <th>Vai trò</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-center">Ngày tạo</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)): ?>
                <?php $i = 1; ?>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="text-center">
                      <?php if ((int)($user['status'] ?? 0) === 1): ?>
                        <span class="badge bg-success">Hoạt động</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Khóa</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($user['created_at'] ?? '') ?>
                    </td>
                    <td class="text-end">
                      <a href="<?= BASE_URL . 'user-edit&id=' . $user['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a
                        href="<?= BASE_URL . 'user-delete&id=' . $user['id'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">
                    Chưa có tài khoản nào trong hệ thống.
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
    'title' => $title ?? 'Quản lý người dùng',
    'pageTitle' => 'Quản lý người dùng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Người dùng', 'url' => BASE_URL . 'users', 'active' => true],
    ],
]);
?>


