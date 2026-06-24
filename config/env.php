<?php
/**
 * config/env.php
 * Loads environment variables from the .env file into $_ENV and getenv()
 * Call this FIRST before any other config file.
 *
 * NOTE: Do not install vlucas/phpdotenv for now — this lightweight
 *       parser covers all our .env needs without an extra dependency.
 */

declare(strict_types=1);

/**
 * Load a .env file from the given path.
 *
 * @param string $path Absolute path to the .env file
 * @throws RuntimeException If the .env file is missing
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException(
            ".env file not found at: {$path}\n" .
            "Copy .env.example to .env and fill in your values."
        );
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comment lines (starting with #)
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Split on first '=' only (values may contain '=')
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        [$key, $value] = $parts;
        $key   = trim($key);
        $value = trim($value);

        // Strip surrounding quotes from value ("value" or 'value')
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Set in environment if not already defined
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// Resolve the .env path (one level up from config/)
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
loadEnv($envPath);

/**
 * Convenience function to retrieve an env value with a default fallback.
 *
 * @param string $key     Environment variable name
 * @param mixed  $default Fallback value if key not found
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== null) ? $value : $default;
}
