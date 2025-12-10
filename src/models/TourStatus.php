<?php

class TourStatus
{
    public static function all(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query('SELECT * FROM tour_statuses ORDER BY id ASC');
        return $stmt->fetchAll() ?: [];
    }

    public static function find($id): ?array
    {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM tour_statuses WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $status = $stmt->fetch();
        return $status ?: null;
    }

    public static function getName($id): string
    {
        $status = self::find($id);
        return $status ? ($status['name'] ?? '') : '';
    }
}
