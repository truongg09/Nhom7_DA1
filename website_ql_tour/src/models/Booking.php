<?php

class Booking {
    
    /**
     * Tạo booking mới
     */
    public function createBooking(array $data): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        $sql = "
            INSERT INTO bookings (
                tour_id, 
               
              
                start_date,
                status,
                notes,
                created_at
            ) VALUES (
                :tour_id,
               
             
                :start_date,
                :status,
                :notes,
                NOW()
            )
        ";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':tour_id' => $data['tour_id'] ?? null,
       
            ':start_date' => $data['start_date'] ?? null,
            ':status' => $data['status'] ?? 'Pending',
            ':notes' => $data['notes'] ?? '',
        ]);
    }
    
    /**
     * Lấy tất cả bookings
     */
    public function getAllBookings(): array {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $sql = "
            SELECT 
                b.*,
                t.name as tour_name,

                t.price
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            ORDER BY b.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy booking theo ID
     */
    public function getBookingById(int $booking_id): ?array {
        $pdo = getDB();
        if (!$pdo) return null;
        
        $sql = "
            SELECT 
                b.*,
                t.name as tour_name,
                
                t.price,
                t.description
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            WHERE b.id = :booking_id
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Cập nhật trạng thái booking
     */
    public function updateStatus(int $booking_id, string $status): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        $sql = "UPDATE bookings SET status = :status WHERE id = :booking_id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':booking_id' => $booking_id,
        ]);
    }
    
    /**
     * Cập nhật thông tin booking
     */
    public function updateBooking(int $booking_id, array $data): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        $sql = "
            UPDATE bookings SET
                tour_id = :tour_id,
               
                start_date = :start_date,
                status = :status,
                notes = :notes
            WHERE id = :booking_id
        ";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':booking_id' => $booking_id,
            ':tour_id' => $data['tour_id'] ?? null,
           
            ':start_date' => $data['start_date'] ?? null,
            ':status' => $data['status'] ?? 'Pending',
            ':notes' => $data['notes'] ?? '',
        ]);
    }
    
    /**
     * Xóa booking
     */
    public function deleteBooking(int $booking_id): bool {
        $pdo = getDB();
        if (!$pdo) return false;
        
        $sql = "DELETE FROM bookings WHERE id = :booking_id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':booking_id' => $booking_id]);
    }
    
    /**
     * Lấy danh sách tours để chọn khi tạo booking
     */
    public function getAvailableTours(): array {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $sql = "
            SELECT id, name, start_date,  price
            FROM tours
            WHERE start_date >= CURDATE()
            ORDER BY start_date ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy bookings theo trạng thái
     */
    public function getBookingsByStatus(string $status): array {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $sql = "
            SELECT 
                b.*,
                t.name as tour_name,
                t.duration,
                t.price
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            WHERE b.status = :status
            ORDER BY b.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


