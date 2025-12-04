<?php 
// Biến $tour, $content, $special_request đã được truyền vào từ TourGuideController::handleTourDiary()
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

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-journal-text me-2"></i>
            Cập nhật thông tin Tour 
            (Ngày: <?= isset($tour['start_date']) ? date('d/m/Y', strtotime($tour['start_date'])) : 'N/A' ?>)
        </h3>
    </div>
    
    <form action="<?= BASE_URL ?>?act=hdv-diary-save" method="POST">
        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?? '' ?>">
        
        <div class="card-body">
            <div class="mb-3">
                <label for="content" class="form-label">
                    <i class="bi bi-journal-text me-1"></i>
                    <strong>Nội dung Nhật ký Tour (Diễn biến trong ngày):</strong>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    class="form-control" 
                    rows="8" 
                    placeholder="Nhập diễn biến, sự kiện, phản hồi của khách hàng, các hoạt động trong ngày..."
                ><?= htmlspecialchars($content ?? '') ?></textarea>
                <small class="form-text text-muted">Ghi chép chi tiết về các hoạt động, sự kiện và phản hồi của khách hàng trong tour.</small>
            </div>
            
            <div class="mb-3">
                <label for="special_request" class="form-label">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Yêu cầu Đặc biệt gửi về Quản lý:</strong>
                </label>
                <textarea 
                    id="special_request" 
                    name="special_request" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Ghi chú về sự cố, yêu cầu cần xử lý gấp, vấn đề phát sinh..."
                ><?= htmlspecialchars($special_request ?? '') ?></textarea>
                <small class="form-text text-muted">Nhập các yêu cầu đặc biệt, sự cố hoặc vấn đề cần quản lý xử lý.</small>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> 
                Lưu Nhật ký & Yêu cầu
            </button>
            <a href="<?= BASE_URL ?>?act=hdv-tours" class="btn btn-secondary float-end">
                <i class="bi bi-arrow-left me-1"></i>
                Quay lại
            </a>
        </div>
    </form>
</div>