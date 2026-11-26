<?php
declare(strict_types=1);

/**
 * Lector ligero de archivos .env (sin dependencias externas).
 */
final class EnvLoader
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Archivo .env no encontrado. Copia .env.example a .env y configúralo.');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
                continue;
            }

            // Comillas envolventes
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && str_ends_with($value, $value[0])) {
                $value = substr($value, 1, -1);
            } else {
                // Comentario inline: VALOR # comentario
                $value = trim(preg_replace('/\s+#.*$/', '', $value) ?? $value);
            }

            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? $default;
    }
}
