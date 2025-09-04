-- Database schema for Jeepney Tracking System
-- Run this script to create necessary tables for tracking functionality

-- Table for storing jeepney information
CREATE TABLE IF NOT EXISTS jeepney_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jeepney_id VARCHAR(50) UNIQUE NOT NULL,
    driver_id INT NOT NULL,
    route VARCHAR(255) NOT NULL,
    capacity INT DEFAULT 14,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES user_tbl(id) ON DELETE CASCADE
);

-- Table for storing real-time location data
CREATE TABLE IF NOT EXISTS location_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jeepney_id VARCHAR(50) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    speed DECIMAL(5, 2) DEFAULT 0.00,
    heading DECIMAL(5, 2) DEFAULT 0.00,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jeepney_id) REFERENCES jeepney_tbl(jeepney_id) ON DELETE CASCADE
);

-- Table for passenger count tracking
CREATE TABLE IF NOT EXISTS passenger_count_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jeepney_id VARCHAR(50) NOT NULL,
    passenger_count INT DEFAULT 0,
    available_seats INT DEFAULT 14,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jeepney_id) REFERENCES jeepney_tbl(jeepney_id) ON DELETE CASCADE
);

-- Table for trip records
CREATE TABLE IF NOT EXISTS trip_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jeepney_id VARCHAR(50) NOT NULL,
    driver_id INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    start_location VARCHAR(255),
    end_location VARCHAR(255),
    total_passengers INT DEFAULT 0,
    distance_km DECIMAL(8, 2) DEFAULT 0.00,
    revenue DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (jeepney_id) REFERENCES jeepney_tbl(jeepney_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES user_tbl(id) ON DELETE CASCADE
);

-- Table for traffic alerts and updates
CREATE TABLE IF NOT EXISTS traffic_alert_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    alert_type ENUM('heavy', 'moderate', 'light', 'construction', 'accident') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    description TEXT,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES user_tbl(id) ON DELETE SET NULL
);

-- Table for system logs
CREATE TABLE IF NOT EXISTS system_log_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_tbl(id) ON DELETE SET NULL
);

-- Table for route definitions
CREATE TABLE IF NOT EXISTS route_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(255) NOT NULL,
    start_point VARCHAR(255) NOT NULL,
    end_point VARCHAR(255) NOT NULL,
    waypoints TEXT, -- JSON format for intermediate stops
    distance_km DECIMAL(8, 2) DEFAULT 0.00,
    estimated_duration_minutes INT DEFAULT 0,
    fare DECIMAL(6, 2) DEFAULT 12.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for seat reservations (optional feature)
CREATE TABLE IF NOT EXISTS seat_reservation_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jeepney_id VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    seat_number INT NOT NULL,
    reservation_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_time TIMESTAMP NOT NULL,
    status ENUM('active', 'expired', 'used', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (jeepney_id) REFERENCES jeepney_tbl(jeepney_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user_tbl(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_reservation (jeepney_id, seat_number, status)
);

-- Insert sample routes
INSERT IGNORE INTO route_tbl (route_name, start_point, end_point, distance_km, estimated_duration_minutes, fare) VALUES
('Divisoria-Mabolo', 'Divisoria Terminal', 'Mabolo Terminal', 8.5, 35, 12.00),
('Lahug-Colon', 'Lahug Terminal', 'Colon Street', 6.2, 28, 12.00),
('Talamban-IT Park', 'Talamban Terminal', 'IT Park', 10.1, 42, 15.00),
('Ayala-SM', 'Ayala Center', 'SM City Cebu', 4.8, 22, 12.00);

-- Insert sample jeepneys (you can modify these after running the script)
INSERT IGNORE INTO jeepney_tbl (jeepney_id, driver_id, route, capacity, status) VALUES
('JP-001', 1, 'Divisoria-Mabolo', 14, 'offline'),
('JP-002', 1, 'Lahug-Colon', 14, 'offline'),
('JP-003', 1, 'Talamban-IT Park', 16, 'offline'),
('JP-004', 1, 'Ayala-SM', 14, 'offline'),
('JP-005', 1, 'Divisoria-Mabolo', 14, 'offline');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_location_jeepney_time ON location_tbl(jeepney_id, timestamp);
CREATE INDEX IF NOT EXISTS idx_passenger_jeepney_time ON passenger_count_tbl(jeepney_id, timestamp);
CREATE INDEX IF NOT EXISTS idx_trip_status ON trip_tbl(status);
CREATE INDEX IF NOT EXISTS idx_traffic_active ON traffic_alert_tbl(is_active, start_time);
CREATE INDEX IF NOT EXISTS idx_system_log_time ON system_log_tbl(timestamp);
CREATE INDEX IF NOT EXISTS idx_seat_reservation_status ON seat_reservation_tbl(status, expiry_time);

-- Add additional columns to user_tbl if they don't exist
ALTER TABLE user_tbl ADD COLUMN IF NOT EXISTS jeepney_id VARCHAR(50) NULL AFTER usert_type;
ALTER TABLE user_tbl ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER jeepney_id;
ALTER TABLE user_tbl ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER phone;
ALTER TABLE user_tbl ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL AFTER status;

-- Create a view for active jeepneys with driver information
CREATE OR REPLACE VIEW active_jeepneys_view AS
SELECT 
    j.jeepney_id,
    j.route,
    j.capacity,
    j.status,
    u.firstname,
    u.lastname,
    u.username,
    u.phone,
    l.latitude,
    l.longitude,
    l.speed,
    l.timestamp as last_location_update,
    pc.passenger_count,
    pc.available_seats
FROM jeepney_tbl j
LEFT JOIN user_tbl u ON j.driver_id = u.id
LEFT JOIN (
    SELECT jeepney_id, latitude, longitude, speed, timestamp,
           ROW_NUMBER() OVER (PARTITION BY jeepney_id ORDER BY timestamp DESC) as rn
    FROM location_tbl
) l ON j.jeepney_id = l.jeepney_id AND l.rn = 1
LEFT JOIN (
    SELECT jeepney_id, passenger_count, available_seats, timestamp,
           ROW_NUMBER() OVER (PARTITION BY jeepney_id ORDER BY timestamp DESC) as rn
    FROM passenger_count_tbl
) pc ON j.jeepney_id = pc.jeepney_id AND pc.rn = 1
WHERE j.status = 'online';