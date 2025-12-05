<?php

class Booking
{
    public static function all(): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query(
            'SELECT b.*, t.name AS tour_name, u1.name AS created_by_name, u2.name AS assigned_guide_name
             FROM bookings b
             LEFT JOIN tours t ON b.tour_id = t.id
             LEFT JOIN users u1 ON b.created_by = u1.id
             LEFT JOIN users u2 ON b.assigned_guide_id = u2.id
             ORDER BY b.created_at DESC'
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
            'SELECT b.*, t.name AS tour_name, u1.name AS created_by_name, u2.name AS assigned_guide_name
             FROM bookings b
             LEFT JOIN tours t ON b.tour_id = t.id
             LEFT JOIN users u1 ON b.created_by = u1.id
             LEFT JOIN users u2 ON b.assigned_guide_id = u2.id
             WHERE b.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'INSERT INTO bookings (tour_id, created_by, assigned_guide_id, status, start_date, end_date, schedule_detail, service_detail, diary, lists_file, notes)
                VALUES (:tour_id, :created_by, :assigned_guide_id, :status, :start_date, :end_date, :schedule_detail, :service_detail, :diary, :lists_file, :notes)';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'tour_id' => $data['tour_id'] ?: null,
            'created_by' => $data['created_by'] ?: null,
            'assigned_guide_id' => $data['assigned_guide_id'] ?: null,
            'status' => $data['status'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'schedule_detail' => $data['schedule_detail'] ?: null,
            'service_detail' => $data['service_detail'] ?: null,
            'diary' => $data['diary'] ?: null,
            'lists_file' => $data['lists_file'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);
    }

    public static function update($id, array $data): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $sql = 'UPDATE bookings SET
                    tour_id = :tour_id,
                    created_by = :created_by,
                    assigned_guide_id = :assigned_guide_id,
                    status = :status,
                    start_date = :start_date,
                    end_date = :end_date,
                    schedule_detail = :schedule_detail,
                    service_detail = :service_detail,
                    diary = :diary,
                    lists_file = :lists_file,
                    notes = :notes,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'tour_id' => $data['tour_id'] ?: null,
            'created_by' => $data['created_by'] ?: null,
            'assigned_guide_id' => $data['assigned_guide_id'] ?: null,
            'status' => $data['status'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'schedule_detail' => $data['schedule_detail'] ?: null,
            'service_detail' => $data['service_detail'] ?: null,
            'diary' => $data['diary'] ?: null,
            'lists_file' => $data['lists_file'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);
    }

    public static function delete($id): bool
    {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return false;
            }
            throw $e;
        }
    }

    public static function getStatusLogs($bookingId): array
    {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->prepare(
            'SELECT bsl.*, u.name AS changed_by_name
             FROM booking_status_logs bsl
             LEFT JOIN users u ON bsl.changed_by = u.id
             WHERE bsl.booking_id = :booking_id
             ORDER BY bsl.changed_at DESC'
        );
        $stmt->execute(['booking_id' => $bookingId]);
        return $stmt->fetchAll() ?: [];
    }
}
