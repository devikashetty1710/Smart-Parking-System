-- Create parking_spaces table if not exists
CREATE TABLE IF NOT EXISTS parking_spaces (
    space_id INT AUTO_INCREMENT PRIMARY KEY,
    space_name VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create users table if not exists
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create reservations table if not exists
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    space_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (space_id) REFERENCES parking_spaces(space_id)
);

-- Insert parking spaces
INSERT INTO parking_spaces (space_name, location, price_per_hour, capacity, description) VALUES
-- Downtown Area (10 spaces)
('DT-A1', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'),
('DT-A2', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'),
('DT-A3', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'),
('DT-A4', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'),
('DT-A5', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'),
('DT-B1', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'),
('DT-B2', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'),
('DT-B3', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'),
('DT-B4', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'),
('DT-B5', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'),

-- Business District (10 spaces)
('BD-A1', 'Business District', 6.00, 1, 'Business district premium parking'),
('BD-A2', 'Business District', 6.00, 1, 'Business district premium parking'),
('BD-A3', 'Business District', 6.00, 1, 'Business district premium parking'),
('BD-A4', 'Business District', 6.00, 1, 'Business district premium parking'),
('BD-A5', 'Business District', 6.00, 1, 'Business district premium parking'),
('BD-B1', 'Business District', 5.50, 1, 'Business district standard parking'),
('BD-B2', 'Business District', 5.50, 1, 'Business district standard parking'),
('BD-B3', 'Business District', 5.50, 1, 'Business district standard parking'),
('BD-B4', 'Business District', 5.50, 1, 'Business district standard parking'),
('BD-B5', 'Business District', 5.50, 1, 'Business district standard parking'),

-- Shopping Mall (10 spaces)
('SM-A1', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'),
('SM-A2', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'),
('SM-A3', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'),
('SM-A4', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'),
('SM-A5', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'),
('SM-B1', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'),
('SM-B2', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'),
('SM-B3', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'),
('SM-B4', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'),
('SM-B5', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'); 