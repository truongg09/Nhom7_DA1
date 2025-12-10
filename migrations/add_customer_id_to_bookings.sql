-- Thêm cột customer_id vào bảng bookings
ALTER TABLE bookings 
ADD COLUMN customer_id INT(11) NULL AFTER tour_id;

-- Thêm khóa phụ (foreign key) với bảng customer
ALTER TABLE bookings 
ADD CONSTRAINT fk_bookings_customer 
FOREIGN KEY (customer_id) REFERENCES customer(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

