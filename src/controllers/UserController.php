<?php

// Controller quản lý tài khoản người dùng (dành cho admin)
class UserController
{
    // Danh sách tất cả tài khoản
    public function index(): void
    {
        requireAdmin();

        $pdo   = getDB();
        $users = [];

        if ($pdo) {
            $stmt = $pdo->prepare('SELECT * FROM users ORDER BY created_at DESC');
            $stmt->execute();
            $users = $stmt->fetchAll();
        }

        view('admin.users.index', [
            'title' => 'Quản lý người dùng',
            'users' => $users,
        ]);
    }

    // Hiển thị form thêm mới
    public function create(): void
    {
        requireAdmin();

        view('admin.users.create', [
            'title' => 'Thêm người dùng',
        ]);
    }

    // Lưu người dùng mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'huong_dan_vien');
        $status   = (int)($_POST['status'] ?? 1);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Vui lòng nhập họ tên';
        }
        if ($email === '') {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        if ($password === '') {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        $pdo = getDB();

        // Kiểm tra trùng email
        if ($pdo && empty($errors)) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors[] = 'Email này đã tồn tại trong hệ thống';
            }
        }

        if (!empty($errors)) {
            view('admin.users.create', [
                'title'  => 'Thêm người dùng',
                'errors' => $errors,
                'old'    => [
                    'name'   => $name,
                    'email'  => $email,
                    'role'   => $role,
                    'status' => $status,
                ],
            ]);
            return;
        }

        if ($pdo) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password, role, status, created_at, updated_at)
                 VALUES (:name, :email, :password, :role, :status, NOW(), NOW())'
            );
            $stmt->execute([
                'name'     => $name,
                'email'    => $email,
                'password' => $hashedPassword,
                'role'     => $role,
                'status'   => $status,
            ]);
        }

        header('Location: ' . BASE_URL . 'users');
        exit;
    }

    // Hiển thị form sửa người dùng
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $pdo  = getDB();
        $user = null;

        if ($pdo) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
        }

        if (!$user) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        view('admin.users.edit', [
            'title' => 'Cập nhật người dùng',
            'user'  => $user,
        ]);
    }

    // Cập nhật người dùng
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'huong_dan_vien');
        $status   = (int)($_POST['status'] ?? 1);

        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $errors = [];

        if ($name === '') {
            $errors[] = 'Vui lòng nhập họ tên';
        }
        if ($email === '') {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }

        $pdo = getDB();

        // Kiểm tra trùng email với tài khoản khác
        if ($pdo && empty($errors)) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id');
            $stmt->execute(['email' => $email, 'id' => $id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors[] = 'Email này đã được sử dụng bởi tài khoản khác';
            }
        }

        if (!empty($errors)) {
            view('admin.users.edit', [
                'title' => 'Cập nhật người dùng',
                'errors' => $errors,
                'user' => [
                    'id'     => $id,
                    'name'   => $name,
                    'email'  => $email,
                    'role'   => $role,
                    'status' => $status,
                ],
            ]);
            return;
        }

        if ($pdo) {
            $sql = 'UPDATE users SET name = :name, email = :email, role = :role, status = :status, updated_at = NOW()';
            $params = [
                'name'   => $name,
                'email'  => $email,
                'role'   => $role,
                'status' => $status,
                'id'     => $id,
            ];

            if ($password !== '') {
                $sql .= ', password = :password';
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        header('Location: ' . BASE_URL . 'users');
        exit;
    }

    // Xóa người dùng
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $pdo = getDB();
        if ($pdo) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
        }

        header('Location: ' . BASE_URL . 'users');
        exit;
    }
}


