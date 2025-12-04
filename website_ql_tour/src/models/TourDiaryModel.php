<?php
// Đảm bảo hàm getDB() đã được load

class TourDiaryModel {

    /**
     * Lấy nhật ký tour hiện có
     * Use Case 5: Cập nhật nhật ký tour
     */
    public function getDiaryByTour(int $tour_id): ?array {
        $pdo = getDB();
        if (!$pdo) return null;
        
        $sql = "SELECT * FROM tour_diaries WHERE tour_id = :tour_id LIMIT 1";
        
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
     * Lưu (hoặc cập nhật) nhật ký tour và yêu cầu đặc biệt
     * Use Case 5: Cập nhật nhật ký tour
     * Use Case 4: Yêu cầu đặc biệt
     */
    public function saveDiary(array $data): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        // Sử dụng ON DUPLICATE KEY UPDATE để nếu đã có nhật ký cho tour đó thì cập nhật
        $sql = "
            INSERT INTO tour_diaries (tour_id, hdv_id, content, special_request, created_at)
            VALUES (:tour_id, :hdv_id, :content, :special_request, NOW())
            ON DUPLICATE KEY UPDATE
                content = VALUES(content),
                special_request = VALUES(special_request),
                created_at = VALUES(created_at)
        ";
        
        try {
            $stmt = $pdo->prepare($sql);
            // Dữ liệu từ controller: ['tour_id', 'hdv_id', 'content', 'special_request']
            $stmt->bindParam(':tour_id', $data['tour_id'], PDO::PARAM_INT);
            $stmt->bindParam(':hdv_id', $data['hdv_id'], PDO::PARAM_INT);
            $stmt->bindParam(':content', $data['content']);
            $stmt->bindParam(':special_request', $data['special_request']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Tour Diary save failed: ' . $e->getMessage());
            return false;
        }
    }
}