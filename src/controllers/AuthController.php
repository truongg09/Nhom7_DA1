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

        // Lấy email đã ghi nhớ (nếu có)
        $rememberEmail = $_COOKIE['remember_email'] ?? '';

        // Hiển thị view login
        view('auth.login', [
            'title' => 'Đăng nhập',
            'redirect' => $redirect,
            'rememberEmail' => $rememberEmail,
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
        $email = trim($_POST['email'] ?? '');
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

        // Lấy kết nối DB
        $pdo = getDB();
        if (!$pdo) {
            $errors[] = 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Tìm user theo email
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $userRow = $stmt->fetch();

        // Kiểm tra tồn tại user và mật khẩu
        if (!$userRow || !password_verify($password, $userRow['password'])) {
            $errors[] = 'Email hoặc mật khẩu không chính xác';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kiểm tra trạng thái tài khoản
        if ((int)($userRow['status'] ?? 0) !== 1) {
            $errors[] = 'Tài khoản của bạn đang bị khóa. Vui lòng liên hệ quản trị viên.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Tạo đối tượng User từ dữ liệu DB
        $user = new User([
            'id'         => $userRow['id'],
            'name'       => $userRow['name'],
            'email'      => $userRow['email'],
            'role'       => $userRow['role'],
            'status'     => $userRow['status'],
            'created_at' => $userRow['created_at'] ?? null,
            'update_at'  => $userRow['update_at'] ?? null,
        ]);

        // Đăng nhập thành công: lưu vào session
        loginUser($user);

        // Ghi nhớ email nếu người dùng chọn "Ghi nhớ tài khoản"
        if (!empty($_POST['remember_me'])) {
            setcookie('remember_email', $user->email, time() + 30 * 24 * 60 * 60, '/');
        } else {
            // Nếu bỏ chọn thì xóa cookie
            setcookie('remember_email', '', time() - 3600, '/');
        }

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

