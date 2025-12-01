<?php
// Controller chịu trách nhiệm xử lý logic cho các trang cơ bản
class HomeController
{
    // Trang welcome - hiển thị cho người chưa đăng nhập
    // Nếu đã đăng nhập thì redirect về trang home
    public function welcome(): void
    {
        // Nếu đã đăng nhập thì redirect về trang home
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        // Hiển thị view welcome
        view('welcome', [
            'title' => 'Chào mừng - Website Quản Lý Tour',
        ]);
    }

    // Trang home - chỉ dành cho người đã đăng nhập
    // Nếu chưa đăng nhập thì redirect về trang welcome
    public function home(): void
    {
        // Yêu cầu phải đăng nhập, nếu chưa thì redirect về welcome
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . 'welcome');
            exit;
        }

        // Lấy thông tin user hiện tại (đã đảm bảo đăng nhập ở trên)
        $currentUser = getCurrentUser();

        // Chuẩn bị số liệu thống kê cho dashboard
        $stats = [
            'totalTours'      => 0,
            'totalBookings'   => 0,
            'totalCustomers'  => 0,
            'totalRevenue'    => 0,
        ];

        $pdo = getDB();
        if ($pdo) {
            try {
                // Tổng số tour
                $stats['totalTours'] = (int)$pdo->query('SELECT COUNT(*) FROM tours')->fetchColumn();
            } catch (Throwable $e) {
                // Bỏ qua nếu bảng chưa tồn tại, tránh làm lỗi trang
            }

            try {
                // Tổng số booking/đặt tour
                $stats['totalBookings'] = (int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
            } catch (Throwable $e) {
                // Bỏ qua nếu bảng chưa tồn tại
            }

            try {
                // Tổng số khách hàng
                $stats['totalCustomers'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
            } catch (Throwable $e) {
                // Bỏ qua nếu bảng chưa tồn tại
            }

            try {
                // Tổng doanh thu (tuỳ vào cấu trúc DB của bạn, nhớ chỉnh lại tên bảng/cột cho đúng)
                // Ví dụ: bảng bookings có cột total_amount lưu tổng tiền của mỗi đơn
                $stats['totalRevenue'] = (float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM bookings')->fetchColumn();
            } catch (Throwable $e) {
                // Bỏ qua nếu bảng/cột chưa tồn tại
            }
        }

        // Hiển thị view home với dữ liệu title, user và thống kê
        view('home', [
            'title' => 'Trang chủ - Website Quản Lý Tour',
            'user' => $currentUser,
            'stats' => $stats,
        ]);
    }

    // Trang hiển thị khi route không tồn tại
    public function notFound(): void
    {
        http_response_code(404);
        // Hiển thị view not_found với dữ liệu title
        view('not_found', [
            'title' => 'Không tìm thấy trang',
        ]);
    }
}
