# Hướng dẫn tạo bảng booking_personnel

## Vấn đề
Bảng `booking_personnel` chưa tồn tại trong database, nhưng hệ thống cần bảng này để:
- Lưu thông tin phân bổ hướng dẫn viên (HDV) cho các booking
- Cho phép HDV xem danh sách tour được phân công
- Kiểm tra quyền truy cập tour của HDV

## Cách tạo bảng

### Cách 1: Sử dụng phpMyAdmin (Khuyến nghị)

1. Mở phpMyAdmin trong trình duyệt (thường là: `http://localhost/phpmyadmin`)

2. Chọn database `website_ql_tour` ở cột bên trái

3. Click vào tab **SQL** ở phía trên

4. Copy và paste nội dung sau vào ô SQL:

```sql
CREATE TABLE IF NOT EXISTS `booking_personnel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) NOT NULL COMMENT 'ID của booking',
  `guide_id` bigint(20) DEFAULT NULL COMMENT 'ID của hướng dẫn viên (từ bảng users)',
  `driver_id` bigint(20) DEFAULT NULL COMMENT 'ID của tài xế (từ bảng users)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking` (`booking_id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_driver_id` (`driver_id`),
  KEY `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

5. Click nút **Go** (hoặc **Thực thi**) để chạy lệnh SQL

6. Nếu thành công, bạn sẽ thấy thông báo "Table has been created" và bảng `booking_personnel` sẽ xuất hiện trong danh sách bảng

### Cách 2: Import từ file SQL

1. Mở phpMyAdmin
2. Chọn database `website_ql_tour`
3. Click tab **Import**
4. Click nút **Choose File** và chọn file `create_booking_personnel_table.sql`
5. Click **Go** để import

## Kiểm tra

Sau khi tạo bảng, bạn có thể kiểm tra bằng cách:

1. Trong phpMyAdmin, click vào bảng `booking_personnel` trong danh sách bảng
2. Xem cấu trúc bảng để đảm bảo các cột đã được tạo đúng

## Các bảng cần thiết khác

Ngoài `booking_personnel`, hệ thống còn cần các bảng sau (nếu chưa có):

- `checkin_status` - Lưu trạng thái check-in của khách hàng
- `tour_diaries` - Lưu nhật ký tour và yêu cầu đặc biệt

Bạn có thể tạo các bảng này bằng cách chạy file `database_schema.sql` trong phpMyAdmin.

## Lưu ý

- Đảm bảo database `website_ql_tour` đã được chọn trước khi chạy SQL
- Nếu bảng đã tồn tại, lệnh `CREATE TABLE IF NOT EXISTS` sẽ không gây lỗi
- Sau khi tạo bảng, bạn cần phân bổ HDV cho các booking thông qua chức năng "Phân bổ nhân sự" trong quản lý booking

