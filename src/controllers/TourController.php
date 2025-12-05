<?php

class TourController
{
    public function __construct()
    {
        requireGuideOrAdmin();
    }

    public function index(): void
    {
        $tours = Tour::all();

        view('admin.tours.index', [
            'title' => 'Danh sách Tour',
            'pageTitle' => 'Danh sách Tour',
            'tours' => $tours,
            'message' => $_GET['message'] ?? null,
            'messageType' => $_GET['type'] ?? 'success',
        ]);
    }

    public function create(): void
    {
        view('admin.tours.create', [
            'title' => 'Thêm tour mới',
            'pageTitle' => 'Thêm tour mới',
        ]);
    }

    public function store(): void
    {
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

        // Set images thành empty string
        $data['fields']['images'] = '';

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

        // Giữ nguyên ảnh cũ (không xử lý upload nữa)
        $data['fields']['images'] = $existingTour['images'] ?? '';

        Tour::update($id, $data['fields']);
        $this->redirectWithMessage('tours', 'Cập nhật tour thành công');
    }

    public function destroy(): void
    {
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

