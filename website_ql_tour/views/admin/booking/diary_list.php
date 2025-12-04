<?php
// View nhật ký dịch vụ
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-journal-text me-2"></i>
                    Nhật ký dịch vụ - Booking #<?= $booking['id'] ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDiaryModal">
                        <i class="bi bi-plus-circle me-2"></i>Thêm nhật ký
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Thông tin booking -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Thông tin booking
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tour:</strong> <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></p>
                                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($booking['customer_name'] ?? 'Không có dữ liệu') ?>
</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ngày khởi hành:</strong> <?= $booking['start_date'] ? date('d/m/Y', strtotime($booking['start_date'])) : 'N/A' ?></p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-info"><?= htmlspecialchars($booking['status']) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danh sách nhật ký -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Ngày giờ</th>
                                <th>Loại dịch vụ</th>
                                <th>Mô tả</th>
                                <th>Ghi chú</th>
                                <th>Người ghi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($diaries)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-inbox me-2"></i>Chưa có nhật ký nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($diaries as $diary): ?>
                                    <tr>
                                        <td><?= isset($diary['created_at']) ? date('d/m/Y H:i', strtotime($diary['created_at'])) : 'N/A' ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?= htmlspecialchars($diary['service_type'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($diary['description'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($diary['notes'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($diary['created_by'] ?? 'N/A') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<!-- Modal thêm nhật ký -->
<div class="modal fade" id="addDiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Thêm nhật ký dịch vụ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>?act=booking-diary&id=<?= $booking['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="service_type" class="form-label">Loại dịch vụ</label>
                        <select class="form-select" id="service_type" name="service_type" required>
                            <option value="">-- Chọn loại dịch vụ --</option>
                            <option value="accommodation">Lưu trú</option>
                            <option value="transportation">Vận chuyển</option>
                            <option value="meal">Ăn uống</option>
                            <option value="activity">Hoạt động</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm nhật ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
