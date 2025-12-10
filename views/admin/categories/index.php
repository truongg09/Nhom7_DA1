<?php
ob_start();
?>

<?php if ($msg = getFlash('success')): ?>
  <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if ($msg = getFlash('error')): ?>
  <div class="alert alert-danger"><?= $msg ?></div>
<?php endif; ?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Danh mục Tour</h3>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <div class="position-relative" style="width: 200px;">
            <i class="bi bi-search position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm kiếm..." style="padding-left: 35px;" />
          </div>
          <a href="<?= BASE_URL ?>category-create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Thêm danh mục
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($categories)): ?>
          <p class="text-muted mb-0">Chưa có danh mục nào. Hãy thêm danh mục mới.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="dataTable">
              <thead class="table-light">
                <tr>
                  <th>STT</th>
                  <th>Tên danh mục</th>
                  <th>Mô tả</th>
                  <th class="text-center">Trạng thái</th>
                  <th class="text-center">Ngày tạo</th>
                  <th class="text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($categories as $category): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td class="text-truncate" style="max-width: 260px;">
                      <?= htmlspecialchars(mb_strimwidth($category['description'] ?? '', 0, 80, '...')) ?>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-<?= (int) ($category['status'] ?? 1) === 1 ? 'success' : 'secondary' ?>">
                        <?= (int) ($category['status'] ?? 1) === 1 ? 'Hoạt động' : 'Ẩn' ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?= htmlspecialchars($category['created_at'] ?? '') ?>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2 w-100">
                        <a href="<?= BASE_URL ?>category-show&id=<?= urlencode($category['id']) ?>" class="btn btn-info btn-sm" title="Xem chi tiết">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>category-edit&id=<?= urlencode($category['id']) ?>" class="btn btn-warning btn-sm" title="Sửa">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="<?= BASE_URL ?>category-delete" method="POST" onsubmit="return confirm('Xác nhận xóa danh mục?');" class="m-0">
                          <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>" />
                          <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!--end::Row-->

<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchInput');
  const table = document.getElementById('dataTable');
  
  if (searchInput && table) {
    searchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
      
      for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (text.toLowerCase().indexOf(searchTerm) > -1) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      }
    });
  }
});
</script>

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


