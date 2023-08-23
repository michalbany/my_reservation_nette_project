CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('1lůžkový', '2lůžkový', 'apartmán') NOT NULL,
    room_number VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    inactive BOOLEAN DEFAULT 0
);
