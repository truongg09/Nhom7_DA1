<?php
// View tạo booking mới
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Tạo booking mới
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

                <form method="POST" action="<?= BASE_URL ?>?act=booking-create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tour_id" class="form-label">Chọn tour <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="tour_id" name="tour_id" required>
                                    <option value="">-- Chọn tour --</option>
                                    <?php foreach ($tours as $tour): ?>
                                        <option value="<?= $tour['id'] ?>" <?= (isset($_POST['tour_id']) && $_POST['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tour['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Ngày khởi hành <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="<?= $_POST['start_date'] ?? '' ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Tên khách hàng <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                    value="<?= $_POST['customer_name'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Số điện thoại <span
                                        class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone"
                                    value="<?= $_POST['customer_phone'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email"
                                    value="<?= $_POST['customer_email'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="number_of_guests" class="form-label">Số lượng khách <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="number_of_guests" name="number_of_guests"
                                    value="<?= $_POST['number_of_guests'] ?? 1 ?>" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes"
                            rows="3"><?= $_POST['notes'] ?? '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>?act=booking-list" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Tạo booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>