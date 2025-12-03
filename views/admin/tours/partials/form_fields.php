<?php
$value = static function ($key, $default = '') use ($old, $tour) {
    if (isset($old[$key])) {
        return $old[$key];
    }

    if (isset($tour[$key])) {
        return $tour[$key];
    }

    return $default;
};
?>

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label">Tên tour <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($value('name')) ?>" required />
  </div>
  <div class="col-md-6">
    <label class="form-label">Danh mục tour</label>
    <select name="category_id" class="form-select">
      <option value="">-- Chọn danh mục --</option>
      <option value="1" <?= (string) $value('category_id') === '1' ? 'selected' : '' ?>>Tour trong nước</option>
      <option value="2" <?= (string) $value('category_id') === '2' ? 'selected' : '' ?>>Tour quốc tế</option>
    </select>
  </div>
  <div class="col-12">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($value('description')) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Lịch trình</label>
    <textarea name="schedule" class="form-control" rows="4" placeholder="Ngày 1: ..."><?= htmlspecialchars($value('schedule')) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Ảnh (JSON/URL)</label>
    <textarea name="images" class="form-control" rows="3" placeholder='["img1.jpg","img2.jpg"]'><?= htmlspecialchars($value('images')) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Bảng giá chi tiết (JSON)</label>
    <textarea name="prices" class="form-control" rows="3" placeholder='{"adult":1000000,"child":500000}'><?= htmlspecialchars($value('prices')) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Chính sách</label>
    <textarea name="policies" class="form-control" rows="3"><?= htmlspecialchars($value('policies')) ?></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Nhà cung cấp</label>
    <textarea name="suppliers" class="form-control" rows="3"><?= htmlspecialchars($value('suppliers')) ?></textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">Giá tour (VNĐ) <span class="text-danger">*</span></label>
    <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($value('price')) ?>" required />
  </div>
  <div class="col-md-4">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="1" <?= (int) $value('status', 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
      <option value="0" <?= (int) $value('status', 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
    </select>
  </div>
</div>

