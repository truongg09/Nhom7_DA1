<?php
$value = static function ($key, $default = '') use ($old, $category) {
    if (isset($old[$key])) {
        return $old[$key];
    }

    if (isset($category[$key])) {
        return $category[$key];
    }

    return $default;
};
?>

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($value('name')) ?>" required />
  </div>
  <div class="col-md-6">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="1" <?= (int) $value('status', 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
      <option value="0" <?= (int) $value('status', 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
    </select>
  </div>
  <div class="col-12">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($value('description')) ?></textarea>
  </div>
</div>


