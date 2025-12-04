<?php
// Đảm bảo hàm getDB() đã được load (thường là qua index.php hoặc autoloader)
// require_once __DIR__ . '/../helpers/database.php';

class TourModel
{

    /**
     * Lấy danh sách các tour được phân công cho một HDV
     * Use Case 2: Xem danh sách tour được phân công
     * Lấy thông qua booking_personnel: tìm các booking có guide_id = hdv_id, sau đó lấy các tour tương ứng
     */
    public function getToursByTourGuide(int $hdv_id): array
    {
        $pdo = getDB();
        if (!$pdo)
            return [];

        $sql = "
            SELECT DISTINCT t.* 
            FROM tours t
            INNER JOIN bookings b ON t.id = b.tour_id
            INNER JOIN booking_personnel bp ON b.id = bp.booking_id
            WHERE bp.guide_id = :hdv_id
            ORDER BY t.start_date DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hdv_id', $hdv_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xem tour có được phân công cho HDV này không (Bảo mật)
     * Kiểm tra thông qua booking_personnel: xem có booking nào thuộc tour này và được phân cho HDV này không
     */
    public function isTourAssignedToHDV(int $hdv_id, int $tour_id): bool
    {
        $pdo = getDB();
        if (!$pdo)
            return false;

        $sql = "
            SELECT COUNT(*) 
            FROM booking_personnel bp
            INNER JOIN bookings b ON bp.booking_id = b.id
            WHERE bp.guide_id = :hdv_id 
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
    public function getAvailableTours(): array
    {
        $pdo = getDB();
        if (!$pdo)
            return [];

        // Tùy chọn: Bạn có thể thêm WHERE status = 'Active' nếu bạn có cột trạng thái
        $sql = "
        SELECT id, name, start_date 
        FROM tours
        ORDER BY start_date DESC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
     * Lấy từ bookings (thông tin khách hàng lưu trực tiếp trong bookings)
     */
    public function getCustomersInTour(int $tour_id): array
    {
        $pdo = getDB();
        if (!$pdo)
            return [];

        // Sử dụng booking_id như customer_id tạm thời (hoặc có thể có bảng customers riêng)
        // Nếu có bảng customers, cần JOIN qua customer_id
        // Ở đây giả định thông tin khách hàng lưu trong bookings
        $sql = "
            SELECT 
                b.id as customer_id, 
                b.customer_name as name, 
                b.customer_phone as phone,
                COALESCE(cs.status, 0) as checkin_status,
                cs.checkin_time
            FROM bookings b
            LEFT JOIN checkin_status cs ON b.id = cs.customer_id AND cs.tour_id = :tour_id
            WHERE b.tour_id = :tour_id_2
            ORDER BY b.customer_name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tour_id', $tour_id, PDO::PARAM_INT);
        $stmt->bindParam(':tour_id_2', $tour_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}