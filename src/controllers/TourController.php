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
        if (!$id || !Tour::find($id)) {
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

        Tour::update($id, $data['fields']);
        $this->redirectWithMessage('tours', 'Cập nhật tour thành công');
    }

    public function destroy(): void
    {
        $id = $_POST['id'] ?? null;
        if ($id && Tour::find($id)) {
            Tour::delete($id);
            $this->redirectWithMessage('tours', 'Xóa tour thành công');
            return;
        }

        $this->redirectWithMessage('tours', 'Tour không tồn tại', 'danger');
    }

    private function validate(array $input): array
    {
        $fields = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'category_id' => trim($input['category_id'] ?? ''),
            'schedule' => trim($input['schedule'] ?? ''),
            'images' => trim($input['images'] ?? ''),
            'prices' => trim($input['prices'] ?? ''),
            'policies' => trim($input['policies'] ?? ''),
            'suppliers' => trim($input['suppliers'] ?? ''),
            'price' => trim($input['price'] ?? ''),
            'status' => isset($input['status']) ? (int) $input['status'] : 1,
        ];

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
        $separator = str_contains($route, '?') ? '&' : '?';
        $url = BASE_URL . $route . $separator . 'message=' . urlencode($message) . '&type=' . $type;
        header('Location: ' . $url);
        exit;
    }
}

