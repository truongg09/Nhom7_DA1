<?php

class CategoryController
{
    public function __construct()
    {
        requireGuideOrAdmin();
    }

    public function index(): void
    {
        $categories = Category::all();

        view('admin.categories.index', [
            'title' => 'Danh mục Tour',
            'pageTitle' => 'Quản lý danh mục Tour',
            'categories' => $categories,
            'message' => $_GET['message'] ?? null,
            'messageType' => $_GET['type'] ?? 'success',
        ]);
    }

    public function create(): void
    {
        view('admin.categories.create', [
            'title' => 'Thêm danh mục tour',
            'pageTitle' => 'Thêm danh mục tour',
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST);

        if ($data['errors']) {
            view('admin.categories.create', [
                'title' => 'Thêm danh mục tour',
                'pageTitle' => 'Thêm danh mục tour',
                'errors' => $data['errors'],
                'old' => $data['fields'],
            ]);
            return;
        }

        Category::create($data['fields']);
        $this->redirectWithMessage('categories', 'Thêm danh mục thành công');
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        $category = $id ? Category::find($id) : null;

        if (!$category) {
            $this->redirectWithMessage('categories', 'Danh mục không tồn tại', 'danger');
            return;
        }

        view('admin.categories.show', [
            'title' => 'Chi tiết danh mục',
            'pageTitle' => 'Chi tiết danh mục tour',
            'category' => $category,
        ]);
    }

    public function edit(): void
    {
        $id = $_GET['id'] ?? null;
        $category = $id ? Category::find($id) : null;

        if (!$category) {
            $this->redirectWithMessage('categories', 'Danh mục không tồn tại', 'danger');
            return;
        }

        view('admin.categories.edit', [
            'title' => 'Chỉnh sửa danh mục',
            'pageTitle' => 'Chỉnh sửa danh mục tour',
            'category' => $category,
        ]);
    }

    public function update(): void
    {
        $id = $_POST['id'] ?? null;
        if (!$id || !Category::find($id)) {
            $this->redirectWithMessage('categories', 'Danh mục không tồn tại', 'danger');
            return;
        }

        $data = $this->validate($_POST);

        if ($data['errors']) {
            view('admin.categories.edit', [
                'title' => 'Chỉnh sửa danh mục',
                'pageTitle' => 'Chỉnh sửa danh mục tour',
                'errors' => $data['errors'],
                'category' => array_merge($data['fields'], ['id' => $id]),
            ]);
            return;
        }

        Category::update($id, $data['fields']);
        $this->redirectWithMessage('categories', 'Cập nhật danh mục thành công');
    }

    public function destroy(): void
    {
        if (!isAdmin()) {
            $this->redirectWithMessage('categories', 'Bạn không có quyền xóa danh mục', 'danger');
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id && Category::find($id)) {
            Category::delete($id);
            $this->redirectWithMessage('categories', 'Xóa danh mục thành công');
            return;
        }

        $this->redirectWithMessage('categories', 'Danh mục không tồn tại', 'danger');
    }

    private function validate(array $input): array
    {
        $fields = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'status' => isset($input['status']) ? (int) $input['status'] : 1,
        ];

        $errors = [];

        if ($fields['name'] === '') {
            $errors[] = 'Tên danh mục không được để trống';
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


