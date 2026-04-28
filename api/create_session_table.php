<?php
/**
 * SCRIPT UNTUK MEMBUAT TABEL SESSION_LOGS
 * 
 * Jalankan script ini sekali melalui browser: 
 * http://localhost/Website-Cek-Penyakit-Tanaman/api/create_session_table.php
 * 
 * Atau copy-paste SQL ke database manager (phpMyAdmin, DBeaver, dll)
 */

require_once 'koneksi.php';

$sql = "
CREATE TABLE IF NOT EXISTS session_logs (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID session log',
    user_id INT UNSIGNED COMMENT 'ID user/admin yang login',
    user_type ENUM('pengguna', 'admin', NULL) COMMENT 'Tipe user',
    ip_address VARCHAR(45) NOT NULL COMMENT 'IP address user',
    action VARCHAR(50) NOT NULL COMMENT 'Aksi (login, logout, dll)',
    status ENUM('success', 'failed') NOT NULL DEFAULT 'success' COMMENT 'Status login',
    attempted_email VARCHAR(255) COMMENT 'Email yang dicoba (untuk failed login)',
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu login',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu aktivitas terakhir',
    user_agent VARCHAR(500) COMMENT 'Browser user agent',
    logout_time TIMESTAMP NULL COMMENT 'Waktu logout',
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_login_time (login_time),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel untuk mencatat semua aktivitas login/logout pengguna';
";

if (mysqli_query($conn, $sql)) {
    echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 5px; max-width: 500px; margin: 20px auto; border: 1px solid #4caf50;'>";
    echo "<h3 style='color: #2e7d32; margin-top: 0;'>✅ Tabel session_logs berhasil dibuat!</h3>";
    echo "<p style='color: #666; margin: 10px 0;'>Tabel ini akan menyimpan semua aktivitas login/logout untuk keperluan keamanan dan audit.</p>";
    echo "<p style='color: #999; font-size: 0.9rem; margin: 10px 0;'>Anda bisa menghapus file ini setelah berhasil membuat tabel.</p>";
    echo "</div>";
} else {
    $error = mysqli_error($conn);
    echo "<div style='background: #fef2f2; padding: 20px; border-radius: 5px; max-width: 500px; margin: 20px auto; border: 1px solid #dc2626;'>";
    echo "<h3 style='color: #dc2626; margin-top: 0;'>❌ Gagal membuat tabel</h3>";
    echo "<p style='color: #666; margin: 10px 0;'><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
    echo "</div>";
}

mysqli_close($conn);
?>
