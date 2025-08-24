CREATE TABLE parties (
    participates_id INT AUTO_INCREMENT PRIMARY KEY,
    politician_name VARCHAR(100) NOT NULL,
    politician_image VARCHAR(255) NOT NULL,
    party_id VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    party_name VARCHAR(100) NOT NULL,
    party_logo VARCHAR(255) NOT NULL,
    booth_name VARCHAR(100) NOT NULL,
    booth_id VARCHAR(50) NOT NULL
);
