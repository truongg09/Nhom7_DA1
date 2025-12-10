<?php

class TourController
{
    public function __construct()
    {
        requireGuideOrAdmin();
    }

    public function index(): void
    {
        $currentUser = getCurrentUser();
        
        // Nếu là hướng dẫn viên, chỉ lấy tours được phân công
        if ($currentUser && $currentUser->isGuide()) {
            $tours = Tour::getAssignedTours($currentUser->id);
            $pageTitle = 'Tour được phân công';
        } else {
            $tours = Tour::all();
            $pageTitle = 'Danh sách Tour';
        }

        view('admin.tours.index', [
            'title' => $pageTitle,
            'pageTitle' => $pageTitle,
            'tours' => $tours,
            'message' => $_GET['message'] ?? null,
            'messageType' => $_GET['type'] ?? 'success',
        ]);
    }

    public function create(): void
    {
        // Chỉ admin mới được tạo tour
        requireAdmin();
        
        view('admin.tours.create', [
            'title' => 'Thêm tour mới',
            'pageTitle' => 'Thêm tour mới',
        ]);
    }

    public function store(): void
    {
        // Chỉ admin mới được tạo tour
        requireAdmin();
        
        $data = $this->validate($_POST);

        if ($data['errors']) {
            view('admin.tours.create', [
                'title' => 'Thêm tour mới',
                'pageTitle' => 'Thêm tour mới',
                'errors' => $data['errors'],
                'old' => $data['fields'],
            ]);
            return;
        }

        // Set images thành JSON rỗng để thỏa constraint JSON trong DB
        $data['fields']['images'] = json_encode([], JSON_UNESCAPED_UNICODE);

        Tour::create($data['fields']);
        $this->redirectWithMessage('tours', 'Thêm tour thành công');
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        $tour = $id ? Tour::find($id) : null;

        if (!$tour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        view('admin.tours.show', [
            'title' => 'Chi tiết tour',
            'pageTitle' => 'Chi tiết tour',
            'tour' => $tour,
        ]);
    }

    public function edit(): void
    {
        // Chỉ admin mới được sửa tour
        requireAdmin();
        
        $id = $_GET['id'] ?? null;
        $tour = $id ? Tour::find($id) : null;

        if (!$tour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        view('admin.tours.edit', [
            'title' => 'Chỉnh sửa tour',
            'pageTitle' => 'Chỉnh sửa tour',
            'tour' => $tour,
        ]);
    }

    public function update(): void
    {
        // Chỉ admin mới được sửa tour
        requireAdmin();
        
        $id = $_POST['id'] ?? null;
        $existingTour = $id ? Tour::find($id) : null;

        if (!$id || !$existingTour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        $data = $this->validate($_POST);

        if ($data['errors']) {
            view('admin.tours.edit', [
                'title' => 'Chỉnh sửa tour',
                'pageTitle' => 'Chỉnh sửa tour',
                'errors' => $data['errors'],
                'tour' => array_merge($data['fields'], ['id' => $id]),
            ]);
            return;
        }

        // Giữ nguyên ảnh cũ (không xử lý upload), nếu trống thì set JSON rỗng
        $data['fields']['images'] = $existingTour['images'] ?? json_encode([], JSON_UNESCAPED_UNICODE);

        Tour::update($id, $data['fields']);
        $this->redirectWithMessage('tours', 'Cập nhật tour thành công');
    }

    public function destroy(): void
    {
        // Chỉ admin mới được xóa tour
        requireAdmin();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        $tour = Tour::find($id);
        if (!$tour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        // Kiểm tra xem tour có bookings đang tham chiếu không
        if (Tour::hasBookings($id)) {
            $this->redirectWithMessage('tours', 'Không thể xóa tour này vì đang có đơn đặt tour liên quan. Vui lòng xóa các đơn đặt trước.', 'danger');
            return;
        }

        // Thử xóa tour
        if (Tour::delete($id)) {
            $this->redirectWithMessage('tours', 'Xóa tour thành công');
        } else {
            $this->redirectWithMessage('tours', 'Không thể xóa tour này. Có thể tour đang được sử dụng ở nơi khác.', 'danger');
        }
    }

    // Xem danh sách khách hàng của tour
    public function customers(): void
    {
        $id = $_GET['id'] ?? null;
        $tour = $id ? Tour::find($id) : null;

        if (!$tour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        // Lấy danh sách customers từ bảng customer
        $customers = Customer::getByTourId($id);

        view('admin.tours.customers', [
            'title' => 'Khách hàng - ' . htmlspecialchars($tour['name']),
            'pageTitle' => 'Khách hàng',
            'tour' => $tour,
            'customers' => $customers,
        ]);
    }

    // Viết nhật ký cho tour
    public function diary(): void
    {
        $id = $_GET['id'] ?? null;
        $tour = $id ? Tour::find($id) : null;

        if (!$tour) {
            $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
            return;
        }

        // Lấy booking đầu tiên của tour (mỗi tour chỉ có 1 nhật ký)
        $bookings = Booking::getByTourId($id);
        $booking = !empty($bookings) ? $bookings[0] : null;
        
        // Nếu có POST thì cập nhật nhật ký
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diary = trim($_POST['diary'] ?? '');

            if ($booking) {
                $bookingData = Booking::find($booking['id']);
                if ($bookingData) {
                    // Cập nhật nhật ký
                    $updateData = [
                        'tour_id' => $bookingData['tour_id'],
                        'created_by' => $bookingData['created_by'],
                        'assigned_guide_id' => $bookingData['assigned_guide_id'],
                        'status' => $bookingData['status'],
                        'start_date' => $bookingData['start_date'],
                        'end_date' => $bookingData['end_date'],
                        'schedule_detail' => $bookingData['schedule_detail'],
                        'service_detail' => $bookingData['service_detail'],
                        'diary' => $diary,
                        'lists_file' => $bookingData['lists_file'],
                        'notes' => $bookingData['notes'],
                    ];
                    
                    if (Booking::update($booking['id'], $updateData)) {
                        $this->redirectWithMessage('tour-diary&id=' . $id, 'Cập nhật nhật ký thành công');
                        return;
                    }
                }
            } else {
                // Nếu chưa có booking, tạo booking mặc định để lưu nhật ký
                $currentUser = getCurrentUser();
                $newBookingData = [
                    'tour_id' => $id,
                    'created_by' => $currentUser ? $currentUser->id : null,
                    'assigned_guide_id' => $currentUser && $currentUser->isGuide() ? $currentUser->id : null,
                    'status' => null,
                    'start_date' => null,
                    'end_date' => null,
                    'schedule_detail' => json_encode(new stdClass(), JSON_UNESCAPED_UNICODE),
                    'service_detail' => json_encode(new stdClass(), JSON_UNESCAPED_UNICODE),
                    'diary' => $diary,
                    'lists_file' => json_encode(new stdClass(), JSON_UNESCAPED_UNICODE),
                    'notes' => '',
                ];
                
                if (Booking::create($newBookingData)) {
                    $this->redirectWithMessage('tour-diary&id=' . $id, 'Tạo nhật ký thành công');
                    return;
                }
            }
        }

        view('admin.tours.diary', [
            'title' => 'Viết nhật ký - ' . htmlspecialchars($tour['name']),
            'pageTitle' => 'Viết nhật ký',
            'tour' => $tour,
            'booking' => $booking,
        ]);
    }

    private function validate(array $input): array
    {
        $fields = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'category_id' => trim($input['category_id'] ?? ''),
            // Các trường dưới đây nhập vào là TEXT thuần, nhưng trong DB vẫn lưu JSON để không vi phạm ràng buộc
            'schedule' => trim($input['schedule'] ?? ''),
            'prices' => trim($input['prices'] ?? ''),
            'policies' => trim($input['policies'] ?? ''),
            'suppliers' => trim($input['suppliers'] ?? ''),
            'price' => trim($input['price'] ?? ''),
            'status' => isset($input['status']) ? (int) $input['status'] : 1,
        ];

        // Chuyển các trường text thành JSON đơn giản dạng {"raw": "..."}
        // để tương thích với các ràng buộc JSON trong CSDL,
        // đồng thời view/form đã có logic bóc tách "raw" nên người dùng chỉ thấy TEXT.
        foreach (['schedule', 'prices', 'policies', 'suppliers'] as $jsonField) {
            $raw = $fields[$jsonField];
            if ($raw === '') {
                // Lưu object rỗng {} thay vì chuỗi rỗng để tránh lỗi JSON_TYPE
                $fields[$jsonField] = json_encode(new stdClass(), JSON_UNESCAPED_UNICODE);
            } else {
                $fields[$jsonField] = json_encode(['raw' => $raw], JSON_UNESCAPED_UNICODE);
            }
        }

        $errors = [];

        if ($fields['name'] === '') {
            $errors[] = 'Tên tour không được để trống';
        }

        if ($fields['price'] === '' || !is_numeric($fields['price'])) {
            $errors[] = 'Giá phải là số hợp lệ';
        }

        return [
            'fields' => $fields,
            'errors' => $errors,
        ];
    }

    private function redirectWithMessage(string $route, string $message, string $type = 'success'): void
    {
        // Không dùng str_contains để tương thích với các phiên bản PHP < 8
        $separator = (strpos($route, '?') !== false) ? '&' : '?';
        $url = BASE_URL . $route . $separator . 'message=' . urlencode($message) . '&type=' . $type;
        header('Location: ' . $url);
        exit;
    }
}

