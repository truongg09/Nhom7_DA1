<?php
ob_start();
?>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">Danh mục Tour</h3>
    <div class="ms-auto">
      <a href="<?= BASE_URL ?>category-create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Thêm danh mục
      </a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
      <p class="text-muted mb-0">Chưa có danh mục nào. Hãy thêm danh mục mới.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th class="text-center">ID</th>
              <th>Tên danh mục</th>
              <th>Mô tả</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($category['id']) ?></td>
                <td><?= htmlspecialchars($category['name']) ?></td>
                <td class="text-truncate" style="max-width: 260px;">
                  <?= htmlspecialchars(mb_strimwidth($category['description'] ?? '', 0, 80, '...')) ?>
                </td>
                <td>
                  <span class="badge bg-<?= (int) ($category['status'] ?? 1) === 1 ? 'success' : 'secondary' ?>">
                    <?= (int) ($category['status'] ?? 1) === 1 ? 'Hoạt động' : 'Ẩn' ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($category['created_at'] ?? '') ?></td>
                <td class="d-flex justify-content-center gap-2">
                  <a href="<?= BASE_URL ?>category-show&id=<?= urlencode($category['id']) ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= BASE_URL ?>category-edit&id=<?= urlencode($category['id']) ?>" class="btn btn-sm btn-warning" title="Sửa">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <form action="<?= BASE_URL ?>category-delete" method="POST" onsubmit="return confirm('Xác nhận xóa danh mục?');">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>" />
                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Danh mục tour',
    'pageTitle' => $pageTitle ?? 'Quản lý danh mục tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý danh mục tour', 'url' => BASE_URL . 'categories', 'active' => true],
    ],
]);


