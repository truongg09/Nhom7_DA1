<?php

class Customer
{
    public static function all(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query('SELECT * FROM customer ORDER BY id ASC');
        return $stmt->fetchAll() ?: [];
    }

    public static function find($id): ?array
    {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM customer WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $customer = $stmt->fetch();
        return $customer ?: null;
    }

    /**
     * Lấy danh sách customers theo tour_id
     * Kiểm tra nhiều cách liên kết: bookings.customer_id, booking_customers, hoặc customer.tour_id
     */
    public static function getByTourId($tourId): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        // Cách 1: Nếu bookings có customer_id
        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT c.*
                 FROM customer c
                 INNER JOIN bookings b ON b.customer_id = c.id
                 WHERE b.tour_id = :tour_id
                 ORDER BY c.id ASC'
            );
            $stmt->execute(['tour_id' => $tourId]);
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                return $result;
            }
        } catch (PDOException $e) {
            // Bỏ qua lỗi và thử cách khác
        }

        // Cách 2: Nếu có bảng trung gian booking_customers
        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT c.*
                 FROM customer c
                 INNER JOIN booking_customers bc ON bc.customer_id = c.id
                 INNER JOIN bookings b ON b.id = bc.booking_id
                 WHERE b.tour_id = :tour_id
                 ORDER BY c.id ASC'
            );
            $stmt->execute(['tour_id' => $tourId]);
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                return $result;
            }
        } catch (PDOException $e) {
            // Bỏ qua lỗi và thử cách khác
        }

        // Cách 3: Nếu customer có tour_id trực tiếp
        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT c.*
                 FROM customer c
                 WHERE c.tour_id = :tour_id
                 ORDER BY c.id ASC'
            );
            $stmt->execute(['tour_id' => $tourId]);
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                return $result;
            }
        } catch (PDOException $e) {
            // Bỏ qua lỗi
        }

        // Nếu không có cách nào hoạt động, trả về tất cả customers (tạm thời để debug)
        // TODO: Cần xác định cách liên kết chính xác giữa customer và tour
        return [];
    }
}

