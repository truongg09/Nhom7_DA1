<?php
// Đảm bảo hàm getDB() đã được load (thường là qua index.php hoặc autoloader)
// require_once __DIR__ . '/../helpers/database.php';

class TourModel
{

    /**
     * Lấy danh sách các tour được phân công cho một HDV
     * Use Case 2: Xem danh sách tour được phân công
     * Lấy thông qua cột assigned_guide_id trong bảng bookings
     */
    // F:\laragon\www\DA1\website_ql_tour\src\models\TourModel.php

    // F:\laragon\www\DA1\website_ql_tour\src\models\TourModel.php (Đã sửa và chính xác)

    public function getToursByTourGuide(int $hdv_id): array
    {
        $pdo = getDB();
        if (!$pdo)
            return [];

        $sql = "
        SELECT 
            t.id, 
            t.name, 
            b.start_date, 
            b.end_date, 
            b.status 
        FROM tours t
        INNER JOIN bookings b ON t.id = b.tour_id
        WHERE b.assigned_guide_id = :hdv_id 
        ORDER BY b.start_date DESC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hdv_id', $hdv_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Kiểm tra xem tour có được phân công cho HDV này không (Bảo mật)
     * Kiểm tra thông qua cột assigned_guide_id trong bảng bookings
     */
    public function isTourAssignedToHDV(int $hdv_id, int $tour_id): bool
    {
        $pdo = getDB();
        if (!$pdo)
            return false;

        $sql = "
            SELECT COUNT(*) 
            FROM bookings b -- ĐÃ SỬA: Chỉ cần truy vấn trực tiếp bảng bookings
            WHERE b.assigned_guide_id = :hdv_id -- Dùng cột assigned_guide_id trong bảng bookings
            AND b.tour_id = :tour_id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hdv_id', $hdv_id, PDO::PARAM_INT);
        $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Lấy danh sách TẤT CẢ các tour (hoặc các tour đang hoạt động) 
     * để hiển thị trong dropdown tạo booking.
     */


    /**
     * Lấy chi tiết thông tin của một tour
     * Use Case 3 & 5
     */
    public function getTourDetails(int $tour_id): ?array
    {
        $pdo = getDB();
        if (!$pdo)
            return null;

        $sql = "SELECT * FROM tours WHERE id = :tour_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
        $stmt->execute();

        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tour ?: null;
    }

    /**
     * Lấy danh sách khách hàng và trạng thái check-in của họ trong tour
     * Use Case 3: Xem danh sách khách hàng trong tour
     * Lưu ý: Giả định thông tin khách hàng được lưu trực tiếp trong bảng bookings
     */
    // F:\laragon\www\DA1\website_ql_tour\src\models\TourModel.php

    public function getCustomersInTour(int $tour_id): array
    {
        $pdo = getDB();
        if (!$pdo)
            return [];

        // Cập nhật: Thêm u.email AS customer_email
        $sql = "
        SELECT 
            b.id AS booking_id, 
            u.id AS user_id,
            u.name AS customer_name, 
            u.email AS customer_email, -- <--- CỘT THIẾU CẦN THÊM VÀO
           
            
            b.status AS booking_status, 
            b.checkin_status,
            b.checkin_time
        FROM bookings b
        -- Tham gia với bảng users thông qua cột 'created_by'
        INNER JOIN users u ON b.created_by = u.id 
        WHERE b.tour_id = :tour_id
        ORDER BY u.name ASC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Trong file TourModel.php
public function updateCheckinStatus(int $user_id, int $tour_id, int $status): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    
    // Nếu status là 1 (Check-in): Lấy thời gian hiện tại
    // Nếu status là 0 (Hủy): Đặt thời gian là NULL
    $checkin_time = ($status == 1) ? date('Y-m-d H:i:s') : NULL;

    $sql = "
        UPDATE bookings 
        SET 
            checkin_status = :status,
            checkin_time = :checkin_time 
        WHERE created_by = :user_id 
        AND tour_id = :tour_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':checkin_time', $checkin_time);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
    
    return $stmt->execute();
}
}