-- Создаем таблицу users для хранения данных о пользователях
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status ENUM('cleaner', 'admin') NOT NULL,
  PRIMARY KEY (id)
);

-- Добавляем в таблицу users пользователя cleaner с паролем test и статусом cleaner
INSERT INTO users (username, password, status) VALUES ('cleaner', '$2y$10$0xV7jwT6lqZ3a1k6fL4J8OQj9nXc5s0ZbY7s8gIw0Qm1z4fKtRk9W', 'cleaner');

-- Добавляем в таблицу users пользователя administrator с паролем admin и статусом admin
INSERT INTO users (username, password, status) VALUES ('administrator', '$2y$10$YtY5vMzGJtFpKb7aX8ZLXeEoGxHgQ2jFwEwRnQ1yq3nDh4lP9oBZS', 'admin');

-- Создаем таблицу reports для хранения данных об отчетах по уборке
CREATE TABLE reports (
  id INT NOT NULL AUTO_INCREMENT,
  employee VARCHAR(255) NOT NULL,
  location VARCHAR(255) NOT NULL,
  startTime DATETIME NOT NULL,
  endTime DATETIME NOT NULL,
  photoUrl VARCHAR(255) NOT NULL,
  confirmed BOOLEAN DEFAULT FALSE,
  rejected BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (id)
);

-- Создаем таблицу logs для хранения данных о действиях пользователей
CREATE TABLE logs (
  id INT NOT NULL AUTO_INCREMENT,
  user VARCHAR(255) NOT NULL,
  action VARCHAR(255) NOT NULL,
  report INT,
  time DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (report) REFERENCES reports(id)
);
