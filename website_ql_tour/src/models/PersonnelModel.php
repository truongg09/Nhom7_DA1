<?php

class PersonnelModel {
    
    /**
     * Lấy danh sách tất cả nhân sự (hướng dẫn viên và tài xế)
     */
        public function getAvailablePersonnel(): array {
        $pdo = getDB();
        if (!$pdo) return [];

        $sql = "
            SELECT 
                gp.id AS guide_profile_id,
                u.id AS user_id,
                u.name,
                u.role,
                gp.phone
            FROM guide_profiles gp
            JOIN users u ON gp.user_id = u.id
            WHERE u.role IN ('huong_dan_vien', 'driver', 'tai_xe')
            AND u.status = 1
            ORDER BY u.name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        
    /**
     * Lấy danh sách hướng dẫn viên
     */
    public function getGuides(): array {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $sql = "
            SELECT 
                id,
                name,
                email,
                phone,
                status
            FROM users
            WHERE role = 'huong_dan_vien'
            AND status = 1
            ORDER BY name ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy danh sách tài xế
     */
    public function getDrivers(): array {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $sql = "
            SELECT 
                id,
                name,
                email,
                phone,
                status
            FROM users
            WHERE role IN ('driver', 'tai_xe')
            AND status = 1
            ORDER BY name ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật phân bổ nhân sự cho booking
     */
    public function updateAllocation(array $data): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        $booking_id = $data['booking_id'] ?? null;
        $guide_id = $data['guide_id'] ?? null;
        $driver_id = $data['driver_id'] ?? null;
        
        if (!$booking_id) return false;
        
        // Kiểm tra xem đã có phân bổ chưa
        $checkSql = "SELECT id FROM booking_personnel WHERE booking_id = :booking_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Cập nhật phân bổ hiện có
            $sql = "
                UPDATE booking_personnel 
                SET guide_id = :guide_id,
                    driver_id = :driver_id,
                    updated_at = NOW()
                WHERE booking_id = :booking_id
            ";
        } else {
            // Tạo phân bổ mới
            $sql = "
                INSERT INTO booking_personnel (booking_id, guide_id, driver_id, created_at, updated_at)
                VALUES (:booking_id, :guide_id, :driver_id, NOW(), NOW())
            ";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindParam(':guide_id', $guide_id, PDO::PARAM_INT);
        $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy thông tin phân bổ nhân sự của một booking
     */
    public function getAllocationByBookingId(int $booking_id): ?array {
        $pdo = getDB();
        if (!$pdo) return null;
        
        $sql = "
            SELECT 
                bp.*,
                g.name as guide_name,
                g.phone as guide_phone,
                d.name as driver_name,
                d.phone as driver_phone
            FROM booking_personnel bp
            LEFT JOIN users g ON bp.guide_id = g.id
            LEFT JOIN users d ON bp.driver_id = d.id
            WHERE bp.booking_id = :booking_id
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}


