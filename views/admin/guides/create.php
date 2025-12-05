<?php
// Form thêm Hướng dẫn viên
ob_start();

$old = $old ?? [
    'name' => '',
    'email' => '',
    'status' => 1,
    'phone' => '',
    'birthdate' => '',
    'avatar' => '',
    'certificate' => '',
    'languages' => '',
    'experience' => '',
    'history' => '',
    'rating' => '',
    'health_status' => '',
    'group_type' => '',
    'speciality' => '',
];
?>

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Thêm Hướng dẫn viên</h3>
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

        <form action="<?= BASE_URL . 'guide-store' ?>" method="post" enctype="multipart/form-data" autocomplete="off">
          <div class="mb-3">
            <label for="name" class="form-label">Họ tên HDV</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              value="<?= htmlspecialchars($old['name'] ?? '') ?>"
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
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Trạng thái</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_active" value="1"
                <?= (int)($old['status'] ?? 1) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_active">Hoạt động</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0"
                <?= (int)($old['status'] ?? 1) === 0 ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_inactive">Khóa</label>
            </div>
          </div>

          <hr>
          <h5 class="mb-3">Hồ sơ chi tiết</h5>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Điện thoại</label>
              <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
            <label for="birthdate" class="form-label">Ngày sinh</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($old['birthdate'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="avatar" class="form-label">Ảnh đại diện</label>
            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
            <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF, WEBP (tối đa 5MB)</small>
          </div>

          <div class="mb-3">
          <label for="certificate" class="form-label">Chứng chỉ chuyên môn</label>
          <textarea class="form-control" id="certificate" name="certificate" rows="2"><?= htmlspecialchars($old['certificate'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="languages" class="form-label">Ngôn ngữ sử dụng</label>
            <textarea class="form-control" id="languages" name="languages" rows="2"><?= htmlspecialchars($old['languages'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
          <label for="experience" class="form-label">Kinh nghiệm (năm)</label>
          <input type="text" class="form-control" id="experience" name="experience" value="<?= htmlspecialchars($old['experience'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label for="tour_history" class="form-label">Lịch sử dẫn tour</label>
          <textarea class="form-control" id="tour_history" name="history" rows="3"><?= htmlspecialchars($old['history'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
          <label for="rating" class="form-label">Đánh giá năng lực / rating</label>
          <textarea class="form-control" id="rating" name="rating" rows="2"><?= htmlspecialchars($old['rating'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="health_status" class="form-label">Tình trạng sức khoẻ</label>
            <textarea class="form-control" id="health_status" name="health_status" rows="2"><?= htmlspecialchars($old['health_status'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="group_type" class="form-label">Phân loại HDV (nội địa/quốc tế/tuyến/khách đoàn...)</label>
            <input type="text" class="form-control" id="group_type" name="group_type" value="<?= htmlspecialchars($old['group_type'] ?? '') ?>">
          </div>

          <div class="mb-3">
          <label for="speciality" class="form-label">Chuyên tuyến / thế mạnh</label>
          <textarea class="form-control" id="speciality" name="speciality" rows="2"><?= htmlspecialchars($old['speciality'] ?? '') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL . 'guides' ?>" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Lưu HDV
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
    'title' => $title ?? 'Thêm Hướng dẫn viên',
    'pageTitle' => 'Thêm Hướng dẫn viên',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => false],
        ['label' => 'Hướng dẫn viên', 'url' => BASE_URL . 'guides', 'active' => false],
        ['label' => 'Thêm mới', 'url' => BASE_URL . 'guide-create', 'active' => true],
    ],
]);
?>


