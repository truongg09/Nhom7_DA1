<?php
class TourDiaryModel {

    /**
     * Lấy nhật ký tour hiện có từ bảng bookings
     */
    public function getDiaryByTour(int $tour_id): ?array {
        $pdo = getDB();
        if (!$pdo) return null;

        $sql = "SELECT diary, notes 
                FROM bookings 
                WHERE tour_id = :tour_id 
                LIMIT 1";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Tour Diary get failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lưu nhật ký tour vào bảng bookings
     */
    public function saveDiary(array $data): bool {
        $pdo = getDB();
        if (!$pdo) return false;

        $sql = "
            UPDATE bookings
            SET diary = :content,
                notes = :special_request,
                updated_at = NOW()
            WHERE tour_id = :tour_id
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tour_id', $data['tour_id'], PDO::PARAM_INT);
            $stmt->bindParam(':content', $data['content']);
            $stmt->bindParam(':special_request', $data['special_request']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Tour Diary save failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
