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

        view('admin.bookings.index', [
            'title' => 'Danh sách Booking',
            'pageTitle' => 'Quản lý Booking',
            'bookings' => $bookings,
            'message' => $_GET['message'] ?? null,
            'messageType' => $_GET['type'] ?? 'success',
        ]);
    }

    public function create(): void
    {
        $tours = Tour::all();
        $users = $this->getUsers();

        view('admin.bookings.create', [
            'title' => 'Thêm booking mới',
            'pageTitle' => 'Thêm booking mới',
            'tours' => $tours,
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST);

        if ($data['errors']) {
            $tours = Tour::all();
            $users = $this->getUsers();
            view('admin.bookings.create', [
                'title' => 'Thêm booking mới',
                'pageTitle' => 'Thêm booking mới',
                'errors' => $data['errors'],
                'old' => $data['fields'],
                'tours' => $tours,
                'users' => $users,
            ]);
            return;
        }

        // Set created_by là user hiện tại nếu chưa có
        if (empty($data['fields']['created_by'])) {
            $user = getCurrentUser();
            $data['fields']['created_by'] = $user ? $user->id : null;
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

        view('admin.bookings.show', [
            'title' => 'Chi tiết booking',
            'pageTitle' => 'Chi tiết booking',
            'booking' => $booking,
            'statusLogs' => $statusLogs,
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

        view('admin.bookings.edit', [
            'title' => 'Chỉnh sửa booking',
            'pageTitle' => 'Chỉnh sửa booking',
            'booking' => $booking,
            'tours' => $tours,
            'users' => $users,
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
            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'pageTitle' => 'Chỉnh sửa booking',
                'errors' => $data['errors'],
                'booking' => array_merge($data['fields'], ['id' => $id]),
                'tours' => $tours,
                'users' => $users,
            ]);
            return;
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

        $stmt = $pdo->prepare(
            'INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_by, note)
             VALUES (:booking_id, :old_status, :new_status, :changed_by, :note)'
        );
        $stmt->execute([
            'booking_id' => $bookingId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'note' => 'Thay đổi trạng thái từ ' . $oldStatus . ' sang ' . $newStatus,
        ]);
    }

    private function redirectWithMessage(string $route, string $message, string $type = 'success'): void
    {
        $separator = (strpos($route, '?') !== false) ? '&' : '?';
        $url = BASE_URL . $route . $separator . 'message=' . urlencode($message) . '&type=' . $type;
        header('Location: ' . $url);
        exit;
    }
}
