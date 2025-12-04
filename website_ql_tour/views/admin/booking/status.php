<?php
// View danh sách booking và tình trạng
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-list-ul me-2"></i>
                    Danh sách booking
                </h3>
                <div class="card-tools">
                    <a href="<?= BASE_URL ?>?act=booking-create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-2"></i>Tạo booking mới
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tour</th>
                                <th>Khách hàng</th>
                                <th>Số lượng</th>
                                <th>Ngày khởi hành</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="bi bi-inbox me-2"></i>Chưa có booking nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?= $booking['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($booking['customer_name'] ?? 'Chưa cập nhật') ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($booking['customer_phone'] ?? '—') ?>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars((string)($booking['number_of_guests'] ?? '—')) ?></td>
                                        <td><?= $booking['start_date'] ? date('d/m/Y', strtotime($booking['start_date'])) : 'N/A' ?></td>
                                        <td>
                                            <?php
                                            $status = $booking['status'] ?? 'Pending';
                                            $statusClass = match($status) {
                                                'Pending' => 'warning',
                                                'Confirmed' => 'info',
                                                'Departed' => 'primary',
                                                'Completed' => 'success',
                                                'Cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= htmlspecialchars($status) ?>
                                            </span>
                                        </td>
                                        <td><?= $booking['created_at'] ? date('d/m/Y H:i', strtotime($booking['created_at'])) : 'N/A' ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= BASE_URL ?>?act=booking-allocate&id=<?= $booking['id'] ?>" 
                                                   class="btn btn-info" title="Phân bổ nhân sự">
                                                    <i class="bi bi-people"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>?act=booking-diary&id=<?= $booking['id'] ?>" 
                                                   class="btn btn-warning" title="Nhật ký dịch vụ">
                                                    <i class="bi bi-journal-text"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>?act=booking-checkin&id=<?= $booking['id'] ?>" 
                                                   class="btn btn-primary" title="Khởi hành">
                                                    <i class="bi bi-airplane"></i>
                                                </a>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-secondary dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Cập nhật trạng thái">
                                                        <i class="bi bi-gear"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>?act=booking-update-status&id=<?= $booking['id'] ?>&status=Confirmed">Xác nhận</a></li>
                                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>?act=booking-update-status&id=<?= $booking['id'] ?>&status=Departed">Đã khởi hành</a></li>
                                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>?act=booking-update-status&id=<?= $booking['id'] ?>&status=Completed">Hoàn thành</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>?act=booking-update-status&id=<?= $booking['id'] ?>&status=Cancelled">Hủy</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
