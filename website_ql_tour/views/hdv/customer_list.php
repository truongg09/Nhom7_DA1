<?php 
// Biến $tour và $customers đã được truyền vào từ TourGuideController::viewCustomerList()
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
        <h3 class="card-title">
            <i class="bi bi-calendar-event me-2"></i>
            Tour khởi hành ngày: <?= isset($tour['start_date']) ? date('d/m/Y', strtotime($tour['start_date'])) : 'N/A' ?>
        </h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Tên Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th style="width: 180px" class="text-center">Trạng thái Check-in</th>
                    <th style="width: 150px" class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-info-circle me-2"></i>Tour này chưa có khách hàng nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $count = 1; foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= $count++ ?>.</td>
                        <td><strong><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></strong></td>
                        <td><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></td>
                        <td class="text-center">
                            <?php if (isset($customer['checkin_status']) && $customer['checkin_status'] == 1): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Đã Check-in 
                                    <?php if (isset($customer['checkin_time'])): ?>
                                        (<?= date('H:i', strtotime($customer['checkin_time'])) ?>)
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Chưa Check-in
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <form method="POST" action="<?= BASE_URL ?>?act=hdv-checkin" style="display: inline-block;">
                                <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?? '' ?>">
                                <input type="hidden" name="tour_id" value="<?= $tour['id'] ?? '' ?>">
                                <input type="hidden" name="status" value="<?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 0 : 1 ?>">
                                
                                <button type="submit" 
                                    class="btn btn-sm <?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 'btn-warning' : 'btn-success' ?>"
                                    title="<?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 'Hủy Check-in' : 'Xác nhận Check-in' ?>"
                                    onclick="return confirm('<?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 'Bạn có chắc muốn hủy check-in?' : 'Xác nhận check-in cho khách hàng này?' ?>');">
                                    <i class="bi <?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 'bi-arrow-counterclockwise' : 'bi-check-circle' ?>"></i> 
                                    <?= (isset($customer['checkin_status']) && $customer['checkin_status'] == 1) ? 'Hủy' : 'Check-in' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>