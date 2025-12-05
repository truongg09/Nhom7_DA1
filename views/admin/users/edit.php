<?php
// Form cập nhật người dùng
ob_start();
?>

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Cập nhật người dùng</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= BASE_URL . 'user-update' ?>" method="post" autocomplete="off">
          <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Họ tên</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              value="<?= htmlspecialchars($user['name'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              id="email"
              name="email"
              value="<?= htmlspecialchars($user['email'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">
              Mật khẩu mới (để trống nếu không đổi)
            </label>
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
            >
          </div>

          <div class="mb-3">
            <label for="role" class="form-label">Vai trò</label>
            <select class="form-select" id="role" name="role">
              <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="huong_dan_vien" <?= ($user['role'] ?? '') === 'huong_dan_vien' ? 'selected' : '' ?>>Hướng dẫn viên</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Trạng thái</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_active" value="1"
                <?= (int)($user['status'] ?? 1) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_active">Hoạt động</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0"
                <?= (int)($user['status'] ?? 1) === 0 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_inactive">Khóa</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL . 'users' ?>" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Cập nhật
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Cập nhật người dùng',
    'pageTitle' => 'Cập nhật người dùng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Người dùng', 'url' => BASE_URL . 'users', 'active' => false],
        ['label' => 'Cập nhật', 'url' => BASE_URL . 'user-edit&id=' . $user['id'], 'active' => true],
    ],
]);
?>



