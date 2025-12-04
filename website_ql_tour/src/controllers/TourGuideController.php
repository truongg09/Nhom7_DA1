<?php

class TourGuideController {
    
    private $tourModel;
    private $checkinModel;
    private $tourDiaryModel;

    public function __construct() {
        // Khởi tạo các Model để tương tác với DB (Cần đảm bảo các file Model đã được require/autoload)
        $this->tourModel = new TourModel();
        $this->checkinModel = new CheckinModel();
        $this->tourDiaryModel = new TourDiaryModel();
    }
    
    private function checkAuth() {
        requireLogin();
        requireGuideOrAdmin();
    }

    /**
     * Chức năng: Xem danh sách tour được phân công (Use Case 2)
     * Route: GET ?act=hdv-tours
     */
    public function viewAssignedTours() {
        $this->checkAuth();
        $hdv_id = $_SESSION['user_id'];
        
        // 1. Gọi Model để lấy dữ liệu
        $tours = $this->tourModel->getToursByTourGuide($hdv_id);

        // 2. Render view với layout
        ob_start();
        extract(['tours' => $tours]);
        include view_path('hdv.tour_list');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Danh sách Tour được phân công',
            'pageTitle' => 'Danh sách Tour được phân công',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Danh sách Tour được phân công', 'active' => true],
            ],
        ]);
    }

    /**
     * Chức năng: Xem danh sách khách hàng trong tour (Use Case 3)
     * Route: GET ?act=hdv-customers&tour_id={tour_id}
     */
    public function viewCustomerList() {
        $this->checkAuth();
        $hdv_id = $_SESSION['user_id'];
        $tour_id = $_GET['tour_id'] ?? null;
        
        if (!$tour_id) {
            $_SESSION['error'] = "Không tìm thấy tour.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // 1. Kiểm tra bảo mật
        if (!$this->tourModel->isTourAssignedToHDV($hdv_id, $tour_id)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập tour này.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // 2. Lấy thông tin tour và danh sách khách hàng
        $tour = $this->tourModel->getTourDetails($tour_id);
        if (!$tour) {
            $_SESSION['error'] = "Không tìm thấy thông tin tour.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        $customers = $this->tourModel->getCustomersInTour($tour_id);

        // 3. Render view với layout
        ob_start();
        extract(['tour' => $tour, 'customers' => $customers]);
        include view_path('hdv.customer_list');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Danh sách Khách hàng - ' . htmlspecialchars($tour['name']),
            'pageTitle' => 'Danh sách Khách hàng: ' . htmlspecialchars($tour['name']),
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Danh sách Tour', 'url' => BASE_URL . '?act=hdv-tours'],
                ['label' => 'Danh sách Khách hàng', 'active' => true],
            ],
        ]);
    }

    /**
     * Chức năng: Nhập check-in từng phần (Use Case 1)
     * Route: POST ?act=hdv-checkin
     */
    public function handleCheckinForm() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        $customer_id = $_POST['customer_id'] ?? null;
        $tour_id = $_POST['tour_id'] ?? null;
        $status = $_POST['status'] ?? 0;
        $hdv_id = $_SESSION['user_id'];
        
        // 1. Kiểm tra bảo mật và dữ liệu đầu vào
        if (!$customer_id || !$tour_id || !$this->tourModel->isTourAssignedToHDV($hdv_id, $tour_id)) {
            $_SESSION['error'] = "Dữ liệu không hợp lệ hoặc bạn không có quyền.";
            header("Location: " . BASE_URL . "?act=hdv-tours"); 
            exit();
        }
        
        // 2. Gọi Model để cập nhật DB
        $success = $this->checkinModel->updateCheckinStatus($tour_id, $customer_id, $status, $hdv_id);
        
        if ($success) {
            $_SESSION['message'] = "Cập nhật check-in thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật check-in thất bại.";
        }
        
        header("Location: " . BASE_URL . "?act=hdv-customers&tour_id={$tour_id}");
        exit();
    }

    /**
     * Chức năng: Cập nhật nhật ký tour (Use Case 5) và Yêu cầu đặc biệt (Use Case 4)
     * Route GET: ?act=hdv-diary&tour_id={tour_id} (Để hiển thị form)
     * Route POST: ?act=hdv-diary-save (Để xử lý dữ liệu)
     */
    public function handleTourDiary() {
        $this->checkAuth();
        $hdv_id = $_SESSION['user_id'];
        $tour_id = $_GET['tour_id'] ?? null;
        
        if (!$tour_id) {
            $_SESSION['error'] = "Không tìm thấy tour.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // Kiểm tra bảo mật
        if (!$this->tourModel->isTourAssignedToHDV($hdv_id, $tour_id)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập tour này.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // 1. Lấy thông tin tour để hiển thị trên form
        $tour = $this->tourModel->getTourDetails($tour_id);
        if (!$tour) {
            $_SESSION['error'] = "Không tìm thấy thông tin tour.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // 2. Lấy nhật ký hiện có (nếu có)
        $current_diary = $this->tourDiaryModel->getDiaryByTour($tour_id);
        $content = $current_diary['content'] ?? '';
        $special_request = $current_diary['special_request'] ?? '';
        
        // 3. Render view với layout
        ob_start();
        extract(['tour' => $tour, 'content' => $content, 'special_request' => $special_request]);
        include view_path('hdv.diary_form');
        $content_html = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Nhật ký Tour - ' . htmlspecialchars($tour['name']),
            'pageTitle' => 'Nhật ký Tour: ' . htmlspecialchars($tour['name']),
            'content' => $content_html,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Danh sách Tour', 'url' => BASE_URL . '?act=hdv-tours'],
                ['label' => 'Nhật ký Tour', 'active' => true],
            ],
        ]);
    }
    
    /**
     * Xử lý POST để lưu nhật ký tour
     * Route POST: ?act=hdv-diary-save
     */
    public function saveTourDiary() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        $hdv_id = $_SESSION['user_id'];
        $content = $_POST['content'] ?? '';
        $special_request = $_POST['special_request'] ?? '';
        $tour_id = $_POST['tour_id'] ?? null;
        
        if (!$tour_id) {
            $_SESSION['error'] = "Không tìm thấy tour.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        // Kiểm tra bảo mật
        if (!$this->tourModel->isTourAssignedToHDV($hdv_id, $tour_id)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập tour này.";
            header("Location: " . BASE_URL . "?act=hdv-tours");
            exit();
        }
        
        $data = [
            'tour_id' => $tour_id,
            'hdv_id' => $hdv_id,
            'content' => $content,
            'special_request' => $special_request,
        ];
        
        $success = $this->tourDiaryModel->saveDiary($data);
        
        if ($success) {
            $_SESSION['message'] = "Cập nhật nhật ký tour thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật nhật ký tour thất bại.";
        }
        
        header("Location: " . BASE_URL . "?act=hdv-diary&tour_id={$tour_id}");
        exit();
    }
}