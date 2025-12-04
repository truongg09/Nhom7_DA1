<?php
// View phân bổ nhân sự
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-people me-2"></i>
                    Phân bổ nhân sự cho booking #<?= $booking['id'] ?>
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
                                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($booking['customer_name']) ?></p>
                                <p><strong>Số lượng khách:</strong> <?= $booking['number_of_guests'] ?></p>
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
                
                <!-- Thông tin phân bổ hiện tại (nếu có) -->
                <?php if (isset($current_allocation) && $current_allocation): ?>
                    <div class="alert alert-info mb-4">
                        <h6><i class="bi bi-info-circle me-2"></i>Phân bổ hiện tại:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($current_allocation['guide_name']): ?>
                                    <p class="mb-1">
                                        <strong>Hướng dẫn viên:</strong> 
                                        <?= htmlspecialchars($current_allocation['guide_name']) ?>
                                        <?= $current_allocation['guide_phone'] ? ' - ' . htmlspecialchars($current_allocation['guide_phone']) : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if ($current_allocation['driver_name']): ?>
                                    <p class="mb-1">
                                        <strong>Tài xế:</strong> 
                                        <?= htmlspecialchars($current_allocation['driver_name']) ?>
                                        <?= $current_allocation['driver_phone'] ? ' - ' . htmlspecialchars($current_allocation['driver_phone']) : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Form phân bổ nhân sự -->
                <form method="POST" action="<?= BASE_URL ?>?act=booking-allocate&id=<?= $booking['id'] ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-badge me-2"></i>
                                        Hướng dẫn viên
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="guide_id" class="form-label">Chọn hướng dẫn viên</label>
                                        <select class="form-select" id="guide_id" name="guide_id">
                                            <option value="">-- Chọn hướng dẫn viên --</option>
                                            <?php if (!empty($personnel_list)): ?>
                                                <?php foreach ($personnel_list as $person): ?>
                                                    <?php if (isset($person['role']) && $person['role'] === 'huong_dan_vien'): ?>
                                                        <?php 
                                                        $selected = false;
                                                        if (isset($_POST['guide_id']) && $_POST['guide_id'] == $person['id']) {
                                                            $selected = true;
                                                        } elseif (isset($current_allocation) && $current_allocation['guide_id'] == $person['id']) {
                                                            $selected = true;
                                                        }
                                                        ?>
                                                        <option value="<?= $person['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($person['name']) ?> 
                                                            <?= isset($person['phone']) ? ' - ' . htmlspecialchars($person['phone']) : '' ?>
                                                            <?= isset($person['email']) ? ' (' . htmlspecialchars($person['email']) . ')' : '' ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>Chưa có dữ liệu nhân sự</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-car-front me-2"></i>
                                        Tài xế
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="driver_id" class="form-label">Chọn tài xế</label>
                                        <select class="form-select" id="driver_id" name="driver_id">
                                            <option value="">-- Chọn tài xế --</option>
                                            <?php if (!empty($personnel_list)): ?>
                                                <?php foreach ($personnel_list as $person): ?>
                                                    <?php if (isset($person['role']) && ($person['role'] === 'driver' || $person['role'] === 'tai_xe')): ?>
                                                        <?php 
                                                        $selected = false;
                                                        if (isset($_POST['driver_id']) && $_POST['driver_id'] == $person['id']) {
                                                            $selected = true;
                                                        } elseif (isset($current_allocation) && $current_allocation['driver_id'] == $person['id']) {
                                                            $selected = true;
                                                        }
                                                        ?>
                                                        <option value="<?= $person['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($person['name']) ?> 
                                                            <?= isset($person['phone']) ? ' - ' . htmlspecialchars($person['phone']) : '' ?>
                                                            <?= isset($person['email']) ? ' (' . htmlspecialchars($person['email']) . ')' : '' ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>Chưa có dữ liệu nhân sự</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>?act=booking-list" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu phân bổ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
