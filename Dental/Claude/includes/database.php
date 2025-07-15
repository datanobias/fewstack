<?php
class Database {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $this->query($sql, array_merge($data, $whereParams));
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
    }
    
    public function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS candidates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(50),
            location VARCHAR(255),
            position VARCHAR(255),
            experience_level VARCHAR(100),
            platform VARCHAR(50),
            profile_url VARCHAR(500),
            bio TEXT,
            skills TEXT,
            score INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'new',
            last_activity DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_score (score),
            INDEX idx_status (status),
            INDEX idx_position (position),
            INDEX idx_platform (platform)
        );
        
        CREATE TABLE IF NOT EXISTS searches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query VARCHAR(255) NOT NULL,
            platform VARCHAR(50),
            location VARCHAR(255),
            results_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            candidate_id INT,
            message TEXT,
            type VARCHAR(50) DEFAULT 'info',
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS practice_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            practice_name VARCHAR(255),
            location VARCHAR(255),
            positions_needed TEXT,
            notification_email VARCHAR(255),
            min_score INT DEFAULT 50,
            auto_alerts BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        
        $this->connection->exec($sql);
    }
    
    public function getConnection() {
        return $this->connection;
    }

    public function scrapeWebData($url) {
        // Placeholder for web scraping logic
        // Use libraries like cURL or file_get_contents to fetch data from $url
        // Parse the data and store it in the database
        throw new Exception("Web scraping not implemented yet.");
    }

    public function monitorSocialMedia($platform, $query) {
        // Placeholder for social media monitoring logic
        // Use APIs or scraping techniques to fetch data from $platform based on $query
        // Parse the data and store it in the database
        throw new Exception("Social media monitoring not implemented yet.");
    }
}
?>