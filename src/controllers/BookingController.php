<?php

class BookingController
{
    public function __construct()
    {
        requireGuideOrAdmin();
    }

    public function index(): void
    {
        $bookings = Booking::all();
        $statuses = TourStatus::all();

        view('admin.bookings.index', [
            'title' => 'Danh sách Booking',
            'pageTitle' => 'Quản lý Booking',
            'bookings' => $bookings,
            'statuses' => $statuses,
            'message' => $_GET['message'] ?? null,
            'messageType' => $_GET['type'] ?? 'success',
        ]);
    }

    public function create(): void
    {
        $tours = Tour::all();
        $users = $this->getUsers();
        $statuses = TourStatus::all();

        view('admin.bookings.create', [
            'title' => 'Thêm booking mới',
            'pageTitle' => 'Thêm booking mới',
            'tours' => $tours,
            'users' => $users,
            'statuses' => $statuses,
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST);

        if ($data['errors']) {
            $tours = Tour::all();
            $users = $this->getUsers();
            $statuses = TourStatus::all();
            view('admin.bookings.create', [
                'title' => 'Thêm booking mới',
                'pageTitle' => 'Thêm booking mới',
                'errors' => $data['errors'],
                'old' => $data['fields'],
                'tours' => $tours,
                'users' => $users,
                'statuses' => $statuses,
            ]);
            return;
        }

        // Set created_by là user hiện tại nếu chưa có
        if (empty($data['fields']['created_by'])) {
            $user = getCurrentUser();
            $data['fields']['created_by'] = $user ? $user->id : null;
        }

        // Xử lý upload file danh sách
        $uploadedFile = $this->uploadListsFile();
        if (isset($_SESSION['upload_error'])) {
            $uploadError = $_SESSION['upload_error'];
            unset($_SESSION['upload_error']);
            $tours = Tour::all();
            $users = $this->getUsers();
            $statuses = TourStatus::all();
            view('admin.bookings.create', [
                'title' => 'Thêm booking mới',
                'pageTitle' => 'Thêm booking mới',
                'errors' => array_merge($data['errors'] ?? [], [$uploadError]),
                'old' => $data['fields'],
                'tours' => $tours,
                'users' => $users,
                'statuses' => $statuses,
            ]);
            return;
        }
        
        if ($uploadedFile) {
            $data['fields']['lists_file'] = $uploadedFile;
        }

        Booking::create($data['fields']);
        $this->redirectWithMessage('bookings', 'Thêm booking thành công');
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        $booking = $id ? Booking::find($id) : null;

        if (!$booking) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        $statusLogs = Booking::getStatusLogs($id);
        $statuses = TourStatus::all();

        $currentUser = getCurrentUser();
        $viewPath = ($currentUser && $currentUser->isGuide()) ? 'hdv.bookings.show' : 'admin.bookings.show';

        view($viewPath, [
            'title' => 'Chi tiết booking',
            'pageTitle' => 'Chi tiết booking',
            'booking' => $booking,
            'statusLogs' => $statusLogs,
            'statuses' => $statuses,
        ]);
    }

    public function edit(): void
    {
        $id = $_GET['id'] ?? null;
        $booking = $id ? Booking::find($id) : null;

        if (!$booking) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        $tours = Tour::all();
        $users = $this->getUsers();
        $statuses = TourStatus::all();

        view('admin.bookings.edit', [
            'title' => 'Chỉnh sửa booking',
            'pageTitle' => 'Chỉnh sửa booking',
            'booking' => $booking,
            'tours' => $tours,
            'users' => $users,
            'statuses' => $statuses,
        ]);
    }

    public function update(): void
    {
        $id = $_POST['id'] ?? null;
        $existingBooking = $id ? Booking::find($id) : null;

        if (!$id || !$existingBooking) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        $data = $this->validate($_POST);

        if ($data['errors']) {
            $tours = Tour::all();
            $users = $this->getUsers();
            $statuses = TourStatus::all();
            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'pageTitle' => 'Chỉnh sửa booking',
                'errors' => $data['errors'],
                'booking' => array_merge($data['fields'], ['id' => $id]),
                'tours' => $tours,
                'users' => $users,
                'statuses' => $statuses,
            ]);
            return;
        }

        // Xử lý upload file danh sách
        $uploadedFile = $this->uploadListsFile($existingBooking['lists_file'] ?? null);
        if (isset($_SESSION['upload_error'])) {
            $uploadError = $_SESSION['upload_error'];
            unset($_SESSION['upload_error']);
            $tours = Tour::all();
            $users = $this->getUsers();
            $statuses = TourStatus::all();
            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'pageTitle' => 'Chỉnh sửa booking',
                'errors' => array_merge($data['errors'] ?? [], [$uploadError]),
                'booking' => array_merge($data['fields'], ['id' => $id]),
                'tours' => $tours,
                'users' => $users,
                'statuses' => $statuses,
            ]);
            return;
        }
        
        if ($uploadedFile) {
            $data['fields']['lists_file'] = $uploadedFile;
        } else {
            // Giữ nguyên file cũ nếu không upload file mới
            $data['fields']['lists_file'] = $existingBooking['lists_file'] ?? '';
        }

        // Kiểm tra nếu status thay đổi thì log lại
        $oldStatus = $existingBooking['status'] ?? null;
        $newStatus = $data['fields']['status'] ?? null;
        
        Booking::update($id, $data['fields']);

        // Nếu status thay đổi, tạo log
        if ($oldStatus != $newStatus && $newStatus !== null) {
            $this->logStatusChange($id, $oldStatus, $newStatus);
        }

        $this->redirectWithMessage('bookings', 'Cập nhật booking thành công');
    }

    public function destroy(): void
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        $booking = Booking::find($id);
        if (!$booking) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        if (Booking::delete($id)) {
            $this->redirectWithMessage('bookings', 'Xóa booking thành công');
        } else {
            $this->redirectWithMessage('bookings', 'Không thể xóa booking này. Có thể booking đang được sử dụng ở nơi khác.', 'danger');
        }
    }

    // Xem và viết nhật ký cho booking
    public function diary(): void
    {
        $id = $_GET['id'] ?? null;
        $booking = $id ? Booking::find($id) : null;

        if (!$booking) {
            $this->redirectWithMessage('bookings', 'Booking không tồn tại', 'danger');
            return;
        }

        // Nếu có POST thì cập nhật nhật ký
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diary = trim($_POST['diary'] ?? '');

            $updateData = [
                'tour_id' => $booking['tour_id'],
                'customer_id' => $booking['customer_id'] ?? null,
                'created_by' => $booking['created_by'],
                'assigned_guide_id' => $booking['assigned_guide_id'],
                'status' => $booking['status'],
                'start_date' => $booking['start_date'],
                'end_date' => $booking['end_date'],
                'schedule_detail' => $booking['schedule_detail'],
                'service_detail' => $booking['service_detail'],
                'diary' => $diary,
                'lists_file' => $booking['lists_file'],
                'notes' => $booking['notes'],
            ];
            
            if (Booking::update($id, $updateData)) {
                $this->redirectWithMessage('booking-diary&id=' . $id, 'Cập nhật nhật ký thành công');
                return;
            }
        }

        $currentUser = getCurrentUser();
        $viewPath = ($currentUser && $currentUser->isGuide()) ? 'hdv.bookings.diary' : 'admin.bookings.diary';

        view($viewPath, [
            'title' => 'Nhật ký - Booking #' . $id,
            'pageTitle' => 'Nhật ký',
            'booking' => $booking,
        ]);
    }

    private function validate(array $input): array
    {
        $fields = [
            'tour_id' => trim($input['tour_id'] ?? ''),
            'created_by' => trim($input['created_by'] ?? ''),
            'assigned_guide_id' => trim($input['assigned_guide_id'] ?? ''),
            'status' => trim($input['status'] ?? ''),
            'start_date' => trim($input['start_date'] ?? ''),
            'end_date' => trim($input['end_date'] ?? ''),
            'schedule_detail' => trim($input['schedule_detail'] ?? ''),
            'service_detail' => trim($input['service_detail'] ?? ''),
            'diary' => trim($input['diary'] ?? ''),
            'lists_file' => trim($input['lists_file'] ?? ''),
            'notes' => trim($input['notes'] ?? ''),
        ];

        $errors = [];

        if ($fields['tour_id'] === '') {
            $errors[] = 'Vui lòng chọn tour';
        }

        return [
            'fields' => $fields,
            'errors' => $errors,
        ];
    }

    private function getUsers(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query('SELECT id, name, role FROM users ORDER BY name ASC');
        return $stmt->fetchAll() ?: [];
    }

    private function logStatusChange($bookingId, $oldStatus, $newStatus): void
    {
        $pdo = getDB();
        if (!$pdo) {
            return;
        }

        $user = getCurrentUser();
        $changedBy = $user ? $user->id : null;

        // Lấy tên status từ database
        $oldStatusName = TourStatus::getName($oldStatus) ?: $oldStatus;
        $newStatusName = TourStatus::getName($newStatus) ?: $newStatus;

        $stmt = $pdo->prepare(
            'INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_by, note)
             VALUES (:booking_id, :old_status, :new_status, :changed_by, :note)'
        );
        $stmt->execute([
            'booking_id' => $bookingId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'note' => 'Thay đổi trạng thái từ ' . $oldStatusName . ' sang ' . $newStatusName,
        ]);
    }

    // Upload file danh sách
    public function uploadListsFile(?string $oldFile = null): ?string
    {
        startSession();
        
        // Kiểm tra xem có file được upload không
        if (!isset($_FILES['lists_file'])) {
            return null;
        }

        $file = $_FILES['lists_file'];
        
        // Kiểm tra lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File vượt quá kích thước cho phép (php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'File vượt quá kích thước cho phép (form)',
                UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
                UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file lên disk',
                UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension',
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? 'Lỗi upload không xác định (mã lỗi: ' . $file['error'] . ')';
            $_SESSION['upload_error'] = $errorMsg;
            return null;
        }

        // Kiểm tra file có tồn tại không
        if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $_SESSION['upload_error'] = 'File upload không hợp lệ';
            return null;
        }
        
        // Cho phép các loại file phổ biến
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/jpg',
        ];
        
        $maxSize = 10 * 1024 * 1024; // 10MB

        // Kiểm tra kích thước file
        if ($file['size'] > $maxSize) {
            $_SESSION['upload_error'] = 'File vượt quá kích thước cho phép (tối đa 10MB)';
            return null;
        }

        // Kiểm tra MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['upload_error'] = 'Loại file không được hỗ trợ. Chỉ chấp nhận: PDF, Word, Excel, Text, CSV, Image';
            return null;
        }

        // Tạo thư mục nếu chưa tồn tại
        $uploadDir = BASE_PATH . '/public/files/bookings/';
        
        // Đảm bảo thư mục cha tồn tại và có quyền
        $parentDir = BASE_PATH . '/public/files/';
        if (!is_dir($parentDir)) {
            @mkdir($parentDir, 0777, true);
            @chmod($parentDir, 0777);
        } elseif (!is_writable($parentDir)) {
            @chmod($parentDir, 0777);
        }
        
        if (!is_dir($uploadDir)) {
            // Lưu umask hiện tại và set về 0 để đảm bảo quyền 777
            $oldUmask = umask(0);
            if (!@mkdir($uploadDir, 0777, true)) {
                umask($oldUmask);
                $_SESSION['upload_error'] = 'Không thể tạo thư mục lưu file: ' . $uploadDir;
                return null;
            }
            // Đảm bảo quyền ghi sau khi tạo
            @chmod($uploadDir, 0777);
            // Khôi phục umask
            umask($oldUmask);
        }

        // Kiểm tra quyền ghi và thử sửa nếu cần
        if (!is_writable($uploadDir)) {
            // Thử thay đổi quyền
            @chmod($uploadDir, 0777);
            
            // Kiểm tra lại
            if (!is_writable($uploadDir)) {
                $_SESSION['upload_error'] = 'Thư mục lưu file không có quyền ghi. Vui lòng liên hệ quản trị viên để cấp quyền ghi cho thư mục: ' . $uploadDir;
                return null;
            }
        }

        $originalFileName = $file['name'];
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $fileName = 'lists_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Xóa file cũ nếu có
            if ($oldFile) {
                // Extract URL từ JSON nếu cần
                $oldFileUrl = '';
                $decoded = json_decode($oldFile, true);
                if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['url'])) {
                        $oldFileUrl = $decoded['url'];
                    } elseif (isset($decoded['raw'])) {
                        $oldFileUrl = $decoded['raw'];
                    } elseif (is_string($decoded)) {
                        $oldFileUrl = $decoded;
                    }
                } else {
                    $oldFileUrl = $oldFile;
                }
                
                if ($oldFileUrl) {
                    $oldFilePath = $this->getFilePathFromUrl($oldFileUrl);
                    if ($oldFilePath && file_exists($oldFilePath) && is_file($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
            }
            
            // Lưu cả URL và tên file gốc dưới dạng JSON
            $fileData = [
                'url' => rtrim(BASE_URL, '/') . '/public/files/bookings/' . $fileName,
                'original_name' => $originalFileName
            ];
            
            // Xóa thông báo lỗi nếu có
            unset($_SESSION['upload_error']);
            return json_encode($fileData, JSON_UNESCAPED_UNICODE);
        }

        $_SESSION['upload_error'] = 'Không thể di chuyển file đến thư mục đích';
        return null;
    }

    // Download file danh sách
    public function downloadListsFile(): void
    {
        $id = $_GET['id'] ?? null;
        $booking = $id ? Booking::find($id) : null;

        if (!$booking || empty($booking['lists_file'])) {
            $this->redirectWithMessage('bookings', 'File không tồn tại', 'danger');
            return;
        }

        // Extract URL và original_name từ JSON nếu cần
        $listsFile = $booking['lists_file'];
        $fileUrl = '';
        $originalFileName = '';
        
        $decoded = json_decode($listsFile, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            // Định dạng mới với url và original_name
            if (isset($decoded['url']) && isset($decoded['original_name'])) {
                $fileUrl = $decoded['url'];
                $originalFileName = $decoded['original_name'];
            }
            // Định dạng cũ với raw
            elseif (isset($decoded['raw'])) {
                $fileUrl = $decoded['raw'];
                $originalFileName = basename($fileUrl);
            }
            // String trong JSON
            elseif (is_string($decoded)) {
                $fileUrl = $decoded;
                $originalFileName = basename($fileUrl);
            }
        } else {
            // Text thuần
            $fileUrl = $listsFile;
            $originalFileName = basename($fileUrl);
        }

        if (empty($fileUrl)) {
            $this->redirectWithMessage('bookings', 'File không tồn tại', 'danger');
            return;
        }

        $filePath = $this->getFilePathFromUrl($fileUrl);

        if (!$filePath || !file_exists($filePath) || !is_file($filePath)) {
            $this->redirectWithMessage('bookings', 'File không tồn tại', 'danger');
            return;
        }

        // Sử dụng tên file gốc nếu có, nếu không thì lấy từ URL hoặc tạo tên mới
        $originalName = $originalFileName ?: basename($fileUrl);
        if (empty($originalName) || $originalName === $fileUrl) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $originalName = 'danh_sach_booking_' . $id . ($extension ? '.' . $extension : '');
        }

        // Set headers để download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }

    // Lấy đường dẫn file từ URL
    private function getFilePathFromUrl(string $url): ?string
    {
        // Loại bỏ BASE_URL và lấy đường dẫn tương đối
        $relativePath = str_replace(BASE_URL, '', $url);
        $relativePath = ltrim($relativePath, '/');
        
        // Tạo đường dẫn tuyệt đối
        $filePath = BASE_PATH . '/' . $relativePath;
        
        // Kiểm tra file có tồn tại và nằm trong thư mục public
        if (file_exists($filePath) && strpos(realpath($filePath), realpath(BASE_PATH . '/public')) === 0) {
            return $filePath;
        }
        
        return null;
    }

    private function redirectWithMessage(string $route, string $message, string $type = 'success'): void
    {
        $separator = (strpos($route, '?') !== false) ? '&' : '?';
        $url = BASE_URL . $route . $separator . 'message=' . urlencode($message) . '&type=' . $type;
        header('Location: ' . $url);
        exit;
    }
}
