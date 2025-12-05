<?php
// Form cập nhật Hướng dẫn viên
ob_start();

$profile = $profile ?? [];
?>

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Cập nhật Hướng dẫn viên</h3>
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

        <form action="<?= BASE_URL . 'guide-update' ?>" method="post" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="id" value="<?= htmlspecialchars($guide['id']) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Họ tên HDV</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              value="<?= htmlspecialchars($guide['name'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email đăng nhập</label>
            <input
              type="email"
              class="form-control"
              id="email"
              name="email"
              value="<?= htmlspecialchars($guide['email'] ?? '') ?>"
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
            <label class="form-label d-block">Trạng thái</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_active" value="1"
                <?= (int)($guide['status'] ?? 1) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_active">Hoạt động</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0"
                <?= (int)($guide['status'] ?? 1) === 0 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_inactive">Khóa</label>
            </div>
          </div>

          <hr>
          <h5 class="mb-3">Hồ sơ chi tiết</h5>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Điện thoại</label>
              <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
            <label for="birthdate" class="form-label">Ngày sinh</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($profile['birthdate'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="avatar" class="form-label">Ảnh đại diện</label>
            <?php if (!empty($profile['avatar'])): ?>
              <div class="mb-2">
                <img src="<?= htmlspecialchars($profile['avatar']) ?>" alt="Avatar hiện tại" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                <p class="text-muted small mb-0">Ảnh hiện tại</p>
              </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
            <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF, WEBP (tối đa 5MB). Để trống nếu không đổi ảnh.</small>
          </div>

          <div class="mb-3">
          <label for="certificate" class="form-label">Chứng chỉ chuyên môn</label>
          <textarea class="form-control" id="certificate" name="certificate" rows="2"><?= htmlspecialchars($profile['certificate'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="languages" class="form-label">Ngôn ngữ sử dụng</label>
            <textarea class="form-control" id="languages" name="languages" rows="2"><?= htmlspecialchars($profile['languages'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
          <label for="experience" class="form-label">Kinh nghiệm (năm)</label>
          <input type="text" class="form-control" id="experience" name="experience" value="<?= htmlspecialchars($profile['experience'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label for="tour_history" class="form-label">Lịch sử dẫn tour</label>
          <textarea class="form-control" id="tour_history" name="history" rows="3"><?= htmlspecialchars($profile['history'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
          <label for="rating" class="form-label">Đánh giá năng lực / rating</label>
          <textarea class="form-control" id="rating" name="rating" rows="2"><?= htmlspecialchars($profile['rating'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="health_status" class="form-label">Tình trạng sức khoẻ</label>
            <textarea class="form-control" id="health_status" name="health_status" rows="2"><?= htmlspecialchars($profile['health_status'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="group_type" class="form-label">Phân loại HDV (nội địa/quốc tế/tuyến/khách đoàn...)</label>
            <input type="text" class="form-control" id="group_type" name="group_type" value="<?= htmlspecialchars($profile['group_type'] ?? '') ?>">
          </div>

          <div class="mb-3">
          <label for="speciality" class="form-label">Chuyên tuyến / thế mạnh</label>
          <textarea class="form-control" id="speciality" name="speciality" rows="2"><?= htmlspecialchars($profile['speciality'] ?? '') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL . 'guides' ?>" class="btn btn-outline-secondary">
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
    'title' => $title ?? 'Cập nhật Hướng dẫn viên',
    'pageTitle' => 'Cập nhật Hướng dẫn viên',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Hướng dẫn viên', 'url' => BASE_URL . 'guides', 'active' => false],
        ['label' => 'Cập nhật', 'url' => BASE_URL . 'guide-edit&id=' . $guide['id'], 'active' => true],
    ],
]);
?>


