<?php

// Nạp cấu hình chung của ứng dụng
$config = require __DIR__ . '/config/config.php';

// Nạp các file chứa hàm trợ giúp
require_once __DIR__ . '/src/helpers/helpers.php'; // Helper chứa các hàm trợ giúp (hàm xử lý view, block, asset, session, ...)
require_once __DIR__ . '/src/helpers/database.php'; // Helper kết nối database(kết nối với cơ sở dữ liệu)

// Nạp các file chứa model
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/TourModel.php';
require_once __DIR__ . '/src/models/CheckinModel.php';
require_once __DIR__ . '/src/models/TourDiaryModel.php';
require_once __DIR__ . '/src/models/Booking.php';
require_once __DIR__ . '/src/models/PersonnelModel.php';
 



// Nạp các file chứa controller
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/TourGuideController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';


// Khởi tạo các controller không yêu cầu quyền đặc biệt
$homeController = new HomeController();
$authController = new AuthController();
$tourguideController = new TourGuideController();
$bookingController = new BookingController();

// Xác định route dựa trên tham số act (mặc định là trang chủ '/')
$act = $_GET['act'] ?? '/';

// Match đảm bảo chỉ một action tương ứng được gọi
match ($act) {
    // Trang welcome (cho người chưa đăng nhập) - mặc định khi truy cập '/'
    '/', 'welcome' => $homeController->welcome(),

    // Trang home (cho người đã đăng nhập)
    'home' => $homeController->home(),

    // Đường dẫn đăng nhập, đăng xuất
    'login' => $authController->login(),
    'check-login' => $authController->checkLogin(),
    'logout' => $authController->logout(),

    // Đường dẫn quản lý booking
    'booking-create' => $bookingController->create(),
    'booking-list' => $bookingController->index(),
    'booking-allocate' => $bookingController->allocatePersonnel(),
    'booking-diary' => $bookingController->tourDiary(),
    'booking-checkin' => $bookingController->departureCheckin(),
    'booking-update-status' => $bookingController->updateStatus(),

    // Đường dẫn vận hành tour cho HDV
    'hdv-tours' => $tourguideController->viewAssignedTours(),
    'hdv-customers' => $tourguideController->viewCustomerList(),
    'hdv-checkin' => $tourguideController->handleCheckinForm(),
    'hdv-diary' => $tourguideController->handleTourDiary(),
    'hdv-diary-save' => $tourguideController->saveTourDiary(),

    // Đường dẫn không tồn tại
    default => $homeController->notFound(),
};