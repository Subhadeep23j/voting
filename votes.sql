CREATE TABLE votes (
    vote_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    party_id INT NOT NULL,
    FOREIGN KEY (party_id) REFERENCES parties(participates_id),
    UNIQUE(user_id) -- ensures one vote per user
);
