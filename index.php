<?php

// Bật hiển thị lỗi trong môi trường phát triển (có thể tắt sau khi sửa xong)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Nạp cấu hình chung của ứng dụng
$config = require __DIR__ . '/config/config.php';

// Nạp các file chứa hàm trợ giúp
require_once __DIR__ . '/src/helpers/helpers.php'; // Helper chứa các hàm trợ giúp (hàm xử lý view, block, asset, session, ...)
require_once __DIR__ . '/src/helpers/database.php'; // Helper kết nối database(kết nối với cơ sở dữ liệu)

// Nạp các file chứa model
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Tour.php';
require_once __DIR__ . '/src/models/Category.php';
require_once __DIR__ . '/src/models/Booking.php';

// Nạp các file chứa controller
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/TourController.php';
require_once __DIR__ . '/src/controllers/CategoryController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';

// Khởi tạo các controller không yêu cầu quyền đặc biệt
$homeController = new HomeController();
$authController = new AuthController();

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

    // Quản lý tour
    'tours' => (new TourController())->index(),
    'tour-show' => (new TourController())->show(),
    'tour-create' => (new TourController())->create(),
    'tour-store' => (new TourController())->store(),
    'tour-edit' => (new TourController())->edit(),
    'tour-update' => (new TourController())->update(),
    'tour-delete' => (new TourController())->destroy(),

    // Quản lý danh mục tour
    'categories' => (new CategoryController())->index(),
    'category-show' => (new CategoryController())->show(),
    'category-create' => (new CategoryController())->create(),
    'category-store' => (new CategoryController())->store(),
    'category-edit' => (new CategoryController())->edit(),
    'category-update' => (new CategoryController())->update(),
    'category-delete' => (new CategoryController())->destroy(),

    // Quản lý booking
    'bookings' => (new BookingController())->index(),
    'booking-show' => (new BookingController())->show(),
    'booking-create' => (new BookingController())->create(),
    'booking-store' => (new BookingController())->store(),
    'booking-edit' => (new BookingController())->edit(),
    'booking-update' => (new BookingController())->update(),
    'booking-delete' => (new BookingController())->destroy(),

    // Đường dẫn không tồn tại
    default => $homeController->notFound(),
};
