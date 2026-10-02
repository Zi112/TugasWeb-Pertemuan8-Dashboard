<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Koneksi PDO dengan Singleton pattern:
 * hanya ada satu instance koneksi selama request berjalan.
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            exit('Koneksi database gagal. Pastikan MySQL aktif dan database.sql sudah diimport.');
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new RuntimeException('Singleton tidak boleh di-unserialize.');
    }

    public static function getInstance(): Database
    {
        return self::$instance ??= new Database();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
