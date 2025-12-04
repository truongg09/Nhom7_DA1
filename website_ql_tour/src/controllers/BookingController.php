<?php
// Tải các Models cần thiết
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/PersonnelModel.php';
require_once __DIR__ . '/../models/TourDiaryModel.php';
require_once __DIR__ . '/../models/CheckinModel.php';
require_once __DIR__ . '/../models/TourModel.php';

class BookingController {

    private $bookingModel;
    private $personnelModel;
    private $tourDiaryModel;
    private $checkinModel;
    private $tourModel;

    public function __construct() {
        // Khởi tạo các Model
        $this->bookingModel = new Booking();
        $this->personnelModel = new PersonnelModel();
        $this->tourDiaryModel = new TourDiaryModel();
        $this->checkinModel = new CheckinModel();
        $this->tourModel = new TourModel();
    }

    // --- 1. Tạo booking ---
    public function create() {
        requireLogin();
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Xử lý dữ liệu POST để tạo booking
            $data = [
                'tour_id' => $_POST['tour_id'] ?? null,
                'customer_name' => $_POST['customer_name'] ?? '',
                'customer_phone' => $_POST['customer_phone'] ?? '',
                'customer_email' => $_POST['customer_email'] ?? '',
                'number_of_guests' => $_POST['number_of_guests'] ?? 1,
                'start_date' => $_POST['start_date'] ?? null,
                'status' => 'Pending',
                'notes' => $_POST['notes'] ?? '',
            ];

            $result = $this->bookingModel->createBooking($data);

            if ($result) {
                $success = "Tạo booking thành công!";
                // Có thể chuyển hướng hoặc hiển thị thông báo
            } else {
                $error = "Tạo booking thất bại. Vui lòng thử lại.";
            }
        }

        // Lấy danh sách tours để chọn
        $tours = $this->tourModel->getAvailableTours();
        
        // Render view
        ob_start();
        extract(['error' => $error ?? null, 'success' => $success ?? null, 'tours' => $tours]);
        include view_path('admin.booking.create');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Tạo booking mới',
            'pageTitle' => 'Tạo booking',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Quản lý booking', 'url' => BASE_URL . '?act=booking-list'],
                ['label' => 'Tạo booking', 'active' => true],
            ],
        ]);
    }

    // --- 2. Danh sách booking và Tình trạng booking ---
    public function index() {
        requireLogin();
        
        // Lấy danh sách booking từ Model
        $bookings = $this->bookingModel->getAllBookings();
        
        // Render view
        ob_start();
        extract(['bookings' => $bookings]);
        include view_path('admin.booking.status');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Danh sách booking',
            'pageTitle' => 'Quản lý booking',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Danh sách booking', 'active' => true],
            ],
        ]);
    }

    // --- 3. Phân bổ nhân sự ---
    public function allocatePersonnel() {
        requireLogin();
        
        $booking_id = $_GET['id'] ?? null;
        if (!$booking_id) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy thông tin phân bổ từ form
            $personnel_data = [
                'booking_id' => $booking_id,
                'guide_id' => !empty($_POST['guide_id']) ? $_POST['guide_id'] : null,
                'driver_id' => !empty($_POST['driver_id']) ? $_POST['driver_id'] : null,
            ];
            
            // Cập nhật phân bổ vào DB thông qua Model
            $result = $this->personnelModel->updateAllocation($personnel_data);
            
            if ($result) {
                $success = "Phân bổ nhân sự thành công!";
            } else {
                $error = "Phân bổ nhân sự thất bại. Vui lòng thử lại.";
            }
        }
        
        // Lấy thông tin booking và danh sách nhân sự để hiển thị form
        $booking = $this->bookingModel->getBookingById($booking_id);
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        // Lấy danh sách nhân sự
        $personnel_list = $this->personnelModel->getAvailablePersonnel();
        
        // Lấy thông tin phân bổ hiện tại (nếu có)
        $current_allocation = $this->personnelModel->getAllocationByBookingId($booking_id);
        
        // Render view
        ob_start();
        extract([
            'booking' => $booking,
            'personnel_list' => $personnel_list,
            'current_allocation' => $current_allocation,
            'error' => $error ?? null,
            'success' => $success ?? null
        ]);
        include view_path('admin.booking.personnel_allocation');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Phân bổ nhân sự',
            'pageTitle' => 'Phân bổ nhân sự',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Quản lý booking', 'url' => BASE_URL . '?act=booking-list'],
                ['label' => 'Phân bổ nhân sự', 'active' => true],
            ],
        ]);
    }

    // --- 4. Nhật ký, dịch vụ ---
    public function tourDiary() {
        requireLogin();
        
        $booking_id = $_GET['id'] ?? null;
        if (!$booking_id) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        // Lấy danh sách nhật ký/dịch vụ của tour cụ thể
        // Giả sử TourDiaryModel có method getDiariesByBookingId
        // $diaries = $this->tourDiaryModel->getDiariesByBookingId($booking_id);
        $diaries = []; // Tạm thời
        
        $booking = $this->bookingModel->getBookingById($booking_id);
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        // Render view
        ob_start();
        extract(['booking' => $booking, 'diaries' => $diaries]);
        include view_path('admin.booking.diary_list');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Nhật ký dịch vụ',
            'pageTitle' => 'Nhật ký dịch vụ',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Quản lý booking', 'url' => BASE_URL . '?act=booking-list'],
                ['label' => 'Nhật ký dịch vụ', 'active' => true],
            ],
        ]);
    }

    // --- 5. Khởi hành (Check-in) ---
    public function departureCheckin() {
        requireLogin();
        
        $booking_id = $_GET['id'] ?? null;
        if (!$booking_id) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Xử lý POST để check-in
            $checkin_data = [
                'booking_id' => $booking_id,
                'checkin_time' => date('Y-m-d H:i:s'),
            ];
            
            // Giả sử CheckinModel có method recordCheckin
            // $result = $this->checkinModel->recordCheckin($checkin_data);
            
            // Cập nhật trạng thái booking thành "Đã khởi hành"
            $this->bookingModel->updateStatus($booking_id, 'Departed');
            
            $success = "Check-in khởi hành thành công!";
        }
        
        $booking = $this->bookingModel->getBookingById($booking_id);
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=booking-list');
            exit();
        }
        
        // Render view
        ob_start();
        extract([
            'booking' => $booking,
            'error' => $error ?? null,
            'success' => $success ?? null
        ]);
        include view_path('admin.booking.checkin_departure');
        $content = ob_get_clean();
        
        view('layouts.AdminLayout', [
            'title' => 'Khởi hành - Check-in',
            'pageTitle' => 'Khởi hành',
            'content' => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=home'],
                ['label' => 'Quản lý booking', 'url' => BASE_URL . '?act=booking-list'],
                ['label' => 'Khởi hành', 'active' => true],
            ],
        ]);
    }
    
    // --- Cập nhật trạng thái booking ---
    public function updateStatus() {
        requireLogin();
        
        $booking_id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if ($booking_id && $status) {
            $this->bookingModel->updateStatus(1,1);
        }
        
        header('Location: ' . BASE_URL . '?act=booking-list');
        exit();
    }
}
?>