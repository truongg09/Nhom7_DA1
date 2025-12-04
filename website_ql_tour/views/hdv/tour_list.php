<?php 
// Biến $tours đã được truyền vào từ TourGuideController::viewAssignedTours()
startSession();
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Các Tour bạn đang phụ trách</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Tên Tour</th>
                    <th>Ngày Bắt đầu</th>
                    <th>Ngày Kết thúc</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tours)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-info-circle me-2"></i>Không có tour nào được phân công cho bạn.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $count = 1; foreach ($tours as $tour): ?>
                    <tr>
                        <td><?= $count++ ?>.</td>
                        <td><strong><?= htmlspecialchars($tour['name'] ?? 'N/A') ?></strong></td>
                        <td><?= isset($tour['start_date']) ? date('d/m/Y', strtotime($tour['start_date'])) : 'N/A' ?></td>
                        <td><?= isset($tour['end_date']) ? date('d/m/Y', strtotime($tour['end_date'])) : 'N/A' ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                    $status = $tour['status'] ?? '';
                                    if ($status == 'Hoàn thành' || $status == 'hoàn thành') echo 'bg-success';
                                    elseif ($status == 'Đang diễn ra' || $status == 'đang diễn ra') echo 'bg-warning';
                                    else echo 'bg-info';
                                ?>
                            ">
                                <?= htmlspecialchars($status ?: 'Chưa xác định') ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>?act=hdv-customers&tour_id=<?= $tour['id'] ?>" class="btn btn-sm btn-primary" title="Xem danh sách khách hàng">
                                    <i class="bi bi-people"></i> Khách hàng
                                </a>
                                <a href="<?= BASE_URL ?>?act=hdv-diary&tour_id=<?= $tour['id'] ?>" class="btn btn-sm btn-info" title="Cập nhật nhật ký tour">
                                    <i class="bi bi-journal-text"></i> Nhật ký
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>