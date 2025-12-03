<?php

class Category
{
    public static function all(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
        return $stmt->fetchAll() ?: [];
    }

    public static function find($id): ?array
    {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'INSERT INTO categories (name, description, status)
                VALUES (:name, :description, :status)';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);
    }

    public static function update($id, array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'UPDATE categories SET
                    name = :name,
                    description = :description,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);
    }

    public static function delete($id): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}


