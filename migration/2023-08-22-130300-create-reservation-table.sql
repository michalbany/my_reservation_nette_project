CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    arrival_date DATE NOT NULL,
    departure_date DATE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);
