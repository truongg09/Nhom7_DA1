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

        // Cách 1: Nếu customer có tour_id trực tiếp (cách đơn giản nhất)
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
            // Nếu không có cột tour_id, thử cách khác
        }

        // Cách 2: Nếu bookings có customer_id
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

        // Cách 3: Nếu có bảng trung gian booking_customers
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
            // Bỏ qua lỗi
        }

        // Nếu không tìm thấy, trả về mảng rỗng
        return [];
    }

    public static function create(array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'INSERT INTO customer (name, phone, email)
                VALUES (:name, :phone, :email)';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'] ?? '',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public static function update($id, array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'UPDATE customer SET
                    name = :name,
                    phone = :phone,
                    email = :email
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'] ?? '',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public static function delete($id): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM customer WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // Nếu có foreign key constraint, không thể xóa
            if ($e->getCode() == '23000') {
                return false;
            }
            throw $e;
        }
    }
}

