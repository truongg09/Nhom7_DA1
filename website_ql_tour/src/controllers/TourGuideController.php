<?php

class TourGuideController
{

    private $tourModel;
    private $checkinModel;
    private $tourDiaryModel;

    public function __construct()
    {
        // Khởi tạo các Model để tương tác với DB (Cần đảm bảo các file Model đã được require/autoload)
        $this->tourModel = new TourModel();
        $this->checkinModel = new CheckinModel();
        $this->tourDiaryModel = new TourDiaryModel();
    }

    private function checkAuth()
    {
        requireLogin();
        requireGuideOrAdmin();
    }

    /**
     * Chức năng: Xem danh sách tour được phân công (Use Case 2)
     * Route: GET ?act=hdv-tours
     */
    // F:\laragon\www\DA1\website_ql_tour\src\controllers\TourGuideController.php

    // ... (Các phần khác của class TourGuideController)

    /**
     * Chức năng: Xem danh sách tour được phân công (Use Case 2)
     * Route: GET ?act=hdv-tours
     */
    public function viewAssignedTours()
    {
        $this->checkAuth();
        $hdv_id = $_SESSION['user_id'];

        // THÊM LẠI DÒNG NÀY (TẠM THỜI)


        $tours = $this->tourModel->getToursByTourGuide($hdv_id);

        // ... (Các phần còn lại của phương thức)
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
    public function viewCustomerList()
    {
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


        // Trong TourGuideController.php, phương thức viewCustomerList()

        // ...
        // Trong TourGuideController.php, phương thức viewCustomerList()

        // ...

        // 2. Lấy thông tin tour và danh sách khách hàng
        $tour = $this->tourModel->getTourDetails($tour_id);

        if (!$tour) {
            // ... (Giữ nguyên phần kiểm tra lỗi)
        }

        // === PHẦN XỬ LÝ NGÀY KHỞI HÀNH (Cần phải CHẮC CHẮN ĐÃ ĐƯỢC LƯU) ===
        if (isset($tour['schedule']) && $tour['schedule']) {
            $scheduleData = json_decode($tour['schedule'], true);

            // Đảm bảo cấu trúc data chính xác
            if ($scheduleData && isset($scheduleData['days'][0]['date'])) {
                $tour['start_date'] = $scheduleData['days'][0]['date'];
            }
        }
        // =================================================================

        $customers = $this->tourModel->getCustomersInTour($tour_id);

        // 3. Render view với layout
// ...

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
    public function handleCheckinForm()
    {


        $customer_id = $_POST['customer_id'] ?? null;
        $tour_id = $_POST['tour_id'] ?? null;
        $status = $_POST['status'] ?? 0;
        $hdv_id = $_SESSION['user_id'];

        $success = $this->tourModel->updateCheckinStatus((int) $customer_id, (int) $tour_id, (int) $status);

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
    public function handleTourDiary()
    {
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
        if (isset($tour['schedule']) && $tour['schedule']) {
            // BẢO VỆ JSON (Do có tiếng Việt)
            $clean_schedule = mb_convert_encoding($tour['schedule'], 'UTF-8', 'UTF-8');
            $scheduleData = json_decode($clean_schedule, true);

            // Trích xuất ngày khởi hành đầu tiên
            if (json_last_error() === JSON_ERROR_NONE && $scheduleData && isset($scheduleData['days'][0]['date'])) {
                // Gán ngày khởi hành chính xác vào key 'start_date'
                $tour['start_date'] = $scheduleData['days'][0]['date'];
            }
        }

        // 2. Lấy nhật ký hiện có (nếu có)
        $current_diary = $this->tourDiaryModel->getDiaryByTour($tour_id);
        $content = $current_diary['diary'] ?? '';
        $special_request = $current_diary['notes'] ?? '';

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
    public function saveTourDiary()
    {
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

        try {
            $success = $this->tourDiaryModel->saveDiary($data);

            if ($success) {
                $_SESSION['message'] = "Cập nhật nhật ký tour thành công.";
            } else {
                $_SESSION['error'] = "Cập nhật nhật ký tour thất bại. (Model trả về false).";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Cập nhật thất bại: Lỗi DB - " . $e->getMessage();
        }

        header("Location: " . BASE_URL . "?act=hdv-diary&tour_id={$tour_id}");
        exit();
    }

}