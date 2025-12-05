<?php

// Controller quản lý tài khoản Hướng dẫn viên (HDV)
class GuideController
{
    private const ROLE_GUIDE = 'huong_dan_vien';
    private const REDIRECT_GUIDES = 'Location: ' . BASE_URL . 'guides';

    // Đảm bảo bảng guide_profiles tồn tại
    private function ensureProfileTable(PDO $pdo): void
    {
        static $ensured = false;
        if ($ensured) return;

        $sql = "
            CREATE TABLE IF NOT EXISTS guide_profiles (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT DEFAULT NULL,
                birthdate DATE DEFAULT NULL,
                avatar VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(20) DEFAULT NULL,
                certificate TEXT DEFAULT NULL,
                languages LONGTEXT DEFAULT NULL,
                experience TEXT DEFAULT NULL,
                history LONGTEXT DEFAULT NULL,
                rating DECIMAL(3,2) DEFAULT NULL,
                health_status TEXT DEFAULT NULL,
                group_type VARCHAR(50) DEFAULT NULL,
                speciality VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        $ensured = true;
    }

    // Lấy hồ sơ từ guide_profiles, fallback guide_files (dữ liệu cũ)
    private function fetchProfile(PDO $pdo, int $userId): ?array
    {
        try {
            $stmt = $pdo->prepare('SELECT * FROM guide_profiles WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $userId]);
            if ($profile = $stmt->fetch()) return $profile;
        } catch (PDOException $e) {}

        try {
            $stmt = $pdo->prepare('SELECT * FROM guide_files WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Chuẩn hóa giá trị: chuyển chuỗi rỗng thành null
    private function normalizeValue($value): mixed
    {
        if ($value === null || $value === '') return null;
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }
        return $value;
    }

    // Chuẩn hóa TEXT/LONGTEXT: chuyển chuỗi rỗng thành null
    private function normalizeText(?string $value): ?string
    {
        return $this->normalizeValue($value);
    }

    // Chuẩn hóa birthdate: validate định dạng date
    private function normalizeBirthdate($birthdate): ?string
    {
        $birthdate = $this->normalizeValue($birthdate);
        if (!$birthdate) return null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
            $date = DateTime::createFromFormat('Y-m-d', $birthdate);
            if ($date && $date->format('Y-m-d') === $birthdate) return $birthdate;
        }
        return null;
    }

    // Chuẩn hóa rating: trích xuất số từ chuỗi, giới hạn 0-5
    private function normalizeRating($rating): ?string
    {
        $rating = $this->normalizeValue($rating);
        if (!$rating || is_array($rating) || is_object($rating)) return null;

        $rating = str_replace(',', '.', trim((string)$rating));
        if ($rating === '') return null;

        $floatValue = null;
        if (is_numeric($rating)) {
            $floatValue = (float)$rating;
        } elseif (preg_match('/(\d+\.?\d*)\s*\/\s*\d+|(\d+\.?\d*)/', $rating, $matches)) {
            $floatValue = (float)($matches[1] ?? $matches[2]);
        }

        if ($floatValue === null) return null;
        return number_format(max(0.0, min(5.0, $floatValue)), 2, '.', '');
    }

    // Giới hạn độ dài chuỗi
    private function truncateString(?string $value, int $maxLength): ?string
    {
        $value = $this->normalizeValue($value);
        if (!$value) return null;
        return mb_strlen($value) > $maxLength ? mb_substr($value, 0, $maxLength) : $value;
    }

    // Upload file avatar
    private function uploadAvatar(?string $oldAvatar = null): ?string
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return $this->normalizeValue($oldAvatar);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes) || $file['size'] > $maxSize) {
            return $oldAvatar;
        }

        $uploadDir = BASE_PATH . '/public/images/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'avatar_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Xóa file cũ nếu có
            if ($oldAvatar && strpos($oldAvatar, '/uploads/') !== false) {
                $oldFilePath = BASE_PATH . '/' . ltrim(str_replace(BASE_URL, '', $oldAvatar), '/');
                if (file_exists($oldFilePath) && is_file($oldFilePath)) @unlink($oldFilePath);
            }
            return rtrim(BASE_URL, '/') . '/public/images/uploads/' . $fileName;
        }

        return $oldAvatar;
    }

    // Chuẩn hóa dữ liệu profile từ POST
    private function normalizeProfileData(array $post, ?string $oldAvatar = null): array
    {
        return [
            'birthdate'     => $this->normalizeBirthdate($post['birthdate'] ?? null),
            'avatar'        => $this->uploadAvatar($oldAvatar),
            'phone'         => $this->truncateString($post['phone'] ?? null, 20),
            'certificate'   => $this->normalizeText($post['certificate'] ?? null),
            'languages'     => $this->normalizeText($post['languages'] ?? null),
            'experience'    => $this->normalizeText($post['experience'] ?? null),
            'history'       => $this->normalizeText($post['history'] ?? null),
            'rating'        => $this->normalizeRating($post['rating'] ?? null),
            'health_status' => $this->normalizeText($post['health_status'] ?? null),
            'group_type'    => $this->truncateString($post['group_type'] ?? null, 50),
            'speciality'    => $this->truncateString($post['speciality'] ?? null, 100),
        ];
    }

    // Validate user data
    private function validateUser(string $name, string $email, string $password = '', ?int $excludeId = null): array
    {
        $errors = [];
        if ($name === '') $errors[] = 'Vui lòng nhập họ tên HDV';
        if ($email === '') {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        if ($password === '' && $excludeId === null) $errors[] = 'Vui lòng nhập mật khẩu';

        if (empty($errors)) {
            $pdo = getDB();
            if ($pdo) {
                $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
                $params = ['email' => $email];
                if ($excludeId) {
                    $sql .= ' AND id <> :id';
                    $params['id'] = $excludeId;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                if ((int)$stmt->fetchColumn() > 0) {
                    $errors[] = $excludeId ? 'Email này đã được sử dụng bởi tài khoản khác' : 'Email này đã tồn tại trong hệ thống';
                }
            }
        }

        return $errors;
    }

    // Validate và chuẩn hóa profile data
    private function validateProfileData(array $profile): array
    {
        foreach ($profile as $key => $value) {
            if ($value === '' || (is_string($value) && trim($value) === '')) {
                $profile[$key] = null;
            }
        }

        if ($profile['rating'] !== null && $profile['rating'] !== '') {
            $rating = is_numeric($profile['rating']) ? (float)$profile['rating'] : null;
            $profile['rating'] = ($rating !== null && $rating >= 0 && $rating <= 5) 
                ? number_format($rating, 2, '.', '') : null;
        } else {
            $profile['rating'] = null;
        }

        return $profile;
    }

    // Upsert profile vào database
    private function upsertProfile(PDO $pdo, int $userId, array $profile): void
    {
        $profile = $this->validateProfileData($profile);

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM guide_profiles WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $exists = (int)$stmt->fetchColumn() > 0;

        $sql = $exists
            ? 'UPDATE guide_profiles SET birthdate = :birthdate, avatar = :avatar, phone = :phone, certificate = :certificate,
                languages = :languages, experience = :experience, history = :history, rating = :rating,
                health_status = :health_status, group_type = :group_type, speciality = :speciality, updated_at = NOW()
                WHERE user_id = :user_id'
            : 'INSERT INTO guide_profiles (user_id, birthdate, avatar, phone, certificate, languages, experience, history, rating, health_status, group_type, speciality, created_at, updated_at)
                VALUES (:user_id, :birthdate, :avatar, :phone, :certificate, :languages, :experience, :history, :rating, :health_status, :group_type, :speciality, NOW(), NOW())';

        $pdo->prepare($sql)->execute(array_merge(['user_id' => $userId], $profile));
    }

    // Kiểm tra và lấy guide theo ID
    private function getGuideById(int $id): ?array
    {
        $pdo = getDB();
        if (!$pdo) return null;

        $this->ensureProfileTable($pdo);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id AND role = :role LIMIT 1');
        $stmt->execute(['id' => $id, 'role' => self::ROLE_GUIDE]);
        return $stmt->fetch() ?: null;
    }

    // Redirect helper
    private function redirect(string $url = ''): void
    {
        header($url ?: self::REDIRECT_GUIDES);
        exit;
    }

    // Kiểm tra POST method
    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }
    }

    // Danh sách HDV
    public function index(): void
    {
        requireAdmin();
        $pdo = getDB();
        $guides = [];

        if ($pdo) {
            $this->ensureProfileTable($pdo);
            $stmt = $pdo->prepare("
                SELECT u.*, gp.phone, gp.languages, gp.group_type
                FROM users u
                LEFT JOIN guide_profiles gp ON gp.user_id = u.id
                WHERE u.role = :role
                ORDER BY u.created_at DESC
            ");
            $stmt->execute(['role' => self::ROLE_GUIDE]);
            $guides = $stmt->fetchAll();
        }

        view('admin.guides.index', ['title' => 'Quản lý HDV', 'guides' => $guides]);
    }

    // Form thêm mới
    public function create(): void
    {
        requireAdmin();
        view('admin.guides.create', ['title' => 'Thêm Hướng dẫn viên']);
    }

    // Lưu mới
    public function store(): void
    {
        requireAdmin();
        $this->requirePost();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $status   = (int)($_POST['status'] ?? 1);
        $profile  = $this->normalizeProfileData($_POST, null);
        $errors   = $this->validateUser($name, $email, $password);

        if (!empty($errors)) {
            view('admin.guides.create', [
                'title'  => 'Thêm Hướng dẫn viên',
                'errors' => $errors,
                'old'    => array_merge(['name' => $name, 'email' => $email, 'status' => $status], $profile),
            ]);
            return;
        }

        $pdo = getDB();
        if ($pdo) {
            $this->ensureProfileTable($pdo);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at, updated_at)
                    VALUES (:name, :email, :password, :role, :status, NOW(), NOW())');
                $stmt->execute([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role'     => self::ROLE_GUIDE,
                    'status'   => $status,
                ]);

                $this->upsertProfile($pdo, (int)$pdo->lastInsertId(), $profile);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        $this->redirect();
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect();

        $guide = $this->getGuideById($id);
        if (!$guide) $this->redirect();

        $pdo = getDB();
        $profile = $pdo ? $this->fetchProfile($pdo, $id) : null;

        view('admin.guides.edit', [
            'title'   => 'Cập nhật Hướng dẫn viên',
            'guide'   => $guide,
            'profile' => $profile,
        ]);
    }

    // Cập nhật
    public function update(): void
    {
        requireAdmin();
        $this->requirePost();

        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $status   = (int)($_POST['status'] ?? 1);

        if ($id <= 0) $this->redirect();

        // Lấy avatar hiện tại
        $oldAvatar = null;
        $pdo = getDB();
        if ($pdo) {
            $this->ensureProfileTable($pdo);
            $stmt = $pdo->prepare('SELECT avatar FROM guide_profiles WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $id]);
            $oldAvatar = $stmt->fetch()['avatar'] ?? null;
        }

        $profile = $this->normalizeProfileData($_POST, $oldAvatar);
        $errors  = $this->validateUser($name, $email, '', $id);

        if (!empty($errors)) {
            view('admin.guides.edit', [
                'title'   => 'Cập nhật Hướng dẫn viên',
                'errors'  => $errors,
                'guide'   => ['id' => $id, 'name' => $name, 'email' => $email, 'status' => $status],
                'profile' => $profile,
            ]);
            return;
        }

        if ($pdo) {
            $this->ensureProfileTable($pdo);
            $pdo->beginTransaction();
            try {
                $sql = 'UPDATE users SET name = :name, email = :email, status = :status, updated_at = NOW()';
                $params = ['name' => $name, 'email' => $email, 'status' => $status, 'id' => $id];
                if ($password !== '') {
                    $sql .= ', password = :password';
                    $params['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id = :id AND role = :role';
                $params['role'] = self::ROLE_GUIDE;
                $pdo->prepare($sql)->execute($params);

                $this->upsertProfile($pdo, $id, $profile);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        $this->redirect();
    }

    // Xem chi tiết
    public function show(): void
    {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect();

        $guide = $this->getGuideById($id);
        if (!$guide) $this->redirect();

        $pdo = getDB();
        $profile = ($pdo ? $this->fetchProfile($pdo, $id) : null);

        view('admin.guides.show', [
            'title'   => 'Hồ sơ chi tiết HDV',
            'guide'   => $guide,
            'profile' => $profile,
        ]);
    }

    // Xóa
    public function delete(): void
    {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect();

        $pdo = getDB();
        if ($pdo) {
            $this->ensureProfileTable($pdo);
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM guide_profiles WHERE user_id = :user_id')->execute(['user_id' => $id]);
                $pdo->prepare('DELETE FROM users WHERE id = :id AND role = :role')->execute(['id' => $id, 'role' => self::ROLE_GUIDE]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        $this->redirect();
    }
}
