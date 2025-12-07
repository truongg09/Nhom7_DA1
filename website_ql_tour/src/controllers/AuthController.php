<?php

// Controller xử lý các chức năng liên quan đến xác thực (đăng nhập, đăng xuất)
class AuthController
{
    
    // Hiển thị form đăng nhập
    public function login()
    {
        // Nếu đã đăng nhập rồi thì chuyển về trang home
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . 'home');
            exit;  
        }

        // Lấy URL redirect nếu có (để quay lại trang đang xem sau khi đăng nhập)
        // Mặc định redirect về trang home
        $redirect = $_GET['redirect'] ?? BASE_URL . 'home';

        // Hiển thị view login
        view('auth.login', [
            'title' => 'Đăng nhập',
            'redirect' => $redirect,
        ]);
    }

    // Xử lý đăng nhập (nhận dữ liệu từ form POST)
    public function checkLogin()
    {
        // Chỉ xử lý khi là POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Lấy dữ liệu từ form
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        // Mặc định redirect về trang home sau khi đăng nhập
        $redirect = $_POST['redirect'] ?? BASE_URL . 'home';

        // Validate dữ liệu đầu vào
        $errors = [];

        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        // Nếu có lỗi validation ban đầu, hiển thị lỗi và dừng lại
        if (!empty($errors)) {
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // --- BẮT ĐẦU: LOGIC XÁC THỰC VỚI DATABASE (ĐÃ SỬA) ---

        $pdo = getDB();
        $user_data = null; // Khởi tạo biến lưu dữ liệu user
        
        if (!$pdo) {
            $errors[] = 'Lỗi kết nối database. Vui lòng thử lại sau.';
        } else {
            // 1. Tìm user theo email, lấy tất cả các trường cần thiết
            $stmt = $pdo->prepare("SELECT id, name, email, role, status, password FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Kiểm tra tồn tại
            if (!$user_data) {
                $errors[] = 'Email hoặc mật khẩu không đúng.';
            } 
            // 3. Kiểm tra mật khẩu (Sử dụng mật khẩu chưa mã hóa theo cấu trúc của bạn)
            else if ($user_data['password'] !== $password) { 
                $errors[] = 'Email hoặc mật khẩu không đúng.';
            }
            // 4. Kiểm tra trạng thái hoạt động
            else if ($user_data['status'] != 1) { 
                $errors[] = 'Tài khoản của bạn đã bị khóa hoặc không hoạt động.';
            }
        }
        
        // --- KẾT THÚC LOGIC XÁC THỰC DB ---

        // Nếu có lỗi sau khi kiểm tra DB, hiển thị lỗi và dừng lại
        if (!empty($errors)) {
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Đăng nhập thành công: Tạo đối tượng User THẬT (lấy dữ liệu từ DB) và lưu vào session
        $user = new User([
            'id' => $user_data['id'],
            'name' => $user_data['name'],
            'email' => $user_data['email'],
            'role' => $user_data['role'],
            'status' => $user_data['status'],
        ]);

        loginUser($user); // Hàm này sẽ lưu ID CHÍNH XÁC (ID 3) vào Session

        // Chuyển hướng về trang được yêu cầu hoặc trang chủ
        header('Location: ' . $redirect);
        exit;
    }

    // Xử lý đăng xuất
    public function logout()
    {
        // Xóa session và đăng xuất
        logoutUser();

        // Chuyển hướng về trang welcome
        header('Location: ' . BASE_URL . 'welcome');
        exit;
    }
}