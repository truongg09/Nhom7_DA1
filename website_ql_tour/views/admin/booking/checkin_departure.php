<?php
// View khởi hành - Check-in
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-airplane me-2"></i>
                    Khởi hành - Check-in - Booking #<?= $booking['id'] ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Thông tin booking -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Thông tin booking
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tour:</strong> <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></p>
                                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></p>
                                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($booking['customer_phone'] ?? 'N/A') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($booking['customer_email'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Số lượng khách:</strong> <?= htmlspecialchars($booking['number_of_guests'] ?? 'N/A') ?></p>
                                <p><strong>Ngày khởi hành:</strong> 
                                    <span class="badge bg-info fs-6">
                                        <?= $booking['start_date'] ? date('d/m/Y', strtotime($booking['start_date'])) : 'N/A' ?>
                                    </span>
                                </p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-<?= $booking['status'] === 'Departed' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars($booking['status']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($booking['status'] !== 'Departed'): ?>
                    <!-- Form check-in -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Xác nhận khởi hành
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>?act=booking-checkin&id=<?= $booking['id'] ?>">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Lưu ý:</strong> Sau khi xác nhận khởi hành, trạng thái booking sẽ được cập nhật thành "Đã khởi hành".
                                </div>
                                
                                <div class="mb-3">
                                    <label for="checkin_time" class="form-label">Thời gian check-in</label>
                                    <input type="datetime-local" class="form-control" id="checkin_time" name="checkin_time" 
                                           value="<?= date('Y-m-d\TH:i') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="checkin_notes" class="form-label">Ghi chú (nếu có)</label>
                                    <textarea class="form-control" id="checkin_notes" name="checkin_notes" rows="3"></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= BASE_URL ?>?act=booking-list" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-airplane me-2"></i>Xác nhận khởi hành
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Đã khởi hành!</strong> Booking này đã được xác nhận khởi hành.
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="<?= BASE_URL ?>?act=booking-list" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
