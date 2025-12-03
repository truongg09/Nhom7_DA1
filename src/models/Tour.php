<?php

class Tour
{
    public static function all(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query(
            'SELECT t.*, c.name AS category_name
             FROM tours t
             LEFT JOIN categories c ON t.category_id = c.id
             ORDER BY t.created_at DESC'
        );
        return $stmt->fetchAll() ?: [];
    }

    public static function find($id): ?array
    {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare(
            'SELECT t.*, c.name AS category_name
             FROM tours t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $tour = $stmt->fetch();
        return $tour ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'INSERT INTO tours (name, description, category_id, schedule, images, prices, policies, suppliers, price, status)
                VALUES (:name, :description, :category_id, :schedule, :images, :prices, :policies, :suppliers, :price, :status)';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'schedule' => $data['schedule'],
            'images' => $data['images'],
            'prices' => $data['prices'],
            'policies' => $data['policies'],
            'suppliers' => $data['suppliers'],
            'price' => $data['price'],
            'status' => $data['status'],
        ]);
    }

    public static function update($id, array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'UPDATE tours SET
                    name = :name,
                    description = :description,
                    category_id = :category_id,
                    schedule = :schedule,
                    images = :images,
                    prices = :prices,
                    policies = :policies,
                    suppliers = :suppliers,
                    price = :price,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'schedule' => $data['schedule'],
            'images' => $data['images'],
            'prices' => $data['prices'],
            'policies' => $data['policies'],
            'suppliers' => $data['suppliers'],
            'price' => $data['price'],
            'status' => $data['status'],
        ]);
    }

    public static function delete($id): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('DELETE FROM tours WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

