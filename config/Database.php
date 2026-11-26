<?php
declare(strict_types=1);

/**
 * Conexión PDO Singleton. Credenciales exclusivamente desde $_ENV.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $k) {
                if (!isset($_ENV[$k]) || $_ENV[$k] === '') {
                    throw new RuntimeException("Variable de entorno $k no definida en .env");
                }
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_NAME'],
                $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Error de conexión BD: ' . $e->getMessage());
                throw new RuntimeException('No se pudo conectar a la base de datos.');
            }
        }

        return self::$instance;
    }
}
