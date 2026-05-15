<?php
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($data);
            $stmt->fetch();
            return $data;
        }
        return '';
    }

    public function write($sessionId, $data) {
        $stmt = $this->conn->prepare("REPLACE INTO sessions (session_id, data, last_accessed) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $sessionId, $data);
        return $stmt->execute();
    }

    public function destroy($sessionId) {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
        return $stmt->execute();
    }

    public function gc($maxLifetime) {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("i", $maxLifetime);
        return $stmt->execute();
    }
}

// الاتصال بقاعدة البيانات
require('database/connect_to_database.php'); // الاتصال بقاعدة البيانات

// تعيين معالج الجلسة
$handler = new DatabaseSessionHandler($conn);
session_set_save_handler($handler, true);

// بدء الجلسة
session_start();
?>