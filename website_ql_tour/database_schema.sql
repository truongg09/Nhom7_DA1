-- File SQL để tạo các bảng cần thiết cho hệ thống quản lý tour
-- Chạy file này trong database 'website_ql_tour' nếu các bảng chưa tồn tại

-- Bảng checkin_status để lưu trạng thái check-in của khách hàng trong tour
CREATE TABLE IF NOT EXISTS `checkin_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT 'Có thể là booking_id hoặc customer_id tùy cấu trúc',
  `hdv_id` int(11) NOT NULL COMMENT 'ID của hướng dẫn viên thực hiện check-in',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Chưa check-in, 1: Đã check-in',
  `checkin_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_checkin` (`tour_id`, `customer_id`),
  KEY `idx_tour_id` (`tour_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_hdv_id` (`hdv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng tour_diaries để lưu nhật ký tour và yêu cầu đặc biệt
CREATE TABLE IF NOT EXISTS `tour_diaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `hdv_id` int(11) NOT NULL COMMENT 'ID của hướng dẫn viên viết nhật ký',
  `content` text DEFAULT NULL COMMENT 'Nội dung nhật ký tour',
  `special_request` text DEFAULT NULL COMMENT 'Yêu cầu đặc biệt gửi về quản lý',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tour_hdv` (`tour_id`, `hdv_id`),
  KEY `idx_tour_id` (`tour_id`),
  KEY `idx_hdv_id` (`hdv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng booking_personnel để lưu phân bổ nhân sự cho booking
CREATE TABLE IF NOT EXISTS `booking_personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `guide_id` int(11) DEFAULT NULL COMMENT 'ID của hướng dẫn viên',
  `driver_id` int(11) DEFAULT NULL COMMENT 'ID của tài xế',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking` (`booking_id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_driver_id` (`driver_id`),
  KEY `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

