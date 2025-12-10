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

        // Nếu có lỗi validation thì quay lại form login
        if (!empty($errors)) {
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kiểm tra thông tin đăng nhập trong database
        $pdo = getDB();
        if (!$pdo) {
            $errors[] = 'Không thể kết nối đến cơ sở dữ liệu';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Tìm user theo email
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $userData = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Có lỗi xảy ra khi kiểm tra thông tin đăng nhập';
            error_log('Login error: ' . $e->getMessage());
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kiểm tra user có tồn tại và mật khẩu đúng không
        if (!$userData || empty($userData['password'])) {
            $errors[] = 'Email hoặc mật khẩu không đúng';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kiểm tra mật khẩu
        $passwordValid = false;
        $storedPassword = $userData['password'] ?? '';
        
        // Kiểm tra xem mật khẩu có được hash không (password hash thường bắt đầu bằng $2y$ hoặc $2a$)
        if (!empty($storedPassword) && (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$2a$') === 0 || strpos($storedPassword, '$2b$') === 0)) {
            // Mật khẩu đã được hash, sử dụng password_verify
            $passwordValid = password_verify($password, $storedPassword);
        } else {
            // Mật khẩu chưa được hash (plain text) - so sánh trực tiếp (tạm thời để hỗ trợ migration)
            // Lưu ý: Sau khi tất cả mật khẩu đã được hash, nên xóa phần này
            $passwordValid = ($password === $storedPassword);
            
            // Nếu đăng nhập thành công với plain text, tự động hash và cập nhật lại database
            if ($passwordValid && !empty($storedPassword)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                $updateStmt->execute(['password' => $hashedPassword, 'id' => $userData['id']]);
            }
        }
        
        if (!$passwordValid) {
            $errors[] = 'Email hoặc mật khẩu không đúng';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kiểm tra trạng thái tài khoản
        if ((int)($userData['status'] ?? 0) !== 1) {
            $errors[] = 'Tài khoản của bạn đã bị khóa';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Tạo đối tượng User với dữ liệu từ database (bao gồm role thực tế)
        $user = new User([
            'id' => $userData['id'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role'], // Lấy role từ database
            'status' => $userData['status'],
        ]);

        // Đăng nhập thành công: lưu vào session
        loginUser($user);

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

