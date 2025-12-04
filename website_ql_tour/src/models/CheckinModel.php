<?php
// Đảm bảo hàm getDB() đã được load

class CheckinModel {

    /**
     * Cập nhật trạng thái check-in của một khách hàng trong tour
     * Use Case 1: Nhập check-in từng phần
     */
    public function updateCheckinStatus(int $tour_id, int $customer_id, int $status, int $hdv_id): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        // Trạng thái: 1 = Đã check-in, 0 = Chưa check-in
        $checkin_time = ($status == 1) ? date('Y-m-d H:i:s') : null;
        
        $sql = "
            INSERT INTO checkin_status (tour_id, customer_id, hdv_id, status, checkin_time)
            VALUES (:tour_id, :customer_id, :hdv_id, :status, :checkin_time)
            ON DUPLICATE KEY UPDATE 
                hdv_id = VALUES(hdv_id), 
                status = VALUES(status), 
                checkin_time = VALUES(checkin_time)
        ";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':hdv_id', $hdv_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':checkin_time', $checkin_time);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Checkin update failed: ' . $e->getMessage());
            return false;
        }
    }
}